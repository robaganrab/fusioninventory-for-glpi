<?php

/*
   ----------------------------------------------------------------------
   FusionInventory
   Copyright (C) 2010-2011 by the FusionInventory Development Team.

   http://www.fusioninventory.org/   http://forge.fusioninventory.org/
   ----------------------------------------------------------------------

   LICENSE

   This file is part of FusionInventory.

   FusionInventory is free software: you can redistribute it and/or modify
   it under the terms of the GNU General Public License as published by
   the Free Software Foundation, either version 2 of the License, or
   any later version.

   FusionInventory is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with FusionInventory.  If not, see <http://www.gnu.org/licenses/>.

   ------------------------------------------------------------------------
   Original Author of file: Vincent Mazzoni
   Co-authors of file: David DURIEUX
   Purpose of file:
   ----------------------------------------------------------------------
 */

ini_set("memory_limit", "-1");
ini_set("max_execution_time", "0");

if (!defined('GLPI_ROOT')) {
   define('GLPI_ROOT', '../../..');
}

if (session_id()=="") {
   session_start();
}

$loadplugins = 0;
if (!isset($_SESSION["glpi_plugins"])) {
   $loadplugins = 1;
}
$loadplugins = 1;

$_SESSION['glpi_use_mode'] = 2;
include_once(GLPI_ROOT."/inc/includes.php");
if (!isset($_SESSION['glpilanguage'])) {
   $_SESSION['glpilanguage'] = 'fr_FR';
}
   
ini_set('display_errors','On');
error_reporting(E_ALL | E_STRICT);
set_error_handler('userErrorHandlerDebug');
$_SESSION['glpi_use_mode'] = 2;

//Agent communication using REST protocol
if (isset($_GET['action']) && isset($_GET['machineid'])) {
   $response = PluginFusioninventoryRestCommunication::communicate($_GET);
   if ($response) {
      echo json_encode($response);
   } else {
      PluginFusioninventoryRestCommunication::sendError();
   }
//Only go there if agent is using the old XML protocol
} else {
   
   $communication  = new PluginFusioninventoryCommunication();
   $pta            = new PluginFusioninventoryAgent();
   $errors         = '';
   
   // ***** For debug only ***** //
   //$GLOBALS["HTTP_RAW_POST_DATA"] = gzcompress('');
   // ********** End ********** //
   
   if (isset($GLOBALS["HTTP_RAW_POST_DATA"])) {
      // Get conf tu know if SSL is only
   
      $fusioninventory_config      = new PluginFusioninventoryConfig();
      $PluginFusioninventoryModule = new PluginFusioninventoryModule();
      
      $fusioninventoryModule_id    = $PluginFusioninventoryModule->getModuleId("fusioninventory");
   
      if ($loadplugins == '1') {
         $users_id = $fusioninventory_config->getValue($fusioninventoryModule_id, 'users_id');
         $_SESSION['glpiID'] = $users_id;
            $plugin = new Plugin();
            $plugin->init();
            $LOADED_PLUGINS = array();
            if (isset($_SESSION["glpi_plugins"]) && is_array($_SESSION["glpi_plugins"])) {
               //doHook("config");
               if (count($_SESSION["glpi_plugins"])) {
                  foreach ($_SESSION["glpi_plugins"] as $name) {
                     Plugin::load($name);
                  }
               }
               // For plugins which require action after all plugin init
               doHook("post_init");
            }
      }
      
      $ssl = $fusioninventory_config->getValue($fusioninventoryModule_id, 'ssl_only');
      if (((isset($_SERVER["HTTPS"])) AND ($_SERVER["HTTPS"] == "on") AND ($ssl == "1"))
          OR ($ssl == "0")) {
         // echo "On continue";
      } else {
         $communication->setXML("<?xml version='1.0' encoding='UTF-8'?>
   <REPLY>
   </REPLY>");
         $communication->noSSL();
         exit();
      }
   
      // Check XML integrity
      $xml = '';
      $PluginFusioninventoryTaskjob = new PluginFusioninventoryTaskjob();
      $PluginFusioninventoryTaskjob->disableDebug();
      $comp = gzuncompress($GLOBALS["HTTP_RAW_POST_DATA"]);
      $PluginFusioninventoryTaskjob->reenableusemode();
      if ($comp) {
         $xml = gzuncompress($GLOBALS["HTTP_RAW_POST_DATA"]);
      } else if (gzinflate (substr($GLOBALS["HTTP_RAW_POST_DATA"], 2))) {
         // ** OCS agent 2.0 Compatibility
         $xml = gzinflate (substr($GLOBALS["HTTP_RAW_POST_DATA"], 2));
      } else {
         $xml = $GLOBALS["HTTP_RAW_POST_DATA"];
      }


      if (PluginFusioninventoryConfig::isExtradebugActive()) {
         file_put_contents(GLPI_PLUGIN_DOC_DIR."/fusioninventory/dial.log".uniqid(), $xml);
      }
      if (@simplexml_load_string($xml,'SimpleXMLElement', LIBXML_NOCDATA)) {
         $pxml = @simplexml_load_string($xml,'SimpleXMLElement', LIBXML_NOCDATA);
      } else if (@simplexml_load_string(utf8_encode($xml),'SimpleXMLElement', LIBXML_NOCDATA)) {
         $pxml = @simplexml_load_string(utf8_encode($xml),'SimpleXMLElement', LIBXML_NOCDATA);
         $xml = utf8_encode($xml);
      } else {
         $PluginFusioninventoryCommunication->setXML("<?xml version='1.0' encoding='UTF-8'?>
<REPLY>
   <ERROR>XML not well formed!</ERROR>
</REPLY>");
         $PluginFusioninventoryCommunication->emptyAnswer();
      }
   
      //
   
   
      $pta->importToken($xml);
   
      $top0 = 0;
      $top0 = gettimeofday();
      if (!$communication->import($xml)) {
   
         if (isset($pxml->DEVICEID)) {
   
            $communication->setXML("<?xml version='1.0' encoding='UTF-8'?>
<REPLY>
</REPLY>");
   
            $a_agent = $pta->InfosByKey(addslashes_deep($pxml->DEVICEID));
   
            // Get taskjob in waiting
            $communication->getTaskAgent($a_agent['id']);
            // ******** Send XML
   
            $communication->addInventory($a_agent['id']);
            $communication->addProlog();
            $communication->setXML($communication->getXML());
   
            echo $communication->getSend();
         }
      } else {
         $communication->setXML("<?xml version='1.0' encoding='UTF-8'?>
<REPLY>
</REPLY>");
         $communication->emptyAnswer();
      }
   }
   
}
session_destroy();
?>
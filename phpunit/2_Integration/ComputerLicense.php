<?php

/*
   ------------------------------------------------------------------------
   FusionInventory
   Copyright (C) 2010-2013 by the FusionInventory Development Team.

   http://www.fusioninventory.org/   http://forge.fusioninventory.org/
   ------------------------------------------------------------------------

   LICENSE

   This file is part of FusionInventory project.

   FusionInventory is free software: you can redistribute it and/or modify
   it under the terms of the GNU Affero General Public License as published by
   the Free Software Foundation, either version 3 of the License, or
   (at your option) any later version.

   FusionInventory is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
   GNU Affero General Public License for more details.

   You should have received a copy of the GNU Affero General Public License
   along with FusionInventory. If not, see <http://www.gnu.org/licenses/>.

   ------------------------------------------------------------------------

   @package   FusionInventory
   @author    David Durieux
   @co-author
   @copyright Copyright (c) 2010-2013 FusionInventory team
   @license   AGPL License 3.0 or (at your option) any later version
              http://www.gnu.org/licenses/agpl-3.0-standalone.html
   @link      http://www.fusioninventory.org/
   @link      http://forge.fusioninventory.org/projects/fusioninventory-for-glpi/
   @since     2013

   ------------------------------------------------------------------------
 */

class ComputerLicense extends PHPUnit_Framework_TestCase {
   public $a_computer1 = array();
   public $a_computer1_beforeformat = array();
   
   function __construct() {
      $this->a_computer1 = array(
          "Computer" => array(
              "name"   => "pc001",
              "serial" => "ggheb7ne7"
          ), 
          "fusioninventorycomputer" => Array(
              'last_fusioninventory_update' => date('Y-m-d H:i:s'),
              'serialized_inventory'        => 'something'
          ),
          'soundcard'      => array(),
          'graphiccard'    => array(),
          'controller'     => array(),
          'processor'      => array(),
          "computerdisk"   => array(),
          'memory'         => array(),
          'monitor'        => array(),
          'printer'        => array(),
          'peripheral'     => array(),
          'networkport'    => array(),
          'software'       => array(),
          'harddrive'      => array(),
          'virtualmachine' => array(),
          'antivirus'      => array(),
          'storage'        => array(),
          'licenseinfo'    => array(
              array(
                  'name'     => 'Microsoft Office 2003',
                  'fullname' => 'Microsoft Office Professional Edition 2003',
                  'serial'   => 'xxxxx-xxxxx-P6RC4-xxxxx-xxxxx'
              )
          ),
          'itemtype'       => 'Computer'
      );

      $this->a_computer1_beforeformat = array(
          "CONTENT" => array(
              "HARDWARE" => array(
                  "NAME"   => "pc001"
              ),
              "BIOS" => array(
                  "SSN" => "ggheb7ne7"
              ), 
              'LICENSEINFOS' => Array(
                  array(
                      'COMPONENTS' => 'Word/Excel/Access/Outlook/PowerPoint/Publisher/InfoPath',
                      'FULLNAME'   => 'Microsoft Office Professional Edition 2003',
                      'KEY'        => 'xxxxx-xxxxx-P6RC4-xxxxx-xxxxx',
                      'NAME'       => 'Microsoft Office 2003',
                      'PRODUCTID'  => 'xxxxx-640-0000xxx-xxxxx'
                  )
              )
          )
      );
   }
   
   
   
   public function testLicenses() {
      global $DB;

      $DB->connect();
      
      $Install = new Install();
      $Install->testInstall(0);

      $_SESSION['glpiactive_entity'] = 0;
      $_SESSION["plugin_fusioninventory_entity"] = 0;
      
      $pfiComputerLib   = new PluginFusioninventoryInventoryComputerLib();
      $computer         = new Computer();
      $GLPIlog          = new GLPIlogs();
      
      $a_computerinventory = $this->a_computer1;
      $a_computer = $a_computerinventory['Computer'];
      $a_computer["entities_id"] = 0;
      $computers_id = $computer->add($a_computer);
      
      $pfiComputerLib->updateComputer($a_computerinventory, 
                                      $computers_id, 
                                      FALSE, 
                                      1);
      
      $GLPIlog->testSQLlogs();
      $GLPIlog->testPHPlogs();

      $computer->getFromDB(1);
      $this->assertEquals('ggheb7ne7', $computer->fields['serial'], 'Computer not updated correctly');
      
      $this->assertEquals(1, 
                          countElementsInTable('glpi_plugin_fusioninventory_computerlicenseinfos'), 
                          'License may be added in fusion table');
      
      $pfComputerLicenseInfo = new PluginFusioninventoryComputerLicenseInfo();
      $pfComputerLicenseInfo->getFromDB(1);
      $a_ref = array(
          'id'                   => 1,
          'computers_id'         => 1,
          'softwarelicenses_id'  => 0,
          'name'                 => 'Microsoft Office 2003',
          'fullname'             => 'Microsoft Office Professional Edition 2003',
          'serial'               => 'xxxxx-xxxxx-P6RC4-xxxxx-xxxxx',
          'is_trial'             => '0',
          'is_update'            => '0',
          'is_oem'               => '0',
          'activation_date'      => NULL
      );
      
      $this->assertEquals($a_ref, 
                          $pfComputerLicenseInfo->fields, 
                          'License data');
      
      
   }   
}



class ComputerLicense_AllTests  {

   public static function suite() {
     
      $suite = new PHPUnit_Framework_TestSuite('ComputerLicense');
      return $suite;
   }
}

?>
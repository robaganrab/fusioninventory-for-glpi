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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginFusioninventoryConfigurationManagement extends CommonDBTM {
   
   static $rightname = 'plugin_fusioninventory_agent';

   
   /**
   * Get name of this type
   *
   * @return text name of this type by language of the user connected
   *
   **/
   static function getTypeName($nb=0) {
      return __('Configuration management', 'fusioninventory');
   }



   function getSearchOptions() {

      $tab = array();

      $tab['common'] = __('Agent', 'fusioninventory');

      $tab[1]['table']     = $this->getTable();
      $tab[1]['field']     = 'name';
      $tab[1]['linkfield'] = 'name';
      $tab[1]['name']      = __('Name');
      $tab[1]['datatype']  = 'itemlink';

      $tab[2]['table']     = $this->getTable();
      $tab[2]['field']     = 'conform';
      $tab[2]['massiveaction']    = false;
      $tab[2]['name']      = __('conform', 'fusioninventory');
      $tab[2]['datatype']  = 'bool';

      $tab[3]['table']     = $this->getTable();
      $tab[3]['field']     = 'sha_referential';
      $tab[3]['massiveaction']    = false;
      $tab[3]['name']      = __('SHA of referential', 'fusioninventory');
      $tab[3]['datatype']  = 'string';

      $tab[4]['table']             = $this->getTable();
      $tab[4]['field']             = 'items_id';
      $tab[4]['name']              = __('Associated element');
      $tab[4]['datatype']          = 'specific';
      $tab[4]['nosearch']          = true;
      $tab[4]['nosort']            = true;
      $tab[4]['massiveaction']     = false;
      $tab[4]['additionalfields']  = array('itemtype');

      
      $tab[5]['table']            = $this->getTable();
      $tab[5]['field']            = 'itemtype';
      $tab[5]['name']             = __('Type');
      $tab[5]['massiveaction']    = false;
      $tab[5]['datatype']         = 'itemtypename';

      
      return $tab;
   }
   
   
   
   /**
    * @see CommonGLPI::defineTabs()
   **/
   function defineTabs($options=array()) {

      $ong = array();
   //   $this->addDefaultFormTab($ong);
      $this->addStandardTab("PluginFusioninventoryConfigurationManagement", $ong, $options);
      return $ong;
   }

   
   
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      $a_tabs = array();
      if ($item->getType() == __CLASS__) {
         $a_tabs[1] = 'Generate the referentiel';
         if ($item->fields['sha_referential'] != '') {
            $a_tabs[2] = 'Diff';
         }
      } else if ($item->getType() == 'Computer') {
         $a_tabs[1] = 'Configuration Management';
      } 
      return $a_tabs;
   }



   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      if ($item->getType() == __CLASS__) {
         if ($tabnum == 1) {
            $item->generateReferential($item->getID());
         } else if ($tabnum == 2) {
            $a_currentinv = $item->generateCurrentInventory($item->getID());
            $item->displayDiff($item->getID(), $a_currentinv);
         }
      } else if ($item->getType() == 'Computer') {
         $pfConfigurationManagement = new PluginFusioninventoryConfigurationManagement();
         $a_find = $pfConfigurationManagement->find("`itemtype`='Computer'
                           AND `items_id`='".$item->getID()."'", '', 1);
         if (count($a_find) == 0) {
            // form to add this computer into config management
            $pfConfigurationManagement->showForm($item);
         } else {
            $data = current($a_find);
            if ($data['sha_referential'] == '') {
               $pfConfigurationManagement->showLinkToDefineRef($data['id']);
            } else {
               // See diff
               $a_currentinv = $pfConfigurationManagement->generateCurrentInventory($data['id']);
               $pfConfigurationManagement->displayDiff($data['id'], $a_currentinv);
            }
         }
         
      } 
      return TRUE;
   }
   
   
   
   
   /** Display item with tabs
    *
    * @since version 0.85
    *
    * @param $options   array
   **/
   function display($options=array()) {

      if (isset($options['id'])
          && !$this->isNewID($options['id'])) {
         $this->getFromDB($options['id']);
      }

      $this->showNavigationHeader($options);
      $this->showTabsContent($options);
   }
   
   
   
   function showForm($item) {

      $this->getEmpty();
      $this->showFormHeader();
      
      echo "<tr class='tab_bg_1'>";
      echo "<td>";
      echo "To add configuratiom management of this device, click on add button...";
      echo Html::hidden('itemtype', array('value' => $item->getType()));
      echo Html::hidden('items_id', array('value' => $item->getID()));
      echo "</td>";
      echo "</tr>";
      
      $this->showFormButtons();

      return true;
   }

   
   
   function showLinkToDefineRef($id) {
      
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr class='tab_bg_1'>";
      echo "<th>";
      echo "<br/><a href='".$this->getFormURL()."?id=".$id."' class='vsubmit'>Define your referential</a><br/><br/>";
      echo "</th>";
      echo "</tr>";
      echo "</table>";
   }
   
   
   
   function generateReferential($items_id) {
      global $CFG_GLPI;
      
      $pfconfmanage_model = new PluginFusioninventoryConfigurationManagement_Model();

      $list_fields = $pfconfmanage_model->getListFields();

      $this->getFromDB($items_id);
      
      // Use model
      
      echo "<form method='post' name='' id=''  action=\"".$CFG_GLPI['root_doc'] .
         "/plugins/fusioninventory/front/configurationmanagement.form.php\">";

      echo "<table class='tab_cadre_fixe'>";
      echo "<tr class='tab_bg_1'>";
      echo '<th colspan="2"></th>';
      echo '<th>Géré</th>';
      echo '<th>Ignoré</th>';
      echo '<th>Pas géré</th>';
      echo '<th>Valeur de référence</th>';
      echo '</tr>';
      $this->displayGenerateLine(1, $list_fields, 1, '', $this->fields['items_id'],$this->fields['itemtype']);
      echo "</table>";

      echo "<tr>";
      echo "<th colspan='5'>";
      echo Html::hidden('id', array('value' => $items_id));
      echo "<input name='update_serialized' value=\"".__('Save').
         "\" class='submit' type='submit'>";
      echo "</th>";
      echo "</tr>";
      
      echo "</table>";
      Html::closeForm();
   }
   
   

   function displayGenerateLine($rank, $a_fields, $new, $tree, $items_id=0, $itemtype='', $a_DBvalues=array()) {
      foreach ($a_fields as $key=>$data) {
         if ($key != '_internal_name_'
                 && $key != '_itemtype_') {
            if (is_array($data)) {
               if ($itemtype == '') {
                  $a_DBData = $this->getData_fromDB($key, $items_id, $data['_itemtype_'], $tree.$key."/".$items_id."/");
               } else {
                  $a_DBData = $this->getData_fromDB($key, $items_id, $itemtype, $tree."/".$items_id."/".$key);
               }
               $celltype = 'td';
               if ($rank == 1) {
                  $celltype = 'th';
               }
               foreach ($a_DBData as $k=>$a_val) {
                  echo "<tr class='tab_bg_3'>";
                  echo '<'.$celltype.' colspan="2" class="center"><strong>';
                  echo $data['_internal_name_'];
                  echo '</strong></'.$celltype.'>';
                  $managed_checked = '';
                  $ignored_checked = '';

                  $tree_temp = $tree."/".$key."/".$a_val['id']."/_managetype_";
                  if ($new) {
                     $managed_checked = 'checked';
                  } else if (isset($this->model_tree[$tree_temp])) {
                     if ($this->model_tree[$tree_temp] == 'managed') {
                        $managed_checked = 'checked';
                     } else {
                        $ignored_checked = 'checked';
                     }
                  }
                  echo '<'.$celltype.' class="center">';
                  echo "<input type='radio' name='".$tree_temp."' value='managed' ".$managed_checked." />";
                  echo '</'.$celltype.'>';
                  echo '<'.$celltype.' class="center">';
                  echo "<input type='radio' name='".$tree_temp."' value='ignored' ".$ignored_checked." />";
                  echo '</'.$celltype.'>';
                  echo '<'.$celltype.' class="center"></'.$celltype.'>';
                  echo '<'.$celltype.'>';
                  echo '</'.$celltype.'>';
                  echo "</tr>";
                  $this->displayGenerateLine(($rank+1), $data, $new, $tree."/".$key."/".$a_val['id'], $a_val['id'], $data['_itemtype_'], $a_val);
               }
            } else {
               $managed_checked = '';
               $ignored_checked = '';
               $notmanaged_checked = '';

               if ($new) {
                  $managed_checked = 'checked';
               } else if (isset($this->model_tree[$tree."/".$key])) {
                  if ($this->model_tree[$tree."/".$key] == 'managed') {
                     $managed_checked = 'checked';
                  } else if ($this->model_tree[$tree."/".$key] == 'ignored') {
                     $ignored_checked = 'checked';
                  } else {
                     $notmanaged_checked = 'checked';
                  }
               }

               echo "<tr class='tab_bg_3'>";
               for ($i=2; $i < $rank; $i++) {
                  echo '<td></td>';
               }
               echo '<td colspan="'.(2-($rank-2)).'">';
               echo $data;            
               echo '</td>';
               echo '<td class="center">';
               $tree_temp = $tree."/".$key;
               $value = '';
               if (isset($a_DBvalues[$key])) {
                  $value = $a_DBvalues[$key];
               }
               echo "<input type='radio' name='".$tree_temp."' value='".$value."' ".$managed_checked." />";
               echo '</td>';
               echo '<td class="center">';
               echo "<input type='radio' name='".$tree_temp."' value='_ignored_' ".$ignored_checked." />";
               echo '</td>';
               echo '<td class="center">';
               echo "<input type='radio' name='".$tree_temp."' value='_notmanaged_' ".$notmanaged_checked." />";
               echo '</td>';
               echo '<td>';
               if (isset($a_DBvalues[$key])) {
                  echo $a_DBvalues[$key];
               }
               echo '</td>';
               echo "</tr>";
            }
         }
      }
   }
   
   
   
   function getData_fromDB($name, $items_id, $itemtype, $tree) {
      $a_DBdata = array();
      $item = new $itemtype();
      if ($item->getFromDB($items_id)) {
      
         switch ($name) {
            case 'manufacturers_id':
               $manufacturer = new Manufacturer();
               if ($manufacturer->getFromDB($item->fields[$name])) {
                  $a_DBdata[] = $manufacturer->fields;
               } else {
                  $manufacturer->getEmpty();
                  $manufacturer->fields['id'] = $item->fields[$name];
                  $a_DBdata[] = $manufacturer->fields;
               }
               break;

            case 'Computer':
               $a_DBdata[] = $item->fields;
               break;

            case 'users_id_tech':
            case 'users_id':
               $user = new User();
               if ($user->getFromDB($item->fields[$name])) {
                  $a_DBdata[] = $user->fields;
               } else {
                  $user->getEmpty();
                  $user->fields['id'] = $item->fields[$name];
                  $a_DBdata[] = $user->fields;
               }
               break;

            case 'processor':
               $deviceProcessor = new DeviceProcessor();
               $item_DeviceProcessor = new Item_DeviceProcessor();
               $a_procs = $item_DeviceProcessor->find("`itemtype`='".$itemtype."'
                  AND `items_id`='".$items_id."'");
               foreach ($a_procs as $a_proc) {
                  $deviceProcessor->getFromDB($a_proc['deviceprocessors_id']);
                  $a_add = $deviceProcessor->fields;
                  $a_add['frequency'] = $a_proc['frequency'];
                  $a_add['serial'] = $a_proc['serial'];
                  $a_add['id'] = $a_proc['id'];
                  $a_DBdata[] = $a_add;               
               }
               break;

            case 'software':
               $software = new Software();
               $softwareVersion = new SoftwareVersion();
               $computer_SoftwareVersion = new Computer_SoftwareVersion();

               $a_compversions = $computer_SoftwareVersion->find("`computers_id`='".$items_id."'");
               foreach ($a_compversions as $a_compversion) {
                  $softwareVersion->getFromDB($a_compversion['softwareversions_id']);
                  $software->getFromDB($softwareVersion->fields['softwares_id']);
                  $a_add = $software->fields;
                  $a_add['version'] = $softwareVersion->fields['name'];
                  $a_DBdata[] = $a_add;               
               }
               break;

         }
      }
      return $a_DBdata;
   }

   

   function generateCurrentInventory($id, $a_ref=array(), $a_currentinv=array(), 
                                     $a_fields=array(), $tree='', $items_id=0, $itemtype='', $a_DBvalues=array()) {
      if (count($a_ref) == 0) {
         $this->getFromDB($id);
         $a_ref = importArrayFromDB($this->fields['serialized_referential']);
         $pfconfmanage_model = new PluginFusioninventoryConfigurationManagement_Model();
         $a_fields = $pfconfmanage_model->getListFields();
         $itemtype = $this->fields['itemtype'];
         $items_id = $this->fields['items_id'];
      }

      foreach ($a_fields as $key=>$data) {
         if ($key != '_internal_name_'
                 && $key != '_itemtype_') {
            if (is_array($data)) {
               if ($itemtype == '') {
                  $a_DBData = $this->getData_fromDB($key, $items_id, $data['_itemtype_'], $tree.$key."/".$items_id."/");
               } else {
                  $a_DBData = $this->getData_fromDB($key, $items_id, $itemtype, $tree."/".$items_id."/".$key);
               }
               foreach ($a_DBData as $k=>$a_val) {
                  $tree_temp = $tree."/".$key."/".$a_val['id']."/_managetype_";
                  if (isset($a_ref[$tree_temp])) {
                     $a_currentinv[$tree_temp] = $a_ref[$tree_temp];
                  } else {
                     $a_currentinv[$tree_temp] = 'managed';
                  }
                  if ($a_currentinv[$tree_temp] != 'ignored') {
                     $a_currentinv = $this->generateCurrentInventory($id, $a_ref, $a_currentinv, $data, $tree."/".$key."/".$a_val['id'], $a_val['id'], $data['_itemtype_'], $a_val);
                  }
               }
            } else {
               $tree_temp = $tree."/".$key;
               $value = '';
               if (isset($a_DBvalues[$key])) {
                  $value = $a_DBvalues[$key];
               }
               if (isset($a_ref[$tree_temp])) {
                  if ($a_ref[$tree_temp] == '_ignored_') {
                     $a_currentinv[$tree_temp] = '_ignored_';
                  } else if ($a_ref[$tree_temp] == '_notmanaged_') {
                     // not use it
                  } else {
                     $a_currentinv[$tree_temp] = $value;
                  }
               } else {
                  $a_currentinv[$tree_temp] = $value;
               }
            }
         }
      }
      return $a_currentinv;
   }
   
   
   
   function displayDiff($items_id, $a_currentinv) {
      
      $pfconfmanage_model = new PluginFusioninventoryConfigurationManagement_Model();

      $list_fields = $pfconfmanage_model->getListFields();
      
      $this->getFromDB($items_id);
      $a_ref = importArrayFromDB($this->fields['serialized_referential']);

      $a_missingInCurrentinv = array_diff_key($a_ref, $a_currentinv);
      $a_missingInRef = array_diff_key($a_currentinv, $a_ref);
      
      $a_update = array();
      foreach ($a_ref as $key=>$value) {
         if (isset($a_currentinv[$key])
                 && $a_currentinv[$key] != $value) {
            $a_update[$key] = $value;
         }
      }
      
      echo "<table class='tab_cadre_fixe'>";

      if (count($a_missingInCurrentinv)) {
         echo "<tr class='tab_bg_1'>";
         echo "<th colspan='3'>";
         echo "Values not found in current inventory but referenced";
         echo "</th>";
         echo "<th colspan='3'>";
         echo "In reference";
         echo "</th>";
         echo "</tr>";

         
         echo "<tr class='tab_bg_1'>";
         echo "<th>";
         echo "What";
         echo "</th>";
         echo "<th>";
         echo "valeur attendue";
         echo "</th>";
         echo "<th>";
         echo "valeur trouvée";
         echo "</th>";
         echo "<th width='40'>";
         echo "Add";
         echo "</th>";
         echo "<th width='40'>";
         echo "Update";
         echo "</th>";
         echo "<th width='40'>";
         echo "Delete";
         echo "</th>";
         echo "</tr>";
      }
      
      $a_miss_curr = array();
      foreach ($a_missingInCurrentinv as $key=>$value) {
         $split = explode('/', $key);
         unset($split[(count($split) - 1)]);
         $a_miss_curr[(implode('/', $split))] = "";
      }
      
      foreach ($a_miss_curr as $key=>$value) {
         // Get all elements of the section
         $split = explode('/', $key);
         $list_fields_temp = $list_fields;
         for ($i=1; $i < count($split); $i += 2) {
            $list_fields_temp = $list_fields_temp[$split[$i]];
         }
         
         echo "<tr class='tab_bg_3'>";
         echo "<th>";
         echo $list_fields_temp['_internal_name_'];
         if (count($split) > 3) {
            echo " (".$key.")";
         }
         echo "</th>";
         echo "<td colspan='2'>";
         echo "</td>";
         echo "<td colspan='3'>";
         echo "</td>";
         echo "</tr>";
         
         foreach ($list_fields_temp as $keyref=>$valueref) {
            if ($keyref != '_internal_name_'
                 && $keyref != '_itemtype_') {
               if (!is_array($valueref)) {
                  if (isset($a_missingInCurrentinv[$key.'/'.$keyref])) {
                     echo "<tr class='tab_bg_3'>";
                     echo "<td>";
                     echo $valueref;
                     echo "</td>";
                     echo "<td style='background-color:#ccffcc'>";
                     echo $a_ref[$key.'/'.$keyref];
                     echo "</td>";
                     echo "<td style='background-color:#ffcccc'>";
                     echo "</td>";
                     echo "<td>";
                     echo "</td>";
                     echo "<td>";
                     echo "</td>";
                     echo "<td>";
                     echo "</td>";
                     echo "</tr>";   
                  }
               }
            }
         }
      }
      

      if (count($a_missingInRef)) {
         echo "<tr class='tab_bg_1'>";
         echo "<th colspan='3'>";
         echo "Values found in current inventory but not referenced";
         echo "</th>";
         echo "</tr>";
         
         echo "<tr class='tab_bg_1'>";
         echo "<th>";
         echo "What";
         echo "</th>";
         echo "<th>";
         echo "valeur attendue";
         echo "</th>";
         echo "<th>";
         echo "valeur trouvée";
         echo "</th>";
         echo "</tr>";
      }
      
      $a_miss_ref = array();
      foreach ($a_missingInRef as $key=>$value) {
         $split = explode('/', $key);
         unset($split[(count($split) - 1)]);
         $a_miss_ref[(implode('/', $split))] = "";
      }
      
      foreach ($a_miss_ref as $key=>$value) {
         // Get all elements of the section
         $split = explode('/', $key);
         $list_fields_temp = $list_fields;
         for ($i=1; $i < count($split); $i += 2) {
            $list_fields_temp = $list_fields_temp[$split[$i]];
         }
         
         echo "<tr class='tab_bg_3'>";
         echo "<th>";
         echo $list_fields_temp['_internal_name_'];
         echo "</th>";
         echo "<td colspan='2'>";
         echo "</td>";
         echo "</tr>";
         
         foreach ($list_fields_temp as $keyref=>$valueref) {
            if ($keyref != '_internal_name_'
                 && $keyref != '_itemtype_') {
               if (!is_array($valueref)) {
                  if (isset($a_missingInRef[$key.'/'.$keyref])) {
                     echo "<tr class='tab_bg_3'>";
                     echo "<td>";
                     echo $valueref;
                     echo "</td>";
                     echo "<td style='background-color:#ffcccc'>";
                     echo "</td>";
                     echo "<td style='background-color:#ccffcc'>";
                     echo $a_currentinv[$key.'/'.$keyref];
                     echo "</td>";
                     echo "</tr>";   
                  }
               }
            }
         }
      }

      
      if (count($a_update)) {
         echo "<tr class='tab_bg_1'>";
         echo "<th colspan='3'>";
         echo "Values not same in ref and current inventory";
         echo "</th>";
         echo "</tr>";

         
         echo "<tr class='tab_bg_1'>";
         echo "<th>";
         echo "What";
         echo "</th>";
         echo "<th>";
         echo "valeur attendue";
         echo "</th>";
         echo "<th>";
         echo "valeur trouvée";
         echo "</th>";
         echo "</tr>";
      }

      $a_update_sections = array();
      foreach ($a_update as $key=>$value) {
         $split = explode('/', $key);
         unset($split[(count($split) - 1)]);
         $a_update_sections[(implode('/', $split))] = "";
      }
      
      foreach ($a_update_sections as $key=>$value) {
         // Get all elements of the section
         $split = explode('/', $key);
         $list_fields_temp = $list_fields;
         for ($i=1; $i < count($split); $i += 2) {
            $list_fields_temp = $list_fields_temp[$split[$i]];
         }
         
         echo "<tr class='tab_bg_3'>";
         echo "<th>";
         echo $list_fields_temp['_internal_name_'];
         echo "</th>";
         echo "<td colspan='2'>";
         echo "</td>";
         echo "</tr>";
         
         foreach ($list_fields_temp as $keyref=>$valueref) {
            if ($keyref != '_internal_name_'
                 && $keyref != '_itemtype_') {
               if (!is_array($valueref)) {
                  if (isset($a_update[$key.'/'.$keyref])) {
                     echo "<tr class='tab_bg_3'>";
                     echo "<td>";
                     echo $valueref;
                     echo "</td>";
                     echo "<td style='background-color:#ccffcc'>";
                     echo $a_ref[$key.'/'.$keyref];
                     echo "</td>";
                     echo "<td style='background-color:#ffcccc'>";
                     echo $a_currentinv[$key.'/'.$keyref];
                     echo "</td>";
                     echo "</tr>";   
                  } else {
                     echo "<tr>";
                     echo "<td>";
                     echo $valueref;
                     echo "</td>";
                     echo "<td colspan='2' class='tab_bg_3' style='background-color:#ccffcc'>";
                     echo $a_ref[$key.'/'.$keyref];
                     echo "</td>";
                     echo "</tr>";   
                  }
               }
            }
         }
      }
      
      echo "</table>";
      
   }
   
   
   
   static function cronCheckdevices() {
      
      $pfConfigurationManagement = new PluginFusioninventoryConfigurationManagement();
      $a_list = $pfConfigurationManagement->find("`sha_referential` IS NOT NULL");
      foreach ($a_list as $id=>$data) {
         
         $a_currinv = $pfConfigurationManagement->generateCurrentInventory($id);
         $sha = sha1(exportArrayToDB($a_currinv));
         
         if ($sha == $data['sha_referential']) {
            $input = array();
            $input['id'] = $id;
            $input['sha_last'] = $sha;
            $input['sentnotification'] = 0;
            $input['conform'] = 1;
            $input['date'] = date('Y-m-d');
            $pfConfigurationManagement->update($input);
         } else if ($sha == $data['sha_last']) {
            // nothing to do, managed last time, only update date
            $input = array();
            $input['id'] = $id;
            $input['date'] = date('Y-m-d');
            $pfConfigurationManagement->update($input);
         } else {
            $input = array();
            $input['id'] = $id;
            $input['sha_last'] = $sha;
            $input['sentnotification'] = 0;
            $input['conform'] = 0;
            $input['date'] = date('Y-m-d');
            $pfConfigurationManagement->update($input);
         }
      }
      // Send emails
      
      
   }
}

?>
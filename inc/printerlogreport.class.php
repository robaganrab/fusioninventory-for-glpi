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
   Original Author of file: David DURIEUX
   Co-authors of file:
   Purpose of file:
   ----------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginFusinvsnmpPrinterLogReport extends CommonDBTM {

   function __construct() {
      global $CFG_GLPI;
      $this->table = "glpi_plugin_fusinvsnmp_printers";
      $CFG_GLPI['glpitablesitemtype']["PluginFusinvsnmpPrinterLogReport"] = $this->table;
   }
   
   
   function getSearchOptions() {
      global $LANG;

      $tab = array();
    
      $tab['common'] = $LANG['plugin_fusinvsnmp']['prt_history'][20];

      $tab[1]['table'] = $this->getTable();
      $tab[1]['field'] = 'id';      
      $tab[1]['linkfield'] = '';
      $tab[1]['name'] = 'id';
      
      $tab[2]['table'] = "glpi_printers";
      $tab[2]['field'] = 'name';
      $tab[2]['linkfield'] = 'printers_id';
      $tab[2]['name'] = $LANG['common'][16];
      $tab[2]['datatype'] = 'itemlink';
      $tab[2]['itemlink_type']  = 'Printer';
//      $tab[2]['forcegroupby'] = true;
      
      
//      $tab[1]['table'] = "glpi_printers";
//      $tab[1]['field'] = 'name';
//      $tab[1]['linkfield'] = 'printers_id';
//      $tab[1]['name'] = $LANG['common'][16];
//      $tab[1]['datatype'] = 'itemlink';
//      $tab[1]['itemlink_type']  = 'Printer';
//      $tab[1]['forcegroupby'] = true;
      
      $tab[24]['table'] = 'glpi_locations';
      $tab[24]['field'] = 'name';
      $tab[24]['linkfield'] = 'locations_id';
      $tab[24]['name'] = $LANG['common'][15];
      $tab[24]['datatype'] = 'itemlink';
      $tab[24]['itemlink_type'] = 'Location';

      $tab[19]['table'] = 'glpi_printertypes';
      $tab[19]['field'] = 'name';
      $tab[19]['linkfield'] = 'printertypes_id';
      $tab[19]['name'] = $LANG['common'][17];
      $tab[19]['datatype'] = 'itemlink';
      $tab[19]['itemlink_type'] = 'PrinterType';

//      $tab[2]['table'] = 'glpi_printermodels';
//      $tab[2]['field'] = 'name';
//      $tab[2]['linkfield'] = 'printermodels_id';
//      $tab[2]['name'] = $LANG['common'][22];
//      $tab[2]['datatype']='itemptype';
//
      $tab[18]['table'] = 'glpi_states';
      $tab[18]['field'] = 'name';
      $tab[18]['linkfield'] = 'states_id';
      $tab[18]['name'] = $LANG['state'][0];
      $tab[18]['datatype']='itemptype';

      $tab[20]['table'] = 'glpi_printers';
      $tab[20]['field'] = 'serial';
      $tab[20]['linkfield'] = 'printers_id';
      $tab[20]['name'] = $LANG['common'][19];

      $tab[23]['table'] = 'glpi_printers';
      $tab[23]['field'] = 'otherserial';
      $tab[23]['linkfield'] = 'printers_id';
      $tab[23]['name'] = $LANG['common'][20];

      $tab[21]['table'] = 'glpi_users';
      $tab[21]['field'] = 'name';
      $tab[21]['linkfield'] = 'users_id';
      $tab[21]['name'] = $LANG['common'][34];

      $tab[3]['table'] = 'glpi_manufacturers';
      $tab[3]['field'] = 'name';
      $tab[3]['linkfield'] = 'manufacturers_id';
      $tab[3]['name'] = $LANG['common'][5];

      $tab[5]['table'] = 'glpi_networkports';
      $tab[5]['field'] = 'ip';
      $tab[5]['linkfield'] = 'printers_id';
      $tab[5]['name'] = $LANG['networking'][14];

//      $tab[4]['table'] = 'glpi_infocoms';
//      $tab[4]['field'] = 'budget';
//      $tab[4]['linkfield'] = '';
//      $tab[4]['name'] = $LANG['financial'][87];

      $tab[6]['table'] = 'glpi_plugin_fusinvsnmp_printerlogs';
      $tab[6]['field'] = 'pages_total';
      $tab[6]['linkfield'] = 'id';
      $tab[6]['name'] = $LANG['plugin_fusinvsnmp']["mapping"][128];

      $tab[7]['table'] = 'glpi_plugin_fusinvsnmp_printerlogs';
      $tab[7]['field'] = 'pages_n_b';
      $tab[7]['linkfield'] = 'id';
      $tab[7]['name'] = $LANG['plugin_fusinvsnmp']["mapping"][129];

      $tab[8]['table'] = 'glpi_plugin_fusinvsnmp_printerlogs';
      $tab[8]['field'] = 'pages_color';
      $tab[8]['linkfield'] = 'id';
      $tab[8]['name'] = $LANG['plugin_fusinvsnmp']["mapping"][130];

      $tab[9]['table'] = 'glpi_plugin_fusinvsnmp_printerlogs';
      $tab[9]['field'] = 'pages_recto_verso';
      $tab[9]['linkfield'] = 'id';
      $tab[9]['name'] = $LANG['plugin_fusinvsnmp']["mapping"][154];

      $tab[10]['table'] = 'glpi_plugin_fusinvsnmp_printerlogs';
      $tab[10]['field'] = 'scanned';
      $tab[10]['linkfield'] = 'id';
      $tab[10]['name'] = $LANG['plugin_fusinvsnmp']["mapping"][155];

      $tab[11]['table'] = 'glpi_plugin_fusinvsnmp_printerlogs';
      $tab[11]['field'] = 'pages_total_print';
      $tab[11]['linkfield'] = 'id';
      $tab[11]['name'] = $LANG['plugin_fusinvsnmp']["mapping"][1423];

      $tab[12]['table'] = 'glpi_plugin_fusinvsnmp_printerlogs';
      $tab[12]['field'] = 'pages_n_b_print';
      $tab[12]['linkfield'] = 'id';
      $tab[12]['name'] = $LANG['plugin_fusinvsnmp']["mapping"][1424];

      $tab[13]['table'] = 'glpi_plugin_fusinvsnmp_printerlogs';
      $tab[13]['field'] = 'pages_color_print';
      $tab[13]['linkfield'] = 'id';
      $tab[13]['name'] = $LANG['plugin_fusinvsnmp']["mapping"][1425];

      $tab[14]['table'] = 'glpi_plugin_fusinvsnmp_printerlogs';
      $tab[14]['field'] = 'pages_total_copy';
      $tab[14]['linkfield'] = 'id';
      $tab[14]['name'] = $LANG['plugin_fusinvsnmp']["mapping"][1426];

      $tab[15]['table'] = 'glpi_plugin_fusinvsnmp_printerlogs';
      $tab[15]['field'] = 'pages_n_b_copy';
      $tab[15]['linkfield'] = 'id';
      $tab[15]['name'] = $LANG['plugin_fusinvsnmp']["mapping"][1427];

      $tab[16]['table'] = 'glpi_plugin_fusinvsnmp_printerlogs';
      $tab[16]['field'] = 'pages_color_copy';
      $tab[16]['linkfield'] = 'id';
      $tab[16]['name'] = $LANG['plugin_fusinvsnmp']["mapping"][1428];

      $tab[17]['table'] = 'glpi_plugin_fusinvsnmp_printerlogs';
      $tab[17]['field'] = 'pages_total_fax';
      $tab[17]['linkfield'] = 'id';
      $tab[17]['name'] = $LANG['plugin_fusinvsnmp']["mapping"][1429];

      return $tab;
   }




}

?>
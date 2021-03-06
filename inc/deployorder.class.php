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
   @author    Walid Nouh
   @co-author
   @copyright Copyright (c) 2010-2013 FusionInventory team
   @license   AGPL License 3.0 or (at your option) any later version
              http://www.gnu.org/licenses/agpl-3.0-standalone.html
   @link      http://www.fusioninventory.org/
   @link      http://forge.fusioninventory.org/projects/fusioninventory-for-glpi/
   @since     2010

   ------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/**
 * Manage packages orders
 **/
class PluginFusioninventoryDeployOrder extends CommonDBTM {

   const INSTALLATION_ORDER   = 0;
   const UNINSTALLATION_ORDER = 1;

   function __construct($order_type = NULL, $packages_id = NULL) {

      if (
            (!is_null($order_type) && is_numeric($order_type) )
         && (!is_null($packages_id) && is_numeric($packages_id) )
      ) {
         $this->getFromDBByQuery(
                     " WHERE plugin_fusioninventory_deploypackages_id = $packages_id
                        AND type = $order_type"
                  );
      }

   }

   /*
    * The 'Render' things should be renamed to something appropriate
    * ... don't know yet, so just leaving it as is -- kiniou
    */
   static function getRender($render) {
      if ($render == 'install') {
         return PluginFusioninventoryDeployOrder::INSTALLATION_ORDER;
      } else {
         return PluginFusioninventoryDeployOrder::UNINSTALLATION_ORDER;
      }
   }

   static function getOrderTypeLabel($order_type) {
      switch($order_type) {
         case PluginFusioninventoryDeployOrder::INSTALLATION_ORDER:
            return('install');
            break;
         case PluginFusioninventoryDeployOrder::UNINSTALLATION_ORDER:
            return('uninstall');
            break;
      }
   }

   /**
    * Create installation & uninstallation orders
    * @param packages_id the package ID
    * @return nothing
    */
   static function createOrders($packages_id) {
      $order = new PluginFusioninventoryDeployOrder();
      $tmp['create_date'] = date("Y-m-d H:i:s");
      $tmp['plugin_fusioninventory_deploypackages_id'] = $packages_id;
      foreach (array(PluginFusioninventoryDeployOrder::INSTALLATION_ORDER,
                     PluginFusioninventoryDeployOrder::UNINSTALLATION_ORDER) as $type) {
         $tmp['type'] = $type;
         $tmp['json'] = json_encode(array('jobs' => array(
            'checks' => array(),
            'associatedFiles' => array(),
            'actions' => array()
         ), 'associatedFiles' => array()));
         $order->add($tmp);
      }
   }



   static function getJson($orders_id) {
      $order = new self;
      $order->getFromDB($orders_id);
      if (!empty($order->fields['json'])) {
         return $order->fields['json'];
      } else {
         return FALSE;
      }
   }



   static function updateOrderJson($orders_id, $datas) {
      $order = new PluginFusioninventoryDeployOrder;
      $options = 0;
      if (version_compare(PHP_VERSION, '5.3.3') >= 0) {
         $options = $options | JSON_NUMERIC_CHECK;
      }
      if (version_compare(PHP_VERSION, '5.4.0') >= 0) {
         $options = $options | JSON_UNESCAPED_SLASHES;
      }
      return $order->update(array(
         'id'   => $orders_id,
         'json' => addcslashes(json_encode($datas, $options), "\\")
      ));
   }



   /**
    * TODO:
    * Create Orders from JSON format import/export
    */
   static function createOrdersFromJson($json) {

   }



   /**
    * Get order ID associated with a package, by type
    * @param packages_id the package ID
    * @param order_type can be self::INSTALLATION_ORDER or self::UNINSTALLATION_ORDER
    * @return 0 if no order found or the order's ID
    */
   static function getIdForPackage($packages_id, $order_type = self::INSTALLATION_ORDER) {
      $orders = getAllDatasFromTable('glpi_plugin_fusioninventory_deployorders',
                                     "`plugin_fusioninventory_deploypackages_id`='$packages_id'" .
                                     " AND `type`='$order_type'");

      if (empty($orders)) {
         return 0;
      } else {
         foreach ($orders as $order) {
            return $order['id'];
         }
      }
   }



   static function getOrderDetails($status = array(), $order_type = self::INSTALLATION_ORDER) {

      //get all jobstatus for this task
      $package_id = $status['items_id'];
      $results = getAllDatasFromTable('glpi_plugin_fusioninventory_deployorders',
                                  "`plugin_fusioninventory_deploypackages_id`='$package_id'" .
                                        " AND `type`='$order_type'");

      $orders =  array();
      if (!empty($results)) {
         $related_classes = array('PluginFusioninventoryDeployCheck'  => 'checks',
                                  'PluginFusioninventoryDeployFile'   => 'associatedFiles',
                                  'PluginFusioninventoryDeployAction' => 'actions');

         foreach ($related_classes as $class => $key) {
            foreach ($results as $result) {
               $tmp            = call_user_func(array($class, 'getForOrder'), $result['id']);
               if ($key == 'associatedFiles') {
                  $orders[$key] = $tmp;
               } else {
                  $orders[$key] = $tmp;
               }
            }
         }
      }

      //set uuid order to jobstatus[id]
      if (!empty($orders)) {
         $orders['uuid'] = $status['id'];
      }

      return $orders;
   }
}

?>

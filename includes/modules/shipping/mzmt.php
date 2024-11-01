<?php
/*
  $Id: mzmt.php,v 1.100 2004-11-09 Josh Dechant Exp $

  Copyright (c) 2004 Josh Dechant

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Protions Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

  class mzmt {
    var $code, $title, $description, $icon, $enabled, $num_zones, $num_tables, $delivery_geozone, $geozone_mode, $order_total;
  
    function mzmt() {
      global $order;

      $this->code = 'mzmt';
      $this->title = MODULE_SHIPPING_MZMT_TEXT_TITLE;
      $this->description = MODULE_SHIPPING_MZMT_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_SHIPPING_MZMT_SORT_ORDER;
      $this->tax_class = MODULE_SHIPPING_MZMT_TAX_CLASS;
      $this->enabled = ((MODULE_SHIPPING_MZMT_STATUS == 'True') ? true : false);

      if(!defined('MODULE_SHIPPING_MZMT_NUMBER_GEOZONES'))
      	define('MODULE_SHIPPING_MZMT_NUMBER_GEOZONES','6');
      	
      if(!defined('MODULE_SHIPPING_MZMT_NUMBER_TABLES'))
      	define('MODULE_SHIPPING_MZMT_NUMBER_TABLES','3');
      	
      $this->num_geozones = MODULE_SHIPPING_MZMT_NUMBER_GEOZONES;
      $this->num_tables = MODULE_SHIPPING_MZMT_NUMBER_TABLES;

      
      if ($this->enabled == true) {
        $this->enabled = false;
        for ($n=1; $n<=$this->num_geozones; $n++) {
          if ( ((int)constant('MODULE_SHIPPING_MZMT_GEOZONE_' . $n . '_ID') > 0) && ((int)constant('MODULE_SHIPPING_MZMT_GEOZONE_' . $n . '_ID') == $this->getGeoZoneID($order->delivery['country']['id'], $order->delivery['zone_id'])) ) {
            $this->enabled = true;
            $this->delivery_geozone = $n;
            break;
          } elseif ( ((int)constant('MODULE_SHIPPING_MZMT_GEOZONE_' . $n . '_ID') == 0) && ($n == (int)$this->num_geozones) ) {
            $this->enabled = true;
            $this->delivery_geozone = $n;
            break;
          }
        }
      }
    }

// class methods
   function quote($method = '') {
   
     global $order, $shipping_weight, $shipping_num_boxes;
     	  $combined_quote_weight = ($shipping_num_boxes * $shipping_weight);
      $this->quotes = array('id' => $this->code,
                            'module' => constant('MODULE_SHIPPING_MZMT_GEOZONE_' . $this->delivery_geozone . '_TEXT_TITLE') . ' (' . $combined_quote_weight . ' lbs)',
                            'methods' => array());

      $this->determineTableMethod(constant('MODULE_SHIPPING_MZMT_GEOZONE_' . $this->delivery_geozone . '_MODE'));  
      
      if ($method) {
        $j = substr($method, 5);
        
        $shipping = $this->determineShipping(preg_split("/[:,]/" , constant('MODULE_SHIPPING_MZMT_GEOZONE_' . $this->delivery_geozone . '_TABLE_' . $j)));
        
        $this->quotes['methods'][] = array('id' => 'table' . $j,
                                           'title' => constant('MODULE_SHIPPING_MZMT_GEOZONE_' . $this->delivery_geozone . '_TABLE_' . $j . '_TEXT_WAY'),
                                           'cost' => $shipping + constant('MODULE_SHIPPING_MZMT_GEOZONE_' . $this->delivery_geozone . '_HANDLING'));
      } else {
        for ($j=1; $j<=$this->num_tables; $j++) {
          if (!tep_not_null(constant('MODULE_SHIPPING_MZMT_GEOZONE_' . $this->delivery_geozone . '_TABLE_' . $j))) continue;
          $shipping = $this->determineShipping(preg_split("/[:,]/" , constant('MODULE_SHIPPING_MZMT_GEOZONE_' . $this->delivery_geozone . '_TABLE_' . $j)));
          $this->quotes['methods'][] = array('id' => 'table' . $j,
                                             'title' => constant('MODULE_SHIPPING_MZMT_GEOZONE_' . $this->delivery_geozone . '_TABLE_' . $j . '_TEXT_WAY'),
                                             'cost' => $shipping + constant('MODULE_SHIPPING_MZMT_GEOZONE_' . $this->delivery_geozone . '_HANDLING'));
        }
      }

      if ($this->tax_class > 0) {
        $this->quotes['tax'] = tep_get_tax_rate($this->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
      }

      if (tep_not_null(constant('MODULE_SHIPPING_MZMT_GEOZONE_' . $this->delivery_geozone . '_ICON'))) $this->quotes['icon'] = tep_image(DIR_WS_ICONS . constant('MODULE_SHIPPING_MZMT_GEOZONE_' . $this->delivery_geozone . '_ICON'), $this->title);

      return $this->quotes;
    }  

    function check() {
      if (!isset($this->_check)) {
        $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_SHIPPING_MZMT_STATUS'");
        $this->_check = mysql_num_rows($check_query);
      }
      return $this->_check;
    }

    function install() {
    
     // install us 48
    tep_db_query("INSERT INTO `geo_zones` VALUES ('', 'Shp: United States: 48', 'United States:  Continental 48 states', NULL, now());");
	tep_db_query("INSERT INTO `zones_to_geo_zones` VALUES ('', 223, 1, last_insert_id(), NULL, now()),
                                        ('', 223, 4, last_insert_id(), NULL, now()),
                                        ('', 223, 5, last_insert_id(), NULL, now()),
                                        ('', 223, 12, last_insert_id(), NULL, now()),
                                        ('', 223, 13, last_insert_id(), NULL, now()),
                                        ('', 223, 14, last_insert_id(), NULL, now()),
                                        ('', 223, 15, last_insert_id(), NULL, now()),
                                        ('', 223, 16, last_insert_id(), NULL, now()),
                                        ('', 223, 18, last_insert_id(), NULL, now()),
                                        ('', 223, 19, last_insert_id(), NULL, now()),
                                        ('', 223, 22, last_insert_id(), NULL, now()),
                                        ('', 223, 23, last_insert_id(), NULL, now()),
                                        ('', 223, 24, last_insert_id(), NULL, now()),
                                        ('', 223, 25, last_insert_id(), NULL, now()),
                                        ('', 223, 26, last_insert_id(), NULL, now()),
                                        ('', 223, 27, last_insert_id(), NULL, now()),
                                        ('', 223, 28, last_insert_id(), NULL, now()),
                                        ('', 223, 29, last_insert_id(), NULL, now()),
                                        ('', 223, 31, last_insert_id(), NULL, now()),
                                        ('', 223, 32, last_insert_id(), NULL, now()),
                                        ('', 223, 33, last_insert_id(), NULL, now()),
                                        ('', 223, 34, last_insert_id(), NULL, now()),
                                        ('', 223, 35, last_insert_id(), NULL, now()),
                                        ('', 223, 36, last_insert_id(), NULL, now()),
                                        ('', 223, 37, last_insert_id(), NULL, now()),
                                        ('', 223, 38, last_insert_id(), NULL, now()),
                                        ('', 223, 39, last_insert_id(), NULL, now()),
                                        ('', 223, 40, last_insert_id(), NULL, now()),
                                        ('', 223, 41, last_insert_id(), NULL, now()),
                                        ('', 223, 42, last_insert_id(), NULL, now()),
                                        ('', 223, 43, last_insert_id(), NULL, now()),
                                        ('', 223, 44, last_insert_id(), NULL, now()),
                                        ('', 223, 45, last_insert_id(), NULL, now()),
                                        ('', 223, 47, last_insert_id(), NULL, now()),
                                        ('', 223, 48, last_insert_id(), NULL, now()),
                                        ('', 223, 49, last_insert_id(), NULL, now()),
                                        ('', 223, 51, last_insert_id(), NULL, now()),
                                        ('', 223, 53, last_insert_id(), NULL, now()),
                                        ('', 223, 54, last_insert_id(), NULL, now()),
                                        ('', 223, 55, last_insert_id(), NULL, now()),
                                        ('', 223, 56, last_insert_id(), NULL, now()),
                                        ('', 223, 57, last_insert_id(), NULL, now()),
                                        ('', 223, 58, last_insert_id(), NULL, now()),
                                        ('', 223, 59, last_insert_id(), NULL, now()),
                                        ('', 223, 61, last_insert_id(), NULL, now()),
                                        ('', 223, 62, last_insert_id(), NULL, now()),
                                        ('', 223, 63, last_insert_id(), NULL, now()),
                                        ('', 223, 64, last_insert_id(), NULL, now()),
                                        ('', 223, 65, last_insert_id(), NULL, now());");
    
    
	// EU ALL
	
	tep_db_query("INSERT INTO `geo_zones` VALUES ('', 'Shp: European Union: All', 'Entire European Union', NULL, now());");
	tep_db_query("INSERT INTO `zones_to_geo_zones` VALUES ('', 14, 0, last_insert_id(), NULL, now()),
                                        ('', 21, 0, last_insert_id(), NULL, now()),
                                        ('', 55, 0, last_insert_id(), NULL, now()),
                                        ('', 56, 0, last_insert_id(), NULL, now()),
                                        ('', 57, 0, last_insert_id(), NULL, now()),
                                        ('', 67, 0, last_insert_id(), NULL, now()),
                                        ('', 72, 0, last_insert_id(), NULL, now()),
                                        ('', 73, 0, last_insert_id(), NULL, now()),
                                        ('', 81, 0, last_insert_id(), NULL, now()),
                                        ('', 84, 0, last_insert_id(), NULL, now()),
                                        ('', 97, 0, last_insert_id(), NULL, now()),
                                        ('', 103, 0, last_insert_id(), NULL, now()),
                                        ('', 105, 0, last_insert_id(), NULL, now()),
                                        ('', 117, 0, last_insert_id(), NULL, now()),
                                        ('', 123, 0, last_insert_id(), NULL, now()),
                                        ('', 124, 0, last_insert_id(), NULL, now()),
                                        ('', 132, 0, last_insert_id(), NULL, now()),
                                        ('', 170, 0, last_insert_id(), NULL, now()),
                                        ('', 171, 0, last_insert_id(), NULL, now()),
                                        ('', 189, 0, last_insert_id(), NULL, now()),
                                        ('', 190, 0, last_insert_id(), NULL, now()),
                                        ('', 195, 0, last_insert_id(), NULL, now()),
                                        ('', 203, 0, last_insert_id(), NULL, now()),
                                        ('', 150, 0, last_insert_id(), NULL, now()),
                                        ('', 222, 0, last_insert_id(), NULL, now());");
	
    
    // us states AK & HI
    
	tep_db_query("INSERT INTO `geo_zones` VALUES ('', 'Shp: United States: AK & HI', 'United States:  Alaska and Hawaii', NULL, now());");
	tep_db_query("INSERT INTO `zones_to_geo_zones` VALUES ('', 223, 2, last_insert_id(), NULL, now()),
	                                        ('', 223, 21, last_insert_id(), NULL, now());");
	
	
	// APO & FPO
	
	tep_db_query("INSERT INTO `geo_zones` VALUES ('', 'Shp: United States: APO/FPO', 'United States:  APO and FPO only', NULL, now());");
	tep_db_query("INSERT INTO `zones_to_geo_zones` VALUES ('', 223, 6, last_insert_id(), NULL, now()),
                                        ('', 223, 7, last_insert_id(), NULL, now()),
                                        ('', 223, 8, last_insert_id(), NULL, now()),
                                        ('', 223, 9, last_insert_id(), NULL, now()),
                                        ('', 223, 10, last_insert_id(), NULL, now()),
                                        ('', 223, 11, last_insert_id(), NULL, now());");
	
	
    
    tep_db_query("INSERT INTO ".TABLE_CONFIGURATION_GROUP." VALUES ('', 'MultiGeoZone MultiTable Shipping', 'The options which configure the MultiGeoZone MultiTable Shipping Module', '1', '1');");
    
     tep_db_query("UPDATE ".TABLE_CONFIGURATION_GROUP." SET sort_order = last_insert_id() WHERE configuration_group_id = last_insert_id();");
    
    tep_db_query("INSERT INTO ".TABLE_CONFIGURATION." VALUES ('', 'Number of Geo Zones', 'MODULE_SHIPPING_MZMT_NUMBER_GEOZONES', '6', 'The number of shipping geo zones.', last_insert_id(), '0', NULL, now(), NULL, NULL)");
    tep_db_query("INSERT INTO ".TABLE_CONFIGURATION." VALUES ('', 'Number of Tables per Geo Zone', 'MODULE_SHIPPING_MZMT_NUMBER_TABLES', '3', 'The number of shipping tables per geo zone.', last_insert_id(), '0', NULL, now(), NULL, NULL)");
    
    
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Enable MultiRegion MultiTable Method', 'MODULE_SHIPPING_MZMT_STATUS', 'True', 'Do you want to offer multi-region multi-table rate shipping?', '6', '0', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Tax Class', 'MODULE_SHIPPING_MZMT_TAX_CLASS', '0', 'Use the following tax class on the shipping fee.', '6', '0', 'tep_get_tax_class_title', 'tep_cfg_pull_down_tax_classes(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_SHIPPING_MZMT_SORT_ORDER', '0', 'Sort order of display.', '6', '0', now())");

      for ($n=1; $n<=$this->num_geozones; $n++) {
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('<hr />Geo Zone $n', 'MODULE_SHIPPING_MZMT_GEOZONE_{$n}_ID', '', 'Enable this for the following geo zone.', '6', '0', 'tep_get_zone_class_title', '_cfg_pull_down_geozones(', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Geo Zone $n Table Method', 'MODULE_SHIPPING_MZMT_GEOZONE_{$n}_MODE', 'weight', 'The shipping cost is based on the total weight, total price, or total count of the items ordered.', '6', '0', 'tep_cfg_select_option(array(\'weight\', \'price\', \'count\'), ', now())");
        
        for ($j=1; $j<=$this->num_tables; $j++) {
          tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Geo Zone $n Shipping Table $j', 'MODULE_SHIPPING_MZMT_GEOZONE_{$n}_TABLE_{$j}', '', 'Shipping table $j for this geo zone', '6', '0', now())");
        }

        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Geo Zone $n Handling Fee', 'MODULE_SHIPPING_MZMT_GEOZONE_{$n}_HANDLING', '0', 'Handling Fee for this shipping geo zone', '6', '0', now())");
      }
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
      tep_db_query("delete from " . TABLE_CONFIGURATION_GROUP . " where configuration_group_title ='MultiGeoZone MultiTable Shipping'");
      
      
      // clear zones
      
     // us 48 
     $this->remove_instaled_zones('Shp: United States: 48');
     $this->remove_instaled_zones('Shp: European Union: All');
     $this->remove_instaled_zones('Shp: United States: AK & HI');
     $this->remove_instaled_zones('Shp: United States: APO/FPO');
     
      
      //ends here
      
    }

   function remove_instaled_zones($zone_name){
   
   	 $zone_query = tep_db_query("select geo_zone_id from geo_zones where geo_zone_name='".$zone_name."'");
     $zone_info = tep_db_fetch_array($zone_query);
     if($zone_info['geo_zone_id']!="" && $zone_info['geo_zone_id']!="0"){
     	tep_db_query("delete from geo_zones where geo_zone_id=".$zone_info['geo_zone_id']);
     	tep_db_query("delete from zones_to_geo_zones where geo_zone_id=".$zone_info['geo_zone_id']);
     
     }
   
   }  
    
    function keys() {
      $keys = array('MODULE_SHIPPING_MZMT_STATUS', 'MODULE_SHIPPING_MZMT_TAX_CLASS', 'MODULE_SHIPPING_MZMT_SORT_ORDER','MODULE_SHIPPING_MZMT_NUMBER_GEOZONES','MODULE_SHIPPING_MZMT_NUMBER_TABLES');

      for ($n=1; $n<=$this->num_geozones; $n++) {
        $keys[] = 'MODULE_SHIPPING_MZMT_GEOZONE_' . $n . '_ID';
        $keys[] = 'MODULE_SHIPPING_MZMT_GEOZONE_' . $n . '_MODE';
        $keys[] = 'MODULE_SHIPPING_MZMT_GEOZONE_' . $n . '_HANDLING';

        for ($j=1; $j<=$this->num_tables; $j++) {
          $keys[] = 'MODULE_SHIPPING_MZMT_GEOZONE_' . $n . '_TABLE_' . $j;
        }
      }

      return $keys;
    }

    function determineTableMethod($geozone_mode) {
      global $total_count, $shipping_weight;

      $this->geozone_mode = $geozone_mode;

      if ($this->geozone_mode == 'price') {
        $this->order_total = $_SESSION['cart']->show_total();
      } elseif ($this->geozone_mode == 'count') {
        $this->order_total = $total_count;
      } else {
        $this->order_total = $shipping_weight;
      }

      return true;    
    }

    function determineShipping($table_cost) {
      global $shipping_num_boxes;

      for ($i=0, $n=sizeof($table_cost); $i<$n; $i+=2) {
        if ($this->order_total >= $table_cost[$i]) {
          $shipping_factor = $table_cost[$i+1];
        }
      }

      if (substr_count($shipping_factor, '%') > 0) {
        $shipping = ((($this->order_total*10)/10)*((str_replace('%', '', $shipping_factor))/100));
      } else {
        $shipping = str_replace('$', '', $shipping_factor);
      }

      if ($this->geozone_mode == 'weight') {
        $shipping = $shipping * $shipping_num_boxes;
      }

      return $shipping;
    }

    function getGeoZoneID($country_id, $zone_id) {
      // First, check for a Geo Zone that explicity includes the country & specific zone (useful for splitting countries with zones up)
      $zone_query = tep_db_query("select gz.geo_zone_id from " . TABLE_GEO_ZONES . " gz left join " . TABLE_ZONES_TO_GEO_ZONES . " ztgz on (gz.geo_zone_id = ztgz.geo_zone_id) where ztgz.zone_country_id = '" . (int)$country_id . "' and ztgz.zone_id = '" . (int)$zone_id . "' and LOWER(gz.geo_zone_name) like 'shp%'");

      if (mysql_num_rows($zone_query)) {
        $zone = mysql_fetch_assoc($zone_query);
        return $zone['geo_zone_id'];
      } else {
        // No luck…  Now check for a Geo Zone for the country and "All Zones" of the country.
        $zone_query = tep_db_query("select gz.geo_zone_id from " . TABLE_GEO_ZONES . " gz left join " . TABLE_ZONES_TO_GEO_ZONES . " ztgz on (gz.geo_zone_id = ztgz.geo_zone_id) where ztgz.zone_country_id = '" . (int)$country_id . "' and (ztgz.zone_id = '0' or ztgz.zone_id is NULL) and LOWER(gz.geo_zone_name) like 'shp%'");

        if (mysql_num_rows($zone_query)) {
          $zone = mysql_fetch_assoc($zone_query);
          return $zone['geo_zone_id'];
        } else {
          return false;
        }
      }
    }

  }

  function _cfg_pull_down_geozones($zone_class_id, $key = '') {
    $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');

    $zone_class_array = array(array('id' => '0', 'text' => 'Rest of the World'));
    $zone_class_query = tep_db_query("select geo_zone_id, geo_zone_name from " . TABLE_GEO_ZONES . " where LOWER(geo_zone_name) like 'shp%' order by geo_zone_name");

    while ($zone_class = mysql_fetch_assoc($zone_class_query)) {
      $zone_class_array[] = array('id' => $zone_class['geo_zone_id'],
                                  'text' => $zone_class['geo_zone_name']);
    }

    return tep_draw_pull_down_menu($name, $zone_class_array, $zone_class_id);
  }
?>

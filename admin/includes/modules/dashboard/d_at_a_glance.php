<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  class d_at_a_glance {
    var $code = 'd_at_a_glance';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function d_at_a_glance() {
      $this->title = MODULE_ADMIN_DASHBOARD_ATA_GLANCE_TITLE;
      $this->description = MODULE_ADMIN_DASHBOARD_ATA_GLANCE_DESCRIPTION;

      if ( defined('MODULE_ADMIN_DASHBOARD_LATEST_NEWS_STATUS') ) {
        $this->sort_order = MODULE_ADMIN_DASHBOARD_ATA_GLANCE_SORT_ORDER;
        $this->enabled = (MODULE_ADMIN_DASHBOARD_ATA_GLANCE_STATUS == 'True');
      }
    }

	function newCustomers() {
		$weekStart = 0;
		$date = date('Y-m-d');
		$timestamp = strtotime($date);
		$dayOfWeek = date('N', $timestamp);
		$startDate = mktime(0,0,0, date('n', $timestamp), date('j', $timestamp) - $dayOfWeek + $weekStart, date('Y', $timestamp));
		$endDate = mktime(0,0,0, date('n', $timestamp), date('j', $timestamp) - $dayOfWeek + 6 + $weekStart, date('Y', $timestamp));
		$st = date('Y-m-d', $startDate);
		$en = date('Y-m-d', $endDate);
        //$cust_query_raw = tep_db_query("select customers_id from " . TABLE_CUSTOMERS . " where customers_created between '$st' AND '$en'");
        $cust_query_raw = tep_db_query("select customers_id from " . TABLE_CUSTOMERS . "");
		$rows = tep_db_num_rows($cust_query_raw);	
		return $rows;
	}		
	
	function newOrders() {
		$weekStart = 0;
		$date = date('Y-m-d');
		$timestamp = strtotime($date);
		$dayOfWeek = date('N', $timestamp);
		$startDate = mktime(0,0,0, date('n', $timestamp), date('j', $timestamp) - $dayOfWeek + $weekStart, date('Y', $timestamp));
		$endDate = mktime(0,0,0, date('n', $timestamp), date('j', $timestamp) - $dayOfWeek + 6 + $weekStart, date('Y', $timestamp));
		$st = date('Y-m-d', $startDate);
		$en = date('Y-m-d', $endDate);
        $cust_query_raw = tep_db_query("select orders_id from " . TABLE_ORDERS . " where date_purchased between '$st' AND '$en'");
        $cust_query_raw = tep_db_query("select orders_id from " . TABLE_ORDERS . "");
		$rows = tep_db_num_rows($cust_query_raw);	
		return $rows;
	}		
	
	function totalSales() {
		$weekStart = 0;
		$date = date('Y-m-d');
		$timestamp = strtotime($date);
		$dayOfWeek = date('N', $timestamp);
		$startDate = mktime(0,0,0, date('n', $timestamp), date('j', $timestamp) - $dayOfWeek + $weekStart, date('Y', $timestamp));
		$endDate = mktime(0,0,0, date('n', $timestamp), date('j', $timestamp) - $dayOfWeek + 6 + $weekStart, date('Y', $timestamp));
		$st = date('Y-m-d', $startDate);
		$en = date('Y-m-d', $endDate);
	    //echo ("select sum(ot.value) as total from " . TABLE_ORDERS . " o, ". TABLE_ORDERS_TOTAL ." ot where o.orders_id = ot.orders_id  and ot.class = 'ot_total' and  o.date_purchased between '$st' AND '$en' ");
       // $cust_query_raw = tep_db_query("select sum(ot.value) as total from " . TABLE_ORDERS . " o, ". TABLE_ORDERS_TOTAL ." ot where o.orders_id = ot.orders_id  and ot.class = 'ot_total' and  o.date_purchased between '$st' AND '$en' ");
        $cust_query_raw = tep_db_query("select sum(ot.value) as total from " . TABLE_ORDERS . " o, ". TABLE_ORDERS_TOTAL ." ot where o.orders_id = ot.orders_id  and ot.class = 'ot_total'");
		$rs = tep_db_fetch_array($cust_query_raw);	
		return number_format($rs['total'],2,'.','');
	}		
	
	function newReviews() { 
		$weekStart = 0;
		$date = date('Y-m-d');
		$timestamp = strtotime($date);
		$dayOfWeek = date('N', $timestamp);
		$startDate = mktime(0,0,0, date('n', $timestamp), date('j', $timestamp) - $dayOfWeek + $weekStart, date('Y', $timestamp));
		$endDate = mktime(0,0,0, date('n', $timestamp), date('j', $timestamp) - $dayOfWeek + 6 + $weekStart, date('Y', $timestamp));
		$st = date('Y-m-d', $startDate);
		$en = date('Y-m-d', $endDate);
        //$cust_query_raw = tep_db_query("select reviews_id from " . TABLE_REVIEWS . " where date_added between '$st' AND '$en'");
        $cust_query_raw = tep_db_query("select reviews_id from " . TABLE_REVIEWS . "");
		$rows = tep_db_num_rows($cust_query_raw);	
		return $rows;		
	}	
		
    function getOutput() {
      if (!class_exists('lastRSS')) {
        include(DIR_WS_CLASSES . 'rss.php');
      }

      $rss = new lastRSS;
      $rss->items_limit = 5;
      $rss->cache_dir = DIR_FS_CACHE;
      $rss->cache_time = 86400;
      $feed = $rss->get('http://www.wponlinestore.com/feed/rss/');
	  $whos_online_query = tep_db_query("select customer_id, full_name, ip_address, time_entry, time_last_click, last_page_url, session_id from " . TABLE_WHOS_ONLINE);
      $rows = tep_db_num_rows($whos_online_query);
      $output = '<div class="round_box_head">'.MODULE_ADMIN_DASHBOARD_ATA_GLANCE_TITLE.'</div>
	              <div class="round_box_mid"><table border="0" width="95%" cellspacing="1" cellpadding="1">';

          $output .= '  <tr>' .
                     '    <td class="inside">Currently there are '.$rows.' customers online</td>'.
                     '  </tr>';

      $output .= '</table></div>
	              <div class="round_box_head">'.MODULE_ADMIN_DASHBOARD_THIS_WEEK_ATA_GLANCE_TITLE.'</div>
                  <div class="round_box_mid"><table border="0" width="95%" cellspacing="1" cellpadding="1">
                  <tr>	
				   <td class="inside">New Customers </td>
				   <td class="inside">'. $this->newCustomers() .'</td>
				  </tr>                   
				  <tr>	
				   <td class="inside">New Orders</td>
				   <td class="inside">'. $this->newOrders() .'</td>
				  </tr>                   
				  <tr>	
				   <td class="inside">Total Sales</td>
				   <td class="inside">' .$this->totalSales(). '</td>
				  </tr>                   
     			  <tr>	
				   <td class="inside">New Reviews</td>
				   <td class="inside">' .$this->newReviews(). '</td>
				  </tr> 
				  </table></div>
	              <div class="round_box_head">'.MODULE_ADMIN_DASHBOARD_MOST_VIEWED_ATA_GLANCE_TITLE.'</div>
                  <div class="round_box_mid"><table border="0" width="95%" cellspacing="5" cellpadding="5">';
	  $rows = 0;
	  $products_query_raw = "select p.products_id, pd.products_name, pd.products_viewed, l.name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_LANGUAGES . " l where p.products_id = pd.products_id and l.languages_id = pd.language_id order by pd.products_viewed DESC";
	  $products_split = new splitPageResults($HTTP_GET_VARS['pages'], 5, $products_query_raw, $products_query_numrows);
	  $products_query = tep_db_query($products_query_raw);
	  while ($products = tep_db_fetch_array($products_query)) {
		$rows++; 

            $output .= ' <tr>
                <td class="inside" valign="top">'.$rows.'.</td>
                <td class="inside"><a href="' . tep_href_link(FILENAME_CATEGORIES, 'action=new_product_preview&read=only&pID=' . $products['products_id'] . '&origin=' . FILENAME_STATS_PRODUCTS_VIEWED . '?pages=' . $HTTP_GET_VARS['pages'], 'NONSSL') . '">' . $products['products_name'] . '</a></td>
                <td class="">'. $products['products_viewed'] .'</td>
              </tr>';

     }
	  $output .= '<tr><td colspan="3" align="right"><a class="link" href="admin.php?page=WP_online_store&submenu=stats_products_viewed" target="_blank">view all....</a></td></tr>
	              </table></div></div>';
		
      return $output;
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_ADMIN_DASHBOARD_ATA_GLANCE_STATUS');
    }
	


    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable At a Glance Module', 'MODULE_ADMIN_DASHBOARD_ATA_GLANCE_STATUS', 'True', 'Do you want to show the at a glance on the dashboard?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_ADMIN_DASHBOARD_ATA_GLANCE_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_ADMIN_DASHBOARD_ATA_GLANCE_STATUS', 'MODULE_ADMIN_DASHBOARD_ATA_GLANCE_SORT_ORDER');
    }
  }
?>

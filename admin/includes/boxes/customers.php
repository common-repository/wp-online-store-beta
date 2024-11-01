<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  $cl_box_groups[] = array(
    'heading' => BOX_HEADING_CUSTOMERS,
    'apps' => array(
      array(
        'code' => FILENAME_CUSTOMERS,
        'title' => BOX_CUSTOMERS_CUSTOMERS,
        'link' => tep_href_link(FILENAME_CUSTOMERS)
      ),
      array(
        'code' => FILENAME_ORDERS,
        'title' => BOX_CUSTOMERS_ORDERS,
        'link' => tep_href_link(FILENAME_ORDERS)
      ),
      array(
        'code' => FILENAME_ORDERS_STATUS,
        'title' => BOX_LOCALIZATION_ORDERS_STATUS,
        'link' => tep_href_link(FILENAME_ORDERS_STATUS)
      ),
       array(
        'code' => FILENAME_STATS_PRODUCTS_VIEWED,
        'title' => BOX_REPORTS_PRODUCTS_VIEWED,
        'link' => tep_href_link(FILENAME_STATS_PRODUCTS_VIEWED)
      ),
      array(
        'code' => FILENAME_STATS_PRODUCTS_PURCHASED,
        'title' => BOX_REPORTS_PRODUCTS_PURCHASED,
        'link' => tep_href_link(FILENAME_STATS_PRODUCTS_PURCHASED)
      ),
      array(
        'code' => FILENAME_STATS_CUSTOMERS,
        'title' => BOX_REPORTS_ORDERS_TOTAL,
        'link' => tep_href_link(FILENAME_STATS_CUSTOMERS)
      ),
      array(
        'code' => FILENAME_WHOS_ONLINE,
        'title' => BOX_TOOLS_WHOS_ONLINE,
        'link' => tep_href_link(FILENAME_WHOS_ONLINE)
      )
    )
  );
?>

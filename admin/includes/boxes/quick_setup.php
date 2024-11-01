<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  $cl_box_groups[] = array(
    'heading' => BOX_HEADING_QUICK_SETUP,
    'apps' => array(
  	)
  );		

  $configuration_groups_query = tep_db_query("select configuration_group_id as cgID, configuration_group_title as cgTitle from " . TABLE_CONFIGURATION_GROUP . " where visible = '1' and configuration_group_id=1 ");
  while ($configuration_groups = tep_db_fetch_array($configuration_groups_query)) {
    $cl_box_groups[sizeof($cl_box_groups)-1]['apps'][] = array(
      'code' => FILENAME_CONFIGURATION,
      'title' => $configuration_groups['cgTitle'],
      'link' => tep_href_link(FILENAME_CONFIGURATION, 'gID=' . $configuration_groups['cgID'])
    );
  }
  
    $cl_box_groups[sizeof($cl_box_groups)-1]['apps'][] = array(
      'code' => FILENAME_CATEGORIES,
      'title' => BOX_CATALOG_CATEGORIES_PRODUCTS,
      'link' => tep_href_link(FILENAME_CATEGORIES)
    );
    
    $cl_box_groups[sizeof($cl_box_groups)-1]['apps'][] = array(
      'code' => FILENAME_PRODUCTS_ATTRIBUTES,
      'title' => BOX_CATALOG_CATEGORIES_PRODUCTS_ATTRIBUTES,
      'link' => tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES)
    );
   
    foreach ($cfgModules->getAll() as $m) {
	    if($m['code']=='payment' || $m['code']=='shipping'){
		    $cl_box_groups[sizeof($cl_box_groups)-1]['apps'][] = array('code' => FILENAME_MODULES,
		                                                               'title' => $m['title'],
		                                                               'link' => tep_href_link(FILENAME_MODULES, 'set=' . $m['code']));
	    }
   }
  
   $cl_box_groups[sizeof($cl_box_groups)-1]['apps'][] = array(
      'code' => FILENAME_FORMAT,
      'title' => BOX_FORMAT,
      'link' => tep_href_link(FILENAME_FORMAT)
    );
  /*array(
        'code' => FILENAME_CUSTOMERS,
        'title' => BOX_CUSTOMERS_CUSTOMERS,
        'link' => tep_href_link(FILENAME_CUSTOMERS)
      ),
      array(
        'code' => FILENAME_ORDERS,
        'title' => BOX_CUSTOMERS_ORDERS,
        'link' => tep_href_link(FILENAME_ORDERS)
      )
    )
  );*/
?>

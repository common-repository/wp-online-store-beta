<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  $cl_box_groups[] = array(
    'heading' => BOX_HEADING_ADVANCED,
    'apps' => array(
     /*
      array(
        'code' => FILENAME_STORE_LOGO,
        'title' => BOX_CONFIGURATION_STORE_LOGO,
        'link' => tep_href_link(FILENAME_STORE_LOGO)
      )*/
    )
  );

  $configuration_groups_query = tep_db_query("select configuration_group_id as cgID, configuration_group_title as cgTitle from " . TABLE_CONFIGURATION_GROUP . " where visible = '1' and configuration_group_id  in (10,11,14,15)  order by sort_order");
  while ($configuration_groups = tep_db_fetch_array($configuration_groups_query)) {
    $cl_box_groups[sizeof($cl_box_groups)-1]['apps'][] = array(
      'code' => FILENAME_CONFIGURATION,
      'title' => $configuration_groups['cgTitle'],
      'link' => tep_href_link(FILENAME_CONFIGURATION, 'gID=' . $configuration_groups['cgID'])
    );
  }
  
  foreach ($cfgModules->getAll() as $m) {
  	if($m['title']=="Action Recorder"){
    	$cl_box_groups[sizeof($cl_box_groups)-1]['apps'][] = array('code' => FILENAME_MODULES,
                                                               'title' => $m['title'],
                                                               'link' => tep_href_link(FILENAME_MODULES, 'set=' . $m['code']));
  	}
  }
  
  $cl_box_groups[sizeof($cl_box_groups)-1]['apps'][] = array(
        'code' => FILENAME_CACHE,
        'title' => BOX_TOOLS_CACHE,
        'link' => tep_href_link(FILENAME_CACHE)
      );

      
    $cl_box_groups[sizeof($cl_box_groups)-1]['apps'][] =  array(
        'code' => FILENAME_ACTION_RECORDER,
        'title' => BOX_TOOLS_ACTION_RECORDER,
        'link' => tep_href_link(FILENAME_ACTION_RECORDER)
      );
      
      
         $cl_box_groups[sizeof($cl_box_groups)-1]['apps'][] =array(
        'code' => FILENAME_SEC_DIR_PERMISSIONS,
        'title' => BOX_TOOLS_SEC_DIR_PERMISSIONS,
        'link' => tep_href_link(FILENAME_SEC_DIR_PERMISSIONS)
      );
       $cl_box_groups[sizeof($cl_box_groups)-1]['apps'][] = array(
        'code' => FILENAME_SERVER_INFO,
        'title' => BOX_TOOLS_SERVER_INFO,
        'link' => tep_href_link(FILENAME_SERVER_INFO)
      );
      /*
       $cl_box_groups[sizeof($cl_box_groups)-1]['apps'][] = array(
        'code' => FILENAME_VERSION_CHECK,
        'title' => BOX_TOOLS_VERSION_CHECK,
        'link' => tep_href_link(FILENAME_VERSION_CHECK)
      );*/
    
?>

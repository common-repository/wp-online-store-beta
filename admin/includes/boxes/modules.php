<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  $cl_box_groups[] = array(
    'heading' => BOX_HEADING_MODULES,
    'apps' => array()
  );

  foreach ($cfgModules->getAll() as $m) {
  	if($m['title']!="Action Recorder"){
    	$cl_box_groups[sizeof($cl_box_groups)-1]['apps'][] = array('code' => FILENAME_MODULES,
                                                               'title' => $m['title'],
                                                               'link' => tep_href_link(FILENAME_MODULES, 'set=' . $m['code']));
  	}
  }
  $cl_box_groups[sizeof($cl_box_groups)-1]['apps'][] = array('code' => FILENAME_SLIDESHOW,
                                                               'title' => BOX_CATALOG_SLIDESHOW,
                                                               'link' => tep_href_link(FILENAME_SLIDESHOW));
?>

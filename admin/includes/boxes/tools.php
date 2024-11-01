<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  $cl_box_groups[] = array(
    'heading' => BOX_HEADING_TOOLS,
    'apps' => array(
      
      array(
        'code' => FILENAME_INFORMATION_MANAGER,
        'title' => 'Information pages',
        'link' => tep_href_link(FILENAME_INFORMATION_MANAGER)
      ),	  
      array(
        'code' => FILENAME_BACKUP,
        'title' => BOX_TOOLS_BACKUP,
        'link' => tep_href_link(FILENAME_BACKUP)
      ),
      array(
        'code' => FILENAME_BANNER_MANAGER,
        'title' => BOX_TOOLS_BANNER_MANAGER,
        'link' => tep_href_link(FILENAME_BANNER_MANAGER)
      ),
          
      
      array(
        'code' => FILENAME_DEFINE_LANGUAGE,
        'title' => BOX_TOOLS_DEFINE_LANGUAGE,
        'link' => tep_href_link(FILENAME_DEFINE_LANGUAGE)
      ),
      array(
        'code' => FILENAME_MAIL,
        'title' => BOX_TOOLS_MAIL,
        'link' => tep_href_link(FILENAME_MAIL)
      ),
      array(
        'code' => FILENAME_NEWSLETTERS,
        'title' => BOX_TOOLS_NEWSLETTER_MANAGER,
        'link' => tep_href_link(FILENAME_NEWSLETTERS)
      )
     
    )
  );
?>

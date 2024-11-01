<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/
define('MODULE_BOXES_LOGINRADIUS_TITLE', 'Social Login');
$title_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_BOXES_LOGINRADIUS_TITLE'");
$title_array = tep_db_fetch_array($title_query);
$title = $title_array['configuration_value'];
  define('MODULE_BOXES_LOGINRADIUS_TITLE', $title);
  define('MODULE_BOXES_LOGINRADIUS_DESCRIPTION', 'Login with Existing Account<br><a href="http://www.help.wponlinestore.com/index.php?/Knowledgebase/Article/View/103/0/setting-up-login-radius-social-login" target="_blank">For instructions click here</a>');
  define('MODULE_BOXES_LOGINRADIUS_BOX_TITLE', $title);
?>

<?php
/*
  $Id: ht_theme_switcher.php v1.2 20110831 Kymation $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2011 osCommerce

  Released under the GNU General Public License
*/

  class ht_theme_switcher {
    var $code = 'ht_theme_switcher';
    var $group = 'header_tags';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function ht_theme_switcher() {
    	global $PHP_SELF;

      $this->title = MODULE_HEADER_TAGS_THEME_SWITCHER_TITLE;
      $this->description = MODULE_HEADER_TAGS_THEME_SWITCHER_DESCRIPTION;

      if ( defined('MODULE_HEADER_TAGS_THEME_SWITCHER_STATUS') ) {
        $this->sort_order = MODULE_HEADER_TAGS_THEME_SWITCHER_SORT_ORDER;
        $this->enabled = (MODULE_HEADER_TAGS_THEME_SWITCHER_STATUS == 'True');
      }

      // Include the function that is used to add products in the Admin
      if( $PHP_SELF == 'modules.php' ) {
        include_once( DIR_WS_FUNCTIONS . 'modules/header_tags/theme_switcher.php');
      }
    }

    function execute() {
      global $oscTemplate;

      $jquery_version = '1.5.1';
      if( MODULE_HEADER_TAGS_THEME_SWITCHER_JQUERY_VERSION != '' ) {
        $jquery_version = MODULE_HEADER_TAGS_THEME_SWITCHER_JQUERY_VERSION;
      }

      $jquery_ui_version = '1.8.6';
      if( MODULE_HEADER_TAGS_THEME_SWITCHER_THEME != '' ) {
        $jquery_ui_version = MODULE_HEADER_TAGS_THEME_SWITCHER_JQUERY_UI_VERSION;
      }

      $theme_name = 'redmond';
      if( MODULE_HEADER_TAGS_THEME_SWITCHER_THEME != '' ) {
        $theme_name = MODULE_HEADER_TAGS_THEME_SWITCHER_THEME;
      }

      $theme_text = '<script type="text/javascript" src="'.tep_catalog_href_link('ext/jquery/jquery-' . $jquery_version . '.min.js').'"></script>' . PHP_EOL;
      $theme_text .= '<script type="text/javascript" src="'.tep_catalog_href_link('ext/jquery/ui/jquery-ui-' . $jquery_ui_version . '.min.js').'"></script>' . PHP_EOL;
      $theme_text .= '<link rel="stylesheet" type="text/css" href="'.tep_catalog_href_link('ext/jquery/ui/' . $theme_name . '/jquery-ui-' . $jquery_ui_version . '.css').'" />' . PHP_EOL;

      $oscTemplate->addBlock( $theme_text, $this->group );
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined( 'MODULE_HEADER_TAGS_THEME_SWITCHER_STATUS' );
    }

    function install() {
      tep_db_query( "insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Theme Switcher', 'MODULE_HEADER_TAGS_THEME_SWITCHER_STATUS', 'True', 'Do you want to be able to select a theme here?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())" );
      tep_db_query( "insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_HEADER_TAGS_THEME_SWITCHER_SORT_ORDER', '1', 'Sort order of display. Lowest is displayed first.', '6', '2', now())" );
      tep_db_query( "insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Jquery Version', 'MODULE_HEADER_TAGS_THEME_SWITCHER_JQUERY_VERSION', '1.5.1', 'The version number of your Jquery module (e.g. 1.5.1).', '6', '3', now())" );
      tep_db_query( "insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Jquery UI Version', 'MODULE_HEADER_TAGS_THEME_SWITCHER_JQUERY_UI_VERSION', '1.8.6', 'The version number of your Jquery UI module (e.g. 1.8.13).', '6', '3', now())" );
      tep_db_query( "insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Theme', 'MODULE_HEADER_TAGS_THEME_SWITCHER_THEME', 'redmond', 'Select the theme that you want to use.', '6', '3', 'tep_cfg_pull_down_themes(', now())" );
    }

    function remove() {
      tep_db_query( "delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
    	$keys = array();

      $keys[] = 'MODULE_HEADER_TAGS_THEME_SWITCHER_STATUS';
      $keys[] = 'MODULE_HEADER_TAGS_THEME_SWITCHER_SORT_ORDER';
      $keys[] = 'MODULE_HEADER_TAGS_THEME_SWITCHER_JQUERY_VERSION';
      $keys[] = 'MODULE_HEADER_TAGS_THEME_SWITCHER_JQUERY_UI_VERSION';
      $keys[] = 'MODULE_HEADER_TAGS_THEME_SWITCHER_THEME';

      return $keys;
    }
  }
?>

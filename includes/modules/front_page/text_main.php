<?php
/*
  $Id: text_main.php v1.0.4 20111029 Kymation $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2011 osCommerce

  Released under the GNU General Public License
*/

  class text_main {
    var $code = 'text_main';
    var $group = 'front_page';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;
    var $languages_array = array ();

    function text_main() {
      $this->title = MODULE_FRONT_PAGE_TEXT_MAIN_TITLE;
      $this->description = MODULE_FRONT_PAGE_TEXT_MAIN_DESCRIPTION;

      if (defined('MODULE_FRONT_PAGE_TEXT_MAIN_STATUS')) {
        $this->sort_order = MODULE_FRONT_PAGE_TEXT_MAIN_SORT_ORDER;
        $this->enabled = (MODULE_FRONT_PAGE_TEXT_MAIN_STATUS == 'True');
      }
    } // function text_main

    function execute() {
      global $oscTemplate, $language, $PHP_SELF, $cPath;

      if ($PHP_SELF == 'index.php' && $cPath == '') {
        // Set the text to display on the front page
        $body_text = '<!-- Text Main BOF -->' . PHP_EOL;
        $body_text .= '  <div class="contentText">' . PHP_EOL;
        $body_text .=  stripslashes (constant( 'MODULE_FRONT_PAGE_TEXT_MAIN_' . strtoupper( $language ) ) . PHP_EOL);
        $body_text .= '  </div>' . PHP_EOL;
        $body_text .= '<!-- Text Main EOF -->' . PHP_EOL;

        $oscTemplate->addBlock( $body_text, $this->group );
      }
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_FRONT_PAGE_TEXT_MAIN_STATUS');
    }

    function install() {
      include_once( DIR_WS_CLASSES . 'language.php' );
      $bm_banner_language_class = new language;
      $languages = $bm_banner_language_class->catalog_languages;

      foreach( $languages as $this_language ) {
        $this->languages_array[$this_language['id']] = $this_language['name'];
      }

      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Text Main', 'MODULE_FRONT_PAGE_TEXT_MAIN_STATUS', 'True', 'Do you want to show the main text block on the front page?', '6', '0', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_FRONT_PAGE_TEXT_MAIN_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '1', now())");

      foreach ($this->languages_array as $language_id => $language_name) {
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ( '" . ucwords($language_name) . " Text', 'MODULE_FRONT_PAGE_TEXT_MAIN_" . strtoupper($language_name) . "', 'Quid ergo hunc aliud moliri, quid optare censetis aut quam omnino causam esse belli?', 'Enter the text that you want to show on the front page in " . $language_name . "', '6', '2', 'tep_draw_fck_field(\'configuration[MODULE_FRONT_PAGE_TEXT_MAIN_" . strtoupper($language_name) . "]\', false, 35, 20,', now())");
      }
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      include_once( DIR_WS_CLASSES . 'language.php' );
      $bm_banner_language_class = new language;
      $languages = $bm_banner_language_class->catalog_languages;

      foreach( $languages as $this_language ) {
        $this->languages_array[$this_language['id']] = $this_language['name'];
      }

      $keys_array = array ();

      $keys_array[] = 'MODULE_FRONT_PAGE_TEXT_MAIN_STATUS';
      $keys_array[] = 'MODULE_FRONT_PAGE_TEXT_MAIN_SORT_ORDER';

      foreach ($this->languages_array as $language_name) {
        $keys_array[] = 'MODULE_FRONT_PAGE_TEXT_MAIN_' . strtoupper($language_name);
      }

      return $keys_array;
    }
  }

?>

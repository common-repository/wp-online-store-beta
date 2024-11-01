<?php
/*
  $Id: customer_greeting.php v1.0.2 20110108 Kymation $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  class customer_greeting {
    var $code = 'customer_greeting';
    var $group = 'front_page';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function customer_greeting() {
      $this->title = MODULE_FRONT_PAGE_CUSTOMER_GREETING_TITLE;
      $this->description = MODULE_FRONT_PAGE_CUSTOMER_GREETING_DESCRIPTION;

      if (defined('MODULE_FRONT_PAGE_CUSTOMER_GREETING_STATUS')) {
        $this->sort_order = MODULE_FRONT_PAGE_CUSTOMER_GREETING_SORT_ORDER;
        $this->enabled = (MODULE_FRONT_PAGE_CUSTOMER_GREETING_STATUS == 'True');
      }
    }

    function execute() {
      global $oscTemplate, $PHP_SELF, $cPath;

      if ($PHP_SELF == 'index.php' && $cPath == '') {
        // Set the text to display on the front page
        $body_text = '<!-- Customer Greeting BOF -->' . "\n";
        $body_text .= '  <div class="contentText">' . "\n";
        $body_text .= tep_customer_greeting() . "\n";
        $body_text .= '  </div>' . "\n";
        $body_text .= '<!-- Customer Greeting EOF -->' . "\n";

        $oscTemplate->addBlock($body_text, $this->group);
      }
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_FRONT_PAGE_CUSTOMER_GREETING_STATUS');
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_FRONT_PAGE_CUSTOMER_GREETING_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Customer Greeting', 'MODULE_FRONT_PAGE_CUSTOMER_GREETING_STATUS', 'True', 'Do you want to show the heading title?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array (
        'MODULE_FRONT_PAGE_CUSTOMER_GREETING_STATUS',
        'MODULE_FRONT_PAGE_CUSTOMER_GREETING_SORT_ORDER'
      );
    }
  }

?>
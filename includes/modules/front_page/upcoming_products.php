<?php
/*
  $Id: upcoming_products.php v1.0.3 20110108 Kymation $
  Most of the execute() code is from the stock osCommerce Upcoming Products module

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  class upcoming_products {
    var $code = 'upcoming_products';
    var $group = 'front_page';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function upcoming_products() {
      $this->title = MODULE_FRONT_PAGE_UPCOMING_PRODUCTS_TITLE;
      $this->description = MODULE_FRONT_PAGE_UPCOMING_PRODUCTS_DESCRIPTION;

      if (defined('MODULE_FRONT_PAGE_UPCOMING_PRODUCTS_STATUS')) {
        $this->sort_order = MODULE_FRONT_PAGE_UPCOMING_PRODUCTS_SORT_ORDER;
        $this->enabled = (MODULE_FRONT_PAGE_UPCOMING_PRODUCTS_STATUS == 'True');
      }
    }

    function execute() {
      global $oscTemplate, $language, $languages_id, $currencies, $PHP_SELF, $cPath;

      if ($PHP_SELF == 'index.php' && $cPath == '') {
        // Get the module contents to display on the front page
        $upcoming_query_raw = "
                        select
                          p.products_id,
                          pd.products_name,
                          products_date_available as date_expected
                        from
                          " . TABLE_PRODUCTS . " p
                          join " . TABLE_PRODUCTS_DESCRIPTION . " pd
                            on pd.products_id = p.products_id
                        where
                          to_days(products_date_available) >= to_days(now())
                          and pd.language_id = '" . ( int ) $languages_id . "'
                        order by
                          " . MODULE_FRONT_PAGE_UPCOMING_PRODUCTS_FIELD . "
                          " . MODULE_FRONT_PAGE_UPCOMING_PRODUCTS_SORT . "
                        limit " . MODULE_FRONT_PAGE_UPCOMING_PRODUCTS_MAX_DISPLAY;

        $upcoming_query = tep_db_query($upcoming_query_raw);
        if (tep_db_num_rows($upcoming_query) > 0) {
          $upcoming_prods_content = '<!-- Upcoming Products BOF -->' . PHP_EOL;

          if( constant( 'MODULE_FRONT_PAGE_UPCOMING_PRODUCTS_TITLE_' . strtoupper( $language ) ) != '') {
            $upcoming_prods_content .= '  <h2>' . constant( 'MODULE_FRONT_PAGE_UPCOMING_PRODUCTS_TITLE_' . strtoupper( $language ) ) . '</h2>';
          }
          $upcoming_prods_content .= '<span style="float: right;">' . TABLE_HEADING_DATE_EXPECTED . '</span>' . PHP_EOL;
          $upcoming_prods_content .= '  <div class="contentText">' . PHP_EOL;

          // Start the table to display the product data
          $upcoming_prods_content .= '    <table border="0" width="100%" cellspacing="0" cellpadding="2" class="productListTable">' . PHP_EOL;

          while ($upcoming_products = tep_db_fetch_array( $upcoming_query ) ) {
            $upcoming_prods_content .= '        <tr>' . PHP_EOL;
            $upcoming_prods_content .= '          <td><a href="' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $upcoming_products['products_id']) . '">' . $upcoming_products['products_name'] . '</a></td>' . PHP_EOL;
            $upcoming_prods_content .= '          <td align="right">' . tep_date_short($upcoming_products['date_expected']) . '</td>' . PHP_EOL;
            $upcoming_prods_content .= '        </tr>' . PHP_EOL;
          }
          // Close the table
          $upcoming_prods_content .= '    </table>' . PHP_EOL;
          $upcoming_prods_content .= '  </div>' . PHP_EOL;
          $upcoming_prods_content .= '<!-- Upcoming Products EOF -->' . PHP_EOL;
        }

        // Add the contents as a module
        $oscTemplate->addBlock($upcoming_prods_content, $this->group);
      }
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_FRONT_PAGE_UPCOMING_PRODUCTS_STATUS');
    }

    function install() {
      include_once( DIR_WS_CLASSES . 'language.php' );
      $bm_banner_language_class = new language;
      $languages = $bm_banner_language_class->catalog_languages;

      foreach( $languages as $this_language ) {
        $this->languages_array[$this_language['id']] = $this_language['name'];
      }

      tep_db_query( "insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_FRONT_PAGE_UPCOMING_PRODUCTS_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
      tep_db_query( "insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Upcoming Products', 'MODULE_FRONT_PAGE_UPCOMING_PRODUCTS_STATUS', 'True', 'Do you want to show the Upcoming Products box on the front page?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query( "insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Title', 'MODULE_FRONT_PAGE_UPCOMING_PRODUCTS_FRONT_TITLE', 'Upcoming Products', 'Title to show on the front page.', '6', '2', now())");
      tep_db_query( "insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES('Expected Sort Field', 'MODULE_FRONT_PAGE_UPCOMING_PRODUCTS_FIELD', 'date_expected', 'The column to sort by in the expected products box.', '6', '3', 'tep_cfg_select_option(array(\'products_name\', \'date_expected\'), ', now())");
      tep_db_query( "insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Expected Sort Order', 'MODULE_FRONT_PAGE_UPCOMING_PRODUCTS_SORT', 'desc', 'This is the sort order used in the expected products box.', '6', '4', 'tep_cfg_select_option(array(\'asc\', \'desc\'), ', now())");
      tep_db_query( "insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Products Expected', 'MODULE_FRONT_PAGE_UPCOMING_PRODUCTS_MAX_DISPLAY', '10', 'Maximum number of products expected to display', '6', '5', now())");

    	foreach( $this->languages_array as $language_name ) {
        tep_db_query( "insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ( '" . ucwords( $language_name ) . " Title', 'MODULE_FRONT_PAGE_UPCOMING_PRODUCTS_TITLE_" . strtoupper( $language_name ) . "', 'Title', 'Enter the title that you want on your box in " . $language_name . "', '6', '14', now())" );
      }
    }

    function remove() {
      tep_db_query( "delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      include_once( DIR_WS_CLASSES . 'language.php' );
      $bm_banner_language_class = new language;
      $languages = $bm_banner_language_class->catalog_languages;

      foreach( $languages as $this_language ) {
        $this->languages_array[$this_language['id']] = $this_language['name'];
      }

      $keys = array ();

      $keys[] = 'MODULE_FRONT_PAGE_UPCOMING_PRODUCTS_STATUS';
      $keys[] = 'MODULE_FRONT_PAGE_UPCOMING_PRODUCTS_SORT_ORDER';
      $keys[] = 'MODULE_FRONT_PAGE_UPCOMING_PRODUCTS_FRONT_TITLE';
      $keys[] = 'MODULE_FRONT_PAGE_UPCOMING_PRODUCTS_FIELD';
      $keys[] = 'MODULE_FRONT_PAGE_UPCOMING_PRODUCTS_SORT';
      $keys[] = 'MODULE_FRONT_PAGE_UPCOMING_PRODUCTS_MAX_DISPLAY';

    	foreach( $this->languages_array as $language_name ) {
    	  $keys[] = 'MODULE_FRONT_PAGE_UPCOMING_PRODUCTS_TITLE_' . strtoupper( $language_name );
    	}

      return $keys;
    }
  }

?>
<?php
/*
  $Id: specials.php v1.1 20110109 Kymation $
  Most of the execute() code is from the stock osCommerce Specials page

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  class specials {
    var $code = 'specials';
    var $group = 'front_page';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function specials() {
      $this->title = MODULE_FRONT_PAGE_SPECIALS_TITLE;
      $this->description = MODULE_FRONT_PAGE_SPECIALS_DESCRIPTION;

      if (defined('MODULE_FRONT_PAGE_SPECIALS_STATUS')) {
        $this->sort_order = MODULE_FRONT_PAGE_SPECIALS_SORT_ORDER;
        $this->enabled = (MODULE_FRONT_PAGE_SPECIALS_STATUS == 'True');
      }
    }

    function execute() {
      global $oscTemplate, $language, $languages_id, $currencies, $PHP_SELF, $cPath;

      if ($PHP_SELF == 'index.php' && $cPath == '') {
        $specials_products_query_raw = "
                select
                  p.products_id,
                  pd.products_name,
                  p.products_price,
                  p.products_tax_class_id,
                  p.products_image,
                  s.specials_new_products_price
                from
                  " . TABLE_PRODUCTS . " p
                  join " . TABLE_PRODUCTS_DESCRIPTION . " pd
                    on pd.products_id = p.products_id
                  join " . TABLE_SPECIALS . " s
                    on s.products_id = p.products_id
                where
                  p.products_status = '1'
                  and pd.language_id = '" . ( int ) $languages_id . "'
                  and s.status = '1'
                order by
                  RAND()
                limit
                  " . MODULE_FRONT_PAGE_SPECIALS_MAX_DISPLAY . "
              ";
        // print 'Specials Query: ' . $specials_products_query_raw . '<br />';
        $specials_products_query = tep_db_query( $specials_products_query_raw );

        if (tep_db_num_rows($specials_products_query) > 0) {
          // Set the text to display on the front page
          $specials_content = '<!-- Specials BOF -->' . "\n";

          if( constant( 'MODULE_FRONT_PAGE_SPECIALS_FRONT_TITLE_' . strtoupper( $language ) ) != '') {
            $specials_content .= '  <h2>';

            if( MODULE_FRONT_PAGE_SPECIALS_LINK == 'True' ) {
              $specials_content .= '<a href="' . tep_href_link( FILENAME_SPECIALS ) . '">';
            }

            $specials_content .= constant( 'MODULE_FRONT_PAGE_SPECIALS_FRONT_TITLE_' . strtoupper( $language ) );

            if( MODULE_FRONT_PAGE_SPECIALS_LINK == 'True' ) {
              $specials_content .= '</a>';
            }

            $specials_content .= '</h2>' . "\n";
          }

          $specials_content .= '  <div class="contentText">' . "\n";
          $specials_content .= '    <table border="0" width="100%" cellspacing="0" cellpadding="2">' . "\n";

          $col = 0;
          while ($specials_products = tep_db_fetch_array($specials_products_query)) {
            // Format the price for the correct currency
            $products_price = '<del>' . $currencies->display_price($specials_products['products_price'], tep_get_tax_rate($specials_products['products_tax_class_id'])) . '</del><br />';
            $products_price .= '<span class="productSpecialPrice">' . $currencies->display_price($specials_products['specials_new_products_price'], tep_get_tax_rate($specials_products['products_tax_class_id'])) . '</span>';

            if ($col == 0) {
              $specials_content .= '    <tr>' . "\n";
            }

            $width = (floor(100 / MODULE_FRONT_PAGE_SPECIALS_COLUMNS));

            $specials_content .= '        <td width="' . $width . '%" align="center" valign="top">' . "\n";
            $specials_content .= '<a href="' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $specials_products['products_id']) . '">' . tep_image(DIR_WS_IMAGES . $specials_products['products_image'], $specials_products['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '</a><br /><a href="' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $specials_products['products_id']) . '">' . $specials_products['products_name'] . '</a><br />' . $products_price;
            $specials_content .= '</td>' . "\n";

            $col++;

            if ($col > (MODULE_FRONT_PAGE_SPECIALS_COLUMNS - 1)) {
              $specials_content .= '    </tr>' . "\n";
              $col = 0;
            }
          } // while( $specials_products

          $specials_content .= '    </table>' . "\n";
          $specials_content .= '  </div>' . "\n";
          $specials_content .= '<!-- Specials EOF -->' . "\n";

          $oscTemplate->addBlock($specials_content, $this->group);
        } // if( tep_db_num_rows
      }
    } // function execute

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_FRONT_PAGE_SPECIALS_STATUS');
    }

    function install() {
      include_once( DIR_WS_CLASSES . 'language.php' );
      $bm_banner_language_class = new language;
      $languages = $bm_banner_language_class->catalog_languages;

      foreach( $languages as $this_language ) {
        $this->languages_array[$this_language['id']] = $this_language['name'];
      }

      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_FRONT_PAGE_SPECIALS_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Specials', 'MODULE_FRONT_PAGE_SPECIALS_STATUS', 'True', 'Do you want to show the Specials box on the front page?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Max Specials', 'MODULE_FRONT_PAGE_SPECIALS_MAX_DISPLAY', '6', 'How many Specials do you want to show on the front page?', '6', '3', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Number of Columns', 'MODULE_FRONT_PAGE_SPECIALS_COLUMNS', '3', 'Number of columns of specials to show', '6', '4', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Link to Specials Page', 'MODULE_FRONT_PAGE_SPECIALS_LINK', 'True', 'Do you want the header to link to the specials page?', '6', '5', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");

    	foreach( $this->languages_array as $language_name ) {
        tep_db_query( "insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ( '" . ucwords( $language_name ) . " Title', 'MODULE_FRONT_PAGE_SPECIALS_FRONT_TITLE_" . strtoupper( $language_name ) . "', 'Title', 'Enter the title that you want on your box in " . $language_name . "', '6', '14', now())" );
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

      $keys = array ();

      $keys[] = 'MODULE_FRONT_PAGE_SPECIALS_STATUS';
      $keys[] = 'MODULE_FRONT_PAGE_SPECIALS_SORT_ORDER';
      $keys[] = 'MODULE_FRONT_PAGE_SPECIALS_MAX_DISPLAY';
      $keys[] = 'MODULE_FRONT_PAGE_SPECIALS_COLUMNS';
      $keys[] = 'MODULE_FRONT_PAGE_SPECIALS_LINK';

    	foreach( $this->languages_array as $language_name ) {
    	  $keys[] = 'MODULE_FRONT_PAGE_SPECIALS_FRONT_TITLE_' . strtoupper( $language_name );
    	}

      return $keys;
    }
  }

?>
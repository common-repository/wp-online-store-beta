<?php
/*
  $Id: featured.php v1.1.6 20111029 Kymation $
  Most of the execute() code is from the stock osCommerce New Products module

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2011 osCommerce

  Released under the GNU General Public License
*/

  class featured {
    var $code = 'featured';
    var $group = 'front_page';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;
    var $count;
    // Set the number of featured products in case the constant is not defined
    var $featured_products = 10;

    function featured() {
      global $PHP_SELF;

      $this->title = MODULE_FRONT_PAGE_FEATURED_TITLE;
      $this->description = MODULE_FRONT_PAGE_FEATURED_DESCRIPTION;

      if (defined('MODULE_FRONT_PAGE_FEATURED_STATUS')) {
        $this->sort_order = MODULE_FRONT_PAGE_FEATURED_SORT_ORDER;
        $this->enabled = (MODULE_FRONT_PAGE_FEATURED_STATUS == 'True');
        $this->count = MODULE_FRONT_PAGE_FEATURED_MAX_DISPLAY + 1;
      }

      // Include the function that is used to add products in the Admin
      if ($_GET['submenu'].'.php' == 'modules.php') {
      	include_once (DIR_FS_CATALOG.'admin/includes/'.DIR_WS_FUNCTIONS . 'modules/front_page/featured.php');
      }

    	if( defined( 'MAX_DISPLAY_FEATURED_PRODUCTS' ) ) {
        $this->featured_products = MAX_DISPLAY_FEATURED_PRODUCTS;
    	}
    }

    function execute() {
      global $oscTemplate, $languages_id, $currencies, $PHP_SELF, $cPath;

      if ($PHP_SELF == 'index.php' && $cPath == '') {
        // Set the text to display on the front page
        $featured__content = '<!-- Featured Products BOF -->' . "\n";
        if (MODULE_FRONT_PAGE_FEATURED_FRONT_TITLE != '') {
          $featured__content .= '  <h2>' . MODULE_FRONT_PAGE_FEATURED_FRONT_TITLE . '</h2>' . "\n";
        }
        $featured__content .= '  <div class="contentText">' . "\n";
        $featured__content .= '    <table border="0" width="100%" cellspacing="0" cellpadding="2">' . "\n";

        $col = 0;
        for ($id = 1; $id < $this->count; $id++) {
          $products_id = @ constant('MODULE_FRONT_PAGE_FEATURED_PRODUCT_' . $id);
          if ($products_id > 0) {
            $featured_products_query_raw = "
                        select
                          p.products_id,
                          pd.products_name,
                          p.products_price,
                          p.products_tax_class_id,
                          p.products_image,
                          s.specials_new_products_price,
                          s.status
                        from
                          " . TABLE_PRODUCTS . " p
                          join " . TABLE_PRODUCTS_DESCRIPTION . " pd
                            on pd.products_id = p.products_id
                          left join " . TABLE_SPECIALS . " s
                            on s.products_id = p.products_id
                        where
                          p.products_id = '" . $products_id . "'
                          and pd.language_id = '" . ( int ) $languages_id . "'
                      ";
            // print 'Featured Query: ' . $featured_products_query_raw . '<br />';
            $featured_products_query = tep_db_query($featured_products_query_raw);
            $featured_products = tep_db_fetch_array($featured_products_query);

            // Format the price for the correct currency
            if ($featured_products['status'] == 1) {
              $products_price = '<del>' . $currencies->display_price($featured_products['products_price'], tep_get_tax_rate($featured_products['products_tax_class_id'])) . '</del><br />';
              $products_price .= '<span class="productSpecialPrice">' . $currencies->display_price($featured_products['specials_new_products_price'], tep_get_tax_rate($featured_products['products_tax_class_id'])) . '</span>';
            } else {
              $products_price = $currencies->display_price($featured_products['products_price'], tep_get_tax_rate($featured_products['products_tax_class_id']));
            }

            if ($col == 0) {
              $featured__content .= '    <tr>' . "\n";
            }

            $width = (floor(100 / MODULE_FRONT_PAGE_FEATURED_COLUMNS));

            $featured__content .= '        <td width="' . $width . '%" align="center" valign="top">' . "\n";
            $featured__content .= '<a href="' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $featured_products['products_id']) . '">' . tep_image(DIR_WS_IMAGES . $featured_products['products_image'], $featured_products['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '</a><br /><a href="' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $featured_products['products_id']) . '">' . $featured_products['products_name'] . '</a><br />' . $products_price;
            $featured__content .= '</td>' . "\n";

            $col++;

            if ($col > (MODULE_FRONT_PAGE_FEATURED_COLUMNS - 1)) {
              $featured__content .= '    </tr>' . "\n";
              $col = 0;
            }
          }
        } // for( $id=1;

        $featured__content .= '    </table>' . "\n";
        $featured__content .= '  </div>' . "\n";
        $featured__content .= '<!-- New Products EOF -->' . "\n";

        $oscTemplate->addBlock($featured__content, $this->group);
      }
    } // function execute

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_FRONT_PAGE_FEATURED_STATUS');
    }

    function install() {
    	if( !defined( 'MAX_DISPLAY_FEATURED_PRODUCTS' ) ) {
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Max Featured Products', 'MAX_DISPLAY_FEATURED_PRODUCTS', '10', 'Set the maximum number of featured products to allow.', '6', '222', now())");
    	}

      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_FRONT_PAGE_FEATURED_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Featured Products', 'MODULE_FRONT_PAGE_FEATURED_STATUS', 'True', 'Do you want to show the Featured box on the front page?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Title', 'MODULE_FRONT_PAGE_FEATURED_FRONT_TITLE', 'Featured Products', 'Title to show on the front page.', '6', '2', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Max Featured Products', 'MODULE_FRONT_PAGE_FEATURED_MAX_DISPLAY', '6', 'How many featured products do you want to show?', '6', '3', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Number of Columns', 'MODULE_FRONT_PAGE_FEATURED_COLUMNS', '3', 'Number of columns of products to show', '6', '4', now())");

      for ($id = 1; $id <= $this->featured_products; $id++) {
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Featured Product #" . $id . "', 'MODULE_FRONT_PAGE_FEATURED_PRODUCT_" . $id . "', '', 'Select featured product #" . $id . " to show', '6', '99', 'tep_cfg_pull_down_products(', now())");
      }
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      $keys = array ();
      $keys[] = 'MODULE_FRONT_PAGE_FEATURED_STATUS';
      $keys[] = 'MODULE_FRONT_PAGE_FEATURED_SORT_ORDER';
      $keys[] = 'MODULE_FRONT_PAGE_FEATURED_FRONT_TITLE';
      $keys[] = 'MODULE_FRONT_PAGE_FEATURED_MAX_DISPLAY';
      $keys[] = 'MODULE_FRONT_PAGE_FEATURED_COLUMNS';

      for ($id = 1; $id <= $this->featured_products; $id++) {
        $keys[] = 'MODULE_FRONT_PAGE_FEATURED_PRODUCT_' . $id;
      }

      return $keys;
    }
  }

?>
<?php
/*
  $Id: new_products.php v1.0.2 20110108 Kymation $
  Most of the execute() code is from the stock osCommerce New Products module

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  class new_products {
    var $code = 'new_products';
    var $group = 'front_page';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function new_products() {
      $this->title = MODULE_FRONT_PAGE_NEW_PRODUCTS_TITLE;
      $this->description = MODULE_FRONT_PAGE_NEW_PRODUCTS_DESCRIPTION;

      if (defined('MODULE_FRONT_PAGE_NEW_PRODUCTS_STATUS')) {
        $this->sort_order = MODULE_FRONT_PAGE_NEW_PRODUCTS_SORT_ORDER;
        $this->enabled = (MODULE_FRONT_PAGE_NEW_PRODUCTS_STATUS == 'True');
      }
    }

    function execute() {
      global $oscTemplate, $languages_id, $currencies, $PHP_SELF, $cPath;

      if ($PHP_SELF == 'index.php' && $cPath == '') {
        // Set the text to display on the front page
        $new_prods_content = '<!-- New Products BOF -->' . "\n";
        if (MODULE_FRONT_PAGE_NEW_PRODUCTS_FRONT_TITLE != '') {
          $new_prods_content .= '  <h2>' . sprintf(MODULE_FRONT_PAGE_NEW_PRODUCTS_FRONT_TITLE, strftime('%B')) . '</h2>' . "\n";
        }
        $new_prods_content .= '  <div class="contentText">' . "\n";

        if ((!isset ($new_products_category_id)) || ($new_products_category_id == '0')) {
          $new_products_query = tep_db_query("select p.products_id, p.products_image, p.products_tax_class_id, pd.products_name, if(s.status, s.specials_new_products_price, p.products_price) as products_price from " . TABLE_PRODUCTS . " p left join " . TABLE_SPECIALS . " s on p.products_id = s.products_id, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_status = '1' and p.products_id = pd.products_id and pd.language_id = '" . (int) $languages_id . "' order by p.products_date_added desc limit " . MODULE_FRONT_PAGE_NEW_PRODUCTS_MAX_DISPLAY);
        } else {
          $new_products_query = tep_db_query("select distinct p.products_id, p.products_image, p.products_tax_class_id, pd.products_name, if(s.status, s.specials_new_products_price, p.products_price) as products_price from " . TABLE_PRODUCTS . " p left join " . TABLE_SPECIALS . " s on p.products_id = s.products_id, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . TABLE_CATEGORIES . " c where p.products_id = p2c.products_id and p2c.categories_id = c.categories_id and c.parent_id = '" . (int) $new_products_category_id . "' and p.products_status = '1' and p.products_id = pd.products_id and pd.language_id = '" . (int) $languages_id . "' order by p.products_date_added desc limit " . MODULE_FRONT_PAGE_NEW_PRODUCTS_MAX_DISPLAY);
        }

        $col = 0;

        $new_prods_content .= '    <table border="0" width="100%" cellspacing="0" cellpadding="2">' . "\n";
        while ($new_products = tep_db_fetch_array($new_products_query)) {
          if ($col === 0) {
            $new_prods_content .= '    <tr>' . "\n";
          }

          $width = (floor(100 / MODULE_FRONT_PAGE_NEW_PRODUCTS_COLUMNS));

          $new_prods_content .= '      <td width="' . $width . '%" align="center" valign="top">' . "\n";
          $new_prods_content .= '<a href="' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $new_products['products_id']) . '">' . tep_image(DIR_WS_IMAGES . $new_products['products_image'], $new_products['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '</a><br /><a href="' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $new_products['products_id']) . '">' . $new_products['products_name'] . '</a><br />' . $currencies->display_price($new_products['products_price'], tep_get_tax_rate($new_products['products_tax_class_id'])) . '</td>' . "\n";

          $col++;

          if ($col > (MODULE_FRONT_PAGE_NEW_PRODUCTS_COLUMNS - 1)) {
            $new_prods_content .= '    </tr>' . "\n";

            $col = 0;
          }
        }

        $new_prods_content .= '    </table>' . "\n";
        $new_prods_content .= '  </div>' . "\n";
        $new_prods_content .= '<!-- New Products EOF -->' . "\n";

        $oscTemplate->addBlock($new_prods_content, $this->group);
      }
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_FRONT_PAGE_NEW_PRODUCTS_STATUS');
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_FRONT_PAGE_NEW_PRODUCTS_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable New Products', 'MODULE_FRONT_PAGE_NEW_PRODUCTS_STATUS', 'True', 'Do you want to show the New Products box on the front page?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Title', 'MODULE_FRONT_PAGE_NEW_PRODUCTS_FRONT_TITLE', 'New Products for %s', 'Title to show on the front page (%s inserts the current month.)', '6', '2', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Max New Products', 'MODULE_FRONT_PAGE_NEW_PRODUCTS_MAX_DISPLAY', '6', 'How many New Products do you want to show on the front page?', '6', '3', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Number of Columns', 'MODULE_FRONT_PAGE_NEW_PRODUCTS_COLUMNS', '3', 'Number of columns of products to show', '6', '4', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array (
        'MODULE_FRONT_PAGE_NEW_PRODUCTS_STATUS',
        'MODULE_FRONT_PAGE_NEW_PRODUCTS_SORT_ORDER',
        'MODULE_FRONT_PAGE_NEW_PRODUCTS_MAX_DISPLAY',
        'MODULE_FRONT_PAGE_NEW_PRODUCTS_FRONT_TITLE',
        'MODULE_FRONT_PAGE_NEW_PRODUCTS_COLUMNS'
      );
    }
  }

?>
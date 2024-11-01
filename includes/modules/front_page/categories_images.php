<?php
/*
  $Id: categories_images.php v1.1.7 20110108 Kymation $
  Most of the execute() code is from the stock osCommerce New Products module

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  class categories_images {
    var $code = 'categories_images';
    var $group = 'front_page';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function categories_images() {
      $this->title = MODULE_FRONT_PAGE_CATEGORIES_IMAGES_TITLE;
      $this->description = MODULE_FRONT_PAGE_CATEGORIES_IMAGES_DESCRIPTION;

      if (defined('MODULE_FRONT_PAGE_CATEGORIES_IMAGES_STATUS')) {
        $this->sort_order = MODULE_FRONT_PAGE_CATEGORIES_IMAGES_SORT_ORDER;
        $this->enabled = (MODULE_FRONT_PAGE_CATEGORIES_IMAGES_STATUS == 'True');
      }
    }

    function execute() {
      global $oscTemplate, $languages_id, $currencies, $PHP_SELF, $cPath;

      if ($PHP_SELF == 'index.php' && $cPath == '') {
        $categories_content = '<!-- Categories Images BOF -->' . "\n";
        if (MODULE_FRONT_PAGE_CATEGORIES_IMAGES_FRONT_TITLE != '') {
          $categories_content .= '  <h2>' . MODULE_FRONT_PAGE_CATEGORIES_IMAGES_FRONT_TITLE . '</h2>' . "\n";
        }
        $categories_content .= '  <div class="contentText">' . "\n";

        // Retrieve the data on the products in the categories list and load into an array
        $categories_query_raw = "
                select
                  c.categories_id,
                  c.categories_image,
                  cd.categories_name
                from " . TABLE_CATEGORIES_DESCRIPTION . " cd
                  join " . TABLE_CATEGORIES . " c
                    on c.categories_id = cd.categories_id
                where
                  c.parent_id = '0'
                  and cd.language_id = '" . (int) $languages_id . "'
                order by
                  c.sort_order
              ";
        //print 'Categories Query: ' . $categories_query_raw . '<br />';
        $categories_query = tep_db_query($categories_query_raw);

        while ($categories = tep_db_fetch_array($categories_query)) {
          $categories_id = $categories['categories_id'];
          $categories_data[$categories_id] = array (
            'id' => $categories_id,
            'name' => $categories['categories_name'],
            'image' => $categories['categories_image']
          );
        } //while ($categories

        // Set up the box in the selected style
        if (count($categories_data) > 0) { // Show only if we have categories in the array
          switch (MODULE_FRONT_PAGE_CATEGORIES_IMAGES_BOX_STYLE) {
            // Show the categories in a fixed grid (# of columns is set in Admin)
            case 'Grid' :
              $categories_content .= '    <table border="0" width="100%" cellspacing="0" cellpadding="2">' . "\n";
              $row = 0;
              $col = 0;
              $space_above = false;
              foreach ($categories_data as $category) {
                if ($col == 0) {
                  $categories_content .= '      <tr>' . "\n";
                }

                $width = (floor(100 / MODULE_FRONT_PAGE_CATEGORIES_IMAGES_BOX_COLUMNS));

                $categories_content .= '        <td width="' . $width . '%" align="center" valign="top">' . "\n";
                $categories_content .= '<a href="' . tep_href_link(FILENAME_DEFAULT, 'cPath=' . $category['id']) . '">';

                // Show the products image if selected in Admin
                if (MODULE_FRONT_PAGE_CATEGORIES_IMAGES_SHOW_IMAGE == 'True') {
                  $categories_content .= tep_image(DIR_WS_IMAGES . $category['image'], $category['name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'hspace="2" vspace="3"');
                  $space_above = true;
                } //if (MODULE_FRONT_PAGE_CATEGORIES_IMAGES_SHOW_IMAGE

                // Show the products name if selected in Admin
                if (MODULE_FRONT_PAGE_CATEGORIES_IMAGES_SHOW_NAME == 'True') {
                  if ($space_above == true) {
                    $categories_content .= "<br />\n";
                  }
                  $categories_content .= $category['name'];
                  $space_above = true;
                } //if (MODULE_FRONT_PAGE_CATEGORIES_IMAGES_SHOW_NAME

                $categories_content .= '</a></td>' . "\n";

                $col++;
                if ($col >= MODULE_FRONT_PAGE_CATEGORIES_IMAGES_BOX_COLUMNS) {
                  $col = 0;
                  $row++;
                  $categories_content .= '</tr>' . "\n";
                } //if ($col

              } //foreach ($categories_data

              if ($col < MODULE_FRONT_PAGE_CATEGORIES_IMAGES_BOX_COLUMNS) {
                $categories_content .= '</tr>' . "\n";
              } //if ($col

              $categories_content .= '</table>' . "\n";
              break;

              // Show the categories in a floating grid (# of columns depends on browser width)
            case 'Float' :
              // Link to the stylesheet and javascript to go in the header
              $header = '<link rel="stylesheet" type="text/css" href="ext/modules/front_page/categories_images/stylesheet.css" />' . "\n";
              $header .= '<script type="text/javascript"><!--' . "\n";
              $header .= 'function set_CSS(el_id, CSS_attr, CSS_val) {' . "\n";
              $header .= '  var el = document.getElementById(el_id);' . "\n";
              $header .= '  if (el) el.style[CSS_attr] = CSS_val;' . "\n";
              $header .= '}' . "\n";
              $header .= '//-->' . "\n";
              $header .= '</script>' . "\n";

              $oscTemplate->addBlock($header, 'header_tags');

              // Set up the on-page code
              $box_number = 1;
              $space_above = false;
              foreach ($categories_data as $category) {
                $categories_content .= '<div class="imageBox" id="box_' . $box_number . '"';
                if (MODULE_FRONT_PAGE_CATEGORIES_IMAGES_BOX_MOUSEOVER == 'True') {
                  // Change the colors in the next line to change the mousover color of the border
                  // See the User's Manual for instructions
                  $categories_content .= ' onmouseover="set_CSS(\'box_' . $box_number . '\',\'borderColor\',\'#aabbdd\')" onmouseout="set_CSS(\'box_' . $box_number . '\',\'borderColor\',\'#182d5c\')" ';
                }
                $categories_content .= '>';

                $categories_content .= '<div class="link_column"><a href="' . tep_href_link(FILENAME_DEFAULT, 'cPath=' . $category['id']) . '">';

                // Show the category image if selected
                if (MODULE_FRONT_PAGE_CATEGORIES_IMAGES_SHOW_IMAGE == 'True') {
                  $categories_content .= tep_image(DIR_WS_IMAGES . $category['image'], $category['name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'hspace="2" vspace="3"');
                } //if (CATEGORIES_IMAGES_BOX_SHOW_IMAGE

                // Show the category name if selected
                if (MODULE_FRONT_PAGE_CATEGORIES_IMAGES_SHOW_NAME == 'True') {
                  $categories_content .= '<strong>' . $category['name'] . '</strong>';
                } //if (CATEGORIES_IMAGES_BOX_SHOW_NAME

                $categories_content .= '</a></div>';

                // Show the subcategories if selected
                if (MODULE_FRONT_PAGE_CATEGORIES_IMAGES_SHOW_SUBCATEGORIES == 'True') {
                  $subcategories_query_raw = "
                                    select
                                      c.categories_id,
                                      cd.categories_name
                                    from
                                      " . TABLE_CATEGORIES_DESCRIPTION . " cd
                                      join " . TABLE_CATEGORIES . " c
                                        on c.categories_id = cd.categories_id
                                    where
                                      c.parent_id = " . $category['id'] . "
                                      and cd.language_id = '" . (int) $languages_id . "'
                                    order by
                                      c.sort_order
                                  ";
                  //print 'Subcategories Query: ' . $subcategories_query_raw . '<br />';
                  $subcategories_query = tep_db_query($subcategories_query_raw);

                  while ($subcategories = tep_db_fetch_array($subcategories_query)) {
                    $categories_content .= '<div class="link_column"><a href="' . tep_href_link(FILENAME_DEFAULT, 'cPath=' . $category['id'] . '_' . $subcategories['categories_id']) . '" class="category_link_sub">' . $subcategories['categories_name'] . '</a></div>';
                  }
                } //if (MODULE_FRONT_PAGE_CATEGORIES_IMAGES_SHOW_SUBCATEGORIES

                $categories_content .= '</div>' . "\n";

                $box_number++;
              } //foreach ($categories_data
              break;

              // Show products in row format, similar to Category list
            case 'Row' :
            default :
              $categories_content .= '    <table border="0" width="100%" cellspacing="0" cellpadding="2">' . "\n";
              foreach ($categories_data as $category) {
                $categories_content .= '<tr>' . "\n";
                // Show the products image if selected in Admin
                if (MODULE_FRONT_PAGE_CATEGORIES_IMAGES_SHOW_IMAGE == 'True') {
                  $categories_content .= '<td>';
                  $categories_content .= '<a href="' . tep_href_link(FILENAME_DEFAULT, 'cPath=' . $category['id']) . '">';
                  $categories_content .= tep_image(DIR_WS_IMAGES . $category['image'], $category['name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'hspace="2" vspace="3"');
                  $categories_content .= '</a>';
                  $categories_content .= '</td>';
                } //if (CATEGORIES_IMAGES_BOX_SHOW_IMAGE

                // Show the products name if selected in Admin
                if (MODULE_FRONT_PAGE_CATEGORIES_IMAGES_SHOW_NAME == 'True') {
                  $categories_content .= '<td>';
                  $categories_content .= '<a href="' . tep_href_link(FILENAME_DEFAULT, 'cPath=' . $category['id']) . '">';
                  $categories_content .= '<b>' . $category['name'] . '</b>';
                  $categories_content .= '</a>';
                  $categories_content .= '</td>';
                } //if (CATEGORIES_IMAGES_BOX_SHOW_NAME

                $categories_content .= '</tr>' . "\n";
              } // foreach ($categories_data

              $categories_content .= '</table>' . "\n";
              break;

          } // switch

          $categories_content .= '  </div>' . "\n";
          $categories_content .= '<!-- Categories Images EOF -->' . "\n";

          $oscTemplate->addBlock($categories_content, $this->group);
        } // if( count
      }
    } // function execute

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_FRONT_PAGE_CATEGORIES_IMAGES_STATUS');
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_FRONT_PAGE_CATEGORIES_IMAGES_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Categories Images', 'MODULE_FRONT_PAGE_CATEGORIES_IMAGES_STATUS', 'True', 'Do you want to show the Categories Images box on the front page?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Title', 'MODULE_FRONT_PAGE_CATEGORIES_IMAGES_FRONT_TITLE', 'Products', 'Title to show on the front page.', '6', '2', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Box Style', 'MODULE_FRONT_PAGE_CATEGORIES_IMAGES_BOX_STYLE', 'Grid', 'Show the Categories Images box in grid format, floating (variable width) grid, or with each category on a line', '6', '3', 'tep_cfg_select_option(array(\'Grid\', \'Float\', \'Rows\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Box Mouseover', 'MODULE_FRONT_PAGE_CATEGORIES_IMAGES_BOX_MOUSEOVER', 'True', 'Show the mouseover effect on each Category (Must select Float in Box style above)', '6', '4', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Number of Columns', 'MODULE_FRONT_PAGE_CATEGORIES_IMAGES_BOX_COLUMNS', '3', 'Number of columns of categories to show in the Categories Images box', '6', '5', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Show Image', 'MODULE_FRONT_PAGE_CATEGORIES_IMAGES_SHOW_IMAGE', 'True', 'Show the category image in the Categories Images box', '6', '6', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Show Name', 'MODULE_FRONT_PAGE_CATEGORIES_IMAGES_SHOW_NAME', 'True', 'Show the category name in the Categories Images box', '6', '7', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Show Subcategories', 'MODULE_FRONT_PAGE_CATEGORIES_IMAGES_SHOW_SUBCATEGORIES', 'True', 'Show the subcategories list under each category (Float mode only)', '6', '8', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array (
        'MODULE_FRONT_PAGE_CATEGORIES_IMAGES_SORT_ORDER',
        'MODULE_FRONT_PAGE_CATEGORIES_IMAGES_STATUS',
        'MODULE_FRONT_PAGE_CATEGORIES_IMAGES_FRONT_TITLE',
        'MODULE_FRONT_PAGE_CATEGORIES_IMAGES_BOX_STYLE',
        'MODULE_FRONT_PAGE_CATEGORIES_IMAGES_BOX_MOUSEOVER',
        'MODULE_FRONT_PAGE_CATEGORIES_IMAGES_BOX_COLUMNS',
        'MODULE_FRONT_PAGE_CATEGORIES_IMAGES_SHOW_IMAGE',
        'MODULE_FRONT_PAGE_CATEGORIES_IMAGES_SHOW_NAME',
        'MODULE_FRONT_PAGE_CATEGORIES_IMAGES_SHOW_SUBCATEGORIES'
      );
    }
  }

?>
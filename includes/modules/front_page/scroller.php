<?php
/*
  $Id: scroller.php v1.0.3 20110108 Kymation $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  class scroller {
    var $code = 'scroller';
    var $group = 'front_page';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;
    var $count;

    function scroller() {
      global $PHP_SELF;

      $this->title = MODULE_FRONT_PAGE_SCROLLER_TITLE;
      $this->description = MODULE_FRONT_PAGE_SCROLLER_DESCRIPTION;

      if (defined('MODULE_FRONT_PAGE_SCROLLER_STATUS')) {
        $this->sort_order = MODULE_FRONT_PAGE_SCROLLER_SORT_ORDER;
        $this->enabled = (MODULE_FRONT_PAGE_SCROLLER_STATUS == 'True');
        $this->count = MODULE_FRONT_PAGE_SCROLLER_MAX_DISPLAY + 1;
      }

      // Include the function that is used to add products in the Admin
       // Include the function that is used to add products in the Admin
      if ($_GET['submenu'].'.php' == 'modules.php') {
      	include_once (DIR_FS_CATALOG.'admin/includes/'.DIR_WS_FUNCTIONS . 'modules/front_page/featured.php');
      }
      
    }

    function execute() {
      global $PHP_SELF, $oscTemplate, $cPath, $languages_id;

      if ($PHP_SELF == 'index.php' && $cPath == '') {
        // Set the Javascript and styles to go in the header
        $header = '<script type="text/javascript" src="'.tep_catalog_href_link('ext/modules/front_page/scroller/jquery.smoothDivScroll-1.1-min.js').'"></script>' . "\n";

        $header .= '<script type="text/javascript">' . "\n";
        $header .= '    (jQuery)(function() {' . "\n";
        $header .= '			(jQuery)("div#scroller").smoothDivScroll({' . "\n";
        $header .= '        autoScroll: "' . MODULE_FRONT_PAGE_SCROLLER_AUTOSCROLL . '",' . "\n";
        $header .= '        autoScrollDirection: "' . MODULE_FRONT_PAGE_SCROLLER_AUTOSCROLL_DIRECTION . '",' . "\n";
        $header .= '        autoScrollStep: ' . MODULE_FRONT_PAGE_SCROLLER_AUTOSCROLL_STEP . ',' . "\n";
        $header .= '        autoScrollInterval: ' . MODULE_FRONT_PAGE_SCROLLER_AUTOSCROLL_INTERVAL . ',' . "\n";
        $header .= '        scrollStep:	' . MODULE_FRONT_PAGE_SCROLLER_MANUAL_SCROLL_STEP . ',' . "\n";
        $header .= '        scrollInterval:	' . MODULE_FRONT_PAGE_SCROLLER_MANUAL_SCROLL_INTERVAL . ',' . "\n";
        $header .= '        visibleHotSpots: "' . MODULE_FRONT_PAGE_SCROLLER_HOTSPOTS_VISIBLE . '",' . "\n";
        $header .= '        hotSpotsVisibleTime: ' . MODULE_FRONT_PAGE_SCROLLER_HOTSPOTS_VISIBLE_TIME . ',' . "\n";
        $header .= '        mouseDownSpeedBooster: ' . MODULE_FRONT_PAGE_SCROLLER_MOUSEDOWN_SPEED . '' . "\n";
        $header .= '      });' . "\n";
        $header .= '		});' . "\n";
        $header .= '	</script>' . "\n";

        $header .= '  <script type="text/javascript">' . "\n";
        $header .= '    function startScrolling() {' . "\n";
        $header .= '      (jQuery)("#scroller").smoothDivScroll("startAutoScroll");' . "\n";
        $header .= '    }' . "\n";
        $header .= '    function stopScrolling() {' . "\n";
        $header .= '      (jQuery)("#scroller").smoothDivScroll("stopAutoScroll");' . "\n";
        $header .= '    }' . "\n";
        $header .= '	</script>' . "\n";

        $header .= '<style type="text/css">' . "\n\n";
        $header .= 'div.scrollingHotSpotLeft {' . "\n";
        $header .= '	min-width: 36px;' . "\n";
        $header .= '	width: 5%;' . "\n";
        $header .= '	height: 100%;' . "\n";
        $header .= '	background-image: url('.HTTP_SERVER.DIR_WS_IMAGES.'big_transparent.gif);' . "\n";
        $header .= '	background-repeat: repeat;' . "\n";
        $header .= '	background-position: center center;' . "\n";
        $header .= '	position: absolute;' . "\n";
        $header .= '	z-index: 200;' . "\n";
        $header .= '	left: 0;' . "\n";
        $header .= '  top: 0;' . "\n";
        $header .= '	cursor: url('.HTTP_SERVER.DIR_WS_IMAGES.'/cursors/cursor_arrow_left.cur), url('.HTTP_SERVER.DIR_WS_IMAGES.'/cursors/cursor_arrow_left.cur),w-resize;' . "\n";
        $header .= '}' . "\n\n";
        $header .= 'div.scrollingHotSpotLeftVisible {' . "\n";
        $header .= '	background-image: url('.HTTP_SERVER.DIR_WS_IMAGES.'/icons/arrow_left.png);' . "\n";
        $header .= '	background-color: #fff;' . "\n";
        $header .= '	background-repeat: no-repeat;' . "\n";
        $header .= '	opacity: 0.35;' . "\n";
        $header .= '	-moz-opacity: 0.35;' . "\n";
        $header .= '	filter: alpha(opacity = 35);' . "\n";
        $header .= '	zoom: 1;' . "\n";
        $header .= '}' . "\n\n";
        $header .= 'div.scrollingHotSpotRight {' . "\n";
        $header .= '	min-width: 36px;' . "\n";
        $header .= '	width: 5%;' . "\n";
        $header .= '	height: 100%;' . "\n";
        $header .= '	background-image: url('.HTTP_SERVER.DIR_WS_IMAGES.'/big_transparent.gif);' . "\n";
        $header .= '	background-repeat: repeat;' . "\n";
        $header .= '	background-position: center center;' . "\n";
        $header .= '	position: absolute;' . "\n";
        $header .= '	z-index: 200;' . "\n";
        $header .= '	right: 0;' . "\n";
        $header .= '  top: 0;' . "\n";
        $header .= '	cursor: url('.HTTP_SERVER.DIR_WS_IMAGES.'/cursors/cursor_arrow_right.cur), url('.HTTP_SERVER.DIR_WS_IMAGES.'/cursors/cursor_arrow_right.cur),e-resize;' . "\n";
        $header .= '}' . "\n\n";
        $header .= 'div.scrollingHotSpotRightVisible {' . "\n";
        $header .= '	background-image: url('.HTTP_SERVER.DIR_WS_IMAGES.'/icons/arrow_right.png);' . "\n";
        $header .= '	background-color: #fff;' . "\n";
        $header .= '	background-repeat: no-repeat;' . "\n";
        $header .= '	opacity: 0.35;' . "\n";
        $header .= '	filter: alpha(opacity = 35);' . "\n";
        $header .= '	-moz-opacity: 0.35;' . "\n";
        $header .= '	zoom: 1;' . "\n";
        $header .= '}' . "\n\n";
        $header .= 'div.scrollWrapper {' . "\n";
        $header .= '	position: relative;' . "\n";
        $header .= '	overflow: hidden;' . "\n";
        $header .= '	width: 100%;' . "\n";
        $header .= '	height: 100%;' . "\n";
        $header .= '}' . "\n\n";
        $header .= 'div.scrollableArea {' . "\n";
        $header .= '	position: relative;' . "\n";
        $header .= '	width: auto;' . "\n";
        $header .= '	height: 100%;' . "\n";
        $header .= '}' . "\n\n";
        $header .= '#scroller {' . "\n";
        $header .= '  width: 96%;' . "\n";
        $header .= '  height: ' . MODULE_FRONT_PAGE_SCROLLER_MODULE_HEIGHT . 'px;' . "\n";
        $header .= '  position: relative;' . "\n";
        $header .= '  padding: 10px;' . "\n";
        $header .= '  margin: 10px 0 10px 0;' . "\n";
        $header .= '  border: 1px solid #999;' . "\n";
        $header .= '}' . "\n\n";
        $header .= '#scroller div.scrollableArea * {' . "\n";
        $header .= '  position: relative;' . "\n";
        $header .= '  float: left;' . "\n";
        $header .= '  margin: 0;' . "\n";
        $header .= '  padding: 0;' . "\n";
        $header .= '}' . "\n";
        $header .= '</style>' . "\n\n";

        $oscTemplate->addBlock($header, 'header_tags');

        // Start the scroller code to display on the front page
        // Select the sort order of the products
        switch (MODULE_FRONT_PAGE_SCROLLER_PRODUCTS_ORDER) {
          case 'date added' :
            $order_sql = 'products_date_added DESC';
            break;

          case 'last modified' :
            $order_sql = 'products_last_modified DESC';
            break;

          case 'random' :
            $order_sql = 'rand()';
            break;
        } // switch( MODULE_FRONT_PAGE_SCROLLER_PRODUCTS_ORDER

        switch (MODULE_FRONT_PAGE_SCROLLER_PRODUCTS_TYPE) {
          case 'specials' :
            $join_sql = "join " . TABLE_SPECIALS . " s
                          on s.products_id = p.products_id";
            $where_sql = "and s.status = '1'";
            break;

          case 'new' :
            $join_sql = '';
            $where_sql = '';
            $order_sql = 'products_date_added DESC';
            break;

          case 'featured' :
            $products_ids = '';
            for ($id = 1; $id <= $this->featured_products; $id++) {
              $pid = constant('MODULE_FRONT_PAGE_SCROLLER_PRODUCT_' . $id);
              if ($pid != 0) {
                $products_ids .= $pid . ', ';
              }
            }
            $products_ids = rtrim($products_ids, ', ');
            $join_sql = '';
            $where_sql = ' and p.products_id in (' . $products_ids . ')';
            break;

          case 'all' :
          default :
            $join_sql = '';
            $where_sql = '';
            break;
        } // switch( MODULE_FRONT_PAGE_SCROLLER_PRODUCTS_TYPE

        $products_query_raw = "
                  select
                    p.products_id,
                    p.products_image,
                    pd.products_name
                  from
                    " . TABLE_PRODUCTS . " p
                    join " . TABLE_PRODUCTS_DESCRIPTION . " pd
                      on pd.products_id = p.products_id
                    " . $join_sql . "
                  where
                    p.products_status = '1'
                    and pd.language_id = '" . ( int ) $languages_id . "'
                    " . $where_sql . "
                  order by
                    " . $order_sql . "
                  limit
                    " . MODULE_FRONT_PAGE_SCROLLER_MAX_DISPLAY . "
                ";
        // print 'Products Query: ' . $products_query_raw . '<br />';
        $products_query = tep_db_query($products_query_raw);

        if (tep_db_num_rows($products_query) > 0) {
          // Set the text to display on the front page
          $body_text = '<!-- Scroller BOF -->' . "\n";
          $body_text .= '  <div id="scroller" onmouseover="stopScrolling();" onmouseout="startScrolling();">' . "\n";
          $body_text .= '    <div class="scrollingHotSpotLeft"></div>' . "\n";
          $body_text .= '    <div class="scrollingHotSpotRight"></div>' . "\n";
          $body_text .= '    <div class="scrollWrapper">' . "\n";
          $body_text .= '      <div class="scrollableArea">' . "\n";

          $col = 0;
          while ($products_data = tep_db_fetch_array($products_query)) {
            $body_text .= '        <a href="' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $products_data['products_id']) . '">' . tep_image(DIR_WS_IMAGES . $products_data['products_image'], $products_data['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, ' style="padding: 0 ' . MODULE_FRONT_PAGE_SCROLLER_IMAGE_PADDING . 'px;" width="100%"') . '</a>';
          } // while( $products_data

          $body_text .= '      </div>' . "\n";
          $body_text .= '    </div>' . "\n";
          $body_text .= '  </div>' . "\n";
          $body_text .= '<!-- Scroller EOF -->' . "\n";
        }

        $oscTemplate->addBlock($body_text, $this->group);
      }
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_FRONT_PAGE_SCROLLER_STATUS');
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_FRONT_PAGE_SCROLLER_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Scroller', 'MODULE_FRONT_PAGE_SCROLLER_STATUS', 'True', 'Do you want to show the scroller?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Scroller Height', 'MODULE_FRONT_PAGE_SCROLLER_MODULE_HEIGHT', '80', 'The height of the scroller in pixels.', '6', '3', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Image Padding', 'MODULE_FRONT_PAGE_SCROLLER_IMAGE_PADDING', '0', 'Space between the product iamges in pixels.', '6', '4', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('AutoStart', 'MODULE_FRONT_PAGE_SCROLLER_AUTOSCROLL', 'onstart', 'When do you want the scroller to start?', '6', '5', 'tep_cfg_select_option(array(\'onstart\', \'always\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('AutoStart Direction', 'MODULE_FRONT_PAGE_SCROLLER_AUTOSCROLL_DIRECTION', 'endlessloopright', 'What direction do you want the scroller to run?', '6', '6', 'tep_cfg_select_option(array(\'right\', \'left\', \'backandforth\', \'endlessloopright\', \'endlessloopleft\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Visible Hotspots', 'MODULE_FRONT_PAGE_SCROLLER_HOTSPOTS_VISIBLE', 'always', 'When do you want the hotspots to show?', '6', '7', 'tep_cfg_select_option(array(\'\', \'onstart\', \'always\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Hotspots Visible Time', 'MODULE_FRONT_PAGE_SCROLLER_HOTSPOTS_VISIBLE_TIME', '5', 'How long do you want the hotspots to show? (seconds)', '6', '8', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Auto Scroll Step', 'MODULE_FRONT_PAGE_SCROLLER_AUTOSCROLL_STEP', '1', 'The number of pixels in each automatic scroll step.', '6', '9', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Auto Scroll Interval', 'MODULE_FRONT_PAGE_SCROLLER_AUTOSCROLL_INTERVAL', '10', 'The time between each automatic scroll step (milliseconds).', '6', '10', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Manual Scroll Step', 'MODULE_FRONT_PAGE_SCROLLER_MANUAL_SCROLL_STEP', '5', 'The number of pixels in each manual scroll step.', '6', '11', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Manual Scroll Interval', 'MODULE_FRONT_PAGE_SCROLLER_MANUAL_SCROLL_INTERVAL', '15', 'The time between each manual scroll step (milliseconds).', '6', '12', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Mouse Down Speed', 'MODULE_FRONT_PAGE_SCROLLER_MOUSEDOWN_SPEED', '3', 'Speed multiplier when the customer clicks on a hotspot.', '6', '13', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Max Products', 'MODULE_FRONT_PAGE_SCROLLER_MAX_DISPLAY', '20', 'The maximum number of products to display in the scroller.', '6', '14', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Products Shown', 'MODULE_FRONT_PAGE_SCROLLER_PRODUCTS_TYPE', 'all', 'What products do you want to show?', '6', '15', 'tep_cfg_select_option(array(\'all\', \'specials\', \'new\', \'featured\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Products Order', 'MODULE_FRONT_PAGE_SCROLLER_PRODUCTS_ORDER', 'random', 'In what order do you want your products to show?', '6', '16', 'tep_cfg_select_option(array(\'random\', \'date added\', \'last modified\'), ', now())");

      for ($id = 1; $id <= $this->featured_products; $id++) {
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Scroller Product #" . $id . "', 'MODULE_FRONT_PAGE_SCROLLER_PRODUCT_" . $id . "', '', 'Select product #" . $id . " to show', '6', '" . ($id +15) . "', 'tep_cfg_pull_down_products(', now())");
      }
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      $keys = array ();
      $keys[] = 'MODULE_FRONT_PAGE_SCROLLER_SORT_ORDER';
      $keys[] = 'MODULE_FRONT_PAGE_SCROLLER_STATUS';
      $keys[] = 'MODULE_FRONT_PAGE_SCROLLER_MODULE_HEIGHT';
      $keys[] = 'MODULE_FRONT_PAGE_SCROLLER_IMAGE_PADDING';
      $keys[] = 'MODULE_FRONT_PAGE_SCROLLER_AUTOSCROLL';
      $keys[] = 'MODULE_FRONT_PAGE_SCROLLER_AUTOSCROLL_DIRECTION';
      $keys[] = 'MODULE_FRONT_PAGE_SCROLLER_HOTSPOTS_VISIBLE';
      $keys[] = 'MODULE_FRONT_PAGE_SCROLLER_HOTSPOTS_VISIBLE_TIME';
      $keys[] = 'MODULE_FRONT_PAGE_SCROLLER_AUTOSCROLL_STEP';
      $keys[] = 'MODULE_FRONT_PAGE_SCROLLER_AUTOSCROLL_INTERVAL';
      $keys[] = 'MODULE_FRONT_PAGE_SCROLLER_MANUAL_SCROLL_STEP';
      $keys[] = 'MODULE_FRONT_PAGE_SCROLLER_MANUAL_SCROLL_INTERVAL';
      $keys[] = 'MODULE_FRONT_PAGE_SCROLLER_MOUSEDOWN_SPEED';
      $keys[] = 'MODULE_FRONT_PAGE_SCROLLER_MAX_DISPLAY';
      $keys[] = 'MODULE_FRONT_PAGE_SCROLLER_PRODUCTS_TYPE';
      $keys[] = 'MODULE_FRONT_PAGE_SCROLLER_PRODUCTS_ORDER';

      for ($id = 1; $id <= $this->featured_products; $id++) {
        $keys[] = 'MODULE_FRONT_PAGE_SCROLLER_PRODUCT_' . $id;
      }

      return $keys;
    }

  } // End class

?>
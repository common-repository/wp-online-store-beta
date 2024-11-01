<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  $listing_split = new splitPageResults($listing_sql, MAX_DISPLAY_SEARCH_RESULTS, 'p.products_id','pages');
?>

  <div class="contentText">

<?php
  if ( ($listing_split->number_of_rows > 0) && ( (PREV_NEXT_BAR_LOCATION == '1') || (PREV_NEXT_BAR_LOCATION == '3') ) ) {
?>

    <div>
      <span style="float: right;"><?php echo TEXT_RESULT_PAGE . ' ' . $listing_split->display_links(MAX_DISPLAY_PAGE_LINKS, tep_get_all_get_params(array('pages', 'info', 'x', 'y'))); ?></span>

      <span><?php echo $listing_split->display_count(TEXT_DISPLAY_NUMBER_OF_PRODUCTS); ?></span>
    </div>

    <br />

<?php
  }

   $sort_orders = array();
    $sort_orders[] = array('id' => '', 'text' => 'Sort By');
    $sort_orders[] = array('id' => 'products_new', 'text' => 'Recently Added');
	//$sort_orders[] = array('id' => 'products_old', 'text' => 'Old products');
	//$sort_orders[] = array('id' => 'products_viewed', 'text' => 'Most Viewed');
	$sort_orders[] = array('id' => 'products_ordered', 'text' => 'Most Popular');
	$sort_orders[] = array('id' => 'products_price1', 'text' => 'Price Low To High');
	$sort_orders[] = array('id' => 'products_price2', 'text' => 'Price High To Low');
    $sort_orders[] = array('id' => 'products_name', 'text' => 'Product Name');
	$sort_orders[] = array('id' => 'products_sort_order', 'text' => 'Sort Order');


    $hidden_get_variables = '';
    reset($_GET);
    while (list($key, $value) = each($_GET)) {
      if ( ($key != 'sort') && ($key != tep_session_name()) && ($key != 'x') && ($key != 'y') ) {
        $hidden_get_variables .= tep_draw_hidden_field($key, $value);
      }
    }
     
	 
  $prod_list_contents = '<div class="ui-widget infoBoxContainer">' .
                        '  <div class="ui-widget-header ui-corner-top infoBoxHeading">' .
                        '    <table border="0" width="100%" cellspacing="0" cellpadding="2" class="productListingHeader">' .
                        '      <tr>';

  
   $prod_list_contents 	.= '        <td align="left" >'.    tep_draw_form('sort_by', tep_href_link(basename($PHP_SELF), tep_get_all_get_params()), 'get').
      								tep_draw_pull_down_menu('sort', $sort_orders, $HTTP_GET_VARS['sort'], 'onChange="this.form.submit();"').
     						 		$hidden_get_variables.'</form></td>';
	  	// optional Product List Filter
    if (PRODUCT_LIST_FILTER > 0) {
      if (isset($HTTP_GET_VARS['manufacturers_id'])) {
        $filterlist_sql = "select distinct c.categories_id as id, cd.categories_name as name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where p.products_status = '1' and p.products_id = p2c.products_id and p2c.categories_id = c.categories_id and p2c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "' and p.manufacturers_id = '" . (int)$HTTP_GET_VARS['manufacturers_id'] . "' order by cd.categories_name";
      } else {
        $filterlist_sql= "select distinct m.manufacturers_id as id, m.manufacturers_name as name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . TABLE_MANUFACTURERS . " m where p.products_status = '1' and p.manufacturers_id = m.manufacturers_id and p.products_id = p2c.products_id and p2c.categories_id = '" . (int)$current_category_id . "' order by m.manufacturers_name";
      }
      $filterlist_query = tep_db_query($filterlist_sql);
      if (tep_db_num_rows($filterlist_query) > 1) {
          $prod_list_contents_filter .= tep_draw_form('filter', '', 'post') . ' ' . TEXT_SHOW . '&nbsp;';
        if (isset($HTTP_GET_VARS['manufacturers_id'])) {
          $prod_list_contents_filter .= tep_draw_hidden_field('manufacturers_id', $HTTP_GET_VARS['manufacturers_id']);
          $options = array(array('id' => '', 'text' => TEXT_ALL_CATEGORIES));
        } else {
          $prod_list_contents_filter .= tep_draw_hidden_field('cPath', $cPath);
          $options = array(array('id' => '', 'text' => TEXT_ALL_MANUFACTURERS));
        }
        $prod_list_contents_filter .= tep_draw_hidden_field('sort', $HTTP_GET_VARS['sort']);
        while ($filterlist = tep_db_fetch_array($filterlist_query)) {
          $options[] = array('id' => $filterlist['id'], 'text' => $filterlist['name']);
        }
        $prod_list_contents_filter .= tep_draw_pull_down_menu('filter_id', $options, (isset($HTTP_GET_VARS['filter_id']) ? $HTTP_GET_VARS['filter_id'] : ''), 'onchange="this.form.submit()"');
        $prod_list_contents_filter .= tep_hide_session_id() . ' </form> ' . "\n";
      }
    }
										
	  	 $prod_list_contents 	.= '<td align="left" nowrap>'.$prod_list_contents_filter.'</td>';
		 $prod_list_contents .= '        <td  style="text-align:right;"><strong>';
  	if(PRODUCT_LIST_CONTENT_LISTING=="both"){
	$prod_list_contents .=' <a href="'. tep_href_link(FILENAME_DEFAULT, tep_get_all_get_params(array('view')) . 'view=grid', $request_type).'">Grid View |</a> <a href="'. tep_href_link(FILENAME_DEFAULT, tep_get_all_get_params(array('view')) . 'view=list', $request_type).'"> List View</a></strong>';
	} else if(PRODUCT_LIST_CONTENT_LISTING=="grid"){
	//$prod_list_contents .=' <a href="'. tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('view')) . 'view=grid', $request_type).'">Grid</a> View</strong>';
	} elseif(PRODUCT_LIST_CONTENT_LISTING=="list"){
	//$prod_list_contents .=' <a href="'. tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('view')) . 'view=grid', $request_type).'">Grid</a> <a href="'. tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('view')) . 'view=list', $request_type).'">List</a> View</strong>';
	}
	$prod_list_contents .=	'</td>';
  
  
  		$prod_list_contents .= '      </tr>' .
                         '    </table>' .
                         '  </div>';

  if ($listing_split->number_of_rows > 0) {
    $rows = 0;
    $listing_query = tep_db_query($listing_split->sql_query);

    $prod_list_contents .= '  <div class="ui-widget-content ui-corner-bottom productListTable">' .
                           '    <table border="0" width="100%" cellspacing="0" cellpadding="2" class="productListingData">';

    while ($listing = tep_db_fetch_array($listing_query)) {
      $rows++;

      $prod_list_contents .= '      <tr>';

      for ($col=0, $n=sizeof($column_list); $col<$n; $col++) {
        switch ($column_list[$col]) {
          case 'PRODUCT_LIST_MODEL':
            $prod_list_contents .= '        <td>' . $listing['products_model'] . '</td>';
            break;
          case 'PRODUCT_LIST_NAME':
            // begin extra product fields
            $extra = '';
            foreach ($epf as $e) {
              if ($e['listing']) {
                $mt = ($e['uses_list'] && !$e['multi_select'] ? ($listing[$e['field']] == 0) : !tep_not_null($listing[$e['field']]));
                if (!$mt) { // only list fields that aren't empty
                  $extra .= '<br><b>' . $e['label'] . ': </b>';
                  if ($e['uses_list']) {
                    if ($e['multi_select']) {
                      $epf_values = explode('|', trim($listing[$e['field']], '|'));
                      $epf_string = '';
                      foreach ($epf_values as $v) {
                        $epf_string .= tep_get_extra_field_list_value($v) . ', ';
                      }
                      $extra .= trim($epf_string, ', ');
                    } else {
                      $extra .= tep_get_extra_field_list_value($listing[$e['field']],$e['show_chain'] == 1);
                    }
                  } else {
                    $extra .= $listing[$e['field']];
                  }
                }
              }
            }
            // end extra product fields

            // end extra product fields
            $lc_align = '';

            if (isset($HTTP_GET_VARS['manufacturers_id']) && tep_not_null($HTTP_GET_VARS['manufacturers_id'])) {
              $prod_list_contents .= '        <td><a href="' . tep_href_link(FILENAME_PRODUCT_INFO, 'manufacturers_id=' . $HTTP_GET_VARS['manufacturers_id'] . '&products_id=' . $listing['products_id']) . '">' . stripslashes($listing['products_name']) . '</a>' . $extra . '</td>';
            } else {
              $prod_list_contents .= '        <td><a href="' . tep_href_link(FILENAME_PRODUCT_INFO, ($cPath ? 'cPath=' . $cPath . '&' : '') . 'products_id=' . $listing['products_id']) . '">' . stripslashes($listing['products_name']) . '</a>' . $extra  . '</td>';
            }
            break;
            
          case 'PRODUCT_LIST_MANUFACTURER':
            $prod_list_contents .= '        <td><a href="' . tep_href_link(FILENAME_DEFAULT, 'manufacturers_id=' . $listing['manufacturers_id']) . '">' . $listing['manufacturers_name'] . '</a></td>';
            break;
          case 'PRODUCT_LIST_PRICE':
            if (tep_not_null($listing['specials_new_products_price'])) {
              $prod_list_contents .= '        <td align="right"><del>' .  $currencies->display_price($listing['products_price'], tep_get_tax_rate($listing['products_tax_class_id'])) . '</del>&nbsp;&nbsp;<span class="productSpecialPrice">' . $currencies->display_price($listing['specials_new_products_price'], tep_get_tax_rate($listing['products_tax_class_id'])) . '</span></td>';
            } else {
              $prod_list_contents .= '        <td align="right">' . $currencies->display_price($listing['products_price'], tep_get_tax_rate($listing['products_tax_class_id'])) . '</td>';
            }
            break;
          case 'PRODUCT_LIST_QUANTITY':
            $prod_list_contents .= '        <td align="right">' . $listing['products_quantity'] . '</td>';
            break;
          case 'PRODUCT_LIST_WEIGHT':
            $prod_list_contents .= '        <td align="right">' . $listing['products_weight'] . '</td>';
            break;
          case 'PRODUCT_LIST_IMAGE':
            if (isset($HTTP_GET_VARS['manufacturers_id'])  && tep_not_null($HTTP_GET_VARS['manufacturers_id'])) {
              $prod_list_contents .= '        <td align="center"><a href="' . tep_href_link(FILENAME_PRODUCT_INFO, 'manufacturers_id=' . $HTTP_GET_VARS['manufacturers_id'] . '&products_id=' . $listing['products_id']) . '">' . tep_image(DIR_WS_IMAGES . $listing['products_image'], stripslashes($listing['products_name']), SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '</a></td>';
            } else {
              $prod_list_contents .= '        <td align="center"><a href="' . tep_href_link(FILENAME_PRODUCT_INFO, ($cPath ? 'cPath=' . $cPath . '&' : '') . 'products_id=' . $listing['products_id']) . '">' . tep_image(DIR_WS_IMAGES . $listing['products_image'], stripslashes($listing['products_name']), SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '</a></td>';
            }
            break;
          case 'PRODUCT_LIST_BUY_NOW':
            $prod_list_contents .= '        <td align="center">' . tep_draw_button(IMAGE_BUTTON_BUY_NOW, 'cart', tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('action')) . 'action=buy_now&products_id=' . $listing['products_id'])) . '</td>';
            break;
			// BOF Product Sort
		  case 'PRODUCT_SORT_ORDER';
            $lc_align = 'center';
            $lc_text = '&nbsp;' . $listing['products_sort_order'] . '&nbsp;';
            break;
// EOF Product Sort
        }
      }

      $prod_list_contents .= '      </tr>';
    }

    $prod_list_contents .= '    </table>' .
                           '  </div>' .
                           '</div>';

    echo $prod_list_contents;
  } else {
?>

    <p><?php echo TEXT_NO_PRODUCTS; ?></p>

<?php
  }

  if ( ($listing_split->number_of_rows > 0) && ((PREV_NEXT_BAR_LOCATION == '2') || (PREV_NEXT_BAR_LOCATION == '3')) ) {
?>

    <br />

    <div>
      <span style="float: right;"><?php echo TEXT_RESULT_PAGE . ' ' . $listing_split->display_links(MAX_DISPLAY_PAGE_LINKS, tep_get_all_get_params(array('pages', 'info', 'x', 'y')),FILENAME_DEFAULT); ?></span>

      <span><?php echo $listing_split->display_count(TEXT_DISPLAY_NUMBER_OF_PRODUCTS); ?></span>
    </div>

<?php
  }
?>

  </div>

<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');
  global $category_depth;
// the following cPath references come from application_top.php
  $category_depth = 'top';
  if (isset($cPath) && tep_not_null($cPath)) {
    $categories_products_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_TO_CATEGORIES . " where categories_id = '" . (int)$current_category_id . "'");
    $categories_products = tep_db_fetch_array($categories_products_query);
    if ($categories_products['total'] > 0) {
      $category_depth = 'products'; // display products
    } else {
      $category_parent_query = tep_db_query("select count(*) as total from " . TABLE_CATEGORIES . " where parent_id = '" . (int)$current_category_id . "'");
      $category_parent = tep_db_fetch_array($category_parent_query);
      if ($category_parent['total'] > 0) {
        $category_depth = 'nested'; // navigate through the categories
      } else {
        $category_depth = 'products'; // category has no products, but display the 'no products' message
      }
    }
  }
  
// BOF: Information Pages Unlimited
  require_once(DIR_WS_FUNCTIONS . 'information.php');
  //tep_information_customer_greeting_define();
// EOF: Information Pages Unlimited  

  require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_DEFAULT);

  require(DIR_WS_INCLUDES . 'template_top.php');

  if ($category_depth == 'nested') {
    $category_query = tep_db_query("select cd.categories_name, c.categories_image from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.categories_id = '" . (int)$current_category_id . "' and cd.categories_id = '" . (int)$current_category_id . "' and cd.language_id = '" . (int)$languages_id . "'");
    $category = tep_db_fetch_array($category_query);
?>

<h1><?php echo $category['categories_name']; ?></h1>

<div class="contentContainer">
  <div class="contentText">
    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
<?php
    if (isset($cPath) && strpos('_', $cPath)) {
// check to see if there are deeper categories within the current category
      $category_links = array_reverse($cPath_array);
      for($i=0, $n=sizeof($category_links); $i<$n; $i++) {
        $categories_query = tep_db_query("select count(*) as total from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.parent_id = '" . (int)$category_links[$i] . "' and c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "'");
        $categories = tep_db_fetch_array($categories_query);
        if ($categories['total'] < 1) {
          // do nothing, go through the loop
        } else {
          $categories_query = tep_db_query("select c.categories_id, cd.categories_name, c.categories_image, c.parent_id from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.parent_id = '" . (int)$category_links[$i] . "' and c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "' order by sort_order, cd.categories_name");
          break; // we've found the deepest category the customer is in
        }
      }
    } else {
      $categories_query = tep_db_query("select c.categories_id, cd.categories_name, c.categories_image, c.parent_id from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.parent_id = '" . (int)$current_category_id . "' and c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "' order by sort_order, cd.categories_name");
    }

    $number_of_categories = tep_db_num_rows($categories_query);

    $rows = 0;
    while ($categories = tep_db_fetch_array($categories_query)) {
      $rows++;
      $cPath_new = tep_get_path($categories['categories_id']);
      $width = (int)(100 / MAX_DISPLAY_CATEGORIES_PER_ROW) . '%';
      echo '        <td align="center" class="smallText" width="' . $width . '" valign="top"><a href="' . tep_href_link(FILENAME_DEFAULT, $cPath_new) . '">' . tep_image(DIR_WS_IMAGES . $categories['categories_image'], $categories['categories_name'], SUBCATEGORY_IMAGE_WIDTH, SUBCATEGORY_IMAGE_HEIGHT) . '<br />' . $categories['categories_name'] . '</a></td>' . "\n";
      if ((($rows / MAX_DISPLAY_CATEGORIES_PER_ROW) == floor($rows / MAX_DISPLAY_CATEGORIES_PER_ROW)) && ($rows != $number_of_categories)) {
        echo '      </tr>' . "\n";
        echo '      <tr>' . "\n";
      }
    }

// needed for the new products module shown below
    $new_products_category_id = $current_category_id;
?>
      </tr>
    </table>
    <!-- category description -->
    <table border="0" width="100%" cellspacing="0" cellpadding="0">
      <tr>
        <td class="productListing-data">
<?php
    echo $categories_desc['categories_description'];
?>
        </td>
        <td class="pageHeading" align="right">
<?php
    if (tep_not_null($category['categories_image']) && (file_exists(DIR_WS_IMAGES . $category['categories_image'])) ) {
      echo tep_image(DIR_WS_IMAGES . $category['categories_image'], $category['categories_name'], HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT);
    }
?>
        </td>
      </tr>
    </table>
    <!--  -->
    <br />

<?php include(DIR_WS_MODULES . FILENAME_NEW_PRODUCTS); ?>

  </div>
</div>

<?php
  } elseif ($category_depth == 'products' || isset($HTTP_GET_VARS['manufacturers_id'])) {
// create column list
if (SHOW_BUTTON == 'True') { 
    $define_list = array('PRODUCT_LIST_MODEL' => PRODUCT_LIST_MODEL,
                         'PRODUCT_LIST_NAME' => PRODUCT_LIST_NAME,
                         'PRODUCT_LIST_MANUFACTURER' => PRODUCT_LIST_MANUFACTURER,
                         'PRODUCT_LIST_PRICE' => PRODUCT_LIST_PRICE,
                         'PRODUCT_LIST_QUANTITY' => PRODUCT_LIST_QUANTITY,
                         'PRODUCT_LIST_WEIGHT' => PRODUCT_LIST_WEIGHT,
                         'PRODUCT_LIST_IMAGE' => PRODUCT_LIST_IMAGE,
                         'PRODUCT_LIST_BUY_NOW' => PRODUCT_LIST_BUY_NOW,
						 'PRODUCT_SORT_ORDER' => PRODUCT_SORT_ORDER	);
}
else {
$define_list = array('PRODUCT_LIST_MODEL' => PRODUCT_LIST_MODEL,
                         'PRODUCT_LIST_NAME' => PRODUCT_LIST_NAME,
                         'PRODUCT_LIST_MANUFACTURER' => PRODUCT_LIST_MANUFACTURER,
                         'PRODUCT_LIST_PRICE' => PRODUCT_LIST_PRICE,
                         'PRODUCT_LIST_QUANTITY' => PRODUCT_LIST_QUANTITY,
                         'PRODUCT_LIST_WEIGHT' => PRODUCT_LIST_WEIGHT,
                         'PRODUCT_LIST_IMAGE' => PRODUCT_LIST_IMAGE,
						 'PRODUCT_SORT_ORDER' => PRODUCT_SORT_ORDER);

}
    asort($define_list);

    $column_list = array();
    reset($define_list);
    while (list($key, $value) = each($define_list)) {
      if ($value > 0) $column_list[] = $key;
    }

    $select_column_list = '';

    for ($i=0, $n=sizeof($column_list); $i<$n; $i++) {
      switch ($column_list[$i]) {
        case 'PRODUCT_LIST_MODEL':
          $select_column_list .= 'p.products_model, ';
          break;
        case 'PRODUCT_LIST_NAME':
          $select_column_list .= 'pd.products_name, ';
          break;
        case 'PRODUCT_LIST_MANUFACTURER':
          $select_column_list .= 'm.manufacturers_name, ';
          break;
        case 'PRODUCT_LIST_QUANTITY':
          $select_column_list .= 'p.products_quantity, ';
          break;
        case 'PRODUCT_LIST_IMAGE':
          $select_column_list .= 'p.products_image, ';
          break;
        case 'PRODUCT_LIST_WEIGHT':
          $select_column_list .= 'p.products_weight, ';
          break;
      }
    }

    
    // begin Extra Product Fields
    function get_osc_category_children($parent_id) {
      $cat_list = array($parent_id);
      $query = tep_db_query('select categories_id from ' . TABLE_CATEGORIES . ' where parent_id = ' . (int)$parent_id);
      while ($cat = tep_db_fetch_array($query)) {
        $children = get_osc_category_children($cat['categories_id']);
        $cat_list = array_merge($cat_list, $children);
      }
      return $cat_list;
    }
    if (isset($HTTP_GET_VARS['manufacturers_id']) && isset($HTTP_GET_VARS['filter_id']) && tep_not_null($HTTP_GET_VARS['filter_id'])) {
      $current_cat = $HTTP_GET_VARS['filter_id'];
    } else {
      $current_cat = $current_category_id;
    }
    $epf_query = tep_db_query("select * from " . TABLE_EPF . " e join " . TABLE_EPF_LABELS . " l where e.epf_status and (e.epf_show_in_listing or e.epf_use_to_restrict_listings) and (e.epf_id = l.epf_id) and (l.languages_id = " . (int)$languages_id . ") and l.epf_active_for_language order by e.epf_order");
    $epf = array();
    while ($e = tep_db_fetch_array($epf_query)) {
      $field = 'extra_value';
      if ($e['epf_uses_value_list']) {
        if ($e['epf_multi_select']) {
          $field .= '_ms';
        } else {
          $field .= '_id';
        }
      }
      $field .= $e['epf_id'];
      if ($e['epf_all_categories'] || ($current_cat == 0)) {
        $hidden_field = false;
      } else {
        $hidden_field = true;
        $base_categories = explode('|', $e['epf_category_ids']);
        $all_epf_categories = array();
        foreach ($base_categories as $cat) {
          $children = get_osc_category_children($cat);
          $all_epf_categories = array_merge($all_epf_categories, $children);
        }
        if (in_array($current_cat, $all_epf_categories)) $hidden_field = false;
      }
      $epf[] = array('id' => $e['epf_id'],
                     'label' => $e['epf_label'],
                     'uses_list' => $e['epf_uses_value_list'],
                     'show_chain' => $e['epf_show_parent_chain'],
                     'restrict' => ($hidden_field ? false : $e['epf_use_to_restrict_listings']),
                     'listing' => $e['epf_show_in_listing'],
                     'multi_select' => $e['epf_multi_select'],
                     'field' => $field);
      $select_column_list .= 'pd.' . $field . ', ';
    }
// end Extra Product Fields
    
    
// show the products of a specified manufacturer
    if (isset($HTTP_GET_VARS['manufacturers_id'])) {
      if (isset($HTTP_GET_VARS['filter_id']) && tep_not_null($HTTP_GET_VARS['filter_id'])) {
// We are asked to show only a specific category
        $listing_sql = "select " . $select_column_list . " p.products_id, p.manufacturers_id, p.products_price, p.products_tax_class_id, p.products_sort_order, IF(s.status, s.specials_new_products_price, NULL) as specials_new_products_price, IF(s.status, s.specials_new_products_price, p.products_price) as final_price from " . TABLE_PRODUCTS . " p left join " . TABLE_SPECIALS . " s on p.products_id = s.products_id, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_MANUFACTURERS . " m, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p.products_status = '1' and p.manufacturers_id = m.manufacturers_id and m.manufacturers_id = '" . (int)$HTTP_GET_VARS['manufacturers_id'] . "' and p.products_id = p2c.products_id and pd.products_id = p2c.products_id and pd.language_id = '" . (int)$languages_id . "' and p2c.categories_id = '" . (int)$HTTP_GET_VARS['filter_id'] . "'";
      } else {
// We show them all
        $listing_sql = "select " . $select_column_list . " p.products_id, p.manufacturers_id, p.products_price, p.products_tax_class_id, p.products_sort_order, IF(s.status, s.specials_new_products_price, NULL) as specials_new_products_price, IF(s.status, s.specials_new_products_price, p.products_price) as final_price from " . TABLE_PRODUCTS . " p left join " . TABLE_SPECIALS . " s on p.products_id = s.products_id, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_MANUFACTURERS . " m where p.products_status = '1' and pd.products_id = p.products_id and pd.language_id = '" . (int)$languages_id . "' and p.manufacturers_id = m.manufacturers_id and m.manufacturers_id = '" . (int)$HTTP_GET_VARS['manufacturers_id'] . "'";
      }
    } else {
// show the products in a given categorie
      if (isset($HTTP_GET_VARS['filter_id']) && tep_not_null($HTTP_GET_VARS['filter_id'])) {
// We are asked to show only specific catgeory
        $listing_sql = "select " . $select_column_list . " p.products_id, p.manufacturers_id, p.products_price, p.products_tax_class_id, p.products_sort_order, IF(s.status, s.specials_new_products_price, NULL) as specials_new_products_price, IF(s.status, s.specials_new_products_price, p.products_price) as final_price from " . TABLE_PRODUCTS . " p left join " . TABLE_SPECIALS . " s on p.products_id = s.products_id, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_MANUFACTURERS . " m, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p.products_status = '1' and p.manufacturers_id = m.manufacturers_id and m.manufacturers_id = '" . (int)$HTTP_GET_VARS['filter_id'] . "' and p.products_id = p2c.products_id and pd.products_id = p2c.products_id and pd.language_id = '" . (int)$languages_id . "' and p2c.categories_id = '" . (int)$current_category_id . "'";
      } else {
// We show them all
        $listing_sql = "select " . $select_column_list . " p.products_id, p.manufacturers_id, p.products_price, p.products_tax_class_id, p.products_sort_order, IF(s.status, s.specials_new_products_price, NULL) as specials_new_products_price, IF(s.status, s.specials_new_products_price, p.products_price) as final_price from " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS . " p left join " . TABLE_MANUFACTURERS . " m on p.manufacturers_id = m.manufacturers_id left join " . TABLE_SPECIALS . " s on p.products_id = s.products_id, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p.products_status = '1' and p.products_id = p2c.products_id and pd.products_id = p2c.products_id and pd.language_id = '" . (int)$languages_id . "' and p2c.categories_id = '" . (int)$current_category_id . "'";
        
   // begin extra product fields
    $restrict_by = '';
    foreach ($epf as $e) {
      if ($e['restrict']) {
        if (isset($HTTP_GET_VARS[$e['field']]) && is_numeric($HTTP_GET_VARS[$e['field']])) {
          $restrict_by .= ' and (pd.' . $e['field'] . ' in (' . (int)$HTTP_GET_VARS[$e['field']] . tep_list_epf_children($HTTP_GET_VARS[$e['field']]) . '))';
        }
      }       
    }
    $listing_sql .= $restrict_by;
// end extra product fields
        
      }
    }

    if ( isset($HTTP_GET_VARS['sort']) ) {
      

      switch ($HTTP_GET_VARS['sort']) {
        case 'products_new':
         $listing_sql .= " order by p.products_date_added  desc" ;
          break;
        case 'products_old':
          $listing_sql .= " order by p.products_date_added  asc" ;
          break;
        case 'products_viewed':
         $listing_sql .= " order by p.products_ordered desc, pd.products_name";
          break;
        case 'products_ordered':
          $listing_sql .= " order by p.products_ordered desc, pd.products_name";
          break;
        case 'products_price1':
         $listing_sql .= " order by final_price   asc , pd.products_name";
          break;
        case 'products_price2':
          $listing_sql .= " order by final_price   desc , pd.products_name";
          break;
        case 'products_name':
          $listing_sql .= " order by   pd.products_name  ";
          break;
		  
// BOF Product Sort	
		case 'products_sort_order':
          $listing_sql .= " order by  p.products_sort_order  , pd.products_name";
          break;
// EOF Product Sort
      }
    } else {
			$listing_sql .= " order by  p.products_sort_order  , pd.products_name";
	}

    $catname = HEADING_TITLE;
    if (isset($HTTP_GET_VARS['manufacturers_id'])) {
      $image = tep_db_query("select manufacturers_image, manufacturers_name as catname from " . TABLE_MANUFACTURERS . " where manufacturers_id = '" . (int)$HTTP_GET_VARS['manufacturers_id'] . "'");
      $image = tep_db_fetch_array($image);
      $catname = $image['catname'];
    } elseif ($current_category_id) {
      $image = tep_db_query("select c.categories_image, cd.categories_name as catname from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.categories_id = '" . (int)$current_category_id . "' and c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "'");
      $image = tep_db_fetch_array($image);
      $catname = $image['catname'];
    }
?>

<h1><?php echo $catname; ?></h1>

<!--  category description  -->
    <table border="0" width="100%" cellspacing="0" cellpadding="0">
      <tr>
        <td class="productListing-data">
<?php
    echo $categories_desc['categories_description'];
?>
        </td>
        <td class="pageHeading" align="right">
<?php
    if (tep_not_null($category['categories_image']) && (file_exists(DIR_WS_IMAGES . $category['categories_image'])) ) {
      echo tep_image(DIR_WS_IMAGES . $category['categories_image'], $category['categories_name'], HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT);
    }
?>
        </td>
      </tr>
    </table>
    
<!--  ends here -->    

<div class="contentContainer">

<?php


// begin extra product fields ?>
 <?php
          if ( isset($epf) ) {
            $epf_list = array();
            foreach ($epf as $e) {
              if ($e['restrict']) $epf_list[] = $e['field'];
            }
            echo tep_draw_form('epf_restrict', FILENAME_DEFAULT, 'get');
            if (is_array($HTTP_GET_VARS) && (sizeof($HTTP_GET_VARS) > 0)) {
              reset($HTTP_GET_VARS);
              while (list($key, $value) = each($HTTP_GET_VARS)) {
                if ( (strlen($value) > 0) && ($key != tep_session_name()) && (!in_array($key, $epf_list)) ) {
                  echo tep_draw_hidden_field($key, $value);
                }
              }
            }
            foreach ($epf as $e) {
              if ($e['restrict']) {
                echo sprintf(TEXT_RESTRICT_TO, $e['label'], tep_draw_pull_down_menu($e['field'], tep_build_epf_pulldown($e['id'], $languages_id, array(array('id' => '', 'text' => TEXT_ANY_VALUE))),'', 'onchange="this.form.submit()"')) . '<br />';
              }
            }
          ?>
          </form>
          <?php
          }
          ?>
<!-- end extra product fields -->
<?php
	 
/* if ( ( isset($_GET['view']) && $_GET['view'] == 'grid' && ( PRODUCT_LIST_CONTENT_LISTING == 'both' || PRODUCT_LIST_CONTENT_LISTING == 'grid')) || ( PRODUCT_LIST_CONTENT_LISTING_DEFAULT == "grid" && $_GET['view']=="" && ( PRODUCT_LIST_CONTENT_LISTING == 'both' || PRODUCT_LIST_CONTENT_LISTING == 'grid')) ) {
    	include(DIR_WS_MODULES . FILENAME_PRODUCT_LISTING_COLUMN);
    } else {
      include(DIR_WS_MODULES . FILENAME_PRODUCT_LISTING_ROW);
    }*/
	if( $_GET['view']=="" &&     (PRODUCT_LIST_CONTENT_LISTING == 'grid')) {
		include(DIR_WS_MODULES . FILENAME_PRODUCT_LISTING_COLUMN);
	}
	 else if (  $_GET['view']=="" &&   PRODUCT_LIST_CONTENT_LISTING_DEFAULT == "list" &&  (PRODUCT_LIST_CONTENT_LISTING == 'both' ) ) 			    { 
		include(DIR_WS_MODULES . FILENAME_PRODUCT_LISTING_ROW);
	} else if (  $_GET['view']=="" &&   PRODUCT_LIST_CONTENT_LISTING_DEFAULT == "grid" &&  (PRODUCT_LIST_CONTENT_LISTING == 'both' ) ) 			    { 
		include(DIR_WS_MODULES . FILENAME_PRODUCT_LISTING_COLUMN);
	}
	else if( $_GET['view']=="" &&    PRODUCT_LIST_CONTENT_LISTING == 'list') {
		include(DIR_WS_MODULES . FILENAME_PRODUCT_LISTING_ROW);
	} 
	 else if($_GET['view']=="grid") {
		include(DIR_WS_MODULES . FILENAME_PRODUCT_LISTING_COLUMN);
	}else if($_GET['view']=="list") {
		include(DIR_WS_MODULES . FILENAME_PRODUCT_LISTING_ROW);
	} else {
	include(DIR_WS_MODULES . FILENAME_PRODUCT_LISTING_ROW);
	}
    
	 
?>

</div>

<?php
  } else {
  
  // Start Modular Front Page
?>
<?php require(DIR_WS_INCLUDES . 'slideshow.php'); 

if( !defined('MODULE_FRONT_PAGE_SPECIALS_STATUS') && !defined( 'MODULE_FRONT_PAGE_HEADING_TITLE_STATUS' ) && !defined('MODULE_FRONT_PAGE_NEW_PRODUCTS_STATUS') && !defined('MODULE_FRONT_PAGE_CUSTOMER_GREETING_STATUS') && !defined('MODULE_FRONT_PAGE_CATEGORIES_IMAGES_STATUS') && !defined('MODULE_FRONT_PAGE_SCROLLER_STATUS') && !defined('MODULE_FRONT_PAGE_TEXT_MAIN_STATUS') && !defined('MODULE_FRONT_PAGE_FEATURED_STATUS') && !defined('MODULE_FRONT_PAGE_UPCOMING_PRODUCTS_STATUS')){
?>
<?php /*?><h1><?php echo HEADING_TITLE; ?></h1><?php */?>

<div class="contentContainer">
  <div class="contentText">
      <?php echo tep_customer_greeting(); ?>
  </div>

<?php
    if (tep_not_null(TEXT_MAIN)) {
?>

  <div class="contentText">
    <?php echo TEXT_MAIN; ?>
  </div>

<?php
    }

    include(DIR_WS_MODULES . FILENAME_NEW_PRODUCTS);
    include(DIR_WS_MODULES . FILENAME_UPCOMING_PRODUCTS);
?>

</div>

<?php
  }
    
else {
?>


<div class="contentContainer">

<?php echo $oscTemplate->getBlocks('front_page'); ?>

</div>

<?php
  // End Modular Front Page
  

  }

  }

  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>

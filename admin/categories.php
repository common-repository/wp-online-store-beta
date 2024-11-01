<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');
  //if(isset($_POST['cPath']))
  	//echo $HTTP_GET_VARS['cPath']=$_POST['cPath'];
  	

  require(DIR_WS_CLASSES . 'currencies.php');
  $currencies = new currencies();

  $action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');
// begin Extra Product Fields
  function get_exclude_list($value_id) {
    $exclude_list = array();
    $query = tep_db_query('select value_id1 from ' . TABLE_EPF_EXCLUDE . ' where value_id2 = ' . (int)$value_id);
    while ($check = tep_db_fetch_array($query)) {
      $exclude_list[] = $check['value_id1'];
    }
    $query = tep_db_query('select value_id2 from ' . TABLE_EPF_EXCLUDE . ' where value_id1 = ' . (int)$value_id);
    while ($check = tep_db_fetch_array($query)) {
      $exclude_list[] = $check['value_id2'];
    }
    return $exclude_list;
  }
  function get_osc_children($value_id) {
    return explode(',', $value_id . tep_list_epf_children($value_id));
  }
  function get_parent_list($value_id) {
    $sql = tep_db_query("select parent_id from " . TABLE_EPF_VALUES . " where value_id = " . (int)$value_id);
    $value = tep_db_fetch_array($sql);
    if ($value['parent_id'] > 0) {
      return get_parent_list($value['parent_id']) . ',' . $value_id;
    } else {
      return $value_id;
    }
  }
  function get_osc_category_children($parent_id) {
    $cat_list = array($parent_id);
    $query = tep_db_query('select categories_id from ' . TABLE_CATEGORIES . ' where parent_id = ' . (int)$parent_id);
    while ($cat = tep_db_fetch_array($query)) {
      $children = get_osc_category_children($cat['categories_id']);
      $cat_list = array_merge($cat_list, $children);
    }
    return $cat_list;
  }
  // get categories for current product
  $product_categories = array($HTTP_GET_VARS['cPath']);
  if (($action == 'new_product') && isset($HTTP_GET_VARS['pID'])) {
    $query = tep_db_query('select categories_id from ' . TABLE_PRODUCTS_TO_CATEGORIES . ' where products_id = ' . (int)$HTTP_GET_VARS['pID']);
    while ($cat = tep_db_fetch_array($query)) {
      $product_categories[] = $cat['categories_id'];
    }    
  }
  $epf_query = tep_db_query("select * from " . TABLE_EPF . " e join " . TABLE_EPF_LABELS . " l where (e.epf_status or e.epf_show_in_admin) and (e.epf_id = l.epf_id) order by e.epf_order");
  $epf = array();
  $xfields = array();
  $link_groups = array();
  $linked_fields = array();
  while ($e = tep_db_fetch_array($epf_query)) {  // retrieve all active extra fields for all languages
    $field = 'extra_value';
    if ($e['epf_uses_value_list']) {
      if ($e['epf_multi_select']) {
        $field .= '_ms';
      } else {
        $field .= '_id';
      }
    }
    $field .= $e['epf_id'];
    $values = '';
    if ($e['epf_uses_value_list'] && $e['epf_active_for_language'] && ($e['epf_has_linked_field'] || $e['epf_multi_select'])) { // if field requires javascript during entry
      $values = array();
      $value_query = tep_db_query('select value_id, value_depends_on from ' . TABLE_EPF_VALUES . ' where epf_id = ' . (int)$e['epf_id'] . ' and languages_id = ' . (int)$e['languages_id']);
      while ($v = tep_db_fetch_array($value_query)) {
        $values[] = $v['value_id'];
        if ($e['epf_has_linked_field'] && $e['epf_multi_select'] && ($v['value_depends_on'] != 0)) {
          $linked_fields[$e['epf_links_to']][$e['languages_id']][$v['value_depends_on']][] = $v['value_id'];
          if (!in_array($v['value_depends_on'], $link_groups[$e['epf_links_to']][$e['languages_id']])) $link_groups[$e['epf_links_to']][$e['languages_id']][] = $v['value_depends_on'];
        }
      }
    }
    if ($e['epf_all_categories']) {
      $hidden_field = false;
    } else {
      $hidden_field = true;
      $base_categories = explode('|', $e['epf_category_ids']);
      $all_epf_categories = array();
      foreach ($base_categories as $cat) {
        $children = get_osc_category_children($cat);
        $all_epf_categories = array_merge($all_epf_categories, $children);
      }
      foreach ($all_epf_categories as $cat) {
        if (in_array($cat, $product_categories)) $hidden_field = false;
      }
    }
    $epf[] = array('id' => $e['epf_id'],
                   'label' => $e['epf_label'],
                   'uses_list' => $e['epf_uses_value_list'],
                   'multi_select' => $e['epf_multi_select'],
                   'show_chain' => $e['epf_show_parent_chain'],
                   'checkbox' => $e['epf_checked_entry'],
                   'display_type' => $e['epf_value_display_type'],
                   'columns' => $e['epf_num_columns'],
                   'linked' => $e['epf_has_linked_field'],
                   'links_to' => $e['epf_links_to'],
                   'size' => $e['epf_size'],
                   'language' => $e['languages_id'],
                   'language_active' => $e['epf_active_for_language'],
                   'values' => $values,
                   'textarea' => $e['epf_textarea'],
                   'field' => $field,
                   'hidden' => $hidden_field);
    if (!in_array( $field, $xfields))
      $xfields[] = $field; // build list of distinct fields    
  }
// end Extra Product Fields
  
  if (tep_not_null($action)) {
    switch ($action) {
      case 'setflag':
        if ( ($HTTP_GET_VARS['flag'] == '0') || ($HTTP_GET_VARS['flag'] == '1') ) {
          if (isset($HTTP_GET_VARS['pID'])) {
            tep_set_product_status($HTTP_GET_VARS['pID'], $HTTP_GET_VARS['flag']);
          }

          if (USE_CACHE == 'true') {
            tep_reset_cache_block('categories');
            tep_reset_cache_block('also_purchased');
          }
        }

        tep_redirect(tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $HTTP_GET_VARS['cPath'] . '&pID=' . $HTTP_GET_VARS['pID']));
        break;
      case 'insert_category':
      case 'update_category':
        if (isset($HTTP_POST_VARS['categories_id'])) $categories_id = tep_db_prepare_input($HTTP_POST_VARS['categories_id']);
        $sort_order = tep_db_prepare_input($HTTP_POST_VARS['sort_order']);

        $sql_data_array = array('sort_order' => (int)$sort_order);
		// BOF Minimum Category Tree Quantity
			$categories_min_quan = tep_db_prepare_input($HTTP_POST_VARS['categories_min_quan']);
			$sql_data_array = array('categories_min_quan' => $categories_min_quan);
		// EOF Minimum Category Tree Quantity
        if ($action == 'insert_category') {
          $insert_sql_data = array('parent_id' => $current_category_id,
                                   'date_added' => 'now()');

          $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

          tep_db_perform(TABLE_CATEGORIES, $sql_data_array);

          $categories_id = tep_db_insert_id();
        } elseif ($action == 'update_category') {
          $update_sql_data = array('last_modified' => 'now()');

          $sql_data_array = array_merge($sql_data_array, $update_sql_data);

          tep_db_perform(TABLE_CATEGORIES, $sql_data_array, 'update', "categories_id = '" . (int)$categories_id . "'");
        }

        $languages = tep_get_languages();
        for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
        /*
          $categories_name_array = $HTTP_POST_VARS['categories_name'];

          $language_id = $languages[$i]['id'];

          $sql_data_array = array('categories_name' => tep_db_prepare_input($categories_name_array[$language_id])); */
        
        /*** Begin Header Tags SEO ***/
          $categories_htc_title_array = $HTTP_POST_VARS['categories_htc_title_tag'];
          $categories_htc_desc_array = $HTTP_POST_VARS['categories_htc_desc_tag'];
          $categories_htc_keywords_array = $HTTP_POST_VARS['categories_htc_keywords_tag'];
          //$categories_htc_description_array = $HTTP_POST_VARS['categories_htc_description_tag'];
          //$categories_seo_url_array = $HTTP_POST_VARS['categories_seo_url'];
        
         $categories_name_array = $HTTP_POST_VARS['categories_name'];
          $categories_description_array = $HTTP_POST_VARS['categories_description'];

          $language_id = $languages[$i]['id'];

          $sql_data_array = array('categories_name' => tep_db_prepare_input($categories_name_array[$language_id]),
                                  'categories_description' => tep_db_prepare_input($categories_description_array[$language_id]),
          'categories_htc_title_tag' => (tep_not_null($categories_htc_title_array[$language_id]) ? tep_db_prepare_input(strip_tags($categories_htc_title_array[$language_id])) :  tep_db_prepare_input(strip_tags($categories_name_array[$language_id]))),
           'categories_htc_desc_tag' => (tep_not_null($categories_htc_desc_array[$language_id]) ? tep_db_prepare_input($categories_htc_desc_array[$language_id]) :  tep_db_prepare_input($categories_name_array[$language_id])),
           'categories_htc_keywords_tag' => (tep_not_null($categories_htc_keywords_array[$language_id]) ? tep_db_prepare_input(strip_tags($categories_htc_keywords_array[$language_id])) :  tep_db_prepare_input(strip_tags($categories_name_array[$language_id])))
         // 'categories_seo_url' => tep_db_prepare_input($categories_seo_url_array[$language_id])
          );
		
          if ($action == 'insert_category') {
            $insert_sql_data = array('categories_id' => $categories_id,
                                     'language_id' => $languages[$i]['id']);

            $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

            tep_db_perform(TABLE_CATEGORIES_DESCRIPTION, $sql_data_array);
          } elseif ($action == 'update_category') {
            tep_db_perform(TABLE_CATEGORIES_DESCRIPTION, $sql_data_array, 'update', "categories_id = '" . (int)$categories_id . "' and language_id = '" . (int)$languages[$i]['id'] . "'");
          }
        }

        $categories_image = new upload('categories_image');
        $categories_image->set_destination(DIR_FS_CATALOG_IMAGES);

        if ($categories_image->parse() && $categories_image->save()) {
          tep_db_query("update " . TABLE_CATEGORIES . " set categories_image = '" . tep_db_input($categories_image->filename) . "' where categories_id = '" . (int)$categories_id . "'");
        }

        if (USE_CACHE == 'true') {
          tep_reset_cache_block('categories');
          tep_reset_cache_block('also_purchased');
        }

        tep_redirect(tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&cID=' . $categories_id));
        break;
      case 'delete_category_confirm':
        if (isset($HTTP_POST_VARS['categories_id'])) {
          $categories_id = tep_db_prepare_input($HTTP_POST_VARS['categories_id']);

          $categories = tep_get_category_tree($categories_id, '', '0', '', true);
          $products = array();
          $products_delete = array();

          for ($i=0, $n=sizeof($categories); $i<$n; $i++) {
            $product_ids_query = tep_db_query("select products_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where categories_id = '" . (int)$categories[$i]['id'] . "'");

            while ($product_ids = tep_db_fetch_array($product_ids_query)) {
              $products[$product_ids['products_id']]['categories'][] = $categories[$i]['id'];
            }
          }

          reset($products);
          while (list($key, $value) = each($products)) {
            $category_ids = '';

            for ($i=0, $n=sizeof($value['categories']); $i<$n; $i++) {
              $category_ids .= "'" . (int)$value['categories'][$i] . "', ";
            }
            $category_ids = substr($category_ids, 0, -2);

            $check_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int)$key . "' and categories_id not in (" . $category_ids . ")");
            $check = tep_db_fetch_array($check_query);
            if ($check['total'] < '1') {
              $products_delete[$key] = $key;
            }
          }

// removing categories can be a lengthy process
          tep_set_time_limit(0);
          for ($i=0, $n=sizeof($categories); $i<$n; $i++) {
            tep_remove_category($categories[$i]['id']);
          }

          reset($products_delete);
          while (list($key) = each($products_delete)) {
            tep_remove_product($key);
          }
        }

        if (USE_CACHE == 'true') {
          tep_reset_cache_block('categories');
          tep_reset_cache_block('also_purchased');
        }

        tep_redirect(tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath));
        break;
      case 'delete_product_confirm':
        if (isset($HTTP_POST_VARS['products_id']) && isset($HTTP_POST_VARS['product_categories']) && is_array($HTTP_POST_VARS['product_categories'])) {
          $product_id = tep_db_prepare_input($HTTP_POST_VARS['products_id']);
          $product_categories = $HTTP_POST_VARS['product_categories'];

          for ($i=0, $n=sizeof($product_categories); $i<$n; $i++) {
            tep_db_query("delete from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int)$product_id . "' and categories_id = '" . (int)$product_categories[$i] . "'");
          }

          $product_categories_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int)$product_id . "'");
          $product_categories = tep_db_fetch_array($product_categories_query);

          if ($product_categories['total'] == '0') {
            tep_remove_product($product_id);
          }
        }

        if (USE_CACHE == 'true') {
          tep_reset_cache_block('categories');
          tep_reset_cache_block('also_purchased');
        }

        tep_redirect(tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath));
        break;
      case 'move_category_confirm':
        if (isset($HTTP_POST_VARS['categories_id']) && ($HTTP_POST_VARS['categories_id'] != $HTTP_POST_VARS['move_to_category_id'])) {
          $categories_id = tep_db_prepare_input($HTTP_POST_VARS['categories_id']);
          $new_parent_id = tep_db_prepare_input($HTTP_POST_VARS['move_to_category_id']);

          $path = explode('_', tep_get_generated_category_path_ids($new_parent_id));

          if (in_array($categories_id, $path)) {
            $messageStack->add_session(ERROR_CANNOT_MOVE_CATEGORY_TO_PARENT, 'error');

            tep_redirect(tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&cID=' . $categories_id));
          } else {
            tep_db_query("update " . TABLE_CATEGORIES . " set parent_id = '" . (int)$new_parent_id . "', last_modified = now() where categories_id = '" . (int)$categories_id . "'");

            if (USE_CACHE == 'true') {
              tep_reset_cache_block('categories');
              tep_reset_cache_block('also_purchased');
            }

            tep_redirect(tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $new_parent_id . '&cID=' . $categories_id));
          }
        }

        break;
      case 'move_product_confirm':
        $products_id = tep_db_prepare_input($HTTP_POST_VARS['products_id']);
        $new_parent_id = tep_db_prepare_input($HTTP_POST_VARS['move_to_category_id']);

        $duplicate_check_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int)$products_id . "' and categories_id = '" . (int)$new_parent_id . "'");
        $duplicate_check = tep_db_fetch_array($duplicate_check_query);
        if ($duplicate_check['total'] < 1) tep_db_query("update " . TABLE_PRODUCTS_TO_CATEGORIES . " set categories_id = '" . (int)$new_parent_id . "' where products_id = '" . (int)$products_id . "' and categories_id = '" . (int)$current_category_id . "'");

        if (USE_CACHE == 'true') {
          tep_reset_cache_block('categories');
          tep_reset_cache_block('also_purchased');
        }

        tep_redirect(tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $new_parent_id . '&pID=' . $products_id));
        break;
      case 'insert_product':
      case 'update_product':
        if (isset($HTTP_GET_VARS['pID'])) $products_id = tep_db_prepare_input($HTTP_GET_VARS['pID']);
        $products_date_available = tep_db_prepare_input($HTTP_POST_VARS['products_date_available']);

        $products_date_available = (date('Y-m-d') < $products_date_available) ? $products_date_available : 'null';

        $sql_data_array = array('products_quantity' => (int)tep_db_prepare_input($HTTP_POST_VARS['products_quantity']),
                                'products_model' => tep_db_prepare_input($HTTP_POST_VARS['products_model']),
                                'products_price' => tep_db_prepare_input($HTTP_POST_VARS['products_price']),
                                'products_date_available' => $products_date_available,
                                'products_weight' => (float)tep_db_prepare_input($HTTP_POST_VARS['products_weight']),
                                'products_status' => tep_db_prepare_input($HTTP_POST_VARS['products_status']),
                                'products_tax_class_id' => tep_db_prepare_input($HTTP_POST_VARS['products_tax_class_id']),
        						'products_seo_url' => tep_db_prepare_input($HTTP_POST_VARS['products_seo_url']),
                                'manufacturers_id' => (int)tep_db_prepare_input($HTTP_POST_VARS['manufacturers_id']),
								// BOF product sort	  
								  'products_sort_order' => tep_db_prepare_input($HTTP_POST_VARS['products_sort_order']) 
								// EOF product sort
								);

        $products_image = new upload('products_image');
        $products_image->set_destination(DIR_FS_CATALOG_IMAGES);
        if ($products_image->parse() && $products_image->save()) {
          $sql_data_array['products_image'] = tep_db_prepare_input($products_image->filename);
        }

        if ($action == 'insert_product') {
          $insert_sql_data = array('products_date_added' => 'now()');

          $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

          tep_db_perform(TABLE_PRODUCTS, $sql_data_array);
          $products_id = tep_db_insert_id();

          tep_db_query("insert into " . TABLE_PRODUCTS_TO_CATEGORIES . " (products_id, categories_id) values ('" . (int)$products_id . "', '" . (int)$current_category_id . "')");
        } elseif ($action == 'update_product') {
          $update_sql_data = array('products_last_modified' => 'now()');

          $sql_data_array = array_merge($sql_data_array, $update_sql_data);

          tep_db_perform(TABLE_PRODUCTS, $sql_data_array, 'update', "products_id = '" . (int)$products_id . "'");
          /** AJAX Attribute Manager  **/ 
  require_once('attributeManager/includes/attributeManagerUpdateAtomic.inc.php'); 
/** AJAX Attribute Manager  end **/
        }

        $languages = tep_get_languages();
        for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
          $language_id = $languages[$i]['id'];

          $sql_data_array = array('products_name' => tep_db_prepare_input($HTTP_POST_VARS['products_name'][$language_id]),
                                  'products_description' => tep_db_prepare_input($HTTP_POST_VARS['products_description'][$language_id]),
                                  'products_url' => tep_db_prepare_input($HTTP_POST_VARS['products_url'][$language_id]),
          'products_head_title_tag' => ((tep_not_null($HTTP_POST_VARS['products_head_title_tag'][$language_id])) ? tep_db_prepare_input(strip_tags($HTTP_POST_VARS['products_head_title_tag'][$language_id])) : tep_db_prepare_input(strip_tags($HTTP_POST_VARS['products_name'][$language_id]))),
                                    'products_head_desc_tag' => ((tep_not_null($HTTP_POST_VARS['products_head_desc_tag'][$language_id])) ? tep_db_prepare_input($HTTP_POST_VARS['products_head_desc_tag'][$language_id]) : tep_db_prepare_input($HTTP_POST_VARS['products_name'][$language_id])),
                                    'products_head_keywords_tag' => ((tep_not_null($HTTP_POST_VARS['products_head_keywords_tag'][$language_id])) ? tep_db_prepare_input(strip_tags($HTTP_POST_VARS['products_head_keywords_tag'][$language_id])) : tep_db_prepare_input(strip_tags($HTTP_POST_VARS['products_name'][$language_id])))
         // 'products_seo_url' => tep_db_prepare_input($HTTP_POST_VARS['products_seo_url'][$language_id]),
          
          );
		            // begin Extra Product Fields
            foreach ($epf as $e) {
              if ($e['language'] == $language_id) {
                if ($e['language_active']) {
                  if ($e['multi_select']) {
                    if (empty($HTTP_POST_VARS[$e['field']][$language_id])) {
                      $value = 'null';
                    } else {
                      //validate multi-select values in case JavaScript was turned off and couldn't prevent errors
                      $value_list = $HTTP_POST_VARS[$e['field']][$language_id];
                      if ($e['linked']) { // validate linked values if field is linked
                        $link_validated_list = array();
                        $lv = 0;
                        foreach ($epf as $lf) {
                          if ($lf['id'] == $e['links_to']) {
                            $lv = (int)$HTTP_POST_VARS[$lf['field']][$language_id];
                          }
                        }
                        $validation_query_raw = 'select value_id from ' . TABLE_EPF_VALUES . ' where epf_id = ' . (int)$e['id'] . ' and languages_id = ' . (int)$e['language'] . ' and ';
                        if ($lv == 0) {
                          $validation_query_raw .= 'value_depends_on = 0';
                        } else {
                          $validation_query_raw .= '(value_depends_on in (0,' . get_parent_list($lv) . '))';
                        }
                        $validation_query = tep_db_query($validation_query_raw);
                        $valid_values = array();
                        while ($valid = tep_db_fetch_array($validation_query)) {
                          $valid_values[] = $valid['value_id'];
                        }
                        foreach ($value_list as $v) {
                          if (in_array($v, $valid_values)) $link_validated_list[] = $v;
                        }
                      } else {
                        $link_validated_list = $value_list;
                      }
                      $validated_value_list = array(); // validate excluded values
                      $excluded_values = array();
                      foreach ($link_validated_list as $v) {
                        if (!in_array($v, $excluded_values)) {
                          $validated_value_list[] = $v;
                          $tmp = get_exclude_list($v);
                          $excluded_values = array_merge($excluded_values, $tmp);
                        }
                      }
                      $value = '|';
                      $sort_query = tep_db_query('select value_id from ' . TABLE_EPF_VALUES . ' where epf_id = ' . (int)$e['id'] . ' and languages_id = ' . (int)$e['language'] . ' order by sort_order, epf_value');
                      while ($val = tep_db_fetch_array($sort_query)) { // store input values in sorted order
                        if (in_array($val['value_id'], $validated_value_list))
                          $value .= tep_db_prepare_input($val['value_id']) . '|';
                      }
                    }
                  } else {
                    $value = tep_db_prepare_input($HTTP_POST_VARS[$e['field']][$language_id]);
                    if ($value == '')
                      $value = (($e['uses_list'] && !$e['multi_select']) ? 0 : 'null');
                  }
                  $extra = array($e['field'] => $value);
                } else {
                  $extra = array($e['field'] => (($e['uses_list'] && !$e['multi_select']) ? 0 : 'null'));
                }
                $sql_data_array = array_merge($sql_data_array, $extra);
              }
            }
            // end Extra Product Fields
          
          if ($action == 'insert_product') {
            $insert_sql_data = array('products_id' => $products_id,
                                     'language_id' => $language_id);

            $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

            tep_db_perform(TABLE_PRODUCTS_DESCRIPTION, $sql_data_array);
          } elseif ($action == 'update_product') {
            tep_db_perform(TABLE_PRODUCTS_DESCRIPTION, $sql_data_array, 'update', "products_id = '" . (int)$products_id . "' and language_id = '" . (int)$language_id . "'");
          }
        }

        $pi_sort_order = 0;
        $piArray = array(0);

        foreach ($HTTP_POST_FILES as $key => $value) {
// Update existing large product images
          if (preg_match('/^products_image_large_([0-9]+)$/', $key, $matches)) {
            $pi_sort_order++;

            $sql_data_array = array('htmlcontent' => tep_db_prepare_input($HTTP_POST_VARS['products_image_htmlcontent_' . $matches[1]]),
                                    'sort_order' => $pi_sort_order);

            $t = new upload($key);
            $t->set_destination(DIR_FS_CATALOG_IMAGES);
            if ($t->parse() && $t->save()) {
              $sql_data_array['image'] = tep_db_prepare_input($t->filename);
            }

            tep_db_perform(TABLE_PRODUCTS_IMAGES, $sql_data_array, 'update', "products_id = '" . (int)$products_id . "' and id = '" . (int)$matches[1] . "'");

            $piArray[] = (int)$matches[1];
          } elseif (preg_match('/^products_image_large_new_([0-9]+)$/', $key, $matches)) {
// Insert new large product images
            $sql_data_array = array('products_id' => (int)$products_id,
                                    'htmlcontent' => tep_db_prepare_input($HTTP_POST_VARS['products_image_htmlcontent_new_' . $matches[1]]));

            $t = new upload($key);
            $t->set_destination(DIR_FS_CATALOG_IMAGES);
            if ($t->parse() && $t->save()) {
              $pi_sort_order++;

              $sql_data_array['image'] = tep_db_prepare_input($t->filename);
              $sql_data_array['sort_order'] = $pi_sort_order;

              tep_db_perform(TABLE_PRODUCTS_IMAGES, $sql_data_array);

              $piArray[] = tep_db_insert_id();
            }
          }
        }

        $product_images_query = tep_db_query("select image from " . TABLE_PRODUCTS_IMAGES . " where products_id = '" . (int)$products_id . "' and id not in (" . implode(',', $piArray) . ")");
        if (tep_db_num_rows($product_images_query)) {
          while ($product_images = tep_db_fetch_array($product_images_query)) {
            $duplicate_image_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_IMAGES . " where image = '" . tep_db_input($product_images['image']) . "'");
            $duplicate_image = tep_db_fetch_array($duplicate_image_query);

            if ($duplicate_image['total'] < 2) {
              if (file_exists(DIR_FS_CATALOG_IMAGES . $product_images['image'])) {
                @unlink(DIR_FS_CATALOG_IMAGES . $product_images['image']);
              }
            }
          }

          tep_db_query("delete from " . TABLE_PRODUCTS_IMAGES . " where products_id = '" . (int)$products_id . "' and id not in (" . implode(',', $piArray) . ")");
        }
			
        if(defined('MODULE_SHIPPING_INDVSHIP_STATUS') && MODULE_SHIPPING_INDVSHIP_STATUS==true){
         // start indvship
          $sql_shipping_array = array('products_ship_zip' => tep_db_prepare_input($_POST['products_ship_zip']),
'products_ship_methods_id' => tep_db_prepare_input($_POST['products_ship_methods_id']),
'products_ship_price' => round(tep_db_prepare_input($_POST['products_ship_price']),4),
'products_ship_price_two' => round(tep_db_prepare_input($_POST['products_ship_price_two']),4));
          $sql_shipping_id_array = array('products_id' => (int)$products_id); 
          $products_ship_query = tep_db_query("SELECT * FROM " . TABLE_PRODUCTS_SHIPPING . " WHERE products_id = " . (int)$products_id);
          if(tep_db_num_rows($products_ship_query) >0) {
            if (($_POST['products_ship_zip'] == '')&&($_POST['products_ship_methods_id'] == '')&&($_POST['products_ship_price'] == '')&&($_POST['products_ship_price_two'] == '')){
              tep_db_query("DELETE FROM " . TABLE_PRODUCTS_SHIPPING . " where products_id = '" . (int)$products_id . "'");
            } else {
              tep_db_perform(TABLE_PRODUCTS_SHIPPING, $sql_shipping_array, 'update', "products_id = '" . (int)$products_id . "'");
            }
          } else {
            if (($_POST['products_ship_zip'] != '')||($_POST['products_ship_methods_id'] != '')||($_POST['products_ship_price'] != '')||($_POST['products_ship_price_two'] != '')){
              $sql_ship_array = array_merge($sql_shipping_array, $sql_shipping_id_array);
              tep_db_perform(TABLE_PRODUCTS_SHIPPING, $sql_ship_array, 'insert');
            }
          }
          // end indvship
        }
        
        
        
        if (USE_CACHE == 'true') {
          tep_reset_cache_block('categories');
          tep_reset_cache_block('also_purchased');
        }

        tep_redirect(tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&pID=' . $products_id));
        break;
      case 'copy_to_confirm':
        if (isset($HTTP_POST_VARS['products_id']) && isset($HTTP_POST_VARS['categories_id'])) {
          $products_id = tep_db_prepare_input($HTTP_POST_VARS['products_id']);
          $categories_id = tep_db_prepare_input($HTTP_POST_VARS['categories_id']);

          if ($HTTP_POST_VARS['copy_as'] == 'link') {
            if ($categories_id != $current_category_id) {
              $check_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int)$products_id . "' and categories_id = '" . (int)$categories_id . "'");
              $check = tep_db_fetch_array($check_query);
              if ($check['total'] < '1') {
                tep_db_query("insert into " . TABLE_PRODUCTS_TO_CATEGORIES . " (products_id, categories_id) values ('" . (int)$products_id . "', '" . (int)$categories_id . "')");
              }
            } else {
              $messageStack->add_session(ERROR_CANNOT_LINK_TO_SAME_CATEGORY, 'error');
            }
          } elseif ($HTTP_POST_VARS['copy_as'] == 'duplicate') {
            $product_query = tep_db_query("select products_quantity, products_model, products_image, products_price, products_date_available, products_weight, products_tax_class_id, manufacturers_id, products_sort_order from " . TABLE_PRODUCTS . " where products_id = '" . (int)$products_id . "'");
            $product = tep_db_fetch_array($product_query);

            tep_db_query("insert into " . TABLE_PRODUCTS . " (products_quantity, products_model,products_image, products_price, products_date_added, products_date_available, products_weight, products_status, products_tax_class_id, manufacturers_id, products_sort_order) values ('" . tep_db_input($product['products_quantity']) . "', '" . tep_db_input($product['products_model']) . "', '" . tep_db_input($product['products_image']) . "', '" . tep_db_input($product['products_price']) . "',  now(), " . (empty($product['products_date_available']) ? "null" : "'" . tep_db_input($product['products_date_available']) . "'") . ", '" . tep_db_input($product['products_weight']) . "', '0', '" . (int)$product['products_tax_class_id'] . "', '" . (int)$product['manufacturers_id'] . "', '" . (int)$product['products_sort_order'] . "')");
            $dup_products_id = tep_db_insert_id();

//            $description_query = tep_db_query("select language_id, products_name, products_description, products_url from " . TABLE_PRODUCTS_DESCRIPTION . " where products_id = '" . (int)$products_id . "'");
//            while ($description = tep_db_fetch_array($description_query)) {
//              tep_db_query("insert into " . TABLE_PRODUCTS_DESCRIPTION . " (products_id, language_id, products_name, products_description, products_url, products_viewed) values ('" . (int)$dup_products_id . "', '" . (int)$description['language_id'] . "', '" . tep_db_input($description['products_name']) . "', '" . tep_db_input($description['products_description']) . "', '" . tep_db_input($description['products_url']) . "', '0')");
//            }

            // description copy modified to work with Extra Product Fields
            $description_query = tep_db_query("select * from " . TABLE_PRODUCTS_DESCRIPTION . " where products_id = '" . (int)$products_id . "'");
            while ($description = tep_db_fetch_array($description_query)) {
              $description['products_id'] = $dup_products_id;
              $description['products_viewed'] = 0;
              tep_db_perform(TABLE_PRODUCTS_DESCRIPTION, $description);
            }
// end Extra Product Fields
            
            
            
            $product_images_query = tep_db_query("select image, htmlcontent, sort_order from " . TABLE_PRODUCTS_IMAGES . " where products_id = '" . (int)$products_id . "'");
            while ($product_images = tep_db_fetch_array($product_images_query)) {
              tep_db_query("insert into " . TABLE_PRODUCTS_IMAGES . " (products_id, image, htmlcontent, sort_order) values ('" . (int)$dup_products_id . "', '" . tep_db_input($product_images['image']) . "', '" . tep_db_input($product_images['htmlcontent']) . "', '" . tep_db_input($product_images['sort_order']) . "')");
            }
			
          if(defined('MODULE_SHIPPING_INDVSHIP_STATUS') && MODULE_SHIPPING_INDVSHIP_STATUS==true){
             // start indvship
            $shipping_query = tep_db_query("select products_ship_methods_id, products_ship_zip from " . TABLE_PRODUCTS_SHIPPING . " where products_id = '" . (int)$products_id . "'");
            while ($shipping = tep_db_fetch_array($shipping_query)) {
              tep_db_query("insert into " . TABLE_PRODUCTS_SHIPPING . " (products_id, products_ship_methods_id, products_ship_zip) values ('" . (int)$dup_products_id . "', '" . tep_db_input($shipping['products_ship_methods_id']) . "', '" . tep_db_input($shipping['products_ship_zip']) . "')");
            } 
			// end indvship
            }
            
            tep_db_query("insert into " . TABLE_PRODUCTS_TO_CATEGORIES . " (products_id, categories_id) values ('" . (int)$dup_products_id . "', '" . (int)$categories_id . "')");
            $products_id = $dup_products_id;
          }

          if (USE_CACHE == 'true') {
            tep_reset_cache_block('categories');
            tep_reset_cache_block('also_purchased');
          }
        }

        tep_redirect(tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $categories_id . '&pID=' . $products_id));
        break;
    }
  }

// check if the catalog image directory exists
  if (is_dir(DIR_FS_CATALOG_IMAGES)) {
    if (!tep_is_writable(DIR_FS_CATALOG_IMAGES)) $messageStack->add(ERROR_CATALOG_IMAGE_DIRECTORY_NOT_WRITEABLE, 'error');
  } else {
    $messageStack->add(ERROR_CATALOG_IMAGE_DIRECTORY_DOES_NOT_EXIST, 'error');
  }

  
 // begin Extra Product Fields
if ($action == 'new_product') {
  foreach ($epf as $e) {
    if ($e['language_active']) {
      if ($e['multi_select']) {
        echo '<script type="text/javascript">' . "\n";
        echo "function process_" . $e['field'] . '_' . $e['language'] . "(id) {\n";
        echo "  var thisbox = document.getElementById('ms' + id);\n";
        echo "  if (thisbox.checked) {\n";
        echo "    switch (id) {\n";
        foreach ($e['values'] as $val) {
          $el = get_exclude_list($val);
          if (!empty($el)) {
            echo "      case " . $val . ":\n";
            foreach($el as $i) {
              echo "        var cb = document.getElementById('ms" . $i . "');\n";
              echo "        cb.checked = false;\n";
            }
            echo "        break;\n";
          }
        }
        echo "      default: ;\n";
        echo "    }\n";
        echo "  }\n";
        echo "}\n";
        echo "</script>\n";
      } elseif ($e['uses_list'] && $e['linked']) {
        echo '<script type="text/javascript">' . "\n";
        if ($e['checkbox']) {
          echo "function process_" . $e['field'] . '_' . $e['language'] . "(id) {\n";
        } else {
          echo "function process_" . $e['field'] . '_' . $e['language'] . "() {\n";
          echo "  var id = document.getElementById('lv" . $e['id'] . '_' . $e['language'] . "').value;\n";
        }
        if (!empty($link_groups[$e['id']][$e['language']])) {
          foreach ($link_groups[$e['id']][$e['language']] as $val) {
            echo "  var lf = document.getElementById('lf" . $e['id'] . '_' . $e['language'] . '_' . $val . "');\n";
            echo "  lf.style.display = 'none'; lf.disabled = true;\n";
            foreach ($linked_fields[$e['id']][$e['language']][$val] as $id) {
              echo "  document.getElementById('ms" . $id . "').disabled = true;\n";
            }
          }
          foreach ($link_groups[$e['id']][$e['language']] as $val) {
            echo "  if (";
            $first = true;
            $enables = '';
            foreach(get_osc_children($val) as $x) {
              if ($first) {
                $first = false;
              } else {
                echo ' || ';
              }
              echo '(id == ' . $x . ')';
            }
            echo ") {\n";
            echo "    var lf = document.getElementById('lf" . $e['id'] . '_' . $e['language'] . '_' . $val . "');\n";
            echo "    lf.style.display = ''; lf.disabled = false;\n";
            foreach ($linked_fields[$e['id']][$e['language']][$val] as $id) {
              $enables .= "    document.getElementById('ms" . $id . "').disabled = false;\n";
            }
            echo $enables;
            echo "  }\n";
          }
          foreach ($linked_fields[$e['id']][$e['language']] as $group) {
            foreach ($group as $id) {
              echo "  var lv = document.getElementById('ms" . $id . "');\n";
              echo "  if (lv.disabled == true) { lv.checked = false; }\n";
            }
          }
        }
        echo "}\n";
        echo "</script>\n";
      }
    }
  }
} // end Extra Product Fields

  
  require(DIR_WS_INCLUDES . 'template_top.php');

  if ($action == 'new_product') {
    $parameters = array('products_name' => '',
                       'products_description' => '',
                       'products_url' => '',
                       'products_id' => '',
                       'products_quantity' => '',
                       'products_model' => '',
                       'products_image' => '',
                       'products_larger_images' => array(),
                       'products_price' => '',
                       'products_weight' => '',
                       'products_date_added' => '',
                       'products_last_modified' => '',
                       'products_date_available' => '',
                       'products_status' => '',
                       'products_tax_class_id' => '',
                       'manufacturers_id' => '',
					    'products_sort_order' => '');

    // begin Extra Product Fields
    foreach ($xfields as $f) {
      $parameters = array_merge($parameters, array($f => ''));
    }
// end Extra Product Fields
    
    $pInfo = new objectInfo($parameters);

    if (isset($HTTP_GET_VARS['pID']) && empty($HTTP_POST_VARS)) {
      // $product_query = tep_db_query("select pd.products_name, pd.products_description, pd.products_url, p.products_id, p.products_quantity, p.products_model, p.products_image, p.products_price, p.products_weight, p.products_date_added, p.products_last_modified, date_format(p.products_date_available, '%Y-%m-%d') as products_date_available, p.products_status, p.products_tax_class_id, p.manufacturers_id from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_id = '" . (int)$HTTP_GET_VARS['pID'] . "' and p.products_id = pd.products_id and pd.language_id = '" . (int)$languages_id . "'"); // epv change
      // begin Extra Product Fields
      
    if(defined('MODULE_SHIPPING_INDVSHIP_STATUS') && MODULE_SHIPPING_INDVSHIP_STATUS==true){
    // start indvship
      $products_shipping_query = tep_db_query("SELECT * FROM " . TABLE_PRODUCTS_SHIPPING . " WHERE products_id=" . (int)$_GET['pID']);
      while ($products_shipping = tep_db_fetch_array($products_shipping_query)) {
        $products_ship_zip = $products_shipping['products_ship_zip'];
        $products_ship_methods_id = $products_shipping['products_ship_methods_id'];
        $products_ship_price = $products_shipping['products_ship_price'];
        $products_ship_price_two = $products_shipping['products_ship_price_two'];
      }
      $shipping=array('products_ship_methods_id' => $products_ship_methods_id,
      'products_ship_zip' => $products_ship_zip,
      'products_ship_price' => $products_ship_price,
      'products_ship_price_two' => $products_ship_price_two);
      $pInfo->objectInfo($shipping);
      // end indvship
    }
    
    
    
      $query = "select pd.products_name, pd.products_description,pd.products_head_title_tag, pd.products_head_desc_tag, pd.products_head_keywords_tag,p.products_seo_url, pd.products_url, p.products_id, p.products_quantity, p.products_model, p.products_image, p.products_price, p.products_weight, p.products_date_added, p.products_last_modified, date_format(p.products_date_available, '%Y-%m-%d') as products_date_available, p.products_status, p.products_tax_class_id, p.manufacturers_id, p.products_sort_order";
      foreach ($xfields as $f) {
        $query .= ', pd.' . $f;
      }
      $query .= " from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_id = '" . (int)$HTTP_GET_VARS['pID'] . "' and p.products_id = pd.products_id and pd.language_id = '" . (int)$languages_id . "'";
      $product_query = tep_db_query($query);
      // end Extra Product Fields
    
      $product = tep_db_fetch_array($product_query);

      $pInfo->objectInfo($product);

      $product_images_query = tep_db_query("select id, image, htmlcontent, sort_order from " . TABLE_PRODUCTS_IMAGES . " where products_id = '" . (int)$product['products_id'] . "' order by sort_order");
      while ($product_images = tep_db_fetch_array($product_images_query)) {
        $pInfo->products_larger_images[] = array('id' => $product_images['id'],
                                                 'image' => $product_images['image'],
                                                 'htmlcontent' => $product_images['htmlcontent'],
                                                 'sort_order' => $product_images['sort_order']);
      }
    }

    $manufacturers_array = array(array('id' => '', 'text' => TEXT_NONE));
    $manufacturers_query = tep_db_query("select manufacturers_id, manufacturers_name from " . TABLE_MANUFACTURERS . " order by manufacturers_name");
    while ($manufacturers = tep_db_fetch_array($manufacturers_query)) {
      $manufacturers_array[] = array('id' => $manufacturers['manufacturers_id'],
                                     'text' => $manufacturers['manufacturers_name']);
    }

    $tax_class_array = array(array('id' => '0', 'text' => TEXT_NONE));
    $tax_class_query = tep_db_query("select tax_class_id, tax_class_title from " . TABLE_TAX_CLASS . " order by tax_class_title");
    while ($tax_class = tep_db_fetch_array($tax_class_query)) {
      $tax_class_array[] = array('id' => $tax_class['tax_class_id'],
                                 'text' => $tax_class['tax_class_title']);
    }

    $languages = tep_get_languages();

    if (!isset($pInfo->products_status)) $pInfo->products_status = '1';
    switch ($pInfo->products_status) {
      case '0': $in_status = false; $out_status = true; break;
      case '1':
      default: $in_status = true; $out_status = false;
    }

    $form_action = (isset($HTTP_GET_VARS['pID'])) ? 'update_product' : 'insert_product';
?>
<script type="text/javascript"><!--
var tax_rates = new Array();
<?php
    for ($i=0, $n=sizeof($tax_class_array); $i<$n; $i++) {
      if ($tax_class_array[$i]['id'] > 0) {
        echo 'tax_rates["' . $tax_class_array[$i]['id'] . '"] = ' . tep_get_tax_rate_value($tax_class_array[$i]['id']) . ';' . "\n";
      }
    }
?>

function doRound(x, places) {
  return Math.round(x * Math.pow(10, places)) / Math.pow(10, places);
}

function getTaxRate() {
  var selected_value = document.forms["new_product"].products_tax_class_id.selectedIndex;
  var parameterVal = document.forms["new_product"].products_tax_class_id[selected_value].value;

  if ( (parameterVal > 0) && (tax_rates[parameterVal] > 0) ) {
    return tax_rates[parameterVal];
  } else {
    return 0;
  }
}

function updateGross() {
  var taxRate = getTaxRate();
  var grossValue = document.forms["new_product"].products_price.value;

  if (taxRate > 0) {
    grossValue = grossValue * ((taxRate / 100) + 1);
  }

  document.forms["new_product"].products_price_gross.value = doRound(grossValue, 4);
}

function updateNet() {
  var taxRate = getTaxRate();
  var netValue = document.forms["new_product"].products_price_gross.value;

  if (taxRate > 0) {
    netValue = netValue / ((taxRate / 100) + 1);
  }

  document.forms["new_product"].products_price.value = doRound(netValue, 4);
}
//--></script>
    <?php echo tep_draw_form('new_product', FILENAME_CATEGORIES, 'cPath=' . $cPath . (isset($HTTP_GET_VARS['pID']) ? '&pID=' . $HTTP_GET_VARS['pID'] : '') . '&action=' . $form_action, 'post', 'enctype="multipart/form-data"'); ?>
    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo sprintf(TEXT_NEW_PRODUCT, tep_output_generated_category_path($current_category_id)); ?></td>
            <td class="pageHeading" align="right"><?php echo tep_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td><table border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main"><?php echo TEXT_PRODUCTS_STATUS; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_radio_field('products_status', '1', $in_status) . '&nbsp;' . TEXT_PRODUCT_AVAILABLE . '&nbsp;' . tep_draw_radio_field('products_status', '0', $out_status) . '&nbsp;' . TEXT_PRODUCT_NOT_AVAILABLE; ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_PRODUCTS_DATE_AVAILABLE; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_input_field('products_date_available', $pInfo->products_date_available, 'id="products_date_available"') . ' <small>(YYYY-MM-DD)</small>'; ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_PRODUCTS_MANUFACTURER; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_pull_down_menu('manufacturers_id', $manufacturers_array, $pInfo->manufacturers_id); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
<?php
    for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
?>
          <tr>
            <td class="main"><?php if ($i == 0) echo TEXT_PRODUCTS_NAME; ?></td>
            <td class="main"><?php echo tep_image(HTTP_SERVER.DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . tep_draw_input_field('products_name[' . $languages[$i]['id'] . ']', (isset($products_name[$languages[$i]['id']]) ? stripslashes($products_name[$languages[$i]['id']]) : tep_get_products_name($pInfo->products_id, $languages[$i]['id']))); ?></td>
          </tr>
<?php
    }
?>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          
          
          <?php 
           
           if(defined('MODULE_SHIPPING_INDVSHIP_STATUS') && MODULE_SHIPPING_INDVSHIP_STATUS!="False"){
           // start indvship ?> <!-- Zipcode -->
           
           
          <!--  <tr bgcolor="#ebebff">
            <td class="main"><?php  echo 'Zip Code'; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_hidden_field('products_ship_zip', $pInfo->products_ship_zip); ?></td>
          </tr> //-->
          <tr bgcolor="#ebebff">
            <td class="main"><?php echo 'Indv. Shipping Price:'; ?></td>
            <td class="main"><?php echo tep_draw_hidden_field('products_ship_zip', $pInfo->products_ship_zip);echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_input_field('products_ship_price', $pInfo->products_ship_price);?></td>
          </tr>
          <tr bgcolor="#ebebff">
            <td class="main"><?php echo 'Each Additional Price:'; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_input_field('products_ship_price_two', $pInfo->products_ship_price_two); ?></td>
          </tr> <!-- end Indvship -->
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <?php // end indvship 
           } ?>
          
          
          
          <tr bgcolor="#ebebff">
            <td class="main"><?php echo TEXT_PRODUCTS_TAX_CLASS; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_pull_down_menu('products_tax_class_id', $tax_class_array, $pInfo->products_tax_class_id, 'onchange="updateGross()"'); ?></td>
          </tr>
          <tr bgcolor="#ebebff">
            <td class="main"><?php echo TEXT_PRODUCTS_PRICE_NET; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_input_field('products_price', $pInfo->products_price, 'onKeyUp="updateGross()"'); ?></td>
          </tr>
          <tr bgcolor="#ebebff">
            <td class="main"><?php echo TEXT_PRODUCTS_PRICE_GROSS; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_input_field('products_price_gross', $pInfo->products_price, 'OnKeyUp="updateNet()"'); ?></td>
          </tr>
          
          

          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
           
          

          
          
<!-- AJAX Attribute Manager  -->
          <tr>
          	<td colspan="2"><?php require_once( 'attributeManager/includes/attributeManagerPlaceHolder.inc.php' )?></td>
          </tr>
<!-- AJAX Attribute Manager end -->
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
<script type="text/javascript"><!--
updateGross();
//--></script>
<?php
    for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
?>
          <tr>
            <td class="main" valign="top"><?php if ($i == 0) echo TEXT_PRODUCTS_DESCRIPTION; ?></td>
            <td><table border="0" cellspacing="0" cellpadding="0">
              <tr>
                <td class="main" valign="top"><?php echo tep_image(HTTP_SERVER.DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']); ?>&nbsp;</td>
                <td class="main"><?php echo tep_draw_textarea_field('products_description[' . $languages[$i]['id'] . ']', 'soft', '70', '10', (isset($products_description[$languages[$i]['id']]) ? stripslashes($products_description[$languages[$i]['id']]) : tep_get_products_description($pInfo->products_id, $languages[$i]['id'])),'id = products_description[' . $languages[$i]['id'] . '] class="ckeditor"'); ?></td>
              </tr>
            </table></td>
          </tr>
<?php
    }
?>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
  <?php if(has_filter('the_content','wpols_seo_filter')){ ?>        
           <tr>
            <td colspan="2" class="main"><hr><?php echo TEXT_PRODUCT_METTA_INFO; ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
<?php
    for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
?>

          <tr>
            <td class="main" valign="top"><?php if ($i == 0) echo TEXT_PRODUCTS_PAGE_TITLE; ?></td>
            <td><table border="0" cellspacing="0" cellpadding="0">
              <tr>
                <td class="main" valign="top"><?php echo tep_image(HTTP_SERVER.DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']); ?>&nbsp;</td>
                <td class="main"><?php echo tep_draw_textarea_field('products_head_title_tag[' . $languages[$i]['id'] . ']', 'soft', '70', '5', (isset($products_head_title_tag[$languages[$i]['id']]) ? stripslashes($products_head_title_tag[$languages[$i]['id']]) : tep_get_products_head_title_tag($pInfo->products_id, $languages[$i]['id']))); ?></td>
              </tr>
            </table></td>
          </tr>
<?php
    }
    for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
?>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
           <tr>
            <td class="main" valign="top"><?php if ($i == 0) echo TEXT_PRODUCTS_HEADER_DESCRIPTION; ?></td>
            <td><table border="0" cellspacing="0" cellpadding="0">
              <tr>
                <td class="main" valign="top"><?php echo tep_image(HTTP_SERVER.DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']); ?>&nbsp;</td>
                <td class="main">
                <?php
                  if (HEADER_TAGS_ENABLE_HTML_EDITOR == 'No Editor' || HEADER_TAGS_ENABLE_EDITOR_CATEGORIES == 'false')
                    echo tep_draw_textarea_field('products_head_desc_tag[' . $languages[$i]['id'] . ']', 'soft', '70', '5', (isset($products_head_desc_tag[$languages[$i]['id']]) ? stripslashes($products_head_desc_tag[$languages[$i]['id']]) : tep_get_products_head_desc_tag($pInfo->products_id, $languages[$i]['id'])));
                  else
                  {
                    if (HEADER_TAGS_ENABLE_HTML_EDITOR == 'FCKEditor') {
                      echo tep_draw_fckeditor('products_head_desc_tag[' . $languages[$i]['id'] . ']', '600', '300', (isset($products_head_desc_tag[$languages[$i]['id']]) ? $products_head_desc_tag[$languages[$i]['id']] : tep_get_products_head_desc_tag($pInfo->products_id, $languages[$i]['id'])));
                    } else if (HEADER_TAGS_ENABLE_HTML_EDITOR == 'CKEditor') {
                      echo tep_draw_textarea_field('products_head_desc_tag[' . $languages[$i]['id'] . ']', 'soft', '110', '15', (isset($products_head_desc_tag[$languages[$i]['id']]) ? $products_head_desc_tag[$languages[$i]['id']] : tep_get_products_head_desc_tag($pInfo->products_id, $languages[$i]['id'])), 'id = "products_head_desc_tag[' . $languages[$i]['id'] . ']" class="ckeditor"');
                    } else {
                      echo tep_draw_textarea_field('products_head_desc_tag[' . $languages[$i]['id'] . ']', 'soft', '70', '15', (isset($products_head_desc_tag[$languages[$i]['id']]) ? $products_head_desc_tag[$languages[$i]['id']] : tep_get_products_head_desc_tag($pInfo->products_id, $languages[$i]['id'])));
                    }
                  }
                 ?>
                 </td>
              </tr>
            </table></td>
          </tr>
<?php
    }
    for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
?>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
           <tr>
            <td class="main" valign="top"><?php if ($i == 0) echo TEXT_PRODUCTS_KEYWORDS; ?></td>
            <td><table border="0" cellspacing="0" cellpadding="0">
              <tr>
                <td class="main" valign="top"><?php echo tep_image(HTTP_SERVER.DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']); ?>&nbsp;</td>
                <td class="main"><?php echo tep_draw_textarea_field('products_head_keywords_tag[' . $languages[$i]['id'] . ']', 'soft', '70', '5', (isset($products_head_keywords_tag[$languages[$i]['id']]) ? stripslashes($products_head_keywords_tag[$languages[$i]['id']]) : tep_get_products_head_keywords_tag($pInfo->products_id, $languages[$i]['id']))); ?></td>
              </tr>
            </table></td>
          </tr>
<?php
    }
 
?>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          
           <tr>
            <td class="main" valign="top"><?php echo TEXT_PRODUCTS_SEO_URL; ?></td>
             <td class="main">&nbsp;<?php echo tep_draw_textarea_field('products_seo_url', 'soft', '70', '5', (isset($products_seo_url) ? stripslashes($products_seo_url) : tep_get_products_seo_url($pInfo->products_id))); ?></td>
           </tr>
 <?php } ?>           
            <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
            
          <tr>
            <td class="main"><?php echo TEXT_PRODUCTS_QUANTITY; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_input_field('products_quantity', $pInfo->products_quantity); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_PRODUCTS_MODEL; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_input_field('products_model', $pInfo->products_model); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main" valign="top"><?php echo TEXT_PRODUCTS_IMAGE; ?></td>
            <td class="main" style="padding-left: 30px;">
              <div><?php echo '' . TEXT_PRODUCTS_MAIN_IMAGE . ' <small>(' . SMALL_IMAGE_WIDTH . ' x ' . SMALL_IMAGE_HEIGHT . 'px)</small><br />' . (tep_not_null($pInfo->products_image) ? '<a href="' . HTTP_SERVER.DIR_WS_CATALOG_IMAGES . $pInfo->products_image . '" target="_blank">' . $pInfo->products_image . '</a> &#124; ' : '') . tep_draw_file_field('products_image'); ?></div>

              <ul id="piList">
<?php
    $pi_counter = 0;

    foreach ($pInfo->products_larger_images as $pi) {
      $pi_counter++;

      echo '                <li id="piId' . $pi_counter . '" class="ui-state-default"><span class="ui-icon ui-icon-arrowthick-2-n-s" style="float: right;"></span><a href="#" onclick="showPiDelConfirm(' . $pi_counter . ');return false;" class="ui-icon ui-icon-trash" style="float: right;"></a><strong>' . TEXT_PRODUCTS_LARGE_IMAGE . '</strong><br />' . tep_draw_file_field('products_image_large_' . $pi['id']) . '<br /><a href="' . HTTP_SERVER.DIR_WS_CATALOG_IMAGES . $pi['image'] . '" target="_blank">' . $pi['image'] . '</a><br /><br />' . TEXT_PRODUCTS_LARGE_IMAGE_HTML_CONTENT . '<br />' . tep_draw_textarea_field('products_image_htmlcontent_' . $pi['id'], 'soft', '70', '3', $pi['htmlcontent']) . '</li>';
    }
?>
              </ul>

              <a href="#" onclick="addNewPiForm();return false;"><span class="ui-icon ui-icon-plus" style="float: left;"></span><?php echo TEXT_PRODUCTS_ADD_LARGE_IMAGE; ?></a>

<div id="piDelConfirm" title="<?php echo TEXT_PRODUCTS_LARGE_IMAGE_DELETE_TITLE; ?>">
  <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo TEXT_PRODUCTS_LARGE_IMAGE_CONFIRM_DELETE; ?></p>
</div>

<style type="text/css">
#piList { list-style-type: none; margin: 0; padding: 0; }
#piList li { margin: 5px 0; padding: 2px; }
</style>

<script type="text/javascript">
$('#piList').sortable({
  containment: 'parent'
});

var piSize = <?php echo $pi_counter; ?>;

function addNewPiForm() {
  piSize++;

  $('#piList').append('<li id="piId' + piSize + '" class="ui-state-default"><span class="ui-icon ui-icon-arrowthick-2-n-s" style="float: right;"></span><a href="#" onclick="showPiDelConfirm(' + piSize + ');return false;" class="ui-icon ui-icon-trash" style="float: right;"></a><strong><?php echo TEXT_PRODUCTS_LARGE_IMAGE; ?></strong><br /><input type="file" name="products_image_large_new_' + piSize + '" /><br /><br /><?php echo TEXT_PRODUCTS_LARGE_IMAGE_HTML_CONTENT; ?><br /><textarea name="products_image_htmlcontent_new_' + piSize + '" wrap="soft" cols="70" rows="3"></textarea></li>');
}

var piDelConfirmId = 0;

$('#piDelConfirm').dialog({
  autoOpen: false,
  resizable: false,
  draggable: false,
  modal: true,
  buttons: {
    'Delete': function() {
      $('#piId' + piDelConfirmId).effect('blind').remove();
      $(this).dialog('close');
    },
    Cancel: function() {
      $(this).dialog('close');
    }
  }
});

function showPiDelConfirm(piId) {
  piDelConfirmId = piId;

  $('#piDelConfirm').dialog('open');
}
</script>

            </td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
<?php
    for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
?>
          <tr>
            <td class="main"><?php if ($i == 0) echo TEXT_PRODUCTS_URL . '<br /><small>' . TEXT_PRODUCTS_URL_WITHOUT_HTTP . '</small>'; ?></td>
            <td class="main"><?php echo tep_image(HTTP_SERVER.DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . tep_draw_input_field('products_url[' . $languages[$i]['id'] . ']', (isset($products_url[$languages[$i]['id']]) ? stripslashes($products_url[$languages[$i]['id']]) : tep_get_products_url($pInfo->products_id, $languages[$i]['id']))); ?></td>
          </tr>
<?php
    }
?>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_PRODUCTS_WEIGHT; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_input_field('products_weight', $pInfo->products_weight); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); // Product Sort ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_EDIT_SORT_ORDER; // Product Sort ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_input_field('products_sort_order', $pInfo->products_sort_order, 'size="2"'); // Product Sort ?></td>
          </tr>
                    <tr bgcolor="#ebebff">
            <td></td>
            <th class="main"><?php echo TEXT_EXTRA_FIELDS; ?></th>
          </tr>
<?php  // begin Extra Product Fields
          if(count($epf)>0){
			foreach ($epf as $e) {
        	  for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
        	    if ($e['language'] == $languages[$i]['id']) {
        	      if ($e['language_active']) {
      	          $currentval = (isset($extra[$e['field']][$languages[$i]['id']]) ? stripslashes($extra[$e['field']][$languages[$i]['id']]) : tep_get_product_extra_value($e['id'], $pInfo->products_id, $languages[$i]['id']));
        	        if ($e['uses_list']) {
        	          if ($e['multi_select']) {
           	          $currentval = (isset($extra[$e['field']][$languages[$i]['id']]) ? $extra[$e['field']][$languages[$i]['id']] : explode('|', trim(tep_get_product_extra_value($e['id'], $pInfo->products_id, $languages[$i]['id']), '|')));
        	            $value_query = tep_db_query('select value_id, value_depends_on from ' . TABLE_EPF_VALUES . ' where epf_id = ' . (int) $e['id'] . ' and languages_id = ' . (int)$e['language'] . ' order by value_depends_on, sort_order, epf_value');
        	            $epfvals = array(array());
        	            while ($val = tep_db_fetch_array($value_query)) {
        	              $epfvals[$val['value_depends_on']][] = $val['value_id'];
        	            }
        	            $inp = '';
        	            if ($e['linked']) {
        	              $tmp =  (isset($extra['extra_value_id' . $e['links_to']][$languages[$i]['id']]) ? stripslashes($extra['extra_value_id' . $e['links_to']][$languages[$i]['id']]) : tep_get_product_extra_value($e['links_to'], $pInfo->products_id, $languages[$i]['id']));
        	              $tmp = get_parent_list($tmp);
        	              $current_linked_val = explode(',', $tmp);
        	            } else {
        	              $current_linked_val = array(0);
        	            }
        	            foreach ($epfvals as $key => $vallist) {
                        $col = 0;
                        if ($e['linked']) {
                          $tparms = ' id="lf' . $e['links_to'] . '_' . $languages[$i]['id'] . '_' . $key . '"';
                          if (($key != 0) && !in_array($key, $current_linked_val))
                            $tparms .= ' style="display: none" disabled';
                        } else {
                          $tparms = '';
                        }
                        $inp .= '<table' . $tparms . '><tr>';
                        foreach ($vallist as $value) {
                          $col++;
                          if ($col > $e['columns']) {
                            $inp .= '</tr><tr>';
                            $col = 1;
                          }
                          $inp .= '<td>' . tep_draw_checkbox_field($e['field'] . "[" . $languages[$i]['id'] . "][]", $value, in_array($value, $currentval), '', 'onClick="process_' . $e['field'] . '_' . $e['language'] . '(' . $value . ')" id="ms' . $value . '"') . '</td><td>' . ($value == '0' ? TEXT_NOT_APPLY : tep_get_extra_field_list_value($value, false, $e['display_type'])) . '<td><td>&nbsp;</td>';
                        }
                        $inp .= '</tr></table>';
        	            }
        	          } else {
          	          $epfvals = tep_build_epf_pulldown($e['id'], $languages[$i]['id'], array(array('id' => 0, 'text' => TEXT_NOT_APPLY)));
          	          if ($e['checkbox']) {
                        $col = 0;
                        $inp = '<table><tr>';
                        foreach ($epfvals as $value) {
                          $col++;
                          if ($col > $e['columns']) {
                            $inp .= '</tr><tr>';
                            $col = 1;
                          }
                          $inp .= '<td>' . tep_draw_radio_field($e['field'] . "[" . $languages[$i]['id'] . "]", $value['id'], false, $currentval, ($e['linked'] ? 'onClick="process_' . $e['field'] . '_' . $e['language'] . '(' . $value['id'] . ')"' : '')) . '</td><td>' . ($value['id'] == '0' ? TEXT_NOT_APPLY : tep_get_extra_field_list_value($value['id'], false, $e['display_type'])) . '<td><td>&nbsp;</td>';
                        }
                        $inp .= '</tr></table>';
          	          } else {
          	            $inp = tep_draw_pull_down_menu($e['field'] . "[" . $languages[$i]['id'] . "]",  $epfvals, $currentval, ($e['linked'] ? 'onChange="process_' . $e['field'] . '_' . $e['language'] . '()" id="lv' . $e['id'] . '_' . $languages[$i]['id'] . '"' : ''));
          	          }
        	          }
        	        } else {
        	          if ($e['textarea']) {
          	            $inp = tep_draw_textarea_field($e['field'] . "[" . $languages[$i]['id'] . "]", 'soft', '70', '5', $currentval, 'id="' . $e['field'] . "_" . $languages[$i]['id'] . '"');
          	          // if using the TinyMCE HTML editor then uncomment the following line
          	         // $inp .= '<br /><a href="javascript:toggleHTMLEditor(\'' . $e['field'] . "_" . $languages[$i]['id'] . '\');">' . TEXT_TOGGLE_HTML . '</a>';
        	          } else {
          	            $inp = tep_draw_input_field($e['field'] . "[" . $languages[$i]['id'] . "]", $currentval, "maxlength=" . $e['size'] . " size=" . $e['size']);
        	          }
        	        }
?>
          <tr bgcolor="#ebebff" <?php if ($e['hidden']) echo 'style="display: none"'; ?>>
            <td class="main"><?php echo $e['label']; ?>:</td>
            <td class="main"><?php echo tep_image(HTTP_CATALOG_SERVER . DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . $inp; ?></td>
          </tr>
<?php
                }
              }
            }
          } 
// end Extra Product Fields
?>
          
        </table></td>
      </tr>
 <?php } ?>     
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td class="smallText" align="right" colspan="2"><?php echo tep_draw_hidden_field('products_date_added', (tep_not_null($pInfo->products_date_added) ? $pInfo->products_date_added : date('Y-m-d'))) . tep_draw_button(IMAGE_SAVE, 'disk', null, 'primary') . tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . (isset($HTTP_GET_VARS['pID']) ? '&pID=' . $HTTP_GET_VARS['pID'] : ''))); ?></td>
      </tr>
    </table>

<script type="text/javascript">
$('#products_date_available').datepicker({
  dateFormat: 'yy-mm-dd'
});
</script>

    </form>
<?php
/*  } elseif ($action == 'new_product_preview') {
    $product_query = tep_db_query("select p.products_id, pd.language_id, pd.products_name, pd.products_description, pd.products_url, p.products_quantity, p.products_model, p.products_image, p.products_price, p.products_weight, p.products_date_added, p.products_last_modified, p.products_date_available, p.products_status, p.manufacturers_id  from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_id = pd.products_id and p.products_id = '" . (int)$HTTP_GET_VARS['pID'] . "'");
    $product = tep_db_fetch_array($product_query);
    
    for epv
    */

    
      } elseif ($action == 'new_product_preview') {
      // begin Extra Product Fields
      $query = "select p.products_id, pd.language_id, pd.products_name, pd.products_description,pd.products_head_title_tag, pd.products_head_desc_tag, pd.products_head_keywords_tag, p.products_seo_url,pd.products_url, p.products_quantity, p.products_model, p.products_image, p.products_price, p.products_weight, p.products_date_added, p.products_last_modified, p.products_date_available, p.products_status, p.manufacturers_id,p.products_sort_order ";
      foreach ($xfields as $f) {
        $query .= ', pd.' . $f;
      }
      $query .= " from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_id = pd.products_id and p.products_id = '" . (int)$HTTP_GET_VARS['pID'] . "'";
      $product_query = tep_db_query($query);
      // end Extra Product Fields
      $product = tep_db_fetch_array($product_query);
    
    
    
    $pInfo = new objectInfo($product);
    $products_image_name = $pInfo->products_image;

    $languages = tep_get_languages();
    for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
	
      $pInfo->products_name = tep_get_products_name($pInfo->products_id, $languages[$i]['id']);
      $pInfo->products_description = tep_get_products_description($pInfo->products_id, $languages[$i]['id']);
      $pInfo->products_url = tep_get_products_url($pInfo->products_id, $languages[$i]['id']);
      $pInfo->products_head_title_tag = tep_db_prepare_input($products_head_title_tag[$languages[$i]['id']]);
        $pInfo->products_head_desc_tag = tep_db_prepare_input($products_head_desc_tag[$languages[$i]['id']]);
        $pInfo->products_head_keywords_tag = tep_db_prepare_input($products_head_keywords_tag[$languages[$i]['id']]);
      // $pInfo->products_seo_url = tep_db_prepare_input($products_seo_url[$languages[$i]['id']]); 
      
      
?>
    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo tep_image(HTTP_SERVER.DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . $pInfo->products_name; ?></td>
            <td class="pageHeading" align="right"><?php echo $currencies->format($pInfo->products_price); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td class="main"><?php echo tep_image(DIR_WS_CATALOG_IMAGES . $products_image_name, $pInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'align="right" hspace="5" vspace="5"') . $pInfo->products_description; ?></td>
      </tr>
<?php


// begin Extra Product Fields
         foreach ($epf as $e) {
           if ($e['language'] == $languages[$i]['id']) {
             if ($e['language_active']) {
               if (isset($HTTP_GET_VARS['read']) && ($HTTP_GET_VARS['read'] == 'only')) {
                 $value = tep_get_product_extra_value($e['id'], $pInfo->products_id, $languages[$i]['id']);
                 if ($e['multi_select'] && ($value != '')) {
                   $value = explode('|', trim($value, '|'));
                 }
               } else {
                 if ($e['multi_select']) {
                   $value = $extra[$e['field']][$languages[$i]['id']];
                 } else {
                   $value = tep_db_prepare_input($extra[$e['field']][$languages[$i]['id']]);
                   if ($e['uses_list'] && ($value == 0)) $value = '';
                 }
               }
               if (tep_not_null($value)) {
                 echo '<tr><td class="main"><b>' . $e['label'] . ': </b>';
                 if ($e['uses_list']) {
                   if ($e['multi_select']) {
                     $output = array();
                     foreach ($value as $val) {
                       $output[] = tep_get_extra_field_list_value($val, $e['show_chain'], $e['display_type']);
                     }
                     echo implode(', ', $output);
                   } else {
                     echo tep_get_extra_field_list_value($value, $e['show_chain'], $e['display_type']);
                   }
                 } else {
                   echo $value;
                 }
                 echo "</td></tr>\n";
               }
             }
           }
         }
// end Extra Product Fields

      if ($pInfo->products_url) {
?>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td class="main"><?php echo sprintf(TEXT_PRODUCT_MORE_INFORMATION, $pInfo->products_url); ?></td>
      </tr>
<?php
      }
?>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
<?php
      if ($pInfo->products_date_available > date('Y-m-d')) {
?>
      <tr>
        <td align="center" class="smallText"><?php echo sprintf(TEXT_PRODUCT_DATE_AVAILABLE, tep_date_long($pInfo->products_date_available)); ?></td>
      </tr>
<?php
      } else {
?>
      <tr>
        <td align="center" class="smallText"><?php echo sprintf(TEXT_PRODUCT_DATE_ADDED, tep_date_long($pInfo->products_date_added)); ?></td>
      </tr>
<?php
      }
?>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
<?php
    }

    if (isset($HTTP_GET_VARS['origin'])) {
      $pos_params = strpos($HTTP_GET_VARS['origin'], '?', 0);
      if ($pos_params != false) {
        $back_url = substr($HTTP_GET_VARS['origin'], 0, $pos_params);
        $back_url_params = substr($HTTP_GET_VARS['origin'], $pos_params + 1);
      } else {
        $back_url = $HTTP_GET_VARS['origin'];
        $back_url_params = '';
      }
    } else {
      $back_url = FILENAME_CATEGORIES;
      $back_url_params = 'cPath=' . $cPath . '&pID=' . $pInfo->products_id;
    }
?>
      <tr>
        <td align="right" class="smallText"><?php echo tep_draw_button(IMAGE_BACK, 'triangle-1-w', tep_href_link($back_url, $back_url_params, 'NONSSL')); ?></td>
      </tr>
    </table>
<?php
  } else {
?>
    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE.TEXT_HELP_INSTURCTIONS.TEXT_HELP_VIDEO; ?></td>
            <td class="pageHeading" align="right"><?php echo tep_draw_separator('pixel_trans.gif', 1, HEADING_IMAGE_HEIGHT); ?></td>
            <td align="right"><table border="0" width="100%" cellspacing="0" cellpadding="0">
              <tr>
                <td class="smallText" align="right">
<?php
    echo tep_draw_form('search', FILENAME_CATEGORIES, '', 'get');
    echo HEADING_TITLE_SEARCH . ' ' . tep_draw_input_field('search');
    echo tep_draw_hidden_field('submenu', 'categories');
    echo tep_draw_hidden_field('page', 'WP_online_store');
    echo tep_hide_session_id() . '</form>';
?>
                </td>
              </tr>
              <tr>
                <td class="smallText" align="right">
<?php
    echo tep_draw_form('goto', FILENAME_CATEGORIES, '', 'get');
    echo HEADING_TITLE_GOTO . ' ' . tep_draw_pull_down_menu('cPath', tep_get_category_tree(), $current_category_id, 'onchange="this.form.submit();"');
    echo tep_draw_hidden_field('submenu', 'categories');
    echo tep_draw_hidden_field('page', 'WP_online_store');
    echo tep_hide_session_id() . '</form>';
?>
                </td>
              </tr>
            </table></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CATEGORIES_PRODUCTS; ?></td>
				<td class="dataTableHeadingContent" align="center">Sort by</td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_STATUS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
    $categories_count = 0;
    $rows = 0;
    if (isset($HTTP_GET_VARS['search'])) {
      $search = tep_db_prepare_input($HTTP_GET_VARS['search']);

/*      $categories_query = tep_db_query("select c.categories_id, cd.categories_name, c.categories_image, c.parent_id, c.sort_order, c.date_added, c.last_modified from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "' and cd.categories_name like '%" . tep_db_input($search) . "%' order by c.sort_order, cd.categories_name");
    } else {
      $categories_query = tep_db_query("select c.categories_id, cd.categories_name, c.categories_image, c.parent_id, c.sort_order, c.date_added, c.last_modified from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.parent_id = '" . (int)$current_category_id . "' and c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "' order by c.sort_order, cd.categories_name");
    }*/
    
      $categories_query = tep_db_query("select c.categories_id, cd.categories_name,cd.categories_htc_title_tag, cd.categories_htc_desc_tag, cd.categories_htc_keywords_tag, cd.categories_seo_url, cd.categories_description, c.categories_image, c.parent_id, c.sort_order, c.date_added, c.last_modified, c.categories_min_quan  from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "' and cd.categories_name like '%" . tep_db_input($search) . "%' order by c.sort_order, cd.categories_name");
    } else {
      $categories_query = tep_db_query("select c.categories_id, cd.categories_name, cd.categories_htc_title_tag, cd.categories_htc_desc_tag, cd.categories_htc_keywords_tag, cd.categories_seo_url,cd.categories_description, c.categories_image, c.parent_id, c.sort_order, c.date_added, c.last_modified , c.categories_min_quan  from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.parent_id = '" . (int)$current_category_id . "' and c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "' order by c.sort_order, cd.categories_name");
    }
    while ($categories = tep_db_fetch_array($categories_query)) {
      $categories_count++;
      $rows++;

// Get parent_id for subcategories if search
      if (isset($HTTP_GET_VARS['search'])) $cPath= $categories['parent_id'];

      if ((!isset($HTTP_GET_VARS['cID']) && !isset($HTTP_GET_VARS['pID']) || (isset($HTTP_GET_VARS['cID']) && ($HTTP_GET_VARS['cID'] == $categories['categories_id']))) && !isset($cInfo) && (substr($action, 0, 3) != 'new')) {
        $category_childs = array('childs_count' => tep_childs_in_category_count($categories['categories_id']));
        $category_products = array('products_count' => tep_products_in_category_count($categories['categories_id']));

        $cInfo_array = array_merge($categories, $category_childs, $category_products);
        $cInfo = new objectInfo($cInfo_array);
      }

      if (isset($cInfo) && is_object($cInfo) && ($categories['categories_id'] == $cInfo->categories_id) ) {
        echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_CATEGORIES, tep_get_path($categories['categories_id'])) . '\'">' . "\n";
      } else {
        echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&cID=' . $categories['categories_id']) . '\'">' . "\n";
      }
?>
                <td class="dataTableContent"><?php echo '<a href="' . tep_href_link(FILENAME_CATEGORIES, tep_get_path($categories['categories_id'])) . '">' . tep_image(DIR_WS_ICONS . 'folder.gif', ICON_FOLDER) . '</a>&nbsp;<strong>' . $categories['categories_name'] . '</strong>'; ?></td>
				<td class="dataTableContent" align="center"><?php echo $categories['sort_order'];?></td>
                <td class="dataTableContent" align="center">&nbsp;</td>
                <td class="dataTableContent" align="right"><?php if (isset($cInfo) && is_object($cInfo) && ($categories['categories_id'] == $cInfo->categories_id) ) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&cID=' . $categories['categories_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
    }

    $products_count = 0;
    if (isset($HTTP_GET_VARS['search'])) {
      $products_query = tep_db_query("select p.products_id, pd.products_name, p.products_quantity, p.products_image, p.products_price, p.products_date_added, p.products_last_modified, p.products_date_available, p.products_status, p2c.categories_id ,p.products_sort_order from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p.products_id = pd.products_id and pd.language_id = '" . (int)$languages_id . "' and p.products_id = p2c.products_id and pd.products_name like '%" . tep_db_input($search) . "%' order by p.products_sort_order, pd.products_name");
    } else {
      $products_query = tep_db_query("select p.products_id, pd.products_name, p.products_quantity, p.products_image, p.products_price, p.products_date_added, p.products_last_modified, p.products_date_available, p.products_status, p.products_sort_order  from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p.products_id = pd.products_id and pd.language_id = '" . (int)$languages_id . "' and p.products_id = p2c.products_id and p2c.categories_id = '" . (int)$current_category_id . "' order by p.products_sort_order, pd.products_name");
    }
    while ($products = tep_db_fetch_array($products_query)) {
      $products_count++;
      $rows++;

// Get categories_id for product if search
      if (isset($HTTP_GET_VARS['search'])) $cPath = $products['categories_id'];

      if ( (!isset($HTTP_GET_VARS['pID']) && !isset($HTTP_GET_VARS['cID']) || (isset($HTTP_GET_VARS['pID']) && ($HTTP_GET_VARS['pID'] == $products['products_id']))) && !isset($pInfo) && !isset($cInfo) && (substr($action, 0, 3) != 'new')) {
// find out the rating average from customer reviews
        $reviews_query = tep_db_query("select (avg(reviews_rating) / 5 * 100) as average_rating from " . TABLE_REVIEWS . " where products_id = '" . (int)$products['products_id'] . "'");
        $reviews = tep_db_fetch_array($reviews_query);
        $pInfo_array = array_merge($products, $reviews);
        $pInfo = new objectInfo($pInfo_array);
      }

      if (isset($pInfo) && is_object($pInfo) && ($products['products_id'] == $pInfo->products_id) ) {
        echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&pID=' . $products['products_id'] . '&action=new_product_preview') . '\'">' . "\n";
      } else {
        echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&pID=' . $products['products_id']) . '\'">' . "\n";
      }
?>
                <td class="dataTableContent"><?php echo '<a href="' . tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&pID=' . $products['products_id'] . '&action=new_product_preview') . '">' . tep_image(DIR_WS_ICONS . 'preview.gif', ICON_PREVIEW) . '</a>&nbsp;' . stripslashes($products['products_name']); ?></td>
				  <td class="dataTableContent" align="center"><?php echo $products['products_sort_order']; // Product Sort ?></td>
                <td class="dataTableContent" align="center">
<?php
      if ($products['products_status'] == '1') {
        echo tep_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10) . '&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_CATEGORIES, 'action=setflag&flag=0&pID=' . $products['products_id'] . '&cPath=' . $cPath) . '">' . tep_image(DIR_WS_IMAGES . 'icon_status_red_light.gif', IMAGE_ICON_STATUS_RED_LIGHT, 10, 10) . '</a>';
      } else {
        echo '<a href="' . tep_href_link(FILENAME_CATEGORIES, 'action=setflag&flag=1&pID=' . $products['products_id'] . '&cPath=' . $cPath) . '">' . tep_image(DIR_WS_IMAGES . 'icon_status_green_light.gif', IMAGE_ICON_STATUS_GREEN_LIGHT, 10, 10) . '</a>&nbsp;&nbsp;' . tep_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10);
      }
?></td>
                <td class="dataTableContent" align="right"><?php if (isset($pInfo) && is_object($pInfo) && ($products['products_id'] == $pInfo->products_id)) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&pID=' . $products['products_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
    }

    $cPath_back = '';
    if (sizeof($cPath_array) > 0) {
      for ($i=0, $n=sizeof($cPath_array)-1; $i<$n; $i++) {
        if (empty($cPath_back)) {
          $cPath_back .= $cPath_array[$i];
        } else {
          $cPath_back .= '_' . $cPath_array[$i];
        }
      }
    }

    $cPath_back = (tep_not_null($cPath_back)) ? 'cPath=' . $cPath_back . '&' : '';
?>
              <tr>
                <td colspan="3"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText"><?php echo TEXT_CATEGORIES . '&nbsp;' . $categories_count . '<br />' . TEXT_PRODUCTS . '&nbsp;' . $products_count; ?></td>
                    <td align="right" class="smallText"><?php if (sizeof($cPath_array) > 0) echo tep_draw_button(IMAGE_BACK, 'triangle-1-w', tep_href_link(FILENAME_CATEGORIES, $cPath_back . 'cID=' . $current_category_id)); if (!isset($HTTP_GET_VARS['search'])) echo tep_draw_button(IMAGE_NEW_CATEGORY, 'plus', tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&action=new_category')) . tep_draw_button(IMAGE_NEW_PRODUCT, 'plus', tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&action=new_product')); ?>&nbsp;</td>
                  </tr>
                </table></td>
              </tr>
            </table></td>
<?php
    $heading = array();
    $contents = array();
    switch ($action) {
      case 'new_category':
        $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_NEW_CATEGORY . '</strong>');

        $contents = array('form' => tep_draw_form('newcategory', FILENAME_CATEGORIES, 'action=insert_category&cPath=' . $cPath, 'post', 'enctype="multipart/form-data"'));
        $contents[] = array('text' => TEXT_NEW_CATEGORY_INTRO);

        $category_inputs_string = '';
        $languages = tep_get_languages();

        
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
          $category_inputs_string .= '<br />' . tep_image(HTTP_SERVER.DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . tep_draw_input_field('categories_name[' . $languages[$i]['id'] . ']');
          
          if(has_filter('the_content','wpols_seo_filter')){   
          $category_htc_title_string .= '<br>' . tep_image(HTTP_SERVER.DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . tep_draw_input_field('categories_htc_title_tag[' . $languages[$i]['id'] . ']');
          $category_htc_desc_string .= '<br>' . tep_image(HTTP_SERVER.DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . tep_draw_input_field('categories_htc_desc_tag[' . $languages[$i]['id'] . ']');
          $category_htc_keywords_string .= '<br>' . tep_image(HTTP_SERVER.DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . tep_draw_input_field('categories_htc_keywords_tag[' . $languages[$i]['id'] . ']');
		   	
          
         
          //$category_seo_url_string .= '<br>' . tep_image(HTTP_SERVER.DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . tep_draw_input_field('categories_seo_url[' . $languages[$i]['id'] . ']');
        }
        }	
       $category_desc_inputs_string = '';
        $languages = tep_get_languages();
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
          $category_desc_inputs_string .= '<br />' . tep_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . tep_draw_textarea_field('categories_description[' . $languages[$i]['id'] . ']', '', 40, 10);
        }
        
        $contents[] = array('text' => '<br />' . TEXT_CATEGORIES_NAME . $category_inputs_string);
         $contents[] = array('text' => '<br />' . TEXT_CATEGORIES_DESC . $category_desc_inputs_string);
        $contents[] = array('text' => '<br />' . TEXT_CATEGORIES_IMAGE . '<br />' . tep_draw_file_field('categories_image'));
         if(has_filter('the_content','wpols_seo_filter')){
         $contents[] = array('text' => '<br>' . 'Header Tags Category Title' . $category_htc_title_string);
        $contents[] = array('text' => '<br>' . 'Header Tags Category Description' . $category_htc_desc_string);
        $contents[] = array('text' => '<br>' . 'Header Tags Category Keywords' . $category_htc_keywords_string);
         } 
      //  $contents[] = array('text' => '<br>' . 'Header Tags SEO URL' . $category_seo_url_string);
        $contents[] = array('text' => '<br />' . TEXT_SORT_ORDER . '<br />' . tep_draw_input_field('sort_order', '', 'size="2"'));
			// BOF Minimum Category Tree Quantity
        $contents[] = array('text' => '<br>' . TEXT_CAT_TREE_MIN_QUAN . '<br>' . tep_draw_input_field('categories_min_quan', '', 'size="3"'));
	// EOF Minimum Category Tree Quantity

        $contents[] = array('align' => 'center', 'text' => '<br />' . tep_draw_button(IMAGE_SAVE, 'disk', null, 'primary') . tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath)));
        break;
      case 'edit_category':
        $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_EDIT_CATEGORY . '</strong>');

        $contents = array('form' => tep_draw_form('categories', FILENAME_CATEGORIES, 'action=update_category&cPath=' . $cPath, 'post', 'enctype="multipart/form-data"') . tep_draw_hidden_field('categories_id', $cInfo->categories_id));
        $contents[] = array('text' => TEXT_EDIT_INTRO);

        $category_inputs_string = '';
        $languages = tep_get_languages();
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
          $category_inputs_string .= '<br />' . tep_image(HTTP_SERVER.DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . tep_draw_input_field('categories_name[' . $languages[$i]['id'] . ']', tep_get_category_name($cInfo->categories_id, $languages[$i]['id']));
          
           $category_htc_title_string .= '<br>' . tep_image(HTTP_SERVER.DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . tep_draw_input_field('categories_htc_title_tag[' . $languages[$i]['id'] . ']', tep_get_category_htc_title($cInfo->categories_id, $languages[$i]['id']));
          $category_htc_desc_string .= '<br>' . tep_image(HTTP_SERVER.DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . tep_draw_input_field('categories_htc_desc_tag[' . $languages[$i]['id'] . ']', tep_get_category_htc_desc($cInfo->categories_id, $languages[$i]['id']));
          $category_htc_keywords_string .= '<br>' . tep_image(HTTP_SERVER.DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . tep_draw_input_field('categories_htc_keywords_tag[' . $languages[$i]['id'] . ']', tep_get_category_htc_keywords($cInfo->categories_id, $languages[$i]['id']));
       //  $category_seo_url .= '<br>' . tep_image(HTTP_SERVER.DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . tep_draw_input_field('categories_seo_url[' . $languages[$i]['id'] . ']', tep_get_category_seo_url($cInfo->categories_id, $languages[$i]['id']));
          
        }
    $category_desc_inputs_string = '';
        $languages = tep_get_languages();
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
          $category_desc_inputs_string .= '<br />' . tep_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . tep_draw_textarea_field('categories_description[' . $languages[$i]['id'] . ']', '', 40, 10, tep_get_category_description($cInfo->categories_id, $languages[$i]['id']));
        }
        $contents[] = array('text' => '<br />' . TEXT_EDIT_CATEGORIES_NAME . $category_inputs_string);
         $contents[] = array('text' => '<br />' . TEXT_CATEGORIES_DESC . $category_desc_inputs_string);
        
        $contents[] = array('text' => '<br />' . tep_image(HTTP_CATALOG_SERVER . DIR_WS_CATALOG_IMAGES . $cInfo->categories_image, $cInfo->categories_name) . '<br />' . DIR_WS_CATALOG_IMAGES . '<br /><strong>' . $cInfo->categories_image . '</strong>');
        $contents[] = array('text' => '<br />' . TEXT_EDIT_CATEGORIES_IMAGE . '<br />' . tep_draw_file_field('categories_image'));
        
         if(has_filter('the_content','wpols_seo_filter')){   
        $contents[] = array('text' => '<br>' . 'Header Tags Category Title' . $category_htc_title_string);
        $contents[] = array('text' => '<br>' . 'Header Tags Category Description' . $category_htc_desc_string);
        $contents[] = array('text' => '<br>' . 'Header Tags Category Keywords' . $category_htc_keywords_string);
       // $contents[] = array('text' => '<br>' . 'Header Tags SEO URL' . $category_seo_url);
         }
        $contents[] = array('text' => '<br />' . TEXT_EDIT_SORT_ORDER . '<br />' . tep_draw_input_field('sort_order', $cInfo->sort_order, 'size="2"'));
	// BOF Minimum Category Tree Quantity
        $contents[] = array('text' => '<br>' . TEXT_CAT_TREE_MIN_QUAN . '<br>' . tep_draw_input_field('categories_min_quan', $cInfo->categories_min_quan, 'size="3"'));
	// EOF Minimum Category Tree Quantity
        $contents[] = array('align' => 'center', 'text' => '<br />' . tep_draw_button(IMAGE_SAVE, 'disk', null, 'primary') . tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&cID=' . $cInfo->categories_id)));
        break;
      case 'delete_category':
        $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_DELETE_CATEGORY . '</strong>');

        $contents = array('form' => tep_draw_form('categories', FILENAME_CATEGORIES, 'action=delete_category_confirm&cPath=' . $cPath) . tep_draw_hidden_field('categories_id', $cInfo->categories_id));
        $contents[] = array('text' => TEXT_DELETE_CATEGORY_INTRO);
        $contents[] = array('text' => '<br /><strong>' . $cInfo->categories_name . '</strong>');
        if ($cInfo->childs_count > 0) $contents[] = array('text' => '<br />' . sprintf(TEXT_DELETE_WARNING_CHILDS, $cInfo->childs_count));
        if ($cInfo->products_count > 0) $contents[] = array('text' => '<br />' . sprintf(TEXT_DELETE_WARNING_PRODUCTS, $cInfo->products_count));
        $contents[] = array('align' => 'center', 'text' => '<br />' . tep_draw_button(IMAGE_DELETE, 'trash', null, 'primary') . tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&cID=' . $cInfo->categories_id)));
        break;
      case 'move_category':
        $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_MOVE_CATEGORY . '</strong>');

        $contents = array('form' => tep_draw_form('categories', FILENAME_CATEGORIES, 'action=move_category_confirm&cPath=' . $cPath) . tep_draw_hidden_field('categories_id', $cInfo->categories_id));
        $contents[] = array('text' => sprintf(TEXT_MOVE_CATEGORIES_INTRO, $cInfo->categories_name));
        $contents[] = array('text' => '<br />' . sprintf(TEXT_MOVE, $cInfo->categories_name) . '<br />' . tep_draw_pull_down_menu('move_to_category_id', tep_get_category_tree(), $current_category_id));
        $contents[] = array('align' => 'center', 'text' => '<br />' . tep_draw_button(IMAGE_MOVE, 'arrow-4', null, 'primary') . tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&cID=' . $cInfo->categories_id)));
        break;
      case 'delete_product':
        $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_DELETE_PRODUCT . '</strong>');

        $contents = array('form' => tep_draw_form('products', FILENAME_CATEGORIES, 'action=delete_product_confirm&cPath=' . $cPath) . tep_draw_hidden_field('products_id', $pInfo->products_id));
        $contents[] = array('text' => TEXT_DELETE_PRODUCT_INTRO);
        $contents[] = array('text' => '<br /><strong>' . $pInfo->products_name . '</strong>');

        $product_categories_string = '';
        $product_categories = tep_generate_category_path($pInfo->products_id, 'product');
        for ($i = 0, $n = sizeof($product_categories); $i < $n; $i++) {
          $category_path = '';
          for ($j = 0, $k = sizeof($product_categories[$i]); $j < $k; $j++) {
            $category_path .= $product_categories[$i][$j]['text'] . '&nbsp;&gt;&nbsp;';
          }
          $category_path = substr($category_path, 0, -16);
          $product_categories_string .= tep_draw_checkbox_field('product_categories[]', $product_categories[$i][sizeof($product_categories[$i])-1]['id'], true) . '&nbsp;' . $category_path . '<br />';
        }
        $product_categories_string = substr($product_categories_string, 0, -4);

        $contents[] = array('text' => '<br />' . $product_categories_string);
        $contents[] = array('align' => 'center', 'text' => '<br />' . tep_draw_button(IMAGE_DELETE, 'trash', null, 'primary') . tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&pID=' . $pInfo->products_id)));
        break;
      case 'move_product':
        $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_MOVE_PRODUCT . '</strong>');

        $contents = array('form' => tep_draw_form('products', FILENAME_CATEGORIES, 'action=move_product_confirm&cPath=' . $cPath) . tep_draw_hidden_field('products_id', $pInfo->products_id));
        $contents[] = array('text' => sprintf(TEXT_MOVE_PRODUCTS_INTRO, $pInfo->products_name));
        $contents[] = array('text' => '<br />' . TEXT_INFO_CURRENT_CATEGORIES . '<br /><strong>' . tep_output_generated_category_path($pInfo->products_id, 'product') . '</strong>');
        $contents[] = array('text' => '<br />' . sprintf(TEXT_MOVE, $pInfo->products_name) . '<br />' . tep_draw_pull_down_menu('move_to_category_id', tep_get_category_tree(), $current_category_id));
        $contents[] = array('align' => 'center', 'text' => '<br />' . tep_draw_button(IMAGE_MOVE, 'arrow-4', null, 'primary') . tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&pID=' . $pInfo->products_id)));
        break;
      case 'copy_to':
        $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_COPY_TO . '</strong>');

        $contents = array('form' => tep_draw_form('copy_to', FILENAME_CATEGORIES, 'action=copy_to_confirm&cPath=' . $cPath) . tep_draw_hidden_field('products_id', $pInfo->products_id));
        $contents[] = array('text' => TEXT_INFO_COPY_TO_INTRO);
        $contents[] = array('text' => '<br />' . TEXT_INFO_CURRENT_CATEGORIES . '<br /><strong>' . tep_output_generated_category_path($pInfo->products_id, 'product') . '</strong>');
        $contents[] = array('text' => '<br />' . TEXT_CATEGORIES . '<br />' . tep_draw_pull_down_menu('categories_id', tep_get_category_tree(), $current_category_id));
        $contents[] = array('text' => '<br />' . TEXT_HOW_TO_COPY . '<br />' . tep_draw_radio_field('copy_as', 'link', true) . ' ' . TEXT_COPY_AS_LINK . '<br />' . tep_draw_radio_field('copy_as', 'duplicate') . ' ' . TEXT_COPY_AS_DUPLICATE);
        $contents[] = array('align' => 'center', 'text' => '<br />' . tep_draw_button(IMAGE_COPY, 'copy', null, 'primary') . tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&pID=' . $pInfo->products_id)));
        break;
      default:
        if ($rows > 0) {
          if (isset($cInfo) && is_object($cInfo)) { // category info box contents
            $category_path_string = '';
            $category_path = tep_generate_category_path($cInfo->categories_id);
            for ($i=(sizeof($category_path[0])-1); $i>0; $i--) {
              $category_path_string .= $category_path[0][$i]['id'] . '_';
            }
            $category_path_string = substr($category_path_string, 0, -1);

            $heading[] = array('text' => '<strong>' . $cInfo->categories_name . '</strong>');

            $contents[] = array('align' => 'center', 'text' => tep_draw_button(IMAGE_EDIT, 'document', tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $category_path_string . '&cID=' . $cInfo->categories_id . '&action=edit_category')) . tep_draw_button(IMAGE_DELETE, 'trash', tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $category_path_string . '&cID=' . $cInfo->categories_id . '&action=delete_category')) . tep_draw_button(IMAGE_MOVE, 'arrow-4', tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $category_path_string . '&cID=' . $cInfo->categories_id . '&action=move_category')));
            $contents[] = array('text' => '<br />' . TEXT_DATE_ADDED . ' ' . tep_date_short($cInfo->date_added));
            if (tep_not_null($cInfo->last_modified)) $contents[] = array('text' => TEXT_LAST_MODIFIED . ' ' . tep_date_short($cInfo->last_modified));
            $contents[] = array('text' => '<br />' . tep_info_image($cInfo->categories_image, $cInfo->categories_name, HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT) . '<br />' . $cInfo->categories_image);
            $contents[] = array('text' => '<br />' . TEXT_SUBCATEGORIES . ' ' . $cInfo->childs_count . '<br />' . TEXT_PRODUCTS . ' ' . $cInfo->products_count);
          } elseif (isset($pInfo) && is_object($pInfo)) { // product info box contents
            $heading[] = array('text' => '<strong>' . tep_get_products_name($pInfo->products_id, $languages_id) . '</strong>');

            $contents[] = array('align' => 'center', 'text' => tep_draw_button(IMAGE_EDIT, 'document', tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&pID=' . $pInfo->products_id . '&action=new_product')) . tep_draw_button(IMAGE_DELETE, 'trash', tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&pID=' . $pInfo->products_id . '&action=delete_product')) . tep_draw_button(IMAGE_MOVE, 'arrow-4', tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&pID=' . $pInfo->products_id . '&action=move_product')) . tep_draw_button(IMAGE_COPY_TO, 'copy', tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&pID=' . $pInfo->products_id . '&action=copy_to')));
            $contents[] = array('text' => '<br />' . TEXT_DATE_ADDED . ' ' . tep_date_short($pInfo->products_date_added));
            if (tep_not_null($pInfo->products_last_modified)) $contents[] = array('text' => TEXT_LAST_MODIFIED . ' ' . tep_date_short($pInfo->products_last_modified));
            if (date('Y-m-d') < $pInfo->products_date_available) $contents[] = array('text' => TEXT_DATE_AVAILABLE . ' ' . tep_date_short($pInfo->products_date_available));
            $contents[] = array('text' => '<br />' . tep_info_image($pInfo->products_image, $pInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '<br />' . $pInfo->products_image);
            $contents[] = array('text' => '<br />' . TEXT_PRODUCTS_PRICE_INFO . ' ' . $currencies->format($pInfo->products_price) . '<br />' . TEXT_PRODUCTS_QUANTITY_INFO . ' ' . $pInfo->products_quantity);
            $contents[] = array('text' => '<br />' . TEXT_PRODUCTS_AVERAGE_RATING . ' ' . number_format($pInfo->average_rating, 2) . '%');
          }
        } else { // create category/product info
          $heading[] = array('text' => '<strong>' . EMPTY_CATEGORY . '</strong>');

          $contents[] = array('text' => TEXT_NO_CHILD_CATEGORIES_OR_PRODUCTS);
        }
        break;
    }

    if ( (tep_not_null($heading)) && (tep_not_null($contents)) ) {
      echo '            <td width="25%" valign="top">' . "\n";

      $box = new box;
      echo $box->infoBox($heading, $contents);

      echo '            </td>' . "\n";
    }
?>
          </tr>
        </table></td>
      </tr>
    </table>
<?php
  }

  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>

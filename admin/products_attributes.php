<?php

/*

  $Id$



  osCommerce, Open Source E-Commerce Solutions

  http://www.oscommerce.com



  Copyright (c) 2010 osCommerce



  Released under the GNU General Public License

*/



  require('includes/application_top.php');



  $languages = tep_get_languages();



  $action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');



    

    if(isset($_POST['option_page'])) $HTTP_GET_VARS['option_page']=$_POST['option_page'];



  if(isset($_POST['value_page'])) $HTTP_GET_VARS['value_page']=$_POST['value_page'];



  if(isset($_POST['attribute_page']))	$HTTP_GET_VARS['attribute_page']=$_POST['attribute_page'];

  

  $option_page = (isset($HTTP_GET_VARS['option_page']) && is_numeric($HTTP_GET_VARS['option_page'])) ? $HTTP_GET_VARS['option_page'] : 1;

  $value_page = (isset($HTTP_GET_VARS['value_page']) && is_numeric($HTTP_GET_VARS['value_page'])) ? $HTTP_GET_VARS['value_page'] : 1;

  $attribute_page = (isset($HTTP_GET_VARS['attribute_page']) && is_numeric($HTTP_GET_VARS['attribute_page'])) ? $HTTP_GET_VARS['attribute_page'] : 1;



  $page_info = 'option_page=' . $option_page . '&value_page=' . $value_page . '&attribute_page=' . $attribute_page;

//BOF - Zappo - Option Types v2 - Check if the option_value TEXT_UPLOAD_NAME is in place, and insert if not found
  $textoptions_query = tep_db_query("select products_options_values_name from " . TABLE_PRODUCTS_OPTIONS_VALUES . " where products_options_values_id = '" . OPTIONS_VALUES_TEXT_ID . "' and language_id = '" . $languages_id . "'");
  $textoptions = tep_db_fetch_array($textoptions_query);
  if (empty($textoptions['products_options_values_name']) || $textoptions['products_options_values_name'] != TEXT_UPLOAD_NAME) {
    tep_db_query("delete from " . TABLE_PRODUCTS_OPTIONS_VALUES . " where products_options_values_id = '" . OPTIONS_VALUES_TEXT_ID . "'");
    for ($i=0, $n=sizeof($languages); $i<$n; $i ++) {
      tep_db_query("insert into " . TABLE_PRODUCTS_OPTIONS_VALUES . " (products_options_values_id, language_id, products_options_values_name) values ('" . OPTIONS_VALUES_TEXT_ID . "', '" . (int)$languages[$i]['id'] . "', '" . TEXT_UPLOAD_NAME . "')");
    }
  }	
//EOF - Zappo - Option Types v2 - Check if the option_value TEXT_UPLOAD_NAME is in place, and insert if not found



  if (tep_not_null($action)) {

    switch ($action) {
		
			case 'clone_attributes':
		$clone_product_id_from = $HTTP_POST_VARS['clone_products_id_from'];
		$clone_product_id_to = $HTTP_POST_VARS['clone_products_id_to'];
		tep_db_query("delete from ".TABLE_PRODUCTS_ATTRIBUTES." WHERE products_id='".$clone_product_id_to."'");
		$attributes = tep_db_query("select products_id, options_id, options_values_id, options_values_price, price_prefix from " . TABLE_PRODUCTS_ATTRIBUTES ." where products_id='".$clone_product_id_from."'");

		while($attributes_values = tep_db_fetch_array($attributes)) {

			tep_db_query("INSERT INTO " . TABLE_PRODUCTS_ATTRIBUTES . " ( products_id, options_id, options_values_id, options_values_price, price_prefix ) VALUES (".$clone_product_id_to.", ".$attributes_values['options_id'].", ".$attributes_values['options_values_id'].", ".$attributes_values['options_values_price'].", '".$attributes_values['price_prefix']."')");

		}
	break;

      case 'add_product_options':

     //BOF - Zappo - Option Types v2 - Update to add option values to products_option.
        $products_options_id = tep_db_prepare_input($HTTP_POST_VARS['products_options_id']);
        $option_name_array = $HTTP_POST_VARS['option_name'];
        $option_cmmnt_array = $HTTP_POST_VARS['option_comment'];
        $option_type = $HTTP_POST_VARS['option_type'];
        $option_length = $HTTP_POST_VARS['option_length'];
        $option_order = $HTTP_POST_VARS['option_order'];

        for ($i=0, $n=sizeof($languages); $i<$n; $i ++) {
          $option_name = tep_db_prepare_input($option_name_array[$languages[$i]['id']]);
          $option_comment = tep_db_prepare_input($option_cmmnt_array[$languages[$i]['id']]);

          tep_db_query("insert into " . TABLE_PRODUCTS_OPTIONS . " (products_options_id, products_options_name, language_id, products_options_type, products_options_length, products_options_comment, products_options_sort_order) values ('" . (int)$products_options_id . "', '" . tep_db_input($option_name) . "', '" . (int)$languages[$i]['id'] . "', '" . $option_type . "', '" . $option_length . "', '" . tep_db_input($option_comment) . "', '" . $option_order . "')");
        }
//EOF - Zappo - Option Types v2 - Update to add option values to products_option.

        tep_redirect(tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES, $page_info));

        break;

      case 'add_product_option_values':

        $value_name_array = $HTTP_POST_VARS['value_name'];

        $value_id = tep_db_prepare_input($HTTP_POST_VARS['value_id']);

        $option_id = tep_db_prepare_input($HTTP_POST_VARS['option_id']);

//BOF - Zappo - Option Types v2 - For TEXT and FILE option types, No need to add anything...
        // Let's Check for OptionType first...
        $optionType_query = tep_db_query("select products_options_type from " . TABLE_PRODUCTS_OPTIONS . " where products_options_id = '" . (int)$option_id . "' and language_id = '" . $languages_id . "'");
        $optionType = tep_db_fetch_array($optionType_query);
        switch ($optionType['products_options_type']) {
          case OPTIONS_TYPE_TEXT:
          case OPTIONS_TYPE_TEXTAREA:
          case OPTIONS_TYPE_FILE:
            // Do Nothing...
          break;
          default:
        for ($i=0, $n=sizeof($languages); $i<$n; $i ++) {
          $value_name = tep_db_prepare_input($value_name_array[$languages[$i]['id']]);

          tep_db_query("insert into " . TABLE_PRODUCTS_OPTIONS_VALUES . "  (products_options_values_id, language_id, products_options_values_name) values ('" . (int)$value_id . "', '" . (int)$languages[$i]['id'] . "', '" . tep_db_input($value_name) . "')");
        }

        tep_db_query("insert into " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " (products_options_id, products_options_values_id) values ('" . (int)$option_id . "', '" . (int)$value_id . "')");

          break;
        }
//EOF - Zappo - Option Types v2 - For TEXT and FILE option types, No need to add anything...
			 tep_redirect(tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES, $page_info));
        break;

      case 'add_product_attributes':

        //BOF - Zappo - Option Types v2 - For TEXT and FILE option types, Lock the value and always use OPTIONS_VALUES_TEXT.
        $products_options_query = tep_db_query("select products_options_type from " . TABLE_PRODUCTS_OPTIONS . " where products_options_id = '" . $HTTP_POST_VARS['options_id'] . "'");
        $products_options_array = tep_db_fetch_array($products_options_query);

        $values_id = tep_db_prepare_input((($products_options_array['products_options_type'] == OPTIONS_TYPE_TEXT) || ($products_options_array['products_options_type'] == OPTIONS_TYPE_TEXTAREA) || ($products_options_array['products_options_type'] == OPTIONS_TYPE_FILE)) ? OPTIONS_VALUE_TEXT_ID : $HTTP_POST_VARS['values_id']);

        $products_id = tep_db_prepare_input($HTTP_POST_VARS['products_id']);
        $options_id = tep_db_prepare_input($HTTP_POST_VARS['options_id']);
//      $values_id = tep_db_prepare_input($HTTP_POST_VARS['values_id']);
//EOF - Zappo - Option Types v2 - For TEXT and FILE option types, Lock the value and always use OPTIONS_VALUES_TEXT.
        $value_price = tep_db_prepare_input($HTTP_POST_VARS['value_price']);
        $price_prefix = tep_db_prepare_input($HTTP_POST_VARS['price_prefix']);
// BOF - Zappo - Option Types v2 - Added Attributes Sort Order
        $value_order = tep_db_prepare_input($HTTP_POST_VARS['value_order']);

        tep_db_query("insert into " . TABLE_PRODUCTS_ATTRIBUTES . "(products_attributes_id ,	products_id ,	options_id ,	options_values_id ,	options_values_price 	, price_prefix 	,products_options_sort_order) values (null, '" . (int)$products_id . "', '" . (int)$options_id . "', '" . (int)$values_id . "', '" . (float)tep_db_input($value_price) . "', '" . tep_db_input($price_prefix) . "', '" . (int)$value_order . "')");
// EOF - Zappo - Option Types v2 - Added Attributes Sort Order

        if (DOWNLOAD_ENABLED == 'true') {

          $products_attributes_id = tep_db_insert_id();



          $products_attributes_filename = tep_db_prepare_input($HTTP_POST_VARS['products_attributes_filename']);

          $products_attributes_maxdays = tep_db_prepare_input($HTTP_POST_VARS['products_attributes_maxdays']);

          $products_attributes_maxcount = tep_db_prepare_input($HTTP_POST_VARS['products_attributes_maxcount']);



          if (tep_not_null($products_attributes_filename)) {

            tep_db_query("insert into " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " values (" . (int)$products_attributes_id . ", '" . tep_db_input($products_attributes_filename) . "', '" . tep_db_input($products_attributes_maxdays) . "', '" . tep_db_input($products_attributes_maxcount) . "')");

          }

        }



        tep_redirect(tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES, $page_info));

        break;

      case 'update_option_name':
//BOF - Zappo - Option Types v2 - Update to add option values to products_option.
        $option_name_array = $HTTP_POST_VARS['option_name'];
        $option_cmmnt_array = $HTTP_POST_VARS['option_comment'];
        $option_type = $HTTP_POST_VARS['option_type'];
        $option_length = $HTTP_POST_VARS['option_length'];
        $option_order = $HTTP_POST_VARS['option_order'];
        $option_id = tep_db_prepare_input($HTTP_POST_VARS['option_id']);

        for ($i=0, $n=sizeof($languages); $i<$n; $i ++) {
          $option_name = tep_db_prepare_input($option_name_array[$languages[$i]['id']]);
          $option_comment = tep_db_prepare_input($option_cmmnt_array[$languages[$i]['id']]);

          tep_db_query("update " . TABLE_PRODUCTS_OPTIONS . " set products_options_name = '" . tep_db_input($option_name) . "', products_options_comment = '" . tep_db_input($option_comment) . "', products_options_type = '" . $option_type . "', products_options_length = '" . $option_length . "', products_options_sort_order = '" . $option_order . "' where products_options_id = '" . (int)$option_id . "' and language_id = '" . (int)$languages[$i]['id'] . "'");
        }

        // - Zappo - Option Types v2 - Automate insertion, deletion or replacement of text option values
        switch ($option_type) {
          case OPTIONS_TYPE_TEXT:
          case OPTIONS_TYPE_TEXTAREA:
          case OPTIONS_TYPE_FILE:
            // Let's Check for pov2po value first... (IF AN OPTION'S TYPE IS CHANGED, ALL OPTION VAlUES ARE LOST!!!)
            $pov2po_query = tep_db_query("select products_options_values_to_products_options_id as id from " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " where products_options_id = '" . (int)$option_id . "'");
            $pov2po = tep_db_fetch_array($pov2po_query);
            if ($pov2po['id']) {
              // - Zappo - Option Types v2 - NEXT LINE DELETES ALL OPTION VALUES IF OPTION TYPE IS CHANGED!!!
              tep_db_query("delete from " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " where products_options_id = '" . $option_id . "'");
            }
            // Now Let's Check for and Update product_attribute values... (IF AN OPTION'S TYPE IS CHANGED, ALL ATTRIBUTES' OPTION VAlUES ARE LOST!!!)
            $done = false;
            $pattrib_query = tep_db_query("select * from " . TABLE_PRODUCTS_ATTRIBUTES . " where options_id = '" . (int)$option_id . "' and options_values_id != '" . OPTIONS_VALUES_TEXT_ID . "'");
            while ($pattrib = tep_db_fetch_array($pattrib_query)) {
              // - Zappo - Option Types v2 - NEXT LINE UPDATES ALL OPTION VALUES IF OPTION TYPE IS CHANGED!!! (You'll probably have some double values!)
              if ($done == false) {
                tep_db_query("update " . TABLE_PRODUCTS_ATTRIBUTES . " set options_values_id = '" . OPTIONS_VALUES_TEXT_ID . "' where options_id = '" . $option_id . "'");
                $done = true;
              }
            }
          default:
            tep_db_query("delete from " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " where products_options_values_id = '" . OPTIONS_VALUES_TEXT_ID . "'");
          break;	
        }
//EOF - Zappo - Option Types v2 - Update to add option values to products_option
        tep_redirect(tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES, $page_info));

        break;

      case 'update_value':
		$value_name_array = $HTTP_POST_VARS['value_name'];

        $value_id = tep_db_prepare_input($HTTP_POST_VARS['value_id']);

        $option_id = tep_db_prepare_input($HTTP_POST_VARS['option_id']);


//BOF - Zappo - Option Types v2 - For TEXT and FILE option types, automatically add OPTIONS_VALUE_TEXT and TEXT_UPLOAD_NAME
        // Let's Check for OptionType first...
        $optionType_query = tep_db_query("select distinct products_options_type from " . TABLE_PRODUCTS_OPTIONS . " where products_options_id = '" . (int)$option_id . "' and language_id = '" . $languages_id . "'");
        $optionType = tep_db_fetch_array($optionType_query);
        switch ($optionType['products_options_type']) {
          case OPTIONS_TYPE_TEXT:
          case OPTIONS_TYPE_TEXTAREA:
          case OPTIONS_TYPE_FILE:
            // Let's Check for pov2po value first... (IF AN OPTION'S TYPE IS CHANGED, ALL OPTION VAlUES ARE LOST!!!)
            $pov2po_query = tep_db_query("select distinct products_options_values_to_products_options_id as id from " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " where products_options_id = '" . (int)$option_id . "'");
            $pov2po = tep_db_fetch_array($optionType_query);
            if ($pov2po['id']) {
              // - Zappo - Option Types v2 - NEXT LINES DELETE ALL OPTION VALUES IF OPTION TYPE IS CHANGED!!!
              tep_db_query("delete from " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " where products_options_id = '" . $option_id . "'");
            }
            tep_db_query("delete from " . TABLE_PRODUCTS_OPTIONS_VALUES . " where products_options_values_id = '" . $value_id . "'");
          break;
          default:
            for ($i=0, $n=sizeof($languages); $i<$n; $i ++) {
              $value_name = tep_db_prepare_input($value_name_array[$languages[$i]['id']]);

              tep_db_query("update " . TABLE_PRODUCTS_OPTIONS_VALUES . " set products_options_values_name = '" . tep_db_input($value_name) . "' where products_options_values_id = '" . tep_db_input($value_id) . "' and language_id = '" . (int)$languages[$i]['id'] . "'");
            }
            tep_db_query("update " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " set products_options_id = '" . (int)$option_id . "'  where products_options_values_id = '" . (int)$value_id . "'");
          break;
        }
//EOF - Zappo - Option Types v2 - For TEXT and FILE option types, automatically add OPTIONS_VALUE_TEXT and TEXT_UPLOAD_NAME

        tep_redirect(tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES, $page_info));

        break;

      case 'update_product_attribute':
//BOF - Zappo - Option Types v2 - Enforce rule that TEXT and FILE Options use value OPTIONS_VALUE_TEXT_ID
        $products_options_query = tep_db_query("select products_options_type from " . TABLE_PRODUCTS_OPTIONS . " where products_options_id = '" . $HTTP_POST_VARS['options_id'] . "'");
        $products_options_array = tep_db_fetch_array($products_options_query);
        switch ($products_options_array['products_options_type']) {
          case OPTIONS_TYPE_TEXT:
          case OPTIONS_TYPE_TEXTAREA:
          case OPTIONS_TYPE_FILE:
            $values_id = OPTIONS_VALUE_TEXT_ID;
          break;
          default: 
          $values_id = tep_db_prepare_input($HTTP_POST_VARS['values_id']);
        }
        $products_id = tep_db_prepare_input($HTTP_POST_VARS['products_id']);
        $options_id = tep_db_prepare_input($HTTP_POST_VARS['options_id']);
//        $values_id = tep_db_prepare_input($HTTP_POST_VARS['values_id']);
//EOF - Zappo - Option Types v2 - Enforce rule that TEXT and FILE Options use value OPTIONS_VALUE_TEXT_ID
        $value_price = tep_db_prepare_input($HTTP_POST_VARS['value_price']);
        $price_prefix = tep_db_prepare_input($HTTP_POST_VARS['price_prefix']);
// BOF - Zappo - Option Types v2 - Added Attributes Sort Order
        $value_order = tep_db_prepare_input($HTTP_POST_VARS['value_order']);
        $attribute_id = tep_db_prepare_input($HTTP_POST_VARS['attribute_id']);

        tep_db_query("update " . TABLE_PRODUCTS_ATTRIBUTES . " set products_id = '" . (int)$products_id . "', options_id = '" . (int)$options_id . "', options_values_id = '" . (int)$values_id . "', options_values_price = '" . (float)tep_db_input($value_price) . "', price_prefix = '" . tep_db_input($price_prefix) . "', products_options_sort_order = '" . tep_db_input($value_order) . "' where products_attributes_id = '" . (int)$attribute_id . "'");
//EOF - Zappo - Option Types v2 - Added Attributes Sort Order


        if (DOWNLOAD_ENABLED == 'true') {

          $products_attributes_filename = tep_db_prepare_input($HTTP_POST_VARS['products_attributes_filename']);

          $products_attributes_maxdays = tep_db_prepare_input($HTTP_POST_VARS['products_attributes_maxdays']);

          $products_attributes_maxcount = tep_db_prepare_input($HTTP_POST_VARS['products_attributes_maxcount']);



          if (tep_not_null($products_attributes_filename)) {

            tep_db_query("replace into " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " set products_attributes_id = '" . (int)$attribute_id . "', products_attributes_filename = '" . tep_db_input($products_attributes_filename) . "', products_attributes_maxdays = '" . tep_db_input($products_attributes_maxdays) . "', products_attributes_maxcount = '" . tep_db_input($products_attributes_maxcount) . "'");

          }

        }



        tep_redirect(tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES, $page_info));

        break;

      case 'delete_option':
       $option_id = tep_db_prepare_input($HTTP_GET_VARS['option_id']);

        tep_db_query("delete from " . TABLE_PRODUCTS_OPTIONS . " where products_options_id = '" . (int)$option_id . "'");
//BOF - Zappo - Option Types v2 - ONE LINE - Added query to auto-delete from pov2po
        tep_db_query("delete from " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " where products_options_id = '" . (int)$option_id . "'");

        tep_redirect(tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES, $page_info));
        break;
      case 'delete_value':
        $value_id = tep_db_prepare_input($HTTP_GET_VARS['value_id']);

        tep_db_query("delete from " . TABLE_PRODUCTS_OPTIONS_VALUES . " where products_options_values_id = '" . (int)$value_id . "'");
        tep_db_query("delete from " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " where products_options_values_id = '" . (int)$value_id . "'");

        tep_redirect(tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES, $page_info));
        break;
      case 'delete_attribute':
        $attribute_id = tep_db_prepare_input($HTTP_GET_VARS['attribute_id']);

        tep_db_query("delete from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_attributes_id = '" . (int)$attribute_id . "'");

// added for DOWNLOAD_ENABLED. Always try to remove attributes, even if downloads are no longer enabled
        tep_db_query("delete from " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " where products_attributes_id = '" . (int)$attribute_id . "'");


        tep_redirect(tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES, $page_info));

        break;
  
    }

  }


  require(DIR_WS_INCLUDES . 'template_top.php');
//BOF - Zappo - Option Types v2 - Define Option Types List
  $products_options_types_list[OPTIONS_TYPE_SELECT] = OPTIONS_TYPE_SELECT_NAME;
  $products_options_types_list[OPTIONS_TYPE_TEXT] = OPTIONS_TYPE_TEXT_NAME;
  $products_options_types_list[OPTIONS_TYPE_TEXTAREA] = OPTIONS_TYPE_TEXTAREA_NAME;
  $products_options_types_list[OPTIONS_TYPE_RADIO] = OPTIONS_TYPE_RADIO_NAME;
  $products_options_types_list[OPTIONS_TYPE_CHECKBOX] = OPTIONS_TYPE_CHECKBOX_NAME;
  //$products_options_types_list[OPTIONS_TYPE_FILE] = OPTIONS_TYPE_FILE_NAME;
  //$products_options_types_list[OPTIONS_TYPE_IMAGE] = OPTIONS_TYPE_IMAGE_NAME;
// Draw a pulldown for Option Types
function draw_optiontype_pulldown($name, $default = '') {
//BOF - Zappo - Option Types v2 - Define Option Types List
  $products_options_types_list[OPTIONS_TYPE_SELECT] = OPTIONS_TYPE_SELECT_NAME;
  $products_options_types_list[OPTIONS_TYPE_TEXT] = OPTIONS_TYPE_TEXT_NAME;
  $products_options_types_list[OPTIONS_TYPE_TEXTAREA] = OPTIONS_TYPE_TEXTAREA_NAME;
  $products_options_types_list[OPTIONS_TYPE_RADIO] = OPTIONS_TYPE_RADIO_NAME;
  $products_options_types_list[OPTIONS_TYPE_CHECKBOX] = OPTIONS_TYPE_CHECKBOX_NAME;
 // $products_options_types_list[OPTIONS_TYPE_FILE] = OPTIONS_TYPE_FILE_NAME;
 // $products_options_types_list[OPTIONS_TYPE_IMAGE] = OPTIONS_TYPE_IMAGE_NAME;
// Draw a pulldown for Option Types
  $values = array();
  foreach ($products_options_types_list as $id => $text) {
    $values[] = array('id' => $id, 'text' => $text);
  }
  return tep_draw_pull_down_menu($name, $values, $default);
}
// Translate option_type_values to english string
function translate_type_to_name($opt_type) {
//BOF - Zappo - Option Types v2 - Define Option Types List
  $products_options_types_list[OPTIONS_TYPE_SELECT] = OPTIONS_TYPE_SELECT_NAME;
  $products_options_types_list[OPTIONS_TYPE_TEXT] = OPTIONS_TYPE_TEXT_NAME;
  $products_options_types_list[OPTIONS_TYPE_TEXTAREA] = OPTIONS_TYPE_TEXTAREA_NAME;
  $products_options_types_list[OPTIONS_TYPE_RADIO] = OPTIONS_TYPE_RADIO_NAME;
  $products_options_types_list[OPTIONS_TYPE_CHECKBOX] = OPTIONS_TYPE_CHECKBOX_NAME;
 // $products_options_types_list[OPTIONS_TYPE_FILE] = OPTIONS_TYPE_FILE_NAME;
 // $products_options_types_list[OPTIONS_TYPE_IMAGE] = OPTIONS_TYPE_IMAGE_NAME;
 // global $products_options_types_list;
  return isset($products_options_types_list[$opt_type]) ? $products_options_types_list[$opt_type] : 'Error ' . $opt_type;
}
//EOF - Zappo - Option Types v2 - Define Option Types List
?>



    <table border="0" width="100%" cellspacing="0" cellpadding="0">

<!-- options and values//-->

      <tr>

        <td width="100%"><table width="100%" border="0" cellspacing="0" cellpadding="0">

          <tr>

            <td valign="top" width="50%"><table width="100%" border="0" cellspacing="0" cellpadding="2">

<!-- options //-->

<?php

  if ($action == 'delete_product_option') { // delete product option

    $options = tep_db_query("select products_options_id, products_options_name from " . TABLE_PRODUCTS_OPTIONS . " where products_options_id = '" . (int)$HTTP_GET_VARS['option_id'] . "' and language_id = '" . (int)$languages_id . "'");

    $options_values = tep_db_fetch_array($options);

?>

              <tr>

                <td class="pageHeading">&nbsp;<?php echo $options_values['products_options_name']; ?>&nbsp;</td>

              </tr>

              <tr>

                <td><table border="0" width="100%" cellspacing="0" cellpadding="2">

                  <tr>

                    <td colspan="3"><?php echo tep_black_line(); ?></td>

                  </tr>

<?php

    $products = tep_db_query("select p.products_id, pd.products_name, pov.products_options_values_name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov, " . TABLE_PRODUCTS_ATTRIBUTES . " pa, " . TABLE_PRODUCTS_DESCRIPTION . " pd where pd.products_id = p.products_id and pov.language_id = '" . (int)$languages_id . "' and pd.language_id = '" . (int)$languages_id . "' and pa.products_id = p.products_id and pa.options_id='" . (int)$HTTP_GET_VARS['option_id'] . "' and pov.products_options_values_id = pa.options_values_id order by pd.products_name");

    if (tep_db_num_rows($products)) {

?>

                  <tr class="dataTableHeadingRow">

                    <td class="dataTableHeadingContent" align="center">&nbsp;<?php echo TABLE_HEADING_ID; ?>&nbsp;</td>

                    <td class="dataTableHeadingContent">&nbsp;<?php echo TABLE_HEADING_PRODUCT; ?>&nbsp;</td>

                    <td class="dataTableHeadingContent">&nbsp;<?php echo TABLE_HEADING_OPT_VALUE; ?>&nbsp;</td>

                  </tr>

                  <tr>

                    <td colspan="3"><?php echo tep_black_line(); ?></td>

                  </tr>

<?php

      $rows = 0;

      while ($products_values = tep_db_fetch_array($products)) {

        $rows++;

?>

                  <tr class="<?php echo (floor($rows/2) == ($rows/2) ? 'attributes-even' : 'attributes-odd'); ?>">

                    <td align="center" class="smallText">&nbsp;<?php echo $products_values['products_id']; ?>&nbsp;</td>

                    <td class="smallText">&nbsp;<?php echo $products_values['products_name']; ?>&nbsp;</td>

                    <td class="smallText">&nbsp;<?php echo $products_values['products_options_values_name']; ?>&nbsp;</td>

                  </tr>

<?php

      }

?>

                  <tr>

                    <td colspan="3"><?php echo tep_black_line(); ?></td>

                  </tr>

                  <tr>

                    <td colspan="3" class="main"><br /><?php echo TEXT_WARNING_OF_DELETE; ?></td>

                  </tr>

                  <tr>

                    <td align="right" colspan="3" class="smallText"><br /><?php echo tep_draw_button(IMAGE_BACK, 'triangle-1-w', tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES, $page_info, 'NONSSL')); ?>&nbsp;</td>

                  </tr>

<?php

    } else {

?>

                  <tr>

                    <td class="main" colspan="3"><br /><?php echo TEXT_OK_TO_DELETE; ?></td>

                  </tr>

                  <tr>

                    <td class="smallText" align="right" colspan="3"><br /><?php echo tep_draw_button(IMAGE_DELETE, 'trash', tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES, 'action=delete_option&option_id=' . $HTTP_GET_VARS['option_id'] . '&' . $page_info, 'NONSSL'), 'primary') . tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES, $page_info, 'NONSSL')); ?>&nbsp;</td>

                  </tr>

<?php

    }

?>

                </table></td>

              </tr>

<?php

  } else {

?>

              <tr>

                <td colspan="3" class="pageHeading">&nbsp;<?php echo HEADING_TITLE_OPT.TEXT_HELP_INSTURCTIONS.TEXT_HELP_VIDEO; ?>&nbsp;</td>

              </tr>

              <tr>

                <td colspan="3" class="smallText" align="right">

<?php

    $options = "select * from " . TABLE_PRODUCTS_OPTIONS . " where language_id = '" . (int)$languages_id . "' order by products_options_id";

    $options_split = new splitPageResults($option_page, MAX_ROW_LISTS_OPTIONS, $options, $options_query_numrows);



    echo $options_split->display_links($options_query_numrows, MAX_ROW_LISTS_OPTIONS, MAX_DISPLAY_PAGE_LINKS, $option_page, 'value_page=' . $value_page . '&attribute_page=' . $attribute_page, 'option_page');
//BOF - Zappo - Option Types v2 - Add column for Option Type, Comment, Length, Sort Order
?>
                </td>
              </tr>
              <tr>
                <td colspan="7"><?php echo tep_black_line(); ?></td>
              </tr>
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent">&nbsp;<?php echo TABLE_HEADING_ID; ?>&nbsp;</td>
                <td class="dataTableHeadingContent">&nbsp;<?php echo TABLE_HEADING_OPT_NAME; ?>&nbsp;</td>
                <td class="dataTableHeadingContent">&nbsp;<?php echo TABLE_HEADING_OPT_TYPE ; ?>&nbsp;</td>
                <td class="dataTableHeadingContent">&nbsp;<?php echo TABLE_HEADING_OPT_ORDER; ?>&nbsp;</td>
                <td class="dataTableHeadingContent">&nbsp;<?php echo TABLE_HEADING_OPT_LENGTH; ?>&nbsp;</td>
                <td class="dataTableHeadingContent">&nbsp;<?php echo TABLE_HEADING_OPT_COMMENT; ?>&nbsp;</td>
                <td class="dataTableHeadingContent" align="center">&nbsp;<?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
              <tr>
                <td colspan="7"><?php echo tep_black_line(); ?></td>
              </tr>
<?php
//EOF - Zappo - Option Types v2 - Add column for Option Type, Comment, Length, Sort Order
  $next_id = 1;
    $rows = 0;
    $options = tep_db_query($options);
    while ($options_values = tep_db_fetch_array($options)) {
      $rows++;
?>
              <tr class="<?php echo (floor($rows/2) == ($rows/2) ? 'attributes-even' : 'attributes-odd'); ?>">
<?php
      if (($action == 'update_option') && ($HTTP_GET_VARS['option_id'] == $options_values['products_options_id'])) {
        echo '<form name="option" action="' . tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES, 'action=update_option_name&' . $page_info, 'NONSSL') . '" method="post">';
//BOF - Zappo - Option Types v2 - Add column for Option Type, Comment, Length, Sort Order
        $NameInput = '';
        $CommentInput = '';
        for ($i = 0, $n = sizeof($languages); $i < $n; $i ++) {
          $option_name = tep_db_query("select products_options_name, products_options_comment from " . TABLE_PRODUCTS_OPTIONS . " where products_options_id = '" . $options_values['products_options_id'] . "' and language_id = '" . $languages[$i]['id'] . "'");
          $option_name = tep_db_fetch_array($option_name);
          $NameInput .= tep_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . ' &nbsp; ' . TABLE_HEADING_OPT_NAME . '<br>' . 
                        '<input type="text" name="option_name[' . $languages[$i]['id'] . ']" size="24" value="' . $option_name['products_options_name'] . '"><br>';
          $CommentInput .= TABLE_HEADING_OPT_COMMENT . '<br><input type="text" name="option_comment[' . $languages[$i]['id'] . ']" size="24" value="' . $option_name['products_options_comment'] . '"><br>';
        }
?>
                <td align="center" class="smallText">&nbsp;<?php echo $options_values['products_options_id']; ?><input type="hidden" name="option_id" value="<?php echo $options_values['products_options_id']; ?>">&nbsp;</td>
                <td class="smallText"><?php echo $NameInput; ?></td>
                <td class="smallText"><?php echo $CommentInput; ?></td>
                <td class="smallText" colspan="3"><?php echo TABLE_HEADING_OPT_LENGTH . ': <input type="text" name="option_length" size="4" value="' . $options_values['products_options_length'] . '"><br>' . 
                                                             TABLE_HEADING_OPT_ORDER . ': <input type="text" name="option_order" size="3" value="' . $options_values['products_options_sort_order'] . '"><br>' . 
                                                             TABLE_HEADING_OPT_TYPE . ': ' . draw_optiontype_pulldown('option_type', $options_values['products_options_type']); ?></td>
                <td align="center" class="smallText">&nbsp;<?php echo tep_draw_button(IMAGE_SAVE, 'disk', null, 'primary') . tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES, $page_info, 'NONSSL')); ?>&nbsp;</td>
<?php
        echo '</form>' . "\n";
      } else {
?>
                <td align="center" class="smallText">&nbsp;<?php echo $options_values["products_options_id"]; ?>&nbsp;</td>
                <td class="smallText">&nbsp;<?php echo $options_values["products_options_name"]; ?>&nbsp;</td>
                <td class="smallText"><?php echo translate_type_to_name($options_values["products_options_type"]); ?></td>
                <td class="smallText"><?php echo $options_values["products_options_sort_order"]; ?></td>
                <td class="smallText">&nbsp;<?php echo $options_values["products_options_length"]; ?>&nbsp;</td>
                <td class="smallText"><?php echo $options_values["products_options_comment"]; ?></td>
                <td align="center" class="smallText">&nbsp;<?php echo tep_draw_button(IMAGE_EDIT, 'document', tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES, 'action=update_option&option_id=' . $options_values['products_options_id'] . '&option_order_by=' . $option_order_by . '&option_page=' . $option_page . '&' . $page_info, 'NONSSL')) . tep_draw_button(IMAGE_DELETE, 'trash', tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES, 'action=delete_product_option&option_id=' . $options_values['products_options_id'] . '&' . $page_info, 'NONSSL')); ?>&nbsp;</td>
<?php
      }
?>
              </tr>
<?php
      $max_options_id_query = tep_db_query("select max(products_options_id) + 1 as next_id from " . TABLE_PRODUCTS_OPTIONS);
      $max_options_id_values = tep_db_fetch_array($max_options_id_query);
      $next_id = $max_options_id_values['next_id'];
    }
?>
              <tr>
                <td colspan="7"><?php echo tep_black_line(); ?></td>
              </tr>
<?php
    if ($action != 'update_option') {
?>
              <tr class="<?php echo (floor($rows/2) == ($rows/2) ? 'attributes-even' : 'attributes-odd'); ?>">
<?php
      echo '<form name="options" action="' . tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES, 'action=add_product_options&' . $page_info, 'NONSSL') . '" method="post"><input type="hidden" name="products_options_id" value="' . $next_id . '">';
        $NameInput = '';
        $CommentInput = '';
        for ($i = 0, $n = sizeof($languages); $i < $n; $i ++) {
          $NameInput .= tep_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . ' &nbsp; ' . TABLE_HEADING_OPT_NAME . ':<br>' . 
                        '<input type="text" name="option_name[' . $languages[$i]['id'] . ']" size="24"><br>';
          $CommentInput .= TABLE_HEADING_OPT_COMMENT . ':<br><input type="text" name="option_comment[' . $languages[$i]['id'] . ']" size="24"><br>';
      }
?>
                <td align="center" class="smallText">&nbsp;<?php echo $next_id; ?>&nbsp;</td>
                <td class="smallText"><?php echo $NameInput; ?></td>
                <td class="smallText"><?php echo $CommentInput; ?></td>
                <td class="smallText" colspan="3"><?php echo TABLE_HEADING_OPT_LENGTH . ': <input type="text" name="option_length" size="4"><br>' . 
                                                             TABLE_HEADING_OPT_ORDER . ': <input type="text" name="option_order" size="3"><br>' . 
                                                             TABLE_HEADING_OPT_TYPE . ': ' . draw_optiontype_pulldown('option_type'); ?></td>
                <td align="center" class="smallText">&nbsp;<?php echo tep_draw_button(IMAGE_INSERT, 'plus'); ?>&nbsp;</td>
<?php
      echo '</form>';
?>
              </tr>
              <tr>
                <td colspan="7"><?php echo tep_black_line(); ?></td>
              </tr>
<?php
//EOF - Zappo - Option Types v2 - Add column for Option Type, Comment, Length, Sort Order
    }
  }
?>
            </table></td>
<!-- options eof //-->
            <td valign="top" width="50%"><table width="100%" border="0" cellspacing="0" cellpadding="2">
<!-- value //-->
<?php
  if ($action == 'delete_option_value') { // delete product option value
    $values = tep_db_query("select products_options_values_id, products_options_values_name from " . TABLE_PRODUCTS_OPTIONS_VALUES . " where products_options_values_id = '" . (int)$HTTP_GET_VARS['value_id'] . "' and language_id = '" . (int)$languages_id . "'");
    $values_values = tep_db_fetch_array($values);
?>
              <tr>
                <td colspan="3" class="pageHeading">&nbsp;<?php echo $values_values['products_options_values_name']; ?>&nbsp;</td>
              </tr>
              <tr>
                <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td colspan="3"><?php echo tep_black_line(); ?></td>
                  </tr>
<?php
    $products = tep_db_query("select p.products_id, pd.products_name, po.products_options_name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_ATTRIBUTES . " pa, " . TABLE_PRODUCTS_OPTIONS . " po, " . TABLE_PRODUCTS_DESCRIPTION . " pd where pd.products_id = p.products_id and pd.language_id = '" . (int)$languages_id . "' and po.language_id = '" . (int)$languages_id . "' and pa.products_id = p.products_id and pa.options_values_id='" . (int)$HTTP_GET_VARS['value_id'] . "' and po.products_options_id = pa.options_id order by pd.products_name");
    if (tep_db_num_rows($products)) {
?>
                  <tr class="dataTableHeadingRow">
                    <td class="dataTableHeadingContent" align="center">&nbsp;<?php echo TABLE_HEADING_ID; ?>&nbsp;</td>
                    <td class="dataTableHeadingContent">&nbsp;<?php echo TABLE_HEADING_PRODUCT; ?>&nbsp;</td>
                    <td class="dataTableHeadingContent">&nbsp;<?php echo TABLE_HEADING_OPT_NAME; ?>&nbsp;</td>
                  </tr>
                  <tr>
                    <td colspan="3"><?php echo tep_black_line(); ?></td>
                  </tr>
<?php
      while ($products_values = tep_db_fetch_array($products)) {
        $rows++;
?>
                  <tr class="<?php echo (floor($rows/2) == ($rows/2) ? 'attributes-even' : 'attributes-odd'); ?>">
                    <td align="center" class="smallText">&nbsp;<?php echo $products_values['products_id']; ?>&nbsp;</td>
                    <td class="smallText">&nbsp;<?php echo $products_values['products_name']; ?>&nbsp;</td>
                    <td class="smallText">&nbsp;<?php echo $products_values['products_options_name']; ?>&nbsp;</td>
                  </tr>
<?php
      }
?>
                  <tr>
                    <td colspan="3"><?php echo tep_black_line(); ?></td>
                  </tr>
                  <tr>
                    <td class="main" colspan="3"><br><?php echo TEXT_WARNING_OF_DELETE; ?></td>
                  </tr>
                  <tr>
                    <td class="main" align="right" colspan="3"><br><?php echo tep_draw_button(IMAGE_BACK, 'triangle-1-w', tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES, $page_info, 'NONSSL')); ?>&nbsp;</td>
                  </tr>
<?php
    } else {
?>
                  <tr>
                    <td class="main" colspan="3"><br><?php echo TEXT_OK_TO_DELETE; ?></td>
                  </tr>
                  <tr>
                    <td class="main" align="right" colspan="3"><br><?php echo tep_draw_button(IMAGE_DELETE, 'trash', tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES, 'action=delete_option&option_id=' . $HTTP_GET_VARS['option_id'] . '&' . $page_info, 'NONSSL'), 'primary') . tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES, $page_info, 'NONSSL')); ?>&nbsp;</td>
                  </tr>
<?php
    }
?>
              	</table></td>
              </tr>
<?php
  } else {
?>
              <tr>
                <td colspan="4" class="pageHeading">&nbsp;<?php echo HEADING_TITLE_VAL; ?>&nbsp;</td>
              </tr>
              <tr>
                <td colspan="4" class="smallText" align="right">
<?php
    $values = "select pov.products_options_values_id, pov.products_options_values_name, pov2po.products_options_id from " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov left join " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " pov2po on pov.products_options_values_id = pov2po.products_options_values_id where pov.language_id = '" . (int)$languages_id . "' order by pov.products_options_values_id";
    $values_split = new splitPageResults($value_page, MAX_ROW_LISTS_OPTIONS, $values, $values_query_numrows);

    echo $values_split->display_links($values_query_numrows, MAX_ROW_LISTS_OPTIONS, MAX_DISPLAY_PAGE_LINKS, $value_page, 'option_page=' . $option_page . '&attribute_page=' . $attribute_page, 'value_page');
?>
                </td>
              </tr>
              <tr>
                <td colspan="4"><?php echo tep_black_line(); ?></td>
              </tr>
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent">&nbsp;<?php echo TABLE_HEADING_ID; ?>&nbsp;</td>
                <td class="dataTableHeadingContent">&nbsp;<?php echo TABLE_HEADING_OPT_NAME; ?>&nbsp;</td>
                <td class="dataTableHeadingContent">&nbsp;<?php echo TABLE_HEADING_OPT_VALUE; ?>&nbsp;</td>
                <td class="dataTableHeadingContent" align="center">&nbsp;<?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
              <tr>
                <td colspan="4"><?php echo tep_black_line(); ?></td>
              </tr>
<?php
    $next_id = 1;
    $rows = 0;
    $values = tep_db_query($values);
    while ($values_values = tep_db_fetch_array($values)) {
      $options_name = tep_options_name($values_values['products_options_id']);
//BOF - Zappo - Option Types v2 - ONE LINE - Fetch products_options_id for deleting the option value
//    (TEXT and FILE Options use the same CUSTOMER-INPUT value, and only one reference should be deleted)
      $option_id = $values_values['products_options_id'];
      $values_name = $values_values['products_options_values_name'];
      $rows++;
?>
              <tr class="<?php echo (floor($rows/2) == ($rows/2) ? 'attributes-even' : 'attributes-odd'); ?>">
<?php
      if (($action == 'update_option_value') && ($HTTP_GET_VARS['value_id'] == $values_values['products_options_values_id'])) {
        echo '<form name="values" action="' . tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES, 'action=update_value&' . $page_info, 'NONSSL') . '" method="post">';
        $inputs = '';
        for ($i = 0, $n = sizeof($languages); $i < $n; $i ++) {
          $value_name = tep_db_query("select products_options_values_name from " . TABLE_PRODUCTS_OPTIONS_VALUES . " where products_options_values_id = '" . (int)$values_values['products_options_values_id'] . "' and language_id = '" . (int)$languages[$i]['id'] . "'");
          $value_name = tep_db_fetch_array($value_name);
          $inputs .= $languages[$i]['code'] . ':&nbsp;<input type="text" name="value_name[' . $languages[$i]['id'] . ']" size="15" value="' . $value_name['products_options_values_name'] . '">&nbsp;<br>';
        }
?>
                <td align="center" class="smallText">&nbsp;<?php echo $values_values['products_options_values_id']; ?><input type="hidden" name="value_id" value="<?php echo $values_values['products_options_values_id']; ?>">&nbsp;</td>
                <td align="center" class="smallText">&nbsp;<?php echo "\n"; ?><select name="option_id">
<?php
//BOF - Zappo - Option Types v2 - Exclude Text and Upload Options from Dropdown
        $options = tep_db_query("select products_options_id, products_options_name, products_options_type from " . TABLE_PRODUCTS_OPTIONS . " where language_id = '" . (int)$languages_id . "' order by products_options_name");
        while ($options_values = tep_db_fetch_array($options)) {
          switch ($options_values['products_options_type']) {
            case OPTIONS_TYPE_TEXT:
            case OPTIONS_TYPE_TEXTAREA:
            case OPTIONS_TYPE_FILE:
              // Exclude from dropdown
            break;
            default:
              echo "\n" . '<option name="' . $options_values['products_options_name'] . '" value="' . $options_values['products_options_id'] . '"';
              if ($values_values['products_options_id'] == $options_values['products_options_id']) { 
                echo ' selected';
              }
              echo '>' . $options_values['products_options_name'] . '</option>';
            }
//EOF - Zappo - Option Types v2 - Exclude Text and Upload Options from Dropdown
        }
?>
                </select>&nbsp;</td>
                <td class="smallText"><?php echo $inputs; ?></td>
                <td align="center" class="smallText">&nbsp;<?php echo tep_draw_button(IMAGE_SAVE, 'disk', null, 'primary') . tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES, $page_info, 'NONSSL')); ?>&nbsp;</td>
<?php
        echo '</form>';
      } else {
//BOF - Zappo - Option Types v2 - Added option ID to Delete button's href && Lock OPTIONS_VALUE_TEXT
?>
                <td align="center" class="smallText">&nbsp;<?php echo $values_values["products_options_values_id"]; ?>&nbsp;</td>
                <td align="center" class="smallText">&nbsp;<?php echo $options_name; ?>&nbsp;</td>
                <td class="smallText">&nbsp;<?php echo $values_name; ?>&nbsp;</td>
                <td align="center" class="smallText" height="24">&nbsp;<?php if ($values_values["products_options_values_id"] != OPTIONS_VALUE_TEXT_ID) {
				?>
				<?php echo tep_draw_button(IMAGE_EDIT, 'document', tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES, 'action=update_option_value&value_id=' . $values_values['products_options_values_id'] . '&' . $page_info, 'NONSSL')) . tep_draw_button(IMAGE_DELETE, 'trash', tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES, 'action=delete_option_value&value_id=' . $values_values['products_options_values_id'] . '&' . $page_info, 'NONSSL')); ?>
                  <?php
                } ?></a>&nbsp;</td>
<?php
//EOF - Zappo - Option Types v2 - Added option ID to Delete button's href && Lock OPTIONS_VALUE_TEXT
      }
      $max_values_id_query = tep_db_query("select max(products_options_values_id) + 1 as next_id from " . TABLE_PRODUCTS_OPTIONS_VALUES);
      $max_values_id_values = tep_db_fetch_array($max_values_id_query);
      $next_id = $max_values_id_values['next_id'];
    }
?>
              </tr>
              <tr>
                <td colspan="4"><?php echo tep_black_line(); ?></td>
              </tr>
<?php
    if ($action != 'update_option_value') {
?>
              <tr class="<?php echo (floor($rows/2) == ($rows/2) ? 'attributes-even' : 'attributes-odd'); ?>">
<?php
      echo '<form name="values" action="' . tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES, 'action=add_product_option_values&' . $page_info, 'NONSSL') . '" method="post">';
?>
                <td align="center" class="smallText">&nbsp;<?php echo $next_id; ?>&nbsp;</td>
                <td align="center" class="smallText">&nbsp;<select name="option_id">
<?php
//BOF - Zappo - Option Types v2 - Exclude Text and Upload Options from Dropdown
      $options = tep_db_query("select products_options_id, products_options_name, products_options_type from " . TABLE_PRODUCTS_OPTIONS . " where language_id = '" . $languages_id . "' order by products_options_name");
      while ($options_values = tep_db_fetch_array($options)) {
        switch ($options_values['products_options_type']) {
          case OPTIONS_TYPE_TEXT:
          case OPTIONS_TYPE_TEXTAREA:
          case OPTIONS_TYPE_FILE:
            // Exclude from dropdown
          break;
          default:
            echo '<option name="' . $options_values['products_options_name'] . '" value="' . $options_values['products_options_id'] . '">' . $options_values['products_options_name'] . '</option>';
        }
//EOF - Zappo - Option Types v2 - Exclude Text and Upload Options from Dropdown
      }

      $inputs = '';
      for ($i = 0, $n = sizeof($languages); $i < $n; $i ++) {
        $inputs .= $languages[$i]['code'] . ':&nbsp;<input type="text" name="value_name[' . $languages[$i]['id'] . ']" size="15">&nbsp;<br>';
      }
?>
                </select>&nbsp;</td>
                <td class="smallText"><input type="hidden" name="value_id" value="<?php echo $next_id; ?>"><?php echo $inputs; ?></td>
                <td align="center" class="smallText">&nbsp;<?php echo tep_draw_button(IMAGE_INSERT, 'plus'); ?>&nbsp;</td>
<?php
      echo '</form>';
?>
              </tr>
              <tr>
                <td colspan="4"><?php echo tep_black_line(); ?></td>
              </tr>
<?php
    }
  }
?>
            </table></td>
          </tr>
        </table></td>
<!-- option value eof //-->

<!-- products_attributes //-->  
 <tr>
        <td class="smallText">&nbsp;</td>
      </tr>
      <tr>
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading">&nbsp;<?php echo HEADING_TITLE_ATRIB; ?>&nbsp;</td>
          </tr>
        </table></td>
      </tr>
      <tr>
<?php
  if ($action == 'update_attribute') {
    $form_action = 'update_product_attribute';
  } else {
    $form_action = 'add_product_attributes';
  }
?>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td class="smallText" align="right">
<?php
  $attributes = "select pa.* from " . TABLE_PRODUCTS_ATTRIBUTES . " pa left join " . TABLE_PRODUCTS_DESCRIPTION . " pd on pa.products_id = pd.products_id and pd.language_id = '" . (int)$languages_id . "' order by pd.products_name";
  $attributes_split = new splitPageResults($attribute_page, MAX_ROW_LISTS_OPTIONS, $attributes, $attributes_query_numrows);

  echo $attributes_split->display_links($attributes_query_numrows, MAX_ROW_LISTS_OPTIONS, MAX_DISPLAY_PAGE_LINKS, $attribute_page, 'option_page=' . $option_page . '&value_page=' . $value_page, 'attribute_page');
// BOF - Zappo - Option Types v2 - Added Attributes Sort Order
?>
            </td>
          </tr>
        </table>
        <form name="attributes" action="<?php echo tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES, 'action=' . $form_action . '&' . $page_info); ?>" method="post"><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td colspan="8"><?php echo tep_black_line(); ?></td>
          </tr>
          <tr class="dataTableHeadingRow">
            <td class="dataTableHeadingContent">&nbsp;<?php echo TABLE_HEADING_ID; ?>&nbsp;</td>
            <td class="dataTableHeadingContent">&nbsp;<?php echo TABLE_HEADING_PRODUCT; ?>&nbsp;</td>
            <td class="dataTableHeadingContent">&nbsp;<?php echo TABLE_HEADING_OPT_NAME; ?>&nbsp;</td>
            <td class="dataTableHeadingContent">&nbsp;<?php echo TABLE_HEADING_OPT_VALUE; ?>&nbsp;</td>
            <td class="dataTableHeadingContent" align="right">&nbsp;<?php echo TABLE_HEADING_OPT_PRICE; ?>&nbsp;</td>
            <td class="dataTableHeadingContent" align="center">&nbsp;<?php echo TABLE_HEADING_OPT_PRICE_PREFIX; ?>&nbsp;</td>
            <td class="dataTableHeadingContent" align="center">&nbsp;<?php echo TABLE_HEADING_OPT_ORDER; ?>&nbsp;</td>
            <td class="dataTableHeadingContent" align="center">&nbsp;<?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
          </tr>
          <tr>
            <td colspan="8"><?php echo tep_black_line(); ?></td>
          </tr>
<?php
// EOF - Zappo - Option Types v2 - Added Attributes Sort Order
  $next_id = 1;
  $attributes = tep_db_query($attributes);
  while ($attributes_values = tep_db_fetch_array($attributes)) {
    $products_name_only = tep_get_products_name($attributes_values['products_id']);
    $options_name = tep_options_name($attributes_values['options_id']);
    $values_name = tep_values_name($attributes_values['options_values_id']);
    $rows++;
?>
          <tr class="<?php echo (floor($rows/2) == ($rows/2) ? 'attributes-even' : 'attributes-odd'); ?>">
<?php
    if (($action == 'update_attribute') && ($HTTP_GET_VARS['attribute_id'] == $attributes_values['products_attributes_id'])) {
?>
            <td class="smallText">&nbsp;<?php echo $attributes_values['products_attributes_id']; ?><input type="hidden" name="attribute_id" value="<?php echo $attributes_values['products_attributes_id']; ?>">&nbsp;</td>
            <td class="smallText">&nbsp;<select name="products_id">
<?php
      $products = tep_db_query("select p.products_id, pd.products_name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where pd.products_id = p.products_id and pd.language_id = '" . $languages_id . "' order by pd.products_name");
      while($products_values = tep_db_fetch_array($products)) {
        if ($attributes_values['products_id'] == $products_values['products_id']) {
          echo "\n" . '<option name="' . $products_values['products_name'] . '" value="' . $products_values['products_id'] . '" SELECTED>' . $products_values['products_name'] . '</option>';
        } else {
          echo "\n" . '<option name="' . $products_values['products_name'] . '" value="' . $products_values['products_id'] . '">' . $products_values['products_name'] . '</option>';
        }
      } 
?>
            </select>&nbsp;</td>
            <td class="smallText">&nbsp;<select name="options_id">
<?php
      $options = tep_db_query("select * from " . TABLE_PRODUCTS_OPTIONS . " where language_id = '" . $languages_id . "' order by products_options_name");
      while($options_values = tep_db_fetch_array($options)) {
        if ($attributes_values['options_id'] == $options_values['products_options_id']) {
          echo "\n" . '<option name="' . $options_values['products_options_name'] . '" value="' . $options_values['products_options_id'] . '" SELECTED>' . $options_values['products_options_name'] . '</option>';
        } else {
          echo "\n" . '<option name="' . $options_values['products_options_name'] . '" value="' . $options_values['products_options_id'] . '">' . $options_values['products_options_name'] . '</option>';
        }
      } 
?>
            </select>&nbsp;</td>
            <td class="smallText">&nbsp;<select name="values_id">
<?php
//BOF - Zappo - Option Types v2 - ONE LINE - Exclude Text and Upload Option Values from Dropdown (They are Auto-Added)
      $values = tep_db_query("select * from " . TABLE_PRODUCTS_OPTIONS_VALUES . " where products_options_values_id != '0' and language_id ='" . $languages_id . "' order by products_options_values_name");
      while($values_values = tep_db_fetch_array($values)) {
        if ($attributes_values['options_values_id'] == $values_values['products_options_values_id']) {
          echo "\n" . '<option name="' . $values_values['products_options_values_name'] . '" value="' . $values_values['products_options_values_id'] . '" SELECTED>' . $values_values['products_options_values_name'] . '</option>';
        } else {
          echo "\n" . '<option name="' . $values_values['products_options_values_name'] . '" value="' . $values_values['products_options_values_id'] . '">' . $values_values['products_options_values_name'] . '</option>';
        }
      } 
//BOF - Zappo - Option Types v2 - Added Attributes Sort Order
?>
            </select>&nbsp;</td>
            <td align="right" class="smallText">&nbsp;<input type="text" name="value_price" value="<?php echo $attributes_values['options_values_price']; ?>" size="6">&nbsp;</td>
            <td align="center" class="smallText">&nbsp;<input type="text" name="price_prefix" value="<?php echo $attributes_values['price_prefix']; ?>" size="2">&nbsp;</td>
            <td align="center" class="smallText">&nbsp;<input type="text" name="value_order" value="<?php echo $attributes_values['products_options_sort_order']; ?>" size="2">&nbsp;</td>
            <td align="center" class="smallText">&nbsp;<?php echo tep_draw_button(IMAGE_SAVE, 'disk', null, 'primary') . tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES, $page_info, 'NONSSL')); ?>&&nbsp;</td>
<?php
//EOF - Zappo - Option Types v2 - Added Attributes Sort Order
      if (DOWNLOAD_ENABLED == 'true') {
        $download_query_raw ="select products_attributes_filename, products_attributes_maxdays, products_attributes_maxcount 
                              from " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " 
                              where products_attributes_id='" . $attributes_values['products_attributes_id'] . "'";
        $download_query = tep_db_query($download_query_raw);
        if (tep_db_num_rows($download_query) > 0) {
          $download = tep_db_fetch_array($download_query);
          $products_attributes_filename = $download['products_attributes_filename'];
          $products_attributes_maxdays  = $download['products_attributes_maxdays'];
          $products_attributes_maxcount = $download['products_attributes_maxcount'];
        }
?>
          <tr class="<?php echo (!($rows % 2)? 'attributes-even' : 'attributes-odd');?>">
            <td>&nbsp;</td>
            <td colspan="5">
              <table>
                <tr class="<?php echo (!($rows % 2)? 'attributes-even' : 'attributes-odd');?>">
                  <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_DOWNLOAD; ?>&nbsp;</td>
                  <td class="smallText"><?php echo TABLE_TEXT_FILENAME; ?></td>
                  <td class="smallText"><?php echo tep_draw_input_field('products_attributes_filename', $products_attributes_filename, 'size="15"'); ?>&nbsp;</td>
                  <td class="smallText"><?php echo TABLE_TEXT_MAX_DAYS; ?></td>
                  <td class="smallText"><?php echo tep_draw_input_field('products_attributes_maxdays', $products_attributes_maxdays, 'size="5"'); ?>&nbsp;</td>
                  <td class="smallText"><?php echo TABLE_TEXT_MAX_COUNT; ?></td>
                  <td class="smallText"><?php echo tep_draw_input_field('products_attributes_maxcount', $products_attributes_maxcount, 'size="5"'); ?>&nbsp;</td>
                </tr>
              </table>
            </td>
            <td>&nbsp;</td>
          </tr>
<?php
      }
?>
<?php
    } elseif (($action == 'delete_product_attribute') && ($HTTP_GET_VARS['attribute_id'] == $attributes_values['products_attributes_id'])) {
//BOF - Zappo - Option Types v2 - Added Attributes Sort Order
?>
            <td class="smallText">&nbsp;<b><?php echo $attributes_values["products_attributes_id"]; ?></b>&nbsp;</td>
            <td class="smallText">&nbsp;<b><?php echo $products_name_only; ?></b>&nbsp;</td>
            <td class="smallText">&nbsp;<b><?php echo $options_name; ?></b>&nbsp;</td>
            <td class="smallText">&nbsp;<b><?php echo $values_name; ?></b>&nbsp;</td>
            <td align="right" class="smallText">&nbsp;<b><?php echo $attributes_values["options_values_price"]; ?></b>&nbsp;</td>
            <td align="center" class="smallText">&nbsp;<b><?php echo $attributes_values["price_prefix"]; ?></b>&nbsp;</td>
            <td align="center" class="smallText">&nbsp;<b><?php echo $attributes_values["products_options_sort_order"]; ?></b>&nbsp;</td>
            <td align="center" class="smallText">&nbsp;<b><?php echo '<a href="' . tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES, 'action=delete_attribute&attribute_id=' . $HTTP_GET_VARS['attribute_id'] . '&' . $page_info) . '">'; ?><?php echo tep_draw_button(IMAGE_DELETE, 'trash', tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES, 'action=delete_attribute&attribute_id=' . $HTTP_GET_VARS['attribute_id'] . '&' . $page_info), 'primary') . tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES, $page_info, 'NONSSL')); ?>&nbsp;</b></td>
<?php
    } else {
?>
            <td class="smallText">&nbsp;<?php echo $attributes_values["products_attributes_id"]; ?>&nbsp;</td>
            <td class="smallText">&nbsp;<?php echo $products_name_only; ?>&nbsp;</td>
            <td class="smallText">&nbsp;<?php echo $options_name; ?>&nbsp;</td>
            <td class="smallText">&nbsp;<?php echo $values_name; ?>&nbsp;</td>
            <td align="right" class="smallText">&nbsp;<?php echo $attributes_values["options_values_price"]; ?>&nbsp;</td>
            <td align="center" class="smallText">&nbsp;<?php echo $attributes_values["price_prefix"]; ?>&nbsp;</td>
            <td align="center" class="smallText">&nbsp;<?php echo $attributes_values["products_options_sort_order"]; ?>&nbsp;</td>
            <td align="center" class="smallText">&nbsp;<?php echo tep_draw_button(IMAGE_EDIT, 'document', tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES, 'action=update_attribute&attribute_id=' . $attributes_values['products_attributes_id'] . '&' . $page_info, 'NONSSL')) ; ?>&nbsp;&nbsp;&nbsp;<?php echo tep_draw_button(IMAGE_DELETE, 'trash', tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES, 'action=delete_product_attribute&attribute_id=' . $attributes_values['products_attributes_id'] . '&' . $page_info, 'NONSSL')); // EOF SPPC attributes hide for groups mod

						?>&nbsp;</td>
<?php
//EOF - Zappo - Option Types v2 - Added Attributes Sort Order
    }
    $max_attributes_id_query = tep_db_query("select max(products_attributes_id) + 1 as next_id from " . TABLE_PRODUCTS_ATTRIBUTES);
    $max_attributes_id_values = tep_db_fetch_array($max_attributes_id_query);
    $next_id = $max_attributes_id_values['next_id'];
?>
          </tr>
<?php
  }

  if ($action != 'update_attribute') {
//BOF - Zappo - Option Types v2 - ONE LINE - Added Attributes Sort Order - Added 1 colspan (two down)
?>
          <tr>
            <td colspan="8"><?php echo tep_black_line(); ?></td>
          </tr>
          <tr class="<?php echo (floor($rows/2) == ($rows/2) ? 'attributes-even' : 'attributes-odd'); ?>">
            <td class="smallText">&nbsp;<?php echo $next_id; ?>&nbsp;</td>
      	    <td class="smallText">&nbsp;<select name="products_id">
<?php
    $products = tep_db_query("select p.products_id, pd.products_name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where pd.products_id = p.products_id and pd.language_id = '" . $languages_id . "' order by pd.products_name");
    while ($products_values = tep_db_fetch_array($products)) {
      echo '<option name="' . $products_values['products_name'] . '" value="' . $products_values['products_id'] . '">' . $products_values['products_name'] . '</option>';
    } 
?>
            </select>&nbsp;</td>
            <td class="smallText">&nbsp;<select name="options_id">
<?php
    $options = tep_db_query("select * from " . TABLE_PRODUCTS_OPTIONS . " where language_id = '" . $languages_id . "' order by products_options_name");
    while ($options_values = tep_db_fetch_array($options)) {
      echo '<option name="' . $options_values['products_options_name'] . '" value="' . $options_values['products_options_id'] . '">' . $options_values['products_options_name'] . '</option>';
    } 
?>
            </select>&nbsp;</td>
            <td class="smallText">&nbsp;<select name="values_id">
<?php
//BOF - Zappo - Option Types v2 - ONE LINE - Exclude Text and Upload Option Values from Dropdown (They are Auto-Added)
    $values = tep_db_query("select * from " . TABLE_PRODUCTS_OPTIONS_VALUES . " where  language_id = '" . $languages_id . "' order by products_options_values_name");
    while ($values_values = tep_db_fetch_array($values)) {
      echo '<option name="' . $values_values['products_options_values_name'] . '" value="' . $values_values['products_options_values_id'] . '">' . $values_values['products_options_values_name'] . '</option>';
    } 
//BOF - Zappo - Option Types v2 - Added Attributes Sort Order
?>
            </select>&nbsp;</td>
            <td align="right" class="smallText">&nbsp;<input type="text" name="value_price" size="6">&nbsp;</td>
            <td align="right" class="smallText">&nbsp;<input type="text" name="price_prefix" size="2" value="+">&nbsp;</td>
            <td align="right" class="smallText">&nbsp;<input type="text" name="value_order" size="2" value="1">&nbsp;</td>
            <td align="center" class="smallText">&nbsp;<?php echo tep_draw_button(IMAGE_INSERT, 'plus'); ?>&nbsp;</td>
          </tr>
		 <tr><td colspan="9">* For Text and teaxtarea  select any  Option Value  or leave it as   They are Auto-Added</td></tr> 
<?php
//EOF - Zappo - Option Types v2 - Added Attributes Sort Order
      if (DOWNLOAD_ENABLED == 'true') {
        $products_attributes_maxdays  = DOWNLOAD_MAX_DAYS;
        $products_attributes_maxcount = DOWNLOAD_MAX_COUNT;
?>
          <tr class="<?php echo (!($rows % 2)? 'attributes-even' : 'attributes-odd');?>">
            <td>&nbsp;</td>
            <td colspan="5">
              <table>
                <tr class="<?php echo (!($rows % 2)? 'attributes-even' : 'attributes-odd');?>">
                  <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_DOWNLOAD; ?>&nbsp;</td>
                  <td class="smallText"><?php echo TABLE_TEXT_FILENAME; ?></td>
                  <td class="smallText"><?php echo tep_draw_input_field('products_attributes_filename', $products_attributes_filename, 'size="15"'); ?>&nbsp;</td>
                  <td class="smallText"><?php echo TABLE_TEXT_MAX_DAYS; ?></td>
                  <td class="smallText"><?php echo tep_draw_input_field('products_attributes_maxdays', $products_attributes_maxdays, 'size="5"'); ?>&nbsp;</td>
                  <td class="smallText"><?php echo TABLE_TEXT_MAX_COUNT; ?></td>
                  <td class="smallText"><?php echo tep_draw_input_field('products_attributes_maxcount', $products_attributes_maxcount, 'size="5"'); ?>&nbsp;</td>
                </tr>
              </table>
            </td>
            <td>&nbsp;</td>
          </tr>
<?php
      } // end of DOWNLOAD_ENABLED section
?>
<?php
  }

?>

          <tr>

            <td colspan="7"><?php echo tep_black_line(); ?></td>

          </tr>

        </table></form></td>

      </tr>
      
      <tr><td>
     	<?php
	  echo '<form name="clone" action="' . tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES, 'action=clone_attributes', 'NONSSL') . '" method="post">';
	?>
	<Table>
	<!-- Data Start -->
        <tr><td class="pageHeading">Clone the attributes of an article</td></tr>
	<tr>

	<td class="smallText">From
	<select name="clone_products_id_from">
	<?php
	$products = tep_db_query("select p.products_id, pd.products_name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where pd.products_id = p.products_id and pd.language_id = '" . $languages_id . "' order by pd.products_name");
	while ($products_values = tep_db_fetch_array($products)) {
	echo '<option name="' . $products_values['products_name'] . '" value="' . $products_values['products_id'] . '">' . $products_values['products_name'] . '</option>';
	}
	?>
	</select></td>
	<td class="smallText">To
	<select name="clone_products_id_to">
	<?php
	$products = tep_db_query("select p.products_id, pd.products_name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where pd.products_id = p.products_id and pd.language_id = '" . $languages_id . "' order by pd.products_name");
	while ($products_values = tep_db_fetch_array($products)) {
	echo '<option name="' . $products_values['products_name'] . '" value="' . $products_values['products_id'] . '">' . $products_values['products_name'] . '</option>';
	}
	?>
	</select>
	</td><td><?php echo tep_image_submit('button_update.gif', IMAGE_UPDATE); ?></td></tr>
	<tr><td>
	</td></tr>
	<!-- Data End -->


    </table></td>

  </tr>

    </table>



<?php

  require(DIR_WS_INCLUDES . 'template_bottom.php');

  require(DIR_WS_INCLUDES . 'application_bottom.php');

?>


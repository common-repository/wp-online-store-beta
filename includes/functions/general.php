<?php

/*

  $Id$



  osCommerce, Open Source E-Commerce Solutions

  http://www.oscommerce.com



  Copyright (c) 2007 osCommerce



  Released under the GNU General Public License

*/



////

// Get the installed version number

  function tep_get_version() {

    static $v;



    if (!isset($v)) {

      $v = trim(implode('', file(DIR_FS_CATALOG . 'includes/version.php')));

    }



    return $v;

  }



////

// Stop from parsing any further PHP code

  function tep_exit() {

   tep_session_close();

   exit();

  }



////

// Redirect to another page or site

  function tep_redirect($url) {

  $_SESSION['cart']=serialize($_SESSION['cart']);

  //  if ( (strstr($url, "\n") != false) || (strstr($url, "\r") != false) ) { 

  //    tep_redirect(tep_href_link(FILENAME_DEFAULT, '', 'NONSSL', false));

 //   }



    if ( (ENABLE_SSL == true) && (getenv('HTTPS') == 'on') ) { // We are loading an SSL page

      if (substr($url, 0, strlen(HTTP_SERVER)) == HTTP_SERVER) { // NONSSL url

        $url = HTTPS_SERVER . substr($url, strlen(HTTP_SERVER)); // Change it to SSL

      }

    }

  header('Location: ' . $url);

    


    tep_exit();

  }



////

// Parse the data used in the html tags to ensure the tags will not break

  function tep_parse_input_field_data($data, $parse) {

    return strtr(trim($data), $parse);

  }



  function tep_output_string($string, $translate = false, $protected = false) {

    if ($protected == true) {

      return htmlspecialchars($string);

    } else {

      if ($translate == false) {

        return tep_parse_input_field_data($string, array('"' => '&quot;'));

      } else {

        return tep_parse_input_field_data($string, $translate);

      }

    }

  }



  function tep_output_string_protected($string) {

    return tep_output_string($string, false, true);

  }



  function tep_sanitize_string($string) {

    $patterns = array ('/ +/','/[<>]/');

    $replace = array (' ', '_');

    return preg_replace($patterns, $replace, trim($string));

  }



////

// Return a random row from a database query

  function tep_random_select($query) {

    $random_product = '';

    $random_query = tep_db_query($query);

    $num_rows = tep_db_num_rows($random_query);

    if ($num_rows > 0) {

      $random_row = tep_rand(0, ($num_rows - 1));

      tep_db_data_seek($random_query, $random_row);

      $random_product = tep_db_fetch_array($random_query);

    }



    return $random_product;

  }



////

// Return a product's name

// TABLES: products

  function tep_get_products_name($product_id, $language = '') {

    global $languages_id;



    if (empty($language)) $language = $languages_id;



    $product_query = tep_db_query("select products_name from " . TABLE_PRODUCTS_DESCRIPTION . " where products_id = '" . (int)$product_id . "' and language_id = '" . (int)$language . "'");

    $product = tep_db_fetch_array($product_query);



    return $product['products_name'];

  }



////

// Return a product's special price (returns nothing if there is no offer)

// TABLES: products

  function tep_get_products_special_price($product_id) {

    $product_query = tep_db_query("select specials_new_products_price from " . TABLE_SPECIALS . " where products_id = '" . (int)$product_id . "' and status = 1");

    $product = tep_db_fetch_array($product_query);



    return $product['specials_new_products_price'];

  }



////

// Return a product's stock

// TABLES: products

  function tep_get_products_stock($products_id) {

    $products_id = tep_get_prid($products_id);

    $stock_query = tep_db_query("select products_quantity from " . TABLE_PRODUCTS . " where products_id = '" . (int)$products_id . "'");

    $stock_values = tep_db_fetch_array($stock_query);



    return $stock_values['products_quantity'];

  }



////

// Check if the required stock is available

// If insufficent stock is available return an out of stock message

  function tep_check_stock($products_id, $products_quantity) {

    $stock_left = tep_get_products_stock($products_id) - $products_quantity;

    $out_of_stock = '';



    if ($stock_left < 0) {

      $out_of_stock = '<span class="markProductOutOfStock">' . STOCK_MARK_PRODUCT_OUT_OF_STOCK . '</span>';

    }



    return $out_of_stock;

  }



////

// Break a word in a string if it is longer than a specified length ($len)

  function tep_break_string($string, $len, $break_char = '-') {

    $l = 0;

    $output = '';

    for ($i=0, $n=strlen($string); $i<$n; $i++) {

      $char = substr($string, $i, 1);

      if ($char != ' ') {

        $l++;

      } else {

        $l = 0;

      }

      if ($l > $len) {

        $l = 1;

        $output .= $break_char;

      }

      $output .= $char;

    }



    return $output;

  }



////

// Return all HTTP GET variables, except those passed as a parameter

  function tep_get_all_get_params($exclude_array = '') {

    global $HTTP_GET_VARS;



    $oscpress_common_get_param=array('page_id','slug');

    if (!is_array($exclude_array)) $exclude_array = array();

	$exclude_array=array_merge($oscpress_common_get_param,$exclude_array);

	 

    $get_url = '';

    //print_r($HTTP_GET_VARS);

    if (is_array($_GET) && (sizeof($_GET) > 0)) {

      reset($_GET);

        

      while (list($key, $value) = each($_GET)) {



        if ( is_string($value) && (strlen($value) > 0) && ($key != tep_session_name()) && ($key != 'error') && (!in_array($key, $exclude_array)) && ($key != 'x') && ($key != 'y') ) {

          $get_url .= $key . '=' . rawurlencode(stripslashes($value)) . '&';

        }

      }

    }



    return $get_url;

  }



////

// Returns an array with countries

// TABLES: countries

  function tep_get_countries($countries_id = '', $with_iso_codes = false) {

    $countries_array = array();

    if (tep_not_null($countries_id)) {

      if ($with_iso_codes == true) {

        $countries = tep_db_query("select countries_name, countries_iso_code_2, countries_iso_code_3 from " . TABLE_COUNTRIES . " where countries_id = '" . (int)$countries_id . "' order by countries_name");

        $countries_values = tep_db_fetch_array($countries);

        $countries_array = array('countries_name' => $countries_values['countries_name'],

                                 'countries_iso_code_2' => $countries_values['countries_iso_code_2'],

                                 'countries_iso_code_3' => $countries_values['countries_iso_code_3']);

      } else {

        $countries = tep_db_query("select countries_name from " . TABLE_COUNTRIES . " where countries_id = '" . (int)$countries_id . "'");

        $countries_values = tep_db_fetch_array($countries);

        $countries_array = array('countries_name' => $countries_values['countries_name']);

      }

    } else {

      $countries = tep_db_query("select countries_id, countries_name from " . TABLE_COUNTRIES . " order by countries_name");

      while ($countries_values = tep_db_fetch_array($countries)) {

        $countries_array[] = array('countries_id' => $countries_values['countries_id'],

                                   'countries_name' => $countries_values['countries_name']);

      }

    }



    return $countries_array;

  }



////

// Alias function to tep_get_countries, which also returns the countries iso codes

  function tep_get_countries_with_iso_codes($countries_id) {

    return tep_get_countries($countries_id, true);

  }



////

// Generate a path to categories

  function tep_get_path($current_category_id = '') {

    global $cPath_array;



    if (tep_not_null($current_category_id)) {

      $cp_size = sizeof($cPath_array);

      if ($cp_size == 0) {

        $cPath_new = $current_category_id;

      } else {

        $cPath_new = '';

        $last_category_query = tep_db_query("select parent_id from " . TABLE_CATEGORIES . " where categories_id = '" . (int)$cPath_array[($cp_size-1)] . "'");

        $last_category = tep_db_fetch_array($last_category_query);



        $current_category_query = tep_db_query("select parent_id from " . TABLE_CATEGORIES . " where categories_id = '" . (int)$current_category_id . "'");

        $current_category = tep_db_fetch_array($current_category_query);



        if ($last_category['parent_id'] == $current_category['parent_id']) {

          for ($i=0; $i<($cp_size-1); $i++) {

            $cPath_new .= '_' . $cPath_array[$i];

          }

        } else {

          for ($i=0; $i<$cp_size; $i++) {

            $cPath_new .= '_' . $cPath_array[$i];

          }

        }

        $cPath_new .= '_' . $current_category_id;



        if (substr($cPath_new, 0, 1) == '_') {

          $cPath_new = substr($cPath_new, 1);

        }

      }

    } else {

      $cPath_new = implode('_', $cPath_array);

    }



    return 'cPath=' . $cPath_new;

  }



////

// Returns the clients browser

  function tep_browser_detect($component) {

    global $HTTP_USER_AGENT;



    return stristr($HTTP_USER_AGENT, $component);

  }



////

// Alias function to tep_get_countries()

  function tep_get_country_name($country_id) {

    $country_array = tep_get_countries($country_id);



    return $country_array['countries_name'];

  }



////

// Returns the zone (State/Province) name

// TABLES: zones

  function tep_get_zone_name($country_id, $zone_id, $default_zone) {

    $zone_query = tep_db_query("select zone_name from " . TABLE_ZONES . " where zone_country_id = '" . (int)$country_id . "' and zone_id = '" . (int)$zone_id . "'");

    if (tep_db_num_rows($zone_query)) {

      $zone = tep_db_fetch_array($zone_query);

      return $zone['zone_name'];

    } else {

      return $default_zone;

    }

  }



////

// Returns the zone (State/Province) code

// TABLES: zones

  function tep_get_zone_code($country_id, $zone_id, $default_zone) {

    $zone_query = tep_db_query("select zone_code from " . TABLE_ZONES . " where zone_country_id = '" . (int)$country_id . "' and zone_id = '" . (int)$zone_id . "'");

    if (tep_db_num_rows($zone_query)) {

      $zone = tep_db_fetch_array($zone_query);

      return $zone['zone_code'];

    } else {

      return $default_zone;

    }

  }



////

// Wrapper function for round()

  function tep_round($number, $precision) {

    if (strpos($number, '.') && (strlen(substr($number, strpos($number, '.')+1)) > $precision)) {

      $number = substr($number, 0, strpos($number, '.') + 1 + $precision + 1);



      if (substr($number, -1) >= 5) {

        if ($precision > 1) {

          $number = substr($number, 0, -1) + ('0.' . str_repeat(0, $precision-1) . '1');

        } elseif ($precision == 1) {

          $number = substr($number, 0, -1) + 0.1;

        } else {

          $number = substr($number, 0, -1) + 1;

        }

      } else {

        $number = substr($number, 0, -1);

      }

    }



    return $number;

  }



////



// Round up function for non whole numbers by GREG DEETH



// The value for the precision variable determines how many digits after the decimal and rounds the last digit up to the next value



// Precision = 0 -> xx.xxxx = x+



// Precision = 1 -> xx.xxxx = xx.+



// Precision = 2 -> xx.xxxx = xx.x+



  function tep_round_up($number, $precision) {



            $number_whole = '';



            $num_left_dec = 0;



            $num_right_dec = 0;



            $num_digits = strlen($number);



            $number_out = '';



            $i = 0;



            while ($i + 1 <= strlen($number))



            {



                        $current_digit = substr($number, $i, ($i + 1) - $num_digits);



                        if ($current_digit == '.') {



                                    $i = $num_digits + 1;



                                    $num_left_dec = strlen($number_whole);



                                    $num_right_dec = ($num_left_dec + 1) - $num_digits;



                        } else {



                                    $number_whole = $number_whole . $current_digit;



                                    $i = $i + 1;



                        }



            }



            if ($num_digits > 3 && $precision < ($num_digits - $num_left_dec - 1) && $precision >= 0) {



                        $i = $precision;



                        $addable = 1;



                        while ($i > 0) {



                                    $addable = $addable * .1;



                                    $i = $i - 1;



                        }



                        $number_out = substr($number, 0, $num_right_dec + $precision) + $addable;



            } else {



                        $number_out = $number;



            }



            return $number_out;



  }

  

////

// Returns the tax rate for a zone / class

// TABLES: tax_rates, zones_to_geo_zones

  function tep_get_tax_rate($class_id, $country_id = -1, $zone_id = -1) {

    global $customer_zone_id, $customer_country_id;

    static $tax_rates = array();



    if ( ($country_id == -1) && ($zone_id == -1) ) {

      if (!tep_session_is_registered('customer_id')) {

        $country_id = STORE_COUNTRY;

        $zone_id = STORE_ZONE;

      } else {

        $country_id = $customer_country_id;

        $zone_id = $customer_zone_id;

      }

    }



    if (!isset($tax_rates[$class_id][$country_id][$zone_id]['rate'])) {

      $tax_query = tep_db_query("select sum(tax_rate) as tax_rate from " . TABLE_TAX_RATES . " tr left join " . TABLE_ZONES_TO_GEO_ZONES . " za on (tr.tax_zone_id = za.geo_zone_id) left join " . TABLE_GEO_ZONES . " tz on (tz.geo_zone_id = tr.tax_zone_id) where (za.zone_country_id is null or za.zone_country_id = '0' or za.zone_country_id = '" . (int)$country_id . "') and (za.zone_id is null or za.zone_id = '0' or za.zone_id = '" . (int)$zone_id . "') and tr.tax_class_id = '" . (int)$class_id . "' group by tr.tax_priority");

      if (tep_db_num_rows($tax_query)) {

        $tax_multiplier = 1.0;

        while ($tax = tep_db_fetch_array($tax_query)) {

          $tax_multiplier *= 1.0 + ($tax['tax_rate'] / 100);

        }



        $tax_rates[$class_id][$country_id][$zone_id]['rate'] = ($tax_multiplier - 1.0) * 100;

      } else {

        $tax_rates[$class_id][$country_id][$zone_id]['rate'] = 0;

      }

    }



    return $tax_rates[$class_id][$country_id][$zone_id]['rate'];

  }



////

// Return the tax description for a zone / class

// TABLES: tax_rates;

  function tep_get_tax_description($class_id, $country_id, $zone_id) {

    static $tax_rates = array();



    if (!isset($tax_rates[$class_id][$country_id][$zone_id]['description'])) {

      $tax_query = tep_db_query("select tax_description from " . TABLE_TAX_RATES . " tr left join " . TABLE_ZONES_TO_GEO_ZONES . " za on (tr.tax_zone_id = za.geo_zone_id) left join " . TABLE_GEO_ZONES . " tz on (tz.geo_zone_id = tr.tax_zone_id) where (za.zone_country_id is null or za.zone_country_id = '0' or za.zone_country_id = '" . (int)$country_id . "') and (za.zone_id is null or za.zone_id = '0' or za.zone_id = '" . (int)$zone_id . "') and tr.tax_class_id = '" . (int)$class_id . "' order by tr.tax_priority");

      if (tep_db_num_rows($tax_query)) {

        $tax_description = '';

        while ($tax = tep_db_fetch_array($tax_query)) {

          $tax_description .= $tax['tax_description'] . ' + ';

        }

        $tax_description = substr($tax_description, 0, -3);



        $tax_rates[$class_id][$country_id][$zone_id]['description'] = $tax_description;

      } else {

        $tax_rates[$class_id][$country_id][$zone_id]['description'] = TEXT_UNKNOWN_TAX_RATE;

      }

    }



    return $tax_rates[$class_id][$country_id][$zone_id]['description'];

  }



////

// Add tax to a products price

  function tep_add_tax($price, $tax) {

    if ( (DISPLAY_PRICE_WITH_TAX == 'true') && ($tax > 0) ) {

      return $price + tep_calculate_tax($price, $tax);

    } else {

      return $price;

    }

  }



// Calculates Tax rounding the result

  function tep_calculate_tax($price, $tax) {

    return $price * $tax / 100;

  }



////

// Return the number of products in a category

// TABLES: products, products_to_categories, categories

  function tep_count_products_in_category($category_id, $include_inactive = false) {

    $products_count = 0;

    if ($include_inactive == true) {

      $products_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p.products_id = p2c.products_id and p2c.categories_id = '" . (int)$category_id . "'");

    } else {

      $products_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p.products_id = p2c.products_id and p.products_status = '1' and p2c.categories_id = '" . (int)$category_id . "'");

    }

    $products = tep_db_fetch_array($products_query);

    $products_count += $products['total'];



    $child_categories_query = tep_db_query("select categories_id from " . TABLE_CATEGORIES . " where parent_id = '" . (int)$category_id . "'");

    if (tep_db_num_rows($child_categories_query)) {

      while ($child_categories = tep_db_fetch_array($child_categories_query)) {

        $products_count += tep_count_products_in_category($child_categories['categories_id'], $include_inactive);

      }

    }



    return $products_count;

  }



////

// Return true if the category has subcategories

// TABLES: categories

  function tep_has_category_subcategories($category_id) {

    $child_category_query = tep_db_query("select count(*) as count from " . TABLE_CATEGORIES . " where parent_id = '" . (int)$category_id . "'");

    $child_category = tep_db_fetch_array($child_category_query);



    if ($child_category['count'] > 0) {

      return true;

    } else {

      return false;

    }

  }



////

// Returns the address_format_id for the given country

// TABLES: countries;

  function tep_get_address_format_id($country_id) {

    $address_format_query = tep_db_query("select address_format_id as format_id from " . TABLE_COUNTRIES . " where countries_id = '" . (int)$country_id . "'");

    if (tep_db_num_rows($address_format_query)) {

      $address_format = tep_db_fetch_array($address_format_query);

      return $address_format['format_id'];

    } else {

      return '1';

    }

  }



////

// Return a formatted address

// TABLES: address_format

  function tep_address_format($address_format_id, $address, $html, $boln, $eoln) {

    $address_format_query = tep_db_query("select address_format as format from " . TABLE_ADDRESS_FORMAT . " where address_format_id = '" . (int)$address_format_id . "'");

    $address_format = tep_db_fetch_array($address_format_query);



    $company = tep_output_string_protected($address['company']);

    if (isset($address['firstname']) && tep_not_null($address['firstname'])) {

      $firstname = tep_output_string_protected($address['firstname']);

      $lastname = tep_output_string_protected($address['lastname']);

    } elseif (isset($address['name']) && tep_not_null($address['name'])) {

      $firstname = tep_output_string_protected($address['name']);

      $lastname = '';

    } else {

      $firstname = '';

      $lastname = '';

    }

    $street = tep_output_string_protected($address['street_address']);

    $suburb = tep_output_string_protected($address['suburb']);

    $city = tep_output_string_protected($address['city']);

    $state = tep_output_string_protected($address['state']);

    if (isset($address['country_id']) && tep_not_null($address['country_id'])) {

      $country = tep_get_country_name($address['country_id']);



      if (isset($address['zone_id']) && tep_not_null($address['zone_id'])) {

        $state = tep_get_zone_code($address['country_id'], $address['zone_id'], $state);

      }

    } elseif (isset($address['country']) && tep_not_null($address['country'])) {

      $country = tep_output_string_protected($address['country']['title']);

    } else {

      $country = '';

    }

    $postcode = tep_output_string_protected($address['postcode']);

    $zip = $postcode;



    if ($html) {

// HTML Mode

      $HR = '<hr />';

      $hr = '<hr />';

      if ( ($boln == '') && ($eoln == "\n") ) { // Values not specified, use rational defaults

        $CR = '<br />';

        $cr = '<br />';

        $eoln = $cr;

      } else { // Use values supplied

        $CR = $eoln . $boln;

        $cr = $CR;

      }

    } else {

// Text Mode

      $CR = $eoln;

      $cr = $CR;

      $HR = '----------------------------------------';

      $hr = '----------------------------------------';

    }



    $statecomma = '';

    $streets = $street;

    if ($suburb != '') $streets = $street . $cr . $suburb;

    if ($state != '') $statecomma = $state . ', ';



    $fmt = $address_format['format'];

    eval("\$address = \"$fmt\";");



    if ( (ACCOUNT_COMPANY == 'true') && (tep_not_null($company)) ) {

      $address = $company . $cr . $address;

    }



    return $address;

  }



////

// Return a formatted address

// TABLES: customers, address_book

  function tep_address_label($customers_id, $address_id = 1, $html = false, $boln = '', $eoln = "\n") {

    if (is_array($address_id) && !empty($address_id)) {

      return tep_address_format($address_id['address_format_id'], $address_id, $html, $boln, $eoln);

    }



    $address_query = tep_db_query("select entry_firstname as firstname, entry_lastname as lastname, entry_company as company, entry_street_address as street_address, entry_suburb as suburb, entry_city as city, entry_postcode as postcode, entry_state as state, entry_zone_id as zone_id, entry_country_id as country_id from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int)$customers_id . "' and address_book_id = '" . (int)$address_id . "'");

    $address = tep_db_fetch_array($address_query);



    $format_id = tep_get_address_format_id($address['country_id']);



    return tep_address_format($format_id, $address, $html, $boln, $eoln);

  }



  function tep_row_number_format($number) {

    if ( ($number < 10) && (substr($number, 0, 1) != '0') ) $number = '0' . $number;



    return $number;

  }



  function tep_get_categories($categories_array = '', $parent_id = '0', $indent = '') {

    global $languages_id;



    if (!is_array($categories_array)) $categories_array = array();



    $categories_query = tep_db_query("select c.categories_id, cd.categories_name from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where parent_id = '" . (int)$parent_id . "' and c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "' order by sort_order, cd.categories_name");

    while ($categories = tep_db_fetch_array($categories_query)) {

      $categories_array[] = array('id' => $categories['categories_id'],

                                  'text' => $indent . $categories['categories_name']);



      if ($categories['categories_id'] != $parent_id) {

        $categories_array = tep_get_categories($categories_array, $categories['categories_id'], $indent . '&nbsp;&nbsp;');

      }

    }



    return $categories_array;

  }



  function tep_get_manufacturers($manufacturers_array = '') {

    if (!is_array($manufacturers_array)) $manufacturers_array = array();



    $manufacturers_query = tep_db_query("select manufacturers_id, manufacturers_name from " . TABLE_MANUFACTURERS . " order by manufacturers_name");

    while ($manufacturers = tep_db_fetch_array($manufacturers_query)) {

      $manufacturers_array[] = array('id' => $manufacturers['manufacturers_id'], 'text' => $manufacturers['manufacturers_name']);

    }



    return $manufacturers_array;

  }



////

// Return all subcategory IDs

// TABLES: categories

  function tep_get_subcategories(&$subcategories_array, $parent_id = 0) {

    $subcategories_query = tep_db_query("select categories_id from " . TABLE_CATEGORIES . " where parent_id = '" . (int)$parent_id . "'");

    while ($subcategories = tep_db_fetch_array($subcategories_query)) {

      $subcategories_array[sizeof($subcategories_array)] = $subcategories['categories_id'];

      if ($subcategories['categories_id'] != $parent_id) {

        tep_get_subcategories($subcategories_array, $subcategories['categories_id']);

      }

    }

  }



// Output a raw date string in the selected locale date format

// $raw_date needs to be in this format: YYYY-MM-DD HH:MM:SS

  function tep_date_long($raw_date) {

    if ( ($raw_date == '0000-00-00 00:00:00') || ($raw_date == '') ) return false;



    $year = (int)substr($raw_date, 0, 4);

    $month = (int)substr($raw_date, 5, 2);

    $day = (int)substr($raw_date, 8, 2);

    $hour = (int)substr($raw_date, 11, 2);

    $minute = (int)substr($raw_date, 14, 2);

    $second = (int)substr($raw_date, 17, 2);



    return strftime(DATE_FORMAT_LONG, mktime($hour,$minute,$second,$month,$day,$year));

  }



////

// Output a raw date string in the selected locale date format

// $raw_date needs to be in this format: YYYY-MM-DD HH:MM:SS

// NOTE: Includes a workaround for dates before 01/01/1970 that fail on windows servers

  function tep_date_short($raw_date) {

    if ( ($raw_date == '0000-00-00 00:00:00') || empty($raw_date) ) return false;



    $year = substr($raw_date, 0, 4);

    $month = (int)substr($raw_date, 5, 2);

    $day = (int)substr($raw_date, 8, 2);

    $hour = (int)substr($raw_date, 11, 2);

    $minute = (int)substr($raw_date, 14, 2);

    $second = (int)substr($raw_date, 17, 2);



    if (@date('Y', mktime($hour, $minute, $second, $month, $day, $year)) == $year) {

      return date(DATE_FORMAT, mktime($hour, $minute, $second, $month, $day, $year));

    } else {

      return preg_replace('/2037$/', $year, date(DATE_FORMAT, mktime($hour, $minute, $second, $month, $day, 2037)));

    }

  }



////

// Parse search string into indivual objects

  function tep_parse_search_string($search_str = '', &$objects) {

    $search_str = trim(strtolower($search_str));



// Break up $search_str on whitespace; quoted string will be reconstructed later

    $pieces = preg_split('/[[:space:]]+/', $search_str);

    $objects = array();

    $tmpstring = '';

    $flag = '';



    for ($k=0; $k<count($pieces); $k++) {

      while (substr($pieces[$k], 0, 1) == '(') {

        $objects[] = '(';

        if (strlen($pieces[$k]) > 1) {

          $pieces[$k] = substr($pieces[$k], 1);

        } else {

          $pieces[$k] = '';

        }

      }



      $post_objects = array();



      while (substr($pieces[$k], -1) == ')')  {

        $post_objects[] = ')';

        if (strlen($pieces[$k]) > 1) {

          $pieces[$k] = substr($pieces[$k], 0, -1);

        } else {

          $pieces[$k] = '';

        }

      }



// Check individual words



      if ( (substr($pieces[$k], -1) != '"') && (substr($pieces[$k], 0, 1) != '"') ) {

        $objects[] = trim($pieces[$k]);



        for ($j=0; $j<count($post_objects); $j++) {

          $objects[] = $post_objects[$j];

        }

      } else {

/* This means that the $piece is either the beginning or the end of a string.

   So, we'll slurp up the $pieces and stick them together until we get to the

   end of the string or run out of pieces.

*/



// Add this word to the $tmpstring, starting the $tmpstring

        $tmpstring = trim(preg_replace('/"/', ' ', $pieces[$k]));



// Check for one possible exception to the rule. That there is a single quoted word.

        if (substr($pieces[$k], -1 ) == '"') {

// Turn the flag off for future iterations

          $flag = 'off';



          $objects[] = trim(preg_replace('/"/', ' ', $pieces[$k]));



          for ($j=0; $j<count($post_objects); $j++) {

            $objects[] = $post_objects[$j];

          }



          unset($tmpstring);



// Stop looking for the end of the string and move onto the next word.

          continue;

        }



// Otherwise, turn on the flag to indicate no quotes have been found attached to this word in the string.

        $flag = 'on';



// Move on to the next word

        $k++;



// Keep reading until the end of the string as long as the $flag is on



        while ( ($flag == 'on') && ($k < count($pieces)) ) {

          while (substr($pieces[$k], -1) == ')') {

            $post_objects[] = ')';

            if (strlen($pieces[$k]) > 1) {

              $pieces[$k] = substr($pieces[$k], 0, -1);

            } else {

              $pieces[$k] = '';

            }

          }



// If the word doesn't end in double quotes, append it to the $tmpstring.

          if (substr($pieces[$k], -1) != '"') {

// Tack this word onto the current string entity

            $tmpstring .= ' ' . $pieces[$k];



// Move on to the next word

            $k++;

            continue;

          } else {

/* If the $piece ends in double quotes, strip the double quotes, tack the

   $piece onto the tail of the string, push the $tmpstring onto the $haves,

   kill the $tmpstring, turn the $flag "off", and return.

*/

            $tmpstring .= ' ' . trim(preg_replace('/"/', ' ', $pieces[$k]));



// Push the $tmpstring onto the array of stuff to search for

            $objects[] = trim($tmpstring);



            for ($j=0; $j<count($post_objects); $j++) {

              $objects[] = $post_objects[$j];

            }



            unset($tmpstring);



// Turn off the flag to exit the loop

            $flag = 'off';

          }

        }

      }

    }



// add default logical operators if needed

    $temp = array();

    for($i=0; $i<(count($objects)-1); $i++) {

      $temp[] = $objects[$i];

      if ( ($objects[$i] != 'and') &&

           ($objects[$i] != 'or') &&

           ($objects[$i] != '(') &&

           ($objects[$i+1] != 'and') &&

           ($objects[$i+1] != 'or') &&

           ($objects[$i+1] != ')') ) {

        $temp[] = ADVANCED_SEARCH_DEFAULT_OPERATOR;

      }

    }

    $temp[] = $objects[$i];

    $objects = $temp;



    $keyword_count = 0;

    $operator_count = 0;

    $balance = 0;

    for($i=0; $i<count($objects); $i++) {

      if ($objects[$i] == '(') $balance --;

      if ($objects[$i] == ')') $balance ++;

      if ( ($objects[$i] == 'and') || ($objects[$i] == 'or') ) {

        $operator_count ++;

      } elseif ( ($objects[$i]) && ($objects[$i] != '(') && ($objects[$i] != ')') ) {

        $keyword_count ++;

      }

    }



    if ( ($operator_count < $keyword_count) && ($balance == 0) ) {

      return true;

    } else {

      return false;

    }

  }



////

// Check date

  function tep_checkdate($date_to_check, $format_string, &$date_array) {

    $separator_idx = -1;



    $separators = array('-', ' ', '/', '.');

    $month_abbr = array('jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec');

    $no_of_days = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);



    $format_string = strtolower($format_string);



    if (strlen($date_to_check) != strlen($format_string)) {

      return false;

    }



    $size = sizeof($separators);

    for ($i=0; $i<$size; $i++) {

      $pos_separator = strpos($date_to_check, $separators[$i]);

      if ($pos_separator != false) {

        $date_separator_idx = $i;

        break;

      }

    }



    for ($i=0; $i<$size; $i++) {

      $pos_separator = strpos($format_string, $separators[$i]);

      if ($pos_separator != false) {

        $format_separator_idx = $i;

        break;

      }

    }



    if ($date_separator_idx != $format_separator_idx) {

      return false;

    }



    if ($date_separator_idx != -1) {

      $format_string_array = explode( $separators[$date_separator_idx], $format_string );

      if (sizeof($format_string_array) != 3) {

        return false;

      }



      $date_to_check_array = explode( $separators[$date_separator_idx], $date_to_check );

      if (sizeof($date_to_check_array) != 3) {

        return false;

      }



      $size = sizeof($format_string_array);

      for ($i=0; $i<$size; $i++) {

        if ($format_string_array[$i] == 'mm' || $format_string_array[$i] == 'mmm') $month = $date_to_check_array[$i];

        if ($format_string_array[$i] == 'dd') $day = $date_to_check_array[$i];

        if ( ($format_string_array[$i] == 'yyyy') || ($format_string_array[$i] == 'aaaa') ) $year = $date_to_check_array[$i];

      }

    } else {

      if (strlen($format_string) == 8 || strlen($format_string) == 9) {

        $pos_month = strpos($format_string, 'mmm');

        if ($pos_month != false) {

          $month = substr( $date_to_check, $pos_month, 3 );

          $size = sizeof($month_abbr);

          for ($i=0; $i<$size; $i++) {

            if ($month == $month_abbr[$i]) {

              $month = $i;

              break;

            }

          }

        } else {

          $month = substr($date_to_check, strpos($format_string, 'mm'), 2);

        }

      } else {

        return false;

      }



      $day = substr($date_to_check, strpos($format_string, 'dd'), 2);

      $year = substr($date_to_check, strpos($format_string, 'yyyy'), 4);

    }



    if (strlen($year) != 4) {

      return false;

    }



    if (!settype($year, 'integer') || !settype($month, 'integer') || !settype($day, 'integer')) {

      return false;

    }



    if ($month > 12 || $month < 1) {

      return false;

    }



    if ($day < 1) {

      return false;

    }



    if (tep_is_leap_year($year)) {

      $no_of_days[1] = 29;

    }



    if ($day > $no_of_days[$month - 1]) {

      return false;

    }



    $date_array = array($year, $month, $day);



    return true;

  }



////

// Check if year is a leap year

  function tep_is_leap_year($year) {

    if ($year % 100 == 0) {

      if ($year % 400 == 0) return true;

    } else {

      if (($year % 4) == 0) return true;

    }



    return false;

  }



////

// Return table heading with sorting capabilities

  function tep_create_sort_heading($sortby, $colnum, $heading) {

    global $PHP_SELF;



    $sort_prefix = '';

    $sort_suffix = '';



    if ($sortby) {

      $sort_prefix = '<a href="' . tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('page', 'info', 'sort')) . 'page=1&sort=' . $colnum . ($sortby == $colnum . 'a' ? 'd' : 'a')) . '" title="' . tep_output_string(TEXT_SORT_PRODUCTS . ($sortby == $colnum . 'd' || substr($sortby, 0, 1) != $colnum ? TEXT_ASCENDINGLY : TEXT_DESCENDINGLY) . TEXT_BY . $heading) . '" class="productListing-heading">' ;

      //$sort_suffix = (substr($sortby, 0, 1) == $colnum ? (substr($sortby, 1, 1) == 'a' ? '+' : '-') : '') . '</a>';
	  // BOF Product Sort
      $sort_suffix = (substr($sortby, 0, 1) == $colnum ? (substr($sortby, 1, 1) == 'a' ? '' : '') : '') . '</a>';
// or use images : comment out line above and uncomment next [skylla]
//	  $sort_suffix = (substr($sortby, 0, 1) == $colnum ? (substr($sortby, 1, 1) == 'a' ? tep_image(DIR_WS_IMAGES . "triangle_down.gif", "ascending") : tep_image(DIR_WS_IMAGES . "triangle_up.gif", "descending") ) : '');
// EOF Product Sort  

    }



    return $sort_prefix . $heading . $sort_suffix;

  }



////

// Recursively go through the categories and retreive all parent categories IDs

// TABLES: categories

  function tep_get_parent_categories(&$categories, $categories_id) {

    $parent_categories_query = tep_db_query("select parent_id from " . TABLE_CATEGORIES . " where categories_id = '" . (int)$categories_id . "'");

    while ($parent_categories = tep_db_fetch_array($parent_categories_query)) {

      if ($parent_categories['parent_id'] == 0) return true;

      $categories[sizeof($categories)] = $parent_categories['parent_id'];

      if ($parent_categories['parent_id'] != $categories_id) {

        tep_get_parent_categories($categories, $parent_categories['parent_id']);

      }

    }

  }



////

// Construct a category path to the product

// TABLES: products_to_categories

  function tep_get_product_path($products_id) {

    $cPath = '';



    $category_query = tep_db_query("select p2c.categories_id from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p.products_id = '" . (int)$products_id . "' and p.products_status = '1' and p.products_id = p2c.products_id limit 1");

    if (tep_db_num_rows($category_query)) {

      $category = tep_db_fetch_array($category_query);



      $categories = array();

      tep_get_parent_categories($categories, $category['categories_id']);



      $categories = array_reverse($categories);



      $cPath = implode('_', $categories);



      if (tep_not_null($cPath)) $cPath .= '_';

      $cPath .= $category['categories_id'];

    }



    return $cPath;

  }


////
// Return a product ID with attributes
  function tep_get_uprid($prid, $params) {
//BOF - Zappo - Option Types v2 - Adding Attributes
    $uprid = $prid;
    if ( (is_array($params)) && (!strstr($prid, '{')) ) {
      while (list($option, $value) = each($params)) {
        $uprid = $uprid . '{' . $option . '}' . htmlspecialchars(stripslashes(trim($value)), ENT_QUOTES);
      }
    } else {
      $uprid = htmlspecialchars(stripslashes($uprid), ENT_QUOTES);
    }
    if ( (is_array($params)) && (!strstr($prid, '{')) ) {
      while (list($option, $value) = each($params)) {
        $uprid = $uprid . '{' . $option . '}' . $value;
      }
    }
//EOF - Zappo - Option Types v2 - Adding Attributes

    return $uprid;
  }

////
// Return a product ID from a product ID with attributes
  function tep_get_prid($uprid) {
    $pieces = explode('{', $uprid);

//BOF - Zappo - Option Types v2 - Always return $pieces
      return $pieces[0];
//EOF - Zappo - Option Types v2 - Always return $pieces
  }

////

// Return a customer greeting

  function tep_customer_greeting() {

    global $customer_id, $customer_first_name;



    if (tep_session_is_registered('customer_first_name') && tep_session_is_registered('customer_id')) {

      $greeting_string = sprintf(TEXT_GREETING_PERSONAL, tep_output_string_protected($customer_first_name), tep_href_link(FILENAME_PRODUCTS_NEW));

    } else {

      $greeting_string = sprintf(TEXT_GREETING_GUEST, tep_href_link(FILENAME_LOGIN, '', 'SSL'), tep_href_link(FILENAME_CREATE_ACCOUNT, '', 'SSL'));

    }



    return $greeting_string;

  }



////

//! Send email (text/html) using MIME

// This is the central mail function. The SMTP Server should be configured

// correct in php.ini

// Parameters:

// $to_name           The name of the recipient, e.g. "Jan Wildeboer"

// $to_email_address  The eMail address of the recipient,

//                    e.g. jan.wildeboer@gmx.de

// $email_subject     The subject of the eMail

// $email_text        The text of the eMail, may contain HTML entities

// $from_email_name   The name of the sender, e.g. Shop Administration

// $from_email_adress The eMail address of the sender,

//                    e.g. info@mytepshop.com



  function tep_mail($to_name, $to_email_address, $email_subject, $email_text, $from_email_name, $from_email_address) {

    if (SEND_EMAILS != 'true') return false;



    // Instantiate a new mail object

    $message = new email(array('X-Mailer: osCommerce'));



    // Build the text version

    $text = strip_tags($email_text);

    if (EMAIL_USE_HTML == 'true') {

      $message->add_html($email_text, $text);

    } else {

      $message->add_text($text);

    }



    // Send message

    $message->build_message();

    $message->send($to_name, $to_email_address, $from_email_name, $from_email_address, $email_subject);

  }



////

// Check if product has attributes

  function tep_has_product_attributes($products_id) {

    $attributes_query = tep_db_query("select count(*) as count from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_id = '" . (int)$products_id . "'");

    $attributes = tep_db_fetch_array($attributes_query);



    if ($attributes['count'] > 0) {

      return true;

    } else {

      return false;

    }

  }



////

// Get the number of times a word/character is present in a string

  function tep_word_count($string, $needle) {

    $temp_array = preg_split('/' . $needle . '/', $string);



    return sizeof($temp_array);

  }



  function tep_count_modules($modules = '') {

    $count = 0;



    if (empty($modules)) return $count;



    $modules_array = explode(';', $modules);



    for ($i=0, $n=sizeof($modules_array); $i<$n; $i++) {

      $class = substr($modules_array[$i], 0, strrpos($modules_array[$i], '.'));



      if (isset($GLOBALS[$class]) && is_object($GLOBALS[$class])) {

        if ($GLOBALS[$class]->enabled) {

          $count++;

        }

      }

    }



    return $count;

  }



  function tep_count_payment_modules() {

    return tep_count_modules(MODULE_PAYMENT_INSTALLED);

  }



  function tep_count_shipping_modules() {

    return tep_count_modules(MODULE_SHIPPING_INSTALLED);

  }



  function tep_create_random_value($length, $type = 'mixed') {

    if ( ($type != 'mixed') && ($type != 'chars') && ($type != 'digits')) return false;



    $rand_value = '';

    while (strlen($rand_value) < $length) {

      if ($type == 'digits') {

        $char = tep_rand(0,9);

      } else {

        $char = chr(tep_rand(0,255));

      }

      if ($type == 'mixed') {

        if (preg_match('/^[a-z0-9]$/i', $char)) $rand_value .= $char;

      } elseif ($type == 'chars') {

        if (preg_match('/^[a-z]$/i', $char)) $rand_value .= $char;

      } elseif ($type == 'digits') {

        if (preg_match('/^[0-9]$/i', $char)) $rand_value .= $char;

      }

    }



    return $rand_value;

  }



  function tep_array_to_string($array, $exclude = '', $equals = '=', $separator = '&') {

    if (!is_array($exclude)) $exclude = array();



    $get_string = '';

    if (sizeof($array) > 0) {

      while (list($key, $value) = each($array)) {

        if ( (!in_array($key, $exclude)) && ($key != 'x') && ($key != 'y') ) {

          $get_string .= $key . $equals . $value . $separator;

        }

      }

      $remove_chars = strlen($separator);

      $get_string = substr($get_string, 0, -$remove_chars);

    }



    return $get_string;

  }



  function tep_not_null($value) {

    if (is_array($value)) {

      if (sizeof($value) > 0) {

        return true;

      } else {

        return false;

      }

    } else {

      if (($value != '') && (strtolower($value) != 'null') && (strlen(trim($value)) > 0)) {

        return true;

      } else {

        return false;

      }

    }

  }



////

// Output the tax percentage with optional padded decimals

  function tep_display_tax_value($value, $padding = TAX_DECIMAL_PLACES) {

    if (strpos($value, '.')) {

      $loop = true;

      while ($loop) {

        if (substr($value, -1) == '0') {

          $value = substr($value, 0, -1);

        } else {

          $loop = false;

          if (substr($value, -1) == '.') {

            $value = substr($value, 0, -1);

          }

        }

      }

    }



    if ($padding > 0) {

      if ($decimal_pos = strpos($value, '.')) {

        $decimals = strlen(substr($value, ($decimal_pos+1)));

        for ($i=$decimals; $i<$padding; $i++) {

          $value .= '0';

        }

      } else {

        $value .= '.';

        for ($i=0; $i<$padding; $i++) {

          $value .= '0';

        }

      }

    }



    return $value;

  }



////

// Checks to see if the currency code exists as a currency

// TABLES: currencies

  function tep_currency_exists($code) {

    $code = tep_db_prepare_input($code);



    $currency_query = tep_db_query("select code from " . TABLE_CURRENCIES . " where code = '" . tep_db_input($code) . "' limit 1");

    if (tep_db_num_rows($currency_query)) {

      $currency = tep_db_fetch_array($currency_query);

      return $currency['code'];

    } else {

      return false;

    }

  }



  function tep_string_to_int($string) {

    return (int)$string;

  }



////

// Parse and secure the cPath parameter values

  function tep_parse_category_path($cPath) {

// make sure the category IDs are integers

    $cPath_array = array_map('tep_string_to_int', explode('_', $cPath));



// make sure no duplicate category IDs exist which could lock the server in a loop

    $tmp_array = array();

    $n = sizeof($cPath_array);

    for ($i=0; $i<$n; $i++) {

      if (!in_array($cPath_array[$i], $tmp_array)) {

        $tmp_array[] = $cPath_array[$i];

      }

    }



    return $tmp_array;

  }



////

// Return a random value

  function tep_rand($min = null, $max = null) {

    static $seeded;



    if (!isset($seeded)) {

      mt_srand((double)microtime()*1000000);

      $seeded = true;

    }



    if (isset($min) && isset($max)) {

      if ($min >= $max) {

        return $min;

      } else {

        return mt_rand($min, $max);

      }

    } else {

      return mt_rand();

    }

  }



  function tep_setcookie($name, $value = '', $expire = 0, $path = '/', $domain = '', $secure = 0) {

    setcookie($name, $value, $expire, $path, (tep_not_null($domain) ? $domain : ''), $secure);

  }



  function tep_validate_ip_address($ip_address) {

    if (function_exists('filter_var') && defined('FILTER_VALIDATE_IP')) {

      return filter_var($ip_address, FILTER_VALIDATE_IP, array('flags' => FILTER_FLAG_IPV4));

    }



    if (preg_match('/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/', $ip_address)) {

      $parts = explode('.', $ip_address);



      foreach ($parts as $ip_parts) {

        if ( (intval($ip_parts) > 255) || (intval($ip_parts) < 0) ) {

          return false; // number is not within 0-255

        }

      }



      return true;

    }



    return false;

  }



  function tep_get_ip_address() {

    global $HTTP_SERVER_VARS;



    $ip_address = null;

    $ip_addresses = array();



    if (isset($HTTP_SERVER_VARS['HTTP_X_FORWARDED_FOR']) && !empty($HTTP_SERVER_VARS['HTTP_X_FORWARDED_FOR'])) {

      foreach ( array_reverse(explode(',', $HTTP_SERVER_VARS['HTTP_X_FORWARDED_FOR'])) as $x_ip ) {

        $x_ip = trim($x_ip);



        if (tep_validate_ip_address($x_ip)) {

          $ip_addresses[] = $x_ip;

        }

      }

    }



    if (isset($HTTP_SERVER_VARS['HTTP_CLIENT_IP']) && !empty($HTTP_SERVER_VARS['HTTP_CLIENT_IP'])) {

      $ip_addresses[] = $HTTP_SERVER_VARS['HTTP_CLIENT_IP'];

    }



    if (isset($HTTP_SERVER_VARS['HTTP_X_CLUSTER_CLIENT_IP']) && !empty($HTTP_SERVER_VARS['HTTP_X_CLUSTER_CLIENT_IP'])) {

      $ip_addresses[] = $HTTP_SERVER_VARS['HTTP_X_CLUSTER_CLIENT_IP'];

    }



    if (isset($HTTP_SERVER_VARS['HTTP_PROXY_USER']) && !empty($HTTP_SERVER_VARS['HTTP_PROXY_USER'])) {

      $ip_addresses[] = $HTTP_SERVER_VARS['HTTP_PROXY_USER'];

    }



    $ip_addresses[] = $HTTP_SERVER_VARS['REMOTE_ADDR'];



    foreach ( $ip_addresses as $ip ) {

      if (!empty($ip) && tep_validate_ip_address($ip)) {

        $ip_address = $ip;

        break;

      }

    }



    return $ip_address;

  }



  function tep_count_customer_orders($id = '', $check_session = true) {

    global $customer_id, $languages_id;



    if (is_numeric($id) == false) {

      if (tep_session_is_registered('customer_id')) {

        $id = $customer_id;

      } else {

        return 0;

      }

    }



    if ($check_session == true) {

      if ( (tep_session_is_registered('customer_id') == false) || ($id != $customer_id) ) {

        return 0;

      }

    }



    $orders_check_query = tep_db_query("select count(*) as total from " . TABLE_ORDERS . " o, " . TABLE_ORDERS_STATUS . " s where o.customers_id = '" . (int)$id . "' and o.orders_status = s.orders_status_id and s.language_id = '" . (int)$languages_id . "' and s.public_flag = '1'");

    $orders_check = tep_db_fetch_array($orders_check_query);



    return $orders_check['total'];

  }



  function tep_count_customer_address_book_entries($id = '', $check_session = true) {

    global $customer_id;



    if (is_numeric($id) == false) {

      if (tep_session_is_registered('customer_id')) {

        $id = $customer_id;

      } else {

        return 0;

      }

    }



    if ($check_session == true) {

      if ( (tep_session_is_registered('customer_id') == false) || ($id != $customer_id) ) {

        return 0;

      }

    }



    $addresses_query = tep_db_query("select count(*) as total from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int)$id . "'");

    $addresses = tep_db_fetch_array($addresses_query);



    return $addresses['total'];

  }



// nl2br() prior PHP 4.2.0 did not convert linefeeds on all OSs (it only converted \n)

  function tep_convert_linefeeds($from, $to, $string) {

    if ((PHP_VERSION < "4.0.5") && is_array($from)) {

      return preg_replace('/(' . implode('|', $from) . ')/', $to, $string);

    } else {

      return str_replace($from, $to, $string);

    }

  }

  
//for Extra Product Fields
  function tep_get_extra_field_list_value($value_id, $show_chain = false, $display_type = 0) {
    $sql = tep_db_query("select epf_value, value_image, parent_id from " . TABLE_EPF_VALUES . " where value_id = " . (int)$value_id);
    $value = tep_db_fetch_array($sql);
    $display = $value['epf_value'];
    if (tep_not_null($value['value_image'])) {
      if ($display_type == 2) {
      	$browser = (isset($_SERVER['HTTP_USER_AGENT']) ? strtolower($_SERVER['HTTP_USER_AGENT']) : '');
      	$pos = strpos($browser, 'msie');
      	if ($pos !== false) { // using Internet Explorer requires different display type for inline tables
       	  $vpos = strpos($browser, ';', $pos);
      	  $version = substr($browser, $pos + 5, $vpos - $pos - 5);
      	  if ($version < 9) {
      	    $tt = 'inline';
      	  } else {
      	    $tt = 'inline-table';
      	  }
       	} else {
      	  $tt = 'inline-table';
        }
        $display = '<table style="display: ' . $tt . '; vertical-align: middle; text-align: center"><tr><td>' . tep_image(DIR_WS_IMAGES . 'epf/' . $value['value_image'], $value['epf_value']) . '<br />' . $value['epf_value'] . '</td></tr></table>';
      } elseif ($display_type == 1) {
        $display = tep_image(DIR_WS_IMAGES . 'epf/' . $value['value_image'], $value['epf_value']);
      }
    }
    if ($show_chain && ($value['parent_id'] > 0)) {
      return tep_get_extra_field_list_value($value['parent_id'], true, $display_type) . ' | ' . $display;
    } else {
      return $display;
    }
  }
  
  function tep_list_epf_children($parent_id) {
    $sql = tep_db_query("select value_id from " . TABLE_EPF_VALUES . " where parent_id = " . (int)$parent_id);
    $list = '';
    while ($i = tep_db_fetch_array($sql)) {
      $list .= ',' . $i['value_id'] . tep_list_epf_children($i['value_id']);
    }
    return $list;
  }
  
  function tep_build_epf_pulldown($epf_id, $languages_id, $value_array = '', $parent_id = 0, $indent = '') {
    if (!is_array($value_array)) $value_array = array();
    $sql = tep_db_query("select epf_value, value_id from " . TABLE_EPF_VALUES . " where epf_id = " . (int)$epf_id . " and languages_id = " . (int)$languages_id . " and parent_id = " . (int)$parent_id . " order by sort_order, epf_value");
    while ($v = tep_db_fetch_array($sql)) {
      $value_array[] = array('id' => $v['value_id'], 'text' => $indent . $v['epf_value']);
      $value_array = tep_build_epf_pulldown($epf_id, $languages_id, $value_array, $v['value_id'], $indent . '&middot;');
    }
    return $value_array;
  }
  
// ends here

// OTF contrib begins
// Decode string encoded with htmlspecialchars()
  function tep_decode_specialchars($string){
    $string=str_replace('&gt;', '>', $string);
    $string=str_replace('&lt;', '<', $string);
    $string=str_replace('&#039;', "'", $string);
    $string=str_replace('&quot;', "\"", $string);
    $string=str_replace('&amp;', '&', $string);

    return $string;
  }
// OTF contrib ends

// BOF Minimum Category Tree Quantity
function get_cat_name_osc ($cat_id) {
  global $languages_id;
  $cat_query = tep_db_query("SELECT categories_name FROM " . TABLE_CATEGORIES_DESCRIPTION . " WHERE categories_id = '".$cat_id."' and language_id = '" . (int)$languages_id . "'");
  $cat = tep_db_fetch_array($cat_query);
  return $cat['categories_name'];
}
function cat_min_order_ok () {
	global $cart, $messageStack,$currencies;
	$result = true;
	$cat_val = array();
	$s_query_cat_mins="SELECT categories_id, categories_min_quan FROM " . TABLE_CATEGORIES ." WHERE categories_min_quan>1";
	$query_cat_mins=tep_db_query($s_query_cat_mins);
	while ($cat_mins = tep_db_fetch_array($query_cat_mins)) {
		$cat_val[$cat_mins['categories_id']] = array('min' => $cat_mins['categories_min_quan'], 'cart' => 0);
 	}
	$products = $cart->get_products();
	for ($p=0; $p<sizeof($products); $p++) {
		$t_prid = tep_get_prid($products[$p]['id']);
 		$cat_query = tep_db_query("select categories_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . $t_prid . "'");
 		while ($cat_result = tep_db_fetch_array($cat_query)) { // prod can be in several categories
			$cur_cat_p=$cat_result['categories_id'];
			$cats=get_cat_ancestors($cur_cat_p);
			foreach ($cats as $cur_cat) {  
				if (array_key_exists($cur_cat,$cat_val)) { // check for all the parents
					//$cat_val[$cur_cat]['cart']=	$cat_val[$cur_cat]['cart']+$cart->get_quantity($t_prid);
					//$cat_val[$cur_cat]['cart']=	$cat_val[$cur_cat]['cart']+$products[$p]['quantity'];
					$cat_val[$cur_cat]['cart']=	$cat_val[$cur_cat]['cart']+($products[$p]['quantity'] * tep_add_tax($products[$p]['final_price'], $products[$p]['products_tax']));
				}
			}
		}
 	}
	foreach ($cat_val AS $cat_id => $values) {
		if (($values["cart"] > 0) and ($values["cart"] < $values["min"])) {
			$result = false;
			// use message stack for the message in the shopping cart - make sure you add the message display code there
			$messageStack->add_session('shopping_cart', 'Category "'.get_cat_name_osc($cat_id).'" has a minimum order  of '.$currencies->display_price($values['min']).'. You have '.$currencies->display_price($values['cart']).' of these items in your order');
			//break;
		}
	}
	return $result;
}
function get_cat_ancestors ($current_category_id = '') {
	$cats=array();
      	$last_category_query = tep_db_query("select parent_id from " . TABLE_CATEGORIES . " where categories_id = '" . $current_category_id . "'");
      	if ($last_category = tep_db_fetch_array($last_category_query)) {
		$cats[]=$current_category_id;
       	while ($last_category['parent_id'] != 0) {
			$cats[]=$last_category['parent_id'];
       		$last_category_query = tep_db_query("select parent_id from " . TABLE_CATEGORIES . " where categories_id = '" . $last_category['parent_id'] . "'");
       		$last_category = tep_db_fetch_array($last_category_query);
       	}
	}
    return $cats;
}
// EOF Minimum Category Tree Quantity
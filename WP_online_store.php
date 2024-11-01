<?php
/*
Plugin Name: WP Online Store
Plugin URI: http://WPonlineStore.com/
Description: An incredible eCommerce plugin for WordPress that uses the legendary osCommerce shopping cart system. Add to ANY Worpress theme instantly with one piece of shortcode: [WP_online_store]. Tons of features AND great support.
Author: IMC Media Group
Version: 1.3.1
Author URI: http://WPOnlineStore.com/
*/


register_activation_hook(__FILE__,'install');
register_deactivation_hook( __FILE__, 'uninstall' );
function install()
{
	require_once WP_PLUGIN_DIR . '/'.basename(dirname(__FILE__)).'/functions/database.php';
    require_once WP_PLUGIN_DIR . '/'.basename(dirname(__FILE__)).'/functions/general.php';
    $db_error = false;
	
    $sql_file = WP_PLUGIN_DIR . '/'.basename(dirname(__FILE__)).'/oscommerce.sql';
    
    osc_db_connect(DB_HOST, DB_USER, DB_PASSWORD);
    osc_set_time_limit(0);
    osc_db_install(DB_NAME, $sql_file);
	if ($db_error != false) {
		generate_config(true);
		
		
		
    } else {
		
        
    }
    
}

generate_config();


function rcopy($src, $dst) {
  if (is_dir($src)) {
    mkdir($dst);
    $files = scandir($src);
    foreach ($files as $file)
    if ($file != "." && $file != "..") rcopy("$src/$file", "$dst/$file"); 
  }
  else if (file_exists($src)) copy($src, $dst);
}

function rrmdir($dir) {
  if (is_dir($dir)) {
    $files = scandir($dir);
    foreach ($files as $file)
    if ($file != "." && $file != "..") rrmdir("$dir/$file");
    rmdir($dir);
  }
  else if (file_exists($dir)) unlink($dir);
} 

function generate_config($force=false){

  
  // for update only update to 1.2.9.1 from < 1.2.9
		global $wpdb;
		
		if( $wpdb->get_var("SHOW TABLES LIKE 'information'") != "information"  && $wpdb->get_var("SHOW TABLES LIKE 'information_group'") != "information_group" )  {
		// so its a > 1.2.9	
			require_once WP_PLUGIN_DIR . '/'.basename(dirname(__FILE__)).'/functions/database.php';
	   		require_once WP_PLUGIN_DIR . '/'.basename(dirname(__FILE__)).'/functions/general.php';
	   		$patch_1_2_9=WP_PLUGIN_DIR . '/'.basename(dirname(__FILE__)).'/patch.sql';
			osc_db_connect(DB_HOST, DB_USER, DB_PASSWORD);
	    	osc_set_time_limit(0);
			osc_db_update(DB_NAME, $patch_1_2_9);
		}
		
		if( $wpdb->get_var("SHOW TABLES LIKE 'customers_to_discount_codes'") != "customers_to_discount_codes" && $wpdb->get_var("SHOW TABLES LIKE 'discount_codes'") != "discount_codes" )  {
		// so its a > 1.2.9	
			$structure_customers_to_discount_codes="CREATE TABLE customers_to_discount_codes (
			customers_id int(11) NOT NULL default '0',
			discount_codes_id int(11) NOT NULL default '0',
			KEY customers_id (customers_id),
			KEY discount_codes_id (discount_codes_id)
			);";
			
			$structure_discount_codes="CREATE TABLE IF NOT EXISTS discount_codes (
			  discount_codes_id int(11) NOT NULL AUTO_INCREMENT,
			  products_id text,
			  categories_id text,
			  manufacturers_id text,
			  excluded_products_id text,
			  customers_id text,
			  orders_total tinyint(1) NOT NULL DEFAULT '0',
			  order_info tinyint(1) NOT NULL DEFAULT '0',
			  discount_codes varchar(8) NOT NULL DEFAULT '',
			  discount_values varchar(8) NOT NULL DEFAULT '',
			  minimum_order_amount decimal(15,4) NOT NULL DEFAULT '0.0000',
			  expires_date date NOT NULL DEFAULT '0000-00-00',
			  number_of_orders int(4) NOT NULL DEFAULT '0',
			  number_of_use int(4) NOT NULL DEFAULT '0',
			  number_of_products int(4) NOT NULL DEFAULT '0',
			  status tinyint(1) NOT NULL DEFAULT '1',
			  PRIMARY KEY (discount_codes_id)
			);";
			
			$wpdb->query($structure_customers_to_discount_codes);
			$wpdb->query($structure_discount_codes);
		
		}
		 
// update ends here
 
// for update only update to 1.2.9.3 from < 1.2.9.3

		if( $wpdb->get_var("SHOW columns from customers LIKE 'guest_account'") != "guest_account"){
			$wpdb->query("ALTER TABLE customers ADD COLUMN guest_account TINYINT NOT NULL DEFAULT '0' AFTER customers_newsletter;");
			$wpdb->query("ALTER TABLE customers MODIFY COLUMN customers_password VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_general_ci;");
			$wpdb->query("ALTER TABLE orders ADD COLUMN customers_dummy_account TINYINT UNSIGNED NOT NULL AFTER customers_address_format_id;");
		
			$wpdb->query("INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Purchase without account', 'PURCHASE_WITHOUT_ACCOUNT', 'yes', 'Do you allow customers to purchase without an account?', '5', '10', 'tep_cfg_select_option(array(\'yes\', \'no\'), ', now());");
			$wpdb->query("INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Purchase without account shippingaddress', 'PURCHASE_WITHOUT_ACCOUNT_SEPARATE_SHIPPING', 'yes', 'Do you allow customers without account to create separately shipping address?', '5', '11', 'tep_cfg_select_option(array(\'yes\', \'no\'), ', now());");
			
		}
		
	if( $wpdb->get_var("SHOW TABLES LIKE 'slideshow'") != "slideshow"){
	
			$slide_show_table="CREATE TABLE IF NOT EXISTS slideshow (
			  slideshow_id int(11) NOT NULL auto_increment,
			  slideshow_name varchar(255) NOT NULL default '',
			  slideshow_description text NOT NULL,
			  slideshow_image varchar(255) default NULL,
			  slideshow_url tinytext NOT NULL,
			  date_added datetime default NULL,
			  last_modified datetime default NULL,
			  PRIMARY KEY  (slideshow_id)
			) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;";
			$wpdb->query($slide_show_table);
			
	}

// category description
	if( $wpdb->get_var("SHOW columns from categories_description LIKE 'categories_description'") != "categories_description"){
		$wpdb->query("ALTER TABLE categories_description ADD categories_description text  NULL");
	}

// EPF 

if( $wpdb->get_var("SHOW TABLES LIKE 'extra_product_fields'") != "extra_product_fields"){
	
			$epf_table="CREATE TABLE extra_product_fields (
			  epf_id int unsigned NOT NULL auto_increment,
			  epf_order int NOT NULL default 0,
			  epf_status tinyint(1) NOT NULL default 1,
			  epf_uses_value_list tinyint(1) not null default 0,
			  epf_advanced_search tinyint(1) not null default 1,
			  epf_show_in_listing tinyint(1) not null default 0,
			  epf_size tinyint unsigned not null default 64,
			  epf_use_as_meta_keyword tinyint(1) not null default 0,
			  epf_use_to_restrict_listings tinyint(1) not null default 0,
			  epf_show_parent_chain tinyint(1) not null default 0,
			  epf_quick_search tinyint(1) not null default 0,
			  epf_multi_select tinyint(1) not null default 0,
			  epf_checked_entry tinyint(1) not null default 0,
			  epf_value_display_type tinyint(1) not null default 0,
			  epf_num_columns tinyint unsigned not null default 1,
			  epf_has_linked_field tinyint(1) not null default 0,
			  epf_links_to int unsigned not null default 0,
			  epf_textarea tinyint(1) not null default 0,
			  epf_show_in_admin tinyint(1) not null default 1,
			  epf_all_categories tinyint(1) not null default 1,
			  epf_category_ids text default null,
			  PRIMARY KEY (epf_id),
			  KEY IDX_ORDER (epf_order)
			);";
			$wpdb->query($epf_table);
			
			$epf_table="CREATE TABLE extra_field_labels (
			  epf_id int unsigned NOT NULL,
			  languages_id int NOT NULL,
			  epf_label varchar(64),
			  epf_active_for_language tinyint(1) not null default 1,
			  PRIMARY KEY (epf_id, languages_id)
			);";
			$wpdb->query($epf_table);
			
			$epf_table="create table extra_field_values (
			  value_id int unsigned not null auto_increment,
			  epf_id int unsigned not null,
			  languages_id int not null,
			  parent_id int unsigned not null default 0,
			  sort_order int not null default 0,
			  epf_value varchar(64),
			  value_depends_on int unsigned not null default 0,
			  value_image varchar(255) default null,
			  primary key (value_id),
			  key IDX_EPF (epf_id, languages_id),
			  key IDX_LINK (value_depends_on)
			)
						;";
			$wpdb->query($epf_table);
			$epf_table="create table extra_value_exclude (
			  value_id1 int unsigned not null,
			  value_id2 int unsigned not null,
			  primary key (value_id1, value_id2)
			);";
			$wpdb->query($epf_table);
			
			$wpdb->query("INSERT INTO `configuration` (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`, `set_function`) VALUES ('QuickSearch searches in description', 'QUICKSEARCH_IN_DESCRIPTION', 'true', 'If set to TRUE the customer can quick search in descriptions otherwise the search is limited to the product name', 1, 113, now(), 'tep_cfg_select_option(array(\'true\', \'false\'),');");
	
	}
	
	if( $wpdb->get_var("select count(*) as info_site from configuration_group where configuration_group_title ='Deactivate store features'") ==0){
		$max_configuration_group_id=$wpdb->get_var("select max(configuration_group_id) as max_configuration_group_id from configuration_group ")+1;

		$wpdb->query("INSERT INTO `configuration_group` (`configuration_group_id`, `configuration_group_title`, `configuration_group_description`, `sort_order`) VALUES (".$max_configuration_group_id.", 'Deactivate store features', 'Toggle between full cart or Catalog only', ".$max_configuration_group_id.");");

		$max_configuration_id=$wpdb->get_var("select max(configuration_id) as max_configuration_id from configuration ")+1;
		
		$wpdb->query("INSERT INTO `configuration` (`configuration_id`, `configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `last_modified`, `date_added`, `set_function`) VALUES (".$max_configuration_id.", 'Show Reviews on Site?', 'SHOW_REVIEWS', 'True', 'Do you want the Reviews to show on the site?', ".$max_configuration_group_id.", ".$max_configuration_id.", NULL, NOW(), 'tep_cfg_select_option(array(\'True\', \'False\'),');");
		
		$wpdb->query("INSERT INTO `configuration` (`configuration_id`, `configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `last_modified`, `date_added`, `set_function`) VALUES (".($max_configuration_id+1).", 'Display the Create Account and Log in Page?', 'SHOW_LOGIN', 'True', 'Do you want the Create an account and Login pages to show?<br>This will also prevent the buttons from displaying', ".$max_configuration_group_id.", ".($max_configuration_id+1).", NULL, NOW(), 'tep_cfg_select_option(array(\'True\', \'False\'),');");
		
		$wpdb->query("INSERT INTO `configuration` (`configuration_id`, `configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `last_modified`, `date_added`, `set_function`) VALUES (".($max_configuration_id+2).", 'Display the Prices on your site?', 'SHOW_PRICE', 'True', 'Do you want the Prices to show on your site?', ".$max_configuration_group_id."," .($max_configuration_id+2).", NULL, NOW(), 'tep_cfg_select_option(array(\'True\', \'False\'),');");
		$wpdb->query("INSERT INTO `configuration` (`configuration_id`, `configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `last_modified`, `date_added`, `set_function`) VALUES (".($max_configuration_id+3).", 'Display the Buy now Button on Product info page?', 'SHOW_BUTTON', 'True', 'Do you want the Add to Cart Button to show?<br />The Buy Now button for the products listing page is controlled from the Products Listing menu and has to be turned off seperately', ".$max_configuration_group_id.", ".($max_configuration_id+3).", NULL, NOW(), 'tep_cfg_select_option(array(\'True\', \'False\'),');");
		$wpdb->query("INSERT INTO `configuration` (`configuration_id`, `configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `last_modified`, `date_added`, `set_function`) VALUES (".($max_configuration_id+4).", 'Display the Shopping cart box?', 'SHOW_CART', 'True', 'Do you want the Shopping cart BOX to show?', ".$max_configuration_group_id.", ".($max_configuration_id+4).", NULL, NOW(), 'tep_cfg_select_option(array(\'True\', \'False\'),');");
		$wpdb->query("INSERT INTO `configuration` (`configuration_id`, `configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `last_modified`, `date_added`, `set_function`) VALUES (".($max_configuration_id+5).", 'Display the Currencies box?', 'SHOW_CURRENCIES', 'True', 'Do you want the Currencies BOX to show?', ".$max_configuration_group_id.", ".($max_configuration_id+5).", NULL, NOW(), 'tep_cfg_select_option(array(\'True\', \'False\'),');");
	
	}

	//option type user image uplaod date: 26-09-2012

	if( $wpdb->get_var("SHOW TABLES LIKE 'files_uploaded'") != "files_uploaded"){
				
				$files_uploaded_table="CREATE TABLE files_uploaded (
				`files_uploaded_id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
				`sesskey` VARCHAR( 32 ) ,
				`customers_id` INT( 11 ) ,
				`files_uploaded_name` VARCHAR( 64 ) NOT NULL ,
				`date` VARCHAR( 32 ) ,
				PRIMARY KEY ( `files_uploaded_id` )
				)  ;";
				$wpdb->query($files_uploaded_table);
				
		}	
	//end option type user image uplaod

	// ajax attribute manager
	if( $wpdb->get_var("SHOW columns from products_attributes LIKE 'products_options_sort_order'") != "products_options_sort_order")
		$wpdb->query("ALTER TABLE products_attributes ADD products_options_sort_order int(10) default 0 ");

	//text options
	if( $wpdb->get_var("SHOW columns from products_options LIKE 'products_options_sort_order'") != "products_options_sort_order")
		$wpdb->query("ALTER TABLE products_options ADD products_options_sort_order int(10) default 0 ");


	// BOF Product Sort	 date: 26-09-2012
if( $wpdb->get_var("select count(*) as gtrans from configuration where configuration_key ='PRODUCT_SORT_ORDER'") ==0){
	
	$wpdb->query("INSERT INTO configuration ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ( 'Display Product Sort Order', 'PRODUCT_SORT_ORDER', '0', 'Do you want to display the Product Sort Order column?', 8, 29, '', '', NULL, NULL);");

}

if( $wpdb->get_var("SHOW columns from products LIKE 'products_sort_order'") != "products_sort_order")
		$wpdb->query("ALTER TABLE products ADD products_sort_order int(11) ");
		//$wpdb->query("INSERT INTO configuration (configuration_id, configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (263, 'Display Product Sort Order', 'PRODUCT_SORT_ORDER', '0', 'Do you want to display the Product Sort Order column?', 8, 29, '', '', NULL, NULL);");
		

	// eof Product Sort	
	//product list , grid view
	$configuration_group_id = $wpdb->get_var("select `configuration_group_id` from configuration_group where configuration_group_title ='Product Listing Style' ");
	 $tot_count = $wpdb->get_var("select count(*) as info_site from `configuration` where `configuration_group_id` ='$configuration_group_id'") ;
	if($tot_count <3){
		 
		$sql = "delete from configuration_group where configuration_group_title ='Product Listing Style'";
		$wpdb->query($sql);
		$sql = "delete from `configuration` where `configuration_group_id` ='".$configuration_group_id."'";
		$wpdb->query($sql);
	}
	if( $wpdb->get_var("select count(*) as info_site from configuration_group where configuration_group_title = 'Product Listing Style'") ==0){

		$max_configuration_group_id=$wpdb->get_var("select max(configuration_group_id) as max_configuration_group_id from configuration_group ")+1;
		$wpdb->query("INSERT INTO `configuration_group` (`configuration_group_id`, `configuration_group_title`, `configuration_group_description`, `sort_order`) VALUES (".$max_configuration_group_id.", 'Product Listing Style', 'Product list should be in Rows (with one product per row)  or Grid (Multiple products per row)', ".$max_configuration_group_id.");");

		$max_configuration_id=$wpdb->get_var("select max(configuration_id) as max_configuration_id from configuration ")+1;

	 	 $wpdb->query("INSERT INTO `configuration` (`configuration_id`, `configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `last_modified`, `date_added`, `set_function`) VALUES (".$max_configuration_id.", 'Product Listing Style', 'PRODUCT_LIST_CONTENT_LISTING', 'both', 'Product list should be in Rows (with one product per row)  or Grid (Multiple products per row', ".$max_configuration_group_id.", ".$max_configuration_id.", NULL, NOW(), 'tep_cfg_select_option(array(\'list\', \'grid\',\'both\'),');");

		// echo "<br>". $wpdb->get_var("select count(*) as info_site from `configuration` where `configuration_group_id` ='$max_configuration_group_id'");
		  
		$wpdb->query("INSERT INTO `configuration` (`configuration_id`, `configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `last_modified`, `date_added`, `set_function`) VALUES (".($max_configuration_id+1).", 'Product Listing Columns', 'COLUMN_COUNT', '4', 'If Product Listing is Columns, How many Columns to show. (# of products in a row)', ".$max_configuration_group_id.", ".($max_configuration_id+1).", NULL, NOW(), '');");
	

	    ;
	

	    // echo "<br>". $wpdb->get_var("select count(*) as info_site from `configuration` where `configuration_group_id` ='$max_configuration_group_id'");

			$wpdb->query("INSERT INTO `configuration` (`configuration_id`, `configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `last_modified`, `date_added`, `set_function`) VALUES (".($max_configuration_id+2).", 'Default layout', 'PRODUCT_LIST_CONTENT_LISTING_DEFAULT', 'grid', 'Default setting', ".$max_configuration_group_id.", ".($max_configuration_id+2).", NULL, NOW(), 'tep_cfg_select_option(array(\'list\', \'grid\'),');");
	
		
		  
	
	}
	//end of list , grid view
	// seo url 
if( $wpdb->get_var("SHOW columns from products_description LIKE 'products_head_title_tag'") != "products_head_title_tag"){
			$wpdb->query("ALTER TABLE products_description ADD products_head_title_tag TEXT NULL;");
			$wpdb->query("ALTER TABLE products_description ADD products_head_desc_tag TEXT NULL;");
			$wpdb->query("ALTER TABLE products_description ADD products_head_keywords_tag  TEXT NULL;");
			$wpdb->query("ALTER TABLE products ADD products_seo_url  TEXT NULL;");
}
if( $wpdb->get_var("SHOW columns from categories_description LIKE 'categories_htc_title_tag'") != "categories_htc_title_tag"){			
			$wpdb->query("ALTER TABLE categories_description ADD categories_htc_title_tag TEXT NULL;");
			$wpdb->query("ALTER TABLE categories_description ADD categories_htc_desc_tag  TEXT NULL;");
			$wpdb->query("ALTER TABLE categories_description ADD categories_htc_keywords_tag   TEXT NULL;");
			$wpdb->query("ALTER TABLE categories_description ADD categories_seo_url   TEXT NULL;");
				
		}
		
// google translator button 
if( $wpdb->get_var("select count(*) as gtrans from configuration where configuration_key ='GOOGLE_ON'") ==0){
	
		$wpdb->query("INSERT INTO `configuration` (`configuration_key`, `configuration_value`) VALUES ('GOOGLE_ON', 'true');");
}		

// switch in slideshow
if( $wpdb->get_var("select count(*) as gtrans from configuration where configuration_key ='SLIDESHOW_ON'") ==0){
	
		$wpdb->query("INSERT INTO `configuration` (`configuration_key`, `configuration_value`) VALUES ('SLIDESHOW_ON', '1');");
}

// Order Editor

if( $wpdb->get_var("select count(*) as gtrans from configuration_group where configuration_group_title ='Order Editor'") ==0){
	     $max_configuration_group_id=$wpdb->get_var("select max(configuration_group_id) as max_configuration_group_id from configuration_group ")+1;
		 $wpdb->query("INSERT INTO configuration_group (configuration_group_id, configuration_group_title, configuration_group_description, sort_order, visible) VALUES ('".$max_configuration_group_id."', 'Order Editor', 'Configuration options for Order Editor', ".$max_configuration_group_id.", 1)");
		 $wpdb->query("INSERT into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) values ('Display the Payment Method dropdown?', 'ORDER_EDITOR_PAYMENT_DROPDOWN', 'true', 'Based on this selection Order Editor will display the payment method as a dropdown menu (true) or as an input field (false).', '$max_configuration_group_id', '1', now(), now(), NULL, 'tep_cfg_select_option(array(\'true\', \'false\'),')");
         $wpdb->query("INSERT into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) values ('Display the Payment Method dropdown?', 'ORDER_EDITOR_PAYMENT_DROPDOWN', 'true', 'Based on this selection Order Editor will display the payment method as a dropdown menu (true) or as an input field (false).', '$max_configuration_group_id', '1', now(), now(), NULL, 'tep_cfg_select_option(array(\'true\', \'false\'),')");
         $wpdb->query("INSERT into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) values ('Use prices from Separate Pricing Per Customer?', 'ORDER_EDITOR_USE_SPPC', 'false', 'Leave this set to false unless SPPC is installed.', '$max_configuration_group_id', '3', now(), now(), NULL, 'tep_cfg_select_option(array(\'true\', \'false\'),')");
         $wpdb->query("INSERT into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) values ('Use QTPro contribution?', 'ORDER_EDITOR_USE_QTPRO', 'false', 'Leave this set to false unless you have QTPro Installed.', '$max_configuration_group_id', '4', now(), now(), NULL, 'tep_cfg_select_option(array(\'true\', \'false\'),')");
         $wpdb->query("INSERT into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) values ('Allow the use of AJAX to update order information?', 'ORDER_EDITOR_USE_AJAX', 'true', 'This must be set to false if using a browser on which JavaScript is disabled or not available.', '$max_configuration_group_id', '5', now(), now(), NULL, 'tep_cfg_select_option(array(\'true\', \'false\'),')");
         $wpdb->query("INSERT into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) values ('Select your credit card payment method', 'ORDER_EDITOR_CREDIT_CARD', 'Credit Card', 'Order Editor will display the credit card fields when this payment method is selected.', '$max_configuration_group_id', '6', now(), now(), NULL, 'tep_cfg_pull_down_payment_methods(')");
		 $wpdb->query("INSERT into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) values ('Attach PDF Invoice to New Order Email', 'ORDER_EDITOR_ADD_PDF_INVOICE_EMAIL', 'false', 'When you send a new Order Email a PDF Invoice kan be attach to your email. This function only works if the contribution PDF Invoice is installed.', '$max_configuration_group_id', '15', now(), now(), NULL, 'tep_cfg_select_option(array(\'true\', \'false\'),')");
		 $wpdb->query("ALTER TABLE orders ADD shipping_module VARCHAR( 255 ) NULL");
}

 //minimum qtyo order
	if( $wpdb->get_var("SHOW columns from `categories` LIKE 'categories_min_quan'") != "categories_min_quan"){
		$wpdb->query("ALTER TABLE `categories` ADD categories_min_quan INT( 11 ) NOT NULL ");
	}

// update ends here		

if( $wpdb->get_var("SHOW columns from products_options LIKE 'products_options_type'") != "products_options_type"){			
			$wpdb->query("
ALTER TABLE products_options
  ADD products_options_type INT( 5 ) NOT NULL ,
  ADD products_options_length SMALLINT( 2 ) DEFAULT '32' NOT NULL ,
  ADD products_options_comment VARCHAR( 32 )
                ");
				
		}
		
if( $wpdb->get_var("SHOW columns from customers_basket_attributes LIKE 'products_options_value_text'") != "products_options_value_text"){			
			$wpdb->query("
ALTER TABLE customers_basket_attributes
  ADD products_options_value_text text
                ");
				
		}		
$wpdb->query("ALTER TABLE `orders_products_attributes` CHANGE `products_options_values` `products_options_values` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ");
//		
// indv shipping

if( $wpdb->get_var("SHOW TABLES LIKE 'products_shipping'") != "products_shipping"){
			
			$products_shipping_table="CREATE TABLE `products_shipping` (
			`products_id` int(11) NOT NULL default '0',
			`products_ship_methods_id` int(11) default NULL,
			`products_ship_zip` varchar(32) default NULL,
			`products_ship_price` varchar(10) default NULL,
			`products_ship_price_two` varchar(10) default NULL
			) ;";
			$wpdb->query($products_shipping_table);
  			
	}			
	if( $wpdb->get_var("select count(*) as gtrans from configuration where configuration_key ='INDIVIDUAL_SHIP_HOME_COUNTRY'") ==0){
		$wpdb->query("INSERT INTO configuration VALUES ('', 'Indiv Ship Home Country', 'INDIVIDUAL_SHIP_HOME_COUNTRY', '223', 'Individual ship home country ID (other countries will have extra freight)', 7, NULL, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL, NULL);");
	}
	if( $wpdb->get_var("select count(*) as gtrans from configuration where configuration_key ='INDIVIDUAL_SHIP_INCREASE'") ==0){
		$wpdb->query("INSERT INTO configuration VALUES ('', 'Indiv Ship Outside Home Increase', 'INDIVIDUAL_SHIP_INCREASE', '3', 'Individual ship x increase for shipping outside home country. For example: If you set your item ship price to $50 and this value to 3 and ship outside your home country they will pay $150, and if this value was 2, they would pay $100.', 7, NULL, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL, NULL);");
	}
// update ends here		

		

  $http_url = parse_url(get_bloginfo('wpurl').'/wp-content/plugins/'.basename(dirname(__FILE__)));
  $http_server = $http_url['scheme'] . '://' . $http_url['host'];
  $https_server = $http_url['scheme'] . 's://' . $http_url['host'];
  $http_catalog = $http_url['path'];
  if (isset($http_url['port']) && !empty($http_url['port'])) {
    $http_server .= ':' . $http_url['port'];
  }

  if (substr($http_catalog, -1) != '/') {
    $http_catalog .= '/';
  }
  $dir_fs_document_root=str_replace("\\",'/',WP_PLUGIN_DIR) . '/'.basename(dirname(__FILE__));
  
  $dir_up_to_plugins=parse_url(get_bloginfo('wpurl').'/wp-content/plugins/');

if($force){
  	if (file_exists($dir_fs_document_root . '/admin/includes/configure.php')) 
		@unlink($dir_fs_document_root . '/admin/includes/configure.php');
	
	if (file_exists($dir_fs_document_root . '/includes/configure.php')) 
		@unlink($dir_fs_document_root . '/includes/configure.php');
  }
  

  
  
   if(!file_exists(WP_PLUGIN_DIR."/WP-online-store-product-images")){
	mkdir(WP_PLUGIN_DIR."/WP-online-store-product-images");
	if(file_exists(WP_PLUGIN_DIR."/wpols"))
		rcopy(WP_PLUGIN_DIR.'/wpols/images',WP_PLUGIN_DIR."/WP-online-store-product-images/images");
	else
		rcopy($dir_fs_document_root.'/images',WP_PLUGIN_DIR."/WP-online-store-product-images/images");
  }  
  
if(!file_exists(WP_PLUGIN_DIR."/WP-online-store-additional-packs/stylesheet/stylesheet.css")){
  	if(!file_exists(WP_PLUGIN_DIR."/WP-online-store-additional-packs")) mkdir(WP_PLUGIN_DIR."/WP-online-store-additional-packs");
  	if(!file_exists(WP_PLUGIN_DIR."/WP-online-store-additional-packs/stylesheet")) mkdir(WP_PLUGIN_DIR."/WP-online-store-additional-packs/stylesheet");

  	copy($dir_fs_document_root.'/stylesheet.css',WP_PLUGIN_DIR."/WP-online-store-additional-packs/stylesheet/stylesheet.css");	
  	
  }
  
  if(!file_exists(WP_PLUGIN_DIR."/WP-online-store-additional-packs/stylesheet/ext"))mkdir(WP_PLUGIN_DIR."/WP-online-store-additional-packs/stylesheet/ext");
  if(!file_exists(WP_PLUGIN_DIR."/WP-online-store-additional-packs/stylesheet/ext/960.css"))copy($dir_fs_document_root.'/ext/960gs/960.css',WP_PLUGIN_DIR."/WP-online-store-additional-packs/stylesheet/ext/960.css");
  if(!file_exists(WP_PLUGIN_DIR."/WP-online-store-additional-packs/stylesheet/ext/960_24_col.css"))copy($dir_fs_document_root.'/ext/960gs/960_24_col.css',WP_PLUGIN_DIR."/WP-online-store-additional-packs/stylesheet/ext/960_24_col.css");
  if(!file_exists(WP_PLUGIN_DIR."/WP-online-store-additional-packs/stylesheet/ext/rtl_960.css"))copy($dir_fs_document_root.'/ext/960gs/rtl_960.css',WP_PLUGIN_DIR."/WP-online-store-additional-packs/stylesheet/ext/rtl_960.css");
  if(!file_exists(WP_PLUGIN_DIR."/WP-online-store-additional-packs/stylesheet/ext/rtl_960_24_col.css"))copy($dir_fs_document_root.'/ext/960gs/rtl_960_24_col.css',WP_PLUGIN_DIR."/WP-online-store-additional-packs/stylesheet/ext/rtl_960_24_col.css");
  
  
  $file_contents = '<?php' . "\n" .
                   '  define(\'HTTP_SERVER\', \'' . $http_server . '\');' . "\n" .
                   '  define(\'HTTP_CATALOG_SERVER\', \'' . $http_server . '\');' . "\n" .
                   '  define(\'HTTPS_CATALOG_SERVER\', \'' . $https_server . '\');' . "\n" .
                   '  define(\'ENABLE_SSL_CATALOG\', false);' . "\n" .
                   '  define(\'DIR_FS_DOCUMENT_ROOT\', \'' . $dir_fs_document_root . '\');' . "\n" .
                   '  define(\'DIR_WS_ADMIN\', \'' . get_bloginfo('wpurl') . '/wp-admin/\');' . "\n" .
                   '  define(\'DIR_FS_ADMIN\', \'' . $dir_fs_document_root . '/admin/\');' . "\n" .
                   '  define(\'DIR_WS_CATALOG\', \'' . $http_catalog . '\');' . "\n" .
                   '  define(\'DIR_FS_CATALOG\', \'' . $dir_fs_document_root . '/\');' . "\n" .
                   '  define(\'DIR_WS_IMAGES\', \'images/\');' . "\n" .
                   '  define(\'DIR_WS_ICONS\', DIR_WS_IMAGES . \'icons/\');' . "\n" .
                   '  define(\'DIR_WS_CATALOG_IMAGES\', \''.$dir_up_to_plugins['path'] . 'WP-online-store-product-images/images/\');' . "\n" .
                   '  define(\'DIR_WS_INCLUDES\', \'includes/\');' . "\n" .
                   '  define(\'DIR_WS_BOXES\',   \'boxes/\');' . "\n" .
                   '  define(\'DIR_WS_FUNCTIONS\',   \'functions/\');' . "\n" .
                   '  define(\'DIR_WS_CLASSES\',  DIR_FS_ADMIN.DIR_WS_INCLUDES. \'classes/\');' . "\n" .
                   '  define(\'DIR_WS_MODULES\',   \'modules/\');' . "\n" .
                   '  define(\'DIR_WS_LANGUAGES\',  \'languages/\');' . "\n" .
                   '  define(\'DIR_WS_CATALOG_LANGUAGES\', DIR_WS_CATALOG . \'includes/languages/\');' . "\n" .
                   '  define(\'DIR_FS_CATALOG_LANGUAGES\', DIR_FS_CATALOG . \'/includes/languages/\');' . "\n" .
                   '  define(\'DIR_FS_CATALOG_IMAGES\', \''.WP_PLUGIN_DIR . '/WP-online-store-product-images/images/\');' . "\n" .
                   '  define(\'DIR_FS_CATALOG_MODULES\', DIR_FS_CATALOG . \'/includes/modules/\');' . "\n" .
                   '  define(\'DIR_FS_BACKUP\', DIR_FS_ADMIN . \'backups/\');' . "\n\n" .
                   '  define(\'DB_SERVER\', \'' . trim(DB_HOST) . '\');' . "\n" .
                   '  define(\'DB_SERVER_USERNAME\', \'' . trim(DB_USER) . '\');' . "\n" .
                   '  define(\'DB_SERVER_PASSWORD\', \'' . trim(DB_PASSWORD) . '\');' . "\n" .
                   '  define(\'DB_DATABASE\', \'' . trim(DB_NAME) . '\');' . "\n" .
                   '  define(\'USE_PCONNECT\', \'false\');' . "\n" .
                   '  define(\'STORE_SESSIONS\', \'mysql\');' . "\n" .
                   '?>';
	
  $file_contents_f = '<?php' . "\n" .
                   '  define(\'HTTP_SERVER\', \'' . $http_server . '\');' . "\n" .
                   '  define(\'HTTPS_SERVER\', \'' . $https_server . '\');' . "\n" .
                   '  define(\'ENABLE_SSL\', false);' . "\n" .
  				   '  define(\'HTTP_COOKIE_DOMAIN\', \'localhost\');' . "\n" .
  				   '  define(\'HTTPS_COOKIE_DOMAIN\', \'localhost\');' . "\n" .
  				   '  define(\'HTTP_COOKIE_PATH\', \'' . $http_catalog . '\');' . "\n" .
  				   '  define(\'HTTPS_COOKIE_PATH\', \'' . $http_catalog . '\');' . "\n" .	
 				   '  define(\'DIR_WS_HTTP_CATALOG\', \'' . $http_catalog . '\');' . "\n" .
  				   '  define(\'DIR_WS_HTTPS_CATALOG\', \'' . $http_catalog . '\');' . "\n" . 	
  				   '  define(\'DIR_WS_IMAGES\', \'' . $dir_up_to_plugins['path'] . 'WP-online-store-product-images/images/\');' . "\n" .
				   '  define(\'DIR_WS_ICONS\',  DIR_WS_IMAGES . \'icons/\');' . "\n" .  		
  				   '  define(\'DIR_WS_INCLUDES\',WP_PLUGIN_DIR. \'/'.basename(dirname(__FILE__)).'/includes/\');' . "\n" .
  				   '  define(\'DIR_WS_BOXES\',  DIR_WS_INCLUDES . \'boxes/\');' . "\n" .
  				   '  define(\'DIR_WS_FUNCTIONS\', DIR_WS_INCLUDES . \'functions/\');' . "\n" .
  				   '  define(\'DIR_WS_CLASSES\', DIR_WS_INCLUDES . \'classes/\');' . "\n" .
                   '  define(\'DIR_WS_MODULES\', DIR_WS_INCLUDES . \'modules/\');' . "\n" .
                   '  define(\'DIR_WS_LANGUAGES\',  DIR_WS_INCLUDES . \'languages/\');' . "\n" .
  				   '  define(\'DIR_WS_DOWNLOAD_PUBLIC\', \'pub/\');' . "\n" .
  				   '  define(\'DIR_FS_CATALOG\',\'' . $dir_fs_document_root . '\');' . "\n" .
  				   '  define(\'DIR_FS_DOWNLOAD\',DIR_FS_CATALOG . \'/downloads/\');' . "\n" .
  				   '  define(\'DIR_FS_DOWNLOAD_PUBLIC\',  DIR_FS_CATALOG . \'pub/\');' . "\n" .
  				   '  define(\'DB_SERVER\', \'' . trim(DB_HOST) . '\');' . "\n" .
                   '  define(\'DB_SERVER_USERNAME\', \'' . trim(DB_USER) . '\');' . "\n" .
                   '  define(\'DB_SERVER_PASSWORD\', \'' . trim(DB_PASSWORD) . '\');' . "\n" .
                   '  define(\'DB_DATABASE\', \'' . trim(DB_NAME) . '\');' . "\n" .
                   '  define(\'USE_PCONNECT\', \'false\');' . "\n" .
                   '  define(\'STORE_SESSIONS\', \'mysql\');' . "\n" .
                   '?>';
  
  if (!file_exists($dir_fs_document_root . '/admin/includes/configure.php')) {
	  $fp = fopen($dir_fs_document_root . '/admin/includes/configure.php', 'w+');
	  fputs($fp, $file_contents);
	  fclose($fp);
  }
  
  if (!file_exists($dir_fs_document_root . '/includes/configure.php')) {
	  $fp = fopen($dir_fs_document_root . '/includes/configure.php', 'w+');
	  fputs($fp, $file_contents_f);
	  fclose($fp);  
  }
  @chmod($dir_fs_document_root . '/includes/configure.php', 0444);
  
  
  
  
}  


function uninstall(){
 global $wpdb;
 $query="drop table if exists action_recorder,products_images,sec_directory_whitelist,address_book, address_format, administrators, banners, banners_history, categories, categories_description, configuration, configuration_group, counter, counter_history, countries, currencies, customers, customers_basket, customers_basket_attributes, customers_info, languages, manufacturers, manufacturers_info, orders, orders_products, orders_status, orders_status_history, orders_products_attributes, orders_products_download, products, products_attributes, products_attributes_download, prodcts_description, products_options, products_options_values, products_options_values_to_products_options, products_to_categories, reviews, reviews_description, sessions, specials, tax_class, tax_rates, geo_zones, whos_online, zones, zones_to_geo_zones,newsletters,orders_total,products_description,products_notifications,information,information_group,customers_to_discount_codes,discount_codes,slideshow,extra_product_fields,extra_field_labels,extra_field_values,extra_value_exclude,products_shipping";  
 $wpdb->query($query);
 $dir_fs_document_root=str_replace("\\",'/',WP_PLUGIN_DIR) . '/'.basename(dirname(__FILE__));
 chmod($dir_fs_document_root . '/includes/configure.php', 0777);
 $uploads = wp_upload_dir();
 @unlink($uploads['basedir'].'/WP_online_store.zip');
 
 if(file_exists(WP_PLUGIN_DIR."/WP-online-store-product-images")){
		rrmdir(WP_PLUGIN_DIR."/WP-online-store-product-images");
		rrmdir(WP_PLUGIN_DIR."/WP-online-store-additional-packs");
 }
}

include WP_PLUGIN_DIR . '/'.basename(dirname(__FILE__)).'/core.php';


?>
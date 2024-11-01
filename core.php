<?php 
add_action('admin_menu', 'oscommerce');
add_action('init', 'osc_session_init_fend'); // starts session
add_action('admin_init', 'osc_session_init'); // starts session

//tinymce 
add_filter('mce_external_plugins', "wpols_register");
add_filter('mce_buttons', 'wpols_add_button', 0);


define('WPOLS_PLUGINS_DIR',basename(dirname(__FILE__)));


add_action('permalink_structure_changed','wpols_permalink_structure_changed');

function wpols_permalink_structure_changed($permalink_structure){
	global $wpdb;
	if($permalink_structure!=""){
	//	global $wp_rewrite;
		
	//	$wp_rewrite->add_external_rule( '(.*)/products/([0-9]+)$', '$1/?slug=product_info.php&products_id=$2' );
  		$wpdb->query("update configuration set configuration_value = 'true', last_modified = now() where configuration_id = 11");
	}
	else $wpdb->query("update configuration set configuration_value = 'false', last_modified = now() where configuration_id = 11");

}

/*
add_filter( 'query_vars', 'wpa5413_query_vars' );
function wpa5413_query_vars( $query_vars )
{
    $query_vars[] = 'wftp';
    return $query_vars;
}*/


add_filter( 'plugin_action_links', 'wpols_alert',10,2);



function wpols_alert( $actions, $plugin_file) {
if($plugin_file == basename(dirname(__FILE__)).'/WP_online_store.php')
	{
	
		$url = preg_match('/<a href="(.+)" title="(.+)">/', $actions['deactivate'], $match);
	
		$msg=sprintf('ARE YOU SURE? %sDeactivating this plugin will delete ALL data in the current installation. If you decide to reactivate later you will have a new clean version only. All changes and customizations will be gone.  %sIf you have data / customizations you wish to keep we suggest a manual update. %sPress OK to proceed', '\n\n', '\n\n', '\n\n');
		$actionlink = '<a href="javascript: if(confirm(\''.$msg.'\')) {window.location=\''.$match[1].'\';}" title="Deactivate this plugin">'.Deactivate.'</a>';

		$actions['deactivate']=$actionlink;
	}

	
    return $actions;
}

/*
function wpols_update_notice() {
	$info = __( '&nbsp;ATTENTION! Major Update<a href="http://www.help.wponlinestore.com/index.php?/Knowledgebase/List/Index/27/upgrading-to-version-13" target="_blank">Click Here.</a>', MY_TEXTDOMAIN );
	echo '<span class="spam">' . $info . '</span>';
}
*/
function wpols_update_notice() {

// readme contents
    $data       = file_get_contents( 'http://plugins.svn.wordpress.org/wp-online-store/trunk/readme.txt?format=txt' );

    // assuming you've got a Changelog section
    // @example == Changelog ==
    $changelog  = stristr( $data, '== Changelog ==' );

    $changelog=str_replace('== Changelog ==',"<br>",$changelog);
    $output = $changelog;
    return print $output;


}



add_action( 'in_plugin_update_message-' . basename(dirname(__FILE__)).'/WP_online_store.php', 'wpols_update_notice' );


function wpols_add_button($buttons)
{
    array_push($buttons, "separator", "wpolsplugin");
    return $buttons;
}
 
function wpols_register($plugin_array)
{
    $url = trim(get_bloginfo('wpurl'), "/");
    $url.= "/wp-content/plugins/".basename(dirname(__FILE__))."/editor_plugin.js";
 
    $plugin_array["wpolsplugin"] = $url;
    return $plugin_array;
}

//ends here

//add_action( 'admin_notices', 'osc_wizard_notice'); // notice to use the wizard


function osc_session_init() {
if (!session_id())
session_start();
ob_start();

if($_REQUEST['force']=='backupnow'){
 header('Content-type: application/x-octet-stream');
          header('Content-disposition: attachment; filename=' . $_REQUEST['backup_file']);

          readfile($_REQUEST['turl'] . $_REQUEST['backup_file']);
          unlink($_REQUEST['turl'] . $_REQUEST['backup_file']);
		exit();
}

}

function osc_session_init_fend(){
session_name('osCsid');
  $request_type = (getenv('HTTPS') == 'on') ? 'SSL' : 'NONSSL';
if (isset($_POST['osCsid'])) {

     session_id($_POST['osCsid']);

   }
elseif ( ($request_type == 'SSL') && isset($_GET['osCsid']) ) {

     session_id($_GET['osCsid']);

   }
//if (!session_id())
session_start();
ob_start();
if($_REQUEST['force']=='downloadnow'){
 header('Content-type: application/x-octet-stream');
          header('Content-disposition: attachment; filename=' . $_REQUEST['file']);

          readfile($_REQUEST['turl'] . $_REQUEST['file']);
         // unlink($_REQUEST['turl'] . $_REQUEST['backup_file']);

}
wp_enqueue_script('jquery');
}


function osc_wizard_notice(){

echo '<div class="error fade" style="background-color:red;"><p><strong>Your OSC plugins wont work properly as it is not configured Yet .<br> Please Use <a href="">the Wizard</a> to configure the plugin.</strong></p></div>';
}


function oscommerce(){

add_menu_page('WP Online Store', 'WP Online Store', 'administrator', 'WP_online_store', 'osc_admin');
}


function osc_admin(){

/*
$tmpPath = dirname(__FILE__).'/admin/modules.php';
$cont = file_get_contents($tmpPath);
if(strlen($cont)!=27537){
//echo 'sd'.strlen($cont);
die('You are not using original files!');
}*/
	if($_REQUEST['submenu']==""){
		$current_page='index';
		include(WP_PLUGIN_DIR.'/'.basename(dirname(__FILE__)).'/admin/login.php');
	}	
	else {
		$current_page=$_REQUEST['submenu'];
		include(WP_PLUGIN_DIR.'/'.basename(dirname(__FILE__)).'/admin/'.$_REQUEST['submenu'].'.php');
	}
$contents=ob_get_contents();
ob_end_clean();

if($current_page=="modules" && $_REQUEST['set']=="payment"){
			if($imcs_file!="this is imcs file")
				die('The payment modules are corrupt , Please re-install.');
			else echo $contents;	
		}
else echo $contents;	 		
}
/* -------------------------------------------------------------------------




/* front end begins here */
function WP_online_store(){
//global $currencies,$currency,$messageStack,$tree,$categories_string, $cPath_array;
//global $request_type, $session_started, $SID;
if(!isset($_REQUEST['slug']))
	include(WP_PLUGIN_DIR.'/'.basename(dirname(__FILE__)).'/index.php');
if($_REQUEST['slug'])
	include(WP_PLUGIN_DIR.'/'.basename(dirname(__FILE__)).'/'.$_REQUEST['slug']);	

}
function column_left(){
//require(DIR_WS_INCLUDES . 'column_left.php');
}

function filter($content = '') {
	if ( '' == $content || !strstr($content, '[WP_online_store]') ) { ob_flush();return $content; }
	return preg_replace('|(<p>)?(\n)*[(\[)WP_online_store(\])](\n)*(</p>)?|', do_shortcode( '[WP_online_store]' ), $content);
}
/*
function wp_ols_wp_title($title=''){
if(isset($_GET['products_id'])){
  global $wpdb;
   $product_info_query = "select p.products_id, pd.products_name, pd.products_description, p.products_model, p.products_quantity, p.products_image, pd.products_url, p.products_price, p.products_tax_class_id, p.products_date_added, p.products_date_available, p.manufacturers_id from  products  p,  products_description pd where p.products_status = '1' and p.products_id = '" . (int)$_GET['products_id'] . "' and pd.products_id = p.products_id and pd.language_id = '" . (int)$_SESSION['languages_id'] . "'";
  $product=$wpdb->get_row($product_info_query);
  return  $product->products_name.' : ';
}	
return $title;
}
*/
/* filers declaration */
add_shortcode('WP_online_store', 'WP_online_store');
add_shortcode('column_left', 'column_left');
add_filter('the_content', 'filter');
//add_filter('wp_title', 'wp_ols_wp_title');

/**/
?>
<?php 
/* $Id$
LoginRadius, Open Source E-Commerce Solutions
http://www.LoginRadius.com
Copyright (c) 2011 LoginRadius
Released under the GNU General Public License
*/
// needs to be included earlier to set the success message in the messageStack
  //require_once('includes/application_top.php');
  class bm_loginradius {
    var $code = 'bm_loginradius';
    var $group = 'boxes';
    var $title;
    var $description;
	var $loginpagetext;
	var $accounttext;
    var $sort_order;
	var $api_key;
	var $api_secret_key;
	var $email_required = false;
    var $enabled = false;
	function bm_loginradius() {
      define('MODULE_BOXES_LOGINRADIUS_DESCRIPTION', 'Login with Existing Account');
      define('MODULE_BOXES_LOGINRADIUS_BOX_TITLE', 'Social Login');
      define('MODULE_BOXES_LOGINRADIUS_BOX_TITLE', $this->title);
      $this->title = MODULE_BOXES_LOGINRADIUS_TITLE;
      $this->description = MODULE_BOXES_LOGINRADIUS_DESCRIPTION;
      if ( defined('MODULE_BOXES_LOGINRADIUS_STATUS') ) {
        $this->sort_order = MODULE_BOXES_LOGINRADIUS_SORT_ORDER;
        $this->enabled = (MODULE_BOXES_LOGINRADIUS_STATUS == 'True');
        $this->api_key = MODULE_BOXES_LOGINRADIUS_API_KEY;
        $this->api_secret_key = MODULE_BOXES_LOGINRADIUS_API_SECRET_KEY;
        $this->email_required = (MODULE_BOXES_LOGINRADIUS_EMAIL_REQUIRED == 'True');
        $this->title = MODULE_BOXES_LOGINRADIUS_TITLE;
        $this->loginpagetext = MODULE_BOXES_LOGINRADIUS_LOGINTEXT;
        $this->accounttext = MODULE_BOXES_LOGINRADIUS_ACCTEXT;
        $this->group = ((MODULE_BOXES_LOGINRADIUS_CONTENT_PLACEMENT == 'Left Column')?'boxes_column_left':'boxes_column_right');
      }
    }
    function execute() {
	  global $oscTemplate, $cart, $navigation, $messageStack, $breadcrumb, $session_started, $customer_id, $customer_first_name, $customer_default_address_id, $customer_country_id, $customer_zone_id, $password, $confirm;
      define('DIR_WS_INCLUDES', 'includes/');
      define('DIR_WS_LANGUAGES', DIR_WS_INCLUDES . 'languages/');
      define('FILENAME_CREATE_ACCOUNT', 'create_account.php');
      $language = 'english';
      require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_CREATE_ACCOUNT);
      if ($session_started == false ) {
        tep_redirect(tep_href_link(FILENAME_COOKIE_USAGE));
      }
      //for adding extra field
      function add_column_if_not_exist($db, $column, $column_attr = "varchar( 255 ) NULL" ) {
        $exists = false;
        $columns = mysql_query("show columns from $db");
        while ($c = mysql_fetch_assoc($columns)) {
          if($c['Field'] == $column) {
            $exists = true;
            break;
          }
        }      
        if (!$exists) {
          mysql_query("ALTER TABLE `$db` ADD `$column`  $column_attr");
        }
      }
	  function remove_tmpuser($lrdata) {
    tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key = '".$lrdata['session']."'");
    }
function popup($msg, $lrdata) {?>
<style type="text/css">
.LoginRadius_overlay {background: none no-repeat scroll 0 0 rgba(127, 127, 127, 0.6);position: absolute;top: 0;left: 0;z-index: 100001;width: 100%;height: 100%;overflow: auto;padding: 220px 20px 20px 20px;padding-bottom: 130px;position: fixed;}
#popupouter {-moz-border-radius:4px;-webkit-border-radius:4px;border-radius:4px;overflow:auto;background:#f3f3f3;padding:0px 0px 0px 0px;width:370px;margin:0 auto;}
#popupinner {-moz-border-radius:4px;-webkit-border-radius:4px;border-radius:4px;overflow:auto;background:#ffffff;margin:10px;padding:10px 8px 4px 8px;}
#textmatter {margin:10px 0px 10px 0px;font-family:Arial, Helvetica, sans-serif;color:#666666;font-size:14px;}
.inputtxt {font-family:Arial, Helvetica, sans-serif;color:#a8a8a8;font-size:11px;border:#e5e5e5 1px solid;width:280px;height:27px;margin:5px 0px 15px 0px;}
.inputbutton {border:#dcdcdc 1px solid;-moz-border-radius:2px;-webkit-border-radius:2px;border-radius:2px;text-decoration:none;
color:#6e6e6e;font-family:Arial, Helvetica, sans-serif;font-size:13px;cursor:pointer;background:#f3f3f3;padding:6px 7px 6px 8px;
margin:0px 8px 0px 0px;}
.inputbutton:hover {border:#00ccff 1px solid;-moz-border-radius:2px;-webkit-border-radius:2px;border-radius:2px;khtml-border-radius:2px;text-decoration:none;color:#000000;font-family:Arial, Helvetica, sans-serif;font-size:13px;cursor:pointer;padding:6px 7px 6px 8px;-moz-box-shadow: 0px 0px  4px #8a8a8a;-webkit-box-shadow: 0px 0px  4px #8a8a8a;box-shadow: 0px 0px  4px #8a8a8a;background:#f3f3f3;margin:0px 8px 0px 0px;}
#textdiv {text-align:right;font-family:Arial, Helvetica, sans-serif;font-size:11px;color:#000000;}
.span {font-family:Arial, Helvetica, sans-serif;font-size:11px;color:#00ccff;}
.span1 {font-family:Arial, Helvetica, sans-serif;font-size:11px;color:#333333;}
<!--[if IE]>
.LoginRadius_content_IE {
background:black;-ms-filter:"progid:DXImageTransform.Microsoft.Alpha(Opacity=90)";filter: alpha(opacity=90);}
<![endif]-->
</style>
<?php
  $output = '<div class="LoginRadius_overlay" class="LoginRadius_content_IE"><div id="popupouter">
             <div id="popupinner">
             <div id="textmatter">';
             if ($msg) {
  $output .= "<b>" . $msg . "</b>";
             }
  $output .= '</div>
             <form method="post" action="">
             <div><input type="text" name="email" id="email" class="inputtxt"/></div><div>
             <input type="submit" id="LoginRadiusRedSliderClick" name="LoginRadiusRedSliderClick" value="Submit" class="inputbutton">
             <input type="button" value="Cancel" class="inputbutton" onClick="history.back(0);" />
	         <input type="hidden" value="'.$lrdata['session'].'" name="session" />';
  $output .= '</div></form></div></div></div>';
  print $output;
  }
      $db = 'customers';
      $column = 'loginradiusid';
      add_column_if_not_exist($db, $column, $column_attr = "varchar( 255 ) NULL" );
      //until here
      $apikey_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_BOXES_LOGINRADIUS_API_KEY'");
      $apikey_array = tep_db_fetch_array($apikey_query);
      $apikey = $apikey_array['configuration_value'];
      $apisecretkey_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_BOXES_LOGINRADIUS_API_SECRET_KEY'");
      $apisecretkey_array = tep_db_fetch_array($apisecretkey_query);
      $apisecretkey = $apisecretkey_array['configuration_value'];
      $emailrequired_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_BOXES_LOGINRADIUS_EMAIL_REQUIRED'");
      $emailrequired_array = tep_db_fetch_array($emailrequired_query);
      $emailrequired = $emailrequired_array['configuration_value'];
      //until here
      if (isset($apikey)) {
	    $obj_auth = new OsLoginRadiusAuth();
        $UserAuth = $obj_auth->auth($apikey, $apisecretkey);
	    $IsHttps = $UserAuth->IsHttps;
	    $iframeHeight = $UserAuth->height;
	    if (!$iframeHeight) {
	      $iframeHeight = 50;
        }
        $iframeWidth = $UserAuth->width;
        if (!$iframeWidth) {
          $iframeWidth = 138;
        }
      }
      if ($IsHttps == 1) {
        $http = "https://";
      }
      else {
        $http = "http://";
      }
      $loc = urlencode($http.$_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
      if(tep_session_is_registered('customer_id')) {
	    $data = '<div class="ui-widget infoBoxContainer">' .
                  '  <div class="ui-widget-header infoBoxHeading">' . MODULE_BOXES_LOGINRADIUS_BOX_TITLE . '</div>' .
                  '  <div class="ui-widget-content infoBoxContents">' . "Hello ". $customer_first_name .'!' . '</div>' .
                  '</div>';	
      }
      else {
        $data = '<div class="ui-widget infoBoxContainer">' .
                  '  <div class="ui-widget-header infoBoxHeading">' . MODULE_BOXES_LOGINRADIUS_BOX_TITLE . '</div>' .
                  '  <div class="ui-widget-content infoBoxContents"><iframe src="'.$http.'hub.loginradius.com/Control/PluginSlider2.aspx?apikey='.$apikey.'&callback='.$loc.'" width="'.$iframeWidth.'" height="'.$iframeHeight.'" frameborder="0" scrolling="no" allowtransparency="true" ></iframe>
 </div></div>';
      }
	  $lrdata = array();
      $obj = new OsLoginRadius();
      $userprofile = $obj->construct($apisecretkey);
  if ($obj->IsAuthenticated == true) {
    $process = true;
    $lrdata['id'] = $userprofile->ID;
	$lrdata['session'] = uniqid('LoginRadius_', true);
    $lrdata['Provider'] = $userprofile->Provider;
    $lrdata['FirstName'] = $userprofile->FirstName;
    $lrdata['LastName'] = $userprofile->LastName;
	$lrdata['NickName']    = $userprofile->NickName;
    $lrdata['FullName'] = $userprofile->FullName;
    $lrdata['ProfileName'] = $userprofile->ProfileName;
    $lrdata['dob'] = $userprofile->BirthDate;
    $lrdata['telephone'] = $userprofile->PhoneNumbers[0]->PhoneNumber;
    if (empty($lrdata['telephone'])) {
      $lrdata['telephone'] = 'default';
    }
    $lrdata['gender'] = $userprofile->Gender;
    $lrdata['city'] = $userprofile->City;
    if (empty($lrdata['city'])) {
      $lrdata['city'] = $userprofile->HomeTown;
    }
    $lrdata['state'] = $lrdata['city'];
    $lrdata['address'] = $userprofile->Addresses;
    if (empty($lrdata['address'])) {
      $lrdata['address'] = $lrdata['city'];
    }
    $lrdata['company'] = $userprofile->Positions[0]->Comapny->Name;
    $lrdata['password'] = mt_rand(8, 15);
    $lrdata['Email'] = $userprofile->Email[0]->Value;
    $error = false;
	tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_title = 'Store tmp data'");
	if (empty($lrdata['Email']) && $emailrequired != 'True') {
      switch ($lrdata['Provider']) {
        case 'twitter':
          $lrdata['Email'] = $lrdata['id'] . '@' . $lrdata['Provider'] . '.com';
        break;
        case 'linkedin':
          $lrdata['Email'] = $lrdata['id'] . '@' . $lrdata['Provider'] . '.com';
        break;
        default:
          $Email_id = substr($lrdata['id'], 7);
          $Email_id2 = str_replace("/", "_", $Email_id);
          $lrdata['Email'] = str_replace(".", "_", $Email_id2) . '@' . $lrdata['Provider'] . '.com';
        break;
      }
    }
    if (empty($lrdata['Email']) && $emailrequired == 'True') {
      $check_existId = tep_db_query("select customers_id, customers_firstname, customers_password, customers_email_address, customers_default_address_id from " . TABLE_CUSTOMERS . " where loginradiusid = '" . mysql_real_escape_string($lrdata['id']) . "'");
      $check_customer = tep_db_fetch_array($check_existId);
      if ($check_customer > 0 && $check_customer != '') {
        $customer_id = $check_customer['customers_id'];
		$customer_default_address_id = $check_customer['customers_default_address_id'];
        $customer_first_name = $check_customer['customers_firstname'];
        tep_session_register('customer_id');
        tep_session_register('customer_default_address_id');
        tep_session_register('customer_first_name');
		tep_db_query("update " . TABLE_CUSTOMERS_INFO . " set customers_info_date_of_last_logon = now(), customers_info_number_of_logons = customers_info_number_of_logons+1 where customers_info_id = '" . (int)$customer_id . "'");
        $sessiontoken = md5(tep_rand() . tep_rand() . tep_rand() . tep_rand());
        $cart->restore_contents();  
        $name = $lrdata['FirstName'] . ' ' . $lrdata['LastName'];
        if (sizeof($navigation->snapshot) > 0) {
          $origin_href = tep_href_link($navigation->snapshot['page'], tep_array_to_string($navigation->snapshot['get'], array(tep_session_name())), $navigation->snapshot['mode']);
          $navigation->clear_snapshot();
          tep_redirect($origin_href);
        } 
        else {
		  define('FILENAME_DEFAULT', 'index.php');
          tep_redirect(tep_href_link(FILENAME_DEFAULT));
        } 
      }
      else {
	  foreach($lrdata as $key => $value)
	  {
	    tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description) values ('Store tmp data', '".$lrdata['session']."', '".$value."', '".$key."')");
	  }
		$msg = "Please enter email to proceed.";
        popup($msg, $lrdata);
     } 
   }
  }
   if (isset($_POST['LoginRadiusRedSliderClick']) && !empty($_POST['session'])) {
     $check_existEmail = tep_db_query("select customers_id, customers_firstname, customers_password, customers_email_address, customers_default_address_id from " . TABLE_CUSTOMERS . " where customers_email_address = '" . $_POST['email'] . "'");
     $check_customer = tep_db_fetch_array($check_existEmail);
	 $lrdata['session'] = $_POST['session'];
     if ($check_customer > 0 || tep_validate_email($_POST['email']) != true) {
       $msg = "<p style='color:red;'><b>This email already registered or invalid. Please choose another one.</b></p>";
       popup($msg ,$lrdata);
     }
     else {
	 $query = tep_db_query("select configuration_title, configuration_key, configuration_value, configuration_description from " . TABLE_CONFIGURATION . " where configuration_key = '" . $_POST['session'] . "'");
	  while($tmp_data = tep_db_fetch_array($query)) {
	    $key = $tmp_data['configuration_description'];
	    $value = $tmp_data['configuration_value'];
	    $lrdata[$key] = $value;
	  }
      $lrdata['Email'] = $_POST['email'];
	 }
   }
   if (isset($lrdata['id']) && !empty($lrdata['id']) && !empty($lrdata['Email'])) {
     if (!empty($lrdata['FirstName']) && !empty($lrdata['LastName'])) {
       $lrdata['FirstName'] = $lrdata['FirstName'];
       $lrdata['LastName'] = $lrdata['LastName'];
     }
     elseif (!empty($lrdata['FullName'])) {
       $lrdata['FirstName'] = $lrdata['FullName'];
       $lrdata['LastName'] = $lrdata['FullName'];
     }
     elseif (!empty($lrdata['ProfileName'])) {
       $lrdata['FirstName'] = $lrdata['ProfileName'];
       $lrdata['LastName']  = $lrdata['ProfileName'];
     }
     elseif (!empty($lrdata['NickName'])) {
       $lrdata['FirstName'] = $lrdata['NickName'];
       $lrdata['LastName'] = $lrdata['NickName'];
     }
     elseif (!empty($email)) {
       $user_name = explode('@', $lrdata['Email']);
       $lrdata['FirstName']  = $user_name[0];
       $lrdata['LastName'] = str_replace("_", " ", $user_name[0]);
     }
     else {
       $lrdata['FirstName'] = $lrdata['id'];
       $lrdata['LastName'] = $lrdata['id'];
     }		 
    $check_existId = tep_db_query("select customers_id, customers_firstname, customers_password, customers_email_address, customers_default_address_id from " . TABLE_CUSTOMERS . " where loginradiusid = '" . mysql_real_escape_string($lrdata['id']) . "'");
    $check_customer = tep_db_fetch_array($check_existId);
    if (!$check_customer && $check_customer == '') {
      $check_existEmail = tep_db_query("select customers_id, customers_firstname, customers_password, customers_email_address, customers_default_address_id from " . TABLE_CUSTOMERS . " where customers_email_address = '" . $lrdata['Email'] . "'");
      $check_customer = tep_db_fetch_array($check_existEmail);
    }
    if ($check_customer) {
      $customer_id = $check_customer['customers_id'];
      $customer_default_address_id = $check_customer['customers_default_address_id'];
      $customer_first_name = $check_customer['customers_firstname'];
      tep_session_register('customer_id');
      tep_session_register('customer_default_address_id');
      tep_session_register('customer_first_name');
      tep_db_query("update " . TABLE_CUSTOMERS_INFO . " set customers_info_date_of_last_logon = now(), customers_info_number_of_logons = customers_info_number_of_logons+1 where customers_info_id = '" . (int)$customer_id . "'");
      $sessiontoken = md5(tep_rand() . tep_rand() . tep_rand() . tep_rand());
      $cart->restore_contents();  
      $name = $lrdata['FirstName'] . ' ' . $lrdata['LastName'];
      if (sizeof($navigation->snapshot) > 0) {
        $origin_href = tep_href_link($navigation->snapshot['page'], tep_array_to_string($navigation->snapshot['get'], array(tep_session_name())), $navigation->snapshot['mode']);
        $navigation->clear_snapshot();
        tep_redirect($origin_href);
      }
      else {
        define('FILENAME_DEFAULT', 'index.php');
        tep_redirect(tep_href_link(FILENAME_DEFAULT));
      } 
    }
    else {
      define('FILENAME_COOKIE_USAGE', 'cookie_usage.php');
      define('FILENAME_LOGIN', 'login.php');
      define('FILENAME_ACCOUNT', 'account.php');
      define('FILENAME_ACCOUNT_EDIT', 'account_edit.php');
      require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_ACCOUNT);
      require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_ACCOUNT_EDIT);
      require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_LOGIN);
      $sql_data_array = array('customers_firstname' => $lrdata['FirstName'],
                              'customers_lastname' => $lrdata['LastName'],
                              'customers_email_address' => $lrdata['Email'],
							  'customers_telephone' => $lrdata['telephone'],
							  'customers_gender' => $lrdata['gender'],
							  'customers_dob' => tep_date_raw($lrdata['dob']),
                              'loginradiusid' => mysql_real_escape_string($lrdata['id']),
                              'customers_password' => tep_encrypt_password($lrdata['password']));
      tep_db_perform(TABLE_CUSTOMERS, $sql_data_array);
      $customer_id = tep_db_insert_id();
      $sql_data_array = array('customers_id' => $customer_id,
                              'entry_firstname' => $lrdata['FirstName'],
                              'entry_lastname' => $lrdata['LastName'],
							  'entry_street_address' => $lrdata['address'],
                              'entry_city' => $lrdata['city'],
							  'entry_gender' => $lrdata['gender'],
							  'entry_company' => $lrdata['company'],
							  'entry_state' => $lrdata['state']
							  );
      tep_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array);
      $address_id = tep_db_insert_id();
      tep_db_query("update " . TABLE_CUSTOMERS . " set customers_default_address_id = '" . (int)$address_id . "' where customers_id = '" . (int)$customer_id . "'");
      tep_db_query("insert into " . TABLE_CUSTOMERS_INFO . " (customers_info_id, customers_info_number_of_logons, customers_info_date_account_created) values ('" . (int)$customer_id . "', '0', now())");
      if (SESSION_RECREATE == 'True') {
        tep_session_recreate();
      }
      $customer_first_name = $lrdata['FirstName'];
	  $customer_default_address_id = $address_id;
      tep_session_register('customer_id');
      tep_session_register('customer_default_address_id');
      tep_session_register('customer_first_name');
      $sessiontoken = md5(tep_rand() . tep_rand() . tep_rand() . tep_rand());
      $cart->restore_contents();
	  remove_tmpuser($lrdata); 
	  $name = $lrdata['FirstName'] . ' ' . $lrdata['LastName'];
	  $email_text = sprintf(EMAIL_GREET_NONE, $lrdata['FirstName']);
      $email_text .= EMAIL_WELCOME . EMAIL_TEXT . EMAIL_CONTACT . EMAIL_WARNING;
      tep_mail($name, $lrdata['Email'], EMAIL_SUBJECT, $email_text, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
      define('FILENAME_CREATE_ACCOUNT_SUCCESS', 'create_account_success.php');
      tep_redirect(tep_href_link(FILENAME_CREATE_ACCOUNT_SUCCESS, '', 'SSL'));
	  }
    }
    if ($messageStack->size('create_account') > 0) {
      echo $messageStack->output('create_account');
    }
    $oscTemplate->addBlock($data, $this->group);
  } // function executes ends
  function isEnabled() {
    return $this->enabled;
  }
  function check() {
      return defined('MODULE_BOXES_LOGINRADIUS_STATUS');
  }
  function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Information Module', 'MODULE_BOXES_LOGINRADIUS_STATUS', 'True', 'Do you want to add the module to your shop?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Content Placement', 'MODULE_BOXES_LOGINRADIUS_CONTENT_PLACEMENT', 'Left Column', 'Should the module be loaded in the left or right column?', '6', '1', 'tep_cfg_select_option(array(\'Left Column\', \'Right Column\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_BOXES_LOGINRADIUS_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
	  tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('LoginRadius API Key', 'MODULE_BOXES_LOGINRADIUS_API_KEY', '0', 'Paste LoginRadius API Key here', '6', '0', now())");
	   tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('LoginRadius API Secret', 'MODULE_BOXES_LOGINRADIUS_API_SECRET_KEY', '0', 'Paste LoginRadius API Secret here', '6', '0', now())");
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Email Required', 'MODULE_BOXES_LOGINRADIUS_EMAIL_REQUIRED', 'True', 'Is Email Required?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Title', 'MODULE_BOXES_LOGINRADIUS_TITLE', 'Social Login', 'Enter the Module Title of your choice', '6', '0', now())");
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Text on Login page', 'MODULE_BOXES_LOGINRADIUS_LOGINTEXT', 'You do not have to create a new account, login with your existing account using any of the following Providers:', 'Enter the text which you want to be appeared on Login page', '6', '0', now())");
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Text on Registration page', 'MODULE_BOXES_LOGINRADIUS_ACCTEXT', 'You do not have to create a new account, login with your existing account using any of the following Providers:', 'Enter the text which you want to be appeared on Registration page', '6', '0', now())");
	}
	 
  function remove() {
    tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

  function keys() {
       return array('MODULE_BOXES_LOGINRADIUS_STATUS', 
                    'MODULE_BOXES_LOGINRADIUS_TITLE',
                    'MODULE_BOXES_LOGINRADIUS_LOGINTEXT',
                    'MODULE_BOXES_LOGINRADIUS_ACCTEXT',
                    'MODULE_BOXES_LOGINRADIUS_CONTENT_PLACEMENT', 
                    'MODULE_BOXES_LOGINRADIUS_SORT_ORDER',
                    'MODULE_BOXES_LOGINRADIUS_API_KEY',
                    'MODULE_BOXES_LOGINRADIUS_API_SECRET_KEY',
                    'MODULE_BOXES_LOGINRADIUS_EMAIL_REQUIRED'
              );
    }
}//class ends 
class OsLoginRadius {
  public $IsAuthenticated, $JsonResponse, $UserProfile; 
  public function construct($ApiSecrete) {
    $IsAuthenticated = false;

    if (isset($_REQUEST['token'])) {
      $ValidateUrl = "https://hub.loginradius.com/userprofile.ashx?token=".$_REQUEST['token']."&apisecrete=".$ApiSecrete."";
      if (in_array('curl', get_loaded_extensions())) {
        $curl_handle = curl_init();
        curl_setopt($curl_handle, CURLOPT_URL, $ValidateUrl);
        curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, false);
        if (ini_get('open_basedir') == '' && (ini_get('safe_mode') == 'Off' or !ini_get('safe_mode'))) {
          curl_setopt($curl_handle, CURLOPT_FOLLOWLOCATION, 1);
          curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
          $JsonResponse = curl_exec($curl_handle);
        }
        else {
          curl_setopt($curl_handle, CURLOPT_HEADER, 1);
          $url = curl_getinfo($curl_handle, CURLINFO_EFFECTIVE_URL);
          curl_close($curl_handle);
          $ch = curl_init();
          $url = str_replace('?','/?',$url);
          curl_setopt($ch, CURLOPT_URL, $url);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          $JsonResponse = curl_exec($ch);
          curl_close($ch);
        }
        $UserProfile = json_decode($JsonResponse);
      }
      else {
        $JsonResponse = file_get_contents($ValidateUrl);
        $UserProfile = json_decode($JsonResponse);
      }
      
      if (isset($UserProfile->ID) && $UserProfile->ID != ''){ 
        $this->IsAuthenticated = true;
        return $UserProfile;
      }
    }
  }
}
class OsLoginRadiusAuth {
  public $IsAuth, $JsonResponse, $UserAuth; 
  public function auth($ApiKey, $ApiSecrete){
    $IsAuth = false;
    if (isset($ApiKey)) {
      $ApiKey = trim($ApiKey);
      $ApiSecrete = trim($ApiSecrete);
      $ValidateUrl = "https://hub.loginradius.com/getappinfo/$ApiKey/$ApiSecrete";
      if (in_array('curl', get_loaded_extensions())) {
        $curl_handle = curl_init();
        curl_setopt($curl_handle, CURLOPT_URL, $ValidateUrl);
        curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, false);
        if (ini_get('open_basedir') == '' && (ini_get('safe_mode') == 'Off' or !ini_get('safe_mode'))) {
          curl_setopt($curl_handle, CURLOPT_FOLLOWLOCATION, 1);
          curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
          $JsonResponse = curl_exec($curl_handle);
        }
        else {
          curl_setopt($curl_handle, CURLOPT_HEADER, 1);
          $url = curl_getinfo($curl_handle, CURLINFO_EFFECTIVE_URL);
          curl_close($curl_handle);
          $ch = curl_init();
          $url = str_replace('?','/?',$url);
          curl_setopt($ch, CURLOPT_URL, $url);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          $JsonResponse = curl_exec($ch);
          curl_close($ch);
        }
        $UserAuth = json_decode($JsonResponse);
      }
      else {
        $JsonResponse = file_get_contents($ValidateUrl);
        $UserAuth = json_decode($JsonResponse);
      }
      if (isset( $UserAuth->IsValid)){ 
        $this->IsAuth = true;
        return $UserAuth;
      }
    }
  }
}?>
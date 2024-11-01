<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

// redirect the customer to a friendly cookie-must-be-enabled page if cookies are disabled (or the session has not started)
  if ($session_started == false) {
    tep_redirect(tep_href_link(FILENAME_COOKIE_USAGE));
  }

  require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_LOGIN);

  $error = false;
  if (isset($HTTP_GET_VARS['action']) && ($HTTP_GET_VARS['action'] == 'process') && isset($HTTP_POST_VARS['formid']) && ($HTTP_POST_VARS['formid'] == $sessiontoken)) {
    $email_address = tep_db_prepare_input($HTTP_POST_VARS['email_address']);
    $password = tep_db_prepare_input($HTTP_POST_VARS['password']);

// Check if email exists
   // $check_customer_query = tep_db_query("select customers_id, customers_firstname, customers_password, customers_email_address, customers_default_address_id from " . TABLE_CUSTOMERS . " where customers_email_address = '" . tep_db_input($email_address) . "'");
   // PWA BOF
// using guest_account with customers_email_address
   $check_customer_query = tep_db_query(  "select customers_id, customers_firstname, customers_password, customers_email_address, customers_default_address_id, guest_account from " . TABLE_CUSTOMERS . " where customers_email_address = '" . tep_db_input($email_address). "' and guest_account='0'");
// PWA EOF
    if (!tep_db_num_rows($check_customer_query)) {
      $error = true;
    } else {
      $check_customer = tep_db_fetch_array($check_customer_query);
// Check that password is good
      if (!tep_validate_password($password, $check_customer['customers_password'])) {
        $error = true;
      } else {
        if (SESSION_RECREATE == 'True') {
        	
        	tep_session_recreate();
        }

// migrate old hashed password to new phpass password
        if (tep_password_type($check_customer['customers_password']) != 'phpass') {
          tep_db_query("update " . TABLE_CUSTOMERS . " set customers_password = '" . tep_encrypt_password($password) . "' where customers_id = '" . (int)$check_customer['customers_id'] . "'");
        }

        $check_country_query = tep_db_query("select entry_country_id, entry_zone_id from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int)$check_customer['customers_id'] . "' and address_book_id = '" . (int)$check_customer['customers_default_address_id'] . "'");
        $check_country = tep_db_fetch_array($check_country_query);

        global 	$customer_id,$customer_default_address_id,$customer_first_name,$customer_country_id,$customer_zone_id;
        
        $customer_id = $check_customer['customers_id'];
        $customer_default_address_id = $check_customer['customers_default_address_id'];
        $customer_first_name = $check_customer['customers_firstname'];
        $customer_country_id = $check_country['entry_country_id'];
        $customer_zone_id = $check_country['entry_zone_id'];
        tep_session_register('customer_id');
        tep_session_register('customer_default_address_id');
        tep_session_register('customer_first_name');
        tep_session_register('customer_country_id');
        tep_session_register('customer_zone_id');

        tep_db_query("update " . TABLE_CUSTOMERS_INFO . " set customers_info_date_of_last_logon = now(), customers_info_number_of_logons = customers_info_number_of_logons+1 where customers_info_id = '" . (int)$customer_id . "'");

// reset session token
        $sessiontoken = md5(tep_rand() . tep_rand() . tep_rand() . tep_rand());

// restore cart contents
        $cart->restore_contents();

        if (sizeof($navigation->snapshot) > 0) {
          $origin_href = tep_href_link($navigation->snapshot['page'], tep_array_to_string($navigation->snapshot['get'], array(tep_session_name())), $navigation->snapshot['mode']);
          $navigation->clear_snapshot();
          tep_redirect($origin_href);
        } else {
          tep_redirect(tep_href_link(FILENAME_DEFAULT));
        }
      }
    }
  }

  if ($error == true) {
    $messageStack->add('login', TEXT_LOGIN_ERROR);
  }

  $breadcrumb->add(NAVBAR_TITLE, tep_href_link(FILENAME_LOGIN, '', 'SSL'));

  require(DIR_WS_INCLUDES . 'template_top.php');
?>

<h1><?php echo HEADING_TITLE; ?></h1>

<?php
  if ($messageStack->size('login') > 0) {
    echo $messageStack->output('login');
  }
?>



<?php
  // PWA BOF
  if (defined('PURCHASE_WITHOUT_ACCOUNT') && ($cart->count_contents() > 0) && (PURCHASE_WITHOUT_ACCOUNT == 'ja' || PURCHASE_WITHOUT_ACCOUNT == 'yes')) {
?>
          <div class="contentContainer" style="width: 100%; float: top;"><?php echo TEXT_GUEST_INTRODUCTION; ?>
                    <div class="contentText">
                        <p><strong>Skip registration and just checkout:</strong>
<?php 
                       echo tep_draw_button(IMAGE_BUTTON_CHECKOUT, 'triangle-1-e', tep_href_link(FILENAME_CREATE_ACCOUNT, 'guest=guest', 'SSL'), 'primary');
                        
                    ?></p>
                        <p><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></p>
                  
           </div></div>
<?php
  }
  // PWA EOF
?>


<?php // login radious 
	//Adding loginradius interface
   if (defined('MODULE_BOXES_LOGINRADIUS_STATUS') && (MODULE_BOXES_LOGINRADIUS_STATUS == 'True')) {
  $text_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_BOXES_LOGINRADIUS_LOGINTEXT'");
$text_array = tep_db_fetch_array($text_query);
$text = $text_array['configuration_value'];
  echo $text."<br><br>";
 $apikey_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_BOXES_LOGINRADIUS_API_KEY'");
$apikey_array = tep_db_fetch_array($apikey_query);
$apikey = $apikey_array['configuration_value'];
$apisecretkey_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_BOXES_LOGINRADIUS_API_SECRET_KEY'");
$apisecretkey_array = tep_db_fetch_array($apisecretkey_query);
$apisecretkey = $apisecretkey_array['configuration_value'];
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
echo "Welcome! <b> " . $customer_first_name . "</b><br><br>";
}
else {
 echo '<iframe src="'.$http.'hub.loginradius.com/Control/PluginSlider2.aspx?apikey='.$apikey.'&callback='.$loc.'" width="'.$iframeWidth.'" height="'.$iframeHeight.'" frameborder="0" scrolling="no"></iframe>';
 }
}

// ends social radious
?>
  


<div class="contentContainer" style="width: 45%; float: left;">
  <h2><?php echo HEADING_NEW_CUSTOMER; ?></h2>
  <div class="contentText">
    <p><?php echo TEXT_NEW_CUSTOMER; ?></p>
    <p><?php echo TEXT_NEW_CUSTOMER_INTRODUCTION; ?></p>

    <p align="right"><?php echo tep_draw_button(IMAGE_BUTTON_CONTINUE, 'triangle-1-e', tep_href_link(FILENAME_CREATE_ACCOUNT, '', 'SSL')); ?></p>
  </div>
</div>

<div class="contentContainer" style="width: 45%; float: left; border-left: 1px dashed #ccc; padding-left: 3%; margin-left: 3%;">
  <h2><?php echo HEADING_RETURNING_CUSTOMER; ?></h2>

  <div class="contentText">
    <p><?php echo TEXT_RETURNING_CUSTOMER; ?></p>

    <?php echo tep_draw_form('login', tep_href_link(FILENAME_LOGIN, 'action=process', 'SSL'), 'post', '', true); ?>

    <table border="0" cellspacing="0" cellpadding="2" width="100%">
      <tr>
        <td class="fieldKey"><?php echo ENTRY_EMAIL_ADDRESS; ?></td>
        <td class="fieldValue"><?php echo tep_draw_input_field('email_address','','size=16'); ?></td>
      </tr>
      <tr>
        <td class="fieldKey"><?php echo ENTRY_PASSWORD; ?></td>
        <td class="fieldValue"><?php echo tep_draw_password_field('password','','size=16'); ?></td>
      </tr>
    </table>

    <p><?php echo '<a href="' . tep_href_link(FILENAME_PASSWORD_FORGOTTEN, '', 'SSL') . '">' . TEXT_PASSWORD_FORGOTTEN . '</a>'; ?></p>

    <p align="right"><?php echo tep_draw_button(IMAGE_BUTTON_LOGIN, 'key', null, 'primary'); ?></p>

    </form>
  </div>
</div>

<?php
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>

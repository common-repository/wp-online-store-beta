<?php

/* $Id: authorizenet_aim.php 19th December, 2008 09:00:00 Colt Taylor $
   Released under the GNU General Public License
   
   osCommerce, Open Source E-Commerce Solutions
   http://www.oscommerce.com

   Original portions copyright 2003 osCommerce
   Updated portions copyright 2004 Jason LeBaron (jason@networkdad.com)
   Restoration of original portions and addition of new portions Copyright (c) 2006 osCommerce
   Updated portions and additions copyright 2006 Brent O'Keeffe - JK Consulting. (brent@jkconsulting.net)
   Customized to the RedFin Gateway - copyright 2008 RedFin Network - Colt Taylor
*/
$redfin_code = 'All right reserved by IMC group';
  class redfin {
    var $code, $title, $description, $enabled, $response;

// class constructor
    function redfin() {

      $this->code = 'redfin';

      if ($_GET['main_page'] != '') {
        $this->title = MODULE_PAYMENT_REDFIN_TEXT_CATALOG_TITLE; // Module title in Catalog
      } else {
        $this->title = MODULE_PAYMENT_REDFIN_TEXT_ADMIN_TITLE; // Module title it Admin
      }
     
      $this->description = MODULE_PAYMENT_REDFIN_TEXT_DESCRIPTION; // Description of Module in Admin
      $this->enabled = ((MODULE_PAYMENT_REDFIN_STATUS == 'True') ? true : false); // If the module is installed or not
      $this->sort_order = MODULE_PAYMENT_REDFIN_SORT_ORDER; // Sort Order of this payment option on the checkout_payment.php page
      $this->form_action_url = tep_href_link('checkout_process.php', '', 'SSL', true); // checkout_process.php - page to go to on completion

      if ((int)MODULE_PAYMENT_REDFIN_ORDER_STATUS_ID > 0) {
        $this->order_status = MODULE_PAYMENT_REDFIN_ORDER_STATUS_ID;
      }

      if (is_object($order)) $this->update_status();

    }

    function update_status() {
      global $order, $db;

      if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_REDFIN_ZONE > 0) ) {
        $check_flag = false;
        $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_REDFIN_ZONE . "' and zone_country_id = '" . $order->billing['country']['id'] . "' order by zone_id");
        while ($check = tep_db_fetch_array($check_query)) {
          if ($check['zone_id'] < 1) {
            $check_flag = true;
            break;
          } elseif ($check['zone_id'] == $order->billing['zone_id']) {
            $check_flag = true;
            break;
          }
        }

        if ($check_flag == false) {
          $this->enabled = false;
        }
      }
    }

    // Validate the credit card information via javascript (Number, Owner, and CVV Lengths)
    function javascript_validation() {
      $js = '  if (payment_value == "' . $this->code . '") {' . "\n" .
            '    var cc_owner = document.checkout_payment.redfin_cc_owner.value;' . "\n" .
            '    var cc_number = document.checkout_payment.redfin_cc_number.value;' . "\n";
            
      if (MODULE_PAYMENT_REDFIN_USE_CVV == 'True')  {
        $js .= '    var cc_cvv = document.checkout_payment.redfin_cc_cvv.value;' . "\n";
      }
      
      $js .= '    if (cc_owner == "" || cc_owner.length < ' . CC_OWNER_MIN_LENGTH . ') {' . "\n" .
             '      error_message = error_message + "' . MODULE_PAYMENT_REDFIN_TEXT_JS_CC_OWNER . '";' . "\n" .
             '      error = 1;' . "\n" .
             '    }' . "\n" .
             '    if (cc_number == "" || cc_number.length < ' . CC_NUMBER_MIN_LENGTH . ') {' . "\n" .
             '      error_message = error_message + "' . MODULE_PAYMENT_REDFIN_TEXT_JS_CC_NUMBER . '";' . "\n" .
             '      error = 1;' . "\n" .
             '    }' . "\n";
             
      if (MODULE_PAYMENT_REDFIN_USE_CVV == 'True')  {
                $js .= '    if (cc_cvv == "" || cc_cvv.length < "3" || cc_cvv.length > "4") {' . "\n".
               '      error_message = error_message + "' . MODULE_PAYMENT_REDFIN_TEXT_JS_CC_CVV . '";' . "\n" .
               '      error = 1;' . "\n" .
               '    }' . "\n";            
      }

      $js .= '  }' . "\n";

      return $js;
    }    
    

    // Display Credit Card information on the checkout_payment.php page
    function selection() {
      global $order;

      for ($i=1; $i<13; $i++) {
        $expires_month[] = array('id' => sprintf('%02d', $i), 'text' => strftime('%B',mktime(0,0,0,$i,1,2000)));
      }

      $today = getdate();
      for ($i=$today['year']; $i < $today['year']+10; $i++) {
        $expires_year[] = array('id' => strftime('%y',mktime(0,0,0,1,1,$i)), 'text' => strftime('%Y',mktime(0,0,0,1,1,$i)));
      }
      
      
      $selection = array('id' => $this->code,
                         'module' => MODULE_PAYMENT_REDFIN_TEXT_CATALOG_TITLE,
                         'fields' => array(array('title' => MODULE_PAYMENT_REDFIN_TEXT_CREDIT_CARD_OWNER,
                                                 'field' => tep_draw_input_field('redfin_cc_owner', $order->billing['firstname'] . ' ' . $order->billing['lastname'])),
                                           array('title' => MODULE_PAYMENT_REDFIN_TEXT_CREDIT_CARD_NUMBER,
                                                 'field' => tep_draw_input_field('redfin_cc_number')),
                                           array('title' => MODULE_PAYMENT_REDFIN_TEXT_CREDIT_CARD_EXPIRES,
                                                 'field' => tep_draw_pull_down_menu('redfin_cc_expires_month', $expires_month) . '&nbsp;' . tep_draw_pull_down_menu('redfin_cc_expires_year', $expires_year))));
                                             
      if (MODULE_PAYMENT_REDFIN_USE_CVV == 'True') {
          $selection['fields'][] = array('title' => MODULE_PAYMENT_REDFIN_TEXT_CVV,
                                         'field' => tep_draw_input_field('redfin_cc_cvv','',"size=4, maxlength=4"));
      }
      
      return $selection;
    }


    // Evaluates the Credit Card Type for acceptance and validity of the Credit Card Number and Expiry Date
    function pre_confirmation_check() {

      require_once(DIR_WS_CLASSES . 'cc_validation.php');

      $cc_validation = new cc_validation();
      $result = $cc_validation->validate($_POST['redfin_cc_number'], $_POST['redfin_cc_expires_month'], $_POST['redfin_cc_expires_year'], $_POST['redfin_cc_cvv']);
      $error = '';
      switch ($result) {
        case -1:
          $error = sprintf(TEXT_CCVAL_ERROR_UNKNOWN_CARD, substr($cc_validation->cc_number, 0, 4));
          break;
        case -2:
        case -3:
        case -4:
          $error = TEXT_CCVAL_ERROR_INVALID_DATE;
          break;
        case false:
          $error = TEXT_CCVAL_ERROR_INVALID_NUMBER;
          break;
      }

      if ( ($result == false) || ($result < 1) ) {
        $payment_error_return = 'payment_error=' . $this->code . '&error=' . urlencode($error) . '&redfin_cc_owner=' . urlencode($_POST['redfin_cc_owner']) . '&redfin_cc_expires_month=' . $_POST['redfin_cc_expires_month'] . '&redfin_cc_expires_year=' . $_POST['redfin_cc_expires_year'];

        tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, $payment_error_return, 'SSL', true, false));
      }

      $this->cc_card_type = $cc_validation->cc_type;
      $this->cc_card_number = $cc_validation->cc_number;
      $this->cc_expiry_month = $cc_validation->cc_expiry_month;
      $this->cc_expiry_year = $cc_validation->cc_expiry_year;
    }

    // Display Credit Card Information on the Checkout Confirmation Page
    function confirmation() {
      global $order;
      
      $confirmation = array('fields' => array(array('title' => MODULE_PAYMENT_REDFIN_TEXT_CREDIT_CARD_TYPE,
                                                    'field' => $this->cc_card_type),
                                              array('title' => MODULE_PAYMENT_REDFIN_TEXT_CREDIT_CARD_OWNER,
                                                    'field' => $_POST['redfin_cc_owner']),
                                              array('title' => MODULE_PAYMENT_REDFIN_TEXT_CREDIT_CARD_NUMBER,
                                                    'field' => substr($this->cc_card_number, 0, 4) . str_repeat('X', (strlen($this->cc_card_number) - 8)) . substr($this->cc_card_number, -4)),
                                              array('title' => MODULE_PAYMENT_REDFIN_TEXT_CREDIT_CARD_EXPIRES,
                                                    'field' => $this->cc_expiry_month . substr($this->cc_expiry_year, -2))));
                                                    
      if (MODULE_PAYMENT_REDFIN_USE_CVV == 'True') {
        $confirmation['fields'][] = array('title' => MODULE_PAYMENT_REDFIN_TEXT_CVV,
                                          'field' => str_repeat('X', strlen($_POST['redfin_cc_cvv'])));
      }

      return $confirmation;
    }

    function process_button() {
      // Hidden fields on the checkout confirmation page
      $process_button_string = tep_draw_hidden_field('redfin_cc_owner', $_POST['redfin_cc_owner']) .
                               tep_draw_hidden_field('redfin_cc_expires_month', $_POST['redfin_expires_month']) . 
                               tep_draw_hidden_field('redfin_cc_expires_year', $_POST['redfin_expires_year'], -2) .
                               tep_draw_hidden_field('redfin_cc_type', $this->cc_card_type) .
                               tep_draw_hidden_field('redfin_cc_number', $this->cc_card_number) . 
                               tep_draw_hidden_field(tep_session_name(), tep_session_id());                           
      if (MODULE_PAYMENT_REDFIN_USE_CVV == 'True') {
        $process_button_string .= tep_draw_hidden_field('redfin_cc_cvv', $_POST['redfin_cc_cvv']);
      }     
      return $process_button_string;
    }

    function before_process() {
      global $order;
      if (empty($this->cc_card_type)) {
        $this->pre_confirmation_check();
      }
      $order_time = date("F j, Y, g:i a");
      $last_order_id = tep_db_query("select * from " . TABLE_ORDERS . " order by orders_id desc limit 1");
      $new_order_id = $last_order_id->fields['orders_id'];
      $new_order_id = ($new_order_id + 1);
      $url = "https://secure.redfinnet.com/smartpayments/transact.asmx/ProcessCreditCard";
      $username = MODULE_PAYMENT_REDFIN_LOGIN;
      $password = MODULE_PAYMENT_REDFIN_PASSWORD; 
      $transtype = "sale";
      $magdata = "";
      $invnum =  $new_order_id;
      $pnref = "";
      $extdata = "";
      $email = $order->customer['email_address'];
      $nameoncard = strtoupper($_POST['redfin_cc_owner']);
      $street = $order->billing['street_address'];
      $zip = $order->billing['postcode'];
      $city = $order->billing['city'];
      $state = $order->billing['state'];
      $country = $order->billing['country']['title'];
      $cardnum = $_POST['redfin_cc_number'];
      $expmonth = $_POST['redfin_cc_expires_month'];
      $expyear = $_POST['redfin_cc_expires_year'];
      $expdate = $_POST['redfin_cc_expires_month'] . substr($_POST['redfin_cc_expires_year'], -2);
      $amount = number_format($order->info['total'], 2);
      if (MODULE_PAYMENT_REDFIN_USE_CVV == 'True') {
        $cardcode = $_POST['redfin_cc_cvv'];        
      }
      else {
        $cardcode = ""; 
      }     
      $result = "";
      $post = "UserName=".$username."&Password=".$password."&TransType=".$transtype."&CardNum=".$cardnum."&ExpDate=".$expdate."&Amount=".$amount."&MagData=".$magdata."&NameOnCard=".$nameoncard."&InvNum=".$invnum."&PNRef=".$pmref."&Zip=".$zip."&Street=".$street."&CVNum=".$cardcode."&ExtData=".$extdata;
      $ch=curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_POST, 1) ;
      curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
      if(MODULE_PAYMENT_REDFIN_CURL_PROXY != 'none') {
        curl_setopt ($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
        curl_setopt ($ch, CURLOPT_PROXY,MODULE_PAYMENT_REDFIN_CURL_PROXY);
      }     
      $xmlresponse = curl_exec($ch);
      curl_close($ch);
      
      $xml = simplexml_load_string($xmlresponse);
      $result = $xml->Result ." - ";
      $respmsg = $xml->RespMSG ." - ";
      $authcode = $xml->AuthCode ." - ";
      $pnref = $xml->PNRef;
      If ($result != 0) {
         tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . urlencode($respmsg) . ' - ' . urlencode(MODULE_PAYMENT_REDFIN_TEXT_DECLINED_MESSAGE), 'SSL', true, false));
      }
      }

    function after_process() {
      global $insert_id, $order;     
      if ((int)$insert_id < 1) return false;      
      $cc_number = preg_replace('/[^0-9]/', '', $_POST['redfin_cc_number']);      
      $cc_number = str_repeat('X', strlen($cc_number) - 4) . substr($cc_number, -4);
      
/*      
      $order_time = date("F j, Y, g:i a");
      $description = '';
      for ($i=0; $i<sizeof($order->products); $i++) {
        $description .= $order->products[$i]['name'] . '(qty: ' . $order->products[$i]['qty'] . ') + ';
      }
      if (MODULE_PAYMENT_REDFIN_EMAIL_CUSTOMER == "True") {
         header("Content-type: text/html");
		     $to = $order->customer['email_address'];
		     $subject = "Thankyou for your Purchase";
		     $body = "A Credit Card Payment for your recent purchase has been received.\nCard Number: " . $cardnum ."\nAmount: $". $amount ."\n\nEmail: ". $email ."\nName: ". $nameoncard ."\nStreet: ". $street ."\nCity/State/ZIP: ". $city .", ". $state ." ". $zip ."\nCountry: ". $country;
		     $headers = "From: " . MODULE_PAYMENT_REDFIN_MERCHANT_EMAIL . "\r\nReply-To: " . MODULE_PAYMENT_REDFIN_MERCHANT_EMAIL;
		     if (mail($to, $subject, $body, $headers)) 
			      {
		 	      } 
		     else 
			      {
		 	      }
      }
      if (MODULE_PAYMENT_REDFIN_EMAIL_MERCHANT == "True") {

      }
*/      
      
      tep_db_query("UPDATE " . TABLE_ORDERS . " 
                    SET cc_type = '" . tep_db_input($this->cc_card_type) . "',
                        cc_owner = '" . tep_db_input($_POST['redfin_cc_owner']) . "',
                        cc_number = '" . $cc_number . "',
                        cc_expires = '" . tep_db_input($_POST['redfin_cc_expires_month'] . substr($_POST['redfin_cc_expires_year'], -2)) . "'                                                    
                    WHERE orders_id = " . (int)$insert_id . " LIMIT 1");
      return false;
    }

    function get_error() {
      global $_GET;
      $error = array('title' => MODULE_PAYMENT_REDFIN_TEXT_ERROR,
                     'error' => stripslashes(urldecode($_GET['error'])));
      return $error;
    }

    function check() {    
      if (!isset($this->_check)) {
        $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_REDFIN_STATUS'");
        $this->_check = tep_db_num_rows($check_query);
      }
      return $this->_check;
    }

    function install() {     
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Redfin Module', 'MODULE_PAYMENT_REDFIN_STATUS', 'True', 'Do you want to accept Redfin Network payments?', '6', '0', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Login Username', 'MODULE_PAYMENT_REDFIN_LOGIN', 'Your User Name', 'The login username used for the Redfin Network service', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Login Password', 'MODULE_PAYMENT_REDFIN_PASSWORD', 'Your Password', 'The login password used for the Redfin Network service', '6', '0', now())");
/*      
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Transaction Mode', 'MODULE_PAYMENT_REDFIN_TESTMODE', 'Test', 'Transaction mode used for processing orders', '6', '0', 'tep_cfg_select_option(array(\'Test\', \'Live\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Authorization Type', 'MODULE_PAYMENT_REDFIN_AUTHORIZATION_TYPE', 'Authorize/Capture', 'Do you want submitted credit card transactions to be authorized only, or authorized and captured?', '6', '0', 'tep_cfg_select_option(array(\'Authorize\', \'Authorize/Capture\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Customer Notifications', 'MODULE_PAYMENT_REDFIN_EMAIL_CUSTOMER', 'False', 'Should Redfin Network e-mail a receipt to the customer?', '6', '0', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Merchant Notifications', 'MODULE_PAYMENT_REDFIN_EMAIL_MERCHANT', 'True', 'Should Redfin Network e-mail a receipt to the merchant?', '6', '0', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Merchant Email Address', 'MODULE_PAYMENT_REDFIN_MERCHANT_EMAIL', 'Your Email Address', 'The Email Address to receive Merchant Notifications', '6', '0', now())");
*/            
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Request CVV Number', 'MODULE_PAYMENT_REDFIN_USE_CVV', 'True', 'Do you want to ask the customer for the card\'s CVV number', '6', '0', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_REDFIN_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_REDFIN_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '2', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Order Status', 'MODULE_PAYMENT_REDFIN_ORDER_STATUS_ID', '0', 'Set the status of orders made with this payment module to this value', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('CURL Proxy URL', 'MODULE_PAYMENT_REDFIN_CURL_PROXY', 'none', 'CURL Proxy URL.  Some hosting providers require you to use their CURL Proxy.  Enter the full URL here.  If Not necessary, use - none', '6', '0', now())");
    }

    function remove() {     
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
//    return array('MODULE_PAYMENT_REDFIN_STATUS', 'MODULE_PAYMENT_REDFIN_LOGIN', 'MODULE_PAYMENT_REDFIN_PASSWORD', 'MODULE_PAYMENT_REDFIN_TESTMODE', 'MODULE_PAYMENT_REDFIN_AUTHORIZATION_TYPE', 'MODULE_PAYMENT_REDFIN_EMAIL_CUSTOMER', 'MODULE_PAYMENT_REDFIN_EMAIL_MERCHANT', 'MODULE_PAYMENT_REDFIN_MERCHANT_EMAIL', 'MODULE_PAYMENT_REDFIN_USE_CVV', 'MODULE_PAYMENT_REDFIN_SORT_ORDER', 'MODULE_PAYMENT_REDFIN_ZONE', 'MODULE_PAYMENT_REDFIN_ORDER_STATUS_ID', 'MODULE_PAYMENT_REDFIN_CURL_PROXY');
      return array('MODULE_PAYMENT_REDFIN_STATUS', 'MODULE_PAYMENT_REDFIN_LOGIN', 'MODULE_PAYMENT_REDFIN_PASSWORD', 'MODULE_PAYMENT_REDFIN_USE_CVV', 'MODULE_PAYMENT_REDFIN_SORT_ORDER', 'MODULE_PAYMENT_REDFIN_ZONE', 'MODULE_PAYMENT_REDFIN_ORDER_STATUS_ID', 'MODULE_PAYMENT_REDFIN_CURL_PROXY');
    }
  }
?>
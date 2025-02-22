<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');
 /* ** GOOGLE CHECKOUT **/
  define('GC_STATE_NEW', 100);
  define('GC_STATE_PROCESSING', 101);
  define('GC_STATE_SHIPPED', 102);
  define('GC_STATE_REFUNDED', 103);
  define('GC_STATE_SHIPPED_REFUNDED', 104);
  define('GC_STATE_CANCELED', 105);
  function google_checkout_state_change($check_status, $status, $oID, 
                                              $cust_notify, $notify_comments) {
      global $db,$messageStack, $orders_statuses;

      define('API_CALLBACK_ERROR_LOG', 
                       DIR_FS_CATALOG. "/googlecheckout/logs/response_error.log");
      define('API_CALLBACK_MESSAGE_LOG',
                       DIR_FS_CATALOG . "/googlecheckout/logs/response_message.log");

      include_once(DIR_FS_CATALOG.'/includes/modules/payment/googlecheckout.php');
      include_once(DIR_FS_CATALOG.'/googlecheckout/library/googlerequest.php');

      $googlepayment = new googlecheckout();
      
      $Grequest = new GoogleRequest($googlepayment->merchantid, 
                                    $googlepayment->merchantkey, 
                                    MODULE_PAYMENT_GOOGLECHECKOUT_MODE==
                                      'https://sandbox.google.com/checkout/'
                                      ?"sandbox":"production",
                                    DEFAULT_CURRENCY);
      $Grequest->SetLogFiles(API_CALLBACK_ERROR_LOG, API_CALLBACK_MESSAGE_LOG);


      $google_answer = tep_db_fetch_array(tep_db_query("SELECT go.google_order_number,  go.order_amount, o.customers_email_address, gc.buyer_id, o.customers_id
                                      FROM " . $googlepayment->table_order . " go 
                                      inner join " . TABLE_ORDERS . " o on go.orders_id =  o.orders_id
                                      inner join " . $googlepayment->table_name . " gc on  gc.customers_id = o.customers_id
                                      WHERE go.orders_id = '" . (int)$oID ."'
                                      group by o.customers_id order by o.orders_id desc"));

      $google_order = $google_answer['google_order_number'];  
      $amount = $google_answer['order_amount'];  

    // If status update is from Google New -> Google Processing on the Admin UI
    // this invokes the processing-order and charge-order commands
    // 1->Google New, 2-> Google Processing
    if($check_status['orders_status'] == GC_STATE_NEW 
               && $status == GC_STATE_PROCESSING && $google_order != '') {
      list($curl_status,) = $Grequest->SendChargeOrder($google_order, $amount);
      if($curl_status != 200) {
        $messageStack->add_session(GOOGLECHECKOUT_ERR_SEND_CHARGE_ORDER, 'error');
      }
      else {
        $messageStack->add_session(GOOGLECHECKOUT_SUCCESS_SEND_CHARGE_ORDER, 'success');           
      }
      list($curl_status,) = $Grequest->SendProcessOrder($google_order);
      if($curl_status != 200) {
        $messageStack->add_session(GOOGLECHECKOUT_ERR_SEND_PROCESS_ORDER, 'error');
      }
      else {
        $messageStack->add_session(GOOGLECHECKOUT_SUCCESS_SEND_PROCESS_ORDER, 'success');           
      }
    } 
    
    // If status update is from Google Processing or Google Refunded -> Google Shipped on the  Admin UI
    // this invokes the deliver-order and archive-order commands
    // 2->Google Processing or Google Refunded, 3-> Google Shipped (refunded)
    else if(($check_status['orders_status'] == GC_STATE_PROCESSING 
            || $check_status['orders_status'] == GC_STATE_REFUNDED)
                 && ($status == GC_STATE_SHIPPED || $status == GC_STATE_SHIPPED_REFUNDED )
                 && $google_order != '') {
      $carrier = $tracking_no = "";
      // Add tracking Data
      if(isset($_POST['carrier_select']) &&  ($_POST['carrier_select'] != 'select') 
           && isset($_POST['tracking_number']) && !empty($_POST['tracking_number'])) {
        $carrier = $_POST['carrier_select'];
        $tracking_no = $_POST['tracking_number'];
        $comments = GOOGLECHECKOUT_STATE_STRING_TRACKING ."\n" .
                    GOOGLECHECKOUT_STATE_STRING_TRACKING_CARRIER . $_POST['carrier_select']  ."\n" .
                    GOOGLECHECKOUT_STATE_STRING_TRACKING_NUMBER . $_POST['tracking_number'] .  "";
        tep_db_query("insert into " . TABLE_ORDERS_STATUS_HISTORY . "
                    (orders_id, orders_status_id, date_added, customer_notified, comments)
                    values ('" . (int)$oID . "',
                    '" . tep_db_input(($check_status['orders_status']==GC_STATE_REFUNDED
                                      ?GC_STATE_SHIPPED_REFUNDED:GC_STATE_SHIPPED)) . "',
                    now(),
                    '" . tep_db_input($cust_notify) . "',
                    '" . tep_db_input($comments)  . "')");
         
      }
      
      list($curl_status,) = $Grequest->SendDeliverOrder($google_order, $carrier,
                              $tracking_no, ($cust_notify==1)?"true":"false");
      if($curl_status != 200) {
        $messageStack->add_session(GOOGLECHECKOUT_ERR_SEND_DELIVER_ORDER, 'error');
      }
      else {
        $messageStack->add_session(GOOGLECHECKOUT_SUCCESS_SEND_DELIVER_ORDER, 'success');           
      }
      list($curl_status,) = $Grequest->SendArchiveOrder($google_order);
      if($curl_status != 200) {
        $messageStack->add_session(GOOGLECHECKOUT_ERR_SEND_ARCHIVE_ORDER, 'error');
      }
      else {
        $messageStack->add_session(GOOGLECHECKOUT_SUCCESS_SEND_ARCHIVE_ORDER, 'success');           
      }
    } 
    // If status update is to Google Canceled on the Admin UI
    // this invokes the cancel-order and archive-order commands
    else if($check_status['orders_status'] != GC_STATE_CANCELED &&
            $status == GC_STATE_CANCELED && $google_order != '') {
      if($check_status['orders_status'] != GC_STATE_NEW){
        list($curl_status,) = $Grequest->SendRefundOrder($google_order, 0,
                                        GOOGLECHECKOUT_STATE_STRING_ORDER_CANCELED
                                        );
        if($curl_status != 200) {
          $messageStack->add_session(GOOGLECHECKOUT_ERR_SEND_REFUND_ORDER, 'error');
        }
        else {
          $messageStack->add_session(GOOGLECHECKOUT_SUCCESS_SEND_REFUND_ORDER, 'success');           
        }
      }
      else {
        // Tell google witch is the OSC's internal order Number        
        list($curl_status,) = $Grequest->SendMerchantOrderNumber($google_order, $oID);
        if($curl_status != 200) {
          $messageStack->add_session(GOOGLECHECKOUT_ERR_SEND_MERCHANT_ORDER_NUMBER, 'error');
        }
        else {
          $messageStack->add_session(GOOGLECHECKOUT_SUCCESS_SEND_MERCHANT_ORDER_NUMBER,  'success');          
        }
      }
//    Is the order is not archive, I do it
      if($check_status['orders_status'] != GC_STATE_SHIPPED 
         && $check_status['orders_status'] != GC_STATE_SHIPPED_REFUNDED){
        list($curl_status,) = $Grequest->SendArchiveOrder($google_order);
        if($curl_status != 200) {
          $messageStack->add_session(GOOGLECHECKOUT_ERR_SEND_ARCHIVE_ORDER, 'error');
        }
        else {
          $messageStack->add_session(GOOGLECHECKOUT_SUCCESS_SEND_ARCHIVE_ORDER, 'success');           
        }
      }
//    Cancel the order
      list($curl_status,) = $Grequest->SendCancelOrder($google_order, 
                                      GOOGLECHECKOUT_STATE_STRING_ORDER_CANCELED,
                                      $notify_comments);
      if($curl_status != 200) {
        $messageStack->add_session(GOOGLECHECKOUT_ERR_SEND_CANCEL_ORDER, 'error');
      }
      else {
        $messageStack->add_session(GOOGLECHECKOUT_SUCCESS_SEND_CANCEL_ORDER, 'success');           
      }
    }
    else if($google_order != '' 
            && $check_status['orders_status'] != $status){
      $statuses = array();
      foreach($orders_statuses as $status_array){
        $statuses[$status_array['id']] = $status_array['text'];
      }
      $messageStack->add_session( sprintf(GOOGLECHECKOUT_ERR_INVALID_STATE_TRANSITION,
                                  $statuses[$check_status['orders_status']],
                                  $statuses[$status],
                                  $statuses[$check_status['orders_status']]),
                                  'error');
    }    
    
    // Send Buyer's message
    if($cust_notify==1 && isset($notify_comments) && !empty($notify_comments)) {
      $cust_notify_ok = '0';      
      if(!((strlen(htmlentities(strip_tags($notify_comments))) > GOOGLE_MESSAGE_LENGTH)
              && MODULE_PAYMENT_GOOGLECHECKOUT_USE_CART_MESSAGING=='True')){
    
        list($curl_status,) = $Grequest->sendBuyerMessage($google_order, 
                             $notify_comments, "true");
        if($curl_status != 200) {
          $messageStack->add_session(GOOGLECHECKOUT_ERR_SEND_MESSAGE_ORDER, 'error');
          $cust_notify_ok = '0';
        }
        else {
          $messageStack->add_session(GOOGLECHECKOUT_SUCCESS_SEND_MESSAGE_ORDER, 'success');           
          $cust_notify_ok = '1';
        }
        if(strlen(htmlentities(strip_tags($notify_comments))) > GOOGLE_MESSAGE_LENGTH) {
          $messageStack->add_session(
          sprintf(GOOGLECHECKOUT_WARNING_CHUNK_MESSAGE, GOOGLE_MESSAGE_LENGTH), 'warning');           
        }
      }
      // Cust notified
      return $cust_notify_ok;
    }
    // Cust notified
    return '0';
  }
  // ** END GOOGLE CHECKOUT ** 
  
  
  if(isset($_POST['oID']))	
  	$HTTP_GET_VARS['oID']=$_POST['oID'];
  if(isset($_POST['status']))	
  	$HTTP_GET_VARS['status']=$_POST['status'];
  if(isset($HTTP_POST_VARS['pages']))
  	$HTTP_GET_VARS['pages'] = $HTTP_POST_VARS['pages'] ;

  require(DIR_WS_CLASSES . 'currencies.php');
  $currencies = new currencies();

  $orders_statuses = array();
  $orders_status_array = array();
  $orders_status_query = tep_db_query("select orders_status_id, orders_status_name from " . TABLE_ORDERS_STATUS . " where language_id = '" . (int)$languages_id . "'");
  while ($orders_status = tep_db_fetch_array($orders_status_query)) {
    $orders_statuses[] = array('id' => $orders_status['orders_status_id'],
                               'text' => $orders_status['orders_status_name']);
    $orders_status_array[$orders_status['orders_status_id']] = $orders_status['orders_status_name'];
  }

  $action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');

  if (tep_not_null($action)) {
    switch ($action) {
      case 'update_order':
        $oID = tep_db_prepare_input($HTTP_GET_VARS['oID']);
        $status = tep_db_prepare_input($HTTP_POST_VARS['status']);
        $comments = tep_db_prepare_input($HTTP_POST_VARS['comments']);

        $order_updated = false;
        $check_status_query = tep_db_query("select customers_name, customers_email_address, orders_status, date_purchased from " . TABLE_ORDERS . " where orders_id = '" . (int)$oID . "'");
        $check_status = tep_db_fetch_array($check_status_query);

       /* if ( ($check_status['orders_status'] != $status) || tep_not_null($comments)) {
          tep_db_query("update " . TABLE_ORDERS . " set orders_status = '" . tep_db_input($status) . "', last_modified = now() where orders_id = '" . (int)$oID . "'");

          $customer_notified = '0';
          if (isset($HTTP_POST_VARS['notify']) && ($HTTP_POST_VARS['notify'] == 'on')) {
            $notify_comments = '';
            if (isset($HTTP_POST_VARS['notify_comments']) && ($HTTP_POST_VARS['notify_comments'] == 'on')) {
              $notify_comments = sprintf(EMAIL_TEXT_COMMENTS_UPDATE, $comments) . "\n\n";
            }

            $email = STORE_NAME . "\n" . EMAIL_SEPARATOR . "\n" . EMAIL_TEXT_ORDER_NUMBER . ' ' . $oID . "\n" . EMAIL_TEXT_INVOICE_URL . ' ' . tep_mail_catalog_href_link(FILENAME_CATALOG_ACCOUNT_HISTORY_INFO, 'order_id=' . $oID, 'SSL') . "\n" . EMAIL_TEXT_DATE_ORDERED . ' ' . tep_date_long($check_status['date_purchased']) . "\n\n" . $notify_comments . sprintf(EMAIL_TEXT_STATUS_UPDATE, $orders_status_array[$status]);

            tep_mail($check_status['customers_name'], $check_status['customers_email_address'], EMAIL_TEXT_SUBJECT, $email, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);

            $customer_notified = '1';
          }

          tep_db_query("insert into " . TABLE_ORDERS_STATUS_HISTORY . " (orders_id, orders_status_id, date_added, customer_notified, comments) values ('" . (int)$oID . "', '" . tep_db_input($status) . "', now(), '" . tep_db_input($customer_notified) . "', '" . tep_db_input($comments)  . "')");

          $order_updated = true;
        } */
        
        if ( ($check_status['orders_status'] != $status) || tep_not_null($comments)) {
          tep_db_query("update " . TABLE_ORDERS . " set orders_status = '" .  tep_db_input($status) . "', last_modified = now() where orders_id = '" . (int)$oID . "'");

// ** GOOGLE CHECKOUT **
          if(defined('MODULE_PAYMENT_GOOGLECHECKOUT_STATUS')){
          //chdir("./..");
          require_once(DIR_FS_CATALOG.DIR_WS_INCLUDES.DIR_WS_LANGUAGES . $language . '/modules/payment/googlecheckout.php');
          $payment_value= MODULE_PAYMENT_GOOGLECHECKOUT_TEXT_TITLE;
          $num_rows = tep_db_num_rows(tep_db_query("select google_order_number from  google_orders where orders_id= ". (int)$oID));

          if($num_rows != 0) {
            $customer_notified = google_checkout_state_change($check_status, $status, $oID, 
                               (@$_POST['notify']=='on'?1:0), 
                               (@$_POST['notify_comments']=='on'?$comments:''));
          }
          $customer_notified = isset($customer_notified)?$customer_notified:'0';
// ** END GOOGLE CHECKOUT **
          }
          if (isset($_POST['notify']) && ($_POST['notify'] == 'on')) {
            $notify_comments = '';
            if (isset($_POST['notify_comments']) && ($_POST['notify_comments'] == 'on') &&  tep_not_null($comments)) {
              $notify_comments = EMAIL_TEXT_COMMENTS_UPDATE . $comments . "\n\n";
            }
// ** GOOGLE CHECKOUT **
            $force_email = false;
            if($num_rows != 0 && (strlen(htmlentities(strip_tags($notify_comments))) >  GOOGLE_MESSAGE_LENGTH && MODULE_PAYMENT_GOOGLECHECKOUT_USE_CART_MESSAGING == 'True')) {
              $force_email = true;
              $messageStack->add_session(GOOGLECHECKOUT_WARNING_SYSTEM_EMAIL_SENT,  'warning');          
            }

            if($num_rows == 0 || $force_email) {
  //send emails, not a google order or configured to use both messaging systems
              $email = STORE_NAME . "\n" . EMAIL_SEPARATOR . "\n" . EMAIL_TEXT_ORDER_NUMBER .  ' ' . $oID . "\n" . EMAIL_TEXT_INVOICE_URL . ' ' .  tep_mail_catalog_href_link(FILENAME_CATALOG_ACCOUNT_HISTORY_INFO, 'order_id=' . $oID, 'SSL') .  "\n" . EMAIL_TEXT_DATE_ORDERED . ' ' . tep_date_long($check_status['date_purchased']) .  "\n\n" . $notify_comments . sprintf(EMAIL_TEXT_STATUS_UPDATE, $orders_status_array[$status]);
              tep_mail($check_status['customers_name'],  $check_status['customers_email_address'], EMAIL_TEXT_SUBJECT, $email, STORE_OWNER,  STORE_OWNER_EMAIL_ADDRESS);
              $customer_notified = '1';
  //send extra emails
            }
          }
          tep_db_query("insert into " . TABLE_ORDERS_STATUS_HISTORY . " (orders_id,  orders_status_id, date_added, customer_notified, comments) values ('" . (int)$oID . "', '" .  tep_db_input($status) . "', now(), '" . tep_db_input($customer_notified) . "', '" .  tep_db_input($comments)  . "')");
          $order_updated = true;
        }
        

        if ($order_updated == true) {
         $messageStack->add_session(SUCCESS_ORDER_UPDATED, 'success');
        } else {
          $messageStack->add_session(WARNING_ORDER_NOT_UPDATED, 'warning');
        }

        tep_redirect(tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('action')) . 'action=edit'));
        break;
      case 'deleteconfirm':
        $oID = tep_db_prepare_input($HTTP_GET_VARS['oID']);

        tep_remove_order($oID, $HTTP_POST_VARS['restock']);

        tep_redirect(tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('oID', 'action'))));
        break;
    }
  }

  if (($action == 'edit') && isset($HTTP_GET_VARS['oID'])) {
    $oID = tep_db_prepare_input($HTTP_GET_VARS['oID']);

    $orders_query = tep_db_query("select orders_id from " . TABLE_ORDERS . " where orders_id = '" . (int)$oID . "'");
    $order_exists = true;
    if (!tep_db_num_rows($orders_query)) {
      $order_exists = false;
      $messageStack->add(sprintf(ERROR_ORDER_DOES_NOT_EXIST, $oID), 'error');
    }
  }

  include(DIR_WS_CLASSES . 'order.php');

  require(DIR_WS_INCLUDES . 'template_top.php');
?>

    <table border="0" width="100%" cellspacing="0" cellpadding="2">
<?php
  if (($action == 'edit') && ($order_exists == true)) {
    $order = new order($oID);
?>
      <tr>
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE.TEXT_HELP_INSTURCTIONS.TEXT_HELP_VIDEO; ?></td>
            <td class="pageHeading" align="right"><?php echo tep_draw_separator('pixel_trans.gif', 1, HEADING_IMAGE_HEIGHT); ?></td>

<td class="smallText" align="right"><?php echo tep_draw_button(IMAGE_ORDERS_EDIT, 'document', tep_href_link(FILENAME_ORDERS_EDIT, 'oID=' . $HTTP_GET_VARS['oID']), null, array('newwindow' => true)) . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a>';echo tep_draw_button(IMAGE_ORDERS_INVOICE, 'document', tep_href_link(FILENAME_ORDERS_INVOICE, 'oID=' . $HTTP_GET_VARS['oID']), null, array('newwindow' => true)) . tep_draw_button(IMAGE_ORDERS_PACKINGSLIP, 'document', tep_href_link(FILENAME_ORDERS_PACKINGSLIP, 'oID=' . $HTTP_GET_VARS['oID']), null, array('newwindow' => true)) . tep_draw_button(IMAGE_BACK, 'triangle-1-w', tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('action')))); ?></td>

 
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table width="100%" border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td colspan="3"><?php echo tep_draw_separator(); ?></td>
          </tr>
          <tr>
            <td valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="2">
              <tr>
                <td class="main" valign="top"><strong><?php echo ENTRY_CUSTOMER; ?></strong></td>
                <td class="main"><?php echo tep_address_format($order->customer['format_id'], $order->customer, 1, '', '<br />'); ?></td>
              </tr>
              <tr>
                <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
              </tr>
              <tr>
                <td class="main"><strong><?php echo ENTRY_TELEPHONE_NUMBER; ?></strong></td>
                <td class="main"><?php echo $order->customer['telephone']; ?></td>
              </tr>
              <tr>
                <td class="main"><strong><?php echo ENTRY_EMAIL_ADDRESS; ?></strong></td>
                <td class="main"><?php echo '<a href="mailto:' . $order->customer['email_address'] . '"><u>' . $order->customer['email_address'] . '</u></a>'; ?></td>
              </tr>
            </table></td>
            <td valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="2">
              <tr>
                <td class="main" valign="top"><strong><?php echo ENTRY_SHIPPING_ADDRESS; ?></strong></td>
                <td class="main"><?php echo tep_address_format($order->delivery['format_id'], $order->delivery, 1, '', '<br />'); ?></td>
              </tr>
            </table></td>
            <td valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="2">
              <tr>
                <td class="main" valign="top"><strong><?php echo ENTRY_BILLING_ADDRESS; ?></strong></td>
                <td class="main"><?php echo tep_address_format($order->billing['format_id'], $order->billing, 1, '', '<br />'); ?></td>
              </tr>
            </table></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td><table border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main"><strong><?php echo ENTRY_PAYMENT_METHOD; ?></strong></td>
            <td class="main"><?php echo $order->info['payment_method']; ?></td>
          </tr>
<?php
    if (tep_not_null($order->info['cc_type']) || tep_not_null($order->info['cc_owner']) || tep_not_null($order->info['cc_number'])) {
?>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo ENTRY_CREDIT_CARD_TYPE; ?></td>
            <td class="main"><?php echo $order->info['cc_type']; ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo ENTRY_CREDIT_CARD_OWNER; ?></td>
            <td class="main"><?php echo $order->info['cc_owner']; ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo ENTRY_CREDIT_CARD_NUMBER; ?></td>
            <td class="main"><?php echo $order->info['cc_number']; ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo ENTRY_CREDIT_CARD_EXPIRES; ?></td>
            <td class="main"><?php echo $order->info['cc_expires']; ?></td>
          </tr>
<?php
    }
?>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr class="dataTableHeadingRow">
            <td class="dataTableHeadingContent" colspan="2"><?php echo TABLE_HEADING_PRODUCTS; ?></td>
            <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS_MODEL; ?></td>
            <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_TAX; ?></td>
            <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_PRICE_EXCLUDING_TAX; ?></td>
            <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_PRICE_INCLUDING_TAX; ?></td>
            <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_TOTAL_EXCLUDING_TAX; ?></td>
            <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_TOTAL_INCLUDING_TAX; ?></td>
          </tr>
<?php
    for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
      echo '          <tr class="dataTableRow">' . "\n" .
           '            <td class="dataTableContent" valign="top" align="right">' . $order->products[$i]['qty'] . '&nbsp;x</td>' . "\n" .
           '            <td class="dataTableContent" valign="top">' . $order->products[$i]['name'];

      if (isset($order->products[$i]['attributes']) && (sizeof($order->products[$i]['attributes']) > 0)) {
        for ($j = 0, $k = sizeof($order->products[$i]['attributes']); $j < $k; $j++) {
        
   //BOF - Zappo - Option Types v2 - Removed <nobr>, because Text options can be very long
          echo '<br><small>&nbsp;<i> - ' . $order->products[$i]['attributes'][$j]['option'] . ': ' . $order->products[$i]['attributes'][$j]['value'];
          if ($order->products[$i]['attributes'][$j]['price'] != '0') echo ' (' . $order->products[$i]['attributes'][$j]['prefix'] . $currencies->format($order->products[$i]['attributes'][$j]['price'] * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']) . ')';
          echo '</i></small>';
//EOF - Zappo - Option Types v2 - Removed <nobr>, because Text options can be very long
        }
      }

      echo '            </td>' . "\n" .
           '            <td class="dataTableContent" valign="top">' . $order->products[$i]['model'] . '</td>' . "\n" .
           '            <td class="dataTableContent" align="right" valign="top">' . tep_display_tax_value($order->products[$i]['tax']) . '%</td>' . "\n" .
           '            <td class="dataTableContent" align="right" valign="top"><strong>' . $currencies->format($order->products[$i]['final_price'], true, $order->info['currency'], $order->info['currency_value']) . '</strong></td>' . "\n" .
           '            <td class="dataTableContent" align="right" valign="top"><strong>' . $currencies->format(tep_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax'], true), true, $order->info['currency'], $order->info['currency_value']) . '</strong></td>' . "\n" .
           '            <td class="dataTableContent" align="right" valign="top"><strong>' . $currencies->format($order->products[$i]['final_price'] * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']) . '</strong></td>' . "\n" .
           '            <td class="dataTableContent" align="right" valign="top"><strong>' . $currencies->format(tep_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax'], true) * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']) . '</strong></td>' . "\n";
      echo '          </tr>' . "\n";
    }
?>
          <tr>
            <td align="right" colspan="8"><table border="0" cellspacing="0" cellpadding="2">
<?php
    for ($i = 0, $n = sizeof($order->totals); $i < $n; $i++) {
      echo '              <tr>' . "\n" .
           '                <td align="right" class="smallText">' . $order->totals[$i]['title'] . '</td>' . "\n" .
           '                <td align="right" class="smallText">' . $order->totals[$i]['text'] . '</td>' . "\n" .
           '              </tr>' . "\n";
    }
?>
            </table></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td class="main"><table border="1" cellspacing="0" cellpadding="5">
          <tr>
            <td class="smallText" align="center"><strong><?php echo TABLE_HEADING_DATE_ADDED; ?></strong></td>
            <td class="smallText" align="center"><strong><?php echo TABLE_HEADING_CUSTOMER_NOTIFIED; ?></strong></td>
            <td class="smallText" align="center"><strong><?php echo TABLE_HEADING_STATUS; ?></strong></td>
            <td class="smallText" align="center"><strong><?php echo TABLE_HEADING_COMMENTS; ?></strong></td>
          </tr>
<?php
    $orders_history_query = tep_db_query("select orders_status_id, date_added, customer_notified, comments from " . TABLE_ORDERS_STATUS_HISTORY . " where orders_id = '" . tep_db_input($oID) . "' order by date_added");
    if (tep_db_num_rows($orders_history_query)) {
      while ($orders_history = tep_db_fetch_array($orders_history_query)) {
        echo '          <tr>' . "\n" .
             '            <td class="smallText" align="center">' . tep_datetime_short($orders_history['date_added']) . '</td>' . "\n" .
             '            <td class="smallText" align="center">';
        if ($orders_history['customer_notified'] == '1') {
          echo tep_image(DIR_WS_ICONS . 'tick.gif', ICON_TICK) . "</td>\n";
        } else {
          echo tep_image(DIR_WS_ICONS . 'cross.gif', ICON_CROSS) . "</td>\n";
        }
        echo '            <td class="smallText">' . $orders_status_array[$orders_history['orders_status_id']] . '</td>' . "\n" .
             '            <td class="smallText">' . nl2br(tep_db_output($orders_history['comments'])) . '&nbsp;</td>' . "\n" .
             '          </tr>' . "\n";
      }
    } else {
        echo '          <tr>' . "\n" .
             '            <td class="smallText" colspan="5">' . TEXT_NO_ORDER_HISTORY . '</td>' . "\n" .
             '          </tr>' . "\n";
    }
?>
        </table></td>
      </tr>
      <tr>
        <td class="main"><br /><strong><?php echo TABLE_HEADING_COMMENTS; ?></strong></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
      </tr>
      <tr><?php echo tep_draw_form('status', FILENAME_ORDERS, tep_get_all_get_params(array('action')) . 'action=update_order'); ?>
        <td class="main"><?php echo tep_draw_textarea_field('comments', 'soft', '60', '5'); ?></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td><table border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td><table border="0" cellspacing="0" cellpadding="2">
              <tr>
                <td class="main"><strong><?php echo ENTRY_STATUS; ?></strong> <?php echo tep_draw_pull_down_menu('status', $orders_statuses, $order->info['orders_status']); ?></td>
              </tr>
              <tr>
                <td class="main"><strong><?php echo ENTRY_NOTIFY_CUSTOMER; ?></strong> <?php echo tep_draw_checkbox_field('notify', '', true); ?></td>
                <td class="main"><strong><?php echo ENTRY_NOTIFY_COMMENTS; ?></strong> <?php echo tep_draw_checkbox_field('notify_comments', '', true); ?></td>
              </tr>
            </table></td>
            <td class="smallText" valign="top"><?php echo tep_draw_button(IMAGE_UPDATE, 'disk', null, 'primary'); ?></td>
         	<!-- googlecheckout Tracking Number -->
<?php 
// orders_status == STATE_PROCESSING -> Processing before delivery

	if(strpos($order->info['payment_method'], 'Google')!= -1 &&  $order->info['orders_status'] == GC_STATE_PROCESSING){
			echo '<td><table border="0" cellpadding="3" cellspacing="0"  width="100%">   
				<tbody>
					<tr>  
						<td style="border-top: 2px solid rgb(255,  255, 255); border-right: 2px solid rgb(255, 255, 255);" nowrap="nowrap" colspan="2">
								<b>Shipping Information</b>  
						</td>  
					</tr>
					<tr>  
						<td nowrap="nowrap" valign="middle"  width="1%">  
							<font size="2">  
								<b>Tracking:</b>  
							</font>  
						</td>  
						<td style="border-right: 2px solid rgb(255,  255, 255); border-bottom: 2px solid rgb(255, 255, 255);" nowrap="nowrap">   
							<input name="tracking_number"  style="color: rgb(0, 0, 0);" id="trackingBox" size="20" type="text">   
						</td>  
					</tr>  
					<tr>  
						<td nowrap="nowrap" valign="middle"  width="1%">  
							<font size="2">  
								<b>Carrier:</b>  
							</font>  
						</td>  
						<td style="border-right: 2px solid rgb(255,  255, 255);" nowrap="nowrap">  
							<select name="carrier_select"  style="color: rgb(0, 0, 0);" id="carrierSelect">  
								<option value="select"  selected="selected">
								 Select ...  
								</option>   
								<option value="USPS">
								 USPS  
								</option>   
								<option value="DHL">
								 DHL  
								</option>   
								<option value="UPS">
								 UPS  
								</option>   
								<option value="Other">
								 Other  
								</option>   
								<option value="FedEx">
								 FedEx  
								</option>   
							</select>  
						</td>  
					</tr>     
				</tbody> 
			</table></td>';
	  
	}
?>
<!-- end googlecheckout Tracking Number -->
         	
          </tr>
        </table></td>
      </form></tr>
<?php
  } else {
?>
      <tr>
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
                        <!-- PWA BOF -->
            <td class="pageHeading"><?php echo HEADING_TITLE . (($order->customer['is_dummy_account'])? ' <b>no account!</b>':'').TEXT_HELP_INSTURCTIONS.TEXT_HELP_VIDEO; ?></td>
            <!-- PWA EOF -->
            <td class="pageHeading" align="right"><?php echo tep_draw_separator('pixel_trans.gif', 1, HEADING_IMAGE_HEIGHT); ?></td>
            <td align="right"><table border="0" width="100%" cellspacing="0" cellpadding="0">
              <tr><?php echo tep_draw_form('orders', FILENAME_ORDERS, '', 'post'); ?>
                <td class="smallText" align="right"><?php echo HEADING_TITLE_SEARCH . ' ' . tep_draw_input_field('oID', '', 'size="12"') . tep_draw_hidden_field('action', 'edit'); ?></td>
              <?php echo tep_hide_session_id(); ?></form></tr>
              <tr><?php echo tep_draw_form('status', FILENAME_ORDERS, '', 'post'); ?>
                <td class="smallText" align="right"><?php echo HEADING_TITLE_STATUS . ' ' . tep_draw_pull_down_menu('status', array_merge(array(array('id' => '', 'text' => TEXT_ALL_ORDERS)), $orders_statuses), '', 'onchange="this.form.submit();"'); ?></td>
              <?php echo tep_hide_session_id(); ?></form></tr>
            </table></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CUSTOMERS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ORDER_TOTAL; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_DATE_PURCHASED; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_STATUS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
    if (isset($HTTP_GET_VARS['cID'])) {
      $cID = tep_db_prepare_input($HTTP_GET_VARS['cID']);
      $orders_query_raw = "select o.orders_id, o.customers_name, o.customers_id, o.payment_method, o.date_purchased, o.last_modified, o.currency, o.currency_value, s.orders_status_name, ot.text as order_total from " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id), " . TABLE_ORDERS_STATUS . " s where o.customers_id = '" . (int)$cID . "' and o.orders_status = s.orders_status_id and s.language_id = '" . (int)$languages_id . "' and ot.class = 'ot_total' order by orders_id DESC";
    } elseif (isset($HTTP_GET_VARS['status']) && is_numeric($HTTP_GET_VARS['status']) && ($HTTP_GET_VARS['status'] > 0)) {
      $status = tep_db_prepare_input($HTTP_GET_VARS['status']);
      $orders_query_raw = "select o.orders_id, o.customers_name, o.payment_method, o.date_purchased, o.last_modified, o.currency, o.currency_value, s.orders_status_name, ot.text as order_total from " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id), " . TABLE_ORDERS_STATUS . " s where o.orders_status = s.orders_status_id and s.language_id = '" . (int)$languages_id . "' and s.orders_status_id = '" . (int)$status . "' and ot.class = 'ot_total' order by o.orders_id DESC";
    } else {
      $orders_query_raw = "select o.orders_id, o.customers_name, o.payment_method, o.date_purchased, o.last_modified, o.currency, o.currency_value, s.orders_status_name, ot.text as order_total from " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id), " . TABLE_ORDERS_STATUS . " s where o.orders_status = s.orders_status_id and s.language_id = '" . (int)$languages_id . "' and ot.class = 'ot_total' order by o.orders_id DESC";
    }
    $orders_split = new splitPageResults($HTTP_GET_VARS['pages'], MAX_DISPLAY_SEARCH_RESULTS, $orders_query_raw, $orders_query_numrows);
    $orders_query = tep_db_query($orders_query_raw);
    while ($orders = tep_db_fetch_array($orders_query)) {
    if ((!isset($HTTP_GET_VARS['oID']) || (isset($HTTP_GET_VARS['oID']) && ($HTTP_GET_VARS['oID'] == $orders['orders_id']))) && !isset($oInfo)) {
        $oInfo = new objectInfo($orders);
      }

      if (isset($oInfo) && is_object($oInfo) && ($orders['orders_id'] == $oInfo->orders_id)) {
        echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id . '&action=edit') . '\'">' . "\n";
      } else {
        echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('oID')) . 'oID=' . $orders['orders_id']) . '\'">' . "\n";
      }
?>
                <td class="dataTableContent"><?php echo '<a href="' . tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $orders['orders_id'] . '&action=edit') . '">' . tep_image(DIR_WS_ICONS . 'preview.gif', ICON_PREVIEW) . '</a>&nbsp;' . $orders['customers_name']; ?></td>
                <td class="dataTableContent" align="right"><?php echo strip_tags($orders['order_total']); ?></td>
                <td class="dataTableContent" align="center"><?php echo tep_datetime_short($orders['date_purchased']); ?></td>
                <td class="dataTableContent" align="right"><?php echo $orders['orders_status_name']; ?></td>
                <td class="dataTableContent" align="right"><?php if (isset($oInfo) && is_object($oInfo) && ($orders['orders_id'] == $oInfo->orders_id)) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('oID')) . 'oID=' . $orders['orders_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
    }
?>
              <tr>
                <td colspan="5"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $orders_split->display_count($orders_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $HTTP_GET_VARS['pages'], TEXT_DISPLAY_NUMBER_OF_ORDERS); ?></td>
                    <td class="smallText" align="right"><?php echo $orders_split->display_links($orders_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $HTTP_GET_VARS['pages'], tep_get_all_get_params(array('pages', 'oID', 'action')),'pages'); ?></td>
                  </tr>
                </table></td>
              </tr>
            </table></td>
<?php
  $heading = array();
  $contents = array();

  switch ($action) {
    case 'delete':
      $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_DELETE_ORDER . '</strong>');

      $contents = array('form' => tep_draw_form('orders', FILENAME_ORDERS, tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id . '&action=deleteconfirm'));
      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO . '<br /><br /><strong>' . $cInfo->customers_firstname . ' ' . $cInfo->customers_lastname . '</strong>');
      $contents[] = array('text' => '<br />' . tep_draw_checkbox_field('restock') . ' ' . TEXT_INFO_RESTOCK_PRODUCT_QUANTITY);
      $contents[] = array('align' => 'center', 'text' => '<br />' . tep_draw_button(IMAGE_DELETE, 'trash', null, 'primary') . tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id)));
      break;
    default:
      if (isset($oInfo) && is_object($oInfo)) {
        $heading[] = array('text' => '<strong>[' . $oInfo->orders_id . ']&nbsp;&nbsp;' . tep_datetime_short($oInfo->date_purchased) . '</strong>');

        $contents[] = array('align' => 'center', 'text' => tep_draw_button(IMAGE_EDIT, 'document', tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id . '&action=edit')) . tep_draw_button(IMAGE_DELETE, 'trash', tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id . '&action=delete')));



        $contents[] = array('align' => 'center', 'text' => tep_draw_button(IMAGE_ORDERS_INVOICE, 'document', tep_href_link(FILENAME_ORDERS_INVOICE, 'oID=' . $oInfo->orders_id), null, array('newwindow' => true)) . tep_draw_button(IMAGE_ORDERS_PACKINGSLIP, 'document', tep_href_link(FILENAME_ORDERS_PACKINGSLIP, 'oID=' . $oInfo->orders_id), null, array('newwindow' => true)). tep_draw_button(IMAGE_ORDERS_EDIT, 'document', tep_href_link(FILENAME_ORDERS_EDIT, 'oID=' . $oInfo->orders_id), null, array('newwindow' => true)));




        $contents[] = array('text' => '<br />' . TEXT_DATE_ORDER_CREATED . ' ' . tep_date_short($oInfo->date_purchased));
        if (tep_not_null($oInfo->last_modified)) $contents[] = array('text' => TEXT_DATE_ORDER_LAST_MODIFIED . ' ' . tep_date_short($oInfo->last_modified));
        $contents[] = array('text' => '<br />' . TEXT_INFO_PAYMENT_METHOD . ' '  . $oInfo->payment_method);
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
<?php
  }
?>
    </table>

<?php
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>

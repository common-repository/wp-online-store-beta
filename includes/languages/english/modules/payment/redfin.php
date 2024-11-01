<?php
/*
  $Id: redfin.php 12/19/2008 09:47:00 Colt Taylor Exp $

  This is a modified version of the standard payment module language file
  which was created by Jason LeBaron (jason@networkdad.com)

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2008 osCommerce

  Released under the GNU General Public License
*/

// Admin Configuration Items

  define('MODULE_PAYMENT_REDFIN_TEXT_ADMIN_TITLE', 'RedFin Network'); // Payment option title as displayed in the admin
  define('MODULE_PAYMENT_REDFIN_TEXT_DESCRIPTION', 'Testing is handled using a dedicated testing account username and password<br />with real credit card numbers, expiration dates and cvv values.<br />Contact Redfin Network at 1-866-834-9576 for further information<br /><br />');

  // Catalog Items

  define('MODULE_PAYMENT_REDFIN_TEXT_CATALOG_TITLE', 'Credit Card');  // Payment option title as displayed to the customer
  define('MODULE_PAYMENT_REDFIN_TEXT_CREDIT_CARD_TYPE', 'Credit Card Type:');
  define('MODULE_PAYMENT_REDFIN_TEXT_CREDIT_CARD_OWNER', 'Credit Card Owner:');
  define('MODULE_PAYMENT_REDFIN_TEXT_CREDIT_CARD_NUMBER', 'Credit Card Number:');
  define('MODULE_PAYMENT_REDFIN_TEXT_CREDIT_CARD_EXPIRES', 'Credit Card Expiry Date:');
  //define('MODULE_PAYMENT_REDFIN_TEXT_CVV', 'CVV Number <a href="javascript:newwindow()"><u>More Info</u></a>');
  define('MODULE_PAYMENT_REDFIN_TEXT_CVV', 'CVV Number <a onClick="javascript:window.open(\'cvv_help.php\',\'jav\',\'width=500,height=550,resizable=no,toolbar=no,menubar=no,status=no\');"><u>More Info</u></a>');
  define('MODULE_PAYMENT_REDFIN_TEXT_JS_CC_OWNER', '* The owner\'s name of the credit card must be at least ' . CC_OWNER_MIN_LENGTH . ' characters.\n');
  define('MODULE_PAYMENT_REDFIN_TEXT_JS_CC_NUMBER', '* The credit card number must be at least ' . CC_NUMBER_MIN_LENGTH . ' characters.\n');
  define('MODULE_PAYMENT_REDFIN_TEXT_JS_CC_CVV', '* The 3 or 4 digit CVV number must be entered from the back of the credit card.\n');
  define('MODULE_PAYMENT_REDFIN_TEXT_DECLINED_MESSAGE', 'Your credit card could not be authorized for this reason. Please correct any information and try again or contact us for further assistance.');
  define('MODULE_PAYMENT_REDFIN_TEXT_ERROR', 'Credit Card Error!');
?>
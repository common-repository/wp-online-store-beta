<?php
/*
  $Id: banktransfer.php,v 1.3 2002/05/31 19:02:02 thomasamoulton Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2002 osCommerce

  Released under the GNU General Public License
*/

  define('MODULE_PAYMENT_BANKTRANSFER_TEXT_TITLE', 'Bank Transfer Payment');
  define('MODULE_PAYMENT_BANKTRANSFER_TEXT_DESCRIPTION', 'Please use the following details to transfer your total order value:<br>
<table border="1" cellspacing="0" cellpadding="2">
<tr><td class="main">Account Name</td><td class="main">' . MODULE_PAYMENT_BANKTRANSFER_ACCNAM . '</td></tr>
<tr><td class="main">Bank Name</td><td class="main">' . MODULE_PAYMENT_BANKTRANSFER_BANKNAM . '</td></tr>
<tr><td class="main">Account Number</td><td class="main">' . MODULE_PAYMENT_BANKTRANSFER_ACCNUM . '</td></tr>
<tr><td class="main">Routing Number</td><td class="main">' . MODULE_PAYMENT_BANKTRANSFER_SORTCODE . '</td></tr>
<tr><td class="main">IBAN Number</td><td class="main">' . MODULE_PAYMENT_BANKTRANSFER_IBAN . '</td></tr>
<tr><td class="main">SWIFT Number</td><td class="main">' . MODULE_PAYMENT_BANKTRANSFER_SWIFT . '</td></tr>
</table>
<br>We will not ship your order until we receive payment in the above account.');
  define('MODULE_PAYMENT_BANKTRANSFER_TEXT_EMAIL_FOOTER', "Please use the following details to transfer your total order value:\n\nAccount No.:  " . MODULE_PAYMENT_BANKTRANSFER_ACCNUM . "\nSort Code:    " . MODULE_PAYMENT_BANKTRANSFER_SORTCODE . "\nAccount Name: " . MODULE_PAYMENT_BANKTRANSFER_ACCNAM . "\nBank Name:    " . MODULE_PAYMENT_BANKTRANSFER_BANKNAM . "\nIBAN number:    " . MODULE_PAYMENT_BANKTRANSFER_IBAN . "\nSWIFT number:    " . MODULE_PAYMENT_BANKTRANSFER_SWIFT ."\n\nYour order will not ship until we receive payments in the above account.");
  define('MODULE_PAYMENT_BANKTRANSFER_SORT_ORDER', 'Sort order of display');
define('MODULE_PAYMENT_BANKTRANSFER_ORDER_STATUS_ID', 'Set the order status');
?>

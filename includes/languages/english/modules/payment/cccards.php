<?php
define('CCCARDS_TEXT_CARD_START_DATE_IF_ON_CARD', 'Card Start Date (If on card):');
define('CCCARDS_TEXT_CARD_START_DATE', 'Card Start Date:');
define('CCCARDS_TEXT_CARD_ISSUE_NUMBER_IF_ON_CARD', 'Card Issue No. (If on card):');
define('CCCARDS_TEXT_CARD_ISSUE_NUMBER', 'Card Issue No.:');
define('CCCARDS_TEXT_CARDS_ACCEPTED', 'Cards Accepted:');
define('CCCARDS_TEXT_CARD_TYPE', 'Card Type:');
define('CCCARDS_TEXT_CARD_HOLDER', 'Card Holder Name:');
define('CCCARDS_TEXT_CARD_NUMBER', 'Card Number:');
define('CCCARDS_TEXT_CARD_EXPIRY_DATE', 'Card Expiry Date:');
define('CCCARDS_TEXT_CATALOG_TITLE', 'Offline transactions (collect payment details)');
define('CCCARDS_TEXT_CARD_CV2_NUMBER', 'Card CV2 Number:');
define('CCCARDS_TEXT_CARD_CV2_NUMBER_WITH_POPUP_LINK', 'CV2 Number');
define('CCCARDS_TEXT_CARD_CV2_NUMBER_TICK_NOT_PRESENT', 'Tick here if card has no CV2 number.');
define('CCCARDS_TEXT_CARD_CV2_NUMBER_NOT_PRESENT', 'Not present');


define('CCCARDS_ERROR_CARD_HOLDER_REQUIRED', '<span class="ErrorInfo">You must enter the card holder\'s name.</span>');
define('CCCARDS_ERROR_CARD_HOLDER_MIN_LENGTH', '<span class="ErrorInfo">The card holder\'s name is too short.</span>');
define('CCCARDS_ERROR_CARD_TYPE', '<span class="ErrorInfo">You must select the type of credit/debit card being used.</span>');
define('CCCARDS_ERROR_CARD_NUMBER_REQUIRED', '<span class="ErrorInfo">You must enter the card number.</span>');
define('CCCARDS_ERROR_CARD_NUMBER_INVALID', '<span class="ErrorInfo">The card number entered is invalid. Please check the number and try again, try another card or contact us for further assistance.</span>');
define('CCCARDS_ERROR_CARD_EXPIRY_DATE_INVALID', '<span class="ErrorInfo">The expiry date selected is invalid.</span>');
define('CCCARDS_ERROR_CARD_CV2_NUMBER_REQUIRED', '<span class="ErrorInfo">You must enter the 3 or 4 digit CV2 number from the back of the card.</span>');
define('CCCARDS_ERROR_CARD_CV2_NUMBER_MISSING_INDICATE', '<span class="ErrorInfo">A CV2 number has not been entered. Please enter the 3 or 4 digit CV2 number from the back of the card or indicate if the card has no CV2 number.</span>');
define('CCCARDS_ERROR_CARD_CV2_NUMBER_INVALID', '<span class="ErrorInfo">The CV2 number entered is invalid.</span>');
define('CCCARDS_ERROR_CARD_START_DATE_INVALID', '<span class="ErrorInfo">The start date selected is invalid. If the card doesn\'t have a start date, please change the selection to Month - Year.</span>');




define('CCCARDS_TEXT_SELECT_CARD_TYPE', 'Select Card Type');

define('CCCARDS_TEXT_SELECT_MONTH', 'Month');
define('CCCARDS_TEXT_SELECT_YEAR', 'Year');

define('CCCARDS_TEXT_VISA', 'Visa');
define('CCCARDS_TEXT_MASTERCARD', 'MasterCard');
define('CCCARDS_TEXT_VISA_DEBIT', 'Visa Debit');
define('CCCARDS_TEXT_MASTERCARD_DEBIT', 'MasterCard Debit');
define('CCCARDS_TEXT_MAESTRO', 'Maestro');
define('CCCARDS_TEXT_VISA_ELECTRON', 'Visa Electron (UKE)');
define('CCCARDS_TEXT_AMERICAN_EXPRESS', 'American Express');
define('CCCARDS_TEXT_DINERS_CLUB', 'Diners Club');
define('CCCARDS_TEXT_JCB', 'JCB');
define('CCCARDS_TEXT_LASER', 'Laser');
define('CCCARDS_TEXT_DISCOVER', 'Discover');

 
define('CCCARDS_TEXT_EMAIL_SUBJECT', 'Extra Card Information for Order #');
define('CCCARDS_TEXT_EMAIL' , "Here are the middle digits of the card number for order #%s:\n\nMiddle Digits: %s\n\nAnd here is the CV2 number:\n\nCV2 Number: %s\n\nYOU MUST NOT STORE THE CV2 NUMBER... DELETE THIS E-MAIL ONCE YOU'VE CHARGED THE CARD!\n\n");
define('CCCARDS_TEXT_EMAIL_CV2_NUMBER_NOT_PRESENT' , "Here are the middle digits of the card number for order #%s:\n\nMiddle Digits: %s\n\nThe customer indicated that their card has no CV2 number.\n\nYOU SHOULD NOT STORE THE CARD NUMBER... DELETE THIS E-MAIL ONCE YOU'VE CHARGED THE CARD!\n\n");
define('CCCARDS_TEXT_EMAIL_CV2_NUMBER_NOT_REQUESTED' , "Here are the middle digits of the card number for order #%s:\n\nMiddle Digits: %s\n\n...YOU SHOULD NOT STORE THE CARD NUMBER... DELETE THIS E-MAIL ONCE YOU'VE CHARGED THE CARD!\n\n");



define('CCCARDS_ADMIN_TEXT_TITLE', 'Extra Information provided by Credit Card module');

define('CCCARDS_ADMIN_TEXT_EMAIL_NOTICE', 'The middle digits of the above card number and any CV2 number for the card have been e-mailed to the address specified in the module configuration.');
define('CCCARDS_ADMIN_TEXT_NO_START_DATE_OR_ISSUE_NUMBER', 'No start date was selected and no issue number was entered.').
define('CCCARDS_ADMIN_TEXT_START_DATE', 'Card Start Date:');
define('CCCARDS_ADMIN_TEXT_ISSUE_NUMBER', 'Card Issue Number:');
 
 
define('CCCARDS_ERROR_JS_CARD_HOLDER_MIN_LENGTH', '* The card holder\'s name must be at least ' . (is_numeric(CC_OWNER_MIN_LENGTH) ? CC_OWNER_MIN_LENGTH : 2) . ' characters.\n');
define('CCCARDS_ERROR_JS_CARD_TYPE', '* You must select the type of credit/debit card being used.\n');
define('CCCARDS_ERROR_JS_CARD_NUMBER_MIN_LENGTH', '* The card number must be at least ' . (is_numeric(CC_NUMBER_MIN_LENGTH) ?  CC_NUMBER_MIN_LENGTH : 16) . ' digits in length.\n');
define('CCCARDS_ERROR_JS_CARD_EXPIRY_DATE_INVALID', '* The expiry date selected is invalid.\n');
define('CCCARDS_ERROR_JS_CARD_CV2_NUMBER_INVALID', '* A valid CV2 number has not been entered.\n--> Please enter the 3 or 4 digit CV2 number from the back of the card.\n');
define('CCCARDS_ERROR_JS_CARD_CV2_NUMBER_INVALID_INDICATE', '* A valid CV2 number has not been entered.\n--> Please enter the 3 or 4 digit CV2 number from the back of the card\n--> or indicate if the card has no CV2 number.\n');
define('CCCARDS_ERROR_JS_CARD_START_DATE_INVALID', '* The start date selected is invalid.\n--> If the card doesn\'t have a start date, please change the selection to \"Month\" - \"Year\".\n');

 
define('CCCARDS_TEXT_ADMIN_TITLE', 'Credit Card v%s');
define('CCCARDS_TEXT_DESCRIPTION', '<fieldset style="background: #F7F6F0; margin-bottom: 1.5em"><legend style="font-size: 1.2em; font-weight: bold">Test Card Details</legend>A valid Credit/Debit Card Number must be used (e.g. 4111111111111111).<br /><br />Any future date can be used for the Expiry Date and any 3 or 4 (AMEX) digit number can be used for the CV2 Number.<br /><br />Maestro can optionally use a Start Date and/or Issue Number.<br /><br />American Express cards normally have and require a Start Date (although this module does not enforce its selection).');
define('CCCARDS_TEXT_NOT_INSTALLED', '');
 

define('CCCARDS_CUSTOM_SURCHARGES_DISCOUNTS_MESSAGE', '');

 
define('CCCARDS_TEXT_SURCHARGE_SHORT', 'Surcharge');
define('CCCARDS_TEXT_SURCHARGE_LONG', 'Card Surcharge');
define('CCCARDS_TEXT_DISCOUNT_SHORT', 'Discount');
define('CCCARDS_TEXT_DISCOUNT_LONG', 'Card Discount');

?>

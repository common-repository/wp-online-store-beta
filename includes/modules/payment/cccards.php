<?php

$cccards_code = 'All right reserved by IMC group'; 

define("TABLE_CCCARDS","softmanual");
define("DIR_WS_TEMPLATE_IMAGES",DIR_WS_IMAGES);
class cccards
{

	var $code = 'cccards';
	
	var $version = '1.0.0';
	
 
	
 
	

	var $_critical_config_problem = false;
	

	var $_config_messages = array();
	

	var $title;
	

	var $description = '';
	

	var $enabled;
	

	var $zone;
	

	var $sort_order;
	

	var $order_status = 0;
	

	var $_card_holder;
	

	var $_card_type;
	

	var $_card_number;
	

	var $_card_expiry;
	

	var $_card_cv2_number;
	

	var $_card_start;

	var $_card_issue_number;
	

	var $_card_number_middle_digits;
	

	function cccards()
	{
		
		global $order, $db;
  
		

		$this->title = CCCARDS_TEXT_CATALOG_TITLE;

		$this->description = CCCARDS_TEXT_DESCRIPTION;
 
                $this->enabled = (( CCCARDS_STATUS == 'True') ? true : false);
		


		
		if (defined('CCCARDS_SORT_ORDER')) {
			$this->sort_order = CCCARDS_SORT_ORDER;
		}
		
		if (defined('CCCARDS_ORDER_STATUS_ID') &&
				(int) CCCARDS_ORDER_STATUS_ID > 0) {
			$this->order_status = CCCARDS_ORDER_STATUS_ID;
		}
		
		if (defined('CCCARDS_ZONE')) {
			$this->zone = (int) CCCARDS_ZONE;
		}
		
		if (is_object($order)) {
			$this->update_status();
		}
	}
	
 
	function javascript_validation()
	{
		$js = '  if (payment_value == "' . $this->code . '") {' . "\n" .
			'    var num_CCCARDS_errors = 0;' . "\n" .
			'    var CCCARDS_error_class = "cccardsFormGadgetError";' . "\n" .
			'    var CCCARDS_card_holder_gadget = document.getElementById(\'cccards-card-holder\');' . "\n" .
			'    var CCCARDS_card_number_gadget = document.getElementById(\'cccards-card-number\');' . "\n" .
			'    var CCCARDS_card_type_gadget = document.getElementById(\'cccards-card-type\');' . "\n" .
			'    var CCCARDS_card_type_gadget_value = CCCARDS_card_type_gadget.options[CCCARDS_card_type_gadget.selectedIndex].value;' . "\n" .
			'    var CCCARDS_card_expiry_month_gadget = document.getElementById(\'cccards-card-expiry-month\');' . "\n" .
			'    var CCCARDS_card_expiry_month_gadget_value = CCCARDS_card_expiry_month_gadget.options[CCCARDS_card_expiry_month_gadget.selectedIndex].value;' . "\n" .
			'    var CCCARDS_card_expiry_year_gadget = document.getElementById(\'cccards-card-expiry-year\');' . "\n" .
			'    var CCCARDS_card_expiry_year_gadget_value = CCCARDS_card_expiry_year_gadget.options[CCCARDS_card_expiry_year_gadget.selectedIndex].value;' . "\n";
		
		if (strtolower(CCCARDS_ASK_FOR_CV2_NUMBER) == 'yes') {
			$js .= '    var CCCARDS_card_cv2_number_gadget = document.getElementById(\'cccards-card-cv2-number\');' . "\n" .
			'    if (document.getElementById(\'cccards-card-cv2-number-not-present\') != undefined) {
			         var CCCARDS_card_cv2_number_not_present_gadget = document.getElementById(\'cccards-card-cv2-number-not-present\');
			     } else {
			         var CCCARDS_card_cv2_number_not_present_gadget = null;
			     }
			';
		}
		
		if ($this->_showStartDate()) {
			$js .= '    var CCCARDS_card_start_month_gadget = document.getElementById(\'cccards-card-start-month\');' . "\n" .
			'    var CCCARDS_card_start_month_gadget_value = CCCARDS_card_start_month_gadget.options[CCCARDS_card_start_month_gadget.selectedIndex].value;' . "\n" .
			'    var CCCARDS_card_start_year_gadget = document.getElementById(\'cccards-card-start-year\');' . "\n" .
			'    var CCCARDS_card_start_year_gadget_value = CCCARDS_card_start_year_gadget.options[CCCARDS_card_start_year_gadget.selectedIndex].value;' . "\n";
		}
		
		$js .= '    if (CCCARDS_card_holder_gadget.value == "" || CCCARDS_card_holder_gadget.value.length < ' .
			(is_numeric(CC_OWNER_MIN_LENGTH) ? CC_OWNER_MIN_LENGTH : 2) . ') {' . "\n" .
			'      num_CCCARDS_errors++;' . "\n" .
			'      error_message = error_message + "' .
			CCCARDS_ERROR_JS_CARD_HOLDER_MIN_LENGTH . '";' . "\n" .
			'      error = 1;' . "\n" .
			'      // Update the form gadget\'s class to give visual feedback to customer' . "\n" .
			'      if (CCCARDS_card_holder_gadget.className.indexOf(CCCARDS_error_class) == -1) {' . "\n" .
			'        CCCARDS_card_holder_gadget.className = CCCARDS_card_holder_gadget.className + " " + CCCARDS_error_class;' . "\n" .
			'      }' . "\n" .
			'    } else {' . "\n" .
			'      // Reset error status if necessary' . "\n" .
			'      CCCARDS_card_holder_gadget.className = CCCARDS_card_holder_gadget.className.replace(CCCARDS_error_class, "");' . "\n" .
			'    }' . "\n" .
			'    if (CCCARDS_card_type_gadget_value == "") {' . "\n" .
			'      num_CCCARDS_errors++;' . "\n" .
			'      error_message = error_message + "' .
			CCCARDS_ERROR_JS_CARD_TYPE . '";' . "\n" .
			'      error = 1;' . "\n" .
			'      // Update the form gadget\'s class to give visual feedback to customer' . "\n" .
			'      if (CCCARDS_card_type_gadget.className.indexOf(CCCARDS_error_class) == -1) {' . "\n" .
			'        CCCARDS_card_type_gadget.className = CCCARDS_card_type_gadget.className + " " + CCCARDS_error_class;' . "\n" .
			'      }' . "\n" .
			'    } else {' . "\n" .
			'      // Reset error status if necessary' . "\n" .
			'      CCCARDS_card_type_gadget.className = CCCARDS_card_type_gadget.className.replace(CCCARDS_error_class, "");' . "\n" .
			'    }' . "\n" .
			'    if (CCCARDS_card_number_gadget.value == "" || CCCARDS_card_number_gadget.value.length < ' .
			(is_numeric(CC_NUMBER_MIN_LENGTH) ?  CC_NUMBER_MIN_LENGTH : 16) . ') {' . "\n" .
			'      num_CCCARDS_errors++;' . "\n" .
			'      error_message = error_message + "' .
			CCCARDS_ERROR_JS_CARD_NUMBER_MIN_LENGTH . '";' . "\n" .
			'      error = 1;' . "\n" .
			'      // Update the form gadget\'s class to give visual feedback to customer' . "\n" .
			'      if (CCCARDS_card_number_gadget.className.indexOf(CCCARDS_error_class) == -1) {' . "\n" .
			'        CCCARDS_card_number_gadget.className = CCCARDS_card_number_gadget.className + " " + CCCARDS_error_class;' . "\n" .
			'      }' . "\n" .
			'    } else {' . "\n" .
			'      // Reset error status if necessary' . "\n" .
			'      CCCARDS_card_number_gadget.className = CCCARDS_card_number_gadget.className.replace(CCCARDS_error_class, "");' . "\n" .
			'    }' . "\n" .
			'    if (CCCARDS_card_expiry_month_gadget_value == "" || CCCARDS_card_expiry_year_gadget_value == "") {' . "\n" .
			'      num_CCCARDS_errors++;' . "\n" .
			'      error_message = error_message + "' .
			CCCARDS_ERROR_JS_CARD_EXPIRY_DATE_INVALID . '";' . "\n" .
			'      error = 1;' . "\n" .
			'    }' . "\n" .
			'    if (CCCARDS_card_expiry_month_gadget_value == "") {' . "\n" .
			'      // Update the form gadget\'s class to give visual feedback to customer' . "\n" .
			'      if (CCCARDS_card_expiry_month_gadget.className.indexOf(CCCARDS_error_class) == -1) {' . "\n" .
			'        CCCARDS_card_expiry_month_gadget.className = CCCARDS_card_expiry_month_gadget.className + " " + CCCARDS_error_class;' . "\n" .
			'      }' . "\n" .
			'    } else {' . "\n" .
			'      // Reset error status if necessary' . "\n" .
			'      CCCARDS_card_expiry_month_gadget.className = CCCARDS_card_expiry_month_gadget.className.replace(CCCARDS_error_class, "");' . "\n" .
			'    }' . "\n" .
			'    if (CCCARDS_card_expiry_year_gadget_value == "") {' . "\n" .
			'      // Update the form gadget\'s class to give visual feedback to customer' . "\n" .
			'      if (CCCARDS_card_expiry_year_gadget.className.indexOf(CCCARDS_error_class) == -1) {' . "\n" .
			'        CCCARDS_card_expiry_year_gadget.className = CCCARDS_card_expiry_year_gadget.className + " " + CCCARDS_error_class;' . "\n" .
			'      }' . "\n" .
			'    } else {' . "\n" .
			'      // Reset error status if necessary' . "\n" .
			'      CCCARDS_card_expiry_year_gadget.className = CCCARDS_card_expiry_year_gadget.className.replace(CCCARDS_error_class, "");' . "\n" .
			'    }' . "\n";
		
		if (strtolower(CCCARDS_ASK_FOR_CV2_NUMBER) == 'yes') {
			if (strtolower(CCCARDS_ALLOW_NO_CV2_NUMBER) == 'yes') {
				$js .= '    if ((CCCARDS_card_cv2_number_not_present_gadget == null &&
					(CCCARDS_card_cv2_number_gadget.value == "" || CCCARDS_card_cv2_number_gadget.value.length < 3 ||
					CCCARDS_card_cv2_number_gadget.value.length > 4)) || 
					(CCCARDS_card_cv2_number_not_present_gadget != null &&
					CCCARDS_card_cv2_number_not_present_gadget.checked == false) && 
					(CCCARDS_card_cv2_number_gadget.value == "" || CCCARDS_card_cv2_number_gadget.value.length < 3 ||
					CCCARDS_card_cv2_number_gadget.value.length > 4)) {' . "\n" .
					'      num_CCCARDS_errors++;' . "\n" .
					'      error_message = error_message + "' .
					CCCARDS_ERROR_JS_CARD_CV2_NUMBER_INVALID_INDICATE . '";' . "\n" .
					'      error = 1;' . "\n" .
					'      // Update the form gadget\'s class to give visual feedback to customer' . "\n" .
					'      if (CCCARDS_card_cv2_number_gadget.className.indexOf(CCCARDS_error_class) == -1) {' . "\n" .
					'        CCCARDS_card_cv2_number_gadget.className = CCCARDS_card_cv2_number_gadget.className + " " + CCCARDS_error_class;' . "\n" .
					'      }' . "\n" .
					'    } else {' . "\n" .
					'      // Reset error status if necessary' . "\n" .
					'      CCCARDS_card_cv2_number_gadget.className = CCCARDS_card_cv2_number_gadget.className.replace(CCCARDS_error_class, "");' . "\n" .
					'    }' . "\n";
			} else {
				$js .= '    if (CCCARDS_card_cv2_number_gadget.value == "" ||
					CCCARDS_card_cv2_number_gadget.value.length < 3 ||
					CCCARDS_card_cv2_number_gadget.value.length > 4) {' . "\n" .
					'      num_CCCARDS_errors++;' . "\n" .
					'      error_message = error_message + "' .
					CCCARDS_ERROR_JS_CARD_CV2_NUMBER_INVALID . '";' . "\n" .
					'      error = 1;' . "\n" .
					'      // Update the form gadget\'s class to give visual feedback to customer' . "\n" .
					'      if (CCCARDS_card_cv2_number_gadget.className.indexOf(CCCARDS_error_class) == -1) {' . "\n" .
					'        CCCARDS_card_cv2_number_gadget.className = CCCARDS_card_cv2_number_gadget.className + " " + CCCARDS_error_class;' . "\n" .
					'      }' . "\n" .
					'    } else {' . "\n" .
					'      // Reset error status if necessary' . "\n" .
					'      CCCARDS_card_cv2_number_gadget.className = CCCARDS_card_cv2_number_gadget.className.replace(CCCARDS_error_class, "");' . "\n" .
					'    }' . "\n";
			}
		}
		
		if ($this->_showStartDate()) {
			$js .=
			'    if ((CCCARDS_card_start_month_gadget_value == "" && CCCARDS_card_start_year_gadget_value != "")' . "\n" .
			'       || (CCCARDS_card_start_month_gadget_value != "" && CCCARDS_card_start_year_gadget_value == "")) {' . "\n" .
			'        num_CCCARDS_errors++;' . "\n" .
			'        error_message = error_message + "' .
			CCCARDS_ERROR_JS_CARD_START_DATE_INVALID . '";' . "\n" .
			'        error = 1;' . "\n" .
			'        if (CCCARDS_card_start_month_gadget_value == "") {' . "\n" .
			'          // Update the form gadget\'s class to give visual feedback to customer' . "\n" .
			'          if (CCCARDS_card_start_month_gadget.className.indexOf(CCCARDS_error_class) == -1) {' . "\n" .
			'            CCCARDS_card_start_month_gadget.className = CCCARDS_card_start_month_gadget.className + " " + CCCARDS_error_class;' . "\n" .
			'          }' . "\n" .
			'        } else {' . "\n" .
			'          // Reset error status if necessary' . "\n" .
			'          CCCARDS_card_start_month_gadget.className = CCCARDS_card_start_month_gadget.className.replace(CCCARDS_error_class, "");' . "\n" .
			'        }' . "\n" .
			'        if (CCCARDS_card_start_year_gadget_value == "") {' . "\n" .
			'          // Update the form gadget\'s class to give visual feedback to customer' . "\n" .
			'          if (CCCARDS_card_start_year_gadget.className.indexOf(CCCARDS_error_class) == -1) {' . "\n" .
			'            CCCARDS_card_start_year_gadget.className = CCCARDS_card_start_year_gadget.className + " " + CCCARDS_error_class;' . "\n" .
			'          }' . "\n" .
			'        } else {' . "\n" .
			'          // Reset error status if necessary' . "\n" .
			'          CCCARDS_card_start_year_gadget.className = CCCARDS_card_start_year_gadget.className.replace(CCCARDS_error_class, "");' . "\n" .
			'        }' . "\n" .
			'    } else {' . "\n" .
			'        // Make sure that, if customer hasn\'t used either start date field, they aren\'t marked as having an error' . "\n" .
			'        CCCARDS_card_start_month_gadget.className = CCCARDS_card_start_month_gadget.className.replace(CCCARDS_error_class, "");' . "\n" .
			'        CCCARDS_card_start_year_gadget.className = CCCARDS_card_start_year_gadget.className.replace(CCCARDS_error_class, "");' . "\n" .
			'    }' . "\n";
		}
		
		if (strtolower(CCCARDS_ASK_FOR_CV2_NUMBER) == 'yes' &&
				strtolower(CCCARDS_ALLOW_NO_CV2_NUMBER) == 'yes') {
			$js .= '    if (CCCARDS_card_cv2_number_not_present_gadget == null &&' . "\n" .
			'         CCCARDS_card_cv2_number_gadget.value == "") {' . "\n" .
			'       parent_el = CCCARDS_card_cv2_number_gadget.parentNode;
					try {
						wrapper_div_el = document.createElement("<div>");
					} catch (e) {
						wrapper_div_el = document.createElement("div");
					}
					parent_el.insertBefore(wrapper_div_el,CCCARDS_card_cv2_number_gadget);
					wrapper_div_el.appendChild(CCCARDS_card_cv2_number_gadget);
					try {
						br_el = document.createElement("<br>");
					} catch (e) {
						br_el = document.createElement("br");
					}
					wrapper_div_el.appendChild(br_el);
					try {
						cv2_number_not_present_checkbox_el = document.createElement(\'<input name="cccards-card-cv2-number-not-present" id="cccards-card-cv2-number-not-present" type="checkbox" value="true" />\');
					} catch (e) {
						cv2_number_not_present_checkbox_el = document.createElement(\'input\');
						cv2_number_not_present_checkbox_el.setAttribute(\'id\', \'cccards-card-cv2-number-not-present\');
						cv2_number_not_present_checkbox_el.setAttribute(\'Name\', \'cccards-card-cv2-number-not-present\');
						cv2_number_not_present_checkbox_el.setAttribute(\'type\', \'checkbox\');
						cv2_number_not_present_checkbox_el.setAttribute(\'value\', \'true\');
					}
					wrapper_div_el.appendChild(cv2_number_not_present_checkbox_el);
					new_text_node_el = document.createTextNode(\' ' .
					addslashes(CCCARDS_TEXT_CARD_CV2_NUMBER_TICK_NOT_PRESENT) . '\');
					wrapper_div_el.appendChild(new_text_node_el);' . "\n" .
			'    }' . "\n";
		}
		
		$js .= '  }' . "\n";
		
		return $js;
	}
	
 
	function selection()
	{
		global $order;
		
 
		$error_encountered = false;
		
		if (isset($_SESSION['CCCARDS_error_encountered'])) {
			$error_encountered = true;
		}
		 
		$today = getdate();
		
		$expiry_month_options[] = array(
			'id' => '',
			'text' => CCCARDS_TEXT_SELECT_MONTH
			);
		
		for ($i = 1; $i < 13; $i++) {
			$expiry_month_options[] = array(
				'id' => sprintf('%02d', $i),
				'text' => strftime(CCCARDS_SELECT_MONTH_FORMAT,
					mktime(0, 0, 0, $i, 1, 2000))
				);
		}
		 
		$expiry_year_options[] = array(
			'id' => '',
			'text' => CCCARDS_TEXT_SELECT_YEAR
			);
		
		for ($i = $today['year']; $i < $today['year'] + 10; $i++) {
			$expiry_year_options[] = array(
				'id' => strftime('%Y', mktime(0, 0, 0, 1, 1, $i)),
				'text' => strftime(CCCARDS_SELECT_YEAR_FORMAT,
					mktime(0, 0, 0, 1, 1, $i))
				);
		}
		
		$start_month_options[] = array(
			'id' => '',
			'text' => CCCARDS_TEXT_SELECT_MONTH
			);
		
		for ($i = 1; $i < 13; $i++) {
			$start_month_options[] = array(
				'id' => sprintf('%02d', $i),
				'text' => strftime(CCCARDS_SELECT_MONTH_FORMAT,
					mktime(0, 0, 0, $i, 1, 2000))
				);
		}
		 
		$start_year_options[] = array(
			'id' => '',
			'text' => CCCARDS_TEXT_SELECT_YEAR
			);
		
		for ($i = $today['year'] - 4; $i <= $today['year']; $i++) {
			$start_year_options[] = array(
				'id' => strftime('%Y', mktime(0, 0, 0, 1, 1, $i)),
				'text' => strftime(CCCARDS_SELECT_YEAR_FORMAT,
					mktime(0, 0, 0, 1, 1, $i))
				);
		}
		
 
		$card_type_options[] = array(
			'id' => '',
			'text' => CCCARDS_TEXT_SELECT_CARD_TYPE
			);
		
		if (strtolower(CCCARDS_ACCEPT_VISA) == 'yes') {
			$card_type_options[] = array(
				'id' => 'VISA',
				'text' => $this->_getCardTypeNameForCode('VISA')
				);
		}
		
		if (strtolower(CCCARDS_ACCEPT_MASTERCARD) == 'yes') {
			$card_type_options[] = array(
				'id' => 'MASTERCARD',
				'text' => $this->_getCardTypeNameForCode('MASTERCARD')
				);
		}
		
		if (strtolower(CCCARDS_ACCEPT_VISA_DEBIT) == 'yes') {
			$card_type_options[] = array(
				'id' => 'VISA_DEBIT',
				'text' => $this->_getCardTypeNameForCode('VISA_DEBIT')
				);
		}
		
		if (strtolower(CCCARDS_ACCEPT_MASTERCARD_DEBIT) == 'yes') {
			$card_type_options[] = array(
				'id' => 'MASTERCARD_DEBIT',
				'text' => $this->_getCardTypeNameForCode('MASTERCARD_DEBIT')
				);
		}
		
		if (strtolower(CCCARDS_ACCEPT_MAESTRO) == 'yes') {
			$card_type_options[] = array(
				'id' => 'MAESTRO',
				'text' => $this->_getCardTypeNameForCode('MAESTRO')
				);
		}
		
		if (strtolower(CCCARDS_ACCEPT_VISA_ELECTRON) == 'yes') {
			$card_type_options[] = array(
				'id' => 'VISA_ELECTRON',
				'text' => $this->_getCardTypeNameForCode('VISA_ELECTRON')
				);
		}
		
		if (strtolower(CCCARDS_ACCEPT_AMERICAN_EXPRESS) == 'yes') {
			$card_type_options[] = array(
				'id' => 'AMERICAN_EXPRESS',
				'text' => $this->_getCardTypeNameForCode('AMERICAN_EXPRESS')
				);
		}
		
		if (strtolower(CCCARDS_ACCEPT_DINERS_CLUB) == 'yes') {
			$card_type_options[] = array(
				'id' => 'DINERS_CLUB',
				'text' => $this->_getCardTypeNameForCode('DINERS_CLUB')
				);
		}
		
		if (strtolower(CCCARDS_ACCEPT_JCB) == 'yes') {
			$card_type_options[] = array(
				'id' => 'JCB',
				'text' => $this->_getCardTypeNameForCode('JCB')
				);
		}
		
		if (strtolower(CCCARDS_ACCEPT_LASER) == 'yes' &&
				strtoupper($_SESSION['currency']) == 'EUR') {
 
			$card_type_options[] = array(
				'id' => 'LASER',
				'text' => $this->_getCardTypeNameForCode('LASER')
				);
		}
		
		if (strtolower(CCCARDS_ACCEPT_DISCOVER) == 'yes') {
			$card_type_options[] = array(
				'id' => 'DISCOVER',
				'text' => $this->_getCardTypeNameForCode('DISCOVER')
				);
		}
		 
		$card_holder = $order->billing['firstname'] . ' ' . $order->billing['lastname'];
		$card_type = '';
		$card_number = '';
		$card_expiry_month = '';
		$card_expiry_year = '';
		
		if (strtolower(CCCARDS_ASK_FOR_CV2_NUMBER) == 'yes') {
			$card_cv2_number = '';
			
			if (strtolower(CCCARDS_ALLOW_NO_CV2_NUMBER) == 'yes') {
				$card_cv2_number_not_present = false;
			}
		}
		
		$card_start_month = '';
		$card_start_year = '';
		$card_issue_number = '';
		 
		if (isset($_SESSION['CCCARDS_card_type'])) {
 
			$card_holder = $_SESSION['CCCARDS_card_holder'];
			$card_type = $_SESSION['CCCARDS_card_type'];
			$card_expiry_month = $_SESSION['CCCARDS_card_expiry_month'];
			$card_expiry_year = $_SESSION['CCCARDS_card_expiry_year'];
			
			if (isset($_SESSION['CCCARDS_card_cv2_number_not_present'])) {
				$card_cv2_number_not_present =
					$_SESSION['CCCARDS_card_cv2_number_not_present'];
			}
			
			if (isset($_SESSION['CCCARDS_card_start_month'])) {
				$card_start_month = $_SESSION['CCCARDS_card_start_month'];
				$card_start_year = $_SESSION['CCCARDS_card_start_year'];
			}
			
			if (isset($_SESSION['CCCARDS_card_issue_number'])) {
				$card_issue_number = $_SESSION['CCCARDS_card_issue_number'];
			}
			
			if (strtolower(CCCARDS_STORE_SENSITIVE_DETAILS_IN_SESSION) == 'yes') {
 
				if (!$this->_referredFromCheckoutProcessURI()) {
 
					unset($_SESSION['CCCARDS_data_entered']);
					
				} else if (isset($_SESSION['CCCARDS_data_entered'])) {
 
					require_once(DIR_WS_CLASSES . 'class.SoftBlowfishEncryption.php');
					
					$bf = new SoftBlowfishEncryption(
						substr(CCCARDS_BLOWFISH_ENCRYPTION_KEYPHRASE, 0, 56));
					
						$plaintext = $bf->decrypt($_SESSION['CCCARDS_data_entered']);
					
					$sensitive_data = unserialize(base64_decode($plaintext));
					
					$card_number = $sensitive_data['card_number'];
					
					if (strtolower(CCCARDS_ASK_FOR_CV2_NUMBER) == 'yes') {
						$card_cv2_number = $sensitive_data['card_cv2_number'];
					}
				}
			}
		}
		
		$selection = array(
			'id' => $this->code,
			'module' => $this->title
			);
		 
		if (strtolower(CCCARDS_SHOW_CARDS_ACCEPTED) == 'yes') {
 
			$cards_accepted_images_source = '';
			
			if (strtolower(CCCARDS_ACCEPT_VISA) == 'yes') {
				$cards_accepted_images_source .= tep_image(DIR_WS_TEMPLATE_IMAGES  .
					'card-icons/visa.png', CCCARDS_TEXT_VISA, '', '',
					'class="cccardsCardIcon"');
			}
			
			if (strtolower(CCCARDS_ACCEPT_MASTERCARD) == 'yes' ||
					strtolower(CCCARDS_ACCEPT_MASTERCARD_DEBIT) == 'yes') {
				if (strtolower(CCCARDS_ACCEPT_MASTERCARD) == 'yes' &&
						strtolower(CCCARDS_ACCEPT_MASTERCARD_DEBIT) == 'yes') {
					$alt_text = CCCARDS_TEXT_MASTERCARD . ' / ' .
						CCCARDS_TEXT_MASTERCARD_DEBIT;
				} else if (strtolower(CCCARDS_ACCEPT_MASTERCARD) == 'yes') {
					$alt_text = CCCARDS_TEXT_MASTERCARD;
				} else {
					$alt_text = CCCARDS_TEXT_MASTERCARD_DEBIT;
				}
				
				$cards_accepted_images_source .= tep_image(DIR_WS_TEMPLATE_IMAGES  .
					'card-icons/mastercard.png', $alt_text, '', '',
					'class="cccardsCardIcon"');
			}
			
			if (strtolower(CCCARDS_ACCEPT_VISA_DEBIT) == 'yes') {
				$cards_accepted_images_source .= tep_image(DIR_WS_TEMPLATE_IMAGES  .
					'card-icons/visa-debit.png', CCCARDS_TEXT_VISA_DEBIT, '', '',
					'class="cccardsCardIcon"');
			}
			
			if (strtolower(CCCARDS_ACCEPT_MAESTRO) == 'yes') {
				$cards_accepted_images_source .= tep_image(DIR_WS_TEMPLATE_IMAGES  .
					'card-icons/maestro.png', CCCARDS_TEXT_MAESTRO, '', '',
					'class="cccardsCardIcon"');
			}
			
			if (strtolower(CCCARDS_ACCEPT_VISA_ELECTRON) == 'yes') {
				$cards_accepted_images_source .= tep_image(DIR_WS_TEMPLATE_IMAGES  .
					'card-icons/visa-electron.png', CCCARDS_TEXT_VISA_ELECTRON, '', '',
					'class="cccardsCardIcon"');
			}
			
			if (strtolower(CCCARDS_ACCEPT_AMERICAN_EXPRESS) == 'yes') {
				$cards_accepted_images_source .= tep_image(DIR_WS_TEMPLATE_IMAGES  .
					'card-icons/american-express.png', CCCARDS_TEXT_AMERICAN_EXPRESS, '', 
					'', 'class="cccardsCardIcon"');
			}
			
			if (strtolower(CCCARDS_ACCEPT_DINERS_CLUB) == 'yes') {
				$cards_accepted_images_source .= tep_image(DIR_WS_TEMPLATE_IMAGES  .
					'card-icons/diners-club.png', CCCARDS_TEXT_DINERS_CLUB, '', '',
					'class="cccardsCardIcon"');
			}
			
			if (strtolower(CCCARDS_ACCEPT_JCB) == 'yes') {
				$cards_accepted_images_source .= tep_image(DIR_WS_TEMPLATE_IMAGES  .
					'card-icons/jcb.png', CCCARDS_TEXT_JCB, '', '',
					'class="cccardsCardIcon"');
			}
			
			if (strtolower(CCCARDS_ACCEPT_LASER) == 'yes' &&
					strtoupper($_SESSION['currency']) == 'EUR') {
 
				$cards_accepted_images_source .= tep_image(DIR_WS_TEMPLATE_IMAGES  .
					'card-icons/laser.png', CCCARDS_TEXT_LASER, '', '',
					'class="cccardsCardIcon"');
			}
			
			if (strtolower(CCCARDS_ACCEPT_DISCOVER) == 'yes') {
				$cards_accepted_images_source .= tep_image(DIR_WS_TEMPLATE_IMAGES  .
					'card-icons/discover.png', CCCARDS_TEXT_DISCOVER, '',
					'', 'class="cccardsCardIcon"');
			}
			
			$selection['fields'][] = array(
				'title' => CCCARDS_TEXT_CARDS_ACCEPTED,
				'field' => $cards_accepted_images_source
				);
		}
		
 
		$on_focus_handler = ' onfocus="javascript:selectcccards();"';
		
 
		$js = '<script language="JavaScript" type="text/javascript">' . "\n" .
			'<!--' . "\n" .
			'function selectcccards()' . "\n" .
			'{' . "\n" .
			"	if (document.getElementById('pmt-" . $this->code . "')) {\n" .
			"		document.getElementById('pmt-" . $this->code . "').checked = 'checked';\n" .
			'	}' . "\n" .
			'}' . "\n" .
			'// -->' . "\n" .
			'</script>' . "\n";
		
		$selection['fields'][] = array(
			'title' => CCCARDS_TEXT_CARD_HOLDER,
			'field' => $js . tep_draw_input_field('cccards-card-holder',
				$card_holder, 'id="cccards-card-holder"' . $on_focus_handler)
			);
		 
		if (strtolower(CCCARDS_ENABLE_SURCHARGES_DISCOUNTS) == 'yes' &&
				strtolower(
				CCCARDS_ENABLE_CUSTOM_SURCHARGES_DISCOUNTS_MESSAGE) == 'yes' &&
				defined('CCCARDS_CUSTOM_SURCHARGES_DISCOUNTS_MESSAGE') &&
				strlen(CCCARDS_CUSTOM_SURCHARGES_DISCOUNTS_MESSAGE) > 0) {
			
			$selection['fields'][] = array(
				'title' => '',
				'field' => CCCARDS_CUSTOM_SURCHARGES_DISCOUNTS_MESSAGE
				);
		}
		
		$selection['fields'][] = array(
			'title' => CCCARDS_TEXT_CARD_TYPE,
			'field' => tep_draw_pull_down_menu('cccards-card-type', $card_type_options,
				$card_type, 'id="cccards-card-type"' . $on_focus_handler)
			);
		
		
 
		$card_number_error_class_string = '';
		$card_cv2_number_error_class_string = '';
		
		if ($error_encountered &&
				strtolower(CCCARDS_STORE_SENSITIVE_DETAILS_IN_SESSION) != 'yes') {
			
			$card_number_error_class_string = ' class="cccardsFormGadgetError"';
			
			if (!isset($card_cv2_number_not_present) || !$card_cv2_number_not_present) {
 
				$card_cv2_number_error_class_string = ' class="cccardsFormGadgetError"';
			}
		}
		
		if (strtolower(CCCARDS_DISABLE_CARD_NUMBER_AUTOCOMPLETE) == 'yes') {
			$selection['fields'][] = array(
				'title' => CCCARDS_TEXT_CARD_NUMBER,
				'field' => tep_draw_input_field('cccards-card-number',
					$card_number, 'id="cccards-card-number"' . ' autocomplete="off"' .
					$on_focus_handler . $card_number_error_class_string)
				);
		} else {
			$selection['fields'][] = array(
				'title' => CCCARDS_TEXT_CARD_NUMBER,
				'field' => tep_draw_input_field('cccards-card-number',
					$card_number, 'id="cccards-card-number"' . $on_focus_handler .
					$card_number_error_class_string)
				);
			
		}
		
		$selection['fields'][] = array(
			'title' => CCCARDS_TEXT_CARD_EXPIRY_DATE,
			'field' => tep_draw_pull_down_menu('cccards-card-expiry-month',
				$expiry_month_options, $card_expiry_month,
				'id="cccards-card-expiry-month"' . $on_focus_handler) . '&nbsp;' .
				tep_draw_pull_down_menu('cccards-card-expiry-year',
				$expiry_year_options, $card_expiry_year,
				'id="cccards-card-expiry-year"' . $on_focus_handler)
			);
		
		
		if (strtolower(CCCARDS_ASK_FOR_CV2_NUMBER) == 'yes') {
 
			$cv2_number_not_present_field = '';
			
			if (strtolower(CCCARDS_ALLOW_NO_CV2_NUMBER) == 'yes') {
				if ($card_cv2_number_not_present ||
						(isset($_SESSION['CCCARDS_card_type']) &&
						$card_cv2_number == '')) {
					$cv2_number_not_present_field = '<br />' .
						tep_draw_checkbox_field('cccards-card-cv2-number-not-present',
						'true', $card_cv2_number_not_present,
						'id="cccards-card-cv2-number-not-present"' . $on_focus_handler) .
						'&nbsp;' . CCCARDS_TEXT_CARD_CV2_NUMBER_TICK_NOT_PRESENT;
				}
			}
			
			if (strtolower(CCCARDS_DISABLE_CV2_NUMBER_AUTOCOMPLETE) == 'yes') {
				$selection['fields'][] = array(
					'title' => CCCARDS_TEXT_CARD_CV2_NUMBER_WITH_POPUP_LINK,
					'field' => tep_draw_input_field('cccards-card-cv2-number',
						$card_cv2_number, 'size="4" maxlength="4" autocomplete="off" ' .
						'id="cccards-card-cv2-number"' . $on_focus_handler .
						$card_cv2_number_error_class_string) . $cv2_number_not_present_field
					);
			} else {
				$selection['fields'][] = array(
					'title' => CCCARDS_TEXT_CARD_CV2_NUMBER_WITH_POPUP_LINK,
					'field' => tep_draw_input_field('cccards-card-cv2-number',
						$card_cv2_number, 'size="4" maxlength="4" ' .
						'id="cccards-card-cv2-number"' . $on_focus_handler .
						$card_cv2_number_error_class_string) . $cv2_number_not_present_field
					);
				
			}
		}
		
		if ($this->_showStartDate()) {
			$selection['fields'][] = array(
				'title' => CCCARDS_TEXT_CARD_START_DATE_IF_ON_CARD,
				'field' => tep_draw_pull_down_menu('cccards-card-start-month',
					$start_month_options, $card_start_month,
					'id="cccards-card-start-month"' . $on_focus_handler) . '&nbsp;' .
					tep_draw_pull_down_menu('cccards-card-start-year',
					$start_year_options, $card_start_year,
					'id="cccards-card-start-year"' . $on_focus_handler)
				);
		}
		
		if ($this->_showIssueNumber()) {
			$selection['fields'][] = array(
				'title' => CCCARDS_TEXT_CARD_ISSUE_NUMBER_IF_ON_CARD,
				'field' => tep_draw_input_field('cccards-card-issue-number',
					$card_issue_number,
					'size="2" maxlength="2" id="cccards-card-issue-number"' .
					$on_focus_handler)
				);
		}
		
		return $selection;
	}
	
 
	function pre_confirmation_check()
	{
		global $messageStack;
		 
		unset($_SESSION['CCCARDS_error_encountered']);
		 
		$data_entered = array();
		
		$data_entered['CCCARDS_card_holder'] =
			trim($_POST['cccards-card-holder']);
		
		$data_entered['CCCARDS_card_type'] = $_POST['cccards-card-type'];
		
		$data_entered['CCCARDS_card_number'] =
			preg_replace('/[^0-9]/', '', $_POST['cccards-card-number']);
		
		$data_entered['CCCARDS_card_expiry_month'] =
			$_POST['cccards-card-expiry-month'];
		
		$data_entered['CCCARDS_card_expiry_year'] =
			$_POST['cccards-card-expiry-year'];
		
		if (strtolower(CCCARDS_ASK_FOR_CV2_NUMBER) == 'yes') {
			if (isset($_POST['cccards-card-cv2-number'])) {
				$data_entered['CCCARDS_card_cv2_number'] = 
					preg_replace('/[^0-9]/', '', $_POST['cccards-card-cv2-number']);
			} else {
				$data_entered['CCCARDS_card_cv2_number'] = '';
			}
			
			if (strtolower(CCCARDS_ALLOW_NO_CV2_NUMBER) == 'yes') {
 
				if (isset($_POST['cccards-card-cv2-number-not-present'])) {
					$data_entered['CCCARDS_card_cv2_number_not_present'] = true;
					$data_entered['CCCARDS_card_cv2_number'] = '';
				} else {
					$data_entered['CCCARDS_card_cv2_number_not_present'] = false;
				}
			}
		}
		
		if (isset($_POST['cccards-card-start-year'])) {
			$data_entered['CCCARDS_card_start_month'] =
				$_POST['cccards-card-start-month'];
			
			$data_entered['CCCARDS_card_start_year'] =
				$_POST['cccards-card-start-year'];
		}
		
		if (isset($_POST['cccards-card-issue-number'])) {
			$data_entered['CCCARDS_card_issue_number'] =
				preg_replace('/[^0-9]/', '', $_POST['cccards-card-issue-number']);
		}
		
 
		foreach ($data_entered as $key => $value) {
 
			if ($key != 'CCCARDS_card_number' &&
					$key != 'CCCARDS_card_cv2_number') {
				$_SESSION[$key] = $value;
			}
		}
		 
		if (strtolower(CCCARDS_STORE_SENSITIVE_DETAILS_IN_SESSION) == 'yes') {
 
			$sensitive_data = array(
				'card_number' => $data_entered['CCCARDS_card_number']
				);
			
			if (strtolower(CCCARDS_ASK_FOR_CV2_NUMBER) == 'yes') {
				$sensitive_data['card_cv2_number'] =
					$data_entered['CCCARDS_card_cv2_number'];
			}
			
			$plaintext = base64_encode(serialize($sensitive_data));
			
 
			require_once(DIR_WS_CLASSES . 'class.SoftBlowfishEncryption.php');
			
			$bf = new SoftBlowfishEncryption(
				substr(CCCARDS_BLOWFISH_ENCRYPTION_KEYPHRASE, 0, 56));
			
			$encrypted = $bf->encrypt($plaintext);
			
				$_SESSION['CCCARDS_data_entered'] = $encrypted;
		}
		 
		$errors = array();
		
 
		$card_holder = $data_entered['CCCARDS_card_holder'];
		
		$card_holder_min_length =
			(is_numeric(CC_OWNER_MIN_LENGTH) && CC_OWNER_MIN_LENGTH > 2 ? CC_OWNER_MIN_LENGTH : 2);
		
		if (strlen($card_holder) == 0) {
			$errors[] = CCCARDS_ERROR_CARD_HOLDER_REQUIRED;
		} else if (strlen($card_holder) < $card_holder_min_length) {
			$errors[] = CCCARDS_ERROR_CARD_HOLDER_LENGTH;
		}
		
 
		$card_type = $data_entered['CCCARDS_card_type'];
		
		if ($card_type == '') {
 
			$errors[] = CCCARDS_ERROR_CARD_TYPE;
		}
		 
		$card_number_valid = false;
		
		$card_number = $data_entered['CCCARDS_card_number'];
		
		if (strlen($card_number) == 0) {
 
			$errors[] = CCCARDS_ERROR_CARD_NUMBER_REQUIRED;
		} else {
			$temp_card_number = strrev($card_number);
			
			$numSum = 0;
			
			for ($i = 0; $i < strlen($temp_card_number); $i++) {
				$current_number = substr($temp_card_number, $i, 1);
				
				// Double every second digit
				if ($i % 2 == 1) {
					$current_number *= 2;
				}
				
				// Add digits of 2-digit numbers together
				if ($current_number > 9) {
					$first_number = $current_number % 10;
					$second_number = ($current_number - $first_number) / 10;
					$current_number = $first_number + $second_number;
				}
				
				$numSum += $current_number;
			}
			
			if ($numSum % 10 != 0) {
				$errors[] = CCCARDS_ERROR_CARD_NUMBER_INVALID;
			} else {
				$card_number_valid = true;
			}
		}
		
 
		if ($card_number_valid && ($card_type == 'MASTERCARD' ||
				$card_type == 'MASTERCARD_DEBIT')) {
			
			$card_is_mastercard_debit = $this->_isCardNumberMasterCardDebit($card_number);
			
			if ((!$card_is_mastercard_debit && $card_type != 'MASTERCARD') ||
					($card_is_mastercard_debit && $card_type != 'MASTERCARD_DEBIT')) {
 
				if ($card_is_mastercard_debit && $card_type == 'MASTERCARD' &&
						strtolower(CCCARDS_ACCEPT_MASTERCARD) != 'yes') {
 
					$errors[] = CCCARDS_ERROR_MASTERCARD_CREDIT_NOT_ACCEPTED;
				} else if (!$card_is_mastercard_debit && $card_type == 'MASTERCARD_DEBIT' &&
						strtolower(CCCARDS_ACCEPT_MASTERCARD_DEBIT) != 'yes') {
 
					$errors[] = CCCARDS_ERROR_MASTERCARD_DEBIT_NOT_ACCEPTED;
				} else {
 
					if ($this->_getCardTypeNameForCode('MASTERCARD', true) !=
							CCCARDS_TEXT_MASTERCARD ||
							$this->_getCardTypeNameForCode('MASTERCARD_DEBIT', true) !=
							CCCARDS_TEXT_MASTERCARD_DEBIT) {
						if ($card_type == 'MASTERCARD') {
							$errors[] =
								CCCARDS_ERROR_CARD_IS_MASTERCARD_DEBIT_NOT_CREDIT;
						} else {
							$errors[] =
								CCCARDS_ERROR_CARD_IS_MASTERCARD_CREDIT_NOT_DEBIT;
						}
					} else {
 
						if ($card_type == 'MASTERCARD') {
							$card_type = 'MASTERCARD_DEBIT';
						} else {
							$card_type == 'MASTERCARD';
						}
					}
				}
			}
		}
		 
		$expiry_month = $data_entered['CCCARDS_card_expiry_month'];
		$expiry_year = $data_entered['CCCARDS_card_expiry_year'];
		
		$current_year = date('Y');
		
		if (!is_numeric($expiry_year) || ($expiry_year < $current_year) ||
				($expiry_year > ($current_year + 10))) {
			$errors[] = CCCARDS_ERROR_CARD_EXPIRY_DATE_INVALID;
		} else {
			$current_month = date('n');
			
			if (!is_numeric($expiry_month) || ($expiry_month <= 0) || ($expiry_month > 12) ||
					($expiry_year == $current_year && $expiry_month < $current_month)) {
				$errors[] = CCCARDS_ERROR_CARD_EXPIRY_DATE_INVALID;
			}
		}
		
		if (strtolower(CCCARDS_ASK_FOR_CV2_NUMBER) == 'yes') {
 
			if (strtolower(CCCARDS_ALLOW_NO_CV2_NUMBER) == 'yes' &&
					$data_entered['CCCARDS_card_cv2_number_not_present']) {
				$card_cv2_number = '000';
			} else {
 
				if (strlen($data_entered['CCCARDS_card_cv2_number']) < 3 ||
						!is_numeric($data_entered['CCCARDS_card_cv2_number'])) {
 
					if (strlen($data_entered['CCCARDS_card_cv2_number']) == 0) {
						if (strtolower(CCCARDS_ALLOW_NO_CV2_NUMBER) == 'yes') {
							$errors[] = CCCARDS_ERROR_CARD_CV2_NUMBER_MISSING_INDICATE;
						} else {
							$errors[] = CCCARDS_ERROR_CARD_CV2_NUMBER_MISSING;
						}
					} else {
						$errors[] = CCCARDS_ERROR_CARD_CV2_NUMBER_INVALID;
					}
				} else {
					$card_cv2_number = $data_entered['CCCARDS_card_cv2_number'];
				}
			}
		}
		
 
		if ($this->_showStartDate()) {
			$start_month = $data_entered['CCCARDS_card_start_month'];
			$start_year = $data_entered['CCCARDS_card_start_year'];
			
			if ($start_month != '' || $start_year != '') {
 
				if (!is_numeric($start_year) || ($start_year > $current_year)) {
					$errors[] = CCCARDS_ERROR_CARD_START_DATE_INVALID;
				} else {
					$current_month = date('n');
					
					if (!is_numeric($start_month) || ($start_month <= 0) || ($start_month > 12) ||
							($start_year == $current_year && $start_month > $current_month)) {
						$errors[] = CCCARDS_ERROR_CARD_START_DATE_INVALID;
					}
				}
			}
		}
		
		if (sizeof($errors) > 0) {
 
			$_SESSION['CCCARDS_error_encountered'] = true;
			
 
			foreach ($errors as $error_message) {
				$messageStack->add_session('checkout_payment', $error_message, 'error');
			}
			
			tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
		}
		
		// Data seems to be valid, store the card details
		$this->_card_holder = $card_holder;
		$this->_card_type = $card_type;
		$this->_card_number = $card_number;
		$this->_card_expiry_month = $expiry_month;
		$this->_card_expiry_year = $expiry_year;
		
		if (strtolower(CCCARDS_ASK_FOR_CV2_NUMBER) == 'yes') {
			$this->_card_cv2_number = $card_cv2_number;
		}
		
		if ($this->_showStartDate()) {
			$this->_card_start_month = $start_month;
			$this->_card_start_year = $start_year;
		}
		
		if ($this->_showIssueNumber()) {
			$this->_card_issue_number = $data_entered['CCCARDS_card_issue_number'];
		}	
		
		$this->_buildSurchargeOrDiscount();
	}
	
 
	function confirmation()
	{
 
		$card_type_name = $this->_getCardTypeNameForCode($this->_card_type, false);
		
		$confirmation = array(
			'fields' => array(
				array(
					'title' => CCCARDS_TEXT_CARD_TYPE . '&nbsp;',
					'field' => $card_type_name
					),
				array(
					'title' => CCCARDS_TEXT_CARD_HOLDER . '&nbsp;',
					'field' => $this->_card_holder
					),
				array(
					'title' => CCCARDS_TEXT_CARD_NUMBER . '&nbsp;',
					'field' => substr($this->_card_number, 0, 4) .
						str_repeat('X', (strlen($this->_card_number) - 8)) .
						substr($this->_card_number, -4)
					)
				)
			);
		
		$confirmation['fields'][] = array(
			'title' => CCCARDS_TEXT_CARD_EXPIRY_DATE . '&nbsp;',
			'field' => strftime('%B, %Y', mktime(0, 0, 0, $this->_card_expiry_month, 1,
				$this->_card_expiry_year))
			);
		
		if (strtolower(CCCARDS_ASK_FOR_CV2_NUMBER) == 'yes') {
			if ($this->_card_cv2_number != '000') {
				$confirmation['fields'][] = array(
					'title' => CCCARDS_TEXT_CARD_CV2_NUMBER . '&nbsp;',
					'field' => $this->_card_cv2_number
					);
			} else {
				$confirmation['fields'][] = array(
					'title' => CCCARDS_TEXT_CARD_CV2_NUMBER . '&nbsp;',
					'field' => CCCARDS_TEXT_CARD_CV2_NUMBER_NOT_PRESENT
					);
			}
		}
		
		if ($this->_showStartDate() && $this->_card_start_year != '') {
			$confirmation['fields'][] = array(
				'title' => CCCARDS_TEXT_CARD_START_DATE . '&nbsp;',
				'field' => strftime('%B, %Y', mktime(0, 0, 0, $this->_card_start_month, 1,
					$this->_card_start_year))
				);
		}
		
		if ($this->_showIssueNumber() && $this->_card_issue_number != '') {
			$confirmation['fields'][] = array(
				'title' => CCCARDS_TEXT_CARD_ISSUE_NUMBER . '&nbsp;',
				'field' => $this->_card_issue_number
				);
		}
		
		return $confirmation;
	}
	
 
	function process_button()
	{ 
		$process_button_string = tep_draw_hidden_field('card-holder', $this->_card_holder) .
			tep_draw_hidden_field('card-type', $this->_card_type) .
			tep_draw_hidden_field('card-number', $this->_card_number) .
			tep_draw_hidden_field('card-expiry', $this->_card_expiry_month .
			substr($this->_card_expiry_year, -2));
		
		if (strtolower(CCCARDS_ASK_FOR_CV2_NUMBER) == 'yes') {
			$process_button_string .= tep_draw_hidden_field('card-cv2-number',
				$this->_card_cv2_number);
		}
		
		if ($this->_showStartDate()) {
			$process_button_string .= tep_draw_hidden_field('card-start', $this->_card_start_month .
				substr($this->_card_start_year, -2));
		}
		
		if ($this->_showIssueNumber()) {
			$process_button_string .= tep_draw_hidden_field('card-issue-number',
				$this->_card_issue_number);
		}
		
		$process_button_string .= tep_draw_hidden_field(tep_session_name(), tep_session_id());
		
		return $process_button_string;
	}
 
	function before_process()
	{
		global $order, $messageStack;
		 
		$order->info['cc_owner'] = $_POST['card-holder'];
		$order->info['cc_type'] = substr($this->_getCardTypeNameForCode($_POST['card-type'], false),
			0, 20);
		 
		$len = strlen($_POST['card-number']);
		
		$this->_card_number_middle_digits = substr($_POST['card-number'], 4, ($len - 8));
		
		$order->info['cc_number'] = substr($_POST['card-number'], 0, 4) .
			str_repeat('X', (strlen($_POST['card-number']) - 8)) .
			substr($_POST['card-number'], -4);
		
		$order->info['cc_expires'] = $_POST['card-expiry'];
		
		if (strtolower(CCCARDS_ASK_FOR_CV2_NUMBER) == 'yes') {
			$this->_card_cv2_number = $_POST['card-cv2-number'];
		}
		
		if ($this->_showStartDate()) {
			$order->info['cc_start'] = $_POST['card-start'];
		}
		
		if ($this->_showIssueNumber()) {
			$order->info['cc_issue'] = $_POST['card-issue-number'];
		}
		 
 
	}
	
 
	function after_process()
	{
		global $insert_id;
	 
		if (strtolower(CCCARDS_ASK_FOR_CV2_NUMBER) == 'yes') {
			if ($this->_card_cv2_number == '000') {
				$message = sprintf(CCCARDS_TEXT_EMAIL_CV2_NUMBER_NOT_PRESENT,
					order_id_imc, $this->_card_number_middle_digits);
			} else {
				$message = sprintf(CCCARDS_TEXT_EMAIL, order_id_imc,
					$this->_card_number_middle_digits, $this->_card_cv2_number);
			}
		} else {
			$message = sprintf(CCCARDS_TEXT_EMAIL_CV2_NUMBER_NOT_REQUESTED, order_id_imc,
				$this->_card_number_middle_digits);
		}
		
		$html_msg['EMAIL_MESSAGE_HTML'] = str_replace("\n\n", '<br />', $message);
		
		tep_mail(CCCARDS_EMAIL, CCCARDS_EMAIL,
			CCCARDS_TEXT_EMAIL_SUBJECT . order_id_imc, $message, STORE_NAME,
			EMAIL_FROM, $html_msg, 'cc_middle_digs');
	}
 
	function after_order_create($tep_order_id)
	{
		global $db, $order;
		 
		$start_and_issue = array(
			'order_id' => $tep_order_id,
			'cc_start' => (isset($order->info['cc_start']) ? $order->info['cc_start'] : ''),
			'cc_issue' => (isset($order->info['cc_issue']) ? $order->info['cc_issue'] : '')
			);
		
		tep_db_perform(TABLE_CCCARDS, $start_and_issue);
	}
	
 
	function admin_notification($tep_order_id)
	{
		global $db;
		
		$transaction_info_sql = "
			SELECT
				*
			FROM
				" . TABLE_CCCARDS . "
			WHERE
				order_id = '" . $tep_order_id . "'";
		
		$CCCARDS_result = $db->Execute($transaction_info_sql);
		
		require(DIR_FS_CATALOG. DIR_WS_MODULES .
			'payment/cccards/cccards_admin_notification.php');
		
		return $output;
	}
	
 
	function _referredFromCheckoutProcessURI()
	{
		$referring_uri = getenv('HTTP_REFERER');
		
		if ($referring_uri !== false) {
			$referring_uri = strtolower($referring_uri);
			 
			$checkout_page_uris = array(
				//tep_href_link(FILENAME_SHOPPING_CART),
				tep_href_link(FILENAME_CHECKOUT_SHIPPING),
				tep_href_link(FILENAME_CHECKOUT_SHIPPING_ADDRESS),
				tep_href_link(FILENAME_CHECKOUT_PAYMENT),
				tep_href_link(FILENAME_CHECKOUT_PAYMENT_ADDRESS),
				tep_href_link(FILENAME_CHECKOUT_CONFIRMATION),
				tep_href_link(FILENAME_CHECKOUT_PROCESS)
				);
			 
			if (defined('FILENAME_CHECKOUT')) {
				$checkout_page_uris[] = tep_href_link(FILENAME_CHECKOUT);
			}
			
			if (defined('FILENAME_FEC_CONFIRMATION')) {
				$checkout_page_uris[] = tep_href_link(FILENAME_FEC_CONFIRMATION);
			}
			
			if (defined('FILENAME_QUICK_CHECKOUT')) {
				$checkout_page_uris[] = tep_href_link(FILENAME_QUICK_CHECKOUT);
			}
			
			foreach ($checkout_page_uris as $checkout_page_uri) {
 
				$checkout_page_uri = strtolower($checkout_page_uri);
				$checkout_page_uri = str_replace('&amp;', '&', $checkout_page_uri);
				
 
				$checkout_page_uri = preg_replace('|https?://[^/]+|', '', $checkout_page_uri);
				
				if (strpos($referring_uri, $checkout_page_uri) !== false) {
 
					return true;
				}
			}
		}
		
		return false;
	}
	
 
	function _showStartDate()
	{
		if (strtolower(CCCARDS_ASK_FOR_START_DATE) != 'yes') {
			return false;
		}
		
		if (strtolower(CCCARDS_ACCEPT_MAESTRO) == 'yes') {
			return true;
		}
		
		if (strtolower(CCCARDS_ACCEPT_AMERICAN_EXPRESS) == 'yes') {
			return true;
		}
		
		return false;
	}
	
 
	function _showIssueNumber()
	{
		if (strtolower(CCCARDS_ACCEPT_MAESTRO) == 'yes') {
			return true;
		}
		
		return false;
	}
	
 
	function _getCardTypeNameForCode($card_type_code, $add_surcharge_discount_info = true)
	{
		$card_type_name = '';
		
		switch ($card_type_code) {
			case 'VISA':
				$card_type_name = CCCARDS_TEXT_VISA;
				break;
			case 'MASTERCARD':
				$card_type_name = CCCARDS_TEXT_MASTERCARD;
				break;
			case 'VISA_DEBIT':
				$card_type_name = CCCARDS_TEXT_VISA_DEBIT;
				break;
			case 'MASTERCARD_DEBIT':
				$card_type_name = CCCARDS_TEXT_MASTERCARD_DEBIT;
				break;
			case 'MAESTRO':
				$card_type_name = CCCARDS_TEXT_MAESTRO;
				break;
			case 'VISA_ELECTRON':
				$card_type_name = CCCARDS_TEXT_VISA_ELECTRON;
				break;
			case 'AMERICAN_EXPRESS':
				$card_type_name = CCCARDS_TEXT_AMERICAN_EXPRESS;
				break;
			case 'DINERS_CLUB':
				$card_type_name = CCCARDS_TEXT_DINERS_CLUB;
				break;
			case 'JCB':
				$card_type_name = CCCARDS_TEXT_JCB;
				break;
			case 'LASER':
				$card_type_name = CCCARDS_TEXT_LASER;
				break;
			case 'DISCOVER':
				$card_type_name = CCCARDS_TEXT_DISCOVER;
				break;
			default:
				break;
		}
		 
		if (strtolower(CCCARDS_ENABLE_SURCHARGES_DISCOUNTS) == 'yes' &&
				isset($GLOBALS['SOFT_pymnt_surcharges_discounts']) &&
				$add_surcharge_discount_info) {
			 
			$tables_of_rates = $this->_getSurchargeDiscountTablesOfRates($card_type_code);
			
			if ($tables_of_rates !== false) { 
				
				$surcharge_or_discount_calculated = $GLOBALS['SOFT_pymnt_surcharges_discounts']->
					calculateSurchargeOrDiscount($tables_of_rates);
				
				if ($surcharge_or_discount_calculated == false) { 
					$card_type_name .= ' (' . $GLOBALS['SOFT_pymnt_surcharges_discounts']->
						getErrorMessage() . ')';
				} else {
 
					$tables_of_short_descs = trim(constant(
						'CCCARDS_SURCHARGES_DISCOUNTS_' . $card_type_code .
						'_SHORT_' . strtoupper($_SESSION['languages_code'])));
					
					$short_desc = $GLOBALS['SOFT_pymnt_surcharges_discounts']->
						getShortDescription($tables_of_short_descs,
							CCCARDS_TEXT_SURCHARGE_SHORT,
							CCCARDS_TEXT_DISCOUNT_SHORT);
					
					if (strlen($short_desc) > 0) {
 
						$card_type_name .= ' (' . $short_desc . ')';
					}
				}
			}
		}
		
		return $card_type_name;
	}
	
 
	function _isCardNumberMasterCardDebit($card_number)
	{ 
		$mastercard_debit_bin_codes = array(
			'512499',
			'512746',
			'516001',
			'516730-516979',
			'517000-517049',
			'524342',
			'527591',
			'535110-535309',
			'535420-535819',
			'537210-537609',
			'557347-557496',
			'557498-557547'
			);
		
		$first_six_digits = substr($card_number, 0, 6);
		
		foreach ($mastercard_debit_bin_codes as $mastercard_debit_bin_code) {
			if (strpos('-', $mastercard_debit_bin_code) !== false) {
				// Compare against a range of codes
				$bin_code_limits = split('-', $mastercard_debit_bin_code);
				
				if ((int) $first_six_digits >= (int) $bin_code_limits[0] &&
						(int) $first_six_digits <= (int) $bin_code_limits[1]) {
					return true;
				}
			} else {
				// Compare against a single code
				if ($first_six_digits == $mastercard_debit_bin_code) {
					return true;
				}
			}
		}
		
		return false;
	}
 
	function _buildSurchargeOrDiscount()
	{
		if (strtolower(CCCARDS_ENABLE_SURCHARGES_DISCOUNTS) == 'yes' &&
				isset($GLOBALS['SOFT_pymnt_surcharges_discounts'])) {
			
			// Check if there are any surcharges/discounts defined for the specified card type
			$tables_of_rates = $this->_getSurchargeDiscountTablesOfRates($this->_card_type);
			
			if ($tables_of_rates !== false) {
				// Get any tables of long descriptions defined for the card type
				$tables_of_long_descs = trim(constant(
					'CCCARDS_SURCHARGES_DISCOUNTS_' . $this->_card_type .
					'_LONG_' . strtoupper($_SESSION['languages_code'])));
				
				$GLOBALS['SOFT_pymnt_surcharges_discounts']->setTablesOfRatesAndLongDescriptions(
					$tables_of_rates, $tables_of_long_descs, CCCARDS_TEXT_SURCHARGE_LONG,
					CCCARDS_TEXT_DISCOUNT_LONG);
			}
		}
	}
	
 
	function _getSurchargeDiscountTablesOfRates($card_type_code)
	{
		$surcharges_discounts = preg_replace('/[\s]+/', '', constant(
			'CCCARDS_SURCHARGES_DISCOUNTS_' . $card_type_code));
		
		if (!is_null($surcharges_discounts) && strlen($surcharges_discounts) > 0) {
			return $surcharges_discounts;
		}
		
		return false;
	}
	
 
 
	 
	
 
 
 
	function _getConfigurationMessagesOutput()
	{
		$config_messages = '';
		
 
 
 
		if (sizeof($this->_config_messages) > 0) {
			$config_messages .= '<fieldset style="background: #f7f6f0; margin-bottom: 1.5em">' .
				'<legend style="font-size: 1.2em; font-weight: bold">Configuration Issues</legend>';
			
			foreach ($this->_config_messages as $config_message) {
				$config_messages .= $config_message;
			}
			
			$config_messages .= '</fieldset>';
		}
		
 
 
		
		
		return $config_messages;
	}
 
	
 
	function update_status()
	{
		global $order, $db;
		
		if (($this->enabled == true) && ($this->zone > 0)) {
			$check_flag = false;
			
			$sql = "
				SELECT
					zone_id
				FROM
					" . TABLE_ZONES_TO_GEO_ZONES . "
				WHERE
					geo_zone_id = '" . $this->zone . "'
				AND
					zone_country_id = '" . $order->billing['country']['id'] . "'
				ORDER BY
					zone_id
				";
			
			$check = $db->Execute($sql);
			
			while (!$check->EOF) {
				if ($check->fields['zone_id'] < 1) {
					$check_flag = true;
					break;
				} elseif ($check->fields['zone_id'] == $order->billing['zone_id']) {
					$check_flag = true;
					break;
				}
				
				$check->MoveNext();
			}
			
			if ($check_flag == false) {
				$this->enabled = false;
			}
		}
	}
	
 
	function install()
	{

		 tep_db_query("
			CREATE TABLE IF NOT EXISTS 
				" . TABLE_CCCARDS . "
			(
				`id` int(11) unsigned NOT NULL auto_increment,
				`order_id` int(11) NOT NULL default '0',
				`cc_start` varchar(4) default NULL,
				`cc_issue` varchar(2) default NULL,
				PRIMARY KEY (`id`)
			);");
 


		 
		$background_colour = '#d0d0d0';
		
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable SOFT Manual Card Module', 'CCCARDS_STATUS', 'True', 'Should SOFT Manual Card be enabled as a payment option for this store?', '6', '0', 'tep_cfg_select_option(array(\'True\', \'False\'),', now())");
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('E-mail Address', 'CCCARDS_EMAIL', '', 'The E-mail Address to which the Middle Digits of the Card Number should be sent.<br /><br />THIS IS ESSENTIAL!<br /><br />Only simple e-mail addresses in format <code>user@domain.com</code> are supported (i.e. multiple addresses aren\'t supported, nor are addresses in the format <code>&quot;User Name&quot; &lt;user@domain.com&gt;</code>).', '6', '0', now())");
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'CCCARDS_ZONE', '0', 'If a zone is selected, this module will only be enabled for the selected zone.<br /><br />Leave set to \"--none--\" if SOFT Manual Card should be used for all customers, regardless of what zone their billing address is in.', '6', '0', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Order Status', 'CCCARDS_ORDER_STATUS_ID', '0', 'Orders paid for using this module will have their order status set to this value.', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
		
		  
		
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('</b></fieldset><fieldset style=\"background: " . $background_colour . "; margin-bottom: 1.5em;\"><legend style=\"font-size: 1.4em; font-weight: bold\">Security Options</legend><b>Store Sensitive Card Details Temporarily in Session?', 'CCCARDS_STORE_SENSITIVE_DETAILS_IN_SESSION', 'Yes', 'Should the customer\'s sensitive card details - the Card Number and the Card CV2 Number - be stored temporarily in the session? (They\'ll be cleared from the session when the order is completed).<br /><br />As standard, if a customer leaves the payment details page to go back to the shipping page or the shopping cart, or if they make a mistake when entering their card details, the module will restore most of the details entered, so the customer doesn\'t have to re-enter them when they come back to the payment page.<br /><br />When this option is enabled, the Card Number and the Card CV2 Number will also be stored temporarily, encrypted in the session using the Blowfish algorithm.<br /><br />If this option is disabled, neither the Card Number nor the Card CV2 Number are stored in the session. Customers will have to re-enter their Card Number and their Card CV2 Number in full any time they come back to the payment page (i.e. if they don\'t go straight from the payment page to order completion).', '6', '0', 'tep_cfg_select_option(array(\'Yes\', \'No\'), ', now())");
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Encryption Keyphrase', 'CCCARDS_BLOWFISH_ENCRYPTION_KEYPHRASE', '" . microtime() . md5(time()) . "', 'The keyphrase to be used to encrypt the Card details if they are to be (temporarily) stored in the session.<br /><br />This keyphrase can be <strong>any</strong> random text string, just make one up.', '6', '0', now())");
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Ask for CV2 Number', 'CCCARDS_ASK_FOR_CV2_NUMBER', 'Yes', 'Should a field be displayed for the customer to enter a card CV2 number?', '6', '0', 'tep_cfg_select_option(array(\'Yes\', \'No\'), ', now())");
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Allow cards with no CV2 Number', 'CCCARDS_ALLOW_NO_CV2_NUMBER', 'Yes', 'A small minority of cards have no CV2 number. If a customer has filled in all card details except for the CV2 number should they be given the option to indicate that their card has no CV2 number? (This is necessary for Laser cards as many of them tend not to have CV2 numbers.)', '6', '0', 'tep_cfg_select_option(array(\'Yes\', \'No\'), ', now())");
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Ask for Start Date', 'CCCARDS_ASK_FOR_START_DATE', 'Yes', 'Should month and year select gadgets be displayed for the customer to select a start date for the card? Please note that when this option is enabled, the start date select gadgets will only be shown if one more card types which can have start dates (Maestro and/or AmEx) are enabled in the Card Type Config section below.', '6', '0', 'tep_cfg_select_option(array(\'Yes\', \'No\'), ', now())");
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Disable Autocomplete for Card Number field', 'CCCARDS_DISABLE_CARD_NUMBER_AUTOCOMPLETE', 'Yes', 'Should the autocomplete functionality of certain browsers be disabled for the Card Number field? (This prevents the browser from automatically entering the customer\'s Card Number).', '6', '0', 'tep_cfg_select_option(array(\'Yes\', \'No\'), ', now())");
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Disable Autocomplete for CV2 field (if CV2 being asked for)', 'CCCARDS_DISABLE_CV2_NUMBER_AUTOCOMPLETE', 'Yes', 'Should the autocomplete functionality of certain browsers be disabled for the CV2 field? (This prevents the browser from automatically entering the customer\'s CV2 Number).', '6', '0', 'tep_cfg_select_option(array(\'Yes\', \'No\'), ', now())");
		
		  
		
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('</b></fieldset><fieldset style=\"background: " . $background_colour . "; margin-bottom: 1.5em;\"><legend style=\"font-size: 1.4em; font-weight: bold\">Card Types Enabled</legend><b>Visa Card Payments', 'CCCARDS_ACCEPT_VISA', 'Yes', 'Does the store accept Visa Card payments?', '6', '0', 'tep_cfg_select_option(array(\'Yes\', \'No\'), ', now())");
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('MasterCard Card Payments', 'CCCARDS_ACCEPT_MASTERCARD', 'Yes', 'Does the store accept MasterCard Card payments?', '6', '0', 'tep_cfg_select_option(array(\'Yes\', \'No\'), ', now())");
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Visa Debit Card Payments', 'CCCARDS_ACCEPT_VISA_DEBIT', 'Yes', 'Does the store accept Visa Debit Card payments?', '6', '0', 'tep_cfg_select_option(array(\'Yes\', \'No\'), ', now())");
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('MasterCard Debit Card Payments', 'CCCARDS_ACCEPT_MASTERCARD_DEBIT', 'Yes', 'Does the store accept MasterCard Debit Card payments?', '6', '0', 'tep_cfg_select_option(array(\'Yes\', \'No\'), ', now())");
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Maestro Card Payments', 'CCCARDS_ACCEPT_MAESTRO', 'Yes', 'Does the store accept Maestro Card payments?', '6', '0', 'tep_cfg_select_option(array(\'Yes\', \'No\'), ', now())");
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Visa Electron (UKE) Card Payments', 'CCCARDS_ACCEPT_VISA_ELECTRON', 'Yes', 'Does the store accept Visa Electron Card payments?', '6', '0', 'tep_cfg_select_option(array(\'Yes\', \'No\'), ', now())");
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('American Express Card Payments', 'CCCARDS_ACCEPT_AMERICAN_EXPRESS', 'No', 'Does the store accept American Express Card payments?', '6', '0', 'tep_cfg_select_option(array(\'Yes\', \'No\'), ', now())");
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Diners Club Card Payments', 'CCCARDS_ACCEPT_DINERS_CLUB', 'No', 'Does the store accept Diners Club Card payments?', '6', '0', 'tep_cfg_select_option(array(\'Yes\', \'No\'), ', now())");
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('JCB Card Payments', 'CCCARDS_ACCEPT_JCB', 'No', 'Does the store accept JCB Card payments?', '6', '0', 'tep_cfg_select_option(array(\'Yes\', \'No\'), ', now())");
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Laser Card Payments', 'CCCARDS_ACCEPT_LASER', 'No', 'Does the store accept Laser Card payments? (Please note that Laser cards can only be used when the customer is checking out in Euros).', '6', '0', 'tep_cfg_select_option(array(\'Yes\', \'No\'), ', now())");
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Discover Card Payments', 'CCCARDS_ACCEPT_DISCOVER', 'No', 'Does the store accept Discover Card payments?', '6', '0', 'tep_cfg_select_option(array(\'Yes\', \'No\'), ', now())");
		
		  
		
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('</b></fieldset><fieldset style=\"background: " . $background_colour . "; margin-bottom: 1.5em;\"><legend style=\"font-size: 1.4em; font-weight: bold\">Display Options</legend><b>Show icons of Cards Accepted', 'CCCARDS_SHOW_CARDS_ACCEPTED', 'Yes', 'Should icons be shown for each Credit/Debit Card accepted?', '6', '0', 'tep_cfg_select_option(array(\'Yes\', \'No\'), ', now())");
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Start/Expiry Month Format', 'CCCARDS_SELECT_MONTH_FORMAT', '%m - %B', 'A valid strftime format code should be entered here, to be used within the Start and Expiry Date Month Selection gadgets.', '6', '0', now())");
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Start/Expiry Year Format', 'CCCARDS_SELECT_YEAR_FORMAT', '%Y', 'A valid strftime format code should be entered here, to be used within the Start and Expiry Date Year Selection gadgets.', '6', '0', now())");
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order of Display.', 'CCCARDS_SORT_ORDER', '0', 'The Sort Order of Display determines what order the installed payment modules are displayed in. The module with the lowest Sort Order is displayed first (towards the top). No two payment modules can have the same sort order, unless all are using \'0\'.', '6', '0', now())");
		


		$languages = tep_get_languages();
		 
		
		 tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('</b></fieldset><fieldset style=\"background: " . $background_colour . "; margin-bottom: 1.5em;\"><legend style=\"font-size: 1.4em; font-weight: bold\">Card Type Surcharges/Discounts</legend><b>Enable Surcharge/Discount Functionality', 'CCCARDS_ENABLE_SURCHARGES_DISCOUNTS', 'No', 'If enabled, this option will allow a Single Rate or Tables of Rates to be specified for any of the enabled card types, to be used in conjunction with the SOFT Payments/Surcharges Discounts Order Total module.<br /><br />This will apply either a surcharge or discount for a card type, dependant on the currency in use and/or the delivery country and/or the value of the order.<br /><br />The Rates can be either Specific Values (E.g. <code>2.00</code> or <code>-3.50</code>) or Percentages (E.g. <code>4%</code> or <code>-0.5%</code>) or, <strong>for surcharges only</strong>, a Percentage plus a Specific Value (E.g. <code>3.4%+0.20</code>).<br /><br /><em>For example</em>: A Single Rate which applies to all Order Values could be specified as <code>2.5%&rdquo;</code> or <code>1.50</code>.<br /><br />The Tables of Rates are comma-separated lists of Limits/Rate pairs. Each Limits/Rate pair consists of an Order Value Range and a Rate, separated by a colon. <br /><br /><em>For example</em>: <code>1000:2.00, 3000:1.50, *:0</code><br /><br />In the above example, orders with a Total Value less than 1000 would have a surcharge of 2.00, those from 1000 up to 3000 would have a surcharge of 1.50 and orders of 3000 and above would have no surcharge applied).<br /><br />Notes: An asterix (*) is a wildcard which matches any value; Lower Limits for ranges can be specified by preceding the Upper Limit with a dash (E.g. <code>300-500</code>).<br /><br />The Tables of Rates can be applied on a currency and/or country basis. More info can be found in the module\'s documentation. A simple example is:<br /><br /><code>GB|IE[1.95%], US[0-1000:2.45%, *:2.85%], *[2.95%]</code><br /><br />Should Surcharges/Discounts be enabled?', '6', '0', 'tep_cfg_select_option(array(\'Yes\', \'No\'), ', now())");
		
		 tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('</b><hr /><b>Visa Card Surcharges/Discounts', 'CCCARDS_SURCHARGES_DISCOUNTS_VISA', '', 'If there are surcharge(s) or discount(s) for Visa Card payments, a Rate or any Table(s) of Rates should be entered here.', '6', '0', now())");
		 
		for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
			 tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('" . tep_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . "&nbsp;Short Surcharges/Discounts Title(s)', 'CCCARDS_SURCHARGES_DISCOUNTS_VISA_SHORT_" . strtoupper($languages[$i]['code']) . "', '', 'Short Descriptive Title to be added after card\'s title in the Card Type selection gadget (E.g. &ldquo;2% Surcharge&rdquo;). Table(s) of Titles use the same format as Table(s) of Rates but with a title instead of a rate.', '6', '0', now())");
			 tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('" . tep_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . "&nbsp;Long Surcharges/Discounts Title(s)', 'CCCARDS_SURCHARGES_DISCOUNTS_VISA_LONG_" . strtoupper($languages[$i]['code']) . "', '', 'Longer Descriptive Title for Order Total Summary Line (E.g. &ldquo;Visa Card Surcharge @ 2%&rdquo;). Table(s) of Titles use the same format as Table(s) of Rates but with a title instead of a rate.', '6', '0', now())");
		}
		
		 tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('</b><hr /><b>MasterCard Card Surcharges/Discounts', 'CCCARDS_SURCHARGES_DISCOUNTS_MASTERCARD', '', 'If there are surcharge(s) or discount(s) for MasterCard Card payments, a Rate or any Table(s) of Rates should be entered here.', '6', '0', now())");
		 
		for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
			 tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('" . tep_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . "&nbsp;Short Surcharges/Discounts Title(s)', 'CCCARDS_SURCHARGES_DISCOUNTS_MASTERCARD_SHORT_" . strtoupper($languages[$i]['code']) . "', '', 'Short Descriptive Title to be added after card\'s title in the Card Type selection gadget (E.g. &ldquo;2% Surcharge&rdquo;). Table(s) of Titles use the same format as Table(s) of Rates but with a title instead of a rate.', '6', '0', now())");
			 tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('" . tep_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . "&nbsp;Long Surcharges/Discounts Title(s)', 'CCCARDS_SURCHARGES_DISCOUNTS_MASTERCARD_LONG_" . strtoupper($languages[$i]['code']) . "', '', 'Longer Descriptive Title for Order Total Summary Line (E.g. &ldquo;MasterCard Card Surcharge @ 2%&rdquo;). Table(s) of Titles use the same format as Table(s) of Rates but with a title instead of a rate.', '6', '0', now())");
		}
		
		 tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('</b><hr /><b>Visa Debit Card Surcharges/Discounts', 'CCCARDS_SURCHARGES_DISCOUNTS_VISA_DEBIT', '', 'If there are surcharge(s) or discount(s) for Visa Debit Card payments, a Rate or any Table(s) of Rates should be entered here.<br /><br />Please Note: Most policies forbid surcharges for debit cards!', '6', '0', now())");
		 
		for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
			 tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('" . tep_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . "&nbsp;Short Surcharges/Discounts Title(s)', 'CCCARDS_SURCHARGES_DISCOUNTS_VISA_DEBIT_SHORT_" . strtoupper($languages[$i]['code']) . "', '', 'Short Descriptive Title to be added after card\'s title in the Card Type selection gadget (E.g. &ldquo;&pound;0.50 Discount&rdquo;). Table(s) of Titles use the same format as Table(s) of Rates but with a title instead of a rate.', '6', '0', now())");
			 tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('" . tep_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . "&nbsp;Long Surcharges/Discounts Title(s)', 'CCCARDS_SURCHARGES_DISCOUNTS_VISA_DEBIT_LONG_" . strtoupper($languages[$i]['code']) . "', '', 'Longer Descriptive Title for Order Total Summary Line (E.g. &ldquo;Visa Debit Card Discount&rdquo;). Table(s) of Titles use the same format as Table(s) of Rates but with a title instead of a rate.', '6', '0', now())");
		}
		
		 tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('</b><hr /><b>MasterCard Debit Card Surcharges/Discounts', 'CCCARDS_SURCHARGES_DISCOUNTS_MASTERCARD_DEBIT', '', 'If there are surcharge(s) or discount(s) for MasterCard Debit Card payments, a Rate or any Table(s) of Rates should be entered here.', '6', '0', 'tep_cfg_textarea(', now())");
		 
		for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
			 tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('" . tep_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . "&nbsp;Short Surcharges/Discounts Title(s)', 'CCCARDS_SURCHARGES_DISCOUNTS_MASTERCARD_DEBIT_SHORT_" . strtoupper($languages[$i]['code']) . "', '', 'Short Descriptive Title to be added after card\'s title in the Card Type selection gadget (E.g. &ldquo;2% Surcharge&rdquo;). Table(s) of Titles use the same format as Table(s) of Rates but with a title instead of a rate.', '6', '0', 'tep_cfg_textarea(', now())");
			 tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('" . tep_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . "&nbsp;Long Surcharges/Discounts Title(s)', 'CCCARDS_SURCHARGES_DISCOUNTS_MASTERCARD_DEBIT_LONG_" . strtoupper($languages[$i]['code']) . "', '', 'Longer Descriptive Title for Order Total Summary Line (E.g. &ldquo;MasterCard Debit Card Surcharge @ 2%&rdquo;). Table(s) of Titles use the same format as Table(s) of Rates but with a title instead of a rate.', '6', '0', 'tep_cfg_textarea(', now())");
		}
		
		 tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('</b><hr /><b>Maestro Card Surcharges/Discounts', 'CCCARDS_SURCHARGES_DISCOUNTS_MAESTRO', '', 'If there are surcharge(s) or discount(s) for Maestro Card payments, a Rate or any Table(s) of Rates should be entered here.<br /><br />Please Note: Most policies forbid surcharges for debit cards!', '6', '0', now())");
		 
		for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
			 tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('" . tep_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . "&nbsp;Short Surcharges/Discounts Title(s)', 'CCCARDS_SURCHARGES_DISCOUNTS_MAESTRO_SHORT_" . strtoupper($languages[$i]['code']) . "', '', 'Short Descriptive Title to be added after card\'s title in the Card Type selection gadget (E.g. &ldquo;&pound;0.50 Discount&rdquo;). Table(s) of Titles use the same format as Table(s) of Rates but with a title instead of a rate.', '6', '0', now())");
			 tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('" . tep_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . "&nbsp;Long Surcharges/Discounts Title(s)', 'CCCARDS_SURCHARGES_DISCOUNTS_MAESTRO_LONG_" . strtoupper($languages[$i]['code']) . "', '', 'Longer Descriptive Title for Order Total Summary Line (E.g. &ldquo;Maestro Card Discount&rdquo;). Table(s) of Titles use the same format as Table(s) of Rates but with a title instead of a rate.', '6', '0', now())");
		}
		
		 tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('</b><hr /><b>Visa Electron Card Surcharges/Discounts', 'CCCARDS_SURCHARGES_DISCOUNTS_VISA_ELECTRON', '', 'If there are surcharge(s) or discount(s) for Visa Electron Card payments, a Rate or any Table(s) of Rates should be entered here.<br /><br />Please Note: Most policies forbid surcharges for debit cards!', '6', '0', now())");
		 
		for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
			 tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('" . tep_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . "&nbsp;Short Surcharges/Discounts Title(s)', 'CCCARDS_SURCHARGES_DISCOUNTS_VISA_ELECTRON_SHORT_" . strtoupper($languages[$i]['code']) . "', '', 'Short Descriptive Title to be added after card\'s title in the Card Type selection gadget (E.g. &ldquo;&pound;0.50 Discount&rdquo;). Table(s) of Titles use the same format as Table(s) of Rates but with a title instead of a rate.', '6', '0', now())");
			 tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('" . tep_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . "&nbsp;Long Surcharges/Discounts Title(s)', 'CCCARDS_SURCHARGES_DISCOUNTS_VISA_ELECTRON_LONG_" . strtoupper($languages[$i]['code']) . "', '', 'Longer Descriptive Title for Order Total Summary Line (E.g. &ldquo;Visa Electron Card Discount&rdquo;). Table(s) of Titles use the same format as Table(s) of Rates but with a title instead of a rate.', '6', '0', now())");
		}
		
		 tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('</b><hr /><b>American Express Card Surcharges/Discounts', 'CCCARDS_SURCHARGES_DISCOUNTS_AMERICAN_EXPRESS', '', 'If there are surcharge(s) or discount(s) for American Express Card payments, a Rate or any Table(s) of Rates should be entered here.', '6', '0', now())");
		 
		for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
			 tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('" . tep_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . "&nbsp;Short Surcharges/Discounts Title(s)', 'CCCARDS_SURCHARGES_DISCOUNTS_AMERICAN_EXPRESS_SHORT_" . strtoupper($languages[$i]['code']) . "', '', 'Short Descriptive Title to be added after card\'s title in the Card Type selection gadget (E.g. &ldquo;4% Surcharge&rdquo;). Table(s) of Titles use the same format as Table(s) of Rates but with a title instead of a rate.', '6', '0', now())");
			 tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('" . tep_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . "&nbsp;Long Surcharges/Discounts Title(s)', 'CCCARDS_SURCHARGES_DISCOUNTS_AMERICAN_EXPRESS_LONG_" . strtoupper($languages[$i]['code']) . "', '', 'Longer Descriptive Title for Order Total Summary Line (E.g. &ldquo;American Express Card Surcharge @ 4%&rdquo;). Table(s) of Titles use the same format as Table(s) of Rates but with a title instead of a rate.', '6', '0', now())");
		}
		
		 tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('</b><hr /><b>Diners Club Card Surcharges/Discounts', 'CCCARDS_SURCHARGES_DISCOUNTS_DINERS_CLUB', '', 'If there are surcharge(s) or discount(s) for Diners Club Card payments, a Rate or any Table(s) of Rates should be entered here.', '6', '0', now())");
		 
		for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
			 tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('" . tep_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . "&nbsp;Short Surcharges/Discounts Title(s)', 'CCCARDS_SURCHARGES_DISCOUNTS_DINERS_CLUB_SHORT_" . strtoupper($languages[$i]['code']) . "', '', 'Short Descriptive Title to be added after card\'s title in the Card Type selection gadget (E.g. &ldquo;2% Surcharge&rdquo;). Table(s) of Titles use the same format as Table(s) of Rates but with a title instead of a rate.', '6', '0', now())");
			 tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('" . tep_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . "&nbsp;Long Surcharges/Discounts Title(s)', 'CCCARDS_SURCHARGES_DISCOUNTS_DINERS_CLUB_LONG_" . strtoupper($languages[$i]['code']) . "', '', 'Longer Descriptive Title for Order Total Summary Line (E.g. &ldquo;Diners Club Card Surcharge @ 2%&rdquo;). Table(s) of Titles use the same format as Table(s) of Rates but with a title instead of a rate.', '6', '0', now())");
		}
		
		 tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('</b><hr /><b>JCB Card Surcharges/Discounts', 'CCCARDS_SURCHARGES_DISCOUNTS_JCB', '', 'If there are surcharge(s) or discount(s) for JCB Card payments, a Rate or any Table(s) of Rates should be entered here.', '6', '0', now())");
		 
		for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
			 tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('" . tep_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . "&nbsp;Short Surcharges/Discounts Title(s)', 'CCCARDS_SURCHARGES_DISCOUNTS_JCB_SHORT_" . strtoupper($languages[$i]['code']) . "', '', 'Short Descriptive Title to be added after card\'s title in the Card Type selection gadget (E.g. &ldquo;2% Surcharge&rdquo;). Table(s) of Titles use the same format as Table(s) of Rates but with a title instead of a rate.', '6', '0', now())");
			 tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('" . tep_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . "&nbsp;Long Surcharges/Discounts Title(s)', 'CCCARDS_SURCHARGES_DISCOUNTS_JCB_LONG_" . strtoupper($languages[$i]['code']) . "', '', 'Longer Descriptive Title for Order Total Summary Line (E.g. &ldquo;JCB Card Surcharge @ 2%&rdquo;). Table(s) of Titles use the same format as Table(s) of Rates but with a title instead of a rate.', '6', '0', now())");
		}
		
		 tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('</b><hr /><b>Laser Card Surcharges/Discounts', 'CCCARDS_SURCHARGES_DISCOUNTS_LASER', '', 'If there are surcharge(s) or discount(s) for Laser Card payments, a Rate or any Table(s) of Rates should be entered here.<br /><br />Please Note: Most policies forbid surcharges for debit cards!', '6', '0', now())");
		 
		for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
			 tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('" . tep_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . "&nbsp;Short Surcharges/Discounts Title(s)', 'CCCARDS_SURCHARGES_DISCOUNTS_LASER_SHORT_" . strtoupper($languages[$i]['code']) . "', '', 'Short Descriptive Title to be added after card\'s title in the Card Type selection gadget (E.g. &ldquo;&pound;0.50 Discount&rdquo;). Table(s) of Titles use the same format as Table(s) of Rates but with a title instead of a rate.', '6', '0', now())");
			 tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('" . tep_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . "&nbsp;Long Surcharges/Discounts Title(s)', 'CCCARDS_SURCHARGES_DISCOUNTS_LASER_LONG_" . strtoupper($languages[$i]['code']) . "', '', 'Longer Descriptive Title for Order Total Summary Line (E.g. &ldquo;Laser Card Discount&rdquo;). Table(s) of Titles use the same format as Table(s) of Rates but with a title instead of a rate.', '6', '0', now())");
		}
		
		 tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('</b><hr /><b>Discover Card Surcharges/Discounts', 'CCCARDS_SURCHARGES_DISCOUNTS_DISCOVER', '', 'If there are surcharge(s) or discount(s) for Discover Card payments, a Rate or any Table(s) of Rates should be entered here.', '6', '0', now())");
		 
		for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
			 tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('" . tep_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . "&nbsp;Short Surcharges/Discounts Title(s)', 'CCCARDS_SURCHARGES_DISCOUNTS_DISCOVER_SHORT_" . strtoupper($languages[$i]['code']) . "', '', 'Short Descriptive Title to be added after card\'s title in the Card Type selection gadget (E.g. &ldquo;2% Surcharge&rdquo;). Table(s) of Titles use the same format as Table(s) of Rates but with a title instead of a rate.', '6', '0', now())");
			 tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('" . tep_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . "&nbsp;Long Surcharges/Discounts Title(s)', 'CCCARDS_SURCHARGES_DISCOUNTS_DISCOVER_LONG_" . strtoupper($languages[$i]['code']) . "', '', 'Longer Descriptive Title for Order Total Summary Line (E.g. &ldquo;Discover Card Card Surcharge @ 2%&rdquo;). Table(s) of Titles use the same format as Table(s) of Rates but with a title instead of a rate.', '6', '0', now())");
		}
		
		 tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Show Message about Surcharges/Discounts', 'CCCARDS_ENABLE_CUSTOM_SURCHARGES_DISCOUNTS_MESSAGE', 'Yes', 'If using the Surcharges/Discounts functionality, it may prove beneficial to give the customer a bit of information about the store\'s policy.<br /><br />If this option is enabled then the message defined in the Languages Definition file will be displayed immediately above the Card Type selection gadget.<br /><br />Should this message be displayed?', '6', '0', 'tep_cfg_select_option(array(\'Yes\', \'No\'), ', now())");
		  
		
		  
		
 

		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('</b></fieldset><p style=\"display: none\">', 'CCCARDS_MADE_BY_SOFT', '" . $this->version . "', '', '6', '0', 'tep_draw_hidden_field(\'made-by-SOFT\' . ', now())");

 
	}
	
  
	
 
	function check()
	{

		
		if (!isset($this->_check)) {
			$check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION .
				" where configuration_key = 'CCCARDS_STATUS'");
			
			$this->_check =tep_db_num_rows($check_query);
		}
		
		return $this->_check;
	}
	
 
	
 
	function remove()
	{


		
		tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" .
			implode("', '", $this->keys()) . "')");
	}
	 
 
	function keys()
	{
		$languages = tep_get_languages();
		
		$keys = array(
			'CCCARDS_STATUS',
			'CCCARDS_EMAIL',
			'CCCARDS_ZONE',
			'CCCARDS_ORDER_STATUS_ID',
			'CCCARDS_STORE_SENSITIVE_DETAILS_IN_SESSION',
			'CCCARDS_BLOWFISH_ENCRYPTION_KEYPHRASE',
			'CCCARDS_ASK_FOR_CV2_NUMBER',
			'CCCARDS_ALLOW_NO_CV2_NUMBER',
			'CCCARDS_ASK_FOR_START_DATE',
			'CCCARDS_DISABLE_CARD_NUMBER_AUTOCOMPLETE',
			'CCCARDS_DISABLE_CV2_NUMBER_AUTOCOMPLETE',
			'CCCARDS_ACCEPT_VISA',
			'CCCARDS_ACCEPT_MASTERCARD',
			'CCCARDS_ACCEPT_VISA_DEBIT',
			'CCCARDS_ACCEPT_MASTERCARD_DEBIT',
			'CCCARDS_ACCEPT_MAESTRO',
			'CCCARDS_ACCEPT_VISA_ELECTRON',
			'CCCARDS_ACCEPT_AMERICAN_EXPRESS',
			'CCCARDS_ACCEPT_DINERS_CLUB',
			'CCCARDS_ACCEPT_JCB',
			'CCCARDS_ACCEPT_LASER',
			'CCCARDS_ACCEPT_DISCOVER',
			'CCCARDS_SHOW_CARDS_ACCEPTED',
			'CCCARDS_SELECT_MONTH_FORMAT',
			'CCCARDS_SELECT_YEAR_FORMAT',
			'CCCARDS_ENABLE_CUSTOM_SURCHARGES_DISCOUNTS_MESSAGE',
			'CCCARDS_SORT_ORDER',
			'CCCARDS_ENABLE_SURCHARGES_DISCOUNTS'
			);
		
		$keys[] = 'CCCARDS_SURCHARGES_DISCOUNTS_VISA';
		
		for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
			$keys[] = 'CCCARDS_SURCHARGES_DISCOUNTS_VISA_SHORT_' .
				strtoupper($languages[$i]['code']);
			$keys[] = 'CCCARDS_SURCHARGES_DISCOUNTS_VISA_LONG_' .
				strtoupper($languages[$i]['code']);
		}
		
		$keys[] = 'CCCARDS_SURCHARGES_DISCOUNTS_MASTERCARD';
		
		for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
			$keys[] = 'CCCARDS_SURCHARGES_DISCOUNTS_MASTERCARD_SHORT_' .
				strtoupper($languages[$i]['code']);
			$keys[] = 'CCCARDS_SURCHARGES_DISCOUNTS_MASTERCARD_LONG_' .
				strtoupper($languages[$i]['code']);
		}
		
		$keys[] = 'CCCARDS_SURCHARGES_DISCOUNTS_VISA_DEBIT';
		
		for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
			$keys[] = 'CCCARDS_SURCHARGES_DISCOUNTS_VISA_DEBIT_SHORT_' .
				strtoupper($languages[$i]['code']);
			$keys[] = 'CCCARDS_SURCHARGES_DISCOUNTS_VISA_DEBIT_LONG_' .
				strtoupper($languages[$i]['code']);
		}
		
		$keys[] = 'CCCARDS_SURCHARGES_DISCOUNTS_MASTERCARD_DEBIT';
		
		for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
			$keys[] = 'CCCARDS_SURCHARGES_DISCOUNTS_MASTERCARD_DEBIT_SHORT_' .
				strtoupper($languages[$i]['code']);
			$keys[] = 'CCCARDS_SURCHARGES_DISCOUNTS_MASTERCARD_DEBIT_LONG_' .
				strtoupper($languages[$i]['code']);
		}
		
		$keys[] = 'CCCARDS_SURCHARGES_DISCOUNTS_MAESTRO';
		
		for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
			$keys[] = 'CCCARDS_SURCHARGES_DISCOUNTS_MAESTRO_SHORT_' .
				strtoupper($languages[$i]['code']);
			$keys[] = 'CCCARDS_SURCHARGES_DISCOUNTS_MAESTRO_LONG_' .
				strtoupper($languages[$i]['code']);
		}
		
		$keys[] = 'CCCARDS_SURCHARGES_DISCOUNTS_VISA_ELECTRON';
		
		for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
			$keys[] = 'CCCARDS_SURCHARGES_DISCOUNTS_VISA_ELECTRON_SHORT_' .
				strtoupper($languages[$i]['code']);
			$keys[] = 'CCCARDS_SURCHARGES_DISCOUNTS_VISA_ELECTRON_LONG_' .
				strtoupper($languages[$i]['code']);
		}
		
		$keys[] = 'CCCARDS_SURCHARGES_DISCOUNTS_AMERICAN_EXPRESS';
		
		for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
			$keys[] = 'CCCARDS_SURCHARGES_DISCOUNTS_AMERICAN_EXPRESS_SHORT_' .
				strtoupper($languages[$i]['code']);
			$keys[] = 'CCCARDS_SURCHARGES_DISCOUNTS_AMERICAN_EXPRESS_LONG_' .
				strtoupper($languages[$i]['code']);
		}
		
		$keys[] = 'CCCARDS_SURCHARGES_DISCOUNTS_DINERS_CLUB';
		
		for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
			$keys[] = 'CCCARDS_SURCHARGES_DISCOUNTS_DINERS_CLUB_SHORT_' .
				strtoupper($languages[$i]['code']);
			$keys[] = 'CCCARDS_SURCHARGES_DISCOUNTS_DINERS_CLUB_LONG_' .
				strtoupper($languages[$i]['code']);
		}
		
		$keys[] = 'CCCARDS_SURCHARGES_DISCOUNTS_JCB';
		
		for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
			$keys[] = 'CCCARDS_SURCHARGES_DISCOUNTS_JCB_SHORT_' .
				strtoupper($languages[$i]['code']);
			$keys[] = 'CCCARDS_SURCHARGES_DISCOUNTS_JCB_LONG_' .
				strtoupper($languages[$i]['code']);
		}
		
		$keys[] = 'CCCARDS_SURCHARGES_DISCOUNTS_LASER';
		
		for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
			$keys[] = 'CCCARDS_SURCHARGES_DISCOUNTS_LASER_SHORT_' .
				strtoupper($languages[$i]['code']);
			$keys[] = 'CCCARDS_SURCHARGES_DISCOUNTS_LASER_LONG_' .
				strtoupper($languages[$i]['code']);
		}
		
		$keys[] = 'CCCARDS_SURCHARGES_DISCOUNTS_DISCOVER';
		
		for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
			$keys[] = 'CCCARDS_SURCHARGES_DISCOUNTS_DISCOVER_SHORT_' .
				strtoupper($languages[$i]['code']);
			$keys[] = 'CCCARDS_SURCHARGES_DISCOUNTS_DISCOVER_LONG_' .
				strtoupper($languages[$i]['code']);
		}
		
		$remaining_keys = array(
			'CCCARDS_MADE_BY_SOFT'
			);
		
		$keys = array_merge($keys, $remaining_keys);
		
		return $keys;
	}
	 
}
 

?>

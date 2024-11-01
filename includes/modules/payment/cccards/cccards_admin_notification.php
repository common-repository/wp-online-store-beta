<?php

 
if (isset($CCCARDS_result->fields)) { 
	$output = "<td>\n";
	
	// Styles for admin/info page
	$output .= <<< STYLEBLOCK
<style type="text/css">
	#cccards-admin { padding: 0.5em 0; border-top: 1px solid #000; border-bottom: 1px solid #000; }
	#cccards-admin img { border: 0; }
	#cccards-admin p { margin: 0 0 0.8em 0; }
	#cccards-admin h2 { font-size: 1em; margin: 0 0 1em 0; padding: 0; }
	#ceon-logo { float: right; margin: 5px 0 5px 0; }
	
	@media print {
		#cccards-admin { display: none; }
	}
</style>
STYLEBLOCK;
	
	$output .= '<div id="cccards-admin">';
	
	$output .= '<h2>';
	
	$output .= '';
	
	$output .= CCCARDS_ADMIN_TEXT_TITLE . '</h2>' . "\n";
	
	$output .= '<p>' . CCCARDS_ADMIN_TEXT_EMAIL_NOTICE . "</p>\n";
	
	if ((is_null($CCCARDS_result->fields['cc_start']) ||
			strlen($CCCARDS_result->fields['cc_start']) == 0) &&
			(is_null($CCCARDS_result->fields['cc_issue']) ||
			strlen($CCCARDS_result->fields['cc_issue']) == 0)) {
		$output .= '<p>' . CCCARDS_ADMIN_TEXT_NO_START_DATE_OR_ISSUE_NUMBER . "</p>\n";
	} else {
		$output .= '<table border="0" cellspacing="0" cellpadding="2">' . "\n";
		
		if (!is_null($CCCARDS_result->fields['cc_start']) &&
				strlen($CCCARDS_result->fields['cc_start']) > 0) {
			$output .= '<tr><td class="main">' . "\n";
			$output .= CCCARDS_ADMIN_TEXT_START_DATE . "\n";
			$output .= '</td><td class="main">';
			$output .= $CCCARDS_result->fields['cc_start'] . "\n";
			$output .= '</td></tr>' . "\n";
		}
		
		if (!is_null($CCCARDS_result->fields['cc_issue']) &&
				strlen($CCCARDS_result->fields['cc_issue']) > 0) {
			$output .= '<tr><td class="main">' . "\n";
			$output .= CCCARDS_ADMIN_TEXT_ISSUE_NUMBER . "\n";
			$output .= '</td><td class="main">';
			$output .= $CCCARDS_result->fields['cc_issue'] . "\n";
			$output .= '</td></tr>' . "\n";
		}
		
		$output .='</table>' . "\n";
	}
	
	$output .= '</div>';
	
	$output .= "</td>\n";
}

?>
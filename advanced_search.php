<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_ADVANCED_SEARCH);

  $breadcrumb->add(NAVBAR_TITLE_1, tep_href_link(FILENAME_ADVANCED_SEARCH));

  require(DIR_WS_INCLUDES . 'template_top.php');
  
  
  // begin Extra Product Fields
    $epf_query = tep_db_query("select * from " . TABLE_EPF . " e join " . TABLE_EPF_LABELS . " l where e.epf_status and e.epf_advanced_search and (e.epf_id = l.epf_id) and (l.languages_id = " . (int)$languages_id . ") and l.epf_active_for_language order by e.epf_order");
    $epf = array();
    while ($e = tep_db_fetch_array($epf_query)) {
      $field = 'extra_value';
      if ($e['epf_uses_value_list']) {
        if ($e['epf_multi_select']) {
          $field .= '_ms';
        } else {
          $field .= '_id';
        }
      }
      $field .= $e['epf_id'];
      $epf[] = array('id' => $e['epf_id'],
                     'label' => $e['epf_label'],
                     'uses_list' => $e['epf_uses_value_list'],
                     'multi_select' => $e['epf_multi_select'],
                     'use_checkbox' => $e['epf_checked_entry'],
                     'columns' => $e['epf_num_columns'],
                     'display_type' => $e['epf_value_display_type'],
                     'field' => $field);
    }
// end Extra Product Fields
  
  
?>

<script type="text/javascript" src="includes/general.js"></script>
<script type="text/javascript"><!--
function check_form() {
  var error_message = "<?php echo JS_ERROR; ?>";
  var error_found = false;
  var error_field;
  var keywords = document.advanced_search.keywords.value;
  var dfrom = document.advanced_search.dfrom.value;
  var dto = document.advanced_search.dto.value;
  var pfrom = document.advanced_search.pfrom.value;
  var pto = document.advanced_search.pto.value;
  var pfrom_float;
  var pto_float;

//begin Extra Product Fields
  var cat = document.advanced_search.categories_id.value;
  var mfg = document.advanced_search.manufacturers_id.value;
<?php
foreach ($epf as $e) {
  if ($e['multi_select']) {
    echo "  var noneset" . $e['id'] . " = true;\n";
    echo "  var chk" . $e['id'] . " = document.getElementsByName('" . $e['field'] . "[]');\n";
    echo "  for (var i = 0; i < chk" . $e['id'] . ".length; i++) {\n";
    echo "    if (chk" . $e['id'] . "[i].checked) {\n";
    echo "      noneset" . $e['id'] . " = false;\n";
    echo "      break; }\n";
    echo "  }\n";
  } elseif ($e['use_checkbox']) {
    echo "  var chk" . $e['id'] . " = document.getElementById('" . $e['field'] . "_');\n";
    echo "  var anyvalset" . $e['id'] . " = chk" . $e['id'] . ".checked;\n";
  } else {
  	echo '  var epf' . $e['id'] . ' = document.advanced_search.' . $e['field'] . ".value;\n";
	}
}
?>
// end Extra Product Fields

  if ( ((keywords == '') || (keywords.length < 1)) && ((dfrom == '') || (dfrom == '<?php echo DOB_FORMAT_STRING; ?>') || (dfrom.length < 1)) && ((dto == '') || (dto == '<?php echo DOB_FORMAT_STRING; ?>') || (dto.length < 1)) && ((pfrom == '') || (pfrom.length < 1)) && ((pto == '') || (pto.length < 1))
  // begin Extra Product Fields
   && (cat == '') && (mfg == '')
<?php
foreach ($epf as $e) {
  if ($e['multi_select']) {
    echo " && noneset" . $e['id'];
  } elseif ($e['use_checkbox']) {
    echo " && anyvalset" . $e['id'];
  } else {
    $fieldid =  'epf' . $e['id'];
	  echo " && (( $fieldid == '' ) || ($fieldid.length < 1))";
	}
}
?>
// end Extra Product Fields
) {
 error_message = error_message + "* <?php echo ERROR_AT_LEAST_ONE_INPUT; ?>\n";
    error_field = document.advanced_search.keywords;
    error_found = true;
  }

  if (dfrom.length > 0) {
    if (!IsValidDate(dfrom, '<?php echo DOB_FORMAT_STRING; ?>')) {
      error_message = error_message + "* <?php echo ERROR_INVALID_FROM_DATE; ?>\n";
      error_field = document.advanced_search.dfrom;
      error_found = true;
    }
  }

  if (dto.length > 0) {
    if (!IsValidDate(dto, '<?php echo DOB_FORMAT_STRING; ?>')) {
      error_message = error_message + "* <?php echo ERROR_INVALID_TO_DATE; ?>\n";
      error_field = document.advanced_search.dto;
      error_found = true;
    }
  }

  if ((dfrom.length > 0) && (IsValidDate(dfrom, '<?php echo DOB_FORMAT_STRING; ?>')) && (dto.length > 0) && (IsValidDate(dto, '<?php echo DOB_FORMAT_STRING; ?>'))) {
    if (!CheckDateRange(document.advanced_search.dfrom, document.advanced_search.dto)) {
      error_message = error_message + "* <?php echo ERROR_TO_DATE_LESS_THAN_FROM_DATE; ?>\n";
      error_field = document.advanced_search.dto;
      error_found = true;
    }
  }

  if (pfrom.length > 0) {
    pfrom_float = parseFloat(pfrom);
    if (isNaN(pfrom_float)) {
      error_message = error_message + "* <?php echo ERROR_PRICE_FROM_MUST_BE_NUM; ?>\n";
      error_field = document.advanced_search.pfrom;
      error_found = true;
    }
  } else {
    pfrom_float = 0;
  }

  if (pto.length > 0) {
    pto_float = parseFloat(pto);
    if (isNaN(pto_float)) {
      error_message = error_message + "* <?php echo ERROR_PRICE_TO_MUST_BE_NUM; ?>\n";
      error_field = document.advanced_search.pto;
      error_found = true;
    }
  } else {
    pto_float = 0;
  }

  if ( (pfrom.length > 0) && (pto.length > 0) ) {
    if ( (!isNaN(pfrom_float)) && (!isNaN(pto_float)) && (pto_float < pfrom_float) ) {
      error_message = error_message + "* <?php echo ERROR_PRICE_TO_LESS_THAN_PRICE_FROM; ?>\n";
      error_field = document.advanced_search.pto;
      error_found = true;
    }
  }

  if (error_found == true) {
    alert(error_message);
    error_field.focus();
    return false;
  } else {
    return true;
  }
}

function popupWindow(url) {
  window.open(url,'popupWindow','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no,width=450,height=280,screenX=150,screenY=150,top=150,left=150')
}
//--></script>

<h1><?php echo HEADING_TITLE_1; ?></h1>

<?php
  if ($messageStack->size('search') > 0) {
    echo $messageStack->output('search');
  }
?>

<?php echo tep_draw_form('advanced_search', tep_href_link(FILENAME_ADVANCED_SEARCH_RESULT, '', 'NONSSL', false), 'post', 'onsubmit="return check_form(this);"') . tep_hide_session_id(); ?>

<div class="contentContainer">
  <h2><?php echo HEADING_SEARCH_CRITERIA; ?></h2>

  <div class="contentText">
    <div>
      <?php echo tep_draw_input_field('keywords', '', 'style="width: 100%"') . tep_draw_hidden_field('search_in_description', '1'); ?>
    </div>

    <br />

    <div>
      <span><?php echo '<a href="' . tep_href_link(FILENAME_POPUP_SEARCH_HELP) . '" target="_blank" onclick="jQuery(\'#helpSearch\').dialog(\'open\'); return false;">' . TEXT_SEARCH_HELP_LINK . '</a>'; ?></span>
      <span style="float: right;"><?php echo tep_draw_button(IMAGE_BUTTON_SEARCH, 'search', null, 'primary'); ?></span>
    </div>

    <div id="helpSearch" title="<?php echo HEADING_SEARCH_HELP; ?>">
      <p><?php echo TEXT_SEARCH_HELP; ?></p>
    </div>

<script type="text/javascript">
jQuery(document).ready(function () {
jQuery('#helpSearch').dialog({
  autoOpen: false,
  buttons: {
    Ok: function() {
      jQuery(this).dialog('close');
    }
  }
});});
</script>

    <br />

    <table border="0" width="100%" cellspacing="0" cellpadding="2">
    <!--  extra field stars -->
     <tr>
                <td></td>
                <td class="fieldKey"><?php echo TEXT_OPTIONAL; ?></td>
              </tr>
	<tr>
	<!--  extra field ends -->  
        <td class="fieldKey"><?php echo ENTRY_CATEGORIES; ?></td>
        <td class="fieldValue"><?php echo tep_draw_pull_down_menu('categories_id', tep_get_categories(array(array('id' => '', 'text' => TEXT_ALL_CATEGORIES)))); ?></td>
      </tr>
      <tr>
        <td class="fieldKey">&nbsp;</td>
        <td class="smallText"><?php echo tep_draw_checkbox_field('inc_subcat', '1', true) . ' ' . ENTRY_INCLUDE_SUBCATEGORIES; ?></td>
      </tr>
      <tr>
        <td class="fieldKey"><?php echo ENTRY_MANUFACTURERS; ?></td>
        <td class="fieldValue"><?php echo tep_draw_pull_down_menu('manufacturers_id', tep_get_manufacturers(array(array('id' => '', 'text' => TEXT_ALL_MANUFACTURERS)))); ?></td>
      </tr>
      
      <?php
// begin Extra Product Fields
    foreach ($epf as $e) {
?>
              <tr>
                <td class="fieldKey"><?php echo $e['label']; ?></td>
                <td class="fieldValue">
                <?php if ($e['uses_list']) {
                  $epf_values = tep_build_epf_pulldown($e['id'], $languages_id);
                  if ($e['multi_select']) { // multi-select field
                    echo TEXT_FOR_FIELD . tep_draw_radio_field('match' . $e['id'], 'any', true) . TEXT_MATCH_ANY . tep_draw_radio_field('match' . $e['id'], 'all') . TEXT_MATCH_ALL . '</td></tr><tr><td class="fieldKey">' . $e['label'] . '</td><td class="fieldValue">';
                    $col = 0;
                    echo '<table><tr>';
                    foreach ($epf_values as $value) {
                      $col++;
                      if ($col > $e['columns']) {
                        echo '</tr><tr>';
                        $col = 1;
                      }
                      echo '<td>' . tep_draw_checkbox_field($e['field'] . '[]', $value['id'], false, 'id="' . $value['id'] . '"') . '</td><td>' . tep_get_extra_field_list_value($value['id'], false, $e['display_type']) . '<td><td>&nbsp;</td>';
                    }
                    echo '</tr></table>';
                  } else { // single select field
                    $epf_values = array_merge( array(array('id' => '', 'text' => TEXT_ANY_VALUE)), $epf_values);
                    if ($e['use_checkbox']) {
                      $col = 0;
                      echo '<table><tr>';
                      foreach ($epf_values as $value) {
                        $col++;
                        if ($col > $e['columns']) {
                          echo '</tr><tr>';
                          $col = 1;
                        }
                        echo '<td>' . tep_draw_radio_field($e['field'], $value['id'], $value['id'] == '', 'id="' . $e['field'] . '_' . $value['id'] . '"') . '</td><td>' . ($value['id'] == '' ? TEXT_ANY_VALUE : tep_get_extra_field_list_value($value['id'], false, $e['display_type'])) . '<td><td>&nbsp;</td>';
                      }
                      echo '</tr></table>';
                    } else {
                      echo tep_draw_pull_down_menu($e['field'], $epf_values);
                    }
                  }
                } else { // text field
                  echo tep_draw_input_field($e['field'], '', 'style="width: 300px"');
                } ?>
                </td>
              </tr>
              <tr>
                <td colspan="2"><?php echo tep_draw_separator('pixel_silver.gif', '100%', '1'); ?></td>
              </tr>
<?php
} 
// end Extra Product Fields
?>

      
      
      <tr>
        <td class="fieldKey"><?php echo ENTRY_PRICE_FROM; ?></td>
        <td class="fieldValue"><?php echo tep_draw_input_field('pfrom'); ?></td>
      </tr>
      <tr>
        <td class="fieldKey"><?php echo ENTRY_PRICE_TO; ?></td>
        <td class="fieldValue"><?php echo tep_draw_input_field('pto'); ?></td>
      </tr>
      <tr>
        <td class="fieldKey"><?php echo ENTRY_DATE_FROM; ?></td>
        <td class="fieldValue"><?php echo tep_draw_input_field('dfrom', '', 'id="dfrom"'); ?><script type="text/javascript">jQuery(document).ready(function () {jQuery('#dfrom').datepicker({dateFormat: '<?php echo JQUERY_DATEPICKER_FORMAT; ?>', changeMonth: true, changeYear: true, yearRange: '-10:+0'});});</script></td>
      </tr>
      <tr>
        <td class="fieldKey"><?php echo ENTRY_DATE_TO; ?></td>
        <td class="fieldValue"><?php echo tep_draw_input_field('dto', '', 'id="dto"'); ?><script type="text/javascript">jQuery(document).ready(function () {jQuery('#dto').datepicker({dateFormat: '<?php echo JQUERY_DATEPICKER_FORMAT; ?>', changeMonth: true, changeYear: true, yearRange: '-10:+0'});});</script></td>
      </tr>
    </table>
  </div>
</div>

</form>

<?php
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>

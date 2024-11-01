<?php

/*

  $Id$



  osCommerce, Open Source E-Commerce Solutions

  http://www.oscommerce.com



  Copyright (c) 2010 osCommerce



  Released under the GNU General Public License

*/

  if ($messageStack->size('header') > 0) {

    echo '<div class="grid_24">' . $messageStack->output('header') . '</div>';

  }

?>



<div id="wpols_header" class="grid_24">

  <!-- <div id="storeLogo"><?php echo '<a href="' . tep_href_link(FILENAME_DEFAULT) . '">' . tep_image(DIR_WS_IMAGES . 'store_logo.png', STORE_NAME) . '</a>'; ?></div> //-->



 



<script type="text/javascript">

  jQuery("#headerShortcuts").buttonset();

</script>

</div>



<div class="grid_24 ui-widget infoBoxContainer">

  <div class="ui-widget-header infoBoxHeading">

  

  <?php echo '&nbsp;&nbsp;' . $breadcrumb->trail(' &raquo; '); ?>

  

  

  <div id="headerShortcuts" style="margin:0px;">

<?php
//  added for osC info site
		if(SHOW_LOGIN == 'True') {
  echo tep_draw_button(HEADER_TITLE_CART_CONTENTS . ($cart->count_contents() > 0 ? ' (' . $cart->count_contents() . ')' : ''), 'cart', tep_href_link(FILENAME_SHOPPING_CART)) ;

echo       tep_draw_button(HEADER_TITLE_CHECKOUT, 'triangle-1-e', tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL')) ;

if (tep_session_is_registered('customer_id')  &&  (!isset($HTTP_GET_VARS['guest']) && !isset($HTTP_POST_VARS['guest'])) && !$order->customer['is_dummy_account']) { echo       tep_draw_button(HEADER_TITLE_MY_ACCOUNT, 'person', tep_href_link(FILENAME_ACCOUNT, '', 'SSL')); }



  

  if (tep_session_is_registered('customer_id')) {  echo tep_draw_button(HEADER_TITLE_LOGOFF, null, tep_href_link(FILENAME_LOGOFF, '', 'SSL'));
  }
} //  added for osC info site

 ?>
  </div>

  

  </div>

  

   

</div>



<?php

  if (isset($HTTP_GET_VARS['error_message']) && tep_not_null($HTTP_GET_VARS['error_message'])) {

?>

<table border="0" width="100%" cellspacing="0" cellpadding="2">

  <tr class="headerError">

    <td class="headerError"><?php echo htmlspecialchars(stripslashes(urldecode($HTTP_GET_VARS['error_message']))); ?></td>

  </tr>

</table>

<?php

  }



  if (isset($HTTP_GET_VARS['info_message']) && tep_not_null($HTTP_GET_VARS['info_message'])) {

?>

<table border="0" width="100%" cellspacing="0" cellpadding="2">

  <tr class="headerInfo">

    <td class="headerInfo"><?php echo htmlspecialchars(stripslashes(urldecode($HTTP_GET_VARS['info_message']))); ?></td>

  </tr>

</table>

<?php

  }

?>


<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GstickyNU General Public License
*/
?>
<!--[if IE]><script type="text/javascript" src="<?php echo tep_catalog_href_link('ext/flot/excanvas.min.js'); ?>"></script><![endif]-->
<link rel="stylesheet" type="text/css" href="<?php echo tep_catalog_href_link('ext/jquery/ui/redmond/jquery-ui-1.8.6.css'); ?>">
<script type="text/javascript" src="<?php echo tep_catalog_href_link('ext/jquery/jquery-1.4.2.min.js'); ?>"></script>
<script type="text/javascript" src="<?php echo tep_catalog_href_link('ext/jquery/ui/jquery-ui-1.8.6.min.js'); ?>"></script>

<?php
  if (tep_not_null(JQUERY_DATEPICKER_I18N_CODE)) {
?>
<script type="text/javascript" src="<?php echo tep_catalog_href_link('ext/jquery/ui/i18n/jquery.ui.datepicker-' . JQUERY_DATEPICKER_I18N_CODE . '.js'); ?>"></script>
<script type="text/javascript">
$.datepicker.setDefaults($.datepicker.regional['<?php echo JQUERY_DATEPICKER_I18N_CODE; ?>']);
</script>
<?php
  }
?>

<script type="text/javascript" src="<?php echo tep_catalog_href_link('ext/flot/jquery.flot.js'); ?>"></script>
<link rel="stylesheet" type="text/css" href="<?php echo site_url();?>/wp-content/plugins/<?php echo WPOLS_PLUGINS_DIR;?>/admin/includes/stylesheet.css">
<script type="text/javascript" src="<?php echo site_url();?>/wp-content/plugins/<?php echo WPOLS_PLUGINS_DIR;?>/admin/includes/general.js"></script>

<!-- AJAX Attribute Manager -->
<?php require_once( DIR_FS_ADMIN.'attributeManager/includes/attributeManagerHeader.inc.php' )?>
<!-- AJAX Attribute Manager end -->

<link rel="stylesheet" type="text/css" href="<?php echo site_url();?>/wp-content/plugins/<?php echo WPOLS_PLUGINS_DIR;?>/admin/includes/menu.css">

<script type="text/javascript" src="<?php echo site_url();?>/wp-content/plugins/<?php echo WPOLS_PLUGINS_DIR;?>/admin/ckeditor/ckeditor.js"></script> 

<script type="text/javascript" src="<?php echo site_url();?>/wp-content/plugins/<?php echo WPOLS_PLUGINS_DIR;?>/admin/includes/jquery.cluetip.js"></script>
<script type="text/javascript">
$(document).ready(function() {
  $('a.tips').cluetip(
  {
  width: '640px',
     

        closePosition: 'title',sticky: true,dropShadow : false,
  onShow: function(ct, c) {
        /* Everything inside onShow is referred to the <a> so,
         this.href, this.id, this.hash, etc...

        ct & c are reference to the wrapper created 
        by the cluetip plugin.
        ct represent the main container, 
        while c is just the content container
        */
        var src = c.text();
        c.html('<embed ' + 
               'height="390" '+
               'width="640" '+
               'wmode="transparent" '+
               'pluginspage="http://www.adobe.com/go/getflashplayer" '+
               'src="<?php echo HELP_VIDEO;?>" '+
               'type="application/x-shockwave-flash" '+
               '/>');
        }

  
  }
  
  );
  
  
  $('a.text_tips').cluetip(
  {
  width: '640px',
     

        closePosition: 'title',sticky: true,dropShadow : false,
  onShow: function(ct, c) {
        /* Everything inside onShow is referred to the <a> so,
         this.href, this.id, this.hash, etc...

        ct & c are reference to the wrapper created 
        by the cluetip plugin.
        ct represent the main container, 
        while c is just the content container
        */
        var src = c.text();
        c.text('<?php echo HELP_TEXT;?>');
        }

  
  }
  
  );
  
  goOnLoad();
});
</script>
<link rel="stylesheet" type="text/css" href="<?php echo site_url();?>/wp-content/plugins/<?php echo WPOLS_PLUGINS_DIR;?>/admin/includes/jquery.cluetip.css">

<?php require( 'header.php'); ?>

<?php

  if (!tep_session_is_registered('admin')) {
  global $admin;
              $admin = array('id' => 1,
                             'username' => 'oscadmin');
				tep_session_register('admin');
			
  }

  if (tep_session_is_registered('admin')) {
    include( 'column_left.php');
?>
<style>
#contentText {
  margin-left: 0;
}
</style>
<?php	
  } else {
?>

<style>
#contentText {
  margin-left: 0;
}
</style>

<?php
  }
?>

<div id="contentText">

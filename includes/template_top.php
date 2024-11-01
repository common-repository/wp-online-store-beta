<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  $oscTemplate->buildBlocks();

  if (!$oscTemplate->hasBlocks('boxes_column_left')) {
    $oscTemplate->setGridContentWidth($oscTemplate->getGridContentWidth() + $oscTemplate->getGridColumnWidth());
  }

  if (!$oscTemplate->hasBlocks('boxes_column_right')) {
    $oscTemplate->setGridContentWidth($oscTemplate->getGridContentWidth() + $oscTemplate->getGridColumnWidth());
  }
?>

<link rel="stylesheet" type="text/css" href="<?php echo tep_catalog_href_link('ext/jquery/ui/redmond/jquery-ui-1.8.6.css');?>" />

<script type="text/javascript" src="<?php echo tep_catalog_href_link('ext/jquery/ui/jquery-ui-1.8.6.min.js');?>"></script> 

<?php if(ZoomEnabled == "true" && has_filter('the_content','wp_magic_zoom') ) { ?>

<script type="text/javascript" src="<?php echo tep_catalog_href_link('js/jquery.jqzoom-core.js');?>"></script>
<script type="text/javascript" src="<?php echo tep_catalog_href_link('js/jquery.lightbox-0.5.js');?>"></script>


<link rel="stylesheet" type="text/css" href="<?php echo tep_catalog_stylesheet_link('jquery.lightbox-0.5.css');?>" />
<link rel="stylesheet" type="text/css" href="<?php echo tep_catalog_stylesheet_link('jquery.jqzoom.css');?>" />

<?php } ?>

<?php
  if (tep_not_null(JQUERY_DATEPICKER_I18N_CODE)) {
?>
<script type="text/javascript" src='<?php echo tep_catalog_href_link("ext/jquery/ui/i18n/jquery.ui.datepicker-".JQUERY_DATEPICKER_I18N_CODE.".js"); ?>'></script>
<script type="text/javascript">
$.datepicker.setDefaults($.datepicker.regional['<?php echo JQUERY_DATEPICKER_I18N_CODE; ?>']);
</script>
<?php
  }
?>
<script type="text/javascript" src="<?php echo tep_catalog_href_link('ext/jquery/bxGallery/jquery.bxGallery.1.1.min.js');?>"></script>
<link rel="stylesheet" type="text/css" href="<?php echo tep_catalog_href_link('ext/jquery/fancybox/jquery.fancybox-1.3.4.css');?>" />
<script type="text/javascript" src="<?php echo tep_catalog_href_link('ext/jquery/fancybox/jquery.fancybox-1.3.4.pack.js');?>"></script>
<link rel="stylesheet" type="text/css" href="<?php echo tep_catalog_stylesheet_link('ext/'.((stripos(HTML_PARAMS, 'dir="rtl"') !== false) ? 'rtl_' : '').'960_24_col.css');?>" />
<link rel="stylesheet" type="text/css" href="<?php echo tep_catalog_stylesheet_link('stylesheet.css');?>" />


<?php echo $oscTemplate->getBlocks('header_tags'); ?>


<div id="bodyWrapper" class="container_<?php echo $oscTemplate->getGridContainerWidth(); ?>">

<?php require(DIR_WS_INCLUDES . 'header.php'); ?>

<?php
  if ($oscTemplate->hasBlocks('boxes_column_left')) {
?>

<div id="columnLeft" class="grid_<?php echo $oscTemplate->getGridColumnWidth(); ?> pull_<?php echo $oscTemplate->getGridContentWidth(); ?>">
  <?php echo $oscTemplate->getBlocks('boxes_column_left'); ?>
</div>

<?php
  } ?>

<div id="bodyContent" class="grid_<?php echo $oscTemplate->getGridContentWidth(); ?> <?php echo ($oscTemplate->hasBlocks('boxes_column_left') ? 'push_' . $oscTemplate->getGridColumnWidth() : ''); ?>">

<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/
?>

</div> <!-- bodyContent //-->

<?php

  if ($oscTemplate->hasBlocks('boxes_column_right')) {
?>

<div id="columnRight" class="grid_<?php echo $oscTemplate->getGridColumnWidth(); ?>">
  <?php echo $oscTemplate->getBlocks('boxes_column_right'); ?>
</div>

<?php
  }
?>

<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>

</div> <!-- bodyWrapper //-->

<?php echo $oscTemplate->getBlocks('footer_scripts'); 

if(is_page_template ( 'onecolumn-page.php' )){
	?>
	<style type="text/css">
		.container_24 .grid_16{width:63%;}
	</style>
	<?php 
}
?>



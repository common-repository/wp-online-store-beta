<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  $xx_mins_ago = (time() - 900);

  require('includes/application_top.php');

  require(DIR_WS_INCLUDES . 'template_top.php');
?>
    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo tep_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td>
			        <?php
			if($_REQUEST["go"]=="yes") {
			
			$categories_query = tep_db_query("select * from categories");
			while ($categories = tep_db_fetch_array($categories_query)) {
				@unlink(DIR_FS_CATALOG_IMAGES.$categories["categories_image"]);
			}
			tep_db_query("delete  from categories_description");
			tep_db_query("delete  from categories");
			
			$products_query = tep_db_query("select * from products");
			while ($products = tep_db_fetch_array($products_query)) {
				@unlink(DIR_FS_CATALOG_IMAGES.$products["products_image"]);
			}
			
			$products_images_query = tep_db_query("select * from products_images");
			while ($products_images = tep_db_fetch_array($products_images_query)) {
				@unlink(DIR_FS_CATALOG_IMAGES.$products_images["image"]);
			}
			
			tep_db_query("delete  from products");
			tep_db_query("delete  from products_attributes");
			tep_db_query("delete  from products_attributes_download");
			tep_db_query("delete  from products_description");
			tep_db_query("delete  from products_images");
			tep_db_query("delete  from products_to_categories");
			
			tep_db_query("delete  from products_options");
			tep_db_query("delete  from products_options_values");
			tep_db_query("delete  from products_options_values_to_products_options");
			
			
			tep_db_query("delete  from reviews");
			tep_db_query("delete  from reviews_description");
			
			tep_db_query("delete  from manufacturers");
			tep_db_query("delete  from manufacturers_info");
			
			echo HEADING_FORMAT_DONE;
			}
			else {
			 echo HEADING_FORMAT_TEXT; } ?></td>
      </tr>
      
    </table>

<?php
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>

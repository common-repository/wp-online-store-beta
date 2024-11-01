<?php
/*
  $Id$
  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com
  Copyright (c) 2010 osCommerce
  Released under the GNU General Public License
*/

define('DIR_SO_CATALOG_IMAGES', '../../../plugins/WP-online-store-product-images/images/');

  $xx_mins_ago = (time() - 900);
  require('includes/application_top.php');

/// optional parameter to set max products per row:

	$max_cols = 3;

  require(DIR_WS_INCLUDES . 'template_top.php');
?>

<table style="border:none" border="0" width="100%" align="center"><tr><td class="smalltext">

<?php
// we've done nothing cool yet... 

$msg_stack = '' . TEXT_FETCH_DB . '';

 if ($HTTP_POST_VARS['sort_order_update']) {

  //set counter

     $sort = 0;

 // while (list($key, $value) = each($sort_order_update)) {

  while (list($key, $value) = each($HTTP_POST_VARS['sort_order_update'])) {

  // update the products sort order

  if ($value!= '') {

   $update = tep_db_query("UPDATE " . TABLE_PRODUCTS . " SET products_sort_order = $value WHERE products_id = $key");
   $sort_i++;
   }
  }

 $msg_stack = '<br>' . UPDATED_SORT_ORDER . ' ' . $sort_i  . ' ' . UQ_PRODUCTS . '</class>';
 }
?>

		  <tr>
            <td class="pageHeading" align="left"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo tep_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>

<table border="0" width="90%" align="center"><tr><td class="smalltext">
<br><form method="post" action="<?php echo tep_href_link(FILENAME_PRODUCTS_SORTER);?>">

<?php  

 // first select all categories that have 0 as parent:
     $sql = tep_db_query("SELECT c.categories_id, cd.categories_name from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd WHERE c.parent_id = 0 AND c.categories_id = cd.categories_id AND cd.language_id = $languages_id");

       echo '<table border="0" >';
        while ($parents = tep_db_fetch_array($sql)) {

           // check if the parent has products

           $check = tep_db_query("SELECT products_id FROM " . TABLE_PRODUCTS_TO_CATEGORIES . " WHERE categories_id = '" . $parents['categories_id'] . "'");
	   if (tep_db_num_rows($check) > 0) {
              $tree = tep_get_category_tree();
              $dropdown= tep_draw_pull_down_menu('cat_id', $tree, '', 'onChange="this.form.submit();"'); //single
              $all_list = '<form method="post" action="'.tep_href_link(FILENAME_PRODUCTS_SORTER).'"><tr><th class="smallText" align="left" valign="top">' . TEXT_ALL_CATEGORIES . '</th><td>' . $dropdown . '</td></tr></form>';
           } else {
           // get the tree for that parent
              $tree = tep_get_category_tree($parents['categories_id']);

             // draw a dropdown with it:

				$dropdown = tep_draw_pull_down_menu('cat_id', $tree, '', 'onChange="this.form.submit();"');

                $list .= '<form method="post" action="'.tep_href_link(FILENAME_PRODUCTS_SORTER).'"><tr><th class="smallText" align="left" valign="top">' . $parents['categories_name'] . '</th><td>' . $dropdown . '</td></tr></form>';
        }
       }
       echo $list . $all_list . '</form></tr></table><p>';

// see if there is a category ID:
 if ($HTTP_POST_VARS['cat_id']) {

// start the table
      echo '<form method="post" action="'.tep_href_link(FILENAME_PRODUCTS_SORTER).'"><table border="0" width="700"><tr>';

       $i = 0;

      // get all active prods in that specific category

       $sql2 = tep_db_query("SELECT p.products_id, p.products_model, p. products_quantity, p.products_status, p.products_sort_order, p.products_image, pd.products_name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " ptc, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_id = ptc.products_id and p.products_id = pd.products_id and language_id = $languages_id and ptc.categories_id = '" . $HTTP_POST_VARS['cat_id'] . "'");

     while ($results = tep_db_fetch_array($sql2)) {
           $i++;

/*             echo '<td class="main" align="center">' . tep_image(DIR_WS_CATALOG . DIR_WS_IMAGES . $results['products_image'], 'ID  ' . $results['products_id'] . ': ' . $results['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '<br>';*/
			 
			              echo '<td class="main" align="center">' . tep_image(DIR_SO_CATALOG_IMAGES . $results['products_image'], 'ID  ' . $results['products_id'] . ': ' . $results['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '<br>';

             echo '<font size="1" color="#ff0000"><b>' . $results['products_model'] . '</b></font><br>' . $results['products_name'] . '<br>';

			 echo '<input type="text" size="3" name="sort_order_update[' . $results['products_id'] . ']" value="' . $results['products_sort_order'] . '">';

             echo '</i></td>';

          if ($i == $max_cols) {
               echo '</tr><tr>';
               $i =0;
         }
    }

  echo '<input type="hidden" name="cat_id" value="' . $HTTP_POST_VARS['cat_id'] . '">';
  echo '</tr><td class="smalltext" align="center" colspan="10"><br><br><br><br>';
  echo tep_image_submit('button_update.gif', IMAGE_UPDATE) . '</td></tr><td class="main" colspan="30" align="left"><br><b>' . LAST_ACTION . '</b><br>' . $msg_stack . '</b></font></td></tr></form>';
  } 
?>
    </tr></table>
  </td>
</tr></table>

<?php
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
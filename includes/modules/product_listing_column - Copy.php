<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/
  $listing_split = new splitPageResults($listing_sql, MAX_DISPLAY_SEARCH_RESULTS, 'p.products_id');
  
    $cols = $_GET['cols'];
    $w = SMALL_IMAGE_WIDTH;
    $h =SMALL_IMAGE_HEIGHT;
	  if($cols=="")
	  $cols = 2;
	  
	   if($h=="")
	  $h = 200;
	  
	  if($w=="")
	  $w = 200;
  
?>
<style>
 .featured-area {
	width:100%;
 
 
	
}

.featured-thumb {

 width: <?php echo $w+1?>px;
 height: <?php echo $h+70;?>px;
 margin-right: 2px;
 margin-bottom:3px;
 display:inline;
 position:relative;
 padding:10px;

}


.borderdiv{
 border:1px dotted #D5D5D5;
	
}
 
		
.thumb {
	 
	float:left;
	font-size: 10px;
 
 
	
	}	
	
 
 
 </style>
 


  <div class="contentText">
 <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
        <td><?php
  if ( ($listing_split->number_of_rows > 0) && ( (PREV_NEXT_BAR_LOCATION == '1') || (PREV_NEXT_BAR_LOCATION == '3') ) ) {
?>

    <div>
      <span style="float: right;"><?php echo TEXT_RESULT_PAGE . ' ' . $listing_split->display_links(MAX_DISPLAY_PAGE_LINKS, tep_get_all_get_params(array('page', 'info', 'x', 'y'))); ?></span>

      <span><?php echo $listing_split->display_count(TEXT_DISPLAY_NUMBER_OF_PRODUCTS); ?></span>
    </div>

    <br />

<?php
  }
  ?>
        </td>
    </tr>
    <tr>
        <td>
		<?php

	
    $sort_orders = array();
    $sort_orders[] = array('id' => '', 'text' => 'Sort By');
    $sort_orders[] = array('id' => '5a', 'text' => 'Sort Order');
//	$sort_orders[] = array('id' => '4a', 'text' => 'Most Popular');
  
    $sort_orders[] = array('id' => '3a', 'text' => 'Price Low To High');
    $sort_orders[] = array('id' => '3d', 'text' => 'Price High To Low');
    $sort_orders[] = array('id' => '2a', 'text' => 'Product Name');


    $hidden_get_variables = '';
    reset($_GET);
    while (list($key, $value) = each($_GET)) {
      if ( ($key != 'sort') && ($key != tep_session_name()) && ($key != 'x') && ($key != 'y') ) {
        $hidden_get_variables .= tep_draw_hidden_field($key, $value);
      }
    }
     
	 		
		 $prod_list_contents = '<div class="ui-widget infoBoxContainer">' .
                        '  <div class="ui-widget-header ui-corner-top infoBoxHeading">' .
                        '    <table border="0" width="100%" cellspacing="0" cellpadding="2" class="productListingHeader">' .
                        '      <tr>';

  
 		 $prod_list_contents 	.= '        <td align="left" >'.    tep_draw_form('sort_by', tep_href_link(basename($PHP_SELF), tep_get_all_get_params()), 'get').
      								tep_draw_pull_down_menu('sort', $sort_orders, $HTTP_GET_VARS['sort'], 'onChange="this.form.submit();"').
     						 		$hidden_get_variables.'</form></td>';
// optional Product List Filter
    if (PRODUCT_LIST_FILTER > 0) {
      if (isset($HTTP_GET_VARS['manufacturers_id'])) {
        $filterlist_sql = "select distinct c.categories_id as id, cd.categories_name as name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where p.products_status = '1' and p.products_id = p2c.products_id and p2c.categories_id = c.categories_id and p2c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "' and p.manufacturers_id = '" . (int)$HTTP_GET_VARS['manufacturers_id'] . "' order by cd.categories_name";
      } else {
        $filterlist_sql= "select distinct m.manufacturers_id as id, m.manufacturers_name as name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . TABLE_MANUFACTURERS . " m where p.products_status = '1' and p.manufacturers_id = m.manufacturers_id and p.products_id = p2c.products_id and p2c.categories_id = '" . (int)$current_category_id . "' order by m.manufacturers_name";
      }
      $filterlist_query = tep_db_query($filterlist_sql);
      if (tep_db_num_rows($filterlist_query) > 1) {
          $prod_list_contents_filter .= tep_draw_form('filter', '', 'post') . ' ' . TEXT_SHOW . '&nbsp;';
        if (isset($HTTP_GET_VARS['manufacturers_id'])) {
          $prod_list_contents_filter .= tep_draw_hidden_field('manufacturers_id', $HTTP_GET_VARS['manufacturers_id']);
          $options = array(array('id' => '', 'text' => TEXT_ALL_CATEGORIES));
        } else {
          $prod_list_contents_filter .= tep_draw_hidden_field('cPath', $cPath);
          $options = array(array('id' => '', 'text' => TEXT_ALL_MANUFACTURERS));
        }
        $prod_list_contents_filter .= tep_draw_hidden_field('sort', $HTTP_GET_VARS['sort']);
        while ($filterlist = tep_db_fetch_array($filterlist_query)) {
          $options[] = array('id' => $filterlist['id'], 'text' => $filterlist['name']);
        }
        $prod_list_contents_filter .= tep_draw_pull_down_menu('filter_id', $options, (isset($HTTP_GET_VARS['filter_id']) ? $HTTP_GET_VARS['filter_id'] : ''), 'onchange="this.form.submit()"');
        $prod_list_contents_filter .= tep_hide_session_id() . ' </form> ' . "\n";
      }
    }
										
	  	 $prod_list_contents 	.= '<td align="left" nowrap>'.$prod_list_contents_filter.'</td>';
  $prod_list_contents .= '        <td  style="text-align:right;"><strong><a href="'. tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('view')) . 'view=grid', $request_type).'">Grid</a> <a href="'. tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('view')) . 'view=list', $request_type).'">List</a> View</strong></td>';
  
  		$prod_list_contents .= '      </tr>' .
                         '    </table>' .
                         '  </div>';
	 $prod_list_contents .= '  <div class="ui-widget-content ui-corner-bottom productListTable">' .
                           '    <table border="0" width="100%" cellspacing="0" cellpadding="2" class="productListingData"><tr><td>
         
		<div class="featured-area" >' ;				 
		 
 
   
$num_new_products = $listing_split->number_of_rows;
  if ($listing_split->number_of_rows > 0) {
    $counter = 0;
    $col = 1;
    $listing_query = tep_db_query($listing_split->sql_query);

    // $new_prods_content = '<table border="0" width="100%" cellspacing="0" cellpadding="2">';
    while ($listing = tep_db_fetch_array($listing_query)) {
	
      $prod_list_contents .= ' <div class="thumb featured-thumb borderdiv"> 
	  						     
                                
								<a href="' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $listing['products_id']) . '">' . tep_image(DIR_WS_IMAGES . $listing['products_image'], $listing['products_name'], $w, $h) . '</a><br /><a href="' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $listing['products_id']) . '">' . $listing['products_name'] . '</a>'. '<br />' . $currencies->display_price($listing['products_price'], tep_get_tax_rate($listing['products_tax_class_id'])).'<a href="'. tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $listing['products_id']).'"><span style="float:left;"><br>'.tep_draw_button(IMAGE_BUTTON_BUY_NOW, 'cart', tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('action')) . 'action=buy_now&products_id=' . $listing['products_id'])) .'</span></a>
							  </div> ';

     

      if (($col % $cols == 0) ) {
        $prod_list_contents .= '</div>
							<div class="featured-area">';

         
      }
	   $col ++;
    }

   
	?>		
      

 
   
	 <?php $prod_list_contents .= '</div> ';
	  $prod_list_contents .= '    </td></tr></table>' .
                           '  </div>' .
                           '</div>';
	  echo $prod_list_contents;
   
?>

<?   
  } else {
?>

<table width="100%" border="0" align="left" cellpadding="0" cellspacing="0">
        
        <tr>
          <td><table width="100%" border="0" align="left" cellpadding="0" cellspacing="0">
            <tr>
              <td width="568" align="left" valign="top"><p><?php echo TEXT_NO_PRODUCTS; ?></p></td>
            </tr>
          </table></td>
        </tr>
       
      </table>
    

<?php
  }
?>
 
        </td>
    </tr>
    <tr>
        <td>
<?
  if ( ($listing_split->number_of_rows > 0) && ((PREV_NEXT_BAR_LOCATION == '2') || (PREV_NEXT_BAR_LOCATION == '3')) ) {
?>

    <br />

    <div>
      <span style="float: right;"><?php echo TEXT_RESULT_PAGE . ' ' . $listing_split->display_links(MAX_DISPLAY_PAGE_LINKS, tep_get_all_get_params(array('page', 'info', 'x', 'y'))); ?></span>

      <span  style="float: left;"><?php echo $listing_split->display_count(TEXT_DISPLAY_NUMBER_OF_PRODUCTS); ?></span>
    </div>


<?php
  }
?>


        </td>
    </tr>
</table>

  </div>
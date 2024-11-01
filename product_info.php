<?php

/*

  $Id$



  osCommerce, Open Source E-Commerce Solutions

  http://www.oscommerce.com



  Copyright (c) 2010 osCommerce



  Released under the GNU General Public License

*/



  require('includes/application_top.php');



  require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_PRODUCT_INFO);



  $product_check_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_status = '1' and p.products_id = '" . (int)$HTTP_GET_VARS['products_id'] . "' and pd.products_id = p.products_id and pd.language_id = '" . (int)$languages_id . "'");

  $product_check = tep_db_fetch_array($product_check_query);



  // begin Extra Product Fields

  $epf = array();

  if ($product_check['total'] > 0) {

    $epf_query = tep_db_query("select * from " . TABLE_EPF . " e join " . TABLE_EPF_LABELS . " l where e.epf_status and (e.epf_id = l.epf_id) and (l.languages_id = " . (int)$languages_id . ") and l.epf_active_for_language order by epf_order");

    while ($e = tep_db_fetch_array($epf_query)) {  // retrieve all active extra fields

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

                     'columns' => $e['epf_num_columns'],

                     'display_type' => $e['epf_value_display_type'],

                     'show_chain' => $e['epf_show_parent_chain'],

                     'search' => $e['epf_advanced_search'],

                     'keyword' => $e['epf_use_as_meta_keyword'],

                     'field' => $field);

    }

    $query = "select p.products_date_added, p.products_last_modified, pd.products_name";

    foreach ($epf as $e) {

      if ($e['keyword']) $query .= ", pd." . $e['field'];

    }

    $query .= " from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_status = '1' and p.products_id = '" . (int)$HTTP_GET_VARS['products_id'] . "' and pd.products_id = p.products_id and pd.language_id = '" . (int)$languages_id . "'";

    $pname = tep_db_fetch_array(tep_db_query($query));

    $datemod = substr((tep_not_null($pname['products_last_modified']) ? $pname['products_last_modified'] : $pname['products_date_added']), 0, 10);

  } else {

    $pname = TEXT_PRODUCT_NOT_FOUND;

    $datemod = date('Y-m-d');

  }

// end Extra Product Fields

  

  

   require(DIR_WS_INCLUDES . 'template_top.php'); // epf change

  

  //  require(DIR_WS_INCLUDES . 'template_top_extra_fields.php');



  if ($product_check['total'] < 1) {

?>



<div class="contentContainer">

  <div class="contentText">

    <?php echo TEXT_PRODUCT_NOT_FOUND; ?>

  </div>



  <div style="float: right;">

    <?php echo tep_draw_button(IMAGE_BUTTON_CONTINUE, 'triangle-1-e', tep_href_link(FILENAME_DEFAULT)); ?>

  </div>

</div>



<?php

  } else {

    // $product_info_query = tep_db_query("select p.products_id, pd.products_name, pd.products_description, p.products_model, p.products_quantity, p.products_image, pd.products_url, p.products_price, p.products_tax_class_id, p.products_date_added, p.products_date_available, p.manufacturers_id from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_status = '1' and p.products_id = '" . (int)$HTTP_GET_VARS['products_id'] . "' and pd.products_id = p.products_id and pd.language_id = '" . (int)$languages_id . "'");

    

  // begin Product Extra Fields

    $query = "select p.products_id, pd.products_name, pd.products_description, p.products_model, p.products_quantity, p.products_image, pd.products_url, p.products_price, p.products_tax_class_id, p.products_date_added, p.products_date_available, p.manufacturers_id";

    foreach ($epf as $e) {

      $query .= ", pd." . $e['field'];

    }

    $query .= " from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_status = '1' and p.products_id = '" . (int)$HTTP_GET_VARS['products_id'] . "' and pd.products_id = p.products_id and pd.language_id = '" . (int)$languages_id . "'";

    $product_info_query = tep_db_query($query);

    // end Product Extra Fields



  

  

    $product_info = tep_db_fetch_array($product_info_query);



    tep_db_query("update " . TABLE_PRODUCTS_DESCRIPTION . " set products_viewed = products_viewed+1 where products_id = '" . (int)$HTTP_GET_VARS['products_id'] . "' and language_id = '" . (int)$languages_id . "'");



    if ($new_price = tep_get_products_special_price($product_info['products_id'])) {

      $products_price = '<del>' . $currencies->display_price($product_info['products_price'], tep_get_tax_rate($product_info['products_tax_class_id'])) . '</del> <span class="productSpecialPrice">' . $currencies->display_price($new_price, tep_get_tax_rate($product_info['products_tax_class_id'])) . '</span>';

    } else {

      $products_price = $currencies->display_price($product_info['products_price'], tep_get_tax_rate($product_info['products_tax_class_id']));

    }



    if (tep_not_null($product_info['products_model'])) {

      $products_name = stripslashes($product_info['products_name']) . '<br /><span class="smallText">[' . $product_info['products_model'] . ']</span>';

    } else {

      $products_name = stripslashes($product_info['products_name']);

    }

?>



<?php if(ZoomEnabled == "true" && has_filter('the_content','wp_magic_zoom') ) {

			 

		?>

<style type="text/css">

			.clearfix:after{clear:both;content:".";display:block;font-size:0;height:0;line-height:0;visibility:hidden;}

			.clearfix{display:block;zoom:1}





			ul#thumblist{display:block;}

			ul#thumblist li{float:left;margin-right:2px;list-style:none;}

			ul#thumblist li a{display:block;border:1px solid #CCC;}

			ul#thumblist li a.zoomThumbActive{

			border:1px solid red;

			}



			.jqzoom{



			text-decoration:none;

			float:left;

			}



			.mouse_image_over  



			{



			height: 16px;



			width: 123px;



            background-image: url("<?php echo HTTP_SERVER.DIR_WS_ICONS;?>zoom_icon.jpg");





			background-repeat: no-repeat;



			background-position: left top;



			margin-top: 10px;



			padding-left: 22px;



			text-align: left;



			line-height: 16px;



			font-size: 10px;



			font-family: Arial,Helvetica,sans-serif;



			}







			</style>

			<style type="text/css">

			/* jQuery lightBox plugin - Gallery style */

			#gallery {

				/*background-color: #444;*/

				 

				 

			}

			#gallery ul { list-style: none; }

			#gallery ul li { display: inline; }

			#gallery ul img {

				border: 5px solid #3e3e3e;

				 

			}

			#gallery ul a:hover img {

				border: 5px solid #fff;

				 

				color: #fff;

			}

			#gallery ul a:hover { color: #fff; }

	

			</style>

			<script type="text/javascript">

			

			

			

			

			jQuery(document).ready(function() {

				//

				 jQuery.noConflict();

			  jQuery('#gallery').lightBox({

					overlayBgColor: '#FFF',

					overlayOpacity: 0.6,

					imageLoading: '<?php echo HTTP_SERVER.DIR_WS_ICONS;?>lightbox-ico-loading.gif',

					imageBtnClose: '<?php echo HTTP_SERVER.DIR_WS_ICONS;?>lightbox-btn-close.gif',

					imageBtnPrev: '<?php echo HTTP_SERVER.DIR_WS_ICONS;?>lightbox-btn-prev.gif',

					imageBtnNext: '<?php echo HTTP_SERVER.DIR_WS_ICONS;?>lightbox-ico-next.gif',

					containerResizeSpeed: 350,

					txtImage: 'Imagem',

					txtOf: 'de'

				   });

					var options1 = {

							zoomType: "<?php echo zoomType?>",

							zoomWidth: <?php echo zoomWidth?>,

							zoomHeight: <?php echo zoomHeight?>,

							xOffset: <?php echo xOffset?>,

							yOffset: <?php echo yOffset?>,

							position: "<?php echo position?>",

							preloadImages: false,

							preloadText: "<?php echo preloadText?>",

							title: <?php echo title?>,

							lens: <?php echo lens?>,

							imageOpacity: <?php echo imageOpacity?>,

							showEffect: "<?php echo showEffect?>",

							hideEffect: "<?php echo hideEffect?>",

							fadeinSpeed: "<?php echo fadeinSpeed?>",

							fadeoutSpeed: "<?php echo fadeoutSpeed?>" 

							 

				//lens:false



							};

				jQuery(".jqzoom").jqzoom(options1);			

							

			});

			</script> 

		<?php } ?>	


 
<?php echo tep_draw_form('cart_quantity', tep_href_link(FILENAME_PRODUCT_INFO, tep_get_all_get_params(array('action')) . 'action=add_product'), 'post', 'enctype="multipart/form-data"'); ?>


<div>

  <h1 style="float: right;"><?php echo $products_price; ?></h1>

  <h1><?php echo stripslashes($products_name); ?></h1>

</div>



<div class="contentContainer">

  <div class="contentText">



<?php

    if (tep_not_null($product_info['products_image'])) {

      $pi_query = tep_db_query("select image, htmlcontent from " . TABLE_PRODUCTS_IMAGES . " where products_id = '" . (int)$product_info['products_id'] . "' order by sort_order");



      if (tep_db_num_rows($pi_query) > 0) {

?>



    <div id="piGal" style="float: right;">

     

<?php

        $pi_counter = 0;

        while ($pi = tep_db_fetch_array($pi_query)) {

	//	  echo 	$pi['image'];

          $pi_counter++;



          $pi_entry = '        <li><a href="';



          if (tep_not_null($pi['htmlcontent'])) {

            $pi_entry .= '#piGalimg_' . $pi_counter;

          } else {

            $pi_entry .= HTTP_SERVER.DIR_WS_IMAGES . $pi['image'];

          }



          $pi_entry .= '" target="_blank" rel="fancybox">' . tep_image(DIR_WS_IMAGES . $pi['image']) . '</a>';



          if (tep_not_null($pi['htmlcontent'])) {

            $pi_entry .= '<div style="display: none;"><div id="piGalimg_' . $pi_counter . '">' . $pi['htmlcontent'] . '</div></div>';

          }

		$pi_entry .= '</li>';

		

		

		if(ZoomEnabled == "true" && has_filter('the_content','wp_magic_zoom') ) {

				if($pi_counter ==  1){

				 

				?>

			<div class="clearfix" style="text-align:center; float:right; padding-right:10px;" >

			

			<a href="<?php echo DIR_WS_IMAGES . $pi['image'];?>" class="jqzoom" rel='gal1' id="gallery"  title="<?php echo stripslashes($product_info['products_name'])?>" >

			   <?php echo tep_image(DIR_WS_IMAGES . $pi['image'],stripslashes($product_info['products_name']),"200","","");?>

			</a> 

		 

		</div>

		 <div style="clear: both;"></div>

		<div style="text-align:center;" ><span class="mouse_image_over">	Mouse over to zoom in</span></div>

			  

		 <div style="clear: both;"></div>

			 <div class="clearfix" style="text-align:left; float:right;" >

		<ul id="thumblist" class="clearfix" >	

				 <li><a class="zoomThumbActive" href='javascript:void(0);' rel="{gallery: 'gal1', smallimage: '<?php echo DIR_WS_IMAGES . $pi['image']?>',largeimage: '<?php echo DIR_WS_IMAGES . $pi['image']?>'}"><?php echo tep_image(DIR_WS_IMAGES . $pi['image'],stripslashes($product_info['products_name']),"50","","")?></a></li>

				 

				<?

				} else {

				

				

			 ?>

			 

			<li><a   href='javascript:void(0);' rel="{gallery: 'gal1', smallimage: '<?php echo DIR_WS_IMAGES . $pi['image']?>',largeimage: '<?php echo DIR_WS_IMAGES . $pi['image']?>'}"><?php echo tep_image(DIR_WS_IMAGES . $pi['image'],stripslashes($product_info['products_name']),"50","","")?></a></li>

		 

			 <?php 

					if($pi_counter % 4==0  )

					{

					echo '</ul>

				<ul id="thumblist" class="clearfix" >';

					}

				}

		  }	 else {

		  if($pi_counter ==  1)

				echo "<ul>";

		 echo $pi_entry;

		  }

        }

?>



      </ul>

      <?php

 

	if(ZoomEnabled == "true" && has_filter('the_content','wp_magic_zoom') ){

?>

 </div>

 <?php }?>

    </div>

<?php

 

	if(ZoomEnabled == "false" || !has_filter('the_content','wp_magic_zoom')){

?>

<script type="text/javascript">

jQuery('#piGal ul').bxGallery({

  maxwidth: 300,

  maxheight: 200,

  thumbwidth: <?php echo (($pi_counter > 1) ? '75' : '0'); ?>,

  thumbcontainer: 300,

  load_image: 'wp-content/plugins/<?php echo basename(dirname(DIR_WS_HTTP_CATALOG.FILENAME_PRODUCT_INFO));?>/ext/jquery/bxGallery/spinner.gif'

});

</script>

<?php }?>









<?php

      } else {

?>



    <div id="piGal" style="float: right;">

      <?php  if(ZoomEnabled == "false" || !has_filter('the_content','wp_magic_zoom') ){  

      echo '<a href="' . HTTP_SERVER.DIR_WS_IMAGES . $product_info['products_image'] . '" target="_blank" rel="fancybox">' . tep_image(DIR_WS_IMAGES . $product_info['products_image'], stripslashes($product_info['products_name']), null, null, 'hspace="5" vspace="5"') . '</a>'; } 

      else { ?>

      <div class="clearfix">

        <a href="<?php echo DIR_WS_IMAGES . $product_info['products_image'];?>" class="jqzoom" rel='gal1' id="gallery" title="triumph" >

        



  <?php echo tep_image(DIR_WS_IMAGES . $product_info['products_image'], stripslashes($product_info['products_name']), 150, null, 'hspace="5" vspace="5"');?>

        </a>

	 

    </div>

<?php } ?>

    </div>



<?php

      }

     ?>

<?php

 

	if(ZoomEnabled == "false" || !has_filter('the_content','wp_magic_zoom') ){

?>

<script type="text/javascript">

jQuery("#piGal a[rel^='fancybox']").fancybox({

  cyclic: true

});















</script>

<?php }?>

<?php

    }

    ?>







<?php echo stripslashes($product_info['products_description']); ?>

<?php 

  // begin Extra Product Fields

  foreach ($epf as $e) {

    $mt = ($e['uses_list'] && !$e['multi_select'] ? ($product_info[$e['field']] == 0) : !tep_not_null($product_info[$e['field']]));

    if (!$mt) { // only display if information is set for product

      echo '<b>' . $e['label'] . ': </b>';

      if ($e['uses_list']) {

        if ($e['multi_select']) {

          $values = explode('|', trim($product_info[$e['field']], '|'));

          $listing = array();

          foreach ($values as $val) {

            $listing[] = tep_get_extra_field_list_value($val, $e['show_chain'], $e['display_type']);

          }

          echo implode(', ', $listing);

        } else {

          echo tep_get_extra_field_list_value($product_info[$e['field']], $e['show_chain'], $e['display_type']);

        }

      } else {

        echo $product_info[$e['field']];

      }

      echo '<br>';

    }

  }

  // end Extra Product Fields

?>

<?php
//BOF - Zappo - Option Types v2 - ONE LINE - Initialize $number_of_uploads
    $number_of_uploads = 0;
    $products_attributes_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_ATTRIBUTES . " patrib where patrib.products_id='" . (int)$HTTP_GET_VARS['products_id'] . "' and patrib.options_id = popt.products_options_id and popt.language_id = '" . (int)$languages_id . "'");
    $products_attributes = tep_db_fetch_array($products_attributes_query);
    if ($products_attributes['total'] > 0) {
?>
          <table border="0" cellspacing="0" cellpadding="2">
            <tr>
              <td class="main" colspan="2"><?php echo TEXT_PRODUCT_OPTIONS; ?></td>
            </tr>
<?php
//BOF - Zappo - Option Types v2 - Add extra Option Values to Query && Placed Options in new file: option_types.php
      $products_options_name_query = tep_db_query("select distinct popt.products_options_id, popt.products_options_name, popt.products_options_type, popt.products_options_length, popt.products_options_comment from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_ATTRIBUTES . " patrib where patrib.products_id='" . (int)$product_info['products_id'] . "' and patrib.options_id = popt.products_options_id and popt.language_id = '" . (int)$languages_id . "' order by popt.products_options_sort_order, popt.products_options_name");
      while ($products_options_name = tep_db_fetch_array($products_options_name_query)) {

        // - Zappo - Option Types v2 - Include option_types.php - Contains all Option Types, other than the original Drowpdown...
        include(DIR_WS_MODULES . 'option_types.php');

        if ($Default == true) {  // - Zappo - Option Types v2 - Default action is (standard) dropdown list. If something is not correctly set, we should always fall back to the standard.
        $products_options_array = array();
        $products_options_query = tep_db_query("select pov.products_options_values_id, pov.products_options_values_name, pa.options_values_price, pa.price_prefix from " . TABLE_PRODUCTS_ATTRIBUTES . " pa, " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov where pa.products_id = '" . (int)$HTTP_GET_VARS['products_id'] . "' and pa.options_id = '" . (int)$ProdOpt_ID . "' and pa.options_values_id = pov.products_options_values_id and pov.language_id = '" . (int)$languages_id . "' order by pa.products_options_sort_order");
        while ($products_options = tep_db_fetch_array($products_options_query)) {
          $products_options_array[] = array('id' => $products_options['products_options_values_id'], 'text' => $products_options['products_options_values_name']);
          if ($products_options['options_values_price'] != '0') {
            $products_options_array[sizeof($products_options_array)-1]['text'] .= ' (' . $products_options['price_prefix'] . $currencies->display_price($products_options['options_values_price'], tep_get_tax_rate($product_info['products_tax_class_id'])) .') ';
          }
        }

        if (isset($cart->contents[$HTTP_GET_VARS['products_id']]['attributes'][$products_options_name['products_options_id']])) {
          $selected_attribute = $cart->contents[$HTTP_GET_VARS['products_id']]['attributes'][$products_options_name['products_options_id']];
        } else {
          $selected_attribute = false;
        }
?>
            <tr>
              <td class="main"><?php echo $ProdOpt_Name . ':'; ?></td>
              <td class="main"><?php echo tep_draw_pull_down_menu('id[' . $ProdOpt_ID . ']', $products_options_array, $selected_attribute) . ' &nbsp; ' . $ProdOpt_Comment;  ?></td>
            </tr><?php
	  } // End if Default=true
  }
//EOF - Zappo - Option Types v2 - Add extra Option Values to Query && Placed Options in new file: option_types.php
?>
          </table>
<?php
    }
?>

<?php

		// added for osC info site

		if (SHOW_BUTTON == 'True') { ?>

    <div class="buttonSet">

     <?php 

	///  product quantity

                                                $product_quantity = tep_get_products_stock($_GET['products_id']);

                                                if ($product_quantity > MAX_QTY_IN_CART) { $product_quantity = MAX_QTY_IN_CART ; } ;

                                                $products_quantity_array = array();

                                                for ($i=1; $i<=$product_quantity; $i++) {

                                                $products_quantity_array[]=array('id' => $i, 'text' => $i); };

echo tep_draw_hidden_field('products_id', $product_info['products_id']) . TEXT_PRODUCT_QUANTITY . tep_draw_input_field('cart_quantity','','size="4"') . '&nbsp;&nbsp;' . tep_draw_button(IMAGE_BUTTON_IN_CART, 'cart', null, 'primary'); 

 ?></div>

<!-- product quantity -->

    <?php }

	?>

    <div style="clear: both;"></div>



<?php

    if ($product_info['products_date_available'] > date('Y-m-d H:i:s')) {

?>



    <p style="text-align: center;"><?php echo sprintf(TEXT_DATE_AVAILABLE, tep_date_long($product_info['products_date_available'])); ?></p>



<?php

    }

?>

	

  </div>



<?php

    $reviews_query = tep_db_query("select count(*) as count from " . TABLE_REVIEWS . " where products_id = '" . (int)$HTTP_GET_VARS['products_id'] . "' and reviews_status = 1");

    $reviews = tep_db_fetch_array($reviews_query);

?>



  <div class="buttonSet">

		<?php

		

			if(MODULE_BOXES_REVIEWS_STATUS == 'True' || SHOW_REVIEWS == 'True')   // added for osC info site 

				echo tep_draw_button(IMAGE_BUTTON_REVIEWS . (($reviews['count'] > 0) ? ' (' . $reviews['count'] . ')' : ''), 'comment', tep_href_link(FILENAME_PRODUCT_REVIEWS, tep_get_all_get_params())); ?>

	

  </div><br /><br /><br />   <?php

   if ($product_check['total'] >= 1) {

      include (DIR_WS_INCLUDES . 'products_next_previous.php');

   }

   ?>



<?php



    if ((USE_CACHE == 'true') && empty($SID)) {

      echo tep_cache_also_purchased(3600);

    } else {

      include(DIR_WS_MODULES . FILENAME_ALSO_PURCHASED_PRODUCTS);

    }

?>



</div>


<?php echo tep_draw_hidden_field('number_of_uploads', $number_of_uploads); ?>
</form>



<?php

  }



  require(DIR_WS_INCLUDES . 'template_bottom.php');

  require(DIR_WS_INCLUDES . 'application_bottom.php');

?>
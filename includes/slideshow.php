<?php



/*



  $Id: slideshow.php,v 1.0 2011/03/12 14:00 parorrey Exp $







  Author: Ali Qureshi - PI Media



  http://www.parorrey.com







  Copyright (c) 2011 PI Media







  Released under the GNU General Public License



*/







// Returns slideshow



// TABLES: slideshow



  function tep_get_slideshow($output='ul', $width, $height) {



	  



 $slides_query = tep_db_query("select slideshow_name, slideshow_url, slideshow_description, slideshow_image FROM " . TABLE_SLIDESHOW . " order by slideshow_id");



 



	$slides_array = array();



	 $slides_ul = '<ul>';



    



      while ($slide_values = tep_db_fetch_array($slides_query)) {



		//  print_r($slides_values);



        $slides_array[] = array('slide_name' => $slide_values['slideshow_name'],



                                   'slide_url' => $slide_values['slideshow_url'],



								   'slide_description' => $slide_values['slideshow_description'],



								   'slide_image' => $slide_values['slideshow_image']);



     



	if($slide_values['slideshow_url']) $slides_ul .=  '<li><a href="' . $slide_values['slideshow_url']. '">' . tep_image(DIR_WS_IMAGES . $slide_values['slideshow_image'], $slide_values['slideshow_name'], $width, $height) . '</a></li>'."\n";  



	 else $slides_ul .=  '<li>' . tep_image(DIR_WS_IMAGES . $slide_values['slideshow_image'], $slide_values['slideshow_name'], $width, $height) . '</li>'."\n";  



	 



	



	  }



	  



   $slides_ul .= '</ul>';



   



       if($output=='array') return $slides_array;	



	else return $slides_ul;	



		    



  }







////

  if(SLIDESHOW_ON==1){

$slideshow = tep_get_slideshow('ul','0','0');



   



      



   if($slideshow!="<ul></ul>"){	 



?>



<link href="<?php echo tep_catalog_href_link('easy-slider.css');?>" rel="stylesheet" type="text/css" />



	<script type="text/javascript" src="<?php echo tep_catalog_href_link('js/easySlider1.7.js');?>"></script>



	<script type="text/javascript">



		(jQuery)(document).ready(function(){	

			var ew=jQuery('#bodyContent').width();
			
			jQuery('#slider ul li img').each(function() {
				
			    jQuery(this).css('width',ew);
				jQuery(this).css('height','100%');
			
			});
			

			jQuery("#slider").easySlider({



				auto: true, 



				continuous: true



			});



		});	



	</script>



<div id="content-slide">	



		<div id="slider" style="width:100%">



			<?php echo  $slideshow;?>



		</div>



</div>



<?php } } ?>
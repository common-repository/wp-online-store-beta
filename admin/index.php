<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  $languages = tep_get_languages();
  $languages_array = array();
  $languages_selected = DEFAULT_LANGUAGE;
  for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
    $languages_array[] = array('id' => $languages[$i]['code'],
                               'text' => $languages[$i]['name']);
    if ($languages[$i]['directory'] == $language) {
      $languages_selected = $languages[$i]['code'];
    }
  }

  require(DIR_WS_INCLUDES . 'template_top.php');
?>

    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td>
		<table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
		    <td width="40%" valign="top" align="left">
				<table width="100%" border="0">
				  <tr>
					<td class="inside"><span class="redHeading">1.3.1 IS HERE AT LAST!!</span> (i know we took our time).<br />
					Sorry for the delays. But we feel they are well worth it. With a new order editor, switchable product views (grid or list) Social media log ins, product sort order, continue shopping buttons and a ton more small bug fixes and improvements.<br />
					<b> Please read this <a href="http://www.help.wponlinestore.com/index.php?/Knowledgebase/Article/View/56/27/wp-online-store---upgrading-to-version-13-guide" target="_blank">update guide</a> </b> prior to running the update from your WP admin plug area.&nbsp; This guide is mainly for people that have customized the plugin. But heck, read it anyway.&nbsp; Cheers, Garfield</td>
					<td class=""><?php /*?><?php echo tep_image(DIR_WS_IMAGES . 'setup.png', ''); ?><?php */?></td>
				  </tr>
				</table>
            </td>
			<td align="center" width="2%" valign="top"><?php echo tep_image(DIR_WS_IMAGES . 'line.png', ''); ?></td>
		    <td width="55%" valign="top" align="left">
				<table width="100%">
				  <tr>
					<td class="inside"><span class="pageHeading">Two ways to Insert your new store into your site</span><br />
					 1. Just add the shortcode [WP_online_store] to your new page/post. <br />
					 2. Create a new page/post and in the page editor (WYSIWYG) you will see a shopping cart icon <?php echo tep_image(DIR_WS_IMAGES . 'basket.png', ''); ?> just click it to add your store.<br />
					 NOTE: If you using a third party WYSIWYG in WordPress (E.G.TinyMCE) the cart icon might not appear, so just use the shortcode.
                    </td>					
				  </tr>
				</table>
            </td>		


			<?php
			  if (sizeof($languages_array) > 1) { ?>
				<td class="pageHeading" align="right"><?php echo tep_draw_form('adminlanguage', FILENAME_DEFAULT, '', 'post') . tep_draw_pull_down_menu('language', $languages_array, $languages_selected, 'onchange="this.form.submit();"') . tep_hide_session_id() . '</form>'; ?></td>
			<?php
			  }
			?>

          </tr>
        </table></td>
      </tr>
	  <tr><td>&nbsp;</td></tr>
      <tr>
        <td width="50%">
		<table border="0" width="100%" cellspacing="0" cellpadding="2">
         <tr>
		 <td width="48%">
		 <?php
		  if ( defined('MODULE_ADMIN_DASHBOARD_INSTALLED') && tep_not_null(MODULE_ADMIN_DASHBOARD_INSTALLED) ) {
			$adm_array = array('d_total_revenue.php');
						
			for ( $i=0, $n=sizeof($adm_array); $i<$n; $i++ ) {
			  $adm = $adm_array[$i];

			  $class = substr($adm, 0, strrpos($adm, '.'));

			  if ( !class_exists($class) ) {
				include(DIR_FS_ADMIN.DIR_WS_INCLUDES.DIR_WS_LANGUAGES . $language . '/modules/dashboard/' . $adm);
				include(DIR_FS_ADMIN.DIR_WS_INCLUDES.DIR_WS_MODULES . 'dashboard/' . $class . '.php');
			  }

			  $ad = new $class();

			  if ( $ad->isEnabled() ) {

				echo $ad->getOutput();
				
			  }
			}
		  }
		?>		 
		 </td>
		 <td width="49%">
		 <?php
		  if ( defined('MODULE_ADMIN_DASHBOARD_INSTALLED') && tep_not_null(MODULE_ADMIN_DASHBOARD_INSTALLED) ) {
			$adm_array = array('d_total_customers.php');
						
			for ( $i=0, $n=sizeof($adm_array); $i<$n; $i++ ) {
			  $adm = $adm_array[$i];

			  $class = substr($adm, 0, strrpos($adm, '.'));

			  if ( !class_exists($class) ) {
				include(DIR_FS_ADMIN.DIR_WS_INCLUDES.DIR_WS_LANGUAGES . $language . '/modules/dashboard/' . $adm);
				include(DIR_FS_ADMIN.DIR_WS_INCLUDES.DIR_WS_MODULES . 'dashboard/' . $class . '.php');
			  }

			  $ad = new $class();

			  if ( $ad->isEnabled() ) {

				echo $ad->getOutput();
				
			  }
			}
		  }
		?>		 
		 </td>
		 <td width="3%"></td>
		 </tr>
        </table></td>
      </tr>
	  	<tr><td>&nbsp;</td></tr>  
<tr><td>	

<table width="97%" border="0" >
<tr>
  <td width="50%" valign="top">
   <table>
    <tr><td colspan="3" valign="top"><div class="round_box2">
	  		 <?php
		  if ( defined('MODULE_ADMIN_DASHBOARD_INSTALLED') && tep_not_null(MODULE_ADMIN_DASHBOARD_INSTALLED) ) {
			$adm_array = array('d_orders.php');
						
			for ( $i=0, $n=sizeof($adm_array); $i<$n; $i++ ) {
			  $adm = $adm_array[$i];

			  $class = substr($adm, 0, strrpos($adm, '.'));

			  if ( !class_exists($class) ) {
				include(DIR_FS_ADMIN.DIR_WS_INCLUDES.DIR_WS_LANGUAGES . $language . '/modules/dashboard/' . $adm);
				include(DIR_FS_ADMIN.DIR_WS_INCLUDES.DIR_WS_MODULES . 'dashboard/' . $class . '.php');
			  }

			  $ad = new $class();

			  if ( $ad->isEnabled() ) {

				echo $ad->getOutput();
				
			  }
			}
		  }
		?>	</div>	
	</td></tr>
	
<tr><td>&nbsp;</td></tr>  
    <tr>
	 <td valign="top">
	 <div class="round_box">
	   	<?php
		  if ( defined('MODULE_ADMIN_DASHBOARD_INSTALLED') && tep_not_null(MODULE_ADMIN_DASHBOARD_INSTALLED) ) {
			$adm_array = array('d_latest_news.php');
						
			for ( $i=0, $n=sizeof($adm_array); $i<$n; $i++ ) {
			  $adm = $adm_array[$i];

			  $class = substr($adm, 0, strrpos($adm, '.'));

			  if ( !class_exists($class) ) {
				include(DIR_FS_ADMIN.DIR_WS_INCLUDES.DIR_WS_LANGUAGES . $language . '/modules/dashboard/' . $adm);
				include(DIR_FS_ADMIN.DIR_WS_INCLUDES.DIR_WS_MODULES . 'dashboard/' . $class . '.php');
			  }

			  $ad = new $class();

			  if ( $ad->isEnabled() ) {

				echo $ad->getOutput();
				
			  }
			}
		  }
		?>	
	 </div>
	 </td>
	 <td></td> 
	 <td valign="top">
	    <div class="round_box">
	   	<?php
		  if ( defined('MODULE_ADMIN_DASHBOARD_INSTALLED') && tep_not_null(MODULE_ADMIN_DASHBOARD_INSTALLED) ) {
			$adm_array = array('d_newsletter.php');
						
			for ( $i=0, $n=sizeof($adm_array); $i<$n; $i++ ) {
			  $adm = $adm_array[$i];

			  $class = substr($adm, 0, strrpos($adm, '.'));

			  if ( !class_exists($class) ) {
				include(DIR_FS_ADMIN.DIR_WS_INCLUDES.DIR_WS_LANGUAGES . $language . '/modules/dashboard/' . $adm);
				include(DIR_FS_ADMIN.DIR_WS_INCLUDES.DIR_WS_MODULES . 'dashboard/' . $class . '.php');
			  }

			  $ad = new $class();

			  if ( $ad->isEnabled() ) {

				echo $ad->getOutput();
				
			  }
			}
		  }
		?>	
	  </div>
	 </td>
	</tr>
		<tr><td>&nbsp;</td></tr>  
	<tr>
	  <td colspan="3">
	   <div class="round_box_head2">A few ads to help with our beer money, click away. ;)</div>
	 <script type="text/javascript"><!--
google_ad_client = "ca-pub-2766843747439485";
/* WPOLS dash */
google_ad_slot = "0463129460";
google_ad_width = 500;
google_ad_height = 60;
//-->
</script>
<script type="text/javascript"
src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script> </td>
	</tr>
   </table>
  </td>
  <td width="5%">&nbsp;</td>
  <td width="30%" valign="top">
   <div class="round_box">
  		 <?php
		  if ( defined('MODULE_ADMIN_DASHBOARD_INSTALLED') && tep_not_null(MODULE_ADMIN_DASHBOARD_INSTALLED) ) {
			$adm_array = array('d_at_a_glance.php');
						
			for ( $i=0, $n=sizeof($adm_array); $i<$n; $i++ ) {
			  $adm = $adm_array[$i];

			  $class = substr($adm, 0, strrpos($adm, '.'));

			  if ( !class_exists($class) ) {
				include(DIR_FS_ADMIN.DIR_WS_INCLUDES.DIR_WS_LANGUAGES . $language . '/modules/dashboard/' . $adm);
				include(DIR_FS_ADMIN.DIR_WS_INCLUDES.DIR_WS_MODULES . 'dashboard/' . $class . '.php');
			  }

			  $ad = new $class();

			  if ( $ad->isEnabled() ) {

				echo $ad->getOutput();
				
			  }
			}
		  }
		?>  
   </div>
  </td>
  
  <td width="30%" valign="top">
    <div class="round_box">
  		 <?php
		  if ( defined('MODULE_ADMIN_DASHBOARD_INSTALLED') && tep_not_null(MODULE_ADMIN_DASHBOARD_INSTALLED) ) {
			$adm_array = array('d_user_stuff.php');
						
			for ( $i=0, $n=sizeof($adm_array); $i<$n; $i++ ) {
			  $adm = $adm_array[$i];

			  $class = substr($adm, 0, strrpos($adm, '.'));

			  if ( !class_exists($class) ) {
				include(DIR_FS_ADMIN.DIR_WS_INCLUDES.DIR_WS_LANGUAGES . $language . '/modules/dashboard/' . $adm);
				include(DIR_FS_ADMIN.DIR_WS_INCLUDES.DIR_WS_MODULES . 'dashboard/' . $class . '.php');
			  }

			  $ad = new $class();

			  if ( $ad->isEnabled() ) {

				echo $ad->getOutput();
				
			  }
			}
		  }
		?>
     </div>		
  </td>
</tr>
</table>
</td></tr> </table>

<?php
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>

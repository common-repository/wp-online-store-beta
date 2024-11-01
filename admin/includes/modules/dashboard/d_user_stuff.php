<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  class d_user_stuff {
    var $code = 'd_user_stuff';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function d_user_stuff() {
      $this->title = MODULE_ADMIN_DASHBOARD_USER_STUFF_TITLE;
      $this->description = MODULE_ADMIN_DASHBOARD_USER_STUFF_DESCRIPTION;

      if ( defined('MODULE_ADMIN_DASHBOARD_USER_STUFF_STATUS') ) {
        $this->sort_order = MODULE_ADMIN_DASHBOARD_USER_STUFF_SORT_ORDER;
        $this->enabled = (MODULE_ADMIN_DASHBOARD_USER_STUFF_STATUS == 'True');
      }
    }

    function getOutput() {
      if (!class_exists('lastRSS')) {
        include(DIR_WS_CLASSES . 'rss.php');
      }

      $rss = new lastRSS;
      $rss->items_limit = 5;
      $rss->cache_dir = DIR_FS_CACHE;
      $rss->cache_time = 86400;
      $feed = $rss->get('http://www.wponlinestore.com/feed/rss/');

      $output = '<div class="round_box_head">'.MODULE_ADMIN_DASHBOARD_USER_STUFF_TITLE.':</div>
	              <div class="round_box_mid"><table border="0" width="95%" cellspacing="1" cellpadding="1">';

          $output .= '<tr>	
					   <td class="inside" target="_blank"><a href="http://www.wponlinestore.com/" target="_blank">WP Online Store </a> - Visit our website </td>
					  </tr> 
					  <tr>	
					   <td class="inside" target="_blank"><a href="http://www.help.wponlinestore.com/" target="_blank">Help & Support </a> - how to guides, live chat, ticket system</td>
					  </tr>   
					  <tr>	
					   <td class="inside" target="_blank"><a href="http://www.wponlinestore.com/mingle/" target="_blank">Forums </a> - Get help from the community or our dedicated support team.</td>
					  </tr> 				  
					  <tr>	
					   <td class="inside" target="_blank"><a href="http://www.wponlinestore.com/store/order/index.php?task=cart&category_id=3.3" target="_blank">More Bells and Whistles </a> - Extend WP Online Store with additional plugins</td>
					  </tr> 
					  <tr>	
					   <td class="inside" target="_blank"><a href="http://www.wponlinestore.com/store/customers/index.php" target="_blank">Feedback </a> - Love it? hate it? tell us your thoughts (be nice, were fragile)</td>
					  </tr>';

      $output .= '</table></div>
	              <div class="round_box_head">'.MODULE_ADMIN_DASHBOARD_BE_SOCIAL_TITLE.'</div>
                  <div class="round_box_mid"><table border="0" width="140px" style="padding:3px" cellspacing="1" cellpadding="1">
				  <tr>	
				   <td colspan="2"><div id="fb-root"></div>
							<script>(function(d, s, id) {
							  var js, fjs = d.getElementsByTagName(s)[0];
							  if (d.getElementById(id)) {return;}
							  js = d.createElement(s); js.id = id;
							  js.src = "//connect.facebook.net/en_US/all.js#xfbml=1";
							  fjs.parentNode.insertBefore(js, fjs);
							}(document, \'script\', \'facebook-jssdk\'));</script>
				   <div class="fb-like" data-href="http://www.facebook.com/WPonlinestore" data-send="false" data-width="140px" data-show-faces="true"></div>
                  </td>
				  </tr> 
                  <tr>
                   <td colspan="2">	<table><tr><td>	
				   
		   
        <a href="https://twitter.com/share" class="twitter-share-button" data-count="horizontal" data-via="wponlinestore">Tweet</a>
		<script type="text/javascript" src="//platform.twitter.com/widgets.js"></script>	   

		</td><td>
		<div class="phsmc" id="phsmc_bottom_plusone">

<!-- Place this tag where you want the +1 button to render -->
<g:plusone></g:plusone>

<!-- Place this render call where appropriate -->
<script type="text/javascript">
  (function() {
    var po = document.createElement(\'script\'); po.type = \'text/javascript\'; po.async = true;
    po.src = \'https://apis.google.com/js/plusone.js\';
    var s = document.getElementsByTagName(\'script\')[0]; s.parentNode.insertBefore(po, s);
  })();
</script>
		
		</td></tr></table>
		
		</td>
                  </tr> 				  
				  <tr>	
				   <td colspan="2" align="left" width="140px">'.tep_image(DIR_WS_IMAGES . 'h-line.png', '').'</td>
			      </tr>
		 	      <tr>	
				   <td class="inside"><form action="https://www.paypal.com/cgi-bin/webscr" method="post">
Like the store? Amazed at the free support? Dazzled by the, erm, dazzly things? Then how about buying a beer for our thirsty programmers?<br>
<input type="hidden" name="cmd" value="_s-xclick">
				    <input type="hidden" name="hosted_button_id" value="WBRHN7DCZN9H4">
					<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
					<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1"></form> 
				   </td>
				  </tr> 
			      </table></div>
	              </div>';

      return $output;
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_ADMIN_DASHBOARD_USER_STUFF_STATUS');
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable User Stuff Module', 'MODULE_ADMIN_DASHBOARD_USER_STUFF_STATUS', 'True', 'Do you want to show the user stuff on the dashboard?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_ADMIN_DASHBOARD_USER_STUFF_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_ADMIN_DASHBOARD_USER_STUFF_STATUS', 'MODULE_ADMIN_DASHBOARD_USER_STUFF_SORT_ORDER');
    }
  }
?>

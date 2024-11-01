<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  class d_newsletter {
    var $code = 'd_newsletter';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function d_newsletter() {
      $this->title = MODULE_ADMIN_DASHBOARD_NEWSLETTER_TITLE;
      $this->description = MODULE_ADMIN_DASHBOARD_NEWSLETTER_DESCRIPTION;

      if ( defined('MODULE_ADMIN_DASHBOARD_NEWSLETTER_STATUS') ) {
        $this->sort_order = MODULE_ADMIN_DASHBOARD_NEWSLETTER_SORT_ORDER;
        $this->enabled = (MODULE_ADMIN_DASHBOARD_NEWSLETTER_STATUS == 'True');
      }
    }

    function getOutput() {
     
    $output .= '<div class="round_box_head">'.MODULE_ADMIN_DASHBOARD_NEWSLETTER_TITLE.'</div><!-- Begin MailChimp Signup Form -->
	            <div class="round_box_mid">
			    <link href="http://cdn-images.mailchimp.com/embedcode/slim-081711.css" rel="stylesheet" type="text/css">
                <style type="text/css">
                #mc_embed_signup{background:#fff; clear:left; font:14px Helvetica,Arial,sans-serif; }
                /* Add your own MailChimp form style overrides in your site stylesheet or in this style block.
                We recommend moving this block and the preceding CSS link to the HEAD of your HTML file. */
                </style>
                <div id="mc_embed_signup">
                <form action="http://wponlinestore.us2.list-manage.com/subscribe/post?u=d4b06cca3ddb1ecb539d5092f&amp;id=39a4707359" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank">
                 <label for="mce-EMAIL">Subscribe to our mailing list</label>
                 <input type="email" value="" name="EMAIL" class="email" id="mce-EMAIL" placeholder="email address" required>
                 <div class="clear"><input type="submit" value="Subscribe" name="subscribe" id="mc-embedded-subscribe" class="add-new-h2"></div>
                </form></div>
				<br /><br /><br/>
               </div></div><!--End mc_embed_signup-->';                

      return $output;
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_ADMIN_DASHBOARD_NEWSLETTER_STATUS');
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Newsletter Module', 'MODULE_ADMIN_DASHBOARD_NEWSLETTER_STATUS', 'True', 'Do you want to show the Newsletter on the dashboard?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_ADMIN_DASHBOARD_NEWSLETTER_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_ADMIN_DASHBOARD_NEWSLETTER_STATUS', 'MODULE_ADMIN_DASHBOARD_NEWSLETTER_SORT_ORDER');
    }
  }
?>

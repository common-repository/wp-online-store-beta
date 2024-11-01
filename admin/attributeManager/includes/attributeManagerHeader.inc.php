<?php
/*
  $Id: attributeManagerHeader.inc.php,v 1.0 21/02/06 Sam West$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Released under the GNU General Public License
  
  Copyright © 2006 Kangaroo Partners
  http://kangaroopartners.com
  osc@kangaroopartners.com
*/

if('new_product' == $action || 'update_product' == $action) {
$amSessionVar = tep_session_name().'='.tep_session_id();
if($_GET['pID']=="") $pid=0;
else $pid=$_GET['pID'];
echo '
<script language="JavaScript" type="text/JavaScript">
	var productsId='.$pid.';
	var pageAction="'.$_GET['action'].'";
	var sessionId="'.$amSessionVar.'";
	var url_to_admin="'. HTTP_SERVER.DIR_WS_CATALOG.'admin/";

</script>
<script language="JavaScript" type="text/JavaScript" src="'. tep_catalog_href_link('admin/attributeManager/javascript/requester.js').'"></script>
<script language="JavaScript" type="text/JavaScript" src="'.tep_catalog_href_link('admin/attributeManager/javascript/alertBoxes.js').'"></script>
<script language="JavaScript" type="text/JavaScript" src="'.tep_catalog_href_link('admin/attributeManager/javascript/attributeManager.js').'"></script>

<link rel="stylesheet" type="text/css" href="'.tep_catalog_href_link('admin/attributeManager/css/attributeManager.css').'" /> ';

}
?>

<script language="JavaScript" type="text/javascript">

function goOnLoad() {
	<?php	if('new_product' == $action || 'update_product' == $action) echo 'attributeManagerInit();';	?>
	SetFocus();
}

</script>

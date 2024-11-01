<?php
/*
  $Id: CSS-Editor.php,v 1.00 03/05/2006 
// Phil Pearce aka Pilly
*/

  require('includes/application_top.php');
  require(DIR_WS_INCLUDES . 'template_top.php');

?>

<script type="text/javascript" src="<?php echo site_url();?>/wp-content/plugins/<?php echo WPOLS_PLUGINS_DIR;?>/admin/MiniColorPicker.js"></script>
<script>
    /**
    * SET SOME DEFAULTS...
    */
    bit = 16;	//set default color depth. (values: 8, 16, 24 or 32)
    pixel = 10; //set picker pixel size.


	/**
	* Do something extra??
	*/
	function doSomethingExtra(some_color){
    	alert("I'll have to do something with " + some_color);
	}

	function doSomethingExtra_2(some_var,some_color){
    	alert("Var contents is " + some_var);
    	alert("I'll have to do something with " + some_color);
	}

	function doSomethingExtra_3(a,b,c,some_color){
    	alert("Var contents is " + a + b + c);
    	alert("I'll have to do something with " + some_color);
	}
</script>
<table border="0" summary="" width="100%" cellspacing="2" cellpadding="2">
  <tr>
   
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="0">
      <tr>
        <td><table border="0" summary="" width="100%" cellspacing="0" cellpadding="0">
		<tr>
			<td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
		</tr>
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE.TEXT_HELP_INSTURCTIONS.TEXT_HELP_VIDEO; ?></td>
            <td class="pageHeading">
				<table cellpadding="0" cellspacing="0" border="0">
					<tr>
					<td class="main"><?php echo TABLE_HEADING_HELP; ?></td>
					<td><?php echo tep_draw_separator('pixel_trans.gif', '10', '10'); ?></td>
					<td><script>initPicker('32bitcolor','',32);</script></td>
					</tr>
				</table></td>
			</tr>
        </table></td>
	</tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
<table width="100%"  border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td>
	<tr>
	<td>
<?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?>	
	</td>
	</tr>
<?php
				
	if($_POST['Submit']){

		$filename = DIR_FS_DOCUMENT_ROOT.'/stylesheet.css';
		$filecontent = $_POST['filecontent'];

	// Let's make sure the file exists and is writable first.
	if (is_writable($filename)) {

   		// In our example we're opening $filename in write mode,
   		// with the pointer at the top, and the file truncated.
   		// The file is also opened in Binary mode.
   		// that's where $somecontent will go when we fwrite() it.
   		if (!$handle = fopen($filename, 'w+b')) {
			echo '<td class="messageStackError">', TEXT_CANNOT_OPEN . '</td>';
         	exit;
   		}

   		// Write $filecontent to our opened file.
   		if (fwrite($handle, $filecontent) === FALSE) {
			echo '<td class="messageStackError">', TEXT_CANNOT_WRITE . '</td>';
       		exit;
   		}
			echo '<td class="messageStackSuccess">', TEXT_SUCCESSFULLY_WRITTEN . '</td>';
				fclose($handle);
		} else {
			echo '<td class="messageStackError">', TEXT_CANNOT_WRITE . '</td>';
		} 
	}

		$filename = DIR_FS_DOCUMENT_ROOT.'/stylesheet.css';
		$fp3 = fopen ($filename, 'rw');

		$filecontent = fread ($fp3, filesize ($filename));
		fclose ($fp3); 
?>	
	</td>
  </tr>
</table>
		<form id="form1" name="form1" method="post" action="">
  		<p>
    	<textarea name="filecontent" cols="110" rows="25"><?php echo $filecontent; ?></textarea>
		 <p>
		 
    	<input type="submit" name="Submit" value="<?=TEXT_SUBMIT?>">
		</p>
		</form>
		
<!--CHANGE LINKS BELOW TO YOUR OWN-->
<!-- 		<a href="javascript:jumpto('<?php echo HTTP_CATALOG_SERVER.DIR_WS_CATALOG?>/account.php')"><?php echo TEXT_ACCOUNT; ?></a><?php echo TEXT_SPACE; ?>
		<a href="javascript:jumpto('<?php echo HTTP_CATALOG_SERVER.DIR_WS_CATALOG?>/conditions.php')"><?php echo TEXT_CONDITIONS; ?></a><?php echo TEXT_SPACE; ?>
		<a href="javascript:jumpto('<?php echo HTTP_CATALOG_SERVER.DIR_WS_CATALOG?>/index.php')"><?php echo TEXT_INDEX; ?></a><?php echo TEXT_SPACE; ?>
		<a href="javascript:jumpto('<?php echo HTTP_CATALOG_SERVER.DIR_WS_CATALOG?>/product_info.php')"><?php echo TEXT_PRODUCT_INFO; ?></a><?php echo TEXT_SPACE; ?>
 -->
              </tr>
            </table></td>
          </tr>
        </table></td>
      </tr>
    </table></td>
<!-- body_text_eof //-->
  </tr>
</table>



<script language="javascript">
<!--
//Drop-down Document Viewer II- © Dynamic Drive (www.dynamicdrive.com)
//For full source code, 100's more DHTML scripts, and TOS,
//visit http://www.dynamicdrive.com

//Specify display mode (0 or 1)
//0 causes document to be displayed in an inline frame, while 1 in a new browser window
	var displaymode=0
//if displaymode=0, configure inline frame attributes (ie: dimensions, intial document shown
	var iframecode='<iframe id="external" style="width:95%;height:400px" src="<?php echo HTTP_CATALOG_SERVER;?>"></iframe>'

/////NO NEED TO EDIT BELOW HERE////////////

	if (displaymode==0)
		document.write(iframecode)

	function jumpto(inputurl){
	if (document.getElementById&&displaymode==0)
		document.getElementById("external").src=inputurl
	else if (document.all&&displaymode==0)
		document.all.external.src=inputurl
	else{
	if (!window.win2||win2.closed)
		win2=window.open(inputurl)
//else if win2 already exists
	else{
		win2.location=inputurl
		win2.focus()
		}
	}
}
//-->
</script>
<?php
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  if (tep_session_is_registered('admin')) {
    $cl_box_groups = array();

    include(DIR_WS_BOXES . 'configuration.php');
    include(DIR_WS_BOXES . 'catalog.php');
    include(DIR_WS_BOXES . 'modules.php');
    include(DIR_WS_BOXES . 'customers.php');
    include(DIR_WS_BOXES . 'taxes.php');
    include(DIR_WS_BOXES . 'localization.php');
    include(DIR_WS_BOXES . 'reports.php');
    include(DIR_WS_BOXES . 'tools.php');
?>

<style>

/*
#menu{
padding:0;
margin:0;
width:100%;
}
#menu ul{
padding:0;
margin:0;
}
#menu li{
position: relative;
float: left;
list-style: none;
margin: 0;
padding:0;
}
#menu li a{
	width:130px;
	display: block;
	text-decoration:none;
	text-align:left;
	color:#21759B;
	padding: 5px;
	font-family: Arial, Helvetica, sans-serif;
	font-size: 14px;
	margin-top: 5px;
	background-image: url(../../images/admin_images/gray-grad.png);
	background-repeat: repeat-x;
}#menu li a:hover{
background-color:#999999;
}
#menu ul ul{
	position: absolute;
	visibility: hidden;
	-moz-box-shadow: 3px 3px 4px #666;
		-webkit-box-shadow: 3px 3px 4px #666;
		box-shadow: 3px 3px 4px #666;
	font-size:11px;
	background-color: #fff;
	z-index:10000;
	
}#menu ul li:hover ul{
visibility:visible;
}
*/
</style>
<table border="0" width="100%" cellspacing="0" cellpadding="0">
  <tr>
    <td >
<div id="menu">
<ul class="menu">
<?php
    foreach ($cl_box_groups as $groups) {
      echo '<li><a href="#" class="parent"><span>' . $groups['heading'] . '</span></a><div><ul>' ;
	
	foreach ($groups['apps'] as $app) {
        echo '<li><a href="' . $app['link'] . '"><span>' . $app['title'] . '</span></a></li>';
      }

      echo '</ul></div></li>';
    }
?>
</ul>
</div>
</td>
</tr>
</table>
<script type="text/javascript">
$('#adminAppMenu').accordion({
  autoHeight: false,
  icons: {
    'header': 'ui-icon-plus',
    'headerSelected': 'ui-icon-minus'
  }

<?php
    $counter = 0;
    foreach ($cl_box_groups as $groups) {
      foreach ($groups['apps'] as $app) {
        if ($app['code'] == $PHP_SELF) {
          echo ',active: ' . $counter;
          break;
        }
      }

      $counter++;
    }
?>

});
</script>

<?php
  }
?>

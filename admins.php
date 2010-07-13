<?php
/*
 * MobileSuv - Mobile Surveys Platform
 *
 * Copyright (C) 2006-2010
 * Yo! Uganda Limited and The Grameen Foundation
 * 	
 * All Rights Reserved
 *
 * Unauthorized redistribution of this software in any form or on any
 * medium is strictly prohibited. This software is released under a
 * license agreement and may be used or copied only in accordance with
 * the terms thereof. It is against the law to copy the software on
 * any other medium, except as specifically provided in the license
 * agreement.  No part of this software may be reproduced, stored
 * in a retrieval system, or transmitted in any form or by any means,
 * electronic, mechanical, photocopied, recorded or otherwise,
 * outside the terms of the said license agreement without the prior
 * written permission of Yo! Uganda Limited.
 *
 * YOGBLICCOD331920192_20090909
 */
?>
<?
header("Expires: Tue, 12 Mar 1910 10:45:00 GMT");
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");

include("constants.php");
include("functions.php");
include("sessions.php");

dbconnect();
validate_session(); 
check_admin_user();

if(isset($_POST["add_admin"])) {
  goto("addadmin.php");
}

/* Switch user to $user -*/
if(isset($_GET['su'])) {  
  su($_GET['admin_id']);
}

if(isset($_GET["enable"]) || isset($_GET["disable"]) || isset($_GET["delete"])) {
  check_super_privilege();
}

if(isset($_GET["disable"])) {
 $sql = "UPDATE admin SET active=0 WHERE id=$_GET[admin_id]";
 execute_nonquery($sql);
 goto("admins.php");
}

if(isset($_GET["enable"])) {
 $sql = "UPDATE admin SET active=1 WHERE id=$_GET[admin_id]";
 execute_nonquery($sql);
 goto("admins.php");
}

if(isset($_GET["delete"])) {
 $sql = "DELETE FROM admin WHERE id=$_GET[admin_id]";
 execute_nonquery($sql);
 goto("admins.php");
}

/* menu highlight */
$page = 'users';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title><?= TITLE ?></title>
<link rel="stylesheet" type="text/css" href="styles/style.css" />
</head>

<body>
<table width="790" border="0" align="center" cellpadding="0" cellspacing="0" class="main">
     <!--DWLayoutTable-->
     <tr>
          <td width="790" height="124" valign="top"><? include('top.php') ?></td>
     </tr>
     <tr>
          <td height="404" valign="top"><table width="100%" border="0" cellpadding="0" cellspacing="0" class="border">
               <!--DWLayoutTable-->
               <tr>
                    <td height="22" colspan="3" align="center" valign="middle" class="caption">Administrators</td>
                    </tr>
               <tr>
                    <td width="9" height="17">&nbsp;</td>
                         <td width="770"><? require 'admins.menu.php' ?></td>
                         <td width="9">&nbsp;</td>
                    </tr>
										
               <tr>
                    <td height="351">&nbsp;</td>
                         <td valign="top">
                              <?
	 $html = '<table width="100%" border="0" cellpadding="0" cellspacing="0">
	 <tr class="title1">
	  <td height="25" valign="top" colspan="2"><u>Names</u></td>
	  <td valign="top"><u>Username</u></td>
	  <td valign="top"><u>Type</u></td>
	  <td valign="top"><u>Status</u></td>
	  <td valign="top"><u>Options</u></td>
	 </tr>'; 
	 $query = mysql_query("SELECT * FROM admin");
	 $color = '#EEEEEE'; $i=0;
     while($row = mysql_fetch_assoc($query)) {
	  $color = $i++%2 ? '#FFFFFF' : '#EEEEEE';
	  $html .='
	  <tr bgcolor="'.$color.'" onmouseover="this.style.backgroundColor=\''.HOVERCOLOR.'\'"
	  onmouseout="this.style.backgroundColor=\''.$color.'\'">
	   <td height="25" width="20">
	      <img src="images/admin.gif" style="cursor: pointer" title="'.($row['firstName'].' '.$row['lastName']).'"/>
	   </td>
	   <td valign="middle">'.$row['firstName'].' '.$row['lastName'].'</td>
	   <td valign="middle">'.(!strcmp($row["username"], 'administrator') ? 
	   '<span style="color: #FF3300">'.$row["username"].'</span>' : $row["username"]).'</td>
	   <td style="color: '.(preg_match("/ADMIN/", $row['type']) ? '#008800' : '#336699').'">'.$row['type'].'</td>
	   <td>'.($row["active"] ? 'ACTIVE' : '<span style="color: #666666">DISABLED</span>').'</td>
	   <td>
	   <a href="?admin_id='.$row["id"].'&su=TRUE" 
	   onclick="return confirm(\'You will be logged out and logged in as: '.$row['username'].' ('.$row['firstName'].' '.$row['lastName'].'). Your current privileges will be dropped. Proceed?\')">Login</a> | 
		<a href="admins.php?admin_id='.$row["id"].'&'.($row["active"] ? 'disable=TRUE' : 'enable=TRUE').'">'.( $row["active"] ? 'Disable' : 'Enable&nbsp;').'</a> |
		<a href="editadmin.php?admin_id='.$row["id"].'">Edit</a> |
	    <a href="admins.php?admin_id='.$row["id"].'&delete=TRUE" style="color: #FF0000"
		onclick="return confirm(\'Are you sure you want to delete: '.$row["name"].'?\')">Delete</a>
	   </td>
	  </tr>';
	 }
	 $html .='
	 <tr>
	  <td height="35" valign="bottom" colspan="3">
	  <form method="post" style="margin: 0px">
	  <input type="submit" name="add_admin" value="Add Administrator" class="button"/>
	  </form></td>
	 </tr>';
	 echo $html.'</table>';
	?>	
	</td>
         <td>&nbsp;</td>
                    </tr>
               <tr>
                    <td height="12"></td>
                         <td></td>
                         <td></td>
                    </tr>
          </table></td>
     </tr>
         <tr>
          <td height="30" valign="top"><? include('bottom.php') ?></td>
     </tr>
</table>
</body>
</html>

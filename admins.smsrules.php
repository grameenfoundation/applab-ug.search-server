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

if(isset($_GET["delete"])) {
  check_super_privilege();
}


if(isset($_GET["delete"])) {
	$sql = "DELETE FROM smsForward where id = '$_GET[id]'";
	execute_update($sql);
	goto("admins.smsrules.php");
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
	  <td height="25" valign="top" width="35%"><u>Create Date</u></td>
	  <td valign="top" ><u>Description</u></td>
	  <td valign="top" width="25%"><u>Options</u></td>
	 </tr>';
		$sql = "SELECT smsForward.*, DATE_FORMAT(createDate, '%d/%m/%Y %r') AS newDate FROM smsForward";
		$result = execute_query($sql);
		if(mysql_num_rows($result)){
			$color = '#E4E4E4'; $i=0;
			while($row = mysql_fetch_assoc($result)){
				$color = $i++%2 ? '#FFFFFF' : '#EEEEEE';
				$hovercolor = HOVERCOLOR;
				$html .="<tr bgcolor=\"$color\" onmouseover=\"this.style.backgroundColor='$hovercolor'\" onmouseout=\"this.style.backgroundColor='$color'\">
						<td height=\"25\">$row[newDate]</td>
						<td><a href=\"admins.viewsmsrule.php?id=$row[id]\"> $row[description] </td>
						<td><a href=\"admins.editsmsrules.php?id=$row[id]\">Edit</a> | 
						<a href=\"admins.smsrules.php?id=$row[id]&delete=TRUE\" style=\"color: #FF0000\" onclick=\"return confirm('Are you sure you want to delete ?')\">Delete</a>
						</td>
					</tr>";
			}
		}
		$survey = mysql_fetch_assoc($result);
	 $html.='<tr>
	  <td height="35" valign="bottom" colspan="3">
	  <form method="post" action="admins.addsmsrules.php" style="margin: 0px">
	  <input type="submit" name="add_sms_rule" value="Add" class="button" />
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

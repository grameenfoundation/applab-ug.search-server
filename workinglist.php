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

extract($_GET);

$userId = get_user_id();

if($clear) {
     $sql = "DELETE FROM workinglist WHERE owner='$userId'";
	 execute_update($sql);
	 redirect('users.php');
}

if(isset($delete)) {
   if(!isset($phone) || !preg_match("/^[0-9]+$/", $phone)) {
       redirect('users.php');
   }
   $sql = "DELETE FROM workinglist WHERE phone='$phone' AND owner='$userId'";
   execute_update($sql);
   redirect('workinglist.php');
}

if(isset($_POST['addtogroup']))
{
   $groups = array();
   foreach($_POST as $key=>$val) {
       if(!preg_match('/^group_[0-9]+$/', $key)) {
	       continue;
	   }
	   $groups[] = preg_replace('/^group_/', '', $val);
   } 
   if(!count($groups)) {
       redirect('workinglist.php');
   }
   $groups = implode(',', $groups); 
   $sql = "SELECT * FROM workinglist WHERE owner='$userId'";
   $result=execute_query($sql);
   $total = mysql_num_rows($result);
   while($row=mysql_fetch_assoc($result)) {
        $sql = "UPDATE user SET groups='$groups' WHERE misdn='$row[phone]'";  
		execute_update($sql);
   }
}

if(isset($_POST['sendquiz'])) {
    redirect('quiz.php?add_from_wl');
}
if(isset($_POST['sendsms'])) {
    redirect('sendsms.php?add_from_wl&return=workinglist.php');
}
if(isset($_POST['schedulesms'])) {
    redirect('messages.php?add_from_wl');
}

$sql="SELECT workinglist.*, DATE_FORMAT(createdate, '%d/%m/%Y %r') AS date FROM workinglist WHERE owner='$userId' ORDER BY createdate";
$result = execute_query($sql);
$total = mysql_num_rows($result);

/* menu highlight */
$page = 'userphones';

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title><?= TITLE ?></title>
<link rel="stylesheet" type="text/css" href="styles/style.css" />
<script type="text/javascript" src="basic.js"></script>
</head>

<body>
<table width="790" border="0" align="center" cellpadding="0" cellspacing="0" class="main">
     <!--DWLayoutTable-->
     <tr>
          <td width="790" height="124" valign="top"><? include('top.php') ?></td>
     </tr>
     <tr>
          		<td height="372" valign="top"><table width="100%" border="0" cellpadding="0" cellspacing="0" class="border">
          					<!--DWLayoutTable-->
          					<tr>
          								<td height="22" colspan="3" align="center" valign="middle" class="caption">Working List - Total: <?= $total ?></td>
                    			</tr>
          					<tr>
          								<td width="28" height="30">&nbsp;</td>
                         			<td width="740" valign="top"><? require 'users.menu.php' ?></td>
                         			<td width="20">&nbsp;</td>
               			</tr>
          					<tr>
          								<td height="295">&nbsp;</td>
               						<td valign="top">
               									<?
	 $html = '<table width="100%" border="0" cellpadding="0" cellspacing="0">
	 <tr class="title1">
	  <td height="25" valign="top"><u>Phone Number</u></td>
	  <td valign="top"><u>Names</u></td>
	  <td valign="top"><u>Add Date</u></td>
	  <td valign="top"><u>Options</u></td>
	 </tr>';
	  
	 $color = '#EEEEEE'; $i=0;
     
	 while($row = mysql_fetch_assoc($result)) 
	 {
	     $color = $i++%2 ? '#FFFFFF' : '#EEEEEE';
	     $names = get_phone_display_label($row['phone']);
		 
	     $html .='
	        <tr bgcolor="'.$color.'" onmouseover="this.style.backgroundColor=\''.HOVERCOLOR.'\'"
	            onmouseout="this.style.backgroundColor=\''.$color.'\'">
	            <td height="25">&nbsp;'.$row['phone'].'</td>
	            <td>'.($names!=$row['phone'] ? $names : '-').'</td>
				<td>'.$row['date'].'</td>
	            <td>
	               <a href="?phone='.$row['phone'].'&delete=TRUE" style="color: #FF0000"
		           onclick="return confirm(\'Are you sure you want to delete: '.$row['phone'].'?\')">Delete</a>
	            </td>
	        </tr>';
	 }
	 
	 $html .='
	 <tr>
	     <td height="35" valign="bottom" colspan="4">
	        <form method=post>
	             <input type="submit" name="clear" value="Clear List" class="button" '.(!$total ? 'disabled="disabled"' : '').' onclick="return confirm(\'Are you sure you want to clear the Working List?\')"/>
				 <input type=button name=addtogroup value="Add to Group" class="button" onclick="addwltgrp()" '.(!$total ? 'disabled="disabled"' : '').'/>
				 <input type=submit name=sendquiz value="Send Quiz" class="button" '.(!$total ? 'disabled="disabled"' : '').'/>
				 <input type=submit name=sendsms value="Send Instant SMS" class="button" '.(!$total ? 'disabled="disabled"' : '').'/>
				 <input type=submit name=schedulesms value="Schedule SMS" class="button" '.(!$total ? 'disabled="disabled"' : '').'/>
	        </form>
	     </td>
	 </tr>';
	 echo $html.'</table>';
	
	?>	</td>
         			<td>&nbsp;</td>
               						</tr>
          					<tr>
          								<td height="23">&nbsp;</td>
               						<td>&nbsp;</td>
               						<td>&nbsp;</td>
               						</tr>
          					
               
               
               
               
          				</table></td>
     </tr>
         <tr>
          <td height="30" valign="top"><? include('bottom.php') ?></td>
     </tr>
</table>
<?
if(isset($_POST['addtogroup']) && $total) {
   print '<script>alert("'.$total.' User(s) successfuly added to selected group(s)");</script>';
}
?>
</body>
</html>

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

$user_details = get_user_details();
$su_users = explode(",", $user_details["su"]); 

if(isset($_POST["submit"])) {
 $old_password = mysql_real_escape_string($_POST["old_password"]);
 $sql = "SELECT * FROM admin WHERE password=PASSWORD(\"$old_password\") AND username=\"$user_details[username]\"";
 $result = execute_query($sql);
 if(!mysql_num_rows($result)) {
   $errors = 'Permission denied. Old password incorrect';
 }  
 if(!isset($errors)) {
  if(strlen($_POST["password"]) || strlen($_POST["password2"])) {
   if(strcmp($_POST["password"], $_POST["password2"]) != 0)
    $errors = '&bull; Passwords do not match<br/>'; 
   elseif(strlen($_POST["password"]) < 4)
    $errors = '&bull; New Password too short/not valid<br/>';	
  }
 }
 if(!isset($errors)) {
  $su_admins = array();
   foreach($_POST as $key=>$val) {
    if(preg_match("/^admin-/", $key) && is_numeric($val))
	 $su_admins [] = $val;
  }
  $su = implode(",", $su_admins);
  $password = mysql_real_escape_string($_POST["password"]);
  $sql = "UPDATE admin SET su=IF(LENGTH('$su'), '$su', NULL), password=IF(LENGTH('$password'), PASSWORD('$password'), password) 
          WHERE username='$user_details[username]' LIMIT 1";
  execute_nonquery($sql);
    show_message("Preferences updated", "Your preferences have been successfully updated");
 }
}

/* menu highlight */
$page = 'account';
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
          <td valign="top"><table width="100%" border="0" cellpadding="0" cellspacing="0" class="border">
     <form method="post">
     <tr>
       <td height="25" colspan="3" align="center" valign="middle" class="caption">Edit Your Preferences</td>
       </tr>
     <tr>
       <td width="69" height="28">&nbsp;</td>
        <td width="637">&nbsp;</td>
        <td width="82">&nbsp;</td>
     </tr>
     <tr>
       <td height="147">&nbsp;</td>
       <td valign="top">
           <fieldset>
            <legend>Your Password</legend>
	    <table width="100%" border="0" cellpadding="0" cellspacing="0">
       <!--DWLayoutTable-->
       <tr>
        <td width="24"></td>
        <td width="137" height="25"></td>
        <td colspan="2" valign="top" style="color: #FF0000"><span style="color: #FF0000">
         <? if(isset($errors)) echo $errors ?>
        </span></td>
       </tr>
        <tr>
         <td></td>
         <td height="24" align="right" valign="middle"><span style="color: #cc0000">*</span>Current Password:&nbsp;&nbsp; </td>
         <td colspan="2" valign="middle"><input name="old_password" type="password" class="input" id="old_password" size="35" /></td>
        </tr>
        <tr>
         <td></td>
         <td height="24" align="right" valign="middle">New Password:&nbsp;&nbsp; </td>
         <td width="230" valign="middle"><input name="password" type="password" class="input" id="password" size="35" /></td>
         <td width="92"></td>
        </tr>
        <tr>
         <td></td>
         <td height="24" align="right" valign="middle">Confirm Password:&nbsp;&nbsp; </td>
         <td valign="middle"><input name="password2" type="password" class="input" id="password2" size="35" /></td>
         <td></td>
        </tr>
        <tr>
         <td></td>
         <td height="34"></td>
         <td valign="middle">
          <input name="submit" type="submit" class="button" id="submit" value="Update Preferences" /></td>
         <td></td>
        </tr>
     </table>
          </fieldset></td>
        <td>&nbsp;</td>
     </tr>
     <tr>
       <td height="14"></td>
       <td></td>
       <td></td>
     </tr>
	 <? if(admin_user()) { ?>
     <tr>
       <td height="159"></td>
       <td valign="top">
	     <fieldset>
	     <legend>Users who can switch to your account</legend>
	   <table width="" border="0" cellpadding="0" cellspacing="0">
         <!--DWLayoutTable-->
         <tr>
           <td width="70" height="28">&nbsp;</td>
              <td width="425">&nbsp;</td>
              <td width="140">&nbsp;</td>
         </tr>
         <tr>
           <td height="101"></td>
           <td valign="top">
		     <? 
	          $list = '<div style="width: 435px; height: 88px; overflow: auto">';
	          $query = mysql_query('SELECT * FROM admin');
	   while($row = mysql_fetch_assoc($query)) {
	    $disabled = !strcmp($row["username"], $user_details["username"]) ? 'disabled="disabled"' : '';
		$checked = in_array($row["id"], $su_users) ? 'checked="checked"' : '';
	    $list .= "<input type=\"checkbox\" name=\"admin-$row[id]\" $checked $disabled value=\"$row[id]\"/>&nbsp;&nbsp;$row[username]<br/>";
	   }
	   print $list.'</div>';
	  ?></td>
           <td></td>
         </tr>
         <tr>
           <td height="14"></td>
           <td></td>
           <td></td>
         </tr>
       </table>
	     </fieldset></td>
       <td></td>
     </tr>
	 <? } ?>
	 </form>
     <tr>
       <td height="24"></td>
       <td>&nbsp;</td>
       <td></td>
     </tr>
     <tr>
       <td height="39"></td>
       <td align="center" valign="middle" id="note"><span style="color: #cc0000">*Please Note:</span> You must provide your currrent password to make updates to your preferences </td>
       <td></td>
     </tr>
     <tr>
       <td height="49"></td>
       <td>&nbsp;</td>
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

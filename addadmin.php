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

check_super_privilege();

if(isset($_POST["cancel"])) {
 header("Location: admins.php");
 exit();
}

if(count($_POST)) {
  $_POST = strip_form_data($_POST);
}  

if(isset($_POST["submit"])) {
   if(!preg_match("/^[a-z0-9\s.-]{3,}$/i", $_POST["firstName"])) {
     $errors = 'First name not valid<br/>';
   }
   if(!preg_match("/^[a-z0-9\s.-]{3,}$/i", $_POST["lastName"])) {
     $errors .= 'Last name not valid<br/>';	 
   }
  if(!preg_match("/^[a-z0-9-._]+@[a-z0-9-]+\.[a-z.]+$/i", $_POST[email])) {
     $errors .= 'Email address not valid<br/>';
  }   
   if(!preg_match("/^[a-z0-9\s.-_]{3,}$/i", $_POST["username"])) {
     $errors .= 'Username not valid<br/>';
   }
   elseif(unique_field_exists('username', $username, 'admin')) {
     $errors .= 'Username exists. Please choose another<br/>';
   }
   if(strcmp($_POST["password"], $_POST["password2"]) != 0) {
     $errors .= 'Passwords do not match<br/>'; 
   }
   elseif(strlen($_POST["password"]) < 4) {
     $errors .= 'Password too short/not valid<br/>';
   }
   if(!isset($errors)) {
     extract(escape_form_data($_POST));
     
	 $sql = "INSERT INTO admin(username, firstName, lastName, email, type, password, createDate) 
	         VALUES('$username', '$firstName', '$lastName', '$email', '$type', PASSWORD('$password'), NOW())";
	 
	 execute_query($sql);
	 goto('admins.php');
  }
}
if(isset($errors)) {
  $errors = "<br/>$errors<br/>";
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
          <td height="367" valign="top"><table width="100%" border="0" cellpadding="0" cellspacing="0" class="border">
               <!--DWLayoutTable-->
               <tr>
                    <td height="22" colspan="3" align="center" valign="middle" class="caption">Add System User Account </td>
                    </tr>
               <tr>
                    <td width="69" height="48">&nbsp;</td>
                    <td width="648">&nbsp;</td>
                    <td width="71">&nbsp;</td>
               </tr>
               <tr>
                    <td height="233">&nbsp;</td>
                    <td valign="top">
                              <fieldset>
                              <legend>User Details</legend>
					     <table width="100%" border="0" cellpadding="0" cellspacing="0">
					          <!--DWLayoutTable-->
					          <tr>
					               <td width="15" height="23">&nbsp;</td>
                   <td width="166">&nbsp;</td>
                   <td width="284" valign="top" class="error">
                        <? if(isset($errors)) echo $errors; ?>             </td>
                   <td width="80">&nbsp;</td>
                  </tr>
					          <form method="post">
					               <tr>
					                    <td height="24">&nbsp;</td>
                    <td align="right" valign="middle">First Name:&nbsp;&nbsp;</td>
                    <td valign="middle"><input name="firstName" type="text" class="input" id="firstName" value="<?= $firstName ?>" size="40" /></td>
                    <td>&nbsp;</td>
                   </tr>
					               <tr>
					                    <td height="24">&nbsp;</td>
                    <td align="right" valign="middle">Last Name:&nbsp;&nbsp;</td>
                    <td valign="middle"><input name="lastName" type="text" class="input" id="lastName" value="<?= $lastName ?>" size="40" /></td>
                    <td>&nbsp;</td>
                   </tr>
					               <tr>
					                    <td height="24">&nbsp;</td>
                    <td align="right" valign="middle">Email Address:&nbsp;&nbsp;</td>
                    <td valign="middle"><input name="email" type="text" class="input" id="email" value="<?= $email ?>" size="40" /></td>
                    <td>&nbsp;</td>
                   </tr>	
					               <tr>
					                    <td height="24">&nbsp;</td>
                    <td align="right" valign="middle">User Type:&nbsp;&nbsp;</td>
                    <td valign="middle"><select name="type" class="input" id="type" style="width: 274px">
                    			<option value="ADMINISTRATOR" <?= $type=='ADMINISTRATOR' ? 'selected="selected"' : ''?>>ADMINISTRATOR</option>
                    			<option value="LIMITED_USER" <?= $type=='LIMITED_USER' ? 'selected="selected"' : ''?>>LIMITED USER</option>
                    			</select>
                    </td>
                    <td>&nbsp;</td>
                   </tr>					   					
					               <tr>
					                    <td height="24">&nbsp;</td>
                    <td align="right" valign="middle">Username:&nbsp;&nbsp;</td>
                    <td valign="middle"><input name="username" type="text" class="input" id="username" value="<?= $username ?>" size="40" /></td>
                    <td>&nbsp;</td>
                   </tr>
					               <tr>
					                    <td height="24">&nbsp;</td>
                    <td align="right" valign="middle">Password:&nbsp;&nbsp;</td>
                    <td valign="middle"><input name="password" type="password" class="input" id="password" size="40" /></td>
                    <td>&nbsp;</td>
                   </tr>
					               <tr>
					                    <td height="24">&nbsp;</td>
                    <td align="right" valign="middle">Confirm Password:&nbsp;&nbsp;</td>
                    <td valign="middle"><input name="password2" type="password" class="input" id="password2" size="40" /></td>
                    <td>&nbsp;</td>
                   </tr> 	 	 
					               <tr>
					                    <td height="35">&nbsp;</td>
                    <td>&nbsp;</td>
                    <td valign="middle"><input name="submit" type="submit" class="button" id="submit" value="Add User" />
                         <input name="cancel" type="submit" class="button" id="cancel" value="Cancel" /></td>
                    <td>&nbsp;</td>
                   </tr>
					               </form>
                  <tr>
                       <td height="16"></td>
                   <td></td>
                   <td></td>
                   <td></td>
                  </tr>
					          </table>
                         </fieldset></td>
                         <td>&nbsp;</td>
               </tr>
               <tr>
                    <td height="62">&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
               </tr>
               
               
               
               
               
               

               
               
          </table></td>
     </tr>
      <tr>
          <td height="30" valign="top"><? include('bottom.php') ?></td>
     </tr>
</table>
</body>
</html>

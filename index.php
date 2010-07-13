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

session_start();

dbconnect();

/*
 * Login from form or SU -
 */
if(isset($_POST['login']) || isset($_GET['su'])) 
{ 
  if(isset($_GET['su'])) {
    $sql = 'SELECT * FROM admin WHERE id='.$_GET['admin']; 
    if(!($query=mysql_query($sql))) {
	    exit;
    }
    if(!mysql_num_rows($query)) {
       header('Location: index.php');
	   exit;
    }
    $row = mysql_fetch_assoc($query); 
    $hash = md5($sukey.$row['sustr']);    
    if(strcmp($hash, $_GET['auth']) != 0) { 
        die('Permission denied');
    }
    $sql = 'UPDATE admin SET sustr=NULL WHERE id='.$_GET['admin'];
    if(!mysql_query($sql)) {
        exit;
    }
    $id = $_GET['login']; 
	$ret = get_user_from_id($id);
  }
  else {
    $ret = validate_user($_POST['username'], $_POST['password']);
    if ($ret == -1) {
       $login_error = 'Login Error';
    }
    elseif($ret == -2) {
      $login_error = 'Login Incorrect';
    }  
    elseif(!$ret['active']){
      $login_error = 'Account Disabled'; 
    }
    if(!isset($login_error)) {
        $id = $ret['id'];
    }
  } 
  if(!isset($login_error)) {
   /*
    * Start Session - Login OK!
	*/ 
   if(start_session($id, $ret['type']) == -1) {
     $login_error = 'Error starting session';
    } 
	else {
	  session_unset();
	  extract($ret);
	  setcookie('usernames', trim("$firstName $lastName"));
	  $logstr = "$firstName $lastName ($username) Logged in from $_SERVER[REMOTE_ADDR] ".(isset($_GET['su']) ? '(Account switched to)' : '');	  
	  logaction($logstr);
	  $page = preg_match("/administrator/i", $ret['type']) ? 'surveys.php' : 'msurveys.php'; 
      header("Location: $page");
      exit;
	}
  }
}

if(isset($_GET["logout"])) 
{
   delete_session();
   header("Location: index.php");
   exit();
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title><?= TITLE ?> - Login</title>
<link rel="stylesheet" type="text/css" href="styles/style.css" />
</head>

<body onload="document.forms[0].username.focus()">
<table width="711" border="0" align="center" cellpadding="0" cellspacing="0" class="main">
     <!--DWLayoutTable-->
     <tr>
          <td width="105" height="192">&nbsp;</td>
          <td width="505">&nbsp;</td>
          <td width="101">&nbsp;</td>
     </tr>
     <tr>
        <td height="272">&nbsp;</td>
        <td valign="top"><table width="505" height="272" border="0" cellpadding="0" cellspacing="0" background="images/loginbg.jpg" 
		  style="background-repeat: no-repeat">
              <!--DWLayoutTable-->
              <tr>
                   <td width="28" height="128">&nbsp;</td>
                        <td width="50">&nbsp;</td>
                        <td width="73">&nbsp;</td>
                        <td width="199">&nbsp;</td>
                        <td width="104">&nbsp;</td>
                        <td width="33">&nbsp;</td>
                        <td width="18">&nbsp;</td>
              </tr>
		      <form method="post">
               <tr>
                    <td height="30">&nbsp;</td>
                         <td>&nbsp;</td>
                         <td valign="middle">Username:</td>
                         <td valign="middle"><input name="username" type="text" class="input" id="username" style="width: 190px; font-size: 12px" /></td>
                         <td valign="middle"><img src="images/users.gif" width="25" height="20" style="cursor: pointer" title="Username"/></td>
                         <td>&nbsp;</td>
                         <td>&nbsp;</td>
               </tr>
               <tr>
                    <td height="30">&nbsp;</td>
                         <td>&nbsp;</td>
                         <td valign="middle">Password:</td>
                         <td valign="middle"><input name="password" type="password" class="input" id="password" style="width: 190px;font-size: 12px"/></td>
                         <td valign="middle"><img src="images/keys.gif" width="22" height="25" style="cursor: pointer" title="Password"/></td>
                         <td>&nbsp;</td>
                         <td>&nbsp;</td>
               </tr>			   
               <tr>
                    <td height="31">&nbsp;</td>
                         <td>&nbsp;</td>
                         <td>&nbsp;</td>
                         <td colspan="2" valign="middle"><input name="login" type="submit" class="button" id="login" value="Login" style="cursor: pointer"/></td>
                         <td>&nbsp;</td>
                         <td>&nbsp;</td>
               </tr>
		      </form>
              <tr>
                   <td height="27">&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td colspan="2" valign="top" style="color: #FF0000"><?= $login_error ?></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
              </tr>
              <tr>
                   <td height="12"></td>
                   <td colspan="5" align="center" valign="top" style="font-size: 10px; color: #333333"> Copyright &copy; 2009 <a href="http://www.grameenfoundation.org" style="font-size: 10px; color: #333333" target="_blank">Grameen Foundation</a>. Powered by <a href="http://www.yo.co.ug" title="Yo! Uganda - Voice Solutions & Software Development" style="font-size: 10px; color: #333333" target="_blank">Yo! Uganda</a> </td>
                   <td></td>
              </tr>
              <tr>
                 <td height="14"></td>
                 <td></td>
                 <td></td>
                 <td></td>
                 <td></td>
                 <td></td>
                 <td></td>
              </tr>
               
               
               
               
               
               
               
               
               
               
               
               
        </table></td>
          <td>&nbsp;</td>
     </tr>
     <tr>
        <td height="84">&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
     </tr>
</table>
</body>
</html>

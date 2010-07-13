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

if(!preg_match("/^[0-9]+$/", $numberId) || !preg_match("/^[0-9]+$/", $quizId)) { 
   goto('quiz.php');
}

$quiz = get_quiz_from_id($quizId);
if(empty($quiz)) {
   goto('quiz.php');
}

if(count($_POST)) {
   $_POST = strip_form_data($_POST);
   extract($_POST);
}
if(isset($_POST['cancel'])) {
  goto("numbers.php?quizId=$quizId");
}
if(isset($_POST['submit'])) {
  if(!preg_match("/^[0-9]{8,}$/", $phone)) {
     $errors = 'Phone number not valid<br/>';
  }
  if(!isset($errors)) {
      $sql = "UPDATE quizphoneno SET phone='$phone' WHERE id=$numberId AND quizId=$quizId";
      if(!(mysql_query($sql))) { 
	      if(preg_match("/duplicate/i", mysql_error())) { 
		      $errors = 'This phone number is already added to this Quiz<br/>';
		  }
		  else {
		      show_message('Database Error', $error."<br/>".$sql, '#FF0000');
		  } 
	  } 
	  else {
	  goto("numbers.php?quizId=$quizId");
	  }
  }
} 

$result = execute_query("SELECT * FROM quizphoneno WHERE id=$numberId");
if(!mysql_num_rows($result)) {
   goto("numbers.php?quizId=$quizId");
}
$row = mysql_fetch_assoc($result);

if(isset($errors)) {
  $errors = "<br/>$errors<br/>";
}
/* menu highlight */
$page = 'messaging';
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
        <td height="327" valign="top">
		   <table width="100%" border="0" cellpadding="0" cellspacing="0" class="border">
               <!--DWLayoutTable-->
               <tr>
                    <td height="22" colspan="7" align="center" valign="middle" class="caption">Edit Quiz Phone Number </td>
              </tr>
               <tr>
                    <td width="100" height="19">&nbsp;</td>
                    <td width="58">&nbsp;</td>
                    <td width="70">&nbsp;</td>
                    <td width="229">&nbsp;</td>
                    <td width="237">&nbsp;</td>
                    <td width="41">&nbsp;</td>
                    <td width="53">&nbsp;</td>
                    </tr>
               <tr>
                  <td height="28">&nbsp;</td>
                  <td colspan="5" valign="top"><table width="" border="0" cellpadding="0" cellspacing="0">
                     <!--DWLayoutTable-->
                     <tr>
                        <td width="34" height="28" valign="middle"><img src="images/quiz.gif" width="34" height="28" style="cursor: pointer" title="<?= $quiz['name'] ?>" /></td>
                        <td width="601" valign="middle">&nbsp; <span class="caption2">Quiz: </span>
                           <?= $quiz['name'] ?>
- <span style="color: #666666">created
<?= $quiz['createDate'] ?>
</span></td>
                     </tr>                     
                  </table></td>
                  <td>&nbsp;</td>
               </tr>
               
               <tr>
                  <td height="27">&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td colspan="3" valign="middle" class="error"><?= $errors ?></td>
                  <td>&nbsp;</td>
               </tr>              
               <tr>
                    <td height="17"></td>
                  <td rowspan="3" valign="top">
				  
				  <img src="images/mobile.jpg" width="43" height="70" style="cursor: pointer" title="<?= $row['phone']?>"/></td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td></td>
               </tr>
			   <form method="post">
               <tr>
                  <td height="26"></td>
                  <td colspan="3" valign="middle">
				     <input name="phone" type="text" class="input" id="phone" value="<?= $row['phone'] ?>" size="25" maxlength="15" /></td>
                  <td>&nbsp;</td>
                  <td></td>
               </tr>
               
               
               
               <tr>
                  <td height="27"></td>
                  <td colspan="2" rowspan="2" valign="middle"><input name="submit" type="submit" class="button" id="submit" value="Update Number" />
                     <input name="cancel" type="submit" class="button" id="cancel" value="Cancel" /></td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td></td>
               </tr>
			   </form>
               <tr>
                  <td height="17"></td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td></td>
               </tr>
               
               <tr>
                  <td height="142"></td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
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

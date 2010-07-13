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
include('scripts/functions.php');
session_start();

dbconnect();
validate_session(); 
check_admin_user();

if(count($_POST)) {
  $_POST = strip_form_data($_POST);
  extract($_POST);
}  

if(isset($_POST['cancel'])) {
   if(isset($_GET['return'])) {
      goto($_GET['return']);
   }
   goto('sendsms.php');
}

if(isset($_POST["submit"])) 
{
   if(!strlen($message)) {
     $errors = 'Message not valid<br/>';
   }
   if(!strlen($phones) && !preg_match("/^[0-9]{9,}/", $wimportlist)) {
      $errors .= 'Message recipient(s) not valid<br/>';
   }
   $phonelist = array();
   if(strlen($phones)) {
      $list = preg_split("/[,\s]+/", preg_replace("/,$/", "", trim($phones)));
	  foreach($list as $phone) {
	      $phone = trim($phone);
	      if(!strlen($phone)) {
		continue;
	      }
	      if(!preg_match("/^[0-9]{9,15}$/", $phone)) {
		     $errors .= "Recipient phone number not valid: $phone";
			 break;
		  }
		  $phonelist[] = $phone;
	  }
   }
   if(strlen($wimportlist)) {
        $wimportlist = preg_replace("/,$/", "", $wimportlist);
        $worklist = preg_split("/,/", trim($wimportlist));
        $phonelist = array_merge($phonelist, $worklist);
   } 
   if(!isset($errors)) {
	 sendsms($message, array_unique($phonelist));
	 clear_working_list();
	 $_SESSION['sent'] = TRUE;
	 goto('sendsms.php');
  }
}
if(isset($errors)) {
  $errors = "<br/>$errors<br/>";
}
if(isset($_SESSION['smsphones'])) {
   $phones = implode(', ', $_SESSION['smsphones']); 	  
   session_unregister('smsphones');
}

if(!count($_POST)) 
{
  if(isset($_GET['sendfif']) && preg_match("/^[0-9]+$/", $_GET['surveyId']) && file_exists(FIFDIR.'/'.$fifile)) {
	$message = 'A new survey has been uploaded. Please complete the survey now';
  }
  if(isset($add_from_wl)) 
  {
      $userId = get_user_id();
	  $result = execute_query("SELECT * FROM workinglist WHERE owner='$userId'");
      $_phones = array();;
	  while($row=mysql_fetch_assoc($result)) {
		   $_phones[] = $row['phone'];
	  }
	  if(count($_phones)) {
	        if(strlen($phones)) {
			    $phones .= ', '.implode(', ', $_phones);
			}
			else {
			    $phones = implode(', ', $_phones);
			}
	  }
  }
}

if(strlen($wimportlist)) 
{
   $wimportlist = trim($wimportlist);
   $wimportlist = preg_replace("/,$/", "", $wimportlist);
   $list = preg_split("/,/", $wimportlist);
   if(count($list)) {
      $wlabel = '&nbsp;&nbsp;<span style="color: #008800">Added ('.count($list).') Number(s)</span>
	  <span style="cursor: pointer;color: #FF0000" onclick="removelabel(\'wl\',true)" title="Click to remove numbers">[delete]</span>';
   }
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
<script type="text/javascript" src="basic.js"></script>
</head>

<body class="main">
<table width="790" border="0" align="center" cellpadding="0" cellspacing="0">
     <!--DWLayoutTable-->
     <tr>
          <td width="790" height="124" valign="top"><? include('top.php') ?></td>
     </tr>
     <tr>
          <td height="388" valign="top"><table width="100%" border="0" cellpadding="0" cellspacing="0" class="border">
               <!--DWLayoutTable-->
               <tr>
                    <td height="22" colspan="6" align="center" valign="middle" class="caption">Send Instant SMS </td>
                    </tr>
               <tr>
                    <td width="12" height="18">&nbsp;</td>
                    <td colspan="4" valign="middle" <?= isset($_SESSION['sent']) ? 'height="80"' : ''?>><? require 'messaging.menu.php' ?></td>
                    <td width="11">&nbsp;</td>
               </tr>
               <tr>
                    <td height="18">&nbsp;</td>
                    <td width="57">&nbsp;</td>
                    <td width="222" align="right" valign="middle" <?= isset($_SESSION['sent']) ? 'height="40"' : ''?>>
					   <?= isset($_SESSION['sent']) ? '<img src="images/delivered.gif" width="20" height="20" />' : '' ?></td>
                    <td width="426" valign="middle" class="caption4">
					   <? if(isset($_SESSION['sent'])){ print '&nbsp;Message Successfully Sent'; session_unregister('sent'); } ?> </td>
                    <td width="60">&nbsp;</td>
                    <td>&nbsp;</td>
               </tr>
               <tr>
                  <td height="311">&nbsp;</td>
                  <td>&nbsp;</td>
                  <td colspan="2" valign="top">
                            <fieldset>
                            <legend>Compose Message</legend>
					     <table width="100%" border="0" cellpadding="0" cellspacing="0">
					          <!--DWLayoutTable-->
					          <tr>
					               <td width="17" height="48">&nbsp;</td>
                   <td width="91">&nbsp;</td>
                   <td width="99">&nbsp;</td>
                   <td colspan="2" valign="middle" class="error">
                        <? if(isset($errors)) echo $errors; ?>             </td>
                   <td width="78">&nbsp;</td>
                  <td width="15">&nbsp;</td>
					          </tr>
				<form method="post">

				   <tr>
					                    <td height="83"></td>
                    <td align="center" valign="top"><img src="images/sms.jpg" style="cursor: pointer" title="Send Instant SMS!"/></td>
                    <td align="right" valign="top"><br />
   Message:&nbsp;&nbsp;</td>
                    <td colspan="2" valign="middle"><textarea name="message" class="input" id="message" 
							  style="width: 300px; height: 80px" onkeydown="checklimit(this, 160)"><?= $message ?></textarea>                           </td>
                    <td></td>
                   	<td></td>
				   </tr>
				   <tr>
				      <td height="30"></td>
				      <td></td>
				      <td></td>
				      <td width="34" valign="middle"><input name="chars" type="text" class="input" id="chars" size="4" maxlength="3" readonly="true" value="<?= strlen($content) ?>" /></td>
		                   <td width="312" valign="middle">&nbsp;Characters</td>
		                   <td></td>
				      <td></td>
				   </tr>
				  <tr>
					                    <td height="60">&nbsp;</td>
                    <td colspan="2" align="right" valign="middle">Recipient(s):&nbsp;&nbsp;</td>
                    <td colspan="2" valign="middle"><textarea name="phones" class="input" id="phones" 
							  style="width: 300px; height: 50px"><?= $phones ?></textarea>                    </td>
                    <td>&nbsp;</td>
                   	<td>&nbsp;</td>
				  </tr>
				  <tr>
					                    <td height="20">&nbsp;</td>
                    <td align="right" valign="middle"></td>
                    <td align="right" valign="middle"></td>
                    <td colspan="2" valign="top">(Separate multiple recipients with commas) </td>
                    <td>&nbsp;</td>
                   	<td>&nbsp;</td>
				  </tr>
				  <tr>
					                    <td height="40">&nbsp;</td>
                    <td align="right" valign="middle"></td>
                    <td align="right" valign="middle"></td>
                    <td colspan="3" valign="middle"><a href="#" title="Get Contacts from Group(s) and Working List" style="color: #FF3300; text-decoration: underline" onclick="importn('worklist');return false;">[Send To Group]</a> <span id="wlabel">
                    			<?= $wlabel ?>
                    </span></td>
                    <td>&nbsp;</td>
				  </tr>					   				   
					               <tr>
					                  <td height="35"></td>
					                  <td></td>
					                  <td></td>
					                  <td colspan="2" valign="middle">
									     <input name="submit" type="submit" class="button" id="submit" value="Send SMS" />
									     <input name="cancel" type="submit" class="button" id="cancel" value="Cancel" 
										 <?= !isset($return) ? 'disabled="disabled"' : '' ?> />
									     <input name="wimportlist" type="hidden" id="wimportlist" value="<?= $wimportlist ?>" /></td>
                    <td></td>
			                     <td></td>
					               </tr>
					               <tr>
					                  <td height="20"></td>
					                  <td></td>
					                  <td></td>
					                  <td></td>
					                  <td></td>
					                  <td></td>
	                    <td></td>
					               </tr>
					               </form>
				            </table>
                       </fieldset></td>
                         <td>&nbsp;</td>
                         <td>&nbsp;</td>
               </tr>
               <tr>
                  <td height="36">&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
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

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
include("excel/functions.php");

dbconnect();
validate_session(); 
check_admin_user();

$user = get_user_details();

extract($_GET);

if(!preg_match("/^[0-9]+$/", $messageId)) { 
   goto('messages.php');
}

$sql = "SELECT message.*, DATE_FORMAT(sendTime, '%d/%m/%Y') AS date, 
        DATE_FORMAT(sendTime, '%H') AS hr, DATE_FORMAT(sendTime, '%i') AS min 
		FROM message WHERE id=$messageId";

$result = execute_query($sql);
if(!mysql_num_rows($result)) {
   goto('messages.php');
}
$message_details = mysql_fetch_assoc($result);

if(isset($_POST["cancel"])) {
 goto("smessage.php?messageId=$messageId");
}

if(count($_POST)) {
  $_POST = strip_form_data($_POST);
  extract($_POST);
}  

if(isset($_POST["submit"])) {
   if(strlen($_POST['name']) && strlen($_POST['name']) < 3) {
     $errors = 'Message name not valid<br/>';
   }
   if(strlen($message) < 2) {
      $errors .= 'Message not valid<br/>';
   }
   /* check date */
   if(!strlen($date)) {
		     $errors .= "Date for sending Message is not valid<br/>";
   }	 
   /* check minute */
   if(!preg_match("/^[0-9]{1,2}$/", $min)) {
		      $errors .= "Time for sending Message not valid<br/>";
   }	  
   if(strlen($senderPhoneNumber)) {
        if(!preg_match("/^[a-zA-Z0-9\s]+$/", $senderPhoneNumber)) {
                $errors .= "Your Sender Phone is not valid. You may only use alphanumeric characters. Leave this field blank if unsure about its significance.";
        }
   }
   if(!strlen($errors)) {
      /* check that send time is not in the past */
      $darr = preg_split("/\//", $date);
      $d = array_shift($darr);
      $m = array_shift($darr);
      $y = array_shift($darr);
      if(mktime($hr, $min, 0, $m, $d, $y) < time()) {
	      $errors .= "Time for sending Message is in the past<br/>";
      } 
      else {	
	      $sendTime = "$y-$m-$d $hr:$min:00";   
      }
   }
   $phone_numbers = read_phone_numbers(); 
   if($phone_numbers == 0) {
      $errors .= 'No valid phone numbers found in uploaded file<br/>';
   } 
   
   if(!isset($errors)) {
     $mname = strlen($_POST['name']) ? mysql_real_escape_string($_POST['name']) : 'M'.date('Ymd', time());
	 $message = mysql_real_escape_string($message);
     $sql = "UPDATE message SET name='$mname', message='$message', sendTime='$sendTime', senderPhoneNumber='$senderPhoneNumber' WHERE id=$messageId";
	 execute_nonquery($sql);
	 /* the phone numbers */	 
	 foreach($phone_numbers as $phone) {
	    if(!preg_match("/^[0-9]{8,}$/", $phone)) {
		   continue;
		} 	 
	    $sql = "INSERT INTO recipient (messageId, phone) VALUES ($messageId, '$phone')";
		if(!mysql_query($sql)) { 
		    $error = mysql_error();
			if(preg_match("/duplicate/i", $error)) { 
		       continue;
			}
			else {
			   show_message('Database Error', $error."<br/>$sql", '#FF0000');
			}
		}
	 }
	 //logaction("Scheduled message: $mname");
	 clear_working_list();
	 goto("smessage.php?messageId=$messageId");
  }
}

if(strlen($qimportlist)) {
   $qimportlist = trim($qimportlist);
   $qimportlist = preg_replace("/,$/", "", $qimportlist);
   $list = preg_split("/,/", $qimportlist);
   if(count($list)) {
      $qlabel = '&nbsp;<span style="color: #008800">Imported Nos. from ('.count($list).') quiz(es)</span>
	  <span style="cursor: pointer;color: #FF0000; font-size: 10px" onclick="removelabel(\'ql\')" title="Click to remove numbers">[delete]</span>';
   }
} 
if(!isset($qlabel)) {
   $qlabel = '&nbsp;Import Numbers From Existing Quiz';
}

if(strlen($wimportlist)) {
   $wimportlist = trim($wimportlist);//print $wimportlist;
   $wimportlist = preg_replace("/,$/", "", $wimportlist);
   $list = preg_split("/,/", $wimportlist);
   if(count($list)) {
      $wlabel = '&nbsp;&nbsp;<span style="color: #008800">Added ('.count($list).') Number(s)</span>
	  <span style="cursor: pointer;color: #FF0000" onclick="removelabel(\'wl\',true)" title="Click to remove numbers">[delete]</span>';
   }
}

if(strlen($gimportlist)) {
   $gimportlist = trim($gimportlist);
   $gimportlist = preg_replace("/,$/", "", $gimportlist);
   $list = preg_split("/,/", $gimportlist);
   if(count($list)) {
      $glabel = '&nbsp;<span style="color: #FF3300">Imported ('.count($list).') Number(s)</span>
	  <span style="cursor: pointer;color: #FF0000; font-size: 10px" onclick="removelabel(\'gl\')" title="Click to remove numbers">[delete]</span>';
   }
} 
if(!isset($glabel)) {
   $glabel = '&nbsp;Import Numbers From General List';
}
if(!strlen($_POST['numbers'])) {
/* get recipients */
$recipients = array();
$sql = "SELECT * FROM recipient WHERE messageId=$messageId";
$result = execute_query($sql);
while($row=mysql_fetch_assoc($result)) {
   $recipients[] = $row['phone'];
}
$numbers = implode(', ', $recipients);
}
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
<link rel="stylesheet" type="text/css" href="styles/date.css" />
<script type="text/javascript" src="date.js"></script>
<script type="text/javascript" src="basic.js"></script>
</head>

<body class="main">
<table width="790" border="0" align="center" cellpadding="0" cellspacing="0">
     <!--DWLayoutTable-->
     <tr>
          <td width="790" height="124" valign="top"><? include('top.php') ?></td>
     </tr>
     <tr>
        <td  valign="top">
		       <table width="100%" border="0" cellpadding="0" cellspacing="0" class="border">
		            <!--DWLayoutTable-->
		            <tr>
		                 <td height="22" colspan="3" align="center" valign="middle" class="caption">Edit Message </td>
                    </tr>
		            <tr>
		                 <td width="15" height="20">&nbsp;</td>
                         <td width="747" valign="top"><? require 'messaging.menu.php' ?></td>
                         <td width="26">&nbsp;</td>
                    </tr>
		            <tr>
		               <td height="542">&nbsp;</td>
		               <td valign="top">
                            <fieldset>
                            <legend>Message Details</legend>
				            <table width="745" border="0" cellpadding="0" cellspacing="0">
				                 <!--DWLayoutTable-->
				                 <tr>
				                      <td width="29" height="23">&nbsp;</td>
                        <td width="58">&nbsp;</td>
                        <td width="46">&nbsp;</td>
                        <td colspan="11" valign="top" class="error">
                           <? if(isset($errors)) echo $errors; ?>					   </td>
                        <td width="16">&nbsp;</td>
				                 </tr>
					     <form method="post" enctype="multipart/form-data">
					     <tr>
					     <td height="28" colspan="3" align="right" valign="middle">Name:&nbsp;&nbsp;</td>
                         <td colspan="11" valign="middle">
						    <input name="name" type="text" class="input" id="name" value="<?= $message_details['name'] ?>" size="55" /></td>
                         <td>&nbsp;</td>
		                    </tr>

                                             <tr>
                                             <td height="28" colspan="3" align="right" valign="middle">Sender Phone:&nbsp;&nbsp;</td>
                         <td colspan="11" valign="middle">
                                                    <input name="senderPhoneNumber" type="text" class="input" id="senderPhoneNumber" value="<?= $message_details['senderPhoneNumber'] ?>" size="55" /></td>
                         <td>&nbsp;</td>
                                    </tr>

                                    <tr><td height="28" colspan="3">&nbsp;</td><td colspan="11"><font style="font-size: 9px;">Set the <b>Sender Phone</b> to the phone number of the sender so that the messages appear to come from this phone number or name. Note that this feature is only available for SMS submission through SMPP connections.</font></td><td>&nbsp;</td></tr>

					                    <tr>
					                         <td height="50">&nbsp;</td>
                                             <td>&nbsp;</td>
                                             <td colspan="12" valign="middle" class="caption2">Add Recipients For this Message </td>
                                             <td>&nbsp;</td>
					                    </tr>	
					                    <tr>
					                         <td height="44">&nbsp;</td>
                                             <td colspan="3" align="right" valign="middle">Browse File:&nbsp;&nbsp;</td>
                                           <td colspan="2" valign="middle"><img src="images/excel.jpg" width="30" height="30" style="cursor: pointer" title="Input phone numbers from file"/><br /></td>
                                           <td colspan="3" valign="middle" nowrap="nowrap">
										   &nbsp;
										   <input type="file" name="file" size="30" style="font-size: 11px"/></td>
                         <td colspan="5" valign="middle">&nbsp;<span style="color: #FF0000">*</span>Excel or (CSV - One No. per line) </td>
                                           <td>&nbsp;</td>
					                    </tr>
					                    <tr>
					                       <td height="60"></td>
					                       <td>&nbsp;</td>
					                       <td>&nbsp;</td>
					                       <td width="28">&nbsp;</td>
					                       <td colspan="4" valign="middle"><img src="images/group.jpg" width="45" height="45" style="cursor: pointer" 
										   title="Click to Import Numbers" onclick="importn('quiz')"/></td>
					                       <td colspan="2" valign="middle" id="qlabel"><?= $qlabel ?></td>
					                       <td width="38" valign="middle"><img src="images/import.gif" width="38" height="46" style="cursor: pointer" title="Click to import numbers from the general list" onclick="importn('general')"/></td>
					                       <td colspan="3" valign="middle" id="glabel"><?= $glabel ?></td>
					                       <td></td>
	                        </tr>
					                    <tr>
					                       <td height="40"></td>
					                       <td colspan="4" align="right" valign="top"></td>
					                       <td colspan="8" valign="middle">
										   <a href="#" title="Get Contacts from Group(s) and Working List" style="color:#FF3300; text-decoration:underline" onclick="importn('worklist');return false;">[Send to Group]</a><span id="wlabel">
					                       			<?= $wlabel ?>
					                       </span></td>
					                       <td width="120">&nbsp;</td>
					                       <td></td>
	                        </tr>					                    
					                    <tr>
					                       <td height="100"></td>
					                       <td colspan="4" align="right" valign="top"><br />
                                              <br />
                                           Input Numbers:&nbsp;&nbsp; </td>
					                       <td colspan="8" valign="middle">
										      <textarea name="numbers" style="width: 280px; height: 70px"  
										   class="input" id="numbers"><?= $numbers ?></textarea>										      									       </td>
					                       <td width="120">&nbsp;</td>
					                       <td></td>
	                        </tr>
					                    <tr>
					                       <td height="14"></td>
					                       <td></td>
					                       <td></td>
					                       <td></td>
					                       <td width="19"></td>
					                       <td colspan="8" valign="top">(Separate with commas) </td>
					                       <td></td>
					                       <td></td>
	                        </tr>
					                    <tr>
					                       <td height="100"></td>
					                       <td colspan="4" align="right" valign="top"><br />
                                              <br />
                                           Message:&nbsp;&nbsp; </td>
					                       <td colspan="8" valign="middle">
										      <textarea name="message" style="width: 280px; height: 70px"  
										   class="input" id="message"><?= $message_details['message'] ?></textarea>										      									       </td>
					                       <td width="120">&nbsp;</td>
					                       <td></td>
	                        </tr>
			                    <tr>
					                       <td height="35"></td>
					                       <td colspan="4" align="right" valign="middle">Send Time:&nbsp;&nbsp; </td>
					                       <td colspan="2" valign="middle"><img src="images/datepicker.gif" width="16" height="16" style="cursor: pointer" title="Select Date" onclick="displayDatePicker('date')"/></td>
					                       <td colspan="5" valign="middle">&nbsp;<input name="date" type="text" class="input" id="date" style="cursor: pointer" title="Select Date" onclick="displayDatePicker('date')" value="<?= $message_details['date'] ?>" size="15" readonly="true"/>
					                          <select name="hr" class="input" id="hr">
                                                 <?= hours($message_details['hr']) ?>
                                              </select>
					                          <input name="min" type="text" class="input" id="min" value="<?= $message_details['min'] ?>" size="4" />
HRS </td>
					                       <td width="41">&nbsp;</td>
					                       <td width="120">&nbsp;</td>
					                       <td></td>
	                        </tr>					                    
					                    

					                    <tr>
					                         <td height="52">&nbsp;</td>
                         <td>&nbsp;</td>
                         <td>&nbsp;</td>
                         <td>&nbsp;</td>
                         <td>&nbsp;</td>
                         <td colspan="9" valign="middle"><input name="submit" type="submit" class="button" id="submit" value="Submt Message" />
                            <input name="cancel" type="submit" class="button" id="cancel" value="Cancel" />
                            <input name="qimportlist" type="hidden" id="qimportlist" value="<?= $qimportlist ?>" />
                            <input name="gimportlist" type="hidden" id="gimportlist" value="<?= $gimportlist ?>" />
                            <input name="wimportlist" type="hidden" id="wimportlist" value="<?= $wimportlist ?>" /></td>
                         <td>&nbsp;</td>
					                    </tr>
					                    <tr>
					                       <td height="21">&nbsp;</td>
					                       <td>&nbsp;</td>
					                       <td>&nbsp;</td>
					                       <td>&nbsp;</td>
					                       <td>&nbsp;</td>
					                       <td width="11">&nbsp;</td>
					                       <td width="5">&nbsp;</td>
					                       <td width="10">&nbsp;</td>
					                       <td width="213">&nbsp;</td>
					                       <td width="24">&nbsp;</td>
					                       <td>&nbsp;</td>
					                       <td width="87">&nbsp;</td>
					                       <td>&nbsp;</td>
					                       <td>&nbsp;</td>
					                       <td>&nbsp;</td>
	                        </tr>
		                       </form>
		                    </table>
                            </fieldset></td>
                       <td>&nbsp;</td>
                  </tr>
		            <tr>
		               <td height="45">&nbsp;</td>
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

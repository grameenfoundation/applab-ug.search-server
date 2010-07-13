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

if(isset($requeue)) {
   $sql = "UPDATE recipient SET status=NULL WHERE messageId='".$messageId."'";
   if(!mysql_query($sql)) {
	$statusMessage = "Error requeueing this message: ".mysql_error();
   } else {
        $sql = "UPDATE message SET messageStatus='1' WHERE id='".$messageId."'";
	if(!mysql_query($sql)) {
		$statusMessage = "Error requeueing this message: ".mysql_error();
	} else {
		$statusMessage = "This message has been successfully requeued and shall be delivered at the stipulated time.";
	}
   }
   goto("smessage.php?messageId=$messageId&statusMessage=".urlencode($statusMessage));
}

if(isset($_POST['recipients'])) {
   goto("recipients.php?messageId=$messageId");
}
if(isset($_POST['add_recipients'])) {
   goto("addrecipient.php?messageId=$messageId");
}
if(isset($_POST['edit'])) {
   goto("editmessage.php?messageId=$messageId");
}
if(isset($_POST['cancel'])) {
   goto("messages.php");
}   
if(isset($_POST['delete'])) {
   delete_message($messageId);
   goto('messages.php');
}

if(!preg_match("/^[0-9]+$/", $messageId)) { 
   goto('messages.php');
}
$sql = "SELECT message.*, DATE_FORMAT(sendTime, '%d/%m/%Y %r') AS sendTime, 
        DATE_FORMAT(createDate, '%d/%m/%Y %T') AS createDate, DATE_FORMAT(sendTime, '%d/%m/%Y %T') AS sendTime 
		FROM message WHERE id=$messageId";

$result = execute_query($sql);
if(!mysql_num_rows($result)) {
   goto('messages.php');
}
$message = mysql_fetch_assoc($result);
switch($message['status']) {
  case 'pending': $title = 'Pending. No attempt made to send Message'; break;
  case 'sent': $title = 'Message sent. Delivery not confirmed'; break;
  case 'delivered': $title = 'Message successfully delivered'; break;   
}
/* recipients */
$sql = "SELECT * FROM recipient WHERE messageId=$messageId";
$result = execute_query($sql);
$total = mysql_num_rows($result);

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
          <td height="421" valign="top"><table width="100%" border="0" cellpadding="0" cellspacing="0" class="border">
               <!--DWLayoutTable-->
               <tr>
                    <td height="22" colspan="4" align="center" valign="middle" class="caption">Scheduled Message </td>
                    </tr>
               <tr>
                    <td width="56" height="20">&nbsp;</td>
                    <td width="25">&nbsp;</td>
                    <td width="654">&nbsp;</td>
                    <td width="53">&nbsp;</td>
                    </tr>
               <tr>
                    <td height="28">&nbsp;</td>
                    <td valign="middle"><img src="images/msg.gif" border="0" /></td>
                    <td valign="middle">&nbsp;
					   <span class="caption2">Message: </span>
					   <?= $message['name'] ?> 
					- 
					<span style="color: #666666">created <?= $message['createDate'] ?>
					</span>					</td>
                    <td>&nbsp;</td>
               </tr>
               <tr>
                    <td height="26">&nbsp;</td>
                    <td colspan="3">&nbsp;<font color="red"><b><div align="center"><? echo $statusMessage; ?></div></b></td>
               </tr>
               <tr>
                  <td height="322">&nbsp;</td>
                  <td colspan="2" valign="top">
				       <fieldset>
					<legend>Message Details</legend>
					<table width="100%" border="0" cellpadding="0" cellspacing="0" 
					style="background-image: none; background-repeat: no-repeat; background-position: center">
					   <!--DWLayoutTable-->
                         <tr>
                              <td width="39" height="20">&nbsp;</td>
                              <td width="52">&nbsp;</td>
                              <td width="129">&nbsp;</td>
                              <td width="362">&nbsp;</td>
                              <td width="95">&nbsp;</td>
                         </tr>                         <tr>
                              <td width="39" height="35">&nbsp;</td>
                              <td width="52">&nbsp;</td>
                              <td width="129" align="right" valign="middle" class="label">Message:&nbsp;&nbsp;</td>
                              <td width="362" valign="middle" class="field" style="padding: 3px 0px 3px 0px"><?= $message['message'] ?></td>
                              <td width="95">&nbsp;</td>
                         </tr>
                         
                         <tr>
                              <td height="35">&nbsp;</td>
                              <td>&nbsp;</td>
                              <td align="right" valign="middle" class="label">Send Time:&nbsp;&nbsp;</td>
                              <td valign="middle" class="field"><?= $message['sendTime'] ?></td>
                              <td>&nbsp;</td>
                         </tr>
                        <tr>
                              <td height="35">&nbsp;</td>
                              <td>&nbsp;</td>
                              <td align="right" valign="middle" class="label">Create Time:&nbsp;&nbsp;</td>
                              <td valign="middle" class="field"><?= $message['createDate'] ?></td>
                              <td>&nbsp;</td>
                        </tr>
                        <tr>
                              <td height="35">&nbsp;</td>
                              <td>&nbsp;</td>
                              <td align="right" valign="middle" class="label">Total Recipients :&nbsp;&nbsp;</td>
                              <td valign="middle" style="font-weight: bold">
							  <?= $total ?>&nbsp;</td>
                              <td>&nbsp;</td>
                        </tr>
                        <tr>
                              <td height="28">&nbsp;</td>
                              <td>&nbsp;</td>
                              <td align="right" valign="middle" class="label">Scheduled By:&nbsp;&nbsp;</td>
                              <td valign="middle" class="field" style="color: #008800">
							  <?= $message['admin'] ?>&nbsp;</td>
                              <td>&nbsp;</td>
                        </tr>	
<? if(strlen($message['senderPhoneNumber'])) { ?>
                        <tr>
                              <td height="28">&nbsp;</td>
                              <td>&nbsp;</td>
                              <td align="right" valign="middle" class="label">Sender Phone:&nbsp;&nbsp;</td>
                              <td valign="middle" class="field" style="color: #008800">
                                                          <?= $message['senderPhoneNumber'] ?>&nbsp;</td>
                              <td>&nbsp;</td>
                        </tr>
<? } ?>
                        <tr>
                              <td height="35">&nbsp;</td>
                              <td>&nbsp;</td>
                              <td align="right" valign="middle" class="label">Updated:&nbsp;&nbsp;</td>
                              <td valign="middle" class="field">
							  <?= $message['updated']?>&nbsp;</td>
                              <td>&nbsp;</td>
                        </tr>	
						<form method="post">					 					 						 					 						 
                         <tr>
                              <td height="48">&nbsp;</td>
                              <td colspan="4" valign="middle" class="field"><input name="recipients" type="submit" class="button" id="recipients" value="Recipients"/>
                                 <input name="add_recipients" type="submit" class="button" id="add_recipients" value="Add Recipients"/> 
                                 <input name="edit" type="submit" class="button" id="edit" value="Edit Message"/>
                                 <input name="delete" type="submit" class="button" id="delete" value="Delete Message" onclick="return confirm('Are you sure?')"/>
                           <input name="cancel" type="submit" class="button" id="cancel" value="Go To Messages" />
			   <input type="button" class="button" value="Requeue" onclick="var response= confirm('Are you sure? Carrying out this operation shall result in the message being re-sent to ALL phone numbers registered for this message. This may result in duplicate delivery of the message to certain phone numbers, if they had already received the message. Remember that you need to have adjusted the sending time for this message to the time when you wish the message to be delivered before using the Requeue function.'); if(response==true) { location.replace('smessage.php?messageId=<? echo $messageId ?>&requeue=1'); return true; } else { return false; } " />
			   </td>
                         </tr>
                         <tr>
                            <td height="16">&nbsp;</td>
                            <td>&nbsp;</td>
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
                  <td height="40">&nbsp;</td>
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

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

if(isset($_POST['schedule'])) {
    $args = isset($add_from_wl) ? '?add_from_wl=TRUE' : NULL;
    redirect('addmessage.php'.$args);
}
if(isset($_POST['cancel'])) {
    redirect('workinglist.php');
}

if(!isset($start) || !is_numeric($start)) {
    $start = 0;
}

if(isset($add_from_wl)) 
{
    $userId = get_user_id();
	$result = execute_query("SELECT * FROM workinglist WHERE owner='$userId'");
    $_total = mysql_num_rows($result);
	if(isset($submit_wl)) {
	     while($row=mysql_fetch_assoc($result)) {
		      $sql = "INSERT INTO recipient(messageId, phone) 
			          VALUES ('$messageId', '$row[phone]') ON DUPLICATE KEY UPDATE updated=NOW()";
		      execute_update($sql);
		 }
		 redirect("smessage.php?messageId=$messageId");
	}
	$addconfirm = 'onclick="return confirm(\'Add '.$_total.' user(s) to this Message?\');"';
    $next_app = '&add_from_wl=add';
}
else {
    $next_app = NULL;
}

if(isset($delete)) {
   if(!preg_match("/^[0-9]+$/", $messageId)) {
       goto("messages.php?start=$start");
   }
   delete_message($messageId);
   goto("messages.php?start=$start");	   
}
if(isset($activate)) {
   if(!preg_match("/^[0-9]+$/", $messageId)) {
       goto("messages.php?start=$start");
   }
   activate_message($messageId);
   goto("messages.php?start=$start");
}

if(isset($deactivate)) {
   if(!preg_match("/^[0-9]+$/", $messageId)) {
       goto("messages.php?start=$start");
   }
   deactivate_message($messageId);
   goto("messages.php?start=$start");
}

$sql = "SELECT message.*, DATE_FORMAT(sendTime, '%d/%m/%Y %r') AS sendTime, DATE_FORMAT(sendTime, '%Y%m%d%H%i') AS stamp, 
        DATE_FORMAT(createDate, '%d/%m/%Y %r') AS created, DATE_FORMAT(updated, '%d/%m/%Y %r') AS updated FROM message";
$result = execute_query($sql);

$total = mysql_num_rows($result);

$limit = 30;
$this_pg = $start + $limit;
$next = $start + $limit;
$back = $start - $limit;

/* listing */
$sql .= " ORDER BY createDate DESC LIMIT $start, $limit";
$result = execute_query($sql);

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
          <td height="362" valign="top"><table width="100%" border="0" cellpadding="0" cellspacing="0" class="border">
               <!--DWLayoutTable-->
               <tr>
                    <td height="22" colspan="3" align="center" valign="middle" class="caption">Scheduled Messages - Total:                       <?= $total ?> </td>
             </tr>
               <tr>
                    <td height="18" colspan="3" valign="middle"><? require 'messaging.menu.php' ?></td>
               </tr>
               <tr>
                    <td width="14" height="300">&nbsp;</td>
                  <td width="757" valign="top">
		             <? 	
		$html = 
		'<table width="100%" border="0" cellpadding="0" cellspacing="0">
		 <tr>
		  <td colspan="4" height="45" valign="middle">
		   <form method=post>
		       <input type="submit" name="schedule" class="button" value="Schedule Message" />
		   </form>
		  </td>
		</tr>
		 <tr class="title1"> 
		  <td height="30" valign="top" colspan="2"><u>Name</u></td> 
		  <td valign="top"><u>Send Time</u></td>
		  <!--td valign="top"><u>Created</u></td-->
		  <td valign="top"><u>Updated</u></td>
		  
		  <td valign="top"><u>Options</u></td>
		 <tr>';
		$color = '#E4E4E4'; $i=0;
		while($row = mysql_fetch_assoc($result)) {
		 if($row[messageStatus] == '1') {
			 $deactivationBlurb=' <a href="?start='.$start.'&messageId='.$row['id'].'&deactivate=TRUE" style="color: #FF0000" title="Suspend sending" onclick="return confirm(\'Are you sure you wish to suspend this message? Delivery for this message shall be suspended and shall not be started again until you request delivery to be resumed. Confirm if this is the action you wish to take.\')">Suspend</a>';
		 } else {
			$deactivationBlurb=' <a href="?start='.$start.'&messageId='.$row['id'].'&activate=TRUE" style="color: #FF0000" title="Resume sending" onclick="return confirm(\'Are you sure you wish to resume delivery for this message? Delivery for this message shall resume immediately. Confirm if this is the action you wish to take.\')">Resume</a>';
		 }

		 $color = $i++%2 ? '#FFFFFF' : '#EEEEEE';
		 $name = $row['name'];
		 if(strlen($name) > 25) {
		    $name = substr($name, 0, 25).'..';
		 }
		 /*if(strlen($row['updated']) > 5) {
		    $row['updated'] = substr($row['updated'], 0, 5);
		 } */

		 if($row['status'] == 'pending') {
		     $title = 'Pending. No attempt made to send Message';
		 }		
		 if($row['status'] == 'sent') {
		    $title = 'Message sent. Delivery not confirmed';
		 }	
		 if($row['status'] == 'delivered') {
		    $title = 'Message successfully delivered';
		 }			
		 
		 $addlink = isset($add_from_wl) ? '<a href="?start='.$start.'&messageId='.$row['id'].
		 '&add_from_wl=add&submit_wl=true" '.$addconfirm.'>[Add Users]</a> | ' : NULL;
		 
		 $html .= '
		 <tr bgcolor="'.$color.'" 
		 onmouseover="this.style.backgroundColor=\''.HOVERCOLOR.'\'" onmouseout="this.style.backgroundColor=\''.$color.'\'"> 
		  <td width="30" height="30" style="padding-left: 3px">
		    <a href="smessage.php?messageId='.$row["id"].'" tittle="'.$row['name'].'"><img src="images/msg.gif" border="0" /></a>
		  </td>
		  <td>
		    &nbsp;<a href="smessage.php?messageId='.$row["id"].'" style="color: #000000" title="Click to Go to Message">'.$name.'</a>
		  </td> 
		  <td style="color: '.($row['stamp'] < date('YmdHi') ? '#999999' : '#000000').'">'.$row['sendTime'].'</td>
		  <!--td>'.$row['created'].'</td-->		  
		  <td>'.$row['updated'].'</td>
		  <td>
		  '.$addlink.'
		  <a href="editmessage.php?messageId='.$row["id"].'">Edit</a> | 
		  <a href="?start='.$start.'&messageId='.$row['id'].'&delete=TRUE'.$next_app.'" style="color: #FF0000" 
		   title="Delete Message" onclick="return confirm(\'Are you sure you want to delete this Message?\')">Delete</a> |'.$deactivationBlurb.'
		  </td>
		 <tr>';
		}
	    if($total > $limit) {
	      $scroll = '
	      <tr>
	       <td height="25" colspan="5" valign="bottom">
		    <div style="text-align: justify;">';
	      if($back >= 0) 
          $scroll .= '<a href="?start='.$back.$next_app.'" style="color: #000000">&laquo; Prev</a> ';
          for($i=0, $l=1; $i < $total; $i= $i + $limit){
           if($i != $start)
		    $scroll .= '<a href="?start='.$i.$next_app.'">'.$l.'</a> ';
           else $scroll .= '<span style="color: #ff0000; font-weight: bold">'.$l.'</span> ';
           $l = $l+1;
	      }
	      if($this_pg < $total) 
	       $scroll .= ' <a href="?start='.$next.$next_app.'" style="color: #000000">Next &raquo;</a>';
		  if($l>2) $html = "$html$scroll <div/></td></tr>";		   
	     }	
		$html .='
		<tr>
		  <td colspan="4" height="45" valign="middle">
		  <form method=post>
		    <input type="submit" name="schedule" class="button" value="Schedule Message" />
			'.(isset($add_from_wl) ? '<input type="submit" name="cancel" class="button" value="Back To Working List" />' : '').'
		  </form>
		  </td>
		</tr>';
		 print $html.'</table>'; 
		?>		</td>
        <td width="17">&nbsp;</td>
             </tr>
               <tr>
                  <td height="20">&nbsp;</td>
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

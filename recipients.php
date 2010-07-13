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

if(!isset($start) || !is_numeric($start)) {
  $start = 0;
}

if(isset($_POST['delete'])) {
   foreach($_POST as $key=>$val) {
      if(!preg_match("/^p[0-9]+$/", $key) || !is_numeric($val)) {
	     continue;
	   }	 
	  $sql = "DELETE FROM recipient WHERE id=$val LIMIT 1";
	  execute_nonquery($sql);
   }
   goto("recipients.php?messageId=$messageId&start=$start");
}   

if(isset($_GET['delete'])) {
   if(!preg_match("/^[0-9]+$/", $recipientId)) {
       goto("recipients.php?messageId=$messageId&start=$start");
   }
   $sql = "DELETE FROM recipient WHERE id=$recipientId LIMIT 1";
   execute_nonquery($sql);
   goto("recipients.php?messageId=$messageId&start=$start");
}

$sql = "SELECT recipient.*, DATE_FORMAT(createDate, '%d/%m/%Y %T') AS createDate, 
        DATE_FORMAT(updated, '%d/%m/%Y %T') AS updated FROM recipient WHERE messageId=$messageId";
$result = execute_query($sql);

$total = mysql_num_rows($result);

$limit = 40;
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
<script type="text/javascript" src="basic.js"></script>
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
                    <td height="22" colspan="4" align="center" valign="middle" class="caption">
					Message Recipients - Total: <?= $total ?>					</td>
             </tr>
               <tr>
                    <td width="17" height="13"></td>
                    <td width="25"></td>
                    <td width="729"></td>
                  <td width="17"></td>
               </tr>
               <tr>
                  <td height="28"></td>
                  <td valign="middle"><img src="images/msg.gif" border="0" /></td>
                  <td valign="middle">&nbsp; <span class="caption2">Message:</span>
                     <?= $message['name'] ?>
- <span style="color: #666666">created
<?= $message['createDate'] ?>
</span></td>
                  <td></td>
               </tr>
               <tr>
                  <td height="20"></td>
                  <td></td>
                  <td></td>
                  <td></td>
               </tr>
               
               
               <tr>
                  <td height="325">&nbsp;</td>
                  <td colspan="2" valign="top">
		<? 	
		$html = 
		'<table width="100%" border="0" cellpadding="0" cellspacing="0">
		 <tr class="title1"> 
		  <td height="30" valign="top"><u>Phone Number</u></td> 
		  <td valign="top" colspan="2"><u>Delivery Status</u></td>
		  <td valign="top"><u>Create Date</u></td>
		  <td valign="top"><u>Updated</u></td>
		  <td valign="top"><u>Options</u></td>
		 <tr><form method="post">';
		$color = '#EEEEEE'; $i=0;
		$list = NULL;
		while($row = mysql_fetch_assoc($result)) {
		 $color = $i++%2 ? '#FFFFFF' : '#EEEEEe';
		 $list .= "p$row[id],";
		 if(!strlen($row['status'])) {
		    $status = 'PENDING';
			$img = 'pending.gif';
		 }
		 elseif(preg_match("/Accepted\sfor\sdelivery/i", $row['status'])) {
		    $status = 'DELIVERED';
			$img = 'delivered.gif';
		 }
		 else {
		    $status = $row['status'];
			$img = 'rejected.gif';
		 }
		 $html .= '
		 <tr bgcolor="'.$color.'" 
		 onmouseover="this.style.backgroundColor=\''.HOVERCOLOR.'\'" onmouseout="this.style.backgroundColor=\''.$color.'\'"> 
		  <td height="25">
		  <input type="checkbox" name="p'.$row['id'].'" id="p'.$row['id'].'" value="'.$row['id'].'" />
		    <a style="color: #000000" 
			href="editrecipient.php?recipientId='.$row["id"].'&messageId='.$messageId.'" tittle="'.$row['phone'].'">'.get_phone_display_label($row['phone']).'</a>
		  </td>		  
		  <td align="right">
		    <img src="images/'.$img.'" style="cursor: pointer" title="'.$status.'"/>
		  </td>
		  <td>&nbsp;&nbsp;'.$status.'</td>
		  <td>'.$row['createDate'].'</td>
		  <td>'.$row['updated'].'</td>
		  <td>
		  <a href="editrecipient.php?recipientId='.$row["id"].'&messageId='.$messageId.'">Edit</a> | 
		  <a href="?start='.$start.'&messageId='.$messageId.'&recipientId='.$row['id'].'&delete=TRUE" style="color: #FF0000" 
		   title="Delete Recipient" onclick="return confirm(\'Are you sure you want to delete this phone number?\')">Delete</a>
		  </td>
		 <tr>';
		}
	    if($total > $limit) {
	      $scroll = '
	      <tr>
	       <td height="25" colspan="6" valign="bottom">
		    <div style="text-align: justify;">';
	      if($back >= 0) 
          $scroll .= '<a href="?start='.$back.'&messageId='.$messageId.'" style="color: #000000">&laquo; Prev</a> ';
          for($i=0, $l=1; $i < $total; $i= $i + $limit){
           if($i != $start)
		    $scroll .= '<a href="?start='.$i.'&messageId='.$messageId.'">'.$l.'</a> ';
           else $scroll .= '<span style="color: #ff0000; font-weight: bold">'.$l.'</span> ';
           $l = $l+1;
	      }
	      if($this_pg < $total) 
	       $scroll .= ' <a href="?start='.$next.'&messageId='.$messageId.'" style="color: #000000">Next &raquo;</a>';
		  if($l>2) $html = "$html$scroll <div/></td></tr>";		   
	     }	
		/* options - */
		$options = '<option value="ALL">ALL</option>';
		foreach($msgstatus as $status) {
		   $options .= '<option value="'.$status['status'].'">'.$status['name'].'</option>';
		}		 
		$html .='
		<tr>
		  <td colspan="6" height="45" valign="middle">
		   <table border="0">
		    <tr>
			 <td>
             <input type="button" class="button" value="Select All" onclick="selectall(true)" />	
             <input type="button" class="button" value="UnSelect All" onclick="selectall(false)" />	
             <input type="submit" name="delete" class="button" value="Delete" onclick="return confirm(\'Are you sure?\')"/>	    
			<input type="button" class="button" value="Add Numbers" 
			  onclick="location.replace(\'addrecipient.php?messageId='.$messageId.'\')" />
			 <input type="button" class="button" value="&laquo; Go To Message" 
			 onclick="location.replace(\'smessage.php?messageId='.$messageId.'\')"/>
		     <input type="hidden" id="list" value="'.preg_replace("/,$/", "", $list).'" />
		  </td>
		  <td width="50" align="right">
		     <a href="xls.msgdelivery.php?messageId='.$messageId.'" target="_blank" title="Export To Excel">
			 <img src="images/excel.jpg" border="0" /></a>
		  </td>		  
		  <td style="cursor: pointer" title="Export To Excel" onclick="window.open(\'xls.msgdelivery.php?messageId='.$messageId.'\')" />(Export To Excel) 
		  </td>
		</tr>
		</table>
		</td>
		</tr>
		</form>';
		 print $html.'</table>'; 
		?>		
		</td>
                    <td>&nbsp;</td>
               </tr>
               <tr>
                  <td height="17">&nbsp;</td>
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

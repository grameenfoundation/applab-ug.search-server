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
validate_session(); 
check_admin_user();

extract($_GET);

if(!isset($start) || !is_numeric($start)) {
  $start = 0;
}

if(isset($_POST['delete'])) {
   foreach($_POST as $key=>$val) {
      if(!preg_match("/^p[0-9]+$/", $key) || !is_numeric($val)) {
	     continue;
	   }	 
	  $sql = "DELETE FROM phoneno WHERE phone='$val' LIMIT 1";
	 execute_nonquery($sql);
   }
   goto("phones.php?start=$start");
} 
if(isset($_GET['sendsms'])) {
   if(preg_match("/^[0-9]+$/", $phone)) {
       session_register('smsphones');
	   $_SESSION['smsphones'] = array($phone);
	   goto("sendsms.php?return=".urlencode("phones.php?start=$start"));
   }
}
if(isset($_POST['sendsms'])) {
   $smsphones = array();
   foreach($_POST as $key=>$val) {
      if(!preg_match("/^p[0-9]+$/", $key) || !is_numeric($val)) {
	     continue;
	   }	 
	  $smsphones[] = $val;
   }
   if(count($smsphones)) {
      session_register('smsphones');
	  $_SESSION['smsphones'] = $smsphones;
	  goto("sendsms.php?return=".urlencode("phones.php?start=$start"));
   }
}   

if(isset($_GET['delete'])) {
   if(!preg_match("/^[0-9]+$/", $phoneId)) {
       goto("phones.php?start=$start");
   }
   $sql = "DELETE FROM phoneno WHERE id=$phoneId LIMIT 1";
   execute_nonquery($sql);
   goto("phones.php?start=$start");
}

$sql = "SELECT phoneno.*, DATE_FORMAT(createDate, '%d/%m/%Y %T') AS created, 
        DATE_FORMAT(updated, '%d/%m/%Y %T') AS updated FROM phoneno";
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
<script type="text/javascript" src="util.js"></script>
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
                    <td height="22" colspan="4" align="center" valign="middle" class="caption">Recieved Phone Numbers - Total:                       <?= $total ?> </td>
             </tr>
               <tr>
                    <td width="17" height="13"></td>
                    <td colspan="2" valign="top"><? require 'messaging.menu.php' ?></td>
                  <td width="17"></td>
               </tr>
               <tr>
                  <td height="28"></td>
                  <td width="40" valign="middle"><img src="images/report.jpg" style="cursor: pointer" title="Unique phone numbers"/></td>
                  <td width="714" valign="middle" class="caption4">&nbsp;Phone Numbers from which SMS Messages came into the System </td>
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
		  <td height="30" valign="top" width="150"><u>Phone Number</u></td> 
		  <td valign="top" width="100"><u>Hits</u></td>
		  <td valign="top"><u>First Recieved</u></td>
		  
		  <td valign="top"><u>Updated</u></td>
		  <td valign="top"><u>Options</u></td>
		 <tr><form method="post">';
		
		$color = '#EEEEEE'; $i=0;
		$list = NULL;
		while($row = mysql_fetch_assoc($result)) {
		 $color = $i++%2 ? '#FFFFFF' : '#EEEEEE';
		 $list .= "p$row[id],";
		 $html .= '
		 <tr bgcolor="'.$color.'" 
		 onmouseover="this.style.backgroundColor=\''.HOVERCOLOR.'\'" onmouseout="this.style.backgroundColor=\''.$color.'\'"> 
		  <td height="25">
		  <input type="checkbox" name="p'.$row['id'].'" id="p'.$row['id'].'" value="'.$row['phone'].'" />
		    <a style="color: #000" href="?start='.$start.'&phone='.$row[phone].'&sendsms" 
			tittle="'.$row['phone'].'">'.$row['phone'].'</a>
		  </td>		  
		  <td class="caption4" style="padding-left: 10px">'.$row['hits'].'</td>
		  <td>'.$row['created'].'</td>
		  <td>'.$row['updated'].'</td>
		  <td>
		  <a href="?start='.$start.'&phone='.$row[phone].'&sendsms">Send SMS</a> | 
		  <a href="?start='.$start.'&phoneId='.$row['id'].'&delete=TRUE" style="color: #FF0000" 
		   title="Delete Quiz" onclick="return confirm(\'Are you sure you want to delete this phone number?\')">Delete</a>
		  </td>
		 <tr>';
		}
	    if($total > $limit) {
	      $scroll = '
	      <tr>
	       <td height="25" colspan="5" valign="bottom">
		    <div style="text-align: justify;">';
	      if($back >= 0) 
          $scroll .= '<a href="?start='.$back.'" style="color: #000000">&laquo; Prev</a> ';
          for($i=0, $l=1; $i < $total; $i= $i + $limit){
           if($i != $start)
		    $scroll .= '<a href="?start='.$i.'">'.$l.'</a> ';
           else $scroll .= '<span style="color: #ff0000; font-weight: bold">'.$l.'</span> ';
           $l = $l+1;
	      }
	      if($this_pg < $total) 
	       $scroll .= ' <a href="?start='.$next.'" style="color: #000000">Next &raquo;</a>';
		  if($l>2) $html = "$html$scroll <div/></td></tr>";		   
	     }	
		$html .='
		<tr>
		  <td colspan="5" height="45" valign="middle">
		   <table border="0">
		    <tr>
			<td>
             <input type="button" class="button" value="Select All" onclick="selectall(true)" />	
             <input type="button" class="button" value="UnSelect All" onclick="selectall(false)" />	
             <input type="submit" name="sendsms" class="button" value="Send Instant SMS" />
			 <input type="submit" name="delete" class="button" value="Delete Selected" 
			 onclick="return confirm(\'Are you sure you want to delete the selected number(s)?\')"/>				  		
		     <input type="hidden" id="list" value="'.preg_replace("/,$/", "", $list).'" />
		     </td>
			 <td width="50" align="right">
			   <a href="xls.phones.php" target="_blank" title="Export Numbers to Excel File"><img src="images/excel.jpg" border="0" /></a>
			  </td>
			  <td><a href="xls.phones.php" style="color: #000" target="_blank" title="Export Numbers to Excel File">Export To Excel</a></td>
			 </tr>
		   </table>
		  </td>
		</tr>
		</form>';
		 print $html.'</table>'; 
		?>		</td>
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

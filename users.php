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

if(isset($_GET['listall'])) {
    if(isset($_SESSION['usearch'])) {
        session_unregister('usearch');
				session_unregister('map');
	}	
	redirect('users.php');
}

extract($_GET);

if(!isset($start) || !is_numeric($start)) 
{
  $start = 0;
}

if(isset($_POST['delete'])) 
{
   if($_POST['allchecked']) 
   {
       $sql = 'DELETE FROM user';
	   if(isset($_SESSION['usearch'])) {
	        $sql .= ' WHERE id IN('.implode(',', $_SESSION['usearch']['matched_list']).')';
	   }
	   execute_update($sql);
	   logaction('Deleted all user User Information!');
	   redirect('users.php');
   }
   else {
       foreach($_POST as $key=>$val) {
           if(!preg_match("/^p[0-9]+$/", $key) || !is_numeric($val)) {
	           continue;
	       }	 
	       $sql = "DELETE FROM user WHERE misdn='$val' LIMIT 1";
	       execute_update($sql);
       }
   }
   redirect("users.php?start=$start");
} 

if(isset($_GET['sendsms'])) 
{
   if(preg_match("/^[0-9]+$/", $phone)) {
       session_register('smsphones');
	   $_SESSION['smsphones'] = array($phone);
	   redirect("sendsms.php?return=".urlencode("users.php?start=$start"));
   }
}

if(isset($_POST['addtolist'])) 
{
   $phonelist = array();
   if($_POST['allchecked']) {
       /* all records */
	   $sql = 'SELECT misdn FROM user';
	   if(isset($_SESSION['usearch'])) {
	        $sql .= ' WHERE id IN('.implode(',', $_SESSION['usearch']['matched_list']).')';
	   }	   
	   $result=execute_query($sql);
	   while($row=mysql_fetch_assoc($result)) {
	       $phonelist[]=$row['misdn'];
	   }
   }
   else {
       foreach($_POST as $key=>$val) {
          if(!preg_match("/^p[0-9]+$/", $key) || !is_numeric($val)) {
	         continue;
	      }	 
	      $phonelist[] = $val;
       }
   }
   if(count($phonelist)) {
      $userId = get_user_id();
	  foreach($phonelist as $phone) {
	       $sql = "INSERT INTO workinglist(createdate, phone, owner) VALUES(NOW(), '$phone', '$userId') ON DUPLICATE KEY UPDATE updated=NOW()";
		   execute_update($sql);
	  }
	  $result = execute_query("SELECT * FROM workinglist WHERE owner='$userId'");
	  $total = mysql_num_rows($result);
	  $_SESSION['working_list_message'] = count($phonelist).' User(s) successfully added to the Working List. There are now '.$total.
	  ' User(s) in your Working List';
	  redirect("?start=$start");
   }
} 
 
if(isset($_POST['sendsms'])) 
{
   $smsphones = array();
   if($_POST['allchecked']) 
   {
       /* all records */
	   $sql = 'SELECT misdn FROM user';
	   if(isset($_SESSION['usearch'])) {
	        $sql .= ' WHERE id IN('.implode(',', $_SESSION['usearch']['matched_list']).')';
	   }	   //die($sql);
	   $result=execute_query($sql);
	   while($row=mysql_fetch_assoc($result)) {
	       $smsphones[]=$row['misdn'];
	   }
   }
   else {
       foreach($_POST as $key=>$val) {
          if(!preg_match("/^p[0-9]+$/", $key) || !is_numeric($val)) {
	         continue;
	      }	 
	      $smsphones[] = $val;
       }
   } 
   if(count($smsphones)) { 
      session_register('smsphones');
	  $_SESSION['smsphones'] = $smsphones;
	  redirect("sendsms.php?return=".urlencode("users.php?start=$start"));
   }
}   

if(isset($_GET['delete'])) 
{
   if(!preg_match("/^[0-9]+$/", $userId)) {
       redirect("?start=$start");
   }
   $sql = "DELETE FROM user WHERE id=$userId LIMIT 1";
   execute_nonquery($sql);
   redirect("?start=$start");
}

if(isset($_SESSION['usearch'])) {
    $sql = $_SESSION['usearch']['sql'];
}
else {
    $sql = "SELECT user.*, DATE_FORMAT(createdate, '%d/%m/%Y %r') AS created, DATE_FORMAT(updated, '%d/%m/%Y %r') AS updated FROM user"; 
}          
		  

$result = execute_query($sql);
$total = mysql_num_rows($result);
$limit = 25;
$this_pg = $start + $limit;
$next = $start + $limit;
$back = $start - $limit;

$_SESSION['map'] = $sql." LIMIT $start, $limit";

save_tmp_users($sql, $start, $limit);

if(!isset($_GET['sort']) || !isset($_GET['order'])) {
    $_GET['sort'] = $sort = 'hits';
	$_GET['order'] = $order = 'desc';
}

$table = "`".md5(get_user_id())."`";
$sql = "SELECT $table.*, DATE_FORMAT(createdate, '%d/%m/%Y %r') AS created FROM $table ORDER BY $sort $order";
$order = isset($order) && preg_match('/asc/', $order) ? 'desc' : 'asc';

$result = execute_query($sql);

/* menu highlight */
$page = 'userphones';

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title><?= TITLE ?></title>
<link rel="stylesheet" type="text/css" href="styles/style.css" />
<script type="text/javascript" src="basic.js"></script>
<script type="text/javascript">
function selectall_(m, _all) {
   if(_all) {
       document.getElementById('allchecked').value = '1';
   }else {
      document.getElementById('allchecked').value = '0';
   }
   selectall(m);
}

</script>
</head>

<body onload="setTopButtons()">
<table width="790" border="0" align="center" cellpadding="0" cellspacing="0" class="main">
     <!--DWLayoutTable-->
     <tr>
          <td width="790" height="124" valign="top"><? include('top.php') ?></td>
     </tr>
     <tr>
          <td height="372" valign="top"><table width="100%" border="0" cellpadding="0" cellspacing="0" class="border">
               <!--DWLayoutTable-->
               <tr>
                    <td height="22" colspan="3" align="center" valign="middle" class="caption">
					Users <?= isset($_SESSION['usearch']) ? '(Search Results)' : '' ?> - Total: <?= $total ?> </td>
                    </tr>
               <tr>
                    <td width="13" height="27">&nbsp;</td>
                    <td width="760" valign="top"><? require 'users.menu.php' ?></td>
                    <td width="15">&nbsp;</td>
               </tr>
               <tr>
                  <td height="300">&nbsp;</td>
                  <td valign="top">
		<? 	
		$html = 
		'<table width="100%" border="0" cellpadding="0" cellspacing="0">	
		 <tr>
		     <td colspan=4 height=50 valign=top>
			     <table border=0>
				    <tr>
					    <td>
						   <a href="xls.users.php" target="_blank" title="Export to MS Excel File">
						      <img src="images/excel.jpg" border="0" />
						   </a>
						</td>
						<td>
						<a href="xls.users.php" style="color:#000;text-decoration:underline" target="_blank" 
						title="Export to MS Excel File">Export This List To MS Excel</a>
						</td>
					</tr>
				 </table>
			 </td>
		 </tr>
		 <tr class="title1"> 
		  <td height="30" valign="top">
		     <a href="?start='.$start.'&sort=names&order='.$order.'" style="text-decoration:underline;color:#000">Names / Phone</a>
		  </td> 
		  <td valign="top">
		     <a href="?start='.$start.'&sort=hits&order='.$order.'" style="text-decoration:underline;color:#000">Total Hits</a>
		  </td>
		  <td valign="top">
		     <a href="?start='.$start.'&sort=createdate&order='.$order.'" style="text-decoration:underline;color:#000">Create Date</a>
		  </td>
		  <td valign="top"><u>Options</u></td>
		 </tr>
		 	<form method="post">
		 <tr >
			 <td colspan="4" valign="top" height="35" >
				<input type="button" class="button" value="Select All" onclick="selectall_(true,false)" />
				'.($total > $limit ? '<input type="button" class="button" value="Select All '.$total.'" onclick="selectall_(true,true)" />' : '').'
				<input type="button" class="button" value="Select None" onclick="document.getElementById(\'allchecked\').value=\'0\' ;selectall(false);" />	
				<input type="submit" name="addtolist" class="button" value="Add to Working List" />
				<input type="submit" name="sendsms" class="button" value="Send Instant SMS" />
				<input type="submit" name="delete" class="button" value="Delete Selected" onclick="return confirm(\'Are you sure you want to delete the selected number(s)?\')"/>
				<input type="button" name="viewLocation" class="button" value="View Location" onclick="window.open(\''.$appurl.'/map.php\')"/>
			 </td>
		 </tr>
		 ';
			
		$color = '#EEEEEE'; $i=0;
		$list = NULL;
		while($row = mysql_fetch_assoc($result)) 
		{
		 
		 $color = $i++%2 ? '#FFFFFF' : '#EEEEEE';
		 $list .= "p$row[id],";
		 
		 $html .= '
		 <tr bgcolor="'.$color.'" 
		 onmouseover="this.style.backgroundColor=\''.HOVERCOLOR.'\'" onmouseout="this.style.backgroundColor=\''.$color.'\'"> 
		  <td height="25">
		  <input type="checkbox" name="p'.$row['id'].'" id="p'.$row['id'].'" value="'.$row['misdn'].'" />
		    <a style="color: #000" href="user.php?userId='.$row['id'].'"
			tittle="'.$row['misdn'].'">'.(strlen($row['names']) ? truncate_str($row['names'], 35).' ['.$row['misdn'].']' : $row['misdn']).'</a>
		  </td>		  
		  <td class="caption4" style="padding-left: 10px">'.$row['hits'].'</td>
		  <td>'.$row['created'].'</td>
		  <td>
		     <a href="?start='.$start.'&phone='.$row['misdn'].'&sendsms">SMS</a> | 
		     <a href="edituser.php?userId='.$row['id'].'">Edit</a> | 
		     <a href="?start='.$start.'&userId='.$row['id'].'&delete=TRUE" style="color: #FF0000" 
		      title="Delete Record" onclick="return confirm(\'Are you sure you want to delete this record?\')">Delete</a>
		  </td>
		 <tr>';
		}
	    if($total > $limit) {
	      $scroll = '
	      <tr>
	       <td height="25" colspan="4" valign="bottom">
		    <div style="text-align: justify;">';
	      if($back >= 0) 
          $scroll .= '<a href="?start='.$back.'&sort='.$_GET['sort'].'&order='.$_GET['order'].'" style="color:#000">&laquo; Prev</a> ';
          for($i=0, $l=1; $i < $total; $i= $i + $limit){
           if($i != $start)
		    $scroll .= '<a href="?start='.$i.'&sort='.$_GET['sort'].'&order='.$_GET['order'].'">'.$l.'</a> ';
           else $scroll .= '<span style="color:#ff0000; font-weight:bold">'.$l.'</span> ';
           $l = $l+1;
	      }
	      if($this_pg < $total) 
	       $scroll .= ' <a href="?start='.$next.'&sort='.$_GET['sort'].'&order='.$_GET['order'].'" style="color:#000">Next &raquo;</a>';
		  if($l>2) $html = "$html$scroll <div/></td></tr>";		   
	     }	
		$buttons = '';
		$html .= '
		<tr>
		  <td colspan="4" height="45" valign="middle">
						<input type="button" class="button" value="Select All" onclick="selectall_(true,false)" />
					'.($total > $limit ? '<input type="button" class="button" value="Select All '.$total.'" onclick="selectall_(true,true)" />' : '').'
						<input type="button" class="button" value="Select None" onclick="document.getElementById(\'allchecked\').value=\'0\' ;selectall(false);" />	
						<input type="submit" name="addtolist" class="button" value="Add to Working List" />
			      <input type="submit" name="sendsms" class="button" value="Send Instant SMS" />
			      <input type="submit" name="delete" class="button" value="Delete Selected" onclick="return confirm(\'Are you sure you want to delete the selected number(s)?\')"/>
						<input type="button" name="viewLocation" class="button" value="View Location" onclick="window.open(\''.$appurl.'/map.php\')"/>
            <input type="hidden" id="list" value="'.preg_replace("/,$/", "", $list).'" />
						<input type=hidden name="allchecked" id="allchecked" value="0" />
		  </td>
		</tr>
		</form>';
		 print $html.'</table>'; 
		?>

		</td>
                    <td>&nbsp;</td>
               </tr>
               <tr>
                  <td height="21">&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
               </tr>
               
          </table></td>
     </tr>
     
     <tr>
          <td height="30" valign="top"><? include('bottom.php') ?></td>
     </tr>
</table>
<? 
if (isset($_SESSION['working_list_message'])) {
     $js = 'alert("'.$_SESSION['working_list_message'].'")';
	 print '<script type="text/javascript">'.$js.'</script>';
	 session_unregister('working_list_message');
}
?>
</body>
</html>

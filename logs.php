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

$sql = "SELECT DATE_FORMAT(time, '%d/%m/%Y %r') AS date, logs.* FROM logs";
if(isset($_POST['show'])) {
      extract($_POST);
      $from = sqldate($from);
      $to = sqldate($to);
}
if(!isset($from)) {
   $from = $to = date('Y-m-d');
}   
$sql .= " WHERE time BETWEEN '$from 00:00:00' AND '$to 23:59:59'";
if(strlen($pattern)) {
    $sql .= " AND action LIKE '%$pattern%'";
}
$sql .= " ORDER BY time DESC";
$result = execute_query($sql);
$total = mysql_num_rows($result);

$from = displaydate($from);
$to = displaydate($to);  

/* menu highlight */
$page = 'audit';
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
                    <td height="22" colspan="3" align="center" valign="middle" class="caption">Audit Trial - Total: <?= $total ?></td>
                    </tr>
			  <form method="post">
               <tr>
                    <td width="13" height="50">&nbsp;</td>
                    <td width="760" valign="middle">
							 	From: &nbsp;
							 	<input name="from" type="text" size="20" class="input" value="<?= (!strlen($from) ? date('d/m/Y') : $from) ?>" readonly="readonly" onclick="displayDatePicker('from')" style="cursor: pointer" title="Select Date" />&nbsp;
			To: &nbsp;   
			   <input name="to" type="text" size="20" class="input" value="<?= (!strlen($to) ? date('d/m/Y') : $to) ?>" readonly="readonly" 
			   onclick="displayDatePicker('to')" style="cursor: pointer" title="Select Date" />	
			   &nbsp;&nbsp;Action: &nbsp;&nbsp;<input name="pattern" type="text" size="30" class="input" value="<?= $pattern ?>"/>
			   <input name="show" value="Show Logs" type="submit" class="button" />
					</td>
                    <td width="15">&nbsp;</td>
               </tr>
			   </form>
               <tr>
                  <td height="350">&nbsp;</td>
                  <td valign="top">
		<? 	
		$html = 
		'<table width="100%" border="0" cellpadding="0" cellspacing="0">
		 <tr id="title"> 
		  <td height="30">Date/Time</td>
		  <td>Action</td>
		  <td>User</td>
		 <tr>';
    $color = '#E4E4E4'; $i=0;
	while($row=mysql_fetch_assoc($result)) {   
	  $color = $i++%2 ? '#FFFFFF' : '#EEEEEE';
	  $id = md5($i);
	 $text = "<u>On $row[date]</u>:<br/><br/><strong>$row[username]</strong><br/><br/>".preg_replace("/\n+/", "<br/>", $row['action']); 
	 $text = preg_replace("/\"/", "'", $text);
	 $text = addslashes($text);
     $html .='
     <tr bgcolor="'.$color.'" 
		 onmouseover="this.style.backgroundColor=\''.HOVERCOLOR.'\'" onmouseout="this.style.backgroundColor=\''.$color.'\'"> 
		<td height="27">&nbsp;&nbsp;'.$row['date'].'</td>   
		<td style="cursor: pointer" onmouseover="s(\''.$id.'\', \''.$text.'\')" onmouseout="h(\''.$id.'\')">
		    <div id="'.$id.'" style="width: 0px"></div>'.truncate_str($row['action'], 50).'
		</td>
        <td style="color: #008800">'.$row['username'].'</td>
	 </tr>';
     }
     print $html.'</table>'; 
   ?>		
   </td>
                    <td>&nbsp;</td>
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

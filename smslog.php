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

if(!count($_GET)) {
   $d1 = $d2 = date('d');
   $m1 = $m2 = date('m');
   $y1 = $y2 = date('Y');
   $hr1 = 00;
   $hr2 = 23;
}
if(!preg_match("/^OUTGOING$/", $direction) && !preg_match("/^INCOMING$/", $direction)) {
    $direction = 'INCOMING';
}

$sql = "SELECT smslog.*, DATE_FORMAT(date, '%d/%m/%Y %r') AS time FROM smslog 
        WHERE date BETWEEN '$y1-$m1-$d1 $hr1:00:00' AND '$y2-$m2-$d2 $hr2:59:59' AND direction='$direction'";
if(isset($processed) && (preg_match("/^0$/", $processed) || preg_match("/^1$/", $processed))) {
    $sql .= " AND processed=$processed";
}

if($phone_no && preg_match("/^INCOMING$/", $direction)){
       $phone =  get_int_phone_format($phone_no);
    $sql .= " AND sender = $phone";
}

if($phone_no && preg_match("/^OUTGOING$/", $direction)){
       $phone =  get_int_phone_format($phone_no);
    $sql .= " AND recipient = $phone";
}

$sql .= " ORDER BY date DESC"; 

$_SESSION['smslogq'] = array('sql'=>$sql, 'direction'=>$direction, 'title'=> $direction." Messages For the Period $d1/$m1/$y1 $hr1:00 HRS - $d2/$m2/$y2 $hr2:00 HRS");

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
          <td height="362" valign="top"><table width="100%" border="0" cellpadding="0" cellspacing="0" class="border">
               <!--DWLayoutTable-->
               <tr>
                    <td height="22" colspan="4" align="center" valign="middle" class="caption"><?= $direction ?> SMS Messages - Total:                       <?= $total ?> </td>
             </tr>
               <tr>
                    <td height="18" colspan="4" valign="middle"><? require 'messaging.menu.php' ?></td>
               </tr>
			   <form>
               <tr>
                    <td width="14" height="33">&nbsp;</td>
                  	<td width="57" valign="middle">Period:</td>
                  	<td width="700" valign="middle">
								
											<select name="m1" class="input" id="m1">
														<?= months($m1) ?>
																								</select>
											<select name="d1" class="input" id="d1">
														<?= days($d1) ?>
																								</select>
											<select name="y1" class="input" id="y1">
														<?= years($y1) ?>
																								</select>
											<select name="hr1" class="input" id="hr1">
														<?= hours($hr1) ?>
																								</select>
											&nbsp;&nbsp;To:&nbsp;&nbsp;
											<select name="m2" class="input" id="m2">
														<?= months($m2) ?>
														</select>
											<select name="d2" class="input" id="d2">
														<?= days($d2) ?>
														</select>
											<select name="y2" class="input" id="y2">
														<?= years($y2) ?>
														</select>
											<select name="hr2" class="input" id="hr2">
														<?= hours($hr2) ?>
														</select>
								</td>
                  	<td width="17">&nbsp;</td>
               </tr>
               <tr>
               			<td height="36"></td>
               			<td valign="middle">Phone:</td>
               			<td valign="middle">
						<input name="phone_no" type="text" class="input" id="phone_no" <? if($phone_no) echo "value = $phone_no" ?> />
						<select name="direction" class="input" id="direction" onchange="if(this.selectedIndex==0) this.form.processed.disabled=true; else this.form.processed.disabled=false;">
												<option value="OUTGOING"<?= $direction=='OUTGOING' ? ' selected="selected"' : '' ?>>OUTGOING SMS</option>
												<option value="INCOMING"<?= $direction=='INCOMING' ? ' selected="selected"' : '' ?>>INCOMING SMS</option>
												</select>
									<select name="processed" class="input" id="processed"<?= $direction == 'OUTGOING' ? ' disabled="disabled"' : '' ?>>
												<option value="ALL">ALL SMS</option>
												<option value="1"<?= preg_match("/^1$/", $processed) ? ' selected="selected"' : '' ?>>PROCESSED</option>
												<option value="0"<?= preg_match("/^0$/", $processed) ? ' selected="selected"' : '' ?>>UNPROCESSED</option>
																					</select>
               						<input name="submit" type="submit" class="button" id="submit" value="Show SMS" />
									<input name="submit2" type="button"<?= $total==0 ? ' disabled="disabled"' : '' ?> class="button" id="submit2" onclick="location.replace('xls.sms.php')" value="Export To CSV"/></td>
               			<td></td>
               			</tr>
						</form>
               <tr>
               			<td height="27"></td>
               			<td>&nbsp;</td>
               			<td></td>
               			<td></td>
               			</tr>
               <tr>
               			<td height="238"></td>
               			<td colspan="2" valign="top">
	   <? 	
		$html = 
		'<table width="100%" border="0" cellpadding="0" cellspacing="0">
		 <tr class="title1"> 
		  <td height="30" valign="top" colspan="2"><u>'.($direction == 'OUTGOING' ? 'To' : 'From').' Number</u></td>		  
		  <td valign="top"><u>Message</u></td>
		  <td valign="top"><u>Date</u></td>
		 <tr>';
		$color = '#E4E4E4'; $i=0;
		while($row = mysql_fetch_assoc($result)) { 
		 $color = $i++%2 ? '#FFFFFF' : '#EEEEEE';		
		 $html .= '
		 <tr bgcolor="'.$color.'" 
		 onmouseover="this.style.backgroundColor=\''.HOVERCOLOR.'\'" onmouseout="this.style.backgroundColor=\''.$color.'\'"> 
		  <td width="30" height="30" style="padding-left: 3px"><img src="images/msg.gif" border="0" /></td>
		  <td>'.($direction=='INCOMING' ? get_phone_display_label( preg_replace("/\+/", "", $row['sender'])) : get_phone_display_label($row['recipient'])).'</td> 
		  <td width="280" style="padding: 6px 10px 6px 0px; color: '.(!$row['processed'] && $direction=='INCOMING' ? '#CC0000' : '#008800').'; cursor: pointer" title="'.$row['message'].'">'.
		  truncate_str($row['message'], 150).'</td>
		  <td>'.$row['time'].'</td>	
		 <tr>';
		 }
		 print $html.'</table>'; 
		?>		</td>
        <td></td>
               			</tr>
               <tr>
               			<td height="20"></td>
               			<td>&nbsp;</td>
               			<td></td>
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

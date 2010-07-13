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

if(isset($cancel)) {
    goto("user.php?userId=$userId");
}

check_id($userId, 'users.php');
$user = get_table_record('user', $userId);

if(empty($user)) {
   goto('users.php');
}
if(!isset($t)) {
   $t = 'quizreply';
}

switch($t) {
    case 'quizreply': 
	      $phonecol = 'phone';
		  $timecol = 'time'; 
		  $textcol = 'reply';
    break;
    case 'sresult': 
	      $phonecol = 'phone';
		  $timecol = 'date';
		  $textcol = 'request'; 
    break;	
    case 'mresult': 
	      $phonecol = 'phoneId';
		  $timecol = 'date'; 
		  $textcol = 'form';
    break;	
    case 'hit': 
	      $phonecol = 'phone';
		  $timecol = 'time';
		  $textcol = 'request'; 
    break;
		case 'oldFormSurveys':
			$phonecol = 'msisdn';
			$timecol = 'logdate';
			$textcol = 'otherInfo';
		break;
	
}

if(!in_array($t, array('quizreply', 'sresult', 'mresult', 'hit', 'oldFormSurveys'))) {
    goto('users.php');
}

if(!isset($start) || !is_numeric($start)) 
{
  $start = 0;
}


$_phone = preg_replace("/^0/", "", $user['misdn']);
$sql = "SELECT $t.*, DATE_FORMAT($timecol, '%d/%m/%Y %r') AS hittime FROM $t WHERE $phonecol='$user[misdn]' 
        OR LOCATE(SUBSTRING($phonecol, 2), '$user[misdn]') OR LOCATE('$_phone', $phonecol)>0";
		
if(strlen($user['phones'])) {
    $sql .= " OR FIND_IN_SET($phonecol, '$user[phones]')";
}
$result = execute_query($sql); //print $sql;

$total = mysql_num_rows($result);

$limit = 20;
$this_pg = $start + $limit;
$next = $start + $limit;
$back = $start - $limit;

/* listing */
$sql .= " ORDER BY $timecol DESC LIMIT $start, $limit";
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
                    <td height="22" colspan="4" align="center" valign="middle" class="caption">User Activity (Total: <?= $total ?>) - <?= $user['names'] ? $user['names'].' ('.$user['misdn'].')' : $user['misdn'] ?></td>
             </tr>
               <tr>
                    <td width="26" height="30">&nbsp;</td>
               		<td colspan="2" valign="middle"><? require 'users.menu.php' ?></td>
               <td width="33">&nbsp;</td>
               </tr>
               <tr>
               			<td height="57">&nbsp;</td>
               			<td width="125" align="right" valign="middle">&raquo; APPLICATION:&nbsp;&nbsp;</td>
               			<td width="604" valign="middle">
									<form>
												<input type="hidden" name="userId" value="<?= $userId ?>" />
												<select name="t" class="input" id="t" style="width: 200px" onchange="this.form.submit()">
															<option value="quizreply" <?= $t == 'quizreply' ? 'selected="selected"' : '' ?>>QUIZ</option>
															<option value="sresult" <?= $t == 'sresult' ? 'selected="selected"' : '' ?>>CODED SURVEY</option>
															<option value="mresult" <?= $t == 'mresult' ? 'selected="selected"' : '' ?>>ENHANCED SURVEY</option>
															<option value="hit" <?= $t == 'hit' ? 'selected="selected"' : '' ?>>KEYWORDS</option>
															<option value="oldFormSurveys" <?= $t == 'oldFormSurveys' ? 'selected="selected"' : '' ?>>FORM SURVEYS</option>
															</select>
												<input name="cancel" type="submit" class="button" id="cancel" value="&laquo; Go To User" />
												</form></td>
               			<td>&nbsp;</td>
               			</tr>
               
               <tr>
               			<td height="20"></td>
               			<td>&nbsp;</td>
        				<td>&nbsp;</td>
        				<td>&nbsp;</td>
        				</tr>
               <tr>
               			<td height="245"></td>
               			<td colspan="2" valign="top">
	    							<? 	
		$html = 
		'<table width="100%" border="0" cellpadding="0" cellspacing="0">
		 <tr class="title1"> 
		  	
		  <td height="30" valign="top"><u>Date/Time</u></td>
		  <td valign="top"><u>Received Message</u></td>	
		 <tr>';
		 
		$color = '#E4E4E4'; $i=0;
		while($row = mysql_fetch_assoc($result)) 
		{ 
		    $color = $i++%2 ? '#FFFFFF' : '#EEEEEE';		
		    $text = ($t != 'mresult') ? truncate_str($row[$textcol], 100) : 'FORM DATA';
			$html .= '
		    <tr bgcolor="'.$color.'" 
		     onmouseover="this.style.backgroundColor=\''.HOVERCOLOR.'\'" onmouseout="this.style.backgroundColor=\''.$color.'\'"> 
		     <td height="30">&nbsp;'.$row['hittime'].'</td>	
			 <td class="field"  style="cursor: pointer" title="'.$text.'">'.$text.'</td>
		 <tr>';
		 }
		 if($total > $limit) 
		 {
	      $scroll = '
	      <tr>
	       <td height="25" colspan="2" valign="bottom">
		    <div style="text-align: justify;">';
	      if($back >= 0) 
          $scroll .= '<a href="?userId='.$userId.'&t='.$t.'&start='.$back.'" style="color: #000000">&laquo; Prev</a> ';
          for($i=0, $l=1; $i < $total; $i= $i + $limit){
           if($i != $start)
		    $scroll .= '<a href="?userId='.$userId.'&t='.$t.'&start='.$i.'">'.$l.'</a> ';
           else $scroll .= '<span style="color: #ff0000; font-weight: bold">'.$l.'</span> ';
           $l = $l+1;
	      }
	      if($this_pg < $total) 
	       $scroll .= ' <a href="?userId='.$userId.'&t='.$t.'&start='.$next.'" style="color: #000000">Next &raquo;</a>';
		  if($l>2) $html = "$html$scroll <div/></td></tr>";		   
	     }	
		 print $html.'</table>'; 
		?>		
		</td>
        <td>&nbsp;</td>
               			</tr>
               <tr>
               			<td height="20"></td>
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

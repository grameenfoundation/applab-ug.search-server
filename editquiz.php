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

if(isset($_POST["cancel"])) {
 goto("questions.php?quizId=$quizId");
}
$quiz = get_quiz_from_id($quizId);
if(empty($quiz)) {
   goto('quiz.php');
}

if(count($_POST)) {
  $_POST = strip_form_data($_POST);
  extract($_POST);
}  

if(isset($_POST["submit"])) {
   if(strlen($_POST['name']) < 3) {
     $errors = 'Quiz name not valid<br/>';
   }
   $phone_numbers = read_phone_numbers(); 
   if($phone_numbers == 0) {
      $errors .= 'No valid phone numbers found in uploaded file<br/>';
   } 
   if($sendall) {
		  /* check date */
		  if(!strlen($_POST['date'])) {
		     $errors .= "Date for sending Questions not valid<br/>";
		  }	 
		  /* check minute */
		  if(!preg_match("/^[0-9]{1,2}$/", $_POST['min']) || $_POST['min']>59) {
		      $errors .= "Time for sending Questions not valid<br/>";
		  }	
		  if(!isset($errors)) {
		  /* check that send time is not in the past */
	      $date = preg_split("/\//", $_POST['date']);
	      $d = array_shift($date);
	      $m = array_shift($date);
	      $y = array_shift($date);
	      $hr = $_POST['hr']; 
	      $min = $_POST['min'];
	      if(mktime($hr, $min, 0, $m, $d, $y) < time()) {
	         $errors .= "The time for sending Questions is in the past<br/>";
	      }	
		  else {
	          $sendTime = "$y-$m-$d $hr:$min:00";  
		  }
		  }
		  //    
   }   
   if(!isset($errors)) {
     $qname = mysql_real_escape_string($_POST['name']);
	 $reply = rtrim($reply);
	 if(strlen($reply) > 160) {
	    $reply = substr($reply, 0, 160);
	 }	
	 $reply = mysql_real_escape_string($reply);
     $sql = "UPDATE quiz SET name='$name', sendall=$sendall, reply=IF(LENGTH('$reply'), '$reply', NULL) WHERE id=$quizId";
     execute_nonquery($sql);
	 if($sendall) {
	     execute_update("UPDATE question SET sendTime='$sendTime' WHERE quizId=$quizId");
	 }
	 /* the phone numbers */	 
	 foreach($phone_numbers as $phone) {
	    if(!preg_match("/^[0-9]{8,}$/", $phone)) {
		   continue;
		} 	 
	    $sql = "INSERT INTO quizphoneno(quizId, createDate, phone) VALUES ($quizId, NOW(), '$phone')";
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
	 logaction("Modified quiz: $qname");
	 goto("questions.php?quizId=$quizId");
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
if($quiz['sendall']) {
   /* get send time */
   $result = execute_query("SELECT DATE_FORMAT(sendTime, '%d/%m/%Y') AS date, DATE_FORMAT(sendTime, '%H') AS hr, 
   DATE_FORMAT(sendTime, '%i') AS min FROM question WHERE quizId=$quizId LIMIT 1");
   if(mysql_num_rows($result)) {
      $row = mysql_fetch_assoc($result);
	  extract($row);
   }
}
extract($_POST);

if(isset($errors)) {
  $errors = "<br/>$errors<br/>";
}
/* menu highlight */
$page = 'quiz';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title><?= TITLE ?></title>
<link rel="stylesheet" type="text/css" href="styles/style.css" />
<link rel="stylesheet" type="text/css" href="styles/date.css" />
<script type="text/javascript" src="basic.js"></script>
<script type="text/javascript" src="date.js"></script>
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
		                 <td height="22" colspan="3" align="center" valign="middle" class="caption">Edit quiz </td>
                    </tr>
		            <tr>
		                 <td width="15" height="48">&nbsp;</td>
                         <td width="747">&nbsp;</td>
                         <td width="26">&nbsp;</td>
                    </tr>
		            <tr>
		                 <td height="260">&nbsp;</td>
                         <td valign="top">
                              <fieldset>
                              <legend>Quiz Details</legend>
					          <table width="745" border="0" cellpadding="0" cellspacing="0">
					               <!--DWLayoutTable-->
					               <tr>
					                    <td width="29" height="23">&nbsp;</td>
                        <td width="58">&nbsp;</td>
                        <td width="46">&nbsp;</td>
                        <td colspan="9" valign="top" class="error">
                           <? if(isset($errors)) echo $errors; ?>					   </td>
                        <td width="16">&nbsp;</td>
					               </tr>
					     <form method="post" enctype="multipart/form-data">
					     <tr>
					     <td height="28" colspan="3" align="right" valign="middle">Name:&nbsp;&nbsp;</td>
                         <td colspan="9" valign="middle">
						    <input name="name" type="text" class="input" id="name" value="<?= $quiz['name'] ?>" size="55" /></td>
                         <td>&nbsp;</td>
		                    </tr>
					     <tr>
					     <td height="70" colspan="3" align="right" valign="top"><br />
					        SMS Reply:&nbsp;&nbsp;</td>
                         <td colspan="9" valign="middle">
						    <textarea name="reply" cols="55" class="input" id="reply" style="width: 360px; height: 65px"><?= $quiz['reply'] ?></textarea></td>
                         <td>&nbsp;</td>
		                    </tr>							
					                    <tr>
					                         <td height="50">&nbsp;</td>
                                             <td>&nbsp;</td>
                                             <td colspan="10" valign="middle" class="caption2"><u>Add Phone Numbers to this quiz</u></td>
                         <td>&nbsp;</td>
					                    </tr>	
					                    <tr>
					                         <td height="44">&nbsp;</td>
                                             <td colspan="3" align="right" valign="middle">Browse File:&nbsp;&nbsp;</td>
                                           <td colspan="2" valign="middle"><img src="images/excel.jpg" width="30" height="30" style="cursor: pointer" title="Input phone numbers from file"/><br /></td>
                                           <td colspan="2" valign="middle" nowrap="nowrap">
										   &nbsp;
										   <input type="file" name="file" size="30" style="font-size: 11px"/></td>
                         <td colspan="4" valign="middle">&nbsp;<span style="color: #FF0000">*</span>Excel or (CSV - One No. per line) </td>
                                           <td>&nbsp;</td>
					                    </tr>
					                    <tr>
					                       <td height="60"></td>
					                       <td>&nbsp;</td>
					                       <td>&nbsp;</td>
					                       <td width="28">&nbsp;</td>
					                       <td colspan="3" valign="middle"><img src="images/group.jpg" width="45" height="45" style="cursor: pointer" 
										   title="Click to Import Numbers" onclick="importn('quiz')"/></td>
					                       <td colspan="2" valign="middle" id="qlabel"><?= $qlabel ?></td>
					                       <td width="38" valign="middle"><img src="images/import.gif" width="38" height="46" style="cursor: pointer" title="Click to import numbers from the general list" onclick="importn('general')"/></td>
					                       <td colspan="2" valign="middle" id="glabel"><?= $glabel ?></td>
					                       <td></td>
	                        </tr>
					                    
					                    <tr>
					                       <td height="120"></td>
					                       <td colspan="4" align="right" valign="top"><br />
   Input Numbers:&nbsp;&nbsp; </td>
					                       <td colspan="6" valign="middle">
										      <textarea name="numbers" style="width: 320px; height: 100px"  
										   class="input" id="numbers"><?= $_POST['numbers'] ?></textarea>										      									       </td>
					                       <td width="120">&nbsp;</td>
					                       <td></td>
	                        </tr>
					                    <tr>
					                       <td height="14"></td>
					                       <td></td>
					                       <td></td>
					                       <td></td>
					                       <td width="19"></td>
					                       <td colspan="6" valign="top" style="font-size: 10px">[Separate phone numbers with commas] </td>
					                       <td></td>
					                       <td></td>
	                        </tr>
					                    <tr>
					                       <td height="40"></td>
					                       <td colspan="4" align="right" valign="middle">Question Sending:&nbsp;&nbsp;</td>
					                       <td colspan="6" valign="middle">
										   <input name="sendall" id="sendall" type="radio" value="0" <?= !$sendall ? 'checked="checked"' : '' ?> onclick="tsend(false)"/>
Send at different times&nbsp;&nbsp;
<input name="sendall" id="sendall" type="radio" value="1" <?= $quiz['sendall'] || $_POST['sendall'] ? 'checked="checked"' : '' ?> onclick="tsend(true)"/>
<span style="color: #0066FF">Send All at same time</span></td>
					                       <td></td>
					                       <td></td>
	                        </tr>		
					                    <tr <?= !$quiz['sendall'] || !$_POST['sendall'] ? 'style="display: none"' : '' ?> id="qs">
					                       <td height="40"></td>
					                       <td colspan="4" align="right" valign="middle">Send Time:&nbsp;&nbsp;</td>
					                       <td colspan="6" valign="middle"><input name="date" type="text" class="input" id="date" style="cursor: pointer" title="Select Date" onclick="displayDatePicker('date')" value="<?= $date ?>" size="15" readonly="true"/>
													<select name="hr" class="input" id="hr">
																<?= hours($hr) ?>
													</select>
													<input name="min" type="text" class="input" id="min"
													 value="<?= $min ? $min : '00' ?>" size="4" />
HRS</td>
					                       <td></td>
					                       <td></td>
	                        </tr>														
					                    
					                    
					                    
					                    

					                    <tr>
					                         <td height="35">&nbsp;</td>
                         <td>&nbsp;</td>
                         <td>&nbsp;</td>
                         <td>&nbsp;</td>
                         <td>&nbsp;</td>
                         <td colspan="7" valign="middle"><input name="submit" type="submit" class="button" id="submit" value="Update Quiz" />
                            		<input name="cancel" type="submit" class="button" id="cancel" value="Cancel" />
                            		<input name="qimportlist" type="hidden" id="qimportlist" value="<?= $qimportlist ?>" />
                            		<input name="gimportlist" type="hidden" id="gimportlist" value="<?= $gimportlist ?>" /></td>
                         <td>&nbsp;</td>
					                    </tr>
					                    <tr>
					                    			<td height="18">&nbsp;</td>
					                    			<td>&nbsp;</td>
					                    			<td>&nbsp;</td>
					                    			<td>&nbsp;</td>
					                    			<td>&nbsp;</td>
					                    			<td width="11">&nbsp;</td>
					                    			<td width="15">&nbsp;</td>
					                    			<td width="213">&nbsp;</td>
					                    			<td width="24">&nbsp;</td>
					                    			<td>&nbsp;</td>
					                    			<td width="128">&nbsp;</td>
					                    			<td>&nbsp;</td>
					                    			<td>&nbsp;</td>
		                    			</tr>
			                     </form>
			                  </table>
                         </fieldset></td>
                         <td>&nbsp;</td>
                    </tr>
		            <tr>
		                 <td height="32"></td>
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
<?
  if($quiz['sendall'] || $_POST['sendall']) { print '<script>tsend(true)</script>'; } 
?>
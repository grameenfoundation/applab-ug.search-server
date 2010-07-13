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

if(isset($_POST["cancel"])) {
   goto("recipients.php?messageId=$messageId");
}

if(count($_POST)) {
  $_POST = strip_form_data($_POST);
  extract($_POST);
}   

if(isset($_POST["submit"])) {	   
   $phone_numbers = read_phone_numbers(); 
   if($phone_numbers == 0) {
      $errors .= 'No valid phone numbers found in uploaded file<br/>';
   } 
   
   if(!isset($errors)) { 
	 foreach($phone_numbers as $phone) {
	    if(!preg_match("/^[0-9]{8,}$/", $phone)) {
		   continue;
		} 	 
	    $sql = "INSERT INTO recipient (messageId, createDate, phone) VALUES ($messageId, NOW(), '$phone')";
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
	 logaction("Scheduled message: $mname");
	 clear_working_list();
	 goto("recipients.php?messageId=$messageId");
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

if(strlen($wimportlist)) {
   $wimportlist = trim($wimportlist);//print $wimportlist;
   $wimportlist = preg_replace("/,$/", "", $wimportlist);
   $list = preg_split("/,/", $wimportlist);
   if(count($list)) {
      $wlabel = '&nbsp;&nbsp;<span style="color: #008800">Added ('.count($list).') Number(s)</span>
	  <span style="cursor: pointer;color: #FF0000" onclick="removelabel(\'wl\',true)" title="Click to remove numbers">[delete]</span>';
   }
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
<script type="text/javascript" src="basic.js"></script>
</head>

<body class="main">
<table width="790" border="0" align="center" cellpadding="0" cellspacing="0">
     <!--DWLayoutTable-->
     <tr>
          <td width="790" height="124" valign="top"><? include('top.php') ?></td>
     </tr>
     <tr>
          <td valign="top">
		       <table width="100%" border="0" cellpadding="0" cellspacing="0" class="border">
		            <!--DWLayoutTable-->
		            <tr>
		                 <td height="22" colspan="5" align="center" valign="middle" class="caption">Add Recipients To Message </td>
                    </tr>
		            <tr>
		                 <td width="46" height="15"></td>
                         <td width="25"></td>
                         <td width="657"></td>
                         <td width="30"></td>
                         <td width="30"></td>
		            </tr>
		            <tr>
		               <td height="28"></td>
		               <td valign="middle"><img src="images/msg.gif" border="0" /></td>
		               <td valign="middle">&nbsp; <span class="caption2">Message:</span>
                          <?= $message['name'] ?>
- <span style="color: #666666">created
<?= $message['createDate'] ?>
</span></td>
		               <td>&nbsp;</td>
		               <td>&nbsp;</td>
	              </tr>
		            <tr>
		               <td height="20"></td>
		               <td>&nbsp;</td>
		               <td>&nbsp;</td>
		               <td>&nbsp;</td>
		               <td></td>
	              </tr>
		            <tr>
		               <td height="314"></td>
		               <td colspan="3" valign="top">
                            <fieldset>
                            <legend>Add Phone Numbers</legend>
				            <table width="709" border="0" cellpadding="0" cellspacing="0">
				                 <!--DWLayoutTable-->
				                 <tr>
			                        <td width="132" height="21">&nbsp;</td>
                        <td colspan="7" valign="middle" class="error"><?= $errors ?></td>
                        <td width="81">&nbsp;</td>
                        <td width="9">&nbsp;</td>
			                   </tr>
					     <form method="post" enctype="multipart/form-data">
					     
					                    	
					                    <tr>
					                         <td height="44" align="right" valign="middle">Browse File:&nbsp;&nbsp;</td>
                                           <td colspan="2" valign="middle"><img src="images/excel.jpg" width="30" height="30" style="cursor: pointer" title="Input phone numbers from file"/><br /></td>
                                           <td colspan="2" valign="middle" nowrap="nowrap">
										   &nbsp;<input type="file" name="file" size="30" style="font-size: 11px"/></td>
                         <td colspan="3" valign="middle">&nbsp;<span style="color: #FF0000">*</span>Excel or (CSV - One No. per line) </td>
                                           <td>&nbsp;</td>
                                           <td>&nbsp;</td>
					                    </tr>
					                    <tr>
					                       <td height="60">&nbsp;</td>
					                       <td colspan="3" valign="middle"><img src="images/group.jpg" width="45" height="45" style="cursor: pointer" 
										   title="Click to Import Numbers" onclick="importn('quiz')"/></td>
					                       <td colspan="2" valign="middle" id="qlabel"><?= $qlabel ?></td>
					                       <td width="38" valign="middle"><img src="images/import.gif" width="38" height="46" style="cursor: pointer" title="Click to import numbers from the general list" onclick="importn('general')"/></td>
					                       <td colspan="2" valign="middle" id="glabel"><?= $glabel ?></td>
	                        <td>&nbsp;</td>
		                    </tr>
<tr>
					                       <td height="40" colspan="2" align="right" valign="top"></td>
					                       <td colspan="6" valign="middle"><a href="#" title="Get Contacts from Group(s) and Working List" style="color: #FF3300; text-decoration: underline" onclick="importn('worklist');return false;">[Send To Group] </a> <span id="wlabel">
					                       			<?= $wlabel ?>
					                       </span></td>
					                       <td>&nbsp;</td>
                                           <td>&nbsp;</td>
		                    </tr>					                    
					                    <tr>
					                       <td height="71" colspan="2" align="right" valign="top"><br />
   Input Numbers:&nbsp;&nbsp; </td>
					                       <td colspan="6" valign="middle">
									       <textarea name="numbers" style="width: 250px; height: 50px"  
										   class="input" id="numbers"><?= $_POST['numbers'] ?></textarea>   									       </td>
					                       <td>&nbsp;</td>
                                           <td>&nbsp;</td>
		                    </tr>
					                    <tr>
					                       <td height="14"></td>
					                       <td width="19"></td>
					                       <td colspan="6" valign="top">(Separate with commas) </td>
					                       <td></td>
                                           <td></td>
		                    </tr>
					                    
					                    
					                    
					                    

					                    <tr>
				                           <td height="63">&nbsp;</td>
                         <td>&nbsp;</td>
                         <td colspan="6" valign="middle"><input name="submit" type="submit" class="button" id="submit" value="Submit Number(s)" />
                             <input name="cancel" type="submit" class="button" id="cancel" value="Cancel" />
                             <input name="qimportlist" type="hidden" id="qimportlist" value="<?= $qimportlist ?>" />
                             <input name="gimportlist" type="hidden" id="gimportlist" value="<?= $gimportlist ?>" />
                             <input name="wimportlist" type="hidden" id="wimportlist" value="<?= $wimportlist ?>" /></td>
                         <td>&nbsp;</td>
					                    <td>&nbsp;</td>
					                    </tr>
					                    <tr>
					                       <td height="26">&nbsp;</td>
					                       <td>&nbsp;</td>
					                       <td width="11">&nbsp;</td>
					                       <td width="15">&nbsp;</td>
					                       <td width="211">&nbsp;</td>
					                       <td width="26">&nbsp;</td>
					                       <td>&nbsp;</td>
					                       <td width="167">&nbsp;</td>
					                       <td>&nbsp;</td>
                                           <td>&nbsp;</td>
		                    </tr>
					                    
					                    
					                    
					                    
					                    
					                    
					     <? for($i=1; $i<=$qns; $i++) { ?>
					                    	
					                    <? } ?>														
			                     </form>
		                    </table>
                            </fieldset></td>
                         <td>&nbsp;</td>
                  </tr>
		            <tr>
		               <td height="32"></td>
		               <td>&nbsp;</td>
		               <td>&nbsp;</td>
		               <td>&nbsp;</td>
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

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
 goto("survey.php?surveyId=$surveyId");
}
$sql = "SELECT survey.*, DATE_FORMAT(sendtime, '%d/%m/%Y') AS date, DATE_FORMAT(sendtime, '%H') AS hr, DATE_FORMAT(sendtime, '%i') AS min
        FROM survey WHERE id=$surveyId";
$result=execute_query($sql);	
if(!mysql_num_rows($result)) {
   goto('surveys.php');
}
$survey  = mysql_fetch_assoc($result);

if(count($_POST)) {
  $_POST = strip_form_data($_POST);
  extract($_POST);
}  

if(isset($_POST["submit"])) {
   if(strlen($_POST['name']) < 3) {
     $errors = 'Survey name not valid<br/>';
   }
   // check date 
/*   if(!strlen($date)) {
	  $errors .= "Date for sending Survey not valid<br/>";
   }	 
   // check minute 
   if(!preg_match("/^[0-9]{1,2}$/", $min) || $min>59) {
	  $errors .= "Time for sending Survey not valid<br/>";
   }	   
   // check that send time is not in the past 
   $sdate = preg_split("/\//", $date);
   $d = array_shift($sdate);
   $m = array_shift($sdate);
   $y = array_shift($sdate);
   if(mktime($hr, $min, 0, $m, $d, $y) < time()) {
	      $errors .= "Time for sending Survey is in the past<br/>";
   }
   else {	
	  $sendtime = "$y-$m-$d $hr:$min:00";   
   }	   */
   $phone_numbers = read_phone_numbers('survey'); 
   if(is_numeric($phone_numbers) && $phone_numbers== 0) {
      $errors .= 'No valid phone numbers found in uploaded file<br/>';
   }
   if(strlen($keyword) && !isset($errors)) {
      if(!isset($disociate)) {
	     if(unique_field_exists('keyword', $keyword, 'survey', $surveyId)) {
		    $errors .= "There's a survey associated with this Keyword (".get_survey_from_keyword($keyword).")<br/>";
		 }
	  }
   }   
   if(!isset($errors)) {
     if(strlen($keyword) && isset($disociate)) {
	     disociate_keyword_with_survey($keyword);
	 }
     extract(escape_form_data($_POST));
	 $reply = rtrim($reply);
	 if(strlen($reply) > 160) {
	    $reply = substr($reply, 0, 160);
	 }	 
     $sql = "UPDATE survey SET name='$name', keyword=IF(LENGTH('$keyword'), '$keyword', NULL), reply=IF(LENGTH('$reply'), '$reply', NULL) 
	 WHERE id=$surveyId";
     execute_nonquery($sql);
	 
	 /* the phone numbers */	 
	 foreach($phone_numbers as $phone) {
	    if(!preg_match("/^[0-9]{8,}$/", $phone)) {
		   continue;
		} 	 
	    $sql = "INSERT INTO surveyno(surveyId, createdate, phone) VALUES ($surveyId, NOW(), '$phone')";
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
	 extract($survey);
	 logaction("Modified survey: Previous Details(Name: $name, Send time: $sendtime, Keyword: $keyword");
	 clear_working_list();
	 goto("survey.php?surveyId=$surveyId");
  }
}

if(strlen($qimportlist)) {
   $qimportlist = trim($qimportlist);
   $qimportlist = preg_replace("/,$/", "", $qimportlist);
   $list = preg_split("/,/", $qimportlist);
   if(count($list)) {
      $qlabel = '&nbsp;<span style="color: #008800">Imported Nos. from ('.count($list).') Survey(s)</span>
	  <br/><span style="cursor: pointer;color: #FF0000; font-size: 10px" 
	  onclick="removelabel(\'ql\', false)" title="Click to remove numbers">[delete]</span>';
   }
} 
if(!isset($qlabel)) {
   $qlabel = '&nbsp;Import Nos. From Existing Survey';
}

if(strlen($gimportlist)) {
   $gimportlist = trim($gimportlist);
   $gimportlist = preg_replace("/,$/", "", $gimportlist);
   $list = preg_split("/,/", $gimportlist);
   if(count($list)) {
      $glabel = '&nbsp;<span style="color: #FF3300">Imported ('.count($list).') Number(s)</span>
	  <span style="cursor: pointer;color: #FF0000; font-size: 10px" onclick="removelabel(\'gl\', false)" title="Click to remove numbers">[delete]</span>';
   }
} 
if(!isset($glabel)) {
   $glabel = '&nbsp;Import Nos. From General List';
}

if(strlen($wimportlist)) {
   $wimportlist = trim($wimportlist);//print $wimportlist;
   $wimportlist = preg_replace("/,$/", "", $wimportlist);
   $list = preg_split("/,/", $wimportlist);
   if(count($list)) {
      $wlabel = '&nbsp;&nbsp;<span style="color: #008800">Imported ('.count($list).') Number(s) from List</span>
	  <span style="cursor: pointer;color: #FF0000" onclick="removelabel(\'wl\',true)" title="Click to remove numbers">[delete]</span>';
   }
} 

if(isset($errors)) {
  $errors = "<br/>$errors<br/>";
}
extract($survey);

/* menu highlight */
$page = 'survey';
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

<body class="main">
<table width="790" border="0" align="center" cellpadding="0" cellspacing="0">
     <!--DWLayoutTable-->
     <tr>
          <td width="790" height="124" valign="top"><? include('top.php') ?></td>
     </tr>
     <tr>
        <td height="577" valign="top">
	         <table width="100%" border="0" cellpadding="0" cellspacing="0" class="border">
	              <!--DWLayoutTable-->
	              <tr>
	                   <td height="22" colspan="3" align="center" valign="middle" class="caption">Edit Survey </td>
                  </tr>
	              <tr>
	                   <td width="15" height="48">&nbsp;</td>
                       <td width="747">&nbsp;</td>
                       <td width="26">&nbsp;</td>
                  </tr>
	              <tr>
	                   <td height="260">&nbsp;</td>
                       <td valign="top">
					   <form name="df" method="post" enctype="multipart/form-data" onsubmit="return !page.locked">
                            <fieldset>
                            <legend>Survey Details</legend>
				            <table width="745" border="0" cellpadding="0" cellspacing="0">
				                 <!--DWLayoutTable-->
				                 <tr>
				                      <td width="29" height="23">&nbsp;</td>
                        <td width="58">&nbsp;</td>
                        <td width="46">&nbsp;</td>
                        <td colspan="8" valign="top" class="error">
                           <? if(isset($errors)) echo $errors; ?>					   </td>
                        <td width="16">&nbsp;</td>
				                 </tr>
					     
					     <tr>
					     <td height="28" colspan="3" align="right" valign="middle">Name:&nbsp;&nbsp;</td>
                         <td colspan="8" valign="middle">
						    <input name="name" type="text" class="input" id="name" value="<?= $name ?>" size="55" /></td>
                         <td>&nbsp;</td>
		                    </tr>
					     <tr>
					     <td height="28" colspan="3" align="right" valign="middle">Keyword:&nbsp;&nbsp;</td>
                         <td colspan="8" valign="middle">
						    <input name="keyword" type="text" class="input" id="keyword" value="<?= $keyword ?>" size="55" /></td>
                         <td>&nbsp;</td>
		                    </tr>
					     <tr>
					     <td height="70" colspan="3" align="right" valign="top"><br />
					        Reply SMS:&nbsp;&nbsp;</td>
                         <td colspan="8" valign="middle">
						 <textarea name="reply" cols="55" class="input" id="reply" style="width: 360px; height: 65px"><?= $reply ?></textarea>                         </td>
                         <td>&nbsp;</td>
		                    </tr>							
					     <tr>
					     <td height="35" colspan="3" align="right" valign="middle"><!--DWLayoutEmptyCell-->&nbsp;</td>
                         <td width="20" valign="middle">
						 <input name="disociate" type="checkbox" id="disociate" value="1"  <?= isset($disociate) ? 'checked="checked"' : NULL ?>/></td>
                         <td colspan="7" valign="middle" style="font-size: 10px">Dissociate Keyword with Any Existing Survey </td>
                         <td>&nbsp;</td>
		                    </tr>							
					     <!--tr>
					     <td height="28" colspan="3" align="right" valign="middle">Send Time:&nbsp;&nbsp;</td>
                         <td colspan="8" valign="middle"><input name="date" type="text" class="input" id="date" style="cursor: pointer" title="Select Date" onclick="displayDatePicker('date')" value="<?= $date ?>" size="20" readonly="true" />
                            <select name="hr" class="input" id="hr">
                               <?= hours($hr) ?>
                                                        </select>
                            <input name="min" type="text" class="input" id="min" value="<?= $min ?>" size="4" />
HRS</td>
                         <td>&nbsp;</td>
		                    </tr-->														
					                    <tr>
					                         <td height="50">&nbsp;</td>
                                             <td>&nbsp;</td>
                                             <td colspan="9" valign="middle" class="caption2"><u>Phone Numbers for this Survey</u> </td>
                                             <td>&nbsp;</td>
					                    </tr>	
					                    <tr>
					                         <td height="44">&nbsp;</td>
                                             <td colspan="4" align="right" valign="middle" nowrap="nowrap">Browse File:&nbsp;&nbsp;</td>
                                           <td colspan="2" valign="middle" nowrap="nowrap"><img src="images/excel.jpg" width="30" height="30" style="cursor: pointer" title="Input phone numbers from file. Excel / CSV or (Text file - one No. per line)"/><br /></td>
                                           <td colspan="4" valign="middle">
&nbsp;										   <input type="file" name="file" size="45" style="font-size: 12px"/></td>
                         <td>&nbsp;</td>
					                    </tr>
					                    <tr>
					                       <td height="60"></td>
					                       <td>&nbsp;</td>
					                       <td>&nbsp;</td>
					                       <td>&nbsp;</td>
					                       <td width="8">&nbsp;</td>
					                       <td colspan="3" valign="middle">
										   <img src="images/group.jpg" width="45" height="45" style="cursor: pointer" 
										   title="Click to Import Numbers" onclick="importn('survey')"/></td>
					                       <td width="237" valign="middle" id="qlabel" style="font-size: 10px"><?= $qlabel ?></td>
					                       <td width="38" valign="middle"><img src="images/import.gif" width="38" height="46" style="cursor: pointer" title="Click to import numbers from the general list" onclick="importn('general')"/></td>
					                       <td width="248" valign="middle" id="glabel" style="font-size: 10px"><?= $glabel ?></td>
					                       <td></td>
	                        </tr>
 <tr>
					                       <td height="40"></td>
					                       <td colspan="5" align="right" valign="top"></td>
					                       <td colspan="5" valign="middle"><a href="#" title="Get Contacts from Group(s) and Working List" style="color: #FF3300; text-decoration: underline" onclick="importn('worklist');return false;">[Send To Group] </a> <span id="wlabel">
					                       			<?= $wlabel ?>
					                       </span></td>
					                       <td></td>
	                        </tr>           
									    
					                    <tr>
					                       <td height="134"></td>
					                       <td colspan="5" align="right" valign="top"><br />
			Input Numbers:&nbsp;&nbsp; </td>
					                       <td colspan="5" valign="middle">
								              <textarea name="numbers" style="width: 320px; height: 100px"  
										   class="input" id="numbers"><?= $numbers ?></textarea>
									          
							               <br />
							               (Separate with commas) </td>
					                       <td></td>
	                        </tr>
					                    
					     <tr>
					      <td height="40">&nbsp;</td>
                          <td>&nbsp;</td>
                          <td>&nbsp;</td>
                          <td>&nbsp;</td>
                          <td>&nbsp;</td>
                          <td width="19">&nbsp;</td>
                          <td colspan="5" valign="middle">
					<input name="submit" type="submit" class="button" id="submit" value="Update Survey" />
					<input name="cancel" type="submit" class="button" id="cancel" value="Cancel" />
                                                  <input name="qimportlist" type="hidden" id="qimportlist" value="<?= $qimportlist ?>" />
                                                  <input name="gimportlist" type="hidden" id="gimportlist" value="<?= $gimportlist ?>" />
                                                  <input name="wimportlist" type="hidden" id="wimportlist" value="<?= $wimportlist ?>" /></td>
                         <td>&nbsp;</td>
					     </tr>
					     <tr>
					        <td height="16">&nbsp;</td>
					        <td>&nbsp;</td>
					        <td>&nbsp;</td>
					        <td>&nbsp;</td>
					        <td>&nbsp;</td>
					        <td>&nbsp;</td>
					        <td width="11">&nbsp;</td>
					        <td width="15">&nbsp;</td>
					        <td>&nbsp;</td>
					        <td>&nbsp;</td>
					        <td>&nbsp;</td>
					        <td>&nbsp;</td>
					        </tr>
					     
					     <tr>
					        <td height="47"></td>
					        <td colspan="10" valign="middle" id="note"><span style="color: #CC0000">Please Note:</span> Numbers Can be Imported only from one of the following file formats: (i) Excel, (ii) CSV (Comma Separated), (iii) Text File. All files should have one phone number per line</td>
					        <td></td>
					        </tr>
					     <tr>
					        <td height="14"></td>
					        <td></td>
					        <td></td>
					        <td></td>
					        <td></td>
					        <td></td>
					        <td></td>
					        <td></td>
					        <td></td>
					        <td></td>
					        <td></td>
					        <td></td>
					        </tr>
		                    </table>
                       </fieldset></form></td>
                       <td>&nbsp;</td>
                  </tr>
	              <tr>
	                   <td height="30"></td>
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

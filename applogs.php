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
include("display.php");
require 'excel/functions.php';

dbconnect();
validate_session(); 
check_admin_user();

if(count($_POST)) {
  $_POST = strip_form_data($_POST);
  extract($_POST);
}  

if(isset($_POST['import'])) 
{ 
    if(!preg_match('/^[0-9]+$/', $start)) {
	    $errors = 'Specify a valid row number for the start of records<br/>';
	}
	if(!$misdn_col) {
         $errors .= 'Phone Number column required<br/>';
    }
	if(!preg_match('/^[0-9]+$/',$titleRow)){
		$errors .= 'Specify a valid row which contains the Titles';
	}
	if(!isset($errors)) {
      $ret = import_applogs();
	  if(!is_array($ret)) {
	      $errors = $ret.'<br/>';
	  } 
   }
   if(!isset($errors)) { 
     $filename = mysql_real_escape_string($_FILES['file']['name']);
	   $importID = md5(time().$filename);
	   $fileSignature = md5_file($_FILES['file']['tmp_name']);
	   $application_txt = get_virtual_app_text($application);
	   
	   $sql = "INSERT INTO vAppUploadLog(date, filename, application, fileSignature, importID) 
	           VALUES(NOW(), '$filename', '$application_txt', '$fileSignature', '$importID')";
	   execute_update($sql);
	   
	   if(!strcmp('GoogleSMS6001', $application)) 
	   {
	       foreach($ret as $log) {
		       $location = mysql_real_escape_string($log['location']);
			   $site = mysql_real_escape_string($log['site']);
		       $sql = "INSERT INTO GoogleSMS6001 (date, logdate, misdn, site, location, importID) 
			           VALUES (NOW(), '$log[date]', '$log[misdn]', IF(LENGTH('$site'), '$site', NULL), 
					   IF(LENGTH('$location'), '$location', NULL), '$importID')";
			   
			   execute_update($sql);
		   }
	   }
	   elseif(!strcmp('HealthIVR', $application)) 
	   {
	       foreach($ret as $log) {
		       $duration = strlen($log['duration']) ? $log['duration'] : NULL;
		       $sql = "INSERT INTO HealthIVR (date, logdate, misdn, duration, importID) 
			           VALUES(NOW(), '$log[date]', '$log[misdn]', IF(LENGTH('$duration'), '$duration', NULL), '$importID')";
			   
			   execute_update($sql);
		   }
	   }
			elseif(!strcmp('SurveyForms',$application)){
				foreach($ret as $log){
					$log[other] = mysql_real_escape_string($log[other]);
					$sql = "INSERT INTO oldFormSurveys (date, logdate, msisdn, otherInfo, importID) 
									VALUES (NOW(), '$log[date]', '$log[msisdn]', '$log[other]','$importID')";
					execute_update($sql);
				}
			}
	   $user = get_user_details();
	   $logstr = "User ($user[firstName] $user[lastName]) uploaded information: $importID ($filename) for ".
	   "virtual application $application_txt. Total records: ".count($ret);
	   logaction($logstr);
	   unlink($_FILES['file']['tmp_name']);
	   show_message('Application Logs Imported', count($ret).' '.$application_txt.' Log(s) have been successfuly imported', '#008800');
   }
}

if(!count($_POST)) {
   $_POST['start'] = 3;
}

if(!isset($application)) {
    $application = 'GoogleSMS6001';
}

if(isset($errors)) {
    $errors = '<br>'.$errors.'<br/>';
}

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
          <td height="448" valign="top"><table width="100%" border="0" cellpadding="0" cellspacing="0" class="border">
               <!--DWLayoutTable-->
               <tr>
                    <td height="22" colspan="4" align="center" valign="middle" class="caption">Import Third Party Application Logs </td>
                    </tr>
               <tr>
                    <td height="30" colspan="4" valign="top"><? require 'users.menu.php' ?></td>
               </tr>
               <tr>
                  <td width="63" height="21"></td>
                  <td width="115">&nbsp;</td>
                  <td width="531" valign="middle" class="error"><?= $errors ?></td>
                  <td width="79"></td>
               </tr>
			   <form method="post" enctype="multipart/form-data">
               <tr>
                 <td height="48"></td>
                 <td valign="middle">Select Application:&nbsp;&nbsp;</td>
                 <td valign="middle"><select name="application" id="application" style="width:300px" onchange="this.form.submit()">
                   <option value="GoogleSMS6001" <?= !strcmp($application, 'GoogleSMS6001') ? 'selected="selected"' : '' ?>>Google SMS</option>
                   <option value="HealthIVR" <?= !strcmp($application, 'HealthIVR') ? 'selected="selected"' : '' ?>>Health IVR</option>
                   <option value="SurveyForms" <?= !strcmp($application, 'SurveyForms') ? 'selected="selected"' : '' ?>>Form Surveys</option>
                 </select>                 </td>
                 <td></td>
               </tr>
               
               <tr>
                 <td height="294">&nbsp;</td>
                 <td colspan="2" valign="top">
				 <fieldset>
				 <legend><?= get_virtual_app_text($application) ?> - Import Application Logs </legend>
				 <?
				 switch($application) {
				    case 'GoogleSMS6001':
					     print get_googlesms_import_form();
						break;
						
						case 'HealthIVR':
					     print get_healthivr_import_form();
						break;
						
						case 'SurveyForms':
							 print get_formSurvey_import_form();
						break;
						
						default: 
					     print get_googlesms_import_form();
						break;
				 }
				 ?>
				 </fieldset>				 </td>
                 <td>&nbsp;</td>
               </tr>
			   </form>
               <tr>
                 <td height="31">&nbsp;</td>
                 <td>&nbsp;</td>
                 <td>&nbsp;</td>
                 <td>&nbsp;</td>
               </tr>
          </table>
		  </td>
     </tr>
     <tr>
          <td height="30" valign="top"><? include('bottom.php') ?></td>
     </tr>
</table>
</body>
</html>

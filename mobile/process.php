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
require '../constants.php';
require '../functions.php';
require '../scripts/functions.php';

dbconnect();
/* Accepts form data from Mobile survey */

if(!count($_POST)) {
   exit();
}
$debug = 1;
$surveyId = $_POST[surveyId]; //$_POST[FormIdentifier] ? $_POST[FormIdentifier] : 0;
$phoneId = $_POST[phoneId];

$sql = "SELECT * FROM msurvey WHERE id='$surveyId'";
$result = e_query($sql);
if(!mysql_num_rows($result)) {
    /* unknown survey */
	$surveyId = 0;
}

$survey = mysql_fetch_assoc($result);
$survey_name = $survey['name'];
$fields = unserialize($survey['fif']);

foreach($_POST as $key=>$val) {
   $_POST[$key] = trim($val);
}
/* upload any submited images */
$files = array(); 
if(is_array($_FILES['sentFromPhone']['name'])) {
	foreach($_FILES['sentFromPhone']['name'] as $key=>$file) {
	   $name = strlen($_FILES['sentFromPhone']['name'][$key]) ? $surveyId.'-'.date('YmdHis').'-'.$_FILES['sentFromPhone']['name'][$key] : NULL; 
	   /* get name of this field */
	   foreach($fields as $f) {
	      if(preg_match("/^($f[code])$/", $_FILES['sentFromPhone']['name'][$key])) {
		      $field = $f['name'];
			  break;
		  }
	   }
	   $files[]= array('field'=>$field, 'value'=>$name, 'code'=>$f['code']);
	   if(!strlen($_FILES['sentFromPhone']['name'][$key])) {
	       $name = mysql_real_escape_string($field);
	       $sql = "INSERT INTO eresult (date, surveyId, name, fieldcode, fieldtype, fieldvalue ) ".
	              "VALUES(NOW(), '$surveyId', '$name', '$f[code]', 'Image', 'NOT_UPLOADED')";
	       e_update($sql);		   
	      continue;
	   }   
	   if(!move_uploaded_file($_FILES['sentFromPhone']['tmp_name'][$key], MOBILE_UPLOADS_DIR.'/'.$name)) {
	      sendmail('Can not upload file as: '.MOBILE_UPLOADS_DIR.'/'.$name);
		  exit();
	   }
	   $name = mysql_real_escape_string($name);
	   $sql = "INSERT INTO eresult (date, surveyId, name, fieldcode, fieldtype, fieldvalue ) ".
	           "VALUES(NOW(), '$surveyId', '$name', '$f[code]', 'Image', 'UPLOADED')";
	   e_update($sql);		   
	}
} else {
	foreach($_FILES as $key=>$file) {
	   $name = strlen($file['name']) ? $surveyId.'-'.date('YmdHis').'-'.$file['name'] : NULL;
	   /* get name of this field */
	   foreach($fields as $f) {
	      if(preg_match("/^($f[code])$/", $key)) {
	              $field = $f['name'];
	                  break;
	          }
	   }
	   $files[]= array('field'=>$field, 'value'=>$name, 'code'=>$f['code']);
	   if(!strlen($file['name'])) {
	       $name = mysql_real_escape_string($field);
	       $sql = "INSERT INTO eresult (date, surveyId, name, fieldcode, fieldtype, fieldvalue ) ".
	              "VALUES(NOW(), '$surveyId', '$name', '$f[code]', 'Image', 'NOT_UPLOADED')";
	       e_update($sql);
	      continue;
	   }
	   if(!move_uploaded_file($file['tmp_name'], MOBILE_UPLOADS_DIR.'/'.$name)) {
	      sendmail('Can not upload file as: '.MOBILE_UPLOADS_DIR.'/'.$name);
	          exit();
	   }
	   $name = mysql_real_escape_string($name);
	   $sql = "INSERT INTO eresult (date, surveyId, name, fieldcode, fieldtype, fieldvalue ) ".
	           "VALUES(NOW(), '$surveyId', '$name', '$f[code]', 'Image', 'UPLOADED')";
	   e_update($sql);
	}
}
$results = array();
foreach($fields as $f) {
   if(preg_match("/numeric/i", $f['type']) || preg_match("/data/i", $f['type'])) {
        $results[] = array('field'=>$f['name'], 'value'=>$_POST[$f['code']], 'type'=>$f['type'], 'code'=>$f['code']);
		
		$name = mysql_real_escape_string($f['name']);
		$value = mysql_real_escape_string($_POST[$f['code']]);
		$sql = "INSERT INTO eresult (date, surveyId, name, fieldcode, fieldtype, fieldvalue ) ".
               "VALUES(NOW(), '$surveyId', '$name', '$f[code]', '$f[type]', '$value')";
		e_update($sql);		
   }
   elseif(preg_match("/checkbox/i", $f['type'])) {
      $values = array();
	  foreach($_POST as $key=>$val) {
	     if(!preg_match("/^($f[code])_/", $key)) {
		    continue;
		 }
		 $value = preg_replace("/^(.)*_/", "", $key);
		 foreach($f['options'] as $option) {
		     if($option['value'] == $value) {
			    $values[]=$option['name'];
				break;
			 }
		 }
	  }
	  foreach($values as $value) 
	  {
	       $value = mysql_real_escape_string($value);
		   $name = mysql_real_escape_string($f['name']);
		   $sql = "INSERT INTO eresult (date, surveyId, name, fieldcode, fieldtype, fieldvalue ) ".
                  "VALUES(NOW(), '$surveyId', '$name', '$f[code]', 'Checkbox', '$value')";
		   e_update($sql);		  
	  }
	  $values = implode("\n", $values);
	  $results[] = array('field'=>$f['name'], 'value'=>$values, 'type'=>$f['type'], 'code'=>$f['code']);	  
   }
   elseif(preg_match("/radio/i", $f['type']) || preg_match("/menu/i", $f['type'])) {
      $value = $_POST[$f['code']];
	  foreach($f['options'] as $option) {
	      if($option['value'] == $value) {
		     $value = $option['name'];
			 break;
		  }
	  }
	  $results[] = array('field'=>$f['name'], 'value'=>$value, 'type'=>$f['type'], 'code'=>$f['code']);
	  
	  $value = mysql_real_escape_string($value);
	  $name = mysql_real_escape_string($f['name']);
	  $sql = "INSERT INTO eresult (date, surveyId, name, fieldcode, fieldtype, fieldvalue ) ".
             "VALUES(NOW(), '$surveyId', '$name', '$f[code]', '$f[type]', '$value')";
	  e_update($sql);		
   }
}
$form = array('data'=>$results, 'uploads'=>$files);
$form = mysql_real_escape_string(serialize($form));
$md5_form = md5($form); 

$sql = "INSERT INTO mresult(date, surveyId, phoneId, form, surveySignature) VALUES(CURRENT_TIMESTAMP(), IF($surveyId, $surveyId, NULL),  IF(LENGTH('$phoneId'), '$phoneId', NULL), '$form', '$md5_form')"; 

			
e_update($sql);
//at this point a survey has been successfully recieved.
if(!($_phoneId = get_valid_reply_msisdn($phoneId))){
	//we can't send back any reply
}else{
	$total = get_survey_count($phoneId, $surveyId);
	if($total == -1){
		$reply = "Your phone number and survey could not be matched to our Database, Contact Applab";
	}else{
		$reply = "Thank you. Your submission has been received. You have so far submitted $total results for \"$survey_name\"";
	}
	global $GLOBAL_message_origin;
	$GLOBAL_message_origin = "APPLAB";
	sendsms($reply, $_phoneId);
}


if($debug) {
   print 'Done';
}
function e_query($sql) {
    if(!($result=mysql_query($sql))) {
	   sendmail("SQL ERROR: $sql \n".mysql_error());
	   exit();
	}
	return $result;
}

function e_update($sql) { 
    if(!mysql_query($sql)) { 
	   sendmail("SQL ERROR: $sql \n".mysql_error());
	   exit();
	}
	return true;
}

function sendmail($message, $subject='SCRIPT ERROR') {
    global $admin_email, $bcc_email, $debug;
	if($debug) {
	    print "Subject: $subject\nMessage: $message\n";
		exit();
	}
	mail($admin_email, $subject, $message, "From: survey script <script@switch1-afol.yo.co.ug>\nBcc:$bcc_address");
}

?>

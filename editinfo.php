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
include("constants.php");
include("functions.php");
include("sessions.php");

dbconnect();
validate_session(); 
check_admin_user();

session_start();

if(count($_POST)) {
    $_POST = strip_form_data($_POST);
    extract($_POST); 
}  
//print_r($_POST);
//exit;
if(isset($_POST["submit"])) {
   if(strlen($names) && !preg_match("/^[a-z0-9\s.-]{3,}$/i", $names)) {
      $errors = 'Names not valid<br/>';
   }   
   if(!($_misdn = get_misdn($misdn))) {
       $errors .= 'Phone Number not valid<br/>';
   }
   elseif(unique_field_exists('misdn', $_misdn, 'user', $_ui)) {
         $errors .= 'Phone Number  "'.$_misdn.'" already exists<br/>';
   }
   if(strlen($phones)) {
        $_phones = preg_split("/,(\s)*/", $phones); 
		$otherphones = array();
		foreach($_phones as $_phone) {
		    if(!($phone = get_misdn_strict($_phone))) {
			    $errors .= 'Invalid Phone Number "'.$_phone.'" in list<br/>';
				break;
			}
			$otherphones[] = $phone;
		}
		if(!strlen($errors))
		    $otherphones = implode(',', $otherphones);
   }
   else {
      $otherphones = NULL;
   }
   if($d || $m || $y) {
        if(!strlen($d) || !strlen($m) || !strlen($y)) {
		    $errors .= 'Date of Birth Not valid<br/>';
		}
		else {
		    $dob = $y.'-'.$m.'-'.$d;
		}
   }
   else {
       $dob = '0000-00-00';
   }
   $groups = array();
   foreach($_POST as $key=>$val) {
       if(!preg_match('/^group_[0-9]+$/', $key)) {
	       continue;
	   }
	   $groups[] = preg_replace('/^group_/', '', $val);
   } 
   $groups = implode(',', $groups);
      
   if(!isset($errors)) {
     extract(escape_form_data($_POST));
	 
	 $sql = "UPDATE user SET names=IF(LENGTH('$names'), '$names', NULL), misdn='$_misdn', 
	         subcountyId=IF(LENGTH('$subcountyId'), '$subcountyId', NULL), phones=IF(LENGTH('$otherphones'), '$otherphones', NULL), 
			 gender=IF(LENGTH('$gender'), '$gender', NULL), dob='$dob', occupationId=IF(LENGTH('$occupationId'), '$occupationId', NULL), 
			 location=IF(LENGTH('$location'), '$location', NULL), groups=IF(LENGTH('$groups'), '$groups', NULL), 
			 deviceInfo=IF(LENGTH('$deviceInfo'), '$deviceInfo', NULL), gpscordinates=IF(LENGTH('$gpscordinates'), '$gpscordinates', NULL),
			 notes=IF(LENGTH('$notes'), '$notes', NULL) WHERE id='$_ui'";			 
	 
	 execute_update($sql);
  }
}

if(isset($errors)) {
    $errors = $errors.'<br/><br/>
	<input type="button" class="button" value="&laquo; Back To Listing" onclick="location.replace(\''.$u.'\')"/>';
    show_message('Errors Occured', $errors, '#FF0000');
}
goto($u);

?>
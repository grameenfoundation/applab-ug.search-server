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

dbconnect();
validate_session(); 
check_admin_user();

extract($_GET);

if(isset($_POST["cancel"])) {
   goto("user.php?userId=$userId");
}

if(!preg_match("/^[0-9]+$/", $userId)) { 
   goto('users.php');
}
$sql = "SELECT user.*, DATE_FORMAT(dob, '%d') AS d, DATE_FORMAT(dob, '%m') AS m, DATE_FORMAT(dob, '%Y') AS y FROM user WHERE id=$userId";

$result = execute_query($sql);
if(!mysql_num_rows($result)) {
   goto('users.php');
}
$user = mysql_fetch_assoc($result);

if(count($_POST)) {
    $_POST = strip_form_data($_POST);
    extract($_POST);
}  

if(isset($_POST["submit"])) {
   if(strlen($names) && !preg_match("/^[a-z0-9\s.-]{3,}$/i", $names)) {
      $errors = 'Names not valid<br/>';
   }   
   if(!($_misdn = get_misdn($misdn))) {
       $errors .= 'Phone Number not valid<br/>';
   }
   elseif(unique_field_exists('misdn', $_misdn, 'user', $userId)) {
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
			 notes=IF(LENGTH('$notes'), '$notes', NULL) WHERE id='$userId'";
	 
	 execute_query($sql);
	 goto("user.php?userId=$userId");
  }
}

extract($user); 

if(isset($errors)) {
  $errors = "<br/>$errors<br/>";
}
if(!count($_POST)) {
    $subcounty = get_table_record('subcounty', $subcountyId);
	extract($subcounty);
}
if(!count($_POST) && strlen($groups)) {
     $_groups = preg_split('/,/', $groups);
	 foreach($_groups as $group) {
	     $_POST['group_'.$group] = $group;
	 }
}
$groupoptions = get_group_options();
$occupation_options = get_table_records('occupation', $occupationId, 'name');

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
</head>

<body>
<table width="790" border="0" align="center" cellpadding="0" cellspacing="0" class="main">
     <!--DWLayoutTable-->
     <tr>
          <td width="790" height="124" valign="top"><? include('top.php') ?></td>
     </tr>
     <tr>
          		<td height="512" valign="top"><table width="100%" border="0" cellpadding="0" cellspacing="0" class="border">
          					<!--DWLayoutTable-->
          					<tr>
          								<td height="22" colspan="3" align="center" valign="middle" class="caption">Edit User Details </td>
                    			</tr>
          					<tr>
          								<td width="42" height="30"></td>
                    			<td width="708" valign="top"><? require 'users.menu.php' ?></td>
          								<td width="38">&nbsp;</td>
               			</tr>
          					
          					<tr>
          								<td height="358">&nbsp;</td>
               						<td valign="top">
               									<form method="post">
												<fieldset>
               												<legend>User Details</legend>
					     			<table width="100%" border="0" cellpadding="0" cellspacing="0">
					     						<!--DWLayoutTable-->
					     						<tr>
					     									<td width="15" height="23">&nbsp;</td>
                   			<td width="236">&nbsp;</td>
                   			<td width="388" valign="top" class="error">
                   						<? if(isset($errors)) echo $errors; ?>             </td>
                   			<td width="57">&nbsp;</td>
					          			<td width="10">&nbsp;</td>
					          			</tr>
					     						
					     									<tr>
					     												<td height="30">&nbsp;</td>
                    			<td align="right" valign="middle">Names:&nbsp;&nbsp;</td>
                    			<td valign="middle"><input name="names" type="text" class="input" id="names" value="<?= $names ?>" size="40" /></td>
                    			<td>&nbsp;</td>
					               			<td>&nbsp;</td>
					               			</tr>
					     									<tr>
					     												<td height="30">&nbsp;</td>
                    			<td align="right" valign="middle">Phone Number:&nbsp;&nbsp;</td>
                    			<td valign="middle"><input name="misdn" type="text" class="input" id="misdn" value="<?= $misdn ?>" size="40" /></td>
                    			<td>&nbsp;</td>
					               			<td>&nbsp;</td>
					               			</tr>
					     									<tr>
					     												<td height="30">&nbsp;</td>
                    			<td align="right" valign="middle">Other Phone Number(s):&nbsp;&nbsp;</td>
                    			<td colspan="2" valign="middle"><input name="phones" type="text" class="input" id="phones" value="<?= $phones ?>" size="40" />
                    						<span style="font-size: 10px; color: #666666">(separate with commas)</span></td>
                    			<td>&nbsp;</td>
					               			</tr>	
					     									<tr>
					     												<td height="30">&nbsp;</td>
                    			<td align="right" valign="middle">Gender:&nbsp;&nbsp;</td>
                    			<td valign="middle">
                    						<select name="gender" class="input" id="gender" style="width: 274px">
                    									<option></option>
                    									<option value="Male" <?= $gender=='Male' ? 'selected="selected"' : ''?>>Male</option>
                    									<option value="Female" <?= $gender=='Female' ? 'selected="selected"' : ''?>>Female</option>
                    									</select>                    </td>
                    			<td>&nbsp;</td>
					               			<td>&nbsp;</td>
					               			</tr>					   					
					     									<tr>
					     												<td height="30">&nbsp;</td>
                    			<td align="right" valign="middle">Date Of Birth:&nbsp;&nbsp;</td>
                    			<td valign="middle">
                    						<select name="d" class="input" id="d">
                    									<option></option>
                    									<?= days($d) ?>
                    									</select>
                    						
                    						<select name="m" class="input" id="m">
                    									<option></option>
                    									<?= months($m) ?>
                    									</select>
                    						<select name="y" class="input" id="y">
                    									<?= years_flexible(1940, date('Y')-10, $y, 1) ?>
                    									</select></td>
                    			<td>&nbsp;</td>
					               			<td>&nbsp;</td>
					               			</tr>
					     									<tr>
					     												<td height="30">&nbsp;</td>
                    			<td align="right" valign="middle">Occupation:&nbsp;&nbsp;</td>
                    			<td valign="middle"><select name="occupationId" class="input" id="occupationId" style="width: 274px">
                                  <option></option>
                                  <?= $occupation_options ?>
                                </select></td>
                    			<td>&nbsp;</td>
					               			<td>&nbsp;</td>
					               			</tr>
					     									<tr>
					     												<td height="30">&nbsp;</td>
                    			<td align="right" valign="middle">Location:&nbsp;&nbsp;</td>
                    			<td valign="middle"><input name="location" type="text" class="input" id="location" value="<?= $location ?>" size="40" /></td>
                    			<td>&nbsp;</td>
					               			<td>&nbsp;</td>
					               			</tr> 	
																     									<tr>
					     												<td height="30">&nbsp;</td>
                    			<td align="right" valign="middle">GPS Co-ordinates:&nbsp;&nbsp;</td>
                    			<td valign="middle"><input name="gpscordinates" type="text" class="input" id="gpscordinates" value="<?= $gpscordinates ?>" size="40" /></td>
                    			<td>&nbsp;</td>
					               			<td>&nbsp;</td>
					               			</tr> 	
											<tr>
					     												<td height="30">&nbsp;</td>
                    			<td align="right" valign="middle">District:&nbsp;&nbsp;</td>
                    			<td valign="middle"><select name="districtId" class="input" id="districtId" style="width: 274px" onchange="set_sc()">
                                  <option></option>
								  <?= get_table_records('district', $districtId, 'name') ?>
                                </select></td>
                    			<td>&nbsp;</td>
					               			<td>&nbsp;</td>
					               			</tr> 							
																     									<tr>
					     												<td height="30">&nbsp;</td>
                    			<td align="right" valign="middle">Subcounty:&nbsp;&nbsp;</td>
                    			<td valign="middle" id="subc">
								<select name="subcountyId" class="input" id="subcountyId" style="width:274px"></select>
                                  </td>
                    			<td>&nbsp;</td>
					               			<td>&nbsp;</td>
					               			</tr> 					
					     									<tr>
					     												<td height="30">&nbsp;</td>
                    			<td align="right" valign="middle">Group(s):&nbsp;&nbsp;</td>
                    			<td valign="middle"><?= $groupoptions ?></td>
                    			<td>&nbsp;</td>
				               			<td>&nbsp;</td>
				               			</tr> 	
					     									<tr>
					     												<td height="30">&nbsp;</td>
                    			<td align="right" valign="middle">Device information:&nbsp;&nbsp;</td>
                    			<td valign="middle"><input name="deviceInfo" type="text" class="input" id="deviceInfo" value="<?= $deviceInfo ?>" size="40" /></td>
                    			<td>&nbsp;</td>
				               			<td>&nbsp;</td>
				               			</tr> 	
					     									<tr>
					     												<td height="52">&nbsp;</td>
                    			<td align="right" valign="top"><br />
                    						Notes:&nbsp;&nbsp;</td>
                    			<td valign="middle"><textarea name="notes" cols="40" class="input" id="notes" style="width: 270px; height: 50px"><?= $notes ?></textarea></td>
                    			<td>&nbsp;</td>
				               			<td>&nbsp;</td>
				               			</tr> 					   				   				    	 
					     									<tr>
					     												<td height="35">&nbsp;</td>
                    			<td>&nbsp;</td>
                    			<td valign="middle"><input name="submit" type="submit" class="button" id="submit" value="Update User" />
                    						<input name="cancel" type="submit" class="button" id="cancel" value="Cancel" /></td>
                    			<td>&nbsp;</td>
					               			<td>&nbsp;</td>
					               			</tr>
					     									<tr>
					     												<td height="16">&nbsp;</td>
					               						<td>&nbsp;</td>
					               						<td>&nbsp;</td>
					               						<td>&nbsp;</td>
		               									<td>&nbsp;</td>
					               			</tr>
					     									
					          			</table>
                         						</fieldset></form></td>
                         			<td>&nbsp;</td>
               						</tr>
          					<tr>
          								<td height="35">&nbsp;</td>
               						<td>&nbsp;</td>
               						<td>&nbsp;</td>
               						</tr>		
          					
						</table></td>
     </tr>
      <tr>
          <td height="30" valign="top"><? include('bottom.php') ?></td>
     </tr>
</table>
<script type="text/javascript">
<?= get_district_js($user) ?>
set_sc();
</script>
</body>
</html>

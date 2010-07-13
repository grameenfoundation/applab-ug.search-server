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

if(isset($_POST["cancel"])) {
   goto('users.php');
}

if(count($_POST)) {
  $_POST = strip_form_data($_POST);
  extract($_POST);
}  

if(isset($_POST["submit"])) {
   if(!preg_match("/^[0-9]+$/", $start) || !$start) {
      $errors = 'Start of record in file not valid<br/>';
   }
   if(!$misdn_col) {
      $errors .= 'Phone Number column required<br/>';
   }  
   if(!isset($errors)) {
      $ret = import_users();
	  if(!is_array($ret)) {
	      $errors = $ret.'<br/>';
	  } 
   }
   if(!isset($errors)) { //foreach ($ret as $user) {print_r($user); print '<br><br>'; } exit;
	 foreach($ret as $user) {
	 	if(unique_field_exists('misdn', $user['misdn'], 'user')) {
		   if($replace_duplicates != 1) {
			   $errors .= "A User with phone \"$user[misdn]\" already exists!<br/>";
		   } 
		}
	 }
  }
  $count_not_updated = 0;
  
  if(!isset($errors)) 
  { 
      $warnings = NULL; 
	  $processed = array();
      foreach($ret as $user) 
	  {
	      extract(escape_form_data($user));
		  if(in_array($misdn, $processed)) {
			    $warnings .= "WARNING: User with phone number \"$misdn\" appears more than once in your uploaded file. 
			                  Only the first occurance has been entered.<br/><br/>";
			    $count_not_updated++;
				continue;		  
		  } 
		  if(strlen($initiativeInfo)) {
		        $result=execute_query("SELECT * FROM initiative WHERE name='$initiativeInfo'");
				if(mysql_num_rows($result)) {
		             $row=mysql_fetch_assoc($result);
					 $initiativeId = $row['id'];
				}
				else {
				    execute_update("INSERT INTO initiative(name) VALUES('$initiativeInfo')");
					$result=execute_query("SELECT * FROM initiative WHERE name='$initiativeInfo'");
					$row=mysql_fetch_assoc($result);
					$initiativeId=$row['id'];
				}
		  } 
		  // occupation
		  if(strlen($occupation)) {
		        $result=execute_query("SELECT * FROM occupation WHERE name='$occupation'");
				if(mysql_num_rows($result)) {
		             $row=mysql_fetch_assoc($result);
					 $occupationId = $row['id'];
				}
				else {
				    execute_update("INSERT INTO occupation(created, name) VALUES(NOW(), '$occupation')");
					$result=execute_query("SELECT * FROM occupation WHERE name='$occupation'");
					$row=mysql_fetch_assoc($result);
					$occupationId=$row['id'];
				}
		  } 
		  // district	      
		  if(strlen($district)) {
		        $result=execute_query("SELECT * FROM district WHERE name='$district'");
				if(mysql_num_rows($result)) {
		             $row=mysql_fetch_assoc($result);
					 $districtId = $row['id'];
				}
				else {
				    execute_update("INSERT INTO district(created, name) VALUES(NOW(), '$district')");
					$result=execute_query("SELECT * FROM district WHERE name='$district'");
					$row=mysql_fetch_assoc($result);
					$districtId=$row['id'];
				}
		  } 
		  // subcounty
		  $subcountyId = '';
		  if(strlen($subcounty)) {
		        $result=execute_query("SELECT * FROM subcounty WHERE name='$subcounty' AND districtId='$districtId'");
				if(mysql_num_rows($result)) {
		             $row=mysql_fetch_assoc($result);
					 $subcountyId = $row['id'];
				}
				else {
				    execute_update("INSERT INTO subcounty(created, districtId, name) VALUES(NOW(), '$districtId', '$subcounty')");
					$result=execute_query("SELECT * FROM subcounty WHERE name='$subcounty' AND districtId='$districtId'");
					$row=mysql_fetch_assoc($result);
					$subcountyId=$row['id'];
				}
		  } 
		  		  		  
		  $sql = 
		    "INSERT INTO user(createdate, names, misdn, phones, gender, dob, occupationId, location, gpscordinates, groups, 
			 subcountyId, deviceInfo, notes) 
	         VALUES(NOW(), IF(LENGTH('$names'), '$names', NULL), '$misdn', IF(LENGTH('$phones'), '$phones', NULL), 
			 IF(LENGTH('$gender'), '$gender', NULL), '$dob', IF(LENGTH('$occupationId'), '$occupationId', NULL), 
			 IF(LENGTH('$location'), '$location', NULL), IF(LENGTH('$gpscordinates'), '$gpscordinates', NULL), 
			 IF(LENGTH('$initiativeId'), '$initiativeId', NULL), IF(LENGTH('$subcountyId'), '$subcountyId', NULL),
			 IF(LENGTH('$deviceInfo'), '$deviceInfo', NULL), IF(LENGTH('$notes'), '$notes', NULL))"; 
		  
		  if(!mysql_query($sql)) 
		  {
		      $error = mysql_error(); 
		      if(preg_match("/duplicate/i", $error) && $replace_duplicates) 
			  { 
			      $sql = "UPDATE user SET names=IF(LENGTH('$names'), '$names', NULL), misdn='$misdn', 
	              phones=IF(LENGTH('$phones'), '$phones', NULL), gender=IF(LENGTH('$gender'), '$gender', NULL), dob='$dob', 
			      occupationId=IF(LENGTH('$occupationId'), '$occupationId', NULL), location=IF(LENGTH('$location'), '$location', NULL), 
			      groups=IF(LENGTH('$initiativeId'), '$initiativeId', NULL), gpscordinates=IF(LENGTH('$gpscordinates'), '$gpscordinates', NULL),
				  deviceInfo=IF(LENGTH('$deviceInfo'), '$deviceInfo', NULL), subcountyId=IF(LENGTH('$subcountyId'), '$subcountyId', NULL),
			      notes=IF(LENGTH('$notes'), '$notes', NULL) WHERE misdn='$misdn'";
                
				  execute_update($sql);
		      } else show_message('ERROR', $error.'<br/><br/>'.$sql, 'red');
	      } 
		  
		  $processed[] = $misdn;	
	  }
	  if(strlen($warnings)) {
		  $warnings = '<br/><br/><span style="color: #FF3300">'.$warnings.'</span>';
	  }
	  show_message('User(s) Successfuly Imported', (count($ret) - $count_not_updated).' User(s) have been successfuly imported in the system'.$warnings."<br/><br/><a href=\"users.php\">Go To Users</a>", '#008800');
  }
}
if(isset($errors)) {
  $errors = "<br/>$errors<br/>";
}
if(!count($_POST)) {
   $start = 3;
   $misdn_col = 1;
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
          <td height="323" valign="top"><table width="100%" border="0" cellpadding="0" cellspacing="0" class="border">
               <!--DWLayoutTable-->
               <tr>
                    <td height="22" colspan="3" align="center" valign="middle" class="caption">Import Users From File </td>
                    </tr>
               <tr>
                    <td height="30" colspan="3" valign="top"><? require 'users.menu.php' ?></td>
               </tr>
               <tr>
                  <td width="69" height="18">&nbsp;</td>
                  <td width="648">&nbsp;</td>
                  <td width="71">&nbsp;</td>
               </tr>
               <tr>
                  <td height="203">&nbsp;</td>
                  <td valign="top">
                            <fieldset>
                            <legend>Import From File</legend>
					     <table width="" border="0" cellpadding="0" cellspacing="0">
					          <!--DWLayoutTable-->
					          <tr>
					               <td width="22" height="23">&nbsp;</td>
                                   <td width="163">&nbsp;</td>
                                   <td width="30">&nbsp;</td>
                                   <td width="13">&nbsp;</td>
                                   <td width="374" valign="top" class="error">
                                      <? if(isset($errors)) echo $errors; ?>             </td>
                   <td width="44">&nbsp;</td>
                            </tr>
					          <form method="post" enctype="multipart/form-data">
					               <tr>
					                    <td height="30">&nbsp;</td>
                                        <td align="right" valign="middle">File</td>
                                        <td align="right" valign="top"><img src="images/excel.jpg" width="30" height="30" style="cursor: pointer" title="Browse Excel file containing the keywords"/></td>
                                      <td align="right" valign="middle">:&nbsp;&nbsp;</td>
                    <td valign="middle"><input type="file" name="file" size="40" style="font-size: 11px"/></td>
                    <td>&nbsp;</td>
                 </tr>
				  <tr>
					                    <td height="30">&nbsp;</td>
                                        <td colspan="3" align="right" valign="middle" nowrap="nowrap">Phone:&nbsp;&nbsp; </td>
                           				<td valign="middle"><select name="misdn_col" class="input" id="misdn_col" style="width: 285px">
													<option value="0"></option>
													<?= get_excel_columns($misdn_col) ?>
																				</select>
									</td>
                    <td>&nbsp;</td>
                  </tr>
				  <tr>
					                    <td height="30">&nbsp;</td>
                                        <td colspan="3" align="right" valign="middle" nowrap="nowrap">Other Phones:&nbsp;&nbsp; </td>
                           				<td valign="middle"><select name="phones_col" class="input" id="phones_col" style="width: 285px">
													<option value="0"></option>
													<?= get_excel_columns($phones_col) ?>
																				</select>
									</td>
                    <td>&nbsp;</td>
                  </tr>				  
				  
				  <tr>
					                    <td height="30">&nbsp;</td>
                                        <td colspan="3" align="right" valign="middle" nowrap="nowrap">Names:&nbsp;&nbsp; </td>
                           				<td valign="middle"><select name="names_col" class="input" id="names_col" style="width: 285px">
													<option value="0"></option>
													<?= get_excel_columns($names_col) ?>
																				</select>
									</td>
                    <td>&nbsp;</td>
                  </tr>
				  <tr>
					                    <td height="30">&nbsp;</td>
                                        <td colspan="3" align="right" valign="middle" nowrap="nowrap">Gender:&nbsp;&nbsp; </td>
                           				<td valign="middle"><select name="gender_col" class="input" id="gender_col" style="width: 285px">
													<option value="0"></option>
													<?= get_excel_columns($gender_col) ?>
																				</select>
									</td>
                    <td>&nbsp;</td>
                  </tr>	
				  <tr>
					                    <td height="30">&nbsp;</td>
                                        <td colspan="3" align="right" valign="middle" nowrap="nowrap">DoB:&nbsp;&nbsp; </td>
                           				<td valign="middle"><select name="dob_col" class="input" id="dob_col" style="width: 285px">
													<option value="0"></option>
													<?= get_excel_columns($dob_col) ?>
																				</select>
									</td>
                    <td>&nbsp;</td>
                  </tr>	
				  <tr>
					                    <td height="30">&nbsp;</td>
                                        <td colspan="3" align="right" valign="middle" nowrap="nowrap">Occupation:&nbsp;&nbsp; </td>
                           				<td valign="middle"><select name="occupation_col" class="input" id="occupation_col" style="width: 285px">
													<option value="0"></option>
													<?= get_excel_columns($occupation_col) ?>
																				</select>
									</td>
                    <td>&nbsp;</td>
                  </tr>	
				  <tr>
					                    <td height="30">&nbsp;</td>
                                        <td colspan="3" align="right" valign="middle" nowrap="nowrap">Location:&nbsp;&nbsp; </td>
                           				<td valign="middle"><select name="location_col" class="input" id="location_col" style="width: 285px">
													<option value="0"></option>
													<?= get_excel_columns($location_col) ?>
																				</select>
									</td>
                    <td>&nbsp;</td>
                  </tr>					  
				  <tr>
					                    <td height="30">&nbsp;</td>
                                        <td colspan="3" align="right" valign="middle" nowrap="nowrap">Group:&nbsp;&nbsp; </td>
                           				<td valign="middle"><select name="initiative_col" class="input" id="initiative_col" style="width: 285px">
													<option value="0"></option>
													<?= get_excel_columns($initiative_col) ?>
																				</select>
									</td>
                    <td>&nbsp;</td>
                  </tr>
				  <tr>
					                    <td height="30">&nbsp;</td>
                                        <td colspan="3" align="right" valign="middle" nowrap="nowrap">District:&nbsp;&nbsp; </td>
                           				<td valign="middle"><select name="district_col" class="input" id="district_col" style="width: 285px">
													<option value="0"></option>
													<?= get_excel_columns($district_col) ?>
																				</select>
									</td>
                    <td>&nbsp;</td>
                  </tr>	
				  <tr>
					                    <td height="30">&nbsp;</td>
                                        <td colspan="3" align="right" valign="middle" nowrap="nowrap">Subcounty:&nbsp;&nbsp; </td>
                           				<td valign="middle"><select name="subcounty_col" class="input" id="subcounty_col" style="width: 285px">
													<option value="0"></option>
													<?= get_excel_columns($subcounty_col) ?>
																				</select>
									</td>
                    <td>&nbsp;</td>
                  </tr>
				  <tr>
					                    <td height="30">&nbsp;</td>
                                        <td colspan="3" align="right" valign="middle" nowrap="nowrap">GPS Co-ordinates:&nbsp;&nbsp; </td>
                           				<td valign="middle">
										<select name="gpscordinates_col" class="input" id="gpscordinates_col" style="width: 285px">
													<option value="0"></option>
													<?= get_excel_columns($gpscordinates_col) ?>
										</select>
									</td>
                    <td>&nbsp;</td>
                  </tr>
				  <tr>
					                    <td height="30">&nbsp;</td>
                                        <td colspan="3" align="right" valign="middle" nowrap="nowrap">Device information:&nbsp;&nbsp; </td>
                           				<td valign="middle"><select name="deviceInfo_col" class="input" id="deviceInfo_col" style="width: 285px">
													<option value="0"></option>
													<?= get_excel_columns($deviceInfo_col) ?>
																				</select>
									</td>
                    <td>&nbsp;</td>
                  </tr>	
	  <tr>
					                    <td height="30">&nbsp;</td>
                                        <td colspan="3" align="right" valign="middle" nowrap="nowrap">Notes:&nbsp;&nbsp; </td>
                           				<td valign="middle"><select name="notes_col" class="input" id="notes_col" style="width: 285px">
													<option value="0"></option>
													<?= get_excel_columns($notes_col) ?>
																				</select>
									</td>
                    <td>&nbsp;</td>
                  </tr>						  				  				  				  				  			  				  
				  <tr>
				     <td height="31">&nbsp;</td>
				     <td colspan="3" align="right" valign="middle">Start of Record:&nbsp;&nbsp; </td>
				     <td valign="middle"><input name="start" type="text" class="input" id="start" value="<?= $start ?>" size="41" maxlength="1" /></td>
				     <td>&nbsp;</td>
				     </tr>
                                  <tr>
                                     <td height="31">&nbsp;</td>
                                     <td colspan="3" align="right" valign="middle">Update Existing Users:&nbsp;&nbsp; </td>
                                     <td valign="middle"><input name="replace_duplicates" id="replace_duplicates" value="1" type="checkbox"<? echo (($replace_duplicates == 1) ? " checked" : "");?>>
                                     &nbsp;&nbsp;Check this box if you want users that are already in the system but are also in your file to be updated with the new content in your file.</td>
                                     <td>&nbsp;</td>
                                     </tr>
				  <tr>
				     <td height="35">&nbsp;</td>
				     <td>&nbsp;</td>
				     <td>&nbsp;</td>
				     <td>&nbsp;</td>
				     <td valign="middle">
						      <input name="submit" type="submit" class="button" id="submit" value="Import Users" />
                              <input name="cancel" type="submit" class="button" id="cancel" value="Cancel" /></td>
                    <td>&nbsp;</td>
				  </tr>
				  <tr>
				     <td height="20"></td>
				     <td></td>
				     <td></td>
				     <td></td>
				     <td></td>
				     <td></td>
				     </tr>
				  <tr>
				     <td height="47"></td>
				     <td colspan="4" valign="middle" id="note"><span style="color: #CC0000">Please Note:</span> Users can only be imported from Excel Files. The Excel file should contain atleast one (1) column - the user's phone Number. Date (If present) should be of format mm/dd/YYYY </td>
				     <td></td>
				     </tr>
				  <tr>
				     <td height="25"></td>
				     <td>&nbsp;</td>
				     <td></td>
				     <td></td>
				     <td></td>
				     <td></td>
				     </tr>
					               </form>
				            </table>
                       </fieldset></td>
                         <td>&nbsp;</td>
               </tr>
               <tr>
                  <td height="48">&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
               </tr>
               
          </table></td>
     </tr><tr>
          <td height="30" valign="top"><? include('bottom.php') ?></td>
     </tr>
</table>
</body>
</html>

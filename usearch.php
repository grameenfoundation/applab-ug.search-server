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

session_start();

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

if(isset($_POST['submit'])) {
     extract(escape_form_data($_POST));
	 
     $sql = "SELECT user.*, DATE_FORMAT(createdate, '%d/%m/%Y %r') AS created, 
             DATE_FORMAT(updated, '%d/%m/%Y %r') AS updated FROM user WHERE 1";
     
	  if(strlen($misdn)) {
	       $_misdn = get_misdn($misdn);
	       $sql .= " AND (misdn='$misdn' OR misdn='$_misdn' OR FIND_IN_SET('$_misdn', phones))";
	  }
	  if(strlen($names)) {
	      $sql .= " AND names LIKE '%$names%'";
	  }	 
	  if(strlen($gender)) {
	      $sql .= " AND gender='$gender'";
	  }	 
	  if($occupationId) {
	      $sql .= " AND occupationId='$occupationId'";
	  }	 	  
	  if(strlen($location)) {
	      $sql .= " AND location LIKE '%$location%'";
	  }	 
	  if(strlen($deviceInfo)) {
	      $sql .= " AND deviceInfo LIKE '%$deviceInfo%'";
	  }	 
	  if($subcountyId) {
	      $sql .= " AND subcountyId='$subcountyId'";
	  }
	  elseif($districtId) {
	      $sql .= " AND subcountyId IN(SELECT id FROM subcounty WHERE districtId='$districtId')";
	  }
	  if(strlen($gpscordinates)) {
	      $sql .= " AND gpscordinates='$gpscordinates'";
	  }
	  /* the groups */
	  $sets = array();
	  foreach($_POST as $key=>$val) {
           if(!preg_match('/^group_[0-9]+$/', $key)) {
	           continue;
	       }
	       $sets[]= "FIND_IN_SET('$val', groups)";
      } 
	  if(count($sets)) {
	      $sql .= " AND (".(implode(' OR ', $sets)).")";
	  }
	  if(preg_match("/^[0-9]+$/", $age1) && preg_match("/^[0-9]+$/", $age2)) {
	      $sql .= " AND (dob NOT REGEXP '^0000-00-00\$') AND (DATE_FORMAT(CURRENT_DATE(), '%Y') - DATE_FORMAT(dob, '%Y')) BETWEEN $age1 AND $age2";
	  }
	  //$sql .= " ORDER BY createdate DESC"; 
	  
	  $result = execute_query($sql);
	  if(!mysql_num_rows($result)) {
	      $msg = 'No matching records found';
	  }
	  else { 
	      $matched_list = array();
		  while($row=mysql_fetch_assoc($result)) {
		      $matched_list[]=$row['id'];
		  }	  
	      $_SESSION['usearch'] = array('sql'=>$sql, 'matched_list'=>$matched_list);
		  //redirect('uresults.php');
		  redirect('users.php');
	  }
} 

if(isset($msg)) {
   $msg = "<br/><strong>$msg</strong><br/><br/>";
}
$sql = 'SELECT * FROM initiative ORDER BY name';
$result=execute_query($sql);

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
          		<td height="424" valign="top"><table width="100%" border="0" cellpadding="0" cellspacing="0" class="border">
          					<!--DWLayoutTable-->
          					<tr>
          								<td height="22" colspan="3" align="center" valign="middle" class="caption">Search For User Record </td>
                    						</tr>
          					<tr>
          								<td width="42" height="30"></td>
                    						<td width="708" valign="top"><? require 'users.menu.php' ?></td>
          											<td width="38">&nbsp;</td>
               						</tr>
          					
          					<tr>
          								<td height="329">&nbsp;</td>
               									<td valign="top">
												<form method="post">
               												<fieldset>
               															<legend>Search Criteria</legend>
					     						<table width="100%" border="0" cellpadding="0" cellspacing="0">
					     									<!--DWLayoutTable-->
					     									<tr>
					     												<td width="15" height="23">&nbsp;</td>
                   						<td width="236">&nbsp;</td>
                   						<td width="388" valign="top" style="color: #008800">
                   									<?= $msg ?>             </td>
                   						<td width="57">&nbsp;</td>
					          						<td width="10">&nbsp;</td>
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
                    						<td align="right" valign="middle">Names:&nbsp;&nbsp;</td>
                    						<td valign="middle"><input name="names" type="text" class="input" id="names" value="<?= $names ?>" size="40" /></td>
                    						<td>&nbsp;</td>
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
                    						<td align="right" valign="middle">Age Betwen:&nbsp;&nbsp;</td>
                    						<td valign="middle"><input name="age1" type="text" class="input" id="age1" value="<?= $age1 ?>" size="14" maxlength="2" />
                    									&nbsp;And&nbsp;
                    									<input name="age2" type="text" class="input" id="age2" value="<?= $age2 ?>" size="14" maxlength="2" /></td>
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
                    						<td valign="middle" id="subc"><select name="subcountyId" class="input" id="subcountyId" style="width:274px">
                  						  </select>
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
					     															<td height="35">&nbsp;</td>
                    						<td>&nbsp;</td>
                    						<td valign="middle"><input name="submit" type="submit" class="button" id="submit" value="Search" /></td>
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
          								<td height="41">&nbsp;</td>
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

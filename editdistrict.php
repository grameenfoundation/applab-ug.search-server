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

dbconnect();
validate_session(); 
check_admin_user();

check_id($districtId, 'districts.php');

if(isset($_POST["cancel"])) {
    goto('districts.php');
}
$district = get_table_record('district', $districtId);
if(empty($district)) {
    goto('districts.php');
}

if(count($_POST)) {
    $_POST = strip_form_data($_POST);
	extract($_POST);
}  

if(isset($_POST["submit"])) 
{
   if(!preg_match('/^[a-z]{2,50}$/i', $name)) {
     $errors = 'District name not valid<br/>';
   }
   elseif(unique_field_exists('name', $name, 'district', $districtId)) {
      $errors .= 'This district already exists<br/>';
   }   
   $subcounties = array();
   if(strlen($sub_counties)) {
       $list = preg_split('/\s+/', $sub_counties);
	   foreach($list as $subcounty) {
	       $subcounty = trim($subcounty);
	       if(!preg_match('/^[a-z]{2,50}$/i', $subcounty)) {
		       $errors = 'Subcounty "'.$subcounty.'" not valid<br/>';
			   break;
		   }
		   $subcounties[]=ucfirst($subcounty);
	   }
   }
   if(!isset($errors)) {
	   $sql = "UPDATE district SET name='$name' WHERE id='$districtId'"; 
	   execute_update($sql);
	   if(mysql_affected_rows())
	        logaction("Updated district ($district[name]) to $name");
			
	   if(count($subcounties) > 0) {
		   foreach($subcounties as $subcounty) {
		       $sql = "INSERT INTO subcounty(created, districtId, name) VALUES(NOW(), '$district[id]', '$subcounty')";
			   execute_update($sql);
		   }
	   }
	   goto("districts.php?districtId=$districtId");
  }
}
if(!count($_POST)) {
    extract($district);
}
if(isset($errors)) {
  $errors = "<br/>$errors<br/>";
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
          		<td height="337" valign="top">
				<table width="100%" border="0" cellpadding="0" cellspacing="0" class="border">
          					<tr>
          								<td height="22" colspan="4" align="center" valign="middle" class="caption">Edit District </td>
                    						</tr>
          					<tr>
          								<td width="50" height="48">&nbsp;</td>
                    						<td colspan="2" valign="top"><? require 'users.settings.php' ?></td>
                    						<td width="71"></td>
               						</tr>
          					
          					
          					
          					<tr>
          								<td height="224">&nbsp;</td>
               									<td width="19">&nbsp;</td>
               									<td width="648" valign="top">
               														<fieldset>
       																	<legend>Details</legend>
					     						<table width="100%" border="0" cellpadding="0" cellspacing="0">
					     									<!--DWLayoutTable-->
					     									<tr>
					     												<td width="17" height="23">&nbsp;</td>
                   						<td width="196">&nbsp;</td>
                   						<td width="336" valign="top" class="error">
                   									<?= $errors; ?>             </td>
                   						<td width="97">&nbsp;</td>
                  						</tr>
					     									<form method="post">
					     												<tr>
					     															<td height="24">&nbsp;</td>
                    						<td align="right" valign="middle">Name:&nbsp;&nbsp;</td>
                    						<td valign="middle"><input name="name" type="text" class="input" id="name" value="<?= $name ?>" size="40" /></td>
                    						<td>&nbsp;</td>
                   						</tr>
					     												<tr>
					     															<td height="120">&nbsp;</td>
                    						<td align="right" valign="top"><br />
                    									Add Sub Counties:&nbsp;&nbsp;</td>
                    						<td valign="middle"><textarea name="sub_counties" cols="40" class="input" 
							 style="width: 280px; height: 100px"	id="sub_counties"><?= $sub_counties ?></textarea>                    </td>
                    						<td>&nbsp;</td>
                   						</tr>
					     												<tr>
					     															<td height="10">&nbsp;</td>
                    						<td>&nbsp;</td>
                    						<td valign="middle" style="font-size:10px">(One Per line) </td>
                    						<td>&nbsp;</td>
                   						</tr>
<tr>
					     															<td height="35">&nbsp;</td>
                    						<td>&nbsp;</td>
                    						<td valign="middle"><input name="submit" type="submit" class="button" id="submit" value="Update District" />
                    											<input name="cancel" type="submit" class="button" id="cancel" value="Cancel" /></td>
                    						<td>&nbsp;</td>
                   						</tr>										
					     												<tr>
					     															<td height="25">&nbsp;</td>
					               									<td>&nbsp;</td>
					               									<td>&nbsp;</td>
					               									<td>&nbsp;</td>
		               									</tr>	
					               						</form>
					          						</table>
                         									</fieldset></td>
                         						<td>&nbsp;</td>
               									</tr>
          					<tr>
          								<td height="41">&nbsp;</td>
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

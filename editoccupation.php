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

extract($_GET);

check_id($occupationId, 'occupations.php');

if(isset($_POST["cancel"])) {
    goto('occupations.php');
}

if(count($_POST)) {
    $_POST = strip_form_data($_POST);
	extract($_POST);
}  

if(isset($_POST["submit"])) 
{
   if(strlen($name) < 2) {
     $errors = 'Occupation name not valid<br/>';
   }
   elseif(unique_field_exists('name', $name, 'occupation', $occupationId)) {
      $errors .= 'This entry already exists<br/>';
   }   
   if(!isset($errors)) {
     extract(escape_form_data($_POST));
	 $sql = "UPDATE occupation SET name='$name', description='$description' WHERE id='$occupationId'"; 
	 execute_update($sql);
	 goto('occupations.php');
  }
}
if(isset($errors)) {
  $errors = "<br/>$errors<br/>";
}
$r = get_table_record('occupation', $occupationId);
if(!$r)  {
      goto('occupations.php');
}
extract($r);

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
          		<td height="337" valign="top"><table width="100%" border="0" cellpadding="0" cellspacing="0" class="border">
          					<!--DWLayoutTable-->
          					<tr>
          								<td height="22" colspan="4" align="center" valign="middle" class="caption">Edit Occupation </td>
                    						</tr>
          					<tr>
          								<td width="46" height="48">&nbsp;</td>
                    						<td colspan="2" valign="top"><? require 'users.settings.php' ?></td>
                    						<td width="71">&nbsp;</td>
               						</tr>
          					<tr>
          								<td height="224">&nbsp;</td>
               									<td width="23">&nbsp;</td>
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
                    						<td align="right" valign="middle">Occupation Name:&nbsp;&nbsp;</td>
                    						<td valign="middle"><input name="name" type="text" class="input" id="name" value="<?= $name ?>" size="40" /></td>
                    						<td>&nbsp;</td>
                   						</tr>
					     												<tr>
					     															<td height="100">&nbsp;</td>
                    						<td align="right" valign="top"><br />
                    									Notes/Descritpion:&nbsp;&nbsp;</td>
                    						<td valign="middle"><textarea name="description" cols="40" class="input" 
							 style="width: 280px; height: 80px"	id="description"><?= $description?></textarea>                    </td>
                    						<td>&nbsp;</td>
                   						</tr>
					     												<tr>
					     															<td height="35">&nbsp;</td>
                    						<td>&nbsp;</td>
                    						<td valign="middle"><input name="submit" type="submit" class="button" id="submit" value="Update Occupation" />
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

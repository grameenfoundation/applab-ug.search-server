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

if(isset($_POST["cancel"])) {
 header("Location: categories.php");
 exit();
}
if(!preg_match("/^[0-9]+$/", $categoryId)) {
   goto('categories.php.php');
}
if(isset($_POST['cancel'])) {
   goto('categories.php.php');
}
$category = get_table_record('category', $categoryId);
if(empty($category)) {
    goto('categories.php.php');
}
extract($category);

if(count($_POST)) {
  $_POST = strip_form_data($_POST);
  extract($_POST);
}  
if(isset($_POST["submit"])) {
   if(!strlen($name)) {
     $errors = 'Category name not valid<br/>';
   }
   elseif(unique_field_exists('name', $name, 'category', $categoryId)) {
     $errors .= 'Category name already exists<br/>';
   }
   if(!isset($errors)) {
     extract(escape_form_data($_POST)); 
     $ckwsearch = isset($_POST['ckwsearch']) ? 1 : 0; 
	 $sql = "UPDATE category SET name='$name', ckwsearch=$ckwsearch, 
	 description=IF(LENGTH('$description'), '$description', NULL) WHERE id=$categoryId";
	 
	 execute_update($sql);
	 goto('categories.php');
  }
}
if(isset($errors)) {
  $errors = "<br/>$errors<br/>";
}
/* menu highlight */
$page = 'keywords';

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
          		<td height="319" valign="top"><table width="100%" border="0" cellpadding="0" cellspacing="0" class="border">
          					<!--DWLayoutTable-->
          					<tr>
          								<td height="22" colspan="3" align="center" valign="middle" class="caption">Edit Keyword Category </td>
                    			</tr>
          					<tr>
          								<td width="69" height="48">&nbsp;</td>
                    					<td width="648">&nbsp;</td>
                    					<td width="71">&nbsp;</td>
          					</tr>
          					<tr>
          								<td height="189">&nbsp;</td>
          								<td valign="top">
               											<fieldset>
               														<legend>Category Details</legend>
					     			<table width="100%" border="0" cellpadding="0" cellspacing="0">
					     						<!--DWLayoutTable-->
					     						<tr>
					     									<td width="17" height="22">&nbsp;</td>
                   			<td width="196">&nbsp;</td>
                   			<td width="336">&nbsp;</td>
                   			<td width="97">&nbsp;</td>
                  			</tr>
					     						<tr>
					     									<td height="23">&nbsp;</td>
					     									<td>&nbsp;</td>
					     									<td valign="top" class="error">
                   														<? if(isset($errors)) echo $errors; ?>             </td>
                   			<td>&nbsp;</td>
		     									</tr>
					     						<form method="post">
					     									
					     									<tr>
					     												<td height="26"></td>
					     												<td align="right" valign="middle">Name:&nbsp;&nbsp;</td>
                    													<td valign="middle"><input name="name" type="text" class="input" id="name" value="<?= $name ?>" size="40" /></td>
                    			<td></td>
		     												</tr>
					     									
					     									<tr>
					     												<td height="26"></td>
					     												<td align="right" valign="middle">Description:&nbsp;&nbsp;</td>
                    			<td valign="middle"><textarea name="description" class="input" id="description" 
								style="width: 270px; height: 70px"><?= $description ?></textarea>
                    			</td>
                    			<td></td>
		     												</tr> 	
															<tr>
					     												<td height="26"></td>
					     												<td align="right" valign="middle">Description:&nbsp;&nbsp;</td>
                    			<td valign="middle">
								<input name="ckwsearch" type="checkbox" id="ckwsearch" value="1" <?= isset($ckwsearch) && $ckwsearch  ? 'checked="checked"' : NULL ?> />
Include in CKW Search</td>
                    			<td></td>
		     												</tr> 	 
					     									
					     									<tr>
					     												<td height="35">&nbsp;</td>
					     												<td>&nbsp;</td>
					     												<td valign="middle"><input name="submit" type="submit" class="button" id="submit" value="Update Category" />
                    																<input name="cancel" type="submit" class="button" id="cancel" value="Cancel" /></td>
                    			<td>&nbsp;</td>
		     												</tr>
					     									<tr>
					     												<td height="42">&nbsp;</td>
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
          								<td height="58">&nbsp;</td>
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

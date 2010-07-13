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

check_id($aliasId, 'dictionary.php');

if(isset($_POST['cancel'])) {
   goto("aliases.php?dictionaryId=$dictionaryId");
}
if(isset($_POST['cancel2'])) {
   goto('dictionary.php');
}
if(count($_POST)) {
   $_POST = strip_form_data($_POST);
   extract($_POST);
}

if(isset($_POST['submit'])) {
   if(!preg_match("/^[a-z0-9-.]{2,50}$/i", $alias)) {
	  $errors = "Alias  not valid<br/>";
   }
   if(!isset($errors)) {
	 $alias = mysql_real_escape_string($alias);
	 $sql = "UPDATE aliases SET alias=LOWER('$alias') WHERE id=$aliasId";
	 if(!mysql_query($sql)) {
	          $error = mysql_error();
		      if(!preg_match("/duplicate/i", $error)) {
		        show_message('Database Error', mysql_error(), '#FF0000');
			  }
	 }
	 goto("aliases.php?dictionaryId=$dictionaryId");
   }
}
$record = get_table_record('aliases', $aliasId);
if(!$record) {
    goto('dictionary.php');
}
extract($record);

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
        <td height="255" valign="top">
		   			<table width="100%" border="0" cellpadding="0" cellspacing="0" class="border">
		   						<!--DWLayoutTable-->
		   						<tr>
		   									<td height="22" colspan="5" align="center" valign="middle" class="caption">Edit Keyword Alias  </td>
              			</tr>
		   						<tr>
		   									<td height="22" colspan="5" valign="top"><? require 'keywords.menu.php' ?></td>
                    			</tr>
		   						<tr>
		   									<td width="116" height="25">&nbsp;</td>
		   									<td width="112">&nbsp;</td>
		   									<td width="466">&nbsp;</td>
		   									<td width="41">&nbsp;</td>
		   									<td width="53">&nbsp;</td>
		   									</tr>
		   						
		   						
		   						<tr>
		   									<td height="27">&nbsp;</td>
                  							<td>&nbsp;</td>
                  							<td colspan="2" valign="middle" class="error"><?= $errors ?></td>
                  			<td>&nbsp;</td>
               			</tr>     <form method="post">        
		   						<tr>
		   									<td height="26">&nbsp;</td>
                  							<td align="right" valign="middle">Name:&nbsp;&nbsp;</td>
                  			<td valign="middle">
                  								<input name="alias" type="text" class="input" id="alias" value="<?= isset($_POST['alias']) ? $_POST['alias'] : $alias ?>" size="40" maxlength="50" /></td>
                  			<td>&nbsp;</td>
                  			<td></td>
               			</tr>
		   						<tr>
		   									<td height="26">&nbsp;</td>
                  							<td align="right" valign="middle">Last Updated:&nbsp;&nbsp;</td>
                  							<td valign="middle"><?= $updated ?>	</td>
                  					<td>&nbsp;</td>
                  			<td></td>
               			</tr>						
		   						
		   									
		   									<tr>
		   												<td height="44"></td>
		   												<td></td>
		   												<td valign="middle"><input name="submit" type="submit" class="button" id="submit" value="Update Alias" />
                  													<input name="cancel2" type="submit" class="button" id="cancel2" value="Back to Dictionary" />
                  													 <input name="cancel" type="submit" class="button" id="cancel" value="Cancel" /></td>
                  			<td></td>
		   												<td></td>
		   												</tr>
		   									<tr>
		   												<td height="87"></td>
		   												<td></td>
		   												<td>&nbsp;</td>
		   												<td></td>
		   												<td></td>
		   												</tr>
			   			</form>
           			    </table></td>
     </tr>
     
     
        <tr>
          <td height="30" valign="top"><? include('bottom.php') ?></td>
     </tr>
</table>
</body>
</html>

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

check_id($dictionaryId, 'dictionary.php');

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
   if(!strlen($aliases)) {
      $errors = 'Please specify atleast one alias<br/>';
   }
   else {
      $aliaslist = array();
      $arr = preg_split("/\n+/", rtrim($aliases)); 
	  for($i=0; $i<count($arr); $i++) {
	      $aliaslist[] = rtrim($arr[$i]);
	  }
	  foreach($aliaslist as $alias) {
	     if(!preg_match("/^[a-z0-9-.]{2,50}$/i", $alias)) {
		     $errors .= "Alias \"$alias\" not valid<br/>";
		 }
	  }
   }
   if(!isset($errors) && (!isset($aliaslist) || !count($aliaslist))) {
       $errors .= 'No aliases to add<br/>';
   }
   if(!isset($errors)) {
       foreach($aliaslist as $alias) {
	      $alias = mysql_real_escape_string($alias);
	      $sql = "INSERT INTO aliases(created, word_id, alias) VALUES(NOW(), $dictionaryId, LOWER('$alias'))";
	      if(!mysql_query($sql)) {
	          $error = mysql_error();
		      if(preg_match("/duplicate/i", $error)) {
		         continue;
			  }
			  show_message('Database Error', mysql_error(), '#FF0000');
	      }
	   }
      goto("aliases.php?dictionaryId=$dictionaryId");
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
          		<td height="285" valign="top"><table width="100%" border="0" cellpadding="0" cellspacing="0" class="border">
          					<!--DWLayoutTable-->
          					<tr>
          								<td height="22" colspan="4" align="center" valign="middle" class="caption">Add Aliases to Word </td>
             			</tr>
          					<tr>
          								<td height="18" colspan="4" valign="top"><? require 'keywords.menu.php' ?></td>
               			</tr>
          					<tr>
          								<td width="121" height="12"></td>
                    			<td width="118"></td>
                    			<td width="379"></td>
               					<td width="170"></td>
               					</tr>
          					<tr>
          								<td height="29"></td>
          								<td></td>
          								<td valign="top" class="error"><?= $errors ?></td>
          								<td></td>
          								</tr>
          					
								<form method="post">
          					<tr>
          								<td height="70">&nbsp;</td>
               						<td align="right" valign="top"><br />
               									Alias(es):&nbsp;&nbsp;</td>
               						<td rowspan="2" valign="top"><textarea name="aliases" class="input" id="aliases" style="width: 280px; height: 100px"><?= $aliases ?></textarea></td>
               						<td>&nbsp;</td>
               			</tr>
          					<tr>
          								<td height="46"></td>
               						<td></td>
               						<td></td>
               			</tr>
          					
          					<tr>
          								<td height="30">&nbsp;</td>
               						<td>&nbsp;</td>
               						<td valign="top"><input name="submit" type="submit" class="button" id="submit" value="Add Alias(es)" />
               									<input name="cancel2" type="submit" class="button" id="cancel2" value="Back to Dictionary" />
               									 <input name="cancel" type="submit" class="button" id="cancel" value="Cancel" /></td>
               						<td>&nbsp;</td>
               						</tr>
									</form>
          					<tr>
          								<td height="15"></td>
               						<td></td>
               						<td></td>
               						<td></td>
               						</tr>
          					<tr>
          								<td height="35"></td>
          								<td colspan="2" align="center" valign="middle" id="note">
										<span style="color: #CC0000">Plese Note:</span> In put one alias per line. Each alias should be a single word</td>
               						<td></td>
          								</tr>
          					<tr>
          								<td height="25"></td>
          								<td></td>
          								<td></td>
          								<td></td>
          								</tr>
          					
          					
          					
          					
          					
          					
          					
               
               
               
          				</table></td>
     </tr>
    
   
     <tr>
          <td height="30" valign="top"><? include('bottom.php') ?></td>
     </tr>
</table>
</body>
</html>

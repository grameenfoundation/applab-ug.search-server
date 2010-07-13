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

check_id($keywordId, 'keywords.php');
if(!($keywd = get_table_record('keyword', $keywordId))) {
   goto('keywords.php');
}

if(isset($_POST["cancel"])) {
   goto("subkeywords.php?keywordId=$keywordId");
}

if(count($_POST)) {
  $_POST = strip_form_data($_POST);
  extract($_POST);
}  

if(isset($_POST["submit"])) {
   if(!preg_match("/^[a-z0-9.-]{1,}$/i", $keyword)) {
     $errors = 'Sub Keyword not valid<br/>';
   }
   else {
	 if(strlen($keyword)>keywordLength) {
	    $errors .= "Keyword too long. Only ".keywordLength." characters allowed";
	 }
   }
   if(!isset($errors)) {
     extract(escape_form_data($_POST));
	 $sql = "INSERT INTO subkeyword(keywordId, keyword, content, createdate) VALUES ($keywordId, '$keyword', '$content', NOW())";	         	 
	 if(!mysql_query($sql)) {
	    $error = mysql_error();
		if(preg_match("/duplicate/i", $error)) {
		   $errors = 'This sub keyword exists<br/>';
		}
		else {
		   show_message('Error', mysql_error());
		}
	 }
	 if(!isset($errors)) {
	    $id = mysql_insert_id();
	    goto("subkeyword.php?keywordId=$keywordId&subkeywordId=$id");
	 }
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
<script type="text/javascript" src="basic.js"></script>
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
                    <td height="22" colspan="3" align="center" valign="middle" class="caption">Create Sub Keyword Under - <?= $keywd['keyword'] ?></td>
                    </tr>
               <tr>
                    <td height="30" colspan="3" valign="top"><? require 'keywords.menu.php' ?></td>
               </tr>
               <tr>
                  <td width="69" height="18"></td>
                  <td width="648"></td>
                  <td width="71"></td>
               </tr>
               <tr>
                 <td height="229"></td>
                 <td valign="top">
                           <fieldset>
                           <legend>Sub Keyword Details</legend>
					     <table width="100%" border="0" cellpadding="0" cellspacing="0">
					          <!--DWLayoutTable-->
					          <tr>
					               <td width="17" height="23">&nbsp;</td>
                   <td width="190">&nbsp;</td>
                   <td colspan="2" valign="top" class="error">
                        <? if(isset($errors)) echo $errors; ?>             </td>
                   <td width="93">&nbsp;</td>
                  </tr>
					          <form method="post">
					               <tr>
					                    <td height="26">&nbsp;</td>
                    <td align="right" valign="middle">Name:&nbsp;&nbsp;</td>
                    <td colspan="2" valign="middle"><input name="keyword" type="text" class="input" id="keyword" value="<?= $keyword ?>" size="45" /></td>
                    <td>&nbsp;</td>
                   </tr>			   
				   <tr>
					                    <td height="60"></td>
                    <td align="right" valign="middle">Content:&nbsp;&nbsp;</td>
                    <td colspan="2" valign="middle"><textarea name="content" class="input" id="content" 
							  style="width: 300px; height: 80px" onkeydown="checklimit(this, 160)"><?= $content ?></textarea>                           </td>
                    <td></td>
                   </tr>
				   <tr>
				      <td height="30"></td>
				      <td></td>
				      <td width="34" valign="middle"><input name="chars" type="text" class="input" id="chars" size="4" maxlength="3" readonly="true" value="<?= strlen($content) ?>" /></td>
		                   <td width="312" valign="middle">&nbsp;Characters</td>
		                   <td></td>
				      </tr>
				   						
					               <tr>
					                  <td height="35"></td>
					                  <td></td>
					                  <td colspan="2" valign="middle">
									     <input name="submit" type="submit" class="button" id="submit" value="Add Sub Keyword" />
                                         <input name="cancel" type="submit" class="button" id="cancel" value="Cancel" /></td>
                    <td></td>
			                     </tr>
					               <tr>
					                  <td height="18"></td>
					                  <td></td>
					                  <td>&nbsp;</td>
					                  <td></td>
					                  <td></td>
			                     </tr>
					               </form>
				           </table>
                      </fieldset></td>
                         <td></td>
               </tr>
               <tr>
                 <td height="36"></td>
                 <td>&nbsp;</td>
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

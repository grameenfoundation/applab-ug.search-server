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
check_id($subkeywordId, 'keywords.php');

if(!($keywd = get_table_record('keyword', $keywordId))) {
   goto('keywords.php');
}

if(isset($_POST['cancel'])) {
   goto("subkeywords.php?keywordId=$keywordId");
}
if(isset($_POST['edit'])) {
   goto("editskeyword.php?keywordId=$keywordId&subkeywordId=$subkeywordId");
}
if(isset($_POST['delete'])) {
  execute_update("DELETE FROM subkeyword WHERE id=$subkeywordId");
  goto("subkeywords.php?keywordId=$keywordId");
}

$sql = "SELECT subkeyword.*, DATE_FORMAT(createdate, '%d/%m/%Y %r') AS createdate, 
        DATE_FORMAT(updated, '%d/%m/%Y %r') AS updated FROM subkeyword WHERE id=$subkeywordId";
$result = execute_query($sql);
if(!mysql_num_rows($result)) {
   goto('keywords.php');
}
extract(mysql_fetch_assoc($result));

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

<body class="main">
<table width="790" border="0" align="center" cellpadding="0" cellspacing="0">
     <!--DWLayoutTable-->
     <tr>
          <td width="790" height="124" valign="top"><? include('top.php') ?></td>
     </tr>
     <tr>
          <td height="343" valign="top"><table width="100%" border="0" cellpadding="0" cellspacing="0" class="border">
               <!--DWLayoutTable-->
               <tr>
                    <td height="22" colspan="3" align="center" valign="middle" class="caption"><?= $keywd['keyword'] ?> - Sub Keyword                    </td>
                    </tr>
               <tr>
                    <td width="56" height="32">&nbsp;</td>
                    <td width="679">&nbsp;</td>
                    <td width="53">&nbsp;</td>
               </tr>
               <tr>
                 <td height="243">&nbsp;</td>
                 <td valign="top">
			          <fieldset>
					<legend>Sub Keyword Details</legend>
					<table width="100%" border="0" cellpadding="0" cellspacing="0" 
					style="background-image: none; background-repeat: no-repeat; background-position: center">
                         <!--DWLayoutTable-->
                         <tr>
                              <td width="91" height="24">&nbsp;</td>
                              <td width="129" valign="top">							  </td>
                              <td width="362" valign="top"></td>
                              <td width="36">&nbsp;</td>
                              <td width="59">&nbsp;</td>
                         </tr>
                         <tr>
                              <td height="35">&nbsp;</td>
                              <td align="right" valign="middle" id="label">Sub Keyword:&nbsp;&nbsp;							  </td>
                              <td valign="middle" class="caption3"><?= $keyword ?>                                   &nbsp;</td>
                              <td>&nbsp;</td>
                              <td>&nbsp;</td>
                         </tr>
       
                        <tr>
                              <td height="35">&nbsp;</td>
                              <td align="right" valign="middle" id="label">Create Time:&nbsp;&nbsp;</td>
                              <td valign="middle" id="field"><?= $createdate ?></td>
                              <td>&nbsp;</td>
                              <td>&nbsp;</td>
                        </tr>
                        <tr>
                              <td height="35">&nbsp;</td>
                              <td align="right" valign="middle" id="label">Content:&nbsp;&nbsp;</td>
                              <td valign="middle" id="field">
							  <?= $content ? $content : 'N/A' ?>&nbsp;</td>
                              <td>&nbsp;</td>
                              <td>&nbsp;</td>
                        </tr>
                        <tr>
                              <td height="35">&nbsp;</td>
                              <td align="right" valign="middle" id="label">Updated:&nbsp;&nbsp;</td>
                              <td valign="middle" id="field">
							  <?= $updated ?>&nbsp;</td>
                              <td>&nbsp;</td>
                              <td>&nbsp;</td>
                        </tr>						 					 						 					 						 
                        <form method="post">
				      <tr>
                              <td height="48"></td>
                              <td></td>
                              <td colspan="2" valign="middle"><input name="edit" type="submit" class="button" id="edit" value="Edit Keyword"/>
                                <input name="delete" type="submit" class="button" id="delete" value="Delete" 
							   onclick="return confirm('Are you sure you want to delete this Sub Keyword?')"/>
                                 <input name="cancel" type="submit" class="button" id="cancel" value="&laquo; Sub Keyword"/>						     </td>
                             <td>&nbsp;</td>
				      </tr>
				      <tr>
				        <td height="16"></td>
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
                 <td height="44">&nbsp;</td>
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

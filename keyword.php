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

if(!preg_match("/^[0-9]+$/", $keywordId)) { 
   goto('keywords.php');
}
if(isset($_POST['cancel'])) {
   if(isset($return)) {
       goto($return);
   }
   goto("keywords.php");
}
if(isset($_POST['edit'])) {
   goto("editkeyword.php?keywordId=$keywordId");
}
if(isset($_POST['sub'])) {
   goto("subkeywords.php?keywordId=$keywordId");
}
if(isset($_POST['hits'])) {
   goto("hits.php?keywordId=$keywordId");
}
if(isset($_POST['delete'])) {
  delete_keyword($keywordId);
  goto('keywords.php');
}


$sql = "SELECT keyword.*, DATE_FORMAT(createDate, '%d/%m/%Y %r') AS createDate, 
        DATE_FORMAT(updated, '%d/%m/%Y %r') AS updated FROM keyword WHERE id=$keywordId";
$result = execute_query($sql);
if(!mysql_num_rows($result)) {
   goto('keywords.php');
}
extract(mysql_fetch_assoc($result));

$aliases = preg_replace("/,/", ", ", $aliases);
/* total replies */
$sql = "SELECT * FROM hit WHERE keyword='$keyword'";
$result = execute_query($sql);
$replies = mysql_num_rows(execute_query($sql));

if($otrigger) {
   $record = get_table_record('otrigger', $keywordId, 'keywordId');
   $options = unserialize($record['options']);
   $oplist = NULL;
   foreach($options as $option) {
      $oplist .= "$option[number]. ".get_column_value('keyword', 'keyword', $option['tkeywordId'], 'id').", ";
   }
   $oplist = '('.preg_replace("/,\s+$/", "", $oplist).')';
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

<body class="main">
<table width="790" border="0" align="center" cellpadding="0" cellspacing="0">
     <!--DWLayoutTable-->
     <tr>
          <td width="790" height="124" valign="top"><? include('top.php') ?></td>
     </tr>
     <tr>
          <td height="406" valign="top"><table width="100%" border="0" cellpadding="0" cellspacing="0" class="border">
               <!--DWLayoutTable-->
               <tr>
                    <td height="22" colspan="3" align="center" valign="middle" class="caption">Keyword - <?= $keyword ?>
                    </td>
                    </tr>
               <tr>
                    <td width="56" height="30">&nbsp;</td>
                    <td width="679">&nbsp;</td>
                    <td width="53">&nbsp;</td>
               </tr>
               <tr>
                  <td height="313">&nbsp;</td>
                  <td valign="top">
				       <fieldset>
					<legend>Keyword Details</legend>
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
                              <td height="30">&nbsp;</td>
                              <td align="right" valign="middle" class="label">Keyword:&nbsp;&nbsp;							  </td>
                              <td valign="middle" class="field"><?= $keyword ?>                                   &nbsp;</td>
                              <td>&nbsp;</td>
                              <td>&nbsp;</td>
                         </tr>
						 <tr>
                              <td height="30">&nbsp;</td>
                              <td align="right" valign="middle" class="label">Keyword Alias:&nbsp;&nbsp;							  </td>
                              <td valign="middle" class="field"><?= $keywordAlias? $keywordAlias: "N/A"; ?>                                   &nbsp;</td>
                              <td>&nbsp;</td>
                              <td>&nbsp;</td>
                         </tr>
                         <tr>
                              <td height="30">&nbsp;</td>
                              <td align="right" valign="middle" class="label">Category:&nbsp;&nbsp;							  </td>
                              <td valign="middle" class="field"><?= get_column_value('category', 'name', $categoryId) ?>                                   &nbsp;</td>
                              <td>&nbsp;</td>
                              <td>&nbsp;</td>
                         </tr>
                         <tr>
                              <td height="30">&nbsp;</td>
                              <td align="right" valign="middle" class="label">Language:&nbsp;&nbsp;							  </td>
                              <td valign="middle" class="field"><?= get_column_value('language', 'name', $languageId) ?>                                   &nbsp;</td>
                              <td>&nbsp;</td>
                              <td>&nbsp;</td>
                         </tr>						 						 
                        <tr>
                              <td height="30">&nbsp;</td>
                              <td align="right" valign="middle" class="label">Content:&nbsp;&nbsp;</td>
                              <td valign="middle" class="field">
							  <?= $content ? preg_replace("/::SHORTCODE::$/", $GLOBALS['shortcode'], $content) : 'N/A' ?>&nbsp;</td>
                              <td>&nbsp;</td>
                              <td>&nbsp;</td>
                        </tr>
						<tr>
                              <td height="30">&nbsp;</td>
                              <td align="right" valign="middle" class="label">Source of Information:&nbsp;&nbsp;</td>
                              <td valign="middle" class="field"><?= $attribution? $attribution: "N/A"; ?></td>
                              <td>&nbsp;</td>
                              <td>&nbsp;</td>
                        </tr>
                        <!--tr>
                              <td height="30">&nbsp;</td>
                              <td align="right" valign="middle" class="label">Alias(es):&nbsp;&nbsp;</td>
                              <td valign="middle" class="field"><?= strlen($aliases) ? $aliases : NONE ?></td>
                              <td>&nbsp;</td>
                              <td>&nbsp;</td>
                        </tr-->	
                        <tr>
                              <td height="30">&nbsp;</td>
                              <td align="right" valign="middle" class="label">Sub Keywords:&nbsp;&nbsp;</td>
                              <td valign="middle" class="field"><?= get_subkeywords($keywordId) ?></td>
                              <td>&nbsp;</td>
                              <td>&nbsp;</td>
                        </tr>							
                        <tr>
                              <td height="30">&nbsp;</td>
                              <td align="right" valign="middle" class="label">Total Hits:&nbsp;&nbsp;</td>
                              <td valign="middle" class="field">
							  <?= $replies ?> Hits </td>
                              <td>&nbsp;</td>
                              <td>&nbsp;</td>
                        </tr>	
                        <tr>
                              <td height="30">&nbsp;</td>
                              <td align="right" valign="middle" class="label">Outbound Trigger:&nbsp;&nbsp;</td>
                              <td valign="middle" class="field">
							  <?= $otrigger ? 'YES '.$oplist : 'NO' ?></td>
                              <td>&nbsp;</td>
                              <td>&nbsp;</td>
                        </tr><? if(strlen($quizAction_action) && ($FEATURE_keyword_action == 1)) { ?>
                        <tr>
                              <td height="30">&nbsp;</td>
                              <td align="right" valign="middle" class="label">Quiz Action:&nbsp;&nbsp;</td>
                              <td valign="middle" class="field">
                                                          <? if(!strcmp($quizAction_action, "remove")) { 
								echo "Remove from Quiz <b>".get_quiz_name_from_id($quizAction_quizId)."</b>";
							     } else {
								echo "Add to Quiz <b>".get_quiz_name_from_id($quizAction_quizId)."</b>";
							     }
							  ?></td>
                              <td>&nbsp;</td>
                              <td>&nbsp;</td>
                        </tr><? } ?>
                        <tr>
                              <td height="30">&nbsp;</td>
                              <td align="right" valign="middle" class="label">Create Time:&nbsp;&nbsp;</td>
                              <td valign="middle" class="field"><?= $createDate ?></td>
                              <td>&nbsp;</td>
                              <td>&nbsp;</td>
                        </tr>																		
                        <tr>
                              <td height="30">&nbsp;</td>
                              <td align="right" valign="middle" class="label">Updated:&nbsp;&nbsp;</td>
                              <td valign="middle" class="field">
							  <?= $updated ?>&nbsp;</td>
                              <td>&nbsp;</td>
                              <td>&nbsp;</td>
                        </tr>						 					 						 					 						 
                        <form method="post">
				      <tr>
                              <td height="48"></td>
                              <td colspan="3" valign="middle" class="field"><input name="hits" type="submit" class="button" id="hits" value="Keyword Hits"/> 
                                    <input name="edit" type="submit" class="button" id="edit" value="Edit Keyword"/>
                                    <input name="sub" type="submit" class="button" id="sub" value="Sub Keywords"/>
                                    <input name="delete" type="submit" class="button" id="delete" value="Delete" 
							   onclick="return confirm('Are you sure you want to delete this Keyword?')"/>
                        <input name="cancel" type="submit" class="button" id="cancel" value="&laquo; Go To Keywords"/>						     </td>
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
                  <td height="37">&nbsp;</td>
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

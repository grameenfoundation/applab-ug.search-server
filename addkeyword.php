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

dbconnect();
validate_session(); 
check_admin_user();

$full_quiz_list = get_all_quiz();

if(isset($_POST["cancel"])) {
   goto('keywords.php');
}

if(count($_POST)) {
  $_POST = strip_form_data($_POST);
  extract($_POST);
}  

if(isset($_POST["submit"])) { 
   if(!_validinteger($quizAction_quizId) || $quizAction_quizId == 0) {
        $quizAction_quizId = 0;
        $quizAction_action = "";
   } else {
        if(strcmp($quizAction_action, "remove") && strcmp($quizAction_action, "add")) {
                $quizAction_quizId = 0;
                $quizAction_action = "";
        }
   }

   if(!preg_match("/^[a-z0-9.-\s_]{1,}$/i", $keyword)) {
     $errors = 'Keyword not valid<br/>';
   }
   else {
     if(unique_field_exists('keyword', $keyword, 'keyword')) {
        $errors .= 'Keyword already exists!<br/>';
     }
   }
   
   if(strlen($keywordAlias)){
		 if(!preg_match("/^[a-z0-9.-\s_]{1,}$/i", $keywordAlias)) {
			 $errors .= 'Keyword Alias not valid<br/>';
		 }
		 else {
			 if(unique_field_exists('keyword', $keywordAlias, 'keyword')) {
					$errors .= 'Keyword Alias already exists!<br/>';
			 }
		}
	 }
	 
	 if($optionalWords){
			$word_s = preg_split('/,/', $optionalWords);
			foreach($word_s as $word_){
				if(!preg_match("/^[0-9a-z.-\s_]{2,}$/i", $word_)){
					$errors .= "Optional word not valid: \"$word_\"<br/>";
				}
			}
		}
   
   $aliaslist = array();
   if(strlen($aliases)) {
      $list = preg_split("/,/", $aliases);
	  foreach($list as $alias) {
	      $alias = trim($alias);
	      if(!preg_match("/^[0-9a-z-.]+$/", $alias)) {
		     $errors .= "Invalid Keyword alias: $alias";
			 break;
		  }
		  elseif(strlen($alias)>keywordLength) {
		     $errors .= "Keyword alias: $alias is too long. Only ".keywordLength." chars allowed";
			 break;
		  }
		  $aliaslist[] = $alias;
	  }
   }
   /* triggers */
   
   if(isset($make)) {
      $triggers = array(); 
	  for($i=1; $i<=9; $i++) { 
	     if(!strlen($_POST['keyword'.$i])) {
		    continue;
		 }
		 $tkeyword = $_POST['keyword'.$i]; 
		 $record = get_table_record('keyword', mysql_real_escape_string($tkeyword), 'keyword'); 
		 if(!$record) {
		    $errors .= "Target keyword \"$tkeyword\" does not exist!<br/>";
			$terror = 1;
			break;
		 }
		 $triggers[] = array('number'=>$i, 'tkeywordId'=>$record['id']);
	  }
	  if(!isset($terror) && !count($triggers)) {
	      $errors .= "Please specify atleast one Target keyword<br/>";
	  } 
   }
   if(!isset($errors)) { 
     extract(escape_form_data($_POST));
	 $aliases = implode(",", array_unique($aliaslist));
	 $otrigger = isset($make) ? 1 : 0;

	 $sql = "INSERT INTO keyword(keyword, content, aliases, keywordAlias, optionalWords, createDate, otrigger, categoryId, languageId, quizAction_quizId, quizAction_action, attribution) VALUES 
	         ('$keyword', '$content', '$aliases', '$keywordAlias', '$optionalWords', NOW(), $otrigger, IF($categoryId, $categoryId, NULL), IF($languageId, $languageId, NULL), '$quizAction_quizId', '$quizAction_action', '$attribution')";	         	 
	 execute_update($sql);
	 $keywordId = mysql_insert_id();
	 keyword_enter_normalizations($keywordId, $keyword, $keywordAlias, $optionalWords);
	 
	 if($keywordId >0 && isset($make)) {
	     $options = mysql_real_escape_string(serialize($triggers));
		 $sql = "INSERT INTO otrigger(createdate, keywordId, options) VALUES(NOW(), $keywordId, '$options')";
		 execute_update($sql);
	 }
	 goto("keyword.php?keywordId=$keywordId");
  }
}

if(isset($make)) {
  for($i=$opcount+1; $i<=9; $i++) {
      $thtml .= '<div id="option'.$i.'" style="display: none"></div>';
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
          <td height="388" valign="top"><table width="100%" border="0" cellpadding="0" cellspacing="0" class="border">
               <!--DWLayoutTable-->
               <tr>
                    <td height="22" colspan="3" align="center" valign="middle" class="caption">Create New Keyword </td>
                    </tr>
               <tr>
                    <td height="30" colspan="3" valign="top"><? require 'keywords.menu.php' ?></td>
               </tr>
               <tr>
                  <td width="69" height="18">&nbsp;</td>
                  <td width="648">&nbsp;</td>
                  <td width="71">&nbsp;</td>
               </tr>
               <tr>
                    <td height="233">&nbsp;</td>
                    <td valign="top">
                              <fieldset>
                              <legend>Keyword Details</legend> 
							  <form method="post" onsubmit="sethtml();return true;">
					     <table width="646" border="0" cellpadding="0" cellspacing="0">
					          <!--DWLayoutTable-->
					          <tr>
					               <td width="17" height="23">&nbsp;</td>
                   <td width="190">&nbsp;</td>
                   <td colspan="2" valign="top" class="error">
                        <? if(isset($errors)) echo $errors; ?>             </td>
                   <td width="93">&nbsp;</td>
                  </tr>
					         
							  
					               <tr>
					                    <td height="26">&nbsp;</td>
                    <td align="right" valign="middle">Keyword:&nbsp;&nbsp;</td>
                    <td colspan="2" valign="middle"><input name="keyword" type="text" class="input" id="keyword" value="<?= $keyword ?>" size="45" /></td>
                    <td>&nbsp;</td>
                   </tr>
				   <tr>
					                    <td height="26">&nbsp;</td>
                    <td align="right" valign="middle">Keyword Alias:&nbsp;&nbsp;</td>
                    <td colspan="2" valign="middle"><input name="keywordAlias" type="text" class="input" id="keywordAlias" value="<?= $keywordAlias ?>" size="45" /></td>
                    <td>&nbsp;</td>
                   </tr>
					               <tr>
					                    <td height="26">&nbsp;</td>
                    <td align="right" valign="middle">Category:&nbsp;&nbsp;</td>
                    <td colspan="2" valign="middle">
					<select name="categoryId" class="input" id="categoryId" style="width: 303px">
					<option value="0">-select category-</option>
                    <?= get_table_records('category', $categoryId) ?>
					</select>                    </td>
					
					
                    <td>&nbsp;</td>
                   </tr>
					               <tr>
					                    <td height="26">&nbsp;</td>
                    <td align="right" valign="middle">Language:&nbsp;&nbsp;</td>
                    <td colspan="2" valign="middle">
					
					<select name="languageId" class="input" id="languageId" style="width: 303px">
					<option value="0">-select language-</option>
								<?= get_table_records('language', $languageId) ?>
										</select></td>
                    <td>&nbsp;</td>
                   </tr>	
			   				   <tr>
					                    <td height="82"></td>
                    <td align="right" valign="middle">Optional words:&nbsp;&nbsp;</td>
                    <td colspan="2" valign="middle"><textarea name="optionalWords" class="input" id="optionalWords" 
							  style="width: 300px; height: 80px" "><?= $optionalWords ?></textarea></td>
                    <td></td>
                   </tr>
				  <!--tr>
					                    <td height="60">&nbsp;</td>
                    <td align="right" valign="middle">Alias(es):&nbsp;&nbsp;</td>
                    <td colspan="2" valign="middle"><textarea name="aliases" class="input" id="aliases" 
							  style="width: 300px; height: 50px"><?= $aliases ?></textarea>                    </td>
                    <td>&nbsp;</td>
                   </tr>
				  <tr>
					                    <td height="25"></td>
                    <td></td>
                    <td colspan="2" valign="top">(Separate multiple aliases with commas) </td>
                    <td></td>
                   </tr-->				   
				   <tr>
					                    <td height="82"></td>
                    <td align="right" valign="middle">Content:&nbsp;&nbsp;</td>
                    <td colspan="2" valign="middle"><textarea name="content" class="input" id="content" 
							  style="width: 300px; height: 80px" onkeyup="checklimit_kword(this, 160)"><?= $content ?></textarea>                           </td>
                    <td></td>
                   </tr>
				   <tr>
					                    <td height="82"></td>
                    <td align="right" valign="middle">Source Of Information:&nbsp;&nbsp;</td>
                    <td colspan="2" valign="middle"><textarea name="attribution" class="input" id="attribution" 
							  style="width: 300px; height: 80px" onkeyup="checklimit_kword(this, 160)"><?= $attribution ?></textarea>                           </td>
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
				<td height="30"></td>
                    <td>&nbsp;</td>
                    <td colspan="2" valign="middle">
					<input name="make" type="checkbox" id="make" onclick="sot(this.checked)" value="checkbox"<?= isset($make) ? ' checked="checked"' : '' ?> />
                       <span style="color: #CC0099">Make Keyword Outbound Trigger</span> </td>
                    <td></td>
                   </tr>
<? if($FEATURE_keyword_action == 1) { ?>
                                   <tr>
                                                            <td height="26"></td>
                    <td align="right" valign="middle">Quiz Action:&nbsp;&nbsp;</td>
                    <td colspan="2" valign="middle"><select name="quizAction_action"><option value="invalid">-- Select Action --</option><option value="remove"<? echo (!strcmp($quizAction_action, "remove") ? " selected" : ""); ?>>Remove</option><option value="add"<? echo (!strcmp($quizAction_action, "add") ? " selected" : ""); ?>>Add</option></select> from/to Quiz&nbsp;<select name="quizAction_quizId"><option value="0">-- Select Quiz --</option><? if(count($full_quiz_list)) {
        foreach($full_quiz_list as $this_quiz) {
                ?><option value="<? echo $this_quiz[id]; ?>"<? echo ($quizAction_quizId == $this_quiz[id] ? " selected" : ""); ?>><? echo $this_quiz[name]; ?></option><?
        }
} ?></select>
                    <td></td>
                   </tr>
<? } ?>
				 <tr>
				   <td></td>
                    <td align="right" id="tlabel" valign="top"></td>
                    <td colspan="2" id="options"><?= $thtml ?></td>
                    <td></td>
                </tr>	
				<td></td>
                    <td align="right"></td>
                    <td colspan="2" valign="middle" id="tnote"></td>
                    <td></td>
                   </tr>					   			   						  
				   						
					               <tr>
					                  <td height="35"></td>
					                  <td></td>
					                  <td colspan="2" valign="middle">
									     <input name="submit" type="submit" class="button" id="submit" value="Add Keyword" />
                                         <input name="mbtn" type="button"<?= !strlen($thtml) ? ' disabled="disabled"' : '' ?> class="button" id="mbtn" onclick="mopt()" value="Options (+)"/>
                                          <input name="lbtn" type="button"<?= !strlen($thtml) ? ' disabled="disabled"' : '' ?> class="button" id="lbtn" onclick="lopt()" value="Options (-)"/>
                                           <input name="cancel" type="submit" class="button" id="cancel" value="Cancel" />
                                           <input name="thtml" type="hidden" id="thtml" />
                                           <input name="opcount" type="hidden" id="opcount" />										   </td>
                    <td></td>
			                     </tr>
					               <tr>
					                  <td height="18"></td>
					                  <td></td>
					                  <td>&nbsp;</td>
					                  <td></td>
					                  <td></td>
			                     </tr>
					          </table>
							  </form>
                         </fieldset> </td>
                         <td>&nbsp;</td>
               </tr>
               <tr>
                    <td height="55">&nbsp;</td>
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
<? if(strlen($thtml)) print '<script>snote()</script>'; ?>

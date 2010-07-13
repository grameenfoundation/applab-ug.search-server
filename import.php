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
require 'excel/functions.php';

dbconnect();
validate_session(); 
check_admin_user();

if(isset($_POST["cancel"])) {
   goto('keywords.php');
}

if(count($_POST)) {
  $_POST = strip_form_data($_POST);
  extract($_POST);
}  

if(isset($_POST["submit"])) {
   if(!preg_match("/^[0-9]+$/", $start) || !$start) {
      $errors = 'Start of record in file not valid<br/>';
   }
   if(!$keyword_col) {
      $errors .= 'Keyword column required<br/>';
   }
   if(!$content_col) {
      $errors .= 'Content column required<br/>';
   }   
   if(!isset($errors)) {
      $ret = import_keywords();
	  if(!is_array($ret)) {
	      $errors = $ret.'<br/>';
	  } 
   }
   if(!isset($errors)) {
	 foreach($ret as $keyword) {
	 	if(unique_field_exists('keyword', $keyword['keyword'], 'keyword')) {
		   if($replace_duplicates != 1) {
			   $errors .= "Keyword \"$keyword[keyword]\" already exists!<br/>";
		   } else {
			   // Delete actual keyword
			   
			   $sql_q2 = "SELECT * FROM keyword WHERE keyword='".$keyword[keyword]."'";
			   if(!($result_q2 = mysql_query($sql_q2))) {
				$errors .= mysql_error()."<br/>";
			   } else {
				if(mysql_num_rows($result_q2)) {
					$row_q2 = mysql_fetch_array($result_q2);
					delete_keyword($row_q2[id]);
					/*
					if(!mysql_query("DELETE FROM otrigger WHERE keywordId='{$row_q2[id]}'")) {
						$errors .= mysql_error()." SQL: DELETE FROM otrigger WHERE keywordId='{$row_q2[id]}'<br/>";
					}
					if(!mysql_query("DELETE FROM subkeyword WHERE keywordId='{$row_q2[id]}'")) {
						$errors .= mysql_error()." SQL: DELETE FROM subkeyword WHERE keywordId='{$row_q2[id]}'<br/>";
					}
					if(!mysql_query("DELETE FROM keyword WHERE id='{$row_q2[id]}'")) {
                                                $errors .= mysql_error()." SQL: DELETE FROM keyword WHERE id='{$row_q2[id]}'";
					}*/
				}
			   }
		   }
		}
	 }
  }
  $count_not_updated = 0;
  if(!isset($errors)) { 
      $warnings = "";
      foreach($ret as $keyword) {
	     $dict_keyword = $keyword;
	     extract(escape_form_data($keyword));
			 
		 
		 $otrigger = ((strlen($trigger_keyword) > 0) && array_key_exists('trigger_keyword', $dict_keyword)) ? 1 : 0;
		 
		 
	     $sql = "INSERT INTO keyword(keyword, keywordAlias, optionalWords, createDate, content, categoryId, languageId, otrigger, attribution) 
		         VALUES ('$keyword', IF(LENGTH('$keywordAlias'), '$keywordAlias', NULL), IF(LENGTH('$optionalWords'), '$optionalWords', NULL), NOW(), '$content', IF(LENGTH('$categoryId'), '$categoryId', NULL), 
				 IF(LENGTH('$languageId'), '$languageId', NULL), $otrigger, IF(LENGTH('$attribution'), '$attribution', NULL))";

         $inserted = true;
		 if(!mysql_query($sql)) {
		    if(preg_match("/duplicate/i", mysql_error())) {
			    $warnings .= "WARNING: Keyword \"$keyword\" appears more than once in your uploaded file. 
			                  Only the first occurance has been entered.<br/>";
			    $count_not_updated++;
		    }
			$inserted = false;
	     }
		 if($inserted) {
		     $keywordId = mysql_insert_id();
				 keyword_enter_normalizations($keywordId, $keyword, $keywordAlias, $optionalWords);
		 }
		 if($otrigger && $inserted && $keywordId) {
		     $sql = "SELECT * FROM keyword WHERE keyword='{$dict_keyword[trigger_keyword]}'";
			 $result=execute_query($sql);
			 if(!mysql_num_rows($result)) {
			     $warnings .= "WARNING: $dict_keyword[trigger_keyword] not found. 
				 Could not set trigger for $dict_keyword[keyword]<br/>";
			 }
			 else {
			    $row=mysql_fetch_assoc($result);
				$triggers = array( array('number'=>1, 'tkeywordId'=>$row['id']) );
				$options = mysql_real_escape_string( serialize($triggers) );
				$sql = "INSERT INTO otrigger(createdate, keywordId, options) VALUES (NOW(), $keywordId, '$options')";
				execute_update($sql);
			 }
		 }
		 
		 
	  }
	  if(strlen($warnings)) {
		  $warnings = '<br/><br/><span style="color: #FF0000">'.$warnings.'</span>';
	  }
	  show_message('Keyword(s) Successfuly Imported', (count($ret) - $count_not_updated).' Keyword(s) have been successfuly imported in the system'.$warnings."<br/><br/><a href=\"keywords.php\">Back</a>", '#008800');
  }
}
if(isset($errors)) {
  $errors = "<br/>$errors<br/>";
}
if(!count($_POST)) {
   $start = 3;
   $keyword_col = 1;
   $content_col = 2;
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
          <td height="323" valign="top"><table width="100%" border="0" cellpadding="0" cellspacing="0" class="border">
               <!--DWLayoutTable-->
               <tr>
                    <td height="22" colspan="3" align="center" valign="middle" class="caption">Import   Keywords From File </td>
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
                  <td height="203">&nbsp;</td>
                  <td valign="top">
                            <fieldset>
                            <legend>Import From File</legend>
					     <table width="" border="0" cellpadding="0" cellspacing="0">
					          <!--DWLayoutTable-->
					          <tr>
					               <td width="22" height="23">&nbsp;</td>
                                   <td width="163">&nbsp;</td>
                                   <td width="30">&nbsp;</td>
                                   <td width="13">&nbsp;</td>
                                   <td width="374" valign="top" class="error">
                                      <? if(isset($errors)) echo $errors; ?>             </td>
                   <td width="44">&nbsp;</td>
                            </tr>
					          <form method="post" enctype="multipart/form-data">
					               <tr>
					                    <td height="30">&nbsp;</td>
                                        <td align="right" valign="middle">File</td>
                                        <td align="right" valign="top"><img src="images/excel.jpg" width="30" height="30" style="cursor: pointer" title="Browse Excel file containing the keywords"/></td>
                                      <td align="right" valign="middle">:&nbsp;&nbsp;</td>
                    <td valign="middle"><input type="file" name="file" size="40" style="font-size: 11px"/></td>
                    <td>&nbsp;</td>
                 </tr>
				  <tr>
					                    <td height="30">&nbsp;</td>
                                        <td colspan="3" align="right" valign="middle" nowrap="nowrap">Keyword Column:&nbsp;&nbsp; </td>
                           				<td valign="middle"><select name="keyword_col" class="input" id="keyword_col" style="width: 285px">
													<option value="0"></option>
													<?= get_excel_columns($keyword_col) ?>
																				</select>
									</td>
                    <td>&nbsp;</td>
                  </tr>
				  
				 
				  <!--tr>
					                    <td height="30">&nbsp;</td>
                                        <td colspan="3" align="right" valign="middle" nowrap="nowrap">Aliases Column:&nbsp;&nbsp; </td>
                           				<td valign="middle"><select name="aliases_col" class="input" id="aliases_col" style="width: 285px">
													<option value="0"></option>
													<?= get_excel_columns($aliases_col) ?>
																				</select>
									</td>
                    <td>&nbsp;</td>
                  </tr-->
				  <tr>
					                    <td height="30">&nbsp;</td>
                                        <td colspan="3" align="right" valign="middle" nowrap="nowrap">Content Column:&nbsp;&nbsp; </td>
                           				<td valign="middle"><select name="content_col" class="input" id="content_col" style="width: 285px">
													<option value="0"></option>
													<?= get_excel_columns($content_col) ?>
																				</select>
									</td>
                    <td>&nbsp;</td>
                  </tr>	
				   <tr>
					                    <td height="30">&nbsp;</td>
                                        <td colspan="3" align="right" valign="middle" nowrap="nowrap">Keyword Alias Column:&nbsp;&nbsp; </td>
                           				<td valign="middle"><select name="keywordAlias_col" class="input" id="keywordAlias_col" style="width: 285px">
													<option value="0"></option>
													<?= get_excel_columns($keywordAlias_col) ?>
																				</select>
									</td>
                    <td>&nbsp;</td>
                  </tr>
				  
				  <tr>
					                    <td height="30">&nbsp;</td>
                                        <td colspan="3" align="right" valign="middle" nowrap="nowrap">Category Column:&nbsp;&nbsp; </td>
                           				<td valign="middle"><select name="category_col" class="input" id="category_col" style="width: 285px">
													<option value="0"></option>
													<?= get_excel_columns($category_col) ?>
																				</select>
									</td>
                    <td>&nbsp;</td>
                  </tr>
					<tr>
					                    <td height="30">&nbsp;</td>
                                        <td colspan="3" align="right" valign="middle" nowrap="nowrap">Optional Words Column:&nbsp;&nbsp; </td>
                           				<td valign="middle"><select name="optionalWords_col" class="input" id="optionalWords_col" style="width: 285px">
													<option value="0"></option>
													<?= get_excel_columns($optionalWords_col) ?>
																				</select>
									</td>
                    <td>&nbsp;</td>
                  </tr>	
					<tr>
					                    <td height="30">&nbsp;</td>
                                        <td colspan="3" align="right" valign="middle" nowrap="nowrap">Source of Information:&nbsp;&nbsp; </td>
                           				<td valign="middle"><select name="attribution_col" class="input" id="attribution_col" style="width: 285px">
													<option value="0"></option>
													<?= get_excel_columns($optionalWords_col) ?>
																				</select>
									</td>
                    <td>&nbsp;</td>
                  </tr>	
									
				  <tr>
					                    <td height="30">&nbsp;</td>
                                        <td colspan="3" align="right" valign="middle" nowrap="nowrap">Language Column:&nbsp;&nbsp; </td>
                           				<td valign="middle"><select name="language_col" class="input" id="language_col" style="width: 285px">
													<option value="0"></option>
													<?= get_excel_columns($language_col) ?>
																				</select>
									</td>
                    <td>&nbsp;</td>
                  </tr>					  				  			  				  
				  <tr>
				     <td height="31">&nbsp;</td>
				     <td colspan="3" align="right" valign="middle">Start of Record:&nbsp;&nbsp; </td>
				     <td valign="middle"><input name="start" type="text" class="input" id="start" value="<?= $start ?>" size="41" maxlength="1" /></td>
				     <td>&nbsp;</td>
				     </tr>
                                  <tr>
                                     <td height="31">&nbsp;</td>
                                     <td colspan="3" align="right" valign="middle">Update existing keywords:&nbsp;&nbsp; </td>
                                     <td valign="middle"><input name="replace_duplicates" id="replace_duplicates" value="1" type="checkbox"<? echo (($replace_duplicates == 1) ? " checked" : "");?>>&nbsp;&nbsp;Check this box if you want keywords that are already in the system but are also in your file to be updated with the new content in your file.</td>
                                     <td>&nbsp;</td>
                                     </tr>
				  <tr>
				     <td height="35">&nbsp;</td>
				     <td>&nbsp;</td>
				     <td>&nbsp;</td>
				     <td>&nbsp;</td>
				     <td valign="middle">
						      <input name="submit" type="submit" class="button" id="submit" value="Import Keywords" />
                              <input name="cancel" type="submit" class="button" id="cancel" value="Cancel" /></td>
                    <td>&nbsp;</td>
				  </tr>
				  <tr>
				     <td height="20"></td>
				     <td></td>
				     <td></td>
				     <td></td>
				     <td></td>
				     <td></td>
				     </tr>
				  <tr>
				     <td height="47"></td>
				     <td colspan="4" valign="middle" id="note"><span style="color: #CC0000">Please Note:</span> Keywords can only be imported from Excel Files. The Excel file should contain atleast two (2) columns containing (i) The Keyword, (ii) Keyword Content </td>
				     <td></td>
				     </tr>
				  <tr>
				     <td height="25"></td>
				     <td>&nbsp;</td>
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
                  <td height="48">&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
               </tr>
               
          </table></td>
     </tr><tr>
          <td height="30" valign="top"><? include('bottom.php') ?></td>
     </tr>
</table>
</body>
</html>

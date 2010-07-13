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
require 'display.php';

dbconnect();
validate_session(); 
check_admin_user();

extract($_GET);

if(!preg_match("/^[0-9]+$/", $surveyId)) { 
   goto('surveys.php');
}

$sql = "SELECT survey.*, DATE_FORMAT(sendtime, '%d/%m/%Y %r') AS sendtime, 
        DATE_FORMAT(createdate, '%d/%m/%Y %r') AS createdate, updated FROM survey WHERE id=$surveyId";
$result = execute_query($sql);
if(!mysql_num_rows($result)) {
   goto('surveys.php');
}
$survey = mysql_fetch_assoc($result);

if(isset($_POST['cancel'])) {
   goto('surveys.php');
}
if(isset($_POST['add'])) {
   goto("addsqn.php?surveyId=$surveyId");
}
if(isset($_POST['edit'])) {
   goto("editsurvey.php?surveyId=$surveyId");
}
if(isset($_POST['replies'])) { 
   goto("sreplies.php?surveyId=$surveyId");
}
if(isset($_POST['delete'])) {
  delete_survey($surveyId);
  goto("surveys.php");
}
if(isset($_GET['delete'])) {
   delete_survey_question($surveyId, $question);
   goto("survey.php?surveyId=$survey[id]");
}
if(isset($_POST['order'])) {
   order_questions($surveyId);
   goto("survey.php?surveyId=$survey[id]");
}
extract($survey);
$questions = unserialize($questions); 
$smscounter = strlen($keyword);
foreach($questions as $qn) {
   $smscounter += strlen($qn['no']) + (1) + (1) + (1);
}
$noquestions = !is_array($questions) || !count($questions);

/* menu highlight */
$page = 'survey';
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
          <td height="420" valign="top"><table width="100%" border="0" cellpadding="0" cellspacing="0" class="border">
               <!--DWLayoutTable-->
               <tr>
                    <td height="22" colspan="6" align="center" valign="middle" class="caption">Survey Details </td>
                    </tr>
               <tr>
                    <td width="31" height="20">&nbsp;</td>
                    <td width="25">&nbsp;</td>
                    <td width="34">&nbsp;</td>
                    <td width="645">&nbsp;</td>
                    <td width="31">&nbsp;</td>
                    <td width="22">&nbsp;</td>
               </tr>
               <tr>
                    <td height="28">&nbsp;</td>
                    <td>&nbsp;</td>
                    <td valign="middle"><img src="images/quiz.gif" width="34" height="28" style="cursor: pointer" title="<?= $name ?>" /></td>
                    <td valign="middle">&nbsp;
					<span class="caption2">Name: </span>
					<?= $name ?> 
					- 
					<span style="color: #666666">created <?= $createdate ?>
					</span>					</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
               </tr>
               <tr>
                    <td height="26">&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
               </tr>
               <tr>
                 <td height="286">&nbsp;</td>
                 <td colspan="4" valign="top">
			          <fieldset>
					<legend>Details</legend>
					<table width="100%" border="0" cellpadding="0" cellspacing="0">
                         <!--DWLayoutTable-->
                         <tr>
                              <td width="21" height="38">&nbsp;</td>
                              <td width="31">&nbsp;</td>
                              <td width="41">&nbsp;</td>
                              <td width="133">&nbsp;							  </td>
                              <td width="454">&nbsp;</td>
                              <td width="36">&nbsp;</td>
                              <td width="17">&nbsp;</td>
                         </tr>
						 <form method="post">
                         <!--tr>
                            <td height="28">&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td align="right" valign="middle">Send Time:&nbsp;&nbsp;</td>
                              <td colspan="2" valign="middle"><?= $sendtime ?></td>
                            <td>&nbsp;</td>
                         </tr-->
                         
                         <tr>
                            <td height="28"></td>
                            <td></td>
                            <td></td>
                            <td align="right" valign="middle" id="label">Created:&nbsp;&nbsp;</td>
                            <td colspan="2" valign="middle" id="field"><?= $createdate ?></td>
                            <td></td>
                         </tr>
                        
                        <tr>
                           <td height="28"></td>
                           <td></td>
                           <td></td>
                           <td align="right" valign="middle" id="label">SMS Counter:&nbsp;&nbsp;</td>
                           <td colspan="2" valign="middle" class="field">
						   <?= $smscounter ?> Characters</td>
                           <td></td>
                        </tr>
                        <tr>
                           <td height="28"></td>
                           <td></td>
                           <td></td>
                           <td align="right" valign="middle" id="label">Keyword:&nbsp;&nbsp;</td>
                           <td colspan="2" valign="middle" class="caption3">
						   <?= $keyword ?>						   &nbsp;</td>
                           <td></td>
                        </tr>						
                        <tr>
                           <td height="28"></td>
                           <td></td>
                           <td></td>
                           <td align="right" valign="middle" id="label">Reply SMS:&nbsp;&nbsp;</td>
                           <td colspan="2" valign="middle" class="field">
						   <?= $reply ? '<div style="padding: 2px 0px 2px 0px">'.$reply.'</div>' : 'N/A' ?>	</td>
                           <td></td>
                        </tr>						
                        <tr>
                           <td height="28"></td>
                           <td></td>
                           <td></td>
                           <td align="right" valign="middle" id="label">Phone Numbers:&nbsp;&nbsp;</td>
                           <td colspan="2" valign="middle" class="caption3">
						   <?= get_item_count('surveyno', 'surveyId', $surveyId) ?>						   &nbsp;</td>
                           <td></td>
                        </tr>                        
                        <tr>
                           <td height="28"></td>
                           <td></td>
                           <td></td>
                           <td align="right" valign="middle" id="label">Updated:&nbsp;&nbsp;</td>
                           <td colspan="2" valign="middle" id="field">
						   <?= $updated ?>						   &nbsp;</td>
                           <td></td>
                        </tr>
                        <tr>
                           <td height="38">&nbsp;</td>
                           <td colspan="5" valign="middle">
						      <div style="padding: 10px 0px 10px 0px">
						     <?
							 if($noquestions) {
							    print '<div class="caption2" style="padding-left: 150px">[No Questions for this Survey]</div>';
							 }
							 else {
							   $html = '<table width="100%" border="0" cellspacing="0" cellpadding="3">
							    <tr id="title">
								   <td height="30" colspan="2">Question</td>
								   <td>Answer(s)</td>
								   <td>Open</td>
								</tr>'; 
							   $bgcolor = '#EEEEEE'; $i=1;
							   foreach($questions as $qn) { 
							      $bgcolor = $i++%2 ? 'aliceblue' : '#EEEEEE';
							      $html.= '
								   <tr bgcolor="'.$bgcolor.'">
								     <td valign="top" style="padding: 6px">
									    <select class="input" name="'.$qn['id'].'">'.qnlist(count($questions), $qn['no']).'</select>
									 </td>
									 <td height="20" style="padding: 6px" valign="top">'.
									 truncate_str($qn['question'], 50).'<br/><br/>
									   <a href="sanswers.php?surveyId='.$surveyId.'&question='.$qn['no'].'" 
									   style="font-size: 10px; color: #FF3300" title="Manage Answers">[Answers]</a> |
									   <a href="editsqn.php?surveyId='.$surveyId.'&question='.$qn['no'].'" 
									   style="font-size: 10px; color: #FF3300" title="Edit Question">[Edit]</a> |
									   <a href="?surveyId='.$surveyId.'&question='.$qn['no'].'&delete=TRUE" 
									   style="font-size: 10px; color: #FF0000" title="Delete Question" onclick="return confirm(\'Are you sure you want to delete this Question?\')">[Delete]</a>
									 </td>'; 
								  $answers = $qn['answers'];
								  $list = NULL;
								  foreach($answers as $answer) {
								     $list .= '<span style="color: #333333">
									 ('.$answer['no'].') '.truncate_str($answer['answer'], 40).'</span><br/>';
								  }
								  $html .= '<td valign="top" width="40%">'.(!is_null($list) ? $list : '<br/>'.NONE).'</td>
								  <td><input name="open'.preg_replace("/^q/", "", $qn['id']).'" type="checkbox"'.($qn['open']==1 ? ' checked="checked"' : '').' readonly="readonly"></td>
								  </tr>';
							   }
							   print $html.'</table>';
							 }
						   ?>
		                      </div></td>
                           <td>&nbsp;</td>
                        </tr>                        
                        <tr>
                           <td height="48">&nbsp;</td>
                           <td>&nbsp;</td>
                           <td colspan="3" valign="middle">
							  <input name="order" type="submit" class="button" id="order" value="Update" /> 
                              <input name="add" type="submit" class="button" id="add" value="Add Question"/>
                               <input name="edit" type="submit" class="button" id="edit" value="Edit Survey"/>
                              <input name="replies" type="submit" class="button" id="replies" value="Results"/> 
                              <input name="delete" type="submit" class="button" id="delete" value="Delete" 
							   onclick="return confirm('Are you sure you want to delete this survey?')"/>
                              <input name="cancel" type="submit" class="button" id="cancel" value="&laquo; Survey List"/>				                 </td>
                             <td>&nbsp;</td>
                           <td>&nbsp;</td>
                        </tr>
						</form>
                        <tr>
                          <td height="35">&nbsp;</td>
                          <td>&nbsp;</td>
                          <td>&nbsp;</td>
                          <td>&nbsp;</td>
                          <td>&nbsp;</td>
                          <td>&nbsp;</td>
                          <td>&nbsp;</td>
                        </tr>
                    </table>
                 </fieldset></td>
                    <td>&nbsp;</td>
               </tr>
               <tr>
                 <td height="36">&nbsp;</td>
                 <td>&nbsp;</td>
                 <td>&nbsp;</td>
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

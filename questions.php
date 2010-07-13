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

if(!preg_match("/^[0-9]+$/", $quizId)) { 
   goto('quiz.php');
}

if(isset($delete)) { 
   if(!preg_match("/^[0-9]+$/", $questionId)) {
       goto("quiz.php");
   }
   delete_question($questionId); 
   goto("questions.php?quizId=$quizId");	   
}

if(isset($statusMessagePrint)) {
	$title_text_status='<tr class="title1"><td colspan="6"><font color="red">'.$statusMessagePrint.'</font></td></tr><tr><td colspan="6">&nbsp;</td></tr>';
}

if(isset($requeue)) {
	$sql = "DELETE FROM qndelivery WHERE questionId IN (SELECT id FROM question WHERE quizId='".$quizId."')";
	if(!mysql_query($sql)) {
		$statusMessagePrint = "Could not requeue. Error: ".mysql_error();
	} else {
		$statusMessagePrint = "Successfully requeued all questions in this quiz. The questions shall be re-sent to all numbers at the stipulated times.";
	}
	goto("questions.php?quizId=$quizId&statusMessagePrint=".urlencode($statusMessagePrint)."");
}

$quiz = get_quiz_from_id($quizId);
if(empty($quiz)) {
   goto('quiz.php');
}

$sql = "SELECT question.*, DATE_FORMAT(sendTime, '%d/%m/%Y %T') AS sendTime, 
        DATE_FORMAT(updated, '%d/%m/%Y %T') AS updated FROM question WHERE quizId=$quizId";
$result = execute_query($sql);
$total = mysql_num_rows($result);

/* menu highlight */
$page = 'quiz';
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
          <td height="399" valign="top"><table width="100%" border="0" cellpadding="0" cellspacing="0" class="border">
               <!--DWLayoutTable-->
               <tr>
                    <td height="22" colspan="4" align="center" valign="middle" class="caption">Quiz Questions - Total: <?= $total ?> </td>
                    </tr>
               <tr>
                    <td width="9" height="20">&nbsp;</td>
                    <td width="34">&nbsp;</td>
                    <td width="735">&nbsp;</td>
                    <td width="10">&nbsp;</td>
               </tr>
               <tr>
                    <td height="28"></td>
                    <td valign="middle"><img src="images/quiz.gif" width="34" height="28" style="cursor: pointer" title="<?= $quiz['name'] ?>"/></td>
                    <td valign="middle">&nbsp;
					<span class="caption2">Quiz:</span> 
					<?= truncate_str($quiz['name'], 50) ?> <?= $quiz['singleKeyword'] ? ' <span style="color: #FF33CC">(Single Keyword)</span>' : '' ?>
					- 
					<span style="color: #666666">created <?= $quiz['createDate'] ?>
					</span>					</td>
                    <td></td>
               </tr>
               <tr>
                    <td height="14"></td>
                    <td></td>
                    <td></td>
                    <td></td>
               </tr>
               <tr>
                  <td height="293"></td>
                  <td colspan="2" valign="top">
				   <?  
					  $html = '
					  <table width="100%" border="0" cellpadding="0" cellspacing="0">'.$title_text_status.'
					    <tr class="title1">
						   <td height="30" valign="top" colspan="2"><u>Question</u></td>
						   <td valign="top"><u>Keyword</u></td>
						   <td valign="top"><u>Replies</u></td>
						   <td valign="top"><u>Send Time</u></td>
						   <td valign="top"><u>Options</u></td>
					    </tr>';
					  $color = '#EEEEEE'; $i=0;
					  while($row=mysql_fetch_assoc($result)) {
					      $color = $i++%2 ? '#FFFFFF' : '#EEEEEE';
					      $question = $row['question'];
						  if(strlen($question) > 30) {
						      $question = substr($question, 0, 30).'..';
						  }
						  $keyword = ($quiz['singleKeyword'] ? $quiz['keyword'] : $row['keyword']);
						  if(strlen($keyword) > 19) {
						      $keyword = substr($keyword, 0, 19).'..';
						  }		
						  /* replies */
						  $sql = "SELECT COUNT(*) FROM quizreply WHERE questionId=$row[id]";
						  $myres2 = execute_query($sql, 0);
						  $myrow2 = mysql_fetch_row($myres2);
						  //$replies = mysql_num_rows(execute_query($sql, 0));					  		  
						  $replies = $myrow2[0];
						  $html .='
						  <tr bgcolor="'.$color.'" onmouseover="this.style.backgroundColor=\''.HOVERCOLOR.'\'"
	                          onmouseout="this.style.backgroundColor=\''.$color.'\'">
						     
							 <td height="28" valign="middle" width="30" style="padding-left: 2px">
							    <a href="question.php?questionId='.$row['id'].'">
								<img src="images/qn2.gif" style="cursor: pointer" title="'.$row['question'].'" border="0" />
								</a>
							 </td>
							 <td>
							  <a href="question.php?questionId='.$row['id'].'" style="color: #000" title="Click To Open Question">
							 '.$question.'</a>
							 </td>
							 <td class="caption4">'.(strlen($keyword) ? $keyword : '-').'</td>
							 <td style="padding-left: 10px" class="caption4">'.$replies.'</td>
							 <td>'.$row['sendTime'].'</td>
							 <td>
							    <a href="replies.php?questionId='.$row['id'].'">Replies</a> |
								<a href="editquestion.php?questionId='.$row['id'].'">Edit</a> |
								<a href="?quizId='.$quizId.'&questionId='.$row['id'].'&delete=TRUE" style="color: #FF0000"
								onclick="return confirm(\'Are you sure you want to delete this Question?\')">Delete</span>
							 </td>
						  </tr>';
					  }
					  $html .='
					  <tr>
					     <td colspan="6" height="40">
						    <input type="button" class="button" value="Add Question" 
							onclick="location.replace(\'addquestion.php?quizId='.$quizId.'\')" />
						    <input type="button" class="button" value="Phone Numbers" 
							onclick="location.replace(\'numbers.php?quizId='.$quizId.'\')" '.(!$total ? 'disabled="disabled"' : '').'/>	
						    <input type="button" class="button" value="Edit Quiz" 
							onclick="location.replace(\'editquiz.php?quizId='.$quizId.'\')" />							
							<input type="button" class="button" value="&laquo; Quiz Items" onclick="location.replace(\'quiz.php\') "/>
							<input type="button" class="button" value="Requeue" onclick="var response= confirm(\'Are you sure? Carrying out this operation shall result in the questions being re-sent to ALL phone numbers registered to this quiz. This may result in duplicate delivery of the questions to certain phone numbers, if they had already received the questions. Remember that you need to have adjusted the sending time for each question to the time when you wish the question to be delivered before using the Requeue function.\'); if(response==true) { location.replace(\'questions.php?quizId='.$quizId.'&requeue=1\'); return true; } else { return false; } " />
						 </td>
					  </tr>';
					  echo $html.'</table>';
					?>					</td>
                    <td></td>
               </tr>
               <tr>
                  <td height="20"></td>
                  <td>&nbsp;</td>
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

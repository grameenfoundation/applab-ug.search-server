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

if(!preg_match("/^[0-9]+$/", $questionId)) { 
   goto('quiz.php');
}

$sql = "SELECT quizId, keyword, question, correctReply, wrongReply, DATE_FORMAT(sendTime, '%d/%m/%Y %r') AS sendTime, 
        DATE_FORMAT(createTime, '%d/%m/%Y %r') AS createTime, updated FROM question WHERE id=$questionId";
$result = execute_query($sql);
if(!mysql_num_rows($result)) {
   goto('quiz.php');
}
$question = mysql_fetch_assoc($result);
$quiz = get_quiz_from_id($question['quizId']);

if(isset($_POST['cancel'])) {
   goto("questions.php?quizId=$quiz[id]");
}
if(isset($_POST['edit'])) {
   goto("editquestion.php?questionId=$questionId");
}
if(isset($_POST['answers'])) {
   goto("answers.php?questionId=$questionId");
}
if(isset($_POST['replies'])) {
   goto("replies.php?questionId=$questionId");
}
if(isset($_POST['delete'])) {
  delete_question($questionId);
  goto("questions.php?quizId=$quiz[id]");
}

/* total answers */
$sql = "SELECT * FROM answer WHERE questionId=$questionId";
$result = execute_query($sql);
$answers = mysql_num_rows(execute_query($sql));

/* total replies */
$sql = "SELECT * FROM quizreply WHERE questionId=$questionId";
$result = execute_query($sql);
$replies = mysql_num_rows(execute_query($sql));

/* menu highlight */
$page = 'quiz';
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
          <td height="421" valign="top"><table width="100%" border="0" cellpadding="0" cellspacing="0" class="border">
               <!--DWLayoutTable-->
               <tr>
                    <td height="22" colspan="4" align="center" valign="middle" class="caption">Quiz Question</td>
                    </tr>
               <tr>
                    <td width="56" height="20">&nbsp;</td>
                    <td width="34">&nbsp;</td>
                    <td width="645">&nbsp;</td>
                    <td width="53">&nbsp;</td>
                    </tr>
               <tr>
                    <td height="28">&nbsp;</td>
                    <td valign="middle"><img src="images/quiz.gif" width="34" height="28" style="cursor: pointer" title="<?= $quiz['name'] ?>" /></td>
                    <td valign="middle">&nbsp;
					<span class="caption2">Quiz: </span>
					<?= truncate_str($quiz['name'], 50) ?> <?= $quiz['singleKeyword'] ? ' <span style="color: #FF33CC">(Single Keyword)</span>' : '' ?>
					- 
					<span style="color: #666666">created <?= $quiz['createDate'] ?>
					</span>					</td>
                    <td>&nbsp;</td>
               </tr>
               <tr>
                    <td height="26">&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
               </tr>
               
               <tr>
                    <td height="341">&nbsp;</td>
                    <td colspan="2" valign="top">
					     <fieldset>
					<legend>Question Details</legend>
					<table width="100%" border="0" cellpadding="0" cellspacing="0" 
					style="background-image: none; background-repeat: no-repeat; background-position: center">
                         <!--DWLayoutTable-->
                         <tr>
                              <td width="55" height="24">&nbsp;</td>
                              <td colspan="3" valign="top">
							  </td>
                              <td width="36">&nbsp;</td>
                              <td width="59">&nbsp;</td>
                         </tr>
                         <tr>
                              <td height="35">&nbsp;</td>
                              <td width="36">&nbsp;</td>
                              <td width="129" align="right" valign="middle">
					<img src="images/qn2.gif" width="22" height="22" style="cursor: pointer" title="<?= $question['question'] ?> "/>&nbsp;&nbsp;							  </td>
                              <td width="362" valign="middle" class="field"><?= $question['question'] ?>                                   &nbsp;</td>
                              <td>&nbsp;</td>
                              <td>&nbsp;</td>
                         </tr>
                         <tr>
                              <td height="35">&nbsp;</td>
                              <td>&nbsp;</td>
                              <td align="right" valign="middle" class="label">Send Time:&nbsp;&nbsp;</td>
                              <td valign="middle" class="field"><?= $question['sendTime'] ?></td>
                              <td>&nbsp;</td>
                              <td>&nbsp;</td>
                         </tr>
                        <tr>
                              <td height="35">&nbsp;</td>
                              <td>&nbsp;</td>
                              <td align="right" valign="middle" class="label">Create Time:&nbsp;&nbsp;</td>
                              <td valign="middle" class="field"><?= $question['createTime'] ?></td>
                              <td>&nbsp;</td>
                              <td>&nbsp;</td>
                        </tr>
                        <tr>
                              <td height="35">&nbsp;</td>
                              <td>&nbsp;</td>
                              <td align="right" valign="middle" class="label">Keyword:&nbsp;&nbsp;</td>
                              <td valign="middle" class="field">
							  <?= $question['keyword'] ? ($quiz['singleKeyword'] ? $quiz['keyword'] : $row['keyword']) : NONE ?>&nbsp;</td>
                              <td>&nbsp;</td>
                              <td>&nbsp;</td>
                        </tr>
                        <tr>
                              <td height="35">&nbsp;</td>
                              <td>&nbsp;</td>
                              <td align="right" valign="middle" class="label">Total Answers:&nbsp;&nbsp;</td>
                              <td valign="middle" class="field">
							  <?= $answers ?> Answer(s)</td>
                              <td>&nbsp;</td>
                              <td>&nbsp;</td>
                        </tr>	
                        <tr>
                              <td height="35">&nbsp;</td>
                              <td>&nbsp;</td>
                              <td align="right" valign="middle" class="label">Total Replies:&nbsp;&nbsp;</td>
                              <td valign="middle" class="field">
							  <?= $replies ?></td>
                              <td>&nbsp;</td>
                              <td>&nbsp;</td>
                        </tr>													
                                <? if($QuizReal == 1) { ?>
                        <tr>
                              <td height="35">&nbsp;</td>
                              <td>&nbsp;</td>
                              <td align="right" valign="middle">Correct Reply:&nbsp;&nbsp;</td>
                              <td valign="middle" style="color: #008800">
							  <?= $question['correctReply'] ? $question['correctReply'] : NONE ?>&nbsp;</td>
                              <td>&nbsp;</td>
                              <td>&nbsp;</td>
                        </tr>
                        <tr>
                              <td height="28">&nbsp;</td>
                              <td>&nbsp;</td>
                              <td align="right" valign="middle">Wrong Reply:&nbsp;&nbsp;</td>
                              <td valign="middle" style="color: #666666">
							  <?= $question['wrongReply'] ? $question['wrongReply'] : NONE ?>&nbsp;</td>
                              <td>&nbsp;</td>
                              <td>&nbsp;</td>
                        </tr>	
                                <? } ?>
                        <tr>
                              <td height="35">&nbsp;</td>
                              <td>&nbsp;</td>
                              <td align="right" valign="middle" class="label">Updated:&nbsp;&nbsp;</td>
                              <td valign="middle" class="field">
							  <?= $question['updated'] ? $question['updated'] : NONE ?>&nbsp;</td>
                              <td>&nbsp;</td>
                              <td>&nbsp;</td>
                        </tr>						 					 						 					 						 
                        <form method="post" onsubmit="return !page.locked">
				      <tr>
                              <td height="48">&nbsp;</td>
                              <td colspan="4" valign="middle">
							  <input name="preview" type="button" class="button" id="preview" value="Preview  SMS" 
							  onclick="previewm(<?= $questionId ?>, <?= $quiz['sendall'] ?>)"/> 
                              <input name="edit" type="submit" class="button" id="edit" value="Edit Question"/>
                              <input name="answers" type="submit" class="button" id="answers" value="Answers"/>
                               <input name="replies" type="submit" class="button" id="replies" value="Replies"/> 
                               <input name="delete" type="submit" class="button" id="delete" value="Delete" 
							   onclick="return confirm('Are you sure?')"/>
                               <input name="cancel" type="submit" class="button" id="cancel" value="&laquo; Quiz Questions"/>						     </td>
                             <td>&nbsp;</td>
				      </tr>
				      <tr>
				         <td height="16">&nbsp;</td>
				         <td>&nbsp;</td>
				         <td>&nbsp;</td>
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
                    <td height="36">&nbsp;</td>
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

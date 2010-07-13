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
   goto("question.php?questionId=$questionId");
}

if(isset($_POST['delete'])) {
  foreach($_POST as $key=>$val) {
     if(preg_match("/^p[0-9]+$/", $key)) {
	     $sql = "DELETE FROM answer WHERE id=$val";
		 execute_nonquery($sql);
	 }
  }
  goto("answers.php?questionId=$questionId");
}
if(isset($_POST['update'])) {
  foreach($_POST as $key=>$val) {
     if(preg_match("/^answer[0-9]+$/", $key)) {
	     $id = preg_replace("/answer/", "", $key);
		 if($QuizReal == 1) {
		 	$correct = $_POST["correct$id"];
		 } else {
		 	$correct = 1;
		 }
		 $sql = "UPDATE answer SET no=$val, correct=$correct WHERE id=$id LIMIT 1";
		 execute_nonquery($sql);
		 //print "$sql<br/>";
	 }
  }
  goto("answers.php?questionId=$questionId");
}

$sql = "SELECT * FROM answer WHERE questionId=$questionId ORDER BY no";
$result = execute_query($sql);
$total = mysql_num_rows($result);
if(!$total) {
   goto("editquestion.php?questionId=$questionId");
}

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
          <td height="390" valign="top"><table width="100%" border="0" cellpadding="0" cellspacing="0" class="border">
               <!--DWLayoutTable-->
               <tr>
                    <td height="22" colspan="4" align="center" valign="middle" class="caption">Question Answers </td>
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
					<?= $quiz['name'] ?> 
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
                  <td height="258">&nbsp;</td>
                  <td colspan="2" valign="top">
				       <fieldset>
					<legend>Arrange / Remove Answers</legend>
					<table width="100%" border="0" cellpadding="0" cellspacing="0" 
					style="background-image: none; background-repeat: no-repeat; background-position: center">
                         <!--DWLayoutTable-->
                         <tr>
                              <td width="91" height="24">&nbsp;</td>
                              <td width="50">&nbsp;</td>
                              <td width="60">&nbsp;</td>
                              <td width="381">&nbsp;</td>
                              <td width="95">&nbsp;</td>
                         </tr>
                         <tr>
                              <td height="35">&nbsp;</td>
                              <td colspan="2" align="right" valign="middle">
					<img src="images/qn2.gif" width="22" height="22" style="cursor: pointer" title="<?= $question['question'] ?> "/>&nbsp;&nbsp;							  </td>
                              <td valign="middle"><?= $question['question'] ?>                                 &nbsp;</td>
                              <td>&nbsp;</td>
                         </tr>
                        <form method="post">
						 <tr>
                              <td height="107">&nbsp;</td>
                              <td>&nbsp;</td>
                              <td>&nbsp;</td>
                              <td valign="top">
							  <?
							   
							   if(!mysql_num_rows($result)) {
							      print 'No Answers for this Question';
							   } 
							   else {
							      $list = NULL;
								  $html = '<table border="0">
								  <tr class="caption2">
								     <td height="30"><u>No.</u></td>
									 <td colspan="2"><u>Answer</u></td>
									 <td width="15"></td>
									 <td>'.($QuizReal != 1 ? "<!--u>Correct</u-->" : "<u>Correct</u>").'</td>
								  </tr>';
								  $i=1;
								  while($row=mysql_fetch_assoc($result)) {
								    $list .= "p$row[id],";
									$options = NULL;
									for($i=1; $i<=$total; $i++) {
									   $options .= '<option value="'.$i.'" '.($row['no']==$i ? 'selected="selected"' : '').'>'.$i.'</option>';
									}
								    $html .= '<tr>
									  <td height="23">
									     <select class="input" style="width: 50px" name="answer'.$row['id'].'">'.$options.'</select>
									  </td>
									  <td>
									     <input type="checkbox" name="p'.$row['id'].'" id="p'.$row['id'].'" value="'.$row['id'].'" />
									  </td>
									  <td>'.$row['answer'].'</td>
									  <td width="15"></td>
									  <td>
									     '.($QuizReal == 1 ? '<input type="radio" name="correct'.$row['id'].'" value="1" '.($row['correct'] ? 'checked="checked"' : '').'>Yes&nbsp;&nbsp;&nbsp;
										 <input type="radio" name="correct'.$row['id'].'" value="0" '.(!$row['correct'] ? 'checked="checked"' : '').'>No' : '').'
										</td>
									</tr>';
								  }
								  echo $html.'</table><br/><input type="hidden" id="list" value="'.preg_replace("/,$/", "", $list).'" />
								  <table border="0">
								      <tr>
									    <td valign="top" style="color: #FF3300">IMPORTANT:&nbsp;</td>
										<td style="font-size: 10px">The Number Assigned to each answer will be used in the reply SMS to 
										this Question
								        </td>
								    </tr>
								  </table>';
							   }
							  ?>							  
							  </td>
                              <td>&nbsp;</td>
                         </tr>
                         
						 <tr>
                            <td height="48">&nbsp;</td>
                            <td>&nbsp;</td>        
                            <td colspan="2" valign="middle">
							<input name="select" type="button" class="button" id="select" value="Select All" onclick="selectall(true)"/>
							<input name="update" type="submit" class="button" id="update" value="Update" />
							<input name="delete" type="submit" class="button" id="delete" value="Delete" 
							   onclick="return confirm('Are you sure?')"/>
                               <input name="cancel" type="submit" class="button" id="cancel" value="&laquo; Go To Question"/>						                        </td>        
                            <td>&nbsp;</td>
                        </tr>
						 <tr>
						    <td height="29">&nbsp;</td>
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
                  <td height="34">&nbsp;</td>
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

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

$quiz = get_quiz_from_id($quizId);
if(empty($quiz)) {
   goto('quiz.php');
}

if(count($_POST)) {
   $_POST = strip_form_data($_POST);
   extract($_POST);
}
if(isset($_POST['cancel'])) {
  goto("questions.php?quizId=$quizId");
}
if(isset($_POST['submit'])) {
  if(strlen($question) < 3) {
     $errors = 'Question not valid<br/>';
  }
  if(!$quiz['sendall']) {
     if(!strlen($date)) {
        $errors .= 'Question send date not valid<br/>';
     }
     if(!preg_match("/^[0-9]+$/", $min)) {
        $errors .= 'Question send time not valid<br/>';
     }
     else {
	      /* check that send time is not in the past */
	      $date = preg_split("/\//", $date);
	      $d = array_shift($date);
	      $m = array_shift($date);
	      $y = array_shift($date);
	      if(mktime($hr, $min, 0, $m, $d, $y) < time()) {
	         $errors .= "Time for sending the Question is in the past<br/>";
	      } 
	      else {
	         $sendTime = "$y-$m-$d $hr:$min:0";
	      } 
     } 
  }
  else {
	 /* get send time */
     $result = execute_query("SELECT * FROM question WHERE quizId=$quizId LIMIT 1");
     if(mysql_num_rows($result)) {
      $row = mysql_fetch_assoc($result);
	    $sendTime = $row['sendTime'];
     }
	 else {
	     $sendTime = '0000-00-00 00:00:00';
	 }
  }
  if(strlen($keyword) && !preg_match("/^[a-z0-9.-_]{2,}$/i", $keyword)) {
      $errors .= 'Keyword not valid<br/>';
  }  
  /* validate answers */
  $answers = array();
  foreach($_POST as $key=>$val) {
      if(!preg_match("/^ans[0-9]+$/", $key) || !strlen($val)) {
	     continue;
	  }
	  $no = preg_replace("/^ans/", "", $key);
	  if(!strlen($val)) continue;
	  if($QuizReal == 1) {
		$correct = $_POST['ans'.$no.'correct'];
	  } else {
		$correct = 1;
	  }
	  $id = $_POST['ans'.$no.'id'];
	  $answer = array('answer' => $val, 'correct' => $correct); 
	  //print_r($answer); exit;
	  $answers[] = $answer;
  } 
  if(isset($use_keyword)) { 
      execute_nonquery("UPDATE question SET keyword=NULL WHERE id=$use_keyword LIMIT 1");
  }
  if(!isset($errors)) { 
     /* check if Keyword not already in use */
	 if(!isset($use_keyword) && strlen($keyword)) { 
	    $sql="SELECT * FROM question WHERE keyword='$keyword'"; 
	    $result = execute_query($sql);
		if(mysql_num_rows($result)) {
		   $row = mysql_fetch_assoc($result);
		   $existing_quiz = get_quiz_from_id($row['quizId']);
		   $warning = '
		     <div style="padding-top: 20px"><img src="images/warning.gif" /></div>
             <div>
			  The Keyword <span style="color: #FF3300; font-weight: bold">'.$keyword.'</span> is already associated with a question:
			 </div>
		     <div style="padding: 10px 0px 10px 0px"><strong>Question:</strong>
			    <span style="color: #666666">'.$row['question'].'</span></div>
			 <div style="padding-bottom: 10px">
			   <strong>From quiz:</strong>
			      <span style="color: #666666">'.$existing_quiz['name'].' - </span>
				  <span style="font-size: 10px;color: #666666">created '.$existing_quiz['createDate'].'</span>. 
			  </div>
			  <div style="padding-bottom: 15px">
			   <input type="checkbox" name="use_keyword" value="'.$row['id'].'"/> 
			   Check this box to use this Keyword and remove it from the Existing Question.<br/><br/>
			   <span style="color: #CC0000">Note: </span>The Old question will remain with NO Keyword!
		     </div>';
		}
	 }
  }
  if(!isset($errors) && !isset($warning)) { 
     /* update question */
	 if($quiz['singleKeyword']) {
	     $questionNo = get_next_question_no($quizId);
	 }
	 extract(escape_form_data($_POST));
	 $sql = "INSERT INTO question (quizId, createDate, createTime, sendTime, question, keyword, correctReply, wrongReply, no) 
	         VALUES($quizId, CURRENT_DATE(), NOW(), '$sendTime', '$question', IF(LENGTH('$keyword'), '$keyword', NULL), 
			 IF(LENGTH('$correctReply'),'$correctReply', NULL), IF(LENGTH('$wrongReply'), '$wrongReply', NULL), 
			 IF($quiz[singleKeyword], '$questionNo', NULL))";
			  
	 lock_system();
	 execute_nonquery($sql);
	 $questionId = mysql_insert_id();
	 unlock_system();
	 if(!strlen($keyword)) {
	    $keyword = $quiz['singleKeyword'] ? $quiz['keyword'].'_'.$questionNo : 'Q'.$quizId.$questionId;
		execute_nonquery("UPDATE question SET keyword='$keyword' WHERE id=$questionId");
	 }
	 /* update answers */
	 $no = 1;
	 foreach($answers as $answer) {
	    $ans = mysql_real_escape_string($answer['answer']);
		$sql = "INSERT INTO answer(questionId, answer, no, correct) VALUES($questionId, '$ans', $no, $answer[correct])";
		
		execute_nonquery($sql);
		$aid = mysql_insert_id();
		execute_nonquery("UPDATE answer SET no=$no WHERE id=$aid");
		$no++;
	 }        
	 goto("question.php?questionId=$questionId");
  }
} 

if(isset($errors)) {
  $errors = "<br/>$errors<br/>";
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
<link rel="stylesheet" type="text/css" href="styles/date.css" />
<script type="text/javascript" src="date.js"></script>
<script type="text/javascript" src="basic.js"></script>
<script type="text/javascript">
<?
$js = "var moreans= {\n";
for($i=1; $i<=$moreans; $i++) {
   $js .= "\tans$i: {showing: ".(isset($_POST['ans'.($i+3)]) ? 'true' : 'false')."},\n";
}
$js = preg_replace("/,$/", "", $js);
$js .= "}\n";
print $js;

?>
</script>
</head>

<body>
<table width="790" border="0" align="center" cellpadding="0" cellspacing="0" class="main">
     <!--DWLayoutTable-->
     <tr>
          <td width="790" height="124" valign="top"><? include('top.php') ?></td>
     </tr>
     <tr>
       <td height="472" valign="top">
	      <table width="100%" border="0" cellpadding="0" cellspacing="0" class="border">
              <!--DWLayoutTable-->
              <tr>
                   <td height="22" colspan="4" align="center" valign="middle" class="caption">Add Question to  quiz</td>
             </tr>
              <tr>
                   <td width="56" height="19">&nbsp;</td>
                   <td width="172">&nbsp;</td>
                   <td width="507">&nbsp;</td>
                   <td width="53">&nbsp;</td>
                   </tr>
              <tr>
                 <td height="28">&nbsp;</td>
                 <td colspan="2" valign="top"><table width="679" border="0" cellpadding="0" cellspacing="0">
                    <!--DWLayoutTable-->
                    <tr>
                       <td width="34" height="28" valign="middle"><img src="images/quiz.gif" width="34" height="28" style="cursor: pointer" title="<?= $quiz['name'] ?>" /></td>
                       <td width="645" valign="middle">&nbsp; <span class="caption2">quiz: </span>
                          <?= truncate_str($quiz['name'], 50) ?> <?= $quiz['singleKeyword'] ? ' <span style="color: #FF33CC">(Single Keyword)</span>' : '' ?>
- <span style="color: #666666">created
<?= $quiz['createDate'] ?>
</span></td>
                    </tr>                     
               </table></td>
                 <td>&nbsp;</td>
              </tr>
              <tr>
                 <td height="27">&nbsp;</td>
                 <td>&nbsp;</td>
                 <td valign="middle" class="error"><?= $errors ?></td>
                 <td>&nbsp;</td>
              </tr>              
              <tr>
                   <td height="351">&nbsp;</td>
                <td colspan="2" valign="top">
			       <form method="post">
					 <fieldset>
					<legend>Question Details</legend>
					<table width="677" border="0" cellpadding="0" cellspacing="0" 
					style="background-image: none; background-repeat: no-repeat; background-position: center">
					   <!--DWLayoutTable-->
                         
                         <tr>
                              <td width="43" height="33">&nbsp;</td>
                              <td width="75">&nbsp;</td>
                              <td width="50">&nbsp;</td>
                              <td colspan="5" valign="middle"><?= $warning ?></td>
                              <td width="28">&nbsp;</td>
                         </tr>
						 
                         <tr>
                              <td height="35">&nbsp;</td>
                              <td colspan="2" align="right" valign="middle">Question:&nbsp;&nbsp;							  </td>
                              <td colspan="5" valign="middle">
							  <input name="question" type="text" class="input" id="question" value="<?= $question ?>" size="45" />
                              &nbsp;</td>
                              <td>&nbsp;</td>
                         </tr>
                         <tr <?= $quiz['sendall'] ? 'style="display: none"' : '' ?>>
                              <td height="35">&nbsp;</td>
                              <td colspan="2" align="right" valign="middle">Send Time:&nbsp;&nbsp;</td>
                            <td colspan="5" valign="middle"><input name="date" type="text" class="input" id="date" style="cursor: pointer" title="Select Date" 
							  onclick="displayDatePicker('date')" value="<?= $_POST['date'] ?>" size="15" readonly="true"/>
                               <select name="hr" class="input" id="hr">
                                  <?= hours($hr) ?>
                                                                                                                            </select>
                               <input name="min" type="text" class="input" id="min" value="<?= $min ? $min : '00' ?>" size="4" />
                            HRS</td>
                            <td>&nbsp;</td>
                         </tr>
                        <tr <?= $quiz['singleKeyword'] ? 'style="display: none"' : '' ?>>
                              <td height="35">&nbsp;</td>
                              <td colspan="2" align="right" valign="middle">Keyword:&nbsp;&nbsp;</td>
                           <td colspan="5" valign="middle">
						   <input name="keyword" type="text" class="input" id="keyword" value="<?= $keyword ?>" size="45" /></td>
                              <td>&nbsp;</td>
                        </tr>	
			<? if($QuizReal == 1) { ?>
                        <tr>
                              <td height="65">&nbsp;</td>
                              <td colspan="2" align="right" valign="middle" style="color: #008800">Correct Reply:&nbsp;&nbsp;</td>
                              <td colspan="5" valign="middle">
							     <textarea name="correctReply" class="input" id="correctReply" 
							  style="width: 300px; height: 50px"><?= $correctReply ?></textarea>							                                   </td>
                              <td>&nbsp;</td>
                        </tr>
                        <tr>
                              <td height="65">&nbsp;</td>
                              <td colspan="2" align="right" valign="middle" style="color: #CC0000">Wrong Reply:&nbsp;&nbsp;</td>
                              <td colspan="5" valign="middle">
							     <textarea name="wrongReply" class="input" id="wrongReply" 
							  style="width: 300px; height: 50px"><?= $wrongReply ?></textarea>							                                  </td>
                              <td>&nbsp;</td>
                        </tr>						
			<? } ?>
                        <tr>
                              <td height="28">&nbsp;</td>
                              <td>&nbsp;</td>
                              <td colspan="6" valign="middle" class="caption2">Answers</td>
                              <td>&nbsp;</td>
                        </tr>
                        <tr>
                              <td height="28">&nbsp;</td>
                              <td colspan="2" align="right" valign="middle">1)&nbsp;&nbsp;</td>
                              <td width="260" valign="middle">
							  <input name="ans1" type="text" class="input" id="ans1" value="<?= $ans1 ?>" size="40" /></td>
                              <td width="20" valign="middle">
							  <!--input name="ans1correct" type="radio" value="0" <?= !$ans1correct ? 'checked="checked"' : '' ?>/-->
                        <? if($QuizReal == 1) { ?>
				<input name="ans1correct" type="radio" value="0" <?= !$ans1correct ? 'checked="checked"' : '' ?>>
                        <? } ?>
				</td>
                              <td width="65" valign="middle">&nbsp;<!--span style="color: #CC0000">Incorrect</span-->
                        <? if($QuizReal == 1) { ?>
				<span style="color: #CC0000">Incorrect</span>
                        <? } ?>
				</td>
                              <td width="20" valign="middle">
							  <!--input name="ans1correct" type="radio" value="1" <?= $ans1correct ? 'checked="checked"' : '' ?>/-->
                        <? if($QuizReal == 1) { ?>
				<input name="ans1correct" type="radio" value="1" <?= $ans1correct ? 'checked="checked"' : '' ?>>
                        <? } ?>
				</td>
                        <td width="116" valign="middle">&nbsp;<!--span style="color: #008800">Correct</span-->
                        <? if($QuizReal == 1) { ?>
				<span style="color: #008800">Correct</span>
                        <? } ?>
			</td>
                        <td>&nbsp;</td>
                        </tr>	
                        <tr>
                              <td height="28">&nbsp;</td>
                              <td colspan="2" align="right" valign="middle">2)&nbsp;&nbsp;</td>
                              <td valign="middle">
							  <input name="ans2" type="text" class="input" id="ans2" value="<?= $ans2 ?>" size="40" /></td>
                              <td valign="middle">
							  <!--input name="ans2correct" type="radio" value="0" <?= !$ans2correct ? 'checked="checked"' : '' ?>/-->
                        <? if($QuizReal == 1) { ?>
				<input name="ans2correct" type="radio" value="0" <?= !$ans2correct ? 'checked="checked"' : '' ?>>
                        <? } ?>
				</td>
                              <td valign="middle">&nbsp;<!--span style="color: #CC0000">Incorrect</span-->
                        <? if($QuizReal == 1) { ?>
                                <span style="color: #CC0000">Incorrect</span>
                        <? } ?>
				</td>
                              <td valign="middle">
							  <!--input name="ans2correct" type="radio" value="1" <?= $ans2correct ? 'checked="checked"' : '' ?>/-->
                        <? if($QuizReal == 1) { ?>
				<input name="ans2correct" type="radio" value="1" <?= $ans2correct ? 'checked="checked"' : '' ?>>
                        <? } ?>
				</td>
                        <td valign="middle">&nbsp;<!--span style="color: #008800">Correct</span-->
                        <? if($QuizReal == 1) { ?>
                                <span style="color: #008800">Correct</span>
                        <? } ?>
			</td>
                        <td>&nbsp;</td>
                        </tr>	
                        <tr>
                              <td height="28">&nbsp;</td>
                              <td colspan="2" align="right" valign="middle">3)&nbsp;&nbsp;</td>
                              <td valign="middle">
							  <input name="ans3" type="text" class="input" id="ans3" value="<?= $ans3 ?>" size="40" /></td>
                              <td valign="middle">
							  <!--input name="ans3correct" type="radio" value="0" <?= !$ans3correct ? 'checked="checked"' : '' ?>/-->
                        <? if($QuizReal == 1) { ?>
				<input name="ans3correct" type="radio" value="0" <?= !$ans3correct ? 'checked="checked"' : '' ?>>
                        <? } ?>
				</td>
                              <td valign="middle">&nbsp;<!--span style="color: #CC0000">Incorrect</span-->
                        <? if($QuizReal == 1) { ?>
                                <span style="color: #CC0000">Incorrect</span>
                        <? } ?>
				</td>
                              <td valign="middle">
							  <!--input name="ans3correct" type="radio" value="1" <?= $ans3correct ? 'checked="checked"' : '' ?>/-->
                        <? if($QuizReal == 1) { ?>
					<input name="ans3correct" type="radio" value="1" <?= $ans3correct ? 'checked="checked"' : '' ?>>
                        <? } ?>
				</td>
                        <td valign="middle">&nbsp;<!--span style="color: #008800">Correct</span-->
                        <? if($QuizReal == 1) { ?>
                                <span style="color: #008800">Correct</span>
                        <? } ?>
			</td>
                        <td>&nbsp;</td>
                        </tr>	
					   <? for($i=1; $i<=$moreans; $i++) { $j = $i+3; $showans = isset($_POST['ans'.$j]) ? 1 : 0; ?>
                        <tr>
                              <td></td>
							  <td id="ans<?= $i ?>ht" height="<?= $showans ? 28 : 0 ?>"></td>
							  <td id="ans<?= $i ?>label" align="right"><?= $showans ? $j.')&nbsp;&nbsp;' : '' ?></td>
                              <td valign="middle" id="ans<?= $i ?>field">
							  <?= $showans ? '<input name="ans'.$j.'" type="text" class="input" value="'.$_POST['ans'.$j].'" size="40" />' : ''?>							  </td>
                              <td valign="middle" id="ans<?= $i ?>radio_incorr">
							  <!--?= $showans ? '<input name="ans'.$j.'correct" type="radio" value="0" '.(!$_POST['ans'.$j.'correct'] ? 'checked="checked"' : '').' />' : '' ?-->							  
                        <? if($QuizReal == 1) { ?>
				<?= $showans ? '<input name="ans'.$j.'correct" type="radio" value="0" '.(!$_POST['ans'.$j.'correct'] ? 'checked="checked"' : '').' />' : '' ?>
                        <? } ?>
				</td>
                              <td valign="middle" id="ans<?= $i ?>radio_incorr_label" style="color: #CC0000">
							  <!--?= $showans ? 'Incorrect' : '' ?-->
                        <? if($QuizReal == 1) { ?>
				<?= $showans ? 'Incorrect' : '' ?>
                        <? } ?>
				</td>
                              <td valign="middle" id="ans<?= $i ?>radio_corr">
							  <!--?= $showans ? '<input name="ans'.$j.'correct" type="radio" value="1" '.($_POST['ans'.$j.'correct'] ? 'checked="checked"' : '').' />' : '' ?-->							  
                        <? if($QuizReal == 1) { ?>
				<?= $showans ? '<input name="ans'.$j.'correct" type="radio" value="1" '.($_POST['ans'.$j.'correct'] ? 'checked="checked"' : '').' />' : '' ?>
                        <? } ?>
				</td>
                              <td valign="middle" id="ans<?= $i ?>radio_corr_label" style="color: #008800">
							  <!--?= $showans ? 'Correct' : '' ?-->
                        <? if($QuizReal == 1) { ?>
				<?= $showans ? 'Correct' : '' ?>
                        <? } ?>
							  </td>							  
                              <td></td>
                        </tr>
						<? } ?>					   																
                        <tr>
                              <td height="48">&nbsp;</td>
                              <td colspan="7" align="center" valign="middle">
							  <input name="submit" type="submit" class="button" id="submit" value="Add Question" />
                                 <input name="mabtn" type="button" class="button" id="mabtn" 
								 value="+ More Answers" <?= isset($_POST['ans'.$maxans]) ? 'disabled="disabled"' : '' ?> onclick="morea()"/>
                                 <input name="labtn" type="button" class="button" id="labtn" 
								 value="- Less Answers" <?= !isset($_POST['ans4']) ? 'disabled="disabled"' : '' ?> onclick="lessa()"/>
                                 <input name="cancel" type="submit" class="button" id="cancel" value="Cancel"/></td>
                              <td>&nbsp;</td>
                        </tr>
						
                        <tr>
                           <td height="20">&nbsp;</td>
                           <td>&nbsp;</td>
                           <td>&nbsp;</td>
                           <td>&nbsp;</td>
                           <td>&nbsp;</td>
                           <td>&nbsp;</td>
                           <td>&nbsp;</td>
                           <td>&nbsp;</td>
                           <td>&nbsp;</td>
                        </tr>
                    </table>
			         </fieldset>
		        </form></td>
                   <td>&nbsp;</td>
              </tr>
              <tr>
                <td height="23">&nbsp;</td>
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

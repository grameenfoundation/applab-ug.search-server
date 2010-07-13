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

if(count($_POST)) {
   $_POST = strip_form_data($_POST);
   extract($_POST);
}
if(isset($_POST['cancel'])) {
  goto("question.php?questionId=$questionId");
}
if(isset($_POST['submit'])) {
  if(strlen($question) < 3) {
     $errors = 'Question not valid<br/>';
  }
  if(!strlen($date)) {
     $errors .= 'Question send date not valid<br/>';
  }
  if(!preg_match("/^[0-9]+$/", $min)) {
     $errors .= 'Question send time not valid<br/>';
  } 
  else {
	   /* check that send time is not in the past we should allow if it is at least some time today */
	   $date = preg_split("/\//", $date);
	   $d = array_shift($date);
	   $m = array_shift($date);
	   $y = array_shift($date);
	   if(mktime($hr, $min, 0, $m, $d, $y) < mktime(0, 0, 0, date("m"), date("d"), date("Y"))) {
	      $errors .= "Time for sending the Question is in the past<br/>";
	   } else {
	      $sendTime = "$y-$m-$d $hr:$min:0";
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
	  if(strlen($val) < 1) {	      
	      $errors .= "Answer $ans not valid<br/>";
		  break;
	  } 
	  if($QuizReal == 1) {
		  $correct = $_POST['ans'.$no.'correct'];
	  } else {
		  $correct = 1;//$_POST['ans'.$no.'correct'];
	  }
	  $id = $_POST['ans'.$no.'id']; 
	  $answer = array('answer' => $val, 'correct' => $correct,  'id' => $id); 
	  $answers[] = $answer;
  } 
  if(isset($use_keyword)) { 
      execute_nonquery("UPDATE question SET keyword=NULL WHERE id=$use_keyword LIMIT 1");
  }
  if(!isset($errors)) { 
     /* check if Keyword not already in use */
	 if(!isset($use_keyword) && strlen($keyword)) { 
	    $sql="SELECT * FROM question WHERE LOWER(keyword)=LOWER('$keyword') AND id<>$questionId"; 
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
			   <strong>From Quiz:</strong>
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
	 extract(escape_form_data($_POST));
	 if(!strlen($keyword)) {
	    $keyword = 'Q'.$_POST['quizId'].$questionId;
	 }	 
	 $sql = "UPDATE question SET sendTime='$sendTime', question='$question', keyword=IF($singleKeyword=0, '$keyword', keyword), 
	         correctReply='$correctReply', wrongReply='$wrongReply' WHERE id=$questionId"; 
	 execute_nonquery($sql);
	 /* update answers */
	 $no = get_last_no($questionId);
	 foreach($answers as $answer) {
	    $ans = mysql_real_escape_string($answer['answer']);
		if($answer['id']) {
		    $sql = "UPDATE answer SET answer='$ans', correct=$answer[correct] 
			        WHERE id=$answer[id] AND questionId=$questionId LIMIT 1";					
		}
		else {
                   $no = $no+1; 
		   $sql = "INSERT INTO answer(questionId, answer, no, correct) VALUES($questionId, '$ans', $no, $answer[correct])";
		}
		execute_nonquery($sql);
	 }   
	 goto("question.php?questionId=$questionId");
  }
} 

$sql = "SELECT question.*, DATE_FORMAT(sendTime, '%d/%m/%Y') AS date, DATE_FORMAT(sendTime, '%H') AS hour, 
        DATE_FORMAT(sendTime, '%i') AS min FROM question WHERE id=$questionId";
$result = execute_query($sql);
if(!mysql_num_rows($result)) {
   goto('quiz.php');
}
$question = mysql_fetch_assoc($result);
$quiz = get_quiz_from_id($question['quizId']);

/* get Answers */
$sql = "SELECT * FROM answer WHERE questionId=$questionId";
$result = execute_query($sql);
$answers = array();
$i = 0;
while($row = mysql_fetch_assoc($result))  {
   $answers[$i++] = $row;
}

if(isset($errors)) {
  $errors = "<br/>$errors<br/>";
}
if(count($answers) <= 3) {
   $extra_ans = $moreans;
}
else {
   $extra_ans = $maxans - count($answers);
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
if(strlen($json)) {
print "var moreans = $json";
}
else {  
$js = "var moreans= {\n";
for($i=1; $i<=$extra_ans; $i++) {
   $js .= "\tans$i: {showing: false},\n";
}
$js = preg_replace("/,$/", "", $js);
$js .= "}\n";
print $js;
}
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
        <td height="" valign="top">
		   <table width="100%" border="0" cellpadding="0" cellspacing="0" class="border">
               <!--DWLayoutTable-->
               <tr>
                    <td height="22" colspan="4" align="center" valign="middle" class="caption">Edit Quiz Question</td>
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
                    <td height="">&nbsp;</td>
                  <td colspan="2" valign="top">
					 <form method="post" onsubmit="document.getElementById('json').value=moreans.toJSON();return true;">
					 <fieldset>
					<legend>Question Details</legend>
					<table width="677" border="0" cellpadding="0" cellspacing="0">
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
							  <input name="question" type="text" class="input" id="question" value="<?= $question['question'] ?>" size="45" />
                              &nbsp;</td>
                              <td>&nbsp;</td>
                         </tr>
                         <tr <?= $quiz['sendall'] ? 'style="display: none"' : '' ?>>
                              <td height="35">&nbsp;</td>
                              <td colspan="2" align="right" valign="middle">Send Time:&nbsp;&nbsp;</td>
                            <td colspan="5" valign="middle"><input name="date" type="text" class="input" id="date" style="cursor: pointer" title="Select Date" 
							  onclick="displayDatePicker('date')" value="<?= $question['date'] ?>" size="15" readonly="true"/>
                               <select name="hr" class="input" id="hr">
                                  <?= hours($question['hour']) ?>
                                                                                                                            </select>
                               <input name="min" type="text" class="input" id="min" value="<?= $question['min'] ?>" size="4" />
                            HRS</td>
                            <td>&nbsp;</td>
                         </tr>
                      
                        <tr <?= $quiz['singleKeyword'] ? 'style="display: none"' : '' ?>>
                              <td height="35">&nbsp;</td>
                              <td colspan="2" align="right" valign="middle">Keyword:&nbsp;&nbsp;</td>
                              <td colspan="5" valign="middle">
							  <input name="keyword" type="text" class="input" id="keyword" value="<?= $question['keyword'] ?>" size="45" />
							  <input name="singleKeyword" type="hidden" id="singleKeyword" value="<?= $quiz['singleKeyword'] ?>" /></td>
                              <td>&nbsp;</td>
                        </tr>						
                        <? if($QuizReal == 1) { ?>
                        <tr>
                              <td height="65">&nbsp;</td>
                              <td colspan="2" align="right" valign="middle" style="color: #008800">Correct Reply:&nbsp;&nbsp;</td>
                              <td colspan="5" valign="middle">
                                                             <textarea name="correctReply" class="input" id="correctReply"
                                                          style="width: 300px; height: 50px"><?= $question['correctReply'] ?></textarea>                                                                                       </td>
                              <td>&nbsp;</td>
                        </tr>
                        <tr>
                              <td height="65">&nbsp;</td>
                              <td colspan="2" align="right" valign="middle" style="color: #CC0000">Wrong Reply:&nbsp;&nbsp;</td>
                              <td colspan="5" valign="middle">
                                                             <textarea name="wrongReply" class="input" id="wrongReply"
                                                          style="width: 300px; height: 50px"><?= $question['wrongReply'] ?></textarea>                                                                                        </td>
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
							  <input name="ans1" type="text" class="input" id="ans1" value="<?= strlen($answers[0]['answer']) ? $answers[0]['answer'] : $_POST['ans1'] ?>" size="40" /></td>
                              <td width="20" valign="middle">
						  <!--input name="ans1correct" type="radio" value="0" <?= !$answers[0]['correct'] ? 'checked="checked"' : '' ?>/-->
				<? if($QuizReal == 1) { ?>
					<input name="ans1correct" type="radio" value="0" <?= !$answers[0]['correct'] ? 'checked="checked"' : '' ?>>
				<? } ?>
			      </td>
                              <td width="65" valign="middle">&nbsp;<!--span style="color: #CC0000">Incorrect</span-->
				<? if($QuizReal == 1) { ?>
					<span style="color: #CC0000">Incorrect</span>
                                <? } ?>
				</td>
                              <td width="20" valign="middle">
				  <!--input name="ans1correct" type="radio" value="1" <?= $answers[0]['correct'] ? 'checked="checked"' : '' ?>/-->
                                <? if($QuizReal == 1) { ?>
					<input name="ans1correct" type="radio" value="1" <?= $answers[0]['correct'] ? 'checked="checked"' : '' ?>>
                                <? } ?>
			      </td>
                        <td width="116" valign="middle">&nbsp;<!--span style="color: #008800">Correct</span-->
                                <? if($QuizReal == 1) { ?>
					<span style="color: #008800">Correct</span>
                                <? } ?>

                           <input name="ans1id" type="hidden" id="ans1id" value="<?= $answers[0]['id'] ?>" /></td>
                        <td>&nbsp;</td>
                        </tr>	
                        <tr>
                              <td height="28">&nbsp;</td>
                              <td colspan="2" align="right" valign="middle">2)&nbsp;&nbsp;</td>
                              <td valign="middle">
							  <input name="ans2" type="text" class="input" id="ans2" value="<?= strlen($answers[1]['answer']) ? $answers[1]['answer'] : $_POST['ans2'] ?>" size="40" /></td>
                              <td valign="middle">
							  <!--input name="ans2correct" type="radio" value="0" <?= !$answers[1]['correct'] ? 'checked="checked"' : '' ?>/-->
                                <? if($QuizReal == 1) { ?>
					<input name="ans2correct" type="radio" value="0" <?= !$answers[1]['correct'] ? 'checked="checked"' : '' ?>>
                                <? } ?>
				</td>
                              <td valign="middle">&nbsp;<!--span style="color: #CC0000">Incorrect</span-->
                                <? if($QuizReal == 1) { ?>
					<span style="color: #CC0000">Incorrect</span>
                                <? } ?>
			      </td>
                              <td valign="middle">
							  <!--input name="ans2correct" type="radio" value="1" <?= $answers[1]['correct'] ? 'checked="checked"' : '' ?>/-->
                                <? if($QuizReal == 1) { ?>
					<input name="ans2correct" type="radio" value="1" <?= $answers[1]['correct'] ? 'checked="checked"' : '' ?>>
                                <? } ?>
				</td>
                        <td valign="middle">&nbsp;<!--span style="color: #008800">Correct</span-->
                                <? if($QuizReal == 1) { ?>
					<span style="color: #008800">Correct</span>
                                <? } ?>
                           <input name="ans2id" type="hidden" id="ans2id" value="<?= $answers[1]['id'] ?>" /></td>
                        <td>&nbsp;</td>
                        </tr>	
                        <tr>
                              <td height="28">&nbsp;</td>
                              <td colspan="2" align="right" valign="middle">3)&nbsp;&nbsp;</td>
                              <td valign="middle">
							  <input name="ans3" type="text" class="input" id="ans3" value="<?= strlen($answers[2]['answer']) ? $answers[2]['answer'] : $_POST['ans3'] ?>" size="40" /></td>
                              <td valign="middle">
							  <!--input name="ans3correct" type="radio" value="0" <?= !$answers[2]['correct'] ? 'checked="checked"' : '' ?>/-->
                                <? if($QuizReal == 1) { ?>
					<input name="ans3correct" type="radio" value="0" <?= !$answers[2]['correct'] ? 'checked="checked"' : '' ?>>
                                <? } ?>
			      </td>
                              <td valign="middle">&nbsp;<!--span style="color: #CC0000">Incorrect</span-->
                                <? if($QuizReal == 1) { ?>
					<span style="color: #CC0000">Incorrect</span>
                                <? } ?>
			      </td>
                              <td valign="middle">
							  <!--input name="ans3correct" type="radio" value="1" <?= $answers[2]['correct'] ? 'checked="checked"' : '' ?>/-->
                                <? if($QuizReal == 1) { ?>
					<input name="ans3correct" type="radio" value="1" <?= $answers[2]['correct'] ? 'checked="checked"' : '' ?>>
                                <? } ?>
				</td>
                        <td valign="middle">&nbsp;<!--span style="color: #008800">Correct</span-->
                                <? if($QuizReal == 1) { ?>
					<span style="color: #008800">Correct</span>
                                <? } ?>
                           <input name="ans3id" type="hidden" id="ans3id" value="<?= $answers[2]['id'] ?>" /></td>
                        <td>&nbsp;</td>
                        </tr>	
                       <? for($i=3; $i<count($answers); $i++) { $j=$i+1; ?>
                        <tr>
                              <td height="28">&nbsp;</td>
                              <td colspan="2" align="right" valign="middle"><?= $i+1 ?>)&nbsp;&nbsp;</td>
                              <td valign="middle">
					<input name="ans<?= $j ?>" type="text" class="input" id="ans13" value="<?= $answers[$i]['answer'] ?>" size="40" /></td>
                              <td valign="middle">
				   <!--input name="ans<?= $i+1 ?>correct" type="radio" value="0" <?= !$answers[$i]['correct'] ? 'checked="checked"' : '' ?>/-->
                                <? if($QuizReal == 1) { ?>
					<input name="ans<?= $i+1 ?>correct" type="radio" value="0" <?= !$answers[$i]['correct'] ? 'checked="checked"' : '' ?>>
                                <? } ?>
				</td>
                              <td valign="middle">&nbsp;<!--span style="color: #CC0000">Incorrect</span-->
                                <? if($QuizReal == 1) { ?>
					<span style="color: #CC0000">Incorrect</span>
                                <? } ?>
				</td>
                              <td valign="middle">
							  <!--input name="ans<?= $j ?>correct" type="radio" value="1" <?= $answers[$i]['correct'] ? 'checked="checked"' : '' ?>/-->
                                <? if($QuizReal == 1) { ?>
					<input name="ans<?= $j ?>correct" type="radio" value="1" <?= $answers[$i]['correct'] ? 'checked="checked"' : '' ?>>
                                <? } ?>
				</td>
                        <td valign="middle">&nbsp;<!--span style="color: #008800">Correct</span-->
                                <? if($QuizReal == 1) { ?>
                                        <span style="color: #008800">Correct</span>
                                <? } ?>
                           <input name="ans<?= $j ?>id" type="hidden" value="<?= $answers[$i]['id'] ?>" /></td>
                        <td>&nbsp;</td>
                        </tr>						   
					   <? } $nlabel=$i; ?>	
					   <? for($i=1; $i<=$extra_ans; $i++) { $j = $i+3+(count($answers) > 3 ? count($answers)-3 : 0); $showans = isset($_POST['ans'.$j]) ? 1 : 0; ?>
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
					<?=$showans ? 'Correct' : '' ?>
                                <? } ?>
<input name="ans<?= $j ?>id" type="hidden" id="ans<?= $j ?>id" value="0" />							  </td>							  
                              <td></td>
                        </tr>
						<? } ?>							   				   
                        <tr>
                              <td height="48"></td>
                           <td colspan="7" align="center" valign="middle">
						      <input name="submit" type="submit" class="button" id="submit" value="Update Question" />
						      <input name="mabtn" type="button" class="button" id="mabtn" 
								 value="+ More Answers" <?= !$extra_ans || isset($answer[$maxans-1]) || isset($_POST['ans'.$maxans])? 'disabled="disabled"' : '' ?> onclick="morea_e(<?= $extra_ans ?>, <?= $nlabel ?>)"/>
                              <input name="labtn" type="button" <?= !count($_POST) ? 'disabled="disabled"' : '' ?> class="button" id="labtn" 
								 value="- Less Answers" onclick="lessa_e(<?= $extra_ans ?>)"/>
<input name="cancel" type="submit" class="button" id="cancel" value="&laquo; Go To Question"/>
                              <input name="quizId" type="hidden" id="quizId" value="<?= $quiz['id'] ?>" />
                              <input name="json" type="hidden" id="json" /></td>
                           <td></td>
                        </tr>
                        <tr>
                           <td height="24"></td>
                           <td>&nbsp;</td>
                           <td></td>
                           <td></td>
                           <td></td>
                           <td></td>
                           <td></td>
                           <td></td>
                           <td></td>
                        </tr>
                    </table>
			         </fieldset></form></td>
                    <td>&nbsp;</td>
               </tr>
               <tr>
                  <td height="31"></td>
                  <td></td>
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

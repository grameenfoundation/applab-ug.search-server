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

if(!preg_match("/^[0-9]+$/", $surveyId)) { 
   goto('surveys.php');
}

$survey = get_table_record('survey', $surveyId);
if(empty($survey)) {
   goto('surveys.php');
}

if(count($_POST)) {
   $_POST = strip_form_data($_POST);
   extract($_POST);
}
if(isset($_POST['cancel'])) {
  goto("survey.php?surveyId=$surveyId");
}
if(isset($_POST['submit'])) {  
  if(strlen($question) < 3) {
     $errors = 'Question not valid<br/>';
  } 
  /* validate answers */
  $answers = array();
  $i=97;
  foreach($_POST as $key=>$val) {
      if(!preg_match("/^ans[0-9]+$/", $key) || !strlen($val)) {
	     continue;
	  }
	  $no = preg_replace("/^ans/", "", $key);
	  if(!strlen($val)) continue;
	  $correct = 1;//$_POST['ans'.$no.'correct'];
	  $id = $_POST['ans'.$no.'id'];
	  $answer = array('id'=>'a'.md5(time().$i), 'no'=>sprintf("%c", $i), 'answer' => $val, 'correct' => $correct); 
	  $i++;
	  $answers[] = $answer;
  } 
  if(!isset($errors)) {  
     $questions = strlen($survey['questions']) ? unserialize($survey['questions']) : array(); 
	 for($i=0; $i<count($questions); $i++) {
	    if($questions[$i]['no'] == $_GET['question']) { 
		   /* modify this question */
		   $qn = array('id'=> 'q'.md5(time()), 'no'=>$questions[$i]['no'], 'question'=>$_POST['question'], 'answers'=>$answers, 'open'=>$_POST['open']); 
		   $questions[$i] = $qn;
		   break;
		}
	 } 
	 $questions = mysql_real_escape_string(serialize($questions));
	 
	 extract(escape_form_data($_POST));
	 $sql = "UPDATE survey SET questions='$questions' WHERE id=$surveyId";      
	 execute_nonquery($sql);      
	 if(mysql_affected_rows())
	     logaction("Updated Question: $_POST[question] for Survey ($survey[name])");
	 goto("survey.php?surveyId=$surveyId");
  }
} 

if(isset($errors)) {
  $errors = "<br/>$errors<br/>";
}
extract($survey);
$questions = unserialize($survey['questions']);
$qn = NULL;
foreach($questions as $q) { 
   if($q['no'] == $question) {
       $qn = $q; 
	   break;
   }
} 
if(!isset($errors) && is_null($qn)) {  
   goto("survey.php?surveyId=$surveyId");
}
$answers = $qn['answers'];
if(count($answers) <= 3) {
   $extra_ans = $moreans;
}
else {
   $extra_ans = $maxans - count($answers);
}  

/* menu highlight */
$page = 'survey';
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
        <td height="425" valign="top">
		   <table width="100%" border="0" cellpadding="0" cellspacing="0" class="border">
               <!--DWLayoutTable-->
               <tr>
                    <td height="22" colspan="4" align="center" valign="middle" class="caption">Edit Survey Question </td>
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
                        <td width="34" height="28" valign="middle"><img src="images/quiz.gif" width="34" height="28" style="cursor: pointer" title="<?= $survey['name'] ?>" /></td>
                        <td width="645" valign="middle">&nbsp; <span class="caption2">Survey: </span>
                           <?= $survey['name'] ?>
- <span style="color: #666666">created
<?= $survey['createdate'] ?>
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
                    <td height="281">&nbsp;</td>
                  <td colspan="2" valign="top">
					 <form method="post">
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
							  <input name="question" type="text" class="input" id="question" value="<?= $qn['question'] ?>" size="45" />
                              &nbsp;</td>
                              <td>&nbsp;</td>
                         </tr>
                         <tr>
                              <td height="35">&nbsp;</td>
                              <td colspan="2" align="right" valign="middle">Open:&nbsp;&nbsp;							  </td>
                              <td colspan="5" valign="middle">
							  <input name="open" type="radio" value="0"<?= $qn['open']==0 ? ' checked="checked"' : '' ?>/>
                              			<span style="color: #990000">NO</span>
                              			&nbsp;&nbsp;
                              			<input name="open" type="radio" value="1"<?= $qn['open']==1 ? ' checked="checked"' : '' ?>/>
										<span style="color: #008800">YES</span></td>
                              <td>&nbsp;</td>
                         </tr>                         						
                        <tr>
                              <td height="28">&nbsp;</td>
                              <td>&nbsp;</td>
                              <td colspan="6" valign="middle" class="caption2"><u>Answers</u></td>
                              <td>&nbsp;</td>
                        </tr>
                        <tr>
                              <td height="28">&nbsp;</td>
                              <td colspan="2" align="right" valign="middle">1)&nbsp;&nbsp;</td>
                              <td width="260" valign="middle">
							  <input name="ans1" type="text" class="input" id="ans1" value="<?= $answers[0]['answer'] ?>" size="40" /></td>
                              <td width="20" valign="middle">
							  <!--input name="ans1correct" type="radio" value="0" <?= !$answers[0]['correct'] ? 'checked="checked"' : '' ?>/--></td>
                              <td width="65" valign="middle">&nbsp;<!--span style="color: #CC0000">Incorrect</span--></td>
                              <td width="20" valign="middle">
							  <!--input name="ans1correct" type="radio" value="1" <?= $answers[0]['correct'] ? 'checked="checked"' : '' ?>/--></td>
                        <td width="116" valign="middle">&nbsp;<!--span style="color: #008800">Correct</span-->
                           <input name="ans1id" type="hidden" id="ans1id" value="<?= $answers[0]['id'] ?>" /></td>
                        <td>&nbsp;</td>
                        </tr>	
                        <tr>
                              <td height="28">&nbsp;</td>
                              <td colspan="2" align="right" valign="middle">2)&nbsp;&nbsp;</td>
                              <td valign="middle">
							  <input name="ans2" type="text" class="input" id="ans2" value="<?= $answers[1]['answer'] ?>" size="40" /></td>
                              <td valign="middle">
							  <!--input name="ans2correct" type="radio" value="0" <?= !$answers[1]['correct'] ? 'checked="checked"' : '' ?>/--></td>
                              <td valign="middle">&nbsp;<!--span style="color: #CC0000">Incorrect</span--></td>
                              <td valign="middle">
							  <!--input name="ans2correct" type="radio" value="1" <?= $answers[1]['correct'] ? 'checked="checked"' : '' ?>/--></td>
                        <td valign="middle">&nbsp;<!--span style="color: #008800">Correct</span-->
                           <input name="ans2id" type="hidden" id="ans2id" value="<?= $answers[1]['id'] ?>" /></td>
                        <td>&nbsp;</td>
                        </tr>	
                        <tr>
                              <td height="28">&nbsp;</td>
                              <td colspan="2" align="right" valign="middle">3)&nbsp;&nbsp;</td>
                              <td valign="middle">
							  <input name="ans3" type="text" class="input" id="ans3" value="<?= $answers[2]['answer'] ?>" size="40" /></td>
                              <td valign="middle">
							  <!--input name="ans3correct" type="radio" value="0" <?= !$answers[2]['correct'] ? 'checked="checked"' : '' ?>/--></td>
                              <td valign="middle">&nbsp;<!--span style="color: #CC0000">Incorrect</span--></td>
                              <td valign="middle">
							  <!--input name="ans3correct" type="radio" value="1" <?= $answers[2]['correct'] ? 'checked="checked"' : '' ?>/--></td>
                        <td valign="middle">&nbsp;<!--span style="color: #008800">Correct</span-->
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
				   <!--input name="ans<?= $i+1 ?>correct" type="radio" value="0" <?= !$answers[$i]['correct'] ? 'checked="checked"' : '' ?>/--></td>
                              <td valign="middle">&nbsp;<!--span style="color: #CC0000">Incorrect</span--></td>
                              <td valign="middle">
							  <!--input name="ans<?= $j ?>correct" type="radio" value="1" <?= $answers[$i]['correct'] ? 'checked="checked"' : '' ?>/--></td>
                        <td valign="middle">&nbsp;<!--span style="color: #008800">Correct</span-->
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
							  <?= $showans ? '<input name="ans'.$j.'" type="text" class="input" value="'.$_POST['ans'.$j].'" size="40" />' : ''?>
							  </td>
                              <td valign="middle" id="ans<?= $i ?>radio_incorr">
							  <!--?= $showans ? '<input name="ans'.$j.'correct" type="radio" value="0" '.(!$_POST['ans'.$j.'correct'] ? 'checked="checked"' : '').' />' : '' ?-->
							  </td>
                              <td valign="middle" id="ans<?= $i ?>radio_incorr_label" style="color: #CC0000">
							  <!--?= $showans ? 'Incorrect' : '' ?--></td>
                              <td valign="middle" id="ans<?= $i ?>radio_corr">
							  <!--?= $showans ? '<input name="ans'.$j.'correct" type="radio" value="1" '.($_POST['ans'.$j.'correct'] ? 'checked="checked"' : '').' />' : '' ?-->
							  </td>
                              <td valign="middle" id="ans<?= $i ?>radio_corr_label" style="color: #008800">
							  <!--?= $showans ? 'Correct' : '' ?--><input name="ans<?= $j ?>id" type="hidden" id="ans<?= $j ?>id" value="0" />
							  </td>							  
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
<input name="cancel" type="submit" class="button" id="cancel" value="Cancel"/>
                              
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
			         </fieldset>
				  </form></td>
                    <td>&nbsp;</td>
               </tr>
               <tr>
                  <td height="46">&nbsp;</td>
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

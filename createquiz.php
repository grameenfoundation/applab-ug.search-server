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
include("excel/functions.php");

dbconnect();
validate_session(); 
check_admin_user();

if(isset($_POST["cancel"])) {
   $args = isset($_GET['add_from_wl']) ? '?add_from_wl=TRUE' : NULL;
   redirect('quiz.php'.$args);
}

if(count($_POST)) {
   $_POST = strip_form_data($_POST);
   extract($_POST);
}  

if(isset($_POST["submit"])) { //print_r($_POST);
   if(strlen($_POST['name']) < 3) {
     $errors = 'Quiz name not valid<br/>';
   }
   if($singleKeyword) {
      $keyword = strtolower($keyword);
      if(!preg_match("/^[a-z0-9]{1,30}$/", $keyword)) {
	      $errors .= 'Please specify a valid Keyword<br/>';
	  }
	  elseif(unique_field_exists('keyword', $keyword, 'quiz')) {
	     $errors .= 'Specified Keyword is already associated with a quiz<br/>';
	  }
   }
   $phone_numbers = read_phone_numbers(); //print_r($phone_numbers);
   if(is_numeric($phone_numbers) && $phone_numbers== 0) {
      $errors .= 'No valid phone numbers found in uploaded file<br/>';
   } 
   if($sendall) {
		  /* check date */
		  if(!strlen($_POST['date'])) {
		     $errors .= "Date for sending Questions not valid<br/>";
		  }	 
		  /* check minute */
		  if(!preg_match("/^[0-9]{1,2}$/", $_POST['min']) || $_POST['min']>59) {
		      $errors .= "Time for sending Questions not valid<br/>";
		  }	
		  if(!isset($errors)) {
		  /* check that send time is not in the past */
	      $date = preg_split("/\//", $_POST['date']);
	      $d = array_shift($date);
	      $m = array_shift($date);
	      $y = array_shift($date);
	      $hr = $_POST['hr']; 
	      $min = $_POST['min'];
	      if(mktime($hr, $min, 0, $m, $d, $y) < time()) {
	         $errors .= "The time for sending Questions is in the past<br/>";
	      }	
		  else {
	          $sendall_time = "$y-$m-$d $hr:$min:00";  
		  }
		  }
		  //    
   }
   $questions = array();
   for($i=1; $i<= $maxqns; $i++) {
       if(!strlen($_POST['question'.$i])) continue;
	   if(strlen($_POST['question'.$i]) < 3) {
	      $errors .= "Question $i not valid<br/>";
		  break;
	   } 
	   else {
	      if(!$sendall) {
		     /* check date */
		     if(!strlen($_POST['date'.$i])) {
		        $errors .= "Date for sending Question $i not valid<br/>";
			    break;
		     }	 
		     /* check minute */
		     if(!preg_match("/^[0-9]{1,2}$/", $_POST['min'.$i]) || $_POST['min'.$i]>59) {
		         $errors .= "Time for sending Question $i not valid<br/>";
			     break;
		     }	
		  }	  
	   }	
	   if(!$sendall) { 
	      /* check that send time is not in the past */
	      $date = preg_split("/\//", $_POST['date'.$i]);
	      $d = array_shift($date);
	      $m = array_shift($date);
	      $y = array_shift($date);
	      $hr = $_POST['hr'.$i]; 
	      $min = $_POST['min'.$i];
	      if(mktime($hr, $min, 0, $m, $d, $y) < time()) {
	         $errors .= "The time for sending Question $i is in the past<br/>";
		     break;
	      }	
	      $sendTime = "$y-$m-$d $hr:$min:00";
	   }
	   else {
	       $sendTime = $sendall_time;
	   }
	   $questions[] = array('question' => $_POST['question'.$i], 'sendTime' => $sendTime);   
   } 
   if(!isset($errors)) {
     $qname = mysql_real_escape_string($_POST['name']);
	 $reply = rtrim($reply);
	 if(strlen($reply) > 160) {
	    $reply = substr($reply, 0, 160);
	 }	
	 $reply = mysql_real_escape_string($reply);	
     $sql = "INSERT INTO quiz(name, sendall, reply, singleKeyword, keyword, createDate) 
	         VALUES('$qname', $sendall, IF(LENGTH('$reply'), '$reply', NULL), $singleKeyword, IF($singleKeyword, '$keyword', NULL), NOW())";
	 lock_system();
	 execute_nonquery($sql);
	 $quizId = mysql_insert_id();
	 unlock_system();
	 $no = 1;
	 foreach($questions as $question) { 
	     $qn = mysql_real_escape_string($question['question']);
		 $sendTime = $question['sendTime'];
	     $sql = "INSERT INTO question(quizId, sendTime, createDate, createTime, question, no) 
		         VALUES ($quizId, '$sendTime', CURRENT_DATE(), NOW(), '$qn', IF($singleKeyword, '$no', NULL))"; 
		 if(!mysql_query($sql)) {
		    $error = mysql_error();
			if(preg_match("/duplicate/i", $error)) { 
			   /* same question ? */
			   continue;
			}  
			else {
			   show_message('Database Error', $error."<br/>$sql", '#FF0000');
			}
		 }	  
		/* update Keyword */
		$questionId = mysql_insert_id();
		$_keyword = $singleKeyword ? $keyword.'_'.$no : 'Q'.$quizId.$questionId;
		$sql="UPDATE question SET keyword='$_keyword' WHERE id=$questionId"; 
		execute_nonquery($sql);		 
		$no++; 
	 }
	 /* the phone numbers */ 	 
	 foreach($phone_numbers as $phone) {
	    if(!preg_match("/^[0-9]{8,}$/", $phone)) {
		   continue;
		}   	 
	    $sql = "INSERT INTO quizphoneno(quizId, createDate, phone) VALUES ($quizId, NOW(), '$phone')";
		if(!mysql_query($sql)) {
		    $error = mysql_error();
			if(preg_match("/duplicate/i", $error)) { 
		       continue;
			}
			else {
			   show_message('Database Error', $error."<br/>$sql", '#FF0000');
			}
		}
	 }
	 logaction("Created quiz: $qname");
	 clear_working_list();
	 goto("questions.php?quizId=$quizId");
  }
}

if(strlen($qimportlist)) {
   $qimportlist = trim($qimportlist);
   $qimportlist = preg_replace("/,$/", "", $qimportlist);
   $list = preg_split("/,/", $qimportlist);
   if(count($list)) {
      $qlabel = '&nbsp;<span style="color: #008800">Imported Nos. from ('.count($list).') quiz(es)</span>
	  <span style="cursor: pointer;color: #FF0000; font-size: 10px" onclick="removelabel(\'ql\',true)" title="Click to remove numbers">[delete]</span>';
   }
} 
if(!isset($qlabel)) {
   $qlabel = '&nbsp;Import Numbers From Existing Quiz';
}

if(strlen($gimportlist)) {
   $gimportlist = trim($gimportlist);
   $gimportlist = preg_replace("/,$/", "", $gimportlist);
   $list = preg_split("/,/", $gimportlist);
   if(count($list)) {
      $glabel = '&nbsp;<span style="color: #FF3300">Imported ('.count($list).') Number(s)</span>
	  <span style="cursor: pointer;color: #FF0000; font-size: 10px" onclick="removelabel(\'gl\',true)" title="Click to remove numbers">[delete]</span>';
   }
} 
if(!isset($glabel)) {
   $glabel = '&nbsp;Import Numbers From General List';
}
if(strlen($wimportlist)) {
   $wimportlist = trim($wimportlist);//print $wimportlist;
   $wimportlist = preg_replace("/,$/", "", $wimportlist);
   $list = preg_split("/,/", $wimportlist);
   if(count($list)) {
      $wlabel = '&nbsp;&nbsp;<span style="color: #008800">Added ('.count($list).') Number(s)</span>
	  <span style="cursor: pointer;color: #FF0000" onclick="removelabel(\'wl\',true)" title="Click to remove numbers">[delete]</span>';
   }
} 

if(isset($add_from_wl) && !count($_POST)) {
    $userId = get_user_id();
	$sql = "SELECT * FROM workinglist WHERE owner='$userId'";
	$result=execute_query($sql);
	$_numbers = array();
	while($row=mysql_fetch_assoc($result)) {
	    $_numbers[]=$row['phone'];
	}
	if(count($_numbers)) {
	    $_POST['numbers'] = implode(', ', $_numbers);
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
$js = "var moreqns = {\n";
for($i=1; $i<=$moreqns; $i++) {
   $js .= "\tqn$i: {showing: ".(isset($_POST['question'.($i+3)]) ? 'true' : 'false')."},\n";
}
$js = preg_replace("/,$/", "", $js);
$js .= "}\n";
print $js;
?>
</script>
</head>

<body class="main">
<table width="790" border="0" align="center" cellpadding="0" cellspacing="0">
     <!--DWLayoutTable-->
     <tr>
          <td width="790" height="124" valign="top"><? include('top.php') ?></td>
     </tr>
     <tr>
        <td height="577" valign="top">
	         <table width="100%" border="0" cellpadding="0" cellspacing="0" class="border">
	              <!--DWLayoutTable-->
	              <tr>
	                   <td height="22" colspan="3" align="center" valign="middle" class="caption">Create Quiz </td>
                  </tr>
	              <tr>
	                   <td width="15" height="48">&nbsp;</td>
                       <td width="747">&nbsp;</td>
                       <td width="26">&nbsp;</td>
                  </tr>
	              <tr>
	                   <td height="260">&nbsp;</td>
                       <td valign="top">
					   <form name="df" method="post" enctype="multipart/form-data" onsubmit="return !page.locked">
                            <fieldset>
                            <legend>Quiz Details</legend>
				            <table width="745" border="0" cellpadding="0" cellspacing="0">
				                 <!--DWLayoutTable-->
				                 <tr>
				                      <td width="17" height="23">&nbsp;</td>
                        <td width="12">&nbsp;</td>
                        <td width="58">&nbsp;</td>
                        <td width="74">&nbsp;</td>
                        <td width="19">&nbsp;</td>
                        <td width="11">&nbsp;</td>
                        <td colspan="8" valign="top" class="error">
                           			<? if(isset($errors)) echo $errors; ?>					   </td>
                        <td width="16">&nbsp;</td>
				                 </tr>
					     
					     <tr>
					     <td height="28" colspan="6" align="right" valign="middle">Name:&nbsp;&nbsp;</td>
                         <td colspan="8" valign="middle">
						    		<input name="name" type="text" class="input" id="name" value="<?= $_POST['name'] ?>" size="55" /></td>
                         <td>&nbsp;</td>
		                    </tr>
					     <tr>
					     <td height="33" colspan="6" align="right" valign="middle">Use Single Keyword:&nbsp;&nbsp;</td>
                         <td colspan="2" valign="middle"><input name="singleKeyword" type="radio" value="0" <?= !$singleKeyword ? 'checked="checked"' : ''?> onclick="single(false)"/>
                         			NO</td>
                         <td colspan="6" valign="middle"><input name="singleKeyword" type="radio" value="1" <?= $singleKeyword ? 'checked="checked"' : ''?> onclick="single(true)"/> 
                         			<span style="color: #FF33CC">YES</span></td>
                         <td>&nbsp;</td>
		                    </tr>	
					     <tr  <?= !$singleKeyword ? 'style="display: none"' : '' ?> id="kr">
					     <td height="33" colspan="6" align="right" valign="middle">Keyword:&nbsp;&nbsp;</td>
                         <td colspan="8" valign="middle">
						    		<input name="keyword" type="text" class="input" id="keyword" value="<?= $_POST['keyword'] ?>" size="55" /></td>
                         <td>&nbsp;</td>
		                    </tr>													
					     <tr>
					     <td height="70" colspan="6" align="right" valign="top"><br />
			Reply SMS:&nbsp;&nbsp;</td>
                         <td colspan="8" valign="middle">
						 			<textarea name="reply" cols="55" class="input" id="reply" 
									style="width: 360px; height: 65px"><?= $_POST['reply'] ?></textarea>						 			                        </td>
                         <td>&nbsp;</td>
		                    </tr>							
					                    <tr>
					                         <td height="50">&nbsp;</td>
                                             <td>&nbsp;</td>
                                             <td>&nbsp;</td>
                                             <td colspan="11" valign="middle" class="caption2"><u>Phone Numbers for this Quiz</u></td>
                         <td>&nbsp;</td>
					                    </tr>	
					                    <tr>
					                         <td height="44">&nbsp;</td>
                                             <td>&nbsp;</td>
                                             <td colspan="2" align="right" valign="middle">Browse File:&nbsp;&nbsp;</td>
                                           <td colspan="2" valign="middle"><img src="images/excel.jpg" width="30" height="30" style="cursor: pointer" title="Input phone numbers from file"/><br /></td>
                                           <td colspan="3" valign="middle">
										   &nbsp;<input type="file" name="file" size="30" style="font-size: 11px"/></td>
                         <td colspan="5" valign="middle">&nbsp;<span style="color: #FF0000">*</span>Excel / CSV or (Text file - one No. per line) </td>
                                           <td>&nbsp;</td>
					                    </tr>
					                    <tr>
					                       <td height="60"></td>
					                       <td></td>
					                       <td>&nbsp;</td>
					                       <td>&nbsp;</td>
					                       <td colspan="3" valign="middle">
										   <img src="images/group.jpg" width="45" height="45" style="cursor: pointer" 
										   title="Click to Import Numbers" onclick="importn('quiz')"/></td>
					                       <td colspan="3" valign="middle" id="qlabel" style="font-size: 10px"><?= $qlabel ?></td>
					                       <td colspan="2" valign="middle"><img src="images/import.gif" width="38" height="46" style="cursor: pointer" title="Click to import numbers from the general list" onclick="importn('general')"/></td>
					                       <td colspan="2" valign="middle" id="glabel" style="font-size: 10px"><?= $glabel ?></td>
					                       <td></td>
	                        </tr>
					                    
					                    <tr>
					                       <td height="40"></td>
					                       <td></td>
					                       <td>&nbsp;</td>
					                       <td>&nbsp;</td>
					                       <td>&nbsp;</td>
					                       <td colspan="8" valign="middle"><a href="#" title="Get Contacts from Group(s) and Working List" style="color: #FF3300; text-decoration: underline" onclick="importn('worklist');return false;">[Send To Group]</a> <span id="wlabel"><?= $wlabel ?></span></td>
					                       <td width="16">&nbsp;</td>
					                       <td></td>
	                        </tr>
					                    <tr>
					                       <td height="120"></td>
					                       <td></td>
					                       <td colspan="3" align="right" valign="top"><br />
   Input Numbers:&nbsp;&nbsp; </td>
					                       <td colspan="8" valign="middle">
									          <textarea name="numbers" style="width: 320px; height: 100px"  
										   class="input" id="numbers"><?= $_POST['numbers'] ?></textarea>										</td>
					                       <td width="16">&nbsp;</td>
					                       <td></td>
	                        </tr>
					                    
					                    <tr>
					                       <td height="14"></td>
					                       <td></td>
					                       <td></td>
					                       <td></td>
					                       <td></td>
					                       <td colspan="8" valign="top" style="font-size: 10px">[Separate phone numbers with commas] </td>
					                       <td></td>
					                       <td></td>
	                        </tr>
					                    <tr>
					                       <td height="40"></td>
					                       <td></td>
					                       <td colspan="3" align="right" valign="middle">Question Sending:&nbsp;&nbsp;</td>
					                       <td colspan="8" valign="middle">
										   <input name="sendall" id="sendall" type="radio" value="0" <?= !$sendall ? 'checked="checked"' : '' ?> onclick="tsend(false)"/>
Send at different times&nbsp;&nbsp;
			<input name="sendall" id="sendall" type="radio" value="1" <?= $sendall ? 'checked="checked"' : '' ?> onclick="tsend(true)"/>
<span style="color: #0066FF">Send All at same time</span></td>
					                       <td></td>
					                       <td></td>
	                        </tr>	
					                    <tr <?= !$sendall ? 'style="display: none"' : '' ?> id="qs">
					                       <td height="40"></td>
					                       <td></td>
					                       <td colspan="3" align="right" valign="middle">Send Time:&nbsp;&nbsp;</td>
					                       <td colspan="8" valign="middle">
										   <input name="date" type="text" class="input" id="date" style="cursor: pointer" title="Select Date" onclick="displayDatePicker('date')" value="<?= $_POST['date'] ?>" size="15" readonly="true"/>
													<select name="hr" class="input" id="hr">
																<?= hours($hr) ?>
																										</select>
													<input name="min" type="text" class="input" id="min" value="<?= $min ? $min : '00' ?>" size="4" />
HRS</td>
					                       <td></td>
					                       <td></td>
	                        </tr>														
					                    
					                    
					                    
					                    

					                    <tr>
					                         <td height="35">&nbsp;</td>
                         <td>&nbsp;</td>
                         <td>&nbsp;</td>
                         <td colspan="8" valign="middle" class="caption2"><u>Question</u></td>
                         <td colspan="3" valign="middle" class="caption2"><u>Send Time</u> </td>
                         <td>&nbsp;</td>
					                    </tr>
					                    <tr>
					                         <td height="28">&nbsp;</td>
                          <td colspan="2" align="right" valign="middle">Qn 1:&nbsp;&nbsp; </td>
                         <td colspan="11" valign="middle" nowrap="nowrap">
                                   <input name="question1" type="text" class="input" id="question1" value="<?= $question1 ?>" size="55" />
                              &nbsp;<img src="images/datepicker.gif" width="16" height="16" style="cursor: pointer" title="Select Date" onclick="displayDatePicker('date1')"/>&nbsp;
                              <input name="date1" type="text" class="input" id="date1" style="cursor: pointer" title="Select Date" onclick="displayDatePicker('date1')" value="<?= $date1 ?>" size="15" readonly="true"/>
                              <select name="hr1" class="input" id="hr1">
                                   <?= hours($hr1) ?>
                              </select>
                              <input name="min1" type="text" class="input" id="min1" value="<?= $min1 ? $min1 : '00' ?>" size="4" />
                              HRS</td>
                         <td>&nbsp;</td>
					     </tr>
					                    <tr>
					                         <td height="28">&nbsp;</td>
                          <td colspan="2" align="right" valign="middle">Qn 2:&nbsp;&nbsp; </td>
                         <td colspan="11" valign="middle" nowrap="nowrap">
                              <input name="question2" type="text" class="input" id="question2" value="<?= $question2 ?>" size="55" />
                              &nbsp;<img src="images/datepicker.gif" width="16" height="16" style="cursor: pointer" title="Select Date" onclick="displayDatePicker('date2')"/>&nbsp;
                              <input name="date2" type="text" class="input" id="date2" style="cursor: pointer" title="Select Date" onclick="displayDatePicker('date2')" value="<?= $date2 ?>" size="15" readonly="true"/>
                              <select name="hr2" class="input" id="hr2">
                                   <?= hours($hr2) ?>
                              </select>
                              <input name="min2" type="text" class="input" id="min2" value="<?= $min2 ? $min2 : '00' ?>" size="4" />
                              HRS</td>
                         <td>&nbsp;</td>
					     </tr>
					      
						  <tr>
					      <td height="28">&nbsp;</td>
                          <td colspan="2" align="right" valign="middle">Qn 3:&nbsp;&nbsp; </td>
                          <td colspan="11" valign="middle" nowrap="nowrap">
                               <input name="question3" type="text" class="input" id="question3" value="<?= $question3 ?>" size="55" />
                               &nbsp;<img src="images/datepicker.gif" width="16" height="16" style="cursor: pointer" title="Select Date" onclick="displayDatePicker('date3')"/>&nbsp;
                               <input name="date3" type="text" class="input" id="date3" style="cursor: pointer" title="Select Date" onclick="displayDatePicker('date3')" value="<?= $date3 ?>" size="15" readonly="true"/>
                               <select name="hr3" class="input" id="hr3">
                                    <?= hours($hr3) ?>
                              </select>
                              <input name="min3" type="text" class="input" id="min3" value="<?= $min3 ? $min3 : '00' ?>" size="4" />
                               HRS</td>
                         <td>&nbsp;</td>
					     </tr>	
						<? for($i=1; $i<=$moreqns; $i++) { $j=$i+3; $showqn = isset($_POST['question'.$j]) ? 1 : 0; ?>
						 <tr id="row<?= $i ?>">
						    <td id="qn<?= $i ?>ht"></td>
							<td colspan="2" align="right" valign="middle" id="qn<?= $i ?>label">
							<?= $showqn ? 'Qn '.$j.':&nbsp;&nbsp;' : '' ?>							</td>
							<td colspan="11" valign="middle" nowrap="nowrap" id="qn<?= $i ?>fields">
							<?
							  if($showqn) {
							     echo '<input name="question'.$j.'" type="text" class="input" id="question'.$j.'" 
								 value="'.$_POST['question'.$j].'" size="55" />&nbsp;
								 <img src="images/datepicker.gif" style="cursor: pointer" title="Select Date" 
								 onclick="displayDatePicker(\'date'.$j.'\')"/>&nbsp;
								 <input name="date'.$j.'" id="date'.$j.'" type="text" class="input" style="cursor: pointer" 
								 title="Select Date" onclick="displayDatePicker(\'date'.$j.'\')" 
								 value="'.$_POST['date'.$j].'" size="15" readonly="true"/>
								 <select name="hr'.$j.'" class="input" id="hr'.$j.'">'.hours($_POST['hr'.$j]).'</select>
								 <input name="min'.$j.'" id="min'.$j.'" type="text" class="input" value="'.$_POST['min'.$j].'" size="4" /> HRS';
							  }
							?>							</td>
							<td></td>
						 </tr> 
					    <? } ?>
					     <tr>
					      <td height="40">&nbsp;</td>
                          <td>&nbsp;</td>
                          <td>&nbsp;</td>
                          <td colspan="11" valign="middle">
					<input name="submit" type="submit" class="button" id="submit" value="Create Quiz" />
                  <input name="mqbtn" type="button" <?= isset($_POST['question'.$maxqns]) ? 'disabled="disabled"' : '' ?> class="button" id="mqbtn" value="+ More Questions" onclick="moreq(true)"/>
                  <input name="lqbtn" type="button" <?= !isset($_POST['question4']) ? 'disabled="disabled"' : '' ?> class="button" id="lqbtn" onclick="lessq(true)" value="- Less Questions"/>
                                                  <input name="cancel" type="submit" class="button" id="cancel" value="Cancel" />
                                                  <input name="qimportlist" type="hidden" id="qimportlist" value="<?= $qimportlist ?>" />
                              <input name="gimportlist" type="hidden" id="gimportlist" value="<?= $gimportlist ?>" />
                              <input name="wimportlist" type="hidden" id="wimportlist" value="<?= $wimportlist ?>" /></td>
                         <td>&nbsp;</td>
					     </tr>
					                    <tr>
					                       <td height="23">&nbsp;</td>
					                       <td>&nbsp;</td>
					                       <td>&nbsp;</td>
					                       <td>&nbsp;</td>
					                       <td>&nbsp;</td>
					                       <td>&nbsp;</td>
					                       <td width="15">&nbsp;</td>
					                       <td width="43">&nbsp;</td>
					                       <td width="170">&nbsp;</td>
					                       <td width="24">&nbsp;</td>
					                       <td width="16">&nbsp;</td>
					                       <td width="22">&nbsp;</td>
					                       <td width="232">&nbsp;</td>
					                       <td>&nbsp;</td>
					                       <td>&nbsp;</td>
	                        </tr>	
		                    </table>
                       </fieldset></form></td>
                       <td>&nbsp;</td>
                  </tr>
	              <tr>
	                   <td height="30"></td>
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
<?
  if($sendall) { print '<script>tsend(true)</script>'; } 
?>

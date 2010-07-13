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
/* 
 * survey processing
 * Sample message [ keyword 1:a 2:b 3:c 4:b or keyword 1a 2b 3c 4b or keyword a:1 2c 4d c:5 ] 
 * Created on 6th Feb. 2009. TAREHE SITA Day! 
 */
if(!is_array($survey)) {
   exit();
}
$questions = unserialize($survey['questions']); 

/////// Deprecated Code
//$reply = array();
//for($i=1; $i<count($msgtokens); $i++) {
//   $qn = strtolower($msgtokens[$i]);
//   $qn = preg_replace("/:/", "", $qn);
//   if(strlen($qn) != 2) { continue; }
//   $x = preg_replace("/^./", "", $qn);
//   $y = preg_replace("/.$/", "", $qn);
//   if(preg_match("/^[0-9]+$/", $x)) {
//       $reply[] = array('no'=>$x, 'answer'=>$y);
//   } 
//   else {
//      $reply[] = array('no'=>$y, 'answer'=>$x);
//   }
//}

// Split up the message into questions and answers
$ans_splitted = survey_split_answers($message);

// Re-format the returned array to meet Johnson's requirements
$reply = array();
foreach($ans_splitted as $q_ext => $a_ext) {
	$reply[] = array('no' => $q_ext, 'answer' => $a_ext);
}

if(!count($reply)) {
   $replytext = "Please complete the survey for keyword \"$keyword\" and try gain";
}
else {
   $replytext = strlen($survey['reply']) ? $survey['reply'] : 'Thank you for participating in this survey survey';   
}
/* match with answers */
$analysis = NULL;
foreach($reply as $qn) {
   /* check for no. in questions */
   foreach($questions as $question) {
       $found = 0; $ans = NULL;
	   if($question['no'] == $qn['no']) { 
	       /* find answer */
		   $answer = NULL;
		   foreach($question['answers'] as $ans) {
		      if($ans['no'] == $qn['answer']) {
			     $answer = "Answer ($ans[answer])";;
				 break;
			  }
		   }
		   if(is_null($answer)) {
		      $answer = "(No matching answer for answer \"$qn[answer]\")";
		   }
		   $analysis .= "$qn[no]. $question[question]\n$answer\n\n";
		   $found = 1;
		   break;
	   }
   }
   if(!$found) { 
       $warnings .= "Qn \"$qn[no]\" unkown\n"; 
   } 
}
if(isset($warnings)) {
   $replytext = "$replytext\n$warnings";
}

/*
print 'Reply: '.preg_replace("/\n+/", "<br/>", $replytext).'<br/>';
print 'Analysis: '.preg_replace("/\n+/", "<br/>", $analysis);
exit();
*/

/* send results to phone & log */
sendsms($replytext, $sender);
$request = mysql_real_escape_string($_GET['message']);
$reply = mysql_real_escape_string($replytext);
$analysis = mysql_real_escape_string($analysis);
$surveyId = preg_match("/^[0-9]+$/", $survey['id']) ? $survey['id'] : 0;

$sql = "INSERT INTO sresult(date, surveyId, phone, request, reply, analysis) 
        VALUES (NOW(), $surveyId, '$sender', '$request', '$reply', '$analysis')";
doupdate($sql);
//

$surveykeyword = 1;

?>

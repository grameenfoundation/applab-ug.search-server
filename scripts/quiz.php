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
/* Process Quiz question 
 *
 */

function handle_question($question, $rkeyword, $ranswer, $rawSMS, $acknowledge=1) 
{ 
   global $sender, $msgtokens, $correct, $QuizReal; 
   $correct = 0;
   /* get answer */
   $sql = "SELECT * FROM answer WHERE questionId=$question[id]";
   if($GLOBALS['DEBUG_incoming']) {
      $log = date('Y-m-d H:i:s').': called handle_question() with: '.$rawSMS;
	  exec("/bin/echo \"$log\" >>/tmp/debug.txt");
   }
   $result = doquery($sql);
   if(!mysql_num_rows($result)) { 
     /* no answers */
	 sendsms("Sorry, the question \"$question[question]\" does not have answers", $sender);
	 quiz_insert($question[id], $sender, $rawSMS, 0);
	 return;
   }
   if($GLOBALS['DEBUG_incoming']) {
       $log = date('Y-m-d H:i:s').": handle_question() DEBUG: $question[question] found";
       exec("/bin/echo \"$log\" >>/tmp/debug.txt");
   }
   if(!preg_match("/^[0-9]+$/", $ranswer)) { 
       global $quiz;

       sendsms("Sorry, the answer \"$ranswer\" is not valid for Question: $question[question]", $sender);
       quiz_insert($question[id], $sender, $rawSMS, 0);

       return;
   }
   $sql = "SELECT * FROM answer WHERE no='".mysql_real_escape_string($ranswer)."' AND questionId=$question[id]";
   $result = doquery($sql);
   if(!mysql_num_rows($result)) { 
      global $quiz;
      if($quiz['singleKeyword']) {
	      $ranswer = chr($ranswer + 96);
	  }
      sendsms("Sorry, the answer \"$ranswer\" is not valid to the Question: $question[question]", $sender);
      // Delete their number from the delivery record for this question and quiz, so they get the question again

      //$sql = "DELETE FROM qndelivery WHERE (phoneId IN (SELECT id FROM quizphoneno WHERE quizId='{$question[quizId]}' AND phone='$sender') AND questionId='{$question[id]}')";
      //mysql_query($sql);
      quiz_insert($question[id], $sender, $rawSMS, 0);

      return;
   }

   if(($QuizReal != 1) && $acknowledge) {
	   global $quiz;
	   $msg = strlen($quiz['reply']) ? $quiz['reply'] : 'Thank you for participating in this Quiz';
	   sendsms($msg, $sender);    
	   //$correct = 0;
   } else if($QuizReal == 1) {
	   $row = mysql_fetch_assoc($result);
	   if(!$row['correct']) {
	      /* incorrect answer */
	          $wrongreply = $question['wrongReply'];
	          if(!strlen($wrongreply)) {
	              $wrongreply = "Sorry, you got the answer wrong to Question: $question[question]";
        	  }
	         sendsms($wrongreply, $sender);
		 $correct = 0;
	   } else {
		   /* answer correct */
		   $correctreply = $question['correctReply'];
		   if(!strlen($correctreply)) {
		      $correctreply = "Congratulations! You got the answer right to the Question: $question[question]";
		   }
		   sendsms($correctreply, $sender);
		   $correct = 1;
	   }
   }
   if(isset($GLOBALS['single_keyword_logged']) && $GLOBALS['single_keyword_logged']) {
       return;
   }   
   /* Log quiz Participation */
   if($GLOBALS['DEBUG_incoming']) {
      $log = date('Y-m-d H:i:s').': logging '.$rawSMS;
	  exec("/bin/echo \"$log\" >>/tmp/debug.txt");
   }
   quiz_insert($question[id], $sender, $rawSMS, $correct);
   if($GLOBALS['quiz']['singleKeyword']) {
       $GLOBALS['single_keyword_logged'] = true;
   }
}  

function quiz_insert($questionId, $sender, $reply, $correct)
{
   global $QuizReal;

   $reply = mysql_real_escape_string($reply);

   if($QuizReal == 1) {
           $sql = "INSERT INTO quizreply(questionId, phone, reply, correct) VALUES ('$questionId', '$sender', '$reply', '$correct')";
   }
   else {
           $sql = "INSERT INTO quizreply(questionId, phone, reply) VALUES ('$questionId', '$sender', '$reply')";
   }

   doupdate($sql);
}
?>

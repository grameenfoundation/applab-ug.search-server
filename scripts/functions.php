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
 * Second argument can be phone/phones
 *
 */

function kannel_sendsms($message, $phones, $userId=0) 
{
   global $smsuser, $smspass, $shortcode, $GLOBAL_message_origin, $kannelurl, $SoccerTXT;
   
   $phonelist = array();
   if(!is_array($phones)) 
   {
       if(!preg_match("/^[0-9]{9,}$/", $phones)) {
               return array();
           }
           $phonelist[] = $phones;
   }
   else {
      $phonelist = $phones;
   }
   for($i=0; $i<count($phonelist); $i++)
          $phonelist[$i] = get_int_phone_format($phonelist[$i]);

   $text = urlencode(stripslashes($message));
   $message = mysql_real_escape_string($message);
   
   if(strlen($GLOBAL_message_origin)) 
   {
	   $senderPhoneIdNumber = $GLOBAL_message_origin;
   } 
   else {
	   $senderPhoneIdNumber = $shortcode;
   }

   /* phone list should now be a list of valid phone numbers */
   $status_arr = array();
   foreach($phonelist as $phone) 
   {
	   if(preg_match("/^25670/", $phone) || preg_match("/^25675/", $phone)) 
	   {
		    // Zain and warid don't allow sender setting
		    $senderPhoneIdNumber = $shortcode;
	   } 

	   if($SoccerTXT == 1) {
		   if(preg_match("/^25670/", $phone) || preg_match("/^25675/", $phone) || preg_match("/^25671/", $phone)) {
				// Zain and warid don't allow sender setting
				if(preg_match("/^25670/", $phone) || preg_match("/^25675/", $phone)) {
					$senderPhoneIdNumber = $shortcode;
				}
$status = shell_exec("/usr/bin/lynx -dump \"http://127.0.0.1:8081/cgi-bin/sendsms?username=$smsuser&password=$smspass&from=$senderPhoneIdNumber&to=$phone&text=$text\"");
		   } else if(preg_match("/^25678/", $phone) || preg_match("/^25677/", $phone) || preg_match("/^2563/", $phone)) {
$status = shell_exec("/usr/bin/lynx -dump \"http://127.0.0.1:8081/cgi-bin/sendsms?username=sting-sendsms&password=sting-sendsms&from=$senderPhoneIdNumber&to=$phone&text=$text\"");
		   }
	   } else {
		   $status = shell_exec("/usr/bin/lynx -dump \"$kannelurl?username=$smsuser&password=$smspass&from=$senderPhoneIdNumber&to=$phone&text=$text\"");
	   }
	   
       $status = preg_replace("/\n+/", "", $status);
       $status = mysql_real_escape_string(preg_replace("/^\s+/", "", $status));

       /* log SMS */
	   $status_arr[] = $status;
           $sql = "INSERT INTO smslog(date, userId, direction, message, recipient, status)
           VALUES(CURRENT_TIMESTAMP(), $userId, 'OUTGOING', '$message', '$phone', '$status')";
       
	   if(!mysql_query($sql)) 
	        continue;
   }
   // Reset this value so other messages use the default
   $GLOBAL_message_origin = "";
   
   return $status_arr;
}
 
function sendsms($message, $phones, $userId=0) {
   global $SMS_method, $GLOBAL_split_kannel_sms;

   if($SMS_method == 1) {
        // Use Kannel
        if($GLOBAL_split_kannel_sms != 1) {
                return kannel_sendsms($message, $phones);
        }
        else
        {
                $msgs_list = split_sms($message);
                $return_array = array();

                $myMessageOrigin = "";
                if(strlen($GLOBAL_message_origin)) {
                        $myMessageOrigin = $GLOBAL_message_origin;
                }

                foreach($msgs_list as $th_msg) {
                        $GLOBAL_message_origin = $myMessageOrigin;
                        $return_array = kannel_sendsms($th_msg, $phones);
                }

                return $return_array;
        }
   }

   $phonelist = array();
   if(!is_array($phones)) { 
       if(!preg_match("/^[0-9]{9,}$/", $phones)) {
	       return;
	   }
	   $phonelist[] = $phones;
   }
   else {
      $phonelist = $phones;
   }
   for($i=0; $i<count($phonelist); $i++) { 
	  $phonelist[$i] = get_int_phone_format($phonelist[$i]); 
   }
   $message = mysql_real_escape_string($message);
   $text = urlencode($message);
   global $smsuser, $smspass, $shortcode;
   
   /* phone list should now be a list of valid phone numbers */
   foreach($phonelist as $phone) {
	   /* use modem */
	   //$sql = "INSERT INTO msgq(date, recipient, message) VALUES(NOW(), '$phone', '$message')";
	   //if(!mysql_query($sql)) {
	   //   die(mysql_eror());
	   //}

	   modem_send_sms($phone, $message);
	   continue;

	   $status = shell_exec("/usr/bin/lynx -dump \"http://localhost:8081/cgi-bin/sendsms?username=$smsuser&password=$smspass&from=$shortcode&to=$phone&text=$text\""); 
	   $status = preg_replace("/\n+/", "", $status);
	   $status = mysql_real_escape_string(preg_replace("/^\s+/", "", $status));
	   
	   /* log SMS */
	   $sql = "INSERT INTO smslog(date, userId, direction, message, recipient, status) 
	   VALUES(CURRENT_TIMESTAMP(), $userId, 'OUTGOING', '$message', '$phone', '$status')";
	   if(!mysql_query($sql)) continue;	   
   }  
}

function modem_send_sms($phone, $message, $activate_background=0)
{
  global $SMS_method, $Grameen_Temporary_Passthru, $shortcode, $GLOBAL_message_origin, $GLOBAL_split_kannel_sms;

  if($SMS_method == 1) {
	// Use Kannel
	if($GLOBAL_split_kannel_sms != 1) {
		return kannel_sendsms($message, $phone);
	} 
	else 
	{
		$msgs_list = split_sms($message);
		$return_array = array();

		$myMessageOrigin = "";
		if(strlen($GLOBAL_message_origin)) {
			$myMessageOrigin = $GLOBAL_message_origin;
		}

		foreach($msgs_list as $th_msg) {
			$GLOBAL_message_origin = $myMessageOrigin;
			$return_array = kannel_sendsms($th_msg, $phone);
		}

		return $return_array; //kannel_sendsms($message, $phone);
	}
  }

  if($Grameen_Temporary_Passthru != 1) {
	  if(!preg_match("/^\+/", $phone)) {
	      $phone = '+'.$phone;
	  }
  } else {
  	  $phone = get_int_phone_format($phone);
  }

  $msgs_list = split_sms($message);
  $return_array = array();
  foreach($msgs_list as $th_msg) {
    if($Grameen_Temporary_Passthru == 1) {
      $return_array = kannel_sendsms($th_msg, $phone);
    } else {
      if($activate_background == 0) {
	      system("/usr/local/grameen/sendsms ".$phone." \"".$th_msg."\" 2> /dev/null 1> /dev/null");
      } else {
	      system("/usr/local/grameen/sendsms ".$phone." \"".$th_msg."\" 2> /dev/null 1> /dev/null &");
      }
      $return_array[0] = "0: Accepted for delivery";
    }
  }

  return $return_array;
}

function send_instant_sms() {
     $sql = "SELECT * FROM msgq WHERE sent=0";
     $result = doquery($sql); 
     while($row=mysql_fetch_assoc($result)) {
         modem_send_sms($row['recipient'], $row['message']);
	 // Commented the line below because the delay is done in the "sendsms" script
	 // sleep(2);
	 $sql = "UPDATE msgq SET sent=1 WHERE id=$row[id]";
         doupdate($sql);
     }
}

function send_scheduled() {
   send_scheduled_sms();
   send_scheduled_questions();
   send_multiple_questions();
}

/*
 * Track SMS Delivery
 * Delivery Masks are explained as follows
 * 1  Delivered to phone
 * 2  Not-Delivered to Phone
 * 4  Queued on SMSC
 * 8  Delivered to SMSC
 * 16 Not-Delivered to SMSC
 * or'ing gives dlrmask 31 (1|2|4|8|16)
 */
function send_scheduled_sms() {
   global $smsuser, $smspass, $shortcode, $appurl, $logfile;

   $checkStatusInterval = 5;
   $messageCount = 0;

   $sql = "SELECT DISTINCT message.* FROM message left join recipient on messageId=message.id WHERE ((sendTime <= CURRENT_TIMESTAMP()) AND (recipient.status is null) AND (message.messageStatus='1'))"; 
   $result = doquery($sql);
   if(!mysql_num_rows($result)) {
     return;
   }

   $log = date('d/m/Y H:i:s').": Sending scheduled SMS..\n";
   while($row=mysql_fetch_assoc($result)) {
      	  $sql = "SELECT * FROM recipient WHERE messageId={$row[id]} and status is NULL";
	  $result2 = doquery($sql);
	  $total = mysql_num_rows($result);
	  $log .= "Message: ".substr($row['message'], 0, 40).".., ($total) recipients\n";
          $text = urlencode($row['message']);
	  logsystemaction("Processing scheduled message {$row[name]}: ".mysql_num_rows($result2)." recipients");
	  while($row2=mysql_fetch_assoc($result2)) {
	     $dlrurl = urlencode($appurl.'/scripts/record-dlr.php?recipientId='.$row2['id']);
	     $phone = get_int_phone_format($row2['phone']);
	     /*
	     $cmd = "/usr/bin/lynx -dump \"http://localhost:8081/cgi-bin/sendsms?username=$smsuser&password=$smspass&from=$shortcode&to=$phone&text=$text&dlrmask=&31&dlrurl=$dlrurl\"";
	     $status = shell_exec($cmd); 
	     $status = preg_replace("/\n+/", "", $status);
	     $status = mysql_real_escape_string(preg_replace("/^\s+/", "", $status));
		 $message = mysql_real_escape_string($row['message']);
		 
		 // logm SMS for statistical use 
	     $sql = "INSERT INTO smslog(date, direction, message, recipient, status) 
	             VALUES(CURRENT_TIMESTAMP(), 'OUTGOING', '$message', '$phone', '$status')" ;      	     
		 if(!mysql_query($sql)) continue;   		 
		 */
		 global $GLOBAL_message_origin;

		 $GLOBAL_message_origin = "";
		 if(strlen($row['senderPhoneNumber'])) {
			$GLOBAL_message_origin = $row['senderPhoneNumber'];
		 }

		 global $GLOBAL_split_kannel_sms;
		 $GLOBAL_split_kannel_sms = 1;

		 $status_array = modem_send_sms($phone, $row['message']);
		 //sleep(2);
		 /* mark message as sent */ 
		 $sql = "UPDATE message SET sent=1 WHERE id=$row[id]";
		 doupdate($sql);
		 /* set status for recipient */ 
		 $sql = "UPDATE recipient SET status='{$status_array[0]}' WHERE id=$row2[id]";
		 doupdate($sql);
		 $messageCount += 1;

		 if(!($messageCount % $checkStatusInterval)) {
			if(message_sending_stop($row[id])) {
				$messageCount = 0;
				break;
			}
		 }
	  }
   }	
   $log = preg_replace("/\n+$/", "", $log); 
   system("/bin/echo \"$log\" >> $logfile");
}

function send_scheduled_questions() {
   global $smsuser, $smspass, $shortcode, $logfile;

   $threshold_ts = mktime(0, 0, 0, date("m"), date("d"), date("Y"));

   // For people automatically subscribing to quizzes, the query below selects a quiz which has questions whose send time is anytime *today*
   // SELECT quiz.id FROM quiz LEFT JOIN question ON question.quizId=quiz.id WHERE UNIX_TIMESTAMP(question.sendTime) < UNIX_TIMESTAMP(CONCAT(DATE_FORMAT(NOW(), "%Y-%m-%d"), " 23:59:59")) AND UNIX_TIMESTAMP(question.sendTime) > UNIX_TIMESTAMP(CONCAT(DATE_FORMAT(NOW(), "%Y-%m-%d"), " 00:00:00")) ORDER BY question.sendTime ASC

   // The query below ensures we only consider questions which meet the following criteria:
   //   * Send time is set for a time which is greater than 00:00:00 today
   //   * Send time has passed
   // This effectively ensures that we consider only questions due to be sent today. If a
   // question was scheduled for yesterday then it fails the first test. If a question is
   // scheduled for tomorrow then it fails the second test.
   //$sql = 'SELECT * FROM question WHERE (sendTime <= CURRENT_TIMESTAMP() AND UNIX_TIMESTAMP(sendTime) > UNIX_TIMESTAMP(CONCAT(DATE_FORMAT(NOW(), "%Y-%m-%d"), " 00:00:00"))) AND quizId NOT IN (SELECT id FROM quiz WHERE sendall)';

   $sql = 'SELECT question.* FROM question LEFT JOIN quiz ON(quiz.id=question.quizId) WHERE (question.sendTime <= CURRENT_TIMESTAMP() AND UNIX_TIMESTAMP(question.sendTime) > UNIX_TIMESTAMP(CONCAT(DATE_FORMAT(NOW(), "%Y-%m-%d"), " 00:00:00"))) AND quiz.sendall=0 AND quiz.singleKeyword=0';

   $result = doquery($sql);
   if(!mysql_num_rows($result)) {
     return;
   }
   $log = date('d/m/Y H:i:s').": Sending scheduled Questions..\n"; 
   while($row=mysql_fetch_assoc($result)) {
	  /* get answers */
	  $sql = "SELECT * FROM answer WHERE questionId=$row[id] ORDER BY no"; 
	  $result2 = doquery($sql);
	  if(!mysql_num_rows($result2)) { 
	     /* no answers */
		 continue;
	  }	  
	  $answers = NULL;
	  while($row2=mysql_fetch_assoc($result2)) {
	     $answers .= "$row2[no]. $row2[answer]\n";
	  } 
	  $answers = preg_replace("/\n+$/", "", $answers);
	  $question = "$row[question]\n$answers\nTo reply type: $row[keyword] answerNo & send to ".$shortcode;	  
      $text = urlencode($question);
	  
      //$sql = "SELECT * FROM quizphoneno WHERE quizId=$row[quizId]";
	$sql = "SELECT quizphoneno.* FROM quizphoneno WHERE quizphoneno.id NOT IN (SELECT phoneId FROM qndelivery WHERE questionId='{$row[id]}') AND quizphoneno.quizId='{$row[quizId]}'";
	  $result2 = doquery($sql);
	  $total = mysql_num_rows($result);
	  $log .= "Question: ".substr($row['question'], 0, 40).".., ($total) recipients\n"; 
	  //print preg_replace("/\n+/", "<br/>", $question).': <br/>---<br/>'; 

	  /* send question to numbers */
	  while($row2=mysql_fetch_assoc($result2)) {
	     $phone = get_int_phone_format($row2['phone']);
	     /*
	     $cmd = "/usr/bin/lynx -dump \"http://localhost:8081/cgi-bin/sendsms?username=$smsuser&password=$smspass&from=$shortcode&to=$phone&text=$text\"";
	     $status = shell_exec($cmd); 
	     $status = preg_replace("/\n+/", "", $status);
	     $status = mysql_real_escape_string(preg_replace("/^\s+/", "", $status));
		 $message = mysql_real_escape_string($question);
	     	 
		 // logm SMS for statistical use 
	     $sql = "INSERT INTO smslog(date, direction, message, recipient, status) 
	             VALUES(CURRENT_TIMESTAMP(), 'OUTGOING', '$message', '$phone', '$status')" ;      	     
		 if(!mysql_query($sql)) continue;   		 
	      */	 
		 $status_array = modem_send_sms($phone, $question);
		 //sleep(2);
		 /* mark message as sent */
		 $sql = "UPDATE question SET sent=1 WHERE id=$row[id]";
		 doupdate($sql);
		 /* set status for recipient */
		 //$sql = "UPDATE quizphoneno SET status='$status' WHERE id=$row2[id]";
		 $sql = "INSERT INTO qndelivery(questionId, status, phoneId) VALUES($row[id], '{$status_array[0]}', '{$row2[id]}')";
		 doupdate($sql);
	  }
   }	
   $log = preg_replace("/\n+$/", "", $log); 
   system("/bin/echo \"$log\" >> $logfile"); 
}

function send_multiple_questions() 
{
    global $shortcode, $logfile; 
	
	$log = date('D Y-m-d H:i:s').": Sending multiple questions\n";
	$sql = "SELECT * FROM quiz WHERE sendall OR singleKeyword";
	$result = doquery($sql); 
	while($row=mysql_fetch_assoc($result)) 
	{
	     $sql = "SELECT * FROM question WHERE quizId='{$row[id]}' AND sendTime <= CURRENT_TIMESTAMP() AND UNIX_TIMESTAMP(sendTime) > UNIX_TIMESTAMP(CONCAT(DATE_FORMAT(NOW(), '%Y-%m-%d'), ' 00:00:00'))"; 	
	     //$sql = "SELECT * FROM question WHERE quizId='{$row[id]}'"; 			 	 
		 $result2 = doquery($sql);
		 if(!mysql_num_rows($result2)) {
		     $log .= "No questions for Quiz ($row[id])..\n"; 
			 continue;
		 }
		 $log .= "\n\nQuiz \"$row[name]\" ($row[id]):\n";
		 $qnids = array();
		 $questions = NULL;
		 $instruction = NULL;
		 
		 while($row2=mysql_fetch_assoc($result2)) 
		 {
			  $sql = "SELECT * FROM answer WHERE questionId=$row2[id] ORDER BY no";
			  $result3 = doquery($sql);
			  if(mysql_num_rows($result3)) {
			      $qnids[] = $row2['id'];
				  if(!$row['singleKeyword']) {
				     $questions .= "$row2[keyword]: $row2[question]\n";
			         $instruction .= "$row2[keyword] AnswerNo ";
				  }
				  else {
				     $questions .= "$row2[no]: $row2[question]\n";
			         $instruction .= "$row2[no] Ans ";
				  }
			  
			      while($row3=mysql_fetch_assoc($result3)) {
					 $questions .= !$row['singleKeyword'] ? "$row3[no]. $row3[answer]\n" : chr($row3['no'] + 96).". $row3[answer]\n";
			      }
			  }
			  else {
			      $log .= "No answers for question \"$row2[question]\" ($row2[id])..\n";
			  }
		 } 
		 if(!strlen($instruction)) {
		     $log .= "Skiping quiz ($row[id]).\nQuiz $row[id] done..\n";
			 continue;
		 }
		 if(!$row['singleKeyword']) {
		    $questions .= "To reply type: ".trim($instruction)." & SMS to ".$shortcode;
		 }
		 else {
		     $questions .= "To reply type: $row[keyword] ".trim($instruction)." & SMS to ".$shortcode;
		 }
		 $log .= "\nQuiz Message:\n------------------\n$questions\n------------------\n\n";		 
		 $idlist = implode(',', $qnids); 
		 $sql = "SELECT * FROM quizphoneno WHERE quizId='{$row[id]}' AND id NOT IN (SELECT phoneId FROM qndelivery WHERE questionId IN( SELECT id FROM question WHERE quizId='{$row[id]}' ) )"; 

		 $result3 = doquery($sql);
		 $total = mysql_num_rows($result3);
		 while($row3=mysql_fetch_assoc($result3)) 
		 {
		    $phone = get_int_phone_format($row3['phone']);	 
			$log .= "$phone\n";
		    $status_array = modem_send_sms($phone, $questions);
		   	foreach($qnids as $id) {
		        $sql = "INSERT INTO qndelivery(questionId, status, phoneId) VALUES($id, '{$status_array[0]}', '{$row3[id]}')";
		        doupdate($sql);
			}
		 }
		 $log .= "Sent to $total recipient(s)..\n";
		 $log .= "Quiz $row[id] done..\n";
	}
	$fh = fopen($logfile, 'a');
	fwrite($fh, $log);
	//header('content-type: text/plain');
	//print $log;
}

function doquery($sql) {
    if(!($result=mysql_query($sql))) {
	   send_error("SQL: $sql\n".mysql_error());
	   /* not reached */
	   return;
	}   
	return $result;
}
function doupdate($sql) {
    if(!mysql_query($sql)) {
	   send_error("SQL: $sql\n".mysql_error());
	   /* not reached */
	   return;
	}   
}
function send_error($error) { 
    $error = $error."\nsent: ".date('Y-m-d H:i:s');
    mail('mpeirwe@gmail.com', 'Script ERROR', "SMS Script Error\n".$error, 
	   "From: Grameen SMS Script<script@switch1-afol.yo.co.ug>\nBcc:gbegumisa@yo.co.ug");
	exit;
}

/* determines if a keyword is a survey keyword */
function survey_keyword($keyword) {
   $keyword = mysql_real_escape_string($keyword);
   $result = doquery("SELECT * FROM survey WHERE LOWER(keyword)='$keyword'"); 
   if(mysql_num_rows($result)) {
      return mysql_fetch_assoc($result);
   }
   return 0;
}

function quiz_keyword($keyword) 
{
   $keyword = mysql_real_escape_string($keyword);
   $result = doquery("SELECT * FROM question WHERE LOWER(keyword)='$keyword'"); 
   if(mysql_num_rows($result)) {
      $question = mysql_fetch_assoc($result);
      $$result = doquery("SELECT * FROM quiz WHERE id=$question[quizId]");
      $GLOBALS['quiz'] = mysql_fetch_assoc($result);
      return $question;
   }
   return 0;
}

function single_quiz_keyword($keyword) 
{
   $keyword = mysql_real_escape_string($keyword);
   $result = doquery("SELECT * FROM quiz WHERE LOWER(keyword)='$keyword' AND singleKeyword=1"); 
   if(!mysql_num_rows($result)) {
      return 0;
   }
   $GLOBALS['quiz'] = mysql_fetch_assoc($result);
   if($GLOBALS['DEBUG_incoming']) {
      foreach($GLOBALS['quiz'] as $key=>$val) {
         $log .= "$key: $val, ";
      }
      $log = preg_replace("/,\s+$/", "", $log);
      $log = date('Y-m-d H:i:s').': Got Single keyword Quiz: '.$log;
      exec("/bin/echo \"$log\" >>/tmp/debug.txt");
   }
   return 1;
}

function single_quiz_get_question($question_no) 
{
   global $quiz;
   $sql = "SELECT * FROM question WHERE quizId='$quiz[id]' AND no='$question_no'";
   $result = doquery($sql);
   if(!mysql_num_rows($result)) {
       if($GLOBALS['DEBUG_incoming']) {
	      $log = date('Y-m-d H:i:s').": single_quiz_get_question() returned 0 (Query: $sql)";
		  exec("/bin/echo \"$log\" >>/tmp/debug.txt");
	   }
       return 0;
   }
   $question = mysql_fetch_assoc($result);
   if($GLOBALS['DEBUG_incoming']) {
       foreach($question as $key=>$val) {
	      $log .= "$key: $val, ";
	   }
	   $log = preg_replace("/,\s+$/", "", $log);
	   $log = date('Y-m-d H:i:s').': single_quiz_get_question() got Question: '.$log;
	   exec("/bin/echo \"$log\" >>/tmp/debug.txt");
   }
   return $question;
}

function single_quiz_map_answer($char_answer) 
{
   $answer = ord(strtolower($char_answer)) - 96;
   if($GLOBALS['DEBUG_incoming']) {
      $log = date('Y-m-d H:i:s').": single_quiz_map_answer($char_answer) mapped answer to: $answer";
	  exec("/bin/echo \"$log\" >>/tmp/debug.txt");
   }
   return $answer;
}

function general_keyword($keyword) {
   $keyword = mysql_real_escape_string($keyword);
   $sql = "SELECT * FROM keyword WHERE LOWER(keyword)=LOWER('$keyword')"; 
   $result = doquery($sql); 
   if(mysql_num_rows($result)) {
      return mysql_fetch_assoc($result);
   }
   /* may be an alias */
   $sql = "SELECT * FROM keyword WHERE FIND_IN_SET('$keyword', aliases)";
   $result = doquery($sql);
   if(mysql_num_rows($result)) {
      global $actualkeywd;
      $row = mysql_fetch_assoc($result);
      $actualkeywd = $row['keyword'];
      return $row; 
   }
   /* may be in column 'keywordAlias'*/
	$sql = "SELECT * FROM keyword WHERE LOWER(keywordAlias)=LOWER('$keyword')";
    $result = doquery($sql);
	if(mysql_num_rows($result)) {
      global $actualkeywd;
      $row = mysql_fetch_assoc($result);
      $actualkeywd = $row['keyword'];
      return $row; 
   }
   return 0;
}

// Ensure we split on a space
function split_sms($sms)
{
        $tt = ceil(strlen($sms)/156);

        $msgno = 0;
        $msgs = array();
        $cc = 0;

        if(strlen($sms) <= 160) {
                return array(0 => $sms);
        }

        $msgs[$msgno] = "1/".$tt." ";
        for($i = 0; $i < strlen($sms); $i++) {
                if(strlen($msgs[$msgno]) == 160) {
                        // Scroll back to preceding space max 17 characters
                        if($sms[$i] != " ") {
                                $lc = "";
                                $spf = 0;
                                for($j = $i; $j > ($i-17); $j--) {
                                        if($sms[$j] == ' ') {
                                                $spf = 1;
                                                break;
                                        }

                                        $lc = $sms[$j].$lc;
                                }
                                if(!$spf) {
                                        $lc = "";
                                        $j = 0;
                                } else {
                                        $msgs[$msgno] = ($msgno+1)."/".$tt." ";
                                        for($k = ($i - 156); $k < $j; $k++) {
                                                $msgs[$msgno] .= $sms[$k];
                                        }

                                        $i -= strlen($lc) - 1;
                                }
                        }

                        $msgno += 1;
                        $msgs[$msgno] = ($msgno+1)."/".$tt." ";
                }

                $msgs[$msgno] .= $sms[$i];
        }

        $msgcount = count($msgs);
        $new_msgs = array();
        foreach($msgs as $msg) {
                if(preg_match("/^(\d+)\/\d+/", $msg, $matches)) {
                        $new_msgs[] = preg_replace("/^(\d+)\/\d+/", ($matches[1]."/".$msgcount), $msg);
                } else {
                        $new_msgs[] = $msg;
                }
        }

        return $new_msgs;
}

function ybs_log_request($module, $action, $status, $reason, $session_id="")
{
        global $PROVISION_LOGFILE, $msisdn;

        if(!($handle = fopen($PROVISION_LOGFILE, "a")))
                return;

        if(strlen($msisdn)) {
                fputs($handle, date("M j H:i:s ").$module."[$action]: ".$session_id."(".$msisdn.") $status: $reason\n");
        } else {
                fputs($handle, date("M j H:i:s ").$module."[$action]: ".$session_id." $status: $reason\n");
        }

        fclose($handle);

        return;
}

function logic_clean_number($number)
{
        $number = preg_replace("/^[^0-9]+/", "", $number);
        $number = preg_replace("/[^0-9]+$/", "", $number);

        return $number;
}

function try_sms_route($message, $shortCode, $sender)
{
	// There is no need to work on the Hexadecimal stuff, this has already been sorted out
	
	// shortCode sometimes has a +
	$shortCode = preg_replace("/[^0-9]+/", "", $shortCode);
	$message = preg_replace('/\s+/', ' ', $message);
	$words = explode(" ", $message);
	
	$keyword = $words[0];

	// Try routing by short code
	$sql = "SELECT * FROM smsForward WHERE shortCode='".mysql_real_escape_string($shortCode)."'";
	if(!($result = mysql_query($sql))) {
		// Error
		return;
	}

	if(!mysql_num_rows($result)) {
		// Try keyword
		$sql = "SELECT * FROM smsForward WHERE keyword='".mysql_real_escape_string($keyword)."'";
		if(!($result = mysql_query($sql))) {
			// Error
			return;
		}

		if(!mysql_num_rows($result)) {
			// This SMS is not a candidate to be forwarded, both the keyword and short code do not match
			return;
		}
	}
	
	// Now carry out the forwarding basing on the content of $forwardRule
	
	$forwardRule = mysql_fetch_assoc($result);
	
	global $PROVISION_LOGFILE, $msisdn;

	$PROVISION_LOGFILE = "/tmp/forward.log";
	$msisdn = "$sender => $shortCode";

	$message = urlencode($message);
	$http_vars = array();
	$httpVars = unserialize($forwardRule[httpVars]);
	foreach($httpVars as $var => $value){
		if(!strcmp("MESSAGE",$value)){
			$http_vars[$var] = $message;
		}else if(!strcmp("MSISDN", $value)){
			$http_vars[$var] = $sender;
		}else if(!strcmp("BNUMBER", $value)){
			$http_vars[$var] = strlen($forwardRule['newBnumber']) ? $forwardRule['newBnumber'] : $shortCode;
		}
		else {
			$http_vars[$var] = $value;
		}
	}

	logsystemaction("An SMS was forwarded to '$forwardRule[url]' basing on the rules indicated by '$forwardRule[description]'");
	$submission_response = http_submit_request($forwardRule[url], $http_vars, $forwardRule[method]);
	if($submission_response < 0) {
		ybs_log_request("FORWARD", "EXECUTE", "ERROR", "Error forwarding SMS to YBS: "._geterror());
		exit();
	}

	if(!strstr($submission_response[response], "OK")) {
		if(preg_match("/ybs_autocreate_message=(.+)$/", $submission_response[response], $matches)) {
			$errorMessage = urldecode($matches[1]);
		} else {
			$errorMessage = $submission_response[response];
		}
		ybs_log_request("FORWARD", "EXECUTE", "ERROR", "Error forwarding SMS to YBS: ".trim($errorMessage));
	} else {
		
		ybs_log_request("FORWARD", "EXECUTE", "SUCCESS", "Successfully forwarded to YBS msisdn:$sender code:$shortCode");
	}

	// Then, we exit the script immediately after this
	exit();
}



?>

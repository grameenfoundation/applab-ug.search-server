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
 * Process incoming SMS
 *
 */
require '../constants.php';
require '../functions.php';
require '../http.php';
require 'functions.php';

$DEBUG_incoming = 1;

// The nokia N95 and E61i and generally high end phones seem
// to send a sequence of characters f9203f4040404040, just
// before the actual message. We manually strip this off
$hexMessage = bin2hex($_GET['message']);
$msgPlain = $_GET['message'];

if(strstr($hexMessage, "f9203f4040404040")) {
	// Strip the first eight characters
	$newMessage = "";
	for($itext = 8; $itext < strlen($msgPlain); $itext++) {
		$newMessage .= $msgPlain[$itext];
	} 

	$_GET['message'] = $newMessage;
}

if($DEBUG_incoming) {
        $handle = fopen("/tmp/INCOMING_debug.txt", "a");
        fprintf($handle, "%s\n", date("Y-m-d H:i:s")." Sender:".$_GET['sender']." Message:[[".$msgPlain."]] MessageHex:[[".$hexMessage."]] NewMessage:[[".$_GET['message']."]]");
        fclose($handle);
}

dbconnect();
$message = rtrim($_GET['message']);
$sender  = preg_replace("/\+/", "",  $_GET['sender']);
$sender = trim($sender);

if(preg_match("/772712884/", $sender)) {
   exit();
}

if(!strlen($message) || !strlen($sender)) {
   exit();
}

//if(preg_match("/test/i", $message)) {
//        $GLOBAL_message_origin = logic_clean_number($_GET['dest']);
//        $content = "Thank you for testing. If you receive this SMS, the service is up and running.";
//        sendsms($content, $sender);
//        exit();
//}

if(preg_match("/call /i", $message) || preg_match("/add /i", $message)) {
	if(preg_match("/call /i", $message)) {
		$callNow = "yes";
	} else {
		$callNow = "no";
	}
	
	// DIMAGI system forwarding
	$values = get_dimagi_values($message);
	if(!preg_match("/^0[0-9]+$/", $values[phone]) || (strlen($values[phone]) != 10)) {
		$GLOBAL_message_origin = "6969";
		$content = "Error - you have specified an invalid phone number. Please try again";
		sendsms($content, $sender);
		exit();
	}

	// Route this SMS to the IVR server
	global $PROVISION_LOGFILE, $msisdn;

        $PROVISION_LOGFILE = "/tmp/forward.log";
        $msisdn = "$sender => 6969 (DIMAGICALLBACK)";

        $message = urlencode($message);
        $http_vars = array();
	$http_vars[callNow] = $callNow;
        $http_vars[phoneNumber] = $values[phone];
        $http_vars[pin] = $values[pin];
        $http_vars[category] = $values[category];
        $http_vars[startTime] = $values[start];
        $http_vars[stopTime] = $values[stop];

        $submission_response = http_submit_request("http://192.168.1.252/dimagi/ivr/callscript/dimagicall.php", $http_vars, 1);
        if($submission_response < 0) {
                ybs_log_request("FORWARD", "EXECUTE", "ERROR", "Error forwarding SMS to DIMAGI callback: "._geterror());
                exit();
        }

        if(!strstr($submission_response[response], "OK")) {
                if(preg_match("/ybs_autocreate_message=(.+)$/", $submission_response[response], $matches)) {
                        $errorMessage = urldecode($matches[1]);
                } else {
                        $errorMessage = $submission_response[response];
                }
                ybs_log_request("FORWARD", "EXECUTE", "ERROR", "Error forwarding SMS to DIMAGI callback: ".trim($errorMessage));
        } else {
                ybs_log_request("FORWARD", "EXECUTE", "SUCCESS", "Forwarded SMS to DIMAGI callback:$sender code:$dest");
        }

	if(!strcmp($callNow, "yes")) {
		$content = "Your request was processed successfully. {$values[phone]} shall shortly receive a call.";
	} else {
		$content = "Your request was processed successfully. {$values[phone]} has been added to the database.";
	}

        $GLOBAL_message_origin = "6969";
        sendsms($content, $sender);
        exit();
}

# Handle Grameen SMPP short code 178
if(preg_match("/178/", $_GET['dest'])) {

	$sql = "INSERT INTO smslog(date, direction, sender, message, processed) VALUES(NOW(), 'INCOMING', '$sender', '".mysql_real_escape_string($message)."', 1)";
	if(mysql_query($sql)) {
		$_GET['smslogId'] = mysql_insert_id();
		$smslogId = $_GET['smslogId'];
	}
}

// If there is a rule to route this message outside, invoke it
try_sms_route($message, $_GET['dest'], $sender);

if($SoccerTXT == 1) {
	$sql = "INSERT INTO smslog(date, direction, sender, message, processed) VALUES(NOW(), 'INCOMING', '$sender', '".mysql_real_escape_string($message)."', 1)";
	if(mysql_query($sql)) {
		$_GET['smslogId'] = mysql_insert_id();
		$smslogId = $_GET['smslogId'];
	}
}

// Strip the leading spaces
if(preg_match("/^\s+/", $message)) {
        $message = preg_replace("/^\s+/", "", $message);
}
$msgtokens = preg_split("/\s+/", $message);
$keyword = mysql_real_escape_string(strtolower($msgtokens[0]));

if(strlen($message) > 1) {
   /* survey */
   $survey = survey_keyword($keyword); 
   if($survey) { 
      require 'survey.php';
   } 
   /* quiz */
   $question = quiz_keyword($keyword); 
   if($question) 
   { 
      require 'quiz.php';
	  handle_question($question, $msgtokens[0], $msgtokens[1], $message);
	  $i=2;
	  while($i<count($msgtokens)) {
	        $question = quiz_keyword($msgtokens[$i]);
			if($question && ($i+1 < count($msgtokens))) {
			    handle_question($question, $msgtokens[$i], $msgtokens[$i+1], $message, 0);
			}
			$i += 2;
	  }
	  $quizkeyword = 1;
   } 
   /* Single Keyword QUIZ */
   if(single_quiz_keyword($keyword)) {
      require 'quiz.php';
      $my_answers = survey_split_answers($message);
	  $notified = 0;
	  if($DEBUG_incoming) {
	      foreach($my_answers as $key=>$val) {
		     $log .= "$key: $val, ";
		  }
		  $log = preg_replace("/,\s+$/", "", $log);
		  $log = date('Y-m-d H:i:s').': survey_split_answers() got: '.$log;
		  exec("/bin/echo \"$log\" >>/tmp/debug.txt");
	  }
	  foreach($my_answers as $my_qn => $my_ans) {
	     $question = single_quiz_get_question($my_qn);
		 if(!is_array($question)) {
		     if($DEBUG_incoming) {
			     exec("/bin/echo \"Skipped $my_qn => $my_ans\" >>/tmp/debug.txt");
			 }
			 continue;
		 }
		 $mappedAnswer = single_quiz_map_answer($my_ans);
		 
		 if($notified == 0) {
		    if($DEBUG_incoming) {
		       exec("/bin/echo \"notified=$notified, handling Question ".
			   "handle_question($question, $my_ans, $mappedAnswer, $message, 0)..\" >>/tmp/debug.txt");
			} 
		    handle_question($question, $my_ans, $mappedAnswer, $message);
			$notified = 1;
		 }
		 else {
		    if($DEBUG_incoming) {
		       exec("/bin/echo \"notified=$notified, handling Question with ".
			   "handle_question($question, $my_ans, $mappedAnswer, $message, 0)..\" >>/tmp/debug.txt");
			}
		    handle_question($question, $my_ans, $mappedAnswer, $message, 0);
		 }
		 if($DEBUG_incoming) {
		     $log = date('Y-m-d H:i:s').": handled question: handle_question($question, $my_ans, $mappedAnswer, $message, 0)";
			 exec("/bin/echo \"$log\" >>/tmp/debug.txt");
		 }
	  }
	  $quizkeyword = 1;
   }

   // Next Generation Keyword Lookups
   $keywd = keyword_get_content($message);
   if(is_array($keywd)) {
     require 'keyword.php';
   }
}
/* trigger */
if(preg_match("/^[1-9]{1,1}$/", $message)) { 
    $sql = "SELECT tmp.*, TIME_TO_SEC(TIMEDIFF(NOW(), date)) AS time FROM tmp WHERE phone='$sender'"; 
	$result = doquery($sql);
	if(mysql_num_rows($result)) {
	    $row = mysql_fetch_assoc($result); 
		/* get keyword */
		$sql = "SELECT * FROM keyword WHERE id=$row[keywordId]";
		$result = doquery($sql);
		$keyword = mysql_fetch_assoc($result);
		
		/* session should be 2 min (can be anything)*/
		if($row['time'] > 60*$_OTRIGGER_SESSION_DUR) {
		    /* send back content of keyword */
			$content = strlen($keyword['content']) ? $keyword['content'] : 'No Content for keyword: '.$keyword['keyword'];
			//sendsms($content, $sender);
			//$otrigger = 1;
		}
		else {
		   /* session still valid */
		   
		   $sql = "SELECT * FROM otrigger WHERE keywordId=$row[keywordId]"; 
		   $result = doquery($sql);
		   $row = mysql_fetch_assoc($result);
		   $options = unserialize($row['options']);
		   $found = 0; 
		   foreach($options as $option) { 
		      if($option['number'] == $message) { 
			     $found = 1;
				 break;
			  }
		   }
		   if($found) {
		        $sql = "SELECT * FROM keyword WHERE id=$option[tkeywordId]";
				$result = doquery($sql);
				$keyword = mysql_fetch_assoc($result);
				if(strlen($keyword['content'])) {
				   if(preg_match("/::SHORTCODE::$/", $keyword['content'])) {
				       $content = preg_replace("/::SHORTCODE::$/", $GLOBALS['shortcode'], $keyword['content']);
				   }
				}
				else {
				   $content = 'No Content for keyword: '.$keyword['keyword'];
				}   
		   }
		   else {
		        $content = "The option: $message was not found for keyword: ".$keyword['keyword'];
		   }
		   sendsms($content, $sender);
		   $otrigger = 1;
		}
	}
}
$InvalidSMS = 0;
if(!isset($surveykeyword) && !isset($quizkeyword) && !isset($genkeyword) && !isset($otrigger)) { 
   $InvalidSMS = 1;
   if($SoccerTXT != 1) {
           //sendsms("Welcome to SoccerTXT!  Put your football knowledge to the text and win Millions worth of prizes. You will soon receive the first questions. Reply answers to 6969.", $sender);
	   /* unknown Keyword - */ 
	   sendsms("We were unable to match your request to any known application. Please correct any mis-spelled words and try again. Kindly contact us if this error persists.", $sender);
   }
   if(preg_match("/^[0-9]+$/", $_GET['smslogId'])) {
      $sql = "UPDATE smslog SET processed=0 WHERE id=$smslogId";
	  doupdate($sql);
   }
} 

/* log phone */
$sql = "INSERT INTO user(createdate, misdn, hits) VALUES(NOW(), '$sender', 1) ON DUPLICATE KEY UPDATE hits=hits+1";
doupdate($sql);

if($SoccerTXT == 1 && $GLOBAL_sender_removed != 1 && $GLOBAL_sender_added != 1) {
           $sql = 'SELECT quiz.id AS id FROM quiz LEFT JOIN question ON question.quizId=quiz.id WHERE UNIX_TIMESTAMP(question.sendTime) < UNIX_TIMESTAMP(CONCAT(DATE_FORMAT(NOW(), "%Y-%m-%d"), " 23:59:59")) AND UNIX_TIMESTAMP(question.sendTime) > UNIX_TIMESTAMP(CONCAT(DATE_FORMAT(NOW(), "%Y-%m-%d"), " 00:00:00")) ORDER BY question.sendTime ASC';
           if(($result = mysql_query($sql))) {
                if(mysql_num_rows($result)) {
                        $row = mysql_fetch_array($result);
                        if(($result2 = mysql_query("SELECT id FROM quizphoneno WHERE phone='$sender' AND quizId='{$row[id]}'"))) {
                                if(!mysql_num_rows($result2)) {
                                        $sql = "INSERT INTO quizphoneno(quizId, createDate, phone, updated) VALUES('{$row[id]}', NOW(), '$sender', NOW())";
                                        mysql_query($sql);
					sendsms("Welcome to SoccerTXT!Put your football knowledge to the text and win Millions worth of prizes.You will soon receive the first questions. Reply answers to 6969.", $sender);
                                } else {
					if($InvalidSMS == 1) {
						sendsms("The keyword \"$msgtokens[0]\" was not found. Please send a valid keyword.", $sender);
					}
				}
                        }
                }
           }
}

?>

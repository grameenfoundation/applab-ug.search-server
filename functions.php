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
include "scalable_keywords.php";

function check_id($resourceId, $page) {
   if(!preg_match("/^[0-9]+$/", $resourceId)) { 
       goto($page);
   }
}

function dbconnect() 
{
   global $dbhost, $dbuser, $dbpasswd, $dbname;
   error_reporting(0);
   if(!mysql_connect($dbhost, $dbuser, $dbpasswd) || !mysql_select_db($dbname)) {
       die('Connection refused!'); 
   } 
}

function lock_system() 
{
   global $SYSTEM_LOCK;
   $sql = "SELECT GET_LOCK('$SYSTEM_LOCK', 10)";
   if(!($query=mysql_query($sql))) {
       die(mysql_error());
   }
   $row=mysql_fetch_row($query);
   if(!$row[0]) {
       die('Lock failed');
   }
}

function unlock_system($exit=0) {
  global $SYSTEM_LOCK;
  $sql = "SELECT RELEASE_LOCK('$SYSTEM_LOCK')";
  if(!($query = mysql_query($sql))) {
     die(mysql_error());
  }
  $row = mysql_fetch_row($query);
  if(!$row[0]) {
    die('Lock ERROR in: unlock_system');
  }
}

function get_mysql_timestamp($windows_timestamp)
{
    /* $windows_timestamp is in days
	 * $daydiff = difference between 1st Jan 1970 (Unix) and 1st Jan 1900 (Windows) 
	 *
	 */
	$daydiff = 25569; 
	$unix_timestamp = ($windows_timestamp - $daydiff)*86400; 
	return date('Y-m-d H:i:s', $unix_timestamp);
}

function get_virtual_app_text($application) 
{
    switch($application) {
	    case 'GoogleSMS6001':
		{
		    return 'Google SMS';
		}
		case 'HealthIVR': 
		{
		    return 'Health IVR';
		}
		case 'SurveyForms':
		{  
		    return 'Form Surveys';
		}
	}
	return $application;
}

/* 
 * Trim & strip slashes
 */
function strip_form_data ($data) {
  foreach($data as $key=>$val) {
    $data["$key"] = trim(stripslashes($val));
  } 
  return $data;  
}

function escape_form_data($data) {
  foreach($data as $key=>$val) {
    $data["$key"] = mysql_real_escape_string($val);
  } 
  return $data;
}  
  
function generate_rand_str($length) {
 $random = array (
   "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", 
   "L", "M", "N", "0", "P", "Q", "R", "S", "T", "U", "V", 
   "W", "X", "Y", "Z", "a", "b", "c", "d", "e", "f", "g", 
   "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", 
   "s", "t", "u", "v", "w", "x", "y", "z", "0", "1", "2", 
   "3", "4", "5", "6", "7", "8", "9", "."
  );
  for($i=1; $i<=$length; $i++) {
   $rand .= $random[mt_rand(0, 63)];
  }
  return $rand;
}

function show_message($title, $message, $color="#000000") { 
 $message = "<span style=\"color: $color; font-size: 12px\">$message</span>";
 $message = array('title' => $title, 'message' => $message);
 include 'message.php';
 exit;
}

function truncate_str($str, $len) {
   if(!strlen($str)) {
      return 'N/A';
   }
   if(strlen($str) <=$len) {
      return $str;
   }
   return substr($str, 0, $len).'..';	  
}

function get_last_day_of_month($month, $year=NULL) 
{
    if(is_null($year)) {
	    $year = date('Y');
	}
	for ($day = 28; $day < 32; $day++) {
        if (!checkdate($month, $day, $year)) {
		    return $day - 1;
		}
    }
    return $day - 1;
}

function save_tmp_users($sql, $start, $limit) 
{
	$table = "`".md5(get_user_id())."`";
	$_sql = 'CREATE TABLE '.$table.' (id INT UNSIGNED NOT NULL, misdn VARCHAR(40), names VARCHAR(80), hits INT UNSIGNED NOT NULL, createdate DATETIME NOT NULL)'; 
	
	if(!mysql_query('DROP TABLE IF EXISTS '.$table)) {
	    fatal('set_user_tmp_table(): Can not drop table: '.mysql_error());
	}
	if(!mysql_query($_sql)) {
	    fatal('set_user_tmp_table(): Can not create table: '.mysql_error());
	}
	$sql = "$sql LIMIT $start, $limit";
	$result=execute_query($sql);
	while($row=mysql_fetch_assoc($result)) {
	     $activity = get_user_activity($row['misdn'], $row['phones']);
		 $hits = get_total_hits($activity);
		 $names = mysql_real_escape_string($row['names']);
		 $sql = "INSERT INTO $table(id, misdn, names, hits, createdate) 
		 VALUES('$row[id]', '$row[misdn]', '$names', '$hits', '$row[createdate]')";
		 execute_update($sql);
	}
}

function get_row_count($table, $column, $value) {
   return get_item_count($table, $column, $value);
}

function get_item_count($table, $column, $value) {
   $sql = "SELECT COUNT(*) FROM $table WHERE $column='$value'"; 
   if(!($result=mysql_query($sql))) {
      fatal("get_item_count($table, $column, $value): ".mysql_error());
   }   
   $row=mysql_fetch_row($result);
   return $row[0];
}

function get_table_record($table, $match, $column='id') 
{
   $sql = "SELECT * FROM $table WHERE $column='$match'"; 
	if(!($result=mysql_query($sql))) {
	    fatal($sql."<br/>function: get_table_record($table, $match, $column)<br> ".mysql_error());
	}   
	return mysql_fetch_assoc($result);
}

function get_column_value($table, $index, $match, $column='id') {
   $sql = "SELECT * FROM $table WHERE $column='$match'";
	if(!($result=mysql_query($sql))) {
	    fatal($sql."<br/>function: get_table_record($table, $match, $column)<br/>".mysql_error());
	}   
	$row = mysql_fetch_assoc($result);
	return $row[$index] ? $row[$index] : 'N/A';
}

/* 
 * Executes SQL statement and returns a result -
 */
function execute_query($sql, $incl=1) {
 if(!($result=mysql_query($sql))) {
    if($incl) {
	    show_dberror($sql);
	}
	fatal(mysql_error().'<br/>'.$sql);
 }
 return $result;
}

function execute_nonquery($sql, $incl=1) {
 if(!mysql_query($sql)) {
    if($incl) {
	    show_dberror($sql);
	}
	fatal(mysql_error().'<br/>'.$sql);
 }
}
/* Aliase for execute_nonquery */
function execute_update($sql) {
   execute_nonquery($sql);
}

/* display Database errors*/
function show_dberror($sql) {
  show_message('Database Error', "SQL statement is:<br/> <strong>$sql</strong><br/><br/>".mysql_error(), "#FF0000");
}

function redirect($page) {
     goto($page);
}

function goto($page) {
    header("Location: $page");
    exit();
}

function fatal($error) {
   print "<u><strong>ERRORS Occured:</strong></u><br/><br/>$error<br/><br/>";  
   exit;
}

function logsystemaction($action)
{
	$action = mysql_real_escape_string($action);
	$sql = "INSERT INTO logs(username, action) VALUES('BACKEND_PROCESSOR', '$action')";
	execute_nonquery($sql);
}

function logaction($action) {
   $user = get_user_details();
   $action = mysql_real_escape_string($action);
   $sql = "INSERT INTO logs(username, action) VALUES('$user[username]', '$action')";
   execute_nonquery($sql);
}

function unique_field_exists($column, $value, $table, $id=0) 
{
   $value = mysql_real_escape_string($value);
   $sql = "SELECT $column FROM $table WHERE $column='$value'";
   if($id) {
      $sql .= " AND id NOT IN($id)";
   }
   if(!($result=mysql_query($sql))) {
       die(mysql_error());
   }
   return mysql_num_rows($result);
}

function delete_district($districtId) 
{
     global $district, $deleting_district;
	 $deleting_district = true;
	 $district = get_table_record('district', $districtId);
	 if(empty($district)) {
	     return;
	 }	
	 $sql = "SELECT * FROM subcounty WHERE districtId='$districtId'";
	 $result=execute_query($sql);	  
	 while($row=mysql_fetch_assoc($result)) {
	     delete_subcounty($row['id']);
	 }
	 $sql = "DELETE FROM district WHERE id='$districtId'";
	 execute_update($sql);
	 logaction("Deleted District: $district[name]");	 
}

function get_subcounty_from_id($subcountyId) 
{
    $sql = "SELECT subcounty.*, district.name AS district FROM subcounty LEFT JOIN district ON (subcounty.districtId=district.id) WHERE
	subcounty.id='$subcountyId'";
	$result=execute_query($sql);
	return mysql_fetch_assoc($result);
}

function delete_subcounty($subcountyId) 
{
     $subcounty = get_table_record('subcounty', $subcountyId);
	 if(empty($subcounty)) {
	     return;
	 }
	 $sql = "SELECT * FROM user WHERE subcountyId='$subcountyId'";
	 $result=execute_query($sql);
	 if(mysql_num_rows($result)) 
	 {
	      $html = '<ul>';
		  while($row=mysql_fetch_assoc($result)) {
		      $html .= '<li>'.$row['names'].'</li>';
		  }
		  show_message('Can not Delete Subcounty', 'The Subcounty "'.$subcounty['name'].'" '.(isset($GLOBALS['deleting_district']) ? '(Located in
		  '.$GLOBALS['district']['name'].' District)' : '').' can not be deleted because is it associated with the following user record(s): 
		  <br/><br/>'.$html, 'red');
	 }
	 $sql = "DELETE FROM subcounty WHERE id='$subcountyId'";
	 execute_update($sql);
	 logaction("Deleted subcounty: $subcounty[name]");
}

function get_int_phone_format($phone) {
         if(strlen($phone) == 9) {
             /* eg 782694615 */
                 if(preg_match("/^7/", $phone)) {
                    $phone = '256'.$phone;
                 }
          }
          elseif(strlen($phone) == 10) {
             /* eg 0772892819 */
                 $phone = '256'.preg_replace("/^0/", "", $phone);
          }
          return $phone;
}

function get_last_no($questionId) {
	$sql = "SELECT MAX(no) FROM answer WHERE questionId=$questionId";
	$result=execute_query($sql);
	$row = mysql_fetch_row($result);
	$no = $row[0];
	if(!strlen($no)) {
		return 0;
	}
	return $no;
}

function get_all_quiz()
{
	$ret_array = array();

	$sql = "SELECT id, name FROM quiz";
	$result = execute_query($sql);

	if(mysql_num_rows($result)) {
		while(($row = mysql_fetch_array($result))) {
			$ret_array[$row[id]] = $row;
		}
	}

	return $ret_array;
}

function get_quiz_name_from_id($quizId)
{
	$quizInformation = get_quiz_from_id($quizId);
	return $quizInformation[name];
}

function get_quiz_from_id($quizId) 
{
   $sql = "SELECT quiz.*, DATE_FORMAT(createDate, '%d/%m/%Y %r') AS createDate FROM quiz WHERE id=$quizId";
   $result = execute_query($sql);
   return mysql_fetch_assoc($result);
}

function get_question_from_id($questionId) 
{
   $sql = "SELECT * FROM question WHERE id=$questionId";
   $result = execute_query($sql);
   return mysql_fetch_assoc($result);
}

function get_next_question_no($quizId) 
{
   $sql = "SELECT MAX(no) AS max FROM question WHERE quizId=$quizId";
   $result = execute_query($sql);
   $row = mysql_fetch_assoc($result);
   if(!$row['max']) {
       return 1;
   }
   return $row['max'] + 1;
}


function get_filepath_from_id($file_id, $table) 
{
   $sql = "SELECT path FROM $table WHERE id=$file_id";
   $result = execute_query($sql);
   $row = mysql_fetch_row($result);
   return $row[0];
}

function delete_quiz($quizId) 
{
  if(!preg_match("/^[0-9]+$/", $quizId)) {
     return;
  }	
  /* delete all info associated with this quiz */
  $sql = "SELECT * FROM quiz WHERE id=$quizId";
  $result = execute_query($sql);
  if(!mysql_num_rows($result)) {
     return;
  }	 
  $row = mysql_fetch_assoc($result);
  $sql = "DELETE FROM quizreply WHERE questionId IN (SELECT id FROM question WHERE quizId=$quizId)";
  execute_nonquery($sql);

  $sql = "DELETE FROM answer WHERE questionId IN (SELECT id FROM question WHERE quizId=$quizId)";
  execute_nonquery($sql);

  $sql = "DELETE FROM quizphoneno WHERE quizId=$quizId";
  execute_nonquery($sql);

  $sql = "DELETE FROM question WHERE quizId=$quizId";
  execute_nonquery($sql);  
  
  /* delete the quiz! */
  $sql = "DELETE FROM quiz WHERE id=$quizId";
  execute_nonquery($sql);  
  logaction("Deleted quiz: $row[name]");
}   

function delete_question($questionId) { 
  if(!preg_match("/^[0-9]+$/", $questionId)) {
     return;
  }	
  /* delete all info associated with this Question */
  $sql = "SELECT * FROM question WHERE id=$questionId";
  $result = execute_query($sql);
  if(!mysql_num_rows($result)) {
     return;
  }	 
  $row = mysql_fetch_assoc($result);
  $quiz = get_quiz_from_id($row['quizId']);

  $sql = "DELETE FROM quizreply WHERE questionId=$questionId";
  execute_nonquery($sql);

  $sql = "DELETE FROM answer WHERE questionId=$questionId";
  execute_nonquery($sql);

  $sql = "DELETE FROM question WHERE id=$questionId";
  execute_nonquery($sql);    
  
  /* re-order question numbers for SINGLE KEYWORD quiz */
  if($row['no']) {
      $sql = "SELECT * FROM question WHERE quizId=$quiz[id]";
	  $result = execute_query($sql);
	  $no = 1;
	  while($row=mysql_fetch_assoc($result)) {
	      $sql = "UPDATE question SET keyword=NULL WHERE id=$row[id]";
		  execute_update($sql);
		  
		  $keyword = $quiz['keyword'].'_'.$no;
		  $sql = "UPDATE question SET no=$no, keyword='$keyword' WHERE id=$row[id]";
		  execute_update($sql);
		  $no++;
	  }
  }
  logaction("Deleted question: \"$row[question]\" for quiz \"$quiz[name]\"");
} 

function activate_message($messageId)
{
  if(!preg_match("/^[0-9]+$/", $messageId)) {
     return;
  }

  $sql = "SELECT * FROM message WHERE id='$messageId'";
  $result = execute_query($sql);
  if(!mysql_num_rows($result)) {
     return;
  }
  $row = mysql_fetch_assoc($result);

  $sql = "UPDATE message SET messageStatus='1' WHERE id='$messageId'";
  execute_nonquery($sql);
  logaction("Re-activated message: \"$row[name]\"");
}

function message_sending_stop($messageId)
{
  $sql = "SELECT * FROM message WHERE id='$messageId'";
  $result = execute_query($sql);
  if(!mysql_num_rows($result)) {
     // Message no longer there
     logsystemaction("Stopping delivery for message $messageId which no longer exists.");
     return 1;
  }

  $row = mysql_fetch_assoc($result);

  if($row[messageStatus] == "0") {
     logsystemaction("Stopping delivery for message \"$row[name]\"");
     return 1;
  }

  return 0;
}

function deactivate_message($messageId)
{
  if(!preg_match("/^[0-9]+$/", $messageId)) {
     return;
  }

  $sql = "SELECT * FROM message WHERE id='$messageId'";
  $result = execute_query($sql);
  if(!mysql_num_rows($result)) {
     return;
  }
  $row = mysql_fetch_assoc($result);

  $sql = "UPDATE message SET messageStatus='0' WHERE id='$messageId'";
  execute_nonquery($sql);
  logaction("Deactivated message: \"$row[name]\"");
}

function delete_message($messageId) { 
  if(!preg_match("/^[0-9]+$/", $messageId)) {
     return;
  }	
  $sql = "SELECT * FROM message WHERE id=$messageId";
  $result = execute_query($sql);
  if(!mysql_num_rows($result)) {
     return;
  }	 
  $row = mysql_fetch_assoc($result);
    
  $sql = "DELETE FROM recipient WHERE messageId=$messageId";
  execute_nonquery($sql);

  $sql = "DELETE FROM message WHERE id=$messageId";
  execute_nonquery($sql);    
  logaction("Deleted message: \"$row[name]\"");
} 

/*
 * checks if a Keyword exists in the system
 */
function keyword_exists($keyword) {
   $sql = "SELECT * FROM keyword WHERE keyword='$keyword'";
   if(!($result=mysql_query($sql))) {
      return -1;
   }	  
   if(!mysql_num_rows($result)) {
      return 0;
   }
   return 1;	  
}

function get_keyword_from_id($keywordId) {
   if(!preg_match("/^[0-9]+$/", $keywordId)) {
       die('Bad Argument(s)');
   } 
   $sql = "SELECT * FROM keyword WHERE id='$keywordId'";
   if(!($result=mysql_query($sql))) {
      return -1;
   }
   return mysql_fetch_assoc($result);
}
function get_subkeywords($keywordId) {
   if(!preg_match("/^[0-9]+$/", $keywordId)) {
       return NULL;
   } 
   $sql = "SELECT * FROM subkeyword WHERE keywordId='$keywordId' ORDER BY keyword";
   if(!($result=mysql_query($sql))) {
      return -1;
   }
   if(!mysql_num_rows($result)) {
      return 'N/A';
   }
   $list = array();
   while($row=mysql_fetch_assoc($result)) {
      $list[]=$row['keyword'];
   }
   return implode(', ', $list);
}

function get_dictionary_word($keyword) 
{
    $args = preg_split("/\s+/", $keyword);
	$dict_keyword = NULL;
	foreach($args as $arg) {
	    $dict_keyword .= ucfirst($arg); 
	}
	return $dict_keyword;
}

function dictionary_word($keywordId)
 {
    $sql = "SELECT * FROM dictionary WHERE keywordId=$keywordId";
	$result = execute_query($sql);
	return mysql_num_rows($result);
}

/*
 * delete all Keyword information from the system
 */
function delete_keyword($keywordId) {
   if(!preg_match("/^[0-9]+$/", $keywordId)) {
       die('Bad Argument(s)');
   }   
   if(($keyword=get_keyword_from_id($keywordId))==-1) {
      return -1;
  }
  /* delete all info from system */	  
  $sql="DELETE FROM hit WHERE keyword='$keyword[keyword]'";
  execute_update($sql);

  $sql="DELETE FROM otrigger WHERE keywordId='$keywordId'";
  execute_update($sql);
  
  /* delete XXXX_autooutboundN */
  $sql = "DELETE FROM otrigger WHERE keywordId IN(SELECT id FROM keyword WHERE keyword REGEXP '^$keyword[keyword]_autooutbound[0-9]+$')";
  execute_update($sql);
  
  $sql = "DELETE FROM keyword WHERE keyword REGEXP '^$keyword[keyword]_autooutbound[0-9]+$'";
  execute_update($sql);
 
  /* delete normalizations */
  $sql = "DELETE FROM normalizedKeywords WHERE keywordId='$keywordId'";
  execute_update($sql);
 
  /* delete keyword */
  $sql="DELETE FROM keyword WHERE id=$keywordId";
  execute_update($sql);
  
  logaction("Deleted Keyword: '$keyword[keyword]'");
  return 1;    
}

function get_delivery_status($astatus) {
    global $msgstatus;
	foreach($msgstatus as $status) {
	    if($status['status'] == $astatus) {
		   return $status;
		}   
	}
	return array();
}

function get_survey_from_id($surveyId) {
   $sql = "SELECT survey.*, DATE_FORMAT(createdate, '%d/%m/%Y %r') AS createdate, DATE_FORMAT(updated, '%d/%m/%Y %r') AS updated 
           FROM survey WHERE id='$surveyId'";
   if(!($result=mysql_query($sql))) {
      fatal("get_survey_from_id($surveyId): ".mysql_error());
   }
   return mysql_fetch_assoc($result);
}

function get_survey_from_keyword($keyword) {
   $sql = "SELECT * FROM survey WHERE keyword='$keyword'";
   if(!($result=mysql_query($sql))) {
      fatal("get_survey_from_keyword($keyword): ".mysql_error());
   }
   return mysql_fetch_assoc($result);
}

function disociate_keyword_with_survey($keyword) {
   $survey = get_survey_from_keyword($keyword); 
   $sql = "UPDATE survey SET keyword=NULL WHERE keyword='$keyword'";
   if(!mysql_query($sql)) {
      fatal("disociate_keyword_with_survey($keyword): ".mysql_error());
   }
   if(!mysql_affected_rows()) {
      return;
   }
   extract($survey);
   $logstr = "Disociated Keyword \"$keyword\" with survey, Details(Name: $name, Created: $createdate, Send Time: $sendtime)";
   logaction($logstr);
}
/* order questions */
function order_questions($surveyId) {
    /* determine if order is unique */
	$arr = array();
	foreach($_POST as $key=>$val) {
       if(!preg_match("/^q[0-9a-z]+$/", $key)) {
	      continue;
	   }	   
	   $arr[]=$val;
	} 
	if(count($arr) != count(array_unique($arr))) {
	   /* dont update */
	   return;
	}
    $survey = get_table_record('survey', $surveyId);
	$questions = unserialize($survey['questions']);
	foreach($_POST as $key=>$val) {
	   if(!preg_match("/^q[0-9a-z]+$/", $key)) {
	      continue;
	   }
	   for($i=0; $i<count($questions); $i++) {
	      if($questions[$i]['id']==$key) { 
			 $open_k = 'open'.preg_replace("/^q/", "", $key);
			 $open = $_POST[$open_k] ? 1 : 0;
			 $questions[$i]['no'] = $val;
			 $questions[$i]['open'] = $_POST[$open_k] ? 1 : 0;
			 break;
		  }
	   }
	} 
   /* sort the questions */
   $questions=sort_questions($questions); 
   $questions = mysql_real_escape_string(serialize($questions));
   $sql = "UPDATE survey SET questions='$questions' WHERE id=$surveyId";
   execute_update($sql);
   if(!mysql_affected_rows()) {
      return;
   }
   $logstr = "Changed Question order for Survey($survey[name])";
   logaction($logstr);
}

function sort_questions($questions) {
   $nos = get_qn_numbers($questions); 
   $sorted = array();
   foreach($nos as $no) {
      foreach($questions as $qn) {
	     if($qn['no'] == $no) {
		    $sorted[]=$qn;
			break;
		 }
	  }
   }
   return $sorted;
}

function get_qn_numbers($questions) {
   $numbers = array();
   foreach($questions as $q) {
      $numbers[]=$q['no'];
   }
   sort($numbers, SORT_NUMERIC);
   return $numbers;
}

/* order answers */

function order_answers($surveyId, $qno) {
    /* determine if order is unique */
	$arr = array();
	foreach($_POST as $key=>$val) {
       if(!preg_match("/^a[0-9a-z]+$/", $key)) {
	      continue;
	   }	   
	   $arr[]=$val;
	} 
	if(count($arr) != count(array_unique($arr))) {
	   /* dont update */
	   return;
	}
    $survey = get_table_record('survey', $surveyId);
	$questions = unserialize($survey['questions']); 
	$index = 0; 
	for($i=0; $i<count($questions); $i++) {
	   if($questions[$i]['no'] == $qno) {
	       $index = $i; 
		   $question = $questions[$i];
		   break;
	   }
	} 
	$answers = $question['answers'];
	$done = array();
	foreach($_POST as $key=>$val) {
	   if(!preg_match("/^a[0-9a-z]+$/", $key)) {
	      continue;
	   }
	   for($i=0; $i<count($answers); $i++) { 
	      if($answers[$i]['id']==$key) {  
		     $answers[$i]['no'] = $val;
			 $answers[$i]['correct'] = $_POST['correct'.$key]; 
			 break;
		  }
	   }
	} 
	/* sort the answers */
	$answers = sort_answers($answers); 
	/* insert answers back into right question */
   $questions[$index]['answers'] = $answers; 
   $questions = mysql_real_escape_string(serialize($questions));
   $sql = "UPDATE survey SET questions='$questions' WHERE id=$surveyId";
   execute_update($sql);
   if(!mysql_affected_rows()) {
      return;
   }
   $logstr = "Changed Answer order for Survey($survey[name], Question: $question[question])";
   logaction($logstr);	
}

function sort_answers($answers) {
   $nos = get_ans_numbers($answers);  
   $sorted = array();
   foreach($nos as $no) {
      foreach($answers as $ans) {
	     if($ans['no'] == $no) { 
		    $sorted[]=$ans;
			break;
		 }
	  }
   } 
   return $sorted;
}

function get_ans_numbers($answers) {
   $numbers = array();
   foreach($answers as $ans) {
      $numbers[]=$ans['no'];
   }
   sort($numbers);
   return $numbers;
}

function delete_answers($surveyId, $qno) {
    $survey = get_table_record('survey', $surveyId);
	$questions = unserialize($survey['questions']); 
	$index = 0; 
	for($i=0; $i<count($questions); $i++) {
	   if($questions[$i]['no'] == $qno) {
	       $index = $i; 
		   $question = $questions[$i];
		   break;
	   }
	} 
	$answers = $question['answers'];
	$remove = array();
	foreach($_POST as $key=>$val) {
	   if(!preg_match("/^d_/", $key)) {
	     continue;
	   }
	   $remove[]=$val;	  
	}
	if(!count($remove)) {
	    return;
	}
	$newans = array();
	foreach($answers as $ans) {
	   if(in_array($ans['id'], $remove)) {
	       continue;
	   }
	   $newans[]=$ans;
	}
	/* re-arrange the remaining answers */
	for($i=0, $j=97; $i<count($newans); $i++) {
	   $newans[$i]['no'] = sprintf("%c", $j);
	   $j++;
	} 
	/* insert back into correct question */
	$questions[$index]['answers'] = $newans; 
    $questions = mysql_real_escape_string(serialize($questions));
    $sql = "UPDATE survey SET questions='$questions' WHERE id=$surveyId";
    execute_update($sql);
    if(!mysql_affected_rows()) {
        return;
    }
    $logstr = "Removed answer(s) from Survey question($survey[name], Question: $question[question])";
    logaction($logstr);		
}

function delete_survey_question($surveyId, $no) {
   $survey = get_table_record('survey', $surveyId);
   if(!strlen($survey['questions'])) {
      return;
   } 
   $questions = unserialize($survey['questions']);
   $newqns = array();
   foreach($questions as $question) {
      if($question['no'] == $no) {
	     $removed = $question;
		 continue;
	  }
	  $newqns[]=$question;
   } 
   if(count($newqns)==1) {
      $newqns[0]['no'] = 1;
   }
   $questions = mysql_real_escape_string(serialize($newqns));
   $sql = "UPDATE survey SET questions='$questions' WHERE id=$surveyId";
   execute_update($sql);
   if(!mysql_affected_rows()) {
      return;
   }
   $logstr = "Removed Question ($removed[question]) from Survey ($survey[name])";
   logaction($logstr);
}

function delete_survey($surveyId) {
   $survey = get_table_record('survey', $surveyId);
   $questions = unserialize($survey['questions']);
   $total = count($questions);
   
   $sql = "DELETE FROM survey WHERE id='$surveyId'";
   execute_update($sql);
   
   extract($survey);     
   $logstr = "Deleted Survey (Name: $name, Keyword: $keyword, Questions: $total question(s), Send time: $sendtime, Updated: $updated, Created: $createdate)";
   logaction($logstr);
}

function get_html_mresult($form, $survey) {
   $html = '<table border="0" cellpadding="0" cellspacing="0">';
   $data = $form['data'];
   foreach($data as $key=>$val) {
      $html .= '
	  <tr>
	     <td>'.$key.'</td>
		 <td>'.$val.'</td>
	  </tr>';
   }
   $html .= '</table>';
   return $html;
}

function get_active_msurvey() {
   /* get current active survey */
   $sql = "SELECT * FROM msurvey WHERE active";
   $result = execute_query($sql);
   if(!mysql_num_rows($result)) {
      return 0;
   }
   return mysql_fetch_assoc($result);
}

function make_html_fif($fields, $surveyId) {
     /* make FIF */
	 $survey = get_table_record('msurvey', $surveyId);
	 $fif = "<Form>\n";
	 $fif .= "\t<FormName>$survey[name]</FormName>\n\t<FormIdentifier>$surveyId</FormIdentifier>\n";
	 foreach($fields as $field) {
	    $fif .= "\t\t<Field>\n";
		$fif .= "\t\t\t<Title>$field[name]</Title>\n";
		$fif .= "\t\t\t<Type>$field[type]</Type>\n";
		$fif .= "\t\t\t<Code>$field[code]</Code>\n";
		if(preg_match("/checkbox/i", $field['type'])) {
		   foreach($field['options'] as $option) { 
		      //$fif .= "\t\t\t<Entry name=\"".$field['code']."_".$option['value']."\" value=\"".$option['value']."\">".$option['name']."</Entry>\n";
		      $fif .= "\t\t\t<Entry value=\"".$option['value']."\" name=\"".$field['code']."_".$option['value']."\">".$option['name']."</Entry>\n";
		   }
		}
		elseif(preg_match("/radio/i", $field['type']) || preg_match("/menu/i", $field['type'])) {
		   foreach($field['options'] as $option) { 
		      $fif .= "\t\t\t<Entry value=\"".$option['value']."\">".$option['name']."</Entry>\n";  		   
		  }
	    }			
		$fif .= "\t\t</Field>\n";
	 }	    
	 $fif .= "</Form>";
	 $fif = htmlspecialchars($fif);
	 $fif = preg_replace("/\t+/", "&nbsp;&nbsp;&nbsp;&nbsp;", $fif);
	 $fif = preg_replace("/\n+/", "<br/>", $fif);
	 return $fif;
}

function get_xml_descision_logic($surveyId) 
{
     $sql = "SELECT * FROM logic WHERE surveyId=$surveyId";
	 $result = execute_query($sql);
	 if(!mysql_num_rows($result)) {
	    return NULL;
	 }
	 $xml = NULL;
	 while($row=mysql_fetch_assoc($result)) {
	      $xml .= "\t<DecisionLogic>\n";
		  $logic = unserialize($row['logic']);
		  foreach($logic['combination'] as $field) {
		      $xml .= "FLD_$field[fieldno]:$field[answer] ";
		  }
		  $xml .= "CONTENT_DISPLAY:$logic[content]";
		  $xml = $xml."</DecisionLogic>\n"; 
	 } 
	 return $xml;
}

function get_xml_fif($fields, $surveyId) 
{
	 $survey = get_table_record('msurvey', $surveyId);
	 $fif = "<Form>\n";
	 $fif .= "\t<FormName>$survey[name]</FormName>\n\t<FormIdentifier>$surveyId</FormIdentifier>\n";
	 
	 $logic = get_xml_descision_logic($surveyId);
	 if(strlen($logic)) {
	      $fif .= "$logic";
	 }
	 
	 foreach($fields as $field) {
	    $fif .= "\t\t<Field>\n";
		$fif .= "\t\t\t<Title>$field[name]</Title>\n";
		$fif .= "\t\t\t<Type>$field[type]</Type>\n";
		$fif .= "\t\t\t<Code>$field[code]</Code>\n";
		if(preg_match("/checkbox/i", $field['type'])) {
		   foreach($field['options'] as $option) { 
		      //$fif .= "\t\t\t<Entry name=\"".$field['code']."_".$option['value']."\" value=\"".$option['value']."\">".$option['name']."</Entry>\n";
			$fif .= "\t\t\t<Entry value=\"".$option['value']."\" name=\"".$field['code']."_".$option['value']."\">".$option['name']."</Entry>\n";
		   }
		}
		elseif(preg_match("/radio/i", $field['type']) || preg_match("/menu/i", $field['type'])) {
		   foreach($field['options'] as $option) { 
		      $fif .= "\t\t\t<Entry value=\"".$option['value']."\">".$option['name']."</Entry>\n";  		   
		  }
	    }	  
		$fif .= "\t\t</Field>\n";
	 }	    
	 $fif .= "</Form>";
	 return $fif;   
} 

function write_tmp_fif($surveyId) { ;
     global $fifile;
	 $survey = get_table_record('msurvey', $surveyId);
	 $fif = get_xml_fif(unserialize($survey['fif']), $surveyId);    
	 /* write file */
	 if(!($fh=fopen(FIF.'/tmp/'.$fifile, 'w'))) {
	     return 0;
	 }
	 if(!fwrite($fh, $fif)) {
	    return 0;
	 }
	 //logaction("Re-wrote FIF for Mobile Survey (Name: $survey[name], Created: $survey[createdate])");
	 return 1;
}

function rewrite_fif($surveyId) { 
     global $fifile;
	 $survey = get_table_record('msurvey', $surveyId);
	 if(!$survey['active']) {
	    return;
	 }
	 $fif = get_xml_fif(unserialize($survey['fif']), $surveyId);
	 $sql = "SELECT * FROM msurvey WHERE active AND id NOT IN($surveyId)";
	 $result = execute_query($sql);
	 while($row=mysql_fetch_assoc($result)) {
	    $fif .= "\n".get_xml_fif(unserialize($row['fif']), $row['id']);;
	 }
	 /* write file */
	 if(!($fh=fopen(FIF.'/'.$fifile, 'w'))) {
	     show_message('Can not write FIF File', 'Failed to open '.FIF.'/'.$fifile, '#FF0000');
	 }
	 $fif = "<Forms>\n$fif\n</Forms>";
	 fwrite($fh, $fif);
	 logaction("Re-wrote FIF for Mobile Survey (Name: $survey[name], Created: $survey[createdate])");
}

function activate_msurvey($surveyId) {
   global $fifile;
   $survey = get_table_record('msurvey', $surveyId);
   
   /* get current active survey */
   $sql = "SELECT * FROM msurvey WHERE active AND id NOT IN ($surveyId)";
   $result = execute_query($sql);
/*   if(mysql_num_rows($result)) {
      $row = mysql_fetch_assoc($result);
      if($surveyId == $row['id']) {
           return;
      }
	  $previous = "Name: $row[name], Created: $row[createdate]";
   } 
   else {
      $previous = 'NONE';
   }*/
    $fif = get_xml_fif(unserialize($survey['fif']), $surveyId);
    while($row=mysql_fetch_assoc($result)) {
	   $fif .= "\n".get_xml_fif(unserialize($row['fif']), $row['id']);
    }
	/* write file */
	if(!($fh=fopen(FIFDIR.'/'.$fifile, 'w'))) {
	    show_message('Can not write FIF File', 'Failed to open '.FIFDIR.'/'.$fifile, '#FF0000');
	}
	$fif = "<Forms>\n$fif\n</Forms>";
	fwrite($fh, $fif); 
	
	/* Activate survey & de-activate others*/
	if(!$survey['active']) {
	   execute_update("UPDATE msurvey SET active=1 WHERE id=$surveyId");
	}
	//execute_update("UPDATE msurvey SET active=0 WHERE id NOT IN($surveyId)");
	
	$logstr = "Activated mobile survey (Name: $survey[name], Created: $survey[createdate]). Previous active survey ($previous)";
	logaction($logstr);  
}

function deactivate_msurvey($surveyId) {
   global $fifile;
   $survey = get_table_record('msurvey', $surveyId);
   if(!$survey['active']) { 
      //return;
   }
   execute_update("UPDATE msurvey SET active=0 WHERE id=$surveyId");
   /* remove FIF file */
   $logstr = "De-Activated mobile survey (Name: $survey[name], Created: $survey[createdate])";
   $sql = "SELECT * FROM msurvey WHERE active";
   $result = execute_query($sql);
   $fif = NULL;
   while($row=mysql_fetch_assoc($result)) {
      $fif .= "\n".get_xml_fif(unserialize($row['fif']), $row['id']); 
   }
   if(strlen($fif)) {
   	   /* write file */
	   if(!($fh=fopen(FIFDIR.'/'.$fifile, 'w'))) {
	      show_message('Can not write FIF File', 'Failed to open '.FIFDIR.'/'.$fifile, '#FF0000');
	   }
	   $fif = "<Forms>\n$fif\n</Forms>";
	   fwrite($fh, $fif); 
   }
   else {
      if(file_exists(FIFDIR.'/'.$fifile)) {
         if(unlink(FIFDIR.'/'.$fifile)) {
	        $logstr .= ", Deleted FIF File.";
	     }
	     else {
	        $logstr .= ", Error Deleting FIF File!";
	     }
      }
   }
   logaction($logstr); 
}   

function delete_msurvey($surveyId) {
   if(!admin_user()) {
       return;
   }
   $survey = get_table_record('msurvey', $surveyId);
   if($survey['active']) {
      deactivate_msurvey($surveyId);
   }   
   $fif = unserialize($survey['fif']);
   $total = count($fif);

   // Delete results
   $sql = "DELETE FROM mresult WHERE surveyId='$surveyId'";
   execute_update($sql);

   $sql = "DELETE FROM msurvey WHERE id='$surveyId'";
   execute_update($sql);
   
   extract($survey);     
   $logstr = "Deleted Mobile Survey (Name: $name, Fields: $total fields(s), Created: $createdate, Updated: $updated)";
   logaction($logstr);
   if(file_exists(FIFDIR.'/'.$surveyId.'.FIF')) {
       if(!unlink(FIFDIR.'/'.$surveyId.'.FIF')) {
	      return;
	   }	  
   }
}

function sqldate($date) {
   $arr = preg_split("/\//", $date);
   $d = array_shift($arr);
   $m = array_shift($arr);
   $y = array_shift($arr);
   return "$y-$m-$d";
}

function displaydate($date) {
   $arr = preg_split("/-/", $date);
   $y = array_shift($arr);
   $m = array_shift($arr);
   $d = array_shift($arr);
   return "$d/$m/$y";
}

function minutes($hour=0) {
 $time = NULL;
 for($i=0; $i < 59; $i++) {
  if($i == $hour)
   $time .= '<option value="'.$i.'" selected="selected">'.sprintf("%02d", $i).'</option>';
  else $time .= '<option value="'.$i.'">'.sprintf("%02d", $i).'</option>';
 }
 return $time;
}

function hours($hour=0) {
 $time = NULL;
 for($i=0; $i < 24; $i++) {
  if($i == $hour)
   $time .= '<option value="'.$i.'" selected="selected">'.sprintf("%02d", $i).'</option>';
  else $time .= '<option value="'.$i.'">'.sprintf("%02d", $i).'</option>';
 }
 return $time;
}

function days($day) {
 for($i=1; $i<32; $i++) {
  if($i < 10) $j = "0$i"; else $j= $i;
  if($i == $day)
   $month_days .= "<option value='$i' selected>$j</option>";
  else 
   $month_days .= "<option value='$i'>$j</option>";
 }
 return $month_days;
}

 function months($month) {
  for($i=1; $i<13; $i++) {
	$months .= ($i == $month) ? '<option value="'.$i.'" selected="selected">'.
	date("M", mktime(0, 0, 0, $i, 1, date("Y"))).'</option>' : '<option value="'.$i.'">'.
	date("M", mktime(0, 0, 0, $i, 1, date("Y"))).'</option>';
  }
  return $months;
 }

function years($year) {
 for($i = date("Y")-2; $i <= date("Y")+10; $i++) {
  if($i == $year)
   $years .= "<option value='$i' selected>$i</option>";
  else
   $years .= "<option value='$i'>$i</option>";
 }
 return $years;
}

function years_flexible($start=0, $end=0, $year=0, $empty=0) 
{
    if(!$start) {
	    $start = date('Y');
	}
	if(!$end) {
	    $end = date('Y') + 20;
	}
	$years = $empty ? '<option value="0"></option>' : NULL;
	for($i = $start; $i <= $end; $i++) {
	    $years .= '<option value="'.$i.'" '.($i==$year ? 'selected="selected"' : '').'>'.$i.'</option>';
	}
	return $years;
}

function get_user_activity($phone, $phones=NULL) 
{
    $activity = array('quiz'=>0, 'csurvey'=>0, 'msurvey'=>0, 'keyword'=>0);
	
	if(!preg_match("/^[0-9]{9,}$/", $phone)) {
	     return $activity;
	}
	/* quiz */
	$_phone = preg_replace("/^0/", "", $phone);
	  
	$sql = "SELECT COUNT(id) FROM quizreply FORCE KEY(id_key) WHERE phone='$phone' OR LOCATE('$_phone', phone)>0";
	if(strlen($phones)) {
	    $sql .= " OR FIND_IN_SET(phone, '$phones')";  
	}
	$result=execute_query($sql);
	$r_row = mysql_fetch_row($result);
	$activity['quiz'] = $r_row[0];

	/* coded survey */
	$sql = "SELECT COUNT(id) FROM sresult FORCE KEY(id_key) WHERE phone='$phone' OR LOCATE('$_phone', phone)>0";
	if(strlen($phones)) {
	    $sql .= " OR FIND_IN_SET(phone, '$phones')";  
	}
	$result=execute_query($sql);
	$r_row = mysql_fetch_row($result);
	$activity['csurvey'] = $r_row[0];

	/* mobile survey */
	$sql = "SELECT COUNT(id) FROM mresult FORCE KEY(id_key) WHERE phoneId='$phone' OR LOCATE(SUBSTRING(phoneId, 2), '$phone') OR LOCATE('$_phone', phoneId)>0";
	if(strlen($phones)) {
	    $sql .= " OR FIND_IN_SET(phoneId, '$phones')";  
	}
	$result=execute_query($sql);
	$r_row = mysql_fetch_row($result);
	$activity['msurvey'] = $r_row[0];
	
	/* keywords */
	$sql = "SELECT COUNT(id) FROM hit FORCE KEY(id_key) WHERE phone='$phone' OR LOCATE('$_phone', phone)>0";
	if(strlen($phones)) {
	    $sql .= " OR FIND_IN_SET(phone, '$phones')";  
	}
	$result=execute_query($sql);
	$r_row = mysql_fetch_row($result);
	$activity['keyword'] = $r_row[0];
	
	/* Google SMS */
	$sql = "SELECT COUNT(id) FROM GoogleSMS6001 WHERE misdn='$phone' OR LOCATE('$_phone', misdn)>0";
	if(strlen($phones)) {
	    $sql .= " OR FIND_IN_SET(misdn, '$phones')";  
	}
	$result=execute_query($sql);
	$r_row = mysql_fetch_row($result);
	$activity['googlesms'] = $r_row[0];

	/* Health IVR */
	$sql = "SELECT COUNT(id) FROM HealthIVR WHERE misdn='$phone' OR LOCATE('$_phone', misdn)>0";
	if(strlen($phones)) {
	    $sql .= " OR FIND_IN_SET(misdn, '$phones')";  
	}
	$result=execute_query($sql);
	$r_row = mysql_fetch_row($result);
	$activity['healthivr'] = $r_row[0];

	/* Old Form Surveys */
	$sql = "SELECT COUNT(id) FROM oldFormSurveys WHERE msisdn='$phone' OR LOCATE('$_phone', msisdn)>0";
	if(strlen($phones)) {
	    $sql .= " OR FIND_IN_SET(msisdn, '$phones')";  
	}
	$result=execute_query($sql);
	$r_row = mysql_fetch_row($result);
	$activity['formsurveys'] = $r_row[0];
	return $activity;
}

function get_total_hits($activity) 
{
	 $total = 0;
	 foreach($activity as $key=>$val) {
	     $total += $val;
	 }
	 return $total;
}

function get_misdn($phone) 
{
    if(strlen($phone) == 10 && preg_match("/^0/", $phone)) {
	    return preg_replace("/^0/", '256', $phone);
	} 
	if(strlen($phone) == 9 && !preg_match("/^0/", $phone)) {
	    return '256'.$phone;
	}
	if(!preg_match("/^256(\d){3}(\d){6}$/", $phone)) {
	     return $phone;
	}
	return $phone;
}

function get_misdn_strict($phone){
	if(strlen($phone) == 10 && preg_match("/^0/", $phone)) {
	    return preg_replace("/^0/", '256', $phone);
	} 
	if(strlen($phone) == 9 && !preg_match("/^0/", $phone)) {
	    return '256'.$phone;
	}
	if(!preg_match("/^256(\d){3}(\d){6}$/", $phone)) {
	     return 0;
	}
	return $phone;
}

function get_phone_display_label($misdn, $len=30) 
{   
    $label = NULL;
	$_misdn = preg_replace("/^0/", "", $misdn);
	$sql = "SELECT * FROM user WHERE misdn='$misdn' OR LOCATE('$_misdn', misdn)>0 OR FIND_IN_SET('$misdn', phones) OR (phones LIKE '%$_misdn%')"; 
	if(!($result=mysql_query($sql))) {
	    return 'ERROR';
	}
	if(!mysql_num_rows($result)) {
	    $label = $misdn;
	}
	$row=mysql_fetch_assoc($result);
	if(!strlen($row['names'])) {
	    $label = $misdn;
	} 
	else {
	     $label = truncate_str($row['names'], $len).' ['.$misdn.']';
	}
	if(preg_match("/^[0-9]{8,}$/", $misdn) && !preg_match("/xls\./", $_SERVER['REQUEST_URI'])) {
	    $userId = strlen($row['id']) ? $row['id'] : 0;
	    $label = '<a href="#" onclick="editinfo('.$userId.', \''.$_SERVER['REQUEST_URI'].'\', \''.$misdn.'\');return false;" style="color: #000000" 
		title="Click to Edit Details">'.$label.'</a>';
		$_SERVER['_p_url'] = $_SERVER['REQUEST_URI'];
	}
	
	return $label;
}

function clear_working_list() {
    if(isset($_POST['wimportlist']) && strlen($_POST['wimportlist'])) {
	     execute_update('DELETE FROM workinglist');
	}
}

function get_location_from_misdn($misdn){
	$_misdn = preg_replace("/^0/", "", $misdn);
	$sql = "SELECT * FROM user WHERE misdn='$misdn' OR LOCATE('$_misdn', misdn)>0 OR FIND_IN_SET('$misdn', phones) OR (phones LIKE '%$_misdn%')"; 
	if(!($result=mysql_query($sql))) {
	    return 'Error';
	}
	if(!mysql_num_rows($result)) {
	    return  'location_unknown';
	}
	$row=mysql_fetch_assoc($result);
	if(!strlen($row['location'])) {
	    return 'location_unknown';
	}
	return $row['location'];
}

/* Returns array of the format:
 * $array[$question_number] = $answer
 */
function survey_split_answers($text)
{
        $retarray = array();
        $question_inprogress = "";
        $qnstored = 0;
        for($i = 0; $i < strlen($text); $i++) {
                if(preg_match("/^[0-9]+$/", $text[$i])) {
                        if($qnstored == 0) {
                                $question_inprogress = $text[$i];
                        } else {
                                $question_inprogress .= $text[$i];
                        }
                        $qnstored = 1;
                        continue;
                } else {
                        $qnstored = 0;
                }
                if(strlen($question_inprogress)) {
                        $retarray[$question_inprogress] .= $text[$i];
                }
        }

        // Strip leading colon, leading space, and trailing space, trailing dot
        $newarray = array();
        foreach($retarray as $k => $v) {
                while(preg_match("/^\s/", $v))
                        $v = preg_replace("/^\s/", "", $v);
                while(preg_match("/^:/", $v))
                        $v = preg_replace("/^:/", "", $v);
                while(preg_match("/^\s/", $v))
                        $v = preg_replace("/^\s/", "", $v);
		while(preg_match("/\.$/", $v))
			$v = preg_replace("/\.$/", "", $v);

                $v = trim($v);

                while(preg_match("/\.$/", $v))
                        $v = preg_replace("/\.$/", "", $v);

		$v = trim($v);

                $newarray[$k] = $v;
        }

        return $newarray;
}

function xls_add_titletext(&$xls_rsx, $text)
{
        if(!count($xls_rsx) || !is_array($xls_rsx)) {
                $xls_rsx = array();
                $xls_rsx[columns] = array();
                $xls_rsx[rows] = array();
                $xls_rsx[rowcount] = 0;
                $xls_rsx[titletext] = array();
        }

        $xls_rsx[titletext][] = $text;
}

function xls_add_title_field(&$xls_rsx, $field)
{
        if(!count($xls_rsx) || !is_array($xls_rsx)) {
                $xls_rsx = array();
                $xls_rsx[columns] = array();
                $xls_rsx[rows] = array();
                $xls_rsx[rowcount] = 0;
                $xls_rsx[titletext] = array();
        }

        $xls_rsx[columns][] = $field;
}

function xls_add_row_field(&$xls_rsx, $field)
{
        $rowidx = $xls_rsx[rowcount];
        $xls_rsx[rows][$rowidx][] = $field;
}

function xls_new_row(&$xls_rsx)
{
        $rowidx = $xls_rsx[rowcount];
        if(!is_array($xls_rsx[rows][$rowidx])) {
                $xls_rsx[rows][$rowidx] = array();
                foreach($xls_rsx[columns] as $this_col) {
                        $xls_rsx[rows][$rowidx][] = "";
                }
        }
        $xls_rsx[rowcount] += 1;
}

function xls_get_array($xls_rsx)
{
        $data = array();

        $i = 0;
        if(count($xls_rsx[titletext])) {
                $xls_rsx[titletext][] = "";

                foreach($xls_rsx[titletext] as $text) {
                        $data[$i][""] = $text;
                        $i++;
                }

                foreach($xls_rsx[columns] as $colpos => $colname) {
                        $data[$i][$colname] = $colname;
                }
                $i++;
        }

        foreach($xls_rsx[rows] as $this_row) {
                foreach($xls_rsx[columns] as $colpos => $colname) {
                        $data[$i][$colname] = $this_row[$colpos];
                }
                $i++;
        }

        return $data;
}

function xls_write_file($filepath, $data)
{
        $fp = fopen("xlsfile:/$filepath", "wb");

        if(!is_resource($fp)) {
                return 0;
        }

        fwrite($fp, serialize($data));

        fclose($fp);

	return 1;
}

function logic_lock_cronsms()
{
	return logic_lock_system("YCPPQUIZ_SMS_PHPCRON_SEND");
}

function logic_unlock_cronsms()
{
	return logic_unlock_system("YCPPQUIZ_SMS_PHPCRON_SEND");
}

function logic_lock_system($lock)
{
        if(!($result = mysql_query("SELECT GET_LOCK('$lock', 45)"))) {
                return -1;
        }

        $row = mysql_fetch_row($result);
        if(!strlen($row[0])) {
                return -1;
        }

        if($row[0] == 0) {
                return -1;
        }

        return 0;
}

function logic_unlock_system($lock)
{
        mysql_query("SELECT RELEASE_LOCK('$lock')");
}

function str_hex($string){
    $hex='';
    for ($i=0; $i < strlen($string); $i++){
        $hex .= (strlen($hex) ? " ".dechex(ord($string[$i])) : dechex(ord($string[$i])));
    }
    return $hex;
}


function hex_str($hex){
    $string='';
    for ($i=0; $i < strlen($hex)-1; $i+=2){
        $string .= chr(hexdec($hex[$i].$hex[$i+1]));
    }
    return $string;
}

function _validinteger($d)
{
        return preg_match("/^[0-9]+$/", $d);
}

function get_dimagi_values($sms)
{
        $arr = preg_split("/\s/", $sms);
        $retarray = array();
        $retarray[phone] = $arr[1];
        $retarray[pin] = $arr[2];
        $retarray[category] = $arr[3];
        $retarray[start] = $arr[4];
        $retarray[stop] = $arr[5];

        return $retarray;
}

// sort an array containing associative arrays using an index within the associative array
function msort($array, $id, $sort_ascending=true) {
        $temp_array = array();
        while(count($array)>0) {
            $lowest_id = 0;
            $index=0;
            foreach ($array as $item) {
                if (isset($item[$id])) {
                                    if ($array[$lowest_id][$id]) {
                    if (strtolower($item[$id]) < strtolower($array[$lowest_id][$id])) {
                        $lowest_id = $index;
                    }
                    }
                                }
                $index++;
            }
            $temp_array[] = $array[$lowest_id];
            $array = array_merge(array_slice($array, 0,$lowest_id), array_slice($array, $lowest_id+1));
        }
                if ($sort_ascending) {
            return $temp_array;
                } else {
                    return array_reverse($temp_array);
                }
    }

// insert an numeric indexed element in an array at a specific position / index
function array_insert(&$array, $insert, $position = -1) {
     $position = ($position == -1) ? (count($array)) : $position ;
     if($position != (count($array))) {
          $ta = $array;
          for($i = $position; $i < (count($array)); $i++) {
               if(!isset($array[$i])) {
                    die(print_r($array, 1)."\r\nInvalid array: All keys must be numerical and in sequence.");
               }
               $tmp[$i+1] = $array[$i];
               unset($ta[$i]);
          }
          $ta[$position] = $insert;
          $array = $ta + $tmp;
          //print_r($array);
     } else {
          $array[$position] = $insert;
     }

     ksort($array);
     return true;
}

//returns 0, or valid number 2567...
function get_valid_reply_msisdn($msisdn){
	//$msisdn 07... , 7...
	if(($_msisdn = get_misdn_strict($msisdn))!=0){
		return $_msisdn;//2567...
	}
	$sql = "SELECT * FROM user WHERE misdn='$misdn'";
	if(!($result=mysql_query($sql))){
		return 0;
	}
	if(!mysql_num_rows($result)){
		return 0;
	}
	
	$row=mysql_fetch_assoc($result);
	$_phones = preg_split("/,(\s)*/", $row['phones']);
	
	foreach($_phones as $_phone){
		if(($_msisdn=get_misdn_strict($_phone)) != 0 ){
			return $_msisdn;
		}
	}
	return 0; //totally failed to get a matched
}

function get_survey_count($phoneId, $surveyId){
	if(!($phoneId && $surveyId)){
		return -1;
	}
	$sql = "SELECT count(*) as total FROM mresult WHERE phoneId ='$phoneId' and surveyId='$surveyId'";
	if(!($result=mysql_query($sql))){
		return -1;
	}
	$row=mysql_fetch_assoc($result);
	return $row['total'];
}

function keyword_remove_words($a, $p)
{
	foreach($a as $word) {
		while(strstr($p, " $word ")) {
			$p = str_replace(" $word ", " ", $p);
		}
	}

	$p = preg_replace("/\s+/", " ", $p);

	return $p;
}

function keyword_remove_redundant_words($k)
{
	$k = strtolower($k);

	$useless_nouns = array("i", "him", "her", "it", "you", "would", "me", "soon");
	$coordinating_conjunctions = array("for", "and", "nor", "but", "or", "yet", "so");
	$correlative_conjunctions = array("both", "not", "and", "only", "but", "also", "either", "or", "neither", "nor", "whether", "or");
	$subordinating_conjunctions = array("after", "because", "although", "if", "before", "since", "though", "unless", "when", "now", "that", "even", "though", "only", "if", "while", "as", "whereas", "whether", "or", "not", "since", "in", "order", "that", "while", "even", "if", "until", "so", "in", "that");
	$conjunctive_adverbs = array("however", "moreover", "nevertheless", "consequently");
	$prepositions= array("aboard", "about", "above", "across", "after", "against", "to", "at", "by", "for", "in", "of", "off", "on", "onto", "out", "up", "via", "the");
	$more_prepositions=array("aboard","about","above","across","after","against","along","alongside","amid","amidst","among","amongst","around","as","aside","astride","at","athwart","atop","barring","before","behind","below","beneath","besides","between","beyond","by","circa","concerning","despite","down","during","except","excluding","failing","following","for","from","given","in","including","inside","into","like","mid","minus","near","next","notwithstanding","of","off","on","onto","opposite","out","outside","over","pace","past","per","plus","regarding","round","save","since","than","through","throughout","till","times","to","toward","towards","under","underneath","unlike","until","up","upon","versus","via","with","within","without","worth","as far as","as well as","by means of","in accordance with","in addition to","in case of","in front of","in lieu of","in place of","in spite of","on account of","on behalf of","on top of","with regard to","with respect to","according to","ahead of","as regards","asper","aside from","because of","close to","due to","except for","far from","in to","inside of","instead of","near to","next to","on to","out from ","out of","outside of","owing to","prior to","pursuant to","regardless of","subsequent to","that of");
	
	
	$k = keyword_remove_words($useless_nouns, $k);
	$k = keyword_remove_words($coordinating_conjunctions, $k);
	$k = keyword_remove_words($correlative_conjunctions, $k);
        $k = keyword_remove_words($subordinating_conjunctions, $k);
        $k = keyword_remove_words($conjunctive_adverbs, $k);
        $k = keyword_remove_words($prepositions, $k);
				$k = keyword_remove_words($more_prepositions,$k);

	return $k;
}

function keyword_cleanup($k)
{
	// Lowercase
	$k = strtolower($k);

	// Replace underscores with spaces
	$k = preg_replace("/[_]+/", " ", $k);

	// Remove any punctuation, apart from commas and replace with space
	$k = preg_replace("/[^A-Za-z0-9,]+/", " ", $k);

	// Remove commas
	$k = preg_replace("/[,]+/", "", $k);

	// Replace multiple spaces with a single space
	$k = preg_replace("/\s+/", " ", $k);

        // Trim leading / trailing spaces
        $k = trim($k);

	// Convert into array
	$k_a = explode(" ", $k);

	// Remove duplicate words
	$k_a = array_flip(array_flip($k_a));

	return $k_a;
}

function keyword_normalize($k)
{
	//print "Before: |$k|\n";

	$k_a = keyword_cleanup($k);

	// Arrange in alphabetical order
	sort($k_a, SORT_STRING);

	// Re-create the string
	$k = implode(" ", $k_a);

	//print "After: |$k|\n";

	// Return MD5 hash
	return md5($k);
}

// This function takes the output of keyword_forward_resolve_sms() and
// carries out a search against the aliases table using each of the
// words to try and see if an alias was entered for any of the valid
// words.  See the explanation above keyword_get_content() to understand
// the meaning of this.
function keyword_reverse_resolve_sms($sms)
{
	global $GLOBAL_reverse_resolved_sms_list, $DEBUG_mode;

	if(!isset($GLOBAL_reverse_resolved_sms_list) || !is_array($GLOBAL_reverse_resolved_sms_list) || !strlen($GLOBAL_reverse_resolved_sms_list)) {
		$GLOBAL_reverse_resolved_sms_list = array();
	}

	$s_md5_h = md5($sms);

	// If we have processed this SMS before, return what is in our cache to save time
	if(isset($GLOBAL_reverse_resolved_sms_list[$s_md5_h])) {
		if($DEBUG_mode == 1) {
			print "keyword_reverse_resolve_sms: returning cached result key:$s_md5_h value:{$GLOBAL_reverse_resolved_sms_list[$s_md5_h]}\n";
		}
		return $GLOBAL_reverse_resolved_sms_list[$s_md5_h];
	}

	$forward_resolved_sms = keyword_forward_resolve_sms($sms);
	if(!strlen($forward_resolved_sms)) {
		$GLOBAL_reverse_resolved_sms_list[$s_md5_h] = "";
		return "";
	}

	$sms_a = keyword_cleanup($forward_resolved_sms);
	if(count($sms_a) == 1 && !strlen($sms_a[0])) {
		// SMS doesn't resolve to anything valid
		$GLOBAL_reverse_resolved_sms_list[$s_md5_h] = "";
                return "";
	}

	$word_idx = array();
	foreach($sms_a as $word) {
		$word_idx[$word] = "";
	}

	$sms_q = "\"".implode("\", \"", $sms_a)."\"";
	$sql = "SELECT dictionary.word AS real_word, aliases.alias AS alias FROM aliases LEFT JOIN dictionary ON dictionary.id=aliases.word_id WHERE aliases.alias IN($sms_q)";
	if(!($result = mysql_query($sql))) {
                send_error("Database error: ".mysql_error()." SQL: ".$sql);
                return -1;
        }

	if(!mysql_num_rows($result)) {
		$GLOBAL_reverse_resolved_sms_list[$s_md5_h] = implode(" ", $sms_a);
		return $GLOBAL_reverse_resolved_sms_list[$s_md5_h];
	}

	while(($row = mysql_fetch_array($result))) {
		$word_idx[$row[alias]] = $row[real_word];
	}

	$resolved_sms = array();
	foreach($word_idx as $k => $v) {
		if(!strlen($v)) {
			$resolved_sms[] = $k;
		} else {
			$resolved_sms[] = $v;
		}
	}

	$GLOBAL_reverse_resolved_sms_list[$s_md5_h] = implode(" ", $resolved_sms);
	return $GLOBAL_reverse_resolved_sms_list[$s_md5_h];
}

// This function forward-resolves an SMS. What this means is that
// we check the SMS to get words which are in the dictionary or
// words which are in the aliases table
function keyword_forward_resolve_sms($sms)
{
	global $GLOBAL_forward_resolved_sms_list, $DEBUG_mode;

	if(!isset($GLOBAL_forward_resolved_sms_list) || !is_array($GLOBAL_forward_resolved_sms_list) || !strlen($GLOBAL_forward_resolved_sms_list)) {
		$GLOBAL_forward_resolved_sms_list = array();
	}

	$s_md5_h = md5($sms);

	// If we have processed this SMS before, return what is in our cache to save time
	if(isset($GLOBAL_forward_resolved_sms_list[$s_md5_h])) {
		if($DEBUG_mode == 1) {
			print "keyword_forward_resolve_sms: returning cached result key:$s_md5_h value:{$GLOBAL_forward_resolved_sms_list[$s_md5_h]}\n";
		}
		return $GLOBAL_forward_resolved_sms_list[$s_md5_h];
	}

	$sms_a = keyword_cleanup($sms);
	if(count($sms_a) == 1 && !strlen($sms_a[0])) {
		// SMS doesn't resolve to anything valid
		$GLOBAL_forward_resolved_sms_list[$s_md5_h] = "";
		return "";
	}

	$word_idx = array();
	foreach($sms_a as $word) {
		$word_idx[$word] = "";
	}

	$sms_q = "\"".implode("\", \"", $sms_a)."\"";
	$sql = "SELECT word FROM dictionary WHERE word IN($sms_q)";
	if(!($result = mysql_query($sql))) {
		send_error("Database error: ".mysql_error()." SQL: ".$sql);
		return -1;
	}

	if(mysql_num_rows($result)) {
		while(($row = mysql_fetch_row($result))) {
			$word_idx[$row[0]] = "found";
		}
	}

	$resolved_sms = array();
	$sms_q = "";
	foreach($word_idx as $k => $v) {
		if(!strcmp($v, "found")) {
			$resolved_sms[] = $k;
			continue;
		}

		if(strlen($sms_q)) {
			$sms_q .= ", '".$k."'";
		} else {
			$sms_q .= "'$k'";
		}
	}

	// If there are words which have not yet been found, try to resolve them from the aliases table
	if(strlen($sms_q)) {
		$sql = "SELECT dictionary.word AS real_word, aliases.alias AS alias FROM aliases LEFT JOIN dictionary ON dictionary.id=aliases.word_id WHERE aliases.alias IN($sms_q)";
		if(!($result = mysql_query($sql))) {
			send_error("Database error: ".mysql_error()." SQL: ".$sql);
			return -1;
		}

		if(mysql_num_rows($result)) {
			while(($row = mysql_fetch_array($result))) {
				$resolved_sms[] = $row[real_word];
			}
		}
	}

	//$resolved_sms = keyword_cleanup(implode(" ", $resolved_sms));
	//print "Resolved SMS: |".implode(" ", $resolved_sms)."|\n";

	if(!count($resolved_sms)) {
		$GLOBAL_forward_resolved_sms_list[$s_md5_h] = "";
		return "";
	}

	$GLOBAL_forward_resolved_sms_list[$s_md5_h] = implode(" ", $resolved_sms);
	return $GLOBAL_forward_resolved_sms_list[$s_md5_h];
}

function keyword_resolve_sms($sms, $dir='forward')
{
	switch($dir)
	{
	case "forward":
		$r_sms = keyword_forward_resolve_sms($sms);
		if(!strlen($r_sms)) {
			return "";
		}

		return keyword_normalize($r_sms);
	case "reverse":
		$r_sms = keyword_reverse_resolve_sms($sms);
		if(!strlen($r_sms)) {
                        return "";
                }

                return keyword_normalize($r_sms);
	case "forward_trimmed":
		$r_sms = keyword_forward_resolve_sms($sms);
		if(!strlen($r_sms)) {
			return "";
		}

		$r_sms = keyword_remove_redundant_words($r_sms);

		return keyword_normalize($r_sms);
	case "reverse_trimmed":
		$r_sms = keyword_reverse_resolve_sms($sms);
		if(!strlen($r_sms)) {
                        return "";
                }

		$r_sms = keyword_remove_redundant_words($r_sms);

                return keyword_normalize($r_sms);
	case "mixed":
		// This is to be implemented as an experimental
		// method which attempts to provide a list of
		// normalized values created as a result of
		// mixing both the output of the forward and
		// reverse resolved SMS. The idea is to cater
		// for a case where some words are aliased and
		// others are not. This currently is not
		// implemented.
		$f_res = keyword_forward_resolve_sms($sms);
		$r_res = keyword_reverse_resolve_sms($sms);

		$mixed_words = $f_res." ".$r_res;

		return "";
	default:
		return "";
	}
}

function keyword_try_get_content($sms, $direction)
{
	if(($rk = keyword_resolve_sms($sms, $direction)) < 0) {
		send_error("Failed to successfully resolve SMS $sms");
		// Error occurred, failed to resolve
		return 0;
	}

	if(!strlen($rk)) {
		// SMS doesn't resolve to any keywords (porter-stemmer?)
		return 0;
	}

	$sql = "SELECT keyword.* FROM normalizedKeywords LEFT JOIN keyword ON normalizedKeywords.keywordId=keyword.id WHERE normalizedKeyword='$rk' ORDER BY normalizedKeywords.wordCount DESC LIMIT 1";
	if(!($result = mysql_query($sql))) {
		send_error("Database error: ".mysql_error()." SQL: ".$sql);
		return 0;
	}

	if(!mysql_num_rows($result)) {
		return 0;
	}

	// We are returning an associative array because this is what the front-end is expecting
	return mysql_fetch_assoc($result);
}

// XXX Cater for outbound triggers in the function which calls this one
// XXX if we fail to find content using a standard-resolved SMS, then we
//     re-run the resolution, this time replacing words in the resolved SMS
//     with their alias equivalent to cater for the case where the system
//     administrator has specified an alias which also exists as a keyword
//     independently. For example if our keyword is "matooke mbale price"
//     and, the aliases for "matooke" are "banana, bananas, matoke, matke".
//     Assume, also that there is another keyword which is "bananas diseases"
//     Now, assume that a subscriber sends the message "I would like to know
//     the price of bananas in mbale".  The resolved SMS shall be
//     "price bananas mbale", because of the existance of another keyword
//     which uses the word "bananas" - hence "bananas" is not checked for
//     in the aliases table. Now, this shall not match the keyword
//     "matooke mbale price".  However, in the second run, we need to
//     now run "matooke mbale price" through the aliases table and see if
//     either of the words is an alias for another word.  Doing this shall
//     now result in us resolving the SMS to "matooke mbale price", correctly.
// XXX If we still fail to find the content using the above two methods,
//     then we shall, as a final resort, use the porter-stemmer algorithm
//     to try and convert the words of our resolved SMS (before the second
//     run through the aliases table) to their stems. If the match still
//     fails then we look for possible aliases of the stemmed words and
//     do a final search.  If that fails then we give up and say there was
//     no match.
// XXX Consider matching based on combination of keywords i.e for an SMS
//     resolved to "add me soccertext", try matching "add me soccertext", then
//     try "add soccertext" then try "add me" then lastly try "me soccertext"
function keyword_get_content($sms)
{
	if(($keywordInfo = keyword_try_get_content($sms, "forward")) <= 0) {
		if(($keywordInfo = keyword_try_get_content($sms, "reverse")) <= 0) {
			if(($keywordInfo = keyword_try_get_content($sms, "forward_trimmed")) <= 0) {
				if(($keywordInfo = keyword_try_get_content($sms, "reverse_trimmed")) <= 0) {
					return 0;
				}
			}
		}
	}

	return $keywordInfo;
}

// The @optional_words are useful in the case where a given SMS may belong to
// a certain keyword but may be resolved to the wrong keyword sequencey because
// it contains another unrelated word.  For example, we may have the keyword "add soil"
// and the keyword "soccertext".  Now, someone may send the SMS "please add me to soccertext"
// Therefore, the SMS shall resolve to "add soccertext".  Hence, we would need to add the word
// "add" as an optional word in the keyword soccertext
function keyword_enter_normalizations($keywordId, $keyword, $keyword_alias, $optional_words)
{
	global $DEBUG_mode;

	mysql_query("DELETE FROM normalizedKeywords WHERE keywordId='$keywordId'");

	// Make dictionary entries
	keyword_add_to_dictionary($keyword);
	keyword_add_to_dictionary($keyword_alias);
	keyword_add_to_dictionary($optional_words);

	// Make entry for keyword
	keyword_enter_normalization($keyword, $keywordId);

	// Make entry for keyword alias
	keyword_enter_normalization($keyword_alias, $keywordId);

	// Make entries for optional words
	$k_a = keyword_cleanup($optional_words);
	if(count($k_a) == 1 && !strlen($k_a[0])) {
		// No optional words found
		return;
	}

	$clean_k = keyword_cleanup($keyword);
	if(count($clean_k) == 1 && !strlen($clean_k[0])) {
		// No use processing options
		return;
	}
	$clean_k_str = implode(" ", $clean_k);

	$opt_c = keywords_get_combinations($k_a);
	foreach($opt_c as $opt) {
		$new_keyword_option = $clean_k_str." ".$opt;
		if($DEBUG_mode == 1) {
			print "keyword_enter_normalizations: new keyword option: |$new_keyword_option|\n";
		}

		keyword_enter_normalization($new_keyword_option, $keywordId);
	}

	return;
}

function keyword_enter_normalization($keyword, $keywordId)
{
	global $DEBUG_mode;

	if(!strlen($keyword)) {
		return;
	}

	$k_a = keyword_cleanup($keyword);
	if(count($k_a) == 1 && !strlen($k_a[0])) {
		// Invalid keyword
		return;
	}

	if($DEBUG_mode) {
		print "keyword_enter_normalization: cleaned keyword: |".implode(" ", $k_a)."|\n";
	}

	$wordCount = count($k_a);
	$nk = keyword_normalize($keyword);

	if(keyword_normalization_exists($keywordId, $nk)) {
		return;
	}

	$sql = "INSERT INTO normalizedKeywords(keywordId, normalizedKeyword, wordCount) VALUES('$keywordId', '$nk', '$wordCount')";
	if($DEBUG_mode) {
		print "keyword_enter_normalization: ".$sql."\n";
	}

	if(!mysql_query($sql)) {
		fatal("Database Error: ".mysql_error()."<br>SQL: ".$sql."<br>");
	}

	return 0;
}

function keyword_normalization_exists($keywordId, $n)
{
	$sql = "SELECT id FROM normalizedKeywords WHERE keywordId='$keywordId' AND normalizedKeyword='$n'";
	if(!($result = mysql_query($sql))) {
		fatal("Database Error: ".mysql_error()."<br>SQL: ".$sql."<br>");
	}

	return mysql_num_rows($result);
}

function keyword_add_to_dictionary($k)
{
	$k_a = keyword_cleanup($k);
        if(count($k_a) == 1 && !strlen($k_a[0])) {
                // No valid words found
                return;
        }

	// Add optional words to the dictionary, if they do not already exist
	foreach($k_a as $this_word) {
		if(unique_field_exists('word', $this_word, 'dictionary')) {
			continue;
		}

		$sql = "INSERT INTO dictionary(created, word) VALUES(NOW(), LOWER('$this_word'))";
		execute_update($sql);
	}
}


function gps_cleanup($field_name, $field_value){
        $gps_fields = array("coordinate", "elevation", "longitude", "latitude");
        $cleanValue ="";
        foreach($gps_fields as $gps_field){
                $pattern = '/'.$gps_field.'/i';
                if(preg_match($pattern, $field_name)){
                                $b = $field_value;
                                $b = trim($b);
                                $b = preg_replace('/[^A-Za-z0-9,.]+/', '', $b);
                                $b = preg_replace('/^[mon]{1}/i', 'N ', $b);
                                $b = preg_replace('/^[dfe]{1}/i', 'E ', $b);
                                $b = preg_replace('/^[pqrs]{1}/i', 'S ', $b);
                                $b = preg_replace('/^[wxyz]{1}/i', 'W ', $b);
                                $b = preg_replace('/\s+/', ' ', $b);
                                $b = preg_replace("/(\w+)(\s?.+)/e", "'\\1'.strIreplace('\\2')", $b);
                                $b = preg_replace('/[^NESW0-9,.\s]+/', '', $b);
																$b = preg_replace('/,+/', '.', $b);
																$b = preg_replace('/^(\.+)?/', '', $b);
																$b = preg_replace('/(NESW)?(\s\.)?([0-9.]+)?/', "\\1 \\3", $b);
																$b = preg_replace('/\s+/', ' ', $b);
                                $b = trim($b);
                                $cleanValue = $b;
                                break;
                }
                else {
                                $cleanValue = $field_value;
                }
        }
        return $cleanValue;
}


function strIreplace($a){
        $out = array("O","l",);//letters
        $in = array("0","1");
        return str_ireplace($out, $in, $a);
}

//if date is valid, returns date of format yyyy-mm-dd
//$date_formats = array ('YYYY-MM-DD', 'YYYY-DD-MM', 'DD-MM-YYYY', 'MM-DD-YYYY', 'YYYYMMDD', 'YYYYDDMM');
function validateDate( $date, $format='YYYY-MM-DD')
    {
        switch( $format )
        {
            case 'YYYY/MM/DD':
            case 'YYYY-MM-DD':
            list( $y, $m, $d ) = preg_split( '/[-\.\/ ]/', $date );
            break;

            case 'YYYY/DD/MM':
            case 'YYYY-DD-MM':
            list( $y, $d, $m ) = preg_split( '/[-\.\/ ]/', $date );
            break;

            case 'DD-MM-YYYY':
            case 'DD/MM/YYYY':
            list( $d, $m, $y ) = preg_split( '/[-\.\/ ]/', $date );
            break;

            case 'MM-DD-YYYY':
            case 'MM/DD/YYYY':
            list( $m, $d, $y ) = preg_split( '/[-\.\/ ]/', $date );
            break;

            case 'YYYYMMDD':
            $y = substr( $date, 0, 4 );
            $m = substr( $date, 4, 2 );
            $d = substr( $date, 6, 2 );
            break;

            case 'YYYYDDMM':
            $y = substr( $date, 0, 4 );
            $d = substr( $date, 4, 2 );
            $m = substr( $date, 6, 2 );
            break;
        }
        if(checkdate( $m, $d, $y )){
                return "$y-$m-$d";
        }else{
                return "";
        }
    }

function globalsettings_get_value($name)
{
	lock_system();
	$sql = "SELECT * FROM GlobalSettings LIMIT 1";
	if(!($result = mysql_query($sql))) {
		die(mysql_error());
	}

	if(!mysql_num_rows($result)) {
		if(!mysql_query("INSERT INTO GlobalSettings(attribution) VALUES('0')")) {
			die(mysql_error());
		}

		$return_value = array();
		$return_value['attribution'] = 0;
	} else {
		$return_value = mysql_fetch_assoc($result);
	}
	unlock_system();

	return $return_value[$name];
}

function globalsettings_set_value($name, $value)
{
	// Ensure we have a row
	$c_value = globalsettings_get_value($name);
	if(!mysql_query("UPDATE GlobalSettings SET `$name`='".mysql_real_escape_string($value)."'")) {
		die(mysql_error());
	}
}



?>

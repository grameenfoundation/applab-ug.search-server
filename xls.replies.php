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
include("constants.php");
include("functions.php");
include("sessions.php");

dbconnect();
validate_session(); 
check_admin_user();

extract($_GET);

if(!preg_match("/^[0-9]+$/", $questionId)) { 
   print 'Error';
   exit;
}
/* the Question */
$question = get_question_from_id($questionId);
if(empty($question)) {
   print 'Question Not Found!';
   exit;
}
/* quiz*/
$quiz = get_quiz_from_id($question['quizId']);

$sql = "SELECT quizreply.*, DATE_FORMAT(time, '%d/%m/%Y %r') AS ftime FROM quizreply WHERE questionId=$questionId 
        ORDER BY time DESC"; 
if(!($result=mysql_query($sql))) {
   print 'Error: '.mysql_error();
   exit;
}
$filename = $question['id'].'-replies.csv';
$filepath = reportDir.'/'.$filename;
if(!($fh = fopen($filepath, 'w'))) {
   print 'Can not open file: '.$filepath;
   exit;
}    
$title = "Replies For Question: $question[question] (from Quiz: $quiz[name])";
fwrite($fh, "\n$title\n\n");
fwrite($fh, "No.,Phone,Reply Message,Answer,Time\n");
$i = 1;
while($row=mysql_fetch_assoc($result)) {
	$no = array_pop(preg_split("/\s+/", $row['reply']));
	if(preg_match("/^[0-9]+$/", $no)) {
		$sql = "SELECT answer FROM answer WHERE no=$no AND questionId=$questionId";
		$result2 = execute_query($sql, 0);
		$row2 = mysql_fetch_assoc($result2);
		$answer = $row2['answer'];
   } 
   if(!strlen($answer)) {
	   $answer = 'Not Found';
	   $row['correct'] = 0;
   } 
   $correct = $row['correct'] ? 'CORRECT' : 'INCORRECT';  
   fwrite($fh, "=\"$i.\",=\"$row[phone]\",=\"".str_replace(",", "", $row[reply])."\",=\"".str_replace(",", "", $answer)."\",=\"$row[ftime]\"\n");
   $i++;
}

fclose($fh);

header("Content-type: text/x-csv");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Content-Transfer-Encoding: binary");
header("Content-Length: ".filesize($filepath));

if(!readfile($filepath)) {
  print "Error reading file: $filepath";
  exit;
}

?>

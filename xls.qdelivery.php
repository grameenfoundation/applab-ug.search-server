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
$delivery = $_GET['status'];
/* the Question */
$question = get_question_from_id($questionId);
if(empty($question)) {
   print 'Question Not Found!';
   exit;
}
/* quiz*/
$quiz = get_quiz_from_id($question['quizId']);

$sql = "SELECT quizphoneno.*, DATE_FORMAT(createDate, '%d/%m/%Y %T') AS created, 
        DATE_FORMAT(updated, '%d/%m/%Y %T') AS updated FROM quizphoneno WHERE quizId=$question[quizId] ORDER BY createDate DESC";
		
$result = execute_query($sql, 0);

$filename = date('Ymd').'-'.$question['id'].'-delivery.csv';
$filepath = reportDir.'/'.$filename;
if(!($fh = fopen($filepath, 'w'))) {
   print 'Can not open file: '.$filepath;
   exit;
}    
$title = "Delivery Report for Question: $question[question] (from Quiz: $quiz[name])";
fwrite($fh, "\n$title\n\n");
fwrite($fh, "No.,Phone,Delivery Status\n");
$i = 1;
while($row=mysql_fetch_assoc($result)) { 
   /* get status */
   $sql = "SELECT * FROM qndelivery WHERE questionId=$questionId AND phoneId=$row[id]";
   $result2=execute_query($sql, 0); 
   $status = $row['status'];
   		 
   if(!strlen($status)) {
		$status = 'PENDING';
		$img = 'pending.gif';
	}
	elseif(preg_match("/Accepted\sfor\sdelivery/i", $status)) {
		$status = 'DELIVERED';
		$img = 'delivered.gif';
   }
   else {
	    $img = 'rejected.gif';
   }	   
  fwrite($fh, "$i.,=\"$row[phone]\",\"$status\"\n");
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
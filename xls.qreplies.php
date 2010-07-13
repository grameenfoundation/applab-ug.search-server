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
include("excel-write.php");
include("functions.php");
include("sessions.php");

dbconnect();
validate_session(); 
check_admin_user();

extract($_GET);

if(!preg_match("/^[0-9]+$/", $quizId)) { 
   die('Error');
}
/* the Question */
$quiz = get_quiz_from_id($quizId);
if(empty($quiz)) {
   die('Quiz Not Found!');
}

$sql = "SELECT quizreply.*, DATE_FORMAT(time, '%d/%m/%Y %r') AS rtime FROM quizreply WHERE questionId 
        IN (SELECT id FROM question WHERE quizId=$quizId) ORDER BY time DESC"; 
        
if(!($result=mysql_query($sql))) {
   die('Error: '.mysql_error());
}
$filename = 'quiz-'.$quiz['id'].'-replies.csv';
$filepath = reportDir.'/'.$filename;

if(!($fh = fopen($filepath, 'w'))) {
   die('Can not open file: '.$filepath);
}    
$title = "Replies For Quiz: $quiz[name], Created On $quiz[createDate])";
fwrite($fh, "\n\"$title\"\n\n");

fwrite($fh, "No.,User/Phone No.,Received Message,Time\n");
$i = 1;
while($row=mysql_fetch_assoc($result)) {
   $reply = preg_replace("/\"/", "", $row['reply']);
   fwrite($fh, "$i.,=\"".get_phone_display_label($row['phone'], 100)."\",\"$reply\",\"$row[rtime]\"\n");
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

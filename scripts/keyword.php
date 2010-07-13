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
/* Process General keyword 
 *
 */ 
if(!is_array($keywd)) {
   exit();
}

$lkeyword = $keywd['keyword'];
$content = $keywd['content'];
$attribution = $keywd['attribution'];

if(!strlen($content)) {
   $content = 'No content was found for this keyword. Kindly notify us about this problem.';
}

$lkeyword = strtolower($lkeyword);
/*
* Check whether attribution information exists and whether it is globally enabled.
*/
$attributionStatus = globalsettings_get_value('attribution');
$content .= (preg_match('/1/', $attribution) && $attributionStatus)? '\n'.$attribution : '';

sendsms($content, $sender);

/* log Hit */
$requestlog = mysql_real_escape_string($message);
$replylog   = mysql_real_escape_string($content);

$sql = "INSERT INTO hit(keyword, phone, request, reply) VALUES('$lkeyword', '$sender', '$requestlog', '$replylog')";
doupdate($sql);

/* outbound triggers */ 
if($keywd['otrigger']) { 
   $sql = "SELECT * FROM tmp WHERE phone='$sender'";
   $result = doquery($sql); 
   if(mysql_num_rows($result)) {
      $sql = "UPDATE tmp SET date=NOW() WHERE phone='$sender'"; 
   } 
   else {
      $sql = "INSERT INTO tmp(phone, keywordId, date) VALUES ('$sender', $keywd[id], NOW())";
   }
   doupdate($sql);
}

$genkeyword = 1;

//
$GLOBAL_sender_removed = 0;
$GLOBAL_sender_added = 0;

// Perform quiz action
switch($keywd['quizAction_action']) {
	case "remove":
	{
		mysql_query("DELETE FROM quizphoneno WHERE phone='$sender' AND quizId='{$keywd[quizAction_quizId]}'");
		$GLOBAL_sender_removed = 1;
		break;
	}
	case "add":
	{
		mysql_query("INSERT INTO quizphoneno(quizId, createDate, phone, updated) VALUES('{$keywd[quizAction_quizId]}', NOW(), '$sender', NOW())");
		$GLOBAL_sender_added = 1;
		break;
	}
	default:
		break;
}

?>

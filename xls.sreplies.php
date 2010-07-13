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

if(!preg_match("/^[0-9]+$/", $surveyId)) { 
   die('Error');
}
/* the Question */
$survey = get_survey_from_id($surveyId);
if(empty($survey)) {
   die('Survey Not Found!');
}

$sql = "SELECT sresult.*, DATE_FORMAT(date, '%d/%m/%Y %r') AS time FROM sresult WHERE surveyId=$surveyId 
        ORDER BY date DESC"; 
if(!($result=mysql_query($sql))) {
   die('Error: '.mysql_error());
}
$filename = 'survey-'.$survey['id'].'-replies.csv';
$filepath = reportDir.'/'.$filename;

$filename_xls = 'survey-'.$survey['id'].'-replies.xls';
$filepath_xls = reportDir.'/'.$filename_xls;

if(!($fh = fopen($filepath, 'w'))) {
   die('Can not open file: '.$filepath);
}    
$title = "Replies For Survey: $survey[name], Created On $survey[createdate])";
fwrite($fh, "\n\"$title\"\n\n");

xls_add_titletext($xls_resource, $title);

if(isset($detailed)) {
fwrite($fh, "No.,Phone,Received Message,Reply,Analysis Text,Time\n");
$i = 1;
while($row=mysql_fetch_assoc($result)) {
   $request = preg_replace("/\"/", "", $row['request']);
   $reply = preg_replace("/\"/", "", $row['reply']);
   $analysis = preg_replace("/\"/", "", $row['analysis']);
   fwrite($fh, "$i.,=\"".get_phone_display_label($row['phone'], 100)."\",\"$request\",\"$reply\",\"$analysis\",\"$row[time]\"\n");
   $i++;
}
}
else {
   xls_add_title_field($xls_resource, "Date");
   xls_add_title_field($xls_resource, "Sender");
   xls_add_title_field($xls_resource, "Raw SMS");

   $title = "Date,Sender,Raw SMS";
   /* show questions in title */
   $keyword = $survey['keyword'];
   $questions = unserialize($survey['questions']);
   foreach($questions as $q) {
      $title .= ",\"$q[question]\"";
      xls_add_title_field($xls_resource, $q[question]);
   }
   fwrite($fh, "$title\n");
   while($row=mysql_fetch_assoc($result)) {
      $record = "\"'$row[time]\",=\"".get_phone_display_label($row['phone'], 100)."\",\"'$row[request]\""; 
      xls_add_row_field($xls_resource, $row[time]);
      xls_add_row_field($xls_resource, $row[phone]);
      xls_add_row_field($xls_resource, $row[request]);
      $request = preg_replace("/^($keyword)/i", "", rtrim(strtolower($row['request'])));
	  // Deprecated code
	  // $msgtokens = preg_split("/\s+/", $request);

	  // Split up the message into questions and answers
	  $ans_splitted = survey_split_answers($request);

	  foreach($questions as $question) {
	     $qfound = false;
		 // Deprecated code
		 // foreach($msgtokens as $token) {
		 foreach($ans_splitted as $token_k => $token_v) {
			// Deprecated code
		    	// $token = preg_replace("/:/", "", $token);
			// $x = preg_replace("/^./", "", $token);
			// $y = preg_replace("/.$/", "", $token);
			// if(preg_match("/^[0-9]+$/", $x)) { $qn  = $x; $ans = $y; } else { $qn  = $y; $ans = $x; }

			// Fix up to meet Johnson's specifications
			$qn = $token_k;
			$ans = $token_v;

			if($question['no'] == $qn) {
			    $qfound = true;
				$answers = $question['answers'];
				$afound = false;
				foreach ($answers as $a) { if($a['no'] == $ans) { $record .= ",\"'$a[answer]\""; xls_add_row_field($xls_resource, $a[answer]); $afound=true; break; } }
				if(!$afound && ($question['open'] != 1)) {
					$record .= ",'$ans (NOT FOUND)";
					xls_add_row_field($xls_resource, "$ans (NOT FOUND)");
				} else if(!$afound && ($question['open'] == 1)) {
					$record .= ",'$ans";
					xls_add_row_field($xls_resource, $ans);
				}
				break;
			}
		 }
	     if(!$qfound) { $record .= ",NOT ASWERED"; xls_add_row_field($xls_resource, "NOT ASWERED"); }
	  }
	  fwrite($fh, "$record\n");	  
	  xls_new_row($xls_resource);
   }
}
fclose($fh);

// Attempt to create XLS file
if(!xls_write_file($filepath_xls, xls_get_array($xls_resource))) {
	header("Content-type: text/x-csv");
	header("Content-Disposition: attachment; filename=\"$filename\"");
	header("Content-Transfer-Encoding: binary");
	header("Content-Length: ".filesize($filepath));

	if(!readfile($filepath)) {
	  die("Error reading file: $filepath");
	}

	exit();
} else {
	unlink($filepath);
	header ("Content-type: application/x-msexcel");
	header ("Content-Disposition: attachment; filename=\"$filename_xls\"" );
	header("Content-Transfer-Encoding: binary");
	header("Content-Length: ".filesize($filepath_xls));

        if(!readfile($filepath_xls)) {
          die("Error reading file: $filepath_xls");
        }

        exit();
}

?>

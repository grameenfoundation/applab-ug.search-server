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

session_start();

if(!isset($_SESSION['smslogq'])) { 
   print 'Error';
   exit;
}

if(!($result=mysql_query($_SESSION['smslogq']['sql']))) {
   print 'Error: '.mysql_error();
   exit;
}
$filename = 'smslog.csv';
$filepath = reportDir.'/'.$filename;
if(!($fh = fopen($filepath, 'w'))) {
   print 'Can not open file: '.$filepath;
   exit;
}    
$title = $_SESSION['smslogq']['title'];
$direction = $_SESSION['smslogq']['direction'];

// Free up the session to avoid locking up HTTPD processes
session_write_close();

fwrite($fh, "\n$title\n\n");
fwrite($fh, ($direction == 'OUTGOING' ? 'To' : 'From')." Number,Message,Date\n");
$i = 1;
while($row=mysql_fetch_assoc($result)) {
   $phone = ($direction=='INCOMING' ? preg_replace("/\+/", "", $row['sender']) : $row['recipient']);
   fwrite($fh, "=\"".get_phone_display_label($phone)."\",\"".str_replace(",", "", $row[message])."\",=\"$row[time]\"\n");
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

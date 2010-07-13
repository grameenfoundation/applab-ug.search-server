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

if(isset($_SESSION['usearch'])) {
     $sql = $_SESSION['usearch']['sql'];
}
else {
$sql = "SELECT user.*, DATE_FORMAT(createdate, '%d/%m/%Y %r') AS created, 
        DATE_FORMAT(updated, '%d/%m/%Y %r') AS updated FROM user ORDER BY createdate DESC";
}		

// Free up the session to avoid locking up HTTPD processes
session_write_close();

if(!($result=mysql_query($sql))) {
   print 'Error: '.mysql_error();
   exit;
}
$filename = date('Ymd').'-users.csv';
$filepath = reportDir.'/'.$filename;
if(!($fh = fopen($filepath, 'w'))) {
   print 'Can not open file: '.$filepath;
   exit;
}    
$title = "User List - Total: ".mysql_num_rows($result);
fwrite($fh, "\n$title\n\n");
fwrite($fh, "No.,User/Phone No.,Total Hits,Created,Updated\n");
$i = 1;
while($row=mysql_fetch_assoc($result)) {
   $activity = get_user_activity($row['misdn'], $row['phones']);
   $hits = get_total_hits($activity);
   
   fwrite($fh, "$i.,=\"".get_phone_display_label($row['misdn'])."\",\"$hits\",\"$row[created]\",$row[updated]\n");
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

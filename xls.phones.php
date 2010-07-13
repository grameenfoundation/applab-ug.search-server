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

$sql = "SELECT phoneno.*, DATE_FORMAT(createDate, '%d/%m/%Y %T') AS created, 
        DATE_FORMAT(updated, '%d/%m/%Y %T') AS updated FROM phoneno ORDER BY createDate DESC";
if(!($result=mysql_query($sql))) {
   print 'Error: '.mysql_error();
   exit;
}
$filename = date('Ymd').'-numbers.csv';
$filepath = reportDir.'/'.$filename;
if(!($fh = fopen($filepath, 'w'))) {
   print 'Can not open file: '.$filepath;
   exit;
}    
$title = "Phone Numbers from which SMS Messages came into the System - Total: ".mysql_num_rows($result);
fwrite($fh, "\n$title\n\n");
fwrite($fh, "No.,Phone Number,Hits,First Recieved,Updated\n");
$i = 1;
while($row=mysql_fetch_assoc($result)) {
   fwrite($fh, "$i.,=\"$row[phone]\",\"$row[hits]\",\"$row[created]\",$row[updated]\n");
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
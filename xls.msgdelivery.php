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

if(!preg_match("/^[0-9]+$/", $messageId)) { 
   die('Error');
}

$sql = "SELECT message.*, DATE_FORMAT(createDate, '%d/%m/%Y %r') AS createDate FROM message WHERE id=$messageId";
$result=execute_query($sql, 0);
if(!mysql_num_rows($result)) {
   die ('Message Not Found!');
}
$message = mysql_fetch_assoc($result);

$sql = "SELECT recipient.*, DATE_FORMAT(createDate, '%d/%m/%Y %r') AS created, 
        DATE_FORMAT(updated, '%d/%m/%Y %r') AS updated FROM recipient WHERE messageId=$messageId";

$result = execute_query($sql, 0);

$filename = 'message-'.$messageId.'-delivery.csv';
$filepath = reportDir.'/'.$filename;
if(!($fh = fopen($filepath, 'w'))) {
   die('Can not open file: '.$filepath);
}    
$title = "Delivery Report for Message: $message[name] (Scheduled - $message[createDate])";
fwrite($fh, "\n$title\n\n");
fwrite($fh, "No.,\"User/Phone No.\",Delivery Status\n");
$i = 1;

while($row=mysql_fetch_assoc($result)) { 
	 if(!strlen($row['status'])) {
		    $status = 'PENDING';
	 }
	 elseif(preg_match("/Accepted\sfor\sdelivery/i", $row['status'])) {
		    $status = 'DELIVERED';
	 }
	 else {
		    $status = $row['status'];
	 }	
	 fwrite($fh, "$i.,=\"".get_phone_display_label($row['phone'], 100)."\",\"$status\"\n");
     $i++;
}
fclose($fh);

header("Content-type: text/x-csv");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Content-Transfer-Encoding: binary");
header("Content-Length: ".filesize($filepath));

if(!readfile($filepath)) {
  print "Error reading file: $filepath";
  exit();
}

?>
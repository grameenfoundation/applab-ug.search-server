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

$sql = "select id, form from mresult";
$result = mysql_query($sql);
$message='';
$ctr = 0;
while($row = mysql_fetch_assoc($result)){
		$md5_form = md5($row[form]);
		if(!mysql_query("update mresult set surveySignature = '$md5_form' where id = '$row[id]'")){
			$message.="Failed to add surveySignature for id: $row[id] Reason: ".mysql_error()."\n\r";
		}else{
			++$ctr;
		}
}

if(strlen($message)){
	print $message;
}else {
	print "Finished: $ctr surveys have been assigned surveySignatures";
}
?>
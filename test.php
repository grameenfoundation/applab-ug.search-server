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
$DEBUG_mode = 1;

print keyword_remove_redundant_words(keyword_forward_resolve_sms("Garden preparation of bananas clearing with the land"))."\n";
exit();
print keyword_forward_resolve_sms("please tell me the price of milk in mbarara")."\n";
print keyword_remove_redundant_words(keyword_forward_resolve_sms("please tell me the price of milk in mbarara"))."\n";
//print keyword_forward_resolve_sms("What's the price of cow peas in bushenyi?")."\n";
//print keyword_reverse_resolve_sms("What's the price of cow peas in bushenyi?")."\n";
exit();

$shortcode = "39483";
$phone = "256782212155";
$th_msg = "Gerald";
$status = shell_exec("/usr/bin/lynx -dump \"http://192.168.1.252:8081/cgi-bin/sendsms?username=sting-sendsms&password=sting-sendsms&from=$shortcode&to=$phone&text=$th_msg\"");
exit();

require 'excel/functions.php';

$arr = read_csv_upload('c:/wamp/www/ycppquiz/'.$_GET['file']);
foreach($arr as $n) print "$n\n";
?>

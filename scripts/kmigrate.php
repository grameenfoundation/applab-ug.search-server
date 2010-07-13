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
#! /usr/bin/php
<?
// This script creates normalizations for existing keywords from the
// keyword, keywordAlias and optionalWords. In addition all necessary
// dictionary entries are made
require("/var/www/html/mobile/constants.php");
require("/var/www/html/mobile/functions.php");

dbconnect();

$sql = "SELECT id, keyword, keywordAlias, optionalWords FROM keyword ORDER BY id ASC";
if(!($result = mysql_query($sql))) {
	die("MySQL error: \"".mysql_error()."\" while executing SQL: \"".$sql."\"");
}

$i = 0;
while(($row = mysql_fetch_array($result))) {
	$i++;
	keyword_enter_normalizations($row[id], $row[keyword], $row[keywordAlias], $row[optionalWords]);
}

print "$i keyword(s) have been successfully normalized\n";

?>

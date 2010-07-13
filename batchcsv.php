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
header("Expires: Tue, 12 Mar 1910 10:45:00 GMT");
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");

include("constants.php");
include("functions.php");
include("sessions.php");
require ("zipfile.inc.php");

//XXX session_start();
dbconnect();
validate_session();

$filename = "Keywords-".date("Ymd");
$sql = "SELECT id, name FROM category ORDER BY name";
$result = execute_query($sql);
$zipfile = new zipfile();
$zipfile ->add_dir("$filename/");

while($row = mysql_fetch_row($result)){
	$id = $row[0];
	$cat = $row[1];
	$sql = "SELECT category.name, keyword.keyword , keyword.attribution, keyword.keywordAlias, keyword.optionalWords, "
		."keyword.content FROM keyword LEFT JOIN category ON category.id=keyword.categoryId WHERE category.id = $id";
	$result2 = execute_query($sql);
	$csv = "Keyword, Attribution, Keyword Alias, Category, Optional Words, Content\r\n";
	while($row2 = mysql_fetch_assoc($result2)) {
		//Eliminate the delimiter, and return carriages from content that potentially malform our CSV
		$format = array("\n", "\r", ",");
        	$row2[content] = str_replace($format," ", $row2[content]);
        	$csv .= "$row2[keyword],$row2[attribution],$row2[keywordAlias],$row2[name],$row2[optionalWords],$row2[content]\r\n";
	}

	$zipfile ->add_file($csv, "$filename/$cat.csv");	
}

header("Content-type: application/octet-stream");
header("Content-disposition: attachment; filename=$filename.zip");
echo $zipfile ->file();

// XXX Free up the session to avoid locking up HTTPD processes^M
//session_write_close();

?>

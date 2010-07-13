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
#! /usr/bin/php
header("Expires: Tue, 12 Mar 1910 10:45:00 GMT");
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");

include("constants.php");
include("functions.php");
include("sessions.php");

session_start();

dbconnect();
validate_session();

if(isset($_GET[start]) && isset($_GET[limit])){ //pictures for currently displayed filtered results
	$sql = $_SESSION['exportq']." ORDER BY date DESC LIMIT $_GET[start], $_GET[limit]";
}

if(isset($_GET[all_pics_for_filter])){ //pictures for current filter
	$sql = $_SESSION[exportq];
}

if(isset($_GET[week])) { //pictures for selected week category
	$sql = $_SESSION[exportq];
	if(!preg_match('/^[0-9]+$/', $week)) {
		 die('ERROR: Invalid week');
	}
	if(!isset($_SESSION['filter_categories'])) {
	    die('ERROR: Invalid filter');
	}
	$category = $_SESSION['filter_categories'][$week];
	$sql .= " AND date BETWEEN '$category[from]' AND '$category[to]'";
}

if(isset($_GET['all'])){
	$sql = "SELECT mresult.*, DATE_FORMAT(date, '%d/%m/%Y/%r') AS time FROM mresult WHERE surveyId=$_GET[surveyId]";
}

// Free up the session to avoid locking up HTTPD processes
session_write_close();

if(!strlen($sql)){
	die("Note: There are no images for Current Survey, Selection or Category");
}
	
$result = execute_query($sql);

if(!mysql_num_rows($result)){
	die("Note: There are no images for Current Survey, Selection or Category");
}

$kk=0;
$pics= array();
while($row = mysql_fetch_assoc($result)) {
	$form = unserialize($row['form']); 
	$data = $form['data'];
	$uploads = $form['uploads']; 
 
	/* the uploads */
	//$pics = array();
	$multi_pics = false;
	if(count($uploads)>1) $multi_pics = true;
	if(count($uploads)>0){
		$c=1;
		foreach($uploads as $f){
			$location = get_location_from_misdn($row['phoneId']);
			$uniqueId = $row['id'];
			$msisdn = $row['phoneId'];
			$picture_name = $location."_".$row['phoneId']."_".$row['id'].($multi_pics ? '_'.$c++ : '').".png";
			//if(strlen($f['value'])) $_SESSION['pics'][] = array("file_name"=>$f['value'], "unique_filename"=>$picture_name);
			if(strlen($f['value']) && file_exists(MOBILE_UPLOADS_DIR.'/'.$f['value'])) {
				$pics[] = array("file_name"=>$f['value'], "unique_filename"=>$picture_name);
			} 
		}
	}
}




//ignore_user_abort(true);
register_shutdown_function("abort_function");

$tmp_folder = "Image".substr(md5(mt_rand(10,1000).time()),4,3);
mkdir(reportDir."/".$tmp_folder);

foreach($pics as $picture){
	//print $pictures['file_name']." < = > ".$pictures['unique_filename']."<br /><br />";
	$from_uploads = MOBILE_UPLOADS_DIR."/".$picture['file_name'];
	$to_tmp_folder = reportDir."/".$tmp_folder.'/'.$picture['unique_filename'];
	copy($from_uploads, $to_tmp_folder);
}

chdir(reportDir."/$tmp_folder");
$a = reportDir."/$tmp_folder.zip";
$b = "*.png";
exec("zip -r $a $b", $output);
//print_r($output);
//exit();
global $uri_zipped;
global $c ; 
$c = reportDir."/$tmp_folder";
$uri_zipped = reportDir."/".$tmp_folder.".zip";
$real_name = basename($uri_zipped);


if(file_exists($uri_zipped)){
//echo "<a href=\"reports/$real_name\">Click to download images</a>";
	header('Content-Description: File Transfer');
	header("Content-type: application/octet-stream");
	header("Content-Disposition: attachment; filename=\"$real_name\"" );
	header("Content-Transfer-Encoding: binary");
	header("Content-Length: ".filesize($uri_zipped));
	readfile($uri_zipped);
}

function abort_function(){
	if(connection_aborted()){
		//go ahead and do your thing
		global $c;
		//$c = reportDir."/$tmp_folder";
		exec("rm -rf $c");
		global $uri_zipped;
		if(file_exists($uri_zipped)){
			unlink($uri_zipped);
		}
	}
	elseif(connection_status==2){
		//this is a timeout, report that script requires more time
	}
	elseif(connection_status==3){
		//aborted and timed-out, report that script requires more time
	}
}

$c = reportDir."/$tmp_folder";
exec("rm -rf $c");
unlink($uri_zipped);

?>

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

session_start();

dbconnect();
validate_session();

/*
S 0.58532
E 30.37932

S 00.59377
E 30.40064

S 00.59388
E 30.40078*/

function getCoordinates($latlong){
	// s00.003,e32.90029
	// e32.90029,s00.003
	$latlong = trim($latlong);
	$coords = preg_split('/,/', $latlong);
	$lat = $long ="";
	foreach($coords as $coord){
		if(preg_match('/(s|n)/i', $coord)){
			//print "::lat:$coord::";
			$lat = $coord;
		}else{
			//print "::long:$coord::";
			$long = $coord;
		}
	}
	
	if(!strlen($lat) || !strlen($long)){
		return array();
	}
	
	$lat = preg_match('/n/i', $lat) ? preg_replace('/n/i', '', $lat) : preg_replace('/s/i', '-', $lat);
	$long = preg_match('/e/i', $long) ? preg_replace('/e/i', '', $long) : preg_replace('/w/i', '-', $long);
	//cleaning
	/*global $latMax, $latMin, $longMax, $longMin;
	if((sprintf('%f', $lat) > $latMax) || sprintf('%f', $lat) < $latMin){
		return array();
	}
	if((sprintf('%f', $long) > $longMax) || sprintf('%f', $long) < $longMin){
		return array();
	}*/
	
	return array("latitude"=>$lat, "longitude"=>$long);
}

$result = execute_query($_SESSION['map']);
$datas = array();
while($row = mysql_fetch_assoc($result)){
	if(strlen($row[gpscordinates])){
		$coords = getCoordinates($row[gpscordinates]);
		if(count($coords)){
			$datas[] = array("name"=>$row[names], "msisdn"=>$row[misdn], "deviceInfo"=>$row[deviceInfo], "notes" => $row[notes], "latitude"=>$coords[latitude], "longitude"=>$coords[longitude]);
		}
	}
}
//print_r($datas);
//exit();
	
/*
$datas[] = array("name"=>"person one", "info"=>"Very hard working", "latitude"=>"0.58532", "longitude"=>"30.37932");
$datas[] = array("name"=>"person two", "info"=>"Knows his thing", "latitude"=>"0.10377", "longitude"=>"30.50064");
$datas[] = array("name"=>"person three", "info"=>"Knows his thing and very hard working", "latitude"=>"00.59388", "longitude"=>"30.80078");*/
$j=0;

?>
<html>
<head>
<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>
<script type="text/javascript">
  function initialize() {
    var latlng = new google.maps.LatLng(1.373333,32.290275); //[n,s],[w,e]
    var myOptions = {
      zoom: 7,
      center: latlng,
      mapTypeId: google.maps.MapTypeId.HYBRID
    };
    var map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
		
		<?foreach($datas as $data){ ?>
			var marker<?="_$j"?> = new google.maps.Marker({
				position: new google.maps.LatLng(parseFloat("<?=$data[latitude]?>"), parseFloat("<?=$data[longitude]?>")),
				map: map,
				title: "<?=$data[name]?>"
			});
			
			var infowindow<?="_$j"?> = new google.maps.InfoWindow({
				content: "<div id='content<?="_$j"?>'>\
										<p>NAME: <?=$data[name]?></p>\
										<p>MSISDN: <?=$data[msisdn]?></p>\
										<p>DEVICE INFO: <?=$data[deviceInfo]?></p>\
										<p>NOTES: <?=$data[notes]?></p>\
									</div>"
			});
			
			google.maps.event.addListener(marker<?="_$j"?>, 'click', function() {
				infowindow<?="_$j"?>.open(map,marker<?="_$j"?>);
			});
		<?$j++;}?>
			
  }

</script>
<style type="text/css">
<!--
#map_canvas {
	height: 500px;
	width: 700px;
	margin-right: auto;
	margin-left: auto;
	margin-top: 100px;
	border: thin ridge #97E927;
}
body {
	margin-left: 0px;
	margin-top: 0px;
}
-->
</style>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"></head>
<body onLoad="initialize()">
  <div id="map_canvas"></div>
</body>
</html>
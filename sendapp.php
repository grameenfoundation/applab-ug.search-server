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

if($_POST['submit']){
extract($_POST);
if(!($phone_number&&$phone_type&&$phone_app)){
	$error = "Ensure that you have set the phone, phone type, and application";
}
else{
	if(preg_match("/^n95$/",$phone_type)){
		/*if(preg_match("/^mobilesuv$/",$phone_app)) getApp("m.php?p=".$phone_number);
		else if(preg_match("/^ckwsearch$/",$phone_app)) getApp("ckwsearch.jad");
		else if(preg_match("/^gis$/",$phone_app)) getApp("gis/m.php?p=".$phone_number);
		exit;*/
		if(preg_match("/^mobilesuv$/",$phone_app)) getapp("mobilesuv", $phone_number, "n95");
		else if(preg_match("/^ckwsearch$/",$phone_app)) getapp("ckwsearch", $phone_number, "n95");
		else if(preg_match("/^gis$/",$phone_app)) getapp("gis", $phone_number, "n95");
	}
	else if(preg_match("/^1680c$/",$phone_type)){
		/*if(preg_match("/^mobilesuv$/",$phone_app)) getApp("1680/m.php?p=".$phone_number);
		else if(preg_match("/^ckwsearch$/",$phone_app)) getApp("1680/ckwsearch.jad");
		else if(preg_match("/^gis$/",$phone_app)) getApp("gis/1680/m.php?p=".$phone_number);
		exit;*/
                if(preg_match("/^mobilesuv$/",$phone_app)) getapp("mobilesuv", $phone_number, "1680");
                else if(preg_match("/^ckwsearch$/",$phone_app)) getapp("ckwsearch", $phone_number, "1680");
                else if(preg_match("/^gis$/",$phone_app)) getapp("gis", $phone_number, "1680");
	}
	
}}

function getapp($app, $msisdn, $phone)
{
	switch($app) {
	case 'mobilesuv':
		if(!strcmp($phone, "n95")) {
			$fsize = filesize('MobileSuv.jar');
			$content="MIDlet-1: MobileSuv,,SurveyApp2
MIDlet-Info-URL: www.yo.co.ug
MIDlet-Jar-Size: $fsize
MIDlet-Jar-URL: MobileSuv.jar
MIDlet-Name: MobileSuv
MIDlet-Permissions: javax.microedition.io.Connector.file.read, javax.microedition.io.Connector.file.write, javax.microedition.io.Connector.http, javax.microedition.location.Location
MIDlet-Vendor: Yo! Uganda Ltd
MIDlet-Version: 0.9
MicroEdition-Configuration: CLDC-1.1
MicroEdition-Profile: MIDP-2.0
phone-id: $msisdn";

			header("Content-Disposition: attachment; filename=\"MobileSuv.jad\"");
			header("Content-type: text/vnd.sun.j2me.app-descriptor");
			header("Content-Length: ".strlen($content));
			echo $content;
			exit();
		} else if(!strcmp($phone, "1680")) {
			$fsize = filesize('1680/MobileSuv.jar');
			$content="MIDlet-1: MobileSuv,,SurveyApp2
MIDlet-Jar-Size: $fsize
MIDlet-Jar-URL: 1680/MobileSuv.jar
MIDlet-Name: MobileSuv
MIDlet-Permissions: javax.microedition.io.Connector.file.read, javax.microedition.io.Connector.file.write, javax.microedition.io.Connector.http
MIDlet-Vendor: Yo! Uganda Ltd
MIDlet-Version: 0.9
MicroEdition-Configuration: CLDC-1.1
MicroEdition-Profile: MIDP-2.0
phone-id: $msisdn";

			header("Content-Disposition: attachment; filename=\"MobileSuv.jad\"");
			header("Content-type: text/vnd.sun.j2me.app-descriptor");
			header("Content-Length: ".strlen($content));
			echo $content;
			exit();
		} else {
			print "Invalid phone type for $app: $phone";
		}
		break;
	case 'ckwsearch':
                if(!strcmp($phone, "n95")) {
			$content = "Download-URL: http://applab.yo.co.ug/mobile/ckwsearch.php
MIDlet-1: CKWSearch, , CKWSearch
MIDlet-Icon: grameen-logo_55x55.png
MIDlet-Info-URL: www.yo.co.ug
MIDlet-Jar-Size: 12678
MIDlet-Jar-URL: ckwsearch.jar
MIDlet-Name: ckwsearch
MIDlet-Permissions: javax.microedition.io.Connector.sms, javax.wireless.messaging.sms.send, javax.microedition.io.Connector.http, javax.microedition.io.Connector.file.write, javax.microedition.io.Connector.file.read
MIDlet-Vendor: Yo! Uganda Ltd
MIDlet-Version: 1.0
MicroEdition-Configuration: CLDC-1.1
MicroEdition-Profile: MIDP-2.0
SMS-Number: 0772712884
";
			header("Content-Disposition: attachment; filename=\"MobileSuv.jad\"");
			header("Content-type: text/vnd.sun.j2me.app-descriptor");
			header("Content-Length: ".strlen($content));
                        echo $content;
                        exit();
                } else if(!strcmp($phone, "1680")) {
			$content = "Download-URL: http://applab.yo.co.ug/mobile/ckwsearch.php
MIDlet-1: CKWSearch, , CKWSearch
MIDlet-Icon: grameen-logo_24x24.png
MIDlet-Info-URL: www.yo.co.ug
MIDlet-Jar-Size: 12637
MIDlet-Jar-URL: 1680/ckwsearch.jar
MIDlet-Name: ckwsearch
MIDlet-Permissions: javax.microedition.io.Connector.sms, javax.wireless.messaging.sms.send, javax.microedition.io.Connector.http, javax.microedition.io.Connector.file.write, javax.microedition.io.Connector.file.read
MIDlet-Vendor: Yo! Uganda Ltd
MIDlet-Version: 1.0
MicroEdition-Configuration: CLDC-1.1
MicroEdition-Profile: MIDP-2.0
SMS-Number: 0772712884
";
			header("Content-Disposition: attachment; filename=\"MobileSuv.jad\"");
			header("Content-type: text/vnd.sun.j2me.app-descriptor");
			header("Content-Length: ".strlen($content));
                        echo $content;
                        exit();
                } else {
                        print "Invalid phone type for $app: $phone";
                }
		break;
	case 'gis':
                if(!strcmp($phone, "n95")) {
			$fsize = filesize('gis/gis.jar');
			$content="MIDlet-1: GIS,,SurveyApp2
MIDlet-Jar-Size: $fsize
MIDlet-Jar-URL: gis/gis.jar
MIDlet-Name: GIS
MIDlet-Permissions: javax.microedition.io.Connector.file.read, javax.microedition.io.Connector.file.write, javax.microedition.io.Connector.http
MIDlet-Vendor: Yo! Uganda Ltd
MIDlet-Version: 0.9
MicroEdition-Configuration: CLDC-1.1
MicroEdition-Profile: MIDP-2.0
phone-id: $msisdn";
                        header("Content-Disposition: attachment; filename=\"MobileSuv.jad\"");
                        header("Content-type: text/vnd.sun.j2me.app-descriptor");
                        header("Content-Length: ".strlen($content));
                        echo $content;
                        exit();
		} else if(!strcmp($phone, "1680")) {
			$fsize = filesize('gis/1680/gis.jar');
			$content="MIDlet-1: GIS,,SurveyApp2
MIDlet-Jar-Size: $fsize
MIDlet-Jar-URL: gis/1680/gis.jar
MIDlet-Name: GIS
MIDlet-Permissions: javax.microedition.io.Connector.file.read, javax.microedition.io.Connector.file.write, javax.microedition.io.Connector.http
MIDlet-Vendor: Yo! Uganda Ltd
MIDlet-Version: 0.9
MicroEdition-Configuration: CLDC-1.1
MicroEdition-Profile: MIDP-2.0
phone-id: $msisdn";

			header("Content-Disposition: attachment; filename=\"gis.jad\"");
			header("Content-type: text/vnd.sun.j2me.app-descriptor");
			header("Content-Length: ".strlen($content));
			echo $content;
		}

		break;
	default:
		print "Invalid app type $app";
		break;
	}

	exit();
}

function getApp2($app){
	header("Location: http://applab.yo.co.ug/mobile/".$app);
}


?>


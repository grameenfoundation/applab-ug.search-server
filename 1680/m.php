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
if(strlen($_GET['p'])) {
        $phoneNumber = $_GET['p'];
} else {
        $phoneNumber = md5(time());
}
$fsize = filesize('MobileSuv.jar');

$content="MIDlet-1: MobileSuv,,SurveyApp2
MIDlet-Jar-Size: $fsize
MIDlet-Jar-URL: MobileSuv.jar
MIDlet-Name: MobileSuv
MIDlet-Permissions: javax.microedition.io.Connector.file.read, javax.microedition.io.Connector.file.write, javax.microedition.io.Connector.http
MIDlet-Vendor: Yo! Uganda Ltd
MIDlet-Version: 0.9
MicroEdition-Configuration: CLDC-1.1
MicroEdition-Profile: MIDP-2.0
phone-id: $phoneNumber";

header("Content-Disposition: attachment; filename=\"MobileSuv.jad\"");
header("Content-type: text/vnd.sun.j2me.app-descriptor");
header("Content-Length: ".strlen($content));
echo $content;

?>

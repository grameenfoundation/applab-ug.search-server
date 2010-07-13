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
#!/usr/bin/php
<?
require '/var/www/html/sting/constants.php';
require '/var/www/html/sting/functions.php';
require '/var/www/html/sting/scripts/functions.php';

$logfile = '/tmp/scheduled.log';
dbconnect();
if(logic_lock_cronsms()) {
        exit();
}

send_scheduled();

logic_unlock_cronsms();

?>

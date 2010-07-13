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
require '../constants.php';
require '../functions.php';
require '../display.php';

dbconnect();

if($_GET['t'] != '593aac37df0ec377ba8b5a99b6d1a53ccc1d8817f51456ba6213dddce90cbb23') {
    exit();
}

$surveyId = preg_match("/^[0-9]+$/", $_GET['surveyId']) ? $_GET['surveyId'] : 11;
$survey = get_table_record('msurvey', $surveyId);
$html = '
<form method="post" action="process.php" enctype="multipart/form-data">
<table border="0">
<tr>
    <td>'.make_html_form($surveyId).'</td>
</tr>
<tr>
   <td>
     <input type="hidden" name="'.FormIdentifier.'" value="'.$survey['id'].'">
	 <input type="hidden" name="'.PhoneId.'" value="0712933140">
	 <input type="submit" value="Submit Data">
   </td>
</tr>
</table>
</fotm>';

print $html;
?>
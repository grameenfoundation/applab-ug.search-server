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

$sql = "SELECT * FROM category WHERE ckwsearch";
if(!($result=mysql_query($sql))) {
   die(mysql_error());
}
$keywords = '<Keywords>'."\r\n";
while($row=mysql_fetch_assoc($result)) {
   $category = get_dictionary_word($row['name']);
   $sql = "SELECT * FROM keyword WHERE categoryId=$row[id]";
   if(!($result2 = mysql_query($sql))) {
       die(mysql_error());
   }
   while($row2 = mysql_fetch_assoc($result2)) {
      $keywords .= '<Keyword>'.trim($category).' '.preg_replace("/\s+/", " ", trim($row2['keyword'])).'</Keyword>'."\r\n";
   }
}
$keywords .= '</Keywords>'."\r\n";
$file = '/tmp/'.md5(microtime()).'.xml';
$fh = fopen($file, 'w');
fwrite($fh, $keywords);
fclose($fh);

header('Content-type: text/xml'); 
readfile($file);
unlink($file);
?>

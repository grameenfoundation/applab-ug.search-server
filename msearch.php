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
include("display.php");

session_start();

dbconnect();
validate_session(); 

extract($_GET);

check_id($surveyId, 'msurveys.php');
$survey = get_table_record('msurvey', $surveyId);
if(empty($survey)) {
   goto('msurveys.php');
}

if(isset($_POST['cancel'])) {
  goto("mresults.php?surveyId=$surveyId");
}
if(count($_POST)) { 
  $_POST = strip_form_data($_POST);
}  

if(isset($_POST['submit'])) 
{
	// The $_SESSION setting of 'search' in msearch.inc.php wipes out our search array
	$search_vl = array();
	foreach($_POST as $key=>$val) 
	{
	    if(preg_match("/^f[0-9a-z]{5,}/", $key) || preg_match("/^phoneId$/", $key)) {
		     if(!strlen($val)) {
			      continue;
			 }
			if(preg_match("/^([^_]+)_.+$/", $key, $mtc_g)) {
				$key = $mtc_g[1];
				if(strlen($search_vl[$key])) {
					$search_vl[$key] .= ",".$val;
				} else {
					$search_vl[$key] = $val;
				}
			} else {
				 $search_vl[$key] = $val;
			}
		}
	}
	require 'msearch.inc.php';
} 
/* menu highlight */
$page = 'msurvey';

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title><?= TITLE ?></title>
<link rel="stylesheet" type="text/css" href="styles/style.css" />
</head>

<body>
<table width="790" border="0" align="center" cellpadding="0" cellspacing="0" class="main">
     <tr>
          <td width="790" height="124" valign="top"><? include('top.php') ?></td>
     </tr>
     <tr>
        <td height="310" valign="top">
		   			<table width="100%" border="0" cellpadding="0" cellspacing="0" class="border">
		   						<!--DWLayoutTable-->
		   						<tr>
		   									<td height="22" colspan="3" align="center" valign="middle" class="caption">Search Survey Results </td>
              			</tr>
		   						<tr>
		   									<td width="23" height="22">&nbsp;</td>
                    						<td width="750">&nbsp;</td>
                    						<td width="15">&nbsp;</td>
		   						</tr>
		   						<tr>
		   									<td height="284">&nbsp;</td>
		   									<td valign="top">
               											<fieldset>
               											<legend>Search Results - <span style="font-weight: normal"><?= truncate_str($survey['name'], 70) ?></span></legend>
									<table width="100%" border="0" cellpadding="0" cellspacing="0">
												<!--DWLayoutTable-->
												<tr>
															<td width="22" height="23">&nbsp;</td>
               												<td width="703">&nbsp;</td>
               												<td width="23">&nbsp;</td>
												</tr>
												<tr>
															<td height="223">&nbsp;</td>
															<td valign="top" bgcolor="#EEEEEE" style="padding: 10px"><?=  make_search_form($surveyId) ?></td>
               												<td>&nbsp;</td>
												</tr>
												<tr>
															<td height="18">&nbsp;</td>
															<td>&nbsp;</td>
															<td>&nbsp;</td>
												</tr>
               									</table>
														</fieldset></td>
               						<td>&nbsp;</td>
		   									</tr>
		   						<tr>
		   									<td height="30">&nbsp;</td>
		   									<td>&nbsp;</td>
		   									<td>&nbsp;</td>
		   									</tr>
           			    </table></td>
     </tr>
    
     
        <tr>
          <td height="30" valign="top"><? include('bottom.php') ?></td>
     </tr>
</table>
</body>
</html>

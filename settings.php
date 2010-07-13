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

dbconnect();
validate_session(); 
check_admin_user();

if(count($_GET)){
	if(strcmp($_GET['attribution'], '1')==0){
		globalsettings_set_value('attribution', '1');
	}elseif(strcmp($_GET['attribution'], '0')==0){
		globalsettings_set_value('attribution', '0');
	}
	header("Location:settings.php");
}

/* menu highlight */
$page = 'keywords';

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title><?= TITLE ?></title>
<link rel="stylesheet" type="text/css" href="styles/style.css" />
<script type="text/javascript" src="basic.js"></script>
</head>

<body>
<table width="790" border="0" align="center" cellpadding="0" cellspacing="0" class="main">
     <!--DWLayoutTable-->
	<tr>
			<td width="790" height="124" valign="top"><? include('top.php') ?></td>
	</tr>
	<tr>
			<td height="388" valign="top"><table width="100%" border="0" cellpadding="0" cellspacing="0" class="border">
               <!--DWLayoutTable-->
				<tr>
                    <td height="22" colspan="3" align="center" valign="middle" class="caption">Settings</td>
                    </tr>
				<tr>
					<td height="30" colspan="3" valign="top"><? require 'keywords.menu.php' ?></td>
				</tr>
				<tr>
					<td width="69" height="18">&nbsp;</td>
					<td width="648">&nbsp;</td>
					<td width="71">&nbsp;</td>
				</tr>
				<tr>
                    <td height="233">&nbsp;</td>
                    <td valign="top">
                        <fieldset>
							<legend>Attribution</legend>
							<table width="646" border="0" cellpadding="0" cellspacing="0">
					        <!--DWLayoutTable-->
								<tr>
									<td width="17" height="23">&nbsp;</td>
									<td width="190">&nbsp;</td>
									<td colspan="2" valign="top" class="error"> <? if(isset($errors)) echo $errors; ?></td>
									<td width="93">&nbsp;</td>
								</tr>		     
								<tr>
									<td height="82"></td>
									<td align="right" valign="middle">
										<?
											$status = globalsettings_get_value('attribution');
											$link = $status ? '<a href="?attribution=0" style="color:#FF3300; text-decoration:none" title="Click to Turn OFF Attribution">Turn OFF Attribution</a>' : '<a href="?attribution=1" style="color: #669933; text-decoration:none" title="Click to Turn ON Attribution">Turn ON Attribution</a>';
											$label = $status ? '<span style="font-weight: bold; color: #669933;">Attribution is currently ON</span>' : '<span style="font-weight:bold; color:#FF3300">Attribution is currently OFF</span>';
											//$button = $label.'<input type="submit" class="button" name="attribution" value="'.$value.'" />';
											echo $label;
										?>:
									</td>
									<td colspan="2" valign="middle" class="field">&nbsp;&nbsp;<?= $link;?></td>
									<td></td>
								</tr>
								<tr>
									<td></td>
									<td align="right"></td>
									<td colspan="2" valign="middle" id="tnote"></td>
									<td></td>
								</tr>
								<tr>
					                <td height="18"></td>
					                <td></td>
					                <td>&nbsp;</td>
					                <td></td>
					                <td></td>
			                     </tr>
					        </table>
                        </fieldset> 
					</td>
                    <td>&nbsp;</td>
				</tr>
				<tr>
                    <td height="55">&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
				</tr>     
			</table>
		</td>
    </tr>
    <tr>
        <td height="30" valign="top"><? include('bottom.php') ?></td>
    </tr>
</table>
</body>
</html>
<? if(strlen($thtml)) print '<script>snote()</script>'; ?>

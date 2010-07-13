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
check_admin_user();

if(count($_POST)) {
     extract($_POST);
}
require 'report.inc.php';

if(isset($errors)) {
  $errors = "<br/>$errors<br/>";
}
if(isset($msg)) {
  $msg = '<br/><span style="color: #008800">'.$msg.'<br/>';
}
/* menu highlight */
$page = 'reports';

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title><?= TITLE ?></title>
<link rel="stylesheet" type="text/css" href="styles/style.css" />
<link rel="stylesheet" type="text/css" href="styles/date.css" />
<script type="text/javascript" src="date.js"></script>
</head>

<body>
<table width="790" border="0" align="center" cellpadding="0" cellspacing="0" class="main">
     <!--DWLayoutTable-->
     <tr>
          <td width="790" height="124" valign="top"><? include('top.php') ?></td>
     </tr>
     <tr>
          		<td height="407" valign="top"><table width="100%" border="0" cellpadding="0" cellspacing="0" class="border">
          					<!--DWLayoutTable-->
          					<tr>
          								<td height="22" colspan="3" align="center" valign="middle" class="caption">Reporting </td>
               									</tr>
          					<tr>
          								<td width="69" height="30">&nbsp;</td>
                   											<td width="648">&nbsp;</td>
                   											<td width="71">&nbsp;</td>
          								</tr>
          					<tr>
          								<td height="322">&nbsp;</td>
          											<td valign="top">
          														<fieldset>
       																	<legend>Select Report Option</legend>
					     									<table width="100%" border="0" cellpadding="0" cellspacing="0">
					     												<!--DWLayoutTable-->
					     												<tr>
					     															<td width="17" height="22">&nbsp;</td>
                   									<td width="196">&nbsp;</td>
                   									<td width="16">&nbsp;</td>
                   									<td width="4">&nbsp;</td>
                   									<td width="316">&nbsp;</td>
                   									<td width="97">&nbsp;</td>
                  									</tr>
					     												<tr>
					     															<td height="23">&nbsp;</td>
					     															<td>&nbsp;</td>
					     															<td colspan="3" valign="top" class="error">
					     																		<?= isset($errors) ? $errors : $msg ?>             </td>
                   									<td>&nbsp;</td>
		     															</tr>
					     												<form method="post">
					     															
					     															<tr>
					     																		<td height="26"></td>
					     																		<td align="right" valign="middle" class="label">1&nbsp;&nbsp;</td>
                    																			<td colspan="2" valign="middle"><input name="type" type="radio" value="1" <?= $type == 1 ? 'checked="checked"' : NULL ?>/></td>
                    																			<td valign="middle" class="field">Quiz</td>
                    																			<td></td>
		     																		</tr>
					     															<tr>
					     																		<td height="26"></td>
					     																		<td align="right" valign="middle" class="label">2&nbsp;&nbsp;</td>
                    																			<td colspan="2" valign="middle"><input name="type" type="radio" value="2" <?= $type == 2 ? 'checked="checked"' : NULL ?>/></td>
                    																			<td valign="middle" class="field">Coded Surveys </td>
                    																			<td></td>
		     																		</tr>
					     															<tr>
					     																		<td height="26"></td>
					     																		<td align="right" valign="middle" class="label">3&nbsp;&nbsp;</td>
                    																			<td colspan="2" valign="middle"><input name="type" type="radio" value="3" <?= $type == 3 ? 'checked="checked"' : NULL ?>/></td>
                    																			<td valign="middle" class="field">Keyword Information System </td>
                    																			<td></td>
		     																		</tr>	
					     															<tr>
					     																		<td height="26"></td>
					     																		<td align="right" valign="middle" class="label">4&nbsp;&nbsp;</td>
                    																			<td colspan="2" valign="middle"><input name="type" type="radio" value="4" <?= $type == 4 ? 'checked="checked"' : NULL ?>/></td>
                    																			<td valign="middle" class="field">Mobile Surveys </td>
                    																			<td></td>
		     																		</tr>																																													
					     															
					     															<tr>
					     																		<td height="34"></td>
					     																		<td>&nbsp;</td>
                    									<td colspan="3" valign="middle" class="caption4">Choose Period</td>
                    									<td></td>
		     																		</tr>
					     															<tr>
					     																		<td height="27"></td>
					     																		<td align="right" valign="middle" class="label">From:&nbsp;&nbsp;</td>
					     																		<td valign="middle"><img src="images/datepicker.gif" width="16" height="16" style="cursor: pointer" title="Select Start date" onclick="displayDatePicker('date1')"/></td>
					     																		<td colspan="2" valign="middle">&nbsp;<input name="date1" type="text" class="input" id="date1" value="<?= $date1 ?>" size="30" readonly="true" style="cursor: pointer" title="Select Start date" onclick="displayDatePicker('date1')"/></td>
					     																		<td></td>
		     																		</tr>
					     															<tr>
					     																		<td height="27"></td>
					     																		<td align="right" valign="middle" class="label">To:&nbsp;&nbsp;</td>
					     																		<td valign="middle"><img src="images/datepicker.gif" width="16" height="16" style="cursor: pointer" title="Select End date" onclick="displayDatePicker('date2')"/></td>
					     																		<td colspan="2" valign="middle">&nbsp;<input name="date2" type="text" class="input" id="date2" value="<?= $date2 ?>" size="30" readonly="true" style="cursor: pointer" title="Select End date" onclick="displayDatePicker('date2')"/></td>
					     																		<td></td>
		     																		</tr>															
					     															<tr>
					     																		<td height="35"></td>
					     																		<td>&nbsp;</td>
					     																		<td colspan="3" valign="middle"><input name="submit" type="submit" class="button" id="submit" value="Show Report" /></td>
                    									<td></td>
		     																		</tr>
					     															<tr>
					     																		<td height="35"></td>
					     																		<td>&nbsp;</td>
					     																		<td>&nbsp;</td>
					     																		<td>&nbsp;</td>
					     																		<td></td>
					     																		<td></td>
		     																		</tr>
					     															</form>
					     												</table>
       															</fieldset></td>
                         									<td>&nbsp;</td>
       											</tr>
          					<tr>
          								<td height="31">&nbsp;</td>
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
<?
if(isset($showreport)) {
   print '<script type="text/javascript">window.open("report.php")</script>';
}
?>

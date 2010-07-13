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
check_admin_user();

extract($_GET);

if(isset($_POST["cancel"])) {  
    redirect('users.php');
}

if(isset($_POST["submit"])) {
   redirect("edituser.php?userId=$userId");
}

if(!preg_match("/^[0-9]+$/", $userId)) { 
   redirect('users.php');
}
$sql = "SELECT user.*, (DATE_FORMAT(CURRENT_DATE(), '%Y') - DATE_FORMAT(dob, '%Y')) AS age, DATE_FORMAT(dob, '%d/%m/%Y') AS _dob, DATE_FORMAT(createdate, '%d/%m/%Y %r') AS createdate, updated FROM user WHERE id=$userId"; 

$result = execute_query($sql);
if(!mysql_num_rows($result)) {
   redirect('users.php');
} 
$user = mysql_fetch_assoc($result);
$activity = get_user_activity($user['misdn'], $user['phones']);
extract($user);
$group_list = get_group_list($groups);
$district = 'N/A';
if($subcountyId) {
     $subcounty = get_subcounty_from_id($subcountyId);
	 $district = $subcounty['district'].' ('.$subcounty['name'].' subcounty)';
}
/* menu highlight */
$page = 'userphones';

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
     <!--DWLayoutTable-->
     <tr>
          <td width="790" height="124" valign="top"><? include('top.php') ?></td>
     </tr>
     <tr>
          		<td height="581" valign="top"><table width="100%" border="0" cellpadding="0" cellspacing="0" class="border">
          					<!--DWLayoutTable-->
          					<tr>
          								<td height="22" colspan="3" align="center" valign="middle" class="caption"> View / Edit User Information </td>
                    			</tr>
          					<tr>
          								<td width="42" height="30">&nbsp;</td>
                    			<td width="708" valign="top"><? require 'users.menu.php' ?></td>
          								<td width="38">&nbsp;</td>
               			</tr>
          					
          					<tr>
          								<td height="358">&nbsp;</td>
               						<td valign="top">
               											<fieldset>
               														<legend>User Information</legend>
					     			<table width="100%" border="0" cellpadding="0" cellspacing="0">
					     						<!--DWLayoutTable-->
					     						<tr>
					     									<td width="15" height="23">&nbsp;</td>
                   			<td width="236">&nbsp;</td>
                   			<td width="388">&nbsp;</td>
                   			<td width="67">&nbsp;</td>
					          			</tr>
					     						<form method="post">
					     									<tr>
					     												<td height="30">&nbsp;</td>
                    			<td align="right" valign="middle">Names:&nbsp;&nbsp;</td>
                    			<td valign="middle" class="field"><?= $names ? $names : 'N/A' ?></td>
                    			<td>&nbsp;</td>
					               			</tr>
					     									<tr>
					     												<td height="30">&nbsp;</td>
                    			<td align="right" valign="middle">Phone Number:&nbsp;&nbsp;</td>
                    			<td valign="middle" class="field"><?= $misdn ? $misdn : 'N/A' ?></td>
                    			<td>&nbsp;</td>
					               			</tr>
					     									<!--tr>
					     												<td height="30">&nbsp;</td>
                    			<td align="right" valign="middle">Hits:&nbsp;&nbsp;</td>
                    			<td valign="middle" class="field"><?= $hits ?></td>
                    			<td>&nbsp;</td>
					               			</tr-->								   
					     									<tr>
					     												<td height="30">&nbsp;</td>
                    			<td align="right" valign="middle">Other Phone Number(s):&nbsp;&nbsp;</td>
                    			<td valign="middle" class="field"><?= $phones ? preg_replace("/,/", ", ", $phones): 'N/A' ?></td>
                    			<td>&nbsp;</td>
					               			</tr>	
					     									<tr>
					     												<td height="30">&nbsp;</td>
                    			<td align="right" valign="middle">Gender:&nbsp;&nbsp;</td>
                    			<td valign="middle" class="field"><?= $gender ? $gender : 'N/A' ?></td>
                    			<td>&nbsp;</td>
					               			</tr>					   					
					     									<tr>
					     												<td height="30">&nbsp;</td>
                    			<td align="right" valign="middle">Age:&nbsp;&nbsp;</td>
                    			<td valign="middle" class="field"><?= !preg_match("/0000-00-00/", $dob) ? "$age (Born $_dob)" : $dob  ?></td>
                    			<td>&nbsp;</td>
					               			</tr>
																     									<tr>
					     												<td height="30">&nbsp;</td>
                    			<td align="right" valign="middle">District:&nbsp;&nbsp;</td>
                    			<td valign="middle" class="field"><?= $district  ?></td>
                    			<td>&nbsp;</td>
					               			</tr>
					     									<tr>
					     												<td height="30">&nbsp;</td>
                    			<td align="right" valign="middle">Occupation:&nbsp;&nbsp;</td>
                    			<td valign="middle" class="field">
								<?= $occupationId ? get_column_value('occupation', 'name', $occupationId) : 'N/A' ?></td>
                    			<td>&nbsp;</td>
					               			</tr>
					     									<tr>
					     												<td height="30">&nbsp;</td>
                    			<td align="right" valign="middle">Location:&nbsp;&nbsp;</td>
                    			<td valign="middle" class="field"><?= $location ? $location : 'N/A' ?></td>
                    			<td>&nbsp;</td>
					               			</tr> 
																     									<tr>
					     												<td height="30">&nbsp;</td>
                    			<td align="right" valign="middle">GPS Co-ordinates:&nbsp;&nbsp;</td>
                    			<td valign="middle" class="field"><?= strlen($gpscordinates) ? $gpscordinates : 'N/A' ?></td>
                    			<td>&nbsp;</td>
					               			</tr> 	
					     									<tr>
					     												<td height="30">&nbsp;</td>
                    			<td align="right" valign="middle">Group(s):&nbsp;&nbsp;</td>
                    			<td valign="middle" class="field"><?= $group_list  ?></td>
                    			<td>&nbsp;</td>
				               			</tr> 	
					     									<tr>
					     												<td height="30">&nbsp;</td>
                    			<td align="right" valign="middle">Device information:&nbsp;&nbsp;</td>
                    			<td valign="middle" class="field"><?= $deviceInfo ? $deviceInfo : 'N/A' ?> </td>
                    			<td>&nbsp;</td>
				               			</tr> 	
					     									<tr>
					     												<td height="30">&nbsp;</td>
                    			<td align="right" valign="middle">Notes:&nbsp;&nbsp;</td>
                    			<td valign="middle" class="field">
                    						<?= $notes ? $notes : 'N/A' ?></td>
                    			<td>&nbsp;</td>
				               			</tr> 	
					     									<tr>
					     												<td height="30">&nbsp;</td>
                    			<td align="right" valign="middle">
                    						Created:&nbsp;&nbsp;</td>
                    			<td valign="middle" class="field"><?= $createdate ?></td>
                    			<td>&nbsp;</td>
				               			</tr> 
					     									<tr>
					     												<td height="30">&nbsp;</td>
                    			<td align="right" valign="middle">
                    						Updated:&nbsp;&nbsp;</td>
                    			<td valign="middle" class="field"><?= $updated ?></td>
                    			<td>&nbsp;</td>
				               			</tr> 
				     									<tr>
					     												<td height="30">&nbsp;</td>
                    			<td colspan="2" align="center" valign="middle" class="caption2"> <u>Actvity Information</u> </td>
                    			<td>&nbsp;</td>
				               			</tr> 
				     									<tr>
					     												<td height="30">&nbsp;</td>
                    			<td align="right" valign="middle">Quiz:&nbsp;&nbsp;</td>
                    			<td valign="middle" class="field" style="cursor: pointer" title="Click for details" onclick="location.replace('activity.php?userId=<?= $user['id'] ?>&t=quizreply')"><strong><?= $activity['quiz'] ?></strong>&nbsp;Hit(s) </td>
                    			<td>&nbsp;</td>
				               			</tr> 
				     									<tr>
					     												<td height="30">&nbsp;</td>
                    			<td align="right" valign="middle">Coded Surveys:&nbsp;&nbsp;</td>
                    			<td valign="middle" class="field" style="cursor: pointer" title="Click for details" onclick="location.replace('activity.php?userId=<?= $user['id'] ?>&t=sresult')"><strong><?= $activity['csurvey'] ?></strong>&nbsp;Hit(s) </td>
                    			<td>&nbsp;</td>
				               			</tr> 
				     									<tr>
					     												<td height="30">&nbsp;</td>
                    			<td align="right" valign="middle">Enhanced Surveys:&nbsp;&nbsp;</td>
                    			<td valign="middle" class="field" style="cursor: pointer" title="Click for details" onclick="location.replace('activity.php?userId=<?= $user['id'] ?>&t=mresult')"><strong><?= $activity['msurvey'] ?></strong>&nbsp;Hit(s) </td>
                    			<td>&nbsp;</td>
				               			</tr> 
				     									<tr>
					     												<td height="30">&nbsp;</td>
                    			<td align="right" valign="middle">Keywords:&nbsp;&nbsp;</td>
                    			<td valign="middle" class="field" style="cursor: pointer" title="Click for details" onclick="location.replace('activity.php?userId=<?= $user['id'] ?>&t=hit')"><strong><?= $activity['keyword'] ?></strong>&nbsp;Hit(s) </td>
                    			<td>&nbsp;</td>
				               			</tr> 	
				     									<tr>
					     												<td height="30">&nbsp;</td>
                    			<td align="right" valign="middle">Google SMS:&nbsp;&nbsp;</td>
                    			<td valign="middle" class="field" style="cursor: pointer" title="Google SMS 6001">
								<strong><?= $activity['googlesms'] ?></strong>&nbsp;Hit(s) </td>
                    			<td>&nbsp;</td>
				               			</tr> 	
														     									<tr>
					     												<td height="30">&nbsp;</td>
                    			<td align="right" valign="middle">Health IVR:&nbsp;&nbsp;</td>
                    			<td valign="middle" class="field" style="cursor:pointer" title="Health IVR">
								<strong><?= $activity['healthivr'] ?></strong>&nbsp;Hit(s) </td>
                    			<td>&nbsp;</td>
				               			</tr> 	
														<tr>
					     								<td height="30">&nbsp;</td>
															<td align="right" valign="middle">Form Surveys:&nbsp;&nbsp;</td>
															<td valign="middle" class="field" style="cursor:pointer" title="Click for Details" onclick="location.replace('activity.php?userId=<?=$user['id']?>&t=oldFormSurveys')">
																	<strong><?= $activity['formsurveys'] ?></strong>&nbsp;Hit(s) 
															</td>
															<td>&nbsp;</td>
				               			</tr> 
					     									<tr>
					     												<td height="35">&nbsp;</td>
                    			<td>&nbsp;</td>
                    			<td valign="middle"><input name="submit" type="submit" class="button" id="submit" value="Edit Information" />
                    								<input name="cancel" type="submit" class="button" id="cancel" value="Go To Users" /></td>
                    			<td>&nbsp;</td>
					               			</tr>
					     									<tr>
					     												<td height="16">&nbsp;</td>
					               						<td>&nbsp;</td>
					               						<td>&nbsp;</td>
					               						<td>&nbsp;</td>
		               									</tr>
					     									</form>
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

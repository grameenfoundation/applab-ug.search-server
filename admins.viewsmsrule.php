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

dbconnect();
validate_session(); 
check_admin_user();

extract($_GET);

if(!preg_match("/^[0-9]+$/", $id)) { 
   goto('admins.smsrules.php');
}

if($_POST[edit]){
	goto('admins.editsmsrules.php?id='.$id);
}

if($_POST[delete]){
	goto('admins.smsrules.php?id='.$id);
}

if($_POST[cancel]){
	goto('admins.smsrules.php?id=');
}

$result = get_table_record('smsForward', $_GET[id], 'id');

if(!$result){
	goto("admins.smsrules.php");
}

extract($result);
$httpVars = unserialize($httpVars);
$routing_policy = $shortCode? "Shortcode Routing" : "Keyword Routing";
$keyword_shortcode = $shortCode? $shortCode : $keyword;


/* menu highlight */
$page = 'users';

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title><?= TITLE ?></title>
<link rel="stylesheet" type="text/css" href="styles/style.css" />
<script type="text/javascript" src="basic.js"></script>
</head>

<body class="main">
<table width="790" border="0" align="center" cellpadding="0" cellspacing="0">
     <!--DWLayoutTable-->
     <tr>
          <td width="790" height="124" valign="top"><? include('top.php') ?></td>
     </tr>
     <tr>
          <td height="406" valign="top"><table width="100%" border="0" cellpadding="0" cellspacing="0" class="border">
               <!--DWLayoutTable-->
               <tr>
                    <td height="22" colspan="3" align="center" valign="middle" class="caption">Keyword - <?= $keyword ?>
                    </td>
                    </tr>
               <tr>
                    <td width="56" height="30">&nbsp;</td>
                    <td width="679">&nbsp;</td>
                    <td width="53">&nbsp;</td>
               </tr>
               <tr>
                  <td height="313">&nbsp;</td>
                  <td valign="top">
				       <fieldset>
					<legend>Routing Details</legend>
					<table width="100%" border="0" cellpadding="0" cellspacing="0" 
					style="background-image: none; background-repeat: no-repeat; background-position: center">
                         <!--DWLayoutTable-->
                         <tr>
                              <td width="91" height="24">&nbsp;</td>
                              <td width="129" valign="top">							  </td>
                              <td width="362" valign="top"></td>
                              <td width="36">&nbsp;</td>
                              <td width="59">&nbsp;</td>
                         </tr>
                         <tr>
                              <td height="30">&nbsp;</td>
                              <td align="right" valign="middle" class="label">Description:&nbsp;&nbsp;							  </td>
                              <td valign="middle" class="field"><?= $description ?>                                   &nbsp;</td>
                              <td>&nbsp;</td>
                              <td>&nbsp;</td>
                         </tr>
						 <tr>
                              <td height="30">&nbsp;</td>
                              <td align="right" valign="middle" class="label">Notes:&nbsp;&nbsp;							  </td>
                              <td valign="middle" class="field"><?= $notes? $notes: "N/A"; ?>                                   &nbsp;</td>
                              <td>&nbsp;</td>
                              <td>&nbsp;</td>
                         </tr>
                         <tr>
                              <td height="30">&nbsp;</td>
                              <td align="right" valign="middle" class="label">Routing in Use:&nbsp;&nbsp;							  </td>
                              <td valign="middle" class="field"><?=$routing_policy?>                                   &nbsp;</td>
                              <td>&nbsp;</td>
                              <td>&nbsp;</td>
                         </tr>
												<?if(strlen($shortCode)){?>
                         <tr>
                              <td height="30">&nbsp;</td>
                              <td align="right" valign="middle" class="label">Shortcode:&nbsp;&nbsp;							  </td>
                              <td valign="middle" class="field"><?= $shortCode ?>                                   &nbsp;</td>
                              <td>&nbsp;</td>
                              <td>&nbsp;</td>
                         </tr>
						 						<?
												}
												if(strlen($keyword)){?>
                        <tr>
                              <td height="30">&nbsp;</td>
                              <td align="right" valign="middle" class="label">Keyword:&nbsp;&nbsp;</td>
                              <td valign="middle" class="field"><?= $keyword? $keyword : 'N/A' ?>&nbsp;</td>
                              <td>&nbsp;</td>
                              <td>&nbsp;</td>
                        </tr>
												<tr>
                              <td height="30">&nbsp;</td>
                              <td align="right" valign="middle" class="label">B number:&nbsp;&nbsp;</td>
                              <td valign="middle" class="field"><?= $newBnumber? $newBnumber : 'Default set to Received B number' ?>&nbsp;</td>
                              <td>&nbsp;</td>
                              <td>&nbsp;</td>
                        </tr>
												<?}?>
                        <tr>
                              <td height="30">&nbsp;</td>
                              <td align="right" valign="middle" class="label">Destination URL:&nbsp;&nbsp;</td>
                              <td valign="middle" class="field"><?= $url ? $url : NONE ?></td>
                              <td>&nbsp;</td>
                              <td>&nbsp;</td>
                        </tr>			
                        <tr>
                              <td height="30">&nbsp;</td>
                              <td align="right" valign="middle" class="label">HTTP method:&nbsp;&nbsp;</td>
                              <td valign="middle" class="field"><?= ($method==0)? "POST" : "GET" ?></td>
                              <td>&nbsp;</td>
                              <td>&nbsp;</td>
                        </tr>	
                        <tr>
                              <td height="30">&nbsp;</td>
                              <td align="right" valign="middle" class="label">HTTP variables:&nbsp;&nbsp;</td>
                              <td valign="middle" class="field">
																<?
																if(count($httpVars)){
																	echo "<table border=\"0\" >";
																	foreach($httpVars as $variable => $value){
																		echo "<tr><td align=\"right\">$variable:&nbsp;&nbsp;</td><td class=\"field\">$value</td></tr>";
																	}
																	echo "</table>";
																}else{
																	echo "NONE";
																}
																?>
															</td>
                              <td>&nbsp;</td>
                              <td>&nbsp;</td>
                        </tr>
                        <tr>
                              <td height="30">&nbsp;</td>
                              <td align="right" valign="middle" class="label">Create Time:&nbsp;&nbsp;</td>
                              <td valign="middle" class="field"><?= $createdate ?></td>
                              <td>&nbsp;</td>
                              <td>&nbsp;</td>
                        </tr>																		
                        					 					 						 					 						 
                        <form method="post">
				      <tr>
                              <td height="48"></td>
                              <td colspan="3" valign="middle" class="field"> 
                                    <input name="edit" type="submit" class="button" id="edit" value="Edit"/>
                                    <input name="delete" type="submit" class="button" id="delete" value="Delete" 
							   onclick="return confirm('Are you sure you want to delete this ?')"/>
                        <input name="cancel" type="submit" class="button" id="cancel" value="&laquo; Go Back"/>						     </td>
                             <td>&nbsp;</td>
				      </tr>
				      <tr>
				        <td height="16"></td>
				        <td></td>
				        <td></td>
				        <td></td>
				        <td></td>
				        </tr>
				      </form>
                    </table>
		               </fieldset></td>
                    <td>&nbsp;</td>
               </tr>
               <tr>
                  <td height="37">&nbsp;</td>
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

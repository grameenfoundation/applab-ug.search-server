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

extract($_GET);

check_id($districtId, 'districts.php');

if(isset($_POST['cancel'])) {
   goto("districts.php");
}
if(isset($_POST['edit'])) {
   goto("editdistrict.php?districtId=$districtId");
}
if(isset($_POST['subcounties'])) {
   goto("subcounties.php?districtId=$districtId");
}

if(isset($_POST['delete'])) {
  delete_district($districtId);
  goto('districts.php');
}


$sql = "SELECT district.*, DATE_FORMAT(updated, '%d/%m/%Y %r') AS updated FROM district WHERE id=$districtId";
       
$result = execute_query($sql);
if(!mysql_num_rows($result)) {
   goto('districts.php');
}
extract(mysql_fetch_assoc($result));

$subcounties = get_item_count('subcounty', 'districtId', $districtId);

/* menu highlight */
$page = 'userphones';

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
          <td height="301" valign="top"><table width="100%" border="0" cellpadding="0" cellspacing="0" class="border">
               <!--DWLayoutTable-->
               <tr>
                    <td height="22" colspan="3" align="center" valign="middle" class="caption">District</td>
                    </tr>
               <tr>
                    <td width="56" height="30">&nbsp;</td>
                    <td width="679">&nbsp;</td>
                    <td width="53">&nbsp;</td>
               </tr>
               <tr>
                 <td height="193">&nbsp;</td>
                 <td valign="top">
			          <fieldset>
					<legend>District Details</legend>
					<table width="100%" border="0" cellpadding="0" cellspacing="0">
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
                              <td align="right" valign="middle" class="label">Name:&nbsp;&nbsp;							  </td>
                              <td valign="middle" class="field"><?= $name ?></td>
                              <td>&nbsp;</td>
                              <td>&nbsp;</td>
                         </tr>
						 <tr>
                              <td height="30">&nbsp;</td>
                              <td align="right" valign="middle" class="label">Updated:&nbsp;&nbsp;							  </td>
                              <td valign="middle" class="field"><?= $updated ?></td>
                              <td>&nbsp;</td>
                              <td>&nbsp;</td>
						 </tr>
                         <tr>
                              <td height="30">&nbsp;</td>
                              <td align="right" valign="middle" class="label">Subcounties:&nbsp;&nbsp;							  </td>
                              <td valign="middle" class="field"><?= $subcounties ?>                                  &nbsp;</td>
                              <td>&nbsp;</td>
                              <td>&nbsp;</td>
                         </tr>
                        					 					 						 					 						 
                        
				      <tr>
                              <td height="48"></td>
                              <td></td>
                              <td colspan="2" valign="middle" class="field">
							    <form method="post">
							    <input name="edit" type="submit" class="button" id="edit" value="Edit District"/>
							    <input name="subcounties" type="submit" class="button" id="subcounties" value="Subcounties"/>
							     <input name="delete" type="submit" class="button" id="delete" value="Delete" 
							   onclick="return confirm('Are you sure you want to delete this district?')"/>
                        <input name="cancel" type="submit" class="button" id="cancel" value="&laquo; Go To Districts"/>						     
						        </form></td>
                             <td>&nbsp;</td>
				      </tr>
				      <tr>
				        <td height="16"></td>
				        <td></td>
				        <td></td>
				        <td></td>
				        <td></td>
			          </tr>
                    </table>
                 </fieldset></td>
                    <td>&nbsp;</td>
               </tr>
               <tr>
                 <td height="54">&nbsp;</td>
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

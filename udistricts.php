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
require 'excel/functions.php';

dbconnect();
validate_session(); 
check_admin_user();

if(isset($_POST["cancel"])) {
   goto('users.php');
}

if(count($_POST)) {
  $_POST = strip_form_data($_POST);
  extract($_POST);
}  

if(isset($_POST["submit"])) 
{
   if(!preg_match("/^[0-9]+$/", $start) || !$start) {
      $errors = 'Start of record in file not valid<br/>';
   }
   if(!$district_col) {
      $errors .= 'District column required<br/>';
   }  
   if(!isset($errors)) {
      $ret = import_districts();
	  if(!is_array($ret)) {
	      $errors = $ret.'<br/>';
	  } 
   }
  
   if(!isset($errors)) 
   { 
       $districts_inserted = 0;
	   $subcounties_inserted = 0;
	   
	   foreach($ret as $district) 
	   {
	       $sql = "SELECT * FROM district WHERE LOWER(name)=LOWER('$district[name]')";
		   $result=execute_query($sql);
		   if(!mysql_num_rows($result)) 
		   {
		       $sql = "INSERT INTO district(created, name) VALUES(NOW(), '$district[name]')";
			   execute_update($sql);
			   $districtId = mysql_insert_id();
			   $districts_inserted++;
		   }
		   else 
		   {
		       $row = mysql_fetch_assoc($result);
			   $districtId = $row['id'];
		   }
		   $subcounties = $district['subcounties'];
		   if(!count($subcounties)) {
		       continue;
		   }
		   foreach($subcounties as $subcounty) 
		   {
		        $sql = "SELECT * FROM subcounty WHERE districtId='$districtId' AND name='$subcounty'";
				$result=execute_query($sql);
				if(!mysql_num_rows($result)) {
				    $sql = "INSERT INTO subcounty(created, districtId, name) VALUES(NOW(), '$districtId', '$subcounty')";
					execute_update($sql);
					$subcounties_inserted++;
				}
		   }
	   }
   }
   show_message('File successfuly processed', 'A total of ('.$districts_inserted.
   ') Districts and ('.$subcounties_inserted.') Subcounties have been added', 'green');
}

if(isset($errors)) {
  $errors = "<br/>$errors<br/>";
}
if(!count($_POST)) {
   $start = 2;
   $district_col = 1;
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
          <td height="323" valign="top"><table width="100%" border="0" cellpadding="0" cellspacing="0" class="border">
               <!--DWLayoutTable-->
               <tr>
                    <td height="22" colspan="3" align="center" valign="middle" class="caption">Import Districts From File </td>
                    </tr>
               <tr>
                    <td width="69" height="48"></td>
                    <td width="648" valign="top"><? require 'users.settings.php' ?></td>
               <td width="71"></td>
               </tr>
               
               
               
               <tr>
                  <td height="203">&nbsp;</td>
                  <td valign="top">
                            <fieldset>
                            <legend>Import From File</legend>
					     <table width="" border="0" cellpadding="0" cellspacing="0">
					          <!--DWLayoutTable-->
					          <tr>
					               <td width="22" height="23">&nbsp;</td>
                                   <td width="163">&nbsp;</td>
                                   <td width="30">&nbsp;</td>
                                   <td width="13">&nbsp;</td>
                                   <td width="374" valign="top" class="error">
                                      <? if(isset($errors)) echo $errors; ?>             </td>
                   <td width="44">&nbsp;</td>
                            </tr>
					          <form method="post" enctype="multipart/form-data">
					               <tr>
					                    <td height="30">&nbsp;</td>
                                        <td align="right" valign="middle">File</td>
                                        <td align="right" valign="top"><img src="images/excel.jpg" width="30" height="30" style="cursor: pointer" title="Browse Excel file containing the keywords"/></td>
                                      <td align="right" valign="middle">:&nbsp;&nbsp;</td>
                    <td valign="middle"><input type="file" name="file" size="40" style="font-size: 11px"/></td>
                    <td>&nbsp;</td>
                 </tr>
				  <tr>
					                    <td height="30">&nbsp;</td>
                                        <td colspan="3" align="right" valign="middle" nowrap="nowrap">District:&nbsp;&nbsp; </td>
                           				<td valign="middle"><select name="district_col" class="input" id="district_col" style="width: 285px">
													<option value="0"></option>
													<?= get_excel_columns($district_col) ?>
																				</select>									</td>
                    <td>&nbsp;</td>
                  </tr>
				  <tr>
					                    <td height="30">&nbsp;</td>
                                        <td colspan="3" align="right" valign="middle" nowrap="nowrap">Subcounty:&nbsp;&nbsp; </td>
                           				<td valign="middle"><select name="subcounty_col" class="input" id="subcounty_col" style="width: 285px">
													<option value="0"></option>
													<?= get_excel_columns($subcounty_col) ?>
																				</select>									</td>
                    <td>&nbsp;</td>
                  </tr>				  
				  
				  <tr>
				     <td height="31">&nbsp;</td>
				     <td colspan="3" align="right" valign="middle">Start of Record:&nbsp;&nbsp; </td>
				     <td valign="middle"><input name="start" type="text" class="input" id="start" value="<?= $start ?>" size="41" maxlength="1" /></td>
				     <td>&nbsp;</td>
				     </tr>
				  <tr>
				     <td height="35">&nbsp;</td>
				     <td>&nbsp;</td>
				     <td>&nbsp;</td>
				     <td>&nbsp;</td>
				     <td valign="middle">
						      <input name="submit" type="submit" class="button" id="submit" value="Upload Districts" />
                              <input name="cancel" type="submit" class="button" id="cancel" value="Cancel" /></td>
                    <td>&nbsp;</td>
				  </tr>
				  <tr>
				     <td height="20"></td>
				     <td></td>
				     <td></td>
				     <td></td>
				     <td></td>
				     <td></td>
				     </tr>
				  <tr>
				     <td height="47"></td>
				     <td colspan="4" valign="middle" id="note"><span style="color: #CC0000">Please Note:</span> Districts can only be imported from Excel Files. The first column of the Excel file must contain the district, the second may contain the subcounty for the district in the first column </td>
				     <td></td>
				     </tr>
				  <tr>
				     <td height="25"></td>
				     <td>&nbsp;</td>
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
                  <td height="48">&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
               </tr>
               
          </table></td>
     </tr><tr>
          <td height="30" valign="top"><? include('bottom.php') ?></td>
     </tr>
</table>
</body>
</html>

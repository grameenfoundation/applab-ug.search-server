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

if(isset($delete)) {   
    check_id($occupationId, 'occupations.php');
    $occupation = get_table_record('occupation', $occupationId);
	if(empty($occupation)) {
	    goto('occupations.php');
	}
	$result=execute_query("SELECT * FROM user WHERE occupationId='$occupationId'");
    if(mysql_num_rows($result)) 
	{
        $message = '<ul>';
		while($row=mysql_fetch_assoc($result)) {
		    $message .= '<li>'.$row['names'].' ('.$row['misdn'].')</li>';
		}	
		$message .= '</ul>';	
		$message = 'This record is associatated with the following user(s): '.$message.'
		You need to first remove this record from the above user(s) before deleting it';
		
		show_message('Can not Delete Record', $message, '#FF0000');
    }
    $sql = "DELETE FROM occupation WHERE id='$occupationId'";
    execute_update($sql);
	logaction("Deleted occupation: $occupation[name]");
    goto('occupations.php');
}

$sql="SELECT occupation.*, DATE_FORMAT(updated, '%d/%m/%Y %r') AS date FROM occupation ORDER BY updated";
$result = execute_query($sql);
$total = mysql_num_rows($result);

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

<body>
<table width="790" border="0" align="center" cellpadding="0" cellspacing="0" class="main">
     <!--DWLayoutTable-->
     <tr>
          <td width="790" height="124" valign="top"><? include('top.php') ?></td>
     </tr>
     <tr>
          		<td height="372" valign="top"><table width="100%" border="0" cellpadding="0" cellspacing="0" class="border">
          					<!--DWLayoutTable-->
          					<tr>
          								<td height="22" colspan="4" align="center" valign="middle" class="caption">Occupations - Total: <?= $total ?></td>
                    			</tr>
          					<tr>
          								<td width="16" height="30">&nbsp;</td>
                         				<td colspan="2" valign="top"><? require 'users.settings.php' ?></td>
                         			<td width="20">&nbsp;</td>
               			</tr>
          					<tr>
          								<td height="295">&nbsp;</td>
               							<td width="12">&nbsp;</td>
               							<td width="740" valign="top">
               									<?
	 $html = '<table width="100%" border="0" cellpadding="0" cellspacing="0">
	 <tr class="title1">
	  <td height="25" valign="top"><u>Name</u></td>
	  <td valign="top"><u>Updated</u></td>
	  <td valign="top"><u>Options</u></td>
	 </tr>';
	  
	 $color = '#EEEEEE'; $i=0;
     
	 while($row = mysql_fetch_assoc($result)) 
	 {
	     $color = $i++%2 ? '#FFFFFF' : '#EEEEEE';
		 
	     $html .='
	        <tr bgcolor="'.$color.'" onmouseover="this.style.backgroundColor=\''.HOVERCOLOR.'\'"
	            onmouseout="this.style.backgroundColor=\''.$color.'\'">
	            <td height="25">&nbsp;'.truncate_str($row['name'], 50).'</td>
	            <td>'.$row['date'].'</td>
	            <td>
	               <a href="editoccupation.php?occupationId='.$row['id'].'">Edit</a> |
				   <a href="?occupationId='.$row['id'].'&delete=TRUE" style="color: #FF0000"
		           onclick="return confirm(\'Are you sure?\')">Delete</a>
	            </td>
	        </tr>';
	 }
	 $html .= '
	 <tr>
	     <td colspan=3 height=35><form action="addoccupation.php">
		 <input type=submit name=add value="Add Occupation" class=button /></form></td>
	 </tr>';
	 echo $html.'</table>';
	
	?>	</td>
         			<td>&nbsp;</td>
               						</tr>
          					<tr>
          								<td height="23">&nbsp;</td>
               							<td>&nbsp;</td>
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

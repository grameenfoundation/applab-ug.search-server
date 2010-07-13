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

check_id($keywordId, 'keywords.php');
if(!($keyword = get_table_record('keyword', $keywordId))) {
   goto('keywords.php');
}
if(isset($_POST['cancel'])) {
   goto("keyword.php?keywordId=$keywordId");
}
if(isset($_POST['add'])) {
   goto("addskeyword.php?keywordId=$keywordId");
}
if(isset($delete)) {
   check_id($subkeywordId, 'keywords.php');
   execute_update("DELETE FROM subkeyword WHERE id=$subkeywordId LIMIT 1");
   goto("subkeywords.php?keywordId=$keywordId");	   
}
$sql = "SELECT subkeyword.*, DATE_FORMAT(createdate, '%d/%m/%Y %r') AS created, 
        DATE_FORMAT(updated, '%d/%m/%Y %r') AS updated FROM subkeyword WHERE keywordId=$keywordId ORDER BY keyword";
$result = execute_query($sql);

/* menu highlight */
$page = 'keywords';

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
          <td height="360" valign="top"><table width="100%" border="0" cellpadding="0" cellspacing="0" class="border">
               <!--DWLayoutTable-->
               <tr>
                    <td height="22" colspan="3" align="center" valign="middle" class="caption"><?= $keyword['keyword'] ?> - Sub Keywords</td>
             </tr>
               <tr>
                    <td height="18" colspan="3" valign="top"><? require 'keywords.menu.php' ?></td>
               </tr>
               <tr>
                    <td width="14" height="300">&nbsp;</td>
                    <td width="757" valign="top">
		               <? 	
		$html = 
		'<table width="100%" border="0" cellpadding="0" cellspacing="0">
		 <tr class="title1"> 
		  <td height="30" valign="top"><u>Sub Keyword</u></td> 
		  <td valign="top"><u>Created</u></td>
		  <td valign="top"><u>Updated</u></td>
		  <td valign="top"><u>Options</u></td>
		 <tr>';
		$color = '#E4E4E4'; $i=0;
		while($row = mysql_fetch_assoc($result)) {
		 $color = $i++%2 ? '#FFFFFF' : '#EEEEEE';
		 $html .= '
		 <tr bgcolor="'.$color.'" 
		 onmouseover="this.style.backgroundColor=\''.HOVERCOLOR.'\'" onmouseout="this.style.backgroundColor=\''.$color.'\'"> 
		  <td height="25">&nbsp;
		    <a href="subkeyword.php?keywordId='.$keywordId.'&subkeywordId='.$row['id'].'" style="color: #000000" 
			title="'.$row['keyword'].' - Click to open Keyword">'.$row['keyword'].'</a>
		  </td>
		  <td>'.$row['created'].'</td> 
		  <td>'.$row['updated'].'</td>
		  <td>
		  <a href="editskeyword.php?keywordId='.$keywordId.'&subkeywordId='.$row["id"].'" title="Edit Sub Keyword">Edit</a> | 
		  <a href="?keywordId='.$keywordId.'&subkeywordId='.$row['id'].'&delete=TRUE" style="color: #FF0000" 
		   title="Delete Keyword" onclick="return confirm(\'Are you sure you want to delete this Sub Keyword?\')">Delete</a>
		  </td>
		 <tr>';
		}
		$html .='
		<form method="post">
		<tr>
		  <td colspan="4" height="45" valign="middle">
		     <input name="cancel" type="submit" class="button" value="&laquo; Go To Keyword">
			<input name="add" type="submit" class="button" value="Create Sub Keyword">
		  </td>
		</tr>
		</form>';
		 print $html.'</table>'; 
		?>		</td>
                    <td width="17">&nbsp;</td>
               </tr>
               <tr>
                  <td height="18"></td>
                  <td></td>
                  <td></td>
               </tr>
               
          </table></td>
     </tr>
   
     <tr>
          <td height="30" valign="top"><? include('bottom.php') ?></td>
     </tr>
</table>
</body>
</html>

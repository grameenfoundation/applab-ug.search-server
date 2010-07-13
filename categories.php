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

if(isset($_POST['add'])) {
   goto('addcategory.php');
}
if(isset($delete)) {
   if(!preg_match("/^[0-9]+$/", $categoryId)) {
       goto("categories.php");
   }
   $result = execute_query("SELECT * FROM keyword WHERE categoryId=$categoryId");
   $total = mysql_num_rows($result);
   if($total > 0) {
       show_message('Can not Delete Category', 'This category can not be deleted because '.$total.' Keyword(s) are associated with it', 'red');
   }
   execute_update("DELETE FROM category WHERE id=$categoryId");
   goto("categories.php");	   
}
$sql = "SELECT category.*, DATE_FORMAT(created, '%d/%m/%Y %T') AS created, 
        DATE_FORMAT(updated, '%d/%m/%Y %r') AS updated FROM category ORDER BY name";
$result = execute_query($sql);
$result2 = execute_query($sql);
if(isset($_POST['csvfile'])){
	$sql = "SELECT category.name, keyword.keyword , keyword.attribution, keyword.keywordAlias, keyword.optionalWords, "
                ."keyword.content FROM keyword LEFT JOIN category ON category.id=keyword.categoryId WHERE category.id = $category";
	$result3 = execute_query($sql);
	$result4 = execute_query($sql);
	$cat = mysql_fetch_row($result4);
	$filename = $cat[0]."-".date("Ymd").".csv";

        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="'.$filename.'"');
	print "Keyword, Attribution, Keyword Alias, Category, Optional Words, Content\r\n";

	while($row = mysql_fetch_assoc($result3)) {
                //Eliminate the delimiter, and return carriages from content that potentially malform our CSV
                $format = array("\n", "\r", ",");
                $row[content] = str_replace($format," ", $row[content]);
                print "$row[keyword],$row[attribution],$row[keywordAlias],$row[name],$row[optionalWords],$row[content]\r\n";
        }

	exit();
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
                    <td height="22" colspan="3" align="center" valign="middle" class="caption">Keyword Categories </td>
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
		  <td height="30" valign="top"><u>Name</u></td> 
		  <td valign="top"><u>Description</u></td>
		  <td valign="top"><u>CKW Search</u></td>
		  <td valign="top"><u>Updated</u></td>
		  <td valign="top"><u>Options</u></td>
		 <tr>';
		$color = '#E4E4E4'; $i=0;
		while($row = mysql_fetch_assoc($result)) {
		 $color = $i++%2 ? '#FFFFFF' : '#EEEEEE';	 	 
		 $select = array();			
		 $html .= '
		 <tr bgcolor="'.$color.'" 
		 onmouseover="this.style.backgroundColor=\''.HOVERCOLOR.'\'" onmouseout="this.style.backgroundColor=\''.$color.'\'"> 
		  <td height="25">&nbsp;'.truncate_str($row['name'], 30).'
		  </td>
		  <td>'.($row['description'] ? truncate_str($row['description'], 40) : 'N/A').'</td> 
		  <td style="padding-left: 20px; color: '.($row['ckwsearch'] ? '#008800' : '#CC0000').'">'.($row['ckwsearch'] ? 'YES' : 'NO').'</td>
		  <td>'.$row['updated'].'</td>
		  <td>
		  <a href="editcategory.php?categoryId='.$row["id"].'" title="Edit Category">Edit</a> | 
		  <a href="?categoryId='.$row['id'].'&delete=TRUE" style="color: #FF0000" 
		   title="Delete Category" onclick="return confirm(\'Are you sure you want to delete this Category?\')">Delete</a>
		  </td>
		 <tr>';
		}
		$html .='
		<form method="post">
		<tr>
		  <td colspan="2" height="45" valign="middle">
		    <input name="add" type="submit" class="button" value="Create Category">
		  </td>
		  <td  height="45" valign="middle">
		    <input name="csvfile" type="submit" class="button" value="Download Category >>">
</td><td colspan="2" height="45" valign="middle">
			<select NAME="category">';
		
		while($row = mysql_fetch_assoc($result2)) {
			$html .= '<option value="'.$row['id'].'">'.truncate_str($row['name'], 30).'</option>';
		}
		
		$html .= '</select>
		  </td>
		</tr>
		</form>';
		 print $html.'</table>'; 
		?>		
		</td>
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

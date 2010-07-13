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

if(!isset($_SESSION['search'])) {
   goto('search.php');
}
$sql = $_SESSION['search']['sql'];
$result = execute_query($sql);

$total = mysql_num_rows($result);

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
                    <td height="22" colspan="3" align="center" valign="middle" class="caption">Keyword Search Results - Total:                    			<?= $total ?> </td>
             </tr>
               <tr>
                    <td height="18" colspan="3" valign="top"><? require 'keywords.menu.php' ?></td>
               </tr>
			   <?
			   if($cat = $_SESSION['search']['cat']){
		
		$sql1 = 'SELECT name FROM category where id='.$cat;
		$result1 = mysql_query($sql) or die(mysql_error());
		$record1 = mysql_fetch_assoc($result1);
		$name1 = $record['name'];
		echo '<tr><td colspan="3">&nbsp&nbsp&nbsp
                <a href="xls.cat.php?categoryId='.$cat.'" target="_blank" title="Export to Excel File"> 
		<img src="images/excel.jpg" border="0"/>
		</a>
		<a href="xls.cat.php?categoryId='.$cat.'" target="_blank" style="color: #000"title="Export '.$name1.' to Excel File"> Export Hits in this Category
		</a></td></tr>
		<tr><td height = "20"></td></tr>';
		
		}?>
			   
               <tr>
                    <td width="14" height="300">&nbsp;</td>
                    <td width="757" valign="top">
		               <?
		
		$html = 
		'<table width="100%" border="0" cellpadding="0" cellspacing="0">
		 <tr class="title1"> 
		  <td height="30" valign="top"><u>Keyword</u></td> 
		  <td valign="top"><u>Created</u></td>
		  <td valign="top" width="50"><u>Hits</u></td>
		  <td valign="top"><u>Aliases</u></td>
		  <td valign="top"><u>Updated</u></td>
		  <td valign="top"><u>Options</u></td>
		 <tr>';
		$color = '#E4E4E4'; $i=0;
		while($row = mysql_fetch_assoc($result)) {
		 $color = $i++%2 ? '#FFFFFF' : '#EEEEEE';
		 /* replies */
                 $wording = str_replace("_"," ",$row[keyword]);
                 $sql = "SELECT * FROM hit WHERE keyword='$row[keyword]' OR keyword='$wording'";
		 $result2=execute_query($sql, 0);
		 $replies=mysql_num_rows($result2);
		 /* aliases */
		 if(strlen($row['aliases'])) {
		    $aliases = preg_split("/,/", trim($row['aliases'])); 
		    $aliases=count($aliases);
		 }	
		 else {
		    $aliases = 0;
		 }
	 	 
		 $html .= '
		 <tr bgcolor="'.$color.'" 
		 onmouseover="this.style.backgroundColor=\''.HOVERCOLOR.'\'" onmouseout="this.style.backgroundColor=\''.$color.'\'"> 
		  <td height="25">&nbsp;
		    <a href="keyword.php?keywordId='.$row['id'].'&return=matched.php" style="color: '.($row['otrigger'] ? '#FF33CC' : '#000000').'" 
			title="'.$row['keyword'].' - Click to open Keyword">'.truncate_str($row['keyword'], 30).'</a>
		  </td>
		  <td>'.$row['created'].'</td> 
		  <td style="padding-left: 5px" class="caption4">'.$replies.'</td>
		  <td style="padding-left: 20px" class="caption4">'.$aliases.'</td>
		  <td>'.$row['updated'].'</td>
		  <td>
		  <a href="hits.php?keywordId='.$row["id"].'" title="Keyword Hits">Hits</a> | 
		  <a href="editkeyword.php?keywordId='.$row["id"].'&return=matched.php" title="Edit Keyword">Edit</a>
		  </td>
		 <tr>';
		}
		if($cat = $_SESSION['search']['cat']){
		
		$sql = 'SELECT name FROM category where id='.$cat;
		$result = mysql_query($sql) or die(mysql_error());
		$record = mysql_fetch_assoc($result);
		$name = $record['name'];
		$export = '<td valign="middle">
                <a href="xls.cat.php?categoryId='.$cat.'" target="_blank" title="Export to Excel File"> 
		<img src="images/excel.jpg" border="0"/>
		</a>
		<a href="xls.cat.php?categoryId='.$cat.'" target="_blank" style="color: #000"title="Export '.$name.' to Excel File"> Export Hits in this Category
		</a>
		</td>
		</tr>';
		}
		print $html.$export.'</table>'; 
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

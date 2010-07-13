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
   goto('addkeyword.php');
}
if(!isset($start) || !is_numeric($start)) {
  $start = 0;
}
if(isset($delete)) {
   check_id($dictionaryId, 'dictionary.php');
   execute_update("DELETE FROM dictionary WHERE id=$dictionaryId");
   
   goto("dictionary.php?start=$start");	   
}
$sql = "SELECT dictionary.*, DATE_FORMAT(created, '%d/%m/%Y %r') AS createdate, 
        DATE_FORMAT(updated, '%d/%m/%Y %r') AS updated FROM dictionary";
$result = execute_query($sql);

$total = mysql_num_rows($result);

$limit = 40;
$this_pg = $start + $limit;
$next = $start + $limit;
$back = $start - $limit;

/* listing */
$sql .= " ORDER BY created DESC LIMIT $start, $limit";
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
                    <td height="22" colspan="3" align="center" valign="middle" class="caption">Dictionary - Total words:                    			<?= $total ?> </td>
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
		 <tr>
		       <td height="30" colspan="4" valign="top">
			   <a href="importwords.php" style="color: #FF3300; text-decoration: underline">Import Words From Excel File</a></td>
		 </tr>
		 <tr class="title1"> 
		  <td height="30" valign="top"><u>Word</u></td> 
		  <td valign="top"><u>Aliases</u></td>
		  <td valign="top"><u>Updated</u></td>
		  <td valign="top"><u>Options</u></td>
		 <tr>';
		$color = '#E4E4E4'; $i=0;
		while($row = mysql_fetch_assoc($result)) {
		 $color = $i++%2 ? '#FFFFFF' : '#EEEEEE';	 
		 $aliases = get_row_count('aliases', 'word_id', $row['id']);	 
		 $html .= '
		 <tr bgcolor="'.$color.'" 
		 onmouseover="this.style.backgroundColor=\''.HOVERCOLOR.'\'" onmouseout="this.style.backgroundColor=\''.$color.'\'"> 
		  <td height="25">&nbsp;'.truncate_str($row['word'], 50).'
		  </td>
		  <td style="padding-left: 20px" class="caption4">'.$aliases.'</td>
		  <td>'.$row['updated'].'</td>
		  <td>
		     <a href="aliases.php?dictionaryId='.$row["id"].'">Aliases</a> | 
			 <a href="editdict.php?dictionaryId='.$row['id'].'">Edit</a> | 
		     <a href="?start='.$start.'&dictionaryId='.$row['id'].'&delete=TRUE" style="color: #FF0000" 
		     onclick="return confirm(\'Are you sure?\')">Delete</a>
		  </td>
		 <tr>';
		}
	    if($total > $limit) {
	      $scroll = '
	      <tr>
	       <td height="25" colspan="4" valign="bottom">
		    <div style="text-align: justify;">';
	      if($back >= 0) 
          $scroll .= '<a href="?start='.$back.'" style="color: #000000">&laquo; Prev</a> ';
          for($i=0, $l=1; $i < $total; $i= $i + $limit){
           if($i != $start)
		    $scroll .= '<a href="?start='.$i.'">'.$l.'</a> ';
           else $scroll .= '<span style="color: #ff0000; font-weight: bold">'.$l.'</span> ';
           $l = $l+1;
	      }
	      if($this_pg < $total) 
	       $scroll .= ' <a href="?start='.$next.'" style="color: #000000">Next &raquo;</a>';
		  if($l>2) $html = "$html$scroll <div/></td></tr>";		   
	     }	
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

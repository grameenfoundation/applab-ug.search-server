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

if(count($_SESSION)) {
    session_unregister('search');
    session_unregister('exclude_results'); 
    session_unregister('excluded_bysearch_results');
    session_unregister('test_results');
    session_unregister('filtered_results');
    session_unregister('pics');    
}

dbconnect();
validate_session(); 
extract($_GET);

if(!isset($start) || !is_numeric($start)) {
  $start = 0;
}
if(isset($delete)) {
   if(!preg_match("/^[0-9]+$/", $surveyId)) {
       goto("quiz.php");
   }
   check_admin_user();
   delete_msurvey($surveyId);
   goto("msurveys.php?start=$start");	   
}
$sql = "SELECT msurvey.*, DATE_FORMAT(createdate, '%d/%m/%Y %r') AS created, DATE_FORMAT(updated, '%d/%m/%Y %r') AS updated FROM msurvey";
if(!admin_user()) {
    $sql .= " WHERE useraccess=1";
}
$result = execute_query($sql);

$total = mysql_num_rows($result);

$limit = 40;
$this_pg = $start + $limit;
$next = $start + $limit;
$back = $start - $limit;

/* listing */
$sql .= " ORDER BY createdate DESC LIMIT $start, $limit";
$result = execute_query($sql);

/* menu highlight */
$page = 'msurvey';
 
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
          <td height="372" valign="top"><table width="100%" border="0" cellpadding="0" cellspacing="0" class="border">
               <!--DWLayoutTable-->
               <tr>
                    <td height="22" colspan="3" align="center" valign="middle" class="caption">Mobile Survey Items  - Total: <?= $total ?> </td>
                    </tr>
               <tr>
                    <td width="13" height="27">&nbsp;</td>
                    <td width="760">&nbsp;</td>
                    <td width="15">&nbsp;</td>
               </tr>
               <tr>
                  <td height="300">&nbsp;</td>
                  <td valign="top">
		<? 	
		$html = 
		'<table width="100%" border="0" cellpadding="0" cellspacing="0">
		 <tr id="title"> 
		  <td height="30" valign="top" colspan="2"><u>Name</u></td> 
		  <td valign="top">Created</td>
		  <td valign="top">Updated</td>
		  <td valign="top">Options</td>
		 <tr>';
		$color = '#E4E4E4'; $i=0;
		while($row = mysql_fetch_assoc($result)) {
		 $color = $i++%2 ? '#FFFFFF' : '#EEEEEE';
		 /* questions */
		 $questions = unserialize($row['questions']);
		 $questions = count($questions);
         /* numbers */
		 $result2 = execute_query("SELECT * FROM surveyno WHERE surveyId=$row[id]", 0);
		 $numbers = mysql_num_rows($result2);
		  
		 $html .= '
		 <tr bgcolor="'.$color.'" 
		 onmouseover="this.style.backgroundColor=\''.HOVERCOLOR.'\'" onmouseout="this.style.backgroundColor=\''.$color.'\'"> 
		  <td width="30" style="padding-left: 2px">
		    <a href="msurvey.php?surveyId='.$row["id"].'" tittle="'.$row['name'].'"><img src="images/quiz1.gif" border="0" /></a>
		  </td>
		  <td height="25">
		    &nbsp;<a href="msurvey.php?surveyId='.$row["id"].'" style="color: '.($row['active'] ? '#008800' : '#000000').'"
			 title="Click to Go to survey">'.
			truncate_str($row['name'], 30).'</a>
		  </td> 
		  <td'.($row['active'] ? ' style="color: #008800"' : '').'>'.$row['created'].'</td>
		  <td'.($row['active'] ? ' style="color: #008800"' : '').'>'.$row['updated'].'</td>
		  <td>
		  <a href="mresults.php?surveyId='.$row["id"].'" '.($row['useraccess'] ? 
		  ' style="color: #FF33CC" title="Click to view results"' : 'title="Not Accessible by Limited Users"').'>Results</a>'.
		  (admin_user() ? ' | 
		  <a href="editmsurvey.php?surveyId='.$row["id"].'">Edit</a> | 
		  <a href="?start='.$start.'&surveyId='.$row['id'].'&delete=TRUE" style="color: #FF0000" 
		   title="Delete survey" onclick="return confirm(\'Are you sure you want to delete this survey?\')">Delete</a>' : '').'
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
		  if($l>2) $html = "$html$scroll </div></td></tr>";		   
	     }
		 if(admin_user()) {	
		    $html .='
		    <tr>
		        <td colspan="8" height="45" valign="middle">
		        <input type="button" class="button" value="Create Survey" 
			       onclick="location.replace(\'addmsurvey.php\')" />
		       </td>
		   </tr>';
		 }
		 print $html.'</table>'; 
		
		?>		
		</td>
                    <td>&nbsp;</td>
               </tr>
               <tr>
                  <td height="21">&nbsp;</td>
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

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

$keyword  = get_keyword_from_id($keywordId);

if(!isset($start) || !is_numeric($start)) {
  $start = 0;
}

if(isset($_POST['delete'])) {
   foreach($_POST as $key=>$val) {
      if(!preg_match("/^hit[0-9]+$/", $key) || !is_numeric($val)) {
	     continue;
	   }	 
	  $sql = "DELETE FROM hit WHERE id=$val LIMIT 1";
	  //execute_nonquery($sql);
   }
   goto("hits.php?keywordId=$keywordId&start=$start");
}   

if(isset($_GET['delete'])) {
   if(!preg_match("/^[0-9]+$/", $hitId)) {
      goto("hits.php?keywordId=$keywordId&start=$start");
   }	  
   $sql="DELETE FROM hit WHERE id=$hitId LIMIT 1";
   execute_nonquery($sql);
   goto("hits.php?keywordId=$keywordId&start=$start");
}
$keywording =  str_replace("_"," ",$keyword[keyword]);
$sql = "SELECT hit.*, DATE_FORMAT(time, '%d/%m/%Y %r') AS hitime FROM hit WHERE keyword='$keyword[keyword]' OR keyword='$keywording'";
$result = execute_query($sql);

$total = mysql_num_rows($result);

$limit = 40;
$this_pg = $start + $limit;
$next = $start + $limit;
$back = $start - $limit;

/* listing */
$sql .= " ORDER BY time DESC LIMIT $start, $limit";
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
<script type="text/javascript" src="basic.js"></script>
</head>

<body>
<table width="790" border="0" align="center" cellpadding="0" cellspacing="0" class="main">
     <!--DWLayoutTable-->
     <tr>
          <td width="790" height="124" valign="top"><? include('top.php') ?></td>
     </tr>
     <tr>
          <td height="421" valign="top"><table width="100%" border="0" cellpadding="0" cellspacing="0" class="border">
               <!--DWLayoutTable-->
               <tr>
                    <td height="22" colspan="3" align="center" valign="middle" class="caption">Hits On Keyword  - <?= $keyword['keyword'] ?> - Total:                       <?= $total ?> </td>
             </tr>
               <tr>
                    <td width="17" height="13"></td>
                    <td width="754"></td>
                    <td width="17"></td>
               </tr>
               <tr>
                  <td height="373"></td>
                  <td valign="top">
	    <? 	
		$html = 
		'<table width="100%" border="0" cellpadding="0" cellspacing="0">
		 <tr class="title1"> 
		  <td height="30" valign="top"><u>User/Phone No.</u></td> 
		  <td valign="top"><u>Request</u></td>
		  <td valign="top"><u>Reply</u></td>
		  <td valign="top"><u>Time</u></td>
		  <td valign="top"><u>Options</u></td>
		 <tr>
		 <form method="post">';
		 
		$color = '#E4E4E4'; $i=0;
		$list = NULL;
		while($row = mysql_fetch_assoc($result)) {
		 $color = $i++%2 ? '#FFFFFF' : '#E4E4E4';
		 $list .= "p$row[id],";
		 			   
		 $html .= '
		 <tr bgcolor="'.$color.'" 
		 onmouseover="this.style.backgroundColor=\''.HOVERCOLOR.'\'" onmouseout="this.style.backgroundColor=\''.$color.'\'"> 
		  <td height="25">
		     <input type="checkbox" name="hit'.$row['id'].'" id="p'.$row['id'].'" value="'.$row['id'].'" />&nbsp;'.get_phone_display_label($row['phone']).'
		  </td>		  
		  <td>'.truncate_str($row['request'], 20).'</td>
		  <td>'.truncate_str($row['reply'], 20).'</td>
		  <td>'.$row['hitime'].'</td>
		  <td>
		  <a href="?start='.$start.'&keywordId='.$keywordId.'&hitId='.$row['id'].'&delete=TRUE" style="color: #FF0000" 
		   title="Delete quiz" onclick="return confirm(\'Are you sure you want to delete this record?\')">Delete</a>
		  </td>
		 <tr>';
		}
	    if($total > $limit) {
	      $scroll = '
	      <tr>
	       <td height="25" colspan="4" valign="bottom">
		    <div style="text-align: justify;">';
	      if($back >= 0) 
          $scroll .= '<a href="?start='.$back.'&keywordId='.$keywordId.'" style="color: #000000">&laquo; Prev</a> ';
          for($i=0, $l=1; $i < $total; $i= $i + $limit){
           if($i != $start)
		    $scroll .= '<a href="?start='.$i.'&keywordId='.$keywordId.'">'.$l.'</a> ';
           else $scroll .= '<span style="color: #ff0000; font-weight: bold">'.$l.'</span> ';
           $l = $l+1;
	      }
	      if($this_pg < $total) 
	       $scroll .= ' <a href="?start='.$next.'&keywordId='.$keywordId.'" style="color: #000000">Next &raquo;</a>';
		  if($l>2) $html = "$html$scroll <div/></td></tr>";		   
	     }	
		echo $html.'</table>';
		$html ='
		<table border="0">
		<tr>
		  <td height="45">
		  <input type="button" class="button" value="Select All" onclick="selectall(true)" />	
             <input type="button" class="button" value="UnSelect All" onclick="selectall(false)" />	
			 <input type="button" class="button" value="&laquo; Go To Keyword" 
			 onclick="location.replace(\'keyword.php?keywordId='.$keywordId.'\')" />	
             <input type="submit" name="delete" class="button" '.(!$total ? 'disabled="disabled"' : '').'
			 value="Delete" onclick="return confirm(\'Are you sure?\')"/>	
		    <input type="hidden" value="'.$list.'" id="list"/>
		  </td>
		  <td width="10"></td>		  	
		  <td valign="middle">
		     <a href="xls.hits.php?keywordId='.$keywordId.'" target="_blank" title="Export Hits to Excel File">
			     <img src="images/excel.jpg" border="0"/>
			 </a>
			 </td>		  
		  <td>
		  <a href="xls.hits.php?keywordId='.$keywordId.'" target="_blank" style="color: #000" 
		  title="Export Hits to Excel File">Export To Excel</a>
		  </td>
		  <td>		  
		</tr>
		</form>';
		 print $html.'</table>'; 
		?>		
		</td>
                    <td></td>
               </tr>
               <tr>
                  <td height="17"></td>
                  <td>&nbsp;</td>
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

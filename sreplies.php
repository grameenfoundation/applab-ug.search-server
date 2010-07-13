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
extract($_GET);

check_id($surveyId, 'surveys.php');
$survey = get_survey_from_id($surveyId);

if(!isset($start) || !is_numeric($start)) {
  $start = 0;
}

if(isset($_POST['delete'])) {
   foreach($_POST as $key=>$val) {
      if(!preg_match("/^rep[0-9]+$/", $key) || !is_numeric($val)) {
	     continue;
	   }	
	  $sql = "DELETE FROM sresult WHERE id=$val LIMIT 1"; 
	  execute_nonquery($sql);
   }
   goto("sreplies.php?surveyId=$surveyId&start=$start");
}   

if(isset($_GET['delete'])) {
   $sql = "DELETE FROM sresult WHERE id=$resultId LIMIT 1"; 
   execute_nonquery($sql);
   goto("sreplies.php?surveyId=$surveyId&start=$start");
}

$sql = "SELECT sresult.*, DATE_FORMAT(date, '%d/%m/%Y %r') AS time FROM sresult WHERE surveyId=$surveyId";
$result = execute_query($sql);

$total = mysql_num_rows($result);

$limit = 50;
$this_pg = $start + $limit;
$next = $start + $limit;
$back = $start - $limit;

/* listing */
$sql .= " ORDER BY date DESC LIMIT $start, $limit";
$result = execute_query($sql);

/* menu highlight */
$page = 'survey';
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
                    <td height="22" colspan="4" align="center" valign="middle" class="caption">Replies To Survey - Total:                       <?= $total ?> </td>
             </tr>
               <tr>
                    <td width="17" height="13"></td>
                    <td width="34"></td>
                  <td width="720"></td>
                  <td width="17"></td>
               </tr>
               <tr>
                  <td height="28"></td>
                  <td valign="middle"><img src="images/quiz.gif" width="34" height="28" style="cursor: pointer" title="<?= $survey['name'] ?>"/></td>
                  <td valign="middle">&nbsp; <span class="caption2">Survey:</span>
                     <a href="survey.php?surveyId=<?= $surveyId ?>" style="color: #666666"><?= $survey['name'] ?>
- Created
<?= $survey['createdate'] ?></a>
</a>&nbsp;&nbsp;[<a href="survey.php?surveyId=<?= $surveyId ?>">Go To Survey</a>]</td>
                  <td></td>
               </tr>
               <tr>
                  <td height="17"></td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td></td>
               </tr>
               <tr>
                  <td height="328"></td>
                  <td colspan="2" valign="top">
	                 <? 	
		$html = 
		'<table width="100%" border="0" cellpadding="0" cellspacing="0">
		 <tr class="title1"> 
		  <td height="30" valign="top"><u>Phone Number</u></td> 
		  <td valign="top"><u>Reply</u></td>
		  <td valign="top"><u>Date/Time</u></td>
		  <td valign="top"><u>Options</u></td>
		 <tr><form method="post">';
		$color = '#E4E4E4'; $i=0;
		$list = NULL;
		while($row = mysql_fetch_assoc($result)) {
		 $color = $i++%2 ? '#FFFFFF' : '#E4E4E4';
		 $list .= "p$row[id],";
		 $html .= '
		 <tr bgcolor="'.$color.'" 
		 onmouseover="this.style.backgroundColor=\''.HOVERCOLOR.'\'" onmouseout="this.style.backgroundColor=\''.$color.'\'"> 
		  <td height="25">
		     <input type="checkbox" name="rep'.$row['id'].'" id="p'.$row['id'].'" value="'.$row['id'].'" />&nbsp;'.get_phone_display_label($row['phone']).'
		  </td>		  
		  <td style="color: #FF3300; font-weight: bold; cursor: pointer" title="'.$row['request'].'">'.truncate_str($row['request'], 40).'</td>
		  <td>'.$row['time'].'</td>
		  <td>
		  <a href="?start='.$start.'&surveyId='.$surveyId.'&replyId='.$row['id'].'&delete=TRUE" style="color: #FF0000" 
		   title="Delete Reply" onclick="return confirm(\'Are you sure you want to delete this reply?\')">Delete</a>
		  </td>
		 <tr>';
		}
	    if($total > $limit) {
	      $scroll = '
	      <tr>
	       <td height="25" colspan="7" valign="bottom">
		    <div style="text-align: justify;">';
	      if($back >= 0) 
          $scroll .= '<a href="?start='.$back.'&surveyId='.$surveyId.'" style="color: #000000">&laquo; Prev</a> ';
          for($i=0, $l=1; $i < $total; $i= $i + $limit){
           if($i != $start)
		    $scroll .= '<a href="?start='.$i.'&surveyId='.$surveyId.'">'.$l.'</a> ';
           else $scroll .= '<span style="color: #ff0000; font-weight: bold">'.$l.'</span> ';
           $l = $l+1;
	      }
	      if($this_pg < $total) 
	       $scroll .= ' <a href="?start='.$next.'&surveyId='.$surveyId.'" style="color: #000000">Next &raquo;</a>';
		  if($l>2) $html = "$html$scroll <div/></td></tr>";		   
	     }	
		echo $html.'</table>';
		$html ='
		<table border="0">
		<tr>
		  <td height="45">
		  <input type="button" class="button" value="Select All" onclick="selectall(true)" />	
             <input type="button" class="button" value="UnSelect All" onclick="selectall(false)" />	
             <input type="submit" name="delete" class="button" value="Delete" onclick="return confirm(\'Are you sure?\')"/>	
		    <input type="hidden" value="'.$list.'" id="list"/>
		  </td>
		  <td width="10"></td>		  	
		  <td valign="middle">
		     <a href="xls.sreplies.php?surveyId='.$surveyId.'" target="_blank" title="Export Replies to Excel File">
			     <img src="images/excel.jpg" border="0"/>
			 </a>
			 </td>		  
		  <td><a href="xls.sreplies.php?surveyId='.$surveyId.'" target="_blank" style="color: #000" title="Export Replies to Excel File">Export To Excel</a></td>
		  <td>		  
		</tr>
		</form>';
		 print $html.'</table>'; 
		?>		</td>
                    <td></td>
               </tr>
               <tr>
                  <td height="17"></td>
                  <td>&nbsp;</td>
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

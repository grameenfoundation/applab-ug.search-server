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

if(!preg_match("/^[0-9]+$/", $quizId)) { 
   goto('quiz.php');
}
$quiz = get_quiz_from_id($quizId);
if(empty($quiz)) {
   goto('quiz.php');
}

if(!isset($start) || !is_numeric($start)) {
  $start = 0;
}

if(isset($_POST['delete'])) {
   foreach($_POST as $key=>$val) {
      if(!preg_match("/^p[0-9]+$/", $key) || !is_numeric($val)) {
	     continue;
	   }	 
	  $sql = "DELETE FROM quizphoneno WHERE id=$val LIMIT 1";
	  execute_nonquery($sql);
   }
   goto("numbers.php?quizId=$quizId&start=$start");
}   

if(isset($_GET['delete'])) {
   if(!preg_match("/^[0-9]+$/", $numberId)) {
       goto("numbers.php?quizId=$quizId&start=$start");
   }
   $sql = "DELETE FROM quizphoneno WHERE id=$numberId LIMIT 1";
   execute_nonquery($sql);
   goto("numbers.php?quizId=$quizId&start=$start");
}

/* get questions */

$sql = "SELECT * FROM question WHERE quizId=$quizId";
$result =execute_query($sql);
$questions = array();
while($row=mysql_fetch_assoc($result)) {
   $questions[] = $row;
}
if(!count($questions)) {
   goto("questions.php?quizId=$quizId");
}
if(!isset($questionId)) {
   $questionId = $questions[0]['id'];
}

$sql = "SELECT quizphoneno.*, DATE_FORMAT(createDate, '%d/%m/%Y %T') AS created, 
        DATE_FORMAT(updated, '%d/%m/%Y %T') AS updated FROM quizphoneno WHERE quizId=$quizId";
$result = execute_query($sql);

$total = mysql_num_rows($result);

$limit = 40;
$this_pg = $start + $limit;
$next = $start + $limit;
$back = $start - $limit;

/* listing */
$sql .= " ORDER BY createDate DESC LIMIT $start, $limit";
$result = execute_query($sql);

/* menu highlight */
$page = 'quiz';

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
                    <td height="22" colspan="6" align="center" valign="middle" class="caption">Quiz Phone Numbers - Total: <?= $total ?> </td>
                    </tr>
               <tr>
                    <td width="17" height="13"></td>
                    <td width="22"></td>
                    <td width="12"></td>
                  <td width="259"></td>
                  <td width="461"></td>
                  <td width="17"></td>
               </tr>
               <tr>
                  <td height="28"></td>
                  <td colspan="2" valign="middle">
				  <img src="images/quiz.gif" width="34" height="28" style="cursor: pointer" title="<?= $quiz['name'] ?>"/></td>
                  <td colspan="2" valign="middle">&nbsp; <span class="caption2">quiz:</span>
                     <?= $quiz['name'] ?>
- <span style="color: #666666">created
<?= $quiz['createDate'] ?>
</span></td>
                  <td></td>
               </tr>
               <tr>
                  <td height="60"></td>
                  <td valign="middle"><img src="images/qn2.gif" width="22" height="22" /></td>
                  <td colspan="2" valign="middle">&nbsp; Choose Question to View Delivery Status: </td>
                  <td valign="middle">
				  <select name="select" onchange="location.replace('numbers.php?quizId=<?= $quizId?>&questionId='+this.options[this.selectedIndex].value+'&start=<?= $start ?>')" <?= !$questionId ? 'style="width: 100px" disabled="disabled"' : '' ?>>
                        <?
						 foreach($questions as $question) {
						   print '<option value="'.$question['id'].'" '.
						   ($questionId==$question['id'] ? 'selected="selected"' : '').'>'.$question['question'].'</option>';
						}
						?>
					 </select>
					 </td>
                  <td></td>
               </tr>
               <tr>
                  <td height="307"></td>
                  <td colspan="4" valign="top">
		<? 	
		$html = 
		'<table width="100%" border="0" cellpadding="0" cellspacing="0">
		 <tr class="title1"> 
		  <td height="30" valign="top"><u>Phone Number</u></td> 
		  <td valign="top" colspan="2"><u>Delivery Status</u></td>
		  <td valign="top"><u>Created</u></td>
		  <td valign="top"><u>Updated</u></td>
		  <td valign="top"><u>Options</u></td>
		 <tr><form method="post">';
		
		$color = '#EEEEEE'; $i=0;
		$list = NULL;
		while($row = mysql_fetch_assoc($result)) {
		 $color = $i++%2 ? '#FFFFFF' : '#EEEEEE';
		 $list .= "p$row[id],";
		 
		 $sql = "SELECT * FROM qndelivery WHERE questionId=$questionId AND phoneId=$row[id]";
		 $result2=execute_query($sql, 0); 
		 $rowr2 = mysql_fetch_assoc($result2);
		 $status = $rowr2['status'];
		 
		 if(!strlen($rowr2['status'])) {
		     $status = 'PENDING';
			 $img = 'pending.gif';
		 }
		 elseif(preg_match("/Accepted\sfor\sdelivery/i", $rowr2['status'])) {
		     $status = 'DELIVERED';
			 $img = 'delivered.gif';
		 }
		 else {
		     $status = $rowr2['status'];
			 $img = 'rejected.gif';
		 }
		 
		 $html .= '
		 <tr bgcolor="'.$color.'" 
		 onmouseover="this.style.backgroundColor=\''.HOVERCOLOR.'\'" onmouseout="this.style.backgroundColor=\''.$color.'\'"> 
		  <td height="25">
		       <input type="checkbox" name="p'.$row['id'].'" id="p'.$row['id'].'" value="'.$row['id'].'" />
			   <a style="color: #000" href="editnumber.php?numberId='.$row["id"].'&quizId='.$quizId.'" 
			   tittle="'.$row['phone'].'">'.get_phone_display_label($row['phone']).'</a>
		  </td>		  
		  <td align="right">
		  <img src="images/'.$img.'" style="cursor: pointer" /></td>
		  <td>&nbsp;&nbsp;'.$status.'</td>
		  <td>'.$row['created'].'</td>
		  <td>'.$row['updated'].'</td>
		  <td>
		  <a href="editnumber.php?numberId='.$row["id"].'&quizId='.$quizId.'">Edit</a> | 
		  <a href="?questionId='.$questionId.'&start='.$start.'&quizId='.$quizId.'&numberId='.$row['id'].'&delete=TRUE" style="color: #FF0000" 
		   title="Delete Quiz" onclick="return confirm(\'Are you sure you want to delete this phone number?\')">Delete</a>
		  </td>
		 <tr>';
		}
	    if($total > $limit) {
	      $scroll = '
	      <tr>
	       <td height="25" colspan="6" valign="bottom">
		    <div style="text-align: justify;">';
	      if($back >= 0) 
          $scroll .= '<a href="?questionId='.$questionId.'&start='.$back.'&quizId='.$quizId.'" style="color: #000000">&laquo; Prev</a> ';
          for($i=0, $l=1; $i < $total; $i= $i + $limit){
           if($i != $start)
		    $scroll .= '<a href="?questionId='.$questionId.'&start='.$i.'&quizId='.$quizId.'">'.$l.'</a> ';
           else $scroll .= '<span style="color: #ff0000; font-weight: bold">'.$l.'</span> ';
           $l = $l+1;
	      }
	      if($this_pg < $total) 
	       $scroll .= ' <a href="?questionId='.$questionId.'&start='.$next.'&quizId='.$quizId.'" style="color: #000000">Next &raquo;</a>';
		  if($l>2) $html = "$html$scroll <div/></td></tr>";		   
	     }	
		$options = '<option value="ALL">ALL</option>';
		foreach($msgstatus as $status) {
		   $options .= '<option value="'.$status['status'].'">'.$status['name'].'</option>';
		}
		$html .='
		<tr>
		  <td colspan="6" height="45" valign="middle">
		   <table border="0" width="100%">
		   <tr>
		    <td>
             <input type="button" class="button" value="Select All" onclick="selectall(true)" />	
             <input type="button" class="button" value="UnSelect All" onclick="selectall(false)" />	
             <input type="submit" name="delete" class="button" value="Delete" onclick="return confirm(\'Are you sure?\')"/>  	    
			<input type="button" class="button" value="Add Numbers" 
			  onclick="location.replace(\'addnumber.php?quizId='.$quizId.'\')" />
			 <input type="button" class="button" value="&laquo; Quiz Questions" 
			 onclick="location.replace(\'questions.php?quizId='.$quizId.'\')"/>
		   <input type="hidden" id="list" value="'.preg_replace("/,$/", "", $list).'" />
		  </td>
		  <td width="30" align="right">
		     <a href="xls.qdelivery.php?questionId='.$questionId.'" title="Export To Excel"><img src="images/excel.jpg" border="0" /></a>
		  </td>
		  <td style="cursor: pointer" title="Export To Excel" onclick="window.open(\'xls.qdelivery.php?questionId='.$questionId.'\')" >(Export To Excel) 
		  </td>
		 </tr>
		  </table>
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

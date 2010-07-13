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

if(isset($_POST['cancel'])) {
    redirect('workinglist.php');
}
if(isset($_POST['create'])) {
    $args = isset($add_from_wl) ? '?add_from_wl=TRUE' : NULL;
	redirect('createquiz.php'.$args);
}

if(!isset($start) || !is_numeric($start)) {
  $start = 0;
}

if(isset($add_from_wl)) 
{
    $userId = get_user_id();
	$result = execute_query("SELECT * FROM workinglist WHERE owner='$userId'");
    $_total = mysql_num_rows($result);
	if(isset($submit_wl)) {
	     while($row=mysql_fetch_assoc($result)) {
		      $sql = "INSERT INTO quizphoneno(quizId, createDate, phone) 
			          VALUES ('$quizId', NOW(), '$row[phone]') ON DUPLICATE KEY UPDATE updated=NOW()";
		      execute_update($sql);
		 }
		 redirect("?start=$start");
	}
	$addconfirm = 'onclick="return confirm(\'Add '.$_total.' user(s) to this Quiz?\');"';
    $next_app = '&add_from_wl=add';
}
else {
    $next_app = NULL;
}

if(isset($delete)) 
{
   if(!preg_match("/^[0-9]+$/", $quizId)) {
       redirect("quiz.php");
   }
   delete_quiz($quizId);
   redirect("quiz.php?start=$start");	   
}
$sql = "SELECT quiz.*, DATE_FORMAT(createDate, '%d/%m/%Y %r') AS created FROM quiz";
$result = execute_query($sql);

$total = mysql_num_rows($result);
$limit = 20;
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
</head>

<body>
<table width="790" border="0" align="center" cellpadding="0" cellspacing="0" class="main">
     <!--DWLayoutTable-->
     <tr>
          <td width="790" height="124" valign="top"><? include('top.php') ?></td>
     </tr>
     <tr>
          <td height="373" valign="top"><table width="100%" border="0" cellpadding="0" cellspacing="0" class="border">
               <!--DWLayoutTable-->
               <tr>
                    <td height="22" colspan="3" align="center" valign="middle" class="caption">Quiz Items - Total: <?= $total ?> </td>
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
		 <tr class="title1"> 
		  <td height="30" valign="top" colspan="2"><u>Quiz Item</u></td> 
		  <td valign="top" style="padding-right: 5px"><u>Qns</u></td>
		  <td valign="top"><u>Phone Nos.</u></td>
		  <td valign="top"><u>Replies</u></td>
		  <td valign="top"><u>Qn Sending</u></td>
		  <!--td valign="top"><u>Created</u></td-->
		  <td valign="top"><u>Options</u></td>
		 <tr>';
		
		$color = '#E4E4E4'; $i=0;
		while($row = mysql_fetch_assoc($result)) {
		 $color = $i++%2 ? '#FFFFFF' : '#EEEEEE';
		 $name = $row['name'];
		 if(strlen($name) > 18) {
		    $name = substr($name, 0, 18).'..';
		 }
		 /* questions */
		 $result2 = execute_query("SELECT * FROM question WHERE quizId=$row[id]", 0);
		 $questions = mysql_num_rows($result2);
         /* numbers */
		 $result2 = execute_query("SELECT COUNT(id) FROM quizphoneno WHERE quizId=$row[id]", 0);
		 $myrow = mysql_fetch_row($result2);
		 $numbers = $myrow[0];
		 /* replies */
		 $sql = "SELECT COUNT(id) FROM quizreply WHERE questionId IN(SELECT id FROM question WHERE quizId=$row[id])";
		 $result2=execute_query($sql, 0);
		 $myrow = mysql_fetch_row($result2);
		 $replies = $myrow[0];
		 
		 $addlink = isset($add_from_wl) ? '<a href="?start='.$start.'&quizId='.$row['id'].
		 '&add_from_wl=add&submit_wl=true" '.$addconfirm.'>[Add Users]</a> | ' : NULL;
		 
		 $html .= '
		 <tr bgcolor="'.$color.'" 
		 onmouseover="this.style.backgroundColor=\''.HOVERCOLOR.'\'" onmouseout="this.style.backgroundColor=\''.$color.'\'"> 
		  <td width="30" style="padding-left: 2px">
		    <a href="questions.php?quizId='.$row["id"].'" tittle="'.$row['name'].'"><img src="images/quiz1.gif" border="0" /></a>
		  </td>
		  <td height="25">
		    &nbsp;<a href="questions.php?quizId='.$row["id"].'" style="color: '.($row['singleKeyword'] ? '#FF33CC' : '#000000').'" title="Click to Go to Quiz">'.$name.'</a>
		  </td> 
		  <td class="caption4">'.$questions.'</td>
		  <td style="padding-left: 20px" class="caption4">'.$numbers.'</td>
		  <td style="padding-left: 20px" class="caption4">'.$replies.'</td>
		  <td style="color: '.($row['sendall'] ? '#008800"' : '#000000').'">'.($row['sendall'] ? 'MULTIPLE' : 'ONE').'</td>
		  <!--td>'.$row['created'].'</td-->
		  <td>
		  '.$addlink.'
		  <a href="qreplies.php?quizId='.$row["id"].'">Results</a> | 
		  <a href="editquiz.php?quizId='.$row["id"].'">Edit</a> | 
		  <a href="?start='.$start.'&quizId='.$row['id'].'&delete=TRUE'.$next_app.'" style="color: #FF0000" 
		   title="Delete Quiz" onclick="return confirm(\'Are you sure you want to delete this Quiz?\')">Delete</a>
		  </td>
		 <tr>';
		}
	    if($total > $limit) {
	      $scroll = '
	      <tr>
	       <td height="25" colspan="7" valign="bottom">
		    <div style="text-align: justify;">';
	      if($back >= 0) 
          $scroll .= '<a href="?start='.$back.$next_app.'" style="color: #000000">&laquo; Prev</a> ';
          for($i=0, $l=1; $i < $total; $i= $i + $limit){
           if($i != $start)
		    $scroll .= '<a href="?start='.$i.$next_app.'">'.$l.'</a> ';
           else $scroll .= '<span style="color: #ff0000; font-weight: bold">'.$l.'</span> ';
           $l = $l+1;
	      }
	      if($this_pg < $total) 
	       $scroll .= ' <a href="?start='.$next.$next_app.'" style="color: #000000">Next &raquo;</a>';
		  if($l>2) $html = "$html$scroll <div/></td></tr>";		   
	     }	
		$html .='
		<tr>
		  <td colspan="8" height="45" valign="middle">
		  <form method=post>
		    <input type="submit" name="create" class="button" value="Create Quiz" />
			'.(isset($add_from_wl) ? '<input type="submit" name="cancel" class="button" value="Back To Working List" />' : '').'
		  </form>
		  </td>
		</tr>';
		
		print $html.'</table>'; 
		
		?>		
		</td>
                    <td>&nbsp;</td>
               </tr>
               <tr>
                  <td height="22">&nbsp;</td>
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

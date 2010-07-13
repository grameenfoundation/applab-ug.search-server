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

if(isset($_POST["submit"])) {
   if(!preg_match("/^[0-9]+$/", $type)) {
     $errors = 'Please select the report type<br/>';
   }
   if(!strlen($date1)) {
       $errors .= 'Select the start date<br/>';
   }
   if(!strlen($date2)) {
       $errors .= 'Select the end date<br/>';
   }   
   if(!isset($errors)) 
   {
      $from = sqldate($date1);
	  $to   = sqldate($date2); 
	  if($type == 1) 
	  {
	     /* quiz report */
		 $sql = "SELECT quiz.*, DATE_FORMAT(createDate, '%d/%m/%Y %r') AS date FROM quiz WHERE id IN (SELECT quizId FROM question WHERE id IN(SELECT questionId FROM quizreply WHERE time BETWEEN '$from 00:00:00' AND '$to 23:59:59') ) ORDER BY createDate DESC";
		 $result = execute_query($sql);
		 if(!mysql_num_rows($result)) 
		 {
		     $msg = 'No Hits found for the selected period<br/>';
		 }
		 else {
		     /* make report */
			 $html = '
			 <table border="0" cellpadding="3" cellspacing="0">
			 <tr>
			    <td colspan="4" id="rptitle" height="40" align="center">Quiz Hits Report for the Period '.$date1.' to '.$date2.'</td>
			 </tr>
			 <tr id="rtitle">
			    <td height="30">Quiz</td>
				<td>Create Date</td>
				<td>Questions & Hits</td>
				<td>Total Hits</td>
			 </tr>';
			 while($row=mysql_fetch_assoc($result)) 
			 {
			     $total_hits = 0;
			     $sql = "SELECT question.*, DATE_FORMAT(sendTime, '%d/%m/%Y %r') AS sendTime FROM question WHERE quizId=$row[id]";
				 $result2 = execute_query($sql);
				 $qhtml = '<table border="0" cellpadding="3" cellspacing="0">';
				 $i=1;
				 while($row2=mysql_fetch_assoc($result2)) 
				 {
				    /* get hits */
					$sql = "SELECT * FROM quizreply WHERE questionId=$row2[id]";
					$hits = mysql_num_rows(execute_query($sql));
					$qhtml .= '
				    <tr>
				       <td>'.($i++).'.&nbsp;&nbsp;</td>
					   <td style="width: 300px">'.$row2['question'].'</td>
					   <td style="width: 50px">'.$hits.'</td>
				    </tr>';	
					$total_hits += $hits;		 
				 }
				 $qhtml .= '</table>';
			     $html .= '
				 <tr>
				    <td height="40" width="300" id="fieldcell">'.$row['name'].'</td>
					<td id="fieldcell" width="170">'.$row['date'].'</td>
					<td id="fieldcell">'.$qhtml.'</td>
					<td id="fieldcell" class="edgetd" align="center">'.$total_hits.'</td>
				 </tr>'; 
			 }
			 $html .= '</table>';
			 $_SESSION['report'] = $html;
			 $showreport=1;
		 }
	  }
	  elseif($type == 2) {
	     /* coded surveys - */
		 $sql = "SELECT survey.*, DATE_FORMAT(createdate, '%d/%m/%Y %r') AS date FROM survey WHERE id 
		         IN (SELECT surveyId FROM sresult WHERE date BETWEEN '$from 00:00:00' AND '$to 23:59:59') ORDER BY createdate DESC";
		 $result = execute_query($sql);
		 
		 if(!mysql_num_rows($result)) 
		 {
		     $msg = 'No Hits found for the selected period<br/>';
		 }
		 else 
		 {
		     /* make report */
			 $html = '
			 <table border="0" cellpadding="3" cellspacing="0">
			 <tr>
			    <td colspan="4" id="rptitle" height="40" align="center">Coded Survey Hits Report for the Period '.$date1.' to '.$date2.'</td>
			 </tr>
			 <tr id="rtitle">
			    <td height="30">Survey</td>
				<td>Create Date</td>
				<td>Questions</td>
				<td>Total Hits</td>
			 </tr>';	
			 while($row=mysql_fetch_assoc($result)) 
			 {
			     $sql = "SELECT * FROM sresult WHERE surveyId=$row[id]";
				 $total_hits = mysql_num_rows(execute_query($sql));
				 
				 $questions = unserialize($row['questions']);
				 $qhtml = '<table border="0" cellpadding="3" cellspacing="0">';
				 $i=1;
				 foreach($questions as $question) {
				    $qhtml .= '
					<tr>
					   <td height="28">'.($i++).'.&nbsp;</td>
					   <td width="300">'.$question['question'].'</td>
					</tr>';	 
				 }
				 $qhtml .= '</table>';	
				 $html .= '
				 <tr>
				    <td height="40" width="300" id="fieldcell">'.$row['name'].' <span id="field">('.$row['keyword'].')</span></td>
					<td  id="fieldcell" width="170">'.$row['date'].'</td>
					<td id="fieldcell">'.$qhtml.'</td>
					<td  id="fieldcell" class="edgetd" align="center">'.$total_hits.'</td>
				 </tr>';
			 }
			 $html .= '</table>';	
			 $_SESSION['report'] = $html;
			 $showreport=1;			  			 		 
		 }	
	  }
	  elseif($type==3) 
	  {
	    //
		    // general keywords 
			$sql = "SELECT keyword, COUNT(keyword) AS hits FROM hit WHERE LENGTH(TRIM(keyword)) AND time BETWEEN '$from 00:00:00' AND '$to 23:59:59' GROUP BY keyword";
		    $result = execute_query($sql);
		    if(!mysql_num_rows($result)) 
		    {
		        $msg = 'No Hits found for the selected period<br/>';
		    }	
			else 
			{
			    /* make report*/ 
			    $html = '
			    <table border="0" cellpadding="3" cellspacing="0">
			    <tr>
			       <td colspan="4" id="rptitle" height="40" align="center">
				      General Keyword Hits Report for the Period '.$date1.' to '.$date2.'</td>
			    </tr>
			    <tr id="rtitle">
			       <td height="30">Keyword</td>
				   <td>Total Hits</td>
				   <td align="center">First Hit</td>
				   <td align="center">Last Hit</td>
			    </tr>';					
				while($row=mysql_fetch_assoc($result)) 
				{
				   /* first hit */
				   $sql = "SELECT DATE_FORMAT(MIN(time), '%d/%m/%Y %r') FROM hit WHERE keyword='$row[keyword]'";
				   $result2=execute_query($sql);
				   $row2=mysql_fetch_row($result2);
				   $first = $row2[0];
				   /* last hit */
				   $sql = "SELECT DATE_FORMAT(MAX(time), '%d/%m/%Y %r') FROM hit WHERE keyword='$row[keyword]'";
				   $result2=execute_query($sql);
				   $row2=mysql_fetch_row($result2);
				   $last = $row2[0];
				   				   
				   $html .= '
				    <tr>
				       <td height="28" width="250" id="fieldcell"><span id="field">'.$row['keyword'].'</span></td>
					   <td id="fieldcell" align="center">'.$row['hits'].'</td>
					   <td id="fieldcell" width="170"  align="center">'.$first.'</td>
					   <td id="fieldcell" width="170" class="edgetd" align="center" 
					   '.($first==$last ? 'style="color: #666666"' : '').'>'.$last.'</td>
				    </tr>';
				}
				$html .= '</table>';
				$_SESSION['report'] = $html;
				$showreport=1;	
			}			
	  }
	  elseif($type == 4) {
	     /* coded surveys - */
		 $sql = "SELECT msurvey.*, DATE_FORMAT(createdate, '%d/%m/%Y %r') AS date FROM msurvey WHERE id 
		         IN (SELECT surveyId FROM mresult WHERE date BETWEEN '$from 00:00:00' AND '$to 23:59:59') ORDER BY createdate DESC";
		 $result = execute_query($sql);
		 
		 if(!mysql_num_rows($result)) 
		 {
		     $msg = 'No Hits found for the selected period<br/>';
		 }
		 else 
		 {
		     /* make report */
			 $html = '
			 <table border="0" cellpadding="3" cellspacing="0">
			 <tr>
			    <td colspan="4" id="rptitle" height="40" align="center">Mobile Survey Hits Report for the Period '.$date1.' to '.$date2.'</td>
			 </tr>
			 <tr id="rtitle">
			    <td height="30">Survey</td>
				<td>Create Date</td>
				<td>Form Fields</td>
				<td>Total Hits</td>
			 </tr>';	
			 while($row=mysql_fetch_assoc($result)) 
			 {
			     $sql = "SELECT * FROM mresult WHERE surveyId=$row[id]";
				 $total_hits = mysql_num_rows(execute_query($sql));
				 
				 $fields = unserialize($row['fif']);
				 $qhtml = '<table border="0" cellpadding="3" cellspacing="0">';
				 $i=1;
				 foreach($fields as $field) {
				    $qhtml .= '
					<tr>
					   <td height="28">'.($i++).'.&nbsp;</td>
					   <td>'.$field['name'].'</td>
					</tr>';	 
				 }
				 $qhtml .= '</table>';	
				 $html .= '
				 <tr>
				    <td height="40" width="300" id="fieldcell">'.$row['name'].'</td>
					<td  id="fieldcell" width="170">'.$row['date'].'</td>
					<td id="fieldcell">'.$qhtml.'</td>
					<td  id="fieldcell" class="edgetd" align="center">'.$total_hits.'</td>
				 </tr>';
			 }
			 $html .= '</table>';	
			 $_SESSION['report'] = $html;
			 $showreport=1;			  			 		 
		 }	
	  }	  
  }
}

if(!count($_POST)) {
    $date1 = '01/'.date('m').'/'.date('Y');
	$date2 = '31/'.date('m').'/'.date('Y');
	$type = 1;
}

?>
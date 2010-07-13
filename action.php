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
include("excel/functions.php");

dbconnect();
validate_session(); 

extract($_GET);

switch($action) 
{
   case 'import':
   
   if($category == 'quiz' || $category == 'survey') {
      $sql = ($category == 'quiz') ? "SELECT quiz.*, DATE_FORMAT(createDate, '%d/%m/%Y %T') AS date FROM quiz ORDER BY createDate DESC" : 
	  "SELECT survey.*, DATE_FORMAT(createdate, '%d/%m/%Y %T') AS date FROM survey ORDER BY createdate DESC";
      if(!($result = mysql_query($sql))) {
         print '{error: true}';
	     exit;
      }	
      if(!mysql_num_rows($result)) {
	      print '{error: false, result: false}';
		  exit;
      }
	  $html = '<table border="0" width="100%" cellspacing="0" cellpadding="0">
	  <tr class="title1">
	     <td height="30" align="top" colspan="2"><u>'.($category=='quiz' ? 'quiz' : 'Survey').'</u></td>
		 <td align="top"><u>Create Date</u></td>
		 <td align="top"><u>Phone Nos.</u></td>
	  </tr>';
	  $color = '#E4E4E4'; $i=0;
	  while($row = mysql_fetch_assoc($result)) {
	      $color = $i++%2 ? '#FFFFFF' : '#E4E4E4';
		  
		  $sql = ($category == 'quiz') ? "SELECT COUNT(*) FROM quizphoneno WHERE quizId=$row[id]" :
		  "SELECT COUNT(*) FROM surveyno WHERE surveyId=$row[id]";
		  if(!$result2=mysql_query($sql)) {
		     print '{error: true}';
			 exit;
		  }
		  $row2 = mysql_fetch_row($result2);
	      $total = $row2[0];
		  
		  $html .= '<tr bgcolor="'.$color.'" 
		  onmouseover="this.style.backgroundColor=\''.HOVERCOLOR.'\'" onmouseout="this.style.backgroundColor=\''.$color.'\'">
		  <td height="25">
		      <input type="checkbox" value"'.$row['id'].'" '.(!$total ? 'disabled="disabled"' : '').' 
			  onclick="addtoqnos(this, '.$row['id'].')"/>
		  </td>
		  <td height="25">'.truncate_str($row['name'], 30).'</td>
		  <td>'.$row['date'].'</td>
		  <td style="padding-left: 15px">'.$total.'</td>
		  </tr>';
	  }
	  $html .= '</table>';
	  $html = '<div style="width: 500px; height: 260px; overflow-x: hidden; overflow-y: auto">'.$html.'</div><br/>
	   <input type="button" class="button" value="Add Phone Numbers" onclick="setlabel(\'quiz\')" 
		   style="cursor: pointer" title="Add Selected Contacts"/>
		   <input type="button" class="button" value="Cancel" style="width: 50px" 
		   onclick="document.getElementById(\'qimportlist\').value = \'\';clearmsg();" 
		   style="cursor: pointer" title="Click to cancel"/>';
	  print $html;
	  exit;
   }
   elseif($category=='general') 
   {
      $sql = "SELECT user.*, DATE_FORMAT(createdate, '%d/%m/%Y %r') AS saved FROM user ORDER BY createdate DESC";
	  if(!$result=mysql_query($sql)) {
	     print '{error: true}';
		 exit;
	  }
	  if(!mysql_num_rows($result)) {
	      print '{error: false, result: false}';
		  exit;
      }
	  $html = '
	  <table border="0" width="100%" cellspacing="0" cellpadding="0">
	  <tr class="title1">
	     <td height="30" align="top" colspan="2"><u>Name/Phone No.</u></td>
		 <td align="top"><u>Created</u></td>
	  </tr>';
	  
	  $color = '#E4E4E4'; $i=0;   
	  while($row=mysql_fetch_assoc($result)) 
	  {
	     $color = $i++%2 ? '#FFFFFF' : '#E4E4E4';
		 
	     $html .= '
		 <tr bgcolor="'.$color.'" 
		    onmouseover="this.style.backgroundColor=\''.HOVERCOLOR.'\'" onmouseout="this.style.backgroundColor=\''.$color.'\'">
		    <td height="25" width="25">
		    <input type="checkbox" value"'.$row['id'].'" onclick="addtognos(this, \''.$row['misdn'].'\')"/></td>
		    <td>'.get_phone_display_label($row['misdn'], 35).'</td>
		    <td>'.$row['saved'].'</td>
		  </tr>';
	  }
	  
	  $html .= '</table>';
	  $html = '<div style="width: 500px; height: 260px; overflow-x: hidden; overflow-y: auto">'.$html.'</div><br/>
	  <input type="button" class="button" value="Add Phone Numbers" onclick="setlabel(\'general\')" 
		   style="cursor: pointer" title="Add Selected Phone Numbers"/>
		   <input type="button" class="button" value="Cancel" style="width: 50px" 
		   onclick="document.getElementById(\'gimportlist\').value = \'\';clearmsg();" 
		   style="cursor: pointer" title="Click to cancel"/>';
	  print $html;
	  exit;
   }
   elseif($category=='worklist') 
   {
      $userId = get_user_id();
	  $sql = 'SELECT * FROM initiative ORDER BY name';
	  if(!$result=mysql_query($sql)) {
	     print '{error: true}';
		 exit;
	  }
	  if(!mysql_num_rows($result)) {
	      print '{error:false, result:false}';
		  exit;
      }
	  $groups_html = '
	  <table border="0" width=100% cellspacing="0" cellpadding="0">
	  <tr class="title1">
	     <td height="30" align="top" colspan="2"><u>SELECT USER GROUP(S)</u></td>
	  </tr>';
	  
	  $color = '#E4E4E4'; $i=0;   
	  $ids = array();
	  //$fh = fopen('/tmp/debug.log', 'w');
	  while($row=mysql_fetch_assoc($result)) 
	  {
	     $color = $i++%2 ? '#FFFFFF' : '#E4E4E4';
		 $id = 'grp'.$i;
		 
		 $_sql = "SELECT * FROM user WHERE FIND_IN_SET('$row[id]', groups) > 0";
		 $result2 = execute_query($_sql);
		 
		 $total = mysql_num_rows($result2);
		 $phones = array();
		 while($row2=mysql_fetch_assoc($result2)) {
		     $phones[] = $row2['misdn'];
		 }
		 $phones = implode(',', $phones);
		 if($total) {
		     $ids[] = $id;
		 }
	     
		 $groups_html .= '
		 <tr bgcolor="'.$color.'" 
		    onmouseover="this.style.backgroundColor=\''.HOVERCOLOR.'\'" onmouseout="this.style.backgroundColor=\''.$color.'\'">
		     <td height="25" width="25">
		     <input type="checkbox" checked="checked" value="'.$phones.'" id="'.$id.'" '.($total ? '' : 'disabled="disabled"').'/></td>
		     <td>'.$row['name'].' ('.$total.')</td>
		 </tr>';
	  }
	  $groupids = implode(',', $ids);
	  $groups_html .= '</table>';
	  //
	  
	  $userId = get_user_id();
	  $sql = "SELECT workinglist.*, DATE_FORMAT(createdate, '%d/%m/%Y %r') AS saved FROM workinglist 
	          WHERE owner='$userId' ORDER BY createdate DESC";
	  if(!$result=mysql_query($sql)) {
	     print '{error: true}';
		 exit;
	  }
	  $total = mysql_num_rows($result);
	  
	  $phones = array();
	  while($row=mysql_fetch_assoc($result)) 
	  {
          $phones[]=$row['misdn'];
	  }
	  
	  $wlist_html = '
	  <table border="0" width="100%" cellspacing="0" cellpadding="0">
	  <tr> 
	      <td height=40 colspan=3>
		  <input type=checkbox id=wlist value="'.implode(',', $phones).'" '.($total ? '' : 'disabled="disabled"').'/>
		  WORKING LIST - '.$total.' MEMBER(S)</td>
	  </tr></table>';
	  	  
	  $html = '<div>'.$groups_html.'</div><div>'.$wlist_html.'</div>';
	  //
	  
	  $html = '<div style="width:500px;">'.$html.'</div><br/>
	  <input type="button" class="button" value="Add Phone Numbers" onclick="setlabel(\'worklist\')" 
		   style="cursor: pointer" title="Add Selected Phone Numbers"/>
		   <input type="button" class="button" value="Cancel" style="width: 50px" 
		   onclick="document.getElementById(\'wimportlist\').value = \'\';clearmsg();" 
		   style="cursor: pointer" title="Click to cancel"/><input type="hidden">
		   <input type="hidden" id="wlids" value="'.$ids.'" >
		   <input type="hidden" id="grpids" value="'.$groupids.'" >';
	  print $html;
	  exit;
   }
   
   case 'preview':
      /* get quiz */
      $sql = "SELECT * FROM quiz WHERE id=(SELECT quizId FROM question WHERE id=$qn)";
      if(!($result=mysql_query($sql))) {
	     print '{error: true}';
		 exit;
	   }
	   if(!mysql_num_rows($result)) {
	      print '{error: true}';
		  exit;
	   }
	   $quiz = mysql_fetch_assoc($result);
	      
       $sql = "SELECT * FROM question WHERE id=$qn";
       if(!($result=mysql_query($sql))) {
	     print '{error: true}';
		 exit;
	   }
	   if(!mysql_num_rows($result)) {
	      print '{error: true}';
		  exit;
	   }	 
	   $question = mysql_fetch_assoc($result);
	   /* get the answers -*/
      $sql = "SELECT * FROM answer WHERE questionId=$qn ORDER BY no";
	  if(!($result=mysql_query($sql))) {
	     print "{error: true, sql: \"$sql\"}";
		 exit;
	  }
	  $answers = NULL;	    
	  //$i = 1;
	  while($row=mysql_fetch_assoc($result)) {
	     if($quiz['singleKeyword']) {
		     $answers .= chr($row['no'] + 96).". $row[answer]<br/>";
		 }
		 else {
	        $answers .= "$row[no]. $row[answer]<br/>";
		 }
		 //$i++;
	  }
	  if($quiz['singleKeyword']) {
	      $reply = "To reply type: $quiz[keyword] &lt;Ans&gt; & SMS to ".$shortcode;
	  }
	  else {
	      $reply = "To reply type: $question[keyword] answerNo & SMS to ".$shortcode;
	  }
	  /* phone nos. */ 
	  $sql = "SELECT * FROM quizphoneno WHERE quizId=$question[quizId]";
      if(!($result=mysql_query($sql))) {
	     print '{error: true}';
		 exit;
	  }
      if(!mysql_num_rows($result)) {
	     print '{error: false}';
		 exit;
	  }
	  $total = mysql_num_rows($result);
	  $html ='
	  <table border="0" width="100%" cellpadding="0" style="padding-left: 50px">
	  <tr>
	     <td height="40" colspan="2" class="caption2" align="center">Question SMS Preview</td>
	  </tr>	 
	  <tr>
	     <td width="20" valign="top">
		     <img src="images/msg.gif" style="cursor: pointer" title="SMS Question Preview"/>
		 </td>
		 <td style="padding-left: 10px">
		    <div>'.$question['question'].'</div>
			<div>'.$answers.'</div>
			<div>'.$reply.'</div>
			</td>
	  </tr>	 	   
	  <tr>
	     <td height="40" style="font-weight: bold" colspan="2">Total Recipients: '.$total.'</td>
	  </tr>';
	  $i = 1;
	  while($row=mysql_fetch_assoc($result)) {
	     $html .= '
		 <tr>
		    <td height="23" colspan="2"><img src="images/phone.gif" />&nbsp;&nbsp;'.$row['phone'].'</td>
		 </tr>';
	  }
	  $html .= '</table>';
	  $html = '
	  <div style="width: 500px; height: 260px; overflow-x: hidden; overflow-y: auto">'.$html.'</div><br/>
		   <span style="padding-left: 50px">
		      <input type="button" class="button" value="Cancel" style="width: 50px" style="cursor: pointer" title="Click to cancel"
		   onclick="clearmsg()" /></span>
	 </div>'; 
	 print $html;
	 exit;
	    
   break;

   case 'preview_m':
      /* get quiz */
      $sql = "SELECT * FROM quiz WHERE id=(SELECT quizId FROM question WHERE id=$qn)";
      if(!($result=mysql_query($sql))) {
	     print '{error: true}';
		 exit;
	   }
	   if(!mysql_num_rows($result)) {
	      print '{error: true}';
		  exit;
	   }
	   $quiz = mysql_fetch_assoc($result);
	   		  
       $sql = "SELECT * FROM question WHERE quizId=$quiz[id]";
       if(!($result=mysql_query($sql))) {
	      print '{error: true}';
		  exit;
	   }
	   if(!mysql_num_rows($result)) {
	      print '{error: true}';
		  exit;
	   }	 
	   
	   //
	   $html ='
	   <table border="0" cellpadding="0" style="padding-left: 50px">
	      <tr>
	           <td height="40" colspan="2" class="caption2" align="center">Questions SMS Preview</td>
	      </tr>';
	   $reply = NULL;
	   while($row=mysql_fetch_assoc($result)) {
	       /* get the answers -*/
		   $sql = "SELECT * FROM answer WHERE questionId=$row[id] ORDER BY no";
		   if(!($result2=mysql_query($sql))) {
		      print "{error: true, sql: \"$sql\"}";
			  exit;
		   }
		   if(!mysql_num_rows($result2)) {
		       continue;
		   }
		   $answers = NULL;	    
	       while($row2=mysql_fetch_assoc($result2)) {
		      if($quiz['singleKeyword']) {
			      $answers .= chr($row2['no'] + 96).". $row2[answer]<br/>";
			  }
			  else {
	             $answers .= "$row2[no]. $row2[answer]<br/>";
			  }
	        }
			$html .='  
			<tr>
	           <td width="20" valign="top">
		        <img src="images/msg.gif" style="cursor: pointer" title="SMS Question Preview"/>
		       </td>
		       <td style="padding: 0px 0px 5px 10px">
		           <div><span style="color: #008800">'.($quiz['singleKeyword'] ? $row['no'] : 
				   $row['keyword']).'</span>: '.$row['question'].'</div>
			       <div>'.$answers.'</div>
			   </td>
	        </tr>';
			if($quiz['singleKeyword']) {
			   $reply .= $row['no'].' &lt;Ans&gt; ';
			}
			else {
			   $reply .= $row['keyword'] .' &lt;Ans No&gt; ';
			}
	   }
	   if($quiz['singleKeyword']) {
	      $reply = $quiz['keyword'].' '.$reply;
	   }
	   $html .= '</table>
	   <table border="0" cellpadding="0" cellspacing="0">';
	   $reply = "To reply type: $reply & SMS to ".$shortcode;
	  $html .='
	  <tr>
	     <td height="40" colspan="2">'.$reply.'</td>
	  </tr>';
	   //
	  /* phone nos. */ 
	  $sql = "SELECT * FROM quizphoneno WHERE quizId=(SELECT quizId FROM question WHERE id=$qn)";
      if(!($result=mysql_query($sql))) {
	     print '{error: true}';
		 exit;
	  }
      if(!mysql_num_rows($result)) {
	     print '{error: false}';
		 exit;
	  }
	  $total = mysql_num_rows($result);
	  $html .='
	  <tr>
	     <td height="40" style="font-weight: bold" colspan="2"><u>Total Recipients: '.$total.'</u></td>
	  </tr>';
	  $i = 1;
	  while($row=mysql_fetch_assoc($result)) {
	     $html .= '
		 <tr>
		    <td height="23"></td>
			<td><img src="images/phone.gif" />&nbsp;&nbsp;'.$row['phone'].'</td>
		 </tr>';
	  }
	  $html .= '</table>';
	  $html = '
	  <div style="width: 500px; height: 260px; overflow-x: hidden; overflow-y: auto">'.$html.'</div><br/>
		   <span style="padding-left: 50px">
		      <input type="button" class="button" value="Cancel" style="width: 50px" style="cursor: pointer" title="Click to cancel"
		   onclick="clearmsg()" /></span>
	 </div>'; 
	 print $html;
	 exit;
	    
   break;
   
   case '_ui':
       require 'display.php';
	   print get_user_info();
	   exit();

   case 'mresult':
       require 'display.php';
	   print get_msurvey_result();
	   exit();

   case 'getgrps':
       require 'display.php';
	   print get_user_groups();
	   exit();
   
   default: print '{error: true}';
   exit;
}

?>

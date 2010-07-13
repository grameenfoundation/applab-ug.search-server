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
include("display.php");

session_start();

dbconnect();
validate_session(); 

extract($_GET);

if(!preg_match("/^[0-9]+$/", $surveyId)) { 
   goto('msurveys.php');
}
if(!admin_user()) {
    goto('mresults.php?surveyId='.$surveyId);
}

$sql = "SELECT msurvey.*, DATE_FORMAT(createdate, '%d/%m/%Y %r') AS createdate, updated FROM msurvey WHERE id=$surveyId";

$result = execute_query($sql);
if(!mysql_num_rows($result)) {
   goto('msurveys.php');
}
$survey = mysql_fetch_assoc($result);
require 'insertf.php';

if(isset($_POST['save_logic'])) {
   $combination = array();
   foreach($_POST as $key=>$val) {
	   if(preg_match("/^(field)([0-9]+)$/", $key, $matches)) {
	       $no = $matches[2];
	       $answer = $_POST['field'.$no.'_ans'];
		   if(!strlen($answer)) {
		      continue;
		   }
		   $combination[] = array('fieldno' => $no, 'question'=>$val, 'answer' => $answer);
	   }
   }
   if(count($combination)) { 
       $logic = array('combination' => $combination, 'content'=>$content_file);
       $logic = mysql_real_escape_string(serialize($logic)); 
	   $sql = "INSERT INTO logic(createdate, surveyId, logic) VALUES (NOW(), '$surveyId', '$logic')"; 
	   execute_update($sql);
	   if($survey['active']) {
	        rewrite_fif($surveyId);
	   }
	   $_SESSION['showlogic'] = 1;
	   goto("msurvey.php?surveyId=$surveyId");
   }
}

if(isset($_GET['change_content'])) 
{
     if(!preg_match("/^[0-9]+$/", $_GET['logicId']) || !strlen($_GET['content'])) {
	     goto("msurvey.php?surveyId=$surveyId");
	 }
	 $record = get_table_record('logic', $_GET['logicId']);
	 if(empty($record)) {
	     goto("msurvey.php?surveyId=$surveyId");
	 }
	 $logic = unserialize($record['logic']);
	 $logic['content'] = $_GET['content']; 
	 $logic = mysql_real_escape_string(serialize($logic)); 
	 $sql = "UPDATE logic SET logic='$logic' WHERE id='$_GET[logicId]' AND surveyId='$surveyId'"; 
	 execute_update($sql);   
	 if($survey['active']) {
	        rewrite_fif($surveyId);
	 }	 
	 $_SESSION['showlogic'] = 1; 
	 goto("msurvey.php?surveyId=$surveyId");
}

if(isset($_GET['delete_logic'])) 
{
	  $sql = "DELETE FROM logic WHERE id='$logicId' AND surveyId='$surveyId'";
	  execute_update($sql);
	  if($survey['active']) {
	       rewrite_fif($surveyId);
	  }   
	  $_SESSION['showlogic'] = 1; 
	  goto("msurvey.php?surveyId=$survey[id]");
}

if(isset($_POST['cancel'])) {
   goto('msurveys.php');
}
if(isset($_POST['results'])) {
   goto("mresults.php?surveyId=$surveyId");
}
if(isset($_POST['search'])) {
   goto("msearch.php?surveyId=$surveyId");
}
if(isset($_POST['edit'])) {
   goto("editmsurvey.php?surveyId=$surveyId");
}
if(isset($_POST['delete'])) {
  delete_msurvey($surveyId);
  goto("msurveys.php");
}
if(isset($_GET['delete'])) {
   //delete_survey_question($surveyId, $question);
   goto("msurvey.php?surveyId=$survey[id]");
}
if(isset($rewrite)) {
   rewrite_fif($surveyId);
   goto("msurvey.php?surveyId=$survey[id]");
}
if(isset($activate)) {
   if($activate == 1) {
      activate_msurvey($surveyId);
   }
   elseif($activate == 0) {
      deactivate_msurvey($surveyId);
   }
   goto("msurvey.php?surveyId=$survey[id]");
}

$logic_set = get_row_count('logic', 'surveyId', $surveyId); 

if($logic_set) 
{
    $sql  = "SELECT * FROM logic WHERE surveyId=$surveyId";
	$result = execute_query($sql);
	$current_logic_html = '<table border=0 width=100% cellpadding=0 cellspacing=0>
	<tr>
	    <td height=30>
			<input type="button" class="button" value="Cancel" onclick="hclogic()" />
		</td>
	 </tr>
	 <tr bgcolor=#e4e4e4>
	    <td height=35>&nbsp;<strong><u>Question Combination</u></strong></td>
		<td>&nbsp;<strong><u>Display Content</u></strong></td>
	 </tr>
	';
	$i = 1;
	while($row=mysql_fetch_assoc($result)) {
	    $logic = unserialize($row['logic']);
		
		$list = '<ul>';
		foreach($logic['combination'] as $field) {
		   $list .= '<li style="cursor: pointer; padding: 3px 0px 3px 0px" title="'.($field['question'].': '.$field['answer']).'">'.truncate_str($field['question'], 40).': <span style="color: #008800">'.truncate_str($field['answer'], 20).'</span></li>';
		}
		$list .= '</ul>';
		$content_options = NULL;
		foreach($content_display_files as $file) {
		    $content_options .= '<option value="'.$file.'" '.(!strcmp($logic['content'], $file) ? 'selected="selected"' : '').'>'.$file.'</option>';
		} 
		$current_logic_html .= '
		<tr>
		    <td>
			  <div id="_l'.$i.'" style="display: none; border: solid #FF3300 1px; background-color: #FFFFFF; width: 400px; height: 100px; padding: 20px"><p style="font-weight: bold; text-decoration: underline">Change Content File</p><select id="_c'.$i.'" class="input" style="width: 300px">'.$content_options.'</select>
			  <p><input type="button" name="change_content" value="Update" class="button" onclick="location.replace(\'?surveyId='.$surveyId.'&logicId='.$row['id'].'&content=\'+document.getElementById(\'_c'.$i.'\').options[document.getElementById(\'_c'.$i.'\').selectedIndex].value+\'&change_content=TRUE\')"/>
			     <input type="button" name="cancel" value="Cancel" class="button" onclick="hedit(\'_l'.$i.'\');" />
				 <input type="hidden" name="logicId" value="'.$row['id'].'" />
				 </p></div>'.$list.'
			</td>
			<td>'.$logic['content'].'
			<p>
			
			<a href="#" onclick="sedit(\'_l'.$i.'\');return false;" style="color: #0000FF; font-size: 10px">[Edit]</a> |
			<a href="?surveyId='.$surveyId.'&logicId='.$row['id'].'&delete_logic=TRUE" style="color: #FF0000; font-size: 10px" onclick="return confirm(\'Are you sure you want to delete this Decision Logic?\')">[Delete Logic]</a>
			</p>
			</td>
		</tr>';
		$i++;
	}
	$current_logic_html .= '</table>';
	$current_logic_html = '<div style="width: 600px; height: 400px; background-color: #fff; 
	border: solid #FF3300 1px; padding: 30px; overflow: auto">'.$current_logic_html.'</div>';
}

$fields = unserialize($survey['fif']); 
if(is_array($fields) && count($fields)) 
{ 
     $qns_list = '<table border=0 width=100% cellpadding=0 cellspacing=0>
	 <tr>
	    <td colspan=3 height=30>
		    <input type=button class=button id="vb" value="View Current Logic" onclick="clogic()" '.($logic_set ? '' : 'disabled="disabled"').' />
			<input type="button" class="button" id="cb" value="Cancel" onclick="hlogic()" />
		</td>
	 </tr>
	 <tr>
	    <td colspan=3><div id="_clogic" style="display: none">'.$current_logic_html.'</div></td>
	 </tr>	 
	 <tr bgcolor=#e4e4e4>
	    <td colspan=2 height=35>&nbsp;<strong><u>Question</u></strong></td>
		<td>&nbsp;<strong><u>Answer</u></strong></td>
	 </tr>';
	 $i = 1;
	 $logic_c = 0;
	 
	 foreach($fields as $field) 
	 {
	       if(!preg_match("/Menu/i", $field['type']) && !preg_match("/Radio/i", $field['type'])) {
		       $i++;
		       continue;
		   } 
		   $ans_list = '
		   <select class="input" style="width: 200px" name="field'.$i.'_ans" id="field'.$i.'_ans" '.(!strlen($_POST['field'.$i.'_ans']) ? 'disabled="disabled"' : '' ).'>
		   <option></option>';
		   
		   foreach($field['options'] as $option) {
			  $ans_list .= '<option>'.$option['name'].'</option>';
		   }
		   $ans_list .= '</select>';
		   $logic_c++;
		   $qns_list .= '
		   <tr onmouseover="this.style.backgroundColor=\'#C6E2A9\'" onmouseout="this.style.backgroundColor=\'#FFFFFF\'">
		      <td height=30>
			      <input type="checkbox" name="field'.$i.'" id="field'.$i.'" 
				  onclick="en_ansf(\'field'.$i.'_ans\')" value="'.$field['name'].'"/>
			  </td>
			  <td style="cursor: pointer" title="'.$field['name'].'">&nbsp;'.truncate_str($field['name'], 60).'</td>
			  <td>'.$ans_list.'</td>
		   </tr>';
		   $i++;   
	 }
	 $content_options = NULL;
	 foreach($content_display_files as $file) 
	 {
	     $content_options .= '<option value="'.$file.'" '.(!strcmp($file, $_POST['content_file']) ? 
		 'selected="selected"' : '' ).'>'.$file.'</option>';
	 }
	 
	 $qns_list .= '
	 <tr>
	     <td colspan=3 height=10>
		     <div id="_clogic" style="display: none">'.$logic_html.'</div>
		 </td>
	 </tr>
	 <tr bgcolor=#e4e4e4>
	    <td colspan=3 height=30>&nbsp;<u><strong>Choose Content File:</strong></u>&nbsp;
		<select class="input" style="width: 250px" id="content_file" name="content_file">'.$content_options.'</select>
		</td>
	 </tr>';
	 $qns_list .= '</table>';
	 
	 $logic_html = '
	 <div id="logicd" style="width: 650px; height: 400px; background-color: #fff; border: solid #FF3300 1px; padding: 30px; overflow: auto">
	     <div>'.($survey['updatelogic'] ? '<u>IMPORTANT</u>: <span style="color: #FF3300">Some Survey Fields have changed and the Decision Logic was modified</span><br/><br/>' : '').'</div>
	     <form method="post">
		    <div>'.$qns_list.'</div>
	        <div style="padding: 20px 0px 20px 0px">
		       <input type="submit" name="save_logic" class="button" value="Create Logic" />
		       <input type="button" class="button" value="Cancel" onclick="hlogic()" />
		    </div>
		 </form>
	 </div>';
}

extract($survey); 

if($updatelogic) {
   execute_update("UPDATE msurvey SET updatelogic=0 WHERE id='$surveyId'");
}

/* menu highlight */
$page = 'msurvey';

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title><?= TITLE ?></title>
<link rel="stylesheet" type="text/css" href="styles/style.css" />
<script type="text/javascript" src="basic.js"></script>
<script type="text/javascript">
function writefif(id) {
   var active = <?= $active ? 'true' : 'false' ?>;
   if(!active) {
      alert('This survey is not currently active. You can only re-write a FIF file for an active survey');
	  return false;
   }
   if(confirm('Rewrite FIF for this survey?')) {
      location.replace('msurvey.php?surveyId='+id+'&rewrite=TRUE');
   }
   return false;
}
</script>
</head>

<body class="main">
<table width="790" border="0" align="center" cellpadding="0" cellspacing="0">
     <!--DWLayoutTable-->
     <tr>
          <td width="790" height="124" valign="top"><? include('top.php') ?></td>
     </tr>
     <tr>
          <td height="362" valign="top"><table width="100%" border="0" cellpadding="0" cellspacing="0" class="border">
               <!--DWLayoutTable-->
               <tr>
                    <td height="22" colspan="6" align="center" valign="middle" class="caption">Mobile Survey Details </td>
                    </tr>
               <tr>
                    <td width="31" height="20">&nbsp;</td>
                    <td width="25">&nbsp;</td>
                    <td width="34">&nbsp;</td>
                    <td width="645">&nbsp;</td>
                    <td width="31">&nbsp;</td>
                    <td width="22">&nbsp;</td>
               </tr>
               <tr>
                    <td height="28">&nbsp;</td>
                    <td>&nbsp;</td>
                    <td valign="middle"><img src="images/quiz.gif" width="34" height="28" style="cursor: pointer" title="<?= $name ?>" /></td>
                    <td valign="middle">&nbsp;
					<span class="caption2">Name: </span>
					<span style="color: <?= $useraccess ? '#FF33CC' : '' ?>"><?= truncate_str($name, 50) ?></span> 
					- 
					<span style="color: #666666">created <?= $createdate ?>
					</span>					</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
               </tr>
               <tr>
                    <td height="26">&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
               </tr>
               <tr>
                 <td height="230">&nbsp;</td>
                 <td colspan="4" valign="top">
			          <fieldset>
					<legend>Form Information File<span style="font-weight: normal"><?= $active ? ' <span style="color: #008800">(CURRENTLY ACTIVE)' : ' <span style="color: #FF0000">(SURVEY NOT ACTIVE)</span>'?></span></legend>
					<table width="100%" border="0" cellpadding="0" cellspacing="0">
                         <form method="post">
                         <tr>
                              <td width="30" height="60"></td>
                              <td width="671" valign="middle"><input name="edit" type="submit" class="button" id="edit" value="Edit Survey"/>
                                <input name="logic" type="button" class="button" id="logic" value="Decision Logic" onclick="slogic()" <?= !$logic_c ? 'disabled="disabled"' : '' ?> <?= $logic_set ? 'style="color: #008800; font-weight: bold"' : '' ?>/>
                                <input name="xml" type="button" class="button" id="xml" value="View XML" onclick="window.open('xml.php?surveyId=<?= $surveyId ?>')"/>
                                <input name="results" type="submit" class="button" id="results" value="Results"/>
                                <input name="search" type="submit" class="button" id="search" value="Search Results"/>
                                <input name="cancel" type="submit" class="button" id="cancel" value="&laquo; Survey List" />
                                <input name="delete" type="submit" class="button" id="delete" value="Delete" 
							   onclick="return confirm('Are you sure you want to delete this survey? All results submitted for this survey shall be deleted as well.')"/></td>
                              <td width="32"></td>
                         </tr>
						 </form>
                         <tr>
                         			<td height="30"></td>
                         			<td valign="middle">
									<div id="_ifield"></div>
						   <? if($survey['active']) { ?>
						   [ <a href="<?= $appurl ?>/FIF/fif.xml" target="_blank" title="Open FIF file">Open Active FIF</a> ] 
						   <!--[ <a href="?surveyId=<?= $surveyId ?>&rewrite=TRUE" title="Rewrite FIF file" 
						   onclick="return confirm('Rewrite FIF for this survey?');">Rewrite FIF</a> ] -->
						   [ <a href="sendsms.php?surveyId=<?= $surveyId ?>&sendfif=TRUE&return=<?= urlencode("msurvey.php?surveyId=$surveyId") ?>" title="Send FIF Link to Phone Numbers">Send SMS Notification</a> ]
						   [ <a href="?surveyId=<?= $surveyId ?>&activate=0" 
						   onclick="return confirm('Are you sure you wish to de-activate this survey?');">De-Activate Survey</a> ]
						   <? } 
						   else {
						      print '[ <a href="?surveyId='.$surveyId.'&activate=1" onclick="return confirm(\'Activate this survey?\');">Activate Survey</a> ]';
						   }
						   ?>
						   [ <a href="#" onclick="insertf();return false;" target="_blank">Insert Field</a> ]						   
						   </td>
                           <td></td>
                         			</tr>
                         
                         
                         
                         
                         <tr>
                           <td height="320">&nbsp;</td>
                           <td valign="middle" bgcolor="#EEEEEE">
						   <div id="logicd" style="display: none"><?= $logic_html ?>
			   			</div>						   <div style="padding: 20px">
						   			<? 

							 /* show  form */
							 print make_html_form($surveyId);
						   ?>	
			   			</div></td>
                            <td>&nbsp;</td>
                         </tr>
						 <form method="post">
                         <tr>
                           <td height="35"></td>
                           <td valign="middle"><input name="edit" type="submit" class="button" id="edit" value="Edit Survey"/>
						      		<input name="logic" type="button" class="button" id="logic" value="Decision Logic" onclick="slogic()" <?= !$logic_c ? 'disabled="disabled"' : '' ?> <?= $logic_set ? 'style="color: #008800; font-weight: bold"' : '' ?>/>
						      		 <input name="xml" type="button" class="button" id="xml" value="View XML" onclick="window.open('xml.php?surveyId=<?= $surveyId ?>')"/>
						       <input name="results" type="submit" class="button" id="results" value="Results"/> 
						      <input name="search" type="submit" class="button" id="search" value="Search Results"/>
						       <input name="cancel" type="submit" class="button" id="cancel" value="&laquo; Survey List" /> 
					          <input name="delete" type="submit" class="button" id="delete" value="Delete" 
							   onclick="return confirm('Are you sure you want to delete this survey? All results submitted for this survey shall be deleted as well.')"/></td>
                           <td></td>
                         </tr>
                         <tr>
                         			<td height="26"></td>
                         			<td>&nbsp;</td>
                         			<td></td>
                         			</tr>
						 </form>
				      </table>
                 </fieldset></td>
                    <td>&nbsp;</td>
               </tr>
               <tr>
                 <td height="34">&nbsp;</td>
                 <td>&nbsp;</td>
                 <td>&nbsp;</td>
                 <td>&nbsp;</td>
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
<script type="text/javascript">
<?
  if($updatelogic) {
      print "slogic();\n";
  }
  elseif(isset($_SESSION['showlogic'])) { 
     unset($_SESSION['showlogic']);
     print "slogic();\nclogic();\n";
  }
  print nf_js($surveyId);
  if(isset($_POST['_sinsert'])) {
      print "\ninsertf()";
  }
  
?>
</script>

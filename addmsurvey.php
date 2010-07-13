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

if(isset($_POST['cancel'])) {
   goto('msurveys.php');
}

if(count($_POST)) { 
  $_POST = strip_form_data($_POST);
  extract($_POST);
}  

if(isset($_POST["submit"])) { 
   if(strlen($_POST['name']) < 3) {
     $errors = 'Survey name not valid<br/>';
   }
   $fields = array();
   $names = array();
   for($i=1, $j=1; $i<= $maxflds; $i++) {
       if(!strlen($_POST['field'.$i])) {
	      continue;
	   } 
	   if(!preg_match("/^[a-z0-9-.\s:'?,]+$/i", $_POST['field'.$i])) {
	      $errors .= "Field Name $i not valid<br/>";
		  break;
	   }	   
	   if(in_array($_POST['field'.$i], $names)) {
	      $errors .= 'Duplicate Field Name "'.$_POST['field'.$i].'"<br/>';
		  break;	      
	   }
	   $names[] = $_POST['field'.$i];
	   $type = $_POST['type'.$i];
	   $code = 'f'.substr(md5($_POST['field'.$i]), 0, 16);
	   $options = array();
	   if(preg_match("/checkbox/i", $type) || preg_match("/radio/i", $type) || preg_match("/menu/i", $type)) {
	      /* get options */
		  $list = rtrim($_POST['field'.$i.'options']);
		  if(!strlen($list)) {
		     $errors = "Options for Field $i not valid<br/>";
			 break;
		  }
		  $list = preg_split("/\n+/", $list);
		  $k = 1;
		  foreach($list as $item) {
		     if(!strlen($item)) {
			     continue;
			 }
			 if(preg_match("/FLD_[0-9]+/i", $item)) {
			     $errors = "Invalid option: $item<br/>";
				 break;
			 }
			 $options[] = array('name'=>rtrim($item), 'value'=>$k);
			 $k++;
		  } 
	   }
	   $fields[] = array('name' => $_POST['field'.$i], 'type' => $type, 'code' => $code, 'options'=>$options);  
	   $j++; 
   } 
   //foreach($fields as $f) { if(!count($f[options])) continue; foreach($f[options] as $o) { print "$o[value]: $o[name]<br/>"; print '<br>'; } }
   if(!isset($errors) && !count($fields)) {
       $errors .= 'Specify at least one field<br/>';
   } 
   if(!isset($errors)) { 
	 extract(escape_form_data($_POST));
	 $fif = mysql_real_escape_string(serialize($fields));
	 $name = mysql_real_escape_string($name);
     $sql = "INSERT INTO msurvey(createdate, name, fif, active, useraccess) 
	         VALUES(CURRENT_TIMESTAMP(), '$name', '$fif', '$active', '$useraccess')"; 
	 execute_nonquery($sql);
	 $surveyId = mysql_insert_id();
	 if(!$surveyId) {
	    show_message('ID Not set', 'Can not obtain file name. mysql_insert_id() returned 0', '#FF0000');
	 }
	 if($active) {
	    activate_msurvey($surveyId);
	 }
	 logaction("Created Mobile Survey (Name: $name)");
	 goto("msurvey.php?surveyId=$surveyId");
  }
}
if(!isset($active)) {
   $active = 1;
}
if(isset($errors)) {
  $errors = "<br/>$errors<br/>";
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
<link rel="stylesheet" type="text/css" href="styles/date.css" />
<script type="text/javascript" src="date.js"></script>
<script type="text/javascript" src="basic.js"></script>
<script type="text/javascript">
<?
$js = "var moreflds = {\n";
for($i=1; $i<=$moreflds; $i++) {
   $js .= "\tfield$i: {showing: ".(isset($_POST['field'.($i+3)]) ? 'true' : 'false')."},\n";
}
$js = preg_replace("/,$/", "", $js);
$js .= "}\n";
print $js;
?>
</script>
</head>

<body class="main">
<table width="790" border="0" align="center" cellpadding="0" cellspacing="0">
     <!--DWLayoutTable-->
     <tr>
          <td width="790" height="124" valign="top"><? include('top.php') ?></td>
     </tr>
     <tr>
       <td height="455" valign="top">
            <table width="100%" border="0" cellpadding="0" cellspacing="0" class="border">
                 <!--DWLayoutTable-->
                 <tr>
                      <td height="22" colspan="3" align="center" valign="middle" class="caption">Create Mobile Survey </td>
                 </tr>
                 <tr>
                      <td width="19" height="48">&nbsp;</td>
                      <td width="747">&nbsp;</td>
                      <td width="22">&nbsp;</td>
                 </tr>
                 <tr>
                   <td height="350"></td>
                   <td valign="top">
			         <form name="df" method="post" enctype="multipart/form-data" onsubmit="return !page.locked">
                          <fieldset>
                          <legend>Survey Details</legend>
		                  <table width="" border="0" cellpadding="0" cellspacing="0">
		                       <!--DWLayoutTable-->
		                       <tr>
		                            <td width="29" height="23">&nbsp;</td>
                        <td width="108">&nbsp;</td>
                        <td width="30">&nbsp;</td>
                        <td width="10">&nbsp;</td>
                        <td width="220">&nbsp;</td>
                        <td width="131">&nbsp;</td>
                        <td width="75">&nbsp;</td>
                        <td width="112">&nbsp;</td>
                        <td width="30">&nbsp;</td>
                        </tr>
		                       <tr>
		                         <td height="23"></td>
		                         <td></td>
		                         <td></td>
		                         <td></td>
		                         <td colspan="4" valign="top" class="error">
                                   <? if(isset($errors)) echo $errors; ?>					   </td>
                        <td></td>
                            </tr>
		                       <tr>
		                         <td height="5"></td>
		                         <td></td>
		                         <td></td>
		                         <td></td>
		                         <td></td>
		                         <td></td>
		                         <td></td>
		                         <td></td>
		                         <td></td>
                            </tr>
		                       
					     

				      <tr>
				                            <td height="28">&nbsp;</td>
                                            <td colspan="3" align="right" valign="middle">Survey Name:&nbsp;&nbsp;</td>
                         <td colspan="4" valign="middle">
					        <input name="name" type="text" class="input" id="name" value="<?= $name ?>" size="55" /></td>
                         <td>&nbsp;</td>
				      </tr>
				      <tr>
				                            <td height="28">&nbsp;</td>
                                            <td colspan="3" align="right" valign="middle">Status:&nbsp;&nbsp;</td>
                           <td colspan="4" valign="middle">
						      <select name="active" class="input" id="active" style="width: 365px">
						         <option value="1" <?= $active ? 'selected="selected"' : '' ?>>ACTIVE</option>
						         <option value="0" <?= !$active ? 'selected="selected"' : '' ?>>NOT ACTIVE</option>
				           </select></td>
                           <td>&nbsp;</td>
                           </tr>	
				      <tr>
				                            <td height="35">&nbsp;</td>
                                            <td colspan="3" align="right" valign="middle">Limited User Access:&nbsp;&nbsp;</td>
                           					<td colspan="4" valign="middle">
											<input name="useraccess" type="radio" value="1" <?= $useraccess==1 ? 'checked="checked"' : '' ?>/>
                           								<span style="color: #008800">YES</span>
                           											&nbsp;&nbsp;
											<input name="useraccess" type="radio" value="0" <?= $useraccess==0 ? 'checked="checked"' : '' ?>/> 
                           											<span style="color: #FF0000">NO</span>
</td>
                           					<td>&nbsp;</td>
                           </tr>							   				  
				      <tr>
				        <td height="7"></td>
				        <td></td>
				        <td></td>
				        <td></td>
				        <td></td>
				        <td></td>
				        <td></td>
				        <td></td>
				        <td></td>
				        </tr>
				      <tr>
				                            <td height="35">&nbsp;</td>
                         <td>&nbsp;</td>
                         <td>&nbsp;</td>
                         <td colspan="2" valign="middle" class="caption2"><u>Field Name </u></td>
                         <td align="center" valign="middle" class="caption2"><u>Field Type </u></td>
                         <td>&nbsp;</td>
                         <td>&nbsp;</td>
                         <td>&nbsp;</td>
                         </tr>					  
			                          <tr>
			                               <td height="28">&nbsp;</td>
                          <td>&nbsp;</td>
                          <td valign="middle">1.</td>
                          <td colspan="2" valign="middle" nowrap="nowrap">
                                   <input name="field1" type="text" class="input" id="field1" value="<?= $field1 ?>" size="35" />                              </td>
                         <td align="center" valign="middle">
						     <select name="type1" class="input" id="type1" style="width: 100px" onchange="color(1)">
						        <? 
							    foreach($fiftypes as $f) { 
								    print '<option value="'.$f['value'].'" '.($type1==$f['value'] ? 'selected="selected"' : '').'>'.
									$f['label'].'</option>'; 
								} 
							?>  
				              </select>
						     <input name="field1options" type="hidden" id="field1options" value="<?= $field1options ?>" /></td>
                          <td align="center" valign="middle" class="options" id="optl1" onclick="opts(1)">
						  <span id="field1optl">options</span></td>
                          <td valign="top"><div id="field1opts"></div></td>
                          <td>&nbsp;</td>
                          </tr>
			                          <tr>
			                               <td height="28">&nbsp;</td>
                          <td>&nbsp;</td>
                          <td valign="middle">2.</td>
                          <td colspan="2" valign="middle" nowrap="nowrap">
						  <input name="field2" type="text" class="input" id="field2" value="<?= $field2 ?>" size="35" /></td>
                         <td align="center" valign="middle">
						 <select name="type2" class="input" id="type2" style="width: 100px" onchange="color(2)">
                             <? 
							    foreach($fiftypes as $f) { 
								    print '<option value="'.$f['value'].'" '.($type2==$f['value'] ? 'selected="selected"' : '').'>'.
									$f['label'].'</option>'; 
								} 
							?>
                          </select>
                            <input name="field2options" type="hidden" id="field2options" value="<?= $field2options ?>" /></td>
                          <td align="center" valign="middle" class="options" id="optl2" onclick="opts(2)">options</td>
                          <td valign="top"><div id="field2opts"></div></td>
                          <td>&nbsp;</td>
                          </tr>
					      
						  <tr>
					      <td height="28">&nbsp;</td>
                          <td>&nbsp;</td>
                          <td valign="middle">3.</td>
                          <td colspan="2" valign="middle" nowrap="nowrap">
						  <input name="field3" type="text" class="input" id="field3" value="<?= $field3 ?>" size="35" /></td>
                         <td align="center" valign="middle">
						 <select name="type3" class="input" id="type3" style="width: 100px" onchange="color(3)">
                             <? 
							    foreach($fiftypes as $f) { 
								    print '<option value="'.$f['value'].'" '.($type3==$f['value'] ? 'selected="selected"' : '').'>'.
									$f['label'].'</option>'; 
								} 
							?>
                          </select>
                            <input name="field3options" type="hidden" id="field3options" value="<?= $field3options ?>"/></td>
                          <td align="center" valign="middle" class="options" id="optl3" onclick="opts(3)">options</td>
                          <td valign="top"><div id="field3opts"></div></td>
                          <td>&nbsp;</td>
                          </tr>	
						<? for($i=1; $i<=$moreflds; $i++) { $j=$i+3; $showfld = isset($_POST['field'.$j]) ? 1 : 0; ?>
						 <tr id="row<?= $i ?>" style="display: <?= $showfld ? (preg_match("/msie/i", $_SERVER['HTTP_USER_AGENT']) ? 'inline' : 'table-row') : 'none'?>">
						    <td <?= $showfld ? 'height="28" ' : ''?>id="fld<?= $i ?>ht"></td>
							<td></td>
							<td valign="middle" id="fld<?= $i ?>label"><?= $showfld ? $j.'.' : '' ?></td>
							<td colspan="2" valign="middle" id="fld<?= $i ?>field">
					           <? if($showfld) print '<input name="field'.$j.'" type="text" class="input" value="'.$_POST['field'.$j].'" size="35" />' ?>							</td>
							<td align="center" valign="middle" id="fld<?= $i ?>type">
						    <?
								  if($showfld) { 
		                            $t = '<select name="type'.$j.'" id="type'.$j.'" class="input" style="width: 100px" onchange="color('.$j.')">';
								    foreach($fiftypes as $f) { 
								      $t .= '<option value="'.$f['value'].'" '.($_POST['type'.$j]==$f['value'] ? 
									  'selected="selected"' : '').'>'.$f['label'].'</option>';
								    }
								    print $t.'</select>';
								  }	 
								  
								 ?>	
						    <input name="field<?= $j ?>options" type="hidden" id="field<?= $j ?>options" 
							value="<?= $_POST['field'.$j.'options'] ?>" />							</td>
							<td align="center" valign="middle" class="options" id="optl<?= $j ?>" onclick="opts(<?= $j ?>)">
							<?= $showfld ? 'options' : '' ?></td>
							<td valign="top"><div id="field<?= $j ?>opts"></div></td>
							<td></td>
							</tr> 
					    <? } ?>
					     <tr>
					      <td height="37"></td>
                          <td></td>
                          <td></td>
                          <td colspan="5" valign="middle">
					<input name="submit" type="submit" class="button" id="submit" value="Create Survey" />
                    <input name="mfbtn" type="button" <?= isset($_POST['field'.$maxfields]) ? 'disabled="disabled"' : '' ?> class="button" id="mfbtn" value="+ More Fields" onclick="moref()"/>
                    <input name="lfbtn" type="button" <?= !isset($_POST['field4']) ? 'disabled="disabled"' : '' ?> class="button" id="lfbtn" onclick="lessf()" value="- Less Fields"/>
                           <input name="cancel" type="submit" class="button" id="cancel" value="Cancel" /></td>
                         <td></td>
                          </tr>
					     <tr>
					        <td height="13"></td>
					        <td></td>
					        <td></td>
					        <td></td>
					        <td></td>
					        <td></td>
					        <td></td>
					        <td></td>
					        <td></td>
					        </tr>
					     
					     <tr>
					        <td height="34"></td>
					        <td colspan="7" align="center" valign="middle" id="note"><span style="color: #CC0000">Please Note:</span> The Code for each field in the form must be unique </td>
					        <td>&nbsp;</td>
					        </tr>
					     <tr>
					        <td height="29"></td>
					        <td>&nbsp;</td>
					        <td></td>
					        <td></td>
					        <td></td>
					        <td></td>
					        <td></td>
					        <td></td>
					        <td></td>
					        </tr>
                          </table>
                       </fieldset>
                   </form></td>
                      <td></td>
                 </tr>
                 <tr>
                   <td height="32"></td>
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
<script>colorall();</script>

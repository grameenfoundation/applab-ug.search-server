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

dbconnect();
validate_session(); 
check_admin_user();

extract($_GET);

if(!preg_match("/^[0-9]+$/", $surveyId)) {
   goto('msurveys.php');
}
if(isset($_POST['cancel'])) {
   goto("msurvey.php?surveyId=$surveyId");
}
$survey = get_table_record('msurvey', $surveyId);

if(empty($survey)) {
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
   $a_fields = unserialize($survey['fif']);
   for($i=1, $j=1; $i<= $maxflds; $i++) 
   {
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
		 $position = $_POST['position'.$i];
	   $code = array_key_exists($i-1, $a_fields) ? $a_fields[$i-1]['code'] :'f'.substr(md5($_POST['field'.$i]), 0, 16);
	   $options = array();
	   if(preg_match("/checkbox/i", $type) || preg_match("/radio/i", $type) || preg_match("/menu/i", $type)) 
	   {
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
	   //$fields[] = array('name' => $_POST['field'.$i], 'type' => $type, 'code' => $code, 'options'=>$options, 'position'=>$position); 
		 $fields[] = array('name' => $_POST['field'.$i], 'type' => $type, 'code' => $code, 'options'=>$options);
	   $j++; 
   }
   if(!isset($errors) && !count($fields)) {
       $errors .= 'Specify at least one field<br/>';
   }
   if(!isset($errors)) 
   { 
	 extract(escape_form_data($_POST));
	 /*$fields = msort($fields, "position", true);
	 $temp_a = array();
	 foreach($a as $field){
			array_pop($field);
			$temp_a[] = $field;
	 }*/
	 $fif = mysql_real_escape_string(serialize($fields));
	 $name = mysql_real_escape_string($name);
	 
   $sql = "UPDATE msurvey SET name='$name', active='$active', useraccess='$useraccess' WHERE id=$surveyId"; 
	 execute_nonquery($sql);
	 $loged_update = 0;
	 if(mysql_affected_rows() > 0) {
	     logaction("Updated Mobile Survey (\"$survey[name]\" to: \"$name\")");
		 $loged_update = 1;
	 }
	 /* update FIF */
	 $sql = "UPDATE msurvey SET fif='$fif' WHERE id=$surveyId"; 
	 execute_nonquery($sql);
	 
	 if(mysql_affected_rows() > 0) 
	 {
	     if(!$loged_update) {
		      logaction("Updated Mobile Survey (\"$survey[name]\" to: \"$name\")");
		      $_SESSION['showlogic'] = 1;
		 }	  
		 
		 $oldfields = unserialize($survey['fif']);
		 
		 $sql = "SELECT * FROM logic WHERE surveyId='$surveyId'";
		 $result=execute_query($sql);
		 
		 while($row=mysql_fetch_assoc($result)) 
		 {
		     $logic = unserialize($row['logic']);
			 $new_combination = array(); 
			 foreach($logic['combination'] as $field) 
			 {
				  $fieldno = $field['fieldno']; 
				  $oldfield = $oldfields[$fieldno-1]; 
				  $oldoptions = $oldfield['options']; 
				  
				  $formfield = $fields[$fieldno-1]; 
				  $formoptions = $formfield['options']; 
				  
				  if(!preg_match("/radio/i", $formfield['type']) && !preg_match("/menu/i", $formfield['type'])) { 
				        /* field type changed */
						continue; 
				  } 
				  /* get index of old answer and pick answer at this index from the form data ! */				  
				  for($i=0; $i<count($oldoptions); $i++) 
				  {
					   if(!strcmp($oldoptions[$i]['name'], $field['answer'])) { 
					       break;
					   }
				  }
				  $answer = $formoptions[$i]['name'];
				  if(!strlen($answer)) {
				       $answer = 'xxxxxx';
				  }
				  $newfield = array('fieldno'=>$fieldno, 'question'=>$formfield['name'], 'answer'=>$answer);
				  $new_combination[] = $newfield;
			 }
			 $combination = array('combination'=>$new_combination, 'content'=>$logic['content']);
			 $_logic = mysql_real_escape_string(serialize($combination)); 
			 $sql = "UPDATE logic SET logic='$_logic' WHERE id='$row[id]' AND surveyId='$surveyId'"; 
			 execute_update($sql);
		 }
	 }
	 if($active) {
	    activate_msurvey($surveyId);
	 }
	 else { 
	    deactivate_msurvey($surveyId);
	 }		 
	 goto("msurvey.php?surveyId=$surveyId");
  }
}

if(isset($errors)) {
  $errors = "<br/>$errors<br/>";
}

extract($survey);
$fields = unserialize($survey['fif']);
if($_GET['at']) {
	array_insert($fields, array('name' => "", 'type' => "Data", 'code' => "", 'options'=>"", 'position'=>""), $_GET['at']);
}if(count($fields) <= 3) {
   $extra_flds = $moreflds;
}
else {
   $extra_flds = $maxflds - count($fields);
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
if(strlen($json)) {
print "var moreflds = $json";
}
else {  
$js = "var moreflds= {\n";
for($i=1; $i<=$extra_flds; $i++) {
   $js .= "\tfield$i: {showing: false},\n";
}
$js = preg_replace("/,$/", "", $js);
$js .= "}\n";
print $js;
}
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
                      <td height="22" colspan="3" align="center" valign="middle" class="caption">Edit Mobile Survey </td>
                 </tr>
                 <tr>
                      <td width="19" height="48">&nbsp;</td>
                      <td width="747">&nbsp;</td>
                      <td width="22">&nbsp;</td>
                 </tr>
                 <tr>
                   <td height="350"></td>
                   <td valign="top">
			         <form name="df" id ="id_df" method="post" enctype="multipart/form-data" onsubmit="return !page.locked">
                          <fieldset>
                          <legend>Survey Details</legend>
		                  <table width="" border="0" cellpadding="0" cellspacing="0">
		                       <!--DWLayoutTable-->
		                       <tr>
		                            <td width="29" height="23">&nbsp;</td>
                        <td width="109">&nbsp;</td>
                        <td width="30">&nbsp;</td>
                        <td width="9">&nbsp;</td>
                        <td width="221">&nbsp;</td>
                        <td width="131">&nbsp;</td>
                        <td width="75">&nbsp;</td>
                        <td width="112" colspan="3">&nbsp;</td>
                        <td width="29">&nbsp;</td>
		                       </tr>
		                       <tr>
		                         <td height="23"></td>
		                         <td></td>
		                         <td></td>
		                         <td></td>
		                         <td colspan="6" valign="top" class="error">
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
		                         <td colspan="3"></td>
		                         <td></td>
                            </tr>
		                       
					     

				      <tr>
				        <td height="28">&nbsp;</td>
                         <td colspan="3" align="right" valign="middle">Survey Name:&nbsp;&nbsp;</td>
                         <td colspan="6" valign="middle">
					        <input name="name" type="text" class="input" id="name" value="<?= $name ?>" size="55" /></td>
                         <td>&nbsp;</td>
                         </tr>
				      <tr>
				        <td height="28">&nbsp;</td>
                        <td colspan="3" align="right" valign="middle">Status:&nbsp;&nbsp;</td>
                         <td colspan="6" valign="middle">
						    <select name="active" class="input" id="active" style="width: 365px">
						       <option value="1" <?= $active ? 'selected="selected"' : '' ?>>ACTIVE</option>
						       <option value="0" <?= !$active ? 'selected="selected"' : '' ?>>NOT ACTIVE</option>
				              </select></td>
                         <td>&nbsp;</td>
                         </tr>	
				      <tr>
				        <td height="28">&nbsp;</td>
                        <td colspan="3" align="right" valign="middle">Limited User Access:&nbsp;&nbsp;</td>
                         <td colspan="6" valign="middle"><input name="useraccess" type="radio" value="1" <?= $useraccess==1 ? 'checked="checked"' : '' ?>/>
									<span style="color: #008800">YES</span> &nbsp;&nbsp;
									<input name="useraccess" type="radio" value="0" <?= $useraccess==0 ? 'checked="checked"' : '' ?>/>
									<span style="color: #FF0000">NO</span></td>
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
				        <td colspan="3"></td>
				        <td></td>
				        </tr>
				      <tr>
				                            <td height="35">&nbsp;</td>
                         <td>&nbsp;</td>
                         <td>&nbsp;</td>
                         <td colspan="2" valign="middle" class="caption2"><u>Field Name </u></td>
                         <td align="center" valign="middle" class="caption2"><u>Field Type </u></td>
                         <td>&nbsp;</td>
                         <td colspan="3">&nbsp;</td>
                         <td>&nbsp;</td>
                         </tr>					  
			                          <tr>
			                               <td height="28">&nbsp;</td>
                          <td>&nbsp;</td>
                          <td valign="middle">1.</td>
                          <td colspan="2" valign="middle" nowrap="nowrap">
                                   <input name="field1" type="text" class="input" id="field1" value="<?= $fields[0]['name'] ?>" size="35" />                              </td>
                         <td align="center" valign="middle">
						     <select name="type1" class="input" id="type1" style="width: 100px">
						        <? 
							    foreach($fiftypes as $f) { 
								    print '<option value="'.$f['value'].'" '.($fields[0]['type']==$f['value'] ? 'selected="selected"' : '').'>'.
									$f['label'].'</option>'; 
								} 
							?>  
					           </select>
						     <input name="field1options" type="hidden" id="field1options" value="<? foreach($fields[0]['options'] as $option) print $option['name']."\n"; ?>" /></td>
                          <td align="center" valign="middle" class="options" id="optl1" onclick="opts(1)"><span id="field1optl">options</span></td>
                          <td valign="middle" width="10">&nbsp;
														<!--<span id="pos1">
															<select name="position1" id="position1">
																<?
																	for($ii = 0; $ii<sizeof($fields); $ii++){
																		$jj = $ii+1;
																		print '<option value="'.$jj.'"'.($jj==1 ? 'selected="selected"' : '').' >'.$jj.'</option>';
																	}
																?>
															</select>
															</span>-->													</td>
                          <!--<td valign="middle" class="options" align="center"><a href="editmsurvey.php?surveyId=<?= $surveyId?>&at=1">insert field</a></td>-->
                          <td valign="middle" width="10">&nbsp;</td>
                          <td><div id="field1opts"></div></td>
                          </tr>
			                          <tr>
			                               <td height="28">&nbsp;</td>
                          <td>&nbsp;</td>
                          <td valign="middle">2.</td>
                          <td colspan="2" valign="middle" nowrap="nowrap"><input name="field2" type="text" class="input" id="field2" value="<?= $fields[1]['name'] ?>" size="35" /></td>
                         <td align="center" valign="middle"><select name="type2" class="input" id="type2" style="width: 100px">
                             <? 
							    foreach($fiftypes as $f) { 
								    print '<option value="'.$f['value'].'" '.($fields[1]['type']==$f['value'] ? 'selected="selected"' : '').'>'.
									$f['label'].'</option>'; 
								} 
							?>
                          </select>
                           <input name="field2options" type="hidden" id="field2options" value="<? foreach($fields[1]['options'] as $option) print $option['name']."\n"; ?>" /></td>
                          <td align="center" valign="middle" class="options" id="optl2" onclick="opts(2)">options</td>
                          <td valign="middle" width="10">&nbsp;
														<!--<span id="pos1">
															<select name="position1" id="position1">
																<?
																	for($ii = 0; $ii<sizeof($fields); $ii++){
																		$jj = $ii+1;
																		print '<option value="'.$jj.'"'.($jj==1 ? 'selected="selected"' : '').' >'.$jj.'</option>';
																	}
																?>
															</select>
															</span>-->													</td>
                          <!--<td valign="middle" class="options" align="center"><a href="editmsurvey.php?surveyId=<?= $surveyId?>&at=2">insert field</a></td>-->
                          <td valign="middle" width="10">&nbsp;</td>
                          <td><div id="field2opts"></div></td>
                          </tr>
					      
						  <tr>
					      <td height="28">&nbsp;</td>
                          <td>&nbsp;</td>
                          <td valign="middle">3.</td>
                          <td colspan="2" valign="middle" nowrap="nowrap">
						     <input name="field3" type="text" class="input" id="field3" value="<?= $fields[2]['name'] ?>" size="35" /></td>
                         <td align="center" valign="middle">
						     <select name="type3" class="input" id="type3" style="width: 100px">
						        <? 
							    foreach($fiftypes as $f) { 
								    print '<option value="'.$f['value'].'" '.($fields[2]['type']==$f['value'] ? 'selected="selected"' : '').'>'.
									$f['label'].'</option>'; 
								} 
							?>
					         </select>
						     <input name="field3options" type="hidden" id="field3options" value="<? foreach($fields[2]['options'] as $option) print $option['name']."\n"; ?>"/></td>
                          <td align="center" valign="middle" class="options" id="optl3" onclick="opts(3)">options</td>
                          <td valign="middle" width="10">&nbsp;
														<!--<span id="pos1">
															<select name="position1" id="position1">
																<?
																	for($ii = 0; $ii<sizeof($fields); $ii++){
																		$jj = $ii+1;
																		print '<option value="'.$jj.'"'.($jj==3 ? 'selected="selected"' : '').' >'.$jj.'</option>';
																	}
																?>
															</select>
															</span>-->													</td>
                          <!--<td valign="middle" class="options" align="center"><a href="editmsurvey.php?surveyId=<?= $surveyId?>&at=3">insert field</a></td>-->
                          <td valign="middle" width="10">&nbsp;</td>
                          <td><div id="field3opts"></div></td>
                          </tr>
						 <? for($i=3; $i<count($fields); $i++) { $j=$i+1; ?>	
						  <tr>
					      <td height="28">&nbsp;</td>
                          <td>&nbsp;</td>
                          <td valign="middle"><?= $i+1 ?>
                             .</td>
                          <td colspan="2" valign="middle" nowrap="nowrap">
						     <input name="field<?= $j ?>" type="text" class="input" value="<?= $fields[$i]['name'] ?>" size="35" /></td>
                         <td align="center" valign="middle">
						     <select name="type<?= $j ?>" id="type<?= $j ?>" class="input" style="width: 100px">
						        <? 
							    foreach($fiftypes as $f) { 
								    print '<option value="'.$f['value'].'" '.($fields[$i]['type']==$f['value'] ? 
									'selected="selected"' : '').'>'.$f['label'].'</option>'; 
								} 
							?>
					         </select>
						     <input name="field<?= $j ?>options" type="hidden" id="field<?= $j ?>options" value="<? foreach($fields[$i]['options'] as $option) print $option['name']."\n"; ?>"/></td>
                          <td align="center" valign="middle"  class="options" id="optl<?= $j ?>" onclick="opts(<?= $j ?>)">options</td>
                          <td valign="middle" width="10">&nbsp;
														<!--<span id="pos1">
															<select name="position<?=$j?>" id="position<?=$j?>">
																<?
																	for($ii = 0; $ii<sizeof($fields); $ii++){
																		$jj = $ii+1;
																		print '<option value="'.$jj.'"'.($jj==$j ? 'selected="selected"' : '').' >'.$jj.'</option>';
																	}
																?>
															</select>
															</span>-->													</td>
                                                            
                          <!--<td valign="middle" <?=($j==(count($fields)))? '' : 'class="options"'?> align="center"><a href="editmsurvey.php?surveyId=<?= $surveyId?>&at=<?=$j?>"><?=($j==(count($fields)))? '' : 'insert field'?></a></td>-->
                          <td valign="middle" width="10">&nbsp;</td>
                          <td><div id="field<?= $j ?>opts"></div></td>
                          </tr>	
					   <? } $nlabel=$i; ?>	
					   <? for($i=1; $i<=$extra_flds; $i++) { $j = $i+3+(count($fields) > 3 ? count($fields)-3 : 0); $showfld = isset($_POST['field'.$j]) ? 1 : 0; ?>
						 <tr id="row<?= $i ?>" style="display: <?= $showfld ? (preg_match("/msie/i", $_SERVER['HTTP_USER_AGENT']) ? 'inline' : 'table-row') : 'none'?>">
						    <td <?= $showfld ? 'height="28" ' : ''?>id="fld<?= $i ?>ht"></td>
							<td></td>
							<td valign="middle" id="fld<?= $i ?>label"><?= $showfld ? $j : '' ?></td>
							<td colspan="2" valign="middle" id="fld<?= $i ?>field">
					           <? if($showfld) print '<input name="field'.$j.'" type="text" class="input" value="'.$_POST['field'.$j].'" size="35" />' ?>							</td>
							<td align="center" valign="middle" id="fld<?= $i ?>type">
						    <?
								  if($showfld) { 
		                            $t = '<select name="type'.$j.'" id="type'.$j.'" class="input" style="width: 100px">';
								    foreach($fiftypes as $f) { 
								      $t .= '<option value="'.$f['value'].'" '.($_POST['type'.$j]==$f['value'] ? 'selected="selected"' : '').'>'.
									  $f['label'].'</option>'; 
								    }
								    print $t.'</select>';
								  }	 
								  
							?>
								 <input name="field<?= $j ?>options" type="hidden" id="field<?= $j ?>options" value="<?= $_POST['field'.$j.'options'] ?>" /></td>
							<td align="center" valign="middle" class="options" id="optl<?= $j ?>" onclick="opts(<?= $j ?>)">
							<?= $showfld ? 'options' : '' ?></td>
							<td colspan="3" valign="top">&nbsp;&nbsp;
							<span id="pos<?= $j ?>"></span>							</td>
							<td><div id="field<?= $j ?>opts"></div></td>
							</tr> 
					    <? } ?>
					     <tr>
					      <td height="37"></td>
                          <td></td>
                          <td></td>
                          <td colspan="7" valign="middle">
					<input name="submit" type="submit" class="button" id="submit" value="Update Survey" />
                    <input name="mfbtn" type="button" class="button" id="mfbtn" value="+ More Fields" <?= !$extra_flds || isset($fields[$maxflds-1]) || isset($_POST['field'.$maxflds])? 'disabled="disabled"' : '' ?> onclick="moref_e(<?= $extra_flds ?>, <?= $nlabel ?>)"/>
                    <input name="lfbtn" type="button" <?= !count($_POST) ? 'disabled="disabled"' : '' ?> class="button" id="lfbtn" onclick="lessf_e(<?= $extra_flds ?>, <?= $nlabel ?>)" value="- Less Fields"/>
                           <input name="cancel" type="submit" class="button" id="cancel" value="Cancel" /></td>
                         <td></td>
                          </tr>
					     <tr>
							 <script>
							 var last_f = <?=$nlabel?>;
							 //insertButtons(<?= $extra_flds?>);
							 </script>
					        <td height="13"></td>
					        <td></td>
					        <td></td>
					        <td></td>
					        <td></td>
					        <td></td>
					        <td></td>
					        <td colspan="3"></td>
					        <td></td>
					        </tr>
					     
					     
					     <tr>
					        <td height="34"></td>
					        <td colspan="9" align="center" valign="middle" id="note"><span style="color: #CC0000">Please Note:</span> The Code for each field in the form must be unique </td>
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
					        <td colspan="3"></td>
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

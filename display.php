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
function get_excel_columns($match, $end='X') 
{
    $options = NULL;
	for($i=65; $i<=ord($end); $i++) {
	    $options .= '<option value="'.($i-64).'"'.($i-64==$match ? ' selected="selected"' : '').'>'.chr($i).'</option>';;
	}
	return $options;
}

function qnlist($total, $no) 
{
   for($i=1; $i<=$total; $i++) {
      $list .= '<option '.($i==$no ? 'selected="selected"' : '').'>'.$i.'</option>';
   }
   return $list;
}

function get_table_records($table, $match, $order='name') 
{
    $result = execute_query("SELECT * FROM $table ORDER BY $order");
	$options = NULL;
	while($row=mysql_fetch_assoc($result)) {
	    $options .= '<option value="'.$row['id'].'"'.($row['id']==$match ? ' selected="selected"' : '').'>'.$row[$order].'</option>';
	}
	return $options;
}

function get_group_options()
{
    $sql = 'SELECT * FROM initiative ORDER BY name';
	$result = execute_query($sql);
	$html = '';
	while($row=mysql_fetch_assoc($result)) {
	    $html .= '<input type=checkbox name="group_'.$row['id'].'" value="'.$row['id'].'" '.(isset($_POST['group_'.$row['id']]) ? 'checked=checked' : '').'/>
		'.$row['name'].'<br/>';
	}
	return $html;
}

function get_user_groups()
{
    $html = get_group_options();
	$html = '<table border=0 align=center class=main>
	   <tr>
	      <td height=40><u><strong>Add User(s) to Group(s)</strong></u></td>
	   </tr>
	   <tr>
	       <td>'.$html.'</td>
	   </tr>
	   <tr>
	      <td>
		      <input type=submit name=addtogroup value="Add To Group(s)" class=button />
		      <input type=button value="Cancel" class=button onclick="clearmsg()" />
		  </td>
	   </tr>
	</table>';
	$html = '<div><form method=post>'.$html.'</form></div>';
	return $html;
}

function get_group_list($groups)
{
    if(!strlen($groups)) {
	    return 'N/A';
	}
	$sql = "SELECT * FROM initiative WHERE id IN($groups) ORDER BY name";
	$result = execute_query($sql);
	$html = '<div>';
	while($row=mysql_fetch_assoc($result)) {
	    $html .= '<div>&bull;&nbsp;'.$row['name'].'</div>';
		
	}
	$html .= '</div>'; 
	return $html;
}

function make_html_form($surveyId) 
{
   $survey = get_table_record('msurvey', $surveyId);
   $html = '
   <table border="0" cellpadding="2" cellspacing="0">
   <tr>
      <td colspan="2" height="30" style="font-size: 13px"><u>'.truncate_str($survey['name'], 70).'</u></td>
   </tr>';
   $fields = unserialize($survey['fif']);
   foreach($fields as $field) 
   {
      if(preg_match("/checkbox/i", $field['type'])) 
	  {
                  $opts = '<table border="0" cellpadding="2" cellspacing="0">';
                  foreach($field['options'] as $option) {
                     $opts .= '
                         <tr>
                            <td><input type="checkbox" name="'.$field['code'].'_'.$option['value'].'" value="'.$option['value'].'" />&nbsp;</td>
                                <td>'.truncate_str($option['name'], 35).'</td>
                         </tr>';
                  }
                  $opts .= '</table>';
                  $html .= '
                  <tr>
                     <td valign="top" align="right"><br/>'.truncate_str($field['name'], 35).':&nbsp;</td>
                         <td>'.$opts.'</td>
                  </tr>';
          }
      elseif(preg_match("/radio/i", $field['type'])) 
	  {
                  $opts = '<table border="0" cellpadding="2" cellspacing="0">';
                  foreach($field['options'] as $option) 
				  {
                     $opts .= '
                         <tr>
                            <td><input type="radio" name="'.$field['code'].'" value="'.$option['value'].'" />&nbsp;</td>
                                <td>'.truncate_str($option['name'], 35).'</td>
                         </tr>';
                  }
                  $opts .= '</table>';
                  $html .= '
                  <tr>
                     <td valign="top" align="right"><br/>'.truncate_str($field['name'], 35).':&nbsp;&nbsp;</td>
                         <td>'.$opts.'</td>
                  </tr>';
          }
      elseif(preg_match("/menu/i", $field['type'])) {
                  $opts = '<select name="'.$field['code'].'" class="input" style="width: 250px">';
                  foreach($field['options'] as $option) {
                     $opts .= '<option>'.truncate_str($option['name'], 35).'</option>';
                  }
                  $opts .= '</select>';
                  $html .= '
                  <tr>
                     <td align="right">'.truncate_str($field['name'], 35).':&nbsp;&nbsp;</td>
                         <td>'.$opts.'</td>
                  </tr>';
          }
          else {
              $f = NULL;
                  if(preg_match("/image/i", $field['type'])) {
                     $f = '<input type="file" name="'.$field['code'].'" size="35" style="font-size: 11px" />' ;
                  }
                  elseif(preg_match("/data/i", $field['type']) || preg_match("/numeric/i", $field['type'])) {
                     $f = '<input type="text" name="'.$field['code'].'" class="input" size="35" />';
                  }
              $html .= '
                  <tr>
                    <td align="right">'.truncate_str($field['name'], 35).':&nbsp;&nbsp;</td>
                        <td>'.$f.'</td>
                  </tr>';
          }
   }
   $html .= '</table>';
   return $html;
}

function make_search_form($surveyId) 
{
   $survey = get_table_record('msurvey', $surveyId);
   $html = '
   <table border="0" cellpadding="2" cellspacing="0">
   <tr>
      <td colspan="2" height="40" style="font-size: 13px"><u>'.truncate_str($survey['name'], 70).'</u></td>
   </tr>
   <form method="post">
      <tr>
	      <td align="right" height="40">Phone ID:&nbsp;&nbsp;</td>
		  <td><input type="text" name="phoneId" class="input" size="35" value="'.$_POST['phoneId'].'" /></td>
	  </tr>';
   $fields = unserialize($survey['fif']);
   foreach($fields as $field) 
   {
      if(preg_match("/checkbox/i", $field['type'])) 
	  {
                  $opts = '<table border="0" cellpadding="2" cellspacing="0">';
                  foreach($field['options'] as $option) {
                     $opts .= '
                         <tr>
                            <td>
							<input type="checkbox" name="'.$field['code'].'_'.$option['value'].'" value="'.$option['name'].'" '.(isset($_POST[$field['code'].'_'.$option['value']]) ? 'checked="checked"' : '').'/>&nbsp;</td>
                                <td>'.truncate_str($option['name'], 35).'</td>
                         </tr>';
                  }
                  $opts .= '</table>';
                  $html .= '
                  <tr>
                     <td valign="top" align="right"><br/>'.truncate_str($field['name'], 35).':&nbsp;</td>
                         <td>'.$opts.'</td>
                  </tr>';
          }
      elseif(preg_match("/radio/i", $field['type'])) 
	  {
                  $opts = '<table border="0" cellpadding="2" cellspacing="0">';
                  foreach($field['options'] as $option) 
				  {
                     $opts .= '
                         <tr>
                            <td height="25">
							<input type="radio" name="'.$field['code'].'" value="'.$option['name'].'" 
							'.($_POST[$field['code']] == $option['name'] ? 'checked="checked"' : '').'/>&nbsp;</td>
                                <td>'.truncate_str($option['name'], 35).'</td>
                         </tr>';
                  }
                  $opts .= '</table>';
                  $html .= '
                  <tr>
                     <td valign="top" height="25" align="right"><br/>'.truncate_str($field['name'], 35).':&nbsp;&nbsp;</td>
                         <td>'.$opts.'</td>
                  </tr>';
          }
      elseif(preg_match("/menu/i", $field['type'])) { 
                  $opts = '<select name="'.$field['code'].'" class="input" style="width: 250px">
				  <option></option>';
                  foreach($field['options'] as $option) {
                     $opts .= '<option value="'.$option['name'].'" '.($_POST[$field['code']]==$option['name'] ? 
					 'selected="selected"' : '').'>'.truncate_str($option['name'], 35).'</option>';
                  }
                  $opts .= '</select>';
                  $html .= '
                  <tr>
                     <td height="25" align="right">'.truncate_str($field['name'], 35).':&nbsp;&nbsp;</td>
                         <td>'.$opts.'</td>
                  </tr>';
          }
          else {
              $f = NULL;
                  if(preg_match("/image/i", $field['type'])) {
                      //$f = '<input type="file" name="'.$field['code'].'" size="35" style="font-size: 11px" />' ;
					  $f = '<select name="'.$field['code'].'" style="width: 250px">
					      <option></option>
						  <option value="::UPLOADED::" '.($_POST[$field['code']]=='::UPLOADED::' ? 'selected="selected"' : '').'>UPLOADED</option>
						  <option value="::NOT_UPLOADED::" '.($_POST[$field['code']]=='::NOT_UPLOADED::' ? 'selected="selected"' : '').'>NOT UPLOADED</option>
					  </select>' ;
                  }
                  elseif(preg_match("/data/i", $field['type']) || preg_match("/numeric/i", $field['type'])) {
                     $f = '<input type="text" name="'.$field['code'].'" class="input" size="35" value="'.$_POST[$field['code']].'" />';
                  }
              $html .= '
                  <tr>
                    <td height="25" align="right">'.truncate_str($field['name'], 35).':&nbsp;&nbsp;</td>
                        <td>'.$f.'</td>
                  </tr>';
          }
   }
   $html .= '
     <tr>
	      <td height="40"></td>
		  <td>
		      <input type="submit" name="submit" class="button" value="Search Results" />
			  <input type="submit" name="cancel" class="button" value="Cancel" />
		  </td>
     </tr>
    </form>
   </table>';
   return $html;
}

function get_user_info() 
{
	if(!admin_user()) {
		$is_su = 0;
		$noaccess = "<font color='red'><b>You shall not be able to update this information.</b></font>";
	} else {
		$is_su = 1;
	}
     $sql = "SELECT user.*, DATE_FORMAT(dob, '%d') AS d, DATE_FORMAT(dob, '%m') AS m, DATE_FORMAT(dob, '%Y') AS y 
	         FROM user WHERE id=$_GET[_ui]";
	 if(!($result=mysql_query($sql))) {
	        return '{error: true}';
	 }
	 if(!mysql_num_rows($result)) {
	        //return '{error: true}';
			$misdn = get_misdn($_GET['misdn']);
			$sql = "INSERT INTO user(createdate, misdn) VALUES(NOW(), '$misdn') ON DUPLICATE KEY UPDATE misdn='$misdn'";
			if(!mysql_query($sql)) {
			    return '{error: true}';
			}
			$sql="SELECT * FROM user WHERE misdn='$misdn'";
			if(!($result=mysql_query($sql))) {
			     return '{error: true}';
			}
			if(!mysql_num_rows($result)) {
			      return '{error: true}';
			}
	 }
	 $user = mysql_fetch_assoc($result);
	 $_GET['_ui'] = $user['id'];
	 extract($user);
	 
	 $gender_options = '<select name="gender" class="input" id="gender" style="width: 274px">
						  <option></option>
                    	  <option value="Male" '.($gender=='Male' ? 'selected="selected"' : '').'>Male</option>
                    	  <option value="Female" '.($gender=='Female' ? 'selected="selected"' : '').'>Female</option>
						</select>';
	 
	 $dob_options = '<select name="d" class="input" id="d">
                        <option></option>'.days($d).'
           			</select>
                    <select name="m" class="input" id="m">
                    	<option></option>'.months($m).'
					</select>
                    <select name="y" class="input" id="y">
                    	'.years_flexible(1940, date('Y')-10, $y, 1).'
           			</select>';
	 
	 $_groups = preg_split('/,/', $groups);
	 foreach($_groups as $group) {
	     $_POST['group_'.$group] = $group;
	 }
	 $groupoptions = get_group_options();
	 $occupation_options = get_table_records('occupation', $occupationId, 'name');
	 $subcounty = get_table_record('subcounty', $subcountyId);
	 $districtId = $subcounty['districtId'];
	 
	 $html = '
	 <script type="text/javascript" src="basic.js"></script>
	 <table border=0 cellpadding=0 cellspacing=0 align=center>
	    <tr>
		    <td height=50 colspan=2 align="center">'.$noaccess.'</td>
		</tr>
	    <tr>
		    <td height=30 align=right>Names:&nbsp;&nbsp;</td>
			<td><input name="names" type="text" class="input" id="names" value="'.$names.'" size="40" /></td>
		</tr>	
	    <tr>
		    <td height=30 align=right>Phone Number:&nbsp;&nbsp;</td>
			<td><input name="misdn" type="text" class="input" id="misdn" value="'.$misdn.'" size="40" /></td>
		</tr>	
	    <tr>
		    <td height=30 align=right>Other Phone Number(s):&nbsp;&nbsp;</td>
			<td><input name="phones" type="text" class="input" id="phones" value="'.$phones.'" size="40" /></td>
		</tr>	
	    <tr>
		    <td height=30 align=right>Gender:&nbsp;&nbsp;</td>
			<td>'.$gender_options.'</td>
		</tr>	
	    <tr>
		    <td height=30 align=right>Date Of Birth:&nbsp;&nbsp;</td>
			<td>'.$dob_options.'</td>										
		</tr>	
	    <tr>
		    <td height=30 align=right>Occupation:&nbsp;&nbsp;</td>
			<td><select name="occupationId" class="input" id="occupationId" style="width: 274px">
                                  <option></option>
                                  '.$occupation_options.'
                                </select></td>
		</tr>	
	    <tr>
		    <td height=30 align=right>Location:&nbsp;</td>
			<td><input name="location" type="text" class="input" id="location" value="'.$location.'" size="40" /></td>
		</tr>	
	    <tr>
		    <td height=30 align=right>GPS Co-ordinates:&nbsp;</td>
			<td><input name="gpscordinates" type="text" class="input" id="gpscordinates" value="'.$gpscordinates.'" size="40" /></td>
		</tr>	
	    <tr>
		    <td height=30 align=right>District:&nbsp;</td>
			<td>
			<select name="districtId" class="input" id="districtId" style="width: 274px" onchange="set_sc()">
			<option></option>
			'.get_table_records('district', $districtId, 'name').'
			</select>			
			</td>
		</tr>	
	    <tr>
		    <td height=30 align=right>Subcounty:&nbsp;</td>
			<td id="subc">
                <select name="subcountyId" class="input" id="subcountyId" style="width:274px"></select>
			</td>
		</tr>							
	    <tr>
		    <td height=30 align=right>Group(s):&nbsp;&nbsp;</td>
			<td>'.$groupoptions.'</td>
		</tr>		
	    <tr>
		    <td height=30 align=right>Device Information:&nbsp;&nbsp;</td>
			<td><input name="deviceInfo" type="text" class="input" id="deviceInfo" value="'.$deviceInfo.'" size="40" /></td>
		</tr>	
	    <tr>
		    <td height=30 align=right>Notes:&nbsp;&nbsp;</td>
			<td>
			     <textarea name="notes" cols="40" class="input" id="notes" style="width: 270px; height: 50px">'.$notes.'</textarea>
			</td>
		</tr>	
	    <tr>
		    <td height=40 align=right></td>
			<td>
			    '.($is_su ? '<input name="submit" type="submit" class="button" id="submit" value="Update User" />' : '').'
                <input name="cancel" type="button" class="button" id="cancel" value="Close" onclick="clearmsg()"/>
			</td>
		</tr>	
		<tr>
		    <td colspan=2 height=10>
			    <input type=hidden name="u" value="'.$_GET['u'].'" />
				<input type=hidden name="_ui" value="'.$_GET['_ui'].'" />
			</td>
		</tr>																				
	 </table>';
	 
	 $html = 
	 '<fieldset>
         <legend>Edit User Information</legend>
	     <form method="post" action="editinfo.php">'.$html.'</form>
	 </fieldset>'; 
	 
	 //$fh=fopen('/tmp/debug.log', 'w'); fwrite($fh, print_r($user, true));
	 return '<div class="main">'.$html.'<script>'.get_district_js($user).';set_sc();</script></div>';
}

function get_msurvey_result() 
{
    $resultId = $_GET['i'];
	$sql = "SELECT * FROM mresult WHERE id='$resultId'";
	if(!($result=mysql_query($sql))) {
	    return '{error: true}';
	}
	if(!mysql_num_rows($result)) {
	    return '{error: true}';
	}
	$row=mysql_fetch_assoc($result);
	$form = unserialize($row['form']);
	$html = '<table width="100%" cellpadding=0 cellspacing=0 id=result>
	<tr>
	    <td colspan=2 height=40 valign=top align=right style=border:none>
		    <input name="cancel" type="button" class="button" id="cancel" value="Cancel" onclick="clearmsg()"/>
		</td>
	</tr>';
	$color = '#EEEEEE'; 
	$i=1;
	foreach($form['data'] as $f) {
	    $color = $i++%2 ? '#EEEEEE' : '#ffffff';
		if(!preg_match("/\s+/", $f['value'])) {
		    $value = strlen($f['value']) > 35 ? '<span style="cursor:pointer" title="'.$f['value'].'">'.truncate_str($f['value'], 35).'</span>' :
			 $f['value'];
		}
		else {
		    $value = (strlen($f['value']) ? preg_replace("/\n+/", "<br/>", $f['value']) : 'N/A');
		}
		$html .= '
		<tr>
		    <td height="27">
			  <div id="label" style="width:300px;padding-right:5px">'.truncate_str($f['field'], 200).'</div></td>
			<td>
			   <div id="field" style="width:300px;">'.$value.'</div>
			 </td>
	    </tr>';
    }
	if(count($form['uploads'])) {
	   $html .= '
	   <tr id=title>
	       <td height=30 colspan=2 align=center>UPLOADS</td>
	   </tr>';
	}
	foreach($form['uploads'] as $f) 
	{
	    if(strlen($f['value']) && file_exists(MOBILE_UPLOADS_DIR.'/'.$f['value'])) 
		{
	        $html .= '
	        <tr>
	        <td height="27" id="label" valign="top"><br/>'.truncate_str($f['field'], 30).'<br/>(Click To Open):</td>
				   <td>
				      <div style="width:300px; height:130px; padding-top:9px; overflow:hidden; cursor:pointer" title="Click to open"
					  onclick="window.open(\''.$GLOBALS['appurl'].'/mobile/uploads/'.$f['value'].'\')">
				         <img src="mobile/uploads/'.$f['value'].'" />
					  </div>
					  <a href="?surveyId='.$row['surveyId'].'&resultId='.$row['id'].'&start='.$_GET['start'].'&file='.urlencode($f['value']).
					  '&delete=TRUE"
					  style="font-size:11px;color:#ff0000" 
					  onclick="return confirm(\'Are you sure you want to delete this file?\')">[Delete]</a>
				   </td>
	        </tr>';
		}
		else {
			$html .= '
			<tr>
			   <td height="27" id="label" valign="top" style="cursor: pointer" title="'.$f['field'].'">'.truncate_str($f['field'], 50).':</td>
			   <td id="field">'.(strlen($f['value']) ? '<span style=color:#ff0000>ERR: FILE NOT FOUND</span>' : 'NOT UPLOADED').'</td>
			</tr>';
		}	
	}
	$html .= '
	<tr>
	    <td colspan=2 height=40 valign=bottom align=right style=border:none>
		    <input name="cancel" type="button" class="button" id="cancel" value="Cancel" onclick="clearmsg()"/>
		</td>
	</tr>
    </table>';
	
	$html = '<div>'.$html.'</div>';
	return $html;
	//return print_r($form, true);
}

function nf_js($surveyId) 
{
    global $_errors;
	$survey = get_table_record('msurvey', $surveyId);
	$fields = unserialize($survey['fif']);
	
	$js = "var fields =[ ";
	foreach($fields as $field) { 
	     $js .= "{name: \"$field[name]\", type: \"$field[type]\", code: \"$field[code]\"}, ";
	}
	$js = preg_replace("/,\s+$/", "", $js);
	$js .= "];\n";
	
	$options = preg_split("/\n+/", rtrim($_POST['options']));
	$_options = array();
	foreach($options as $option) {
	    $option = trim($option);
		if(!strlen($option)) continue;
		$_options[] = $option;
	}  
	$options = implode("|", $_options);
	$_js = "var _form={";
	$_js .= "position:\"$_POST[position]\", afterfield:\"$_POST[afterfield]\", name:\"$_POST[name]\", type:\"$_POST[type]\", ";
	$_js .= "options:\"$options\", errors:\"$_errors\"};\n";
	return $js.$_js;
}   

function get_district_js($form=NULL)
{
    if(is_null($form)) {
	    $form = $_POST;
	}
	$sql = "SELECT * FROM district ORDER BY name";
	if(!($result=mysql_query($sql))) {
	    die(mysql_error());
	}		
	$districts = array();
	while($row=mysql_fetch_assoc($result)) 
	{
	     $result2 = execute_query("SELECT * FROM subcounty WHERE districtId=$row[id] ORDER BY name");
		 $subcounties = array();
		 while($row2=mysql_fetch_assoc($result2)) 
		 {
		     $subcounties[] = "{id:$row2[id], name:\"$row2[name]\", selected:".($form['subcountyId']==$row2['id'] ? 'true' : 'false')."}";
		 }
		 $subcounties = '['.implode(', ', $subcounties).']';
		 $districts[] = "{id:$row[id], name:\"$row[name]\", subcounties:$subcounties, selected:".
		 ($_POST['districtId']==$row['id'] ? 'true' : 'false')."}";
	}
	$js = "var districts = [\n".implode(",\n", $districts)."\n];";
	return $js;
} 

function get_sub_counties($districtId) 
{
    $sql = "SELECT * FROM subcounty WHERE districtId='$districtId'";
	if(!($result=mysql_query($sql))) {
	     die("ERROR in: get_sub_counties($districtId)");
	}
	if(!mysql_num_rows($result)) {
	    return 'N/A';
	}
	$html = '<div><ul>';
	while($row=mysql_fetch_assoc($result)) {
	    $html .= '<li>'.$row['name'].'</li>';
	} 
} 

function get_googlesms_import_form() 
{
    extract($_POST);
	
	$html = '
	<table border=0 cellpadding=0 cellspacing=0 width=100% align=center>
	   </tr>
	       <td colspan=2 height=40></td>
	   </tr>
	   <tr>
	       <td width=180 height=30 align=right><img src=images/excel.jpg style=cursor:pointer title="Browse Excel File">&nbsp;</td>
		   <td><input type="file" name="file" size="40" style="font-size:11px"/></td>
	   </tr>
	   <tr>
	       <td height=30 align=right>Date/Time:&nbsp;&nbsp;</td>
		   <td>
		      <select name="date_col" class="input" style="width:285px">
		         <option value="0"></option>
			     '.get_excel_columns($date_col).'
			  </select>
		   </td>
	   </tr>	  
	   <tr>
	       <td height=30 align=right>MISDN:&nbsp;&nbsp;</td>
		   <td>
		      <select name="misdn_col" class="input" style="width:285px">
		         <option value="0"></option>
			     '.get_excel_columns($misdn_col).'
			  </select>
		   </td>
	   </tr>	
	   <tr>
	       <td height=30 align=right>Site:&nbsp;&nbsp;</td>
		   <td>
		      <select name="site_col" class="input" style="width:285px">
		         <option value="0"></option>
			     '.get_excel_columns($site_col).'
			  </select>
		   </td>
	   </tr>	
	   <tr>
	       <td height=30 align=right>Location:&nbsp;&nbsp;</td>
		   <td>
		      <select name="location_col" class="input" style="width:285px">
		         <option value="0"></option>
			     '.get_excel_columns($location_col).'
			  </select>
		   </td>
	   </tr>
	   <tr>
	       <td height=30 align=right>Start of Records:&nbsp;&nbsp;</td>
		   <td><input name="start" type="text" class="input" id="start" value="'.$start.'" size="41" maxlength="2" />
		   </td>
	   </tr>	   
	   <tr>
	       <td height=30></td>
		   <td><input type=checkbox name=skip value=1 '.(isset($skip) ? 'checked="checked"' : '').'/>Skip Records with Invalid Numbers</td>
	   </tr>		   
	   <tr>
	       <td height=40></td>
		   <td>
		       <input type=submit class=button name=import value="Import Logs" />
		   </td>
	   </tr>
	   <tr>
	       <td colspan=2 height=30></td>
	   </tr>
	</table>';
	
	return $html;
}

function get_healthivr_import_form() 
{
    extract($_POST);
	
	$html = '
	<table border=0 cellpadding=0 cellspacing=0 width=100% align=center>
	   </tr>
	       <td colspan=2 height=40></td>
	   </tr>
	   <tr>
	       <td width=180 height=30 align=right><img src=images/excel.jpg style=cursor:pointer title="Browse Excel File">&nbsp;</td>
		   <td><input type="file" name="file" size="40" style="font-size:11px"/></td>
	   </tr>
	   <tr>
	       <td height=30 align=right>Date:&nbsp;&nbsp;</td>
		   <td>
		      <select name="date_col" class="input" style="width:285px">
		         <option value="0"></option>
			     '.get_excel_columns($date_col).'
			  </select>
		   </td>
	   </tr>	
	   <tr>
	       <td height=30 align=right>Time:&nbsp;&nbsp;</td>
		   <td>
		      <select name="time_col" class="input" style="width:285px">
		         <option value="0"></option>
			     '.get_excel_columns($time_col).'
			  </select>
		   </td>
	   </tr>		     
	   <tr>
	       <td height=30 align=right>MISDN:&nbsp;&nbsp;</td>
		   <td>
		      <select name="misdn_col" class="input" style="width:285px">
		         <option value="0"></option>
			     '.get_excel_columns($misdn_col).'
			  </select>
		   </td>
	   </tr>	
	   <tr>
	       <td height=30 align=right>Duration:&nbsp;&nbsp;</td>
		   <td>
		      <select name="duration_col" class="input" style="width:285px">
		         <option value="0"></option>
			     '.get_excel_columns($duration_col).'
			  </select>
		   </td>
	   </tr>	
	   <tr>
	       <td height=30 align=right>Start of Records:&nbsp;&nbsp;</td>
		   <td><input name="start" type="text" class="input" id="start" value="'.$start.'" size="41" maxlength="2" />
		   </td>
	   </tr>		   
	   <tr>
	       <td height=30></td>
		   <td><input type=checkbox name=skip value=1 '.(isset($skip) ? 'checked="checked"' : '').'/>Skip Records with Invalid Numbers</td>
	   </tr>		   
	   <tr>
	       <td height=40></td>
		   <td>
		       <input type=submit class=button name=import value="Import Logs" />
		   </td>
	   </tr>
	   <tr>
	       <td colspan=2 height=30></td>
	   </tr>
	</table>';
	
	return $html;
}

function get_formSurvey_import_form() 
{
    extract($_POST);
	
	$html = '
	<table border=0 cellpadding=0 cellspacing=0 width=100% align=center>
	   </tr>
	       <td colspan=2 height=40></td>
	   </tr>
	   <tr>
	       <td width=180 height=30 align=right><img src=images/excel.jpg style=cursor:pointer title="Browse Excel File">&nbsp;</td>
		   <td><input type="file" name="file" size="40" style="font-size:11px"/></td>
	   </tr>
	   <tr>
	       <td height=30 align=right>Date:&nbsp;&nbsp;</td>
		   <td>
		      <select name="date_col" class="input" style="width:285px">
		         <option value="0"></option>
			     '.get_excel_columns($date_col).'
			  </select>
		   </td>		     
	   <tr>
	       <td height=30 align=right>MSISDN:&nbsp;&nbsp;</td>
		   <td>
		      <select name="misdn_col" class="input" style="width:285px">
		         <option value="0"></option>
			     '.get_excel_columns($misdn_col).'
			  </select>
		   </td>
	   </tr>
		 <tr>
	       <td height=30 align=right>Title Row:&nbsp;&nbsp;</td>
		   <td><input name="titleRow" type="text" class="input" id="titleRow" value="'.(strlen($_POST['titleRow']) ? $_POST['titleRow'] : 1).'" size="41" maxlength="2" />
		   </td>
	   </tr>
	   <tr>
	       <td height=30 align=right>Start of Records:&nbsp;&nbsp;</td>
		   <td><input name="start" type="text" class="input" id="start" value="'.$start.'" size="41" maxlength="2" />
		   </td>
	   </tr>		   
	   <tr>
	       <td height=30></td>
		   <td><input type=checkbox name=skip value=1 '.(isset($skip) ? 'checked="checked"' : '').'/>Skip Records with Invalid Numbers</td>
	   </tr>		   
	   <tr>
	       <td height=40></td>
		   <td>
		       <input type=submit class=button name=import value="Import Logs" />
		   </td>
	   </tr>
	   <tr>
	       <td colspan=2 height=30></td>
	   </tr>
	</table>';
	
	return $html;
}


?>

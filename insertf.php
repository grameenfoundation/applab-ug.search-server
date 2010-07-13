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
if(!isset($survey) || !is_array($survey) || !function_exists('dbconnect')) {
     exit;
}
if(isset($_POST['_sinsert'])) {
     global $_errors;
	 $_errors = NULL;
	 $fields = unserialize($survey['fif']);
	 extract($_POST);
	 
	 if(!strlen($position)) {
	      $_errors = 'Select the position where to insert the field';
	 }
	 if(!isset($_errors) && $position==3 && !strlen($afterfield)) {
	      $_errors = 'Select after which field to insert the new field';
	 }  
	 if(!isset($_errors) && !strlen($name)) {
	       $_errors = 'New Field not valid';
	 }
	 if(!isset($_errors) && strlen($name)) {
	       $name = strtolower(trim($name));
		   foreach($fields as $f) {
		       if( strcmp(strtolower($f['name']), $name) == 0) {
			       $_errors = 'This field already exists';
				   break;
			   }
		   }
	 }
	 if(!isset($errors) && strlen($options)) {
	     $_options = array();
		 if(in_array($type, array('Radio', 'Menu', 'Checkbox'))) 
		 {
		      $list = preg_split("/\n+/", rtrim($options));
			  $k = 1;
			  foreach($list as $item) {
			       if(!strlen($item)) {
				        continue;
				   }
				   if(preg_match("/FLD_[0-9]+/i", $item)) {
				       $_errors = "Invalid field option: $item";
					   break;
				   }
				   $_options[] = array('name'=>rtrim($item), 'value'=>$k);
				   $k++;
			  }
		 }
		 if(!count($_options)) {
		     $_errors = 'No options specified';
		 }
	 }
	 if(!isset($_errors)) {
	     $code = 'f'.substr(md5($name), 0, 16);
		 if(!isset($_options)) {
		     $_options = NULL;
		 }	 
		 $field = array('name' => $name, 'type' => $type, 'code' => $code, 'options'=>$_options); 
		 
		 switch($position) 
		 {
		      case 1:
			      $_fields = array($field);
				  $fields = array_merge($_fields, $fields);
				  break;
			  
			  case 2:
			      $fields[] = $field;
				  break;
				  
			  case 3:
			      $_fields = array();
				  for($i=0; $i<count($fields); $i++) 
				  {
				        $_fields[] = $fields[$i];
						if($fields[$i]['code'] == $afterfield) 
						{
						     $_fields[] = $field;
							 for($j=$i+1; $j<count($fields); $j++) {
							      $_fields[] = $fields[$j];
							 }
							 break;
						}
				  }
				  $fields = $_fields;
			      break;	  
			  
			  default: break;
		 }
		 $fif = mysql_real_escape_string(serialize($fields));
		 $sql = "UPDATE msurvey SET fif='$fif' WHERE id='$survey[id]'";
		 execute_update($sql);
		 
		 $log = "Inserted New field in Mobile Survey \"$survey[name]\". Field Details (Name: $name, Type: $type)";
		 logaction($log);
		 rewrite_fif($survey['id']);
		 header("Location: msurvey.php?surveyId=$survey[id]");
		 exit();
	 }
}

?>
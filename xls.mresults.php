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
include("constants.php");
include("excel-write.php");
include("functions.php");
include("sessions.php");

session_start();

dbconnect();
validate_session(); 

extract($_GET);

if(!preg_match("/^[0-9]+$/", $surveyId)) { 
   die('ERROR: Invalid survey');
}

$debug = isset($debug) ? 1 : 0;
if($debug) {
    header('Content-type: text/plain');
}

$sql = "SELECT msurvey.*, DATE_FORMAT(createdate, '%d/%m/%Y %r') AS createdate, updated FROM msurvey WHERE id=$surveyId";
$result = execute_query($sql);
if(!mysql_num_rows($result)) {
   goto('msurveys.php');
}
$survey = mysql_fetch_assoc($result); 
$fields = unserialize($survey['fif']);

if(!isset($_SESSION['exportq'])) {
     die('ERROR: No results to download');
}
$sql = $_SESSION['exportq'];

if(isset($week)) {
    if(!preg_match('/^[0-9]+$/', $week)) {
	     die('ERROR: Invalid week');
	}
	if(!isset($_SESSION['filter_categories'])) {
	    die('ERROR: Invalid filter');
	}
	$category = $_SESSION['filter_categories'][$week];
	$sql .= " AND date BETWEEN '$category[from]' AND '$category[to]'";
	$xlslabel = $category['xlslabel'];
}

// Free up the session to avoid locking up HTTPD processes
session_write_close();

$sql .= ' ORDER BY date DESC';
$result = execute_query($sql);
$total = mysql_num_rows($result);
if(!($result=mysql_query($sql))) {
     die('ERROR: '.mysql_error());
}
$filename = date('YmdHis').'-survey-results.csv';
$filepath = reportDir.'/'.$filename;

$filename_xls = date('YmdHis').'-survey-results.xls';
$filepath_xls = reportDir.'/'.$filename_xls;

if(!($fh = fopen($filepath, 'w'))) {
   print 'Can not open file: '.$filepath;
   exit;
}    
$title = "Results of Mobile Survey: $survey[name] - ";
if(isset($xlslabel)) {
    $title .= "($xlslabel) ";
}
$title .= "Total Results Exported: $total";
$total_result_set = mysql_num_rows(execute_query('SELECT * FROM mresult WHERE surveyId='.$surveyId));
if($total_result_set > $total) {
   $excluded_total = $total_result_set - $total;
   $title .= " ($excluded_total Result(s) have been excluded)";
}
$title .= ", Survey created on $survey[createdate]";
fwrite($fh, "\n\"$title\"\n\n");

xls_add_titletext($xls_resource, $title);

$i = 1;
if(isset($detailed)) {
while($row=mysql_fetch_assoc($result)) {
   fwrite($fh, "RESULT $i.\n");
    $form = unserialize($row['form']);
	$data = $form['data'];
	$phoneId = strlen($row['phoneId']) ? get_phone_display_label($row['phoneId'], 100) : 'N/A';
	$title = "";
	$fields = "";
	foreach($data as $f) {
		$title .= "\"$f[field]\",";
		$fields .= strlen($f['value']) ? "\"$f[value]\"," : '"N/A",';
   }  
   /* the Images */
   $uploads = $form['uploads'];
   foreach($uploads as $f) { 
	  $title .= "\"$f[field]\",";
	  if(strlen($f['value']) && file_exists(MOBILE_UPLOADS_DIR.'/'.$f['value'])) {
	     $fields .= "UPLOADED,";
	  }	
	  else {
	     $fields .= strlen($f['value']) ? 'ERR: NOT FOUND' : 'NOT UPLOADED';
	  }	   	    
   }
   $title = "Phone ID,Date/Time,".preg_replace("/,$/", "", $title);
   $fields = "=\"$phoneId\",\"$row[time]\",".preg_replace("/,$/", "", $fields);
   fwrite($fh, "$title\n$fields\n\n");
   $i++;
}
}
else {
   $row = mysql_fetch_assoc($result); 
   /* get column names */
   $form = unserialize($row['form']);
   $data = $form['data'];
   $phoneId = strlen($row['phoneId']) ? get_phone_display_label($row['phoneId'], 100) : 'N/A';
	 $location = get_location_from_misdn($row['phoneId']);
	 $uniqeid = $location."_".$row['phoneId']."_".$row['id'];
   
   $columns = array();
	 
   $heading = "Result,Unique ID,User/Phone ID,Date/Time,";
   xls_add_title_field($xls_resource, "Result");
	 xls_add_title_field($xls_resource, "Unique ID");
   xls_add_title_field($xls_resource, "User/Phone ID");
   xls_add_title_field($xls_resource, "Date/Time");
   $record = "$i,\"$location\",=\"$phoneId\",\"$row[time]\",";
   xls_add_row_field($xls_resource, "$i");
	 xls_add_row_field($xls_resource, $uniqeid);
   xls_add_row_field($xls_resource, $phoneId);
   xls_add_row_field($xls_resource, $row[time]);
   foreach($data as $f) {
		$f['value'] = gps_cleanup($f['field'], $f['value']); //clean up fields with keywords; gps, elevation, coordinate
	  $chars_out = array ("–","’");
	  $chars_in = array ("-","'");
	  $f[value] = str_replace($chars_out, $chars_in, $f[value]);
	  $f[field] = str_replace($chars_out, $chars_in, $f[field]);
      $columns[] = $f['field'];
	  $code_columns[] = $f['code'];
	  xls_add_title_field($xls_resource, $f[field]);
	  $heading .= "\"$f[field]\",";
	  $record .= strlen($f['value']) ? "=\"$f[value]\"," : '"N/A",';
	  xls_add_row_field($xls_resource, strlen($f['value']) ? "$f[value]" : 'N/A');
   } 
   if($debug) {
       print_r($columns);
   }
   /* The images */
   $uploads = $form['uploads'];
   $images = array(); 
   foreach($uploads as $f) {  
      $images[] = $f['field']; 
	  $heading .= "\"$f[field]\",";
	  xls_add_title_field($xls_resource, $f[field]);
	  if(strlen($f['value']) && file_exists(MOBILE_UPLOADS_DIR.'/'.$f['value'])) {
	    $record .= "\"UPLOADED\",";
		xls_add_row_field($xls_resource, "UPLOADED");
	  }
	  elseif(strlen($f['value'])) {
	    $record .= "\"ERR: NOT FOUND\",";
		xls_add_row_field($xls_resource, "ERR: NOT FOUND");
	  }
	  else {
	    $record .= "\"NOT UPLOADED\",";
		xls_add_row_field($xls_resource, "NOT UPLOADED");
	  }
   } 
   $heading = preg_replace("/,$/", "", $heading)."\n";
   $record = preg_replace("/,$/", "", $record)."\n";
   $chars_out = array ("–","’");
   $chars_in = array ("-","'");
   fwrite($fh, $heading);
   fwrite($fh, str_replace($chars_out, $chars_in, $record));
   xls_new_row($xls_resource);
   $i++;
   while($row=mysql_fetch_assoc($result)) { 
      $form = unserialize($row['form']);
	  $data = $form['data'];
	  if($debug)
	      print_r($data);
	  
		$location = get_location_from_misdn($row['phoneId']);
		$uniqeid = $location."_".$row['phoneId']."_".$row['id'];
			
	  $record = "$i,\"$uniqueid\",=\"".get_phone_display_label($row['phoneId'], 100)."\",\"$row[time]\","; 
		
	  xls_add_row_field($xls_resource, "$i");
		xls_add_row_field($xls_resource, $uniqeid);
	  xls_add_row_field($xls_resource, get_phone_display_label($row['phoneId'], 100));
	  xls_add_row_field($xls_resource, $row[time]);
	  $gender_of_house_hold = true;
	  $villa = true;
		 $elevation = true;
		 $cood_1 = true;
		 $cood_2 = true;
	  //foreach($columns as $column) {
		 foreach($code_columns as $column) {
	     $cfound = false;
		 
			 foreach($data as $f) {
				 $f['value'] = gps_cleanup($f['field'], $f['value']); //clean up fields with keywords; gps, elevation, coordinate
				 
				 if($debug)
							print "matching \"$column\" with \"$f[field]\"..\n";
				 
							//if(preg_match("/^($column)$/i", $f['field'])) {
							///if(strcasecmp($column, $f['field'])==0) {
				if(strcasecmp($column, $f['code'])==0) {
					if($debug) 
						 print "[matched]\n";
					 
					$chars_out = array ("–","’");
					$chars_in = array ("-","'");
					$f[value] = str_replace($chars_out, $chars_in, $f[value]);
					$record .= strlen($f['value']) ? "=\"$f[value]\"," : '"N/A",';
					xls_add_row_field($xls_resource, strlen($f['value']) ? "$f[value]" : 'N/A');
					$cfound = true;
					break;
				 }
		   else if((strcasecmp("2. Gender of household head", $f['field'])==0) && ($gender_of_house_hold==true)) {
			   $gender_of_house_hold = false;
		      if($debug) 
			       print "[matched]\n";
				   
			  $chars_out = array ("–","’");
			  $chars_in = array ("-","'");
			  $f[value] = str_replace($chars_out, $chars_in, $f[value]);
			  $record .= strlen($f['value']) ? "=\"$f[value]\"," : '"N/A",';
			  xls_add_row_field($xls_resource, strlen($f['value']) ? "$f[value]" : 'N/A');
			  $cfound = true;
			  break;
		   }else if((strcasecmp("4. Village Name", $f['field'])==0) && ($villa==true)) {
			   $villa = false;
		      if($debug) 
			       print "[matched]\n";
				   
			  $chars_out = array ("–","’");
			  $chars_in = array ("-","'");
			  $f[value] = str_replace($chars_out, $chars_in, $f[value]);

			  $record .= strlen($f['value']) ? "=\"$f[value]\"," : '"N/A",';
			  xls_add_row_field($xls_resource, strlen($f['value']) ? "$f[value]" : 'N/A');
			  $cfound = true;
			  break;
		   }
		   else if((strcasecmp("Elevation", $f['field'])==0) && ($elevation == true)) {
			  $elevation = false;
		      if($debug) 
			       print "[matched]\n";
				   
			  $chars_out = array ("–","’");
			  $chars_in = array ("-","'");
			  $f[value] = str_replace($chars_out, $chars_in, $f[value]);
			  $record .= strlen($f['value']) ? "=\"$f[value]\"," : '"N/A",';
			  xls_add_row_field($xls_resource, strlen($f['value']) ? "$f[value]" : 'N/A');
			  $cfound = true;
			  break;
		   }
		   else if((strcasecmp("Coordinate 1", $f['field'])==0) && ($cood_1 == true)) {
			  $cood_1 = false;
		      if($debug) 
			       print "[matched]\n";
				   
			  $chars_out = array ("–","’");
			  $chars_in = array ("-","'");
			  $f[value] = str_replace($chars_out, $chars_in, $f[value]);
			  $record .= strlen($f['value']) ? "=\"$f[value]\"," : '"N/A",';
			  xls_add_row_field($xls_resource, strlen($f['value']) ? "$f[value]" : 'N/A');
			  $cfound = true;
			  break;
		   }
		   else if((strcasecmp("Coordinate 2", $f['field'])==0) && ($cood_2 ==true)) {
			   $cood_2 = false;
		      if($debug) 
			       print "[matched]\n";
				   
			  $chars_out = array ("–","’");
			  $chars_in = array ("-","'");
			  $f[value] = str_replace($chars_out, $chars_in, $f[value]);
			  $record .= strlen($f['value']) ? "=\"$f[value]\"," : '"N/A",';
			  xls_add_row_field($xls_resource, strlen($f['value']) ? "$f[value]" : 'N/A');
			  $cfound = true;
			  break;
		   }
		   elseif($debug) 
				print "[not matched]\n"; 
		 }  
		 if(!$cfound) { 
			xls_add_row_field($xls_resource, "-");
			$record .= "-,";  
		 }
	  }
	  /* the Images */
	  $uploads = $form['uploads'];
	  foreach($images as $column) {
	     $cfound = false;
		 foreach($uploads as $f) {
		    if(preg_match("/^($column)$/i", $f['field'])) {
		       if(strlen($f['value']) && file_exists(MOBILE_UPLOADS_DIR.'/'.$f['value'])) {
		          $record .= "\"UPLOADED\",";
				  xls_add_row_field($xls_resource, "UPLOADED");
		        }
		        elseif(strlen($f['value'])) {
		 	       $record .= "\"ERR: NOT FOUND\",";
				   xls_add_row_field($xls_resource, "ERR: NOT FOUND");
				}
				else {
				   xls_add_row_field($xls_resource, "NOT UPLOADED");
				   $record .= "\"NOT UPLOADED\",";
				}
				$cfound = true;
				break;
			}
		 }
		 if(!$cfound) { 
			xls_add_row_field($xls_resource, "-");
		    $record .= "-,";
		 }
	  }
	  $record = preg_replace("/,$/", "", $record)."\n";
	  xls_new_row($xls_resource);
	  fwrite($fh, str_replace($chars_out, $chars_in, $record));
	  $i++;
   }
}
fclose($fh);

if($debug) {
     unlink($filepath);
	 exit();
}

$browser = $_SERVER['HTTP_USER_AGENT'];
if(preg_match("/msie/i", $browser)) {
   //header("Location: reports/$filename");
   //exit();
}

if(!xls_write_file($filepath_xls, xls_get_array($xls_resource))) {
	header("Content-type: text/x-csv");
	header("Content-Disposition: attachment; filename=\"$filename\"");
	header("Content-Transfer-Encoding: binary");
	header("Content-Length: ".filesize($filepath));

	if(!readfile($filepath)) {
	  print "Error reading file: $filepath";
	  exit;
	}
} else {
	unlink($filepath);
	header ("Content-type: application/x-msexcel");
	header ("Content-Disposition: attachment; filename=\"$filename_xls\"" );
	header("Content-Transfer-Encoding: binary");
	header("Content-Length: ".filesize($filepath_xls));

    if(!readfile($filepath_xls)) {
       die("Error reading file: $filepath_xls");
    }

    exit();
}
?>

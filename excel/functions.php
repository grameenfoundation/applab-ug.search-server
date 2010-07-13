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
require 'excel/reader.php';

function read_phone_numbers($type='quiz') 
{ 
   /*uploaded file */
   if(strlen($_FILES['file']['name'])) {
	  if(preg_match("/\.xls$/", $_FILES['file']['name'])) {
         $uploaded_numbers = read_excel_upload($_FILES['file']['tmp_name']);
	  }
	  else {
	     /* CSV or text file */
		 $uploaded_numbers = read_csv_upload($_FILES['file']['tmp_name']);
	  }
	  if(!count($uploaded_numbers)) {
		  return 0;
	  }	  
   } 
   /* input numbers */
   $input_numbers = array(); 
   $numbers = $_POST['numbers'];
   $numbers = preg_replace("/\n+/", ",", rtrim($numbers));
   $numbers = preg_replace("/\s+/", "", $numbers);
   $numbers = preg_replace("/-+/", "", $numbers); 
   if(strlen($numbers)) {
      $nos = preg_split("/,/", $numbers); 
	  foreach($nos as $number) { 
		 if(!strlen($number)) {
		    continue;
		 }
		 if(strlen($number) && !preg_match("/^[0-9]{8,}$/", $number)) {  
		    $errors .= "Input Phone number not valid: $number<br/>";
			break;
		 }
		 $input_numbers[] = $number; 
	  } 
   } 
   /* imported numbers from quiz */
   $imported_quiz = array();
   $importlist = $_POST['qimportlist']; 
   if(strlen($importlist)) {
       $ids = preg_split("/,/", trim($importlist)); 
	   foreach($ids as $cId) {
	      if(!preg_match("/^[0-9]+$/", $cId)) {
		      continue;
		  }	 
		  $sql = ($type=='quiz') ? "SELECT * FROM quizphoneno WHERE quizId=$cId" : "SELECT * FROM surveyno WHERE surveyId=$cId"; 
		  $result = execute_query($sql);
		  while($row=mysql_fetch_assoc($result)) {
		      $imported_quiz[] = $row['phone'];
		  }
	   }
   }
   /* imported numbers from general list*/
   $imported_general = array();
   $importlist = $_POST['gimportlist']; 
   if(strlen($importlist)) {
       $phones = preg_split("/,/", trim($importlist)); 
	   foreach($phones as $phone) {
	       if(!preg_match("/^[0-9]{8,}$/", $phone)) { 
		      continue;
		   }	
		   $imported_general[] = $phone; 
	   }
	   $imported_general = array_unique($imported_general);
	   
   }  

   /* imported numbers from working list*/
   $imported_worklist = array();
   $importlist = $_POST['wimportlist']; 
   if(strlen($importlist)) {
       $phones = preg_split("/,/", trim($importlist)); 
	   foreach($phones as $phone) {
	       if(!preg_match("/^[0-9]{8,}$/", $phone)) { 
		      continue;
		   }	
		   $imported_worklist[] = $phone; 
	   } 
   }     
   
   $phone_numbers = array_merge($input_numbers, $imported_quiz, $imported_general, $imported_worklist);
   if(isset($uploaded_numbers)) {
	    $phone_numbers = array_merge($phone_numbers, $uploaded_numbers);
   }   
   
   return array_unique($phone_numbers); 
}

function read_excel_upload($file) 
{
   $data = new Spreadsheet_Excel_Reader();
   $data->setOutputEncoding('CP1251');
   $data->read($file);
   $cells = $data->sheets[0]['cells'];
   $numbers = array();
   for ($row=1; strlen($cells[$row][1]); $row++) {
      $number = trim($cells[$row][1]);
	  $number = preg_replace("/\s+/", "", $number);
	  $number = preg_replace("/\+/", "", $number);
	  $number = preg_replace("/-/", "", $number);
      if(!preg_match("/^[0-9]{8,}$/", $number)) {
	     continue;
	  }	 
	  $numbers[] = $number;
   }
   return $numbers;
}

function read_csv_upload($file) {
   $number_list = array();
   if(!($lines = @file($file))) {
      print "The file $file is unreadable!";
	  exit; 
   } 
   foreach($lines as $line) {
      $numbers = preg_split("/\n|,/", $line); 
	  foreach($numbers as $number) {
	     $number = $number = preg_replace("/\s+/", "", $number);
		 $number = preg_replace("/\+/", "", $number);
		 $number = preg_replace("/-/", "", $number);
		 if(!preg_match("/^[0-9]{8,}$/", $number)) {
		    continue;
		 } //print "$number, ";
		 $number_list[] = trim($number);
	  }
   } //exit;
   return $number_list;
}

function import_keywords() 
{  
   error_reporting(0);
   $keyword_col = $_POST['keyword_col'];
   $keywordAlias_col = $_POST['keywordAlias_col'];
   $content_col = $_POST['content_col'];
	 $optionalWords_col = $_POST['optionalWords_col'];
   $category_col = $_POST['category_col'];
   $language_col = $_POST['language_col'];
   $attribution_col = $_POST['attribution_col'];
   $file = $_FILES['file']['tmp_name'];
   $start = $_POST['start'];
   
   if(!is_readable($file) || !file_exists($file)) {
      return '<strong>File not Uploaded or Unreadable</strong>';
   }
   $data = new Spreadsheet_Excel_Reader();
   $data->setOutputEncoding('CP1251');
   $data->read($file);
   $cells = @$data->sheets[0]['cells'];
   $keywords = array();

   for($row=$start; strlen($cells[$row][1]); $row++) 
   {    
      $keyword = rtrim($cells[$row][$keyword_col]);
	  if(!preg_match("/^[0-9a-z.-\s_]{2,}$/i", $keyword)) {
          return "Keyword not valid: \"$keyword\" (Line: $row)";
      }   
      if(strlen($keyword)>keywordLength) {
          return "Keyword: \"$keyword\" is too long (Line: $row)";
      }

	  $keywordAlias = rtrim($cells[$row][$keywordAlias_col]);
	  if($keywordAlias){
	  if(!preg_match("/^[0-9a-z.-\s_]{2,}$/i", $keywordAlias)) {
          return "Keyword Alias not valid: \"$keywordAlias\" (Line: $row)";
      }   
      if(strlen($keywordAlias)>keywordLength) {
          return "Keyword Alias: \"$keywordAlias\" is too long (Line: $row)";
      }
	  }
	  
	  $content = $cells[$row][$content_col];
		$optionalWords = $cells[$row][$optionalWords_col];
		
		if($optionalWords){
			$word_s = preg_split('/,/', $optionalWords);
			foreach($word_s as $word_){
				if(!preg_match("/^[0-9a-z.-\s_]{2,}$/i", $word_)){
					return "Optional word not valid: \"$word_\" (Line: $row)";
				}
			}
		}
		
		
	  $categoryId = NULL;
	  if($category_col) 
	  { 
	      $category = mysql_real_escape_string(trim($cells[$row][$category_col])); 
		  if(strlen($category)) {
		     /* check if category exists */
		     $sql = "SELECT * FROM category WHERE name='$category'";
		     if(!($result=mysql_query($sql))) {
		         return 'ERROR: '.mysql_error();
		     }
		     if(!mysql_num_rows($result)) {
			 if(logic_lock_system("GRAMEEN_NEW_CATEGORY")) {
				return 'ERROR: Unable to lock system while adding new category';
			 }

			 $sql_query = "INSERT INTO category(name, created) VALUES('$category', NOW())";
			 if(!mysql_query($sql_query)) {
			   logic_unlock_system("GRAMEEN_NEW_CATEGORY");
			   return 'ERROR: '.mysql_error().' while adding new category';
			 }

			 logic_unlock_system("GRAMEEN_NEW_CATEGORY");
			 $categoryId = mysql_insert_id();
		     } 
			 else {
		       $_row = mysql_fetch_assoc($result);
		       $categoryId = $_row['id'];
             }
		  }
	  }
	  $languageId = NULL;
	  if($language_col) 
	  {
	      $language = mysql_real_escape_string($cells[$row][$language_col]);
		  if(strlen($language)) {
		     /* check if language exists */
		     $sql = "SELECT * FROM language WHERE name='$language'";
		     if(!($result=mysql_query($sql))) {
		         return 'ERROR: '.mysql_error();
		     }
		     if(!mysql_num_rows($result)) {
                         if(logic_lock_system("GRAMEEN_NEW_LANGUAGE")) {
                                return 'ERROR: Unable to lock system while adding new language';
                         }

                         $sql_query = "INSERT INTO language(name, created) VALUES('$language', NOW())";
                         if(!mysql_query($sql_query)) {
                           logic_unlock_system("GRAMEEN_NEW_LANGUAGE");
                           return 'ERROR: '.mysql_error().' while adding new language';
                         }

                         logic_unlock_system("GRAMEEN_NEW_LANGUAGE");
                         $languageId = mysql_insert_id();

		     } else {
			     $_row = mysql_fetch_assoc($result);
			     $languageId = $_row['id'];
		     }
		  }
	  }
	  $attribution = NULL;
	  if($attribution_col){
		$_attribution = rtrim(trim($cells[$row][$attribution_col]));
		if(strlen($_attribution)){
			$attribution = $_attribution;
		}
	  }
	  
	  if(strlen($content) && preg_match("/\|\|/", $content)) 
	  {
	      global $triggers;
		  $triggers = array();
		  
		  $content_sections = preg_split("/\|\|/", $content);
		  $section = array_shift($content_sections);
		  $first_content = preg_replace("/\.$/", "", $section).'. For more, SMS 1 to ::SHORTCODE::';
		  
		  $keywords[] = array( 'keyword'=>$keyword, 'content'=>$first_content, 'keywordAlias'=>$keywordAlias, 'optionalWords'=>$optionalWords,
		                       'categoryId'=>$categoryId, 'languageId'=>$languageId, 'trigger_keyword'=>$keyword.'_autooutbound1');
		  
		  $n = 1;		  
		  $total = count($content_sections);
		  
		  foreach($content_sections as $section) {
		     $section = trim($section);
			 $auto_keyword = $keyword.'_autooutbound'.$n;
			 
			 $k = array( 'keyword'=>$auto_keyword, 'content'=>$section, 
			             'categoryId'=>$categoryId, 'languageId'=>$languageId, 'keywordAlias'=>'', 'trigger_keyword'=>'');
			 			 
			 if($n < $total) {
			     $k['content'] = $section = preg_replace("/\.$/", "", $section).'. For more, SMS 1 to ::SHORTCODE::';
				 $k['trigger_keyword'] = $keyword.'_autooutbound'.($n + 1);
			 }
			 
			 $keywords[] = $k;
			 $n++;
		  }
	  }	
	  else {
	     $keywords[] = array( 'keyword'=>$keyword, 'content'=>$content, 'optionalWords'=>$optionalWords,
		                      'categoryId'=>$categoryId, 'languageId'=>$languageId,
							  'keywordAlias'=>$keywordAlias, 'trigger_keyword'=>'', 'attribution'=>$attribution);             
	  }
	  				  						  
   }
   if(!count($keywords)) {
      return '<strong>No keywords found in Uploaded File.</strong>';
   } 
	 
	 return array_reverse($keywords);   
}

function import_words($file, $start=1) {  
   if(!is_readable($file) || !file_exists($file)) {
      return '<strong>File not Uploaded or Unreadable</strong>';
   }
   $data = new Spreadsheet_Excel_Reader();
   $data->setOutputEncoding('CP1251');
   $data->read($file);
   $cells = @$data->sheets[0]['cells'];
   $words = array();

   for($row=$start; strlen($cells[$row][1]); $row++) {    
      $word = rtrim($cells[$row][1]);
	  if(!preg_match("/^[0-9a-z.-\s_]{2,}$/i", $word)) {
          return "Word not valid: \"$word\" (Line: $row)";
      }   
	  $list = preg_split("/,|\s+/", rtrim($cells[$row][2])); 
      $newlist = array();
	  /* validate aliases */
	  foreach($list as $alias) {
	        if(!strlen($alias)) continue;
		    if(!preg_match("/^[0-9a-z_]{2,}$/i", $alias)) {
			   return "Keyword Alias not valid: \"$alias\" (Line: $row)";
			}
			$newlist[]=$alias;
	  }
	  $words[] = array('word'=>$word, 'aliases'=>$newlist);
   }
   if(!count($words)) {
      return '<strong>No words found in Uploaded File.</strong>';
   }
   return $words;   
}

function _20090325_import_keywords($file, $aliases=0, $start=1) {  
   if(!is_readable($file) || !file_exists($file)) {
      return '<strong>File not Uploaded or Unreadable</strong>';
   }
   $data = new Spreadsheet_Excel_Reader();
   $data->setOutputEncoding('CP1251');
   $data->read($file);
   $cells = @$data->sheets[0]['cells'];
   $keywords = array();

   for($row=$start; strlen($cells[$row][1]); $row++) {    
      $keyword = rtrim($cells[$row][1]);
	  if(!preg_match("/^[0-9a-z.-\s_]{2,}$/i", $keyword)) {
          return "Keyword not valid: \"$keyword\" (Line: $row)";
      }   
      if(strlen($keyword)>keywordLength) {
          return "Keyword: \"$keyword\" is too long (Line: $row)";
      }   
	  if($aliases) {
	     $list = preg_split("/,\s+/", rtrim($cells[$row][2]));
		 $newlist = array();
		 $content = $cells[$row][3];
		 /* validate aliases */
		 foreach($list as $alias) {
		    if(!preg_match("/^[0-9a-z_]{2,}$/i", $alias)) {
			   return "Keyword Alias not valid: \"$alias\" (Line: $row)";
			}
			if(strlen($alias)>keywordLength) {
			   return "Keyword alias: \"$alias\" is too long (Line: $row). Only ".keywordLength." chars allowed";
			}
			$newlist[]=$alias;
		 }
		 if(!count($newlist)) {
		    $aliaslist = NULL;
		 }
		 else {
		     $aliaslist = implode(',', array_unique($newlist));
		 }
	  }
	  else {
	     $content = $cells[$row][2];
		 $aliaslist = NULL;
	  }
	  $keywords[] = array('keyword'=>$keyword, 'aliases'=>$aliaslist, 'content'=>$content);
   }
   if(!count($keywords)) {
      return '<strong>No keywords found in Uploaded File.</strong>';
   }
   return $keywords;   
}

function import_users() 
{  
   error_reporting(0);
   /*
   $misdn_col = $_POST['misdn_col'];
   $phones_col = $_POST['phones_col'];
   $names_col = $_POST['calltime_col'];
   $gender_col = $_POST['gender_col'];
   $dob_col = $_POST['dob_col'];
   $occupation_col = $_POST['occupation_col'];
   $location_col = $_POST['location_col'];
   $initiative_col = $_POST['initiative_col'];
   $deviceInfo_col = $_POST['deviceInfo_col'];
   $start = $_POST['start']; */
   
   extract($_POST);
   
   $file = $_FILES['file']['tmp_name']; 
   
   if(!is_readable($file) || !file_exists($file)) {
      return '<strong>File not Uploaded or Unreadable</strong>';
   }
   $data = new Spreadsheet_Excel_Reader();
   $data->setOutputEncoding('CP1251');
   $data->read($file);
   $cells = @$data->sheets[0]['cells'];
   $users = array();
   
   for($row=$start; strlen($cells[$row][$misdn_col]); $row++) 
   {    
      $misdn = rtrim(trim($cells[$row][$misdn_col]));
	  if(!($_misdn = get_misdn_strict($misdn))) {
          return "Phone Number not valid: \"$misdn\" (Line: $row)";
      } 
      if($phones_col) {
	      $phones = rtrim(trim($cells[$row][$phones_col]));
		  $otherphones = array();
		  if(strlen($phones)) {
          $_phones = preg_split("/,(\s)*/", $phones);
		  foreach($_phones as $_phone) {
		      if(!($phone = get_misdn_strict($_phone))) { 
			      return "Invalid Phone Number \"$_phone\" in list  (Line: $row)";
			  }
			  $otherphones[] = $phone;
		  }
		  $otherphones = implode(',', $otherphones);	
		  }  
      }
      else {
         $otherphones = NULL;
      }
	  if($dob_col) {
	     $dob = $cells[$row][$dob_col];  
	     if(!strlen($dob)) {
		      $dob = '0000-00-00';
		 }
		 elseif(strlen($dob) && !preg_match("/^[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}$/", $dob)) {
	          return "Date of birth not valid: \"$dob\" (Line: $row)";
	     }
	  }
	  else {
	     $dob = '0000-00-00';
	  }
	  if($gender_col) {
	      $gender =  $cells[$row][$gender_col];
		  if(strlen($gender) && !preg_match("/^(male|female)$/i", $gender)) {
		      return "Gender not valid: \"$gender\" (Line: $row)";
		  }
	  }
	  else {
	     $gender = NULL;
	  }
	  $names = $names_col ? rtrim(trim($cells[$row][$names_col])) :  NULL;
	  $occupation = $occupation_col ? rtrim(trim($cells[$row][$occupation_col])) :  NULL;
	  $location = $location_col ? rtrim(trim($cells[$row][$location_col])) :  NULL;
	  $initiativeInfo = $initiative_col ? rtrim(trim($cells[$row][$initiative_col])) :  NULL;
	  $deviceInfo = $deviceInfo_col ? rtrim(trim($cells[$row][$deviceInfo_col])) :  NULL;
	  $notes = $notes_col ? rtrim(trim($cells[$row][$notes_col])) :  NULL;
	  // new fields
	  $district = $district_col ? rtrim(trim($cells[$row][$district_col])) :  NULL;
	  $subcounty = $subcounty_col ? rtrim(trim($cells[$row][$subcounty_col])) :  NULL;
	  $gpscordinates = $gpscordinates_col ? rtrim(trim($cells[$row][$gpscordinates_col])) :  NULL;
	  
	  $users[] = array('misdn'=>$_misdn, 'phones'=>$otherphones, 'names'=>$names, 'dob'=>$dob, 'occupation'=>$occupation, 
	  'location'=>$location, 'initiativeInfo'=>$initiativeInfo, 'deviceInfo'=>$deviceInfo, 'notes'=>$notes, 'district'=>$district, 
	  'subcounty'=>$subcounty, 'gpscordinates'=>$gpscordinates);						  
   }
   if(!count($users)) {
       return '<strong>No records found in Uploaded File.</strong>';
   } 
   return $users;   
}

function import_applogs() 
{  
   error_reporting(0);
   extract($_POST);
   
   $file = $_FILES['file']['tmp_name']; 
   
   if(!is_readable($file) || !file_exists($file)) {
      return '<strong>File not Uploaded or Unreadable</strong>';
   }
     
   $data = new Spreadsheet_Excel_Reader();
   $data->setOutputEncoding('CP-1251');
   $data->read($file);
   $cells = @$data->sheets[0]['cells'];
   $logs = array();
	 
  if(preg_match('/GoogleSMS6001/', $application) || preg_match('/HealthIVR/', $application)){
   for($row=$start; strlen($cells[$row][$misdn_col]); $row++) 
   {    
      $misdn = rtrim(trim($cells[$row][$misdn_col]));
	  if(!($_misdn = get_misdn_strict($misdn))) {
          if(isset($skip)) {
		       continue;
		  }
		  return "Phone Number not valid: \"$misdn\" (Line: $row)";
      } 
	  if(!$date_col) {
	      $date = '0000-00-00 00:00:00';
	  }
	  else
	  {
	     $date = $cells[$row][$date_col]; 
	     if(!strlen($date)) {
		      $date = '0000-00-00 00:00:00';
		 }
		 else {
		     if(!strcmp('GoogleSMS6001', $application)) {
			     $date = get_mysql_timestamp($date);
			 }
			 elseif(!strcmp('HealthIVR', $application)) {
			     if(!preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}+$/', $date)) {
				      $date = '0000-00-00';
				 }
				 if(strlen($time_col)) {
				     $time = $cells[$row][$time_col];
					 if(!strlen($time)) {
					      $time = '00:00:00';
					 }
				 }
				 else {
				     $time = '00:00:00';
				 }
				 $date = $date.' '.$time; 
			 }
		 }
	  }
	  if(!strcmp('GoogleSMS6001', $application)) {
	      $site = $site_col ? rtrim(trim($cells[$row][$site_col])) :  NULL;
	      $location = $location_col ? rtrim(trim($cells[$row][$location_col])) :  NULL;
		  $logs[] = array('date'=>$date, 'misdn'=>$_misdn, 'location'=>$location, 'site'=>$site);
	  }
	  elseif(!strcmp('HealthIVR', $application)) {
	      $duration = $duration_col ? rtrim(trim($cells[$row][$duration_col])) :  NULL;
		  
		  if(strlen($duration) && !preg_match('/^[0-9]+$/', $duration)) {
		       $duration = NULL;
		  }	   
		  $logs[] = array('date'=>$date, 'misdn'=>$_misdn, 'duration'=>$duration);	   
	  }
	  
   }
	}else{
# importing old FormSurvey starts from here
		if(!$date_col){
			return "Date Column required";		
		}
		if(!preg_match('/^[0-9]+$/',$titleRow)){
			return "Specify the number of the row Containing the Title Fields";
		}
		if(sprintf("%d", $titleRow) > sprintf("%d", $start)){
			return "Invalid Title Row, Specify the number of the row Containing the Title Fields";
		}
		if($date_col == $misdn_col){
			return "Date Column can not be the same as the MSISDN Column";
		}
		
		$_titles = $cells[$titleRow];
		if(!$_titles){
			return "Invalid Title Row, Specify the number of the row Containing the Title Fields";
		}
		
		$found = false;
		$tctr=0;
		foreach($_titles as $key => $title){
			if(!strlen($title)){
				if($found){
					break;
				}
				continue;
			}else{
				$found = true;
			}
				$titles[$key] = $title;
		}

		$newTitles[$date_col] = $titles[$date_col];
		$newTitles[$misdn_col] = $titles[$misdn_col];
		
		foreach($titles as $key => $value){
			if(strlen($newTitles[$key])){
				continue;
			}
			$newTitles[$key] = $value; //A => "MSISDN"
		}
		
		$data = array();
		$date_formats = array ('YYYY-MM-DD', 'YYYY-DD-MM', 'DD-MM-YYYY', 'MM-DD-YYYY', 'YYYYMMDD', 'YYYYDDMM');
		for($row = $start; strlen($cells[$row][$misdn_col]); $row++){
			$xdata = array();
			$misdn="";
			if(!($_misdn = get_misdn_strict($cells[$row][$misdn_col]))) {
				if(isset($skip)) {
					continue;
				}
				$misdn = $cells[$row][$misdn_col];
			}
			$cells[$row][$misdn_col] = $_misdn;
			
			$_date="";
			foreach($date_formats as $date_format){
				$_date=validateDate($cells[$row][$date_col], $date_format);
				if(strlen($_date)){
					break;
				}
			}
			$date = $cells[$row][$date_col];
			if(!strlen($_date)){
				return "Date is Invalid: \"$date\" (Line: $row) <br> Valid Date Formats are 'YYYY-MM-DD', 'YYYY-DD-MM', 'DD-MM-YYYY', 'MM-DD-YYYY', 'YYYYMMDD', 'YYYYDDMM'";
			}
			$cells[$row][$date_col] = $_date;
			
			$xdata["other"] = "";
			$ctr = 0;
			foreach($newTitles as $col => $value){
				if($ctr < 2){ //date , msisdn
					if($ctr == 0)
						$xdata['date'] = $cells[$row][$col];
					else
						$xdata['msisdn'] = $cells[$row][$col];
					$ctr++;
				}else{
					if(strlen($cells[$row][$col])){
						$xdata["other"] .= $value.":".$cells[$row][$col]." ";
					}
				}
			}
			$data[] = $xdata;
		}
		$logs = $data;
		/*print_r($data);
		exit();*/
	}
   if(!count($logs)) {
       return '<strong>No records found in Uploaded File.</strong>';
   } 
   return $logs;   
}


function import_districts() 
{  
   extract($_POST);
   
   $file = $_FILES['file']['tmp_name']; 
   
   if(!is_readable($file) || !file_exists($file)) {
      return '<strong>File not Uploaded or Unreadable</strong>';
   }
   $data = new Spreadsheet_Excel_Reader();
   $data->setOutputEncoding('CP1251');
   $data->read($file);
   $cells = @$data->sheets[0]['cells'];
   $districts = array();
   
   for($row=$start; strlen($cells[$row][$district_col]); $row++) 
   {    
       $name = rtrim(trim($cells[$row][$district_col]));
	   if(!preg_match('/^[a-z]{2,50}$/i', $name)) {
           return "District not valid: \"$name\" (Line: $row)";
       } 
	   $subcounty_list = array();
       if($subcounty_col) 
	   {
	       $subcounties = rtrim(trim($cells[$row][$subcounty_col]));
		   if(strlen($subcounties)) {
               $_subcounties = preg_split("/,(\s)*/", $subcounties);
		       foreach($_subcounties as $subcounty) {
		           if(!preg_match('/^[a-z]{2,50}$/i', $subcounty)) { 
			           return "Invalid subcounty \"$subcounty\" in list  (Line: $row)";
			       }
			       $subcounty_list[] = $subcounty;
			   }
		   }  
       }   
	   $districts[]=array('name'=>$name, 'subcounties'=>$subcounty_list);
   }
   if(!count($districts)) {
        return '<strong>No records found in Uploaded File.</strong>';
   } 
   return $districts;   
}


?>

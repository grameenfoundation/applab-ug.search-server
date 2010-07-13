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
require 'mresults.inc.php';

session_start();

dbconnect();
validate_session();
 
extract($_GET);

if(isset($all)) {
     unset_search_vars();
	 header("Location: mresults.php?surveyId=$surveyId");
	 exit();
} 
if(isset($cancel) && $cancel=='cfilter') {
     unset($_SESSION['filter']['categorize']);
	 header("Location: mresults.php?surveyId=$surveyId");
	 exit();
}

check_id($surveyId, 'msurveys.php');
$survey = get_table_record('msurvey', $surveyId);
if(empty($survey)) {
    goto('msurveys.php');
}

if(!isset($start) || !is_numeric($start)) {
    $start = 0;
}



set_exclude_results();

if(isset($_POST['filter'])) {
    filter_results();
}

set_unique_idlist();

if(isset($_POST['deletelist'])) 
{
   check_admin_user();
   $_total = 0;
   foreach($_POST as $key=>$val) {
       if(!preg_match('/^r_[0-9]+$/', $key)) {
	      continue;
	   }
       $result = get_table_record('mresult', $val); 
       $form = unserialize($result['form']); 
       foreach($form['uploads'] as $upload) 
	   { 
           list($key, $file) = each($upload); 
	       if(strlen($file) && file_exists(MOBILE_UPLOADS_DIR.'/'.$file)) {
	           if(!unlink(MOBILE_UPLOADS_DIR.'/'.$file)) {
		       }
	       }
       } 
	   $sql = "DELETE FROM mresult WHERE id='$val' AND surveyId='$surveyId' LIMIT 1"; 
       execute_update($sql);
       $_total++;   
	}
	$logged = 0;
    if($_POST['allchecked']) 
	{
	    $sql = "SELECT * FROM mresult WHERE surveyId='$surveyId'".add_exclude_idlist();
		$result = execute_query($sql);
		while($row=mysql_fetch_assoc($result)) {
			      $form = unserialize($row['form']); 
				  foreach($form['uploads'] as $upload)  
				  {
				      list($key, $file) = each($upload); 
					  if(strlen($file) && file_exists(MOBILE_UPLOADS_DIR.'/'.$file)) {
	                      if(!unlink(MOBILE_UPLOADS_DIR.'/'.$file)) {
		                  }
	                  }
				  }
		}
		/* delete all */
		$sql = "DELETE FROM mresult WHERE surveyId='$surveyId'".add_exclude_idlist();
		execute_update($sql);
		logaction("Deleted selected all results for mobile survey: $survey[name]");
		$logged = 1;
	}	
	if(!$logged)
	    logaction("Deleted $_total result(s) for mobile survey: $survey[name]");
	
	unset_search_vars();
    goto("mresults.php?surveyId=$surveyId"); 
}

if(isset($delete)) 
{
   check_admin_user();
   check_id($resultId, 'msurveys.php');
   $result = get_table_record('mresult', $resultId);
   if(strlen($file)) {
      if(file_exists(MOBILE_UPLOADS_DIR.'/'.$file)) {
	     /* delete file */
		if(!unlink(MOBILE_UPLOADS_DIR.'/'.$file)) {
		    show_message("Can Not Delete File", "The file \"".MOBILE_UPLOADS_DIR.'/'.$file." \" can not be deleted!", "#FF0000");
		}
		$logstr = "Deleted uploaded file \"$file\" from Survey result (Result Created: $result[date], 
		Phone ID: $result[phoneId]), Survey Details (Name: $survey[name], Created: $survey[createdate])";
		logaction($logstr);		 
	  }
	  goto("mresults.php?surveyId=$surveyId&start=$start");
  }
}

if(isset($deleteresult)) 
{
   check_admin_user();
   check_id($resultId, 'msurveys.php');
   $result = get_table_record('mresult', $resultId); 
   $form = unserialize($result['form']); 
   foreach($form['uploads'] as $upload) { 
      list($key, $file) = each($upload); 
	  if(strlen($file) && file_exists(MOBILE_UPLOADS_DIR.'/'.$file)) {
	     if(!unlink(MOBILE_UPLOADS_DIR.'/'.$file)) {
		 }
	  }
   } 
   execute_update("DELETE FROM mresult WHERE id=$resultId LIMIT 1");
   $logstr = "Deleted Survey result (Result Created: $result[date], 
   Phone ID: $result[phoneId]), Survey Details (Name: $survey[name], Created: $survey[createdate])";
   logaction($logstr);	
   goto("mresults.php?surveyId=$surveyId&start=$start");
}

if(!admin_user()) {
	$sql = "SELECT mresult.*, DATE_FORMAT(date, '%d/%m/%Y %r') AS time FROM mresult LEFT JOIN msurvey ON (msurvey.id=mresult.surveyId) WHERE mresult.surveyId='$surveyId' AND msurvey.useraccess=1";
} 
else {
	$sql = "SELECT mresult.*, DATE_FORMAT(date, '%d/%m/%Y %r') AS time FROM mresult WHERE surveyId='$surveyId'";
}
$total_result_set = mysql_num_rows( execute_query($sql) );

$sql .= add_exclude_idlist();
$sql .= add_unique_idlist();
$_SESSION['exportq'] = $sql;

if(isset($_SESSION['filter']) && isset($_SESSION['filter']['categorize'])) {
    $_SESSION['filterq'] = $sql;
}

$result = execute_query($sql);

$total = mysql_num_rows($result); 
$limit = 20;
$this_pg = $start + $limit;
$next = $start + $limit;
$back = $start - $limit;

/* listing */
$sql .= " ORDER BY date DESC LIMIT $start, $limit";
$result = execute_query($sql);
$content = display_mresults();

if(isset($_SESSION['filtered_results'])) {
    //$total_listing = $total;
	//$summary = $total.' Filtered';
	$summary = $total.' Filtered';
	//$total = $_SESSION['total_result_set'];
}
elseif(isset($_SESSION['search'])) {
    if(count($_SESSION['exclude_results'])) {
        //$total = $_SESSION['total_result_set'];
		//$total_listing = $total_result_set-count($_SESSION['exclude_results']);
		//$summary = $total_listing.' Search';
		$summary = $total.' Search';
    }
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
<script type="text/javascript" src="basic.js"></script>
<script type="text/javascript" src="date.js"></script>
<script>
function deleter() {
     var m = document.getElementById('allchecked').value == '1' ? 'Are you sure you want to DELETE ALL the <?= $total ?> result(s) for this survey?' :
	 'Are you sure you want to DELETE the selected results on this page?';
	 if(!confirm(m)) return false; 
	 document.forms[0].submit();
}
function selectall_(m, _all) {
   if(_all) {
       document.getElementById('allchecked').value = '1';
   }else {
      document.getElementById('allchecked').value = '0';
   }
   selectall(m);
   //alert(document.getElementById('allchecked').value);
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
          <td valign="top"><table width="100%" border="0" cellpadding="0" cellspacing="0" class="border">
               <!--DWLayoutTable-->
               <tr>
                    <td height="22" colspan="6" align="center" valign="middle" class="caption">
					Mobile Survey Results - Total Results in Survey: <?= $total_result_set ?></td>
            </tr>
               <tr>
                    <td width="20" height="20">&nbsp;</td>
                    <td width="36">&nbsp;</td>
                    <td width="34">&nbsp;</td>
                    <td width="645">&nbsp;</td>
                    <td width="34">&nbsp;</td>
                    <td width="19">&nbsp;</td>
               </tr>
               <tr>
                    <td height="28">&nbsp;</td>
                    <td>&nbsp;</td>
                    <td valign="middle"><img src="images/quiz.gif" width="34" height="28" style="cursor: pointer" title="<?= $name ?>" /></td>
                    <td valign="middle">&nbsp;
					<span class="caption2">Survey: </span>
					<span style="color: <?= $survey['useraccess'] ? '#FF33CC' : '' ?>"><?= truncate_str($survey['name'], 30) ?></span> 
					- 
					<span style="color: #666666">created <?= $survey['createdate'] ?>
					</span> &nbsp;&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
               </tr>
               <tr <?= !isset($summary) ? 'style=display:none' : ''?>>
                    <td height="40"></td>
                    <td colspan="4" valign="middle" bgcolor="aliceblue" style="padding-left:20px;border-top:solid #fff 4px">
					  <?= isset($summary) ? '<span style="color:#ff6600;font-weight:bold">[Showing '.
					  $summary.' Result(s) from a Total of '.$total_result_set.']</span>' : '' ?>					
					  </td>
                    <td></td>
               </tr>
               <tr>
                  <td height="330">&nbsp;</td>
                 <td colspan="4" valign="top">
	                <?= $content ?>	   
	   </td>
                  <td>&nbsp;</td>
               </tr>
        <tr>
                <td height="19">&nbsp;</td>
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
<script>
var ffields = {
from: '<?= isset($_SESSION['filter']) ? $_SESSION['filter']['from'] : date('d/m/Y') ?>',
to: '<?= isset($_SESSION['filter']) ? $_SESSION['filter']['to'] : date('d/m/Y') ?>',
type: '<?= isset($_SESSION['filter']) ? $_SESSION['filter']['type'] : '' ?>',
location: '<?= isset($_SESSION['filter']) ? $_SESSION['filter']['location'] : '' ?>',
xduplicates: <?= isset($_SESSION['filter']) && isset($_SESSION['filter']['xduplicates']) ? 'true' : 'false' ?>,
categorize: <?= isset($_SESSION['filter']) && isset($_SESSION['filter']['categorize']) ? 'true' : 'false' ?>
};
</script>
</body>
</html>

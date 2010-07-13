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
function display_mresults() 
{
if(isset($_SESSION['filtered_results'])) {
    if(isset($_SESSION['filter']['categorize']))
	     return display_categorized();
}
global $surveyId, $total, $start, $limit, $next, $back, $this_pg, $result, $all; 

$html = '
<div style="height: 40px">
    <table border="0">
        <tr valign="middle">
           <td><img src="images/excel.jpg"></td>
           <td>
               <a href="xls.mresults.php?surveyId='.$surveyId.'" target="_blank" style="color:#000000; text-decoration:underline"
               title="Click to Open Results in Microsoft Excel">Open Results in MS Excel</a>
           </td>
		   <td width=35></td>
           <td><img src="images/filter.jpg" style=cursor:pointer onclick="filter();"></td>
           <td>
               <a href="#" onclick="filter();return false;" style="color:#000000; text-decoration:underline"
               title="Filter results">Filter Results</a>
           </td>		   
           <td width="35"></td>
           <td id="download-images">
							<img src="images/Imagen-PNG-32x32.png" align="ABSMIDDLE"/>&nbsp;<a href="#" onclick="iDownload('.$surveyId.','.$start.','.$limit.'); return false;" style="color: #000000; text-decoration: underline" title="Click To Download All Images">Download Images</a>
					 </td>
        </tr>
    </table>   	  
</div>';

$color = '#E4E4E4'; $i=0;
$ids = array();

$html .= '
<div>
<form method="post">	 
<table border=0 width="100%" cellpadding=0 cellspacing=0>
    <tr>
        <td height=30 colspan=5>';

	if(admin_user()) {
		$html .= '
           <a href="#" style="text-decoration:underline;color:#0000ff" onclick="selectall_(true, false);return false;">Select All</a> |
           <a href="#" style="text-decoration:underline;color:#0000ff" onclick="selectall_(true, true);return false;">
           Select All '.$total.' Result(s)</a> | 
           <a href="#" style="text-decoration:underline;color:#0000ff" onclick="selectall_(false);return false;">Select None</a>|
           <a href="#" style="text-decoration:underline" onclick="return deleter();">[Delete Selected]</a> |
           <a href="msurvey.php?surveyId='.$surveyId.'" style="text-decoration: underline">Go To Survey</a> | ';
	}

	$html .= '
           <a href="mresults.php?surveyId='.$surveyId.'&all" style="text-decoration: underline">Show All Results</a> | 
           <a href="msearch.php?surveyId='.$surveyId.'" style="text-decoration: underline">Search Results</a>
        </td>
    </tr>
    <tr id=title>
        <td height=40 colspan=2><u>Date/Time</u></td>
        <td><u>Phone ID/Name</u></td>
        <td><u>Survey ID</u></td>
        <td><u>Options</u></td>
     </tr>';

while($row = mysql_fetch_assoc($result)) 
{ 
    $form = unserialize($row['form']); 
    $data = $form['data'];
    $uploads = $form['uploads']; 

    if(count($uploads)>1) $more_pics = true;
    $ctr = 1;
    $location = get_location_from_misdn($row['phoneId']);

    foreach($uploads as $f) {
        $picture_name = $location.'_'.$row['phoneId'].'_'.$row['id'].($more_pics ? '_'.$ctr++ : '').'.png';
        if(strlen($f['value']) && file_exists(MOBILE_UPLOADS_DIR.'/'.$f['value'])) {
            //$_SESSION['pics'][] = array('file_name'=>$f['value'], 'unique_filename'=>$picture_name); 
        }
    }
	
    $time = $row['time'];
    preg_match('/AM|PM/', $time, $arr);
    $time = preg_replace('/:[0-9]+\s(AM|PM)$/', ' ', $time).array_pop($arr);

    $cb = 'r_'.$row['id'];
    $ids[] = $cb;
    $color = $i++%2 ? '#ffffff' : '#EEEEEE';
    $uniqueid = $location.'_'.$row['phoneId'].'_'.$row['id'];
    $phoneid = $row['phoneId'] ? get_phone_display_label($row['phoneId'], 30) : 'N/A';

    $html .= '
    <tr bgcolor='.$color.' onmouseover="this.style.backgroundColor=\''.HOVERCOLOR.'\'" 
	     onmouseout="this.style.backgroundColor=\''.$color.'\'">
        <td height=30><input type=checkbox name="'.$cb.'" id="'.$cb.'" value="'.$row['id'].'" /></td>
        <td>'.$time.'</td>
        <td>'.$phoneid.'</td>
        <td>'.truncate_str($uniqueid, 30).'</td>
        <td>
            <a href="#" onclick="dr('.$row['id'].', '.$start.');return false;" style=color:#0000ff>View</a>';

    if(admin_user()) {
	    $html .= ' |
            <a href="?surveyId='.$surveyId.'&resultId='.$row['id'].'&start='.$start.'&deleteresult=TRUE" 
            onclick="if(!confirm(\'Are you sure you want to delete this result?\')) return false;" style=color:#ff0000>Delete</a>';
    }
    $html .= '
        </td>
      </tr>';
}
$ids = implode(',', $ids);

$html .= '
  </table>
    <input type=hidden value="'.$ids.'" id="list" />
    <input type=hidden value="0" name="allchecked" id="allchecked" /> 
    <input type=hidden name="deletelist" id="deletelist" /> 
  </form>
</div>';



if($total > $limit)
{   
    $scroll = '<div style="text-align:justify; padding:10px">';
    if($back >= 0) { 
        $scroll .= '<a href="?surveyId='.$surveyId.'&start='.$back.'" style="color: #000000">&laquo; Prev</a> ';
    }	 
    for($i=0, $l=1; $i < $total; $i= $i + $limit){
        if($i != $start)
            $scroll .= '<a href="?surveyId='.$surveyId.'&start='.$i.'">'.$l.'</a> ';
        else $scroll .= '<span style="color: #ff0000; font-weight: bold">'.$l.'</span> ';
        $l = $l+1;
    }
    if($this_pg < $total) {
        $scroll .= ' <a href="?surveyId='.$surveyId.'&start='.$next.'" style="color: #000000">Next &raquo;</a>';
    }
    if($l>2) {
        $html .= $scroll.'</div>';
    }		   
}
return $html;

}

function display_categorized() 
{
    if(!isset($_SESSION['filtered_results'])) {
	    return 'ERROR';
	}
	global $surveyId;
	$from = explode('/', $_SESSION['filter']['from']);
	$to = explode('/', $_SESSION['filter']['to']);
	
	$start_month = $from[1];
	$start_day = $from[0];
	
	$end_month = $to[1];
	$end_day = $to[0];
	
	$year = $from[2];
	
	$categories = array();
	$i = 0;
	for($month=$start_month; $month<=$end_month; $month++) 
	{
		/* 1st - 7th */
		$categories[] = array(
		'index' => $i++,
		'from' => $year.'-'.$month.'-01 00:00:00', 
		'to' => $year.'-'.$month.'-07 23:59:59',
		'label' => 'Week 1 - '.date('M jS', mktime(0, 0, 0, $month, 1, $year)).' to '.
		           date('M jS', mktime(0, 0, 0, $month, 7, $year)),
		'xlslabel' => 'Week 1 - '.date('M jS', mktime(0, 0, 0, $month, 1, $year)).' to '.
		           date('M jS', mktime(0, 0, 0, $month, 7, $year)).', '.$year);

		if($month==$end_month && $month==date('m')) {
		    if(date('d')<8) {
			    break;
			}
		}	

		/* 8th - 14th */
		$categories[] = array(
		'index' => $i++,
		'from' => $year.'-'.$month.'-08 00:00:00', 
		'to' => $year.'-'.$month.'-14 23:59:59', 
		'label' => 'Week 2 - '.date('M jS', mktime(0, 0, 0, $month, 8, $year)).' to '.
		           date('M jS', mktime(0, 0, 0, $month, 14, $year)),
		'xlslabel' => 'Week 2 - '.date('M jS', mktime(0, 0, 0, $month, 8, $year)).' to '.
		           date('M jS', mktime(0, 0, 0, $month, 14, $year)).', '.$year);
        
		if($month==$end_month && $month==date('m')) {
		    if(date('d')<15) {
			    break;
			}
		}		
		
		/* 15th - 21st */
		$categories[] = array(
		'index' => $i++,
		'from' => $year.'-'.$month.'-15 00:00:00', 
		'to' => $year.'-'.$month.'-21 23:59:59', 
		'label' => 'Week 3 - '.date('M jS', mktime(0, 0, 0, $month, 15, $year)).' to '.
		           date('M jS', mktime(0, 0, 0, $month, 21, $year)),
		'xlslabel' => 'Week 3 - '.date('M jS', mktime(0, 0, 0, $month, 15, $year)).' to '.
		           date('M jS', mktime(0, 0, 0, $month, 21, $year)).', '.$year);

		if($month==$end_month && $month==date('m')) {
		    if(date('d')<22) {
			    break;
			}
		}

		/* 22nd - end of month */
		$lastday = get_last_day_of_month($month);
		$categories[] = array(
		'index' => $i++,
		'from' => $year.'-'.$month.'-22 00:00:00', 
		'to' => $year.'-'.$month.'-'.get_last_day_of_month($month).' 23:59:59', 
		'label' => 'Week 4 - '.date('M jS', mktime(0, 0, 0, $month, 22, $year)).' to '.
		           date('M jS', mktime(0, 0, 0, $month, $lastday, $year)),
		'xlslabel' => 'Week 4 - '.date('M jS', mktime(0, 0, 0, $month, 22, $year)).' to '.
		           date('M jS', mktime(0, 0, 0, $month, $lastday, $year)).', '.$year);		   
				   
	}
	$_SESSION['filter_categories'] = $categories;
	$html = '
	<div>
	   <table border=0>
	   <tr>
	      <td height=40><img src=images/cancel.gif style=cursor:pointer title=Cancel>&nbsp;</td>
		  <td width=160><a href="?surveyId='.$surveyId.'&cancel=cfilter" style="color:#000;text-decoration:underline">Cancel Category Filter</a></td>
	      <td height=40><img src=images/filter.jpg style=cursor:pointer title=Filter onclick="filter();"></td>
		  <td><a href="#" style="color:#000;text-decoration:underline" onclick="filter();return false;">Filter Again</a></td>
	   </tr>	   
	   </table>   
	</div>
	<div>
	<table width=70% cellpadding=0 cellspacing=0 id=result>
	<tr id=title>
	    <td align=center width=120 height=35>Week</td>
		<td align=center width=120>Total Results</td>
		<td align=center colspan=2>Options</td>
	</tr>';
	
	foreach($categories as $c) {
	    $sql = $_SESSION['filterq']." AND date BETWEEN '$c[from]' AND '$c[to]'"; 
		$total = mysql_num_rows( execute_query($sql) );
		$html .= '
		<tr>
		   <td height=30 width=200>'.$c['label'].'</td>
		   <td align=center><strong>'.$total.'</strong></td>
		   <td align=center width=60>
		       <a href="xls.mresults.php?surveyId='.$surveyId.'&week='.$c['index'].'" target="_blank" title="Export Results to MS Excel">
			      <img src=images/excel.jpg border=0 />
			   </a>
		   </td>
		   <td align=center width=60>
		       <a href="downloadimages.php?week='.$c['index'].'"  target="_blank" title="Download All Images In this Category">
			      <img src=images/Imagen-PNG-32x32.png border=0 />
			   </a>
		   </td>
		</tr>';
	}
	$html .= '</table></div>';
	return $html;
	
}

function set_exclude_results() 
{ 
     global $surveyId;
     if(isset($_SESSION['exclude_results'])) {
	     return;
     }
	 
	 $_SESSION['exclude_results'] = array(); 
	 $_SESSION['test_results'] = array();
	 	 
	 
	 $sql = "SELECT * FROM mresult WHERE surveyId='$surveyId'";
	 
	 $result=execute_query($sql);
	 $_SESSION['total_result_set'] = mysql_num_rows($result);
	 
	 while($row=mysql_fetch_assoc($result)) {
	     $form = unserialize($row['form']);
		 $data = $form['data'];
		 foreach($data as $field) {
		      if(!preg_match('/^radio/i', $field['type'])) {
			      continue;
			  } 
			  if(preg_match('/^is\sthis\sa\stest/i', $field['field']) && preg_match('/^yes$/i', $field['value'])) { 
				   $_SESSION['exclude_results'][] = $row['id'];
				   $_SESSION['test_results'][] = $row['id'];
			  } 
		 }
	 }
	 if(!count($_SESSION['test_results'])) {
	     $_SESSION['test_results'][]=0;
	 } 
}

function add_exclude_idlist() 
{
    global $surveyId;
	$idlist = array();
	if(isset($_SESSION['filtered_results']) && count($_SESSION['filtered_results'])){
		return ' AND mresult.id IN ('.implode(',', $_SESSION['filtered_results']).')';
    }	
	elseif(isset($_SESSION['exclude_results']) && count($_SESSION['exclude_results'])){
		return ' AND mresult.id NOT IN ('.implode(',', $_SESSION['exclude_results']).')';
    }
	return '';
}

function set_unique_idlist(){
	if(!isset($_SESSION['filter']['xduplicates'])){
		unset($_SESSION['unique_idlist']);
		return;
	}
	
	if(isset($_SESSION['unique_idlist'])){
		return;
	}
	
	global $surveyId;
	$sql = "SELECT DISTINCT surveySignature, id FROM mresult WHERE surveyId = '$surveyId' GROUP BY surveySignature";
	$result=execute_query($sql);
	$_SESSION['unique_idlist'] = array();
	while($row = mysql_fetch_assoc($result)){
		$_SESSION['unique_idlist'][] = $row[id];
	}
	if(!count($_SESSION['unique_idlist'])){
		$_SESSION['unique_idlist'][] = 0;
	}
}

function add_unique_idlist(){
	if(isset($_SESSION['unique_idlist']) && count($_SESSION['unique_idlist'])){
		return " AND mresult.id IN (".implode(',', $_SESSION['unique_idlist']).")";
	}
	return '';
}

function filter_results()
{
    global $surveyId;
	session_unregister('filtered_results');
	$_SESSION['filter'] = $_POST;
	$date = preg_split('/\//', $_POST['from']);
	$from = $date[2].'-'.$date[1].'-'.$date[0].' 00:00:00';
	$date = preg_split('/\//', $_POST['to']);
	$to = $date[2].'-'.$date[1].'-'.$date[0].' 23:59:59';
		
	$sql = "SELECT * FROM mresult WHERE surveyId='$surveyId' AND date BETWEEN '$from' AND '$to'";
	//if(isset($_SESSION['excluded_bysearch_results']) && count($_SESSION['excluded_bysearch_results'])) {
	//    $sql .= ' AND id NOT IN('.implode(',', $_SESSION['excluded_bysearch_results']).')';
	//}
	
	$type = $_POST['type'];
	if(!strlen($type)) {
	    $type = 1;
	} 
	switch($type) 
	{
	    case 1: /* All results excluding test results */
		    $sql .= add_exclude_idlist();
		break;
		
		case 2: /* All results */// print_r($_SESSION); exit;
		    if(isset($_SESSION['excluded_bysearch_results']) && count($_SESSION['excluded_bysearch_results'])) {
			    $sql .= ' AND id NOT IN('.implode(',', $_SESSION['excluded_bysearch_results']).')';
				/*if(isset($_SESSION['search']) && strlen($_SESSION['phone_id_search'])) {
				    $sql .= " AND phoneId='$_SESSION[phone_id_search]'";
				} */
			}
		break;
		
		case 3: /* Test Results */
		    if(isset($_SESSION['test_results']) && count($_SESSION['test_results'])) {
			    $sql .= ' AND id IN('.implode(',', $_SESSION['test_results']).')'; 
				//print_r($_SESSION['test_results']); exit;
			}
		break;
	}
	
    $filtered_results = array();
	$result = execute_query($sql);
	while($row=mysql_fetch_assoc($result)) {
	    if(strlen($_POST['location'])) {
			if(strcasecmp($_POST['location'], get_location_from_misdn($row['phoneId'])) !=0) {
				continue;
			}
		}
		$filtered_results[] = $row['id'];
	}
	if(!count($filtered_results)) {
	    $filtered_results[]=0;
	}
	$_SESSION['filtered_results'] = $filtered_results; 
}

function unset_search_vars() 
{
    session_unregister('search');
    session_unregister('exclude_results'); 
    session_unregister('excluded_bysearch_results');
    session_unregister('test_results');
    session_unregister('filtered_results');
    session_unregister('pics');
		session_unregister('unique_idlist');
}

?>

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
if(!function_exists('execute_query')) {
    exit();
}
$sql = "SELECT * FROM mresult WHERE surveyId='$surveyId'";
$result = execute_query($sql);

// The variable below wipes out any value of this type
$_SESSION['search'] = true;
//$_SESSION['phone_id_search'] = $_POST['phoneId'];
//$_SESSION['total_result_set'] = mysql_num_rows($result);
$_SESSION['exclude_results'] = array(); 
$_SESSION['excluded_bysearch_results'] = array(); 
$_SESSION['test_results'] = array();

while($row = mysql_fetch_assoc($result)) 
{ 
    if(strlen($_POST['phoneId'])) { 
        if(!preg_match("/^($_POST[phoneId])$/", $row['phoneId'])) {
            $_SESSION['exclude_results'][] = $row['id']; 
			$_SESSION['excluded_bysearch_results'][] = $row['id'];
            continue;
        } 
    }

    $form = unserialize($row['form']); 
    $data = $form['data'];
    $uploads = $form['uploads']; 

    $matched = 1;
    foreach($data as $item) 
    {  
		/* exclude test results */
		if(preg_match('/radio/i', $item['type'])) {  
		    if(preg_match('/^is\sthis\sa\stest/i', $item['field']) && preg_match('/^yes$/i', $item['value'])) { 
			    $_SESSION['exclude_results'][] = $row['id'];
				$_SESSION['test_results'][] = $row['id'];  
				continue;
			}
		}
		foreach($search_vl as $key=>$val) 
        {
            if(preg_match("/^($item[code])$/", $key) || preg_match("/^($item[code])_[0-9]+$/", $key)) 
            { 
                $checkbox_selected = 0;
                if(preg_match("/checkbox/i", $item['type'])) 
				{
				    if(!strlen($item['value'])) {
					    $matched = 0;
					    break;
					}
                    $values = preg_split("/\n+/", $item['value']);  
		            $my_s_val = explode(",", $val);
		            $inters_c = array_intersect($my_s_val, $values); 
		            if(is_array($inters_c) && (count($inters_c) == count($my_s_val))) {
                        $checkbox_selected = 1;
			            $matched = 1; 
                        break;
                    } 
					$matched = 0;
                }
                /* do basic match for input fields*/
                else { 
                    if(preg_match("/data/i", $item['type'])) {
                        if(!preg_match("/($val)/i", $item['value']))
                            $matched = 0;
                    }
                    elseif(!preg_match("/^($val)$/i", $item['value'])) {
                         $matched = 0;
                    }
                    break;
                }					  
            }
            if($matched || $checkbox_selected) 
                break;
        }
    }
    if($matched && count($uploads)) { 
        foreach($search_vl as $key=>$val) 
        {
            $uploaded = 0;
            foreach($uploads as $upload) 
            {   
                if(in_array($key,  array_values($upload)) && strlen($upload['value'])) { 
                    $uploaded = 1;
                    break 1;
                }
            }
            if(preg_match("/^::UPLOADED::$/", $val) && !$uploaded)
                $matched = 0;
					
            if(preg_match("/^::NOT_UPLOADED::$/", $val) && $uploaded)
                $matched = 0; 	
                
		    if(!$matched) break;
                       						   
        }
    }
    if(!$matched) {
        $_SESSION['exclude_results'][] = $row['id'];
		$_SESSION['excluded_bysearch_results'][] = $row['id'];
        continue;
    }
    if(count($uploads)>1) $more_pics = true;
    $ctr = 1;
    $location = get_location_from_misdn($row['phoneId']);

    foreach($uploads as $f) {
        $picture_name = $location.'_'.$row['phoneId'].'_'.$row['id'].($more_pics ? '_'.$ctr++ : '').'.png';
        if(strlen($f['value']) && file_exists(MOBILE_UPLOADS_DIR.'/'.$f['value'])) {
            $_SESSION['pics'][] = array('file_name'=>$f['value'], 'unique_filename'=>$picture_name); 
        }
    }
} 
session_unregister('filtered_results');
if(!count($_SESSION['test_results'])) {
    $_SESSION['test_results'][]=0;
} 
header('Location: mresults.php?surveyId='.$surveyId);
exit();

?>

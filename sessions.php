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

function validate_user($username, $password) 
{
	$username = mysql_real_escape_string($username);
	$password = mysql_real_escape_string($password);
	$sql = "SELECT * FROM admin WHERE username='$username' AND password=PASSWORD('$password')"; 
	if(!($result = mysql_query($sql))) { 
		return -1;
	}

	if(!mysql_num_rows($result)) {
		return -2;
	}
	return mysql_fetch_assoc($result);
}

/*
 * Returns -1 on failure, the session ID on success
 */

function start_session($userid, $type='ADMINISTRATOR') 
{
	global $_DEFAULT_SESSION_LENGTH, $session_id;
	/* Lock */
	$session_id = get_unique_string();
	if($session_id == -1) {
		return -1;
    }		
	$exp = time() + $_DEFAULT_SESSION_LENGTH * 60;

	$sql = "INSERT INTO sessions(session_id, expiry_time, userid, type) ".
		"VALUES('$session_id', '$exp', '$userid', '$type')";

	if(!mysql_query($sql)) {
		return -1;
	}
	setcookie("session_id", $session_id);
	return $session_id;
}

function validate_session()
{
   global $session_id, $usertype;
   $now = time();  
   $sql = "SELECT * FROM sessions WHERE session_id='$session_id'"; 
   if(!($result=mysql_query($sql))) 
   { 
	  print 'Authentication error';
	  exit;
   }
   if(!mysql_num_rows($result)) {
	  header('Location: index.php');
	  exit();  
   }
   $row = mysql_fetch_assoc($result); 
   if($now > $row['expiry_time']) 
   { 
	  header('Location: index.php');
	  exit();
   }
   $usertype = $row['type'];
}

/* GLOBALS['usertype'] is set when  validate_session() is called */
function admin_user() 
{
    if(!preg_match("/^ADMINISTRATOR$/", $GLOBALS['usertype']) && !preg_match("/^LIMITED_USER$/", $GLOBALS['usertype'])) 
	{
	     die('Can not determine user type');
	}
	return preg_match("/^ADMINISTRATOR$/", $GLOBALS['usertype']);
}

function check_admin_user() 
{
    if(admin_user()) {
	     return;
	}
    header('Location: msurveys.php');
	exit();
}

/*
 * Returns -1 on error, 0 on success
 */
function delete_session() 
{
    global $session_id;
    $sql = "DELETE FROM sessions WHERE session_id='$session_id'";
    if(!mysql_query($sql)) {		
        die('ERROR: can not end session');
    }
    /* Delete old sessions as well */
    $now = time();
    $sql = "DELETE FROM sessions WHERE $now > expiry_time";
    if(!mysql_query($sql)) {
       die('ERROR: can not end session');
    }
    setcookie("session_id", "", time() - 3600);
    return 0;
}

function get_unique_string()
{
    $string="";
    for($c = 0; $c < 30; $c++) {
       $string .= mt_rand(1, 16009);
    }
    return md5($string);
}

/*
 * get details of logged in user
 */
function get_user_details() 
{ 
    global $session_id;
    $sql = "SELECT userid FROM sessions WHERE session_id='$session_id'";
    if(!($result = mysql_query($sql))) { 
       die(mysql_error());
    }
    if(!mysql_num_rows($result)) {
       return 0;
    }
    $row = mysql_fetch_row($result);
    $sql = "SELECT * FROM admin WHERE id=$row[0]";
    
    if(!($result = mysql_query($sql))) {
        die(mysql_error());
    }	
    return mysql_fetch_assoc($result); 
}

function get_user_id() 
{
    $user = get_user_details();
	if(empty($user)) {
	    die('ERROR: Can not get user ID');
	}
	return $user['id'];
}

function get_user_from_id($id) 
{
   $sql = "SELECT * FROM admin WHERE id=$id";
   $result = execute_query($sql);
   if(!mysql_num_rows($result)) {
      die('No details');
   }
   return mysql_fetch_assoc($result);
}

function get_user_from_username($username) 
{
   $sql = "SELECT * FROM admin WHERE username='$username'";
   $result = execute_query($sql);
   if(!mysql_num_rows($result)) {
      die('No details');
   }
   return mysql_fetch_assoc($result);
}

/* 
 * Checks if current user is "super" administrator
 * must be called from admin/
 */
function check_super_privilege() 
{
    global $superuser;
    $user = get_user_details();
    if(strcmp($user["username"], $superuser) != 0) 
	{
       show_message('Permission denied', 'You do not have the necessary privileges', '#FF0000');
    }	 
}

function su($user_id=0) 
{
    global $superuser, $sukey;
    if(!$user_id) {
       $row = get_user_from_username($superuser); 
	   $user_id = $row['id']; 
    } 
    $user_details = get_user_from_id($user_id); 
    $myinfo = get_user_details();
  
    if(!strcmp($myinfo["username"], $user_details["username"])) {
       show_message("Already logged in", "You are already logged in as: $myinfo[names] ($myinfo[username])", "#FF0000");
    } 
    $allowed_su_admins = explode(",", $user_details["su"]); 
    if(($myinfo['username'] != $superuser) && !in_array($myinfo["id"], $allowed_su_admins)) {
        show_message("Permission denied", "You are not authorized to login as this person", "#FF0000");
    }
    if(!$user_details['active']) { 
       show_message("Account disabled", "The account you are trying to log into is disabled!", "#FF0000");  
    }
    $rand = generate_rand_str(50);
    $auth = md5($sukey.$rand); 
    $sql = "UPDATE admin SET sustr='$rand' WHERE id=$myinfo[id]"; 
    execute_nonquery($sql);
    delete_session();
    header("Location: index.php?admin=$myinfo[id]&login=$user_id&auth=$auth&su=login");
    exit();
}

?>

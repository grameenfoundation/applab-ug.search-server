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

if(count($_POST)) {
  $_POST = strip_form_data($_POST);
	extract(escape_form_data($_POST));
} 

if(isset($_POST[cancel])){
	goto("admins.smsrules.php");
}

$httpVars = array();

if(isset($_POST['addsmsrule'])){
	$keyword="";
	$shortcode="";
	if(preg_match('/shortcode_routing/', $routing_policy)){
		if(!preg_match('/\d{3,}/',$keyword_shortcode)){
			$errors = "Shortcode must be atleast 3 digits and consist of only numbers<br />";
		}
		$shortcode = $keyword_shortcode;
	}
	else{
		if(!preg_match('/[0-9a-zA-Z]{3,}+/',$routing_policy)){
			$errors = "Keyword contain invalid characters<br />";
		}
		$keyword = $keyword_shortcode;
	}
	
	if(strlen($newBnumber) && preg_match('/\D+/' ,$newBnumber)){
		$errors .= "The Bnumber specified is invalid. It should consist of numbers only<br />";
	}
	
	if(!preg_match('/http:\/\/[\w\.\-_]+/i', $url)){
		$errors .= "The url specified is invalid<br />";
	}
	
	for($k=1; $k<=10; $k++){
		if(strlen($_POST["variable$k"])){
			if(!preg_match('/^[0-9a-zA-Z]+$/', $_POST["variable$k"])){
				$errors .="Invalid characters in Variable: '".$_POST["variable$k"]."'<br />";
			}
			elseif(!strlen($_POST["value$k"])){
				$errors .="Please specify a value for '".$_POST["variable$k"]."' Variable <br />";
			}
			else{
				$httpVars[$_POST["variable$k"]] = $_POST["value$k"];
			}
		}
	}
	
	if(!strlen($errors)){
		$httpVars = serialize($httpVars);
		$sql = "INSERT INTO smsForward(description, notes, shortCode, keyword, newBnumber, createDate, url, method, httpVars) values 
						('$description', '$notes', '$shortcode', '$keyword', '$newBnumber', now(), '$url', '$method', '$httpVars' )";
		execute_update($sql);
		goto("admins.smsrules.php");
	}
}

if(strlen($errors)) {
  $errors = "<br/>$errors<br/>";
}

/* menu highlight */
$page = 'users';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title><?= TITLE ?></title>
<link rel="stylesheet" type="text/css" href="styles/style.css" />
<script type="text/javascript" src="basic.js"></script>
</head>

<body>
<table width="790" border="0" align="center" cellpadding="0" cellspacing="0" class="main">
     <!--DWLayoutTable-->
     <tr>
          <td width="790" height="124" valign="top"><? include('top.php') ?></td>
     </tr>
     <tr>
          <td height="404" valign="top"><table width="100%" border="0" cellpadding="0" cellspacing="0" class="border">
               <!--DWLayoutTable-->
               <tr>
                    <td height="22" colspan="3" align="center" valign="middle" class="caption">Administrators</td>
                    </tr>
               
								<tr><td width="15" height="17">&nbsp;</td>
                         <td width="770"><? require 'admins.menu.php' ?></td>
                         <td width="15">&nbsp;</td>
                    </tr>
										
               <tr>
                    <td height="450" width="20">&nbsp;</td>
                    <td valign="top">	
										<form method="post" action="admins.addsmsrules.php">
											<fieldset>
												<legend>Incoming Routing Details</legend>
													<table width="646" border="0" cellpadding="0" cellspacing="0">
													<tr>
															<td width="35" height="23"></td>
															<td width="185"></td>
															<td ></td>
															<td width="30"></td>
														</tr>
													<tr>
															<td width="35" ></td>
															<td width="185"></td>
															<td colspan="2"  class="error">
                                                            <?if(strlen($errors)) echo $errors;?>                                                            </td>
															<td width="30"></td>
														</tr>
													<tr>
															<td height="26"></td>
															<td align="right" valign="middle">Description:&nbsp;&nbsp;</td>
															<td colspan="2"><input type="text" name="description" class="input" size="45" value="<?=$description?>"/></td>
															<td></td>
														</tr>
														<tr>
															<td></td>
															<td align="right" valign="middle">Notes:&nbsp;&nbsp;</td>
															<td colspan="2"><textarea type="text" name="notes" class="input" style="width: 300px; height: 60px" ><?=$notes?></textarea></td>
															<td></td>
														</tr>
														<tr>
															<td height="26"></td>
															<td align="right" valign="middle">Select Routing to use:&nbsp;&nbsp;</td>
															<td colspan="3"><select name="routing_policy" style="width: 303px" onchange="this.options[this.selectedIndex].value == 'keyword_routing' ? document.getElementById('b_link').style.visibility='visible': document.getElementById('b_link').style.visibility='hidden'" >
																		<option value="shortcode_routing" <?=($routing_policy=='shortcode_routing')? 'selected="selected"' : ""?>>Shortcode Routing</option>
																		<option value="keyword_routing" <?=($routing_policy=='keyword_routing')? 'selected="selected"' : ""?> >Keyword Routing</option>
															</select>&nbsp;<span onclick="shB_f()" onmouseover="this.style.color='#0033FF'" onmouseout="this.style.color='black'" id="b_link" class ="bnum"style="visibility:<?= $routing_policy == 'keyword_routing' ? 'visible' : 'hidden'?>" >Re-write Bnumber</span>
															<input type="hidden" id="newBnumber" name="newBnumber" value="<?= $newBnumber?>"/>
															</td>
															<!--td></td-->
														</tr>
														<tr>
															<td height="26"></td>
															<td align="right" valign="middle">Enter keyword or Shortcode:&nbsp;&nbsp;</td>
															<td colspan="2"><input type="text" name="keyword_shortcode" id="keyword_shortcode" class="input" value="<?=$keyword_shortcode?>" size="45"/></td>
															<td></td>
														</tr>
														<tr>
															<td height="26"></td>
															<td align="right" valign="middle">Destination URL:&nbsp;&nbsp;</td>
															<td colspan="2"><input type="text" name="url" id="url" class="input"value="<?=$url?>" size="45"/></td>
															<td></td>
														</tr>
														<tr>
															<td height="26"></td>
															<td align="right" valign="middle">HTTP method:&nbsp;&nbsp;</td>
															<td colspan="2"><select name = "method" style="width: 303px">
																	<option value = "0" <?=($method==0)? 'selected:"selected"': ''?>>GET</option>
																	<option value = "1" <?=($method==1)? 'selected:"selected"': ''?>>POST</option>
															</select></td>
															<td></td>
														</tr>
														<tr>
															<td height="26"></td>
															<td align="right" valign="middle">HTTP Variables:&nbsp;&nbsp;</td>
															<td><b>Variable</td>
														    <td><b>Value</td>
													      <td></td>
														</tr>
														<?
														for($i=0;$i<10;$i++){
															$j=$i+1;
															echo"
															<tr>
															<td height=\"26\"></td>
															<td></td>
															<td><input class=\"input\" type=text name=\"variable$j\" value=\"".$_POST["variable$j"]."\"/></td>
															<td><input class=\"input\" type=text name=\"value$j\"/ value=\"".$_POST["value$j"]."\"></td>
															<td></td>
															</tr>
															";
														}
														?>
														<tr>
                              <td height="59"></td>
															<td></td>
															<td colspan="2"><input type="submit" name="addsmsrule" class="button" value="Create">
															<input name="cancel" type="submit" class="button" id="cancel" value="Cancel" /></td>
                              <td></td>
														</tr>
														<tr>
                              <td height="59"></td>
															<td colspan="4" valign="middle" align="center" id="note"><span style="color: #CC0000">Please Note:</span> You may specify the special values <b>MESSAGE</b>, <b>MSISDN</b> and <b>BNUMBER</b>, and these shall be filled automatically with the <b>SMS</b>, the <b>Sender Phone number</b> and the <b>Short code</b> respectively.</td>
															
														</tr>
														<tr>
                              <td height="25"></td>
															<td></td>
															<td></td>
															<td></td>
                              <td></td>
														</tr>
													</table>
										  </fieldset>
										</form>
										</td>
										<td>&nbsp;</td>
               </tr>
               <tr>
                    <td height="12"></td>
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

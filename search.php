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
include("display.php");

session_start();

dbconnect();
validate_session(); 
check_admin_user();

if(isset($_POST['cancel'])) {
  goto("keywords.php");
}
if(isset($_POST['submit']) OR isset($_POST['csvfile'])) {
      $keyword = mysql_real_escape_string(trim($_POST['keyword']));
	  //$sql = "SELECT * FROM keyword WHERE keyword LIKE '%$keyword%'"; 
	  if(isset($_POST['submit'])){ $sql = "SELECT keyword.*, DATE_FORMAT(createDate, '%d/%m/%Y %T') AS created, 
              DATE_FORMAT(updated, '%d/%m/%Y %T') AS updated FROM keyword WHERE keyword LIKE '%$keyword%'";
	  }else{
	  $sql = "SELECT category.name, keyword.keyword , keyword.attribution, keyword.keywordAlias, keyword.optionalWords, "
                ."keyword.content FROM keyword LEFT JOIN category ON category.id=keyword.categoryId WHERE keyword.keyword LIKE '%$keyword%'";
	  }
	  if($categoryId) {
	      $sql .= " AND keyword.categoryId=$categoryId";
	      $cat = $categoryId;
	  }
	  if($languageId) {
	      $sql .= " AND keyword.languageId=$languageId";
	  }
	  if($attribution) {
		  $attribution = strtolower($attribution);
	      $sql .= " AND LOWER(keyword.attribution) LIKE '%$attribution%'";
	  }
	  $sql .= " ORDER BY keyword.createDate DESC"; 
	  $result = execute_query($sql);
	  if(!mysql_num_rows($result)) {
	      $msg = 'No matching keyword(s) found';
	  }
	  else { 
	   	if(isset($_POST['submit'])){  $_SESSION['search']= array('cat'=>$cat, 'sql'=>$sql, 'keyword'=>$_POST['keyword']);
	     		header("Location: matched.php");
		 	exit();
	   	}else{
			$result = execute_query($sql);
			$filename = "Search-".date("Ymd");
			header('Content-Type: application/csv');
        		header('Content-Disposition: attachment; filename="'.$filename.'.csv"');
        		print "Keyword, Attribution, Keyword Alias, Category, Optional Words, Content\r\n";

        		while($row = mysql_fetch_assoc($result)) {
                		//Eliminate the delimiter, and return carriages from content that potentially malform our CSV
                		$format = array("\n", "\r", ",");
                		$row[content] = str_replace($format," ", $row[content]);
                		print "$row[keyword],$row[attribution],$row[keywordAlias],$row[name],$row[optionalWords],$row[content]\r\n";
        		}

        		exit();
		
		}
	  }
} 
	

if(isset($errors)) {
  $errors = "<br/>$errors<br/>";
}
/* menu highlight */
$page = 'keywords';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title><?= TITLE ?></title>
<link rel="stylesheet" type="text/css" href="styles/style.css" />
</head>

<body>
<table width="790" border="0" align="center" cellpadding="0" cellspacing="0" class="main">
     <!--DWLayoutTable-->
     <tr>
          <td width="790" height="124" valign="top"><? include('top.php') ?></td>
     </tr>
     <tr>
        <td height="310" valign="top">
		   			<table width="100%" border="0" cellpadding="0" cellspacing="0" class="border">
		   						<!--DWLayoutTable-->
		   						<tr>
		   									<td height="22" colspan="3" align="center" valign="middle" class="caption">Search For Keyword </td>
              			</tr>
		   						<tr>
		   									<td height="26" colspan="3" valign="top"><? require 'keywords.menu.php' ?></td>
                    			</tr>
		   						<tr>
		   									<td width="76" height="23">&nbsp;</td>
               						<td width="609">&nbsp;</td>
               						<td width="103">&nbsp;</td>
               			</tr>
		   						<tr>
		   									<td height="165">&nbsp;</td>
               						<td valign="top">
               									<fieldset><legend>Search</legend>
									<table width="100%" border="0" cellpadding="0" cellspacing="0">
												<!--DWLayoutTable-->
												<tr>
															<td width="102" height="18">&nbsp;</td>
               												<td width="83">&nbsp;</td>
               												<td width="297">&nbsp;</td>
               												<td width="125">&nbsp;</td>
               									</tr>
												<tr>
															<td height="24">&nbsp;</td>
															<td>&nbsp;</td>
															<td valign="top" style="color: #006600"><?= $msg ?></td>
															<td>&nbsp;</td>
												</tr>
												<form method="post">
												<tr>
															<td height="26">&nbsp;</td>
               												<td align="right" valign="middle">Keyword:&nbsp;&nbsp;</td>
                  			<td valign="middle">
                  																<input name="keyword" type="text" class="input" id="keyword" value="<?= $_POST['keyword'] ?>" size="36" /></td>
                  			<td>&nbsp;</td>
               									</tr>
												<tr>
															<td height="26">&nbsp;</td>
               												<td align="right" valign="middle">Category:&nbsp;&nbsp;</td>
                  											<td valign="middle"><select name="categoryId" class="input" id="categoryId" style="width: 250px">
																		<option value="0"></option>
																		<?= get_table_records('category', $categoryId) ?>
															</select></td>
                  			<td>&nbsp;</td>
               									</tr>
												<tr>
															<td height="26">&nbsp;</td>
               												<td align="right" valign="middle">Attribution:&nbsp;&nbsp;</td>
                  											<td valign="middle"><input name="attribution" type="text" class="input" id="attribution" value="<?= $_POST['attribution'] ?>" size="36" />
															</td>
                  			<td>&nbsp;</td>
               									</tr>
												<tr>
															<td height="26">&nbsp;</td>
               												<td align="right" valign="middle">Language:&nbsp;&nbsp;</td>
                  											<td valign="middle"><select name="languageId" class="input" id="languageId" style="width: 250px">
																		<option value="0"></option>
																		<?= get_table_records('language', $languageId) ?>
															</select></td>
                  			<td>&nbsp;</td>
               									</tr>																								
												<tr>
															<td height="44">&nbsp;</td>
               												<td>&nbsp;</td>
               												<td valign="middle"><input name="submit" type="submit" class="button" id="submit" value="Search" />
           																									<input name="cancel" type="submit" class="button" id="cancel" value="Cancel" /></td>
                  			<td>&nbsp;</td>

                                                  <td><input name="csvfile" type="submit" class="button" id="csvfile" value="Download Keywords" /></td>
                  			<td>&nbsp;</td>
               									</tr>
												</form>
												<tr>
															<td height="33">&nbsp;</td>
               												<td>&nbsp;</td>
               												<td>&nbsp;</td>
               												<td>&nbsp;</td>
               												</tr>
               									</table>
									</fieldset>               			</td>
               						<td>&nbsp;</td>
               						</tr>
		   						<tr>
		   									<td height="57">&nbsp;</td>
               						<td>&nbsp;</td>
               						<td>&nbsp;</td>
               						</tr>
		   				
           			    </table></td>
     </tr>
    
     
        <tr>
          <td height="30" valign="top"><? include('bottom.php') ?></td>
     </tr>
</table>
</body>
</html>

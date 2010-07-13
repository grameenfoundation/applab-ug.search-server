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
<? require 'clock.php'; ?>
<table width="790" height="124" border="0" cellpadding="0" cellspacing="0">
               <!--DWLayoutTable-->
               <tr>
                    <td width="790" height="90" valign="top">
                         <table width="790" height="90" border="0" cellpadding="0" cellspacing="0" background="images/header.jpg"
					style="background-repeat: no-repeat">
                              <!--DWLayoutTable-->
                              <tr>
                                   <td width="283" height="34"></td>
                                        <td width="306"></td>
                                        <td width="191"></td>
                                        <td width="10"></td>
                              </tr>
                              
                              
                              <tr>
                                 <td height="26">&nbsp;</td>
                                 <td align="right" valign="middle" style="color: #333; font-size: 10px">
								 Welcome, <span style="color: #FF3300"><? echo $_COOKIE['usernames'] ?>!</span> </td>
                                        <td align="center" valign="middle">
									       <div style="font-size: 10px; color: #666666">
									   <?= '<span id="ClockTime" onclick="clockToggleSeconds()">'.date('dS M, Y g:i:s A').'</span>' ?>
                                              </div></td>
                                        <td>&nbsp;</td>
                              </tr>
                              <tr>
                                 <td height="34">&nbsp;</td>
                                 <td>&nbsp;</td>
                                 <td>&nbsp;</td>
                                 <td>&nbsp;</td>
                              </tr>
                         </table></td>
                    </tr>
               <tr>
                    <td height="30" valign="middle">
                <?  				 
				  $list = '<table border="0">
				   <tr>';			 
			      foreach($menu as $item) { 
				     if($item['admin'] && !preg_match("/^ADMINISTRATOR$/", $GLOBALS['usertype'])) {
					     continue;
					 }
				     if($item['page'] == $page) 
					 { 
				        $list .=
					    '<td height="20" valign="middle" style="padding: 0px 5px 0px 5px" bgcolor="#AF062F">
					       <a href="'.$item['link'].'" title="'.$item['title'].'" 
						    style="font-family: Tahoma, Arial; color: #FFFFFF; font-size: 11px; font-weight: bold; text-decoration: none">
						    '.$item['label'].'</a>
					    </td>';				    
				     }
				     else 
					 {
				         $list .= 
				          '<td height="20" valign="middle" style="padding: 0px 5px 0px 5px" bgcolor="#669933"
				              onmouseover="this.style.backgroundColor=\'#AF062F\'"
				              onmouseout="this.style.backgroundColor=\'#669933\'">
				              <a href="'.$item['link'].'" title="'.$item['title'].'" target="_parent" 
					             style="font-family: Tahoma, Arial; color: #FFFFFF; font-size: 11px; font-weight: bold; text-decoration: none">'.
					          $item['label'].'</a>
				          </td>';
				     }
                 }
				 $list .= '</tr></table>';
				 print $list
			   ?>			   
			   </td>
                 </tr>
               
          </table>
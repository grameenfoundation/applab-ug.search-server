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
include('constants.php');
if(!is_array($message)) {
 exit();
}
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
          <td height="347" valign="top"><table width="100%" border="0" cellpadding="0" cellspacing="0" class="border">
               <!--DWLayoutTable-->
               <tr>
                    <td height="22" colspan="3" align="center" valign="middle" class="caption">Status Message </td>
                    </tr>
               <tr>
                    <td width="99" height="53">&nbsp;</td>
                         <td width="593">&nbsp;</td>
                         <td width="96">&nbsp;</td>
                    </tr>
               <tr>
                    <td height="191">&nbsp;</td>
                         <td valign="top">
                              <fieldset>
                              <legend><?= $message["title"] ?></legend>
					        <table width="100%" border="0" cellpadding="0" cellspacing="0">
					             <!--DWLayoutTable-->
					             <tr>
					                  <td width="13" height="21">&nbsp;</td>
           <td width="575">&nbsp;</td>
           <td width="22">&nbsp;</td>
          </tr>
					             <tr>
					                  <td height="121">&nbsp;</td>
            <td valign="middle" style="font-size: 12px"><?= $message["message"]; ?></td>
           <td>&nbsp;</td>
          </tr>
					             <tr>
					                  <td height="24">&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
          </tr>
				              </table>
			             </fieldset></td>
                         <td>&nbsp;</td>
                    </tr>
               <tr>
                    <td height="79">&nbsp;</td>
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

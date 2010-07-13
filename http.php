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
// Returns an array of the format:
// 	$array[headers] => an array of headers
//	$array[response] => response text
//	$array[sessiondump] => construction of session
function http_submit_request($url, $http_vars, $method)
{
	$data = http_get_formdata($http_vars);

	if(!preg_match("/^http[s]*:\/\/[^\/]+.*$/", $url)) {
		_seterror("Invalid URL $url");
		return -1;
	}

        /* We do not tamper with cURL if we're using paymentech */
        if(function_exists('curl_init')) {
                $curl_path = "/usr/bin/curl";
                $res = http_try_curl_elite($url, $data, $curl_path, $method);
                return $res;
        } else {
		_seterror("cURL not found");
		return -1;
	}
}

function http_do_post($url, $http_vars, $paymentech=0, $payment_opts="")
{
	global $CHASE_PAYMENT_CLEARNING;

	$CHASE_PAYMENT_CLEARNING = 0;
	if($paymentech == 1) {
		$CHASE_PAYMENT_CLEARNING = 1;
		$url = $payment_opts[paymentech_url];
	}

	// Authorize.Net
	if($paymentech == 2) {
		$CHASE_PAYMENT_CLEARNING = 2;
		$url = $payment_opts[authorizenet_url];
	}

        $data = http_get_formdata($http_vars, $paymentech, $payment_opts);

/*
return '<?xml version="1.0" encoding="UTF-8"?>
<Response>
	<NewOrderResp>
		<IndustryType/>
		<MessageType>AC</MessageType>
		<MerchantID>329482983</MerchantID>
		<TerminalID>23423</TerminalID>
		<CardBrand>EC</CardBrand>
		<AccountNum>239482398423</AccountNum>
		<OrderID>394283</OrderID>
		<TxRefNum>2938429384</TxRefNum>
		<TxRefIdx>23948</TxRefIdx>
		<ProcStatus>0</ProcStatus>
		<ApprovalStatus>1</ApprovalStatus>
		<RespCode>39</RespCode>
		<AVSRespCode>39</AVSRespCode>
		<CVV2RespCode>29</CVV2RespCode>
		<AuthCode>39</AuthCode>
		<StatusMsg>Server error processing this request because its fatal</StatusMsg>
		<RespMsg>You do not have sufficient funds to process this request</RespMsg>
		<CustomerRefNum>34ref</CustomerRefNum>
		<CustomerName>name</CustomerName>
		<RespTime>39238</RespTime>
	</NewOrderResp>
</Response>';
*/
	/* We do not tamper with cURL if we're using paymentech */
        if(function_exists('curl_init')) {
		$curl_path = "/usr/bin/curl";
		$res = http_try_curl($url, $data, $paymentech, $payment_opts, $curl_path);
		return $res;
	}

        /* Otherwise try sockets */
	$data = http_get_formdata($http_vars, $paymentech, $payment_opts);
	if(!preg_match("/^(http[s]*):\/\/([^\/]+)(.*)$/i", $url, $matches)) {
		_seterror("Invalid submission URL: $url");
		return -1;
	}

	$protocol = strtolower($matches[1]);
	$hostname = strtolower($matches[2]);
	$folder = $matches[3];
	$port = 0;

	$matches = array();
	if(preg_match("/^[^:]+[:]([0-9]+)$/", $hostname, $matches)) {
		$port = $matches[1];
		$hostname = preg_replace("/[:][0-9]+$/", "", $hostname);
	}

	if(!strlen($folder))
		$folder = "/";

	if(!strlen($hostname)) {
		_seterror("Invalid submission URL hostname: $url");
		return -1;
        }

	if(strcmp($protocol, "http") && strcmp($protocol, "https")) {
		_seterror("Invalid submission URL protocol: $url");
                return -1;
	}

	if(!$port) {
		if(!strcmp($protocol, "http"))
			$port = "80";
		else
			$port = "443";
	}

	$errno = 0;
	$errstr = "";
	if(!strcmp($protocol, "https")) {
		$fp = fsockopen("ssl://".$hostname, $port, &$errno, &$errstr);
	} else {
		$fp = fsockopen($hostname, $port, &$errno, &$errstr);
	}

	if(!$fp) {
		_seterror($errstr);
		return -1;
	}

	if($paymentech == 1) {
		$request =
"POST ".$folder." HTTP/1.0\r\n".
"Host: ".$hostname."\r\n".
"MIME-Version: 1.0\r\n".
"User-Agent: YBS Chase Paymentech Clearing Agent ( http://www.yo.co.ug/ )\r\n".
"Connection: close\r\n".
"Content-Type: application/PTI43\r\n".
"Content-Length: ".strlen($data)."\r\n".
"Content-transfer-encoding: text\r\n".
"Request-number: 1\r\n".
"Document-type: Request\r\n".
"Merchant-id: ".$payment_opts[paymentech_mid]."\r\n\r\n".
$data;
	} else if($paymentech == 2) {
		$request =
"POST ".$folder." HTTP/1.1\r\n".
"Host: ".$hostname."\r\n".
"User-Agent: YBS Authorize.Net Module ( http://www.yo.co.ug/ )\r\n".
"Connection: close\r\n".
"Content-Type: application/x-www-form-urlencoded\r\n".
"Content-Length: ".strlen($data)."\r\n\r\n".
$data;
	} else {
		$request =
"POST ".$folder." HTTP/1.1\n".
"Host: ".$hostname."\n".
"User-Agent: YBS PayPal Module ( http://www.yo.co.ug/ )\n".
"Connection: close\n".
"Content-Type: application/x-www-form-urlencoded\n".
"Content-Length: ".strlen($data)."\n\n".
$data;
	}

	fwrite($fp, $request);

	$headers = array();

	$file_handle = fopen("/tmp/response.bin", "ab");
	fwrite($file_handle, ("--------[REQUEST]--------\n".$request."\n--------[RESPONSE]--------\n"));

	/* Get the headers */
	while(!feof($fp)) {
		$line = fgets($fp);
		$line = str_replace("\r\n", "\n", $line);
		fwrite($file_handle, $line);

		$line = chop($line);

		if(!strlen($line))
			break;

                if(preg_match("/^([^:]+):\s*(.+)$/", $line, $matches)) {
                        $headers[strtolower($matches[1])] = $matches[2];
                } else if(preg_match("/^HTTP\/1\.1\s([0-9]+)\s(.+)$/", $line, $matches)) {
                        $headers[httpstatuscode] = $matches[1];
                        $headers[httpstatusmsg] = strtolower($matches[2]);
                }
	}

	/* If we were sent a 100 continue, we need to parse the rest of the final headers */
	if(strcmp($headers[httpstatuscode], "100") == 0) {
	        while(!feof($fp)) {
        	        $line = fgets($fp);
                	$line = str_replace("\r\n", "\n", $line);
	                fwrite($file_handle, $line);

        	        $line = chop($line);

                	if(!strlen($line))
	                        break;

        	        if(preg_match("/^([^:]+):\s*(.+)$/", $line, $matches)) {
                	        $headers[strtolower($matches[1])] = $matches[2];
	                } else if(preg_match("/^HTTP\/1\.1\s([0-9]+)\s(.+)$/", $line, $matches)) {
        	                $headers[httpstatuscode] = $matches[1];
                	        $headers[httpstatusmsg] = strtolower($matches[2]);
	                }
        	}
	}

        /* Get the response */
        if(isset($headers["content-length"])) {
                $response = fgets($fp, $headers["content-length"]+1);
		fwrite($file_handle, $response);
        } else {
                /* Chunked response, get length in Hex */
       	        $line = fgets($fp);
		fwrite($file_handle, $line);
               	$line = chop($line);
                $length = hexdec($line);

       	        $response = fgets($fp, $length+1);
		fwrite($file_handle, $response);
        }

	/* Read any more stuff there might be */
	while(!feof($fp)) {
		$line = fgets($fp);
		fwrite($file_handle, $line);
	}

	fwrite($file_handle, $line."\n");
	fclose($file_handle);

	/* Close the connection */
	fclose($fp);

        if(strcmp($headers[httpstatuscode], "200") && strcmp($headers[httpstatusmsg], "ok")) {
                _seterror("HTTP Error: ".$headers[httpstatuscode]." ".$headers[httpstatusmsg]." ".$response);
                return -1;
        }

	return $response;
}

function http_try_curl_elite($url, $data, $curl_path, $method)
{
	if($method == 1) {
		$http_method = "-G";
	} else {
		$http_method = "";
	}

        $curl_cmd = $curl_path." -v --include -m 180 $http_method -d \"$data\" $url 2>>/dev/null"; ///tmp/response.bin";
        exec($curl_cmd, $return_array);

	$response = "";
        foreach($return_array as $relem) {
		$relem = str_replace("\r\n", "\n", $relem);
                $response .= $relem."\n";
        }

	$mixed_content = explode("\n\n", $response);
	$headers = explode("\n", $mixed_content[0]);
	$body = $mixed_content[1];

	$retarray = array();

	$retarray[headers] = $headers;
	$retarray[response] = $body;

	$mtc_res = preg_match("/^(http[s]*):\/\/([^\/]+)(.*)$/i", $url, $matches);

	if($mtc_res) {
	        $protocol = strtolower($matches[1]);
        	$hostname = strtolower($matches[2]);
	        $folder = $matches[3];
        	$port = 0;

	        $matches = array();
        	if(preg_match("/^[^:]+[:]([0-9]+)$/", $hostname, $matches)) {
                	$port = $matches[1];
	                $hostname = preg_replace("/[:][0-9]+$/", "", $hostname);
        	}

	        if(!strlen($folder))
        	        $folder = "/";

	        if(!strlen($hostname)) {
        	        _seterror("Invalid submission URL hostname: $url");
                	return -1;
	        }

	        if(!$port) {
        	        if(!strcmp($protocol, "http"))
                	        $port = "80";
	                else
        	                $port = "443";
	        }

		if($method != 1) {
	                $request =
"> POST ".$folder." HTTP/1.1\n".
"> Host: ".$hostname."\n".
"> User-Agent: YBS HTTP Module ( http://www.yo.co.ug/ )\n".
"> Connection: close\n".
"> Content-Type: application/x-www-form-urlencoded\n".
"> Content-Length: ".strlen($data)."\n> \n> ".
$data."\n\n";
		} else {
			$request =
"> GET ".$folder."?".$data." HTTP/1.1\n".
"> Host: ".$hostname."\n".
"> User-Agent: YBS HTTP Module ( http://www.yo.co.ug/ )\n".
"> Connection: close\n> \n\n\n";
		}

		foreach($headers as $hddr) {
			$request .= "< ".$hddr."\n";
		}

		$request .= "< \n";

		$bddys = explode("\n", $body);
		foreach($bddys as $bddy) {
			$request .= "< ".$bddy."\n";
		}
	}

	$retarray[sessiondump] = $request;

	return $retarray;
}

function http_try_curl($url, $data, $paymentech_value, $payment_opts, $curl_path)
{
/*        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_STDERR, $file_handle);

	if($paymentech_value == 1) {
		$http_headers = array(
				"MIME-Version: 1.0",
				"User-Agent: YBS Chase Paymentech Clearing Agent ( http://www.yo.co.ug/ )",
				"Connection: close",
				"Content-Type: application/PTI43",
				"Content-transfer-encoding: text",
				"Request-number: 1",
				"Document-type: Request",
				"Merchant-id: ".$payment_opts[paymentech_mid]);
	} else if($paymentech_value == 2) {
		$http_headers = array(
				"User-Agent: YBS Authorize.Net Module ( http://www.yo.co.ug/ )",
				"Connection: close");
	} else {
		$http_headers = array(
                                "User-Agent: YBS PayPal Module ( http://www.yo.co.ug/ )",
                                "Connection: close");
	}

	curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headers);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
                _seterror("HTTP Error while submitting details: ".curl_error($ch));
		curl_close($ch);
                return -1;
        }

	curl_close($ch);
*/

        if($paymentech_value == 1) {
                $http_headers = array(
                                "MIME-Version: 1.0",
                                "User-Agent: YBS Chase Paymentech Clearing Agent ( http://www.yo.co.ug/ )",
                                "Connection: close",
                                "Content-Type: application/PTI43",
                                "Content-transfer-encoding: text",
                                "Request-number: 1",
                                "Document-type: Request",
                                "Merchant-id: ".$payment_opts[paymentech_mid]);
        } else if($paymentech_value == 2) {
                $http_headers = array(
                                "User-Agent: YBS Authorize.Net Module ( http://www.yo.co.ug/ )",
                                "Connection: close");
        } else {
                $http_headers = array(
                                "User-Agent: YBS PayPal Module ( http://www.yo.co.ug/ )",
                                "Connection: close");
        }

	$header_string = "";
	foreach($http_headers as $hdr) {
		$header_string .= " -H \"$hdr\"";
	}

	$curl_cmd = $curl_path." -v -m 180 $header_string -d \"$data\" $url 2>>/dev/null"; ///tmp/response.bin";
	exec($curl_cmd, $return_array);

	foreach($return_array as $relem) {
		$response .= $relem;
	}

        return $response;
}

/* Stupid stuff failed to work - uses fopen() */
function http_do_post_old($url, $http_vars)
{
	$data = http_get_formdata($http_vars);
	$params = array('http' => array('method' => 'POST',
					'header' => 'Content-type: application/x-www-form-urlencoded',
					'content' => $data));

	$ctx = stream_context_create($params);
	$fp = fopen($url, 'r', false, $ctx);

	if (!$fp) {
		_seterror("Error opening $url: <b>$php_errormsg</b>");
		return -1;
	}

	$meta_data = stream_get_meta_data($fp);
	$content_length = 0;

	foreach($meta_data[wrapper_data] as $k) {
		if(preg_match("/^Content-Length:\s+(.+)$/", $k, $matches)) {
			$content_length = $matches[1];
			break;
		}
	}

	$response = fgets($fp);

	if ($response === false) {
		_seterror("Error reading from server: <b>$php_errormsg</b>");
		fclose($fp);
		return -1;
        }

	print $response;
	exit();

	fclose($fp);
	return $response;
}

function http_add_parameter(&$http_var, $name, $param)
{
	$http_var[$name] = $param;
}

function http_get_formdata($http_var, $paymentech=0, $payment_opts="")
{
	$data = "";

	if(!$paymentech) {
		foreach($http_var as $k => $v) {
			if(strlen($data))
				$data .= "&";

			$data .= $k."=".urlencode($v);
		}
	} else if($paymentech == 1) {
		if(!strlen($payment_opts[paymentech_mid])) {
			die("System error: MID not provided");
		}

		if(!strlen($payment_opts[paymentech_orderid])) {
			die("System error: OrderId not provided");
		}

		if(strlen($payment_opts[cvv])) {
			$paymentech_cvv_info = "\r\n		<CardSecVal>".$payment_opts[cvv]."</CardSecVal>";
		} else {
			$paymentech_cvv_info = "";
		}

		if(!strlen($payment_opts["ADDRESS2"])) {
			die("System error: ADDRESS2 not provided");
		}

		$paymentech_message_type = "AC";
		if($payment_opts[paymentech_validateonly] == 1) {
			$paymentech_message_type = "A";
		}

		$paymentech_expiry_date = substr($http_var["EXPDATE"], 0, 2).substr($http_var["EXPDATE"], 4, 2);
		/* Paymentech doesn't like long comments? */
		$http_var["DESC"] = "Account Recharge";

		/* Failsafe mechanisms */
		$http_var["ZIP"] = http_ensure_length($http_var["ZIP"], 10);
		$http_var["STREET"] = http_ensure_length($http_var["STREET"], 30);
		$payment_opts["ADDRESS2"] = http_ensure_length($payment_opts["ADDRESS2"], 30);
		$http_var["CITY"] = http_ensure_length($http_var["CITY"], 20);
		$http_var["STATE"] = http_ensure_length($http_var["STATE"], 2);
		$http_var["COUNTRYCODE"] = http_ensure_length($http_var["COUNTRYCODE"], 2);
		$payment_opts[paymentech_orderid] = http_ensure_length($payment_opts[paymentech_orderid], 22);
		$http_var["DESC"] = http_ensure_length($http_var["DESC"], 64);

		$data = '<?xml version="1.0" encoding="UTF-8"?>'."\r\n".
'<Request>'."\r\n".
'	<NewOrder>'."\r\n".
'		<IndustryType>EC</IndustryType>'."\r\n".
'		<MessageType>'.$paymentech_message_type.'</MessageType>'."\r\n".
'		<BIN>'.$payment_opts[paymentech_bin].'</BIN>'."\r\n".
'		<MerchantID>'.$payment_opts[paymentech_mid].'</MerchantID>'."\r\n".
'		<TerminalID>'.$payment_opts[paymentech_terminalid].'</TerminalID>'."\r\n".
'		<AccountNum>'.$http_var["ACCT"].'</AccountNum>'."\r\n".
'		<Exp>'.$paymentech_expiry_date.'</Exp>'."\r\n".
'		<CurrencyCode>840</CurrencyCode>'."\r\n".
'		<CurrencyExponent>2</CurrencyExponent>'.$paymentech_cvv_info."\r\n".
'		<AVSzip>'.$http_var["ZIP"].'</AVSzip>'."\r\n".
'		<AVSaddress1>'.$http_var["STREET"].'</AVSaddress1>'."\r\n".
'		<AVSaddress2>'.$payment_opts["ADDRESS2"].'</AVSaddress2>'."\r\n".
'		<AVScity>'.$http_var["CITY"].'</AVScity>'."\r\n".
'		<AVSstate>'.$http_var["STATE"].'</AVSstate>'."\r\n".
'		<AVScountryCode>'.$http_var["COUNTRYCODE"].'</AVScountryCode>'.
		/*<CustomerProfileFromOrderInd>A</CustomerProfileFromOrderInd>*/""."\r\n".
'		<OrderID>'.$payment_opts[paymentech_orderid].'</OrderID>'."\r\n".
'		<Amount>'.($http_var["AMT"]*100).'</Amount>'."\r\n".
'		<Comments>'.$http_var["DESC"].'</Comments>'."\r\n".
'	</NewOrder>'."\r\n".
'</Request>';
	} else if($paymentech == 2) {
		$authorizenet_array = array();
		http_add_parameter(&$authorizenet_array, "x_login", $payment_opts[authorizenet_apilogin]);
		http_add_parameter(&$authorizenet_array, "x_tran_key", $payment_opts[authorizenet_transkey]);
		http_add_parameter(&$authorizenet_array, "x_type", "AUTH_CAPTURE");
		http_add_parameter(&$authorizenet_array, "x_amount", $http_var["AMT"]);
		http_add_parameter(&$authorizenet_array, "x_card_num", $http_var["ACCT"]);
		http_add_parameter(&$authorizenet_array, "x_exp_date", $http_var["EXPDATE"]);
		http_add_parameter(&$authorizenet_array, "x_version", "3.1");
		http_add_parameter(&$authorizenet_array, "x_method", "CC");
		http_add_parameter(&$authorizenet_array, "x_test_request", "FALSE");
		http_add_parameter(&$authorizenet_array, "x_description", $http_var["DESC"]);
		http_add_parameter(&$authorizenet_array, "x_first_name", $http_var["FIRSTNAME"]);
                http_add_parameter(&$authorizenet_array, "x_last_name", $http_var["LASTNAME"]);
                http_add_parameter(&$authorizenet_array, "x_address", $http_var["STREET"]);
                http_add_parameter(&$authorizenet_array, "x_city", $http_var["CITY"]);
                http_add_parameter(&$authorizenet_array, "x_state", $http_var["STATE"]);
                http_add_parameter(&$authorizenet_array, "x_zip", $http_var["ZIP"]);
                http_add_parameter(&$authorizenet_array, "x_country", $http_var["COUNTRYCODE"]);
                http_add_parameter(&$authorizenet_array, "x_email", $payment_opts[customer_email]);
		http_add_parameter(&$authorizenet_array, "x_cust_id", $payment_opts[customer_id]);
		http_add_parameter(&$authorizenet_array, "x_delim_data", "TRUE");
		http_add_parameter(&$authorizenet_array, "x_delim_char", "|");
		http_add_parameter(&$authorizenet_array, "x_relay_response", "FALSE");

		$data = http_get_formdata($authorizenet_array);
	}

	return $data;
}

function http_ensure_length($var, $maxlength)
{
	if(strlen($var) > $maxlength) {
		$newvar = substr($var, 0, $maxlength);

		return $newvar;
	}

	return $var;
}

function http_parse_url_response($response_str)
{
        $intial=0;
        $nvpArray = array();

	global $CHASE_PAYMENT_CLEARNING;

	if($CHASE_PAYMENT_CLEARNING != 1) {
		if($CHASE_PAYMENT_CLEARNING == 2) {
			// Authorize.Net
			$response_array = preg_split("/\|/", $response_str);
			$nvpArray = array();

			if(count($response_array) == 1) {
				/* HTML based error */
	                        $nvpArray["ACK"] = "ERROR";
        	                $nvpArray["L_LONGMESSAGE0"] = $response_str;

				return $nvpArray;
			}

			/* foreach($response_array as $k => $v) {
				print ($k+1)." => $v<br>";
			}*/

			$nvpArray["TIMESTAMP"] = "Unavailable from Authorize.Net";
			if($response_array[0] == 1) {
				/* Accepted */
                                $nvpArray["ACK"] = "SUCCESS";
                                $nvpArray["TRANSACTIONID"] = "Authorize.Net TXID: ".$response_array[6];
                                $nvpArray["TRANSACTIONID"] .= " Authorize.Net AUTHCODE: ".$response_array[4];
                                $nvpArray["AVSCODE"] = $response_array[5];
			} else {
				/* Error */
	                        $nvpArray["ACK"] = "ERROR";
        	                $nvpArray["L_LONGMESSAGE0"] = $response_array[3];

                	        $otherPossibleErrors = authorizenet_get_possible_error_string($response_array);

                        	if(strlen($nvpArray["L_LONGMESSAGE0"])) {
	                                $nvpArray["L_LONGMESSAGE0"] .= "<br>".$otherPossibleErrors;
        	                } else {
                	                $nvpArray["L_LONGMESSAGE0"] = $otherPossibleErrors;
                        	}

			}

			return $nvpArray;
		}

	        while(strlen($response_str)){
        	        $keypos= strpos($response_str, '=');
                	$valuepos = strpos($response_str,'&') ? strpos($response_str,'&'): strlen($response_str);
	                $keyval=substr($response_str,$intial,$keypos);
        	        $valval=substr($response_str,$keypos+1,$valuepos-$keypos-1);
                	$nvpArray[urldecode($keyval)] =urldecode( $valval);
	                $response_str = substr($response_str,$valuepos+1,strlen($response_str));
        	}
	} else {
		global $http_xml_current_param, $http_xml_parsed_info;

		$http_xml_current_param = "";
		$http_xml_parsed_info = array();

		if (!($xmlparser = xml_parser_create())) {
			$nvpArray["ACK"] = "ERROR";
			$nvpArray["L_LONGMESSAGE0"] = "Failed to create XML parser";

			return $nvpArray;
		}

		xml_set_element_handler($xmlparser, "http_xml_parser_start_tag", "http_xml_parser_end_tag");
		xml_set_character_data_handler($xmlparser, "http_xml_parser_tag_contents");

		if(!xml_parse($xmlparser, $response_str, true)) {
			$nvpArray["ACK"] = "ERROR";
			$nvpArray["L_LONGMESSAGE0"] = "Failed to parse reply XML: ".xml_error_string(xml_get_error_code($xmlparser));

			xml_parser_free($xmlparser);

			return $nvpArray;
		}

		xml_parser_free($xmlparser);

		$http_xml_parsed_info["TIMESTAMP"] = $http_xml_parsed_info["RESPTIME"];
		if($http_xml_parsed_info["PROCSTATUS"] != 0) {
			$http_xml_parsed_info["ACK"] = "ERROR";
			$http_xml_parsed_info["L_LONGMESSAGE0"] = $http_xml_parsed_info["STATUSMSG"];

			$otherPossibleErrors = paymentech_get_possible_error_string($http_xml_parsed_info);

			if(strlen($http_xml_parsed_info["L_LONGMESSAGE0"])) {
				$http_xml_parsed_info["L_LONGMESSAGE0"] .= "<br>".$otherPossibleErrors;
			} else {
				$http_xml_parsed_info["L_LONGMESSAGE0"] = $otherPossibleErrors;
			}
		} else {
			if($http_xml_parsed_info["APPROVALSTATUS"] != 1) {
				$http_xml_parsed_info["ACK"] = "ERROR";
				if(strlen($http_xml_parsed_info["RESPMSG"])) {
					$http_xml_parsed_info["L_LONGMESSAGE0"] = $http_xml_parsed_info["RESPMSG"];
				} else {
					$http_xml_parsed_info["L_LONGMESSAGE0"] = $http_xml_parsed_info["STATUSMSG"];
				}

				$otherPossibleErrors = paymentech_get_possible_error_string($http_xml_parsed_info);

				if(strlen($http_xml_parsed_info["L_LONGMESSAGE0"])) {
					$http_xml_parsed_info["L_LONGMESSAGE0"] .= "<br>".$otherPossibleErrors;
				} else {
					$http_xml_parsed_info["L_LONGMESSAGE0"] = $otherPossibleErrors;
				}
			} else {
				$http_xml_parsed_info["ACK"] = "SUCCESS";
				$http_xml_parsed_info["TRANSACTIONID"] = "PAYMENTECH TXREF: ".$http_xml_parsed_info["TXREFNUM"];
				$http_xml_parsed_info["TRANSACTIONID"] .= " PAYMENTECH AUTHCODE: ".$http_xml_parsed_info["AUTHCODE"];
				$http_xml_parsed_info["AVSCODE"] = $http_xml_parsed_info["TXREFIDX"];
			}
		}

		return $http_xml_parsed_info;
	}

        return $nvpArray;
}

function http_xml_parser_start_tag($parser, $name, $attribs)
{
	global $http_xml_current_param;

	$http_xml_current_param = $name;
}

function http_xml_parser_end_tag($parser, $name)
{
}

function http_xml_parser_tag_contents($parser, $data)
{
	global $http_xml_current_param, $http_xml_parsed_info;

	if(strlen($http_xml_parsed_info[$http_xml_current_param])) {
		$http_xml_parsed_info[$http_xml_current_param] .= $data;
	} else {
		$http_xml_parsed_info[$http_xml_current_param] = $data;
	}
}

function authorizenet_get_possible_error_string($responseArray)
{
	$avsRespCode = trim($responseArray[5]);
	$resultString = authorizenet_get_avs_errorstring($avsRespCode);

        if(strlen($resultString)) {
		$resultString = "&bull; ".$resultString;
		if(strlen($responseArray[6])) {
			$resultString .= "<br>&bull; Transaction Reference Number: ".$responseArray[6];
		}

                $resultString = "<font color=\"blue\"><br>See also the following additional information which may have caused the error. Please avail this additional information to your support contact for further tracing:</font><br><br>".$resultString;
        }

        return $resultString;
}

function paymentech_get_possible_error_string($responseArray)
{
	/* Check the CVV2RespCode value and AVSRespCode value */
	$cvv2RespCode = trim($responseArray["CVV2RESPCODE"]);
	$avsRespCode = trim($responseArray["AVSRESPCODE"]);

	$resultString = "";
	$cvvErrorString = paymentech_get_cvv_errorstring($cvv2RespCode);

	if(strlen($cvvErrorString)) {
		$resultString = "&bull; ".$cvvErrorString;
	}

	$avsErrorString = paymentech_get_avs_errorstring($avsRespCode);
	if(strlen($avsErrorString)) {
		if(strlen($resultString)) {
			$resultString .= "<br>&bull; ".$avsErrorString;
		} else {
			$resultString = $avsErrorString;
		}
	}

	if(strlen($resultString)) {
		$resultString .= "<br>&bull; Transaction Reference Number: ".$responseArray["TXREFNUM"];
	}

	if(strlen($resultString)) {
		$resultString = "<font color=\"blue\"><br>See also the following additional information which may have caused the error. Please avail this additional information to your support contact for further tracing:</font><br><br>".$resultString;
	}

	return $resultString;
}

function paymentech_get_cvv_errorstring($matchCase)
{
/*	if(strcmp($matchCase, "M") == 0) {
		return "Match";
	}
*/
	if(strcmp($matchCase, "N") == 0) {
		return "No match for CVV2 value provided";
	}

	if(strcmp($matchCase, "P") == 0) {
		return "CVV2 Number not processed";
	}

	if(strcmp($matchCase, "S") == 0) {
		return "CVV2 Should have been present";
	}

	if(strcmp($matchCase, "U") == 0) {
		return "Unsupported by Issuer/Issuer unable to process request for CVV2";
	}

	if(strcmp($matchCase, "I") == 0) {
		return "Invalid CVV2";
	}

	if(strcmp($matchCase, "Y") == 0) {
		return "Invalid CVV2";
	}

	if(!strlen($matchCase)) {
		return "CVV2 processing not applicable (non-Visa)";
	}

	return "";
}

function paymentech_get_avs_errorstring($matchCase)
{
/*	if(strcmp($matchCase, "M") == 0) {
		return "Match";
	}
*/

	if(strcmp($matchCase, "N") == 0) {
		return "No match";
	}

	if(strcmp($matchCase, "P") == 0) {
		return "AVS information not processed";
	}

	if(strcmp($matchCase, "S") == 0) {
		return "AVS information should have been present";
	}

	if(strcmp($matchCase, "U") == 0) {
		return "Unsupported by Issuer/Issuer unable to process request for AVS verification";
	}

	if(strcmp($matchCase, "I") == 0) {
		return "Invalid AVS information";
	}

	if(strcmp($matchCase, "Y") == 0) {
		return "Invalid AVS information";
	}

	if(!strlen($matchCase)) {
		return "AVS verification not applicable (non-Visa)";
	}

	if(strcmp($matchCase, "1") == 0) {
		return "No address supplied";
	}

	if(strcmp($matchCase, "2") == 0) {
		return "Bill-to address did not pass Auth Host edit checks";
	}

	if(strcmp($matchCase, "3") == 0) {
		return "AVS not performed";
	}

	if(strcmp($matchCase, "4") == 0) {
		return "Issuer does not participate in AVS";
	}

	if(strcmp($matchCase, "R") == 0) {
		return "Issuer does not participate in AVS";
	}

	if(strcmp($matchCase, "5") == 0) {
		return "Edit-error - AVS data is invalid";
	}

	if(strcmp($matchCase, "6") == 0) {
		return "System unavailable or time-out";
	}

	if(strcmp($matchCase, "7") == 0) {
		return "Address information unavailable";
	}

	if(strcmp($matchCase, "8") == 0) {
		return "Transaction Ineligible for AVS";
	}

	if(strcmp($matchCase, "9") == 0) {
		return "Zip Match / Zip4 Match / Locale match";
	}

	if(strcmp($matchCase, "A") == 0) {
		return "Zip Match / Zip 4 Match / Locale no match";
	}

	if(strcmp($matchCase, "B") == 0) {
		return "Zip Match / Zip 4 no Match / Locale match";
	}

	if(strcmp($matchCase, "C") == 0) {
		return "Zip Match / Zip 4 no Match / Locale no match";
	}

	if(strcmp($matchCase, "D") == 0) {
		return "Zip No Match / Zip 4 Match / Locale match";
	}

	if(strcmp($matchCase, "E") == 0) {
		return "Zip No Match / Zip 4 Match / Locale no match";
	}

	if(strcmp($matchCase, "F") == 0) {
		return "Zip No Match / Zip 4 No Match / Locale match";
	}

	if(strcmp($matchCase, "G") == 0) {
		return "No match at all for AVS information";
	}

	if(strcmp($matchCase, "H") == 0) {
		return "Zip Match / Locale match";
	}

	if(strcmp($matchCase, "J") == 0) {
		return "Issuer does not participate in Global AVS";
	}

	if(strcmp($matchCase, "JA") == 0) {
		return "International street address and postal match";
	}

	if(strcmp($matchCase, "JB") == 0) {
		return "International street address match. Postal code not verified.";
	}

	if(strcmp($matchCase, "JC") == 0) {
		return "International street address and postal code not verified.";
	}

	if(strcmp($matchCase, "JD") == 0) {
		return "International postal code match. Street address not verified.";
	}

/*
	if(strcmp($matchCase, "M1") == 0) {
		return "Cardholder name matches";
	}

	if(strcmp($matchCase, "M2") == 0) {
		return "Cardholder name, billing address, and postal code matches";
	}

	if(strcmp($matchCase, "M3") == 0) {
		return "Cardholder name and billing code matches";
	}

	if(strcmp($matchCase, "M4") == 0) {
		return "Cardholder name and billing address match";
	}
*/

	if(strcmp($matchCase, "M5") == 0) {
		return "Cardholder name incorrect, billing address and postal code match";
	}

	if(strcmp($matchCase, "M6") == 0) {
		return "Cardholder name incorrect, billing address matches";
	}

	if(strcmp($matchCase, "M7") == 0) {
		return "Cardholder name incorrect, billing address matches";
	}

	if(strcmp($matchCase, "M8") == 0) {
		return "Cardholder name, billing address and postal code are all incorrect";
	}

	if(strcmp($matchCase, "N3") == 0) {
		return "Address matches, ZIP not verified";
	}

/*
	if(strcmp($matchCase, "N4") == 0) {
		return "Address and ZIP code match (International only)";
	}
*/

	if(strcmp($matchCase, "N5") == 0) {
		return "Address not verified (International only)";
	}

	if(strcmp($matchCase, "N6") == 0) {
		return "Address and ZIP code match (International only)";
	}

	if(strcmp($matchCase, "N7") == 0) {
		return "ZIP matches, address not verified";
	}

/*
	if(strcmp($matchCase, "N8") == 0) {
		return "Address and ZIP code match (International only)";
	}
*/

	if(strcmp($matchCase, "UK") == 0) {
		return "Unknown AVS error";
	}

/*
	if(strcmp($matchCase, "X") == 0) {
		return "Zip Match / Zip 4 Match / Address Match";
	}
*/
	if(strcmp($matchCase, "Y") == 0) {
		return "AVS Lookup not Performed";
	}

	if(strcmp($matchCase, "Z") == 0) {
		return "Zip Match / Locale no match";
	}

	return "";
}

function authorizenet_get_avs_errorstring($matchCase)
{
	if(strcmp($matchCase, "A") == 0) {
		return "Address (Street) matches, ZIP does not";
	}

	if(strcmp($matchCase, "B") == 0) {
		return "Address information not provided for AVS check";
	}

	if(strcmp($matchCase, "E") == 0) {
		return "AVS error";
	}

        if(strcmp($matchCase, "G") == 0) {
                return "Non-U.S. Card Issuing Bank";
        }

        if(strcmp($matchCase, "N") == 0) {
                return "No Match on Address (Street) or ZIP";
        }

        if(strcmp($matchCase, "P") == 0) {
                return "AVS not applicable for this transaction";
        }

        if(strcmp($matchCase, "R") == 0) {
                return "Retry . System unavailable or timed out";
        }
        if(strcmp($matchCase, "S") == 0) {
                return "Service not supported by issuer";
        }

        if(strcmp($matchCase, "U") == 0) {
                return "Address information is unavailable";
        }

        if(strcmp($matchCase, "W") == 0) {
                return "Nine digit ZIP matches, Address (Street) does not";
        }

        if(strcmp($matchCase, "X") == 0) {
                return "Address (Street) and nine digit ZIP match";
        }

        if(strcmp($matchCase, "Y") == 0) {
                return "Address (Street) and five digit ZIP match";
        }

        if(strcmp($matchCase, "Z") == 0) {
                return "Five digit ZIP matches, Address (Street) does not";
        }

	return "";
}

function _seterror($error)
{
        global $_YBSW_ERROR_MSG;

        $_YBSW_ERROR_MSG = $error;
}

function _geterror()
{
        global $_YBSW_ERROR_MSG;

	return $_YBSW_ERROR_MSG;
}
?>

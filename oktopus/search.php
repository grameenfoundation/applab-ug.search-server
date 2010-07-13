<?php
define("ROOT", "/var/www/html");
include ROOT."/mobile/constants.php";
include ROOT."/mobile/display.php";
include ROOT."/mobile/functions.php";

dbconnect();
if(isset($_GET['log'])){
$content = "Cached content. Inbox access log.";
	 $sql = "INSERT INTO OktopusSearchLog (handset_id, interviewee_id, location, query, content, server_entry_time, handset_submit_time) VALUES ('".$_GET['handset_id']."', '".$_GET['interviewee_id']."', '".mysql_real_escape_string($_GET['location'])."', '".$_GET['keyword']."', '".$content."', NOW(), '".mysql_real_escape_string($_GET['handset_submit_time'])."')";
 if(!(mysql_query($sql))) {
            //TODO
        }
$sql = "INSERT INTO hit(keyword, phone, request, reply) VALUES('".$_GET['keyword']."', '".$_GET['handset_id']."', '".$_GET['keyword']."', '".$content."')";
        if(!(mysql_query($sql))) {
            //TODO
        }

exit();
}

if($word = $_GET['keyword']) {
	$results = keyword_get_content($word);
	if(is_array($results)) {
		$error = 0;
		print $results['content'];
		if(strlen($results['attribution']) > 0) print "\n\nAttribution: ".$results['attribution'];
		print "\n\nLast Updated: ".$results['updated'];
	} else {	
		$error = 0;
		print "No content could be associated with your keyword:\n'$word'\n";
		print "\nTry downloading an updated list of keywords and repeating your search.";
		print "\nIf your problem persists, please report this error.\n";
	}
}else{
	$error = 1;
	print "An internal error occurred. Please call 0712954253 for assistance.";
}

if($error == 0){

	$sql = "INSERT INTO OktopusSearchLog (handset_id, interviewee_id, location, query, content, server_entry_time, handset_submit_time) VALUES ('".$_GET['handset_id']."', '".$_GET['interviewee_id']."', '".mysql_real_escape_string($_GET['location'])."', '".$word."', '".$results['content']."', NOW(), '".mysql_real_escape_string($_GET['handset_submit_time'])."')";
	if(!(mysql_query($sql))) {
		//TODO
  	}
	
	 $sql = "INSERT INTO hit(keyword, phone, request, reply) VALUES('$word', '".$_GET['handset_id']."', '$word', '".$results['content']."')";
        if(!(mysql_query($sql))) {
            //TODO
        }
	
}

exit();

?>

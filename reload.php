<?php 

require 'vendor/autoload.php'; // include Composer goodies
require_once 'db.php';

$db = new db();
$db->connect();  

$doc = array();

//Get ip
$ip = filter_input(INPUT_POST, 'ip');
$doc['ip'] = $ip;

rtsp($doc);

echo 'ok';

function rtsp($doc) {	
	$user = 'admin';
	$pw = 'admin';
	$req = '11';
	$link = 'rtsp://'.$user.':'.$pw.'@'.$doc['ip'].'/'.$req;
	$cmd = "ffmpeg -stimeout 2000000 -i "
		.$link." "
		."-f image2 -vframes 1 -y "
		."/var/www/html/ipcam/pic/".$doc['ip'].".jpeg 2>&1"; 		
	$output = shell_exec($cmd);	
	echo $output;
	$doc['link'] = $link;
	$doc['capture_timestamp'] = new MongoDB\BSON\UTCDateTime(strtotime(date('Y-m-d H:i:s')) * 1000);  
	$doc['capture_result'] = setDocResult($output);
											
	return $doc;	
}

function update_db($doc) {
	//Connect to database
	$collection = $client->ipcam->capture_list;				
	
	$collection->updateOne(
		[ '_id' => $entry['_id'] ],
		[ '$set' => $doc ]
	);
	
	unset($client);
	unset($collection);	
}	

function setDocResult($output) {
	if(strpos($output,'Connection timed out') > 0) {
		return 'Connection timeout';		//Host offline	
	} else if(strpos($output,'Connection refused') > 0) {
		return 'Connection refused';		//Host online but no RSTP
	} else if(strpos($output,'400 Bad Request') > 0) {
		return '400 Bad Request';			//RTSP ok but bad request	
	} else if(strpos($output,'401 Unauthorized') > 0) {
		return '401 Unauthorized';			//RTSP & request ok but incorrect password
	} else if(strpos($output,'Invalid data found') > 0) {
		return 'Invalid data found'; 		//Invalid data found
	} else if(strpos($output,'Output #0, image2, to') > 0){
		return 'Success'; 					//Connect success
	}
	return $output;
}
<?php 

require 'vendor/autoload.php';			
$manager = new MongoDB\Driver\Manager('mongodb://127.0.0.1:27017');

$ip = filter_input(INPUT_POST, "ip");
$action = filter_input(INPUT_POST, "action");
$country = filter_input(INPUT_POST, "country");

if($action === "Love") {
	$data = ["love" => "love"];
	$return = "Un-Love";
} else {
	$data = ["love" => ""];
	$return = "Love";
}
try {
	$bulk = new MongoDB\Driver\BulkWrite();

	$bulk->update(
		[ 'ip' => (float)$ip ],
		[ '$set' => $data ],
		['multi' => false, 'upsert' => true]
	);

	if($country == 'ALL') {
		$collection = 'ipcam.capture_list_all';
	} else if($country == 'HK') {
		$collection = 'ipcam.capture_list_hk'; 
	} else if($country == 'SG') {
		$collection = 'ipcam.capture_list_sg'; 
	}

	$manager->executeBulkWrite($collection, $bulk);

} catch (Exception $e) {
	echo $e;
}

echo $return;
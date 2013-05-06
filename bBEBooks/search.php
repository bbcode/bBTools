<?php 
require_once 'functions.php';
require_once 'amazon_api.php';
require_once 'amazon_result.php';

//turn on full error reports for development purposes - should be turned off for production environment
error_reporting(E_ALL);
//error_reporting(0);
//let's parse some request details
$debug = false;
if (isset($_GET["debug"])) {
	if ($_GET["debug"] == "true") {
    	$debug = true;
	}
}
$keyword = "";
$format = "";
$bbcode = "false";
if (isset($_REQUEST["keyword"])) {
	$keyword = $_REQUEST["keyword"];
	$keyword = preg_replace('/\\D/', '', $keyword);
}

if (isset($_REQUEST["format"])) {
	$format = $_REQUEST["format"];
}

if (isset($_REQUEST["bbcode"])) {
	$bbcode = $_REQUEST["bbcode"];
}

$Amazon = new Amazon($debug);
$b = $Amazon->getResults($keyword, $format);
$b['Completed'] = true;

header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
if ($bbcode == "true") {
header('Content-type: text/plain');
	echo ($b["Items"][0]->BBCode);
} else {
	header('Content-type: application/json');
	if (isset($_GET["callback"])) {
		echo $_GET['callback'] . '('.json_encode($b).')';
	} else {
		echo json_encode($b);
	}
}
?>

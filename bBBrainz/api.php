<?php
require_once('../config.php');
header('Content-Type: application/javascript; charset=utf-8');
header('Access-Control-Allow-Origin: https://'.$site_name_this_is_stupid_nobody_cares.'/');
require_once('common.php');

if(empty($_GET['artist']) || empty($_GET['album']))
	die('artist, album required');

$results = search_mb_release($_GET['artist'], $_GET['album']);
$mbid = $results[0]['id'];
$data = process_release($mbid);

$output = json_encode(Array(
	'error' => 'false',
	'type' => 'music',
	'image' => $data['image'],
	'description' => $data['description'],
	'artist' => $data['artist'],
	'album' => $data['title'],
	'year' => $data['year']
));

if(!empty($_GET['callback']))
	echo "$_GET[callback]($output)";
else
	echo $output;


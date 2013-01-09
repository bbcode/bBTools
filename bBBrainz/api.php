<?php
header('Content-Type: application/javascript; charset=utf-8');
header('Access-Control-Allow-Origin: *');
require_once('common.php');
$output = null;

if(empty($_GET['action']) || $_GET['action'] == 'fetch') {
	if((empty($_GET['artist']) || empty($_GET['album'])) && empty($_GET['mbid']))
		die('artist and album or mbid required');

	if(empty($_GET['mbid'])) {
		$results = search_mb_release($_GET['artist'], $_GET['album']);
		$mbid = $results[0]['id'];
	} else {
		$mbid = $_GET['mbid'];
	}

	$data = process_release($mbid);
	$output = Array(
		'error' => 'false',
		'type' => 'music',
		'image' => $data['image'],
		'description' => $data['description'],
		'artist' => $data['artist'],
		'album' => $data['title'],
		'year' => $data['year'],
		'release_year' => $data['release_year']
	);
} elseif($_GET['action'] == 'search') {
	if(empty($_GET['artist']) || empty($_GET['album']))
		die('artist and album required');

	$output = Array();
	$results = search_mb_release($_GET['artist'], $_GET['album']);
	foreach($results as $release) {
		$output[] = Array(
			'mbid' => (string)$release['id'],
			'artist' => (string)$release->{'artist-credit'}->{'name-credit'}->artist->name,
			'album' => (string)$release->title,
			'trackcount' => (int)$release->{'medium-list'}->{'track-count'},
			'country' => (string)$release->country,
			'date' => (string)$release->date,
			'mburl' => $mb_release_page.$release['id']
		);
	}
}

$output = json_encode($output);
if(!empty($_GET['callback']))
	echo "$_GET[callback]($output)";
else
	echo $output;


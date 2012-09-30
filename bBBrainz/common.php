<?php
$mbapi = 'http://musicbrainz.baconseed.org/ws/2';
$mbpage = 'http://musicbrainz.org/release/';
$lastfm_key = 'fc1bd1e1c71fb7222b564e6130e3044a';
$imgur_api = 'http://api.imgur.com/2/upload.xml';
$imgur_keys = Array('9e6655dae944b92c731fcd763b7fb795', '29e4d33b086551e033d7fc07a02c5129', 'e8a9c9e5d99d81ade9c06172becbdcc8');

function makeBox($title, $content) {
	$str = "[size=3][b][color=#555555]".$title."[/color][/b][/size]\n";
	$str .= "[size=2][quote]\n";
	$str .= $content;
	$str .= "\n[/quote][/size]\n";
	return $str;
}

function makeKeyVal($key, $val) {
	return "[b]".$key.":[/b] ".$val."\n";
}

function uierror($text) {
	echo '<center>';
	echo "<font color='red'><strong>Error: $text</strong></font>";
	echo '<p><a href="index.php">Perhaps things will be different next time...</a></p>';
	echo '</center>';
	die();
}

function get_mb_release($mbid) {
	global $mbapi;
	$metafields = Array(
		'artist-credits',
		'labels',
		'discids',
		'recordings',
	);
	$metafields = implode('+', $metafields);

	$request = "$mbapi/release/$mbid?inc=$metafields";
	$release = simplexml_load_file($request);
	if($release === False)
		uierror('Entry not found for release id '. htmlspecialchars($mbid));
	assert(sizeof($release) === 1);
	$release = $release->release;
	return $release;
}

function search_mb_release($artist, $album) {
	global $mbapi;
	$artist = urlencode($artist);
	$album = urlencode($album);

	$request = "$mbapi/release/?query=artist:$artist AND release:$album";
	$results = simplexml_load_file($request);
	if($results === False)
		uierror('Could not load response.');
	$results = $results->{'release-list'}->release;
	return $results;
}

function get_lastfm_album($artist, $album) {
	// Last.fm supports mbid lookup, but they have a piss poor selection because
	// they only seem to index one release id per release group.
	global $lastfm_key;
	$request = "http://ws.audioscrobbler.com/2.0/?method=album.getinfo&api_key=$lastfm_key&artist=$artist&album=$album";
	$lastfm = simplexml_load_file($request);
	$lastfm = $lastfm->album;
	return $lastfm;
}

function send_imgur_upload($url) {
	global $imgur_api, $imgur_keys;
	$ch = curl_init($imgur_api);
	$pvars = Array('image' => $url, 'key' => $imgur_keys[array_rand($imgur_keys)]);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $pvars);
	$imgurxml = curl_exec($ch);
	curl_close($ch);
	$xmlparse = simplexml_load_string($imgurxml);
	$image = $xmlparse->links->original[0];
	return $image;
}

function process_release($mbid) {
	global $mbpage;
	$release = get_mb_release($mbid);

	$artist = $release->{'artist-credit'}->{'name-credit'}->artist->name;
	$label = $release->{'label-info-list'}->{'label-info'}->label->name;
	$title = $release->title;
	$releasedate = $release->date;

	if(preg_match('/\d{4}/', $releasedate, $year))
		$year = $year[0];
	else
		$year = '';

	$lastfm = get_lastfm_album($artist, $title);

	// Images are received in ascending dimensions.
	$coverurl = $lastfm->image[sizeof($lastfm->image)-1];
	$image = send_imgur_upload($coverurl);

	// Grab/format all discs => tracks
	$discs = Array();
	foreach($release->{'medium-list'}->{'medium'} as $disc) {
		$tracks = Array();
		foreach($disc->{'track-list'}->track as $track) {
			$number = (int)$track->number;
			$name = $track->recording->title;
			$tracks[] = "[b]${number}[/b] - $name";
		}
		$tracks = implode("\n", $tracks) . "\n";
		$discs[] = $tracks;
	}

	// Add disc numbers for multiple discs
	if(sizeof($discs) > 1) {
		foreach($discs as $i => &$disc)
			$disc = '[b]Disc '. ($i+1) .":[/b]\n$disc";
		unset($disc); // Prevent reference leakage
	}
	$tracklist = implode("\n", $discs);

	$info = '';
	$info .= makeKeyVal('Album', $title);
	$info .= makeKeyVal('Artist', $artist);
	$info .= makeKeyVal('Label', $label);
	$info .= makeKeyVal('Release Date', $releasedate);
	if($release->asin)
		$info .= makeKeyVal('Amazon', 'http://www.amazon.com/dp/'. $release->asin);
	$info .= makeKeyVal('MusicBrainz', htmlspecialchars("$mbpage$mbid"));
	$info .= makeKeyVal('Last.fm', $lastfm->url);
	$description = makeBox('Information', $info) . makebox('Track List', $tracklist);

	return Array(
		'artist' => (string)$artist,
		'title' => (string)$title,
		'image' => (string)$image,
		'year' => $year,
		'description' => $description
	);
}


<?php
require_once('common.php');

if(empty($_GET['mbid']))
	uierror('Release id is required.');
$mbid = $_GET['mbid'];

$release = get_mb_release($mbid);

$artist = $release->{'artist-credit'}->{'name-credit'}->artist->name;
$label = $release->{'label-info-list'}->{'label-info'}->label->name;
$title = $release->title;
$releasedate = $release->date;

$lastfm = get_lastfm_album($artist, $title);

// Images are received in ascending dimensions.
$coverurl = $lastfm->image[sizeof($lastfm->image)-1];
$image = send_imgur_upload($coverurl);

$tags = Array();
foreach($lastfm->toptags->tag as $tag)
	$tags[] = $tag->name;
$tags = implode(', ', $tags);

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
if($tags)
	$info .= makeKeyVal('Tags', $tags);
$info .= makeKeyVal('Label', $label);
$info .= makeKeyVal('Release Date', $releasedate);
if($release->asin)
	$info .= makeKeyVal('Amazon', 'http://www.amazon.com/dp/'. $release->asin);
$info .= makeKeyVal('MusicBrainz', htmlspecialchars("$mbpage$mbid"));
$info .= makeKeyVal('Last.fm', $lastfm->url);
$output = makeBox('Information', $info) . makebox('Track List', $tracklist);

?>
<html>
	<head>
		<title>bBBrainz</title>
		<link rel="stylesheet" type="text/css" href="style.css" />
		<script type="text/javascript">
			function selectAll(node) {
				node.focus();
				node.select();
			}
		</script>
	</head>
	<body>
		<div class="heading_info">
			<a class="index_link" href="index.php">Click to Search Again</a>
			<em class="warning">*Please ensure output is correct before posting*</em>
			<h2><?php echo $artist." - ".$title; ?></h2>
			<textarea onclick="selectAll(this);" cols="30"><?php echo $image;?></textarea>
		</div>
		<img class="cover_art" src="<?php echo $image;?>"/> 
		<textarea onclick="selectAll(this);" rows="40" cols="115">
		<?php echo $output; ?>
		</textarea> 
	</body>
</html>

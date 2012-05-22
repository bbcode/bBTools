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

$tracks = Array();
foreach($release->{'medium-list'}->{'medium'}->{'track-list'}->track as $track)
	$tracks[(int)$track->number] = $track->recording->title;

$tracktext = Array();
foreach($tracks as $number => $name)
	$tracktext[]= "[b]${number}[/b] - $name";
$tracktext = implode("\n", $tracktext) . "\n";

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
$output = makeBox('Information', $info) . makebox('Track List', $tracktext);

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

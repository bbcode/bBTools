<?php
require_once('common.php');

if(empty($_GET['mbid']))
	uierror('Release id is required.');
$mbid = $_GET['mbid'];

$metafields = Array(
	'artist-credits',
	'labels',
	'discids',
	'recordings',
);
$metafields = implode('+', $metafields);

$request = "$apiroot/release/$mbid?inc=$metafields";
$release = simplexml_load_file($request);
if($release === False)
	uierror('Entry not found for release id '. htmlspecialchars($mbid));
assert(sizeof($release) === 1);
$release = $release->release;

$artist = $release->{'artist-credit'}->{'name-credit'}->artist->name;
$label = $release->{'label-info-list'}->{'label-info'}->label->name;
$title = $release->title;
$releasedate = $release->date;

// Last.fm supports mbid lookup, but they have a piss poor selection because
// they only seem to index one release id per release group.
$request = "http://ws.audioscrobbler.com/2.0/?method=album.getinfo&api_key=$lastfm_key&artist=$artist&album=$title";
$lastfm = simplexml_load_file($request);
$lastfm = $lastfm->album;

// Images are received in ascending dimensions.
$coverurl = $lastfm->image[sizeof($lastfm->image)-1];

$ch = curl_init("http://api.imgur.com/2/upload.xml");
$pvars = Array('image' => $coverurl, 'key' => $imgur_keys[array_rand($imgur_keys)]);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $pvars);
$imgurxml = curl_exec($ch);
curl_close($ch);
$xmlparse = simplexml_load_string($imgurxml);
$image = $xmlparse->links->original[0];

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
		<script type="text/javascript">
			function selectAll(id) {
				document.getElementById(id).focus();
				document.getElementById(id).select();
			}
		</script>
	</head>
	<body>
	<center><p><b><a href="index.php">Click to Search Again</a></b></p></center>
	<center><font color="red"><i>*PLEASE ENSURE OUTPUT IS CORRECT BEFORE POSTING*</i></font></center>

	<p><center><h2><?php echo $title." - ".$artist; ?></h2></center> 
	<p><center><i><textarea id="img" onClick="selectAll('img');" cols="30"><?php echo $image;?></textarea></center></i> 
	<p><img class="thumbnail" style="padding:15px" align=left src="<?php echo $image;?>"/> 
	<textarea id="desc" onClick="selectAll('desc');" rows="40" cols="115">
	<?php echo $output; ?>
	</textarea> 
	</div>
	</body>
</html>

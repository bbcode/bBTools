<?php
$apiroot = 'http://musicbrainz.org/ws/2';
$mbpage = 'http://musicbrainz.org/release/';
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
$info .= makeKeyVal('MusicBrainz', htmlspecialchars("$mbpage$mbid"));
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
	<?php /*
		<p><center><i><textarea id="img" onClick="selectAll('img');" cols="30"><?php echo $image;?></textarea></center></i> 
		<p><img class="thumbnail" style="padding:15px" align=left src="<?php echo $image;?>"/> 
	*/ ?>
	<textarea id="desc" onClick="selectAll('desc');" rows="40" cols="115">
	<?php echo $output; ?>
	</textarea> 
	</div>
	</body>
</html>

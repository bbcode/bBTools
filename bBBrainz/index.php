<?php
$apiroot = 'http://musicbrainz.org/ws/2';
$mbpage = 'http://musicbrainz.org/release/';
$mbid = '78d49ab6-02fc-4542-8b3d-abdcb8838cb0';

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

$metafields = Array(
	'artist-credits',
	'labels',
	'discids',
	'recordings',
);
$metafields = implode('+', $metafields);

$request = "$apiroot/release/$mbid?inc=$metafields";
$release = simplexml_load_file($request);
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
$info .= makeKeyVal('MusicBrainz', "$mbpage$mbid");
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
	<center><p class="control"><b><a class="expand" href="<?php echo basename(__FILE__); ?>">Click to Search Again</a></b></p></center>
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

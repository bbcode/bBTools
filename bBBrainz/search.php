<?php
ini_set('display_errors', 'On');
require_once('common.php');

if(empty($_GET['artist']) || empty($_GET['album']))
	uierror('Artist and album names required.');

$artist = $_GET['artist'];
$album = $_GET['album'];

$request = "$apiroot/release/?query=artist:$artist AND release:$album";
$results = simplexml_load_file($request);
if($results === False)
	uierror('Could not load response.');
$results = $results->{'release-list'}->release;
?>
<html>
	<head>
		<title>bBBrainz</title>
		<link rel="stylesheet" type="text/css" href="style.css" />
	</head>
	<body>
		<table class="search_results">
			<tr class="head">
				<th></th>
				<th>Artist</th>
				<th>Album</th>
				<th>Track Count</th>
				<th>Country</th>
				<th>Release Date</th>
				<th>MusicBrainz</th>
				<th>Amazon</th>
			</tr>
			<?php foreach($results as $release) {
				echo '<tr>';
				echo '<td><a href="fetch.php?mbid='. $release['id'] .'"><button>Choose</button></a></td>';
				echo '<td>'. $release->{'artist-credit'}->{'name-credit'}->artist->name .'</td>';
				echo '<td>'. $release->title .'</td>';
				echo '<td>'. $release->{'medium-list'}->{'track-count'} .'</td>';
				echo '<td>'. $release->country .'</td>';
				echo '<td>'. $release->date .'</td>';
				echo '<td><a target="_blank" href="'. $mbpage.$release['id'] .'">View on MB</a></td>';
				if($release->asin)
					echo '<td><a target="_blank" href="http://www.amazon.com/dp/'. $release->asin .'">View on Amazon</a></td>';
				else
					echo '<td></td>';
				echo '</tr>';
			} ?>

		</table>
		<form action="search.php" method="GET">
			<label for="artist">Artist:</label><br/>
			<input type="text" name="artist" id="artist" size="40" value="<?php echo htmlspecialchars($artist); ?>" /><br/>
			<label for="album">Album:</label><br/>
			<input type="text" name="album" id="album" size="40" value="<?php echo htmlspecialchars($album); ?>" /><br/>
			<input type="submit" value="Try Harder" />
		</form>
	</body>
</html>

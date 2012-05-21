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
<table>
	<tr>
		<td></td>
		<td>Artist</td>
		<td>Album</td>
		<td>Track Count</td>
		<td>Country</td>
		<td>Release Date</td>
		<td>MusicBrainz</td>
		<td>Amazon</td>
	</tr>
	<?php foreach($results as $release) {
		echo '<tr>';
		echo '<td><a href="fetch.php?mbid='. $release['id'] .'">Go</a></td>';
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

<html>
	<head>
		<title>bBBrainz</title>
		<link rel="stylesheet" type="text/css" href="style.css" />
	</head>
	<body>
		<h2>bBBrainz Album Description Generator</h2>
		<h3>By name</h3>
		<form action="search.php" method="GET">
			<label for="artist">Artist:</label><br/>
			<input type="text" name="artist" id="artist" size="40" placeholder="Rebecca Black" /><br/>
			<label for="album">Album:</label><br/>
			<input type="text" name="album" id="album" size="40" placeholder="Friday" /><br/>
			<input type="submit" value="MusicBrainz Search" />
			<button name="crispy" value="bacon">I'm Feeling Crispy</button>
		</form>

		<h3>By release id</h3>
		<form action="fetch.php" method="GET">
			<label for="mbid">MusicBrainz Release Id:</label>
			<a href="http://musicbrainz.org/doc/Release" target="_blank">(?)</a><br/>
			<input type="text" name="mbid" id="mbid" size="40" placeholder="78d49ab6-02fc-4542-8b3d-abdcb8838cb0" /><br/>
			<input type="submit" value="Generate Description" />
		</form>
	</body>
</html>

<?php
header('Content-Type: text/html; charset=utf-8');
require_once('common.php');

if(empty($_GET['mbid']))
	uierror('Release id is required.');
$mbid = $_GET['mbid'];
$data = process_release($mbid);

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
			<h2><?= "$data[artist] - $data[title]" ?></h2>
			<textarea onclick="selectAll(this);" cols="30"><?= $data['image'] ?></textarea>
		</div>
		<img class="cover_art" src="<?= $data['image'] ?>"/> 
		<textarea onclick="selectAll(this);" rows="40" cols="115"><?= $data['description'] ?></textarea> 
	</body>
</html>

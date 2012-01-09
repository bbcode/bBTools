<?php
require_once('amazon-api.php');

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
$json = !empty($_REQUEST['json']);
$callback = $_REQUEST['callback'];
if(!empty($_REQUEST['noload']))
	die("noload");

if(isset($_REQUEST['album']) && isset($_REQUEST['artist'])) {
	$output = "";
	$album = $_REQUEST['album'];
	$artist = $_REQUEST['artist'];
	$image = "";

	//get amazon stuff
	$amazon = new Amazon();
	$parameters = array(
		"region" => "com",
		"Operation" => "ItemSearch", // we will be searching
		"SearchIndex" => "Music",
		'ResponseGroup' => 'Images,ItemAttributes,Tracks,BrowseNodes',// we want images, item info...more? TODO
		"Keywords" => $album,
		"Artist" => $artist); // this is what we are looking for
	$queryUrl = $amazon->getSignedUrl($parameters);

	if(!$json) {
		echo "<!-- $queryUrl -->";
	}

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $queryUrl);
    $result = curl_exec($ch);
    curl_close($ch);
	
	//parse it
	$request = simplexml_load_string($result) or die ("xml response not loading");
	if($request->Items->TotalResults > 0) {
		//imgur upload
		$image = $request->Items->Item->LargeImage->URL;
		if (empty($image)) {
			$image = '* Image Not Available';
		} else {
			$apikeys = array("9e6655dae944b92c731fcd763b7fb795", "29e4d33b086551e033d7fc07a02c5129", "e8a9c9e5d99d81ade9c06172becbdcc8");
			$ch = curl_init("http://api.imgur.com/2/upload.xml");
			$pvars = array('image'=>$image, 'key'=>$apikeys[array_rand($apikeys)]);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $pvars);
			$imgurxml = curl_exec($ch);
			curl_close($ch);
			$xmlparse = simplexml_load_string($imgurxml);
			$image = $xmlparse->links->original[0];
		}
	
		//get data
		$_amazonpage = "http://www.amazon.com/dp/".$request->Items->Item->ASIN;
		$_artist = $request->Items->Item->ItemAttributes->Artist;
		$_label = $request->Items->Item->ItemAttributes->Label;
		$_title = $request->Items->Item->ItemAttributes->Title;
		$_releasedate = strtotime($request->Items->Item->ItemAttributes->ReleaseDate);
		//genres
		$_genres = array();
		foreach ($request->Items->Item->BrowseNodes->children() as $browsenode) {
			if($browsenode) {
				array_push($_genres, $browsenode->Name);
			}
		}
		//track data
		$tracks = "";
		foreach ($request->Items->Item->Tracks->children() as $disk) {
			if($disk) {
				foreach ($disk->children() as $track) {
					if($track) {
						$attr = $track->attributes();
						$tracks .= "[b]".$attr["Number"]."[/b] - ".$track."\n";
					}
				}
				$tracks .= "\n";
			}
		}
	
		$info = "";
		$info .= makeKeyVal("Album", $_title);
		$info .= makeKeyVal("Artist", $_artist);
		$info .= makeKeyVal("Genres", implode(", ", $_genres));
		$info .= makeKeyVal("Label", $_label);
		$info .= makeKeyVal("Release Date", date("F j, Y",$_releasedate));
		$info .= makeKeyVal("Amazon", "$_amazonpage");
		
		//$output .= makeBox("Description", "[b]None Available[/b]");
		$output .= makeBox("Information", $info);
		$output .= makebox("Track List", $tracks);
	} else {
		if($json) {
			$output = json_encode(array('error' => 'true'));
			if(!empty($callback)) {
				$output = $callback . '(' . $output . ')';
			}
			die($output);
		} else {
			die('<br><center>No results found. <a href="'.basename(__FILE__).'">Click to search again.</a></center>');
		}
	}
	if(!$json) {
	?>
	<html>
	<head>
	<link rel="shortcut icon" href="http://bbalbums.co.cc/favicon.ico">
	<title>BBAlbums</title>
	<script type="text/javascript">
	function selectAll(id) {
		document.getElementById(id).focus();
		document.getElementById(id).select();
	}
	</script>
	</head>
	<body>
	<center><p class="control"><b><a class="expand" href="<?php echo basename(__FILE__);?>">Click to Search Again</a></center></b></p> 
	<center><font color="red"><i>*PLEASE ENSURE OUTPUT IS CORRECT BEFORE POSTING*</i></font></center><p> 
	<p><center><h2><?php echo $_title." - ".$_artist; ?></h2></center> 
	<p><center><i><textarea id="img" onClick="selectAll('img');" cols="30"><?php echo $image;?></textarea></center></i> 
	<p><img class="thumbnail" style="padding:15px" align=left src="<?php echo $image;?>"/> 
	<textarea id="desc" onClick="selectAll('desc');" rows="40" cols="115">
	<?php echo $output; ?>
	</textarea> 
	</div>
	</body>
	</html>
	<?php
	} else {
		if($json) {
			$output = json_encode(array('error' => 'false', 'type' => 'music', 'image' => $image.'', 'description' => $output, 'artist' => $_artist.'', 'album' => $_title.'', 'year' => date("Y",$_releasedate)));
			if(!empty($callback)) {
				$output = $callback . '(' . $output . ')';
			}
			die($output);
		} else {
			die('<br><center>No results found. <a href="'.basename(__FILE__).'">Click to search again.</a></center>');
		}
	}
} else {
	?>
	<html>
	<head>
	<link rel="shortcut icon" href="http://bbalbums.co.cc/favicon.ico">
	<title>BBAlbums</title>
	<script type="text/javascript">
	function selectAll(id) {
		document.getElementById(id).focus();
		document.getElementById(id).select();
	}
	</script>
	</head>
	<body>
	<form action="<?php echo basename(__FILE__); ?>" method="GET">
	<fieldset>
        <h3><label for="id">Search for Album:</label></h3>
		<p><input type="text" name="album" value="Random Album Title" onfocus="this.value=''; this.onfocus=null;" ></p>
		<p><input type="text" name="artist" value="deadmau5" onfocus="this.value=''; this.onfocus=null;" ></p>
		<p><input class="submit" id="submit" name="submit" type="submit" value="Search"></p>
	</fieldset>
	</form>
	</body>
	</html>
	<?php
}
?>
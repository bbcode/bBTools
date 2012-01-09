<?php
//http://imuzdb.com/search?t=b&s=Blah			Album
//http://imuzdb.com/search?t=a&s=Ok Go			Artist

//<span>Artist:</span><a href="http://imuzdb.com/a[0-9]*/[a-zA-z0-9_]*">([a-zA-z0-9_]*)</a>
$patternArtist = regexify('<span>Artist:</span><a href="http://imuzdb.com/a[0-9]*/[a-zA-z0-9_]*">([a-zA-z0-9_]*)</a>');
$patternLength = regexify('<p><span>Length:</span>([a-zA-z0-9_ ]*)</p>');
$patternReleased = regexify('<p><span>Released:</span>([a-zA-z0-9_ ,]*)</p>');
$patternLabel = regexify('<span>Labels:</span><a href="http://imuzdb.com/l[0-9]*/[a-zA-z0-9_]*/albums">([a-zA-z0-9_]*)</a>');

$searchDesc1 = '<div id="tabContent1" class="active data"><div>';
$searchDesc2 = '</div></div>';

$searchLabel1 = '<p><span>Labels:</span>';
$searchLabel2 = '</p>';

$explodeToken = ' <strong>&middot;</strong> ';

$searchGenre1 = '<p><span>Genres:</span>';
$searchGenre2 = '</p>';

$searchTrack1 = '<div class="tracks"><div class="subtitle">Album´s Tracks:</div>';
$searchTrack2 = '</ul></div>';

$amazonLink = "http://www.amazon.com/s/ref=nb_sb_noss?url=search-alias%3Ddigital-music&field-keywords=";


function stripLinks($str) {
	$str = str_replace('</a>', '', $str);
	return preg_replace('/<a[^>]+href[^>]+>/', '', $str);
}
function regexify($str) {
	return "/".str_replace('/', '\/', $str)."/";
}
function makeBox($title, $content) {
	$str = "[size=3][b][color=#555555]".$title."[/color][/b][/size]<br>";
	$str .= "[size=2][quote]<br>";
	$str .= $content;
	$str .= "<br>[/quote][/size]<br>";
	return $str;
}
function makeKeyVal($key, $val) {
	return "[b]".$key.":[/b] ".$val."<br>";
}

function htmlToBBCODE($str) {
	// TODO
	return $str;
}

$output = "";
$info = "";
$album = $_REQUEST['album'];
$artist = $_REQUEST['artist'];
$page = file_get_contents("http://imuzdb.com/search?t=b&s=".urlencode($album));

if(strpos($page,"result") !== false) {
	die("<b>No results!</b>");
}

//details
if(preg_match($patternArtist, $page, $matches) > 0) {
	$info .= makeKeyVal("Artist", $matches[1]);
} else {
	$info .= makeKeyVal("Artist", $artist);
}
if(preg_match($patternLength, $page, $matches) > 0)
	$info .= makeKeyVal("Length", $matches[1]);
if(preg_match($patternReleased, $page, $matches) > 0)
	$info .= makeKeyVal("Released", $matches[1]);
//labels...this is difficult
$pos1 = strpos($page, $searchLabel1);
$str = "";
if($pos1 !== false) {
	$labels = "";
	$pos2 = strpos($page, $searchLabel2, $pos1); //starting at $pos1
	$str = substr($page, $pos1 + strlen($searchLabel1), $pos2 - ($pos1 + strlen($searchLabel1)));
	$explode = explode($explodeToken, $str);
	$implode = array();
	foreach ($explode as $foreach) {
		$search = 'albums">';
		$temp1 = substr($foreach, strpos($foreach, $search) + strlen($search));
		$temp1 = substr($temp1, 0, strlen($temp1)-4);
		array_push($implode, $temp1);
	}
	$info .= makeKeyVal("Label".((count($implode) > 1) ? "s" : ""),implode(", ",$implode));
}

//genres...this is difficult
$pos1 = strpos($page, $searchGenre1);
$str = "";
if($pos1 !== false) {
	$labels = "";
	$pos2 = strpos($page, $searchGenre2, $pos1); //starting at $pos1
	$str = substr($page, $pos1 + strlen($searchLabel1), $pos2 - ($pos1 + strlen($searchGenre1)));
	$explode = explode($explodeToken, $str);
	$implode = array();
	foreach ($explode as $foreach) {
		$search = 'albums">';
		$temp1 = substr($foreach, strpos($foreach, $search) + strlen($search));
		$temp1 = substr($temp1, 0, strlen($temp1)-4);
		array_push($implode, $temp1);
	}
	$info .= makeKeyVal("Genre".((count($implode) > 1) ? "s" : ""),implode(", ",$implode));
}
	
$output .= makeBox("Details", $info);

//description
$pos1 = strpos($page, $searchDesc1);
$str = "";
if($pos1 !== false) {
	$pos2 = strpos($page, $searchDesc2, $pos1); //starting at $pos1
	$str = substr($page, $pos1, ($pos2 + strlen($searchDesc2)) - $pos1);
} else {
	$str = "[b]None Available[/b]";
}
$output .= makeBox("Description", $str);

//tracks
$output .= makeBox("Track Listing", "");

//extern sites
$buy = "";
$buy .= makeKeyVal("Amazon","[url]".$amazonLink.urlencode($album)."[/url]");
$output .= makeBox("External Sites", $buy);

echo $output;
?>

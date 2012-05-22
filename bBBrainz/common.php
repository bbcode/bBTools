<?php
//FIXME: Content-Type will change when we add JSONP support.
header('Content-Type: text/html; charset=utf-8');
$apiroot = 'http://musicbrainz.org/ws/2';
$mbpage = 'http://musicbrainz.org/release/';
$lastfm_key = 'fc1bd1e1c71fb7222b564e6130e3044a';
$imgur_keys = Array('9e6655dae944b92c731fcd763b7fb795', '29e4d33b086551e033d7fc07a02c5129', 'e8a9c9e5d99d81ade9c06172becbdcc8');
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

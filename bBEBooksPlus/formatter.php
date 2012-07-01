<?php
function createInfoBlock($header, $map) {
	$block = "";
	$block .= "[size=3][b][color=#FF6014]".$header."[/color][/b][/size]\n";
	$block .= "[size=2][quote]\n";

	foreach($map as $key => $value) {
		$block .= "[b]".$key."[/b] ".$value."\n";
	}

	$block .= "[/quote][/size]";
	return $block;
}
?>
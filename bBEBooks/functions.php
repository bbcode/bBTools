<?php
//keys
define("GOODREADS_KEY", "mX4GSwTu0NTZ5pkQrWOyw");
define("AMAZON_ASSOCIATE_ID", "boocut-20");
define("AMAZON_PUBLIC", "AKIAI4STSD7K63J2ABAA");
define("AMAZON_PRIVATE", "fIllcCDuxwSjxGuRxgXnBFgH4syPgnhh8AYdKboy");
$imgur_keys = array("bfff43342cc7b0247e8aa52676ec17b6", "2e263f3986e1b4e2e4130f68a41621c4", "9e6655dae944b92c731fcd763b7fb795", "29e4d33b086551e033d7fc07a02c5129", "e8a9c9e5d99d81ade9c06172becbdcc8");
define("IMGUR_KEY", $imgur_keys[array_rand($imgur_keys)]);
$banned_tags = array("erotica", "books", "essays", "classics", "correspondence", "criticism", "fiction", "foreign.language.fiction", "genre.fiction", "letters", "reading", "women.s.fiction", "used.textbooks", "new", "products", "all.product", "literary", "literature", "world.literature", "british", "short.stories", "poetry", "history", "historical", "reference", "united.states", "canadian.detectives", "cat.sleuths", "readers", "sherlock.holmes");

function load_simplexml($url) {
	$xml = load_resource($url);
	$result = simplexml_load_string($xml) or die ("xml response not loading");
	return $result;
}

function load_resource($url) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_URL, $url);
	$val = curl_exec($ch);
	curl_close($ch);
	return $val;
}

function unhtmlentities($string)
{
    // replace numeric entities
    $string = preg_replace('~&#x([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $string);
    $string = preg_replace('~&#([0-9]+);~e', 'chr("\\1")', $string);
    $string = str_replace("<b>", "[b]",$string);
	$string = str_replace("“", "\"", $string);
	$string = str_replace("”", "\"", $string);
	$string = str_replace("—", "-", $string);
	$string = str_replace("’", "'", $string);
    $string = str_replace("</b>", "[/b]",$string);    
    $string = str_replace("<B>", "[b]",$string);
    $string = str_replace("</B>", "[/b]",$string);   
    $string = str_replace(' rel="nofollow"', '', $string);
 	$string = str_replace(' target="_blank"', '', $string);
    $string = str_replace("<a href=", "[url=",$string);
 	$string = str_replace('">', ']', $string);
	$string = str_replace('[url="', '[url=', $string);
    $string = str_replace("</a>", "[/url]",$string);  
    $string = str_replace("<sup>", "",$string);
    $string = str_replace("</sup>", "",$string);  
    $string = str_replace("<sub>", "",$string);
    $string = str_replace("</sub>", "",$string);     
    $string = str_replace("<i>", "[i]",$string);
    $string = str_replace("<I>", "[i]",$string);
    $string = str_replace("</I>", "[/i]",$string);
    $string = str_replace("</i>", "[/i]",$string);
    $string = str_replace("</b>","[/b]",$string);
    $string = str_replace("<br>","\n",$string);
    $string = str_replace("<BR>","\n",$string);    
	$string = str_replace("<br/>", "\n", $string);
	$string = str_replace("<br />", "\n", $string);
    $string = str_replace("<P>","\n",$string);
    $string = str_replace("<DIV>","",$string);
    $string = str_replace("<div>","",$string);    
    $string = str_replace("</div>","",$string);  
    $string = str_replace("</DIV>","",$string);
    $string = str_replace("<p>","\n",$string);
    $string = str_replace("<P>","\n",$string);
    $string = str_replace("<P style=\"MARGIN: 0px\">","\n",$string);
    $string = str_replace("</p>","\n",$string);
    $string = str_replace("</P>","\n",$string);
    $string = str_replace("<ul>","[list]",$string);
    $string = str_replace("</ul>","[/list]",$string);
    $string = str_replace("<li>","[*]",$string);
    $string = str_replace("</li>","\n",$string);
    $string = str_replace("<UL>","[list]",$string);
    $string = str_replace("</UL>","[/list]",$string);
    $string = str_replace("<LI>","[*]",$string);
    $string = str_replace("</LI>","\n",$string);
    $string = str_replace("<DIV style=\"MARGIN: 0px\">","\n",$string);
    $string = str_replace("<p style=\"MARGIN: 0px\">","\n",$string);
    $string = str_replace("<p style=\"margin: 0px;\">","\n",$string);
    $string = str_replace("<em>","[i]",$string);
    $string = str_replace("</em>","[/i]",$string);
    $string = str_replace("<EM>","[i]",$string);
    $string = str_replace("</EM>","[/i]",$string);
    $string = str_replace("<h3>","\n",$string);
    $string = str_replace("</h3>","\n",$string);
    $string = str_replace("<ol>","[list]",$string);
    $string = str_replace("</ol>","[/list]",$string);
	$string = str_replace("<p align=\"center\">", "", $string);
	$string = str_replace("<big>", "", $string);
	$string = str_replace("</big>", "", $string);
    // replace literal entities
    $trans_tbl = get_html_translation_table(HTML_ENTITIES);
    $trans_tbl = array_flip($trans_tbl);
    return strtr($string, $trans_tbl);
}
?>
<?php
//keys
define("GOODREADS_KEY", "mX4GSwTu0NTZ5pkQrWOyw");
define("AMAZON_ASSOCIATE_ID", "boocut-20");
define("AMAZON_PUBLIC", "AKIAJYFOL6ZHB5SUHEUQ");
define("AMAZON_PRIVATE", "gj5TzvGoImnCEUE8WDjM4ItLfTGkX9lesUV1CUDq");
$imgur_keys = array(
	"6b796bbc2196188688e23c21bc6c6bb0",
	"49cd2570c2c302afb4e700d8e4eeb9ff",
	"b19a96ba7162e6e01662be5c3f70fa81",
	"775b30c0ed1a6f5e6484309fbcac6434",
	"f266dc7c427cad1b4a8fc87351e7ed0a"
);
define("IMGUR_KEY", $imgur_keys[array_rand($imgur_keys)]);
$banned_tags = array("erotica", "books", "essays", "classics", "correspondence", "criticism", "fiction", "foreign.language.fiction", "genre.fiction", "letters", "reading", "women.s.fiction", "used.textbooks", "new", "products", "all.product", "literary", "literature", "world.literature", "british", "short.stories", "poetry", "history", "historical", "reference", "united.states", "canadian.detectives", "cat.sleuths", "readers", "sherlock.holmes");

function load_simplexml($url) {
	return simplexml_load_file($url);
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
    // Replaces entire tag with given values.
    $tag_replacements = Array(
        // tag => [opening, closing]
        'div' => Array('', ''),
        'sub' => Array('', ''),
        'sup' => Array('', ''),
        'b' => Array('[b]', '[/b]'),
        'strong' => Array('[b]', '[/b]'),
        'i' => Array('[i]', '[/i]'),
        'em' => Array('[i]', '[/i]'),
        'u' => Array('[u]', '[/u]'),
        'br' => Array("\n", ''),
        'p' => Array("\n", "\n"),
        'ul' => Array('[list]', '[/list]'),
        'ol' => Array('[list=1]', '[/list]'),
        'li' => Array('[*]', "\n"),
        'h\\d' => Array("\n", "\n"),
    );

    foreach($tag_replacements as $tag=>$replacements) {
		$string = preg_replace("/<\\s*$tag\\b[^>]*>/i", $replacements[0], $string);
		$string = preg_replace("/<\\s*\\/\\s*$tag\\b[^>]*>/i", $replacements[1], $string);
    }

    // Strip all non-link tags
    $string = preg_replace("/<\\s*[^a][^>]*>/i", $replacements[0], $string);
    $string = preg_replace("/<\\s*\\/\\s*[^a][^>]*>/i", $replacements[1], $string);

    // replace numeric entities
    $string = preg_replace('~&#x([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $string);
    $string = preg_replace('~&#([0-9]+);~e', 'chr("\\1")', $string);
    $string = str_replace(' rel="nofollow"', '', $string);
 	$string = str_replace(' target="_blank"', '', $string);
    $string = str_replace("<a href=", "[url=",$string);
 	$string = str_replace('">', ']', $string);
	$string = str_replace('[url="', '[url=', $string);
    $string = str_replace("</a>", "[/url]",$string);  
	$string = str_replace("“", "\"", $string);
	$string = str_replace("”", "\"", $string);
	$string = str_replace("—", "-", $string);
	$string = str_replace("’", "'", $string);

    // replace literal entities
    $trans_tbl = get_html_translation_table(HTML_ENTITIES);
    $trans_tbl = array_flip($trans_tbl);
    return strtr($string, $trans_tbl);
}
?>

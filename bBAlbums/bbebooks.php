<?php
//assign value for title of page
$pageTitle = 'Book Information Search';
//create an array with filepaths for multiple page scripts - default is checkFlash.js
//$customScript[0] = '';
//$customScript[1] = '';
//create an array with filepaths for multiple screen/projection stylesheet variable - default is master.css
//$customCSS[0] = 'meta/styles/master.css';
//$customCSS[1] = '';
//connect to database
//include '';
//include global functions and settings
//include '';
//include page header
// include 'meta/inc/header.php';
?>
<?php header("Content-type: text/html; charset=utf-8");

//script makes call to Amazon Ecommerce Web Service at http://developer.amazonwebservices.com/connect/kbcategory.jspa?categoryID=19
//pass isbn or asin as $_GET['id'] variable for different item views
//ini_set("allow_url_fopen", "On");
//switch off zend compatability for this file, applied locally as it may effect php 4
ini_set ("zend.ze1_compatibility_mode", 0);
//switch on url_fopen for simplexml_load_file, applied locally as it can be security risk
//ini_set('allow_url_fopen', 1);

//include the Amazon Product services API class
require_once 'amazon-api.php';

//turn on full error reports for development purposes - should be turned off for production environment
error_reporting(E_ALL);

//set default value for asin or isbn value - primary value to make Amazon E-Commerce Web Services request work
if (!isset($_GET['q'])) {
	$q = '0596005601';
} else {
   $q = preg_replace("/[^0-9X]/","", $_GET['q']);
}

if (isset($_GET['submit'])): 
//if Amazon E-Commerce Web Services has been queried using the form

echo '<center><p class="control"><b><a class="expand" href="'.basename(__FILE__).'">Click to Search Again</a></center></b></p>'."\n";

$Amazon=new Amazon();

$parameters=array(
"region"=>"com",
"Operation"=>"ItemSearch", // we will be searching
"SearchIndex"=>"Books", // search all categories, use "Books" to limit to books
'ResponseGroup'=>'Images,ItemAttributes,EditorialReview,Reviews,Similarities,BrowseNodes',// we want images, item info, reviews, and related items
"Keywords"=>"$q"); // this is what we are looking for, you could use the book's title instead

$queryUrl=$Amazon->getSignedUrl($parameters);

// print out Query URL for dev purposes - TURN OFF in Production
echo '<!--'.$queryUrl.'-->';

$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL, $queryUrl);
$xml = curl_exec($ch);
curl_close($ch);

$request=simplexml_load_string($xml) or die ("xml response not loading");

if($request->Items->TotalResults > 0) // we have at least one response
	{
		//set Amazon xml values as specifc variables to be printed out below
		$image = $request->Items->Item->LargeImage->URL;
		//imgur upload
		if (empty($image)) { $image = '* Image Not Available'; }
		else 
		{
			$apikeys  = array("9e6655dae944b92c731fcd763b7fb795", "29e4d33b086551e033d7fc07a02c5129", "e8a9c9e5d99d81ade9c06172becbdcc8");
			$ch = curl_init("http://api.imgur.com/2/upload.xml");
			$pvars = array('image'=>$image, 'key'=>$apikeys[array_rand($apikeys)]);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    	curl_setopt($ch, CURLOPT_POSTFIELDS, $pvars);
		    $imgurxml = curl_exec($ch);
			curl_close($ch);
			$xmlparse = simplexml_load_string($imgurxml);
			$image=$xmlparse->links->original[0];
		}
		$title = $request->Items->Item->ItemAttributes->Title;
		$pagenumber = $request->Items->Item->ItemAttributes->NumberOfPages;
		$pageurl = $request->Items->Item->ItemAttributes->DetailPageURL;
		$publisher = $request->Items->Item->ItemAttributes->Publisher;
		$publicationDate = $request->Items->Item->ItemAttributes->PublicationDate;
		$author = $request->Items->Item->ItemAttributes->Author;
		$_genres = array();
		foreach ($request->Items->Item->BrowseNodes->children() as $browsenode) {
			if($browsenode) {
				array_push($_genres, $browsenode->Name);
			}
		}

//simple logic check for author and director values, shows

if (strlen($author) > 2) {
        $creator = $author;
	} elseif (empty($author)) {
        $creator = $request->Items->Item->ItemAttributes->Director;
	} else
	{
        $creator = '* Creator Not Available';
	}

		$asin = $request->Items->Item->ASIN;
		$uri = $request->Items->Item->DetailPageURL;

// Replace HTML characters into something readable for Product description

function unhtmlentities($string)
{
    // replace numeric entities
    $string = preg_replace('~&#x([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $string);
    $string = preg_replace('~&#([0-9]+);~e', 'chr("\\1")', $string);
    $string = str_replace("<b>", "[b]",$string);
    $string = str_replace("</b>", "[/b]",$string);    
    $string = str_replace("<B>", "[b]",$string);
    $string = str_replace("</B>", "[/b]",$string);       
    $string = str_replace("<a href=", "[url=",$string); 
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
    // replace literal entities
    $trans_tbl = get_html_translation_table(HTML_ENTITIES);
    $trans_tbl = array_flip($trans_tbl);
    return strtr($string, $trans_tbl);
}

//Strip spaces from tags
$tag_spaces = str_replace(" ",".",$creator);
$tag_spaces = str_replace("-",".",$creator);
$tag_spaces = str_replace("..","",$tag_spaces);
$tag_spaces = str_replace(". ",".",$tag_spaces);
$tag_spaces = str_replace(" ",".",$tag_spaces); // I have no idea what I'm doing

//Clean Genres
$genres = implode(",", $_genres);
$genres = str_replace(", & ",".",$genres);
$genres = str_replace(" ",".",$genres);
$genres = str_replace("-",".",$genres);
$genres = str_replace(",.",", ",$genres);
$genres = str_replace(".&.",".",$genres);

$displayformat = implode("/", $_GET["format"]);

// print out Amazon xml values as html

echo '<center><font color="red"><i>*PLEASE ENSURE OUTPUT IS CORRECT BEFORE POSTING*</i></font></center><p>'."\n";
echo '<p><center><h2>'.$title.' - '.$creator.' ['.$displayformat.']</h2></center>'."\n";
echo '<p><center><i>Image Link:</center></i>';
echo '<center><textarea rows=1 cols="25">'.$image.''."</textarea></center>\n";
echo '<p><center><i>Suggested Tags:</i>';
echo '<p><textarea rows="1" cols="115">'.$tag_spaces.', '.$genres.'</textarea></center>'."\n";
// start output
echo '<p><hr>'."\n";
echo '<p><img class="thumbnail" style="padding:15px" align=left src="'.$image.'"/>'."\n";
echo '<textarea rows="40" cols="115">'."\n";
echo '[size=3][b][color=#FF3300]Book Details:[/color][/b][/size]'."\n";
echo '[size=2][quote]'."\n";
echo '[b]Title:[/b] '.$title.''."\n";
echo '[b]Author:[/b] '.$creator.''."\n";
echo '[b]ISBN:[/b] '.$asin .''."\n";
echo '[b]Publisher:[/b] '.$publisher.''."\n";
echo '[b]Publication Date:[/b] '.$publicationDate.''."\n";
echo '[b]Number of Pages:[/b] '.$pagenumber.''."\n";

$_amazonpage = "http://www.amazon.com/dp/".$request->Items->Item->ASIN; // Amazon Link

echo '[b]Website:[/b] [url='.$_amazonpage.']Amazon[/url], [url=http://www.librarything.com/isbn/'.$asin .']LibraryThing[/url], [url=http://books.google.com/books?vid=ISBN'.$asin .']Google Books[/url]'."\n";
echo '[B]Format: '.$displayformat.'[/B]'."";
echo '[/quote][/size]'."\n";
echo '[size=3][b][color=#FF3300]Synopsis from Amazon:[/color][/size][/b]'."\n";
if (isset($request->Items->Item->EditorialReviews)) // Check to see if there is an editorial review
	{
	$editorialReview = $request->Items->Item->EditorialReviews->EditorialReview->Content;
	$edreview = unhtmlentities($editorialReview); // Runs editorial review through HTML parser
	echo '[quote][size=2]'.$edreview.'[/size][/quote]'."\n";
}
else{
	echo 'No book information found'."\n"; // No Review found
}


// ++++++++++    Wikipedia Book Title Search    ++++++++++++++++++

		$wikibookurl = "http://en.wikipedia.org/w/api.php?action=opensearch&search=".urlencode($title)."&format=xml&limit=1";
		$ch = curl_init($wikibookurl);
		curl_setopt($ch, CURLOPT_HTTPGET, TRUE);
		curl_setopt($ch, CURLOPT_POST, FALSE);
		curl_setopt($ch, CURLOPT_HEADER, false);   // Include head as needed
		curl_setopt($ch, CURLOPT_NOBODY, FALSE);        // Return body
		curl_setopt($ch, CURLOPT_VERBOSE, FALSE);           // Minimize logs
		curl_setopt($ch, CURLOPT_REFERER, "");            // Referer value
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);    // No certificate
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);     // Follow redirects
		curl_setopt($ch, CURLOPT_MAXREDIRS, 4);             // Limit redirections to four
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);     // Return in string
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 6.1; he; rv:1.9.2.8) Gecko/20100722 Firefox/3.6.8");   // Webbot name
		$page = curl_exec($ch);
		$xml = simplexml_load_string($page);

function unhtmlwikiurl($string)
	{
    // replace paranthesis
    $string = str_replace("(","%28",$string);
    $string = str_replace(")","%29",$string);
    // replace literal entities
    return strtr($string);
	}

		$wikibook = unhtmlentities($wikibookurl); // Passes paranthesis through unhtmlwikiurl function
		
		if((string)$xml->Section->Item->Description) {
			$wikibooktitle = ((string)$xml->Section->Item->Text);
			$wikibookdescription = ((string)$xml->Section->Item->Description);
			$wikibooktitleurl = ((string)$xml->Section->Item->Url);
		
			echo '[size=4][b]Wikipedia Book Information:[/b][/size]'."\n";
			echo '[quote]'."\n";
			echo '[b]Title:[/b] '.$wikibooktitle.''."\n";
			echo '[b]Title Description:[/b] '.$wikibookdescription.''."\n";
			echo '[b]Title URL:[/b] [url]'.$wikibook.'[/url]'."\n";
			echo '[/quote]'."\n";
			
	} else {
			echo '';
			return "";
		}

// Wiki Author Search...Theres probably a cleaner way to combine the two
		
		$wikiauthorurl = "http://en.wikipedia.org/w/api.php?action=opensearch&search=".urlencode($creator)."&format=xml&limit=1";
		$ca = curl_init($wikiauthorurl);
		curl_setopt($ca, CURLOPT_HTTPGET, TRUE);
		curl_setopt($ca, CURLOPT_POST, FALSE);
		curl_setopt($ca, CURLOPT_HEADER, false);   // Include head as needed
		curl_setopt($ca, CURLOPT_NOBODY, FALSE);        // Return body
		curl_setopt($ca, CURLOPT_VERBOSE, FALSE);           // Minimize logs
		curl_setopt($ca, CURLOPT_REFERER, "");            // Referer value
		curl_setopt($ca, CURLOPT_SSL_VERIFYPEER, FALSE);    // No certificate
		curl_setopt($ca, CURLOPT_FOLLOWLOCATION, TRUE);     // Follow redirects
		curl_setopt($ca, CURLOPT_MAXREDIRS, 4);             // Limit redirections to four
		curl_setopt($ca, CURLOPT_RETURNTRANSFER, TRUE);     // Return in string
		curl_setopt($ca, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 6.1; he; rv:1.9.2.8) Gecko/20100722 Firefox/3.6.8");   // Webbot name
		$page = curl_exec($ca);
		$xml = simplexml_load_string($page);

		$wikiauthortitleurl = unhtmlentities($wikiauthorurl); // Passes paranthesis through unhtmlwikiurl function
		
		if((string)$xml->Section->Item->Description) {
			$wikiauthor = ((string)$xml->Section->Item->Text);
			$wikiauthordescription = ((string)$xml->Section->Item->Description);
			$wikiauthortitleurl = ((string)$xml->Section->Item->Url);
		
			echo '[size=4][b]Wikipedia Author Information[/b][/size]'."\n";
			echo '[quote]'."\n";
			echo '[b]Author Name:[/b] '.$wikiauthor.''."\n";
			echo '[b]Author Description:[/b] '.$wikiauthordescription.''."\n";
			echo '[b]Author URL:[/b] [url]'.$wikiauthortitleurl.'[/url]'."\n";
			echo '[/quote]'."\n";
			echo '</textarea>'."\n";			
	} else {
			echo '';
			return "";
		}
}

else
{
   //no search results
	echo '<p>No results for your query for <strong>"'.$q.'"</strong></p>'; 
}

else: //show form and allow the user to check for Amazon.com reviews and more
?>

<form id="checkAmazon" name="checkAmazon" action="<?php echo basename(__FILE__); ?>" method="get">
	<fieldset>
        <h3><label for="id">Search Book by ISBN or Keyword:</label></h3>
		<p><input type="text" id="q" name="q" value="9780345391803" onfocus="this.value=''; this.onfocus=null;" /></p>
		<input type="checkbox" id="EPUB" name="format[]" value="EPUB">EPUB
		<input type="checkbox" id="MOBI" name="format[]" value="MOBI" checked>MOBI
		<input type="checkbox" id="HTML" name="format[]" value="HTML">HTML
		<input type="checkbox" id="PDF" name="format[]" value="PDF">PDF
		<input type="checkbox" id="LIT" name="format[]" value="LIT">LIT
		<input type="checkbox" id="LRF" name="format[]" value="LRF">LRF
		<input type="checkbox" id="RTF" name="format[]" value="RTF">RTF
		<p><input class="submit" id="submit" name="submit" type="submit" value="search" />
		</p>
	</fieldset>
</form>

<?php
//end submit isset if statement on line 45
endif;
?>
</div>
<!-- end main div -->
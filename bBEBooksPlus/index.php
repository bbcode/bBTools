<?php
require_once("config.php");
require_once("formatter.php");

/* Gather Input */
$isbn = $_REQUEST['isbn'];
if(!$isbn) {
   die("You must enter an isbn!");
}
/* End Gather Input */

/* Collect Raw Data */
$googleBooksURL = "https://www.googleapis.com/books/v1/volumes?q=isbn:$isbn";
$googleBooksRaw = file_get_contents($googleBooksURL);
$googleBooks = json_decode($googleBooksRaw, true);
$goodreadsURL = "http://www.goodreads.com/book/isbn_to_id/$isbn?key=$goodreadsApiKey";
$goodreadsID = file_get_contents($goodreadsURL);
/* End Collect Raw Data */

/* Parse Raw Data */
$parsed = array();

$parsed['info']['Google Books API URL:'] = $googleBooksURL;
$parsed['info']['GoodReads ID'] = $goodreadsID;
/* End Parse Raw Data */

/* Build Output */
$output = "";
$output .=  createInfoBlock("Book Information", $parsed['info']);
/* End Build Output */

/* Output */
?>

<html>
<head>
	<title>bBEBooksPlus</title>
</head>
<body>
	<textarea rows="30" cols="80"><?= $output ?></textarea>
</body>
</html>


<?php
/* End Output */
?>

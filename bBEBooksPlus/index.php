<?php
$isbn = $_REQUEST['isbn'];
if(!$isbn) {
   die("You must enter an isbn!");
}

$googleBooksURL = "http://www.googleapis.com/books/v1/volumes?q=isbn:".$isbn;
$googleBooksRaw = file_get_contents($googleBooksURL);

echo $googleBooksRaw;

?>

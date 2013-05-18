<?php
require_once 'functions.php';
class AmazonResult
{
	var $Author;
	var $AuthorGoodReadsUrl;
	var $AuthorGoodReadsImage;
	var $AuthorImgurImage;
	var $AuthorGoodReadsBiography;
	var $AuthorBirthDate;
	var $AuthorDeathDate;
	var $Binding;
    var $Title;
	var $PublicationDate;
	var $ReleaseDate;
	var $Publisher;
    var $ISBN;
    var $EAN;
    var $ASIN;
    var $Label;
    var $Languages;
    var $AmazonImage;
    var $ImgurImage;
    var $NumberOfPages;
	var $DetailPageUrl;
	var $Genre;
	var $Review;
	var $LibraryThingsUrl;
    var $GoogleBooksUrl;
    var $GoodReadsUrl;
    var $BBCode;
	var $Tags;
	var $TagString;
	var $BBTitle;
	

	public function __construct($item, $completed = false) {
	    $link = explode("%3F", strval($item->DetailPageURL));
	    if (count($link) > 0) {
	        $this->DetailPageUrl = $link[0];
        } else {
            $this->DetailPageUrl = strval($item->DetailPageURL);
        }
        
        $this->AmazonImage = strval($item->LargeImage->URL);
	    $attr = $item->ItemAttributes;
        $this->Author = strval($attr->Author[0]);
		$this->Tags = array();
		$this->AddTag($this->Author);
		if (strlen(strval($attr->Genre)) > 0) {
	    	$this->Genre = strval($attr->Genre);
			$this->AddTag($this->Genre);
		}
		
        $this->Binding = strval($attr->Binding[0]);
        $this->EAN = strval($attr->EAN);
        $this->ASIN = strval($item->ASIN);
        $this->DetailPageUrl = "http://amzn.com/" . $this->ASIN;
        $this->ISBN = strval($attr->ISBN);

        if ($completed) {
			// we only process images and grab wiki/goodreads data when this is the only result
            $this->ImgurImage = $this->OldUploadImage($this->AmazonImage);
			$this->ParseGoodReadsData();
			if(strpos($this->AuthorGoodReadsImage, '/nophoto/') === -1) {
				$this->AuthorImgurImage = $this->UploadImage($this->AuthorGoodReadsImage);
			} else {
				$this->AuthorImgurImage = '';
			}
        }
        $this->Label = strval($attr->Label);
        $langs = array();
		if (count($attr->Languages->Language) > 0) {
        	foreach($attr->Languages->Language as $lang) {
            	if (!in_array(strval($lang->Name), $langs)) {
                	$langs[] = strval($lang->Name);
            	}
        	}
		}
        $this->Languages = implode(", ", $langs);
        $this->NumberOfPages = strval($attr->NumberOfPages);
        $this->PublicationDate = strval($attr->PublicationDate);
        $this->Publisher = strval($attr->Publisher);
        $this->ReleaseDate = strval($attr->ReleaseDate);
        $this->BookTitle = strval($attr->Title);
		$this->Title = strval($attr->Title) . " - " . $this->Author;
        $this->LibraryThingsUrl = "http://www.librarything.com/isbn/" . $this->ASIN;
        $this->GoogleBooksUrl = "http://books.google.com/books?vid=ISBN" . $this->ASIN;
        if (count($item->EditorialReviews) > 0) {
            $this->Review = strval($item->EditorialReviews->EditorialReview->Content);
        }
        $this->BBCode = $this->bbCode();

		$this->ParseTags($item);
		$tagstring = implode(", ", $this->Tags);
		if (strlen($tagstring) > 200) {
			$tagstring = substr(substr($tagstring, 0, 200), 0, strrpos(substr($tagstring, 0, 200), ","));
                }
                //don't do tags, they suck.
		$this->TagString = ""; //$tagstring;
		
    }

    public function AddTag($tag) {
		global $banned_tags;
		foreach(explode("&", $tag) as $tag_item) {
			$tag_item = preg_replace('~[^\\pL\d]+~u', '.', $tag_item);
			
			// trim
			$tag_item = trim($tag_item, '.');

			// transliterate
			if (function_exists('iconv'))
			{
				$tag_item = iconv('utf-8', 'us-ascii//TRANSLIT', $tag_item);
			}
			
			// lowercase
			$tag_item = strtolower($tag_item);

			if ( (!in_array($tag_item, $this->Tags)) && (!in_array($tag_item, $banned_tags))) {
				$this->Tags[] = $tag_item;
			}
		}
	}
    
    public function AmazonResult($item, $completed = false) {
        __construct($item, $completed);
    }
    
	public function ParseTags($item) {
		if (count($item->BrowseNodes) > 0) {
			foreach ($item->BrowseNodes->BrowseNode as $browsenode) {
				$this->GetTags($browsenode);
			}
		}
	}
	
	public function GetTags($browsenode) {
		$tags = array();
		//get current node tag
		if (count($this->Tags) < 10) {
			$this->AddTag(strval($browsenode->Name));
			if (count($browsenode->Children->BrowseNode) > 0) {
				foreach($browsenode->Children->BrowseNode as $child) {
					$this->GetTags($child);
				}
			}
			if (count($browsenode->Ancestors->BrowseNode) > 0) {
				foreach($browsenode->Ancestors->BrowseNode as $ancestor) {
					if ($ancestor->BrowseNodeId <> "1000") {
						$this->GetTags($ancestor);
					}
				}
			}
		}	
	}
	
    public function ParseGoodReadsData() {
		if (strlen($this->ISBN) > 0) {
			// this just gets us the good reads id for the isbn
        	$good_reads_book_api_url = "http://www.goodreads.com/book/isbn_to_id?key=" . GOODREADS_KEY . "&isbn=" . $this->ISBN;
			$good_reads_book_id = "";
        	try {
				$good_reads_book_id = @load_resource($good_reads_book_api_url);
			} catch (Exception $e) {
				
			}
        	if (($good_reads_book_id != "") && ($good_reads_book_id != "No book with that ISBN")) {
	
				$this->GoodReadsUrl = "http://www.goodreads.com/book/show/" . $good_reads_book_id;
        
        		//load book information
        		$bookcontentsurl = "http://www.goodreads.com/book/show/" . $good_reads_book_id . ".xml?key=" . GOODREADS_KEY;
        		$bookcontentxml = @load_simplexml($bookcontentsurl);
				$author_id = $bookcontentxml->book->authors->author->id;
        
				// load author information
        		$authorcontenturl = "http://www.goodreads.com/author/show/" . $author_id . ".xml?key=" . GOODREADS_KEY;
				$authorcontentxml = @load_simplexml($authorcontenturl);
				$author = $authorcontentxml->author;
				$this->AuthorGoodReadsImage = strval($author->image_url);
				$this->AuthorGoodReadsBiography = strval($author->about);
				$this->AuthorBirthDate = strval($author->born_at);
				$this->AuthorDeathDate = strval($author->died_at);
				$this->AuthorGoodReadsUrl = "http://www.goodreads.com/author/show/" . $author_id;
			}
		}
    }
    
    public function OldUploadImage($image) {
		$ch = curl_init("http://api.imgur.com/2/upload.xml");
		$pvars = array('image'=> $image, 'key'=> IMGUR_KEY);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $pvars);
		$imgurxml = curl_exec($ch);
		curl_close($ch);
		$xmlparse = simplexml_load_string($imgurxml);
		$imgur_img = strval($xmlparse->links->original[0]);
		return $imgur_img;
    }

    public function UploadImage($image) {
		if (strlen($image) > 0) {
			$url = "https://images.baconbits.org/api.php?upload=" . $image;
        	// create curl resource 
        	$ch = curl_init(); 

        	// set url 
        	curl_setopt($ch, CURLOPT_URL, $url); 

        	//return the transfer as a string 
        	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        	curl_setopt($ch, CURLOPT_HEADER, 0);
        	curl_setopt($ch, CURLOPT_POST, 0);

        	// $output contains the output string 
			$output = curl_exec($ch);
		
        	// close curl resource to free up system resources 
        	curl_close($ch);
			$json = json_decode($output);
			if ($json->status_code == 200) {
				return $json->data->img_url;
			} else {
				return "";
			}
		}
		return "";
    }
    
    public function bbCode() {
        $bbCode = "[size=3][b]Book Details:[/b][/size]\n";
        $bbCode .= "[quote]\n";
        //$bbCode .= "[b]Title:[/b] " . $this->BookTitle . "\n";
        //$bbCode .= "[b]Author:[/b] " . $this->Author . "\n";
        if ($this->Genre != "") {
            $bbCode .= "[b]Genre:[/b] " . $this->Genre . "\n";
        }
        if ($this->ISBN != "") {
            $bbCode .= "[b]ISBN:[/b] " . $this->ISBN . "\n";
        }
        if ($this->EAN != "") {
        	$bbCode .= "[b]EAN:[/b] " . $this->EAN . "\n";
		}
        if ($this->ASIN != "") {
        	$bbCode .= "[b]ASIN:[/b] " . $this->ASIN . "\n";
		}
        $bbCode .= "[b]Publisher:[/b] " . $this->Publisher . "\n";
        $bbCode .= "[b]Publication Date:[/b] " . $this->PublicationDate . "\n";
        $bbCode .= "[b]Number of Pages:[/b] " . $this->NumberOfPages . "\n";
        $bbCode .= "[b]Website:[/b] [url=" . $this->DetailPageUrl . "]Amazon[/url], ";
        $bbCode .= "[url=" . $this->LibraryThingsUrl ."]LibraryThing[/url], ";
        $bbCode .= "[url=" . $this->GoogleBooksUrl . "]Google Books[/url]";
		if ($this->GoodReadsUrl != "") {
        	$bbCode .= ", [url=" . $this->GoodReadsUrl . "]Goodreads[/url]";
                }
        $bbCode .= "\n";
        $bbCode .= "[/quote]\n";
        $bbCode .= "[size=3][b]Synopsis:[/b][/size]\n";
	if(strlen($this->Review) > 0) {
		$bbCode .= "[quote]" . unhtmlentities($this->Review) . "[/quote]\n";
	} else {
		$bbCode .= "\n[size=7][color=red][b]MISSING DESCRIPTION\n[/b][/color][/size]\n\n";
	}
		if ($this->AuthorGoodReadsUrl != "" && $this->AuthorGoodReadsBiography != "") {
			$bbCode .= "[size=3][b]GoodReads Author Information:[/b][/size]\n";
			$bbCode .= "[quote]\n";
			if ($this->AuthorImgurImage != "") {
				$bbCode .= "[quote][align=center][img=" . $this->AuthorImgurImage . "][/align][/quote]\n";
			}
			$bbCode .= "[b]Author Name:[/b] " . $this->Author;
			if (strlen($this->AuthorBirthDate) > 0) {
				$bbCode .= " (Born: " . $this->AuthorBirthDate;
				if ($this->AuthorDeathDate <> "") {
					$bbCode .= " / Died: " . $this->AuthorDeathDate;
				}
				$bbCode .= ")";
			}
			$bbCode .= "\n\n";
			$bbCode .= "[b]Author Description:[/b] " . unhtmlentities($this->AuthorGoodReadsBiography) . "\n\n";
			$bbCode .= "[b]Author URL:[/b] " . $this->AuthorGoodReadsUrl . "\n";
			$bbCode .= "[/quote]\n";
		}
        return $bbCode;
    }
}

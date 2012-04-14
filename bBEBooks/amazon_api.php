<?php
require_once 'functions.php';
class Amazon
{
	// public key
	var $publicKey = AMAZON_PUBLIC;
	// private key
	var $privateKey = AMAZON_PRIVATE;
	var $associateId = AMAZON_ASSOCIATE_ID;
	
	var $debug = false;
	
	public function __construct($debug = false) {
        $this->debug = $debug;
    }
    
    public function Amazon($debug = false) {
        __construct($debug);
    }
    
	/**
	    *Get a signed URL
		*@param string $region used to define country
		*@param array $param used to build url
		*@return array $signature returns the signed string and its components
	*/
	public function generateSignature($param)
	{
		// url basics
		$signature['method']='GET';
		$signature['host']='ecs.amazonaws.'.$param['region'];
		$signature['uri']='/onca/xml';

	    // necessary parameters
		$param['Service'] = "AWSECommerceService";
	    $param['AWSAccessKeyId'] = $this->publicKey;
	    $param['Timestamp'] = gmdate("Y-m-d\TH:i:s\Z");
	    $param['Version'] = '2011-08-01';
		$param['AssociateTag'] = $this->associateId;
		ksort($param);
	    foreach ($param as $key=>$value)
	    {
	        $key = str_replace("%7E", "~", rawurlencode($key));
	        $value = str_replace("%7E", "~", rawurlencode($value));
	        $queryParamsUrl[] = $key."=".$value;
	    }
		// glue all the  "params=value"'s with an ampersand
	    $signature['queryUrl']= implode("&", $queryParamsUrl);

	    // we'll use this string to make the signature
		$StringToSign = $signature['method']."\n".$signature['host']."\n".$signature['uri']."\n".$signature['queryUrl'];
	    // make signature
	    $signature['string'] = str_replace("%7E", "~",
			rawurlencode(
				base64_encode(
					hash_hmac("sha256",$StringToSign,$this->privateKey,True
					)
				)
			)
		);
	    return $signature;
	}
	/**
	    * Get signed url response
		* @param string $region
		* @param array $params
		* @return string $signedUrl a query url with signature
	*/
	public function getSignedUrl($params)
	{
		$signature=$this->generateSignature($params);
		return $signedUrl= "http://".$signature['host'].$signature['uri'].'?'.$signature['queryUrl'].'&Signature='.$signature['string'];
	}
	
	public function buildParams($query) {
	    return array(
            "region"=>"com",
            "Operation"=>"ItemSearch", // we will be searching
            "SearchIndex"=>"Books", // search all categories, use "Books" to limit to books
            'ResponseGroup'=>'Images,ItemAttributes,EditorialReview,Reviews,Similarities,BrowseNodes',// we want images, item info, reviews, and related items
            "Keywords"=>"$query"); // this is what we are looking for, you could use the book's title instead
	}
	
	public function getResults($keyword, $format) {
	    $parameters = $this->buildParams($keyword);
        $queryUrl = $this->getSignedUrl($parameters);
        $result = load_simplexml($queryUrl) or die ("xml response not loading");
        return $this->parseResults($result, $queryUrl, $format);
	}
	
	public function parseResults($result, $queryurl, $format) {
	    $res = array();
	    if (strtolower($result->Items->Request->IsValid) == "false") {
	        $res["Completed"] = "False";
	        $res["RequestID"] = strval($result->OperationRequest[0]->RequestId[0]);
	        $res["RequestIsValid"] = strval($result->Items->Request->IsValid);
            $res["RequestProcessingTime"] = strval($result->OperationRequest[0]->RequestProcessingTime[0]);
	        $res["Error"] = strval($result->Items->Request->Errors->Error->Code);
	        if ($this->debug) {
                $res["RequestedAmazonURL"] = $queryurl;
            }
	    } else {
		    $completed = $result->Items->TotalResults[0] == 1;
			if ($this->debug) {
				$completed = false;
			}
	        $res["Completed"] = $result->Items->TotalResults[0] == 1;
	        $res["Items"] = $this->parseItems($result->Items, $completed, $format);
	        if ($this->debug) {
                $res["RequestedAmazonURL"] = $queryurl;
            }
	        $res["RequestID"] = strval($result->OperationRequest[0]->RequestId[0]);
	        $res["RequestIsValid"] = strval($result->Items->Request->IsValid);
            $res["RequestProcessingTime"] = strval($result->OperationRequest[0]->RequestProcessingTime[0]);
	        $res["ResultCount"] = strval($result->Items->TotalResults);
			$res["Error"] = "";
        }
            $res["Completed"] = false;
	    return $res;
	}
	
	public function parseItems($items, $completed = false, $format) {
	    $list = array();
	    
	    foreach($items->Item as $item) {
	        $list[] = new AmazonResult($item, $completed, $format);
	    }
	    return $list;
	}
}
?>
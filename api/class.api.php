<?php

/**
 * @file
 * Lots of this code comes from another project so it may be re-used
 */

/// Default API limit
define ('APIDEFLIM',1000);

/**
 * API class provinding tools for the actions
 */
class API extends Req {
    
    /**
     * API is working over Req which is working with $_REQUEST.
     * This function sets it properly from multiple sources:
     * 
     * - php://input (JSON Application)
     * - Request
     * 
     * [Code from SNE]
     */
    static function buildRequest(){
  
	// Get Data
	$json = file_get_contents('php://input');

	// Form the request from the imput
	if ($json!=""){
	    $_REQUEST = array_merge_recursive($_REQUEST,json_decode($json, true));
	}
    }

    /**
     * Check all required fields for an API request
     */
    static function checkRequest(){
	// user
	if (!Req::val('user')) API::throwErrorExit("User is required");
	// pass
	if (!Req::val('pass')) API::throwErrorExit("Password is required");
	// action
	if (!Req::val('action')) API::throwErrorExit("Action is required");
    }

    /**
     * Print JSON error message and exit
     */
    static function throwErrorExit($msg){
	echo json_encode(array("error"=>$msg));
	exit;
    }

    /**
     * Print API response. Format handling in this function
     * 
     * [Code from SNE]
     */
    static function printResponse($what) {
	$str="";
	if (!$what || !count($what)) {
	    $str=json_encode(json_decode("[]"));
	}
	
	// TODO extend here for different output format
	if (!Req::val('format') || Req::val('format')=="json"){
	    $str = json_encode($what);
	    if (Req::val('debug')) $str = self::prettifyJSON($str)."\n";

	    // JSONP
	    if (Req::val('callback')){
		echo  Req::val('callback')."(".$str.");";
		return;
	    }

	    // Pure JSON
	    echo $str;
	}
	else if (Req::val('format')=="xml"){
	    $xml = new SimpleXMLElement("<?xml version=\"1.0\"?><result></result>");
	    self::array_to_xml($what,$xml);
	    echo $xml->asXML();
	}


    }

    /**
     * Remove integer keys from an array
     */
    static function cleanAss(&$arr) {

	foreach($arr as $k => $v) {
	    if (is_array($arr[$k])) self::cleanAss($arr[$k]);
	    else if(is_numeric($k)) {
		unset($arr[$k]);
	    }
	}

    }
    
    /**
     * [SO]: http://stackoverflow.com/questions/173400/how-to-check-if-php-array-is-associative-or-sequential
     */
    private function is_assoc(&$array) {
	return (bool)count(array_filter(array_keys($array), 'is_string'));
    }
    
    
    /**
     * function defination to convert array to xml
     * 
     * God save SO !
     * [SO]:http://stackoverflow.com/questions/1397036/how-to-convert-array-to-simplexml
     */
    private static function array_to_xml($arr, &$xml) {

	foreach($arr as $key => $value) {
	    if(is_array($value)) {
		$key = is_numeric($key) ? "item$key" : $key;
		$subnode = $xml->addChild("$key");
		self::array_to_xml($value, $subnode);
	    }
	    else {
		$key = is_numeric($key) ? "item$key" : $key;
		$xml->addChild("$key","$value");
	    }
	}
    }


    /**
     * PHP independent prettifying solution for JSON!
     * 
     * Returns the $json string prettifyied...
     * 
     * TODO: reference somewhere in SO!
     */
    static function prettifyJSON( $json )
    {
	$result = '';
	$level = 0;
	$prev_char = '';
	$in_quotes = false;
	$ends_line_level = NULL;
	$json_length = strlen( $json );

	for( $i = 0; $i < $json_length; $i++ ) {
	    $char = $json[$i];
	    $new_line_level = NULL;
	    $post = "";
	    if( $ends_line_level !== NULL ) {
		$new_line_level = $ends_line_level;
		$ends_line_level = NULL;
	    }
	    if( $char === '"' && $prev_char != '\\' ) {
		$in_quotes = !$in_quotes;
	    } else if( ! $in_quotes ) {
		switch( $char ) {
		    case '}': case ']':
			$level--;
			$ends_line_level = NULL;
			$new_line_level = $level;
			break;

		    case '{': case '[':
			$level++;
		    case ',':
			$ends_line_level = $level;
			break;

		    case ':':
			$post = " ";
			break;

		    case " ": case "\t": case "\n": case "\r":
			$char = "";
			$ends_line_level = $new_line_level;
			$new_line_level = NULL;
			break;
		}
	    }
	    if( $new_line_level !== NULL ) {
		$result .= "\n".str_repeat( "\t", $new_line_level );
	    }
	    $result .= $char.$post;
	    $prev_char = $char;
	}

	return $result;
    }

}

?>
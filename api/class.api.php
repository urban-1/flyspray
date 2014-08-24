<?php

// Default API limit
define ('APIDEFLIM',1000);

class API extends Req {
    // Do stuff here! 
    // 1. Build internal request from php://input and set it as $_REQUEST!

    static function throwErrorExit($msg){
	echo json_encode(array("error"=>$msg));
	exit;
    }

    static function printResponse($what) {
	if (!$what || !count($what)) {
	    echo json_encode(json_decode("[]"));
	    return;
	}
	
	// TODO extend here for different output format
	if (!Req::val('format') || Req::val('format')=="json"){
	    $out = json_encode($what);
	    if (Req::val('debug')) echo self::prettifyJSON($out);
	    else echo $out;
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
	return (bool)count(array_filter(array_keys(&$array), 'is_string'));
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
<?php



// Useful functions that are used throughout



if( !function_exists('str_getcsv') ) {
	function str_getcsv($input, $delimiter=',', $enclosure='"', $escape=null, $eol=null) {
		$temp=fopen("php://memory", "rw");
		fwrite($temp, $input);
		fseek($temp, 0);
		$r = array();
		while (($data = fgetcsv($temp, 4096, $delimiter, $enclosure)) !== false) {
			$r[] = $data;
		}
		fclose($temp);
		return $r[0];
	}
}

function better_excerpt($string, $length) {

	$string = preg_replace( array('/<+\s*\/*\s*([A-Z][A-Z0-9]*)\b[^>]*\/*\s*>+/i','/\&\#160\;/'), array('',''), $string );
	if( strlen($string) > $length) {
		if( strpos($string,".",$length) === false ) {
			if( strpos($string," ",$length) === false ) $returner = mb_strcut($string,0,strpos($string," ",$length));
			else $returner = mb_strcut($string,0,$length);
		} else {
			$returner = mb_strcut($string,0,strpos($string,".",$length)+1);
		}
	} else $returner = $string;
	// scrub all html, fuck the periods. All html;/
	return trim($returner);
}

function stripper($source,$start,$end){
	$finder = $start;
	$left = strpos($stuff,$finder);
	$right = strpos($stuff,$end,$left)-$left+1;
	return substr($stuff,$left,$right);
}

function yanker($source,$start,$end){
	$finder = $start;
	$left = strpos($stuff,$finder);
	$right = strpos($stuff,$end,$left)-$left+1;
	return substr($stuff,0,$left).substr($stuff,$left+$right);
}

function date_smoosh($start, $end) {

	$start = strtotime($start);
	$end = strtotime($end);

	if( !empty($end) ) {
		if( date("m",$start ) == date("m",$end ) ) {
			$month1 = date("F",$start );
			$month2 ='';

			if( date("d",$start ) == date("d",$end ) ) {
				$day1 = date("jS",$start );
				$day2 = '';
			} else {
				$day1 = date("j",$start );
				$day2 = date("j",$end );
			}

		} else {
			$month1 = date("F",$start );
			$month2 = date("F",$end );

			$day1 = date("j",$start );
			$day2 = date("j",$end );
		}

		if(!empty($month2)) $month2 = ' - '.$month2;
		else if(!empty($day2)) $day2 = '-'.$day2;
	} else {
		$month1 = date("F",$start );
		$month2 ='';
		$day1 = date("jS", $start );
		$day2 = '';
	}

	return $month1.' '.$day1.$month2.' '.$day2;
}

function fixDate($date) { return substr($date, 0, strpos($date," GMT")); }

function getTagName($a) { return $a->tag; }

?>
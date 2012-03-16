<?php

// Sets some globals which are useful for different parsing methods
date_default_timezone_set('America/Los_Angeles');

if (!function_exists('str_getcsv')) { 
define( "PHP_OLD", true);
	function str_getcsv($input, $delimiter=',', $enclosure='"', $escape=null, $eol=null) { 
		$temp=fopen("php://memory", "rw"); 
		fwrite($temp, $input); 
		fseek($temp, 0); 
		$r = array(); 
		while (($data = fgetcsv($temp, 4096, $delimiter, $enclosure)) !== false) { 
		$r[] = $data; 
		} 
		fclose($temp); 
		return $r; 
	}
} else define( "PHP_OLD", false);

function no_null($var){
	$pass = is_array($var) && isset($var[0]) && $var[0] != NULL;
	return $pass;
}

// Compiles the various classes

require_once('connect.php');
require_once('crypt.php');
require_once('render.php');
require_once('form.php');
require_once('report.php');

?>
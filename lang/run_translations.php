<?php

// This utility can be used to convert all po files to mo files

$dir = getcwd();
$files = scandir( $dir);

include( dirname(__FILE__).'/../utils/pho-mo.php');

foreach ($files as $key => $value) {

	if( strpos($value, '.po') !== false && strpos($value, 'tmp') === false ) {
		// Cleaning up Drupal PO file
		$fh = fopen($value, 'r');
		$tmp = 'tmp-'.$value;
		$tmp_fh = fopen($tmp, 'a');

		# For some reason does not like to convert successfully unless the msgid is preceeded by #\n - not sure why
		while( ($line = fgets($fh, 65536)) !== false) {
			if( strpos($line, 'msgid') !== false && $last_line != '#' ) $fwrite = fwrite($tmp_fh, "#\n");
			$fwrite = fwrite($tmp_fh, $line);
			$last_line = $line;
		}
		fclose($fh);

		$new_mo = str_replace( '.po', '.mo', $value );

		// Converting
		if( phpmo_convert( $tmp, $new_mo ) ) echo "Converted $value\n";
		else echo "Failed to convert $value\n";
		unlink($tmp);
	}
}


?>
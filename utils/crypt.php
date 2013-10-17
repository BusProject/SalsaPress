<?php

// Class used to encrypt and decrypt the Salsa Password for storage
// Uses the WP Salt

class SalsaCrypt {

	public $pass = '';

	function __construct($stored = '') {
		if( strlen($stored) > 0 ) {
			$pass = '';
			$salt = str_split(wp_salt());
			$split = preg_split('/ /', $stored);
			for ($i=0; $i < count($split); $i++) {
				$pass .= chr($split[$i] / ord($salt[$i]) );
			}
			$this->pass = $pass;
		}
	}

	function store($hasher = '') {
		// Just uses the default wordpress salt.
		// Useful because it should be different from site to site
		$salt = str_split(wp_salt());
		$d = str_split($hasher);
		$hash = '';
		for ($i=0; $i < count($d); $i++) {
			$hash .= ord($d[$i])*ord($salt[$i]).' ';
		}
		return substr($hash,0,-1);
	}
}
?>
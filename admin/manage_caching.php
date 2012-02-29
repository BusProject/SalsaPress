<?php

add_option('salsapress_caches');

function  salsapress_cache_section() {
	echo '<p>SalsaPress caches outward-facing Salsa API calls to speed up your site. Each cache lives for about 12 hours so it may take a second to see changes from Salsa on forms, reports, and event lists.</p>';
}

function salsapress_cache() {
	$options = get_option('salsapress_options');
	$cache = isset( $options['salsapress_stop_cache'] ) ? $options['salsapress_stop_cache'] : false;
	$checked = $cache ?  ' checked="checked" ' : '';
	echo "<input ".$checked." id='salsapress_cache' name='salsapress_options[salsapress_stop_cache]' type='checkbox' />";
}

function salsapress_cache_reset() {
	echo '<h3 class="reset_caches">Click to Reset</h3>'.
	'<em>Site may slow down while cache is rebuilt</em>';
}

function salsapress_reset_caches() {
	$options = get_option('salsapress_caches');
	$success = true;
	foreach ($options as $key => $value) {
		$delete = delete_transient($key);
		if( !$delete ) {
			$get = get_transient($key) === false;
		}
		if( $delete || $get ) {
			$options[$key] = null;
		}
	}
	update_option('salsapress_caches',$options);
	echo json_encode(array('success' => $success));
	exit;
}

?>
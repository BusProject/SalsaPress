<?php

add_shortcode( 'salsa', 'salsapress_salsa_render' );

//Jolokia filters out the shortcode
function salsapress_salsa_render($atts, $content = null ) {
	extract( shortcode_atts( array(
		'data' => ''
	), $atts ) );

	$info = array();
	foreach( json_decode('['.$data.']') as $v ):
		$info[$v->name] = $v->value;
	endforeach;

	$render = new SalsaRender($info['type']);
	$done = $render->render($info);
	return $done;
}


add_filter("mce_external_plugins", "add_salsapress_tinymce_plugin");


function add_salsapress_tinymce_plugin($plugin_array) {
	$plugin_array['salsa'] =  salspress_base.'admin/editor_plugin.js';
	wp_enqueue_script( 'SalsaPress', salspress_base.'admin/salsapress_admin.js',array( 'jquery' ), '1.0', true );

	localize_scripts();
	return $plugin_array;
}

?>
<?php 
/*
Plugin Name: SalsaPress
Plugin URI: http://busproject.org/
Description: SalsaPress connects WordPress to Salsa
Author: Scott Duncombe
Version: 1.0
Author URI: http://scottduncombe.com/
*/

// Setting a base path. Easy change if the code is going to be incorporated into a theme, use get_bloginfo('theme_directory') instead
$base = WP_PLUGIN_URL . '/' . str_replace(basename( __FILE__), "" ,plugin_basename(__FILE__));
define('base', $base);

// Setting up the Admin Page and SalsaPress options
require_once('utils/crypt.php');
require_once('utils/classes.php');
require_once('utils/functions.php');
require_once('utils/shortcode.php');
require_once('utils/ajax.php');

// Admin Menu
require_once('admin/admin_menu.php');
// Embedder
require_once('admin/embed.php');

// Widgets
require_once('widgets/coming_events.php');
require_once('widgets/signup_page.php');
require_once('widgets/event_form.php');

// Setting the defaults when activating the plugin
register_activation_hook(__FILE__, 'salsapress_defaults');
add_action('wp_enqueue_scripts', 'enque_salsapress');
add_action('admin_init', 'salsapress_options_init' );


function salsapress_options_init(){
	add_action('media_buttons', 'salsapress_form_button', 20);
    add_action('wp_ajax_salsapress_salsa_report_render', 'salsapress_salsa_report_render');
	add_action('wp_ajax_salsapress_form_button_iframe', 'salsapress_form_button_iframe');
}

function enque_salsapress() {
	//Enqueing external scripts and styles
	wp_enqueue_script( 'SalsaPress', WP_PLUGIN_URL.'/SalsaPress/utils/SalsaPress.js',array( 'jquery' ), '1.0', true );
	wp_enqueue_style( 'SalsaPress', WP_PLUGIN_URL.'/SalsaPress/utils/SalsaPress.css','', '0.5', 'all' );	
	localize_scripts();
}

function localize_scripts() {
	wp_localize_script( 'SalsaPress', 'SalsaPressVars', array(
		'ajaxurl'          => admin_url( 'admin-ajax.php' ),
		'SalsaAjax' => wp_create_nonce( 'myajax-post-comment-nonce' ),
		'stylesheet_directory' => base
		)
	);
}


?>
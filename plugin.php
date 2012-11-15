<?php 
/*
Plugin Name: SalsaPress
Plugin URI: https://github.com/BusProject/SalsaPress
Description: SalsaPress connects WordPress to Salsa
Author: Scott Duncombe
Version: 2.1
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

// Admin Menus
require_once('admin/admin_menu.php');
require_once('admin/manage_caching.php');
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
	add_action('wp_ajax_salsapress_reset_caches', 'salsapress_reset_caches');
	
}

function enque_salsapress() {
	//Enqueing external scripts and styles
	wp_enqueue_script( 'SalsaPress', base.'utils/SalsaPress.js',array( 'jquery' ), '1.0', true );
	wp_enqueue_style( 'SalsaPress', base.'utils/SalsaPress.css','', '0.5', 'all' );	
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

/*  Copyright 2012  Scott Duncombe  (email : srduncombe@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


?>
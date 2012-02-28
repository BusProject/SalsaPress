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
require_once('admin/admin_menu.php');
// Setting the defaults when activating the plugin
register_activation_hook(__FILE__, 'buspress_defaults');


?>
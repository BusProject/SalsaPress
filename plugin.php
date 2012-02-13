<?php 
/*
Plugin Name: BusPress
Plugin URI: http://busproject.org/
Description: BusPress connects WordPress to Salsa
Author: Scott Duncombe
Version: 1.0
Author URI: http://scottduncombe.com/
*/

// Setting a base path. Easy change if the code is going to be incorporated into a theme, use get_bloginfo('theme_directory') instead
$base = WP_PLUGIN_URL . '/' . str_replace(basename( __FILE__), "" ,plugin_basename(__FILE__));
define('base', $base);


require_once('admin/admin_menu.php');

?>
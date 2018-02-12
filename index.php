<?php
/*
Plugin Name: MF Widget Logic (Select)
Plugin URI: 
Description: 
Version: 3.0.4
Licence: GPLv2 or later
Author: Martin Fors
Author URI: http://frostkom.se
Text Domain: lang_wls
Domain Path: /lang

Depends: MF Base
*/

include_once("include/functions.php");

if(is_admin())
{
	add_action('admin_menu', 'menu_wls');
	add_filter('widget_update_callback', 'widget_update_wls', 10, 3);
	add_action('sidebar_admin_setup', 'sidebar_admin_wls');

	add_action('rwmb_meta_boxes', 'meta_boxes_wls');

	add_action('clone_page', 'clone_page_wls', 10, 2);

	add_filter('customize_loaded_components', 'remove_widgets_wls');

	load_plugin_textdomain('lang_wls', false, dirname(plugin_basename(__FILE__)).'/lang/');
}

else
{
	add_filter('sidebars_widgets', 'sidebars_widgets_wls', 10);
}
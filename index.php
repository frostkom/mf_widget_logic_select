<?php
/*
Plugin Name: MF Widget Logic (Select)
Plugin URI: https://github.com/frostkom/mf_widget_logic_select
Description: 
Version: 3.1.6
Licence: GPLv2 or later
Author: Martin Fors
Author URI: https://frostkom.se
Text Domain: lang_wls
Domain Path: /lang

Depends: MF Base
GitHub Plugin URI: frostkom/mf_widget_logic_select
*/

include_once("include/classes.php");
include_once("include/functions.php");

$obj_wls = new mf_widget_logic_select();

if(is_admin())
{
	add_action('admin_init', array($obj_wls, 'admin_init'), 0);

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
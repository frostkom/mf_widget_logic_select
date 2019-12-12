<?php
/*
Plugin Name: MF Widget Logic
Plugin URI: https://github.com/frostkom/mf_widget_logic_select
Description: 
Version: 3.2.18
Licence: GPLv2 or later
Author: Martin Fors
Author URI: https://frostkom.se
Text Domain: lang_wls
Domain Path: /lang

Depends: MF Base
GitHub Plugin URI: frostkom/mf_widget_logic_select
*/

include_once("include/classes.php");

$obj_wls = new mf_widget_logic_select();

if(is_admin())
{
	add_action('admin_init', array($obj_wls, 'admin_init'), 0);

	add_filter('widget_update_callback', array($obj_wls, 'widget_update_callback'), 10, 3);
	add_action('sidebar_admin_setup', array($obj_wls, 'sidebar_admin_setup'));

	add_action('rwmb_meta_boxes', array($obj_wls, 'rwmb_meta_boxes'));

	add_action('clone_page', array($obj_wls, 'clone_page'), 10, 2);

	add_filter('customize_loaded_components', array($obj_wls, 'customize_loaded_components'));

	load_plugin_textdomain('lang_wls', false, dirname(plugin_basename(__FILE__)).'/lang/');
}

else
{
	add_filter('sidebars_widgets', array($obj_wls, 'sidebars_widgets'), 10);
}

add_filter('get_widget_search', array($obj_wls, 'get_widget_search'));
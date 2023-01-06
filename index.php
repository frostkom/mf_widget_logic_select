<?php
/*
Plugin Name: MF Widget Logic
Plugin URI: https://github.com/frostkom/mf_widget_logic_select
Description:
Version: 3.5.25
Licence: GPLv2 or later
Author: Martin Fors
Author URI: https://martinfors.se
Text Domain: lang_wls
Domain Path: /lang

Depends: MF Base
GitHub Plugin URI: frostkom/mf_widget_logic_select
*/

if(!function_exists('is_plugin_active') || function_exists('is_plugin_active') && is_plugin_active("mf_base/index.php"))
{
	include_once("include/classes.php");

	$obj_wls = new mf_widget_logic_select();

	if(is_admin())
	{
		register_uninstall_hook(__FILE__, 'uninstall_wls');

		add_action('admin_init', array($obj_wls, 'admin_init'), 0);

		add_filter('gutenberg_use_widgets_block_editor', '__return_false', 100);

		add_filter('widget_update_callback', array($obj_wls, 'widget_update_callback'), 10, 3);
		add_action('sidebar_admin_setup', array($obj_wls, 'sidebar_admin_setup'));

		add_action('rwmb_meta_boxes', array($obj_wls, 'rwmb_meta_boxes'));

		add_action('clone_page', array($obj_wls, 'clone_page'), 10, 2);

		if(is_plugin_active("mf_theme_core/index.php"))
		{
			global $obj_theme_core;

			if(!isset($obj_theme_core))
			{
				$obj_theme_core = new mf_theme_core();
			}

			if($obj_theme_core->is_theme_active())
			{
				add_filter('customize_loaded_components', array($obj_wls, 'customize_loaded_components'));
			}
		}

		load_plugin_textdomain('lang_wls', false, dirname(plugin_basename(__FILE__))."/lang/");
	}

	else
	{
		add_action('wp_head', array($obj_wls, 'wp_head'), 0);

		add_filter('sidebars_widgets', array($obj_wls, 'sidebars_widgets'), 10);

		add_filter('filter_before_widget', array($obj_wls, 'filter_before_widget'));
	}

	add_filter('get_widget_search', array($obj_wls, 'get_widget_search'));

	function uninstall_wls()
	{
		mf_uninstall_plugin(array(
			'options' => array('widget_logic', 'widget_logic_state', 'widget_logic_screens'),
		));
	}
}
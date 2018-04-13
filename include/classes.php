<?php

class mf_widget_logic_select
{
	function __construct(){}

	function admin_init()
	{
		$plugin_include_url = plugin_dir_url(__FILE__);
		$plugin_version = get_plugin_version(__FILE__);

		mf_enqueue_style('style_wls', $plugin_include_url."style_wp.css", $plugin_version);
		mf_enqueue_script('script_wls', $plugin_include_url."script_wp.js", $plugin_version);
	}
}
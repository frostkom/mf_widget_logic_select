<?php

if(!defined('ABSPATH'))
{
	header("Content-Type: text/css; charset=utf-8");

	$folder = str_replace("/wp-content/plugins/mf_widget_logic_select/include", "/", dirname(__FILE__));

	require_once($folder."wp-load.php");
}

else
{
	global $wpdb;
}

$arr_widget_logic_state = get_option_or_default('widget_logic_state', array());
$arr_widget_logic_screens = get_option_or_default('widget_logic_screens', array());

$out = "";

foreach($arr_widget_logic_state as $key => $value)
{
	switch($value)
	{
		case 'logged_in':
			$out .= "body:not(.is_logged_in) .".$key."
			{
				display: none;	
			}";
		break;

		case 'logged_out':
			$out .= "body.is_logged_in .".$key."
			{
				display: none;	
			}";
		break;
	}

	//do_log("WLS: ".$key.": ".var_export($value, true));
}

foreach($arr_widget_logic_screens as $key => $arr_value)
{
	if(is_array($arr_value) && count($arr_value) > 0)
	{
		$display_on_mobile = $display_on_tablet = $display_on_desktop = false;

		foreach($arr_value as $value)
		{
			if($value == 'mobile')
			{
				$display_on_mobile = true;
			}

			if($value == 'tablet')
			{
				$display_on_tablet = true;
			}

			if($value == 'desktop')
			{
				$display_on_desktop = true;
			}
		}

		if($display_on_mobile == false)
		{
			$out .= ".is_mobile .".$key."
			{
				display: none;	
			}";
		}

		if($display_on_tablet == false)
		{
			$out .= ".is_tablet .".$key."
			{
				display: none;	
			}";
		}

		if($display_on_desktop == false)
		{
			$out .= ".is_desktop .".$key."
			{
				display: none;	
			}";
		}
	}

	//do_log("WLS: ".$key.": ".var_export($value, true));
}

echo "@media all
{"
	.$out
."}";
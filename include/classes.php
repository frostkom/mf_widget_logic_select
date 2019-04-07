<?php

class mf_widget_logic_select
{
	function __construct()
	{
		$this->meta_prefix = 'mf_wls_';
	}

	function get_widget_search($search_for)
	{
		$out = 0;

		if($search_for != '')
		{
			$arr_search_widget = get_option('widget_'.$search_for);

			if(is_array($arr_search_widget) && count($arr_search_widget) > 0)
			{
				$arr_widget_area = get_option('sidebars_widgets');
				$arr_widget_logic = get_option('widget_logic');

				//do_log("Widgets: ".$search_for." --- ".var_export($arr_search_widget, true)." ---------- ".var_export($arr_widget_area, true)." ---------- ".var_export($arr_widget_logic, true));

				foreach($arr_search_widget as $key_search => $arr_search)
				{
					foreach($arr_widget_area as $arr_area)
					{
						if(is_array($arr_area) && count($arr_area) > 0)
						{
							foreach($arr_area as $widget)
							{
								if($search_for."-".$key_search == $widget)
								{
									if(isset($arr_widget_logic[$widget]))
									{
										if($arr_widget_logic[$widget] == '')
										{
											$out = get_option('page_on_front');
										}

										else
										{
											$arr_page_widget_logic = explode('||', $arr_widget_logic[$widget]);

											foreach($arr_page_widget_logic as $page_widget_logic)
											{
												$page_id = get_match("/is_page\((.*?)\)/is", $page_widget_logic, false);
												$singular_type = trim(get_match("/is_singular\((.*?)\)/is", $page_widget_logic, false), '\"');

												if($page_id > 0)
												{
													$out = $page_id;

													break;
												}

												else if($singular_type != '')
												{
													$arr_data = array();
													get_post_children(array('add_choose_here' => false, 'post_type' => $singular_type, 'limit' => 1), $arr_data);

													if(count($arr_data) > 0)
													{
														foreach($arr_data as $key => $value)
														{
															$out = $key;

															break;
														}

														break;
													}
												}

												/*else if(substr($page_widget_logic, 0, 12) == "is_singular(")
												{
													$post_type = get_match("/\"(.*?)\\/", $page_widget_logic, false);

													$arr_data = array();
													get_post_children(array('add_choose_here' => false, 'post_type' => $post_type, 'limit' => 1), $arr_data);

													if(count($arr_data) > 0)
													{
														foreach($arr_data as $key => $value)
														{
															$out = $key;

															break;
														}

														break;
													}
												}*/

												else
												{
													switch($page_widget_logic)
													{
														case 'is_home()':
															$out = get_option('page_on_front');

															break;
														break;

														/*case 'is_category()':
															$out = "???";

															break;
														break;*/

														default:
															do_log("Widget Logic Missing 2: '".$page_widget_logic."'");
														break;
													}
												}
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}

		return $out;
	}

	function admin_init()
	{
		$plugin_include_url = plugin_dir_url(__FILE__);
		$plugin_version = get_plugin_version(__FILE__);

		mf_enqueue_style('style_wls', $plugin_include_url."style_wp.css", $plugin_version);
		mf_enqueue_script('script_wls', $plugin_include_url."script_wp.js", $plugin_version);
	}

	function widget_update_callback($instance, $new_instance, $this_widget)
	{
		if((!$wl_options = get_option('widget_logic')) || !is_array($wl_options))
		{
			$wl_options = array();
		}

		$widget_id = isset($this_widget->id) ? $this_widget->id : "";

		if(isset($_POST[$widget_id.'-widget_logic']))
		{
			$wl_options[$widget_id] = trim($_POST[$widget_id.'-widget_logic']);

			update_option('widget_logic', $wl_options);
		}

		return $instance;
	}

	function widget_logic_empty_control(){}

	function widget_logic_extra_control()
	{
		global $wp_registered_widget_controls;

		if((!$wl_options = get_option('widget_logic')) || !is_array($wl_options))
		{
			$wl_options = array();
		}

		$params = func_get_args();
		$id = array_pop($params);

		// go to the original control function
		$callback = $wp_registered_widget_controls[$id]['callback_wl_redirect'];

		if(is_callable($callback))
		{
			call_user_func_array($callback, $params);
		}

		$value = !empty($wl_options[$id]) ? htmlspecialchars(stripslashes($wl_options[$id]), ENT_QUOTES) : '';

		// dealing with multiple widgets - get the number. if -1 this is the 'template' for the admin interface
		$number = $params[0]['number'];

		if($number == -1)
		{
			$number = "__i__";
			$value = "";
		}

		$id_disp = $id;

		if(isset($wp_registered_widget_controls[$id]['id_base']) && isset($number))
		{
			$id_disp = $wp_registered_widget_controls[$id]['id_base']."-".$number;
		}

		/*if($number != "__i__")
		{*/
			$arr_values = explode(" || ", $value);

			echo "<div id='".$id_disp."-widget_logic_select' class='widget_logic_select mf_form'>"
				.get_toggler_container(array('type' => 'start', 'text' => __("Choose page to display this widget on", 'lang_wls'), 'rel' => $id_disp))
					.show_select(array('data' => get_post_types_for_select(array('post_status' => '')), 'name' => $id_disp."-widget_logic_data[]", 'value' => $arr_values))
					.show_textfield(array('name' => $id_disp."-widget_logic", 'value' => $value, 'xtra' => "class='".$id_disp."-widget_logic widefat".(preg_match("/is_singular/", $value) ? "" : " hide")."'"))
				.get_toggler_container(array('type' => 'end'))
			."</div>";
		//}
	}

	function sidebar_admin_setup()
	{
		global $wp_registered_widgets, $wp_registered_widget_controls;

		if((!$wl_options = get_option('widget_logic')) || !is_array($wl_options))
		{
			$wl_options = array();
		}

		// Add extra field to each widget
		foreach($wp_registered_widgets as $id => $widget)
		{
			if(!$wp_registered_widget_controls[$id])
			{
				wp_register_widget_control($id, $widget['name'], array($this, 'widget_logic_empty_control'));
			}

			$wp_registered_widget_controls[$id]['callback_wl_redirect'] = $wp_registered_widget_controls[$id]['callback'];
			$wp_registered_widget_controls[$id]['callback'] = array($this, 'widget_logic_extra_control');

			array_push($wp_registered_widget_controls[$id]['params'], $id);
		}

		// Update options
		if('post' == strtolower($_SERVER['REQUEST_METHOD']))
		{
			if(isset($_POST['widget-id']))
			{
				foreach((array)$_POST['widget-id'] as $widget_number => $widget_id)
				{
					if(isset($_POST[$widget_id.'-widget_logic']))
					{
						$wl_options[$widget_id] = trim($_POST[$widget_id.'-widget_logic']);
					}
				}
			}

			// clean up empty options
			$regd_plus_new = array_merge(
				array_keys($wp_registered_widgets),
				array_values((array)(isset($_POST['widget-id']) ? $_POST['widget-id'] : array()))
			);

			foreach(array_keys($wl_options) as $key)
			{
				if(!in_array($key, $regd_plus_new))
				{
					unset($wl_options[$key]);
				}
			}
		}

		update_option('widget_logic', $wl_options);
	}

	function meta_page_widgets()
	{
		global $wp_registered_widgets;

		$out = "";

		$post_id = filter_input(INPUT_GET, 'post', FILTER_SANITIZE_NUMBER_INT);

		if($post_id > 0)
		{
			$post_type = get_post_type($post_id);

			$arr_output = array();
			$arr_sidebars = wp_get_sidebars_widgets();
			$arr_sidebar_names = get_sidebars_for_select();
			$arr_widget_logic = get_option('widget_logic');

			foreach($arr_sidebars as $sidebar_key => $sidebar)
			{
				if($sidebar_key != 'wp_inactive_widgets')
				{
					$arr_output[$sidebar_key] = array();

					if(is_array($sidebar))
					{
						foreach($sidebar as $widget)
						{
							$arr_widget_parts = explode('-', $widget);

							$count_temp = count($arr_widget_parts) - 1;

							$widget_type = "";

							for($i = 0; $i < $count_temp; $i++)
							{
								$widget_type .= ($i > 0 ? "-" : "").$arr_widget_parts[$i];
							}

							$widget_id = $arr_widget_parts[$count_temp];

							if(isset($arr_widget_logic[$widget]) && $arr_widget_logic[$widget] != '')
							{
								$show_on_page = false;

								$arr_page_widget_logic = explode('||', $arr_widget_logic[$widget]);

								foreach($arr_page_widget_logic as $page_widget_logic)
								{
									$page_widget_logic = trim($page_widget_logic);

									$page_id = get_match("/is_page\((.*?)\)/is", $page_widget_logic, false);
									$singular_type = trim(get_match("/is_singular\((.*?)\)/is", $page_widget_logic, false), '\"');

									if($page_id > 0)
									{
										if($page_id == $post_id)
										{
											$show_on_page = true;
										}
									}

									else if($singular_type != '')
									{
										if($singular_type == $post_type)
										{
											$show_on_page = true;
										}
									}

									else
									{
										switch($page_widget_logic)
										{
											case 'is_home()':
												if($post_id == get_option('page_on_front'))
												{
													$show_on_page = true;
												}
											break;

											case 'is_category()':
												if(is_category())
												{
													$show_on_page = true;
												}
											break;

											default:
												do_log("Widget Logic Missing 1: '".$page_widget_logic."'");
											break;
										}
									}
								}

								if($show_on_page == true)
								{
									$widget_name = $wp_registered_widgets[$widget]['name']; //wp_widget_description($widget)

									$arr_output[$sidebar_key][$widget] = $widget_name;
								}
							}
						}
					}

					else
					{
						do_log("Something went wrong with sidebar (".var_export($sidebar, true).", ".var_export($arr_sidebars, true).")");
					}

					if(count($arr_output[$sidebar_key]) == 0)
					{
						unset($arr_output[$sidebar_key]);
					}
				}
			}

			if(count($arr_output) > 0)
			{
				$out .= "<ul class='meta_list'>";

					foreach($arr_output as $sidebar_key => $sidebar)
					{
						if(isset($arr_sidebar_names[$sidebar_key]))
						{
							$out .= "<li>".$arr_sidebar_names[$sidebar_key]."</li>
							<ul>";

								foreach($sidebar as $widget_key => $widget)
								{
									$out .= "<li><a href='".admin_url("widgets.php#".$sidebar_key)."&".$widget_key."'>".$widget."</a></li>";
								}

							$out .= "</ul>";
						}

						else
						{
							do_log("The Widget Area does not exist (".$sidebar_key.", ".var_export($arr_sidebar_names, true).")"); //var_export($arr_output, true)
						}
					}

				$out .= "</ul>";
			}
		}

		return $out;
	}

	function rwmb_meta_boxes($meta_boxes)
	{
		$meta_boxes[] = array(
			'id' => $this->meta_prefix.'widgets',
			'title' => __("Widgets", 'lang_wls'),
			'post_types' => get_post_types_for_metabox(),
			'context' => 'normal',
			'priority' => 'low',
			'fields' => array(
				array(
					'id' => $this->meta_prefix.'widgets',
					'type' => 'custom_html',
					'callback' => array($this, 'meta_page_widgets'),
				),
			)
		);

		return $meta_boxes;
	}

	function clone_page($post_id_old, $post_id_new)
	{
		$widget_logic = get_option('widget_logic');

		$updated = false;

		foreach($widget_logic as $key => $value)
		{
			if(preg_match("/is_page\(".$post_id_old."\)/", $value))
			{
				$widget_logic[$key] .= " || is_page(".$post_id_new.")";

				$updated = true;
			}
		}

		if($updated == true)
		{
			update_option('widget_logic', $widget_logic);
		}
	}

	function customize_loaded_components($components)
	{
		$i = array_search('widgets', $components);

		if(false !== $i)
		{
			unset($components[$i]);
		}

		return $components;
	}

	function sidebars_widgets($sidebars_widgets)
	{
		global $wp_reset_query_is_done;

		if((!$wl_options = get_option('widget_logic')) || !is_array($wl_options))
		{
			$wl_options = array();
		}

		foreach($sidebars_widgets as $widget_area => $widget_list)
		{
			if($widget_area == 'wp_inactive_widgets' || empty($widget_list))
			{
				continue;
			}

			foreach($widget_list as $pos => $widget_id)
			{
				if(empty($wl_options[$widget_id]))
				{
					continue;
				}

				$wl_value = stripslashes(trim($wl_options[$widget_id]));

				if(empty($wl_value))
				{
					continue;
				}

				$wl_value = apply_filters('widget_logic_eval_override', $wl_value);

				if($wl_value === false)
				{
					unset($sidebars_widgets[$widget_area][$pos]);

					continue;
				}

				if($wl_value === true)
				{
					continue;
				}

				if(stristr($wl_value, "return") === false)
				{
					$wl_value = "return (".$wl_value.");";
				}

				if(!eval($wl_value))
				{
					unset($sidebars_widgets[$widget_area][$pos]);
				}
			}
		}

		return $sidebars_widgets;
	}
}
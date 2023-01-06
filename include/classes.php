<?php

class mf_widget_logic_select
{
	function __construct()
	{
		$this->meta_prefix = 'mf_wls_';
	}

	function get_widget_search($search_for)
	{
		global $wpdb;

		$out = 0;

		if($search_for != '')
		{
			$arr_search_widget = get_option('widget_'.$search_for);

			if(is_array($arr_search_widget) && count($arr_search_widget) > 0)
			{
				$arr_widget_area = get_option('sidebars_widgets');
				$arr_widget_logic = get_option('widget_logic');

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
									if(!isset($arr_widget_logic[$widget]) || $arr_widget_logic[$widget] == '')
									{
										$out = get_option('page_on_front');

										// Get first published page if displaying latest posts
										if(!($out > 0))
										{
											$show_on_front = get_option('show_on_front');

											if($show_on_front == 'posts')
											{
												$out = $wpdb->get_var($wpdb->prepare("SELECT ID FROM ".$wpdb->posts." WHERE post_type = %s AND post_status = %s ORDER BY menu_order ASC LIMIT 0, 1", 'page', 'publish'));
											}
										}

										break 3;
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
												if(get_post_status($page_id) == 'publish')
												{
													$out = $page_id;
												}

												break 4;
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

														break 5;
													}

													break 4;
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

														break 5;
													}

													break 4;
												}
											}*/

											else
											{
												switch($page_widget_logic)
												{
													case 'is_front_page()':
														$page_on_front = get_option('page_on_front');

														if($page_on_front > 0)
														{
															$out = $page_on_front;
														}
													break;

													case 'is_home()':
														// Old way...
														//$out = get_option('page_on_front');

														$show_on_front = get_option('show_on_front');
														$page_on_front = get_option('page_on_front');
														$page_for_posts = get_option('page_for_posts');

														if(($show_on_front == 'page' || $page_on_front > 0) && $page_for_posts > 0)
														{
															$out = $page_for_posts;
														}

														else
														{
															$out = $page_on_front;
														}

														break 4;
													break;

													/*case 'is_category()':
														$out = "???";

														break 4;
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

		return $out;
	}

	function admin_init()
	{
		global $pagenow;

		switch($pagenow)
		{
			case 'post.php':
				$plugin_include_url = plugin_dir_url(__FILE__);
				$plugin_version = get_plugin_version(__FILE__);

				mf_enqueue_style('style_wls', $plugin_include_url."style_post_wp.css", $plugin_version);
			break;

			case 'widgets.php':
				$plugin_include_url = plugin_dir_url(__FILE__);
				$plugin_version = get_plugin_version(__FILE__);

				mf_enqueue_style('style_widgets_wls', $plugin_include_url."style_widgets_wp.css", $plugin_version);
				mf_enqueue_script('script_widgets_wls', $plugin_include_url."script_widgets_wp.js", array(
					'choose_here_text' => __("Filter Widgets Here", 'lang_wls'),
				), $plugin_version);
			break;
		}
	}

	function widget_update_callback($instance, $new_instance, $this_widget)
	{
		$widget_id = (isset($this_widget->id) ? $this_widget->id : "");

		if(isset($_POST[$widget_id.'-widget_logic_state']))
		{
			$arr_widget_logic_state = get_option_or_default('widget_logic_state', array());

			$arr_widget_logic_state[$widget_id] = trim($_POST[$widget_id.'-widget_logic_state']);

			update_option('widget_logic_state', $arr_widget_logic_state);
		}

		if(isset($_POST[$widget_id.'-widget_logic_screens']))
		{
			$arr_widget_logic_screens = get_option_or_default('widget_logic_screens', array());

			$arr_widget_logic_screens[$widget_id] = trim($_POST[$widget_id.'-widget_logic_screens']);

			update_option('widget_logic_screens', $arr_widget_logic_screens);
		}

		/*else
		{
			$arr_widget_logic_screens = get_option_or_default('widget_logic_screens', array());

			unset($arr_widget_logic_screens[$widget_id]);

			update_option('widget_logic_screens', $arr_widget_logic_screens);
		}*/

		if(isset($_POST[$widget_id.'-widget_logic']))
		{
			$wl_options = get_option_or_default('widget_logic', array());

			$wl_options[$widget_id] = trim($_POST[$widget_id.'-widget_logic']);

			update_option('widget_logic', $wl_options);
		}

		return $instance;
	}

	function widget_logic_empty_control(){}

	function get_logged_in_state_for_select()
	{
		return array(
			'' => "-- ".__("Choose Here", 'lang_wls')." --",
			'logged_out' => __("Logged Out", 'lang_wls'),
			'logged_in' => __("Logged In", 'lang_wls'),
		);
	}

	function get_screens_for_select()
	{
		return array(
			'mobile' => __("Mobile", 'lang_wls'),
			'tablet' => __("Tablet", 'lang_wls'),
			'desktop' => __("Desktop", 'lang_wls'),
		);
	}

	function widget_logic_extra_control()
	{
		global $wp_registered_widget_controls;

		$arr_widget_logic_state = get_option('widget_logic_state');
		$arr_widget_logic_screens = get_option('widget_logic_screens');
		$wl_options = get_option_or_default('widget_logic', array());

		$params = func_get_args();
		$id = array_pop($params);

		// go to the original control function
		$callback = $wp_registered_widget_controls[$id]['callback_wl_redirect'];

		if(is_callable($callback))
		{
			call_user_func_array($callback, $params);
		}

		$value = (!empty($wl_options[$id]) ? htmlspecialchars(stripslashes($wl_options[$id]), ENT_QUOTES) : '');

		// dealing with multiple widgets - get the number. if -1 this is the 'template' for the admin interface
		$number = $params[0]['number'];

		if($number == -1)
		{
			$number = "__i__";
			$arr_widget_logic_screens = array();
			$value = "";
		}

		$id_disp = $id;

		if(isset($wp_registered_widget_controls[$id]['id_base']) && isset($number))
		{
			$id_disp = $wp_registered_widget_controls[$id]['id_base']."-".$number;
		}

		/*if($number != "__i__")
		{*/
			$arr_logic_pages = explode(" || ", $value);

			echo "<div id='".$id_disp."-widget_logic_select' class='widget_logic_select mf_form'>"
				.get_toggler_container(array('type' => 'start', 'text' => __("Choose where to display this widget", 'lang_wls'), 'rel' => $id_disp))
					.show_select(array('data' => $this->get_logged_in_state_for_select(), 'name' => $id_disp."-widget_logic_state", 'value' => (isset($arr_widget_logic_state[$id_disp]) ? $arr_widget_logic_state[$id_disp] : array()), 'class' => "widget_logic_state"))
					.show_select(array('data' => $this->get_screens_for_select(), 'name' => $id_disp."-widget_logic_screens[]", 'value' => (isset($arr_widget_logic_screens[$id_disp]) ? $arr_widget_logic_screens[$id_disp] : array()), 'class' => "widget_logic_screens"))
					.show_select(array('data' => get_post_types_for_select(array('post_status' => '')), 'name' => $id_disp."-widget_logic_data[]", 'value' => $arr_logic_pages, 'class' => "widget_logic_page"))
					.show_textfield(array('name' => $id_disp."-widget_logic", 'value' => $value, 'xtra' => "class='".$id_disp."-widget_logic widefat widget_logic".(preg_match("/is_singular/", $value) ? "" : " hide")."'"))
				.get_toggler_container(array('type' => 'end'))
			."</div>";
		//}
	}

	function sidebar_admin_setup()
	{
		global $wp_registered_widgets, $wp_registered_widget_controls;

		$arr_widget_logic_state = get_option_or_default('widget_logic_state', array());
		$arr_widget_logic_screens = get_option_or_default('widget_logic_screens', array());
		$wl_options = get_option_or_default('widget_logic', array());

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
					if(isset($_POST[$widget_id.'-widget_logic_state']))
					{
						$arr_widget_logic_state[$widget_id] = $_POST[$widget_id.'-widget_logic_state'];
					}

					if(isset($_POST[$widget_id.'-widget_logic_screens']))
					{
						$arr_widget_logic_screens[$widget_id] = $_POST[$widget_id.'-widget_logic_screens'];
					}

					else
					{
						unset($arr_widget_logic_screens[$widget_id]);
					}

					if(isset($_POST[$widget_id.'-widget_logic']))
					{
						$wl_options[$widget_id] = trim($_POST[$widget_id.'-widget_logic']);
					}
				}
			}

			// Clean up empty options
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

		update_option('widget_logic_state', $arr_widget_logic_state);
		update_option('widget_logic_screens', $arr_widget_logic_screens);
		update_option('widget_logic', $wl_options);
	}

	function meta_page_widgets()
	{
		global $wp_registered_widgets, $post, $obj_theme_core;

		$out = "";

		$post_id = $post->ID;

		if($post_id > 0)
		{
			$post_type = get_post_type($post_id);

			if(!isset($obj_theme_core))
			{
				$obj_theme_core = new mf_theme_core();
			}

			$theme_dir_name = $obj_theme_core->get_theme_dir_name();

			$arr_output = array();

			$arr_output['widget_window_side'] = array();
			$arr_output['widget_slide'] = array();
			$arr_output['widget_pre_header'] = array();
			$arr_output['widget_header'] = array();
			$arr_output['widget_after_header'] = array();

			switch($theme_dir_name)
			{
				case 'mf_theme':
					$arr_output['widget_front'] = array();
				break;

				case 'mf_parallax':
					$arr_output['widget_pre_content'] = array();
				break;
			}

			$arr_output['widget_sidebar_left'] = array();
			$arr_output['widget_sidebar'] = array();
			$arr_output['widget_after_heading'] = array();
			$arr_output['widget_after_content'] = array();
			$arr_output['widget_below_content'] = array();
			$arr_output['widget_pre_footer'] = array();
			$arr_output['widget_footer'] = array();

			$arr_sidebars = wp_get_sidebars_widgets();
			$arr_sidebar_names = get_sidebars_for_select();
			$arr_widget_logic = get_option('widget_logic');

			foreach($arr_sidebars as $sidebar_key => $arr_sidebars)
			{
				if($sidebar_key != 'wp_inactive_widgets')
				{
					if(!isset($arr_output[$sidebar_key]))
					{
						$arr_output[$sidebar_key] = array();
					}

					if(is_array($arr_sidebars))
					{
						foreach($arr_sidebars as $widget_handle)
						{
							$arr_widget_parts = explode('-', $widget_handle);

							$count_temp = (count($arr_widget_parts) - 1);

							$widget_type = "";

							for($i = 0; $i < $count_temp; $i++)
							{
								$widget_type .= ($i > 0 ? "-" : "").$arr_widget_parts[$i];
							}

							$widget_id = $arr_widget_parts[$count_temp];

							if(isset($arr_widget_logic[$widget_handle]) && $arr_widget_logic[$widget_handle] != '')
							{
								$show_on_page = false;

								$arr_page_widget_logic = explode('||', $arr_widget_logic[$widget_handle]);

								foreach($arr_page_widget_logic as $page_widget_logic)
								{
									$page_widget_logic = trim($page_widget_logic);

									$page_id = get_match("/is_page\((.*?)\)/is", $page_widget_logic, false);
									$singular_type = trim(get_match("/is_singular\((.*?)\)/is", $page_widget_logic, false), '\"');
									$tax_type = trim(get_match("/is_tax\((.*?)\)/is", $page_widget_logic, false), '\"');

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

									else if($tax_type != '')
									{
										/*$taxonomy = get_object_taxonomies($post_type);

										$post_tax = var_export($taxonomy, true);

										$terms = get_the_terms($post_id, $taxonomy);

										if(!empty($terms))
										{
											foreach($terms as $term)
											{
												$post_tax .= " || ".var_export($term, true);
											}
										}

										do_log("Checking '".$page_widget_logic."' -> ".$tax_type." -> ".$post_tax);*/

										/*if($tax_type == $post_tax)
										{
											$show_on_page = true;
										}*/
									}

									else
									{
										switch($page_widget_logic)
										{
											case 'is_front_page()':
												$page_on_front = get_option('page_on_front');

												if($post_id == $page_on_front)
												{
													$show_on_page = true;
												}
											break;

											case 'is_home()':
												// Old way...
												/*if($post_id == get_option('page_on_front'))
												{
													$show_on_page = true;
												}*/

												$show_on_front = get_option('show_on_front');
												$page_on_front = get_option('page_on_front');
												$page_for_posts = get_option('page_for_posts');

												if(($show_on_front == 'page' || $page_on_front > 0) && $page_for_posts > 0)
												{
													if($post_id == $page_for_posts)
													{
														$show_on_page = true;
													}
												}

												else
												{
													if($post_id == $page_on_front)
													{
														$show_on_page = true;
													}
												}
											break;

											case 'is_category()':
												if(is_category())
												{
													$show_on_page = true;
												}
											break;

											default:
												$log_message = "Widget Logic Missing 1: '".$page_widget_logic."' (#".$post_id.")";

												if(substr($page_widget_logic, 0, 12) == "is_category(")
												{
													$category_id = (int)str_replace(array("is_category(", ")"), "", $page_widget_logic);

													if($category_id > 0)
													{
														if(is_category($category_id))
														{
															$show_on_page = true;
														}
													}

													else
													{
														do_log("Widget Logic Category Error: '".$page_widget_logic."' -> ".$category_id." (#".$post_id.")");
													}

													do_log($log_message, 'trash');
												}

												else
												{
													do_log($log_message);
												}
											break;
										}
									}
								}

								if($show_on_page == true)
								{
									$widget_name = $wp_registered_widgets[$widget_handle]['name']; //wp_widget_description($widget_handle)

									$arr_output[$sidebar_key][$widget_handle] = $widget_name;
								}
							}
						}
					}

					else
					{
						do_log("Something went wrong with sidebar (".var_export($sidebar, true).", ".var_export($arr_sidebars, true).")");
					}

					/*if(count($arr_output[$sidebar_key]) == 0)
					{
						unset($arr_output[$sidebar_key]);
					}*/
				}
			}

			if(count($arr_output) > 0)
			{
				$out .= "<div class='page_widget_list'>";

					foreach($arr_output as $sidebar_key => $arr_widgets)
					{
						$out .= "<div class='".$sidebar_key."'>
							<h3><a href='".admin_url("widgets.php#".$sidebar_key)."'>".$arr_sidebar_names[$sidebar_key]."</a> <i class='fa fa-plus blue'></i></h3>";

							if(isset($arr_sidebar_names[$sidebar_key]) && is_array($arr_widgets) && count($arr_widgets) > 0)
							{
								$out .= "<ul>";

									foreach($arr_widgets as $widget_key => $widget_name)
									{
										$out .= "<li><a href='".admin_url("widgets.php#".$sidebar_key)."&".$widget_key."'>".$widget_name."</a> <i class='fa fa-wrench blue'></i></li>";
									}

								$out .= "</ul>";
							}

						$out .= "</div>";

						if(isset($arr_sidebar_names[$sidebar_key]))
						{
							// Do nothing
						}

						else if(!in_array($sidebar_key, array('sidebar-primary', 'sidebar-content-intro')))
						{
							do_log("The Widget Area does not exist (".$sidebar_key.", ".var_export($arr_sidebar_names, true).")");
						}
					}

				$out .= "</div>";
			}
		}

		return $out;
	}

	function rwmb_meta_boxes($meta_boxes)
	{
		global $obj_base;

		if(!isset($obj_base))
		{
			$obj_base = new mf_base();
		}

		$meta_boxes[] = array(
			'id' => $this->meta_prefix.'widgets',
			'title' => __("Widgets", 'lang_wls'),
			'post_types' => $obj_base->get_post_types_for_metabox(),
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

	function wp_head()
	{
		$arr_widget_logic_state = get_option_or_default('widget_logic_state', array());
		$arr_widget_logic_screens = get_option_or_default('widget_logic_screens', array());

		if(IS_EDITOR || count($arr_widget_logic_state) > 0 || count($arr_widget_logic_screens) > 0)
		{
			$plugin_include_url = plugin_dir_url(__FILE__);
			$plugin_version = get_plugin_version(__FILE__);

			mf_enqueue_style('style_widget_logic', $plugin_include_url."style.php", $plugin_version);
		}
	}

	function sidebars_widgets($sidebars_widgets)
	{
		global $post;

		$wl_options = get_option_or_default('widget_logic', array());

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
					$log_message .= ($log_message != '' ? ", " : "")."Logic Override (".$pos.")";

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
					/*if($widget_area == 'sidebar-main')
					{
						$post_id = (isset($post->ID) && $post->ID > 0 ? $post->ID : 0);

						if($post_id > 0)
						{
							do_log("sidebars_widgets(".$post_id.", ".var_export(get_the_category(), true).", ".$widget_area."): Eval failed (".$pos.", ".$wl_value.")");
						}
					}*/

					unset($sidebars_widgets[$widget_area][$pos]);
				}
			}
		}

		return $sidebars_widgets;
	}

	function filter_before_widget($html)
	{
		if(IS_EDITOR)
		{
			$arr_sidebars = wp_get_sidebars_widgets();

			foreach($arr_sidebars as $sidebar_key => $arr_sidebar_widgets)
			{
				if($sidebar_key != 'wp_inactive_widgets' && is_array($arr_sidebar_widgets))
				{
					foreach($arr_sidebar_widgets as $widget_key => $widget_class)
					{
						if(strpos($html, $widget_class) !== false)
						{
							$html = str_replace("'widget ", "'widget widget_has_edit ", $html);
							$html = str_replace(" widget ", " widget widget_has_edit ", $html);

							$html .= "<a href='".admin_url("widgets.php#".$sidebar_key)."&".$widget_class."' class='edit_widget'><i class='fa fa-wrench' title='".__("Edit Widget", 'lang_wls')."'></i></a>";

							break 2;
						}
					}
				}
			}
		}

		return $html;
	}
}
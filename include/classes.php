<?php

class mf_widget_logic_select
{
	function __construct()
	{
		$this->meta_prefix = "mf_wls_";
	}

	function admin_init()
	{
		$plugin_include_url = plugin_dir_url(__FILE__);
		$plugin_version = get_plugin_version(__FILE__);

		mf_enqueue_style('style_wls', $plugin_include_url."style_wp.css", $plugin_version);
		mf_enqueue_script('script_wls', $plugin_include_url."script_wp.js", $plugin_version);
	}

	function meta_page_widgets()
	{
		global $wpdb, $wp_registered_widgets;

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
												do_log("Widget Logic Missing: ".$page_widget_logic);
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

							/*else
							{
								$out .= "<li>".__("All Pages", 'lang_wls').": ".$sidebar_key.": ".$widget_type.", ".$widget_id."</li>";
							}*/
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
			//'context' => 'side',
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
}
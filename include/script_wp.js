jQuery(function($)
{
	function update_widget_area_heading(dom_obj)
	{
		var dom_heading = dom_obj.parents(".widgets-sortables").find(".sidebar-name h2"),
			dom_info = dom_heading.children("em");

		if(dom_info.length > 0)
		{
			var widget_amount = parseInt(dom_info.children("span").text()) + 1;

			dom_info.children("span").text(widget_amount);
		}

		else
		{
			var widget_amount = 1;

			dom_heading.append("<em>(<span>" + widget_amount + "</span>)</em>");
		}
	}

	function update_widget_heading(dom_obj, update)
	{
		var i = 0,
			widget_class = "widget",
			widget_title = "";

		dom_obj.find(".widget_logic_state select").find("option:selected").each(function()
		{
			if($(this).val() != '' && i < 3)
			{
				widget_title += (widget_title != '' ? ", " : "") + $(this).text().trim();

				i++;
			}
		});

		if(dom_obj.find(".widget_logic_screens select").find("option").length > dom_obj.find(".widget_logic_screens select").find("option:selected").length)
		{
			dom_obj.find(".widget_logic_screens select").find("option:selected").each(function()
			{
				if(i < 3)
				{
					widget_title += (widget_title != '' ? ", " : "") + $(this).text().trim();

					i++;
				}
			});
		}

		dom_obj.find(".widget_logic_page select").find("option:selected").each(function()
		{
			if(i < 3)
			{
				widget_title += (widget_title != '' ? ", " : "") + $(this).text().trim();

				i++;
			}

			widget_class += " page_" + $(this).attr('value');
		});

		if(widget_title != '' || update == true)
		{
			if(i == 3)
			{
				widget_title += "..."; /* Do not use &hellip; here */
			}

			var dom_widget = dom_obj.parents(".widget"),
				dom_heading = dom_widget.find(".widget-title h3"),
				dom_info = dom_heading.children("em");

			dom_widget.attr({'class': widget_class});

			if(widget_title == '')
			{
				if(dom_info.length > 0)
				{
					dom_info.remove();
				}
			}

			else
			{
				if(dom_info.length > 0)
				{
					dom_info.text("(" + widget_title + ")");
				}

				else
				{
					dom_heading.append("<em>(" + widget_title + ")</em>");
				}
			}
		}
	}

	var page_hash = location.hash;

	if(page_hash != '')
	{
		var arr_hash = page_hash.split('&'),
			dom_obj = $(arr_hash[0]);

		if(dom_obj.length > 0)
		{
			var dom_wraps = $("#widgets-right .widgets-holder-wrap"),
				dom_scroll_to = dom_obj;

			dom_wraps.addClass('closed');
			dom_obj.parent(".widgets-holder-wrap").removeClass('closed');

			dom_wraps.find(".widget").each(function()
			{
				var widget_id = $(this).attr('id'),
					arr_widget_id = widget_id.split('_');

				if(arr_widget_id[1] == arr_hash[1])
				{
					dom_scroll_to = dom_obj.children("#" + widget_id);

					dom_scroll_to.addClass('open').children(".widget-inside").show();
				}
			});

			if(dom_scroll_to.length > 0)
			{
				jQuery("html, body").animate(
				{
					scrollTop: (dom_scroll_to.offset().top - 40)
				}, 800);
			}
		}
	}

	var select_count = 0;

	$(".widget_logic_select").each(function()
	{
		var dom_container = $(this),
			dom_obj_page = dom_container.find(".widget_logic_page select");

		if(select_count == 0)
		{
			$("#widgets-right").prepend("<select><option value=''>-- " + script_wls.choose_here_text + " --</option>" + dom_obj_page.html() + "</select>");
		}

		select_count++;

		update_widget_area_heading(dom_obj_page);

		update_widget_heading(dom_container, false);
	});

	$(document).on('change', "#widgets-right > select", function()
	{
		var dom_widgets_obj = $("#widgets-right").find(".widget"),
			dom_value = $(this).val();

		if(dom_value != '')
		{
			dom_widgets_obj.addClass('filter_hide').each(function()
			{
				if($(this).hasClass("page_" + dom_value))
				{
					$(this).removeClass('filter_hide');
				}
			});
		}

		else
		{
			dom_widgets_obj.removeClass('filter_hide');
		}
	});

	$(document).on('click', "#widgets-right .widgets-holder-wrap .widget:not(.open)", function()
	{
		var widget_id = $(this).attr('id'),
			arr_widget_id = widget_id.split("_"),
			holder_id = $(this).parent(".widgets-sortables").attr('id');

		location.hash = holder_id + "&" + arr_widget_id[1];
	});

	$(document).on('change', ".widgets-sortables .widget_logic_select", function() /* .form_select.widget_logic_page > select*/
	{
		var dom_container = $(this),
			dom_obj_page = dom_container.find(".widget_logic_page select"),
			select_array = dom_obj_page.val(),
			select_string = '';

		if(select_array)
		{
			for(var i = 0; i < select_array.length; i++)
			{
				select_string += (select_string != '' ? ' || ' : '') + select_array[i];
			}
		}

		dom_obj_page.parent(".form_select").siblings(".form_textfield").children("input.widget_logic").val(select_string).removeClass('hide');

		update_widget_heading(dom_container, true);
	});

	$(document).on('change blur', ".widget-content input, .widget-content select", function()
	{
		$(this).parents(".widget-content").siblings(".widget-control-actions").find("input[type='submit']").removeClass('is_disabled').removeAttr('disabled');
	});
});
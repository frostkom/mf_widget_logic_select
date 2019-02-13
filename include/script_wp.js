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

	function update_widget_heading(dom_obj)
	{
		var i = 0,
			out = '';

		dom_obj.find("option:selected").each(function()
		{
			if(i < 3)
			{
				out += (out != '' ? ", " : "") + $(this).text().trim();
			}

			else if(i == 3)
			{
				out += "&hellip;";
			}

			i++;
		});

		if(out != '')
		{
			var dom_heading = dom_obj.parents(".widget").find(".widget-title h3"),
				dom_info = dom_heading.children("em");

			if(dom_info.length > 0)
			{
				dom_info.text("(" + out + ")");
			}

			else
			{
				dom_heading.append("<em>(" + out + ")</em>");
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

			jQuery("html, body").animate(
			{
				scrollTop: (dom_scroll_to.offset().top - 40)
			}, 800);
		}
	}

	$(".widget_logic_select select").each(function()
	{
		update_widget_area_heading($(this));
		update_widget_heading($(this));
	});

	/* This will just override the more specific click below */
	/*$("#widgets-right .widgets-holder-wrap.closed .widgets-sortables").on('click', function()
	{
		var holder_id = $(this).attr('id');

		location.hash = holder_id + "&";
	});*/

	$("#widgets-right .widgets-holder-wrap .widget:not(.open)").on('click', function()
	{
		var widget_id = $(this).attr('id'),
			arr_widget_id = widget_id.split("_"),
			holder_id = $(this).parent(".widgets-sortables").attr('id');

		location.hash = holder_id + "&" + arr_widget_id[1];
	});

	$(".widgets-sortables").on('change', ".widget_logic_select .form_select > select", function()
	{
		var select_array = $(this).val(),
			select_string = '';

		if(select_array)
		{
			for(var i = 0; i < select_array.length; i++)
			{
				select_string += (select_string != '' ? ' || ' : '') + select_array[i];
			}
		}

		$(this).parent(".form_select").siblings(".form_textfield").children("input").val(select_string).removeClass('hide');

		update_widget_heading($(this));
	});
});
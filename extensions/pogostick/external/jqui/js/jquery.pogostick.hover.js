/*
* psYiiExtensions jquery.pogostick.hover.js
* Hijacked from Filament Group Labs (http://www.filamentgroup.com/lab/styling_buttons_and_toolbars_with_the_jquery_ui_css_framework/)!
* Thanks Guys!
*/
//	All hover and click logic for buttons
$(".ps-button:not(.ui-state-disabled)")
.hover(
	function()
	{ 
		$(this).addClass("ui-state-hover"); 
	},
	function()
	{ 
		$(this).removeClass("ui-state-hover"); 
	}
)
.mousedown(
	function()
	{
		$(this).parents('.ps-buttonset-single:first').find(".ps-button.ui-state-active").removeClass("ui-state-active");
		if ($(this).is('.ui-state-active.ps-button-toggleable, .ps-buttonset-multi .ui-state-active') )
		{ 
			$(this).removeClass("ui-state-active"); 
		}
		else 
		{ 
			$(this).addClass("ui-state-active"); 
		}	
	}
)
.mouseup(
	function()
	{
		if (!$(this).is('.ps-button-toggleable, .ps-buttonset-single .ps-button,  .ps-buttonset-multi .ps-button') )
		{
			$(this).removeClass("ui-state-active");
		}
	}
);
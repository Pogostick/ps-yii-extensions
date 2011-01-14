/*
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 * 
 * Hijacked from Filament Group Labs (http://www.filamentgroup.com/lab/styling_buttons_and_toolbars_with_the_jquery_ui_css_framework/)!
 * Thanks Guys!
 */

/**
 * Hover functionality for jQuery UI buttons
 * 
 * @package 	psYiiExtensions.external.jqui
 * @subpackage 	js
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id: CPSController.php 319 2009-12-23 06:23:25Z jerryablan@gmail.com $
 * @since 		v1.0.3
 * 
 * @filesource
 */

//	All hover and click logic for buttons
var psBindAllButtonLogicEvents_ = function()
{
	$(".ps-button:not(.ui-state-disabled)")
	.hover( function() {  $(this).addClass("ui-state-hover");  }, function() {  $(this).removeClass("ui-state-hover"); } )
	.mousedown(
		function()
		{
			$(this).parents('.ps-buttonset-single:first').find(".ps-button.ui-state-active").removeClass("ui-state-active");
			if ($(this).is('.ui-state-active.ps-button-toggleable, .ps-buttonset-multi .ui-state-active') )
				$(this).removeClass("ui-state-active"); 
			else 
				$(this).addClass("ui-state-active"); 
		}
	)
	.mouseup(
		function()
		{
			if (!$(this).is('.ps-button-toggleable, .ps-buttonset-single .ps-button,  .ps-buttonset-multi .ps-button') )
				$(this).removeClass("ui-state-active");
		}
	);
}

//	Call it now...
psBindAllButtonLogicEvents_();
<?php
/**
 * CPSEnhanceJS.php
 *
 * Copyright (c) 2010 Jerry Ablan <jablan@pogostick.com>.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 *
 * This file is part of Pogostick : Yii Extensions.
 *
 * We share the same open source ideals as does the jQuery team, and
 * we love them so much we like to quote their license statement:
 *
 * You may use our open source libraries under the terms of either the MIT
 * License or the Gnu General Public License (GPL) Version 2.
 *
 * The MIT License is recommended for most projects. It is simple and easy to
 * understand, and it places almost no restrictions on what you can do with
 * our code.
 *
 * If the GPL suits your project better, you are also free to use our code
 * under that license.
 *
 * You don’t have to do anything special to choose one license or the other,
 * and you don’t have to notify anyone which license you are using.
 */

//	Include Files
//	Constants
//	Global Settings

/**
 * Provides access to the EnhanceJS framework from The Filament Group
 *
 * @package 	psYiiExtensions
 * @subpackage 	widgets
 *
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN $Id: CPSEnhanceJS.php 388 2010-06-13 16:26:43Z jerryablan@gmail.com $
 * @since 		v1.0.0
 *
 * @filesource
 */
class CPSEnhanceJS extends CPSjQueryWidget
{
	//********************************************************************************
	//* Constants
	//********************************************************************************

	/**
	* The name of this widget
	*/
	const PS_WIDGET_NAME = 'EnhanceJS';

	/**
	* The path where the assets for this widget are stored (underneath the psYiiExtensions/external base
	* Currently, a CDN is in use and no local files are required...
	*/
	const PS_EXTERNAL_PATH = '/EnhanceJS';

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	* Registers the needed CSS and JavaScript.
	*
	* @param mixed $oWidget The widget making this call, if any
	* @param string $sTheme The theme to use. If it's specified, will override theme set at the time of creation
	*/
	public static function loadScripts( $oWidget = null, $sTheme = null )
	{
		static $_bLoaded = false;

		if ( ! $_bLoaded )
		{
			//	Instantiate if needed...
			$_oWidget = ( null == $oWidget ) ? new CPSEnhanceJS() : $oWidget;

			//	Save then Set baseUrl...
			$_sOldPath = $_oWidget->baseUrl;
			$_oWidget->baseUrl = $_oWidget->extLibUrl . self::PS_EXTERNAL_PATH;

			//	jQuery first...
			if ( false !== ( PS::o( PS::_cs()->scriptMap, 'jquery.js' ) ) )
				PS::_cs()->registerCoreScript( 'jquery' );

			//	Register scripts necessary
			$_oWidget->pushScriptFile( $_oWidget->baseUrl . '/enhance.js' );
			PS::_rs( 'ps.enhancejs.init', 'enhance({loadScripts:"tree/js/jQuery.tree.js"});', CClientScript::POS_HEAD );

			//	Restore path
			$_oWidget->baseUrl = $_sOldPath;

			//	And mark completed...
			$_bLoaded = true;
		}
	}
}
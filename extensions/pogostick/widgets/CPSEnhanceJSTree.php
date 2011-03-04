<?php
/**
 * CPSEnhanceJSTree.php
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
 * Provides access to the EnhanceJS Tree from The Filament Group
 *
 * @package 	psYiiExtensions
 * @subpackage 	widgets
 *
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN $Id: CPSEnhanceJSTree.php 388 2010-06-13 16:26:43Z jerryablan@gmail.com $
 * @since 		v1.0.0
 *
 * @filesource
 */
class CPSEnhanceJSTree extends CPSEnhanceJS
{
	//********************************************************************************
	//* Constants
	//********************************************************************************

	/**
	* The name of this widget
	*/
	const PS_WIDGET_NAME = 'EnhanceJS.tree';

	/**
	* The path where the assets for this widget are stored (underneath the psYiiExtensions/external base
	* Currently, a CDN is in use and no local files are required...
	*/
	const PS_EXTERNAL_PATH = '/EnhanceJS/tree';

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	* Registers the needed CSS and JavaScript.
	* @param boolean If true, system will try to find jquery plugins based on the pattern jquery.<plugin-name[.min].js
	* @return CClientScript The current app's ClientScript object
	*/
	public function registerClientScripts( $bLocateScript = false )
	{
		//	Daddy...
		parent::registerClientScripts( $bLocateScript );

		//	Me
		self::loadScripts( $this );

		//	Don't forget subclasses
		return PS::_cs();
	}

	/**
	* Generate the necessary JS for this widget
	*
	* @param string $sTargetSelector
	* @param array $arOptions
	* @param string $sInsertBeforeOptions
	* @return string
	*/
	protected function generateJavascript( $sTargetSelector = null, $arOptions = null, $sInsertBeforeOptions = null )
	{
		//	Get the options...
		if ( $arOptions ) $this->mergeOptions( $arOptions );
		$_sId = $this->getId();

		//	Make options
		$_oData = $this->getOption( 'data', array(), true );
		$this->unsetOption('name');
		$this->unsetOption('id');
		$_sOptions = $this->makeOptions();
		if ( $_oData ) $this->setOption( 'data', $_oData->getValue() );

		//	Jam something in front of options?
		if ( null != $sInsertBeforeOptions ) $_sOptions = $sInsertBeforeOptions . ( ! empty( $_sOptions ) ? ', ' . $_sOptions : '' );

		$this->script =<<<CODE
jQuery('#{$_sId}').tree({$_sOptions});
CODE;

		return $this->script;
	}

	/**
	* Constructs and returns an EnhanceJS tree widget
	*
	* The options passed in are dynamically added to the options array and will be accessible
	* and modifiable as normal (.i.e. $this->theme, $this->baseUrl, etc.)
	*
	* @param array $arOptions The options for the widget
	* @param string $sClass The class of the calling object if different
	* @return CPSEnhanceJSTree
	*/
	public static function create( $sName = null, array $arOptions = array() )
	{
		return parent::create( PS::nvl( $sName, self::PS_WIDGET_NAME ), array_merge( $arOptions, array( 'class' => __CLASS__ ) ) );
	}

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
			parent::loadScripts();

			//	Instantiate if needed...
			$_oWidget = ( null == $oWidget ) ? new CPSEnhanceJS() : $oWidget;

			//	Save then Set baseUrl...
			$_sOldPath = $_oWidget->baseUrl;
			$_oWidget->baseUrl = $_oWidget->extLibUrl . self::PS_EXTERNAL_PATH;

			$_oWidget->pushScriptFile( $_oWidget->baseUrl . '/js/jQuery.tree.js' );
			$_oWidget->pushCssFile( $_oWidget->baseUrl . '/css/enhanced.css' );

			//	And mark completed...
			$_bLoaded = true;
		}
	}
}
<?php
/**
 * This file is part of the psYiiExtensions package.
 *
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * CPSCKEditorWidget a wrapper to the excellent (@link http://ckeditor.com/ CKEditor)
 *
 * @package 	psYiiExtensions
 * @subpackage 	widgets
 *
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id: CPSCKEditorWidget.php 369 2010-01-22 20:43:49Z jerryablan@gmail.com $
 * @since 		v1.0.4
 *
 * @filesource
 */
class CPSCKEditorWidget extends CPSjQueryWidget
{
	//********************************************************************************
	//* Constants
	//********************************************************************************

	/**
	* The name of this widget
	*/
	const PS_WIDGET_NAME = 'ckeditor';

	/**
	* The path where the assets for this widget are stored (underneath the psYiiExtensions/external base
	*/
	const PS_EXTERNAL_PATH = '/ckeditor';

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/***
	* Initialize the widget
	*
	*/
	public function preinit()
	{
		//	Call daddy
		parent::preinit();

		//	Set the default widgetName
		$this->widgetName = self::PS_WIDGET_NAME;
	}

	/**
	* Registers the needed CSS and JavaScript.
	* @param boolean If true, system will try to find jquery plugins based on the pattern jquery.<plugin-name[.min].js
	* @return CClientScript The current app's ClientScript object
	*/
	public function registerClientScripts( $bLocateScript = false )
	{
		//	Daddy...
		parent::registerClientScripts( $bLocateScript );

		//	Reset the baseUrl for our own scripts
		$this->baseUrl = $this->extLibUrl . self::PS_EXTERNAL_PATH;

		//	Register scripts necessary
		$this->pushScriptFile( "{$this->baseUrl}/ckeditor.js" );

		//	Don't forget subclasses
		return PS::_cs();
	}

	//********************************************************************************
	//* Private methods
	//********************************************************************************

	/**
	* Generates the javascript code for the widget
	*
	* @return string
	*/
	protected function generateJavascript( $sTargetSelector = null, $arOptions = null, $sInsertBeforeOptions = null )
	{
		//	Get the options...
		$_arOptions = ( null != $arOptions ) ? $arOptions : $this->makeOptions();
		$_sId = $this->getTargetSelector( $sTargetSelector );
		$_sTarget = $this->target;

		//	Jam something in front of options?
		if ( null != $sInsertBeforeOptions )
		{
			$_sOptions = $sInsertBeforeOptions;
			if ( ! empty( $_arOptions ) ) $_sOptions .= ", {$_arOptions}";
			$_arOptions = $_sOptions;
		}

		if ( $_arOptions ) $_arOptions = ',' . $_arOptions;

		$this->script =<<<CODE
CKEDITOR.replace('{$_sId}'{$_arOptions});
CODE;

		return $this->script;
	}

	/**
	* Constructs and returns a widget
	*
	* The options passed in are dynamically added to the options array and will be accessible
	* and modifiable as normal (.i.e. $this->theme, $this->baseUrl, etc.)
	*
	* @param string $sName The type of jq widget to create
	* @param array $arOptions The options for the widget
	* @param string $sClass The class of the calling object if different
	* @return CPSjQueryWidget
	*/
	public static function create( $sName = null, array $arOptions = array() )
	{
		return parent::create( self::PS_WIDGET_NAME, array_merge( $arOptions, array( 'class' => __CLASS__ ) ) );
	}

}
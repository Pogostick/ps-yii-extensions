<?php
/**
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * CPSWysiwygWidget a wrapper to the excellent (@link http://code.google.com/p/jwysiwyg/ WYSIWYG jQuery widget)
 * 
 * @package 	psYiiExtensions
 * @subpackage 	widgets
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id: CPSWysiwygWidget.php 369 2010-01-22 20:43:49Z jerryablan@gmail.com $
 * @since 		v1.0.0
 *  
 * @filesource
 */
class CPSWysiwygWidget extends CPSjqUIWrapper
{
	//********************************************************************************
	//* Constants
	//********************************************************************************
	
	/**
	* The name of this widget
	*/
	const PS_WIDGET_NAME = 'wysiwyg';

	/**
	* The path where the assets for this widget are stored (underneath the psYiiExtensions/external base
	*/
	const PS_EXTERNAL_PATH = '/jquery-plugins/wysiwyg';

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
	
		$this->pushScriptFile( "{$this->baseUrl}/jquery.wysiwyg.js" );
		PS::_rcf( "{$this->baseUrl}/jquery.wysiwyg.css" );
		
		return PS::_cs();
	}

	/**
	* Constructs and returns a jQuery widget
	* 
	* The options passed in are dynamically added to the options array and will be accessible 
	* and modifiable as normal (.i.e. $this->theme, $this->baseUrl, etc.)
	* 
	* @param array $arOptions The options for the widget
	* @param string $sClass The class of the calling object if different
	* @return CPSjqGridWidget
	*/
	public static function create( $sName = null, array $arOptions = array() )
	{
		return parent::create( PS::nvl( $sName, self::PS_WIDGET_NAME ), array_merge( $arOptions, array( 'class' => __CLASS__ ) ) );
	}
	
}

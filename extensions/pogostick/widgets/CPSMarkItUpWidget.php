<?php
/**
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * CPSMarkItUpWidget a wrapper to the excellent (@link http://markitup.jaysalvat.com MarkItUp jQuery widget)
 * 
 * @package 	psYiiExtensions
 * @subpackage 	widgets
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id: CPSMarkItUpWidget.php 369 2010-01-22 20:43:49Z jerryablan@gmail.com $
 * @since 		v1.0.0
 *  
 * @filesource
 * 
 * @property $skinToUse The skin to use. Defaults to 'markitup'
 * @property $setToUse The parse set to use. Defaults to 'html'
 * @property $settingsToUse The NAME of the JSON settings array included with the "set". Defaults to 'mySettings'
 * @property $multiUseClass The class name that when applied to a TEXTAREA will automatically make it a markItUp TEXTAREA. 
 */
class CPSMarkItUpWidget extends CPSjqUIWrapper
{
	//********************************************************************************
	//* Constants
	//********************************************************************************
	
	/**
	* The name of this widget
	*/
	const PS_WIDGET_NAME = 'markItUp';

	/**
	* The path where the assets for this widget are stored (underneath the psYiiExtensions/external base
	*/
	const PS_EXTERNAL_PATH = '/jquery-plugins/markitup';

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	* initialize
	*/
	public function preinit()
	{
		//	Phone home
		parent::preinit();
		
		//	Set some defaults in case user lazy (like me)
		$this->widgetName = self::PS_WIDGET_NAME;
		
		//	Add the default options for jqUI stuff
		$this->addOptions( 
			array(
				'skinToUse_' => 'string:markitup',
				'setToUse_' => 'string:html',
				'settingsToUse_' => 'string:mySettings',
				'multiUseClass_' => 'string',
			)
		);
	}
	
	/**
	* Registers the needed CSS and JavaScript.
	* @param boolean If true, system will try to find jquery plugins based on the pattern jquery.<plugin-name[.min].js
	* @return CClientScript The current app's ClientScript object
	*/
	public function registerClientScripts( $bLocateScript = false )
	{
		//	Daddy...
		$this->autoRegister = false;
		
		parent::registerClientScripts( $bLocateScript );
		
		//	Reset the baseUrl
		$this->baseUrl = $this->extLibUrl . self::PS_EXTERNAL_PATH;

		//	Register scripts necessary
		$this->pushScriptFile( "{$this->baseUrl}/jquery.markitup.pack.js" );
		$this->pushScriptFile( "{$this->baseUrl}/sets/{$this->setToUse}/set.js" );
		PS::_rcf( "{$this->baseUrl}/skins/{$this->skinToUse}/style.css" );
		PS::_rcf( "{$this->baseUrl}/sets/{$this->setToUse}/style.css" );
	
		//	Get the javascript for this widget
		$_sScript = $this->generateJavascript( $this->multiUseClass ? $this->multiUseClass : null, $this->settingsToUse );
		$this->registerWidgetScript( $_sScript );
		
		//	Don't forget subclasses
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
	* @return CPSMarkItUpWidget
	*/
	public static function create( $sName = null, array $arOptions = array() )
	{
		return parent::create( PS::nvl( $sName, self::PS_WIDGET_NAME ), array_merge( $arOptions, array( 'class' => __CLASS__ ) ) );
	}
}

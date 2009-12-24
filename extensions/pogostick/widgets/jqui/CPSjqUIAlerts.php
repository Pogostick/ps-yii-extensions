<?php
/*
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * The CPSjqUIAlerts allows the {@link http://abeautifulsite.net/notebook/87 jQuery Alerts} to be used in Yii.
 * 
 * @package 	psYiiExtensions.widgets
 * @subpackage 	jqui
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id$
 * @since 		v1.0.4
 *  
 * @filesource
 */
class CPSjqUIAlerts extends CPSjqUIWrapper
{
	//********************************************************************************
	//* Constants
	//********************************************************************************
	
	/**
	* The name of this widget
	*/
	const PS_WIDGET_NAME = 'jquery-alerts';
	/**
	* The path where the assets for this widget are stored (underneath the psYiiExtensions/external base
	*/
	const PS_EXTERNAL_PATH = '/jquery-plugins/alerts';

	//********************************************************************************
	//* Methods
	//********************************************************************************

	/**
	* Initialize
	*/
	public function init()
	{
		parent::init();
	
		//	Set my name...	
		$this->widgetName = self::PS_WIDGET_NAME;
	}

	/**
	* Registers the needed CSS and JavaScript.
	*/
	public function registerClientScripts()
	{
		//	Daddy...
		parent::registerClientScripts();
		
		//	Reset the baseUrl for our own scripts
		$this->baseUrl = $this->extLibUrl . self::PS_EXTERNAL_PATH;

		//	Register scripts necessary
		CPSHelp::_rsf( "{$this->baseUrl}/jquery.alerts.js" );

		//	Register css files...
		CPSHelp::_rcf( "{$this->baseUrl}/jquery.alerts.css" );
		
		return CPSHelp::_cs();
	}

	/**
	* Generate our script
	* 
	* @param string $sTargetSelector
	* @param array $arOptions
	* @param string $sInsertBeforeOptions
	* @return string
	*/
	protected function generateJavascript( $sTargetSelector = null, $arOptions = null, $sInsertBeforeOptions = null )
	{
		//	Use jQuery UI
		return 'jQuery.alerts.dialogClass = "ui-dialog";';
	}
	
	//********************************************************************************
	//* Private methods
	//********************************************************************************

	/**
	* Constructs and returns a jQuery widget
	* 
	* The options passed in are dynamically added to the options array and will be accessible 
	* and modifiable as normal (.i.e. $this->theme, $this->baseUrl, etc.)
	* 
	* @param string $sName The type of jq widget to create
	* @param array $arOptions The options for the widget
	* @param string $sClass The class of the calling object if different
	* @return CPSjqGridWidget
	*/
	public static function create( $sName, array $arOptions = array(), $sClass = __CLASS__ )
	{
		return parent::create( self::PS_WIDGET_NAME, $arOptions, $sClass );
	}
	
	/**
	* Registers the needed CSS and JavaScript.
	* 
	* One may use this to load the scripts necessary for styling buttons and 
	* whatnot when jqUI widgets are not in use on a page.
	*
	* @param CPSjqUIWrapper $oWidget The widget making this call, if any
	* @param string $sTheme The theme to use. If it's specified, will override theme set at the time of creation
	* @param CClientScript $oCS The clientScript object of the app
	*/
	public static function loadScripts( $oWidget = null, $sTheme = null )
	{
		//	Daddy...
		parent::loadScripts( $oWidget, $sTheme );
		
		//	Instantiate if needed...
		$_oWidget = ( null == $oWidget ) ? new CPSjqUIWrapper() : $oWidget;

		//	Save then Set baseUrl...
		$_sOldPath = $_oWidget->baseUrl;
		$_oWidget->baseUrl = $_oWidget->extLibUrl . self::PS_EXTERNAL_PATH;
		
		//	Register scripts necessary
		CPSHelp::_rsf( "{$_oWidget->baseUrl}/jquery.alerts.js" );

		//	Register css files...
		CPSHelp::_rcf( "{$_oWidget->baseUrl}/jquery.alerts.css" );
		
		//	Restore path
		$_oWidget->baseUrl = $_sOldPath;
	}

}
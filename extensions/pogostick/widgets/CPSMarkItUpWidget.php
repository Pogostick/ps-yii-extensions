<?php
/**
 * CPSMarkItUpWidget class file.
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://ps-yii-extensions.googlecode.com
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @license http://www.pogostick.com/license/
 */

/**
 * CPSMarkItUpWidget a wrapper to the excellent (@link http://markitup.jaysalvat.com MarkItUp jQuery widget)
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @version SVN: $Id$
 * @package psYiiExtensions
 * @subpackage Widgets
 * @since 1.0.0
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
	//* Constructor
	//********************************************************************************

	/**
	* Constructs a CPSjqUIWraqpper
	*
	* @param mixed $oOwner
	* @return CPSjqUIWraqpper
	*/
	function __construct( $oOwner = null )
	{
		//	Phone home
		parent::__construct( $oOwner );
		
		//	Add the default options for jqUI stuff
		$this->addOptions( 
			array(
				'skinToUse_' => array( CPSOptionManager::META_DEFAULTVALUE => 'markitup', CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'string' ) ),
				'setToUse_' => array( CPSOptionManager::META_DEFAULTVALUE => 'html', CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'string' ) ),
				'settingsToUse_' => array( CPSOptionManager::META_DEFAULTVALUE => 'mySettings', CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'string' ) ),
				'multiUseClass_' => array( CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'string' ) ),
			)
		);
	}
	
	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	* Initialize the widget
	* 
	*/
	public function init()
	{
		//	Call daddy
		parent::init();
		
		//	Set some defaults in case user lazy (like me)
		$this->widgetName = self::PS_WIDGET_NAME;
		if ( $this->isEmpty( $this->skinToUse ) ) $this->skinToUse = 'markitup';
		if ( $this->isEmpty( $this->setToUse ) ) $this->setToUse = 'html';
		if ( $this->isEmpty( $this->settingsToUse ) ) $this->settingsToUse = 'mySettings';
	}
	
	/***
	* Runs this widget
	*
	*/
	public function run()
	{
		//	Phone home...
		parent::run();
		
		//	Register the scripts/css
		$this->registerClientScripts();
	}

	/**
	* Registers the needed CSS and JavaScript.
	*
	* @param string $sId
	*/
	public function registerClientScripts()
	{
		//	Daddy...
		$_oCS = Yii::app()->getClientScript();
		
		//	Reset the baseUrl
		$this->baseUrl = $this->extLibUrl . self::PS_EXTERNAL_PATH;

		//	Register scripts necessary
		self::loadScripts( $this, $this->theme );
		$_oCS->registerScriptFile( "{$this->baseUrl}/jquery.markitup.pack.js" );
		$_oCS->registerScriptFile( "{$this->baseUrl}/sets/{$this->setToUse}/set.js" );
		$_oCS->registerCssFile( "{$this->baseUrl}/skins/{$this->skinToUse}/style.css" );
		$_oCS->registerCssFile( "{$this->baseUrl}/sets/{$this->setToUse}/style.css" );
	
		//	Get the javascript for this widget
		$_sScript = $this->generateJavascript( ( ! $this->isEmpty( $this->multiUseClass ) ) ? $this->multiUseClass : null, $this->settingsToUse );
		$_oCS->registerScript( 'ps_' . md5( self::PS_WIDGET_NAME . $this->widgetName . '#' . $this->id . '.' . time() ), $_sScript, CClientScript::POS_READY );
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
	public static function create( array $arOptions = array(), $sClass = __CLASS__ )
	{
		return parent::create( self::PS_WIDGET_NAME, $arOptions, $sClass );
	}
}
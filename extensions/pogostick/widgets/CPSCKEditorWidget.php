<?php
/**
 * CPSCKEditorWidget class file.
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://ps-yii-extensions.googlecode.com
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @license http://www.pogostick.com/license/
 */

/**
 * CPSCKEditorWidget a wrapper to the excellent (@link http://ckeditor.com/ CKEditor)
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @version SVN: $Id$
 * @package psYiiExtensions
 * @subpackage Widgets
 * @since 1.0.4
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
	public function init()
	{
		//	Call daddy
		parent::init();
		
		//	Set the default widgetName
		$this->widgetName = self::PS_WIDGET_NAME;
	}		
	
	/***
	* Runs this widget
	*
	*/
	public function run()
	{
		//	Phone home
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
		
		//	Reset the baseUrl for our own scripts
		$this->baseUrl = $this->extLibUrl . self::PS_EXTERNAL_PATH;

		//	Register scripts necessary
		$_oCS->registerScriptFile( "{$this->baseUrl}/ckeditor.js" );
	
		//	Get the javascript for this widget
		$_oCS->registerScript( 'ps' . self::PS_WIDGET_NAME . '.' . $this->widgetName . '#' . $this->id, $this->generateJavascript(), CClientScript::POS_READY );
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
		
		//	Jam something in front of options?
		if ( null != $sInsertBeforeOptions )
		{
			$_sOptions = $sInsertBeforeOptions;
			if ( ! empty( $_arOptions ) ) $_sOptions .= ", {$_arOptions}";
			$_arOptions = $_sOptions;
		}

		$this->script =<<<CODE
CKEDITOR.replace('{$_sId}');
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
	public static function create( array $arOptions = array(), $sClass = __CLASS__ )                  
	{
		return parent::create( self::PS_WIDGET_NAME, $arOptions, $sClass );
	}
}
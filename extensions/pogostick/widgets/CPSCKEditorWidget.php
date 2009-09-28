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
class CPSCKEditorWidget extends CPSWidget
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
		$_sId = $sTargetSelector;
		
		//	Jam something in front of options?
		if ( null != $sInsertBeforeOptions )
		{
			$_sOptions = $sInsertBeforeOptions;
			if ( ! empty( $_arOptions ) ) $_sOptions .= ", {$_arOptions}";
			$_arOptions = $_sOptions;
		}

		$this->script =<<<CODE
$('{$_sId}').{$this->widgetName}({$_arOptions});
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
	public static function create( $sName, array $arOptions = array(), $sClass = __CLASS__ )                  
	{
		//	Instantiate...
		$_oWidget = new $sClass();

		//	Set default options...
		$_oWidget->widgetName = $sName;
		$_oWidget->target = CPSHelp::getOption( $arOptions, 'target', null, true );
		$_oWidget->id = $_oWidget->name = CPSHelp::getOption( $arOptions, 'id', $sName );
		$_oWidget->name = CPSHelp::getOption( $arOptions, 'name', $_oWidget->id );

		return $_oWidget->finalizeCreate( $arOptions );
	}

	/**
	* Finalize the creation of a widget
	* 
	* This allows subclasses to initialize their class then finalize the creation here.
	* 	
	* @param CPSjQueryWidget $oWidget The widget to finalize
	* @param array $arOptions Options for this widget
	* @returns CPSjQueryWidget
	*/
	protected function finalizeCreate( $arOptions = array() )
	{
		//	Set variable options...
		if ( is_array( $arOptions ) )
		{
			//	Check for scripts...
			if ( isset( $arOptions[ '_scripts' ] ) && is_array( $arOptions[ '_scripts' ] ) )
			{
				//	Add them and remove from options...
				$this->addScripts( $arOptions[ '_scripts' ] );
				unset( $arOptions[ '_scripts' ] );
			}

			//	Now process the rest of the options...			
			foreach ( $arOptions as $_sKey => $_oValue )
				$this->addOption( $_sKey, null, false, $_oValue );
		}
		
		//	Initialize the widget
		$this->init();

		//	Does user want us to run it?
		if ( $this->autoRun ) $this->run();

		//	And return...
		return $this;
	}

}
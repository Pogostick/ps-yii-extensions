<?php
/**
 * CPSjqAchtung class file.
 *
 * @filesource
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://www.pogostick.com Pogostick, LLC.
 * @package psYiiExtensions
 * @subpackage Widgets
 * @since v1.0.5
 * @version SVN: $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified  $Date$
 */
/**
 * CPSjqAchtung provides
 * Widget that implements jQuery plug-in {@link http://code.google.com/p/achtung-ui/ Achtung}
 */
class CPSjqAchtung extends CPSjQueryWidget
{
	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	/**
	* The name of this widget
	*/
	const PS_WIDGET_NAME = 'achtung';
	/**
	* The path where the assets for this widget are stored (underneath the psYiiExtensions/external base
	* Currently, a CDN is in use and no local files are required...
	*/
	const PS_EXTERNAL_PATH = '/jquery-plugins/achtung';

	//********************************************************************************
	//* Property Access Methods
	//********************************************************************************

	//********************************************************************************
	//* Magic Method Ovverides
	//********************************************************************************

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**                                                                                                                     
	* Registers the needed CSS and JavaScript.
	*/
	public function registerClientScripts()
	{
		//	Daddy...
		$_oCS = parent::registerClientScripts();
		
		//	Reset the baseUrl for our own scripts
		$this->baseUrl = $this->extLibUrl . self::PS_EXTERNAL_PATH;
		
		//	Register css
		$_oCS->registerCssFile( $this->baseUrl . DIRECTORY_SEPARATOR . "ui.achtung-min.css", CClientScript::POS_HEAD );
		
		//	Register scripts necessary
		$_oCS->registerScriptFile( $this->baseUrl . DIRECTORY_SEPARATOR . "ui.achtung-min.js", CClientScript::POS_HEAD );

		//	Get the javascript for this widget
		$_oCS->registerScript( 'ps_' . md5( self::PS_WIDGET_NAME . $this->widgetName . '#' . $this->id . '.' . $this->target . '.' . time() ), $this->generateJavascript(), CClientScript::POS_READY );

		//	Don't forget subclasses
		return $_oCS;
	}

	/**
	* Constructs and returns a jQuery Tools widget
	* 
	* The options passed in are dynamically added to the options array and will be accessible 
	* and modifiable as normal (.i.e. $this->theme, $this->baseUrl, etc.)
	* 
	* @param array $arOptions The options for the widget
	* @param string $sClass The class of the calling object if different
	* @return CPSjqMaskedInputWrapper
	*/
	public static function create( array $arOptions = array(), $sClass = __CLASS__ )
	{
		return parent::create( self::PS_WIDGET_NAME, $arOptions, $sClass );
	}

	//********************************************************************************
	//* Private Methods
	//********************************************************************************
	
	/**
	* Generates the javascript code for the widget
	*
	* @return string
	*/
	protected function generateJavascript( $sTargetSelector = null, $arOptions = null, $sInsertBeforeOptions = null )
	{
		$_sOptions = CPSHelp::getOption( $this->getPublicOptions(), self::PS_WIDGET_NAME, '' );
		$_sId = $this->getTargetSelector( $sTargetSelector );
		
		$this->script =<<<CODE
$('{$_sId}').{$this->widgetName}("{$_sOptions}");
CODE;

		return $this->script;
	}
	
}
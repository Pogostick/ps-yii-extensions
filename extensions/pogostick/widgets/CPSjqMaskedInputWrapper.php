<?php
/**
* CPSjqMaskedInputWrapper class file.
*
* @author Jerry Ablan <jablan@pogostick.com>
* @link http://ps-yii-extensions.googlecode.com
* @copyright Copyright &copy; 2009 Pogostick, LLC
* @license http://www.gnu.org/licenses/gpl.html
*/

/**
* The CPSjqMaskedInputWrapper allows the {@link http://digitalbush.com/projects/masked-input-plugin/ jQuery Masked Input Plugin} to be used in Yii.
*
* @author Jerry Ablan <jablan@pogostick.com>
* @version SVN: $Id$
* @filesource
* @package psYiiExtensions
* @subpackage Widgets
* @since psYiiExtensions v1.0.4
*/
class CPSjqMaskedInputWrapper extends CPSjQueryWidget
{
	//********************************************************************************
	//* Constants
	//********************************************************************************
	
	/**
	* The name of this widget
	*/
	const PS_WIDGET_NAME = 'mask';
	/**
	* The path where the assets for this widget are stored (underneath the psYiiExtensions/external base
	* Currently, a CDN is in use and no local files are required...
	*/
	const PS_EXTERNAL_PATH = '/jquery-plugins/maskedinput';

	//********************************************************************************
	//* Methods
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
		
		//	Register scripts necessary
		$_oCS->registerScriptFile( $this->baseUrl . DIRECTORY_SEPARATOR . "jquery.maskedinput-1.2.2.min.js" );

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
		$_sMask = CPSHelp::getOption( $this->getPublicOptions(), 'mask', '' );
		$_sId = $this->getTargetSelector( $sTargetSelector );
		
		$this->script =<<<CODE
$('{$_sId}').{$this->widgetName}("{$_sMask}");
CODE;

		return $this->script;
	}
	
}
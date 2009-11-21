<?php
/**
 * CPSjqValidate class file.
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
 * Widget that implements jQuery plug-in {@link http://bassistance.de/jquery-plugins/jquery-plugin-validation/ Validate}
 */
class CPSjqValidate extends CPSjQueryWidget
{
	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	/**
	* The name of this widget
	*/
	const PS_WIDGET_NAME = 'validate';
	/**
	* The path where the assets for this widget are stored (underneath the psYiiExtensions/external base
	* Currently, a CDN is in use and no local files are required...
	*/
	const PS_EXTERNAL_PATH = '/jquery-plugins/validate';
	const CDN_PATH = 'http://ajax.microsoft.com/ajax/jquery.validate/1.5.5/jquery.validate.min.js';

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
		
		//	Meta data for goodness...
		$_oCS->registerScriptFile( $this->extLibUrl . '/jquery-plugins/jquery.metadata.js', CClientScript::POS_HEAD );
		
		//	Register scripts necessary
		if ( defined( __CLASS__ . '::CDN_PATH' ) )
			$_oCS->registerScriptFile( self::CDN_PATH, CClientScript::POS_HEAD );
		else
			$_oCS->registerScriptFile( $this->baseUrl . '/jquery.validate.min.js', CClientScript::POS_HEAD );
			
		$_oCS->registerScriptFile( $this->baseUrl . '/additional-methods.js', CClientScript::POS_HEAD );

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

		$_sValidate = 'jQuery.validator.addMethod( "phoneUS", function(phone_number, element) { phone_number = phone_number.replace(/\s+/g, ""); return this.optional(element) || phone_number.length > 9 && phone_number.match(/^(1[\s\.-]?)?(\([2-9]\d{2}\)|[2-9]\d{2})[\s\.-]?[2-9]\d{2}[\s\.-]?\d{4}$/);}, "Please specify a valid phone number");';
		$_sValidate .= 'jQuery.validator.addMethod( "postalcode", function(postalcode, element) { return this.optional(element) || postalcode.match(/(^\d{5}(-\d{4})?$)|(^[ABCEGHJKLMNPRSTVXYabceghjklmnpstvxy]{1}\d{1}[A-Za-z]{1} ?\d{1}[A-Za-z]{1}\d{1})$/);}, "Please specify a valid postal/zip code");';
		
		$this->script =<<<CODE
{$_sValidate}
jQuery('{$_sId}').{$this->widgetName}({$_arOptions});
CODE;

		return $this->script;
	}
	
}
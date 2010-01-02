<?php
/*
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * Widget that implements jQuery plug-in {@link http://bassistance.de/jquery-plugins/jquery-plugin-validation/ Validate}
 * 
 * @package 	psYiiExtensions
 * @subpackage 	widgets
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id$
 * @since 		v1.0.5
 *  
 * @filesource
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
	const CDN_ROOT = 'http://ajax.microsoft.com/ajax/jquery.validate/1.6';
	const CDN_PATH = 'http://ajax.microsoft.com/ajax/jquery.validate/1.6/jquery.validate.min.js';

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**                                                                                                                     
	* Registers the needed CSS and JavaScript.
	*/
	public function registerClientScripts()
	{
		//	Daddy...
		parent::registerClientScripts();
		
		//	Reset the baseUrl for our own scripts
		$this->baseUrl = $this->extLibUrl . self::PS_EXTERNAL_PATH;
		
		//	Meta data for goodness...
		PS::_rsf( $this->extLibUrl . '/jquery-plugins/jquery.metadata.js', CClientScript::POS_HEAD );
		
		//	Register scripts necessary
//		PS::_rsf( self::CDN_ROOT . '/jquery.validate.min.js', CClientScript::POS_HEAD );
		PS::_rsf( $this->baseUrl . '/jquery.validate.min.js', CClientScript::POS_HEAD );
			
//		PS::_rsf( self::CDN_ROOT . '/additional-methods.js', CClientScript::POS_HEAD );
		PS::_rsf( $this->baseUrl . '/additional-methods.js', CClientScript::POS_HEAD );

		//	Get the javascript for this widget
		$this->registerWidgetScript();

		//	Don't forget subclasses
		return PS::_cs();
	}

	/**
	* Constructs and returns a jQuery Tools widget
	* 
	* The options passed in are dynamically added to the options array and will be accessible 
	* and modifiable as normal (.i.e. $this->theme, $this->baseUrl, etc.)
	* 
	* @param string $sName The widget name
	* @param array $arOptions The options for the widget
	* @param string $sClass The class of the calling object if different
	* @return CPSjqMaskedInputWrapper
	*/
	public static function create( $sName = null, array $arOptions = array() )
	{
		return parent::create( PS::nvl( $sName, self::PS_WIDGET_NAME ), array_merge( $arOptions, array( 'class' => __CLASS__ ) ) );
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

		$_sValidate = '$.validator.addMethod( "phoneUS", function(phone_number, element) { phone_number = phone_number.replace(/\s+/g, ""); return this.optional(element) || phone_number.length > 9 && phone_number.match(/^(1[\s\.-]?)?(\([2-9]\d{2}\)|[2-9]\d{2})[\s\.-]?[2-9]\d{2}[\s\.-]?\d{4}$/);}, "Please specify a valid phone number");';
		$_sValidate .= '$.validator.addMethod( "postalcode", function(postalcode, element) { return this.optional(element) || postalcode.match(/(^\d{5}(-\d{4})?$)|(^[ABCEGHJKLMNPRSTVXYabceghjklmnpstvxy]{1}\d{1}[A-Za-z]{1} ?\d{1}[A-Za-z]{1}\d{1})$/);}, "Please specify a valid postal/zip code");';
		
		//	Put these via registerScript as not to double them up.
		PS::_rs( '#psValidate.validator.addMethod#', $_sValidate );
		
		$this->script =<<<CODE
$('{$_sId}').validate({$_arOptions});
CODE;

		return $this->script;
	}
	
}
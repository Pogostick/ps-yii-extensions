<?php
/*
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * CPSForm provides form helper functions
 * 
 * @package 	psYiiExtensions
 * @subpackage 	helpers
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id$
 * @since 		v1.0.5
 *  
 * @filesource
 * 
 * @property string $codeModel The name of the code model for code lookups
 * @property string $hintTemplate The template for displaying hints
 */
class CPSForm implements IPSBase
{
	//********************************************************************************
	//* Constants
	//********************************************************************************
	
	const SEARCH_PREFIX = '##pss_';
	
	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	protected static $m_sSearchFieldLabelTemplate = '<label class="ps-form-search-label" for="{fieldId}">{title}</label>';
	protected static $m_sSearchFieldTemplate = '{label}<span class="ps-form-search-field ui-widget-container">{field}</span>';
	
	//********************************************************************************
	//* Public Methods
	//********************************************************************************
	
	/**
	* Creates a form from an option array
	* 
	* @param array $arOptions
	* @return string
	* @todo document this function
	*/
	public static function create( $arOptions = array() )
	{
		//	Make sure we have some form fields...
		if ( ! $_arFields = PS::o( $arOptions, 'fields', null, true ) )
			throw new CPSException( 'You must define form fields to use this method.' );

		//	Return as string?
		$_bReturnString = PS::o( $arOptions, 'returnString', false, true );
		$_bShowDates = PS::o( $arOptions, 'showDates', true, true );
		$_bValidate = PS::o( $arOptions, 'validate', false );
		$_bErrorSummary = PS::o( $arOptions, 'errorSummary', true, true );

		//	Our model?
		$_oModel = PS::o( $arOptions, 'formModel', null, true );
		
		//	Let's begin...
		$_sOut = PS::beginFormEx( $arOptions );
		
		//	Error summary wanted?
		if ( $_oModel && $_bErrorSummary )
			$_sOut .= PS::errorSummary( $_oModel );
			
		//	Now create form fields...
		foreach ( $_arFields as $_arValue )
		{
			$_bPassed = true;
			
			//	First element must be type...
			$_sType = array_shift( $_arValue );
			
			//	Handle a runtime conditional column display
			if ( $_sCondition = PS::o( $_arValue, 'condition', null, true ) )
				$_bPassed = eval( 'return(' . $_sCondition . ');' );
			
			if ( $_bPassed )
			{
				switch ( $_sType )
				{
					case 'hidden':
						$_sOut .= call_user_func_array( array( 'PS', 'hiddenField' ), $_arValue );
						break;
						
					case 'submit':
						$_sOut .= call_user_func_array( array( 'PS', 'submitButtonBar' ), $_arValue );
						break;
						
					default:
						//	Push model into the front of the array...
						array_unshift( $_arValue, $_sType, $_oModel );
						
						//	Make the field
						$_sOut .= call_user_func_array( array( 'PS', 'field' ), $_arValue );
						
						//	CKEditor needs special handing for validate...
						if ( $_bValidate && $_sType == PS::CKEDITOR )
						{
							$_sFieldId = PS::getLastFieldId();
							$_sFormId = PS::getCurrentFormId();
							$_sScript = "jQuery('#{$_sFormId}').submit(function(e){ jQuery('#{$_sFieldId}').val(CKEDITOR.instances.{$_sFieldId}.getData()); return true; });";
							PS::_rs( '#psForm.ckeditor.' . $_sFieldId . '.get_data', $_sScript, CClientScript::POS_READY );
						}
						break;
				}
			}
		}
		
		//	Does user want dates? Show 'em
		if ( $_bShowDates && $_oModel instanceof CPSModel ) $_sOut .= $_oModel->showDates();

		//	Ok, done building form...
		$_sOut .= PS::endForm();
		
		//	Does user want data returned?
		if ( $_bReturnString ) return $_sOut;
		
		//	Guess not, just spit it out...
		echo $_sOut;
	}

	/**
	* Makes a nice form header
	* 
	* @param mixed $sTitle
	* @param mixed $arOptions
	*/
	public static function formHeaderEx( $sTitle, $arOptions = array() )
	{
		$_arMenuItems = PS::o( $arOptions, 'menuItems', array() );
		$_sDivClass = PS::o( $arOptions, 'divClass', 'ps-form-header' );
		$_bShowFlashDiv = PS::o( $arOptions, 'showFlashDiv', true );
		$_sHtmlInject = PS::o( $arOptions, 'htmlInject', null );
		$_sSubHeader = PS::o( $arOptions, 'subHeader', null );
		
		//	Do the header
		return self::formHeader( $sTitle, $_arMenuItems, $_sDivClass, $_bShowFlashDiv, $_sHtmlInject ) . $_sSubHeader;
	}
	
	/**
	* Creates a standard form header
	* 
	* Pass in menu item array as follows:
	* 
	* array( 'id' => array( 'label', 'url', 'icon' ), ... )
	* 
	* Each item is made into a jQuery UI button with an optional jQUI icon. 
	* 
	* Example:
	* 
	* 	echo CPSForm::formHeader( 'Site Manager', 
	*		array( 'new' => 
	*			array(
	*				'label' => 'New Site',
	*				'url' => array( 'create' ),
	* 				'formId' => 'id for form' // optional
	*				'icon' => 'circle-plus',
	*			) 	
	*		)
	* 	);
	* 
	* @param string $sTitle
	* @param array $arMenuItems
	* @param string $sDivClass
	* @param boolean $bShowFlashDiv If true, will output a standard ps-flash-display div
	* @returns string
	* 
	* @deprecated Use formHeaderEx
	*/
	public static function formHeader( $sTitle, $arMenuItems = array(), $sDivClass = 'ps-form-header', $bShowFlashDiv = true, $sHtmlInject = null )
	{
		$_bIcon = false;
		$_sClass = $_sLink = $_sOut = null;
		$_sFlash = $bShowFlashDiv ? PS::flashMessage( 'success', true ) : null;
		$_sExtra = null;//'style="margin-bottom:' . ( $_sFlash ? '32px' : '10px' ) . '";"';
		
		//	Create menu
		foreach ( $arMenuItems as $_sId => $_arItem ) 
		{
			$_sOnClick = null;
			$_sAccess = PS::o( $_arItem, 'access', null, true );
			
			//	Can user have this item?
			if ( $_sAccess && $_sAccess != Yii::app()->user->accessRole )
				continue;
			
			$_sLabel = PS::o( $_arItem, 'label', $sTitle, true );
			$_sLink = PS::normalizeUrl( PS::o( $_arItem, 'url', array('#'), true ) );
			$_sOut .= PS::jquiButton( $_sLabel, $_sLink, $_arItem );
		}
		
		return <<<HTML
		<div class="{$sDivClass}" {$_sExtra}>
			<h1 class="ps-form-header-left">{$sTitle}</h1>{$_sFlash}
			<p style="clear:both;">{$_sOut}</p>
			<div style="clear:both"></div>{$sHtmlInject}
		</div>
HTML;
	}
	
	/**
	* Output a generic search bar...
	* 
	* @param mixed $arOptions
	*/
	public static function searchBar( $arOptions = array() )
	{
		$_arFields = PS::o( $arOptions, 'fields', array(), true );
		$_sDivClass = PS::o( $arOptions, 'class', 'ps-search-bar', true );
		
		foreach ( $_arFields as $_sName => $_arField )
		{
			$_sTitle = PS::o( $_arField, 'title', 'Search', true );
			$_eType = PS::o( $_arField, 'type', 'text', true );
			$_arTypeOptions = PS::o( $_arField, 'typeOptions', array(), true );
			$_arData = PS::o( $_arField, 'data', array(), true );

			//	Setup some css...
			$_sClass = PS::o( $_arTypeOptions, 'class', null, true );
			$_arTypeOptions['class'] = trim( $_sClass );
			
			$_arTypeOptions['id'] = PS::o( $_arTypeOptions, 'id', PS::getWidgetId( self::SEARCH_PREFIX ) . '_' . $_eType );
			if ( ! is_numeric( $_eType ) ) $_arTypeOptions['size'] = PS::o( $_arTypeOptions, 'size', '15' );

			if ( $_sTitle ) $_sTitle .= ':';
			
			$_sField = PS::activefield( $_eType, null, $_sName, $_arTypeOptions, array(), $_arData );
			$_sLabel = strtr( self::$m_sSearchFieldLabelTemplate, array( '{fieldId}' => $_arTypeOptions['id'], '{title}' => $_sTitle ) );
			$_sOut .= strtr( self::$m_sSearchFieldTemplate, array( '{label}' => $_sLabel, '{field}' => $_sField ) );
		}
 
		return <<<HTML
		<div class="{$_sDivClass}">{$_sOut}</div>
HTML;
	}

}
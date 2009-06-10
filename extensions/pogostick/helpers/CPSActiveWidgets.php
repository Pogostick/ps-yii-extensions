<?php
/**
 * CPSActiveWidgets class file.
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://ps-yii-extensions.googlecode.com
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @license http://www.pogostick.com/license/
 */

/**
 * CPS provides
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @version SVN: $Id$
 * @package psYiiExtensions
 * @subpackage Widgets
 * @since 1.0.0
 */
class CPSActiveWidgets extends CHtml
{
	//********************************************************************************
	//* Constants
	//********************************************************************************
	
	/**
	* Field Types
	*/
	const TEXTAREA = 'activeTextArea';
	const TEXT = 'activeTextField';
	const HIDDEN = 'activeHiddenField';
	const PASSWORD = 'activePasswordField';
	const FILE = 'activeFileField';
	const RADIO = 'activeRadioButton';
	const CHECK = 'activeCheckBox';
	const DROPDOWN = 'activeDropDownList';
	const LISTBOX = 'activeListBox';
	const CHECKLIST = 'activeCheckBoxList';
	const RADIOLIST = 'activeRadioButtonList';
	const WYSIWYG = 'wysiwyg';
	const MARKITUP = 'markItUp';
	const CODEDD = 'activeCodeDropDownList';
	const JQUI = 'CPSjqUIWrapper';

	//********************************************************************************
	//* Public methods
	//********************************************************************************
	
	/**
	* Adds a simple div block with label and field
	* 
	* If $sLabel is null, $sColName is used as your label name
	* 	
	* @param string $eFieldType One of the * constants
	* @param CModel $oModel The model for this form
	* @param string $sColName The column/attribute name
	* @param array $arOptions The htmlOptions for the field
	* @param string $sLabel The real name of the attribute if different
	* @param array $arLabelOptions The htmlOptions for the label
	* @param array $arData Any data necessary for the field (i.e. drop down data)
	* @returns string
	*/
	public static function simpleActiveBlock( $eFieldType, $oModel, $sColName, $arOptions = array(), $sLabel = null, $arLabelOptions = array(), $arData = null, $arWidgetOptions = array() )
	{
		$_sHtml = '';
		
		if ( isset( $arOptions[ '_appendHtml' ] ) )
		{
			$_sHtml = $arOptions[ '_appendHtml' ];
			unset( $arOptions[ '_appendHtml' ] );
		}
		
		$_sOut = '<div class="simple">';
		$_sOut .= self::activeLabelEx( $oModel, ( null == $sLabel ) ? $sColName : $sLabel, $arLabelOptions );
		$_sOut .= self::activeField( $eFieldType, $oModel, $sColName, $arOptions, $arWidgetOptions, $arData );
		$_sOut .= $_sHtml;
		$_sOut .= '</div>';

		return $_sOut;
	}

	/**
	* Adds an activefield to a form
	* 
	* @param string $eFieldType One of the * constants
	* @param CModel $oModel The model for this form
	* @param string $sColName The column/attribute name
	* @param array $arHtmlOptions The htmlOptions for the field
	* @param array $arWidgetOptions The widget options for the field
	* @param array $arData Any data necessary for the field (i.e. drop down data)
	* @returns string
	*/
	public static function activeField( $eFieldType, $oModel, $sColName, $arHtmlOptions = array(), $arWidgetOptions = array(), $arData = null )
	{
		//	Stuff to put after widget
		$_sAppendHtml = '';
		
		if ( isset( $arHtmlOptions[ '_appendHtml' ] ) )
		{
			$_sAppendHtml = $arHtmlOptions[ '_appendHtml' ];
			unset( $arHtmlOptions[ '_appendHtml' ] );
		}
		
		//	Auto set id and name if they aren't already...
		if ( ! isset( $arHtmlOptions[ 'name' ] ) ) $arHtmlOptions[ 'name' ] = self::resolveName( $oModel, $sColName );
		if ( ! isset( $arHtmlOptions[ 'id' ] ) ) $arHtmlOptions[ 'id' ] = self::getIdByName( $arHtmlOptions[ 'name' ] );
				
		//	Handle special types...
		switch ( $eFieldType )
		{
			case self::JQUI:
				if ( isset( $arHtmlOptions[ '_widget' ] ) )
				{
					$arWidgetOptions[ 'name' ] = $arHtmlOptions[ 'name' ];
					$arWidgetOptions[ 'id' ] = $arHtmlOptions[ 'id' ];
					$_sWidget = $arHtmlOptions[ '_widget' ];
					unset( $arHtmlOptions[ '_widget' ] );
					CPSjqUIWrapper::create( $_sWidget, $arWidgetOptions );
					$eFieldType = self::TEXT;
				}
				break;
			
			case self::TEXT:
				if ( ! isset( $arHtmlOptions[ 'size' ] ) )
					$arHtmlOptions[ 'size' ] = 60;
				break;
			
			//	WYSIWYG Plug-in
			case self::WYSIWYG:
				CPSWysiwygWidget::create( array_merge( $arWidgetOptions, array( 'autoRun' => true, 'id' => $_sId, 'name' => $_sName ) ) );
				$eFieldType = self::TEXTAREA;
				break;
				
			//	markItUp! Plug-in
			case self::MARKITUP:
				$arWidgetOptions[ 'name' ] = $arHtmlOptions[ 'name' ];
				$arWidgetOptions[ 'id' ] = $arHtmlOptions[ 'id' ];
				CPSMarkItUpWidget::create( $arWidgetOptions );
				$eFieldType = self::TEXTAREA;
				break;

			//	Special code drop down. List data is gotten here...
			case self::CODEDD:
				$eFieldType = self::DROPDOWN;
				$arData = CHtml::listData( Code::findByType( ( $arData == null ) ? $sColName : $arData ), 'code_uid', 'code_desc_text' );
				//	Intentionally fall through to next block...
				
			//	These guys need data in third parameter
			case self::DROPDOWN:
				//	Auto-set prompt
				if ( ! isset( $arHtmlOptions[ 'noprompt' ] ) )
					$arHtmlOptions[ 'prompt' ] = 'Select One...';
			case self::CHECKLIST:
			case self::RADIOLIST:
			case self::LISTBOX:
				return self::$eFieldType( $oModel, $sColName, $arData, $arHtmlOptions );
		}
		
		return self::$eFieldType( $oModel, $sColName, $arHtmlOptions ) . $_sAppendHtml;
	}

	/**
	* Create a drop downlist filled with codes give a code type.
	* 
	* @param string $sAttribute
	* @param string $sCodeType
	* @param array $arHtmlOptions
	* @param integer $iDefaultUID
	* @return string
	*/
	public static function activeCodeDropDownList( $sAttribute, $sCodeType, $arHtmlOptions = array(), $iDefaultUID = 0 )
	{
		$_oModel = Code::model();
		
		CHtml::resolveNameID( $_oModel, $sAttribute, $arHtmlOptions );
		$_sSel = $_oModel->$attribute;
		
		$_sOptions = "\n" . CHtml::listOptions( $iDefaultUID, $_oModel->findAll( "code_type = '$sCodeType'" ), $arHtmlOptions );
		CHtml::clientChange( 'change', $arHtmlOptions );
		
		if ( $_oModel->hasErrors( $sAttribute ) )
			CHtml::addErrorCss( $arHtmlOptions );
			
		if ( isset( $arHtmlOptions[ 'multiple' ] ) )
		{
			if ( substr( $arHtmlOptions[ 'name' ], -2 ) !== '[]' )
				$arHtmlOptions[ 'name' ] .= '[]';
		}
		
		return CHtml::tag( 'select', $arHtmlOptions, $_sOptions );
	}
	
	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	//********************************************************************************
	//* Property Access Methods
	//********************************************************************************

	//********************************************************************************
	//* Magic Method Ovverides
	//********************************************************************************

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	//********************************************************************************
	//* Private Methods
	//********************************************************************************
}
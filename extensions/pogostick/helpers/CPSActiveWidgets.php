<?php
/**
 * CPSActiveWidgets class file.
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://ps-yii-extensions.googlecode.com
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @license http://www.pogostick.com/license/
 * @package psYiiExtensions
 */

/**
 * CPS provides
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @version SVN: $Id$
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
	const PSAWFT_TEXTAREA = 'activeTextArea';
	const PSAWFT_TEXT = 'activeTextField';
	const PSAWFT_HIDDEN = 'activeHiddenField';
	const PSAWFT_PASSWORD = 'activePasswordField';
	const PSAWFT_FILE = 'activeFileField';
	const PSAWFT_RADIO = 'activeRadioButton';
	const PSAWFT_CHECK = 'activeCheckBox';
	const PSAWFT_DROPDOWN = 'activeDropDownList';
	const PSAWFT_LISTBOX = 'activeListBox';
	const PSAWFT_CHECKLIST = 'activeCheckBoxList';
	const PSAWFT_RADIOLIST = 'activeRadioButtonList';
	const PSAWFT_WYSIWYG = 'wysiwyg';
	const PSAWFT_MARKITUP = 'markItUp';

	//********************************************************************************
	//* Public methods
	//********************************************************************************
	
	/**
	* Adds a simple div block with label and field
	* 
	* If $sLabel is null, $sColName is used as your label name
	* 	
	* @param string $eFieldType One of the PSAWFT_* constants
	* @param CModel $oModel The model for this form
	* @param string $sColName The column/attribute name
	* @param array $arOptions The htmlOptions for the field
	* @param string $sLabel The real name of the attribute if different
	* @param array $arLabelOptions The htmlOptions for the label
	* @param array $arData Any data necessary for the field (i.e. drop down data)
	* @returns string
	*/
	public static function simpleActiveBlock( $eFieldType, $oModel, $sColName, $arOptions = array(), $sLabel = null, $arLabelOptions = array(), $arData = null )
	{
		$_sOut = '<div class="simple">';
		$_sOut .= self::activeLabelEx( $oModel, ( null == $sLabel ) ? $sColName : $sLabel, $arLabelOptions );
		$_sOut .= self::activeField( $eFieldType, $oModel, $sColName, $arOptions, null, $arData );
		$_sOut .= '</div>';

		return $_sOut;
	}

	/**
	* Adds an activefield to a form
	* 
	* @param string $eFieldType One of the PSAWFT_* constants
	* @param CModel $oModel The model for this form
	* @param string $sColName The column/attribute name
	* @param array $arHtmlOptions The htmlOptions for the field
	* @param array $arWidgetOptions The widget options for the field
	* @param array $arData Any data necessary for the field (i.e. drop down data)
	* @returns string
	*/
	public static function activeField( $eFieldType, $oModel, $sColName, $arHtmlOptions = array(), $arWidgetOptions = array(), $arData = null )
	{
		//	Auto set id and name if they aren't already...
		if ( ! isset( $arHtmlOptions[ 'name' ] ) ) $arHtmlOptions[ 'name' ] = self::resolveName( $oModel, $sColName );
		if ( ! isset( $arHtmlOptions[ 'id' ] ) ) $arHtmlOptions[ 'id' ] = self::getIdByName( $arHtmlOptions[ 'name' ] );
				
		//	Handle special types...
		switch ( $eFieldType )
		{
			//	WYSIWYG Plug-in
			case self::PSAWFT_WYSIWYG:
				CPSWysiwygWidget::create( array_merge( $arWidgetOptions, array( 'autoRun' => true, 'id' => $_sId, 'name' => $_sName ) ) );
				$eFieldType = self::PSAWFT_TEXTAREA;
				break;
				
			//	markItUp! Plug-in
			case self::PSAWFT_MARKITUP:
				$arWidgetOptions[ 'name' ] = $arHtmlOptions[ 'name' ];
				$arWidgetOptions[ 'id' ] = $arHtmlOptions[ 'id' ];
				CPSMarkItUpWidget::create( $arWidgetOptions );
				$eFieldType = self::PSAWFT_TEXTAREA;
				break;

			//	These guys need data in third parameter
			case self::PSAWFT_DROPDOWN:
			case self::PSAWFT_CHECKLIST:
			case self::PSAWFT_RADIOLIST:
			case self::PSAWFT_LISTBOX:
				return self::$eFieldType( $oModel, $sColName, $arData, $arHtmlOptions );
		}
		
		return self::$eFieldType( $oModel, $sColName, $arHtmlOptions );
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
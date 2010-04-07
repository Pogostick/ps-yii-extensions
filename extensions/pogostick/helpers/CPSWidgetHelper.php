<?php
/*
 * This file is part of the psYiiExtensions package.
 *
 * @copyright Copyright &copy; 2010 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * A collection of helper methods that augment CHtml.
 *
 * @package 	psYiiExtensions
 * @subpackage 	helpers
 *
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id$
 * @since 		v1.0.6
 *
 * @filesource
 *
 * @property string $codeModel The name of the code model for code lookups
 * @property string $hintTemplate The template for displaying hints
 * @property string $blockClass The class in which to wrap label/input pairs.
 * @property-read string $idPrefix The id prefix to use
 * @property-read string $namePrefix The name prefix to use
 * @property-read string $currentFormId The current form's id
 * @property-read string $lastFieldId The id of the last generated form field
 * @property-read string $lastFieldName The name of the last generated form field
 */
class CPSWidgetHelper extends CPSHelperBase
{
	//********************************************************************************
	//* Constants
	//********************************************************************************

	/**
	 * A prefix for generated ids
	 */
	const ID_PREFIX = 'pye';

	/**
	* These are a list of form elements that can be used along with the methods in this class.
	*/
	const 	TEXTAREA 		= 'activeTextArea';
	const 	TEXT 			= 'activeTextField';
	const 	HIDDEN 			= 'activeHiddenField';
	const 	PASSWORD 		= 'activePasswordField';
	const 	FILE 			= 'activeFileField';
	const 	RADIO 			= 'activeRadioButton';
	const 	CHECK 			= 'activeCheckBox';
	const 	DROPDOWN 		= 'activeDropDownList';
	const 	LISTBOX 		= 'activeListBox';
	const 	CHECKLIST 		= 'activeCheckBoxList';
	const 	RADIOLIST 		= 'activeRadioButtonList';
	const 	WYSIWYG 		= 'wysiwyg';
	const 	CKEDITOR 		= 'CPSCKEditorWidget';
	const 	MARKITUP 		= 'markItUp';
	const 	CODEDD 			= 'activeCodeDropDownList';
	const 	JQUI 			= 'CPSjqUIWrapper';
	const 	FG_MENU 		= 'CPSfgMenu';
	const	CAPTCHA 		= 'CCaptcha';
	const	LABEL 			= 'label';
	const	LABEL_ACTIVE	= 'activeLabel';
	const	MULTISELECT		= 'jqMultiselect';
	const	MCDROPDOWN		= 'mcDropdown';

	/**
	* Faux methods for tranformation types
	*/
	const 	CODE_DISPLAY	= 'inactiveCodeDisplay';		//	Not a real method, just a placeholder
	const 	TEXT_DISPLAY	= 'inactiveTextDisplay';		//	Not a real method, just a placeholder

	/**
	* Available UI styles
	*/
	const	UI_DEFAULT = 0;
	const	UI_JQUERY = 1;

	/**
	 * Available built-in drop-down lists
	 */
	const	DD_GENERIC = 9999;
	const	DD_US_STATES = 1000;
	const	DD_MONTH_NUMBERS = 1001;
	const	DD_MONTH_NAMES = 1002;
	const	DD_YEARS = 1003;
	const	DD_CC_TYPES = 1004;
	const	DD_DAY_NUMBERS = 1005;
	const	DD_YES_NO = 1006;
	const	DD_TIME_ZONES = 1007;
	const	DD_YES_NO_ALL = 1008;
	const	DD_JQUI_THEMES = 1009;

	/**
	 * Database-driven drop-down list
	 */
	const 	DD_CODE_TABLE = 'activeCodeDropDownList';

	/**
	 * Types of document headers
	 */
	const	HTML = 0;
	const	XHTML = 1;
	const	STRICT = 2;
	const	FRAMESET = 4;
	const	TRANSITIONAL = 8;
	const	HTML32 = -1;
	const	HTML20 = -2;
	const	LOOSE = -3;

	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	/**
	* An id counter for generating unique ids
	* @var integer
	*/
	protected static $m_iIdCount = 0;
	public static function getNextIdCount() { return self::$m_iIdCount++; }

	/**
	* Maps normal form fields to an input type
	* @staticvar array
	*/
	protected static $m_arInputMap = array(
		self::TEXTAREA 		=> 'textarea',
		self::TEXT 			=> 'text',
		self::HIDDEN 		=> 'hidden',
		self::PASSWORD 		=> 'password',
		self::FILE 			=> 'file',
		self::RADIO 		=> 'radio',
		self::CHECK 		=> 'checkbox',
	);

	/**
	* Whether or not jQuery Validate is being used...
	* @staticvar boolean
	*/
	protected static $m_bValidating = false;

	/**
	* Whether or not we are we have built a container
	* @staticvar boolean
	*/
	protected static $m_bInForm = false;

	/**
	* The default class for active fields when input hover is enabled
	* @staticvar string
	*/
	protected static $m_sOnClass = null;

	/**
	* The default class for inactive fields when input hover is enabled
	* @staticvar string
	*/
	protected static $m_sOffClass = null;

	/**
	* Tracks the current form id...
	* @staticvar string
	*/
	protected static $m_sCurrentFormId = null;
	public static function getCurrentFormId() { return self::$m_sCurrentFormId; }

	/**
	* The id of the last field that was generated
	* @var string
	*/
	protected static $m_sLastFieldId = null;
	public static function getLastFieldId() { return self::$m_sLastFieldId; }

	/**
	* The name of the last field that was generated
	* @var string
	*/
	protected static $m_sLastFieldName = null;
	public static function getLastFieldName() { return self::$m_sLastFieldName; }

	/**
	 * Did we load our CSS yet?
	 * @var boolean
	 */
	protected static $m_bCssLoaded = false;

	//********************************************************************************
	//* Properties
	//********************************************************************************

	/**
	* The name of the code model for automated code dropdown lists
	* @staticvar string
	*/
	protected static $m_sCodeModel = null;
	public static function getCodeModel() { return self::$m_sCodeModel; }
	public static function setCodeModel( $sValue ) { self::$m_sCodeModel = $sValue; }

	/**
	* Template for hints. They will be displayed right after the div simple/complex tag.
	* %%HINT%% will be replaced with your hint text.
	* @staticvar string
	*/
	protected static $m_sHintTemplate = '<p class="hint">%%HINT%%</p>';
	public static function getHintTemplate() { return self::$m_sHintTemplate; }
	public static function setHintTemplate( $sValue ) { self::$m_sHintTemplate = $sValue; }

	/**
	* Whether or not to use the id prefix. Defaults to false.
	* @staticvar boolean
	*/
	public static $m_bUseIdPrefixes = false;
	public static function getUseIdPrefixes() { return self::$m_bUseIdPrefixes; }
	public static function setUseIdPrefixes( $bValue ) { self::$m_bUseIdPrefixes = $bValue; }

	/**
	* The id prefixes to use.
	* @staticvar array
	*/
	public static $m_arIdPrefixes = array(
		'text' => 'txt_',
		'password' => 'txt_',
		'textarea' => 'txt_',
		'radio' => 'radio_',
		'check' => 'check_',
		'label' => 'label_',
		'select' => 'slt_',
		'file' => 'file_',
	);
	public static function getIdPrefix() { return self::$m_bUseIdPrefixes ? PS::o( self::$m_arIdPrefixes, $sType ) : null; }

	/**
	* Whether or not to use the name prefix. Defaults to false.
	* @staticvar boolean
	*/
	public static $m_bUseNamePrefixes = false;
	public static function getUseNamePrefixes() { return self::$m_bUseNamePrefixes; }
	public static function setUseNamePrefixes( $bValue ) { self::$m_bUseNamePrefixes = $bValue; }

	/**
	* The name prefixes to use.
	* @staticvar string
	*/
	public static $m_arNamePrefixes = array();
	public static function getNamePrefix( $eType ) { return self::$m_bUseNamePrefixes ? PS::o( self::$m_arNamePrefixes, $eType ) : null; }
	public static function setNamePrefix( $eType, $sValue ) { self::$m_arNamePrefixes[ $eType ] = $sValue; }

	/**
	* The HTML for required elements. Defaults to null
	* @staticvar string
	*/
	protected static $m_sRequiredHtml = null;
	public static function getRequiredHtml() { return self::$m_sRequiredHtml; }
	public static function setRequiredHtml( $sValue ) { self::$m_sRequiredHtml = $sValue; }

	/**
	* The suffix for label elements. Appended to labels. Defaults to ':'.
	* @staticvar string
	*/
	protected static $m_sLabelSuffix = ':';
	public static function getLabelSuffix() { return self::$m_sLabelSuffix; }
	public static function setLabelSuffix( $sValue ) { self::$m_sLabelSuffix = $sValue; }

	/**
	* The container tag for form fields
	* @var string
	*/
	protected static $m_sFormFieldContainer = 'div';
	public static function getFormFieldContainer() { return self::$m_sFormFieldContainer; }
	public static function setFormFieldContainer( $sValue ) { self::$m_sFormFieldContainer = $sValue; }

	/**
	* The css class for the container tag for form fields
	* @var string
	*/
	protected static $m_sFormFieldContainerClass = 'simple';
	public static function getFormFieldContainerClass() { return self::$m_sFormFieldContainerClass; }
	public static function setFormFieldContainerClass( $sValue ) { self::$m_sFormFieldContainerClass = $sValue; }

	/**
	* The prefix for form field containers generated by this library. Defaults to 'PIF'
	* @staticvar string
	*/
	protected static $m_sFormFieldContainerPrefix = 'PIF';
	public static function getFormFieldContainerPrefix() { return self::$m_sFormFieldContainerPrefix; }
	public static function setFormFieldContainerPrefix( $sValue ) { self::$m_sFormFieldContainerPrefix = $sValue; }

	//********************************************************************************
	//* Public methods
	//********************************************************************************

	/**
	* Generate a random ID # for a widget
	* @param string $sPrefix
	*/
	public static function getWidgetId( $sPrefix = self::ID_PREFIX )
	{
		return $sPrefix . self::getNextIdCount();
	}

	/**
	* Creates a form field
	*
	* $arOptions may contain any HTML options for the field. Special values are:
	*
	* label				Label to display
	* labelOptions		Options for the label tag
	* data				Data to pass to the field (i.e. select options array)
	* widgetOptions		Options for the widget
	* content			Optional content for field. If not specified, it's generated
	*
	* @param int $eFieldType
	* @param CModel $oModel
	* @param string $sColName
	* @param array $arOptions
	* @return string
	* @static
	* @access public
	*/
	public static function field( $eFieldType, CModel $oModel, $sColName, $arOptions = array() )
	{
		$_arDivOpts = PS::o( $arOptions, '_divOpts', array(), true );
		$_sHint = PS::o( $arOptions, 'hint', null, true );

		//	A little switcheroo...
		if ( $eFieldType == PS::CODE_DISPLAY )
		{
			$eFieldType = PS::TEXT_DISPLAY;
			$arOptions['transform'] = '*';
			$arOptions['name'] = $arOptions['id'] = 'disp_' . $oModel->id . '_' . $sColName;
			$arOptions['class'] = PS::addClass( $arOptions['class'], 'ps-text-display' );
			$_sOut = PS::activeHiddenField( $oModel, $sColName );
			$_sOut .= PS::field( $eFieldType, $oModel, $sColName, $arOptions );
		}
		else if ( $eFieldType == PS::MULTISELECT )
		{
			//	Another special dealio
			$arWidgetOptions = PS::o( $arOptions, 'widgetOptions', array(), true );
			$arWidgetOptions['name'] = PS::o( $arOptions, 'name', $sColName, true );
			$arWidgetOptions['id'] = PS::o( $arOptions, 'id', $arWidgetOptions['name'], true );
			$arWidgetOptions['naked'] = true;
			$_oSelected = PS::o( $arOptions, 'selected', null, true );
			$arWidgetOptions['extraScriptFiles'] = array( 'js/plugins/blockUI/jquery.blockUI.js', 'js/plugins/localisation/jquery.localisation-min.js', 'js/plugins/tmpl/jquery.tmpl.1.1.1.js' );
			CPSjQueryWidget::create( 'multiselect', $arWidgetOptions );
			$arOptions['class'] = PS::addClass( PS::o( $arOptions, 'class', null ), 'multiselect' );
			$arOptions['multiple'] = 'multiple';
			return self::dropDownList( $sColName, $_oSelected, PS::o( $arOptions, 'data', null, true ), $arOptions );
		}
		else
		{
			//	Get our operating parameters
			$_sLabel = PS::o( $arOptions, 'label', null, true );
			$_arLabelOptions = PS::o( $arOptions, 'labelOptions', array(), true );
			if ( $eFieldType == PS::TEXTAREA ) $_arLabelOptions['style'] .= 'vertical-align:top;';
			$_sSuffixToUse = PS::o( $_arLabelOptions, 'noSuffix', false, true ) ? '' : self::$m_sLabelSuffix;
			$_arWidgetOptions = PS::o( $arOptions, 'widgetOptions', array(), true );
			$_arData = PS::o( $arOptions, 'data', null, true );
			$_sHtml = PS::o( $arOptions, '_appendHtml', '', true );
			$_sDivClass = PS::o( $arOptions, '_divClass', null, true );
			$_sTransform = PS::o( $arOptions, 'transform', null, true );
			$_sTitle = PS::o( $arOptions, 'title', null );
			$_arValueMap = PS::o( $arOptions, 'valueMap', array(), true );
			$_sContent = PS::o( $arOptions, 'content', null, true );

			//	Value map...
			if ( in_array( $oModel->{$sColName}, array_keys( $_arValueMap ) ) && isset( $_arValueMap[$oModel->{$sColName}] ) )
				$arOptions['value'] = $_arValueMap[$oModel->{$sColName}];

			//	Do auto-tooltipping...
			if ( ! $_sTitle && $oModel && method_exists( $oModel, 'attributeTooltips' ) )
			{
				if ( $_arTemp = $oModel->attributeTooltips() )
				{
					if ( isset( $_arTemp[$sColName] ) )
						$arOptions['title'] = self::encode( $_arTemp[ $sColName ] );
				}
			}

			//	Denote checkbox/radiobutton labels
			if ( ! $_sDivClass & ( $eFieldType == self::CHECK || $eFieldType == self::RADIO || $eFieldType == self::CHECKLIST || $eFieldType == self::RADIOLIST ) )
				$_sDivClass = 'chk_label';

			//	Need an id for div tag
			if ( ! isset( $arOptions[ 'id' ] ) ) $arOptions[ 'id' ] = self::getIdByName( self::resolveName( $oModel, $sColName ) );

			//	Preset class for hover effects if enabled...
			if ( isset( self::$m_sOffClass ) && ! isset( $arOptions[ 'class' ] ) )
				$arOptions[ 'class' ] = self::$m_sOffClass;

			if ( null == $oModel )
				$_sOut = self::label( $_sLabel, $arOptions[ 'id' ], $_arLabelOptions );
			else
			{
				//	Set label name
				$_arLabelOptions['label'] = PS::nvl( $_sLabel, PS::nvl( $oModel->getAttributeLabel( $sColName ), $sColName ) ) . $_sSuffixToUse;
				$_sOut = ( $eFieldType == PS::TEXT_DISPLAY ? self::activeLabel( $oModel, $sColName, $_arLabelOptions ) : self::activeLabelEx( $oModel, $sColName, $_arLabelOptions ) );
			}

			//	Do a value transform if requested
			if ( $_sTransform && $oModel ) $oModel->{$sColName} = CPSTransform::valueOf( $_sTransform, $oModel->$sColName );

			//	Build our field
			$_sOut .= ( null !== $_sContent ) ? $_sContent : self::activeField( $eFieldType, $oModel, $sColName, $arOptions, $_arWidgetOptions, $_arData );
			$_sOut .= $_sHtml;

			//	Construct the div...
			$_arDivOpts = array_merge(
				$_arDivOpts,
				array(
					'id' => self::$m_sFormFieldContainerPrefix . '_' . $arOptions['id'],
					'class' => trim( self::$m_sFormFieldContainerClass . ' ' . $_sDivClass ),
				)
			);
		}

		//	Any hints?
		if ( $_sHint ) $_sHint = str_ireplace( '%%HINT%%', $_sHint, self::$m_sHintTemplate );

		return PS::tag( self::$m_sFormFieldContainer, $_arDivOpts, $_sOut . $_sHint );
	}

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
	* @deprecated
	*/
	public static function simpleActiveBlock( $eFieldType, $oModel, $sColName, $arOptions = array(), $sLabel = null, $arLabelOptions = array(), $arData = null, $arWidgetOptions = array() )
	{
		return self::field( $eFieldType, $oModel, $sColName,
			array_merge(
				$arOptions,
				array(
					'label' => $sLabel,
					'labelOptions' => $arLabelOptions,
					'data' => $arData,
					'widgetOptions' => $arWidgetOptions,
				)
			)
		);
	}

	/**
	* Adds an activefield to a form
	*
	* There are two special options you can use in $arHtmlOptions:
	*
	*   _appendHtml		--	Extra Html code/scripts to be inserted AFTER the form element has been created
	*   _widget			--	The name of the jQuery UI widget to create when type = self::JQUI
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
		//	Auto set id and name if they aren't already...
		if ( ! isset( $arHtmlOptions[ 'name' ] ) ) $arHtmlOptions[ 'name' ] = ( null != $oModel ) ? self::resolveName( $oModel, $sColName ) : $sColName;
		if ( ! isset( $arHtmlOptions[ 'id' ] ) ) $arHtmlOptions[ 'id' ] = self::getIdByName( $arHtmlOptions[ 'name' ] );

		//	Save for callers...
		self::$m_sLastFieldName = $arHtmlOptions['name'];
		self::$m_sLastFieldId = $arHtmlOptions['id'];

		//	Non-model field?
		if ( null === $oModel )
			return self::inactiveField( $eFieldType, $sColName, $arHtmlOptions, $arWidgetOptions, $arData );

		//	Stuff to put after widget
		$_sBeforeHtml = null;
		$_sAppendHtml = PS::o( $arHtmlOptions, '_appendHtml', '', true );

		//	Are we validating this form? Add required tags automagically
		if ( self::$m_bValidating )
		{
			//	Get any additional params for validation
			$_sClass = PS::o( $arHtmlOptions, '_validate', null, true );
			if ( $oModel->isAttributeRequired( $sColName ) ) PS::addClass( $_sClass, 'required' );
			$_sClass = ' ' . PS::o( $arHtmlOptions, 'class', null );
			$arHtmlOptions['class'] = trim( $_sClass );
		}

		//	Get our value...
		if ( $sColName != ( $_sCleanCol = CPSTransform::cleanColumn( $sColName ) ) )
		{
			//	Use our handy transformer...
			$sColName = $_sCleanCol;
			$_oValue = CPSTransform::getValue( $oModel, $sColName );
		}
		else
			$_oValue = PS::o( $arHtmlOptions, 'value', $oModel->{$sColName}, true );

		//	Handle custom drop downs...
		if ( self::setDropDownValues( $eFieldType, $arHtmlOptions, $arData, PS::nvl( $oModel->{$sColName} ) ) )
			$eFieldType = self::DROPDOWN;

		//	Handle special types...
		switch ( $eFieldType )
		{
			case self::TEXT_DISPLAY:
				$arHtmlOptions['style'] = PS::o( $arHtmlOptions, 'style' ) . ' border:none; background-color: transparent;';
				$arHtmlOptions['class'] = PS::addClass( $arOptions['class'], 'ps-text-display' );
				$arHtmlOptions['content'] = PS::tag( 'label', array( 'class' => 'ps-text-display', CPSTransform::valueOf( '*', $oModel->$sColName ) ) );
				$eFieldType = self::TEXT;
				break;

			//	Build a jQuery UI widget
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

			//	Build a McDropdown
			case self::MCDROPDOWN:
				$arWidgetOptions[ 'linkText' ] = false;
				$_sTarget = PS::o( $arWidgetOptions, 'target', $arHtmlOptions['id'] );
				$_sTargetMenu = PS::o( $arWidgetOptions, 'targetMenu', $arHtmlOptions['id'] . '_menu' );
				$_sValueColumn = PS::o( $arHtmlOptions, 'valueColumn' );

				//	Insert text field...
				$_sOut = PS::textField( $arHtmlOptions['name'], $_oValue, array( 'id' => $arHtmlOptions['id'] ) );

				//	Create menu...
				$_sOut .= CPSTransform::asUnorderedList( $arData, array( 'class' => 'mcdropdown_menu', 'valueColumn' => $_sValueColumn, 'linkText' => false, 'id' => $_sTargetMenu ) );

				$arWidgetOptions['target'] = $_sTarget;
				$arWidgetOptions['targetMenu'] = $_sTargetMenu;

				CPSMcDropdownWidget::create( null, $arWidgetOptions );
				return $_sBeforeHtml . $_sOut . $_sAppendHtml;

			//	Build a Filament Group menu
			case self::FG_MENU:
				ob_start();
				$arWidgetOptions[ 'prompt' ] = PS::o( $arHtmlOptions, 'prompt', 'Select One...', true );
				CPSfgMenu::create( null, $arWidgetOptions );
				$_sOut = ob_get_contents();
				ob_end_clean();
				return $_sBeforeHtml . $_sOut . $_sAppendHtml;

			//	Default for text field
			case self::TEXT:
				//	Masked input?
				$_sMask = PS::o( $arHtmlOptions, 'mask', null, true );
				if ( ! empty( $_sMask ) ) $_oMask = CPSjqMaskedInputWrapper::create( null, array( 'target' => '#' . $arHtmlOptions[ 'id' ], 'mask' => $_sMask ) );

				if ( ! isset( $arHtmlOptions[ 'size' ] ) )
					$arHtmlOptions[ 'size' ] = 60;
				break;

			//	WYSIWYG Plug-in
			case self::WYSIWYG:
				CPSWysiwygWidget::create( null, array_merge( $arWidgetOptions, array( 'autoRun' => true, 'id' => $_sId, 'name' => $_sName ) ) );
				$eFieldType = self::TEXTAREA;
				break;

			//	CKEditor Plug-in
			case self::CKEDITOR:
				CPSCKEditorWidget::create( null, array_merge( $arWidgetOptions, array( 'autoRun' => true, 'target' => $arHtmlOptions[ 'id' ] ) ) );
				$eFieldType = self::TEXTAREA;
				break;

			//	markItUp! Plug-in
			case self::MARKITUP:
				$arWidgetOptions[ 'name' ] = $arHtmlOptions[ 'name' ];
				$arWidgetOptions[ 'id' ] = $arHtmlOptions[ 'id' ];
				CPSMarkItUpWidget::create( null, $arWidgetOptions );
				$eFieldType = self::TEXTAREA;
				break;

			case self::CAPTCHA:
				$arOptions['hint'] = 'Please enter the letters as they are shown in the image above.<br />Letters are not case-sensitive.';
				ob_start();
				echo PS::openTag( 'div', array( 'class' => 'ps-captcha-container' ) );
				Yii::app()->getController()->widget( self::CAPTCHA, $arWidgetOptions );
				echo PS::closeTag( 'div' );
				$_sBeforeHtml = ob_get_contents();
				ob_end_clean();
				$eFieldType = self::TEXT;
				break;

			//	These guys need data in third parameter
			case self::DROPDOWN:
				//	Auto-set prompt if not there...
				if ( ! isset( $arHtmlOptions[ 'noprompt' ] ) ) $arHtmlOptions[ 'prompt' ] = PS::o( $arHtmlOptions, 'prompt', 'Select One...', true );
				//	Intentionally fall through to next block...

			case self::CHECKLIST:
			case self::RADIOLIST:
			case self::LISTBOX:
				return self::$eFieldType( $oModel, $sColName, $arData, $arHtmlOptions );
		}

		return $_sBeforeHtml . self::$eFieldType( $oModel, $sColName, $arHtmlOptions ) . $_sAppendHtml;
	}

	/**
	* Adds a non-model field to a form
	*
	* There are two special options you can use in $arHtmlOptions:
	*
	*   _appendHtml		--	Extra Html code/scripts to be inserted AFTER the form element has been created
	*   _widget			--	The name of the jQuery UI widget to create when type = self::JQUI
	*
	* @param string $eFieldType One of the * constants
	* @param string $sColName The column/attribute name
	* @param array $arHtmlOptions The htmlOptions for the field
	* @param array $arWidgetOptions The widget options for the field
	* @param array $arData Any data necessary for the field (i.e. drop down data)
	* @returns string
	*/
	public static function inactiveField( $eFieldType, $sColName, $arHtmlOptions = array(), $arWidgetOptions = array(), $arData = null )
	{
		//	Settings
		$_sBeforeHtml = null;
		$_sAppendHtml = PS::o( $arHtmlOptions, '_appendHtml', '', true );
		$_oValue = PS::o( $arHtmlOptions, 'value', null, true );

		//	Handle special types...
		switch ( $eFieldType )
		{
			//	Build a jQuery UI widget
			case self::JQUI:
				if ( isset( $arHtmlOptions[ '_widget' ] ) )
				{
					$arWidgetOptions[ 'name' ] = $arHtmlOptions[ 'name' ];
					$arWidgetOptions[ 'id' ] = $arHtmlOptions[ 'id' ];
					$_sWidget = $arHtmlOptions[ '_widget' ];
					unset( $arHtmlOptions[ '_widget' ] );
					CPSjqUIWrapper::create( $_sWidget, $arWidgetOptions );
					$_sType = 'text';
				}
				break;

			//	WYSIWYG Plug-in
			case self::WYSIWYG:
				CPSWysiwygWidget::create( null, array_merge( $arWidgetOptions, array( 'autoRun' => true, 'id' => $_sId, 'name' => $_sName ) ) );
				$_sType = 'textarea';
				break;

			//	markItUp! Plug-in
			case self::MARKITUP:
				$arWidgetOptions[ 'name' ] = $arHtmlOptions[ 'name' ];
				$arWidgetOptions[ 'id' ] = $arHtmlOptions[ 'id' ];
				CPSMarkItUpWidget::create( null, $arWidgetOptions );
				$_sType = 'textarea';
				break;

			case self::TEXT_DISPLAY:
				$arHtmlOptions['style'] = PS::o( $arHtmlOptions, 'style' ) . ' border:none; background-color: transparent;';
				$_sType = 'text';
				break;

			default:
				$_sType = PS::o( self::$m_arInputMap, $eFieldType );
				break;
		}

		//	Do drop downs...
		if ( null != ( $arData = self::setDropDownValues( $eFieldType, $arHtmlOptions, $arData, $_oValue ) ) )
			return parent::dropDownList( $sColName, $_oValue, $arData, $arHtmlOptions );

		//	Otherwise output the field if we have a type
		if ( null != $_sType ) return self::inputField( $_sType, $sColName, $_oValue, $arHtmlOptions );

		//	No clue...
		return;

		//	Are we validating this form? Add required tags automagically
		if ( self::$m_bValidating )
		{
			//	Get any additional params for validation
			$_sClass = PS::o( $arHtmlOptions, '_validate', null, true );
			if ( $oModel->isAttributeRequired( $sColName ) ) PS::addClass( $_sClass, 'required' );
			$_sClass = ' ' . PS::o( $arHtmlOptions, 'class', null );
			$arHtmlOptions['class'] = trim( $_sClass );
		}

		//	Get our value...
		if ( $sColName != ( $_sCleanCol = CPSTransform::cleanColumn( $sColName ) ) )
		{
			//	Use our handy transformer...
			$sColName = $_sCleanCol;
			$_oValue = CPSTransform::getValue( $oModel, $sColName );
		}
		else
			$_oValue = PS::o( $arHtmlOptions, 'value', $oModel->{$sColName}, true );

		//	Handle custom drop downs...
		if ( self::setDropDownValues( $eFieldType, $arHtmlOptions, $arData, PS::nvl( $oModel->{$sColName} ) ) )
			$eFieldType = self::DROPDOWN;

		//	Handle special types...
		switch ( $eFieldType )
		{
			case self::TEXT_DISPLAY:
				$arHtmlOptions['style'] = PS::o( $arHtmlOptions, 'style' ) . ' border:none; background-color: transparent;';
				$eFieldType = self::TEXT;
				break;

			//	Build a jQuery UI widget
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

			//	Build a Filament Group menu
			case self::FG_MENU:
				ob_start();
				$arWidgetOptions[ 'prompt' ] = PS::o( $arHtmlOptions, 'prompt', 'Select One...', true );
				CPSfgMenu::create( null, $arWidgetOptions );
				$_sOut = ob_get_contents();
				ob_end_clean();
				return $_sBeforeHtml . $_sOut . $_sAppendHtml;

			//	Default for text field
			case self::TEXT:
				//	Masked input?
				$_sMask = PS::o( $arHtmlOptions, 'mask', null, true );
				if ( ! empty( $_sMask ) ) $_oMask = CPSjqMaskedInputWrapper::create( null, array( 'target' => '#' . $arHtmlOptions[ 'id' ], 'mask' => $_sMask ) );

				if ( ! isset( $arHtmlOptions[ 'size' ] ) )
					$arHtmlOptions[ 'size' ] = 60;
				break;

			//	WYSIWYG Plug-in
			case self::WYSIWYG:
				CPSWysiwygWidget::create( null, array_merge( $arWidgetOptions, array( 'autoRun' => true, 'id' => $_sId, 'name' => $_sName ) ) );
				$eFieldType = self::TEXTAREA;
				break;

			//	CKEditor Plug-in
			case self::CKEDITOR:
				CPSCKEditorWidget::create( null, array_merge( $arWidgetOptions, array( 'autoRun' => true, 'target' => $arHtmlOptions[ 'id' ] ) ) );
				$eFieldType = self::TEXTAREA;
				break;

			//	markItUp! Plug-in
			case self::MARKITUP:
				$arWidgetOptions[ 'name' ] = $arHtmlOptions[ 'name' ];
				$arWidgetOptions[ 'id' ] = $arHtmlOptions[ 'id' ];
				CPSMarkItUpWidget::create( null, $arWidgetOptions );
				$eFieldType = self::TEXTAREA;
				break;

			case self::CAPTCHA:
				$arOptions['hint'] = 'Please enter the letters as they are shown in the image above.<br />Letters are not case-sensitive.';
				ob_start();
				echo PS::openTag( 'div', array( 'class' => 'ps-captcha-container' ) );
				Yii::app()->getController()->widget( self::CAPTCHA, $arWidgetOptions );
				echo PS::closeTag( 'div' );
				$_sBeforeHtml = ob_get_contents();
				ob_end_clean();
				$eFieldType = self::TEXT;
				break;

			//	These guys need data in third parameter
			case self::DROPDOWN:
				//	Auto-set prompt if not there...
				if ( ! isset( $arHtmlOptions[ 'noprompt' ] ) ) $arHtmlOptions[ 'prompt' ] = PS::o( $arHtmlOptions, 'prompt', 'Select One...', true );
				//	Intentionally fall through to next block...

			case self::CHECKLIST:
			case self::RADIOLIST:
			case self::LISTBOX:
				return self::$eFieldType( $oModel, $sColName, $arData, $arHtmlOptions );
		}

		return $_sBeforeHtml . self::$eFieldType( $oModel, $sColName, $arHtmlOptions ) . $_sAppendHtml;
	}

	/**
	* Create a drop downlist filled with codes give a code type.
	*
	* @param CModel $oModel
	* @param string $sAttribute
	* @param array $arHtmlOptions
	* @param integer $iDefaultUID
	* @return string
	*/
	public static function activeCodeDropDownList( $oModel, $sAttribute, &$arHtmlOptions = array(), $iDefaultUID = 0 )
	{
		if ( null != ( $_sCodeModel = PS::o( $arHtmlOptions, 'codeModel', self::$m_sCodeModel, true ) ) )
		{
			$_oCodeModel = new $_sCodeModel;

			if ( $_oCodeModel instanceof CPSCodeTableModel )
			{
				$_sValType = PS::o( $arHtmlOptions, 'codeType', $sAttribute, true );
				$_sValAbbr = PS::o( $arHtmlOptions, 'codeAbbr', null, true );
				if ( ! $_sValAbbr ) $_sValAbbr = PS::o( $arHtmlOptions, 'codeAbbreviation', null, true );
				$_sValId = PS::o( $arHtmlOptions, 'codeId', null, true );
				$_sSort = PS::o( $arHtmlOptions, 'sortOrder', 'code_desc_text', true );

				if ( $_sValId )
					$_arOptions = self::listData( $_oCodeModel->findById( $_sValId ), 'id', 'code_desc_text' );
				elseif ( ! $_sValAbbr )
					$_arOptions = self::listData( $_oCodeModel->findAllByType( $_sValType, $_sSort ), 'id', 'code_desc_text' );
				elseif ( $_sValAbbr )
					$_arOptions = self::listData( $_oCodeModel->findAllByAbbreviation( $_sValAbbr, $_sValType, $_sSort ), 'id', 'code_desc_text' );

				if ( isset( $arHtmlOptions[ 'multiple' ] ) )
				{
					if ( substr( $arHtmlOptions[ 'name' ], -2 ) !== '[]' )
						$arHtmlOptions[ 'name' ] .= '[]';
				}

				return self::activeDropDownList( $oModel, $sAttribute, $_arOptions, $arHtmlOptions );
			}
		}
	}

	/**
	* Output a google analytics function...
	*
	* @param string $sId Your site id for Google Analytics
	*/
	public static function googleAnalytics( $sId )
	{
		echo<<<HTML
<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
try {
var pageTracker = _gat._getTracker("{$sId}");
pageTracker._trackPageview();
} catch(err) {}</script>
HTML;
	}

	/**
	* Enables an input highlight effect when a form field is hovered over
	*
	* @param string $sSelector The jQuery/CSS selector(s) to apply effect to
	* @param array $arOptions Options for the generated scripts/CSS
	* @access public
	* @static
	* @since psYiiExtensions v1.0.5
	*/
	public static function enableInputHover( $sSelector, $arOptions = array() )
	{
		$_sTempCss = '';

		//	Basic options...
		$_sOffClass = self::$m_sOffClass = PS::o( $arOptions, 'offClass', 'idle' );
		$_sOnClass = self::$m_sOnClass = PS::o( $arOptions, 'onClass', 'activeField' );
		$_sOffBGColor = PS::o( $arOptions, 'offBackgroundColor', '#ffffff' );
		$_sOnBGColor = PS::o( $arOptions, 'onBackgroundColor', '#ffffff' );
		$_sOnBorderColor = PS::o( $arOptions, 'onBorderColor', '#33677F' );
		$_iOnBorderSize = PS::o( $arOptions, 'onBorderSize', 1 );
		$_sOffBorderColor = PS::o( $arOptions, 'offBorderColor', '#85b1de' );
		$_iOffBorderSize = PS::o( $arOptions, 'offBorderSize', 1 );

		//	Optional background image for non-hovered field
		$_sFieldImageUrl = PS::o( $arOptions, 'fieldImageUrl' );
		$_sFieldImageRepeat = PS::o( $arOptions, 'fieldImageRepeat', 'repeat-x' );
		$_sFieldImagePosition = PS::o( $arOptions, 'fieldImagePosition', 'top' );

		//	Set up the cool input effects...
		$_sScript =<<<CODE
jQuery("{$sSelector}").addClass("{$_sOffClass}");jQuery("{$sSelector}").focus(function(){jQuery(this).addClass("{$_sOnClass}").removeClass("{$_sOffClass}");}).blur(function(){jQuery(this).removeClass("{$_sOnClass}").addClass("{$_sOffClass}");});
CODE;

		//	Register script
		PS::registerScript( md5( time() ), $_sScript );

		$_sCss =<<<CODE
{$sSelector} input[type="text"]:focus,
{$sSelector} input[type="password"]:focus,
{$sSelector} select:focus
{
	background-image: none;
	background-color: {$_sOnBGColor};
	border: solid {$_iOnBorderSize}px {$_sOnBorderColor};
}

.{$_sOnClass}
{
	background-image: none;
	background-color: {$_sOnBGColor};
	border: solid {$_iOffBorderSize}px {$_sOffBorderColor};
}

.{$_sOffClass}
{
	background-color: {$_sOffBGColor};
	border: solid {$_iOffBorderSize}px {$_sOffBorderColor};
CODE;

		if ( ! empty( $_sFieldImageUrl ) )
		{
			$_sTempCss =<<<CSS
	background-image: url('{$_sFieldImageUrl}');
	background-repeat: {$_sFieldImageRepeat};
	background-position: {$_sFieldImagePosition};
CSS;
		}

		//	Add anything else we've appended...
		$_sCss .= $_sTempCss . "\n}";

		//	Register CSS
		PS::registerCss( md5( time() ), $_sCss );
	}

	/**
	* Generates a generic drop-down select list
	*
	* @param enum $eType
	* @param string $sName
	* @param string $sLabel
	* @param array $arOptions
	* @return string|boolean
	*/
	public static function dropDown( $eType, $sName, $sLabel = null, $arOptions = array() )
	{
		$_sOut = null;
		$_sValue = PS::o( $arOptions, 'value', null, true );
		$_sLabelClass = PS::o( $arOptions, 'labelClass', null, true );

		if ( $sLabel ) $_sOut = self::label( $sLabel, $sName, $arOptions );

		if ( null == ( $_arOptions = self::getGenericDropDownValues( $eType, $arOptions ) ) )
			return false;

		if ( ! empty( $_arOptions ) )
		{
			$_sInner = '';
			$_sValue = PS::nvl( PS::o( $arOptions, 'value', null, true ), $_sValue );

			foreach ( $_arOptions as $_sKey => $_sVal )
			{
				$_arOpts = array( 'value' => $_sKey );
				if ( $_sValue == $_sKey ) $_arOpts[ 'selected' ] = 'selected';
				$_sInner .= self::tag( 'option', $_arOpts, $_sVal );
			}

			$arOptions['name'] = $sName;
			$_sOut .= self::tag( 'SELECT', $arOptions, $_sInner );
		}

		return $_sOut;
	}

	/**
	 * Generates an opening form tag.
	 * Note, only the open tag is generated. A close tag should be placed manually
	 * at the end of the form.
	 * @param mixed the form action URL (see {@link normalizeUrl} for details about this parameter.)
	 * @param string form method (e.g. post, get)
	 * @param array additional HTML attributes (see {@link tag}).
	 * @return string the generated form tag.
	 * @since 1.0.4
	 * @see endForm
	 */
	public static function beginForm( $sAction = '', $sMethod = 'POST', $arHtmlOptions = array() )
	{
		$_arValidateOptions = PS::o( $arHtmlOptions, 'validateOptions', array(), true );

		if ( PS::o( $arHtmlOptions, 'validate', false, true ) )
		{
			self::$m_bValidating = true;
			if ( ! isset( $_arValidateOptions['target'] ) ) $_arValidateOptions['target'] = self::getFormSelector( $arHtmlOptions );
			CPSjqValidate::create( null, $_arValidateOptions );
		}

		if ( PS::o( $arHtmlOptions, 'selectmenu', false, true ) )
			CPSjqSelectMenu::create( null, array( 'target' => self::getFormSelector( $arHtmlOptions ) ) );

		if ( $_sFormTitle = PS::o( $arHtmlOptions, 'formTitle', null, true ) )
			echo PS::tag( PS::o( $arHtmlOptions, 'formTitleTag', 'h1', true ), array(), $_sFormTitle );

		//	Grab current form id
		self::$m_sCurrentFormId = PS::o( $arHtmlOptions, 'id' );

		return parent::beginForm( $sAction, $sMethod, $arHtmlOptions );
	}

	/**
	 * Generates an opening form tag.
	 * Note, only the open tag is generated. A close tag should be placed manually
	 * at the end of the form.
	 * @param array $arFormOptions The options for building this form
	 * @return string the generated form tag.
	 * @since 1.0.6
	 * @see endForm
	 */
	public static function beginFormEx( $arFormOptions = array() )
	{
		//	Make sure we have a form id...
		if ( ! isset( $arFormOptions['id'] ) ) $arFormOptions['id'] = 'ps-edit-form';

		//	Get the rest of our options
		$_eUIStyle = PS::o( $arFormOptions, 'uiStyle', self::UI_DEFAULT, true );
		$_sAction = PS::o( $arFormOptions, 'action', '', true );
		$_sMethod = PS::o( $arFormOptions, 'method', 'POST', true );
		$_bSetPageTitle = PS::o( $arFormOptions, 'setPageTitle', true, true );

		//	Register form CSS if desired...
		foreach ( PS::o( $arFormOptions, 'cssFiles', array( '/css/form.css' ), true ) as $_sFile )
			PS::_rcf( $_sFile );

		//	And scripts
		foreach ( PS::o( $arFormOptions, 'scriptFiles', array(), true ) as $_sFile )
			PS::_rsf( $_sFile );

		//	What type of form?
		switch ( $_eUIStyle )
		{
			case self::UI_JQUERY:
				$_sContainerClass = 'ui-edit-container ui-widget-content';
				$_sContentClass = 'yiiForm';
				PS::$errorCss = $_sErrorClass = 'ui-state-error';
				break;

			case self::UI_DEFAULT:
			default:
				$_sContainerClass = 'ps-edit-container';
				$_sContentClass = 'yiiForm';
				PS::$errorCss = $_sErrorClass = 'ps-validate-error';
				break;
		}

		//	Set validation error class...
		if ( PS::o( $arFormOptions, 'validate', false ) == true )
		{
			$_arValid = PS::o( $arFormOptions, 'validateOptions', array(), true );
			$_arValid['errorClass'] = PS::o( $_arValid, 'errorClass', self::$errorCss );
			$_arValid['ignoreTitle'] = PS::o( $_arValid, 'ignoreTitle', true );
			$arFormOptions['validateOptions'] = $_arValid;
		}

		//	So it begins...
		$_sOut = null;

		//	Form header info...
		if ( $_sFormHeader = PS::o( $arFormOptions, 'formHeader', null, true ) )
		{
			//	Page title...
			if ( $_bSetPageTitle ) Yii::app()->getController()->pageTitle = Yii::app()->name . ' : ' . $_sFormHeader;

			//	Form title...
			$_sFormHeaderTag = PS::o( $arFormOptions, 'formHeaderTag', 'H1', true );
			$_arFormHeaderOptions = PS::o( $arFormOptions, 'formHeaderOptions', array(), true );
			$_sOut .= PS::tag( $_sFormHeaderTag, $_arFormHeaderOptions, $_sFormHeader );

			if ( $_sFormHeaderContent = PS::o( $arFormOptions, 'formHeaderContent', null, true ) )
			{
				$_sFormHeaderContentTag = PS::o( $arFormOptions, 'formHeaderContentTag', 'DIV', true );
				$_arFormHeaderContentOptions = PS::o( $arFormOptions, 'formHeaderContentOptions', array(), true );
				$_sOut .= PS::tag( $_sFormHeaderContentTag, $_arFormHeaderContentOptions, $_sFormHeaderContent );
			}
		}

		//	Build out begining of form...
		$_sOut .= PS::openTag( 'div', array( 'class' => $_sContainerClass ) );
		$_sOut .= PS::openTag( 'div', array( 'class' => $_sContentClass ) );

		//	Build the form
		self::$m_bInForm = true;
		return $_sOut . self::beginForm( $_sAction, $_sMethod, $arFormOptions );
	}

	/**
	 * Generates a closing form tag.
	 * @return string the generated tag
	 * @since 1.0.6
	 * @see beginFormEx
	 */
	public static function endForm()
	{
		//	Finish our container
		$_sAppend = ( self::$m_bInForm ) ? self::closeTag( 'div' ) . PS::closeTag( 'div' ) : null;
		self::$m_bInForm = false;

		return parent::endForm() . $_sAppend;
	}

	/**
	* Outputs a <LABEL>. NOTE: Does not add ID and NAME prefixes...
	*
	* @param mixed $sName
	* @param mixed $sLabel
	* @param mixed $arOptions
	*/
	public static function textLabel( $sName, $sLabel = null, $arOptions = array() )
	{
		$_sType = PS::o( $arOptions, '_forType', 'text' );
		$_bRequired = PS::o( $arOptions, '_required', false );
		$arOptions[ 'id' ] = $arOptions['name'] = self::getIdPrefix( 'label' ) . $sName;
		$_sSuffixToUse = PS::o( $arOptions, 'noSuffix', false, true ) ? '' : self::$m_sLabelSuffix;

		return self::tag( 'label', $arOptions, ( ( $sLabel == null ) ? $sName : $sLabel ) . $_sSuffixToUse . self::$afterRequiredLabel );
	}

	/**
	 * Generates a submit button.
	 * @param string the button label
	 * @param array additional HTML attributes. Besides normal HTML attributes, a few special
	 * attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
	 * @return string the generated button tag
	 * @see clientChange
	 */
	public static function submitButton( $sLabel = 'Submit', $arHtmlOptions = array() )
	{
		$arHtmlOptions['type'] = 'submit';

		//	jQUI Button?
		if ( PS::o( $arHtmlOptions, 'jqui', false, true ) ) return self::jquiButton( $sLabel, '_submit_', $arHtmlOptions );

		//	Otherwise use regular button
		return self::button( $sLabel, $arHtmlOptions );
	}

	/**
	* Generates a submit button bar.
	*
	* Additional HTML options are available for the bar div itself:
	*
	* barClass		The class to apply to the bar's div tag. Defaults to ps-submit-button-bar
	* noBorder		If true, no border line will be displayed on the top of the bar.
	* barLeft		If true, submit button will be flush left instead of the default flush right
	* barCenter		If true, submit button will be centered instead of the default flush right
	*
	* @param array $arHtmlOptions HTML options for the submit button.
	*/
	public static function beginButtonBar( $arHtmlOptions = array() )
	{
		$_bDialog = PS::o( $arHtmlOptions, 'jquiDialog', false );

		//	Get orientation of buttons
		if ( PS::o( $arHtmlOptions, 'barLeft' ) )
			$_sDirClass = 'ps-submit-button-bar-left';
		else if ( PS::o( $arHtmlOptions, 'barCenter' ) )
			$_sDirClass = 'ps-submit-button-bar-center';
		else
			$_sDirClass = 'ps-submit-button-bar-right';

		$_sClass = PS::o( $arHtmlOptions, 'barClass', $_bDialog ? '.ui-dialog .ui-dialog-buttonpane' : 'ps-submit-button-bar' ) . ' ' . $_sDirClass;

		if ( ! $_bNoBorder = PS::o( $arHtmlOptions, 'noBorder', false ) ) $_sClass .= ' ps-submit-button-bar-border';

		return PS::openTag( 'div', array( 'class' => $_sClass ) );
	}

	/**
	* End a button bar
	*
	*/
	public static function endButtonBar()
	{
		return PS::closeTag( 'div' );
	}

	/**
	* Generates a submit button bar.
	*
	* Additional HTML options are available for the bar div itself:
	*
	* barClass		The class to apply to the bar's div tag. Defaults to ps-submit-button-bar
	* noBorder		If true, no border line will be displayed on the top of the bar.
	* barLeft		If true, submit button will be flush left instead of the default flush right
	* barCenter		If true, submit button will be centered instead of the default flush right
	*
	* @param string $sLable The button label
	* @param array $arHtmlOptions HTML options for the submit button.
	* @returns string The generated button tag
	* @see clientChange
	*/
	public static function submitButtonBar( $sLabel = 'Submit', $arHtmlOptions = array() )
	{
		//	Make sure current form id is set if we have it...
		$arHtmlOptions['formId'] = PS::o( $arHtmlOptions, 'formId', self::$m_sCurrentFormId );
		return self::beginButtonBar( $arHtmlOptions ) . self::submitButton( $sLabel, $arHtmlOptions ) . self::endButtonBar();
	}

	/****
	* Output a jQuery UI icon, icon button, or plain button
	*
	* @param string $sLabel
	* @param string $sLink
	* @param array $arOptions
	*/
	public static function jquiButton( $sLabel, $sLink, $arOptions = array() )
	{
		static $_bRegistered = false;
		$_sSize = $_sIconPos = $_bIconOnly = null;
		$_sLink = is_array( $sLink ) ? CHtml::normalizeUrl( $sLink ) : $sLink;
		$_bSubmit = ( $_sLink == '_submit_' || PS::o( $arOptions, 'submit', false, true ) );

		$_sId = PS::o( $arOptions, 'id', self::getWidgetId( self::ID_PREFIX . '.jqbtn' ), true );
		$_sFormId = PS::o( $arOptions, 'formId', self::$m_sCurrentFormId, true );

		if ( $_sIcon = PS::o( $arOptions, 'icon', null, true ) )
		{
			$_bIconOnly = PS::o( $arOptions, 'iconOnly', false, true );
			$_sIcon = "<span class=\"ui-icon ui-icon-{$_sIcon}\"></span>";
			if ( $sLabel && ! $_bIconOnly )
				$_sIconPos = "ps-button-icon-" . PS::o( $arOptions, 'iconPosition', 'left', true );
			else
			{
				$_sSize = PS::o( $arOptions, 'iconSize', null, true );
				$_sIconPos = 'ps-button-icon-solo' . ( ( $_sSize ) ? '-' . $_sSize : '' );
			}
		}

		if ( $_sOnClick = PS::o( $arOptions, 'click', null, true ) )
			$_sOnClick = 'onClick="' . $_sOnClick . '"';
		else
		{
			if ( $_sConfirm = PS::o( $arOptions, 'confirm', null, true ) )
			{
				$_sHref = $_sLink;
				$_sForm = ( $_sFormId ) ? "document.getElementById('{$_sFormId}')" : 'this.form';
				$_sConfirm = str_replace( "'", "''", str_replace( '"', '""', $_sConfirm ) );
				$_sOnClick = "return confirmAction({$_sForm},'{$_sConfirm}','{$_sLink}','{$_sHref}');";
				$_sLink = '#';

				if ( ! $_bRegistered )
				{
					$_sAction = $_bSubmit ? 'return oForm.submit();' : 'window.location.href = sHref;';
					$_sScript = <<<HTML
function confirmAction( oForm, sMessage, sHref )
{
	jConfirm( sMessage, 'Please Confirm Your Action', function( bVal ) {
		if ( bVal )
		{
			{$_sAction}
			return true;
		}

		return false;
	});
}
HTML;
					//	Register scripts
					PS::_cs()->registerScript( self::getWidgetId( self::ID_PREFIX . '.cas.' ), $_sScript, CClientScript::POS_END );
					CPSjqUIAlerts::loadScripts();
					$_bRegistered = true;
				}
			}
			else
			{
				if ( $_bSubmit || $_sLink == '_submit_' )
				{
					$_sLink = '';
					$_sOnClick = "return jQuery(" . ( $_sFormId ? "'#{$_sFormId}'" : "'div.yiiForm>form'" ) . ").submit();";
				}
			}
		}

		//	Set our link options
		$arOptions['id'] = $_sId;
		$arOptions['title'] = $sLabel;
		$_sClass = PS::o( $arOptions, 'class', null );
		$arOptions['class'] = "ps-button {$_sIconPos} ui-state-default ui-corner-all {$_sClass}";

		//	Make sure we add our onClick...
		if ( $_sOnClick )
		{
			$arOptions['onclick'] = $_sOnClick;
			$arOptions['encode'] = false;
		}

		//	Generate the link
		return self::link( ( $_sIcon . $sLabel ), $_sLink, $arOptions );
	}

	/**
	* Formats the created and modified dates
	*
	* @param mixed $oModel
	* @deprecated This method has been moved to CPSModel
	*/
	public static function showDates( $oModel, $sCreatedColumn = 'created', $sModifiedColumn = 'modified', $sDateFormat = 'm/d/Y h:i:s A' )
	{
		$_sOut = null;

		if ( $oModel->hasAttribute( $sCreatedColumn ) && $oModel->hasAttribute( $sModifiedColumn ) )
		{
			$_dtCreate = strtotime( $oModel->$sCreatedColumn );
			$_dtLMod = strtotime( $oModel->$sModifiedColumn );

			//	Fix up dates
			$_dtCreate = date( $sDateFormat, $_dtCreate );
			$_dtLMod = date( $sDateFormat, $_dtLMod );

			$_sOut = '<div class="ps-form-footer">';
			$_sOut .= '<span><strong>Created:</strong>&nbsp;' . $_dtCreate . '</span>' . self::pipe( '/' ) . '<span><strong>Modified:</strong>&nbsp;' . $_dtLMod . '</span>';
			$_sOut .= '</div>';
		}

		return $_sOut;
	}

	/**
	 * Generates an image button with an optional hover component.
	 * @param string the button label
	 * @param string $sHref the destination link
	 * @param array additional HTML attributes. Besides normal HTML attributes, a few special
	 * attributes are also recognized (see {@link hoverImage}, {@link clientChange} and {@link tag} for more details.)
	 * @return string the generated button tag
	 * @see clientChange
	 * @see hoverImage
	 */
	public static function imageLink( $src, $sHref, $htmlOptions = array() )
	{
		if ( $_sHoverImage = PS::o( $htmlOptions, 'hoverImage', null, true ) )
		{
			//	Create the script
			$htmlOptions['id'] = PS::o( $htmlOptions, 'id', self::ID_PREFIX . self::$count++ );

			$_sScript =<<<HTML
	jQuery('#{$htmlOptions['id']}').hover(
		function(){
			jQuery(this).attr('src','{$_sHoverImage}');
		},
		function(){
			jQuery(this).attr('src','{$src}');
		}
	);
HTML;
			//	Register the script
			PS::registerScript( 'ib#' . self::getWidgetId( self::ID_PREFIX ), $_sScript );
		}

		return self::tag( 'a', array( 'href' => $sHref ), self::image( $src, null, $htmlOptions ) );
	}

	/**
	 * Generates an image submit button with an optional hover component.
	 * @param string the button label
	 * @param array additional HTML attributes. Besides normal HTML attributes, a few special
	 * attributes are also recognized (see {@link hoverImage}, {@link clientChange} and {@link tag} for more details.)
	 * @return string the generated button tag
	 * @see clientChange
	 * @see hoverImage
	 */
	public static function imageButton( $src, $htmlOptions = array() )
	{
		if ( $_sHoverImage = PS::o( $htmlOptions, 'hoverImage', null, true ) )
		{
			//	Create the script
			$htmlOptions['id'] = PS::o( $htmlOptions, 'id', self::ID_PREFIX . self::$count++ );

			$_sScript =<<<HTML
	/**
	* Hover mechanism for image button
	*/
	jQuery('#{$htmlOptions['id']}').hover(
		function(){
			jQuery(this).attr('src','{$_sHoverImage}');
		},
		function(){
			jQuery(this).attr('src','{$src}');
		}
	);
HTML;
			//	Register the script
			PS::_rs( 'ib#' . self::ID_PREFIX . self::$count++, $_sScript, CClientScript::POS_READY );
		}

		return parent::imageButton( $src, $htmlOptions );
	}

	/**
	* Retrieves the target selector of a form
	*
	* @param array $arHtmlOptions
	* @param string $sDefaultId
	* @return string
	*/
	protected static function getFormSelector( $arHtmlOptions = array(), $sDefaultId = 'div.yiiForm>form' )
	{
		$_sTarget =	PS::o( $arHtmlOptions, 'id', null );
		return ( $_sTarget == null ) ? $sDefaultId : '#' . $_sTarget;
	}

	/**
	 * Generates a radio button list.
	 * A radio button list is like a {@link checkBoxList check box list}, except that
	 * it only allows single selection.
	 * @param string name of the radio button list. You can use this name to retrieve
	 * the selected value(s) once the form is submitted.
	 * @param mixed selection of the radio buttons. This can be either a string
	 * for single selection or an array for multiple selections.
	 * @param array value-label pairs used to generate the radio button list.
	 * Note, the values will be automatically HTML-encoded, while the labels will not.
	 * @param array addtional HTML options. The options will be applied to
	 * each checkbox input. The following special options are recognized:
	 * <ul>
	 * <li>template: string, specifies how each checkbox is rendered. Defaults
	 * to "{input} {label}", where "{input}" will be replaced by the generated
	 * radio button input tag while "{label}" be replaced by the corresponding radio button label.</li>
	 * <li>separator: string, specifies the string that separates the generated radio buttons.</li>
	 * <li>labelOptions: array, specifies the additional HTML attributes to be rendered
	 * for every label tag in the list. This option has been available since version 1.0.10.</li>
	 * </ul>
	 * @return string the generated radio button list
	 */
	public static function radioButtonList($name,$select,$data,$htmlOptions=array())
	{
		$template=isset($htmlOptions['template'])?$htmlOptions['template']:'{input} {label}';
		$separator=isset($htmlOptions['separator'])?$htmlOptions['separator']:"<br/>\n";
		unset($htmlOptions['template'],$htmlOptions['separator']);

		$labelOptions=isset($htmlOptions['labelOptions'])?$htmlOptions['labelOptions']:array();
		unset($htmlOptions['labelOptions']);

		$items=array();
		$baseID=self::getIdByName($name);
		$id=0;
		foreach($data as $value=>$label)
		{
			$checked=!strcmp($value,$select);
			$htmlOptions['value']=$value;
			$htmlOptions['id']=$baseID.'_'.$id++;
			$option=self::radioButton($name,$checked,$htmlOptions);
			$label=self::label($label,$htmlOptions['id'],$labelOptions);
			$items[]=strtr($template,array('{input}'=>$option,'{label}'=>$label));
		}
		return implode($separator,$items);
	}

	/**
	* Create a field set with optional legend
	*
	* @param string $sLegend
	* @param array $arOptions
	* @return string
	*/
	public static function beginFieldset( $sLegend, $arOptions = array() )
	{
		$_arLegendOptions = PS::o( $arOptions, 'legendOptions', array(), true );
		return self::tag( 'fieldset', $arOptions, ( $sLegend ? self::tag( 'legend', $_arLegendOptions, $sLegend ) : false ), false );
	}

	/**
	* Closes an open fieldset
	*
	* @returns string
	*/
	public static function endFieldset()
	{
		return self::closeTag( 'fieldset' );
	}

	/**
	* Outputs a themed div with a message
	*
	* @param string $sMsg
	* @return boolean
	*/
	public static function flashHighlight( $sMsg = null )
	{
		if ( $sMsg )
		{
			return self::tag( 'div',
				array(
					'class' => 'ui-widget'
				),
				self::tag( 'div',
					array(
						'class' => 'ui-state-highlight ui-corner-all',
						'style' => 'padding:1em; margin: 5px 0px 15px 0px;'
					),
					'<p><span class="ui-icon ui-icon-info" style="float: left; margin-right:10px;"></span>' . $sMsg . '</p>'
				)
			);
		}
	}

	/**
	* Puts up flash div if the flash message specified is set. Defaults to 'success'.
	* @param string $sWhich
	*/
	public static function flashMessage( $sWhich = 'success', $bLeft = false )
	{
		$_sMsg = ( Yii::app()->user->hasFlash( $sWhich ) ) ? Yii::app()->user->getFlash( $sWhich ) : null;
		$_sDiv = 'ps-flash-display' . ( $bLeft ? '-left' : '' );
		PS::registerScript( 'ps.flash.display', 'jQuery(".' . $_sDiv . '").animate({opacity: 1.0}, 3000).fadeOut();' );
		return self::tag( 'div', array( 'class' => $_sDiv ), $_sMsg );
	}

	/**
	* Returns a styled menu separator.
	* @param string $sInner
	* @returns string
	*/
	public static function pipe( $sInner = '|' )
	{
		return '<span class="ps-pipe">' . $sInner . '</span>';
	}

	/**
	 * Displays a summary of validation errors for one or several models.
	 * @param mixed the models whose input errors are to be displayed. This can be either a single model or an array of models.
	 * @param string a piece of HTML code that appears in front of the errors
	 * @param string a piece of HTML code that appears at the end of the errors
	 * @param array additional HTML attributes to be rendered in the container div tag. This parameter has been available since version 1.0.7.
	 * @return string the error summary. Empty if no errors are found.
	 * @see CModel::getErrors
	 * @see errorSummaryCss
	 */
	public static function errorSummary( $oModel, $sHeader = null, $sFooter = null, $arHtmlOptions = array() )
	{
		$_sContent = null;
		$_arModel = $oModel;
		$_bNoIcon = PS::o( $arHtmlOptions, 'noIcon', false, true );
		self::$errorSummaryCss = 'ps-error-summary ui-state-error';

		if ( ! is_array( $_arModel ) ) $_arModel = array( $oModel );

		foreach ( $_arModel as $_oModel )
		{
			$_arError = $_oModel->getErrors();

			foreach ( $_arError as $_oError )
			{
				foreach ( $_oError as $_sError )
					if ( ! empty( $_sError ) ) $_sContent .= PS::tag( 'li', array(), $_sError );
			}
		}

		if ( $_sContent !== null )
		{
			$_sIcon = ( ! $_bNoIcon ) ? '<span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span>' : null;
			if ( ! $sHeader ) $sHeader = self::tag( 'strong', array(), Yii::t( 'yii', 'Please fix the following input errors:' ) );

			$arHtmlOptions['class'] = PS::o( $arHtmlOptions, 'class', self::$errorSummaryCss, true );
			return self::tag( 'div', $arHtmlOptions, $_sIcon . $sHeader . self::tag( 'ul', array(), $_sContent ) ) . $sFooter;
		}
	}

	/**
	* Transforms markdown to safe text
	* @param string $sText
	* @return Purified
	*/
	public static function markdownTransform( $sText )
	{
		$_oParser = new CMarkdownParser();
		return $_oParser->safeTransform( $sText );
	}

	/**
	 * Adds a class to an array of classes.
	 * @param mixed The existing classes. If a string is passed in, a string is returned. If an array is passed in, an array is returned.
	 * @param mixed $oClassData
	 */
	public static function addClass( $oClass, $oClassData )
	{
		$_arClassList = ( ! is_array( $oClass ) ) ? explode( ' ', trim( $oClass ) ) : $oClass;
		$_arNewClasses = PS::makeArray( $oClassData );

		foreach ( $_arNewClasses as $_sClass )
			if ( ! in_array( $_sClass, $_arClassList ) ) $_arClassList[] = $_sClass;

		return is_array( $oClass ) ? $_arClassList : implode( ' ', $_arClassList );
	}

	/**
	 * Removes a class, or pattern of classes from an array of classes.
	 * @param mixed The existing classes. If a string is passed in, a string is returned. If an array is passed in, an array is returned.
	 * @param mixed $oClassData
	 */
	public static function removeClass( $oClass, $oClassData )
	{
		$_arClassList = ( ! is_array( $oClass ) ) ? explode( ' ', trim( $oClass ) ) : $oClass;
		$_arNewClasses = PS::makeArray( $oClassData );
		$_arClass = array();

		foreach ( $_arClassList as $_sClass )
		{
			if ( ! in_array( $_sClass, $_arNewClasses ) )
				$_arClass[] = $_sClass;
		}

		return is_array( $oClass ) ? $_arClass : implode( ' ', $_arClass );
	}

	//********************************************************************************
	//* Protected Methods
	//********************************************************************************

	/**
	* Gets the options for pre-fab select boxes
	*
	* @param mixed $eFieldType
	* @returns boolean
	*/
	protected static function setDropDownValues( $eFieldType, &$arHtmlOptions = array(), &$arData = null, $oSelected = null )
	{
		$_arData = null;

		//	One of our generics? Set data, type and return
		if ( $_arData = self::getGenericDropDownValues( $eFieldType, $arHtmlOptions, $arData ) )
		{
			$eFieldType = self::DROPDOWN;
			$arData = $_arData;
		}
		else
		{
			//	Generic or dropdown? Set data, type and return
			if ( $eFieldType == self::DD_GENERIC || $eFieldType == self::DROPDOWN )
			{
				$_arData = $arData;
				$eFieldType = self::DROPDOWN;
			}
		}

		//	Return a copy of the data array
		return $_arData;
	}

	/**
	* Gets the options for pre-fab select boxes
	*
	* @param mixed $eFieldType
	* @returns boolean
	*/
	protected static function getGenericDropDownValues( $eType, &$arHtmlOptions = array(), &$arData = null )
	{
		$_arData = null;

		if ( is_numeric( $eType ) )
		{
			switch ( $eType )
			{
				case self::DD_GENERIC:
					$_arData = PS::nvl( $arData, PS::o( $arHtmlOptions, 'data', null ) );
					break;

				case self::DD_YES_NO:
					$_arData = array( 0 => 'No', 1 => 'Yes' );
					break;

				case self::DD_YES_NO_ALL:
					$_arData = array( -1 => 'All', 0 => 'No', 1 => 'Yes' );
					break;

				case self::DD_US_STATES:
					$_arData = require( 'static/us_state_array.php' );
					break;

				case self::DD_MONTH_NUMBERS:
					if ( null == PS::o( $arHtmlOptions, 'value' ) ) $arHtmlOptions['value'] = date('m');
					$_arData = require( 'static/month_numbers_array.php' );
					break;

				case self::DD_DAY_NUMBERS:
					if ( null == PS::o( $arHtmlOptions, 'value' ) ) $arHtmlOptions['value'] = date('d');
					$_arData = require( 'static/day_numbers_array.php' );
					break;

				case self::DD_MONTH_NAMES:
					if ( null == PS::o( $arHtmlOptions, 'value' ) ) $arHtmlOptions['value'] = date('m');
					$_arData = require( 'static/month_names_array.php' );
					break;

				case self::DD_YEARS:
					if ( null == PS::o( $arHtmlOptions, 'value' ) ) $arHtmlOptions['value'] = date('Y');
					$_iRange = PS::o( $arHtmlOptions, 'range', 5, true );
					$_iRangeStart = PS::o( $arHtmlOptions, 'rangeStart', date('Y'), true );
					$_arData = array();
					for ( $_i = 0, $_iBaseYear = $_iRangeStart; $_i < $_iRange; $_i++ ) $_arData[ ( $_iBaseYear + $_i ) ] = ( $_iBaseYear + $_i );
					break;

				case self::DD_CC_TYPES:
					$_arData = require( 'static/cc_types_array.php' );
					break;

				case self::DD_TIME_ZONES:
					$_arData = require( 'static/time_zones_array.php' );
					break;

				case self::DD_JQUI_THEMES:
					$_arData = CPSjqUIWrapper::getValidThemes();
					break;
			}
		}

		return $_arData;
	}

}

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
 * @author Jerry Ablan <jablan@pogostick.com>
 * @version SVN: $Id$
 * @package psYiiExtensions
 * @subpackage Helpers
 * @since 1.0.0
 */
class CPSActiveWidgets extends CHtml
{
	//********************************************************************************
	//* Constants
	//********************************************************************************
	
	//	Me
	const ID_PREFIX = 'psaw';
	
	/**
	* These are a list of form elements that can be used along with the methods in this class.
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
	const CKEDITOR = 'ckeditor';
	const MARKITUP = 'markItUp';
	const CODEDD = 'activeCodeDropDownList';
	const JQUI = 'CPSjqUIWrapper';
	const FG_MENU = 'CPSfgMenu';

	//	Types of drop downs...
	const	DD_GENERIC = -1;
	const	DD_US_STATES = 0;
	const	DD_MONTH_NUMBERS = 1;
	const	DD_MONTH_NAMES = 2;
	const	DD_YEARS = 3;
	const	DD_CC_TYPES = 4;
	const	DD_DAY_NUMBERS = 5;
	const	DD_YES_NO = 6;
	
	//	Types of HTML
	const	HTML = 0;
	const	XHTML = 1;
	const	STRICT = 2;
	const	FRAMESET = 4;
	const	TRANSITIONAL = 8;
	const	HTML32 = -1;
	const	HTML20 = -2;
	const	LOOSE = -3;
	
	//********************************************************************************
	//* Member variables
	//********************************************************************************

	/**
	* Template for hints. They will be displayed right after the div simple/complex tag.
	* %%HINT%% will be replaced with your hint text.
	* 
	* @var string
	*/
	protected static $m_sHintTemplate = '<p class="hint">%%HINT%%</p>';
	public function getHintTemplate() { return self::$m_sHintTemplate; }
	public function setHintTemplate( $sValue ) { self::$m_sHintTemplate = $sValue; }
	
	protected static $m_arInputMap = array( 
		self::TEXTAREA => 'textarea',
		self::TEXT => 'text',
		self::HIDDEN => 'hidden',
		self::PASSWORD => 'password',
		self::FILE => 'file',
		self::RADIO => 'radio',
		self::CHECK => 'checkbox',
	);
	
	/**
	* Name Prefixes
	*/
	public static $useIdPrefixes = false;
	public static $idPrefixes = array(
		'text' => 'txt_',
		'password' => 'txt_',
		'textarea' => 'txt_',
		'radio' => 'radio_',
		'check' => 'check_',
		'label' => 'label_',
		'select' => 'slt_',
		'file' => 'file_',
	);
	public static function getIdPrefix( $sType ) { return ( self::$useIdPrefixes && is_array( self::$idPrefixes ) && self::nvl( self::$idPrefixes[ $sType ] ) ) ? self::$idPrefixes[ $sType ] : null; }

	/**
	* Name Prefixes
	*/
	public static $useNamePrefixes = false;
	public static $namePrefixes = array();
	public static function getNamePrefix( $sType ) { return ( self::$useNamePrefixes && is_array( self::$namePrefixes ) && self::nvl( self::$namePrefixes[ $sType ] )  ) ? self::$namePrefixes[ $sType ] : null; }
	
	public static $requiredHtml = null;
	
	public static $labelSuffix = ':';

	public static $formFieldContainer = 'div';
	public static $formFieldContainerClass = 'input-holder';
	
	/**
	* Whether or not jQuery Validate is being used...
	* 
	* @var boolean
	*/
	protected static $m_bValidating = false;

	/**
	* The default class for active fields
	* 
	* @var string
	*/
	protected static $m_sOnClass = null;
	/**
	* The default class for inactive fields
	* 
	* @var string
	*/
	protected static $m_sOffClass = null;
	
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
		//	Get append Html		
		$_sHtml = PS::o( $arOptions, '_appendHtml', '', true );
		$_sDivClass = PS::o( $arOptions, '_divClass', null, true );
		$_sTransform = PS::o( $arOptions, 'transform', null, true );
		$_sTitle = PS::o( $arOptions, 'title', null );
		$_sHint = PS::o( $arOptions, 'hint', null, true );
		$_arValueMap = PS::o( $arOptions, 'valueMap', array(), true );

		//	Value map...
		if ( in_array( $oModel->{$sColName}, array_keys( $_arValueMap ) ) && isset( $_arValueMap[$oModel->{$sColName}] ) ) 
			$arOptions['value'] = $_arValueMap[$oModel->{$sColName}];
		
		//	Do auto-tooltipping...
		if ( ! $_sTitle && $oModel && method_exists( $oModel, 'attributeTooltips' ) )
		{
			if ( $_arTemp = $oModel->attributeTooltips() )
			{
				if ( isset( $_arTemp[$sColName] ) ) 
				{
					$arOptions['title'] = $_arTemp[ $sColName ];
				}
			}
		}

		if ( ! $_sDivClass & ( $eFieldType == self::CHECK || $eFieldType == self::RADIO || $eFieldType == self::CHECKLIST || $eFieldType == self::RADIOLIST ) ) $_sDivClass = 'chk_label';
		
		//	Need an id for div tag
		if ( ! isset( $arOptions[ 'id' ] ) ) $arOptions[ 'id' ] = self::getIdByName( self::resolveName( $oModel, $sColName ) );
		
		//	Preset class for hover effects if enabled...
		if ( isset( self::$m_sOffClass ) && ! isset( $arOptions[ 'class' ] ) ) $arOptions[ 'class' ] = self::$m_sOffClass;
		
		if ( null == $oModel )		
			$_sOut = self::label( $sLabel, $arOptions[ 'id' ], $arLabelOptions );
		else
		{
			//	Set label name
			$arLabelOptions['label'] = CPSHelp::nvl( $sLabel, CPSHelp::nvl( $oModel->getAttributeLabel( $sColName ), $sColName ) ) . self::$labelSuffix;
			$_sOut = self::activeLabelEx( $oModel, $sColName, $arLabelOptions );
		}

		if ( $_sTransform && $oModel ) $oModel->$sColName = CPSTransform::value( $_sTransform, $oModel->$sColName );
		
		$_sOut .= self::activeField( $eFieldType, $oModel, $sColName, $arOptions, $arWidgetOptions, $arData );
		$_sOut .= $_sHtml;
		
		//	Any hints?
		if ( $_sHint ) $_sHint = str_ireplace( '%%HINT%%', $_sHint, self::$m_sHintTemplate );

		//	Construct the div...
		$_sOut = '<div id="PIF_' . $arOptions[ 'id' ] . '" class="simple' . ( $_sDivClass ? ' ' . $_sDivClass : '' ) . '">' . $_sOut . $_sHint . '</div>';

		return $_sOut;
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
		//	Stuff to put after widget
		$_sAppendHtml = PS::o( $arHtmlOptions, '_appendHtml', '', true );
		
		//	Auto set id and name if they aren't already...
		if ( ! isset( $arHtmlOptions[ 'name' ] ) ) $arHtmlOptions[ 'name' ] = ( null != $oModel ) ? self::resolveName( $oModel, $sColName ) : $sColName;
		if ( ! isset( $arHtmlOptions[ 'id' ] ) ) $arHtmlOptions[ 'id' ] = self::getIdByName( $arHtmlOptions[ 'name' ] );
		
		if ( self::$m_bValidating )
		{
			//	Get any additional params for validation
			$_sClass = PS::o( $arHtmlOptions, '_validate', null, true );
			if ( $oModel->isAttributeRequired( $sColName, self::$scenario ) ) $_sClass .= ' required';
			$_sClass .= ' ' . PS::o( $arHtmlOptions, 'class', null );
			$arHtmlOptions['class'] = trim( $_sClass );
		}
		
		if ( null === $oModel )
		{
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
					CPSWysiwygWidget::create( array_merge( $arWidgetOptions, array( 'autoRun' => true, 'id' => $_sId, 'name' => $_sName ) ) );
					$_sType = 'textarea';
					break;                                                                                
					
				//	markItUp! Plug-in
				case self::MARKITUP:
					$arWidgetOptions[ 'name' ] = $arHtmlOptions[ 'name' ];
					$arWidgetOptions[ 'id' ] = $arHtmlOptions[ 'id' ];
					CPSMarkItUpWidget::create( $arWidgetOptions );
					$_sType = 'textarea';
					break;
					
				default:
					$_sType = PS::o( self::$m_arInputMap, $eFieldType );
             		break;
			}

			if ( is_numeric( $eFieldType ) )
			{
				switch ( $eFieldType )
				{
					case self::DD_YES_NO:
						$arHtmlOptions['options'] = PS::o( $arHtmlOptions, 'options', array( 0 => 'No', 1 => 'Yes' ), true );
						//	Fall through...
					
					case self::DD_GENERIC:	//	Options passed in via array
					case self::DD_US_STATES:
					case self::DD_MONTH_NUMBERS:
					case self::DD_DAY_NUMBERS:
					case self::DD_MONTH_NAMES:
					case self::DD_YEARS:
					case self::DD_CC_TYPES:
						return self::dropDown( $eFieldType, $sColName, null, $arHtmlOptions );
				}
			}
			
			if ( null != $_sType )
				return self::inputField( $_sType, $sColName, $_oValue, $arHtmlOptions );
				
			return;
		}
		
		//	Handle custom drop downs...
		if ( is_numeric( $eFieldType ) )
		{
			switch ( $eFieldType )
			{
				case self::DD_GENERIC:	//	Options passed in via array
					$eFieldType = self::DROPDOWN;
					break;
					
				case self::DD_YES_NO:
					$eFieldType = self::DROPDOWN;
					$arData = array( 0 => 'No', 1 => 'Yes' );
					break;
					
				case self::DD_US_STATES:
					$eFieldType = self::DROPDOWN;
					$arData = require( 'us_state_array.php' );
					break;
					
				case self::DD_MONTH_NUMBERS:
					$eFieldType = self::DROPDOWN;
					if ( $_oValue == null ) $_oValue = date( 'm' );
					$arData = require( 'month_numbers_array.php' );
					break;
					
				case self::DD_DAY_NUMBERS:
					$eFieldType = self::DROPDOWN;
					if ( $_oValue == null ) $_oValue = date( 'd' );
					$arData = require( 'day_numbers_array.php' );
					break;
					
				case self::DD_MONTH_NAMES:
					$eFieldType = self::DROPDOWN;
					if ( $_oValue == null ) $_oValue = date( 'm' );
					$arData = require( 'month_names_array.php' );
					break;
					
				case self::DD_YEARS:
					if ( $_oValue == null ) $_oValue = date( 'Y' );
					$_iRange = PS::o( $arOptions, 'range', 5, true );
					$_iRangeStart = PS::o( $arOptions, 'rangeStart', date('Y'), true );
					
					$arData = array();
					for ( $_i = 0, $_iBaseYear = $_iRangeStart; $_i < $_iRange; $_i++ ) $arData[ ( $_iBaseYear + $_i ) ] = ( $_iBaseYear + $_i );
					break;
					
				case self::DD_CC_TYPES:
					$arData = require( 'cc_types_array.php' );
					break;
			}
		}
			
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
					$eFieldType = self::TEXT;
				}
				break;
				
			//	Build a Filament Group menu
			case self::FG_MENU:
				CPSfgMenu::create( $arWidgetOptions );
				return;
			
			//	Default for text field
			case self::TEXT:
				//	Masked input?
				$_sMask = PS::o( $arHtmlOptions, 'mask', null, true );
				if ( ! empty( $_sMask ) ) $_oMask = CPSjqMaskedInputWrapper::create( array( 'target' => '#' . $arHtmlOptions[ 'id' ], 'mask' => $_sMask ) );
				
				if ( ! isset( $arHtmlOptions[ 'size' ] ) )
					$arHtmlOptions[ 'size' ] = 60;
				break;
			
			//	WYSIWYG Plug-in
			case self::WYSIWYG:
				CPSWysiwygWidget::create( array_merge( $arWidgetOptions, array( 'autoRun' => true, 'id' => $_sId, 'name' => $_sName ) ) );
				$eFieldType = self::TEXTAREA;
				break;                                                                                
				
			//	CKEditor Plug-in
			case self::CKEDITOR:
				CPSCKEditorWidget::create( array_merge( $arWidgetOptions, array( 'autoRun' => true, 'target' => $arHtmlOptions[ 'id' ] ) ) );
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
				//	Auto-set prompt if not there...
				if ( ! isset( $arHtmlOptions[ 'noprompt' ] ) ) $arHtmlOptions[ 'prompt' ] = 'Select One...';
				//	Intentionally fall through to next block...
				
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
	public static function activeCodeDropDownList( $sAttribute, $sCodeType, &$arHtmlOptions = array(), $iDefaultUID = 0 )
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
		
		return self::tag( 'select', $arHtmlOptions, $_sOptions );
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
\$("{$sSelector}").addClass("{$_sOffClass}");\$("{$sSelector}").focus(function(){\$(this).addClass("{$_sOnClass}").removeClass("{$_sOffClass}");}).blur(function(){\$(this).removeClass("{$_sOnClass}").addClass("{$_sOffClass}");});
CODE;
	
		//	Register script
		Yii::app()->getClientScript()->registerScript( md5( time() ), $_sScript, CClientScript::POS_READY );
		
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
		Yii::app()->getClientScript()->registerCSS( md5( time() ), $_sCss );
	}
	
	public static function dropDown( $eType, $sName, $sLabel = null, $arOptions = array() )
	{
		$_sValue = PS::o( $arOptions, 'value', null, true );
		$_sLabelClass = PS::o( $arOptions, 'labelClass', null, true );

		if ( $sLabel ) $_sOut = self::label( $sLabel, $sName, $arOptions );
		
		switch ( $eType )
		{
			case self::DD_GENERIC:	//	Options passed in via array
				$_arOptions = PS::o( $arOptions, 'options', array(), true );
				break;
				
			case self::DD_US_STATES:
				$_arOptions = require( 'us_state_array.php' );
				break;
				
			case self::DD_MONTH_NUMBERS:
				if ( $_sValue == null ) $_sValue = date( 'm' );
				$_arOptions = require( 'month_numbers_array.php' );
				break;
				
			case self::DD_DAY_NUMBERS:
				if ( $_sValue == null ) $_sValue = date( 'd' );
				$_arOptions = require( 'day_numbers_array.php' );
				break;
				
			case self::DD_MONTH_NAMES:
				if ( $_sValue == null ) $_sValue = date( 'm' );
				$_arOptions = require( 'month_names_array.php' );
				break;
				
			case self::DD_YEARS:
				if ( $_sValue == null ) $_sValue = date( 'Y' );
				$_iRange = PS::o( $arOptions, 'range', 5 );
				$_iRangeStart = PS::o( $arOptions, 'rangeStart', date('Y'), true );
				$_arOptions = array();
				
				for ( $_i = 0; $_i < $_iRange; $_i++ ) 
					$_arOptions[ ( $_iRangeStart + $_i ) ] = ( $_iRangeStart + $_i );
				break;
				
			case self::DD_CC_TYPES:
				$_arOptions = require( 'cc_types_array.php' );
				break;
				
			default:
				return false;
		}
		
		if ( ! empty( $_arOptions ) )
		{
			$_sInner = '';

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
		if ( PS::o( $arHtmlOptions, 'validate', false, true ) )
		{
			self::$m_bValidating = true;
			$_arValidateOptions = PS::o( $arHtmlOptions, 'validateOptions', array(), true );
			if ( ! isset( $_arValidateOptions['target'] ) ) $_arValidateOptions['target'] = self::getFormSelector( $arHtmlOptions );
			CPSjqValidate::create( $_arValidateOptions );
		}
		
		if ( PS::o( $arHtmlOptions, 'selectmenu', false, true ) )
			CPSjqSelectMenu::create( array( 'target' => self::getFormSelector( $arHtmlOptions ) ) );
		
		return parent::beginForm( $sAction, $sMethod, $arHtmlOptions );
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
		return self::tag( 'label', $arOptions, ( ( $sLabel == null ) ? $sName : $sLabel ) . self::$labelSuffix . self::$afterRequiredLabel );
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
		return self::button($label,$htmlOptions);
	}

	/**
	 * Generates a submit button bar.
	 * @param string the button label
	 * @param array additional HTML attributes. Besides normal HTML attributes, a few special
	 * attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
	 * @return string the generated button tag
	 * @see clientChange
	 */
	public static function submitButtonBar( $sLabel = 'Submit', $arHtmlOptions = array() )
	{
		return '<div class="ps-submit-button-bar">' . self::submitButton( $sLabel, $arHtmlOptions ) . '<hr /></div>';
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

		$_sId = PS::o( $arOptions, 'id', CPSHelp::getWidgetId( self::ID_PREFIX . '.jqbtn' ), true );
		$_sFormId = PS::o( $arOptions, 'formId', null, true );

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
				$_sForm = ( $_sFormId ) ? "document.getElementById(\"{$_sFormId}\")" : 'this.form';
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
					Yii::app()->clientScript->registerScript( CPSHelp::getWidgetId( self::ID_PREFIX . '.cas.' ), $_sScript, CClientScript::POS_END );
					CPSjqUIAlerts::create( array( 'theme_' => CPSjqUIAlerts::getCurrentTheme() ) );
					$_bRegistered = true;
				}
			}
			else
			{
				if ( $_bSubmit || $_sLink == '_submit_' )
				{
					$_sLink = '#';
					$_sOnClick = "return \$(" . ( $_sFormId ? "'#{$_sFormId}'" : "'div.yiiForm>form'" ) . ").submit();";
				}
			}
		}
		
		//	Set our link options
		$arOptions['id'] = $_sId;
		$arOptions['title'] = $sLabel;
		$_sClass = PS::o( $arOptions, 'class', null );
		$arOptions['class'] = "ps-button {$_sIconPos} ui-state-default ui-corner-all {$_sClass}";
		if ( $_sOnClick ) $arOptions['onclick'] = $_sOnClick;
	
		//	Generate the link
		return self::link( ( $_sIcon . $sLabel ), $_sLink, $arOptions );
	}

	/**
	* If value is !set||empty, default is returned
	* 
	* @param mixed $oVal
	* @param mixed $oDefault
	* @deprecated Moved to CPSHelp.php
	*/
	public static function nvl( $oVal, $oDefault = null )
	{
		if ( isset( $oVal ) && ! empty($oVal) ) return $oVal;
		return $oDefault;
	}
	
	/**
	* Formats the created and modified dates 
	* 
	* @param mixed $oModel
	*/
	public static function showDates( $oModel, $sCreatedColumn = 'created', $sModifiedColumn = 'modified', $sDateFormat = 'm/d/Y h:i:s A' )
	{
		$_sOut = null;
		
		if ( $oModel->hasAttribute( $sCreatedColumn ) && $oModel->hasAttribute( $sModifiedColumn ) )
		{
			$_sOut = '<div class="ps-form-footer">';
			$_sOut .= '<span><strong>Created:</strong>&nbsp;' . date( $sDateFormat, ! is_numeric( $oModel->$sCreatedColumn ) ? strtotime( $oModel->$sCreatedColumn ) : $oModel->$sCreatedColumn ) . '</span><span class="ps-pipe">/</span><span><strong>Modified:</strong>&nbsp;' . date( $sDateFormat, ! is_numeric( $oModel->$sModifiedColumn ) ? strtotime( $oModel->$sModifiedColumn ) : $oModel->$sModifiedColumn ) . '</span>';
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
			Yii::app()->getClientScript()->registerScript( 'ib#' . self::ID_PREFIX . self::$count++, $_sScript, CClientScript::POS_READY );
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
			Yii::app()->getClientScript()->registerScript( 'ib#' . self::ID_PREFIX . self::$count++, $_sScript, CClientScript::POS_READY );
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
	* 
	* @param string $sWhich
	*/
	public static function flashMessage( $sWhich = 'success' )
	{
		if ( Yii::app()->user->hasFlash( $sWhich ) ) 
		{
			Yii::app()->clientScript->registerScript( 'psFlashDisplay', '$(".ps-flash-display").animate({opacity: 1.0}, 3000).fadeOut();', CClientScript::POS_READY );
			return self::tag( 'div', array( 'class' => 'ps-flash-display' ), Yii::app()->user->getFlash( $sWhich ) );
		}
	}
}

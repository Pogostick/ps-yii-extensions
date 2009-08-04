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

	//	Types of drop downs...
	const	DD_GENERIC = -1;
	const	DD_US_STATES = 0;
	const	DD_MONTH_NUMBERS = 1;
	const	DD_MONTH_NAMES = 2;
	const	DD_YEARS = 3;
	const	DD_CC_TYPES = 4;
	
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
	
	private static $m_arInputMap = array( 
		self::TEXTAREA => 'textarea',
		self::TEXT => 'text',
		self::HIDDEN => 'hidden',
		self::PASSWORD => 'password',
		self::FILE => 'file',
		self::RADIO => 'radio',
		self::CHECK => 'checkbox',
	);
	
	public static $useIdPrefixes = false;
	public static $useNamePrefixes = false;
	
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

	public static $namePrefixes = array();
	public static $requiredHtml = null;
	public static $labelSuffix = ':';
	public static $formFieldContainer = 'div';
	public static $formFieldContainerClass = 'input-holder';

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
		$_sHtml = CPSHelp::getOption( $arOptions, '_appendHtml', '', true );
		
		//	Need an id for div tag
		if ( ! isset( $arOptions[ 'id' ] ) ) $arOptions[ 'id' ] = self::getIdByName( self::resolveName( $oModel, $sColName ) );
		
		//	Preset class for hover effects if enabled...
		if ( isset( self::$m_sOffClass ) && ! isset( $arOptions[ 'class' ] ) ) $arOptions[ 'class' ] = self::$m_sOffClass;

		if ( null == $oModel )		
			$_sOut = self::label( $sLabel, $sName, $arLabelOptions );
		else
			$_sOut = self::activeLabelEx( $oModel, ( null == $sLabel ) ? $sColName : $sLabel, $arLabelOptions );
			
		$_sOut .= self::activeField( $eFieldType, $oModel, $sColName, $arOptions, $arWidgetOptions, $arData );
		$_sOut .= $_sHtml;

		//	Construct the div...
		$_sOut = '<div id="PIF_' . $arOptions[ 'id' ] . '" class="simple">' . $_sOut . '</div>';

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
		$_sAppendHtml = CPSHelp::getOption( $arHtmlOptions, '_appendHtml', '', true );
		
		//	Auto set id and name if they aren't already...
		if ( ! isset( $arHtmlOptions[ 'name' ] ) ) $arHtmlOptions[ 'name' ] = ( null != $oModel ) ? self::resolveName( $oModel, $sColName ) : $sColName;
		if ( ! isset( $arHtmlOptions[ 'id' ] ) ) $arHtmlOptions[ 'id' ] = self::getIdByName( $arHtmlOptions[ 'name' ] );
		
		if ( null == $oModel )
		{
			$_oValue = CPSHelp::getOption( $arHtmlOptions, 'value', null, true );
			
			//	Handle special types...
			switch ( $eFieldType )
			{
				default:
					$_sType = CPSHelp::getOption( self::$m_arInputMap, $eFieldType );
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
			}
			
			if ( null != $_sType )
				return self::inputField( $_sType, $sColName, $_oValue, $arHtmlOptions );
				
			return;
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
			
			//	Default for text field
			case self::TEXT:
				//	Masked input?
				$_sMask = CPSHelp::getOption( $arHtmlOptions, 'mask', null, true );
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
			case self::WYSIWYG:
				CPSCKEditorWidget::create( array_merge( $arWidgetOptions, array( 'autoRun' => true, 'id' => $_sId, 'name' => $_sName ) ) );
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
		
		return CHtml::tag( 'select', $arHtmlOptions, $_sOptions );
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
		$_sOffClass = self::$m_sOffClass = CPSHelp::getOption( $arOptions, 'offClass', 'idle' );
		$_sOnClass = self::$m_sOnClass = CPSHelp::getOption( $arOptions, 'onClass', 'activeField' );
		$_sOffBGColor = CPSHelp::getOption( $arOptions, 'offBackgroundColor', '#ffffff' );
		$_sOnBGColor = CPSHelp::getOption( $arOptions, 'onBackgroundColor', '#ffffff' );
		$_sOnBorderColor = CPSHelp::getOption( $arOptions, 'onBorderColor', '#33677F' );
		$_iOnBorderSize = CPSHelp::getOption( $arOptions, 'onBorderSize', 1 );
		$_sOffBorderColor = CPSHelp::getOption( $arOptions, 'offBorderColor', '#85b1de' );
		$_iOffBorderSize = CPSHelp::getOption( $arOptions, 'offBorderSize', 1 );
		
		//	Optional background image for non-hovered field
		$_sFieldImageUrl = CPSHelp::getOption( $arOptions, 'fieldImageUrl' );
		$_sFieldImageRepeat = CPSHelp::getOption( $arOptions, 'fieldImageRepeat', 'repeat-x' );
		$_sFieldImagePosition = CPSHelp::getOption( $arOptions, 'fieldImagePosition', 'top' );
		
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
	
	public static function beginFieldset( $sLegend = null, array $arHtmlOptions = array() )
	{
		$_sOut = '<FIELDSET>';
		if ( $sLegend ) $_sOut .= CHtml::tag( 'legend', $arHtmlOptions, $sLegend );
		return $_sOut;
	}
	
	public static function endFieldSet()
	{
		return '</FIELDSET>';
	}
	
	public static function dropDown( $eType, $sName, $sLabel = null, $arOptions = array() )
	{
		$_sValue = CPSHelp::getOption( $arOptions, 'value', null, true );
		$_sLabelClass = CPSHelp::getOption( $arOptions, 'labelClass', null, true );

		$_sOut = self::textLabel( $sName, $sLabel, array( 'for' => $sName, '_forType' => 'select', 'class' => $_sLabelClass ) );
		
		switch ( $eType )
		{
			case self::DD_GENERIC:	//	Options passed in via array
				$_arOptions = CPSHelp::getOption( $arOptions, 'options', array(), true );
				break;
				
			case self::DD_US_STATES:
				$_arOptions = require( 'us_state_array.php' );
				break;
				
			case self::DD_MONTH_NUMBERS:
				if ( $_sValue == null ) $_sValue = date( 'm' );
				$_arOptions = require( 'month_numbers_array.php' );
				break;
				
			case self::DD_MONTH_NAMES:
				if ( $_sValue == null ) $_sValue = date( 'm' );
				$_arOptions = require( 'month_names_array.php' );
				break;
				
			case self::DD_YEARS:
				if ( $_sValue == null ) $_sValue = date( 'Y' );
				$_iRange = CPSHelp::getOption( $arOptions, 'range', 5 );
				$_arOptions = array();
				
				for ( $_i = 0; $_i < $_iRange; $_i++ ) 
					$_arOptions[ ( date( 'Y' ) + $_i ) ] = ( date( 'Y' ) + $_i );
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
				$_sInner .= self::tag( 'option', null, $_sVal, $_arOpts );
			}

			$_sOut .= self::tag( 'SELECT', $sName, $_sInner, $arOptions );
		}
				
		return $_sOut;
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
		$_sType = CPSHelp::getOption( $arOptions, '_forType', 'text' );
		$_bRequired = CPSHelp::getOption( $arOptions, '_required', false );
		$arOptions[ 'id' ] = self::getIdPrefix( 'label' ) . $sName;
		return self::tag( 'label', self::getIdPrefix( 'label' ) . $sName, ( ( $sLabel == null ) ? $sName : $sLabel ) . self::getLabelSuffix() . self::getRequiredHtml( $bRequired ), $arOptions );
	}

	public static function getIdPrefix( $sType )
	{
		return ( self::$useIdPrefixes && is_array( self::$idPrefixes ) && self::nvl( self::$idPrefixes[ $sType ] ) ) ? self::$idPrefixes[ $sType ] : null;
	}

	public static function getNamePrefix( $sType )
	{
		return ( self::$useNamePrefixes && is_array( self::$namePrefixes ) && self::nvl( self::$namePrefixes[ $sType ] )  ) ? self::$namePrefixes[ $sType ] : null;
	}
	
	public static function getRequiredHtml()
	{
		return self::nvl( self::$requiredHtml );
	}
	
	public static function getLabelSuffix()
	{
		return self::nvl( self::$labelSuffix );
	}
	
	public static function nvl( $oVal, $oDefault = null )
	{
		if ( isset( $oVal ) && $oVal ) return $oVal;
		return $oDefault;
	}
	
}

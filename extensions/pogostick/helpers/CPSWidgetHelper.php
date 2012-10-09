<?php
/**
 * This file is part of the psYiiExtensions package.
 *
 * @copyright                           Copyright (c) 2010-2012 Pogostick, LLC
 * @link                                http://www.pogostick.com Pogostick, LLC.
 * @license                             http://www.pogostick.com/licensing
 * @author                              Jerry Ablan <jablan@pogostick.com>
 * @filesource
 */
/**
 * A collection of helper methods that augment CHtml.
 *
 * @property string      $codeModel          The name of the code model for code lookups
 * @property string      $hintTemplate       The template for displaying hints
 * @property string      $blockClass         The class in which to wrap label/input pairs.
 * @property-read string $idPrefix           The id prefix to use
 * @property-read string $namePrefix         The name prefix to use
 * @property-read string $currentFormId      The current form's id
 * @property-read string $lastFieldId        The id of the last generated form field
 * @property-read string $lastFieldName      The name of the last generated form field
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
	const STD_JQUI_FORM_CONTAINER_CLASS = 'ui-edit-container ui-widget form';
	const STD_FORM_CONTAINER_CLASS = 'ps-edit-form';
	const STD_BOOTSTRAP_FORM_CONTAINER_CLASS = 'bootstrap2-edit-form';
	const STD_BOOTSTRAP2_FORM_CONTAINER_CLASS = 'bootstrap2-edit-form';
	const STD_BOOTSTRAP2_FORM_CLASS = 'form-horizontal';
	const STD_FORM_CLASS = 'yiiForm';

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
	const CKEDITOR = 'CPSCKEditorWidget';
	const MARKITUP = 'markItUp';
	const CODEDD = 'activeCodeDropDownList';
	const DATADD = 'activeDataDropDownList';
	const JQUI = 'CPSjqUIWrapper';
	const FG_MENU = 'CPSfgMenu';
	const CAPTCHA = 'CCaptcha';
	const LABEL = 'label';
	const LABEL_ACTIVE = 'activeLabel';
	const MULTISELECT = 'jqMultiselect';
	const MCDROPDOWN = 'mcDropdown';

	/**
	 * Faux methods for transformation types
	 */
	const CODE_DISPLAY = 'inactiveCodeDisplay'; //	Not a real method, just a placeholder
	const TEXT_DISPLAY = 'inactiveTextDisplay'; //	Not a real method, just a placeholder

	/**
	 * Available UI styles
	 */
	const UI_DEFAULT = 0;
	const UI_JQUERY = 1;
	const UI_BOOTSTRAP = 3;
	const UI_BOOTSTRAP1 = 3;
	const UI_BOOTSTRAP2 = 3;

	/**
	 * Available built-in drop-down lists
	 */
	const DD_GENERIC = 9999;
	const DD_US_STATES = 1000;
	const DD_MONTH_NUMBERS = 1001;
	const DD_MONTH_NAMES = 1002;
	const DD_YEARS = 1003;
	const DD_CC_TYPES = 1004;
	const DD_DAY_NUMBERS = 1005;
	const DD_YES_NO = 1006;
	const DD_TIME_ZONES = 1007;
	const DD_YES_NO_ALL = 1008;
	const DD_JQUI_THEMES = 1009;

	/**
	 * Database-driven drop-down list
	 */
	const DD_CODE_TABLE = 'activeCodeDropDownList';
	const DD_DATA_LOOKUP = 'activeDataDropDownList';

	/**
	 * Types of document headers
	 */
	const HTML = 0;
	const XHTML = 1;
	const STRICT = 2;
	const FRAMESET = 4;
	const TRANSITIONAL = 8;
	const HTML32 = -1;
	const HTML20 = -2;
	const LOOSE = -3;

	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	/**
	 * @var bool
	 */
	protected static $_showRequiredLabel = true;
	/**
	 * An id counter for generating unique ids
	 *
	 * @var integer
	 */
	protected static $_idCounter = 0;
	/**
	 * Maps normal form fields to an input type
	 *
	 * @var array
	 */
	protected static $_inputMap
		= array(
			self::TEXTAREA => 'textarea',
			self::TEXT     => 'text',
			self::HIDDEN   => 'hidden',
			self::PASSWORD => 'password',
			self::FILE     => 'file',
			self::RADIO    => 'radio',
			self::CHECK    => 'checkbox',
		);
	/**
	 * Whether or not jQuery Validate is being used...
	 *
	 * @var boolean
	 */
	protected static $_validating = false;
	/**
	 * Whether or not we are we have built a container
	 *
	 * @var boolean
	 */
	protected static $_inForm = false;
	/**
	 * The default class for active fields when input hover is enabled
	 *
	 * @var string
	 */
	protected static $_onClass = null;
	/**
	 * The default class for inactive fields when input hover is enabled
	 *
	 * @var string
	 */
	protected static $_offClass = null;
	/**
	 * Tracks the current form id...
	 *
	 * @var string
	 */
	protected static $_currentFormId = null;
	/**
	 * The id of the last field that was generated
	 *
	 * @var string
	 */
	protected static $_lastFieldId = null;
	/**
	 * The name of the last field that was generated
	 *
	 * @var string
	 */
	protected static $_lastFieldName = null;
	/**
	 * Did we load our CSS yet?
	 *
	 * @var boolean
	 */
	protected static $_cssLoaded = false;
	/**
	 * @var int
	 */
	protected static $_uiStyle = self::UI_DEFAULT;
	/**
	 * The name of the code model for automated code dropdown lists
	 *
	 * @var string
	 */
	protected static $_codeModel = null;
	/**
	 * Template for hints. They will be displayed right after the div simple/complex tag.
	 * %%HINT%% will be replaced with your hint text.
	 *
	 * @var string
	 */
	protected static $_hintTemplate = '<p class="hint">%%HINT%%</p>';
	/**
	 * Whether or not to use the id prefix. Defaults to false.
	 *
	 * @var boolean
	 */
	protected static $_useIdPrefixes = false;
	/**
	 * The id prefixes to use.
	 *
	 * @var array
	 */
	protected static $_idPrefixes
		= array(
			'text'     => 'txt_',
			'password' => 'txt_',
			'textarea' => 'txt_',
			'radio'    => 'radio_',
			'check'    => 'check_',
			'label'    => 'label_',
			'select'   => 'slt_',
			'file'     => 'file_',
		);
	/**
	 * Whether or not to use the name prefix. Defaults to false.
	 *
	 * @var boolean
	 */
	protected static $_useNamePrefixes = false;
	/**
	 * The name prefixes to use.
	 *
	 * @var string
	 */
	protected static $_namePrefixes = array();
	/**
	 * The HTML for required elements. Defaults to null
	 *
	 * @var string
	 */
	protected static $_requiredHtml = null;
	/**
	 * The suffix for label elements. Appended to labels. Defaults to ':'.
	 *
	 * @var string
	 */
	protected static $_labelSuffix = ':';
	/**
	 * The container tag for form fields
	 *
	 * @var string
	 */
	protected static $_formFieldContainer = 'div';
	/**
	 * The css class for the container tag for form fields
	 *
	 * @var string
	 */
	protected static $_formFieldContainerClass = 'simple';
	/**
	 * The prefix for form field containers generated by this library. Defaults to 'PIF'
	 *
	 * @var string
	 */
	protected static $_formFieldContainerPrefix = 'PIF';

	/**
	 * @var string
	 */
	protected static $_requiredLabel = '*';

	//********************************************************************************
	//* Public methods
	//********************************************************************************
	/**
	 * @static
	 *
	 * @param string $type
	 *
	 * @return mixed|null
	 */
	public static function getIdPrefix( $type = 'text' )
	{
		return self::$_useIdPrefixes ? PS::o( self::$_idPrefixes, $type ) : null;
	}

	/**
	 * @static
	 * @return int
	 */
	public static function getNextIdCount()
	{
		return self::$_idCounter++;
	}

	/**
	 * Generate a random ID # for a widget
	 *
	 * @param string $prefix
	 *
	 * @return string
	 */
	public static function getWidgetId( $prefix = self::ID_PREFIX )
	{
		return $prefix . self::getNextIdCount();
	}

	/**
	 * Creates a form field
	 *
	 * $options may contain any HTML options for the field. Special values are:
	 *
	 * label Label to display
	 * labelOptions Options for the label tag
	 * data Data to pass to the field (i.e. select options array)
	 * widgetOptions Options for the widget
	 * content Optional content for field. If not specified, it's generated
	 *
	 * @param int    $inputFieldType
	 * @param CModel $model
	 * @param string $attributeName
	 * @param array  $options
	 *
	 * @return string
	 * @static
	 * @access public
	 */
	public static function field( $inputFieldType, CModel $model, $attributeName, $options = array() )
	{
		$_divOptions = PS::o( $options, '_divOpts', array(), true );
		$_hint = PS::o( $options, 'hint', null, true );

		//	A little switcheroo...
		if ( self::CODE_DISPLAY == $inputFieldType )
		{
			$inputFieldType = PS::TEXT_DISPLAY;
			$options['transform'] = '*';
			$options['name'] = $options['id'] = 'disp_' . self::getNextIdCount() . '_' . $attributeName;
			$options['class'] = PS::addClass( $options['class'], 'ps-text-display' );
			$_output = PS::activeHiddenField( $model, $attributeName );
			$_output .= PS::field( $inputFieldType, $model, $attributeName, $options );
		}
		else
		{
			if ( PS::MULTISELECT == $inputFieldType )
			{
				//	Another special dealio
				$widgetOptions = PS::o( $options, 'widgetOptions', array(), true );
				$widgetOptions['name'] = PS::o( $options, 'name', $attributeName, true );
				$widgetOptions['id'] = PS::o( $options, 'id', $widgetOptions['name'], true );
				$widgetOptions['naked'] = true;
				$widgetOptions['extraScriptFiles'] = array(
					'js/plugins/blockUI/jquery.blockUI.js',
					'js/plugins/localisation/jquery.localisation-min.js',
					'js/plugins/tmpl/jquery.tmpl.1.1.1.js'
				);

				$_selectedItem = PS::o( $options, 'selected', null, true );

				CPSjQueryWidget::create( 'multiselect', $widgetOptions );
				$options['class'] = PS::addClass( PS::o( $options, 'class', null ), 'multiselect' );
				$options['multiple'] = 'multiple';

				return self::dropDownList( $attributeName, $_selectedItem, PS::o( $options, 'data', null, true ), $options );
			}
			else
			{
				//	Get our operating parameters
				$_label = PS::o( $options, 'label', null, true );
				$_labelOptions = PS::o( $options, 'labelOptions', array(), true );

				if ( PS::TEXTAREA == $inputFieldType )
				{
					$_labelOptions['style'] = PS::o( $_labelOptions, 'style' ) . 'vertical-align:top;';
				}

				$_suffixToUse = PS::o( $_labelOptions, 'noSuffix', false, true ) ? '' : self::$_labelSuffix;
				$_widgetOptions = PS::o( $options, 'widgetOptions', array(), true );
				$_data = PS::o( $options, 'data', array(), true );
				$_sHtml = PS::o( $options, '_appendHtml', null, true );
				$_prependHtml = PS::o( $options, '_prependHtml', null, true );
				$_divClass = PS::o( $options, '_divClass', null, true );
				$_transform = PS::o( $options, 'transform', null, true );
				$_title = PS::o( $options, 'title', null );
				$_valueMap = PS::o( $options, 'valueMap', array(), true );
				$_content = PS::o( $options, 'content', null, true );

				//	Value map...
				if ( in_array( $model->{$attributeName}, array_keys( $_valueMap ) ) && isset( $_valueMap[$model->{$attributeName}] ) )
				{
					$options['value'] = $_valueMap[$model->{$attributeName}];
				}

				//	Do auto-tooltipping...
				if ( !$_title && $model && method_exists( $model, 'attributeTooltips' ) )
				{
					if ( null !== ( $_temp = $model->attributeTooltips() ) )
					{
						if ( isset( $_temp[$attributeName] ) )
						{
							$options['title'] = self::encode( $_temp[$attributeName] );
						}
					}
				}

				//	Denote checkbox/radio button labels
				if ( !$_divClass && PS::in( $inputFieldType, self::CHECK, self::RADIO, self::CHECKLIST, self::RADIOLIST ) )
				{
					$_divClass = 'chk_label';
				}

				//	Need an id for div tag
				if ( !isset( $options['id'] ) )
				{
					$options['id'] = self::getIdByName( self::resolveName( $model, $attributeName ) );
				}

				//	Preset class for hover effects if enabled...
				if ( isset( self::$_offClass ) && !isset( $options['class'] ) )
				{
					$options['class'] = self::$_offClass;
				}

				if ( null == $model )
				{
					$_output = self::label( $_label, $options['id'], $_labelOptions );
				}
				else
				{
					//	Set label name
					if ( $_label === false )
					{
						$_labelOptions['label'] = false;
					}
					else
					{
						$_labelOptions['label']
							= PS::nvl( $_label, PS::nvl( $model->getAttributeLabel( $attributeName ), $attributeName ) ) . $_suffixToUse;
					}

					if ( self::UI_BOOTSTRAP == self::$_uiStyle )
					{
						$_labelOptions['class'] = PS::o( $_labelOptions, 'class', 'control-label' );
					}

					$_output = ( $inputFieldType == PS::TEXT_DISPLAY
						? self::activeLabel( $model, $attributeName, $_labelOptions )
						:
						self::activeLabelEx( $model, $attributeName, $_labelOptions ) );
				}

				//	Do a value transform if requested
				if ( $_transform && $model )
				{
					$model->{$attributeName} = CPSTransform::valueOf( $_transform, $model->$attributeName );
				}

				//	Build our field
				$_markup = ( null !== $_content )
					? $_content
					:
					self::activeField( $inputFieldType, $model, $attributeName, $options, $_widgetOptions, $_data );

				//	Wrap bootstrap controls in a div
				$_controls = PS::in( self::getUiStyle(), self::UI_BOOTSTRAP, self::UI_BOOTSTRAP1, self::UI_BOOTSTRAP2 );

				$_output .= $_prependHtml . ( $_controls ? '<div class="controls">' : null ) . $_markup . $_sHtml . ( $_controls ? '</div>' : null );

				$_divOptions['id'] = self::$_formFieldContainerPrefix . '_' . PS::nvl( PS::o( $_divOptions, 'id' ), $options['id'] );
				$_divOptions['class'] = PS::addClass( $_divClass, PS::$_formFieldContainerClass . ' ' . PS::o( $_divOptions, 'class' ) );

				if ( self::UI_BOOTSTRAP == self::$_uiStyle && $model->getError( $attributeName ) )
				{
					$_divOptions['class'] = PS::addClass( $_divOptions['class'], 'error' );
				}
			}
		}

		//	Any hints?
		if ( $_hint )
		{
			$_hint = str_ireplace( '%%HINT%%', $_hint, self::$_hintTemplate );
		}

		return PS::tag( self::$_formFieldContainer, $_divOptions, $_output . $_hint );
	}

	/**
	 * Adds a simple div block with label and field
	 *
	 * If $label is null, $attributeName is used as your label name
	 *
	 * @param string $inputFieldType One of the * constants
	 * @param CModel $model          The model for this form
	 * @param string $attributeName  The column/attribute name
	 * @param array  $options        The htmlOptions for the field
	 * @param string $label          The real name of the attribute if different
	 * @param array  $arLabelOptions The htmlOptions for the label
	 * @param array  $listData       Any data necessary for the field (i.e. drop down data)
	 *
	 * @param array  $widgetOptions
	 *
	 * @return string
	 * @deprecated
	 */
	public static function simpleActiveBlock(
		$inputFieldType, $model, $attributeName, $options = array(), $label = null,
		$arLabelOptions = array(), $listData = null, $widgetOptions = array()
	)
	{
		return self::field(
			$inputFieldType,
			$model,
			$attributeName,
			array_merge(
				$options,
				array(
					'label'         => $label,
					'labelOptions'  => $arLabelOptions,
					'data'          => $listData,
					'widgetOptions' => $widgetOptions,
				)
			)
		);
	}

	/**
	 * Adds an activefield to a form
	 *
	 * There are two special options you can use in $htmlOptions:
	 *
	 * _appendHtml -- Extra Html code/scripts to be inserted AFTER the form element has been created
	 * _widget -- The name of the jQuery UI widget to create when type = self::JQUI
	 *
	 * @param string $inputFieldType One of the * constants
	 * @param CModel $model          The model for this form
	 * @param string $attributeName  The column/attribute name
	 * @param array  $htmlOptions    The htmlOptions for the field
	 * @param array  $widgetOptions  The widget options for the field
	 * @param array  $listData       Any data necessary for the field (i.e. drop down data)
	 *
	 * @throws Exception
	 * @return string
	 */
	public static function activeField( $inputFieldType, $model, $attributeName, $htmlOptions = array(), $widgetOptions = array(), $listData = null )
	{
		//	Auto set id and name if they aren't already...
		if ( !isset( $htmlOptions['name'] ) )
		{
			$htmlOptions['name'] = ( null != $model ) ? self::resolveName( $model, $attributeName ) : $attributeName;
		}
		if ( !isset( $htmlOptions['id'] ) )
		{
			$htmlOptions['id'] = self::getIdByName( $htmlOptions['name'] );
		}

		//	Save for callers...
		self::$_lastFieldName = $htmlOptions['name'];
		self::$_lastFieldId = $htmlOptions['id'];

		//	Non-model field?
		if ( null === $model )
		{
			return self::inactiveField( $inputFieldType, $attributeName, $htmlOptions, $widgetOptions, $listData );
		}

		//	Stuff to put after widget
		$_beforeHtml = null;
		$_appendHtml = PS::o( $htmlOptions, '_appendHtml', '', true );

		//	Are we validating this form? Add required tags automagically
		if ( self::$_validating )
		{
			//	Get any additional params for validation
			$_sClass = PS::o( $htmlOptions, '_validate', null, true );
			$_isRequired = ( false !== stripos( PS::o( $htmlOptions, 'class' ), 'required' ) );

			if ( !$_isRequired && $model->isAttributeRequired( $attributeName ) )
			{
				PS::addClass( $_sClass, 'required' );
			}

			if ( !$_isRequired && $model->isAttributeRequired( $attributeName ) )
			{
				PS::addClass( $_sClass, 'required' );
				//				PS::addClass( $htmlOptions['labelOptions']['class'], 'required' );
				//				$htmlOptions['required'] = true;
				//				$htmlOptions['labelOptions']['required'] = true;
			}
			$htmlOptions['class'] = trim( PS::addClass( $_sClass, PS::o( $htmlOptions, 'class' ) ) );
		}

		//	Get our value...
		if ( $attributeName != ( $_cleanedAttribute = CPSTransform::cleanColumn( $attributeName ) ) )
		{
			//	Use our handy transformer...
			$attributeName = $_cleanedAttribute;
			$_value = CPSTransform::getValue( $model, $attributeName );
		}
		else
		{
			$_value = PS::o( $htmlOptions, 'value', $model->{$attributeName}, true );
		}

		//	Handle custom drop downs...
		if ( self::setDropDownValues( $inputFieldType, $htmlOptions, $listData, PS::nvl( $model->{$attributeName} ) ) )
		{
			$inputFieldType = self::DROPDOWN;
		}

		//	Handle special types...
		switch ( $inputFieldType )
		{
			case self::TEXT_DISPLAY:
				$htmlOptions['style'] = PS::o( $htmlOptions, 'style' ) . ' border:none; background-color: transparent;';
				$htmlOptions['class'] = PS::addClass( $htmlOptions['class'], 'ps-text-display' );
				$htmlOptions['readonly'] = 'readonly';

				if ( null !== PS::o( $htmlOptions, 'content' ) )
				{
					$htmlOptions['content'] = PS::tag( 'label', array( 'class' => 'ps-text-display', $_value ) );
				}

				$inputFieldType = self::TEXT;
				break;

			//	Build a jQuery UI widget
			case self::JQUI:
				if ( isset( $htmlOptions['_widget'] ) )
				{
					$widgetOptions['name'] = $htmlOptions['name'];
					$widgetOptions['id'] = $htmlOptions['id'];
					$_sWidget = $htmlOptions['_widget'];
					unset( $htmlOptions['_widget'] );
					CPSjqUIWrapper::create( $_sWidget, $widgetOptions );
					$inputFieldType = self::TEXT;
				}
				break;

			//	Build a McDropdown
			case self::MCDROPDOWN:
				$widgetOptions['linkText'] = false;
				$_target = PS::o( $widgetOptions, 'target', $htmlOptions['id'] );
				$_sTargetMenu = PS::o( $widgetOptions, 'targetMenu', $htmlOptions['id'] . '_menu' );
				$_sValueColumn = PS::o( $htmlOptions, 'valueColumn' );

				//	Insert text field...
				$_output = PS::textField( $htmlOptions['name'], $_value, array( 'id' => $htmlOptions['id'] ) );

				//	Create menu...
				$_output .= CPSTransform::asUnorderedList(
					$listData,
					array(
						'class'       => 'mcdropdown_menu',
						'valueColumn' => $_sValueColumn,
						'linkText'    => false,
						'id'          => $_sTargetMenu
					)
				);

				$widgetOptions['target'] = $_target;
				$widgetOptions['targetMenu'] = $_sTargetMenu;

				CPSMcDropdownWidget::create( null, $widgetOptions );

				return $_beforeHtml . $_output . $_appendHtml;

			//	Build a Filament Group menu
			case self::FG_MENU:
				ob_start();
				$widgetOptions['prompt'] = PS::o( $htmlOptions, 'prompt', 'Select One...', true );
				CPSfgMenu::create( null, $widgetOptions );
				$_output = ob_get_contents();
				ob_end_clean();

				return $_beforeHtml . $_output . $_appendHtml;

			//	Default for text field
			case self::TEXT:
				//	Masked input?
				$_sMask = PS::o( $htmlOptions, 'mask', null, true );
				if ( !empty( $_sMask ) )
				{
					$_oMask = CPSjqMaskedInputWrapper::create(
						null,
						array(
							'target' => '#' . $htmlOptions['id'],
							'mask'   => $_sMask
						)
					);
				}

				if ( !isset( $htmlOptions['size'] ) )
				{
					$htmlOptions['size'] = 60;
				}
				break;

			//	WYSIWYG Plug-in
			case self::WYSIWYG:
				CPSWysiwygWidget::create(
					null,
					array_merge(
						$widgetOptions,
						array(
							'autoRun' => true,
							'id'      => $htmlOptions['id'],
							'name'    => $htmlOptions['name']
						)
					)
				);
				$inputFieldType = self::TEXTAREA;
				break;

			//	CKEditor Plug-in
			case self::CKEDITOR:
				CPSCKEditorWidget::create(
					null,
					array_merge(
						$widgetOptions,
						array(
							'autoRun' => true,
							'target'  => $htmlOptions['id']
						)
					)
				);
				$inputFieldType = self::TEXTAREA;
				break;

			//	markItUp! Plug-in
			case self::MARKITUP:
				$widgetOptions['name'] = $htmlOptions['name'];
				$widgetOptions['id'] = $htmlOptions['id'];
				CPSMarkItUpWidget::create( null, $widgetOptions );
				$inputFieldType = self::TEXTAREA;
				break;

			case self::CAPTCHA:
				$htmlOptions['hint'] = 'Please enter the letters as they are shown in the image above.<br />Letters are not case-sensitive.';
				ob_start();
				echo PS::openTag( 'div', array( 'class' => 'ps-captcha-container' ) );
				Yii::app()->getController()->widget( self::CAPTCHA, $widgetOptions );
				echo PS::closeTag( 'div' );
				$_beforeHtml = ob_get_contents();
				ob_end_clean();
				$inputFieldType = self::TEXT;
				break;

			//	These guys need data in third parameter
			case self::DROPDOWN:
				//	Auto-set prompt if not there...
				if ( !isset( $htmlOptions['noprompt'] ) || false === PS::o( $htmlOptions, 'prompt', null ) )
				{
					$htmlOptions['prompt'] = PS::o( $htmlOptions, 'prompt', 'Select One...', true );
				}
			//	Intentionally fall through to next block...

			case self::CHECKLIST:
			case self::RADIOLIST:
			case self::LISTBOX:
				return self::$inputFieldType( $model, $attributeName, $listData, $htmlOptions );
		}

		$_fieldOutput = null;

		//		try
		{
			if ( defined( 'PYE_TRACE_LEVEL' ) && PYE_TRACE_LEVEL > 3 )
			{
//				CPSLog::trace( __METHOD__, 'Rendering field "' . $attributeName . '" of type "' . $inputFieldType . '"' );
			}

			if ( method_exists( __CLASS__, $inputFieldType ) )
			{
				$_fieldOutput = self::$inputFieldType( $model, $attributeName, $htmlOptions );
			}
			else
			{
				throw new Exception( 'Unknown input field type: ' . $inputFieldType );
			}
		}

		//		catch ( Exception $_ex )
		//		{
		//			CPSLog::error( __METHOD__, 'Error rendering field "' . $attributeName . '" of type "' . $inputFieldType . '": ' . $_ex->getMessage() );
		//		}

		return $_beforeHtml . $_fieldOutput . $_appendHtml;
	}

	/**
	 * Adds a non-model field to a form
	 *
	 * There are two special options you can use in $htmlOptions:
	 *
	 * _appendHtml -- Extra Html code/scripts to be inserted AFTER the form element has been created
	 * _widget -- The name of the jQuery UI widget to create when type = self::JQUI
	 *
	 * @param string $inputFieldType One of the * constants
	 * @param string $attributeName  The column/attribute name
	 * @param array  $htmlOptions    The htmlOptions for the field
	 * @param array  $widgetOptions  The widget options for the field
	 * @param array  $listData       Any data necessary for the field (i.e. drop down data)
	 *
	 * @return string
	 */
	public static function inactiveField( $inputFieldType, $attributeName, $htmlOptions = array(), $widgetOptions = array(), $listData = null )
	{
		//	Settings
		$_beforeHtml = null;
		$_appendHtml = PS::o( $htmlOptions, '_appendHtml', '', true );
		$_value = PS::o( $htmlOptions, 'value', null, true );

		//	Handle special types...
		switch ( $inputFieldType )
		{
			//	Build a jQuery UI widget
			case self::JQUI:
				if ( isset( $htmlOptions['_widget'] ) )
				{
					$widgetOptions['name'] = $htmlOptions['name'];
					$widgetOptions['id'] = $htmlOptions['id'];
					$_sWidget = $htmlOptions['_widget'];
					unset( $htmlOptions['_widget'] );
					CPSjqUIWrapper::create( $_sWidget, $widgetOptions );
					$_sType = 'text';
				}
				break;

			//	WYSIWYG Plug-in
			case self::WYSIWYG:
				CPSWysiwygWidget::create(
					null,
					array_merge(
						$widgetOptions,
						array(
							'autoRun' => true,
							'id'      => $htmlOptions['id'],
							'name'    => $htmlOptions['name']
						)
					)
				);
				$_sType = 'textarea';
				break;

			//	markItUp! Plug-in
			case self::MARKITUP:
				$widgetOptions['name'] = $htmlOptions['name'];
				$widgetOptions['id'] = $htmlOptions['id'];
				CPSMarkItUpWidget::create( null, $widgetOptions );
				$_sType = 'textarea';
				break;

			case self::TEXT_DISPLAY:
				$htmlOptions['style'] = PS::o( $htmlOptions, 'style' ) . ' border:none; background-color: transparent;';
				$htmlOptions['readonly'] = 'readonly';
				$_sType = 'text';
				break;

			default:
				$_sType = PS::o( self::$_inputMap, $inputFieldType );
				break;
		}

		//	Do drop downs...
		if ( null != ( $listData = self::setDropDownValues( $inputFieldType, $htmlOptions, $listData, $_value ) ) )
		{
			return parent::dropDownList( $attributeName, $_value, $listData, $htmlOptions );
		}

		//	Otherwise output the field if we have a type
		if ( null != $_sType )
		{
			return self::inputField( $_sType, $attributeName, $_value, $htmlOptions );
		}

		//	No clue...
		return;

		//	Are we validating this form? Add required tags automagically
		if ( self::$_validating )
		{
			//	Get any additional params for validation
			$_sClass = PS::o( $htmlOptions, '_validate', null, true );
			if ( $model->isAttributeRequired( $attributeName ) )
			{
				PS::addClass( $_sClass, 'required' );
			}
			$_sClass = ' ' . PS::o( $htmlOptions, 'class', null );
			$htmlOptions['class'] = trim( $_sClass );
		}

		//	Get our value...
		if ( $attributeName != ( $_cleanedAttribute = CPSTransform::cleanColumn( $attributeName ) ) )
		{
			//	Use our handy transformer...
			$attributeName = $_cleanedAttribute;
			$_value = CPSTransform::getValue( $model, $attributeName );
		}
		else
		{
			$_value = PS::o( $htmlOptions, 'value', $model->{$attributeName}, true );
		}

		//	Handle custom drop downs...
		if ( null !== self::setDropDownValues( $inputFieldType, $htmlOptions, $listData, PS::nvl( $model->{$attributeName} ) ) )
		{
			$inputFieldType = self::DROPDOWN;
		}

		//	Handle special types...
		switch ( $inputFieldType )
		{
			case self::TEXT_DISPLAY:
				$htmlOptions['style'] = PS::o( $htmlOptions, 'style' ) . ' border:none; background-color: transparent;';
				$inputFieldType = self::TEXT;
				break;

			//	Build a jQuery UI widget
			case self::JQUI:
				if ( isset( $htmlOptions['_widget'] ) )
				{
					$widgetOptions['name'] = $htmlOptions['name'];
					$widgetOptions['id'] = $htmlOptions['id'];
					$_sWidget = $htmlOptions['_widget'];
					unset( $htmlOptions['_widget'] );
					CPSjqUIWrapper::create( $_sWidget, $widgetOptions );
					$inputFieldType = self::TEXT;
				}
				break;

			//	Build a Filament Group menu
			case self::FG_MENU:
				ob_start();
				$widgetOptions['prompt'] = PS::o( $htmlOptions, 'prompt', 'Select One...', true );
				CPSfgMenu::create( null, $widgetOptions );
				$_output = ob_get_contents();
				ob_end_clean();

				return $_beforeHtml . $_output . $_appendHtml;

			//	Default for text field
			case self::TEXT:
				//	Masked input?
				$_sMask = PS::o( $htmlOptions, 'mask', null, true );
				if ( !empty( $_sMask ) )
				{
					$_oMask = CPSjqMaskedInputWrapper::create(
						null,
						array(
							'target' => '#' . $htmlOptions['id'],
							'mask'   => $_sMask
						)
					);
				}

				if ( !isset( $htmlOptions['size'] ) )
				{
					$htmlOptions['size'] = 60;
				}
				break;

			//	WYSIWYG Plug-in
			case self::WYSIWYG:
				CPSWysiwygWidget::create(
					null,
					array_merge(
						$widgetOptions,
						array(
							'autoRun' => true,
							'id'      => $_id,
							'name'    => $_name
						)
					)
				);
				$inputFieldType = self::TEXTAREA;
				break;

			//	CKEditor Plug-in
			case self::CKEDITOR:
				CPSCKEditorWidget::create(
					null,
					array_merge(
						$widgetOptions,
						array(
							'autoRun' => true,
							'target'  => $htmlOptions['id']
						)
					)
				);
				$inputFieldType = self::TEXTAREA;
				break;

			//	markItUp! Plug-in
			case self::MARKITUP:
				$widgetOptions['name'] = $htmlOptions['name'];
				$widgetOptions['id'] = $htmlOptions['id'];
				CPSMarkItUpWidget::create( null, $widgetOptions );
				$inputFieldType = self::TEXTAREA;
				break;

			case self::CAPTCHA:
				$options['hint'] = 'Please enter the letters as they are shown in the image above.<br />Letters are not case-sensitive.';
				ob_start();
				echo PS::openTag( 'div', array( 'class' => 'ps-captcha-container' ) );
				Yii::app()->getController()->widget( self::CAPTCHA, $widgetOptions );
				echo PS::closeTag( 'div' );
				$_beforeHtml = ob_get_contents();
				ob_end_clean();
				$inputFieldType = self::TEXT;
				break;

			//	These guys need data in third parameter
			case self::DROPDOWN:
				//	Auto-set prompt if not there...
				if ( !isset( $htmlOptions['noprompt'] ) )
				{
					$htmlOptions['prompt'] = PS::o( $htmlOptions, 'prompt', 'Select One...', true );
				}
			//	Intentionally fall through to next block...

			case self::CHECKLIST:
			case self::RADIOLIST:
			case self::LISTBOX:
				return self::$inputFieldType( $model, $attributeName, $listData, $htmlOptions );
		}

		return $_beforeHtml . self::$inputFieldType( $model, $attributeName, $htmlOptions ) . $_appendHtml;
	}

	/**
	 * Create a drop-down list filled with codes give a code type.
	 *
	 * @param CModel  $model
	 * @param string  $sAttribute
	 * @param array   $htmlOptions
	 * @param integer $iDefaultUID
	 *
	 * @return string
	 */
	public static function activeCodeDropDownList( $model, $sAttribute, &$htmlOptions = array(), $iDefaultUID = 0 )
	{
		if ( null != ( $_sCodeModel = PS::o( $htmlOptions, 'codeModel', self::$_codeModel, true ) ) )
		{
			$_model = new $_sCodeModel;

			if ( $_model instanceof CPSCodeTableModel )
			{
				$_sValType = strtoupper( PS::o( $htmlOptions, 'codeType', $sAttribute, true ) );
				$_sValAbbr = strtoupper( PS::o( $htmlOptions, 'codeAbbr', null, true ) );
				$_valueFilter = PS::o( $htmlOptions, 'valueFilter', null, true );
				$_model->setValueFilter( $_valueFilter );
				if ( !$_sValAbbr )
				{
					$_sValAbbr = PS::o( $htmlOptions, 'codeAbbreviation', null, true );
				}
				$_sValId = PS::o( $htmlOptions, 'codeId', null, true );
				$_sSort = PS::o( $htmlOptions, 'sortOrder', 'code_desc_text', true );
				$_arOptions = array();

				if ( $_sValId )
				{
					$_arOptions = self::listData( $_model->findById( $_sValId ), 'id', 'code_desc_text' );
				}
				elseif ( !$_sValAbbr )
				{
					$_arOptions = self::listData( $_model->findAllByType( $_sValType, $_sSort, $_valueFilter ), 'id', 'code_desc_text' );
				}
				elseif ( $_sValAbbr )
				{
					$_arOptions = self::listData( $_model->findAllByAbbreviation( $_sValAbbr, $_sValType, $_sSort ), 'id', 'code_desc_text' );
				}

				if ( isset( $htmlOptions['multiple'] ) )
				{
					if ( substr( $htmlOptions['name'], -2 ) !== '[]' )
					{
						$htmlOptions['name'] .= '[]';
					}
				}

				return self::activeDropDownList( $model, $sAttribute, $_arOptions, $htmlOptions );
			}
		}
	}

	/**
	 * Create a drop downlist filled with data from a table
	 *
	 * @param CModel  $model
	 * @param string  $sAttribute
	 * @param array   $htmlOptions
	 * @param integer $iDefaultUID
	 *
	 * @throws Exception
	 * @return string
	 */
	public static function activeDataDropDownList( $model, $sAttribute, &$htmlOptions = array(), $iDefaultUID = 0 )
	{
		if ( null != ( $_modelName = PS::o( $htmlOptions, 'dataModel', null, true ) ) )
		{
			$_model = new $_modelName();

			$_id = PS::o( $htmlOptions, 'dataId', 'id', true );
			$_name = PS::o( $htmlOptions, 'dataName', null, true );
			$_condition = PS::o( $htmlOptions, 'dataCondition', null, true );
			$_order = PS::o( $htmlOptions, 'dataOrder', null, true );

			if ( $_id && $_name )
			{
				try
				{
					$_arOptions = self::listData(
						$_model->findAll(
							array(
								'select'    => $_id . ', ' . $_name,
								'order'     => PS::nvl( $_order, $_name ),
								'condition' => $_condition
							)
						),
						$_id,
						$_name
					);

					if ( isset( $htmlOptions['multiple'] ) )
					{
						if ( substr( $htmlOptions['name'], -2 ) !== '[]' )
						{
							$htmlOptions['name'] .= '[]';
						}
					}

					return self::activeDropDownList( $model, $sAttribute, $_arOptions, $htmlOptions );
				}
				catch ( Exception $_ex )
				{
					CPSLog::error( __METHOD__, 'Exception pulling data: ' . $_ex->getMessage() );
					throw $_ex;
				}
			}
		}

		return null;
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
	 * @param string $selector  The jQuery/CSS selector(s) to apply effect to
	 * @param array  $options   Options for the generated scripts/CSS
	 *
	 * @access public
	 * @static
	 * @since  psYiiExtensions v1.0.5
	 */
	public static function enableInputHover( $selector, $options = array() )
	{
		$_tempCss = '';

		//	Basic options...
		$_offClass = self::$_offClass = PS::o( $options, 'offClass', 'idle' );
		$_onClass = self::$_onClass = PS::o( $options, 'onClass', 'activeField' );
		$_sOffBGColor = PS::o( $options, 'offBackgroundColor', '#ffffff' );
		$_sOnBGColor = PS::o( $options, 'onBackgroundColor', '#ffffff' );
		$_sOnBorderColor = PS::o( $options, 'onBorderColor', '#33677F' );
		$_iOnBorderSize = PS::o( $options, 'onBorderSize', 1 );
		$_sOffBorderColor = PS::o( $options, 'offBorderColor', '#85b1de' );
		$_iOffBorderSize = PS::o( $options, 'offBorderSize', 1 );

		//	Optional background image for non-hovered field
		$_sFieldImageUrl = PS::o( $options, 'fieldImageUrl' );
		$_sFieldImageRepeat = PS::o( $options, 'fieldImageRepeat', 'repeat-x' );
		$_sFieldImagePosition = PS::o( $options, 'fieldImagePosition', 'top' );

		//	Set up the cool input effects...
		$_script
			= <<<CODE
jQuery("{$selector}").addClass("{$_offClass}");jQuery("{$selector}").focus(function(){jQuery(this).addClass("{$_onClass}").removeClass("{$_offClass}");}).blur(function(){jQuery(this).removeClass("{$_onClass}").addClass("{$_offClass}");});
CODE;

		//	Register script
		PS::registerScript( md5( time() ), $_script );

		$_sCss
			= <<<CODE
{$selector} input[type="text"]:focus,
{$selector} input[type="password"]:focus,
{$selector} select:focus
{
	background-image: none;
	background-color: {$_sOnBGColor};
	border: solid {$_iOnBorderSize}px {$_sOnBorderColor};
}

.{$_onClass}
{
	background-image: none;
	background-color: {$_sOnBGColor};
	border: solid {$_iOffBorderSize}px {$_sOffBorderColor};
}

.{$_offClass}
{
	background-color: {$_sOffBGColor};
	border: solid {$_iOffBorderSize}px {$_sOffBorderColor};
CODE;

		if ( !empty( $_sFieldImageUrl ) )
		{
			$_tempCss
				= <<<CSS1
 	background-image: url({$_sFieldImageUrl});
	background-repeat: {$_sFieldImageRepeat};
	background-position {$_sFieldImagePosition};
CSS1;
		}

		//	Add anything else we've appended...
		$_sCss .= $_tempCss . "\n}";

		//	Register CSS
		PS::registerCss( md5( time() ), $_sCss );
	}

	/**
	 * Generates a generic drop-down select list
	 *
	 * @param enum   $type
	 * @param string $name
	 * @param string $label
	 * @param array  $options
	 *
	 * @return string|boolean
	 */
	public static function dropDown( $type, $name, $label = null, $options = array() )
	{
		$_output = null;
		$_sValue = PS::o( $options, 'value', null, true );
		$_labelClass = PS::o( $options, 'labelClass', null, true );

		if ( $label )
		{
			$_output = self::label( $label, $name, $options );
		}

		if ( null == ( $_arOptions = self::getGenericDropDownValues( $type, $options ) ) )
		{
			return false;
		}

		if ( !empty( $_arOptions ) )
		{
			$_sInner = '';
			$_sValue = PS::nvl( PS::o( $options, 'value', null, true ), $_sValue );

			foreach ( $_arOptions as $_sKey => $_sVal )
			{
				$_arOpts = array( 'value' => $_sKey );
				if ( $_sValue == $_sKey )
				{
					$_arOpts['selected'] = 'selected';
				}
				$_sInner .= self::tag( 'option', $_arOpts, $_sVal );
			}

			$options['name'] = $name;
			$_output .= self::tag( 'SELECT', $options, $_sInner );
		}

		return $_output;
	}

	/**
	 * Generates an opening form tag.
	 * Note, only the open tag is generated. A close tag should be placed manually
	 * at the end of the form.
	 *
	 * @param mixed  the form action URL (see {@link normalizeUrl} for details about this parameter.)
	 * @param string form method (e.g. post, get)
	 * @param array  additional HTML attributes (see {@link tag}).
	 *
	 * @return string the generated form tag.
	 * @since 1.0.4
	 * @see   endForm
	 */
	public static function beginForm( $action = '', $method = 'POST', $htmlOptions = array() )
	{
		$_validateOptions = PS::o( $htmlOptions, 'validateOptions', array(), true );

		if ( PS::o( $htmlOptions, 'validate', false, true ) )
		{
			self::$_validating = true;
			if ( !isset( $_validateOptions['target'] ) )
			{
				$_validateOptions['target'] = self::getFormSelector( $htmlOptions );
			}
			CPSjqValidate::create( null, $_validateOptions );
		}

		if ( PS::o( $htmlOptions, 'selectmenu', false, true ) )
		{
			CPSjqSelectMenu::create( null, array( 'target' => self::getFormSelector( $htmlOptions ) ) );
		}

		if ( $_sFormTitle = PS::o( $htmlOptions, 'formTitle', null, true ) )
		{
			echo PS::tag( PS::o( $htmlOptions, 'formTitleTag', 'h1', true ), array(), $_sFormTitle );
		}

		//	Grab current form id
		self::$_currentFormId = PS::o( $htmlOptions, 'id' );

		return parent::beginForm( $action, $method, $htmlOptions );
	}

	/**
	 * Generates an opening form tag.
	 * Note, only the open tag is generated. A close tag should be placed manually
	 * at the end of the form.
	 *
	 * @param array $formOptions The options for building this form
	 *
	 * @return string the generated form tag.
	 * @since 1.0.6
	 * @see   endForm
	 */
	public static function beginFormEx( &$formOptions = array() )
	{
		$_trail = null;

		//	Make sure we have a form id...
		if ( !isset( $formOptions['id'] ) )
		{
			$formOptions['id'] = 'ps-edit-form';
		}

		//	Get the rest of our options
		$_uiStyle = PS::o( $formOptions, 'uiStyle', self::getUiStyle(), true );
		$_action = PS::o( $formOptions, 'action', '', true );
		$_method = PS::o( $formOptions, 'method', 'POST', true );
		$_setPageTitle = PS::o( $formOptions, 'setPageTitle', true, true );
		$_errorCss = PS::o( $formOptions, 'errorCss', $_uiStyle == self::UI_DEFAULT ? 'error' : 'ui-state-error' );
		$_formHeader = PS::o( $formOptions, 'formHeader', null, true );

		//	Register form CSS if desired...
		PS::_rcf( PS::o( $formOptions, 'cssFiles', array(), true ) );

		//	And scripts
		PS::_rsf( PS::o( $formOptions, 'scriptFiles', array(), true ) );

		//	What type of form?
		switch ( $_uiStyle )
		{
			case self::UI_JQUERY:
				$_formContainerClass = PS::o( $formOptions, 'formContainerClass', self::STD_JQUI_FORM_CONTAINER_CLASS, true );
				$_formClass = PS::o( $formOptions, 'formClass', self::STD_FORM_CLASS, true );
				PS::$errorCss = $_errorClass = $_errorCss;
				break;

			case self::UI_BOOTSTRAP:
				$_formContainerClass = PS::o( $formOptions, 'formContainerClass', self::STD_BOOTSTRAP_FORM_CONTAINER_CLASS, true );
				$_formClass = PS::o( $formOptions, 'formClass', self::STD_FORM_CLASS, true );
				PS::$errorCss = $_errorClass = $_errorCss;
				$formOptions['class'] = $_formClass;
				break;

			case self::UI_BOOTSTRAP2:
				$_formContainerClass = PS::o( $formOptions, 'formContainerClass', self::STD_BOOTSTRAP2_FORM_CONTAINER_CLASS, true );
				$_formClass = PS::o( $formOptions, 'formClass', self::STD_BOOTSTRAP2_FORM_CLASS, true );
				PS::$errorCss = $_errorClass = $_errorCss;
				$formOptions['class'] = $_formClass;
				break;

			case self::UI_DEFAULT:
			default:
				$_formContainerClass = PS::o( $formOptions, 'formContainerClass', self::STD_FORM_CONTAINER_CLASS, true );
				$_formClass = PS::o( $formOptions, 'formClass', self::STD_FORM_CLASS, true );
				PS::$errorCss = $_errorClass = 'ps-validate-error';
				break;
		}

		self::setUiStyle( $_uiStyle );

		//	Set validation error class...
		if ( PS::o( $formOptions, 'validate', false ) == true )
		{
			$_validList = PS::o( $formOptions, 'validateOptions', array(), true );
			$_validList['errorClass'] = PS::o( $_validList, 'errorClass', self::$errorCss );
			$_validList['ignoreTitle'] = PS::o( $_validList, 'ignoreTitle', true );
			$formOptions['validateOptions'] = $_validList;
		}

		//	So it begins...
		$_output = null;

		if ( $_trail )
		{
			$_output .= $_trail;
		}

		//	Form header info...
		if ( $_formHeader )
		{
			//	Page title...
			if ( $_setPageTitle )
			{
				PS::_gc()->pageTitle = PS::_a()->name . ' : ' . $_formHeader;
			}

			//	Form title...
			$_formHeaderTag = PS::o( $formOptions, 'formHeaderTag', 'H1', true );
			$_arFormHeaderOptions = PS::o( $formOptions, 'formHeaderOptions', array(), true );
			$_output .= PS::tag( $_formHeaderTag, $_arFormHeaderOptions, $_formHeader );

			if ( $_formHeaderContent = PS::o( $formOptions, 'formHeaderContent', null, true ) )
			{
				$_formHeaderContentTag = PS::o( $formOptions, 'formHeaderContentTag', 'DIV', true );
				$_formHeaderContentOptions = PS::o( $formOptions, 'formHeaderContentOptions', array(), true );
				$_output .= PS::tag( $_formHeaderContentTag, $_formHeaderContentOptions, $_formHeaderContent );
			}
		}

		//	Build out beginning of form...
		$_output .= PS::openTag( 'div', array( 'class' => $_formContainerClass ) );
		//		$_output .= PS::openTag( 'div', array( 'class' => $_formClass ) );

		//	Build the form
		self::$_inForm = true;

//		CPSLog::trace( $_output . print_r( $formOptions, true ) );
		return $_output . self::beginForm( $_action, $_method, $formOptions );
	}

	/**
	 * Generates a closing form tag.
	 *
	 * @return string the generated tag
	 * @since 1.0.6
	 * @see   beginFormEx
	 */
	public static function endForm()
	{
		//	Finish our container
		$_append = ( self::$_inForm ) ? self::closeTag( 'div' ) : null;
		self::$_inForm = false;

		return parent::endForm() . $_append;
	}

	/**
	 * Outputs a <LABEL>. NOTE: Does not add ID and NAME prefixes...
	 *
	 * @param mixed $name
	 * @param mixed $label
	 * @param mixed $options
	 *
	 * @return string
	 */
	public static function textLabel( $name, $label = null, $options = array() )
	{
		$_sType = PS::o( $options, '_forType', 'text' );
		$_bRequired = PS::o( $options, '_required', false );
		$options['id'] = $options['name'] = self::getIdPrefix( 'label' ) . $name;
		$_suffixToUse = PS::o( $options, 'noSuffix', false, true ) ? '' : self::$_labelSuffix;

		return self::tag( 'label', $options, ( ( $label == null ) ? $name : $label ) . $_suffixToUse . self::getRequiredLabel() );
	}

	/**
	 * Generates a submit button.
	 *
	 * @param string the button label
	 * @param array  additional HTML attributes. Besides normal HTML attributes, a few special
	 *               attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
	 *
	 * @return string the generated button tag
	 * @see clientChange
	 */
	public static function submitButton( $label = 'Submit', $htmlOptions = array() )
	{
		$_before = $_after = null;
		$htmlOptions['type'] = 'submit';

		//	jQUI Button?
		if ( PS::o( $htmlOptions, 'jqui', false, true ) )
		{
			return self::jquiButton( $label, '_submit_', $htmlOptions );
		}

		if ( self::UI_BOOTSTRAP == self::$_uiStyle )
		{
			if ( !isset( $htmlOptions['class'] ) )
			{
				$htmlOptions['class'] = null;
			}

			$htmlOptions['class'] = self::addClass( $htmlOptions['class'], 'btn' );
			$_before = '<div class="control-group"><div class="controls">';
//' <span class="help-block">Example block-level help text here.</span>'
			$_after = '</div></div>';
		}

		//	Otherwise use regular button
		return $_before . self::htmlButton( $label, $htmlOptions ) . $_after;
	}

	/**
	 * Generates a submit button bar.
	 *
	 * Additional HTML options are available for the bar div itself:
	 *
	 * barClass The class to apply to the bar's div tag. Defaults to ps-submit-button-bar
	 * noBorder If true, no border line will be displayed on the top of the bar.
	 * barLeft If true, submit button will be flush left instead of the default flush right
	 * barCenter If true, submit button will be centered instead of the default flush right
	 *
	 * @param array $htmlOptions HTML options for the submit button.
	 *
	 * @return string
	 */
	public static function beginButtonBar( $htmlOptions = array() )
	{
		$_bDialog = PS::o( $htmlOptions, 'jquiDialog', false );

		//	Get orientation of buttons
		if ( PS::o( $htmlOptions, 'barLeft' ) )
		{
			$_sDirClass = 'ps-submit-button-bar-left';
		}
		else
		{
			if ( PS::o( $htmlOptions, 'barCenter' ) )
			{
				$_sDirClass = 'ps-submit-button-bar-center';
			}
			else
			{
				$_sDirClass = 'ps-submit-button-bar-right';
			}
		}

		$_sClass = PS::o( $htmlOptions, 'barClass', $_bDialog ? '.ui-dialog .ui-dialog-buttonpane' : 'ps-submit-button-bar' ) . ' ' . $_sDirClass;

		if ( !$_bNoBorder = PS::o( $htmlOptions, 'noBorder', false ) )
		{
			$_sClass .= ' ps-submit-button-bar-border';
		}

		return PS::openTag( 'div', array( 'class' => $_sClass ) );
	}

	/**
	 * End a button bar
	 *
	 * @return string
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
	 * barClass The class to apply to the bar's div tag. Defaults to ps-submit-button-bar
	 * noBorder If true, no border line will be displayed on the top of the bar.
	 * barLeft If true, submit button will be flush left instead of the default flush right
	 * barCenter If true, submit button will be centered instead of the default flush right
	 *
	 * @param string $label
	 * @param array  $htmlOptions HTML options for the submit button.
	 *
	 * @internal param string $sLable The button label
	 * @return string The generated button tag
	 * @see      clientChange
	 */
	public static function submitButtonBar( $label = 'Submit', $htmlOptions = array() )
	{
		$_sLegend = null;

		//	Make sure current form id is set if we have it...
		$htmlOptions['formId'] = PS::o( $htmlOptions, 'formId', self::$_currentFormId );
		if ( self::$_showRequiredLabel && trim( self::$afterRequiredLabel ) )
		{
			$_sLegend = '<span class="ps-form-legend"><span class="required">' . self::getRequiredLabel() . '</span> denotes required fields</span>';
		}

		return self::beginButtonBar( $htmlOptions ) . $_sLegend . self::submitButton( $label, $htmlOptions ) . self::endButtonBar();
	}

	/****
	 * Output a jQuery UI icon, icon button, or plain button
	 *
	 * @param string $label
	 * @param string $sLink
	 * @param array  $options
	 *
	 * @return string
	 */
	public static function jquiButton( $label, $sLink, $options = array() )
	{
		static $_bRegistered = false;
		$_sSize = $_sIconPos = $_bIconOnly = null;
		$_sLink = is_array( $sLink ) ? CHtml::normalizeUrl( $sLink ) : $sLink;
		$_bSubmit = ( $_sLink == '_submit_' || PS::o( $options, 'submit', false, true ) );

		$_id = PS::o( $options, 'id', self::getWidgetId( self::ID_PREFIX . '.jqbtn' ), true );
		$_sFormId = PS::o( $options, 'formId', self::$_currentFormId, true );

		if ( $_icon = PS::o( $options, 'icon', null, true ) )
		{
			$_bIconOnly = PS::o( $options, 'iconOnly', false, true );
			$_icon = "<span class=\"ui-icon ui-icon-{$_icon}\"></span>";
			if ( $label && !$_bIconOnly )
			{
				$_sIconPos = "ps-button-icon-" . PS::o( $options, 'iconPosition', 'left', true );
			}
			else
			{
				$_sSize = PS::o( $options, 'iconSize', null, true );
				$_sIconPos = 'ps-button-icon-solo' . ( ( $_sSize ) ? '-' . $_sSize : '' );
			}
		}

		if ( $_sOnClick = PS::o( $options, 'click', null, true ) )
		{
			$_sOnClick = 'onClick="' . $_sOnClick . '"';
		}
		else
		{
			if ( $_sConfirm = PS::o( $options, 'confirm', null, true ) )
			{
				$_sHref = $_sLink;
				$_sForm = ( $_sFormId ) ? "document.getElementById('{$_sFormId}')" : 'this.form';
				$_sConfirm = str_replace( "'", "''", str_replace( '"', '""', $_sConfirm ) );
				$_sOnClick = "return confirmAction({$_sForm},'{$_sConfirm}','{$_sLink}','{$_sHref}');";
				$_sLink = '#';

				if ( !$_bRegistered )
				{
					$_action = $_bSubmit ? 'return oForm.submit();' : 'window.location.href = sHref;';
					$_script
						= <<<JS
function confirmAction( oForm, sMessage, sHref )
{
	jConfirm( sMessage, 'Please Confirm Your Action', function( bVal ) {
		if ( bVal )
		{
			{$_action}
			return true;
		}

		return false;
	});
}
JS;
					//	Register scripts
					PS::_cs()->registerScript( self::getWidgetId( self::ID_PREFIX . '.cas.' ), $_script, CClientScript::POS_END );
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
		$options['id'] = $_id;
		$options['title'] = $label;
		$_sClass = PS::o( $options, 'class', null );
		$options['class'] = "ps-button {$_sIconPos} ui-state-default ui-corner-all {$_sClass}";

		//	Make sure we add our onClick...
		if ( $_sOnClick )
		{
			$options['onclick'] = $_sOnClick;
			$options['encode'] = false;
		}

		//	Generate the link
		return self::link( ( $_icon . $label ), $_sLink, $options );
	}

	/**
	 * Formats the created and modified dates
	 *
	 * @param CActiveRecord $model
	 * @param string        $createdColumn
	 * @param string        $lmodColumn
	 * @param string        $dateFormat
	 *
	 * @return null|string
	 * @deprecated This method has been moved to CPSModel
	 */
	public static function showDates( $model, $createdColumn = 'created', $lmodColumn = 'modified', $dateFormat = 'm/d/Y h:i:s A' )
	{
		$_output = null;

		if ( $model->hasAttribute( $createdColumn ) && $model->hasAttribute( $lmodColumn ) )
		{
			$_dtCreate = strtotime( $model->$createdColumn );
			$_dtLMod = strtotime( $model->$lmodColumn );

			//	Fix up dates
			$_dtCreate = date( $dateFormat, $_dtCreate );
			$_dtLMod = date( $dateFormat, $_dtLMod );

			$_output = '<div class="ps-form-footer">';
			$_output
				.= '<span><strong>Created:</strong>&nbsp;' . $_dtCreate . '</span>' . self::pipe( '/' ) . '<span><strong>Modified:</strong>&nbsp;' .
				$_dtLMod . '</span>';
			$_output .= '</div>';
		}

		return $_output;
	}

	/**
	 * Generates an image button with an optional hover component.
	 *
	 * @param string $src          the button label
	 * @param string $href         the destination link
	 * @param array  $htmlOptions  additional HTML attributes. Besides normal HTML attributes, a few special
	 *                             attributes are also recognized (see {@link hoverImage}, {@link clientChange} and {@link tag} for more details.)
	 *
	 * @return string the generated button tag
	 *
	 * @see      clientChange
	 * @see      hoverImage
	 */
	public static function imageLink( $src, $href, $htmlOptions = array() )
	{
		if ( null === ( $_hoverImage = PS::o( $htmlOptions, 'hoverImage', null, true ) ) )
		{
			//	Create the script
			$htmlOptions['id'] = PS::o( $htmlOptions, 'id', self::ID_PREFIX . self::$count++ );

			$_script
				= <<<JS
	jQuery('#{$htmlOptions['id']}').hover(
		function(){
			jQuery(this).attr('src','{$_hoverImage}');
		},
		function(){
			jQuery(this).attr('src','{$src}');
		}
	);
JS;
			//	Register the script
			PS::registerScript( 'ib#' . self::getWidgetId( self::ID_PREFIX ), $_script );
		}

		return self::tag( 'a', array( 'href' => $href ), self::image( $src, null, $htmlOptions ) );
	}

	/**
	 * {@InheritDoc}
	 */
	public static function imageButton( $src, $htmlOptions = array() )
	{
		if ( null !== ( $_hoverImage = PS::o( $htmlOptions, 'hoverImage', null, true ) ) )
		{
			//	Create the script
			$htmlOptions['id'] = PS::o( $htmlOptions, 'id', self::ID_PREFIX . self::$count++ );

			$_script
				= <<<JS
/** Hover mechanism for image button */
jQuery('#{$htmlOptions['id']}').hover(
	function(){
		jQuery(this).attr('src','{$_hoverImage}');
	},
	function(){
		jQuery(this).attr('src','{$src}');
	}
);
JS;
			//	Register the script
			PS::_rs( 'ib#' . self::ID_PREFIX . self::$count++, $_script, CClientScript::POS_READY );
		}

		return parent::imageButton( $src, $htmlOptions );
	}

	/**
	 * Retrieves the target selector of a form
	 *
	 * @param array  $htmlOptions
	 * @param string $defaultId
	 *
	 * @return string
	 */
	protected static function getFormSelector( $htmlOptions = array(), $defaultId = 'div.yiiForm>form' )
	{
		$_target = PS::o( $htmlOptions, 'id', null );

		return ( $_target == null ) ? $defaultId : '#' . $_target;
	}

	/**
	 * {@InheritDoc}
	 */
	public static function radioButtonList( $name, $select, $data, $htmlOptions = array() )
	{
		$_template = PS::o( $htmlOptions, 'template', '{input} {label}', true );
		$_separator = PS::o( $htmlOptions, 'separator', '<br/>' . PHP_EOL, true );
		$_labelOptions = PS::o( $htmlOptions, 'labelOptions', array(), true );

		$_items = array();
		$_baseId = self::getIdByName( $name );
		$_id = 0;

		foreach ( $data as $_value => $_label )
		{
			$_checked = !strcmp( $_value, $select );
			$htmlOptions['value'] = $_value;
			$htmlOptions['id'] = $_baseId . '_' . $_id++;
			$_option = self::radioButton( $name, $_checked, $htmlOptions );
			$_label = self::label( $_label, $htmlOptions['id'], $_labelOptions );

			$_items[] = strtr(
				$_template,
				array(
					'{input}' => $_option,
					'{label}' => $_label
				)
			);
		}

		return implode( $_separator, $_items );
	}

	/**
	 * Create a field set with optional legend
	 *
	 * @param string $legend
	 * @param array  $options
	 *
	 * @return string
	 */
	public static function beginFieldset( $legend, $options = array() )
	{
		return self::tag(
			'fieldset',
			$options,
			( $legend ? self::tag( 'legend', PS::o( $options, 'legendOptions', array(), true ), $legend ) : false ),
			false
		);
	}

	/**
	 * Closes an open fieldset
	 *
	 * @return string
	 */
	public static function endFieldset()
	{
		return self::closeTag( 'fieldset' );
	}

	/**
	 * Outputs a themed div with a message
	 *
	 * @param string $message
	 *
	 * @return boolean
	 */
	public static function flashHighlight( $message = null )
	{
		if ( null !== $message )
		{
			return self::tag(
				'div',
				array(
					'class' => 'ui-widget'
				),
				self::tag(
					'div',
					array(
						'class' => 'ui-state-highlight ui-corner-all',
						'style' => 'padding:1em; margin: 5px 0px 15px 0px;'
					),
					'<p><span class="ui-icon ui-icon-info" style="float: left; margin-right:10px;"></span>' . $message . '</p>'
				)
			);
		}
	}

	/**
	 * Puts up flash div if the flash message specified is set. Defaults to 'success'.
	 *
	 * @param string $which
	 * @param bool   $left
	 *
	 * @return string
	 */
	public static function flashMessage( $which = 'success', $left = false )
	{
		$_message = ( Yii::app()->user->hasFlash( $which ) ) ? Yii::app()->user->getFlash( $which ) : null;
		$_div = 'ps-flash-display' . ( $left ? '-left' : '' );
		PS::registerScript( 'ps.flash.display', 'jQuery(".' . $_div . '").animate({opacity: 1.0}, 3000).fadeOut();' );

		return self::tag( 'div', array( 'class' => $_div ), $_message );
	}

	/**
	 * Returns a styled menu separator.
	 *
	 * @param string $separator
	 *
	 * @return string
	 */
	public static function pipe( $separator = '|' )
	{
		return '<span class="ps-pipe">' . $separator . '</span>';
	}

	/**
	 * Displays a summary of validation errors for one or several models.
	 *
	 * @param mixed  $models      the models whose input errors are to be displayed. This can be either a single model or an array of models.
	 * @param string $header      a piece of HTML code that appears in front of the errors
	 * @param string $footer      a piece of HTML code that appears at the end of the errors
	 * @param array  $htmlOptions additional HTML attributes to be rendered in the container div tag. This parameter has been available since version 1.0.7.
	 *
	 * @return string the error summary. Empty if no errors are found.
	 * @see CModel::getErrors
	 * @see errorSummaryCss
	 */
	public static function errorSummary( $models, $header = null, $footer = null, $htmlOptions = array() )
	{
		$_content = null;
		$_errorCount = 0;
		$_models = $models;
		$_noIcon = PS::o( $htmlOptions, 'noIcon', false, true );
		$_iconClass = PS::o( $htmlOptions, 'errorIconClass', null, true );
		$_headerTag = PS::o( $htmlOptions, 'headerTag', 'strong', true );
		$_errorListClass = PS::o( $htmlOptions, 'errorListClass', null, true );
		$_singleErrorListClass = PS::o( $htmlOptions, 'singleErrorListClass', $_errorListClass, true );

		if ( self::UI_BOOTSTRAP == PS::o( $htmlOptions, 'uiStyle', self::UI_JQUERY ) )
		{
			$_bootstrap = true;
			self::$errorSummaryCss = 'alert alert-error fade in';
		}
		else
		{
			$_bootstrap = false;
			self::$errorSummaryCss = 'ps-error-summary ui-state-error';
		}

		if ( !is_array( $_models ) )
		{
			$_models = array( $models );
		}

		/** @var $_model CActiveRecord */
		foreach ( $_models as $_model )
		{
			$_errors = $_model->getErrors();

			foreach ( $_errors as $_error )
			{
				foreach ( $_error as $_errorMessage )
				{
					if ( !empty( $_errorMessage ) )
					{
						$_content .= PS::tag( 'li', array(), $_errorMessage );
						$_errorCount++;
					}
				}
			}
		}

		if ( $_content !== null )
		{
			if ( $_bootstrap )
			{
				return <<<HTML
<div class="alert alert-error" style="margin-top: 15px;">
<a class="close" data-dismiss="alert" href="#">&times;</a>
<h4 class="alert-heading">Please check for the following errors:</h4>
<ul>{$_content}</ul>
</div>
HTML;
			}
			else
			{
				if ( !$_iconClass )
				{
					$_icon = ( !$_noIcon )
						?
						'<span class="ui-icon ui-icon-alert" style="float: left; margin-top: 3px; margin-left: 0.5em; margin-right: 6px;"></span>'
						:
						null;
				}
				else
				{
					$_icon
						= '<span class="' . $_iconClass . '" style="float: left; margin-top: 0.5em; margin-left: 0.5em; margin-right: 6px;"></span>';
				}

				if ( null === $header )
				{
					$header = self::tag(
						$_headerTag,
						array(
							'style' => 'font-style: italic; font-weight: bold;'
						),
						Yii::t( 'yii', 'Please fix the following input errors:' )
					);
				}

				//	Different class for single errors perhaps?
				if ( $_errorCount == 1 )
				{
					$_errorListClass = $_singleErrorListClass;
				}

				$htmlOptions['class'] = PS::o( $htmlOptions, 'class', self::$errorSummaryCss, true );

				return self::tag(
					'div',
					$htmlOptions,
					$_icon . $header . self::tag(
						'ul',
						array(
							'class' => $_errorListClass,
							'style' => 'margin-left:25px; margin-top:5px'
						),
						$_content
					)
				) . $footer;
			}
		}
	}

	/**
	 * Transforms markdown to safe text
	 *
	 * @param string $text
	 *
	 * @return string
	 */
	public static function markdownTransform( $text )
	{
		$_parser = new CMarkdownParser();

		return $_parser->safeTransform( $text );
	}

	/**
	 * Adds a class to an array of classes.
	 *
	 * @param string|array $class The existing classes. If a string is passed in, a string is returned. If an array is passed in, an array is returned.
	 * @param string|array $classData
	 *
	 * @return array|string
	 */
	public static function addClass( $class, $classData )
	{
		$_classList = ( !is_array( $class ) ) ? explode( ' ', trim( $class ) ) : $class;
		$_newClassList = PS::makeArray( $classData );

		foreach ( $_newClassList as $_sClass )
		{
			if ( !in_array( $_sClass, $_classList ) )
			{
				$_classList[] = $_sClass;
			}
		}

		return is_array( $class ) ? $_classList : implode( ' ', $_classList );
	}

	/**
	 * Removes a class, or pattern of classes from an array of classes.
	 *
	 * @param string|array $class The existing class(es). If a string is passed in, a string is returned. If an array is passed in, an array is returned.
	 * @param string|array $classData
	 *
	 * @return array|string
	 */
	public static function removeClass( $class, $classData )
	{
		$_classList = ( !is_array( $class ) ) ? explode( ' ', trim( $class ) ) : $class;
		$_newClassList = PS::makeArray( $classData );
		$_arClass = array();

		foreach ( $_classList as $_sClass )
		{
			if ( !in_array( $_sClass, $_newClassList ) )
			{
				$_arClass[] = $_sClass;
			}
		}

		return is_array( $class ) ? $_arClass : implode( ' ', $_arClass );
	}

	//********************************************************************************
	//* Protected Methods
	//********************************************************************************

	/**
	 * Gets the options for pre-fab select boxes
	 *
	 * @param mixed $inputFieldType
	 * @param array $htmlOptions
	 * @param array $listData
	 * @param int   $selected
	 *
	 * @return boolean
	 */
	protected static function setDropDownValues( &$inputFieldType, &$htmlOptions = array(), &$listData = null, $selected = null )
	{
		$_data = null;

		if ( is_numeric( $inputFieldType ) && $inputFieldType >= 1000 )
		{
			//	One of our generics? Set data, type and return
			if ( null !== ( $_data = self::getGenericDropDownValues( $inputFieldType, $htmlOptions, $listData ) ) )
			{
				$inputFieldType = self::DROPDOWN;
				$listData = $_data;
			}
			else
			{
				//	Generic or dropdown? Set data, type and return
				if ( $inputFieldType == self::DD_GENERIC || $inputFieldType == self::DROPDOWN )
				{
					$_data = $listData;
					$inputFieldType = self::DROPDOWN;
				}
			}
		}

		//	Return a copy of the data array
		return $_data;
	}

	/**
	 * Gets the options for pre-fab select boxes
	 *
	 * @param int   $type
	 * @param array $htmlOptions
	 * @param array $listData
	 *
	 * @internal param mixed $inputFieldType
	 *
	 * @return array
	 */
	protected static function getGenericDropDownValues( $type, &$htmlOptions = array(), &$listData = null )
	{
		$_data = null;

		if ( is_numeric( $type ) )
		{
			switch ( $type )
			{
				case self::DD_GENERIC:
					$_data = $listData ? : PS::o( $htmlOptions, 'data', null );
					break;

				case self::DD_YES_NO:
					$_data = array(
						0 => 'No',
						1 => 'Yes'
					);
					break;

				case self::DD_YES_NO_ALL:
					$_data = array(
						-1 => 'All',
						0  => 'No',
						1  => 'Yes'
					);
					break;

				case self::DD_US_STATES:
					$_data = require( 'static/us_state_array.php' );
					break;

				case self::DD_MONTH_NUMBERS:
					if ( null == PS::o( $htmlOptions, 'value' ) )
					{
						$htmlOptions['value'] = date( 'm' );
					}
					$_data = require( 'static/month_numbers_array.php' );
					break;

				case self::DD_DAY_NUMBERS:
					if ( null == PS::o( $htmlOptions, 'value' ) )
					{
						$htmlOptions['value'] = date( 'd' );
					}
					$_data = require( 'static/day_numbers_array.php' );
					break;

				case self::DD_MONTH_NAMES:
					if ( null == PS::o( $htmlOptions, 'value' ) )
					{
						$htmlOptions['value'] = date( 'm' );
					}
					$_data = require( 'static/month_names_array.php' );
					break;

				case self::DD_YEARS:
					if ( null == PS::o( $htmlOptions, 'value' ) )
					{
						$htmlOptions['value'] = date( 'Y' );
					}
					$_range = PS::o( $htmlOptions, 'range', 5, true );
					$_rangeStart = PS::o( $htmlOptions, 'rangeStart', date( 'Y' ), true );
					$_data = array();
					for ( $_i = 0, $_baseYear = $_rangeStart; $_i < $_range; $_i++ )
					{
						$_data[( $_baseYear + $_i )] = ( $_baseYear + $_i );
					}
					break;

				case self::DD_CC_TYPES:
					$_data = require( 'static/cc_types_array.php' );
					break;

				case self::DD_TIME_ZONES:
					$_data = require( 'static/time_zones_array.php' );
					break;

				case self::DD_DATA_LOOKUP:
					$_id = PS::o( $htmlOptions, 'dataId', null, true );
					$_name = PS::o( $htmlOptions, 'dataName', null, true );
					$_modelName = PS::o( $htmlOptions, 'dataModel', null, true );
					$_condition = PS::o( $htmlOptions, 'dataCondition', null, true );

					if ( $_id && $_name && $_modelName )
					{
						/** @var $_model CActiveRecord */
						if ( null !== ( $_model = call_user_func( array( $_modelName, 'model' ) ) ) )
						{
							$_models = $_model->findAll(
								array(
									'select'    => $_id . ', ' . $_name,
									'condition' => $_condition
								)
							);

							if ( null !== $_models )
							{
								foreach ( $_models as $_model )
								{
									$_data[$_model->{$_id}] = $_model->{$_name};
								}
							}
						}
					}
					break;

				case self::DD_JQUI_THEMES:
					$_data = \CPSjqUIWrapper::getValidThemes();
					break;
			}
		}

		return $_data;
	}

	/**
	 * Returns the external url that was published.
	 *
	 * @return string
	 * @static
	 */
	public static function getExternalLibraryUrl()
	{
		$_path = str_replace( PS::_gbu(), '', Yii::app()->getAssetManager()->getPublishedUrl( Yii::getPathOfAlias( 'pogostick.external' ), true ) );
		if ( defined( 'PYE_TRACE_LEVEL' ) && PYE_TRACE_LEVEL > 3 )
		{
//			CPSLog::trace( __METHOD__, 'External Library URL: ' . $_path );
		}

		return $_path;
	}

	/**
	 * Returns the path that was published.
	 *
	 * @return string
	 * @static
	 */
	public static function getExternalLibraryPath()
	{
		$_path = str_replace( PS::_gbp(), '', Yii::app()->getAssetManager()->getPublishedPath( Yii::getPathOfAlias( 'pogostick.external' ), true ) );
		if ( defined( 'PYE_TRACE_LEVEL' ) && PYE_TRACE_LEVEL > 3 )
		{
//			CPSLog::trace( __METHOD__, 'External Library Path: ' . $_path );
		}

		return $_path;
	}

	/**
	 * Generates a label tag for a model attribute.
	 * This is an enhanced version of {@link activeLabel}. It will render additional
	 * CSS class and mark when the attribute is required.
	 * In particular, it calls {@link CModel::isAttributeRequired} to determine
	 * if the attribute is required.
	 * If so, it will add a CSS class {@link CHtml::requiredCss} to the label,
	 * and decorate the label with {@link CHtml::beforeRequiredLabel} and
	 * {@link CHtml::afterRequiredLabel}.
	 *
	 * @param CModel $model       the data model
	 * @param string $attribute   the attribute
	 * @param array  $htmlOptions additional HTML attributes.
	 *
	 * @return string the generated label tag
	 * @since 1.0.2
	 */
	public static function activeLabelEx( $model, $attribute, $htmlOptions = array() )
	{
		$realAttribute = $attribute;
		self::resolveName( $model, $attribute ); // strip off square brackets if any

		if ( null === PS::o( $htmlOptions, 'required' ) )
		{
			$htmlOptions['required'] = $model->isAttributeRequired( $attribute );
		}

		return self::activeLabel( $model, $realAttribute, $htmlOptions );
	}

	/**
	 * Outputs a nicely formatted JSON object
	 *
	 * @param mixed $data A JSON encoded string or an object
	 *
	 * @return string
	 */
	public static function jsonFormat( $data = null )
	{
		$_indent = ' ';
		$_result = null;
		$_indentLevel = 0;
		$_inString = false;

		//	Is it valid?
		if ( !is_string( $data ) )
		{
			$data = json_decode( $data );
		}

		if ( empty( $data ) )
		{
			return false;
		}

		for ( $_i = 0, $_count = strlen( $data ); $_i < $_count; $_i++ )
		{
			switch ( $data[$_i] )
			{
				case '{':
				case '[':
					$_result .= $data[$_i] . ( $_inString ? PHP_EOL . str_repeat( $_indent, ++$_indentLevel ) : null );
					break;

				case '}':
				case ']':
					$_result .= ( !$_inString ? PHP_EOL . str_repeat( $_indent, $_indentLevel ) : null ) . $data[$_i];
					if ( --$_indentLevel < 0 )
					{
						$_indentLevel = 0;
					}
					break;

				case ',':
					if ( !$_inString )
					{
						$_result .= ',' . PHP_EOL . str_repeat( $_indent, $_indentLevel );
					}
					else
					{
						$_result .= $data[$_i];
					}
					break;

				case ':':
					if ( !$_inString )
					{
						$_result .= ': ';
					}
					else
					{
						$_result .= $data[$_i];
					}
					break;

				case '"':
					if ( 0 < $_i && '\\' != $data[$_i - 1] )
					{
						$_inString = !$_inString;
					}

				default:
					$_result .= $data[$_i];
					break;
			}
		}

		return $_result;
	}

	/**
	 * @static
	 *
	 * @param int $seconds
	 */
	public static function formatSeconds( $seconds = 0 )
	{
	}

	/**
	 * @static
	 *
	 * @param $type
	 *
	 * @return mixed|null
	 */
	public static function getNamePrefix( $type )
	{
		return self::$_useNamePrefixes ? PS::o( self::$_namePrefixes, $type ) : null;
	}

	/**
	 * @static
	 *
	 * @param $type
	 * @param $value
	 */
	public static function setNamePrefix( $type, $value )
	{
		self::$_namePrefixes[$type] = $value;
	}

	//**************************************************************************
	//* Properties
	//**************************************************************************

	/**
	 * @param boolean $validating
	 *
	 * @return void
	 */
	public static function setValidating( $validating )
	{
		self::$_validating = $validating;
	}

	/**
	 * @return boolean
	 */
	public static function getValidating()
	{
		return self::$_validating;
	}

	/**
	 * @param boolean $useNamePrefixes
	 *
	 * @return void
	 */
	public static function setUseNamePrefixes( $useNamePrefixes )
	{
		self::$_useNamePrefixes = $useNamePrefixes;
	}

	/**
	 * @return boolean
	 */
	public static function getUseNamePrefixes()
	{
		return self::$_useNamePrefixes;
	}

	/**
	 * @param boolean $useIdPrefixes
	 *
	 * @return void
	 */
	public static function setUseIdPrefixes( $useIdPrefixes )
	{
		self::$_useIdPrefixes = $useIdPrefixes;
	}

	/**
	 * @return boolean
	 */
	public static function getUseIdPrefixes()
	{
		return self::$_useIdPrefixes;
	}

	/**
	 * @param int $uiStyle
	 *
	 * @return void
	 */
	public static function setUiStyle( $uiStyle )
	{
		self::$_uiStyle = $uiStyle;
	}

	/**
	 * @return int
	 */
	public static function getUiStyle()
	{
		return self::$_uiStyle;
	}

	/**
	 * @param boolean $showRequiredLabel
	 *
	 * @return void
	 */
	public static function setShowRequiredLabel( $showRequiredLabel )
	{
		self::$_showRequiredLabel = $showRequiredLabel;
	}

	/**
	 * @return boolean
	 */
	public static function getShowRequiredLabel()
	{
		return self::$_showRequiredLabel;
	}

	/**
	 * @param string $requiredHtml
	 *
	 * @return void
	 */
	public static function setRequiredHtml( $requiredHtml )
	{
		self::$_requiredHtml = $requiredHtml;
	}

	/**
	 * @return string
	 */
	public static function getRequiredHtml()
	{
		return self::$_requiredHtml;
	}

	/**
	 * @param string $onClass
	 *
	 * @return void
	 */
	public static function setOnClass( $onClass )
	{
		self::$_onClass = $onClass;
	}

	/**
	 * @return string
	 */
	public static function getOnClass()
	{
		return self::$_onClass;
	}

	/**
	 * @param string $offClass
	 *
	 * @return void
	 */
	public static function setOffClass( $offClass )
	{
		self::$_offClass = $offClass;
	}

	/**
	 * @return string
	 */
	public static function getOffClass()
	{
		return self::$_offClass;
	}

	/**
	 * @param string $namePrefixes
	 *
	 * @return void
	 */
	public static function setNamePrefixes( $namePrefixes )
	{
		self::$_namePrefixes = $namePrefixes;
	}

	/**
	 * @return string
	 */
	public static function getNamePrefixes()
	{
		return self::$_namePrefixes;
	}

	/**
	 * @param string $lastFieldName
	 *
	 * @return void
	 */
	public static function setLastFieldName( $lastFieldName )
	{
		self::$_lastFieldName = $lastFieldName;
	}

	/**
	 * @return string
	 */
	public static function getLastFieldName()
	{
		return self::$_lastFieldName;
	}

	/**
	 * @param string $lastFieldId
	 *
	 * @return void
	 */
	public static function setLastFieldId( $lastFieldId )
	{
		self::$_lastFieldId = $lastFieldId;
	}

	/**
	 * @return string
	 */
	public static function getLastFieldId()
	{
		return self::$_lastFieldId;
	}

	/**
	 * @param string $labelSuffix
	 *
	 * @return void
	 */
	public static function setLabelSuffix( $labelSuffix )
	{
		self::$_labelSuffix = $labelSuffix;
	}

	/**
	 * @return string
	 */
	public static function getLabelSuffix()
	{
		return self::$_labelSuffix;
	}

	/**
	 * @param array $inputMap
	 *
	 * @return void
	 */
	public static function setInputMap( $inputMap )
	{
		self::$_inputMap = $inputMap;
	}

	/**
	 * @return array
	 */
	public static function getInputMap()
	{
		return self::$_inputMap;
	}

	/**
	 * @param boolean $inForm
	 *
	 * @return void
	 */
	public static function setInForm( $inForm )
	{
		self::$_inForm = $inForm;
	}

	/**
	 * @return boolean
	 */
	public static function getInForm()
	{
		return self::$_inForm;
	}

	/**
	 * @param array $idPrefixes
	 *
	 * @return void
	 */
	public static function setIdPrefixes( $idPrefixes )
	{
		self::$_idPrefixes = $idPrefixes;
	}

	/**
	 * @return array
	 */
	public static function getIdPrefixes()
	{
		return self::$_idPrefixes;
	}

	/**
	 * @param int $idCounter
	 *
	 * @return void
	 */
	public static function setIdCounter( $idCounter )
	{
		self::$_idCounter = $idCounter;
	}

	/**
	 * @return int
	 */
	public static function getIdCounter()
	{
		return self::$_idCounter;
	}

	/**
	 * @param string $hintTemplate
	 *
	 * @return void
	 */
	public static function setHintTemplate( $hintTemplate )
	{
		self::$_hintTemplate = $hintTemplate;
	}

	/**
	 * @return string
	 */
	public static function getHintTemplate()
	{
		return self::$_hintTemplate;
	}

	/**
	 * @param string $formFieldContainerPrefix
	 *
	 * @return void
	 */
	public static function setFormFieldContainerPrefix( $formFieldContainerPrefix )
	{
		self::$_formFieldContainerPrefix = $formFieldContainerPrefix;
	}

	/**
	 * @return string
	 */
	public static function getFormFieldContainerPrefix()
	{
		return self::$_formFieldContainerPrefix;
	}

	/**
	 * @param string $formFieldContainerClass
	 *
	 * @return void
	 */
	public static function setFormFieldContainerClass( $formFieldContainerClass )
	{
		self::$_formFieldContainerClass = $formFieldContainerClass;
	}

	/**
	 * @return string
	 */
	public static function getFormFieldContainerClass()
	{
		return self::$_formFieldContainerClass;
	}

	/**
	 * @param string $formFieldContainer
	 *
	 * @return void
	 */
	public static function setFormFieldContainer( $formFieldContainer )
	{
		self::$_formFieldContainer = $formFieldContainer;
	}

	/**
	 * @return string
	 */
	public static function getFormFieldContainer()
	{
		return self::$_formFieldContainer;
	}

	/**
	 * @param string $currentFormId
	 *
	 * @return void
	 */
	public static function setCurrentFormId( $currentFormId )
	{
		self::$_currentFormId = $currentFormId;
	}

	/**
	 * @return string
	 */
	public static function getCurrentFormId()
	{
		return self::$_currentFormId;
	}

	/**
	 * @param boolean $cssLoaded
	 *
	 * @return void
	 */
	public static function setCssLoaded( $cssLoaded )
	{
		self::$_cssLoaded = $cssLoaded;
	}

	/**
	 * @return boolean
	 */
	public static function getCssLoaded()
	{
		return self::$_cssLoaded;
	}

	/**
	 * @param string $codeModel
	 *
	 * @return void
	 */
	public static function setCodeModel( $codeModel )
	{
		self::$_codeModel = $codeModel;
	}

	/**
	 * @return string
	 */
	public static function getCodeModel()
	{
		return self::$_codeModel;
	}

	/**
	 * @return string
	 */
	public static function getRequiredLabel()
	{
		return self::$_requiredLabel;
	}
}

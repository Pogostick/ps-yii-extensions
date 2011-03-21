<?php
/**
 * This file is part of the psYiiExtensions package.
 *
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */
/**
 * The ultimate wrapper for any jQuery widget
 *
 * @category		Widgets
 * @package		psYiiExtensions
 * @subpackage 	widgets.jqui
 *
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version		SVN: $Id: CPSjQueryWidget.php 396 2010-07-27 17:36:55Z jerryablan@gmail.com $
 * @since			v1.0.0
 *
 * @filesource
 *
 * @property $autoRun The name of the widget you'd like to create (i.e. draggable, accordion, etc.)
 * @property $widgetName The name of the widget you'd like to create (i.e. draggable, accordion, etc.)
 * @property $target The jQuery selector to which to apply this widget. If $target is not specified, "id" is used and prepended with a "#".
 */
class CPSjQueryWidget extends CPSWidget
{
	//********************************************************************************
	//* Methods
	//********************************************************************************

	/**
	* Initialize
	*/
	function preinit()
	{
		parent::preinit();

		//	Add the default options for jqUI stuff
		$this->addOptions(
			array(
				'autoRun_' => 'bool:true::true',
				'autoRegister_' => 'bool:true',
				'widgetName_' => 'string:::true',
				'widgetMethodName_' => 'string',
				'target_' => 'string:::true',
				'locateScript_' => 'bool:false',
				'naked_' => 'bool:false',				//	Setting naked = true turns on autoRegister and locateScript
				'extraCssFiles_' => 'array:array()',		//	For nakedness
				'extraScriptFiles_' => 'array:array()',	//	For nakedness
			)
		);
	}

	/**
	* Initialize the widget
	*
	*/
	public function init()
	{
		//	Daddy
		parent::init();

		//	Validate baseUrl
		if ( empty( $this->baseUrl ) ) $this->baseUrl = $this->extLibUrl;
	}

	/***
	* Runs this widget
	*
	*/
	public function run()
	{
		//	Register the scripts/css
		$this->registerClientScripts( $this->locateScript );

		//	Generate the HTML if available
		echo $this->generateHtml();
	}

	/**
	* Returns the external url that was published.
	* @return string
	* @static
	*/
	public static function getExternalLibraryUrl()
	{
		return PS::getExternalLibraryUrl();
	}

	/**
	* Returns the path that was published.
	* @return string
	* @static
	*/
	public static function getExternalLibraryPath()
	{
		return PS::getExternalLibraryPath();
	}

	/**
	* Adds a user script to the output array
	*
	* @param array $arScript
	*/
	public function addScripts( $scriptFiles = array() )
	{
		foreach ( $scriptFiles as $_scriptFile )
			$this->_scriptFiles[] = $_scriptFile;
	}

	/**
	* Registers the needed CSS and JavaScript.
	* This method DOES NOT call generateJavascript()
	* @param boolean If true, system will try to find jquery plugins based on the pattern jquery.<plugin-name[.min].js
	* @return CClientScript The current app's ClientScript object
	*/
	public function registerClientScripts( $autoLocateScript = false )
	{
		//	Additional scripts, let dad load them.
		if ( is_array( $this->_scriptFiles ) )
		{
			foreach ( $this->_scriptFiles as $_scriptFile )
				$this->pushScriptFile( $_scriptFile );
		}

		//	Do we have a registered script?
		if ( $autoLocateScript )
		{
			$_yiiBase = PS::_gbu();
			$_widgetName = $this->widgetName;

			//	Try and auto-find script file...
			$_basePath = self::getExternalLibraryPath() . '/jquery-plugins/' . $_widgetName;
			$_filePath = $_basePath . '/jquery.' . $_widgetName;

			$_baseUrl = self::getExternalLibraryUrl() . '/jquery-plugins/' . $_widgetName;
			$_fileUrl = $_baseUrl . '/jquery.' . $_widgetName;

			//	See if we have such a plug-in
			$_fileList = array(
				$_filePath . '.min.js',
				$_filePath . '-min.js',
				$_filePath . '.js',
				$_basePath . '/ui.' . $_widgetName . '.min.js',
				$_basePath . '/ui.' . $_widgetName . '-min.js',
				$_basePath . '/ui.' . $_widgetName . '.js',
				$_basePath . '/js/jquery.' . $_widgetName . '.min.js',
				$_basePath . '/js/jquery.' . $_widgetName . '-min.js',
				$_basePath . '/js/jquery.' . $_widgetName . '.js',
				$_basePath . '/js/ui.' . $_widgetName . '.min.js',
				$_basePath . '/js/ui.' . $_widgetName . '-min.js',
				$_basePath . '/js/ui.' . $_widgetName . '.js',
			);

			//	Ok, check 'em out...
			foreach ( $_fileList as $_file )
			{
				if ( file_exists( $_file ) )
				{
					$this->pushScriptFile( str_replace( $_SERVER['DOCUMENT_ROOT'] . $_yiiBase, '', $_file ) );
					break;
				}
			}

			//	Any others?
			foreach ( PS::nvl( $this->extraScriptFiles, array() ) as $_scriptFile )
				$this->pushScriptFile( str_replace( $_SERVER['DOCUMENT_ROOT'] . $_yiiBase, '', $_basePath . DIRECTORY_SEPARATOR . $_scriptFile ) );

			//	Now css...
			$_fileList = array(
				$_filePath . '.min.css',
				$_filePath . '-min.css',
				$_filePath . '.css',
				$_basePath . '/ui.' . $_widgetName . '.min.css',
				$_basePath . '/ui.' . $_widgetName . '-min.css',
				$_basePath . '/ui.' . $_widgetName . '.css',
				$_basePath . '/css/jquery.' . $_widgetName . '.min.css',
				$_basePath . '/css/jquery.' . $_widgetName . '-min.css',
				$_basePath . '/css/jquery.' . $_widgetName . '.css',
				$_basePath . '/css/ui.' . $_widgetName . '.min.css',
				$_basePath . '/css/ui.' . $_widgetName . '-min.css',
				$_basePath . '/css/ui.' . $_widgetName . '.css',
			);

			foreach ( $_fileList as $_file )
			{
				if ( file_exists( $_file ) )
				{
					$this->pushCssFile( str_replace( $_SERVER['DOCUMENT_ROOT'] . $_yiiBase, '', $_file ) );
					break;
				}
			}

			//	Any other css?
			foreach ( PS::nvl( $this->extraCssFiles, array() ) as $_cssFile )
				$this->pushCssFile( str_replace( $_SERVER['DOCUMENT_ROOT'] . $_yiiBase, '', $_basePath . DIRECTORY_SEPARATOR . $_cssFile ) );

			//	Clear 'em out.
			$this->extraScriptFiles = $this->extraCssFiles = null;
		}

		//	Daddy...
		parent::registerClientScripts();

		//	Auto register our script
		if ( $this->autoRegister )
		{
			$this->registerWidgetScript();
			$this->autoRegister = false;
		}

		//	Don't forget subclasses
		return PS::_cs();
	}

	//********************************************************************************
	//* Private methods
	//********************************************************************************

	/**
	* Generates the javascript code for the widget
	*
	* @return string
	*/
	protected function generateJavascript( $targetSelector = null, $options = null, $insertBeforeOptions = null )
	{
		//	Fix up the button image if wanted
		if ( $this->widgetName == 'datepicker' && $this->hasOption( 'buttonImage' ) && $this->buttonImage === true )
			$this->buttonImage = $this->getExternalLibraryUrl() . '/jqui/js/images/calendar.gif';

		$_widgetMethodName = PS::nvl( $this->widgetMethodName, $this->widgetName );

		//	Get the options...
		$_options = ( null !== $options ? $options : $this->makePublicOptions() );
		$_targetSelector = $this->getTargetSelector( $targetSelector );
		$_scriptId = 'jQuery' . ( null !== $_targetSelector ? "('{$_targetSelector}')" : '' );

		//	Jam something in front of options?
		if ( null !== $insertBeforeOptions )
		{
			$_staticOptions = $insertBeforeOptions;
			if ( ! empty( $_options ) ) $_staticOptions .= ", {$_options}";
			$_options = $_staticOptions;
		}

		$this->script =<<<CODE
{$_scriptId}.{$_widgetMethodName}({$_options});
CODE;

		return $this->script;
	}

	/**
	* Determines the target CSS selector for this widget
	*
	* @access protected
	* @since psYiiExtensions v1.0.5
	* @param string $targetSelector The CSS selector to target, allows you to override option settings
	* @return string
	*/
	protected function getTargetSelector( $targetSelector = null )
	{
		$_scriptId = null;

		//	Get the target. Passed in class overrides all...
		if ( null != $targetSelector )
		{
			//	Add a period if one is not there, assume it's a class...
			if ( $targetSelector[0] != '.' && $targetSelector != '#' ) $targetSelector = ".{$targetSelector}";
			$_scriptId = $targetSelector;
		}
		else
		{
			//	Do we have a target element?
			if ( $this->hasOption( 'target' ) && $this->target == '_NONE_' )
				$_scriptId = null;
			else if ( ! empty( $this->target ) )
				$_scriptId = $this->target;
			else
				$_scriptId = "#{$this->id}";
		}

		//	Return the selector
		return $_scriptId;
	}

	//********************************************************************************
	//* Static methods
	//********************************************************************************

	/**
	* Constructs and returns a jQuery widget
	*
	* The options passed in are dynamically added to the options array and will be accessible
	* and modifiable as normal (.i.e. $this->theme, $this->baseUrl, etc.)
	*
	* @param string $name The type of jq widget to create
	* @param array $options The options for the widget
	* @param string $sClass The class of the calling object if different
	* @return CPSjQueryWidget
	*/
	public static function create( $name = null, array $options = array() )
	{
		//	Instantiate...
		$_class = PS::o( $options, 'class', __CLASS__, true );
		$_widget = new $_class();

		//	Set default options...
		$_widget->widgetName = $name;
		$_widget->target = PS::o( $options, 'target', null, true );
		$_widget->id = $_widget->name = PS::o( $options, 'id', $name );
		$_widget->name = PS::o( $options, 'name', $_widget->id );

		if ( PS::o( $options, 'naked', ( $_class == __CLASS__ ) ) )
		{
			$_widget->locateScript = true;
			$_widget->autoRegister = true;
		}

		return $_widget->finalizeCreate( $options );
	}

	/**
	* Finalize the creation of a widget
	*
	* This allows subclasses to initialize their class then finalize the creation here.
	*
	* @param CPSjQueryWidget $oWidget The widget to finalize
	* @param array $options Options for this widget
	* @return CPSjQueryWidget
	*/
	protected function finalizeCreate( $options = array() )
	{
		//	Initialize the widget
		$this->init();

		//	Set variable options...
		if ( is_array( $options ) )
		{
			$_baseUrl = $this->baseUrl;

			//	Check for scripts...
			foreach ( PS::o( $options, '_scripts', array(), true ) as $_scriptFile )
				$this->registerWidgetScript( $_scriptFile );

			//	Check for scripts...
			foreach ( PS::o( $options, '_scriptFiles', array(), true ) as $_scriptFile )
				$this->pushScriptFile( $_baseUrl . $_scriptFile );

			//	Check for css...
			foreach ( PS::o( $options, '_cssFiles', array(), true ) as $_cssFile )
				$this->pushCssFile( $_baseUrl . $_cssFile );

			//	Now process the rest of the options...
			foreach ( $options as $_key => $_value )
				$this->addOption( $_key, $_value );
		}

		//	Does user want us to run it?
		if ( $this->autoRun )
			$this->run();

		//	And return...
		return $this;
	}
}
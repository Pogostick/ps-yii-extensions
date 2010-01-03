<?php
/*
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * The ultimate wrapper for any jQuery widget
 * 
 * @package 	psYiiExtensions
 * @subpackage 	widgets
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id$
 * @since 		v1.0.0
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
	//* Member variables
	//********************************************************************************

	/**
	* Any additional widget scripts
	* 
	* @var array
	*/
	protected $m_arScripts = array();

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
				'widgetName_' => 'string:::true',
				'target_' => 'string:::true',
			)
		);
	}
	
	/**
	* Returns the external url that was published.
	* @return string
	* @static
	*/
	public static function getExternalLibraryUrl()
	{
		return Yii::app()->getAssetManager()->getPublishedUrl( Yii::getPathOfAlias( 'pogostick.external' ), true );
	}
	
	/**
	* Returns the path that was published.
	* @return string
	* @static
	*/
	public static function getExternalLibraryPath()
	{
		return Yii::app()->getAssetManager()->getPublishedPath( Yii::getPathOfAlias( 'pogostick.external' ), true );
	}
	
	/**
	* Adds a user script to the output array
	* 
	* @param array $arScript
	*/
	public function addScripts( $arScripts = array() )
	{
		foreach ( $arScripts as $_sScript )
			$this->m_arScripts[] = $_sScript;
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
		$this->registerClientScripts();

		//	Generate the HTML if available
		echo $this->generateHtml();
	}

	/**
	* Registers the needed CSS and JavaScript.
	* This method DOES NOT call generateJavascript()
	* @param boolean If true, system will try to find jquery plugins based on the pattern jquery.<plugin-name[.min].js
	* @returns CClientScript The current app's ClientScript object
	*/
	public function registerClientScripts( $bLocateScript = false )
	{
		//	Additional scripts, let dad load them.
		if ( ! empty( $this->m_arScripts ) && is_array( $this->m_arScripts ) )
		{
			foreach ( $this->m_arScripts as $_sScript )
				$this->pushScriptFile( $_sScript );
		}
		
		//	Daddy...
		parent::registerClientScripts();

		//	Do we have a registered script?
		if ( $bLocateScript )
		{
			//	Try and auto-find script file...
			$_sFilePath = $this->getExternalLibraryPath() . '/jquery-plugins/' . $this->name . '/jquery.' . $this->name;
			$_sFileUrl = $this->getExternalLibraryUrl() . '/jquery-plugins/' . $this->name . '/jquery.' . $this->name;

			//	See if we have such a plug-in
			if ( file_exists(  $_sFilePath . '.min.js' ) )
				PS::_rsf( $_sFileUrl . '.min.js', CClientScript::POS_HEAD );
			//	Try non-minimized version...
			else if ( file_exists( $_sFilePath . '.js' ) )
				PS::_rsf( $_sFileUrl . '.js', CClientScript::POS_HEAD );
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
	protected function generateJavascript( $sTargetSelector = null, $arOptions = null, $sInsertBeforeOptions = null )
	{
		//	Fix up the button image if wanted
		if ( $this->widgetName == 'datepicker' && $this->hasOption( 'buttonImage' ) && $this->buttonImage === true )
			$this->buttonImage = $this->getExternalLibraryUrl() . '/jqui/js/images/calendar.gif';
			
		//	Get the options...		
		$_arOptions = ( null != $arOptions ) ? $arOptions : $this->makeOptions();
		$_sId = 'jQuery' . ( ( null != ( $_sTarget = $this->getTargetSelector( $sTargetSelector ) ) ) ? "('{$_sTarget}')" : null );
		
		//	Jam something in front of options?
		if ( null != $sInsertBeforeOptions )
		{
			$_sOptions = $sInsertBeforeOptions;
			if ( ! empty( $_arOptions ) ) $_sOptions .= ", {$_arOptions}";
			$_arOptions = $_sOptions;
		}

		$this->script =<<<CODE
{$_sId}.{$this->widgetName}({$_arOptions});
CODE;

		return $this->script;
	}
	
	/**
	* Determines the target CSS selector for this widget
	* 
	* @access protected
	* @since psYiiExtensions v1.0.5
	* @param string $sTargetSelector The CSS selector to target, allows you to override option settings
	* @returns string
	*/
	protected function getTargetSelector( $sTargetSelector = null )
	{
		$_sId = null;
		
		//	Get the target. Passed in class overrides all...
		if ( null != $sTargetSelector )
		{
			//	Add a period if one is not there, assume it's a class...
			if ( $sTargetSelector[0] != '.' && $sTargetSelector != '#' ) $sTargetSelector = ".{$sTargetSelector}";
			$_sId = $sTargetSelector;
		}
		else
		{
			//	Do we have a target element?
			if ( $this->hasOption( 'target' ) && $this->target == '_NONE_' )
				$_sId = null;
			else if ( ! empty( $this->target ) ) 
				$_sId = $this->target;
			else
				$_sId = "#{$this->id}";
		}
		
		//	Return the selector
		return $_sId;
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
	* @param string $sName The type of jq widget to create
	* @param array $arOptions The options for the widget
	* @param string $sClass The class of the calling object if different
	* @return CPSjQueryWidget
	*/
	public static function create( $sName = null, array $arOptions = array() )
	{
		//	Instantiate...
		$_sClass = PS::o( $arOptions, 'class', null, true );
		$_oWidget = new $_sClass();

		//	Set default options...
		$_oWidget->widgetName = $sName;
		$_oWidget->target = PS::o( $arOptions, 'target', null, true );
		$_oWidget->id = $_oWidget->name = PS::o( $arOptions, 'id', $sName );
		$_oWidget->name = PS::o( $arOptions, 'name', $_oWidget->id );

		return $_oWidget->finalizeCreate( $arOptions );
	}

	/**
	* Finalize the creation of a widget
	* 
	* This allows subclasses to initialize their class then finalize the creation here.
	* 	
	* @param CPSjQueryWidget $oWidget The widget to finalize
	* @param array $arOptions Options for this widget
	* @returns CPSjQueryWidget
	*/
	protected function finalizeCreate( $arOptions = array() )
	{
		//	Initialize the widget
		$this->init();

		//	Set variable options...
		if ( is_array( $arOptions ) )
		{
			//	Check for scripts...
			foreach ( PS::o( $arOptions, '_scripts', array(), true ) as $_sScript ) 
				$this->pushScriptFile( $this->baseUrl . $_sScript );

			//	Check for css...
			foreach ( PS::o( $arOptions, '_cssFiles', array(), true ) as $_sCss ) 
				$this->pushCssFile( $this->baseUrl . $_sCss );

			//	Now process the rest of the options...			
			foreach ( $arOptions as $_sKey => $_oValue ) 
				$this->addOption( $_sKey, $_oValue );
		}
		
		//	Does user want us to run it?
		if ( $this->autoRun ) $this->run();

		//	And return...
		return $this;
	}

}

<?php
/*
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * CPSWidget is the base class for all Pogostick widgets for Yii. This object 
 * is pretty much identical to CPSComponent but offers a little bit of extra functionality.
 * 
 * @package 	psYiiExtensions
 * @subpackage 	base
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id$
 * @since 		v1.0.0
 * 
 * @filesource
 */
class CPSWidget extends CInputWidget implements IPSComponent
{
	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	/**
	* The internal name of the component.
	* @var string
	*/
	protected $m_sInternalName;
	
	/**
	 * Tracks if we have been initialized yet.
	 * @var boolean
	 */
	protected $m_bInitialized = false;
	
	/**
	 * Our behaviors. Cached for speed here...
	 * @var array
	 */
	protected $m_arBehaviorCache = array();

	/**
	* Our CSS files
	* @var array
	*/
	protected $m_arCssFiles = array();
	
	/**
	* Our JS files
	* @var array
	*/
	protected $m_arScriptFiles = array();
	
	//********************************************************************************
	//* Yii Overrides
	//********************************************************************************
	
	/**
	* Constructs a widget
	*/
	public function __construct( $oOwner = null )
	{
		parent::__construct( $oOwner );
		
		//	Log it and check for issues...
		Yii::trace( Yii::t( '{class} constructed', array( "{class}" => get_class( $this ) ) ), 'pogostick.base' );
		
		//	Preinitialize
		$this->preinit();
	}
	
	/**
	 * Preinitialize the component
	 * Override to add your own functionality before init() is called.
	 */
	public function preinit()
	{
		//	Create our internal name
		PS::createInternalName( $this );
		
		//	Attach our default Behavior
		$this->attachBehavior( 'psWidget', 'pogostick.behaviors.CPSWidgetBehavior' );
	}
	
	/**
	* Initialize our component.
	*/
	public function init()
	{
		if ( ! $this->m_bInitialized )
		{
			//	Now call parent's init...
			parent::init();
			
			if ( empty( $this->name ) ) $this->name = $this->m_sInternalName;

			//	Call our behaviors init() method if they exist
			foreach ( $this->m_arBehaviorCache as $_sName )
				$this->asa( $_sName )->init();

			//	Get the id/name of this widget
			list( $this->name, $this->id ) = $this->resolveNameID();

			//	We are now...
			$this->m_bInitialized = true;
		}
	}
	
	/**
	* Pushes a css file onto the page load stack. 
	* @param string $sPath Path of css relative to doc_root
	* @param string $sId
	*/
	public function pushCssFile( $sPath, $sMedia = 'screen' )
	{
		return $this->m_arCssFiles[] = array( $sPath, $sMedia );
	}

	/**
	* Pushes a script onto the page load stack. 
	* 
	* @param string $sPath Path of script relative to doc_root
	* @param integer $iPosition
	* @param string $sId
	*/
	public function pushScriptFile( $sPath, $iPosition = CClientScript::POS_HEAD )
	{
		$this->m_arScriptFiles[] = array( $sPath, $iPosition );
	}

	/***
	* Handles registration of scripts & css files...
	* @returns CClientScript Returns the current applications CClientScript object {@link CWebApplication::getClientScript}
	*/
	public function registerClientScripts()
	{
		//	Register a special CSS file if we have one...
		if ( $this->cssFile ) $this->pushCssFile( $this->cssFile );
			
		//	Load css files and unset from array...
		foreach ( $this->m_arCssFiles as $_sKey => $_arFile )
		{
			PS::_rcf( Yii::app()->baseUrl . $_arFile[0], $_arFile[1] );
			unset( $this->m_arCssFiles[ $_sKey ] );
		}

		//	Load script files and unset from array...
		foreach ( $this->m_arScriptFiles as $_sKey => $_arFile )
		{
			PS::_rsf( Yii::app()->baseUrl . $_arFile[0], $_arFile[1] );
			unset( $this->m_arScriptFiles[ $_sKey ] );
		}
			
		//	Send upstream for convenience
		return PS::_cs();
	}
	
	/**
	 * Registers a widget script.
	 * If no script is provided, the object's generateJavascript() method is called to get the sript.
	 * @param string $sScript
	 * @param integer Where to load the script. See {@link CClientScript} for values.
	 */
	public function registerWidgetScript( $sScript = null, $iWhere = CClientScript::POS_READY )
	{
		if ( null === $sScript ) $sScript = $this->generateJavascript();
		if ( $sScript ) PS::_rs( 'ps_' . md5( __CLASS__ . $this->widgetName . '#' . $this->id . '.' . $this->target . '.' . time() ), $sScript, $iWhere );
	}

	/**
	 * Attaches an Behavior to this component.
	 * We just cache the names here for lookup speed. 
	 * @param string the Behavior's name. It should uniquely identify this Behavior.
	 * @param mixed the Behavior configuration. This is passed as the first parameter to {@link YiiBase::createComponent} to create the Behavior object.
	 * @return IPSBehavior the Behavior object
	 */
	public function attachBehavior( $sName, $oBehavior )
	{
		//	Attach the Behavior at the parent and add options here...
		if ( $_oObject = parent::attachBehavior( $sName, $oBehavior ) )
		{
			//	Add to our cache...
			$this->m_arBehaviorCache[] = $sName;
		}
		
		return $_oObject;
	}
	
	/**
	 * Alias for setOptions
	 * @param array $arConfig
	 * @see setOptions
	 */
	public function configure( $arConfig = array() )
	{
		$this->setOptions( $arConfig );
	}

	/**
	* Given an ID, a unique name is built and returned
	* 
	* @param string $sId
	* @returns string
	*/
	public function getUniqueId( $sId = null )
	{
		return 'ps.' . PS::nvl( $sId, __CLASS__ ) . '.' . self::getNextIdCount();
	}

	//********************************************************************************
	//* Private Methods 
	//********************************************************************************
	
	/**
	* Generates the javascript code for the widget
	* @return string
	*/
	protected function generateJavascript()
	{
	}

	/**
	* Generates the javascript code for the widget
	* @return string
	*/
	protected function generateHtml()
	{
	}

	//********************************************************************************
	//* Interface Requirements
	//********************************************************************************
	
	/**
	 * Get our internal name
	 * @returns string
	 */
	public function getInternalName() { return $this->m_sInternalName; }
	
	/**
	 * Set our internal name
	 * @param string $sName
	 */
	public function setInternalName( $sValue ) { $this->m_sInternalName = $sValue; }
	
	//********************************************************************************
	//* Magic Methods
	//********************************************************************************

	/**
	 * Gets an option from the collection or passes through to parent.
	 * @param string $sName the option, property or event name
	 * @return mixed 
	 * @throws CException if the property or event is not defined
	 * @see __set
	 */
	public function __get( $sName )
	{
		//	Then behaviors
		foreach ( $this->m_arBehaviorCache as $_sBehavior )
		{
			if ( ( $_oBehave = $this->asa( $_sBehavior ) ) instanceof IPSOptionContainer && $_oBehave->contains( $sName ) )
				return $_oBehave->getValue( $sName );
		}

		//	Try daddy...
		return parent::__get( $sName );
	}

	/**
	 * Sets value of a component option or property.
	 * @param string $sName the property, option or event name
	 * @param mixed $oValue the property value or callback
	 * @throws CException if the property/event is not defined or the property is read only.
	 * @see __get
	 */
	public function __set( $sName, $oValue )
	{
		//	Then behaviors
		foreach ( $this->m_arBehaviorCache as $_sBehavior )
		{
			if ( ( $_oBehave = $this->asa( $_sBehavior ) ) instanceof IPSOptionContainer && $_oBehave->contains( $sName ) )
				return $_oBehave->setValue( $sName, $oValue );
		}

		//	Let parent take a stab. He'll check getter/setters and Behavior methods
		return parent::__set( $sName, $oValue );
	}

	/**
	 * Test to see if an option is set.
	 * @param string $sName
	 */
	public function __isset( $sName )
	{
		//	Then behaviors
		foreach ( $this->m_arBehaviorCache as $_sBehavior )
		{
			if ( ( $_oBehave = $this->asa( $_sBehavior ) ) instanceof IPSOptionContainer && $_oBehave->contains( $sName ) )
				return $_oBehave->getValue( $sName ) !== null;
		}

		return parent::__isset( $sName );
	}
	
	/**
	 * Unset an option
	 * @param string $sName
	 */
	public function __unset( $sName )
	{
		//	Check my options first...
		if ( ! $this->m_oOptions->contains( $sName ) ) 
			$this->unsetOption( $sName );
		else
			//	Try dad
			parent::__unset( $sName );
	}
	
	/**
	 * Calls the named method which is not a class method.
	 * Do not call this method. This is a PHP magic method that we override
	 * @param string $sName The method name
	 * @param array $arParams The method parameters
	 * @throws CPSOptionException if the property/event is not defined or the property is read only.
	 * @see __call
	 * @return mixed The method return value
	 */
	public function __call( $sName, $arParams )
	{
		$_oEvent = null;
		
		try
		{
			//	Look for behavior methods
			foreach ( $this->m_arBehaviorCache as $_sBehavior )
			{
				if ( $_oBehave = $this->asa( $_sBehavior ) )
				{
					if ( method_exists( $_oBehave, $sName ) )
						return call_user_func_array( array( $_oBehave, $sName ), $arParams ); 
				}
			}
		}
		catch ( CPSOptionException $_ex ) { /* Ignore and pass through */ }

		//	Pass on to dad
		return parent::__call( $sName, $arParams );
	}

	//********************************************************************************
	//* Statics 
	//********************************************************************************
	
	/**
	* Constructs and returns a widget
	* 
	* The options passed in are dynamically added to the options array and will be accessible 
	* and modifiable as normal (.i.e. $this->theme, $this->baseUrl, etc.)
	* 
	* @param string $sName The type of widget to create
	* @param array $arOptions The options for the widget
	* @return CPSWidget
	*/
	public static function create( $sName = null, array $arOptions = array() )
	{
		//	Instantiate...
		$_sName = PS::nvl( $sName, ( version_compare( PHP_VERSION, '5.3.0' ) >= 0 ) ? get_called_class() : $sName );
		$_sClass = PS::o( $arOptions, 'class', ( version_compare( PHP_VERSION, '5.3.0' ) >= 0 ) ? get_called_class() : $_sName, true );
 		$_oWidget = new $_sClass();
		$_oWidget->widgetName = $_sName;
		$_oWidget->id = $_oWidget->name = PS::o( $arOptions, 'id', $_sName );
		$_oWidget->name = PS::o( $arOptions, 'name', $_oWidget->id );

		//	Push any optional scripts
		foreach ( PS::o( $arOptions, '_scripts', array(), true ) as $_sScript ) 
			$_oWidget->pushScriptFile( $_oWidget->baseUrl . $_sScript );
				
		//	And CSS
		foreach ( PS::o( $arOptions, '_cssFiles', array(), true ) as $_sCss ) 
			$_oWidget->pushCssFile( $_oWidget->baseUrl . $_sCss);

		//	Now process the rest of the options...
		$_oWidget->addOptions( $arOptions );

		//	Initialize our widget
		$_oWidget->init();
		
		//	And run it...
		if ( $_oWidget->autoRun )
			$_oWidget->run();

		//	And return...
		return $_oWidget;
	}

}
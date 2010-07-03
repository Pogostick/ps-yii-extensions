<?php
/*
 * This file is part of the psYiiExtensions package.
 *
 * @copyright Copyright &copy; 2009-2010 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * CPSComponent is the base class for all psYiiExtensions.
 * It contains functionality to call behavior methods without the need for chaining.
 * All PSComponents have a preinit() method that is called during construction.
 *
 * @package 	psYiiExtensions
 * @subpackage 	base
 *
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN $Id$
 * @since 		v1.0.0
 *
 * @filesource
 *
 * @property-read string $internalName The internal name of the component.
 */
class CPSComponent extends CApplicationComponent implements IPSComponent
{
	//********************************************************************************
	//* Properties
	//********************************************************************************

	/**
	* The internal name of the component.
	* @var string
	*/
	protected $m_sInternalName;

	/**
	 * Tracks the status of debug mode for component
	 * @var boolean Enable/disable debug mode
	 */
	protected $m_bDebugMode = false;
	public function getDebugMode() { return $this->m_bDebugMode; }
	public function setDebugMode( $bValue ) { $this->m_bDebugMode = $bValue; }

	//********************************************************************************
	//* Member Variables
	//********************************************************************************

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

	//********************************************************************************
	//* Yii Overrides
	//********************************************************************************

	/**
	* Constructs a component.
	*/
	public function __construct( $arConfig = array() )
	{
		//	No parent constructor

		//	Log it and check for issues...
//		CPSLog::trace( 'pogostick.base', '{class} constructed', array( "{class}" => get_class( $this ) ) );

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
		$this->attachBehavior( 'psComponent', 'pogostick.behaviors.CPSComponentBehavior' );
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

			//	Call our behaviors init()
			foreach ( $this->m_arBehaviorCache as $_sName )
				$this->asa( $_sName )->init();

			//	We are now...
			$this->m_bInitialized = true;
		}
	}

	/**
	 * Attaches an Behavior to this component.
	 * This method will create the Behavior object based on the given
	 * configuration. After that, the Behavior object will be initialized
	 * by calling its {@link IPSBehavior::attach} method.
	 *
	 * @param string $sName the Behavior's name. It should uniquely identify this Behavior.
	 * @param mixed $oBehavior the Behavior configuration. This is passed as the first parameter to {@link YiiBase::createComponent} to create the Behavior object.
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
		//	Ours?
		if ( $this->hasProperty( $sName ) )
			return $this->{$sName};

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
		//	Ours?
		if ( $this->hasProperty( $sName ) )
			return $this->{$sName} = $oValue;

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
		//	Then behaviors
		foreach ( $this->m_arBehaviorCache as $_sBehavior )
		{
			if ( ( $_oBehave = $this->asa( $_sBehavior ) ) instanceof IPSOptionContainer && $_oBehave->contains( $sName ) )
			{
				$_oBehave->unsetOption( $sName );
				return;
			}
		}

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

	/**
	 * Checks if a component has an attached behavior
	 * @param string $sClass
	 * @returns boolean
	 */
	public function hasBehavior( $sClass )
	{
		//	Look for behavior methods
		foreach ( $this->m_arBehaviorCache as $_sBehavior )
		{
			if ( null !== ( $_oBehave = $this->asa( $_sBehavior ) ) )
			{
				if ( $_oBehav instanceof $sClass )
				return true;
			}

			//	Check for nicknames...
			if ( $sClass == $_sBehavior )
				return true;
		}

		//	Nope?
		return false;
	}

	/**
	 * Outputs a debug string if in debug mode.
	 * @param <type> $sMessage The message
	 * @param <type> $sCategory The category/method of the output
	 * @param <type> $sRoute The destination of output. Can be 'echo', 'trace|info|error|debug|etc...', 'http', 'firephp'
	 */
	public function _debug( $sMessage, $sCategory = null, $sRoute = null )
	{
		if ( $this->m_bDebugMode )
			echo $sMessage . '<BR />';
	}

}
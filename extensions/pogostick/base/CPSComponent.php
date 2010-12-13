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
 * @version 	SVN $Id: CPSComponent.php 405 2010-10-21 21:44:02Z jerryablan@gmail.com $
 * @since 		v1.0.0
 *
 * @filesource
 *
 * @property-read string $internalName The internal name of the component.
 * @property boolean $debugMode Enable trace-level debugging
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
	protected $_internalName;
	public function getInternalName() { return $this->_internalName; }
	public function setInternalName( $value ) { $this->_internalName = $value; }

	/**
	 * Tracks the status of debug mode for component
	 * @var boolean Enable/disable debug mode
	 */
	protected $_debugMode = false;
	public function getDebugMode() { return $this->_debugMode; }
	public function setDebugMode( $value ) { $this->_debugMode = $value; }

	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	/**
	 * Tracks if this component has been initialized yet.
	 * @var boolean
	 */
	protected $_initialized = false;

	/**
	 * Our behaviors. Cached for speed here...
	 * @var array
	 */
	protected $_behaviorList = array();

	//********************************************************************************
	//* Yii Overrides
	//********************************************************************************

	/**
	* Constructs a component.
	*/
	public function __construct( $config = array() )
	{
		//	Set any properties via standard config array
		if ( is_array( $config ) && ! empty( $config ) )
			$this->_loadConfiguration( $config );

		//	Preinitialize, called before afterConstruct
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
		if ( ! $this->_initialized )
		{
			//	Now call parent's init...
			parent::init();

			//	Call our behaviors init()
			foreach ( $this->_behaviorList as $_name )
				$this->asa( $_name )->init();

			//	We are now...
			$this->_initialized = true;
		}
	}

	/**
	 * Attaches an Behavior to this component.
	 * This method will create the Behavior object based on the given
	 * configuration. After that, the Behavior object will be initialized
	 * by calling its {@link IPSBehavior::attach} method.
	 *
	 * @param string $name the Behavior's name. It should uniquely identify this Behavior.
	 * @param mixed $behavior the Behavior configuration. This is passed as the first parameter to {@link YiiBase::createComponent} to create the Behavior object.
	 * @return IPSBehavior the Behavior object
	 */
	public function attachBehavior( $name, $behavior )
	{
		//	Attach the Behavior at the parent and add options here, then cache
		if ( null !== ( $_component = parent::attachBehavior( $name, $behavior ) ) )
			$this->_behaviorList[] = $name;

		return $_component;
	}

	/**
	 * Alias for setOptions
	 * @param array $optionList
	 * @see setOptions
	 */
	public function configure( $optionList = array() )
	{
		$this->setOptions( $optionList );
	}

	/**
	 * Determines whether a property can be read.
	 * A property can be read if the class has a getter method
	 * for the property name. Note, property name is case-insensitive.
	 * @param string the property name
	 * @return boolean whether the property can be read
	 * @see canSetProperty
	 */
	public function canGetProperty( $name )
	{
		parent::canGetProperty( $name ) || $this->hasProperty( $name );
	}
	
	/**
	 * Determines whether a property can be set.
	 * A property can be written if the class has a setter method
	 * for the property name. Note, property name is case-insensitive.
	 * @param string the property name
	 * @return boolean whether the property can be written
	 * @see canGetProperty
	 */
	public function canSetProperty( $name )
	{
		parent::canSetProperty( $name ) || $this->hasProperty( $name );
	}

	/**
	 * Determines whether a property is defined.
	 * A property is defined if there is a getter or setter method
	 * defined in the class. Note, property names are case-insensitive.
	 * @param string the property name
	 * @return boolean whether the property is defined
	 * @see canGetProperty
	 * @see canSetProperty
	 */
	public function hasProperty( $name )
	{
		if ( parent::hasProperty( $name ) )
			return true;

		foreach ( $this->_behaviorList as $_behaviorName )
		{
			if ( ( $_behavior = $this->asa( $_behaviorName ) ) instanceof IPSOptionContainer && $_behavior->contains( $name ) && $_behavior->getEnabled() )
				return true;
		}
		
		return false;
	}

	//********************************************************************************
	//* Magic Methods
	//********************************************************************************

	/**
	 * Gets an option from the collection or passes through to parent.
	 * @param string $name the option, property or event name
	 * @return mixed
	 * @throws CException if the property or event is not defined
	 * @see __set
	 */
	public function __get( $name )
	{
		try
		{
			return parent::__get( $name );
		}
		catch ( CException $_ex )
		{
			//	Didn't work. Try our behavior cache...
			foreach ( $this->_behaviorList as $_behaviorName )
			{
				if ( ( $_behavior = $this->asa( $_behaviorName ) ) instanceof IPSOptionContainer && $_behavior->contains( $name ) )
					return $_behavior->getValue( $name );
			}

			//	If we get here, then bubble the exception...
			throw $_ex;
		}
	}

	/**
	 * Sets value of a component option or property.
	 * @param string $name the property, option or event name
	 * @param mixed $oValue the property value or callback
	 * @throws CException if the property/event is not defined or the property is read only.
	 * @see __get
	 */
	public function __set( $name, $value )
	{
		try
		{
			parent::__set( $name, $value );
		}
		catch ( CException $_ex )
		{
			//	Didn't work. Try our behavior cache...
			foreach ( $this->_behaviorList as $_behaviorName )
			{
				if ( ( $_behavior = $this->asa( $_behaviorName ) ) instanceof IPSOptionContainer && $_behavior->contains( $name ) )
					return $_behavior->setValue( $name, $value );
			}

			//	If we get here, then bubble the exception...
			throw $_ex;
		}
	}

	/**
	 * Test to see if an option is set.
	 * @param string $name
	 */
	public function __isset( $name )
	{
		//	Then behaviors
		foreach ( $this->_behaviorList as $_behavior )
		{
			if ( ( $_component = $this->asa( $_behavior ) ) instanceof IPSOptionContainer && $_component->contains( $name ) )
				return $_component->getValue( $name ) !== null;
		}

		return parent::__isset( $name );
	}

	/**
	 * Unset an option
	 * @param string $name
	 */
	public function __unset( $name )
	{
		//	Then behaviors
		foreach ( $this->_behaviorList as $_behavior )
		{
			if ( ( $_component = $this->asa( $_behavior ) ) instanceof IPSOptionContainer && $_component->contains( $name ) )
			{
				$_component->unsetOption( $name );
				return;
			}
		}

		//	Try dad
		parent::__unset( $name );
	}

	/**
	 * Calls the named method which is not a class method.
	 * Do not call this method. This is a PHP magic method that we override
	 * @param string $name The method name
	 * @param array $parameterList The method parameters
	 * @throws CPSOptionException if the property/event is not defined or the property is read only.
	 * @see __call
	 * @return mixed The method return value
	 */
	public function __call( $name, $parameterList )
	{
		$_oEvent = null;

		try
		{
			//	Look for behavior methods
			foreach ( $this->_behaviorList as $_behavior )
			{
				if ( $_component = $this->asa( $_behavior ) )
				{
					if ( method_exists( $_component, $name ) )
						return call_user_func_array( array( $_component, $name ), $parameterList );
				}
			}
		}
		catch ( CPSOptionException $_ex ) { /* Ignore and pass through */ }

		//	Pass on to dad
		return parent::__call( $name, $parameterList );
	}

	/**
	 * Checks if a component has an attached behavior
	 * @param string $class
	 * @returns boolean
	 */
	public function hasBehavior( $class )
	{
		//	Look for behavior methods
		foreach ( $this->_behaviorList as $_behavior )
		{
			if ( null !== ( $_component = $this->asa( $_behavior ) ) )
			{
				if ( $_component instanceof $class )
				return true;
			}

			//	Check for nicknames...
			if ( $class == $_behavior )
				return true;
		}

		//	Nope?
		return false;
	}

	/**
	 * Outputs a debug string if in debug mode.
	 * @param <type> $message The message
	 * @param <type> $category The category/method of the output
	 * @param <type> $route The destination of output. Can be 'echo', 'trace|info|error|debug|etc...', 'http', 'firephp'
	 */
	public function _debug( $message, $category = null, $route = null )
	{
		if ( $this->_debugMode )
			echo $message . '<BR />';
	}

	//********************************************************************************
	//* Private Methods
	//********************************************************************************

	/**
	 * Loads an array into properties if they exist.
	 * @param array $optionList
	 */
	protected function _loadConfiguration( $optionList = array(), $overwriteExisting = true )
	{
		//	Make a copy for posterity
		if ( property_exists( $this, '_optionList' ) )
		{
			if ( $overwriteExisting || empty( $this->_optionList ) )
				$this->_optionList = $optionList;
			else
				$this->_optionList = array_merge( $this->_optionList, $optionList );
		}

		try
		{
			foreach ( $optionList as $_option => $_value )
			{
				try
				{
					//	See if __set has a better time with this...
					$this->{$_option} = $_value;
				}
				catch ( Exception $_ex )
				{
					//	Completely ignore errors...
				}
			}
		}
		catch ( Exception $_ex )
		{
			CPSLog::error( __METHOD__, 'Error while loading configuration options: ' . $_ex->getMessage() );
		}
	}
}
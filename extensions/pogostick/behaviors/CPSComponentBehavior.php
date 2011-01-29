<?php
/*
 * This file is part of the psYiiExtensions package.
 *
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 * @filesource
 */
/**
 * Provides a base for the pYe behaviors
 *
 * @package 	psYiiExtensions
 * @subpackage 	behaviors
 *
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id: CPSComponentBehavior.php 405 2010-10-21 21:44:02Z jerryablan@gmail.com $
 * @since 		v1.0.1
 */
class CPSComponentBehavior extends CBehavior implements IPSOptionContainer, IPSBehavior
{
	//********************************************************************************
	//* Properties
	//********************************************************************************

	/**
	* The internal name of the component.
	* @var string
	*/
	protected $_internalName;

	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	/**
	 * @var CPSOptionCollection $_optionList Our options
	 */
	protected $_optionList;

	/**
	 * @var boolean $initialized Tracks if we have been initialized yet.
	 */
	protected $_initialized = false;

	/**
	 * @var boolean $addIfMissing If option is not found, it is added
	 */
	protected $_addIfMissing = true;

	//********************************************************************************
	//* Yii Overrides
	//********************************************************************************

	/**
	* Constructs a component.
	*/
	public function __construct( $options = array() )
	{
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
		CPSHelperBase::createInternalName( $this );

		//	Create our option collection
		$this->_optionList = new CPSOptionCollection();

		//	Add our options...
		$this->addOptions( self::getBaseOptions() );

		//	Set the external library pathn
		if ( ! CPSHelperBase::isCLI() )
			$this->extLibUrl = Yii::app()->getAssetManager()->publish( Yii::getPathOfAlias( 'pogostick.external' ), true );
	}

	/**
	* Initialize our component.
	*/
	public function init()
	{
		$this->_initialized = true;
	}

	/**
	* Returns only the public options
	* @return array A copy of the public options stored...
	* @see getOptions
	*/
	public function getPublicOptions()
	{
		return $this->_optionList->getOptions( true );
	}

	/**
	 * Gets an options value
	 * @param string $key
	 * @param mixed $defaultValue
	 * @return mixed
	 */
	public function getValue( $key, $defaultValue = null )
	{
		return $this->_optionList->getValue( $key, $defaultValue );
	}

	/**
	 * Sets an options value
	 * @param string $key
	 * @param mixed $value
	 * @param boolean $addIfMissing If option is not found, it is added
	 */
	public function setValue( $key, $value = null, $addIfMissing = true )
	{
		$this->_optionList->setValue( $key, $value, $this->_addIfMissing = $addIfMissing );
	}

	/**
	 * Makes a set of options
	 *
	 * @param boolean $publicOnly
	 * @param integer $outputFormat
	 * @param boolean $noCheck
	 * @return mixed
	 */
	public function makeOptions( $publicOnly = true, $outputFormat = CPSHelperBase::OF_JSON, $noCheck = false )
	{
		return CPSOptionHelper::makeOptions( $this, $publicOnly, $outputFormat, $noCheck );
	}

	/**
	 * Makes a set of public options
	 *
	 * @param integer $outputFormat
	 * @param boolean $noCheck
	 * @return mixed
	 */
	public function makePublicOptions( $outputFormat = CPSHelperBase::OF_JSON, $noCheck = false )
	{
		return CPSOptionHelper::makeOptions( $this, true, $outputFormat, $noCheck );
	}

	/**
	 * Merges an array of options into the component options.
	 * You can pass in an array of (key=>value) pairs or an array of {@link CPSOption}s
	 * @param array $optionList
	 * @return mixed
	 */
	public function mergeOptions( $optionList = array() )
	{
		foreach ( $optionList as $_key => $_value )
		{
			if ( $_value instanceof CPSOption )
			{
				$_key = $_value->getName();
				$_value = $_value->getValue();
			}

			$this->setOption( $_key, $_value );
		}

		return $this->getOwner();
	}

	//********************************************************************************
	//* Interface Requirements
	//********************************************************************************

	/**
	 * Get our internal name
	 * @return string
	 */
	public function getInternalName() { return $this->_internalName; }

	/**
	 * Set our internal name
	 * @param string $name
	 */
	public function setInternalName( $sValue ) { $this->_internalName = $sValue; }

	/**
	* Adds an option to the collection.
	* @param string $key
	* @param array $arValue
	* @param bool $bNoSort If set to false, the option array will not be sorted after the addition
	* @see unsetOption
	*/
	public function addOption( $key, $value = null, $definition = null ) { $this->_optionList->addOption( $key, $value, $definition ); }

	/**
	* Add options in bulk
	* @param array $optionList
	* @see setOptions
	* @see getOptions
	*/
	public function addOptions( $options = array() ) { $this->_optionList->addOptions( $options ); }

	/**
	* Retrieves an option value
	* @param string $key
	* @return mixed
	* @see getOptions
	*/
	public function getOption( $key, $defaultValue = null, $unsetAfter = false ) { return $this->_optionList->getOption( $key, $defaultValue, $unsetAfter ); }

	/**
	 * Returns an array of options
	 * @param boolean $publicOnly
	 * @param array $optionFilter
	 * @return array
	 */
	public function getOptions( $publicOnly = false, $optionFilter = array() ) { return $this->_optionList->getOptions( $publicOnly, $optionFilter ); }
	public function getRawOptions( $publicOnly = false, $optionFilter = array() ) { return $this->_optionList->toArray( $publicOnly, $optionFilter ); }
	public function &getOptionsObject() { return $this->_optionList; }

	/**
	* Sets an option
	*
	* @param string $key
	* @param mixed $value
	* @see getOption
	*/
	public function setOption( $key, $value = null ) { $this->_optionList->setValue( $key, $value ); }

	/**
	* Set options in bulk
	*
	* @param array $optionList An array containing option_key => value pairs
	* @param boolean $clearFirst If true, empties array before setting options.
	* @see getOptions
	*/
	public function setOptions( $options = array(), $clearFirst = false ) { $this->_optionList->setOptions( $options, $clearFirst ); }

	/**
	* Unsets a single option
	*
	* @param string $key
	* @param mixed $value
	* @see setOption
	* @see getOption
	*/
	public function unsetOption( $key ) { $this->_optionList->unsetOption( $key ); }

	/**
	* Resets the collection to empty
	*/
	public function clear() { $this->_optionList->clear(); }

	/**
	* Checks if an option exists in the options array...
	*
	* @param string $key
	* @return bool
	* @see setOption
	* @see setOptions
	*/
	public function contains( $key ) { return $this->_optionList->contains( $key ); }
	public function hasOption( $key ) { return $this->contains( $key ); }

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
		return parent::canGetProperty( $name ) || $this->contains( $name );
	}

	/**
	 * Determines whether a property can be read.
	 * A property can be read if the class has a getter method
	 * for the property name. Note, property name is case-insensitive.
	 * @param string the property name
	 * @return boolean whether the property can be read
	 * @see canSetProperty
	 */
	public function canSetProperty( $name )
	{
		return parent::canGetProperty( $name ) || $this->contains( $name );
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
		//	Check my options first...
		if ( $this->_optionList->contains( $name ) )
			return $this->_optionList->getValue( $name );

		//	Try daddy...
		return parent::__get( $name );
	}

	/**
	 * Sets value of a component option or property.
	 * @param string $name the property, option or event name
	 * @param mixed $value the property value or callback
	 * @throws CException if the property/event is not defined or the property is read only.
	 * @see __get
	 */
	public function __set( $name, $value )
	{
		//	Check my options first...
		if ( $this->_optionList->contains( $name ) )
			return $this->_optionList->setValue( $name, $value );

		//	Let parent take a stab. He'll check getter/setters and Behavior methods
		return parent::__set( $name, $value );
	}

	/**
	 * Test to see if an option is set.
	 * @param string $name
	 */
	public function __isset( $name )
	{
		//	Mine first...
		if ( $this->_optionList->contains( $name ) )
			return null !== $this->_optionList->getValue( $name );

		return parent::__isset( $name );
	}

	/**
	 * Unset an option
	 * @param string $name
	 */
	public function __unset( $name )
	{
		//	Check my options first...
		if ( $this->_optionList->contains( $name ) )
			$this->_optionList->setValue( $name );
		else
			//	Try dad
			parent::__unset( $name );
	}

	//********************************************************************************
	//* Events
	//********************************************************************************

	/**
	* Redirect event to owner object
	*
	* This doesn't actually raise an event. What it does is calls the
	* owner's event raiser method. That method will then raise the event.
	*
	* @param string $name The event name
	* @param CPSApiEvent $event The event
	*/
	public function raiseEvent( $name, $event )
	{
		//	Save called name...
		$_originalName = $name;

		//	Handler exists? Call it
		if ( method_exists( $this->getOwner(), $name ) )
			return call_user_func_array( array( $this->getOwner(), $name ), array( $event ) );

		//	See if pre-handler exists...
		if ( 0 == strncasecmp( 'on', $name, 2 ) )
			$name = substr( $name, 2 );

		$name = lcfirst( $name );

		if ( method_exists( $this->getOwner(), $name ) )
			return call_user_func_array( array( $this->getOwner(), $name ), array( $event ) );

		//	Not there? Throw error...
		return parent::raiseEvent( $_originalName, $event );
	}

	/**
	* Logs a message to the application log
	*
	* @param string $message The log message
	* @param string $category The category for this log entry. Defaults to __METHOD__
	* @param string $level The level of this log. Defaults to 'trace'
	*/
	protected function log( $message, $category = __METHOD__, $level = 'trace' )
	{
		return Yii::log( $message, $level, $category );
	}

	/**
	* Log helpers
	*
	* @param string $message The log message
	* @param string $category The category for this log entry. Defaults to __METHOD__
	*/
	protected function logInfo( $message, $category = __METHOD__ ) { $this->log( $message, $category, 'info' ); }
	protected function logError( $message, $category = __METHOD__ ) { $this->log( $message, $category, 'error' ); }
	protected function logWarning( $message, $category = __METHOD__ ) { $this->log( $message, $category, 'warning' ); }
	protected function logTrace( $message, $category = __METHOD__ ) { $this->log( $message, $category, 'trace' ); }

	//********************************************************************************
	//* Private Methods
	//********************************************************************************

	/**
	* Easier on the eyes
	* @access private
	*/
	private function getBaseOptions()
	{
		return(
			array(
				'baseUrl_' => 'string',
				'checkOptions_' => 'bool:true',
				'validOptions_' => 'array:array()',
				'checkCallbacks_' => 'bool:true',
				'validCallbacks_' => 'array:array()',
				'callbacks_' => 'array:array()',
				'extLibUrl_' => 'string:' . DIRECTORY_SEPARATOR,
			)
		);
	}

}
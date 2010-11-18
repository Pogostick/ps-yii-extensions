<?php
/*
 * This file is part of the psYiiExtensions package.
 *
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
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
 *
 * @filesource
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
	 * Our options
	 * @var CPSOptionCollection
	 */
	protected $_optionList;

	/**
	 * Tracks if we have been initialized yet.
	 * @var boolean
	 */
	protected $_initialized = false;

	//********************************************************************************
	//* Yii Overrides
	//********************************************************************************

	/**
	* Constructs a component.
	*/
	public function __construct( $arConfig = array() )
	{
		//	Log it and check for issues...
//		Yii::trace( 'pogostick.behaviors', '{class} constructed', array( "{class}" => get_class( $this ) ) );

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

		//	Create our option collection
		$this->_optionList = new CPSOptionCollection();

		//	Add our options...
		$this->addOptions( self::getBaseOptions() );

		//	Set the external library pathn
		if ( ! PS::isCLI() )
			$this->extLibUrl = Yii::app()->getAssetManager()->publish( Yii::getPathOfAlias( 'pogostick.external' ), true );
	}

	/**
	* Initialize our component.
	*/
	public function init()
	{
		if ( ! $this->_initialized )
		{
			//	We are now...
			$this->_initialized = true;
		}
	}

	/**
	* Returns only the public options
	* @returns array A copy of the public options stored...
	* @see getOptions
	*/
	public function getPublicOptions()
	{
		return $this->_optionList->getOptions( true );
	}

	/**
	 * Gets an options value
	 * @param string $sKey
	 * @param mixed $oDefault
	 * @returns mixed
	 */
	public function getValue( $sKey, $oDefault = null )
	{
		return $this->_optionList->getValue( $sKey, $oDefault );
	}

	/**
	 * Sets an options value
	 * @param string $sKey
	 * @param mixed $value
	 * @param boolean $bAddIfMissing If option is not found, it is added
	 */
	public function setValue( $sKey, $value = null, $bAddIfMissing = true )
	{
		$this->_optionList->setValue( $sKey, $value, $bAddIfMissing );
	}

	/**
	 * Makes a set of options
	 *
	 * @param boolean $bPublicOnly
	 * @param integer $iFormat
	 * @param boolean $bNoCheck
	 * @return mixed
	 */
	public function makeOptions( $bPublicOnly = true, $iFormat = PS::OF_JSON, $bNoCheck = false )
	{
		return CPSOptionHelper::makeOptions( $this, $bPublicOnly, $iFormat, $bNoCheck );
	}

	/**
	 * Makes a set of public options
	 *
	 * @param integer $iFormat
	 * @param boolean $bNoCheck
	 * @return mixed
	 */
	public function makePublicOptions( $iFormat = PS::OF_JSON, $bNoCheck = false )
	{
		return CPSOptionHelper::makeOptions( $this, true, $iFormat, $bNoCheck );
	}

	/**
	 * Merges an array of options into the component options.
	 * You can pass in an array of (key=>value) pairs or an array of {@link CPSOption}s
	 * @param array $optionList
	 * @returns mixed
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
	 * @returns string
	 */
	public function getInternalName() { return $this->_internalName; }

	/**
	 * Set our internal name
	 * @param string $sName
	 */
	public function setInternalName( $sValue ) { $this->_internalName = $sValue; }

	/**
	* Adds an option to the collection.
	* @param string $sKey
	* @param array $arValue
	* @param bool $bNoSort If set to false, the option array will not be sorted after the addition
	* @see unsetOption
	*/
	public function addOption( $sKey, $value = null, $oPattern = null ) { $this->_optionList->addOption( $sKey, $value, $oPattern ); }

	/**
	* Add options in bulk
	* @param array $optionList
	* @see setOptions
	* @see getOptions
	*/
	public function addOptions( array $optionList ) { $this->_optionList->addOptions( $optionList ); }

	/**
	* Retrieves an option value
	* @param string $sKey
	* @return mixed
	* @see getOptions
	*/
	public function getOption( $sKey, $oDefault = null, $bUnset = false ) { return $this->_optionList->getOption( $sKey, $oDefault, $bUnset ); }

	/**
	 * Returns an array of options
	 * @param boolean $bPublicOnly
	 * @param array $arOnlyThese
	 * @return array
	 */
	public function getOptions( $bPublicOnly = false, $arOnlyThese = array() ) { return $this->_optionList->getOptions( $bPublicOnly, $arOnlyThese ); }
	public function getRawOptions( $bPublicOnly = false, $arOnlyThese = array() ) { return $this->_optionList->toArray( $bPublicOnly, $arOnlyThese ); }
	public function &getOptionsObject() { return $this->_optionList; }

	/**
	* Sets an option
	*
	* @param string $sKey
	* @param mixed $value
	* @see getOption
	*/
	public function setOption( $sKey, $value ) { $this->_optionList->setValue( $sKey, $value ); }

	/**
	* Set options in bulk
	*
	* @param array $optionList An array containing option_key => value pairs
	* @param boolean If true, empties array before setting options.
	* @see getOptions
	*/
	public function setOptions( array $optionList, $bClearFirst = false ) { $this->_optionList->setOptions( $optionList, $bClearFirst ); }

	/**
	* Unsets a single option
	*
	* @param string $sKey
	* @param mixed $value
	* @see setOption
	* @see getOption
	*/
	public function unsetOption( $sKey ) { $this->_optionList->unsetOption( $sKey ); }

	/**
	* Resets the collection to empty
	*/
	public function clear() { $this->_optionList->clear(); }

	/**
	* Checks if an option exists in the options array...
	*
	* @param string $sKey
	* @return bool
	* @see setOption
	* @see setOptions
	*/
	public function contains( $sKey ) { return $this->_optionList->contains( $sKey ); }
	public function hasOption( $sKey ) { return $this->contains( $sKey ); }

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
	 * @param string $sName the option, property or event name
	 * @return mixed
	 * @throws CException if the property or event is not defined
	 * @see __set
	 */
	public function __get( $sName )
	{
		//	Check my options first...
		if ( $this->_optionList->contains( $sName ) )
			return $this->_optionList->getValue( $sName );

		//	Try daddy...
		return parent::__get( $sName );
	}

	/**
	 * Sets value of a component option or property.
	 * @param string $sName the property, option or event name
	 * @param mixed $value the property value or callback
	 * @throws CException if the property/event is not defined or the property is read only.
	 * @see __get
	 */
	public function __set( $sName, $value )
	{
		//	Check my options first...
		if ( $this->_optionList->contains( $sName ) )
			return $this->_optionList->setValue( $sName, $value );

		//	Let parent take a stab. He'll check getter/setters and Behavior methods
		return parent::__set( $sName, $value );
	}

	/**
	 * Test to see if an option is set.
	 * @param string $sName
	 */
	public function __isset( $sName )
	{
		//	Mine first...
		if ( $this->_optionList->contains( $sName ) )
			return null !== $this->_optionList->getValue( $sName );

		return parent::__isset( $sName );
	}

	/**
	 * Unset an option
	 * @param string $sName
	 */
	public function __unset( $sName )
	{
		//	Check my options first...
		if ( $this->_optionList->contains( $sName ) )
			$this->_optionList->setValue( $sName );
		else
			//	Try dad
			parent::__unset( $sName );
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
	* @param string $sName The event name
	* @param CPSApiEvent $oEvent The event
	*/
	public function raiseEvent( $sName, $oEvent )
	{
		//	Save called name...
		$_sOrigName = $sName;

		//	Handler exists? Call it
		if ( method_exists( $this->getOwner(), $sName ) )
			return call_user_func_array( array( $this->getOwner(), $sName ), array( $oEvent ) );

		//	See if pre-handler exists...
		if ( 0 == strncasecmp( 'on', $sName, 2 ) )
			$sName = substr( $sName, 2 );

		$sName = lcfirst( $sName );

		if ( method_exists( $this->getOwner(), $sName ) )
			return call_user_func_array( array( $this->getOwner(), $sName ), array( $oEvent ) );

		//	Not there? Throw error...
		return parent::raiseEvent( $_sOrigName, $oEvent );
	}

	/**
	* Logs a message to the application log
	*
	* @param string $sMessage The log message
	* @param string $sCategory The category for this log entry. Defaults to __METHOD__
	* @param string $sLevel The level of this log. Defaults to 'trace'
	*/
	protected function log( $sMessage, $sCategory = __METHOD__, $sLevel = 'trace' )
	{
		return Yii::log( $sMessage, $sLevel, $sCategory );
	}

	/**
	* Log helpers
	*
	* @param string $sMessage The log message
	* @param string $sCategory The category for this log entry. Defaults to __METHOD__
	*/
	protected function logInfo( $sMessage, $sCategory = __METHOD__ ) { $this->log( $sMessage, $sCategory, 'info' ); }
	protected function logError( $sMessage, $sCategory = __METHOD__ ) { $this->log( $sMessage, $sCategory, 'error' ); }
	protected function logWarning( $sMessage, $sCategory = __METHOD__ ) { $this->log( $sMessage, $sCategory, 'warning' ); }
	protected function logTrace( $sMessage, $sCategory = __METHOD__ ) { $this->log( $sMessage, $sCategory, 'trace' ); }

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

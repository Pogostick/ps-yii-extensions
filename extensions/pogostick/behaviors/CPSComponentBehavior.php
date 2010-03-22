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
 * @version 	SVN: $Id$
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
	protected $m_sInternalName;
	
	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	/**
	 * Our options
	 * @var CPSOptionCollection
	 */
	protected $m_oOptions;
		
	/**
	 * Tracks if we have been initialized yet.
	 * @var boolean
	 */
	protected $m_bInitialized = false;
	
	//********************************************************************************
	//* Yii Overrides
	//********************************************************************************
	
	/**
	* Constructs a component.
	*/
	public function __construct( $arConfig = array() )
	{
		//	Log it and check for issues...
		Yii::trace( 'pogostick.behaviors', '{class} constructed', array( "{class}" => get_class( $this ) ) );
		
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
		$this->m_oOptions = new CPSOptionCollection();
		
		//	Add our options...
		$this->addOptions( self::getBaseOptions() );
		
		//	Set the external library path
		$this->extLibUrl = Yii::app()->getAssetManager()->publish( Yii::getPathOfAlias( 'pogostick.external' ), true );
	}
	
	/**
	* Initialize our component.
	*/
	public function init()
	{
		if ( ! $this->m_bInitialized )
		{
			//	We are now...
			$this->m_bInitialized = true;
		}
	}
	
	/**
	* Returns only the public options
	* @returns array A copy of the public options stored...
	* @see getOptions
	*/
	public function getPublicOptions() 
	{ 
		return $this->m_oOptions->getOptions( true ); 
	}

	/**
	 * Gets an options value
	 * @param string $sKey
	 * @param mixed $oDefault
	 * @returns mixed
	 */
	public function getValue( $sKey, $oDefault = null )
	{
		return $this->m_oOptions->getValue( $sKey, $oDefault );
	}

	/**
	 * Sets an options value
	 * @param string $sKey
	 * @param mixed $oValue
	 * @param boolean $bAddIfMissing If option is not found, it is added
	 */
	public function setValue( $sKey, $oValue = null, $bAddIfMissing = true )
	{
		$this->m_oOptions->setValue( $sKey, $oValue, $bAddIfMissing );
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
	 * @param array $arOptions
	 * @returns mixed
	 */
	public function mergeOptions( $arOptions = array() )
	{
		foreach ( $arOptions as $_sKey => $_oValue )
		{
			if ( $_oValue instanceof CPSOption )
			{
				$_sKey = $_oValue->getName();
				$_oValue = $_oValue->getValue();
			}

			$this->setOption( $_sKey, $_oValue );
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
	public function getInternalName() { return $this->m_sInternalName; }
	
	/**
	 * Set our internal name
	 * @param string $sName
	 */
	public function setInternalName( $sValue ) { $this->m_sInternalName = $sValue; }
	
	/**
	* Adds an option to the collection.
	* @param string $sKey
	* @param array $arValue
	* @param bool $bNoSort If set to false, the option array will not be sorted after the addition
	* @see unsetOption
	*/
	public function addOption( $sKey, $oValue = null, $oPattern = null ) { $this->m_oOptions->addOption( $sKey, $oValue, $oPattern ); }

	/**
	* Add options in bulk
	* @param array $arOptions
	* @see setOptions
	* @see getOptions
	*/
	public function addOptions( array $arOptions ) { $this->m_oOptions->addOptions( $arOptions ); }

	/**
	* Retrieves an option value
	* @param string $sKey
	* @return mixed
	* @see getOptions
	*/
	public function getOption( $sKey, $oDefault = null, $bUnset = false ) { return $this->m_oOptions->getOption( $sKey, $oDefault, $bUnset ); }

	/**
	 * Returns an array of options
	 * @param boolean $bPublicOnly
	 * @param array $arOnlyThese
	 * @return array
	 */
	public function getOptions( $bPublicOnly = false, $arOnlyThese = array() ) { return $this->m_oOptions->getOptions( $bPublicOnly, $arOnlyThese ); }
	public function getRawOptions( $bPublicOnly = false, $arOnlyThese = array() ) { return $this->m_oOptions->toArray( $bPublicOnly, $arOnlyThese ); }
	public function &getOptionsObject() { return $this->m_oOptions; }

	/**
	* Sets an option
	*
	* @param string $sKey
	* @param mixed $oValue
	* @see getOption
	*/
	public function setOption( $sKey, $oValue ) { $this->m_oOptions->setValue( $sKey, $oValue ); }

	/**
	* Set options in bulk
	*
	* @param array $arOptions An array containing option_key => value pairs
	* @param boolean If true, empties array before setting options.
	* @see getOptions
	*/
	public function setOptions( array $arOptions, $bClearFirst = false ) { $this->m_oOptions->setOptions( $arOptions, $bClearFirst ); }
	
	/**
	* Unsets a single option
	*
	* @param string $sKey
	* @param mixed $oValue
	* @see setOption
	* @see getOption
	*/
	public function unsetOption( $sKey ) { $this->m_oOptions->unsetOption( $sKey ); }

	/**
	* Resets the collection to empty
	*/
	public function clear() { $this->m_oOptions->clear(); }

	/**
	* Checks if an option exists in the options array...
	*
	* @param string $sKey
	* @return bool
	* @see setOption
	* @see setOptions
	*/
	public function contains( $sKey ) { return $this->m_oOptions->contains( $sKey ); }
	public function hasOption( $sKey ) { return $this->contains( $sKey ); }
	
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
		if ( $this->m_oOptions->contains( $sName ) ) 
			return $this->m_oOptions->getValue( $sName );
			
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
		//	Check my options first...
		if ( $this->m_oOptions->contains( $sName ) ) 
			return $this->m_oOptions->setValue( $sName, $oValue );

		//	Let parent take a stab. He'll check getter/setters and Behavior methods
		return parent::__set( $sName, $oValue );
	}

	/**
	 * Test to see if an option is set.
	 * @param string $sName
	 */
	public function __isset( $sName )
	{
		//	Mine first...
		if ( $this->m_oOptions->contains( $sName ) ) 
			return null !== $this->m_oOptions->getValue( $sName );
			
		return parent::__isset( $sName );
	}
	
	/**
	 * Unset an option
	 * @param string $sName
	 */
	public function __unset( $sName )
	{
		//	Check my options first...
		if ( $this->m_oOptions->contains( $sName ) ) 
			$this->m_oOptions->setValue( $sName );
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

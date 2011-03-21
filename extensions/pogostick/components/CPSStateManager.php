<?php
/**
 * This file is part of the Pogostick Yii Extension library
 *
 * @copyright Copyright &copy; 2009-2010 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */
/**
 * CPSStateManager
 * A replacement for the stock Yii CWebUser state manager
 *
 * @package 	psYiiExtensions
 * @subpackage	components
 *
 * @author		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN $Id$
 * @since		v1.2.0
 *
 * @filesource
 */
class CPSStateManager extends CPSComponent
{
	//********************************************************************************
	//* Constants
	//********************************************************************************

	/**
	 * The base prefix for all state keys
	 */
	const BASE_KEY_PREFIX = 'stateManager';
	/**
	 * The base prefix for all flash keys
	 */
	const BASE_FLASH_KEY_PREFIX = 'flashMessage';

	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	/**
	 * @var boolean If true, all keys are hashed before session storage
	 */
	protected $_hashKeys = true;
	public function getHashKeys() { return $this->_hashKeys; }
	public function setHashKeys( $value ) { return $this->_hashKeys = $value; }

	/**
	 * @var boolean If true, all data will be serialized for storage
	 */
	protected $_serializeData = true;
	public function getSerializeData() { return $this->_serializeData; }
	public function setSerializeData( $value ) { return $this->_serializeData = $value; }

	/**
	 * @var string The current key prefix
	 */
	protected $_keyPrefix = null;
	public function getStateKeyPrefix() { return $this->_keyPrefix; }
	public function setStateKeyPrefix( $value ) { $this->_keyPrefix = $value; }

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	 * Constructor with optional state key prefix override
	 * @param string $keyPrefix
	 */
	public function __construct( $keyPrefix = null )
	{
		//	Call pop
		parent::__construct();

		//	Initialize the state key prefix
		$this->_keyPrefix = ( null === $keyPrefix ? md5( self::BASE_KEY_PREFIX ) : $keyPrefix );
	}

	/**
	 * Returns the value of a variable that is stored in user session.
	 *
	 * This function is designed to be used by CWebUser descendant classes
	 * who want to store additional user information in user session.
	 * A variable, if stored in user session using {@link setState} can be
	 * retrieved back using this function.
	 *
	 * @param string $key variable name
	 * @param mixed $defaultValue default value
	 * @return mixed the value of the variable. If it doesn't exist in the session, the provided default value will be returned
	 * @see setState
	 */
	public function getState( $key, $defaultValue = null, $isFlash = false )
	{
		return $this->getRawState( $this->getInternalKey( $key, $isFlash ), $defaultValue );
	}

	/**
	 * Retrieves the raw state
	 * @param string $key
	 * @param mixed $defaultValue
	 * @return mixed
	 */
	public function getRawState( $key, $defaultValue = null )
	{
		$_result = ( isset( $_SESSION[$key] ) ? $this->_unserialize( $_SESSION[$key] ) : $_SESSION[$key] = $defaultValue );
		if ( $this->_debugMode ) CPSLog::trace( __METHOD__, 'Raw GET:[' . $key . '] = [' . $_result . ']' );
		return $_result;
	}

	/**
	 * Stores a variable in user session.
	 *
	 * This function is designed to be used by CWebUser descendant classes
	 * who want to store additional user information in user session.
	 * By storing a variable using this function, the variable may be retrieved
	 * back later using {@link getState}. The variable will be persistent
	 * across page requests during a user session.
	 *
	 * @param string $key variable name
	 * @param mixed $value variable value
	 * @param mixed $defaultValue default value. If $value===$defaultValue, the variable will be removed from the session
	 * @see getState
	 */
	public function setState( $key, $value, $defaultValue = null, $isFlash = false )
	{
		$this->setRawState( $this->getInternalKey( $key, $isFlash ), $value, $defaultValue );
	}

	/**
	 * Sets a session state
	 * @param string $key
	 * @param mixed $value
	 * @param mixed $defaultValue
	 */
	public function setRawState( $key, $value, $defaultValue = null )
	{
		if ( $this->_debugMode ) CPSLog::trace( __METHOD__, 'Raw Set:[' . $key . '] = [' . $value . ']' );

		if ( $value === $defaultValue )
			unset( $_SESSION[$key] );
		else
			$_SESSION[$key] = $this->_serialize( $value );
	}

	/**
	 * Returns a value indicating whether there is a state of the specified name.
	 * @param string $key state name
	 * @return boolean whether there is a state of the specified name.
	 * @since 1.0.3
	 */
	public function hasState( $key )
	{
		$_key = $this->getInternalKey( $key );
		return isset( $_SESSION[$_key] );
	}

	/**
	 * Clears all user identity information from persistent storage.
	 * This will remove the data stored via {@link setState}.
	 */
	public function clearStates()
	{
		$_baseKey = $this->getInternalKey();
		$_length = strlen( $_baseKey );

		foreach ( array_keys( $_SESSION ) as $_key )
		{
			if ( ! strncmp( $_key, $_baseKey, $_length ) )
				unset( $_SESSION[$_key] );
		}
	}

	/**
	 * Returns all flash messages.
	 * This method is similar to {@link getFlash} except that it returns all
	 * currently available flash messages.
	 * @param boolean $delete whether to delete the flash messages after calling this method.
	 * @return array flash messages (key => message).
	 */
	public function getFlashes( $delete = true )
	{
		$_flashList = array();
		$_baseKey = $this->getInternalKey( null, true );
		$_length = strlen( $_baseKey );

		$_keyList = array_keys( $_SESSION );

		foreach ( $_keyList as $_key )
		{
			if ( ! strncmp( $_key, $_baseKey, $_length ) )
			{
				$_flashList[substr( $_key, $_length )] = $_SESSION[$_key];
				if ( $delete ) unset( $_SESSION[$_key] );
			}
		}

		if ( $delete ) $this->setRawState( $this->getInternalKey( self::BASE_FLASH_KEY_PREFIX . '.counters', true ), array() );

		return $_flashList;
	}

	/**
	 * Returns a flash message.
	 * A flash message is available only in the current and the next requests.
	 * @param string $key key identifying the flash message
	 * @param mixed $defaultValue value to be returned if the flash message is not available.
	 * @param boolean $delete whether to delete this flash message after accessing it.
	 * Defaults to true. This parameter has been available since version 1.0.2.
	 * @return mixed the message message
	 */
	public function getFlash( $key, $defaultValue = null, $delete = true )
	{
		$_value = $this->getState( $key, $defaultValue, true );
		if ( $delete ) $this->setFlash( $key, null );
		return $_value;
	}

	/**
	 * Stores a flash message.
	 * A flash message is available only in the current and the next requests.
	 * @param string $key key identifying the flash message
	 * @param mixed $value flash message
	 * @param mixed $defaultValue if this value is the same as the flash message, the flash message
	 * will be removed. (Therefore, you can use setFlash('key',null) to remove a flash message.)
	 */
	public function setFlash( $key, $value, $defaultValue = null )
	{
		$this->setState( $key, $value, $defaultValue, true );
		$_counterList = $this->getState( self::BASE_FLASH_KEY_PREFIX . '.counters', array() );

		if ( $value === $defaultValue )
			unset( $_counterList[$key] );
		else
			$_counterList[$key] = 0;

		$this->setState( self::BASE_FLASH_KEY_PREFIX . '.counters', $_counterList, array() );
	}

	/**
	 * @param string $key key identifying the flash message
	 * @return boolean whether the specified flash message exists
	 */
	public function hasFlash( $key )
	{
		return ( null !== $this->getFlash( $key, null, false ) );
	}

	/**
	 * Constructs an "internal" key built from the key you're looking for and the key prefix. Optionally hashes the key.
	 * @param string $key If null, the hashed prefix is returned
	 * @param boolean $isFlashKey If true, the base flash key prefix is appeneded to the key before hashing.
	 * @return string
	 */
	public function getInternalKey( $key = null, $isFlashKey = false )
	{
		//	Construct the key
		$_key = session_id() . '.' . $this->_keyPrefix . ( $isFlashKey ? '.' . self::BASE_FLASH_KEY_PREFIX : null );
		if ( ! empty( $key ) ) $_key .= '.' . ( $this->_hashKeys ? md5( $key ) : $key );
		if ( $this->_debugMode ) CPSLog::trace( __METHOD__, 'Internalize Key:[' . $key . '] => [' . $_key . ']' );
		return $_key;
	}

	//********************************************************************************
	//* Private Methods
	//********************************************************************************

	/**
	 * Smarter serializer that can handle SimpleXmlElement objects
	 * @param mixed $value
 	 * @return mixed
	 */
	protected function _serialize( $value )
	{
		if ( $this->_serializeData )
		{
			try
			{
				if ( $value instanceof SimpleXMLElement || $value instanceof Util_SpXmlElement )
					return $value->asXML();

				if ( is_object( $value ) )
					return serialize( $value );
			}
			catch ( Exception $_ex )
			{
			}
		}

		return $value;
	}

	/**
	 * Unserializer that can handle SimpleXmlElement objects
	 * @param mixed $value
	 * @return mixed
	 */
	protected function _unserialize( $value )
	{
		try
		{
			if ( $this->_isSerialized( $value ) )
			{
				if ( $value instanceof SimpleXMLElement || $value instanceof Util_SpXmlElement )
					return simplexml_load_string( $value );

				return @unserialize( $value );
			}
		}
		catch ( Exception $_ex )
		{
		}

		return $value;
	}

	/**
	 * Tests if a value needs unserialization
	 * @param mixed $value
	 * @return boolean
	 */
	protected function _isSerialized( $value )
	{
		$_result = @unserialize( $value );
		return !( false === $_result && $value != @serialize( false ) );
	}

}

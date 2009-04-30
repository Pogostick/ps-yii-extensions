<?php
/**
 * CPSOptionsBehavior class file.
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://ps-yii-extensions.googlecode.com
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://ps-yii-extensions.googlecode.com
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @license http://www.pogostick.com/license/
 */

/**
 * CPSOptionsBehavior provides the base class for generic options settings for use with any class.
 * Avoids the need for declaring member variables and provides convenience magic functions to search the options.
 *
 * addOptions array format:
 * <code>
 * array(
 * 		'optionName' = array(
 * 			'value' => default value,
 * 			'type' => 'typename' (i.e. string, array, integer, etc.),
 * 			'valid' => array( 'v1', 'v2', 'v3', etc.) // An array of valid values for the option
 * 		)
 * )
 * </code>
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @version SVN: $Id$
 * @package psYiiExtensions
 * @subpackage Behaviors
 * @filesource
 * @since 1.0.4
 */
abstract class CPSOptionsBehavior extends CBehavior
{
	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	/**
	* Holds the settings for this object
	*
	* @var array
	*/
	public static $m_arOptions = array();
	/**
	* The delimiter to use for sub-options. Defaults to '.'
	*
	* @var string
	*/
	public static $m_sDelimiter = '.';

	//********************************************************************************
	//* Property Accessors
	//********************************************************************************

	/**
	* Delimiter getter
	* @returns string The currently set delimiter. Defaults to '.'
	*/
	public function getDelimiter() { return( self::$m_sDelimiter ); }
	/**
	* Delimiter setter
	* @var $sValue The string to use as a delimiter
	*/
	public function setDelimiter( $sValue ) { self::$m_sDelimiter = $sValue; }

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	* Adds an option to the behavior
	*
	* @param array $arOptions The array (key=>value pairs) of options to set
	* @param mixed $oValue
	*/
	public function addOptions( array $arOptions )
	{
		foreach ( $arOptions as $_sKey => $_oValue )
			$this->addOption( $_sKey, $_oValue );
	}

	/**
	* Adds a single option to the behavior
	*
	* @param string $sKey
	* @param mixed $oValue
	* @param array $arOrigArgs
	* @return array The last option processed
	*/
	public function addOption( $sKey, $oValue = null )
	{
		//	Is the key bogus?
		if ( null == $sKey || '' == trim( $sKey ) )
			throw new CException( Yii::t( 'psOptionsBehavior', 'Invalid property name "{property}".', array( '{property}' => $sKey ) ) );

		self::$m_arOptions[ $sKey ] = $oValue;
	}

	/**
	* Get the value of the supplied option key
	*
	* @param string $sKey
	* @param boolean $bOnlyPublic Returns only options that are not marked as 'private' => true
	* @returns array|null
	*/
	public function &getOption( $sKey, $bOnlyPublic = false )
	{
		$_arTemp =& $this->walkOptionChain( $sKey, false );

		if ( $_arTemp[ 'status' ] )
			$_oObject =& $_arTemp[ 'object' ];
		else
			$_oObject =& $_arTemp[ $_arTemp[ 'missingKey' ] ];

		if ( $bOnlyPublic && $_arTemp[ 'containsPrivate' ] )
			$_oObject = null;

		$_oObject =& $_oObject[ $sKey ];

		if ( null != $_oObject && is_array( $_oObject ) && array_key_exists( 'value', $_oObject ) )
			return $_oObject[ 'value' ];

		return $_oObject;
	}

	/***
	* Set the value of the supplied option key
	*
	* @param string $sKey
	* @param mixed $oValue
	*/
	public function setOption( $sKey, $oValue, $bAddIfMissing = false )
	{
		$_oObject =& $this->walkOptionChain( $sKey );

		$_sKey = ( ! $_oObject[ 'status' ] ) ? $_oObject[ 'missingKey' ] : $_oObject[ 'lastKey' ];

		if ( ! $bAddIfMissing && ( null == $_oObject || ! $_oObject[ 'status' ] ) )
			throw new CException( Yii::t( 'psOptionsBehavior', 'Options key "{key}" from "{masterKey}" not found.', array( '{key}' => $_oObject[ 'lastKey' ], '{masterKey}' => $sKey  ) ) );

		if ( $_oObject[ 'status' ] && is_array( $_oObject[ 'object' ][ $_sKey ] ) && array_key_exists( 'value', $_oObject[ 'object' ][ $_sKey ] ) )
			$_oObject[ 'object' ][ $_sKey ][ 'value' ] = $oValue;
		else if ( array_key_exists( $_sKey, $_oObject[ 'object' ] ) )
			$_oObject[ 'object' ][ $_sKey ] = $oValue;
		else
			throw new CException( Yii::t( 'psOptionsBehavior', 'Options key "{key}" from "{masterKey}" not found.', array( '{key}' => $_oObject[ 'lastKey' ], '{masterKey}' => $sKey  ) ) );
	}

	/**
	* Walks the options chain and returns the final object...
	*
	* @param string $sOptionKey Key to start at
	* @return array Returns an array with various information about the call
	*/
	public function &walkOptionChain( $sOptionKey )
	{
		//	Local vars...
		$_bPrivate = false;
		$_bContainsPrivate = false;
		$_oObject =& self::$m_arOptions;

		//	Our return object...
		$_arReturn = array( 'status' => true, 'object' => null, 'lastKey' => null, 'keyArray' => array(), 'containsPrivate' => false, 'missingKey' => null );

		//	If start key given, scoot up to it...
		$_arKeys = explode( self::$m_sDelimiter, $sOptionKey );
		$_i = 0;

		foreach ( $_arKeys as $_sKeyName )
		{
			//	Set return parameters
			$_arReturn[ 'keyArray' ][ 'name' ] = $_arReturn[ 'lastKey' ] = $_sKeyName;
			$_bPrivate = ( isset( $_oObject[ 'private' ] ) && $_oObject[ 'private' ] );
			$_arReturn[ 'keyArray' ][ 'private' ] = $_bPrivate;
			$_arReturn[ 'containsPrivate' ] = ( bool )( $_bContainsPrivate |= $_bPrivate );

			//	Is this key contained within array?
			if ( is_array( $_oObject ) && ! array_key_exists( $_sKeyName, $_oObject ) )
			{
				$_arReturn[ 'object' ] =& $_oObject;
				$_arReturn[ 'status' ] = false;
				$_arReturn[ 'missingKey' ] = $_sKeyName;
				return $_arReturn;
			}

			//	Counter...
		if ( ++$_i <= sizeof( $_arKeys ) - 1 )
				$_oObject =& $_oObject[ $_sKeyName ];

			//	Move our reference
			$_arReturn[ 'object' ] =& $_oObject;

		}

		//	Set our array reference
		//	Return our object
		return $_arReturn;
	}

	//********************************************************************************
	//* Magic Function Overrides
	//********************************************************************************

	/**
	 * Returns a property value or an event handler list by property or event name. You can access sub-option settings via using the delimiter between options.
	 * The default delimiter is '.'. For example, you can get $this->validOptions.baseUrl instead of making two calls. There is no limit to the depth. There is no
	 * parent::__get call in here because our parent is CBehavior
	 * @param string the property name or the event name
	 * @return mixed the property value or the event handler list
	 * @throws CException if the property/event is not defined.
	 */
	public function __get( $sName )
	{
		//	Look for member variables with that name
		$_oGetResults = $this->getOption( $sName );

		if ( $_oGetResults === null )
			return parent::__get( $sName );

		return $_oGetResults;
	}

	/**
	 * Sets value of a component property. You can access sub-option settings via using the delimiter between options.
	 * The default delimiter is '.'. For example, you can set $this->validOptions.baseUrl = '/mybaseurl' instead of making two calls. There is no limit to the depth.
	 * @param string the property name or event name
	 * @param mixed the property value or event handler
	 * @throws CException If the property is not defined or read-only.
	 */
	public function __set( $sArgs, $oValue = null )
	{
		//	Look in options array...
		try { $this->setOption( $sArgs, $oValue ); return; } catch ( Exception $_ex ) { /* Ignore for passthru */ }

		//	Look for member variables to set...
		parent::__set( $sArgs, $oValue );
	}

	/**
	 * Checks if a property value is null.
	 * @param string the property name or the event name
	 * @return boolean whether the property value is null
	 */
	public function __isset( $sName )
	{
//		try { parent::__isset( $sName ); } catch ( Exception $_ex ) { /* Ignore for passthru */ }

		return null != $this->getOption( $sName );
	}

	/**
	 * Sets a component property to be null.
	 * @param string the property name or the event name
	 * @since 1.0.1
	 */
	public function __unset( $sName )
	{
//		try { return parent::__unset( $sName ); } catch ( Exception $_ex ) { /* Ignore for passthru */ }

		$this->setOption( $sName, null );
	}
}
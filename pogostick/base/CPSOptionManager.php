<?php
/**
 * CPSOptionManager class file.
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://ps-yii-extensions.googlecode.com
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @license http://www.pogostick.com/license/
 */

/**
 * CPSOptionManager provides the base class for generic options settings for use with any class.
 * Avoids the need for declaring member variables and provides convenience magic functions to search the options.
 *
 * setOptions array format:
 * <code>
 * array(
 * 		'optionName' => array(							//	optionName is the name of your option key
 * 			// Predefined Array Values
 * 			'value' => default value,					//	Defaults to null,
 * 			'type' => 'typename',						//	Any valid PHP type (i.e. string, array, integer, etc.),
 * 			'access' => 'public',						//	The access level for this key (public, protected, or private)
 * 			'component' => 'false',						//	Indicates that this is a private option.
 * 			'valid' => array( 'v1', 'v2', 'v3', etc.)	// An array of valid values for the option
 * 		)
 * )
 * </code>
 *
 * @access public
 * @author Jerry Ablan <jablan@pogostick.com>
 * @version SVN: $Id$
 * @filesource
 * @package psYiiExtensions
 * @subpackage Base
 */
class CPSOptionManager
{
	//********************************************************************************
	//* Constants
	//********************************************************************************

	const PSOPTION_ERROR_NOT_ARRAY = -1;

	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	/**
	* The base option collection
	*
	* @staticvar array
	*/
	private static $m_arOptions = array();
	/**
	* My internal tag for option filtering
	*
	* @var string
	*/
	protected $m_sInternalName = null;
	/**
	* The delimiter to use for prefixes. This must contain only characters that are not allowed
	* in variable names (i.e. '::', '||', '.', etc.). Defaults to '::'. There is no length limit,
	* but 2 works out. There is really no need to ever change this unless you have a strong dislike
	* of the '::' characters.
	*
	* @var string
	*/
	protected $m_sPrefixDelimiter = '::';

	//********************************************************************************
	//* Public methods...
	//********************************************************************************

	/**
	* Constructor
	*
	*/
	public function __construct()
	{
		//	Create our name
		$_sName = CPSCommonBase::createInternalName( $this );

		//	Log it and check for issues...
		CPSCommonBase::writeLog( Yii::t( $_sName, '{class} constructed', array( "{class}" => $_sClass ) ), 'trace', $_sName );
	}

	/**
	* Sets option manager metadata. Metadata keys must begin with an underscore ('_').
	* If not provided, one will be added.
	*
	* @param string|array $oName
	* @param mixed $oValue
	*/
	public function setMetaData( $oName, $oValue = null )
	{
		$_arTemp = $oName;

		if ( ! is_array( $oName ) )
			$_arTemp = array( $oName => $oValue );

		foreach ( $_arTemp as $_sKey => $_oValue )
		{
			if ( false === strpos( $_sKey, '_' ) )
				$_sKey .= '_' . $_sKey;

			$this->setOption( $_sKey, $_oValue );
		}
	}

	//********************************************************************************
	//* Property Access Methods
	//********************************************************************************

	/**
	* Getters for member variables
	*
	*/
	public function getInternalName() { return $this->m_sInternalName; }
	public function getNamePrefix() { return $this->m_m_sInternalName . $this->m_sPrefixDelimiter; }
	public function getPrefixDelimiter() { return $this->m_sPrefixDelimiter; }
	public function getValidPattern() { return $this->m_arValidPattern; }

	/**
	* Setters for member variables
	*
	*/
	public function setInternalName( $sValue ) { $this->m_sInternalName = $sValue; }
	protected function setPrefixDelimiter( $sValue ) { $this->m_sPrefixDelimiter = $sValue; }

	/**
	* Returns a reference to the entire option array
	*
	* @returns array A reference to the internal options array
	* @see getOption
	* @see setOptions
	*/
	public function &getOptions() { return self::$m_arOptions; }

	/**
	* Add bulk options to the manager.
	*
	* @param array $arOptions An array containing option_key => value pairs to put into
	* option array. The parameter $arOptions is merged with the existing options array.
	* Existing option values are overwritten or added.
	*
	* <code>
	* $this->setOptions( array( 'option_x' => array( 'value' => '1', 'valid' => array( 'x', 'y', 'z' ) );
	* </code>
	*
	* @see getOptions
	* @see setOption
	*/
	public function setOptions( array $arOptions )
	{
		foreach ( $arOptions as $_sKey => $_oValue )
			$this->setOption( $_sKey, $_oValue );

		//	Sort the array
		ksort( self::$m_arOptions );
	}

	/**
	* Retrieves an option value from the options array. If key doesn't exist, it's created as an empty array and returned.
	*
	* @param string $sKey
	* @return mixed
	* @see getOptions
	* @see setOption
	*/
	public function &getOption( $sKey )
	{
		//	Validate the key
		if ( null == ( $sKey = $this->validateKey( $sKey ) ) )
			return null;

		return ( isset( self::$m_arOptions[ $sKey ] ) ) ? self::$m_arOptions[ $sKey ] : self::$m_arOptions[ $sKey ] = null;
	}

	/**
	* Sets a single option to the array
	*
	* @param string $sKey
	* @param mixed $oValue
	* @return null
	* @see setOptions
	*/
	public function setOption( $sKey, $oValue )
	{
		//	Validate the key
		if ( null == ( $sKey = $this->validateKey( $sKey ) ) )
			return null;

		//	Set the option...
		self::$m_arOptions[ $sKey ] = $oValue;

		//	Sort the array
		ksort( self::$m_arOptions );
	}

	/**
	* Ensures that provided key belongs to this behavior.
	*
	* @param string $sKey
	* @returns null|string
	*/
	public function validateKey( $sKey )
	{
		//	Fix up the key if it's directed
		if ( false !== ( $_iPos = strpos( $sKey, $this->m_sPrefixDelimiter ) ) )
		{
			$_sName = substr( $sKey, 0, $_iPos );
			$sKey = substr( $sKey, $_iPos + strlen( $this->m_sPrefixDelimiter ) );

			//	If option doesn't belong to me, boogie...
			if ( $this->m_sInternalName != $_sName )
				return null;
		}

		//	Return validated key
		return $sKey;
	}

	public function checkOption( $sKey, $oValue, array &$arResults = null )
	{
		//	Our results array
		$_arReturn = array();

//		if ( ! is_array( $oValue ) )
//			return self::PSOPTION_ERROR_NOT_ARRAY;

		/*
 * 		'optionName' => array(							//	optionName is the name of your option key
 * 			// Predefined Array Values
 * 			'value' => default value,					//	Defaults to null,
 * 			'type' => 'typename',						//	Any valid PHP type (i.e. string, array, integer, etc.),
 * 			'access' => 'public',						//	The access level for this key (public, protected, or private)
 * 			'component' => 'false',						//	Indicates that this is a private option.
 * 			'valid' => array( 'v1', 'v2', 'v3', etc.)	// An array of valid values for the option
*/
	}
}
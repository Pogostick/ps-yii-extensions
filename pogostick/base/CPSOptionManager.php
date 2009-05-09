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
 * @author Jerry Ablan <jablan@pogostick.com>
 * @version SVN: $Id$
 * @filesource
 * @package psYiiExtensions
 * @subpackage Base
 * @since 1.0.0
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
	public static $m_arOptions = array();
	/**
	* My internal tag for option filtering
	*
	* @var string
	*/
	protected $m_sInternalName = null;
	/**
	* A reference to my parent
	*
	* @var mixed
	*/
	protected $m_oParent = null;

	//********************************************************************************
	//* Public methods...
	//********************************************************************************

	/**
	* Constructor
	*
	*/
	public function __construct( &$oParent )
	{
		//	Create our name
		$this->setInternalName( 'psOptionManager' );

		//	Set our parent
		$this->m_oParent =& $oParent;

		//	Log it and check for issues...
		CPSCommonBase::writeLog( Yii::t( $this->getInternalName(), '{class} constructed', array( "{class}" => $_sClass ) ), 'trace', $this->getInternalName() );
	}

	//********************************************************************************
	//* Property Access Methods
	//********************************************************************************

	/**
	* Getter
	*
	* @returns string
	*/
	public function getInternalName() { return $this->m_sInternalName; }

	/**
	* Setter
	*
	* @param string $sValue
	*/
	public function setInternalName( $sValue ) { $this->m_sInternalName = $sValue; }

	/**
	* Returns a reference to the entire reference array
	*
	* @returns array A reference to the internal options array
	* @see getOption
	*/
	public function &getOptions() { return self::$m_arOptions; }

	/**
	* Add bulk options to the manager.
	*
	* @param array $arOptions An array containing option_key => value pairs to put into option array. The parameter $arOptions is merged with the existing options array. Existing option values are overwritten or added.
	* <code>
	* $this->setOptions( array( 'option_x' => array( 'value' => '1', 'valid' => array( 'x', 'y', 'z' ) );
	* </code>
	* @returns null
	* @see getOptions
	*/
	public function setOptions( array $arOptions )
	{
		//	Merge the supplied options
		self::$m_arOptions = array_merge( self::$m_arOptions, $arOptions );

		//	Sort the array
		ksort( self::$m_arOptions );
	}

	/**
	* Retrieves an option value from the options array. If key doesn't exist, it's created as an empty array and returned.
	*
	* @param string $sKey
	* @return mixed
	* @see getOptions
	*/
	public function &getOption( $sKey )
	{
		//	Fix up the key if it's directed
		if ( false !== ( $_iPos = strpos( $sKey, '::' ) ) )
		{
			$_sName = substr( $sKey, 0, $_iPos );
			$sKey = substr( $sKey, $_iPos + 2 );

			//	If option doesn't belong to me, boogie...
			if ( $this->m_sInternalName != $_sName )
				return null;
		}

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
		//	Fix up the key if it's directed
		if ( false !== ( $_iPos = strpos( $sKey, '::' ) ) )
		{
			$_sName = substr( $sKey, 0, $_iPos );
			$sKey = substr( $sKey, $_iPos + 2 );

			//	If option doesn't belong to me, boogie...
			if ( $this->m_sInternalName != $_sName )
				return null;
		}

		//	Set the option...
		self::$m_arOptions[ $sKey ] = $oValue;

		//	Sort the array
		ksort( self::$m_arOptions );
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
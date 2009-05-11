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
 * Avoids the need for declaring member variables and provides convenience magic functions to
 * search the options.
 *
 * Here is an example option specification and pattern.
 *
 * <code>
 * array(
 *		//	optionName is the name of your option key, optionally private (by appending an
 * 		//	underscore ('_') to the option name. Private options are never returned from
 * 		//	(@link makeOptions())
 * 		'optionName{_}' => array(
 * 			//	Option name for a javascript widget. Defaults to '<b>optionName</b>'.
 * 			'_extName' => external-facing name | empty
 *			//	Defaults to null,
 * 			'_value' => default value,
 * 			//	Lay out the validation parameters
 * 			'_validPattern' => array(
 * 				//	Any valid PHP type (i.e. string, array, integer, etc.),
 * 				'type' => 'typename',
 * 				// An array of valid values for the option
 * 				'valid' => array( 'v1', 'v2', 'v3', etc.)
 * 		)
 * )
 * </code>
 *
 * <b>_extName</b> tells the option manager to use this when formatting an options array with (@link CPSOptionManager::makeOptions()).
 *
 * <b>_value</b> tells the option manager what to set the value of the option to upon creation. New values will override this default value.
 *
 * When adding options to the options manager, you must specify the pattern for the option's value.
 * The pattern must be placed in the array element with a key of '<b>_validPattern</b>' to be recognized
 * by the option manager.
 *
 * <b>_validPattern</b> tells the option manager what type of variable is allowed to be assigned to the option.
 * Any legal PHP type is allowed. If more than one type is allowed, you may send in an array as the
 * value of '<b>type</b>'.
 *
 * This pattern can include none, one, or more pattern sub-types.
 *
 * These are:
 * 	1. <b>type</b>
 *  2. <b>valid</b>
 *  3. <b>required</b>
 *
 * <b>type</b> tells the option manager what type of variable is allowed to be assigned to the option.
 * Any legal PHP type is allowed. If more than one type is allowed, you may send in an array as the
 * value of '<b>type</b>'.
 *
 * <b>valid</b> tells the option manager the valid values of the option that is being set. This must
 * be specified as an array. For instance, if an option can only have three possible values: '<b>public</b>',
 * '<b>protected</b>', or '<b>private</b>', the array specified for the value of '<b>valid</b>' would be
 *
 * <b>required</b> tells the option manager that the option is required and as such, must have a non-null <b>_value</b>.
 *
 * <code>
 *
 * array( 'public', 'protected', 'private' )
 *
 * </code>
 *
 * This next snippet defines an option named '<b>hamburgerCount</b>'. It is private because we've appended
 * an underscore to the name. It's default value is 6, it can be only of type '<b>int</b>', and has no
 * required values.
 *
 * <code>
 *
 * 'hamburgerCount_' = array(
 * 	'_value' => 6,
 * 	'_validPattern' =>
 * 		array(
 * 			'type' => 'int',
 * 			'valid' => null,
 * 		),
 * 	);
 *
 * </code>
 *
 * Using this option from a Pogostick component or widget is as simple as this:
 *
 * <code>
 * $this->hamburgerCount_ = 3;
 * echo $this->hamburgerCount_;
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
	public function __construct( $sInternalName = null )
	{
		//	Create our name
		$_sName = CPSCommonBase::createInternalName( $this );

		//	Reset our name if requested...
		if ( null != $sInternalName )
			$this->setInternalName( $sInternalName );

		//	Log it and check for issues...
		CPSCommonBase::writeLog( Yii::t( $_sName, '{class} constructed', array( "{class}" => $_sClass ) ), 'trace', $_sName );
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

	//********************************************************************************
	//* Options Access Methods
	//********************************************************************************

	/**
	* Returns a reference to the entire option array
	*
	* @returns array A reference to the internal options array
	* @see getOption
	* @see setOptions
	*/
	public function &getOptions() { return self::$m_arOptions[ $this->m_sInternalName ]; }

	/**
	* Add bulk options to the manager.
	*
	* @param array $arOptions An array containing option_key => value pairs to put into
	* option array. The parameter $arOptions is merged with the existing options array.
	* Existing option values are overwritten or added.
	* @param bool $bAdd Indicates that this option setting is a creation one (i.e. addOptions() analog)
	*
	* <code>
	* $this->setOptions( array( 'option_x' => array( 'value' => '1', 'valid' => array( 'x', 'y', 'z' ) );
	* </code>
	*
	* @see getOptions
	* @see setOption
	*/
	public function setOptions( array $arOptions, $bAdd = true )
	{
		foreach ( $arOptions as $_sKey => $_oValue )
			if ( $_sKey = $this->validateKey( $_sKey ) )
				$this->setOption( $_sKey, $_oValue, $bAdd );

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

		//	Not a private option? Check if it's in the array as private and kajigger the key...
		if ( ! $this->hasOption( $sKey ) && false === strpos( $sKey, '_', strlen( $sKey ) - 1 ) )
			if ( isset( self::$m_arOptions[ $sKey . '_' ] ) )
				$sKey .= '_';

		return ( isset( self::$m_arOptions[ $sKey ][ '_value' ] ) ) ? self::$m_arOptions[ $sKey ][ '_value' ] : self::$m_arOptions[ $sKey ][ '_value' ] = array();
	}

	/**
	* Sets a single option to the array
	*
	* @param string $sKey
	* @param mixed $oValue
	* @param bool $bAdd Indicates that this option setting is a creation one (i.e. addOptions() analog)
	* @return null
	* @see setOptions
	*/
	public function setOption( $sKey, $oValue, $bAdd = false )
	{
		//	Validate the key
		if ( null == ( $sKey = $this->validateKey( $sKey ) ) )
			return null;

		//	Check for private options...
		if ( ! $this->hasOption( $sKey ) && false === strpos( $sKey, '_', strlen( $sKey ) - 1 ) )
			if ( isset( self::$m_arOptions[ $sKey . '_' ] ) )
				$sKey .= '_';

		//	Being added?
		if ( $bAdd )
			self::$m_arOptions[ $sKey ] = $oValue;
		else
		{
			if ( ! is_array( $oValue ) )
				self::$m_arOptions[ $sKey ][ '_value' ] = $oValue;
			else if ( is_array( self::$m_arOptions[ $sKey ] ) && is_array( $oValue ) )
				self::$m_arOptions[ $sKey ] = array_merge( self::$m_arOptions[ $sKey ], $oValue );
			else
				self::$m_arOptions[ $sKey ] = $oValue;
		}

		//	Sort the array
		ksort( self::$m_arOptions );
	}

	/**
	* Checks if an option exists in the options array...
	*
	* @param string $sKey
	* @return bool
	* @see setOption
	* @see setOptions
	*/
	public function hasOption( $sKey )
	{
		$_arOptions = $this->getOptions();

		if ( empty( $_arOptions ) )
			$_arOptions = array();

		return in_array( $this->validateKey( $sKey ), $_arOptions );
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

	/**
	* Checks the options array or a passed in array for validity checking...
	*
	* @param array $arOptions
	* @throws CException
	* @returns bool
	*/
	public function checkOptions( array $arOptions = null )
	{
		//	None were passed in? Then we check our array
		if ( empty( $arOptions ) )
			$arOptions = self::$m_arOptions;

		//	One at a time...
		foreach ( $arOptions as $_sKey => $_oValue )
			$this->checkOption( $this->validateKey( $_sKey ), $_oValue );

		//	We made it here? We cool baby!
		return true;
	}

	/**
	* Checks a single option against its pattern.
	*
	* @param string $sKey
	* @param mixed $oValue
	* @throws CException
	* @returns bool
	*/
	public function checkOption( $sKey, $oValue )
	{
		//	Check if this option is available
		if ( $this->hasOption( $sKey ) )
		{
			//	See if it hase valid options...
			$_arValidPattern = $this->{$sKey}[ '_validPattern' ];

			//	What type should it be?
			if ( array_key_exists( 'type', $_arValid ) )
			{
				//	Get the type of our value...
				$_sType = gettype( $oValue );

				//	Get the value of 'type'
				$_oVPType = $_arValidPattern[ '' ];

				//	Is this a valid type for this option?
				if ( ( ! is_array( $_oVPType ) && ( $_sType != $_oVPType ) ) || ( is_array( $_oVPType ) && ! in_array( $_sType, $_oVPType ) ) )
					throw new CException( Yii::t( __CLASS__, '"{x}" must be of type "{y}"', array( '{x}' => $_sKey, '{y}' => ( is_array( $_oVPType ) ) ? implode( ', ', $_oVPType ) : $_oVPType ) ) );
			}

			//	Check if this is a valid value for this option
			if ( array_key_exists( 'valid', $_arValidPattern ) )
			{
				$_arValid = $arValidOptions[ 'valid' ];

				if ( is_array( $_arValid[ 'valid' ] ) && ! in_array( $_oValue, $_arValid ) )
					throw new CException( Yii::t( __CLASS__, '"{x}" must be one of: "{y}"', array( '{x}' => $_sKey, '{y}' => implode( ', ', $_arValid ) ) ) );
			}

			if ( isset( $_arValidPattern[ 'required' ] ) && $_arValidPattern[ 'required' ] && empty( self::$m_arOptions[ $sKey ] ) )
				throw new CException( Yii::t( __CLASS__, '"{x}" is a required option', array( '{x}' => $_sKey ) ) );
		}
		else
			//	Invalid option
			throw new CException( Yii::t( __CLASS__, '"{x}" is not a valid option for "{intname}"', array( '{x}' => $sKey, '{intname}' => $this->m_sInternalName ) ) );

		//	Looks clean....
		return true;
	}

}
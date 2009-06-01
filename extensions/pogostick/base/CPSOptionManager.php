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
 *			//	Defaults to null,
 * 			CPSOptionManager::META_DEFAULTVALUE => default value,
 * 			//	Lay out the validation parameters
 * 			CPSOptionManager::META_RULES => array(
 * 				//	Any valid PHP type (i.e. string, array, integer, etc.),
 * 				'type' => 'typename',
 * 				'_extName' => external-facing name | empty
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
	//* Constants
	//********************************************************************************

	/**
	* These are shortcuts to the metadata options in the array.
	*/

	/**
	* @var string|array
	*/
	const META_ALLOWED 		= '_allowed';
	/**
	* @var string
	*/
	const META_DEFAULTVALUE	= '_defaultValue';
	/**
	* @var string
	*/
	const META_EXTERNALNAME = '_extName';
	/**
	* @var bool
	*/
	const META_PRIVATE 		= '_private';
	/**
	* @var bool
	*/
	const META_REQUIRED 	= '_required';
	/**
	* @var array
	*/
	const META_RULES 		= '_rules';
	/**
	* @var string
	*/
	const META_TYPE 		= '_type';
	/**
	* @var string
	*/
	const META_KEYNAME 		= '_keyName';
	/**
	* @var string
	* @todo Implement handling of this
	*/
	const META_VALUEPATTERN	= '_valuePattern';
	/**
	* @var array
	*/
	private $VALID_METADATA =
		array(
			self::META_ALLOWED,
			self::META_EXTERNALNAME,
			self::META_DEFAULTVALUE,
			self::META_PRIVATE,
			self::META_REQUIRED,
			self::META_RULES,
			self::META_TYPE,
			self::META_VALUEPATTERN,
			self::META_KEYNAME,
		);
	/**
	* @var array
	*/
	private $VALID_METADATA_TYPES =
		array(
			'string',
			'int',
			'integer',
			'bool',
			'boolean',
			'array',
			'double',
			'object',
		);

	/**
	* The meta data prefix tag
	* @var string
	*/
	const MD_TAG = '_md.';

	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	/**
	* The base option collection
	*
	* @var array
	*/
	protected $m_arOptions = array();
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
		CPSCommonBase::writeLog( Yii::t( $_sName, '{class} constructed', array( "{class}" => get_class( $this ) ) ), 'trace', $_sName );
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
	* @returns array
	*/
	public function &getOptions() { return $this->m_arOptions; }

	/**
	* Returns a copy of ONLY the public options in the internal options array
	*
	* @see getOptions
	* @returns array
	*/
	public function getPublicOptions()
	{
		$_arOptions = array();

		//	Build an array with non-private entities
		foreach( $this->m_arOptions as $_sKey => $_oValue )
		{
			//	Go through metadata, pull out non-private keys
			if ( $this->isMetaDataKey( $_sKey ) && ! $this->getMetaDataValue( $_sKey, CPSOptionManager::META_PRIVATE ) )
			{
				$_sRealKey = $this->getMetaDataValue( $_sKey, CPSOptionManager::META_KEYNAME );

				//	Validate the key
				if ( null == ( $_sKey = $this->validateKey( $_sKey, true ) ) )
					continue;

				//	This option is safe to output. Pull the key from the array
				$_arOptions[ $_sRealKey ] = $this->m_arOptions[ $_sRealKey ];
			}
		}

		return $_arOptions;
	}

	/**
	* Add bulk options to the manager.
	*
	* @param array $arOptions An array containing option_key => value pairs to put into
	* option array. The parameter $arOptions is merged with the existing options array.
	* Existing option values are overwritten or added.
	* @param bool $bAdd Indicates that this option setting is a creation one (i.e. addOptions() analog)
	*
	* <code>
	* $this->setOptions( array( 'option_x' => array( CPSOptionManager::META_DEFAULTVALUE => '1', CPSOptionManager::META_ALLOWED => array( 'x', 'y', 'z' ) );
	* </code>
	*
	* @see getOptions
	* @see setOption
	*/
	public function setOptions( array $arOptions, $bAdd = true )
	{
		//	Run through array and set each item.
		foreach ( $arOptions as $_sKey => $_oValue )
				$this->setOption( $_sKey, $_oValue, $bAdd );

		//	Sort the array
		ksort( $this->m_arOptions );
	}

	/**
	* Sets a single option to the array
	*
	* $oValue
	*
	* @param string $sKey
	* @param mixed $oValue
	* @param bool $bAdd Indicates that this option setting is a creation one (i.e. addOptions() analog).
	* @return null
	* @see setOptions
	*/
	public function setOption( $sKey, $oValue )
	{
		//	Validate the key
		if ( null == ( $sKey = $this->validateKey( $sKey ) ) )
			return null;

		//	Get the value out of the array...
		return $this->m_arOptions[ $sKey ] = $oValue;
	}

	/**
	* Add bulk options to the manager.
	*
	* @param array $arOptions An array containing option_key => value pairs to put into
	* option array. The parameter $arOptions is merged with the existing options array.
	* Existing option values are overwritten or added.
	* @param bool $bAdd Indicates that this option setting is a creation one (i.e. addOptions() analog)
	*
	* <code>
	* $this->setOptions( array( 'option_x' => array( CPSOptionManager::META_DEFAULTVALUE => '1', CPSOptionManager::META_ALLOWED => array( 'x', 'y', 'z' ) );
	* </code>
	*
	* @see getOption
	* @see setOption
	* @see addOption
	*/
	public function addOptions( array $arOptions )
	{
		//	Run through array and set each item.
		foreach ( $arOptions as $_sKey => $_oValue )
				$this->addOption( $_sKey, $_oValue, true );

		//	Sort the array
		ksort( $this->m_arOptions );
	}

	/**
	* Adds a single option to the array
	*
	* @param string $sKey
	* @param array $oValue Must be an array containing the RULES for this option
	* @see setOption
	*/
	public function addOption( $sKey, $oValue, $bNoSort = false )
	{
		$_bPrivate = false;

		//	Validate the key
		if ( null == ( $sKey = $this->validateKey( $sKey ) ) )
			return null;

		//	Check for private options...
		if ( false !== ( $_iPos = strpos( $sKey, '_', strlen( $sKey ) - 1 ) ) )
		{
			//	Strip off '_'
			$sKey = substr( $sKey, 0, $_iPos );
			$this->setMetaDataValue( $sKey, self::META_PRIVATE, true );
			$_bPrivate = true;
		}

		//	Get the value out of the array...
		$_arRules = array();

		if ( is_array( $oValue ) )
		{
			$_arRules = ( isset( $oValue[ self::META_RULES ] ) ) ? $oValue[ self::META_RULES ] : array();

			//	Fix up bool types...
			$_arType = $_arRules[ self::META_TYPE ];

			if ( ! is_array( $_arType ) )
				$_arType = array( $_arType );

			//	Allowed type?
			foreach ( $_arType as $_sKey => $_sType )
			{
				if ( null != $_sType && ! in_array( $_sType, $this->VALID_METADATA_TYPES ) )
					throw new CException( Yii::t( __CLASS__, 'Invalid "type" specified for "{x}"', array( '{x}' => $sKey ) ), 1  );

				//	Fix up our allowed shortcuts...
				if ( $_sType == 'bool' )
				{
					$_arType[ $_sKey ] = 'boolean';
					$this->setMetaDataValue( $_arType, self::META_TYPE, 'boolean' );
					$_sType = 'boolean';
				}

				if ( $_sType == 'int' )
				{
					$_arType[ $_sKey ] = 'integer';
					$this->setMetaDataValue( $_arType, self::META_TYPE, 'integer' );
					$_sType = 'integer';
				}

				//	Set value
				$_oValue = null;

				//	Try and set it correctly...
				if ( isset( $_arRules[ self::META_TYPE ] ) )
				{
					if ( ! isset( $oValue[ self::META_DEFAULTVALUE ] ) )
					{
						switch ( $_sType )
						{
							case 'integer':
								$_oValue = 0;
								break;
							case 'string':
								$_oValue = '';
								break;
							case 'boolean':
								$_oValue = false;
								break;
							case 'array':
								$_oValue = array();
								break;
							default:
								$_oValue = null;
								break;
						}
					}
					else
					{
						//	Only validate public options
						if ( ! $oValue[ self::META_PRIVATE ] )
						{
							//	Skip null strings...
							if ( $_sType == 'string' && null == $oValue[ self::META_DEFAULTVALUE ] )
								;
							else
							{
								if ( $_sType != gettype( $oValue[ self::META_DEFAULTVALUE ] ) && ! $_bPrivate && null != $oValue[ self::META_DEFAULTVALUE ] )
									throw new CException( Yii::t( __CLASS__, '"{x}" must be of type "{y}"', array( '{x}' => $sKey, '{y}' => ( is_array( $_sType ) ) ? implode( ', ', $_sType ) : $_sType ) ), 1  );
							}
						}

						//	It passed...
						$_oValue = $oValue[ self::META_DEFAULTVALUE ];
					}
				}
			}
		}

		//	Set the key name since ours is lowercased...
		$_arRules[ self::META_KEYNAME ] = $sKey;

		//	Set all the metadata rules...
		$this->setMetaDataValues( $sKey, $_arRules );
		
		//	Required?
		$this->setMetaDataValue( $sKey, self::META_REQUIRED, isset( $oValue[ self::META_REQUIRED ] ) ? $oValue[ self::META_REQUIRED ] : false );

		//	Now stuff the default value, if there...
		$this->m_arOptions[ $sKey ] = $_oValue;

		//	Sort the array
		if ( ! $bNoSort )
			ksort( $this->m_arOptions );
	}

	/**
	* Retrieves an option value from the options array. If key doesn't exist, it's created as an empty array and returned.
	*
	* @param string $sKey
	* @return mixed|null if not found
	* @see getOptions
	* @see setOption
	*/
	public function &getOption( $sKey, $oDefault = null )
	{
		//	Validate the key
		if ( null == ( $sKey = $this->validateKey( $sKey ) ) )
			return null;

		//	Key not there? Make it with default...
		if ( ! isset( $this->m_arOptions[ $sKey ] ) )
			$this->m_arOptions[ $sKey ] = $oDefault;

		return $this->m_arOptions[ $sKey ];
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
		return in_array( $this->validateKey( $sKey ), $this->m_arOptions );
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

			//	Strip off meta data identifier for comparison
			if ( self::MD_TAG == substr( $_sName, 0, strlen( self::MD_TAG ) ) )
				$_sName = substr( $_sName, strlen( self::MD_TAG ) );

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
			$arOptions = $this->m_arOptions;

		//	One at a time...
		foreach ( $arOptions as $_sKey => $_oValue )
			$this->checkOption( $_sKey, $_oValue );

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
		//	Validate the key
		if ( null == ( $sKey = $this->validateKey( $sKey ) ) )
			return null;

		//	Check if this option is available
		if ( $this->hasOption( $sKey ) )
		{
			$_bHasValue = ( isset( $this->m_arOptions[ $this->getMetaDataValue( $sKey, self::META_KEYNAME ) ] ) || isset( $this->m_arOptions[ strtolower( $sKey ) ] ) );
			
			//	Required and missing? Bail
			if ( $this->getMetaDataValue( $sKey, self::META_REQUIRED ) && ! $_bHasValue )
				throw new CException( Yii::t( __CLASS__, '"{x}" is a required option', array( '{x}' => $sKey ) ), 1 );

			//	Get the type of our value...
			$_sType = gettype( $oValue );

			//	Get the value of 'type'
			$_oVPType = $this->getMetaDataValue( $sKey, self::META_TYPE );

			//	Is this a valid type for this option?
			if ( null !== $oValue && null !== $_VPType )
			{			
				if ( ( ! is_array( $_oVPType ) && ( $_sType != $_oVPType ) ) || ( is_array( $_oVPType ) && ! in_array( $_sType, $_oVPType ) ) )
					throw new CException( Yii::t( __CLASS__, '"{x}" must be of type "{y}". Found type "{z}"', array( '{x}' => $sKey, '{y}' => ( is_array( $_oVPType ) ) ? implode( ', ', $_oVPType ) : $_oVPType, '{z}' => $_oVOType ) ), 1  );

				//	Check if this is a valid value for this option
				if ( null !== ( $_arValid = $this->getMetaDataValue( $sKey, self::META_ALLOWED ) ) )
				{
					if ( null != $oValue && is_array( $_arValid ) && ! in_array( $oValue, $_arValid ) )
						throw new CException( Yii::t( __CLASS__, '"{x}" must be one of: "{y}"', array( '{x}' => $sKey, '{y}' => implode( ', ', $_arValid ) ) ), 1  );
				}
			}
		}
		else
			//	Invalid option
			throw new CException( Yii::t( __CLASS__, '"{x}" is not a valid option for "{intname}"', array( '{x}' => $sKey, '{intname}' => $this->m_sInternalName ) ), 1  );

		//	Looks clean....
		return true;
	}

	//********************************************************************************
	//* Private metadata access methods...
	//********************************************************************************

	/**
	* Returns a string to be used as the meta data key for our options
	*
	* @returns string The metadata key
	*/
	protected function getMetaDataKey( $sKey ) { return self::MD_TAG . $this->m_sInternalName . $this->getPrefixDelimiter() . strtolower( $sKey ); }

	/**
	* Adds an array of metadata to the option array
	*
	* @param string $sKey
	* @param array $arRules
	*/
	protected function setMetaDataValues( $sKey, array $arRules )
	{
		foreach ( $arRules as $_sKey => $_oValue )
			$this->setMetaDataValue( $sKey, $_sKey, $_oValue );

		ksort( $this->m_arOptions );
	}

	/**
	* Sets a single option metadata value.
	*
	* @param string $sKey
	* @param string $sWhich
	* @param mixed $oValue
	* @return string
	*/
	protected function setMetaDataValue( $sKey, $eMDKey, $oValue )
	{
		//	Validate the key
		if ( null == ( $sKey = $this->validateKey( $sKey ) ) )
			return null;

		if ( in_array( $eMDKey, $this->VALID_METADATA ) )
			$this->m_arOptions[ $this->getMetaDataKey( $sKey ) ][ $eMDKey ] = $oValue;
	}

	/**
	* Gets a single option metadata value.
	*
	* @param string $sKey
	* @return array The meta data for the specified key
	*/
	public function getMetaData( $sKey )
	{
		return $this->m_arOptions[ $this->getMetaDataKey( $this->validateKey( $sKey ) ) ];
	}

	/**
	* Gets a single option metadata value.
	*
	* @param string $sKey
	* @param string $eMDKey (Use the self::META_* constants)
	* @return mixed
	*/
	public function getMetaDataValue( $sKey, $eMDKey )
	{
		//	Validate the key
		if ( null == ( $sKey = $this->validateKey( $sKey ) ) )
			return null;

		if ( in_array( $eMDKey, $this->VALID_METADATA ) )
			return $this->m_arOptions[ $this->getMetaDataKey( $sKey ) ][ $eMDKey ];
	}

	/**
	* Tests if a key is a meta data key.
	*
	* @param string $sKey
	*/
	public function isMetaDataKey( $sKey )
	{
		return ( false !== strpos( $sKey, $this->getMetaDataKey( '' ) ) );
	}

}
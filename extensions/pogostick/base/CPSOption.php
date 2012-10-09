<?php
/**
 * This file is part of the psYiiExtensions package.
 *
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link      http://www.pogostick.com Pogostick, LLC.
 * @license   http://www.pogostick.com/licensing
 */

/**
 * An object to represent an option in the option collection.
 * An option can be public or private. Neither hinder access however,
 * requests for options from the collection can be filtered by this
 * attribute.
 *
 * All options may have an optional rule pattern. A rule pattern describes
 * the acceptable parameters and value(s) for this option. The pattern requires
 * strict placement of parameters separated by a colon, or an array of parameters:
 *
 *         rules_pattern    =    type:default_value:external_name:is_required:allowed_value
 *
 *             or
 *
 *         rules_pattern    =    array( type, default_value, external_name, is_required, allowed_value )
 *
 *        where:
 *
 *        type                 =    Any valid PHP type
 *
 *        default_value        =    The default value for this option if not specified or set
 *
 *        external_name        =    The external name of this option. Allows you to compensate
 *                                for duplicate option names/keys
 *
 *        is_required            =    If option is required or not. Expressions passed in will be
 *                                eval'd. Must evalute to bool(true) or bool(false)
 *
 *        allowed_value        =    The allowed value(s) for this option. Any value is allowed or
 *                                a pipe-delimited list of strings will be transformed into an
 *                                array for lookups.
 *
 * To define a private option, either set the isPrivate property
 * to true or suffix (or prefix) the option name with an underscore. For
 * examples:
 *
 * $_oOption = new CPSOption( 'myPrivateOption_', 'I am private' );
 * $_oOption = new CPSOption( '_myPrivateOption2', 'I am private too!' );
 * $_oOption = new CPSOption( 'myPublicOption', 'I am public!' );
 *
 * <b>Please note that underscores are stripped from the beginning and end of all
 * option names. They are reserved.</b>
 *
 * @package        psYiiExtensions
 * @subpackage     base
 *
 * @author         Jerry Ablan <jablan@pogostick.com>
 * @version        SVN $Id: CPSOption.php 368 2010-01-18 01:55:44Z jerryablan@gmail.com $
 * @since          v1.0.6
 *
 * @filesource
 *
 * @property-read string  $name         The option name
 * @property mixed        $value        The option value
 * @property array        $rules        The rules array for this option
 *
 * @property-read boolean $isRequired   True if this option is required
 * @property-read boolean $isPrivate    True is this is a private option
 * @property-read mixed   $defaultValue The default value for this option
 * @property-read string  $externalName The external name of this option
 * @property-read string  $optionType   The type of value
 * @property-read mixed   $allowed      The allowed value(s) of this option
 *
 * @see            CPSOptionCollection
 */
class CPSOption implements IPSBase
{
	//********************************************************************************
	//* Constants
	//********************************************************************************

	/**
	 * These define the rule pattern types
	 */
	const    RPT_TYPE = '__psO#t';
	const    RPT_DEFAULT = '__psO#d';
	const    RPT_EXTERNAL_NAME = '__psO#e';
	const    RPT_REQUIRED = '__psO#r';
	const    RPT_ALLOWED = '__psO#a';
	const    RPT_PRIVATE = '__psO#p';

	/**
	 * @var array
	 */
	protected $arValidTypes = array(
		'string',
		'int',
		'integer',
		'bool',
		'boolean',
		'array',
		'double',
		'object',
		'mixed',
	);

	public function isValidType( $sType = null )
	{
		return in_array( CPSHelperBase::nvl( $sType, $this->getRule( self::RPT_TYPE ) ), $this->arValidTypes );
	}

	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	/**
	 * The name of this option
	 *
	 * @var string
	 */
	protected $_name;

	/**
	 * The value of this option
	 *
	 * @var mixed
	 */
	protected $_value = null;

	/**
	 * The rules for this option
	 *
	 * @var array
	 */
	protected $_rulePattern = null;

	//********************************************************************************
	//* Constructor
	//********************************************************************************

	/**
	 * Construct an option
	 *
	 * @param              string The name
	 * @param              mixed  The value
	 * @param string|array Rule   pattern for validation
	 *
	 * @return CPSOption
	 */
	public function __construct( $name, $value = null, $rulePattern = null )
	{
		//	Private?
		$_isPrivate = ( $name != ( $_cleanName = rtrim( trim( $name ), '_' ) ) );

		//	Build a ruleset
		$this->setRules( $rulePattern, $_isPrivate );

		//	Set values
		$this->_name = $_cleanName;
		$this->_value = CPSHelperBase::nvl( $value, $this->getDefaultValue() );
	}

	//********************************************************************************
	//* Property Accessors
	//********************************************************************************

	/**
	 * Get the option name
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->_name;
	}

	/**
	 * Get the option value
	 *
	 * @param mixed
	 */

	public function getValue( $defaultIfNull = false )
	{
		return $defaultIfNull ? CPSHelperBase::nvl( $this->_value, $this->getDefaultValue() ) : $this->_value;
	}

	/**
	 * Set the option value
	 *
	 * @param mixed
	 */
	public function setValue( $value )
	{
		$this->_value = $value;
	}

	/**
	 * Retrieves a single rule value
	 *
	 * @param string The rule
	 *
	 * @return mixed
	 */
	public function getRule( $key, $defaultValue = null )
	{
		return CPSHelperBase::o( $this->_rulePattern, $key, $defaultValue );
	}

	/**
	 * Retrieves all rules
	 *
	 * @return array
	 */
	public function getRules()
	{
		return $this->_rulePattern;
	}

	/**
	 * Sets a rule pattern value
	 *
	 * @param string The rule to set
	 * @param mixed  The rule pattern value
	 */
	public function setRule( $key, $value )
	{
		CPSHelperBase::so( $this->_rulePattern, $key, $value );
	}

	/**
	 * Sets rules from a pattern
	 *
	 * @param mixed the rule pattern
	 */
	public function setRules( $rulePattern, $private = false )
	{
		$this->_rulePattern = CPSOptionHelper::parseRulePattern( $rulePattern, $private );
	}

	//********************************************************************************
	//* Rule Checkers/Properties
	//********************************************************************************

	/**
	 * Returns true if this is a required option
	 *
	 * @return boolean
	 */
	public function getIsRequired()
	{
		return $this->getRule( self::RPT_REQUIRED, false );
	}

	public function setIsRequired( $bValue )
	{
		$this->setRule( self::RPT_REQUIRED, $bValue );
	}

	/**
	 * Returns true if this option is private
	 *
	 * @return boolean
	 */
	public function getIsPrivate()
	{
		return $this->getRule( self::RPT_PRIVATE, false );
	}

	public function setIsPrivate( $bValue )
	{
		$this->setRule( self::RPT_PRIVATE, $bValue );
	}

	/**
	 * Returns default value of option
	 *
	 * @return mixed
	 */
	public function getDefaultValue()
	{
		return $this->getRule( self::RPT_DEFAULT );
	}

	public function setDefaultValue( $value )
	{
		$this->setRule( self::RPT_DEFAULT, $value );
	}

	/**
	 * Returns external name of option
	 *
	 * @return string
	 */
	public function getExternalName()
	{
		return $this->getRule( self::RPT_EXTERNAL_NAME, $this->_name );
	}

	public function setExternalName( $name )
	{
		$this->setRule( self::RPT_EXTERNAL_NAME, $name );
	}

	/**
	 * Gets the type of this option
	 *
	 * @return string
	 */
	public function getOptionType()
	{
		return $this->getRule( self::RPT_TYPE, 'string' );
	}

	public function setOptionType( $sType )
	{
		return $this->setRule( self::RPT_TYPE, $sType );
	}

	/**
	 * Returns the allowable value(s) for this option
	 *
	 * @return mixed
	 */
	public function getAllowed()
	{
		return $this->getRule( self::RPT_ALLOWED );
	}

	/**
	 * Sets the allowable value(s) for this option
	 *
	 * @param mixed The allowed option value(s)
	 */
	public function setAllowed( $value )
	{
		return $this->setRule( self::RPT_ALLOWED, $value );
	}

	//********************************************************************************
	//* Miscellaneous
	//********************************************************************************

	/***
	 * Used in a string context, this object returns its value.
	 *
	 * @return mixed
	 */
	public function __toString()
	{
		return $this->_value;
	}

}
<?php
/*
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
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
 * 		rules_pattern	=	type:default_value:external_name:is_required:allowed_value
 * 
 * 			or
 * 
 * 		rules_pattern	=	array( type, default_value, external_name, is_required, allowed_value )
 * 
 *		where:
 * 
 *		type 				=	Any valid PHP type
 * 
 *		default_value		=	The default value for this option if not specified or set
 * 
 *		external_name		=	The external name of this option. Allows you to compensate 
 *								for duplicate option names/keys
 * 
 *		is_required			=	If option is required or not. Expressions passed in will be 
 *								eval'd. Must evalute to bool(true) or bool(false)
 * 
 *		allowed_value		=	The allowed value(s) for this option. Any value is allowed or 
 *								a pipe-delimited list of strings will be transformed into an 
 *								array for lookups.
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
 * @package 	psYiiExtensions
 * @subpackage 	base
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN $Id$
 * @since 		v1.0.6
 * 
 * @filesource
 * 
 * @property-read string $name The option name
 * @property mixed $value The option value
 * @property array $rules The rules array for this option
 * 
 * @property-read boolean $isRequired True if this option is required
 * @property-read boolean $isPrivate True is this is a private option
 * @property-read mixed $defaultValue The default value for this option
 * @property-read string $externalName The external name of this option
 * @property-read string $optionType The type of value
 * @property-read mixed $allowed The allowed value(s) of this option
 * 
 * @see CPSOptionCollection
 */
class CPSOption implements IPSBase
{
	//********************************************************************************
	//* Constants
	//********************************************************************************
	
	/**
	* These define the rule pattern types
	*/
	const	RPT_TYPE 			= '__psO#t';
	const	RPT_DEFAULT			= '__psO#d';
	const	RPT_EXTERNAL_NAME	= '__psO#e';
	const	RPT_REQUIRED		= '__psO#r';
	const	RPT_ALLOWED			= '__psO#a';
	const	RPT_PRIVATE			= '__psO#p';

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
	public function isValidType( $sType = null ) { return in_array( PS::nvl( $sType, $this->getRule( self::RPT_TYPE ) ), $this->arValidTypes ); }

	//********************************************************************************
	//* Member Variables
	//********************************************************************************
	
	/**
	 * The name of this option
	 * @var string
	 */
	protected $m_sName;
	
	/**
	 * The value of this option
	 * @var mixed
	 */
	protected $m_oValue = null;

	/**
	 * The rules for this option
	 * @var array
	 */
	protected $m_arRules = null;

	//********************************************************************************
	//* Constructor
	//********************************************************************************
	
	/**
	 * Construct an option
	 * @param string The name
	 * @param mixed The value
	 * @param string|array Rule pattern for validation
	 * @return CPSOption
	 */
	public function __construct( $sName, $oValue = null, $oRulePattern = null )
	{
		//	Private?
		$_bPrivate = ( $sName != ( $_sCleanName = trim( $sName, '_' ) ) );

		//	Build a ruleset
		$this->setRules( $oRulePattern, $_bPrivate );
		
		//	Set values
		$this->m_sName = $_sCleanName;
		$this->m_oValue = PS::nvl( $oValue, $this->getDefaultValue() );
	}
	
	//********************************************************************************
	//* Property Accessors
	//********************************************************************************
	
	/**
	 * Get the option name
	 * @returns string
	 */
	public function getName() { return $this->m_sName; }
	
	/**
	 * Get the option value
	 * @param mixed
	 */
	 
	public function getValue( $bDefaultIfNull = false ) { return $bDefaultIfNull ? PS::nvl( $this->m_oValue, $this->getDefaultValue() ) : $this->m_oValue; }
	
	/**
	 * Set the option value
	 * @param mixed
	 */
	public function setValue( $oValue ) { $this->m_oValue = $oValue; }

	/**
	 * Retrieves a single rule value
	 * @param string The rule
	 * @return mixed
	 */
	public function getRule( $eWhich, $oDefault = null ) { return PS::o( $this->m_arRules, $eWhich, $oDefault ); }
	
	/**
	 * Retrieves all rules
	 * @return array
	 */
	public function getRules() { return $this->m_arRules; }

	/**
	 * Sets a rule pattern value
	 * @param string The rule to set
	 * @param mixed The rule pattern value
	 */
	public function setRule( $eWhich, $oValue ) { PS::so( $this->m_arRules, $eWhich, $oValue ); }
	
	/**
	 * Sets rules from a pattern
	 * @param mixed the rule pattern
	 */
	public function setRules( $oPattern, $bPrivate = false ) { $this->m_arRules = CPSOptionHelper::parseRulePattern( $oPattern, $bPrivate ); }

	//********************************************************************************
	//* Magic Methods 
	//********************************************************************************
	
	/**
	 * Magic override for getting option value
	 * @param string $sKey
	 * @returns mixed
	 */
	public function __get( $sOption )
	{
		if ( ! method_exists( $this, $_sMethod = 'get' . $sOption ) )
			throw new CPSOptionException( "Unknown option property \"{$sOption}\" requested." );
			
		return $this->$_sMethod( $sOption );
	}

	/**
	 * Magic override for setting option value
	 * @param string $sOption
	 * @param mixed $oValue
	 */
	public function __set( $sOption, $oValue )
	{
		if ( ! method_exists( $this, $_sMethod = 'set' . $sOption ) )
			throw new CPSOptionException( "Cannot set option property \"{$sOption}\"." );
			
		$this->$_sMethod( $oValue );
	}
	
	//********************************************************************************
	//* Rule Checkers/Properties
	//********************************************************************************
	
	/**
	 * Returns true if this is a required option
	 * @returns boolean
	 */
	public function getIsRequired() { return $this->getRule( self::RPT_REQUIRED, false ); }
	public function setIsRequired( $bValue ) { $this->setRule( self::RPT_REQUIRED, $bValue ); }
	
	/**
	 * Returns true if this option is private
	 * @returns boolean
	 */
	public function getIsPrivate() { return $this->getRule( self::RPT_PRIVATE, false ); }
	public function setIsPrivate( $bValue ) { $this->setRule( self::RPT_PRIVATE, $bValue ); }
	
	/**
	 * Returns default value of option
	 * @returns mixed
	 */
	public function getDefaultValue() { return $this->getRule( self::RPT_DEFAULT ); }
	public function setDefaultValue( $oValue ) { $this->setRule( self::RPT_DEFAULT, $oValue ); }
	
	/**
	 * Returns external name of option
	 * @returns string
	 */
	public function getExternalName() { return $this->getRule( self::RPT_EXTERNAL_NAME, $this->m_sName ); }
	public function setExternalName( $sName ) { $this->setRule( self::RPT_EXTERNAL_NAME, $sName ); }
	
	/**
	 * Gets the type of this option
	 * @returns string
	 */
	public function getOptionType() { return $this->getRule( self::RPT_TYPE, 'string' ); }
	public function setOptionType( $sType ) { return $this->setRule( self::RPT_TYPE, $sType ); }
	
	/**
	 * Returns the allowable value(s) for this option
	 * @returns mixed
	 */
	public function getAllowed() { return $this->getRule( self::RPT_ALLOWED ); }
	
	/**
	 * Sets the allowable value(s) for this option
	 * @param mixed The allowed option value(s)
	 */
	public function setAllowed( $oValue ) { return $this->setRule( self::RPT_ALLOWED, $oValue ); }

	//********************************************************************************
	//* Miscellaneous
	//********************************************************************************
	
	/***
	 * Used in a string context, this object returns its value.
	 * @returns mixed
	 */
	public function __toString()
	{
		return $this->m_oValue;
	}
	
}
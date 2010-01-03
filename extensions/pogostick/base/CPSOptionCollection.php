<?php
/*
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright &copy; 2009-2010 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * @package 	psYiiExtensions
 * @subpackage 	base
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN $Id$
 * @since 		v1.0.6
 * 
 * @filesource
 */
class CPSOptionCollection extends CAttributeCollection implements IPSOptionContainer
{
	//********************************************************************************
	//* Public Methods
	//********************************************************************************
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		//	This is a case-sensitive collection
		$this->caseSensitive = true;
	}
	
	/**
	 * Adds an item into the collection.
	 * 
	 * Items consist of a key and a value. The value passed in here may either be an actual value for your key
	 * or you may pass in an array where the format is as follows:
	 * 
	 * array( 
	 * 	<actual_value>,
	 * 	<rules_pattern>
	 * )
	 * 
	 * Please see {@link CPSOption} for rule pattern definitions.
	 * 
	 * @param string $sKey key
	 * @param mixed $oPattern value rules
	 * @param mixed $oValue An optional value
	 * @see CPSOption
	 */
	public function add( $sKey, $oValue = null, $oPattern = null )
	{
		//	Add to the collection
		$_oOption = new CPSOption( $sKey, $oValue, $oPattern );
		parent::add( $_oOption->getName(), $_oOption );
	}
	
	/**
	 * Gets an options value
	 * @param string $sKey
	 * @param mixed $oDefault
	 * @returns mixed
	 */
	public function getValue( $sKey, $oDefault = null )
	{
		if ( $this->contains( $sKey ) )
			return $this->itemAt( $sKey )->getValue();
			
		return $oDefault;
	}

	/**
	 * Sets an options value
	 * @param string $sKey
	 * @param mixed $oValue
	 * @param boolean $bAddIfMissing If option is not found, it is added
	 */
	public function setValue( $sKey, $oValue = null, $bAddIfMissing = true )
	{
		if ( null === ( $_oOption = $this->itemAt( $sKey ) ) && $bAddIfMissing ) 
			$this->add( $sKey, $oValue );

		//	Is one defined? Return it...
		$this->itemAt( $sKey )->setValue( $oValue );
	}

	/**
	 * Return this as an array
	 * @param boolean $bPublicOnly
	 * @param array $arOnlyThese
	 * @returns array
	 */
	public function toArray( $bPublicOnly = false, $arOnlyThese = array() )
	{
		$_arOut = array();
		if ( array() === $arOnlyThese ) $arOnlyThese = null;
		
		foreach ( parent::toArray() as $_sKey => $_oOption )
		{
			if ( $bPublicOnly && $_oOption->getIsPrivate() )
				continue;
				
			if ( $arOnlyThese && ! in_array( $_sKey, $arOnlyThese ) )
				continue;
				
			$_arOut[ $_sKey ] = $_oOption;
		}
		
		return $_arOut;
	}

	//********************************************************************************
	//* Interface Requirements
	//********************************************************************************
	
	/**
	 * Alias of CPSOptionCollection::add()
	 * @param string $sKey key
	 * @param mixed $oPattern value rules
	 * @param mixed $oValue An optional value
	 * @see CPSOptionCollection::add()
	 */
	public function addOption( $sKey, $oValue = null, $oPattern = null )
	{
		$this->add( $sKey, $oValue, $oPattern );
	}
	
	/**
	 * Adds an array of options to the collection. Array should be key/pattern pairs:
	 * 
	 * array(
	 * 	'key' => 'pattern',
	 * 	...,
	 * )
	 * 
	 * Where:
	 * 
	 * 		key			=	Name of option
	 * 		pattern		=	Rule pattern
	 * 
	 * @param array $arOptions
	 * @see add
	 * @see CPSOptionHelper::parseRulePattern()
	 */
	public function addOptions( array $arOptions )
	{
		foreach ( $arOptions as $_sKey => $_oValue )
			$this->add( $_sKey, null, $_oValue );
	}

	/**
	* Sets an option
	*
	* @param string $sKey
	* @param mixed $oValue
	* @see getOption
	*/
	public function setOption( $sKey, $oValue ) { $this->setValue( $sKey, $oValue ); }

	/**
	* Set options in bulk
	*
	* @param array $arOptions An array containing option_key => value pairs
	* @see getOptions
	*/
	public function setOptions( array $arOptions ) 
	{ 
		foreach ( $arOptions as $_sKey => $_oOption ) 
			$this->setValue( $_sKey, $_oOption );
	}

	/**
	* Unsets a single option
	* @param string $sKey
	*/
	public function unsetOption( $sKey ) { $this->remove( $sKey ); }

	/***
	 * Get the value of an option
	 * @param string $sKey
	 * @return mixed
	 */
	public function getOption( $sKey, $oDefault = null, $bUnset = false ) 
	{ 
		$_oValue = PS::nvl( $this->itemAt( $sKey ), $oDefault );
		if ( $bUnset ) $this->unsetOption( $sKey );
		return $_oValue;
	}
	
	/**
	 * Returns options in an array. 
	 * @param boolean If true, only returns non-private options
	 * @param array $arOnlyThese Only options named in this array will be returned
	 * @returns array
	 */
	public function getOptions( $bPublicOnly = false, $arOnlyThese = array() ) 
	{ 
		$_arOptions = array();
		if ( empty( $arOnlyThese ) ) $arOnlyThese = null;
		
		foreach ( $this as $_sKey => $_oOption )
		{
			if ( $bPublicOnly && $_oOption->getIsPrivate() )
				continue;
				
			if ( $arOnlyThese && !in_array( $_sKey, $arOnlyThese ) )
				continue;
				
			$_arOptions[ $_sKey ] = $_oOption->getValue();
		}
		
		return $_arOptions;
	}

}
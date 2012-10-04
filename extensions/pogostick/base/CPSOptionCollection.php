<?php
/**
 * This file is part of the psYiiExtensions package.
 *
 * @copyright Copyright &copy; 2009-2010 Pogostick, LLC
 * @link      http://www.pogostick.com Pogostick, LLC.
 * @license   http://www.pogostick.com/licensing
 */

/**
 * @package          psYiiExtensions
 * @subpackage       base
 *
 * @author           Jerry Ablan <jablan@pogostick.com>
 * @version          SVN $Id: CPSOptionCollection.php 368 2010-01-18 01:55:44Z jerryablan@gmail.com $
 * @since            v1.0.6
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
	 *     <actual_value>,
	 *     <rules_pattern>
	 * )
	 *
	 * Please see {@link CPSOption} for rule pattern definitions.
	 *
	 * @param string $key         key
	 * @param mixed  $rulePattern value rules
	 * @param mixed  $value       An optional value
	 *
	 * @see CPSOption
	 */
	public function add( $key, $value = null, $rulePattern = null )
	{
		//	Already here?
		if ( $this->contains( $key ) )
		{
			$this->setOption( $key, $value );
		}
		else
		{
			//	Add to the collection
			$_option = new CPSOption( $key, $value, $rulePattern );
			parent::add( $_option->getName(), $_option );
		}
	}

	/**
	 * Gets an options value
	 *
	 * @param string $key
	 * @param mixed  $defaultValue
	 *
	 * @return mixed
	 */
	public function getValue( $key, $defaultValue = null )
	{
		if ( $this->contains( $key ) )
		{
			return $this->itemAt( $key )->getValue();
		}

		return $defaultValue;
	}

	/**
	 * Sets an options value
	 *
	 * @param string  $key
	 * @param mixed   $value
	 * @param boolean $addIfMissing If option is not found, it is added
	 */
	public function setValue( $key, $value = null, $addIfMissing = true )
	{
		if ( null === ( $_option = $this->itemAt( $key ) ) && $addIfMissing )
		{
			$this->add( $key, $value );
		}

		//	Is one defined? Return it...
		$this->itemAt( $key )->setValue( $value );
	}

	/**
	 * Return this as an array
	 *
	 * @param boolean $publicOnly
	 * @param array   $optionFilter
	 *
	 * @return array
	 */
	public function toArray( $publicOnly = false, $optionFilter = array() )
	{
		$_result = array();
		if ( array() === $optionFilter )
		{
			$optionFilter = null;
		}

		$_data = parent::toArray();

		foreach ( $_data as $_key => $_option )
		{
			if ( $publicOnly && $_option->getIsPrivate() )
			{
				continue;
			}

			if ( $optionFilter && !in_array( $_key, $optionFilter ) )
			{
				continue;
			}

			$_result[$_key] = $_option;
		}

		return $_result;
	}

	//********************************************************************************
	//* Interface Requirements
	//********************************************************************************

	/**
	 * Alias of CPSOptionCollection::add()
	 *
	 * @param string $key         key
	 * @param mixed  $value       An optional value
	 * @param mixed  $rulePattern value rules
	 *
	 * @see CPSOptionCollection::add()
	 */
	public function addOption( $key, $value = null, $rulePattern = null )
	{
		$this->add( $key, $value, $rulePattern );
	}

	/**
	 * Adds an array of options to the collection. Array should be key/pattern pairs:
	 *
	 * array(
	 *     'key' => 'pattern',
	 *     ...,
	 * )
	 *
	 * Where:
	 *
	 *         key            =    Name of option
	 *         pattern        =    Rule pattern
	 *
	 * @param array $options
	 *
	 * @see add
	 * @see CPSOptionHelper::parseRulePattern()
	 */
	public function addOptions( $options = array() )
	{
		foreach ( $options as $_key => $_rulePattern )
		{
			$this->addOption( $_key, null, $_rulePattern );
		}
	}

	/**
	 * Sets an option
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @see getOption
	 */
	public function setOption( $key, $value = null )
	{
		$this->setValue( $key, $value );
	}

	/**
	 * Set options in bulk
	 *
	 * @param array   $options    An array containing option_key => value pairs
	 * @param boolean $clearFirst If true, the option collection is cleared before the options are added.
	 *
	 * @see getOptions
	 */
	public function setOptions( $options = array(), $clearFirst = false )
	{
		if ( $clearFirst )
		{
			$this->clear();
		}

		foreach ( $options as $_key => $_option )
		{
			$this->setValue( $_key, $_option );
		}
	}

	/**
	 * Unsets a single option
	 *
	 * @param string $key
	 */
	public function unsetOption( $key )
	{
		$this->remove( $key );
	}

	/***
	 * Get the value of an option
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function getOption( $key, $defaultValue = null, $unsetValue = false )
	{
		$_value = CPSHelperBase::nvl( $this->itemAt( $key ), $defaultValue );
		if ( $unsetValue )
		{
			$this->unsetOption( $key );
		}

		return $_value;
	}

	/**
	 * Returns options in an array.
	 *
	 * @param       boolean       If true, only returns non-private options
	 * @param array $optionFilter Only options named in this array will be returned
	 *
	 * @return array
	 */
	public function getOptions( $publicOnly = false, $optionFilter = array() )
	{
		$_optionList = array();
		if ( empty( $optionFilter ) )
		{
			$optionFilter = null;
		}

		foreach ( $this as $_key => $_option )
		{
			if ( $publicOnly && $_option->getIsPrivate() )
			{
				continue;
			}

			if ( $optionFilter && !in_array( $_key, $optionFilter ) )
			{
				continue;
			}

			$_optionList[$_key] = $_option->getValue();
		}

		return $_optionList;
	}

}
<?php
/**
 * CPSOptionsBehavior class file.
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://ps-yii-extensions.googlecode.com
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @license http://www.pogostick.com/license/
 */

/**
 * CPSOptionsBehavior is a behavior wrapper class for CPSOptionManager.
 *
 * It provides a base behavior for generic options settings for use with any class.
 *
 * addOptions array format:
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
	* An instance of our option manager for this behavior
	*
	* @var (@link CPSOptionManager)
	*/
	private $m_oOptions;

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	* Creates our option manager object
	* @returns CPSOptionsBehavior
	* @see (@link CPSOptionManager)
	*/
	public function __construct()
	{
		//	Log
		Yii::log( 'constructed psOptionsBehavior object for [' . get_parent_class() . ']' );

		//	build our option manager...
		$this->m_oOptions = new CPSOptionManager();
	}

	/**
	* Returns a reference to the entire reference array
	*
	* @returns array A reference to the internal options array
	* @see getOption
	*/
	public function &getOptions() { return $this->m_oOptions->getOptions(); }

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
	public function setOptions( array $arOptions ) { $this->m_oOptions->setOptions( $arOptions ); }

	/**
	* Retrieves an option value from the options array. If key doesn't exist, it's created as an empty array and returned.
	*
	* @param string $sKey
	* @return mixed
	* @see getOptions
	*/
	public function getOption( $sKey ) { return $this->m_oOptions->getOption( $sKey ); }

	/**
	* Sets a single option to the array
	*
	* @param string $sKey
	* @param mixed $oValue
	* @return null
	* @see setOptions
	*/
	public function setOption( $sKey, $oValue ) { $this->m_oOptions->setOption( $sKey, $oValue ); }

}
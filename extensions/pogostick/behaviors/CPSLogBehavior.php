<?php
/**
 * This file is part of the psYiiExtensions package.
 *
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 * @filesource
 */

/**
 * A base class for AR behaviors
 *
 * @package		pyel
 * @subpackage 	behaviors
 *
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @since 		v1.1.0
 */
class CPSLogBehavior extends CBehavior implements IPSBehavior
{
	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::_construct( $options );

		//	Preinitialize
		$this->preinit();
	}

	//********************************************************************************
	//* Interface Requirements
	//********************************************************************************

	/**
	 * Preinitialize the object
	 */
	public function preinit()
	{
		CPSHelperBase::createInternalName( $this );
	}

	/**
	 * Initialize
	 */
	public function init()
	{
	}

	/**
	* Get the internal name of our component
	* @return string
	*/
	public function getInternalName() { return $this->_internalName; }

	/**
	* Set the internal name of this component
	* @param string
	*/
	public function setInternalName( $sValue ) { $this->_internalName = $sValue; }

}
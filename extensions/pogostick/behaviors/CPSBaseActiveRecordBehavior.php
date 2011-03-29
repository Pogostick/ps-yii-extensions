<?php
/**
 * This file is part of the psYiiExtensions package.
 *
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * A base class for AR behaviors
 *
 * @package 	psYiiExtensions
 * @subpackage 	behaviors
 *
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id: CPSBaseActiveRecordBehavior.php 405 2010-10-21 21:44:02Z jerryablan@gmail.com $
 * @since 		v1.0.6
 *
 * @filesource
 */
class CPSBaseActiveRecordBehavior extends CActiveRecordBehavior implements IPSBehavior
{
	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	/**
	* @var string The internal name of the component.
	*/
	protected $_internalName;

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	 * Constructor
	 */
	public function __construct()
	{
		//	Log it and check for issues...
		if ( defined( 'PYE_TRACE_LEVEL' ) && PYE_TRACE_LEVEL > 4 )
			CPSLog::trace( 'pogostick.behaviors', __CLASS__ . ' constructed' );

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
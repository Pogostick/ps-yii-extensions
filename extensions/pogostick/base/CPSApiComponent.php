<?php
/*
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * CPSApiComponent provides a convenient base class for APIs.
 * Introduces three new events:
 * 
 * onBeforeApiCall
 * onAfterApiCall
 * onRequestComplete
 * 
 * Each are called respectively and pass the handler a CPSApiEvent
 * object with details of the call.
 * 
 * @package 	psYiiExtensions
 * @subpackage 	base
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id: CPSApiComponent.php 405 2010-10-21 21:44:02Z jerryablan@gmail.com $
 * @since 		v1.0.0
 * 
 * @filesource
 */
class CPSApiComponent extends CPSComponent
{
	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	* Preinitialize
	*
	*/
	public function preinit()
	{
		//	Call daddy...
		parent::preinit();

		//	Attach our default behavior
		$this->attachBehavior( $this->_internalName, 'pogostick.behaviors.CPSApiBehavior' );
	}

	//********************************************************************************
	//* Private Methods
	//********************************************************************************

}
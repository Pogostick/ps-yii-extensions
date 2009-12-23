<?php
/*
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * This interface defines methods required for base psYiiExtension objects.
 * 
 * @package 	psYiiExtensions
 * @subpackage 	base
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN $Id$
 * @since 		v1.0.6
 * 
 * @filesource
 */
interface IPogostickBehavior extends IPogostickBehaviorBase
{
	//********************************************************************************
	//* Public Methods
	//********************************************************************************
	/**
	* Returns true if this component has attached behaviors
	* @returns boolean
	*/
	public function getHasBehaviors();
	/**
	* Sets the flag to indicate that this component has attached behaviors
	* @param boolean $bValue
	*/
	public function setHasBehaviors( $bValue );
	/**
	* Retrieves the behaviors attached to this component
	* @returns array
	*/
	public function getBehaviors();
	/**
	* Returns true if the method exists in an attached behavior
	* @param string $sMethodName
	* @return boolean
	*/
	public function &hasBehaviorMethod( $sMethodName );
	/**
	* Returns true if the property exists within an attached behavior
	* @param string $sName
	* @return boolean
	*/
	public function &hasBehaviorProperty( $sName );
	/**
	* Returns a reference to the value of an attached behavior property
	* @param string $sName
	* @returns mixed
	*/
	public function &getBehaviorProperty( $sName );
	/**
	* Sets the value of an attached behavior property
	* @param string $sName
	* @param mixed $oValue
	*/
	public function setBehaviorProperty( $sName, $oValue );

}
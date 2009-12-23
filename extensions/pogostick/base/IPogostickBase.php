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
interface IPogostickBase extends IPogostick
{
	//********************************************************************************
	//* Public Methods
	//********************************************************************************
	
	/**
	* Get the internal name of our component
	* @returns string
	*/
	public function getInternalName();
	/**
	* Set the internal name of this component
	* @param string
	*/
	public function setInternalName( $sValue );
	/**
	* Get the internal name with the delimiter appended
	* @returns string
	*/
	public function getNamePrefix();
	/**
	* Get the delimiter used in our options indexes
	* @returns string
	*/
	public function getPrefixDelimiter();

}
<?php
/*
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright &copy; 2009-2010 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * Implementors of this interface contain options.
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
interface IPSOptionContainer extends IPSBase
{
	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	* Adds an option to the collection.
	*/
	function addOption( $sKey, $oValue = null, $oPattern = null );

	/**
	* Add an array of options to the option collection
	*/
	function addOptions( array $arOptions );

	/**
	* Retrieves an option value
	*/
	function getOption( $sKey, $oDefault = null, $bUnset = false );

	/**
	* Returns all options as a key=>value pair associative array
	* @returns array
	*/
	function getOptions( $bPublicOnly = false );

	/**
	* Sets a single option value
	* @param string $sKey
	* @param mixed $oValue
	*/
	function setOption( $sKey, $oValue );

	/**
	* Set options in a bulk manner. $arOptions should be array of key => value pairs.
	*/
	function setOptions( array $arOptions );

	/**
	* Unsets a single option
	*/
	function unsetOption( $sKey );

	/**
	* Checks if the collection contains a key
	*/
	function contains( $sKey );

}
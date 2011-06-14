<?php
/**
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * Provides functionality helpful for parent/child relationships
 * 
 * @package 	psYiiExtensions
 * @subpackage 	behaviors
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id: CPSParentChildBehavior.php 353 2010-01-02 19:43:58Z jerryablan@gmail.com $
 * @since 		v1.0.6
 * 
 * @filesource
 */
class CPSParentChildBehavior extends CPSBaseActiveRecordBehavior
{
	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	* Returns whether or not this model has children
	* Override as necessary
	* 
	* @return boolean
	*/
	public function hasChildren()
	{
		return false;
	}
	
	/**
	* Named scope to return child models
	* Override as necessary
	* 
	* @param integer $iParentId Default to null
	*/
	public function childOf( $iParentId = null )
	{
		return $this;
	}
	
	/**
	* Outputs a string of UL/LI tags from an array of models suitable
	* for menu structures
	* 
	* @param array $arModel
	* @param array $arOptions
	* @return string
	*/
	public function asUnorderedList( $arModel = array(), $arOptions = array() )
	{
		return CPSTransform::asUnorderedList( $arModel, $arOptions );
	}
	
}
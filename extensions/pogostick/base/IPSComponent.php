<?php
/*
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright &copy; 2009-2010 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */
/**
 * This interface defines methods required for base pYe objects.
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
interface IPSComponent extends IPSBase
{
	//********************************************************************************
	//* Public Methods
	//********************************************************************************
	
	/**
	 * Preinitialize the object
	 */
	function preinit();
	
	/**
	 * Initialize the object
	 */
	function init();
	
	/**
	* Get the internal name of our component
	* @returns string
	*/
	function getInternalName();
	
	/**
	* Set the internal name of this component
	* @param string
	*/
	function setInternalName( $sValue );

}
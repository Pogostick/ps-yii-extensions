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
interface IPogostickBehaviorBase extends IPogostick
{
	//********************************************************************************
	//* Constants
	//********************************************************************************
	
	/**
	* These define the indexes into our options array for tracking information
	*/
	const BEHAVIOR_META_METHODS = '_classMethods';
	const BEHAVIOR_META_OBJECT = '_object';
	const BEHAVIOR_META_VALID = '_validOptions';
	const BEHAVIOR_META_VARS = '_classVars';

}
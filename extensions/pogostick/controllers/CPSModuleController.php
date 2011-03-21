<?php
/**
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */
/**
 * CPSModuleController provides filtered access to module resources
 * 
 * @package 	psYiiExtensions
 * @subpackage 	controllers
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id: CPSModuleController.php 355 2010-01-02 22:19:05Z jerryablan@gmail.com $
 * @since 		v1.0.4
 * 
 * @filesource
 */
abstract class CPSModuleController extends CPSController
{
	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	* The filter method for 'accessControl' filter.
	* This filter is a wrapper of {@link CPSModuleAccessControlFilter}.
	* To use this filter, you must override the {@link accessRules} method.
	* @param CFilterChain the filter chain that the filter is on.
	*/
    public function filterAccessControl( $filterChain )
    {
        $filter = new CPSModuleAccessControlFilter();
        $filter->setRules( $this->accessRules() );
        $filter->filter( $filterChain );
    }

}
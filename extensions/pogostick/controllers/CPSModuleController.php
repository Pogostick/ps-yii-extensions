<?php
/**
 * CPSModuleController class file.
 *
 * @filesource
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://www.pogostick.com Pogostick, LLC.
 * @package psYiiExtensions
 * @subpackage Controllers
 * @since v1.0.4
 * @version SVN: $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified  $Date$
 */
 
//	Imports
Yii::import( 'pogostick.filters.CPSModuleAccessControlFilter' );
 
/**
 * CPSModuleController provides filtered access to module resources
 *
 * @package psYiiExtensions
 * @subpackage Controllers
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
        $filter = new CPSModuleAccessControlFilter;
        $filter->setRules( $this->accessRules() );
        $filter->filter( $filterChain );
    }

}
<?php
/**
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * CPSModuleAccessControlFilter provides module-based filtering
 * 
 * @package 	psYiiExtensions
 * @subpackage 	filters
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id: CPSModuleAccessControlFilter.php 358 2010-01-02 23:33:40Z jerryablan@gmail.com $
 * @since 		v1.0.4
 * 
 * @filesource
 */
class CPSModuleAccessControlFilter extends CAccessControlFilter implements IPSBase
{
	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	 * Performs the pre-action filtering.
	 * @param CFilterChain $oFilterChain the filter chain that the filter is on.
	 * @return boolean whether the filtering process should continue and the action should be executed.
	 * @access protected
	 */
	protected function preFilter( $oFilterChain )
	{
		$_oApp = Yii::app();
		$_oModule = Yii::app()->controller->module;
		$_oRequest = $_oApp->getRequest();
		$_oUser = $_oModule->user;
		$_sVerb = $_oRequest->getRequestType();
		$_sIP = $_oRequest->getUserHostAddress();
		$_arRules = $this->getRules();
		
		foreach ( $_arRules as $_oRule )
		{
			//	Is allowed?
			if ( ( $_iAllow = $_oRule->isUserAllowed( $_oUser, $oFilterChain->controller, $oFilterChain->action, $_sIP, $_sVerb ) ) > 0 )
				return true;
			
			if ( $_iAllow < 0 )
			{
				$this->accessDenied( $_oUser );
				return false;
			}
		}

		//	If we made it here, it's all good...
		return true;
	}

}
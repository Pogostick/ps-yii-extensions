<?php
/**
 * CPSModuleAccessControlFilter class file.
 *
 * @filesource
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://www.pogostick.com Pogostick, LLC.
 * @package psYiiExtensions
 * @subpackage Filters
 * @since v1.0.4
 * @version SVN: $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified  $Date$
 */
/**
 * CPSModuleAccessControlFilter provides module-based filtering
 *
 * @package psYiiExtensions
 * @subpackage Filters
 */
class CPSModuleAccessControlFilter extends CAccessControlFilter
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
				break;
			
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
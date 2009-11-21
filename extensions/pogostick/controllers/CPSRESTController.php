<?php
/**
 * CPSRESTController class file.
 *
 * @filesource
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://www.pogostick.com Pogostick, LLC.
 * @package psYiiExtensions
 * @subpackage Controllers
 * @since v1.0.6
 * @version SVN: $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified  $Date$
 */

 /**
 * CPSRESTController provides REST functionality
 *
 * @package psYiiExtensions
 * @subpackage Controllers
 */
class CPSRESTController extends CPSController
{
	//********************************************************************************
	//* Constants
	//********************************************************************************

	//********************************************************************************
	//* Member Variables
	//********************************************************************************
	
	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	 * Runs the action after passing through all filters.
	 * This method is invoked by {@link runActionWithFilters} after all 
	 * possible filters have been executed and the action starts to run.
	 * 
	 * @param CAction $oAction Action to run
	 */
	public function runAction( $oAction )
	{
		$_oPrior = $this->getAction();
		$this->setAction( $oAction );
		
		if ( $this->beforeAction( $oAction ) )
		{
			$this->dispatchRequest( $oAction );
			$this->afterAction( $oAction );
		}
		
		$this->setAction( $_oPrior );
	}

	/**
	 * Creates the action instance based on the action name.
	 * The action can be either an inline action or an object.
	 * The latter is created by looking up the action map specified in {@link actions}.
	 * @param string ID of the action. If empty, the {@link defaultAction default action} will be used.
	 * @return CAction the action instance, null if the action does not exist.
	 * @see actions
	 */
	public function createAction( $sActionId )
	{
		$_sActionId = ( $sActionId === '' ) ? $this->defaultAction : $sActionId;
		$_sMethod = 'request' . $_sActionId;
		
		//	Is it a valid request?
		if ( method_exists( $this, 'get' . $_sActionId ) )
			$_sMethod = 'get' . $_sActionId;
		else if ( method_exists( $this, 'post' . $_sActionId ) )
			$_sMethod = 'post' . $_sActionId;
		else if ( ! method_exists( $this, 'request' . $_sActionId ) )
			return $this->missingAction( $_sActionId );

		return new CPSRESTAction( $this, $_sActionId );
	}

	//********************************************************************************
	//* Private Methods
	//********************************************************************************
	
	/**
	 * Runs the named REST action.
	 * Filters specified via {@link filters()} will be applied.
	 * @param string $sActionId Action id
	 * @throws CHttpException if the action does not exist or the action name is not proper.
	 * @see filters
	 * @see createAction
	 * @see runAction
	 * @access protected
	 */
	protected function dispatchRequest( CAction $oAction )
	{
		$_sActionId = $oAction->getId();
		$_sMethod = 'request' . $_sActionId;
		$_arParams = $_REQUEST;
		$_arUrlParams = array();
		
		//	If additional parameters are specified in the URL, convert to parameters...
		$_sUri = Yii::app()->getRequest()->getRequestUri();
		$_sUri = str_ireplace( '/' . $this->getId() . '/' . $_sActionId . '/', '', $_sUri );
		$_arOpts = explode( '/', trim( $_sUri, '/' ) );
		
		for ( $_i = 0, $_iSize = count( $_arOpts ); $_i < $_iSize; $_i ++ )
			$_arUrlParams[ $_i ] = $_arOpts[ $_i ];
		
		//	Is it a valid request?
		if ( method_exists( $this, 'get' . $_sActionId ) )
		{
			$_sMethod = 'get' . $_sActionId;
			$_arParams = $_GET;
		}
		else if ( method_exists( $this, 'post' . $_sActionId ) )
		{
			$_sMethod = 'post' . $_sActionId;
			$_arParams = $_POST;
		}
		else if ( ! method_exists( $this, 'request' . $_sActionId ) )
			return $this->missingAction( $_sActionId );

		//	All rest methods echo their output
		echo call_user_func_array( array( $this, $_sMethod ), array_merge( $_arUrlParams, $_arParams ) );
	}

}
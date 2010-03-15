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
		$this->pushAction( $this->getAction() );
		
		$this->setAction( $oAction );
		
		if ( $this->beforeAction( $oAction ) )
		{
			$this->dispatchRequest( $oAction );
			$this->afterAction( $oAction );
		}

		$this->setAction( $this->popAction() );
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
		
		//	Is it a valid request?
		if ( ! method_exists( $this, 'get' . $_sActionId ) && ! method_exists( $this, 'post' . $_sActionId ) && ! method_exists( $this, 'request' . $_sActionId ) )
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
		$_arParams = $_REQUEST;
		$_arUrlParams = array();
		$_arOpts = array();

		//	If additional parameters are specified in the URL, convert to parameters...
		$_sUri = Yii::app()->getRequest()->getRequestUri();

		if ( null != ( $_sUri = trim( str_ireplace( '/' . $this->getId() . '/' . $_sActionId, '', $_sUri ) ) ) )
		{
			$_sUri = trim( $_sUri, '/?' );
			$_arOpts = ( ! empty( $_sUri ) ? explode( '/', $_sUri ) : array() );
			
			foreach ( $_arOpts as $_sKey => $_oValue )
			{
				if ( false !== strpos( $_oValue, '=' ) )
				{
					if ( null != ( $_arTemp = explode( '=', $_oValue ) ) )
						$_arOpts[ $_arTemp[0] ] = $_arTemp[1];
						
					unset( $_arOpts[ $_sKey ] );
				}
				else
					$_arOpts[ $_sKey ] = $_oValue;
			}
		}
		
		//	Any query string? (?x=y&...)
		if ( null != ( $_sQuery = parse_url( $_sUri, PHP_URL_QUERY ) ) )
			$_arOpts = array_merge( explode( '=', $_sQuery ), $_arOpts );
		
		//	load into url params
		foreach ( $_arOpts as $_sKey => $_sValue )
			if ( ! isset( $_arUrlParams[ $_sKey ] ) ) $_arUrlParams[ $_sKey ] = $_sValue;
		
		//	Is it a valid request?
		$_sType = strtolower( $this->getRequest()->getRequestType() );
		$_sMethod = $_sType . ucfirst( $_sActionId );
		
		foreach ( ( $_sType == 'post' ? $_POST : $_GET ) as $_sKey => $_oValue )
		{
			if ( ! is_array( $_oValue ) )
				$_arUrlParams[ $_sKey ] = $_oValue;
			else
			{
				foreach ( $_oValue as $_sSubKey => $_oSubValue )
					$_arUrlParams[ $_sSubKey ] = $_oSubValue;
			}
		}

		if ( ! method_exists( $this, $_sMethod ) )
		{
			//	Is it a valid catchall request?
			if ( ! method_exists( $this, 'request' . $_sActionId ) )
				//	No clue what it is, so must be bogus. Hand off to missing action...
				return $this->missingAction( $_sActionId );

			$_sMethod = 'request' . $_sActionId;
		}

		//	All rest methods echo their output
		echo call_user_func_array( array( $this, $_sMethod ), array_values( $_arUrlParams ) );
	}

}
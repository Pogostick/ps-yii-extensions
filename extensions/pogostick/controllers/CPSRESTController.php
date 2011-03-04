<?php
/**
 * CPSRESTController class file.
 *
 * @filesource
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://www.pogostick.com Pogostick, LLC.
 * @package psYiiExtensions
 * @subpackage Controllers
 * @since v1.0.6
 * @version SVN: $Revision: 402 $
 * @modifiedby $LastChangedBy: jerryablan@gmail.com $
 * @lastmodified  $Date: 2010-09-11 19:00:16 -0400 (Sat, 11 Sep 2010) $
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
	//* Public Methods
	//********************************************************************************

	/**
	 * Runs the action after passing through all filters.
	 * This method is invoked by {@link runActionWithFilters} after all
	 * possible filters have been executed and the action starts to run.
	 *
	 * @param CAction $action Action to run
	 */
	public function runAction( $action )
	{
		$this->pushAction( $this->getAction() );

		$this->setAction( $action );

		if ( $this->beforeAction( $action ) )
		{
			$this->dispatchRequest( $action );
			$this->afterAction( $action );
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
		$_actionId = ( $sActionId === '' ) ? $this->defaultAction : $sActionId;

		//	Is it a valid request?
		if ( ! method_exists( $this, 'get' . $_actionId ) && ! method_exists( $this, 'post' . $_actionId ) && ! method_exists( $this, 'request' . $_actionId ) )
			return $this->missingAction( $_actionId );

		return new CPSRESTAction( $this, $_actionId );
	}

	/**
	 * Converts an array to JSON
	 *
	 * @param array $arData
	 */
	public function arrayToJSON( $arData = array() )
	{
		$_arOut = array();

		foreach ( $arData as $_key => &$_value )
			$_arOut[] = $_key . ':' . $this->toJSON( $_value );

		return '{' . implode( ',', $_arOut ) . '}';
	}

	/***
	 * Converts an item to JSON
	 *
	 * @param mixed $oValue
	 */
	function toJSON( $oValue )
	{
		if ( is_array( $oValue ) )
			$_oOut = $this->arrayToJSON( $oValue );
		else if ( is_string( $oValue ) )
			$_oOut = '"' . addslashes( $oValue ) . '"';
		else if ( is_bool( $oValue ) )
			$_oOut = $oValue ? 'true' : 'false';
		else if ( is_null( $oValue ) )
			$_oOut = '""';
		else
			$_oOut = $oValue;

		return $_oOut;
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
	protected function dispatchRequest( CAction $action )
	{
		$_actionId = $action->getId();
		$_parameters = $_REQUEST;
		$_urlParameters = array();
		$_options = array();

		//	If additional parameters are specified in the URL, convert to parameters...
		$_uri = PS::_gr()->getRequestUri();

		if ( null != ( $_uri = trim( str_ireplace( '/' . $this->getId() . '/' . $_actionId, '', $_uri ) ) ) )
		{
			$_uri = trim( $_uri, '/?' );
			$_options = ( ! empty( $_uri ) ? explode( '/', $_uri ) : array() );

			foreach ( $_options as $_key => $_value )
			{
				if ( false !== strpos( $_value, '=' ) )
				{
					if ( null != ( $_list = explode( '=', $_value ) ) )
						$_options[ $_list[0] ] = $_list[1];

					unset( $_options[ $_key ] );
				}
				else
					$_options[ $_key ] = $_value;
			}
		}

		//	Any query string? (?x=y&...)
		if ( null != ( $_queryString = parse_url( $_uri, PHP_URL_QUERY ) ) )
			$_options = array_merge( explode( '=', $_queryString ), $_options );

		//	load into url params
		foreach ( $_options as $_key => $_value )
			if ( ! isset( $_urlParameters[ $_key ] ) ) $_urlParameters[ $_key ] = $_value;

		//	Is it a valid request?
		$_requestType = strtolower( $this->getRequest()->getRequestType() );
		$_requestMethod = $_requestType . ucfirst( $_actionId );

		if ( $_requestType == 'post' )
		{
			foreach ( $_POST as $_key => $_value )
			{
				if ( ! is_array( $_value ) )
					$_urlParameters[ $_key ] = $_value;
				else
				{
					foreach ( $_value as $_subKey => $_subValue )
						$_urlParameters[ $_subKey ] = $_subValue;
				}
			}
		}

		if ( ! method_exists( $this, $_requestMethod ) )
		{
			//	Is it a valid catchall request?
			if ( ! method_exists( $this, 'request' . $_actionId ) )
				//	No clue what it is, so must be bogus. Hand off to missing action...
				return $this->missingAction( $_actionId );

			$_requestMethod = 'request' . $_actionId;
		}

		CPSLog::trace( __METHOD__, 'calling ' . $_requestMethod );

		echo call_user_func_array( array( $this, $_requestMethod ), array_values( $_urlParameters ) );
	}

	/**
	 * Creates a JSON encoded array (as a string) with a standard REST response. Override to provide
	 * a different response format.
	 *
	 * @param array $resultList
	 * @param boolean $isError
	 * @param string $errorText
	 * @param integer $errorCode
	 * @return string JSON encoded array
	 */
	protected function _createResponse( $resultList = array(), $isError = false, $errorMessage = 'failure', $errorCode = 0 )
	{
		if ( $isError )
		{
			$_response = array(
				'result' => 'failure',
				'errorMessage' => $errorMessage,
				'errorCode' => $errorCode,
			);

			if ( $resultList )
				$_response['resultData'] = $resultList;
		}
		else
		{
			$_response = array(
				'result' => 'success',
			);

			if ( $resultList )
				$_response['resultData'] = $resultList;
		}

		return json_encode( $_response );
	}

	/***
	 * Translates errors from normal model attribute names to REST map names
	 * @param CActiveRecord $model
	 * @return array
	 */
	protected function _translateErrors( CActiveRecord $model )
	{
		if ( $_errorList = $model->getErrors() )
		{
			if ( method_exists( $model, 'attributeRestMap' ) )
			{
				$_restMap = $model->attributeRestMap();
				$_resultList = array();

				foreach ( $_errorList as $_key => $_value )
				{
					if ( in_array( $_key, array_keys( $_restMap ) ) )
						$_resultList[ $_restMap[ $_key ] ] = $_value;
				}

				$_errorList = $_resultList;
			}
		}

		return $_errorList;
	}
}
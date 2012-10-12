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
 *
 * @property integer $outputFormat
 * @property boolean $singleParameterActions
 *
 */
class CPSRESTController extends CPSController
{
	//**************************************************************************
	//* Private Members
	//**************************************************************************

	/**
	 * The requested output format. Defaults to null which requires the handler
	 * to return the proper format.
	 *
	 * @var int
	 */
	protected $_outputFormat = null;

	/**
	 * If true, all inbound request parameters will be passed to the action
	 * as a hash instead of individual arguments.
	 *
	 * @var bool
	 */
	protected $_singleParameterActions = false;

	//********************************************************************************
	//* Private Members
	//********************************************************************************

	/**
	 * @var int The type of data
	 */
	protected $_dataFormat = PS::OF_JSON;

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
			$this->_dispatchRequest( $action );
			$this->afterAction( $action );
		}

		$this->setAction( $this->popAction() );
	}

	/**
	 * Creates the action instance based on the action name.
	 * The action can be either an inline action or an object.
	 * The latter is created by looking up the action map specified in {@link actions}.
	 *
	 * @param $actionId
	 *
	 * @return CAction the action instance, null if the action does not exist.
	 * @see actions
	 */
	public function createAction( $actionId )
	{
		$_actionId = ( empty( $actionId ) ) ? $this->defaultAction : $actionId;
		$_requestMethod = strtolower( PS::_gr()->getRequestType() );

//	Let _dispatchRequest() do the check.
//		//	Is it a valid request?
//		if ( ! method_exists( $this, $_requestMethod ) && ! method_exists( $this, 'request' . ucfirst( $_actionId ) ) )
//			return $this->missingAction( $_actionId );

		return new CPSRESTAction( $this, $_actionId, $_requestMethod );
	}

	/**
	 * *** THIS IS A PUBLIC EXPOSURE OF THE PROTECTED METHOD ***
	 *
	 * Runs the named REST action.
	 * Filters specified via {@link filters()} will be applied.
	 *
	 * @param \CPSRESTAction $action
	 *
	 * @return mixed
	 * @see createAction
	 * @see runAction
	 * @access protected
	 * @throws CHttpException if the action does not exist or the action name is not proper.
	 */
	public function dispatchRequest( CAction $action )
	{
		return $this->_dispatchRequest( $action );
	}

	//********************************************************************************
	//* Private Methods
	//********************************************************************************

	/**
	 * Runs the named REST action.
	 * Filters specified via {@link filters()} will be applied.
	 *
	 * @param \CAction $action
	 *
	 * @return mixed
	 * @see createAction
	 * @see runAction
	 * @access protected
	 * @throws CHttpException if the action does not exist or the action name is not proper.
	 */
	//	@todo This needs serious re-working
	protected function _dispatchRequest( CAction $action )
	{
		$_actionId = $action->getId();
		$_parameters = $_REQUEST;
		$_urlParameters = array();
		$_options = array();

		//	If additional parameters are specified in the URL, convert to parameters...
		$_uri = PS::_gr()->getRequestUri();
		$_frag = '/' . $this->getId() . '/' . $_actionId;

		//	Strip off everything after the route...
		if ( null != ( $_uri = trim( substr( $_uri, stripos( $_uri, $_frag ) + strlen( $_frag ) ), ' /?' ) ) )
		{
			$_options = ( !empty( $_uri ) ? explode( '/', $_uri ) : array() );

			foreach ( $_options as $_key => $_value )
			{
				if ( false !== strpos( $_value, '=' ) )
				{
					if ( null != ( $_list = explode( '=', $_value ) ) )
					{
						$_options[$_list[0]] = $_list[1];
					}

					unset( $_options[$_key] );
				}
				else
				{
					$_options[$_key] = $_value;
				}
			}
		}

		//	Any query string? (?x=y&...)
		if ( null != ( $_queryString = parse_url( $_uri, PHP_URL_QUERY ) ) )
		{
			$_queryOptions = array();
			parse_str( $_queryString, $_queryOptions );
			$_options = array_merge( $_queryOptions, $_options );

			//	Remove route
			if ( isset( $_options['r'] ) )
			{
				unset( $_options['r'] );
			}
		}

		//	load into url params
		foreach ( $_options as $_key => $_value )
		{
			if ( !isset( $_urlParameters[$_key] ) )
			{
				$_urlParameters[$_key] = $_value;
			}
		}

		//	Is it a valid request?
		$_requestType = strtolower( PS::_gr()->getRequestType() );
		$_requestMethod = $_requestType . ucfirst( $_actionId );

		if ( $_requestType == 'post' )
		{
			foreach ( $_POST as $_key => $_value )
			{
				if ( !is_array( $_value ) )
				{
					$_urlParameters[$_key] = $_value;
				}
				else
				{
					foreach ( $_value as $_subKey => $_subValue )
					{
						$_urlParameters[$_subKey] = $_subValue;
					}
				}
			}
		}
		else if ( 'get' != $_requestType )
		{
		}

		if ( !method_exists( $this, $_requestMethod ) )
		{
			//	Is it a valid catchall request?
			if ( !method_exists( $this, 'request' . $_actionId )
			)
				//	No clue what it is, so must be bogus. Hand off to missing action...
			{
				return $this->missingAction( $_actionId );
			}

			$_requestMethod = 'request' . $_actionId;
		}

		$_callResults = call_user_func_array(
			array(
				$this,
				$_requestMethod
			),
			//	Pass in parameters collected as a single array or individual values
            $this->_singleParameterActions ? array( $_urlParameters ) : array_values( $_urlParameters )
        );
		//	Echo output...
		$_output = $this->_formatOutput( $_callResults );
		echo $_output;

		//	Also return the results should anyone care to have them...
		return $_callResults;
	}

	/**
	 * Converts the given argument to the proper format for
	 * return the consumer application.
	 *
	 * @param mixed $output
	 *
	 * @return mixed
	 */
	protected function _formatOutput( $output )
	{
		//	Transform output
		switch ( $this->_outputFormat )
		{
			case PS::OF_JSON:
				@header( 'Content-type: application/json' );

				//	Are we already in JSON?
				if ( null !== @json_decode( $output ) )
				{
					break;
				}

				/**
				 * Chose NOT to overwrite in the case of an error while
				 * formatting into json via builtin.
				 */
				//	@todo Not sure if this is all that wise, will cause confusion when your methods return nada.
				if ( false !== ( $_response = json_encode( $output ) ) )
				{
					$output = $_response;
				}
				break;

			case PS::OF_XML:
				$output = PS::arrayToXml( $output, 'response' );

				//	Set appropriate content type
				if ( stristr( $_SERVER['HTTP_ACCEPT'], 'application/xhtml+xml' ) )
				{
					header( 'Content-type: application/xhtml+xml;charset=utf-8' );
				}
				else
				{
					header( 'Content-type: text/xml;charset=utf-8' );
				}
				break;

			case PS::OF_RAW:
				//	Nothing to do...
				break;

			case PS::OF_ASSOC_ARRAY:
			default:
			if ( !is_array( $output ) )
			{
				$output = array( $output );
			}
				break;
		}

		//	And return the formatted (or not as the case may be) output
		return $output;
	}

	/**
	 * Creates a JSON encoded array (as a string) with a standard REST response. Override to provide
	 * a different response format.
	 *
	 * @param array   $resultList
	 * @param boolean $isError
	 * @param string  $errorMessage
	 * @param integer $errorCode
	 * @param array   $additionalInfo
	 *
	 * @return string JSON encoded array
	 */
	protected function _createResponse( $resultList = array(), $isError = false, $errorMessage = 'failure', $errorCode = 0, $additionalInfo = array() )
	{
		if ( $isError )
		{
			$_response = array(
				'result'       => 'failure',
				'errorMessage' => $errorMessage,
				'errorCode'    => $errorCode,
			);

			if ( $resultList )
			{
				$_response['resultData'] = $resultList;
			}
		}
		else
		{
			$_response = array(
				'result' => 'success',
			);

			if ( $resultList )
			{
				$_response['resultData'] = $resultList;
			}
		}

		//	Add in any additional info...
		if ( is_array( $additionalInfo ) && !empty( $additionalInfo ) )
		{
			foreach ( $additionalInfo as $_key => $_value )
			{
				$_response[$_key] = $_value;
			}
		}

		return $_response;
	}

	/**
	 * Creates a JSON encoded array (as a string) with a standard REST response. Override to provide
	 * a different response format.
	 *
	 * @param string|Exception $errorMessage
	 * @param integer          $errorCode
	 *
	 * @return string JSON encoded array
	 */
	protected function _createErrorResponse( $errorMessage = 'failure', $errorCode = 0 )
	{
		$_additionalInfo = null;

		if ( $errorMessage instanceof Exception )
		{
			$_ex = $errorMessage;

			$errorMessage = $_ex->getMessage();
			$errorCode = ( $_ex instanceof CHttpException ? $_ex->statusCode : $_ex->getCode() );
			$_previous = $_ex->getPrevious();

			//	In debug mode, we output more information
			if ( $this->_debugMode )
			{
				$_additionalInfo = array(
					'errorType'  => 'Exception',
					'errorClass' => get_class( $_ex ),
					'errorFile'  => $_ex->getFile(),
					'errorLine'  => $_ex->getLine(),
					'stackTrace' => $_ex->getTrace(),
					'previous'   => ( $_previous ? $this->_createErrorResponse( $_previous ) : null ),
				);
			}
		}

		//	Set some error headers
		header('Pragma: no-cache');
		header('Cache-Control: no-store, no-cache, max-age=0, must-revalidate');

		return $this->_createResponse( array(), true, $errorMessage, $errorCode, $_additionalInfo );
	}

	/***
	 * Translates errors from normal model attribute names to REST map names
	 *
	 * @param CActiveRecord $model
	 *
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
					{
						$_resultList[$_restMap[$_key]] = $_value;
					}
				}

				$_errorList = $_resultList;
			}
		}

		return $_errorList;
	}

	//**************************************************************************
	//* Properties
	//**************************************************************************

	/**
	 * @param int $outputFormat
	 *
	 * @return \CPSRESTController
	 */
	public function setOutputFormat( $outputFormat )
	{
		$this->_outputFormat = $outputFormat;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getOutputFormat()
	{
		return $this->_outputFormat;
	}

	/**
	 * @param boolean $singleParameterActions
	 *
	 * @return \CPSRESTController
	 */
	public function setSingleParameterActions( $singleParameterActions )
	{
		$this->_singleParameterActions = $singleParameterActions;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getSingleParameterActions()
	{
		return $this->_singleParameterActions;
	}
}

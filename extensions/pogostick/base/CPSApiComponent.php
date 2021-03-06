<?php
/**
 * This file is part of the psYiiExtensions package.
 *
 * @copyright         Copyright (c) 2009-2011 Pogostick, LLC.
 * @link              http://www.pogostick.com Pogostick, LLC.
 * @license           http://www.pogostick.com/licensing
 * @author            Jerry Ablan <jablan@pogostick.com>
 * @package           psYiiExtensions
 * @subpackage        base.components
 * @since             v1.0.0
 * @filesource
 *
 * @package           psYiiExtensions
 * @subpackage        base.components
 *
 * @author            Jerry Ablan <jablan@pogostick.com>
 * @version           SVN: $Id: CPSApiComponent.php 405 2010-10-21 21:44:02Z jerryablan@gmail.com $
 * @since             v1.0.0
 */
/**
 * CPSApiComponent provides a convenient base class for APIs.
 *
 * Introduces three new events:
 *
 * onBeforeApiCall
 * onAfterApiCall
 * onRequestComplete
 *
 * Each are called respectively and pass the handler a CPSApiEvent
 * object with details of the call.
 *
 * @throws CPSApiException
 * @property string  $altApiKey
 * @property string  $appendFormat
 * @property string  $apiBaseUrl
 * @property string  $apiKey
 * @property string  $apiQueryName
 * @property string  $apiToUse
 * @property string  $apiSubUrls
 * @property string  $httpMethod
 * @property integer $returnFormat
 * @property string  $payload
 * @property string  $requestData
 * @property string  $requestMap
 * @property string  $requireApiQueryName
 * @property string  $testApiKey
 * @property string  $testAltApiKey
 * @property string  $userAgent
 * @property string  $lastErrorMessage
 * @property string  $lastErrorMessageExtra
 * @property string  $lastErrorCode
 */
class CPSApiComponent extends CPSComponent
{
	//*************************************************************************
	//*	Constants
	//*************************************************************************

	/**
	 * HTTP Methods
	 */
	const
		HTTP_POST = 'POST',
		HTTP_GET = 'GET',
		HTTP_PUT = 'PUT',
		HTTP_DELETE = 'DELETE';

	//********************************************************************************
	//* Properties
	//********************************************************************************

	/**
	 * @var string an alternate/second API key
	 */
	protected $_altApiKey;
	/**
	 * @var string
	 */
	protected $_appendFormat;
	/**
	 * @var string
	 */
	protected $_apiBaseUrl;
	/**
	 * @var string
	 */
	protected $_apiKey;
	/**
	 * @var string
	 */
	protected $_apiQueryName;
	/**
	 * @var string
	 */
	protected $_apiToUse;
	/**
	 * @var string
	 */
	protected $_apiSubUrls;
	/**
	 * @var string
	 */
	protected $_httpMethod;
	/**
	 * @var integer
	 */
	protected $_returnFormat;
	/**
	 * @var string
	 */
	protected $_payload;
	/**
	 * @var string
	 */
	protected $_requestData;
	/**
	 * @var string
	 */
	protected $_requestMap;
	/**
	 * @var string
	 */
	protected $_requireApiQueryName;
	/**
	 * @var string
	 */
	protected $_testApiKey;
	/**
	 * @var string
	 */
	protected $_testAltApiKey;
	/**
	 * @var string
	 */
	protected $_userAgent = 'Pogostick Yii Extensions; (+http://www.pogostick.com/yii)';
	/**
	 * @var string
	 */
	protected $_lastErrorMessage;
	/**
	 * @var string
	 */
	protected $_lastErrorMessageExtra;
	/**
	 * @var string
	 */
	protected $_lastErrorCode;
	/**
	 * @var string The base url for this component
	 */
	protected $_baseUrl;
	/**
	 * @var boolean If true, options will be validated
	 */
	protected $_validateOptions = true;
	/**
	 * @var array $_validOptions The options considered valid
	 */
	protected $_validOptions = array();
	/**
	 * @var boolean If true, callbacks will be validated
	 */
	protected $_validateCallbacks = true;
	/**
	 * @var array $_validCallbacks The callbacks considered valid
	 */
	protected $_validCallbacks = array();
	/**
	 * @var array $_callbacks The list of client-side callbacks
	 */
	protected $_callbacks = array();
	/**
	 * @var string $_externalLibraryUrl The url of the external library (duh!)
	 */
	protected $_externalLibraryUrl = '/';

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	 * Initialize
	 */
	public function init()
	{
		//	Call daddy...
		parent::init();

		//	Attach our events...
		$this->attachEventHandler( 'onBeforeApiCall', array( $this, 'beforeApiCall' ) );
		$this->attachEventHandler( 'onAfterApiCall', array( $this, 'afterApiCall' ) );
		$this->attachEventHandler( 'onRequestComplete', array( $this, 'requestComplete' ) );
	}

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	 * Adds to the requestMap array
	 *
	 * @param string $label         The "friendly" name for consumers
	 * @param string $parameterName The name of the API variable, if null, $label
	 * @param bool   $required
	 * @param array  $options
	 * @param string $apiName
	 * @param string $subApiName
	 *
	 * @return bool True if operation succeeded
	 * @see makeMapItem
	 * @see makeMapArray
	 */
	public function addRequestMapping( $label, $parameterName = null, $required = false, $options = array(), $apiName = null, $subApiName = null )
	{
		//	Save for next call
		static $_lastApiName;
		static $_lastAction;

		//	Set up statics so next call can omit those parameters.
		if ( null !== $apiName && $apiName != $_lastApiName )
		{
			$_lastApiName = $apiName;
		}

		//	Make sure sub API name is set...
		if ( null === $_lastAction && null == $subApiName )
		{
			$subApiName = '/';
		}

		if ( null !== $subApiName && $subApiName != $_lastAction )
		{
			$_lastAction = $subApiName;
		}

		//	Build the options
		$_mapOptions = array(
			'name'     => ( null !== $parameterName ) ? $parameterName : $label,
			'required' => $required
		);

		//	Add on any supplied options
		if ( !empty( $options ) )
		{
			$_mapOptions = array_merge( $_mapOptions, $options );
		}

		//	Add the mapping...
		if ( null == $_lastApiName && null == $_lastAction )
		{
			return false;
		}

		//	Add mapping...
		if ( null === $this->_requestMap )
		{
			$this->_requestMap = array();
		}

		$this->_requestMap[$_lastApiName][$_lastAction][$label] = $_mapOptions;

		return true;
	}

	/**
	 * Makes the actual HTTP request based on settings
	 *
	 * @param string $subType
	 * @param array  $requestData
	 * @param string $requestMethod
	 *
	 * @return string
	 */
	protected function makeRequest( $subType = '/', $requestData = null, $requestMethod = 'GET' )
	{
		//	Make sure apiQueryName is set...
		if ( $this->_requireApiQueryName && null === $this->_apiQueryName )
		{
			throw new CPSApiException(
				Yii::t(
					__CLASS__,
					'Required option "apiQueryName" is not set.'
				)
			);
		}

		//	Default...
		$_queryString = null;
		$_payload = $this->_requestData;

		//	Check data...
		if ( is_array( $requestData ) && !empty( $requestData ) )
		{
			$_payload = array_merge( $_payload, $requestData );
		}

		//	Check subtype...
		if ( !empty( $subType ) && is_array( $this->_requestMap[$this->_apiToUse] ) )
		{
			if ( !array_key_exists( $subType, $this->_requestMap[$this->_apiToUse] ) )
			{
				throw new CPSApiException(
					Yii::t(
						__CLASS__,
						'Invalid API SubType specified for "{apiToUse}". Valid subtypes are "{subTypes}"',
						array(
							'{apiToUse}' => $this->_apiToUse,
							'{subTypes}' => implode( ', ', array_keys( $this->_requestMap[$this->_apiToUse] ) )
						)
					)
				);
			}
		}

		//	Build the url...
		$_url = rtrim( $this->_apiBaseUrl, '/' ) . '/';

		if ( isset( $this->apiSubUrls[$this->_apiToUse] ) && '/' != $this->apiSubUrls[$this->_apiToUse] )
		{
			$_url .= $this->apiSubUrls[$this->_apiToUse];
		}

		//	Add the API key...
		if ( $this->_requireApiQueryName )
		{
			$_queryString = $this->_apiQueryName . '=' . $this->_apiKey;
		}

		//	Add the request data to the Url...
		if ( is_array( $this->_requestMap ) && !empty( $subType ) && isset( $this->_requestMap[$this->_apiToUse] ) )
		{
			$_requestMap = $this->_requestMap[$this->_apiToUse][$subType];
			$_result = array();

			//	Add any extra requestData parameters unchecked to the query string...
			foreach ( $_payload as $_key => $_value )
			{
				if ( !array_key_exists( $_key, $_requestMap ) )
				{
					$_queryString .= '&' . $_key . '=' . urlencode( $_value );
					unset( $_payload[$_key] );
				}
			}

			//	Now build the url...
			foreach ( $_requestMap as $_key => $_mapping )
			{
				//	Tag as done...
				$_result[] = $_key;

				//	Is there a default value?
				if ( isset( $_mapping['default'] ) && !isset( $_payload[$_key] ) )
				{
					$_payload[$_key] = $_mapping['default'];
				}

				if ( isset( $_mapping['required'] ) && $_mapping['required'] && !array_key_exists( $_key, $_payload ) )
				{
					throw new CPSApiException(
						Yii::t(
							__CLASS__,
							'Required parameter {param} was not included in requestData',
							array(
								'{param}' => $_key,
							)
						)
					);
				}

				//	Add to query string
				if ( isset( $_payload[$_key] ) )
				{
					$_queryString .= '&' . $_mapping['name'] . '=' . urlencode( $_payload[$_key] );
				}
			}
		}
		//	Handle non-requestMap call...
		else if ( is_array( $_payload ) )
		{
			foreach ( $_payload as $_key => $_value )
			{
				if ( isset( $_payload[$_key] ) )
				{
					$_queryString .= '&' . $_key . '=' . urlencode( $_value );
				}
			}
		}

		if ( $this->_debugLevel > 3 )
		{
			CPSLog::trace( __METHOD__, 'Calling onBeforeApiCall' );
		}

		//	Handle events...
		$_event = new CPSApiEvent( $_url, $_queryString, null, $this );
		$this->onBeforeApiCall( $_event );

		if ( $this->_debugLevel > 3 )
		{
			CPSLog::trace( __METHOD__, 'Making request: ' . $_queryString );
		}

		//	Ok, we've build our request, now let's get the results...
		$_response = PS::makeHttpRequest( $_url, $_queryString, $requestMethod, $this->_userAgent );

		if ( $this->_debugLevel > 3 )
		{
			CPSLog::trace( __METHOD__, 'Call complete' );
		}

//		if ( $this->_debugLevel > 4 )
//			CPSLog::trace( __METHOD__, 'Response: ' . var_export( $_response, true ) );

		//	Handle events...
		$_event->setUrlResults( $_response );
		$this->onAfterApiCall( $_event );

		//	If user doesn't want JSON output, then reformat
		switch ( $this->_returnFormat )
		{
			case PS::OF_XML:
				$_response = CPSTransform::arrayToXml( json_decode( $_response, true ), 'Results' );
				break;

			case PS::OF_ASSOC_ARRAY:
				$_response = json_decode( $_response, true );
				break;

			default: //	Naked
				break;
		}

		//	Raise our completion event...
		$_event->setUrlResults( $_response );
		$this->onRequestComplete( $_event );

		//	Return results...
		return $_response;
	}

	//********************************************************************************
	//* Events and Handlers
	//********************************************************************************

	/**
	 * Raises the onBeforeApiCall event
	 *
	 * @param CPSApiEvent $event
	 */
	public function onBeforeApiCall( CPSApiEvent $event )
	{
		$this->raiseEvent( 'onBeforeApiCall', $event );
	}

	/**
	 * Base event handler
	 *
	 * @param CPSApiEvent $event
	 *
	 * @return boolean
	 */
	public function beforeApiCall( CPSApiEvent $event )
	{
		return true;
	}

	/**
	 * Raises the onAfterApiCall event. $event contains "raw" return data
	 *
	 * @param CPSApiEvent $event
	 */
	public function onAfterApiCall( CPSApiEvent $event )
	{
		$this->raiseEvent( 'onAfterApiCall', $event );
	}

	/**
	 * Base event handler
	 *
	 * @param CPSApiEvent $event
	 *
	 * @return boolean
	 */
	public function afterApiCall( CPSApiEvent $event )
	{
		return true;
	}

	/**
	 * Raises the onRequestComplete event. $event contains "processed" return data (if applicable)
	 *
	 * @param CPSApiEvent $event
	 */
	public function onRequestComplete( CPSApiEvent $event )
	{
		$this->raiseEvent( 'onRequestComplete', $event );
	}

	/**
	 * Base event handler
	 *
	 * @param CPSApiEvent $event
	 *
	 * @return boolean
	 */
	public function requestComplete( CPSApiEvent $event )
	{
		return true;
	}

	//********************************************************************************
	//* Accessors
	//********************************************************************************

	/**
	 * @return string
	 */
	public function getAltApiKey()
	{
		return $this->_altApiKey;
	}

	/**
	 * @param $altApiKey
	 *
	 * @return \CPSApiComponent
	 */
	public function setAltApiKey( $altApiKey )
	{
		$this->_altApiKey = $altApiKey;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getAppendFormat()
	{
		return $this->_appendFormat;
	}

	/**
	 * @param $appendFormat
	 *
	 * @return \CPSApiComponent
	 */
	public function setAppendFormat( $appendFormat )
	{
		$this->_appendFormat = $appendFormat;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getApiBaseUrl()
	{
		return $this->_apiBaseUrl;
	}

	/**
	 * @param $apiBaseUrl
	 *
	 * @return \CPSApiComponent
	 */
	public function setApiBaseUrl( $apiBaseUrl )
	{
		$this->_apiBaseUrl = $apiBaseUrl;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getApiKey()
	{
		return $this->_apiKey;
	}

	/**
	 * @param $apiKey
	 *
	 * @return \CPSApiComponent
	 */
	public function setApiKey( $apiKey )
	{
		$this->_apiKey = $apiKey;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getApiQueryName()
	{
		return $this->_apiQueryName;
	}

	/**
	 * @param $apiQueryName
	 *
	 * @return \CPSApiComponent
	 */
	public function setApiQueryName( $apiQueryName )
	{
		$this->_apiQueryName = $apiQueryName;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getApiToUse()
	{
		return $this->_apiToUse;
	}

	/**
	 * @param $apiToUse
	 *
	 * @return \CPSApiComponent
	 */
	public function setApiToUse( $apiToUse )
	{
		$this->_apiToUse = $apiToUse;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getApiSubUrls()
	{
		return $this->_apiSubUrls;
	}

	/**
	 * @param $apiSubUrls
	 *
	 * @return \CPSApiComponent
	 */
	public function setApiSubUrls( $apiSubUrls )
	{
		$this->_apiSubUrls = $apiSubUrls;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getHttpMethod()
	{
		return $this->_httpMethod;
	}

	/**
	 * @param $httpMethod
	 *
	 * @return \CPSApiComponent
	 */
	public function setHttpMethod( $httpMethod )
	{
		$this->_httpMethod = $httpMethod;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getReturnFormat()
	{
		return $this->_returnFormat;
	}

	/**
	 * @param $returnFormat
	 *
	 * @return \CPSApiComponent
	 */
	public function setReturnFormat( $returnFormat )
	{
		$this->_returnFormat = $returnFormat;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getRequestData()
	{
		return $this->_requestData;
	}

	/**
	 * @param $requestData
	 *
	 * @return \CPSApiComponent
	 */
	public function setRequestData( $requestData )
	{
		$this->_requestData = $requestData;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getRequestMap()
	{
		return $this->_requestMap;
	}

	/**
	 * @param $requestMap
	 *
	 * @return \CPSApiComponent
	 */
	public function setRequestMap( $requestMap )
	{
		$this->_requestMap = $requestMap;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getRequireApiQueryName()
	{
		return $this->_requireApiQueryName;
	}

	/**
	 * @param $requireApiQueryName
	 *
	 * @return \CPSApiComponent
	 */
	public function setRequireApiQueryName( $requireApiQueryName )
	{
		$this->_requireApiQueryName = $requireApiQueryName;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getTestApiKey()
	{
		return $this->_testApiKey;
	}

	/**
	 * @param $testApiKey
	 *
	 * @return \CPSApiComponent
	 */
	public function setTestApiKey( $testApiKey )
	{
		$this->_testApiKey = $testApiKey;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getTestAltApiKey()
	{
		return $this->_testAltApiKey;
	}

	/**
	 * @param $testAltApiKey
	 *
	 * @return \CPSApiComponent
	 */
	public function setTestAltApiKey( $testAltApiKey )
	{
		$this->_testAltApiKey = $testAltApiKey;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getUserAgent()
	{
		return $this->_userAgent;
	}

	/**
	 * @param $userAgent
	 *
	 * @return \CPSApiComponent
	 */
	public function setUserAgent( $userAgent )
	{
		$this->_userAgent = $userAgent;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getLastErrorMessage()
	{
		return $this->_lastErrorMessage;
	}

	/**
	 * @param $lastErrorMessage
	 *
	 * @return \CPSApiComponent
	 */
	public function setLastErrorMessage( $lastErrorMessage )
	{
		$this->_lastErrorMessage = $lastErrorMessage;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getLastErrorMessageExtra()
	{
		return $this->_lastErrorMessageExtra;
	}

	/**
	 * @param $lastErrorMessageExtra
	 *
	 * @return \CPSApiComponent
	 */
	public function setLastErrorMessageExtra( $lastErrorMessageExtra )
	{
		$this->_lastErrorMessageExtra = $lastErrorMessageExtra;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getLastErrorCode()
	{
		return $this->_lastErrorCode;
	}

	/**
	 * @param $lastErrorCode
	 *
	 * @return \CPSApiComponent
	 */
	public function setLastErrorCode( $lastErrorCode )
	{
		$this->_lastErrorCode = $lastErrorCode;

		return $this;
	}

	/**
	 * @param string $payload
	 *
	 * @return void
	 */
	public function setPayload( $payload )
	{
		$this->_payload = $payload;
	}

	/**
	 * @return string
	 */
	public function getPayload()
	{
		return $this->_payload;
	}
}
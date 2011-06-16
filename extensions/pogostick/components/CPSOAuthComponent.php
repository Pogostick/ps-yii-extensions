<?php
/**
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * The CPSOAuthComponent is the base class for all Pogostick widgets for Yii
 * 
 * @package 	psYiiExtensions
 * @subpackage 	base
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN $Id: CPSOAuthComponent.php 358 2010-01-02 23:33:40Z jerryablan@gmail.com $
 * @since 		v1.0.3
 * 
 * @filesource
 */
class CPSOAuthComponent extends CPSApiComponent
{
	//*************************************************************************
	//* Private Members 
	//*************************************************************************
	
	/**
	 * Our OAuth object
	 * @var OAuth
	 */
	protected $_oauth = null;

	/**
	 * The current token
	 * @var array
	 */
	protected $_currentToken = null;

	/**
	 * @var string
	 */
	protected $_callbackUrl;
	/**
	 * @var bool
	 */
	protected $_isAuthorized = false;
	/**
	 * @var string
	 */
	protected $_accessTokenUrl = '/oauth/access_token';
	/**
	 * @var string
	 */
	protected $_authorizeUrl = '/oauth/authorize';
	/**
	 * @var string
	 */
	protected $_requestTokenUrl = '/oauth/request_token';

	//********************************************************************************
	//* Constructor
	//********************************************************************************

	/***
	 * Constructor
	 *
	 */
	public function __construct( )
	{
		//	No oauth? No run...
		if ( !extension_loaded( 'oauth' ) )
				{
					throw new CException( Yii::t( 'psOAuthBehavior', 'The "oauth" extension is not loaded. Please install 
			and/or load the oath extension (PECL).' ) );
				}

		//	Call daddy...
		parent::__construct( );
	}

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	 * Initialize this behavior
	 *
	 */
	public function init( )
	{
		parent::init( );

		//	Events...
		$this->attachEventHandler( 'onUserAuthorized', array( $this, 'userAuthorized' ) );

		//	Create our object...
		$this->_oauth = new OAuth( 
			$this->_apiKey, 
			$this->_altApiKey, 
			OAUTH_SIG_METHOD_HMACSHA1, 
			OAUTH_AUTH_TYPE_URI 
		);

		//	Load any tokens we have...
		$this->loadToken( );

		//	Have we been authenticated?
		if ( ! $this->_isAuthorized )
		{
			if ( isset( $_REQUEST['oauth_token'] ) )
			{
				if ( $this->_oauth->setToken( $_REQUEST['oauth_token'], $_REQUEST['oauth_verifier'] ) )
				{
					$_token = $this->_oauth->getAccessToken(
						$this->_apiBaseUrl . $this->_accessTokenUrl, 
						null, 
						$_REQUEST['oauth_verifier'] 
					);
					
					$this->storeToken( $_token );
					$this->_isAuthorized = true;
				}

				//	Raise our event
				if ( $this->_isAuthorized )
					$this->onUserAuthorized( new CPSOAuthEvent( $this->_currentToken ) );
			}
		}
	}

	/**										 I
	 * Appends the current token to the authorizeUrl option
	 *
	 * @param mixed $token
	 */
	public function getAuthorizeUrl( )
	{
		$_token = $this->_oauth->getRequestToken( 
			$this->_apiBaseUrl . $this->_requestTokenUrl, 
			$this->_callbackUrl 
		);
		
		return $this->_apiBaseUrl . $this->_authorizeUrl . '?oauth_token=' . $_token['oauth_token'];
	}

	/**
	 * Stores the current token in a member variable and in the user state oAuthToken
	 *
	 * @param array $token
	 */
	public function storeToken( $token = array() )
	{
		$_name = $this->getInternalName();

		try
		{
			PS::_ss( $_name . '_oAuthToken', $token );
			PS::_ss( $_name . '_isAuthorized', $this->_isAuthorized );
			$this->_currentToken = $token;
		}
		catch ( Exception $_ex )
		{
			CPSLog::error(
				'pogostick.behaviors',
				Yii::t(
					$_name,
					'Exception storing OAuth token "{a}/{b}" : {c}',
					array(
						'{a}' => $token['oauth_token'],
						'{b}' => $token['oauth_token_secret'],
						'{c}' => $_ex->getMessage(),
					)
				)
			);
		}
	}

	/**
	 * Loads a token from the user state oAuthToken
	 *
	 */
	public function loadToken()
	{
		if ( null !== ( $_user = PS::_gu() ) )
		{
			if ( null != ( $_token = PS::_gs( 'com.pogostick.oauth.token' ) ) )
			{
				$this->_currentToken = $_token;
				$this->_isAuthorized = PS::_gs( 'com.pogostick.oauth.isAuthorized', false );
			}
		}
	}

	//********************************************************************************
	//* Events
	//********************************************************************************

	/***
	 * User has been authorized event
	 * @param CPSOAuthEvent $_event
	 */
	public function onUserAuthorized( $_event )
	{
		$this->raiseEvent( 'onUserAuthorized', $_event );
	}

	//********************************************************************************
	//* Public methods
	//********************************************************************************

	/***
	 * Fetches a protected resource using the tokens stored
	 *
	 * @param string $action
	 * @param array $requestData
	 * @param string $method
	 * @param array $headers
	 * @return string
	 */
	protected function makeRequest( $action, $requestData = array(), $method = CPSApiComponent::HTTP_GET, $headers = array() )
	{
		//	Default...
		$_payload = $this->_requestData;
		$_validParams = array();
		$_requiresAuth = false;
		$_requireOneOf = $_response = null;
		$_found = true;

		//	Check data...
		if ( null != $requestData ) 
			$_payload = array_merge( $_payload, $requestData );

		//	Add the request data to the Url...
		if ( is_array( $this->_requestMap )  )
		{
			$_map = PS::oo( $this->_requestMap, $this->_apiToUse, $action );
			$_options = PS::o( $_map, 'options' );
			$_requireOneOf = PS::o( $_options, '_requireOneOf' );
			$_method = PS::o( $_options, '_method', $method );
			$_params = PS::o( $_options, 'params' );
			$_found = ( null == $_requireOneOf );

			//	Build our query
			if ( is_array( $_params ) )
			{
				foreach ( $_params as $_key => $_required )
				{
					//	Check required items
					if ( null !== $_requireOneOf && ! $_found )
						$_found &= in_array( $_key, $_requireOneOf );

					if ( $_required && ! isset( $_payload[$_key] ) )
					{
						throw new CException(
							Yii::t(
								__CLASS__,
								'Required parameter {param} was not included in requestData',
								array(
									'{param}' => $_key,
								)
							)
						);
					}

					//	Add to query string if set...
					if ( isset( $_payload[$_key] ) )
						$_validParams[$_key] = $_payload[$_key];
				}
			}
		}

		//	Check requireOneOf option...
		if ( ! $_found )
		{
			throw new CException(
				Yii::t(
					__CLASS__,
					'This call requires one of the following parameters: {params}',
					array(
						'{param}' => implode( ', ', $_requireOneOf ),
					)
				)
			);
		}

		//	Build the url...
		$_url = $this->_apiBaseUrl . '/' . $this->_apiToUse . ( ( $action == '/' ) ? '' : '/' . $action ) . '.json';

		//	Handle events...
		$_event = new CPSApiEvent( $_url, $_validParams, null, $this );
		$this->onBeforeApiCall( $_event );

		//	Make the call...
		try
		{
			$_token = $this->_currentToken;

			if ( $this->_oauth->setToken( $_token['oauth_token'], $_token['oauth_token_secret'] ) )
			{
				if ( $this->_oauth->fetch( $_url, $_validParams, $_method, $headers ) )
				{
					//	Get results...
					$_response = $this->_oauth->getLastResponse();
				}
				else
				{
					//	Get error response
				}
			}
		}
		catch ( Exception $_ex )
		{
			$_response = null;
			CPSLog::error(
				'pogostick.base',
				Yii::t(
					$this->getInternalName(),
					'Error making OAuth fetch request in {class}: {message}',
					array(
						 '{class}' => get_class( $this ),
						 'message' => $_ex->getMessage() ) ) );
		}

		//	Handle events...
		$_event->setUrlResults( $_response );
		$this->onAfterApiCall( $_event );

		//	Raise our completion event...
		$_event->setUrlResults( $_response );
		$this->onRequestComplete( $_event );

		//	If user doesn't want JSON output, then reformat
		switch ( $this->_returnFormat )
		{
			case 'xml':
				$_response = CPSTransform::arrayToXml( json_decode( $_response, true ), 'Results' );
				break;
				
			case 'json':
				//	Already in array format
				break;

			case 'array':
				$_response = json_decode( $_response );
				break;
		}

		//	Return results...
		return $_response;
	}

	/**
	 * @param string $accessTokenUrl
	 * @return void
	 */
	public function setAccessTokenUrl( $accessTokenUrl )
	{
		$this->_accessTokenUrl = $accessTokenUrl;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getAccessTokenUrl( )
	{
		return $this->_accessTokenUrl;
	}

	/**
	 * @param string $callbackUrl
	 * @return void
	 */
	public function setCallbackUrl( $callbackUrl )
	{
		$this->_callbackUrl = $callbackUrl;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getCallbackUrl( )
	{
		return $this->_callbackUrl;
	}

	/**
	 * @param array $currentToken
	 * @return void
	 */
	public function setCurrentToken( $currentToken )
	{
		$this->_currentToken = $currentToken;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getCurrentToken( )
	{
		return $this->_currentToken;
	}

	/**
	 * @param boolean $isAuthorized
	 * @return void
	 */
	public function setIsAuthorized( $isAuthorized )
	{
		$this->_isAuthorized = $isAuthorized;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getIsAuthorized( )
	{
		return $this->_isAuthorized;
	}

	/**
	 * @param \OAuth $oauth
	 * @return void
	 */
	public function setOAuthObject( $oauth )
	{
		$this->_oauth = $oauth;
		return $this;
	}

	/**
	 * @return \OAuth
	 */
	public function getOAuthObject( )
	{
		return $this->_oauth;
	}

	/**
	 * @param string $requestTokenUrl
	 * @return void
	 */
	public function setRequestTokenUrl( $requestTokenUrl )
	{
		$this->_requestTokenUrl = $requestTokenUrl;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getRequestTokenUrl( )
	{
		return $this->_requestTokenUrl;
	}
}
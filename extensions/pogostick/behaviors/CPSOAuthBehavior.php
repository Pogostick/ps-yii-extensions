<?php
/**
 * CPSOAuthBehavior class file.
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://ps-yii-extensions.googlecode.com
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @license http://www.pogostick.com/license/
 */

require_once( dirname( __FILE__ ) . '/../components/oauth/OAuth.php' );
 
/**
 * CPSOAuthBehavior provides OAuth support to any Pogostick component
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @version SVN: $Id$
 * @package psYiiExtensions
 * @subpackage Components
 * @since 1.0.0
 * @uses pogostick.components.oauth.OAuth.php
 */
class CPSOAuthBehavior extends CPSApiBehavior
{
	//********************************************************************************
	//* Constants
	//********************************************************************************

	//	Signature types
	const OAUTH_SIGMETHOD_HMAC_SHA1 = 'HMAC-SHA1';
	
	//********************************************************************************
	//* Constructor
	//********************************************************************************

	/***
	* Constructor
	*
	*/
	public function __construct()
	{
		//	Call daddy...
		parent::__construct();

		//	Add ours...
		$this->addOptions( self::getBaseOptions() );
		
		//	Log it and check for issues...
		CPSCommonBase::writeLog( Yii::t( $this->getInternalName(), '{class} constructed', array( "{class}" => get_class( $this ) ) ), 'trace', $this->getInternalName() );
	}

	//********************************************************************************
	//* Public Methods
	//********************************************************************************
                                     
	/**
	* Add our options
	*
	*/
	private function getBaseOptions()
	{
		return(
			array(
				//	Required settings
				'consumerObject' => array( CPSOptionManager::META_DEFAULTVALUE => null, CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'object' ) ),
				'token' => array( CPSOptionManager::META_DEFAULTVALUE => '', CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'object' ) ),
				'signatureMethod' => array( CPSOptionManager::META_DEFAULTVALUE => CPSOAuthBehavior::OAUTH_SIGMETHOD_HMAC_SHA1, CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'string' ) ),
				'signatureObject' => array( CPSOptionManager::META_DEFAULTVALUE => null, CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'object' ) ),
				'callbackUrl' => array( CPSOptionManager::META_DEFAULTVALUE => '', CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'string' ) ),
				'version' => array( CPSOptionManager::META_DEFAULTVALUE => '1', CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'string' ) ),
				//	Informational
				'httpLastStatus' => array( CPSOptionManager::META_DEFAULTVALUE => '', CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'string' ) ),
				'apiLastUrl' => array( CPSOptionManager::META_DEFAULTVALUE => '', CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'string' ) ),
				'currentState' => array( CPSOptionManager::META_DEFAULTVALUE => '', CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'string' ) ),
				'isAuthorized' => array( CPSOptionManager::META_DEFAULTVALUE => false, CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'boolean' ) ),
				//	Urls
				'accessTokenUrl' => array( CPSOptionManager::META_DEFAULTVALUE => '/oauth/access_token', CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'string' ) ),
				'authorizeUrl' => array( CPSOptionManager::META_DEFAULTVALUE => '/oauth/authorize', CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'string' ) ),
				'requestTokenUrl' => array( CPSOptionManager::META_DEFAULTVALUE => '/oauth/request_token', CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'string' ) ),
			)
		);
	}

	/**
	* Initialize this behavior
	* 
	*/
	public function init()
	{
		//	Set our defaults
		switch ( $this->signatureMethod )
		{
			case self::OAUTH_SIGMETHOD_HMAC_SHA1:
			default:
				$this->signatureObject = new OAuthSignatureMethod_HMAC_SHA1();
				break;
		}
		
		//	Make our consumer object...
		$this->consumerObject = new OAuthConsumer( $this->apiKey, $this->apiSecretKey, $this->callbackUrl );
		
		//	Set state from previous session if any
		$this->isAuthorized = false;
		
		//	Have we been authenticated?
		if ( null != $_REQUEST[ 'oauth_token' ] ) 
		{
			$this->token = new OAuthConsumer( $_REQUEST[ 'oauth_token' ], ( null != $_REQUEST[ 'oauth_token_secret' ] ) ? $_REQUEST[ 'oauth_token_secret' ] : $_REQUEST[ 'oauth_request_token_secret' ] );
			$this->isAuthorized = true;
			$this->getAccessToken();
		}
		else
			$this->getRequestToken();
	}

	/**
	* Appends the current token to the authorizeUrl option
	* 	
	* @param mixed $oToken
	*/
	public function getAuthorizeUrl()
	{
		return $this->apiBaseUrl . $this->authorizeUrl . '?oauth_token=' . $this->token->key;
	}

	/**
	* Retrieve the access token
	* 
	* @param array $oToken
	* @return array
	*/
	public function getAccessToken()
	{
		$_oToken = $this->makeOAuthRequest( $this->apiBaseUrl . $this->accessTokenUrl );
		return $this->setToken( $_oToken[ 'oauth_token' ], $_oToken[ 'oauth_token_secret' ] );
	}

	/**
	* Retrieve the request token
	* 
	*/
	public function getRequestToken() 
	{ 
		$_oToken = $this->makeOAuthRequest( $this->apiBaseUrl . $this->requestTokenUrl );
		return $this->setToken( $_oToken[ 'oauth_token' ], $_oToken[ 'oauth_token_secret' ] );
	}
	
	/**
	* Given the two token parts, an OAuthConsumer object is created...
	* 
	* @param string $Token
	* @param string $sTokenSecret
	* @returns OAuthConsumer
	*/
	public function setToken( $sToken, $sTokenSecret )
	{
		return $this->token = new OAuthConsumer( $sToken, $sTokenSecret, $this->callbackUrl );
	}

	/**
	* Parse the response back from the OAuth server
	* 
	* @param string $sResponse The response from the OAuth server
	* @return array An array of parameters returned from the OAuth server
	*/
	protected function parseOAuthResponse( $sResponse )
	{
		$_arResponse = array();
		
		foreach ( explode( '&', $sResponse ) as $_sParam )
		{
			$_arPair = explode( '=', $_sParam, 2 );
			if ( count( $_arPair ) != 2 )
				continue;
				
			$_arResponse[ urldecode( $_arPair[ 0 ] ) ] = urldecode( $_arPair[ 1 ] );
		}
		
		return $_arResponse;
	}
	
	/**
	* Makes a request to an OAuth server
	* 
	* @param string $sUrl
	* @param array $arArgs
	* @param string $sMethod
	* @return array
	* @see parseOAuthResponse
	*/
	public function makeOAuthRequest( $sUrl, $arArgs = array(), $sMethod = NULL )
	{
		$_sCallbackUrl = $this->callbackUrl;
		
		if ( empty( $sMethod ) ) 
			$sMethod = empty( $arArgs ) ? 'GET' : 'POST';
		
		if ( ! in_array( 'oauth_callback', $arArgs ) && ! empty( $_sCallbackUrl ) )
			$arArgs[ 'oauth_callback' ] = $_sCallbackUrl;
		
		$_oRequest = OAuthRequest::from_consumer_and_token( $this->consumerObject, $this->token, $sMethod, $sUrl, $arArgs );
		$_oRequest->sign_request( $this->signatureObject, $this->consumerObject, $this->token );
	
		switch ( $sMethod )
		{
			case 'GET': 
				return $this->parseOAuthResponse( $this->makeHttpRequest( $_oRequest->to_url() ) );
				
			case 'POST': 
				return $this->parseOAuthResponse( $this->makeHttpRequest( $_oRequest->get_normalized_http_url(), $_oRequest->to_postdata(), 'POST' ) );
		}
	}
}
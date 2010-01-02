<?php
/*
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * CPSOAuthBehavior provides OAuth support to any Pogostick component
 * 
 * Introduces new event: onUserAuthorized raised when a user has been authorized
 * 
 * @package 	psYiiExtensions
 * @subpackage 	behaviors
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id$
 * @since 		v1.0.0
 * 
 * @filesource
 * 
 * @property-read OAuth $oAuthObject The curent OAuth object
 * @property-read string $token The current token
 */
class CPSOAuthBehavior extends CPSApiBehavior
{
	//********************************************************************************
	//* Constants
	//********************************************************************************

	/***
	* Our OAuth object
	* @var OAuth
	*/
	protected $m_oOAuth = null;
	
	/**
	* The current token
	* @var array
	*/
	protected $m_arCurToken = null;
	
	//********************************************************************************
	//* Properties
	//********************************************************************************

	/**
	* Retrieves the current token
	* @returns array
	*/
	public function getToken() { return $this->m_arCurToken; }

	/**
	* Retrieves the OAuth object
	* @returns oauth
	*/
	public function getOAuthObject() { return $this->m_oOAuth; }
	
	//********************************************************************************
	//* Constructor
	//********************************************************************************

	/***
	* Constructor
	*
	*/
	public function __construct()
	{
		//	No oauth? No run...
		if ( ! extension_loaded( 'oauth' ) )
			throw new CException( Yii::t( 'psOAuthBehavior', 'The "oauth" extension is not loaded. Please install and/or load the oath extension (PECL).' ) );
		
		//	Call daddy...
		parent::__construct();
	}
	
	//********************************************************************************
	//* Public Methods
	//********************************************************************************
                                     
	/**
	 * Pre-initialize
	 */
	public function preinit()
	{
		parent::preinit();
		
		//	Add our options
		$this->addOptions( self::getBaseOptions() );
		
		//	Events...
		$this->attachEventHandler( 'onUserAuthorized', array( $this, 'userAuthorized' ) );
	}
		
	/**
	* Initialize this behavior
	* 
	*/
	public function init()
	{
		//	Create our object...		
		$this->m_oOAuth = new OAuth( $this->apiKey, $this->apiSecretKey, OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_URI );

		//	Load any tokens we have...
		$this->loadToken();
		
		//	Have we been authenticated?
		if ( ! $this->isAuthorized )
		{
			if ( isset( $_REQUEST[ 'oauth_token' ] ) )
			{
				if ( $this->m_oOAuth->setToken( $_REQUEST[ 'oauth_token' ], $_REQUEST[ 'oauth_verifier' ] ) )
				{
					$_arToken = $this->m_oOAuth->getAccessToken( $this->apiBaseUrl . $this->accessTokenUrl, null, $_REQUEST[ 'oauth_verifier' ] );
					$this->storeToken( $_arToken );
					$this->isAuthorized = true;
				}
				
				//	Raise our event
				if ( $this->isAuthorized )
					$this->onUserAuthorized( new CPSOAuthEvent( $this->m_arCurToken ) );
			}
		}
	}
	
	/**                                         I
	* Appends the current token to the authorizeUrl option
	* 	
	* @param mixed $oToken
	*/
	public function getAuthorizeUrl()
	{
		$_arToken = $this->m_oOAuth->getRequestToken( $this->apiBaseUrl . $this->requestTokenUrl, $this->callbackUrl );
		return $this->apiBaseUrl . $this->authorizeUrl . '?oauth_token=' . $_arToken[ 'oauth_token' ];
	}
	
	/**
	* Stores the current token in a member variable and in the user state oAuthToken
	* 
	* @param array $oToken
	*/
	public function storeToken( $oToken = array() )
	{
		try
		{
			Yii::app()->user->setState( $this->getInternalName() . '_oAuthToken', $oToken );
			Yii::app()->user->setState( $this->getInternalName() . '_isAuthorized', $this->isAuthorized );
			$this->m_arCurToken = $oToken;
		}
		catch ( Exception $_ex )
		{
			$_sName = $this->getInternalName();
			CPSLog::error( 'pogostick.behaviors', Yii::t( $_sName, 'Error storing OAuth token "{a}/{b}" : {c}', array( "{a}" => $oToken['oauth_token'], "{b}" => $oToken['oauth_token_secret'], "{c}" => $_ex->getMessage() ) ) );
		}
	}

	/**
	* Loads a token from the user state oAuthToken
	* 
	*/
	public function loadToken()
	{
		$_oUser = Yii::app()->user;
		
		if ( $_oUser )
		{
			if ( null != ( $_oToken = $_oUser->getState( $this->getInternalName() . '_oAuthToken' ) ) )
			{
				$this->m_arCurToken = $_oToken;
				$this->isAuthorized = ( $_oUser->getState( $this->getInternalName() . '_isAuthorized' ) == true );
			}
			else
				$_oToken = array();
		}
	}

	//********************************************************************************
	//* Private Methods
	//********************************************************************************
	
	/**
	* Our options
	*/
	private function getBaseOptions()
	{
		return(
			array(
				//	Required settings
				'callbackUrl' => 'string',
				'isAuthorized' => 'boolean:false',
				
				//	Urls
				'accessTokenUrl' => 'string:/oauth/access_token',
				'authorizeUrl' => 'string:/oauth/authorize', 
				'requestTokenUrl' => 'string:/oauth/request_token', 
			)
		);
	}

	//********************************************************************************
	//* Events
	//********************************************************************************
	
	/***
	 * User has been authorized event
	 * @param CPSOAuthEvent $oEvent
	 */
	public function onUserAuthorized( $oEvent )
	{
		$this->raiseEvent( 'onUserAuthorized', $oEvent );
	}
	
}
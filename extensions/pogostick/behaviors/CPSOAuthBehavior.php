<?php
/**
 * CPSOAuthBehavior class file.
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://ps-yii-extensions.googlecode.com
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @license http://www.pogostick.com/license/
 */
 
/**
 * CPSOAuthBehavior provides OAuth support to any Pogostick component
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @version SVN: $Id$
 * @package psYiiExtensions
 * @subpackage Behaviors
 * @since 1.0.0
 * @uses pogostick.components.oauth.OAuth.php
 */
class CPSOAuthBehavior extends CPSApiBehavior
{
	//********************************************************************************
	//* Constants
	//********************************************************************************

	/***
	* Our OAuth object
	* 
	* @var OAuth
	*/
	protected $m_oOAuth = null;
	/**
	* The current token
	* 
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
				'callbackUrl' => array( CPSOptionManager::META_DEFAULTVALUE => '', CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'string' ) ),
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
				
				$this->raiseEvent( 'onUserAuthorized', new CPSOAuthEvent( $this->m_arCurToken ) );
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
			CPSCommonBase::writeLog( Yii::t( $_sName, 'Error storing OAuth token "{a}/{b}" : {c}', array( "{a}" => $oToken['oauth_token'], "{b}" => $oToken['oauth_token_secret'], "{c}" => $_ex->getMessage() ), 'trace', $_sName ) );		
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

}
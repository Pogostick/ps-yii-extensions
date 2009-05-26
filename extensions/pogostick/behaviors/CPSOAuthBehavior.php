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
 * @subpackage Components
 * @since 1.0.0
 */
class CPSOAuthBehavior extends CPSApiBehavior
{
	//********************************************************************************
	//* Constants
	//********************************************************************************

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
				//	API options
				'accessTokenUrl' => array( CPSOptionManager::META_DEFAULTVALUE => '', CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'string' ) ),
				'authorizeTokenUrl' => array( CPSOptionManager::META_DEFAULTVALUE => '', CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'string' ) ),
				'token' => array( CPSOptionManager::META_DEFAULTVALUE => '', CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'string' ) ),
				'tokenSecret' => array( CPSOptionManager::META_DEFAULTVALUE => '', CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'string' ) ),
				'signatureMethod' => array( CPSOptionManager::META_DEFAULTVALUE => CPSOAuthBehavior::OAUTH_SIGMETHOD_HMAC_SHA1, CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'string' ) ),
				'callbackUrl' => array( CPSOptionManager::META_DEFAULTVALUE => '', CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'string' ) ),
				'requestTokenUrl' => array( CPSOptionManager::META_DEFAULTVALUE => '', CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'string' ) ),
				'version' => array( CPSOptionManager::META_DEFAULTVALUE => '1', CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'string' ) ),
			)
		);
	}

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	public function getAccessToken() { return new CPSOAuthResponse( $this->makeOAuthRequest( CPSApiBehavior::HTTP_GET, $this->getOption( 'accessTokenUrl' ) ) ); }
	public function getAuthorizationUrl() { return $this->getOption( 'authorizeTokenUrl' ) . '?oauth_token=' . $this->getRequestToken()->oauth_token; }
	public function getRequestToken() { return new CPSOAuthResponse( $this->makeOAuthRequest( CPSApiBehavior::HTTP_POST, $this->getOption( 'requestTokenUrl' ) ) ); }

	public function makeOAuthRequest( $sMethod = null, $sUrl = null, $arParams = null )
	{
		if ( empty( $sMethod ) || empty( $sUrl ) )
			return false;

		if( empty( $arParams[ 'oauth_signature' ] ) )
			$arParams = $this->prepareParameters( $sMethod, $sUrl, $arParams );

		$_sQuery = '';
		if ( count( $arParams[ 'request' ] ) > 0 )
		{
			foreach ( $arParams[ 'request' ] as $_sKey => $_sValue )
				$_sQuery .= $_sKey . '=' . $_sValue . '&';
			$_sQuery = substr( $_sQuery, 0, -1 );
		}

		return $this->makeHttpRequest( $sUrl, $_sQuery, $sMethod, null, null, $this->getOAuthHeaders( $sUrl, $arParams[ 'oauth' ] ) );
	}

	public function setToken( $sToken = null, $sSecret = null )
	{
		$this->setOption( 'token', $sToken );
		$this->setOption( 'tokenSecret', $sSecret );
	}

	public function encode( $sString ) { return rawurlencode( utf8_encode( $sString ) ); }

	protected function getOAuthHeaders( $sUrl, $arOAuthHeaders )
	{
		$_sHeaders;
		$_arUrlParts = parse_url( $sUrl );
		$_sOAuth = 'Authorization: OAuth realm="http://' . $_SERVER[ 'HTTP_HOST' ] . '",';

		if ( is_array( $arOAuthHeaders ) )
		{
			foreach( $arOAuthHeaders as $sName => $sValue )
				$_sOAuth .= "{$sName}=\"{$sValue}\",";
		}

		return array( substr( $_sOAuth, 0, -1 ) );
	}

	protected function generateNonce()
	{
		$_sNonce = $this->getOption( 'nonce' );
		if ( isset( $_sNonce ) ) // for unit testing
			return $_sNonce;

		return md5( uniqid( rand(), true ) );
	}

	protected function generateSignature( $sMethod = null, $sUrl = null, $arParams = null )
	{
		if ( empty( $sMethod ) || empty( $sUrl ) )
			return false;

		$_sAllParams = '';

		if ( is_array( $arParams ) )
		{
			foreach ( $arParams as $_sKey => $_sValue )
				$_sAllParams .= $_sKey . '=' . $this->encode( $_sValue ) . '&';

			$_sAllParams = $this->encode( substr( $_sAllParams, 0, -1 ) );
		}

		$sUrl = $this->encode( $this->normalizeUrl( $sUrl ) );
		return $this->signString( "{$sMethod}&{$sUrl}&{$_AllParms}" );
	}

	protected function normalizeUrl( $sUrl = null )
	{
		$_arUrlParts = parse_url( $sUrl );
		$_sScheme = strtolower( $_arUrlParts[ 'scheme' ] );
		$_sHost   = strtolower( $_arUrlParts[ 'host' ] );
		$_iPort = intval( $_arUrlParts[ 'port' ] );

		$_sOut = $_sScheme . '://' . $_sHost;

		if ( $_iPort > 0 && ( $_sScheme === 'http' && $_iPort !== 80 ) || ( $_sScheme === 'https' && $_iPort !== 443 ) )
			$_sOut .= ':' . $_iPort;

		$_sOut .= $_arUrlParts[ 'path' ];

		if ( ! empty( $_arUrlParts[ 'query' ] ) )
			$_sOut .= '?' . $_arUrlParts[ 'query' ];

		return $_sOut;
	}

	protected function prepareParameters( $sMethod = null, $sUrl = null, $arParams = null )
	{
		if ( empty( $sMethod ) || empty( $sUrl ) )
			return false;

		$_arOAuth[ 'oauth_consumer_key' ] = $this->getOption( 'apiKey' );
		$_arOAuth[ 'oauth_token' ] = $this->getOption( 'token' );
		$_arOAuth[ 'oauth_nonce' ] = $this->generateNonce();
		$_sTimeStamp = $this->getOption( 'timestamp' );
		$_arOAuth[ 'oauth_timestamp' ] = ! isset( $_sTimeStamp ) ? time() : $_sTimeStamp; // for unit test
		$_arOAuth[ 'oauth_signature_method' ] = $this->getOption( 'signatureMethod' );
		$_arOAuth[ 'oauth_version' ] = $this->getOption( 'version' );

		//	Encode
		array_walk( $_arOAuth, array( $this, 'encode' ) );

		if ( is_array( $arParams ) )
			array_walk( $arParams, array( $this, 'encode' ) );

		$_arEncParams = array_merge( $_arOAuth, ( array )$arParams );

		//	Sort
		ksort( $_arEncParams );

		//	Sign
		$_arOAuth[ 'oauth_signature' ] = $this->encode( $this->generateSignature( $sMethod, $sUrl, $_arEncParms ) );

		return array( 'request' => $arParams, 'oauth' => $_arOAuth );
	}

	protected function signString( $sString = null )
	{
		$_sOut = false;

		switch ( $this->getOption( 'signatureMethod' ) )
		{
			case self::OAUTH_SIGMETHOD_HMAC_SHA1:
				$_sKey = $this->encode( $this->getOption( 'altApiKey' ) ) . '&' . $this->encode( $this->getOption( 'tokenSecret' ) );
				$_sOut = base64_encode( hash_hmac( 'sha1', $sString, $_sKey, true ) );
				break;
		}

		return $_sOut;
	}

}
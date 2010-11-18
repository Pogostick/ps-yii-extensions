<?php
//error_reporting(E_ALL);
//	$Id: SF_App.php,v 1.6 2008/04/29 18:57:17 jablan Exp $
// +---------------------------------------------------------------------------+
// | Pogostick SnowFrame™ (PHP5)                                               |
// | http://www.snowframe.com                                                  |
// +---------------------------------------------------------------------------+
// | Copyright (c) 2007-2008 Pogostick, LLC.                                   |
// | All rights reserved.                                                      |
// |                                                                           |
// | This file is part of SnowFrame™.                                          |
// |                                                                           |
// | SnowFrame™ is free software: you can redistribute it and/or modify        |
// | it under the terms of the GNU General Public License as published by      |
// | the Free Software Foundation, either version 3 of the License, or         |
// | (at your option) any later version.                                       |
// |                                                                           |
// | SnowFrame™ is distributed in the hope that it will be useful,             |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of            |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
// | GNU General Public License for more details.                              |
// |                                                                           |
// | Please visit http://www.gnu.org/licenses for a copy of the license        |
// +---------------------------------------------------------------------------+

//	Includes
require_once( "SnowFrame.php" );

/**
*@desc The main SnowFrame application class
*/
class SF_App_02
{
	/**
	*@desc Platform API object
	*/
	protected $m_oPFAPI = null;
	/**
	*@desc Sonetrix API object
	*/
	protected $m_oSntx = null;
	/**
	*@desc Database API object
	*/
	protected $m_oDB = null;
	/**
	*@desc User database object
	*/
	protected $m_oUserDB = null;
	/**
	*@desc Enable/disable Sonetrix integration
	*/
	protected $m_bEnableSonetrix = true;
	/**
	*@desc The last command sent to the app
	*/
	protected $m_sCmd = 'show';
	/***
	*@desc The IP address of the user
	*/
	protected $m_sIPAddr = null;
	/***
	*@desc The referring IP address of the user
	*/
	protected $m_sReferrer = null;
	/***
	*@desc Array of advertisers
	*/
	protected $m_arBanner = array();
	/**
	*@desc Invitation placement
	*/
	protected $m_eInvitePlacement = SF_InvitePlacement::AfterAdd;
	/**
	*@desc The current page action
	*/
	protected $m_sPageAction = 'home';
	/***
	*@desc The current page sub-action
	*/
	protected $m_sPageSubAction = '';
	/**
	*@desc The application configuration container
	*/
	protected $m_oConfig = null;

	/**
	 * Constructor
	 *
	 * @param SF_AppConfig $oConfig
	 */
	function __construct( $oConfig )
	{
		//	Get the config container loaded
		$this->initializeConfiguration( $oConfig );

		//	Construct a platform specific object
		$this->initializePlatform();

		//	Create a new Sonetrix object...
		$this->initializeSonetrix();

		//	Open database if method exists...
		if ( method_exists( $this, "dbConnect" ) )
			$this->dbConnect();

		//	Process the query string...
		$this->processQueryString();
	}

	/**
	 * Destructor
	 *
	 */
	public function __destruct()
	{
		if ( $this->m_oDB )
			$this->m_oDB->closeDatabase();
	}

	/**
	*@desc Initialize the configuration container
	*/
	protected function initializeConfiguration( $oConfig )
	{
		//	Get the config container loaded
		if ( $oConfig != null )
			$this->m_oConfig = $oConfig;
		else
			$this->m_oConfig = new SF_AppConfig();

		//	Populate container a bit...
		$this->m_oConfig->BaseUrl = $sBaseUrl;
		$this->m_oConfig->AppName = $sAppName;
		$this->m_oConfig->AppId = $sAppId;
		$this->m_oConfig->APIKey = $sAPIKey;
		$this->m_oConfig->APISecret = $sAPISecret;
		$this->m_oConfig->AppProfileId = $sProfileId;
		$this->m_oConfig->PTC = $iPTC;
	}

	/**
	*@desc Initialize the Sonetrix object
	*/
	protected function initializeSonetrix()
	{
		if ( $this->m_oConfig->EnableSonetrix )
			$this->m_oConfig->Sonetrix = new Sonetrix( $this->m_oConfig->AppId, $this->m_oConfig->PTC, null, null, ( $this->m_oConfig->PTC == SF_PlatformTypes::Facebook && $this->m_oConfig->PFAPI->PFAPI ? $this->m_oConfig->PFAPI->PFAPI : null ), $sAppProfileId = $sProfileId );
	}

	/***
	*@desc Initialize the platform api
	*/
	protected function initializePlatform()
	{
		switch ( $this->m_oConfig->PTC )
		{
			case SF_PlatformTypes::Friendster:
				break;

			case SF_PlatformTypes::MySpace:
				break;

			case SF_PlatformTypes::Facebook:
				$this->m_oConfig->PFAPI = new SF_Facebook( $sBaseUrl, $sAppName, $sAppId, $sAPIKey, $sAPISecret );
				break;

			case SF_PlatformTypes::Bebo:
				$this->m_oConfig->PFAPI = new SF_Bebo( $sBaseUrl, $sAppName, $sAppId, $sAPIKey, $sAPISecret, $sProfileId );
				break;
		}
	}

	/**
	*@desc Property setter
	*/
	public function __set( $sProp, $oValue )
	{
		switch ( $sProp )
		{
			case "SubAction":
				$this->m_sPageSubAction = $oValue;
				break;

			case "Action":
				$this->m_sPageAction = $oValue;
				break;
		}
	}

	/**
	*@desc Public getter
	*/
	public function __get( $sProp )
	{
		if ( property_exists( $oConfig, $sProp ) )
			return( eval( "$oConfig" . "->" . $sProp ) );

		switch ( $sProp )
		{
			case "Config":
				return( $this->m_oConfig );

			case "Command":
				return( $this->m_sCmd );

			case "Action":
				return( $this->m_sPageAction );

			case "SubAction":
				return( $this->m_sPageSubAction );
		}
	}

	/**
	 * Loads the page data
	 *
	 * @param unknown_type $sPageName
	 */
	public function loadPage( $sPageName = "index.php" )
	{
		//	Validate the user...
		$this->validateUser();

		//	First time? Go to invite page...
		if ( $sPageName == "index.php" )
		{
			$this->inviteRedirect();
		}
	}

	/**
	 * Before rendering
	 *
	 */
	public function preRender( $sPage = "index.php" )
	{
		//	Track the hit...
		$this->trackPageView( $sPage );

		if ( $this->m_oConfig->Sonetrix )
			echo "<!-- huid " . $this->m_oConfig->Sonetrix->getHashUID() . " -->";
	}

	/**
	 * Renders quiz to the page...
	 *
	 */
	public function render( $sPage = "index.php", $iTitle = "1" )
	{
		$this->preRender( $sPage );

		//	Spit out GA code...
		if ( null != $this->m_oConfig->PFAPI && method_exists( $this->m_oConfig->PFAPI, "render" ) )
			$this->m_oConfig->render( $sPage, $iTitle );
	}

	/**
	 * Creates a database object and opens a connection.
	 *
	 * @param unknown_type $sDBHost
	 * @param unknown_type $sDBUser
	 * @param unknown_type $sDBPass
	 * @param unknown_type $sDBName
	 */
	public function openDatabase()
	{
		$this->m_oConfig->UserDB = null;
		$this->m_oConfig->DB = new SF_Database( $sDBHost, $sDBUser, $sDBPass, $sDBName, true );

		if ( $this->m_oConfig->UserDBClassName != null )
		{
			try
			{
				require_once( $this->m_oConfig->UserDBClassName . ".php" );
				$this->m_oConfig->UserDB = new $sUserDBClassName( $this->m_oConfig->DB, $this->m_oConfig->AppId, $this->m_oConfig->PTC, $this->m_oConfig->Sonetrix, $this->m_oConfig->PFAPI );
			}
			catch ( Exception $_ex )
			{
				//	No user database available...
			}
		}
	}

	/***
	*@desc Validates the user...
	*/
	public function validateUser( $bRequireFrame = true, $bRequireAdd = true, $bRequireLogin = false, $bUsePrefs = true, $bAddUser = true )
	{
		if ( $this->PFAPI )
			$this->m_oConfig->PFAPI->validateUser( $bRequireFrame, $bRequireAdd, $bRequireLogin, $bUsePrefs, $bAddUser );

		//	Let the user database have a look
		if ( $this->UserDB && method_exists( $this->UserDB, "processQueryString" ) )
			$this->m_oConfig->UserDB->processQueryString( $this->m_oConfig->PFUserId );
	}

	/**
	 * Generic query string processing for the application.
	 *
	 */
	protected function processQueryString()
	{
		if ( isset( $_SERVER['HTTP_REFERER'] ) )
			$this->m_sReferrer = $_SERVER['HTTP_REFERER'];

		//	Default values...
		$this->m_sIPAddr = $_SERVER['REMOTE_ADDR'];

		//	What's this page's command?
		if ( isset( $_REQUEST["c"] ) )
		{
			$this->m_sCmd = $_REQUEST[ 'c' ];

			//	Deal with screwy facebook bug where they don't parse url properly
			if ( substr( $this->m_sCmd, 0, 7 ) == 'addhttp' )
			{
				$_sUrl = substr( $this->m_sCmd, 3 );
				error_log( "Screwy URL redirect [{$_sUrl} -->> {" . $this->PFAPI->AppUrl . '?c=add&auth_token=' . $_REQUEST['auth_token'] . '&installed=1' . "]", 0 );
				$this->redirect( $this->PFAPI->AppUrl . '?c=add&auth_token=' . $_REQUEST['auth_token'] . '&installed=1' );
			}
		}

		//	Handle app removal here...
		if ( $this->m_sCmd == "del" && $this->m_oConfig->UserDB != null )
		{
			//	If user is in database, remove him/her
			if ( $this->UserDB->userIsInDB( $this->PFUserId ) )
				$this->UserDB->removeApp( $this->PFUserId );

			//	Now bail...
			exit;
		}

		//	Any page actions?
		if ( isset( $_REQUEST[ 'action' ] ) )
			$this->m_sPageAction = $_REQUEST[ 'action' ];

		//	Any page sub-actions?
		if ( isset( $_REQUEST[ 'sa' ] ) )
			$this->m_sPageSubAction = $_REQUEST[ 'sa' ];

		//	Let the platform take a look at the query string...
		if ( $this->m_oConfig->PFAPI && method_exists( $this->m_oConfig->PFAPI, "processQueryString" ) )
			$this->m_oConfig->PFAPI->processQueryString();

		//	Let sonetrix have a look at the query string...
		if ( $this->m_bEnableSonetrix && $this->m_oConfig && $this->m_oConfig->PFAPI && method_exists( $this->m_oConfig, "processQueryString" ) )
			$this->m_oConfig->processQueryString( $this->m_oConfig->PFAPI->PFUserId );

		//	Return page command...
		return( $this->m_sCmd );
	}

	/**
	 * Returns the current command
	 *
	 * @return unknown
	 */
	public function getCurrentCommand()
	{
		return( $this->m_sCmd );
	}

	/**
	 * Determines if a specific command was sent
	 *
	 * @param unknown_type $sCmd
	 * @return unknown
	 */
	public function isPageCommand( $sCmd )
	{
		return( strtolower( $this->m_sCmd ) == strtolower( $sCmd ) );
	}

	/**
	 * Tracks a page hit...
	 *
	 * @param unknown_type $sPage
	 * @param unknown_type $sPage
	 */
	public function trackPageView( $sPage )
	{
		if ( $this->m_bEnableSonetrix && $this->m_oConfig )
			$this->m_oConfig->user_pageView( $this->PFUserId, $sPage );
	}

	/**
	 * Redirects to invite page if app was just installed...
	 *
	 */
	public function inviteRedirect()
	{
		if ( $this->m_eInvitePlacement == SF_InvitePlacement::AfterAdd && isset( $_REQUEST['installed'] ) && $_REQUEST['installed'] == '1' )
		{
			//	No user database? Ping sonetrix with addition
			if ( ! $this->UserDB && $this->m_oConfig )
				$this->m_oConfig->setHashUID( $this->m_oConfig->user_install( $this->m_oConfig->PFAPI->PFUserId, $this->m_oConfig->PFAPI->getSessionKey() ) );

			$this->redirect( $this->m_oConfig->PFAPI->AppUrl . "invite.php" );
		}
	}

	/***
	*@desc generic redirect
	*/
	public function redirect( $sUrl )
	{
		$this->m_oConfig->PFAPI->redirect( $sUrl );
	}

	/**
	 * Returns a comma separated list of invited people for exclusion...
	 *
	 * @return unknown
	 */
	public function getInviteeList()
	{
		if ( $this->m_bEnableSonetrix && $this->m_oConfig )
			return( $this->m_oConfig->user_getInvitees( $this->PFUserId ) );
	}

	/***
	*@desc Adds an ad vendor to the application
	*/
	public function addAdVendor( $iAVC, $sIds )
	{
		$this->m_arBanner[ $iAVC ] = new SF_BannerAd( $iAVC, explode(',',$sIds) );
	}

	/***
	* @desc Set ref urls for an app
	*/
	public function setRefHandle( $sFBML )
	{
		$this->m_oConfig->PFAPI->setRefHandle( $sFBML );
	}

	/**
	 * Emit banner stuff
	 *
	 * @param unknown_type $sKey
	 */
	public function getAdCode( $iAVC = null, $eType = SF_AdType::Banner )
	{
		$sOut = "";

		if ( isset( $this->m_arBanner[ $iAVC ] ) )
			$sOut = $this->m_arBanner[ $iAVC ]->getAdCode( $this->m_oConfig->PFAPI->getPFType(), $eType );
		else
		{
			foreach ( $this->m_arBanner as $sKey => $oValue )
				$sOut = $oValue->getAdCode( $this->m_oConfig->PFAPI->getPFType(), $eType );
		}

		return( $sOut );
	}
}
?>
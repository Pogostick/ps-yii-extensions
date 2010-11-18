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
class SF_App
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
	/***
	*@desc The application config object
	*/
	protected $m_oConfig = null;

	/**
	 * Constructor
	 *
	 * @param unknown_type $sBaseUrl
	 * @param unknown_type $sAppName
	 * @param unknown_type $sAppId
	 * @param unknown_type $sAPIKey
	 * @param unknown_type $sAPISecret
	 */
	function __construct( $iPTC, $sBaseUrl, $sAppName, $sAppId, $sAPIKey, $sAPISecret, $sProfileId = null, $oConfig = null )
	{
		//	Get the config container loaded
		if ( $oConfig != null )
		{
			$this->m_oConfig = $oConfig;
		}
		else
		{
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

		//	Construct a platform specific object
		$this->initializePlatform( $iPTC, $sBaseUrl, $sAppName, $sAppId, $sAPIKey, $sAPISecret, $sProfileId );

		//	Create a new Sonetrix object...
		if ( $this->m_bEnableSonetrix )
		{
			$this->m_oSntx = new Sonetrix( $sAppId, $iPTC, null, null, ( $iPTC == SF_PlatformTypes::Facebook && $this->m_oPFAPI->PFAPI ? $this->m_oPFAPI->PFAPI : null ), $sProfileId );

			if ( $this->m_oSntx && $this->m_oPFAPI )
				$this->m_oPFAPI->Sonetrix = $this->m_oSntx;
		}

		//	If we have the info in the config object, open the database...
		if ( null != $this->m_oConfig && $this->DBHost != '' )
		{
			$this->openDatabase( $this->DBHost, $this->DBUser, $this->DBPass, $this->DBName, $this->UserDBClassName );
		}
		else
		{
			//	Open database if method exists...
			if ( method_exists( $this, "dbConnect" ) )
				$this->dbConnect();
		}

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

	/***
	*@desc Initialize the platform api
	*/
	public function initializePlatform( $iPTC, $sBaseUrl, $sAppName, $sAppId, $sAPIKey, $sAPISecret, $sProfileId )
	{
		switch ( $iPTC )
		{
			case SF_PlatformTypes::Friendster:
				break;

			case SF_PlatformTypes::MySpace:
				break;

			case SF_PlatformTypes::Facebook:
				$this->m_oPFAPI = new SF_Facebook( $sBaseUrl, $sAppName, $sAppId, $sAPIKey, $sAPISecret );
				break;

			case SF_PlatformTypes::Bebo:
				$this->m_oPFAPI = new SF_Bebo( $sBaseUrl, $sAppName, $sAppId, $sAPIKey, $sAPISecret, $sProfileId );
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
		switch ( $sProp )
		{
			case "Config":
				return( $this->m_oConfig );

			case "PFUserId":
				return( $this->m_oPFAPI->PFUserId );

			case "PFAPI":
				return( $this->m_oPFAPI );

			case "UserDB":
				return( $this->m_oUserDB );

			case "DB":
				return( $this->m_oDB );

			case "Sonetrix":
				return( $this->m_oSntx );

			case "Command":
				return( $this->m_sCmd );

			case "Action":
				return( $this->m_sPageAction );

			case "SubAction":
				return( $this->m_sPageSubAction );
		}

		//	Try and get it from config object if we get here...
		return( $this->m_oConfig->__get( $sProp ) );
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
	 * Renders quiz to the page...
	 *
	 */
	public function preRender( $sPage = "index.php" )
	{
		//	Track the hit...
		$this->trackPageView( $sPage );

		if ( $this->m_oSntx )
			echo "<!-- huid " . $this->m_oSntx->getHashUID() . " -->";
	}

	/**
	 * Renders quiz to the page...
	 *
	 */
	public function render( $sPage = "index.php", $iTitle = "1" )
	{
		$this->preRender( $sPage );

		//	Spit out GA code...
		if ( null != $this->m_oPFAPI && method_exists( $this->m_oPFAPI, "render" ) )
			$this->m_oPFAPI->render( $sPage, $iTitle );
	}

	/**
	 * Creates a database object and opens a connection.
	 *
	 * @param unknown_type $sDBHost
	 * @param unknown_type $sDBUser
	 * @param unknown_type $sDBPass
	 * @param unknown_type $sDBName
	 */
	public function openDatabase( $sDBHost, $sDBUser, $sDBPass, $sDBName, $sUserDBClassName = null )
	{
		$this->m_oUserDB = null;
		$this->m_oDB = new SF_Database( $sDBHost, $sDBUser, $sDBPass, $sDBName, true );

		if ( $sUserDBClassName != null )
		{
			try
			{
				require_once( $sUserDBClassName . ".php" );
				$this->m_oUserDB = new $sUserDBClassName( $this->m_oDB, $this->m_oPFAPI->AppId, $this->m_oPFAPI->getPFType(), $this->m_oSntx, $this->m_oPFAPI );

				//	Set user database in platform api...
				if ( $this->m_oPFAPI && $this->m_oUserDB )
					$this->m_oPFAPI->UserDB = $this->m_oUserDB;
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
		if ( $this->m_oPFAPI )
			$this->m_oPFAPI->validateUser( $bRequireFrame, $bRequireAdd, $bRequireLogin, $bUsePrefs, $bAddUser );

		//	Let the user database have a look
		if ( $this->m_oUserDB && method_exists( $this->m_oUserDB, "processQueryString" ) )
			$this->m_oUserDB->processQueryString( $this->m_oPFAPI->PFUserId );
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
			$this->processAppCommands();

		//	Any page actions?
		if ( isset( $_REQUEST[ 'action' ] ) )
			$this->m_sPageAction = $_REQUEST[ 'action' ];

		//	Any page sub-actions?
		if ( isset( $_REQUEST[ 'sa' ] ) )
			$this->m_sPageSubAction = $_REQUEST[ 'sa' ];

		//	Let the platform take a look at the query string...
		if ( $this->m_oPFAPI && method_exists( $this->m_oPFAPI, "processQueryString" ) )
			$this->m_oPFAPI->processQueryString();

		//	Let sonetrix have a look at the query string...
		if ( $this->m_bEnableSonetrix && $this->m_oSntx && $this->m_oPFAPI && method_exists( $this->m_oSntx, "processQueryString" ) )
			$this->m_oSntx->processQueryString( $this->m_oPFAPI->PFUserId );

		//	Return page command...
		return( $this->m_sCmd );
	}

	/***
	*@desc Process special app commands ('c')
	*/
	protected function processAppCommands()
	{
		//	Store it...
		$this->m_sCmd = $_REQUEST[ 'c' ];

		//	Deal with screwy facebook bug where they don't parse url properly
		if ( substr( $this->m_sCmd, 0, 7 ) == 'addhttp' )
		{
			$_sUrl = substr( $this->m_sCmd, 3 );
			$this->redirect( $this->PFAPI->AppUrl . '?c=add&auth_token=' . $_REQUEST['auth_token'] . '&installed=1&refuid=' . ( isset( $_REQUEST['refuid'] ) ? $_REQUEST['refuid'] : '' ) );
		}

		//	Process a ping request
		if ( $this->m_sCmd == 'ping' )
		{
			echo 'pong';
			exit;
		}

		//	Handle app removal here...
		if ( $this->m_sCmd == "del" )
		{
			if ( $this->m_oUserDB != null )
			{
				//	If user is in database, remove him/her
				if ( $this->UserDB->userIsInDB( $this->PFUserId ) )
				{
					$this->UserDB->removeApp( $this->PFUserId );
					echo "RFD";
				}
				else
					echo "NID";
			}
			else
				echo "NOD";


			//	Now bail...
			exit;
		}

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
		if ( $this->m_bEnableSonetrix && $this->m_oSntx )
			$this->m_oSntx->user_pageView( $this->PFUserId, $sPage );
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
			if ( ! $this->UserDB && $this->m_oSntx )
				$this->m_oSntx->setHashUID( $this->m_oSntx->user_install( $this->m_oPFAPI->PFUserId, $this->m_oPFAPI->getSessionKey() ) );

			$this->redirect( $this->m_oPFAPI->AppUrl . "invite.php" );
		}
	}

	/***
	*@desc generic redirect
	*/
	public function redirect( $sUrl )
	{
		$this->m_oPFAPI->redirect( $sUrl );
	}

	/**
	 * Returns a comma separated list of invited people for exclusion...
	 *
	 * @return unknown
	 */
	public function getInviteeList()
	{
		if ( $this->m_bEnableSonetrix && $this->m_oSntx )
			return( $this->m_oSntx->user_getInvitees( $this->PFUserId ) );
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
		$this->m_oPFAPI->setRefHandle( $sFBML );
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
			$sOut = $this->m_arBanner[ $iAVC ]->getAdCode( $this->m_oPFAPI->getPFType(), $eType );
		else
		{
			foreach ( $this->m_arBanner as $sKey => $oValue )
				$sOut = $oValue->getAdCode( $this->m_oPFAPI->getPFType(), $eType );
		}

		return( $sOut );
	}
	
	public function publishTemplate( $sBundleId = null, &$arBundleData = array(), $arTargetIds = null )
	{
		$_sBundleId = $sBundleId;
		$_sAppUrl = $this->PFAPI->AppUrl;
		
		try
		{
			//	See if we can get the bundle id for this app...
			if ( $sBundleId == null )
			{
				if ( $arBundle = $this->PFAPI->getRegisteredBundles() )
				{
					//	Get the bundle id...
					$_sBundleId = isset( $arBundle, $arBundle[ 0 ], $arBundle[ 0 ][ 'template_bundle_id' ] ) ? $arBundle[0]['template_bundle_id'] : null;
					error_log( 'Found registered bundle id ' . $_sBundleId . ' for app [' . $this->getQuizName() . ']' );
				}
				else
				{
					//	Create a default quiz bundle...
					$arOneLine = $arShort = $arAction = array();
					$arOneLine[] = '{*actor*} just took the {*quiz_name*} quiz and the result was {*quiz_result_name*}.';
					
					$arShort[] = array( 
						'template_title' => '{*actor*} just took the {*quiz_name*} quiz and the result was {*quiz_result_name*}.',
						'template_body' => '{*quiz_result_full*}',
					);
		    
					$arAction[] = array( 'text' => 'Take the quiz!', 'href' => $this->PFAPI->AppUrl );
					if ( $this->m_sFanPageUrl ) $arAction[] = array( 'text' => 'Become a fan!', 'href' => $this->m_sFanPageUrl );
					
					$_sBundleId = $this->PFAPI->registerBundle( $arOneLine, $arShort, null, $arLinks );
					error_log( 'Registered bundle id ' . $_sBundleId . ' for app [' . $this->getQuizName() . ']' );
				}
			}
			
			//	Do we have a bundle to publish?
			if ( $_sBundleId )
			{
				//	Create array...
				if ( ! sizeof( $arBundleData ) )
				{
					$arBundleData = array(
						'quiz_name' => "&quot;<a href=\"{$_sAppUrl}\">{$this->m_sQuizName}</a>&quot;",
						'quiz_result_name' => "&quot;<a href=\"{$_sAppUrl}\">{$this->m_oResult->FullName}</a>&quot;",
						'quiz_result_full' => $this->m_oResult->Description . "<br /><br /><a href=\"{$_sAppUrl}\">Why not take the quiz yourself?</a>",
						'images' => array( array( 'src' => $this->m_oResult->ImageUrl, 'href' => $_sAppUrl ) ),
					);
				}
					
				//	Publish action to feed...
				$sResult = $this->PFAPI->publishBundle( $_sBundleId, $arBundleData, $arTargetIds, null, 2 );
			}
		}
		catch ( Exception $_ex )
		{
			error_log( 'Error publishing bundle id ' . $_sBundleId . ': ' . $_ex->getMessage() );
		}
	}
}
?>
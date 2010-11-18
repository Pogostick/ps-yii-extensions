<?php
//	$Id$
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
*@desc The main SnowFrame application configuration class
*/
class SF_AppConfig extends SF_Snob
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
	*@desc Invitation placement
	*/
	protected $m_eInvitePlacement = SF_InvitePlacement::AfterAdd;
	/**
	*@desc The base application url on the server
	*/
	protected $m_sBaseUrl = "";
	/**
	*@desc The application's about or profile page
	*/
	protected $m_sAppPage = "";
	/**
	*@desc The application's platform url
	*/
	protected $m_sAppUrl = "";
	/**
	*@desc The call back url
	*/
	protected $m_sCallbackUrl = "";
	/**
	*@desc The application name on the platform
	*/
	protected $m_sAppName = "";
	/**
	*@desc The application id on the platform
	*/
	protected $m_sAppId = "";
	/**
	*@desc The app api key
	*/
	protected $m_sAPIKey = "";
	/**
	*@desc The app secret key
	*/
	protected $m_sAPISecret = "";
	/**
	*@desc Platform user id
	*/
	protected $m_sPFUserId = null;
	/**
	*@desc Referring platform user id
	*/
	protected $m_sRefPFUserId = null;
	/**
	*@desc App profile id (if it has one)
	*/
	protected $m_sProfileId = null;
	/**
	*@desc Google Analytics key
	*/
	protected $m_sGAKey = null;
	/**
	*@desc The physical server url
	*/
	protected $m_sServerUrl = null;
	/**
	*@desc The platform type code
	*/
	protected $m_iPTC = 1000;
	/**
	*@desc Database host name
	*/
	protected $m_sDBHost = '';
	/**
	*@desc Database user name
	*/
	protected $m_sDBUser = '';
	/**
	*@desc Database password
	*/
	protected $m_sDBPass = '';
	/**
	*@desc Database name
	*/
	protected $m_sDBName = '';
	/**
	*@desc Database user database class name
	*/
	protected $m_sUserDBClassName = '';
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
	*@desc The current page action
	*/
	protected $m_sPageAction = 'home';
	/***
	*@desc The current page sub-action
	*/
	protected $m_sPageSubAction = '';
	/***
	*@desc The plug-in array
	*/
	protected $m_arPlugIn = null;

	/**
	*@desc Property setter
	*/
	public function __set( $sProp, $oValue )
	{
		switch ( $sProp )
		{
			default:	//	If not in the list, pass it on...
				parent::__set( $sProp, $oValue );
				break;

			case "PTC":
				$this->m_iPTC = $oValue;
				break;

			case "PFAPI":
				$this->m_oPFAPI = $oValue;
				break;

			case "Sonetrix":
				$this->m_oSntx = $oValue;
				break;

			case "DB":
				$this->m_oDB = $oValue;
				break;

			case "UserDB":
				$this->m_oUserDB = $oValue;
				break;

			case "EnableSonetrix":
				$this->m_bEnableSonetrix = $oValue;
				break;

			case "InvitePlacement":
				$this->m_eInvitePlacement = $oValue;
				break;

			case "BaseUrl":
				$this->m_sBaseUrl = $oValue;
				break;

			case "AppPage":
				$this->m_sAppPage = $oValue;
				break;

			case "AppUrl":
				$this->m_sAppUrl = $oValue;
				break;

			case "CallbackUrl":
				$this->m_sCallbackUrl = $oValue;
				break;

			case "AppName":
				$this->m_sAppName = $oValue;
				break;

			case "AppId":
				$this->m_sAppId = $oValue;
				break;

			case "APIKey":
				$this->m_sAPIKey = $oValue;
				break;

			case "APISecret":
				$this->m_sAPISecret = $oValue;
				break;

			case "PFUserId":
				$this->m_sPFUserId = $oValue;
				break;

			case "RefPFUserId":
				$this->m_sRefPFUserId = $oValue;
				break;

			case "AppProfileId":
				$this->m_sProfileId = $oValue;
				break;

			case "GAKey":
				$this->m_sGAKey = $oValue;
				break;

			case "ServerUrl":
				$this->m_sServerUrl = $oValue;
				break;

			case "DBHost":
				$this->m_sDBHost = $oValue;
				break;

			case "DBUser":
				$this->m_sDBUser = $oValue;
				break;

			case "DBPass":
				$this->m_sDBPass = $oValue;
				break;

			case "DBName":
				$this->m_sDBName = $oValue;
				break;

			case "UserDBClassName":
				$this->m_sUserDBClassName = $oValue;
				break;

			case "Command":
				$this->m_sCmd = $oValue;
				break;

			case "ClientIPAddress":
				$this->m_sIPAddr = $oValue;
				break;

			case "HttpReferrer":
				$this->m_sReferrer = $oValue;
				break;

			case "Banners":
				$this->m_arBanner = $oValue;
				break;

			case "PageAction":
			case "Action":
				$this->m_sPageAction = $oValue;
				break;

			case "PageSubAction":
			case "SubAction":
				$this->m_sPageSubAction = $oValue;
				break;

			case "PlugIns":
				$this->m_arPlugIn = $oValue;
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
			default:	//	If not in the list, pass it on...
				return( parent::__get( $sProp ) );

			case "PTC":
				return( $this->m_iPTC );

			case "PFAPI":
				return( $this->m_oPFAPI );

			case "Sonetrix":
				return( $this->m_oSntx );

			case "DB":
				return( $this->m_oDB );

			case "UserDB":
				return( $this->m_oUserDB );

			case "EnableSonetrix":
				return( $this->m_bEnableSonetrix );

			case "InvitePlacement":
				return( $this->m_eInvitePlacement );

			case "BaseUrl":
				return( $this->m_sBaseUrl );

			case "AppPage":
				return( $this->m_sAppPage );

			case "AppUrl":
				return( $this->m_sAppUrl );

			case "CallbackUrl":
				return( $this->m_sCallbackUrl );

			case "AppName":
				return( $this->m_sAppName );

			case "AppId":
				return( $this->m_sAppId );

			case "APIKey":
				return( $this->m_sAPIKey );

			case "APISecret":
				return( $this->m_sAPISecret );

			case "PFUserId":
				return( $this->m_sPFUserId );

			case "RefPFUserId":
				return( $this->m_sRefPFUserId );

			case "AppProfileId":
				return( $this->m_sProfileId );

			case "GAKey":
				return( $this->m_sGAKey );

			case "ServerUrl":
				return( $this->m_sServerUrl );

			case "DBHost":
				return( $this->m_sDBHost );

			case "DBUser":
				return( $this->m_sDBUser );

			case "DBPass":
				return( $this->m_sDBPass );

			case "DBName":
				return( $this->m_sDBName );

			case "UserDBClassName":
				return( $this->m_sUserDBClassName );

			case "Command":
				return( $this->m_sCmd );

			case "ClientIPAddress":
				return( $this->m_sIPAddr );

			case "HttpReferrer":
				return( $this->m_sReferrer );

			case "Banners":
				return( $this->m_arBanner );

			case "InvitePlacement":
				return( $this->m_eInvitePlacement );

			case "PageAction":
			case "Action":
				return( $this->m_sPageAction );

			case "PageSubAction":
			case "SubAction":
				return( $this->m_sPageSubAction );

			case "PlugIns":
				return( $this->m_arPlugIn );
		}
	}
}
?>
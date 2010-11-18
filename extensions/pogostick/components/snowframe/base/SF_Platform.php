<?php
//	$Id: SF_Platform.php,v 1.7 2008/05/07 19:02:57 jablan Exp $
// +---------------------------------------------------------------------------+
// | Pogostick SnowFrame? (PHP5)                                               |
// | http://www.snowframe.com                                                  |
// +---------------------------------------------------------------------------+
// | Copyright (c) 2007-2008 Pogostick, LLC.                                   |
// | All rights reserved.                                                      |
// |                                                                           |
// | This file is part of SnowFrame?.                                          |
// |                                                                           |
// | SnowFrame? is free software: you can redistribute it and/or modify        |
// | it under the terms of the GNU General Public License as published by      |
// | the Free Software Foundation, either version 3 of the License, or         |
// | (at your option) any later version.                                       |
// |                                                                           |
// | SnowFrame? is distributed in the hope that it will be useful,             |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of            |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
// | GNU General Public License for more details.                              |
// |                                                                           |
// | Please visit http://www.gnu.org/licenses for a copy of the license        |
// +---------------------------------------------------------------------------+

/**
*@desc Base of all platform APIs
*/
abstract class SF_Platform
{
	protected $m_sBaseUrl = "";
	protected $m_sAppPage = "";
	protected $m_sAppUrl = "";
	protected $m_sCallbackUrl = "";
	protected $m_sAppName = "";
	protected $m_sAppId = "";
	protected $m_sAPIKey = "";
	protected $m_sAPISecret = "";
	protected $m_oPFAPI = null;
	protected $m_sPFUserId = null;
	protected $m_sRefPFUserId = null;
	protected $m_sProfileId = null;
	protected $m_sGAKey = null;
	protected $m_sServerUrl = null;
	protected $m_oUserDB = null;
	protected $m_oSntx = null;
	protected $m_sNSPrefix = "";

	/**
	*@desc Constructor
	*/
	function __construct( $sBaseUrl, $sAppName, $sAppId, $sAPIKey, $sAPISecret = "", $sProfileId = "" )
	{
		$this->m_sBaseUrl = $sBaseUrl;
		$this->m_sAppName = $sAppName;
		$this->m_sAppId = $sAppId;
		$this->m_sAPIKey = $sAPIKey;
		$this->m_sAPISecret = $sAPISecret;
		$this->m_sProfileId = $sProfileId;
		$this->m_sServerUrl = $sBaseUrl . $sAppName . "/";
	}

	/**
	 * Setting properties
	 *
	 * @param string $sProp
	 * @return object value
	 */
	public function __set( $sProp, $oValue )
	{
		switch ( $sProp )
		{
			case "RefPFUserId":
				$this->m_sRefPFUserId = $oValue;
				break;

			case "UserDB":
				$this->m_oUserDB = $oValue;
				break;

			case "GAKey":
				$this->m_sGAKey = $oValue;
				break;

			case "Sonetrix":
				$this->m_oSntx = $oValue;
				break;

			case "NSPrefix":
				$this->m_sNSPrefix = $oValue;
				break;
		}
	}

	/**
	 * Getting properties
	 *
	 * @param string $sProp
	 * @return object value
	 */
	public function __get( $sProp )
	{
		switch ( $sProp )
		{
			case "RefPFUserId":
				return( $this->m_sRefPFUserId );

			case "UserDB":
				return( $this->m_oUserDB );

			case "BaseUrl":
				return( $this->m_sBaseUrl );

			case "AppName":
				return( $this->m_sAppName );

			case "AppId":
				return( $this->m_sAppId );

			case "APIKey":
				return( $this->m_sAPIKey );

			case "APISecretKey":
				return( $this->m_sAPISecret );

			case "CallbackUrl":
				return( $this->m_sCallbackUrl );

			case "AppPage":
				return( $this->m_sAppPage );

			case "AppUrl":
				return( $this->m_sAppUrl );

			case "PFAPI":
				return( $this->m_oPFAPI );

			case "PFUserId":
				return( $this->m_sPFUserId );

			case "ProfileId":
				return( $this->m_sProfileId );

			case "ServerUrl":
				return( $this->m_sServerUrl );

			case "GAKey":
				return( $this->m_sGAKey );

			case "Sonetrix":
				return( $this->m_oSntx );

			case "NSPrefix":
				return( $this->m_sNSPrefix );
		}
	}

	/***
	*@desc Have a looksee at the query string...
	*/
	public function processQueryString()
	{
		$this->m_sRefPFUserId = null;

		if ( isset( $_REQUEST['refuid'] ) )
			$this->m_sRefPFUserId = $_REQUEST[ 'refuid' ];
	}

	/***
	*@desc Generic redirect
	*/
	public function redirect( $sUrl = null )
	{
		try
		{
			if ( null != $this->m_oPFAPI && null != $sUrl )
				$this->m_oPFAPI->redirect( $sUrl );
		}
		catch ( Exception $_ex )
		{
			//	Totally ignore error if not supported...
		}
	}

	/**
	 * Gets generic dashboard FBML
	 *
	 * @param unknown_type $arDashPoints
	 * @return unknown
	 */
	protected function getDashboardFBML( $sPrefix, $arDashPoints )
	{
		$sOut = "<$sPrefix:dashboard>";

		foreach ( $arDashPoints as $oPName => $oPoint )
		{
			$sOut .= "<$sPrefix:" . $oPoint[ 'type' ] . " href=\"" . $oPoint[ 'url' ] . "\">" . $oPoint[ 'name' ] . "</$sPrefix:" . $oPoint[ 'type' ] . ">";
		}

		$sOut .= "</$sPrefix:dashboard>";

		return( $sOut );
	}

	/**
	* @desc Sets the google analytics key if one...
	*/
	public function setGoogleAnalyticsId( $sId )
	{
		$this->m_sGAKey = $sId;
	}

	/***
	*@desc Renders any information to the page
	*/
	public function render( $sPage = "", $iTitle = 1 )
	{
		$this->renderGoogleAnalytics( $sPage, $iTitle );
	}

	/**
	 * Returns the callback url of the app
	 *
	 * @return unknown
	 */
	public function getCallbackUrl()
	{
		return( $this->m_sServerUrl );
	}

	/**
	 * Gets the application url
	 *
	 * @return unknown
	 */
	public function getAppUrl()
	{
		return( $this->m_sAppUrl );
	}

	/**
	 * Require add
	 *
	 */
	public function requireAdd()
	{
		if ( null != $this->m_oPFAPI )
			$this->requireAdd();
	}

	/**
	 * Require login
	 *
	 */
	public function requireLogin()
	{
		if ( null != $this->m_oPFAPI )
			$this->requireLogin();
	}

	/**
	 * Require frame
	 *
	 */
	public function requireFrame()
	{
		if ( null != $this->m_oPFAPI )
			$this->m_oPFAPI->requireFrame();
	}

	/**
	 * Validate user
	 *
	 */
	public function validateUser( $bRequireFrame = true, $bRequireAdd = true, $bRequireLogin = false, $bUsePrefs = true, $bAddUser = true )
	{
		$sPref = null;

		if ( $bRequireFrame )
			$this->requireFrame();

		if ( $bRequireAdd )
			$this->requireAdd();

		if ( $bRequireLogin )
			$this->requireLogin();

		if ( $bUsePrefs )
		{
			//	Try and get prior hash uid...
			$sPref = $this->getUserData( SF_Preferences::HashUID );

			//	Doesn't exist?
			if ( $sPref == null || $sPref == "" )
			{
				try
				{
					//	Get data from server...
					$arDemoData = $this->getUserInfo();

					//	Set the demo data...
					$this->m_oSntx->setDemoData( SF_Helpers::array_to_string( $arDemoData ) );

					//	Store with client
					$this->setUserData( SF_Preferences::HashUID, $this->m_oSntx->getHashUID() );
				}
				catch ( Exception $_ex )
				{
					//	Unknown
					$this->m_oSntx->setHashUID( 0 );
				}
			}
			else
				$this->m_oSntx->setHashUID( intval($sPref) );

			$this->setUserData( SF_Preferences::HashUID, $this->m_oSntx->getHashUID() );
		}

		//	Save session key...
		$sPFSessionKey = $this->getSessionKey();

		//	Add the user...
		if ( $this->m_oUserDB )
		{
			if ( $bAddUser || ( isset( $_REQUEST['installed'] ) && $_REQUEST['installed'] == '1' ) )
			{
				$_sFirst = '';
				$_sLast = '';

				try
				{
					$_rs = $this->execPFSQL( "select first_name, last_name from user where uid = " . $this->m_sPFUserId );
					if ( $_rs )
					{
						$_sFirst = $_rs[ 0 ][ 'first_name' ];
						$_sLast = $_rs[ 0 ][ 'last_name' ];
					}
				}
				catch ( Exception $_ex )
				{
				}

				//	Is user in not the database? Add him...
				if ( ! $this->m_oUserDB->userIsInDB( $this->m_sPFUserId ) )
					$this->m_oUserDB->addNewUser( $this->m_sPFUserId, $sPFSessionKey, $this->m_sRefPFUserId, true, $_sFirst, $_sLast );
				elseif ( isset( $_REQUEST['installed'] ) && $_REQUEST['installed'] == '1' )
					$this->m_oUserDB->addOldUser( $this->m_sPFUserId, $sPFSessionKey, $_sFirst, $_sLast );
				else
				{
					if ( method_exists( $this->m_oUserDB, "touchUser" ) )
						$this->m_oUserDB->touchUser( $this->m_sPFUserId, $sPFSessionKey, $_sFirst, $_sLast );
				}
			}
			else
			{
				try
				{
					if ( method_exists( $this->m_oUserDB, "touchUser" ) )
					{
						$this->m_oUserDB->touchUser( $this->m_sPFUserId, $sPFSessionKey );
					}
				}
				catch ( Exception $_ex )
				{
					error_log( "Error touching user: " . $_ex->getMessage(), 0 );
				}
			}

			//	Store the referrer...
			if ( $this->m_sRefPFUserId != null )
				$this->UserDB->storeReferrer( $this->m_sPFUserId, $this->m_sRefPFUserId );
		}
	}

	/**
	*@desc Renders google analytics code...
	*/
	abstract public function renderGoogleAnalytics( $sPage = "", $iTitle = 1 );

	/**
	*@desc Returns the platform type code
	*/
	abstract public function getPFType();

	/**
	*@desc Returns the platform display name
	*/
	abstract public function getPFName();

	/***
	*@desc
	*/
	abstract public function setProfileML( $sML, $sPFUserId = null, $sProfileML = '', $sProfileActionML = '', $sMobileProfileML = '' );

	/***
	*@desc Executes platform specific SQL (i.e. FQL, SNQL, etc.)
	*/
	abstract public function execPFSQL( $sSQL );

	/***
	*@desc get platform specific user data
	*/
	abstract public function getUserData( $ePref );

	/***
	*@desc Sets a reference handle
	*/
	abstract public function setRefHandle( $sFBML, $sHandle = "" );

	/**
	*@desc Retrieve data from server about current user...
	*/
	abstract public function getUserInfo( $sFields = "", $sUIDs = null );

	/**
	*@desc Gets current session key
	*/
	abstract public function getSessionKey();

	/**
	*@desc Publish to feed
	*/
	abstract public function publishUserAction( $title, $body = '', $image_1 = null, $image_1_link = null );

	/**
	*@desc Publish to feed (templatized)
	*/
	abstract public function publishTemplatizedAction( $title_template, $title_data,
		$body_template, $body_data, $body_general,
		$image_1=null, $image_1_link=null,
		$image_2=null, $image_2_link=null,
		$image_3=null, $image_3_link=null,
		$image_4=null, $image_4_link=null,
		$target_ids='', $page_actor_id=null);

	/**
	*@desc Get the add application url
	*/
	abstract public function getAddAppUrl( $sNextParam = null );

	/***
	*@desc Display platform error message
	*/
	public function getErrorMessage( $sMessage )
	{
		return( "<{$this->m_sNSPrefix}:error message=\"$sMessage\" />" );
	}

	/**
	 * Renders a standard invite page...
	 *
	 * @param unknown_type $sInviteText
	 * @param unknown_type $sInviteLabel
	 * @param unknown_type $sActionType
	 * @param unknown_type $sActionText
	 */
	public function loadInvitePage( $sInviteText, $sInviteLabel, &$sInviteFBML, &$arFriends, $sExtraParms = null )
	{
		$iCount = 0;

		$sIDList = $this->m_oSntx->user_getInvitees();
		$sAction = "";

		$rs = $this->execPFSQL( "SELECT uid FROM user WHERE has_added_app = 1 and uid IN (SELECT uid2 FROM friend WHERE uid1 = " . $this->PFUserId . " )" );

		$arFriends = "";

		if ( $rs && is_array( $rs ) )
		{
			for ( $i = 0; $i < count($rs); $i++ )
			{
				if ( $arFriends != "" )
					$arFriends .= ",";

				$arFriends .= $rs[ $i ]['uid'];

				$iCount++;
			}
		}

		//	Append already invited folks...
		if ( $sIDList != "" )
			$arFriends .= "," . trim($sIDList);

		$sNextUrl = "&refuid=" . $this->PFUserId;
		if ( $sExtraParms != null )
			$sNextUrl .= $sExtraParms;

		$sAddAppUrl = $this->getAddAppUrl( $sNextUrl );

		$sInviteFBML = $this->getInviteContent( $sInviteText, $sInviteLabel, $sAddAppUrl );

		return( $sNextUrl );
	}

	public function getInviteContent( $sInviteText, $sInviteLabel, $sAddAppUrl )
	{
		$sInviteFBML = <<<FBML
<{$this->m_sNSPrefix}:name uid="{$this->PFUserId}" firstnameonly="true" shownetwork="false"/>$sInviteText
<{$this->m_sNSPrefix}:req-choice url="{$sAddAppUrl}" label="$sInviteLabel" />
FBML;

		return( $sInviteFBML );
	}

	/***
	*@desc Creates an xx:EDITOR custom select box. RS must be in 0 = ID, 1 = Label format
	*/
	public static function getMLEditorSelect( $sLabel, $sName, $oRS, $iDefault, $sNSPrefix = "fb" )
	{
		$sOut = "";

		$sOut .= "<{$sNSPrefix}:editor-custom label=\"{$sLabel}\">";
		$sOut .= "<select name=\"{$sName}\">";

		while ( $row = mysql_fetch_array( $oRS ) )
		{
			$sOut .= "<option value=\"{$row[0]}\"";
			if ( $row[ 0 ] == $iDefault )
				$sOut .= " selected ";
			$sOut .= ">" . $row[1] . "</option>";
		}

		$sOut .= "</select>";
		$sOut .= "</{$sNSPrefix}:editor-custom>";

		return( $sOut );
	}

	/***
	*@desc Creates an xx:EDITOR custom select box. RS must be in 0 = ID, 1 = Label format
	*/
	public static function getCodeSelectList( $oDB, $sLabel, $sName, $sCodeType, $iDefault, $sNSPrefix = "fb" )
	{
		$rs = $oDB->getRecordset( "select code_uid, code_desc_text from pspan_code_t where code_type_text = '" . strtoupper( $sCodeType ) . "' order by 2", false );
		return( SF_Platform::getMLEditorSelect( $sLabel, $sName, $rs, $iDefault, $sNSPrefix ) );
	}
}
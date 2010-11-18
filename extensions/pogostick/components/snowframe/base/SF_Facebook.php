<?php
//	$Id: SF_Facebook.php,v 1.4 2008/05/07 19:03:05 jablan Exp $
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

//	Includes
require_once( "SF_Platform.php" );
require_once( "facebook.php" );

/**
*@desc Encapsulates the Facebook API
*/
class SF_Facebook extends SF_Platform
{
	/**
	*@desc Constructor
	*/
	function __construct( $sBaseUrl, $sAppName, $sAppId, $sAPIKey, $sAPISecret )
	{
		//	Call the base class
		parent::__construct( $sBaseUrl, $sAppName, $sAppId, $sAPIKey, $sAPISecret );

		$this->m_sNSPrefix = "fb";

		$this->m_oPFAPI = new Facebook( $sAPIKey, $sAPISecret );
		$this->m_sPFUserId = $this->m_oPFAPI->user;

		//	Set id into Sonetrix
		if ( $this->m_oSntx )
			$this->m_oSntx->setPFUserId( $this->m_sPFUserId );

		//	Instantiate FB specifics
		$this->m_sAppPage = "http://www.facebook.com/apps/application.php?id=" . $this->AppId;
		$this->m_sAppUrl = "http://apps.facebook.com/" . $this->AppName . "/";
		$this->m_sAppCallbackUrl = $this->AppUrl;
	}

	/**
	*@desc Renders google analytics for Facebook
	*/
	public function renderGoogleAnalytics( $sPage = "", $iTitle = 0 )
	{
		if ( null != $this->GAKey )
			echo "<fb:google-analytics uacct=\"{$this->GAKey}\" />";
	}

	/***
	*@desc Returns the platform type code
	*/
	public function getPFType()
	{
		return( SF_PlatformTypes::Facebook );
	}

	/***
	*@desc Returns the platform display name
	*/
	public function getPFName()
	{
		return( "Facebook" );
	}

	/***
	*@desc require user to add application
	*/
	public function requireAdd()
	{
		$this->m_oPFAPI->require_login();
		$this->m_sPFUserId = $this->m_oPFAPI->user;

		//	Set id into Sonetrix
		if ( $this->m_oSntx )
			$this->m_oSntx->setPFUserId( $this->m_sPFUserId );
	}

	/**
	 * Require login
	 *
	 */
	public function requireLogin()
	{
		$this->m_sPFUserId = $this->m_oPFAPI->require_login();
		$this->m_sPFUserId = $this->m_oPFAPI->user;

		//	Set id into Sonetrix
		if ( $this->m_oSntx )
			$this->m_oSntx->setPFUserId( $this->m_sPFUserId );
	}

	/**
	 * Require frame
	 *
	 */
	public function requireFrame()
	{
		$this->m_oPFAPI->require_frame();
	}

	/**
	*@desc Sets the profile markup language
	*/
	public function setProfileML( $sML, $sPFUserId = null, $sProfileML = '', $sProfileActionML = '', $sMobileProfileML = '' )
	{
		return( $this->m_oPFAPI->api_client->profile_setFBML( $sML, $sPFUserId, $sProfileML, $sProfileActionML, $sMobileProfileML, ( $sProfileML == '' ? $sML : $sProfileML ) ) );
	}

	/**
	 * Gets generic dashboard FBML
	 *
	 * @param unknown_type $arDashPoints
	 * @return unknown
	 */
	protected function getDashboardFBML( $arDashPoints )
	{
		return( parent::getDashboardFBML( "fb", $arDashPoints ) );
	}

	/**
	 * Executes an FQL query
	 *
	 * @param unknown_type $sFQL
	 * @return unknown
	 */
	public function execPFSQL( $sSQL )
	{
		return( $this->m_oPFAPI->api_client->fql_query( $sSQL ) );
	}

	/***
	*@desc Get FB user preference from data store...
	*/
	public function getUserData( $eType )
	{
		return( null );//$this->m_oPFAPI->api_client->data_getUserPreference( $eType ) );
	}

	/***
	*@desc Set FB user preference in data store
	*/
	public function setUserData( $eType, $oValue )
	{
		try
		{
			$this->m_oPFAPI->api_client->data_setUserPreference( $eType, $oValue );
		}
		catch ( Exception $_ex )
		{
			error_log( __METHOD__ . ' : ' . $_ex->getMessage(), 0 );
		}
	}

	/**
	*@desc Sets a refhandle...
	*/
	public function setRefHandle( $sFBML, $sHandle = "" )
	{
		try
		{
			$this->m_oPFAPI->api_client->fbml_setRefHandle( ( $sHandle == "" ? "gbl_" . $this->m_sAppId : $sHandle ), $sFBML );
		}
		catch ( Exception $_ex )
		{
		}
	}

	/**
	*@desc Get user data from FB
	*/
	public function getUserInfo( $sFields = "", $sUIDs = null )
	{
		return( $this->m_oPFAPI->api_client->users_getInfo( ( $sUIDs == null ? $this->m_sPFUserId : $sUIDs ), ( $sFields == "" ? "birthday,current_location,sex" : $sFields ) ) );
	}

	/**
	*@desc Get session key
	*/
	public function getSessionKey()
	{
		return( $this->m_oPFAPI->fb_params['session_key'] );
	}

	/***
	*@desc Publish feed
	*/
	public function publishUserAction( $title, $body = '', $image_1 = null, $image_1_link = null )
	{
		$this->m_oPFAPI->api_client->feed_publishActionOfUser( $title, $body, $image_1, $image_1_link );
	}

	/***
	*@desc Publish feed
	*/
	public function publishBundle( $template_bundle_id, $template_data, $target_ids='', $body_general='', $story_size = 1, $user_message = '' )
	{
		return $this->m_oPFAPI->api_client->feed_publishUserAction( $template_bundle_id, $template_data, $target_ids, $body_general, $story_size, $user_message );
	}
	
	public function getRegisteredBundles()
	{
		return $this->m_oPFAPI->api_client->feed_getRegisteredTemplateBundles();
	}
	
	/**
	* Registers a template bundle and returns its ID
	* 
	* @param mixed $arOneLine
	* @param mixed $arShort
	* @param mixed $arFull
	* @param mixed $arLinks
	*/
	public function registerBundle( $arOneLine, $arShort = array(), $arFull = null, $arLinks = array() )
	{
		return $this->m_oPFAPI->api_client->feed_registerTemplateBundle( 
			$arOneLine,
			$arShort,
			$arFull,
			$arLinks
		);
	}	
	
	public function deactivateBundle( $sBundleId )
	{
		return $this->m_oPFAPI->api_client->feed_deactivateTemplateBundleByID( $sBundleId );
	}

	/***
	*@desc Publish templatized action
	*/
	public function publishTemplatizedAction( $title_template, $title_data,
		$body_template, $body_data, $body_general,
		$image_1=null, $image_1_link=null,
		$image_2=null, $image_2_link=null,
		$image_3=null, $image_3_link=null,
		$image_4=null, $image_4_link=null,
		$target_ids='', $page_actor_id=null)
	{
		return( $this->m_oPFAPI->api_client->feed_publishTemplatizedAction( $title_template, $title_data,
			$body_template, $body_data, $body_general,
			$image_1, $image_1_link,
			$image_2, $image_2_link,
			$image_3, $image_3_link,
			$image_4, $image_4_link,
			$target_ids, $page_actor_id ) );
	}

	public function getAddAppUrl( $sNextParam = null )
	{
		return( $this->PFAPI->get_add_url( $sNextParam ) );
	}
}
?>
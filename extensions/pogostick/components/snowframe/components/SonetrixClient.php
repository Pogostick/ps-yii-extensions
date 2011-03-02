<?php
/**
 * Sonetrix.php
 *
 * Copyright (c) 2010 Jerry Ablan <jablan@pogostick.com>.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 *
 * This file is part of Sonetrix by Pogostick, LLC.
 *
 * We share the same open source ideals as does the jQuery team, and
 * we love them so much we like to quote their license statement:
 *
 * You may use our open source libraries under the terms of either the MIT
 * License or the Gnu General Public License (GPL) Version 2.
 *
 * The MIT License is recommended for most projects. It is simple and easy to
 * understand, and it places almost no restrictions on what you can do with
 * our code.
 *
 * If the GPL suits your project better, you are also free to use our code
 * under that license.
 *
 * You don’t have to do anything special to choose one license or the other,
 * and you don’t have to notify anyone which license you are using.
 */

//	Include Files
//	Constants
//	Global Settings

/**
 * Sonetrix PHP Client for Yii
 *
 * @package 	snowframe
 * @subpackage	components
 *
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN $Id$
 * @since 		v1.0.0
 *
 * @filesource
 */
class SonetrixPreferences
{
	const HashUID = 100;
}

class SonetrixClient extends CPSApiComponent
{
	//********************************************************************************
	//* Constants
	//********************************************************************************

	/**
	 * Statistic Types
	 */
	const	STAT_ADDS = 0;
	const	STAT_GENDER = 1;
	const	STAT_AGE = 2;
	const	STAT_INVITES = 3;

	/**
	 * Supported Platforms
	 */
	const PF_FACEBOOK = 1000;
	const PF_MySpace = 1001;
	const PF_Bebo = 1002;
	const PF_Meebo = 1003;
	const PF_Orkut = 1004;
	const PF_OpenSocial = 1005;
	const PF_Friendster = 1006;

	protected $m_sUserAgent = "";
	protected $m_sAppId = "";
	protected $m_sAppProfileId = "";
	protected $m_sUrl = "http://www.sonetrix.com/ws/fb.php";
	protected $m_bIsValid = false;
	protected $m_sdDemo = null;
	protected $m_iHashUID = null;
	protected $m_bLogCalls = false;
	protected $m_iPTC = SonetrixPlatformTypes::Facebook;
	protected $m_sReferrer = "";
	protected $m_sSourceIP = "";
	protected $m_oFacebook = null;
	protected $m_sPFUserId = "";
	protected $m_sDevKey = null;
	protected $m_sAppKey = null;

	/**
	 * Constructor
	 * @param unknown_type $sAppId
	 */
	public function __construct( $sAppId, $iPTC = SonetrixPlatformTypes::Facebook, $sDevKey = null, $sAppKey = null, $oFacebook = null, $sAppProfileId = null )
	{
		$this->m_sReferrer = $_SERVER['HTTP_REFERER'];
		$this->m_sSourceIP = $_SERVER['REMOTE_ADDR'];
		$this->m_sAppId = $sAppId;
		$this->m_sAppProfileId = $sAppProfileId;
		$this->m_sUserAgent = "Sonetrix API PHP5 Client 0.95b (curl/sonetrix.com) " . phpversion();
		$this->m_sDevKey = $sDevKey;
		$this->m_sAppKey = $sAppKey;

		//	Set to false to disable Sonetrix
		$this->m_bIsValid = true;

		//	Platform type code...
		$this->m_iPTC = $iPTC;

		//	The Facebook API object...
		$this->m_oFacebook = $oFacebook;

		//	Get demo data if available...
		if ( null != $this->m_oFacebook && $this->m_oFacebook->user )
		{
			//	Store user id
			$this->m_sPFUserId = $this->m_oFacebook->user;

			//	Get hash from preferences...
			try
			{
				$sPref = $this->m_oFacebook->api_client->data_getUserPreference( SonetrixPreferences::HashUID );
			}
			catch ( Exception $_ex )
			{
				$sPref = null;
			}

			//	Not there? Make a new one...
			if ( null == $sPref || "" == $sPref )
			{
				//	Pull demo data...
				try
				{
					$arDemoData = $this->m_oFacebook->api_client->users_getInfo( $this->m_sPFUserId, "birthday,current_location,sex" );

					//	Set the demo data...
					$iHashUID = $this->setDemoData( $this->array_to_string( $arDemoData ) );
					if ( $iHashUID != "" && $iHashUID != null )
						$this->m_oFacebook->api_client->data_setUserPreference( SonetrixPreferences::HashUID, $iHashUID );
				}
				catch ( Exception $_ex )
				{
					//	Ignore...
				}
			}
			else
				$this->setHashUID( $sPref );
		}
	}

	/***
	*@desc Builds a payload for a Sonetrix request
	*/
	protected function buildPayload( $sCmd, $arData = null )
	{
		//	Start with the command
		$sOut = "c=$sCmd";

		//	Sonetrix application key
		if ( null != $this->m_sAppKey )
			$sOut = "&ak=" . $this->m_sAppKey;

		//	Sonetrix developer key
		if ( null != $this->m_sDevKey )
			$sOut = "&dk=" . $this->m_sDevKey;

		//	Add platform type code...
		$sOut .= "&ptc=" . $this->m_iPTC;

		//	Add on app id
		$sOut .= "&uai=" . $this->m_sAppId;

		//	Add app profile id if available
		if ( "" != $this->m_sAppProfileId )
			$sOut .= "&uapi=" . $this->m_sAppProfileId;

		//	Add hash uid / demo data
		if ( null != $this->m_iHashUID )
		{
			$sOut .= "&huid=" . $this->m_iHashUID;
		}
		else
		{
			if ( null != $this->m_sdDemo )
				$sOut .= "&ud=" . $this->m_sdDemo;
		}

		//	Add referrer if available
		if ( "" != $this->m_sReferrer && null != $this->m_sReferrer )
			$sOut .= "&href=" . urlencode( $this->m_sReferrer );

		//	And source IP address..
		$sOut .= "&srcip=" . urlencode( $this->m_sSourceIP );

		//	Add additional parameters if provided
		if ( null != $arData )
		{
			foreach ( $arData as $sKey => $oValue )
			{
				$sOut .= "&" . $sKey . "=" . $oValue;
			}
		}

		return( $sOut );
	}

	/**
	 * Set the demo data for this connection
	 *
	 * @param unknown_type $sdData
	 */
	public function setDemoData( $sdData, $bDebug = false )
	{
		$this->m_sdDemo = $sdData;

		//	Get a hash UID from Sonetrix...
		$iHashUID = intval( $this->getRequest( $this->m_sUrl, $this->buildPayload( "hash" ) ) );
		$this->setHashUID( $iHashUID );
		return( $iHashUID );
	}

	/**
	 * Sets the has for the current user
	 *
	 * @param unknown_type $iHashUID
	 */
	public function setHashUID( $iHashUID )
	{
		$this->m_iHashUID = $iHashUID;
		return( $iHashUID );
	}

	/**
	 * Returns the hash for the current user
	 *
	 * @return unknown
	 */
	public function getHashUID()
	{
		return( $this->m_iHashUID );
	}

	/**
	 * Track a user install
	 *
	 * @param unknown_type $sUser
	 * @return unknown
	 */
	public function user_install( $sUser = "", $sSigSessionKey = "" )
	{
		if ( ! $this->m_bIsValid )
			return( false );

		$sPayload = $this->buildPayload( "install", array( "u" => ( $sUser == "" ? $this->m_sPFUserId : $sUser ), "ssk" => urlencode( $sSigSessionKey ) ) );
		return( $this->setHashUID( intval( $this->getRequest( $this->m_sUrl, $sPayload ) ) ) );
	}

	/**
	 * Track a user uninstall
	 *
	 * @param unknown_type $sUser
	 * @return unknown
	 */
	public function user_uninstall( $sUser = "" )
	{
		if ( ! $this->m_bIsValid )
			return( false );

		$sPayload = $this->buildPayload( "uninstall", array( "u" => ( $sUser == "" ? $this->m_sPFUserId : $sUser ) ) );
		return( $this->getRequest( $this->m_sUrl, $sPayload ) == "OK" );
	}

	/**
	 * Track a page view
	 *
	 * @param unknown_type $sUser
	 * @param unknown_type $sPage
	 * @return unknown
	 */
	public function user_pageView( $sUser = "", $sPage = "" )
	{
		if ( ! $this->m_bIsValid )
			return( false );

		$sPayload = $this->buildPayload( "hit", array( "u" => ( $sUser == "" ? $this->m_sPFUserId : $sUser ), "p" => urlencode( $sPage ) ) );
		$sResult = $this->getRequest( $this->m_sUrl, $sPayload );
		return( $sResult == "OK" );
	}

	/**
	 * Track user invites
	 *
	 * @param unknown_type $sUser
	 * @param unknown_type $sIDList
	 * @return unknown
	 */
	public function user_trackInvites( $sUser = "", $sIDList = "" )
	{
		if ( ! $this->m_bIsValid )
			return( false );

		$sPayload = $this->buildPayload( "invite", array( "u" => ( $sUser == "" ? $this->m_sPFUserId : $sUser ), "list" => urlencode( $sIDList ) ) );
		$sResult = $this->getRequest( $this->m_sUrl, $sPayload );
		return( $sResult == "OK" );
	}

	/**
	 * Track conversions
	 *
	 * @param unknown_type $sUser
	 * @param unknown_type $sIDList
	 * @return unknown
	 */
	public function user_trackReferral( $sUser = "", $sRefID = "" )
	{
		if ( ! $this->m_bIsValid )
			return( false );

		$sPayload = $this->buildPayload( "convert", array( "u" => ( $sUser == "" ? $this->m_sPFUserId : $sUser ), "r" => $sRefID ) );
		return( $this->getRequest( $this->m_sUrl, $sPayload ) );
	}

	/**
	 * Get a comma separated list of previously invited users...
	 *
	 * @param unknown_type $sUser
	 * @return unknown
	 */
	public function user_getInvitees( $sUser = "" )
	{
		if ( ! $this->m_bIsValid )
			return( null );

		$sPayload = $this->buildPayload( "invitees", array( "u" => ( $sUser == "" ? $this->m_sPFUserId : $sUser ) ) );
		return( $this->getRequest( $this->m_sUrl, $sPayload ) );
	}

	/**
	 * Get stats for a year/month
	 *
	 * @param unknown_type $iYear
	 * @param unknown_type $iMonth
	 * @return unknown
	 */
	public function stats_getStatsYM( $iYear, $iMonth )
	{
		return( $this->stats_getStatsYMD( $iYear, $iMonth ) );
	}

	/**
	 * Get stats for a year/month/day
	 *
	 * @param unknown_type $iYear
	 * @param unknown_type $iMonth
	 * @param unknown_type $iDay
	 * @return unknown
	 */
	public function stats_getStatsYMD( $iYear, $iMonth, $iDay = "0" )
	{
		if ( ! $this->m_bIsValid )
			return( null );

		$sPayload = $this->buildPayload( "stats", array( "mm" => $iMonth, "yy" => $iYear, "dd" => $iDay, "hh" => "-1" ) );
		$sXml = $this->getRequest( $this->m_sUrl, $sPayload );
		return( XmlToArray( $sXml ) );
	}

	/**
	 * Make the REST request
	 *
	 * @param unknown_type $sUrl
	 * @param unknown_type $sQueryString
	 * @return unknown
	 */
	protected function getRequest( $sUrl, $sQueryString )
	{
		if ( function_exists( 'curl_init' ) )
		{
			// Use CURL if installed...
			$oConn = curl_init();
			curl_setopt( $oConn, CURLOPT_URL, $sUrl . "?" . $sQueryString );
			curl_setopt( $oConn, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $oConn, CURLOPT_USERAGENT, $this->m_sUserAgent );
			curl_setopt( $oConn, CURLOPT_TIMEOUT, 60 );
			$sResult = curl_exec( $oConn );
			curl_close( $oConn );
		}
		else
		{
			// Non-CURL based version...
			$oContext =
				array('http' =>
					array('method' => 'POST',
						'header' => 'Content-type: application/x-www-form-urlencoded'."\r\n".
						'User-Agent: ' . $m_sUserAgent ."\r\n".
						'Content-length: ' . strlen( $sQueryString ),
						'content' => $post_string)
					);

			$oContextId = stream_context_create( $oContext );

			$oSocket = fopen( $sUrl . "?" . $sQueryString, 'r', false, $oContextId );

			if ( $oSocket )
			{
				$sResult = '';

				while ( !feof( $oSocket ) )
					$sResult .= fgets( $oSocket, 4096 );

				fclose( $oSocket );
			}
		}

		return( $sResult );
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $sXml
	 * @return unknown
	 */
	protected function XmlToArray( $sXml )
	{
		$arData = array();

		if ( $sXml )
		{
			$oAttrs = $sXml->attributes();

			foreach ( $sXml->children() as $oChild)
			{
				if ( !empty($oAttrs['list'] ) )
				{
					$arData[] = $this->XmlToArray( $oChild );
				}
				else
				{
					$arData[ $oChild->___n ] = $this->XmlToArray( $oChild );
				}
			}
		}

		if ( sizeof( $arData ) > 0 )
		{
			return( $arData );
		}
		else
		{
			return( ( string )$sXml->CDATA() );
		}
	}

	/**
	 * Converts an array to a string that is safe to pass via a URL
	 *
	 * @param unknown_type $array
	 * @return unknown
	 */
	public function array_to_string( $array )
	{
	   $retval = '';
	   $null_value = "^^^";
	   if ( $array != null )
	   {
		   foreach ($array as $index => $val) {
			   if(gettype($val)=='array') $value='^^array^'.$this->array_to_string($val);    else $value=$val;
			   if (!$value)
				   $value = $null_value;
			   $retval .= urlencode(base64_encode($index)) . '|' . urlencode(base64_encode($value)) . '||';
		   }
	   }
	   return urlencode(substr($retval, 0, -2));
	}

	/**
	 * Converts a string created by array_to_string() back into an array.
	 *
	 * @param unknown_type $string
	 * @return unknown
	 */
	public function string_to_array($string)
	{
	   $retval = array();
	   $string = urldecode($string);
	   $tmp_array = explode('||', $string);
	   $null_value = urlencode(base64_encode("^^^"));
	   foreach ($tmp_array as $tmp_val) {
		   list($index, $value) = explode('|', $tmp_val);
		   $decoded_index = base64_decode(urldecode($index));
		   if($value != $null_value){
			   $val= base64_decode(urldecode($value));
			   if(substr($val,0,8)=='^^array^') $val=$this->string_to_array(substr($val,8));
			   $retval[$decoded_index]=$val;
			  }
		   else
			   $retval[$decoded_index] = NULL;
	   }
	   return $retval;
	}

	/***
	*@desc Convenience function to get user id
	*/
	protected function getPFUserId( $sPFUserId = null )
	{
		return( $sPFUserId == null ? $this->PFUserId : $sPFUserId );
	}

	public function processQueryString( $sPFUserId = null )
	{
		//	Handle query string
		if ( isset( $_REQUEST['ids'] ) )
			$this->user_trackInvites( $this->getPFUserId( $sPFUserId ), implode( ",", $_REQUEST['ids'] ) );
	}
}

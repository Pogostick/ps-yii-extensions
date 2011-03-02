<?php
/**
 * This file is part of the SnowFrame package.
 * 
 * @copyright Copyright &copy; 2010 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */
 
 

/**
 * The base SnowFrame application
 * 
 * @package 	snowframe
 * @subpackage 	base
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id: CSFBaseApplication.php 388 2010-06-13 16:26:43Z jerryablan@gmail.com $
 * @since 		v1.1.0
 *  
 * @filesource
 */
class CSFBaseApplication extends CPSApiComponent
{
	//********************************************************************************
	//* Public Methods
	//********************************************************************************
	
	/**
	 * Initialize
	 */
	public function preinit()
	{
		parent::preinit();
		$this->addOptions( self::getBaseOptions() );
	}
	
	/**
	 * Get our base options
	 * @return array
	 */
	private function getBaseOptions()
	{
		return array(
			'appId' => 'string',
			'appProfileId' => 'string',
			'appName' => 'string',
			'appPage' => 'string',
			'appUrl' => 'string',
			'appName' => 'string',
			'callbackUrl' => 'string',
			'pfTypeCode' => 'int',
			'pfApi' => 'object',
			'enableSonetrix' => 'bool:true',
			'sonetrix' => 'object',
			'currentUser' => 'object',
			'pfUserId' => 'string',
			'pfRefUserId' => 'string',
			'banners' => 'array:array()',
			'invitePlacement' => 'int:' . SF::IP_AFTER_ADD,
			'pageAction' => 'string:home',
			'pageCommand' => 'string',
			'googleAnalyticsKey' => 'string',
			'serverUrl' => 'string',
			'dbUserClassName' => 'string',
			'clientIPAddress' => 'string',
			'httpReferrer' => 'string',
		);
	}

	/**
	 * Intialize
	 */
	public function init()
	{
		parent::init();
		
		//	Construct a platform specific object
		$this->initializePlatform();

		//	Create a new Sonetrix object...
		if ( $this->m_bEnableSonetrix )
		{
			$this->sonetrix = new Sonetrix( $sAppId, $iPTC, null, null, ( $iPTC == SF::PT_FACEBOOK && $this->pfApi->PFAPI ? $this->pfApi->PFAPI : null ), $sProfileId );
			if ( $this->sonetrix && $this->pfApi ) $this->pfApi->Sonetrix = $this->sonetrix;
		}

		//	Process the query string...
		$this->processQueryString();
	}

	/***
	* Initialize the platform api
	*/
	public function initializePlatform()
	{
		switch ( $this->pfTypeCode )
		{
			case SF::PT_FRIENDSTER:
			case SF::PT_MYSPACE:
				break;

			case SF::PT_FACEBOOK:
				$this->pfApi = new CSFFacebookPlatform( $sBaseUrl, $sAppName, $sAppId, $sAPIKey, $sAPISecret );
				break;

			case SF::PT_BEBO:
				$this->pfApi = new CSFBeboPlatform( $sBaseUrl, $sAppName, $sAppId, $sAPIKey, $sAPISecret, $sProfileId );
				break;
		}
		
		if ( ! $this->pfApi )
			throw new CException( 'Invalid, unsupported, or no platform type selected.' );
	}

	/**
	 * Generic query string processing for the application.
	 */
	protected function processQueryString()
	{
		$this->httpReferrer = PS::o( $_SERVER, 'HTTP_REFERER' );
		$this->clientIPAddress = PS::o( $_SERVER, 'REMOTE_ADDR' );
		$this->pageAction = PS::o( $_REQUEST, 'action' );
		$this->pageSubAction = PS::o( $_REQUEST, 'sa' );

		if ( null !== ( $_sCmd = PS::o( $_REQUEST, 'c', null, true ) ) )
			$this->processApplicationCommand( $_sCmd );
	}

	/**
	 * Process any single application commands
	 * @param string $sCommand
	 */
	protected function processApplicationCommand( $sCommand = null )
	{
		$this->pageCommand = trim( PS::nvl( $sCommand, $this->pageCommand ) );

		//	Deal with screwy facebook bug where they don't parse url properly
		if ( substr( $this->pageCommand, 0, 7 ) == 'addhttp' )
		{
			$_sUrl = substr( $this->pageCommand, 3 );
			$this->redirect( $this->pfApi->appUrl . '?c=add&auth_token=' . $_REQUEST['auth_token'] . '&installed=1&refuid=' . ( isset( $_REQUEST['refuid'] ) ? $_REQUEST['refuid'] : '' ) );
		}

		switch ( $this->pageCommand )
		{
			case 'ping':
				echo 'pong';
				break;
				
			case 'del':
				if ( $this->currentUser )
					$this->currentUser->remove();
				break;
		}
		
		//	Stop execution here.
		exit;
	}

	/**
	 * Tracks a page hit...
	 * @param string $sPage
	 */
	public function capturePageView( $sPage )
	{
		if ( $this->sonetrix )
			$this->sonetrix->user_pageView( $this->PFUserId, $sPage );
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
			if ( ! $this->UserDB && $this->sonetrix )
				$this->sonetrix->setHashUID( $this->sonetrix->user_install( $this->pfApi->PFUserId, $this->pfApi->getSessionKey() ) );

			$this->redirect( $this->pfApi->AppUrl . "invite.php" );
		}
	}

	/***
	*@desc generic redirect
	*/
	public function redirect( $sUrl )
	{
		$this->pfApi->redirect( $sUrl );
	}

	/**
	 * Returns a comma separated list of invited people for exclusion...
	 *
	 * @return unknown
	 */
	public function getInviteeList()
	{
		if ( $this->m_bEnableSonetrix && $this->sonetrix )
			return( $this->sonetrix->user_getInvitees( $this->PFUserId ) );
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
		$this->pfApi->setRefHandle( $sFBML );
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
			$sOut = $this->m_arBanner[ $iAVC ]->getAdCode( $this->pfApi->getPFType(), $eType );
		else
		{
			foreach ( $this->m_arBanner as $sKey => $oValue )
				$sOut = $oValue->getAdCode( $this->pfApi->getPFType(), $eType );
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
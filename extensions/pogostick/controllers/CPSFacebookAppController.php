<?php
/**
 * CSFacebookAppController.php
 *
 * Copyright (c) 2010 Jerry Ablan <jablan@pogostick.com>.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 *
 * This file is part of Pogostick : Yii Extensions.
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
Yii::import( 'pogostick.components.facebook.CPSFacebook' );

/**
 * CPSFacebookAppController class file.
 *
 * @package 	psYiiExtensions
 * @subpackage	controllers
 *
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN $Id$
 * @since 		v1.0.0
 *
 * @filesource
 */
class CPSFacebookAppController extends CPSController
{
	//********************************************************************************
	//* Configuration Parameters
	//********************************************************************************

	/**
	 * Maximum number of photos to show
	 */
	const MAX_FRIENDS_TO_SHOW = 7;
	const DEBUG = false;
	const USE_CACHE = false;
	const IS_CANVAS = true;
	const IS_CONNECT = false;
	const THUMB_CACHE = 'thumb_cache';
	const FRIEND_CACHE = 'friend_cache';
	const APP_FRIEND_CACHE = 'app_friend_cache';
	const PROFILE_CACHE = 'profile_cache';
	const ME_CACHE = 'me_cache';
	const FL_CACHE = 'fl_cache';

	//********************************************************************************
	//* Private Members
	//********************************************************************************

	/**
	* @var string The user model class
	*/
	protected $_userModelClass = 'User';
	public function getUserModelClass() { return $this->_userModelClass; }
	public function setUserModelClass( $value ) { $this->_userModelClass = $value; }

	/**
	 * The Facebook API
	 * @var CPSFacebook
	 */
	protected $_facebookApi;
	public function getFacebookApi() { return $this->_facebookApi; }

	/**
	 * @var CPSFacebookUserIdentity The current user
	 */
	protected $_identity = null;
	public function getIdentity() { return $this->_identity; }

	/**
	 * @var string User's access token
	 */
	protected $_accessToken;
	public function getAccessToken() { return $this->_accessToken; }

	/**
	 * @var string Logout Url
	 */
	protected $_logoutUrl = '';
	public function getLogoutUrl() { return $this->_logoutUrl; }

	/**
	 * @var string Login Url
	 */
	protected $_loginUrl = '';
	public function getLoginUrl() { return $this->_loginUrl; }

	/**
	 * @var array The Facebook session data
	 */
	protected $_session;
	public function getSession() { return $this->_session; }

	/**
	 * @var boolean True if this is a deauthorization request
	 */
	protected $_isDeauthRequest = false;
	public function getIsDeauthRequest() { return $this->_isDeauthRequest; }

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	 * Initialize
	 */
	public function init()
	{
		//	See if this is a deauth...
		$this->_isDeauthRequest = ( false !== strpos( $_SERVER['REQUEST_URI'], 'app/deauthorize' ) );

		if ( isset( $_REQUEST['request_ids'] ) )
		{
			$this->actionInviteComplete( 'request_ids' );
			$this->redirect( '/' );
		}

		parent::init();

		//	Get our identity
		$this->_identity = PS::_gs( 'currentIdentity' );

        //	Handle an rss feed
        if ( isset( $_REQUEST[ 'rss' ] ) )
        {
            header( "Content-Type: application/xml; charset=ISO-8859-1" );
            echo $this->_getRssFeed();
            die();
        }

		//	No facebook?
		if ( PS::_gs( 'standalone' ) || PS::o( $_REQUEST, 'standalone' ) )
			PS::_ss( 'standalone', true );

		//	Set up events...
		$this->onFacebookLogin = array( $this, 'facebookLogin' );

		//	Ignore ajax requests...
		if ( ! Yii::app()->getRequest()->getIsAjaxRequest() )
		{
			//	Set up the Facebook API
			$this->_initializeFacebook();
		}
	}

	/**
	* The filters for this controller
	*
	* @return array Action filters
	*/
	public function filters()
	{
		if ( PS::isCLI() ) return array();

		//	Perform access control for CRUD operations
		return array(
			'accessControl',
		);
	}

	/**
	* The base access rules for our CRUD controller
	*
	* @return array Access control rules
	*/
	public function accessRules()
    {
		//	The base rules allow only authorized users
        return array(
            array( 'allow',
                'actions' => array( '*' ),
                'users' => array( '@' ),
            ),
        );
    }

	//********************************************************************************
	//* Public Actions
	//********************************************************************************

	/**
	 * Home page
	 */
	public function actionIndex()
	{
		$this->render( 'index' );
	}

	/**
	 * Admin page
	 */
	public function actionAdmin()
	{
		$this->layout = 'admin';
		if ( null !== ( $_user = PS::_gs( 'currentUser' ) ) )
			$this->render( ( $_user && $_user->admin_level_nbr != 0 ) ? 'admin' : 'index' );
	}

	/**
	 * Called after invitations have been sent.
	 */
	public function actionInviteComplete( $queryParam = 'ids' )
	{
		if ( defined( 'PYE_TRACE_LEVEL' ) && PYE_TRACE_LEVEL > 3 )
			CPSLog::trace( __METHOD__, 'Invite complete: ' . print_r( $_REQUEST, true ) );

		//	$_POST['ids'] is an array of invited friends...
		if ( null !== ( $_user = PS::_gs( 'currentUser' ) ) )
		{
			$_user->invite_count_nbr += count( PS::o( $_REQUEST, $queryParam, array() ) );
			$_user->update( array( 'invite_count_nbr' ) );
		}

		$this->redirect( PS::_gp( 'appUrl' ) );
	}

	/**
	 * Called when user removes app and/or permissions
	 * @todo when Facebook fixes their shit and actually send a user id along with this, it will be useful.
	 */
	public function actionDeauthorize()
	{
		if ( defined( 'PYE_TRACE_LEVEL' ) && PYE_TRACE_LEVEL > 3 )
			CPSLog::trace( __METHOD__, 'Deauth request: ' . print_r( $_REQUEST, true ) );

		$this->layout = false;

		//	Reset!
		if ( null !== ( $_user = PS::_gs( 'currentUser' ) ) )
		{
			$_user->app_del_date = date( 'Y-m-d H:i:s' );
			$_user->app_del_count_nbr += 1;
			$_user->access_token_text = null;
			$_user->session_key_text = null;
			$_user->update( array( 'app_del_date', 'app_del_count_nbr', 'access_token_text', 'session_key_text' ) );
		}
	}

	/**
	 * Invite page
	 */
	public function actionInviteFriends()
	{
		$this->render( 'index' );
	}

	/**
	 * This is the action to handle external exceptions.
	 */
	public function actionError()
	{
	    if ( $error = Yii::app()->errorHandler->error )
	    {
	    	if ( Yii::app()->request->isAjaxRequest )
			{
				$this->layout = false;
	    		echo $error['message'];
				return;
			}

        	$this->render( 'error', $error );
	    }
	}

	//********************************************************************************
	//* Events
	//********************************************************************************

	/**
	 * Event: facebookLogin
	 * @param CEvent $event
	 */
	public function onFacebookLogin( CEvent $event )
	{
		$this->raiseEvent( 'onFacebookLogin', $event );
	}

	/**
	 * facebookLogin event handler stub
	 * @param CEvent $event
	 * @return boolean
	 */
	public function facebookLogin( CEvent $event )
	{
		return true;
	}

	public function getFbUserName( $fbUserId )
	{
		try
		{
			if ( $this->_identity && $this->_identity->getFBUserId() == $fbUserId )
				return 'You';

			$_user = json_decode( file_get_contents( 'https://graph.facebook.com/' . $fbUserId ) );

			if ( $_user )
				return trim( $_user->first_name . ' ' . $_user->last_name );
		}
		catch ( Exception $_ex )
		{
		}

		return 'Facebook User';
	}

	//********************************************************************************
	//* Private Methods
	//********************************************************************************

	/**
	 * Initialize the Facebook stuff
	 * @return boolean
	 */
	protected function _initializeFacebook()
	{
		//	Create the api object
		$this->_facebookApi = PS::_gco( 'facebook' );

		//	Get the login url
		$this->_loginUrl = $this->_facebookApi->getLoginUrl(
			array(
				'canvas' => self::IS_CANVAS,
				'fbconnect' => self::IS_CONNECT,
				'req_perms' => $this->_facebookApi->getAppPermissions(),
			)
		);

		PS::_ss(
			'_currentIdentity',
			$this->_identity = new CPSFacebookUserIdentity(
				$this->_facebookApi,
				$this->_loginUrl,
				$this->_userModelClass
			)
		);
	}

	/**
	 * Returns an RSS feed
	 * @param integer items to return
	 */
	protected function _getRssFeed( $items = 5 )
	{
	}
}
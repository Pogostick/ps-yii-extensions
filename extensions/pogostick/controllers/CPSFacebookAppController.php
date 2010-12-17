<?php
/*
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
	const IS_CANVAS = false;
	const IS_CONNECT = true;
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
	 * The Facebook API
	 * @var CPSFacebook
	 */
	protected $_facebookApi;
	public function getFacebookApi() { return $this->_facebookApi; }

	/**
	 * @var array The Facebook session data
	 */
	protected $_session;
	public function getSession() { return $this->_session; }

	/**
	 * @var string User's access token
	 */
	protected $_accessToken;
	public function getAccessToken() { return $this->_accessToken; }

	/**
	 * @var string The user's Facebook ID
	 */
	protected $_fbUserId;
	public function getFBUserId() { return $this->_fbUserId; }

	/**
	 * @var array The user's Facebook info
	 */
	protected $_me;
	public function getMe() { return $this->_me; }

	/**
	 * @var string Login Url
	 */
	protected $_firstName;
	public function getFirstName() { return $this->_firstName; }

	/**
	 * @var string Login Url
	 */
	protected $_loginUrl = '';
	public function getLoginUrl() { return $this->_loginUrl; }

	/**
	 * @var string Logout Url
	 */
	protected $_logoutUrl = '';
	public function getLogoutUrl() { return $this->_logoutUrl; }

	/**
	 * @var User The current user
	 */
	protected $_user = null;
	public function getUser() { return $this->_user; }

	/**
	 * @var array The users' list of friends
	 */
	protected $_friendList = null;
	public function getFriendList() { return $this->_friendList; }

	/**
	 * @var array The users' list of friends who also use this app
	 */
	protected $_appFriendList = null;
	public function getAppFriendList() { return $this->_appFriendList; }

	/**
	 * @var boolean If true, user's albums and pictures are loaded and cached
	 */
	protected $_autoLoadPictures = true;
	public function getAutoLoadPictures() { return $this->_autoLoadPictures; }
	public function setAutoLoadPictures( $value ) { $this->_autoLoadPictures = $value; return $this; }

	/**
	 * @var boolean True if user has authorized the app
	 */
	protected $_isConnected = false;
	public function getIsConnected() { return $this->_isConnected; }

	/**
	 * @var boolean True if this is a deauthorization request
	 */
	protected $_isDeauthRequest = false;
	public function getIsDeauthRequest() { return $this->_isDeauthRequest; }

	/**
	 * @var boolean If true, user is redirected to invite friends page after allowing application
	 */
	protected $_inviteAfterInstall = true;
	public function getInviteAfterInstall() { return $this->_inviteAfterInstall; }
	public function setInviteAfterInstall( $value ) { $this->_inviteAfterInstall = $value; }

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

		parent::init();

		//	Set proper ini settings for FBC
        ini_set( 'zend.ze1_compatibility_mode', 0 );

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
			//	Set up the session for the page
			if ( ! PS::_gs( 'standalone' ) )
				$this->_initializeFacebook();
		}
	}

	/**
	* The filters for this controller
	*
	* @returns array Action filters
	*/
	public function filters()
	{
		if ( $_SERVER['HTTP_HOST'] == 'localhost' ) return array();

		//	Perform access control for CRUD operations
		return array(
//			'accessControl',
		);
	}

	/**
	* The base access rules for our CRUD controller
	*
	* @returns array Access control rules
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
		$this->render( ( $this->_user && $this->_user->admin_level_nbr != 0 ) ? 'admin' : 'index' );
	}

	/**
	 * Called after invitations have been sent.
	 */
	public function actionInviteComplete()
	{
		CPSLog::trace( __METHOD__, 'Invite complete: ' . print_r( $_REQUEST, true ) );

		//	$_POST['ids'] is an array of invited friends...
		$this->_user->invite_count_nbr += count( PS::o( $_REQUEST, 'ids', array() ) );
		$this->_user->update( array( 'invite_count_nbr' ) );
		$this->redirect( PS::_gp( 'appUrl' ) );
	}

	/**
	 * Called when user removes app and/or permissions
	 * @todo when Facebook fixes their shit and actually send a user id along with this, it will be useful.
	 */
	public function actionDeauthorize()
	{
		CPSLog::trace( __METHOD__, 'Deauth request: ' . print_r( $_REQUEST, true ) );

		$this->layout = false;

		//	Reset!
		if ( $this->_user )
		{
			$this->_user->app_del_date = date( 'Y-m-d H:i:s' );
			$this->_user->app_del_count_nbr += 1;
			$this->_user->access_token_text = null;
			$this->_user->session_key_text = null;
			$this->_user->update( array( 'app_del_date', 'app_del_count_nbr', 'access_token_text', 'session_key_text' ) );
		}
	}

	/**
	 * Invite page
	 */
	public function actionInviteFriends()
	{
		$this->render( 'inviteFriends' );
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

	//********************************************************************************
	//* Private Methods
	//********************************************************************************

	/**
	 * Get all friend data
	 * @return array
	 */
	protected function _getAllFriends()
	{
		$this->_friendList = PS::_gs( self::FRIEND_CACHE );

		try
		{
			if ( empty( $this->_friendList ) )
			{
				$_fql = "SELECT uid, name, first_name, pic_big, pic_square FROM user WHERE uid IN ( SELECT uid2 FROM friend where uid1 = '{$this->_fbUserId}' ) order by name";
				$this->_friendList = $this->_facebookApi->api( array( 'method' => 'fql.query', 'query' => $_fql ) );
				PS::_ss( self::FRIEND_CACHE, $this->_friendList );
			}
		}
		catch ( Exception $_ex )
		{
			CPSLog::error( __METHOD__, 'Exception: ' . $_ex->getMessage() );
			error_log( 'Get all friends failed: ' . $_ex->getMessage() );
			$this->_forceLogin();
		}

		return $this->_friendList;
	}

	/**
	 * Get friend data who have this app
	 * @return array
	 */
	protected function _getAppFriends()
	{
		CPSLog::trace( __METHOD__, 'Getting app friends...' );

		$this->_appFriendList = null; //PS::_gs( self::APP_FRIEND_CACHE . $this->_fbUserId );

		try
		{
			if ( empty( $this->_appFriendList ) )
			{
				$_fql = "select uid from user where is_app_user = '1' and uid in ( select uid2 from friend where uid1 = '{$this->_fbUserId}' ) order by name";
				$_list = $this->_facebookApi->api( array( 'method' => 'fql.query', 'query' => $_fql ) );

				CPSLog::trace( __METHOD__, '  - App Friend List Retrieved: ' . print_r( $_list, true ) );

				//	Make into a list of uids...
				foreach ( $_list as $_friend )
				{
					if ( ! empty( $_friend['uid'] ) )
						$this->_appFriendList[] = '\'' . $_friend['uid'] . '\'';
				}

				PS::_ss( self::APP_FRIEND_CACHE . $this->_fbUserId, $this->_appFriendList );
			}
		}
		catch ( Exception $_ex )
		{
			CPSLog::error( __METHOD__, 'Exception: ' . $_ex->getMessage() );
			error_log( 'Get APP friends failed: ' . $_ex->getMessage() );
			$this->_forceLogin();
		}

		CPSLog::trace( __METHOD__, '  - App Friend List: ' . print_r( $this->_appFriendList, true ) );
		return $this->_appFriendList;
	}

	/**
	 * Initialize the Facebook stuff
	 * @return boolean
	 */
	protected function _initializeFacebook()
	{
		//	Create the api object
		try
		{
			$this->_facebookApi = Yii::app()->getComponent( 'facebook' );
		}
		catch ( Exception $_ex )
		{
			throw $_ex;
		}

		//	Get the login url
		$this->_loginUrl = $this->_facebookApi->getLoginUrl(
			array(
				'canvas' => self::IS_CANVAS,
				'fbconnect' => self::IS_CONNECT,
				'req_perms' => $this->_facebookApi->getAppPermissions(),
			)
		);

		$this->_session = $this->_facebookApi->getSession();

		if ( ! $this->_session )
		{
			error_log( 'No session' );
			$this->_forceLogin();
		}

		try
		{
			//	Get our info...
			$this->_me = $this->_facebookApi->api( '/me' );

			if ( ! $this->_me )
				throw new Exception( 'Not really logged in...' );

			if ( PS::_ig() && ! CPSFacebookUser::authenticateUser( $this->_facebookApi ) )
				throw new Exception( 'Invalid session' );

			//	Log into system...
			$this->_fbUserId = $this->_session['uid'];
			$this->_accessToken = $this->_session['access_token'];
			$this->_firstName = PS::o( $this->_me, 'first_name' );
			$this->_loadUser();

			if ( $this->_autoLoadPictures )
			{
				$_photoList = $this->getFacebookApi()->getAlbums();

				if ( empty( $_photoList ) )
					PS::_rs( '_psAutoLoadPictures', '$(function(){$.get("/app/photos",function(){});});' );
			}

			return $this->_isConnected = true;
		}
		catch ( Exception $_ex )
		{
			CPSLog::error( __METHOD__, 'FB Exception: ' . $_ex->getMessage() );
			error_log( 'init exception: ' . $_ex->getMessage() );
			$this->_forceLogin();
		}

		return false;
	}

	/**
	 * Loads the user from the database. If the user is not found, a new row is added.
	 * @return boolean
	 */
	protected function _loadUser()
	{
		$_user = null;

		//	NO user id? Bail!
		if ( empty( $this->_fbUserId ) )
		{
			CPSLog::error(__METHOD__,'FBUID EMPTY!' . $this->_fbUserId );
			return false;
		}

		//	Is this a new app user?
		$_user = User::model()->find( array(
			'condition' => 'pform_user_id_text = :pform_user_id_text and pform_type_code = :pform_type_code',
			'params' => array(
				':pform_user_id_text' => $this->_fbUserId,
				':pform_type_code' => 1000,
			)
		));

		//	Not found, assume new...
		if ( ! $_user )
		{
			//	New user...
			$_user = new User();
			$_user->pform_user_id_text = $this->_fbUserId;
			$_user->pform_type_code = 1000;
			$_user->app_add_date = date( 'Y-m-d h:i:s' );
			$_user->app_del_date = null;
		}

		//	Set new stuff
		$_user->session_key_text = $this->_accessToken;
		$_user->last_visit_date = date( 'Y-m-d h:i:s' );

		//	User installed app this time?
		if ( '1' == PS::o( $_REQUEST, 'installed' ) )
		{
			$_user->app_add_date = date( 'Y-m-d h:i:s' );
			$_user->app_del_date = null;
			$_user->app_add_count_nbr += 1;

			//	Invite friends?
			if ( $this->_inviteAfterInstall )
			{
				$this->redirect( 'inviteFriends', true, 301 );
				return false;
			}
		}

		if ( $this->_me )
		{
			$_user->first_name_text = $this->_firstName;
			$_user->last_name_text = PS::o( $this->_me, 'last_name' );
			$_user->email_addr_text = PS::o( $this->_me, 'email' );
			$_user->full_name_text = $this->_firstName . ' ' . strtoupper( substr( PS::o( $this->_me, 'last_name' ), 0, 1 ) . '.' );
		}

		//	Load app friends
		$this->_getAppFriends();

		//	Save info...
		$_user->save();

		//	Set our current user...
		PS::_ss( 'currentUser', $this->_user = $_user );

		//	Raise the facebook login event
		$this->onFacebookLogin( new CEvent( $_user ) );

		return true;
	}

	/**
	 * Returns an RSS feed
	 * @param integer items to return
	 */
	protected function _getRssFeed( $items = 5 )
	{
	}

	/**
	 * Forces a redirect to the Facebook login url...
	 */
	protected function _forceLogin()
	{
		//	If this is a deauth, clean up the database...
		if ( $this->_isDeauthRequest )
		{
			if ( $this->_loadUser() )
				$this->actionDeauthorize();

			die();
		}

		error_log( 'Force logging out user, no session found.' );
		CPSLog::info( __METHOD__, 'Force logging out user, no session found.' );

		//	Logout user...
		Yii::app()->user->logout();

		CPSLog::trace( __METHOD__, 'Facebook login redirect: ' . $this->_loginUrl );

		echo '<script type="text/javascript">window.top.location.href = "' . $this->_loginUrl . '";</script>';
		flush();
		exit;
	}

}
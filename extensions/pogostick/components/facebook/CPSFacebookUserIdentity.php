<?php
/**
 * This file is part of the Pogostick Yii Extension library
 *
 * @copyright Copyright &copy; 2009-2010 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */
/**
 * A base user identity class
 *
 * @package 	psYiiExtensions
 * @subpackage 	components
 *
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN $Id$
 *
 * @since 		v1.0.6
 * @filesource
 */
class CPSFacebookUserIdentity extends CUserIdentity
{
	//********************************************************************************
	//* Class Properties
	//********************************************************************************

	/**
	* @var string Our user id
	*/
	protected $_id = null;
	public function getId() { return $this->_id; }
	public function setId( $value ) { $this->_id = $value; }

	/**
	* @var string The user model class
	*/
	protected $_userModelClass = null;
	public function getUserModelClass() { return $this->_userModelClass; }
	public function setUserModelClass( $value ) { $this->_userModelClass = $value; }

	/**
	 * @var string User's access token
	 */
	protected $_accessToken;
	public function getAccessToken() { return $this->_accessToken; }

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
	 * @var string $fbUserId The user's Facebook ID
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
	 * @var CModel The current user model
	 */
	protected $_user = null;
	public function getUser() { return $this->_user; }

	/**
	 * @var array The users' list of friends
	 */
	protected $_friendList = null;
	public function getFriendList() { return $this->_friendList; }

	/**
	 * The users' list of friends who also use this app
	 * @property-read array $appFriendList
	 * @var array $_appFriendList
	 */
	protected $_appFriendList = null;
	public function getAppFriendList() { return $this->_appFriendList; }

	/**
	 * @property boolean $cacheAppFriends
	 * @var boolean $_cacheAppFriends
	 */
	protected $_cacheAppFriends = false;
	public function getCacheAppFriends() { return $this->_cacheAppFriends; }
	public function setCacheAppFriends( $value ) { $this->_cacheAppFriends = $value; }

	/**
	 * @var boolean If true, user's albums and pictures are loaded and cached
	 */
	protected $_autoLoadPictures = false;
	public function getAutoLoadPictures() { return $this->_autoLoadPictures; }
	public function setAutoLoadPictures( $value ) { $this->_autoLoadPictures = $value; return $this; }

	/**
	 * @var boolean If true, user is redirected to invite friends page after allowing application
	 */
	protected $_inviteAfterInstall = true;
	public function getInviteAfterInstall() { return $this->_inviteAfterInstall; }
	public function setInviteAfterInstall( $value ) { $this->_inviteAfterInstall = $value; }

	/**
	 * @var boolean True if user has authorized the app
	 */
	protected $_isConnected = false;
	public function getIsConnected() { return $this->_isConnected; }

	//********************************************************************************
	//* Private Members
	//********************************************************************************

	/**
	 * @var CPSFacebook An instance of our Facebook API object
	 */
	protected $_facebookApi;

	/**
	 * @var boolean If true, we are in the middle of forcing a login...
	 */
	protected $_inForceLogin;

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	 * Constructor.
	 *
	 * @param CPSFacebook
	 * @param string password
	 */
	public function __construct( CPSFacebook $facebookApi, $loginUrl, $userModelClass = 'User' )
	{
		parent::__construct( null, null );

		$this->_facebookApi = $facebookApi;
		$this->_loginUrl = $loginUrl;
		$this->_userModelClass = $userModelClass;

		Yii::import( 'pogostick.events.CPSAuthenticationEvent' );

		$this->_initialize();
	}

	/**
	 * Pass-through function to api()
	 * @return mixed
	 */
	public function api( /* variable */ )
	{
		$_arguments = func_get_args();
		return call_user_func_array( array( $this->_facebookApi, 'api' ), $_arguments );
	}

	/**
	 * Authenticates a user based on session validity from Facebook.
	 * This method is required by {@link IUserIdentity}.
	 * @return boolean whether authentication succeeds.
	 */
	public function authenticate()
	{
		$this->errorCode = self::ERROR_UNKNOWN_IDENTITY;

		if ( $this->_facebookApi )
		{
			$this->_session = $this->_facebookApi->getSession();
			$this->_me = $this->_facebookApi->api( '/me' );

			if ( $this->_session && $this->_me )
			{
				$this->errorCode = self::ERROR_NONE;
				$this->username = trim( $this->_me['name'] );

				//	Raise our event
				$this->onLoginSuccess( new CPSAuthenticationEvent( $this, $this->_me, $this ) );

				return true;
			}
		}

		$this->onLoginFailure( new CPSAuthenticationEvent( $this, $this->_me, $this ) );

		return false;
	}

	//********************************************************************************
	//* Protected Methods
	//********************************************************************************

	/**
	 * Initialize the Facebook stuff
	 * @todo Come up with a better property placement design
	 * @return boolean
	 */
	public function _initialize()
	{
		//	Ignore second call and standalone...
		if ( PS::_gs( 'standalone', false ) || $this->_inForceLogin )
			return false;

		$this->_session = $this->_facebookApi->getSession();

		if ( ! $this->_session )
		{
			$this->_inForceLogin = true;
			$this->_forceLogin();
		}

		$this->_inForceLogin = false;

		try
		{
			//	Get our info...
			$this->_me = PS::_gs( 'currentMe' );

CPSLog::trace( __METHOD__, '  . Pulling /me' );
			try
			{
				if ( null === $this->_me && ! ( $this->_me = $this->_facebookApi->api( '/me' ) ) )
					throw new Exception( 'Not really logged in...' );
			}
			catch ( CPSFacebookApiException $_ex )
			{
CPSLog::error( __METHOD__, 'FB API Exception: ' . $_ex->getMessage() );
			}

CPSLog::trace( __METHOD__, '  . Authenticating session' );

			//	Not logged in? Authenticate!
			if ( PS::_ig() && ! CPSFacebookUser::authenticateUser( $this->_facebookApi, $this ) )
				throw new Exception( 'Invalid session' );

CPSLog::trace( __METHOD__, '  . Loading user' );

			//	Log into system...
			$this->_me = $this->_facebookApi->api( '/me' );
			$this->_fbUserId = $this->_session['uid'];
			$this->_accessToken = $this->_session['access_token'];
			$this->_firstName = PS::o( $this->_me, 'first_name' );
			$this->_loadUser();

			if ( $this->_autoLoadPictures )
			{
CPSLog::trace( __METHOD__, '  . Loading albums' );

				CPSFacebook::$_photoList = $this->getAlbums();

				if ( empty( CPSFacebook::$_photoList ) )
					PS::_rs( '_psAutoLoadPictures', '$(function(){$.get("/app/photos",function(){});});', CClientScript::POS_END );
			}

			return $this->_isConnected = true;
		}
		catch ( Exception $_ex )
		{
			CPSLog::error( __METHOD__, 'FB Exception: ' . $_ex->getMessage() );

			$this->_inForceLogin = true;
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
		$_model = call_user_func( array( $this->_userModelClass, 'model' ) );

		$_user = $_model->find( array(
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
			$_user = get_class( $_model );
			$_user->pform_user_id_text = $this->_fbUserId;
			$_user->pform_type_code = 1000;
			$_user->app_add_date = date( 'Y-m-d h:i:s' );
			$_user->app_del_date = null;
		}

		//	Set new stuff
		$_freshness = ( $_user->session_key_text != $this->_accessToken );

		$_user->session_key_text = $this->_accessToken;
		$_user->last_visit_date = date( 'Y-m-d h:i:s' );

		//	User installed app this time?
		if ( '1' == PS::o( $_REQUEST, 'installed' ) )
		{
			$_user->app_add_date = date( 'Y-m-d h:i:s' );
			$_user->app_del_date = null;
			$_user->app_add_count_nbr += 1;

//			//	Invite friends?
//			if ( $this->_inviteAfterInstall )
//			{
//				$this->redirect( 'inviteFriends', true, 301 );
//				return false;
//			}
		}

		if ( $this->_me )
		{
			PS::_ss( CPSFacebookAppController::ME_CACHE, $this->_me );
			$_user->first_name_text = $this->_firstName;
			$_user->last_name_text = PS::o( $this->_me, 'last_name' );
			$_user->email_addr_text = PS::o( $this->_me, 'email' );
			$_user->full_name_text = $this->_firstName . ' ' . strtoupper( substr( PS::o( $this->_me, 'last_name' ), 0, 1 ) . '.' );
		}

		//	Clear all the caches
		if ( $_freshness )
		{
			PS::_ss( CPSFacebookAppController::THUMB_CACHE, null );
			PS::_ss( CPSFacebookAppController::FRIEND_CACHE, null );
			PS::_ss( CPSFacebookAppController::APP_FRIEND_CACHE, null );
			PS::_ss( CPSFacebookAppController::PROFILE_CACHE, null );
			PS::_ss( CPSFacebookAppController::FL_CACHE, null );
		}

		//	Load app friends
		$this->_getAppFriends();

		//	Save info...
		$_user->save();

		//	Stow da ting mon
		$this->_id = $_user->id;

		//	Set our current user...
		PS::_ss( 'currentUser', $this->_user = $_user );
		PS::_ss( 'currentIdentity', $this );

		//	Raise the facebook login event in our controller
		if ( $_freshness )
			PS::_gc()->onFacebookLogin( new CEvent( $_user ) );

		return true;
	}

	/**
	 * Get all friend data
	 * @return array
	 */
	protected function _getAllFriends()
	{
		$this->_friendList = PS::_gs( CPSFacebookAppController::FRIEND_CACHE );

		try
		{
			if ( empty( $this->_friendList ) )
			{
				$_fql = "SELECT uid, name, first_name, pic_big, pic_square FROM user WHERE uid IN ( SELECT uid2 FROM friend where uid1 = '{$this->_fbUserId}' ) order by name";
				$this->_friendList = $this->_facebookApi->api( array( 'method' => 'fql.query', 'query' => $_fql ) );
				PS::_ss( CPSFacebookAppController::FRIEND_CACHE, $this->_friendList );
			}
		}
		catch ( Exception $_ex )
		{
			CPSLog::error( __METHOD__, 'Exception getAllFriends: ' . $_ex->getMessage() );
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
		$this->_appFriendList = ( $this->_cacheAppFriends ? PS::_gs( CPSFacebookAppController::APP_FRIEND_CACHE ) : null );

		try
		{
			if ( empty( $this->_appFriendList ) )
			{
				$_fql = "select uid from user where is_app_user = '1' and uid in ( select uid2 from friend where uid1 = '{$this->_fbUserId}' ) order by name";
				$_list = $this->_facebookApi->api( array( 'method' => 'fql.query', 'query' => $_fql ) );

				if ( defined( 'PYE_TRACE_LEVEL' ) && PYE_TRACE_LEVEL > 3 )
					CPSLog::trace( __METHOD__, '  - App Friend List Retrieved: ' . print_r( $_list, true ) );

				//	Make into a list of uids...
				foreach ( $_list as $_friend )
				{
					if ( ! empty( $_friend['uid'] ) )
						$this->_appFriendList[] = '\'' . $_friend['uid'] . '\'';
				}

				if ( $this->_cacheAppFriends )
					PS::_ss( CPSFacebookAppController::APP_FRIEND_CACHE, $this->_appFriendList );
			}
		}
		catch ( Exception $_ex )
		{
			CPSLog::error( __METHOD__, 'Exception: ' . $_ex->getMessage() );
			$this->_forceLogin();
		}

		return $this->_appFriendList;
	}

	/**
	 * Fills the album cache
	 * @param string The album ID to return, null for all
	 */
	public function getAlbums( $id = null )
	{
		static $_inProgress = false;

		$_model = call_user_func( array( $this->_userModelClass, 'model' ) );

		if ( null !== ( $_user = $_model->find( ':pform_user_id_text = pform_user_id_text', array( ':pform_user_id_text' => $this->_fbUserId ) ) ) )
			CPSFacebook::$_photoList = json_decode( $_user->photo_cache_text, true );

		if ( $_inProgress ) return CPSFacebook::$_photoList;

		if ( empty( CPSFacebook::$_photoList ) )
		{
			CPSLog::trace( __METHOD__, 'Reloading photo cache...' );
			$_inProgress = true;
			CPSFacebook::$_photoList = array();

			try
			{
				$_result = $this->_facebookApi->api( '/me/albums' );
				if ( null != ( CPSFacebook::$_photoList = PS::o( $_result, 'data' ) ) )
				{
					$_result = array();

					foreach ( CPSFacebook::$_photoList as $_key => $_album )
					{
						CPSFacebook::$_photoList[$_key]['photos'] = $this->getPhotos( $_album['id'] );

						if ( ! empty( CPSFacebook::$_photoList[$_key]['photos'] ) )
						{
							foreach ( CPSFacebook::$_photoList[$_key]['photos'] as $_photo )
							{
								if ( isset( $_photo['picture'] ) )
								{
									CPSFacebook::$_photoList[$_key]['picture'] = $_photo['picture'];
									break;
								}
							}
						}
					}

					CPSLog::trace( __METHOD__, 'Saving photos to user table cache...' );
					$_user->photo_cache_text = json_encode( CPSFacebook::$_photoList );
					$_user->update( array( 'photo_cache_text' ) );
				}
			}
			catch ( Exception $_ex )
			{
				CPSLog::error( __METHOD__, 'Exception: ' . $_ex->getMessage() );
			}
		}

		$_inProgress = false;
		return CPSFacebook::$_photoList;
	}

	/**
	 * Retrieves photos or a photo
	 * @param string The album ID
	 * @param string The photo ID or null for all photos in the album
	 */
	public function getPhotos( $aid, $id = null, $limit = null )
	{
		static $_recursed = false;

		if ( null == $aid )
			return null;

		if ( null == CPSFacebook::$_photoList )
			$this->getAlbums();

		if ( isset( CPSFacebook::$_photoList, CPSFacebook::$_photoList[$aid], CPSFacebook::$_photoList[$aid]['photos'] ) )
		{
			CPSFacebook::$_photoList = CPSFacebook::$_photoList[$aid]['photos'];
			if ( ! empty( $id ) ) return PS::o( CPSFacebook::$_photoList, $id );
		}

		//	Not there, grab photos and cache...
		$_parameterList = array();
		if ( null != $limit ) $_parameterList['limit'] = $limit;

		$_url = '/' . $aid . '/photos';
		$_resultList = array();

		while ( true )
		{
			try
			{
				$_tempList = $this->_facebookApi->api( $_url, $_parameterList );
			}
			catch ( Exception $_ex )
			{
				break;
			}

			if ( $_tempList )
			{
				$_resultList = array_merge( $_resultList, PS::o( $_tempList, 'data', array() ) );

				if ( null != $limit || null === ( $_url = PS::oo( $_tempList, 'paging', 'next' ) ) )
					break;
			}
			else
				break;
		}

		return $_resultList;
	}

	/**
	 * Forces a redirect to the Facebook login url...
	 */
	protected function _forceLogin()
	{
		CPSLog::info( __METHOD__, 'Force logging out user, no session found.' );

		//	If this is a deauth, clean up the database...
		if ( false !== strpos( $_SERVER['REQUEST_URI'], 'app/deauthorize' ) )
		{
			if ( $this->_loadUser() )
				PS::redirect( 'deauthorize', array( 'id' => $this->_user->id ) );
		}

		//	Logout user...
		PS::_gu()->logout();

		CPSLog::trace( __METHOD__, 'Facebook login redirect: ' . $this->_loginUrl );

//		echo 'redirect';//<script type="text/javascript">window.top.location.href = "' . $this->_loginUrl . '";</script>';
		echo '<script type="text/javascript">window.top.location.href = "' . $this->_loginUrl . '";</script>';
//		Yii::app()->end();
	}

	//********************************************************************************
	//* Events
	//********************************************************************************

	/**
	* Raises the onLoginFailure event
	* @param CPSAuthenticationEvent $event
	*/
	public function onLoginFailure( CPSAuthenticationEvent $event )
	{
		$this->raiseEvent( 'onLoginFailure', $event );
	}

	/**
	* Raises the onLoginSuccess event
	* @param CPSAuthenticationEvent $event
	*/
	public function onLoginSuccess( CPSAuthenticationEvent $event )
	{
		$this->raiseEvent( 'onLoginSuccess', $event );
	}

}

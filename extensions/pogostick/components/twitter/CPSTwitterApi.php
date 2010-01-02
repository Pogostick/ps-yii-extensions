<?php
/*
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * CPSTwitterApi provides access to the {@link http://apiwiki.twitter.com Twitter API}
 * 
 * @package 	psYiiExtensions.components
 * @subpackage 	twitter
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id$
 * @since 		v1.0.3
 * 
 * @filesource
 * 
 * @todo Implement Direct Message API
 * @todo Implement Block APIs
 * @todo Implement Trends API
 * @todo Complete Search API integdration
 */
class CPSTwitterApi extends CPSOAuthComponent
{
	//********************************************************************************
	//* Constants
	//********************************************************************************

	const STATUS_API = 'statuses';
	const USER_API = 'users';
	const DIRECTMESSAGE_API = 'direct_message';
	const FRIENDSHIP_API = 'friendships';
	const FRIEND_API = 'friends';
	const FOLLOWER_API = 'followers';
	const ACCOUNT_API = 'account';
	const FAVORITE_API = 'favorites';
	const NOTIFICATION_API = 'notifications';
	const BLOCK_API = 'blocks';
	const TRENDS_API = 'trends';
	const SEARCH_API = 'search';

	//********************************************************************************
	//* Construct
	//********************************************************************************

	/**
	* Preinitialize
	*/
	public function preinit()
	{
		//	Phone home...
		parent::preinit();

		//	Add ours...
		$this->addOptions( self::getBaseOptions() );
	}

	/**
	* Add our options
	*/
	private function getBaseOptions()
	{
		return(
			array(
				//	Required settings
				'userId' => 'string:',
				'screenName' => 'string:',
			)
		);
	}

	//********************************************************************************
	//* Yii Overrides
	//********************************************************************************

	public function init()
	{
		//	Call daddy
		parent::init();

		//	Set current twitter api url
		if ( ! $this->apiBaseUrl )
			$this->apiBaseUrl = 'http://twitter.com';
		
		//	Create the base array
		$this->requestMap = array();

		//********************************************************************************
		//* Statuses API
		//********************************************************************************
		
		$this->addTwitterRequestMapping( 'public_timeline',                                                      
			null,
			null,
			self::STATUS_API
		);

		$this->addTwitterRequestMapping( 'friends_timeline',
			array(
				'since_id' => false,
				'max_id' => false,
				'count' => false,
				'page' => false,
			),
			array( '_requireAuth' => true )
		);

		$this->addTwitterRequestMapping( 'user_timeline',
			array(
				'id' => false,
				'user_id' => false,
				'screen_name' => false,
				'since_id' => false,
				'max_id' => false,
				'count' => false,
				'page' => false,
			),
			array( 
				'_requireAuth' => true,
				'_requireOneOf' => array( 'id', 'user_id', 'screen_name' ),
			)
		);

		$this->addTwitterRequestMapping( 'mentions',
			array(
				'since_id' => false,
				'max_id' => false,
				'count' => false,
				'page' => false,
			),
			array( '_requireAuth' => true )
		);

		$this->addTwitterRequestMapping( 'show',
			array(
				'id' => false,
				'user_id' => false,
				'screen_name' => false,
			),
			array( 
				'_requireOneOf' => array( 'id', 'user_id', 'screen_name' ),
			)
		);

		$this->addTwitterRequestMapping( 'update',
			array(
				'status' => true,
				'in_reply_to_status_id' => false
			),
			array(
				'_method' => CPSApiBehavior::HTTP_POST,
			)
		);

		$this->addTwitterRequestMapping( 'destroy',
			array(
				'id' => true
			),
			array(
				'_method' => CPSApiBehavior::HTTP_POST,
			)
		);

		$this->addTwitterRequestMapping( 'friends',
			array(
				'id' => false,
				'user_id' => false,
				'screen_name' => false,
				'page' => false,
			)
		);

		$this->addTwitterRequestMapping( 'followers',
			array(
				'id' => false,
				'user_id' => false,
				'screen_name' => false,
				'page' => false,
			),
			array( '_requireAuth' => true )
		);

		//********************************************************************************
		//* Users API
		//********************************************************************************

		$this->addTwitterRequestMapping( 'show',
			array(
				'id' => false,
				'user_id' => false,
				'screen_name' => false,
			),
			array(
				'_requireOneOf' => array( 'id', 'user_id', 'screen_name' ),
			),
			self::USER_API
		);

		//********************************************************************************
		//* Friendship API
		//********************************************************************************

		$this->addTwitterRequestMapping( 'create',
			array(
				'id' => false,
				'user_id' => false,
				'screen_name' => false,
				'follow' => false,
			),
			array(
				'_method' => CPSApiBehavior::HTTP_POST,
				'_requireOneOf' => array( 'id', 'user_id', 'screen_name' ),
				'_requireAuth' => true,
			),
			self::FRIENDSHIP_API
		);

		$this->addTwitterRequestMapping( 'destroy',
			array(
				'id' => false,
				'user_id' => false,
				'screen_name' => false,
			),
			array(
				'_method' => CPSApiBehavior::HTTP_POST,
				'_requireOneOf' => array( 'id', 'user_id', 'screen_name' ),
				'_requireAuth' => true,
			),
			self::FRIENDSHIP_API
		);

		$this->addTwitterRequestMapping( 'exists',
			array(
				'user_a' => true,
				'user_b' => true,
			),
			array( '_requireAuth' => true ),
			self::FRIENDSHIP_API
		);

		//********************************************************************************
		//* Friend API
		//********************************************************************************

		$this->addTwitterRequestMapping( 'ids',
			array(
				'id' => false,
				'user_id' => false,
				'screen_name' => false,
				'page' => false,
			),
			array(
				'_requireOneOf' => array( 'id', 'user_id', 'screen_name' ),
			),
			self::FRIEND_API
		);

		//********************************************************************************
		//* Follower API
		//********************************************************************************

		$this->addTwitterRequestMapping( 'ids',
			array(
				'id' => false,
				'user_id' => false,
				'screen_name' => false,
				'page' => false,
			),
			array(
				'_requireOneOf' => array( 'id', 'user_id', 'screen_name' ),
			),
			self::FOLLOWER_API
		);

		//********************************************************************************
		//* Account API
		//********************************************************************************

		$this->addTwitterRequestMapping( 'verify_credentials',
			null,
			array( '_requireAuth' => true ),
			self::ACCOUNT_API
		);

		$this->addTwitterRequestMapping( 'rate_limit_status',
			null,
			array( '_requireAuth' => true )
		 );

		$this->addTwitterRequestMapping( 'end_session',
			null,
			array(
				'_method' => CPSApiBehavior::HTTP_POST,
				'_requireAuth' => true
			)
		 );

		$this->addTwitterRequestMapping( 'update_delivery_device',
			array(
				'device' => true,
			),
			array(
				'_method' => CPSApiBehavior::HTTP_POST,
				'_requireAuth' => true
			)
		);

		$this->addTwitterRequestMapping( 'update_profile_colors',
			array(
				'profile_background_color' => false,
				'profile_text_color' => false,
				'profile_link_color' => false,
				'profile_sidebar_fill_color' => false,
				'profile_sidebar_border_color' => false,
			),
			array(
				'_method' => CPSApiBehavior::HTTP_POST,
				'_requireOneOf' => array( 'profile_background_color', 'profile_text_color', 'profile_link_color', 'profile_sidebar_fill_color', 'profile_sidebar_border_color' ),
				'_requireAuth' => true,
			)
		);

		$this->addTwitterRequestMapping( 'update_profile_image',
			array(
				'image' => true,
			),
			array(
				'_method' => CPSApiBehavior::HTTP_POST,
				'_requireAuth' => true,
			)
		);

		$this->addTwitterRequestMapping( 'update_profile_background_image',
			array(
				'image' => true,
				'tile' => false,
			),
			array(
				'_method' => CPSApiBehavior::HTTP_POST,
				'_requireAuth' => true,
			)
		);

		$this->addTwitterRequestMapping( 'update_profile',
			array(
				'name' => false,
				'email' => false,
				'url' => false,
				'location' => false,
				'description' => false,
			),
			array(
				'_method' => CPSApiBehavior::HTTP_POST,
				'_requireOneOf' => array( 'name', 'email', 'url', 'location', 'description' ),
				'_requireAuth' => true,
			)
		);

		//********************************************************************************
		//* Favorite API
		//********************************************************************************

		$this->addTwitterRequestMapping( '/',
			array(
				'id' => false,
				'page' => false,
			),
			array(
				'_requireAuth' => true,
			),
			self::FAVORITE_API
		);

		$this->addTwitterRequestMapping( 'create',
			array(
				'id' => true,
			),
			array(
				'_method' => CPSApiBehavior::HTTP_POST,
				'_requireAuth' => true,
			)
		);

		$this->addTwitterRequestMapping( 'destroy',
			array(
				'id' => true,
			),
			array(
				'_method' => CPSApiBehavior::HTTP_POST,
				'_requireAuth' => true,
			)
		);

		//********************************************************************************
		//* Notification API
		//********************************************************************************

		$this->addTwitterRequestMapping( 'follow',
			array(
				'id' => false,
				'user_id' => false,
				'screen_name' => false,
			),
			array(
				'_method' => CPSApiBehavior::HTTP_POST,
				'_requireOneOf' => array( 'id', 'user_id', 'screen_name' ),
				'_requireAuth' => true,
			),
			self::NOTIFICATION_API
		);

		$this->addTwitterRequestMapping( 'leave',
			array(
				'id' => false,
				'user_id' => false,
				'screen_name' => false,
			),
			array(
				'_method' => CPSApiBehavior::HTTP_POST,
				'_requireOneOf' => array( 'id', 'user_id', 'screen_name' ),
				'_requireAuth' => true,
			)
		);
		
		//********************************************************************************
		//* Search API
		//********************************************************************************

		$this->addTwitterRequestMapping( '/',
			array(
				'callback' => false,
				'lang' => false,
				'rpp' => false,
				'page' => false,
				'since_id' => false,
				'geocode' => false,
				'show_user' => false,
				'q' => true,
			),
			null,
			self::SEARCH_API
		);
		
	}

	//********************************************************************************
	//* Public methods
	//********************************************************************************

	/**
	* Loads data into the component from an alternate source (i.e. database, cookie, etc.)
	* 
	* @param string $sUserId The Twitter user_id
	* @param string $sScreenName The Twitter screen_name
	* @param boolean $bAuthorized Is user authorized?
	* @param array $arToken The access token
	*/
	public function loadData( $sUserId = null, $sScreenName = null, $bAuthorized = false, $arToken = array() )
	{
		$this->userId = $sUserId;
		$this->screenName = $sScreenName;
		$this->isAuthorized = $bAuthorized;
		$this->storeToken( $arToken );
	}

	//********************************************************************************
	//* Twitter API Public Access Methods
	//********************************************************************************

	/**
	* Returns the 20 most recent statuses from non-protected users who have set a custom user icon. 
	* The public timeline is cached for 60 seconds so requesting it more often than that is a waste of resources.
	* 
	* @return mixed
	*/
	public function getPublicTimeline()
	{
		$this->apiToUse = self::STATUS_API;
		return $this->makeRequest( 'public_timeline' );
	}

	/**
	* Returns the 20 most recent statuses posted by the authenticating user and that user's friends. 
	* This is the equivalent of /timeline/home on the Web.
	* 
	* @param string $sSinceId
	* @param string $sMaxId
	* @param integer $iCount
	* @param integer $iPage
	* @return mixed
	*/
	public function getFriendsTimeline( $sSinceId = null, $sMaxId = null, $iCount = null, $iPage = null )
	{
		$this->apiToUse = self::STATUS_API;
		return $this->makeRequest( 'friends_timeline', array( 'since_id' => $sSinceId, 'max_id' => $sMaxId, 'count' => $iCount, 'page' => $iPage ) );
	}

	/**
	* Returns the 20 most recent statuses posted from the authenticating user. 
	* It's also possible to request another user's timeline via the id parameter. 
	* This is the equivalent of the Web /<user> page for your own user, or the profile 
	* page for a third party
	* 
	* @param string $sId
	* @param string $sUserId
	* @param string $sScreenName
	* @param string $sSinceId
	* @param string $sMaxId
	* @param integer $iCount
	* @param integer $iPage
	* @return mixed
	*/
	public function getUserTimeline( $sId = null, $sUserId = null, $sScreenName = null, $sSinceId = null, $sMaxId = null, $iCount = null, $iPage = null )
	{
		$this->apiToUse = self::STATUS_API;
		return $this->makeRequest( 'user_timeline', array( 'id' => $sId, 'user_id' => $sUserId, 'screen_name' => $sScreenName, 'since_id' => $sSinceId, 'max_id' => $sMaxId, 'count' => $iCount, 'page' => $iPage ) );
	}

	/**
	* Returns the 20 most recent mentions (status containing @username) for the authenticating user.
	* 
	* @param string $sSinceId
	* @param string $sMaxId
	* @param integer $iCount
	* @param integer $iPage
	* @return mixed
	*/
	public function getMentions( $sSinceId = null, $sMaxId = null, $iCount = null, $iPage = null )
	{
		$this->apiToUse = self::STATUS_API;
		return $this->makeRequest( 'mentions', array( 'since_id' => $sSinceId, 'max_id' => $sMaxId, 'count' => $iCount, 'page' => $iPage ) );
	}

	/**
	* Returns extended information of a given user, specified by ID or screen name 
	* as per the required id parameter.  The author's most recent status will be returned inline.
	* 
	* @param string $sId
	* @param string $sUserId
	* @param string $sScreenName
	* @return mixed
	*/
	public function getExtendedUserInfo( $sId = null, $sUserId = null, $sScreenName = null )
	{
		$this->apiToUse = self::USER_API;
		return $this->makeRequest( 'show', array( 'id' => $sId, 'user_id' => $sUserId, 'screen_name' => $sScreenName ) );
	}

	/**
	* Returns a single status, specified by the id parameter below.  
	* The status's author will be returned inline.
	* 
	* @param string $sId
	* @return mixed
	*/
	public function getStatus( $sId )
	{
		$this->apiToUse = self::STATUS_API;
		return $this->makeRequest( 'show', array( 'id' => $sId ) );
	}

	/**
	* Updates the authenticating user's status.  
	* Requires the status parameter specified below.  Request must be a POST.  
	* A status update with text identical to the authenticating user's current 
	* status will be ignored to prevent duplicates
	* 
	* @param string $sStatus
	* @param string $sReplyId
	* @return mixed
	*/
	public function updateStatus( $sStatus, $sReplyId = null )
	{
		$this->apiToUse = self::STATUS_API;
		return $this->makeRequest( 'update', array( 'status' => $sStatus, 'in_reply_to_status_id' => $sReplyId ) );
	}

	/**
	* Destroys the status specified by the required ID parameter.  
	* The authenticating user must be the author of the specified status
	* 
	* @param string $sUpdateId
	* @return mixed
	*/
	public function removeStatus( $sUpdateId )
	{
		$this->apiToUse = self::STATUS_API;
		return $this->makeRequest( 'destroy', array( 'id' => $sUpdateId ) );
	}

	/**
	* Returns a user's friends, each with current status inline. 
	* They are ordered by the order in which they were added as friends. 
	* Defaults to the authenticated user's friends. 
	* It's also possible to request another user's friends list via the $sId parameter.
	* 
	* @param string $sId
	* @param string $sUserId
	* @param string $sScreenName
	* @param integer $iPage
	* @return mixed
	*/
	public function getFriends( $sId = null, $sUserId = null, $sScreenName = null, $iPage = null )
	{
		$this->apiToUse = self::STATUS_API;
		return $this->makeRequest( 'friends', array( 'id' => $sId, 'user_id' => $sUserId, 'screen_name' => $sScreenName, 'page' => $iPage ) );
	}

	/**
	* Returns the authenticating user's followers, each with current status inline.  
	* They are ordered by the order in which they joined Twitter
	* 
	* @param string $sId
	* @param string $sUserId
	* @param string $sScreenName
	* @param integer $iPage
	* @return mixed
	*/
	public function getFollowers( $sId = null, $sUserId = null, $sScreenName = null, $iPage = null )
	{
		$this->apiToUse = self::STATUS_API;
		return $this->makeRequest( 'followers', array( 'id' => $sId, 'user_id' => $sUserId, 'screen_name' => $sScreenName, 'page' => $iPage ) );
	}

	/**
	* Allows the authenticating users to follow the user specified in the $sId parameter.  
	* Returns the befriended user in the requested format when successful.  
	* Returns a string describing the failure condition when unsuccessful.
	* 
	* @param string $sId
	* @param string $sUserId
	* @param string $sScreenName
	* @param boolean $bEnableNotifications
	* @return mixed
	*/
	public function followUser( $sId = null, $sUserId = null, $sScreenName = null, $bEnableNotifications = false )
	{
		$this->apiToUse = self::FRIENDSHIP_API;
		return $this->makeRequest( 'create', array( 'id' => $sId, 'user_id' => $sUserId, 'screen_name' => $sScreenName, 'follow' => $bEnableNotifications ) );
	}

	/**
	* Allows the authenticating users to unfollow the user specified in the $sId parameter.  
	* Returns the unfollowed user in the requested format when successful.  
	* Returns a string describing the failure condition when unsuccessful.
	* 
	* @param string $sId
	* @param string $sUserId
	* @param string $sScreenName
	* @return mixed
	*/
	public function unfollowUser( $sId = null, $sUserId = null, $sScreenName = null )
	{
		$this->apiToUse = self::FRIENDSHIP_API;
		return $this->makeRequest( 'destroy', array( 'id' => $sId, 'user_id' => $sUserId, 'screen_name' => $sScreenName ) );
	}

	/**
	* Tests for the existence of friendship between two users. 
	* Will return true if $sFollowerId follows $sFolloweeId, otherwise will return false.
	* 
	* @param string $sFolloweeId The user to test
	* @param string $sFollowerId The other user to test. If null, authenticated user id is used
	* @return mixed
	*/
	public function isFollowing( $sFolloweeId, $sFollowerId = null )
	{
		$this->apiToUse = self::FRIENDSHIP_API;
		return $this->makeRequest( 'exists', array( 'user_a' => ( null == $sFollowerId ) ? $this->userId : $sFollowerId, 'user_b' => $sFolloweeId ) );
	}

	/***
	* Returns an array of numeric IDs for every user the specified user is following.
	* 
	* @param string $sId
	* @param string $sUserId
	* @param string $sScreenName
	* @param integer $iPage
	* @return mixed
	*/
	public function getFollowingList( $sId = null, $sUserId = null, $sScreenName = null, $iPage = null )
	{
		$this->apiToUse = self::FRIEND_API;
		return $this->makeRequest( 'ids', array( 'id' => $sId, 'user_id' => $sUserId, 'screen_name' => $sScreenName, 'page' => $iPage ) );
	}

	/**
	* Returns an array of users whom are following the authenticated user
	* 
	* @param string $sId
	* @param string $sUserId
	* @param string $sScreenName
	* @param integer $iPage
	* @return mixed
	*/
	public function getFollowerList( $sId = null, $sUserId = null, $sScreenName = null, $iPage = null )
	{
		$this->apiToUse = self::FOLLOWER_API;
		return $this->makeRequest( 'ids', array( 'id' => $sId, 'user_id' => $sUserId, 'screen_name' => $sScreenName, 'page' => $iPage ) );
	}
	
	/**
	* Returns an HTTP 200 OK response code and a representation of the requesting user 
	* if authentication was successful; returns a 401 status code and an error message if not.  
	* Use this method to test if supplied user credentials are valid.
	* 
	*/
	public function verifyCredentials()
	{
		$this->apiToUse = self::ACCOUNT_API;
		return $this->makeRequest( 'verify_credentials' );
	}

	/**
	* Returns the remaining number of API requests available to the requesting user before the 
	* API limit is reached for the current hour. Calls to this do not count against the rate limit.  
	* If authentication credentials are provided, the rate limit status for the authenticating user is returned.  
	* Otherwise, the rate limit status for the requester's IP address is returned. 
	* Learn more about the {@link http://apiwiki.twitter.com/Rate-limiting REST API rate limiting}.
	*/
	public function getRateLimitStatus()
	{
		$this->apiToUse = self::ACCOUNT_API;
		return $this->makeRequest( 'rate_limit_status' );
	}

	/**
	* Ends the session of the authenticating user, returning a null cookie.  
	* Use this method to sign users out of client-facing applications like widgets
	*/
	public function endSession()
	{
		$this->apiToUse = self::ACCOUNT_API;
		return $this->makeRequest( 'end_session' );
	}

	/**
	* 
	* @param string $sDevice
	* @return mixed
	*/
	public function updateDeliveryDevice( $sDevice )
	{
		$this->apiToUse = self::ACCOUNT_API;
		return $this->makeRequest( 'update_deliver_device', array( 'device' => $sDevice ) );
	}

	/**
	* 
	* @param string $sBG
	* @param string $sText
	* @param string $sLink
	* @param string $sSBFill
	* @param string $sSBBorder
	* @return mixed
	*/
	public function updateProfileColors( $sBG = null, $sText = null, $sLink = null, $sSBFill = null, $sSBBorder = null )
	{
		$this->apiToUse = self::ACCOUNT_API;
		return $this->makeRequest( 'update_profile_colors', array( 'profile_background_color' => $sBG, 'profile_text_color' => $sText, 'profile_link_color' => $sLink, 'profile_sidebar_fill_color' => $sSBFill, 'profile_sidebar_border_color' => $sSBBorder ) );
	}

	/**
	* 
	* @param string $sImageData
	* @return mixed
	*/
	public function updateProfileImage( $sImageData )
	{
		$this->apiToUse = self::ACCOUNT_API;
		return $this->makeRequest( 'update_profile_image', array( 'image' => $sImageData ) );
	}

	/**
	* 
	* @param string $sImageData
	* @param boolean $bTiled
	* @return mixed
	*/
	public function updateProfileBackgroundImage( $sImageData, $bTiled = false )
	{
		$this->apiToUse = self::ACCOUNT_API;
		return $this->makeRequest( 'update_profile_background_image', array( 'image' => $sImageData, 'tile' => $bTiled ) );
	}

	/**
	* 
	* @param string $sName
	* @param string $sEmail
	* @param string $sUrl
	* @param string $sLocation
	* @param string $sDescription
	* @return mixed
	*/
	public function updateProfile( $sName = null, $sEmail = null, $sUrl = null, $sLocation = null, $sDescription = null )
	{
		$this->apiToUse = self::ACCOUNT_API;
		return $this->makeRequest( 'update_profile', array( 'name' => $sName, 'email' => $sEmail, 'url' => $sUrl, 'location' => $sLocation, 'description' => $sDescription ) );
	}

	/**
	* 
	* @param string $sId
	* @param integer $iPage
	* @return mixed
	*/
	public function getFavorites( $sId = null, $iPage = null )
	{
		$this->apiToUse = self::FAVORITE_API;
		return $this->makeRequest( '/', array( 'id' => $sId, 'page' => $iPage ) );
	}

	/**
	* 
	* @param string $sStatusId
	* @return mixed
	*/
	public function addFavorite( $sStatusId )
	{
		$this->apiToUse = self::FAVORITE_API;
		return $this->makeRequest( 'create', array( 'id' => $sStatusId ) );
	}
	
	/**
	* 
	* @param string $sStatusId
	* @return mixed
	*/
	public function removeFavorite( $sStatusId )
	{
		$this->apiToUse = self::FAVORITE_API;
		return $this->makeRequest( 'destroy', array( 'id' => $sStatusId ) );
	}
	
	/**
	* Enables device notifications for updates from the specified user
	* 
	* @param string $sId
	* @param string $sUserId
	* @param string $sScreenName
	* @return mixed Returns the specified user when successful
	*/
	public function enableFollowNotifications( $sId = null, $sUserId = null, $sScreenName = null )
	{
		$this->apiToUse = self::NOTIFICATION_API;
		return $this->makeRequest( 'follow', array( 'id' => $sId, 'user_id' => $sUserId, 'screen_name' => $sScreenName ) );
	}
	
	/**
	* Disables notifications for updates from the specified user to the authenticating user
	* 
	* @param string $sId
	* @param string $sUserId
	* @param string $sScreenName
	* @return mixed Returns the specified user when successful
	*/
	public function disableFollowNotifications( $sId = null, $sUserId = null, $sScreenName = null )
	{
		$this->apiToUse = self::NOTIFICATION_API;
		return $this->makeRequest( 'leave', array( 'id' => $sId, 'user_id' => $sUserId, 'screen_name' => $sScreenName ) );
	}
	
	/**
	* Returns tweets that match a specified query
	* 
	* @param string $sTerm
	* @param string $sCallback
	* @param string $sLang
	* @param integer $iRPP
	* @param integer $iPage
	* @param string $sSinceId
	* @param string $sGeocode
	* @param boolean $bShowUser
	* @return mixed
	*/
	public function search( $sTerm, $sCallback = null, $sLang = null, $iRPP = null, $iPage = null, $sSinceId = null, $sGeocode = null, $bShowUser = false )
	{
		$this->apiToUse = self::SEARCH_API;
		return $this->makeRequest( '/', array( 'q' => $sTerm, 'callback' => $sCallback, 'lang' => $sLang, 'rpp' => $iRPP, 'page' => $iPage, 'since_id' => $sSinceId, 'geogode' => $sGeocode, 'show_user' => $bShowUser ) );
	}

	//********************************************************************************
	//* Private Methods
	//********************************************************************************

	/**
	* Adds a request to the requestMap array
	*
	* @param string $sAction The action to call
	* @param array $arParams The parameters for this request
	* @param array $arOptions The options for this request
	* @param string $sController The API to call
	* @return bool True if operation succeeded
	*/
	protected function addTwitterRequestMapping( $sAction, $arParams = null, $arOptions = null, $sController = null )
	{
		//	Save for next call
		static $_sLastController;

		//	Set up statics so next call can omit those parameters.
		if ( null != $sController && $sController != $_sLastController )
			$_sLastController = $sController;

		//	Add the mapping...
		if ( null == $_sLastController )
			return false;

		//	Add mapping...
		$_arOptions =& $this->getOptions();
		$_arOptions[ 'requestMap' ][ $_sLastController ][ $sAction ] = array( 'params' => $arParams, 'options' => $arOptions, 'controller' => $_sLastController );

		return true;
	}

}
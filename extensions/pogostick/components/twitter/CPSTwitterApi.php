<?php
/**
 * CPSTwitterApi class file.
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://ps-yii-extensions.googlecode.com
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @license http://www.pogostick.com/license/
 */

/**
 * CPSTwitterApi provides access to the Twitter API
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @version SVN: $Id$
 * @package psYiiExtensions
 * @subpackage Components
 * @since 1.0.0
 *
 * @todo Implement Direct Message API
 * @todo Implement Block API
 */
class CPSTwitterApi extends CPSApiComponent
{
	//********************************************************************************
	//* Constants
	//********************************************************************************

	const TWITTER_STATUS_API = 'statuses';
	const TWITTER_USER_API = 'users';
	const TWITTER_DIRECTMESSAGE_API = 'direct_message';
	const TWITTER_FRIENDSHIP_API = 'friendships';
	const TWITTER_FRIEND_API = 'friends';
	const TWITTER_FOLLOWER_API = 'followers';
	const TWITTER_ACCOUNT_API = 'account';
	const TWITTER_FAVORITE_API = 'favorites';
	const TWITTER_NOTIFICATION_API = 'notifications';
	const TWITTER_BLOCK_API = 'blocks';

	//********************************************************************************
	//* Yii Overrides
	//********************************************************************************

	public function init()
	{
		//	Call daddy
		parent::init();

		//	Create the base array
		$this->requestMap = array();

		//********************************************************************************
		//* Statuses API
		//********************************************************************************

		$this->addTwitterRequestMapping( 'public_timeline',
			null,
			null,
			self::TWITTER_STATUS_API
		);

		$this->addTwitterRequestMapping( 'friends_timeline',
			array(
				'since_id' => false,
				'max_id' => false,
				'count' => false,
				'page' => false,
			),
			null
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
			)
		);

		$this->addTwitterRequestMapping( 'mentions',
			array(
				'since_id' => false,
				'max_id' => false,
				'count' => false,
				'page' => false,
			)
		);

		$this->addTwitterRequestMapping( 'show',
			array( 'id' => true )
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
			)
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
			self::TWITTER_USER_API
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
			),
			self::TWITTER_FRIENDSHIP_API
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
			),
			self::TWITTER_FRIENDSHIP_API
		);

		$this->addTwitterRequestMapping( 'test',
			array(
				'user_a' => true,
				'user_b' => true,
			),
			null,
			self::TWITTER_FRIENDSHIP_API
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
			self::TWITTER_FRIEND_API
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
			self::TWITTER_FOLLOWER_API
		);

		//********************************************************************************
		//* Account API
		//********************************************************************************

		$this->addTwitterRequestMapping( 'verify_credentials',
			null,
			null,
			self::TWITTER_ACCOUNT_API
		);

		$this->addTwitterRequestMapping( 'rate_limit_status' );

		$this->addTwitterRequestMapping( 'end_session' );

		$this->addTwitterRequestMapping( 'update_delivery_device',
			array(
				'device' => true,
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
			)
		);

		$this->addTwitterRequestMapping( 'update_profile_image',
			array(
				'image' => true,
			),
			array(
				'_method' => CPSApiBehavior::HTTP_POST,
			)
		);

		$this->addTwitterRequestMapping( 'update_profile_background_image',
			array(
				'image' => true,
				'tile' => false,
			),
			array(
				'_method' => CPSApiBehavior::HTTP_POST,
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
			null,
			self::TWITTER_FAVORITE_API
		);

		$this->addTwitterRequestMapping( 'create',
			array(
				'id' => true,
			),
			array(
				'_method' => CPSApiBehavior::HTTP_POST,
			)
		);

		$this->addTwitterRequestMapping( 'destroy',
			array(
				'id' => true,
			),
			array(
				'_method' => CPSApiBehavior::HTTP_POST,
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
			),
			self::TWITTER_NOTIFICATION_API
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
			)
		);
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

	/**
	* Makes the actual HTTP request based on settings
	*
	* @param string $sAction
	* @param array $arRequestData
	* @return string
	*/
	public function makeRequest( $sAction, $arRequestData = null )
	{
		//	Default...
		$_arRequestData = $this->requestData;

		//	Check data...
		if ( null != $arRequestData )
			$_arRequestData = array_merge( $_arRequestData, $arRequestData );

		//	Check action...
		if ( ! empty( $sAction ) && is_array( $this->requestMap[ $this->apiToUse ] ) && ! array_key_exists( $sAction, $this->requestMap[ $this->apiToUse ] ) )
		{
			throw new CException(
				Yii::t(
					__CLASS__,
					'Invalid action "{subType}" specified for controller "{apiToUse}". Valid subtypes are "{subTypes}"',
					array(
						'{subType}' => $sAction,
						'{apiToUse}' => $this->apiToUse,
						'{subTypes}' => implode( ', ', array_keys( $this->requestMap[ $this->apiToUse ] ) )
					)
				)
			);
		}

		//	Add the request data to the Url...
		if ( is_array( $this->requestMap )  )
		{
			$_arMap = ( isset( $this->requestMap[ $this->apiToUse ][ $sAction ] ) ) ? $this->requestMap[ $this->apiToUse ][ $sAction ] : null;
			$_arOptions = ( isset( $_arMap[ 'options' ] ) ) ? $_arMap[ 'options' ] : null;
			$_arReqOneOf = ( isset( $_arOptions[ '_requireOneOf' ] ) ) ? $_arOptions[ '_requireOneOf' ] : null;
			$_sMethod = ( isset( $_arOptions[ '_method' ] ) ) ? $_arOptions[ '_method' ] : CPSApiBehavior::HTTP_GET;
			$_arParams = ( isset( $_arParams[ 'params' ] ) ) ? $_arParams[ 'params' ] : null;

			$_sQuery = '';
			$_bThere = ( null == $_arReqOneOf );

			//	Build our query
			if ( is_array( $_arParams ) )
			{
				foreach ( $_arParams as $_sKey => $_bRequired )
				{
					//	Check required items
					if ( null != $_arReqOneOf && ! $_bThere )
						$_bThere = in_array( $_sKey, $_arReqOneOf );

					if ( $_bRequired && ! isset( $_arRequestData[ $_sKey ] ) )
					{
						throw new CException(
							Yii::t(
								__CLASS__,
								'Required parameter {param} was not included in requestData',
								array(
									'{param}' => $_sKey,
								)
							)
						);
					}

					//	Add to query string if set...
					if ( isset( $_arRequestData[ $_sKey ] ) )
						$_sQuery .= "&{$_sKey}=" . urlencode( $_arRequestData[ $_sKey ] );
				}
			}
		}

		//	Check requireOneOf option...
		if ( ! $_bThere )
		{
			throw new CException(
				Yii::t(
					__CLASS__,
					'This call requires one of the following parameters: {params}',
					array(
						'{param}' => implode( ', ', $_requireOneOf ),
					)
				)
			);
		}

		//	Build the url...
		$_sUrl = $this->apiBaseUrl . '/' . $this->apiToUse . ( ( $sAction == '/' ) ? '' : '/' . $sAction ) . '.json';

		//	Strip off initial &amp;
		if ( $_sMethod != CPSApiBehavior::HTTP_POST )
			$_sQuery = substr( $_sQuery, 1 );

		//	Handle events...
		$_oEvent = new CPSApiEvent( $_sUrl, $_sQuery, null, $this );
		$this->beforeApiCall( $_oEvent );

		//	Ok, we've build our request, now let's get the results...
		$_sResults = $this->makeHttpRequest( $_sUrl, $_sQuery, $_sMethod, $this->userAgent );

		//	Handle events...
		$_oEvent->urlResults = $_sResults;
		$this->afterApiCall( $_oEvent );

		//	Raise our completion event...
		$_oEvent->setUrlResults( $_sResults );
		$this->requestComplete( $_oEvent );

		//	If user doesn't want JSON output, then reformat
		switch ( $this->format )
		{
			case 'xml':
				$_sResults = CPSHelp::arrayToXml( json_decode( $_sResults, true ), 'Results' );
				break;

			case 'array':
				$_sResults = json_decode( $_sResults, true );
				break;
		}

		//	Return results...
		return $_sResults;
	}

}
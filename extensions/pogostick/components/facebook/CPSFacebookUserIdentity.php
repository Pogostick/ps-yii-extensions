<?php
/*
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
	//* Private Members
	//********************************************************************************

	/**
	* @var string Our user id
	*/
	protected $_id = null;
	public function getId() { return $this->_id; }
	public function setId( $value ) { $this->_id = $value; }

	/**
	 * @var array The user's Facebook info
	 */
	protected $_me;
	public function getMe() { return $this->_me; }

	/**
	 * @var CPSFacebook An instance of our Facebook API object
	 */
	protected $_facebookApi;
	public function getFacebookApi() { return $this->_facebookApi; }

	/**
	 * @var string The Facebook ID of the logged in user
	 * @return string The Facebook ID of the logged in user
	 */
	public function getFBUserId() { return $this->_facebookApi->getFBUserId(); }

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	 * Constructor.
	 *
	 * @param CPSFacebook
	 * @param string password
	 */
	public function __construct( CPSFacebook $facebookApi )
	{
		$this->_facebookApi = $facebookApi;

		parent::__construct( null, null );

		Yii::import( 'pogostick.events.CPSAuthenticationEvent' );
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
			$_session = $this->_facebookApi->getSession();
			$this->_me = $this->_facebookApi->api( '/me' );

			if ( $_session && $this->_me )
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

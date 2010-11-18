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
	* Our Facebook user id
	*
	* @var int
	*/
	protected $_facebookId;
	public function getId() { return $this->_facebookId; }
	public function setId( $value ) { $this->_facebookId = $value; }
	
	protected $_facebookApi;
	public function getFacebookApi() { return $this->_facebookApi; }

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	 * Constructor.
	 *
	 * @param string user name
	 * @param string password
	 */
	public function __construct( $facebookApi )
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
			$_session = $this->_facebookApi->getFacebookApi()->getSession();
			$_me = $this->_facebookApi->getMe();
			
			if ( $_session && $_me )
			{
				$this->errorCode = self::ERROR_NONE;
				$this->username = trim( $_me['name'] );
				$this->setId( $this->_facebookApi->getFBUserId() );

				//	Raise our event
				$this->onLoginSuccess( new CPSAuthenticationEvent( $this, $_me, $this ) );
				
				return true;
			}
		}
		
		$this->onLoginFailure( new CPSAuthenticationEvent( $this, $_me, $this ) );

		return false;
	}

	//********************************************************************************
	//* Events
	//********************************************************************************

	/**
	* Raises the onLoginFailure event
	* @param CPSAuthenticationEvent $oEvent
	*/
	public function onLoginFailure( CPSAuthenticationEvent $oEvent )
	{
		$this->raiseEvent( 'onLoginFailure', $oEvent );
	}

	/**
	* Raises the onLoginSuccess event
	* @param CPSAuthenticationEvent $oEvent
	*/
	public function onLoginSuccess( CPSAuthenticationEvent $oEvent )
	{
		$this->raiseEvent( 'onLoginSuccess', $oEvent );
	}

}

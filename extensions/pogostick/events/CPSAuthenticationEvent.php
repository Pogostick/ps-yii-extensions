<?php
/**
 * This file is part of the psYiiExtensions package.
 *
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * CPSAuthenticationEvent provides specialized events for {@link CPSUserIdentity}
 * Introduces two new events:
 *
 * onLoginFailure
 * onLoginSuccess
 *
 * @package 	psYiiExtensions
 * @subpackage 	events
 *
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id: CPSAuthenticationEvent.php 374 2010-03-15 21:46:54Z jerryablan@gmail.com $
 * @since 		v1.0.6
 *
 * @filesource
 */
class CPSAuthenticationEvent extends CEvent
{
	//********************************************************************************
	//* Members
	//********************************************************************************

	/**
	* The user
	*
	* @var CWebUser
	*/
	protected $_user;

	/**
	* The identity
	*
	* @var IUserIdentity
	*/
	protected $_userIdentity;

	//********************************************************************************
	//* Property Accessors
	//********************************************************************************

	//	These are all read-only

	public function getUser() { return $this->_user; }
	public function getUserIdentity() { return $this->_userIdentity; }

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	* Constructor
	*
	* @param mixed $sender
	* @param CWebUser
	* @param IUserIdentity
	*
	* @return CPSAuthenticationEvent
	*/
	public function __construct( $sender, $user, $userIdentity )
	{
		parent::__construct( $sender );

		$this->_user = $user;
		$this->_userIdentity = $userIdentity;
	}

}
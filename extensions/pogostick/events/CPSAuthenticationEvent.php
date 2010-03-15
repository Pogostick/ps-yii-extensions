<?php
/*
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright &copy; 2009 Pogostick, LLC
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
 * @version 	SVN: $Id$
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
	protected $m_oUser;

	/**
	* The identity
	*
	* @var IUserIdentity
	*/
	protected $m_oIdent;

	//********************************************************************************
	//* Property Accessors
	//********************************************************************************

	//	These are all read-only

	public function getUser() { return $this->m_oUser; }
	public function getUserIdentity() { return $this->m_oIdent; }

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
	public function __construct( $oSender, $oUser, $oUserIdentity )
	{
		parent::__construct( $oSender );

		$this->m_oUser = $oUser;
		$this->m_oIdent = $oUserIdentity;
	}

}
<?php
/**
 * This file is part of SnowFrame(tm)
 * 
 * @copyright Copyright &copy; 2009-2010 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 * 
 */

/**
 * @package 	snowframe
 * @subpackage 	base
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN $Id: CSFPlatformUser.php 388 2010-06-13 16:26:43Z jerryablan@gmail.com $
 * @filesource
 */
class CSFPlatformUser extends CUserIdentity
{
	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	/**
	* Our user's uid
	* @var string
	*/
	protected $m_sUserId = null;
	public function getId() { return $this->m_iUserId; }

	/**
	 * Validates the user.
	 * 
	 * @param mixed $bRequireFrame
	 * @param mixed $bRequireAdd
	 * @param mixed $bRequireLogin
	 * @param mixed $bUsePrefs
	 * @param mixed $bAddUser
	 */
	public function validateUser( $oApi, $arOptions = array() )
	{
		$_bRequireFrame = PS::o( $arOptions, 'requireFrame', true );
		$_bRequireAdd = PS::o( $arOptions, 'requireAdd', true );
		$_bRequireLogin = PS::o( $arOptions, 'requireLogin', true );
		$_bUsePrefs = PS::o( $arOptions, 'usePrefs', true );
		$_bAddUser = PS::o( $arOptions, 'addUser', true );

		$oApi->validateUser( $_bRequireFrame, $_bRequireAdd, $_bRequireLogin, $_bUsePrefs, $_bAddUser );

		//	Let the user database have a look
		if ( $this->m_oUserDB && method_exists( $this->m_oUserDB, "processQueryString" ) )
			$this->m_oUserDB->processQueryString( $this->pfApi->PFUserId );
	}

	/**
	 * Authenticates a user.
	 * @return boolean whether authentication succeeds.
	 */
	public function authenticate()
	{
		$_sUserName = trim( strtolower( $this->username ) );

		//	User name match?
		$_bFound = ( null != ( $_oUser = Users::model()->find( 'username = :username', array( ':username' => $_sUserName ) ) ) );
		
		//	Nope? Try email address lookup
		if ( ! $_bFound )
			$_bFound = ( null != ( $_oUser = Users::model()->find( 'email = :email', array( ':email' => $_sUserName ) ) ) );

		//	Bah! 
		if ( ! $_bFound || trim( strtolower( $this->password ) ) != trim( strtolower( $_oUser->password ) ) )
			$this->errorCode = self::ERROR_USERNAME_INVALID;
		else
		{
			$this->m_iUserId = $_oUser->id;
			$this->username = $_oUser->username;
			$this->setState( 'fullName', trim( $_oUser->first_name . ' ' . $_oUser->last_name ) );
			$this->setState( 'firstName', $_oUser->first_name );
			$this->setState( 'lastName', $_oUser->last_name );
			$this->setState( 'accessRole', 'player' );
			$this->clearState( 'adminLevel' );
			$_oUser->touch( 'last_login_date' );
			$this->errorCode = self::ERROR_NONE;
		}

		return( !$this->errorCode );
	}
}

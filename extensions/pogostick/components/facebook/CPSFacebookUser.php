<?php
/*
 * CPSFacebookUser.php
 *
 * Copyright (c) 2010 Jerry Ablan <jablan@pogostick.com>.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 *
 * This file is part of the Pogostick Yii Extensions.
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
//	Constants
//	Global Settings

/**
 * CPSFacebookUser
 *
 * Represents a Facebook user
 *
 * @package 	psYiiExtensions
 * @subpackage 	components
 *
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN $Id$
 * @since 		v1.0.0
 *
 * @filesource
 */
class CPSFacebookUser extends CWebUser
{
	//********************************************************************************
	//* Member variables
	//********************************************************************************

	/**
	* @var array A copy of the user data
	*/
	protected $_me = null;
	public function getMe() { return $this->_me; }

	/**
	* @var User A copy of the user data
	*/
	protected $_user = null;
	public function getUser() { return $this->_user; }

	/**
	* @var array A copy of the user session
	*/
	protected $_fbSession = null;
	public function getFBSession() { return $this->_fbSession; }

	/**
	* @var array A copy of the user session
	*/
	protected $_fbApi = null;
	public function getFBApi() { return $this->_fbApi; }

	//********************************************************************************
	//* Public Methods
	//********************************************************************************
	
	/**
	 * Authenticate a facebook user
	 * @param CPSFacebookAppController $fbApi
	 * @return boolean 
	 */
	public static function authenticateUser( CPSFacebookAppController $fbApi )
	{
		$_result = false;
		$_identity = new CPSFacebookUserIdentity( $fbApi );
		if ( $_identity->authenticate() && CUserIdentity::ERROR_NONE == $_identity->errorCode )
		{
			Yii::app()->user->allowAutoLogin = false;
			Yii::app()->user->login( $_identity, 0 );
			$_result = true;
		}

		return $_result;		
	}

	/**
	 * After a user has logged in, we cache the user row...
	 */
	protected function afterLogin( $fromCookie )
	{
		parent::afterLogin( $fromCookie );

		//	Set the current user value...
		if ( null === ( $this->_user = PS::_gs( 'currentUser' ) ) )
		{
			if ( $this->_user = User::model()->findByPk( PS::_gu()->getId() ) )
			{
				PS::_ss( 'currentUser', $this->_user );
				PS::_ss( 'currentMe', $this->_me );
			}
		}
	}

	/**
	 * After a user has logged out, clear our data...
	 */
	protected function afterLogout()
	{
		if ( parent::afterLogout() )
			$this->_me = $this->_session = null;
	}

}
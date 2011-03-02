<?php
/**
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
Yii::import( 'pogostick.helpers.PS.php' );

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
	* @var string A copy of the facebook user Id
	*/
	protected $_fbUserId = null;
	public function getFBUserId() { return PS::o( $this->_me, 'id' ); }
	public function setFBUserId( $value ) { $this->_fbUserId = $value; }

	/**
	* @var array A copy of the user session
	*/
	protected $_facebookApi = null;
	public function getfacebookApi() { return $this->_facebookApi; }

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	 * Initialize user and pull anything out of the session that's there...
	 */
	public function init()
	{
		parent::init();

		$this->_user = CPSHelperBase::_gs( 'currentUser' );
		$this->_me = CPSHelperBase::_gs( 'currentMe' );
	}

	/**
	 * Authenticate a facebook user
	 * @param CPSFacebook
	 * @param CPSFacebookUserIdentity You may optionally pass in an identiy object
	 * @return boolean
	 */
	public static function authenticateUser( CPSFacebook $facebookApi, CPSFacebookUserIdentity $identity = null )
	{
		$_result = false;
		$_identity = ( null === $identity ? new CPSFacebookUserIdentity( $facebookApi, null ) : $identity );

		if ( $_identity->authenticate() && CUserIdentity::ERROR_NONE == $_identity->errorCode )
		{
			PS::_gu()->allowAutoLogin = false;
			PS::_gu()->login( $_identity, 0 );

			//	Set the current user value...
			if ( null === ( $_user = PS::_gs( 'currentUser' ) ) )
			{
				$_me = $_identity->getMe();
				PS::_gu()->setFBUserId( $_me['id'] );

				if ( null !== ( $_user = User::model()->find( 'pform_user_id_text = :pform_user_id_text', array( ':pform_user_id_text' => $_me['id'] ) ) ) )
				{
					PS::_ss( 'currentUser', $_user );
					PS::_ss( 'currentMe', $_me );
				}
			}

			$_result = true;
		}

		return $_result;
	}

	/**
	 * After a user has logged out, clear our data...
	 */
	protected function afterLogout()
	{
		if ( parent::afterLogout() )
		{
			$this->_me = null;
			PS::_ss( 'currentUser', $this->_user );
			PS::_ss( 'currentMe', null );
		}
	}

}
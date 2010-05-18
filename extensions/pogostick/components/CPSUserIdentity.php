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
class CPSUserIdentity extends CUserIdentity
{
	//********************************************************************************
	//* Private Members
	//********************************************************************************

	/**
	* Our user id
	*
	* @var int
	*/
	protected $m_iUserId;
	public function getId() { return $this->m_iUserId; }
	public function setId( $iValue ) { $this->m_iUserId = $iValue; }

	/**
	* A generic user type you can use to distinguish between your users
	* @var mixed
	*/
	protected $m_mUserType;
	public function getUserType() { return $this->m_mUserType; }
	public function setUserType( $mValue ) { $this->m_mUserType = $mValue; }

	/**
	 * The model with which to use for database authentication.
	 * @var CModel
	 */
	protected $m_oAuthModel;
	public function getAuthModel() { return $this->m_oAuthModel; }
	public function setAuthModel( $oValue ) { $this->m_oAuthModel = $oValue; }

	/**
	 * The column names in {@link authModel} to use for authentication
	 *
	 * @var array
	 */
	protected $m_arAuthAttributes = array(
		'username' => 'username',
		'password' => 'password',
		'email' => 'email',
	);
	public function getAuthAttributes() { return $this->m_arAuthAttributes; }
	public function setAuthAttributes( $arValue ) { $this->m_arAuthAttributes = $arValue; }

	/**
	 * If true, users can use their email address to log in as well as a user name
	 * @var boolean
	 */
	protected $m_bAllowEmailLogins = true;
	public function getAllowEmailLogins() { return $this->m_bAllowEmailLogins; }
	public function setAllowEmailLogins( $bValue ) { $this->m_bAllowEmailLogins = $bValue; }

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	 * Constructor.
	 *
	 * @param string user name
	 * @param string password
	 */
	public function __construct( $sUserName, $sPassword, $mUserType = null )
	{
		$this->m_mUserType = $mUserType;

		parent::__construct( $sUserName, $sPassword );

		Yii::import( 'pogostick.events.CPSAuthenticationEvent' );
	}

	/**
	 * Authenticates a user based on {@link userName} and {@link password}.
	 * Derived classes should override this method, or an exception will be thrown.
	 * This method is required by {@link IUserIdentity}.
	 * @param mixed $mUserType
	 * @return boolean whether authentication succeeds.
	 */
	public function authenticate( $mUserType = null )
	{
		if ( ! $this->m_oAuthModel )
			throw new CException( Yii::t( 'psYiiExtensions', '{class}::authModel must be set or {class}::authenticate() must be overridden.', array( '{class}' => get_class( $this ) ) ) );

		//	Attach our event handlers
		$this->attachEventHandler( 'onLoginFailure', array( $this, 'loginFailure' ) );
		$this->attachEventHandler( 'onLoginSuccess', array( $this, 'loginSuccess' ) );

		$this->username = trim( strtolower( $this->username ) );
		if ( null !== $mUserType ) $this->m_mUserType = $mUserType;

		$_arCondition = array( $this->m_arAuthAttributes['username'] . ' = :' . $this->m_arAuthAttributes['username'] );
		$_arParams = array( ':' . $this->m_arAuthAttributes['username'] => $this->username );

		if ( $this->m_bAllowEmailLogins && $this->m_arAuthAttributes['email'] )
		{
			$_arCondition[] = $this->m_arAuthAttributes['email'] . ' = :' . $this->m_arAuthAttributes['email'];
			$_arParams[ ':' . $this->m_arAuthAttributes['email'] ] = $this->username;
		}

		$_oUser = $this->m_oAuthModel->find( implode( ' OR ', $_arCondition ), $_arParams );

		if ( $_oUser === null )
			$this->errorCode = self::ERROR_USERNAME_INVALID;
		else
		{
			//	Get our event
			$_oEvent = new CPSAuthenticationEvent( $this, $_oUser, $this );

			if ( $this->password !== $_oUser->{$this->m_arAuthAttributes['password']} )
			{
				$this->errorCode = self::ERROR_PASSWORD_INVALID;
				$this->onLoginFailure( $_oEvent );
			}
			else
			{
				$this->errorCode = self::ERROR_NONE;
				$this->setId( $_oUser->id );
				$this->setUserType( $_oUser->user_type_code );
				$this->onLoginSuccess( $_oEvent );
			}
		}

		return ! $this->errorCode;
	}

	/**
	 * Authenticates the password.
	 * This acts as an 'authenticate' validator as declared in rules().
	 *
	 * @param CFormModel $oForm The data to validate
	 * @param string $sUser
	 * @param string $sPassword
	 * @returns bool
	 */
	public static function authenticatePassword( $arOptions = array(), $sClassName = __CLASS__ )
	{
		$_bResult = false;
		$_sUser = PS::o( $arOptions, 'userName' );
		$_sPass = PS::o( $arOptions, 'password' );
		$_oFormModel = PS::o( $arOptions, 'formModel' );

		$_oIdent = new $sClassName( $_sUser, $_sPass );
		$_oIdent->setAuthModel( PS::o( $arOptions, 'authModel' ) );
		$_oIdent->authenticate();

		switch ( $_oIdent->errorCode )
		{
			case self::ERROR_NONE:
				$_iDuration = PS::o( $arOptions, 'rememberMe', false ) ? 3600 * 24 * 30 : 0;
				Yii::app()->user->login( $_oIdent, $_iDuration );
				$_bResult = true;
				break;

			default:
				if ( $_oFormModel ) $_oFormModel->addError( 'form', 'User name or password is incorrect.' );
				break;
		}

		return $_bResult;
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

<?php

/**
 * UserIdentity represents the data needed to identity a user.
 * It contains the authentication method that checks if the provided
 * data can identity the user.
 */
class UserIdentity extends CUserIdentity
{
	//********************************************************************************
	//* Member Variables
	//********************************************************************************
	
	/**
	* Get our user id
	* @var integer
	*/
    protected $_id;
	public function getId() { return $this->_id; }
    
	/**
	 * Authenticates a user.
	 * The example implementation makes sure if the username and password
	 * are both 'demo'.
	 * In practical applications, this should be changed to authenticate
	 * against some persistent user identity storage (e.g. database).
	 * @return boolean whether authentication succeeds.
	 */
	public function authenticate()
	{
        $username = strtolower( $this->username );
        $user = User::model()->find( 'LOWER(user_name_text) = ?' , array( $username ) );
        
        if ( $user === null )
        	$this->errorCode = self::ERROR_USERNAME_INVALID;
        else if ( md5( $this->password ) != $user->password_text )
            $this->errorCode = self::ERROR_PASSWORD_INVALID;
        else
        {
            $this->_id = $user->id;
            $this->username = $user->user_name_text;
            $this->errorCode = self::ERROR_NONE;
        }
        
        return ! $this->errorCode;
	}
}
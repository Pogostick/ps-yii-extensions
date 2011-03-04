<?php
/**
 * This file is part of the Pogostick Yii Extension library
 *
 * @copyright Copyright &copy; 2009-2010 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */
/**
 * A base web user class with enhanced session security
 *
 * @package 	psYiiExtensions
 * @subpackage	components
 *
 * @author		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN $Id$
 * @since		v1.2.0
 *
 * @filesource
 */
class CPSWebUser extends CWebUser
{
	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	/**
	 * @var CPSStateManager Our state manager
	 */
	protected $_stateManager = null;
	public function getStateManager() { return $this->_stateManager; }
	public function setStateManager( $value ) { $this->_stateManager = $value; }

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	 * Constructor with optional state key prefix override
	 * @param string $keyPrefix
	 */
	public function __construct( $keyPrefix = null )
	{
		//	Initialize the state key prefix
		$this->_stateManager = new CPSStateManager( $keyPrefix );
	}

	/**
	 * Returns the value of a variable that is stored in user session.
	 *
	 * This function is designed to be used by CWebUser descendant classes
	 * who want to store additional user information in user session.
	 * A variable, if stored in user session using {@link setState} can be
	 * retrieved back using this function.
	 *
	 * @param string $key variable name
	 * @param mixed $defaultValue default value
	 * @return mixed the value of the variable. If it doesn't exist in the session, the provided default value will be returned
	 * @see setState
	 */
	public function getState( $key, $defaultValue = null, $isFlash = false )
	{
		return $this->_stateManager->getState( $key, $defaultValue, $isFlash );
	}

	/**
	 * Stores a variable in user session.
	 *
	 * This function is designed to be used by CWebUser descendant classes
	 * who want to store additional user information in user session.
	 * By storing a variable using this function, the variable may be retrieved
	 * back later using {@link getState}. The variable will be persistent
	 * across page requests during a user session.
	 *
	 * @param string $key variable name
	 * @param mixed $value variable value
	 * @param mixed $defaultValue default value. If $value===$defaultValue, the variable will be removed from the session
	 * @see getState
	 */
	public function setState( $key, $value, $defaultValue = null, $isFlash = false )
	{
		$this->_stateManager->setState( $key, $value, $defaultValue, $isFlash );
	}

	/**
	 * Returns a value indicating whether there is a state of the specified name.
	 * @param string $key state name
	 * @return boolean whether there is a state of the specified name.
	 * @since 1.0.3
	 */
	public function hasState( $key )
	{
		return $this->_stateManager->hasState( $key );
	}

	/**
	 * Clears all user identity information from persistent storage.
	 * This will remove the data stored via {@link setState}.
	 */
	public function clearStates()
	{
		$this->_stateManager->clearStates();
	}

	/**
	 * Returns all flash messages.
	 * This method is similar to {@link getFlash} except that it returns all
	 * currently available flash messages.
	 * @param boolean $delete whether to delete the flash messages after calling this method.
	 * @return array flash messages (key => message).
	 */
	public function getFlashes( $delete = true )
	{
		return $this->_stateManager->getFlashes( $delete );
	}

	/**
	 * Returns a flash message.
	 * A flash message is available only in the current and the next requests.
	 * @param string $key key identifying the flash message
	 * @param mixed $defaultValue value to be returned if the flash message is not available.
	 * @param boolean $delete whether to delete this flash message after accessing it.
	 * Defaults to true. This parameter has been available since version 1.0.2.
	 * @return mixed the message message
	 */
	public function getFlash( $key, $defaultValue = null, $delete = true )
	{
		return $this->_stateManager->getFlash( $key, $defaultValue, $delete );
	}

	/**
	 * Stores a flash message.
	 * A flash message is available only in the current and the next requests.
	 * @param string $key key identifying the flash message
	 * @param mixed $value flash message
	 * @param mixed $defaultValue if this value is the same as the flash message, the flash message
	 * will be removed. (Therefore, you can use setFlash('key',null) to remove a flash message.)
	 */
	public function setFlash( $key, $value, $defaultValue = null )
	{
		$this->_stateManager->setFlash( $key, $value, $defaultValue );
	}

	/**
	 * @param string $key key identifying the flash message
	 * @return boolean whether the specified flash message exists
	 */
	public function hasFlash( $key )
	{
		return $this->_stateManager->hasFlash( $key );
	}
}
<?php
/**
 * CPSRestBehavior.php
 * 
 * Copyright (c) 2010 Jerry Ablan <jablan@pogostick.com>.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 * 
 * This file is part of Pogostick : Yii Extensions.
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
 * CPSRestBehavior provides REST behaviors to controllers
 *
 * @package 	psYiiExtensions
 * @subpackage	behaviors
 *
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN $Id: CPSRestBehavior.php 395 2010-07-15 21:34:48Z jerryablan@gmail.com $
 * @since 		v1.1.0
 *
 * @filesource
 */
class CPSRestBehavior extends CPSComponentBehavior
{
	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	/**
	 * The actions we know are for REST
	 * @var array
	 */
	protected $_restActions = array();
	public function getRestActions() { return $this->_restActions; }
	public function setRestActions( $restActions ) { $this->_restActions = $restActions; }

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/***
	 * Registers an action as a REST action
	 * @param string $actionName
	 * @param string $method
	 * @param mixed $access
	 */
	public function addRestAction( $actionName, $method = 'GET', $access = CPSCRUDController::ACCESS_TO_AUTH )
	{
		$this->_restActions[ $actionName ] = array(
			'method' => $method,
			'access' => $access,
		);
	}

	/**
	 * Determines if this behavior knows about a particular action.
	 * @param string $actionName
	 * @param string $method
	 * @return boolean
	 */
	public function hasAction( $actionName, $method = 'GET' )
	{
		if ( $_action = CPSHelperBase::o( $this->_restActions, $actionName ) )
			return ( CPSHelperBase::o( $this->_restActions[$actionName], 'method' ) == $method );

		return false;
	}

	/**
	 * Creates the action instance based on the action name.
	 * The action can be either an inline action or an object.
	 * The latter is created by looking up the action map specified in {@link actions}.
	 * @param string ID of the action. If empty, the {@link defaultAction default action} will be used.
	 * @return CAction the action instance, null if the action does not exist.
	 * @see actions
	 */
	public function createAction( $sActionId )
	{
		$_sActionId = ( $sActionId === '' ) ? $this->getOwner()->defaultAction : $sActionId;

		//	Is it a valid request?
		if ( ! method_exists( $this->getOwner(), 'get' . $_sActionId ) && ! method_exists( $this->getOwner(), 'post' . $_sActionId ) && ! method_exists( $this->getOwner(), 'request' . $_sActionId ) )
			return $this->getOwner()->missingAction( $_sActionId );

		return new CPSRESTAction( $this->getOwner(), $_sActionId );
	}

	//********************************************************************************
	//* Private Methods
	//********************************************************************************

	/**
	 * Runs the named REST action.
	 * Filters specified via {@link filters()} will be applied.
	 * @param string $sActionId Action id
	 * @throws CHttpException if the action does not exist or the action name is not proper.
	 * @see filters
	 * @see createAction
	 * @see runAction
	 * @access protected
	 */
	protected function dispatchRequest( CAction $oAction )
	{
		$_sActionId = $oAction->getId();
		$_arParams = $_REQUEST;
		$_arUrlParams = array();
		$_arOpts = array();

		//	If additional parameters are specified in the URL, convert to parameters...
		$_sUri = Yii::app()->getRequest()->getRequestUri();

		if ( null != ( $_sUri = trim( str_ireplace( '/' . $this->getOwner()->getId() . '/' . $_sActionId, '', $_sUri ) ) ) )
		{
			$_sUri = trim( $_sUri, '/?' );
			$_arOpts = ( ! empty( $_sUri ) ? explode( '/', $_sUri ) : array() );

			foreach ( $_arOpts as $_sKey => $_oValue )
			{
				if ( false !== strpos( $_oValue, '=' ) )
				{
					if ( null != ( $_arTemp = explode( '=', $_oValue ) ) )
						$_arOpts[ $_arTemp[0] ] = $_arTemp[1];

					unset( $_arOpts[ $_sKey ] );
				}
				else
					$_arOpts[ $_sKey ] = $_oValue;
			}
		}

		//	Any query string? (?x=y&...)
		if ( null != ( $_sQuery = parse_url( $_sUri, PHP_URL_QUERY ) ) )
			$_arOpts = array_merge( explode( '=', $_sQuery ), $_arOpts );

		//	load into url params
		foreach ( $_arOpts as $_sKey => $_sValue )
			if ( ! isset( $_arUrlParams[ $_sKey ] ) ) $_arUrlParams[ $_sKey ] = $_sValue;

		//	Is it a valid request?
		$_sType = strtolower( $this->getOwner()->getRequest()->getRequestType() );
		$_sMethod = $_sType . ucfirst( $_sActionId );

		if ( $_sType == 'post' )
		{
			foreach ( $_POST as $_sKey => $_oValue )
			{
				if ( ! is_array( $_oValue ) )
					$_arUrlParams[ $_sKey ] = $_oValue;
				else
				{
					foreach ( $_oValue as $_sSubKey => $_oSubValue )
						$_arUrlParams[ $_sSubKey ] = $_oSubValue;
				}
			}
		}

		if ( ! method_exists( $this->getOwner(), $_sMethod ) )
		{
			//	Is it a valid catchall request?
			if ( ! method_exists( $this->getOwner(), 'request' . $_sActionId ) )
				//	No clue what it is, so must be bogus. Hand off to missing action...
				return $this->getOwner()->missingAction( $_sActionId );

			$_sMethod = 'request' . $_sActionId;
		}

		//	All rest methods echo their output
		echo call_user_func_array( array( $this->getOwner(), $_sMethod ), array_values( $_arUrlParams ) );
	}

}
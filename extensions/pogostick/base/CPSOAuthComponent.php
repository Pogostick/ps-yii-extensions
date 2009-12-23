<?php
/*
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * The CPSOAuthComponent is the base class for all Pogostick widgets for Yii
 * 
 * @package 	psYiiExtensions
 * @subpackage 	base
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN $Id$
 * @since 		v1.0.3
 * 
 * @filesource
 */
class CPSOAuthComponent extends CPSApiComponent
{
	//********************************************************************************
	//* Constructor
	//********************************************************************************

	/**
	* Constructor
	*
	*/
	public function __construct( $oOwner = null )
	{
		//	Call daddy
		parent::__construct( $oOwner );

		//	Attach our api behavior
		$this->attachBehavior( $this->getInternalName(), 'pogostick.behaviors.CPSOAuthBehavior' );

		//	Log it and check for issues...
		CPSCommonBase::writeLog( Yii::t( $this->getInternalName(), '{class} constructed', array( "{class}" => get_class( $this ) ) ), 'trace', $this->getInternalName() );
	}

	//********************************************************************************
	//* Public methods
	//********************************************************************************

	/***
	* Fetches a protected resource using the tokens stored
	* 
	* @param string $sUrl
	* @param array $arArgs
	* @param string $sMethod
	* @param array $arHeaders Headers to add to the call
	*/
	protected function makeRequest( $sAction, $arRequestData = array(), $sMethod = null, $arHeaders = array() )
	{
		//	Default...
		$_arRequestData = $this->requestData;
		$_arReqArgs = array();
		$_bRequireAuth = false;
		$_sResults = null;

		//	Check data...
		if ( null != $arRequestData ) $_arRequestData = array_merge( $_arRequestData, $arRequestData );

		//	Add the request data to the Url...
		if ( is_array( $this->requestMap )  )
		{
			$_arMap = ( isset( $this->requestMap[ $this->apiToUse ][ $sAction ] ) ) ? $this->requestMap[ $this->apiToUse ][ $sAction ] : null;
			$_arOptions = ( isset( $_arMap[ 'options' ] ) ) ? $_arMap[ 'options' ] : null;
			$_arReqOneOf = ( isset( $_arOptions[ '_requireOneOf' ] ) ) ? $_arOptions[ '_requireOneOf' ] : null;
			$_sMethod = ( isset( $_arOptions[ '_method' ] ) ) ? $_arOptions[ '_method' ] : ( null != $sMethod ) ? $sMethod : CPSApiBehavior::HTTP_GET;
			$_arParams = ( isset( $_arParams[ 'params' ] ) ) ? $_arParams[ 'params' ] : null;

			$_bThere = ( null == $_arReqOneOf );
			
			//	Build our query
			if ( is_array( $_arParams ) )
			{
				foreach ( $_arParams as $_sKey => $_bRequired )
				{
					//	Check required items
					if ( null != $_arReqOneOf && ! $_bThere )
						$_bThere = in_array( $_sKey, $_arReqOneOf );

					if ( $_bRequired && ! isset( $_arRequestData[ $_sKey ] ) )
					{
						throw new CException(
							Yii::t(
								__CLASS__,
								'Required parameter {param} was not included in requestData',
								array(
									'{param}' => $_sKey,
								)
							)
						);
					}

					//	Add to query string if set...
					if ( isset( $_arRequestData[ $_sKey ] ) )
						$_arReqArgs[ $_sKey ] = $_arRequestData[ $_sKey ];
				}
			}
		}

		//	Check requireOneOf option...
		if ( ! $_bThere )
		{
			throw new CException(
				Yii::t(
					__CLASS__,
					'This call requires one of the following parameters: {params}',
					array(
						'{param}' => implode( ', ', $_requireOneOf ),
					)
				)
			);
		}

		//	Build the url...
		$_sUrl = $this->apiBaseUrl . '/' . $this->apiToUse . ( ( $sAction == '/' ) ? '' : '/' . $sAction ) . '.json';

		//	Handle events...
		$_oEvent = new CPSApiEvent( $_sUrl, $_arReqArgs, null, $this );
		$this->beforeApiCall( $_oEvent );

		//	Make the call...
		try
		{
			$_arToken = $this->getToken();
			if ( $this->getOAuthObject()->setToken( $_arToken[ 'oauth_token' ], $_arToken[ 'oauth_token_secret' ] ) )
			{
				if ( $this->getOAuthObject()->fetch( $_sUrl, $_arReqArgs, $_sMethod, $arHeaders ) )
				{
					//	Get results...
					$_sResults = $this->getOAuthObject()->getLastResponse();
				}
				else
				{
					//	Get error response
				}
			}
		}
		catch ( Exception $_ex )
		{
			$_sResults = null;
			CPSCommonBase::writeLog( Yii::t( $this->getInternalName(), 'Error making OAuth fetch request in {class}: {message}', array( "{class}" => get_class( $this ), 'message' => $_ex->getMessage() ) ), 'trace', $this->getInternalName() );
		}

		//	Handle events...
		$_oEvent->urlResults = $_sResults;
		$this->afterApiCall( $_oEvent );

		//	Raise our completion event...
		$_oEvent->setUrlResults( $_sResults );
		$this->requestComplete( $_oEvent );

		//	If user doesn't want JSON output, then reformat
		switch ( $this->format )
		{
			case 'xml':
				$_sResults = CPSHelp::arrayToXml( json_decode( $_sResults, true ), 'Results' );
				break;
				
			case 'json':
				//	Already in array format
				break;

			case 'array':
				$_sResults = json_decode( $_sResults );
				break;
		}

		//	Return results...
		return $_sResults;
	}

	//********************************************************************************
	//* Events and Handlers
	//********************************************************************************

	/**
	 * Declares events and the corresponding event handler methods.
	 * @return array events (array keys) and the corresponding event handler methods (array values).
	 * @see CBehavior::events
	 */
	public function events()
	{
		return(
			array_merge(
				parent::events(),
				array(
					'onUserAuthorized' => 'userAuthorized',
				)
			)
		);
	}

	/**
	* userAuthorized event
	* 
	* @param CPSApiEvent $oEvent
	*/
	public function onUserAuthorized( $oEvent )
	{
		return $this->raiseEvent( 'onUserAuthorized', $oEvent );
	}
	
	/**
	* Called when a user has been authorized
	* 
	* @param mixed $sData
	*/
	public function userAuthorized( $sData )
	{
		$this->onUserAuthorized( new CPSApiEvent( null, null, $sData ) );
	}
	
}
<?php
/*
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * CPSApiComponent provides a convenient base class for APIs.
 * Introduces three new events:
 * 
 * onBeforeApiCall
 * onAfterApiCall
 * onRequestComplete
 * 
 * Each are called respectively and pass the handler a CPSApiEvent
 * object with details of the call.
 * 
 * @package 	psYiiExtensions
 * @subpackage 	base
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version SVN: $Id$
 * @since 		v1.0.0
 * 
 * @filesource
 */
class CPSApiComponent extends CPSComponent
{
	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	* Constructor
	*
	*/
	public function __construct()
	{
		//	Call daddy...
		parent::__construct( $this );

		//	Attach our default behavior
		$this->attachBehavior( $this->m_sInternalName, 'pogostick.behaviors.CPSApiBehavior' );

		//	Log it and check for issues...
		CPSCommonBase::writeLog( Yii::t( $this->m_sInternalName, '{class} constructed', array( "{class}" => get_class( $this ) ) ), 'trace', $this->m_sInternalName );
	}

	//********************************************************************************
	//* Private Methods
	//********************************************************************************

	/**
	* Makes the actual HTTP request based on settings
	*
	* @param string $sSubType
	* @param array $arRequestData
	* @return string
	*/
	protected function makeRequest( $sSubType = '/', $arRequestData = null, $sMethod = 'GET' )
	{
		//	Make sure apiQueryName is set...
		if ( $this->requireApiQueryName && $this->isEmpty( $this->apiQueryName ) )
		{
			throw new CException(
				Yii::t(
					__CLASS__,
					'Required option "apiQueryName" is not set.'
				)
			);
		}

		//	Default...
		$_arRequestData = $this->requestData;

		//	Check data...
		if ( null != $arRequestData ) $_arRequestData = array_merge( $_arRequestData, $arRequestData );

		//	Check subtype...
		if ( ! empty( $sSubType ) && is_array( $this->requestMap[ $this->apiToUse ] ) )
		{
			if ( ! array_key_exists( $sSubType, $this->requestMap[ $this->apiToUse ] ) )
			{
				throw new CException(
					Yii::t(
						__CLASS__,
						'Invalid API SubType specified for "{apiToUse}". Valid subtypes are "{subTypes}"',
						array(
							'{apiToUse}' => $this->apiToUse,
							'{subTypes}' => implode( ', ', array_keys( $this->requestMap[ $this->apiToUse ] ) )
						)
					)
				);
			}
		}

		//	First build the url...
		$_sUrl = $this->apiBaseUrl .
			( substr( $this->apiBaseUrl, strlen( $this->apiBaseUrl ) - 1, 1 ) != '/' ? '/' : '' ) .
			( ( isset( $this->apiSubUrls[ $this->apiToUse ] ) && '/' != $this->apiSubUrls[ $this->apiToUse ] ) ? $this->apiSubUrls[ $this->apiToUse ] : '' );

		//	Add the API key...
		if ( $this->requireApiQueryName ) $_sQuery = $this->apiQueryName . '=' . $this->apiKey;

		//	Add the request data to the Url...
		if ( is_array( $this->requestMap ) && ! empty( $sSubType ) )
		{
			$_arRequestMap = $this->requestMap[ $this->apiToUse ][ $sSubType ];
			$_arDone = array();
			
			//	Add any extra requestData parameters unchecked to the query string...
			foreach ( $_arRequestData as $_sKey => $_sValue )
			{
				if ( ! array_key_exists( $_sKey, $_arRequestMap ) ) 
				{
					$_sQuery .= '&' . $_sKey . '=' . urlencode( $_sValue );
					unset( $_arRequestData[ $_sKey ] );
				}
			}
				
			//	Now build the url...
			foreach ( $_arRequestMap as $_sKey => $_arInfo )
			{
				//	Tag as done...
				$_arDone[] = $_sKey;
				
				//	Is there a default value?
				if ( isset( $_arInfo[ 'default' ] ) && ! isset( $_arRequestData[ $_sKey ] ) ) $_arRequestData[ $_sKey ] = $_arInfo[ 'default' ];
				
				if ( isset( $_arInfo[ 'required' ] ) && $_arInfo[ 'required' ] && ! array_key_exists( $_sKey, $_arRequestData ) )
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

				//	Add to query string
				if ( isset( $_arRequestData[ $_sKey ] ) ) $_sQuery .= '&' . $_arInfo[ 'name' ] . '=' . urlencode( $_arRequestData[ $_sKey ] );
			}
		}
		//	Handle non-requestMap call...
		else if ( is_array( $_arRequestData ) )
		{
			foreach ( $this->requestData as $_sKey => $_oValue )
			{
				if ( isset( $_arRequestData[ $_sKey ] ) ) $_sQuery .= '&' . $_sKey . '=' . urlencode( $_arRequestData[ $_sKey ] );
			}
		}

		//	Handle events...
		$_oEvent = new CPSApiEvent( $_sUrl, $_sQuery, null, $this );
		$this->beforeApiCall( $_oEvent );

		//	Ok, we've build our request, now let's get the results...
		$_sResults = $this->makeHttpRequest( $_sUrl, $_sQuery, $sMethod, $this->userAgent );

		//	Handle events...
		$_oEvent->urlResults = $_sResults;
		$this->afterApiCall( $_oEvent );

		//	If user doesn't want JSON output, then reformat
		switch ( $this->format )
		{
			case 'xml':
				$_sResults = CPSHelp::arrayToXml( json_decode( $_sResults, true ), 'Results' );
				break;

			case 'array':
				$_sResults = json_decode( $_sResults, true );
				break;
		}

		//	Raise our completion event...
		$_oEvent->setUrlResults( $_sResults );
		$this->requestComplete( $_oEvent );

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
					'onBeforeApiCall' => 'beforeApiCall',
					'onAfterApiCall' => 'afterApiCall',
					'onRequestComplete' => 'requestComplete',
				)
			)
		);
	}

	/**
	* Call to raise the onBeforeApiCall event
	*
	* @param CPSApiEvent $oEvent
	*/
	public function beforeApiCall( CPSApiEvent $oEvent )
	{
		$this->onBeforeApiCall( $oEvent );
	}

	/**
	* Raises the onBeforeApiCall event
	*
	* @param CPSApiEvent $oEvent
	*/
	public function onBeforeApiCall( CPSApiEvent $oEvent )
	{
		$this->raiseEvent( 'onBeforeApiCall', $oEvent );
	}

	/**
	* Call to raise the onAfterApiCall event
	*
	* @param CPSApiEvent $oEvent
	*/
	public function afterApiCall( CPSApiEvent $oEvent )
	{
		$this->onAfterApiCall( $oEvent );
	}

	/**
	* Raises the onAfterApiCall event. $oEvent contains "raw" return data
	*
	* @param CPSApiEvent $oEvent
	*/
	public function onAfterApiCall( CPSApiEvent $oEvent )
	{
		$this->raiseEvent( 'onAfterApiCall', $oEvent );
	}

	/**
	* Call to raise the onRequestComplete event
	*
	* @param CPSApiEvent $oEvent
	*/
	public function requestComplete( CPSApiEvent $oEvent )
	{
		$this->onRequestComplete( $oEvent );
	}

	/**
	* Raises the onRequestComplete event. $oEvent contains "processed" return data (if applicable)
	*
	* @param CPSApiEvent $oEvent
	*/
	public function onRequestComplete( CPSApiEvent $oEvent )
	{
		$this->raiseEvent( 'onRequestComplete', $oEvent );
	}

}
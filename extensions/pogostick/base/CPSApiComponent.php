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
 * @version 	SVN: $Id$
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
	* Preinitialize
	*
	*/
	public function preinit()
	{
		//	Call daddy...
		parent::preinit();

		//	Attach our default behavior
		$this->attachBehavior( $this->m_sInternalName, 'pogostick.behaviors.CPSApiBehavior' );
		
		//	Attach our events...
		$this->attachEventHandler( 'onBeforeApiCall', array( $this, 'beforeApiCall' ) );
		$this->attachEventHandler( 'onAfterApiCall', array( $this, 'afterApiCall' ) );
		$this->attachEventHandler( 'onRequestComplete', array( $this, 'requestComplete' ) );
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
		if ( null != $arRequestData && is_array( $arRequestData ) ) $_arRequestData = array_merge( $_arRequestData, $arRequestData );

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
		if ( is_array( $this->requestMap ) && ! empty( $sSubType ) && isset( $this->requestMap[ $this->apiToUse ] ) )
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
			foreach ( $_arRequestData as $_sKey => $_oValue )
			{
				if ( isset( $_arRequestData[ $_sKey ] ) ) $_sQuery .= '&' . $_sKey . '=' . urlencode( $_oValue );
			}
		}

		CPSLog::trace( __METHOD__, 'Calling onBeforeApiCall' );

		//	Handle events...
		$_oEvent = new CPSApiEvent( $_sUrl, $_sQuery, null, $this );
		$this->onBeforeApiCall( $_oEvent );

		CPSLog::trace( __METHOD__, 'Making request: ' . $_sQuery );

		//	Ok, we've build our request, now let's get the results...
		$_sResults = PS::makeHttpRequest( $_sUrl, $_sQuery, $sMethod, $this->userAgent );

		CPSLog::trace( __METHOD__, 'Call complete: ' . var_export( $_sResults, true ) );

		//	Handle events...
		$_oEvent->urlResults = $_sResults;
		$this->onAfterApiCall( $_oEvent );

		//	If user doesn't want JSON output, then reformat
		switch ( $this->returnFormat )
		{
			case 'xml':
				$_sResults = CPSTransform::arrayToXml( json_decode( $_sResults, true ), 'Results' );
				break;

			case 'array':
				$_sResults = json_decode( $_sResults, true );
				break;
				
			default:	//	Naked
				break;
		}

		//	Raise our completion event...
		$_oEvent->setUrlResults( $_sResults );
		$this->onRequestComplete( $_oEvent );

		//	Return results...
		return $_sResults;
	}

	//********************************************************************************
	//* Events and Handlers
	//********************************************************************************

	/**
	* Raises the onBeforeApiCall event
	*
	* @param CPSApiEvent $oEvent
	*/
	public function onBeforeApiCall( CPSApiEvent $oEvent )
	{
		$this->raiseEvent( 'onBeforeApiCall', $oEvent);
	}
	
	public function beforeApiCall( CPSApiEvent $oEvent )
	{
		return true;
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

	public function afterApiCall( CPSApiEvent $oEvent )
	{
		return true;
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

	public function requestComplete( CPSApiEvent $oEvent )
	{
		return true;
	}

}
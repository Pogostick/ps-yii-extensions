<?php
/**
 * CPSApiWidgetBehavior class file.
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://www.pogostick.com/
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @license http://www.pogostick.com/license/
 */

/**
 * CPSApiWidget provides a behavior to classes for making API calls
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @version $Id$
 * @package psYiiExtensions
 * @subpackage Behaviors
 * @since 1.0.5
 */
class CPSApiWidgetBehavior extends CPSApiBehavior
{
	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/***
	* Constructor
	*
	*/
	public function __construct( $arClassOptions = null )
	{
		//	Call daddy...
		parent::__construct( $arClassOptions );

		//	Add ours...
		$this->setOptions( self::getBaseOptions() );

		//	Log it and check for issues...
		CPSCommonBase::writeLog( Yii::t( $this->getInternalName(), '{class} constructed', array( "{class}" => get_class( $this ) ) ), 'trace', $this->getInternalName() );
    }

	/**
	* Allows for single behaviors
	*
	*/
	private function getBaseOptions()
	{
		return(
			array(
				//	Widget Options
				'html' => array( 'value' => '', 'type' => 'string' ),
				'script' => array( 'value' => '', 'type' => 'string' ),
				'cssFile' => array( 'value' => '', 'type' => 'string' ),
				'viewName' => array( 'value' => '', 'type' => 'string' ),

				//	API options
				'altApiKey' => array( 'value' => '', 'type' => 'string' ),
				'apiBaseUrl' => array( 'value' => '', 'type' => 'string' ),
				'apiKey' => array( 'value' => '', 'type' => 'string' ),
				'apiQueryName' => array( 'value' => '', 'type' => 'string' ),
				'apiToUse' => array( 'value' => '', 'type' => 'string' ),
				'apiSubUrls' => array( 'value' => array(), 'type' => 'array' ),
				'format' => array( 'value' => 'array', 'type' => 'string' ),
				'httpMethod' => array( 'value' => self::HTTP_GET, 'type' => 'string' ),
				'requestData' => array( 'value' => array(), 'type' => 'array' ),
				'requestMap' => array( 'value' => array(), 'type' => 'array' ),
				'userAgent' => array( 'value' => 'Pogostick Components for Yii; (+http://www.pogostick.com/yii)', 'type' => 'string' ),
			)
		);
	}

	//********************************************************************************
	//* Public Methods
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
	* beforeApiCall event
	*
	* @param CPSApiEvent $oEvent
	*/
	public function beforeApiCall( $oEvent )
	{
	}

	/**
	* afterApiCall event
	*
	* @param CPSApiEvent $oEvent
	*/
	public function afterApiCall( $oEvent )
	{
	}

	/**
	* requestComplete event
	*
	* @param CPSApiEvent $oEvent
	*/
	public function requestComplete( $oEvent )
	{
	}

	//********************************************************************************
	//* Private Methods
	//********************************************************************************

	 /**
	 * Make an HTTP request
	 *
	 * @param string $sUrl The URL to call
	 * @param string $sQueryString The query string to attach
	 * @param string $sMethod The HTTP method to use. Can be 'GET' or 'SET'
	 * @param integer $iTimeOut The number of seconds to wait for a response. Defaults to 60 seconds
	 * @param function|array $oHeaderCallback The callback function to call after the header has been read. Accepts function reference or array( object, method )
	 * @param function|array $oReadCallback The callback function to call after the body has been read. Accepts function reference or array( object, method )
	 * @return mixed The data returned from the HTTP request or null for no data
	 */
	protected function makeHttpRequest( $sUrl, $sQueryString = null, $sMethod = 'GET', $sUserAgent = null, $iTimeOut = 60, $oHeaderCallback = null, $oReaderCallback = null )
	{
		//	Our user-agent string
		$_sAgent = ( null != $sUserAgent ) ? $sUserAgent : 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; .NET CLR 2.0.50727; .NET CLR 3.0.04506; InfoPath.3)';

		//	Our return results
		$_sResult = null;

		// Use cURL
		if ( function_exists( 'curl_init' ) )
		{
			$_oCurl = curl_init();

			curl_setopt( $_oCurl, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $_oCurl, CURLOPT_FAILONERROR, true );
			curl_setopt( $_oCurl, CURLOPT_USERAGENT, $_sAgent );
			curl_setopt( $_oCurl, CURLOPT_TIMEOUT, 60 );
			curl_setopt( $_oCurl, CURLOPT_FOLLOWLOCATION, true );
			curl_setopt( $_oCurl, CURLOPT_URL, $sUrl . ( 'GET' == $sMethod  ? ( ! empty( $sQueryString ) ? '?' . $sQueryString : '' ) : '' ) );

			if ( null != $oHeaderCallback )
				cur_setopt( $_oCurl, CURLOPT_HEADERFUNCTION, $oHeaderCallback );

			if ( null != $oHeaderCallback )
				cur_setopt( $_oCurl, CURLOPT_READFUNCTION, $oReadCallback );

			//	If this is a post, we have to put the post data in another field...
			if ( 'POST' == $sMethod )
			{
				curl_setopt( $_oCurl, CURLOPT_URL, $sUrl );
				curl_setopt( $_oCurl, CURLOPT_POST, true );
				curl_setopt( $_oCurl, CURLOPT_POSTFIELDS, $sQueryString );
			}

			$_sResult = curl_exec( $_oCurl );
			curl_close( $_oCurl );
		}
		else
			throw new CException( '"libcurl" is required to use this functionality. Please reconfigure your php.ini to include "libcurl".' );

		return( $_sResult );
	}

	/**
	* Adds to the requestMap array
	*
	* @param string $sLabel
	* @param string $sParamName
	* @param bool $bRequired
	* @param array $arOptions
	* @param string $sApiName
	* @param string $sSubApiName
	* @return array
	* @see makeMapItem
	* @see makeMapArray
	*/
	protected function OLD____addRequestMapping( $sLabel, $sParamName = null, $bRequired = false, array $arOptions = null, $sApiName = null, $sSubApiName = null )
	{
		//	Save for next call
		static $_sLastApiName;
		static $_sLastSubApiName;

		//	Get our requestMap array...
		$_arReqMap =& $this->getOption( 'requestMap' );

		//	Set up statics so next call can omit those parameters.
		if ( $sApiName != $_sLastApiName && null != $sApiName )
			$_sLastApiName = $sApiName;

		if ( $sSubApiName != $_sLastSubApiName && null != $sSubApiName )
			$_sLastSubApiName = $sSubApiName;

		//	Build the options
		$_arTemp = array( 'name' => ( null != $sParamName ) ? $sParamName : $sLabel, 'required' => $bRequired );

		//	Add on supplied options
		if ( null != $arOptions )
			$_arTemp = array_merge( $_arTemp, $arOptions );

		//	Get array item ready for insertion
		if ( null != $_sLastApiName && null != $_sLastSubApiName )
			$_arReqMap[ $_sLastApiName ][ $_sLastApiName ] = $_arTemp;
		else if ( null != $_sLastApiName && null == $_sLastSubApiName )
			$_arFinal[ $sLabel ] = $_arTemp;
		else
			$_arFinal = $_arTemp;

		$_arReqMap[ $_sLastApiName ] = $_arFinal;

		return $_arFinal;
	}

	/**
	* Creates an array for requestMap
	*
	* @param array $arMap The map of items to insert into the array. Format is the same as (@link makeMapItem)
	* @param bool $bSetRequestMap If false, will NOT insert constructed array into (@link requestMap)
	* @returns array Returns the constructed array item ready to insert into your requestMap
	* @see makeMapItem
	*/
	protected function makeMapArray( $sApiName, $sSubApiName = null, array $arMap, $bSetRequestMap = true )
	{
		$_arFinal = array();

		foreach ( $arMap as $_sKey => $_oValue )
		{
			$_sLabel = ( in_array( 'label', $_oValue ) ) ? $_oValue[ 'name' ] : $_oValue[ 'label' ];
			$_bRequired = ( in_array( 'required', $_oValue ) ) ? $_oValue[ 'required' ] : false;
			$_arOptions = ( in_array( 'options', $_oValue ) ) ? $_oValue[ 'options' ] : null;
			$_sParamName = $_oValue[ 'name' ];

			if ( $bSetRequestMap )
				$this->addRequestMapping( $_sLabel, $_oValue[ 'name' ], $sApiName, $sSubApiName, $_bRequired, $_arOptions );
			else
			{
				$_arTemp = $this->makeMapItem( $_sLabel, $_sParamName, $_bRequired, $_arOptions );
 				$_arFinal[ $sApiName ] = ( ! is_array( $_arFinal[ $sApiName ] ) && sizeof( $_arFinal[ $sApiName ] ) > 0 ) ? $_arTemp : array_merge( $_arFinal[ $sApiName ], $_arTemp );
 			}
		}

		//	Return final array
		return( $_arFinal );
	}

	/**
	* Creates an entry for requestMap and inserts it into the array.
	*
	* @param string $sLabel The label or friendly name of this map item
	* @param string $sParamName The actual parameter name to send to API. If not specified, will default to $sLabel
	* @param bool $bRequired Set to true if the parameter is required
	* @param array $arOptions If supplied, will merge with generated options
	* @param array $arTargetArray If supplied, will insert into array
	* @returns array Returns the constructed array item ready to insert into your requestMap
	*/
	protected function makeMapItem( $sLabel, $sParamName = null, $bRequired = false, array $arOptions = null, array $arTargetArray = null )
	{
		//	Build default settings
		$_arMapOptions = array( 'name' => ( null != $sParamName ) ? $sParamName : $sLabel, 'required' => $bRequired );

		//	Add on supplied options
		if ( null != $arOptions )
			$_arMapOptions = array_merge( $_arMapOptions, $arOptions );

		//	Insert for caller if requested
		if ( null != $arTargetArray )
			$arTargetArray[ $sLabel ] = $_arMapOptions;

		//	Return our array
		return( $_arMapOptions );
	}
}
<?php
/**
 * CPSApiBehavior class file.
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://ps-yii-extensions.googlecode.com
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @license http://www.pogostick.com/license/
 */

/**
 * CPSApiBehavior provides a behavior to classes for making API calls
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @version SVN $Id$
 * @package psYiiExtensions
 * @subpackage Behaviors
 * @filesource
 * @since 1.0.5
 */
class CPSApiBehavior extends CPSComponentBehavior
{
	//********************************************************************************
	//* Constants
	//********************************************************************************

	/**
	* 'GET' Http method
	*/
	const HTTP_GET = 'GET';
	/**
	* 'PUT' Http method
	*/
	const HTTP_POST = 'POST';

	//********************************************************************************
	//* Constructor
	//********************************************************************************

	/***
	* Constructor
	*
	*/
	public function __construct()
	{
		//	Call daddy...
		parent::__construct();

		//	Add ours...
		$this->setOptions( self::getBaseOptions() );

		//	Log it and check for issues...
		CPSCommonBase::writeLog( Yii::t( $this->getInternalName(), '{class} constructed', array( "{class}" => get_class( $this ) ) ), 'trace', $this->getInternalName() );
	}

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	* Allows for single behaviors
	*
	*/
	private function getBaseOptions()
	{
		return(
			array(
				//	API options
				'altApiKey' => array( '_value' => '', '_validPattern' => array( 'type' => 'string' ) ),
				'apiBaseUrl' => array( '_value' => '', '_validPattern' => array( 'type' => 'string' ) ),
				'apiKey' => array( '_value' => '', '_validPattern' => array( 'type' => 'string' ) ),
				'apiQueryName' => array( '_value' => '', '_validPattern' => array( 'type' => 'string' ) ),
				'apiToUse' => array( '_value' => '', '_validPattern' => array( 'type' => 'string' ) ),
				'apiSubUrls' => array( '_value' => array(), '_validPattern' => array( 'type' => 'array' ) ),
				'format' => array( '_value' => 'array', '_validPattern' => array( 'type' => 'string' ) ),
				'httpMethod' => array( '_value' => self::HTTP_GET, '_validPattern' => array( 'type' => 'string' ) ),
				'requestData' => array( '_value' => array(), '_validPattern' => array( 'type' => 'array' ) ),
				'requestMap' => array( '_value' => array(), '_validPattern' => array( 'type' => 'array' ) ),
				'userAgent' => array( '_value' => 'Pogostick Components for Yii; (+http://www.pogostick.com/yii)', '_validPattern' => array( 'type' => 'string' ) ),
			)
		);
	}

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
	public function makeHttpRequest( $sUrl, $sQueryString = null, $sMethod = 'GET', $sUserAgent = null, $iTimeOut = 60, $oHeaderCallback = null, $oReaderCallback = null )
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
	* @param string $sLabel The "friendly" name for consumers
	* @param string $sParamName The name of the API variable, if null, $sLabel
	* @param bool $bRequired
	* @param array $arOptions
	* @param string $sApiName
	* @param string $sSubApiName
	* @return bool True if operation succeeded
	* @see makeMapItem
	* @see makeMapArray
	*/
	protected function addRequestMapping( $sLabel, $sParamName = null, $bRequired = false, array $arOptions = null, $sApiName = null, $sSubApiName = null )
	{
		//	Save for next call
		static $_sLastApiName;
		static $_sLastSubApiName;

		//	Set up statics so next call can omit those parameters.
		if ( null != $sApiName && $sApiName != $_sLastApiName )
			$_sLastApiName = $sApiName;

		if (  null != $sSubApiName && $sSubApiName != $_sLastSubApiName )
			$_sLastSubApiName = $sSubApiName;

		//	Build the options
		$_arTemp = array( 'name' => ( null != $sParamName ) ? $sParamName : $sLabel, 'required' => $bRequired );

		//	Add on any supplied options
		if ( null != $arOptions )
			$_arTemp = array_merge( $_arTemp, $arOptions );

		//	Add the mapping...
		if ( null == $_sLastApiName && null == $_sLastSubApiName )
			return false;

		//	Add mapping...
		$_arOptions =& $this->getOptions();
		$_arOptions[ 'requestMap' ][ '_value' ][ $_sLastApiName ][ $_sLastSubApiName ][ $sLabel ] = $_arTemp;

		return true;
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

}
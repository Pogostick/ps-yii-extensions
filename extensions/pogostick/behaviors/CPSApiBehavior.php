<?php
/*
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * CPSApiBehavior provides a behavior to classes for making API calls
 * 
 * @package 	psYiiExtensions
 * @subpackage 	behaviors
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id$
 * @since 		v1.0.5
 * 
 * @filesource
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
	//* Public Methods
	//********************************************************************************

	/***
	* Initialize
	*/
	public function preinit()
	{
		//	Call daddy...
		parent::preinit();

		//	Add ours...
		$this->addOptions( self::getBaseOptions() );
	}

	/**
	* Allows for single behaviors
	*
	*/
	private function getBaseOptions()
	{
		return(
			array(
				//	API options
				'altApiKey' => 'string:',
				'appendFormat' => 'boolean:false',
				'apiBaseUrl' => 'string:',
				'apiKey' => 'string:',
				'apiQueryName' => 'string:',
				'apiToUse' => 'string:',
				'apiSubUrls' => 'array:array()',
				'httpMethod' => 'string:' . self::HTTP_GET,
				'requestData' => 'array:array()',
				'requestMap' => 'array:array()',
				'requireApiQueryName' => 'boolean:false',
				'testApiKey' => 'string:',
				'testAltApiKey' => 'string:',
				'userAgent' => 'string:Pogostick Yii Extensions; (+http://www.pogostick.com/yii)',
				'lastErrorMessage' => 'string:',
				'lastErrorMessageExtra' => 'string:',
				'lastErrorCode' => 'string:',
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
	 * @param array $arHeaders Headers to add to the request
	 * @param function|array $oHeaderCallback The callback function to call after the header has been read. Accepts function reference or array( object, method )
	 * @param function|array $oReadCallback The callback function to call after the body has been read. Accepts function reference or array( object, method )
	 * @return mixed The data returned from the HTTP request or null for no data
	 */
	public function makeHttpRequest( $sUrl, $sQueryString = null, $sMethod = 'GET', $sUserAgent = null, $iTimeOut = 60, $arHeaders = null, $oHeaderCallback = null, $oReaderCallback = null )
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
			curl_setopt( $_oCurl, CURLOPT_VERBOSE, true );
			curl_setopt( $_oCurl, CURLOPT_FOLLOWLOCATION, true );
			curl_setopt( $_oCurl, CURLOPT_URL, $sUrl . ( 'GET' == $sMethod  ? ( ! empty( $sQueryString ) ? '?' . $sQueryString : '' ) : '' ) );

			if ( null != $arHeaders )
				curl_setopt( $_oCurl, CURLOPT_HTTPHEADER, $arHeaders );

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
			$_e = curl_errno( $_oCurl );
			$_em = curl_error( $_oCurl );
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
	public function addRequestMapping( $sLabel, $sParamName = null, $bRequired = false, array $arOptions = null, $sApiName = null, $sSubApiName = '/' )
	{
		//	Save for next call
		static $_sLastApiName;
		static $_sLastAction;

		//	Set up statics so next call can omit those parameters.
		if ( null != $sApiName && $sApiName != $_sLastApiName )
			$_sLastApiName = $sApiName;

		if (  null != $sSubApiName && $sSubApiName != $_sLastAction )
			$_sLastAction = $sSubApiName;

		//	Build the options
		$_arTemp = array( 'name' => ( null != $sParamName ) ? $sParamName : $sLabel, 'required' => $bRequired );

		//	Add on any supplied options
		if ( null != $arOptions )
			$_arTemp = array_merge( $_arTemp, $arOptions );

		//	Add the mapping...
		if ( null == $_sLastApiName && null == $_sLastAction )
			return false;

		//	Add mapping...
		$_arOptions =& $this->getOptions();
		$_arOptions[ 'requestMap' ][ $_sLastApiName ][ $_sLastAction ][ $sLabel ] = $_arTemp;

		return true;
	}

}
<?php
/**
 * CPSHttpClient.php
 */
 
/**
 * CPSHttpClient
 */
class CPSHttpClient extends CPSComponent
{
	//*************************************************************************
	//* Class Constants 
	//*************************************************************************

	/**
	 * @constant string HTTP methods
	 */
	const
		HTTP_GET = 'GET',
		HTTP_POST = 'POST',
		HTTP_PUT = 'PUT',
		HTTP_DELETE = 'DELETE'
	;

	//*************************************************************************
	//* Public Methods 
	//*************************************************************************

	/**
	 * Retrieves the resource via GET
	 * @static
	 * @param string $url
	 * @param array $parameters
	 * @param array $curlOptions
	 * @return mixed
	 */
	public static function get( $url, $parameters = array(), $curlOptions = array(), $callback = null )
	{
		CPSLog::trace( __METHOD__, 'GET: ' . $url );
		return self::_http( $url, self::HTTP_GET, $parameters, $curlOptions, $callback );
	}

	/**
	 * Posts to the resource
	 * @static
	 * @param string $url
	 * @param array $parameters
	 * @param array $curlOptions
	 * @return mixed
	 */
	public static function post( $url, $parameters = array(), $curlOptions = array(), $callback = null )
	{
		return self::_http( $url, self::HTTP_POST, $parameters, $curlOptions, $callback );
	}

	/**
	 * Sends http DELETE to the resource
	 * @static
	 * @param string $url
	 * @param array $parameters
	 * @param array $curlOptions
	 * @return mixed
	 */
	public static function delete( $url, $parameters = array(), $curlOptions = array(), $callback = null )
	{
		return self::_http( $url, self::HTTP_DELETE, $parameters, $curlOptions, $callback );
	}

	/**
	 * PUTs to the resource
	 * @static
	 * @param string $url
	 * @param array $parameters
	 * @param array $curlOptions
	 * @return mixed
	 */
	public static function put( $url, $parameters = array(), $curlOptions = array(), $callback = null )
	{
		return self::_http( $url, self::HTTP_PUT, $parameters, $curlOptions, $callback );
	}

	/**
	 * Make an HTTP request
	 *
	 * @param string $url The URL to call
	 * @param string $payload The query string to attach
	 * @param string $method The HTTP method to use. Can be 'GET', 'POST', 'PUT', or 'DELETE'
	 * @param mixed $sNewAgent The custom user method to use. Defaults to 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; .NET CLR 2.0.50727; .NET CLR 3.0.04506; InfoPath.3)'
	 * @param integer $iTimeOut The number of seconds to wait for a response. Defaults to 60 seconds
	 * @return mixed The data returned from the HTTP request or null for no data
	 */
	protected static function _http( $url, $method = 'GET', $payload = null, $curlOptions = array(), $callback = null )
	{
		//	Our return results
		$_payload = $payload;

		//	Convert array to KVPs...
		if ( is_array( $payload ) )
		{
			$_payload = null;

			foreach ( $payload as $_key => $_value )
				$_payload .= "&{$_key}={$_value}";
		}

		$_cookieJar = tempnam( '/tmp', 'curl.cookie.' . getmypid() );

		//	Set up our options
		$_options =	array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_COOKIEJAR => $_cookieJar,
			CURLOPT_AUTOREFERER => true,
			CURLOPT_URL => $url,
			CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1',
		);

		//	Add in the callers options
		foreach ( $curlOptions as $_key => $_value )
			$_options[$_key] = $_value;

		//	If this is a post, we have to put the post data in another field...
		switch ( $method )
		{
			case 'GET':
				if ( 'GET' == $method && ! empty( $_payload ) )
					$curlOptions[CURLOPT_URL] = rtrim( $url, '/' ) . '/?' . rtrim( $_payload, '&' );
				break;

			case 'POST':
				$curlOptions[CURLOPT_POST] = true;
				$curlOptions[CURLOPT_POSTFIELDS] = $_payload;
				break;

			case 'PUT':
				$curlOptions[CURLOPT_PUT] = true;
				$curlOptions[CURLOPT_INFILE] = $_payload;
				$curlOptions[CURLOPT_INFILESIZE] = strlen( $_payload );
				break;

			case 'DELETE':
				$curlOptions[CURLOPT_CUSTOMREQUEST] = 'DELETE';
				$curlOptions[CURLOPT_POSTFIELDS] = $_payload;
				break;
		}
echo $url;
		$_curl = curl_init();
		curl_setopt_array( $_curl, $_options );

		if ( false === ( $_result = curl_exec( $_curl ) ) )
			$_result = curl_getinfo( $_curl );

		curl_close( $_curl );

		//	Call  callback with results if requested...
		if ( is_callable( $callback ) )
			call_user_func( $callback, $_result );

		return $_result;
	}

	//*************************************************************************
	//* Private Methods 
	//*************************************************************************

	//*************************************************************************
	//* Properties
	//*************************************************************************

}

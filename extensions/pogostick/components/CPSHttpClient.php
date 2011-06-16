<?php
/**
 * CPSHttpClient.php
 */

/**
 * CPSHttpClient
 * Encapsulates common HTTP requests
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
	//* Private Members
	//*************************************************************************

	/**
	 * @static
	 * @var array
	 */
	protected static $_lastResponse = null;

	//*************************************************************************
	//* Public Methods
	//*************************************************************************

	/**
	 * Retrieves the resource via GET
	 * @static
	 * @param string $url
	 * @param array $parameters
	 * @param array $curlOptions
	 * @param null $callback
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
	 * @param callback $callback
	 * @return mixed
	 */
	public static function post( $url, $parameters = array(), $curlOptions = array(), $callback = null )
	{
		CPSLog::trace( __METHOD__, 'POST: ' . $url . ' : ' . print_r( $curlOptions, true ) );
		return self::_http( $url, self::HTTP_POST, $parameters, $curlOptions, $callback );
	}

	/**
	 * Sends http DELETE to the resource
	 * @static
	 * @param string $url
	 * @param array $parameters
	 * @param array $curlOptions
	 * @param callback $callback
	 * @return mixed
	 */
	public static function delete( $url, $parameters = array(), $curlOptions = array(), $callback = null )
	{
		CPSLog::trace( __METHOD__, 'DELETE: ' . $url );
		return self::_http( $url, self::HTTP_DELETE, $parameters, $curlOptions, $callback );
	}

	/**
	 * PUTs to the resource
	 * @static
	 * @param string $url
	 * @param array $parameters
	 * @param array $curlOptions
	 * @param callback $callback
	 * @return mixed
	 */
	public static function put( $url, $parameters = array(), $curlOptions = array(), $callback = null )
	{
		CPSLog::trace( __METHOD__, 'PUT: ' . $url );
		return self::_http( $url, self::HTTP_PUT, $parameters, $curlOptions, $callback );
	}

	/**
	 * Make an HTTP request
	 *
	 * @param string $url The URL to call
	 * @param string $method The HTTP method to use. Can be 'GET', 'POST', 'PUT', or 'DELETE'
	 * @param string $payload The query string to attach
	 * @param array $curlOptions
	 * @param callback $callback
	 * @return mixed The data returned from the HTTP request or null for no data
	 */
	protected static function _http( $url, $method = 'GET', $payload = null, $curlOptions = array(), $callback = null )
	{
		CPSLog::trace( __METHOD__, 'HttpRequest: "' . $method . '" with payload "' . print_r( $payload, true ) );

		//	Our return results
		$_payload = $payload;

		//	Convert array to KVPs...
		if ( is_array( $payload ) )
		{
			$_payload = null;

			foreach ( $payload as $_key => $_value )
				$_payload .= "&{$_key}={$_value}";
		}

//		$_cookieJar = tempnam( '/tmp', 'curl.cookie.' . getmypid() );

		//	Set up our default options
		$_options =	array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_FOLLOWLOCATION => true,
//			CURLOPT_COOKIEJAR => $_cookieJar,
		);

		//	Now merge in the caller's options
		foreach ( $curlOptions as $_key => $_value )
			$_options[$_key] = $_value;

		//	Various methods require various tweaks
		switch ( $method )
		{
			case 'GET':
				if ( 'GET' == $method && ! empty( $_payload ) )
					$url = rtrim( $url, '/' ) . '/?' . rtrim( $_payload, '&' );

				$_options[CURLOPT_HTTPGET] = true;
				break;

			case 'POST':
				$_options[CURLOPT_POST] = true;
				$_options[CURLOPT_POSTFIELDS] = $_payload;
				break;

			case 'PUT':
				$_options[CURLOPT_PUT] = true;
				$_options[CURLOPT_INFILE] = $_payload;
				$_options[CURLOPT_INFILESIZE] = strlen( $_payload );
				break;

			case 'DELETE':
				$_options[CURLOPT_CUSTOMREQUEST] = 'DELETE';
				$_options[CURLOPT_POSTFIELDS] = $_payload;
				break;
		}

		//	Set the url
		$_options[CURLOPT_URL] = $url;

		//	Initialize and set options
		$_curl = curl_init();
		curl_setopt_array( $_curl, $_options );

		//	Make the request
		$_result = curl_exec( $_curl );
		self::$_lastResponse = curl_getinfo( $_curl );

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

	/**
	 * @return array
	 */
	public static
	function getLastResponse()
	{
		return self::$_lastResponse;
	}

}

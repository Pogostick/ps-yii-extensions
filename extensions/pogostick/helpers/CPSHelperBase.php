<?php
/*
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * Base functionality that I want in ALL helper classes
 * 
 * @package 	psYiiExtensions
 * @subpackage 	helpers
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id$
 * @since 		v1.0.5
 *  
 * @filesource
 */
class CPSHelperBase extends CHtml implements IPSBase
{
	//********************************************************************************
	//* Constants for all components
	//********************************************************************************

	/**
	* Standard output formats
	*/
	const	OF_JSON 		= 0;
	const	OF_HTTP 		= 1;
	const	OF_ASSOC_ARRAY 	= 2;
	
	//********************************************************************************
	//* Private Members
	//********************************************************************************
	
	/**
	* Cache the client script object for speed
	* @var CClientScript
	*/
	protected static $m_oClientScript = null;
	
	/**
	 * An array of class names to search in for missing methods
	 * @var array
	 */
	protected static $m_arClassPath = array();
	public static function getClassPath() { return self::$m_arClassPath; }
	public static function setClassPath( $arClasses ) { self::$m_arClassPath = $arClasses; }
	public static function addClassToPath( $sClass ) { self::$m_arClassPath[] = $sClass; }
	
	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	* Creates the internal name of a component/widget. Use (@link setInternalName) to change.
	* @param IPSBase $oComponent
	* @returns IPSComponent
	*/
	public static function createInternalName( IPSComponent $oComponent )
	{
		//	Get the class...
		$_sClass = get_class( $oComponent );

		//	Set names (with a little Pogostick magic!)
		$_sIntName = ( false !== strpos( $_sClass, 'PS', 0 ) ) ? str_replace( 'PS', 'ps', $_sClass ) : $_sClass;

		//	Set the names inside the object
		$oComponent->setInternalName( $_sIntName );

		//	Return
		return $oComponent;
	}

	/**
	* If value is not set or empty, last passed in argument is returned
	* Allows for multiple nvl chains ( nvl(x,y,z,null) )
	* Since PHP evaluates the arguments before calling a function, this is NOT a short-circuit method.
	* 
	* @param mixed 
	* @returns mixed
	*/
	public static function nvl()
	{
		$_oDefault = null;
		$_iArgs = func_num_args();
		$_arArgs = func_get_args();
		
		for ( $_i = 0; $_i < $_iArgs; $_i++ )
		{
			if ( isset( $_arArgs[ $_i ] ) && ! empty( $_arArgs[ $_i ] ) )
				return $_arArgs[ $_i ];
				
			$_oDefault = $_arArgs[ $_i ];
		}

		return $_oDefault;
	}
	
	/**
	* Returns an analog to Java System.currentTimeMillis()
	* 
	* @returns integer
	*/
	public static function currentTimeMillis()
	{
		list( $_uSec, $_sec ) = explode( ' ', microtime() );
		return ( ( float )$_uSec + ( float )$_sec );
	}
	
	/**
	 * Alias for {@link CPSHelperBase::getOption)
	* @param array $arOptions
	* @param string $sKey
	* @param mixed $oDefault
	* @param boolean $bUnset
	* @returns mixed
	* @access public
	* @static
	* @see CPSHelperBase::getOption
	 */
	public static function o( &$arOptions = array(), $sKey, $oDefault = null, $bUnset = false )
	{
		return self::getOption( $arOptions, $sKey, $oDefault, $bUnset );
	}
	
	/**
	* Retrieves an option from the given array. 
	* $oDefault is set and returned if $sKey is not 'set'. Optionally will unset option in array.
	*
	* @param array $arOptions
	* @param string $sKey
	* @param mixed $oDefault
	* @param boolean $bUnset
	* @returns mixed
	* @access public
	* @static
	*/
	public static function getOption( &$arOptions = array(), $sKey, $oDefault = null, $bUnset = false )
	{
		$_oValue = $oDefault;
		
		if ( is_array( $arOptions ) )
		{
			if ( ! array_key_exists( $sKey, $arOptions ) )
			{
				//	Ignore case and look...
			    $_sNewKey = strtolower( $sKey );
			    foreach ( $arOptions as $_sKey => $_sValue )
			    {
		    		if ( strtolower( $_sKey ) == $_sNewKey )
		    		{
		    			//	Set correct key and break
		    			$sKey = $_sKey;
		    			break;
					}
				}
	        }
			
			if ( isset( $arOptions[ $sKey ] ) )
			{
				$_oValue = $arOptions[ $sKey ];
				if ( $bUnset ) unset( $arOptions[ $sKey ] );
			}
			               
			//	Set it in the array if not an unsetter...
			if ( ! $bUnset ) $arOptions[ $sKey ] = $_oValue;
		}

		//	Return...
		return $_oValue;
	}

	/**
	* Sets an option in the given array. Alias of {@link CPSHelperBase::setOption}
	* @param array $arOptions
	* @param string $sKey
	* @param mixed $oValue
	* @returns mixed The new value of the key
	* @static
	*/
	public static function so( array &$arOptions, $sKey, $oValue = null )
	{
		return self::setOption( $arOptions, $sKey, $oValue );
	}
	
	/**
	* Sets an option in the given array
	*
	* @param array $arOptions
	* @param string $sKey
	* @param mixed $oValue
	* @returns mixed The new value of the key
	* @static
	*/
	public static function setOption( array &$arOptions, $sKey, $oValue = null )
	{
		return $arOptions[ $sKey ] = $oValue;
	}

	/**
	* Unsets an option in the given array. Alias of {@link CPSHelperBase::unsetOption}
	*
	* @param array $arOptions
	* @param string $sKey
	* @returns mixed The new value of the key
	* @static
	*/
	public static function uo( array &$arOptions, $sKey )
	{
		self::unsetOption( $arOptions, $sKey, null );
	}
	
	/**
	* Unsets an option in the given array
	*
	* @param array $arOptions
	* @param string $sKey
	* @returns mixed The new value of the key
	* @static
	*/
	public static function unsetOption( array &$arOptions, $sKey )
	{
		if ( in_array( $sKey, $arOptions ) )
			unset( $arOptions[$sKey] );
	}
	
	/**
	* Merges an array without overwriting. Accepts multiple array arguments
	* If an index exists in the target array, it is appended to the value.
	* @returns array
	*/
	public static function smart_array_merge()
	{
		$_iCount = func_num_args();
		$_arResult = array();
		
		for ( $_i = 0; $_i < $_iCount; $_i++ )
		{
			foreach ( func_get_arg( $_i ) as $_sKey => $_oValue )
			{
				if ( isset( $_arResult[ $_sKey ] ) ) $_oValue = $_arResult[ $_sKey ] . ' ' . $_oValue;
				$_arResult[ $_sKey ] = $_oValue;
			}
		}
		
		return $_arResult;
	}

	/**
	 * Make an HTTP request
	 *
	 * @param string $sUrl The URL to call
	 * @param string $sQueryString The query string to attach
	 * @param string $sMethod The HTTP method to use. Can be 'GET' or 'SET'
	 * @param mixed $sNewAgent The custom user method to use. Defaults to 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; .NET CLR 2.0.50727; .NET CLR 3.0.04506; InfoPath.3)'
	 * @param integer $iTimeOut The number of seconds to wait for a response. Defaults to 60 seconds
	 * @return mixed The data returned from the HTTP request or null for no data
	 */
	public static function makeHttpRequest( $sUrl, $sQueryString = null, $sMethod = 'GET', $sNewAgent = null, $iTimeOut = 60 )
	{
		//	Our user-agent string
		$_sAgent = PS::nvl( $sNewAgent, 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; .NET CLR 2.0.50727; .NET CLR 3.0.04506; InfoPath.3)' );

		//	Our return results
		$_sResult = null;

		// Use CURL if installed...
		if ( function_exists( 'curl_init' ) )
		{
			$_oCurl = curl_init();
			curl_setopt( $_oCurl, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $_oCurl, CURLOPT_FAILONERROR, true );
			curl_setopt( $_oCurl, CURLOPT_USERAGENT, $_sAgent );
			curl_setopt( $_oCurl, CURLOPT_TIMEOUT, 60 );
			curl_setopt( $_oCurl, CURLOPT_FOLLOWLOCATION, true );
			curl_setopt( $_oCurl, CURLOPT_URL, $sUrl . ( 'GET' == $sMethod ? ( ! empty( $sQueryString ) ? "?" . $sQueryString : '' ) : '' ) );

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
			throw new Exception( '"libcurl" is required to use this functionality. Please reconfigure your php.ini to include "libcurl".' );

		return $_sResult;
	}

	/**
	* Parse HTML field for a tag...
	*
	* @param string $sData
	* @param string $sTag
	* @param string $sTagEnd
	* @param integer $iStart Defaults to 0
	* @param string $sNear
	* @return string
	*/
	public static function suckTag( $sData, $sTag, $sTagEnd, $iStart = 0, $sNear = null )
	{
		$_sResult = "";
		$_l = strlen( $sTag );

		//	If near value given, get position of that as start
		if ( $sNear != null )
		{
			$_iStart = stripos( $sData, $sNear, $iStart );
			if ( $_iStart >= 0 )
				$iStart = $_iStart + strlen( $sNear );
		}

		$_i = stripos( $sData, $sTag, $iStart );
		$_k = strlen( $sTagEnd );

		if ( $_i !== false )
		{
			$_j = stripos( $sData, $sTagEnd, $_i + $_l );

			if ( $_j >= 0 )
			{
				$iStart = $_i;
				$_sResult = substr( $sData, $_i + $_l,  $_j - $_i - $_l );
			}

			return( trim( $_sResult ) );
		}

		return( null );
	}

	/**
	* Checks to see if the passed in data is an Url
	*
	* @param string $sData
	* @return boolean
	*/
	public static function isUrl( $sData )
	{
		return( ( @parse_url( $sData ) ) ? TRUE : FALSE );
	}

	/**
	 * Generates a span or div wrapped hyperlink tag.
	 *
	 * @param string $sText The link body. It will NOT be HTML-encoded. Therefore you can pass in HTML code such as an image tag.
	 * @param string $sUrl The Url of the link
	 * @param string $sWrapperId The "id" of the created wrapper
	 * @param array $arHtmlOptions Additional HTML attributes. Besides normal HTML attributes, a few special attributes are also recognized (see {@link clientChange} for more details.)
	 * @param string $sClass The optional class of the created span
	 * @param boolean $bUseDiv If true, a <div> tag will be used instead of a <span>
	 * @return string The generated hyperlink
	 */
	public static function wrappedLink( $sText, $sUrl = '#', $sWrapperId = null, $arHtmlOptions = array(), $sClass = null, $bUseDiv = false )
	{
		return( '<' .
			( $bUseDiv ? 'div' : 'span' ) .
			( null != $sWrapperId ? ' id="' . $sWrapperId . '"' : '' ) .
			( null != $sClass ? ' class="' . $sClass . '"' : '' ) . '>' .
			CHtml::link( $sText, $sUrl, $arHtmlOptions ) .
			'</' . ( $bUseDiv ? 'div' : 'span' ) . '>'
		);
	}

	/**
	* Checks for an empty variable.
	*
	* Useful because the PHP empty() function cannot be reliably used with overridden __get methods.
	*
	* @param mixed $oVar
	* @return bool
	*/
	public static function isEmpty( $oVar )
	{
		return empty( $oVar );
	}

	/**
	* Converts an array to Xml
	*
	* @param mixed $arData The array to convert
	* @param mixed $sRootNodeName The name of the root node in the returned Xml
	* @param string $sXml The converted Xml
	*/
	public static function arrayToXml( $arData, $sRootNodeName = 'data', $sXml = null )
	{
		// turn off compatibility mode as simple xml doesn't like it
		if ( 1 == ini_get( 'zend.ze1_compatibility_mode' ) )
			ini_set( 'zend.ze1_compatibility_mode', 0 );

		if ( null == $sXml )
			$sXml = simplexml_load_string( "<?xml version='1.0' encoding='utf-8'?><{$sRootNodeName} />" );

		// loop through the data passed in.
		foreach ( $arData as $_sKey => $_oValue )
		{
			// no numeric keys in our xml please!
			if ( is_numeric($_sKey ) )
				$_sKey = "unknownNode_". ( string )$_sKey;

			// replace anything not alpha numeric
			$_sKey = preg_replace( '/[^a-z]/i', '', $_sKey );

			// if there is another array found recrusively call this function
			if ( is_array( $_oValue ) )
			{
				$_oNode = $sXml->addChild( $_sKey );
				self::arrayToXml( $_oValue, $sRootNodeName, $_oNode );
			}
			else
			{
				// add single node.
				$_oValue = htmlentities( $_oValue );
				$sXml->addChild( $_sKey, $_oValue );
			}
		}

		return( $sXml->asXML() );
	}
	
	/**
	* Returns the Url of the currently loaded page.
	* @returns string
	*/
	public static function getCurrentPageUrl()
	{
		$_bSSL = ( isset( $_SERVER[ 'HTTPS' ] ) && $_SERVER[ 'HTTPS' ] == 'on' );
		return 'http' . ( ( $_bSSL ) ? 's' : '' ) . '://' . $_SERVER[ "SERVER_NAME" ] . ( ( $_SERVER[ "SERVER_PORT" ] != "80" ) ? ":" . $_SERVER[ "SERVER_PORT" ] : '' ) .  $_SERVER[ "REQUEST_URI" ];
	}
	
	/**
	* Go out and pull the {@link http://gravatar.com/ gravatar} url for the specificed email address.
	* 
	* @access public
	* @static
	* @param string $sEmailAddress
	* @param integer $iSize The size of the image to return from 1px to 512px. Defaults to 80
	* @param string $sRating The rating of the image to return: G, PG, R, or X. Defaults to G
	* @since psYiiExtensions v1.0.4
	*/
	public static function getGravatarUrl( $sEmailAddress, $iSize = 80, $sRating = 'g' )
	{
		$sRating = strtolower( $sRating{0} );
		$iSize = intval( $iSize );
		if ( $iSize < 1 || $iSize > 512 ) throw new CPSException( '"$iSize" parameter is out of bounds. Must be between 1 and 512.' );
		if ( ! in_array( $sRating, array( 'g', 'pg', 'r', 'x' ) ) ) throw new CPSException( '"$sRating" parameter must be either "G", "PG", "R", or "X".' );
		
		return "http://www.gravatar.com/avatar/" . md5( strtolower( $sEmailAddress ) ) . ".jpg?s={$iSize}&r={$sRating}";
	}
	
	/**
	* Takes parameters and returns an array of the values.
	* 
	* @param string|array $oData,... One or more values to read and put into the return array.
	* @returns array
	*/
	public static function makeArray( $oData )
	{
		$_arOut = array();
		$_iCount = func_num_args();
		
		for ( $_i = 0; $_i < $_iCount; $_i++ )
		{
    		//	Any other columns to touch?
    		if ( null !== ( $_oArg = func_get_arg( $_i ) ) )
    		{
    			if ( ! is_array( $_oArg ) )
    				$_arOut[] = $_oArg;
    			else
    			{
    				foreach ( $_oArg as $_sValue )
    					$_arOut[] = $_sValue;
				}
			}
		}
		
		//	Return the fresh array...
		return $_arOut;
	}
	
	//********************************************************************************
	//* Yii Convenience Mappings
	//********************************************************************************
	
	/***
	 * Retrieves and caches the Yii ClientScript object
	 * @returns CClientScript
 	 * @access public
	 * @static
	 */
	public static function getClientScript() 
	{ 
		return self::$m_oClientScript ? self::$m_oClientScript : self::$m_oClientScript = Yii::app()->getClientScript(); 
	}

	/**
	* Returns the current clientScript object. Caches for subsequent calls...
	* @returns CClientScript
	* @access public
	* @static
	*/
	public static function _cs() 
	{ 
		return self::getClientScript();
	}

	/**
	* Registers a CSS file
	* 
	* @param string URL of the CSS file
	* @param string media that the CSS file should be applied to. If empty, it means all media types.
	* @access public
	* @static
	*/
	public static function _rcf( $sUrl, $sMedia = '' )
	{
		return self::_cs()->registerCssFile( $sUrl, $sMedia );
	}

	/**
	* Registers a javascript file.
	* 
	* @param string URL of the javascript file
	* @param integer the position of the JavaScript code. Valid values include the following:
	* <ul>
	* <li>CClientScript::POS_HEAD : the script is inserted in the head section right before the title element.</li>
	* <li>CClientScript::POS_BEGIN : the script is inserted at the beginning of the body section.</li>
	* <li>CClientScript::POS_END : the script is inserted at the end of the body section.</li>
	* </ul>
	* @access public
	* @static
	*/
	public static function registerScriptFile( $sUrl, $ePosition = self::POS_HEAD )
	{
		return self::_rsf( $sUrl, $ePosition );
	}

	/**
	* Registers a CSS file
	* 
	* @param string URL of the CSS file
	* @param string media that the CSS file should be applied to. If empty, it means all media types.
	* @access public
	* @static
	*/
	public static function registerCssFile( $sUrl, $sMedia = '' )
	{
		return self::_rcf( $sUrl, $sMedia );
	}

	/**
	* Registers a piece of CSS code.
	* 
	* @param string ID that uniquely identifies this piece of CSS code
	* @param string the CSS code
	* @param string media that the CSS code should be applied to. If empty, it means all media types.
	* @access public
	* @static
	*/
	public static function _rc( $sId, $sCss, $sMedia = '' )
	{
		return self::_cs()->registerCss( $sId, $sCss, $sMedia );
	}

	/**
	* Registers a piece of CSS code.
	* 
	* @param string ID that uniquely identifies this piece of CSS code
	* @param string the CSS code
	* @param string media that the CSS code should be applied to. If empty, it means all media types.
	* @access public
	* @static
	*/
	public static function registerCss( $sId, $sCss, $sMedia = '' )
	{
		return self::_rc( $sId, $sCss, $sMedia );
	}

	/**
	* Registers a javascript file.
	* 
	* @param string URL of the javascript file
	* @param integer the position of the JavaScript code. Valid values include the following:
	* <ul>
	* <li>CClientScript::POS_HEAD : the script is inserted in the head section right before the title element.</li>
	* <li>CClientScript::POS_BEGIN : the script is inserted at the beginning of the body section.</li>
	* <li>CClientScript::POS_END : the script is inserted at the end of the body section.</li>
	* </ul>
	* @access public
	* @static
	*/
	public static function _rsf( $sUrl, $ePosition = CClientScript::POS_HEAD )
	{
		self::_cs()->registerScriptFile( $sUrl, $ePosition );
	}

	/**
	* Registers a piece of javascript code.
	* 
	* @param string ID that uniquely identifies this piece of JavaScript code
	* @param string the javascript code
	* @param integer the position of the JavaScript code. Valid values include the following:
	* <ul>
	* <li>CClientScript::POS_HEAD : the script is inserted in the head section right before the title element.</li>
	* <li>CClientScript::POS_BEGIN : the script is inserted at the beginning of the body section.</li>
	* <li>CClientScript::POS_END : the script is inserted at the end of the body section.</li>
	* <li>CClientScript::POS_LOAD : the script is inserted in the window.onload() function.</li>
	* <li>CClientScript::POS_READY : the script is inserted in the jQuery's ready function.</li>
	* </ul>
	* @access public
	* @static
	*/
	public static function _rs( $sId, $sScript, $ePosition = CClientScript::POS_READY )
	{
		self::_cs()->registerScript( $sId, $sScript, $ePosition );
	}

	/**
	* Registers a piece of javascript code.
	* 
	* @param string ID that uniquely identifies this piece of JavaScript code
	* @param string the javascript code
	* @param integer the position of the JavaScript code. Valid values include the following:
	* <ul>
	* <li>CClientScript::POS_HEAD : the script is inserted in the head section right before the title element.</li>
	* <li>CClientScript::POS_BEGIN : the script is inserted at the beginning of the body section.</li>
	* <li>CClientScript::POS_END : the script is inserted at the end of the body section.</li>
	* <li>CClientScript::POS_LOAD : the script is inserted in the window.onload() function.</li>
	* <li>CClientScript::POS_READY : the script is inserted in the jQuery's ready function.</li>
	* </ul>
	* @access public
	* @static
	*/
	public static function registerScript( $sId, $sScript, $ePosition = CClientScript::POS_READY )
	{
		return self::_rs( $sId, $sScript, $ePosition );
	}

	/**
	* Registers a meta tag that will be inserted in the head section (right before the title element) of the resulting page.
	* 
	* @param string content attribute of the meta tag
	* @param string name attribute of the meta tag. If null, the attribute will not be generated
	* @param string http-equiv attribute of the meta tag. If null, the attribute will not be generated
	* @param array other options in name-value pairs (e.g. 'scheme', 'lang')
	* @access public
	* @static
	*/
	public static function _rmt( $sContent, $sName = null, $sHttpEquiv = null, $arOptions = array() )
	{
		self::_cs()->registerMetaTag( $sContent, $sName, $sHttpEquiv, $arOptions );
	}
	
	/**
	* Registers a meta tag that will be inserted in the head section (right before the title element) of the resulting page.
	* 
	* @param string content attribute of the meta tag
	* @param string name attribute of the meta tag. If null, the attribute will not be generated
	* @param string http-equiv attribute of the meta tag. If null, the attribute will not be generated
	* @param array other options in name-value pairs (e.g. 'scheme', 'lang')
	* @access public
	* @static
	*/
	public static function registerMetaTag( $sContent, $sName = null, $sHttpEquiv = null, $arOptions = array() )
	{
		return self::_rmt( $sContent, $sName, $sHttpEquiv, $arOptions );
	}

	//********************************************************************************
	//* Magic Methods
	//********************************************************************************
	
	/**
	 * Calls a static method in classPath if not found here. Allows you to extend this object
	 * at runtime with additional helpers.
	 * 
	 * Only available in PHP 5.3+
	 * 
	 * @param string $sMethod
	 * @param array $arParams
	 * @return mixed
	 */
	public static function __callStatic( $sMethod, $arParams )
	{
		foreach ( self::$m_arClassPath as $_sClass )
		{
			if ( method_exists( $_sClass, $sMethod ) ) 
				return call_user_func_array( $_sClass . '::' . $sMethod, $arParams );
		}
	}

}
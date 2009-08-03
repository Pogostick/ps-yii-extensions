<?php
/**
 * CPSHelp class file.
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://www.pogostick.com.com/
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @license http://www.pogostick.com/license/
 */

/**
 * CPSHelp provides a slew of static helper methods
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @version $Id$
 * @package psYiiExtensions
 * @subpackage Helpers
 */
class CPSHelp
{
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
		$_sAgent = $sNewAgent;

		if ( $sNewAgent == "" )
			$_sAgent = "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; .NET CLR 2.0.50727; .NET CLR 3.0.04506; InfoPath.3)";

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

		return( $_sResult );
	}

	/**
	* Retrieves an option from the given array. 
	* 
	* $oDefault is set and returned if $sKey is not 'set'. Optionally will unset option in array.
	*
	* @access public
	* @static
	* @param array $arOptions
	* @param string $sKey
	* @param mixed $oDefault
	* @param boolean $bUnset
	* @returns mixed
	*/
	public static function getOption( &$arOptions, $sKey, $oDefault = null, $bUnset = false )
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
	* Sets an option in the given array
	*
	* @param array $arOptions
	* @param string $sKey
	* @param mixed $oValue
	* @returns mixed The new value of the key
	* @static
	*/
	public static function setOption( array $arOptions, $sKey, $oValue = null )
	{
		return $arOptions[ $sKey ] = $oValue;
	}

	/**
	* Unsets an option in the given array
	*
	* @param array $arOptions
	* @param string $sKey
	* @returns mixed The new value of the key
	* @static
	*/
	public static function unsetOption( array $arOptions, $sKey )
	{
		return self::setOption( $arOptions, $sKey, null );
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
	* Returns an Xml string representation of an array of CActiveRecords
	*
	* @param array $oRS
	* @param array $arOptions
	*/
	public static function asXml( $arData, $arOptions = null )
	{
		//	Initialize...
		$_sHeader = '';
		$_sOut = '';
		$_oSchema = null;

		//	Process options...
		$_bjqGrid = self::getOption( $arOptions, 'jqGrid', false );

		//	Default options for jqGrid
		if ( $_bjqGrid )
		{
			$_sContainer = 'row';
			$_sElement = 'cell';
			$_bUseColumnElementNames = false;
			$_bIdInAttribute = true;
			$_sIdName = 'id';
			$_bUseCDataForStrings = true;
			$_bEncloseResults = true;
			$_sEncloseTag = 'rows';
			$_arAddElements = self::getOption( $arOptions, 'addElements', null );
			$_bAddTypes = false;
		}
		else
		{
			$_sContainer = self::getOption( $arOptions, 'container', 'row' );
			$_sElement = self::getOption( $arOptions, 'element', 'cell' );
			$_bUseColumnElementNames = self::getOption( $arOptions, 'useColumnElementNames', false );
			$_bIdInAttribute = self::getOption( $arOptions, 'idInAttribute', true );
			$_bUseCDataForStrings = self::getOption( $arOptions, 'useCDataForStrings', true );
			$_bEncloseResults = self::getOption( $arOptions, 'encloseResults', false );
			$_sEncloseTag = self::getOption( $arOptions, 'encloseTag', 'rows' );
			$_bAddTypes = self::getOption( $arOptions, 'addTypes', false );
			$_sAddTypeAttributeName = self::getOption( $arOptions, 'addTypeAttributeName', 'type' );
		}

		$_sIdName = self::getOption( $arOptions, 'idName', 'id' );
		$_arColList = self::getOption( $arOptions, 'columnList', null );
		$_bAllowNulls = self::getOption( $arOptions, 'allowNulls', false );
		$_sReplaceNullWith = self::getOption( $arOptions, 'replaceNullWith', '&nbsp;' );
		$_arInnerElements = self::getOption( $arOptions, 'innerElements', null );
		$_arAddElements = self::getOption( $arOptions, 'addElements', null );
		$_arIgnoreColumns = self::getOption( $arOptions, 'ignoreColumns', null );

		$_sPrimaryKey = '';

		//	Loop through array of ActiveRecords
		foreach ( $arData as $_oRow )
		{
			//	Get the Pk
			if ( $_bIdInAttribute && $_bUseColumnElementNames && $_sPrimaryKey == '' )
			{
				$_sIdName = $_oRow->tableSchema->primaryKey;
				$_sPrimaryKey = $_oRow->getPrimaryKey();
			}

			$_sOut .= CHtml::openTag( $_sContainer, $_bIdInAttribute ? array( $_sIdName => $_oRow->primaryKey ) : array() );

			$_arAttr = $_oRow->getAttributes( null );

			foreach ( $_arAttr as $_sKey => $_oValue )
			{
				if ( is_array( $_arIgnoreColumns ) && in_array( $_sKey, $_arIgnoreColumns ) )
					continue;

				$_sElemToUse = ( $_bUseColumnElementNames ) ? $_sKey : $_sElement;
				$_sType = gettype( $_oValue );
				$_sOut .= CHtml::tag( $_sElemToUse, ( $_bAddTypes ? array( 'type' => $_sType ) : array() ), ( $_sType == 'string' && $_bUseCDataForStrings ) ? CHtml::cdata( $_oValue ) : $_oValue );
			}

			//	Anything extra to add?
			if ( $_arAddElements && is_array( $_arAddElements ) )
			{
				foreach ( $_arAddElements as $_oElement )
				{
					if ( $_oElement[ 'type' ] == 'function' )
					{
						$_sResult = $_oRow->{$_oElement['value']}( $_oRow );
						$_sOut .= CHtml::tag( $_oElement[ 'name' ], ( $_bAddTypes ? array( 'type' => $_oElement[ 'type' ] ) : array() ), ( $_bUseCDataForStrings  ) ? CHtml::cdata( $_sResult ) : $_sResult );
					}
					else
					{
						if ( $_oElement[ 'value' ] == '**rowdata**' )
						{
							try
							{
								$_sTemp = 'return( $_oRow->' . $_oElement[ 'column' ] . ');';
								$_oResult = eval( $_sTemp );
								$_sOut .= CHtml::tag( $_oElement[ 'name' ], ( $_bAddTypes ? array( 'type' => $_oElement[ 'type' ] ) : array() ), ( $_oElement[ 'type' ] == 'string' && $_bUseCDataForStrings ) ? CHtml::cdata( $_oResult ) : $_oResult );
							}
							catch ( Exception $_ex )
							{
								//	Ignore...
								$_sOut .= CHtml::tag( $_oElement[ 'name' ], ( $_bAddTypes ? array( 'type' => $_oElement[ 'type' ] ) : array() ), '' );
							}
						}
						else
							$_sOut .= CHtml::tag( $_oElement[ 'name' ], ( $_bAddTypes ? array( 'type' => $_oElement[ 'type' ] ) : array() ), ( $_oElement[ 'type' ] == 'string' && $_bUseCDataForStrings ) ? CHtml::cdata( $_oElement[ 'value' ] ) : $_oElement[ 'value' ] );
					}
				}
			}

			$_sOut .= CHtml::closeTag( $_sContainer );
		}

		//	Anything extra to add?
		if ( $_arInnerElements && is_array( $_arInnerElements ) )
		{
			foreach ( $_arInnerElements as $_oElement )
				$_sHeader .= CHtml::tag( $_oElement[ 'name' ], ( $_bAddTypes ? array( 'type' => $_oElement[ 'type' ] ) : array() ), ( $_oElement[ 'type' ] == 'string' && $_bUseCDataForStrings ) ? CHtml::cdata( $_oElement[ 'value' ] ) : $_oElement[ 'value' ] );
		}

		if ( $_bEncloseResults && ! empty( $_sEncloseTag ) )
			$_sOut = CHtml::tag( $_sEncloseTag, array( 'itemCount' => sizeof( $arData ) ), $_sHeader . $_sOut );

		//	And return...
		return( $_sOut );
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
		return ( null == $oVar || '' == $oVar );
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

}
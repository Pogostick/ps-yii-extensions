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
class CPSHelp extends CPSHelperBase
{
	//********************************************************************************
	//* Private Members
	//********************************************************************************
	
	/**
	* Widget prefix
	* 
	* @var mixed
	*/
	protected static $m_iIDCounter = 0;
	
	/**
	* A generic parameter array
	* 
	* @var array
	*/
	protected static $m_arParams = array();
	public function getParameter( $sKey ) { return PS::o( self::$m_arParams, $sKey ); }
	public function setParameter( $sKey, $sValue ) { self::$m_arParams[ $sKey ] = $sValue; }
	public function getParameters() { return self::$m_arParams; }
	public function resetParameters() { self::$m_arParams = array(); }
	
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
	
	/**
	* Generate a random ID # for a widget
	* 
	* @param string $sPrefix
	*/
	public static function getWidgetId( $sPrefix = null )
	{
		return $sPrefix . self::$m_iIDCounter++;
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
	
	/**
	* Given a source array and an array of columns, populates a parameter array
	* with data. Returns the completed condition string.
	* 
	* Data in $arColumns can be strings or arrays to override defaults:
	* 
	* $arColumns = array(
	* 	'user_name',															//	Exact, '='
	* 	'email' => array( 'template' => '%{column}%', 'operator' => 'like' ),	//	Or...
	* 	'email' => array( 'like' => true ),										//	Same as above
	* 
	* 	'password',
	* 	...
	* )
	* 
	* You can also specify special array parameters which will auto-set the values.
	* These are:
	* 
	* lt		Sets operator to '<'
	* lte		Sets operator to '<='
	* eq		Sets operator to '='
	* gt		Sets operator to '>'
	* gte		Sets operator to '>='
	* like		Sets operator to 'like' and template to '%{column}%'
	* ^like		Sets operator to 'like' and template to '{column}%'
	* like$		Sets operator to 'like' and template to '%{column}'
	* 
	* Additional accepted parameters:
	* 
	* operator		The operator for the condition
	* template		The template for the column in the condition
	* dateOnly		If true, will compare only the date portion of column
	* 
	* @param array $arSource
	* @param array $arColumns
	* 
	* @returns string
	*/
	public static function buildParameterSet( $arSource = array(), $arColumns = array() )
	{
		self::resetParameters();
		
		$_sCondition = null;
		
		foreach ( $arColumns as $_sKey => $_oColumn )
		{
			$_sTemplate = '{column}';
			$_sOperator = '=';
			$_sColumn = $_oColumn;
			$_bOr = false;
			
			if ( is_array( $_sColumn ) )
			{
				//	Set the column name
				$_sColumn = $_sKey;
				
				//	No data? Don't process...
				if ( isset( $arSource[ $_sColumn ] ) )
				{
					if ( $_bLike = PS::o( $_oColumn, 'like', false ) )
					{
						//	Special 'like' case...
						$_sTemplate = '%{column}%';
						$_sOperator = 'like';
					}
					else if ( $_bLike = PS::o( $_oColumn, '^like', false ) )
					{
						//	Special 'like' case...
						$_sTemplate = '{column}%';
						$_sOperator = 'like';
					}
					else if ( $_bLike = PS::o( $_oColumn, 'like$', false ) )
					{
						//	Special 'like' case...
						$_sTemplate = '%{column}';
						$_sOperator = 'like';
					}
					else
					{
						//	Special operators...
						if ( PS::o( $_oColumn, 'lt' ) ) $_sOperator = '<';
						if ( PS::o( $_oColumn, 'lte' ) ) $_sOperator = '<=';
						if ( PS::o( $_oColumn, 'eq' ) ) $_sOperator = '=';
						if ( PS::o( $_oColumn, 'gt' ) ) $_sOperator = '>';
						if ( PS::o( $_oColumn, 'gte' ) ) $_sOperator = '>=';
						if ( PS::o( $_oColumn, 'or' ) ) $_bOr = true;
						
						//	All others...
						$_sTemplate = PS::o( $_oColumn, 'template', '{column}' );
						
						//	Will override special ones above if both are supplied.
						$_sOperator = PS::o( $_oColumn, 'operator', '=' );
					}
					
					//	Date only...
					if ( PS::o( $_oColumn, 'dateOnly', false ) ) $_sTemplate = "date({$_sTemplate})";
				}
				else
					$_sColumn = null;
			}
			
			//	Make the parameter if we have a value...
			if ( null != $_sColumn ) $_sCondition = self::findParam( $arSource, $_sColumn, $_sCondition, $_sOperator, $_sTemplate, $_bOr );
		}

		//	Return the new conditions...
		return $_sCondition;
	}
	
	/**
	* Given a source array, a column name, and an optional condition, if the
	* column exists in the array, it will be added to the condition string and 
	* placed in the generic parameter array.
	* 
	* @param array $arSource The source array (i.e. $_POST)
	* @param string $sColumn The column name to pull out
	* @param string $sCondition Any existing condition string to which to append
	* @param string $sOperator The operator for the column (i.e. '=' )
	* @param string $sTemplate The template for the condition (i.e. '%{column}%' for 'like' operators)
	* @param string $bOr If true, condition will be "OR" instead of "AND"
	* 
	* @returns string The amended condition string
	*/
	public static function findParam( $arSource = array(), $sColumn, $sCondition = null, $sOperator = '=', $sTemplate = '{column}', $bOr = false )
	{
		if ( isset( $arSource[ $sColumn ] ) )
		{
			//	Only process if value isn't blank. 
			$_sTemp = trim( PS::o( $arSource, $sColumn ) );
			if ( strlen( $_sTemp ) ) $sCondition = self::makeParam( $sColumn, $_sTemp, $sCondition, $sOperator, $sTemplate, $bOr );
		}
		
		//	Return the condition string...
		return $sCondition;
	}
	
	/**
	* Given a column name, a value, and an optional condition, it will be added to the 
	* condition string and placed in the generic parameter array.
	* 
	* @param string $sColumn The column name to pull out
	* @param string $oValue The column value
	* @param string $sCondition Any existing condition string to which to append
	* @param string $sOperator The operator for the column (i.e. '=' )
	* @param string $sTemplate The template for the condition (i.e. '%{column}%' for 'like' operators)
	* @param string $bOr If true, condition will be "OR" instead of "AND"
	* 
	* @returns string The amended condition string
	*/
	public static function makeParam( $sColumn, $oValue, $sCondition = null, $sOperator = '=', $sTemplate = '{column}', $bOr = false )
	{
		$_sParam = ':' . $sColumn;
		$sCondition .= ( $sCondition ? ( $bOr ? ' or ' : ' and ' ) : '' ) . "`{$sColumn}` {$sOperator} {$_sParam}";
		self::setParameter( $_sParam, str_ireplace( '{column}', $oValue, $sTemplate ) );
		
		//	Return the condition string...
		return $sCondition;
	}
	
}
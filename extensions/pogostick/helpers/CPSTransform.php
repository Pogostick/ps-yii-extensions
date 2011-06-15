<?php
/**
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * CPSTransform provides helper functions for generic transformations and display formatting in grids and forms
 * 
 * @package 	psYiiExtensions
 * @subpackage 	helpers
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id: CPSTransform.php 395 2010-07-15 21:34:48Z jerryablan@gmail.com $
 * @since 		v1.0.6
 *  
 * @filesource
*/
class CPSTransform implements IPSBase
{
	//********************************************************************************
	//* Member Variables
	//********************************************************************************
	
	/**
	* Transformation mapping
	* @var array
	*/
	protected static $m_arTransform = array(
		'@' => 'linkTransform',
		'?' => 'boolTransform',
		'#' => 'timeTransform',
		'!' => 'styleTransform',
		'<' => 'alignTransform',
		'|' => 'alignTransform',
		'>' => 'alignTransform',
		'.' => 'numberTransform',
		'$' => 'currencyTransform',
		',' => 'integerTransform',
		'*' => 'codeLookup',
	);
	
	/**
	* The currency code to use for currency displays
	* @var string
	*/
	protected static $m_sCurrencyCode = '$';
	public static function getCurrencyCode() { return self::$m_sCurrencyCode; }
	public static function setCurrencyCode( $sValue ) { self::$m_sCurrencyCode = $sValue; }

	//********************************************************************************
	//* Public Methods
	//********************************************************************************
	
	/**
	* Strips off a format character from the front of a column name
	* @param string $sColumn
	* @return string
	*/
	public static function cleanColumn( $sColumn )
	{
		if ( in_array( $sColumn[0], array_keys( self::$m_arTransform ) ) ) $sColumn = substr( $sColumn, 1 );
		return $sColumn;
	}
	
	/**
	* Gets the value of a column
	* @param string $sType
	* @param mixed $oValue
	* @return mixed
	*/
	public static function valueOf( $sType, $oValue )
	{
		foreach ( self::$m_arTransform as $_sChar => $_sMethod )
		{
			if ( $sType == $_sChar )
			{
				list( $oValue, $_bLink ) = self::$_sMethod( $sType, $oValue );
				break;
			}
		}
		
		return $oValue;
	}
	
	/**
	* Sets the value of a column
	* 
	* @param CModel $oModel
	* @param string $sColumn
	* @param mixed $oValue
	*/
	protected static function setValue( CModel $oModel, $sColumn, $oValue = null )
	{
		if ( $oModel->hasAttribute( self::columnChain( $sColumn ) ) )
		{
			$_sColumn = self::columnChain( $sColumn );
			$oModel->{$_sColumn} = $oValue;
		}
	}
	
	/**
	* Retrieves a formatted value
	* 
	* @param CModel $oModel
	* @param string $sColumn
	* @param string $sFormatColumn
	* @return mixed
	*/
	public static function getValue( $oModel, $sColumn, $sFormatColumn = null )
	{
		if ( ! $oModel instanceof CModel ) 
			return $oModel[ self::columnChain( PS::nvl( $sFormatColumn, $sColumn ) ) ];
		
		return eval( 'return $oModel->' . self::columnChain( $sColumn ) . ';' );
	}
	
	/**
	* Splits apart a complex column name into its components and constructs a 
	* column chain (i.e. game.long_name becomes game->long_name)
	* @param string $sColumn
	* @return string
	*/
	protected static function columnChain( $sColumn )
	{
		$_sCmd = $sColumn;
		
		if ( false !== strpos( $sColumn, '.' ) )
		{
			$_sCmd = null;
			$_arParts = explode( '.', $sColumn );
			foreach ( $_arParts as $_sPart ) $_sCmd .= ( $_sCmd !== null ? '->' : '' ) . $_sPart;
		}

		return $_sCmd;
	}
	
	/**
	* Get a formatted column value
	* 
	* @param CModel $oModel
	* @param array $arColumns
	* @param string $sLinkView
	* @param string $sWrapTag
	* @param array $arWrapOptions
	* @return string
	*/
	public static function column( CModel $oModel, $arColumns = array(), $sLinkView = 'update', $sWrapTag = 'td', $arWrapOptions = array() )
	{
		$_bValue = $_sOut = null;
		$_arValueMap = array();
		$_bIsModel = ( $oModel instanceof CModel );
		$_sPK = ( $_bIsModel ) ? $oModel->getTableSchema()->primaryKey : null;
		
		//	Build columns
		foreach ( $arColumns as $_sKey => $_oColumn )
		{
			$_bLink = false;
			$_bCodeValue = $_sNullDisplay = $_sDisplayFormat = $_arValueMap = $_oValue = null;
			$_sColumn = $_oColumn;
			
			//	Any column options?
			if ( is_array( $_oColumn ) )
			{
				$_sColumn = array_shift( $_oColumn );
				$_arColOpts = $_oColumn;

				//	Get column options
				if ( $_arColOpts ) 
				{
					$_arValueMap = PS::o( $_arColOpts, 'valueMap', array(), true );
					$_sDisplayFormat = PS::o( $_arColOpts, 'displayFormat', null, true );
					$_sNullDisplay = PS::o( $_arColOpts, 'nullDisplay', null, true );
					$_bCodeValue = PS::o( $_arColOpts, 'codeValue', null, true );
					
					//	Anything remaining gets rolled into wrap options
					$arWrapOptions = PS::smart_array_merge( $_arColOpts, $arWrapOptions );
				}
			}
			
			//	Process column...
			if ( in_array( $_sColumn[0], array_keys( self::$m_arTransform ) ) )
			{
				$_sRealCol = self::cleanColumn( $_sColumn );
				$_sMethod = self::$m_arTransform[ $_sColumn[0] ];
				$_oValue = self::getValue( $oModel, $_bIsModel ? $_sRealCol : $_sColumn );
				list( $_oValue, $_bLink, $_arWrapOpts ) = self::$_sMethod( $_sColumn[0], $_oValue, $_sDisplayFormat );
				$arWrapOptions = PS::smart_array_merge( $arWrapOptions, $_arWrapOpts );
			}
			else
				$_sRealCol = $_sColumn;

			if ( ! strlen( $_oValue ) ) $_oValue = self::getValue( $oModel, $_sRealCol );
			
			//	Map value for display...
			if ( $_arValueMap && isset( $_oValue ) && in_array( $_oValue, array_keys( $_arValueMap ) ) )
				if ( isset( $_arValueMap[ $_oValue ] ) ) $_oValue = $_arValueMap[ $_oValue ];

			//	Pretty it up...
			if ( is_array( $sLinkView ) )
			{
				$_arLink = $sLinkView;
				$_arLink[ $_sPK ] = $oModel->{$_sPK};
			}
			else
				$_arLink = array( $sLinkView, $_sPK => $oModel->{$_sPK} );
			
			$_sColumn = ( $_bLink || $_sPK == $_sRealCol ) ?
				PS::link( $_oValue, $_arLink )
				:
				( PS::o( $arWrapOptions, 'encode', true ) ? PS::encode( $_oValue ) : $_oValue );

			$_sOut .= ( $sWrapTag ) ? PS::tag( $sWrapTag, $arWrapOptions, $_sColumn ) : $_sColumn;
		}

		return $_sOut;
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
	
	//********************************************************************************
	//* Private Transformation Methods 
	//********************************************************************************
	
	/**
	* Converts to a link
	* 
	* @param string $sHow
	* @param mixed $oValue
	* @return array
	*/
	protected static function linkTransform( $sHow, $oValue = null )
	{
		return array( $oValue, true, array() );
	}
	
	/**
	* Converts a boolean into a yes/no
	* Supports 1, 0, y, n, Y, and N
	* 
	* @param string $sHow
	* @param mixed $oValue
	* @return array
	*/
	protected static function boolTransform( $sHow, $oValue )
	{
		$_oValue = ( empty( $oValue ) || $oValue === 'N' || $oValue === 'n' || $oValue === 0 ) ? 'No' : 'Yes';
		return self::alignTransform( '|', $_oValue );
	}
	
	/**
	* Converts a UNIX timestamp into a readable date.
	* 
	* @param string $sHow
	* @param mixed $oValue
	* @param string $sFormat The format of the returned date
	* @return array
	*/
	protected static function timeTransform( $sHow, $oValue, $sFormat = 'F d, Y' )
	{
		return array( date( $sFormat, strtotime( $oValue ) ), false, array() );
	}
	
	/**
	* Converts a number into a nice formatted number
	* 
	* @param string $sHow
	* @param mixed $oValue
	* @param int $iDecimals The number of decimal places to apply
	* @return array
	*/
	protected static function numberTransform( $sHow, $oValue, $iDecimals = 2 )
	{
		return self::alignTransform( '>', number_format( doubleval( $oValue ), $iDecimals ) );
	}
	
	/**
	* Converts an integer into a nice formatted number
	* 
	* @param string $sHow
	* @param mixed $oValue
	* @return array
	*/
	protected static function integerTransform( $sHow, $oValue )
	{
		return self::numberTransform( $sHow, $oValue, 0 );
	}

	/**
	* Converts a number into a nice formatted currency number
	* 
	* @param string $sHow
	* @param mixed $oValue
	* @param int $iDecimals The number of decimal places to apply
	* @return array
	* 
	* @todo Convert to use money_format()
	*/
	protected static function currencyTransform( $sHow, $oValue, $iDecimals = 2 )
	{
		return self::alignTransform( '>', self::$m_sCurrencyCode . number_format( doubleval( $oValue ), $iDecimals ) );
	}
	
	/**
	* Generic style transformation
	* 
	* @param string $sHow
	* @param mixed $oValue
	* @return array
	*/
	protected static function styleTransform( $sHow, $oValue )
	{	
		return self::alignTransform( $sHow, $oValue );
	}
	
	/**
	* Generic code replacement. Requires codeModel to be defined.
	* 
	* @param string $sHow
	* @param mixed $oValue
	* @param int $iDecimals The number of decimal places to apply
	* @return array
	*/
	protected static function codeLookup( $sHow, $oValue )
	{
		if ( $_sCodeModel = PS::getCodeModel() )
			$oValue = call_user_func_array( array( $_sCodeModel, 'getCodeDescription' ), array( $oValue ) );

		return array( $oValue, false, array() );
	}
	
	/**
	* Generic alignment transformations for grids
	* 
	* @param string $sHow
	* @param mixed $oValue
	* @return array
	*/
	protected static function alignTransform( $sHow, $oValue )
	{
		$_arStyle = array();
		
		switch ( $sHow )
		{
			case '|':
				$_arStyle['style'] = 'text-align:center;';
				break;
				
			case '>':
				$_arStyle['style'] = 'text-align:right;';
				break;
				
			case '<':
			default:
				$_arStyle['style'] = 'text-align:left;';
				break;
		}
		
		return array( $oValue, false, $_arStyle );
	}
	
	/**
	* Converts a `camelCase`, human-friendly or `underscore_notation` string to `underscore_notation`
	* 
	* @param string $sString The string to convert
	* @param string $sChar Optional separator character. Defaults to '_'
	* @return string The converted string
	*/
	public static function underscorize( $sString, $sChar = '_' )
	{
		$sString = strtolower( $sString[ 0 ] ) . substr( $sString, 1 );
		
		//	If the string is already underscore notation then leave it
		if ( false !== strpos( $sString, $sChar ) )
		{
			// Allow humanized string to be passed in
		}
		elseif ( false !== strpos( $sString, ' ' ) ) 
		{
			$sString = strtolower( preg_replace('#\s+#', $sChar, $sString ) );
		}
		else
		{
			do
			{
				$_sOld = $sString;
				$sString = preg_replace( '/([a-zA-Z])([0-9])/', '\1' . $sChar . '\2', $sString );
				$sString = preg_replace( '/([a-z0-9A-Z])([A-Z])/', '\1' . $sChar . '\2', $sString );
			}
			while ( $_sOld != $sString );
			
			$sString = strtolower( $sString );
		}

		return $sString;
	}
	
	//********************************************************************************
	//* as* Transformation Methods
	//********************************************************************************

	public static function asUnorderedListFromArray( $arList, $arOptions = array(), $bRecursiveCall = false )
	{
		$_arKeys = array_keys( $arList );
		$_i = 0;
		$_count = count( $arList );
		$_sOut = null;
		$_listOptions = PS::o( $arOptions, 'listOptions', array(), true );

		while ( $_i < $_count )
		{
			$_oValue = $arList[ $_arKeys[ $_i ] ];

			$_sOut .= PS::tag( 
				'li',
				PS::o( $arOptions, 'itemOptions', array() ),
				PS::link( 
					is_array( $_oValue ) ? $_arKeys[ $_i ] : $_oValue,
					$_arKeys[ $_i ],
					PS::o( $arOptions, 'linkOptions', array() )
				),
				false
			) . PHP_EOL;
			
			if ( is_array( $_oValue ) )
			{
				$_sOut .= PS::tag(
					'ul',
					array(),
					CPSTransform::asUnorderedListFromArray( $arList[ $_arKeys[ $_i ] ], $arOptions, true )
				) . PHP_EOL;
			}

			$_sOut .= PS::closeTag( 'li' ) . PHP_EOL;

			$_i++;
		}

		if ( $bRecursiveCall ) return $_sOut;

		return PS::tag( 'ul', $_listOptions, $_sOut ) . PHP_EOL;
	}

	/**
	* Outputs a string of UL/LI tags from an array of models suitable
	* for menu structures
	* 
	* @param array $arModel
	* @param array $arOptions
	* @return string
	*/
	public static function asUnorderedList( $arModel = array(), $arOptions = array() )
	{
		static $_bInit = false;
		static $_sValColumn;
		static $_sKeyColumn;
		static $_sChildrenRelation;
		static $_bLinkText;
		static $_bEncode = true;

		$_sClass = $_sId = $_sOut = null;
		
		if ( ! $_bInit )
		{
			$_sId = PS::o( $arOptions, 'id', null, true );
			$_bLinkText = PS::o( $arOptions, 'linkText', true, true );
			$_sKeyColumn = PS::o( $arOptions, 'keyColumn', 'id', true );
			$_sValColumn = PS::o( $arOptions, 'valueColumn', null, true );
			$_sChildrenRelation = PS::o( $arOptions, 'childrenRelation', 'children', true );
			$_sClass = PS::o( $arOptions, 'class', null, true );
			$_bEncode = PS::o( $arOptions, 'encode', true, true );

			$_bInit = true;
		}

		//	Loop...
		foreach ( $arModel as $_oModel )
		{
			//	Does this model have relational kids?
			if ( $_oModel instanceof CPSModel && $_oModel->hasBehavior( 'CPSParentChildBehavior' ) )
			{
				$_bHasKids = ( in_array( $_sChildrenRelation, array_keys( $_oModel->relations() ) ) && $_oModel->hasChildren() );
			}
			else
			{
				$_bHasKids = false;
			}
					
			$_oValue = $_oModel->{$_sValColumn};
			$_arLinkOpts = PS::o( $arOptions, 'linkOptions', array() );
			$_sContent = $_bEncode ? PS::encode( $_oValue ) : $_oValue;
			$_arLinkOpts['rel'] = $_oModel->{$_sKeyColumn};
			if ( $_bLinkText ) $_sContent = PS::link( $_sContent, PS::o( $_arLinkOpts, 'href', '#', true), $_arLinkOpts );
			if ( $_bHasKids ) $_sContent .= self::asUnorderedList( $_oModel->{$_sChildrenRelation}, $arOptions );

			$_arLiOpts = array( 'rel' => $_oModel->{$_sKeyColumn} );
			$_sOut .= PS::tag( 'li', $_arLiOpts, $_sContent );
		}

		$_arOpts = array();
		if ( $_sId ) $_arOpts['id'] = $_sId;
		if ( $_sClass ) $_arOpts['class'] = $_sClass;

		return $_sOut ? PS::tag( 'ul', $_arOpts, $_sOut ) : null;
	}
	
	/**
	* Returns an Xml string representation of an array of CActiveRecords
	* Will automatically format data for a jqGrid if option 'jqGrid' is set to true.
	* 
	* @param array The array of models
	* @param array Options for building the xml
	* @todo needs doc
	*/
	public static function asXml( $arData = array(), $arOptions = array() )
	{
		//	Initialize...
		$_sHeader = '';
		$_sOut = '';
		$_oSchema = null;

		//	Default options for jqGrid
		if ( $_bjqGrid = PS::o( $arOptions, 'jqGrid', false ) )
		{
			$_sContainer = 'row';
			$_sElement = 'cell';
			$_bUseColumnElementNames = false;
			$_bIdInAttribute = true;
			$_sIdName = 'id';
			$_bUseCDataForStrings = true;
			$_bEncloseResults = true;
			$_sEncloseTag = 'rows';
			$_arAddElements = PS::o( $arOptions, 'addElements', null );
			$_bAddTypes = false;
		}
		else
		{
			$_sContainer = PS::o( $arOptions, 'container', 'row' );
			$_sElement = PS::o( $arOptions, 'element', 'cell' );
			$_bUseColumnElementNames = PS::o( $arOptions, 'useColumnElementNames', false );
			$_bIdInAttribute = PS::o( $arOptions, 'idInAttribute', true );
			$_bUseCDataForStrings = PS::o( $arOptions, 'useCDataForStrings', true );
			$_bEncloseResults = PS::o( $arOptions, 'encloseResults', false );
			$_sEncloseTag = PS::o( $arOptions, 'encloseTag', 'rows' );
			$_bAddTypes = PS::o( $arOptions, 'addTypes', false );
			$_sAddTypeAttributeName = PS::o( $arOptions, 'addTypeAttributeName', 'type' );
		}

		$_sIdName = PS::o( $arOptions, 'idName', 'id' );
		$_arColList = PS::o( $arOptions, 'columnList', null );
		$_bAllowNulls = PS::o( $arOptions, 'allowNulls', false );
		$_sReplaceNullWith = PS::o( $arOptions, 'replaceNullWith', '&nbsp;' );
		$_arInnerElements = PS::o( $arOptions, 'innerElements', array() );
		$_arAddElements = PS::o( $arOptions, 'addElements', array() );
		$_arIgnoreColumns = PS::o( $arOptions, 'ignoreColumns', array() );

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

			$_sOut .= PS::openTag( $_sContainer, $_bIdInAttribute ? array( $_sIdName => $_oRow->primaryKey ) : array() );

			foreach ( $_oRow->getAttributes() as $_sKey => $_oValue )
			{
				if ( in_array( $_sKey, $_arIgnoreColumns ) )
					continue;

				$_sElemToUse = ( $_bUseColumnElementNames ) ? $_sKey : $_sElement;
				$_sType = gettype( $_oValue );
				$_sOut .= PS::tag( $_sElemToUse, ( $_bAddTypes ? array( 'type' => $_sType ) : array() ), ( $_sType == 'string' && $_bUseCDataForStrings ) ? PS::cdata( $_oValue ) : $_oValue );
			}

			//	Anything extra to add?
			foreach ( $_arAddElements as $_oElement )
			{
				if ( $_oElement[ 'type' ] == 'function' )
				{
					$_sResult = $_oRow->{$_oElement['value']}( $_oRow );
					$_sOut .= PS::tag( $_oElement[ 'name' ], ( $_bAddTypes ? array( 'type' => $_oElement[ 'type' ] ) : array() ), ( $_bUseCDataForStrings  ) ? PS::cdata( $_sResult ) : $_sResult );
				}
				else
				{
					if ( $_oElement[ 'value' ] == '**rowdata**' )
					{
						try
						{
							$_sTemp = 'return( $_oRow->' . $_oElement[ 'column' ] . ');';
							$_oResult = eval( $_sTemp );
							$_sOut .= PS::tag( $_oElement[ 'name' ], ( $_bAddTypes ? array( 'type' => $_oElement[ 'type' ] ) : array() ), ( $_oElement[ 'type' ] == 'string' && $_bUseCDataForStrings ) ? PS::cdata( $_oResult ) : $_oResult );
						}
						catch ( Exception $_ex )
						{
							//	Ignore...
							$_sOut .= PS::tag( $_oElement[ 'name' ], ( $_bAddTypes ? array( 'type' => $_oElement[ 'type' ] ) : array() ), '' );
						}
					}
					else
						$_sOut .= PS::tag( $_oElement[ 'name' ], ( $_bAddTypes ? array( 'type' => $_oElement[ 'type' ] ) : array() ), ( $_oElement[ 'type' ] == 'string' && $_bUseCDataForStrings ) ? PS::cdata( $_oElement[ 'value' ] ) : $_oElement[ 'value' ] );
				}
			}

			$_sOut .= PS::closeTag( $_sContainer );
		}

		//	Anything extra to add?
		foreach ( $_arInnerElements as $_oElement )
			$_sHeader .= PS::tag( $_oElement[ 'name' ], ( $_bAddTypes ? array( 'type' => $_oElement[ 'type' ] ) : array() ), ( $_oElement[ 'type' ] == 'string' && $_bUseCDataForStrings ) ? PS::cdata( $_oElement[ 'value' ] ) : $_oElement[ 'value' ] );

		if ( $_bEncloseResults && ! empty( $_sEncloseTag ) )
			$_sOut = PS::tag( $_sEncloseTag, array( 'itemCount' => sizeof( $arData ) ), $_sHeader . $_sOut );

		//	And return...
		return $_sOut;
	}

	/**
	* Returns an array suitable as list data from an array of models
	* 
	* @param array $arData
	* @param string $sKeyColumn
	* @param string $sValueColumn
	* @return array
	* @static
	* @access public
	*/
	public static function asListData( $arData = array(), $sKeyColumn, $sValueColumn )
	{
		$_arOut = array();
		
		foreach ( $arData as $_oRow )
			$_arOut[ $_oRow->getAttribute( $sKeyColumn ) ] = $_oRow->getAttribute( $sValueColumn );
			
		return $_arOut;
	}

}
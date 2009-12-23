<?php
/*
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * CPSTransform provides helper functions for display formatting in grids and forms
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
class CPSTransform extends CPSHelperBase
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
	* @returns mixed
	*/
	public static function value( $sType, $oValue )
	{
		foreach ( self::$m_arTransform as $_sChar => $_sMethod )
		{
			if ( $sType == $_sChar )
			{
				list( $oValue, $_bLink ) = self::$_sMethod( $_sColumn, $oValue );
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
	protected static function getValue( $oModel, $sColumn, $sFormatColumn = null )
	{
		if ( ! $oModel instanceof CModel ) 
			return $oModel[ self::columnChain( PS::nvl( $sFormatColumn, $sColumn ) ) ];
		
		return eval( 'return $oModel->' . self::columnChain( $sColumn ) . ';' );
	}
	
	/**
	* Splits apart a complex column name into its components and constructs a 
	* column chain (i.e. game.long_name becomes game->long_name)
	* @param string $sColumn
	* @returns string
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
	* @returns string
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
					$arWrapOptions = CPSHelp::smart_array_merge( $_arColOpts, $arWrapOptions );
				}
			}
			
			//	Process column...
			if ( in_array( $_sColumn[0], array_keys( self::$m_arTransform ) ) )
			{
				$_sRealCol = self::cleanColumn( $_sColumn );
				$_sMethod = self::$m_arTransform[ $_sColumn[0] ];
				$_oValue = self::getValue( $oModel, $_bIsModel ? $_sRealCol : $_sColumn );
				list( $_oValue, $_bLink, $_arWrapOpts ) = self::$_sMethod( $_sColumn[0], $_oValue, $_sDisplayFormat );
				$arWrapOptions = CPSHelp::smart_array_merge( $arWrapOptions, $_arWrapOpts );
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
				CHtml::link( $_oValue, $_arLink )
				:
				( PS::o( $arWrapOptions, 'encode', true ) ? CHtml::encode( $_oValue ) : $_oValue );

			$_sOut .= ( $sWrapTag ) ? CHtml::tag( $sWrapTag, $arWrapOptions, $_sColumn ) : $_sColumn;
		}

		return $_sOut;
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
			$oValue = $_sCodeModel::getCodeDescription( $oValue );

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
	
}
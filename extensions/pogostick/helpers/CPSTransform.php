<?php
/**
 * CPSTransform class file.
 *
 * @filesource
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://www.pogostick.com Pogostick, LLC.
 * @package psYiiExtensions
 * @subpackage helpers
 * @since v1.0.5
 * @version SVN: $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified  $Date$
 */

/**
 * CPSTransform provides form helper functions
 */
class CPSTransform extends CPSHelperBase
{
	//********************************************************************************
	//* Member Variables
	//********************************************************************************
	
	/**
	* Transformation mapping
	* 
	* @var mixed
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
		',' => 'currencyTransform',
	);

	//********************************************************************************
	//* Public Methods
	//********************************************************************************
	
	public static function cleanColumn( $sColumn )
	{
		if ( in_array( $sColumn[0], array_keys( self::$m_arTransform ) ) ) $sColumn = substr( $sColumn, 1 );
		return $sColumn;
	}
	
	public static function value( $sType, $oValue )
	{
		foreach ( self::$m_arTransform as $_sChar => $_sMethod )
		{
			if ( $sType == $_sChar )
			{
				list( $_sColumn, $oValue, $_bLink ) = self::$_sMethod( $_sColumn, $oValue );
				break;
			}
		}
		
		return $oValue;
	}
	
	protected static function setValue( $oModel, $sColumn, $oValue = null )
	{
		return eval( '$oModel->' . self::columnChain( $sColumn ) . ' = \'' . $oValue . '\'; return;' );
	}
	
	protected static function getValue( $oModel, $sColumn )
	{
		return eval( 'return $oModel->' . self::columnChain( $sColumn ) . ';' );
	}
	
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
	
	public static function column( $oModel, $arColumns = array(), $sLinkView = 'update', $sWrapTag = 'td', $arWrapOptions = array() )
	{
		$_bValue = $_sOut = null;
		$_arValueMap = array();
		$_sPK = $oModel->getTableSchema()->primaryKey;
		
		//	Build columns
		foreach ( $arColumns as $_sKey => $_oColumn )
		{
			$_bLink = false;
			$_oValue = null;
			$_sColumn = $_oColumn;
			
			//	Any column options?
			if ( is_array( $_oColumn ) )
			{
				$_sColumn = $_sKey;
				$_arColOpts = $_oColumn;

				//	Get column options
				if ( $_arColOpts ) $_arValueMap = self::getOption( $_arColOpts, 'valueMap', array(), true );
			}
			
			//	Process column...
			if ( in_array( $_sColumn[0], array_keys( self::$m_arTransform ) ) )
			{
				$_sRealCol = self::cleanColumn( $_sColumn );
				$_sMethod = self::$m_arTransform[ $_sColumn[0] ];
				list( $_oValue, $_bLink, $_arWrapOpts ) = self::$_sMethod( $_sColumn[0], self::getValue( $oModel, $_sRealCol ) );
				$arWrapOptions = array_merge( $arWrapOptions, $_arWrapOpts );
			}
			else
				$_sRealCol = $_sColumn;

			if ( ! $_oValue ) $_oValue = self::getValue( $oModel, $_sRealCol );
			
			//	Map value for display...
			if ( isset( $_oValue ) && in_array( $_oValue, array_keys( $_arValueMap ) ) )
				if ( isset( $_arValueMap[ $_oValue ] ) ) $_oValue = $_arValueMap[ $_oValue ];

			//	Pretty it up...
			$_sColumn = ( $_bLink || $_sPK == $_sRealCol ) ?
				CHtml::link( $_oValue, array( $sLinkView, $_sPK => $oModel->{$_sPK} ) )
				:
				CHtml::encode( $_oValue );

			$_sOut .= ( $sWrapTag ) ? CHtml::tag( $sWrapTag, $arWrapOptions, $_sColumn ) : $_sColumn;
		}

		return $_sOut;
	}

	//********************************************************************************
	//* Private Methods 
	//********************************************************************************
	
	protected static function linkTransform( $sHow, $oValue = null )
	{
		return array( $oValue, true, array() );
	}
	
	protected static function boolTransform( $sHow, $oValue )
	{
		$_oValue = ( empty( $oValue ) || $oValue === 'N' || $oValue === 'n' || $oValue === 0 ) ? 'No' : 'Yes';
		return array( $_oValue, false, array() );
	}
	
	protected static function timeTransform( $sHow, $oValue, $sFormat = 'F d, Y' )
	{
		return array( date( $sFormat, $oValue ), false, array() );
	}
	
	protected static function numberTransform( $sHow, $oValue, $iDecimals = 2 )
	{
		return self::alignTransform( '>', number_format( doubleval( $oValue ), $iDecimals ) );
	}
	
	protected static function currencyTransform( $sHow, $oValue, $iDecimals = 2 )
	{
		return self::alignTransform( '>', '$' . number_format( doubleval( $oValue ), $iDecimals ) );
	}
	
	protected static function styleTransform( $sHow, $oValue )
	{	
		return self::alignTransform( $sHow, $oValue, '>' );
	}
	
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
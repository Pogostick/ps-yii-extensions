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

//	Need this 
Yii::import('pogostick.helpers.CPSActiveWidgets'); 

/**
 * CPSTransform provides form helper functions
 */
class CPSTransform
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
	
	public static function column( $oModel, $arColumns = array(), $sLinkView = 'update', $sWrapTag = 'td', $arWrapOptions = array() )
	{
		$_bValue = $_sOut = null;
		$_sPK = $oModel->getTableSchema()->primaryKey;
		
		//	Build columns
		foreach ( $arColumns as $_sColumn )
		{
			$_bLink = false;
			$_oValue = null;

			if ( in_array( $_sColumn[0], array_keys( self::$m_arTransform ) ) )
			{
				$_sRealCol = self::cleanColumn( $_sColumn );
				$_sMethod = self::$m_arTransform[ $_sColumn[0] ];
				list( $_sColumn, $_oValue, $_bLink, $_arWrapOpts ) = self::$_sMethod( $_sColumn, $oModel->$_sRealCol );
				$arWrapOptions = array_merge( $arWrapOptions, $_arWrapOpts );
			}

			if ( ! $_oValue ) $_oValue = $oModel->{$_sColumn};
			
			$_sColumn = ( $_bLink || $_sPK == $_sColumn ) ?
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
	
	protected static function linkTransform( $sColumn, $oValue = null )
	{
		return array( self::cleanColumn( $sColumn ), $oValue, true, array() );
	}
	
	protected static function boolTransform( $sColumn, $oValue )
	{
		$_oValue = ( empty( $oValue ) || $oValue === 'N' || $oValue === 'n' || $oValue === 0 ) ? 'No' : 'Yes';
		return array( self::cleanColumn( $sColumn ), $_oValue, false, array() );
	}
	
	protected static function timeTransform( $sColumn, $oValue, $sFormat = 'F d, Y' )
	{
		return array( self::cleanColumn( $sColumn ), date( $sFormat, $oValue ), false, array() );
	}
	
	protected static function styleTransform( $sColumn, $oValue )
	{	
		return self::alignTransform( $sColumn, $oValue, '>' );
	}
	
	protected static function alignTransform( $sColumn, $oValue )
	{
		$_arStyle = array();
		
		switch ( $sColumn[0] )
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
		
		return array( self::cleanColumn( $sColumn ), $oValue, false, $_arStyle );
	}
	
}
<?php
/**
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * Simple data list
 * 
 * @package 	psYiiExtensions
 * @subpackage 	widgets
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id: CPSDataList.php 322 2009-12-23 23:51:37Z jerryablan@gmail.com $
 * @since 		v1.0.6
 *  
 * @filesource
 */
class CPSDataList
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
	);

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	public static function create( $sDataName, $oModel, $arColumns = array(), $arActions = array() )
	{
		return CHtml::tag( 'table', array( 'class' => 'dataGrid' ), self::getDataListRows( $oModel, $arColumns ) );
	}

	/***
	* Builds a row for a data list
	* If a column name is prefixed with an '@', it will be stripped and the column will be a link to the 'update' view
	* 
	* @param array $arModel
	* @param array $arColumns
	* @param array $arActions
	* @param string $sDataName
	* @return string
	*/
	public static function getDataListRows( $oModel, $arColumns = array() )
	{
		$_sActions = $_bValue = $_sTD = $_sOut = null;
		$_sPK = $oModel->getTableSchema()->primaryKey;
			
		//	Build columns
		foreach ( $arColumns as $_sColumn )
		{
			$_bLink = false;
			$_oValue = null;

			foreach ( self::$m_arTransform as $_sChar => $_sMethod )
			{
				if ( $_sColumn{0} == $_sChar )
				{
					list( $_sColumn, $_oValue, $_bLink ) = self::$_sMethod( $oModel, $_sColumn );
					break;
				}
			}

			if ( ! $_oValue ) $_oValue = $oModel->{$_sColumn};
				
			$_oValue = ( $_bLink || $_sPK == $_sColumn ) ?
				CHtml::link( $_oValue, array( 'update', $_sPK => $oModel->{$_sPK} ) ) 
				:
				CHtml::encode( $_oValue );

			$_sOut .= '<tr><th class="label">' . CHtml::encode( $oModel->getAttributeLabel( $_sColumn ) ) . '</th><td>' . $_oValue . '</td></tr>';
		}
			
		return $_sOut;
	}

	protected static function linkTransform( $oModel, $sColumn )
	{
		$_sColumn = substr( $sColumn, 1 );
		return array( $_sColumn, null, true );
	}
	
	protected static function boolTransform( $oModel, $sColumn )
	{
		$_sColumn = substr( $sColumn, 1 );
		$_oValue = $oModel->{$_sColumn};
		$_oValue = ( ! $_oValue || $_oValue = 'N' || $_oValue = 'n' || $_oValue == 0 ) ? 'No' : 'Yes';
		return array( $_sColumn, $_oValue, false );
	}
	
	protected static function timeTransform( $oModel, $sColumn )
	{
		$_sColumn = substr( $sColumn, 1 );
		$_oValue = date( "F d, Y", $oModel->{$_sColumn} );
		return array( $_sColumn, $_oValue, false );
	}
	
}
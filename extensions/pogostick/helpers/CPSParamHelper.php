<?php
/**
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * Provides parameter helper functions
 * 
 * @package 	psYiiExtensions
 * @subpackage 	helpers
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id: CPSParamHelper.php 398 2010-08-05 20:12:38Z jerryablan@gmail.com $
 * @since 		v1.0.6
 *  
 * @filesource
 */
class CPSParamHelper implements IPSBase
{
	//********************************************************************************
	//* Private Members
	//********************************************************************************
	
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
	* @return string
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
	* @return string The amended condition string
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
	* @return string The amended condition string
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
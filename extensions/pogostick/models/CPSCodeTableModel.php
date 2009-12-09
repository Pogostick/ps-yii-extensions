<?php
/**
 * CPSCodeTableModel file
 * 
 * Provides a base class for code lookup tables in your database
 *
 * @filesource
 * @author Jerry Ablan <jablan@pogostick.com>
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @package psYiiExtensions
 * @subpackage models
 * @since v1.0.6
 * @version SVN: $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified  $Date$
 */
abstract class CPSCodeTableModel extends CPSModel
{
	/**
	* Find a code by abbreviation
	* 
	* @param string $sAbbr
	* @param string $sType
	* @return array
	* @static
	*/
	public static function findByAbbreviation( $sAbbr, $sType, $sOrder = 'code_desc_text' )
	{
		$_oCrit = new CDbCriteria();
		$_oCrit->condition = 'code_abbr_text = :code_abbr_text and code_type_text = :code_type_text';
		$_oCrit->params = array( 
			':code_abbr_text' => $sAbbr,
			':code_type_text' => $sType
		);
		$_oCrit->order = $sOrder;
		
		return self::model()->find( $_oCrit );
	}

	/**
	* Find a code by type
	* 
	* @param string $sType
	* @return array
	* @static
	*/
	public static function findAllByType( $sType, $sOrder = 'code_desc_text' )
	{
		$_oCrit = new CDbCriteria();
		$_oCrit->condition = 'code_type_text = :code_type_text';
		$_oCrit->params = array( ':code_type_text' => $sType );
		$_oCrit->order = $sOrder;
		
		return self::model()->findAll( $_oCrit );
	}

	/**
	* Find a code by type
	* 
	* @param string $sType
	* @return array
	* @static
	*/
	public static function findAllByAbbreviation( $sAbbr, $sType = null, $sOrder = 'code_desc_text' )
	{
		$_oCrit = new CDbCriteria();
		$_oCrit->condition = 'code_abbr_text = :code_abbr_text and code_type_text = :code_type_text';
		$_oCrit->params = array( 
			':code_abbr_text' => $sAbbr,
			':code_type_text' => $sType
		);
		$_oCrit->order = $sOrder;
		
		return self::model()->findAll( $_oCrit );
	}

	/**
	* Finds a single code by code_id
	* 
	* Duplicates findByPk, but wanted to be consistent.
	* 	
	* @param integer $iCodeId
	* @return CActiveRecord
	* @static
	*/
	public static function findById( $iCodeId )
	{
		return self::model()->findByPk( $iCodeId );
	}
}

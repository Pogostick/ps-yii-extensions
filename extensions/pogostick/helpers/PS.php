<?php
/*
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * Base functionality that I want in ALL my helper classes
 * 
 * @package 	psYiiExtensions
 * @subpackage 	helpers
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id$
 * @since 		v1.0.6
 *  
 * @filesource
 * 
 * @todo Find a better way to do this
 */
class PS extends CPSActiveWidgets
{
	//********************************************************************************
	//* Public Methods
	//********************************************************************************
	
	/**
	* Convenience method to access simpleActiveBlock
	* 
	* $arOptions may contain any HTML options for the field. Special values are:
	* 
	* label				Label to display
	* labelOptions		Options for the label tag
	* data				Data to pass to the field (i.e. select options array)
	* widgetOptions		Options for the widget
	* 
	* @param int $eFieldType
	* @param CModel $oModel
	* @param string $sColName
	* @param array $arOptions
	* @return string
	* @static
	* @access public
	*/
	public static function field( $eFieldType, CModel $oModel, $sColName, $arOptions = array() )
	{
		//	A little switcheroo...
		if ( $eFieldType == PS::CODE_DISPLAY )
		{
			$eFieldType = PS::TEXT_DISPLAY;
			$arOptions['transform'] = '*';
			echo PS::field( PS::HIDDEN, $oModel, $sColName );
		}

		$_sLabel = CPSHelp::getOption( $arOptions, 'label', null, true );
		$_arLabelOptions = CPSHelp::getOption( $arOptions, 'labelOptions', array(), true );
		$_arWidgetOptions = CPSHelp::getOption( $arOptions, 'widgetOptions', array(), true );
		$_arFieldData = CPSHelp::getOption( $arOptions, 'data', null, true );
		return parent::simpleActiveBlock( $eFieldType, $oModel, $sColName, $arOptions, $_sLabel, $_arLabelOptions, $_arFieldData, $_arWidgetOptions );
	}
	
	/**
	* Convienence instance of CPSHelp::getOption
	* 
	* @param array $arOptions
	* @param string $sKey
	* @param mixed $oDefault
	* @param boolean $bUnset
	* @return mixed
	* @static
	* @access public
	*/
	public static function o( &$arOptions = array(), $sKey, $oDefault = null, $bUnset = false )
	{
		return CPSHelp::getOption( $arOptions, $sKey, $oDefault, $bUnset );
	}
	
	/**
	* Sets an option in the given array
	*
	* @param array $arOptions
	* @param string $sKey
	* @param mixed $oValue
	* @returns mixed The new value of the key
	* @static
	* @access public
	*/
	public static function so( array $arOptions, $sKey, $oValue = null )
	{
		return CPSHelp::setOption( $arOptions, $sKey, $oValue );
	}
	
	/**
	* Unsets an option in the given array
	*
	* @param array $arOptions
	* @param string $sKey
	* @returns mixed The new value of the key
	* @static
	* @access public
	*/
	public static function uo( array $arOptions, $sKey )
	{
		return CPSHelp::setOption( $arOptions, $sKey, null );
	}

	/**
	* Returns the number of "interval" between the two dates
	* 
	* @param string $dtStart
	* @param string $dtEnd
	* @param mixed $sInterval
	* @returns DateInterval
	* @static
	* @access public
	*/
	public static function dateDiff( $dtStart, $dtEnd )
	{
		return CPSHelp::dateDiff( $dtStart, $dtEnd );
	}
	
	/**
	* Returns an array suitable as list data from an array of models
	* 
	* @param array $arData
	* @param string $sKeyColumn
	* @param string $sValueColumn
	* @returns array
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
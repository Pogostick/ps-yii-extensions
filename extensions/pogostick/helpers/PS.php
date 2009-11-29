<?php
/**
 * PS.php class file.
 *
 * @filesource
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://www.pogostick.com Pogostick, LLC.
 * @package psYiiExtensions
 * @subpackage helpers
 * @since v1.0.6
 * @version SVN: $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified  $Date$
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
	*/
	public static function field( $eFieldType, $oModel, $sColName, $arOptions = array() )
	{
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
	*/
	public static function o( &$arOptions = array(), $sKey, $oDefault = null, $bUnset = false )
	{
		return CPSHelp::getOption( $arOptions, $sKey, $oDefault, $bUnset );
	}

	/**
	* Returns the number of "interval" between the two dates
	* 
	* @param string $dtStart
	* @param string $dtEnd
	* @param mixed $sInterval
	*/
	public static function dateDiff( $dtStart, $dtEnd )
	{
		return CPSHelp::dateDiff( $dtStart, $dtEnd );
	}

}
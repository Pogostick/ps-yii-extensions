<?php
/**
 * CPSActiveWidgets class file.
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://ps-yii-extensions.googlecode.com
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @license http://www.pogostick.com/license/
 * @package psYiiExtensions
 */

/**
 * CPS provides
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @version SVN: $Id$
 * @subpackage Widgets
 * @since 1.0.0
 */
class CPSActiveWidgets
{
	/**
	* Create a drop downlist filled with codes give a code type.
	* 
	* @param string $sAttribute
	* @param string $sCodeType
	* @param array $arHtmlOptions
	* @param integer $iDefaultUID
	* @return string
	*/
	public static function activeCodeDropDownList( $sAttribute, $sCodeType, $arHtmlOptions = array(), $iDefaultUID = 0 )
	{
		$_oModel = Code::model();
		
		CHtml::resolveNameID( $_oModel, $sAttribute, $arHtmlOptions );
		$_sSel = $_oModel->$attribute;
		
		$_sOptions = "\n" . CHtml::listOptions( $iDefaultUID, $_oModel->findAll( "code_type = '$sCodeType'" ), $arHtmlOptions );
		CHtml::clientChange( 'change', $arHtmlOptions );
		
		if ( $_oModel->hasErrors( $sAttribute ) )
			CHtml::addErrorCss( $arHtmlOptions );
			
		if ( isset( $arHtmlOptions[ 'multiple' ] ) )
		{
			if ( substr( $arHtmlOptions[ 'name' ], -2 ) !== '[]' )
				$arHtmlOptions[ 'name' ] .= '[]';
		}
		
		return CHtml::tag( 'select', $arHtmlOptions, $_sOptions );
	}
	
	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	//********************************************************************************
	//* Property Access Methods
	//********************************************************************************

	//********************************************************************************
	//* Magic Method Ovverides
	//********************************************************************************

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	//********************************************************************************
	//* Private Methods
	//********************************************************************************
}
<?php
/**
 * CPSForm class file.
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
 * CPSForm provides form helper functions
 */
class CPSForm
{
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

	/**
	* Creates a standard form header
	* 
	* Pass in menu item array as follows:
	* 
	* array( 'id' => array( 'label', 'url', 'icon' ), ... )
	* 
	* Each item is made into a jQuery UI button with an optional jQUI icon. 
	* 
	* Example:
	* 
	* 	echo CPSForm::formHeader( 'Site Manager', 
	*		array( 'new' => 
	*			array(
	*				'label' => 'New Site',
	*				'url' => array( 'create' ),
	* 				'formId' => 'id for form' // optional
	*				'icon' => 'circle-plus',
	*			) 	
	*		)
	* 	);
	* 
	* @param string $sTitle
	* @param array $arMenuItems
	* @param string $sDivClass
	* @returns string
	*/
	public static function formHeader( $sTitle, $arMenuItems = array(), $sDivClass = 'form-header' )
	{
		$_bIcon = false;
		$_sClass = $_sLink = $_sOut = null;
		
		//	Create menu
		foreach ( $arMenuItems as $_sId => $_arItem ) 
		{
			$_sOnClick = null;
			$_sLabel = CPSHelp::getOption( $_arItem, 'label', 'Menu Item', true );
			$_sLink = CPSActiveWidgets::normalizeUrl( CPSHelp::getOption( $_arItem, 'url', array('#'), true ) );
			$_sOut .= CPSActiveWidgets::jquiButton( $_sLabel, $_sLink, $_arItem );
		}
		
		return <<<HTML
		<div class="{$sDivClass}">
			<h1>{$sTitle}</h1>
			<p>{$_sOut}</p>
		</div>
HTML;
	}
	
	//********************************************************************************
	//* Private Methods
	//********************************************************************************
}
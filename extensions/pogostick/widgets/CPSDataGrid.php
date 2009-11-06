<?php
/**
 * CPSDataGrid class file.
 *
 * @filesource
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://www.pogostick.com Pogostick, LLC.
 * @package psYiiExtensions
 * @subpackage widgets
 * @since v1.0.6
 * @version SVN: $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified  $Date$
 */
class CPSDataGrid
{
	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	public static function create( $sDataName, $arModel, $arColumns = array(), $arActions = array(), $oSort = null, $oPages = null, $arPagerOptions = array() )
	{
		$_sOut = self::beginDataGrid( $arModel, $oSort, $arColumns, ! empty( $arActions ) );
		$_sOut .= self::getDataGridRows( $arModel, $arColumns, $arActions, $sDataName );
		$_sOut .= self::endDataGrid();
		
		if ( $oPages ) Yii::app()->controller->widget( 'CLinkPager', array_merge( array( 'pages' => $oPages ), $arPagerOptions ) );
		
		return $_sOut;
	}

	/**
	* Creates a data grid
	* 
	* @param CModel $oModel
	* @param CSort $oSort
	* @param array $arColumns
	* @param boolean $bAddActions
	* @return string
	*/
	public static function beginDataGrid( $oModel, $oSort = null, $arColumns = array(), $bAddActions = true )
	{
		$_sHeaders = null;
		
		foreach ( $arColumns as $_sColumn )
		{
			$_sColumn = CPSTransform::cleanColumn( $_sColumn );
			$_sHeaders .= CHtml::tag( 'th', array(), ( $oSort ) ? $oSort->link( $_sColumn ) : $_sColumn );
		}	

		if ( $bAddActions && ! empty( $oModel ) ) $_sHeaders .= CHtml::tag( 'th', array(), 'Actions' );
			
		return CHtml::tag( 'table', array( 'class' => 'dataGrid' ), CHtml::tag( 'tr', array(), $_sHeaders ), false );
	}
	
	/***
	* Builds all rows for a dataGrid
	* If a column name is prefixed with an '@', it will be stripped and the column will be a link to the 'update' view
	* If a column name is prefixed with an '?', it will be stripped and the column will be treated as a boolean
	* 
	* @param array $arModel
	* @param array $arColumns
	* @param array $arActions
	* @param string $sDataName
	* @return string
	*/
	public static function getDataGridRows( $arModel, $arColumns = array(), $arActions = null, $sDataName = 'item' )
	{
		$_sViewName = null;
		$_sOut = empty( $arModel ) ? '<tr><td style="text-align:center" colspan="' . sizeof( $arColumns ) . '">No Records Found</td></tr>' : null;
		if ( null === $arActions ) $arActions = array( 'edit', 'delete' );

		foreach ( $arModel as $_iIndex => $_oModel )
		{
			$_sActions = null;
			$_sPK = $_oModel->getTableSchema()->primaryKey;
			$_sTD = CPSTransform::column( $_oModel, $arColumns );
				
			//	Build actions...
			if ( null !== $arActions && is_array( $arActions ) )
			{
				foreach ( $arActions as $_sAction )
				{
					if ( is_array( $_sAction ) )
					{
						$_sViewName = $_sAction[1];
						$_sAction = $_sAction[0];
					}
					
					switch ( $_sAction )
					{
						case 'edit':
							$_sActions .= CPSActiveWidgets::jquiButton( 'Edit', array( CPSHelp::nvl( $_sViewName, 'update' ), $_sPK => $_oModel->{$_sPK} ), array( 'iconOnly' => true, 'icon' => 'pencil', 'iconSize' => 'small' ) );
							break;
							
						case 'delete':
							$_sActions .= CPSActiveWidgets::jquiButton( 'Delete', array( CPSHelp::nvl( $_sViewName, 'delete' ), $_sPK => $_oModel->{$_sPK} ),
								array(
									'confirm' => "Do you really want to delete this {$sDataName}?",
									'iconOnly' => true, 
									'icon' => 'trash', 
									'iconSize' => 'small'
								)
							);
							break;
					}
				}
				
				$_sTD .= CHtml::tag( 'td', array( 'class' => 'grid-actions' ), $_sActions );
			}
			
			$_sOut .= CHtml::tag( 'tr', array(), $_sTD );
		}
		
		return $_sOut;
	}
	
	/**
	* Closes a data grid
	* 
	*/
	public static function endDataGrid()
	{
		return '</TABLE>';
	}
	
}
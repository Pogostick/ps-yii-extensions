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
class CPSDataGrid extends CPSHelperBase
{
	//********************************************************************************
	//* Public Methods
	//********************************************************************************
	
    /**
    * Outputs a data grid with pager on the bottom
    * 
    * @param string $sDataName
    * @param array $arModel
    * @param array $arColumns
    * @param array $arActions
    * @param CSort $oSort
    * @param CPagination $oPages
    * @param array $arPagerOptions
    * @param string $sLinkView
    */
	public static function create( $sDataName, $arModel, $arColumns = array(), $arActions = array(), $oSort = null, $oPages = null, $arPagerOptions = array(), $sLinkView = 'update' )
	{
		$_sPK = PS::o( $arPagerOptions, 'pk', null, true );

		//	Build pager...
		$_oWidget = Yii::app()->controller->createWidget( 'CPSLinkPager', array_merge( array( 'pages' => $oPages ), $arPagerOptions ) );
		//	Build grid...
		if ( $_oWidget->pagerLocation == CPSLinkPager::TOP_LEFT || $_oWidget->pagerLocation == CPSLinkPager::TOP_RIGHT ) $_oWidget->run();
		
		$_sOut = self::beginDataGrid( $arModel, $oSort, $arColumns, ! empty( $arActions ) );
		$_sOut .= self::getDataGridRows( $arModel, $arColumns, $arActions, $sDataName, $sLinkView, $_sPK );
		$_sOut .= self::endDataGrid();
		
		if ( $_oWidget->pagerLocation == CPSLinkPager::BOTTOM_LEFT || $_oWidget->pagerLocation == CPSLinkPager::BOTTOM_RIGHT ) $_oWidget->run();
		
		return $_sOut;
	}

    /**
    * Outputs a data grid with pager on the bottom
    * 
    * @param string $sDataName
    * @param array $arModel
    * @param array $arColumns
    * @param array $arActions
    * @param CSort $oSort
    * @param CPagination $oPages
    * @param array $arPagerOptions
    * @param string $sLinkView
    */
	public static function createEx( $arModel, $arOptions = array() )
	{
		$_sPK = PS::o( $arOptions, 'pk', null, true );
		$_sDataName = self::getOption( $arOptions, 'dataItemName', 'Your Data' );
		$_arColumns = self::getOption( $arOptions, 'columns', array() );
		$_arActions = self::getOption( $arOptions, 'actions', array() );
		$_oSort = self::getOption( $arOptions, 'sort', null );
		$_oPages = self::getOption( $arOptions, 'pages', null );
		$_arPagerOptions = self::getOption( $arOptions, 'pagerOptions', array() );
		$_sLinkView = self::getOption( $arOptions, 'linkView', 'update' );
		$_iPagerLocation = self::getOption( $_arPagerOptions, 'location', CPSLinkPager::TOP_RIGHT, true );

		//	Create widget...
		if ( $_oPages ) $_oWidget = Yii::app()->controller->createWidget( 'CPSLinkPager', array_merge( array( 'pages' => $_oPages ), $_arPagerOptions ) );
		if ( $_oWidget ) $_oWidget->pagerLocation = self::nvl( $_iPagerLocation, $_oWidget->pagerLocation );

		//	Where do you want it?
		if ( $_oWidget ) if ( $_oWidget->pagerLocation == CPSLinkPager::TOP_LEFT || $_oWidget->pagerLocation == CPSLinkPager::TOP_RIGHT ) $_oWidget->run();
		
		//	Build our grid
		$_sOut = self::beginDataGrid( $arModel, $_oSort, $_arColumns, ! empty( $_arActions ) );
		$_sOut .= self::getDataGridRows( $arModel, $_arColumns, $_arActions, $_sDataName, $_sLinkView, $_sPK );
		$_sOut .= self::endDataGrid();
		
		//	Display on the bottom...
		if ( $_oWidget ) if ( $_oWidget->pagerLocation == CPSLinkPager::BOTTOM_LEFT || $_oWidget->pagerLocation == CPSLinkPager::BOTTOM_RIGHT ) $_oWidget->run();
		
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
		
		foreach ( $arColumns as $_sKey => $_oColumn )
		{
			$_sColumn = ( is_array( $_oColumn ) ? $_sKey : $_oColumn );
			$_sColumn = CPSTransform::cleanColumn( $_sColumn );
			$_sLabel = PS::o( $_oColumn, 'label', ( $oSort ) ? $oSort->link( $_sColumn ) : P::o( $oModel->getAttributeLabel( $_sColumn ), $_sColumn ), true );
			$_sHeaders .= CHtml::tag( 'th', array(), $_sLabel );
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
	* @param string $sLinkView
	* @return string
	*/
	public static function getDataGridRows( $arModel, $arColumns = array(), $arActions = null, $sDataName = 'item', $sLinkView = null, $sPK = null )
	{
		$_sViewName = $sLinkView;
		$_sOut = empty( $arModel ) ? '<tr><td style="text-align:center" colspan="' . sizeof( $arColumns ) . '">No Records Found</td></tr>' : null;
		if ( null === $arActions ) $arActions = array( 'edit', 'delete' );
		$_arOptions = CPSHelp::getOption( $arActions, 'options', array(), true );
		$_sLockColumn = CPSHelp::getOption( $_arOptions, 'lockColumn', null, true );

		foreach ( $arModel as $_iIndex => $_oModel )
		{
			$_sActions = null;
			$_sPK = PS::nvl( $sPK, $_oModel->getTableSchema()->primaryKey );
			$_sTD = CPSTransform::column( $_oModel, $arColumns, $sLinkView );
				
			//	Build actions...
			if ( $_sPK && null !== $arActions && is_array( $arActions ) )
			{
				foreach ( $arActions as $_oParts )
				{
					$_sAction = $_oParts;
					
					if ( is_array( $_oParts ) )
					{
						$_sAction = $_oParts[0];
						$_sViewName = $_oParts[1];
					}
					
					if ( $_sAction == 'lock' && ! $_sLockColumn )
						continue;
					
					switch ( $_sAction )
					{
						case 'lock':	//	Special case if model contains lock column
							$_sLockName = ( ! $_oModel->{$_sLockColumn} ) ? 'Lock' : 'Unlock';
							$_sIconName = ( $_oModel->{$_sLockColumn} ) ? 'locked' : 'unlocked';

							//	Lock import file
							$_sActions .= CPSActiveWidgets::jquiButton( $_sLockName, array( CPSHelp::nvl( $_sViewName, 'toggleLock' ), $_sPK => $_oModel->{$_sPK} ),
								array(
									'confirm' => "Do you really want to " . strtolower( $_sLockName ) . " this {$sDataName}?",
									'iconOnly' => true, 
									'icon' => $_sIconName,
									'iconSize' => 'small'
								)
							);
							break;
						
						case 'edit':
							$_sActions .= CPSActiveWidgets::jquiButton( 'Edit', array( self::nvl( $_sViewName, $sLinkView, 'update' ), $_sPK => $_oModel->{$_sPK} ), array( 'iconOnly' => true, 'icon' => 'pencil', 'iconSize' => 'small' ) );
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
							
						default:	//	Catchall for prefab stuff...
							$_sActions .= str_ireplace( '%%PK_VALUE%%', $_oModel->{$_sPK}, $_sAction );
							break;
					}
				}
				
				$_sTD .= CHtml::tag( 'td', array( 'class' => 'grid-actions' ), '<div class="_grid_actions">' . $_sActions . '<hr /></div>' );
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
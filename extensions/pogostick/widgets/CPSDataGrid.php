<?php
/*
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * Simple data grid
 * 
 * @package 	psYiiExtensions
 * @subpackage 	widgets
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id$
 * @since 		v1.0.6
 *  
 * @filesource
 */
class CPSDataGrid extends CPSHelperBase
{
	//********************************************************************************
	//* Private Members
	//********************************************************************************
	
	/**
	* Columns in this grid
	* 
	* @var int
	*/
	protected static $m_iColumnCount;
	
	/**
	* Grid options
	* 
	* @var array
	*/
	protected static $m_arGridOptions = array();
	public function getGridOptions() { return $this->m_arGridOptions; }
	public function setGridOptions( $arValue ) { $this->m_arGridOptions = $arValue; }

	//********************************************************************************
	//* Public Methods
	//********************************************************************************
	
    /**
    * Outputs a data grid with pager on the bottom
    * 
    * @param array $arModel Array of models
    * @param array $arOptions Grid options
    * @returns string
    */
	public static function createEx( $arModel, $arOptions = array() )
	{
		//	Initialize...
		$_sOut = $_sPager = null;
		self::$m_iColumnCount = 0;
		self::$m_arGridOptions = $arOptions;
		
		//	Store our model, get our options
		self::$m_arGridOptions['data'] = $arModel;
		$_arPagerOptions = self::getOption( $arOptions, 'pagerOptions', array(), true );
		$_sPagerClass = PS::o( $arOptions, 'pagerClass', 'CPSLinkPager', true );
		$_bAccordion = PS::o( $arOptions, 'accordion', false, true );
		$_sGridHeader = PS::o( $arOptions, 'gridHeader', null, true );
		$_oPages = PS::o( $arOptions, 'pages', null, true );
		
		//	Only work with CPSLinkPagers
		if ( ! is_a( $_sPagerClass, 'CPSLinkPager' ) ) $_sPagerClass = 'CPSLinkPager';

		$_iPagerLocation = self::getOption( $_arPagerOptions, 'location', $_sPagerClass::TOP_RIGHT, true );

		//	Create widget...
		if ( $_oPages ) 
		{
			$_oWidget = Yii::app()->controller->createWidget( $_sPagerClass, array_merge( array( 'pages' => $_oPages ), $_arPagerOptions ) );
		
			if ( $_oWidget ) 
			{
				$_oWidget->pagerLocation = self::nvl( $_iPagerLocation, $_oWidget->pagerLocation );
				$_sPager = $_oWidget->run( true );

				//	Where do you want it?
				if ( $_oWidget->pagerLocation == $_sPagerClass::TOP_LEFT || $_oWidget->pagerLocation == $_sPagerClass::TOP_RIGHT ) $_sOut .= $_sPager;
			}
		}

		//	Add accordion header if requested...
		if ( $_bAccordion ) echo '<h3 class="ui-accordion-header ui-helper-reset ui-state-active ui-corner-top ps-grid-accordion-header"><span class="ui-icon ui-icon-triangle-1-s" ></span><a href="#"><strong>' . $_sGridHeader . '</strong></a></h3>';
		
		//	Build our grid
		$_sOut .= self::beginDataGrid();
		$_sOut .= self::getDataGridRows();
		$_sOut .= self::endDataGrid();

		//	Display on the bottom...
		if ( $_oWidget && ( $_oWidget->pagerLocation == $_sPagerClass::BOTTOM_LEFT || $_oWidget->pagerLocation == $_sPagerClass::BOTTOM_RIGHT ) ) 
			$_sOut .= $_sPager;
		
		return $_sOut;
	}

	/**
	* Creates a data grid from options
	* @return string
	*/
	protected static function beginDataGrid()
	{
		$oSort = PS::o( self::$m_arGridOptions, 'sort', null );
		$arColumns = PS::o( self::$m_arGridOptions, 'columns', array() );
		$arModel = PS::o( self::$m_arGridOptions, 'data', null );
		$_arActions = PS::o( self::$m_arGridOptions, 'actions', array() );
		$bAddActions = ! empty( $_arActions );
		$_sGridId = PS::o( self::$m_arGridOptions, 'id', null, true );
		$_sGridClass = PS::o( self::$m_arGridOptions, 'gridClass', 'dataGrid', true );
		
		$_sHeaders = null;
		$_oModel = is_array( $arModel ) && count( $arModel ) ? current( $arModel ) : null;

		if ( ! $_oModel && null != ( $_sModelName = PS::o( self::$m_arGridOptions, 'modelName' ) ) )
			$_oModel = new $_sModelName();

		foreach ( $arColumns as $_sKey => $_oColumn )
		{
			$_sColumn = CPSTransform::cleanColumn( ( is_array( $_oColumn ) ? $_sKey = array_shift( $_oColumn ) : $_oColumn ) );
			if ( $_oModel ) $_sModelLabel = $_oModel->getAttributeLabel( $_sColumn );
			$_sLabel = PS::o( $_oColumn, 'label', ( $oSort ) ? self::appendSortArrow( $oSort->link( $_sColumn ) ) : ( $_sModelLabel ? $_sModelLabel : $_sColumn ), true );
			$_sHeaders .= CHtml::tag( 'th', array(), $_sLabel );
			self::$m_iColumnCount++;
		}	

		if ( $bAddActions ) 
		{
			$_sHeaders .= CHtml::tag( 'th', array(), 'Actions' );
			self::$m_iColumnCount++;
		}
			
		//	Begin our grid
		$_arTableOpts = array(
			'class' => $_sGridClass,
		);
		
		if ( $_sGridId ) $_arTableOpts['id'] = $_sGridId;
		
		return PS::tag( 'table', $_arTableOpts, PS::tag( 'tr', array(), $_sHeaders ), false );
	}
	
	/***
	* Builds all data rows for a grid
	* If a column name is prefixed with an '@', it will be stripped and the column will be a link to the 'update' view
	* If a column name is prefixed with an '?', it will be stripped and the column will be treated as a boolean
	* 
	* @returns string
	*/
	protected static function getDataGridRows()
	{
		//	Pull are variables from the options
		$arModel = PS::o( self::$m_arGridOptions, 'data', array() );
		$sLinkView = PS::o( self::$m_arGridOptions, 'linkView', null );
		$sDataName = PS::o( self::$m_arGridOptions, 'dataItemName', 'item' );
		$arColumns = PS::o( self::$m_arGridOptions, 'columns', array() );
		$arActions = PS::o( self::$m_arGridOptions, 'actions', null );
		$sPK = PS::o( self::$m_arGridOptions, 'pk', null );
		$bEncode = PS::o( self::$m_arGridOptions, 'encode', true );
		$arDivComment = PS::o( self::$m_arGridOptions, 'divComment', array() );
		$_sRowIdTemplate = PS::o( self::$m_arGridOptions, 'rowIdTemplate', '_grid_row_{pk}' );
		
		//	sub options
		$_arOptions = PS::o( $arActions, 'options', array(), true );
		$_sLockColumn = PS::o( $_arOptions, 'lockColumn', null, true );
		
		//	Build the grid rows
		$_sOut = null;
		$_iRow = 0;
		if ( null === $arActions ) $arActions = array( 'edit', 'delete' );
		
		if ( ! $arModel || ( is_array( $arModel ) && ! count( $arModel ) ) ) 
			$_sOut .= CHtml::tag( 'tr', array(), PS::tag( 'td', array( 'class' => 'ps-data-grid-no-data-found', 'colspan' => self::$m_iColumnCount ), 'No Records Found' ) );
		else
		{
			foreach ( $arModel as $_iIndex => $_oModel )
			{
				$_sActions = null;
				$_sPK = PS::nvl( $sPK, $_oModel->getTableSchema()->primaryKey );
				$_sTD = CPSTransform::column( $_oModel, $arColumns, $sLinkView, 'td', array( 'encode' => $bEncode ) );
					
				//	Build actions...
				if ( $_sPK && ! empty( $arActions ) )
				{
					foreach ( $arActions as $_oParts )
					{
						$_sAction = $_oParts;
						
						//	Our default view (update)
						$_sViewName = PS::nvl( $sLinkView, 'update' );

						//	If action is an array, first element is action, second is view (which can also be an array)
						if ( is_array( $_oParts ) )
						{
							$_sAction = $_oParts[0];
							$_sViewName = $_oParts[1];
						}
						
						//	Skip lock actions on non-lockable columns
						if ( $_sAction == 'lock' && ! $_sLockColumn )
							continue;
							
						//	Fix up link view array...
						$_arLink = array( $_sViewName );
						if ( is_array( $_sViewName ) ) $_arLink = $_sViewName;
						
						//	Stuff in the PK(s)
						$_arLink[ $_sPK ] = $_oModel->{$_sPK};
						foreach ( $_arLink as $_sKey => $_sValue )
						{
							foreach ( array_keys( $_oModel->getAttributes() ) as $_sAttribute )
							$_arLink[ $_sKey ] = str_ireplace( "%%{$_sAttribute}%%", $_oModel->{$_sAttribute}, $_arLink[ $_sKey ] );
						}

						//	Add the action
						switch ( $_sAction )
						{
							case 'lock':	//	Special case if model contains lock column
								$_sLockName = ( ! $_oModel->{$_sLockColumn} ) ? 'Lock' : 'Unlock';
								$_sIconName = ( $_oModel->{$_sLockColumn} ) ? 'locked' : 'unlocked';

								//	Lock import file
								$_sActions .= CPSActiveWidgets::jquiButton( $_sLockName, $_arLink,
									array(
										'confirm' => "Do you really want to " . strtolower( $_sLockName ) . " this {$sDataName}?",
										'iconOnly' => true, 
										'icon' => $_sIconName,
										'iconSize' => 'small'
									)
								);
								break;
							
							case 'view':
							case 'edit':
								$_sActions .= CPSActiveWidgets::jquiButton( 'Edit', $_arLink, array( 'iconOnly' => true, 'icon' => $_sAction == 'edit' ? 'pencil' : 'gear', 'iconSize' => 'small' ) );
								break;
								
							case 'delete':
								$_sActions .= CPSActiveWidgets::jquiButton( 'Delete', array( 'delete', $_sPK => $_oModel->{$_sPK} ),
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
				
				$_arRowOpts = array();
				
				if ( count( $arDivComment ) && $_oModel->hasErrors() )
					$_arRowOpts = array( 'class' => $arDivComment[1], 'title' => implode( ', ', current( $_oModel->getErrors() ) ) );
					
				//	Row id template? Fill it in
				if ( $_sRowIdTemplate ) 
				{
					if ( false !== stripos( $_sRowIdTemplate, '{#}' ) )
						$_arRowOpts['id'] = str_ireplace( '{#}', $_iRow, $_sRowIdTemplate );
					else if ( false !== stripos( $_sRowIdTemplate, '{pk}' ) && $_sPK )
						$_arRowOpts['id'] = str_ireplace( '{pk}', $_oModel->{$_sPK}, $_sRowIdTemplate );
				}
				
				$_sOut .= CHtml::tag( 'tr', $_arRowOpts, $_sTD );
				
				//	Add subrows...
				if ( ! empty( $_oModel->subRows ) )
				{
					foreach ( $_oModel->subRows as $_oRow )
					{
						$_arInnerOptions = self::smart_array_merge( PS::o( $_oRow, '_innerHtmlOptions', array(), true ), array( 'encode' => false ) );
						$_arOuterOptions = self::smart_array_merge( array( 'class' => 'ps-sub-row' ), PS::o( $_oRow, '_outerHtmlOptions', array(), true ) );
						
						$_sRow = CPSTransform::column( $_oRow, array_keys( $_oRow ), null, 'td', $_arInnerOptions );

						if ( ! empty( $arActions ) )
						{
							$_sRow .= CHtml::tag( 'td', self::smart_array_merge( $_arInnerOptions, array( 'class' => 'grid-actions' ) ), '<div class="_grid_actions">&nbsp;<hr /></div>' );
						}
							
						$_sOut .= CHtml::tag( 'tr', $_arOuterOptions, $_sRow );
					}
				}
				
				//	Increment row counter
				$_iRow++;
			}
		}
		
		return $_sOut;
	}
	
	/**
	* Closes a data grid
	* 
	*/
	protected static function endDataGrid()
	{
		return PS::closeTag( 'table' );
	}

	/**
	* Appends a nice little arrow to a sort link.
	* 
	* @param string $sLink
	*/
	protected static function appendSortArrow( $sLink )
	{
		return $sLink;
		
		$_iPosNext = stripos( $sLink, '-' );
		$_iPos = stripos( $sLink, '.desc' );
		
		if ( $_iPos !== false && $_iPos < $_iPosNext ) 
		{
			$_sDir = 's';
			$_sTitle = 'Sorted Ascending';
		}
		else
		{
			$_sTitle = 'Sorted Descending';
			$_sDir = 'n';
		}			

		return $sLink . PS::tag( 'span', array( 'title' => $_sTitle, 'class' => "ui-icon ui-icon-arrowthickstop-1-{$_sDir} ps-data-grid-sort-arrow"  ) );
	}	

	//********************************************************************************
	//* Deprecated
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
    * @param mixed $sLinkView
    * @deprecated Please use CPSDataGrid::createEx()
    * @see CPSDataGrid::createEx
    */
	public static function create( $sDataName, $arModel, $arColumns = array(), $arActions = array(), $oSort = null, $oPages = null, $arPagerOptions = array(), $sLinkView = 'update', $bEncode = true )
	{
		//	Build option array for createEx
		$_arOpts = array();
		$_arOpts['modelName'] = PS::o( $arPagerOptions, 'modelName', null, true );
		$_arOpts['accordion'] = PS::o( $arPagerOptions, 'accordion', false, true );
		$_arOpts['gridHeader'] = PS::o( $arPagerOptions, 'gridHeader', null, true );
		$_arOpts['divComment'] = PS::o( $arPagerOptions, 'divComment', array(), true );
		$_arOpts['pk'] = PS::o( $arPagerOptions, 'pk', null, true );

		$_arOpts['pages'] = $oPages;
		$_arOpts['sort'] = $oSort;
		$_arOpts['actions'] = $arActions;
		$_arOpts['columns'] = $arColumns;
		$_arOpts['dataItemName'] = $sDataName;
		$_arOpts['linkView'] = $sLinkView;
		$_arOpts['encode'] = $bEncode;
		$_arOpts['pagerOptions'] = $arPagerOptions;

		return self::createEx( $arModel, $_arOpts );
	}

}
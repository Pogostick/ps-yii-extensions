<?php
/**
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
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
 * @version 	SVN: $Id: CPSDataGrid.php 390 2010-07-03 04:40:47Z jerryablan@gmail.com $
 * @since 		v1.0.6
 *  
 * @filesource
 */
class CPSDataGrid implements IPSBase
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

	/**
	* Map of predefined menuButton names to types
	*/
	protected static $m_arMenuButtonMap = array(
		'none' 		=> null,
		'new'		=> PS::ACTION_CREATE,
		'create'	=> PS::ACTION_CREATE,
		'view'		=> PS::ACTION_VIEW,
		'edit'		=> PS::ACTION_EDIT,
		'update'	=> PS::ACTION_EDIT,
		'save'		=> PS::ACTION_SAVE,
		'delete'	=> PS::ACTION_DELETE,
		'lock' 		=> PS::ACTION_LOCK,
		'unlock'	=> PS::ACTION_UNLOCK,
		'admin'		=> PS::ACTION_ADMIN,
		'cancel'	=> PS::ACTION_CANCEL,
		'return'	=> PS::ACTION_CANCEL,
		'preview'	=> PS::ACTION_PREVIEW,
	);

	/**
	* Map of predefined row-level actions to names
	*/
	protected static $m_arActionMap = array(
		PS::ACTION_CREATE => 'create',
		PS::ACTION_VIEW => 'show',
		PS::ACTION_EDIT => 'edit',
		PS::ACTION_SAVE => 'save',
		PS::ACTION_DELETE => 'delete',
		PS::ACTION_LOCK => 'lock',
		PS::ACTION_UNLOCK => 'unlock',
		PS::ACTION_PREVIEW => 'preview',
	);

	/**
	 * Converts a string action to its corresponding enum
	 * @param string $sAction
	 * @return integer
	 */
	public static function getMenuButtonType( $sButton ) { return is_numeric( $sButton ) ? intval( $sButton ) : PS::o( self::$m_arMenuButtonMap, $sButton ); }
	
	//********************************************************************************
	//* Public Methods
	//********************************************************************************
	
    /**
    * Outputs a data grid with pager on the bottom
    * 
    * @param array $arModel Array of models
    * @param array $arOptions Grid options
    * @return string
    */
	public static function createEx( $arModel, $arOptions = array() )
	{
		//	Initialize...
		$_sFormHeader = $_sOut = $_sPager = null;
		self::$m_iColumnCount = 0;
		self::$m_arGridOptions = $arOptions;
		
		//	Register our css...
		PS::_rcf( PS::_publish( Yii::getPathOfAlias( 'pogostick.templates.css' ) . DIRECTORY_SEPARATOR . 'ps-data-grid.css' ) );
		
		//	Store our model, get our options
		self::$m_arGridOptions['data'] = $arModel;
		$_arPagerOptions = PS::o( $arOptions, 'pagerOptions', array(), true );
		$_sPagerClass = PS::o( $arOptions, 'pagerClass', 'CPSLinkPager', true );
		$_bAccordion = PS::o( $arOptions, 'accordion', false, true );
		$_sGridHeader = PS::o( $arOptions, 'gridHeader', null, true );
		$_oPages = PS::o( $arOptions, 'pages', null, true );
		$_arFormHeader = PS::o( $arOptions, 'formHeader', null, true );
		$_sItemName = PS::o( $arOptions, 'itemName', 'itme' );
		
		if ( null !== $_arFormHeader )
		{
			$_arFormHeader['flashMessage'] = PS::o( $arOptions, 'flashMessage' );
			$_arFormHeader['itemName'] = PS::o( $_arFormHeader, 'itemName', $_sItemName );
			$_sFormHeader = PS::o( $_arFormHeader, 'title', 'Form Header', true );
		}
		else
		{
			if ( null !== ( $_arMenuButtons = PS::o( $arOptions, 'menuButtons', null, true ) ) )
			{
				$_sFormHeader = PS::o( $_arFormHeader, 'title', 'Form Header', true );
				$_arFormHeader = array(
					'menuButtons' => $_arMenuButtons,
					'itemName' => PS::o( $arOptions, 'dataItemName', $_sItemName ),
				);
			}
		}
		
		//	Make a form header!
		if ( $_arFormHeader ) echo CPSForm::formHeaderEx( $_sFormHeader, $_arFormHeader );
		
		//	Only work with CPSLinkPagers
		if ( ! is_a( $_sPagerClass, 'CPSLinkPager' ) ) $_sPagerClass = 'CPSLinkPager';

		$_iPagerLocation = PS::o( $_arPagerOptions, 'location', PS::PL_TOP_RIGHT, true );

		//	Create widget...
		if ( $_oPages ) 
		{
			$_oWidget = Yii::app()->controller->createWidget( $_sPagerClass, array_merge( array( 'pages' => $_oPages ), $_arPagerOptions ) );
		
			if ( $_oWidget ) 
			{
				$_oWidget->pagerLocation = PS::nvl( $_iPagerLocation, $_oWidget->pagerLocation );
				$_sPager = $_oWidget->run( true );

				//	Where do you want it?
				if ( $_oWidget->pagerLocation == PS::PL_TOP_LEFT || $_oWidget->pagerLocation == PS::PL_TOP_RIGHT ) $_sOut .= $_sPager;
			}
		}

		//	Add accordion header if requested...
		if ( $_bAccordion ) echo '<h3 class="ui-accordion-header ui-helper-reset ui-state-active ui-corner-top ps-grid-accordion-header"><span class="ui-icon ui-icon-triangle-1-s" ></span><a href="#"><strong>' . $_sGridHeader . '</strong></a></h3>';
		
		//	Build our grid
		$_sOut .= self::beginDataGrid();
		$_sOut .= self::getDataGridRows();
		$_sOut .= self::endDataGrid();

		//	Display on the bottom...
		if ( $_oWidget && ( $_oWidget->pagerLocation == PS::PL_BOTTOM_LEFT || $_oWidget->pagerLocation == PS::PL_BOTTOM_RIGHT ) ) 
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
		$_sGridClass = PS::o( self::$m_arGridOptions, 'gridClass', 'ps-data-grid ui-widget-content', true );
		
		$_sHeaders = null;
		$_oModel = is_array( $arModel ) && count( $arModel ) ? current( $arModel ) : null;

		if ( ! $_oModel && null != ( $_sModelName = PS::o( self::$m_arGridOptions, 'modelName' ) ) )
			$_oModel = new $_sModelName();

		foreach ( $arColumns as $_sKey => $_oColumn )
		{
			$_sColumn = CPSTransform::cleanColumn( ( is_array( $_oColumn ) ? $_sKey = array_shift( $_oColumn ) : $_oColumn ) );
			if ( $_oModel ) $_sModelLabel = $_oModel->getAttributeLabel( $_sColumn );
			$_sLabel = PS::o( $_oColumn, 'label', ( $oSort ) ? self::appendSortArrow( $oSort->link( $_sColumn ) ) : ( $_sModelLabel ? $_sModelLabel : $_sColumn ), true );
			$_sHeaders .= PS::tag( 'th', array(), $_sLabel );
			self::$m_iColumnCount++;
		}	

		if ( $bAddActions ) 
		{
			$_sHeaders .= PS::tag( 'th', array( 'class' => 'ps-actions-header' ), 'Actions' );
			self::$m_iColumnCount++;
		}
			
		//	Begin our grid
		$_arTableOpts = array(
			'class' => $_sGridClass,
		);
		
		if ( $_sGridId ) $_arTableOpts['id'] = $_sGridId;
		
		return PS::tag( 'table', $_arTableOpts, PS::tag( 'tr', array( 'class' => 'ui-widget-header' ), $_sHeaders ), false );
	}
	
	/***
	* Builds all data rows for a grid
	* If a column name is prefixed with an '@', it will be stripped and the column will be a link to the 'update' view
	* If a column name is prefixed with an '?', it will be stripped and the column will be treated as a boolean
	* 
	* @return string
	*/
	protected static function getDataGridRows()
	{
		//	Pull are variables from the options
		$_sNoDataMessage = PS::o( self::$m_arGridOptions, 'noDataMessage', 'No Records Found', true );
		$arModel = PS::o( self::$m_arGridOptions, 'data', array() );
		$sLinkView = PS::o( self::$m_arGridOptions, 'linkView', null );
		$arColumns = PS::o( self::$m_arGridOptions, 'columns', array() );
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
		
		//	Build the output row
		$_arRowOpts = array();
		
		if ( count( $arDivComment ) && $_oModel->hasErrors() )
			$_arRowOpts = array( 'class' => $arDivComment[1], 'title' => implode( ', ', current( $_oModel->getErrors() ) ) );
			
		$_arRowOpts['class'] = PS::o( $_arRowOpts, 'class', 'ui-widget-content' );
		
		if ( ! $arModel || ( is_array( $arModel ) && ! count( $arModel ) ) ) 
		{
			$_sOut .= PS::tag( 'tr', $_arRowOpts, PS::tag( 'td', array( 'class' => 'ps-data-grid-no-data-found', 'colspan' => self::$m_iColumnCount ), $_sNoDataMessage ) );
		}
		else
		{
			$_sBaseClass = $_arOptions['class'];
			
			foreach ( $arModel as $_iIndex => $_oModel )
			{
				$_sActions = null;
				$_sPK = PS::nvl( $sPK, $_oModel->getTableSchema()->primaryKey );
				$_sTD = CPSTransform::column( $_oModel, $arColumns, $sLinkView, 'td', array( 'encode' => $bEncode ) );
					
				//	Build actions...
				if ( $_sActions = self::buildActions( $_oModel ) )
					$_sTD .= PS::tag( 'td', array( 'class' => 'ps-grid-actions' ), '<div class="ps-grid-actions-inner">' . $_sActions . '<hr /></div>' );
				
				//	Row id template? Fill it in
				if ( $_sRowIdTemplate ) 
				{
					if ( false !== stripos( $_sRowIdTemplate, '{#}' ) )
						$_arRowOpts['id'] = str_ireplace( '{#}', $_iRow, $_sRowIdTemplate );
					else if ( false !== stripos( $_sRowIdTemplate, '{pk}' ) && $_sPK )
						$_arRowOpts['id'] = str_ireplace( '{pk}', $_oModel->{$_sPK}, $_sRowIdTemplate );
				}
				
				//	Mark odd rows
				$_sClass = $_sBaseClass;
				
				if ( $_iRow % 2 == 0 ) 
				{
					$_sClass .= ' ui-state-default ps-data-grid-row-odd';
				}
				else
				{
					$_sClass .= ' ui-state-active ps-data-grid-row-even';
				}
				
				$_arOptions['class'] = $_sClass;
				
				$_sOut .= PS::tag( 'tr', $_arOptions, $_sTD );
				
				//	Add subrows...
				if ( ! empty( $_oModel->subRows ) )
				{
					foreach ( $_oModel->subRows as $_oRow )
					{
						$_arInnerOptions = PS::smart_array_merge( PS::o( $_oRow, '_innerHtmlOptions', array(), true ), array( 'encode' => false ) );
						$_arOuterOptions = PS::smart_array_merge( array( 'class' => 'ps-sub-row' ), PS::o( $_oRow, '_outerHtmlOptions', array(), true ) );
						
						$_sRow = CPSTransform::column( $_oRow, array_keys( $_oRow ), null, 'td', $_arInnerOptions );

						if ( ! empty( $arActions ) )
						{
							$_sRow .= PS::tag( 'td', PS::smart_array_merge( $_arInnerOptions, array( 'class' => 'grid-actions' ) ), '<div class="_grid_actions">&nbsp;<hr /></div>' );
						}
							
						$_sOut .= PS::tag( 'tr', $_arOuterOptions, $_sRow );
					}
				}
				
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
	//* Private Methods
	//********************************************************************************
	
	/**
	* Builds an action cell
	* 
	* @param CPSModel $oModel
	*/
	protected static function buildActions( CPSModel $oModel )
	{
		$_oId = $_sActions = $_eAction = null;
		$_sDataName = PS::o( self::$m_arGridOptions, 'dataItemName', 'item' );

		//	Fix up link view array...
		$_sLinkView = PS::o( self::$m_arGridOptions, 'linkView', null );
		$_sViewName = PS::nvl( $_sLinkView, 'update' );
			
		$_sPK = PS::nvl( PS::o( self::$m_arGridOptions, 'pk' ), $oModel->getTableSchema()->primaryKey );
		$_arActions = PS::o( self::$m_arGridOptions, 'actions', array( 'edit', 'delete' ) );
		
		//	sub options
		$_arOptions = PS::o( $_arActions, 'options', array(), true );
		$_sLockColumn = PS::o( $_arOptions, 'lockColumn', null, true );

		//	Build the actions
		foreach ( $_arActions as $_sKey => $_oParts )
		{
			$_eAction = PS::ACTION_NONE;
			$_sAction = ( is_numeric( $_oParts ) && $_oParts <= PS::ACTION_GENERIC ) ? self::$m_arActionMap[ $_oParts ] : $_oParts;
			$_arActionOptions = null;
			$_arLink = is_array( $_sViewName ) ? $_sViewName : array( $_sViewName );

			//	If action is an array, first element is action, second is viewName (which can also be an array)
			if ( is_array( $_oParts ) )
			{
				if ( isset( $_oParts[1] ) )
				{
					$_sViewName = $_oParts[1];
					$_arLink = is_array( $_sViewName ) ? $_sViewName : array( $_sViewName );
					$_sAction = array_shift( $_oParts );
					unset( $_oParts[1] );
				}
				else
				{
					//	The rest art action options...
					$_arActionOptions = $_oParts;
					$_arLink = PS::nvl( PS::o( $_arActionOptions, 'url' ), $_arLink );
					if ( ! is_array( $_arLink ) ) $_arLink = array( $_arLink );
				}
			}

			//	Invalid action? Skip
			if ( is_numeric( $_sAction ) )
				$_eAction = intval( $_sAction );
			else if ( is_array( $_sAction ) && is_numeric( $_sKey ) )
				$_eAction = intval( $_sKey );
			else if ( false === ( $_eAction = array_search( $_sAction, self::$m_arActionMap, true ) ) )
				continue;
			
			//	Skip lock actions on non-lockable columns
			if ( ! $_sLockColumn and ( $_eAction == PS::ACTION_LOCK || $_eAction == PS::ACTION_UNLOCK ) )
				continue;
				
			//	Stuff in the PK(s)
			$_arLink[ $_sPK ] = $_oId = $oModel->{$_sPK};
			foreach ( $_arLink as $_sKey => $_sValue )
			{
				if ( 0 != preg_match( '/\%\%(.*)+\%\%/i', $_arLink[$_sKey], $_arMatch ) )
				{
					if ( $_arMatch )
					{
						foreach ( array_keys( $oModel->getAttributes() ) as $_sAttribute )
						{
							$_arLink[ $_sKey ] = str_ireplace( "%%{$_sAttribute}%%", $oModel->{$_sAttribute}, $_arLink[ $_sKey ] );
						}
					}
				}
			}

			//	Add the action
			switch ( $_eAction )
			{
				/**
				* Creates a generic "action" button
				*/
				case PS::ACTION_GENERIC:
					$_sLabel = PS::o( $_arActionOptions, 'label' );
					$_sIconName = PS::o( $_arActionOptions, 'icon' );
					$_sConfirm = PS::o( $_arActionOptions, 'confirm' );

					//	Build an action
					$_sActions .= PS::jquiButton( $_sLabel, $_arLink,
						array(
							'confirm' => $_sConfirm,
							'iconOnly' => true, 
							'icon' => $_sIconName,
							'iconSize' => 'small'
						)
					);
					break;
					
				case PS::ACTION_LOCK:			//	Special case if model contains lock column
					$_sLockName = ( ! $oModel->{$_sLockColumn} ) ? 'Lock' : 'Unlock';
					$_sIconName = ( $oModel->{$_sLockColumn} ) ? 'locked' : 'unlocked';

					//	Lock import file
					$_sActions .= PS::jquiButton( $_sLockName, $_arLink,
						array(
							'confirm' => "Do you really want to " . strtolower( $_sLockName ) . " this {$_sDataName}?",
							'iconOnly' => true, 
							'icon' => $_sIconName,
							'iconSize' => 'small'
						)
					);
					break;
				
				case PS::ACTION_VIEW:
					$_sActions .= PS::jquiButton( 'View', $_arLink, array( 'iconOnly' => true, 'icon' => 'gear', 'iconSize' => 'small' ) );
					break;
					
				case PS::ACTION_EDIT:
					$_sActions .= PS::jquiButton( 'Edit', $_arLink, array( 'iconOnly' => true, 'icon' => 'pencil', 'iconSize' => 'small' ) );
					break;
					
				case PS::ACTION_DELETE:
					$_sActions .= PS::jquiButton( 'Delete', array( 'delete', $_sPK => $_oId ),
						array(
							'confirm' => "Do you really want to delete this {$_sDataName}?",
							'iconOnly' => true, 
							'icon' => 'trash', 
							'iconSize' => 'small'
						)
					);
					break;
					
				default:	//	Catchall for prefab stuff...
					$_sActions .= str_ireplace( '%%PK_VALUE%%', $_oId, $_sAction );
					break;
			}
		}
		
		return $_sActions;
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
    * @see CPSDataGrid::createEx
    * @deprecated Please use CPSDataGrid::createEx()
    */
	public static function create( $sDataName, $arModel, $arColumns = array(), $arActions = array(), $oSort = null, $oPages = null, $arPagerOptions = array(), $sLinkView = 'update', $bEncode = true )
	{
		//	Build option array for createEx
		return self::createEx( $arModel, 
			array(
				'modelName' 	=> PS::o( $arPagerOptions, 'modelName', null, true ),
				'accordion' 	=> PS::o( $arPagerOptions, 'accordion', false, true ),
				'gridHeader' 	=> PS::o( $arPagerOptions, 'gridHeader', null, true ),
				'divComment' 	=> PS::o( $arPagerOptions, 'divComment', array(), true ),
				'pk' 			=> PS::o( $arPagerOptions, 'pk', null, true ),
				'pages' 		=> $oPages,
				'sort' 			=> $oSort,
				'actions' 		=> $arActions,
				'columns' 		=> $arColumns,
				'dataItemName' 	=> $sDataName,
				'linkView' 		=> $sLinkView,
				'encode' 		=> $bEncode,
				'pagerOptions'	=> $arPagerOptions,
			)
		);
	}

}
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
    * @param string $sDataName
    * @param array $arModel
    * @param array $arColumns
    * @param array $arActions
    * @param CSort $oSort
    * @param CPagination $oPages
    * @param array $arPagerOptions
    * @param mixed $sLinkView
    */
	public static function create( $sDataName, $arModel, $arColumns = array(), $arActions = array(), $oSort = null, $oPages = null, $arPagerOptions = array(), $sLinkView = 'update', $bEncode = true )
	{
		$_sOut = $_sPager = null;
		self::$m_iColumnCount = 0;
		self::$m_arGridOptions = $arOptions;
		
		$_sPK = PS::o( $arPagerOptions, 'pk', null, true );
		$_sGridHeader = PS::o( $arPagerOptions, 'gridHeader', $sDataName, true );
		$_bAccordion = PS::o( $arPagerOptions, 'accordion', false, true );
		$_arDivComment = PS::o( $arPagerOptions, 'divComment', array(), true );

		//	Build pager...
		if ( $_oWidget = Yii::app()->controller->createWidget( 'CPSLinkPager', array_merge( array( 'pages' => $oPages ), $arPagerOptions ) ) )
		{
			$_sPager = $_oWidget->run( true );

			//	Build grid...
			if ( $_oWidget->pagerLocation == CPSLinkPager::TOP_LEFT || $_oWidget->pagerLocation == CPSLinkPager::TOP_RIGHT ) 
				$_sOut .= $_sPager;
		}
		
		//	Accordion header?
		if ( $_bAccordion ) echo '<h3 class="ui-accordion-header ui-helper-reset ui-state-active ui-corner-top"><span class="ui-icon ui-icon-triangle-1-s" ></span><a href="#"><strong>' . $_sGridHeader . '</strong></a></h3>';
		
		$_sOut .= self::beginDataGrid( $arModel, $oSort, $arColumns, ! empty( $arActions ) );
		$_sOut .= self::getDataGridRows( $arModel, $arColumns, $arActions, $sDataName, $sLinkView, $_sPK, $bEncode, $_arDivComment );
		$_sOut .= self::endDataGrid();
		
		if ( $_oWidget && ( $_oWidget->pagerLocation == CPSLinkPager::BOTTOM_LEFT || $_oWidget->pagerLocation == CPSLinkPager::BOTTOM_RIGHT ) )
			$_sOut .= $_sPager;

		return $_sOut;
	}

    /**
    * Outputs a data grid with pager on the bottom
    * 
    * @param array $arModel Array of models
    * @param array $arOptions Grid options
    * @returns string
    */
	public static function createEx( $arModel, $arOptions = array() )
	{
		$_sOut = $_sPager = null;
		self::$m_iColumnCount = 0;
		self::$m_arGridOptions = $arOptions;
		
		$_sPK = PS::o( $arOptions, 'pk', null, true );
		$_sDataName = self::getOption( $arOptions, 'dataItemName', 'Your Data' );
		$_arColumns = self::getOption( $arOptions, 'columns', array() );
		$_arActions = self::getOption( $arOptions, 'actions', array() );
		$_oSort = self::getOption( $arOptions, 'sort', null );
		$_oPages = self::getOption( $arOptions, 'pages', null );
		$_sGridHeader = PS::o( $arOptions, 'gridHeader', $sDataName, true );
		$_bAccordion = PS::o( $arOptions, 'accordion', false, true );
		$_bEncode = PS::o( $arOptions, 'encode', true, true );
		$_arDivComment = PS::o( $arOptions, 'divComment', array(), true );
		$_arPagerOptions = self::getOption( $arOptions, 'pagerOptions', array() );
		$_sLinkView = self::getOption( $arOptions, 'linkView', 'update' );
		$_sModelName = PS::o( $arOptions, 'modelName', null );
		
		$_iPagerLocation = self::getOption( $_arPagerOptions, 'location', CPSLinkPager::TOP_RIGHT, true );

		//	Create widget...
		if ( $_oPages ) 
		{
			$_oWidget = Yii::app()->controller->createWidget( 'CPSLinkPager', array_merge( array( 'pages' => $_oPages ), $_arPagerOptions ) );
		
			if ( $_oWidget ) 
			{
				$_oWidget->pagerLocation = self::nvl( $_iPagerLocation, $_oWidget->pagerLocation );
				$_sPager = $_oWidget->run( true );
				
				//	Where do you want it?
				if ( $_oWidget->pagerLocation == CPSLinkPager::TOP_LEFT || $_oWidget->pagerLocation == CPSLinkPager::TOP_RIGHT ) $_sOut .= $_sPager;
			}
		}

		//	Add accordion header if requested...
		if ( $_bAccordion ) echo '<h3 class="ui-accordion-header ui-helper-reset ui-state-active ui-corner-top ps-grid-accordion-header"><span class="ui-icon ui-icon-triangle-1-s" ></span><a href="#"><strong>' . $_sGridHeader . '</strong></a></h3>';
		
		//	Build our grid
		$_sOut .= self::beginDataGrid( $arModel, $_oSort, $_arColumns, ! empty( $_arActions ) );
		$_sOut .= self::getDataGridRows( $arModel, $_arColumns, $_arActions, $_sDataName, $_sLinkView, $_sPK, $_bEncode, $_arDivComment );
		$_sOut .= self::endDataGrid();
		
		//	Display on the bottom...
		if ( $_oWidget && ( $_oWidget->pagerLocation == CPSLinkPager::BOTTOM_LEFT || $_oWidget->pagerLocation == CPSLinkPager::BOTTOM_RIGHT ) ) 
			$_sOut .= $_sPager;
		
		return $_sOut;
	}

	/**
	* Creates a data grid
	* 
	* @param array $arModel
	* @param CSort $oSort
	* @param array $arColumns
	* @param boolean $bAddActions
	* @return string
	*/
	public static function beginDataGrid( $arModel, $oSort = null, $arColumns = array(), $bAddActions = true )
	{
		$_sHeaders = null;
		$_oModel = is_array( $arModel ) && count( arModel ) ? current( $arModel ) : null;

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
			
		return CHtml::tag( 'table', array( 'width' => '100%', 'class' => 'dataGrid' ), CHtml::tag( 'tr', array(), $_sHeaders ), false );
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
	* @param mixed $sLinkView
	* @return string
	*/
	public static function getDataGridRows( $arModel, $arColumns = array(), $arActions = null, $sDataName = 'item', $sLinkView = null, $sPK = null, $bEncode = true, $arDivComment = array() )
	{
		$_sOut = null;
		if ( null === $arActions ) $arActions = array( 'edit', 'delete' );
		$_arOptions = PS::o( $arActions, 'options', array(), true );
		$_sLockColumn = PS::o( $_arOptions, 'lockColumn', null, true );
		
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
			}
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

	/**
	* Appends a nice little arrow to a sort link.
	* 
	* @param string $sLink
	*/
	public static function appendSortArrow( $sLink )
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
}
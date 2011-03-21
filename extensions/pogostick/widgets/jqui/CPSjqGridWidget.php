<?php
/**
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * The CPSjqGridWidget allows the {@link http://www.trirand.com/blog/ jqGrid} to be used in Yii.
 *
 * Thanks to {@link http://www.yiiframework.com/forum/index.php?action=profile;u=20 MetaYii} for some ideas on valid options and callbacks.
 * 
 * @package 	psYiiExtensions.widgets
 * @subpackage 	jqui
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id: CPSjqGridWidget.php 395 2010-07-15 21:34:48Z jerryablan@gmail.com $
 * @since 		v1.0.0
 *  
 * @filesource
 */
class CPSjqGridWidget extends CPSjqUIWrapper
{
	//********************************************************************************
	//* Methods
	//********************************************************************************

	/**
	* Registers the needed CSS and JavaScript.
	* @param boolean If true, system will try to find jquery plugins based on the pattern jquery.<plugin-name[.min].js
	* @return CClientScript The current app's ClientScript object
	*/
	public function registerClientScripts( $bLocateScript = false )
	{
		//	Daddy...
		parent::registerClientScripts( $bLocateScript );
		
		//	Register scripts necessary
		$this->pushScriptFile( "{$this->extLibUrl}/jqGrid/js/i18n/grid.locale-en.js" );
		$this->pushScriptFile( "{$this->extLibUrl}/jqGrid/js/jquery.jqGrid.min.js" );

		//	Register css files...
		PS::_rcf( "{$this->extLibUrl}/jqGrid/css/ui.jqgrid.css", 'screen' );
		
		return PS::_cs();
	}

	//********************************************************************************
	//* Private methods
	//********************************************************************************

	/**
	* Generates the javascript code for the widget
	*
	* @return string
	*/
	protected function generateHtml()
	{
		$_sHtml =<<<CODE
<table id="{$this->id}"></table>
CODE;

		if ( $_sPager = trim( $this->pager, '.#' ) )
			$_sHtml .= '<div id="' . $_sPager . '"></div>';

		return( $_sHtml );
	}

	/**
	* Generates the javascript code for the widget
	*
	* @return string
	*/
	protected function generateJavascript()
	{
		$_id = $this->getId();

		$_apiOptions = $this->getOption( 'apiOptions', array(), true );
		$_arOptions = $this->makeOptions();

		$this->script =<<<CODE
jQuery('#{$_id}').jqGrid({$_arOptions});

CODE;

		if ( $_options = $_apiOptions->getValue() )
		{
			foreach ( $_options as $_key => $_option )
				$this->script .= 'jQuery(\'#' . $_id . '\').jqGrid(\'' . $_key . '\',\'' . $this->pager . '\',' . json_encode( $_option ) . ');' . PHP_EOL;
		}
		else
			$this->script .= 'jQuery(\'#' . $_id . '\').jqGrid(\'navGrid\',\'' . $this->pager . '\',{edit:false,add:false,del:false,search:true});' . PHP_EOL;

		return $this->script;
	}

	/**
	* Constructs and returns a jQuery widget
	* 
	* The options passed in are dynamically added to the options array and will be accessible 
	* and modifiable as normal (.i.e. $this->theme, $this->baseUrl, etc.)
	* 
	* @param array $options The options for the widget
	* @param string $sClass The class of the calling object if different
	* @return CPSjqGridWidget
	*/
	public static function create( $name = null, array $options = array() )
	{
		return parent::create( 
			PS::nvl( 
				$name, 
				self::PS_WIDGET_NAME 
			), 
			array_merge( 
				$options, 
				array( 
					'class' => __CLASS__ 
				) 
			) 
		);
	}
	
	/**
	* Handles and formats the query and Xml output for a jqGrid
	*
	* @param CModel $oModel The model to use for the query
	* @param CDbCriteria|string $oCriteria Can be a full CDbCriteria object or a comma separated list of columns to "SELECT"
	* @param array $arQSElems The query string elements in array format. Must be in PAGE, ROWS, SORTCOLUMN, SORTORDER. Defaults to 'page', 'rows', 'sidx', 'sord'
	* @return string The Xml data for the grid
	*/
	public static function asXml( $oModel, $oCriteria = null, $arQSElems = null, $bReturnString = false )
	{
		//	Defaults...
		$_iPage = PS::o( $_REQUEST, 0, 1 );
		$_iLimit = PS::o( $_REQUEST, 1, 25 );
		$_iSortCol = PS::o( $_REQUEST, 2, 1 );
		$_sSortOrder = PS::o( $_REQUEST, 3, 'asc' );
		$_sSearchField = PS::o( $_REQUEST, 4 );
		$_sSearchValue = PS::o( $_REQUEST, 5 );
		$_sSearchOperator = PS::o( $_REQUEST, 6 );
		$_arArgs = array( 'page', 'rows', 'sidx', 'sord', 'searchField', 'searchString', 'searchOper' );

		//	Use user argument naames?
		if ( $arQSElems )
		{
			unset( $_arArgs );
			$_arArgs = $arQSElems;
		}

		//	Get a count of rows for this result set
		$_iRowCount = $oModel->count( $oCriteria );

		//	Calculate paging info
		$_iTotalPages = ( $_iRowCount > 0 ) ? ceil( $_iRowCount / $_iLimit ) : 0;

		//	Sanity checks
		if ( $_iPage > $_iTotalPages ) $_iPage = $_iTotalPages;
		if ( $_iPage < 1 ) $_iPage = 1;

		//	Calculate starting offset
		$_iStart = $_iLimit * $_iPage - $_iLimit;

		//	Sanity check
		if ( $_iStart < 0 ) $_iStart = 0;

		//	Adjust the criteria for the actual query...
		$_dbc = new CDbCriteria();

		if ( $oCriteria instanceof CDbCriteria )
			$_dbc->mergeWith( $oCriteria );
		else if ( gettype( $oCriteria ) == 'string' )
			$_dbc->select = $oCriteria;
		
		//	Handle search requests...
		if ( $_sSearchField && $_sSearchValue && $_sSearchOperator )
		{
			$_sOrigSearchValue = $_sSearchValue;
			
			$_sCon = $_dbc->condition;
			$_sCon .= ' and ' . $_sSearchField;
			
			if ( ! is_numeric( $_sSearchField ) )
				$_sSearchValue = "'" . addslashes( $_sSearchValue ) . "'";

			switch ( $_sSearchOperator )
			{
				case 'eq': $_sCon .= ' = '; break;
				case 'ne': $_sCon .= ' <> '; break;
				case 'lt': $_sCon .= ' < '; break;
				case 'le': $_sCon .= ' <= '; break;
				case 'gt': $_sCon .= ' > '; break;
				case 'ge': $_sCon .= ' => '; break;
				case 'bw': $_sCon .= ' LIKE '; $_sSearchValue = "'%" . addslashes( $_sOrigSearchValue ) . "'"; break;
				case 'ew': $_sCon .= ' LIKE '; $_sSearchValue = "'" . addslashes( $_sOrigSearchValue ) . "%'"; break;
				case 'cn': $_sCon .= ' LIKE '; $_sSearchValue = "'%" . addslashes( $_sOrigSearchValue ) . "%'"; break;
			}
			
			$_dbc->condition = $_sCon . $_sSearchValue;
		}

		$_dbc->order = "{$_iSortCol} {$_sSortOrder}";
		$_dbc->limit = $_iLimit;
		$_dbc->offset = $_iStart;
		$_oRows = $oModel->findAll( $_dbc );

		//	Set appropriate content type
		if ( ! headers_sent() )
		{
			if ( stristr( $_SERVER[ 'HTTP_ACCEPT' ], "application/xhtml+xml" ) )
				header( "Content-type: application/xhtml+xml;charset=utf-8" );
			else
				header( "Content-type: text/xml;charset=utf-8" );
		}

		//	Now create the Xml...
		$_sOut = CPSTransform::asXml(
			$_oRows,
			array(
				'jqGrid' => true,
				'innerElements' => array(
					array( 'name' => 'page', 'type' => 'integer', 'value' => $_iPage ),
					array( 'name' => 'total', 'type' => 'integer', 'value' => $_iTotalPages ),
					array( 'name' => 'records', 'type' => 'integer', 'value' => $_iRowCount ),
				),
			)
		);

		//	Spit it out...
		if ( ! $bReturnString )
			echo "<?xml version='1.0' encoding='utf-8'?>" . $_sOut;
		else
			return( "<?xml version='1.0' encoding='utf-8'?>" . $_sOut );
	}
}

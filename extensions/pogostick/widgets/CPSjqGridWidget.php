<?php
/**
* CPSjqGridWidget class file.
*
* @author Jerry Ablan <jablan@pogostick.com>
* @link http://ps-yii-extensions.googlecode.com
* @copyright Copyright &copy; 2009 Pogostick, LLC
* @license http://www.gnu.org/licenses/gpl.html
*/

/**
* The CPSjqGridWidget allows the {@link http://www.trirand.com/blog/ jqGrid} to be used in Yii.
*
* Thanks to {@link http://www.yiiframework.com/forum/index.php?action=profile;u=20 MetaYii} for some ideas on valid options and callbacks.
*
* @author Jerry Ablan <jablan@pogostick.com>
* @version SVN: $Id$
* @filesource
* @package psYiiExtensions
* @subpackage Widgets
* @since 1.0.1
*/
class CPSjqGridWidget extends CPSjqUIWrapper
{
	//********************************************************************************
	//* Constants
	//********************************************************************************
	
	/**
	* The name of this widget
	*/
	const PS_WIDGET_NAME = 'jqGrid';
	/**
	* The path where the assets for this widget are stored (underneath the psYiiExtensions/external base
	*/
	const PS_EXTERNAL_PATH = '/jqGrid';

	//********************************************************************************
	//* Methods
	//********************************************************************************

	public function init()
	{
		parent::init();
	
		//	Set my name...	
		$this->widgetName = self::PS_WIDGET_NAME;
	}
		

	/**
	* Registers the needed CSS and JavaScript.
	*/
	public function registerClientScripts()
	{
		//	Daddy...
		$_oCS = parent::registerClientScripts();
		
		//	Reset the baseUrl for our own scripts
		$this->baseUrl = $this->extLibUrl . self::PS_EXTERNAL_PATH;

		//	Register scripts necessary
		$_oCS->registerScriptFile( "{$this->baseUrl}/js/i18n/grid.locale-en.js" );
		$_oCS->registerScriptFile( "{$this->baseUrl}/js/jquery.jqGrid.min.js" );

		//	Register css files...
		$_oCS->registerCssFile( "{$this->baseUrl}/css/ui.jqgrid.css", 'screen' );
		
		return $_oCS;
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
<table id="{$this->id}" class="scroll"></table><div id="{$this->pagerId}" class="scroll" style="text-align:center;"></div>
CODE;
		return( $_sHtml );
	}

	/**
	* Override of makeOptions to insert correct pager jQuery code
	*
	*/
	protected function makeOptions()
	{
		//	Fix up the pager...
		$_sPagerId = $this->pagerId;
		return str_replace( "'pagerId':'{$_sPagerId}'", "pager: jQuery('#{$_sPagerId}')", parent::makeOptions() );
	}
	
	/**
	* Generates the javascript code for the widget
	*
	* @return string
	*/
	protected function generateJavascript( $sClassName = null, $arOptions = null )
	{
		//	Call daddy...
		parent::generateJavascript( $sClassName, $arOptions );

		//	Add the pager...
		$this->script .= ".navGrid('#{$this->pagerId}',{edit:false,add:false,del:false});";

		return $this->script;
	}

	/**
	* Constructs and returns a jQuery widget
	* 
	* The options passed in are dynamically added to the options array and will be accessible 
	* and modifiable as normal (.i.e. $this->theme, $this->baseUrl, etc.)
	* 
	* @param array $arOptions The options for the widget
	* @param string $sClass The class of the calling object if different
	* @return CPSjqGridWidget
	*/
	public static function create( array $arOptions = array(), $sClass = __CLASS__ )
	{
		return parent::create( self::PS_WIDGET_NAME, $arOptions, $sClass );
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
		$_iPage = 1;
		$_iLimit = 25;
		$_iSortCol = 1;
		$_iTotalPages = 0;
		$_sSortOrder = 'asc';
		$_sSearchField;
		$_sSearchValue;
		$_sSearchOperator;
		$_arArgs = array( 'page', 'rows', 'sidx', 'sord', 'searchField', 'searchString', 'searchOper' );
		$_bHaveDBC = ( $oCriteria instanceof CDbCriteria );

		//	Use user argument naames?
		if ( $arQSElems )
		{
			unset( $_arArgs );
			$_arArgs = $arQSElems;
		}

		//	Get any passed in arguments
		if ( isset( $_REQUEST[ $_arArgs[ 0 ] ] ) ) $_iPage = $_REQUEST[ $_arArgs[ 0 ] ];
		if ( isset( $_REQUEST[ $_arArgs[ 1 ] ] ) ) $_iLimit = $_REQUEST[ $_arArgs[ 1 ] ];
		if ( isset( $_REQUEST[ $_arArgs[ 2 ] ] ) ) $_iSortCol = $_REQUEST[ $_arArgs[ 2 ] ];
		if ( isset( $_REQUEST[ $_arArgs[ 3 ] ] ) ) $_sSortOrder = $_REQUEST[ $_arArgs[ 3 ] ];
		if ( isset( $_REQUEST[ $_arArgs[ 4 ] ] ) ) $_sSearchField = $_REQUEST[ $_arArgs[ 4 ] ];
		if ( isset( $_REQUEST[ $_arArgs[ 5 ] ] ) ) $_sSearchValue = $_REQUEST[ $_arArgs[ 5 ] ];
		if ( isset( $_REQUEST[ $_arArgs[ 6 ] ] ) ) $_sSearchOperator = $_REQUEST[ $_arArgs[ 6 ] ];

		Yii::trace( var_export($_REQUEST,true));
		
		//	Get a count of rows for this result set
		$_iRowCount = $oModel->count( ( $_bHaveDBC ) ? $oCriteria : '' );

		//	Calculate paging info
		if ( $_iRowCount > 0 ) $_iTotalPages = ceil( $_iRowCount / $_iLimit );

		//	Sanity checks
		if ( $_iPage > $_iTotalPages ) $_iPage = $_iTotalPages;
		if ( $_iPage < 1 ) $_iPage = 1;

		//	Calculate starting offset
		$_iStart = $_iLimit * $_iPage - $_iLimit;

		//	Sanity check
		if ( $_iStart < 0 ) $_iStart = 0;

		//	Adjust the criteria for the actual query...
		$_dbc = new CDbCriteria();

		if ( $_bHaveDBC )
		{
			unset( $_dbc );
			$_dbc = $oCriteria;
		}
		else if ( 'string' == gettype( $oCriteria ) )
		{
			$_dbc->select = $oCriteria;
		}
		
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
		if ( ! headers_sent() ) header( "Content-type: " . ( ( stristr( $_SERVER[ 'HTTP_ACCEPT' ], "application/xhtml+xml" ) ) ? "application/xhtml+xml" : "text/xml" ) . ";charset=utf-8" );

		//	Now create the Xml...
		$_sOut = CPSHelp::asXml(
			$_oRows,
			array(
				self::PS_WIDGET_NAME => true,
				'innerElements' => array(
					array( 'name' => 'page', 'type' => 'integer', 'value' => $_iPage ),
					array( 'name' => 'total', 'type' => 'integer', 'value' => $_iTotalPages ),
					array( 'name' => 'records', 'type' => 'integer', 'value' => $_iRowCount ),
				),
			)
		);

		//	Spit it out...
		if ( $bReturnString ) return "<?xml version='1.0' encoding='utf-8'?>" . $_sOut;	
			
		//	Otherwise, just spit it out...
		echo "<?xml version='1.0' encoding='utf-8'?>" . $_sOut;	
	}
}
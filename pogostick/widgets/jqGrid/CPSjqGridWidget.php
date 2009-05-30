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
* @since 1.0.3
*/
class CPSjqGridWidget extends CPSWidget
{
	//********************************************************************************
	//* Methods
	//********************************************************************************

	/**
	* Constructs a CPSjqGridWidget
	*
	* @param mixed $oOwner
	* @return CPSjqGridWidget
	*/
	public function __construct( $oOwner = null )
	{
		parent::__construct( $oOwner );

		//	Set the valid options for this widget
		$this->addOptions(
			array(
				'altRows' => array( CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'boolean' ) ),
				'caption' => array( CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'string' ) ),
				'cellEdit' => array( CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'boolean' ) ),
				'cellsubmit' => array( CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'string', CPSOptionManager::META_ALLOWED => array( 'remote', 'clientarray' ) ) ),
				'cellurl' => array( CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'string' ) ),
				'colModel' => array( CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'array' ) ),
				'colNames' => array( CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'array' ) ),
				'datastr' => array( CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'string' ) ),
				'datatype' => array( CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'string', CPSOptionManager::META_ALLOWED => array( 'xml', 'xmlstring', 'json', 'jsonstring', 'clientside' ) ) ),
				'deselectAfterSort' => array( CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'boolean' ) ),
				'editurl' => array( CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'string' ) ),
				'expandcolumn' => array( CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'boolean' ) ),
				'forceFit' => array( CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'boolean' ) ),
				'gridstate' => array( CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'string', CPSOptionManager::META_ALLOWED => array( 'visible', 'hidden' ) ) ),
				'hiddengrid' => array( CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'boolean' ) ),
				'hidegrid' => array( CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'boolean' ) ),
				'height' => array( CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => array( 'string', 'integer' ) ) ),
				'imgpath' => array( CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'string' ) ),
				'jsonReader' => array( CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'array' ) ),
				'loadonce' => array( CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'boolean' ) ),
				'loadtext' => array( CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'string' ) ),
				'loadui' => array( CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'string', CPSOptionManager::META_ALLOWED => array( 'disable', 'enable', 'block' ) ) ),
				'multiselect' => array( CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'boolean' ) ),
				'mtype' => array( CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'string', CPSOptionManager::META_ALLOWED => array( 'GET', 'PUT' ) ) ),
				'multikey' => array( CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'string' ) ),
				'multiboxonly' => array( CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'boolean' ) ),
				'pagerId' => array( CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'string' ) ),
				'prmNames' => array( CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'array' ) ),
				'postData' => array( CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'array' ) ),
				'resizeclass' => array( CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'string' ) ),
				'rowNum' => array( CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'integer' ) ),
				'rowList' => array( CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'array' ) ),
				'scroll' => array( CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'boolean' ) ),
				'scrollrows' => array( CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'boolean' ) ),
				'sortclass' => array( CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'string' ) ),
				'shrinkToFit' => array( CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'boolean' ) ),
				'sortascimg' => array( CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'string' ) ),
				'sortdescimg' => array( CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'string' ) ),
				'sortname' => array( CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'string' ) ),
				'sortorder' => array( CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'string' ) ),
				'subGrid' => array( CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'boolean' ) ),
				'subGridModel' => array( CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'array' ) ),
				'subGridType' => array( CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'string' ) ),
				'subGridUrl' => array( CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'string' ) ),
				'theme' => array( CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'string', CPSOptionManager::META_ALLOWED => array( 'basic', 'coffee', 'green', 'sand', 'steel' ) ) ),
				'toolbar' => array( CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'array' ) ),
				'treeGrid' => array( CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'boolean' ) ),
				'tree_root_level' => array( CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'integer' ) ),
				'url' => array( CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'string' ) ),
				'userData' => array( CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'array' ) ),
				'viewrecords' => array( CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'boolean' ) ),
				'width' => array( CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'integer' ) ),
				'xmlReader' => array( CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'array' ) ),
			)
		);

		//	Set the valid callbacks
		$this->validCallbacks = array(
			'afterInsertRow',
			'gridComplete',
			'loadBeforeSend',
			'loadComplete',
			'loadError',
			'onCellSelect',
			'ondblClickRow',
			'onHeaderClick',
			'onRighClickRow',
			'onSelectAll',
			'onSelectRow',
			'onSortCol',
			'subGridRowExpanded',
			'subGridRowCollapsed',
			'subGridType',
		);
	}

	/***
	* Runs this widget
	*
	*/
	public function run()
	{
		//	Validate baseUrl
		if ( $this->isEmpty( $this->baseUrl ) )
			$this->baseUrl = $this->getExtLibUrl() . '/jqGrid';

		//	Register the scripts/css
		$this->registerClientScripts();

		//	Generate the HTML for this widget
		echo $this->generateHtml();
	}

	/**
	* Registers the needed CSS and JavaScript.
	*
	* @param string $sId
	*/
	public function registerClientScripts()
	{
		//	Daddy...
		$_oCS = parent::registerClientScripts();

		//	If image path isn't specified, set to current theme path
		if ( $this->isEmpty( $this->imgpath ) )
			$this->imgpath = "{$this->baseUrl}/themes/{$this->theme}/images";

		//	Register scripts necessary
		$_oCS->registerScriptFile( "{$this->baseUrl}/jquery.jqGrid.js?path=" . $this->baseUrl );
		$_oCS->registerScriptFile( "{$this->baseUrl}/js/jqModal.js" );
		$_oCS->registerScriptFile( "{$this->baseUrl}/js/jqDnR.js" );

		//	Get the javascript for this widget
		$_sScript = $this->generateJavascript();
		$_oCS->registerScript( 'Yii.' . __CLASS__ . '#' . $this->id, $_sScript, CClientScript::POS_READY );

		//	Register css files...
		$_oCS->registerCssFile( "{$this->baseUrl}/themes/{$this->theme}/grid.css", 'screen' );
		$_oCS->registerCssFile( "{$this->baseUrl}/themes/jqModal.css", 'screen' );
	}

	//********************************************************************************
	//* Private methods
	//********************************************************************************

	/**
	* Generates the javascript code for the widget
	*
	* @return string
	*/
	protected function generateJavascript()
	{
		$this->script = '';

		$_arOptions = $this->makeOptions();

		$this->script .=<<<CODE
jQuery("#{$this->id}").jqGrid( {$_arOptions} );
CODE;

		return( $this->script );
	}

	/**
	* Generates the javascript code for the widget
	*
	* @return string
	*/
	protected function generateHtml()
	{
		$_sHtml = '';
		$_sPagerId = $this->pagerId;

		$_sHtml .=<<<CODE
<table id="{$this->id}" class="scroll"></table>
<div id="{$_sPagerId}" class="scroll" style="text-align:center;"></div>
CODE;

		return( $_sHtml );
	}

	/**
	* Override of makeOptions to insert correct pager jQuery code
	*
	*/
	protected function makeOptions()
	{
		$_arOptions = $this->getPublicOptions();

		//	Fix up the pager...
		$_sPagerId = $this->pagerId;
		return str_replace( "'pagerId':'{$_sPagerId}'", "'pager': jQuery('#{$_sPagerId}')", parent::makeOptions() );
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
		$_sSortOrder = 'asc';
		$_arArgs = array( 'page', 'rows', 'sidx', 'sord' );
		$_bHaveDBC = ( $oCriteria instanceof CDbCriteria );

		//	Use user argument naames?
		if ( $arQSElems )
		{
			unset( $_arArgs );
			$_arArgs = $arQSElems;
		}

		//	Get any passed in arguments
		if ( isset( $_REQUEST[ $_arArgs[ 0 ] ] ) )
			$_iPage = $_REQUEST[ $_arArgs[ 0 ] ];

		if ( isset( $_REQUEST[ $_arArgs[ 1 ] ] ) )
			$_iLimit = $_REQUEST[ $_arArgs[ 1 ] ];

		if ( isset( $_REQUEST[ $_arArgs[ 2 ] ] ) )
			$_iSortCol = $_REQUEST[ $_arArgs[ 2 ] ];

		if ( isset( $_REQUEST[ $_arArgs[ 3 ] ] ) )
			$_sSortOrder = $_REQUEST[ $_arArgs[ 3 ] ];

		//	Get a count of rows for this result set
		$_iRowCount = $oModel->count( ( $_bHaveDBC ) ? $oCriteria : '' );

		//	Calculate paging info
		if ( $_iRowCount > 0 )
			$_iTotalPages = ceil( $_iRowCount / $_iLimit );
		else
			$_iTotalPages = 0;

		//	Sanity checks
		if ( $_iPage > $_iTotalPages )
			$_iPage = $_iTotalPages;

		if ( $_iPage < 1 )
			$_iPage = 1;

		//	Calculate starting offset
		$_iStart = $_iLimit * $_iPage - $_iLimit;

		//	Sanity check
		if ( $_iStart < 0 )
			$_iStart = 0;

		//	Adjust the criteria for the actual query...
		$_dbc = new CDbCriteria();

		if ( $_bHaveDBC )
		{
			unset( $_dbc );
			$_dbc = $oCriteria;
		}
		else if ( gettype( $oCriteria ) == 'string' )
		{
			$_dbc->select = $oCriteria;
		}

		$_dbc->order = "{$_iSortCol} {$_sSortOrder}";
		$_dbc->limit = $_iLimit;
		$_dbc->offset = $_iStart;
		$_oRows = $oModel->findAll( $_dbc );

		//	Set appropriate content type
		if ( stristr( $_SERVER[ 'HTTP_ACCEPT' ], "application/xhtml+xml" ) )
			header( "Content-type: application/xhtml+xml;charset=utf-8" );
		else
			header( "Content-type: text/xml;charset=utf-8" );

		//	Now create the Xml...
		$_sOut = CPSHelp::asXml(
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
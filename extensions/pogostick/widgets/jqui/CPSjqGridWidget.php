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
	//* Methods
	//********************************************************************************

	/**
	* Registers the needed CSS and JavaScript.
	*
	* @param string $sId
	*/
	public function registerClientScripts()
	{
		//	Daddy...
		$_oCS = parent::registerClientScripts();
		
		//	Register scripts necessary
		$_oCS->registerScriptFile( "{$this->extLibUrl}/jqGrid/js/i18n/grid.locale-en.js" );
		$_oCS->registerScriptFile( "{$this->extLibUrl}/jqGrid/js/jquery.jqGrid.min.js" );

		//	Register css files...
		$_oCS->registerCssFile( "{$this->extLibUrl}/jqGrid/css/ui.jqgrid.css", 'screen' );
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
<table id="{$this->id}" class="scroll"></table>
<div id="{$this->pagerId}" class="scroll" style="text-align:center;"></div>
CODE;
		return( $_sHtml );
	}

	/**
	* Override of makeOptions to insert correct pager jQuery code
	*
	*/
	protected function makeOptions()
	{
		$this->widgetName = 'jqGrid';
		
		//	Fix up the pager...
		$_sPagerId = $this->pagerId;
		return str_replace( "'pagerId':'{$_sPagerId}'", "pager: jQuery('#{$_sPagerId}')", parent::makeOptions() );
	}
	
	/**
	* Generates the javascript code for the widget
	*
	* @return string
	*/
	protected function generateJavascript()
	{
		$_arOptions = $this->makeOptions();
		
		$_bEdit = $this->pagerEdit | false;
		$_bAdd = $this->pagerAdd | false;
		$_bDel = $this->pagerDel | false;

		$this->script =<<<CODE
$('#{$this->id}').{$this->widgetName}({$_arOptions}).navGrid('#{$this->pagerId}',{edit:$_bEdit,add:$_bAdd,del:$_bDel});
CODE;

		return( $this->script );
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
		if ( ! headers_sent() )
		{
			if ( stristr( $_SERVER[ 'HTTP_ACCEPT' ], "application/xhtml+xml" ) )
				header( "Content-type: application/xhtml+xml;charset=utf-8" );
			else
				header( "Content-type: text/xml;charset=utf-8" );
		}

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

	//********************************************************************************
	//* Static methods
	//********************************************************************************
	
	/**
	* Constructs and returns a jqUI widget
	* 
	* The $baseUrl and $theme values are cached between calls so you do not need to 
	* specify them each time you call this method. 
	* 
	* The options passed in are dynamically added to the options array and will be accessible 
	* and modifiable as normal (.i.e. $this->theme, $this->baseUrl, etc.)
	* 
	* @param string $sName The type of jqUI widget to create
	* @param array $arOptions The options for the widget
	* @param boolean $bAutoRun Whether or not to call the run() method of the widget
	* @param string $sId The DOM id of the widget if other than $sName
	* @param string $sTheme The jqUI theme to use
	* @param string $sBaseUrl The base Url of the jqUI files, if different from the default
	* @return CPSjqUIWrapper
	*/
	public static function create( $sName, array $arOptions = array(), $bAutoRun = false, $sId = null, $sTheme = null, $sBaseUrl = null )
	{
		static $_sLastTheme = null;
		static $_sLastBaseUrl = null;

		//	Set up theme and base url for next call...		
		if ( $sTheme != $_sLastTheme ) $_sLastTheme = $sTheme;
		if ( $sBaseUrl != $_sLastBaseUrl ) $_sLastBaseUrl = $sBaseUrl;

		//	Instantiate...
		$_oWidget = new CPSjqGridWidget();

		//	Set default options...
		$_oWidget->id = ( null == $sId ) ? $sName : $sId;
		$_oWidget->name = ( null == $sId ) ? $sName : $sId;
		$_oWidget->theme = $_sLastTheme;
		$_oWidget->baseUrl = $_sLastBaseUrl;
		$_oWidget->widgetName = $sName;
		
		//	Set variable options...
		if ( is_array( $arOptions ) )
		{
			//	Check for scripts...
			if ( isset( $arOptions[ '_scripts' ] ) && is_array( $arOptions[ '_scripts' ] ) )
			{
				//	Add them...
				$_oWidget->addScripts( $arOptions[ '_scripts' ] );
					
				//	Kill _scripts option...
				unset( $arOptions[ '_scripts' ] );
				
			}

			//	Now process the rest of the options...			
			foreach( $arOptions as $_sKey => $_oValue )
			{
				//	Add it
				$_oWidget->addOption( $_sKey, null, false );
				
				//	Set it
				$_oWidget->setOption( $_sKey, $_oValue );
			}
		}
		
		//	Initialize the widget
		$_oWidget->init();

		//	Does user want us to run it?
		if ( $bAutoRun ) $_oWidget->run();
		
		//	And return...
		return $_oWidget;
	}
}
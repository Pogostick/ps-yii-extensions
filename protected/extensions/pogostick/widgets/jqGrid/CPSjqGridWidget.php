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
	* @param array $arOptions
	* @return CPSjqGridWidget
	*/
	public function init()
	{
		//	Set the valid options for this widget
		$this->validOptions = array(
			'altRows' => array( 'type' => 'boolean' ),
			'caption' => array( 'type' => 'string' ),
			'cellEdit' => array( 'type' => 'boolean' ),
			'cellsubmit' => array( 'type' => 'string', 'valid' => array( 'remote', 'clientarray' ) ),
			'cellurl' => array( 'type' => 'string' ),
			'colModel' => array( 'type' => 'array' ),
			'colNames' => array( 'type' => 'array' ),
			'datastr' => array( 'type' => 'string' ),
			'datatype' => array( 'type' => 'string', 'valid' => array( 'xml', 'xmlstring', 'json', 'jsonstring', 'clientside' ) ),
			'deselectAfterSort' => array( 'type' => 'boolean' ),
			'editurl' => array( 'type' => 'string' ),
			'expandcolumn' => array( 'type' => 'boolean' ),
			'forceFit' => array( 'type' => 'boolean' ),
			'gridstate' => array( 'type' => 'string', 'valid' => array( 'visible', 'hidden' ) ),
			'hiddengrid' => array( 'type' => 'boolean' ),
			'hidegrid' => array( 'type' => 'boolean' ),
			'height' => array( 'type' => array( 'string', 'integer' ) ),
			'imgpath' => array( 'type' => 'string' ),
			'jsonReader' => array( 'type' => 'array' ),
			'loadonce' => array( 'type' => 'boolean' ),
			'loadtext' => array( 'type' => 'string' ),
			'loadui' => array( 'type' => 'string', 'valid' => array( 'disable', 'enable', 'block' ) ),
			'multiselect' => array( 'type' => 'boolean' ),
			'mtype' => array( 'type' => 'string', 'valid' => array( 'GET', 'PUT' ) ),
			'multikey' => array( 'type' => 'string' ),
			'multiboxonly' => array( 'type' => 'boolean' ),
			'pagerId' => array( 'type' => 'string' ),
			'prmNames' => array( 'type' => 'array' ),
			'postData' => array( 'type' => 'array' ),
			'resizeclass' => array( 'type' => 'string' ),
			'rowNum' => array( 'type' => 'integer' ),
			'rowList' => array( 'type' => 'array' ),
			'scroll' => array( 'type' => 'boolean' ),
			'scrollrows' => array( 'type' => 'boolean' ),
			'sortclass' => array( 'type' => 'string' ),
			'shrinkToFit' => array( 'type' => 'boolean' ),
			'sortascimg' => array( 'type' => 'string' ),
			'sortdescimg' => array( 'type' => 'string' ),
			'sortname' => array( 'type' => 'string' ),
			'sortorder' => array( 'type' => 'string' ),
			'subGrid' => array( 'type' => 'boolean' ),
			'subGridModel' => array( 'type' => 'array' ),
			'subGridType' => array( 'type' => 'string' ),
			'subGridUrl' => array( 'type' => 'string' ),
			'theme' => array( 'type' => 'string', 'valid' => array( 'basic', 'coffee', 'green', 'sand', 'steel' ) ),
			'toolbar' => array( 'type' => 'array' ),
			'treeGrid' => array( 'type' => 'boolean' ),
			'tree_root_level' => array( 'type' => 'integer' ),
			'url' => array( 'type' => 'string' ),
			'userData' => array( 'type' => 'array' ),
			'viewrecords' => array( 'type' => 'boolean' ),
			'width' => array( 'type' => 'integer' ),
			'xmlReader' => array( 'type' => 'array' ),
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

		parent::init();
	}

	/***
	* Runs this widget
	*
	*/
	public function run()
	{
		//	Validate baseUrl
		if ( empty( $this->baseUrl ) )
			throw new CHttpException( 500, __CLASS__ . ': baseUrl is required.');

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
	protected function registerClientScripts()
	{
		//	Daddy...
		$_oCS = parent::registerClientScripts();

		//	If image path isn't specified, set to current theme path
		if ( ! array_key_exists( 'imgpath', $this->options ) || empty( $this->options[ 'imgpath' ] ) )
			$this->options[ 'imgpath' ] = "{$this->baseUrl}/themes/{$this->options[ 'theme' ]}/images";

		//	Register scripts necessary
		$_oCS->registerScriptFile( "{$this->baseUrl}/jquery.jqGrid.js" );
		$_oCS->registerScriptFile( "{$this->baseUrl}/js/jqModal.js" );
		$_oCS->registerScriptFile( "{$this->baseUrl}/js/jqDnR.js" );

		//	Get the javascript for this widget
		$_sScript = $this->generateJavascript();
		$_oCS->registerScript( 'Yii.' . __CLASS__ . '#' . $this->id, $_sScript, CClientScript::POS_READY );

		//	Register css files...
		$_oCS->registerCssFile( "{$this->baseUrl}/themes/{$this->getOption( 'theme' )}/grid.css", 'screen' );
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
		$_sPagerId = $this->getOption( 'pagerId' );

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
	protected function makeOptions( $arOptions = null )
	{
		//	Fix up the pager...
		$_sPagerId = $this->getOption( 'pagerId' );
		return( str_replace( "'pagerId':'{$_sPagerId}'", "'pager': jQuery('#{$_sPagerId}')", parent::makeOptions( $arOptions ) ) );
	}

}

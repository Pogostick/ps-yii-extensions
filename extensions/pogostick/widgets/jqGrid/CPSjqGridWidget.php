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
		parent::init();

		//	Set the valid options for this widget
		$this->setOptions(
			array(
				'altRows' => array( '_validPattern' => array( 'type' => 'boolean' ) ),
				'caption' => array( '_validPattern' => array( 'type' => 'string' ) ),
				'cellEdit' => array( '_validPattern' => array( 'type' => 'boolean' ) ),
				'cellsubmit' => array( '_validPattern' => array( 'type' => 'string', 'valid' => array( 'remote', 'clientarray' ) ) ),
				'cellurl' => array( '_validPattern' => array( 'type' => 'string' ) ),
				'colModel' => array( '_validPattern' => array( 'type' => 'array' ) ),
				'colNames' => array( '_validPattern' => array( 'type' => 'array' ) ),
				'datastr' => array( '_validPattern' => array( 'type' => 'string' ) ),
				'datatype' => array( '_validPattern' => array( 'type' => 'string', 'valid' => array( 'xml', 'xmlstring', 'json', 'jsonstring', 'clientside' ) ) ),
				'deselectAfterSort' => array( '_validPattern' => array( 'type' => 'boolean' ) ),
				'editurl' => array( '_validPattern' => array( 'type' => 'string' ) ),
				'expandcolumn' => array( '_validPattern' => array( 'type' => 'boolean' ) ),
				'forceFit' => array( '_validPattern' => array( 'type' => 'boolean' ) ),
				'gridstate' => array( '_validPattern' => array( 'type' => 'string', 'valid' => array( 'visible', 'hidden' ) ) ),
				'hiddengrid' => array( '_validPattern' => array( 'type' => 'boolean' ) ),
				'hidegrid' => array( '_validPattern' => array( 'type' => 'boolean' ) ),
				'height' => array( '_validPattern' => array( 'type' => array( 'string', 'integer' ) ) ),
				'imgpath' => array( '_validPattern' => array( 'type' => 'string' ) ),
				'jsonReader' => array( '_validPattern' => array( 'type' => 'array' ) ),
				'loadonce' => array( '_validPattern' => array( 'type' => 'boolean' ) ),
				'loadtext' => array( '_validPattern' => array( 'type' => 'string' ) ),
				'loadui' => array( '_validPattern' => array( 'type' => 'string', 'valid' => array( 'disable', 'enable', 'block' ) ) ),
				'multiselect' => array( '_validPattern' => array( 'type' => 'boolean' ) ),
				'mtype' => array( '_validPattern' => array( 'type' => 'string', 'valid' => array( 'GET', 'PUT' ) ) ),
				'multikey' => array( '_validPattern' => array( 'type' => 'string' ) ),
				'multiboxonly' => array( '_validPattern' => array( 'type' => 'boolean' ) ),
				'pagerId' => array( '_validPattern' => array( 'type' => 'string' ) ),
				'prmNames' => array( '_validPattern' => array( 'type' => 'array' ) ),
				'postData' => array( '_validPattern' => array( 'type' => 'array' ) ),
				'resizeclass' => array( '_validPattern' => array( 'type' => 'string' ) ),
				'rowNum' => array( '_validPattern' => array( 'type' => 'integer' ) ),
				'rowList' => array( '_validPattern' => array( 'type' => 'array' ) ),
				'scroll' => array( '_validPattern' => array( 'type' => 'boolean' ) ),
				'scrollrows' => array( '_validPattern' => array( 'type' => 'boolean' ) ),
				'sortclass' => array( '_validPattern' => array( 'type' => 'string' ) ),
				'shrinkToFit' => array( '_validPattern' => array( 'type' => 'boolean' ) ),
				'sortascimg' => array( '_validPattern' => array( 'type' => 'string' ) ),
				'sortdescimg' => array( '_validPattern' => array( 'type' => 'string' ) ),
				'sortname' => array( '_validPattern' => array( 'type' => 'string' ) ),
				'sortorder' => array( '_validPattern' => array( 'type' => 'string' ) ),
				'subGrid' => array( '_validPattern' => array( 'type' => 'boolean' ) ),
				'subGridModel' => array( '_validPattern' => array( 'type' => 'array' ) ),
				'subGridType' => array( '_validPattern' => array( 'type' => 'string' ) ),
				'subGridUrl' => array( '_validPattern' => array( 'type' => 'string' ) ),
				'theme' => array( '_validPattern' => array( 'type' => 'string', 'valid' => array( 'basic', 'coffee', 'green', 'sand', 'steel' ) ) ),
				'toolbar' => array( '_validPattern' => array( 'type' => 'array' ) ),
				'treeGrid' => array( '_validPattern' => array( 'type' => 'boolean' ) ),
				'tree_root_level' => array( '_validPattern' => array( 'type' => 'integer' ) ),
				'url' => array( '_validPattern' => array( 'type' => 'string' ) ),
				'userData' => array( '_validPattern' => array( 'type' => 'array' ) ),
				'viewrecords' => array( '_validPattern' => array( 'type' => 'boolean' ) ),
				'width' => array( '_validPattern' => array( 'type' => 'integer' ) ),
				'xmlReader' => array( '_validPattern' => array( 'type' => 'array' ) ),
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
	public function registerClientScripts()
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

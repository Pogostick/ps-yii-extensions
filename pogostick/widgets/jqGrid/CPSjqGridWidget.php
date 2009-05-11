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

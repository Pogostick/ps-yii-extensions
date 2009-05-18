<?php
/**
* CPSjqRatingWidget class file.
*
* @author Jerry Ablan <jablan@pogostick.com>
* @link http://ps-yii-extensions.googlecode.com
* @copyright Copyright &copy; 2009 Pogostick, LLC
* @license http://www.gnu.org/licenses/gpl.html
*/

/**
* The CPSjqRatingWidget allows the {@link http://www.fyneworks.com/jquery/star-rating/ JQ Rating} to be used in Yii.
*
* @author Jerry Ablan <jablan@pogostick.com>
* @version SVN: $Id$
* @package psYiiExtensions
* @subpackage Widgets
* @since 1.0.0
*/
class CPSjqRatingWidget extends CPSWidget
{
	//********************************************************************************
	//* Constructor
	//********************************************************************************

	/**
	* Constructs a CPSjqRatingWidget
	*
	*/
	public function __construct( $oOwner = null )
	{
		//	Phone home. Call first to get base behaviors loaded...
		parent::__construct( $oOwner );

		//	Add these options in the constructor so the Yii base can pre-fill them from the config files.
		$this->addOptions(
			array(
				'ajaxCallback' => array( CPSOptionManager::META_DEFAULTVALUE => '', CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'string' ) ),
				'cancel' => array( CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'string' ) ),
				'cancelValue' => array( CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'string' ) ),
				'half' => array( CPSOptionManager::META_DEFAULTVALUE => false, CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'bool', CPSOptionManager::META_ALLOWED => array( true, false ) ) ),
				'hoverTips' => array( CPSOptionManager::META_DEFAULTVALUE => array(), CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'array' ) ),
				'readOnly' => array( CPSOptionManager::META_DEFAULTVALUE => false, CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'bool', CPSOptionManager::META_ALLOWED => array( true, false ) ) ),
				'required' => array( CPSOptionManager::META_DEFAULTVALUE => false, CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'bool', CPSOptionManager::META_ALLOWED => array( true, false ) ) ),
				'selectValue' => array( CPSOptionManager::META_DEFAULTVALUE => 0, CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'double' ) ),
				'split' => array( CPSOptionManager::META_DEFAULTVALUE => 1, CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'int' ) ),
				'starClass' => array( CPSOptionManager::META_DEFAULTVALUE => 'start', CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'string' ) ),
				'starCount' => array( CPSOptionManager::META_DEFAULTVALUE => 5, CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'int' ) ),
				'starTitles' => array( CPSOptionManager::META_DEFAULTVALUE => array(), CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'array' ) ),
				'starValues' => array( CPSOptionManager::META_DEFAULTVALUE => array(), CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'array' ) ),
				'supressScripts' => array( CPSOptionManager::META_DEFAULTVALUE => false, CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'bool', CPSOptionManager::META_ALLOWED => array( true, false ) ) ),
			)
		);

		//	These are the valid callbacks for this class
		$this->validCallbacks = array(
			'callback',
			'focus',
			'blur',
		);

		//	Set our view name...
		$this->viewName = __CLASS__ . 'View';
	}

	//********************************************************************************
	//* Yii Overrides
	//********************************************************************************

	/***
	* Runs this widget
	*
	*/
	public function run()
	{
		//	Validate baseUrl
		if ( '' == $this->baseUrl )
			throw new CHttpException( 403, __CLASS__ . ': baseUrl is required.');

		//	Register the scripts/css
		$this->registerClientScripts();

		$this->html = $this->render( $this->viewName,
				array( "options" => $this->makeOptions() ),
				$this->returnString
		);

		return $this->html;
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

		//	Register scripts necessary
		$_oCS->registerScriptFile( "{$this->baseUrl}/jquery.MetaData.js" );
		$_oCS->registerScriptFile( "{$this->baseUrl}/jquery.rating.js" );

		//	Get the javascript for this widget
		$_sScript = $this->generateJavascript();

		if ( ! $this->supressScripts && ! $this->returnString )
				$_oCS->registerScript( 'PS.' . __CLASS__ . '#' . $this->id, $_sScript, CClientScript::POS_READY );

		//	Register css files...
		$_oCS->registerCssFile( "{$this->baseUrl}/jquery.rating.css", 'screen' );
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
		//	No callback set? then make the ajax callback
		if ( ! isset( $this->callbacks[ 'callback' ] ) && ! empty( $this->ajaxCallback ) )
		{
			$_arTemp = array(
				'type' => 'GET',
				'url' => Yii::app()->createUrl( $this->ajaxCallback ),
				'dataType' => 'html'
			);

			$_sCBBody = 'function(value,link){var arTemp = ' . CJavaScript::encode( $_arTemp ) . '; arTemp[\'data\'] = \'value=\'+value+\'&link=\'+link; $.ajax(arTemp);}';

			$this->callbacks[ 'callback' ] = $_sCBBody;
		}

		$_arOptions = $this->makeOptions();

		//	Now rating apply...
		$this->script .= '$(\'.' . $this->starClass . '\').rating(' . $_arOptions . '); ';

		return( $this->script );
	}

	/**
	* Generates the javascript code for the widget
	*
	* @return string
	*/
	protected function generateHtml()
	{
		$_iMaxCount = $this->starCount;

		//	Handle multiple star outputs...
		if ( $this->half )
			$this->split = 2;

		if ( $this->split > 1 )
			$_iMaxCount *= $this->split;

		for ( $_i = 0; $_i < $_iMaxCount; $_i++ )
		{
			$_sHtml .= '<input type="radio" class="' . $this->starClass;

			if ( $this->half )
				$_sHtml .= ' {half:true}';
			else if ( $this->split > 1 )
				$_sHtml .= ' {split:' . $this->split . '}';

			$_sHtml .= '" name="' . $this->name . '" ';

			if ( is_array( $this->starTitles ) && sizeof( $this->starTitles ) > 0 )
				$_sHtml .= 'title="' . $this->starTitles[ $_i ] . '" ';

			if ( is_array( $this->starValues ) && sizeof( $this->starValues ) > 0 )
				$_sHtml .= 'value="' . $this->starValues[ $_i ] . '" ';
			else
				$_sHtml .= 'value="' . ( $_i + 1 ) . '" ';

			if ( $this->selectValue != 0 && ( $this->selectValue * $this->split ) == ( $_i + 1 ) )
				$_sHtml .= 'checked="checked" ';

			$_sHtml .= ' />';
		}

		return( $_sHtml );
	}

 	/**
	* Convenience function to create a star rating widget
	*
	* Available options:
	*
	* suppressScripts	boolean		If true, scripts will be stored in the member variable 'scripts' and not output
	* returnString		boolean		If true, the output of this widget will be stored in a string and not echo'd. It is available through the member variable 'html'
	* baseUrl			string		The location of the jqRating installation
	* id				string		The HTML id of the widget. Defaults to null
	* name				string		The HTML name of the widget. Defaults to rating{x}, x is incremented with each use.
	* starClass			string		The HTML class name of the widget's output
	* split				integer		The number of times to split each star. Allows for 1/2 and 1/4 ratings, etc. Default 0
	* starCount			integer		The number of stars to display. Default 5
	* selectValue		integer		The value to mark as 'preselected' when displaying
	* readOnly			boolean		Makes the widget read-only, no input allowed.
	* required			boolean		Disables the 'cancel' button so user can only select one of the specified values
	* cancel			string		The tooltip text for the cancel button, defaults to 'Cancel Rating'
	* cancelValue		string		The value assigned to the widget when the cancel button is selected
	* ajaxCallback		function	The URL to call when a star is clicked. This URL is called via AJAX. Will be overriden by a value in 'callback' below...
	*
	* Available Callbacks
	*
	* callback			function	The Javascript function executed when a star is clicked
	* blur				function	The Javascript function executed when stars are blurred
	* focus				function	The Javascript function executed when stars are focused
	*
	* @param array $arOptions
	* @returns CPSjqRatingWidget
	*/
	public static function createRating( $arOptions )
	{
		static $_iIdCount = 0;

		//	Fix up the base url...
		$_sBaseUrl = CPSHelp::getOption( $arOptions, 'baseUrl', '' );

		if ( null == $_sBaseUrl )
			$_sBaseUrl = Yii::app()->baseUrl . '/extra/jqRating';

		//	Put it back in the array
		CPSHelp::setOption( $arOptions, 'baseUrl', $_sBaseUrl );

		$sId = CPSHelp::getOption( $arOptions, 'id' );
		$sName = CPSHelp::getOption( $arOptions, 'name' );

		//	Build the options...
		$_arOptions = array(
			'supressScripts' => CPSHelp::getOption( $arOptions, 'supressScripts', false ),
			'returnString' => CPSHelp::getOption( $arOptions, 'returnString', false ),
			'baseUrl' => CPSHelp::getOption( $arOptions, 'baseUrl', $_sBaseUrl ),
			'name' => ( $sName == null ? 'rating' . $_iIdCount : $sName . $_iIdCount ),
			'starClass' => CPSHelp::getOption( $arOptions, 'starClass', 'star' ),
			'split' => CPSHelp::getOption( $arOptions, 'split', 1 ),
			'starCount' => CPSHelp::getOption( $arOptions, 'starCount', 5 ),
			'selectValue' => ( double )CPSHelp::getOption( $arOptions, 'selectValue', 0 ),
			'ajaxCallback' => CPSHelp::getOption( $arOptions, 'ajaxCallback' ),
			'starTitles' => CPSHelp::getOption( $arOptions, 'starTitles' ),
			'starValues' => CPSHelp::getOption( $arOptions, 'starValues' ),
			'hoverTips' => CPSHelp::getOption( $arOptions, 'hoverTips' ),
			'callbacks' =>
				array(
					'callback' => CPSHelp::getOption( $arOptions, 'callback', null ),
					'focus' => CPSHelp::getOption( $arOptions, 'focus', null ),
					'blur' => CPSHelp::getOption( $arOptions, 'blur', null ),
			),
		);

		//	Not logged in? No ratings for you!
		if ( Yii::app()->user->isGuest )
			unset( $arOptions[ 'ajaxCallback' ] );

		$_oWidget = Yii::app()->controller->widget(
			'pogostick.widgets.jqRating.CPSjqRatingWidget',
			$_arOptions
		);

		//	Return my created widget
		return( $_oWidget );
 	}

	/**
	* Handles and formats the query and Xml output for a jqGrid
	*
	* @param CModel $oModel The model to use for the query
	* @param CDbCriteria|string $oCriteria Can be a full CDbCriteria object or a comma separated list of columns to "SELECT"
	* @param array $arQSElems The query string elements in array format. Must be in PAGE, ROWS, SORTCOLUMN, SORTORDER. Defaults to 'page', 'rows', 'sidx', 'sord'
	* @return string The Xml data for the grid
	*/
	public static function asjqGridXmlData( $oModel, $oCriteria = null, $arQSElems = null, $bReturnString = false )
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
		$_iRowCount = $oModel->count( ( $_bHaveDBC ) ? $oCriteria : null );

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
		$_sOut = self::asXml(
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
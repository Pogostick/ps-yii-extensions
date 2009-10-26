<?php
/**
* CPSjqToolsWrapper class file.
*
* @author Jerry Ablan <jablan@pogostick.com>
* @link http://ps-yii-extensions.googlecode.com
* @copyright Copyright &copy; 2009 Pogostick, LLC
* @license http://www.gnu.org/licenses/gpl.html
*/

/**
* The CPSjqToolsWrapper allows the {@link http://flowplayer.org/tools/index.html jQuery Tools} to be used in Yii.
*
* @author Jerry Ablan <jablan@pogostick.com>
* @version SVN: $Id$
* @filesource
* @package psYiiExtensions
* @subpackage Widgets
* @since 1.0.4
*/
class CPSjqToolsWrapper extends CPSjQueryWidget
{
	//********************************************************************************
	//* Constants
	//********************************************************************************
	
	/**
	* The name of this widget
	*/
	const PS_WIDGET_NAME = 'jqTools';
	/**
	* The path where the assets for this widget are stored (underneath the psYiiExtensions/external base
	* Currently, a CDN is in use and no local files are required...
	*/
	const PS_EXTERNAL_PATH = '/jquery-tools';

	//********************************************************************************
	//* Member Variables
	//********************************************************************************
	
	/**
	* A list of the support files for each tool
	* 
	* @var array
	*/
	protected $m_arSupportFiles = array();
	
	//********************************************************************************
	//* Constructor
	//********************************************************************************

	public function __construct( $sOwner = null )
	{
		//	Phone home...
		parent::__construct( $oOwner );
		
		//	Add the default options for jqUI stuff
		$this->addOptions( 
			array(
				'paneClass_' => array( CPSOptionManager::META_REQUIRED => false, CPSOptionManager::META_DEFAULTVALUE => null, CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'string' ) ),
			)
		);
		
	}
	
	//********************************************************************************
	//* Methods
	//********************************************************************************

	public function init()
	{
		parent::init();
	
		//	Set up support files array...
		$this->m_arSupportFiles = array(
			'tooltip' => array(
				'css' => array( 'tooltip.css' )
			),
			'tabs' => array(
				'css' => array( 'tabs.css' )
			),
			'scrollable' => array(
				'css' => array( 'scrollable.css' )
			),
			'overlay' => array(
				'css' => array( 'overlay.css' )
			),
		);

		//	Make sure we have the right stuff		
		if ( 'tabs' == $this->widgetName )
		{
			if ( $this->isEmpty( $this->paneClass ) )
				throw new CException( Yii::t( 'yii', 'When creating "tabs" you must set the "paneClass" option to the class of the div containing the tab panes.' ) );
		}
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
		$_oCS->registerScriptFile( $this->baseUrl . '/jquery.tools.min.js' );
		
//	Uncomment to use CDN		
//		$_oCS->registerScriptFile( "http://cdn.jquerytools.org/1.1.2/tiny/jquery.tools.min.js" );

		//	Add for flashembed if we need it...		
		if ( 'flashembed' == $this->widgetName ) $_oCS->registerScriptFile( "http://static.flowplayer.org/js/tools/tools.flashembed-1.0.4.min.js" );
		
		//	Register any CSS files for this tool...
		if ( isset( $this->m_arSupportFiles[ $this->widgetName ] ) )
		{
			if ( isset( $this->m_arSupportFiles[ $this->widgetName ][ 'css' ] ) )
			{
//				foreach ( $this->m_arSupportFiles[ $this->widgetName ][ 'css' ] as $_sFile )
//					$_oCS->registerCssFile( "{$this->baseUrl}/css/{$_sFile}", 'screen' );
			}
		}

		//	Get the javascript for this widget
		$_oCS->registerScript( 'ps_' . md5( self::PS_WIDGET_NAME . $this->widgetName . '#' . $this->id . '.' . $this->target . '.' . time() ), $this->generateJavascript(), CClientScript::POS_READY );

		//	Don't forget subclasses
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
	protected function generateJavascript( $sClassName = null, $arOptions = null, $sInsertBeforeOptions = null )
	{
		//	Fix up tabs...
		if ( null == $sInsertBeforeOptions && 'tabs' == $this->widgetName )
			$sInsertBeforeOptions = '"div.' . $this->paneClass . ' > div"';
			
		//	Expose widget is attached to target as a click event...
		switch ( $this->widgetName )
		{
			case 'expose':
				$_arOptions = ( null != $arOptions ) ? $arOptions : $this->makeOptions();
				$_sId = ( null != $sClassName ) ? '.' . $sClassName : ( ! $this->isEmpty( $this->target ) ) ? $this->target : '#' . $this->id;
				$_sScript = "$(\"{$_sId}\").click(function(){\$(this).expose({$_arOptions}).load();});";
				break;
			
			case 'flashembed':
				$_sSrc = $this->getOption( 'src', null );
				$_arOptions = $this->makeOptions();
				$_sScript = 'flashembed("' . $this->target . '", "' . $_sSrc . '", ' . $_arOptions . ');';
				break;
				
			default:
				$_sScript = parent::generateJavascript( $sClassName, $arOptions, $sInsertBeforeOptions );
				break;
		}
		
		return $this->script = $_sScript;
	}

	/**
	* Constructs and returns a jQuery Tools widget
	* 
	* The options passed in are dynamically added to the options array and will be accessible 
	* and modifiable as normal (.i.e. $this->theme, $this->baseUrl, etc.)
	* 
	* @param string $sName The type of jqTools widget to create
	* @param array $arOptions The options for the widget
	* @param string $sClass The class of the calling object if different
	* @return CPSjqToolsWrapper
	*/
	public static function create( $sName, array $arOptions = array(), $sClass = __CLASS__ )
	{
		return parent::create( $sName, $arOptions, $sClass );
	}

}
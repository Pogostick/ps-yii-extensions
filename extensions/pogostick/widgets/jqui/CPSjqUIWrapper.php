<?php
/**
* CPSjqUIWraqpper class file.
*
* @author Jerry Ablan <jablan@pogostick.com>
* @link http://ps-yii-extensions.googlecode.com
* @copyright Copyright &copy; 2009 Pogostick, LLC
* @license http://www.gnu.org/licenses/gpl.html
*/

/**
* The CPSjqUIWraqpper is a wrapper for any jQuery UI widget
* 
* This class must be instantiated by using the static method (@link create). 
* If you do not use that method, the options will not initiate properly.
* 
* Here we create an accordion:
* <code>
* //	Create an accordian...
* CPSjqUIWrapper::create( 'accordion', array( 'header' => 'h3' ) );
* 
* ...
* 
* <html>
* 
* ...
* 
* <h2 class="demoHeaders">Accordion</h2>
* <div id="accordion">
* 	<div>
* 		<h3><a href="#">First</a></h3>
* 		<div>Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet.</div>
* 	</div>
* 	<div>
* 		<h3><a href="#">Second</a></h3>
* 		<div>Phasellus mattis tincidunt nibh.</div>
* 	</div>
* 	<div>
* 		<h3><a href="#">Third</a></h3>
* 		<div>Nam dui erat, auctor a, dignissim quis.</div>
* 	</div>
* </div>
* 
* ...
* 
* </html>
* </code>
* 
* In the above example, the 3rd parameter instructs the library to execute the widget's 
* (@link CWidget::run()) method after it has been initialized. This causes the output to 
* render. If you wish to run the widget at a later time, use the following code:
* <code>
* //	Create an accordian...
* 
* $_widget = CPSjqUIWrapper::create( 'accordion', array( 'header' => 'h3' ), false, null, 'cupertino' );
* 
* ...
* 
* $_widget->run();
* </code>
* 
* 
*
* @author Jerry Ablan <jablan@pogostick.com>
* @version $Id$
* @filesource
* @package psYiiExtensions
* @subpackage Widgets
* @since 1.0.0
* 
* @property $theme The theme to use. Defaults to 'base'
* @property $imagePath Image path will be automatically set. You can override the default here.
* @property $currentTheme The currently used theme
* @property $multiTheme If multiple themes are allowed
*/
class CPSjqUIWrapper extends CPSjQueryWidget
{
	//********************************************************************************
	//* Constants
	//********************************************************************************
	
	/**
	* The path where the assets for this widget are stored (underneath the psYiiExtensions/external base
	*/
	const PS_WIDGET_NAME = 'jqui';
	/**
	* The path where the assets for this widget are stored (underneath the psYiiExtensions/external base
	*/
	const PS_EXTERNAL_PATH = '/jqui';
	
	//********************************************************************************
	//* Member variables
	//********************************************************************************

	/**
	* The current theme. If set, future theme changes will be ignored
	* 
	* @staticvar string
	* @access protected
	*/
	protected static $m_sCurrentTheme;
	
	/**
	* If true, no theme-blocking will be done.
	* 
	* @staticvar boolean
	* @access protected
	*/
	protected static $m_bMultiTheme = false;
	
	/**
	* The current valid themes for jqUI widgets
	* 
	* @var array
	*/
	protected $m_arValidThemes = array( 'base', 'black-tie', 'blitzer', 'cupertino', 'dot-luv', 'excite-bike', 'hot-sneaks', 'humanity', 'mint-choc', 'redmond', 'smoothness', 'south-street', 'start', 'swanky-purse', 'trontastic', 'ui-darkness', 'ui-lightness', 'vader' ); 
	
	//********************************************************************************
	//* Property Accessors
	//********************************************************************************
	
	public function getCurrentTheme() { return self::$m_sCurrentTheme; }
	public function setCurrentTheme( $sTheme ) { self::$m_sCurrentTheme = $sTheme; }
	public function getMultiTheme() { return self::$m_bMultiTheme; }
	public function setMultiTheme( $bValue ) { self::$m_bMultiTheme = $bValue; }

	//********************************************************************************
	//* Constructor
	//********************************************************************************

	/**
	* Constructs a CPSjqUIWraqpper
	*
	* @param mixed $oOwner
	* @return CPSjqUIWraqpper
	*/
	function __construct( $oOwner = null )
	{
		//	Phone home...
		parent::__construct( $oOwner );
		
		//	Add the default options for jqUI stuff
		$this->addOptions( 
			array(
				'theme_' => array( CPSOptionManager::META_REQUIRED => true, CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'string', CPSOptionManager::META_ALLOWED => $this->m_arValidThemes ) ),
				'imagePath_' => array( CPSOptionManager::META_REQUIRED => false, CPSOptionManager::META_DEFAULTVALUE => '', CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'string' ) ),
			)
		);
	}
	
	/**
	* Initialize the widget
	* 
	*/
	public function init()
	{
		//	Call daddy
		parent::init();
		
		//	Validate defaults...
		$_sTheme = $this->theme;
		
		if ( empty( $_sTheme ) ) $this->theme = ( ! empty( self::$m_sCurrentTheme ) ) ? self::$m_sCurrentTheme : $_sTheme = 'base';
		if ( $this->isEmpty( $this->baseUrl ) ) $this->baseUrl = $this->extLibUrl . self::PS_EXTERNAL_PATH;
		if ( $this->isEmpty( $this->imagePath ) ) $this->imagePath = "{$this->baseUrl}/css/{$this->theme}/images";
	}

	/***
	* Runs this widget
	*
	*/
	public function run()
	{
		//	Register the scripts/css
		$this->registerClientScripts();

		//	Generate the HTML if available
		if ( method_exists( $this, 'generateHtml' ) ) echo $this->generateHtml();
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
		self::loadScripts( $this );

		//	Get the javascript for this widget
		$_oCS->registerScript( 'ps_' . md5( self::PS_WIDGET_NAME . '.' . $this->widgetName . '#' . $this->id . '#' . $this->target . '.' . time() ), $this->generateJavascript(), CClientScript::POS_READY );

		//	Don't forget subclasses
		return $_oCS;
	}

	//********************************************************************************
	//* Static methods
	//********************************************************************************
	
	/**
	* Constructs and returns a jQuery widget
	* 
	* The options passed in are dynamically added to the options array and will be accessible 
	* and modifiable as normal (.i.e. $this->theme, $this->baseUrl, etc.)
	* 
	* @param string $sName The type of jq widget to create
	* @param array $arOptions The options for the widget
	* @param string $sId The DOM id of the widget if other than $sName
	* @param string $sClass The class of the calling object if different
	* @return CPSjqUIWrapper
	*/
	public static function create( $sName, array $arOptions = array(), $sClass = __CLASS__ )
	{
		return parent::create( $sName, $arOptions, $sClass );
	}
	
	/**
	* Registers the needed CSS and JavaScript.
	* 
	* One may use this to load the scripts necessary for styling buttons and 
	* whatnot when jqUI widgets are not in use on a page.
	*
	* @param CPSjqUIWrapper $oWidget The widget making this call, if any
	* @param string $sTheme The theme to use. If it's specified, will override theme set at the time of creation
	* @param CClientScript $oCS The clientScript object of the app
	*/
	public static function loadScripts( $oWidget = null, $sTheme = null )
	{
		//	Daddy...
		$_oCS = Yii::app()->getClientScript();
		
		//	Instantiate if needed...
		$_oWidget = ( null == $oWidget ) ? new CPSjqUIWrapper() : $oWidget;

		//	Save then Set baseUrl...
		$_sOldPath = $_oWidget->baseUrl;
		$_oWidget->baseUrl = $_oWidget->extLibUrl . self::PS_EXTERNAL_PATH;
		
		//	Set $sTheme if missing...
		if ( null != $oWidget && null == $sTheme )
			$sTheme = $oWidget->theme;

		//	Check theme overrides...
		if ( ! self::$m_bMultiTheme && ! empty( self::$m_sCurrentTheme ) )
			 $_oWidget->theme = self::$m_sCurrentTheme;
		else
			$_oWidget->theme = self::$m_sCurrentTheme = $sTheme;
		
		//	Register scripts necessary
		$_oCS->registerCoreScript( 'jquery' );
		$_oCS->registerScriptFile( "{$_oWidget->baseUrl}/js/jquery-ui-1.7.1.min.js" );
		$_oCS->registerScriptFile( "{$_oWidget->baseUrl}/js/jquery.pogostick.hover.js", CClientScript::POS_END );

		//	Register css files...
		$_oCS->registerCssFile( "{$_oWidget->baseUrl}/css/{$_oWidget->theme}/ui.all.css", 'screen' );
		$_oCS->registerCssFile( "{$_oWidget->baseUrl}/css/ui.pogostick.css", 'screen' );
		
		//	Restore path
		$_oWidget->baseUrl = $_sOldPath;
	}

}
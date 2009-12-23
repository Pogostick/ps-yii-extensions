<?php
/*
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * The CPSjqUIWraqpper is a wrapper for any jQuery UI widget
 * 
 * This class must be instantiated by using the static method (@link create). 
 * If you do not use that method, the options will not initiate properly.
 *
 * Here we create an accordion:
 * 
 * <code>
 * //	Create an accordian...
 * CPSjqUIWrapper::create( 'accordion', array( 'header' => 'h3', 'target' => '#accordion' ) );
 * ...
 * <html>
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
 * @package 	psYiiExtensions.widgets
 * @subpackage 	jqui
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id$
 * @since 		v1.0.0
 *  
 * @filesource
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
	protected $m_arValidThemes = array( 
		'base', 
		'black-tie', 
		'blitzer', 
		'cupertino', 
		'dark-hive',
		'dot-luv', 
		'eggplant',
		'excite-bike', 
		'flick',
		'hot-sneaks', 
		'humanity', 
		'le-frog', 
		'mint-choc', 
		'overcast',
		'pepper-grinder',
		'redmond', 
		'smoothness', 
		'south-street', 
		'start', 
		'sunny',
		'swanky-purse',
		'trontastic',
		'ui-darkness',
		'ui-lightness',
		'vader'
	); 
	
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
				'theme_' => 'string:::true:' . implode( '|', $this->m_arValidThemes ),
				'imagePath_' => 'string',
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
		//	Push stuff to load...
		if ( $this->script ) $this->pushScriptFile( $this->extLibUrl . $this->script );

		//	Daddy...
		$_oCS = parent::registerClientScripts();
		
		//	Register scripts necessary
		self::loadScripts( $this );

		//	Fix up datepicker internationalization
		if ( $this->widgetName == 'datepicker' )
		{
			//	Is there a regional attribute? Pull it out and remove...
			$_sRegion = ( ! $this->isEmpty( $this->regional ) ) ? $this->regional : '';
			$this->unsetOption( 'regional' );

			//	Not en? Let's load i18n file...
			if ( ! empty( $_sRegion ) ) $_oCS->registerScriptFile( "http://jquery-ui.googlecode.com/svn/tags/latest/ui/minified/i18n/jquery-ui-i18n.min.js" );
			
			//	Set defaults for datepicker if this is one...
			$_sRegion = "$.datepicker.setDefaults($.extend({showMonthAfterYear: false},$.datepicker.regional['{$_sRegion}']));";
			$_oCS->registerScript( 'ps.reset.datepicker.' . md5( self::PS_WIDGET_NAME . '.' . $this->widgetName . '#' . $this->id . '#' . $this->target . '.' . time() ), $_sRegion, CClientScript::POS_READY );
		}
		
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
		
		//	Check theme overrides...
		$_sTheme = CPSHelp::nvl( $sTheme, $_oWidget->theme, self::$m_sCurrentTheme, Yii::app()->params['theme'] );
		if ( ! self::$m_bMultiTheme && empty( self::$m_sCurrentTheme ) ) self::$m_sCurrentTheme = $_sTheme;

		$_oWidget->theme = $_sTheme;
		
		//	Register scripts necessary
		$_oCS->registerScriptFile( "http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.2/jquery-ui.min.js" );
		$_oCS->registerScriptFile( $_oWidget->baseUrl . '/js/jquery.pogostick.hover.js', CClientScript::POS_END );

		//	Register css files if we have a theme...
		if ( $_oWidget->theme )
		{
//	Uncomment to use CDN			
//			$_oCS->registerCssFile( "http://jqueryui.com/latest/themes/{$_oWidget->theme}/ui.all.css" );

			$_oCS->registerCssFile( "{$_oWidget->baseUrl}/css/{$_oWidget->theme}/ui.all.css" );
			$_oCS->registerCssFile( "{$_oWidget->baseUrl}/css/ui.pogostick.css" );
		}
		
		//	Restore path
		$_oWidget->baseUrl = $_sOldPath;
	}

}
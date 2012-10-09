<?php
/*
 * This file is part of the psYiiExtensions package.
 *
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * The CPSjqUIWrapper is a wrapper for any jQuery UI widget
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
 * @version 	SVN: $Id: CPSjqUIWrapper.php 404 2010-10-16 00:50:38Z jerryablan@gmail.com $
 * @since 		v1.0.0
 *
 * @filesource
 *
 * @property $theme The theme to use. Defaults to 'cupertino'
 * @property $imagePath Image path will be automatically set. You can override the default here.
 * @property $currentTheme The currently used theme
 * @property $multiTheme If multiple themes are allowed
 * @property-read string $stateName The prefix for state storage
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
	protected static $m_arValidThemes = array(
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
	public static function getValidThemes() { return self::$m_arValidThemes; }

	/**
	* A name for storing in the state.
	* @var string
	*/
	protected static $m_sStateName = '_ps_jqui_theme';
	public static function getStateName() { return self::$m_sStateName; }

	//********************************************************************************
	//* Property Accessors
	//********************************************************************************

	public static function getCurrentTheme() { return self::$m_sCurrentTheme ? self::$m_sCurrentTheme : self::$m_sCurrentTheme = Yii::app()->user->getState( self::$m_sStateName ); }
	public static function setCurrentTheme( $sTheme, $bSessionToo = true ) { if ( $bSessionToo ) Yii::app()->user->setState( self::$m_sStateName, $sTheme ); self::$m_sCurrentTheme = $sTheme; }
	public static function getMultiTheme() { return self::$m_bMultiTheme; }
	public static function setMultiTheme( $bValue ) { self::$m_bMultiTheme = $bValue; }

	//********************************************************************************
	//* Constructor
	//********************************************************************************

	/**
	* Initialize
	*/
	function preinit()
	{
		//	Phone home...
		parent::preinit();

		//	Add the default options for jqUI stuff
		$this->addOptions(
			array(
				'theme_' => 'string:::true:' . implode( '|', self::$m_arValidThemes ),
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

		if ( empty( $_sTheme ) ) $this->theme = ( ! empty( self::$m_sCurrentTheme ) ) ? self::$m_sCurrentTheme : $_sTheme = PS::nvl( Yii::app()->params['theme'], 'cupertino' );
		if ( empty( $this->baseUrl ) ) $this->baseUrl = $this->extLibUrl . self::PS_EXTERNAL_PATH;
		if ( empty( $this->imagePath ) ) $this->imagePath = "{$this->baseUrl}/css/{$this->theme}/images";
	}

	/***
	* Runs this widget
	*
	*/
	public function run()
	{
		//	Register the scripts/css
		$_bLocate = $this->locateScript;
		$this->registerClientScripts( $_bLocate );

		//	Generate the HTML if available
		echo $this->generateHtml();
	}

	/**
	* Registers the needed CSS and JavaScript.
	* @param boolean If true, system will try to find jquery plugins based on the pattern jquery.<plugin-name[.min].js
	* @return CClientScript The current app's ClientScript object
	*/
	public function registerClientScripts( $bLocateScript = false )
	{
		//	Don't auto register
		$_bAutoReg = $this->autoRegister;
		$this->autoRegister = false;

		//	Call dad
		parent::registerClientScripts( $bLocateScript );
		$this->autoRegister = $_bAutoReg;

		//	Register scripts necessary
		self::loadScripts( $this, $this->theme );

		//	Fix up datepicker internationalization
		if ( $this->widgetName == 'datepicker' )
		{
			//	Is there a regional attribute? Pull it out and remove...
			$_sRegion = ( ! empty( $this->regional ) ) ? $this->regional : '';
			$this->unsetOption( 'regional' );

			//	Not en? Let's load i18n file...
			if ( ! empty( $_sRegion ) ) PS::_rsf( ( PS::o( $_SERVER, 'HTTPS' ) == 'on' ? 'https' : 'http' ) . "://jquery-ui.googlecode.com/svn/tags/latest/ui/minified/i18n/jquery-ui-i18n.min.js", CClientScript::POS_END );

			//	Set defaults for datepicker if this is one...
			$_sRegion = "$.datepicker.setDefaults($.extend({showMonthAfterYear: false},$.datepicker.regional['{$_sRegion}']));";
			PS::_rs( 'ps.reset.datepicker.' . md5( self::PS_WIDGET_NAME . '.' . $this->widgetName . '#' . $this->id . '#' . $this->target . '.' . time() ), $_sRegion );
		}

		//	Get the javascript for this widget
		if ( $this->autoRegister )
			$this->registerWidgetScript();

		//	Don't forget subclasses
		return PS::_cs();
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
	* @param string $sClass The class of the calling object if different
	* @return CPSjqUIWrapper
	*/
	public static function create( $sName = null, array $arOptions = array() )
	{
		$arOptions['class'] = PS::o( $arOptions, 'class', __CLASS__ );
		if ( $sName == $arOptions['class'] ) $arOptions['naked'] = true;
		return parent::create( $sName, $arOptions );
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
		static $_bLoaded = false;

		if ( ! $_bLoaded )
		{
			//	Instantiate if needed...
			$_oWidget = ( null == $oWidget ) ? new CPSjqUIWrapper() : $oWidget;

			//	Save then Set baseUrl...
			$_sOldPath = $_oWidget->baseUrl;
			$_oWidget->baseUrl = $_oWidget->extLibUrl . self::PS_EXTERNAL_PATH;

			//	Check theme overrides...
			$_sTheme = PS::nvl( $sTheme, $_oWidget->theme, Yii::app()->params['theme'], self::getCurrentTheme() );
			if ( ! self::$m_bMultiTheme && ! self::getCurrentTheme() ) self::setCurrentTheme( $_sTheme );

			$_oWidget->theme = $_sTheme;

			//	Register css files if we have a theme...
			if ( $_oWidget->theme )
			{
				if ( false !== PS::o( PS::_cs()->scriptMap, 'jqueryui.css' ) )
				{
					PS::_rcf( ( PS::o( $_SERVER, 'HTTPS' ) == 'on' ? 'https' : 'http' ) . "://ajax.googleapis.com/ajax/libs/jqueryui/1/themes/{$_oWidget->theme}/jquery-ui.css" );
				}

				PS::_rcf( PS::makePath( $_oWidget->baseUrl, 'css', 'ui.pogostick.css' ) );
			}

			//	Register scripts necessary
			if ( false !== PS::o( PS::_cs()->scriptMap, 'jqueryui.js' ) )
				PS::_rsf( ( PS::o( $_SERVER, 'HTTPS' ) == 'on' ? 'https' : 'http' ) . "://ajax.googleapis.com/ajax/libs/jqueryui/1/jquery-ui.min.js" );

			PS::_rsf( PS::makePath( $_oWidget->baseUrl, 'js', 'jquery.pogostick.hover.js' ), CClientScript::POS_END );

			//	Restore path
			$_oWidget->baseUrl = $_sOldPath;

			//	And mark completed...
			$_bLoaded = true;
		}
	}

}

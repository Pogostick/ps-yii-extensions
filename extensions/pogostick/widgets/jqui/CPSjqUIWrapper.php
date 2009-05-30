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
* @author Jerry Ablan <jablan@pogostick.com>
* @version $Id$
* @filesource
* @package psYiiExtensions
* @subpackage Widgets
* @since 1.0.0
*/
class CPSjqUIWrapper extends CPSWidget
{
	//********************************************************************************
	//* Member variables
	//********************************************************************************

	/**
	* The current valid themes for jqUI widgets
	* 
	* @var array
	*/
	protected $m_arValidThemes = array( 'base', 'black-tie', 'blitzer', 'cupertino', 'dot-luv', 'excite-bike', 'hot-sneaks', 'humanity', 'mint-choc', 'redmond', 'smoothness', 'south-street', 'start', 'swanky-purse', 'trontastic', 'ui-darkness', 'ui-lightness', 'vader' ); 
	
	/**
	* Any additional widget scripts
	* 
	* @var array
	*/
	protected $m_arScripts = array();

	//********************************************************************************
	//* Methods
	//********************************************************************************

	/**
	* Constructs a CPSjqUIWraqpper
	*
	* @param mixed $oOwner
	* @return CPSjqUIWraqpper
	*/
	public function __construct( $oOwner = null )
	{
		parent::__construct( $oOwner );
		
		//	Add the default options for jqUI stuff
		$this->addOptions( 
			array(
				//	The name of the widget you'd like to create (i.e. draggable, accordion, etc.)
				'widgetName_' => array( CPSOptionManager::META_REQUIRED => true, CPSOptionManager::META_DEFAULTVALUE => '', CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'string' ) ),
				//	Image path will be automatically set. You can override the default here.
				'imagePath_' => array( CPSOptionManager::META_REQUIRED => false, CPSOptionManager::META_DEFAULTVALUE => '', CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'string' ) ),
				//	The theme to use. Defaults to 'base'
				'theme_' => array( CPSOptionManager::META_REQUIRED => true, CPSOptionManager::META_RULES => array( CPSOptionManager::META_TYPE => 'string', CPSOptionManager::META_ALLOWED => $this->m_arValidThemes ) ),
			)
		);
	}
	
	/**
	* Adds a user script to the output array
	* 
	* @param array $arScript
	*/
	public function addScripts( $arScripts )
	{
		foreach ( $arScripts as $_sScript )
			$this->m_arScripts[] = $_sScript;
	}

	/***
	* Runs this widget
	*
	*/
	public function run()
	{
		//	Validate baseUrl
		if ( $this->isEmpty( $this->baseUrl ) )
			$this->baseUrl = $this->getExtLibUrl() . '/jqui';

		//	Validate theme
		if ( $this->isEmpty( $this->theme ) )
			$this->theme = 'base';

		//	Register the scripts/css
		$this->registerClientScripts();
	}

	/**
	* Registers the needed CSS and JavaScript.
	*
	* @param string $sId
	*/
	public function registerClientScripts()
	{
		static $_iScriptCount = 0;
		
		//	Daddy...
		$_oCS = parent::registerClientScripts();
		
		//	Require jQuery
		Yii::app()->clientScript->registerCoreScript( 'jquery' );

		//	If image path isn't specified, set to current theme path
		if ( $this->isEmpty( $this->imagePath ) )
			$this->imagePath = "{$this->baseUrl}/css/{$this->theme}/images";

		//	Register scripts necessary
		$_oCS->registerScriptFile( "{$this->baseUrl}/js/jquery-ui-1.7.1.min.js" );
	
		//	Get the javascript for this widget
		$_oCS->registerScript( 'psjqui.' . $this->widgetName . '#' . $this->id, $this->generateJavascript(), CClientScript::POS_READY );

		//	Additional scripts		
		foreach ( $this->m_arScripts as $_sScript )
			$_oCS->registerScript( 'psjqui.script' . $_iScriptCount++ . '#' . $this->id, $_sScript, CClientScript::POS_READY );

		//	Register css files...
		$_oCS->registerCssFile( "{$this->baseUrl}/css/{$this->theme}/ui.all.css", 'screen' );
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
$('#{$this->id}').{$this->widgetName}({$_arOptions});
CODE;

		return( $this->script );
	}

	//********************************************************************************
	//* Static methods
	//********************************************************************************
	
	public static function create( $sName, array $arOptions = array(), $bAutoRun = false, $sId = null, $sTheme = 'base', $sBaseUrl = null )
	{
		static $_sLastTheme = null;
		static $_sLastBaseUrl = null;

		//	Set up theme and base url for next call...		
		if ( $sTheme != $_sLastTheme ) $_sLastTheme = $sTheme;
		if ( $sBaseUrl != $_sLastBaseUrl ) $_sLastBaseUrl = $sBaseUrl;

		//	Instantiate...
		$_oWidget = new CPSjqUIWrapper();

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
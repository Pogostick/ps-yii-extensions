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
class CPSjqToolsWrapper extends CPSjqUIWrapper
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
	//* Methods
	//********************************************************************************

	public function init()
	{
		parent::init();
	
		//	Set my name...	
		$this->widgetName = self::PS_WIDGET_NAME;
		
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
		$_oCS->registerScriptFile( "http://cdn.jquerytools.org/1.0.2/jquery.tools.min.js" );

		//	Add for flashembed if we need it...		
		if ( 'flashembed' == $this->widgetName ) $_oCS->registerScriptFile( "http://static.flowplayer.org/js/flashembed-1.0.3.min.js" );
		
		//	Register any CSS files for this tool...
		if ( isset( $this->m_arSupportFiles[ $this->widgetName ] ) )
		{
			if ( isset( $this->m_arSupportFiles[ $this->widgetName ][ 'css' ] ) )
			{
				foreach ( $this->m_arSupportFiles[ $this->widgetName ][ 'css' ] as $_sFile )
					$_oCS->registerCssFile( "{$this->baseUrl}/css/{$_sFile}", 'screen' );
			}
		}

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
	protected function generateJavascript()
	{
		$_arOptions = $this->makeOptions();

		$this->script =<<<CODE
$('#{$this->id}').{$this->widgetName}({$_arOptions});
CODE;

		return $this->script;
	}

	/**
	* Constructs and returns a jQuery widget
	* 
	* The options passed in are dynamically added to the options array and will be accessible 
	* and modifiable as normal (.i.e. $this->theme, $this->baseUrl, etc.)
	* 
	* @param array $arOptions The options for the widget
	* @param string $sClass The class of the calling object if different
	* @return CPSjqToolsWrapper
	*/
	public static function create( array $arOptions = array(), $sClass = __CLASS__ )
	{
		return parent::create( self::PS_WIDGET_NAME, $arOptions, $sClass );
	}
	
}
<?php
/**
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * The CPSjqToolsWrapper allows the {@link http://flowplayer.org/tools/index.html jQuery Tools} to be used in Yii.
 * 
 * @package 	psYiiExtensions
 * @subpackage 	widgets
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id: CPSjqToolsWrapper.php 371 2010-02-08 06:43:18Z jerryablan@gmail.com $
 * @since 		v1.0.4
 *  
 * @filesource
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
	* @var array
	*/
	protected $m_arSupportFiles = array();
	
	//********************************************************************************
	//* Public Methods
	//********************************************************************************
	
	/**
	 * Preinitialize
	 */
	public function preinit()
	{
		//	Phone home...
		parent::preinit();
		
		//	Add the default options for jqUI stuff
		$this->addOptions( 
			array(
				'paneClass_' => 'string',
			)
		);
		
	}
	
	/**
	 * Initialize
	 */
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
			if ( ! $this->paneClass )
				throw new CException( Yii::t( 'yii', 'When creating "tabs" you must set the "paneClass" option to the class of the div containing the tab panes.' ) );
		}
	}
		
	/**
	* Registers the needed CSS and JavaScript.
	* @param boolean If true, system will try to find jquery plugins based on the pattern jquery.<plugin-name[.min].js
	* @return CClientScript The current app's ClientScript object
	*/
	public function registerClientScripts( $bLocateScript = false )
	{
		//	Daddy...
		parent::registerClientScripts( $bLocateScript );
		
		//	Reset the baseUrl for our own scripts
		$this->baseUrl = $this->extLibUrl . self::PS_EXTERNAL_PATH;
		
		//	Register scripts necessary
		$this->pushScriptFile( $this->baseUrl . '/jquery.tools.min.js' );
		
//	Uncomment to use CDN		
//		$this->pushScriptFile( "http://cdn.jquerytools.org/1.1.2/tiny/jquery.tools.min.js" );

		//	Add for flashembed if we need it...
		if ( 'flashembed' == $this->widgetName ) $this->pushScriptFile( "http://static.flowplayer.org/js/tools/tools.flashembed-1.0.4.min.js" );
		
		//	Register any CSS files for this tool...
		if ( isset( $this->m_arSupportFiles, $this->m_arSupportFiles[ $this->widgetName ], $this->m_arSupportFiles[ $this->widgetName ]['css'] ) )
		{
//	I forgot why I commented this out... sorry. It may be needed.			
//			foreach ( $this->m_arSupportFiles[ $this->widgetName ][ 'css' ] as $_sFile )
//				PS::_rcf( "{$this->baseUrl}/css/{$_sFile}", 'screen' );
		}

		//	Don't forget subclasses
		return PS::_cs();
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
				$_sScript = '$("' . $this->target . '").flashembed("' . $_sSrc . '", ' . $_arOptions . ');';
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
	public static function create( $sName = null, array $arOptions = array() )
	{
		return parent::create( PS::nvl( $sName, self::PS_WIDGET_NAME ), array_merge( $arOptions, array( 'class' => __CLASS__ ) ) );
	}

}
<?php
/**
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
* The CPSfgMenu allows the {@link http://www.filamentgroup.com/lab/jquery_ipod_style_and_flyout_menus/ Filament Group Fly-Out Menu} to be used in Yii.
 * 
 * @package 	psYiiExtensions.widgets
 * @subpackage 	jqui
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id: CPSfgMenu.php 368 2010-01-18 01:55:44Z jerryablan@gmail.com $
 * @since 		v1.0.6
 *  
 * @filesource
 * 
 * @todo Not yet completed
 */
class CPSfgMenu extends CPSjqUIWrapper
{
	//********************************************************************************
	//* Constants
	//********************************************************************************

	/**
	* The name of this widget
	*/
	const PS_WIDGET_NAME = 'fg-menu';
	
	/**
	* The path where the assets for this widget are stored (underneath the psYiiExtensions/external base
	*/
	const PS_EXTERNAL_PATH = '/jquery-plugins/fg-menu';

	//********************************************************************************
	//* Methods
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
				'prompt_' => 'string:Select One...::true',
				'valueColumn' => 'string',
				'imagePath_' => 'string',
				'anchorId_' => 'string',
				'data_' => 'array:array()',
			)
		);
	}
	
	/**
	 * Initialize
	 * 
	 */
	public function init()
	{
		parent::init();
	
		//	Set my name...	
		$this->widgetName = self::PS_WIDGET_NAME;
		
		//	Create an anchor id
		$this->setOption( 'anchorId', $this->getUniqueId( $this->getValue( 'anchorId', 'fg.menu.a.id' ) ) );
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
		$this->pushScriptFile( "{$this->baseUrl}/fg.menu.js" );

		//	Register css files...
		PS::_rcf( "{$this->baseUrl}/fg.menu.css" );
		
		return PS::_cs();
	}
	
	/**
	* Generate the necessary HTML
	* @return string
	*/
	protected function generateHtml()
	{
		$_sHtml = parent::generateHtml();
		
		//	Get our options...
		$this->target = $_sId = $this->getId();
		$_sAnchorId = $this->getValue( 'anchorId' );
		
		$_sHtml .= PS::hiddenField( $_sId . '_value', $this->getValue( 'value' ) );
		$_sHtml .= '<a tabindex="0" href="#' . $_sAnchorId . '" class="ps-button ps-button-icon-right ps-button-no-float ui-widget ui-state-default ui-corner-all" id="' . $_sId . '"><span class="ui-icon ui-icon-triangle-1-s"></span>' . $this->prompt . '</a>';
		$_sHtml .= '<div id="' . $_sAnchorId . '" class="hidden">';
		$_sHtml .= CPSTransform::asUnorderedList( $this->getValue( 'data', array() ), $this->makeOptions( true, PS::OF_ASSOC_ARRAY, true ) );
		$_sHtml .= '</div>';
		
		//	Remove the id...
		$this->target = $this->getTargetSelector( $_sAnchorId );
		
		$this->unsetOption('valueColumn');
		
		return $_sHtml;
	}

	/**
	* Generate the necessary JS for this widget
	* 
	* @param string $sTargetSelector
	* @param array $arOptions
	* @param string $sInsertBeforeOptions
	* @return string
	*/
	protected function generateJavascript( $sTargetSelector = null, $arOptions = null, $sInsertBeforeOptions = null )
	{
		//	Get the options...
		if ( $arOptions ) $this->mergeOptions( $arOptions );
		$_sId = $this->getId();
		
		//	Make options
		$_oData = $this->getOption( 'data', array(), true );
		$this->unsetOption('name');
		$this->unsetOption('id');
		$_sOptions = $this->makeOptions();

		$this->setOption( 'data', $_oData->getValue() );

		//	Jam something in front of options?
		if ( null != $sInsertBeforeOptions ) $_sOptions = $sInsertBeforeOptions . ( ! empty( $_sOptions ) ? ', ' . $_sOptions : '' );

		$this->script =<<<CODE
jQuery('#{$_sId}').menu({updateValueId:'#{$_sId}_value',backLink:false,content:jQuery("#{$_sId}").next().html()});
CODE;

		return $this->script;
	}
	
	//********************************************************************************
	//* Private methods
	//********************************************************************************

	/**
	* Constructs and returns a jQuery widget
	* 
	* The options passed in are dynamically added to the options array and will be accessible 
	* and modifiable as normal (.i.e. $this->theme, $this->baseUrl, etc.)
	* 
	* @param array $arOptions The options for the widget
	* @param string $sClass The class of the calling object if different
	* @return CPSjqGridWidget
	*/
	public static function create( $sName = null, array $arOptions = array() )
	{
		return parent::create( PS::nvl( $sName, self::PS_WIDGET_NAME ), array_merge( $arOptions, array( 'class' => __CLASS__ ) ) );
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
		parent::loadScripts( $oWidget, $sTheme );
		
		//	Instantiate if needed...
		$_oWidget = ( null == $oWidget ) ? new CPSfgMenu() : $oWidget;

		//	Register scripts necessary
		return $_oWidget->registerClientScripts();
	}

}
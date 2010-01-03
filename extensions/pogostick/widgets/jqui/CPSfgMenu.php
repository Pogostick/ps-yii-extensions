<?php
/*
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright &copy; 2009 Pogostick, LLC
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
 * @version 	SVN: $Id$
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

	public function init()
	{
		parent::init();
	
		//	Set my name...	
		$this->widgetName = self::PS_WIDGET_NAME;
	}
		
	/**
	* Registers the needed CSS and JavaScript.
	* @param boolean If true, system will try to find jquery plugins based on the pattern jquery.<plugin-name[.min].js
	* @returns CClientScript The current app's ClientScript object
	*/
	public function registerClientScripts( $bLocateScript = false )
	{
		//	Daddy...
		parent::registerClientScripts( $bLocateScript );
		
		//	Reset the baseUrl for our own scripts
		$this->baseUrl = $this->extLibUrl . self::PS_EXTERNAL_PATH;

		//	Register scripts necessary
		PS::_rsf( "{$this->baseUrl}/fg.menu.js" );

		//	Register css files...
		PS::_rcf( "{$this->baseUrl}/fg.menu.css" );
		
		return PS::_cs();
	}
	
	/**
	* Generate the necessary HTML
	* @returns string
	*/
	protected function generateHtml()
	{
		return null;
		
		$_sHtml .= parent::generateHtml();
		
		//	Get our options...
		$_sAnchorId = $this->getUniqueId( $this->getOption( 'anchorId', 'fg.menu.a.id' ) );
		
		//	Build HTML
		$_sHtml .= PS::tag( 'a', array( 'href' => '#' . $_sAnchorId, 'id' => $this->getTargetSelector(), 'class' => 'ps-button ps-button-icon-right ui-widget ui-state-default ui-corner-all' ), 
			PS::tag( 'span', array( 'class' => 'ui-icon ui-icon-triangle-1-s' ), $this->prompt )
		);
		
		$this->id = $_sAnchorId;
		$this->target = $this->getTargetSelector( $_sAnchorId );
		
		return $_sHtml . PS::tag( 'div', array( 'id' => $_sAnchorId, 'class' => 'hidden' ), 
			CPSModel::asUnorderedList( $this->data, $this->makeOptions( null, CPSComponentBehavior::ASSOC_ARRAY, true )  )
		);
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
		return null;
		
		//	Get the options...
		$_arOptions = PS::nvl( $arOptions, $this->makeOptions( null, CPSComponentBehavior::ASSOC_ARRAY ) );
		$_sId = $this->getTargetSelector( $sTargetSelector );
		
		//	Set a couple of convenient defaults
		if ( null == PS::o( $_arOptions, 'content', null ) ) $_arOptions['content'] = '$(\'' . $_sId . '\').next().html()';
		if ( null == PS::o( $_arOptions, 'crumbDefaultText', null ) ) $_arOptions['crumbDefaultText'] = ' ';
		
		//	Pull out the data...
		PS::o( $_arOptions, 'data', null, true );
			
		//	Make options
		$_sOptions = $this->makeOptions( $_arOptions, CPSComponentBehavior::OF_JSON, false, true );

		//	Jam something in front of options?
		if ( null != $sInsertBeforeOptions ) $_sOptions = $sInsertBeforeOptions . ( ! empty( $_sOptions ) ? ', ' . $_sOptions : '' );

		$this->script =<<<CODE
jQuery('{$_sId}').menu({$_arOptions});
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
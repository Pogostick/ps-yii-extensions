<?php
/**
 * CPSMarkItUpWidget class file.
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://ps-yii-extensions.googlecode.com
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @license http://www.pogostick.com/license/
 * @package psYiiExtensions
 */

/**
 * CPSMarkItUpWidget a wrapper to the excellent (@link http://markitup.jaysalvat.com MarkItUp jQuery widget)
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @version SVN: $Id$
 * @subpackage Widgets
 * @since 1.0.0
 */
class CPSMarkItUpWidget extends CPSjqUIWrapper
{
	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/***
	* Runs this widget
	*
	*/
	public function run()
	{
		parent::run();
		
		//	This widget info
		$this->baseUrl = $this->extLibUrl . '/jquery-plugins/markitup';
		$this->widgetName = 'markItUp';

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
		//	Daddy...
		$_oCS = Yii::app()->getClientScript();
		
		//	Register scripts necessary
		self::loadScripts( $this, $this->theme );
		$_oCS->registerScriptFile( "{$this->extLibUrl}/jquery-plugins/markitup/jquery.markitup.pack.js" );
		$_oCS->registerScriptFile( "{$this->extLibUrl}/jquery-plugins/markitup/sets/html/set.js" );
		$_oCS->registerCssFile( "{$this->extLibUrl}/jquery-plugins/markitup/skins/markitup/style.css" );
		$_oCS->registerCssFile( "{$this->extLibUrl}/jquery-plugins/markitup/sets/html/style.css" );
	
		//	Get the javascript for this widget
		$_oCS->registerScript( 'pswysiwyg.' . $this->widgetName . '#' . $this->id, $this->generateJavascript( 'markItUp', 'mySettings' ), CClientScript::POS_READY );
	}

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
	public static function create( array $arOptions = array(), $sClass = __CLASS__ )
	{
		return parent::create( 'markItUp', $arOptions, $sClass );
	}
	
}

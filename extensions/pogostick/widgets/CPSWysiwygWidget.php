<?php
/**
 * CPSWysiwygWidget class file.
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://ps-yii-extensions.googlecode.com
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @license http://www.pogostick.com/license/
 * @package psYiiExtensions
 */

/**
 * CPSWysiwygWidget a wrapper to the excellent (@link http://code.google.com/p/jwysiwyg/ WYSIWYG jQuery widget)
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @version SVN: $Id$
 * @subpackage Widgets
 * @since 1.0.0
 */
class CPSWysiwygWidget extends CPSjqUIWrapper
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
		$this->baseUrl = $this->extLibUrl . '/jquery-plugins';
		$this->widgetName = 'wysiwyg';

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
	
		//	Get the javascript for this widget
		$_oCS->registerScript( 'psjqui.' . $this->widgetName . '#' . $this->id, $this->generateJavascript(), CClientScript::POS_READY );
	}

}

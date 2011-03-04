<?php
/**
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * CPSgApiWidget provides access to the {@link http://code.google.com/apis/ajaxsearch/ Google AJAX APIs}
 * 
 * @package 	psYiiExtensions.widgets
 * @subpackage 	gApi
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id: CPSgApiWidget.php 358 2010-01-02 23:33:40Z jerryablan@gmail.com $
 * @since 		v1.0.0
 *  
 * @filesource
 */

class CPSgApiWidget extends CPSApiWidget
{
	//********************************************************************************
	//* Constructor
	//********************************************************************************

	/**
	* Constructor
	*
	*/
	public function preinit()
	{
		//	Daddy...
		parent::preinit();

		//	Our object settings
		$this->addOption( 'apisToLoad', array(), 'array:array():::maps|search|feeds|language|gdata|earth|visualization' );
	}

	//********************************************************************************
	//* Yii Overrides
	//********************************************************************************

	/**
	* Runs the widget
	*/
	public function run()
	{
		parent::run();
		$this->registerClientScripts();
		echo $this->generateHtml();
	}

	/**
	* Generates the needed javascript
	* @return string
	*/
	protected function generateJavascript()
	{
		$this->script = '';

		if ( is_array( $this->apisToLoad ) )
		{
			foreach ( $this->apisToLoad as $_sApi => $_sVersion )
				$this->script .= "google.load(\"{$_sApi}\", \"{$_sVersion}\");\n";
		}

		return $this->script;
	}

	/**
	* Registers the scripts necessary for this widget
	*/
	public function registerClientScripts()
	{
		parent::registerClientScripts();

		$_sApiKey = $this->apiKey;

		//	Register scripts necessary
		$this->pushScriptFile( "http://www.google.com/jsapi?key={$_sApiKey}", CClientScript::POS_HEAD );
		$this->pushScriptFile( "http://maps.google.com/maps?file=api&v=2&key={$_sApiKey}&sensor=false", CClientScript::POS_HEAD );
		$this->pushScriptFile( 'http://gmaps-utility-library.googlecode.com/svn/trunk/markermanager/1.1/src/markermanager.js', CClientScript::POS_HEAD );
		$this->pushScriptFile( 'http://gmaps-utility-library.googlecode.com/svn/trunk/extinfowindow/release/src/extinfowindow.js', CClientScript::POS_HEAD );

		PS::_rs( "Yii.{__CLASS__}.#.{$this->id}", $this->generateJavascript() );
		PS::_rs( "Yii.{__CLASS__}.#.{$this->id}.onLoad", 'initialize();' );
	}
}
<?php
/**
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * CPSFacebookConnect provides an interface to {@link http://developers.facebook.com/connect.php Facebook Connect}
 * 
 * @package 	psYiiExtensions.components
 * @subpackage 	facebook
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id: CPSFacebookConnect.php 354 2010-01-02 22:05:36Z jerryablan@gmail.com $
 * @since 		v1.0.3
 * 
 * @filesource
 * 
 * @todo Quite a bit
 */
class CPSFacebookConnect extends CPSApiWidget
{
	/**
	* Our init function
	*
	*/
	public function __construct()
	{
		parent::__construct();

		$this->addOptions(
			array(
				'appId' => 'string',
				'callbackUrl' => 'string',
				'xdrUrl' => 'string',
			)
		);
	}

	/***
	* Runs this widget
	*
	*/
	public function run()
	{
		//	Register the scripts/css
		$this->registerClientScripts();
	}

	protected function generateJavascript()
	{
		$_sUrl = $this->getOption( 'xdrUrl' );
		$_sOut =<<<JSCRIPT
FB.init('{$this->apiKey}', '{$_sUrl}' );
FB.ensureInit(
	function()
	{
//    	FB.Connect.showPermissionDialog( "email" );
	}
);
JSCRIPT;

		return( $_sOut );
  	}

  	protected function generateHtml()
  	{
  		return( '' );
	}

	/**
	* Register the necessary Facebook Connect scripts...
	*
	*/
	public function registerClientScripts()
	{
		parent::registerClientScripts();

		$this->pushScriptFile( 'http://static.ak.connect.facebook.com/js/api_lib/v0.4/FeatureLoader.js.php', CClientScript::POS_END );
		PS::_rs( 'Yii.' . __CLASS__ . '#' . $this->id, $this->generateJavascript() );
	}
}
<?php
/**
 * CPSFacebookConnect class file.
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://ps-yii-extensions.googlecode.com
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @license http://www.pogostick.com/license/
 */

/**
 * CPSFacebookConnect provides an interface to {@link http://developers.facebook.com/connect.php Facebook Connect}
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @version SVN $Id$
 * @filesource
 * @package psYiiExtensions
 * @subpackage Components
 * @since 1.0.0
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
				'appId' => array( CPSOptionManager::META_RULES => array( CPSComponent::META_TYPE => 'string' ) ),
				'callbackUrl' => array( CPSOptionManager::META_RULES => array( CPSComponent::META_TYPE => 'string' ) ),
				'xdrUrl' => array( CPSOptionManager::META_RULES => array( CPSComponent::META_TYPE => 'string' ) ),
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
		$_oCS = parent::registerClientScripts();

		$_oCS->registerScriptFile( 'http://static.ak.connect.facebook.com/js/api_lib/v0.4/FeatureLoader.js.php', CClientScript::POS_HEAD );
		$_oCS->registerScript( 'Yii.' . __CLASS__ . '#' . $this->id, $this->generateJavascript(), CClientScript::POS_READY );
	}
}
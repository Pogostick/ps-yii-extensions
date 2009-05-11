<?php
/**
 * CPSgApiWidget class file.
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://ps-yii-extensions.googlecode.com
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @license http://www.pogostick.com/license/
 * @package psYiiExtensions
 */

/**
 * CPSgApiWidget provides access to the {@link http://code.google.com/apis/ajaxsearch/ Google AJAX APIs}
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @version SVN: $Id$
 * @filesource
 * @package psYiiExtensions
 * @subpackage Widgets
 * @since 1.0.0
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
	public function __construct()
	{
		//	Daddy...
		parent::__construct();

		//	Our object settings
		$this->setOption(
			'apisToLoad',
			array(
				'_value' => array(),
				'_validPattern' =>
					array(
						'type' => 'array',
						'valid' => array( 'maps', 'search', 'feeds', 'language', 'gdata', 'earth', 'visualization' ),
					),
			),
			true
		);

		//	Log it and check for issues...
		CPSCommonBase::writeLog( Yii::t( $this->getInternalName(), '{class} constructed', array( "{class}" => get_class( $this ) ) ), 'trace', $this->getInternalName() );
	}

	//********************************************************************************
	//* Yii Overrides
	//********************************************************************************

	public function run()
	{
		parent::run();
		$this->registerClientScripts();
		echo $this->generateHtml();
	}

	protected function generateJavascript()
	{
		$this->script = '';

//		foreach ( $this->apisToLoad as $_sApi => $_sVersion )
//			$this->script .= "google.load(\"{$_sApi}\", \"{$_sVersion}\");";

		return( $this->script );
	}

	protected function generateHtml()
	{
		return( null );
	}

	public function registerClientScripts()
	{
		$_oCS = parent::registerClientScripts();

		$_sApiKey = $this->apiKey;

		//	Register scripts necessary
		$_oCS->registerScriptFile( "http://www.google.com/jsapi?key={$_sApiKey}", CClientScript::POS_HEAD );
		$_oCS->registerScriptFile( "http://maps.google.com/maps?file=api&v=2&key={$_sApiKey}&sensor=false", CClientScript::POS_HEAD );
		$_oCS->registerScriptFile( 'http://gmaps-utility-library.googlecode.com/svn/trunk/markermanager/1.1/src/markermanager.js', CClientScript::POS_HEAD );
//		$_oCS->registerScriptFile( 'http://gmaps-utility-library.googlecode.com/svn/trunk/tabbedmaxcontent/1.0/src/tabbedmaxcontent.js', CClientScript::POS_HEAD );
		$_oCS->registerScriptFile( 'http://gmaps-utility-library.googlecode.com/svn/trunk/extinfowindow/release/src/extinfowindow.js', CClientScript::POS_HEAD );

		$_oCS->registerScript( "Yii.{__CLASS__}.#.{$this->id}", $this->generateJavascript(), CClientScript::POS_READY );
		$_oCS->registerScript( "Yii.{__CLASS__}.#.{$this->id}.onLoad", "initialize();", CClientScript::POS_READY );
	}
}

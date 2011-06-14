<?php
/**
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * CPSFirePHP class file.
 *
 * This will repeat messages from the error.log as well as variables, form data, 
 * validation errors, session data, and other useful info into the Firebug console 
 * window.  Other than the normal log usage, you don't have to tell it to trace anything,
 * it'll get the info automagically.
 *
 * Installation:
 * -------------
 * 1.	Install Firebug, http://www.getfirebug.com, reopen browser, turn Firebug on.
 * 
 * 2.	Turn debug on in /index.php  ie: uncomment defined('YII_DEBUG') or define('YII_DEBUG',true);
 * 
 * 3.	Add as a component to your main.php configuration file: 
 * 
 * 'firePHP' => array(
 *     'class' => 'pogostick.helpers.CPSFirePHP',
 * ),
 * 
 * 4.	Add Yii::app()->firePHP->enableConsole( $this ); between the <HEAD> tags of your layout file(s). 
 * 
 * Credits:
 * --------
 * Converted from original CakePHP FireCake Helper by {@link http://bakery.cakephp.org/articles/view/firecake-helper zomg}
 * 
 * @package 	psYiiExtensions
 * @subpackage 	helpers
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id: CPSFirePHP.php 322 2009-12-23 23:51:37Z jerryablan@gmail.com $
 * @since 		v1.0.4
 *  
 * @filesource
 */
class CPSFirePHP extends CPSComponent
{
	//********************************************************************************
	//* Constants
	//********************************************************************************
	
	const	JS_NAME = 'arPSFirePHP';

	//********************************************************************************
	//* Public Methods
	//********************************************************************************
	
	/**
	* View FirePHP information
	* 
	* @param CView $oView
	*/
	function enableConsole( CView $oView  )
	{
		//	Define javascript array
		$_sScript = "\nvar " . self::JS_NAME . " = new Array();";
		$_sScript .= "\n" . self::JS_NAME . "[ 'Version' ] = '" . Yii::getVersion() . "';";

		//	Comment out the ones you don't need.
		//	The first 4 or 5 are suggested mostly.
		//	The others are general info that doesn't change much, but
		//	Might be good for familarization with the way Yii works.

		$_sScript .= "\n" . $this->getSessionData();
		$_sScript .= "\n" . $this->getPageData();
		$_sScript .= "\n" . $this->getVars( $oView );
		$_sScript .= "\n" . $this->getLogs();
		$_sScript .= "\n" . $this->getConstants();
		$_sScript .= "\n" . $this->getModels();
		$_sScript .= "\n" . $this->getControllers();
		$_sScript .= "\n" . $this->getPhp();
		$_sScript .= "\n" . $this->getModules();

		$_sScript .= " \nconsole.dir( " . self::JS_NAME . " );\n";
		
		//	Now echo it out and call the Firebug console.
		Yii::app()->getClientScript()->registerScript( self::JS_NAME, $_sScript, CClientScript::POS_READY );
	}
	
	//********************************************************************************
	//* Private Methods
	//********************************************************************************

	/**
	 * Get variables in the view
	 */
	protected function getVars( $oView )
	{
		//	Controller variables
		$_arData = array( 'action' => $oView->getAction()->getId(), 'id' => $oView->getId(), 'layout' => $oView->layout, 'uniqueId' => $oView->getUniqueId(), 'viewPath' => $oView->getViewPath(), 'pageTitle' => $oView->pageTitle ); 
		
		//	Parameters
		$_arParams = Yii::app()->getParams();
		if ( is_array( $_arParams ) ) $_arData = array_merge( $_arData, $_arParams );

		return self::JS_NAME . "[ 'Variables' ] = " . CJSON::encode( $_arData ) . ";";
	}

	/**
	 * Dumps the log file to the console
	 */
	protected function getLogs()
	{
		//	Flatten log entries...
		foreach ( Yii::getLogger()->getLogs() as $_oLog )
			$_arLogData[] = implode( ', ', $_oLog );
		
		return self::JS_NAME . "[ 'Application Log' ] = " . CJSON::encode( $_arLogData ) . ";";
	}
	
	/**
	 * Dumps the defined constants
	 */
	protected function getConstants()
	{
		$_arData = get_defined_constants( true );
		$_arData = $_arData[ 'user' ];
		return self::JS_NAME . "[ 'Constants' ] = " . json_encode( $_arData ) . ";";
	}
	
	/***
	* Dumps the current session data
	*/
	protected function getSessionData() 
	{
		return self::JS_NAME . "[ 'Session Data' ] = " . CJSON::encode( $_SESSION ) . ";";
	}

	/**
	 * Get page data, generally form submissions.
	 */
	protected function getPageData()
	{
		$_arData = array();
		
		if ( is_array( $_REQUEST ) ) $_arData = array_merge( $_arData, $_REQUEST );
		
		if ( ! empty( $_arData ) ) 
			return self::JS_NAME . "[ '\$_REQUEST Data' ] = " . CJSON::encode( $_arData ) . ";";

		return self::JS_NAME . "[ '$_REQUEST' ] = 'No Data Submitted';";
	}

	/**
	 * Get some info about your installation.
	 */
	function getPhp() 
	{
		$_arTemp = array();
		$_arTemp['VERSION'] = phpversion();
		$_arTemp['REQUEST_URI'] = $_SERVER['REQUEST_URI'];
		$_arTemp['SERVER_PORT'] = $_SERVER['SERVER_PORT'];
		$_arTemp['SCRIPT_FILENAME'] = $_SERVER['SCRIPT_FILENAME'];
		$_arTemp['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'];

		return self::JS_NAME . "['PHP Info'] = " . CJSON::encode( $_arTemp ) . ";";
	}

	/**
	 * Make sure you got mod_rewrite installed and other stuff.
	 */
	function getModules() 
	{
		if ( function_exists( 'apache_get_modules' ) ) return self::JS_NAME . "['Apache Modules'] = " . CJSON::encode( apache_get_modules() ) . ";";
	}

	/**
	 * Get each controller and some info about them put into an array.
	 */
	function getControllers() 
	{
		$_arTemp = array();

		$_sCPath = Yii::app()->getControllerPath();
		$_arPaths = scandir( $_sCPath );
		
		foreach ( $_arPaths as $_sPath )
		{
			if ( is_file( $_sCPath . DIRECTORY_SEPARATOR . $_sPath ) && preg_match( "/^(.+)Controller\.php$/", basename( $_sPath ), $m ) )
			{
				$_sControllerName = str_ireplace( 'Controller.php', '', $_sPath );
				$_sClassName = $_sControllerName . 'Controller';
				Yii::import( 'application.controllers.' . $_sClassName );
				$_sControllerName{0} = strtolower( $_sControllerName{0} );
				$_oObj = new $_sClassName( $_sControllerName );
				$_arData = $this->getClassDiffValues( get_class_vars( 'CController' ), get_object_vars( $_oObj ) );
				$_arData[ 'Defined Views' ] = $this->getViewList( $_oObj );

				$_arTemp[ $_sControllerName ] = $_arData;
			}
		}

		return self::JS_NAME . "[ 'Controllers' ] = ".CJSON::encode( $_arTemp ).";";
	}

	/**
	 * Get each model and some info about them put into an array.
	 */
	function getModels() 
	{
		$_arTemp = array();

		$_sCPath = Yii::getPathOfAlias( 'application.models' );
		$_arPaths = scandir( $_sCPath );
		
		foreach ( $_arPaths as $_sPath )
		{
			if ( is_file( $_sCPath . DIRECTORY_SEPARATOR . $_sPath ) && preg_match( "/^(.+)\.php$/", basename( $_sPath ), $m  ) )
			{
				$_sClassName = $_sModelName = str_ireplace( '.php', '', $_sPath );
				Yii::import( 'application.models.' . $_sClassName );
				$_oObj = new $_sClassName( $_sModelName );
				$_arData = $this->getClassDiffValues( get_class_vars( 'CModel' ), get_object_vars( $_oObj ) );
				$_arData[ 'Table Name' ] = ( method_exists( $_oObj, 'tableName' ) ) ? $_oObj->tableName() : $_sModelName;
				$_arTemp[ $_sModelName ] = $_arData;
			}
		}

		return self::JS_NAME . "[ 'Models' ] = ".CJSON::encode( $_arTemp ).";";
	}

	/**
	* Returns an array of variable differences between to classes
	* 
	* @param array $arBaseVars
	* @param array $arClassVars
	* @return bool
	*/
	function getClassDiffValues( &$arBaseVars, &$arClassVars )
	{
		$_arTemp = array();

		foreach ( $arClassVars as $_sName => $_oVar )
		{
			if ( @$arBaseVars[ $_sName ] !== $arClassVars[ $_sName ] )
				$_arTemp[ $_sName ] = ( is_array( $_oVar ) ) ? implode( ', ', $_oVar ) : $_oVar;
		}

		return $_arTemp;
	}

	/**
	* Gets a list of views for a controller
	* 
	* @param CBaseController $oCtrl
	*/
	protected function getViewList( $oCtrl )
	{
		$_arTemp = array();
		$_sPath = $oCtrl->getViewPath();
		
		try
		{
			if ( is_dir( $_sPath ) )
			{
				foreach ( scandir( $_sPath ) as $_sFile )
				{
					if ( is_file( $_sPath . DIRECTORY_SEPARATOR . $_sFile ) && preg_match("/(.php|.html)$/", $_sFile ) ) 
						$_arTemp[] = $_sFile;
				}
			}
		}
		catch ( Exception $_ex ) {}
		
		return $_arTemp;
	}
}
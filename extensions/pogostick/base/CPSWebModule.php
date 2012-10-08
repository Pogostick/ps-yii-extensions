<?php
/**
 * This file is part of the psYiiExtensions package.
 *
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link      http://www.pogostick.com Pogostick, LLC.
 * @license   http://www.pogostick.com/licensing
 */

/**
 * CPSWebModule provides extra functionality to the base module functionality of Yii
 *
 * @package        psYiiExtensions
 * @subpackage     base
 *
 * @author         Jerry Ablan <jablan@pogostick.com>
 * @version        SVN $Id: CPSWebModule.php 401 2010-08-31 21:04:18Z jerryablan@gmail.com $
 * @since          v1.0.5
 *
 * @filesource
 */
class CPSWebModule extends CWebModule implements IPSBase
{
	//********************************************************************************
	//* Private Members & Accessors
	//********************************************************************************

	protected $_debugMode = null;

	public function getDebugMode()
	{
		return $this->_debugMode;
	}

	public function setDebugMode( $value )
	{
		$this->_debugMode = $value;
	}

	protected $_configPath = null;

	public function getConfigPath()
	{
		return $this->_configPath;
	}

	public function setConfigPath( $sValue )
	{
		$this->_configPath = $sValue;
	}

	protected $_assetPath = null;

	public function getAssetPath()
	{
		return $this->_assetPath;
	}

	public function setAssetPath( $sValue )
	{
		$this->_assetPath = $sValue;
	}

	protected $_assetUrl = null;

	public function getAssetUrl()
	{
		return $this->_assetUrl;
	}

	protected function setAssetUrl( $sUrl )
	{
		$this->_assetUrl = $sUrl;
	}

	//	Accessor to app's db...
	public function getDB()
	{
		return Yii::app()->getDB();
	}

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	 * Initialize
	 *
	 */
	public function init()
	{
		//	Phone home...
		parent::init();

		//	import the module-level models and components
		$this->setImport(
			array(
				$this->id . '.models.*',
				$this->id . '.components.*',
			)
		);

		//	Read private configuration...
		if ( !empty( $this->_configPath ) )
		{
			$this->configure( require( $this->basePath . $this->_configPath ) );
		}

		//	Get our asset manager going...
		$this->_setAssetPaths();

		//	Who doesn't need this???
		if ( false !== ( CPSHelperBase::o( CPSHelperBase::_cs()->scriptMap, 'jquery.js' ) ) )
		{
			CPSHelperBase::_cs()->registerCoreScript( 'jquery' );
		}
	}

	//********************************************************************************
	//* Private Methods
	//********************************************************************************

	/**
	 * Initializes the asset manager for this module
	 */
	protected function _setAssetPaths()
	{
		$_assetManager = CPSHelperBase::_a()->getAssetManager();
		if ( !$this->_assetPath )
		{
			$this->_assetPath = $_assetManager->getBasePath() . DIRECTORY_SEPARATOR . $this->getId();
		}
		if ( !is_dir( $this->_assetPath ) )
		{
			@mkdir( $this->_assetPath );
		}
		$this->_assetUrl = $_assetManager->publish( $this->_assetPath, true, -1 );
	}

}
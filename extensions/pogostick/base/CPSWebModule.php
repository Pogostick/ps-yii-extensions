<?php
/**
 * CPSWebModule class file.
 *
 * @filesource
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://www.pogostick.com Pogostick, LLC.
 * @package psYiiExtensions
 * @subpackage Base
 * @since v1.0.5
 * @version SVN: $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified  $Date$
 */
/**
 * CPSWebModule provides extra functionality to the base module functionality of Yii
 *
 * @package psYiiExtensions
 * @subpackage Base
 */
class CPSWebModule extends CWebModule
{
	//********************************************************************************
	//* Private Members & Accessors
	//********************************************************************************
	
	protected $m_sConfigPath = null;
	public function getConfigPath() { return $this->m_sConfigPath; }
	public function setConfigPath( $sValue ) { $this->m_sConfigPath = $sValue; }	

	protected $m_sAssetPath = null;
	public function getAssetPath() { return $this->m_sAssetPath; }
	public function setAssetPath( $sValue ) { $this->m_sAssetPath = $sValue; }	
	
	protected $m_sAssetUrl = null;
	public function getAssetUrl() { return $this->m_sAssetUrl; }
	protected function setAssetUrl( $sUrl ) { $this->m_sAssetUrl = $sUrl; }

	protected $m_sDBTablePrefix = null;
	public function getDBTablePrefix() { return $this->m_sDBTablePrefix; }
	public function setDBTablePrefix( $sValue ) { $this->m_sDBTablePrefix = $sValue; }	
	
	//	Accessor to app's db...
	public function getDB() { return Yii::app()->getDB(); }

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
		if ( ! empty( $this->m_sConfigPath ) ) $this->configure( require( $this->basePath . $this->m_sConfigPath ) );
		
		//	Set our asset url...
		$this->assetUrl = Yii::app()->assetManager->publish( $this->basePath . ( empty( $this->assetPath ) ? DIRECTORY_SEPARATOR . 'assets' : $this->assetPath ), true, -1 );
		
		//	Add jquery
		Yii::app()->clientScript->registerCoreScript( 'jquery' );
	}                                                                                                          	
	
}
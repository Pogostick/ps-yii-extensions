<?php
/**
 * CPSController class file.
 *
 * @filesource
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://www.pogostick.com Pogostick, LLC.
 * @package psYiiExtensions
 * @subpackage Controllers
 * @since v1.0.4
 * @version SVN: $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified  $Date$
 */

 /**
 * CPSController provides filtered access to resources
 *
 * @package psYiiExtensions
 * @subpackage Controllers
 */
abstract class CPSController extends CController
{
	//********************************************************************************
	//* Constants
	//********************************************************************************

	/**
	* @var integer The number of items to display per page
	*/
	const PAGE_SIZE = 10;
	
	/**
	* @var string Indexes into {@link CPSController:m_arUserActionMap}
	*/
	const ACCESS_TO_ANY = 0;
	const ACCESS_TO_AUTH = 1;
	const ACCESS_TO_ADMIN = 2;
	const ACCESS_TO_NONE = 3;

	//********************************************************************************
	//* Member Variables
	//********************************************************************************
	
	/**
	* An optional page heading
	* 
	* @var string
	*/
	protected $m_sPageHeading = null;
	public function getPageHeading() { return $this->m_sPageHeading; }
	public function setPageHeading( $sValue ) { $this->m_sPageHeading = $sValue; }

	/**
	* @var CActiveRecord The currently loaded data model instance.
	* @access protected
	*/
	protected $m_oModel = null;
	public function getModel() { return $this->m_oModel; }
	protected function setModel( $oValue ) { $this->m_oModel = $oValue; }	
	
	/**
	* @var string The name of the model for this controller
	* @access protected
	*/
	protected $m_sModelName = null;
	public function getModelName() { return $this->m_sModelName; }
	protected function setModelName( $sValue ) { $this->m_sModelName = $sValue; }	

	/**
	* @var boolean Try to find proper layout to use
	* @access protected
	*/
	protected $m_bAutoLayout = true;
	public function getAutoLayout() { return $this->m_bAutoLayout; }
	public function setAutoLayout( $bValue ) { $this->m_bAutoLayout = $bValue; }
	
	/**
	* @var string The default action for this controller
	* @access protected
	*/
	protected $m_sDefaultAction = 'index';
	public function getDefaultAction() { return $this->m_sDefaultAction; }
	public function setDefaultAction( $sValue ) { $this->m_sDefaultAction = $sValue; }	
	
	/**
	* @var array An associative array of POST commands and their applicable methods
	* @access protected
	*/
	protected $m_arCommandMap = array();
	public function getCommandMap() { return $this->m_arCommandMap; }
	public function setCommandMap( $oValue ) { $this->m_arCommandMap = $oValue; }	
	public function addCommandToMap( $sKey, $oValue = null, $eWhich = null ) { $this->m_arCommandMap[ $sKey ] = $oValue; if ( $eWhich ) $this->addUserActions( $eWhich, array( $sKey ) ); }	

	/**
	* @var array An array of actions permitted by any user
	* @access protected
	*/
	protected $m_arUserActionList = array();
	public function getUserActionList( $eWhich ) { return CPSHelp::getOption( $this->m_arUserActionList, $eWhich ); }
	public function setUserActionList( $eWhich, $arValue ) { $this->m_arUserActionList[ $eWhich ] = $arValue; }
	public function addUserActions( $eWhich, $arActions = array() ) { $this->m_arUserActionList[ $eWhich ] = array_merge( $this->m_arUserActionList[ $eWhich ], $arActions );  }

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	* Initialize the controller
	* 
	*/
	public function init()
	{
		//	Phone home...
		parent::init();
		
		//	Find layout...
		if ( $this->m_bAutoLayout ) if ( file_exists(Yii::app()->getBasePath().'/views/layouts/'.$this->getId().'.php') ) $this->layout = $this->getId();
	}

	/**
	* A generic action that renders a page and passes in the model
	* 
	* @param string The action id
	* @param CModel The model
	* @param array Extra parameters to pass to the view
	* @param string The name of the variable to pass to the view. Defaults to 'model'
	*/
	public function genericAction( $sActionId, $oModel = null, $arExtraParams = array(), $sModelVarName = 'model' )
	{
		$this->render( $sActionId, array_merge( $arExtraParams, array( $sModelVarName => ( $oModel ) ? $oModel : $this->loadModel() ) ) );
	}
	
	/**
	* Returns the data model based on the primary key given in the GET variable.
	* If the data model is not found, an HTTP exception will be raised.
	* 
	* @param integer the primary key value. Defaults to null, meaning using the 'id' GET variable
	* @throws CHttpException
	*/
	public function loadModel( $iId = null )
	{
		if ( null === $this->m_oModel )
		{
			$_iId = CPSHelp::getOption( $_GET, 'id', $iId );
			$this->m_oModel = $this->load( $_iId );

			//	No data? bug out
			if ( null === $this->m_oModel ) CHttpException( 404, 'The requested page does not exist.' );
			
			//	Get the name of this model...
			$this->m_sModelName = get_class( $this->m_oModel );
		}
		
		//	Return our model...
		return $this->m_oModel;
	}

	//********************************************************************************
	//* Private Methods
	//********************************************************************************
	
	/**
	* Saves the data in the model
	* 
	* @param CModel The model to save
	* @param array The array of data to merge with the model
	* @param string Where to redirect after a successful save
	*/
	protected function saveModel( $oModel, $arData = array(), $sRedirectAction = 'show' )
	{
		if ( isset( $arData, $arData[ $this->m_sModelName ] ) )
		{
			$oModel->setAttributes( $arData[ $this->m_sModelName ] );
			if ( $oModel->save() ) $this->redirect( array( $sRedirectAction, 'id' => $oModel->id ) );
		}
		
		return false;
	}
	
	/**
	* Loads a page of models
	* @param boolean Whether or not to apply a sort. Defaults to false
	* 
	* @returns array Element 0 is the results of the find. Element 1 is the pagination object
	*/
	protected function loadPaged( $bSort = false )
	{
		$_oSort = $_oCrit = $_oPage = null;
		
		//	Make criteria
		$_oCrit = new CDbCriteria;
		$_oPage = new CPagination( $this->loadCount( $_oCrit ) );
		$_oPage->pageSize = self::PAGE_SIZE;
		$_oPage->applyLimit( $_oCrit );
	
		//	Sort...
		if ( $bSort )
		{	
			$_oSort = new CSort( $this->m_sModelName );
			$_oSort->applyOrder( $_oCrit );
		}

		//	Return an array of what we've build...
		return array( $this->loadAll( $_oCrit ), $_oCrit, $_oPage, $_oSort );
	}
	
	/**
	* Loads a model(s) based on criteria and scopes.
	* 
	* @param string The method to append
	* @param CDbCriteria The criteria for the lookup
	* @param array Scopes to apply to this request
	* @param array Options for the data load
	* @returns CActiveRecord|array
	*/
	protected function genericModelLoad( $sMethod, &$oCrit = null, $arScope = array(), $arOptions = array() )
	{
		$_sMethod = $this->getModelLoadString( $arScope, $arOptions ) . $sMethod;
		return eval( "return (" . $_sMethod . ");" );
	}

	/**
	* This method reads the data from the database and returns the row. 
	* Must override in subclasses.
	* @var integer $iId The primary key to look up
	* @returns CActiveRecord
	*/
	protected function load( $iId = null )
	{
		return $this->genericModelLoad( 'findByPk(' . $iId . ')' );
	}
	
	/**
	* Loads all data using supplied criteria
	* @param CDbCriteria $oCrit
	* @return array Array of CActiveRecord
	* @todo When using PHP v5.3, {@link eval} will no longer be needed
	*/
	protected function loadAll( &$oCrit = null )
	{
		return $this->genericModelLoad( 'findAll(' . ( null !== $oCrit ? '$oCrit' : '' ) . ')', $oCrit );
	}
	
	/**
	* Returns the count of rows that match the supplied criteria
	* 
	* @param CDbCriteria $oCrit
	* @return integer The number of rows
	*/
	protected function loadCount( &$oCrit = null )
	{
		$_sCrit = ( $oCrit ) ? '$oCrit' : null;
		return $this->genericModelLoad( 'count(' . $_sCrit. ')', $oCrit );
	}
	
	/**
	* Builds a string suitable for {@link eval}. The verb is intentionally not appeneded.
	* 
	* @param array $arScope
	* @returns string
	* @todo Will be deprecated after upgrade to PHP v5.3
	*/
	protected function getModelLoadString( $arScope = array(), $arOptions = array() )
	{
		$_sScopes = ( count( $arScope ) ) ? implode( '->', $arScope ) . '->' : null;
		return $this->m_sModelName . '::model()->' . $_sScopes;
	}

}
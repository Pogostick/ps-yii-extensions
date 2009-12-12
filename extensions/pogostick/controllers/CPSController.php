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
	const ACCESS_TO_ANON = 0;
	const ACCESS_TO_GUEST = 1;
	const ACCESS_TO_AUTH = 2;
	const ACCESS_TO_ADMIN = 3;
	const ACCESS_TO_SUPERADMIN = 5;
	
	//	Last...
	const ACCESS_TO_NONE = 6;

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

	/***
	* Allows you to change your action prefix
	* 
	* @var string
	*/
	protected $m_sMethodPrefix = 'action';
	public function getMethodPrefix() { return $this->m_sMethodPrefix; }
	public function setMethodPrefix( $sValue ) { $this->m_sMethodPrefix = $sValue; }

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
	protected function setModelName( $sValue ) 
	{ 
		$this->m_sModelName = $sValue; 
		$this->m_sGlobalSearchStateId = 'PS_' . strtoupper( $this->modelName ) . '_SEARCH_CRIT';
		$this->m_arCurrentSearchCriteria = Yii::app()->user->getState( $this->m_sGlobalSearchStateId );
	}

	/**
	* The id in the global state of our current filter/search criteria
	* 
	* @var string
	*/
	protected $m_sGlobalSearchStateId = null;
	
	/**
	* Stores the current search criteria
	* 
	* @var array
	*/
	protected $m_arCurrentSearchCriteria = null;
	public function getSearchCriteria() { return $this->m_arCurrentSearchCriteria; }
	public function setSearchCriteria( $arValue ) 
	{
		$this->m_arCurrentSearchCriteria = $arValue; 
		Yii::app()->user->setState( $this->m_sGlobalSearchStateId, $arValue ); 
	}
	
	/**
	* @var boolean Try to find proper layout to use
	* @access protected
	*/
	protected $m_bAutoLayout = true;
	public function getAutoLayout() { return $this->m_bAutoLayout; }
	public function setAutoLayout( $bValue ) { $this->m_bAutoLayout = $bValue; }
	
	/**
	* @var boolean Try to find missing action
	* @access protected 
	*/
	protected $m_bAutoMissing = true;
	public function getAutoMissing() { return $this->m_bAutoMissing; }
	public function setAutoMissing( $bValue ) { $this->m_bAutoMissing = $sValue; }
	
	/**
	* @var array An associative array of POST commands and their applicable methods
	* @access protected
	*/
	protected $m_arCommandMap = array();
	public function getCommandMap() { return $this->m_arCommandMap; }
	public function setCommandMap( $oValue ) { $this->m_arCommandMap = $oValue; }	
	public function addCommandToMap( $sKey, $oValue = null, $eWhich = null ) { $this->m_arCommandMap[ $sKey ] = $oValue; if ( $eWhich ) $this->addUserActions( $eWhich, array( $sKey ) ); }	

	/**
	* Action queue for keeping track of where we are...
	* 
	* @var array
	*/
	protected $m_arActionQueue = array();
	
	/**
	* @var array An array of actions permitted by any user
	* @access protected
	*/
	protected $m_arUserActionList = array();
	protected function resetUserActionList() { $this->m_arUserActionList = array(); $this->addUserAction( self::ACCESS_TO_ANY, 'error' ); }
	protected function setUserActionList( $eWhich, $arValue ) { $this->m_arUserActionList[ $eWhich ] = null; $this->addUserActions( $eWhich, $arValue ); }
	public function getUserActionList( $eWhich ) { return CPSHelp::getOption( $this->m_arUserActionList, $eWhich ); }
	public function addUserActionRole( $eWhich, $sRole, $sAction ) { $this->m_arUserActionList[ $eWhich ]['roles'][] = $arValue; }

	public function addUserAction( $eWhich, $sAction ) 
	{ 
		if ( ! isset( $this->m_arUserActionList[ $eWhich ] ) || ! is_array( $this->m_arUserActionList[ $eWhich ] ) ) 
			$this->m_arUserActionList[ $eWhich ] = array(); 
			
		if ( ! in_array( $sAction, $this->m_arUserActionList[ $eWhich ] ) )
			$this->m_arUserActionList[ $eWhich ][] = $sAction;
			
		//	Make sure we don't lose our error handler...
		if ( $eWhich == self::ACCESS_TO_ANY )
		{
			if ( ! in_array( 'error', $this->m_arUserActionList[ $eWhich ] ) )
				$this->addUserAction( self::ACCESS_TO_ANY, 'error' );
		}
	}

	public function addUserActions( $eWhich, $arActions = array() ) 
	{ 
		if ( ! is_array( $this->m_arUserActionList[ $eWhich ] ) ) 
			$this->m_arUserActionList[ $eWhich ] = array(); 
		
		foreach ( $arActions as $_sAction )
			$this->addUserAction( $eWhich, $_sAction );
	}
	
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
		if ( $this->m_bAutoLayout && ! isset( $_SERVER['argv'] ) ) if ( file_exists( Yii::app()->getBasePath() . '/views/layouts/' . $this->getId() . '.php' ) ) $this->layout = $this->getId();
		
		//	Allow errors
		$this->addUserAction( self::ACCESS_TO_ANY, 'error' );
		
		//	Pull any search criteria we've stored...
		if ( $this->getModelName() ) $this->m_arCurrentSearchCriteria = Yii::app()->user->getState( $this->m_sGlobalSearchStateId );
	}
	
	/**
	* A generic action that renders a page and passes in the model
	* 
	* @param string The action id
	* @param CModel The model
	* @param array Extra parameters to pass to the view
	* @param string The name of the variable to pass to the view. Defaults to 'model'
	*/
	public function genericAction( $sActionId, $oModel = null, $arExtraParams = array(), $sModelVarName = 'model', $sFlashKey = null, $sFlashValue = null, $sFlashDefaultValue = null )
	{
		if ( $sFlashKey ) Yii::app()->user->setFlash( $sFlashKey, $sFlashValue, $sFlashDefaultValue );
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
			if ( null == $this->m_oModel ) $this->redirect( array( $this->defaultAction ) );
			
			//	Get the name of this model...
			$this->setModelName( get_class( $this->m_oModel ) );
		}
		
		//	Return our model...
		return $this->m_oModel;
	}

	/**
	* Provide automatic missing action mapping...
	* 
	* @param string $sActionId
	*/
	public function missingAction( $sActionId )
	{
		if ( $this->m_bAutoMissing ) if ( $this->getViewFile( $sActionId ) ) $this->render( $sActionId );
		parent::missingAction( $sActionId );
	}

	/**
	* If value is !set||empty, last passed in argument is returned
	* 
	* Allows for multiple nvl chains ( nvl(x,y,z,null) ). Copied from CPSHelp::nvl()
	* for ease of use.
	* 
	* @param mixed 
	* @see CPSHelp::nvl
	*/
	public function nvl()
	{
		$_oDefault = null;
		$_iArgs = func_num_args();
		$_arArgs = func_get_args();
		
		for ( $_i = 0; $_i < $_iArgs; $_i++ )
		{
			if ( isset( $_arArgs[ $_i ] ) && ! empty( $_arArgs[ $_i ] ) )
				return $_arArgs[ $_i ];
				
			$_oDefault = $_arArgs[ $_i ];
		}

		return $_oDefault;
	}
	
	/**
	* Our error handler...
	* 
	*/
	public function actionError()
	{
		if ( ! $_oError = Yii::app()->errorHandler->error )
			throw new CHttpException( 404, 'Page not found.' );

	    $this->render( 'error', array( 'error' => $_oError ) );
	}
	
	//********************************************************************************
	//* Private Methods
	//********************************************************************************
	
	/**
	* Saves the data in the model
	* 
	* @param CModel $oModel The model to save
	* @param array $arData The array of data to merge with the model
	* @param string $sRedirectAction Where to redirect after a successful save
	* @param boolean $bAttributesSet If true, attributes will not be set from $arData
	* @param string $sModelName Optional model name
	* @param string $sSuccessMessage Flash message to set if successful
	* @param boolean $bNoCommit If true, transaction will not be committed
	* @returns boolean
	*/
	protected function saveModel( $oModel, $arData = array(), $sRedirectAction = 'show', $bAttributesSet = false, $sModelName = null, $sSuccessMessage = null, $bNoCommit = false )
	{
		$_sMessage = PS::nvl( $sSuccessMessage, 'Your changes have been saved.' );
		$_sModelName = PS::nvl( $sModelName, PS::nvl( $oModel->modelName, $this->m_sModelName ) );

		if ( isset( $arData, $arData[ $_sModelName ] ) )
		{
			if ( ! $bAttributesSet ) $oModel->setAttributes( $arData[ $_sModelName ] );
			
			if ( $oModel->save() ) 
			{
				if ( ! $bNoCommit && $oModel instanceof CPSModel && $oModel->hasTransaction() ) $oModel->commitTransaction();
				
				Yii::app()->user->setFlash( 'success', $_sMessage );
				
				if ( $sRedirectAction ) 
					$this->redirect( array( $sRedirectAction, 'id' => $oModel->id ) );

				return true;
			}
		}
		
		return false;
	}
	
	/**
	* Loads a page of models
	* @param boolean Whether or not to apply a sort. Defaults to false
	* 
	* @returns array Element 0 is the results of the find. Element 1 is the pagination object
	*/
	protected function loadPaged( $bSort = false, $oCriteria = null )
	{
		$_oSort = $_oCrit = $_oPage = null;

		//	Make criteria
		$_oCrit = PS::nvl( $oCriteria, new CDbCriteria() );
		$_oPage = new CPagination( $this->loadCount( $_oCrit ) );
		$_oPage->pageSize = PS::nvl( $_REQUEST['perPage'], self::PAGE_SIZE );
		if ( isset( $_REQUEST, $_REQUEST['page'] ) ) $_oPage->setCurrentPage( intval( $_REQUEST['page'] ) - 1 );
		$_oPage->applyLimit( $_oCrit );
		
		//	Sort...
		if ( $bSort )
		{
			$_oSort = new CPSSort( $this->m_sModelName );
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
	
	/**
	* Pushes an action onto the action queue
	* 
	* @param CAction $oAction
	*/
	protected function pushAction( $oAction )
	{
		array_push( $this->m_arActionQueue, $oAction );
	}
	
	/**
	* Retrieves the latest pushed action
	* @return CAction
	*/
	protected function popAction()
	{
		return array_pop( $this->m_arActionQueue );
	}

	/**
	* Clears the current search criteria
	* @returns null 
	*/
	protected function clearSearchCriteria()
	{
		$this->m_arCurrentSearchCriteria = null;
		Yii::app()->user->clearState( $this->m_sGlobalSearchStateId );
		
		return null;
	}

	/**
	* Logs a message to the application log
	* 
	* @param string $sMessage
	* @param string $sCategory
	*/
	protected function log( $sMessage, $sCategory = __CLASS__, $sLevel = 'trace' )
	{
		return Yii::log( $sMessage, $sLevel, $sCategory );
	}
	
	/**
	* Log helpers
	* 
	* @param string $sMessage
	* @param string $sCategory
	*/
	protected function logInfo( $sMessage, $sCategory = __CLASS__ ) { self::log( $sMessage, $sCategory, 'info' ); }
	protected function logError( $sMessage, $sCategory = __CLASS__ ) { self::log( $sMessage, $sCategory, 'error' ); }
	protected function logWarning( $sMessage, $sCategory = __CLASS__ ) { self::log( $sMessage, $sCategory, 'warning' ); }
	protected function logTrace( $sMessage, $sCategory = __CLASS__ ) { self::log( $sMessage, $sCategory, 'trace' ); }
}

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
	protected $m_sDefaultAction = 'admin';
	public function getDefaultAction() { return $this->m_sDefaultAction; }
	public function setDefaultAction( $sValue ) { $this->m_sDefaultAction = $sValue; }	
	
	/**
	* @var array An associative array of POST commands and their applicable methods
	* @access protected
	*/
	protected $m_arCommandMap = array();
	public function getCommandMap() { return $this->m_arCommandMap; }
	public function setCommandMap( $oValue ) { $this->m_arCommandMap = $oValue; }	
	public function addCommandToMap( $sKey, $oValue = null ) { $this->m_arCommandMap[ $sKey ] = $oValue; }	

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

		//	Add command mappings...
		$this->addCommandToMap( 'delete' );
		$this->addCommandToMap( 'undelete' );

		//	Set our access rules..
		$this->setUserActionList( self::ACCESS_TO_ANY, array( 'login', 'index' ) );
		$this->setUserActionList( self::ACCESS_TO_AUTH, array( 'admin', 'create', 'delete', 'logout', 'show', 'update' ) );
		$this->setUserActionList( self::ACCESS_TO_ADMIN, array() );
		$this->setUserActionList( self::ACCESS_TO_NONE, array() );
		
		//	Find layout...
		if ( $this->m_bAutoLayout ) if ( file_exists(Yii::app()->getBasePath().'/views/layouts/'.$this->getId().'.php') ) $this->layout = $this->getId();
	}

	/**
	* The filters for this controller
	* 
	* @returns array Action filters
	*/
	public function filters()
	{
		//	Perform access control for CRUD operations
		return array(
			'accessControl',
		);
	}

	/**
	* The base access rules for our CRUD controller
	* 
	* @returns array Access control rules
	*/
	public function accessRules()
	{
		static $_arRules;
		static $_bInit;
		
		//	Build access rule array...
		if ( ! isset( $_bInit ) )
		{
			$_arRules = array();
			
			for ( $_i = 0; $_i <= self::ACCESS_TO_NONE; $_i++ )
			{
				//	Get the user type
				switch ( $_i )
				{
					case self::ACCESS_TO_ANY: 	$_sVerb = 'allow'; 	$_sValid = '*'; 	break;
					case self::ACCESS_TO_AUTH: 	$_sVerb = 'allow'; 	$_sValid = '@'; 	break;
					case self::ACCESS_TO_ADMIN:	$_sVerb = 'allow'; 	$_sValid = 'admin';	break;
					case self::ACCESS_TO_NONE: 	$_sVerb = 'deny'; 	$_sValid = '*';		break;
				}
				
				//	Add to rules array
				$_arRules[] = array( 
					$_sVerb, 
					'actions' => $this->m_arUserActionList[ $_i ],
					'users' => array( $_sValid )
				);
			}
			
			$_bInit = true;
		}

		//	Return the rules...
		return $_arRules;
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
	//* Default actions
	//********************************************************************************

	/**
	* Shows a model
	*/
	public function actionShow() { $this->genericAction( 'show' ); }

	/**
	* Creates a new model.
	* If creation is successful, the browser will be redirected to the 'show' page.
	* 
	* @param array If specified, also passed to the view. 
	*/
	public function actionCreate( $arExtraParams = array() )
	{
		$_oModel = new $this->m_sModelName;
		if ( ! $this->saveModel( $_oModel, $_POST ) ) $this->genericAction( 'create', $_oModel, $arExtraParams );
	}
	
	/**
	* Update the model
	* 
	*/
	public function actionUpdate( $arExtraParams = array() )
	{
		$_oModel = $this->loadModel();
		if ( ! $this->saveModel( $_oModel, $_POST ) ) $this->genericAction( 'update', $_oModel, $arExtraParams );
	}

	/**
	* Deletes a particular model. 
	* Only allowed via POST
	* 
	* @throws CHttpException
	*/
	public function actionDelete( $sRedirectAction = 'admin' )
	{
		if ( Yii::app()->request->isPostRequest && $this->loadModel() )
		{
			$this->loadModel()->delete();
			$this->redirect( array( $sRedirectAction ) );
		}
		
		throw new CHttpException( 400, 'Invalid request. Please do not repeat this request again.' );
	}

	/**
	* Lists all models
	*/
	public function actionList( $arExtraParams = array() )
	{
		@list( $_arModels, $_oCrit, $_oPage, $_oSort ) = $this->loadAll( $_oCrit );
		$this->render( 'list', array_merge( $arExtraParams, array( 'models' => $_arModels, 'pages' => $_oPage ) ) );
	}
	
	/**
	* Manages all models.
	*/
	public function actionAdmin( $arExtraParams = array() )
	{
		$_oResult = $this->processCommand();
		list( $_arModels, $_oCrit, $_oPage, $_oSort ) = $this->loadPaged( true );
		$this->render( 'admin', array_merge( $arExtraParams, array( 'models' => $_arModels, 'pages' => $_oPage, 'sort' => $_oSort, 'result' => $_oResult ) ) );
	}

	//********************************************************************************
	//* Command Methods
	//********************************************************************************
	
	/**
	* Delete a model
	* 
	*/
	protected function commandDelete()
	{
		$this->loadModel( $_POST[ 'id' ] )->delete();
		$this->refresh();
	}
	
	/**
	* Undelete a model
	* 
	*/
	protected function commandUndelete()
	{
		$this->loadModel( $_POST[ 'id' ] )->delete( true );
		$this->refresh();
	}
	
	//********************************************************************************
	//* Private Methods
	//********************************************************************************
	
	/**
	* Executes any command triggered on the admin page. 
	* Maps to {@link CPSController::adminCommandMap} and calls the appropriate method.
	* 
	* @returns mixed
	*/
	protected function processCommand( $arData = array(), $bPOSTSource = true, $sIndexName = 'command' )
	{
		//	Our return variable
		$_oResults = null;
		
		//	Get command's method...
		$_sCmd = strtolower( CPSHelp::getOption( $arData, $sIndexName ) );
		
		//	Do we have a command mapping?
		if ( in_array( $_sCmd, array_keys( $this->m_arCommandMap ) ) )
		{
			//	Get the method name to call...
			$_sMethod = CPSHelp::getOption( $this->m_arCommandMap, $_sCmd );
			
			//	No method set? Look for methods named command<Command> to process request
			if ( null === $_sMethod && method_exists( $this, 'command' . ucwords( $_sCmd ) ) )
				$_sMethod = 'command' . ucwords( $_sCmd );

			//	Do we have a winner?
			if ( null !== $_sMethod )
			{
				//	Get any miscellaneous data into the appropriate array
				if ( count( $arData ) ) 
				{
					if ( $bPOSTSource ) 
						$_POST = array_merge( $_POST, $arData );
					else
						$_GET = array_merge( $_GET, $arData );
				}
				
				$_oResults = $this->{$_sMethod}();
			}
		}

		//	Return the results
		return $_oResults;
	}

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
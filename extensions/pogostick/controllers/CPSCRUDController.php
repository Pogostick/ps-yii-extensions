<?php
/**
 * This file is part of the psYiiExtensions package.
 *
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link      http://www.pogostick.com Pogostick, LLC.
 * @license   http://www.pogostick.com/licensing
 */

/**
 * CPSCRUDController provides standard filtered access to CRUD resources
 *
 * @package        psYiiExtensions
 * @subpackage     controllers
 *
 * @author         Jerry Ablan <jablan@pogostick.com>
 * @version        SVN: $Id: CPSCRUDController.php 400 2010-08-12 16:44:41Z jerryablan@gmail.com $
 * @since          v1.0.4
 *
 * @filesource
 */
abstract class CPSCRUDController extends CPSController
{
	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	/**
	 * If true, the admin system will not process commands, but render the admin page
	 * in a manner suitable for use with {@link CGridView}
	 *
	 * @var string
	 */
	protected $_enableAdminDashboard = false;

	public function getEnableAdminDashboard()
	{
		return $this->_enableAdminDashboard;
	}

	public function setEnableAdminDashboard( $value )
	{
		$this->_enableAdminDashboard = $value;
	}

	/**
	 * The name of the Login Form class. Defaults to 'LoginForm'
	 *
	 * @var string
	 */
	protected $_loginFormClass = null;

	public function getLoginFormClass()
	{
		return PS::nvl( $this->_loginFormClass, 'LoginForm' );
	}

	public function setLoginFormClass( $value )
	{
		$this->_loginFormClass = $value;
	}

	/***
	 * Mimic Gii's breadcrumbs property
	 *
	 * @var array
	 */
	protected $_breadcrumbs = array();

	public function getBreadcrumbs()
	{
		return $this->_breadcrumbs;
	}

	public function setBreadcrumbs( $value )
	{
		$this->_breadcrumbs = $value;
	}

	/***
	 * Mimic Gii's menu property
	 *
	 * @var array
	 */
	protected $_menu = array();

	public function getMenu()
	{
		return $this->_menu;
	}

	public function setMenu( $value )
	{
		$this->_menu = $value;
	}

	/**
	 * If true, only the 'update' view is called for create and update.
	 *
	 * @var boolean $singleViewMode
	 */
	protected $_singleViewMode = false;

	public function getSingleViewMode()
	{
		return $this->_singleViewMode;
	}

	public function setSingleViewMode( $value = true )
	{
		$this->_singleViewMode = $value;

		return $this;
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

		$this->defaultAction = 'admin';

		//	Add command mappings...
		$this->addCommandToMap( 'delete', array( $this, 'commandDelete' ) );
		$this->addCommandToMap( 'undelete', array( $this, 'commandUndelete' ) );

		//	Set our access rules..
		$this->addUserAction( self::ACCESS_TO_ALL, 'error' );
		$this->addUserActions( self::ACCESS_TO_GUEST, array( 'login', 'show', 'list', 'contact' ) );
		$this->addUserActions( self::ACCESS_TO_AUTH, array( 'login', 'logout', 'admin', 'create', 'delete', 'update', 'index', 'view' ) );
	}

	/**
	 * The filters for this controller
	 *
	 * @return array Action filters
	 */
	public function filters()
	{
		if ( $_SERVER['HTTP_HOST'] == 'localhost' )
		{
			return array();
		}

		//	Perform access control for CRUD operations
		return array(
			'accessControl',
		);
	}

	/**
	 * The base access rules for our CRUD controller
	 *
	 * @return array Access control rules
	 */
	public function accessRules()
	{
		static $_ruleList;
		static $_isInitialized;

		//	Console apps can bypass this...
		if ( PS::_a() instanceof CConsoleApplication )
		{
			return array();
		}

		//	Build access rule array...
		if ( !isset( $_isInitialized ) )
		{
			$_ruleList = array();

			for ( $_i = 0; $_i <= self::ACCESS_TO_NONE; $_i++ )
			{
				$_theVerb = $_validMatch = null;

				//	Get the user type
				switch ( $_i )
				{
					case self::ACCESS_TO_ALL:
					case self::ACCESS_TO_ANY:
					case self::ACCESS_TO_ANON:
						$_theVerb = 'allow';
						$_validMatch = '*';
						break;

					case self::ACCESS_TO_GUEST:
						$_theVerb = 'allow';
						$_validMatch = '?';
						break;

					case self::ACCESS_TO_AUTH:
						$_theVerb = 'allow';
						$_validMatch = '@';
						break;

					case self::ACCESS_TO_ADMIN:
						$_theVerb = 'allow';
						$_validMatch = 'admin';
						break;

					case self::ACCESS_TO_NONE:
						$_theVerb = 'deny';
						$_validMatch = '*';
						break;
				}

				//	Add to rules array
				if ( $_theVerb && $_validMatch )
				{
					$_tempList = array(
						$_theVerb,
						'actions' => PS::o( $this->m_arUserActionList, $_i ),
						'users'   => array( $_validMatch )
					);

					if ( $_tempList['actions'] == null )
					{
						unset( $_tempList['actions'] );
					}

					$_ruleList[] = $_tempList;
				}
			}

			$_isInitialized = true;
		}

		//	Return the rules...
		return $_ruleList;
	}

	//********************************************************************************
	//* Default actions
	//********************************************************************************

	/**
	 * Default login
	 */
	public function actionLogin()
	{
		if ( !\PS::_ig() )
		{
			$this->redirect( PS::_gu()->getReturnUrl() );

			return;
		}

		$_formClass = $this->getLoginFormClass();
		$_postClass = str_replace( '\\', '_', $_formClass );

		/** @var $_model \CFormModel */
		$_model = new $_formClass();

		if ( isset( $_POST[$_postClass] ) )
		{
			$_model->attributes = $_POST[$_postClass];

			//	Validate user input and redirect to previous page if valid
			if ( $_model->validate() )
			{
				$this->redirect( PS::_gu()->getReturnUrl() );

				return;
			}
		}

		//	Display the login form
		$this->render(
			'login',
			array(
				'form' => $_model
			)
		);
	}

	/**
	 * Logout the user
	 *
	 */
	public function actionLogout()
	{
		\PS::_gu()->logout();
		$this->redirect( PS::_gu()->loginUrl );
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'show' page.
	 *
	 * @param array If specified, also passed to the view.
	 */
	public function actionCreate( $options = array() )
	{
		$this->actionUpdate( $options, true );
	}

	/**
	 * Update the model
	 *
	 */
	public function actionUpdate( $options = array(), $fromCreate = false )
	{
		//	Handle singleViewMode...
		$_model = ( $fromCreate ? new $this->m_sModelName : $this->loadModel() );
		$_viewName = ( $fromCreate ? ( $this->_singleViewMode ? 'update' : 'create' ) : 'update' );

		if ( $this->isPostRequest )
		{
			$this->saveModel( $_model, $_POST, 'update' );
		}

		$options['update'] = ( !$fromCreate );
		$this->genericAction( $_viewName, $_model, $options );
	}

	/**
	 * View the model
	 *
	 */
	public function actionView( $options = array() )
	{
		$_model = $this->loadModel();
		$this->genericAction( 'view', $_model, $options );
	}

	/**
	 * Deletes a particular model.
	 * Only allowed via POST
	 *
	 * @throws CHttpException
	 */
	public function actionDelete( $sRedirectAction = 'admin' )
	{
		if ( Yii::app()->request->isPostRequest )
		{
			if ( $this->loadModel() )
			{
				$this->loadModel()->delete();
				$this->redirect( array( $sRedirectAction ) );
			}
		}
		else
		{
			if ( isset( $_GET['id'] ) )
			{
				$this->loadModel( $_GET['id'] )->delete();
				$this->redirect( array( $sRedirectAction ) );
			}
		}

		throw new CHttpException( 404, 'Invalid request. Please do not repeat this request again.' );
	}

	/**
	 * Manages all models.
	 */
	public function actionAdmin( $options = array(), $oCriteria = null )
	{
		if ( $this->_enableAdminDashboard )
		{
			$this->actionAdminDashboard( $options );

			return;
		}

		//	Regular old admin page...
		if ( $this->m_sModelName )
		{
			@list( $_arModels, $_oCrit, $_oPage, $_oSort ) = $this->loadPaged( true, $oCriteria );
		}

		$this->render( 'admin', array_merge( $options, array( 'models' => $_arModels, 'pages' => $_oPage, 'sort' => $_oSort ) ) );
	}

	/**
	 * Admin page for use with a {@link CGridView}
	 *
	 * @param array $options
	 *
	 * @return void
	 */
	public function actionAdminDashboard( $options = array() )
	{
		if ( null !== $this->_modelName )
		{
			$_model = new $this->_modelName( 'search' );
			$_model->unsetAttributes();

			if ( isset( $_REQUEST[$this->_modelName] ) )
			{
				$_model->attributes = $_REQUEST[$this->_modelName];
			}

			$this->render(
				'admin',
				array_merge(
					$options,
					array(
						'model' => $_model,
					)
				)
			);

			return;
		}

		throw new Exception( 'No model name/class set, unable to render "adminDashboard" page.' );
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
		$this->loadModel( $_POST['id'] )->delete();
		$this->refresh();
	}

	/**
	 * Undelete a model
	 *
	 */
	protected function commandUndelete()
	{
		$this->loadModel( $_POST['id'] )->delete( true );
		$this->refresh();
	}

}

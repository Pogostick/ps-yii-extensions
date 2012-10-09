<?php
/**
 * This file is part of the psYiiExtensions package.
 *
 * @copyright      Copyright (c) 2009-2011 Pogostick, LLC.
 * @link           http://www.pogostick.com Pogostick, LLC.
 * @license        http://www.pogostick.com/licensing
 * @package        psYiiExtensions
 * @subpackage     controllers
 *
 * @author         Jerry Ablan <jablan@pogostick.com>
 * @version        SVN: $Id: CPSController.php 405 2010-10-21 21:44:02Z jerryablan@gmail.com $
 * @since          v1.0.4
 *
 * @filesource
 */

/**
 * CPSController
 * A generic base class for controllers
 *
 * @throws CException|CHttpException
 *
 * @property boolean $isPostRequest
 * @property boolean $isAjaxRequest
 */
abstract class CPSController extends CController implements IPSBase
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
	const ACCESS_TO_ALL = 0;
	const ACCESS_TO_ANY = 0;
	const ACCESS_TO_ANON = 0;
	const ACCESS_TO_GUEST = 1;
	const ACCESS_TO_AUTH = 2;
	const ACCESS_TO_ADMIN = 3;
	const ACCESS_TO_SUPERADMIN = 5;

	//	Last...
	const ACCESS_TO_NONE = 6;

	/**
	 * The name of our command form field
	 */
	const COMMAND_FIELD_NAME = '__psCommand';

	/**
	 * Standard search text for rendering
	 */
	const SEARCH_HELP_TEXT = 'You may optionally enter a comparison operator (<strong>&lt;</strong>,
		<strong>&lt;=</strong>,
		<strong>&gt;</strong>, <strong>&gt;=</strong>, <strong>&lt;&gt;</strong>or <strong>=</strong>) at the beginning
		 of each search value to specify how the
		 comparison should be done.';

	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	/**
	 * @var int
	 */
	protected $_debugMode;

	/**
	 * @var array context menu items. This property will be assigned to {@link CMenu::items}.
	 */
	protected $_menu = array();

	/**
	 * @return array
	 */
	public function getMenu()
	{
		return $this->_menu;
	}

	/**
	 * @param $value
	 *
	 * @return \CPSController
	 */
	public function setMenu( $value )
	{
		$this->_menu = $value;

		return $this;
	}

	/**
	 * @var array the breadcrumbs of the current page. The value of this property will
	 * be assigned to {@link CBreadcrumbs::links}. Please refer to {@link CBreadcrumbs::links}
	 * for more details on how to specify this property.
	 */
	protected $_breadcrumbs = array();

	/**
	 * @return array
	 */
	public function getBreadcrumbs()
	{
		return $this->_breadcrumbs;
	}

	/**
	 * @param $value
	 *
	 * @return \CPSController
	 */
	public function setBreadcrumbs( $value )
	{
		$this->_breadcrumbs = $value;

		return $this;
	}

	/**
	 * An optional, additional page heading
	 *
	 * @var string
	 */
	protected $m_sPageHeading;

	/**
	 * @return string
	 */
	public function getPageHeading()
	{
		return $this->m_sPageHeading;
	}

	/**
	 * @param $sValue
	 *
	 * @return \CPSController
	 */
	public function setPageHeading( $sValue )
	{
		$this->m_sPageHeading = $sValue;

		return $this;
	}

	/***
	 * Allows you to change your action prefix
	 *
	 * @var string
	 */
	protected $m_sMethodPrefix = 'action';

	/**
	 * @return string
	 */
	public function getMethodPrefix()
	{
		return $this->m_sMethodPrefix;
	}

	/**
	 * @param $sValue
	 *
	 * @return \CPSController
	 */
	public function setMethodPrefix( $sValue )
	{
		$this->m_sMethodPrefix = $sValue;

		return $this;
	}

	/**
	 * @var CActiveRecord The currently loaded data model instance.
	 * @access protected
	 */
	protected $m_oModel = null;

	/**
	 * @return \CActiveRecord|null
	 */
	public function getModel()
	{
		return $this->m_oModel;
	}

	/**
	 * @param $oValue
	 *
	 * @return \CPSController
	 */
	protected function setModel( $oValue )
	{
		$this->m_oModel = $oValue;

		return $this;
	}

	/**
	 * @var string The name of the model for this controller
	 * @access protected
	 */
	protected $m_sModelName = null;
	protected $_modelName = null;

	/**
	 * @return null|string
	 */
	public function getModelName()
	{
		return $this->m_sModelName;
	}

	/**
	 * @param $value
	 *
	 * @return \CPSController
	 */
	protected function setModelName( $value )
	{
		$this->_modelName = $this->m_sModelName = $value;
		$this->m_sSearchStateId = 'PS_' . strtoupper( $value ) . '_SEARCH_CRIT';
		$this->m_arCurrentSearchCriteria = PS::_gs( $this->m_sSearchStateId );

		return $this;
	}

	/**
	 * Convenience access to isPostRequest
	 *
	 * @return boolean
	 */
	public function getIsPostRequest()
	{
		return PS::_gr()->getIsPostRequest();
	}

	/**
	 * Convenience access to isAjaxRequest
	 *
	 * @return boolean
	 */
	public function getIsAjaxRequest()
	{
		return PS::_gr()->getIsAjaxRequest();
	}

	/**
	 * Returns the base url of the current app
	 *
	 * @return string
	 */
	public function getAppBaseUrl()
	{
		return PS::_gbu();
	}

	/**
	 * The id in the state of our current filter/search criteria
	 *
	 * @var string
	 */
	protected $m_sSearchStateId = null;

	/**
	 * Stores the current search criteria
	 *
	 * @var array
	 */
	protected $m_arCurrentSearchCriteria = null;

	/**
	 * @return array|null
	 */
	public function getSearchCriteria()
	{
		return $this->m_arCurrentSearchCriteria;
	}

	/**
	 * @param $arValue
	 *
	 * @return \CPSController
	 */
	public function setSearchCriteria( $arValue )
	{
		$this->m_arCurrentSearchCriteria = $arValue;
		PS::_ss( $this->m_sSearchStateId, $arValue );

		return $this;
	}

	/**
	 * @var string the default layout for the controller view. Defaults to 'application.views.layouts.column1',
	 * meaning using a single column layout. See 'protected/views/layouts/column1.php'.
	 */
	public $_pageLayout = 'main';

	/**
	 * @return mixed #P#C\CPSController.layout|? #P#C\CPSController.layout|?
	 */
	public function getPageLayout()
	{
		return $this->_pageLayout = $this->layout;
	}

	/**
	 * @param $value
	 *
	 * @return \CPSController
	 */
	public function setPageLayout( $value )
	{
		$this->_pageLayout = $this->layout = $value;

		return $this;
	}

	/**
	 * @var string the layout of the content portion of this controller. If specified,
	 * content is passed through this layout before it is sent to your main page layout.
	 */
	protected $_contentLayout = null;

	/**
	 * @return null|string
	 */
	public function getContentLayout()
	{
		return $this->_contentLayout;
	}

	/**
	 * @param $value
	 *
	 * @return \CPSController
	 */
	public function setContentLayout( $value )
	{
		$this->_contentLayout = $value;

		return $this;
	}

	/**
	 * @var boolean Try to find proper layout to use
	 * @access protected
	 */
	protected $m_bAutoLayout = true;

	/**
	 * @return bool
	 */
	public function getAutoLayout()
	{
		return $this->m_bAutoLayout;
	}

	/**
	 * @param $bValue
	 *
	 * @return \CPSController
	 */
	public function setAutoLayout( $bValue )
	{
		$this->m_bAutoLayout = $bValue;

		return $this;
	}

	/**
	 * @var boolean Try to find missing action
	 * @access protected
	 */
	protected $m_bAutoMissing = true;

	/**
	 * @return bool
	 */
	public function getAutoMissing()
	{
		return $this->m_bAutoMissing;
	}

	/**
	 * @param $value
	 *
	 * @return \CPSController
	 */
	public function setAutoMissing( $value )
	{
		$this->m_bAutoMissing = $value;

		return $this;
	}

	/**
	 * @var array An associative array of POST commands and their applicable methods
	 * @access protected
	 */
	protected $m_arCommandMap = array();

	/**
	 * @return array
	 */
	public function getCommandMap()
	{
		return $this->m_arCommandMap;
	}

	/**
	 * @param $oValue
	 *
	 * @return \CPSController
	 */
	public function setCommandMap( $oValue )
	{
		$this->m_arCommandMap = $oValue;

		return $this;
	}

	/**
	 * @param      $sKey
	 * @param null $oValue
	 * @param null $eWhich
	 *
	 * @return \CPSController
	 */
	public function addCommandToMap( $sKey, $oValue = null, $eWhich = null )
	{
		$this->m_arCommandMap[$sKey] = $oValue;
		if ( $eWhich )
		{
			$this->addUserActions( $eWhich, array( $sKey ) );
		}

		return $this;
	}

	/**
	 * Action queue for keeping track of where we are...
	 *
	 * @var array
	 */
	protected $m_arActionQueue = array();

	/**
	 * A list of actions registered by our portlets
	 *
	 * @var array
	 */
	protected $m_arPortletActions = array();

	/**
	 * @return array
	 */
	public function getPortletActions()
	{
		return $this->m_arPortletActions;
	}

	/**
	 * @param $arValue
	 *
	 * @return \CPSController
	 */
	public function setPortletActions( $arValue )
	{
		$this->m_arPortletActions = $arValue;

		return $this;
	}

	/**
	 * @param $sName
	 * @param $arCallback
	 *
	 * @return \CPSController
	 */
	public function addPortletAction( $sName, $arCallback )
	{
		$this->m_arPortletActions[$sName] = $arCallback;

		return $this;
	}

	/**
	 * @var array An array of actions permitted by any user
	 * @access protected
	 */
	protected $m_arUserActionList = array();

	/**
	 * @return \CPSController
	 */
	protected function resetUserActionList()
	{
		$this->m_arUserActionList = array();
		$this->addUserAction( self::ACCESS_TO_ANY, 'error' );

		return $this;
	}

	/**
	 * @param $eWhich
	 * @param $arValue
	 *
	 * @return \CPSController
	 */
	protected function setUserActionList( $eWhich, $arValue )
	{
		$this->m_arUserActionList[$eWhich] = null;
		$this->addUserActions( $eWhich,
			$arValue );

		return $this;
	}

	/**
	 * @param $eWhich
	 *
	 * @return mixed #M#C\PS.o|? #M#C\PS.o|?
	 */
	public function getUserActionList( $eWhich )
	{
		return PS::o( $this->m_arUserActionList, $eWhich );
	}

	/**
	 * @param $eWhich
	 * @param $roleName
	 * @param $action
	 *
	 * @return \CPSController
	 */
	public function addUserActionRole( $eWhich, $roleName, $action )
	{
		$this->m_arUserActionList[$eWhich]['roles'][$roleName] = $action;

		return $this;
	}

	/**
	 * @param $eWhich
	 * @param $sAction
	 *
	 * @return \CPSController
	 */
	public function removeUserAction( $eWhich, $sAction )
	{
		if ( !isset( $this->m_arUserActionList[$eWhich] ) || !is_array( $this->m_arUserActionList[$eWhich] ) )
		{
			return;
		}

		if ( in_array( $sAction, $this->m_arUserActionList[$eWhich] ) )
		{
			unset( $this->m_arUserActionList[$eWhich][$sAction] );
		}

		return $this;
	}

	/**
	 * @param $eWhich
	 * @param $sAction
	 *
	 * @return \CPSController
	 */
	public function addUserAction( $eWhich, $sAction )
	{
		if ( !isset( $this->m_arUserActionList[$eWhich] ) || !is_array( $this->m_arUserActionList[$eWhich] ) )
		{
			$this->m_arUserActionList[$eWhich] = array();
		}

		if ( !in_array( $sAction, $this->m_arUserActionList[$eWhich] ) )
		{
			$this->m_arUserActionList[$eWhich][] = $sAction;
		}

		//	Make sure we don't lose our error handler...
		if ( $eWhich == self::ACCESS_TO_ANY )
		{
			if ( !in_array( 'error', $this->m_arUserActionList[$eWhich] ) )
			{
				$this->addUserAction( self::ACCESS_TO_ANY, 'error' );
			}
		}

		return $this;
	}

	/**
	 * @param       $eWhich
	 * @param array $arActions
	 *
	 * @return \CPSController
	 */
	public function addUserActions( $eWhich, $arActions = array() )
	{
		if ( !is_array( PS::o( $this->m_arUserActionList, $eWhich ) ) )
		{
			$this->m_arUserActionList[$eWhich] = array();
		}

		foreach ( $arActions as $_sAction )
		{
			$this->addUserAction( $eWhich, $_sAction );
		}

		return $this;
	}

	protected $_displayName;

	/**
	 * @param $value
	 *
	 * @return \CPSController
	 */
	protected function setDisplayName( $value )
	{
		$this->_displayName = $value;

		return $this;
	}

	/**
	 */
	protected function getDisplayName()
	{
		return $this->_displayName;
	}

	protected $_cleanTrail;

	/**
	 */
	protected function getCleanTrail()
	{
		return $this->_cleanTrail;
	}

	/**
	 * @param $value
	 *
	 * @return \CPSController
	 */
	protected function setCleanTrail( $value )
	{
		$this->_cleanTrail = $value;

		return $this;
	}

	/**
	 * @var array $viewData The array of data passed to views
	 */
	protected $_viewData = array();

	/**
	 * @return array
	 */
	protected function getViewData()
	{
		return $this->_viewData;
	}

	/**
	 * @param $value
	 *
	 * @return \CPSController
	 */
	protected function setViewData( $value )
	{
		$this->_viewData = $value;

		return $this;
	}

	/**
	 * @var array Any values in this array will be extracted into each view before it's rendered. The value "currentUser" is added automatically.
	 */
	protected $_extraViewDataList;

	/**
	 * @return array
	 */
	protected function getExtraViewDataList()
	{
		return $this->_extraViewDataList;
	}

	/**
	 * @param $value
	 *
	 * @return \CPSController
	 */
	protected function setExtraViewDataList( $value )
	{
		$this->_extraViewDataList = $value;

		return $this;
	}

	/**
	 * @var string The prefix to prepend to variables extracted into the view from {@link $_extraViewDataList}. Defaults to '_' (single underscore).
	 */
	protected $_extraViewDataPrefix = '_';

	/**
	 * @return string
	 */
	protected function getExtraViewDataPrefix()
	{
		return $this->_extraViewDataPrefix;
	}

	/**
	 * @param $value
	 *
	 * @return \CPSController
	 */
	protected function setExtraViewDataPrefix( $value )
	{
		$this->_extraViewDataPrefix = $value;

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

		//	Find layout...
		if ( PHP_SAPI != 'cli' && $this->m_bAutoLayout && !Yii::app() instanceof CConsoleApplication )
		{
			if ( file_exists( Yii::app()->getBasePath() . '/views/layouts/' . $this->getId() . '.php' ) )
			{
				$this->_pageLayout = $this->getId();
			}
		}

		//	Allow errors
		$this->addUserAction( self::ACCESS_TO_ANY, 'error' );

		//	Pull any search criteria we've stored...
		if ( $this->getModelName() )
		{
			$this->m_arCurrentSearchCriteria = PS::_gs( $this->m_sSearchStateId );
		}

		//	Ensure conformity
		if ( !is_array( $this->_extraViewDataList ) )
		{
			$this->_extraViewDataList = array();
		}

		//	Add "currentUser" value to extra view data
		if ( null === PS::o( $this->_extraViewDataList, $this->_extraViewDataPrefix . 'currentUser' ) )
		{
			$this->_extraViewDataList[$this->_extraViewDataPrefix . 'currentUser'] = PS::_gcu();
		}

		//	And some defaults...
		$this->_cleanTrail = $this->_displayName;
		$this->defaultAction = 'index';
	}

	/**
	 * How about a default action that displays static pages? Huh? Huh?
	 *
	 * In your configuration file, configure the urlManager as follows:
	 *
	 *    'urlManager' => array(
	 *        'urlFormat' => 'path',
	 *        'showScriptName' => false,
	 *        'rules' => array(
	 *            ... all your rules should be first ...
	 *            //    Add this as the last line in your rules.
	 *            '<view:\w+>' => 'default/_static',
	 *        ),
	 *
	 * The above assumes your default controller is DefaultController. If is different
	 * simply change the route above (default/_static) to your default route.
	 *
	 * Finally, create a directory under your default controller's view path:
	 *
	 *        /path/to/your/app/protected/views/default/_static
	 *
	 * Place your static files in there, for example:
	 *
	 *        /path/to/your/app/protected/views/default/_static/aboutUs.php
	 *        /path/to/your/app/protected/views/default/_static/contactUs.php
	 *        /path/to/your/app/protected/views/default/_static/help.php
	 *
	 * @return array
	 */
	public function actions()
	{
		return array_merge(
			array(
				'_static' => array(
					'class'    => 'CViewAction',
					'basePath' => '_static',
				),
			),
			parent::actions()
		);
	}

	/**
	 * A generic action that renders a page and passes in the model
	 *
	 * @param        $sActionId
	 * @param null   $oModel
	 * @param array  $arExtraParams
	 * @param string $sModelVarName
	 * @param null   $sFlashKey
	 * @param null   $sFlashValue
	 * @param null   $sFlashDefaultValue
	 *
	 * @internal param \The $string action id
	 *
	 * @internal param \The $CModel model
	 *
	 * @internal param \Extra $array parameters to pass to the view
	 *
	 * @internal param \The $string name of the variable to pass to the view. Defaults to 'model'
	 */
	public function genericAction( $sActionId, $oModel = null, $arExtraParams = array(), $sModelVarName = 'model', $sFlashKey = null,
								   $sFlashValue = null, $sFlashDefaultValue = null )
	{
		if ( $sFlashKey )
		{
			PS::_sf( $sFlashKey, $sFlashValue, $sFlashDefaultValue );
		}
		$this->render( $sActionId,
			array_merge( $arExtraParams, array( $sModelVarName => ( $oModel ) ? $oModel : $this->loadModel() ) ) );
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 *
	 * @param null $iId
	 *
	 * @return \CActiveRecord|null
	 *
	 * @internal param \the $integer primary key value. Defaults to null, meaning using the 'id' GET variable
	 * @throws CHttpException
	 */
	public function loadModel( $iId = null )
	{
		if ( null === $this->m_oModel )
		{
			$_iId = PS::o( $_GET, 'id', $iId );
			$this->m_oModel = $this->load( $_iId );

			//	No data? bug out
			if ( null === $this->m_oModel )
			{
				$this->redirect( array( $this->defaultAction ) );
			}

			//	Get the name of this model...
			$this->setModelName( get_class( $this->m_oModel ) );
		}

		//	Return our model...
		return $this->m_oModel;
	}

	/**
	 * Provide automatic missing action mapping...
	 * Also handles a theme change request from any portlets
	 *
	 * @param string $sActionId
	 */
	public function missingAction( $sActionId = null )
	{
		if ( $this->m_bAutoMissing )
		{
			if ( empty( $sActionId ) )
			{
				$sActionId = $this->defaultAction;
			}

			if ( $this->getViewFile( $sActionId ) )
			{
				$this->render( $sActionId );

				return;
			}
		}

		parent::missingAction( $sActionId );
	}

	/**
	 * Our error handler...
	 */
	public function actionError()
	{
		if ( null === ( $_error = PS::_ge() ) )
		{
			if ( !$this->getIsAjaxRequest() )
			{
				echo $_error['message'];
			}
		}

		if ( !$this->hasView( 'error' ) )
		{
			throw new CHttpException( 404, 'Page not found.' );
		}

		$this->render( 'error', array( 'error' => $_error ) );
	}

	/**
	 * Convenience access to Yii request
	 *
	 * @return CHttpRequest
	 */
	public function getRequest()
	{
		return PS::_gr();
	}

	/**
	 * See if there are any commands that need processing
	 *
	 * @param CAction $oAction
	 *
	 * @return boolean
	 */
	public function beforeAction( $oAction )
	{
		//	If we have commands, give it a shot...
		if ( count( $this->m_arCommandMap ) && parent::beforeAction( $oAction ) )
		{
			$this->processCommand();
		}

		return true;
	}

	/** {@InheritDoc} */
	public function render( $view, $data = null, $return = false, $layoutData = null )
	{
		//	Allow passing second array in third parameter
		if ( !is_bool( $return ) )
		{
			$_return = $layoutData ? : false;
			$layoutData = $return;
			$return = $_return;
		}

		if ( $this->beforeRender( $view ) )
		{
			$_output = $this->renderPartial( $view, $data, true );

			if ( ( $layoutFile = $this->getLayoutFile( $this->layout ) ) !== false )
			{
				if ( !is_array( $layoutData ) )
				{
					$layoutData = array();
				}

				$_layoutData = array_merge(
					array(
						'content'=> $_output
					),
					$layoutData
				);

				$_output = $this->renderFile( $layoutFile, $_layoutData, true );
			}

			$this->afterRender( $view, $_output );

			$_output = $this->processOutput( $_output );

			if ( $return )
			{
				return $_output;
			}

			echo $_output;
		}
	}

	/**
	 * Renders a view with a layout.
	 *
	 * This method first calls {@link renderPartial} to render the view (called content view).
	 * It then renders the layout view which may embed the content view at appropriate place.
	 * In the layout view, the content view rendering result can be accessed via variable
	 * <code>$content</code>. At the end, it calls {@link processOutput} to insert scripts
	 * and dynamic contents if they are available.
	 *
	 * By default, the layout view script is "protected/views/layouts/main.php".
	 * This may be customized by changing {@link layout}.
	 *
	 * @param      $viewName
	 * @param null $viewData
	 * @param bool $returnString
	 *
	 * @internal param \name $string of the view to be rendered. See {@link getViewFile} for details
	 * about how the view script is resolved.
	 *
	 * @internal param \data $array to be extracted into PHP variables and made available to the view script
	 *
	 * @internal param \whether $boolean the rendering result should be returned instead of being displayed to end users.
	 * @return string the rendering result. Null if the rendering result is not required.
	 * @see      renderPartial
	 * @see      getLayoutFile
	 */
	public function newRender( $viewName, $viewData = null, $returnString = false )
	{
		//	make sure we're all on the same page...
		$this->_pageLayout = $this->layout;

		$_output = $this->renderPartial( $viewName, $viewData, true );

		if ( $this->_pageLayout && false !== ( $_layoutFile = $this->getLayoutFile( $this->_pageLayout ) ) )
		{
			//	Process content layout if required
			if ( $this->_contentLayout && false !== (
			$_contentLayoutFile = $this->getLayoutFile( $this->_contentLayout ) )
			)
			{
				$_output = $this->renderPartial( $_contentLayoutFile, $viewData, true );
			}

			$_output = $this->renderFile( $_layoutFile, array( 'content' => $_output ), true );
			$_output = $this->processOutput( $_output );
		}

		if ( $returnString )
		{
			return $_output;
		}

		echo $_output;
	}

	/**
	 * Renders a view.
	 *
	 * The named view refers to a PHP script (resolved via {@link getViewFile})
	 * that is included by this method. If $data is an associative array,
	 * it will be extracted as PHP variables and made available to the script.
	 *
	 * This method differs from {@link render()} in that it does not
	 * apply a layout to the rendered result. It is thus mostly used
	 * in rendering a partial view, or an AJAX response.
	 *
	 * This override adds the current user to the data automatically in the $_currentUser variable
	 *
	 * @param      $view
	 * @param null $data
	 * @param bool $return
	 * @param bool $processOutput
	 *
	 * @internal param \name $string of the view to be rendered. See {@link getViewFile} for details
	 * about how the view script is resolved.
	 *
	 * @internal param \data $array to be extracted into PHP variables and made available to the view script
	 *
	 * @internal param \whether $boolean the rendering result should be returned instead of being displayed to end users
	 *
	 * @internal param \whether $boolean the rendering result should be postprocessed using {@link processOutput}.
	 * @return string the rendering result. Null if the rendering result is not required.
	 * @throws CException if the view does not exist
	 * @see      getViewFile
	 * @see      processOutput
	 * @see      render
	 */
	public function renderPartial( $view, $data = null, $return = false, $processOutput = false )
	{
		if ( false === ( $_viewFile = $this->getViewFile( $view ) ) )
		{
			throw new CException(
				Yii::t(
					'yii',
					'{controller} cannot find the requested view "{view}".',
					array(
						'{controller}' => get_class( $this ),
						'{view}'       => $view
					)
				)
			);
		}

		if ( null === $data )
		{
			$data = array();
		}

		$_output = $this->renderFile(
			$_viewFile,
			array_merge(
				$this->_extraViewDataList,
				$this->_viewData,
				$data
			),
			true
		);

		if ( $processOutput )
		{
			$_output = $this->processOutput( $_output );
		}

		if ( $return )
		{
			return $_output;
		}

		echo $_output;
	}

	/**
	 * Creates a standard form options array and loads page niceties
	 *
	 * @param CModel $model
	 * @param array  $options
	 *
	 * @internal param array|string $If a string is passed in, it is used as the title.
	 * @return array
	 */
	public function setStandardFormOptions( $model, $options = array() )
	{
		$_title = null;

		//	Shortcut... only passed in the title...
		if ( !empty( $options ) && is_string( $options ) )
		{
			$_title = $options;

			$options = array(
				'title'       => PS::_gan() . ' :: ' . $_title,
				'breadcrumbs' => array( $_title ),
			);
		}

		//	Abbreviated arguments?
		if ( is_array( $model ) && array() === $options )
		{
			$options = $model;
			$model = PS::o( $options, 'model' );
		}

		$_formId = PS::o( $options, 'id', 'ps-edit-form' );

		//	Put a cool flash span on the page
		if ( PS::o( $options, 'enableFlash', true, true ) )
		{
			$_flashClass = PS::o( $options, 'flashSuccessClass', 'operation-result-success' );
			$_flashTitle = 'Success!';

			if ( null === ( $_message = PS::_gf( 'success' ) ) )
			{
				if ( null !== ( $_message = PS::_gf( 'failure' ) ) )
				{
					$_flashTitle = 'There was a problem...';
					$_flashClass = PS::o( $options, 'flashFailureClass', 'operation-result-failure' );
				}
			}

			$_flashText = $_message;

			$_spanId = PS::o( $options, 'flashSpanId', 'operation-result', true );
			PS::_ss( 'psForm-flash-html',
				PS::tag( 'span',
					array(
						'id'    => $_spanId,
						'class' => $_flashClass
					),
					$_message ) );

			//	Register a nice little fader...
			if ( $_flashText )
			{
				$_fader = <<<SCRIPT
notify('default',{title:'{$_flashTitle}',text:'{$_flashText}'});
//
//$('span.{$_flashClass}').fadeIn('500',function(){
//	$(this).delay(3000).fadeOut(3500);
//});
SCRIPT;
				PS::_rs( $_formId . '.' . $_spanId . '.fader', $_fader, CClientScript::POS_READY );
			}
		}

		//	Set the standard nav options
		if ( false !== PS::o( $options, 'viewNavigation', true ) )
		{
			$this->setViewNavigationOptions( $options );
		}

		$_formOptions = array(
			'id'                  => $_formId,
			'showLegend'          => PS::o( $options, 'showLegend', true ),
			'showDates'           => PS::o( $options, 'showDates', false ),
			'method'              => PS::o( $options, 'method', 'POST' ),
			'uiStyle'             => PS::o( $options, 'uiStyle', PS::UI_JQUERY ),
			'formClass'           => PS::o( $options, 'formClass', 'form' ),
			'formModel'           => $model,
			'errorCss'            => PS::o( $options, 'errorCss', 'error' ),
			//	We want error summary...
			'errorSummary'        => PS::o( $options, 'errorSummary', true ),
			'errorSummaryOptions' => array(
				'header' => '<p class="ps-error-summary">The following problems occurred:</p>',
			),
			'validate'            => PS::o( $options, 'validate', true ),
			'validateOptions'     => PS::o( $options,
				'validateOptions',
				array(
					'ignoreTitle' => true,
					'errorClass'  => 'ps-validate-error',
				)
			),
		);

		PS::setFormFieldContainerClass( PS::o( $options, 'rowClass', ( PS::UI_BOOTSTRAP == $_formOptions['uiStyle'] ? 'control-group' : 'row' ) ) );

		$_crumbs = $this->_breadcrumbs ? : PS::o( $options, 'breadcrumbs', null, true );

		PS::setUiStyle( $_formOptions['uiStyle'] );

		if ( !empty( $_crumbs ) && PS::UI_BOOTSTRAP == $_formOptions['uiStyle'] )
		{
			$_trail = '<ul class="breadcrumb">';

			foreach ( $_crumbs as $_name => $_link )
			{
				if ( false !== $_link )
				{
					$_trail .= '<li><a href="' . ( '/' != $_link ? PS::_cu( $_link ) :
						$_link ) . '">' . $_name . '</a> <span class="divider">/</span></li>';
				}
				else
				{
					$_trail .= '<li class="active">' . $_name . '</li>';
				}
			}

			echo $_trail . '</ul>';
		}

		if ( null !== ( $_header = PS::o( $options, 'header' ) ) )
		{
			echo $_header;
		}

		if ( null !== ( $_subHeader = PS::o( $options, 'subHeader' ) ) )
		{
			echo $_subHeader;
		}

		if ( false !== PS::o( $options, 'renderSearch', false ) )
		{
			echo PS::tag( 'p', array(), self::SEARCH_HELP_TEXT );
			echo PS::link( 'Advanced Search', '#', array( 'class' => 'search-button' ) );

			echo PS::tag(
				'div',
				array( 'class' => 'search-form' ),
				$this->renderPartial( '_search', array( 'model' => $model ), true )
			);

			//	Register the search script, if any
			if ( null !== ( $_searchScript = PS::o( $options, '__searchScript' ) ) )
			{
				PS::_rs( 'search', $_searchScript );
			}
		}

		if ( PS::UI_JQUERY == PS::o( $options, 'uiStyle', PS::UI_JQUERY ) )
		{
			CPSjqUIWrapper::loadScripts();
		}

		if ( PS::o( $options, 'validate', true ) )
		{
			CPSjqValidate::loadScripts();
		}

		return $_formOptions;
	}

	/**
	 * Sets the content type for this page to the specified MIME type
	 *
	 * @param         $contentType
	 * @param boolean $noLayout If true, the layout for this page is set to false
	 *
	 * @internal param $ <type> $contentType The MIME type to set
	 *
	 */
	public function setContentType( $contentType, $noLayout = true )
	{
		if ( $noLayout )
		{
			$this->layout = false;
		}
		header( 'Content-Type: ' . $contentType );
	}

	/**
	 * Sets the standard page navigation options (title, crumbs, menu, etc.)
	 *
	 * @param array $options
	 *
	 * @return array
	 */
	public function setViewNavigationOptions( &$options = array() )
	{
		//	Page title
		$_title = PS::o( $options, 'title', null, true );
		$_subtitle = PS::o( $options, 'subtitle', null, true );

		//	Do some auto-page-setup...
		if ( false !== ( $_header = PS::o( $options, 'header' ) ) )
		{
			if ( null === $_header )
			{
				$_header = $_title;
			}

			if ( !empty( $_header ) )
			{
				if ( null !== ( $_headerIcon = PS::o( $options, 'headerIcon', null, true ) ) )
				{
					$_header =
						PS::tag( 'div',
							array( 'class' => 'ps-form-header-title' ),
							PS::tag( 'span',
								array( 'class' => 'ps-form-header-icon' ),
								PS::image( $_headerIcon ) ) .
								PS::tag( 'span',
									array( 'class' => 'ps-form-header-text' ),
									$_header ) .
								PS::tag( 'div',
									array( 'class' => 'ps-form-header-message' ),
									PS::_gs( 'psForm-flash-html' ) )
						);
				}

				$options['header'] = PS::tag( 'h1', array( 'class' => 'ui-generated-header' ), $_header );

				//	Do some auto-page-setup...
				$_subHeader = PS::o( $options, 'subHeader' );

				if ( !empty( $_subHeader ) )
				{
					$options['subHeader'] =
						PS::tag( 'div', array( 'class' => 'ui-state-active ui-generated-subheader' ), $_subHeader );
				}

				//	Generate subtitle from header...
				if ( null === $_title && null === $_subtitle && null !== $_header )
				{
					$_subtitle = $_header;
				}

				if ( null === $_subtitle )
				{
					$_title = PS::_gan() . ' :: ' . $_subtitle;
				}

				if ( null === $_title )
				{
					$_title = PS::_gan();
				}

				$this->setPageTitle( $options['title'] = $_title );
			}
		}

		//	Set crumbs
		$this->_breadcrumbs = PS::o( $options, 'breadcrumbs' );

		//	Let side menu be set from here as well...
		if ( null !== ( $_menuItems = PS::o( $options, 'menu', null ) ) )
		{
			//	Rebuild menu items if not in standard format
			$_finalMenu = array();

			foreach ( $_menuItems as $_itemLabel => $_itemLink )
			{
				if ( null === ( $_label = PS::o( $_itemLink, 'label', null, true ) ) )
				{
					$_label = $_itemLabel;
				}

				if ( null === ( $_link = PS::o( $_itemLink, 'link', null, true ) ) )
				{
					$_link = $_itemLink;
				}

				$_finalMenu[] = array(
					'label' => $_label,
					'url'   => $_link,
				);
			}

			$options['menu'] = $this->_menu = $_finalMenu;
		}

		$_enableSearch = ( PS::o( $options, 'enableSearch', false ) || PS::o( $options, 'renderSearch', false ) );

		//	Drop the search script on the page if enabled...
		if ( false !== $_enableSearch )
		{
			$_searchSelector = PS::o( $options, 'searchSelector', '.search-button' );
			$_toggleSpeed = PS::o( $options, 'toggleSpeed', 'fast' );
			$_searchForm = PS::o( $options, 'searchForm', '.search-form' );
			$_targetFormId = PS::o( $options, 'id', 'ps-edit-form' );

			$_searchScript = <<<JS
$(function(){
	$('{$_searchSelector}').click(function(){
		$('{$_searchForm}').slideToggle('{$_toggleSpeed}');
		return false;
	});

	$('{$_searchForm} form').submit(function(){
		$.fn.yiiGridView.update('{$_targetFormId}', {
			data: $(this).serialize()
		});
		return false;
	});
});
JS;
			$options['__searchScript'] = $_searchScript;
		}

		//	Return reconstructed options for standard form use
		return $options;
	}

	/**
	 * Checks to see if a view is available
	 *
	 * @param string $viewName
	 *
	 * @return bool
	 */
	public function hasView( $viewName )
	{
		return ( false !== $this->getViewFile( $viewName ) );
	}

	//********************************************************************************
	//* Private Methods
	//********************************************************************************

	/**
	 * Executes any commands
	 * Maps to {@link CPSController::commandMap} and calls the appropriate method.
	 *
	 * @param array  $arData
	 * @param string $sIndexName
	 *
	 * @return mixed
	 */
	protected function processCommand( $arData = array(), $sIndexName = self::COMMAND_FIELD_NAME )
	{
		//	Our return variable
		$_oResults = null;

		//	Get command's method...
		$_sCmd = PS::o( $_REQUEST, $sIndexName );

		//	Do we have a command mapping?
		if ( null !== ( $_arCmd = PS::o( $this->m_arCommandMap, $_sCmd ) ) )
		{
			//	Get any miscellaneous data into the appropriate array
			if ( count( $arData ) )
			{
				if ( $this->getIsPostRequest() )
				{
					$_POST = array_merge( $_POST, $arData );
				}
				else
				{
					$_GET = array_merge( $_GET, $arData );
				}
			}

			$_oResults = call_user_func( $_arCmd[1] );
		}

		//	Return the results
		return $_oResults;
	}

	/**
	 * Saves the data in the model
	 *
	 * @param CModel  $oModel          The model to save
	 * @param array   $arData          The array of data to merge with the model
	 * @param string  $sRedirectAction Where to redirect after a successful save
	 * @param boolean $bAttributesSet  If true, attributes will not be set from $arData
	 * @param string  $sModelName      Optional model name
	 * @param string  $sSuccessMessage Flash message to set if successful
	 * @param boolean $bNoCommit       If true, transaction will not be committed
	 * @param bool    $bSafeOnly
	 *
	 * @return boolean
	 */
	protected function saveModel( &$oModel, $arData = array(), $sRedirectAction = 'show', $bAttributesSet = false, $sModelName = null,
								  $sSuccessMessage = null, $bNoCommit = false, $bSafeOnly = false )
	{
		$_sMessage = PS::nvl( $sSuccessMessage, 'Your changes have been saved.' );
		$_sModelName = PS::nvl( $sModelName, PS::nvl( $oModel->getModelClass(), $this->m_sModelName ) );

		if ( isset( $arData, $arData[$_sModelName] ) )
		{
			if ( !$bAttributesSet )
			{
				$oModel->setAttributes( $arData[$_sModelName], $bSafeOnly );
			}

			if ( $oModel->save() )
			{
				if ( !$bNoCommit && $oModel instanceof CPSModel && $oModel->hasTransaction() )
				{
					$oModel->commitTransaction();
				}

				PS::_sf( 'success', $_sMessage );

				if ( $sRedirectAction )
				{
					$this->redirect( array(
						$sRedirectAction,
						'id' => $oModel->id
					) );
				}

				return true;
			}
		}

		return false;
	}

	/***
	 * Just like saveModel, but doesn't commit, and never redirects.
	 *
	 * @param CPSModel $oModel
	 * @param array    $arData
	 * @param boolean  $bAttributesSet
	 * @param string   $sSuccessMessage
	 *
	 * @return boolean
	 * @see saveModel
	 */
	protected function saveTransactionModel( &$oModel, $arData = array(), $bAttributesSet = false, $sSuccessMessage = null )
	{
		return $this->saveModel( $oModel, $arData, false, $bAttributesSet, null, $sSuccessMessage, true );
	}

	/**
	 * Loads a page of models
	 *
	 * @param bool $bSort
	 * @param null $oCriteria
	 *
	 * @internal param \Whether $boolean or not to apply a sort. Defaults to false
	 *
	 * @return array Element 0 is the results of the find. Element 1 is the pagination object
	 */
	protected function loadPaged( $bSort = false, $oCriteria = null )
	{
		$_oSort = $_oCrit = $_oPage = null;

		//	Make criteria
		$_oCrit = PS::nvl( $oCriteria, new CDbCriteria() );
		$_oPage = new CPagination( $this->loadCount( $_oCrit ) );
		$_oPage->pageSize = PS::o( $_REQUEST, 'perPage', self::PAGE_SIZE );
		if ( isset( $_REQUEST, $_REQUEST['page'] ) )
		{
			$_oPage->setCurrentPage( intval( $_REQUEST['page'] ) - 1 );
		}
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
	 * @param       $sMethod
	 * @param null  $oCrit
	 * @param array $arScope
	 * @param array $arOptions
	 *
	 * @internal param \The $string method to append
	 *
	 * @internal param \The $CDbCriteria criteria for the lookup
	 *
	 * @internal param \Scopes $array to apply to this request
	 *
	 * @internal param \Options $array for the data load
	 * @return CActiveRecord|array
	 */
	protected function genericModelLoad( $sMethod, &$oCrit = null, $arScope = array(), $arOptions = array() )
	{
		$_sMethod = $this->getModelLoadString( $arScope, $arOptions ) . $sMethod;

		return eval( "return (" . $_sMethod . ");" );
	}

	/**
	 * This method reads the data from the database and returns the row.
	 * Must override in subclasses.
	 *
	 * @var integer $iId The primary key to look up
	 * @return CActiveRecord
	 */
	protected function load( $iId = null )
	{
		return $this->genericModelLoad( 'findByPk(' . $iId . ')' );
	}

	/**
	 * Loads all data using supplied criteria
	 *
	 * @param CDbCriteria $oCrit
	 *
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
	 *
	 * @return integer The number of rows
	 */
	protected function loadCount( &$oCrit = null )
	{
		$_sCrit = ( $oCrit ) ? '$oCrit' : null;

		return $this->genericModelLoad( 'count(' . $_sCrit . ')', $oCrit );
	}

	/**
	 * Builds a string suitable for {@link eval}. The verb is intentionally not appeneded.
	 *
	 * @param array $arScope
	 * @param array $arOptions
	 *
	 * @return string
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
	 *
	 * @return CAction
	 */
	protected function popAction()
	{
		return array_pop( $this->m_arActionQueue );
	}

	/**
	 * Pushes a variable onto the view data stack
	 *
	 * @param string $variableName
	 * @param mixed  $variableData
	 */
	protected function addViewData( $variableName, $variableData = null )
	{
		$this->_viewData[$variableName] = $variableData;
	}

	/**
	 * Clears the current search criteria
	 *
	 * @return null
	 */
	protected function clearSearchCriteria()
	{
		$this->m_arCurrentSearchCriteria = null;
		Yii::app()->user->clearState( $this->m_sSearchStateId );

		return null;
	}

	/**
	 * Turns off the layout, echos the JSON encoded version of data and returns. Optionally encoding HTML characters.
	 *
	 * @param array|bool $payload The response data
	 * @param boolean    $encode  If true, response is run through htmlspecialchars()
	 * @param int        $encodeOptions
	 *
	 * @return
	 */
	protected function _ajaxReturn( $payload = false, $encode = false, $encodeOptions = ENT_NOQUOTES )
	{
		$this->layout = false;

		if ( false === $payload || true === $payload )
		{
			$payload = ( $payload ? '1' : '0' );
		}

		$_result = json_encode( $payload );
		if ( $encode )
		{
			$_result = htmlspecialchars( $_result, $encodeOptions );
		}

		echo $_result;

		return;
	}

	/**
	 * @param $debugMode
	 *
	 * @return \CPSController
	 */
	public function setDebugMode( $debugMode )
	{
		$this->_debugMode = $debugMode;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getDebugMode()
	{
		return $this->_debugMode;
	}

}
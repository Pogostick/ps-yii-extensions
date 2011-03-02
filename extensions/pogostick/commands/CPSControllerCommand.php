<?php
/**
 * CPSControllerCommand class file.
 *
 * @filesource
 * @author Jerry Ablan <jablan@pogostick.com>
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @package psYiiExtensions
 * @subpackage commands
 * @since v1.0.6
 * @version SVN: $Revision: 380 $
 * @modifiedby $LastChangedBy: jerryablan@gmail.com $
 * @lastmodified  $Date: 2010-04-05 07:20:21 -0400 (Mon, 05 Apr 2010) $
 */

//	Imports for me...
Yii::import( 'pogostick.commands.CPSConsoleCommand' );
 
/**
* Command that generates a Pogostick Yii Controller
*/
class CPSControllerCommand extends CPSConsoleCommand
{
	//********************************************************************************
	//* Constants
	//********************************************************************************

	/**
	* Our version
	*/
	const VERSION = '1.0.6';

	//********************************************************************************
	//* Public Methods
	//********************************************************************************
	
	/**
	* Initialize the command
	* 
	*/
	public function init()
	{
		parent::init();
		
		//	Add our options
		$this->addOptions( self::getBaseOptions() );

		//	Set base values
		$this->templatePath = Yii::getPathOfAlias( 'pogostick.templates.crud' );
		
		$this->controllerBaseClass = 'CPSController';
		$this->controllerTemplateName = 'controller.php';

		$this->name = 'pscontroller';
	}

	/**
	* Runs the command
	* 
	* @param array $arArgs Command line parameters specific for this command
	*/
	public function run( $arArgs )
	{
		//	Process arguments...
		$arArgs = $this->processArguments( $arArgs );
		
		//	Check args...
		if ( ! ( $_sControllerId = array_shift( $arArgs ) ) )
		{
			echo "\033[1mError\033[0m: controller name is required.\n";
			echo $this->getHelp();
			return;
		}
		
		//	Get actions from arguments...
		$_arActions = array( 'index' );

		//	The rest of the arguments are actions...		
		while ( $_sAction = array_shift( $arArgs ) )
			array_push( $_arActions, $_sAction );

		//	Set our actions...
		$this->actions = $_arActions;
		
		//	Load the module
		$_oModule = Yii::app();
		$_sModuleId = ( $_oModule instanceof CWebModule ) ? $_oModule->id . '/' : '';

		//	Get controller stuff
		list( $_sControllerClass, $_sControllerFile, $_sControllerId ) = $this->parseControllerId( $_sControllerId, $_oModule );
		
		//	Build our parameter array
		$_sClasses = $this->boldEchoString( '(' . $_sControllerFile . ')', $_sControllerClass, true );

		$_arList = array(
			basename( $_sControllerFile ) => array(
				'source' => $this->templatePath . DIRECTORY_SEPARATOR . $this->controllerTemplateName,
				'target' => $_sControllerFile,
				'callback' => array( $this, 'generateController' ),
				'params' => array( $_sControllerClass, $this->actions ),
			),
		);

		$_sViewPath = $_oModule->viewPath . DIRECTORY_SEPARATOR . str_replace( '/', DIRECTORY_SEPARATOR, $_sControllerId );
		
		//	Actions
		foreach ( $this->actions as $_sAction )
		{
			$_arList[ $_sAction . '.php' ] = array(
				'source' => $this->templatePath . DIRECTORY_SEPARATOR . $this->viewTemplateName, 
				'target' => $_sViewPath . DIRECTORY_SEPARATOR . $_sAction . '.php',
				'callback' => array( $this, 'generateAction' ),
				'params' => array(),
			);
			
			$_sClasses .= $this->boldEchoString( '(' . $_sViewPath . DIRECTORY_SEPARATOR . $_sAction . '.php' . ')', $_sAction, true );
		}

		$this->displayParameters( 'Controller Generator', array( 'Output Path' => $_sViewPath ) );
		
		$_sPrompt = "Do you want to generate the above classes? [\033[1mY\033[0mes|\033[1mN\033[0mo] ";
		
		//	Generate
		$_arList = $this->copyFiles( $_arList );
		
		//	Display results...
		echo $this->getResultDisplay( $_arList );
	}

	/**
	* Generate a controller, ingore lack of PK for views
	* 
	* @param mixed $sSourceFile
	* @param mixed $arParams
	* @return string
	*/
	public function generateController( $sSourceFile, $arParams )
	{
		if ( ! is_file( $sSourceFile ) ) $sSourceFile = YII_PATH . '/cli/views/shell/controller/' . basename( $sSourceFile );
		
		return $this->renderFile( 
			$sSourceFile, 
			array( 
				'className' => $arParams[ 0 ], 
				'actions' => $arParams[ 1 ], 
				'baseClass' => $this->controllerBaseClass 
			), 
			true
		);
	}

	/**
	* Generate an action file
	* 
	* @param string $sSourceFile
	* @param array $arParams
	* @return string
	*/
	public function generateAction( $sSourceFile, $arParams )
	{
		if ( ! is_file( $sSourceFile ) ) $sSourceFile = YII_PATH . '/cli/views/shell/controller/' . basename( $sSourceFile );
		return $this->renderFile( $sSourceFile, array(), true );
	}

	/**
	* Generates an input field
	* 
	* @param string $sModelClass
	* @param CDbColumnSchema $oColumn
	*/
	public function generateInputField( $sModelClass, $oColumn )
	{
		if ( $oColumn->type === 'boolean' ) return "PS::field( PS::CHECK, \$model, '{$oColumn->name}' )";
		
		if ( false !== stripos( $oColumn->dbType, 'text' ) )
			return "PS::field( PS::TEXTAREA, \$model, '{$oColumn->name}', array( 'rows' => 6, 'cols' => 50 ) )";
			
		$_sType = ( preg_match( '/^(password|pass|passwd|passcode)$/i', $oColumn->name ) ) ? 'PS::PASSWORD' : 'PS::TEXT';

		if ( $oColumn->type !== 'string' || $oColumn->size === null )
			return "PS::field( {$_sType}, \$model, '{$_oColumn->name}' )";

		if ( ( $_iSize = $_iMaxLength = $oColumn->size ) > 60 ) $_iSize = 60;

		return "PS::field( {$_sType}, $model, '{$oColumn->name}', array( 'size' => $_iSize, 'maxlength' => $_iMaxLength ) )";
	}

	/**
	* Display help
	* 
	*/
	public function getHelp()
	{
		$_sName = $this->bold( $this->name );
		
		$_sOptions = $this->getDisplayOptions();
		
		return <<<EOD
USAGE
	\033[1m{$_sName}\033[0m <\033[1mcontroller-ID\033[0m> [\033[1maction-ID\033[0m]... [options]

DESCRIPTION
  This command generates a controller and views associated with
  the specified actions.

PARAMETERS
 * controller-ID: required, controller ID, e.g., 'post'.
   If the controller should be located under a subdirectory,
   please specify the controller ID as 'path/to/ControllerID',
   e.g., 'admin/user'.

   If the controller belongs to a module, please specify
   the controller ID as 'ModuleID/ControllerID' or
   'ModuleID/path/to/Controller' (assuming the controller is
   under a subdirectory of that module).

 * action-ID: optional, action ID. You may supply one or several
   action IDs. A default 'index' action will always be generated.

OPTIONS

  -f, --force                  if the files exists, you will be able to 
                                   optionally overwrite them.
                                   
  -d, --database               The database component to use. 
                                   Defaults to 'db'

  -b, --base-class             The base class to use for generated controllers. 
                                   Defaults to 'CPSController'
                                   
  -t, --template-path          The template path to use.
  
  -n, --template-name          The name of the template to use. 
                                   Defaults to 'controller.php'

{$_sOptions}
EXAMPLES
 * Generates the 'post' controller:
        {$_sName} post

 * Generates the 'post' controller with additional actions 'contact'
   and 'about':
        {$_sName} post contact about

 * Generates the 'post' controller which should be located under
   the 'admin' subdirectory of the base controller path:
        {$_sName} admin/post

 * Generates the 'post' controller which should belong to
   the 'admin' module:
        {$_sName} admin/post

NOTE: in the last two examples, the commands are the same, but
the generated controller file is located under different directories.
Yii is able to detect whether 'admin' refers to a module or a subdirectory.

EOD;
	}

	//********************************************************************************
	//* Private Methods
	//********************************************************************************
	
	protected function parseControllerId( $sModelClass, $sControllerId, $oModule )
	{
		if ( empty( $sControllerId ) )
		{
			$sControllerId = $sModelClass;
			$_sControllerClass = ucfirst( $sControllerId ) . 'Controller';
			$_sControllerFile = $oModule->controllerPath . DIRECTORY_SEPARATOR . $_sControllerClass . '.php';
			$sControllerId[ 0 ] = strtolower( $sControllerId[ 0 ] );
		}
		else
		{
			//	Build the controller
			if ( false === ( $_iPos = strrpos( $sControllerId, '/' ) ) )
			{
				$_sControllerClass = ucfirst( $sControllerId ) . 'Controller';
				$_sControllerFile = $oModule->controllerPath . DIRECTORY_SEPARATOR . $_sControllerClass . '.php';
				$sControllerId[ 0 ] = strtolower( $sControllerId[ 0 ] );
			}
			else
			{
				$_sLast = substr( $sControllerId, $_iPos + 1 );
				$_sLast[ 0 ] = strtolower( $_sLast );
				$_iPos2 = strpos( $sControllerId, '/' );
				$_sFirst = substr( $sControllerId, 0, $_iPos2 );
				$_sMiddle = $_iPos === $_iPos2 ? '' : substr( $sControllerId, $_iPos2 + 1, $_iPos - $_iPos2 );

				$_sControllerClass = ucfirst( $_sLast ) . 'Controller';
				$_sControllerFile = ( $_sMiddle === '' ? '' : $_sMiddle . '/' ) . $_sControllerClass . '.php';
				$sControllerId = empty( $_sMiddle ) ? $_sLast : $_sMiddle . '/' . $_sLast;
				
				if ( null !== ( $_oNewModule = Yii::app()->getModule( $_sFirst ) ) )
					$oModule = $_oNewModule;
				else
				{
					$_sControllerFile = $_sFirst . '/' . $_sControllerFile;
					$sControllerId = $_sFirst . '/' . $sControllerId;
				}

				$_sControllerFile = $oModule->controllerPath . DIRECTORY_SEPARATOR . str_replace( '/', DIRECTORY_SEPARATOR, $_sControllerFile );
			}
		}
		
		return array( $_sControllerClass, $_sControllerFile, $sControllerId );
	}

	/**
	* Easier on the eyes
	*
	* @access private
	*/
	private function getBaseOptions()
	{
		return(
			array(
				'viewTemplateName' => 'string:view.php',
				'controllerBaseClass' => 'string:CPSController',
				'controllerTemplateName' => 'string:controller.php',
			)
		);
	}
	
}

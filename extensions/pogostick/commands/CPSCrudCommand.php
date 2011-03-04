<?php
/**
 * CPSCrudCommand class file.
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
Yii::import( 'pogostick.commands.CPSControllerCommand' );
 
/**
* Command that generates a Pogostick Yii CRUD set
*/
class CPSCrudCommand extends CPSControllerCommand
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
		$this->actions = array( 'index', 'create', 'update', 'admin', '_form' );

		$this->baseClass = 'CPSModel';
		$this->templateName = 'model.php';

		$this->controllerBaseClass = 'CPSCRUDController';
		$this->controllerTemplateName = 'controller.php';

		$this->name = 'pscrud';
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
		if ( ! ( $_sModelClass = PS::o( $arArgs, 0, null ) ) )
		{
			echo $this->bold( 'Error' ) . ': model class name is required.' . PHP_EOL;
			echo $this->getHelp();
			return;
		}
		
		//	Any id?
		$_sControllerId = trim( PS::o( $arArgs, 1, null ) );
		
		//	Load the module
		$_oModule = $this->loadModule( $_sModelClass );
				
		//	Import the model
		$_sModelClass = Yii::import( $_sModelClass );

		//	Get controller stuff
		list( $_sControllerClass, $_sControllerFile, $_sControllerId ) = $this->parseControllerId( $_sModelClass, $_sControllerId, $_oModule );

		$_sViewPath = $_oModule->viewPath . DIRECTORY_SEPARATOR . str_replace( '.', DIRECTORY_SEPARATOR, $_sControllerId );
		
		//	Build our parameter array
		$_arList = array(
			basename( $_sControllerFile ) => array(
				'source' => $this->templatePath . DIRECTORY_SEPARATOR . $this->controllerTemplateName,
				'target' => $_sControllerFile,
				'callback' => array( $this, 'generateController' ),
				'params' => array( $_sControllerClass, $_sModelClass ),
			),
		);

		//	Actions
		foreach ( $this->actions as $_sAction )
		{
			$_arList[ $_sAction.'.php' ] = array(
				'source' => $this->templatePath . DIRECTORY_SEPARATOR . $_sAction . '.php',
				'target' => $_sViewPath . DIRECTORY_SEPARATOR . $_sAction . '.php',
				'callback' => array( $this, 'generateView' ),
				'params' => array( $_sControllerClass, $_sModelClass, $_sAction ),
			);
		}

		//	Show generator header
		echo $this->displayParameters( 'CRUD Generator', array( 'Output Path' => $_sViewPath ) );
		
		//	Generate
		$_arList = $this->copyFiles( $_arList );
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
		list( $_sControllerClass, $_sModelClass ) = $arParams;
		
		$_oModel = CPSModel::model( $_sModelClass );

		//	Fall back to default ones
		if ( ! is_file( $sSourceFile ) ) $sSourceFile = YII_PATH . '/cli/views/shell/crud/' . basename( $sSourceFile );

		//	Render
		return $this->renderFile( 
			$sSourceFile,
			array(
				'ID' => PS::nvl( $_oModel->tableSchema->primaryKey, '' ),
				'controllerClass' => $_sControllerClass,
				'modelClass' => $_sModelClass,
				'baseClass' => $this->controllerBaseClass,
				'databaseName' => $this->databaseName,
			),
			true
		);

//		echo "Model \"" . $this->bold( $_sModelClass ) . "\" not found. Unable to generate controller \"" . $this->bold( $_sControllerClass ) . "\".\n";
		
//		return null;
	}

	/**
	* Generate a view
	* 
	* @param string $sSource
	* @param string $sModelClass
	* @return string
	*/
	public function generateView( $sSource, $arParams = array() )
	{
		list( $_sControllerClass, $_sModelClass, $_sAction ) = $arParams;

		try
		{
			if ( $_oModel = CPSModel::model( $_sModelClass ) )
			{
				$_oTable = $_oModel->getTableSchema();
				$_arColumns = $_oTable->columns;
				
				if ( isset( $_oTable->primaryKey ) ) unset( $_arColumns[ $_oTable->primaryKey ] );

				//	Check source file...
				if ( ! is_file( $sSource ) ) 
				{
					//	Try our generic view, then default framework
					if ( ! is_file( $sSource = Yii::getPathOfAlias( 'pogostick.templates.crud' ) . '/view.php' ) )
						$sSource = YII_PATH . '/cli/views/shell/crud/' . basename( $sSource );
				}
				
				return $this->renderFile( $sSource,
					array(
						'ID' => PS::nvl( $_oTable->primaryKey, '' ),
						'modelClass' => $_sModelClass,
						'columns' => $_arColumns
					),
					true
				);
			}
		}
		catch ( Exception $_ex )
		{
		}

		echo "Model \"" . $this->bold( $_sModelClass ) . "\" not found. Unable to generate view \"" . $this->bold( $_sAction ) . "\".\n";
		return null;
	}

	/**
	* Generates an input label
	* 
	* @param string $sModelClass
	* @param CDbColumnSchema $oColumn
	*/
	public function generateInputLabel( $sModelClass, $oColumn )
	{
		//	Does nothing as PS::field() method will provide label
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
	\033[1m{$_sName}\033[0m <\033[1mmodel-class\033[0m> [\033[1mcontroller-ID\033[0m] [options]

DESCRIPTION
  This command generates a controller and views that accomplish
  CRUD operations for the specified data model.

PARAMETERS
 * model-class: required, the name of the data model class. This can
   also be specified as a path alias (e.g. application.models.Post).
   If the model class belongs to a module, it should be specified
   as 'ModuleID.models.ClassName'.

 * controller-ID: optional, the controller ID (e.g. 'post').
   If this is not specified, the model class name will be used
   as the controller ID. In this case, if the model belongs to
   a module, the controller will also be created under the same
   module.

   If the controller should be located under a subdirectory,
   please specify the controller ID as 'path/to/ControllerID'
   (e.g. 'admin/user').

   If the controller belongs to a module (different from the module
   that the model belongs to), please specify the controller ID
   as 'ModuleID/ControllerID' or 'ModuleID/path/to/Controller'.

OPTIONS

  -f, --force                  if the files exists, you will be able to 
                                   optionally overwrite them.
                                   
  -d, --database               The database component to use. 
                                   Defaults to 'db'

  -b, --base-class             The base class to use for generated controllers. 
                                   Defaults to 'CPSCRUDController'
                                   
  -t, --template-path          The template path to use.
  
  -n, --template-name          The name of the template to use. 
                                   Defaults to 'controller.php'

{$_sOptions}
EXAMPLES
 * Generates CRUD for the Post model:
        {$_sName} Post

 * Generates CRUD for the Post model which belongs to module 'admin':
        {$_sName} admin.models.Post

 * Generates CRUD for the Post model. The generated controller should
   belong to module 'admin', but not the model class:
        {$_sName} Post admin/post

EOD;
	}

	//********************************************************************************
	//* Private Methods
	//********************************************************************************

	/**
	* Easier on the eyes
	*
	* @access private
	*/
	private function getBaseOptions()
	{
		return(
			array(
				'actions' => 'array:array()',
			)
		);
	}
 
}

<?php
/**
 * CPSModuleCommand class file.
 *
 * @filesource
 * @author Jerry Ablan <jablan@pogostick.com>
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @package psYiiExtensions
 * @subpackage commands
 * @since v1.0.6
 * @version SVN: $Revision: 383 $
 * @modifiedby $LastChangedBy: jerryablan@gmail.com $
 * @lastmodified  $Date: 2010-05-17 23:58:13 -0400 (Mon, 17 May 2010) $
 */
 
//	Imports for me...
Yii::import( 'pogostick.commands.CPSConsoleCommand' );
 
/**
* Command that generates a Pogostick Yii Module
*/
class CPSModuleCommand extends CPSConsoleCommand
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
		
		//	Set base values
		$this->templatePath = Yii::getPathOfAlias( 'pogostick.templates.module' );
		
		$this->basePath = Yii::app()->getModulePath();
		$this->baseClass = 'CPSWebModule';
		$this->templateName = 'module.php';

		$this->name = 'psmodule';
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
  \033[1m{$_sName}\033[0m <\033[1mmodule-ID\033[0m> [options]

DESCRIPTION
  This command generates an application module.

PARAMETERS
  * module-ID: required, module ID. It is case-sensitive.

OPTIONS

  -f, --force                  if the files exists, you will be able to 
                                   optionally overwrite them.
                                   
  -b, --base-class             The base class to use for generated controllers. 
                                   Defaults to 'CPSWebModule'
                                   
  -t, --template-path          The template path to use.
  
  -n, --template-name          The name of the template to use. 
                                   Defaults to 'module.php'

{$_sOptions}

EOD;
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
		if ( ! ( $_sModuleId = array_shift( $arArgs ) ) )
		{
			echo "\033[1mError\033[0m: model ID is required.\n";
			echo $this->getHelp();
			return;
		}
		
		$_sModuleClass = ucfirst( $_sModuleId ) . 'Module';
		$_sModulePath = Yii::app()->getModulePath() . DIRECTORY_SEPARATOR . $_sModuleId;
		$_sSourceDir = PS::nvl( $this->templatePath, YII_PATH . '/cli/views/shell/module' );

		$_arList = $this->buildFileList( $_sSourceDir, $_sModulePath );

		$_arList[ $this->templateName ][ 'target' ] = $_sModulePath . DIRECTORY_SEPARATOR . $_sModuleClass . '.php';
		$_arList[ $this->templateName ][ 'callback' ] = array( $this, 'generateFile' );
		$_arList[ $this->templateName ][ 'params' ] = array(
			'moduleClass' => $_sModuleClass,
			'moduleID' => $_sModuleId,
		);

		$_arList[ $_sModuleClass . '.php' ] = $_arList[ $this->templateName ];
		unset( $_arList[ $this->templateName ] );

		//	Show params
		$this->displayParameters( 'Module Generator', array( 'Output Path' => $_sModulePath ) );
			
		//	Generate
		$_arList = $this->copyFiles( $_arList );
		
		//	Display results...
		echo $this->getResultDisplay( $_arList );
	}

}

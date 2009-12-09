<?php
/**
* This is the template for generating the module class file.
* The following variables are available in this template:
* - $moduleID: The ID of this module
* - $moduleClass: The name of this module
*/

//	Include our header
$className = $moduleClass;

include( Yii::getPathOfAlias( 'pogostick.templates.crud' ) . '/build_template_header.php' );
 
echo <<<HTML
class {$moduleClass} extends CPSWebModule
{
	//********************************************************************************
	//* Public Methods
	//********************************************************************************
	
	/***
	* Initialize the module
	* 
	*/
	public function init()
	{
		/**
		 * @TODO
		 * This method is called when the module is being created.
		 * You may place code here to customize the module or the application.
		 */
		
		//	Phone home...
		parent::init();


		//	Import the module-level models and components
		\$this->setImport( 
			array(
				'{$moduleID}.models.*',
				'{$moduleID}.components.*',
		));
	}

	/**
	 * Called before an action is performed
	 * 
	 * @param CController \$oController
	 * @param string \$sAction
	 */
	public function beforeControllerAction( \$oController, \$sAction )
	{
		/**
		* @TODO
		* 
		* This method is called before any module controller action 
		* is performed you may place customized code here or remove.
		*/
		
		return parent::beforeControllerAction( \$oController, \$sAction );
	}
}
HTML;

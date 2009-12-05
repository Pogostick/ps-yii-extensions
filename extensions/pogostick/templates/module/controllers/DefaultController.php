<?php
/**
* This is the template for generating the controller class file.
*/
 
$controllerClass = 'DefaultController';
$className = $controllerClass . ' class';

//	Include our header
include( Yii::getPathOfAlias( 'pogostick.templates.crud' ) . '/build_template_header.php' );
 
//	The rest...
echo <<<HTML
class {$controllerClass} extends CPSController
{
	//********************************************************************************
	//* Public Methods
	//********************************************************************************
	
	public function init()
	{
		//	Phone home...
		parent::init();
	}

	/**
	 * Default admin action. Change conditions for your system
	 *
	 */
	public function actionAdmin( \$arExtraParams = array(), \$oCriteria = null )
	{
		//	Add your own functionality...
		return parent::actionAdmin( \$arExtraParams, \$oCriteria );
	}

}
HTML;

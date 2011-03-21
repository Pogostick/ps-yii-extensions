<?php
/**
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * This is the template for generating the controller class file for crud.
 * The following variables are available in this template:
 * - $ID: the primary key name
 * - $controllerClass: the controller class name
 * - $baseClass: The parent class
 * - $modelClass: the model class name
 * 
 * @package 	psYiiExtensions.templates
 * @subpackage 	crud
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id: controller.php 322 2009-12-23 23:51:37Z jerryablan@gmail.com $
 * @since 		v1.0.6
 *  
 * @filesource
 * 
 */

$_sClass = PS::nvl( $modelClass, $ID );
$className = $controllerClass . ' class';

//	Include our header
include( Yii::getPathOfAlias( 'pogostick.templates.crud' ) . '/build_template_header.php' );
 
//	The rest...
echo <<<HTML
class {$controllerClass} extends {$baseClass}
{
	//********************************************************************************
	//* Public Methods
	//********************************************************************************
	
	public function init()
	{
		//	Phone home...
		parent::init();
		
		//	Set model name...
		\$this->setModelName( '{$_sClass}' );
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

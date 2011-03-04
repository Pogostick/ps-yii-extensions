<?php
/**
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * This is the template for generating the create view for crud.
 * The following variables are available in this template:
 * - $ID: the primary key name
 * - $modelClass: the model class name
 * - $columns: a list of column schema objects
 * 
 * @package 	psYiiExtensions.templates
 * @subpackage 	crud
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id: create.php 380 2010-04-05 11:20:21Z jerryablan@gmail.com $
 * @since 		v1.0.6
 *  
 * @filesource
 * 
 */

$className = 'create view';
 
//	Include our header 
include( Yii::getPathOfAlias( 'pogostick.templates.crud' ) . '/build_template_header.php' );

echo <<<HTML
	echo CPSForm::formHeaderEx( '', 
		array( 
			'menuButtons' => array( 'save', 'cancel' ),
			'itemName' => '{$modelClass}',
			'formId' => 'ps-edit-form',
		)
	);

	echo $this->renderPartial( '_form', 
		array(
			'model' => $model,
			'update' => false,
		)
	);
HTML;

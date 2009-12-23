<?php
/*
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright &copy; 2009 Pogostick, LLC
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
 * @version 	SVN: $Id$
 * @since 		v1.0.6
 *  
 * @filesource
 * 
 */

$className = 'update view';
 
//	Include our header 
include( Yii::getPathOfAlias( 'pogostick.templates.crud' ) . '/build_template_header.php' );

echo<<<HTML
	echo CPSForm::formHeader( 'Edit {$modelClass}: ' . \$model->{$ID}, 
		array( 
			'save' => array(
				'label' => 'Save',
				'url' =>  '_submit_',
				'icon' => 'disk',
			),
			
			'cancel' => array(
				'label' => 'Cancel',
				'url' => array( 'admin' ),
				'icon' => 'cancel',
			),
			
			'delete' => array(
				'label' => 'Delete',
				'url' => array( 'delete' ),
				'confirm' => 'Do you really want to delete this {$modelClass}?',
				'icon' => 'trash',
			),
		)
	);
	
	echo \$this->renderPartial( '_form', array(
		'model' => \$model,
		'update' => true,
	));
HTML;

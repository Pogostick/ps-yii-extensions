<?php
/**
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * This is the template for generating the list view for crud.
 * The following variables are available in this template:
 * - $ID: the primary key name
 * - $modelClass: the model class name
 * - $columns: a list of column schema objects
 * 
 * @package 	psYiiExtensions.templates
 * @subpackage 	crud
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id: list.php 322 2009-12-23 23:51:37Z jerryablan@gmail.com $
 * @since 		v1.0.6
 *  
 * @filesource
 * 
 */

$className = 'list view';
 
//	Include our header 
include( Yii::getPathOfAlias( 'pogostick.templates.crud' ) . '/build_template_header.php' );

echo <<<HTML
	echo CPSForm::formHeader( '{$modelClass} List', 
		array( 
			'new' => array(
				'label' => 'Add New {$modelClass}',
				'url' =>  array( 'create' ),
				'icon' => 'circle-plus',
			),
			
			'return' => array(
				'label' => '{$modelClass} Manager',
				'url' => array( 'admin' ),
				'icon' => 'arrowreturnthick-1-w',
			)
		)
	);
	
	echo CPSDataList::create( '{$modelClass}', \$models, null, null, null, \$pages );
HTML;

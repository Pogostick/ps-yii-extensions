<?php
/**
 * This is the template for generating the list view for crud.
 * The following variables are available in this template:
 * - $ID: the primary key name
 * - $modelClass: the model class name
 * - $columns: a list of column schema objects
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

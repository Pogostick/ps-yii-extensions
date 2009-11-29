<?php
/**
 * This is the template for generating the show view for crud.
 * The following variables are available in this template:
 * - $ID: the primary key name
 * - $modelClass: the model class name
 * - $columns: a list of column schema objects
 */

$className = 'show view';
 
//	Include our header 
include( Yii::getPathOfAlias( 'pogostick.templates.crud' ) . '/build_template_header.php' );

echo <<<HTML
	echo CPSForm::formHeader( 'View {$modelClass}: ' . \$model->{$ID}, 
		array( 
			'new' => array(
				'label' => 'New {$modelClass}',
				'url' =>  '_submit_',
				'icon' => 'disk',
			),
			
			'return' => array(
				'label' => '{$modelClass} Manager',
				'url' => array( 'admin' ),
				'icon' => 'arrowreturnthick-1-w',
			),
		)
	);
	
	echo \$this->renderPartial( '_form', array(
		'model' => \$model,
		'update' => false,
	));
HTML;

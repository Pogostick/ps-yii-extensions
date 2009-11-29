<?php
/**
 * This is the template for generating the admin view for crud.
 * The following variables are available in this template:
 * - $ID: the primary key name
 * - $modelClass: the model class name
 * - $columns: a list of column schema objects
 */

//	Build an array for the data grid...

$_sCols = null;
foreach ( $columns as $column ) $_sCols .= ( ( $_sCols ) ? ', ' : '' ) . '\'' . $column->name . '\'';
$_sCols = "array( {$_sCols } )";
	
//	Include our header...
$className = 'admin view';
include( Yii::getPathOfAlias( 'pogostick.templates.crud' ) . '/build_template_header.php' );

//	And output the rest...
echo <<<HTML
	echo CPSForm::formHeader( '{$modelClass} Manager', 
		array( 'new' => 
			array(
				'label' => 'New {$modelClass}',
				'url' => array( 'create' ),
				'icon' => 'circle-plus',
			)
		)
	);

	echo CPSDataGrid::create( 
		'{$modelClass}', 
		\$models, 
		{$_sCols},
		array( 'edit', 'delete' ), 
		\$sort, 
		\$pages, 
		array( 'header' => '' ) 
	);

HTML;

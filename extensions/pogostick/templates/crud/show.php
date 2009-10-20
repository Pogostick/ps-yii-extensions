<?php
/**
 * This is the template for generating the show view for crud.
 * The following variables are available in this template:
 * - $ID: the primary key name
 * - $modelClass: the model class name
 * - $columns: a list of column schema objects
 */

$_sCols = null;
foreach ( $columns as $column )  $_sCols .= ( ( $_sCols ) ? ', ' : '' ) . '\'' . ( $ID ? '@' : '' ) . $column->name . '\'';
$_sCols = "array( {$_sCols } )";

echo <<<HTML
<?php
	echo CPSForm::formHeader( '{$modelClass} : ' . \$model->{$ID}, 
		array( 
			'cancel' => array(
				'label' => 'Cancel',
				'url' =>  array( 'admin' ),
				'icon' => 'cancel',
			),
			
			'save' => array(
				'label' => 'Save',
				'url' =>  '_submit_',
				'icon' => 'disk',
			),
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

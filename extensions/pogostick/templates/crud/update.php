<?php
/**
 * This is the template for generating the create view for crud.
 * The following variables are available in this template:
 * - $ID: the primary key name
 * - $modelClass: the model class name
 * - $columns: a list of column schema objects
 */
echo<<<HTML
<?php
	echo CPSForm::formHeader( 'Edit : ' . \$model->{$ID}, 
		array( 
			'new' => array(
				'label' => 'New',
				'url' =>  array( 'create' ),
				'icon' => 'circle-plus',
			),
			
			'edit' => array(
				'label' => 'Edit',
				'url' =>  array( 'Update' ),
				'icon' => 'pencil',
			),
			
			'delete' => array(
				'label' => 'Delete',
				'url' => array( 'delete' ),
				'confirm' => 'Do you really want to delete this {$modelClass}?',
				'icon' => 'trash',
			),
			
			'return' => array(
				'label' => 'User Manager',
				'url' => array( 'admin' ),
				'icon' => 'arrowreturnthick-1-w',
			)
		)
	);
	
	echo \$this->renderPartial( '_form', array(
		'model' => \$model,
		'_oModel' => \$model,
		'_bUpdate' => true,
		'update' => true,
	));
HTML;

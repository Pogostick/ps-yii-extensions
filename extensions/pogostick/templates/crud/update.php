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

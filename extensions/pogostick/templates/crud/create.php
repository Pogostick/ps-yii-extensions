<?php
/**
 * This is the template for generating the create view for crud.
 * The following variables are available in this template:
 * - $ID: the primary key name
 * - $modelClass: the model class name
 * - $columns: a list of column schema objects
 */
 
echo <<<HTML
<?php
	echo CPSForm::formHeader( 'New {$modelClass}', 
		array( 
			'save' => array(
				'label' => 'Save',
				'url' => 'submit',
				'icon' => 'disk',
			),

			'cancel' => array(
				'label' => 'Cancel',
				'url' => array( 'admin' ),
				'icon' => 'cancel',
			),
			
			'return' => array(
				'label' => '{$modelClass} Manager',
				'url' => array( 'admin' ),
				'icon' => 'arrowreturnthick-1-w',
			)
		)
	);

echo \$this->renderPartial( '_form', array(
	'model' => \$model,
	'_oModel' => \$model,
	'_bUpdate' => false,
	'update' => false,
));
HTML;


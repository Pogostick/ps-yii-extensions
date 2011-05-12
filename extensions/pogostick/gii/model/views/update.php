<?php
	if ( $update )
	{
		$_options = array(
			'subtitle' => 'Update a Pod',
			'header' => 'Update Pod "' . $model->pod_name_text . '"',
			'breadcrumbs' => array(
				'Pod Manager' => array( 'admin' ),
				$model->pod_name_text,
			),
			'menu' => array(
				'Pod Manager' => array( 'admin' ),
				'Create a Pod' => array( 'create' ),
			),
		);
	}
	else
	{
		$_options = array(
			'header' => 'Create a Pod',
			'breadcrumbs' => array(
				'Pod Manager' => array( 'admin' ),
				'Create',
			),
			'menu' => array(
				'Pod Manager' => array( 'admin' ),
			),
		);
	}

	//	Render the form
	echo $this->renderPartial( 
		'_form', 
		array( 
			'model' => $model,
			'_formOptions' => $this->setStandardFormOptions( $model,$_options ),
			'update' => $update,
		)
	);

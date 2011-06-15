<?php
/**
 * Created by JetBrains PhpStorm.
 * User: jablan
 * Date: 5/12/11
 * Time: 10:02 AM
 * To change this template use File | Settings | File Templates.
 */

$this->setStandardFormOptions(
	$model,
	array(
		'id' => 'pod-grid',
		'header' => 'Manage Pods',
		'headerIcon' => PS::_cu( 'public/images/icon-pod.png' ),
		'renderSearch' => true,
		'subtitle' => 'Pod Manager',
		'breadcrumbs' => array( 'Pod Manager' ),
		'menu' => array(
			'Create a Pod' => array( 'create' ),
		),
	)
);

$this->widget(
	'zii.widgets.grid.CGridView',
	array(
		'id' => 'pod-grid',
		'dataProvider' => $model->search(),
		'filter' => $model,
		'columns' => array(
			'id',
			'pod_name_text',
			'class_name_text',
			'api_host_name_text',
			'api_alt_host_name_text',
			'api_port_nbr',
			array(
				'class'=>'CButtonColumn',
				'template' => '{update} {delete}',
				'header' => 'Actions',
			),
		)
	)
);
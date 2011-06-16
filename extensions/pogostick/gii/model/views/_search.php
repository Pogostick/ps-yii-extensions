<?php
	PS::setShowRequiredLabel( false );

	$_formOptions = array(
		'formModel' => $model,
		'formClass' => 'wide form ui-widget',
		'action' => PS::_cu( $this->route ),
		'method' => 'GET',
		'showLegend' => false,
		'uiStyle' => PS::UI_JQUERY,
	);

	$_fieldList = array();

	$_fieldList[] = array( 'html', '<fieldset><legend>Advanced Search</legend>' );

	$_fieldList[] = array( PS::DD_DATA_LOOKUP, 'id', array( 'prompt' => 'Any', 'dataName' => 'id', 'dataModel' => 'PodConfig' ) );
	$_fieldList[] = array( PS::TEXT, 'pod_name_text', array( 'size' => 30, 'maxlength' => 30 ) );

	$_fieldList[] = array(
		PS::DD_DATA_LOOKUP,
		'datacenter_id',
		array(
			'prompt' => 'Any',
			'dataId' => 'id',
			'dataName' => 'datacenter_name_text',
			'dataModel' => 'Datacenter',
		)
	);

	$_fieldList[] = array( PS::TEXT, 'class_name_text', array( 'size' => 60, 'maxlength' => 100 ) );
	$_fieldList[] = array( PS::TEXT, 'api_host_name_text', array( 'size' => 60, 'maxlength' => 200 ) );
	$_fieldList[] = array( PS::TEXT, 'content_mount_text', array( 'label' => 'Mount', 'size' => 60 ) );
	$_fieldList[] = array( PS::TEXT, 'content_host_text', array( 'label' => 'Mount Host', 'size' => 60 ) );

	$_fieldList[] = array( 'html', PS::submitButton( 'Search', array( 'style' => 'float:right;margin-top:5px;' ) ) );
	$_fieldList[] = array( 'html', '</fieldset>' );

	$_formOptions['fields'] = $_fieldList;

	CPSForm::create( $_formOptions );

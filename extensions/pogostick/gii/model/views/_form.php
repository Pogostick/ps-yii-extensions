<?php
	$_formFields = $_output = null;

	foreach ( $this->getTableSchema()->columns as $_column )
	{
		//	Skip the ID
		if ( $_column->autoIncrement )
			continue;

		$_formFields .= $this->generateActiveField( $this->modelClass, $_column ) . PHP_EOL;
	}

	echo <<<HTML
<\?php
	$_fieldList = array();

	$_fieldList[] = array( 'html', '<fieldset><legend>Section Name</legend>' );
	{$_formFields}
	$_fieldList[] = array( 'html', '</fieldset>' );

	$_fieldList[] = array( 'html', PS::submitButton( $update ? 'Save' : 'Create' ) );

	$_formOptions['fields'] = $_fieldList;

	CPSForm::create( $_formOptions );
HTML;

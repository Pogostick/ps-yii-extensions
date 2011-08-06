<?php
	$_arOpts = array();

	echo PS::beginForm( '', 'POST', array( 'validate' => true, 'id' => 'frmLogin' ) );

		echo PS::errorSummary( $form );
		
		echo PS::field( PS::TEXT, $form, 'username' );
		echo PS::field( PS::PASSWORD, $form, 'password' );
		echo PS::field( PS::CHECK, $form, 'rememberMe' );
		
		echo PS::submitButtonBar( 'Login', array( 'formId' => 'frmLogin', 'noBorder' => true, 'barCenter' => true, 'style' => 'margin-top:10px;', 'jqui' => true, 'icon' => 'person' ) );
		
	echo PS::endForm();

<?
echo <<<HTML
<?
	Yii::app()->clientScript->registerCssFile( '/css/form.css' );
	Yii::app()->clientScript->registerScript( 'psFlashDisplay', '$(".ps-flash-display").animate({opacity: 1.0}, 3000).fadeOut();', CClientScript::POS_READY );

//	Uncomment for automatic tooltips
//	CPSjqToolsWrapper::create( 'tooltip', array( 'target' => '#ps-edit-form :input', 'tip' => '.ps-auto-tooltip', 'position' => 'center right', 'offset' => array( -2, 10 ), 'effect' => 'fade', 'opacity' => 0.7 ) );

	CHtml::\$afterRequiredLabel = null;
?>
<div class="yiiForm">
<?
	echo PS::errorSummary( \$model );

	echo PS::beginForm( '', 'POST', 
		array( 
			'validate' => true, 
			'validateOptions' => array(
			), 
			'id' => 'ps-edit-form', 
			'name' => 'ps-edit-form'
		)
	);
		echo PS::errorSummary(\$model);

HTML;

		foreach ( $columns as $name => $column )
		{
			if ( $name == 'created' || $name == 'date_created' || $name == 'modified' || $name == 'date_modified' )
				continue;

			if ( $column->type === 'boolean' )
				$_sType = "PS::field( PS::CHECK, \$model, '{$column->name}' )";
			else if ( stripos( $column->dbType, 'text' ) !== false )
				$_sType = "PS::field( PS::TEXTAREA, \$model, '{$column->name}', array( 'rows' => 6, 'cols' => 50 ) )";
			else
			{
				$_sField = ( preg_match( '/^(password|pass|passwd|passcode)$/i', $column->name ) ) ? 'PS::PASSWORD' : 'PS::TEXT';

				if ( $column->type !== 'string' || $column->size === null )
					$_sType = "PS::field( {$_sField}, \$model, '{$column->name}' )";
				else
				{
					if ( ( $size = $maxLength = $column->size ) > 60 ) $size = 60;
					$_sType = "PS::field( {$_sField}, \$model, '{$column->name}', array( 'size' => $size, 'maxlength' => $maxLength ) )";
				}
			}
			
			echo "		echo {$_sType};\n";
		}

echo <<<HTML

		if ( \$update ) echo PS::showDates( \$model, \$model->getCreatedColumn(), \$model->getLModColumn() );
	echo PS::endForm();
?>
</div>
<div class="ps-auto-tooltip"></div>
HTML;

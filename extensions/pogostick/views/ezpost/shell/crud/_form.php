<?
echo <<<HTML
<?
	Yii::app()->clientScript->registerCssFile( \$this->module->assetUrl . '/css/form.css' );";
?>
<div class="yiiForm">
	<p>Fields with <span class="required">*</span> are required.</p>
<?
	echo CPSActiveWidgets::beginForm();
		echo CPSActiveWidgets::errorSummary(\$model);
HTML;

		foreach ( $columns as $name => $column )
		{
			if ( $name == 'created' || $name == 'modified' )
				continue;
			
			if ( $column->type === 'boolean' )
				$_sType = "CPSActiveWidgets::simpleActiveBlock( CPSActiveWidgets::CHECK, \$model, '{$column->name}' )";
			else if ( stripos( $column->dbType, 'text' ) !== false )
				$_sType = "CPSActiveWidgets::simpleActiveBlock( CPSActiveWidgets::TEXTAREA, \$model, '{$column->name}', array( 'rows' => 6, 'cols' => 50 ) )";
			else
			{
				$_sField = ( preg_match( '/^(password|pass|passwd|passcode)$/i', $column->name ) ) ? 'CPSActiveWidgets::PASSWORD' : 'CPSActiveWidgets::TEXT';

				if ( $column->type !== 'string' || $column->size === null )
					$_sType = "CPSActiveWidgets::simpleActiveBlock( {$_sField}, \$model, '{$column->name}' )";
				else
				{
					if ( ( $size = $maxLength = $column->size ) > 60 ) $size = 60;
					$_sType = "CPSActiveWidgets::simpleActiveBlock( {$_sField}, \$model, '{$column->name}', array( 'size' => $size, 'maxlength' => $maxLength ) )";
				}
			}
			
			echo $_sType . "\n";
		}

echo <<<HTML
		if ( $update ) 
		{	
			echo CPSActiveWidgets::simpleActiveBlock( CPSActiveWidgets::TEXT, \$model, 'created', array( 'disabled' => true ) );
			echo CPSActiveWidgets::simpleActiveBlock( CPSActiveWidgets::TEXT, \$model, 'modified', array( 'disabled' => true ) );
		}
		echo '<br />';
		echo CHtml::submitButton( 'Save' );
	echo CPSActiveWidgets::endForm();
?>
</div>
HTML;

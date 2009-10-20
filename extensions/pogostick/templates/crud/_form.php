<?
echo <<<HTML
<?
	Yii::app()->clientScript->registerCssFile( \$this->module->assetUrl . '/css/form.css' );
	CPSjqToolsWrapper::create( 'tooltip', array( 'target' => '#ps-edit-form :input[type="text"],#ps-edit-form :input[type="textarea"],#ps-edit-form :input[type="radio"],#ps-edit-form :input[type="checkbox"]', 'tip'=>'.tooltip', 'position' => 'center right', 'offset' => array( -2, 10 ), 'effect' => 'fade', 'opacity' => 0.7 ) );
	Yii::app()->clientScript->registerScript( 'psFlashDisplay', '\$(".ps-flash-display").animate({opacity: 1.0}, 3000).fadeOut();', CClientScript::POS_READY );
?>
<div class="yiiForm">
	<p>Fields with <span class="required">*</span> are required.</p>
<?
	if ( Yii::app()->user->hasFlash( 'success' ) ) echo '<div class="ps-flash-display">' . Yii::app()->user->getFlash('success') . '</div>';
	echo CPSActiveWidgets::beginForm( '', 'POST', array( 'id' => 'ps-edit-form', 'validate' => true, 'selectmenu' => true ) );
		echo CPSActiveWidgets::errorSummary(\$model);

HTML;

		foreach ( $columns as $name => $column )
		{
			if ( $name == 'created' || $name == 'date_created' || $name == 'modified' || $name == 'date_modified' )
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
			
			echo "		echo {$_sType};\n";
		}

echo <<<HTML

		if ( \$update ) echo CPSActiveWidgets::showDates( \$model, null, \$model->getCreatedColumn(), \$model->getLModColumn() );
	echo CPSActiveWidgets::endForm();
?>
</div>
HTML;


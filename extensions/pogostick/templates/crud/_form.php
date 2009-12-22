<?
//	Our header...
$className = 'form';
include( Yii::getPathOfAlias( 'pogostick.templates.crud' ) . '/build_template_header.php' );

//	The rest
echo <<<HTML
	Yii::app()->clientScript->registerCssFile( '/css/form.css' );

	//	I don't like this, I prefer bold-faced labels
	CHtml::\$afterRequiredLabel = null;
	
//	Uncomment for automatic tooltips
//	CPSjqToolsWrapper::create( 'tooltip', array( 'target' => '#ps-edit-form :input', 'tip' => '.ps-auto-tooltip', 'position' => 'center right', 'offset' => array( -2, 10 ), 'effect' => 'fade', 'opacity' => 0.7 ) );
?>
<div class="ps-edit-container">
	<div class="yiiForm">
<?php
		echo PS::beginForm( '', 'POST', 
			array( 
				'validate' => true, 
				'validateOptions' => array(
					'errorClass' => 'ps-validate-error',
					'ignoreTitle' => true,
// @todo Place your extra validation options here...
//					'rules' => array(
//						'model_name[column_name] => array(
//							'rule' => rule options,
//					),
//					'messages' => array(
//						'model_name[column_name] => array(
//							'rule_name' => 'message',
//						),
//					),
				), 
				'id' => 'ps-edit-form', 
				'name' => 'ps-edit-form'
			)
		);
	
			echo PS::errorSummary( \$model );

HTML;

		//	Loop through columns and generate a form
		foreach ( $columns as $_sName => $_oColumn )
		{
			//	Try to ignore some non-display columns. You can remove or augment as desired
			if ( in_array( $_sName, array( 'create_date', 'lmod_date', 'create_user_id', 'lmod_user_id', 'created', 'date_created', 'modified', 'date_modified' ) ) )
				continue;

			//	Build the form
			if ( $_oColumn->type === 'boolean' )
				$_sType = "PS::field( PS::CHECK, \$model, '{$_oColumn->name}' )";
			else if ( stripos( $_oColumn->dbType, 'text' ) !== false )
				$_sType = "PS::field( PS::TEXTAREA, \$model, '{$_oColumn->name}', array( 'rows' => 6, 'cols' => 50 ) )";
			else
			{
				//	Try to distinguish password fields from text fields
				$_sField = ( preg_match( '/^(password|pass|passwd|passcode)$/i', $_oColumn->name ) ) ? 'PS::PASSWORD' : 'PS::TEXT';

				if ( $_oColumn->type !== 'string' || $_oColumn->size === null )
					$_sType = "PS::field( {$_sField}, \$model, '{$_oColumn->name}' )";
				else
				{
					//	Assume tinyint/bools are on/off or yes/no...
					if ( $_oColumn->type == 'int' && $_oColumn->size == 1 )
					{
						$_sType = "PS::field( PS::DD_YES_NO, \$model, '{$_oColumn->name}' )";
					}
					//	Assume "code" db domain is dropdown, you can set the data type
					else if ( preg_match( '/(.*)+_code/i', $_oColumn->name ) )
					{
						$_sType = "PS::field( PS::DROPDOWN, \$model, '{$_oColumn->name}', array( 'data' => array() ) )";
					}
					//	Don't make fields over 60 chars wide
					else if ( ( $size = $maxLength = $_oColumn->size ) > 60 ) 
						$size = 60;

					//	Generate the field
					$_sType = "PS::field( {$_sField}, \$model, '{$_oColumn->name}', array( 'size' => $size, 'maxlength' => $maxLength ) )";
				}
			}
			
			echo "			echo {$_sType};\n";
		}

echo <<<HTML

			if ( \$update ) echo PS::showDates( \$model, \$model->getCreatedColumn(), \$model->getLModColumn() );
		echo PS::endForm();
?>
	</div>
</div>
HTML;

<?php
/**
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * Generic form
 * 
 * @package 	psYiiExtensions.templates
 * @subpackage 	crud
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id: _form.php 380 2010-04-05 11:20:21Z jerryablan@gmail.com $
 * @since 		v1.0.6
 *  
 * @filesource
 */

//	Our header...
$className = 'form';
include( Yii::getPathOfAlias( 'pogostick.templates.crud' ) . '/build_template_header.php' );

//	The rest
echo <<<HTML
PS::_rcf( '/css/form.css' );

//	I don't like this, I prefer bold-faced labels
PS::\$afterRequiredLabel = null;
	
//	Uncomment for automatic tooltips
//	CPSjqToolsWrapper::create( 'tooltip', array( 'target' => '#ps-edit-form :input', 'tip' => '.ps-auto-tooltip', 'position' => 'center right', 'offset' => array( -2, 10 ), 'effect' => 'fade', 'opacity' => 0.7 ) );
?>
<div class="ps-edit-container">
	<div class="yiiForm">
<?php
		\$_arFormOpts = array( 
			'id' => 'ps-edit-form', 
			'name' => 'ps-edit-form',
			'uiStyle' => PS::UI_JQUERY,

			'cssFiles' => array( 
				'/css/form.css',
				//	Add additional css here
			),
	
			'scriptFiles' => array( 
				//	Add any script files here
			),
	
			'formModel' => \$model,

			//	We want error summary...
			'errorSummary' => true,

//	Uncomment if you are uploading from this form			
//			'enctype' => 'multipart/form-data',
			
			'validate' => true, 
			'validateOptions' => array(
				'errorClass' => 'ps-validate-error',
				'ignoreTitle' => true,
// @todo Place your extra validation options here...
//					'rules' => array(
//						'model_name[column_name]' => array(
//							'rule' => 'rule options',
//						),
//					),
//					'messages' => array(
//						'model_name[column_name]' => array(
//							'rule_name' => 'message',
//						),
//					),
			), 
		)
	);

	\$_arFormOpts['fields'] = array(
HTML;

		//	Loop through columns and generate a form
		foreach ( $columns as $_sName => $_oColumn )
		{
			//	Try to ignore some non-display columns. You can remove or augment as desired
			if ( in_array( $_sName, array( 'create_date', 'lmod_date', 'create_user_id', 'lmod_user_id', 'created', 'date_created', 'modified', 'date_modified' ) ) )
				continue;

			//	Build the form
			if ( $_oColumn->type === 'boolean' )
				$_sType = "array( PS::CHECK, '{$_oColumn->name}' )";
			else if ( stripos( $_oColumn->dbType, 'text' ) !== false )
				$_sType = "array( PS::TEXTAREA, '{$_oColumn->name}', array( 'rows' => 6, 'cols' => 50 ) )";
			else
			{
				//	Try to distinguish password fields from text fields
				$_sField = ( preg_match( '/^(password|pass|passwd|passcode)$/i', $_oColumn->name ) ) ? 'PS::PASSWORD' : 'PS::TEXT';

				if ( $_oColumn->type !== 'string' || $_oColumn->size === null )
					$_sType = "array( {$_sField}, '{$_oColumn->name}' )";
				else
				{
					//	Assume tinyint/bools are on/off or yes/no...
					if ( $_oColumn->type == 'int' && $_oColumn->size == 1 )
					{
						$_sType = "array( PS::DD_YES_NO, '{$_oColumn->name}' )";
					}
					//	Assume "code" db domain is dropdown, you can set the data type
					else if ( preg_match( '/(.*)+_code/i', $_oColumn->name ) )
					{
						$_sType = "array( PS::DROPDOWN, '{$_oColumn->name}', array( 'data' => array() ) )";
					}
					//	Don't make fields over 60 chars wide
					else if ( ( $size = $maxLength = $_oColumn->size ) > 60 ) 
						$size = 60;

					//	Generate the field
					$_sType = "\t\tarray( {$_sField}, '{$_oColumn->name}', array( 'size' => {$size}, 'maxlength' => {$maxLength} ) )";
				}
				
				echo $_sType . PHP_EOL;
			}
		}

		echo <<<HTML
		};
		
		CPSForm::create( \$_arFormOpts );"
?>
	</div>
</div>
HTML;

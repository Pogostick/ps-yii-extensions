<?php
/*
 * This file was generated by the psYiiExtensions scaffolding package.
 * 
 * @copyright Copyright &copy; 2009 My Company, LLC.
 * @link http://www.example.com
 */

/**
 * form file
 * 
 * @package 	blog
 * @subpackage 	
 * 
 * @author 		Web Master <webmaster@example.com>
 * @version 	SVN: $Id$
 * @since 		v1.0.6
 *  
 * @filesource
 * 
 */

CPSHelp::_rcf( '/css/form.css' );

//	I don't like this, I prefer bold-faced labels
PS::$afterRequiredLabel = null;

//	Uncomment for automatic tooltips
//	CPSjqToolsWrapper::create( 'tooltip', array( 'target' => '#ps-edit-form :input', 'tip' => '.ps-auto-tooltip', 'position' => 'center right', 'offset' => array( -2, 10 ), 'effect' => 'fade', 'opacity' => 0.7 ) );
?>
<div class="ps-edit-container">
	<div class="yiiForm">
<?php
		if ( isset( $_POST, $_POST['previewPost']) && ! $model->hasErrors())
		{
			//	Display preview
		}	

		echo PS::beginForm( '', 'POST', 
			array( 
				'validate' => true, 
				'validateOptions' => array(
					'errorClass' => 'ps-validate-error',
					'ignoreTitle' => true,
					'rules' => array(
						'Post[title_text]' => array(
							'rule' => array(
								'required' => true,
							),
						),
						'Post[content_text]' => array(
							'rule' => array(
								'required' => true,
							),
						),
					),
					'messages' => array(
						'Post[title_text]' => array(
							'required' => 'You must give your blog entry a title.',
						),
						'Post[content_text]' => array(
							'required' => 'Your blog entry must have something to read!',
						),
					),
				), 
				'id' => 'ps-edit-form', 
				'name' => 'ps-edit-form'
			)
		);
	
			echo PS::errorSummary( $model );
			echo PS::field( PS::TEXT, $model, 'title_text', array( 'size' => 60, 'maxlength' => 128 ) );
			echo PS::field( PS::TEXTAREA, $model, 'content_text', array( 'rows' => 6, 'cols' => 50 ) );
			echo PS::field( PS::DD_GENERIC, $model, 'status_nbr', array( 'data' => Post::model()->getStatusOptions() ) );
			
			echo PS::submitButtonBar( 'Preview', array( 'name' => 'previewPost' ) );
			
			echo $model->showDates();
		echo PS::endForm();
?>
	</div>
</div>
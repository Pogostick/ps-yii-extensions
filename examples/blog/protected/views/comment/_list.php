<?php
/*
 * This file is part of psYiiExtensions blog example
 * 
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 * 
 * @copyright Copyright &copy; 2009 What's Up Interactive, Inc.
 * @link http://www.whatsup.com What's Up Interactive, Inc.
 * 
 * @copyright Copyright &copy; 2009 InTopic Media, LLC
 * @link http://www.intopicmedia.com InTopic Media, LLC.
 */
/**
 * @package 	blog.comment
 * @subpackage 	views
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN $Id$
 * @since 		v1.0.6
 * 
 * @filesource
 */
 
$_sPipe = PS::pipe();

echo PS::tag( 'h2', array(), 'Comments' );

foreach ( $comments as $_oComment )
{
	//	Pending comments are not available to guests...
	if ( Yii::app()->user->isGuest && $_oComment->status_nbr == Comment::STATUS_PENDING )
		continue;

	//	Create a link to this post
	$_sLink = PS::link( '#' . $_oComment->id, array( 'post/show', 'id' => isset( $post ) ? $post->id : $_oComment->post ? $_oComment->post->id : 'Unknown Post', '#' => $_oComment->id ),
		array(
			'class' => 'comment-permalink',
			'title' => 'Permalink to this comment',
		)
	);
		
	//	Make our post
	echo PS::openTag( 'div', array( 'class' => 'comment-item ui-widget-content', 'id' => 'c' . $_oComment->id ) );
	
		echo PS::tag( 'div', array( 'class' => 'comment-link ui-widget-header' ), $_sLink );

		echo PS::tag( 'div', array( 'class' => 'comment-author' ), 
			'On ' . PS::tag( 'span', array( 'class' => 'comment-time' ), $_oComment->create_date ) . 
			' ' . $_oComment->authorLink . ' said:'
		);
		
		echo PS::openTag( 'div', array( 'class' => 'comment-nav' ) );
			
			if ( !Yii::app()->user->isGuest )
			{
				if ( $_oComment->status_nbr == Comment::STATUS_PENDING )
				{
					echo '<span class="pending">Pending approval</span>' . $_sPipe;
					echo PS::linkButton( 'Approve', array( 'submit' => array( 'comment/approve', 'id' => $_oComment->id ) ) ) . $_sPipe;
				}

				echo implode( $_sPipe, 
					array( 
						PS::link( 'Update', array( 'comment/update', 'id' => $_oComment->id ) ), 
						PS::linkButton( 'Delete', array( 'submit' => array( 'comment/delete', 'id' => $_oComment->id ), 'confirm' => 'Are you sure you want to delete this comment?' ) ),
					)
				);
			}
	    
		echo PS::closeTag( 'div' );

		echo PS::tag( 'div', array( 'class' => 'comment-content' ), $_oComment->content_display_text );
		
	echo PS::closeTag( 'div' );
}
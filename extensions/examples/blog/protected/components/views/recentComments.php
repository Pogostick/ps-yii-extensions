<?php 

if ( $_arComments = $this->getRecentComments() )
{
	echo PS::openTag( 'ul' );

	foreach( $_arComments as $_oComment )
		echo PS::tag( 'li', array(), $_oComment->authorLink . ' on ' . PS::link( PS::encode( $_oComment->post->title_text ), array( 'post/show', 'id' => $_oComment->post->id ) ) );
		
	echo PS::closeTag( 'ul' );
}

<?php

$this->renderPartial( '_post', array( 'post' => $post, 'show' => true ) );

echo PS::openTag( 'div', array( 'id' => 'comments' ) );

if ( $post->comment_count_nbr >= 1 )
    PS::tag( 'h3', array(), $post->comment_count_nbr . ' comment' . ( $post->comment_count_nbr > 1 ? 's' : '' ) . ' to ' . PS::encode( $post->title_text ) );

$this->renderPartial( '/comment/_list', array( 'comments' => $comments, 'post' => $post ) );

$this->renderPartial( '/comment/_form', array( 'comment' => $newComment, 'update' => false ) );

echo PS::closeTag( 'div' ) . '<!-- comments -->';

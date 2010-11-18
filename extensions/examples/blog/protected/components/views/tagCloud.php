<?php

foreach ( Tag::model()->findTagWeights() as $_sTag => $_sWeight ) 
{
	echo PS::tag( 'span', array( 'class' => 'tag', 'style' => 'font-size:' . $_sWeight . 'pt !important;' ), 
		PS::link( PS::encode( $_sTag ), array( 'post/list', 'tag' => $_sTag ) )
	);
}
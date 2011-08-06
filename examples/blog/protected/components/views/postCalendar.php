<?php
	if ( ! isset( $postDate ) ) $postDate = PS::o( $_REQUEST, 'date', date('Y-m-d') );
	$postDate = date( 'd-m-Y', strtotime( $postDate ) );

	echo PS::beginForm( array( 'postsByDate' ), 'POST', array( 'id' => 'frmPostCalendar' ) );
	
		echo PS::hiddenField( 'dateValue', null, array( 'id' => 'dateValue' ) );
		echo PS::tag( 'div', array( 'id' => 'postCalendarDatepicker', 'style' => 'font-size:.8em !important' ) );
		CPSjqUIWrapper::create( 'datepicker', array( 'target' => '#postCalendarDatepicker', 'callbacks' => array( 'defaultDate' => 'new Date("' . date( 'm/d/Y', strtotime( $postDate ) ) . '")', 'onSelect' => 'function(sDate,oInst){$(\'#dateValue\').val(sDate);$(\'#frmPostCalendar\').submit();return true;}' ) ) );
		
	echo PS::endForm();

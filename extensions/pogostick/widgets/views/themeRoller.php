<?php
	$_arOpts = array();
	
	$_sTheme = Yii::app()->user->getState( CPSjqUIWrapper::getStateName() );
	$_iSelected = array_search( $_sTheme, CPSjqUIWrapper::getValidThemes() );
	
	echo PS::beginForm( null, 'POST', array( 'id' => 'frmThemeRoller' ) );
		echo PS::hiddenField( CPSCRUDController::COMMAND_FIELD_NAME, CPSThemeRoller::POST_ACTION );
		echo PS::hiddenField( '__uri', $this->getOwner()->getRequest()->getRequestUri() );
		echo PS::dropDown( PS::DD_JQUI_THEMES, '__theme', null, array( 'value' => $_iSelected, 'id' => '_themeRoller', 'style' => 'width:100%' ) );
	echo PS::endForm();
?>
<script type="text/javascript">
<!--
	jQuery(function(){
		jQuery('#_themeRoller').change(function(e){
			return jQuery('#frmThemeRoller').submit();
		});
	});
//-->
</script>	
	

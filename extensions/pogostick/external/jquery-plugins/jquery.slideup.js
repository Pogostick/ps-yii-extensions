var $awfsjQuery = jQuery.noConflict(); // Support for more than one version of jQuery in a page.

$awfsjQuery(document).ready(function() {
	$awfsjQuery('input[value]').each(function(){
		if(this.type == 'text' && (this.name=="name" || this.name=="email")) {
			$awfsjQuery(this).focus(function(){ if (this.value == this.defaultValue) { this.value = ''; }});
			$awfsjQuery(this).blur(function(){ if (!this.value.length) { this.value = this.defaultValue; }});
		}
	});
});

$awfsjQuery(function() {
  if($awfsjQuery.cookie('dont_show_footer_form') == null){
    $awfsjQuery('#footerform').slideDown("slow");
  }
});


function slidedown() {
  $awfsjQuery(function() {
    $awfsjQuery("#dontshowanymore").click(function() { $awfsjQuery.cookie('dont_show_footer_form', 'true', { expires: 3650, path: '/'}); });
	$awfsjQuery("#closefornow").click(function() { $awfsjQuery.cookie('dont_show_footer_form', 'true'); });
    $awfsjQuery('#footerform').slideUp("slow");
  });
}
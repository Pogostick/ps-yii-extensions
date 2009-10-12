<?php
/**
* Examples for the CPSjqToolsWrapper class
*
* @author Jerry Ablan <jablan@pogostick.com>
* @version SVN: $Id$
* @package psYiiExtensions
* @subpackage Examples
* @since 1.0.4
*/

/***
* Example file for CPSjqToolsWrapper class
* 
* To use this, render as a view from any controller
*/

//	Create some tabs...
CPSjqToolsWrapper::create( 'tabs', array( 'target' => 'ul.tabs', 'paneClass' => 'panes' ) );

//	Create a tooltip
CPSjqToolsWrapper::create( 'tooltip', array( 'target' => '#trigger' ) );

//	Create form tooltips
CPSjqToolsWrapper::create( 'tooltip', 
	array( 
		'target' => '#myform :input', 
		'position' => array( 'center', 'right' ), 
		'offset' => array( -2, 10 ), 
		'effect' => 'toggle', 
		'opacity' => 0.7 
	) 
);

//	Create a tooltip
CPSjqToolsWrapper::create( 'scrollable', array( 'target' => 'div.scrollable' ) );

//	Create a tooltip
CPSjqToolsWrapper::create( 'overlay', array( 'target' => 'button[rel]' ) );

//	Create a tooltip
CPSjqToolsWrapper::create( 'expose', array( 'target' => '#test' ) );

?>
<h1>jQuery Tools Example Page</h1>

<p>This page demonstrates the use of the CPSjqToolsWrapper class from the Pogostick Yii Extensions Library.</p>

<h2>Tabs</h2>
<!-- the tabs --> 
<ul class="tabs"> 
    <li><a href="#">Tab 1</a></li> 
    <li><a href="#">Tab 2</a></li> 
    <li><a href="#">Tab 3</a></li> 
</ul> 
 
<!-- tab "panes" --> 
<div class="panes"> 
    <div>First tab content. Tab contents are called "panes"</div> 
    <div>Second tab content</div> 
    <div>Third tab content</div> 
</div>

<h2>Tooltips</h2>
<!-- trigger element --> 
<a href="#" id="trigger"> 
	Move the mouse over this box to see the tooltip in action.
</a> 
 
<!-- tooltip element --> 
<div class="tooltip"> 
	<h3>The Tooltip</h3> 
 
	<p> 
		<strong>A great tool for designing better layouts and more intuitive user-interfaces.</strong> 
	</p> 
 
	<p> 
		Tooltips can contain any HTML such as links, images, forms and tables.
		This tooltip is vertically centered on the top edge of the trigger element.
	</p> 
</div> 

<style> 
/* simple css-based tooltip */
div.tooltipg {
	background-color:#000;
	outline:1px solid #669;
	border:2px solid #fff;
	padding:10px 15px;
	width:200px;
	display:none;
	color:#fff;
	text-align:left;
	font-size:12px;

	/* outline radius for mozilla/firefox only */
	outline-radius:4px;
	-moz-outline-radius:4px;
	-webkit-outline-radius:4px;
}

#myform {
	border:1px outset #ccc;
	background:#fff url(http://static.flowplayer.org/img/global/gradient/h300.png) repeat-x;
	padding:20px;
	margin:20px 0;
	text-align:center;
	width:350px;
	-moz-border-radius:4px;
}
 
#myform h3 {
	margin:0 0 10px 0;
}
 
/* http://www.quirksmode.org/css/forms.html */
label, input, textarea {
	display: block;
	width: 150px;
	float: left;
	margin-bottom: 20px;
}
 
label {
	text-align: right;
	width: 75px;
	padding-right: 20px;
}
 
br {
	clear: left;
}
</style> 

<h2>Form Tooltips</h2>
<form id="myform"> 
 
	<h3>Registration Form</h3> 
 
	<!-- username --> 
	<label for="username">Username</label> 
	<input id="username" /> 
	<div class="tooltipg">Must be at least 8 characters.</div><br/> 
 
	<!-- password --> 
	<label for="password">Password</label> 
	<input id="password" type="password" /> 
	<div class="tooltipg">Try to make it hard to guess.</div><br /> 
 
	<!-- email --> 
	<label for="username">Email</label> 
	<input id="email" /> 
	<div class="tooltipg">We won't send you any marketing material.</div><br /> 
 
	<!-- message --> 
	<label for="body">Message</label> 
	<textarea id="body"></textarea> 
	<div class="tooltipg">What's on your mind?</div><br /> 
 
</form> 

<h2>Scrollable</h2>
<!-- root element for scrollable --> 
<div class="scrollable"> 
     
    <!-- root element for the items --> 
    <div class="items"> 
        <div>0</div>  <div>1</div>  <div>2</div>  <div>3</div>  <div>4</div>  
        <div>5</div>  <div>6</div>  <div>7</div>  <div>8</div>  <div>9</div>  
        <div>10</div> <div>11</div> <div>12</div> <div>13</div> <div>14</div>  
    </div> 
     
</div>

<h2>Overlay</h2>
<!-- a button that triggers the overlay. the 'rel' attribute is a jQuery selector to the overlay trigger --> 
<button type="button" rel="#overlay">Open overlay</button>

<!-- overlayed element, which is styled with external stylesheet --> 
<div class="overlay" id="overlay"> 
 
    <!-- here is the content for overlay, can be anything --> 
    <h2 style="margin:10px 0">Here is my overlay</h2> 
 
    <p style="float: left; margin:0px 20px 0 0;"> 
        <img src="http://static.flowplayer.org/img/title/eye192.png" /> 
    </p> 
 
    <p> 
         Class aptent taciti sociosqu ad litora torquent per conubia nostra, 
         per inceptos himenaeos. Donec lorem ligula, elementum vitae, 
         imperdiet a, posuere nec, ante. Quisque mattis massa id metus. 
    </p> 
 
</div>

<h2>Expose</h2>
<style> 
/* the styling of the exposed element */
#test {
	border:1px solid #ccc;
	background-color:#fff;
	padding:50px;
	font-size:30px;
	margin:20px auto;
	text-align:center;
	width:600px;
}
</style> 
<div id="test"> 
    Click on this element to expose it. 
</div>
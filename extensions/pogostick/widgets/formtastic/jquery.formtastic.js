/* 
 * jQuery UI Formtastic! 1.0a
 *
 * Copyright (c) 2010 Jerry Ablan <jablan@pogostick.com>.
 * @link http://www.pogostick.com Pogostick, LLC.
 *
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * @license http://jquery.org/license
 * 
 * We share the same open source ideals as does the jQuery team, and
 * we love them so much we like to quote their license statement:
 * 
 * You may use our open source libraries under the terms of either the MIT
 * License or the Gnu General Public License (GPL) Version 2.
 * 
 * The MIT License is recommended for most projects. It is simple and easy to
 * understand, and it places almost no restrictions on what you can do with
 * our code.
 * 
 * If the GPL suits your project better, you are also free to use our code
 * under that license.
 * 
 * You don’t have to do anything special to choose one license or the other,
 * and you don’t have to notify anyone which license you are using.
 *
 * Depends:
 *   jquery.ui.core.js
 *   jquery.ui.widget.js
 */
(function($, undefined) {

	$.widget( 'ui.formtastic', {

		//********************************************************************************
		//* Default Options
		//********************************************************************************

		_options : {
		},

		//********************************************************************************
		//* Variables
		//********************************************************************************

		//********************************************************************************
		//* Methods
		//********************************************************************************

		/**
		 * Make our awesome form!
		 */
		_create: function() {
			var _this = this;
			var _form = _this.element;
			var _elements = _form.find( 'input,select,textarea' );

			_form.addClass('ui-widget ui-formtastic');
			$('fieldset',_form).addClass('ui-widget-content');
			$('legend',_form).addClass('ui-widget-header ui-corner-all');

			$.each(_elements,function() {
				$(this).addClass('ui-state-default ui-corner-all');
				$(this).wrap('<label />');

				if($(this).is(':reset ,:submit'))
					_this._buttons(this);
				else if($(this).is(':checkbox'))
					_this._checkBoxes(this);
				else if($(this).is('input[type="text"]') || $(this).is('textarea')||$(this).is('input[type="password"]'))
					_this._textBoxes(this);
				else if($(this).is(':radio'))
					_this._radioButtons(this);
				else if($(this).is('select'))
					_this._dropDowns(this);

				if($(this).hasClass('date')) {
					$(this).datepicker();
				}
			});

			$('.hover').hover(
				function() {
					$(this).addClass('ui-state-hover');
				},
				function() {
					$(this).removeClass('ui-state-hover');
				}
			);
		},

		/**
		 * Choose your destructor!
		 */
		destroy : function() {
			$.Widget.prototype.destroy.apply( this, arguments );
		},

		/**
		 * Awesome text boxes
		 */
		_textBoxes : function( element ) {
			$(element).bind({
				focusin : function() {
					$(this).toggleClass('ui-state-focus');
				},
				focusout: function() {
					$(this).toggleClass('ui-state-focus');
				}
			});
		 },

		/**
		 * Awesome buttons
		 */
		_buttons : function( element ) {
			if($(element).is(':submit')) {
				$(element).addClass('ui-priority-primary ui-corner-all ui-state-disabled hover');

				$(element).bind('click',function(e) {
					e.preventDefault();
				});
			} else if($(element).is(':reset')) {
				$(element).addClass('ui-priority-secondary ui-corner-all hover');
			}

			$(element).bind('mousedown mouseup', function() {
				$(this).toggleClass('ui-state-active');
			});
		},

		 /**
		  * Awesome check boxes
		  */
		 _checkBoxes : function( element ) {
			 $(element).parent('label').after('<span />');

			 var _parent =  $(element).parent('label').next();

			 $(element).addClass('ui-helper-hidden');

			 _parent.css({
				 width : 16,
				 height : 16,
				 display : 'block'
			 });

			_parent.wrap('<span class="ui-state-default ui-corner-all" style="display:inline-block;width:16px;height:16px;margin-right:5px;" />');
			_parent.parent().addClass('hover');
			_parent.parent('span').click(function(e) {
				$(this).toggleClass('ui-state-active');
				_parent.toggleClass('ui-icon ui-icon-check');
				$(element).click();
			});
		},

		/**
		 * Awesome radio buttons
		 */
		_radioButtons : function( element ) {
			$(element).parent('label').after('<span />');
			var _parent =  $(element).parent('label').next();

			$(element).addClass('ui-helper-hidden');
			_parent.addClass('ui-icon ui-icon-radio-off');
			_parent.wrap('<span class="ui-state-default ui-corner-all" style="display:inline-block;width:16px;height:16px;margin-right:5px;" />');
			_parent.parent().addClass('hover');
			_parent.parent('span').click(function(e) {
				$(this).toggleClass('ui-state-active');
				_parent.toggleClass('ui-icon-radio-off ui-icon-bullet');
				$(element).click();
			});
		},

		/**
		 * Awesome drop-downs
		 */
		_dropDowns : function( element ) {
			var _parent = $(element).parent();
			_parent
				.css({
					'display' : 'block',
					width : 140,
					height : 21
				})
				.addClass('ui-state-default ui-corner-all');

			$(element).addClass('ui-helper-hidden');

			_parent.append('<span id="_labelText" style="float:left;"></span><span style="float:right;display:inline-block" class="ui-icon ui-icon-triangle-1-s" ></span>');
			_parent.after('<ul class="ui-helper-reset ui-widget-content ui-helper-hidden" style="position:absolute;z-index:50;width:140px;"></ul>');

			$.each($('option',element),function() {
				$(_parent).next('ul').append('<li class="hover">'+$(this).html()+'</li>');
			});

			$(_parent).next('ul').find('li').click(function(){
				$('#_labelText').html($(this).html());
				$(element).val($(this).html());
			});

			$(_parent).click(function(e) {
				$(this).next().slideToggle('fast');
				e.preventDefault();
			});
		}
	});

	$.extend( $.ui.formtastic, {
		version: "1.0a"
	});

})( jQuery );

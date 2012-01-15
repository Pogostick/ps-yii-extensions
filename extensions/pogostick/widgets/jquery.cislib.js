//	$Id$

/**
 * jquery.cislib.js 1.0.0
 *
 * CIS jQuery plugin/uility library for front-end applications
 *
 * Copyright (c) 2010 Silverpop Systems, Inc. <cis@jablan@pogostick.com>
 * @author Jerry Ablan <jablan@jablan@pogostick.com>
 * @package sp.cis
 * @subpackage js
 * @version 1.0.0
 * @filesource
 */

(function($) {

	//********************************************************************************
	//* Main Entry Point
	//********************************************************************************

	$.fn.cislib = function( opts ) {
		
		//	InstaFail!
		if ( ! this.length ) {
			return this
		};
		
		//	Handle named methods
		if ( typeof arguments[0] == 'string' ) {
			if ( this.length> 1 ) {
				var _args = arguments;

				return this.each(function() {
					$.fn.cislib.apply( $(this), _args );
				});
			};

			//	Invoke method handler
			$.fn.cislib[arguments[0]].apply( this, $.makeArray( arguments ).slice( 1 ) || [] );

			//	Scoot...
			return this;
		};

		//	Initialize options for this call
		var options = $.extend( {}, $.fn.cislib.defaults, opts || {} );

		//	Invoke our error handler if still viable...
		if ( $.isFunction( $.fn.cislib.ajaxErrorHandler ) ) {
			$.fn.cislib.ajaxErrorHandler();
		}

		//	Loop through each element and return
		return this.each(function() {
			//	Variables...
			var $this = $(this);

			//	Metadata plugin support...
			options = $.metadata ? $.extend( {}, options, $this.data() ) : options;

		});	//	Each element
	};

	//********************************************************************************
	//* Default Options
	//********************************************************************************

	$.fn.cislib.defaults = {
		lastAjaxErrorMessage : null,

		ajaxErrorHandler : function() {
			var _msg = null;

			$.ajaxSetup({
				success : $.fn.cislib.lastAjaxErrorMessage = null,
				error: function( xhrObject, statusText ) {
					switch ( xhrObject.status ) {
						case 0:
							_msg = 'No network connection available';
							break;

						case 404:
							_msg = 'Requested URL not found';
							break;

						case 500:
							_msg = 'Internal server error';
							break;

						default:
							switch ( statusText ) {
								case 'parsererror':
									_msg = 'Error parsing of JSON request/response failed';
									break;

								case 'timeout':
									_msg = 'Request timed out';
									break;

								default:
									_msg = 'Unknown error: ' . xhrObject.responseText;
									break;
							}
					}

					//	Set
					$.fn.cislib.lastAjaxErrorMessage = _msg;
				}
			});
		}
	};

	//********************************************************************************
	//* Default Implementation
	//********************************************************************************

	$(function() {
		$(document.body).cislib();
	});

})( jQuery );



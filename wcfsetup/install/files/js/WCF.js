"use strict";

/**
 * Class and function collection for WCF.
 * 
 * Major Contributors: Markus Bartz, Tim Duesterhus, Matthias Schmidt and Marcel Werk
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

(function() {
	// store original implementation
	var $jQueryData = jQuery.fn.data;
	
	/**
	 * Override jQuery.fn.data() to support custom 'ID' suffix which will
	 * be translated to '-id' at runtime.
	 * 
	 * @see	jQuery.fn.data()
	 */
	jQuery.fn.data = function(key, value) {
		var args = [].slice.call(arguments)
		
		if (key) {
			switch (typeof key) {
				case 'object':
					for (var $key in key) {
						if ($key.match(/ID$/)) {
							var $value = key[$key];
							delete key[$key];
							
							$key = $key.replace(/ID$/, '-id');
							key[$key] = $value;
						}
					}
					
					args[0] = key;
				break;
				
				case 'string':
					if (key.match(/ID$/)) {
						args[0] = key.replace(/ID$/, '-id');
					}
				break;
			}
		}
		
		// call jQuery's own data method
		var $data = $jQueryData.apply(this, args);
		
		// handle .data() call without arguments
		if (key === undefined) {
			for (var $key in $data) {
				if ($key.match(/Id$/)) {
					$data[$key.replace(/Id$/, 'ID')] = $data[$key];
					delete $data[$key];
				}
			}
		}
		
		return $data;
	};
	
	// provide a sane window.console implementation
	if (!window.console) window.console = { };
	var consoleProperties = [ "log",/* "debug",*/ "info", "warn", "exception", "assert", "dir", "dirxml", "trace", "group", "groupEnd", "groupCollapsed", "profile", "profileEnd", "count", "clear", "time", "timeEnd", "timeStamp", "table", "error" ];
	for (var i = 0; i < consoleProperties.length; i++) {
		if (typeof (console[consoleProperties[i]]) === 'undefined') {
			console[consoleProperties[i]] = function () { };
		}
	}
	
	if (typeof(console.debug) === 'undefined') {
		// forward console.debug to console.log (IE9)
		console.debug = function(string) { console.log(string); };
	}
})();

/**
 * Adds a Fisher-Yates shuffle algorithm for arrays.
 * 
 * @see	http://stackoverflow.com/a/2450976
 */
window.shuffle = function(array) {
	var currentIndex = array.length, temporaryValue, randomIndex;
	
	// While there remain elements to shuffle...
	while (0 !== currentIndex) {
		// Pick a remaining element...
		randomIndex = Math.floor(Math.random() * currentIndex);
		currentIndex -= 1;
		
		// And swap it with the current element.
		temporaryValue = array[currentIndex];
		array[currentIndex] = array[randomIndex];
		array[randomIndex] = temporaryValue;
	}
	
	return this;
};

/**
 * User-Agent based browser detection and touch detection.
 */
(function(jQuery) {
	var ua = navigator.userAgent.toLowerCase();
	var match = /(chrome)[ \/]([\w.]+)/.exec( ua ) ||
		/(webkit)[ \/]([\w.]+)/.exec( ua ) ||
		/(opera)(?:.*version|)[ \/]([\w.]+)/.exec( ua ) ||
		/(msie) ([\w.]+)/.exec( ua ) ||
		ua.indexOf("compatible") < 0 && /(mozilla)(?:.*? rv:([\w.]+)|)/.exec( ua ) ||
		[];
	
	var matched = {
		browser: match[ 1 ] || "",
		version: match[ 2 ] || "0"
	};
	var browser = {};
	
	if ( matched.browser ) {
		browser[ matched.browser ] = true;
		browser.version = matched.version;
	}
	
	// Chrome is Webkit, but Webkit is also Safari.
	if ( browser.chrome ) {
		browser.webkit = true;
	} else if ( browser.webkit ) {
		browser.safari = true;
	}
	
	jQuery.browser = jQuery.browser || { };
	jQuery.browser = $.extend(jQuery.browser, browser);
	jQuery.browser.touch = (!!('ontouchstart' in window) || (!!('msMaxTouchPoints' in window.navigator) && window.navigator.msMaxTouchPoints > 0));
	
	// detect smartphones
	jQuery.browser.smartphone = ($('html').css('caption-side') == 'bottom');
	
	// properly detect IE11
	if (jQuery.browser.mozilla && ua.match(/trident/)) {
		jQuery.browser.mozilla = false;
		jQuery.browser.msie = true;
	}
	
	// detect iOS devices
	jQuery.browser.iOS = /\((ipad|iphone|ipod);/.test(ua);
	if (jQuery.browser.iOS) {
		$('html').addClass('iOS');
	}
	
	// dectect Android
	jQuery.browser.android = (ua.indexOf('android') !== -1);
	
	// allow plugins to detect the used editor, value should be the same as the $.browser.<editorName> key
	jQuery.browser.editor = 'redactor';
	
	// CKEditor support (removed in WCF 2.1), do NOT remove this variable for the sake for compatibility
	jQuery.browser.ckeditor = false;
	
	// Redactor support
	jQuery.browser.redactor = true;
	
	// work-around for zoom bug on iOS when using .focus()
	if (jQuery.browser.iOS) {
		jQuery.fn.focus = function(data, fn) {
			return arguments.length > 0 ? this.on('focus', null, data, fn) : this.trigger('focus');
		};
	}
})(jQuery);

/**
 * Initialize WCF namespace
 */
// non strict equals by intent
if (window.WCF == null) window.WCF = { };

/**
 * Extends jQuery with additional methods.
 */
$.extend(true, {
	/**
	 * Removes the given value from the given array and returns the array.
	 * 
	 * @param	array		array
	 * @param	mixed		element
	 * @return	array
	 */
	removeArrayValue: function(array, value) {
		return $.grep(array, function(element, index) {
			return value !== element;
		});
	},
	
	/**
	 * Escapes an ID to work with jQuery selectors.
	 * 
	 * @see		http://docs.jquery.com/Frequently_Asked_Questions#How_do_I_select_an_element_by_an_ID_that_has_characters_used_in_CSS_notation.3F
	 * @param	string		id
	 * @return	string
	 */
	wcfEscapeID: function(id) {
		return id.replace(/(:|\.)/g, '\\$1');
	},
	
	/**
	 * Returns true if given ID exists within DOM.
	 * 
	 * @param	string		id
	 * @return	boolean
	 */
	wcfIsset: function(id) {
		return !!$('#' + $.wcfEscapeID(id)).length;
	},
	
	/**
	 * Returns the length of an object.
	 * 
	 * @param	object		targetObject
	 * @return	integer
	 */
	getLength: function(targetObject) {
		var $length = 0;
		
		for (var $key in targetObject) {
			if (targetObject.hasOwnProperty($key)) {
				$length++;
			}
		}
		
		return $length;
	}
});

/**
 * Extends jQuery's chainable methods.
 */
$.fn.extend({
	/**
	 * Returns tag name of first jQuery element.
	 * 
	 * @returns	string
	 */
	getTagName: function() {
		return (this.length) ? this.get(0).tagName.toLowerCase() : '';
	},
	
	/**
	 * Returns the dimensions for current element.
	 * 
	 * @see		http://api.jquery.com/hidden-selector/
	 * @param	string		type
	 * @return	object
	 */
	getDimensions: function(type) {
		var css = { };
		var dimensions = { };
		var wasHidden = false;
		
		// show element to retrieve dimensions and restore them later
		if (this.is(':hidden')) {
			css = WCF.getInlineCSS(this);
			
			wasHidden = true;
			
			this.css({
				display: 'block',
				visibility: 'hidden'
			});
		}
		
		switch (type) {
			case 'inner':
				dimensions = {
					height: this.innerHeight(),
					width: this.innerWidth()
				};
			break;
			
			case 'outer':
				dimensions = {
					height: this.outerHeight(),
					width: this.outerWidth()
				};
			break;
			
			default:
				dimensions = {
					height: this.height(),
					width: this.width()
				};
			break;
		}
		
		// restore previous settings
		if (wasHidden) {
			WCF.revertInlineCSS(this, css, [ 'display', 'visibility' ]);
		}
		
		return dimensions;
	},
	
	/**
	 * Returns the offsets for current element, defaults to position
	 * relative to document.
	 * 
	 * @see		http://api.jquery.com/hidden-selector/
	 * @param	string		type
	 * @return	object
	 */
	getOffsets: function(type) {
		var css = { };
		var offsets = { };
		var wasHidden = false;
		
		// show element to retrieve dimensions and restore them later
		if (this.is(':hidden')) {
			css = WCF.getInlineCSS(this);
			wasHidden = true;
			
			this.css({
				display: 'block',
				visibility: 'hidden'
			});
		}
		
		switch (type) {
			case 'offset':
				offsets = this.offset();
			break;
			
			case 'position':
			default:
				offsets = this.position();
			break;
		}
		
		// restore previous settings
		if (wasHidden) {
			WCF.revertInlineCSS(this, css, [ 'display', 'visibility' ]);
		}
		
		return offsets;
	},
	
	/**
	 * Changes element's position to 'absolute' or 'fixed' while maintaining it's
	 * current position relative to viewport. Optionally removes element from
	 * current DOM-node and moving it into body-element (useful for drag & drop)
	 * 
	 * @param	boolean		rebase
	 * @return	object
	 */
	makePositioned: function(position, rebase) {
		if (position != 'absolute' && position != 'fixed') {
			position = 'absolute';
		}
		
		var $currentPosition = this.getOffsets('position');
		this.css({
			position: position,
			left: $currentPosition.left,
			margin: 0,
			top: $currentPosition.top
		});
		
		if (rebase) {
			this.remove().appentTo('body');
		}
		
		return this;
	},
	
	/**
	 * Disables a form element.
	 * 
	 * @return	jQuery
	 */
	disable: function() {
		return this.attr('disabled', 'disabled');
	},
	
	/**
	 * Enables a form element.
	 * 
	 * @return	jQuery
	 */
	enable: function() {
		return this.removeAttr('disabled');
	},
	
	/**
	 * Returns the element's id. If none is set, a random unique
	 * ID will be assigned.
	 * 
	 * @return	string
	 */
	wcfIdentify: function() {
		return window.bc_wcfDomUtil.identify(this[0]);
	},
	
	/**
	 * Returns the caret position of current element. If the element
	 * does not equal input[type=text], input[type=password] or
	 * textarea, -1 is returned.
	 * 
	 * @return	integer
	 */
	getCaret: function() {
		if (this.is('input')) {
			if (this.attr('type') != 'text' && this.attr('type') != 'password') {
				return -1;
			}
		}
		else if (!this.is('textarea')) {
			return -1;
		}
		
		var $position = 0;
		var $element = this.get(0);
		if (document.selection) { // IE 8
			// set focus to enable caret on this element
			this.focus();
			
			var $selection = document.selection.createRange();
			$selection.moveStart('character', -this.val().length);
			$position = $selection.text.length;
		}
		else if ($element.selectionStart || $element.selectionStart == '0') { // Opera, Chrome, Firefox, Safari, IE 9+
			$position = parseInt($element.selectionStart);
		}
		
		return $position;
	},
	
	/**
	 * Sets the caret position of current element. If the element
	 * does not equal input[type=text], input[type=password] or
	 * textarea, false is returned.
	 * 
	 * @param	integer		position
	 * @return	boolean
	 */
	setCaret: function (position) {
		if (this.is('input')) {
			if (this.attr('type') != 'text' && this.attr('type') != 'password') {
				return false;
			}
		}
		else if (!this.is('textarea')) {
			return false;
		}
		
		var $element = this.get(0);
		
		// set focus to enable caret on this element
		this.focus();
		if (document.selection) { // IE 8
			var $selection = document.selection.createRange();
			$selection.moveStart('character', position);
			$selection.moveEnd('character', 0);
			$selection.select();
		}
		else if ($element.selectionStart || $element.selectionStart == '0') { // Opera, Chrome, Firefox, Safari, IE 9+
			$element.selectionStart = position;
			$element.selectionEnd = position;
		}
		
		return true;
	},
	
	/**
	 * Shows an element by sliding and fading it into viewport.
	 * 
	 * @param	string		direction
	 * @param	object		callback
	 * @param	integer		duration
	 * @returns	jQuery
	 */
	wcfDropIn: function(direction, callback, duration) {
		if (!direction) direction = 'up';
		if (!duration || !parseInt(duration)) duration = 200;
		
		return this.show(WCF.getEffect(this, 'drop'), { direction: direction }, duration, callback);
	},
	
	/**
	 * Hides an element by sliding and fading it out the viewport.
	 * 
	 * @param	string		direction
	 * @param	object		callback
	 * @param	integer		duration
	 * @returns	jQuery
	 */
	wcfDropOut: function(direction, callback, duration) {
		if (!direction) direction = 'down';
		if (!duration || !parseInt(duration)) duration = 200;
		
		return this.hide(WCF.getEffect(this, 'drop'), { direction: direction }, duration, callback);
	},
	
	/**
	 * Shows an element by blinding it up.
	 * 
	 * @param	string		direction
	 * @param	object		callback
	 * @param	integer		duration
	 * @returns	jQuery
	 */
	wcfBlindIn: function(direction, callback, duration) {
		if (!direction) direction = 'vertical';
		if (!duration || !parseInt(duration)) duration = 200;
		
		return this.show(WCF.getEffect(this, 'blind'), { direction: direction }, duration, callback);
	},
	
	/**
	 * Hides an element by blinding it down.
	 * 
	 * @param	string		direction
	 * @param	object		callback
	 * @param	integer		duration
	 * @returns	jQuery
	 */
	wcfBlindOut: function(direction, callback, duration) {
		if (!direction) direction = 'vertical';
		if (!duration || !parseInt(duration)) duration = 200;
		
		return this.hide(WCF.getEffect(this, 'blind'), { direction: direction }, duration, callback);
	},
	
	/**
	 * Highlights an element.
	 * 
	 * @param	object		options
	 * @param	object		callback
	 * @returns	jQuery
	 */
	wcfHighlight: function(options, callback) {
		return this.effect('highlight', options, 600, callback);
	},
	
	/**
	 * Shows an element by fading it in.
	 * 
	 * @param	object		callback
	 * @param	integer		duration
	 * @returns	jQuery
	 */
	wcfFadeIn: function(callback, duration) {
		if (!duration || !parseInt(duration)) duration = 200;
		
		return this.show(WCF.getEffect(this, 'fade'), { }, duration, callback);
	},
	
	/**
	 * Hides an element by fading it out.
	 * 
	 * @param	object		callback
	 * @param	integer		duration
	 * @returns	jQuery
	 */
	wcfFadeOut: function(callback, duration) {
		if (!duration || !parseInt(duration)) duration = 200;
		
		return this.hide(WCF.getEffect(this, 'fade'), { }, duration, callback);
	},
	
	/**
	 * Returns a CSS property as raw number.
	 * 
	 * @param	string		property
	 */
	cssAsNumber: function(property) {
		if (this.length) {
			var $property = this.css(property);
			if ($property !== undefined) {
				return parseInt($property.replace(/px$/, ''));
			}
		}
		
		return 0;
	},
	/**
	 * @deprecated Use perfectScrollbar directly.
	 * 
	 * This is taken from the jQuery adaptor of perfect scrollbar.
	 * Copyright (c) 2015 Hyunje Alex Jun and other contributors
	 * Licensed under the MIT License
	 */
	perfectScrollbar: function (settingOrCommand) {
	    var ps = require('perfect-scrollbar');
	    
	    return this.each(function () {
	      if (typeof settingOrCommand === 'object' ||
	          typeof settingOrCommand === 'undefined') {
	        // If it's an object or none, initialize.
	        var settings = settingOrCommand;
	        if (!$(this).data('psID'))
	          ps.initialize(this, settings);
	      } else {
	        // Unless, it may be a command.
	        var command = settingOrCommand;

	        if (command === 'update') {
	          ps.update(this);
	        } else if (command === 'destroy') {
	          ps.destroy(this);
	        }
	      }

	      return jQuery(this);
	    });
	}
});

/**
 * WoltLab Suite Core methods
 */
$.extend(WCF, {
	/**
	 * count of active dialogs
	 * @var	integer
	 */
	activeDialogs: 0,
	
	/**
	 * Counter for dynamic element ids
	 * 
	 * @var	integer
	 */
	_idCounter: 0,
	
	/**
	 * Returns a dynamically created id.
	 * 
	 * @see		https://github.com/sstephenson/prototype/blob/5e5cfff7c2c253eaf415c279f9083b4650cd4506/src/prototype/dom/dom.js#L1789
	 * @return	string
	 */
	getRandomID: function() {
		return window.bc_wcfDomUtil.getUniqueId();
	},
	
	/**
	 * Wrapper for $.inArray which returns boolean value instead of
	 * index value, similar to PHP's in_array().
	 * 
	 * @param	mixed		needle
	 * @param	array		haystack
	 * @return	boolean
	 */
	inArray: function(needle, haystack) {
		return ($.inArray(needle, haystack) != -1);
	},
	
	/**
	 * Adjusts effect for partially supported elements.
	 * 
	 * @param	jQuery		object
	 * @param	string		effect
	 * @return	string
	 */
	getEffect: function(object, effect) {
		// most effects are not properly supported on table rows, use highlight instead
		if (object.is('tr')) {
			return 'highlight';
		}
		
		return effect;
	},
	
	/**
	 * Returns inline CSS for given element.
	 * 
	 * @param	jQuery		element
	 * @return	object
	 */
	getInlineCSS: function(element) {
		var $inlineStyles = { };
		var $style = element.attr('style');
		
		// no style tag given or empty
		if (!$style) {
			return { };
		}
		
		$style = $style.split(';');
		for (var $i = 0, $length = $style.length; $i < $length; $i++) {
			var $fragment = $.trim($style[$i]);
			if ($fragment == '') {
				continue;
			}
			
			$fragment = $fragment.split(':');
			$inlineStyles[$.trim($fragment[0])] = $.trim($fragment[1]);
		}
		
		return $inlineStyles;
	},
	
	/**
	 * Reverts inline CSS or negates a previously set property.
	 * 
	 * @param	jQuery		element
	 * @param	object		inlineCSS
	 * @param	array<string>	targetProperties
	 */
	revertInlineCSS: function(element, inlineCSS, targetProperties) {
		for (var $i = 0, $length = targetProperties.length; $i < $length; $i++) {
			var $property = targetProperties[$i];
			
			// revert inline CSS
			if (inlineCSS[$property]) {
				element.css($property, inlineCSS[$property]);
			}
			else {
				// negate inline CSS
				element.css($property, '');
			}
		}
	},
	
	/**
	 * @deprecated Use WoltLabSuite/Core/Core.getUuid().
	 */
	getUUID: function() {
		return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
			var r = Math.random()*16|0, v = c == 'x' ? r : (r&0x3|0x8);
			return v.toString(16);
		});
	},
	
	/**
	 * Converts a base64 encoded file into a native Blob.
	 * 
	 * @param	string		base64data
	 * @param	string		contentType
	 * @param	integer		sliceSize
	 * @return	Blob
	 */
	base64toBlob: function(base64data, contentType, sliceSize) {
		contentType = contentType || '';
		sliceSize = sliceSize || 512;
		
		var $byteCharacters = atob(base64data);
		var $byteArrays = [ ];
		
		for (var $offset = 0; $offset < $byteCharacters.length; $offset += sliceSize) {
			var $slice = $byteCharacters.slice($offset, $offset + sliceSize);
			
			var $byteNumbers = new Array($slice.length);
			for (var $i = 0; $i < $slice.length; $i++) {
				$byteNumbers[$i] = $slice.charCodeAt($i);
			}
			
			var $byteArray = new Uint8Array($byteNumbers);
			$byteArrays.push($byteArray);
		}
		
		return new Blob($byteArrays, { type: contentType });
	},
	
	/**
	 * Converts legacy URLs to the URL schema used by WCF 2.1.
	 * 
	 * @param	string		url
	 * @return	string
	 */
	convertLegacyURL: function(url) {
		return url.replace(/^index\.php\/(.*?)\/\?/, function(match, controller) {
			var $parts = controller.split(/([A-Z][a-z0-9]+)/);
			var $controller = '';
			for (var $i = 0, $length = $parts.length; $i < $length; $i++) {
				var $part = $parts[$i].trim();
				if ($part.length) {
					if ($controller.length) $controller += '-';
					$controller += $part.toLowerCase();
				}
			}
			
			return 'index.php?' + $controller + '/&';
		});
	}
});

/**
 * Browser related functions.
 */
WCF.Browser = {
	/**
	 * determines if browser is chrome
	 * @var	boolean
	 */
	_isChrome: null,
	
	/**
	 * Returns true, if browser is Chrome, Chromium or using GoogleFrame for Internet Explorer.
	 * 
	 * @return	boolean
	 */
	isChrome: function() {
		if (this._isChrome === null) {
			this._isChrome = false;
			if (/chrom(e|ium)/.test(navigator.userAgent.toLowerCase())) {
				this._isChrome = true;
			}
		}
		
		return this._isChrome;
	}
};

/**
 * Dropdown API
 * 
 * @deprecated	3.0 - please use `Ui/SimpleDropdown` instead
 */
WCF.Dropdown = {
	/**
	 * Initializes dropdowns.
	 */
	init: function(api) {
		window.bc_wcfSimpleDropdown.initAll();
	},
	
	/**
	 * Initializes a dropdown.
	 * 
	 * @param	{jQuery}		button
	 * @param	{boolean|Event}		isLazyInitialization
	 */
	initDropdown: function(button, isLazyInitialization) {
		window.bc_wcfSimpleDropdown.init(button[0], isLazyInitialization);
	},
	
	/**
	 * Removes the dropdown with the given container id.
	 * 
	 * @param	string		containerID
	 */
	removeDropdown: function(containerID) {
		window.bc_wcfSimpleDropdown.destroy(containerID);
	},
	
	/**
	 * Initializes a dropdown fragment which behaves like a usual dropdown
	 * but is not controlled by a trigger element.
	 * 
	 * @param	jQuery		dropdown
	 * @param	jQuery		dropdownMenu
	 */
	initDropdownFragment: function(dropdown, dropdownMenu) {
		window.bc_wcfSimpleDropdown.initFragment(dropdown[0], dropdownMenu[0]);
	},
	
	/**
	 * Registers a callback notified upon dropdown state change.
	 * 
	 * @param	string		identifier
	 * @var		object		callback
	 */
	registerCallback: function(identifier, callback) {
		window.bc_wcfSimpleDropdown.registerCallback(identifier, callback);
	},
	
	/**
	 * Toggles a dropdown.
	 * 
	 * @param	object		event
	 * @param	string		targetID
	 */
	_toggle: function(event, targetID) {
		window.bc_wcfSimpleDropdown._toggle(event, targetID);
	},
	
	/**
	 * Toggles a dropdown.
	 * 
	 * @param	string		containerID
	 * @param       {boolean=}      disableAutoFocus
	 */
	toggleDropdown: function(containerID, disableAutoFocus) {
		window.bc_wcfSimpleDropdown._toggle(null, containerID, null, disableAutoFocus);
	},
	
	/**
	 * Returns dropdown by container id.
	 * 
	 * @param	string		containerID
	 * @return	jQuery
	 */
	getDropdown: function(containerID) {
		var dropdown = window.bc_wcfSimpleDropdown.getDropdown(containerID);
		
		return (dropdown) ? $(dropdown) : null;
	},
	
	/**
	 * Returns dropdown menu by container id.
	 * 
	 * @param	string		containerID
	 * @return	jQuery
	 */
	getDropdownMenu: function(containerID) {
		var menu = window.bc_wcfSimpleDropdown.getDropdownMenu(containerID);
		
		return (menu) ? $(menu) : null;
	},
	
	/**
	 * Sets alignment for given container id.
	 * 
	 * @param	string		containerID
	 */
	setAlignmentByID: function(containerID) {
		window.bc_wcfSimpleDropdown.setAlignmentById(containerID);
	},
	
	/**
	 * Sets alignment for dropdown.
	 * 
	 * @param	jQuery		dropdown
	 * @param	jQuery		dropdownMenu
	 */
	setAlignment: function(dropdown, dropdownMenu) {
		window.bc_wcfSimpleDropdown.setAlignment(dropdown[0], dropdownMenu[0]);
	},
	
	/**
	 * Closes all dropdowns.
	 */
	_closeAll: function() {
		window.bc_wcfSimpleDropdown.closeAll();
	},
	
	/**
	 * Closes a dropdown without notifying callbacks.
	 * 
	 * @param	string		containerID
	 */
	close: function(containerID) {
		window.bc_wcfSimpleDropdown.close(containerID);
	},
	
	/**
	 * Destroies an existing dropdown menu.
	 * 
	 * @param	string		containerID
	 * @return	boolean
	 */
	destroy: function(containerID) {
		window.bc_wcfSimpleDropdown.destroy(containerID);
	}
};

/**
 * Namespace for interactive dropdowns.
 */
WCF.Dropdown.Interactive = { };

if (COMPILER_TARGET_DEFAULT) {
	/**
	 * General interface to create and manage interactive dropdowns.
	 */
	WCF.Dropdown.Interactive.Handler = {
		/**
		 * global container for interactive dropdowns
		 * @var        jQuery
		 */
		_dropdownContainer: null,
		
		/**
		 * list of dropdown instances by identifier
		 * @var        object<WCF.Dropdown.Interactive.Instance>
		 */
		_dropdownMenus: {},
		
		/**
		 * Creates a new interactive dropdown instance.
		 *
		 * @param        jQuery                triggerElement
		 * @param        string                identifier
		 * @param        object                options
		 * @return        WCF.Dropdown.Interactive.Instance
		 */
		create: function (triggerElement, identifier, options) {
			if (this._dropdownContainer === null) {
				this._dropdownContainer = $('<div class="dropdownMenuContainer" />').appendTo(document.body);
				WCF.CloseOverlayHandler.addCallback('WCF.Dropdown.Interactive.Handler', $.proxy(this.closeAll, this));
			}
			
			var $instance = new WCF.Dropdown.Interactive.Instance(this._dropdownContainer, triggerElement, identifier, options);
			this._dropdownMenus[identifier] = $instance;
			
			return $instance;
		},
		
		/**
		 * Opens an interactive dropdown, returns false if identifier is unknown.
		 *
		 * @param        string                identifier
		 * @return        boolean
		 */
		open: function (identifier) {
			if (this._dropdownMenus[identifier]) {
				this._dropdownMenus[identifier].open();
				
				return true;
			}
			
			return false;
		},
		
		/**
		 * Closes an interactive dropdown, returns false if identifier is unknown.
		 *
		 * @param        string                identifier
		 * @return        boolean
		 */
		close: function (identifier) {
			if (this._dropdownMenus[identifier]) {
				this._dropdownMenus[identifier].close();
				
				return true;
			}
			
			return false;
		},
		
		/**
		 * Closes all interactive dropdowns.
		 */
		closeAll: function () {
			for (var instance in this._dropdownMenus) {
				if (this._dropdownMenus.hasOwnProperty(instance)) {
					this._dropdownMenus[instance].close();
				}
			}
		},
		
		getOpenDropdown: function () {
			for (var instance in this._dropdownMenus) {
				if (this._dropdownMenus.hasOwnProperty(instance)) {
					if (this._dropdownMenus[instance].isOpen()) {
						return this._dropdownMenus[instance];
					}
				}
			}
			
			return null;
		},
		
		/**
		 * Returns the dropdown with given identifier or `undefined` if no such dropdown exists.
		 *
		 * @param        string                identifier
		 * @return        {WCF.Dropdown.Interactive.Instance?}
		 */
		getDropdown: function (identifier) {
			return this._dropdownMenus[identifier];
		}
	};
	
	/**
	 * Represents and manages a single interactive dropdown instance.
	 *
	 * @param        jQuery                dropdownContainer
	 * @param        jQuery                triggerElement
	 * @param        string                identifier
	 * @param        object                options
	 */
	WCF.Dropdown.Interactive.Instance = Class.extend({
		/**
		 * dropdown container
		 * @var        jQuery
		 */
		_container: null,
		
		/**
		 * inner item list
		 * @var        jQuery
		 */
		_itemList: null,
		
		/**
		 * header link list
		 * @var        jQuery
		 */
		_linkList: null,
		
		/**
		 * option list
		 * @var        object
		 */
		_options: {},
		
		/**
		 * arrow pointer
		 * @var        jQuery
		 */
		_pointer: null,
		
		/**
		 * trigger element
		 * @var        jQuery
		 */
		_triggerElement: null,
		
		/**
		 * Represents and manages a single interactive dropdown instance.
		 *
		 * @param        jQuery                dropdownContainer
		 * @param        jQuery                triggerElement
		 * @param        string                identifier
		 * @param        object                options
		 */
		init: function (dropdownContainer, triggerElement, identifier, options) {
			this._options = options || {};
			this._triggerElement = triggerElement;
			
			var $itemContainer = null;
			if (options.staticDropdown === true) {
				this._container = this._triggerElement.find('.interactiveDropdownStatic:eq(0)').data('source', identifier).click(function (event) {
					event.stopPropagation();
				});
			}
			else {
				this._container = $('<div class="interactiveDropdown" data-source="' + identifier + '" />').click(function (event) {
					event.stopPropagation();
				});
				
				var $header = $('<div class="interactiveDropdownHeader" />').appendTo(this._container);
				$('<span class="interactiveDropdownTitle">' + options.title + '</span>').appendTo($header);
				this._linkList = $('<ul class="interactiveDropdownLinks inlineList"></ul>').appendTo($header);
				
				$itemContainer = $('<div class="interactiveDropdownItemsContainer" />').appendTo(this._container);
				this._itemList = $('<ul class="interactiveDropdownItems" />').appendTo($itemContainer);
				
				$('<a href="' + options.showAllLink + '" class="interactiveDropdownShowAll">' + WCF.Language.get('wcf.user.panel.showAll') + '</a>').appendTo(this._container);
			}
			
			this._pointer = $('<span class="elementPointer"><span /></span>').appendTo(this._container);
			
			require(['Environment'], (function (Environment) {
				if (Environment.platform() === 'desktop') {
					if ($itemContainer !== null) {
						// use jQuery scrollbar on desktop, mobile browsers have a similar display built-in
						$itemContainer.perfectScrollbar({
							suppressScrollX: true
						});
					}
				}
			}).bind(this));
			
			this._container.appendTo(dropdownContainer);
		},
		
		/**
		 * Returns the dropdown container.
		 *
		 * @return        jQuery
		 */
		getContainer: function () {
			return this._container;
		},
		
		/**
		 * Returns the inner item list.
		 *
		 * @return        jQuery
		 */
		getItemList: function () {
			return this._itemList;
		},
		
		/**
		 * Returns the header link list.
		 *
		 * @return        jQuery
		 */
		getLinkList: function () {
			return this._linkList;
		},
		
		/**
		 * Opens the dropdown.
		 */
		open: function () {
			WCF.Dropdown._closeAll();
			
			this._triggerElement.addClass('open');
			this._container.addClass('open');
			
			WCF.System.Event.fireEvent('com.woltlab.wcf.Search', 'close');
			
			this.render();
		},
		
		/**
		 * Closes the dropdown
		 */
		close: function () {
			this._triggerElement.removeClass('open');
			this._container.removeClass('open');
			
			WCF.System.Event.fireEvent('WCF.Dropdown.Interactive.Instance', 'close', {
				instance: this
			});
		},
		
		/**
		 * Returns true if dropdown instance is visible.
		 *
		 * @returns     {boolean}
		 */
		isOpen: function () {
			return this._triggerElement.hasClass('open');
		},
		
		/**
		 * Toggles the dropdown state, returns true if dropdown is open afterwards, else false.
		 *
		 * @return        boolean
		 */
		toggle: function () {
			if (this._container.hasClass('open')) {
				this.close();
				
				return false;
			}
			else {
				WCF.Dropdown.Interactive.Handler.closeAll();
				
				this.open();
				
				return true;
			}
		},
		
		/**
		 * Resets the inner item list and closes the dropdown.
		 */
		resetItems: function () {
			this._itemList.empty();
			
			this.close();
		},
		
		/**
		 * Renders the dropdown.
		 */
		render: function () {
			require(['Ui/Alignment', 'Ui/Screen'], (function (UiAlignment, UiScreen) {
				if (UiScreen.is('screen-lg')) {
					UiAlignment.set(this._container[0], this._triggerElement[0], {
						horizontal: 'right',
						pointer: true
					});
				}
				else {
					this._container.css({
						bottom: '',
						left: '',
						right: '',
						top: elById('pageHeaderPanel').clientHeight + 'px'
					});
				}
			}).bind(this));
		},
		
		/**
		 * Rebuilds the desktop scrollbar.
		 */
		rebuildScrollbar: function () {
			require(['Environment'], function (Environment) {
				if (Environment.platform() === 'desktop') {
					var $itemContainer = this._itemList.parent();
					
					// do NOT use 'update', seems to be broken
					$itemContainer.perfectScrollbar('destroy');
					$itemContainer.perfectScrollbar({
						suppressScrollX: true
					});
				}
			}.bind(this));
		}
	});
	
	/**
	 * Clipboard API
	 * 
	 * @deprecated	3.0 - please use `WoltLabSuite/Core/Controller/Clipboard` instead
	 */
	WCF.Clipboard = {
		/**
		 * Initializes the clipboard API.
		 * 
		 * @param	string		page
		 * @param	integer		hasMarkedItems
		 * @param	object		actionObjects
		 * @param	integer		pageObjectID
		 */
		init: function(page, hasMarkedItems, actionObjects, pageObjectID) {
			require(['EventHandler', 'WoltLabSuite/Core/Controller/Clipboard'], function(EventHandler, ControllerClipboard) {
				ControllerClipboard.setup({
					hasMarkedItems: (hasMarkedItems > 0),
					pageClassName: page,
					pageObjectId: pageObjectID
				});
				
				for (var type in actionObjects) {
					if (actionObjects.hasOwnProperty(type)) {
						(function (type) {
							EventHandler.add('com.woltlab.wcf.clipboard', type, function (data) {
								// only consider events if the action has been executed
								if (data.responseData === null) {
									return;
								}
								
								if (actionObjects[type].hasOwnProperty(data.responseData.actionName)) {
									actionObjects[type][data.responseData.actionName].triggerEffect(data.responseData.objectIDs);
								}
							});
						})(type);
					}
				}
			});
		},
		
		/**
		 * Reloads the list of marked items.
		 */
		reload: function() {
			require(['WoltLabSuite/Core/Controller/Clipboard'], function(ControllerClipboard) {
				ControllerClipboard.reload();
			});
		}
	};
}
else {
	WCF.Dropdown.Interactive.Handler = {
		_dropdownContainer: {},
		_dropdownMenus: {},
		create: function() {},
		open: function() {},
		close: function() {},
		closeAll: function() {},
		getOpenDropdown: function() {},
		getDropdown: function() {}
	};
	
	WCF.Dropdown.Interactive.Instance = Class.extend({
		_container: {},
		_itemList: {},
		_linkList: {},
		_options: {},
		_pointer: {},
		_triggerElement: {},
		init: function() {},
		getContainer: function() {},
		getItemList: function() {},
		getLinkList: function() {},
		open: function() {},
		close: function() {},
		isOpen: function() {},
		toggle: function() {},
		resetItems: function() {},
		render: function() {},
		rebuildScrollbar: function() {}
	});
	
	WCF.Clipboard = {
		init: function() {},
		reload: function() {}
	};
}

/**
 * @deprecated Use WoltLabSuite/Core/Timer/Repeating
 */
WCF.PeriodicalExecuter = Class.extend({
	/**
	 * callback for each execution cycle
	 * @var	object
	 */
	_callback: null,
	
	/**
	 * interval
	 * @var	integer
	 */
	_delay: 0,
	
	/**
	 * interval id
	 * @var	integer
	 */
	_intervalID: null,
	
	/**
	 * execution state
	 * @var	boolean
	 */
	_isExecuting: false,
	
	/**
	 * Initializes a periodical executer.
	 * 
	 * @param	function		callback
	 * @param	integer			delay
	 */
	init: function(callback, delay) {
		if (!$.isFunction(callback)) {
			console.debug('[WCF.PeriodicalExecuter] Given callback is invalid, aborting.');
			return;
		}
		
		this._callback = callback;
		this._interval = delay;
		this.resume();
	},
	
	/**
	 * Executes callback.
	 */
	_execute: function() {
		if (!this._isExecuting) {
			try {
				this._isExecuting = true;
				this._callback(this);
				this._isExecuting = false;
			}
			catch (e) {
				this._isExecuting = false;
				throw e;
			}
		}
	},
	
	/**
	 * Terminates loop.
	 */
	stop: function() {
		if (!this._intervalID) {
			return;
		}
		
		clearInterval(this._intervalID);
	},
	
	/**
	 * Resumes the interval-based callback execution.
	 * 
	 * @deprecated	2.1 - use restart() instead
	 */
	resume: function() {
		this.restart();
	},
	
	/**
	 * Restarts the interval-based callback execution.
	 */
	restart: function() {
		if (this._intervalID) {
			this.stop();
		}
		
		this._intervalID = setInterval($.proxy(this._execute, this), this._interval);
	},
	
	/**
	 * Sets the interval and restarts the interval.
	 * 
	 * @param	integer		interval
	 */
	setInterval: function(interval) {
		this._interval = interval;
		
		this.restart();
	}
});

/**
 * Handler for loading overlays
 * 
 * @deprecated	3.0 - Please use WoltLabSuite/Core/Ajax/Status
 */
WCF.LoadingOverlayHandler = {
	/**
	 * Adds one loading-request and shows the loading overlay if nessercery
	 */
	show: function() {
		require(['WoltLabSuite/Core/Ajax/Status'], function(AjaxStatus) {
			AjaxStatus.show();
		});
	},
	
	/**
	 * Removes one loading-request and hides loading overlay if there're no more pending requests
	 */
	hide: function() {
		require(['WoltLabSuite/Core/Ajax/Status'], function(AjaxStatus) {
			AjaxStatus.hide();
		});
	},
	
	/**
	 * Updates a icon to/from spinner
	 * 
	 * @param	jQuery	target
	 * @pram	boolean	loading
	 */
	updateIcon: function(target, loading) {
		var $method = (loading === undefined || loading ? 'addClass' : 'removeClass');
		
		target.find('.icon')[$method]('fa-spinner');
		if (target.hasClass('icon')) {
			target[$method]('fa-spinner');
		}
	}
};

/**
 * Namespace for AJAXProxies
 */
WCF.Action = {};

/**
 * Basic implementation for AJAX-based proxyies
 * 
 * @deprecated	3.0 - please use `WoltLabSuite/Core/Ajax.api()` instead
 * 
 * @param	object		options
 */
WCF.Action.Proxy = Class.extend({
	_ajaxRequest: null,
	
	/**
	 * Initializes AJAXProxy.
	 * 
	 * @param	object		options
	 */
	init: function(options) {
		this._ajaxRequest = null;
		
		options = $.extend(true, {
			autoSend: false,
			data: { },
			dataType: 'json',
			after: null,
			init: null,
			jsonp: 'callback',
			async: true,
			failure: null,
			showLoadingOverlay: true,
			success: null,
			suppressErrors: false,
			type: 'POST',
			url: 'index.php?ajax-proxy/&t=' + SECURITY_TOKEN,
			aborted: null,
			autoAbortPrevious: false
		}, options);
		
		if (options.dataType === 'jsonp') {
			require(['AjaxJsonp'], function(AjaxJsonp) {
				AjaxJsonp.send(options.url, options.success, options.failure, {
					parameterName: options.jsonp
				});
			});
		}
		else {
			require(['AjaxRequest'], (function(AjaxRequest) {
				this._ajaxRequest = new AjaxRequest({
					data: options.data,
					type: options.type,
					url: options.url,
					withCredentials: (options.url === 'index.php?ajax-proxy/&t=' + SECURITY_TOKEN),
					responseType: (options.dataType === 'json' ? 'application/json' : ''),
					
					autoAbort: options.autoAbortPrevious,
					ignoreError: options.suppressErrors,
					silent: !options.showLoadingOverlay,
					
					failure: options.failure,
					finalize: options.after,
					success: options.success
				});
				
				if (options.autoSend) {
					this._ajaxRequest.sendRequest();
				}
			}).bind(this));
		}
	},
	
	/**
	 * Sends an AJAX request.
	 * 
	 * @param	abortPrevious	boolean
	 */
	sendRequest: function(abortPrevious) {
		require(['AjaxRequest'], (function(AjaxRequest) {
			if (this._ajaxRequest !== null) {
				this._ajaxRequest.sendRequest(abortPrevious);
			}
		}).bind(this));
	},
	
	/**
	 * Aborts the previous request
	 */
	abortPrevious: function() {
		require(['AjaxRequest'], (function(AjaxRequest) {
			if (this._ajaxRequest !== null) {
				this._ajaxRequest.abortPrevious();
			}
		}).bind(this));
	},
	
	/**
	 * Sets options, MUST be used to set parameters before sending request
	 * if calling from child classes.
	 * 
	 * @param	string		optionName
	 * @param	mixed		optionData
	 */
	setOption: function(optionName, optionData) {
		require(['AjaxRequest'], (function(AjaxRequest) {
			if (this._ajaxRequest !== null) {
				this._ajaxRequest.setOption(optionName, optionData);
			}
		}).bind(this));
	},
	
	// legacy methods, no longer supported
	showLoadingOverlayOnce: function() {},
	suppressErrors: function() {},
	_failure: function(jqXHR, textStatus, errorThrown) {},
	_success: function(data, textStatus, jqXHR) {},
	_after: function() {}
});

/**
 * Basic implementation for simple proxy access using bound elements.
 * 
 * @param	object		options
 * @param	object		callbacks
 */
WCF.Action.SimpleProxy = Class.extend({
	/**
	 * Initializes SimpleProxy.
	 * 
	 * @param	object		options
	 * @param	object		callbacks
	 */
	init: function(options, callbacks) {
		/**
		 * action-specific options
		 */
		this.options = $.extend(true, {
			action: '',
			className: '',
			elements: null,
			eventName: 'click'
		}, options);
		
		/**
		 * proxy-specific options
		 */
		this.callbacks = $.extend(true, {
			after: null,
			failure: null,
			init: null,
			success: null
		}, callbacks);
		
		if (!this.options.elements) return;
		
		// initialize proxy
		this.proxy = new WCF.Action.Proxy(this.callbacks);
		
		// bind event listener
		this.options.elements.each($.proxy(function(index, element) {
			$(element).bind(this.options.eventName, $.proxy(this._handleEvent, this));
		}, this));
	},
	
	/**
	 * Handles event actions.
	 * 
	 * @param	object		event
	 */
	_handleEvent: function(event) {
		this.proxy.setOption('data', {
			actionName: this.options.action,
			className: this.options.className,
			objectIDs: [ $(event.target).data('objectID') ]
		});
		
		this.proxy.sendRequest();
	}
});

/**
 * Basic implementation for AJAXProxy-based deletion.
 *
 * @param        string                className
 * @param        string                containerSelector
 * @param        string                buttonSelector
 */
WCF.Action.Delete = Class.extend({
	/**
	 * delete button selector
	 * @var        string
	 */
	_buttonSelector: '',
	
	/**
	 * callback function called prior to triggering the delete effect
	 * @var        function
	 */
	_callback: null,
	
	/**
	 * action class name
	 * @var        string
	 */
	_className: '',
	
	/**
	 * container selector
	 * @var        string
	 */
	_containerSelector: '',
	
	/**
	 * list of known container ids
	 * @var        array<string>
	 */
	_containers: [],
	
	/**
	 * Initializes 'delete'-Proxy.
	 *
	 * @param        string                className
	 * @param        string                containerSelector
	 * @param        string                buttonSelector
	 */
	init: function (className, containerSelector, buttonSelector) {
		this._containerSelector = containerSelector;
		this._className = className;
		this._buttonSelector = (buttonSelector) ? buttonSelector : '.jsDeleteButton';
		this._callback = null;
		
		this.proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
		
		this._initElements();
		
		WCF.DOMNodeInsertedHandler.addCallback('WCF.Action.Delete' + this._className.hashCode(), $.proxy(this._initElements, this));
	},
	
	/**
	 * Initializes available element containers.
	 */
	_initElements: function () {
		$(this._containerSelector).each((function (index, container) {
			var $container = $(container);
			var $containerID = $container.wcfIdentify();
			
			if (!WCF.inArray($containerID, this._containers)) {
				var $deleteButton = $container.find(this._buttonSelector);
				
				if ($deleteButton.length) {
					this._containers.push($containerID);
					$deleteButton.click($.proxy(this._click, this));
				}
			}
		}).bind(this));
	},
	
	/**
	 * Sends AJAX request.
	 *
	 * @param        object                event
	 */
	_click: function (event) {
		var $target = $(event.currentTarget);
		event.preventDefault();
		
		if ($target.data('confirmMessageHtml') || $target.data('confirmMessage')) {
			WCF.System.Confirmation.show($target.data('confirmMessageHtml') ? $target.data('confirmMessageHtml') : $target.data('confirmMessage'), $.proxy(this._execute, this), {target: $target}, undefined, $target.data('confirmMessageHtml') ? true : false);
		}
		else {
			WCF.LoadingOverlayHandler.updateIcon($target);
			this._sendRequest($target);
		}
	},
	
	/**
	 * Is called if the delete effect has been triggered on the given element.
	 *
	 * @param        jQuery                element
	 */
	_didTriggerEffect: function (element) {
		// does nothing
	},
	
	/**
	 * Executes deletion.
	 *
	 * @param        string                action
	 * @param        object                parameters
	 */
	_execute: function (action, parameters) {
		if (action === 'cancel') {
			return;
		}
		
		WCF.LoadingOverlayHandler.updateIcon(parameters.target);
		this._sendRequest(parameters.target);
	},
	
	/**
	 * Sends the request
	 *
	 * @param        jQuery        object
	 */
	_sendRequest: function (object) {
		this.proxy.setOption('data', {
			actionName: 'delete',
			className: this._className,
			interfaceName: 'wcf\\data\\IDeleteAction',
			objectIDs: [$(object).data('objectID')]
		});
		
		this.proxy.sendRequest();
	},
	
	/**
	 * Deletes items from containers.
	 *
	 * @param        object                data
	 * @param        string                textStatus
	 * @param        object                jqXHR
	 */
	_success: function (data, textStatus, jqXHR) {
		if (this._callback) {
			this._callback(data.objectIDs);
		}
		
		this.triggerEffect(data.objectIDs);
	},
	
	/**
	 * Sets a callback function called prior to triggering the delete effect.
	 *
	 * @param        {function}        callback
	 */
	setCallback: function (callback) {
		if (typeof callback !== 'function') {
			throw new TypeError("[WCF.Action.Delete] Expected a valid callback for '" + this._className + "'.");
		}
		
		this._callback = callback;
	},
	
	/**
	 * Triggers the delete effect for the objects with the given ids.
	 *
	 * @param        array                objectIDs
	 */
	triggerEffect: function (objectIDs) {
		for (var $index in this._containers) {
			var $container = $('#' + this._containers[$index]);
			var $button = $container.find(this._buttonSelector);
			if (WCF.inArray($button.data('objectID'), objectIDs)) {
				var self = this;
				$container.wcfBlindOut('up', function () {
					var $container = $(this).remove();
					self._containers.splice(self._containers.indexOf($container.wcfIdentify()), 1);
					self._didTriggerEffect($container);
					
					if ($button.data('eventName')) {
						WCF.System.Event.fireEvent('com.woltlab.wcf.action.delete', $button.data('eventName'), {
							button: $button,
							container: $container
						});
					}
				});
			}
		}
	}
});

if (COMPILER_TARGET_DEFAULT) {
	/**
	 * Basic implementation for deletion of nested elements.
	 *
	 * The implementation requires the nested elements to be grouped as numbered lists
	 * (ol lists). The child elements of the deleted elements are moved to the parent
	 * element of the deleted element.
	 *
	 * @see        WCF.Action.Delete
	 */
	WCF.Action.NestedDelete = WCF.Action.Delete.extend({
		/**
		 * @see        WCF.Action.Delete.triggerEffect()
		 */
		triggerEffect: function (objectIDs) {
			for (var $index in this._containers) {
				var $container = $('#' + this._containers[$index]);
				if (WCF.inArray($container.find(this._buttonSelector).data('objectID'), objectIDs)) {
					// move children up
					if ($container.has('ol').has('li').length) {
						if ($container.is(':only-child')) {
							$container.parent().replaceWith($container.find('> ol'));
						}
						else {
							$container.replaceWith($container.find('> ol > li'));
						}
						
						this._containers.splice(this._containers.indexOf($container.wcfIdentify()), 1);
						this._didTriggerEffect($container);
					}
					else {
						var self = this;
						$container.wcfBlindOut('up', function () {
							$(this).remove();
							self._containers.splice(self._containers.indexOf($(this).wcfIdentify()), 1);
							self._didTriggerEffect($(this));
						});
					}
				}
			}
		}
	});
	
	/**
	 * Basic implementation for AJAXProxy-based toggle actions.
	 *
	 * @param        string                className
	 * @param        jQuery                containerList
	 * @param        string                buttonSelector
	 */
	WCF.Action.Toggle = Class.extend({
		/**
		 * toogle button selector
		 * @var        string
		 */
		_buttonSelector: '.jsToggleButton',
		
		/**
		 * action class name
		 * @var        string
		 */
		_className: '',
		
		/**
		 * container selector
		 * @var        string
		 */
		_containerSelector: '',
		
		/**
		 * list of known container ids
		 * @var        array<string>
		 */
		_containers: [],
		
		/**
		 * Initializes 'toggle'-Proxy
		 *
		 * @param        string                className
		 * @param        string                containerSelector
		 * @param        string                buttonSelector
		 */
		init: function (className, containerSelector, buttonSelector) {
			this._containerSelector = containerSelector;
			this._className = className;
			this._buttonSelector = (buttonSelector) ? buttonSelector : '.jsToggleButton';
			this._containers = [];
			
			// initialize proxy
			var options = {
				success: $.proxy(this._success, this)
			};
			this.proxy = new WCF.Action.Proxy(options);
			
			// bind event listener
			this._initElements();
			WCF.DOMNodeInsertedHandler.addCallback('WCF.Action.Toggle' + this._className.hashCode(), $.proxy(this._initElements, this));
		},
		
		/**
		 * Initializes available element containers.
		 */
		_initElements: function () {
			$(this._containerSelector).each($.proxy(function (index, container) {
				var $container = $(container);
				var $containerID = $container.wcfIdentify();
				
				if (!WCF.inArray($containerID, this._containers)) {
					this._containers.push($containerID);
					$container.find(this._buttonSelector).click($.proxy(this._click, this));
				}
			}, this));
		},
		
		/**
		 * Sends AJAX request.
		 *
		 * @param        object                event
		 */
		_click: function (event) {
			var $target = $(event.currentTarget);
			event.preventDefault();
			
			if ($target.data('confirmMessageHtml') || $target.data('confirmMessage')) {
				WCF.System.Confirmation.show($target.data('confirmMessageHtml') ? $target.data('confirmMessageHtml') : $target.data('confirmMessage'), $.proxy(this._execute, this), {target: $target}, undefined, $target.data('confirmMessageHtml') ? true : false);
			}
			else {
				WCF.LoadingOverlayHandler.updateIcon($target);
				this._sendRequest($target);
			}
		},
		
		/**
		 * Executes toggeling.
		 *
		 * @param        string                action
		 * @param        object                parameters
		 */
		_execute: function (action, parameters) {
			if (action === 'cancel') {
				return;
			}
			
			WCF.LoadingOverlayHandler.updateIcon(parameters.target);
			this._sendRequest(parameters.target);
		},
		
		_sendRequest: function (object) {
			this.proxy.setOption('data', {
				actionName: 'toggle',
				className: this._className,
				interfaceName: 'wcf\\data\\IToggleAction',
				objectIDs: [$(object).data('objectID')]
			});
			
			this.proxy.sendRequest();
		},
		
		/**
		 * Toggles status icons.
		 *
		 * @param        object                data
		 * @param        string                textStatus
		 * @param        object                jqXHR
		 */
		_success: function (data, textStatus, jqXHR) {
			this.triggerEffect(data.objectIDs);
		},
		
		/**
		 * Triggers the toggle effect for the objects with the given ids.
		 *
		 * @param        array                objectIDs
		 */
		triggerEffect: function (objectIDs) {
			for (var $index in this._containers) {
				var $container = $('#' + this._containers[$index]);
				var $toggleButton = $container.find(this._buttonSelector);
				if (WCF.inArray($toggleButton.data('objectID'), objectIDs)) {
					$container.wcfHighlight();
					this._toggleButton($container, $toggleButton);
				}
			}
		},
		
		/**
		 * Triggers the toggle effect on a button
		 *
		 * @param        jQuery        $container
		 * @param        jQuery        $toggleButton
		 */
		_toggleButton: function ($container, $toggleButton) {
			var $newTitle = '';
			
			// toggle icon source
			WCF.LoadingOverlayHandler.updateIcon($toggleButton, false);
			if ($toggleButton.hasClass('fa-square-o')) {
				$toggleButton.removeClass('fa-square-o').addClass('fa-check-square-o');
				$newTitle = ($toggleButton.data('disableTitle') ? $toggleButton.data('disableTitle') : WCF.Language.get('wcf.global.button.disable'));
				$toggleButton.attr('title', $newTitle);
			}
			else {
				$toggleButton.removeClass('fa-check-square-o').addClass('fa-square-o');
				$newTitle = ($toggleButton.data('enableTitle') ? $toggleButton.data('enableTitle') : WCF.Language.get('wcf.global.button.enable'));
				$toggleButton.attr('title', $newTitle);
			}
			
			// toggle css class
			$container.toggleClass('disabled');
		}
	});
}
else {
	WCF.Action.NestedDelete = WCF.Action.Delete.extend({
		triggerEffect: function() {},
		_buttonSelector: "",
		_callback: {},
		_className: "",
		_containerSelector: "",
		_containers: {},
		init: function() {},
		_initElements: function() {},
		_click: function() {},
		_didTriggerEffect: function() {},
		_execute: function() {},
		_sendRequest: function() {},
		_success: function() {},
		setCallback: function() {}
	});
	
	WCF.Action.Toggle = Class.extend({
		_buttonSelector: "",
		_className: "",
		_containerSelector: "",
		_containers: {},
		init: function() {},
		_initElements: function() {},
		_click: function() {},
		_execute: function() {},
		_sendRequest: function() {},
		_success: function() {},
		triggerEffect: function() {},
		_toggleButton: function() {}
	});
}

/**
 * Executes provided callback if scroll threshold is reached. Usable to determine
 * if user reached the bottom of an element to load new elements on the fly.
 * 
 * If you do not provide a value for 'reference' and 'target' it will assume you're
 * monitoring page scrolls, otherwise a valid jQuery selector must be provided for both.
 * 
 * @param	integer		threshold
 * @param	object		callback
 * @param	string		reference
 * @param	string		target
 */
WCF.Action.Scroll = Class.extend({
	/**
	 * callback used once threshold is reached
	 * @var	object
	 */
	_callback: null,
	
	/**
	 * reference object
	 * @var	jQuery
	 */
	_reference: null,
	
	/**
	 * target object
	 * @var	jQuery
	 */
	_target: null,
	
	/**
	 * threshold value
	 * @var	integer
	 */
	_threshold: 0,
	
	/**
	 * Initializes a new WCF.Action.Scroll object.
	 * 
	 * @param	integer		threshold
	 * @param	object		callback
	 * @param	string		reference
	 * @param	string		target
	 */
	init: function(threshold, callback, reference, target) {
		this._threshold = parseInt(threshold);
		if (this._threshold === 0) {
			console.debug("[WCF.Action.Scroll] Given threshold is invalid, aborting.");
			return;
		}
		
		if ($.isFunction(callback)) this._callback = callback;
		if (this._callback === null) {
			console.debug("[WCF.Action.Scroll] Given callback is invalid, aborting.");
			return;
		}
		
		// bind element references
		this._reference = $((reference) ? reference : window);
		this._target = $((target) ? target : document);
		
		// watch for scroll event
		this.start();
		
		// check if browser navigated back and jumped to offset before JavaScript was loaded
		this._scroll();
	},
	
	/**
	 * Calculates if threshold is reached and notifies callback.
	 */
	_scroll: function() {
		var $targetHeight = this._target.height();
		var $topOffset = this._reference.scrollTop();
		var $referenceHeight = this._reference.height();
		
		// calculate if defined threshold is visible
		if (($targetHeight - ($referenceHeight + $topOffset)) < this._threshold) {
			this._callback(this);
		}
	},
	
	/**
	 * Enables scroll monitoring, may be used to resume.
	 */
	start: function() {
		this._reference.on('scroll', $.proxy(this._scroll, this));
	},
	
	/**
	 * Disables scroll monitoring, e.g. no more elements loadable.
	 */
	stop: function() {
		this._reference.off('scroll');
	}
});

/**
 * Namespace for date-related functions.
 */
WCF.Date = {};

/**
 * Provides a date picker for date input fields.
 * 
 * @deprecated	3.0 - no longer required
 */
WCF.Date.Picker = { init: function() {} };

/**
 * Provides utility functions for date operations.
 * 
 * @deprecated	3.0 - use `DateUtil` instead
 */
WCF.Date.Util = {
	/**
	 * Returns UTC timestamp, if date is not given, current time will be used.
	 * 
	 * @param	Date		date
	 * @return	integer
	 * 
	 * @deprecated	3.0 - use `DateUtil::gmdate()` instead
	 */
	gmdate: function(date) {
		var $date = (date) ? date : new Date();
		
		return Math.round(Date.UTC(
			$date.getUTCFullYear(),
			$date.getUTCMonth(),
			$date.getUTCDay(),
			$date.getUTCHours(),
			$date.getUTCMinutes(),
			$date.getUTCSeconds()
		) / 1000);
	},
	
	/**
	 * Returns a Date object with precise offset (including timezone and local timezone).
	 * Parameters timestamp and offset must be in milliseconds!
	 * 
	 * @param	integer		timestamp
	 * @param	integer		offset
	 * @return	Date
	 * 
	 * @deprecated	3.0 - use `DateUtil::getTimezoneDate()` instead
	 */
	getTimezoneDate: function(timestamp, offset) {
		var $date = new Date(timestamp);
		var $localOffset = $date.getTimezoneOffset() * 60000;
		
		return new Date((timestamp + $localOffset + offset));
	}
};

/**
 * Hash-like dictionary. Based upon idead from Prototype's hash
 * 
 * @see	https://github.com/sstephenson/prototype/blob/master/src/prototype/lang/hash.js
 */
WCF.Dictionary = Class.extend({
	/**
	 * list of variables
	 * @var	object
	 */
	_variables: { },
	
	/**
	 * Initializes a new dictionary.
	 */
	init: function() {
		this._variables = { };
	},
	
	/**
	 * Adds an entry.
	 * 
	 * @param	string		key
	 * @param	mixed		value
	 */
	add: function(key, value) {
		this._variables[key] = value;
	},
	
	/**
	 * Adds a traditional object to current dataset.
	 * 
	 * @param	object		object
	 */
	addObject: function(object) {
		for (var $key in object) {
			this.add($key, object[$key]);
		}
	},
	
	/**
	 * Adds a dictionary to current dataset.
	 * 
	 * @param	object		dictionary
	 */
	addDictionary: function(dictionary) {
		dictionary.each($.proxy(function(pair) {
			this.add(pair.key, pair.value);
		}, this));
	},
	
	/**
	 * Retrieves the value of an entry or returns null if key is not found.
	 * 
	 * @param	string		key
	 * @returns	mixed
	 */
	get: function(key) {
		if (this.isset(key)) {
			return this._variables[key];
		}
		
		return null;
	},
	
	/**
	 * Returns true if given key is a valid entry.
	 * 
	 * @param	string		key
	 */
	isset: function(key) {
		return this._variables.hasOwnProperty(key);
	},
	
	/**
	 * Removes an entry.
	 * 
	 * @param	string		key
	 */
	remove: function(key) {
		delete this._variables[key];
	},
	
	/**
	 * Iterates through dictionary.
	 * 
	 * Usage:
	 * 	var $hash = new WCF.Dictionary();
	 * 	$hash.add('foo', 'bar');
	 * 	$hash.each(function(pair) {
	 * 		// alerts:	foo = bar
	 * 		alert(pair.key + ' = ' + pair.value);
	 * 	});
	 * 
	 * @param	function	callback
	 */
	each: function(callback) {
		if (!$.isFunction(callback)) {
			return;
		}
		
		for (var $key in this._variables) {
			var $value = this._variables[$key];
			var $pair = {
				key: $key,
				value: $value
			};
			
			callback($pair);
		}
	},
	
	/**
	 * Returns the amount of items.
	 * 
	 * @return	integer
	 */
	count: function() {
		return $.getLength(this._variables);
	},
	
	/**
	 * Returns true if dictionary is empty.
	 * 
	 * @return	integer
	 */
	isEmpty: function() {
		return !this.count();
	}
});

// non strict equals by intent
if (window.WCF.Language == null) {
	/**
	 * @deprecated Use WoltLabSuite/Core/Language
	 */
	WCF.Language = {
		add: function(key, value) {
			require(['Language'], function(Language) {
				Language.add(key, value);
			});
		},
		addObject: function(object) {
			require(['Language'], function(Language) {
				Language.addObject(object);
			});
		},
		get: function(key, parameters) {
			// This cannot be sanely provided as a compatibility wrapper.
			throw new Error('Call to deprecated WCF.Language.get("' + key + '")');
		}
	};
}

/**
 * Number utilities.
 * @deprecated Use WoltLabSuite/Core/NumberUtil
 */
WCF.Number = {
	/**
	 * Rounds a number to a given number of decimal places. Defaults to 0.
	 * 
	 * @param	number		number
	 * @param	decimalPlaces	number of decimal places
	 * @return	number
	 */
	round: function (number, decimalPlaces) {
		decimalPlaces = Math.pow(10, (decimalPlaces || 0));
		
		return Math.round(number * decimalPlaces) / decimalPlaces;
	}
};

/**
 * String utilities.
 * @deprecated Use WoltLabSuite/Core/StringUtil
 */
WCF.String = {
	/**
	 * Adds thousands separators to a given number.
	 * 
	 * @see		http://stackoverflow.com/a/6502556/782822
	 * @param	mixed		number
	 * @return	string
	 */
	addThousandsSeparator: function(number) {
		return String(number).replace(/(^-?\d{1,3}|\d{3})(?=(?:\d{3})+(?:$|\.))/g, '$1' + WCF.Language.get('wcf.global.thousandsSeparator'));
	},
	
	/**
	 * Escapes special HTML-characters within a string
	 * 
	 * @param	string	string
	 * @return	string
	 */
	escapeHTML: function (string) {
		return String(string).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
	},
	
	/**
	 * Escapes a String to work with RegExp.
	 * 
	 * @see		https://github.com/sstephenson/prototype/blob/master/src/prototype/lang/regexp.js#L25
	 * @param	string	string
	 * @return	string
	 */
	escapeRegExp: function(string) {
		return String(string).replace(/([.*+?^=!:${}()|[\]\/\\])/g, '\\$1');
	},
	
	/**
	 * Rounds number to given count of floating point digits, localizes decimal-point and inserts thousands-separators
	 * 
	 * @param	mixed	number
	 * @return	string
	 */
	formatNumeric: function(number, decimalPlaces) {
		number = String(WCF.Number.round(number, decimalPlaces || 2));
		var numberParts = number.split('.');
		
		number = this.addThousandsSeparator(numberParts[0]);
		if (numberParts.length > 1) number += WCF.Language.get('wcf.global.decimalPoint') + numberParts[1];
		
		number = number.replace('-', '\u2212');
		
		return number;
	},
	
	/**
	 * Makes a string's first character lowercase
	 * 
	 * @param	string		string
	 * @return	string
	 */
	lcfirst: function(string) {
		return String(string).substring(0, 1).toLowerCase() + string.substring(1);
	},
	
	/**
	 * Makes a string's first character uppercase
	 * 
	 * @param	string		string
	 * @return	string
	 */
	ucfirst: function(string) {
		return String(string).substring(0, 1).toUpperCase() + string.substring(1);
	},
	
	/**
	 * Unescapes special HTML-characters within a string
	 * 
	 * @param	string		string
	 * @return	string
	 */
	unescapeHTML: function (string) {
		return String(string).replace(/&amp;/g, '&').replace(/&quot;/g, '"').replace(/&lt;/g, '<').replace(/&gt;/g, '>');
	}
};

/**
 * Basic implementation for WCF TabMenus. Use the data attributes 'active' to specify the
 * tab which should be shown on init. Furthermore you may specify a 'store' data-attribute
 * which will be filled with the currently selected tab.
 */
WCF.TabMenu = {
	/**
	 * Initializes all TabMenus
	 */
	init: function() {
		require(['WoltLabSuite/Core/Ui/TabMenu'], function(UiTabMenu) {
			UiTabMenu.setup();
		});
	},
	
	/**
	 * Reloads the tab menus.
	 */
	reload: function() {
		this.init();
	}
};

/**
 * Templates that may be fetched more than once with different variables.
 * Based upon ideas from Prototype's template.
 * 
 * Usage:
 * 	var myTemplate = new WCF.Template('{$hello} World');
 * 	myTemplate.fetch({ hello: 'Hi' }); // Hi World
 * 	myTemplate.fetch({ hello: 'Hello' }); // Hello World
 * 	
 * 	my2ndTemplate = new WCF.Template('{@$html}{$html}');
 * 	my2ndTemplate.fetch({ html: '<b>Test</b>' }); // <b>Test</b>&lt;b&gt;Test&lt;/b&gt;
 *	
 * 	var my3rdTemplate = new WCF.Template('You can use {literal}{$variable}{/literal}-Tags here');
 * 	my3rdTemplate.fetch({ variable: 'Not shown' }); // You can use {$variable}-Tags here
 * 
 * @param	template		template-content
 * @see		https://github.com/sstephenson/prototype/blob/master/src/prototype/lang/template.js
 */
WCF.Template = Class.extend({
	/**
	 * Prepares template
	 * 
	 * @param	$template		template-content
	 */
	init: function(template) {
		var $literals = new WCF.Dictionary();
		var $tagID = 0;
		
		// escape \ and ' and newlines
		template = template.replace(/\\/g, '\\\\').replace(/'/g, "\\'").replace(/(\r\n|\n|\r)/g, '\\n');
		
		// save literal-tags
		template = template.replace(/\{literal\}(.*?)\{\/literal\}/g, $.proxy(function(match) {
			// hopefully no one uses this string in one of his templates
			var id = '@@@@@@@@@@@'+Math.random()+'@@@@@@@@@@@';
			$literals.add(id, match.replace(/\{\/?literal\}/g, ''));
			
			return id;
		}, this));
		
		// remove comments
		template = template.replace(/\{\*.*?\*\}/g, '');
		
		var parseParameterList = function(parameterString) {
			var $chars = parameterString.split('');
			var $parameters = { };
			var $inName = true;
			var $name = '';
			var $value = '';
			var $doubleQuoted = false;
			var $singleQuoted = false;
			var $escaped = false;
			
			for (var $i = 0, $max = $chars.length; $i < $max; $i++) {
				var $char = $chars[$i];
				if ($inName && $char != '=' && $char != ' ') $name += $char;
				else if ($inName && $char == '=') {
					$inName = false;
					$singleQuoted = false;
					$doubleQuoted = false;
					$escaped = false;
				}
				else if (!$inName && !$singleQuoted && !$doubleQuoted && $char == ' ') {
					$inName = true;
					$parameters[$name] = $value;
					$value = $name = '';
				}
				else if (!$inName && $singleQuoted && !$escaped && $char == "'") {
					$singleQuoted = false;
					$value += $char;
				}
				else if (!$inName && !$singleQuoted && !$doubleQuoted && $char == "'") {
					$singleQuoted = true;
					$value += $char;
				}
				else if (!$inName && $doubleQuoted && !$escaped && $char == '"') {
					$doubleQuoted = false;
					$value += $char;
				}
				else if (!$inName && !$singleQuoted && !$doubleQuoted && $char == '"') {
					$doubleQuoted = true;
					$value += $char;
				}
				else if (!$inName && ($doubleQuoted || $singleQuoted) && !$escaped && $char == '\\') {
					$escaped = true;
					$value += $char;
				}
				else if (!$inName) {
					$escaped = false;
					$value += $char;
				}
			}
			$parameters[$name] = $value;
			
			if ($doubleQuoted || $singleQuoted || $escaped) throw new Error('Syntax error in parameterList: "' + parameterString + '"');
			
			return $parameters;
		};
		
		var unescape = function(string) {
			return string.replace(/\\n/g, "\n").replace(/\\\\/g, '\\').replace(/\\'/g, "'");
		};
		
		template = template.replace(/\{(\$[^\}]+?)\}/g, function(_, content) {
			content = unescape(content.replace(/\$([^.\[\(\)\]\s]+)/g, "(v['$1'])"));
			
			return "' + WCF.String.escapeHTML(" + content + ") + '";
		})
		// Numeric Variable
		.replace(/\{#(\$[^\}]+?)\}/g, function(_, content) {
			content = unescape(content.replace(/\$([^.\[\(\)\]\s]+)/g, "(v['$1'])"));
			
			return "' + WCF.String.formatNumeric(" + content + ") + '";
		})
		// Variable without escaping
		.replace(/\{@(\$[^\}]+?)\}/g, function(_, content) {
			content = unescape(content.replace(/\$([^.\[\(\)\]\s]+)/g, "(v['$1'])"));
			
			return "' + " + content + " + '";
		})
		// {lang}foo{/lang}
		.replace(/\{lang\}(.+?)\{\/lang\}/g, function(_, content) {
			return "' + WCF.Language.get('" + content + "', v) + '";
		})
		// {include}
		.replace(/\{include (.+?)\}/g, function(_, content) {
			content = content.replace(/\\\\/g, '\\').replace(/\\'/g, "'");
			var $parameters = parseParameterList(content);
			
			if (typeof $parameters['file'] === 'undefined') throw new Error('Missing file attribute in include-tag');
			
			$parameters['file'] = $parameters['file'].replace(/\$([^.\[\(\)\]\s]+)/g, "(v.$1)");
			
			return "' + " + $parameters['file'] + ".fetch(v) + '";
		})
		// {if}
		.replace(/\{if (.+?)\}/g, function(_, content) {
			content = unescape(content.replace(/\$([^.\[\(\)\]\s]+)/g, "(v['$1'])"));
			
			return	"';\n" +
				"if (" + content + ") {\n" +
				"	$output += '";
		})
		// {elseif}
		.replace(/\{else ?if (.+?)\}/g, function(_, content) {
			content = unescape(content.replace(/\$([^.\[\(\)\]\s]+)/g, "(v['$1'])"));
			
			return	"';\n" +
				"}\n" +
				"else if (" + content + ") {\n" +
				"	$output += '";
		})
		// {implode}
		.replace(/\{implode (.+?)\}/g, function(_, content) {
			$tagID++;
			
			content = content.replace(/\\\\/g, '\\').replace(/\\'/g, "'");
			var $parameters = parseParameterList(content);
			
			if (typeof $parameters['from'] === 'undefined') throw new Error('Missing from attribute in implode-tag');
			if (typeof $parameters['item'] === 'undefined') throw new Error('Missing item attribute in implode-tag');
			if (typeof $parameters['glue'] === 'undefined') $parameters['glue'] = "', '";
			
			$parameters['from'] = $parameters['from'].replace(/\$([^.\[\(\)\]\s]+)/g, "(v.$1)");
			
			return 	"';\n"+
				"var $implode_" + $tagID + " = false;\n" +
				"for ($implodeKey_" + $tagID + " in " + $parameters['from'] + ") {\n" +
				"	v[" + $parameters['item'] + "] = " + $parameters['from'] + "[$implodeKey_" + $tagID + "];\n" +
				(typeof $parameters['key'] !== 'undefined' ? "		v[" + $parameters['key'] + "] = $implodeKey_" + $tagID + ";\n" : "") +
				"	if ($implode_" + $tagID + ") $output += " + $parameters['glue'] + ";\n" +
				"	$implode_" + $tagID + " = true;\n" +
				"	$output += '";
		})
		// {foreach}
		.replace(/\{foreach (.+?)\}/g, function(_, content) {
			$tagID++;
			
			content = content.replace(/\\\\/g, '\\').replace(/\\'/g, "'");
			var $parameters = parseParameterList(content);
			
			if (typeof $parameters['from'] === 'undefined') throw new Error('Missing from attribute in foreach-tag');
			if (typeof $parameters['item'] === 'undefined') throw new Error('Missing item attribute in foreach-tag');
			$parameters['from'] = $parameters['from'].replace(/\$([^.\[\(\)\]\s]+)/g, "(v.$1)");
			
			return	"';\n" +
				"$foreach_"+$tagID+" = false;\n" +
				"for ($foreachKey_" + $tagID + " in " + $parameters['from'] + ") {\n" +
				"	$foreach_"+$tagID+" = true;\n" +
				"	break;\n" +
				"}\n" +
				"if ($foreach_"+$tagID+") {\n" +
				"	for ($foreachKey_" + $tagID + " in " + $parameters['from'] + ") {\n" +
				"		v[" + $parameters['item'] + "] = " + $parameters['from'] + "[$foreachKey_" + $tagID + "];\n" +
				(typeof $parameters['key'] !== 'undefined' ? "		v[" + $parameters['key'] + "] = $foreachKey_" + $tagID + ";\n" : "") +
				"		$output += '";
		})
		// {foreachelse}
		.replace(/\{foreachelse\}/g, 
			"';\n" +
			"	}\n" +
			"}\n" +
			"else {\n" +
			"	{\n" +
			"		$output += '"
		)
		// {/foreach}
		.replace(/\{\/foreach\}/g, 
			"';\n" +
			"	}\n" +
			"}\n" +
			"$output += '"
		)
		// {else}
		.replace(/\{else\}/g, 
			"';\n" +
			"}\n" +
			"else {\n" +
			"	$output += '"
		)
		// {/if} and {/implode}
		.replace(/\{\/(if|implode)\}/g, 
			"';\n" +
			"}\n" +
			"$output += '"
		);
		
		// call callback
		for (var key in WCF.Template.callbacks) {
			template = WCF.Template.callbacks[key](template);
		}
		
		// insert delimiter tags
		template = template.replace('{ldelim}', '{').replace('{rdelim}', '}');
		
		$literals.each(function(pair) {
			template = template.replace(pair.key, pair.value);
		});
		
		template = "$output += '" + template + "';";
		
		try {
			this.fetch = new Function("v", "v = window.$.extend({}, v, { __wcf: window.WCF, __window: window }); var $output = ''; " + template + ' return $output;');
		}
		catch (e) {
			console.debug("var $output = ''; " + template + ' return $output;');
			throw e;
		}
	},
	
	/**
	 * Fetches the template with the given variables.
	 * 
	 * @param	v	variables to insert
	 * @return		parsed template
	 */
	fetch: function(v) {
		// this will be replaced in the init function
	}
});

/**
 * Array of callbacks that will be called after parsing the included tags. Only applies to Templates compiled after the callback was added.
 * 
 * @var	array<Function>
 */
WCF.Template.callbacks = [ ];

/**
 * Toggles options.
 * 
 * @param	string		element
 * @param	array		showItems
 * @param	array		hideItems
 * @param	function	callback
 */
WCF.ToggleOptions = Class.extend({
	/**
	 * target item
	 * 
	 * @var	jQuery
	 */
	_element: null,
	
	/**
	 * list of items to be shown
	 * 
	 * @var	array
	 */
	_showItems: [],
	
	/**
	 * list of items to be hidden
	 * 
	 * @var	array
	 */
	_hideItems: [],
		
	/**
	 * callback after options were toggled
	 * 
	 * @var	function
	 */
	 _callback: null,
	
	/**
	 * Initializes option toggle.
	 * 
	 * @param	string		element
	 * @param	array		showItems
	 * @param	array		hideItems
	 * @param	function	callback
	 */
	init: function(element, showItems, hideItems, callback) {
		this._element = $('#' + element);
		this._showItems = showItems;
		this._hideItems = hideItems;
		if (callback !== undefined) {
			this._callback = callback;
		}
		
		// bind event
		this._element.click($.proxy(this._toggle, this));
		
		// execute toggle on init
		this._toggle();
	},
	
	/**
	 * Toggles items.
	 */
	_toggle: function() {
		if (!this._element.prop('checked')) return;
		
		for (var $i = 0, $length = this._showItems.length; $i < $length; $i++) {
			var $item = this._showItems[$i];
			
			$('#' + $item).show();
		}
		
		for (var $i = 0, $length = this._hideItems.length; $i < $length; $i++) {
			var $item = this._hideItems[$i];
			
			$('#' + $item).hide();
		}
		
		if (this._callback !== null) {
			this._callback();
		}
	}
});

/**
 * Namespace for all kind of collapsible containers.
 */
WCF.Collapsible = {};

/**
 * Simple implementation for collapsible content, neither does it
 * store its state nor does it allow AJAX callbacks to fetch content.
 */
WCF.Collapsible.Simple = {
	/**
	 * Initializes collapsibles.
	 */
	init: function() {
		$('.jsCollapsible').each($.proxy(function(index, button) {
			this._initButton(button);
		}, this));
	},
	
	/**
	 * Binds an event listener on all buttons triggering the collapsible.
	 * 
	 * @param	object		button
	 */
	_initButton: function(button) {
		var $button = $(button);
		var $isOpen = $button.data('isOpen');
		
		if (!$isOpen) {
			// hide container on init
			$('#' + $button.data('collapsibleContainer')).hide();
		}
		
		$button.click($.proxy(this._toggle, this));
	},
	
	/**
	 * Toggles collapsible containers on click.
	 * 
	 * @param	object		event
	 */
	_toggle: function(event) {
		var $button = $(event.currentTarget);
		var $isOpen = $button.data('isOpen');
		var $target = $('#' + $.wcfEscapeID($button.data('collapsibleContainer')));
		
		if ($isOpen) {
			$target.stop().wcfBlindOut('vertical', $.proxy(function() {
				this._toggleImage($button);
			}, this));
			$isOpen = false;
		}
		else {
			$target.stop().wcfBlindIn('vertical', $.proxy(function() {
				this._toggleImage($button);
			}, this));
			$isOpen = true;
		}
		
		$button.data('isOpen', $isOpen);
		
		// suppress event
		event.stopPropagation();
		return false;
	},
	
	/**
	 * Toggles image of target button.
	 * 
	 * @param	jQuery		button
	 */
	_toggleImage: function(button) {
		var $icon = button.find('span.icon');
		if (button.data('isOpen')) {
			$icon.removeClass('fa-chevron-right').addClass('fa-chevron-down');
		}
		else {
			$icon.removeClass('fa-chevron-down').addClass('fa-chevron-right');
		}
	}
};

/**
 * Basic implementation for collapsible containers with AJAX support. Results for open
 * and closed state will be cached.
 * 
 * @param	string		className
 */
WCF.Collapsible.Remote = Class.extend({
	/**
	 * class name
	 * @var	string
	 */
	_className: '',
	
	/**
	 * list of active containers
	 * @var	object
	 */
	_containers: {},
	
	/**
	 * container meta data
	 * @var	object
	 */
	_containerData: {},
	
	/**
	 * action proxy
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * Initializes the controller for collapsible containers with AJAX support.
	 * 
	 * @param	string	className
	 */
	init: function(className) {
		this._className = className;
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
		
		// initialize each container
		this._init();
		
		WCF.DOMNodeInsertedHandler.addCallback('WCF.Collapsible.Remote', $.proxy(this._init, this));
	},
	
	/**
	 * Initializes a collapsible container.
	 * 
	 * @param	string		containerID
	 */
	_init: function(containerID) {
		this._getContainers().each($.proxy(function(index, container) {
			var $container = $(container);
			var $containerID = $container.wcfIdentify();
			
			if (this._containers[$containerID] === undefined) {
				this._containers[$containerID] = $container;
				
				this._initContainer($containerID);
			}
		}, this));
	},
	
	/**
	 * Initializes a collapsible container.
	 * 
	 * @param	string		containerID
	 */
	_initContainer: function(containerID) {
		var $target = this._getTarget(containerID);
		var $buttonContainer = this._getButtonContainer(containerID);
		var $button = this._createButton(containerID, $buttonContainer);
		
		// store container meta data
		this._containerData[containerID] = {
			button: $button,
			buttonContainer: $buttonContainer,
			isOpen: this._containers[containerID].data('isOpen'),
			target: $target
		};
		
		// add 'jsCollapsed' CSS class
		if (!this._containers[containerID].data('isOpen')) {
			$('#' + containerID).addClass('jsCollapsed');
		}
	},
	
	/**
	 * Returns a collection of collapsible containers.
	 * 
	 * @return	jQuery
	 */
	_getContainers: function() { },
	
	/**
	 * Returns the target element for current collapsible container.
	 * 
	 * @param	integer		containerID
	 * @return	jQuery
	 */
	_getTarget: function(containerID) { },
	
	/**
	 * Returns the button container for current collapsible container.
	 * 
	 * @param	integer		containerID
	 * @return	jQuery
	 */
	_getButtonContainer: function(containerID) { },
	
	/**
	 * Creates the toggle button.
	 * 
	 * @param	integer		containerID
	 * @param	jQuery		buttonContainer
	 */
	_createButton: function(containerID, buttonContainer) {
		var $button = elBySel('.jsStaticCollapsibleButton', buttonContainer[0]);
		if ($button !== null && $button.parentNode === buttonContainer[0]) {
			$button.classList.remove('jsStaticCollapsibleButton');
			$button = $($button);
		}
		else {
			$button = $('<span class="collapsibleButton jsTooltip pointer icon icon16 fa-chevron-down" title="' + WCF.Language.get('wcf.global.button.collapsible') + '">').prependTo(buttonContainer);
		}
		
		$button.data('containerID', containerID).click($.proxy(this._toggleContainer, this));
		
		return $button;
	},
	
	/**
	 * Toggles a container.
	 * 
	 * @param	object		event
	 */
	_toggleContainer: function(event) {
		var $button = $(event.currentTarget);
		var $containerID = $button.data('containerID');
		var $isOpen = this._containerData[$containerID].isOpen;
		var $state = ($isOpen) ? 'open' : 'close';
		var $newState = ($isOpen) ? 'close' : 'open';
		
		// fetch content state via AJAX
		this._proxy.setOption('data', {
			actionName: 'loadContainer',
			className: this._className,
			interfaceName: 'wcf\\data\\ILoadableContainerAction',
			objectIDs: [ this._getObjectID($containerID) ],
			parameters: $.extend(true, {
				containerID: $containerID,
				currentState: $state,
				newState: $newState
			}, this._getAdditionalParameters($containerID))
		});
		this._proxy.sendRequest();
		
		// toogle 'jsCollapsed' CSS class
		$('#' + $containerID).toggleClass('jsCollapsed');
		
		// set spinner for current button
		// this._exchangeIcon($button);
	},
	
	/**
	 * Exchanges button icon.
	 * 
	 * @param	jQuery		button
	 * @param	string		newIcon
	 */
	_exchangeIcon: function(button, newIcon) {
		newIcon = newIcon || 'spinner';
		button.removeClass('fa-chevron-down fa-chevron-right fa-spinner').addClass('fa-' + newIcon);
	},
	
	/**
	 * Returns the object id for current container.
	 * 
	 * @param	integer		containerID
	 * @return	integer
	 */
	_getObjectID: function(containerID) {
		return $('#' + containerID).data('objectID');
	},
	
	/**
	 * Returns additional parameters.
	 * 
	 * @param	integer		containerID
	 * @return	object
	 */
	_getAdditionalParameters: function(containerID) {
		return {};
	},
	
	/**
	 * Updates container content.
	 * 
	 * @param	integer		containerID
	 * @param	string		newContent
	 * @param	string		newState
	 */
	_updateContent: function(containerID, newContent, newState) {
		this._containerData[containerID].target.html(newContent);
	},
	
	/**
	 * Sets content upon successful AJAX request.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		// validate container id
		if (!data.returnValues.containerID) return;
		var $containerID = data.returnValues.containerID;
		
		// check if container id is known
		if (!this._containers[$containerID]) return;
		
		// update content storage
		this._containerData[$containerID].isOpen = (data.returnValues.isOpen) ? true : false;
		var $newState = (data.returnValues.isOpen) ? 'open' : 'close';
		
		// update container content
		this._updateContent($containerID, $.trim(data.returnValues.content), $newState);
		
		// update icon
		//this._exchangeIcon(this._containerData[$containerID].button, (data.returnValues.isOpen ? 'chevron-down' : 'chevron-right'));
	}
});

/**
 * Basic implementation for collapsible containers with AJAX support. Requires collapsible
 * content to be available in DOM already, if you want to load content on the fly use
 * WCF.Collapsible.Remote instead.
 */
WCF.Collapsible.SimpleRemote = WCF.Collapsible.Remote.extend({
	/**
	 * Initializes an AJAX-based collapsible handler.
	 * 
	 * @param	string		className
	 */
	init: function(className) {
		this._super(className);
		
		// override settings for action proxy
		this._proxy = new WCF.Action.Proxy({
			showLoadingOverlay: false
		});
	},
	
	/**
	 * @see	WCF.Collapsible.Remote._initContainer()
	 */
	_initContainer: function(containerID) {
		this._super(containerID);
		
		// hide container on init if applicable
		if (!this._containerData[containerID].isOpen) {
			this._containerData[containerID].target.hide();
			this._exchangeIcon(this._containerData[containerID].button, 'chevron-right');
		}
	},
	
	/**
	 * Toggles container visibility.
	 * 
	 * @param	object		event
	 */
	_toggleContainer: function(event) {
		var $button = $(event.currentTarget);
		var $containerID = $button.data('containerID');
		var $isOpen = this._containerData[$containerID].isOpen;
		var $currentState = ($isOpen) ? 'open' : 'close';
		var $newState = ($isOpen) ? 'close' : 'open';
		
		this._proxy.setOption('data', {
			actionName: 'toggleContainer',
			className: this._className,
			interfaceName: 'wcf\\data\\IToggleContainerAction',
			objectIDs: [ this._getObjectID($containerID) ],
			parameters: $.extend(true, {
				containerID: $containerID,
				currentState: $currentState,
				newState: $newState
			}, this._getAdditionalParameters($containerID))
		});
		this._proxy.sendRequest();
		
		// exchange icon
		this._exchangeIcon(this._containerData[$containerID].button, ($newState === 'open' ? 'chevron-down' : 'chevron-right'));
		
		// toggle container
		if ($newState === 'open') {
			this._containerData[$containerID].target.show();
		}
		else {
			this._containerData[$containerID].target.hide();
		}
		
		// toogle 'jsCollapsed' CSS class
		$('#' + $containerID).toggleClass('jsCollapsed');
		
		// update container data
		this._containerData[$containerID].isOpen = ($newState === 'open' ? true : false);
	}
});

/**
 * Holds userdata of the current user
 * 
 * @deprecated	use WCF/WoltLab/User
 */
WCF.User = {
	/**
	 * id of the active user
	 * @var	integer
	 */
	userID: 0,
	
	/**
	 * name of the active user
	 * @var	string
	 */
	username: '',
	
	/**
	 * Initializes userdata
	 * 
	 * @param	integer	userID
	 * @param	string	username
	 */
	init: function(userID, username) {
		this.userID = userID;
		this.username = username;
	}
};

/**
 * Namespace for effect-related functions.
 */
WCF.Effect = {};

/**
 * Scrolls to a specific element offset, optionally handling menu height.
 */
WCF.Effect.Scroll = Class.extend({
	/**
	 * Scrolls to a specific element offset.
	 * 
	 * @param	jQuery		element
	 * @param	boolean		excludeMenuHeight
	 * @param	boolean		disableAnimation
	 * @return	boolean
	 */
	scrollTo: function(element, excludeMenuHeight, disableAnimation) {
		if (!element.length) {
			return true;
		}
		
		var $elementOffset = element.getOffsets('offset').top;
		var $documentHeight = $(document).height();
		var $windowHeight = $(window).height();
		
		if ($elementOffset > $documentHeight - $windowHeight) {
			$elementOffset = $documentHeight - $windowHeight;
			if ($elementOffset < 0) {
				$elementOffset = 0;
			}
		}
		
		if (disableAnimation === true) {
			$('html,body').scrollTop($elementOffset);
		}
		else {
			$('html,body').animate({ scrollTop: $elementOffset }, 400, function (x, t, b, c, d) {
				return -c * ( ( t = t / d - 1 ) * t * t * t - 1) + b;
			});
		}
		
		return false;
	}
});

/**
 * Handles clicks outside an overlay, hitting body-tag through bubbling.
 * 
 * You should always remove callbacks before disposing the attached element,
 * preventing errors from blocking the iteration. Furthermore you should
 * always handle clicks on your overlay's container and return 'false' to
 * prevent bubbling.
 * 
 * @deprecated	3.0 - please use `Ui/CloseOverlay` instead
 */
WCF.CloseOverlayHandler = {
	/**
	 * Adds a new callback.
	 * 
	 * @param	string		identifier
	 * @param	object		callback
	 */
	addCallback: function(identifier, callback) {
		require(['Ui/CloseOverlay'], function(UiCloseOverlay) {
			UiCloseOverlay.add(identifier, callback);
		});
	},
	
	/**
	 * Removes a callback from list.
	 * 
	 * @param	string		identifier
	 */
	removeCallback: function(identifier) {
		require(['Ui/CloseOverlay'], function(UiCloseOverlay) {
			UiCloseOverlay.remove(identifier);
		});
	},
	
	/**
	 * Triggers the callbacks programmatically.
	 */
	forceExecution: function() {
		require(['Ui/CloseOverlay'], function(UiCloseOverlay) {
			UiCloseOverlay.execute();
		});
	}
};

/**
 * @deprecated Use WoltLabSuite/Core/Dom/Change/Listener
 */
WCF.DOMNodeInsertedHandler = {
	addCallback: function(identifier, callback) {
		require(['WoltLabSuite/Core/Dom/Change/Listener'], function (ChangeListener) {
			ChangeListener.add('__legacy__', callback);
		});
	},
	_executeCallbacks: function() {
		require(['WoltLabSuite/Core/Dom/Change/Listener'], function (ChangeListener) {
			ChangeListener.trigger();
		});
	},
	execute: function() {
		this._executeCallbacks();
	}
};

/**
 * Notifies objects once a DOM node was removed.
 */
WCF.DOMNodeRemovedHandler = {
	/**
	 * list of callbacks
	 * @var	WCF.Dictionary
	 */
	_callbacks: new WCF.Dictionary(),
	
	/**
	 * prevent infinite loop if a callback manipulates DOM
	 * @var	boolean
	 */
	_isExecuting: false,
	
	/**
	 * indicates that overlay handler is listening to DOMNodeRemoved events on body-tag
	 * @var	boolean
	 */
	_isListening: false,
	
	/**
	 * Adds a new callback.
	 * 
	 * @param	string		identifier
	 * @param	object		callback
	 */
	addCallback: function(identifier, callback) {
		this._bindListener();
		
		if (this._callbacks.isset(identifier)) {
			console.debug("[WCF.DOMNodeRemovedHandler] identifier '" + identifier + "' is already bound to a callback");
			return false;
		}
		
		this._callbacks.add(identifier, callback);
	},
	
	/**
	 * Removes a callback from list.
	 * 
	 * @param	string		identifier
	 */
	removeCallback: function(identifier) {
		if (this._callbacks.isset(identifier)) {
			this._callbacks.remove(identifier);
		}
	},
	
	/**
	 * Binds click event handler.
	 */
	_bindListener: function() {
		if (this._isListening) return;
		
		if (window.MutationObserver) {
			var $mutationObserver = new MutationObserver((function(mutations) {
				var $triggerEvent = false;
				
				mutations.forEach((function(mutation) {
					if (mutation.removedNodes.length) {
						$triggerEvent = true;
					}
				}).bind(this));
				
				if ($triggerEvent) {
					this._executeCallbacks({ });
				}
			}).bind(this));
			
			$mutationObserver.observe(document.body, {
				childList: true,
				subtree: true
			});
		}
		else {
			$(document).bind('DOMNodeRemoved', $.proxy(this._executeCallbacks, this));
		}
		
		this._isListening = true;
	},
	
	/**
	 * Executes callbacks if a DOM node is removed.
	 */
	_executeCallbacks: function(event) {
		if (this._isExecuting) return;
		
		// do not track events while executing callbacks
		this._isExecuting = true;
		
		this._callbacks.each(function(pair) {
			// execute callback
			pair.value(event);
		});
		
		// enable listener again
		this._isExecuting = false;
	}
};

/**
 * Namespace for option handlers.
 */
WCF.Option = { };

if (COMPILER_TARGET_DEFAULT) {
	/**
	 * Handles option selection.
	 */
	WCF.Option.Handler = Class.extend({
		/**
		 * Initializes the WCF.Option.Handler class.
		 */
		init: function () {
			this._initOptions();
			
			WCF.DOMNodeInsertedHandler.addCallback('WCF.Option.Handler', $.proxy(this._initOptions, this));
		},
		
		/**
		 * Initializes all options.
		 */
		_initOptions: function () {
			$('.jsEnablesOptions').each($.proxy(this._initOption, this));
		},
		
		/**
		 * Initializes an option.
		 *
		 * @param        integer                index
		 * @param        object                option
		 */
		_initOption: function (index, option) {
			// execute action on init
			this._change(option);
			
			// bind event listener
			$(option).change($.proxy(this._handleChange, this));
		},
		
		/**
		 * Applies whenever an option is changed.
		 *
		 * @param        object                event
		 */
		_handleChange: function (event) {
			this._change($(event.target));
		},
		
		/**
		 * Enables or disables options on option value change.
		 *
		 * @param        object                option
		 */
		_change: function (option) {
			option = $(option);
			
			var disableOptions = eval(option.data('disableOptions'));
			var enableOptions = eval(option.data('enableOptions'));
			
			// determine action by type
			switch (option.getTagName()) {
				case 'input':
					switch (option.attr('type')) {
						case 'checkbox':
							this._execute(option.prop('checked'), disableOptions, enableOptions);
							break;
						
						case 'radio':
							if (option.prop('checked')) {
								var isActive = true;
								if (option.data('isBoolean') && option.val() != 1) {
									isActive = false;
								}
								
								this._execute(isActive, disableOptions, enableOptions);
							}
							break;
					}
					break;
				
				case 'select':
					var $value = option.val();
					var relevantDisableOptions = [];
					var relevantEnableOptions = [];
					
					if (disableOptions.length > 0) {
						for (var $index in disableOptions) {
							var $item = disableOptions[$index];
							
							if ($item.value == $value) {
								relevantDisableOptions.push($item.option);
							}
							else {
								relevantEnableOptions.push($item.option);
							}
						}
					}
					
					if (enableOptions.length > 0) {
						for (var $index in enableOptions) {
							var $item = enableOptions[$index];
							
							if ($item.value == $value) {
								relevantEnableOptions.push($item.option);
							}
							else {
								relevantDisableOptions.push($item.option);
							}
						}
					}
					
					this._execute(true, relevantDisableOptions, relevantEnableOptions);
					break;
			}
		},
		
		/**
		 * Enables or disables options.
		 *
		 * @param        boolean                isActive
		 * @param        array                disableOptions
		 * @param        array                enableOptions
		 */
		_execute: function (isActive, disableOptions, enableOptions) {
			if (disableOptions.length > 0) {
				for (var $i = 0, $size = disableOptions.length; $i < $size; $i++) {
					var $target = disableOptions[$i];
					if ($.wcfIsset($target)) {
						this._enableOption($target, !isActive);
					}
					else {
						var $dl = $('.' + $.wcfEscapeID($target) + 'Input');
						if ($dl.length) {
							this._enableOptions($dl.children('dd').find('input, select, textarea'), !isActive);
						}
					}
				}
			}
			
			if (enableOptions.length > 0) {
				for (var $i = 0, $size = enableOptions.length; $i < $size; $i++) {
					var $target = enableOptions[$i];
					if ($.wcfIsset($target)) {
						this._enableOption($target, isActive);
					}
					else {
						var $dl = $('.' + $.wcfEscapeID($target) + 'Input');
						if ($dl.length) {
							this._enableOptions($dl.children('dd').find('input, select, textarea'), isActive);
						}
					}
				}
			}
		},
		
		/**
		 * Enables/Disables an option.
		 *
		 * @param        string                target
		 * @param        boolean                enable
		 */
		_enableOption: function (target, enable) {
			this._enableOptionElement($('#' + $.wcfEscapeID(target)), enable);
		},
		
		/**
		 * Enables/Disables an option element.
		 *
		 * @param        string                target
		 * @param        boolean                enable
		 */
		_enableOptionElement: function (element, enable) {
			element = $(element);
			var $tagName = element.getTagName();
			
			if ($tagName == 'select' || ($tagName == 'input' && (element.attr('type') == 'checkbox' || element.attr('type') == 'file' || element.attr('type') == 'radio'))) {
				if ($tagName === 'input' && element[0].type === 'radio') {
					if (!element[0].checked) {
						if (enable) element.enable();
						else element.disable();
					}
					else {
						// Skip active radio buttons, this preserves the value on submit,
						// while the user is still unable to move the selection to the other,
						// now disabled options.
					}
				}
				else {
					if (enable) element.enable();
					else element.disable();
				}
				
				if (element.parents('.optionTypeBoolean:eq(0)')) {
					// escape dots so that they are not recognized as class selectors
					var elementId = element.wcfIdentify().replace(/\./g, "\\.");
					
					var noElement = $('#' + elementId + '_no');
					if (enable) noElement.enable();
					else noElement.disable();
					
					var neverElement = $('#' + elementId + '_never');
					if (neverElement.length) {
						if (enable) neverElement.enable();
						else neverElement.disable();
					}
				}
			}
			else {
				if (enable) element.removeAttr('readonly');
				else element.attr('readonly', true);
			}
			
			if (enable) {
				element.closest('dl').removeClass('disabled');
			}
			else {
				element.closest('dl').addClass('disabled');
			}
		},
		
		/**
		 * Enables/Disables an option consisting of multiple form elements.
		 *
		 * @param        string                target
		 * @param        boolean                enable
		 */
		_enableOptions: function (targets, enable) {
			for (var $i = 0, $length = targets.length; $i < $length; $i++) {
				this._enableOptionElement(targets[$i], enable);
			}
		}
	});
}
else {
	WCF.Option.Handler = Class.extend({
		init: function() {},
		_initOptions: function() {},
		_initOption: function() {},
		_handleChange: function() {},
		_change: function() {},
		_execute: function() {},
		_enableOption: function() {},
		_enableOptionElement: function() {},
		_enableOptions: function() {}
	});
}

WCF.PageVisibilityHandler = {
	/**
	 * list of callbacks
	 * @var	WCF.Dictionary
	 */
	_callbacks: new WCF.Dictionary(),
	
	/**
	 * indicates that event listeners are bound
	 * @var	boolean
	 */
	_isListening: false,
	
	/**
	 * name of window's hidden property
	 * @var	string
	 */
	_hiddenFieldName: '',
	
	/**
	 * Adds a new callback.
	 * 
	 * @param	string		identifier
	 * @param	object		callback
	 */
	addCallback: function(identifier, callback) {
		this._bindListener();
		
		if (this._callbacks.isset(identifier)) {
			console.debug("[WCF.PageVisibilityHandler] identifier '" + identifier + "' is already bound to a callback");
			return false;
		}
		
		this._callbacks.add(identifier, callback);
	},
	
	/**
	 * Removes a callback from list.
	 * 
	 * @param	string		identifier
	 */
	removeCallback: function(identifier) {
		if (this._callbacks.isset(identifier)) {
			this._callbacks.remove(identifier);
		}
	},
	
	/**
	 * Binds click event handler.
	 */
	_bindListener: function() {
		if (this._isListening) return;
		
		var $eventName = null;
		if (typeof document.hidden !== "undefined") {
			this._hiddenFieldName = "hidden";
			$eventName = "visibilitychange";
		}
		else if (typeof document.mozHidden !== "undefined") {
			this._hiddenFieldName = "mozHidden";
			$eventName = "mozvisibilitychange";
		}
		else if (typeof document.msHidden !== "undefined") {
			this._hiddenFieldName = "msHidden";
			$eventName = "msvisibilitychange";
		}
		else if (typeof document.webkitHidden !== "undefined") {
			this._hiddenFieldName = "webkitHidden";
			$eventName = "webkitvisibilitychange";
		}
		
		if ($eventName === null) {
			console.debug("[WCF.PageVisibilityHandler] This browser does not support the page visibility API.");
		}
		else {
			$(document).on($eventName, $.proxy(this._executeCallbacks, this));
		}
		
		this._isListening = true;
	},
	
	/**
	 * Executes callbacks if page is hidden/visible again.
	 */
	_executeCallbacks: function(event) {
		if (this._isExecuting) return;
		
		// do not track events while executing callbacks
		this._isExecuting = true;
		
		var $state = document[this._hiddenFieldName];
		this._callbacks.each(function(pair) {
			// execute callback
			pair.value($state);
		});
		
		// enable listener again
		this._isExecuting = false;
	}
};

/**
 * Namespace for table related classes.
 */
WCF.Table = { };

/**
 * Handles empty tables which can be used in combination with WCF.Action.Proxy.
 */
WCF.Table.EmptyTableHandler = Class.extend({
	/**
	 * handler options
	 * @var	object
	 */
	_options: {},
	
	/**
	 * class name of the relevant rows
	 * @var	string
	 */
	_rowClassName: '',
	
	/**
	 * Initializes a new WCF.Table.EmptyTableHandler object.
	 * 
	 * @param	jQuery		tableContainer
	 * @param	string		rowClassName
	 * @param	object		options
	 */
	init: function(tableContainer, rowClassName, options) {
		this._rowClassName = rowClassName;
		this._tableContainer = tableContainer;
		
		this._options = $.extend(true, {
			emptyMessage: null,
			emptyMessageHtml: null,
			messageType: 'info',
			refreshPage: false,
			updatePageNumber: false,
			isTable: (this._tableContainer.find('table').length !== 0)
		}, options || { });
		
		WCF.DOMNodeRemovedHandler.addCallback('WCF.Table.EmptyTableHandler.' + rowClassName, $.proxy(this._remove, this));
	},
	
	/**
	 * Returns the current number of table rows.
	 * 
	 * @return	integer
	 */
	_getRowCount: function() {
		return this._tableContainer.find((this._options.isTable ? 'table tr.' : '.tabularList .') + this._rowClassName).length;
	},
	
	/**
	 * Handles an empty table.
	 */
	_handleEmptyTable: function() {
		if (this._options.emptyMessage) {
			// insert message
			this._tableContainer.replaceWith($('<p />').addClass(this._options.messageType).text(this._options.emptyMessage));
		}
		else if (this._options.emptyMessageHtml) {
			// insert message
			this._tableContainer.replaceWith($('<p />').addClass(this._options.messageType).html(this._options.emptyMessageHtml));
		}
		else if (this._options.refreshPage) {
			// refresh page
			if (this._options.updatePageNumber) {
				// calculate the new page number
				var pageNumberURLComponents = window.location.href.match(/(\?|&)pageNo=(\d+)/g);
				if (pageNumberURLComponents) {
					var currentPageNumber = pageNumberURLComponents[pageNumberURLComponents.length - 1].match(/\d+/g);
					if (this._options.updatePageNumber > 0) {
						currentPageNumber++;
					}
					else {
						currentPageNumber--;
					}
					
					window.location = window.location.href.replace(pageNumberURLComponents[pageNumberURLComponents.length - 1], pageNumberURLComponents[pageNumberURLComponents.length - 1][0] + 'pageNo=' + currentPageNumber);
				}
			}
			else {
				window.location.reload();
			}
		}
		else {
			// simply remove the table container
			this._tableContainer.remove();
		}
	},
	
	/**
	 * Handles the removal of a DOM node.
	 */
	_remove: function(event) {
		if ($.getLength(event)) {
			var element = $(event.target);
			
			// check if DOM element is relevant
			if (element.hasClass(this._rowClassName)) {
				if (this._options.isTable) {
					var tbody = element.parents('tbody:eq(0)');
					
					// check if table will be empty if DOM node is removed
					if (tbody.children('tr').length == 1) {
						this._handleEmptyTable();
					}
				}
				else if (this._getRowCount() === 1) {
					this._handleEmptyTable();
				}
			}
		}
		else if (!this._getRowCount()) {
			this._handleEmptyTable();
		}
	}
});

/**
 * Namespace for search related classes.
 */
WCF.Search = {};

/**
 * Performs a quick search.
 * 
 * @deprecated  3.0 - please use `WoltLabSuite/Core/Ui/Search/Input` instead
 */
WCF.Search.Base = Class.extend({
	/**
	 * notification callback
	 * @var	object
	 */
	_callback: null,
	
	/**
	 * represents index of search string (relative to original caret position)
	 * @var	integer
	 */
	_caretAt: -1,
	
	/**
	 * class name
	 * @var	string
	 */
	_className: '',
	
	/**
	 * comma separated list
	 * @var	boolean
	 */
	_commaSeperated: false,
	
	/**
	 * delay in milliseconds before a request is send to the server
	 * @var	integer
	 */
	_delay: 0,
	
	/**
	 * list with values that are excluded from searching
	 * @var	array
	 */
	_excludedSearchValues: [],
	
	/**
	 * count of available results
	 * @var	integer
	 */
	_itemCount: 0,
	
	/**
	 * item index, -1 if none is selected
	 * @var	integer
	 */
	_itemIndex: -1,
	
	/**
	 * @var string
	 */
	_lastValue: '',
	
	/**
	 * result list
	 * @var	jQuery
	 */
	_list: null,
	
	/**
	 * old search string, used for comparison
	 * @var	array<string>
	 */
	_oldSearchString: [ ],
	
	/**
	 * action proxy
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * search input field
	 * @var	jQuery
	 */
	_searchInput: null,
	
	/**
	 * minimum search input length, MUST be 1 or higher
	 * @var	integer
	 */
	_triggerLength: 3,
	
	/**
	 * delay timer
	 * @var	WCF.PeriodicalExecuter
	 */
	_timer: null,
	
	/**
	 * Initializes a new search.
	 * 
	 * @param	jQuery		searchInput
	 * @param	object		callback
	 * @param	array		excludedSearchValues
	 * @param	boolean		commaSeperated
	 * @param	boolean		showLoadingOverlay
	 */
	init: function(searchInput, callback, excludedSearchValues, commaSeperated, showLoadingOverlay) {
		if (callback !== null && callback !== undefined && !$.isFunction(callback)) {
			console.debug("[WCF.Search.Base] The given callback is invalid, aborting.");
			return;
		}
		
		this._callback = (callback) ? callback : null;
		this._caretAt = -1;
		this._delay = 0;
		this._excludedSearchValues = [];
		this._lastValue = '';
		if (excludedSearchValues) {
			this._excludedSearchValues = excludedSearchValues;
		}
		
		this._searchInput = $(searchInput);
		if (!this._searchInput.length) {
			console.debug("[WCF.Search.Base] Selector '" + searchInput + "' for search input is invalid, aborting.");
			return;
		}
		
		this._searchInput.keydown($.proxy(this._keyDown, this)).keyup($.proxy(this._keyUp, this)).wrap('<span class="dropdown" />');
		
		if ($.browser.mozilla && $.browser.touch) {
			this._searchInput.on('input', $.proxy(this._keyUp, this));
		}
		
		this._list = $('<ul class="dropdownMenu" />').insertAfter(this._searchInput);
		this._commaSeperated = (commaSeperated) ? true : false;
		this._oldSearchString = [ ];
		
		this._itemCount = 0;
		this._itemIndex = -1;
		
		this._proxy = new WCF.Action.Proxy({
			showLoadingOverlay: (showLoadingOverlay !== true ? false : true),
			success: $.proxy(this._success, this),
			autoAbortPrevious: true
		});
		
		if (this._searchInput.is('input')) {
			this._searchInput.attr('autocomplete', 'off');
		}
		
		this._searchInput.blur($.proxy(this._blur, this));
		
		WCF.Dropdown.initDropdownFragment(this._searchInput.parent(), this._list);
	},
	
	/**
	 * Closes the dropdown after a short delay.
	 */
	_blur: function() {
		var self = this;
		new WCF.PeriodicalExecuter(function(pe) {
			if (self._list.is(':visible')) {
				self._clearList(false);
			}
			
			pe.stop();
		}, 250);
	},
	
	/**
	 * Blocks execution of 'Enter' event.
	 * 
	 * @param	object		event
	 */
	_keyDown: function(event) {
		if (event.which === $.ui.keyCode.ENTER) {
			var $dropdown = this._searchInput.parents('.dropdown');
			
			if ($dropdown.data('disableAutoFocus')) {
				if (this._itemIndex !== -1) {
					event.preventDefault();
				}
			}
			else if ($dropdown.data('preventSubmit') || this._itemIndex !== -1) {
				event.preventDefault();
			}
		}
	},
	
	/**
	 * Performs a search upon key up.
	 * 
	 * @param	object		event
	 */
	_keyUp: function(event) {
		// handle arrow keys and return key
		switch (event.which) {
			case 37: // arrow-left
			case 39: // arrow-right
				return;
			
			case 38: // arrow up
				this._selectPreviousItem();
				return;
			
			case 40: // arrow down
				this._selectNextItem();
				return;
			
			case 13: // return key
				return this._selectElement(event);
		}
		
		var $content = this._getSearchString(event);
		if ($content === '') {
			this._clearList(false);
		}
		else if ($content.length >= this._triggerLength) {
			if (this._lastValue === $content) {
				return;
			}
			
			this._lastValue = $content;
			
			var $parameters = {
				data: {
					excludedSearchValues: this._excludedSearchValues,
					searchString: $content
				}
			};
			
			if (this._delay) {
				if (this._timer !== null) {
					this._timer.stop();
				}
				
				var self = this;
				this._timer = new WCF.PeriodicalExecuter(function() {
					self._queryServer($parameters);
					
					self._timer.stop();
					self._timer = null;
				}, this._delay);
			}
			else {
				this._queryServer($parameters);
			}
		}
		else {
			// input below trigger length
			this._clearList(false);
		}
	},
	
	/**
	 * Queries the server.
	 * 
	 * @param	object		parameters
	 */
	_queryServer: function(parameters) {
		this._searchInput.parents('.searchBar').addClass('loading');
		this._proxy.setOption('data', {
			actionName: 'getSearchResultList',
			className: this._className,
			interfaceName: 'wcf\\data\\ISearchAction',
			parameters: this._getParameters(parameters)
		});
		this._proxy.sendRequest();
	},
	
	/**
	 * Sets query delay in miliseconds.
	 * 
	 * @param	integer		delay
	 */
	setDelay: function(delay) {
		this._delay = delay;
	},
	
	/**
	 * Selects the next item in list.
	 */
	_selectNextItem: function() {
		if (this._itemCount === 0) {
			return;
		}
		
		// remove previous marking
		this._itemIndex++;
		if (this._itemIndex === this._itemCount) {
			this._itemIndex = 0;
		}
		
		this._highlightSelectedElement();
	},
	
	/**
	 * Selects the previous item in list.
	 */
	_selectPreviousItem: function() {
		if (this._itemCount === 0) {
			return;
		}
		
		this._itemIndex--;
		if (this._itemIndex === -1) {
			this._itemIndex = this._itemCount - 1;
		}
		
		this._highlightSelectedElement();
	},
	
	/**
	 * Highlights the active item.
	 */
	_highlightSelectedElement: function() {
		this._list.find('li').removeClass('dropdownNavigationItem');
		this._list.find('li:eq(' + this._itemIndex + ')').addClass('dropdownNavigationItem');
	},
	
	/**
	 * Selects the active item by pressing the return key.
	 * 
	 * @param	object		event
	 * @return	boolean
	 */
	_selectElement: function(event) {
		if (this._itemCount === 0) {
			return true;
		}
		
		this._list.find('li.dropdownNavigationItem').trigger('click');
		
		return false;
	},
	
	/**
	 * Returns search string.
	 * 
	 * @return	string
	 */
	_getSearchString: function(event) {
		var $searchString = $.trim(this._searchInput.val());
		if (this._commaSeperated) {
			var $keyCode = event.keyCode || event.which;
			if ($keyCode == $.ui.keyCode.COMMA) {
				// ignore event if char is ','
				return '';
			}
			
			var $current = $searchString.split(',');
			var $length = $current.length;
			for (var $i = 0; $i < $length; $i++) {
				// remove whitespaces at the beginning or end
				$current[$i] = $.trim($current[$i]);
			}
			
			for (var $i = 0; $i < $length; $i++) {
				var $part = $current[$i];
				
				if (this._oldSearchString[$i]) {
					// compare part
					if ($part != this._oldSearchString[$i]) {
						// current part was changed
						$searchString = $part;
						this._caretAt = $i;
						
						break;
					}
				}
				else {
					// new part was added
					$searchString = $part;
					break;
				}
			}
			
			this._oldSearchString = $current;
		}
		
		return $searchString;
	},
	
	/**
	 * Returns parameters for quick search.
	 * 
	 * @param	object		parameters
	 * @return	object
	 */
	_getParameters: function(parameters) {
		return parameters;
	},
	
	/**
	 * Evalutes search results.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		this._clearList(false);
		this._searchInput.parents('.searchBar').removeClass('loading');
		
		if ($.getLength(data.returnValues)) {
			for (var $i in data.returnValues) {
				var $item = data.returnValues[$i];
				
				this._createListItem($item);
			}
		}
		else if (!this._handleEmptyResult()) {
			return;
		}
		
		WCF.CloseOverlayHandler.addCallback('WCF.Search.Base', $.proxy(function() { this._clearList(); }, this));
		
		var $containerID = this._searchInput.parents('.dropdown').wcfIdentify();
		if (!WCF.Dropdown.getDropdownMenu($containerID).hasClass('dropdownOpen')) {
			WCF.Dropdown.toggleDropdown($containerID, true);
			
			this._openDropdown();
		}
		
		// pre-select first item
		this._itemIndex = -1;
		if (!WCF.Dropdown.getDropdown($containerID).data('disableAutoFocus')) {
			this._selectNextItem();
		}
	},
	
	/**
	 * Is called after the dropdown has been opened.
	 */
	_openDropdown: function() {
		// does nothing
	},
	
	/**
	 * Handles empty result lists, should return false if dropdown should be hidden.
	 * 
	 * @return	boolean
	 */
	_handleEmptyResult: function() {
		return false;
	},
	
	/**
	 * Creates a new list item.
	 * 
	 * @param	object		item
	 * @return	jQuery
	 */
	_createListItem: function(item) {
		var $listItem = $('<li><span>' + WCF.String.escapeHTML(item.label) + '</span></li>').appendTo(this._list);
		$listItem.data('objectID', item.objectID).data('label', item.label).click($.proxy(this._executeCallback, this));
		
		this._itemCount++;
		
		return $listItem;
	},
	
	/**
	 * Executes callback upon result click.
	 * 
	 * @param	object		event
	 */
	_executeCallback: function(event) {
		var $clearSearchInput = false;
		var $listItem = $(event.currentTarget);
		// notify callback
		if (this._commaSeperated) {
			// auto-complete current part
			var $result = $listItem.data('label');
			this._oldSearchString[this._caretAt] = $result;
			this._searchInput.val(this._oldSearchString.join(', '));
			
			if ($.browser.webkit) {
				// chrome won't display the new value until the textarea is rendered again
				// this quick fix forces chrome to render it again, even though it changes nothing
				this._searchInput.css({ display: 'block' });
			}
			
			// set focus on input field again
			var $position = this._searchInput.val().toLowerCase().indexOf($result.toLowerCase()) + $result.length;
			this._searchInput.focus().setCaret($position);
		}
		else {
			if (this._callback === null) {
				this._searchInput.val($listItem.data('label'));
			}
			else {
				$clearSearchInput = (this._callback($listItem.data()) === true) ? true : false;
			}
		}
		
		// close list and revert input
		this._clearList($clearSearchInput);
	},
	
	/**
	 * Closes the suggestion list and clears search input on demand.
	 * 
	 * @param	boolean		clearSearchInput
	 */
	_clearList: function(clearSearchInput) {
		if (clearSearchInput && !this._commaSeperated) {
			this._searchInput.val('');
		}
		
		// close dropdown
		WCF.Dropdown.getDropdown(this._searchInput.parents('.dropdown').wcfIdentify()).removeClass('dropdownOpen');
		WCF.Dropdown.getDropdownMenu(this._searchInput.parents('.dropdown').wcfIdentify()).removeClass('dropdownOpen');
		
		this._list.end().empty();
		
		WCF.CloseOverlayHandler.removeCallback('WCF.Search.Base');
		
		// reset item navigation
		this._itemCount = 0;
		this._itemIndex = -1;
	},
	
	/**
	 * Adds an excluded search value.
	 * 
	 * @param	string		value
	 */
	addExcludedSearchValue: function(value) {
		if (!WCF.inArray(value, this._excludedSearchValues)) {
			this._excludedSearchValues.push(value);
		}
	},
	
	/**
	 * Removes an excluded search value.
	 * 
	 * @param	string		value
	 */
	removeExcludedSearchValue: function(value) {
		var index = $.inArray(value, this._excludedSearchValues);
		if (index != -1) {
			this._excludedSearchValues.splice(index, 1);
		}
	}
});

/**
 * Provides quick search for users and user groups.
 * 
 * @see	WCF.Search.Base
 * @deprecated  3.0 - please use `WoltLabSuite/Core/Ui/User/Search/Input` instead
 */
WCF.Search.User = WCF.Search.Base.extend({
	/**
	 * @see	WCF.Search.Base._className
	 */
	_className: 'wcf\\data\\user\\UserAction',
	
	/**
	 * include user groups in search
	 * @var	boolean
	 */
	_includeUserGroups: false,
	
	/**
	 * @see	WCF.Search.Base.init()
	 */
	init: function(searchInput, callback, includeUserGroups, excludedSearchValues, commaSeperated) {
		this._includeUserGroups = includeUserGroups;
		
		this._super(searchInput, callback, excludedSearchValues, commaSeperated);
	},
	
	/**
	 * @see	WCF.Search.Base._getParameters()
	 */
	_getParameters: function(parameters) {
		parameters.data.includeUserGroups = this._includeUserGroups ? 1 : 0;
		
		return parameters;
	},
	
	/**
	 * @see	WCF.Search.Base._createListItem()
	 */
	_createListItem: function(item) {
		var $listItem = this._super(item);
		
		var $icon = null;
		if (item.icon) {
			$icon = $(item.icon);
		}
		else if (this._includeUserGroups && item.type === 'group') {
			$icon = $('<span class="icon icon16 fa-users" />');
		}
		
		if ($icon) {
			var $label = $listItem.find('span').detach();
			
			var $box16 = $('<div />').addClass('box16').appendTo($listItem);
			
			$box16.append($icon);
			$box16.append($('<div />').append($label));
		}
		
		// insert item type
		$listItem.data('type', item.type);
		
		return $listItem;
	}
});

/**
 * Namespace for system-related classes.
 */
WCF.System = { };

/**
 * Namespace for dependency-related classes.
 */
WCF.System.Dependency = { };

/**
 * JavaScript Dependency Manager.
 */
WCF.System.Dependency.Manager = {
	/**
	 * list of callbacks grouped by identifier
	 * @var	object
	 */
	_callbacks: { },
	
	/**
	 * list of loaded identifiers
	 * @var	array<string>
	 */
	_loaded: [ ],
	
	/**
	 * list of setup callbacks grouped by identifier
	 * @var	object
	 */
	_setupCallbacks: { },
	
	/**
	 * Registers a callback for given identifier, will be executed after all setup
	 * callbacks have been invoked.
	 * 
	 * @param	string		identifier
	 * @param	object		callback
	 */
	register: function(identifier, callback) {
		if (!$.isFunction(callback)) {
			console.debug("[WCF.System.Dependency.Manager] Callback for identifier '" + identifier + "' is invalid, aborting.");
			return;
		}
		
		// already loaded, invoke now
		if (WCF.inArray(identifier, this._loaded)) {
			setTimeout(function() {
				callback();
			}, 1);
		}
		else {
			if (!this._callbacks[identifier]) {
				this._callbacks[identifier] = [ ];
			}
			
			this._callbacks[identifier].push(callback);
		}
	},
	
	/**
	 * Registers a setup callback for given identifier, will be invoked
	 * prior to all other callbacks.
	 * 
	 * @param	string		identifier
	 * @param	object		callback
	 */
	setup: function(identifier, callback) {
		if (!$.isFunction(callback)) {
			console.debug("[WCF.System.Dependency.Manager] Setup callback for identifier '" + identifier + "' is invalid, aborting.");
			return;
		}
		
		if (!this._setupCallbacks[identifier]) {
			this._setupCallbacks[identifier] = [ ];
		}
		
		this._setupCallbacks[identifier].push(callback);
	},
	
	/**
	 * Invokes all callbacks for given identifier and marks it as loaded.
	 * 
	 * @param	string		identifier
	 */
	invoke: function(identifier) {
		if (this._setupCallbacks[identifier]) {
			for (var $i = 0, $length = this._setupCallbacks[identifier].length; $i < $length; $i++) {
				this._setupCallbacks[identifier][$i]();
			}
			
			delete this._setupCallbacks[identifier];
		}
		
		this._loaded.push(identifier);
		
		if (this._callbacks[identifier]) {
			for (var $i = 0, $length = this._callbacks[identifier].length; $i < $length; $i++) {
				this._callbacks[identifier][$i]();
			}
			
			delete this._callbacks[identifier];
		}
	},
	
	reset: function(identifier) {
		var index = this._loaded.indexOf(identifier);
		if (index !== -1) {
			this._loaded.splice(index, 1);
		}
	}
};

/**
 * Provides flexible dropdowns for tab-based menus.
 */
WCF.System.FlexibleMenu = {
	/**
	 * Initializes the WCF.System.FlexibleMenu class.
	 */
	init: function() { /* does nothing */ },
	
	/**
	 * Registers a tab-based menu by id.
	 * 
	 * Required DOM:
	 * <container>
	 * 	<ul style="white-space: nowrap">
	 * 		<li>tab 1</li>
	 * 		<li>tab 2</li>
	 * 		...
	 * 		<li>tab n</li>
	 * 	</ul>
	 * </container>
	 * 
	 * @param	string		containerID
	 */
	registerMenu: function(containerID) {
		require(['WoltLabSuite/Core/Ui/FlexibleMenu'], function(UiFlexibleMenu) {
			UiFlexibleMenu.register(containerID);
		});
	},
	
	/**
	 * Rebuilds a container, will be automatically invoked on window resize and registering.
	 * 
	 * @param	string		containerID
	 */
	rebuild: function(containerID) {
		require(['WoltLabSuite/Core/Ui/FlexibleMenu'], function(UiFlexibleMenu) {
			UiFlexibleMenu.rebuild(containerID);
		});
	}
};

/**
 * Namespace for mobile device-related classes.
 */
WCF.System.Mobile = { };

/**
 * Stores object references for global access.
 */
WCF.System.ObjectStore = {
	/**
	 * list of objects grouped by identifier
	 * @var	object<array>
	 */
	_objects: { },
	
	/**
	 * Adds a new object to the collection.
	 * 
	 * @param	string		identifier
	 * @param	object		object
	 */
	add: function(identifier, obj) {
		if (this._objects[identifier] === undefined) {
			this._objects[identifier] = [ ];
		}
		
		this._objects[identifier].push(obj);
	},
	
	/**
	 * Invokes a callback passing the matching objects as a parameter.
	 * 
	 * @param	string		identifier
	 * @param	object		callback
	 */
	invoke: function(identifier, callback) {
		if (this._objects[identifier]) {
			for (var $i = 0; $i < this._objects[identifier].length; $i++) {
				callback(this._objects[identifier][$i]);
			}
		}
	}
};

/**
 * Stores captcha callbacks used for captchas in AJAX contexts.
 */
WCF.System.Captcha = {
	/**
	 * ids of registered captchas
	 * @var	{string[]}
	 */
	_registeredCaptchas: [],
	
	/**
	 * Adds a callback for a certain captcha.
	 * 
	 * @param	string		captchaID
	 * @param	function	callback
	 */
	addCallback: function(captchaID, callback) {
		require(['WoltLabSuite/Core/Controller/Captcha'], function(ControllerCaptcha) {
			try {
				ControllerCaptcha.add(captchaID, callback);
				
				this._registeredCaptchas.push(captchaID);
			}
			catch (e) {
				if (e instanceof TypeError) {
					console.debug('[WCF.System.Captcha] Given callback is no function');
					return;
				}
				
				// ignore other errors
			}
		}.bind(this));
	},
	
	/**
	 * Returns the captcha data for the captcha with the given id.
	 * 
	 * @return	object
	 */
	getData: function(captchaID) {
		var returnValue;
		
		if (this._registeredCaptchas.indexOf(captchaID) === -1) {
			return returnValue;
		}
		
		var ControllerCaptcha = require('WoltLabSuite/Core/Controller/Captcha');
		try {
			returnValue = ControllerCaptcha.getData(captchaID);
		}
		catch (e) {
			console.debug('[WCF.System.Captcha] Unknow captcha id "' + captchaID + '"');
		}
		
		return returnValue;
	},
	
	/**
	 * Removes the callback with the given captcha id.
	 */
	removeCallback: function(captchaID) {
		require(['WoltLabSuite/Core/Controller/Captcha'], function(ControllerCaptcha) {
			try {
				ControllerCaptcha.delete(captchaID);
				
				this._registeredCaptchas.splice(this._registeredCaptchas.indexOf(item), 1);
			}
			catch (e) {
				// ignore errors for unknown captchas
			}
		}.bind(this));
	}
};

WCF.System.Page = { };

/**
 * System notification overlays.
 * 
 * @deprecated	3.0 - please use `Ui/Notification` instead
 * 
 * @param	string		message
 * @param	string		cssClassNames
 */
WCF.System.Notification = Class.extend({
	_cssClassNames: '',
	_message: '',
	
	/**
	 * Creates a new system notification overlay.
	 * 
	 * @param	string		message
	 * @param	string		cssClassNames
	 */
	init: function(message, cssClassNames) {
		this._cssClassNames = cssClassNames || '';
		this._message = message || '';
	},
	
	/**
	 * Shows the notification overlay.
	 * 
	 * @param	object		callback
	 * @param	integer		duration
	 * @param	string		message
	 * @param	string		cssClassName
	 */
	show: function(callback, duration, message, cssClassNames) {
		require(['Ui/Notification'], (function(UiNotification) {
			UiNotification.show(
				message || this._message,
				callback,
				cssClassNames || this._cssClassNames
			);
		}).bind(this));
	}
});

/**
 * Provides dialog-based confirmations.
 *
 * @deprecated	3.0 - please use `Ui/Confirmation` instead
 */
WCF.System.Confirmation = {
	/**
	 * Displays a confirmation dialog.
	 * 
	 * @param	string		message
	 * @param	object		callback
	 * @param	object		parameters
	 * @param	jQuery		template
	 * @param       boolean         messageIsHtml
	 */
	show: function(message, callback, parameters, template, messageIsHtml) {
		if (typeof template === 'object') {
			var $wrapper = $('<div />');
			$wrapper.append(template);
			template = $wrapper.html();
		}
		require(['Ui/Confirmation'], function(UiConfirmation) {
			UiConfirmation.show({
				legacyCallback: callback,
				message: message,
				parameters: parameters,
				template: (template || ''),
				messageIsHtml: (messageIsHtml === true)
			});
		});
	}
};

/**
 * Disables the ability to scroll the page.
 */
WCF.System.DisableScrolling = {
	/**
	 * number of times scrolling was disabled (nested calls)
	 * @var	integer
	 */
	_depth: 0,
	
	/**
	 * old overflow-value of the body element
	 * @var	string
	 */
	_oldOverflow: null,
	
	/**
	 * Disables scrolling.
	 */
	disable: function () {
		// do not block scrolling on touch devices
		if ($.browser.touch) {
			return;
		}
		
		if (this._depth === 0) {
			this._oldOverflow = $(document.body).css('overflow');
			$(document.body).css('overflow', 'hidden');
		}
		
		this._depth++;
	},
	
	/**
	 * Enables scrolling again.
	 * Must be called the same number of times disable() was called to enable scrolling.
	 */
	enable: function () {
		if (this._depth === 0) return;
		
		this._depth--;
		
		if (this._depth === 0) {
			$(document.body).css('overflow', this._oldOverflow);
		}
	}
};

/**
 * Disables the ability to zoom the page.
 */
WCF.System.DisableZoom = {
	/**
	 * number of times zoom was disabled (nested calls)
	 * @var	integer
	 */
	_depth: 0,
	
	/**
	 * old viewport settings in meta[name=viewport]
	 * @var	string
	 */
	_oldViewportSettings: null,
	
	/**
	 * Disables zooming.
	 */
	disable: function () {
		if (this._depth === 0) {
			var $meta = $('meta[name=viewport]');
			this._oldViewportSettings = $meta.attr('content');
			$meta.attr('content', this._oldViewportSettings + ',maximum-scale=1');
		}
		
		this._depth++;
	},
	
	/**
	 * Enables scrolling again.
	 * Must be called the same number of times disable() was called to enable scrolling.
	 */
	enable: function () {
		if (this._depth === 0) return;
		
		this._depth--;
		
		if (this._depth === 0) {
			$('meta[name=viewport]').attr('content', this._oldViewportSettings);
		}
	}
};

/**
 * Puts an element into HTML 5 fullscreen mode.
 */
WCF.System.Fullscreen = {
	/**
	 * Puts the given element into full screen mode.
	 * Note: This must be a raw HTMLElement, not a jQuery wrapped one.
	 * Note: This must be called from a user triggered event listener for
	 * 	security reasons.
	 * 
	 * @param	object		Element to show full screen.
	 */
	enterFullscreen: function (element) {
		if (element.requestFullscreen) {
			element.requestFullscreen();
		}
		else if (element.msRequestFullscreen) {
			element.msRequestFullscreen();
		}
		else if (element.mozRequestFullScreen) {
			element.mozRequestFullScreen();
		}
		else if (element.webkitRequestFullscreen) {
			element.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
		}
	},
	/**
	 * Toggles full screen mode. Either calls `enterFullscreen` with the given
	 * element, if full screen mode is not active. Calls `exitFullscreen`
	 * otherwise.
	 */
	toggleFullscreen: function (element) {
		if (this.getFullscreenElement() === null) {
			this.enterFullscreen(element);
		}
		else {
			this.exitFullscreen();
		}
	},
	/**
	 * Retrieves the element that is shown in full screen mode.
	 * Returns null if either full screen mode is not supported or
	 * if full screen mode is not active.
	 * 
	 * @return	object
	 */
	getFullscreenElement: function () {
		if (document.fullscreenElement) {
			return document.fullscreenElement;
		}
		else if (document.mozFullScreenElement) {
			return document.mozFullScreenElement;
		}
		else if (document.webkitFullscreenElement) {
			return document.webkitFullscreenElement;
		}
		else if (document.msFullscreenElement) {
			return document.msFullscreenElement;
		}
		
		return null;
	},
	/**
	 * Exits full screen mode.
	 */
	exitFullscreen: function () {
		if (document.exitFullscreen) {
			document.exitFullscreen();
		}
		else if (document.msExitFullscreen) {
			document.msExitFullscreen();
		}
		else if (document.mozCancelFullScreen) {
			document.mozCancelFullScreen();
		}
		else if (document.webkitExitFullscreen) {
			document.webkitExitFullscreen();
		}
	},
	/**
	 * Returns whether the full screen API is supported in this browser.
	 * 
	 * @return	boolean
	 */
	isSupported: function () {
		if (document.documentElement.requestFullscreen || document.documentElement.msRequestFullscreen || document.documentElement.mozRequestFullScreen || document.documentElement.webkitRequestFullscreen) {
			return true;
		}
		
		return false;
	}
};

/**
 * Provides the 'jump to page' overlay.
 * 
 * @deprecated	3.0 - use `WoltLabSuite/Core/Ui/Page/JumpTo` instead
 */
WCF.System.PageNavigation = {
	init: function(selector, callback) {
		require(['WoltLabSuite/Core/Ui/Page/JumpTo'], function(UiPageJumpTo) {
			var elements = elBySelAll(selector);
			for (var i = 0, length = elements.length; i < length; i++) {
				UiPageJumpTo.init(elements[i], callback);
			}
		});
	}
};

/**
 * Sends periodical requests to protect the session from expiring. By default
 * it will send a request 1 minute before it would expire.
 * 
 * @param	integer		seconds
 */
WCF.System.KeepAlive = Class.extend({
	/**
	 * Initializes the WCF.System.KeepAlive class.
	 * 
	 * @param	integer		seconds
	 */
	init: function(seconds) {
		new WCF.PeriodicalExecuter(function(pe) {
			new WCF.Action.Proxy({
				autoSend: true,
				data: {
					actionName: 'keepAlive',
					className: 'wcf\\data\\session\\SessionAction'
				},
				failure: function() { pe.stop(); },
				showLoadingOverlay: false,
				success: function(data) {
					WCF.System.PushNotification.executeCallbacks(data);
				},
				suppressErrors: true
			});
		}, (seconds * 1000));
	}
});

/**
 * System-wide handler for push notifications.
 */
WCF.System.PushNotification = {
	/**
	 * list of callbacks grouped by type
	 * @var	object<array>
	 */
	_callbacks: { },
	
	/**
	 * Adds a callback for a specific notification type.
	 * 
	 * @param	string		type
	 * @param	object		callback
	 */
	addCallback: function(type, callback) {
		if (this._callbacks[type] === undefined) {
			this._callbacks[type] = [ ];
		}
		
		this._callbacks[type].push(callback);
	},
	
	/**
	 * Executes all registered callbacks by type.
	 * 
	 * @param	object		data
	 */
	executeCallbacks: function(data) {
		for (var $type in data.returnValues) {
			if (this._callbacks[$type] !== undefined) {
				for (var $i = 0; $i < this._callbacks[$type].length; $i++) {
					this._callbacks[$type][$i](data.returnValues[$type]);
				}
			}
		}
	}
};

/**
 * System-wide event system.
 * 
 * @deprecated	3.0 - please use `EventHandler` instead
 */
WCF.System.Event = {
	/**
	 * Registers a new event listener.
	 * 
	 * @param	string		identifier
	 * @param	string		action
	 * @param	object		listener
	 * @return	string
	 */
	addListener: function(identifier, action, listener) {
		return window.__wcf_bc_eventHandler.add(identifier, action, listener);
	},
	
	/**
	 * Removes a listener, requires the uuid returned by addListener().
	 * 
	 * @param	string		identifier
	 * @param	string		action
	 * @param	string		uuid
	 * @return	boolean
	 */
	removeListener: function(identifier, action, uuid) {
		return window.__wcf_bc_eventHandler.remove(identifier, action, uuid);
	},
	
	/**
	 * Removes all registered event listeners for given identifier and action.
	 * 
	 * @param	string		identifier
	 * @param	string		action
	 * @return	boolean
	 */
	removeAllListeners: function(identifier, action) {
		return window.__wcf_bc_eventHandler.removeAll(identifier, action);
	},
	
	/**
	 * Fires a new event and notifies all registered event listeners.
	 * 
	 * @param	string		identifier
	 * @param	string		action
	 * @param	object		data
	 */
	fireEvent: function(identifier, action, data) {
		window.__wcf_bc_eventHandler.fire(identifier, action, data);
	}
};

if (COMPILER_TARGET_DEFAULT) {
	/**
	 * Worker support for frontend based upon DatabaseObjectActions.
	 *
	 * @param        string                className
	 * @param        string                title
	 * @param        object                parameters
	 * @param        object                callback
	 */
	WCF.System.Worker = Class.extend({
		/**
		 * worker aborted
		 * @var        boolean
		 */
		_aborted: false,
		
		/**
		 * DBOAction method name
		 * @var        string
		 */
		_actionName: '',
		
		/**
		 * callback invoked after worker completed
		 * @var        object
		 */
		_callback: null,
		
		/**
		 * DBOAction class name
		 * @var        string
		 */
		_className: '',
		
		/**
		 * dialog object
		 * @var        jQuery
		 */
		_dialog: null,
		
		/**
		 * action proxy
		 * @var        WCF.Action.Proxy
		 */
		_proxy: null,
		
		/**
		 * dialog title
		 * @var        string
		 */
		_title: '',
		
		/**
		 * Initializes a new worker instance.
		 *
		 * @param        string                actionName
		 * @param        string                className
		 * @param        string                title
		 * @param        object                parameters
		 * @param        object                callback
		 * @param        object                confirmMessage
		 */
		init: function (actionName, className, title, parameters, callback) {
			this._aborted = false;
			this._actionName = actionName;
			this._callback = callback || null;
			this._className = className;
			this._dialog = null;
			this._proxy = new WCF.Action.Proxy({
				autoSend: true,
				data: {
					actionName: this._actionName,
					className: this._className,
					parameters: parameters || {}
				},
				showLoadingOverlay: false,
				success: $.proxy(this._success, this)
			});
			this._title = title;
		},
		
		/**
		 * Handles response from server.
		 *
		 * @param        object                data
		 */
		_success: function (data) {
			// init binding
			if (this._dialog === null) {
				this._dialog = $('<div />').hide().appendTo(document.body);
				this._dialog.wcfDialog({
					closeConfirmMessage: WCF.Language.get('wcf.worker.abort.confirmMessage'),
					closeViaModal: false,
					onClose: $.proxy(function () {
						this._aborted = true;
						this._proxy.abortPrevious();
						
						window.location.reload();
					}, this),
					title: this._title
				});
			}
			
			if (this._aborted) {
				return;
			}
			
			if (data.returnValues.template) {
				this._dialog.html(data.returnValues.template);
			}
			
			// update progress
			this._dialog.find('progress').attr(
				'value',
				data.returnValues.progress
			).text(data.returnValues.progress + '%').next('span').text(data.returnValues.progress + '%');
			
			// worker is still busy with its business, carry on
			if (data.returnValues.progress < 100) {
				// send request for next loop
				var $parameters = data.returnValues.parameters || {};
				$parameters.loopCount = data.returnValues.loopCount;
				
				this._proxy.setOption('data', {
					actionName: this._actionName,
					className: this._className,
					parameters: $parameters
				});
				this._proxy.sendRequest();
			}
			else if (this._callback !== null) {
				this._callback(this, data);
			}
			else {
				// exchange icon
				this._dialog.find('.fa-spinner').removeClass('fa-spinner').addClass('fa-check green');
				this._dialog.find('.contentHeader h1').text(WCF.Language.get(
					'wcf.global.worker.completed'));
				
				// display continue button
				var $formSubmit = $('<div class="formSubmit" />').appendTo(this._dialog);
				$('<button class="buttonPrimary">' + WCF.Language.get('wcf.global.button.next') + '</button>').appendTo(
					$formSubmit).focus().click(function () {
					if (data.returnValues.redirectURL) {
						window.location = data.returnValues.redirectURL;
					}
					else {
						window.location.reload();
					}
				});
				
				this._dialog.wcfDialog('render');
			}
		}
	});
	
	/**
	 * Default implementation for inline editors.
	 *
	 * @param        string                elementSelector
	 */
	WCF.InlineEditor = Class.extend({
		/**
		 * list of registered callbacks
		 * @var        array<object>
		 */
		_callbacks: [],
		
		/**
		 * list of dropdown selections
		 * @var        object
		 */
		_dropdowns: {},
		
		/**
		 * list of container elements
		 * @var        object
		 */
		_elements: {},
		
		/**
		 * notification object
		 * @var        WCF.System.Notification
		 */
		_notification: null,
		
		/**
		 * list of known options
		 * @var        array<object>
		 */
		_options: [],
		
		/**
		 * action proxy
		 * @var        WCF.Action.Proxy
		 */
		_proxy: null,
		
		/**
		 * list of trigger elements by element id
		 * @var        object<object>
		 */
		_triggerElements: {},
		
		/**
		 * list of data to update upon success
		 * @var        array<object>
		 */
		_updateData: [],
		
		/**
		 * element selector
		 * @var         string
		 */
		_elementSelector: null,
		
		/**
		 * quick option for the inline editor
		 * @var         string
		 */
		_quickOption: null,
		
		/**
		 * Initializes a new inline editor.
		 */
		init: function (elementSelector) {
			this._elementSelector = elementSelector;
			
			var $elements = $(elementSelector);
			if (!$elements.length) {
				return;
			}
			
			this._setOptions();
			for (var $i = 0, $length = this._options.length; $i < $length; $i++) {
				if (this._options[$i].isQuickOption) {
					this._quickOption = this._options[$i].optionName;
					break;
				}
			}
			
			this.rebuild();
			
			WCF.DOMNodeInsertedHandler.addCallback(
				'WCF.InlineEditor' + this._elementSelector.hashCode(),
				$.proxy(this.rebuild, this)
			);
			
			this._proxy = new WCF.Action.Proxy({
				success: $.proxy(this._success, this)
			});
			
			WCF.CloseOverlayHandler.addCallback('WCF.InlineEditor', $.proxy(this._closeAll, this));
			
			this._notification = new WCF.System.Notification(
				WCF.Language.get('wcf.global.success'),
				'success'
			);
		},
		
		/**
		 * Identify new elements and adds the event listeners to them.
		 */
		rebuild: function () {
			var $elements = $(this._elementSelector);
			var self = this;
			$elements.each(function (index, element) {
				var $element = $(element);
				var $elementID = $element.wcfIdentify();
				
				if (self._elements[$elementID] === undefined) {
					// find trigger element
					var $trigger = self._getTriggerElement($element);
					if ($trigger === null || $trigger.length !== 1) {
						return;
					}
					
					$trigger.on(WCF_CLICK_EVENT, $.proxy(self._show, self)).data(
						'elementID',
						$elementID
					);
					if (self._quickOption) {
						// simulate click on target action
						$trigger.disableSelection().data(
							'optionName',
							self._quickOption
						).dblclick($.proxy(self._click, self));
					}
					
					// store reference
					self._elements[$elementID] = $element;
				}
			});
		},
		
		/**
		 * Closes all inline editors.
		 */
		_closeAll: function () {
			for (var $elementID in this._elements) {
				this._hide($elementID);
			}
		},
		
		/**
		 * Sets options for this inline editor.
		 */
		_setOptions: function () {
			this._options = [];
		},
		
		/**
		 * Register an option callback for validation and execution.
		 *
		 * @param        object                callback
		 */
		registerCallback: function (callback) {
			if ($.isFunction(callback)) {
				this._callbacks.push(callback);
			}
		},
		
		/**
		 * Returns the triggering element.
		 *
		 * @param        jQuery                element
		 * @return        jQuery
		 */
		_getTriggerElement: function (element) {
			return null;
		},
		
		/**
		 * Shows a dropdown menu if options are available.
		 *
		 * @param        object                event
		 */
		_show: function (event) {
			event.preventDefault();
			var $elementID = $(event.currentTarget).data('elementID');
			
			// build dropdown
			var $trigger = null;
			if (!this._dropdowns[$elementID]) {
				this._triggerElements[$elementID] = $trigger = this._getTriggerElement(this._elements[$elementID]).addClass(
					'dropdownToggle');
				var parent = $trigger[0].parentNode;
				if (parent && parent.nodeName === 'LI' && parent.childElementCount === 1) {
					// do not add a wrapper element if the trigger is the only child
					parent.classList.add('dropdown');
				}
				else {
					$trigger.wrap('<span class="dropdown" />');
				}
				
				this._dropdowns[$elementID] = $('<ul class="dropdownMenu" />').insertAfter($trigger);
			}
			this._dropdowns[$elementID].empty();
			
			// validate options
			var $hasOptions = false;
			var $lastElementType = '';
			for (var $i = 0, $length = this._options.length; $i < $length; $i++) {
				var $option = this._options[$i];
				
				if ($option.optionName === 'divider') {
					if ($lastElementType !== '' && $lastElementType !== 'divider') {
						$('<li class="dropdownDivider" />').appendTo(this._dropdowns[$elementID]);
						$lastElementType = $option.optionName;
					}
				}
				else if (this._validate($elementID, $option.optionName) || this._validateCallbacks(
					$elementID,
					$option.optionName
				)) {
					var $listItem = $('<li><span>' + $option.label + '</span></li>').appendTo(this._dropdowns[$elementID]);
					$listItem.data('elementID', $elementID).data(
						'optionName',
						$option.optionName
					).data('isQuickOption', ($option.isQuickOption ? true : false)).click($.proxy(
						this._click,
						this
					));
					
					$hasOptions = true;
					$lastElementType = $option.optionName;
				}
			}
			
			if ($hasOptions) {
				// if last child is divider, remove it
				var $lastChild = this._dropdowns[$elementID].children().last();
				if ($lastChild.hasClass('dropdownDivider')) {
					$lastChild.remove();
				}
				
				// check if only element is a quick option
				var $quickOption = null;
				var $count = 0;
				this._dropdowns[$elementID].children().each(function (index, child) {
					var $child = $(child);
					if (!$child.hasClass('dropdownDivider')) {
						if ($child.data('isQuickOption')) {
							$quickOption = $child;
						}
						else {
							$count++;
						}
					}
				});
				
				if (!$count) {
					$quickOption.trigger('click');
					
					if (this._triggerElements[$elementID]) {
						WCF.Dropdown.close(this._triggerElements[$elementID].parents('.dropdown').wcfIdentify());
					}
					
					return false;
				}
			}
			
			if ($trigger !== null) {
				WCF.Dropdown.initDropdown($trigger, event.originalEvent || event);
			}
			
			return false;
		},
		
		/**
		 * Validates an option.
		 *
		 * @param        string                elementID
		 * @param        string                optionName
		 * @returns        boolean
		 */
		_validate: function (elementID, optionName) {
			return false;
		},
		
		/**
		 * Validates an option provided by callbacks.
		 *
		 * @param        string                elementID
		 * @param        string                optionName
		 * @return        boolean
		 */
		_validateCallbacks: function (elementID, optionName) {
			var $length = this._callbacks.length;
			if ($length) {
				for (var $i = 0; $i < $length; $i++) {
					if (this._callbacks[$i].validate(this._elements[elementID], optionName)) {
						return true;
					}
				}
			}
			
			return false;
		},
		
		/**
		 * Handles AJAX responses.
		 *
		 * @param        object                data
		 * @param        string                textStatus
		 * @param        jQuery                jqXHR
		 */
		_success: function (data, textStatus, jqXHR) {
			var $length = this._updateData.length;
			if (!$length) {
				return;
			}
			
			this._updateState(data);
			
			this._updateData = [];
		},
		
		/**
		 * Update element states based upon update data.
		 *
		 * @param        object                data
		 */
		_updateState: function (data) {
		},
		
		/**
		 * Handles clicks within dropdown.
		 *
		 * @param        object                event
		 */
		_click: function (event) {
			var $listItem = $(event.currentTarget);
			var $elementID = $listItem.data('elementID');
			var $optionName = $listItem.data('optionName');
			
			if (!this._execute($elementID, $optionName)) {
				this._executeCallback($elementID, $optionName);
			}
			
			this._hide($elementID);
		},
		
		/**
		 * Executes actions associated with an option.
		 *
		 * @param        string                elementID
		 * @param        string                optionName
		 * @return        boolean
		 */
		_execute: function (elementID, optionName) {
			return false;
		},
		
		/**
		 * Executes actions associated with an option provided by callbacks.
		 *
		 * @param        string                elementID
		 * @param        string                optionName
		 * @return        boolean
		 */
		_executeCallback: function (elementID, optionName) {
			var $length = this._callbacks.length;
			if ($length) {
				for (var $i = 0; $i < $length; $i++) {
					if (this._callbacks[$i].execute(this._elements[elementID], optionName)) {
						return true;
					}
				}
			}
			
			return false;
		},
		
		/**
		 * Hides a dropdown menu.
		 *
		 * @param        string                elementID
		 */
		_hide: function (elementID) {
			if (this._dropdowns[elementID]) {
				this._dropdowns[elementID].empty().removeClass('dropdownOpen');
			}
		}
	});
}
else {
	WCF.System.Worker = Class.extend({
		_aborted: false,
		_actionName: "",
		_callback: {},
		_className: "",
		_dialog: {},
		_proxy: {},
		_title: "",
		init: function() {},
		_success: function() {}
	});
	
	WCF.InlineEditor = Class.extend({
		_callbacks: {},
		_dropdowns: {},
		_elements: {},
		_notification: {},
		_options: {},
		_proxy: {},
		_triggerElements: {},
		_updateData: {},
		init: function() {},
		_closeAll: function() {},
		_setOptions: function() {},
		registerCallback: function() {},
		_getTriggerElement: function() {},
		_show: function() {},
		_validate: function() {},
		_validateCallbacks: function() {},
		_success: function() {},
		_updateState: function() {},
		_click: function() {},
		_execute: function() {},
		_executeCallback: function() {},
		_hide: function() {}
	});
}

/**
 * Default implementation for ajax file uploads.
 *
 * @deprecated        Use WoltLabSuite/Core/Upload
 *
 * @param        jquery                buttonSelector
 * @param        jquery                fileListSelector
 * @param        string                className
 * @param        jquery                options
 */
WCF.Upload = Class.extend({
	/**
	 * name of the upload field
	 * @var        string
	 */
	_name: '__files[]',
	
	/**
	 * button selector
	 * @var        jQuery
	 */
	_buttonSelector: null,
	
	/**
	 * file list selector
	 * @var        jQuery
	 */
	_fileListSelector: null,
	
	/**
	 * upload file
	 * @var        jQuery
	 */
	_fileUpload: null,
	
	/**
	 * class name
	 * @var        string
	 */
	_className: '',
	
	/**
	 * iframe for IE<10 fallback
	 * @var        jQuery
	 */
	_iframe: null,
	
	/**
	 * internal file id
	 * @var        integer
	 */
	_internalFileID: 0,
	
	/**
	 * additional options
	 * @var        jQuery
	 */
	_options: {},
	
	/**
	 * upload matrix
	 * @var        array
	 */
	_uploadMatrix: [],
	
	/**
	 * true, if the active user's browser supports ajax file uploads
	 * @var        boolean
	 */
	_supportsAJAXUpload: true,
	
	/**
	 * fallback overlay for stupid browsers
	 * @var        jquery
	 */
	_overlay: null,
	
	/**
	 * Initializes a new upload handler.
	 *
	 * @param        string                buttonSelector
	 * @param        string                fileListSelector
	 * @param        string                className
	 * @param        object                options
	 */
	init: function (buttonSelector, fileListSelector, className, options) {
		this._buttonSelector = buttonSelector;
		this._fileListSelector = fileListSelector;
		this._className = className;
		this._internalFileID = 0;
		this._options = $.extend(true, {
			action: 'upload',
			multiple: false,
			url: 'index.php?ajax-upload/&t=' + SECURITY_TOKEN
		}, options || {});
		
		this._options.url = WCF.convertLegacyURL(this._options.url);
		if (this._options.url.indexOf('index.php') === 0) {
			this._options.url = WSC_API_URL + this._options.url;
		}
		
		// check for ajax upload support
		var $xhr = new XMLHttpRequest();
		this._supportsAJAXUpload = ($xhr && ('upload' in $xhr) && ('onprogress' in $xhr.upload));
		
		// create upload button
		this._createButton();
	},
	
	/**
	 * Creates the upload button.
	 */
	_createButton: function () {
		if (this._supportsAJAXUpload) {
			this._fileUpload = $('<input type="file" name="' + this._name + '" ' + (this._options.multiple ? 'multiple="true" ' : '') + '/>');
			this._fileUpload.change($.proxy(this._upload, this));
			var $button = $('<p class="button uploadButton"><span>' + WCF.Language.get('wcf.global.button.upload') + '</span></p>');
			elAttr($button[0], 'role', 'button');
			$button.prepend(this._fileUpload);
			
			this._fileUpload[0].addEventListener('focus', function() {
				if (this.classList.contains('focus-visible')) {
					$button[0].classList.add('active');
				}
			});
			this._fileUpload[0].addEventListener('blur', function() { $button[0].classList.remove('active'); });
		}
		else {
			var $button = $('<p class="button uploadFallbackButton"><span>' + WCF.Language.get('wcf.global.button.upload') + '</span></p>');
			elAttr($button[0], 'role', 'button');
			elAttr($button[0], 'tabindex', '0');
			$button.click($.proxy(this._showOverlay, this));
		}
		
		this._insertButton($button);
	},
	
	/**
	 * Inserts the upload button.
	 *
	 * @param        jQuery                button
	 */
	_insertButton: function (button) {
		this._buttonSelector.prepend(button);
	},
	
	/**
	 * Removes the upload button.
	 */
	_removeButton: function () {
		var $selector = '.uploadButton';
		if (!this._supportsAJAXUpload) {
			$selector = '.uploadFallbackButton';
		}
		
		this._buttonSelector.find($selector).remove();
	},
	
	/**
	 * Callback for file uploads.
	 *
	 * @param        object              event
	 * @param        File                file
	 * @param        Blob                blob
	 * @param        Array|FileList      list of files
	 * @return       integer
	 */
	_upload: function (event, file, blob, files) {
		var $uploadID = null;
		var $files = [];
		
		if (typeof files !== 'undefined') {
			$files = files;
		}
		else {
			if (file) {
				$files.push(file);
			}
			else if (blob) {
				var $ext = '';
				switch (blob.type) {
					case 'image/png':
						$ext = '.png';
						break;
					
					case 'image/jpeg':
						$ext = '.jpg';
						break;
					
					case 'image/gif':
						$ext = '.gif';
						break;
				}
				
				$files.push({
					name: 'pasted-from-clipboard' + $ext
				});
			}
			else {
				$files = this._fileUpload.prop('files');
			}
		}
		
		if ($files.length) {
			var $fd = new FormData();
			$uploadID = this._createUploadMatrix($files);
			
			// no more files left, abort
			if (!this._uploadMatrix[$uploadID].length) {
				return null;
			}
			
			for (var $i = 0, $length = $files.length; $i < $length; $i++) {
				if (this._uploadMatrix[$uploadID][$i]) {
					var $internalFileID = this._uploadMatrix[$uploadID][$i].data('internalFileID');
					
					if (blob) {
						$fd.append('__files[' + $internalFileID + ']', blob, $files[$i].name);
					}
					else {
						$fd.append('__files[' + $internalFileID + ']', $files[$i], $files[$i].name);
					}
				}
			}
			
			$fd.append('actionName', this._options.action);
			$fd.append('className', this._className);
			var $additionalParameters = this._getParameters();
			for (var $name in $additionalParameters) {
				$fd.append('parameters[' + $name + ']', $additionalParameters[$name]);
			}
			
			var self = this;
			$.ajax({
				type: 'POST',
				url: this._options.url,
				enctype: 'multipart/form-data',
				data: $fd,
				contentType: false,
				processData: false,
				success: function (data, textStatus, jqXHR) {
					self._success($uploadID, data);
				},
				error: $.proxy(this._error, this),
				xhr: function () {
					var $xhr = $.ajaxSettings.xhr();
					if ($xhr) {
						$xhr.upload.addEventListener('progress', function (event) {
							self._progress($uploadID, event);
						}, false);
					}
					return $xhr;
				},
				xhrFields: {
					withCredentials: true
				}
			});
		}
		
		return $uploadID;
	},
	
	/**
	 * Creates upload matrix for provided files.
	 *
	 * @param        array<object>                files
	 * @return        integer
	 */
	_createUploadMatrix: function (files) {
		if (files.length) {
			var $uploadID = this._uploadMatrix.length;
			this._uploadMatrix[$uploadID] = [];
			
			for (var $i = 0, $length = files.length; $i < $length; $i++) {
				var $file = files[$i];
				var $li = this._initFile($file);
				
				if (!$li.hasClass('uploadFailed')) {
					$li.data('filename', $file.name).data('internalFileID', this._internalFileID++);
					this._uploadMatrix[$uploadID][$i] = $li;
				}
			}
			
			return $uploadID;
		}
		
		return null;
	},
	
	/**
	 * Callback for success event.
	 *
	 * @param        integer                uploadID
	 * @param        object                data
	 */
	_success: function (uploadID, data) {
	},
	
	/**
	 * Callback for error event.
	 *
	 * @param        jQuery                jqXHR
	 * @param        string                textStatus
	 * @param        string                errorThrown
	 */
	_error: function (jqXHR, textStatus, errorThrown) {
	},
	
	/**
	 * Callback for progress event.
	 *
	 * @param        integer                uploadID
	 * @param        object                event
	 */
	_progress: function (uploadID, event) {
		var $percentComplete = Math.round(event.loaded * 100 / event.total);
		
		for (var $i in this._uploadMatrix[uploadID]) {
			this._uploadMatrix[uploadID][$i].find('progress').attr('value', $percentComplete);
		}
	},
	
	/**
	 * Returns additional parameters.
	 *
	 * @return        object
	 */
	_getParameters: function () {
		return {};
	},
	
	/**
	 * Initializes list item for uploaded file.
	 *
	 * @return        jQuery
	 */
	_initFile: function (file) {
		return $('<li>' + file.name + ' (' + file.size + ')<progress max="100" /></li>').appendTo(this._fileListSelector);
	},
	
	/**
	 * Shows the fallback overlay (work in progress)
	 */
	_showOverlay: function () {
		// create iframe
		if (this._iframe === null) {
			this._iframe = $('<iframe name="__fileUploadIFrame" />').hide().appendTo(document.body);
		}
		
		// create overlay
		if (!this._overlay) {
			this._overlay = $('<div><form enctype="multipart/form-data" method="post" action="' + this._options.url + '" target="__fileUploadIFrame" /></div>').hide().appendTo(document.body);
			
			var $form = this._overlay.find('form');
			$('<dl class="wide"><dd><input type="file" id="__fileUpload" name="' + this._name + '" ' + (this._options.multiple ? 'multiple="true" ' : '') + '/></dd></dl>').appendTo($form);
			$('<div class="formSubmit"><input type="submit" value="Upload" accesskey="s" /></div></form>').appendTo($form);
			
			$('<input type="hidden" name="isFallback" value="1" />').appendTo($form);
			$('<input type="hidden" name="actionName" value="' + this._options.action + '" />').appendTo($form);
			$('<input type="hidden" name="className" value="' + this._className + '" />').appendTo($form);
			var $additionalParameters = this._getParameters();
			for (var $name in $additionalParameters) {
				$('<input type="hidden" name="' + $name + '" value="' + $additionalParameters[$name] + '" />').appendTo($form);
			}
			
			$form.submit($.proxy(function () {
				var $file = {
					name: this._getFilename(),
					size: ''
				};
				
				var $uploadID = this._createUploadMatrix([$file]);
				var self = this;
				this._iframe.data('loading', true).off('load').load(function () {
					self._evaluateResponse($uploadID);
				});
				this._overlay.wcfDialog('close');
			}, this));
		}
		
		this._overlay.wcfDialog({
			title: WCF.Language.get('wcf.global.button.upload')
		});
	},
	
	/**
	 * Evaluates iframe response.
	 *
	 * @param        integer                uploadID
	 */
	_evaluateResponse: function (uploadID) {
		var $returnValues = $.parseJSON(this._iframe.contents().find('pre').html());
		this._success(uploadID, $returnValues);
	},
	
	/**
	 * Returns name of selected file.
	 *
	 * @return        string
	 */
	_getFilename: function () {
		return $('#__fileUpload').val().split('\\').pop();
	}
});

/**
 * Default implementation for parallel AJAX file uploads.
 *
 * @deprecated        Use WoltLabSuite/Core/Upload
 */
WCF.Upload.Parallel = WCF.Upload.extend({
	/**
	 * @see        WCF.Upload.init()
	 */
	init: function (buttonSelector, fileListSelector, className, options) {
		// force multiple uploads
		options = $.extend(true, options || {}, {
			multiple: true
		});
		
		this._super(buttonSelector, fileListSelector, className, options);
	},
	
	/**
	 * @see        WCF.Upload._upload()
	 */
	_upload: function () {
		var $files = this._fileUpload.prop('files');
		for (var $i = 0, $length = $files.length; $i < $length; $i++) {
			var $file = $files[$i];
			var $formData = new FormData();
			var $internalFileID = this._createUploadMatrix($file);
			
			if (!this._uploadMatrix[$internalFileID].length) {
				continue;
			}
			
			$formData.append('__files[' + $internalFileID + ']', $file);
			$formData.append('actionName', this._options.action);
			$formData.append('className', this._className);
			var $additionalParameters = this._getParameters();
			for (var $name in $additionalParameters) {
				$formData.append('parameters[' + $name + ']', $additionalParameters[$name]);
			}
			
			this._sendRequest($internalFileID, $formData);
		}
	},
	
	/**
	 * Sends an AJAX request to upload a file.
	 *
	 * @param        integer                internalFileID
	 * @param        FormData        formData
	 * @return       jqXHR
	 */
	_sendRequest: function (internalFileID, formData) {
		var self = this;
		
		return $.ajax({
			type: 'POST',
			url: this._options.url,
			enctype: 'multipart/form-data',
			data: formData,
			contentType: false,
			processData: false,
			success: function (data, textStatus, jqXHR) {
				self._success(internalFileID, data);
			},
			error: $.proxy(this._error, this),
			xhr: function () {
				var $xhr = $.ajaxSettings.xhr();
				if ($xhr) {
					$xhr.upload.addEventListener('progress', function (event) {
						self._progress(internalFileID, event);
					}, false);
				}
				return $xhr;
			}
		});
	},
	
	/**
	 * Creates upload matrix for provided file and returns its internal file id.
	 *
	 * @param        object                file
	 * @return        integer
	 */
	_createUploadMatrix: function (file) {
		var $li = this._initFile(file);
		if (!$li.hasClass('uploadFailed')) {
			$li.data('filename', file.name).data('internalFileID', this._internalFileID);
			this._uploadMatrix[this._internalFileID++] = $li;
			
			return this._internalFileID - 1;
		}
		
		return null;
	},
	
	/**
	 * Callback for success event.
	 *
	 * @param        integer                internalFileID
	 * @param        object                data
	 */
	_success: function (internalFileID, data) {
	},
	
	/**
	 * Callback for progress event.
	 *
	 * @param        integer                internalFileID
	 * @param        object                event
	 */
	_progress: function (internalFileID, event) {
		var $percentComplete = Math.round(event.loaded * 100 / event.total);
		
		this._uploadMatrix[internalFileID].find('progress').attr('value', $percentComplete);
	},
	
	/**
	 * @see        WCF.Upload._showOverlay()
	 */
	_showOverlay: function () {
		// create iframe
		if (this._iframe === null) {
			this._iframe = $('<iframe name="__fileUploadIFrame" />').hide().appendTo(document.body);
		}
		
		// create overlay
		if (!this._overlay) {
			this._overlay = $('<div><form enctype="multipart/form-data" method="post" action="' + this._options.url + '" target="__fileUploadIFrame" /></div>').hide().appendTo(document.body);
			
			var $form = this._overlay.find('form');
			$('<dl class="wide"><dd><input type="file" id="__fileUpload" name="' + this._name + '" ' + (this._options.multiple ? 'multiple="true" ' : '') + '/></dd></dl>').appendTo($form);
			$('<div class="formSubmit"><input type="submit" value="Upload" accesskey="s" /></div></form>').appendTo($form);
			
			$('<input type="hidden" name="isFallback" value="1" />').appendTo($form);
			$('<input type="hidden" name="actionName" value="' + this._options.action + '" />').appendTo($form);
			$('<input type="hidden" name="className" value="' + this._className + '" />').appendTo($form);
			var $additionalParameters = this._getParameters();
			for (var $name in $additionalParameters) {
				$('<input type="hidden" name="' + $name + '" value="' + $additionalParameters[$name] + '" />').appendTo($form);
			}
			
			$form.submit($.proxy(function () {
				var $file = {
					name: this._getFilename(),
					size: ''
				};
				
				var $internalFileID = this._createUploadMatrix($file);
				var self = this;
				this._iframe.data('loading', true).off('load').load(function () {
					self._evaluateResponse($internalFileID);
				});
				this._overlay.wcfDialog('close');
			}, this));
		}
		
		this._overlay.wcfDialog({
			title: WCF.Language.get('wcf.global.button.upload')
		});
	},
	
	/**
	 * Evaluates iframe response.
	 *
	 * @param        integer                internalFileID
	 */
	_evaluateResponse: function (internalFileID) {
		var $returnValues = $.parseJSON(this._iframe.contents().find('pre').html());
		this._success(internalFileID, $returnValues);
	}
});

/**
 * Namespace for sortables.
 */
WCF.Sortable = { };

if (COMPILER_TARGET_DEFAULT) {
	/**
	 * Sortable implementation for lists.
	 *
	 * @param        string                containerID
	 * @param        string                className
	 * @param        integer                offset
	 * @param        object                options
	 */
	WCF.Sortable.List = Class.extend({
		/**
		 * additional parameters for AJAX request
		 * @var        object
		 */
		_additionalParameters: {},
		
		/**
		 * action class name
		 * @var        string
		 */
		_className: '',
		
		/**
		 * container id
		 * @var        string
		 */
		_containerID: '',
		
		/**
		 * container object
		 * @var        jQuery
		 */
		_container: null,
		
		/**
		 * notification object
		 * @var        WCF.System.Notification
		 */
		_notification: null,
		
		/**
		 * show order offset
		 * @var        integer
		 */
		_offset: 0,
		
		/**
		 * list of options
		 * @var        object
		 */
		_options: {},
		
		/**
		 * proxy object
		 * @var        WCF.Action.Proxy
		 */
		_proxy: null,
		
		/**
		 * object structure
		 * @var        object
		 */
		_structure: {},
		
		/**
		 * Creates a new sortable list.
		 *
		 * @param        string                containerID
		 * @param        string                className
		 * @param        integer                offset
		 * @param        object                options
		 * @param        boolean                isSimpleSorting
		 * @param        object                additionalParameters
		 */
		init: function (containerID, className, offset, options, isSimpleSorting, additionalParameters) {
			this._additionalParameters = additionalParameters || {};
			this._containerID = $.wcfEscapeID(containerID);
			this._container = $('#' + this._containerID);
			this._className = className;
			this._offset = (offset) ? offset : 0;
			this._proxy = new WCF.Action.Proxy({
				success: $.proxy(this._success, this)
			});
			this._structure = {};
			
			// init sortable
			this._options = $.extend(true, {
				axis: 'y',
				connectWith: '#' + this._containerID + ' .sortableList',
				disableNesting: 'sortableNoNesting',
				doNotClear: true,
				errorClass: 'sortableInvalidTarget',
				forcePlaceholderSize: true,
				handle: '',
				helper: 'clone',
				items: 'li:not(.sortableNoSorting)',
				opacity: .6,
				placeholder: 'sortablePlaceholder',
				tolerance: 'pointer',
				toleranceElement: '> span'
			}, options || {});
			
			var sortableList = $('#' + this._containerID + ' .sortableList');
			if (sortableList.is('tbody')) {
				if (this._options.items === 'li:not(.sortableNoSorting)') {
					this._options.items = 'tr:not(.sortableNoSorting)';
					this._options.toleranceElement = '';
				}
				
				if (this._options.helper === 'clone') {
					this._options.helper = this._tableRowHelper.bind(this);
					
					// explicitly set column widths to avoid column resizing during dragging
					var thead = sortableList.prev('thead');
					if (thead) {
						thead.find('th').each(function (index, element) {
							element = $(element);
							
							element.width(element.width());
						});
					}
				}
			}
			
			if (isSimpleSorting) {
				sortableList.sortable(this._options);
			}
			else {
				sortableList.nestedSortable(this._options);
			}
			
			if (this._className) {
				var $formSubmit = this._container.find('.formSubmit');
				if (!$formSubmit.length) {
					$formSubmit = this._container.next('.formSubmit');
					if (!$formSubmit.length) {
						console.debug("[WCF.Sortable.Simple] Unable to find form submit for saving, aborting.");
						return;
					}
				}
				
				$formSubmit.children('button[data-type="submit"]').click($.proxy(this._submit, this));
			}
		},
		
		/**
		 * Fixes the width of the cells of the dragged table row.
		 *
		 * @param        {Event}                event
		 * @param        {jQuery}        ui
		 * @return        {jQuery}
		 */
		_tableRowHelper: function (event, ui) {
			ui.children('td').each(function (index, element) {
				element = $(element);
				
				element.width(element.width());
			});
			
			return ui;
		},
		
		/**
		 * Saves object structure.
		 */
		_submit: function () {
			// reset structure
			this._structure = {};
			
			// build structure
			this._container.find('.sortableList').each($.proxy(function (index, list) {
				var $list = $(list);
				var $parentID = $list.data('objectID');
				
				if ($parentID !== undefined) {
					$list.children(this._options.items).each($.proxy(function (index, listItem) {
						var $objectID = $(listItem).data('objectID');
						
						if (!this._structure[$parentID]) {
							this._structure[$parentID] = [];
						}
						
						this._structure[$parentID].push($objectID);
					}, this));
				}
			}, this));
			
			// send request
			var $parameters = $.extend(true, {
				data: {
					offset: this._offset,
					structure: this._structure
				}
			}, this._additionalParameters);
			
			this._proxy.setOption('data', {
				actionName: 'updatePosition',
				className: this._className,
				interfaceName: 'wcf\\data\\ISortableAction',
				parameters: $parameters
			});
			this._proxy.sendRequest();
		},
		
		/**
		 * Shows notification upon success.
		 *
		 * @param        object                data
		 * @param        string                textStatus
		 * @param        jQuery                jqXHR
		 */
		_success: function (data, textStatus, jqXHR) {
			if (this._notification === null) {
				this._notification = new WCF.System.Notification(WCF.Language.get('wcf.global.success.edit'));
			}
			
			this._notification.show();
		}
	});
}
else {
	WCF.Sortable.List = Class.extend({
		_additionalParameters: {},
		_className: "",
		_containerID: "",
		_container: {},
		_notification: {},
		_offset: 0,
		_options: {},
		_proxy: {},
		_structure: {},
		init: function() {},
		_tableRowHelper: function() {},
		_submit: function() {},
		_success: function() {}
	});
}

WCF.Popover = Class.extend({
	/**
	 * currently active element id
	 * @var	string
	 */
	_activeElementID: '',
	
	_identifier: '',
	_popoverObj: null,
	
	/**
	 * Initializes a new WCF.Popover object.
	 * 
	 * @param	string		selector
	 */
	init: function(selector) {
		var mobile = false;
		require(['Environment'], function(Environment) {
			if (Environment.platform() !== 'desktop') {
				mobile = true;
			}
		}.bind(this));
		if (mobile) return;
		
		// assign default values
		this._activeElementID = '';
		this._identifier = selector;
		
		require(['WoltLabSuite/Core/Controller/Popover'], (function(popover) {
			popover.init({
				attributeName: 'legacy',
				className: selector,
				identifier: this._identifier,
				legacy: true,
				loadCallback: this._legacyLoad.bind(this)
			});
		}).bind(this));
	},
	
	_initContainers: function() {},
	
	_legacyLoad: function(objectId, popover) {
		this._activeElementID = objectId;
		this._popoverObj = popover;
		
		this._loadContent();
	},
	
	_insertContent: function(elementId, template) {
		this._popoverObj.setContent(this._identifier, elementId, template);
	}
});

/**
 * Provides an extensible item list with built-in search.
 * 
 * @param	string		itemListSelector
 * @param	string		searchInputSelector
 */
WCF.EditableItemList = Class.extend({
	/**
	 * allows custom input not recognized by search to be added
	 * @var	boolean
	 */
	_allowCustomInput: false,
	
	/**
	 * action class name
	 * @var	string
	 */
	_className: '',
	
	/**
	 * internal data storage
	 * @var	mixed
	 */
	_data: { },
	
	/**
	 * form container
	 * @var	jQuery
	 */
	_form: null,
	
	/**
	 * item list container
	 * @var	jQuery
	 */
	_itemList: null,
	
	/**
	 * current object id
	 * @var	integer
	 */
	_objectID: 0,
	
	/**
	 * object type id
	 * @var	integer
	 */
	_objectTypeID: 0,
	
	/**
	 * search controller
	 * @var	WCF.Search.Base
	 */
	_search: null,
	
	/**
	 * search input element
	 * @var	jQuery
	 */
	_searchInput: null,
	
	/**
	 * Creates a new WCF.EditableItemList object.
	 * 
	 * @param	string		itemListSelector
	 * @param	string		searchInputSelector
	 */
	init: function(itemListSelector, searchInputSelector) {
		this._itemList = $(itemListSelector);
		this._searchInput = $(searchInputSelector);
		this._data = { };
		
		if (!this._itemList.length || !this._searchInput.length) {
			console.debug("[WCF.EditableItemList] Item list and/or search input do not exist, aborting.");
			return;
		}
		
		this._objectID = this._getObjectID();
		this._objectTypeID = this._getObjectTypeID();
		
		// bind item listener
		this._itemList.find('.jsEditableItem').click($.proxy(this._click, this));
		
		// create item list
		if (!this._itemList.children('ul').length) {
			$('<ul />').appendTo(this._itemList);
		}
		this._itemList = this._itemList.children('ul');
		
		// bind form submit
		this._form = this._itemList.parents('form').submit($.proxy(this._submit, this));
		
		if (this._allowCustomInput) {
			var self = this;
			this._searchInput.keydown($.proxy(this._keyDown, this)).keypress($.proxy(this._keyPress, this)).on('paste', function() {
				setTimeout(function() { self._onPaste(); }, 100);
			});
		}
		
		// block form submit through [ENTER]
		this._searchInput.parents('.dropdown').data('preventSubmit', true);
	},
	
	/**
	 * Handles the key down event.
	 * 
	 * @param	object		event
	 */
	_keyDown: function(event) {
		if (event === null) {
			return this._keyPress(null);
		}
		
		return true;
	},
	
	/**
	 * Handles the key press event.
	 * 
	 * @param	object		event
	 */
	_keyPress: function(event) {
		// 44 = [,] (charCode != keyCode)
		if (event === null || event.charCode === 44 || event.charCode === $.ui.keyCode.ENTER || ($.browser.mozilla && event.keyCode === $.ui.keyCode.ENTER)) {
			if (event !== null && event.charCode === $.ui.keyCode.ENTER && this._search) {
				if (this._search._itemIndex !== -1) {
					return false;
				}
			}
			
			var $value = $.trim(this._searchInput.val());
			
			// read everything left from caret position
			if (event && event.charCode === 44) {
				$value = $value.substring(0, this._searchInput.getCaret());
			}
			
			if ($value === '') {
				return true;
			}
			
			this.addItem({
				objectID: 0,
				label: $value
			});
			
			// reset input
			if (event && event.charCode === 44) {
				this._searchInput.val($.trim(this._searchInput.val().substr(this._searchInput.getCaret())));
			}
			else {
				this._searchInput.val('');
			}
			
			if (event !== null) {
				event.stopPropagation();
			}
			
			return false;
		}
		
		return true;
	},
	
	/**
	 * Handle paste event.
	 */
	_onPaste: function() {
		// split content by comma
		var $value = $.trim(this._searchInput.val());
		$value = $value.split(',');
		
		for (var $i = 0, $length = $value.length; $i < $length; $i++) {
			var $label = $.trim($value[$i]);
			if ($label === '') {
				continue;
			}
			
			this.addItem({
				objectID: 0,
				label: $label
			});
		}
		
		this._searchInput.val('');
	},
	
	/**
	 * Loads raw data and converts it into internal structure. Override this methods
	 * in your derived classes.
	 * 
	 * @param	object		data
	 */
	load: function(data) { },
	
	/**
	 * Removes an item on click.
	 * 
	 * @param	object		event
	 * @return	boolean
	 */
	_click: function(event) {
		var $element = $(event.currentTarget);
		var $objectID = $element.data('objectID');
		var $label = $element.data('label');
		
		if (this._search) {
			this._search.removeExcludedSearchValue($label);
		}
		this._removeItem($objectID, $label);
		
		$element.remove();
		
		event.stopPropagation();
		return false;
	},
	
	/**
	 * Returns current object id.
	 * 
	 * @return	integer
	 */
	_getObjectID: function() {
		return 0;
	},
	
	/**
	 * Returns current object type id.
	 * 
	 * @return	integer
	 */
	_getObjectTypeID: function() {
		return 0;
	},
	
	/**
	 * Adds a new item to the list.
	 * 
	 * @param	object		data
	 * @return	boolean
	 */
	addItem: function(data) {
		if (this._data[data.objectID]) {
			if (!(data.objectID === 0 && this._allowCustomInput)) {
				return true;
			}
		}
		
		var $listItem = $('<li class="badge">' + WCF.String.escapeHTML(data.label) + '</li>').data('objectID', data.objectID).data('label', data.label).appendTo(this._itemList);
		$listItem.click($.proxy(this._click, this));
		
		if (this._search) {
			this._search.addExcludedSearchValue(data.label);
		}
		this._addItem(data.objectID, data.label);
		
		return true;
	},
	
	/**
	 * Clears the list of items.
	 */
	clearList: function() {
		this._itemList.children('li').each($.proxy(function(index, element) {
			var $element = $(element);
			
			if (this._search) {
				this._search.removeExcludedSearchValue($element.data('label'));
			}
			
			$element.remove();
			this._removeItem($element.data('objectID'), $element.data('label'));
		}, this));
	},
	
	/**
	 * Handles form submit, override in your class.
	 */
	_submit: function() {
		this._keyDown(null);
	},
	
	/**
	 * Adds an item to internal storage.
	 * 
	 * @param	integer		objectID
	 * @param	string		label
	 */
	_addItem: function(objectID, label) {
		this._data[objectID] = label;
	},
	
	/**
	 * Removes an item from internal storage.
	 * 
	 * @param	integer		objectID
	 * @param	string		label
	 */
	_removeItem: function(objectID, label) {
		delete this._data[objectID];
	},
	
	/**
	 * Returns the search input field.
	 * 
	 * @return	jQuery
	 */
	getSearchInput: function() {
		return this._searchInput;
	}
});

/**
 * Provides a language chooser.
 *
 * @param       {string}                                containerId             input element conainer id
 * @param       {string}                                chooserId               input element id
 * @param       {int}                                   languageId              selected language id
 * @param       {object<int, object<string, string>>}   languages               data of available languages
 * @param       {function}                              callback                function called after a language is selected
 * @param       {boolean}                               allowEmptyValue         true if no language may be selected
 * 
 * @deprecated  3.0 - please use `WoltLabSuite/Core/Language/Chooser` instead
 */
WCF.Language.Chooser = Class.extend({
	/**
	 * Initializes the language chooser.
	 *
	 * @param       {string}                                containerId             input element container id
	 * @param       {string}                                chooserId               input element id
	 * @param       {int}                                   languageId              selected language id
	 * @param       {object<int, object<string, string>>}   languages               data of available languages
	 * @param       {function}                              callback                function called after a language is selected
	 * @param       {boolean}                               allowEmptyValue         true if no language may be selected
	 */
	init: function(containerId, chooserId, languageId, languages, callback, allowEmptyValue) {
		require(['WoltLabSuite/Core/Language/Chooser'], function(LanguageChooser) {
			LanguageChooser.init(containerId, chooserId, languageId, languages, callback, allowEmptyValue);
		});
	}
});

/**
 * Namespace for style related classes.
 */
WCF.Style = { };

/**
 * Converts static user panel items into interactive dropdowns.
 * 
 * @deprecated	2.1 - Please use WCF.User.Panel.Interactive instead
 * 
 * @param	string		containerID
 */
WCF.UserPanel = Class.extend({
	/**
	 * target container
	 * @var	jQuery
	 */
	_container: null,
	
	/**
	 * initialization state
	 * @var	boolean
	 */
	_didLoad: false,
	
	/**
	 * original link element
	 * @var	jQuery
	 */
	_link: null,
	
	/**
	 * language variable name for 'no items'
	 * @var	string
	 */
	_noItems: '',
	
	/**
	 * reverts to original link if return values are empty
	 * @var	boolean
	 */
	_revertOnEmpty: true,
	
	/**
	 * Initializes the WCF.UserPanel class.
	 * 
	 * @param	string		containerID
	 */
	init: function(containerID) {
		this._container = $('#' + containerID);
		this._didLoad = false;
		this._revertOnEmpty = true;
		
		if (this._container.length != 1) {
			console.debug("[WCF.UserPanel] Unable to find container identified by '" + containerID + "', aborting.");
			return;
		}
		
		this._convert();
	},
	
	/**
	 * Converts link into an interactive dropdown menu.
	 */
	_convert: function() {
		this._container.addClass('dropdown');
		this._link = this._container.children('a').remove();
		
		var $button = $('<a href="' + this._link.attr('href') + '" class="dropdownToggle">' + this._link.html() + '</a>').appendTo(this._container).click($.proxy(this._click, this));
		var $dropdownMenu = $('<ul class="dropdownMenu" />').appendTo(this._container);
		$('<li class="jsDropdownPlaceholder"><span>' + WCF.Language.get('wcf.global.loading') + '</span></li>').appendTo($dropdownMenu);
		
		this._addDefaultItems($dropdownMenu);
		
		this._container.dblclick($.proxy(function() {
			window.location = this._link.attr('href');
			return false;
		}, this));
		
		WCF.Dropdown.initDropdown($button, false);
	},
	
	/**
	 * Adds default items to dropdown menu.
	 * 
	 * @param	jQuery		dropdownMenu
	 */
	_addDefaultItems: function(dropdownMenu) { },
	
	/**
	 * Adds a dropdown divider.
	 * 
	 * @param	jQuery		dropdownMenu
	 */
	_addDivider: function(dropdownMenu) {
		$('<li class="dropdownDivider" />').appendTo(dropdownMenu);
	},
	
	/**
	 * Handles clicks on the dropdown item.
	 * 
	 * @param	object		event
	 */
	_click: function(event) {
		event.preventDefault();
		
		if (this._didLoad) {
			return;
		}
		
		new WCF.Action.Proxy({
			autoSend: true,
			data: this._getParameters(),
			success: $.proxy(this._success, this)
		});
		
		this._didLoad = true;
	},
	
	/**
	 * Returns a list of parameters for AJAX request.
	 * 
	 * @return	object
	 */
	_getParameters: function() {
		return { };
	},
	
	/**
	 * Handles successful AJAX requests.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		var $dropdownMenu = WCF.Dropdown.getDropdownMenu(this._container.wcfIdentify());
		$dropdownMenu.children('.jsDropdownPlaceholder').remove();
		
		if (data.returnValues && data.returnValues.template) {
			$('' + data.returnValues.template).prependTo($dropdownMenu);
			
			// update badge
			this._updateBadge(data.returnValues.totalCount);
			
			this._after($dropdownMenu);
		}
		else {
			$('<li><span>' + WCF.Language.get(this._noItems) + '</span></li>').prependTo($dropdownMenu);
			
			// remove badge
			this._updateBadge(0);
		}
	},
	
	/**
	 * Updates badge count.
	 * 
	 * @param	integer		count
	 */
	_updateBadge: function(count) {
		count = parseInt(count) || 0;
		
		if (count) {
			var $badge = this._container.find('.badge');
			if (!$badge.length) {
				$badge = $('<span class="badge badgeUpdate" />').appendTo(this._container.children('.dropdownToggle'));
				$badge.before(' ');
			}
			$badge.html(count);
		}
		else {
			this._container.find('.badge').remove();
		}
	},
	
	/**
	 * Execute actions after the dropdown menu has been populated.
	 * 
	 * @param	object		dropdownMenu
	 */
	_after: function(dropdownMenu) { }
});

jQuery.fn.extend({
	// shim for 'ui.wcfDialog'
	wcfDialog: function(method) {
		var args = arguments;
		
		require(['Dom/Util', 'Ui/Dialog'], (function(DomUtil, UiDialog) {
			var id = DomUtil.identify(this[0]);
			
			if (method === 'close') {
				UiDialog.close(id);
			}
			else if (method === 'render') {
				UiDialog.rebuild(id);
			}
			else if (method === 'option') {
				if (args.length === 3) {
					if (args[1] === 'title' && typeof args[2] === 'string') {
						UiDialog.setTitle(id, args[2]);
					}
					else if (args[1].indexOf('on') === 0) {
						UiDialog.setCallback(id, args[1], args[2]);
					}
					else if (args[1] === 'closeConfirmMessage' && args[2] === null) {
						UiDialog.setCallback(id, 'onBeforeClose', null);
					}
				}
			}
			else {
				if (this[0].parentNode.nodeType === Node.DOCUMENT_FRAGMENT_NODE) {
					// if element is not already part of the DOM, UiDialog.open() will fail
					document.body.appendChild(this[0]);
				}
				
				var options = (args.length === 1 && typeof args[0] === 'object') ? args[0] : {};
				UiDialog.openStatic(id, null, options);
				
				if (options.hasOwnProperty('title')) {
					UiDialog.setTitle(id, options.title);
				}
			}
		}).bind(this));
		
		return this;
	}
});

/**
 * Provides a slideshow for lists.
 */
$.widget('ui.wcfSlideshow', {
	/**
	 * button list object
	 * @var	jQuery
	 */
	_buttonList: null,
	
	/**
	 * number of items
	 * @var	integer
	 */
	_count: 0,
	
	/**
	 * item index
	 * @var	integer
	 */
	_index: 0,
	
	/**
	 * item list object
	 * @var	jQuery
	 */
	_itemList: null,
	
	/**
	 * list of items
	 * @var	jQuery
	 */
	_items: null,
	
	/**
	 * timer object
	 * @var	WCF.PeriodicalExecuter
	 */
	_timer: null,
	
	/**
	 * list item width
	 * @var	integer
	 */
	_width: 0,
	
	/**
	 * list of options
	 * @var	object
	 */
	options: {
		/* enables automatic cycling of items */
		cycle: true,
		/* cycle interval in seconds */
		cycleInterval: 5,
		/* gap between items in pixels */
		itemGap: 50
	},
	
	/**
	 * Creates a new instance of ui.wcfSlideshow.
	 */
	_create: function() {
		this._itemList = this.element.children('ul');
		this._items = this._itemList.children('li');
		this._count = this._items.length;
		this._index = 0;
		
		if (this._count > 1) {
			this._initSlideshow();
		}
	},
	
	/**
	 * Initializes the slideshow.
	 */
	_initSlideshow: function() {
		// calculate item dimensions
		var $itemHeight = $(this._items.get(0)).outerHeight();
		this._items.addClass('slideshowItem');
		this._width = this.element.css('height', $itemHeight).innerWidth();
		this._itemList.addClass('slideshowItemList').css('left', 0);
		
		this._items.each($.proxy(function(index, item) {
			$(item).show().css({
				height: $itemHeight,
				left: ((this._width + this.options.itemGap) * index),
				width: this._width
			});
		}, this));
		
		this.element.css({
			height: $itemHeight,
			width: this._width
		}).hover($.proxy(this._hoverIn, this), $.proxy(this._hoverOut, this));
		
		// create toggle buttons
		this._buttonList = $('<ul class="slideshowButtonList" />').appendTo(this.element);
		for (var $i = 0; $i < this._count; $i++) {
			var $link = $('<li><a><span class="icon icon16 fa-circle" /></a></li>').data('index', $i).click($.proxy(this._click, this)).appendTo(this._buttonList);
			if ($i == 0) {
				$link.find('.icon').addClass('active');
			}
		}
		
		this._resetTimer();
		
		$(window).resize($.proxy(this._resize, this));
	},
	
	/**
	 * Rebuilds slideshow height in case the initial height contained resources affecting the
	 * element height, but loading has not completed on slideshow init.
	 */
	rebuildHeight: function() {
		var $firstItem = $(this._items.get(0)).css('height', 'auto');
		var $itemHeight = $firstItem.outerHeight();
		
		this._items.css('height', $itemHeight + 'px');
		this.element.css('height', $itemHeight + 'px');
	},
	
	/**
	 * Handles browser resizing
	 */
	_resize: function() {
		this._width = this.element.css('width', 'auto').innerWidth();
		this._items.each($.proxy(function(index, item) {
			$(item).css({
				left: ((this._width + this.options.itemGap) * index),
				width: this._width
			});
		}, this));
		
		this._index--;
		this.moveTo(null);
	},
	
	/**
	 * Disables cycling while hovering.
	 */
	_hoverIn: function() {
		if (this._timer !== null) {
			this._timer.stop();
		}
	},
	
	/**
	 * Enables cycling after mouse out.
	 */
	_hoverOut: function() {
		this._resetTimer();
	},
	
	/**
	 * Resets cycle timer.
	 */
	_resetTimer: function() {
		if (!this.options.cycle) {
			return;
		}
		
		if (this._timer !== null) {
			this._timer.stop();
		}
		
		var self = this;
		this._timer = new WCF.PeriodicalExecuter(function() {
			self.moveTo(null);
		}, this.options.cycleInterval * 1000);
	},
	
	/**
	 * Handles clicks on the select buttons.
	 * 
	 * @param	object		event
	 */
	_click: function(event) {
		this.moveTo($(event.currentTarget).data('index'));
		
		this._resetTimer();
	},
	
	/**
	 * Moves to a specified item index, NULL will move to the next item in list.
	 * 
	 * @param	integer		index
	 */
	moveTo: function(index) {
		this._index = (index === null) ? this._index + 1 : index;
		if (this._index == this._count) {
			this._index = 0;
		}
		
		$(this._buttonList.find('.icon').removeClass('active').get(this._index)).addClass('active');
		this._itemList.css('left', this._index * (this._width + this.options.itemGap) * -1);
		
		this._trigger('moveTo', null, { index: this._index });
	},
	
	/**
	 * Returns item by index or null if index is invalid.
	 * 
	 * @return	jQuery
	 */
	getItem: function(index) {
		if (this._items[index]) {
			return this._items[index];
		}
		
		return null;
	}
});

jQuery.fn.extend({
	datepicker: function(method) {
		var element = this[0], parameters = Array.prototype.slice.call(arguments, 1);
		
		switch (method) {
			case 'destroy':
				window.__wcf_bc_datePicker.destroy(element);
				break;
				
			case 'getDate':
				return window.__wcf_bc_datePicker.getDate(element);
			
			case 'option':
				if (parameters[0] === 'onClose') {
					if (parameters.length > 1) {
						return this.datepicker('setOption', 'onClose', parameters[1]);
					}
					
					return function() {};
				}
				
				console.warn("datepicker('option') supports only 'onClose'.");
				break;
			
			case 'setDate':
				window.__wcf_bc_datePicker.setDate(element, parameters[0]);
				break;
				
			case 'setOption':
				if (parameters[0] === 'onClose') {
					window.__wcf_bc_datePicker.setCloseCallback(element, parameters[1]);
				}
				else {
					console.warn("datepicker('setOption') supports only 'onClose'.");
				}
				break;
				
			default:
				console.debug("Unsupported method '" + method + "' for datepicker()");
				break;
		}
		
		return this;
	}
});

jQuery.fn.extend({
	wcfTabs: function(method) {
		var element = this[0], parameters = Array.prototype.slice.call(arguments, 1);
		
		require(['Dom/Util', 'WoltLabSuite/Core/Ui/TabMenu'], function(DomUtil, TabMenu) {
			var container = TabMenu.getTabMenu(DomUtil.identify(element));
			if (container !== null) {
				container[method].apply(container, parameters);
			}
		});
	}
});

/**
 * jQuery widget implementation of the wcf pagination.
 * 
 * @deprecated	3.0 - use `WoltLabSuite/Core/Ui/Pagination` instead
 */
$.widget('ui.wcfPages', {
	_api: null,
	
	SHOW_LINKS: 11,
	SHOW_SUB_LINKS: 20,
	
	options: {
		// vars
		activePage: 1,
		maxPage: 1
	},
	
	/**
	 * Creates the pages widget.
	 */
	_create: function() {
		require(['WoltLabSuite/Core/Ui/Pagination'], (function(UiPagination) {
			this._api = new UiPagination(this.element[0], {
				activePage: this.options.activePage,
				maxPage: this.options.maxPage,
				
				callbackShouldSwitch: (function(pageNo) {
					var result = this._trigger('shouldSwitch', undefined, {
						nextPage: pageNo
					});
					
					return (result !== false);
				}).bind(this),
				callbackSwitch: (function(pageNo) {
					this._trigger('switched', undefined, {
						activePage: pageNo
					});
				}).bind(this)
			});
		}).bind(this));
	},
	
	/**
	 * Destroys the pages widget.
	 */
	destroy: function() {
		$.Widget.prototype.destroy.apply(this, arguments);
		
		this._api = null;
		this.element[0].innerHTML = '';
	},
	
	/**
	 * Sets the given option to the given value.
	 * See the jQuery UI widget documentation for more.
	 */
	_setOption: function(key, value) {
		if (key == 'activePage') {
			if (value != this.options[key] && value > 0 && value <= this.options.maxPage) {
				// you can prevent the page switching by returning false or by event.preventDefault()
				// in a shouldSwitch-callback. e.g. if an AJAX request is already running.
				var $result = this._trigger('shouldSwitch', undefined, {
					nextPage: value
				});
				
				if ($result || $result !== undefined) {
					this._api.switchPage(value);
					
				}
				else {
					this._trigger('notSwitched', undefined, {
						activePage: value
					});
				}
			}
		}
		
		return this;
	}
});

/**
 * Namespace for category related classes.
 */
WCF.Category = { };

if (COMPILER_TARGET_DEFAULT) {
	/**
	 * Handles selection of categories.
	 */
	WCF.Category.NestedList = Class.extend({
		/**
		 * list of categories
		 * @var        object
		 */
		_categories: {},
		
		/**
		 * Initializes the WCF.Category.NestedList object.
		 */
		init: function () {
			var self = this;
			$('.jsCategory').each(function (index, category) {
				var $category = $(category).data('parentCategoryID', null).change($.proxy(self._updateSelection, self));
				self._categories[$category.val()] = $category;
				
				// find child categories
				var $childCategoryIDs = [];
				$category.parents('li').find('.jsChildCategory').each(function (innerIndex, childCategory) {
					var $childCategory = $(childCategory).data('parentCategoryID', $category.val()).change($.proxy(self._updateSelection, self));
					self._categories[$childCategory.val()] = $childCategory;
					$childCategoryIDs.push($childCategory.val());
					
					if ($childCategory.is(':checked')) {
						$category.prop('checked', 'checked');
					}
				});
				
				$category.data('childCategoryIDs', $childCategoryIDs);
			});
		},
		
		/**
		 * Updates selection of categories.
		 *
		 * @param        object                event
		 */
		_updateSelection: function (event) {
			var $category = $(event.currentTarget);
			var $parentCategoryID = $category.data('parentCategoryID');
			
			if ($category.is(':checked')) {
				// child category
				if ($parentCategoryID !== null) {
					// mark parent category as checked
					this._categories[$parentCategoryID].prop('checked', 'checked');
				}
			}
			else {
				// top-level category
				if ($parentCategoryID === null) {
					// unmark all child categories
					var $childCategoryIDs = $category.data('childCategoryIDs');
					for (var $i = 0, $length = $childCategoryIDs.length; $i < $length; $i++) {
						this._categories[$childCategoryIDs[$i]].prop('checked', false);
					}
				}
			}
		}
	});
	
	/**
	 * Handles selection of categories.
	 */
	WCF.Category.FlexibleCategoryList = Class.extend({
		/**
		 * category list container
		 * @var        jQuery
		 */
		_list: null,
		
		/**
		 * list of children per category id
		 * @var        object<integer>
		 */
		_categories: {},
		
		init: function (elementID) {
			this._list = $('#' + elementID);
			
			this._buildStructure();
			
			this._list.find('input:checked').each(function () {
				$(this).trigger('change');
			});
			
			if (this._list.children('li').length < 2) {
				this._list.addClass('flexibleCategoryListDisabled');
				return;
			}
		},
		
		_buildStructure: function () {
			var self = this;
			this._list.find('.jsCategory').each(function (i, category) {
				var $category = $(category).change(self._updateSelection.bind(self));
				var $categoryID = parseInt($category.val());
				var $childCategories = [];
				
				$category.parents('li:eq(0)').find('.jsChildCategory').each(function (j, childCategory) {
					var $childCategory = $(childCategory);
					$childCategory.data('parentCategory', $category).change(self._updateSelection.bind(self));
					
					var $childCategoryID = parseInt($childCategory.val());
					$childCategories.push($childCategory);
					
					var $subChildCategories = [];
					
					$childCategory.parents('li:eq(0)').find('.jsSubChildCategory').each(function (k, subChildCategory) {
						var $subChildCategory = $(subChildCategory);
						$subChildCategory.data('parentCategory', $childCategory).change(self._updateSelection.bind(self));
						$subChildCategories.push($subChildCategory);
					});
					
					self._categories[$childCategoryID] = $subChildCategories;
				});
				
				self._categories[$categoryID] = $childCategories;
			});
		},
		
		_updateSelection: function (event) {
			var $category = $(event.currentTarget);
			var $categoryID = parseInt($category.val());
			var $parentCategory = $category.data('parentCategory');
			
			if ($category.is(':checked')) {
				if ($parentCategory) {
					$parentCategory.prop('checked', 'checked');
					
					$parentCategory = $parentCategory.data('parentCategory');
					if ($parentCategory) {
						$parentCategory.prop('checked', 'checked');
					}
				}
			}
			else {
				// uncheck child categories
				if (this._categories[$categoryID]) {
					for (var $i = 0, $length = this._categories[$categoryID].length; $i < $length; $i++) {
						var $childCategory = this._categories[$categoryID][$i];
						$childCategory.prop('checked', false);
						
						var $childCategoryID = parseInt($childCategory.val());
						if (this._categories[$childCategoryID]) {
							for (var $j = 0, $innerLength = this._categories[$childCategoryID].length; $j < $innerLength; $j++) {
								this._categories[$childCategoryID][$j].prop('checked', false);
							}
						}
					}
				}
				
				// uncheck direct parent if it has no more checked children
				if ($parentCategory) {
					var $parentCategoryID = parseInt($parentCategory.val());
					for (var $i = 0, $length = this._categories[$parentCategoryID].length; $i < $length; $i++) {
						if (this._categories[$parentCategoryID][$i].prop('checked')) {
							// at least one child is checked, break
							return;
						}
					}
					
					$parentCategory = $parentCategory.data('parentCategory');
					if ($parentCategory) {
						$parentCategoryID = parseInt($parentCategory.val());
						for (var $i = 0, $length = this._categories[$parentCategoryID].length; $i < $length; $i++) {
							if (this._categories[$parentCategoryID][$i].prop('checked')) {
								// at least one child is checked, break
								return;
							}
						}
					}
				}
			}
		}
	});
}
else {
	WCF.Category.NestedList = Class.extend({
		_categories: {},
		init: function() {},
		_updateSelection: function() {}
	});
	
	WCF.Category.FlexibleCategoryList = Class.extend({
		_list: {},
		_categories: {},
		init: function() {},
		_buildStructure: function() {},
		_updateSelection: function() {}
	});
}

/**
 * Initializes WCF.Condition namespace.
 */
WCF.Condition = { };

/**
 * Initialize WCF.Notice namespace.
 */
WCF.Notice = { };

/**
 * Encapsulate eval() within an own function to prevent problems
 * with optimizing and minifiny JS.
 * 
 * @param	mixed		expression
 * @returns	mixed
 */
function wcfEval(expression) {
	return eval(expression);
}


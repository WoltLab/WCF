/**
 * Class and function collection for WCF.
 * 
 * Major Contributors: Markus Bartz, Tim Duesterhus, Matthias Schmidt and Marcel Werk
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2013 WoltLab GmbH
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
					
					arguments[0] = key;
				break;
				
				case 'string':
					if (key.match(/ID$/)) {
						arguments[0] = key.replace(/ID$/, '-id');
					}
				break;
			} 
		}
		
		
		// call jQuery's own data method
		var $data = $jQueryData.apply(this, arguments);
		
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
			console[consoleProperties[i]] = function () { }
		}
	}
	
	if (typeof(console.debug) === 'undefined') {
		// forward console.debug to console.log (IE9)
		console.debug = function(string) { console.log(string); };
	}
})();

/**
 * Simple JavaScript Inheritance
 * By John Resig http://ejohn.org/
 * MIT Licensed.
 */
// Inspired by base2 and Prototype
(function(){var a=false,b=/xyz/.test(function(){xyz})?/\b_super\b/:/.*/;this.Class=function(){};Class.extend=function(c){function g(){if(!a&&this.init)this.init.apply(this,arguments);}var d=this.prototype;a=true;var e=new this;a=false;for(var f in c){e[f]=typeof c[f]=="function"&&typeof d[f]=="function"&&b.test(c[f])?function(a,b){return function(){var c=this._super;this._super=d[a];var e=b.apply(this,arguments);this._super=c;return e;};}(f,c[f]):c[f]}g.prototype=e;g.prototype.constructor=g;g.extend=arguments.callee;return g;};})();

/**
 * Provides a hashCode() method for strings, similar to Java's String.hashCode().
 * 
 * @see	http://werxltd.com/wp/2010/05/13/javascript-implementation-of-javas-string-hashcode-method/
 */
String.prototype.hashCode = function() {
	var $char;
	var $hash = 0;
	
	if (this.length) {
		for (var $i = 0, $length = this.length; $i < $length; $i++) {
			$char = this.charCodeAt($i);
			$hash = (($hash << 5) - $hash) + $char;
			$hash = $hash & $hash; // convert to 32bit integer
		}
	}
	
	return $hash;
};

/**
 * User-Agent based browser detection and touch detection.
 */
(function() {
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
	browser = {};
	
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
	
	jQuery.browser = browser;
	jQuery.browser.touch = (!!('ontouchstart' in window) || !!('msmaxtouchpoints' in window.navigator));
})();

/**
 * jQuery.browser.mobile (http://detectmobilebrowser.com/)
 * jQuery.browser.mobile will be true if the browser is a mobile device
 **/
(function(a){jQuery.browser.mobile=/android.+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od|ad)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|e\-|e\/|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(di|rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|xda(\-|2|g)|yas\-|your|zeto|zte\-/i.test(a.substr(0,4))})(navigator.userAgent||navigator.vendor||window.opera);

/**
 * Initialize WCF namespace
 */
var WCF = {};

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
		var dimensions = css = {};
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
		var offsets = css = {};
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
	 * @return jQuery
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
		if (!this.attr('id')) {
			this.attr('id', WCF.getRandomID());
		}
		
		return this.attr('id');
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
	}
});

/**
 * WoltLab Community Framework core methods
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
		var $elementID = '';
		
		do {
			$elementID = 'wcf' + this._idCounter++;
		}
		while ($.wcfIsset($elementID));
		
		return $elementID;
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
 */
WCF.Dropdown = {
	/**
	 * list of callbacks
	 * @var	object
	 */
	_callbacks: { },
	
	/**
	 * initialization state
	 * @var	boolean
	 */
	_didInit: false,
	
	/**
	 * list of registered dropdowns
	 * @var	object
	 */
	_dropdowns: { },
	
	/**
	 * container for dropdown menus
	 * @var	object
	 */
	_menuContainer: null,
	
	/**
	 * list of registered dropdown menus
	 * @var	object
	 */
	_menus: { },
	
	/**
	 * Initializes dropdowns.
	 */
	init: function() {
		if (this._menuContainer === null) {
			this._menuContainer = $('<div id="dropdownMenuContainer" />').appendTo(document.body);
		}
		
		var self = this;
		$('.dropdownToggle:not(.jsDropdownEnabled)').each(function(index, button) {
			self.initDropdown($(button), false);
		});
		
		if (!this._didInit) {
			this._didInit = true;
			
			WCF.CloseOverlayHandler.addCallback('WCF.Dropdown', $.proxy(this._closeAll, this));
			WCF.DOMNodeInsertedHandler.addCallback('WCF.Dropdown', $.proxy(this.init, this));
		}
		
		$(window).resize($.proxy(this._resize, this));
	},
	
	/**
	 * Handles resizing the window by making sure that the menu positions are
	 * recalculated.
	 */
	_resize: function() {
		for (var $containerID in this._dropdowns) {
			this._menus[$containerID].removeData('orientationX');
		}
	},
	
	/**
	 * Initializes a dropdown.
	 * 
	 * @param	jQuery		button
	 * @param	boolean		isLazyInitialization
	 */
	initDropdown: function(button, isLazyInitialization) {
		if (button.hasClass('jsDropdownEnabled') || button.data('target')) {
			return;
		}
		
		var $dropdown = button.parents('.dropdown');
		if (!$dropdown.length) {
			// broken dropdown, ignore
			return;
		}
		
		var $dropdownMenu = button.next('.dropdownMenu');
		if (!$dropdownMenu.length) {
			// broken dropdown, ignore
			return;
		}
		
		$dropdownMenu.detach().appendTo(this._menuContainer);
		var $containerID = $dropdown.wcfIdentify();
		if (!this._dropdowns[$containerID]) {
			button.addClass('jsDropdownEnabled').click($.proxy(this._toggle, this));
			
			this._dropdowns[$containerID] = $dropdown;
			this._menus[$containerID] = $dropdownMenu;
		}
		
		button.data('target', $containerID);
		
		if (isLazyInitialization) {
			button.trigger('click');
		}
	},
	
	/**
	 * Initializes a dropdown fragment which behaves like a usual dropdown
	 * but is not controlled by a trigger element.
	 * 
	 * @param	jQuery		dropdown
	 * @param	jQuery		dropdownMenu
	 */
	initDropdownFragment: function(dropdown, dropdownMenu) {
		var $containerID = dropdown.wcfIdentify();
		if (this._dropdowns[$containerID]) {
			console.debug("[WCF.Dropdown] Cannot register dropdown identified by '" + $containerID + "' as a fragement.");
			return;
		}
		
		this._dropdowns[$containerID] = dropdown;
		this._menus[$containerID] = dropdownMenu.detach().appendTo(this._menuContainer);
	},
	
	/**
	 * Registers a callback notified upon dropdown state change.
	 * 
	 * @param	string		identifier
	 * @var		object		callback
	 */
	registerCallback: function(identifier, callback) {
		if (!$.isFunction(callback)) {
			console.debug("[WCF.Dropdown] Callback for '" + identifier + "' is invalid");
			return false;
		}
		
		if (!this._callbacks[identifier]) {
			this._callbacks[identifier] = [ ];
		}
		
		this._callbacks[identifier].push(callback);
	},
	
	/**
	 * Toggles a dropdown.
	 * 
	 * @param	object		event
	 * @param	string		targetID
	 */
	_toggle: function(event, targetID) {
		var $targetID = (event === null) ? targetID : $(event.currentTarget).data('target');
		
		// close all dropdowns
		for (var $containerID in this._dropdowns) {
			var $dropdown = this._dropdowns[$containerID];
			var $dropdownMenu = this._menus[$containerID];
			
			if ($dropdown.hasClass('dropdownOpen')) {
				$dropdown.removeClass('dropdownOpen');
				$dropdownMenu.removeClass('dropdownOpen');
				
				this._notifyCallbacks($containerID, 'close');
			}
			else if ($containerID === $targetID) {
				$dropdown.addClass('dropdownOpen');
				$dropdownMenu.addClass('dropdownOpen');
				
				this._notifyCallbacks($containerID, 'open');
				
				this.setAlignment($dropdown, $dropdownMenu);
			}
		}
		
		if (event !== null) {
			event.stopPropagation();
			return false;
		}
	},
	
	/**
	 * Toggles a dropdown.
	 * 
	 * @param	string		containerID
	 */
	toggleDropdown: function(containerID) {
		this._toggle(null, containerID);
	},
	
	/**
	 * Returns dropdown by container id.
	 * 
	 * @param	string		containerID
	 * @return	jQuery
	 */
	getDropdown: function(containerID) {
		if (this._dropdowns[containerID]) {
			return this._dropdowns[containerID];
		}
		
		return null;
	},
	
	/**
	 * Returns dropdown menu by container id.
	 * 
	 * @param	string		containerID
	 * @return	jQuery
	 */
	getDropdownMenu: function(containerID) {
		if (this._menus[containerID]) {
			return this._menus[containerID];
		}
		
		return null;
	},
	
	/**
	 * Sets alignment for given container id.
	 * 
	 * @param	string		containerID
	 */
	setAlignmentByID: function(containerID) {
		var $dropdown = this.getDropdown(containerID);
		if ($dropdown === null) {
			console.debug("[WCF.Dropdown] Unable to find dropdown identified by '" + containerID + "'");
		}
		
		var $dropdownMenu = this.getDropdownMenu(containerID);
		if ($dropdownMenu === null) {
			console.debug("[WCF.Dropdown] Unable to find dropdown menu identified by '" + containerID + "'");
		}
		
		this.setAlignment($dropdown, $dropdownMenu);
	},
	
	/**
	 * Sets alignment for dropdown.
	 * 
	 * @param	jQuery		dropdown
	 * @param	jQuery		dropdownMenu
	 */
	setAlignment: function(dropdown, dropdownMenu) {
		// get dropdown position
		var $dropdownDimensions = dropdown.getDimensions('outer');
		var $dropdownOffsets = dropdown.getOffsets('offset');
		var $menuDimensions = dropdownMenu.getDimensions('outer');
		var $windowWidth = $(window).width();
		
		// check if button belongs to an i18n textarea
		var $button = dropdown.find('.dropdownToggle');
		if ($button.hasClass('dropdownCaptionTextarea')) {
			// use button dimensions instead
			$dropdownDimensions = $button.getDimensions('outer');
		}
		
		// validate if current alignment is still fine, prevents "jumping"
		var $align = null;
		switch (dropdownMenu.data('orientationX')) {
			case 'left':
				if (($dropdownOffsets.left + $menuDimensions.width) > $windowWidth) {
					$align = 'right';
				}
			break;
			
			case 'right':
				if (($dropdownOffsets.left + $dropdownDimensions.width - $menuDimensions.width) < 0) {
					$align = 'left';
				}
			break;
			
			default:
				$align = 'left';
				
				if (($dropdownOffsets.left + $menuDimensions.width) > $windowWidth) {
					$align = 'right';
				}
			break;
		}
		
		// alignment has changed
		if ($align !== null) {
			dropdownMenu.data('orientationX', $align);
			
			var $left = 'auto';
			var $right = 'auto';
			
			if ($align === 'left') {
				dropdownMenu.removeClass('dropdownArrorRight');
				
				$left = $dropdownOffsets.left + 'px';
			}
			else {
				dropdownMenu.addClass('dropdownArrowRight');
				
				$right = ($windowWidth - ($dropdownOffsets.left + $dropdownDimensions.width)) + 'px';
			}
			
			dropdownMenu.css({
				left: $left,
				right: $right,
				top: $dropdownOffsets.top + $dropdownDimensions.height + 7 + 'px'
			});
		}
	},
	
	/**
	 * Closes all dropdowns.
	 */
	_closeAll: function() {
		for (var $containerID in this._dropdowns) {
			var $dropdown = this._dropdowns[$containerID];
			if ($dropdown.hasClass('dropdownOpen')) {
				$dropdown.removeClass('dropdownOpen');
				this._menus[$containerID].removeClass('dropdownOpen');
				
				this._notifyCallbacks($containerID, 'close');
			}
		}
	},
	
	/**
	 * Closes a dropdown without notifying callbacks.
	 * 
	 * @param	string		containerID
	 */
	close: function(containerID) {
		if (!this._dropdowns[containerID]) {
			return;
		}
		
		this._dropdowns[containerID].removeClass('dropdownMenu');
		this._menus[containerID].removeClass('dropdownMenu');
	},
	
	/**
	 * Notifies callbacks.
	 * 
	 * @param	string		containerID
	 * @param	string		action
	 */
	_notifyCallbacks: function(containerID, action) {
		if (!this._callbacks[containerID]) {
			return;
		}
		
		for (var $i = 0, $length = this._callbacks[containerID].length; $i < $length; $i++) {
			this._callbacks[containerID][$i](containerID, action);
		}
	}
};

/**
 * Clipboard API
 */
WCF.Clipboard = {
	/**
	 * action proxy object
	 * @var	WCF.Action.Proxy
	 */
	_actionProxy: null,
	
	/**
	 * action objects
	 * @var	object
	 */
	_actionObjects: {},
	
	/**
	 * list of clipboard containers
	 * @var	jQuery
	 */
	_container: null,
	
	/**
	 * container meta data
	 * @var	object
	 */
	_containerData: { },
	
	/**
	 * user has marked items
	 * @var	boolean
	 */
	_hasMarkedItems: false,
	
	/**
	 * list of ids of marked objects grouped by object type
	 * @var	object
	 */
	_markedObjectIDs: { },
	
	/**
	 * current page
	 * @var	string
	 */
	_page: '',
	
	/**
	 * current page's object id
	 * @var	integer
	 */
	_pageObjectID: 0,
	
	/**
	 * proxy object
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * list of elements already tracked for clipboard actions
	 * @var	object
	 */
	_trackedElements: { },
	
	/**
	 * Initializes the clipboard API.
	 * 
	 * @param	string		page
	 * @param	integer		hasMarkedItems
	 * @param	object		actionObjects
	 * @param	integer		pageObjectID
	 */
	init: function(page, hasMarkedItems, actionObjects, pageObjectID) {
		this._page = page;
		this._actionObjects = actionObjects || { };
		this._hasMarkedItems = (hasMarkedItems > 0);
		this._pageObjectID = parseInt(pageObjectID) || 0;
		
		this._actionProxy = new WCF.Action.Proxy({
			success: $.proxy(this._actionSuccess, this),
			url: 'index.php/ClipboardProxy/?t=' + SECURITY_TOKEN + SID_ARG_2ND
		});
		
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this),
			url: 'index.php/Clipboard/?t=' + SECURITY_TOKEN + SID_ARG_2ND
		});
		
		// init containers first
		this._containers = $('.jsClipboardContainer').each($.proxy(function(index, container) {
			this._initContainer(container);
		}, this));
		
		// loads marked items
		if (this._hasMarkedItems && this._containers.length) {
			this._loadMarkedItems();
		}
		
		var self = this;
		WCF.DOMNodeInsertedHandler.addCallback('WCF.Clipboard', function() {
			self._containers = $('.jsClipboardContainer').each($.proxy(function(index, container) {
				self._initContainer(container);
			}, self));
		});
	},
	
	/**
	 * Loads marked items on init.
	 */
	_loadMarkedItems: function() {
		new WCF.Action.Proxy({
			autoSend: true,
			data: {
				containerData: this._containerData,
				pageClassName: this._page,
				pageObjectID: this._pageObjectID
			},
			success: $.proxy(this._loadMarkedItemsSuccess, this),
			url: 'index.php/ClipboardLoadMarkedItems/?t=' + SECURITY_TOKEN + SID_ARG_2ND
		});
	},
	
	/**
	 * Reloads the list of marked items.
	 */
	reload: function() {
		this._loadMarkedItems();
	},
	
	/**
	 * Marks all returned items as marked
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_loadMarkedItemsSuccess: function(data, textStatus, jqXHR) {
		this._resetMarkings();
		
		for (var $typeName in data.markedItems) {
			if (!this._markedObjectIDs[$typeName]) {
				this._markedObjectIDs[$typeName] = { };
			}
			
			var $objectData = data.markedItems[$typeName];
			for (var $i in $objectData) {
				this._markedObjectIDs[$typeName].push($objectData[$i]);
			}
			
			// loop through all containers
			this._containers.each($.proxy(function(index, container) {
				var $container = $(container);
				
				// typeName does not match, continue
				if ($container.data('type') != $typeName) {
					return true;
				}
				
				// mark items as marked
				$container.find('input.jsClipboardItem').each($.proxy(function(innerIndex, item) {
					var $item = $(item);
					if (WCF.inArray($item.data('objectID'), this._markedObjectIDs[$typeName])) {
						$item.prop('checked', true);
						
						// add marked class for element container
						$item.parents('.jsClipboardObject').addClass('jsMarked');
					}
				}, this));
				
				// check if there is a markAll-checkbox
				$container.find('input.jsClipboardMarkAll').each(function(innerIndex, markAll) {
					var $allItemsMarked = true;
					
					$container.find('input.jsClipboardItem').each(function(itemIndex, item) {
						var $item = $(item);
						if (!$item.prop('checked')) {
							$allItemsMarked = false;
						}
					});
					
					if ($allItemsMarked) {
						$(markAll).prop('checked', true);
					}
				});
			}, this));
		}
		
		// call success method to build item list editors
		this._success(data, textStatus, jqXHR);
	},
	
	/**
	 * Resets all checkboxes.
	 */
	_resetMarkings: function() {
		this._containers.each($.proxy(function(index, container) {
			var $container = $(container);
			
			this._markedObjectIDs[$container.data('type')] = [ ];
			$container.find('input.jsClipboardItem, input.jsClipboardMarkAll').prop('checked', false);
			$container.find('.jsClipboardObject').removeClass('jsMarked');
		}, this));
	},
	
	/**
	 * Initializes a clipboard container.
	 * 
	 * @param	object		container
	 */
	_initContainer: function(container) {
		var $container = $(container);
		var $containerID = $container.wcfIdentify();
		
		if (!this._trackedElements[$containerID]) {
			$container.find('.jsClipboardMarkAll').data('hasContainer', $containerID).click($.proxy(this._markAll, this));
			
			this._markedObjectIDs[$container.data('type')] = [ ];
			this._containerData[$container.data('type')] = {};
			$.each($container.data(), $.proxy(function(index, element) {
				if (index.match(/^type(.+)/)) {
					this._containerData[$container.data('type')][WCF.String.lcfirst(index.replace(/^type/, ''))] = element;
				}
			}, this));
			
			this._trackedElements[$containerID] = [ ];
		}
		
		// track individual checkboxes
		$container.find('input.jsClipboardItem').each($.proxy(function(index, input) {
			var $input = $(input);
			var $inputID = $input.wcfIdentify();
			
			if (!WCF.inArray($inputID, this._trackedElements[$containerID])) {
				this._trackedElements[$containerID].push($inputID);
				
				$input.data('hasContainer', $containerID).click($.proxy(this._click, this));
			}
		}, this));
	},
	
	/**
	 * Processes change checkbox state.
	 * 
	 * @param	object		event
	 */
	_click: function(event) {
		var $item = $(event.target);
		var $objectID = $item.data('objectID');
		var $isMarked = ($item.prop('checked')) ? true : false;
		var $objectIDs = [ $objectID ];
		
		if ($item.data('hasContainer')) {
			var $container = $('#' + $item.data('hasContainer'));
			var $type = $container.data('type');
		}
		else {
			var $type = $item.data('type');
		}
		
		if ($isMarked) {
			this._markedObjectIDs[$type].push($objectID);
			$item.parents('.jsClipboardObject').addClass('jsMarked');
		}
		else {
			this._markedObjectIDs[$type] = $.removeArrayValue(this._markedObjectIDs[$type], $objectID);
			$item.parents('.jsClipboardObject').removeClass('jsMarked');
		}
		
		// item is part of a container
		if ($item.data('hasContainer')) {
			// check if all items are marked
			var $markedAll = true;
			$container.find('input.jsClipboardItem').each(function(index, containerItem) {
				var $containerItem = $(containerItem);
				if (!$containerItem.prop('checked')) {
					$markedAll = false;
				}
			});
			
			// simulate a ticked 'markAll' checkbox
			$container.find('.jsClipboardMarkAll').each(function(index, markAll) {
				if ($markedAll) {
					$(markAll).prop('checked', true);
				}
				else {
					$(markAll).prop('checked', false);
				}
			});
		}
		
		this._saveState($type, $objectIDs, $isMarked);
	},
	
	/**
	 * Marks all associated clipboard items as checked.
	 * 
	 * @param	object		event
	 */
	_markAll: function(event) {
		var $item = $(event.target);
		var $objectIDs = [ ];
		var $isMarked = true;
		
		// if markAll object is a checkbox, allow toggling
		if ($item.is('input')) {
			$isMarked = $item.prop('checked');
		}
		
		if ($item.data('hasContainer')) {
			var $container = $('#' + $item.data('hasContainer'));
			var $type = $container.data('type');
		}
		else {
			var $type = $item.data('type');
		}
		
		// handle item containers
		if ($item.data('hasContainer')) {
			// toggle state for all associated items
			$container.find('input.jsClipboardItem').each($.proxy(function(index, containerItem) {
				var $containerItem = $(containerItem);
				var $objectID = $containerItem.data('objectID');
				if ($isMarked) {
					if (!$containerItem.prop('checked')) {
						$containerItem.prop('checked', true);
						this._markedObjectIDs[$type].push($objectID);
						$objectIDs.push($objectID);
					}
				}
				else {
					if ($containerItem.prop('checked')) {
						$containerItem.prop('checked', false);
						this._markedObjectIDs[$type] = $.removeArrayValue(this._markedObjectIDs[$type], $objectID);
						$objectIDs.push($objectID);
					}
				}
			}, this));
			
			if ($isMarked) {
				$container.find('.jsClipboardObject').addClass('jsMarked');
			}
			else {
				$container.find('.jsClipboardObject').removeClass('jsMarked');
			}
		}
		
		// save new status
		this._saveState($type, $objectIDs, $isMarked);
	},
	
	/**
	 * Saves clipboard item state.
	 * 
	 * @param	string		type
	 * @param	array		objectIDs
	 * @param	boolean		isMarked
	 */
	_saveState: function(type, objectIDs, isMarked) {
		this._proxy.setOption('data', {
			action: (isMarked) ? 'mark' : 'unmark',
			containerData: this._containerData,
			objectIDs: objectIDs,
			pageClassName: this._page,
			pageObjectID: this._pageObjectID,
			type: type
		});
		this._proxy.sendRequest();
	},
	
	/**
	 * Updates editor options.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		// clear all editors first
		var $containers = {};
		$('.jsClipboardEditor').each(function(index, container) {
			var $container = $(container);
			var $types = eval($container.data('types'));
			for (var $i = 0, $length = $types.length; $i < $length; $i++) {
				var $typeName = $types[$i];
				$containers[$typeName] = $container;
			}
			
			var $containerID = $container.wcfIdentify();
			WCF.CloseOverlayHandler.removeCallback($containerID);
			
			$container.empty();
		});
		
		// do not build new editors
		if (!data.items) return;
		
		// rebuild editors
		for (var $typeName in data.items) {
			if (!$containers[$typeName]) {
				continue;
			}
			
			// create container
			var $container = $containers[$typeName];
			var $list = $container.children('ul');
			if ($list.length == 0) {
				$list = $('<ul />').appendTo($container);
			}
			
			var $editor = data.items[$typeName];
			var $label = $('<li class="dropdown"><span class="dropdownToggle button">' + $editor.label + '</span></li>').appendTo($list);
			var $itemList = $('<ol class="dropdownMenu"></ol>').appendTo($label);
			
			// create editor items
			for (var $itemIndex in $editor.items) {
				var $item = $editor.items[$itemIndex];
				
				var $listItem = $('<li><span>' + $item.label + '</span></li>').appendTo($itemList);
				$listItem.data('container', $container);
				$listItem.data('objectType', $typeName);
				$listItem.data('actionName', $item.actionName).data('parameters', $item.parameters);
				$listItem.data('internalData', $item.internalData).data('url', $item.url).data('type', $typeName);
				
				// bind event
				$listItem.click($.proxy(this._executeAction, this));
			}
			
			// add 'unmark all'
			$('<li class="dropdownDivider" />').appendTo($itemList);
			$('<li><span>' + WCF.Language.get('wcf.clipboard.item.unmarkAll') + '</span></li>').appendTo($itemList).click($.proxy(function() {
				this._proxy.setOption('data', {
					action: 'unmarkAll',
					type: $typeName
				});
				this._proxy.setOption('success', $.proxy(function(data, textStatus, jqXHR) {
					for (var $__containerID in this._containers) {
						var $__container = $(this._containers[$__containerID]);
						if ($__container.data('type') == $typeName) {
							$__container.find('.jsClipboardMarkAll, .jsClipboardItem').prop('checked', false);
							$__container.find('.jsClipboardObject').removeClass('jsMarked');
							
							break;
						}
					}
					
					// call and restore success method
					this._success(data, textStatus, jqXHR);
					this._proxy.setOption('success', $.proxy(this._success, this));
				}, this));
				this._proxy.sendRequest();
			}, this));
			
			WCF.Dropdown.initDropdown($label.children('.dropdownToggle'), false);
		}
	},
	
	/**
	 * Closes the clipboard editor item list.
	 */
	_closeLists: function() {
		$('.jsClipboardEditor ul').removeClass('dropdownOpen');
	},
	
	/**
	 * Executes a clipboard editor item action.
	 * 
	 * @param	object		event
	 */
	_executeAction: function(event) {
		var $listItem = $(event.currentTarget);
		var $url = $listItem.data('url');
		if ($url) {
			window.location.href = $url;
		}
		
		if ($listItem.data('parameters').className && $listItem.data('parameters').actionName) {
			if ($listItem.data('parameters').actionName === 'unmarkAll' || $listItem.data('parameters').objectIDs) {
				var $confirmMessage = $listItem.data('internalData')['confirmMessage'];
				if ($confirmMessage) {
					var $template = $listItem.data('internalData')['template'];
					if ($template) $template = $($template);
					
					WCF.System.Confirmation.show($confirmMessage, $.proxy(function(action) {
						if (action === 'confirm') {
							var $data = { };
							
							if ($template && $template.length) {
								$('#wcfSystemConfirmationContent').find('input, select, textarea').each(function(index, item) {
									var $item = $(item);
									$data[$item.prop('name')] = $item.val();
								});
							}
							
							this._executeAJAXActions($listItem, $data);
						}
					}, this), '', $template);
				}
				else {
					this._executeAJAXActions($listItem, { });
				}
			}
		}
		
		// fire event
		$listItem.data('container').trigger('clipboardAction', [ $listItem.data('type'), $listItem.data('actionName'), $listItem.data('parameters') ]);
	},
	
	/**
	 * Executes the AJAX actions for the given editor list item.
	 * 
	 * @param	jQuery		listItem
	 * @param	object		data
	 */
	_executeAJAXActions: function(listItem, data) {
		data = data || { };
		var $objectIDs = [];
		if (listItem.data('parameters').actionName !== 'unmarkAll') {
			$.each(listItem.data('parameters').objectIDs, function(index, objectID) {
				$objectIDs.push(parseInt(objectID));
			});
		}
		
		var $parameters = {
			data: data,
			containerData: this._containerData[listItem.data('type')]
		};
		var $__parameters = listItem.data('internalData')['parameters'];
		if ($__parameters !== undefined) {
			for (var $key in $__parameters) {
				$parameters[$key] = $__parameters[$key];
			}
		}
		
		new WCF.Action.Proxy({
			autoSend: true,
			data: {
				actionName: listItem.data('parameters').actionName,
				className: listItem.data('parameters').className,
				objectIDs: $objectIDs,
				parameters: $parameters
			},
			success: $.proxy(function(data) {
				if (listItem.data('parameters').actionName !== 'unmarkAll') {
					listItem.data('container').trigger('clipboardActionResponse', [ data, listItem.data('type'), listItem.data('actionName'), listItem.data('parameters') ]);
				}
				
				this._loadMarkedItems();
			}, this)
		});
		
		if (this._actionObjects[listItem.data('objectType')] && this._actionObjects[listItem.data('objectType')][listItem.data('parameters').actionName]) {
			this._actionObjects[listItem.data('objectType')][listItem.data('parameters').actionName].triggerEffect($objectIDs);
		}
	},
	
	/**
	 * Sends a clipboard proxy request.
	 * 
	 * @param	object		item
	 */
	sendRequest: function(item) {
		var $item = $(item);
		
		this._actionProxy.setOption('data', {
			parameters: $item.data('parameters'),
			typeName: $item.data('type')
		});
		this._actionProxy.sendRequest();
	}
};

/**
 * Provides a simple call for periodical executed functions. Based upon
 * ideas by Prototype's PeriodicalExecuter.
 * 
 * @see		https://github.com/sstephenson/prototype/blob/master/src/prototype/lang/periodical_executer.js
 * @param	function		callback
 * @param	integer			delay
 */
WCF.PeriodicalExecuter = Class.extend({
	/**
	 * callback for each execution cycle
	 * @var	object
	 */
	_callback: null,
	
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
		this._intervalID = setInterval($.proxy(this._execute, this), delay);
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
	}
});

/**
 * Handler for loading overlays
 */
WCF.LoadingOverlayHandler = {
	/**
	 * count of active loading-requests
	 * @var	integer
	 */
	_activeRequests: 0,
	
	/**
	 * loading overlay
	 * @var	jQuery
	 */
	_loadingOverlay: null,
	
	/**
	 * WCF.PeriodicalExecuter instance
	 * @var	WCF.PeriodicalExecuter
	 */
	_pending: null,
	
	/**
	 * Adds one loading-request and shows the loading overlay if nessercery
	 */
	show: function() {
		if (this._loadingOverlay === null) { // create loading overlay on first run
			this._loadingOverlay = $('<div class="spinner"><span class="icon icon48 icon-spinner" /> <span>' + WCF.Language.get('wcf.global.loading') + '</span></div>').hide().appendTo($('body'));
		}
		
		this._activeRequests++;
		if (this._activeRequests == 1) {
			if (this._pending === null) {
				var self = this;
				this._pending = new WCF.PeriodicalExecuter(function(pe) {
					if (self._activeRequests) {
						self._loadingOverlay.stop(true, true).fadeIn(100);
					}
					
					pe.stop();
					self._pending = null;
				}, 250); 
			}
			
		}
	},
	
	/**
	 * Removes one loading-request and hides loading overlay if there're no more pending requests
	 */
	hide: function() {
		this._activeRequests--;
		if (this._activeRequests == 0) {
			if (this._pending !== null) {
				this._pending.stop();
				this._pending = null;
			}
			
			this._loadingOverlay.stop(true, true).fadeOut(100);
		}
	},
	
	/**
	 * Updates a icon to/from spinner
	 * 
	 * @param	jQuery	target
	 * @pram	boolean	loading
	 */
	updateIcon: function(target, loading) {
		var $method = (loading === undefined || loading ? 'addClass' : 'removeClass');
		
		target.find('.icon')[$method]('icon-spinner');
		if (target.hasClass('icon')) {
			target[$method]('icon-spinner');
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
 * @param	object		options
 */
WCF.Action.Proxy = Class.extend({
	/**
	 * shows loading overlay for a single request
	 * @var	boolean
	 */
	_showLoadingOverlayOnce: false,
	
	/**
	 * suppresses errors
	 * @var	boolean
	 */
	_suppressErrors: false,
	
	/**
	 * last request
	 * @var	jqXHR
	 */
	_lastRequest: null,
	
	/**
	 * Initializes AJAXProxy.
	 * 
	 * @param	object		options
	 */
	init: function(options) {
		// initialize default values
		this.options = $.extend(true, {
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
			url: 'index.php/AJAXProxy/?t=' + SECURITY_TOKEN + SID_ARG_2ND,
			aborted: null,
			autoAbortPrevious: false
		}, options);
		
		this.confirmationDialog = null;
		this.loading = null;
		this._showLoadingOverlayOnce = false;
		this._suppressErrors = (this.options.suppressErrors === true);
		
		// send request immediately after initialization
		if (this.options.autoSend) {
			this.sendRequest();
		}
		
		var self = this;
		$(window).on('beforeunload', function() { self._suppressErrors = true; });
	},
	
	/**
	 * Sends an AJAX request.
	 * 
	 * @param	abortPrevious	boolean
	 * @return	jqXHR
	 */
	sendRequest: function(abortPrevious) {
		this._init();
		
		if (abortPrevious || this.options.autoAbortPrevious) {
			this.abortPrevious();
		}
		
		this._lastRequest = $.ajax({
			data: this.options.data,
			dataType: this.options.dataType,
			jsonp: this.options.jsonp,
			async: this.options.async,
			type: this.options.type,
			url: this.options.url,
			success: $.proxy(this._success, this),
			error: $.proxy(this._failure, this)
		});
		return this._lastRequest;
	},
	
	/**
	 * Aborts the previous request
	 */
	abortPrevious: function() {
		if (this._lastRequest !== null) {
			this._lastRequest.abort();
			this._lastRequest = null;
		}
	},
	
	/**
	 * Shows loading overlay for a single request.
	 */
	showLoadingOverlayOnce: function() {
		this._showLoadingOverlayOnce = true;
	},
	
	/**
	 * Suppressed errors for this action proxy.
	 */
	suppressErrors: function() {
		this._suppressErrors = true;
	},
	
	/**
	 * Fires before request is send, displays global loading status.
	 */
	_init: function() {
		if ($.isFunction(this.options.init)) {
			this.options.init(this);
		}
		
		if (this.options.showLoadingOverlay || this._showLoadingOverlayOnce) {
			WCF.LoadingOverlayHandler.show();
		}
	},
	
	/**
	 * Handles AJAX errors.
	 * 
	 * @param	object		jqXHR
	 * @param	string		textStatus
	 * @param	string		errorThrown
	 */
	_failure: function(jqXHR, textStatus, errorThrown) {
		if (textStatus == 'abort') {
			// call child method if applicable
			if ($.isFunction(this.options.aborted)) {
				this.options.aborted(jqXHR);
			}
			
			return;
		}
		
		try {
			var $data = $.parseJSON(jqXHR.responseText);
			
			// call child method if applicable
			var $showError = true;
			if ($.isFunction(this.options.failure)) {
				$showError = this.options.failure($data, jqXHR, textStatus, errorThrown);
			}
			
			if (!this._suppressErrors && $showError !== false) {
				var $details = '';
				if ($data.stacktrace) $details = '<br /><p>Stacktrace:</p><p>' + $data.stacktrace + '</p>';
				else if ($data.exceptionID) $details = '<br /><p>Exception ID: <code>' + $data.exceptionID + '</code></p>';
				
				$('<div class="ajaxDebugMessage"><p>' + $data.message + '</p>' + $details + '</div>').wcfDialog({ title: WCF.Language.get('wcf.global.error.title') });
			}
		}
		// failed to parse JSON
		catch (e) {
			// call child method if applicable
			var $showError = true;
			if ($.isFunction(this.options.failure)) {
				$showError = this.options.failure(null, jqXHR, textStatus, errorThrown);
			}
			
			if (!this._suppressErrors && $showError !== false) {
				var $message = (textStatus === 'timeout') ? WCF.Language.get('wcf.global.error.timeout') : jqXHR.responseText;
				
				// validate if $message is neither empty nor 'undefined'
				if ($message && $message != 'undefined') {
					$('<div class="ajaxDebugMessage"><p>' + $message + '</p></div>').wcfDialog({ title: WCF.Language.get('wcf.global.error.title') });
				}
			}
		}
		
		this._after();
	},
	
	/**
	 * Handles successful AJAX requests.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	object		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		// call child method if applicable
		if ($.isFunction(this.options.success)) {
			// trim HTML before processing, see http://jquery.com/upgrade-guide/1.9/#jquery-htmlstring-versus-jquery-selectorstring
			if (data && data.returnValues && data.returnValues.template !== undefined) {
				data.returnValues.template = $.trim(data.returnValues.template);
			}
			
			this.options.success(data, textStatus, jqXHR);
		}
		
		this._after();
	},
	
	/**
	 * Fires after an AJAX request, hides global loading status.
	 */
	_after: function() {
		this._lastRequest = null;
		if ($.isFunction(this.options.after)) {
			this.options.after();
		}
		
		if (this.options.showLoadingOverlay || this._showLoadingOverlayOnce) {
			WCF.LoadingOverlayHandler.hide();
			
			if (this._showLoadingOverlayOnce) {
				this._showLoadingOverlayOnce = false;
			}
		}
		
		WCF.DOMNodeInsertedHandler.execute();
		
		// fix anchor tags generated through WCF::getAnchor()
		$('a[href*=#]').each(function(index, link) {
			var $link = $(link);
			if ($link.prop('href').indexOf('AJAXProxy') != -1) {
				var $anchor = $link.prop('href').substr($link.prop('href').indexOf('#'));
				var $pageLink = document.location.toString().replace(/#.*/, '');
				$link.prop('href', $pageLink + $anchor);
			}
		});
	},
	
	/**
	 * Sets options, MUST be used to set parameters before sending request
	 * if calling from child classes.
	 * 
	 * @param	string		optionName
	 * @param	mixed		optionData
	 */
	setOption: function(optionName, optionData) {
		this.options[optionName] = optionData;
	}
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
 * @param	string		className
 * @param	string		containerSelector
 * @param	string		buttonSelector
 */
WCF.Action.Delete = Class.extend({
	/**
	 * delete button selector
	 * @var	string
	 */
	_buttonSelector: '',
	
	/**
	 * action class name
	 * @var	string
	 */
	_className: '',
	
	/**
	 * container selector
	 * @var	string
	 */
	_containerSelector: '',
	
	/**
	 * list of known container ids
	 * @var	array<string>
	 */
	_containers: [ ],
	
	/**
	 * Initializes 'delete'-Proxy.
	 * 
	 * @param	string		className
	 * @param	string		containerSelector
	 * @param	string		buttonSelector
	 */
	init: function(className, containerSelector, buttonSelector) {
		this._containerSelector = containerSelector;
		this._className = className;
		this._buttonSelector = (buttonSelector) ? buttonSelector : '.jsDeleteButton';
		
		this.proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
		
		this._initElements();
		
		WCF.DOMNodeInsertedHandler.addCallback('WCF.Action.Delete' + this._className.hashCode(), $.proxy(this._initElements, this));
	},
	
	/**
	 * Initializes available element containers.
	 */
	_initElements: function() {
		var self = this;
		$(this._containerSelector).each(function(index, container) {
			var $container = $(container);
			var $containerID = $container.wcfIdentify();
			
			if (!WCF.inArray($containerID, self._containers)) {
				self._containers.push($containerID);
				$container.find(self._buttonSelector).click($.proxy(self._click, self));
			}
		});
	},
	
	/**
	 * Sends AJAX request.
	 * 
	 * @param	object		event
	 */
	_click: function(event) {
		var $target = $(event.currentTarget);
		event.preventDefault();
		
		if ($target.data('confirmMessage')) {
			WCF.System.Confirmation.show($target.data('confirmMessage'), $.proxy(this._execute, this), { target: $target });
		}
		else {
			WCF.LoadingOverlayHandler.updateIcon($target);
			this._sendRequest($target);
		}
	},
	
	/**
	 * Executes deletion.
	 * 
	 * @param	string		action
	 * @param	object		parameters
	 */
	_execute: function(action, parameters) {
		if (action === 'cancel') {
			return;
		}
		
		WCF.LoadingOverlayHandler.updateIcon(parameters.target);
		this._sendRequest(parameters.target);
	},
	
	/**
	 * Sends the request
	 * 
	 * @param	jQuery	object
	 */
	_sendRequest: function(object) {
		this.proxy.setOption('data', {
			actionName: 'delete',
			className: this._className,
			interfaceName: 'wcf\\data\\IDeleteAction',
			objectIDs: [ $(object).data('objectID') ]
		});
		
		this.proxy.sendRequest();
	},
	
	/**
	 * Deletes items from containers.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	object		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		this.triggerEffect(data.objectIDs);
	},
	
	/**
	 * Triggers the delete effect for the objects with the given ids.
	 * 
	 * @param	array		objectIDs
	 */
	triggerEffect: function(objectIDs) {
		for (var $index in this._containers) {
			var $container = $('#' + this._containers[$index]);
			if (WCF.inArray($container.find('.jsDeleteButton').data('objectID'), objectIDs)) {
				$container.wcfBlindOut('up', function() { $(this).remove(); });
			}
		}
	}
});

/**
 * Basic implementation for deletion of nested elements.
 * 
 * The implementation requires the nested elements to be grouped as numbered lists
 * (ol lists). The child elements of the deleted elements are moved to the parent
 * element of the deleted element.
 * 
 * @see	WCF.Action.Delete
 */
WCF.Action.NestedDelete = WCF.Action.Delete.extend({
	/**
	 * @see	WCF.Action.Delete.triggerEffect()
	 */
	triggerEffect: function(objectIDs) {
		for (var $index in this._containers) {
			var $container = $('#' + this._containers[$index]);
			if (WCF.inArray($container.find(this._buttonSelector).data('objectID'), objectIDs)) {
				// move child categories up
				if ($container.has('ol').has('li')) {
					if ($container.is(':only-child')) {
						$container.parent().replaceWith($container.find('> ol'));
					}
					else {
						$container.replaceWith($container.find('> ol > li'));
					}
				}
				else {
					$container.wcfBlindOut('up', function() { $(this).remove(); });
				}
			}
		}
	}
});

/**
 * Basic implementation for AJAXProxy-based toggle actions.
 * 
 * @param	string		className
 * @param	jQuery		containerList
 * @param	string		buttonSelector
 */
WCF.Action.Toggle = Class.extend({
	/**
	 * toogle button selector
	 * @var	string
	 */
	_buttonSelector: '.jsToggleButton',
	
	/**
	 * action class name
	 * @var	string
	 */
	_className: '',
	
	/**
	 * container selector
	 * @var	string
	 */
	_containerSelector: '',
	
	/**
	 * list of known container ids
	 * @var	array<string>
	 */
	_containers: [ ],
	
	/**
	 * Initializes 'toggle'-Proxy
	 * 
	 * @param	string		className
	 * @param	string		containerSelector
	 * @param	string		buttonSelector
	 */
	init: function(className, containerSelector, buttonSelector) {
		this._containerSelector = containerSelector;
		this._className = className;
		this._buttonSelector = (buttonSelector) ? buttonSelector : '.jsToggleButton';
		
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
	_initElements: function() {
		$(this._containerSelector).each($.proxy(function(index, container) {
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
	 * @param	object		event
	 */
	_click: function(event) {
		var $target = $(event.currentTarget);
		event.preventDefault();
		
		if ($target.data('confirmMessage')) {
			WCF.System.Confirmation.show($target.data('confirmMessage'), $.proxy(this._execute, this), { target: $target });
		}
		else {
			WCF.LoadingOverlayHandler.updateIcon($target);
			this._sendRequest($target);
		}
	},
	
	/**
	 * Executes toggeling.
	 * 
	 * @param	string		action
	 * @param	object		parameters
	 */
	_execute: function(action, parameters) {
		if (action === 'cancel') {
			return;
		}
		
		WCF.LoadingOverlayHandler.updateIcon(parameters.target);
		this._sendRequest(parameters.target);
	},
	
	_sendRequest: function(object) {
		this.proxy.setOption('data', {
			actionName: 'toggle',
			className: this._className,
			interfaceName: 'wcf\\data\\IToggleAction',
			objectIDs: [ $(object).data('objectID') ]
		});
		
		this.proxy.sendRequest();
	},
	
	/**
	 * Toggles status icons.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	object		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		this.triggerEffect(data.objectIDs);
	},
	
	/**
	 * Triggers the toggle effect for the objects with the given ids.
	 * 
	 * @param	array		objectIDs
	 */
	triggerEffect: function(objectIDs) {
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
	 * Tiggers the toggle effect on a button
	 * 
	 * @param	jQuery	$container
	 * @param	jQuery	$toggleButton
	 */
	_toggleButton: function($container, $toggleButton) {
		// toggle icon source
		WCF.LoadingOverlayHandler.updateIcon($toggleButton, false);
		if ($toggleButton.hasClass('icon-check-empty')) {
			$toggleButton.removeClass('icon-check-empty').addClass('icon-check');
			$newTitle = ($toggleButton.data('disableTitle') ? $toggleButton.data('disableTitle') : WCF.Language.get('wcf.global.button.disable'));
			$toggleButton.attr('title', $newTitle);
		}
		else {
			$toggleButton.removeClass('icon-check').addClass('icon-check-empty');
			$newTitle = ($toggleButton.data('enableTitle') ? $toggleButton.data('enableTitle') : WCF.Language.get('wcf.global.button.enable'));
			$toggleButton.attr('title', $newTitle);
		}
		
		// toggle css class
		$container.toggleClass('disabled');
	}
});

/**
 * Executes provided callback if scroll threshold is reached. Usuable to determine
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
 */
WCF.Date.Picker = {
	/**
	 * date format
	 * @var	string
	 */
	_dateFormat: 'yy-mm-dd',
	
	/**
	 * time format
	 * @var	string
	 */
	_timeFormat: 'g:ia',
	
	/**
	 * Initializes the jQuery UI based date picker.
	 */
	init: function() {
		// ignore error 'unexpected literal' error; this might be not the best approach
		// to fix this problem, but since the date is properly processed anyway, we can
		// simply continue :)	- Alex
		var $__log = $.timepicker.log;
		$.timepicker.log = function(error) {
			if (error.indexOf('Error parsing the date/time string: Unexpected literal at position') == -1) {
				$__log(error);
			}
		};
		
		this._convertDateFormat();
		this._initDatePicker();
		WCF.DOMNodeInsertedHandler.addCallback('WCF.Date.Picker', $.proxy(this._initDatePicker, this));
	},
	
	/**
	 * Convert PHPs date() format to jQuery UIs date picker format.
	 */
	_convertDateFormat: function() {
		// replacement table
		// format of PHP date() => format of jQuery UI date picker
		//
		// No equivalence in PHP date():
		// oo	day of the year (three digit)
		// !	Windows ticks (100ns since 01/01/0001)
		//
		// No equivalence in jQuery UI date picker:
		// N	ISO-8601 numeric representation of the day of the week
		// w	Numeric representation of the day of the week
		// W	ISO-8601 week number of year, weeks starting on Monday
		// t	Number of days in the given month
		// L	Whether it's a leap year
		var $replacementTable = {
			// time
			'a': ' tt',
			'A': ' TT',
			'g': 'h',
			'G': 'H',
			'h': 'hh',
			'H': 'HH',
			'i': 'mm',
			's': 'ss',
			'u': 'l',
			
			// day
			'd': 'dd',
			'D': 'D',
			'j': 'd',
			'l': 'DD',
			'z': 'o',
			'S': '', // English ordinal suffix for the day of the month, 2 characters, will be discarded

			// month
			'F': 'MM',
			'm': 'mm',
			'M': 'M',
			'n': 'm',

			// year
			'o': 'yy',
			'Y': 'yy',
			'y': 'y',

			// timestamp
			'U': '@'
		};
		
		// do the actual replacement
		// this is not perfect, but a basic implementation and should work in 99% of the cases
		this._dateFormat = WCF.Language.get('wcf.date.dateFormat').replace(/([^dDjlzSFmMnoYyU\\]*(?:\\.[^dDjlzSFmMnoYyU\\]*)*)([dDjlzSFmMnoYyU])/g, function(match, part1, part2, offset, string) {
			for (var $key in $replacementTable) {
				if (part2 == $key) {
					part2 = $replacementTable[$key];
				}
			}
			
			return part1 + part2;
		});
		
		this._timeFormat = WCF.Language.get('wcf.date.timeFormat').replace(/([^aAgGhHisu\\]*(?:\\.[^aAgGhHisu\\]*)*)([aAgGhHisu])/g, function(match, part1, part2, offset, string) {
			for (var $key in $replacementTable) {
				if (part2 == $key) {
					part2 = $replacementTable[$key];
				}
			}
			
			return part1 + part2;
		});
	},
	
	/**
	 * Initializes the date picker for valid fields.
	 */
	_initDatePicker: function() {
		$('input[type=date]:not(.jsDatePicker)').each($.proxy(function(index, input) {
			var $input = $(input);
			var $inputName = $input.prop('name');
			var $inputValue = $input.val(); // should be Y-m-d, must be interpretable by Date
			
			// update $input
			$input.prop('type', 'text').addClass('jsDatePicker');
			
			// set placeholder
			if ($input.data('placeholder')) $input.attr('placeholder', $input.data('placeholder'));
			
			// insert a hidden element representing the actual date
			$input.removeAttr('name');
			$input.before('<input type="hidden" id="' + $input.wcfIdentify() + 'DatePicker" name="' + $inputName + '" value="' + $inputValue + '" />');
			
			// init date picker
			$input.datepicker({
				altField: '#' + $input.wcfIdentify() + 'DatePicker',
				altFormat: 'yy-mm-dd', // PHPs strtotime() understands this best
				beforeShow: function(input, instance) {
					// dirty hack to force opening below the input
					setTimeout(function() {
						instance.dpDiv.position({
							my: 'left top',
							at: 'left bottom',
							collision: 'none',
							of: input
						});
					}, 1);
				},
				changeMonth: true,
				changeYear: true,
				dateFormat: this._dateFormat,
				dayNames: WCF.Language.get('__days'),
				dayNamesMin: WCF.Language.get('__daysShort'),
				dayNamesShort: WCF.Language.get('__daysShort'),
				monthNames: WCF.Language.get('__months'),
				monthNamesShort: WCF.Language.get('__monthsShort'),
				showOtherMonths: true,
				yearRange: ($input.hasClass('birthday') ? '-100:+0' : '1900:2038'),
				onClose: function(dateText, datePicker) {
					// clear altField when datepicker is cleared
					if (dateText == '') {
						$(datePicker.settings["altField"]).val(dateText);
					}
				}
			});
			
			// format default date
			if ($inputValue) {
				$input.datepicker('setDate', new Date($inputValue));
			}
			
			// bug workaround: setDate creates the widget but unfortunately doesn't hide it...
			$input.datepicker('widget').hide();
		}, this));
		
		$('input[type=datetime]:not(.jsDatePicker)').each($.proxy(function(index, input) {
			var $input = $(input);
			var $inputName = $input.prop('name');
			var $inputValue = $input.val(); // should be Y-m-d H:i:s, must be interpretable by Date
			
			// drop the seconds
			if (/[0-9]{2}:[0-9]{2}:[0-9]{2}$/.test($inputValue)) {
				$inputValue = $inputValue.replace(/:[0-9]{2}$/, '');
				$input.val($inputValue);
			}
			
			// update $input
			$input.prop('type', 'text').addClass('jsDatePicker');
			
			// insert a hidden element representing the actual date
			$input.removeAttr('name');
			$input.before('<input type="hidden" id="' + $input.wcfIdentify() + 'DatePicker" name="' + $inputName + '" value="' + $inputValue + '" />');
			
			// init date picker
			$input.datetimepicker({
				altField: '#' + $input.wcfIdentify() + 'DatePicker',
				altFieldTimeOnly: false,
				altFormat: 'yy-mm-dd', // PHPs strtotime() understands this best
				altTimeFormat: 'HH:mm',
				beforeShow: function(input, instance) {
					// dirty hack to force opening below the input
					setTimeout(function() {
						instance.dpDiv.position({
							my: 'left top',
							at: 'left bottom',
							collision: 'none',
							of: input
						});
					}, 1);
				},
				changeMonth: true,
				changeYear: true,
				controlType: 'select',
				dateFormat: this._dateFormat,
				dayNames: WCF.Language.get('__days'),
				dayNamesMin: WCF.Language.get('__daysShort'),
				dayNamesShort: WCF.Language.get('__daysShort'),
				hourText: WCF.Language.get('wcf.date.hour'),
				minuteText: WCF.Language.get('wcf.date.minute'),
				monthNames: WCF.Language.get('__months'),
				monthNamesShort: WCF.Language.get('__monthsShort'),
				showButtonPanel: false,
				showTime: false,
				showOtherMonths: true,
				timeFormat: this._timeFormat,
				yearRange: ($input.hasClass('birthday') ? '-100:+0' : '1900:2038'),
				onClose: function(dateText, datePicker) {
					// clear altField when datepicker is cleared
					if (dateText == '') {
						$(datePicker.settings.altField).val('');
					}
				}
			});
			
			// format default date
			if ($inputValue) {
				$input.removeClass('hasDatepicker').datetimepicker('setDate', new Date($inputValue));
			}
			
			// bug workaround: setDate creates the widget but unfortunately doesn't hide it...
			$input.datepicker('widget').hide();
		}, this));
	}
};

/**
 * Provides utility functions for date operations.
 */
WCF.Date.Util = {
	/**
	 * Returns UTC timestamp, if date is not given, current time will be used.
	 * 
	 * @param	Date		date
	 * @return	integer
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
	 * Parameters timestamp and offset must be in miliseconds!
	 * 
	 * @param	integer		timestamp
	 * @param	integer		offset
	 * @return	Date
	 */
	getTimezoneDate: function(timestamp, offset) {
		var $date = new Date(timestamp);
		var $localOffset = $date.getTimezoneOffset() * 60000;
		
		return new Date((timestamp + $localOffset + offset));
	}
};

/**
 * Handles relative time designations.
 */
WCF.Date.Time = Class.extend({
	/**
	 * Date of current timestamp
	 * @var	Date
	 */
	_date: 0,
	
	/**
	 * list of time elements
	 * @var	jQuery
	 */
	_elements: null,
	
	/**
	 * difference between server and local time
	 * @var	integer
	 */
	_offset: null,
	
	/**
	 * current timestamp
	 * @var	integer
	 */
	_timestamp: 0,
	
	/**
	 * Initializes relative datetimes.
	 */
	init: function() {
		this._elements = $('time.datetime');
		this._offset = null;
		this._timestamp = 0;
		
		// calculate relative datetime on init
		this._refresh();
		
		// re-calculate relative datetime every minute
		new WCF.PeriodicalExecuter($.proxy(this._refresh, this), 60000);
		
		// bind dom node inserted listener
		WCF.DOMNodeInsertedHandler.addCallback('WCF.Date.Time', $.proxy(this._domNodeInserted, this));
	},
	
	/**
	 * Updates element collection once a DOM node was inserted.
	 */
	_domNodeInserted: function() {
		this._elements = $('time.datetime');
		this._refresh();
	},
	
	/**
	 * Refreshes relative datetime for each element.
	 */
	_refresh: function() {
		this._date = new Date();
		this._timestamp = (this._date.getTime() - this._date.getMilliseconds()) / 1000;
		if (this._offset === null) {
			this._offset = this._timestamp - TIME_NOW;
		}
		
		this._elements.each($.proxy(this._refreshElement, this));
	},
	
	/**
	 * Refreshes relative datetime for current element.
	 * 
	 * @param	integer		index
	 * @param	object		element
	 */
	_refreshElement: function(index, element) {
		var $element = $(element);
		
		if (!$element.attr('title')) {
			$element.attr('title', $element.text());
		}
		
		var $timestamp = $element.data('timestamp') + this._offset;
		var $date = $element.data('date');
		var $time = $element.data('time');
		var $offset = $element.data('offset');
		
		// skip for future dates
		if ($element.data('isFutureDate')) return;
		
		// timestamp is less than 60 seconds ago
		if ($timestamp >= this._timestamp || this._timestamp < ($timestamp + 60)) {
			$element.text(WCF.Language.get('wcf.date.relative.now'));
		}
		// timestamp is less than 60 minutes ago (display 1 hour ago rather than 60 minutes ago)
		else if (this._timestamp < ($timestamp + 3540)) {
			var $minutes = Math.max(Math.round((this._timestamp - $timestamp) / 60), 1);
			$element.text(WCF.Language.get('wcf.date.relative.minutes', { minutes: $minutes }));
		}
		// timestamp is less than 24 hours ago
		else if (this._timestamp < ($timestamp + 86400)) {
			var $hours = Math.round((this._timestamp - $timestamp) / 3600);
			$element.text(WCF.Language.get('wcf.date.relative.hours', { hours: $hours }));
		}
		// timestamp is less than 6 days ago
		else if (this._timestamp < ($timestamp + 518400)) {
			var $midnight = new Date(this._date.getFullYear(), this._date.getMonth(), this._date.getDate());
			var $days = Math.ceil(($midnight / 1000 - $timestamp) / 86400);
			
			// get day of week
			var $dateObj = WCF.Date.Util.getTimezoneDate(($timestamp * 1000), $offset * 1000);
			var $dow = $dateObj.getDay();
			var $day = WCF.Language.get('__days')[$dow];
			
			$element.text(WCF.Language.get('wcf.date.relative.pastDays', { days: $days, day: $day, time: $time }));
		}
		// timestamp is between ~700 million years BC and last week
		else {
			var $string = WCF.Language.get('wcf.date.dateTimeFormat');
			$element.text($string.replace(/\%date\%/, $date).replace(/\%time\%/, $time));
		}
	}
});

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

/**
 * Global language storage.
 * 
 * @see	WCF.Dictionary
 */
WCF.Language = {
	_variables: new WCF.Dictionary(),
	
	/**
	 * @see	WCF.Dictionary.add()
	 */
	add: function(key, value) {
		this._variables.add(key, value);
	},
	
	/**
	 * @see	WCF.Dictionary.addObject()
	 */
	addObject: function(object) {
		this._variables.addObject(object);
	},
	
	/**
	 * Retrieves a variable.
	 * 
	 * @param	string		key
	 * @return	mixed
	 */
	get: function(key, parameters) {
		// initialize parameters with an empty object
		if (parameters == null) var parameters = { };
		
		var value = this._variables.get(key);
		
		if (value === null) {
			// return key again
			return key;
		}
		else if (typeof value === 'string') {
			// transform strings into template and try to refetch
			this.add(key, new WCF.Template(value));
			return this.get(key, parameters);
		}
		else if (typeof value.fetch === 'function') {
			// evaluate templates
			value = value.fetch(parameters);
		}
		
		return value;
	}
};

/**
 * Handles multiple language input fields.
 * 
 * @param	string		elementID
 * @param	boolean		forceSelection
 * @param	object		values
 * @param	object		availableLanguages
 */
WCF.MultipleLanguageInput = Class.extend({
	/**
	 * list of available languages
	 * @var	object
	 */
	_availableLanguages: {},
	
	/**
	 * button element
	 * @var	jQuery
	 */
	_button: null,
	
	/**
	 * initialization state
	 * @var	boolean
	 */
	_didInit: false,
	
	/**
	 * target input element
	 * @var	jQuery
	 */
	_element: null,
	
	/**
	 * true, if data was entered after initialization
	 * @var	boolean
	 */
	_insertedDataAfterInit: false,
	
	/**
	 * enables multiple language ability
	 * @var	boolean
	 */
	_isEnabled: false,
	
	/**
	 * enforce multiple language ability
	 * @var	boolean
	 */
	_forceSelection: false,
	
	/**
	 * currently active language id
	 * @var	integer
	 */
	_languageID: 0,
	
	/**
	 * language selection list
	 * @var	jQuery
	 */
	_list: null,
	
	/**
	 * list of language values on init
	 * @var	object
	 */
	_values: null,
	
	/**
	 * Initializes multiple language ability for given element id.
	 * 
	 * @param	integer		elementID
	 * @param	boolean		forceSelection
	 * @param	boolean		isEnabled
	 * @param	object		values
	 * @param	object		availableLanguages
	 */
	init: function(elementID, forceSelection, values, availableLanguages) {
		this._button = null;
		this._element = $('#' + $.wcfEscapeID(elementID));
		this._forceSelection = forceSelection;
		this._values = values;
		this._availableLanguages = availableLanguages;
		
		// unescape values
		if ($.getLength(this._values)) {
			for (var $key in this._values) {
				this._values[$key] = WCF.String.unescapeHTML(this._values[$key]);
			}
		}
		
		// default to current user language
		this._languageID = LANGUAGE_ID;
		if (this._element.length == 0) {
			console.debug("[WCF.MultipleLanguageInput] element id '" + elementID + "' is unknown");
			return;
		}
		
		// build selection handler
		var $enableOnInit = ($.getLength(this._values) > 0) ? true : false;
		this._insertedDataAfterInit = $enableOnInit;
		this._prepareElement($enableOnInit);
		
		// listen for submit event
		this._element.parents('form').submit($.proxy(this._submit, this));
		
		this._didInit = true;
	},
	
	/**
	 * Builds language handler.
	 * 
	 * @param	boolean		enableOnInit
	 */
	_prepareElement: function(enableOnInit) {
		this._element.wrap('<div class="dropdown preInput" />');
		var $wrapper = this._element.parent();
		this._button = $('<p class="button dropdownToggle"><span>' + WCF.Language.get('wcf.global.button.disabledI18n') + '</span></p>').prependTo($wrapper);
		
		// insert list
		this._list = $('<ul class="dropdownMenu"></ul>').insertAfter(this._button);
		
		// add a special class if next item is a textarea
		if (this._button.nextAll('textarea').length) {
			this._button.addClass('dropdownCaptionTextarea');
		}
		else {
			this._button.addClass('dropdownCaption');
		}
		
		// insert available languages
		for (var $languageID in this._availableLanguages) {
			$('<li><span>' + this._availableLanguages[$languageID] + '</span></li>').data('languageID', $languageID).click($.proxy(this._changeLanguage, this)).appendTo(this._list);
		}
		
		// disable language input
		if (!this._forceSelection) {
			$('<li class="dropdownDivider" />').appendTo(this._list);
			$('<li><span>' + WCF.Language.get('wcf.global.button.disabledI18n') + '</span></li>').click($.proxy(this._disable, this)).appendTo(this._list);
		}
		
		WCF.Dropdown.initDropdown(this._button, enableOnInit);
		
		if (enableOnInit || this._forceSelection) {
			this._isEnabled = true;
			
			// pre-select current language
			this._list.children('li').each($.proxy(function(index, listItem) {
				var $listItem = $(listItem);
				if ($listItem.data('languageID') == this._languageID) {
					$listItem.trigger('click');
				}
			}, this));
		}
		
		WCF.Dropdown.registerCallback($wrapper.wcfIdentify(), $.proxy(this._handleAction, this));
	},
	
	/**
	 * Handles dropdown actions.
	 * 
	 * @param	string		containerID
	 * @param	string		action
	 */
	_handleAction: function(containerID, action) {
		if (action === 'open') {
			this._enable();
		}
		else {
			this._closeSelection();
		}
	},
	
	/**
	 * Enables the language selection or shows the selection if already enabled.
	 * 
	 * @param	object		event
	 */
	_enable: function(event) {
		if (!this._isEnabled) {
			var $button = (this._button.is('p')) ? this._button.children('span:eq(0)') : this._button;
			$button.addClass('active');
			
			this._isEnabled = true;
		}
		
		// toggle list
		if (this._list.is(':visible')) {
			this._showSelection();
		}
	},
	
	/**
	 * Shows the language selection.
	 */
	_showSelection: function() {
		if (this._isEnabled) {
			// display status for each language
			this._list.children('li').each($.proxy(function(index, listItem) {
				var $listItem = $(listItem);
				var $languageID = $listItem.data('languageID');
				
				if ($languageID) {
					if (this._values[$languageID] && this._values[$languageID] != '') {
						$listItem.removeClass('missingValue');
					}
					else {
						$listItem.addClass('missingValue');
					}
				}
			}, this));
		}
	},
	
	/**
	 * Closes the language selection.
	 */
	_closeSelection: function() {
		this._disable();
	},
	
	/**
	 * Changes the currently active language.
	 * 
	 * @param	object		event
	 */
	_changeLanguage: function(event) {
		var $button = $(event.currentTarget);
		this._insertedDataAfterInit = true;
		
		// save current value
		if (this._didInit) {
			this._values[this._languageID] = this._element.val();
		}
		
		// set new language
		this._languageID = $button.data('languageID');
		if (this._values[this._languageID]) {
			this._element.val(this._values[this._languageID]);
		}
		else {
			this._element.val('');
		}
		
		// update marking
		this._list.children('li').removeClass('active');
		$button.addClass('active');
		
		// update label
		this._button.children('span').addClass('active').text(this._availableLanguages[this._languageID]);
		
		// close selection and set focus on input element
		if (this._didInit) {
			this._element.blur().focus();
		}
	},
	
	/**
	 * Disables language selection for current element.
	 * 
	 * @param	object		event
	 */
	_disable: function(event) {
		if (event === undefined && this._insertedDataAfterInit) {
			event = null;
		}
		
		if (this._forceSelection || !this._list || event === null) {
			return;
		}
		
		// remove active marking
		this._button.children('span').removeClass('active').text(WCF.Language.get('wcf.global.button.disabledI18n'));
		
		// update element value
		if (this._values[LANGUAGE_ID]) {
			this._element.val(this._values[LANGUAGE_ID]);
		}
		else {
			// no value for current language found, proceed with empty input
			this._element.val();
		}
		
		this._element.blur();
		this._insertedDataAfterInit = false;
		this._isEnabled = false;
		this._values = { };
	},
	
	/**
	 * Prepares language variables on before submit.
	 */
	_submit: function() {
		// insert hidden form elements on before submit
		if (!this._isEnabled) {
			return 0xDEADBEEF;
		}
		
		// fetch active value
		if (this._languageID) {
			this._values[this._languageID] = this._element.val();
		}
		
		var $form = $(this._element.parents('form')[0]);
		var $elementID = this._element.wcfIdentify();
		
		for (var $languageID in this._availableLanguages) {
			if (this._values[$languageID] === undefined) {
				this._values[$languageID] = '';
			}
			
			$('<input type="hidden" name="' + $elementID + '_i18n[' + $languageID + ']" value="' + WCF.String.escapeHTML(this._values[$languageID]) + '" />').appendTo($form);
		}
		
		// remove name attribute to prevent conflict with i18n values
		this._element.removeAttr('name');
	}
});

/**
 * Number utilities.
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
		number = number.replace('.', WCF.Language.get('wcf.global.decimalPoint'));
		
		number = this.addThousandsSeparator(number);
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
	 * list of tabmenu containers
	 * @var	object
	 */
	_containers: { },
	
	/**
	 * initialization state
	 * @var	boolean
	 */
	_didInit: false,
	
	/**
	 * Initializes all TabMenus
	 */
	init: function() {
		var $containers = $('.tabMenuContainer');
		var self = this;
		$containers.each(function(index, tabMenu) {
			var $tabMenu = $(tabMenu);
			var $containerID = $tabMenu.wcfIdentify();
			if (self._containers[$containerID]) {
				// continue with next container
				return true;
			}
			
			if ($tabMenu.data('store') && !$('#' + $tabMenu.data('store')).length) {
				$('<input type="hidden" name="' + $tabMenu.data('store') + '" value="" id="' + $tabMenu.data('store') + '" />').appendTo($tabMenu.parents('form').find('.formSubmit'));
			}
			
			// init jQuery UI TabMenu
			self._containers[$containerID] = $tabMenu;
			$tabMenu.wcfTabs({
				active: false,
				activate: function(event, eventData) {
					var $panel = $(eventData.newPanel);
					var $container = $panel.closest('.tabMenuContainer');
					
					// store currently selected item
					var $tabMenu = $container;
					while (true) {
						// do not trigger on init
						if ($tabMenu.data('isParent') === undefined) {
							break;
						}
						
						if ($tabMenu.data('isParent')) {
							if ($tabMenu.data('store')) {
								$('#' + $tabMenu.data('store')).val($panel.attr('id'));
							}
							
							break;
						}
						else {
							$tabMenu = $tabMenu.data('parent');
						}
					}
					
					// set panel id as location hash
					if (WCF.TabMenu._didInit) {
						if (window.history) {
							window.history.pushState(null, document.title, window.location.toString().replace(/#.+$/, '') + '#' + $panel.attr('id'));
						}
						else {
							location.hash = '#' + $panel.attr('id');
						}
					}
					
					//$container.trigger('tabsbeforeactivate', event, eventData);
				}
			});
			
			$tabMenu.data('isParent', ($tabMenu.children('.tabMenuContainer, .tabMenuContent').length > 0)).data('parent', false);
			if (!$tabMenu.data('isParent')) {
				// check if we're a child element
				if ($tabMenu.parent().hasClass('tabMenuContainer')) {
					$tabMenu.data('parent', $tabMenu.parent());
				}
			}
		});
		
		// try to resolve location hash
		if (!this._didInit) {
			this._selectActiveTab();
			$(window).bind('hashchange', $.proxy(this.selectTabs, this));
			
			if (!this._selectErroneousTab()) {
				this.selectTabs();
			}
		}
		
		this._didInit = true;
	},
	
	/**
	 * Reloads the tab menus.
	 */
	reload: function() {
		this._containers = { };
		this.init();
	},
	
	/**
	 * Force display of first erroneous tab and returns true if at least one
	 * tab contains an error.
	 * 
	 * @return	boolean
	 */
	_selectErroneousTab: function() {
		for (var $containerID in this._containers) {
			var $tabMenu = this._containers[$containerID];
			
			if (!$tabMenu.data('isParent') && $tabMenu.find('.formError').length) {
				while (true) {
					if ($tabMenu.data('parent') === false) {
						break;
					}
					
					$tabMenu = $tabMenu.data('parent').wcfTabs('selectTab', $tabMenu.wcfIdentify());
				}
				
				return true;
			}
		}
		
		return false;
	},
	
	/**
	 * Selects the active tab menu item.
	 */
	_selectActiveTab: function() {
		for (var $containerID in this._containers) {
			var $tabMenu = this._containers[$containerID];
			if ($tabMenu.data('active')) {
				var $index = $tabMenu.data('active');
				var $subIndex = null;
				if (/-/.test($index)) {
					var $tmp = $index.split('-');
					$index = $tmp[0];
					$subIndex = $tmp[1];
				}
				
				$tabMenu.find('.tabMenuContent').each(function(innerIndex, tabMenuItem) {
					var $tabMenuItem = $(tabMenuItem);
					if ($tabMenuItem.wcfIdentify() == $index) {
						$tabMenu.wcfTabs('select', innerIndex);
						if ($subIndex !== null) {
							if ($tabMenuItem.hasClass('tabMenuContainer')) {
								$tabMenuItem.wcfTabs('selectTab', $tabMenu.data('active'));
							}
							else {
								$tabMenu.wcfTabs('selectTab', $tabMenu.data('active'));
							}
						}
						
						return false;
					}
				});
			}
		}
	},
	
	/**
	 * Resolves location hash to display tab menus.
	 * 
	 * @return	boolean
	 */
	selectTabs: function() {
		if (location.hash) {
			var $hash = location.hash.substr(1);
			
			// try to find matching tab menu container
			var $tabMenu = $('#' + $.wcfEscapeID($hash));
			if ($tabMenu.length === 1 && $tabMenu.hasClass('ui-tabs-panel')) {
				$tabMenu = $tabMenu.parent('.ui-tabs');
				if ($tabMenu.length) {
					$tabMenu.wcfTabs('selectTab', $hash);
					
					// check if this is a nested tab menu
					if ($tabMenu.hasClass('ui-tabs-panel')) {
						$hash = $tabMenu.wcfIdentify();
						$tabMenu = $tabMenu.parent('.ui-tabs');
						if ($tabMenu.length) {
							$tabMenu.wcfTabs('selectTab', $hash);
						}
					}
					
					return true;
				}
			}
		}
		
		return false;
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
				if ($inName && $char != '=' && $char != ' ') $name += $char
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
			content = unescape(content.replace(/\$([^.\[\s]+)/g, "(v['$1'])"));
			
			return "' + WCF.String.escapeHTML(" + content + ") + '";
		})
		// Numeric Variable
		.replace(/\{#(\$[^\}]+?)\}/g, function(_, content) {
			content = unescape(content.replace(/\$([^.\[\s]+)/g, "(v['$1'])"));
			
			return "' + WCF.String.formatNumeric(" + content + ") + '";
		})
		// Variable without escaping
		.replace(/\{@(\$[^\}]+?)\}/g, function(_, content) {
			content = unescape(content.replace(/\$([^.\[\s]+)/g, "(v['$1'])"));
			
			return "' + " + content + " + '";
		})
		// {if}
		.replace(/\{if (.+?)\}/g, function(_, content) {
			content = unescape(content.replace(/\$([^.\[\s]+)/g, "(v['$1'])"));
			
			return	"';\n" +
				"if (" + content + ") {\n" +
				"	$output += '";
		})
		// {elseif}
		.replace(/\{else ?if (.+?)\}/g, function(_, content) {
			content = unescape(content.replace(/\$([^.\[\s]+)/g, "(v['$1'])"));
			
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
			
			$parameters['from'] = $parameters['from'].replace(/\$([^.\[\s]+)/g, "(v.$1)");
			
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
			$parameters['from'] = $parameters['from'].replace(/\$([^.\[\s]+)/g, "(v.$1)");
			
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
			this.fetch = new Function("v", "if (typeof v != 'object') { v = {}; } v.__window = window; v.__wcf = window.WCF; var $output = ''; " + template + ' return $output;');
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
			$icon.removeClass('icon-chevron-right').addClass('icon-chevron-down');
		}
		else {
			$icon.removeClass('icon-chevron-down').addClass('icon-chevron-right');
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
		var $isOpen = this._containers[containerID].data('isOpen');
		var $button = $('<span class="collapsibleButton jsTooltip pointer icon icon16 icon-' + ($isOpen ? 'chevron-down' : 'chevron-right') + '" title="'+WCF.Language.get('wcf.global.button.collapsible')+'">').prependTo(buttonContainer);
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
		button.removeClass('icon-chevron-down icon-chevron-right icon-spinner').addClass('icon-' + newIcon);
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
	 * Sets content upon successfull AJAX request.
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
		this._exchangeIcon(this._containerData[$containerID].button, (data.returnValues.isOpen ? 'chevron-down' : 'chevron-right'));
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
 * Provides collapsible sidebars with persistency support.
 */
WCF.Collapsible.Sidebar = Class.extend({
	/**
	 * trigger button object
	 * @var	jQuery
	 */
	_button: null,
	
	/**
	 * trigger button height
	 * @var	integer
	 */
	_buttonHeight: 0,
	
	/**
	 * sidebar state
	 * @var	boolean
	 */
	_isOpen: false,
	
	/**
	 * main container object
	 * @var	jQuery
	 */
	_mainContainer: null,
	
	/**
	 * action proxy
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * sidebar object
	 * @var	jQuery
	 */
	_sidebar: null,
	
	/**
	 * sidebar height
	 * @var	integer
	 */
	_sidebarHeight: 0,
	
	/**
	 * sidebar identifier
	 * @var	string
	 */
	_sidebarName: '',
	
	/**
	 * sidebar offset from document top
	 * @var	integer
	 */
	_sidebarOffset: 0,
	
	/**
	 * user panel height
	 * @var	integer
	 */
	_userPanelHeight: 0,
	
	/**
	 * Creates a new WCF.Collapsible.Sidebar object.
	 */
	init: function() {
		this._sidebar = $('.sidebar:eq(0)');
		if (!this._sidebar.length) {
			console.debug("[WCF.Collapsible.Sidebar] Could not find sidebar, aborting.");
			return;
		}
		
		this._isOpen = (this._sidebar.data('isOpen')) ? true : false;
		this._sidebarName = this._sidebar.data('sidebarName');
		this._mainContainer = $('#main');
		this._sidebarHeight = this._sidebar.height();
		this._sidebarOffset = this._sidebar.getOffsets('offset').top;
		this._userPanelHeight = $('#topMenu').outerHeight();
		
		// add toggle button
		this._button = $('<a class="collapsibleButton jsTooltip" title="' + WCF.Language.get('wcf.global.button.collapsible') + '" />').prependTo(this._sidebar);
		this._button.wrap('<span />');
		this._button.click($.proxy(this._click, this));
		this._buttonHeight = this._button.outerHeight();
		
		WCF.DOMNodeInsertedHandler.execute();
		
		this._proxy = new WCF.Action.Proxy({
			showLoadingOverlay: false,
			url: 'index.php/AJAXInvoke/?t=' + SECURITY_TOKEN + SID_ARG_2ND
		});
		
		$(document).scroll($.proxy(this._scroll, this)).resize($.proxy(this._scroll, this));
		
		this._renderSidebar();
		this._scroll();
	},
	
	/**
	 * Handles clicks on the trigger button.
	 */
	_click: function() {
		this._isOpen = (this._isOpen) ? false : true;
		
		this._proxy.setOption('data', {
			actionName: 'toggle',
			className: 'wcf\\system\\user\\collapsible\\content\\UserCollapsibleSidebarHandler',
			isOpen: (this._isOpen ? 1 : 0),
			sidebarName: this._sidebarName
		});
		this._proxy.sendRequest();
		
		this._renderSidebar();
	},
	
	/**
	 * Aligns the toggle button upon scroll or resize.
	 */
	_scroll: function() {
		var $window = $(window);
		var $scrollOffset = $window.scrollTop();
		
		// calculate top and bottom coordinates of visible sidebar
		var $topOffset = Math.max($scrollOffset - this._sidebarOffset, 0);
		var $bottomOffset = Math.min(this._mainContainer.height(), ($window.height() + $scrollOffset) - this._sidebarOffset);
		
		var $buttonTop = 0;
		if ($bottomOffset === $topOffset) {
			// sidebar not within visible area
			$buttonTop = this._sidebarOffset + this._sidebarHeight;
		}
		else {
			$buttonTop = $topOffset + (($bottomOffset - $topOffset) / 2);
			
			// if the user panel is above the sidebar, substract it's height
			var $overlap = Math.max(Math.min($topOffset - this._userPanelHeight, this._userPanelHeight), 0);
			if ($overlap > 0) {
				$buttonTop += ($overlap / 2);
			}
		}
		
		// ensure the button does not exceed bottom boundaries
		if (($bottomOffset - $topOffset - this._userPanelHeight) < this._buttonHeight) {
			$buttonTop = $buttonTop - this._buttonHeight;
		}
		else {
			// exclude half button height
			$buttonTop = Math.max($buttonTop - (this._buttonHeight / 2), 0);
		}
		
		this._button.css({ top: $buttonTop + 'px' });
		
	},
	
	/**
	 * Renders the sidebar state.
	 */
	_renderSidebar: function() {
		if (this._isOpen) {
			$('.sidebarOrientationLeft, .sidebarOrientationRight').removeClass('sidebarCollapsed');
		}
		else {
			$('.sidebarOrientationLeft, .sidebarOrientationRight').addClass('sidebarCollapsed');
		}
		
		// update button position
		this._scroll();
	}
});

/**
 * Holds userdata of the current user
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
		
		// handles menu height
		/*if (excludeMenuHeight) {
			$elementOffset = Math.max($elementOffset - $('#topMenu').outerHeight(), 0);
		}*/
		
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
 * Creates a smooth scroll effect.
 */
WCF.Effect.SmoothScroll = WCF.Effect.Scroll.extend({
	/**
	 * Initializes effect.
	 */
	init: function() {
		var self = this;
		$(document).on('click', 'a[href$=#top],a[href$=#bottom]', function() {
			var $target = $(this.hash);
			self.scrollTo($target, true);
			
			return false;
		});
	}
});

/**
 * Creates the balloon tool-tip.
 */
WCF.Effect.BalloonTooltip = Class.extend({
	/**
	 * initialization state
	 * @var	boolean
	 */
	_didInit: false,
	
	/**
	 * tooltip element
	 * @var	jQuery
	 */
	_tooltip: null,
	
	/**
	 * cache viewport dimensions
	 * @var	object
	 */
	_viewportDimensions: { },
	
	/**
	 * Initializes tooltips.
	 */
	init: function() {
		if (jQuery.browser.mobile) return;
		
		if (!this._didInit) {
			// create empty div
			this._tooltip = $('<div id="balloonTooltip" class="balloonTooltip"><span id="balloonTooltipText"></span><span class="pointer"><span></span></span></div>').appendTo($('body')).hide();
			
			// get viewport dimensions
			this._updateViewportDimensions();
			
			// update viewport dimensions on resize
			$(window).resize($.proxy(this._updateViewportDimensions, this));
			
			// observe DOM changes
			WCF.DOMNodeInsertedHandler.addCallback('WCF.Effect.BalloonTooltip', $.proxy(this.init, this));
			
			this._didInit = true;
		}
		
		// init elements
		$('.jsTooltip').each($.proxy(this._initTooltip, this));
	},
	
	/**
	 * Updates cached viewport dimensions.
	 */
	_updateViewportDimensions: function() {
		this._viewportDimensions = $(document).getDimensions();
	},
	
	/**
	 * Initializes a tooltip element.
	 * 
	 * @param	integer		index
	 * @param	object		element
	 */
	_initTooltip: function(index, element) {
		var $element = $(element);
		
		if ($element.hasClass('jsTooltip')) {
			$element.removeClass('jsTooltip');
			var $title = $element.attr('title');
			
			// ignore empty elements
			if ($title !== '') {
				$element.data('tooltip', $title);
				$element.removeAttr('title');
				
				$element.hover(
					$.proxy(this._mouseEnterHandler, this),
					$.proxy(this._mouseLeaveHandler, this)
				);
				$element.click($.proxy(this._mouseLeaveHandler, this));
			}
		}
	},
	
	/**
	 * Shows tooltip on hover.
	 * 
	 * @param	object		event
	 */
	_mouseEnterHandler: function(event) {
		var $element = $(event.currentTarget);
		
		var $title = $element.attr('title');
		if ($title && $title !== '') {
			$element.data('tooltip', $title);
			$element.removeAttr('title');
		}
		
		// reset tooltip position
		this._tooltip.css({
			top: "0px",
			left: "0px"
		});
		
		// empty tooltip, skip
		if (!$element.data('tooltip')) {
			this._tooltip.hide();
			return;
		}
		
		// update text
		this._tooltip.children('span:eq(0)').text($element.data('tooltip'));
		
		// get arrow
		var $arrow = this._tooltip.find('.pointer');
		
		// get arrow width
		this._tooltip.show();
		var $arrowWidth = $arrow.outerWidth();
		this._tooltip.hide();
		
		// calculate position
		var $elementOffsets = $element.getOffsets('offset');
		var $elementDimensions = $element.getDimensions('outer');
		var $tooltipDimensions = this._tooltip.getDimensions('outer');
		var $tooltipDimensionsInner = this._tooltip.getDimensions('inner');
		
		var $elementCenter = $elementOffsets.left + Math.ceil($elementDimensions.width / 2);
		var $tooltipHalfWidth = Math.ceil($tooltipDimensions.width / 2);
		
		// determine alignment
		var $alignment = 'center';
		if (($elementCenter - $tooltipHalfWidth) < 5) {
			$alignment = 'left';
		}
		else if ((this._viewportDimensions.width - 5) < ($elementCenter + $tooltipHalfWidth)) {
			$alignment = 'right';
		}
		
		// calculate top offset
		if ($elementOffsets.top + $elementDimensions.height + $tooltipDimensions.height - $(document).scrollTop() < $(window).height()) {
			var $top = $elementOffsets.top + $elementDimensions.height + 7;
			this._tooltip.removeClass('inverse');
			$arrow.css('top', -5);
		}
		else {
			var $top = $elementOffsets.top - $tooltipDimensions.height - 7;
			this._tooltip.addClass('inverse');
			$arrow.css('top', $tooltipDimensions.height);
		}
		
		// calculate left offset
		switch ($alignment) {
			case 'center':
				var $left = Math.round($elementOffsets.left - $tooltipHalfWidth + ($elementDimensions.width / 2));
				
				$arrow.css({
					left: ($tooltipDimensionsInner.width / 2 - $arrowWidth / 2) + "px"
				});
			break;
			
			case 'left':
				var $left = $elementOffsets.left;
				
				$arrow.css({
					left: "5px"
				});
			break;
			
			case 'right':
				var $left = $elementOffsets.left + $elementDimensions.width - $tooltipDimensions.width;
				
				$arrow.css({
					left: ($tooltipDimensionsInner.width - $arrowWidth - 5) + "px"
				});
			break;
		}
		
		// move tooltip
		this._tooltip.css({
			top: $top + "px",
			left: $left + "px"
		});
		
		// show tooltip
		this._tooltip.wcfFadeIn();
	},
	
	/**
	 * Hides tooltip once cursor left the element.
	 * 
	 * @param	object		event
	 */
	_mouseLeaveHandler: function(event) {
		this._tooltip.stop().hide().css({
			opacity: 1
		});
	}
});

/**
 * Handles clicks outside an overlay, hitting body-tag through bubbling.
 * 
 * You should always remove callbacks before disposing the attached element,
 * preventing errors from blocking the iteration. Furthermore you should
 * always handle clicks on your overlay's container and return 'false' to
 * prevent bubbling.
 */
WCF.CloseOverlayHandler = {
	/**
	 * list of callbacks
	 * @var	WCF.Dictionary
	 */
	_callbacks: new WCF.Dictionary(),
	
	/**
	 * indicates that overlay handler is listening to click events on body-tag
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
			console.debug("[WCF.CloseOverlayHandler] identifier '" + identifier + "' is already bound to a callback");
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
		
		$('body').click($.proxy(this._executeCallbacks, this));
		
		this._isListening = true;
	},
	
	/**
	 * Executes callbacks on click.
	 */
	_executeCallbacks: function(event) {
		this._callbacks.each(function(pair) {
			// execute callback
			pair.value();
		});
	}
};

/**
 * Notifies objects once a DOM node was inserted.
 */
WCF.DOMNodeInsertedHandler = {
	/**
	 * list of callbacks
	 * @var	array<object>
	 */
	_callbacks: [ ],
	
	/**
	 * prevent infinite loop if a callback manipulates DOM
	 * @var	boolean
	 */
	_isExecuting: false,
	
	/**
	 * Adds a new callback.
	 * 
	 * @param	string		identifier
	 * @param	object		callback
	 */
	addCallback: function(identifier, callback) {
		this._callbacks.push(callback);
	},
	
	/**
	 * Executes callbacks on click.
	 */
	_executeCallbacks: function() {
		if (this._isExecuting) return;
		
		// do not track events while executing callbacks
		this._isExecuting = true;
		
		for (var $i = 0, $length = this._callbacks.length; $i < $length; $i++) {
			this._callbacks[$i]();
		}
		
		// enable listener again
		this._isExecuting = false;
	},
	
	/**
	 * Executes all callbacks.
	 */
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
		
		$(document).bind('DOMNodeRemoved', $.proxy(this._executeCallbacks, this));
		
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
 * Namespace for table related classes.
 */
WCF.Table = {};

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
	 * Initalizes a new WCF.Table.EmptyTableHandler object.
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
			messageType: 'info',
			refreshPage: false,
			updatePageNumber: false
		}, options || { });
		
		WCF.DOMNodeRemovedHandler.addCallback('WCF.Table.EmptyTableHandler.' + rowClassName, $.proxy(this._remove, this));
	},
	
	/**
	 * Handles the removal of a DOM node.
	 */
	_remove: function(event) {
		var element = $(event.target);
		
		// check if DOM element is relevant
		if (element.hasClass(this._rowClassName)) {
			var tbody = element.parents('tbody:eq(0)');
			
			// check if table will be empty if DOM node is removed
			if (tbody.children('tr').length == 1) {
				if (this._options.emptyMessage) {
					// insert message
					this._tableContainer.replaceWith($('<p />').addClass(this._options.messageType).text(this._options.emptyMessage));
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
			}
		}
	}
});

/**
 * Namespace for search related classes.
 */
WCF.Search = {};

/**
 * Performs a quick search.
 */
WCF.Search.Base = Class.extend({
	/**
	 * notification callback
	 * @var	object
	 */
	_callback: null,
	
	/**
	 * class name
	 * @var	string
	 */
	_className: '',
	
	/**
	 * comma seperated list
	 * @var	boolean
	 */
	_commaSeperated: false,
	
	/**
	 * list with values that are excluded from seaching
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
		this._excludedSearchValues = [];
		if (excludedSearchValues) {
			this._excludedSearchValues = excludedSearchValues;
		}
		
		this._searchInput = $(searchInput);
		if (!this._searchInput.length) {
			console.debug("[WCF.Search.Base] Selector '" + searchInput + "' for search input is invalid, aborting.");
			return;
		}
		
		this._searchInput.keydown($.proxy(this._keyDown, this)).keyup($.proxy(this._keyUp, this)).wrap('<span class="dropdown" />');
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
		if (event.which === 13) {
			if (this._searchInput.parents('.dropdown').data('disableAutoFocus') && this._itemIndex === -1) {
				// allow submitting
			}
			else {
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
			break;
			
			case 38: // arrow up
				this._selectPreviousItem();
				return;
			break;
			
			case 40: // arrow down
				this._selectNextItem();
				return;
			break;
			
			case 13: // return key
				return this._selectElement(event);
			break;
		}
		
		var $content = this._getSearchString(event);
		if ($content === '') {
			this._clearList(true);
		}
		else if ($content.length >= this._triggerLength) {
			var $parameters = {
				data: {
					excludedSearchValues: this._excludedSearchValues,
					searchString: $content
				}
			};
			
			this._proxy.setOption('data', {
				actionName: 'getSearchResultList',
				className: this._className,
				interfaceName: 'wcf\\data\\ISearchAction',
				parameters: this._getParameters($parameters)
			});
			this._proxy.sendRequest();
		}
		else {
			// input below trigger length
			this._clearList(false);
		}
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
			if ($keyCode == 188) {
				// ignore event if char is 188 = ,
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
			WCF.Dropdown.toggleDropdown($containerID);
		}
		
		// pre-select first item
		this._itemIndex = -1;
		if (!WCF.Dropdown.getDropdown($containerID).data('disableAutoFocus')) {
			this._selectNextItem();
		}
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
			for (var $i = 0, $length = this._oldSearchString.length; $i < $length; $i++) {
				var $part = this._oldSearchString[$i];
				if ($result.toLowerCase().indexOf($part.toLowerCase()) === 0) {
					this._oldSearchString[$i] = $result;
					this._searchInput.val(this._oldSearchString.join(', '));
					
					if ($.browser.webkit) {
						// chrome won't display the new value until the textarea is rendered again
						// this quick fix forces chrome to render it again, even though it changes nothing
						this._searchInput.css({ display: 'block' });
					}
					
					// set focus on input field again
					var $position = this._searchInput.val().toLowerCase().indexOf($result.toLowerCase()) + $result.length;
					this._searchInput.focus().setCaret($position);
					
					break;
				}
			}
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
	 * Adds an excluded search value.
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
			$icon = $('<span class="icon icon16 icon-group" />');
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
 * Enables mobile navigation.
 */
WCF.System.MobileNavigation = {
	init: function() {
		this._convertNavigation();
		
		WCF.DOMNodeInsertedHandler.addCallback('WCF.System.MobileNavigation', function() {
			WCF.System.MobileNavigation._convertNavigation();
		});
	},
	
	_convertNavigation: function() {
		$('nav.jsMobileNavigation').removeClass('jsMobileNavigation').each(function(index, navigation) {
			var $navigation = $(navigation);
			$('<a class="invisible" tabindex="9999999" />').click(function() {}).prependTo($navigation);
			$('<a class="invisible" tabindex="9999999"><span class="icon icon16 icon-list" />' + ($navigation.data('buttonLabel') !== undefined ? (' ' + $navigation.data('buttonLabel')) : '') + '</a>').click(function() {}).prependTo($navigation);
		});
	}
};

/**
 * System notification overlays.
 * 
 * @param	string		message
 * @param	string		cssClassNames
 */
WCF.System.Notification = Class.extend({
	/**
	 * callback on notification close
	 * @var	object
	 */
	_callback: null,
	
	/**
	 * CSS class names
	 * @var	string
	 */
	_cssClassNames: '',
	
	/**
	 * notification message
	 * @var	string
	 */
	_message: '',
	
	/**
	 * notification overlay
	 * @var	jQuery
	 */
	_overlay: null,
	
	/**
	 * Creates a new system notification overlay.
	 * 
	 * @param	string		message
	 * @param	string		cssClassNames
	 */
	init: function(message, cssClassNames) {
		this._cssClassNames = cssClassNames || 'success';
		this._message = message || WCF.Language.get('wcf.global.success');
		this._overlay = $('#systemNotification');
		
		if (!this._overlay.length) {
			this._overlay = $('<div id="systemNotification"><p></p></div>').hide().appendTo(document.body);
		}
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
		duration = parseInt(duration);
		if (!duration) duration = 2000;
		
		if (callback && $.isFunction(callback)) {
			this._callback = callback;
		}
		
		this._overlay.children('p').html((message || this._message));
		this._overlay.children('p').removeClass().addClass((cssClassNames || this._cssClassNames));
		
		// hide overlay after specified duration
		new WCF.PeriodicalExecuter($.proxy(this._hide, this), duration);
		
		this._overlay.wcfFadeIn(undefined, 300);
	},
	
	/**
	 * Hides the notification overlay after executing the callback.
	 * 
	 * @param	WCF.PeriodicalExecuter		pe
	 */
	_hide: function(pe) {
		if (this._callback !== null) {
			this._callback();
		}
		
		this._overlay.wcfFadeOut(undefined, 300);
		
		pe.stop();
	}
});

/**
 * Provides dialog-based confirmations.
 */
WCF.System.Confirmation = {
	/**
	 * notification callback
	 * @var	object
	 */
	_callback: null,
	
	/**
	 * confirmation dialog
	 * @var	jQuery
	 */
	_dialog: null,
	
	/**
	 * callback parameters
	 * @var	object
	 */
	_parameters: null,
	
	/**
	 * dialog visibility
	 * @var	boolean
	 */
	_visible: false,
	
	/**
	 * confirmation button
	 * @var	jQuery
	 */
	_confirmationButton: null,
	
	/**
	 * Displays a confirmation dialog.
	 * 
	 * @param	string		message
	 * @param	object		callback
	 * @param	object		parameters
	 * @param	jQuery		template
	 */
	show: function(message, callback, parameters, template) {
		if (this._visible) {
			console.debug('[WCF.System.Confirmation] Confirmation dialog is already open, refusing action.');
			return;
		}
		
		if (!$.isFunction(callback)) {
			console.debug('[WCF.System.Confirmation] Given callback is invalid, aborting.');
			return;
		}
		
		this._callback = callback;
		this._parameters = parameters;
		
		var $render = true;
		if (this._dialog === null) {
			this._createDialog();
			$render = false;
		}
		
		this._dialog.find('#wcfSystemConfirmationContent').empty().hide();
		if (template && template.length) {
			template.appendTo(this._dialog.find('#wcfSystemConfirmationContent').show());
		}
		
		this._dialog.find('p').text(message);
		this._dialog.wcfDialog({
			onClose: $.proxy(this._close, this),
			onShow: $.proxy(this._show, this),
			title: WCF.Language.get('wcf.global.confirmation.title')
		});
		if ($render) {
			this._dialog.wcfDialog('render');
		}
		
		this._confirmationButton.focus();
		this._visible = true;
	},
	
	/**
	 * Creates the confirmation dialog on first use.
	 */
	_createDialog: function() {
		this._dialog = $('<div id="wcfSystemConfirmation" class="systemConfirmation"><p /><div id="wcfSystemConfirmationContent" /></div>').hide().appendTo(document.body);
		var $formButtons = $('<div class="formSubmit" />').appendTo(this._dialog);
		
		this._confirmationButton = $('<button class="buttonPrimary">' + WCF.Language.get('wcf.global.confirmation.confirm') + '</button>').data('action', 'confirm').click($.proxy(this._click, this)).appendTo($formButtons);
		$('<button>' + WCF.Language.get('wcf.global.confirmation.cancel') + '</button>').data('action', 'cancel').click($.proxy(this._click, this)).appendTo($formButtons);
	},
	
	/**
	 * Handles button clicks.
	 * 
	 * @param	object		event
	 */
	_click: function(event) {
		this._notify($(event.currentTarget).data('action'));
	},
	
	/**
	 * Handles dialog being closed.
	 */
	_close: function() {
		if (this._visible) {
			this._notify('cancel');
		}
	},
	
	/**
	 * Notifies callback upon user's decision.
	 * 
	 * @param	string		action
	 */
	_notify: function(action) {
		this._visible = false;
		this._dialog.wcfDialog('close');
		
		this._callback(action, this._parameters);
	},
	
	/**
	 * Tries to set focus on confirm button.
	 */
	_show: function() {
		this._dialog.find('button.buttonPrimary').blur().focus();
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
 * Provides the 'jump to page' overlay.
 */
WCF.System.PageNavigation = {
	/**
	 * submit button
	 * @var	jQuery
	 */
	_button: null,
	
	/**
	 * page No description
	 * @var	jQuery
	 */
	_description: null,
	
	/**
	 * dialog overlay
	 * @var	jQuery
	 */
	_dialog: null,
	
	/**
	 * active element id
	 * @var	string
	 */
	_elementID: '',
	
	/**
	 * list of tracked navigation bars
	 * @var	object
	 */
	_elements: { },
	
	/**
	 * page No input
	 * @var	jQuery
	 */
	_pageNo: null,
	
	/**
	 * Initializes the 'jump to page' overlay for given selector.
	 * 
	 * @param	string		selector
	 * @param	object		callback
	 */
	init: function(selector, callback) {
		var $elements = $(selector);
		if (!$elements.length) {
			return;
		}
		
		callback = callback || null;
		if (callback !== null && !$.isFunction(callback)) {
			console.debug("[WCF.System.PageNavigation] Callback for selector '" + selector + "' is invalid, aborting.");
			return;
		}
		
		this._initElements($elements, callback);
	},
	
	/**
	 * Initializes the 'jump to page' overlay for given elements.
	 * 
	 * @param	jQuery		elements
	 * @param	object		callback
	 */
	_initElements: function(elements, callback) {
		var self = this;
		elements.each(function(index, element) {
			var $element = $(element);
			var $elementID = $element.wcfIdentify();
			
			if (self._elements[$elementID] === undefined) {
				self._elements[$elementID] = $element;
				$element.find('li.jumpTo').data('elementID', $elementID).click($.proxy(self._click, self));
			}
		}).data('callback', callback);
	},
	
	/**
	 * Shows the 'jump to page' overlay.
	 * 
	 * @param	object		event
	 */
	_click: function(event) {
		this._elementID = $(event.currentTarget).data('elementID');
		
		if (this._dialog === null) {
			this._dialog = $('<div id="pageNavigationOverlay" />').hide().appendTo(document.body);
			
			var $fieldset = $('<fieldset><legend>' + WCF.Language.get('wcf.global.page.jumpTo') + '</legend></fieldset>').appendTo(this._dialog);
			$('<dl><dt><label for="jsPageNavigationPageNo">' + WCF.Language.get('wcf.global.page.jumpTo') + '</label></dt><dd></dd></dl>').appendTo($fieldset);
			this._pageNo = $('<input type="number" id="jsPageNavigationPageNo" value="1" min="1" max="1" class="tiny" />').keyup($.proxy(this._keyUp, this)).appendTo($fieldset.find('dd'));
			this._description = $('<small></small>').insertAfter(this._pageNo);
			var $formSubmit = $('<div class="formSubmit" />').appendTo(this._dialog);
			this._button = $('<button class="buttonPrimary">' + WCF.Language.get('wcf.global.button.submit') + '</button>').click($.proxy(this._submit, this)).appendTo($formSubmit);
		}
		
		this._button.enable();
		this._description.html(WCF.Language.get('wcf.global.page.jumpTo.description').replace(/#pages#/, this._elements[this._elementID].data('pages')));
		this._pageNo.val(this._elements[this._elementID].data('pages')).attr('max', this._elements[this._elementID].data('pages'));
		
		this._dialog.wcfDialog({
			'title': WCF.Language.get('wcf.global.page.pageNavigation')
		});
	},
	
	/**
	 * Validates the page No input.
	 */
	_keyUp: function() {
		var $pageNo = parseInt(this._pageNo.val()) || 0;
		if ($pageNo < 1 || $pageNo > this._pageNo.attr('max')) {
			this._button.disable();
		}
		else {
			this._button.enable();
		}
	},
	
	/**
	 * Redirects to given page No.
	 */
	_submit: function() {
		var $pageNavigation = this._elements[this._elementID];
		if ($pageNavigation.data('callback') === null) {
			var $redirectURL = $pageNavigation.data('link').replace(/pageNo=%d/, 'pageNo=' + this._pageNo.val());
			window.location = $redirectURL;
		}
		else {
			$pageNavigation.data('callback')(this._pageNo.val());
			this._dialog.wcfDialog('close');
		}
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
				suppressErrors: true
			});
		}, (seconds * 1000));
	}
});

/**
 * Default implementation for inline editors.
 * 
 * @param	string		elementSelector
 */
WCF.InlineEditor = Class.extend({
	/**
	 * list of registered callbacks
	 * @var	array<object>
	 */
	_callbacks: [ ],
	
	/**
	 * list of dropdown selections
	 * @var	object
	 */
	_dropdowns: { },
	
	/**
	 * list of container elements
	 * @var	object
	 */
	_elements: { },
	
	/**
	 * notification object
	 * @var	WCF.System.Notification
	 */
	_notification: null,
	
	/**
	 * list of known options
	 * @var	array<object>
	 */
	_options: [ ],
	
	/**
	 * action proxy
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * list of data to update upon success
	 * @var	array<object>
	 */
	_updateData: [ ],
	
	/**
	 * Initializes a new inline editor.
	 */
	init: function(elementSelector) {
		var $elements = $(elementSelector);
		if (!$elements.length) {
			return;
		}
		
		this._setOptions();
		var $quickOption = '';
		for (var $i = 0, $length = this._options.length; $i < $length; $i++) {
			if (this._options[$i].isQuickOption) {
				$quickOption = this._options[$i].optionName;
				break;
			}
		}
		
		var self = this;
		$elements.each(function(index, element) {
			var $element = $(element);
			var $elementID = $element.wcfIdentify();
			
			// find trigger element
			var $trigger = self._getTriggerElement($element);
			if ($trigger === null || $trigger.length !== 1) {
				return;
			}
			
			$trigger.click($.proxy(self._show, self)).data('elementID', $elementID);
			if ($quickOption) {
				// simulate click on target action
				$trigger.disableSelection().data('optionName', $quickOption).dblclick($.proxy(self._click, self));
			}
			
			// store reference
			self._elements[$elementID] = $element;
		});
		
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
		
		WCF.CloseOverlayHandler.addCallback('WCF.InlineEditor', $.proxy(this._closeAll, this));
		
		this._notification = new WCF.System.Notification(WCF.Language.get('wcf.global.success'), 'success');
	},
	
	/**
	 * Closes all inline editors.
	 */
	_closeAll: function() {
		for (var $elementID in this._elements) {
			this._hide($elementID);
		}
	},
	
	/**
	 * Sets options for this inline editor.
	 */
	_setOptions: function() {
		this._options = [ ];
	},
	
	/**
	 * Register an option callback for validation and execution.
	 * 
	 * @param	object		callback
	 */
	registerCallback: function(callback) {
		if ($.isFunction(callback)) {
			this._callbacks.push(callback);
		}
	},
	
	/**
	 * Returns the triggering element.
	 * 
	 * @param	jQuery		element
	 * @return	jQuery
	 */
	_getTriggerElement: function(element) {
		return null;
	},
	
	/**
	 * Shows a dropdown menu if options are available.
	 * 
	 * @param	object		event
	 */
	_show: function(event) {
		var $elementID = $(event.currentTarget).data('elementID');
		
		// build dropdown
		var $trigger = null;
		if (!this._dropdowns[$elementID]) {
			$trigger = this._getTriggerElement(this._elements[$elementID]).addClass('dropdownToggle').wrap('<span class="dropdown" />');
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
			else if (this._validate($elementID, $option.optionName) || this._validateCallbacks($elementID, $option.optionName)) {
				var $listItem = $('<li><span>' + $option.label + '</span></li>').appendTo(this._dropdowns[$elementID]);
				$listItem.data('elementID', $elementID).data('optionName', $option.optionName).click($.proxy(this._click, this));
				
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
		}
		
		if ($trigger !== null) {
			WCF.Dropdown.initDropdown($trigger, true);
		}
		
		return false;
	},
	
	/**
	 * Validates an option.
	 * 
	 * @param	string		elementID
	 * @param	string		optionName
	 * @returns	boolean
	 */
	_validate: function(elementID, optionName) {
		return false;
	},
	
	/**
	 * Validates an option provided by callbacks.
	 * 
	 * @param	string		elementID
	 * @param	string		optionName
	 * @return	boolean
	 */
	_validateCallbacks: function(elementID, optionName) {
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
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		var $length = this._updateData.length;
		if (!$length) {
			return;
		}
		
		this._updateState();
		
		this._updateData = [ ];
	},
	
	/**
	 * Update element states based upon update data.
	 */
	_updateState: function() { },
	
	/**
	 * Handles clicks within dropdown.
	 * 
	 * @param	object		event
	 */
	_click: function(event) {
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
	 * @param	string		elementID
	 * @param	string		optionName
	 * @return	boolean
	 */
	_execute: function(elementID, optionName) {
		return false;
	},
	
	/**
	 * Executes actions associated with an option provided by callbacks.
	 * 
	 * @param	string		elementID
	 * @param	string		optionName
	 * @return	boolean
	 */
	_executeCallback: function(elementID, optionName) {
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
	 * @param	string		elementID
	 */
	_hide: function(elementID) {
		if (this._dropdowns[elementID]) {
			this._dropdowns[elementID].empty().parent('span').removeClass('dropdownOpen');
		}
	}
});

/**
 * Default implementation for ajax file uploads
 * 
 * @param	jquery		buttonSelector
 * @param	jquery		fileListSelector
 * @param	string		className
 * @param	jquery		options
 */
WCF.Upload = Class.extend({
	/**
	 * name of the upload field
	 * @var	string
	 */
	_name: '__files[]',
	
	/**
	 * button selector
	 * @var	jQuery
	 */
	_buttonSelector: null,
	
	/**
	 * file list selector
	 * @var	jQuery
	 */
	_fileListSelector: null,
	
	/**
	 * upload file
	 * @var	jQuery
	 */
	_fileUpload: null,
	
	/**
	 * class name
	 * @var	string
	 */
	_className: '',
	
	/**
	 * iframe for IE<10 fallback
	 * @var	jQuery
	 */
	_iframe: null,
	
	/**
	 * additional options
	 * @var	jQuery
	 */
	_options: {},
	
	/**
	 * upload matrix
	 * @var	array
	 */
	_uploadMatrix: [],
	
	/**
	 * true, if the active user's browser supports ajax file uploads
	 * @var	boolean
	 */
	_supportsAJAXUpload: true,
	
	/**
	 * fallback overlay for stupid browsers
	 * @var	jquery
	 */
	_overlay: null,
	
	/**
	 * Initializes a new upload handler.
	 * 
	 * @param	string		buttonSelector
	 * @param	string		fileListSelector
	 * @param	string		className
	 * @param	object		options
	 */
	init: function(buttonSelector, fileListSelector, className, options) {
		this._buttonSelector = buttonSelector;
		this._fileListSelector = fileListSelector;
		this._className = className;
		this._options = $.extend(true, {
			action: 'upload',
			multiple: false,
			url: 'index.php/AJAXUpload/?t=' + SECURITY_TOKEN + SID_ARG_2ND
		}, options || { });
		
		// check for ajax upload support
		var $xhr = new XMLHttpRequest();
		this._supportsAJAXUpload = ($xhr && ('upload' in $xhr) && ('onprogress' in $xhr.upload));
		
		// create upload button
		this._createButton();
	},
	
	/**
	 * Creates the upload button.
	 */
	_createButton: function() {
		if (this._supportsAJAXUpload) {
			this._fileUpload = $('<input type="file" name="' + this._name + '" ' + (this._options.multiple ? 'multiple="true" ' : '') + '/>');
			this._fileUpload.change($.proxy(this._upload, this));
			var $button = $('<p class="button uploadButton"><span>' + WCF.Language.get('wcf.global.button.upload') + '</span></p>');
			$button.prepend(this._fileUpload);
		}
		else {
			var $button = $('<p class="button uploadFallbackButton"><span>' + WCF.Language.get('wcf.global.button.upload') + '</span></p>');
			$button.click($.proxy(this._showOverlay, this));
		}
		
		this._insertButton($button);
	},
	
	/**
	 * Inserts the upload button.
	 * 
	 * @param	jQuery		button
	 */
	_insertButton: function(button) {
		this._buttonSelector.append(button);
	},
	
	/**
	 * Removes the upload button.
	 */
	_removeButton: function() {
		var $selector = '.uploadButton';
		if (!this._supportsAJAXUpload) {
			$selector = '.uploadFallbackButton';
		}
		
		this._buttonSelector.find($selector).remove();
	},
	
	/**
	 * Callback for file uploads.
	 */
	_upload: function() {
		var $files = this._fileUpload.prop('files');
		if ($files.length) {
			var $fd = new FormData();
			var $uploadID = this._createUploadMatrix($files);
			
			// no more files left, abort
			if (!this._uploadMatrix[$uploadID].length) {
				return;
			}
			
			var $validFilenames = [ ];
			for (var $i = 0, $length = this._uploadMatrix[$uploadID].length; $i < $length; $i++) {
				var $li = this._uploadMatrix[$uploadID][$i];
				if (!$li.hasClass('uploadFailed')) {
					$validFilenames.push($li.data('filename'));
				}
			}
			
			for (var $i = 0, $length = $files.length; $i < $length; $i++) {
				if ($validFilenames.indexOf($files[$i].name) !== -1) {
					$fd.append('__files[]', $files[$i]);
				}
			}
			
			$fd.append('actionName', this._options.action);
			$fd.append('className', this._className);
			var $additionalParameters = this._getParameters();
			for (var $name in $additionalParameters) {
				$fd.append('parameters['+$name+']', $additionalParameters[$name]);
			}
			
			var self = this;
			$.ajax({ 
				type: 'POST',
				url: this._options.url,
				enctype: 'multipart/form-data',
				data: $fd,
				contentType: false,
				processData: false,
				success: function(data, textStatus, jqXHR) {
					self._success($uploadID, data);
				},
				error: $.proxy(this._error, this),
				xhr: function() {
					var $xhr = $.ajaxSettings.xhr();
					if ($xhr) {
						$xhr.upload.addEventListener('progress', function(event) {
							self._progress($uploadID, event);
						}, false);
					}
					return $xhr;
				}
			});
		}
	},
	
	/**
	 * Creates upload matrix for provided files.
	 * 
	 * @param	array<object>		files
	 * @return	integer
	 */
	_createUploadMatrix: function(files) {
		if (files.length) {
			var $uploadID = this._uploadMatrix.length;
			this._uploadMatrix[$uploadID] = [ ];
			
			for (var $i = 0, $length = files.length; $i < $length; $i++) {
				var $file = files[$i];
				var $li = this._initFile($file);
				
				if (!$li.hasClass('uploadFailed')) {
					$li.data('filename', $file.name);
					this._uploadMatrix[$uploadID].push($li);
				}
			}
			
			return $uploadID;
		}
		
		return null;
	},
	
	/**
	 * Callback for success event.
	 * 
	 * @param	integer		uploadID
	 * @param	object		data
	 */
	_success: function(uploadID, data) { },
	
	/**
	 * Callback for error event.
	 * 
	 * @param	jQuery		jqXHR
	 * @param	string		textStatus
	 * @param	string		errorThrown
	 */
	_error: function(jqXHR, textStatus, errorThrown) { },
	
	/**
	 * Callback for progress event.
	 * 
	 * @param	integer		uploadID
	 * @param	object		event
	 */
	_progress: function(uploadID, event) {
		var $percentComplete = Math.round(event.loaded * 100 / event.total);
		
		for (var $i = 0; $i < this._uploadMatrix[uploadID].length; $i++) {
			this._uploadMatrix[uploadID][$i].find('progress').attr('value', $percentComplete);
		}
	},
	
	/**
	 * Returns additional parameters.
	 * 
	 * @return	object
	 */
	_getParameters: function() {
		return {};
	},
	
	/**
	 * Initializes list item for uploaded file.
	 * 
	 * @return	jQuery
	 */
	_initFile: function(file) {
		return $('<li>' + file.name + ' (' + file.size + ')<progress max="100" /></li>').appendTo(this._fileListSelector);
	},
	
	/**
	 * Shows the fallback overlay (work in progress)
	 */
	_showOverlay: function() {
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
			
			$form.submit($.proxy(function() {
				var $file = {
					name: this._getFilename(),
					size: ''
				};
				
				var $uploadID = this._createUploadMatrix([ $file ]);
				var self = this;
				this._iframe.data('loading', true).off('load').load(function() { self._evaluateResponse($uploadID); });
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
	 * @param	integer		uploadID
	 */
	_evaluateResponse: function(uploadID) {
		var $returnValues = $.parseJSON(this._iframe.contents().find('pre').html());
		this._success(uploadID, $returnValues);
	},
	
	/**
	 * Returns name of selected file.
	 * 
	 * @return	string
	 */
	_getFilename: function() {
		return $('#__fileUpload').val().split('\\').pop();
	}
});

/**
 * Namespace for sortables.
 */
WCF.Sortable = {};

/**
 * Sortable implementation for lists.
 * 
 * @param	string		containerID
 * @param	string		className
 * @param	integer		offset
 * @param	object		options
 */
WCF.Sortable.List = Class.extend({
	/**
	 * additional parameters for AJAX request
	 * @var	object
	 */
	_additionalParameters: { },
	
	/**
	 * action class name
	 * @var	string
	 */
	_className: '',
	
	/**
	 * container id
	 * @var	string
	 */
	_containerID: '',
	
	/**
	 * container object
	 * @var	jQuery
	 */
	_container: null,
	
	/**
	 * notification object
	 * @var	WCF.System.Notification
	 */
	_notification: null,
	
	/**
	 * show order offset
	 * @var	integer
	 */
	_offset: 0,
	
	/**
	 * list of options
	 * @var	object
	 */
	_options: { },
	
	/**
	 * proxy object
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * object structure
	 * @var	object
	 */
	_structure: { },
	
	/**
	 * Creates a new sortable list.
	 * 
	 * @param	string		containerID
	 * @param	string		className
	 * @param	integer		offset
	 * @param	object		options
	 * @param	boolean		isSimpleSorting
	 * @param	object		additionalParameters
	 */
	init: function(containerID, className, offset, options, isSimpleSorting, additionalParameters) {
		this._additionalParameters = additionalParameters || { };
		this._containerID = $.wcfEscapeID(containerID);
		this._container = $('#' + this._containerID);
		this._className = className;
		this._offset = (offset) ? offset : 0;
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
		this._structure = { };
		
		// init sortable
		this._options = $.extend(true, {
			axis: 'y',
			connectWith: '#' + this._containerID + ' .sortableList',
			disableNesting: 'sortableNoNesting',
			doNotClear: true,
			errorClass: 'sortableInvalidTarget',
			forcePlaceholderSize: true,
			helper: 'clone',
			items: 'li:not(.sortableNoSorting)',
			opacity: .6,
			placeholder: 'sortablePlaceholder',
			tolerance: 'pointer',
			toleranceElement: '> span'
		}, options || { });
		
		if (isSimpleSorting) {
			$('#' + this._containerID + ' .sortableList').sortable(this._options);
		}
		else {
			$('#' + this._containerID + ' > .sortableList').nestedSortable(this._options);
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
	 * Saves object structure.
	 */
	_submit: function() {
		// reset structure
		this._structure = { };
		
		// build structure
		this._container.find('.sortableList').each($.proxy(function(index, list) {
			var $list = $(list);
			var $parentID = $list.data('objectID');
			
			if ($parentID !== undefined) {
				$list.children(this._options.items).each($.proxy(function(index, listItem) {
					var $objectID = $(listItem).data('objectID');
					
					if (!this._structure[$parentID]) {
						this._structure[$parentID] = [ ];
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
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		if (this._notification === null) {
			this._notification = new WCF.System.Notification(WCF.Language.get('wcf.global.success.edit'));
		}
		
		this._notification.show();
	}
});

WCF.Popover = Class.extend({
	/**
	 * currently active element id
	 * @var	string
	 */
	_activeElementID: '',
	
	/**
	 * cancels popover
	 * @var	boolean
	 */
	_cancelPopover: false,
	
	/**
	 * element data
	 * @var	object
	 */
	_data: { },
	
	/**
	 * default dimensions, should reflect the estimated size
	 * @var	object
	 */
	_defaultDimensions: {
		height: 150,
		width: 450
	},
	
	/**
	 * default orientation, may be a combintion of left/right and bottom/top
	 * @var	object
	 */
	_defaultOrientation: {
		x: 'right',
		y: 'top'
	},
	
	/**
	 * delay to show or hide popover, values in miliseconds
	 * @var	object
	 */
	_delay: {
		show: 800,
		hide: 500
	},
	
	/**
	 * true, if an element is being hovered
	 * @var	boolean
	 */
	_hoverElement: false,
	
	/**
	 * element id of element being hovered
	 * @var	string
	 */
	_hoverElementID: '',
	
	/**
	 * true, if popover is being hovered
	 * @var	boolean
	 */
	_hoverPopover: false,
	
	/**
	 * minimum margin (all directions) for popover
	 * @var	integer
	 */
	_margin: 20,
	
	/**
	 * periodical executer once element or popover is no longer being hovered
	 * @var	WCF.PeriodicalExecuter
	 */
	_peOut: null,
	
	/**
	 * periodical executer once an element is being hovered
	 * @var	WCF.PeriodicalExecuter
	 */
	_peOverElement: null,
	
	/**
	 * popover object
	 * @var	jQuery
	 */
	_popover: null,
	
	/**
	 * popover content
	 * @var	jQuery
	 */
	_popoverContent: null,
	
	
	/**
	 * popover horizontal offset
	 * @var	integer
	 */
	_popoverOffset: 10,
	
	/**
	 * element selector
	 * @var	string
	 */
	_selector: '',
	
	/**
	 * Initializes a new WCF.Popover object.
	 * 
	 * @param	string		selector
	 */
	init: function(selector) {
		if ($.browser.mobile) return;
		
		// assign default values
		this._activeElementID = '';
		this._cancelPopover = false;
		this._data = { };
		this._defaultDimensions = {
			height: 150,
			width: 450
		};
		this._defaultOrientation = {
			x: 'right',
			y: 'top'
		};
		this._delay = {
			show: 800,
			hide: 500
		};
		this._hoverElement = false;
		this._hoverElementID = '';
		this._hoverPopover = false;
		this._margin = 20;
		this._peOut = null;
		this._peOverElement = null;
		this._popoverOffset = 10;
		this._selector = selector;
		
		this._popover = $('<div class="popover"><span class="icon icon48 icon-spinner"></span><div class="popoverContent"></div></div>').hide().appendTo(document.body);
		this._popoverContent = this._popover.children('.popoverContent:eq(0)');
		this._popover.hover($.proxy(this._overPopover, this), $.proxy(this._out, this));
		
		this._initContainers();
		WCF.DOMNodeInsertedHandler.addCallback('WCF.Popover.'+selector, $.proxy(this._initContainers, this));
	},
	
	/**
	 * Initializes all element triggers.
	 */
	_initContainers: function() {
		if ($.browser.mobile) return;
		
		var $elements = $(this._selector);
		if (!$elements.length) {
			return;
		}
		
		$elements.each($.proxy(function(index, element) {
			var $element = $(element);
			var $elementID = $element.wcfIdentify();
			
			if (!this._data[$elementID]) {
				this._data[$elementID] = {
					'content': null,
					'isLoading': false
				};
				
				$element.hover($.proxy(this._overElement, this), $.proxy(this._out, this));
				
				if ($element.is('a') && $element.attr('href')) {
					$element.click($.proxy(this._cancel, this));
				}
			}
		}, this));
	},
	
	/**
	 * Cancels popovers if link is being clicked
	 */
	_cancel: function(event) {
		this._cancelPopover = true;
		this._hide(true);
	},
	
	/**
	 * Triggered once an element is being hovered.
	 * 
	 * @param	object		event
	 */
	_overElement: function(event) {
		if (this._cancelPopover) {
			return;
		}
		
		if (this._peOverElement !== null) {
			this._peOverElement.stop();
		}
		
		var $elementID = $(event.currentTarget).wcfIdentify();
		this._hoverElementID = $elementID;
		this._peOverElement = new WCF.PeriodicalExecuter($.proxy(function(pe) {
			pe.stop();
			
			// still above the same element
			if (this._hoverElementID === $elementID) {
				this._activeElementID = $elementID;
				this._prepare();
			}
		}, this), this._delay.show);
		
		this._hoverElement = true;
		this._hoverPopover = false;
	},
	
	/**
	 * Prepares popover to be displayed.
	 */
	_prepare: function() {
		if (this._cancelPopover) {
			return;
		}
		
		if (this._peOut !== null) {
			this._peOut.stop();
		}
		
		// hide and reset
		if (this._popover.is(':visible')) {
			this._hide(true);
		}
		
		// insert html
		if (!this._data[this._activeElementID].loading && this._data[this._activeElementID].content) {
			this._popoverContent.html(this._data[this._activeElementID].content);
			
			WCF.DOMNodeInsertedHandler.execute();
		}
		else {
			this._data[this._activeElementID].loading = true;
		}
		
		// get dimensions
		var $dimensions = this._popover.show().getDimensions();
		if (this._data[this._activeElementID].loading) {
			$dimensions = {
				height: Math.max($dimensions.height, this._defaultDimensions.height),
				width: Math.max($dimensions.width, this._defaultDimensions.width)
			};
		}
		else {
			$dimensions = this._fixElementDimensions(this._popover, $dimensions);
		}
		this._popover.hide();
		
		// get orientation
		var $orientation = this._getOrientation($dimensions.height, $dimensions.width);
		this._popover.css(this._getCSS($orientation.x, $orientation.y));
		
		// apply orientation to popover
		this._popover.removeClass('bottom left right top').addClass($orientation.x).addClass($orientation.y);
		
		this._show();
	},
	
	/**
	 * Displays the popover.
	 */
	_show: function() {
		if (this._cancelPopover) {
			return;
		}
		
		this._popover.stop().show().css({ opacity: 1 }).wcfFadeIn();
		
		if (this._data[this._activeElementID].loading) {
			this._popover.children('span').show();
			this._loadContent();
		}
		else {
			this._popover.children('span').hide();
			this._popoverContent.css({ opacity: 1 });
		}
	},
	
	/**
	 * Loads content, should be overwritten by child classes.
	 */
	_loadContent: function() { },
	
	/**
	 * Inserts content and animating transition.
	 * 
	 * @param	string		elementID
	 * @param	boolean		animate
	 */
	_insertContent: function(elementID, content, animate) {
		this._data[elementID] = {
			content: content,
			loading: false
		};
		
		// only update content if element id is active
		if (this._activeElementID === elementID) {
			if (animate) {
				// get current dimensions
				var $dimensions = this._popoverContent.getDimensions();
				
				// insert new content
				this._popoverContent.css({
					height: 'auto',
					width: 'auto'
				});
				this._popoverContent.html(this._data[elementID].content);
				var $newDimensions = this._popoverContent.getDimensions();
				
				// enforce current dimensions and remove HTML
				this._popoverContent.html('').css({
					height: $dimensions.height + 'px',
					width: $dimensions.width + 'px'
				});
				
				// animate to new dimensons
				var self = this;
				this._popoverContent.animate({
					height: $newDimensions.height + 'px',
					width: $newDimensions.width + 'px'
				}, 300, function() {
					self._popover.children('span').hide();
					self._popoverContent.html(self._data[elementID].content).css({ opacity: 0 }).animate({ opacity: 1 }, 200);
					
					WCF.DOMNodeInsertedHandler.execute();
				});
			}
			else {
				// insert new content
				this._popover.children('span').hide();
				this._popoverContent.html(this._data[elementID].content);
				
				WCF.DOMNodeInsertedHandler.execute();
			}
		}
	},
	
	/**
	 * Hides the popover.
	 */
	_hide: function(disableAnimation) {
		var self = this;
		this._popoverContent.stop();
		this._popover.stop();
		
		if (disableAnimation) {
			self._popover.css({ opacity: 0 }).hide();
			self._popoverContent.empty().css({ height: 'auto', opacity: 0, width: 'auto' });
		}
		else {
			this._popover.wcfFadeOut(function() {
				self._popoverContent.empty().css({ height: 'auto', opacity: 0, width: 'auto' });
				self._popover.hide();
			});
		}
	},
	
	/**
	 * Triggered once popover is being hovered.
	 */
	_overPopover: function() {
		if (this._peOut !== null) {
			this._peOut.stop();
		}
		
		this._hoverElement = false;
		this._hoverPopover = true;
	},
	
	/**
	 * Triggered once element *or* popover is now longer hovered.
	 */
	_out: function(event) {
		if (this._cancelPopover) {
			return;
		}
		
		this._hoverElementID = '';
		this._hoverElement = false;
		this._hoverPopover = false;
		
		this._peOut = new WCF.PeriodicalExecuter($.proxy(function(pe) {
			pe.stop();
			
			// hide popover is neither element nor popover was hovered given time
			if (!this._hoverElement && !this._hoverPopover) {
				this._hide(false);
			}
		}, this), this._delay.hide);
	},
	
	/**
	 * Resolves popover orientation, tries to use default orientation first.
	 * 
	 * @param	integer		height
	 * @param	integer		width
	 * @return	object
	 */
	_getOrientation: function(height, width) {
		// get offsets and dimensions
		var $element = $('#' + this._activeElementID);
		var $offsets = $element.getOffsets('offset');
		var $elementDimensions = $element.getDimensions();
		var $documentDimensions = $(document).getDimensions();
		
		// try default orientation first
		var $orientationX = (this._defaultOrientation.x === 'left') ? 'left' : 'right';
		var $orientationY = (this._defaultOrientation.y === 'bottom') ? 'bottom' : 'top';
		var $result = this._evaluateOrientation($orientationX, $orientationY, $offsets, $elementDimensions, $documentDimensions, height, width);
		
		if ($result.flawed) {
			// try flipping orientationX
			$orientationX = ($orientationX === 'left') ? 'right' : 'left';
			$result = this._evaluateOrientation($orientationX, $orientationY, $offsets, $elementDimensions, $documentDimensions, height, width);
			
			if ($result.flawed) {
				// try flipping orientationY while maintaing original orientationX
				$orientationX = ($orientationX === 'right') ? 'left' : 'right';
				$orientationY = ($orientationY === 'bottom') ? 'top' : 'bottom';
				$result = this._evaluateOrientation($orientationX, $orientationY, $offsets, $elementDimensions, $documentDimensions, height, width);
				
				if ($result.flawed) {
					// try flipping both orientationX and orientationY compared to default values
					$orientationX = ($orientationX === 'left') ? 'right' : 'left';
					$result = this._evaluateOrientation($orientationX, $orientationY, $offsets, $elementDimensions, $documentDimensions, height, width);
					
					if ($result.flawed) {
						// fuck this shit, we will use the default orientation
						$orientationX = (this._defaultOrientationX === 'left') ? 'left' : 'right';
						$orientationY = (this._defaultOrientationY === 'bottom') ? 'bottom' : 'top';
					}
				}
			}
		}
		
		return {
			x: $orientationX,
			y: $orientationY
		};
	},
	
	/**
	 * Evaluates if popover fits into given orientation.
	 * 
	 * @param	string		orientationX
	 * @param	string		orientationY
	 * @param	object		offsets
	 * @param	object		elementDimensions
	 * @param	object		documentDimensions
	 * @param	integer		height
	 * @param	integer		width
	 * @return	object
	 */
	_evaluateOrientation: function(orientationX, orientationY, offsets, elementDimensions, documentDimensions, height, width) {
		var $heightDifference = 0, $widthDifference = 0;
		switch (orientationX) {
			case 'left':
				$widthDifference = offsets.left - width;
			break;
			
			case 'right':
				$widthDifference = documentDimensions.width - (offsets.left + width);
			break;
		}
		
		switch (orientationY) {
			case 'bottom':
				$heightDifference = documentDimensions.height - (offsets.top + elementDimensions.height + this._popoverOffset + height);
			break;
			
			case 'top':
				$heightDifference = offsets.top - (height - this._popoverOffset);
			break;
		}
		
		// check if both difference are above margin
		var $flawed = false;
		if ($heightDifference < this._margin || $widthDifference < this._margin) {
			$flawed = true;
		}
		
		return {
			flawed: $flawed,
			x: $widthDifference,
			y: $heightDifference
		};
	},
	
	/**
	 * Computes CSS for popover.
	 * 
	 * @param	string		orientationX
	 * @param	string		orientationY
	 * @return	object
	 */
	_getCSS: function(orientationX, orientationY) {
		var $css = {
			bottom: 'auto',
			left: 'auto',
			right: 'auto',
			top: 'auto'
		};
		
		var $element = $('#' + this._activeElementID);
		var $offsets = $element.getOffsets('offset');
		var $elementDimensions = this._fixElementDimensions($element, $element.getDimensions());
		var $windowDimensions = $(window).getDimensions();
		
		switch (orientationX) {
			case 'left':
				$css.right = $windowDimensions.width - ($offsets.left + $elementDimensions.width);
			break;
			
			case 'right':
				$css.left = $offsets.left;
			break;
		}
		
		switch (orientationY) {
			case 'bottom':
				$css.top = $offsets.top + ($elementDimensions.height + this._popoverOffset);
			break;
			
			case 'top':
				$css.bottom = $windowDimensions.height - ($offsets.top - this._popoverOffset);
			break;
		}
		
		return $css;
	},
	
	/**
	 * Tries to fix dimensions if element is partially hidden (overflow: hidden).
	 * 
	 * @param	jQuery		element
	 * @param	object		dimensions
	 * @return	dimensions
	 */
	_fixElementDimensions: function(element, dimensions) {
		var $parentDimensions = element.parent().getDimensions();
		
		if ($parentDimensions.height < dimensions.height) {
			dimensions.height = $parentDimensions.height;
		}
		
		if ($parentDimensions.width < dimensions.width) {
			dimensions.width = $parentDimensions.width;
		}
		
		return dimensions;
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
			this._searchInput.keydown($.proxy(this._keyDown, this)).on('paste', function() {
				setTimeout(function() { self._onPaste(); }, 100);
			});
		}
	},
	
	/**
	 * Handles the key down event.
	 * 
	 * @param	object		event
	 */
	_keyDown: function(event) {
		// 188 = [,]
		if (event === null || event.which === 188) {
			var $value = $.trim(this._searchInput.val());
			
			// read everything left from caret position
			if (event && event.which === 188) {
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
			if (event && event.which === 188) {
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
	}
});

/**
 * Provides a generic sitemap.
 */
WCF.Sitemap = Class.extend({
	/**
	 * sitemap name cache
	 * @var	array
	 */
	_cache: [ ],
	
	/**
	 * dialog overlay
	 * @var	jQuery
	 */
	_dialog: null,
	
	/**
	 * initialization state
	 * @var	boolean
	 */
	_didInit: false,
	
	/**
	 * action proxy
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * Initializes the generic sitemap.
	 */
	init: function() {
		$('#sitemap').click($.proxy(this._click, this));
		
		this._cache = [ ];
		this._dialog = null;
		this._didInit = false;
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
	},
	
	/**
	 * Handles clicks on the sitemap icon.
	 */
	_click: function() {
		if (this._dialog === null) {
			this._dialog = $('<div id="sitemapDialog" />').appendTo(document.body);
			
			this._proxy.setOption('data', {
				actionName: 'getSitemap',
				className: 'wcf\\data\\sitemap\\SitemapAction'
			});
			this._proxy.sendRequest();
		}
		else {
			this._dialog.wcfDialog('open');
		}
	},
	
	/**
	 * Handles successful AJAX responses.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		if (this._didInit) {
			this._cache.push(data.returnValues.sitemapName);
			
			this._dialog.find('#sitemap_' + data.returnValues.sitemapName).html(data.returnValues.template);
			
			// redraw dialog
			this._dialog.wcfDialog('render');
		}
		else {
			// mark sitemap name as loaded
			this._cache.push(data.returnValues.sitemapName);
			
			// insert sitemap template
			this._dialog.html(data.returnValues.template);
			
			// bind event listener
			this._dialog.find('.sitemapNavigation').click($.proxy(this._navigate, this));
			
			// select active item
			this._dialog.find('.tabMenuContainer').wcfTabs('select', 'sitemap_' + data.returnValues.sitemapName);
			
			// show dialog
			this._dialog.wcfDialog({
				title: WCF.Language.get('wcf.page.sitemap')
			});
			
			this._didInit = true;
		}
	},
	
	/**
	 * Navigates between different sitemaps.
	 * 
	 * @param	object		event
	 */
	_navigate: function(event) {
		var $sitemapName = $(event.currentTarget).data('sitemapName');
		if (WCF.inArray($sitemapName, this._cache)) {
			this._dialog.find('.tabMenuContainer').wcfTabs('select', 'sitemap_' + $sitemapName);
			
			// redraw dialog
			this._dialog.wcfDialog('render');
		}
		else {
			this._proxy.setOption('data', {
				actionName: 'getSitemap',
				className: 'wcf\\data\\sitemap\\SitemapAction',
				parameters: {
					sitemapName: $sitemapName
				}
			});
			this._proxy.sendRequest();
		}
	}
});

/**
 * Provides a language chooser.
 * 
 * @param	string		containerID
 * @param	string		inputFieldID
 * @param	integer		languageID
 * @param	object		languages
 * @param	object		callback
 */
WCF.Language.Chooser = Class.extend({
	/**
	 * callback object
	 * @var	object
	 */
	_callback: null,
	
	/**
	 * dropdown object
	 * @var	jQuery
	 */
	_dropdown: null,
	
	/**
	 * input field
	 * @var	jQuery
	 */
	_input: null,
	
	/**
	 * Initializes the language chooser.
	 * 
	 * @param	string		containerID
	 * @param	string		inputFieldID
	 * @param	integer		languageID
	 * @param	object		languages
	 * @param	object		callback
	 * @param	boolean		allowEmptyValue
	 */
	init: function(containerID, inputFieldID, languageID, languages, callback, allowEmptyValue) {
		var $container = $('#' + containerID);
		if ($container.length != 1) {
			console.debug("[WCF.Language.Chooser] Invalid container id '" + containerID + "' given");
			return;
		}
		
		// bind language id input
		this._input = $('#' + inputFieldID);
		if (!this._input.length) {
			this._input = $('<input type="hidden" name="' + inputFieldID + '" value="' + languageID + '" />').appendTo($container);
		}
		
		// handle callback
		if (callback !== undefined) {
			if (!$.isFunction(callback)) {
				console.debug("[WCF.Language.Chooser] Given callback is invalid");
				return;
			}
			
			this._callback = callback;
		}
		
		// create language dropdown
		this._dropdown = $('<div class="dropdown" id="' + containerID + '-languageChooser" />').appendTo($container);
		$('<div class="dropdownToggle boxFlag box24" data-toggle="' + containerID + '-languageChooser"></div>').appendTo(this._dropdown);
		var $dropdownMenu = $('<ul class="dropdownMenu" />').appendTo(this._dropdown);
		
		for (var $languageID in languages) {
			var $language = languages[$languageID];
			var $item = $('<li class="boxFlag"><a class="box24"><div class="framed"><img src="' + $language.iconPath + '" alt="" class="iconFlag" /></div> <div><h3>' + $language.languageName + '</h3></div></a></li>').appendTo($dropdownMenu);
			$item.data('languageID', $languageID).click($.proxy(this._click, this));
			
			// update dropdown label
			if ($languageID == languageID) {
				var $html = $('' + $item.html());
				var $innerContent = $html.children().detach();
				this._dropdown.children('.dropdownToggle').empty().append($innerContent);
			}
		}
		
		// allow an empty selection (e.g. using as language filter)
		if (allowEmptyValue) {
			$('<li class="dropdownDivider" />').appendTo($dropdownMenu);
			var $item = $('<li><a>' + WCF.Language.get('wcf.global.language.noSelection') + '</a></li>').data('languageID', 0).click($.proxy(this._click, this)).appendTo($dropdownMenu);
			
			if (languageID === 0) {
				this._dropdown.children('.dropdownToggle').empty().append($item.html());
			}
		}
		
		WCF.Dropdown.init();
	},
	
	/**
	 * Handles click events.
	 * 
	 * @param	object		event
	 */
	_click: function(event) {
		var $item = $(event.currentTarget);
		var $languageID = $item.data('languageID');
		
		// update input field
		this._input.val($languageID);
		
		// update dropdown label
		var $html = $('' + $item.html());
		var $innerContent = ($languageID === 0) ? $html : $html.children().detach();
		this._dropdown.children('.dropdownToggle').empty().append($innerContent);
		
		// execute callback
		if (this._callback !== null) {
			this._callback($item);
		}
	}
});

/**
 * Namespace for style related classes.
 */
WCF.Style = { };

/**
 * Provides a visual style chooser.
 */
WCF.Style.Chooser = Class.extend({
	/**
	 * dialog overlay
	 * @var	jQuery
	 */
	_dialog: null,
	
	/**
	 * action proxy
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * Initializes the style chooser class.
	 */
	init: function() {
		$('<li class="styleChooser"><a>' + WCF.Language.get('wcf.style.changeStyle') + '</a></li>').appendTo($('#footerNavigation > ul.navigationItems')).click($.proxy(this._showDialog, this));
		
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
	},
	
	/**
	 * Displays the style chooser dialog.
	 */
	_showDialog: function() {
		if (this._dialog === null) {
			this._dialog = $('<div id="styleChooser" />').hide().appendTo(document.body);
			this._loadDialog();
		}
		else {
			this._dialog.wcfDialog({
				title: WCF.Language.get('wcf.style.changeStyle')
			});
		}
	},
	
	/**
	 * Loads the style chooser dialog.
	 */
	_loadDialog: function() {
		this._proxy.setOption('data', {
			actionName: 'getStyleChooser',
			className: 'wcf\\data\\style\\StyleAction'
		});
		this._proxy.sendRequest();
	},
	
	/**
	 * Handles successful AJAX requests.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		if (data.actionName === 'changeStyle') {
			window.location.reload();
			return;
		}
		
		this._dialog.html(data.returnValues.template);
		this._dialog.find('li').addClass('pointer').click($.proxy(this._click, this));
		
		this._showDialog();
	},
	
	/**
	 * Changes user style.
	 * 
	 * @param	object		event
	 */
	_click: function(event) {
		this._proxy.setOption('data', {
			actionName: 'changeStyle',
			className: 'wcf\\data\\style\\StyleAction',
			objectIDs: [ $(event.currentTarget).data('styleID') ]
		});
		this._proxy.sendRequest();
	}
});

/**
 * Converts static user panel items into interactive dropdowns.
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
	 * Initialites the WCF.UserPanel class.
	 * 
	 * @param	string		containerID
	 */
	init: function(containerID) {
		this._container = $('#' + containerID);
		this._didLoad = false;
		this._revertOnEmpty = true;
		
		if (this._container.length != 1) {
			console.debug("[WCF.UserPanel] Unable to find container identfied by '" + containerID + "', aborting.");
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
		
		var $button = $('<a class="dropdownToggle">' + this._link.html() + '</a>').appendTo(this._container).click($.proxy(this._click, this));
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
	 */
	_click: function() {
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
			var $badge = this._container.find('.badge');
			if (!$badge.length) {
				$badge = $('<span class="badge badgeInverse" />').appendTo(this._container.children('.dropdownToggle'));
				$badge.before(' ');
			}
			$badge.html(data.returnValues.totalCount);
			
			this._after($dropdownMenu);
		}
		else {
			$('<li><span>' + WCF.Language.get(this._noItems) + '</span></li>').prependTo($dropdownMenu);
			
			// remove badge
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

/**
 * WCF implementation for dialogs, based upon ideas by jQuery UI.
 */
$.widget('ui.wcfDialog', {
	/**
	 * close button
	 * @var	jQuery
	 */
	_closeButton: null,
	
	/**
	 * dialog container
	 * @var	jQuery
	 */
	_container: null,
	
	/**
	 * dialog content
	 * @var	jQuery
	 */
	_content: null,
	
	/**
	 * modal overlay
	 * @var	jQuery
	 */
	_overlay: null,
	
	/**
	 * plain html for title
	 * @var	string
	 */
	_title: null,
	
	/**
	 * title bar
	 * @var	jQuery
	 */
	_titlebar: null,
	
	/**
	 * dialog visibility state
	 * @var	boolean
	 */
	_isOpen: false,
	
	/**
	 * option list
	 * @var	object
	 */
	options: {
		// dialog
		autoOpen: true,
		closable: true,
		closeButtonLabel: null,
		hideTitle: false,
		modal: true,
		title: '',
		zIndex: 400,
		
		// event callbacks
		onClose: null,
		onShow: null
	},
	
	/**
	 * @see	$.widget._createWidget()
	 */
	_createWidget: function(options, element) {
		// ignore script tags
		if ($(element).getTagName() === 'script') {
			console.debug("[ui.wcfDialog] Ignored script tag");
			this.element = false;
			return null;
		}
		
		$.Widget.prototype._createWidget.apply(this, arguments);
	},
	
	/**
	 * Initializes a new dialog.
	 */
	_init: function() {
		if (this.options.autoOpen) {
			this.open();
		}
		
		// act on resize
		$(window).resize($.proxy(this._resize, this));
	},
	
	/**
	 * Creates a new dialog instance.
	 */
	_create: function() {
		if (this.options.closeButtonLabel === null) {
			this.options.closeButtonLabel = WCF.Language.get('wcf.global.button.close');
		}
		
		// create dialog container
		this._container = $('<div class="dialogContainer" />').hide().css({ zIndex: this.options.zIndex }).appendTo(document.body);
		this._titlebar = $('<header class="dialogTitlebar" />').hide().appendTo(this._container);
		this._title = $('<span class="dialogTitle" />').hide().appendTo(this._titlebar);
		this._closeButton = $('<a class="dialogCloseButton jsTooltip" title="' + this.options.closeButtonLabel + '"><span /></a>').click($.proxy(this.close, this)).hide().appendTo(this._titlebar);
		this._content = $('<div class="dialogContent" />').appendTo(this._container);
		
		this._setOption('title', this.options.title);
		this._setOption('closable', this.options.closable);
		
		// move target element into content
		var $content = this.element.detach();
		this._content.html($content);
		
		// create modal view
		if (this.options.modal) {
			this._overlay = $('#jsWcfDialogOverlay');
			if (!this._overlay.length) {
				this._overlay = $('<div id="jsWcfDialogOverlay" class="dialogOverlay" />').css({ height: '100%', zIndex: 399 }).hide().appendTo(document.body);
			}
			
			if (this.options.closable) {
				this._overlay.click($.proxy(this.close, this));
				
				$(document).keyup($.proxy(function(event) {
					if (event.keyCode && event.keyCode === $.ui.keyCode.ESCAPE) {
						this.close();
						event.preventDefault();
					}
				}, this));
			}
		}
		
		WCF.DOMNodeInsertedHandler.execute();
	},
	
	/**
	 * Sets the given option to the given value.
	 * See the jQuery UI widget documentation for more.
	 */
	_setOption: function(key, value) {
		this.options[key] = value;
		
		if (key == 'hideTitle' || key == 'title') {
			if (!this.options.hideTitle && this.options.title != '') {
				this._title.html(this.options.title).show();
			} else {
				this._title.html('');
			}
		} else if (key == 'closable' || key == 'closeButtonLabel') {
			if (this.options.closable) {
				this._closeButton.attr('title', this.options.closeButtonLabel).show().find('span').html(this.options.closeButtonLabel);
				
				WCF.DOMNodeInsertedHandler.execute();
			} else {
				this._closeButton.hide();
			}
		}
		
		if ((!this.options.hideTitle && this.options.title != '') || this.options.closable) {
			this._titlebar.show();
		} else {
			this._titlebar.hide();
		}
		
		return this;
	},
	
	/**
	 * Opens this dialog.
	 */
	open: function() {
		// ignore script tags
		if (this.element === false) {
			return;
		}
		
		if (this.isOpen()) {
			return;
		}
		
		if (this._overlay !== null) {
			WCF.activeDialogs++;
			
			if (WCF.activeDialogs === 1) {
				this._overlay.show();
			}
		}
		
		this.render();
		this._isOpen = true;
	},
	
	/**
	 * Returns true if dialog is visible.
	 * 
	 * @return	boolean
	 */
	isOpen: function() {
		return this._isOpen;
	},
	
	/**
	 * Closes this dialog.
	 * 
	 * This function can be manually called, even if the dialog is set as not
	 * closable by the user.
	 * 
	 * @param	object		event
	 */
	close: function(event) {
		if (!this.isOpen()) {
			return;
		}
		
		this._isOpen = false;
		this._container.wcfFadeOut();
		
		if (this._overlay !== null) {
			WCF.activeDialogs--;
			
			if (WCF.activeDialogs === 0) {
				this._overlay.hide();
			}
		}
		
		if (this.options.onClose !== null) {
			this.options.onClose();
		}
		
		if (event !== undefined) {
			event.preventDefault();
			event.stopPropagation();
			return false;
		}
	},
	
	/**
	 * Renders dialog on resize if visible.
	 */
	_resize: function() {
		if (this.isOpen()) {
			this.render();
		}
	},
	
	/**
	 * Renders this dialog, should be called whenever content is updated.
	 */
	render: function() {
		// check if this if dialog was previously hidden and container is fixed
		// at 0px (mobile optimization), in this case scroll to top
		if (!this._container.is(':visible') && this._container.css('top') === '0px') {
			window.scrollTo(0, 0);
		}
		
		// force dialog and it's contents to be visible
		this._container.show();
		this._content.children().show();
		
		// remove fixed content dimensions for calculation
		this._content.css({
			height: 'auto',
			width: 'auto'
		});
		
		// terminate concurrent rendering processes
		this._container.stop();
		this._content.stop();
		
		// set dialog to be fully opaque, prevents weird bugs in WebKit
		this._container.show().css('opacity', 1.0);
		
		// handle positioning of form submit controls
		var $heightDifference = 0;
		if (this._content.find('.formSubmit').length) {
			$heightDifference = this._content.find('.formSubmit').outerHeight();
			
			this._content.addClass('dialogForm').css({ marginBottom: $heightDifference + 'px' });
		}
		else {
			this._content.removeClass('dialogForm').css({ marginBottom: '0px' });
		}
		
		// force 800px or 90% width
		var $windowDimensions = $(window).getDimensions();
		if ($windowDimensions.width * 0.9 > 800) {
			this._container.css('maxWidth', '800px');
		}
		
		// calculate dimensions
		var $containerDimensions = this._container.getDimensions('outer');
		var $contentDimensions = this._content.getDimensions();
		
		// calculate maximum content height
		var $heightDifference = $containerDimensions.height - $contentDimensions.height;
		var $maximumHeight = $windowDimensions.height - $heightDifference - 120;
		this._content.css({ maxHeight: $maximumHeight + 'px' });
		
		this._determineOverflow();
		
		// calculate new dimensions
		$containerDimensions = this._container.getDimensions('outer');
		
		// move container
		var $leftOffset = Math.round(($windowDimensions.width - $containerDimensions.width) / 2);
		var $topOffset = Math.round(($windowDimensions.height - $containerDimensions.height) / 2);
		
		// place container at 20% height if possible
		var $desiredTopOffset = Math.round(($windowDimensions.height / 100) * 20);
		if ($desiredTopOffset < $topOffset) {
			$topOffset = $desiredTopOffset;
		}
		
		// apply offset
		this._container.css({
			left: $leftOffset + 'px',
			top: $topOffset + 'px'
		});
		
		// remove static dimensions
		this._content.css({
			height: 'auto',
			width: 'auto'
		});
		
		if (!this.isOpen()) {
			// hide container again
			this._container.hide();
			
			// fade in container
			this._container.wcfFadeIn($.proxy(function() {
				if (this.options.onShow !== null) {
					this.options.onShow();
				}
			}, this));
		}
	},
	
	/**
	 * Determines content overflow based upon static dimensions.
	 */
	_determineOverflow: function() {
		var $max = $(window).getDimensions();
		var $maxHeight = this._content.css('maxHeight');
		this._content.css('maxHeight', 'none');
		var $dialog = this._container.getDimensions('outer');
		
		var $overflow = 'visible';
		if (($max.height * 0.8 < $dialog.height) || ($max.width * 0.8 < $dialog.width)) {
			$overflow = 'auto';
		}
		
		this._content.css('overflow', $overflow);
		this._content.css('maxHeight', $maxHeight);
	},
	
	/**
	 * Returns calculated content dimensions.
	 * 
	 * @param	integer		maximumHeight
	 * @return	object
	 */
	_getContentDimensions: function(maximumHeight) {
		var $contentDimensions = this._content.getDimensions();
		
		// set height to maximum height if exceeded
		if (maximumHeight && $contentDimensions.height > maximumHeight) {
			$contentDimensions.height = maximumHeight;
		}
		
		return $contentDimensions;
	}
});

/**
 * Custom tab menu implementation for WCF.
 */
$.widget('ui.wcfTabs', $.ui.tabs, {
	/**
	 * Workaround for ids containing a dot ".", until jQuery UI devs learn
	 * to properly escape ids ... (it took 18 months until they finally
	 * fixed it!)
	 * 
	 * @see	http://bugs.jqueryui.com/ticket/4681
	 * @see	$.ui.tabs.prototype._sanitizeSelector()
	 */
	_sanitizeSelector: function(hash) {
		return hash.replace(/([:\.])/g, '\\$1');
	},
	
	/**
	 * @see	$.ui.tabs.prototype.select()
	 */
	select: function(index) {
		if (!$.isNumeric(index)) {
			// panel identifier given
			this.panels.each(function(i, panel) {
				if ($(panel).wcfIdentify() === index) {
					index = i;
					return false;
				}
			});
			
			// unable to identify panel
			if (!$.isNumeric(index)) {
				console.debug("[ui.wcfTabs] Unable to find panel identified by '" + index + "', aborting.");
				return;
			}
		}
		
		this._setOption('active', index);
	},
	
	/**
	 * Selects a specific tab by triggering the 'click' event.
	 * 
	 * @param	string		tabIdentifier
	 */
	selectTab: function(tabIdentifier) {
		tabIdentifier = '#' + tabIdentifier;
		
		this.anchors.each(function(index, anchor) {
			var $anchor = $(anchor);
			if ($anchor.prop('hash') === tabIdentifier) {
				$anchor.trigger('click');
				return false;
			}
		});
	},
	
	/**
	 * Returns the currently selected tab index.
	 * 
	 * @return	integer
	 */
	getCurrentIndex: function() {
		return this.lis.index(this.lis.filter('.ui-tabs-selected'));
	},
	
	/**
	 * Returns true if identifier is used by an anchor.
	 * 
	 * @param	string		identifier
	 * @param	boolean		isChildren
	 * @return	boolean
	 */
	hasAnchor: function(identifier, isChildren) {
		var $matches = false;
		
		this.anchors.each(function(index, anchor) {
			var $href = $(anchor).attr('href');
			if (/#.+/.test($href)) {
				// split by anchor
				var $parts = $href.split('#', 2);
				if (isChildren) {
					$parts = $parts[1].split('-', 2);
				}
				
				if ($parts[1] === identifier) {
					$matches = true;
					
					// terminate loop
					return false;
				}
			}
		});
		
		return $matches;
	},
	
	/**
	 * Shows default tab.
	 */
	revertToDefault: function() {
		var $active = this.element.data('active');
		if (!$active || $active === '') $active = 0;
		
		this.select($active);
	},
	
	/**
	 * @see	$.ui.tabs.prototype._processTabs()
	 */
	_processTabs: function() {
		var that = this;

		this.tablist = this._getList()
			.addClass( "ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all" )
			.attr( "role", "tablist" );

		this.tabs = this.tablist.find( "> li:has(a[href])" )
			.addClass( "ui-state-default ui-corner-top" )
			.attr({
				role: "tab",
				tabIndex: -1
			});

		this.anchors = this.tabs.map(function() {
				return $( "a", this )[ 0 ];
			})
			.addClass( "ui-tabs-anchor" )
			.attr({
				role: "presentation",
				tabIndex: -1
			});

		this.panels = $();

		this.anchors.each(function( i, anchor ) {
			var selector, panel,
				anchorId = $( anchor ).uniqueId().attr( "id" ),
				tab = $( anchor ).closest( "li" ),
				originalAriaControls = tab.attr( "aria-controls" );

			// inline tab
			selector = anchor.hash;
			panel = that.element.find( that._sanitizeSelector( selector ) );
			
			if ( panel.length) {
				that.panels = that.panels.add( panel );
			}
			if ( originalAriaControls ) {
				tab.data( "ui-tabs-aria-controls", originalAriaControls );
			}
			tab.attr({
				"aria-controls": selector.substring( 1 ),
				"aria-labelledby": anchorId
			});
			panel.attr( "aria-labelledby", anchorId );
		});

		this.panels
			.addClass( "ui-tabs-panel ui-widget-content ui-corner-bottom" )
			.attr( "role", "tabpanel" );
	},
	
	/**
	 * @see	$.ui.tabs.prototype.load()
	 */
	load: function( index, event ) {
		return;
	}
});

/**
 * jQuery widget implementation of the wcf pagination.
 */
$.widget('ui.wcfPages', {
	SHOW_LINKS: 11,
	SHOW_SUB_LINKS: 20,
	
	options: {
		// vars
		activePage: 1,
		maxPage: 1,
		
		// language
		// we use options here instead of language variables, because the paginator is not only usable with pages
		nextPage: null,
		previousPage: null
	},
	
	/**
	 * Creates the pages widget.
	 */
	_create: function() {
		if (this.options.nextPage === null) this.options.nextPage = WCF.Language.get('wcf.global.page.next');
		if (this.options.previousPage === null) this.options.previousPage = WCF.Language.get('wcf.global.page.previous');
		
		this.element.addClass('pageNavigation');
		
		this._render();
	},
	
	/**
	 * Destroys the pages widget.
	 */
	destroy: function() {
		$.Widget.prototype.destroy.apply(this, arguments);
		
		this.element.children().remove();
	},
	
	/**
	 * Renders the pages widget.
	 */
	_render: function() {
		// only render if we have more than 1 page
		if (!this.options.disabled && this.options.maxPage > 1) {
			var $hasHiddenPages = false;
			
			// make sure pagination is visible
			if (this.element.hasClass('hidden')) {
				this.element.removeClass('hidden');
			}
			this.element.show();
			
			this.element.children().remove();
			
			var $pageList = $('<ul />');
			this.element.append($pageList);
			
			var $previousElement = $('<li class="button skip" />');
			$pageList.append($previousElement);
			
			if (this.options.activePage > 1) {
				var $previousLink = $('<a' + ((this.options.previousPage != null) ? (' title="' + this.options.previousPage + '"') : ('')) + '></a>');
				$previousElement.append($previousLink);
				this._bindSwitchPage($previousLink, this.options.activePage - 1);
				
				var $previousImage = $('<span class="icon icon16 icon-double-angle-left" />');
				$previousLink.append($previousImage);
			}
			else {
				var $previousImage = $('<span class="icon icon16 icon-double-angle-left" />');
				$previousElement.append($previousImage);
				$previousElement.addClass('disabled');
				$previousImage.addClass('disabled');
			}
			
			// add first page
			$pageList.append(this._renderLink(1));
			
			// calculate page links
			var $maxLinks = this.SHOW_LINKS - 4;
			var $linksBefore = this.options.activePage - 2;
			if ($linksBefore < 0) $linksBefore = 0;
			var $linksAfter = this.options.maxPage - (this.options.activePage + 1);
			if ($linksAfter < 0) $linksAfter = 0;
			if (this.options.activePage > 1 && this.options.activePage < this.options.maxPage) $maxLinks--;
			
			var $half = $maxLinks / 2;
			var $left = this.options.activePage;
			var $right = this.options.activePage;
			if ($left < 1) $left = 1;
			if ($right < 1) $right = 1;
			if ($right > this.options.maxPage - 1) $right = this.options.maxPage - 1;
			
			if ($linksBefore >= $half) {
				$left -= $half;
			}
			else {
				$left -= $linksBefore;
				$right += $half - $linksBefore;
			}
			
			if ($linksAfter >= $half) {
				$right += $half;
			}
			else {
				$right += $linksAfter;
				$left -= $half - $linksAfter;
			}
			
			$right = Math.ceil($right);
			$left = Math.ceil($left);
			if ($left < 1) $left = 1;
			if ($right > this.options.maxPage) $right = this.options.maxPage;
			
			// left ... links
			if ($left > 1) {
				if ($left - 1 < 2) {
					$pageList.append(this._renderLink(2));
				}
				else {
					$('<li class="button jumpTo"><a title="' + WCF.Language.get('wcf.global.page.jumpTo') + '" class="jsTooltip">...</a></li>').appendTo($pageList);
					$hasHiddenPages = true;
				}
			}
			
			// visible links
			for (var $i = $left + 1; $i < $right; $i++) {
				$pageList.append(this._renderLink($i));
			}
			
			// right ... links
			if ($right < this.options.maxPage) {
				if (this.options.maxPage - $right < 2) {
					$pageList.append(this._renderLink(this.options.maxPage - 1));
				}
				else {
					$('<li class="button jumpTo"><a title="' + WCF.Language.get('wcf.global.page.jumpTo') + '" class="jsTooltip">...</a></li>').appendTo($pageList);
					$hasHiddenPages = true;
				}
			}
			
			// add last page
			$pageList.append(this._renderLink(this.options.maxPage));
			
			// add next button
			var $nextElement = $('<li class="button skip" />');
			$pageList.append($nextElement);
			
			if (this.options.activePage < this.options.maxPage) {
				var $nextLink = $('<a' + ((this.options.nextPage != null) ? (' title="' + this.options.nextPage + '"') : ('')) + '></a>');
				$nextElement.append($nextLink);
				this._bindSwitchPage($nextLink, this.options.activePage + 1);
				
				var $nextImage = $('<span class="icon icon16 icon-double-angle-right" />');
				$nextLink.append($nextImage);
			}
			else {
				var $nextImage = $('<span class="icon icon16 icon-double-angle-right" />');
				$nextElement.append($nextImage);
				$nextElement.addClass('disabled');
				$nextImage.addClass('disabled');
			}
			
			if ($hasHiddenPages) {
				$pageList.data('pages', this.options.maxPage);
				WCF.System.PageNavigation.init('#' + $pageList.wcfIdentify(), $.proxy(function(pageNo) {
					this.switchPage(pageNo);
				}, this));
			}
		}
		else {
			// otherwise hide the paginator if not already hidden
			this.element.hide();
		}
	},
	
	/**
	 * Renders a page link.
	 * 
	 * @parameter	integer		page
	 * @return	jQuery
	 */
	_renderLink: function(page, lineBreak) {
		var $pageElement = $('<li class="button"></li>');
		if (lineBreak != undefined && lineBreak) {
			$pageElement.addClass('break');
		}
		if (page != this.options.activePage) {
			var $pageLink = $('<a>' + WCF.String.addThousandsSeparator(page) + '</a>'); 
			$pageElement.append($pageLink);
			this._bindSwitchPage($pageLink, page);
		}
		else {
			$pageElement.addClass('active');
			var $pageSubElement = $('<span>' + WCF.String.addThousandsSeparator(page) + '</span>');
			$pageElement.append($pageSubElement);
		}
		
		return $pageElement;
	},
	
	/**
	 * Binds the 'click'-event for the page switching to the given element.
	 * 
	 * @parameter	$(element)	element
	 * @paremeter	integer		page
	 */
	_bindSwitchPage: function(element, page) {
		var $self = this;
		element.click(function() {
			$self.switchPage(page);
		});
	},
	
	/**
	 * Switches to the given page
	 * 
	 * @parameter	Event		event
	 * @parameter	integer		page
	 */
	switchPage: function(page) {
		this._setOption('activePage', page);
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
					this.options[key] = value;
					this._render();
					this._trigger('switched', undefined, {
						activePage: value
					});
				}
				else {
					this._trigger('notSwitched', undefined, {
						activePage: value
					});
				}
			}
		}
		else {
			this.options[key] = value;
			
			if (key == 'disabled') {
				if (value) {
					this.element.children().remove();
				}
				else {
					this._render();
				}
			}
			else if (key == 'maxPage') {
				this._render();
			}
		}
		
		return this;
	},
	
	/**
	 * Start input of pagenumber
	 * 
	 * @parameter	Event		event
	 */
	_startInput: function(event) {
		// hide a-tag
		var $childLink = $(event.currentTarget);
		if (!$childLink.is('a')) $childLink = $childLink.parent('a');
		
		$childLink.hide();
		
		// show input-tag
		var $childInput = $childLink.parent('li').children('input')
			.css('display', 'block')
			.val('');
		
		$childInput.focus();
	},
	
	/**
	 * Stops input of pagenumber
	 * 
	 * @parameter	Event		event
	 */
	_stopInput: function(event) {
		// hide input-tag
		var $childInput = $(event.currentTarget);
		$childInput.css('display', 'none');
		
		// show a-tag
		var $childContainer = $childInput.parent('li');
		if ($childContainer != undefined && $childContainer != null) {
			$childContainer.children('a').show();
		}
	},
	
	/**
	 * Handles input of pagenumber
	 * 
	 * @parameter	Event		event
	 */
	_handleInput: function(event) {
		var $ie7 = ($.browser.msie && $.browser.version == '7.0');
		if (event.type != 'keyup' || $ie7) {
			if (!$ie7 || ((event.which == 13 || event.which == 27) && event.type == 'keyup')) {
				if (event.which == 13) {
					this.switchPage(parseInt($(event.currentTarget).val()));
				}
				
				if (event.which == 13 || event.which == 27) {
					this._stopInput(event);
					event.stopPropagation();
				}
			}
		}
	}
});

/**
 * Namespace for category related classes.
 */
WCF.Category = { };

/**
 * Handles selection of categories.
 */
WCF.Category.NestedList = Class.extend({
	/**
	 * list of categories
	 * @var	object
	 */
	_categories: { },
	
	/**
	 * Initializes the WCF.Category.NestedList object.
	 */
	init: function() {
		var self = this;
		$('.jsCategory').each(function(index, category) {
			var $category = $(category).data('parentCategoryID', null).change($.proxy(self._updateSelection, self));
			self._categories[$category.val()] = $category;
			
			// find child categories
			var $childCategoryIDs = [ ];
			$category.parents('li').find('.jsChildCategory').each(function(innerIndex, childCategory) {
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
	 * @param	object		event
	 */
	_updateSelection: function(event) {
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
 * Encapsulate eval() within an own function to prevent problems
 * with optimizing and minifiny JS.
 * 
 * @param	mixed		expression
 * @returns	mixed
 */
function wcfEval(expression) {
	return eval(expression);
}

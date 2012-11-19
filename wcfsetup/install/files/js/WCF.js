/**
 * Class and function collection for WCF
 * 
 * @author	Markus Bartz, Tim Düsterhus, Alexander Ebert, Matthias Schmidt
 * @copyright	2001-2011 WoltLab GmbH
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
		if (key && key.match(/ID$/)) {
			arguments[0] = key.replace(/ID$/, '-id');
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
	 * Returns tag name of current jQuery element.
	 * 
	 * @returns	string
	 */
	getTagName: function() {
		return this.get(0).tagName.toLowerCase();
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
			css = {
				display: this.css('display'),
				visibility: this.css('visibility')
			};
			
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
			this.css(css);
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
			css = {
				display: this.css('display'),
				visibility: this.css('visibility')
			};
			
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
			this.css(css);
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
		if (this.getTagName() == 'input') {
			if (this.attr('type') != 'text' && this.attr('type') != 'password') {
				return -1;
			}
		}
		else if (this.getTagName() != 'textarea') {
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
		if (this.getTagName() == 'input') {
			if (this.attr('type') != 'text' && this.attr('type') != 'password') {
				return false;
			}
		}
		else if (this.getTagName() != 'textarea') {
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
		
		return this.show(WCF.getEffect(this.getTagName(), 'drop'), { direction: direction }, duration, callback);
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
		
		return this.hide(WCF.getEffect(this.getTagName(), 'drop'), { direction: direction }, duration, callback);
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
		
		return this.show(WCF.getEffect(this.getTagName(), 'blind'), { direction: direction }, duration, callback);
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
		
		return this.hide(WCF.getEffect(this.getTagName(), 'blind'), { direction: direction }, duration, callback);
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
		
		return this.show(WCF.getEffect(this.getTagName(), 'fade'), { }, duration, callback);
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
		
		return this.hide(WCF.getEffect(this.getTagName(), 'fade'), { }, duration, callback);
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
	 * Counter for dynamic element id's
	 *
	 * @var	integer
	 */
	_idCounter: 0,
	
	/**
	 * Shows a modal dialog with a built-in AJAX-loader.
	 * 
	 * @param	string		dialogID
	 * @param	boolean		resetDialog
	 * @return	jQuery
	 */
	showAJAXDialog: function(dialogID, resetDialog) {
		if (!dialogID) {
			dialogID = this.getRandomID();
		}
		
		if (!$.wcfIsset(dialogID)) {
			$('<div id="' + dialogID + '"></div>').appendTo(document.body);
		}
		
		var dialog = $('#' + $.wcfEscapeID(dialogID));
		
		if (resetDialog) {
			dialog.empty();
		}
		
		var dialogOptions = arguments[2] || {};
		dialogOptions.ajax = true;
		
		dialog.wcfDialog(dialogOptions);
		
		return dialog;
	},
	
	/**
	 * Shows a modal dialog.
	 * 
	 * @param	string		dialogID
	 */
	showDialog: function(dialogID) {
		// we cannot work with a non-existant dialog, if you wish to
		// load content via AJAX, see showAJAXDialog() instead
		if (!$.wcfIsset(dialogID)) return;
		
		var $dialog = $('#' + $.wcfEscapeID(dialogID));
		
		var dialogOptions = arguments[1] || {};
		$dialog.wcfDialog(dialogOptions);
	},
	
	/**
	 * Returns a dynamically created id.
	 * 
	 * @see		https://github.com/sstephenson/prototype/blob/master/src/prototype/dom/dom.js#L1789
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
	 * @param	object		object
	 * @param	string		effect
	 * @return	string
	 */
	getEffect: function(tagName, effect) {
		// most effects are not properly supported on table rows, use highlight instead
		if (tagName == 'tr') {
			return 'highlight';
		}
		
		return effect;
	}
});

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
	 * Initializes dropdowns.
	 */
	init: function() {
		var $userPanelHeight = $('#topMenu').outerHeight();
		var self = this;
		$('.dropdownToggle').each(function(index, button) {
			var $button = $(button);
			if ($button.data('target')) {
				return true;
			}
			
			var $dropdown = $button.parents('.dropdown');
			if (!$dropdown.length) {
				// broken dropdown, ignore
				return true;
			}
			
			var $containerID = $dropdown.wcfIdentify();
			if (!self._dropdowns[$containerID]) {
				$button.click($.proxy(self._toggle, self));
				self._dropdowns[$containerID] = $dropdown;
				
				var $dropdownHeight = $dropdown.outerHeight();
				var $top = $dropdownHeight + 7;
				if ($dropdown.parents('#topMenu').length) {
					// fix calculation for user panel (elements may be shorter than they appear)
					$top = $userPanelHeight;
				}
				
				// calculate top offset for menu
				$button.next('.dropdownMenu').css({
					top: $top + 'px'
				});
			}
			
			$button.data('target', $containerID);
		});
		
		if (!this._didInit) {
			this._didInit = true;
			
			WCF.CloseOverlayHandler.addCallback('WCF.Dropdown', $.proxy(this._closeAll, this));
			WCF.DOMNodeInsertedHandler.addCallback('WCF.Dropdown', $.proxy(this.init, this));
		}
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
	 */
	_toggle: function(event) {
		var $targetID = $(event.currentTarget).data('target');
		
		// close all dropdowns
		for (var $containerID in this._dropdowns) {
			var $dropdown = this._dropdowns[$containerID];
			if ($dropdown.hasClass('dropdownOpen')) {
				$dropdown.removeClass('dropdownOpen');
				this._notifyCallbacks($dropdown, 'close');
			}
			else if ($containerID === $targetID) {
				// fix top offset
				var $dropdownMenu = $dropdown.find('.dropdownMenu');
				if ($dropdownMenu.css('top') === '7px') {
					$dropdownMenu.css({
						top: $dropdown.outerHeight() + 7
					});
				}
				
				$dropdown.addClass('dropdownOpen');
				this._notifyCallbacks($dropdown, 'open');
				
				this.setAlignment($dropdown);
			}
		}
		
		event.stopPropagation();
		return false;
	},
	
	/**
	 * Sets alignment for dropdown.
	 * 
	 * @param	jQuery		dropdown
	 * @param	jQuery		dropdownMenu
	 */
	setAlignment: function(dropdown, dropdownMenu) {
		var $dropdownMenu = (dropdown) ? dropdown.find('.dropdownMenu:eq(0)') : dropdownMenu;
		
		// calculate if dropdown should be right-aligned if there is not enough space
		var $dimensions = $dropdownMenu.getDimensions('outer');
		var $offsets = $dropdownMenu.getOffsets('offset');
		var $windowWidth = $(window).width();
		
		if (($offsets.left + $dimensions.width) > $windowWidth) {
			$dropdownMenu.css({
				left: 'auto',
				right: '0px'
			}).addClass('dropdownArrowRight');
		}
		else if ($dropdownMenu.css('right') != '0px') {
			$dropdownMenu.css({
				left: '0px',
				right: 'auto'
			}).removeClass('dropdownArrowRight');
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
				
				this._notifyCallbacks($dropdown, 'close');
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
		
		this._dropdowns[containerID].removeClass('open');
	},
	
	/**
	 * Notifies callbacks.
	 * 
	 * @param	jQuery		dropdown
	 * @param	string		action
	 */
	_notifyCallbacks: function(dropdown, action) {
		var $containerID = dropdown.wcfIdentify();
		if (!this._callbacks[$containerID]) {
			return;
		}
		
		for (var $i = 0, $length = this._callbacks[$containerID].length; $i < $length; $i++) {
			this._callbacks[$containerID][$i](dropdown, action);
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
	 * list of ids of marked objects
	 * @var	array
	 */
	_markedObjectIDs: [],
	
	/**
	 * current page
	 * @var	string
	 */
	_page: '',
	
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
	 */
	init: function(page, hasMarkedItems, actionObjects) {
		this._page = page;
		this._actionObjects = actionObjects;
		if (!actionObjects) {
			this._actionObjects = {};
		}
		if (hasMarkedItems) {
			this._hasMarkedItems = true;
		}
		
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
		if (this._hasMarkedItems) {
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
				pageClassName: this._page
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
			var $objectData = data.markedItems[$typeName];
			for (var $i in $objectData) {
				this._markedObjectIDs.push($objectData[$i]);
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
					if (WCF.inArray($item.data('objectID'), this._markedObjectIDs)) {
						$item.attr('checked', 'checked');
						
						// add marked class for element container
						$item.parents('.jsClipboardObject').addClass('jsMarked');
					}
				}, this));
				
				// check if there is a markAll-checkbox
				$container.find('input.jsClipboardMarkAll').each(function(innerIndex, markAll) {
					var $allItemsMarked = true;
					
					$container.find('input.jsClipboardItem').each(function(itemIndex, item) {
						var $item = $(item);
						if (!$item.attr('checked')) {
							$allItemsMarked = false;
						}
					});
					
					if ($allItemsMarked) {
						$(markAll).attr('checked', 'checked');
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
		this._containers.each(function(index, container) {
			var $container = $(container);
			
			$container.find('input.jsClipboardItem, input.jsClipboardMarkAll').removeAttr('checked');
			$container.find('.jsClipboardObject').removeClass('jsMarked');
		});
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
		var $isMarked = ($item.attr('checked')) ? true : false;
		var $objectIDs = [ $objectID ];
		
		if ($isMarked) {
			this._markedObjectIDs.push($objectID);
			$item.parents('.jsClipboardObject').addClass('jsMarked');
		}
		else {
			this._markedObjectIDs = $.removeArrayValue(this._markedObjectIDs, $objectID);
			$item.parents('.jsClipboardObject').removeClass('jsMarked');
		}
		
		// item is part of a container
		if ($item.data('hasContainer')) {
			var $container = $('#' + $item.data('hasContainer'));
			var $type = $container.data('type');
			
			// check if all items are marked
			var $markedAll = true;
			$container.find('input.jsClipboardItem').each(function(index, containerItem) {
				var $containerItem = $(containerItem);
				if (!$containerItem.attr('checked')) {
					$markedAll = false;
				}
			});
			
			// simulate a ticked 'markAll' checkbox
			$container.find('.jsClipboardMarkAll').each(function(index, markAll) {
				if ($markedAll) {
					$(markAll).attr('checked', 'checked');
				}
				else {
					$(markAll).removeAttr('checked');
				}
			});
		}
		else {
			// standalone item
			var $type = $item.data('type');
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
		if ($item.getTagName() == 'input') {
			$isMarked = $item.attr('checked');
		}
		
		// handle item containers
		if ($item.data('hasContainer')) {
			var $container = $('#' + $item.data('hasContainer'));
			var $type = $container.data('type');
			
			// toggle state for all associated items
			$container.find('input.jsClipboardItem').each($.proxy(function(index, containerItem) {
				var $containerItem = $(containerItem);
				var $objectID = $containerItem.data('objectID');
				if ($isMarked) {
					if (!$containerItem.attr('checked')) {
						$containerItem.attr('checked', 'checked');
						this._markedObjectIDs.push($objectID);
						$objectIDs.push($objectID);
					}
				}
				else {
					if ($containerItem.attr('checked')) {
						$containerItem.removeAttr('checked');
						this._markedObjectIDs = $.removeArrayValue(this._markedObjectIDs, $objectID);
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
				$list = $('<ul class="dropdown"></ul>').appendTo($container);
			}
			
			var $editor = data.items[$typeName];
			var $label = $('<li><span class="dropdownToggle button">' + $editor.label + '</span></li>').appendTo($list);
			var $itemList = $('<ol class="dropdownMenu"></ol>').appendTo($label);
			
			$label.click(function() { $list.toggleClass('dropdownOpen'); });
			
			// create editor items
			for (var $itemIndex in $editor.items) {
				var $item = $editor.items[$itemIndex];
				
				if ($item.actionName === 'unmarkAll') {
					$('<li class="dropdownDivider" />').appendTo($itemList);
				}
				
				var $listItem = $('<li><span>' + $item.label + '</span></li>').appendTo($itemList);
				$listItem.data('objectType', $typeName);
				$listItem.data('actionName', $item.actionName).data('parameters', $item.parameters);
				$listItem.data('internalData', $item.internalData).data('url', $item.url).data('type', $typeName);
				
				// bind event
				$listItem.click($.proxy(this._executeAction, this));
			}
			
			// block click event
			$container.click(function(event) {
				event.stopPropagation();
			});
			
			// register event handler
			var $containerID = $container.wcfIdentify();
			WCF.CloseOverlayHandler.addCallback($containerID, $.proxy(this._closeLists, this));
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
		$listItem.trigger('clipboardAction', [ $listItem.data('type'), $listItem.data('actionName'), $listItem.data('parameters') ]);
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
					listItem.trigger('clipboardActionResponse', [ data, listItem.data('type'), listItem.data('actionName'), listItem.data('parameters') ]);
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
	 * count of active requests
	 * @var	integer
	 */
	_activeRequests: 0,
	
	/**
	 * loading overlay
	 * @var	jQuery
	 */
	_loadingOverlay: null,
	
	/**
	 * loading overlay state
	 * @var	boolean
	 */
	_loadingOverlayVisible: false,
	
	/**
	 * timer for overlay activity
	 * @var	integer
	 */
	_loadingOverlayVisibleTimer: 0,
	
	/**
	 * suppresses errors
	 * @var	boolean
	 */
	_suppressErrors: false,
	
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
			after: null,
			init: null,
			failure: null,
			showLoadingOverlay: true,
			success: null,
			type: 'POST',
			url: 'index.php/AJAXProxy/?t=' + SECURITY_TOKEN + SID_ARG_2ND
		}, options);
		
		this.confirmationDialog = null;
		this.loading = null;
		this._suppressErrors = false;
		
		// send request immediately after initialization
		if (this.options.autoSend) {
			this.sendRequest();
		}
		
		var self = this;
		$(window).on('beforeunload', function() { self._suppressErrors = true; });
	},
	
	/**
	 * Sends an AJAX request.
	 */
	sendRequest: function() {
		this._init();
		
		$.ajax({
			data: this.options.data,
			dataType: 'json',
			type: this.options.type,
			url: this.options.url,
			success: $.proxy(this._success, this),
			error: $.proxy(this._failure, this)
		});
	},
	
	/**
	 * Fires before request is send, displays global loading status.
	 */
	_init: function() {
		if ($.isFunction(this.options.init)) {
			this.options.init(this);
		}
		
		this._activeRequests++;
		
		if (this.options.showLoadingOverlay) {
			this._showLoadingOverlay();
		}
	},
	
	/**
	 * Displays the loading overlay if not already visible due to an active request.
	 */
	_showLoadingOverlay: function() {
		// create loading overlay on first run
		if (this._loadingOverlay === null) {
			this._loadingOverlay = $('<div class="spinner"><img src="' + WCF.Icon.get('wcf.icon.loading') + '" alt="" class="icon48" /> <span>' + WCF.Language.get('wcf.global.loading') + '</span></div>').hide().appendTo($('body'));
		}
		
		// fade in overlay
		if (!this._loadingOverlayVisible) {
			this._loadingOverlayVisible = true;
			this._loadingOverlay.stop(true, true).fadeIn(100, $.proxy(function() {
				new WCF.PeriodicalExecuter($.proxy(this._hideLoadingOverlay, this), 100);
			}, this));
		}
	},
	
	/**
	 * Hides loading overlay if no requests are active and the timer reached at least 1 second.
	 * 
	 * @param	object		pe
	 */
	_hideLoadingOverlay: function(pe) {
		this._loadingOverlayVisibleTimer += 100;
		
		if (this._activeRequests == 0 && this._loadingOverlayVisibleTimer >= 100) {
			this._loadingOverlayVisible = false;
			this._loadingOverlayVisibleTimer = 0;
			pe.stop();
			
			this._loadingOverlay.fadeOut(100);
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
		try {
			var data = $.parseJSON(jqXHR.responseText);
			
			// call child method if applicable
			var $showError = true;
			if ($.isFunction(this.options.failure)) {
				$showError = this.options.failure(jqXHR, textStatus, errorThrown, jqXHR.responseText);
			}
			
			if (!this._suppressErrors && $showError !== false) {
				$('<div class="ajaxDebugMessage"><p>' + data.message + '</p><p>Stacktrace:</p><p>' + data.stacktrace + '</p></div>').wcfDialog({ title: WCF.Language.get('wcf.global.error.title') });
			}
		}
		// failed to parse JSON
		catch (e) {
			// call child method if applicable
			var $showError = true;
			if ($.isFunction(this.options.failure)) {
				$showError = this.options.failure(jqXHR, textStatus, errorThrown, jqXHR.responseText);
			}
			
			if (!this._suppressErrors && $showError !== false) {
				$('<div class="ajaxDebugMessage"><p>' + jqXHR.responseText + '</p></div>').wcfDialog({ title: WCF.Language.get('wcf.global.error.title') });
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
		// enable DOMNodeInserted event
		WCF.DOMNodeInsertedHandler.enable();
		
		// call child method if applicable
		if ($.isFunction(this.options.success)) {
			this.options.success(data, textStatus, jqXHR);
		}
		
		this._after();
	},
	
	/**
	 * Fires after an AJAX request, hides global loading status.
	 */
	_after: function() {
		if ($.isFunction(this.options.after)) {
			this.options.after();
		}
		
		this._activeRequests--;
		
		// disable DOMNodeInserted event
		WCF.DOMNodeInsertedHandler.disable();
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
	},
	
	/**
	 * Displays a spinner image for given element.
	 *
	 * @param	jQuery		element
	 */
	showSpinner: function(element) {
		element = $(element);
		
		if (element.getTagName() !== 'img') {
			console.debug('The given element is not an image, aborting.');
			return;
		}
		
		// force element dimensions
		element.attr('width', element.attr('width'));
		element.attr('height', element.attr('height'));
		
		// replace image
		element.attr('src', WCF.Icon.get('wcf.global.loading'));
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
 * @param	jQuery		containerList
 * @param	jQuery		badgeList
 */
WCF.Action.Delete = Class.extend({
	/**
	 * Initializes 'delete'-Proxy.
	 * 
	 * @param	string		className
	 * @param	jQuery		containerList
	 * @param	jQuery		badgeList
	 */
	init: function(className, containerList, badgeList) {
		if (!containerList.length) return;
		this.containerList = containerList;
		this.className = className;
		this.badgeList = badgeList;
		
		// initialize proxy
		var options = {
			success: $.proxy(this._success, this)
		};
		this.proxy = new WCF.Action.Proxy(options);
		
		// bind event listener
		this.containerList.each($.proxy(function(index, container) {
			$(container).find('.jsDeleteButton').bind('click', $.proxy(this._click, this));
		}, this));
	},
	
	/**
	 * Sends AJAX request.
	 * 
	 * @param	object		event
	 */
	_click: function(event) {
		var $target = $(event.currentTarget);
		
		if ($target.data('confirmMessage')) {
			WCF.System.Confirmation.show($target.data('confirmMessage'), $.proxy(this._execute, this), { target: $target });
		}
		else {
			this.proxy.showSpinner($target);
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
		
		this.proxy.showSpinner(parameters.target);
		this._sendRequest(parameters.target);
	},
	
	_sendRequest: function(object) {
		this.proxy.setOption('data', {
			actionName: 'delete',
			className: this.className,
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
		this.containerList.each($.proxy(function(index, container) {
			var $objectID = $(container).find('.jsDeleteButton').data('objectID');
			if (WCF.inArray($objectID, objectIDs)) {
				$(container).wcfBlindOut('up', function() {
					$(container).empty().remove();
				}, container);
				
				// update badges
				if (this.badgeList) {
					this.badgeList.each(function(innerIndex, badge) {
						$(badge).html($(badge).html() - 1);
					});
				}
			}
		}, this));
	}
});

/**
 * Basic implementation for AJAXProxy-based toggle actions.
 * 
 * @param	string		className
 * @param	jQuery		containerList
 * @param	string		toggleButtonSelector
 */
WCF.Action.Toggle = Class.extend({
	/**
	 * Initializes 'toggle'-Proxy
	 * 
	 * @param	string		className
	 * @param	jQuery		containerList
	 */
	init: function(className, containerList, toggleButtonSelector) {
		if (!containerList.length) return;
		this.containerList = containerList;
		this.className = className;
		
		this.toggleButtonSelector = '.jsToggleButton';
		if (toggleButtonSelector) {
			this.toggleButtonSelector = toggleButtonSelector;
		}
		
		// initialize proxy
		var options = {
			success: $.proxy(this._success, this)
		};
		this.proxy = new WCF.Action.Proxy(options);
		
		// bind event listener
		this.containerList.each($.proxy(function(index, container) {
			$(container).find(this.toggleButtonSelector).bind('click', $.proxy(this._click, this));
		}, this));
	},
	
	/**
	 * Sends AJAX request.
	 * 
	 * @param	object		event
	 */
	_click: function(event) {
		this.proxy.setOption('data', {
			actionName: 'toggle',
			className: this.className,
			objectIDs: [ $(event.target).data('objectID') ]
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
		this.containerList.each($.proxy(function(index, container) {
			var $toggleButton = $(container).find(this.toggleButtonSelector);
			if (WCF.inArray($toggleButton.data('objectID'), objectIDs)) {
				$(container).wcfHighlight();
				
				// toggle icon source
				$toggleButton.attr('src', function() {
					if (this.src.match(/disabled\.svg$/)) {
						return this.src.replace(/disabled\.svg$/, 'enabled.svg');
					}
					else {
						return this.src.replace(/enabled\.svg$/, 'disabled.svg');
					}
				});
				
				// toogle icon title
				$toggleButton.attr('title', function() {
					if (this.src.match(/enabled\.svg$/)) {
						if ($(this).data('disableTitle')) {
							return $(this).data('disableTitle');
						}
						
						return WCF.Language.get('wcf.global.button.disable');
					}
					else {
						if ($(this).data('enableTitle')) {
							return $(this).data('enableTitle');
						}
						
						return WCF.Language.get('wcf.global.button.enable');
					}
				});
				
				// toggle css class
				$(container).toggleClass('disabled');
			}
		}, this));
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
	 * Initializes the jQuery UI based date picker.
	 */
	init: function() {
		$('input[type=date]').each(function(index, input) {
			// do *not* use .attr()
			var $input = $(input).prop('type', 'text');
			
			// TODO: we should support all these braindead date formats, at least within output
			$input.datepicker({
				changeMonth: true,
				changeYear: true,
				showOtherMonths: true,
				dateFormat: 'yy-mm-dd',
				yearRange: '1900:2038' // TODO: make it configurable?
			});
		});
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
	 * Parameter timestamp must be in miliseconds!
	 * 
	 * @param	integer		timestamp
	 * @param	integer		offset
	 * @return	Date
	 */
	getTimezoneDate: function(timestamp, offset) {
		var $date = new Date(timestamp);
		var $localOffset = $date.getTimezoneOffset() * -1 * 60000;
		
		return new Date((timestamp - $localOffset - offset));
	}
};

/**
 * Handles relative time designations.
 */
WCF.Date.Time = Class.extend({
	/**
	 * Initializes relative datetimes.
	 */
	init: function() {
		// initialize variables
		this.elements = $('time.datetime');
		this.timestamp = 0;
		
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
		this.elements = $('time.datetime');
		this._refresh();
	},
	
	/**
	 * Refreshes relative datetime for each element.
	 */
	_refresh: function() {
		// TESTING ONLY!
		var $date = new Date();
		this.timestamp = ($date.getTime() - $date.getMilliseconds()) / 1000;
		// TESTING ONLY!
		
		this.elements.each($.proxy(this._refreshElement, this));
	},
	
	/**
	 * Refreshes relative datetime for current element.
	 * 
	 * @param	integer		index
	 * @param	object		element
	 */
	_refreshElement: function(index, element) {
		if (!$(element).attr('title')) {
			$(element).attr('title', $(element).text());
		}
		
		var $timestamp = $(element).data('timestamp');
		var $date = $(element).data('date');
		var $time = $(element).data('time');
		var $offset = $(element).data('offset');
		
		// timestamp is in the future
		if ($timestamp > this.timestamp) {
			var $string = WCF.Language.get('wcf.date.dateTimeFormat');
			$(element).text($string.replace(/\%date\%/, $date).replace(/\%time\%/, $time));
		}
		// timestamp is less than 60 minutes ago (display 1 hour ago rather than 60 minutes ago)
		else if (this.timestamp < ($timestamp + 3540)) {
			var $minutes = Math.round((this.timestamp - $timestamp) / 60);
			$(element).text(eval(WCF.Language.get('wcf.date.relative.minutes')));
		}
		// timestamp is less than 24 hours ago
		else if (this.timestamp < ($timestamp + 86400)) {
			var $hours = Math.round((this.timestamp - $timestamp) / 3600);
			$(element).text(eval(WCF.Language.get('wcf.date.relative.hours')));
		}
		// timestamp is less than a week ago
		else if (this.timestamp < ($timestamp + 604800)) {
			var $days = Math.round((this.timestamp - $timestamp) / 86400);
			var $string = eval(WCF.Language.get('wcf.date.relative.pastDays'));
			
			// get day of week
			var $dateObj = WCF.Date.Util.getTimezoneDate(($timestamp * 1000), $offset);
			var $dow = $dateObj.getDay();
			
			$(element).text($string.replace(/\%day\%/, WCF.Language.get('__days')[$dow]).replace(/\%time\%/, $time));
		}
		// timestamp is between ~700 million years BC and last week
		else {
			var $string = WCF.Language.get('wcf.date.dateTimeFormat');
			$(element).text($string.replace(/\%date\%/, $date).replace(/\%time\%/, $time));
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
	 * Returns true, if dictionary is empty.
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
		if (typeof parameters === 'undefined') var parameters = {};
		
		var value = this._variables.get(key);
		
		if (typeof value === 'string') {
			// transform strings into template and try to refetch
			this.add(key, new WCF.Template(value));
			return this.get(key, parameters);
		}
		else if (value !== null && typeof value === 'object' && typeof value.fetch !== 'undefined') {
			// evaluate templates
			value = value.fetch(parameters);
		}
		else if (value === null) {
			// return key again
			return key;
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
		this._element = $('#' + $.wcfEscapeID(elementID));
		this._forceSelection = forceSelection;
		this._values = values;
		this._availableLanguages = availableLanguages;
		
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
		// enable DOMNodeInserted event
		WCF.DOMNodeInsertedHandler.enable();
		
		this._element.wrap('<div class="dropdown preInput" />');
		var $wrapper = this._element.parent();
		var $button = $('<p class="button dropdownToggle"><span>' + WCF.Language.get('wcf.global.button.disabledI18n') + '</span></p>').prependTo($wrapper);
		$button.data('target', $wrapper.wcfIdentify()).click($.proxy(this._enable, this));
		
		// insert list
		this._list = $('<ul class="dropdownMenu"></ul>').insertAfter($button);
		
		// add a special class if next item is a textarea
		if ($button.nextAll('textarea').length) {
			$button.addClass('dropdownCaptionTextarea');
		}
		else {
			$button.addClass('dropdownCaption');
			this._element.css('height', $wrapper.outerHeight());
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
		
		if (enableOnInit || this._forceSelection) {
			$button.trigger('click');
			
			// pre-select current language
			this._list.children('li').each($.proxy(function(index, listItem) {
				var $listItem = $(listItem);
				if ($listItem.data('languageID') == this._languageID) {
					$listItem.trigger('click');
				}
			}, this));
		}
		
		WCF.Dropdown.registerCallback($wrapper.wcfIdentify(), $.proxy(this._handleAction, this));
		
		// disable DOMNodeInserted event
		WCF.DOMNodeInsertedHandler.disable();
	},
	
	/**
	 * Handles dropdown actions.
	 * 
	 * @param	jQuery		dropdown
	 * @param	string		action
	 */
	_handleAction: function(dropdown, action) {
		if (action === 'close') {
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
			var $button = $(event.currentTarget);
			$button.next('.dropdownMenu').css({
				top: ($button.outerHeight() - 1) + 'px'
			});
			
			if ($button.getTagName() === 'p') {
				$button = $button.children('span:eq(0)');
			}
			
			$button.addClass('active');
			
			this._isEnabled = true;
			this._insertedDataAfterInit = false;
		}
		
		// toggle list
		if (this._list.is(':visible')) {
			this._closeSelection();
		}
		else {
			this._showSelection();
		}
		
		// discard event
		event.stopPropagation();
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
		if (!this._insertedDataAfterInit) {
			// prevent loop of death
			this._insertedDataAfterInit = true;
			
			this._disable();
		}
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
		this._list.prev('.dropdownToggle').children('span').text(this._availableLanguages[this._languageID]);
		
		// close selection and set focus on input element
		this._closeSelection();
		this._element.blur().focus();
	},
	
	/**
	 * Disables language selection for current element.
	 * 
	 * @param	object		event
	 */
	_disable: function(event) {
		if (this._forceSelection || !this._list || event === undefined) {
			return;
		}
		
		// remove active marking
		this._list.prev('.dropdownToggle').children('span').removeClass('active').text(WCF.Language.get('wcf.global.button.disabledI18n'));
		this._closeSelection();
		
		// update element value
		if (this._values[LANGUAGE_ID]) {
			this._element.val(this._values[LANGUAGE_ID]);
		}
		else {
			// no value for current language found, proceed with empty input
			this._element.val();
		}
		
		this._element.blur();
		this._isEnabled = false;
	},
	
	/**
	 * Prepares language variables on before submit.
	 */
	_submit: function() {
		// insert hidden form elements on before submit
		if (!this._isEnabled) {
			return 0xDEADBEAF;
		}
		
		// fetch active value
		if (this._languageID) {
			this._values[this._languageID] = this._element.val();
		}
		
		var $form = $(this._element.parents('form')[0]);
		var $elementID = this._element.wcfIdentify();
		
		for (var $languageID in this._values) {
			$('<input type="hidden" name="' + $elementID + '_i18n[' + $languageID + ']" value="' + this._values[$languageID] + '" />').appendTo($form);
		}
		
		// remove name attribute to prevent conflict with i18n values
		this._element.removeAttr('name');
	}
});

/**
 * Icon collection used across all JavaScript classes.
 * 
 * @see	WCF.Dictionary
 */
WCF.Icon = {
	/**
	 * list of icons
	 * @var	WCF.Dictionary
	 */
	_icons: new WCF.Dictionary(),
	
	/**
	 * @see	WCF.Dictionary.add()
	 */
	add: function(name, path) {
		this._icons.add(name, path);
	},
	
	/**
	 * @see	WCF.Dictionary.addObject()
	 */
	addObject: function(object) {
		this._icons.addObject(object);
	},
	
	/**
	 * @see	WCF.Dictionary.get()
	 */
	get: function(name) {
		return this._icons.get(name);
	}
};

/**
 * Number utilities.
 */
WCF.Number = {
	/**
	 * Rounds a number to a given number of floating points digits. Defaults to 0.
	 * 
	 * @param	number		number
	 * @param	floatingPoint	number of digits
	 * @return	number
	 */
	round: function (number, floatingPoint) {
		floatingPoint = Math.pow(10, (floatingPoint || 0));
		
		return Math.round(number * floatingPoint) / floatingPoint;
	}
};

/**
 * String utilities.
 */
WCF.String = {
	/**
	 * Adds thousands separators to a given number.
	 * 
	 * @param	mixed		number
	 * @return	string
	 */
	addThousandsSeparator: function(number) {
		var $numberString = String(number);
		var parts = $numberString.split(/[^0-9]+/);
		
		var $decimalPoint = $numberString.match(/[^0-9]+/);
		
		$numberString = parts[0];
		var $decimalPart = '';
		if ($decimalPoint !== null) {
			delete parts[0];
			var $decimalPart = $decimalPoint.join('')+parts.join('');
		}
		if (parseInt(number) >= 1000 || parseInt(number) <= -1000) {
			var $negative = false;
			if (parseInt(number) <= -1000) {
				$negative = true;
				$numberString = $numberString.substring(1);
			}
			var $separator = WCF.Language.get('wcf.global.thousandsSeparator');
			
			if ($separator != null && $separator != '') {
				var $numElements = new Array();
				var $firstPart = $numberString.length % 3;
				if ($firstPart == 0) $firstPart = 3;
				for (var $i = 0; $i < Math.ceil($numberString.length / 3); $i++) {
					if ($i == 0) $numElements.push($numberString.substring(0, $firstPart));
					else {
						var $start = (($i - 1) * 3) + $firstPart;
						$numElements.push($numberString.substring($start, $start + 3));
					}
				}
				$numberString = (($negative) ? ('-') : ('')) + $numElements.join($separator);
			}
		}
		
		return $numberString + $decimalPart;
	},
	
	/**
	 * Escapes special HTML-characters within a string
	 * 
	 * @param	string	string
	 * @return	string
	 */
	escapeHTML: function (string) {
		return string.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
	},
	
	/**
	 * Escapes a String to work with RegExp.
	 *
	 * @see		https://github.com/sstephenson/prototype/blob/master/src/prototype/lang/regexp.js#L25
	 * @param	string	string
	 * @return	string
	 */
	escapeRegExp: function(string) {
		return string.replace(/([.*+?^=!:${}()|[\]\/\\])/g, '\\$1');
	},
	
	/**
	 * Rounds number to given count of floating point digits, localizes decimal-point and inserts thousands-separators
	 * 
	 * @param	mixed	number
	 * @return	string
	 */
	formatNumeric: function(number, floatingPoint) {
		number = String(WCF.Number.round(number, floatingPoint || 2));
		number = number.replace('.', WCF.Language.get('wcf.global.decimalPoint'));
		
		return this.addThousandsSeparator(number);
	},
	
	/**
	 * Makes a string's first character lowercase
	 * 
	 * @param	string		string
	 * @return	string
	 */
	lcfirst: function(string) {
		return string.substring(0, 1).toLowerCase() + string.substring(1);
	},
	
	/**
	 * Makes a string's first character uppercase
	 * 
	 * @param	string		string
	 * @return	string
	 */
	ucfirst: function(string) {
		return string.substring(0, 1).toUpperCase() + string.substring(1);
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
				select: function(event, ui) {
					var $panel = $(ui.panel);
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
						location.hash = '#' + $panel.attr('id');
					}
					
					$container.trigger('tabsselect', event, ui);
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
			this.selectTabs();
			$(window).bind('hashchange', $.proxy(this.selectTabs, this));
			
			if (!this._selectErroneousTab()) {
				this._selectActiveTab();
			}
		}
		
		this._didInit = true;
	},
	
	/**
	 * Force display of first erroneous tab, returns true, if at
	 * least one tab contains an error.
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
					
					$tabMenu = $tabMenu.data('parent').wcfTabs('select', $tabMenu.wcfIdentify());
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
								$tabMenuItem.wcfTabs('select', $tabMenu.data('active'));
							}
							else {
								$tabMenu.wcfTabs('select', $tabMenu.data('active'));
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
	 */
	selectTabs: function() {
		if (location.hash) {
			var $hash = location.hash.substr(1);
			var $subIndex = null;
			if (/-/.test(location.hash)) {
				var $tmp = $hash.split('-');
				$hash = $tmp[0];
				$subIndex = $tmp[1];
			}
			
			// find a container which matches the first part
			for (var $containerID in this._containers) {
				var $tabMenu = this._containers[$containerID];
				if ($tabMenu.wcfTabs('hasAnchor', $hash, false)) {
					if ($subIndex !== null) {
						// try to find child tabMenu
						var $childTabMenu = $tabMenu.find('#' + $.wcfEscapeID($hash) + '.tabMenuContainer');
						if ($childTabMenu.length !== 1) {
							return;
						}
						
						// validate match for second part
						if (!$childTabMenu.wcfTabs('hasAnchor', $subIndex, true)) {
							return;
						}
						
						$childTabMenu.wcfTabs('select', $hash + '-' + $subIndex);
					}
					
					$tabMenu.wcfTabs('select', $hash);
					return;
				}
			}
		}
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
	 * template content
	 * @var	string
	 */
	_template: '',
	
	/**
	 * saved literal tags
	 * @var	WCF.Dictionary
	 */
	_literals: new WCF.Dictionary(),
	
	/**
	 * Prepares template
	 * 
	 * @param	$template		template-content
	 */
	init: function($template) {
		this._template = $template;
		
		// save literal-tags
		this._template = this._template.replace(/\{literal\}(.*?)\{\/literal\}/g, $.proxy(function ($match) {
			// hopefully no one uses this string in one of his templates
			var id = '@@@@@@@@@@@'+Math.random()+'@@@@@@@@@@@';
			this._literals.add(id, $match.replace(/\{\/?literal\}/g, ''));
			
			return id;
		}, this));
	},
	
	/**
	 * Fetches the template with the given variables
	 *
	 * @param	$variables	variables to insert
	 * @return			parsed template
	 */
	fetch: function($variables) {
		var $result = this._template;
		
		// insert them :)
		for (var $key in $variables) {
			$result = $result.replace(new RegExp(WCF.String.escapeRegExp('{$'+$key+'}'), 'g'), WCF.String.escapeHTML(new String($variables[$key])));
			$result = $result.replace(new RegExp(WCF.String.escapeRegExp('{#$'+$key+'}'), 'g'), WCF.String.formatNumeric($variables[$key]));
			$result = $result.replace(new RegExp(WCF.String.escapeRegExp('{@$'+$key+'}'), 'g'), $variables[$key]);
		}
		
		// insert delimiter tags
		$result = $result.replace('{ldelim}', '{').replace('{rdelim}', '}');
		
		// and re-insert saved literals
		return this.insertLiterals($result);
	},
	
	/**
	 * Inserts literals into given string
	 * 
	 * @param	$template	string to insert into
	 * @return			string with inserted literals
	 */
	insertLiterals: function ($template) {
		this._literals.each(function ($pair) {
			$template = $template.replace($pair.key, $pair.value);
		});
		
		return $template;
	},
	
	/**
	 * Compiles this template into javascript-code
	 * 
	 * @return	WCF.Template.Compiled
	 */
	compile: function () {
		var $compiled = this._template;
		
		// escape \ and '
		$compiled = $compiled.replace('\\', '\\\\').replace("'", "\\'");
		
		// parse our variable-tags
		$compiled = $compiled.replace(/\{\$(.*?)\}/g, function ($match) {
			var $name = '$v.' + $match.substring(2, $match.length - 1);
			// trinary operator to maintain compatibility with uncompiled template
			// ($name) ? $name : '$match'
			// -> $v.muh ? $v.muh : '{$muh}'
			return "' + WCF.String.escapeHTML("+ $name + " ? " + $name + " : '" + $match + "') + '";
		}).replace(/\{#\$(.*?)\}/g, function ($match) {
			var $name = '$v.' + $match.substring(3, $match.length - 1);
			// trinary operator to maintain compatibility with uncompiled template
			// ($name) ? $name : '$match'
			// -> $v.muh ? $v.muh : '{$muh}'
			return "' + WCF.String.formatNumeric("+ $name + " ? " + $name + " : '" + $match + "') + '";
		}).replace(/\{@\$(.*?)\}/g, function ($match) {
			var $name = '$v.' + $match.substring(3, $match.length - 1);
			// trinary operator to maintain compatibility with uncompiled template
			// ($name) ? $name : '$match'
			// -> $v.muh ? $v.muh : '{$muh}'
			return "' + ("+ $name + " ? " + $name + " : '" + $match + "') + '";
		});
		
		// insert delimiter tags
		$compiled = $compiled.replace('{ldelim}', '{').replace('{rdelim}', '}');
		
		// escape newlines
		$compiled = $compiled.replace(/(\r\n|\n|\r)/g, '\\n');
		
		// and re-insert saved literals
		return new WCF.Template.Compiled("'" + this.insertLiterals($compiled) + "';");
	}
});

/**
 * Represents a compiled template
 * 
 * @param	compiled		compiled template
 */
WCF.Template.Compiled = Class.extend({
	/**
	 * Compiled template
	 * 
	 * @var	string
	 */
	_compiled: '',
	
	/**
	 * Initializes our compiled template
	 * 
	 * @param	$compiled	compiled template
	 */
	init: function($compiled) {
		this._compiled = $compiled;
	},
	
	/**
	 * @see	WCF.Template.fetch
	 */
	fetch: function($v) {
		return eval(this._compiled);
	}
});

/**
 * Toggles options.
 * 
 * @param	string		element
 * @param	array		showItems
 * @param	array		hideItems
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
	 * Initializes option toggle.
	 * 
	 * @param	string		element
	 * @param	array		showItems
	 * @param	array		hideItems
	 */
	init: function(element, showItems, hideItems) {
		this._element = $('#' + element);
		this._showItems = showItems;
		this._hideItems = hideItems;
		
		// bind event
		this._element.click($.proxy(this._toggle, this));
		
		// execute toggle on init
		this._toggle();
	},
	
	/**
	 * Toggles items.
	 */
	_toggle: function() {
		if (!this._element.attr('checked')) return;
		
		for (var $i = 0, $length = this._showItems.length; $i < $length; $i++) {
			var $item = this._showItems[$i];
			
			$('#' + $item).show();
		}
		
		for (var $i = 0, $length = this._hideItems.length; $i < $length; $i++) {
			var $item = this._hideItems[$i];
			
			$('#' + $item).hide();
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
				this._toggleImage($button, 'wcf.icon.closed');
			}, this));
			$isOpen = false;
		}
		else {
			$target.stop().wcfBlindIn('vertical', $.proxy(function() {
				this._toggleImage($button, 'wcf.icon.opened');
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
	 * @param	string		image
	 */
	_toggleImage: function(button, image) {
		var $icon = WCF.Icon.get(image);
		var $image = button.find('img');
		
		if ($image.length) {
			$image.attr('src', $icon);
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
		
		// validate containers
		var $containers = this._getContainers();
		if ($containers.length == 0) {
			console.debug('[WCF.Collapsible.Remote] Empty container set given, aborting.');
		}
		
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
		
		// initialize each container
		$containers.each($.proxy(function(index, container) {
			var $container = $(container);
			var $containerID = $container.wcfIdentify();
			this._containers[$containerID] = $container;
			
			this._initContainer($containerID);
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
		var $button = $('<a class="collapsibleButton jsTooltip" title="'+WCF.Language.get('wcf.global.button.collapsible')+'"><img src="' + WCF.Icon.get('wcf.icon.' + ($isOpen ? 'opened' : 'closed')) + '" alt="" class="icon16" /></a>').prependTo(buttonContainer);
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
			objectIDs: [ this._getObjectID($containerID) ],
			parameters: $.extend(true, {
				containerID: $containerID,
				currentState: $state,
				newState: $newState
			}, this._getAdditionalParameters($containerID))
		});
		this._proxy.sendRequest();
		
		// set spinner for current button
		this._exchangeIcon($button);
	},
	
	/**
	 * Exchanges button icon.
	 * 
	 * @param	jQuery		button
	 * @param	string		newIcon
	 */
	_exchangeIcon: function(button, newIcon) {
		newIcon = newIcon || WCF.Icon.get('wcf.icon.loading');
		button.find('img').attr('src', newIcon);
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
		this._updateContent($containerID, data.returnValues.content, $newState);
		
		// update icon
		this._exchangeIcon(this._containerData[$containerID].button, WCF.Icon.get('wcf.icon.' + (data.returnValues.isOpen ? 'opened' : 'closed')));
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
			this._exchangeIcon(this._containerData[containerID].button, WCF.Icon.get('wcf.icon.closed'));
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
			objectIDs: [ this._getObjectID($containerID) ],
			parameters: $.extend(true, {
				containerID: $containerID,
				currentState: $currentState,
				newState: $newState
			}, this._getAdditionalParameters($containerID))
		});
		this._proxy.sendRequest();
		
		// exchange icon
		this._exchangeIcon(this._containerData[$containerID].button, WCF.Icon.get('wcf.icon.' + ($newState === 'open' ? 'opened' : 'closed')));
		
		// toggle container
		if ($newState === 'open') {
			this._containerData[$containerID].target.show();
		}
		else {
			this._containerData[$containerID].target.hide();
		}
		
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
		WCF.DOMNodeInsertedHandler.enable();
		this._button = $('<a class="collapsibleButton jsTooltip" title="' + WCF.Language.get('wcf.global.button.collapsible') + '" />').prependTo(this._sidebar);
		this._button.click($.proxy(this._click, this));
		this._buttonHeight = this._button.outerHeight();
		WCF.DOMNodeInsertedHandler.disable();
		
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
			this._mainContainer.removeClass('sidebarCollapsed');
		}
		else {
			this._mainContainer.addClass('sidebarCollapsed');
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
	 * @return	boolean
	 */
	scrollTo: function(element, excludeMenuHeight) {
		if (!element.length) {
			return true;
		}
		
		var $elementOffset = element.getOffsets().top;
		var $documentHeight = $(document).height();
		var $windowHeight = $(window).height();
		
		// handles menu height
		if (excludeMenuHeight) {
			$elementOffset = Math.max($elementOffset - $('#topMenu').outerHeight(), 0);
		}
		
		if ($elementOffset > $documentHeight - $windowHeight) {
			$elementOffset = $documentHeight - $windowHeight;
			if ($elementOffset < 0) {
				$elementOffset = 0;
			}
		}
		
		$('html,body').animate({ scrollTop: $elementOffset }, 400, function (x, t, b, c, d) {
			return -c * ( ( t = t / d - 1 ) * t * t * t - 1) + b;
		});
		
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
		var $top = $elementOffsets.top + $elementDimensions.height + 7;
		
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
	 * @var	WCF.Dictionary
	 */
	_callbacks: new WCF.Dictionary(),
	
	/**
	 * true if DOMNodeInserted event should be ignored
	 * @var	boolean
	 */
	_discardEvent: true,
	
	/**
	 * prevent infinite loop if a callback manipulates DOM
	 * @var	boolean
	 */
	_isExecuting: false,
	
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
			console.debug("[WCF.DOMNodeInsertedHandler] identifier '" + identifier + "' is already bound to a callback");
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
		
		$(document).bind('DOMNodeInserted', $.proxy(this._executeCallbacks, this));
		
		this._isListening = true;
	},
	
	/**
	 * Executes callbacks on click.
	 */
	_executeCallbacks: function(event) {
		if (this._discardEvent || this._isExecuting) return;
		
		// do not track events while executing callbacks
		this._isExecuting = true;
		
		this._callbacks.each(function(pair) {
			// execute callback
			pair.value(event);
		});
		
		// enable listener again
		this._isExecuting = false;
	},
	
	/**
	 * Disables DOMNodeInsertedHandler, should be used after you've enabled it.
	 */
	disable: function() {
		this._discardEvent = true;
	},
	
	/**
	 * Enables DOMNodeInsertedHandler, should be used if you're inserting HTML (e.g. via AJAX)
	 * which might contain event-related elements. You have to disable the DOMNodeInsertedHandler
	 * once you've enabled it, if you fail it will cause an infinite loop!
	 */
	enable: function() {
		this._discardEvent = false;
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
			showLoadingOverlay: (showLoadingOverlay === false ? false : true),
			success: $.proxy(this._success, this)
		});
		
		if (this._searchInput.getTagName() === 'input') {
			this._searchInput.attr('autocomplete', 'off');
		}
	},
	
	/**
	 * Blocks execution of 'Enter' event.
	 * 
	 * @param	object		event
	 */
	_keyDown: function(event) {
		if (event.which === 13) {
			event.preventDefault();
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
		
		// no items available, abort
		if (!$.getLength(data.returnValues)) {
			return;
		}
		
		for (var $i in data.returnValues) {
			var $item = data.returnValues[$i];
			
			this._createListItem($item);
		}
		
		this._list.parent().addClass('dropdownOpen');
		WCF.Dropdown.setAlignment(undefined, this._list);
		
		WCF.CloseOverlayHandler.addCallback('WCF.Search.Base', $.proxy(function() { this._clearList(true); }, this));
	},
	
	/**
	 * Creates a new list item.
	 * 
	 * @param	object		item
	 * @return	jQuery
	 */
	_createListItem: function(item) {
		var $listItem = $('<li><span>' + item.label + '</span></li>').appendTo(this._list);
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
					this._searchInput.attr('value', this._oldSearchString.join(', '));
					
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
		
		this._list.parent().removeClass('dropdownOpen').end().empty();
		
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
		
		// insert item type
		if (this._includeUserGroups) $('<img src="' + WCF.Icon.get('wcf.icon.user' + (item.type == 'group' ? 's' : '')) + '" alt="" class="icon16" style="margin-right: 4px;" />').prependTo($listItem.children('span:eq(0)'));
		$listItem.data('type', item.type);
		
		return $listItem;
	}
});

/**
 * Namespace for system-related classes.
 */
WCF.System = { };

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
		this._message = message;
		this._overlay = $('#systemNotification');
		
		if (!this._overlay.length) {
			this._overlay = $('<div id="systemNotification"><p></p></div>').appendTo(document.body);
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
		
		this._overlay.addClass('open');
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
		
		this._overlay.removeClass('open');
		
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
		if (this._dialog === null) {
			this._createDialog();
		}
		
		this._dialog.find('#wcfSystemConfirmationContent').empty().hide();
		if (template && template.length) {
			template.appendTo(this._dialog.find('#wcfSystemConfirmationContent').show());
		}
		
		this._dialog.find('p').html(message);
		this._dialog.wcfDialog({
			onClose: $.proxy(this._close, this),
			onShow: $.proxy(this._show, this),
			title: WCF.Language.get('wcf.global.confirmation.title')
		});
		
		this._visible = true;
	},
	
	/**
	 * Creates the confirmation dialog on first use.
	 */
	_createDialog: function() {
		this._dialog = $('<div id="wcfSystemConfirmation" class="systemConfirmation"><p /><div id="wcfSystemConfirmationContent" /></div>').hide().appendTo(document.body);
		var $formButtons = $('<div class="formSubmit" />').appendTo(this._dialog);
		
		$('<button class="buttonPrimary">' + WCF.Language.get('wcf.global.confirmation.confirm') + '</button>').data('action', 'confirm').click($.proxy(this._click, this)).appendTo($formButtons);
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
			console.debug($element.data());
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
			this._pageNo = $('<input type="number" id="jsPageNavigationPageNo" value="1" min="1" max="1" class="long" />').keyup($.proxy(this._keyUp, this)).appendTo($fieldset.find('dd'));
			this._description = $('<small></small>').insertAfter(this._pageNo);
			var $formSubmit = $('<div class="formSubmit" />').appendTo(this._dialog);
			this._button = $('<button class="buttonPrimary">' + WCF.Language.get('wcf.global.button.submit') + '</button>').click($.proxy(this._submit, this)).appendTo($formSubmit);
		}
		
		this._button.enable();
		this._description.html(WCF.Language.get('wcf.global.page.jumpTo.description').replace(/#pages#/, this._elements[this._elementID].data('pages')));
		this._pageNo.val('1').attr('max', this._elements[this._elementID].data('pages'));
		
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
		new WCF.PeriodicalExecuter(function() {
			new WCF.Action.Proxy({
				autoSend: true,
				data: {
					actionName: 'keepAlive',
					className: 'wcf\\data\\session\\SessionAction'
				},
				showLoadingOverlay: false
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
			
			// store reference
			self._elements[$elementID] = $element;
		});
		
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
		
		this._setOptions();
		
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
		
		// build drop down
		if (!this._dropdowns[$elementID]) {
			var $trigger = this._getTriggerElement(this._elements[$elementID]).addClass('dropdownToggle').wrap('<span class="dropdown" />');
			var $dropdown = $trigger.parent('span');
			$trigger.data('target', $dropdown.wcfIdentify());
			this._dropdowns[$elementID] = $('<ul class="dropdownMenu" style="top: ' + ($dropdown.outerHeight() + 14) + 'px;" />').insertAfter($trigger);
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
			this._dropdowns[$elementID].parent('span').addClass('dropdownOpen');
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
	 */
	init: function(buttonSelector, fileListSelector, className, options) {
		this._buttonSelector = buttonSelector;
		this._fileListSelector = fileListSelector;
		this._className = className;
		this._options = $.extend(true, {
			action: 'upload',
			multiple: false,
			url: 'index.php/AJAXUpload/?t=' + SECURITY_TOKEN + SID_ARG_2ND
		}, options);
		
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
			this._fileUpload = $('<input type="file" name="'+this._name+'" '+(this._options.multiple ? 'multiple="true" ' : '')+'/>');
			this._fileUpload.change($.proxy(this._upload, this));
			var $button = $('<p class="button uploadButton"><span>'+WCF.Language.get('wcf.global.button.upload')+'</span></p>');
			$button.append(this._fileUpload);
		}
		else {
			var $button = $('<p class="button"><span>Upload</span></p>');
			$button.click($.proxy(this._showOverlay, this));
		}
		
		this._insertButton($button);
	},
	
	/**
	 * Inserts the upload button.
	 */
	_insertButton: function(button) {
		this._buttonSelector.append(button);
	},
	
	/**
	 * Callback for file uploads.
	 */
	_upload: function() {
		var $files = this._fileUpload.prop('files');
		
		if ($files.length > 0) {
			var $fd = new FormData();
			var self = this;
			var $uploadID = this._uploadMatrix.length;
			this._uploadMatrix[$uploadID] = [];
			
			for (var $i = 0; $i < $files.length; $i++) {
				var $li = this._initFile($files[$i]);
				$li.data('filename', $files[$i].name);
				this._uploadMatrix[$uploadID].push($li);
				$fd.append('__files[]', $files[$i]);
			}
			$fd.append('actionName', this._options.action);
			$fd.append('className', this._className);
			var $additionalParameters = this._getParameters();
			for (var $name in $additionalParameters) {
				$fd.append('parameters['+$name+']', $additionalParameters[$name]);
			}
			
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
	 * Callback for success event
	 */
	_success: function(uploadID, data) {
		console.debug(data);
	},
	
	/**
	 * Callback for error event
	 */
	_error: function(jqXHR, textStatus, errorThrown) {
		console.debug(jqXHR.responseText);
	},
	
	/**
	 * Callback for progress event
	 */
	_progress: function(uploadID, event) {
		var $percentComplete = Math.round(event.loaded * 100 / event.total);
		
		for (var $i = 0; $i < this._uploadMatrix[uploadID].length; $i++) {
			this._uploadMatrix[uploadID][$i].find('progress').attr('value', $percentComplete);
		}
	},
	
	/**
	 * Returns additional parameters.
	 */
	_getParameters: function() {
		return {};
	},
	
	_initFile: function(file) {
		var $li = $('<li>'+file.name+' ('+file.size+')<progress max="100"></progress></li>');
		this._fileListSelector.append($li);
		
		return $li;
	},
	
	/**
	 * Shows the fallback overlay (work in progress)
	 */
	_showOverlay: function() {
		var $self = this;
		if (!this._overlay) {
			// create overlay
			this._overlay = $('<div style="display: none;"><form enctype="multipart/form-data" method="post" action="'+this._options.url+'"><dl><dt><label for="__fileUpload">File</label></dt><dd><input type="file" id="__fileUpload" name="'+this._name+'" '+(this._options.multiple ? 'multiple="true" ' : '')+'/></dd></dl><div class="formSubmit"><input type="submit" value="Upload" accesskey="s" /></div></form></div>');
		}
		
		// create iframe
		var $iframe = $('<iframe style="display: none"></iframe>'); // width: 300px; height: 100px; border: 5px solid red
		$iframe.attr('name', $iframe.wcfIdentify());
		$('body').append($iframe);
		this._overlay.find('form').attr('target', $iframe.wcfIdentify());
		
		// add events (iframe onload)
		$iframe.load(function() {
			console.debug('iframe ready');
			console.debug($iframe.contents());
		});
		
		this._overlay.find('form').submit(function() {
			$iframe.data('loading', true);
			$self._overlay.wcfDialog('close');
		});
		
		this._overlay.wcfDialog({
			title: 'Upload',
			onClose: function() {
				if (!$iframe.data('loading')) {
					$iframe.remove();
				}
			}
		});
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
			$('#' + this._containerID + ' > .sortableList').wcfNestedSortable(this._options);
		}
		
		if (this._className) {
			this._container.find('.formSubmit > button[data-type="submit"]').click($.proxy(this._submit, this));
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
			this._notification = new WCF.System.Notification(WCF.Language.get('wcf.global.form.edit.success'));
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
		show: 250,
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
			show: 250,
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
		
		this._popover = $('<div class="popover"><div class="popoverContent"></div></div>').hide().appendTo(document.body);
		this._popoverContent = this._popover.children('.popoverContent:eq(0)');
		this._popover.hover($.proxy(this._overPopover, this), $.proxy(this._out, this));
		
		this._initContainers();
		WCF.DOMNodeInsertedHandler.addCallback('WCF.Popover.'+selector, $.proxy(this._initContainers, this));
	},
	
	/**
	 * Initializes all element triggers.
	 */
	_initContainers: function() {
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
				
				if ($element.getTagName() === 'a' && $element.attr('href')) {
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
			this._loadContent();
		}
		else {
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
					self._popoverContent.html(self._data[elementID].content).css({ opacity: 0 }).animate({ opacity: 1 }, 200);
				});
			}
			else {
				// insert new content
				this._popoverContent.html(this._data[elementID].content);
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
			this._searchInput.keydown($.proxy(this._keyDown, this));
		}
	},
	
	/**
	 * Handles the key down event.
	 * 
	 * @param	object		event
	 */
	_keyDown: function(event) {
		if (event === null || (event.which === 13 || event.which === 188)) {
			var $value = $.trim(this._searchInput.val());
			if ($value === '') {
				return true;
			}
			
			this.addItem({
				objectID: 0,
				label: $value
			});
			
			// reset input
			this._searchInput.val('');
			
			if (event !== null) {
				event.stopPropagation();
			}
			
			return false;
		}
		
		return true;
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
		
		var $listItem = $('<li class="badge">' + data.label + '</li>').data('objectID', data.objectID).data('label', data.label).appendTo(this._itemList);
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
			
			// show dialog
			this._dialog.wcfDialog({
				title: WCF.Language.get('wcf.sitemap.title')
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
			var $item = $('<li class="boxFlag"><a class="box24"><div class="framed"><img src="' + $language.iconPath + '" alt="" class="iconFlag" /></div> <hgroup><h1>' + $language.languageName + '</h1></hgroup></a></li>').appendTo($dropdownMenu);
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
		if (data.returnValues.actionName === 'changeStyle') {
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
 * WCF implementation for nested sortables.
 */
$.widget("ui.wcfNestedSortable", $.extend({}, $.ui.nestedSortable.prototype, {
	_clearEmpty: function(item) {
		/* Does nothing because we want to keep empty lists */
	}
}));

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
	 * dialog content dimensions
	 * @var	object
	 */
	_contentDimensions: null,
	
	/**
	 * rendering state
	 * @var	boolean
	 */
	_isRendering: false,
	
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
		
		// AJAX support
		ajax: false,
		data: { },
		showLoadingOverlay: true,
		success: null,
		type: 'POST',
		url: 'index.php/AJAXProxy/?t=' + SECURITY_TOKEN + SID_ARG_2ND,
		
		// event callbacks
		onClose: null,
		onShow: null
	},
	
	/**
	 * Initializes a new dialog.
	 */
	_init: function() {
		if (this.options.closeButtonLabel === null) {
			this.options.closeButtonLabel = WCF.Language.get('wcf.global.close');
		}
		
		if (this.options.ajax) {
			new WCF.Action.Proxy({
				autoSend: true,
				data: this.options.data,
				showLoadingOverlay: this.options.showLoadingOverlay,
				success: $.proxy(this._success, this),
				type: this.options.type,
				url: this.options.url
			});
			
			// force open if using AJAX
			this.options.autoOpen = true;
			
			// apply loading overlay
			this._content.addClass('overlayLoading');
		}
		
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
		// create dialog container
		this._container = $('<div class="dialogContainer" />').hide().css({ zIndex: this.options.zIndex }).appendTo(document.body);
		this._titlebar = $('<header class="dialogTitlebar" />').hide().appendTo(this._container);
		this._title = $('<span class="dialogTitle" />').hide().appendTo(this._titlebar);
		this._closeButton = $('<a class="dialogCloseButton"><span /></a>').click($.proxy(this.close, this)).hide().appendTo(this._titlebar);
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
				this._overlay = $('<div id="jsWcfDialogOverlay" class="dialogOverlay" />').css({ height: '100%', zIndex: 399 }).appendTo(document.body);
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
	 * Handles successful AJAX requests.
	 *
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		if (this._isOpen) {
			// initialize dialog content
			this._initDialog(data);

			// remove loading overlay
			this._content.removeClass('overlayLoading');

			if (this.options.success !== null && $.isFunction(this.options.success)) {
				this.options.success(data, textStatus, jqXHR);
			}
		}
	},
	
	/**
	 * Initializes dialog content if applicable.
	 * 
	 * @param	object		data
	 */
	_initDialog: function(data) {
		// insert template
		if (this._getResponseValue(data, 'template')) {
			this._content.children().html(this._getResponseValue(data, 'template'));
			this.render();
		}
		
		// set title
		if (this._getResponseValue(data, 'title')) {
			this._setOption('title', this._getResponseValue(data, 'title'));
		}
	},
	
	/**
	 * Returns a response value, taking care of different object
	 * structure returned by AJAXProxy.
	 * 
	 * @param	object		data
	 * @param	string		key
	 */
	_getResponseValue: function(data, key) {
		if (data.returnValues && data.returnValues[key]) {
			return data.returnValues[key];
		}
		else if (data[key]) {
			return data[key];
		}
		
		return null;
	},
	
	/**
	 * Opens this dialog.
	 */
	open: function() {
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
	 * Returns true, if dialog is visible.
	 * 
	 * @return	boolean
	 */
	isOpen: function() {
		return this._isOpen;
	},
	
	/**
	 * Closes this dialog.
	 */
	close: function() {
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
		if (!this.isOpen()) {
			// temporarily display container
			this._container.show();
		}
		else {
			// remove fixed content dimensions for calculation
			this._content.css({
				height: 'auto',
				width: 'auto'
			});
		}
		
		// force content to be visible
		this._content.children().each(function() {
			$(this).show();
		});
		
		// handle multiple rendering requests
		if (this._isRendering) {
			// stop current process
			this._container.stop();
			this._content.stop();
			
			// set dialog to be fully opaque, should prevent weird bugs in WebKit
			this._container.show().css('opacity', 1.0);
		}
		
		if (this._content.find('.formSubmit').length) {
			this._content.addClass('dialogForm');
		}
		else {
			this._content.removeClass('dialogForm');
		}
		
		// calculate dimensions
		var $windowDimensions = $(window).getDimensions();
		var $containerDimensions = this._container.getDimensions('outer');
		var $contentDimensions = this._content.getDimensions();
		
		// calculate maximum content height
		var $heightDifference = $containerDimensions.height - $contentDimensions.height;
		var $maximumHeight = $windowDimensions.height - $heightDifference;
		this._content.css({ maxHeight: $maximumHeight + 'px' });
		
		// re-caculate values if container height was previously limited
		if ($maximumHeight < $contentDimensions.height) {
			$containerDimensions = this._container.getDimensions('outer');
		}
		
		// handle multiple rendering requests
		if (this._isRendering) {
			// use current dimensions as previous ones
			this._contentDimensions = this._getContentDimensions($maximumHeight);
		}
		
		// calculate new dimensions
		$contentDimensions = this._getContentDimensions($maximumHeight);
		
		// move container
		var $leftOffset = Math.round(($windowDimensions.width - $containerDimensions.width) / 2);
		var $topOffset = Math.round(($windowDimensions.height - $containerDimensions.height) / 2);
		
		// place container at 20% height if possible
		var $desiredTopOffset = Math.round(($windowDimensions.height / 100) * 20);
		if ($desiredTopOffset < $topOffset) {
			$topOffset = $desiredTopOffset;
		}
		
		if (!this.isOpen()) {
			// hide container again
			this._container.hide();
			
			// apply offset
			this._container.css({
				left: $leftOffset + 'px',
				top: $topOffset + 'px'
			});
			
			// save current dimensions
			this._contentDimensions = $contentDimensions;
			
			// force dimensions
			this._content.css({
				height: this._contentDimensions.height + 'px',
				width: this._contentDimensions.width + 'px'
			});
			
			// fade in container
			this._container.wcfFadeIn($.proxy(function() {
				this._isRendering = false;
			}));
		}
		else {
			// save reference (used in callback)
			var $content = this._content;
			
			// force previous dimensions
			$content.css({
				height: this._contentDimensions.height + 'px',
				width: this._contentDimensions.width + 'px'
			});
			
			// apply new dimensions
			$content.animate({
				height: ($contentDimensions.height) + 'px',
				width: ($contentDimensions.width) + 'px'
			}, 300, function() {
				// remove static dimensions
				$content.css({
					height: 'auto',
					width: 'auto'
				});
			});
			
			// store new dimensions
			this._contentDimensions = $contentDimensions;
			
			// move container
			this._isRendering = true;
			this._container.animate({
				left: $leftOffset + 'px',
				top: $topOffset + 'px'
			}, 300, $.proxy(function() {
				this._isRendering = false;
			}, this));
		}
		
		if (this.options.onShow !== null) {
			this.options.onShow();
		}
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
		if ($contentDimensions.height > maximumHeight) {
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
		
		$.ui.tabs.prototype.select.apply(this, arguments);
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
	 * Returns true, if identifier is used by an anchor.
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
		
		// icons
		previousIcon: null,
		arrowDownIcon: null,
		nextIcon: null,
		
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
		if (this.options.previousIcon === null) this.options.previousIcon = WCF.Icon.get('wcf.icon.circleArrowLeft');
		if (this.options.nextIcon === null) this.options.nextIcon = WCF.Icon.get('wcf.icon.circleArrowRight');
		if (this.options.arrowDownIcon === null) this.options.arrowDownIcon = WCF.Icon.get('wcf.icon.arrowDown');
		
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
			
			var $pageList = $('<ul></ul>');
			this.element.append($pageList);
			
			var $previousElement = $('<li></li>').addClass('button skip');
			$pageList.append($previousElement);
			
			if (this.options.activePage > 1) {
				var $previousLink = $('<a' + ((this.options.previousPage != null) ? (' title="' + this.options.previousPage + '"') : ('')) + '></a>');
				$previousElement.append($previousLink);
				this._bindSwitchPage($previousLink, this.options.activePage - 1);
				
				var $previousImage = $('<img src="' + this.options.previousIcon + '" alt="" />');
				$previousLink.append($previousImage);
			}
			else {
				var $previousImage = $('<img src="' + this.options.previousIcon + '" alt="" />');
				$previousElement.append($previousImage);
				$previousElement.addClass('disabled');
				$previousImage.addClass('disabled');
			}
			$previousImage.addClass('icon16');
			
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
					$('<li class="button jumpTo"><a title="' + WCF.Language.get('wcf.global.page.jumpTo') + '" class="jsTooltip">…</a></li>').appendTo($pageList);
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
					$('<li class="button jumpTo"><a title="' + WCF.Language.get('wcf.global.page.jumpTo') + '" class="jsTooltip">…</a></li>').appendTo($pageList);
					$hasHiddenPages = true;
				}
			}
			
			// add last page
			$pageList.append(this._renderLink(this.options.maxPage));
			
			// add next button
			var $nextElement = $('<li></li>').addClass('button skip');
			$pageList.append($nextElement);
			
			if (this.options.activePage < this.options.maxPage) {
				var $nextLink = $('<a' + ((this.options.nextPage != null) ? (' title="' + this.options.nextPage + '"') : ('')) + '></a>');
				$nextElement.append($nextLink);
				this._bindSwitchPage($nextLink, this.options.activePage + 1);
				
				var $nextImage = $('<img src="' + this.options.nextIcon + '" alt="" />');
				$nextLink.append($nextImage);
			}
			else {
				var $nextImage = $('<img src="' + this.options.nextIcon + '" alt="" />');
				$nextElement.append($nextImage);
				$nextElement.addClass('disabled');
				$nextImage.addClass('disabled');
			}
			$nextImage.addClass('icon16');
			
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
 * Encapsulate eval() within an own function to prevent problems
 * with optimizing and minifiny JS.
 * 
 * @param	mixed		expression
 * @returns	mixed
 */
function wcfEval(expression) {
	return eval(expression);
}

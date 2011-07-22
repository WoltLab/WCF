/**
 * Class and function collection for WCF
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

/**
 * Initialize WCF namespace
 */
var WCF = {};

/**
 * Extends jQuery with additional methods.
 */
$.extend(true, {
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
	 * Applies a grow-effect by resizing element while moving the
	 * element appropriately
	 * 
	 * @param	object		data
	 * @param	object		options
	 * @return	jQuery
	 */
	wcfGrow: function(data, options) {
		// create temporarily element to determine dimensions
		var $tempElementID = WCF.getRandomID();
		$('body').append('<div id="' + $tempElementID + '" class="wcfDimensions">' + data.content + '</div>');
		var $tempElement = $('#' + $tempElementID);
		
		// get content dimensions
		var $dimensions = $tempElement.getDimensions();
		
		// remove temporarily element
		$tempElement.empty().remove();
		
		// move parent element, used if applying effect on dialogs
		if (!data.parent) {
			data.parent = this;
		}
		
		// calculate values for grow-effect
		var $borderHeight = parseInt(data.parent.css('borderTopWidth')) + parseInt(data.parent.css('borderBottomWidth'));
		var $borderWidth = parseInt(data.parent.css('borderLeftWidth')) + parseInt(data.parent.css('borderRightWidth'));
		
		var $windowDimensions = $(window).getDimensions();
		var $leftOffset = Math.round(($windowDimensions.width - ($dimensions.width + $borderWidth)) / 2);
		var $topOffset = Math.round(($windowDimensions.height - ($dimensions.height + $borderHeight)) / 2);
		
		data.parent.makePositioned('fixed', false);
		data.parent.animate({
			left: $leftOffset + 'px',
			top: $topOffset + 'px'
		}, options);
		
		return this.animate({
			height: $dimensions.height,
			width: $dimensions.width
		}, options);
	},
	
	/**
	 * Shows an element by sliding and fading it into viewport.
	 * 
	 * @param	string		direction
	 * @param	object		callback
	 * @returns	jQuery
	 */
	wcfDropIn: function(direction, callback) {
		if (!direction) direction = 'up';
		
		return this.show(WCF.getEffect(this.getTagName(), 'drop'), { direction: direction }, 600, callback);
	},
	
	/**
	 * Hides an element by sliding and fading it out the viewport.
	 * 
	 * @param	string		direction
	 * @param	object		callback
	 * @returns	jQuery
	 */
	wcfDropOut: function(direction, callback) {
		if (!direction) direction = 'down';
		
		return this.hide(WCF.getEffect(this.getTagName(), 'drop'), { direction: direction }, 600, callback);
	},
	
	/**
	 * Shows an element by blinding it up.
	 * 
	 * @param	string		direction
	 * @param	object		callback
	 * @returns	jQuery
	 */
	wcfBlindIn: function(direction, callback) {
		if (!direction) direction = 'vertical';
		
		return this.show(WCF.getEffect(this.getTagName(), 'blind'), { direction: direction }, 200, callback);
	},
	
	/**
	 * Hides an element by blinding it down.
	 * 
	 * @param	string		direction
	 * @param	object		callback
	 * @returns	jQuery
	 */
	wcfBlindOut: function(direction, callback) {
		if (!direction) direction = 'vertical';
		
		return this.hide(WCF.getEffect(this.getTagName(), 'blind'), { direction: direction }, 200, callback);
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
	}
});

/**
 * WoltLab Community Framework core methods
 */
$.extend(WCF, {
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
	 */
	showAJAXDialog: function(dialogID, resetDialog) {
		if (!dialogID) {
			dialogID = this.getRandomID();
		}
		
		if (!$.wcfIsset(dialogID)) {
			$('body').append($('<div id="' + dialogID + '"></div>'));
		}
		
		var dialog = $('#' + $.wcfEscapeID(dialogID));
		
		if (resetDialog) {
			dialog.empty();
		}
		
		dialog.addClass('overlayLoading');
		
		var dialogOptions = arguments[2] || {};
		dialog.wcfAJAXDialog(dialogOptions);
	},
	
	/**
	 * Shows a modal dialog.
	 * @param	string		dialogID
	 */
	showDialog: function(dialogID) {
		// we cannot work with a non-existant dialog, if you wish to
		// load content via AJAX, see showAJAXDialog() instead
		if (!$.wcfIsset(dialogID)) return;
		
		var $dialog = $('#' + $.wcfEscapeID(dialogID));
		
		var dialogOptions = arguments[2] || {};
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
 * Provides a simple call for periodical executed functions. Based upon
 * ideas by Prototype's PeriodicalExecuter.
 * 
 * @see		https://github.com/sstephenson/prototype/blob/master/src/prototype/lang/periodical_executer.js
 * @param	function		callback
 * @param	integer			delay
 */
WCF.PeriodicalExecuter = function(callback, delay) { this.init(callback, delay); };
WCF.PeriodicalExecuter.prototype = {
	/**
	 * Initializes a periodical executer.
	 * 
	 * @param	function		callback
	 * @param	integer			delay
	 */
	init: function(callback, delay) {
		this.callback = callback;
		this.delay = delay;
		this.loop = true;
		
		this.intervalID = setInterval($.proxy(this._execute, this), this.delay);
	},
	
	/**
	 * Executes callback.
	 */
	_execute: function() {
		this.callback(this);
		
		if (!this.loop) {
			clearInterval(this.intervalID);
		}
	},
	
	/**
	 * Terminates loop.
	 */
	stop: function() {
		this.loop = false;
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
WCF.Action.Proxy = function(options) { this.init(options); };
WCF.Action.Proxy.prototype = {
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
			success: null,
			type: 'POST',
			url: 'index.php?action=AJAXProxy&t=' + SECURITY_TOKEN + SID_ARG_2ND
		}, options);
		
		this.confirmationDialog = null;
		this.loading = null;
		
		// send request immediately after initialization
		if (this.options.autoSend) {
			this.sendRequest();
		}
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
			this.options.init();
		}
		
		$('<div id="actionProxyLoading" style="display: none;">Loading …</div>').appendTo($('body'));
		this.loading = $('#actionProxyLoading');
		this.loading.wcfDropIn();
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
			if ($.isFunction(this.options.failure)) {
				this.options.failure(jqXHR, textStatus, errorThrown, data);
			}
			
			var $randomID = WCF.getRandomID();
			$('<div id="' + $randomID + '" title="HTTP/1.0 ' + jqXHR.status + ' ' + errorThrown + '"><p>Der Server antwortete: ' + data.message + '.</p></div>').wcfDialog();
		}
		// failed to parse JSON
		catch (e) {
			var $randomID = WCF.getRandomID();
			$('<div id="' + $randomID + '" title="HTTP/1.0 ' + jqXHR.status + ' ' + errorThrown + '"><p>Der Server antwortete: ' + jqXHR.responseText + '.</p></div>').wcfDialog();
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
		
		this.loading.wcfDropOut('up');
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
};

/**
 * Basic implementation for simple proxy access using bound elements.
 * 
 * @param	object		options
 * @param	object		callbacks
 */
WCF.Action.SimpleProxy = function(options, callbacks) { this.init(options, callbacks); };
WCF.Action.SimpleProxy.prototype = {
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
};

/**
 * Basic implementation for AJAXProxy-based deletion.
 * 
 * @param	string		className
 * @param	jQuery		containerList
 */
WCF.Action.Delete = function(className, containerList) { this.init(className, containerList); };
WCF.Action.Delete.prototype = {
	/**
	 * Initializes 'delete'-Proxy.
	 * 
	 * @param	string		className
	 * @param	jQuery		containerList
	 */
	init: function(className, containerList) {
		if (!containerList.length) return;
		this.containerList = containerList;
		this.className = className;
		
		// initialize proxy
		var options = {
			success: $.proxy(this._success, this)
		};
		this.proxy = new WCF.Action.Proxy(options);
		
		// bind event listener
		this.containerList.each($.proxy(function(index, container) {
			$(container).find('.deleteButton').bind('click', $.proxy(this._click, this));
		}, this));
	},
	
	/**
	 * Sends AJAX request.
	 * 
	 * @param	object		event
	 */
	_click: function(event) {
		var $target = $(event.target);
		
		if ($target.data('confirmMessage')) {
			if (confirm($target.data('confirmMessage'))) {
				this._sendRequest($target);
			}
		}
		else {
			this._sendRequest($target);
		}
		
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
		// remove items
		this.containerList.each(function(index, container) {
			var $objectID = $(container).find('.deleteButton').data('objectID');
			if (WCF.inArray($objectID, data.objectIDs)) {
				$(container).wcfBlindOut('up', function() {
					$(container).empty().remove();
				}, container);
			}
		});
	}
};

/**
 * Basic implementation for AJAXProxy-based toggle actions.
 * 
 * @param	string		className
 * @param	jQuery		containerList
 */
WCF.Action.Toggle = function(className, containerList) { this.init(className, containerList); };
WCF.Action.Toggle.prototype = {
	/**
	 * Initializes 'toggle'-Proxy
	 * 
	 * @param	string		className
	 * @param	jQuery		containerList
	 */
	init: function(className, containerList) {
		if (!containerList.length) return;
		this.containerList = containerList;
		this.className = className;
		
		// initialize proxy
		var options = {
			success: $.proxy(this._success, this)
		};
		this.proxy = new WCF.Action.Proxy(options);
		
		// bind event listener
		this.containerList.each($.proxy(function(index, container) {
			$(container).find('.toggleButton').bind('click', $.proxy(this._click, this));
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
		// remove items
		this.containerList.each(function(index, container) {
			var $toggleButton = $(container).find('.toggleButton');
			if (WCF.inArray($toggleButton.data('objectID'), data.objectIDs)) {
				$(container).wcfHighlight();
				
				// toggle icon source
				$toggleButton.attr('src', function() {
					if (this.src.match(/enabled(S|M|L)\.png$/)) {
						return this.src.replace(/enabled(S|M|L)\.png$/, 'disabled$1\.png');
					}
					else {
						return this.src.replace(/disabled(S|M|L)\.png$/, 'enabled$1\.png');
					}
				});
				// toogle icon title
				$toggleButton.attr('title', function() {
					if (this.src.match(/enabled(S|M|L)\.png$/)) {
						return $(this).data('disableMessage');
					}
					else {
						return $(this).data('enableMessage');
					}
				});
			}
		});
	}
};

/**
 * Namespace for date-related functions.
 */
WCF.Date = {};

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
WCF.Date.Time = function() { this.init(); };
WCF.Date.Time.prototype = {
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
		
		// timestamp is less than 60 minutes ago (display 1 hour ago rather than 60 minutes ago)
		if (this.timestamp < ($timestamp + 3540)) {
			var $minutes = Math.round((this.timestamp - $timestamp) / 60);
			$(element).text(eval(WCF.Language.get('wcf.global.date.relative.minutes')));
		}
		// timestamp is less than 24 hours ago
		else if (this.timestamp < ($timestamp + 86400)) {
			var $hours = Math.round((this.timestamp - $timestamp) / 3600);
			$(element).text(eval(WCF.Language.get('wcf.global.date.relative.hours')));
		}
		// timestamp is less than a week ago
		else if (this.timestamp < ($timestamp + 604800)) {
			var $days = Math.round((this.timestamp - $timestamp) / 86400);
			var $string = eval(WCF.Language.get('wcf.global.date.relative.pastDays'));
		
			// get day of week
			var $dateObj = WCF.Date.Util.getTimezoneDate(($timestamp * 1000), $offset);
			var $dow = $dateObj.getDay();
			
			$(element).text($string.replace(/\%day\%/, WCF.Language.get('__days')[$dow]).replace(/\%time\%/, $time));
		}
		// timestamp is between ~700 million years BC and last week
		else {
			var $string = WCF.Language.get('wcf.global.date.dateTimeFormat');
			$(element).text($string.replace(/\%date\%/, $date).replace(/\%time\%/, $time));
		}
	}
};

/**
 * Hash-like dictionary. Based upon idead from Prototype's hash
 * 
 * @see	https://github.com/sstephenson/prototype/blob/master/src/prototype/lang/hash.js
 */
WCF.Dictionary = function() { this.init(); };
WCF.Dictionary.prototype = {
	/**
	 * Initializes a new dictionary.
	 */
	init: function() {
		this.variables = { };
	},
	
	/**
	 * Adds an entry.
	 * 
	 * @param	string		key
	 * @param	mixed		value
	 */
	add: function(key, value) {
		this.variables[key] = value;
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
			return this.variables[key];
		}
		
		return null;
	},
	
	/**
	 * Returns true if given key is a valid entry.
	 * 
	 * @param	string		key
	 */
	isset: function(key) {
		return this.variables.hasOwnProperty(key);
	},
	
	/**
	 * Removes an entry.
	 * 
	 * @param	string		key
	 */
	remove: function(key) {
		delete this.variables[key];
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
		
		for (var $key in this.variables) {
			var $value = this.variables[$key];
			var $pair = {
				key: $key,
				value: $value
			};
			
			callback($pair);
		}
	}
};

/**
 * Global language storage.
 * 
 * @see	WCF.Dictionary
 */
WCF.Language = {
	_variables: new WCF.Dictionary(),
	
	/**
	 * @see	WCF.Dictionary.addObject()
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
	get: function(key) {
		return this._variables.get(key);
	}
};

/**
 * String utilities.
 */
WCF.String = {
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
	 * Initializes all TabMenus
	 */
	init: function() {
		$('.tabMenuContainer').each(function(index, tabMenu) {
			if (!$(tabMenu).attr('id')) {
				var $randomID = WCF.getRandomID();
				$(tabMenu).attr('id', $randomID);
			}
			
			// init jQuery UI TabMenu
			$(tabMenu).wcfTabs({
				select: function(event, ui) {
					var $panel = $(ui.panel);
					var $container = $panel.closest('.tabMenuContainer');
					
					// store currently selected item
					if ($container.data('store')) {
						if ($.wcfIsset($container.data('store'))) {
							$('#' + $container.data('store')).attr('value', $panel.attr('id'));
						}
					}
				}
			});
			
			// display active item on init
			if ($(tabMenu).data('active')) {
				$(tabMenu).find('.tabMenuContent').each(function(index, tabMenuItem) {
					if ($(tabMenuItem).attr('id') == $(tabMenu).data('active')) {
						$(tabMenu).wcfTabs('select', index);
					}
				});
			}
		});
	}
};

/**
 * Toggles options.
 * 
 * @param	string		element
 * @param	array		showItems
 * @param	array		hideItems
 */
WCF.ToggleOptions = function(element, showItems, hideItems) { this.init(element, showItems, hideItems); };
WCF.ToggleOptions.prototype = {
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
};

/**
 * Basic implementation for WCF dialogs.
 */
$.widget('ui.wcfDialog', $.ui.dialog, {
	_init: function() {
		this.options.autoOpen = true;
		this.options.close = function(event, ui) {
			$(this).parent('.ui-dialog').wcfDropOut('down', $.proxy(function() {
				$(this).parent('.ui-dialog').empty().remove();
			}, this));
		};
		this.options.height = 'auto';
		this.options.minHeight = 0;
		this.options.modal = true;
		this.options.width = 'auto';
		
		$.ui.dialog.prototype._init.apply(this, arguments);
	}
});

/**
 * Basic implementation for WCF dialogs loading content
 * via AJAX before calling dialog itself.
 */
$.widget('ui.wcfAJAXDialog', $.ui.dialog, {
	/**
	 * Indicates wether callback was already executed
	 * 
	 * @var	boolean
	 */
	_callbackExecuted: false,
	
	/**
	 * Initializes AJAX-request to fetch content.
	 */
	_init: function() {
		if (this.options.ajax) {
			this._loadContent();
		}
		
		// force dialog to be placed centered
		this.options.position = {
			my: 'center center',
			at: 'center center'
		};
		
		// dialog should display a spinner-like image, thus immediately fire up dialog
		this.options.autoOpen = true;
		this.options.width = 'auto';
		this.options.minHeight = 80;
		
		// disable ability to move dialog
		this.options.resizable = false;
		this.options.draggable = false;
		
		this.options.modal = true;
		this.options.hide = {
			effect: 'drop',
			direction: 'down'
		};
		
		this.options.close = function(event, ui) {
			// loading ajax content seems to block properly closing
			$(this).parent('.ui-dialog').empty().remove();
		};
		
		if (this.options.preventClose) {
			this.options.closeOnEscape = false;
		}
		
		$.ui.dialog.prototype._init.apply(this, arguments);
		
		// remove complete node instead of removing node-by-node
		if (this.options.hideTitle && this.options.preventClose) {
			this.element.parent('.ui-dialog').find('div.ui-dialog-titlebar').empty().remove();
		}
		else {
			if (this.options.hideTitle) {
				// remove title element
				$('#ui-dialog-title-' + this.element.attr('id')).empty().remove();
			}
			
			if (this.options.preventClose) {
				// remove close-button
				this.element.parent('.ui-dialog').find('a.ui-dialog-titlebar-close').empty().remove();
			}
		}
	},
	
	/**
	 * Loads content via AJAX.
	 * 
	 * @todo	Enforce JSON
	 */
	_loadContent: function() {
		var $type = 'GET';
		if (this.options.ajax.type) {
			$type = this.options.ajax.type;
			
			if (this.options.ajax.type != 'GET' && this.options.ajax.type != 'POST') {
				$type = 'GET';
			}
		}
		
		var $data = this.options.ajax.data || {};
		
		$.ajax({
			url: this.options.ajax.url,
			context: this,
			dataType: 'json',
			type: $type,
			data: $data,
			success: $.proxy(this._createDialog, this),
			error: function(transport) {
				alert(transport.responseText);
			}
		});
	},
	
	/**
	 * Inserts content.
	 * 
	 * @param	string		data
	 */
	_createDialog: function(data) {
		data.ignoreTemplate = true;
		this.element.data('responseData', data);
		
		this.element.wcfGrow({
			content: data.template,
			parent: this.element.parent('.ui-dialog')
		}, {
			duration: 600,
			complete: $.proxy(function(data) {
				this.element.css({
					height: 'auto'
				});
				
				// prevent double execution due to two complete-calls (two times animate)
				if (this._callbackExecuted) {
					return;
				}
				
				this._callbackExecuted = true;
				
				this.element.removeClass('overlayLoading');
				this.element.html(this.element.data('responseData').template);
				
				if (this.options.ajax.success) {
					this.options.ajax.success();
				}
			}, this)
		});
	},
	
	/**
	 * Redraws dialog, should be executed everytime content is changed.
	 */
	redraw: function() {
		var $dimensions = this.element.getDimensions();
		
		if ($dimensions.height > 200) {
			this.element.wcfGrow({
				content: this.element.html(),
				parent: this.element.parent('.ui-dialog')
			}, {
				duration: 600,
				complete: function() {
					$(this).css({ height: 'auto' });
				}
			});
		}
	}
});

/**
 * Workaround for ids containing a dot ".", until jQuery UI devs learn
 * to properly escape ids ... (it took 18 months until they finally
 * fixed it!)
 * 
 * @see	http://bugs.jqueryui.com/ticket/4681
 */
$.widget('ui.wcfTabs', $.ui.tabs, {
	_init: function() {
		$.ui.dialog.prototype._init.apply(this, arguments);
	},
	
	_sanitizeSelector: function(hash) {
		return hash.replace(/([:\.])/g, '\\$1');
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
/**
 * Class and function collection for WCF
 * 
 * @author	Markus Bartz, Tim Düsterhus, Alexander Ebert
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

 /* Simple JavaScript Inheritance
 * By John Resig http://ejohn.org/
 * MIT Licensed.
 */
// Inspired by base2 and Prototype
(function(){var a=false,b=/xyz/.test(function(){xyz})?/\b_super\b/:/.*/;this.Class=function(){};Class.extend=function(c){function g(){if(!a&&this.init)this.init.apply(this,arguments);}var d=this.prototype;a=true;var e=new this;a=false;for(var f in c){e[f]=typeof c[f]=="function"&&typeof d[f]=="function"&&b.test(c[f])?function(a,b){return function(){var c=this._super;this._super=d[a];var e=b.apply(this,arguments);this._super=c;return e;};}(f,c[f]):c[f]}g.prototype=e;g.prototype.constructor=g;g.extend=arguments.callee;return g;};})();

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
	 * CAUTION:	This method does not work properly, you should not rely
	 *		on it for now. It seems to work with the old jQuery UI-
	 *		based dialog, but no longer works with usual elements.
	 *		I will either try to fix it or remove it later, thus
	 *		this method will be deprecated for now.      -Alexander
	 * 
	 * Applies a grow-effect by resizing element while moving the element
	 * appropriately. Make sure the passed data.content element contains
	 * all elements which affect this indirectly, this includes outer
	 * containers which may apply an obstrusive padding.
	 * 
	 * @deprecated
	 * @param	object		data
	 * @param	object		options
	 * @return	jQuery
	 */
	wcfGrow: function(data, options) {
		var $content = $(data.content);
		var $parent = (data.parent) ? $(data.parent) : $(this);
		
		// calculate dimensions
		var $windowDimensions = $(window).getDimensions();
		var $elementDimensions = $content.getDimensions('outer');
		var $parentDimensions = $parent.getDimensions('outer');
		var $parentInnerDimensions = $parent.getDimensions('inner');
		var $parentDifference = {
			height: $parentDimensions.height - $parentInnerDimensions.height,
			width: $parentDimensions.width - $parentInnerDimensions.width
		};

		// calculate offsets
		var $leftOffset = Math.round(($windowDimensions.width - ($elementDimensions.width + $parentDifference.width)) / 2);
		var $topOffset = Math.round(($windowDimensions.height - ($elementDimensions.height + $parentDifference.height)) / 2);

		// try to align vertically at 30% if previously calculated value is NOT lower
		var $desiredTopOffset = Math.round(($windowDimensions / 100) * 30);
		if ($desiredTopOffset < $topOffset) {
			$topOffset = $desiredTopOffset;
		}
		
		$parent.makePositioned('fixed', false);
		$parent.animate({
			left: $leftOffset + 'px',
			top: $topOffset + 'px'
		}, options);
		
		return this.animate({
			height: $elementDimensions.height,
			width: $elementDimensions.width
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
		dialog.wcfDialog(dialogOptions);

		return dialog;
	},
	
	/**
	 * Shows a modal dialog.
	 * 
	 * @param	string		dialogID
	 * @param	boolean		moveToBody
	 */
	showDialog: function(dialogID, moveToBody) {
		// we cannot work with a non-existant dialog, if you wish to
		// load content via AJAX, see showAJAXDialog() instead
		if (!$.wcfIsset(dialogID)) return;

		var $dialog = $('#' + $.wcfEscapeID(dialogID));
		if (moveToBody) {
			$dialog.remove().appendTo($('body'));
		}
		
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

 * Clipboard API
 */
WCF.Clipboard = {
	/**
	 * action proxy object
	 * @var	WCF.Action.Proxy
	 */
	_actionProxy: null,
	
	/**
	 * list of clipboard containers
	 * @var	jQuery
	 */
	_container: null,
	
	/**
	 * user has marked items
	 * @var	boolean
	 */
	_hasMarkedItems: false,
	
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
	 * Initializes the clipboard API.
	 */
	init: function(page, hasMarkedItems) {
		this._page = page;
		if (hasMarkedItems) this._hasMarkedItems = true;
		
		this._actionProxy = new WCF.Action.Proxy({
			success: $.proxy(this._actionSuccess, this),
			url: 'index.php/ClipboardProxy/?t=' + SECURITY_TOKEN + SID_ARG_2ND
		});
		
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this),
			url: 'index.php/Clipboard/?t=' + SECURITY_TOKEN + SID_ARG_2ND
		});
		
		// init containers first
		this._containers = $('.clipboardContainer').each($.proxy(function(index, container) {
			this._initContainer(container);
		}, this));
		
		// loads marked items
		if (this._hasMarkedItems) {
			this._loadMarkedItems();
		}
	},
	
	/**
	 * Loads marked items on init.
	 */
	_loadMarkedItems: function() {
		new WCF.Action.Proxy({
			autoSend: true,
			data: {
				pageClassName: this._page
			},
			success: $.proxy(this._loadMarkedItemsSuccess, this),
			url: 'index.php/ClipboardLoadMarkedItems/?t=' + SECURITY_TOKEN + SID_ARG_2ND
		});
	},
	
	/**
	 * Marks all returned items as marked
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_loadMarkedItemsSuccess: function(data, textStatus, jqXHR) {
		for (var $typeName in data.markedItems) {
			var $objectData = data.markedItems[$typeName];
			var $objectIDs = [];
			for (var $i in $objectData) {
				$objectIDs.push($objectData[$i]);
			}
			
			// loop through all containers
			this._containers.each(function(index, container) {
				var $container = $(container);
				
				// typeName does not match, continue
				if ($container.data('type') != $typeName) {
					return true;
				}
				
				// mark items as marked
				$container.find('input.clipboardItem').each(function(innerIndex, item) {
					var $item = $(item);
					if (WCF.inArray($item.data('objectID'), $objectIDs)) {
						$item.attr('checked', 'checked');
					}
				});
				
				// check if there is a markAll-checkbox
				$container.find('input.clipboardMarkAll').each(function(innerIndex, markAll) {
					var $allItemsMarked = true;
					
					$container.find('input.clipboardItem').each(function(itemIndex, item) {
						var $item = $(item);
						if (!$item.attr('checked')) {
							$allItemsMarked = false;
						}
					});
					
					if ($allItemsMarked) {
						$(markAll).attr('checked', 'checked');
					}
				});
			});
		}
		
		// call success method to build item list editors
		this._success(data, textStatus, jqXHR);
	},
	
	/**
	 * Initializes a clipboard container.
	 * 
	 * @param	object		container
	 */
	_initContainer: function(container) {
		var $container = $(container);
		
		// fetch id or assign a random one if none found
		var $id = $container.attr('id');
		if (!$id) {
			$id = WCF.getRandomID();
			$container.attr('id', $id);
		}
		
		// bind mark all checkboxes
		$container.find('.clipboardMarkAll').each($.proxy(function(index, item) {
			$(item).data('hasContainer', $id).click($.proxy(this._markAll, this));
		}, this));
		
		// bind item checkboxes
		$container.find('input.clipboardItem').each($.proxy(function(index, item) {
			$(item).data('hasContainer', $id).click($.proxy(this._click, this));
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
		
		// item is part of a container
		if ($item.data('hasContainer')) {
			var $container = $('#' + $item.data('hasContainer'));
			var $type = $container.data('type');
			
			// check if all items are marked
			var $markedAll = true;
			$container.find('input.clipboardItem').each(function(index, containerItem) {
				var $containerItem = $(containerItem);
				if (!$containerItem.attr('checked')) {
					$markedAll = false;
				}
			});
			
			// simulate a ticked 'markAll' checkbox
			$container.find('.clipboardMarkAll').each(function(index, markAll) {
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
			$container.find('input.clipboardItem').each(function(index, containerItem) {
				var $containerItem = $(containerItem);
				if ($isMarked) {
					if (!$containerItem.attr('checked')) {
						$containerItem.attr('checked', 'checked');
						$objectIDs.push($containerItem.data('objectID'));
					}
				}
				else {
					if ($containerItem.attr('checked')) {
						$containerItem.removeAttr('checked');
						$objectIDs.push($containerItem.data('objectID'));
					}
				}
			});
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
		$('.clipboardEditor').each(function(index, container) {
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
				$list = $('<ul></ul>').appendTo($container);
			}
			
			var $editor = data.items[$typeName];
			var $label = $('<li><span>' + $editor.label + '</span></li>').appendTo($list)
			var $itemList = $('<ol class="dropdown"></ol>').appendTo($label);

			$label.click(function() { $itemList.toggleClass('open'); });
			
			// create editor items
			for (var $itemIndex in $editor.items) {
				var $item = $editor.items[$itemIndex];
				var $listItem = $('<li>' + $item.label + '</li>').appendTo($itemList);
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

	_closeLists: function() {
		$('.clipboardEditor ul ol').each(function(index, list) {
			$(this).removeClass('open');
		});
	},
	
	/**
	 * Executes a clipboard editor item action.
	 * 
	 * @param	object		event
	 */
	_executeAction: function(event) {
		var $listItem = $(event.target);
		var $url = $listItem.data('url');
		if ($url) {
			window.location.href = $url;
		}
		
		// fire event
		$listItem.trigger('clipboardAction', [ $listItem.data('type'), $listItem.data('actionName') ]);
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
			this.options.init(this);
		}
		
		if (this.options.showLoadingOverlay) {
			this._activeRequests++;
			this._showLoadingOverlay();
		}
	},

	/**
	 * Displays the loading overlay if not already visible due to an active request.
	 */
	_showLoadingOverlay: function() {
		// create loading overlay on first run
		if (this._loadingOverlay === null) {
			this._loadingOverlay = $('<div id="actionProxyLoading" class="actionProxyLoading"><img src="' + RELATIVE_WCF_DIR + 'icon/spinner1.svg" alt="" />' + WCF.Language.get('wcf.global.loading') + '</div>').hide().appendTo($('body'));
		}

		// fade in overlay
		if (!this._loadingOverlayVisible) {
			this._loadingOverlayVisible = true;
			this._loadingOverlay.stop(true, true).fadeIn(200, $.proxy(function() {
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
		
		if (this._activeRequests == 0 && this._loadingOverlayVisibleTimer >= 1000) {
			this._loadingOverlayVisible = false;
			this._loadingOverlayVisibleTimer = 0;
			pe.stop();

			this._loadingOverlay.fadeOut(200);
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
			if ($.isFunction(this.options.failure)) {
				this.options.failure(jqXHR, textStatus, errorThrown, data);
			}
			
			var $randomID = WCF.getRandomID();
			$('<div class="ajaxDebugMessage" id="' + $randomID + '"><p>' + data.message + '</p><p>Stacktrace:</p><p>' + data.stacktrace + '</p></div>').wcfDialog({ title: WCF.Language.get('wcf.global.error.title') });
		}
		// failed to parse JSON
		catch (e) {
			var $randomID = WCF.getRandomID();
			$('<div class="ajaxDebugMessage" id="' + $randomID + '"><p style="padding: 3px;">' + jqXHR.responseText + '.</p></div>').wcfDialog({ title: WCF.Language.get('wcf.global.error.title') });
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

		if (this.options.showLoadingOverlay) {
			this._activeRequests--;
		}
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
			console.debug('Given element is not an image, aborting.');
			return;
		}

		// force element dimensions
		element.attr('width', element.attr('width'));
		element.attr('height', element.attr('height'));

		// replace image
		element.attr('src', WCF.Icon.get('wcf.global.spinner'));
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
 * @param	jQuery		badgeList
 */
WCF.Action.Delete = function(className, containerList, badgeList) { this.init(className, containerList, badgeList); };
WCF.Action.Delete.prototype = {
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
				this.proxy.showSpinner($target);
				this._sendRequest($target);
			}
		}
		else {
			this.proxy.showSpinner($target);
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
		this.containerList.each($.proxy(function(index, container) {
			var $objectID = $(container).find('.deleteButton').data('objectID');
			if (WCF.inArray($objectID, data.objectIDs)) {
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
};

/**
 * Basic implementation for AJAXProxy-based toggle actions.
 * 
 * @param	string		className
 * @param	jQuery		containerList
 * @param	string		toggleButtonSelector
 */
WCF.Action.Toggle = function(className, containerList, toggleButtonSelector) { this.init(className, containerList, toggleButtonSelector); };
WCF.Action.Toggle.prototype = {
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
		
		this.toggleButtonSelector = '.toggleButton';
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
		// remove items
		this.containerList.each($.proxy(function(index, container) {
			var $toggleButton = $(container).find(this.toggleButtonSelector);
			if (WCF.inArray($toggleButton.data('objectID'), data.objectIDs)) {
				$(container).wcfHighlight();
				
				// toggle icon source
				$toggleButton.attr('src', function() {
					if (this.src.match(/disabled1\.svg$/)) {
						return this.src.replace(/disabled1\.svg$/, 'enabled1.svg');
					}
					else {
						return this.src.replace(/enabled1\.svg$/, 'disabled1.svg');
					}
				});
				// toogle icon title
				$toggleButton.attr('title', function() {
					if (this.src.match(/enabled1\.svg$/)) {
						return $(this).data('disableMessage');
					}
					else {
						return $(this).data('enableMessage');
					}
				});
			}
		}, this));
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
		
		// timestamp is less than 60 minutes ago (display 1 hour ago rather than 60 minutes ago)
		if (this.timestamp < ($timestamp + 3540)) {
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
	get: function(key) {
		return this._variables.get(key);
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
WCF.MultipleLanguageInput = function(elementID, forceSelection, values, availableLanguages) { this.init(elementID, forceSelection, values, availableLanguages); };
WCF.MultipleLanguageInput.prototype = {
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
		this._element.wrap('<div class="preInput" />');
		var $wrapper = this._element.parent();
		var $button = $('<p class="dropdownCaption"><span>enable i18n</span></p>').prependTo($wrapper);

		$button.click($.proxy(this._enable, this));
		WCF.CloseOverlayHandler.addCallback(this._element.wcfIdentify(), $.proxy(this._closeSelection, this));
		
		if (enableOnInit) {
			$button.trigger('click');

			// pre-select current language
			this._list.children('li').each($.proxy(function(index, listItem) {
				var $listItem = $(listItem);
				if ($listItem.data('languageID') == this._languageID) {
					$listItem.trigger('click');
				}
			}, this));
		}
	},

	/**
	 * Enables the language selection or shows the selection if already enabled.
	 * 
	 * @param	object		event
	 */
	_enable: function(event) {
		if (!this._isEnabled) {
			var $button = $(event.target);
			if ($button.getTagName() == 'p') {
				$button = $button.children('span');
			}

			$button.addClass('active');

			// insert list
			if (this._list === null) {
				this._list = $('<ul class="dropdown"></ul>').insertAfter($button.parent());
				this._list.click(function(event) {
					// discard click event
					event.stopPropagation();
				});
				
				// insert available languages
				for (var $languageID in this._availableLanguages) {
					$('<li>' + this._availableLanguages[$languageID] + '</li>').data('languageID', $languageID).click($.proxy(this._changeLanguage, this)).appendTo(this._list);
				}

				// disable language input
				$('<li class="divider">disable i18n</li>').click($.proxy(this._disable, this)).appendTo(this._list);
			}

			this._isEnabled = true;
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

			// show list
			this._list.addClass('open');
		}
	},

	/**
	 * Closes the language selection.
	 */
	_closeSelection: function() {
		this._list.removeClass('open');
	},

	/**
	 * Changes the currently active language.
	 * 
	 * @param	object		event
	 */
	_changeLanguage: function(event) {
		var $button = $(event.target);

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
		this._list.prev('.dropdownCaption').children('span').text(this._availableLanguages[this._languageID]);

		// close selection and set focus on input element
		this._closeSelection();
		this._element.focus();
	},

	/**
	 * Disables language selection for current element.
	 */
	_disable: function() {
		// remove active marking
		this._list.prev('.dropdownCaption').children('span').removeClass('active').text('enable i18n');
		this._closeSelection();

		// update element value
		if (this._values[LANGUAGE_ID]) {
			this._element.val(this._values[LANGUAGE_ID]);
		}
		else {
			// no value for current language found, proceed with empty input
			this._element.val();
		}

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
};

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
 * Templates that may be fetched more than once with different variables. Based upon ideas from Prototype's template
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
 * 
 * @param	template		template-content
 * @see		https://github.com/sstephenson/prototype/blob/master/src/prototype/lang/template.js
 */
WCF.Template = function(template) { this.init(template); };
WCF.Template.prototype = {
	/**
	 * Template-content
	 * 
	 * @var	string
	 */
	_template: '',
	
	/**
	 * Saved literal-tags
	 * 
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
		
		// and re-insert saved literals
		return new WCF.Template.Compiled("'" + this.insertLiterals($compiled) + "';");
	}
};

/**
 * Represents a compiled template
 * 
 * @param	compiled		compiled template
 */
WCF.Template.Compiled = function(compiled) { this.init(compiled); };
WCF.Template.Compiled.prototype = {
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
		$('.collapsible').each($.proxy(function(index, button) {
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
			$($button.data('collapsibleContainer')).hide();
		}
		
		$button.click($.proxy(this._toggle, this));
	},
	
	/**
	 * Toggles collapsible containers on click.
	 * 
	 * @param	object		event
	 */
	_toggle: function(event) {
		var $button = this._findElement($(event.target));
		if ($button === false) {
			return false;
		}
		
		var $isOpen = $button.data('isOpen');
		var $target = $('#' + $.wcfEscapeID($button.data('collapsibleContainer')));
		
		if ($isOpen) {
			$target.stop().wcfBlindOut('vertical', $.proxy(function() {
				this._toggleImage($button, 'wcf.global.closed');
			}, this));
			$isOpen = false;
		}
		else {
			$target.stop().wcfBlindIn('vertical', $.proxy(function() {
				this._toggleImage($button, 'wcf.global.opened');
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
	},
	
	/**
	 * Finds the anchor element (sometimes the image will show up as target).
	 * 
	 * @param	jQuery		element
	 * @return	jQuery
	 */
	_findElement: function(element) {
		if (element.getTagName() == 'a') {
			return element;
		}
		
		element = $(element.parent('a'));
		if (element.length == 1) {
			return element;
		}
		
		console.debug('[WCF.Collapsible.Simple] Could not find valid parent, aborting.');
		return false;
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
			var $id = $container.wcfIdentify();
			this._containers[$id] = $container;
			
			this._initContainer($id, $container);
		}, this));
	},
	
	_initContainer: function(containerID, container) {
		var $target = this._getTarget(containerID);
		var $buttonContainer = this._getButtonContainer(containerID);
		var $button = this._createButton(containerID, $buttonContainer);
		
		// store container meta data
		this._containerData[containerID] = {
			button: $button,
			buttonContainer: $buttonContainer,
			isOpen: container.data('isOpen'),
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
		var $button = $('<a class="balloonTooltip" title="'+WCF.Language.get('wcf.global.button.collapsible')+'"><img src="' + WCF.Icon.get('wcf.icon.' + ($isOpen ? 'opened' : 'closed')) + '" alt="" /></a>').prependTo(buttonContainer);
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
			actionName: 'toggleContainer',
			className: this._className,
			objectIDs: [this._getObjectID($containerID)],
			parameters: $.extend(true, {
				containerID: $containerID,
				currentState: $state,
				newState: $newState
			}, this._getAdditionalParameters($containerID))
		});
		this._proxy.sendRequest();

		// set spinner for current button
		this._showSpinner($button);
	},

	_showSpinner: function(button) {
		button.find('img').attr('src', WCF.Icon.get('wcf.icon.loading'));
	},

	_hideSpinner: function(button, newIcon) {
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
		this._hideSpinner(this._containerData[$containerID].button, WCF.Icon.get('wcf.icon.' + (data.returnValues.isOpen ? 'opened' : 'closed')));
	}
});

/**
 * Holds userdata of the current user
 */
WCF.User = {
	/**
	 * UserID of the user
	 * 
	 * @var	integer
	 */
	userID: 0,
	
	/**
	 * Username of the user
	 * 
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
 * Creates a smooth scroll effect.
 */
WCF.Effect.SmoothScroll = function() { this.init(); };
WCF.Effect.SmoothScroll.prototype = {
	/**
	 * Initializes effect.
	 */
	init: function() {
		$('a[href=#top],a[href=#bottom]').click(function() {
			var $target = $(this.hash);
			if ($target.length) {
				var $targetOffset = $target.getOffsets().top;
				if ($targetOffset > $(document).height() - $(window).height()) {
					$targetOffset = $(document).height() - $(window).height();
					if ($targetOffset < 0) $targetOffset = 0;
				}
				
				$('html,body').animate({ scrollTop: $targetOffset }, 400, function (x, t, b, c, d) {
					return -c * ((t=t/d-1)*t*t*t - 1) + b;
				});
				
				return false;
			}
		});
	}
};

/**
 * Creates the balloon tool-tip.
 */
WCF.Effect.BalloonTooltip = function() { this.init(); };
WCF.Effect.BalloonTooltip.prototype = {
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
			this._tooltip = $('<div id="balloonTooltip" style="position: absolute"><span id="balloonTooltipText"></span><span class="pointer"><span></span></span></div>').appendTo($('body')).hide();

			// get viewport dimensions
			this._updateViewportDimensions();

			// update viewport dimensions on resize
			$(window).resize($.proxy(this._updateViewportDimensions, this));

			// observe DOM changes
			WCF.DOMNodeInsertedHandler.addCallback('WCF.Effect.BallonTooltip', $.proxy(this.init, this));

			this._didInit = true;
		}
		
		// init elements
		$('.balloonTooltip').each($.proxy(this._initTooltip, this));
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
		
		if ($element.hasClass('balloonTooltip')) {
			$element.removeClass('balloonTooltip');
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
		$alignment = 'center';
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
		this._tooltip.fadeIn('fast');
	},
	
	/**
	 * Hides tooltip once cursor left the element.
	 * 
	 * @param	object		event
	 */
	_mouseLeaveHandler: function(event) {
		this._tooltip.hide();
	}
};

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
	_executeCallbacks: function() {
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
		if (this._isExecuting) return;

		// do not track events fired within the next 100 ms
		this._isExecuting = true;
		new WCF.PeriodicalExecuter($.proxy(function(pe) {
			this._isExecuting = false;

			pe.stop();
		}, this), 100);

		this._callbacks.each(function(pair) {
			// execute callback
			pair.value(event);
		});
	}
};

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
	 * list with values that are excluded from seaching
	 * @var	array
	 */
	_excludedSearchValues: [],

	/**
	 * result list
	 * @var	jQuery
	 */
	_list: null,

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
	_triggerLength: 1,

	/**
	 * Initializes a new search.
	 * 
	 * @param	jQuery		searchInput
	 * @param	object		callback
	 * @param	array		excludedSearchValues
	 */
	init: function(searchInput, callback, excludedSearchValues) {
		if (!$.isFunction(callback)) {
			console.debug("[WCF.Search.Base] Given callback is invalid, aborting.");
			return;
		}

		this._callback = callback;
		if (excludedSearchValues) {
			this._excludedSearchValues = excludedSearchValues;
		}
		this._searchInput = $(searchInput).keyup($.proxy(this._keyUp, this));
		this._searchInput.wrap('<span />');
		this._list = $('<ul class="dropdown" />').insertAfter(this._searchInput);
		
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
	},
	
	/**
	 * Performs a search upon key up.
	 */
	_keyUp: function() {
		var $content = $.trim(this._searchInput.val());
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
				actionName: 'getList',
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
		if (!$.getLength(data.returnValues)) {
			this._clearList(false);

			return;
		}

		this._clearList(false);
		
		for (var $i in data.returnValues) {
			var $item = data.returnValues[$i];

			this._createListItem($item);
		}
		
		this._list.addClass('open');
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
		
		return $listItem;
	},

	/**
	 * Executes callback upon result click.
	 * 
	 * @param	object		event
	 */
	_executeCallback: function(event) {
		var $listItem = $(event.currentTarget);

		// notify callback
		this._callback($listItem.data());

		// close list and revert input
		this._clearList(true);
	},

	/**
	 * Closes the suggestion list and clears search input on demand.
	 * 
	 * @param	boolean		clearSearchInput
	 */
	_clearList: function(clearSearchInput) {
		if (clearSearchInput) {
			this._searchInput.val('');
		}

		this._list.removeClass('open').empty();
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
	 * @see	WCF.Search.Base
	 */
	init: function(searchInput, callback, includeUserGroups) {
		this._includeUserGroups = includeUserGroups;
		
		this._super(searchInput, callback);
	},
	
	/**
	 * @see	WCF.Search.Base._getParameters()
	 */
	_getParameters: function(parameters) {
		parameters.data.includeUserGroups = this._includeUserGroups;
		
		return parameters;
	},
	
	/**
	 * @see	WCF.Search.Base._createListItem()
	 */
	_createListItem: function(item) {
		var $listItem = this._super(item);
		
		// insert item type
		$('<img src="' + RELATIVE_WCF_DIR + 'icon/user' + (item.type == 'group' ? 's' : '') + '1.svg" alt="" />').insertBefore($listItem.children('span:eq(0)'));
		$listItem.data('type', item.type);
		
		return $listItem;
	}
});

/**
 * Provides a toggleable sidebar.
 */
$.widget('ui.wcfSidebar', {
	/**
	 * toggle button
	 * @var	jQuery
	 */
	_button: null,

	_container: null,

	/**
	 * sidebar visibility
	 * @var	boolean
	 */
	_visible: true,

	/**
	 * Creates a new toggleable sidebar.
	 */
	_create: function() {
		this.element.wrap('<div class="collapsibleSidebar"></div>');
		this._container = this.element.parents('aside:eq(0)');
		
		// create toggle button
		this._button = $('<span class="collapsibleSidebarButton" title="' + WCF.Language.get('wcf.global.button.collapsible') + '"><span></span></span>').appendTo(this._container);

		// bind event
		this._button.click($.proxy(this._toggle, this));
	},

	/**
	 * Toggles visibility on button click.
	 */
	_toggle: function() {
		if (this._visible) {
			this.hide();
		}
		else {
			this.show();
		}
	},

	/**
	 * Shows sidebar content.
	 */
	show: function() {
		if (this._visible) {
			return;
		}

		this._visible = true;
		this._container.removeClass('collapsed');
	},

	/**
	 * Hides the sidebar content.
	 */
	hide: function() {
		if (!this._visible) {
			return;
		}
		
		this._visible = false;
		this._container.addClass('collapsed');
	}
});

/**
 * Provides a toggleable sidebar with persistent visibility.
 */
$.widget('ui.wcfPersistentSidebar', $.ui.wcfSidebar, {
	/**
	 * widget options
	 * @var	object
	 */
	options: {
		className: '',
		collapsed: false,
		objectTypeID: 0
	},

	/**
	 * action proxy
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,

	/**
	 * Creates a new toggleable sidebar.
	 */
	_create: function() {
		if (this.options.className === '' || this.options.objectTypeID === 0) {
			console.debug('[ui.wcfPersistentSidebar] Class name or object type id missing, aborting.');
			return;
		}
		
		$.ui.wcfSidebar.prototype._init.apply(this, arguments);

		// collapse on init
		if (this.options.collapsed) {
			this.element.hide();
			this._visible = false;
		}

		// init proxy
		this._proxy = new WCF.Action.Proxy();
	},

	/**
	 * Shows sidebar content.
	 */
	show: function() {
		if (this._visible) {
			return;
		}

		$.ui.wcfSidebar.prototype._init.apply(this, arguments);

		// save state
		this._save();
	},

	/**
	 * Hides the sidebar content.
	 */
	hide: function() {
		if (!this._visible) {
			return;
		}

		$.ui.wcfSidebar.prototype._init.apply(this, arguments);

		// save state
		this._save();
	},

	/**
	 * Save collapsible state
	 */
	_save: function() {
		var $currentState = (!this._visible) ? 'close' : 'open';
		var $state = (this._visible) ? 'open' : 'close';

		this._proxy.setOption('data', {
			actionName: 'toggleSidebar',
			className: this.options.className,
			parameters: {
				data: {
					currentState: $currentState,
					newState: $newState,
					objectTypeID: this.options.objectTypeID
				}
			}
		});
		this._proxy.sendRequest();
	}
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
	 * dialog content dimensions
	 * @var	object
	 */
	_contentDimensions: null,

	/**
	 * difference between inner and outer content width
	 * @var	object
	 */
	/*
	_dimensionDifferences: {
		height: 0,
		width: 0
	},*/

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
		zIndex: 1000,

		// AJAX support
		ajax: false,
		data: { },
		showLoadingOverlay: true,
		success: null,
		type: 'POST',
		url: 'index.php/AJAXProxy/?t=' + SECURITY_TOKEN + SID_ARG_2ND
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
		$(window).resize($.proxy(this.render, this));
	},

	/**
	 * Creates a new dialog instance.
	 */
	_create: function() {
		// create dialog container
		this._container = $('<div class="wcfDialogContainer"></div>').hide().css({ zIndex: this.options.zIndex }).appendTo(document.body);
		
		// create title
		if (!this.options.hideTitle && this.options.title != '') {
			this._titlebar = $('<header class="wcfDialogTitlebar"></header>').appendTo(this._container);
			this._title = $('<span class="wcfDialogTitle"></div>').html(this.options.title).appendTo(this._titlebar);
		}

		// create close button
		if (this.options.closable) {
			this._closeButton = $('<a class="wcfDialogCloseButton"><span>TODO: close</span></a>').click($.proxy(this.close, this));

			if (!this.options.hideTitle && this.options.title != '') {
				this._closeButton.appendTo(this._titlebar);
			}
			else {
				this._closeButton.appendTo(this._container);
			}
		}
		
		// create content container
		this._content = $('<div class="wcfDialogContent"></div>').appendTo(this._container);

		// move target element into content
		var $content = this.element.remove();
		this._content.html($content);

		// create modal view
		if (this.options.modal) {
			this._overlay = $('<div class="wcfDialogOverlay"></div>').css({ height: '100%', zIndex: 900 }).appendTo(document.body);

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

		/*
		// caulculate dimensions differences
		this._container.show();
		var $contentInnerDimensions = this._content.getDimensions();
		var $contentOuterDimensions = this._content.getDimensions('outer');
		
		this._dimensionDifferences = {
			height: ($contentOuterDimensions.height - $contentInnerDimensions.height),
			width: ($contentOuterDimensions.width - $contentInnerDimensions.width)
		};
		*/
	},
	
	/**
	 * Handles successful AJAX requests.
	 *
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		// initialize dialog content
		this._initDialog(data);

		// remove loading overlay
		this._content.removeClass('overlayLoading');
		
		if (this.options.success !== null && $.isFunction(this.options.success)) {
			this.options.success(data, textStatus, jqXHR);
		}
	},
	
	/**
	 * Initializes dialog content if applicable.
	 * 
	 * @param	object		data
	 */
	_initDialog: function(data) {
		// insert template
		data.ignoreTemplate = true;
		var $template = this._getResponseValue(data, 'template');
		if ($template !== null) {
			this._content.children().html($template);
			this.render();
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
			this._overlay.show();
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
			this._overlay.hide();
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

		// calculate dimensions
		var $windowDimensions = $(window).getDimensions();
		var $containerDimensions = this._container.getDimensions('outer');
		var $contentDimensions = this._content.getDimensions();

		// calculate maximum content height
		var $heightDifference = $containerDimensions.height - $contentDimensions.height;
		var $maximumHeight = $windowDimensions.height - $heightDifference/* - (this._dimensionDifferences.height * 2)*/;
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
			}, 600, function() {
				// remove static dimensions
				$content.css({
					height: 'auto',
					width: 'auto'
				});
			});
			
			// store new dimensions
			this._contentDimensions = $contentDimensions;

			// move container
			this._container.animate({
				left: $leftOffset + 'px',
				top: $topOffset + 'px'
			}, 600, $.proxy(function() {
				this._isRendering = false;
			}));
		}

		this._isRendering = true;
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
		
		// fix dimensions
		/*
		$contentDimensions = {
			height: $contentDimensions.height*//* - this._dimensionDifferences.height*//*,
			width: $contentDimensions.width - this._dimensionDifferences.width
		};
		*/

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
		previousDisabledIcon: null,
		arrowDownIcon: null,
		nextIcon: null,
		nextDisabledIcon: null,
		
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
		if (this.options.previousIcon === null) this.options.previousIcon = WCF.Icon.get('wcf.icon.previous');
		if (this.options.previousDisabledIcon === null) this.options.previousDisabledIcon = WCF.Icon.get('wcf.icon.previous.disabled');
		if (this.options.nextIcon === null) this.options.nextIcon = WCF.Icon.get('wcf.icon.next');
		if (this.options.nextDisabledIcon === null) this.options.nextDisabledIcon = WCF.Icon.get('wcf.icon.next.disabled');
		if (this.options.arrowDownIcon === null) this.options.arrowDownIcon = WCF.Icon.get('wcf.icon.dropdown');
		
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
			// make sure pagination is visible
			if (this.element.hasClass('hidden')) {
				this.element.removeClass('hidden');
			}
			this.element.show();
			
			this.element.children().remove();
			
			var $pageList = $('<ul></ul>');
			
			var $previousElement = $('<li></li>').addClass('skip');
			$pageList.append($previousElement);
			
			if (this.options.activePage > 1) {
				var $previousLink = $('<a' + ((this.options.previousPage != null) ? (' title="' + this.options.previousPage + '" class="balloonTooltip"') : ('')) + '></a>');
				$previousElement.append($previousLink);
				this._bindSwitchPage($previousLink, this.options.activePage - 1);
				
				var $previousImage = $('<img src="' + this.options.previousIcon + '" alt="" />');
				$previousLink.append($previousImage);
			}
			else {
				var $previousImage = $('<img src="' + this.options.previousDisabledIcon + '" alt="" />');
				$previousElement.append($previousImage);
				$previousElement.addClass('disabled');
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
					var $leftChildren = $('<li class="children"></li>');
					$pageList.append($leftChildren);
					
					var $leftChildrenLink = $('<a class="dropdownCaption">&hellip;</a>');
					$leftChildren.append($leftChildrenLink);
					// commented all page number input events out, because the normal pagination also
					// don't have this function at this moment. This may get completely removed or
					// updated as soon as this gets reimplemented in the normal pagination -- Markus Bartz
//					$leftChildrenLink.click($.proxy(this._startInput, this));
					
					var $leftChildrenImage = $('<img src="' + this.options.arrowDownIcon + '" alt="" />');
					$leftChildrenLink.append($leftChildrenImage);
					
					var $leftChildrenInput = $('<input type="text" name="pageNo" class="tiny" />');
					$leftChildren.append($leftChildrenInput);
//					$leftChildrenInput.keydown($.proxy(this._handleInput, this));
//					$leftChildrenInput.keyup($.proxy(this._handleInput, this));
//					$leftChildrenInput.blur($.proxy(this._stopInput, this));
					
					var $leftChildrenContainer = $('<div class="dropdown"></div>');
					$leftChildren.append($leftChildrenContainer);
					
					var $leftPointerContainer = $('<span class="pointer"><span></span></span>');
					$leftChildrenContainer.append($leftPointerContainer);
					
					var $leftChildrenList = $('<ul></u>');
					$leftChildrenContainer.append($leftChildrenList);
					
					// render sublinks
					var $k = 0;
					var $step = Math.ceil(($left - 2) / this.SHOW_SUB_LINKS);
					for (var $i = 2; $i <= $left; $i += $step) {
						$leftChildrenList.append(this._renderLink($i, ($k != 0 && $k % 4 == 0)));
						$k++;
					}
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
					var $rightChildren = $('<li class="children"></li>');
					$pageList.append($rightChildren);
					
					var $rightChildrenLink = $('<a class="dropdownCaption">&hellip;</a>');
					$rightChildren.append($rightChildrenLink);
//					$rightChildrenLink.click($.proxy(this._startInput, this));
					
					var $rightChildrenImage = $('<img src="' + this.options.arrowDownIcon + '" alt="" />');
					$rightChildrenLink.append($rightChildrenImage);
					
					var $rightChildrenInput = $('<input type="text" name="pageNo" class="tiny" />');
					$rightChildren.append($rightChildrenInput);
//					$rightChildrenInput.keydown($.proxy(this._handleInput, this));
//					$rightChildrenInput.keyup($.proxy(this._handleInput, this));
//					$rightChildrenInput.blur($.proxy(this._stopInput, this));
					
					var $rightChildrenContainer = $('<div class="dropdown"></div>');
					$rightChildren.append($rightChildrenContainer);
					
					var $rightPointerContainer = $('<span class="pointer"><span></span></span>');
					$rightChildrenContainer.append($rightPointerContainer);
					
					var $rightChildrenList = $('<ul></ul>');
					$rightChildrenContainer.append($rightChildrenList);
					
					// render sublinks
					var $k = 0;
					var $step = Math.ceil((this.options.maxPage - $right) / this.SHOW_SUB_LINKS);
					for (var $i = $right; $i < this.options.maxPage; $i += $step) {
						$rightChildrenList.append(this._renderLink($i, ($k != 0 && $k % 4 == 0)));
						$k++;
					}
				}
			}
			
			// add last page
			$pageList.append(this._renderLink(this.options.maxPage));
			
			// add next button
			var $nextElement = $('<li></li>').addClass('skip');
			$pageList.append($nextElement);
			
			if (this.options.activePage < this.options.maxPage) {
				var $nextLink = $('<a' + ((this.options.nextPage != null) ? (' title="' + this.options.nextPage + '" class="balloonTooltip"') : ('')) + '></a>');
				$nextElement.append($nextLink);
				this._bindSwitchPage($nextLink, this.options.activePage + 1);
				
				var $nextImage = $('<img src="' + this.options.nextIcon + '" alt="" />');
				$nextLink.append($nextImage);
			}
			else {
				var $nextImage = $('<img src="' + this.options.nextDisabledIcon + '" alt="" />');
				$nextElement.append($nextImage);
				$nextElement.addClass('disabled');
			}
			
			this.element.append($pageList);
		}
		else {
			// otherwise hide the paginator if not already hidden
			this.element.hide();
		}
	},
	
	/**
	 * Renders a page link
	 * 
	 * @parameter	integer		page
	 * 
	 * @return		$(element)
	 */
	_renderLink: function(page, lineBreak) {
		var $pageElement = $('<li></li>');
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
				
				if ($result) {
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

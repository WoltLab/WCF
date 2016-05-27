/**
 * Versatile popover manager.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Controller/Popover
 */
define(['Ajax', 'Dictionary', 'Environment', 'Dom/ChangeListener', 'Dom/Util', 'Ui/Alignment'], function(Ajax, Dictionary, Environment, DomChangeListener, DomUtil, UiAlignment) {
	"use strict";
	
	var _activeId = null;
	var _cache = new Dictionary();
	var _elements = new Dictionary();
	var _handlers = new Dictionary();
	var _hoverId = null;
	var _suspended = false;
	var _timeoutEnter = null;
	var _timeoutLeave = null;
	
	var _popover = null;
	var _popoverContent = null;
	
	var _callbackClick = null;
	var _callbackHide = null;
	var _callbackMouseEnter = null;
	var _callbackMouseLeave = null;
	
	/** @const */ var STATE_NONE = 0;
	/** @const */ var STATE_LOADING = 1;
	/** @const */ var STATE_READY = 2;
	
	/** @const */ var DELAY_HIDE = 500;
	/** @const */ var DELAY_SHOW = 300;
	
	/**
	 * @exports	WoltLab/WCF/Controller/Popover
	 */
	return {
		/**
		 * Builds popover DOM elements and binds event listeners.
		 */
		_setup: function() {
			if (_popover !== null) {
				return;
			}
			
			_popover = elCreate('div');
			_popover.className = 'popover forceHide';
			
			_popoverContent = elCreate('div');
			_popoverContent.className = 'popoverContent';
			_popover.appendChild(_popoverContent);
			
			var pointer = elCreate('span');
			pointer.className = 'elementPointer';
			pointer.appendChild(elCreate('span'));
			_popover.appendChild(pointer);
			
			document.body.appendChild(_popover);
			
			// static binding for callbacks (they don't change anyway and binding each time is expensive)
			_callbackClick = this._hide.bind(this);
			_callbackMouseEnter = this._mouseEnter.bind(this);
			_callbackMouseLeave = this._mouseLeave.bind(this);
			
			// event listener
			_popover.addEventListener('mouseenter', this._popoverMouseEnter.bind(this));
			_popover.addEventListener('mouseleave', _callbackMouseLeave);
			
			_popover.addEventListener('animationend', this._clearContent.bind(this));
			
			window.addEventListener('beforeunload', (function() {
				_suspended = true;
				
				if (_timeoutEnter !== null) {
					window.clearTimeout(_timeoutEnter);
				}
				
				this._hide(true);
			}).bind(this));
			
			DomChangeListener.add('WoltLab/WCF/Controller/Popover', this._init.bind(this));
		},
		
		/**
		 * Initializes a popover handler.
		 * 
		 * Usage:
		 * 
		 * ControllerPopover.init({
		 * 	attributeName: 'data-object-id',
		 * 	className: 'fooLink',
		 * 	identifier: 'com.example.bar.foo',
		 * 	loadCallback: function(objectId, popover) {
		 * 		// request data for object id (e.g. via WoltLab/WCF/Ajax)
		 * 		
		 * 		// then call this to set the content
		 * 		popover.setContent('com.example.bar.foo', objectId, htmlTemplateString);
		 * 	}
		 * });
		 * 
		 * @param	{Object}	options		handler options
		 */
		init: function(options) {
			if (Environment.platform() !== 'desktop') {
				return;
			}
			
			options.attributeName = options.attributeName || 'data-object-id';
			options.legacy = (options.legacy === true);
			
			this._setup();
			
			if (_handlers.has(options.identifier)) {
				return;
			}
			
			_handlers.set(options.identifier, {
				attributeName: options.attributeName,
				elements: options.legacy ? options.className : elByClass(options.className),
				legacy: options.legacy,
				loadCallback: options.loadCallback
			});
			
			this._init(options.identifier);
		},
		
		/**
		 * Initializes a popover handler.
		 * 
		 * @param	{string}	identifier	handler identifier
		 */
		_init: function(identifier) {
			if (typeof identifier === 'string' && identifier.length) {
				this._initElements(_handlers.get(identifier), identifier);
			}
			else {
				_handlers.forEach(this._initElements.bind(this));
			}
		},
		
		/**
		 * Binds event listeners for popover-enabled elements.
		 * 
		 * @param	{Object}	options		handler options
		 * @param	{string}	identifier	handler identifier
		 */
		_initElements: function(options, identifier) {
			var elements = options.legacy ? elBySelAll(options.elements) : options.elements;
			for (var i = 0, length = elements.length; i < length; i++) {
				var element = elements[i];
				
				var id = DomUtil.identify(element);
				if (_cache.has(id)) {
					return;
				}
				
				var objectId = (options.legacy) ? id : ~~element.getAttribute(options.attributeName);
				if (objectId === 0) {
					continue;
				}
				
				element.addEventListener('mouseenter', _callbackMouseEnter);
				element.addEventListener('mouseleave', _callbackMouseLeave);
				
				if (element.nodeName === 'A' && elAttr(element, 'href')) {
					element.addEventListener('click', _callbackClick);
				}
				
				var cacheId = identifier + "-" + objectId;
				elData(element, 'cache-id', cacheId);
				
				_elements.set(id, {
					element: element,
					identifier: identifier,
					objectId: objectId
				});
				
				if (!_cache.has(cacheId)) {
					_cache.set(identifier + "-" + objectId, {
						content: null,
						state: STATE_NONE
					});
				}
			}
		},
		
		/**
		 * Sets the content for given identifier and object id.
		 * 
		 * @param	{string}	identifier	handler identifier
		 * @param	{int}           objectId	object id
		 * @param	{string}	content		HTML string
		 */
		setContent: function(identifier, objectId, content) {
			var cacheId = identifier + "-" + objectId;
			var data = _cache.get(cacheId);
			if (data === undefined) {
				throw new Error("Unable to find element for object id '" + objectId + "' (identifier: '" + identifier + "').");
			}
			
			data.content = DomUtil.createFragmentFromHtml(content);
			data.state = STATE_READY;
			
			if (_activeId) {
				var activeElement = _elements.get(_activeId).element;
				
				if (elData(activeElement, 'cache-id') === cacheId) {
					this._show();
				}
			}
		},
		
		/**
		 * Handles the mouse start hovering the popover-enabled element.
		 * 
		 * @param	{object}	event	event object
		 */
		_mouseEnter: function(event) {
			if (_suspended) {
				return;
			}
			
			if (_timeoutEnter !== null) {
				window.clearTimeout(_timeoutEnter);
				_timeoutEnter = null;
			}
			
			var id = DomUtil.identify(event.currentTarget);
			if (_activeId === id && _timeoutLeave !== null) {
				window.clearTimeout(_timeoutLeave);
				_timeoutLeave = null;
			}
			
			_hoverId = id;
			
			_timeoutEnter = window.setTimeout((function() {
				_timeoutEnter = null;
				
				if (_hoverId === id) {
					this._show();
				}
			}).bind(this), DELAY_SHOW);
		},
		
		/**
		 * Handles the mouse leaving the popover-enabled element or the popover itself.
		 */
		_mouseLeave: function() {
			_hoverId = null;
			
			if (_timeoutLeave !== null) {
				return;
			}
			
			if (_callbackHide === null) {
				_callbackHide = this._hide.bind(this);
			}
			
			if (_timeoutLeave !== null) {
				window.clearTimeout(_timeoutLeave);
			}
			
			_timeoutLeave = window.setTimeout(_callbackHide, DELAY_HIDE);
		},
		
		/**
		 * Handles the mouse start hovering the popover element.
		 */
		_popoverMouseEnter: function() {
			if (_timeoutLeave !== null) {
				window.clearTimeout(_timeoutLeave);
				_timeoutLeave = null;
			}
		},
		
		/**
		 * Shows the popover and loads content on-the-fly.
		 */
		_show: function() {
			if (_timeoutLeave !== null) {
				window.clearTimeout(_timeoutLeave);
				_timeoutLeave = null;
			}
			
			var forceHide = false;
			if (_popover.classList.contains('active')) {
				this._hide();
				
				forceHide = true;
			}
			else if (_popoverContent.childElementCount) {
				forceHide = true;
			}
			
			if (forceHide) {
				_popover.classList.add('forceHide');
				
				// force layout
				_popover.offsetTop;
				
				this._clearContent();
				
				_popover.classList.remove('forceHide');
			}
			
			_activeId = _hoverId;
			
			var elementData = _elements.get(_activeId);
			var data = _cache.get(elData(elementData.element, 'cache-id'));
			
			if (data.state === STATE_READY) {
				_popoverContent.appendChild(data.content);
				
				this._rebuild(_activeId);
			}
			else if (data.state === STATE_NONE) {
				data.state = STATE_LOADING;
				
				_handlers.get(elementData.identifier).loadCallback(elementData.objectId, this);
			}
		},
		
		/**
		 * Hides the popover element.
		 */
		_hide: function() {
			if (_timeoutLeave !== null) {
				window.clearTimeout(_timeoutLeave);
				_timeoutLeave = null;
			}
			
			_popover.classList.remove('active');
		},
		
		/**
		 * Clears popover content by moving it back into the cache.
		 */
		_clearContent: function() {
			if (_activeId && _popoverContent.childElementCount && !_popover.classList.contains('active')) {
				var activeElData = _cache.get(elData(_elements.get(_activeId).element, 'cache-id'));
				while (_popoverContent.childNodes.length) {
					activeElData.content.appendChild(_popoverContent.childNodes[0]);
				}
			}
		},
		
		/**
		 * Rebuilds the popover.
		 */
		_rebuild: function() {
			if (_popover.classList.contains('active')) {
				return;
			}
			
			_popover.classList.remove('forceHide');
			_popover.classList.add('active');
			
			UiAlignment.set(_popover, _elements.get(_activeId).element, {
				pointer: true,
				vertical: 'top'
			});
		},
		
		_ajaxSetup: function() {
			// does nothing
			return {};
		},
		
		/**
		 * Sends an AJAX requests to the server, simple wrapper to reuse the request object.
		 * 
		 * @param	{Object}	data		request data
		 * @param	{function}	success		success callback
		 * @param	{function=}	failure		error callback
		 */
		ajaxApi: function(data, success, failure) {
			if (typeof success !== 'function') {
				throw new TypeError("Expected a valid callback for parameter 'success'.");
			}
			
			Ajax.api(this, data, success, failure);
		}
	};
});

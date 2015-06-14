/**
 * Versatile popover manager.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Controller/Popover
 */
define(['Ajax', 'Dictionary', 'Environment', 'DOM/ChangeListener', 'DOM/Util', 'UI/Alignment'], function(Ajax, Dictionary, Environment, DOMChangeListener, DOMUtil, UIAlignment) {
	"use strict";
	
	var _activeId = null;
	var _baseHeight = 0;
	var _cache = new Dictionary();
	var _elements = new Dictionary();
	var _handlers = new Dictionary();
	var _hoverId = null;
	var _suspended = false;
	var _timeoutEnter = null;
	var _timeoutLeave = null;
	
	var _popover = null;
	var _popoverContent = null;
	var _popoverLoading = null;
	
	var _callbackClick = null;
	var _callbackHide = null;
	var _callbackMouseEnter = null;
	var _callbackMouseLeave = null;
	
	/** @const */ var STATE_NONE = 0;
	/** @const */ var STATE_LOADING = 1;
	/** @const */ var STATE_READY = 2;
	
	/** @const */ var DELAY_SHOW = 800;
	/** @const */ var DELAY_HIDE = 500;
	
	/**
	 * @exports	WoltLab/WCF/Controller/Popover
	 */
	var ControllerPopover = {
		/**
		 * Builds popover DOM elements and binds event listeners.
		 */
		_setup: function() {
			if (_popover !== null) {
				return;
			}
			
			_popover = document.createElement('div');
			_popover.classList.add('popover');
			
			_popoverContent = document.createElement('div');
			_popoverContent.classList.add('popoverContent');
			_popover.appendChild(_popoverContent);
			
			var pointer = document.createElement('span');
			pointer.classList.add('elementPointer');
			pointer.appendChild(document.createElement('span'));
			_popover.appendChild(pointer);
			
			_popoverLoading = document.createElement('span');
			_popoverLoading.className = 'icon icon32 fa-spinner';
			_popover.appendChild(_popoverLoading);
			
			document.body.appendChild(_popover);
			
			// static binding for callbacks (they don't change anyway and binding each time is expensive)
			_callbackClick = this._hide.bind(this);
			_callbackMouseEnter = this._mouseEnter.bind(this);
			_callbackMouseLeave = this._mouseLeave.bind(this);
			
			// event listener
			_popover.addEventListener('mouseenter', this._popoverMouseEnter.bind(this));
			_popover.addEventListener('mouseleave', _callbackMouseLeave);
			
			_popoverContent.addEventListener('transitionend', function(event) {
				if (event.propertyName === 'height') {
					_popoverContent.classList.remove('loading');
				}
			});
			
			_popover.addEventListener('transitionend', this._clearContent.bind(this));
			
			window.addEventListener('beforeunload', (function() {
				_suspended = true;
				
				if (_timeoutEnter !== null) {
					window.clearTimeout(_timeoutEnter);
				}
				
				this._hide(true);
			}).bind(this));
			
			DOMChangeListener.add('WoltLab/WCF/Controller/Popover', this._init.bind(this));
		},
		
		/**
		 * Initializes a popover handler.
		 * 
		 * Usage:
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
		 * @param	{object<string, *>}	options		handler options
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
				elements: options.legacy ? options.className : document.getElementsByClassName(options.className),
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
		 * @param	{object<string, *>}	options		handler options
		 * @param	{string}		identifier	handler identifier
		 */
		_initElements: function(options, identifier) {
			var elements = options.legacy ? document.querySelectorAll(options.elements) : options.elements;
			for (var i = 0, length = elements.length; i < length; i++) {
				var element = elements[i];
				
				var id = DOMUtil.identify(element);
				if (_cache.has(id)) {
					return;
				}
				
				var objectId = (options.legacy) ? id : ~~element.getAttribute(options.attributeName);
				if (objectId === 0) {
					continue;
				}
				
				element.addEventListener('mouseenter', _callbackMouseEnter);
				element.addEventListener('mouseleave', _callbackMouseLeave);
				
				if (element.nodeName === 'A' && element.getAttribute('href')) {
					element.addEventListener('click', _callbackClick);
				}
				
				var cacheId = identifier + "-" + objectId;
				element.setAttribute('data-cache-id', cacheId);
				
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
		 * @param	{integer}	objectId	object id
		 * @param	{string}	content		HTML string
		 */
		setContent: function(identifier, objectId, content) {
			var cacheId = identifier + "-" + objectId;
			var data = _cache.get(cacheId);
			if (data === undefined) {
				throw new Error("Unable to find element for object id '" + objectId + "' (identifier: '" + identifier + "').");
			}
			
			data.content = DOMUtil.createFragmentFromHtml(content);
			data.state = STATE_READY;
			
			if (_activeId) {
				var activeElement = _elements.get(_activeId).element;
				
				if (activeElement.getAttribute('data-cache-id') === cacheId) {
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
			
			var id = DOMUtil.identify(event.currentTarget);
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
		 * 
		 * @param	{object}	event	event object
		 */
		_mouseLeave: function(event) {
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
		 * 
		 * @param	{object}	event	event object
		 */
		_popoverMouseEnter: function(event) {
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
			
			var disableAnimation = (_activeId !== null && _activeId !== _hoverId);
			if (_popover.classList.contains('active')) {
				this._hide(disableAnimation);
			}
			
			_activeId = _hoverId;
			
			var elData = _elements.get(_activeId);
			var data = _cache.get(elData.element.getAttribute('data-cache-id'));
			
			if (data.state === STATE_READY) {
				_popoverContent.appendChild(data.content);
			}
			else if (data.state === STATE_NONE) {
				_popoverContent.classList.add('loading');
			}
			
			this._rebuild(_activeId);
			
			if (data.state === STATE_NONE) {
				data.state = STATE_LOADING;
				
				_handlers.get(elData.identifier).loadCallback(elData.objectId, this);
			}
		},
		
		/**
		 * Hides the popover element.
		 * 
		 * @param	{(object|boolean)}	event	event object or boolean if popover should be forced hidden
		 */
		_hide: function(event) {
			if (_timeoutLeave !== null) {
				window.clearTimeout(_timeoutLeave);
				_timeoutLeave = null;
			}
			
			_popover.classList.remove('active');
			
			if (typeof event === 'boolean' && event === true) {
				_popover.classList.add('disableAnimation');
				
				// force reflow
				_popover.offsetHeight;
				
				this._clearContent();
			}
		},
		
		/**
		 * Clears popover content by moving it back into the cache.
		 */
		_clearContent: function() {
			if (_activeId && _popoverContent.childElementCount && !_popover.classList.contains('active')) {
				var activeElData = _cache.get(_elements.get(_activeId).element.getAttribute('data-cache-id'));
				while (_popoverContent.childNodes.length) {
					activeElData.content.appendChild(_popoverContent.childNodes[0]);
				}
				
				_popoverContent.style.removeProperty('height');
			}
		},
		
		/**
		 * Rebuilds the popover.
		 */
		_rebuild: function() {
			if (_popover.classList.contains('active')) {
				return;
			}
			
			_popover.classList.add('active');
			_popover.classList.remove('disableAnimation');
			if (_popoverContent.classList.contains('loading')) {
				if (_popoverContent.childElementCount === 0) {
					if (_baseHeight === 0) {
						_baseHeight = _popoverContent.offsetHeight;
					}
					
					_popoverContent.style.setProperty('height', _baseHeight + 'px');
				}
				else {
					_popoverContent.style.removeProperty('height');
					
					var height = _popoverContent.offsetHeight;
					_popoverContent.style.setProperty('height', _baseHeight + 'px');
					
					// force reflow
					_popoverContent.offsetHeight;
					
					_popoverContent.style.setProperty('height', height + 'px');
				}
			}
			
			UIAlignment.set(_popover, _elements.get(_activeId).element, {
				pointer: true,
				vertical: 'top',
				verticalOffset: 3
			});
		},
		
		_ajaxSetup: function() {
			// does nothing
			return {};
		},
		
		/**
		 * Sends an AJAX requests to the server, simple wrapper to reuse the request object.
		 * 
		 * @param	{object<string, *>}	data		request data
		 * @param	{function<object>}	success		success callback
		 * @param	{function<object>=}	failure		error callback
		 */
		ajaxApi: function(data, success, failure) {
			if (typeof success !== 'function') {
				throw new TypeError("Expected a valid callback for parameter 'success'.");
			}
			
			Ajax.api(this, data, success, failure);
		}
	};
	
	return ControllerPopover;
});

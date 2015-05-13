define(['Dictionary', 'DOM/Util', 'UI/Alignment'], function(Dictionary, DOMUtil, UIAlignment) {
	"use strict";
	
	var _activeId = 0;
	var _activeIdentifier = '';
	var _cache = null;
	var _handlers = null;
	var _suspended = false;
	var _timeoutEnter = null;
	var _timeoutLeave = null;
	
	var _popover = null;
	var _popoverContent = null;
	var _popoverLoading = null;
	
	/** @const */ var STATE_NONE = 0;
	/** @const */ var STATE_LOADING = 1;
	/** @const */ var STATE_READY = 2;
	
	/**
	 * @constructor
	 */
	function ControllerPopover() {};
	ControllerPopover.prototype = {
		_setup: function() {
			if (_popover !== null) {
				return;
			}
			
			_cache = new Dictionary();
			_handlers = new Dictionary();
			
			_popover = document.createElement('div');
			_popover.classList.add('popover');
			
			var pointer = document.createElement('span');
			pointer.classList.add('elementPointer');
			pointer.appendChild(document.createElement('span'));
			_popover.appendChild(pointer);
			
			_popoverLoading = document.createElement('span');
			_popoverLoading.className = 'icon icon48 fa-spinner';
			_popover.appendChild(_popoverLoading);
			
			_popoverContent = document.createElement('div');
			_popoverContent.classList.add('popoverContent');
			_popover.appendChild(_popoverContent);
			
			document.body.appendChild(_popover);
			
			window.addEventListener('beforeunload', (function() {
				_suspended = true;
				this._hide(true);
			}).bind(this));
			
			WCF.DOMNodeInsertedHandler.addCallback('WoltLab/WCF/Controller/Popover', this._init.bind(this));
		},
		
		init: function(options) {
			if ($.browser.mobile) {
				return;
			}
			
			options.attributeName = options.attributeName || 'data-object-id';
			
			this._setup();
			
			if (_handlers.has(options.identifier)) {
				return;
			}
			
			_cache.set(options.identifier, new Dictionary());
			_handlers.set(options.identifier, {
				attributeName: options.attributeName,
				elements: document.getElementsByClassName(options.className),
				loadCallback: options.loadCallback
			});
			
			this._init(options.identifier)
		},
		
		setContent: function(identifier, objectId, content) {
			content = (typeof content === 'string') ? content.trim() : '';
			if (content.length === 0) {
				throw new Error("Expected a non-empty HTML string for '" + objectId + "' (identifier: '" + identifier + "').");
			}
			
			var objects = _cache.get(identifier);
			if (objects === undefined) {
				throw new Error("Expected a valid identifier, '" + identifier + "' is invalid.");
			}
			
			var obj = objects.get(objectId);
			if (obj === undefined) {
				throw new Error("Expected a valid object id, '" + objectId + "' is invalid (identifier: '" + identifier + "').");
			}
			
			obj.element = DOMUtil.createFragmentFromHtml(content);
			obj.state = STATE_READY;
			console.debug(obj);
			this._show(identifier, objectId);
		},
		
		_init: function(identifier) {
			if (typeof identifier === 'string' && identifier.length) {
				this._initElements(identifier, _handlers.get(identifier));
			}
			else {
				_handlers.forEach((function(options, identifier) {
					this._initElements(identifier, options);
				}).bind(this));
			}
		},
		
		_initElements: function(identifier, options) {
			var cachedElements = _cache.get(identifier);
			console.debug(identifier);
			console.debug(options);
			for (var i = 0, length = options.elements.length; i < length; i++) {
				var element = options.elements[i];
				var objectId = ~~element.getAttribute(options.attributeName);
				
				if (objectId === 0 || cachedElements.has(objectId)) {
					continue;
				}
				
				element.addEventListener('mouseenter', (function() { this._mouseEnter(identifier, objectId); }).bind(this));
				element.addEventListener('mouseleave', (function() { this._mouseLeave(identifier, objectId); }).bind(this));
				
				if (element.nodeName === 'A' && element.getAttribute('href')) {
					element.addEventListener('click', (function() {
						this._hide(true);
					}).bind(this))
				}
				
				cachedElements.set(objectId, {
					element: element,
					state: STATE_NONE
				});
			}
		},
		
		_mouseEnter: function(identifier, objectId) {
			if (this._timeoutEnter !== null) {
				window.clearTimeout(this._timeoutEnter);
			}
			
			this._hoverIdentifier = identifier;
			this._hoverId = objectId;
			window.setTimeout((function() {
				if (this._hoverId === objectId) {
					this._show(identifier, objectId);
				}
			}).bind(this));
		},
		
		_mouseLeave: function(identifier, objectId) {
			
		},
		
		_show: function(identifier, objectId) {
			if (this._intervalOut !== null) {
				window.clearTimeout(this._intervalOut);
			}
			
			if (_popover.classList.contains('active')) {
				this._hide(true);
			}
			
			if (_activeId && _activeId !== objectId) {
				var cachedContent = _cache.get(_activeElementId);
				while (_popoverContent.childNodes.length) {
					cachedContent.appendChild(_popoverContent.childNodes[0]);
				}
			}
			
			var content = _cache.get(identifier).get(objectId);
			if (content.state === STATE_READY) {
				_popoverContent.classList.remove('loading');
				_popoverContent.appendChild(content.element);
			}
			else if (content.state === STATE_NONE) {
				_popoverContent.classList.add('loading');
			}
			
			_activeId = objectId;
			_activeIdentifier = identifier;
			
			if (content.state === STATE_NONE) {
				content.state = STATE_LOADING;
				
				this._load(identifier, objectId);
			}
		},
		
		_hide: function(disableAnimation) {
			_popover.classList.remove('active');
			
			if (disableAnimation) {
				_popover.classList.add('disableAnimation');
			}
			
			_activeIdentifier = '';
			_activeId = null;
		},
		
		_load: function(identifier, objectId) {
			_handlers.get(identifier).loadCallback(objectId, this);
		},
		
		_rebuild: function(elementId) {
			if (elementId !== _activeElementId) {
				return;
			}
			
			_popover.classList.add('active');
			_popoverContent.appendChild(_cache.get(elementId));
			_popoverContent.classList.remove('loading');
			
			UIAlignment.set(_popover, document.getElementById(elementId), {
				pointer: true
			});
		}
	};
	
	return new ControllerPopover();
});

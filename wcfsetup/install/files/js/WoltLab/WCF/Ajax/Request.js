define(['Core', 'Language', 'DOM/ChangeListener', 'DOM/Util', 'UI/Dialog', 'WoltLab/WCF/Ajax/Status'], function(Core, Language, DOMChangeListener, DOMUtil, UIDialog, AjaxStatus) {
	"use strict";
	
	var _didInit = false;
	var _ignoreAllErrors = false;
	
	function AjaxRequest(options) {
		this._data = null;
		this._options = {};
		this._previousXhr = null;
		this._xhr = null;
		
		this._init(options);
	};
	AjaxRequest.prototype = {
		/**
		 * Initializes the request options.
		 * 
		 * @param	{object<string, *>}	options		request options
		 */
		_init: function(options) {
			this._options = Core.extend({
				// request data
				data: {},
				type: 'POST',
				url: '',
				
				// behavior
				autoAbort: false,
				ignoreError: false,
				pinData: false,
				silent: false,
				
				// callbacks
				failure: null,
				finalize: null,
				success: null,
				
				callbackObject: null
			}, options);
			
			this._options.url = Core.convertLegacyUrl(this._options.url);
			
			if (this._options.pinData) {
				this._data = Core.extend({}, this._options.data);
			}
			
			if (this._options.callbackObject !== null) {
				if (typeof this._options.callbackObject._ajaxFailure === 'function') this._options.failure = this._options.callbackObject._ajaxFailure.bind(this._options.callbackObject);
				if (typeof this._options.callbackObject._ajaxFinalize === 'function') this._options.finalize = this._options.callbackObject._ajaxFinalize.bind(this._options.callbackObject);
				if (typeof this._options.callbackObject._ajaxSuccess === 'function') this._options.success = this._options.callbackObject._ajaxSuccess.bind(this._options.callbackObject);
			}
			
			if (_didInit === false) {
				_didInit = true;
				
				window.addEventListener('beforeunload', function() { _ignoreAllErrors = true; });
			}
		},
		
		/**
		 * Dispatches a request, optionally aborting a currently active request.
		 * 
		 * @param	{boolean}	abortPrevious	abort currently active request
		 */
		sendRequest: function(abortPrevious) {
			if (abortPrevious === true || this._options.autoAbort) {
				this.abortPrevious();
			}
			
			if (!this._options.silent) {
				AjaxStatus.show();
			}
			
			if (this._xhr instanceof XMLHttpRequest) {
				this._previousXhr = this._xhr;
			}
			
			this._xhr = new XMLHttpRequest();
			this._xhr.open(this._options.type, this._options.url, true);
			this._xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
			this._xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
			
			var self = this;
			
			var options = Core.clone(this._options);
			this._xhr.onload = function() {
				if (this.readyState === XMLHttpRequest.DONE) {
					if (this.status >= 200 && this.status < 300 || this.status === 304) {
						self._success(this, options);
					}
					else {
						self._failure(this, options);
					}
				}
			};
			this._xhr.onerror = function() {
				self._failure(this, options);
			};
			
			if (this._options.type === 'POST') {
				var data = this._options.data;
				if (typeof data === 'object') {
					data = Core.serialize(data);
				}
				
				this._xhr.send(data);
			}
			else {
				this._xhr.send();
			}
		},
		
		/**
		 * Aborts a previous request.
		 */
		abortPrevious: function() {
			if (this._previousXhr === null) {
				return;
			}
			
			this._previousXhr.abort();
			this._previousXhr = null;
			
			if (!this._options.silent) {
				AjaxStatus.hide();
			}
		},
		
		/**
		 * Sets a specific option.
		 * 
		 * Do not call this method, it exists for compatibility with WCF.Action.Proxy
		 * and will be removed at some point without further notice.
		 * 
		 * @deprecated	2.2
		 * 
		 * @param	{string}	key	option name
		 * @param	{*}		value	option value
		 */
		setOption: function(key, value) {
			this._options[key] = value;
		},
		
		/**
		 * Sets request data while honoring pinned data from setup callback.
		 * 
		 * @param	{object<string, *>}	data	request data
		 */
		setData: function(data) {
			if (this._data !== null) {
				data = Core.extend(this._data, data);
			}
			
			this._options.data = data;
		},
		
		/**
		 * Handles a successful request.
		 * 
		 * @param	{XMLHttpRequest}	xhr		request object
		 * @param	{object<string, *>}	options		request options
		 */
		_success: function(xhr, options) {
			if (!options.silent) {
				AjaxStatus.hide();
			}
			
			if (typeof options.success === 'function') {
				var data = null;
				if (xhr.getResponseHeader('Content-Type') === 'application/json') {
					try {
						data = JSON.parse(xhr.responseText);
					}
					catch (e) {
						// invalid JSON
						this._failure(xhr);
						
						return;
					}
					
					// trim HTML before processing, see http://jquery.com/upgrade-guide/1.9/#jquery-htmlstring-versus-jquery-selectorstring
					if (data.returnValues !== undefined && data.returnValues.template !== undefined) {
						data.returnValues.template = data.returnValues.template.trim();
					}
				}
				
				options.success(data, xhr.responseText, xhr);
			}
			
			this._finalize(options);
		},
		
		/**
		 * Handles failed requests, this can be both a successful request with
		 * a non-success status code or an entirely failed request.
		 * 
		 * @param	{XMLHttpRequest}	xhr		request object
		 * @param	{object<string, *>}	options		request options
		 */
		_failure: function (xhr, options) {
			if (_ignoreAllErrors) {
				return;
			}
			
			if (!options.silent) {
				AjaxStatus.hide();
			}
			
			var data = null;
			try {
				data = JSON.parse(xhr.responseText);
			}
			catch (e) {}
			
			var showError = true;
			if (typeof options.failure === 'function') {
				showError = options.failure(data, xhr);
			}
			
			if (options.ignoreError !== true && showError !== false) {
				var details = '';
				var message = '';
				
				if (data !== null) {
					if (data.stacktrace) details = '<br /><p>Stacktrace:</p><p>' + data.stacktrace + '</p>';
					else if (data.exceptionID) details = '<br /><p>Exception ID: <code>' + data.exceptionID + '</code></p>';
					
					message = data.message;
				}
				else {
					message = xhr.responseText;
				}
				
				if (!message || message === 'undefined') {
					return;
				}
				
				var html = '<div class="ajaxDebugMessage"><p>' + message + '</p>' + details + '</div>';
				
				UIDialog.open(DOMUtil.getUniqueId(), html, {
					title: Language.get('wcf.global.error.title')
				});
			}
			
			this._finalize(options);
		},
		
		/**
		 * Finalizes a request.
		 * 
		 * @param	{object<string, *>}	options		request options
		 */
		_finalize: function(options) {
			if (typeof options.finalize === 'function') {
				options.finalize(this._xhr);
			}
			
			this._previousXhr = null;
			
			DOMChangeListener.trigger();
			
			// fix anchor tags generated through WCF::getAnchor()
			var links = document.querySelectorAll('a[href*="#"]');
			for (var i = 0, length = links.length; i < length; i++) {
				var link = links[i];
				var href = link.getAttribute('href');
				if (href.indexOf('AJAXProxy') !== -1 || href.indexOf('ajax-proxy') !== -1) {
					href = href.substr(href.indexOf('#'));
					link.setAttribute('href', document.location.toString().replace(/#.*/, '') + href);
				}
			}
		}
	};
	
	return AjaxRequest;
});

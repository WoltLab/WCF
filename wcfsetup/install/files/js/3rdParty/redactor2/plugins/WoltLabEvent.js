$.Redactor.prototype.WoltLabEvent = function() {
	"use strict";
	
	var _activeInstances = 0;
	
	return {
		init: function() {
			this._callbacks = [];
			this._elementId = this.$element[0].id;
			
			require(['EventHandler'], (function(EventHandler) {
				this.WoltLabEvent._setEvents(EventHandler);
				
				EventHandler.add('com.woltlab.wcf.redactor2', 'destroy_' + this._elementId, (function () {
					EventHandler.removeAllBySuffix('com.woltlab.wcf.redactor2', this._elementId);
				}).bind(this))
			}).bind(this));
			
			this.$editor[0].addEventListener('focus', function () {
				_activeInstances++;
				
				document.documentElement.classList.add('redactorActive');
			});
			this.$editor[0].addEventListener('blur', function () {
				_activeInstances--;
				
				// short delay to prevent flickering when switching focus between editors
				window.setTimeout(function () {
					if (_activeInstances === 0) {
						document.documentElement.classList.remove('redactorActive');
					}
				}, 100);
			})
		},
		
		_setEvents: function(EventHandler) {
			var elementId = this.$element[0].id;
			
			var observeLoad = this.observe.load;
			this.observe.load = (function() {
				observeLoad.call(this);
				
				EventHandler.fire('com.woltlab.wcf.redactor2', 'observe_load_' + elementId, {
					editor: this.$editor[0]
				});
			}).bind(this);
			
			this.opts.callbacks.keyup = function(event) {
				var data = {
					cancel: false,
					event: event
				};
				
				EventHandler.fire('com.woltlab.wcf.redactor', 'keyup_' + elementId, data);
				
				return (data.cancel === false);
			};
			
			// provide editor message on callback
			EventHandler.add('com.woltlab.wcf.redactor2', 'getText_' + elementId, (function(data) {
				data.message = this.code.get();
			}).bind(this));
			
			// clear editor content on reset
			EventHandler.add('com.woltlab.wcf.redactor2', 'reset_' + elementId, (function() {
				this.code.set('');
			}).bind(this));
		},
		
		register: function(callbackName, callback) {
			require(['EventHandler'], (function(EventHandler) {
				var uuid = this.uuid;
				
				if (this._callbacks.indexOf(callbackName) === -1) {
					this.opts.callbacks[callbackName] = (function (event) {
						var data = {
							cancel: false,
							event: event,
							redactor: this
						};
						
						EventHandler.fire('com.woltlab.wcf.redactor2', callbackName + '_' + uuid + '_' + this.WoltLabEvent._elementId, data);
						
						return (data.cancel === false);
					}).bind(this);
					
					this._callbacks.push(callbackName);
				}
				
				require(['EventHandler'], (function(EventHandler) {
					EventHandler.add('com.woltlab.wcf.redactor2', callbackName + '_' + uuid + '_' + this.WoltLabEvent._elementId, callback);
				}).bind(this));
			}).bind(this));
		}
	};
};

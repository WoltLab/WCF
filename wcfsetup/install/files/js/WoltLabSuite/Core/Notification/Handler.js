define(['Ajax', 'Core', 'EventHandler'], function(Ajax, Core, EventHandler) {
	"use strict";
	
	if (!('Promise' in window) || !('Notification' in window)) {
		// fake object exposed to ancient browsers (*cough* IE11 *cough*)
		return {
			setup: function () {}
		}
	}
	
	var _allowNotification = false;
	var _icon = '';
	var _inactiveSince = 0;
	var _lastRequestTimestamp = window.TIME_NOW;
	var _requestTimer = null;
	var _sessionKeepAlive = 0;
	
	return {
		setup: function (options) {
			options = Core.extend({
				icon: '',
				sessionKeepAlive: 0
			}, options);
			
			_icon = options.icon;
			_sessionKeepAlive = options.sessionKeepAlive * 60;
			
			console.log("DEBUG ONLY");
			var x = this._dispatchRequest.bind(this);
			//this._prepareNextRequest();
			
			document.addEventListener('visibilitychange', this._onVisibilityChange.bind(this));
			window.addEventListener('storage', this._onStorage.bind(this));
			
			this._onVisibilityChange();
			
			Notification.requestPermission().then(function (result) {
				if (result === 'granted') {
					_allowNotification = true;
					console.log("DEBUG ONLY");
					x();
				}
			});
		},
		
		_onVisibilityChange: function() {
			_inactiveSince = (document.hidden) ? Date.now() : 0;
		},
		
		_getNextDelay: function() {
			if (_inactiveSince === 0) return 5;
			
			// milliseconds -> minutes
			var inactiveMins = ~~((Date.now() - _inactiveSince) / 60000);
			if (inactiveMins < 15) {
				return 5;
			}
			else if (inactiveMins < 30) {
				return 10;
			}
			
			return 15;
		},
		
		_prepareNextRequest: function() {
			var delay = Math.min(this._getNextDelay(), _sessionKeepAlive);
			
			_requestTimer = window.setTimeout(this._dispatchRequest.bind(this), delay * 60000);
		},
		
		_dispatchRequest: function() {
			var parameters = {};
			EventHandler.fire('com.woltlab.wcf.notification', 'beforePoll', parameters);
			
			// this timestamp is used to determine new notifications and to avoid
			// notifications being displayed multiple times due to different origins
			// (=subdomains) used, because we cannot synchronize them in the client
			parameters.lastRequestTimestamp = _lastRequestTimestamp;
			
			Ajax.api(this, {
				parameters: parameters
			});
		},
		
		_onStorage: function() {
			window.clearTimeout(_requestTimer);
			this._prepareNextRequest();
			
			// TODO: update counters and stuff, this is not the requesting tab!
		},
		
		_ajaxSuccess: function(data) {
			// forward keep alive data
			window.WCF.System.PushNotification.executeCallbacks(data.returnValues.keepAliveData);
			
			var abort = false;
			var pollData = data.returnValues.pollData;
			
			// store response data in session storage
			try {
				window.localStorage.setItem(Core.getStoragePrefix() + 'notification', JSON.stringify(pollData));
			}
			catch (e) {
				// storage is unavailable, e.g. in private mode, log error and disable polling
				abort = true;
				
				window.console.log(e);
			}
			
			if (!abort) {
				this._prepareNextRequest();
			}
			
			_lastRequestTimestamp = data.returnValues.lastRequestTimestamp;
			
			EventHandler.fire('com.woltlab.wcf.notification', 'afterPoll', pollData);
			
			this._showNotification(pollData);
		},
		
		_showNotification: function(pollData) {
			if (!_allowNotification) {
				return;
			}
			
			if (typeof pollData.notification === 'object' && typeof pollData.notification.message ===  'string') {
				new Notification(pollData.notification.title, {
					body: pollData.notification.message,
					icon: _icon
				})
			}
		},
		
		_ajaxSetup: function() {
			return {
				data: {
					actionName: 'poll',
					className: 'wcf\\data\\session\\SessionAction'
				},
				ignoreError: !window.ENABLE_DEBUG_MODE,
				silent: !window.ENABLE_DEBUG_MODE
			};
		}
	}
});

/**
 * Provides desktop notifications via periodic polling with an
 * increasing request delay on inactivity.
 * 
 * @author      Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module      WoltLabSuite/Core/Notification/Handler
 */
define(['Ajax', 'Core', 'EventHandler', 'StringUtil'], function(Ajax, Core, EventHandler, StringUtil) {
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
	//noinspection JSUnresolvedVariable
	var _lastRequestTimestamp = window.TIME_NOW;
	var _requestTimer = null;
	
	/**
	 * @exports     WoltLabSuite/Core/Notification/Handler
	 */
	return {
		/**
		 * Initializes the desktop notification system.
		 * 
		 * @param       {Object}        options         initialization options
		 */
		setup: function (options) {
			options = Core.extend({
				enableNotifications: false,
				icon: '',
			}, options);
			
			_icon = options.icon;
			
			this._prepareNextRequest();
			
			document.addEventListener('visibilitychange', this._onVisibilityChange.bind(this));
			window.addEventListener('storage', this._onStorage.bind(this));
			
			this._onVisibilityChange(null);
			
			if (options.enableNotifications) {
				switch (window.Notification.permission) {
					case 'granted':
						_allowNotification = true;
						break;
					case 'default':
						window.Notification.requestPermission(function (result) {
							if (result === 'granted') {
								_allowNotification = true;
							}
						});
						break;
				}
			}
		},
		
		/**
		 * Detects when this window is hidden or restored.
		 * 
		 * @param       {Event}         event
		 * @protected
		 */
		_onVisibilityChange: function(event) {
			// document was hidden before
			if (event !== null && !document.hidden) {
				var difference = (Date.now() - _inactiveSince) / 60000;
				if (difference > 4) {
					this._resetTimer();
					this._dispatchRequest();
				}
			}
			
			_inactiveSince = (document.hidden) ? Date.now() : 0;
		},
		
		/**
		 * Returns the delay in minutes before the next request should be dispatched.
		 * 
		 * @return      {int}
		 * @protected
		 */
		_getNextDelay: function() {
			if (_inactiveSince === 0) return 5;
			
			// milliseconds -> minutes
			var inactiveMinutes = ~~((Date.now() - _inactiveSince) / 60000);
			if (inactiveMinutes < 15) {
				return 5;
			}
			else if (inactiveMinutes < 30) {
				return 10;
			}
			
			return 15;
		},
		
		/**
		 * Resets the request delay timer.
		 * 
		 * @protected
		 */
		_resetTimer: function() {
			if (_requestTimer !== null) {
				window.clearTimeout(_requestTimer);
				_requestTimer = null;
			}
		},
		
		/**
		 * Schedules the next request using a calculated delay.
		 * 
		 * @protected
		 */
		_prepareNextRequest: function() {
			this._resetTimer();
			
			_requestTimer = window.setTimeout(this._dispatchRequest.bind(this), this._getNextDelay() * 60000);
		},
		
		/**
		 * Requests new data from the server.
		 * 
		 * @protected
		 */
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
		
		/**
		 * Notifies subscribers for updated data received by another tab.
		 * 
		 * @protected
		 */
		_onStorage: function() {
			// abort and re-schedule periodic request
			this._prepareNextRequest();
			
			var pollData, keepAliveData, abort = false;
			try {
				pollData = window.localStorage.getItem(Core.getStoragePrefix() + 'notification');
				keepAliveData = window.localStorage.getItem(Core.getStoragePrefix() + 'keepAliveData');
				
				pollData = JSON.parse(pollData);
				keepAliveData = JSON.parse(keepAliveData);
			}
			catch (e) {
				abort = true;
			}
			
			if (!abort) {
				EventHandler.fire('com.woltlab.wcf.notification', 'onStorage', {
					pollData: pollData,
					keepAliveData: keepAliveData
				});
			}
		},
		
		_ajaxSuccess: function(data) {
			var abort = false;
			var keepAliveData = data.returnValues.keepAliveData;
			var pollData = data.returnValues.pollData;
			
			// forward keep alive data
			window.WCF.System.PushNotification.executeCallbacks({returnValues: keepAliveData});
			
			// store response data in local storage
			try {
				window.localStorage.setItem(Core.getStoragePrefix() + 'notification', JSON.stringify(pollData));
				window.localStorage.setItem(Core.getStoragePrefix() + 'keepAliveData', JSON.stringify(keepAliveData));
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
		
		/**
		 * Displays a desktop notification.
		 * 
		 * @param       {Object}        pollData
		 * @protected
		 */
		_showNotification: function(pollData) {
			if (!_allowNotification) {
				return;
			}
			
			//noinspection JSUnresolvedVariable
			if (typeof pollData.notification === 'object' && typeof pollData.notification.message ===  'string') {
				//noinspection JSUnresolvedVariable
				var notification = new window.Notification(pollData.notification.title, {
					body: StringUtil.unescapeHTML(pollData.notification.message),
					icon: _icon
				});
				notification.onclick = function () {
					window.focus();
					notification.close();
					
					//noinspection JSUnresolvedVariable
					window.location = pollData.notification.link;
				};
			}
		},
		
		_ajaxSetup: function() {
			//noinspection JSUnresolvedVariable
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

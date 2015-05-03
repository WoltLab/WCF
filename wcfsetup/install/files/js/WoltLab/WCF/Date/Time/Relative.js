"use strict";

/**
 * Transforms <time> elements to display the elapsed time relative to the current time.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Date/Time/Relative
 */
define(function() {
	var _elements = null;
	var _offset = null;
	
	/**
	 * @constructor
	 */
	var DateTimeRelative = function() {};
	DateTimeRelative.prototype = {
		/**
		 * Transforms <time> elements on init and binds event listeners.
		 */
		setup: function() {
			_elements = document.getElementsByTagName('time');
			
			this._refresh();
			
			new WCF.PeriodicalExecuter(this._refresh.bind(this), 60000);
			
			WCF.DOMNodeInsertedHandler.addCallback('WCF.Date.Time', this._refresh.bind(this));
		},
		
		_refresh: function() {
			var date = new Date();
			var timestamp = (date.getTime() - date.getMilliseconds()) / 1000;
			if (_offset === null) _offset = timestamp - TIME_NOW;
			
			for (var i = 0, length = _elements.length; i < length; i++) {
				var element = _elements[i];
				
				if (!element.classList.contains('datetime') || element.getAttribute('data-is-future-date')) continue;
				
				if (!element.getAttribute('title')) element.setAttribute('title', element.textContent.trim());
				
				var elTimestamp = ~~element.getAttribute('data-timestamp') + _offset;
				var elDate = element.getAttribute('data-date');
				var elTime = element.getAttribute('data-time');
				var elOffset = element.getAttribute('data-offset');
				
				// timestamp is less than 60 seconds ago
				if (elTimestamp >= timestamp || timestamp < (elTimestamp + 60)) {
					element.textContent = WCF.Language.get('wcf.date.relative.now');
				}
				// timestamp is less than 60 minutes ago (display 1 hour ago rather than 60 minutes ago)
				else if (timestamp < (elTimestamp + 3540)) {
					var minutes = Math.max(Math.round((timestamp - elTimestamp) / 60), 1);
					element.textContent = WCF.Language.get('wcf.date.relative.minutes', { minutes: minutes });
				}
				// timestamp is less than 24 hours ago
				else if (timestamp < (elTimestamp + 86400)) {
					var hours = Math.round((timestamp - elTimestamp) / 3600);
					element.textContent = WCF.Language.get('wcf.date.relative.hours', { hours: hours });
				}
				// timestamp is less than 6 days ago
				else if (timestamp < (elTimestamp + 518400)) {
					var midnight = new Date(date.getFullYear(), date.getMonth(), date.getDate());
					var days = Math.ceil((midnight / 1000 - elTimestamp) / 86400);
					
					// get day of week
					var dateObj = WCF.Date.Util.getTimezoneDate((elTimestamp * 1000), elOffset * 1000);
					var dow = dateObj.getDay();
					var day = WCF.Language.get('__days')[dow];
					
					element.textContent = WCF.Language.get('wcf.date.relative.pastDays', { days: days, day: day, time: elTime });
				}
				// timestamp is between ~700 million years BC and last week
				else {
					element.textContent = WCF.Language.get('wcf.date.shortDateTimeFormat').replace(/\%date\%/, elDate).replace(/\%time\%/, elTime);
				}
			}
		}
	};
	
	return new DateTimeRelative();
});

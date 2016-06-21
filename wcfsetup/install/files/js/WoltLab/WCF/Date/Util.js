/**
 * Provides utility functions for date operations.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Date/Util
 */
define(['Language'], function(Language) {
	"use strict";
	
	/**
	 * @exports	WoltLab/WCF/Date/Util
	 */
	var DateUtil = {
		/**
		 * Returns the formatted date.
		 * 
		 * @param	{Date}		date		date object
		 * @returns	{string}	formatted date
		 */
		formatDate: function(date) {
			return this.format(date, Language.get('wcf.date.dateFormat'));
		},
		
		/**
		 * Returns the formatted time.
		 * 
		 * @param	{Date}		date		date object
		 * @returns	{string}	formatted time
		 */
		formatTime: function(date) {
			return this.format(date, Language.get('wcf.date.timeFormat'));
		},
		
		/**
		 * Returns the formatted date time.
		 * 
		 * @param	{Date}		date		date object
		 * @returns	{string}	formatted date time
		 */
		formatDateTime: function(date) {
			return this.format(date, Language.get('wcf.date.dateTimeFormat').replace(/%date%/, Language.get('wcf.date.dateFormat')).replace(/%time%/, Language.get('wcf.date.timeFormat')));
		},
		
		/**
		 * Formats a date using PHP's `date()` modifiers.
		 * 
		 * @param	{Date}		date		date object
		 * @param	{string}	format		output format
		 * @returns	{string}	formatted date
		 */
		format: function(date, format) {
			var char;
			var out = '';
			
			// ISO 8601 date, best recognition by PHP's strtotime()
			if (format === 'c') {
				format = 'Y-m-dTH:i:sP';
			}
			
			for (var i = 0, length = format.length; i < length; i++) {
				switch (format[i]) {
					// seconds
					case 's':
						// `00` through `59`
						char = ('0' + date.getSeconds().toString()).slice(-2);
						break;
					
					// minutes
					case 'i':
						// `00` through `59`
						char = date.getMinutes();
						if (char < 10) char = "0" + char;
						break;
					
					// hours
					case 'a':
						// `am` or `pm`
						char = (date.getHours() > 11) ? 'pm' : 'am';
						break;
					case 'g':
						// `1` through `12`
						char = date.getHours();
						if (char === 0) char = 12;
						else if (char > 12) char -= 12;
						break;
					case 'h':
						// `01` through `12`
						char = date.getHours();
						if (char === 0) char = 12;
						else if (char > 12) char -= 12;
						
						char = ('0' + char.toString()).slice(-2);
						break;
					case 'A':
						// `AM` or `PM`
						char = (date.getHours() > 11) ? 'PM' : 'AM';
						break;
					case 'G':
						// `0` through `23`
						char = date.getHours();
						break;
					case 'H':
						// `00` through `23`
						char = date.getHours();
						char = ('0' + char.toString()).slice(-2);
						break;
					
					// day
					case 'd':
						// `01` through `31`
						char = date.getDate();
						char = ('0' + char.toString()).slice(-2);
						break;
					case 'j':
						// `1` through `31`
						char = date.getDate();
						break;
					case 'l':
						// `Monday` through `Sunday` (localized)
						char = Language.get('__days')[date.getDay()];
						break;
					case 'D':
						// `Mon` through `Sun` (localized)
						char = Language.get('__daysShort')[date.getDay()];
						break;
					case 'S':
						// ignore english ordinal suffix
						char = '';
						break;
					
					// month
					case 'm':
						// `01` through `12`
						char = date.getMonth() + 1;
						char = ('0' + char.toString()).slice(-2);
						break;
					case 'n':
						// `1` through `12`
						char = date.getMonth() + 1;
						break;
					case 'F':
						// `January` through `December` (localized)
						char = Language.get('__months')[date.getMonth()];
						break;
					case 'M':
						// `Jan` through `Dec` (localized)
						char = Language.get('__monthsShort')[date.getMonth()];
						break;
					
					// year
					case 'y':
						// `00` through `99`
						char = date.getYear().toString().replace(/^\d{2}/, '');
						break;
					case 'Y':
						// Examples: `1988` or `2015`
						char = date.getFullYear();
						break;
					
					// timezone
					case 'P':
						var offset = date.getTimezoneOffset();
						char = (offset > 0) ? '-' : '+';
						
						offset = Math.abs(offset);
						
						char += ('0' + (~~(offset / 60)).toString()).slice(-2);
						char += ':';
						char += ('0' + (offset % 60).toString()).slice(-2);
						
						break;
						
					// specials
					case 'r':
						char = date.toString();
						break;
					case 'U':
						char = Math.round(date.getTime() / 1000);
						break;
					
					default:
						char = format[i];
						break;
				}
				
				out += char;
			}
			
			return out;
		},
		
		/**
		 * Returns UTC timestamp, if date is not given, current time will be used.
		 * 
		 * @param	{Date}		date	target date
		 * @return	{int}		UTC timestamp in seconds
		 */
		gmdate: function(data) {
			if (!(date instanceof Date)) {
				date = new Date();
			}
			
			return Math.round(Date.UTC(
				date.getUTCFullYear(),
				date.getUTCMonth(),
				date.getUTCDay(),
				date.getUTCHours(),
				date.getUTCMinutes(),
				date.getUTCSeconds()
			) / 1000);
		},
		
		/**
		 * Returns a Date object with precise offset (including timezone and local timezone).
		 * 
		 * @param	{int}		timestamp	timestamp in miliseconds
		 * @param	{int}		offset		timezone offset in miliseconds
		 * @return	{Date}		localized date
		 */
		getTimezoneDate: function(timestamp, offset) {
			var date = new Date(timestamp);
			var localOffset = date.getTimezoneOffset() * 60000;
			
			return new Date((timestamp + localOffset + offset));
		}
	};
	
	return DateUtil;
});

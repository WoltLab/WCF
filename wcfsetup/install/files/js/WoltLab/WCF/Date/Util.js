/**
 * Provides utility functions for date operations.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Date/Util
 */
define([], function() {
	"use strict";
	
	/**
	 * @exports	WoltLab/WCF/Date/Util
	 */
	var DateUtil = {
		/**
		 * Returns UTC timestamp, if date is not given, current time will be used.
		 * 
		 * @param	Date		date	target date
		 * @return	integer		UTC timestamp in seconds
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
		 * @param	integer		timestamp	timestamp in miliseconds
		 * @param	integer		offset		timezone offset in miliseconds
		 * @return	Date		localized date
		 */
		getTimezoneDate: function(timestamp, offset) {
			var date = new Date(timestamp);
			var localOffset = date.getTimezoneOffset() * 60000;
			
			return new Date((timestamp + localOffset + offset));
		}
	};
	
	return DateUtil;
});

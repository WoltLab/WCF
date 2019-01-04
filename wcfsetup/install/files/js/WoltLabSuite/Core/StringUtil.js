/**
 * Provides helper functions for String handling.
 * 
 * @author	Tim Duesterhus, Joshua Ruesweg
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/StringUtil
 */
define(['Language', './NumberUtil'], function(Language, NumberUtil) {
	"use strict";
	
	/**
	 * @exports	WoltLabSuite/Core/StringUtil
	 */
	return {
		/**
		 * Adds thousands separators to a given number.
		 * 
		 * @see		http://stackoverflow.com/a/6502556/782822
		 * @param	{?}	number
		 * @return	{String}
		 */
		addThousandsSeparator: function(number) {
			// Fetch Language, as it cannot be provided because of a circular dependency
			if (Language === undefined) Language = require('Language');
			
			return String(number).replace(/(^-?\d{1,3}|\d{3})(?=(?:\d{3})+(?:$|\.))/g, '$1' + Language.get('wcf.global.thousandsSeparator'));
		},
		
		/**
		 * Escapes special HTML-characters within a string
		 * 
		 * @param	{?}	string
		 * @return	{String}
		 */
		escapeHTML: function (string) {
			return String(string).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
		},
		
		/**
		 * Escapes a String to work with RegExp.
		 * 
		 * @see		https://github.com/sstephenson/prototype/blob/master/src/prototype/lang/regexp.js#L25
		 * @param	{?}	string
		 * @return	{String}
		 */
		escapeRegExp: function(string) {
			return String(string).replace(/([.*+?^=!:${}()|[\]\/\\])/g, '\\$1');
		},
		
		/**
		 * Rounds number to given count of floating point digits, localizes decimal-point and inserts thousands separators.
		 * 
		 * @param	{?}		number
		 * @param	{int}		decimalPlaces	The number of decimal places to leave after rounding.
		 * @return	{String}
		 */
		formatNumeric: function(number, decimalPlaces) {
			// Fetch Language, as it cannot be provided because of a circular dependency
			if (Language === undefined) Language = require('Language');
			
			number = String(NumberUtil.round(number, decimalPlaces || -2));
			var numberParts = number.split('.');
			
			number = this.addThousandsSeparator(numberParts[0]);
			if (numberParts.length > 1) number += Language.get('wcf.global.decimalPoint') + numberParts[1];
			
			number = number.replace('-', '\u2212');
			
			return number;
		},
		
		/**
		 * Makes a string's first character lowercase.
		 * 
		 * @param	{?}		string
		 * @return	{String}
		 */
		lcfirst: function(string) {
			return String(string).substring(0, 1).toLowerCase() + string.substring(1);
		},
		
		/**
		 * Makes a string's first character uppercase.
		 * 
		 * @param	{?}		string
		 * @return	{String}
		 */
		ucfirst: function(string) {
			return String(string).substring(0, 1).toUpperCase() + string.substring(1);
		},
		
		/**
		 * Unescapes special HTML-characters within a string.
		 * 
		 * @param	{?}		string
		 * @return	{String}
		 */
		unescapeHTML: function(string) {
			return String(string).replace(/&amp;/g, '&').replace(/&quot;/g, '"').replace(/&lt;/g, '<').replace(/&gt;/g, '>');
		},
		
		/**
		 * Shortens numbers larger than 1000 by using unit suffixes.
		 *
		 * @param	{?}		number
		 * @return	{String}
		 */
		shortUnit: function(number) {
			var unitSuffix = '';
			
			if (number >= 1000000) {
				number /= 1000000;
				
				if (number > 10) {
					number = Math.floor(number);
				}
				else {
					number = NumberUtil.round(number, -1);
				}
				
				unitSuffix = 'M';
			}
			else if (number >= 1000) {
				number /= 1000;
				
				if (number > 10) {
					number = Math.floor(number);
				}
				else {
					number = NumberUtil.round(number, -1);
				}
				
				unitSuffix = 'k';
			}
			
			return this.formatNumeric(number) + unitSuffix;
		}
	};
});

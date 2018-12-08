/**
 * Provides helper functions for Number handling.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/NumberUtil
 */
define([], function() {
	"use strict";
	
	/**
	 * @exports	WoltLabSuite/Core/NumberUtil
	 */
	var NumberUtil = {
		/**
		 * Decimal adjustment of a number.
		 *
		 * @see	https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Math/round
		 * @param	{Number}	value	The number.
		 * @param	{Integer}	exp	The exponent (the 10 logarithm of the adjustment base).
		 * @returns	{Number}	The adjusted value.
		 */
		round: function (value, exp) {
			// If the exp is undefined or zero...
			if (typeof exp === 'undefined' || +exp === 0) {
				return Math.round(value);
			}
			value = +value;
			exp = +exp;
			
			// If the value is not a number or the exp is not an integer...
			if (isNaN(value) || !(typeof exp === 'number' && exp % 1 === 0)) {
				return NaN;
			}
			
			// Shift
			value = value.toString().split('e');
			value = Math.round(+(value[0] + 'e' + (value[1] ? (+value[1] - exp) : -exp)));
			
			// Shift back
			value = value.toString().split('e');
			return +(value[0] + 'e' + (value[1] ? (+value[1] + exp) : exp));
		}
	};
	
	return NumberUtil;
});

/**
 * Provides helper functions for file handling.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/FileUtil
 */
define(['StringUtil'], function(StringUtil) {
	"use strict";
	
	return {
		/**
		 * Formats the given filesize.
		 * 
		 * @param	{integer}	byte		number of bytes
		 * @param	{integer}	precision	number of decimals
		 * @return	{string}	formatted filesize
		 */
		formatFilesize: function(byte, precision) {
			if (precision === undefined) {
				precision = 2;
			}
			
			var symbol = 'Byte';
			if (byte >= 1000) {
				byte /= 1000;
				symbol = 'kB';
			}
			if (byte >= 1000) {
				byte /= 1000;
				symbol = 'MB';
			}
			if (byte >= 1000) {
				byte /= 1000;
				symbol = 'GB';
			}
			if (byte >= 1000) {
				byte /= 1000;
				symbol = 'TB';
			}
			
			return StringUtil.formatNumeric(byte, -precision) + ' ' + symbol;
		}
	};
});

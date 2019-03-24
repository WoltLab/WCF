define([], function() {
	"use strict";
	
	var ColorUtil = {
		/**
		 * Converts HEX into RGB.
		 *
		 * @param	string		hex     hex value as #ccc or #abc123
		 * @return	object          r-g-b values
		 */
		hexToRgb: function(hex) {
			hex = hex.replace(/^#/, '');
			if (/^([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/.test(hex)) {
				// only convert abc and abcdef
				hex = hex.split('');
				
				// parse shorthand #xyz
				if (hex.length === 3) {
					return {
						r: parseInt(hex[0] + '' + hex[0], 16),
						g: parseInt(hex[1] + '' + hex[1], 16),
						b: parseInt(hex[2] + '' + hex[2], 16)
					};
				}
				else {
					return {
						r: parseInt(hex[0] + '' + hex[1], 16),
						g: parseInt(hex[2] + '' + hex[3], 16),
						b: parseInt(hex[4] + '' + hex[5], 16)
					};
				}
			}
			
			return Number.NaN;
		},
		
		/**
		 * Converts RGB into HEX.
		 *
		 * @see	http://www.linuxtopia.org/online_books/javascript_guides/javascript_faq/rgbtohex.htm
		 * 
		 * @param	{(int|string)}	r       red or rgb(1, 2, 3) or rgba(1, 2, 3, .4)
		 * @param	{int}		g       green
		 * @param	{int}		b       blue
		 * @return	{string}        hex value #abc123
		 */
		rgbToHex: function(r, g, b) {
			var charList = "0123456789ABCDEF";
			
			if (g === undefined) {
				if (r.match(/^rgba?\((\d+), ?(\d+), ?(\d+)(?:, ?[0-9.]+)?\)$/)) {
					r = RegExp.$1;
					g = RegExp.$2;
					b = RegExp.$3;
				}
			}
			
			return (charList.charAt((r - r % 16) / 16) + '' + charList.charAt(r % 16)) + '' + (charList.charAt((g - g % 16) / 16) + '' + charList.charAt(g % 16)) + '' + (charList.charAt((b - b % 16) / 16) + '' + charList.charAt(b % 16));
		}
	};
	
	return ColorUtil;
});

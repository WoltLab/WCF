/**
 * Provides helper functions for Image metadata handling.
 *
 * @author	Tim Duesterhus
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Image/ImageUtil
 */
define([], function() {
	"use strict";
	
	return {
		/**
		 * Returns whether the given canvas contains transparent pixels.
		 *
		 * @param       image   {Canvas}  Canvas to check
		 * @returns             {bool}
		 */
		containsTransparentPixels: function (canvas) {
			var imageData = canvas.getContext('2d').getImageData(0, 0, canvas.width, canvas.height);
			
			for (var i = 3, max = imageData.data.length; i < max; i += 4) {
				if (imageData.data[i] !== 255) return true;
			}
			
			return false;
		}
	};
});

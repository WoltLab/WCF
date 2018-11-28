/**
 * Wrapper around the web browser's various clipboard APIs.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Clipboard
 */
define([], function() {
	"use strict";
	
	return {
		copyElementTextToClipboard: function (element) {
			var promise;
			if (navigator.clipboard) {
				promise = navigator.clipboard.writeText(element.innerText);
			}
			else {
				promise = Promise.reject('navigator.clipboard is not supported');
			}
			
			return promise.catch(function () {
				if (!window.getSelection) throw new Error('window.getSelection is not supported');
				
				var range = document.createRange();
				range.selectNode(element);
				window.getSelection().addRange(range);
				if (!document.execCommand('copy')) {
					throw new Error("execCommand('copy') failed");
				}
			})
		}
	};
});

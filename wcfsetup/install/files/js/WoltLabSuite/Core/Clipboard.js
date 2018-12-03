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
		copyTextToClipboard: function (text) {
			if (navigator.clipboard) {
				return navigator.clipboard.writeText(text);
			}
			else if (window.getSelection) {
				var textarea = elCreate('textarea');
				textarea.style.cssText = 'position: absolute; left: -9999px; width: 0;';
				document.body.appendChild(textarea);
				try {
					textarea.value = text;
					textarea.select();
					if (!document.execCommand('copy')) {
						return Promise.reject(new Error("execCommand('copy') failed"));
					}
					return Promise.resolve();
				}
				finally {
					elRemove(textarea);
				}
			}
			
			return Promise.reject(new Error('Neither navigator.clipboard, nor window.getSelection is supported.'));
		},
		
		copyElementTextToClipboard: function (element) {
			return this.copyTextToClipboard(element.textContent);
		}
	};
});

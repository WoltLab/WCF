/**
 * Wrapper around the web browser's various clipboard APIs.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Clipboard
 */
define(['Environment', 'Ui/Screen'], function(Environment, UiScreen) {
	"use strict";
	
	return {
		copyTextToClipboard: function (text) {
			if (navigator.clipboard) {
				return navigator.clipboard.writeText(text);
			}
			else if (window.getSelection) {
				var textarea = elCreate('textarea');
				textarea.contentEditable = true;
				textarea.readOnly = false;
				
				// iOS has some implicit restrictions that, if crossed, cause the browser to scroll to the top.
				var scrollDisabled = false;
				if (Environment.platform() === 'ios') {
					scrollDisabled = true;
					UiScreen.scrollDisable();
					
					var topPx = (~~(window.innerHeight / 4) + window.pageYOffset);
					textarea.style.cssText = 'font-size: 16px; position: absolute; left: 1px; top: ' + topPx + 'px; width: 50px; height: 50px; overflow: hidden;border: 5px solid red;';
				}
				else {
					textarea.style.cssText = 'position: absolute; left: -9999px; top: -9999px; width: 0; height: 0;';
				}
				
				document.body.appendChild(textarea);
				try {
					// see: https://stackoverflow.com/a/34046084/782822
					textarea.value = text;
					var range = document.createRange();
					range.selectNodeContents(textarea);
					var selection = window.getSelection();
					selection.removeAllRanges();
					selection.addRange(range);
					textarea.setSelectionRange(0, 999999);
					if (!document.execCommand('copy')) {
						return Promise.reject(new Error("execCommand('copy') failed"));
					}
					return Promise.resolve();
				}
				finally {
					elRemove(textarea);
					
					if (scrollDisabled) {
						UiScreen.scrollEnable();
					}
				}
			}
			
			return Promise.reject(new Error('Neither navigator.clipboard, nor window.getSelection is supported.'));
		},
		
		copyElementTextToClipboard: function (element) {
			return this.copyTextToClipboard(element.textContent);
		}
	};
});

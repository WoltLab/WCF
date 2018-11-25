/**
 * Highlights code in the Code bbcode.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Bbcode/Code
 */
define(['WoltLabSuite/Core/Prism', 'prism/prism-meta'], function(Prism, PrismMeta) {
	"use strict";
	
	/** @const */ var CHUNK_SIZE = 50;
	
	// Define idleify() for piecewiese highlighting to not block the UI thread.
	var idleify = function (callback) {
		return function () {
			var args = arguments;
			return new Promise(function (resolve, reject) {
				var body = function () {
					try {
						resolve(callback.apply(null, args));
					}
					catch (e) {
						reject(e);
					}
				};
				
				if (window.requestIdleCallback) {
					window.requestIdleCallback(body, { timeout: 5000 });
				}
				else {
					setTimeout(body, 0);
				}
			});
		};
	};
	
	/**
	 * @constructor
	 */
	function Code(container) {
		var matches;
		
		this.container = container;
		this.codeContainer = elBySel('.codeBoxCode > code', this.container);
		this.language = null;
		for (var i = 0; i < this.codeContainer.classList.length; i++) {
			if ((matches = this.codeContainer.classList[i].match(/language-(.*)/))) {
				this.language = matches[1];
			}
		}
	}
	Code.highlightAll = function () {
		elBySelAll('.codeBox:not(.highlighting):not(.highlighted)', document, function (codeBox) {
			var handle = new Code(codeBox);
			if (handle.language) handle.highlight();
		})
	};
	Code.prototype = {
		highlight: function () {
			if (!this.language) {
				return Promise.reject(new Error('No language detected'));
			}
			if (!PrismMeta[this.language]) {
				return Promise.reject(new Error('Unknown language ' + this.language));
			}
			
			this.container.classList.add('highlighting');
			
			return require(['prism/components/prism-' + PrismMeta[this.language].file])
			.then(idleify(function () {
				var highlighted = Prism.highlightSeparateLines(this.codeContainer.textContent, this.language);
				var highlightedLines = elBySelAll('[data-number]', highlighted);
				var originalLines = elBySelAll('.codeBoxLine > span', this.codeContainer);
				
				if (highlightedLines.length !== originalLines.length) {
					throw new Error('Unreachable');
				}
				
				var promises = [];
				for (var chunkStart = 0, max = highlightedLines.length; chunkStart < max; chunkStart += CHUNK_SIZE) {
					promises.push(idleify(function (chunkStart) {
						var chunkEnd = Math.min(chunkStart + CHUNK_SIZE, max);
						
						for (var offset = chunkStart; offset < chunkEnd; offset++) {
							originalLines[offset].parentNode.replaceChild(highlightedLines[offset], originalLines[offset]);
						}
					})(chunkStart));
				}
				return Promise.all(promises);
			}.bind(this)))
			.then(function () {
				this.container.classList.remove('highlighting');
				this.container.classList.add('highlighted');
			}.bind(this))
		}
	}
	
	return Code;
});

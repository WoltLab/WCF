/**
 * Inserts smilies into a WYSIWYG editor instance, with WAI-ARIA keyboard support.
 * 
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module      WoltLabSuite/Core/Ui/Smiley/Insert
 */
define(['EventHandler', 'EventKey'], function (EventHandler, EventKey) {
	'use strict';
	
	function UiSmileyInsert(editorId) { this.init(editorId); }
	
	UiSmileyInsert.prototype = {
		_editorId: '',
		
		/**
		 * @param {string} editorId
		 */
		init: function (editorId) {
			this._editorId = editorId;
			
			var container = elById('smilies-' + this._editorId);
			if (!container) {
				throw new Error('Unable to find the message tab menu container containing the smilies.');
			}
			
			container.addEventListener('keydown', this._keydown.bind(this));
			container.addEventListener('mousedown', this._mousedown.bind(this));
		},
		
		/**
		 * @param {KeyboardEvent} event
		 * @protected
		 */
		_keydown: function(event) {
			var activeButton = document.activeElement;
			if (!activeButton.classList.contains('jsSmiley')) {
				return;
			}
			
			if (EventKey.ArrowLeft(event) || EventKey.ArrowRight(event) || EventKey.Home(event) || EventKey.End(event)) {
				event.preventDefault();
				
				var smilies = Array.prototype.slice.call(elBySelAll('.jsSmiley', event.currentTarget));
				if (EventKey.ArrowLeft(event)) {
					smilies.reverse();
				}
				
				var index = smilies.indexOf(activeButton);
				if (EventKey.Home(event)) {
					index = 0;
				}
				else if (EventKey.End(event)) {
					index = smilies.length - 1;
				}
				else {
					index = index + 1;
					if (index === smilies.length) {
						index = 0;
					}
				}
				
				smilies[index].focus();
			}
			else if (EventKey.Enter(event) || EventKey.Space(event)) {
				event.preventDefault();
				
				this._insert(elBySel('img', activeButton));
			}
		},
		
		/**
		 * @param {MouseEvent} event
		 * @protected
		 */
		_mousedown: function (event) {
			event.preventDefault();
			
			// Clicks may occur on a few different elements, but we are only looking for the image.
			var listItem = event.target.closest('li');
			var img = elBySel('img', listItem);
			if (img) this._insert(img);
		},
		
		/**
		 * @param {Element} img
		 * @protected
		 */
		_insert: function(img) {
			EventHandler.fire('com.woltlab.wcf.redactor2', 'insertSmiley_' + this._editorId, {
				img: img
			});
		}
	};
	return UiSmileyInsert;
});

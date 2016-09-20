/**
 * Manages the autosave process storing the current editor message in the local
 * storage to recover it on browser crash or accidental navigation.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Ui/Redactor/Autosave
 */
define(['Language', 'Dom/Traverse'], function(Language, DomTraverse) {
	"use strict";
	
	// time between save requests in seconds
	var _frequency = 15;
	
	//noinspection JSUnresolvedVariable
	var _prefix = 'wsc' + window.WCF_PATH.hashCode() + '-';
	
	/**
	 * @param       {Element}       element         textarea element
	 * @constructor
	 */
	function UiRedactorAutosave(element) { this.init(element); }
	UiRedactorAutosave.prototype = {
		/**
		 * Initializes the autosave handler and removes outdated messages from storage.
		 * 
		 * @param       {Element}       element         textarea element
		 */
		init: function (element) {
			this._container = null;
			this._editor = null;
			this._element = element;
			this._key = _prefix + elData(this._element, 'autosave');
			this._lastMessage = '';
			this._originalMessage = '';
			this._overlay = null;
			this._restored = false;
			this._timer = null;
			
			this._cleanup();
			
			// remove attribute to prevent Redactor's built-in autosave to kick in
			this._element.removeAttribute('data-autosave');
			
			var form = DomTraverse.parentByTag(this._element, 'FORM');
			if (form !== null) {
				form.addEventListener('submit', this.destroy.bind(this));
			}
		},
		
		/**
		 * Returns the initial value for the textarea, used to inject message
		 * from storage into the editor before initialization.
		 * 
		 * @return      {string}        message content
		 */
		getInitialValue: function() {
			var value = '';
			try {
				value = window.localStorage.getItem(this._key);
			}
			catch (e) {
				window.console.warn("Unable to access local storage: " + e.message);
			}
			
			try {
				value = JSON.parse(value);
			}
			catch (e) {
				value = '';
			}
			
			// check if storage is outdated
			if (value !== null && typeof value === 'object') {
				var lastEditTime = ~~elData(this._element, 'autosave-last-edit-time');
				if (lastEditTime * 1000 > value.timestamp) {
					//noinspection JSUnresolvedVariable
					return this._element.value;
				}
				
				//noinspection JSUnresolvedVariable
				this._originalMessage = this._element.value;
				this._restored = true;
				
				return value.content;
			}
			
			//noinspection JSUnresolvedVariable
			return this._element.value;
		},
		
		/**
		 * Enables periodical save of editor contents to local storage.
		 * 
		 * @param       {$.Redactor}    editor  redactor instance
		 */
		watch: function(editor) {
			this._editor = editor;
			
			if (this._timer !== null) {
				throw new Error("Autosave timer is already active.");
			}
			
			this._timer = window.setInterval(this._saveToStorage.bind(this), _frequency * 1000);
			
			this._saveToStorage();
		},
		
		/**
		 * Disables autosave handler, for use on editor destruction.
		 */
		destroy: function () {
			this.clear();
			
			this._editor = null;
			
			window.clearInterval(this._timer);
			this._timer = null;
		},
		
		/**
		 * Removed the stored message, for use after a message has been submitted.
		 */
		clear: function () {
			this._lastMessage = '';
			
			try {
				window.localStorage.removeItem(this._key);
			}
			catch (e) {
				window.console.warn("Unable to remove from local storage: " + e.message);
			}
		},
		
		/**
		 * Creates the autosave controls, used to keep or discard the restored draft.
		 */
		createOverlay: function () {
			if (!this._restored) {
				return;
			}
			
			var container = elCreate('div');
			container.className = 'redactorAutosaveRestored active';
			
			var title = elCreate('span');
			title.textContent = Language.get('wcf.editor.autosave.restored');
			container.appendChild(title);
			
			var button = elCreate('a');
			button.href = '#';
			button.innerHTML = '<span class="icon icon16 fa-check green"></span>';
			button.addEventListener(WCF_CLICK_EVENT, (function (event) {
				event.preventDefault();
				
				this.hideOverlay();
			}).bind(this));
			container.appendChild(button);
			
			button = elCreate('a');
			button.href = '#';
			button.innerHTML = '<span class="icon icon16 fa-times red"></span>';
			button.addEventListener(WCF_CLICK_EVENT, (function (event) {
				event.preventDefault();
				
				// remove from storage
				this.clear();
				
				// set code
				this._editor.code.start(this._originalMessage);
				
				// set value
				this._editor.core.textarea().val(this._editor.clean.onSync(this._editor.$editor.html()));
				
				this.hideOverlay();
			}).bind(this));
			container.appendChild(button);
			
			this._editor.core.box()[0].appendChild(container);
			
			this._container = container;
		},
		
		/**
		 * Hides the autosave controls.
		 */
		hideOverlay: function () {
			if (this._container !== null) {
				this._container.classList.remove('active');
				
				window.setTimeout((function () {
					elRemove(this._container);
					
					this._container = null;
					this._originalMessage = '';
				}).bind(this), 1000);
			}
		},
		
		/**
		 * Saves the current message to storage unless there was no change.
		 * 
		 * @protected
		 */
		_saveToStorage: function() {
			var content = this._editor.code.get();
			if (this._editor.utils.isEmpty(content)) {
				content = '';
			}
			
			if (this._lastMessage === content) {
				// break if content hasn't changed
				return;
			}
			
			try {
				window.localStorage.setItem(this._key, JSON.stringify({
					content: content,
					timestamp: Date.now()
				}));
				
				this._lastMessage = content;
				
				this.hideOverlay();
			}
			catch (e) {
				window.console.warn("Unable to write to local storage: " + e.message);
			}
		},
		
		/**
		 * Removes stored messages older than one week.
		 * 
		 * @protected
		 */
		_cleanup: function () {
			var oneWeekAgo = Date.now() - (7 * 24 * 3600 * 1000), removeKeys = [];
			var i, key, length, value;
			for (i = 0, length = window.localStorage.length; i < length; i++) {
				key = window.localStorage.key(i);
				
				// check if key matches our prefix
				if (key.indexOf(_prefix) !== 0) {
					continue;
				}
				
				try {
					value = window.localStorage.getItem(key);
				}
				catch (e) {
					window.console.warn("Unable to access local storage: " + e.message);
				}
				
				try {
					value = JSON.parse(value);
				}
				catch (e) {
					value = { timestamp: 0 };
				}
				
				if (!value || value.timestamp < oneWeekAgo) {
					removeKeys.push(key);
				}
			}
			
			for (i = 0, length = removeKeys.length; i < length; i++) {
				try {
					window.localStorage.removeItem(removeKeys[i]);
				}
				catch (e) {
					window.console.warn("Unable to remove from local storage: " + e.message);
				}
			}
		}
	};
	
	return UiRedactorAutosave;
});

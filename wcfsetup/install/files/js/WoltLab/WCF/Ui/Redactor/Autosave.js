/**
 * Manages the autosave process storing the current editor message in the local
 * storage to recover it on browser crash or accidental navigation.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Ui/Redactor/Autosave
 */
define([], function() {
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
			this._editor = null;
			this._element = element;
			this._key = _prefix + elData(this._element, 'autosave');
			this._lastMessage = '';
			this._timer = null;
			
			this._cleanup();
			
			// remove attribute to prevent Redactor's built-in autosave to kick in
			this._element.removeAttribute('data-autosave');
		},
		
		/**
		 * Returns the initial value for the textarea, used to inject message
		 * from storage into the editor before initialization.
		 * 
		 * @return      {string}        message content
		 */
		getInitialValue: function () {
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
			var oneWeekAgo = Date.now() - (7 * 24 * 3600 * 1000);
			var key, value;
			for (var i = 0, length = window.localStorage.length; i < length; i++) {
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
					try {
						window.localStorage.removeItem(key);
					}
					catch (e) {
						window.console.warn("Unable to remove from local storage: " + e.message);
					}
				}
			}
		}
	};
	
	return UiRedactorAutosave;
});

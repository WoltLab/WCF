/**
 * Handles user interaction with the quick reply feature.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Ui/Message/Reply
 */
define(['Ajax', 'Core', 'EventHandler', 'Language', 'Dom/Util', 'Ui/Notification', '../Scroll'], function(Ajax, Core, EventHandler, Language, DomUtil, UiNotification, UiScroll) {
	"use strict";
	
	/**
	 * @constructor
	 */
	function UiMessageReply(options) { this.init(options); }
	UiMessageReply.prototype = {
		/**
		 * Initializes a new quick reply field.
		 * 
		 * @param       {Object}        options         configuration options
		 */
		init: function(options) {
			this._options = Core.extend({
				ajax: {
					className: ''
				},
				quoteManager: null,
				successMessage: 'wcf.global.success.add'
			}, options);
			
			this._container = elById('messageQuickReply');
			this._content = elBySel('.messageContent', this._container);
			this._textarea = elById('text');
			this._editor = null;
			this._loadingOverlay = null;
			
			// prevent marking of text for quoting
			elBySel('.message', this._container).classList.add('jsInvalidQuoteTarget');
			
			// handle submit button
			var submitCallback = this._submit.bind(this);
			var submitButton = elBySel('button[data-type="save"]');
			submitButton.addEventListener(WCF_CLICK_EVENT, submitCallback);
			
			// bind reply button
			var replyButtons = elBySelAll('.jsQuickReply');
			for (var i = 0, length = replyButtons.length; i < length; i++) {
				replyButtons[i].addEventListener(WCF_CLICK_EVENT, (function(event) {
					event.preventDefault();
					
					UiScroll.element(this._container, (function() {
						this._getEditor().focus.end();
					}).bind(this));
				}).bind(this));
			}
			
			// TODO: add event listener for submit through keyboard in Redactor
		},
		
		/**
		 * Validates the message and submits it to the server.
		 * 
		 * @param       {Event}         event   event object
		 * @protected
		 */
		_submit: function(event) {
			event.preventDefault();
			
			if (!this._validate()) {
				// validation failed, bail out
				return;
			}
			
			this._showLoadingOverlay();
			
			// build parameters
			var parameters = DomUtil.getDataAttributes(this._container, 'data-', true, true);
			parameters.data = { message: this._getEditor().code.get() };
			parameters.removeQuoteIDs = (this._options.quoteManager) ? this._options.quoteManager.getQuotesMarkedForRemoval() : [];
			
			EventHandler.fire('com.woltlab.wcf.redactor2', 'submit_text', parameters.data);
			
			Ajax.api(this, {
				parameters: parameters
			});
		},
		
		/**
		 * Validates the message and invokes listeners to perform additional validation.
		 * 
		 * @return      {boolean}       validation result
		 * @protected
		 */
		_validate: function() {
			// remove all existing error elements
			var errorMessages = elByClass('innerError', this._container);
			while (errorMessages.length) {
				elRemove(errorMessages[0]);
			}
			
			// check if editor contains actual content
			if (this._getEditor().utils.isEmpty()) {
				this.throwError(this._textarea, Language.get('wcf.global.form.error.empty'));
				return false;
			}
			
			var data = {
				api: this,
				editor: this._getEditor(),
				message: this._getEditor().code.get(),
				valid: true
			};
			
			EventHandler.fire('com.woltlab.wcf.redactor2', 'validate_text', data);
			
			return (data.valid !== false);
		},
		
		/**
		 * Throws an error by adding an inline error to target element.
		 * 
		 * @param       {Element}       element         erroneous element
		 * @param       {string}        message         error message
		 */
		throwError: function(element, message) {
			var error = elCreate('small');
			error.className = 'innerError';
			error.textContent = message;
			
			DomUtil.insertAfter(error, element);
		},
		
		/**
		 * Displays a loading spinner while the request is processed by the server.
		 * 
		 * @protected
		 */
		_showLoadingOverlay: function() {
			if (this._loadingOverlay === null) {
				this._loadingOverlay = elCreate('div');
				this._loadingOverlay.className = 'messageContentLoadingOverlay';
				this._loadingOverlay.innerHTML = '<span class="icon icon96 fa-spinner"></span>';
			}
			
			this._content.classList.add('loading');
			this._content.appendChild(this._loadingOverlay);
		},
		
		/**
		 * Hides the loading spinner.
		 * 
		 * @protected
		 */
		_hideLoadingOverlay: function() {
			this._content.classList.remove('loading');
			
			var loadingOverlay = elBySel('.messageContentLoadingOverlay', this._content);
			if (loadingOverlay !== null) {
				loadingOverlay.parentNode.removeChild(loadingOverlay);
			}
		},
		
		/**
		 * Resets the editor contents and notifies event listeners.
		 * 
		 * @protected
		 */
		_reset: function() {
			this._getEditor().code.set('<p>\u200b</p>');
			
			EventHandler.fire('com.woltlab.wcf.redactor2', 'reset_text');
		},
		
		/**
		 * Handles errors occured during server processing.
		 * 
		 * @param       {Object}        data    response data
		 * @protected
		 */
		_handleError: function(data) {
			//noinspection JSUnresolvedVariable
			this.throwError(this._textarea, data.returnValues.errorType);
		},
		
		/**
		 * Returns the current editor instance.
		 * 
		 * @return      {Object}       editor instance
		 * @protected
		 */
		_getEditor: function() {
			if (this._editor === null) {
				if (typeof window.jQuery === 'function') {
					this._editor = window.jQuery(this._textarea).data('redactor');
				}
				else {
					throw new Error("Unable to access editor, jQuery has not been loaded yet.");
				}
			}
			
			return this._editor;
		},
		
		/**
		 * Inserts the rendered message into the post list, unless the post is on the next
		 * page in which case a redirect will be performed instead.
		 * 
		 * @param       {Object}        data    response data
		 * @protected
		 */
		_insertMessage: function(data) {
			// TODO: clear autosave content and disable it
			
			// redirect to new page
			//noinspection JSUnresolvedVariable
			if (data.returnValues.url) {
				//noinspection JSUnresolvedVariable
				window.location = data.returnValues.url;
			}
			else {
				//noinspection JSUnresolvedVariable
				if (data.returnValues.template) {
					var elementId;
					
					// insert HTML
					if (elData(this._container, 'sort-order') === 'DESC') {
						//noinspection JSUnresolvedVariable
						DomUtil.insertHtml(data.returnValues.template, this._container, 'after');
						elementId = DomUtil.identify(this._container.nextElementSibling);
					}
					else {
						//noinspection JSUnresolvedVariable
						DomUtil.insertHtml(data.returnValues.template, this._container, 'before');
						elementId = DomUtil.identify(this._container.previousElementSibling);
					}
					
					// update last post time
					//noinspection JSUnresolvedVariable
					elData(this._container, 'last-post-time', data.returnValues.lastPostTime);
					
					window.location.hash = elementId;
					UiScroll.element(elById(elementId));
				}
				
				UiNotification.show(Language.get(this._options.successMessage));
				
				// TODO: resume autosave
				
				if (this._options.quoteManager) {
					this._options.quoteManager.countQuotes();
				}
			}
		},
		
		_ajaxSuccess: function(data) {
			this._insertMessage(data);
			
			this._reset();
			
			this._hideLoadingOverlay();
		},
		
		_ajaxFailure: function(data) {
			this._hideLoadingOverlay();
			
			//noinspection JSUnresolvedVariable
			if (data === null || data.returnValues === undefined || data.returnValues.errorType === undefined) {
				return true;
			}
			
			this._handleError(data);
			
			return false;
		},
		
		_ajaxSetup: function() {
			return {
				data: {
					actionName: 'quickReply',
					className: this._options.ajax.className,
					interfaceName: 'wcf\\data\\IMessageQuickReplyAction'
				}
			};
		}
	};
	
	return UiMessageReply;
});

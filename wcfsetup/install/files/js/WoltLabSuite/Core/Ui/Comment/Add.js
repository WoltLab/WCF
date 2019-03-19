/**
 * Handles the comment add feature.
 * 
 * Warning: This implementation is also used for responses, but in a slightly
 *          modified version. Changes made to this class need to be verified
 *          against the response implementation.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Ui/Comment/Add
 */
define([
	'Ajax', 'Core', 'EventHandler', 'Language', 'Dom/ChangeListener', 'Dom/Util', 'Dom/Traverse', 'Ui/Dialog', 'Ui/Notification', 'WoltLabSuite/Core/Ui/Scroll', 'EventKey', 'User', 'WoltLabSuite/Core/Controller/Captcha'
],
function(
	Ajax, Core, EventHandler, Language, DomChangeListener, DomUtil, DomTraverse, UiDialog, UiNotification, UiScroll, EventKey, User, ControllerCaptcha
) {
	"use strict";
	
	if (!COMPILER_TARGET_DEFAULT) {
		var Fake = function() {};
		Fake.prototype = {
			init: function() {},
			_submitGuestDialog: function() {},
			_submit: function() {},
			_getParameters: function () {},
			_validate: function() {},
			throwError: function() {},
			_showLoadingOverlay: function() {},
			_hideLoadingOverlay: function() {},
			_reset: function() {},
			_handleError: function() {},
			_getEditor: function() {},
			_insertMessage: function() {},
			_ajaxSuccess: function() {},
			_ajaxFailure: function() {},
			_ajaxSetup: function() {},
			_cancelGuestDialog: function() {}
		};
		return Fake;
	}
	
	/**
	 * @constructor
	 */
	function UiCommentAdd(container) { this.init(container); }
	UiCommentAdd.prototype = {
		/**
		 * Initializes a new quick reply field.
		 * 
		 * @param       {Element}       container       container element
		 */
		init: function(container) {
			this._container = container;
			this._content = elBySel('.jsOuterEditorContainer', this._container);
			this._textarea = elBySel('.wysiwygTextarea', this._container);
			this._editor = null;
			this._loadingOverlay = null;
			
			this._content.addEventListener(WCF_CLICK_EVENT, (function (event) {
				if (this._content.classList.contains('collapsed')) {
					event.preventDefault();
					
					this._content.classList.remove('collapsed');
					
					this._focusEditor();
				}
			}).bind(this));
			
			// handle submit button
			var submitButton = elBySel('button[data-type="save"]', this._container);
			submitButton.addEventListener(WCF_CLICK_EVENT, this._submit.bind(this));
		},
		
		/**
		 * Scrolls the editor into view and sets the caret to the end of the editor.
		 * 
		 * @protected
		 */
		_focusEditor: function () {
			UiScroll.element(this._container, (function () {
				window.jQuery(this._textarea).redactor('WoltLabCaret.endOfEditor');
			}).bind(this));
		},
		
		/**
		 * Submits the guest dialog.
		 * 
		 * @param	{Event}		event
		 * @protected
		 */
		_submitGuestDialog: function(event) {
			// only submit when enter key is pressed
			if (event.type === 'keypress' && !EventKey.Enter(event)) {
				return;
			}
			
			var usernameInput = elBySel('input[name=username]', event.currentTarget.closest('.dialogContent'));
			if (usernameInput.value === '') {
				elInnerError(usernameInput, Language.get('wcf.global.form.error.empty'));
				usernameInput.closest('dl').classList.add('formError');
				
				return;
			}
			
			var parameters = {
				parameters: {
					data: {
						username: usernameInput.value
					}
				}
			};
			
			if (ControllerCaptcha.has('commentAdd')) {
				var data = ControllerCaptcha.getData('commentAdd');
				if (data instanceof Promise) {
					data.then((function (data) {
						parameters = Core.extend(parameters, data);
						this._submit(undefined, parameters);
					}).bind(this));
				}
				else {
					parameters = Core.extend(parameters, data);
					this._submit(undefined, parameters);
				}
			}
			else {
				this._submit(undefined, parameters);
			}
		},
		
		/**
		 * Validates the message and submits it to the server.
		 * 
		 * @param	{Event?}	event			event object
		 * @param	{Object?}	additionalParameters	additional parameters sent to the server
		 * @protected
		 */
		_submit: function(event, additionalParameters) {
			if (event) {
				event.preventDefault();
			}
			
			if (!this._validate()) {
				// validation failed, bail out
				return;
			}
			
			this._showLoadingOverlay();
			
			// build parameters
			var parameters = this._getParameters();
			
			EventHandler.fire('com.woltlab.wcf.redactor2', 'submit_text', parameters.data);
			
			if (!User.userId && !additionalParameters) {
				parameters.requireGuestDialog = true;
			}
			
			Ajax.api(this, Core.extend({
				parameters: parameters
			}, additionalParameters));
		},
		
		/**
		 * Returns the request parameters to add a comment.
		 * 
		 * @return      {{data: {message: string, objectID: number, objectTypeID: number}}}
		 * @protected
		 */
		_getParameters: function () {
			var commentList = this._container.closest('.commentList');
			
			return {
				data: {
					message: this._getEditor().code.get(),
					objectID: ~~elData(commentList, 'object-id'),
					objectTypeID: ~~elData(commentList, 'object-type-id')
				}
			};
		},
		
		/**
		 * Validates the message and invokes listeners to perform additional validation.
		 * 
		 * @return      {boolean}       validation result
		 * @protected
		 */
		_validate: function() {
			// remove all existing error elements
			elBySelAll('.innerError', this._container, elRemove);
			
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
			elInnerError(element, (message === 'empty' ? Language.get('wcf.global.form.error.empty') : message));
		},
		
		/**
		 * Displays a loading spinner while the request is processed by the server.
		 * 
		 * @protected
		 */
		_showLoadingOverlay: function() {
			if (this._loadingOverlay === null) {
				this._loadingOverlay = elCreate('div');
				this._loadingOverlay.className = 'commentLoadingOverlay';
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
			
			var loadingOverlay = elBySel('.commentLoadingOverlay', this._content);
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
			
			if (document.activeElement) {
				document.activeElement.blur();
			}
			
			this._content.classList.add('collapsed');
		},
		
		/**
		 * Handles errors occurred during server processing.
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
		 * Inserts the rendered message.
		 * 
		 * @param       {Object}        data    response data
		 * @return      {Element}       scroll target
		 * @protected
		 */
		_insertMessage: function(data) {
			// insert HTML
			//noinspection JSCheckFunctionSignatures
			DomUtil.insertHtml(data.returnValues.template, this._container, 'after');
			
			UiNotification.show(Language.get('wcf.global.success.add'));
			
			DomChangeListener.trigger();
			
			return this._container.nextElementSibling;
		},
		
		/**
		 * @param {{returnValues:{guestDialog:string}}} data
		 * @protected
		 */
		_ajaxSuccess: function(data) {
			if (!User.userId && data.returnValues.guestDialog) {
				UiDialog.openStatic('jsDialogGuestComment', data.returnValues.guestDialog, {
					closable: false,
					onClose: function() {
						if (ControllerCaptcha.has('commentAdd')) {
							ControllerCaptcha.delete('commentAdd');
						}
					},
					title: Language.get('wcf.global.confirmation.title')
				});
				
				var dialog = UiDialog.getDialog('jsDialogGuestComment');
				elBySel('input[type=submit]', dialog.content).addEventListener(WCF_CLICK_EVENT, this._submitGuestDialog.bind(this));
				elBySel('button[data-type="cancel"]', dialog.content).addEventListener(WCF_CLICK_EVENT, this._cancelGuestDialog.bind(this));
				elBySel('input[type=text]', dialog.content).addEventListener('keypress', this._submitGuestDialog.bind(this));
			}
			else {
				var scrollTarget = this._insertMessage(data);
				
				if (!User.userId) {
					UiDialog.close('jsDialogGuestComment');
				}
				
				this._reset();
				
				this._hideLoadingOverlay();
				
				window.setTimeout((function () {
					UiScroll.element(scrollTarget);
				}).bind(this), 100);
			}
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
					actionName: 'addComment',
					className: 'wcf\\data\\comment\\CommentAction'
				},
				silent: true
			};
		},
		
		/**
		 * Cancels the guest dialog and restores the comment editor.
		 */
		_cancelGuestDialog: function() {
			UiDialog.close('jsDialogGuestComment');
			
			this._hideLoadingOverlay();
		}
	};
	
	return UiCommentAdd;
});

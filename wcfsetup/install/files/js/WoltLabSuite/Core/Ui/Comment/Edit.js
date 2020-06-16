/**
 * Provides editing support for comments.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Ui/Comment/Edit
 */
define(
	[
		'Ajax',         'Core',            'Dictionary',          'Environment',
		'EventHandler', 'Language',        'List',                'Dom/ChangeListener', 'Dom/Traverse',
		'Dom/Util',     'Ui/Notification', 'Ui/ReusableDropdown', 'WoltLabSuite/Core/Ui/Scroll'
	],
	function(
		Ajax,            Core,              Dictionary,            Environment,
		EventHandler,    Language,          List,                  DomChangeListener,    DomTraverse,
		DomUtil,         UiNotification,    UiReusableDropdown,    UiScroll
	)
{
	"use strict";
	
	if (!COMPILER_TARGET_DEFAULT) {
		var Fake = function() {};
		Fake.prototype = {
			init: function() {},
			rebuild: function() {},
			_click: function() {},
			_prepare: function() {},
			_showEditor: function() {},
			_restoreMessage: function() {},
			_save: function() {},
			_validate: function() {},
			throwError: function() {},
			_showMessage: function() {},
			_hideEditor: function() {},
			_restoreEditor: function() {},
			_destroyEditor: function() {},
			_getEditorId: function() {},
			_getObjectId: function() {},
			_ajaxFailure: function() {},
			_ajaxSuccess: function() {},
			_ajaxSetup: function() {}
		};
		return Fake;
	}
	
	/**
	 * @constructor
	 */
	function UiCommentEdit(container) { this.init(container); }
	UiCommentEdit.prototype = {
		/**
		 * Initializes the comment edit manager.
		 * 
		 * @param	{Element}       container       container element
		 */
		init: function(container) {
			this._activeElement = null;
			this._callbackClick = null;
			this._comments = new List();
			this._container = container;
			this._editorContainer = null;
			
			this.rebuild();
			
			DomChangeListener.add('Ui/Comment/Edit_' + DomUtil.identify(this._container), this.rebuild.bind(this));
		},
		
		/**
		 * Initializes each applicable message, should be called whenever new
		 * messages are being displayed.
		 */
		rebuild: function() {
			elBySelAll('.comment', this._container, (function (comment) {
				if (this._comments.has(comment)) {
					return;
				}
				
				if (elDataBool(comment, 'can-edit')) {
					var button = elBySel('.jsCommentEditButton', comment);
					if (button !== null) {
						if (this._callbackClick === null) {
							this._callbackClick = this._click.bind(this);
						}
						
						button.addEventListener(WCF_CLICK_EVENT, this._callbackClick);
					}
				}
				
				this._comments.add(comment);
			}).bind(this));
		},
		
		/**
		 * Handles clicks on the edit button.
		 * 
		 * @param	{?Event}	event		event object
		 * @protected
		 */
		_click: function(event) {
			event.preventDefault();
			
			if (this._activeElement === null) {
				this._activeElement = event.currentTarget.closest('.comment');
				
				this._prepare();
				
				Ajax.api(this, {
					actionName: 'beginEdit',
					objectIDs: [this._getObjectId(this._activeElement)]
				});
			}
			else {
				UiNotification.show('wcf.message.error.editorAlreadyInUse', null, 'warning');
			}
		},
		
		/**
		 * Prepares the message for editor display.
		 * 
		 * @protected
		 */
		_prepare: function() {
			this._editorContainer = elCreate('div');
			this._editorContainer.className = 'commentEditorContainer';
			this._editorContainer.innerHTML = '<span class="icon icon48 fa-spinner"></span>';
			
			var content = elBySel('.commentContentContainer', this._activeElement);
			content.insertBefore(this._editorContainer, content.firstChild);
		},
		
		/**
		 * Shows the message editor.
		 * 
		 * @param	{Object}	data		ajax response data
		 * @protected
		 */
		_showEditor: function(data) {
			var id = this._getEditorId();
			
			var icon = elBySel('.icon', this._editorContainer);
			elRemove(icon);
			
			var editor = elCreate('div');
			editor.className = 'editorContainer';
			//noinspection JSUnresolvedVariable
			DomUtil.setInnerHtml(editor, data.returnValues.template);
			this._editorContainer.appendChild(editor);
			
			// bind buttons
			var formSubmit = elBySel('.formSubmit', editor);
			
			var buttonSave = elBySel('button[data-type="save"]', formSubmit);
			buttonSave.addEventListener(WCF_CLICK_EVENT, this._save.bind(this));
			
			var buttonCancel = elBySel('button[data-type="cancel"]', formSubmit);
			buttonCancel.addEventListener(WCF_CLICK_EVENT, this._restoreMessage.bind(this));
			
			EventHandler.add('com.woltlab.wcf.redactor', 'submitEditor_' + id, (function(data) {
				data.cancel = true;
				
				this._save();
			}).bind(this));
			
			var editorElement = elById(id);
			if (Environment.editor() === 'redactor') {
				window.setTimeout((function() {
					UiScroll.element(this._activeElement);
				}).bind(this), 250);
			}
			else {
				editorElement.focus();
			}
		},
		
		/**
		 * Restores the message view.
		 * 
		 * @protected
		 */
		_restoreMessage: function() {
			this._destroyEditor();
			
			elRemove(this._editorContainer);
			
			this._activeElement = null;
		},
		
		/**
		 * Saves the editor message.
		 * 
		 * @protected
		 */
		_save: function() {
			var parameters = {
				data: {
					message: ''
				}
			};
			
			var id = this._getEditorId();
			
			EventHandler.fire('com.woltlab.wcf.redactor2', 'getText_' + id, parameters.data);
			
			if (!this._validate(parameters)) {
				// validation failed
				return;
			}
			
			EventHandler.fire('com.woltlab.wcf.redactor2', 'submit_' + id, parameters);
			
			Ajax.api(this, {
				actionName: 'save',
				objectIDs: [this._getObjectId(this._activeElement)],
				parameters: parameters
			});
			
			this._hideEditor();
		},
		
		/**
		 * Validates the message and invokes listeners to perform additional validation.
		 *
		 * @param       {Object}        parameters      request parameters
		 * @return      {boolean}       validation result
		 * @protected
		 */
		_validate: function(parameters) {
			// remove all existing error elements
			elBySelAll('.innerError', this._activeElement, elRemove);
			
			// check if editor contains actual content
			var editorElement = elById(this._getEditorId());
			if (window.jQuery(editorElement).data('redactor').utils.isEmpty()) {
				this.throwError(editorElement, Language.get('wcf.global.form.error.empty'));
				return false;
			}
			
			var data = {
				api: this,
				parameters: parameters,
				valid: true
			};
			
			EventHandler.fire('com.woltlab.wcf.redactor2', 'validate_' + this._getEditorId(), data);
			
			return (data.valid !== false);
		},
		
		/**
		 * Throws an error by adding an inline error to target element.
		 *
		 * @param       {Element}       element         erroneous element
		 * @param       {string}        message         error message
		 */
		throwError: function(element, message) {
			elInnerError(element, message);
		},
		
		/**
		 * Shows the update message.
		 * 
		 * @param	{Object}	data		ajax response data
		 * @protected
		 */
		_showMessage: function(data) {
			// set new content
			//noinspection JSCheckFunctionSignatures
			DomUtil.setInnerHtml(elBySel('.commentContent .userMessage', this._editorContainer.parentNode), data.returnValues.message);
			
			this._restoreMessage();
			
			UiNotification.show();
		},
		
		/**
		 * Hides the editor from view.
		 * 
		 * @protected
		 */
		_hideEditor: function() {
			elHide(elBySel('.editorContainer', this._editorContainer));
			
			var icon = elCreate('span');
			icon.className = 'icon icon48 fa-spinner';
			this._editorContainer.appendChild(icon);
		},
		
		/**
		 * Restores the previously hidden editor.
		 * 
		 * @protected
		 */
		_restoreEditor: function() {
			var icon = elBySel('.fa-spinner', this._editorContainer);
			elRemove(icon);
			
			var editorContainer = elBySel('.editorContainer', this._editorContainer);
			if (editorContainer !== null) elShow(editorContainer);
		},
		
		/**
		 * Destroys the editor instance.
		 * 
		 * @protected
		 */
		_destroyEditor: function() {
			EventHandler.fire('com.woltlab.wcf.redactor2', 'autosaveDestroy_' + this._getEditorId());
			EventHandler.fire('com.woltlab.wcf.redactor2', 'destroy_' + this._getEditorId());
		},
		
		/**
		 * Returns the unique editor id.
		 * 
		 * @return	{string}	editor id
		 * @protected
		 */
		_getEditorId: function() {
			return 'commentEditor' + this._getObjectId(this._activeElement);
		},
		
		/**
		 * Returns the element's `data-object-id` value.
		 * 
		 * @param	{Element}	element         target element
		 * @return	{int}
		 * @protected
		 */
		_getObjectId: function(element) {
			return ~~elData(element, 'object-id');
		},
		
		_ajaxFailure: function(data) {
			var editor = elBySel('.redactor-layer', this._editorContainer);
			
			// handle errors occurring on editor load
			if (editor === null) {
				this._restoreMessage();
				
				return true;
			}
			
			this._restoreEditor();
			
			//noinspection JSUnresolvedVariable
			if (!data || data.returnValues === undefined || data.returnValues.errorType === undefined) {
				return true;
			}
			
			//noinspection JSUnresolvedVariable
			elInnerError(editor, data.returnValues.errorType);
			
			return false;
		},
		
		_ajaxSuccess: function(data) {
			switch (data.actionName) {
				case 'beginEdit':
					this._showEditor(data);
					break;
					
				case 'save':
					this._showMessage(data);
					break;
			}
		},
		
		_ajaxSetup: function() {
			var objectTypeId = ~~elData(this._container, 'object-type-id');
			
			return {
				data: {
					className: 'wcf\\data\\comment\\CommentAction',
					parameters: {
						data: {
							objectTypeID: objectTypeId
						}
					}
				},
				silent: true
			};
		}
	};
	
	return UiCommentEdit;
});

/**
 * Provides editing support for comment responses.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Ui/Comment/Response/Edit
 */
define(
	[
		'Ajax',         'Core',            'Dictionary',          'Environment',
		'EventHandler', 'Language',        'List',                'Dom/ChangeListener', 'Dom/Traverse',
		'Dom/Util',     'Ui/Notification', 'Ui/ReusableDropdown', 'WoltLabSuite/Core/Ui/Scroll', 'WoltLabSuite/Core/Ui/Comment/Edit'
	],
	function(
		Ajax,            Core,              Dictionary,            Environment,
		EventHandler,    Language,          List,                  DomChangeListener,    DomTraverse,
		DomUtil,         UiNotification,    UiReusableDropdown,    UiScroll, UiCommentEdit
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
	function UiCommentResponseEdit(container) { this.init(container); }
	Core.inherit(UiCommentResponseEdit, UiCommentEdit, {
		/**
		 * Initializes the comment edit manager.
		 * 
		 * @param	{Element}       container       container element
		 */
		init: function(container) {
			this._activeElement = null;
			this._callbackClick = null;
			this._container = container;
			this._editorContainer = null;
			this._responses = new List();
			
			this.rebuild();
			
			DomChangeListener.add('Ui/Comment/Response/Edit_' + DomUtil.identify(this._container), this.rebuild.bind(this));
		},
		
		/**
		 * Initializes each applicable message, should be called whenever new
		 * messages are being displayed.
		 */
		rebuild: function() {
			elBySelAll('.commentResponse', this._container, (function (response) {
				if (this._responses.has(response)) {
					return;
				}
				
				if (elDataBool(response, 'can-edit')) {
					var button = elBySel('.jsCommentResponseEditButton', response);
					if (button !== null) {
						if (this._callbackClick === null) {
							this._callbackClick = this._click.bind(this);
						}
						
						button.addEventListener('click', this._callbackClick);
					}
				}
				
				this._responses.add(response);
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
				this._activeElement = event.currentTarget.closest('.commentResponse');
				
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
			
			var content = elBySel('.commentResponseContent', this._activeElement);
			content.insertBefore(this._editorContainer, content.firstChild);
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
			DomUtil.setInnerHtml(elBySel('.commentResponseContent .userMessage', this._editorContainer.parentNode), data.returnValues.message);
			
			this._restoreMessage();
			
			UiNotification.show();
		},
		
		/**
		 * Returns the unique editor id.
		 * 
		 * @return	{string}	editor id
		 * @protected
		 */
		_getEditorId: function() {
			return 'commentResponseEditor' + this._getObjectId(this._activeElement);
		},
		
		_ajaxSetup: function() {
			var objectTypeId = ~~elData(this._container, 'object-type-id');
			
			return {
				data: {
					className: 'wcf\\data\\comment\\response\\CommentResponseAction',
					parameters: {
						data: {
							objectTypeID: objectTypeId
						}
					}
				},
				silent: true
			};
		}
	});
	
	return UiCommentResponseEdit;
});

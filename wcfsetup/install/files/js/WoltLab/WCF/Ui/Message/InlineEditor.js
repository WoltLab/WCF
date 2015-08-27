/**
 * Flexible message inline editor.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Ui/Message/InlineEditor
 */
define(
	[
		'Ajax',         'Core',            'Dictionary',        'Environment',
		'EventHandler', 'Language',        'ObjectMap',         'Dom/Traverse',
		'Dom/Util',     'Ui/Notification', 'Ui/SimpleDropdown'
	],
	function(
		Ajax,            Core,              Dictionary,          Environment,
		EventHandler,    Language,          ObjectMap,           DomTraverse,
		DomUtil,         UiNotification,    UiSimpleDropdown
	)
{
	"use strict";
	
	var _activeElement = null;
	var _dropdownMenus = new Dictionary();
	var _elements = new ObjectMap();
	var _options = {};
	
	/**
	 * @exports	WoltLab/WCF/Ui/Message/InlineEditor
	 */
	var UiMessageInlineEditor = {
		/**
		 * Initializes the message inline editor.
		 * 
		 * @param	{object<mixed>}		options		list of configuration options
		 */
		init: function(options) {
			_options = Core.extend({
				canEditInline: false,
				extendedForm: true,
				
				className: '',
				containerId: 0,
				editorPrefix: 'messageEditor',
				
				messageSelector: '.jsMessage',
				
				callbackDropdownInit: null,
				callbackDropdownOpen: null
			}, options);
			
			this._initElements();
		},
		
		/**
		 * Initializes each applicable message.
		 */
		_initElements: function() {
			var button, canEdit, element, elements = elBySelAll(_options.messageSelector);
			for (var i = 0, length = elements.length; i < length; i++) {
				element = elements[i];
				if (_elements.has(element)) {
					continue;
				}
				
				button = elBySel('.jsMessageEditButton', element);
				if (button !== null) {
					canEdit = elAttrBool(element, 'data-can-edit');
					
					if (_options.canEditInline) {
						button.addEventListener('click', this._clickDropdown.bind(this, element));
						
						if (canEdit) {
							button.addEventListener('dblclick', this._click.bind(this, element));
						}
					}
					else if (canEdit) {
						button.addEventListener('click', this._click.bind(this, element));
					}
				}
				
				
				var messageBody = elBySel('.messageBody', element);
				var messageFooter = elBySel('.messageFooter', element);
				
				_elements.set(element, {
					messageBody: messageBody,
					messageBodyEditor: null,
					messageFooter: messageFooter,
					messageFooterButtons: elBySel('.messageFooterButtons', messageFooter),
					messageText: elBySel('.messageText', messageBody)
				});
			}
		},
		
		/**
		 * Handles clicks on the edit button or the edit dropdown item.
		 * 
		 * @param	{Element}	element		message element
		 * @param	{?object}	event		event object
		 */
		_click: function(element, event) {
			if (event !== null) event.preventDefault();
			
			if (_activeElement === null) {
				_activeElement = element;
				
				this._prepare();
				
				Ajax.api(this, {
					actionName: 'beginEdit',
					parameters: {
						containerID: _options.containerId,
						objectID: this._getObjectId(element)
					}
				});
			}
			else {
				UiNotification.show('wcf.message.error.editorAlreadyInUse', null, 'warning');
			}
		},
		
		/**
		 * Creates and opens the dropdown on first usage.
		 * 
		 * @param	{Element}	element		message element
		 * @param	{object}	event		event object
		 */
		_clickDropdown: function(element, event) {
			event.preventDefault();
			
			var button = event.currentTarget;
			if (button.classList.contains('dropdownToggle')) {
				return;
			}
			
			// build dropdown
			button.classList.add('dropdownToggle');
			button.parentNode.classList.add('dropdown');
			
			var dropdownMenu = elCreate('ul');
			dropdownMenu.className = 'dropdownMenu';
			
			var items = _options.callbackDropdownInit(element, dropdownMenu);
			if (items !== null) this._dropdownBuild(element, dropdownMenu, items);
			
			DomUtil.insertAfter(dropdownMenu, button);
			
			_dropdownMenus.set(this._getObjectId(element), dropdownMenu);
			
			UiSimpleDropdown.init(button, true);
			
			var id = DomUtil.identify(button.parentNode);
			UiSimpleDropdown.registerCallback(id, this._dropdownToggle.bind(this, element));
		},
		
		/**
		 * Creates the dropdown menu on first usage.
		 * 
		 * @param	{Element}		element		message element
		 * @param	{Element}		dropdownMenu	dropdown menu
		 * @param	{array<object>}		items		list of dropdown items
		 */
		_dropdownBuild: function(element, dropdownMenu, items) {
			var item, label, listItem;
			var callbackClick = this._clickDropdownItem.bind(this, element);
			
			for (var i = 0, length = items.length; i < length; i++) {
				item = items[i];
				listItem = elCreate('li');
				
				if (item.special === 'divider') {
					listItem.className = 'dropdownDivider';
				}
				else {
					elData(listItem, 'action', item.action);
					label = elCreate('span');
					label.textContent = Language.get(item.label);
					listItem.appendChild(label);
					
					if (item.special === 'edit') {
						listItem.addEventListener('click', this._click.bind(this, element));
					}
					else {
						listItem.addEventListener('click', callbackClick);
					}
					
					if (item.visible === false) {
						elHide(listItem);
					}
				}
				
				dropdownMenu.appendChild(listItem);
			}
		},
		
		/**
		 * Callback for dropdown toggle.
		 * 
		 * @param	{Element}	element		message element
		 * @param	{integer}	containerId	container id
		 * @param	{string}	action		toggle action, either 'open' or 'close'
		 */
		_dropdownToggle: function(element, containerId, action) {
			_elements.get(element).messageFooterButtons.classList[(action === 'open' ? 'add' : 'remove')]('forceVisible');
			
			if (action === 'open' && typeof _options.callbackDropdownOpen === 'function') {
				_options.callbackDropdownOpen(element, this._getObjectId(element));
			}
		},
		
		/**
		 * Handles clicks on a dropdown item.
		 * 
		 * @param	{Element}	element		message element
		 * @param	{object}	event		event object
		 */
		_clickDropdownItem: function(element, event) {
			event.preventDefault();
			
			_options.callbackDropdownSelect(element, this._getObjectId(element), elAttr(event.currentTarget, 'data-class-name'));
		},
		
		/**
		 * Prepares the message for editor display.
		 */
		_prepare: function() {
			var data = _elements.get(_activeElement);
			
			var messageBodyEditor = elCreate('div');
			messageBodyEditor.className = 'messageBody editor';
			data.messageBodyEditor = messageBodyEditor;
			
			var icon = elCreate('span');
			icon.className = 'icon icon48 fa-spinner';
			messageBodyEditor.appendChild(icon);
			
			DomUtil.insertAfter(messageBodyEditor, data.messageBody);
			
			elHide(data.messageBody);
		},
		
		/**
		 * Shows the message editor.
		 * 
		 * @param	{object}	data		ajax response data
		 */
		_showEditor: function(data) {
			var id = this._getEditorId();
			var elementData = _elements.get(_activeElement);
			
			_activeElement.classList.add('jsInvalidQuoteTarget');
			var icon = DomTraverse.childByClass(elementData.messageBodyEditor, 'icon');
			icon.parentNode.removeChild(icon);
			
			var messageBody = elementData.messageBodyEditor;
			var editor = elCreate('div');
			editor.className = 'editorContainer';
			DomUtil.setInnerHtml(editor, data.returnValues.template);
			messageBody.appendChild(editor);
			
			// bind buttons
			var formSubmit = elBySel('.formSubmit', editor);
			
			var buttonSave = elBySel('button[data-type="save"]', formSubmit);
			buttonSave.addEventListener('click', this._save.bind(this));
			
			if (_options.extendedForm) {
				var buttonExtended = elBySel('button[data-type="extended"]', formSubmit);
				buttonExtended.addEventListener('click', this._prepareExtended.bind(this));
			}
			
			var buttonCancel = elBySel('button[data-type="cancel"]', formSubmit);
			buttonCancel.addEventListener('click', this._restoreMessage.bind(this));
			
			EventHandler.add('com.woltlab.wcf.redactor', 'submitEditor_' + id, (function(data) {
				data.cancel = true;
				
				this._save();
			}).bind(this));
			
			// hide message options
			elHide(elementData.messageFooter);
			
			var editorElement = elById(id);
			if (Environment.editor() === 'redactor') {
				window.setTimeout((function() {
					// TODO: quote manager
					if (this._quoteManager) {
						this._quoteManager.setAlternativeEditor($element);
					}
					
					// TODO
					new WCF.Effect.Scroll().scrollTo(_activeElement, true);
				}).bind(this), 250);
			}
			else {
				editorElement.focus();
			}
		},
		
		/**
		 * Restores the message view.
		 */
		_restoreMessage: function() {
			var elementData = _elements.get(_activeElement);
			
			this._destroyEditor();
			
			elRemove(elementData.messageBodyEditor);
			elementData.messageBodyEditor = null;
			
			elShow(elementData.messageBody);
			elShow(elementData.messageFooter);
			_activeElement.classList.remove('jsInvalidQuoteTarget');
			
			_activeElement = null;
			
			// @TODO
			if (this._quoteManager) {
				this._quoteManager.clearAlternativeEditor();
			}
		},
		
		/**
		 * Saves the editor message.
		 */
		_save: function() {
			var parameters = {
				containerID: _options.containerId,
				data: {
					message: ''
				},
				objectID: this._getObjectId(),
				removeQuoteIDs: [] // @TODO
			};
			
			var id = this._getEditorId();
			EventHandler.fire('com.woltlab.wcf.redactor', 'getText_' + id, parameters.data);
			EventHandler.fire('com.woltlab.wcf.messageOptionsInline', 'submit_' + id, parameters);
			
			Ajax.api(this, {
				actionName: 'save',
				parameters: parameters
			});
			
			this._hideEditor();
		},
		
		/**
		 * Shows the update message.
		 * 
		 * @param	{object}	data		ajax response data
		 */
		_showMessage: function(data) {
			var elementData = _elements.get(_activeElement);
			var attachmentLists = elBySelAll('.attachmentThumbnailList, .attachmentFileList', elementData.messageBody);
			
			// set new content
			DomUtil.setInnerHtml(elementData.messageBody, data.returnValues.message);
			
			// handle attachment list
			if (typeof data.returnValues.attachmentList === 'string') {
				for (var i = 0, length = attachmentLists.length; i < length; i++) {
					elRemove(attachmentLists[i]);
				}
				
				var element = elCreate('div');
				DomUtil.setInnerHtml(element, data.returnValues.attachmentList);
				
				while (element.childNodes.length) {
					elementData.messageBody.appendChild(element.childNodes[0]);
				}
			}
			
			this._restoreMessage();
			
			this._updateHistory(this._getHash(this._getObjectId()));
			
			UiNotification.show();
			
			// @TODO
			return;
			
			if (this._quoteManager) {
				this._quoteManager.clearAlternativeEditor();
				this._quoteManager.countQuotes();
			}
		},
		
		/**
		 * Initiates the jump to the extended edit form.
		 */
		_prepareExtended: function() {
			var data = {
				actionName: 'jumpToExtended',
				parameters: {
					containerID: _options.containerId,
					message: '',
					messageID: this._getObjectId()
				}
			};
			
			var id = this._getEditorId();
			EventHandler.fire('com.woltlab.wcf.redactor', 'getText_' + id, data.parameters);
			
			Ajax.api(this, data);
		},
		
		/**
		 * Hides the editor from view.
		 */
		_hideEditor: function() {
			var elementData = _elements.get(_activeElement);
			elHide(DomTraverse.childByClass(elementData.messageBodyEditor, 'editorContainer'));
			
			var icon = elCreate('span');
			icon.className = 'icon icon48 fa-spinner';
			elementData.messageBodyEditor.appendChild(icon);
		},
		
		/**
		 * Restores the previously hidden editor.
		 */
		_restoreEditor: function() {
			var elementData = _elements.get(_activeElement);
			var icon = elBySel('.fa-spinner', elementData.messageBodyEditor);
			elRemove(icon);
			console.debug(icon);
			elShow(DomTraverse.childByClass(elementData.messageBodyEditor, 'editorContainer'));
		},
		
		/**
		 * Destroys the editor instance.
		 */
		_destroyEditor: function() {
			EventHandler.fire('com.woltlab.wcf.redactor', 'destroy_' + this._getEditorId());
		},
		
		/**
		 * Returns the hash added to the url after successfully editing a message.
		 * 
		 * @param	{integer}	objectId	message object id
		 * @return	string
		 */
		_getHash: function(objectId) {
			return '#message' + objectId;
		},
		
		/**
		 * Updates the history to avoid old content when going back in the browser
		 * history.
		 * 
		 * @param	hash
		 */
		_updateHistory: function(hash) {
			window.location.hash = hash;
		},
		
		/**
		 * Returns the unique editor id.
		 * 
		 * @return	{string}	editor id
		 */
		_getEditorId: function() {
			return _options.editorPrefix + this._getObjectId();
		},
		
		/**
		 * Returns the element's `data-object-id` value.
		 * 
		 * @param	{Element=}	element		target element, `_activeElement` if empty
		 * @return	{integer}
		 */
		_getObjectId: function(element) {
			return ~~elAttr(element || _activeElement, 'data-object-id');
		},
		
		_ajaxFailure: function(data) {
			this._restoreEditor();
			
			if (!data || data.returnValues === undefined || data.returnValues.errorType === undefined) {
				return true;
			}
			
			var elementData = _elements.get(_activeElement);
			var innerError = elBySel('.innerError', elementData.messageBodyEditor);
			if (innerError === null) {
				innerError = elCreate('small');
				innerError.className = 'innerError';
				
				var editor = elBySel('.redactor-editor', elementData.messageBodyEditor);
				DomUtil.insertAfter(innerError, editor);
			}
			
			
			innerError.textContent = data.returnValues.errorType;
			
			return false;
		},
		
		_ajaxSuccess: function(data) {
			switch (data.actionName) {
				case 'beginEdit':
					this._showEditor(data);
					break;
					
				case 'jumpToExtended':
					window.location = data.returnValues.url;
					break;
				
				case 'save':
					this._showMessage(data);
					break;
			}
		},
		
		_ajaxSetup: function() {
			return {
				data: {
					className: _options.className,
					interfaceName: 'wcf\\data\\IMessageInlineEditorAction'
				}
			};
		},
		
		/** @deprecated	2.2 - used only for backward compatibility with `WCF.Message.InlineEditor` */
		legacyGetDropdownMenus: function() { return _dropdownMenus; },
		
		/** @deprecated	2.2 - used only for backward compatibility with `WCF.Message.InlineEditor` */
		legacyGetElements: function() { return _elements; },
		
		/** @deprecated	2.2 - used only for backward compatibility with `WCF.Message.InlineEditor` */
		legacyEdit: function(containerId) {
			this._click(elById(containerId), null);
		}
	};
	
	return UiMessageInlineEditor;
});

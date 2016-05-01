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
		'Dom/Util',     'Ui/Notification', 'Ui/ReusableDropdown'
	],
	function(
		Ajax,            Core,              Dictionary,          Environment,
		EventHandler,    Language,          ObjectMap,           DomTraverse,
		DomUtil,         UiNotification,    UiReusableDropdown
	)
{
	"use strict";
	
	/**
	 * @constructor
	 */
	function UiMessageInlineEditor(options) { this.init(options); }
	UiMessageInlineEditor.prototype = {
		/**
		 * Initializes the message inline editor.
		 * 
		 * @param	{Object<string, *>}		options		list of configuration options
		 */
		init: function(options) {
			this._activeDropdownElement = null;
			this._activeElement = null;
			this._dropdownMenu = null;
			this._elements = new ObjectMap();
			this._options = Core.extend({
				canEditInline: false,
				extendedForm: true,
				
				className: '',
				containerId: 0,
				dropdownIdentifier: '',
				editorPrefix: 'messageEditor',
				
				messageSelector: '.jsMessage'
			}, options);
			
			this._initElements();
		},
		
		/**
		 * Initializes each applicable message.
		 * 
		 * @protected
		 */
		_initElements: function() {
			var button, canEdit, element, elements = elBySelAll(this._options.messageSelector);
			
			for (var i = 0, length = elements.length; i < length; i++) {
				element = elements[i];
				if (this._elements.has(element)) {
					continue;
				}
				
				button = elBySel('.jsMessageEditButton', element);
				if (button !== null) {
					canEdit = elDataBool(element, 'can-edit');
					
					if (this._options.canEditInline) {
						button.addEventListener('click', this._clickDropdown.bind(this, element));
						button.classList.add('jsDropdownEnabled');
						
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
				
				this._elements.set(element, {
					button: button,
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
		 * @param	{?Event}	event		event object
		 * @protected
		 */
		_click: function(element, event) {
			if (element === null) element = this._activeDropdownElement;
			if (event) event.preventDefault();
			
			if (this._activeElement === null) {
				this._activeElement = element;
				
				this._prepare();
				
				Ajax.api(this, {
					actionName: 'beginEdit',
					parameters: {
						containerID: this._options.containerId,
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
		 * @param	{Object}	event		event object
		 * @protected
		 */
		_clickDropdown: function(element, event) {
			event.preventDefault();
			
			var button = event.currentTarget;
			if (button.classList.contains('dropdownToggle')) {
				return;
			}
			
			button.classList.add('dropdownToggle');
			button.parentNode.classList.add('dropdown');
			(function(button, element) {
				button.addEventListener('click', (function(event) {
					event.preventDefault();
					event.stopPropagation();
					
					this._activeDropdownElement = element;
					UiReusableDropdown.toggleDropdown(this._options.dropdownIdentifier, button);
				}).bind(this));
			}).bind(this)(button, element);
			
			// build dropdown
			if (this._dropdownMenu === null) {
				this._dropdownMenu = elCreate('ul');
				this._dropdownMenu.className = 'dropdownMenu';
				
				var items = this._dropdownGetItems();
				
				EventHandler.fire('com.woltlab.wcf.inlineEditor', 'dropdownInit_' + this._options.dropdownIdentifier, {
					items: items
				});
				
				this._dropdownBuild(items);
				
				UiReusableDropdown.init(this._options.dropdownIdentifier, this._dropdownMenu);
				UiReusableDropdown.registerCallback(this._options.dropdownIdentifier, this._dropdownToggle.bind(this));
			}
			
			setTimeout(function() {
				Core.triggerEvent(button, 'click');
			}, 10);
		},
		
		/**
		 * Creates the dropdown menu on first usage.
		 * 
		 * @param	{Object}        items   list of dropdown items
		 * @protected
		 */
		_dropdownBuild: function(items) {
			var item, label, listItem;
			var callbackClick = this._clickDropdownItem.bind(this);
			
			for (var i = 0, length = items.length; i < length; i++) {
				item = items[i];
				listItem = elCreate('li');
				elData(listItem, 'item', item.item);
				
				if (item.item === 'divider') {
					listItem.className = 'dropdownDivider';
				}
				else {
					label = elCreate('span');
					label.textContent = Language.get(item.label);
					listItem.appendChild(label);
					
					if (item.item === 'editItem') {
						listItem.addEventListener('click', this._click.bind(this, null));
					}
					else {
						listItem.addEventListener('click', callbackClick);
					}
				}
				
				this._dropdownMenu.appendChild(listItem);
			}
		},
		
		/**
		 * Callback for dropdown toggle.
		 * 
		 * @param	{int}           containerId	container id
		 * @param	{string}	action		toggle action, either 'open' or 'close'
		 * @protected
		 */
		_dropdownToggle: function(containerId, action) {
			var elementData = this._elements.get(this._activeDropdownElement);
			elementData.button.parentNode.classList[(action === 'open' ? 'add' : 'remove')]('dropdownOpen');
			elementData.messageFooterButtons.classList[(action === 'open' ? 'add' : 'remove')]('forceVisible');
			
			if (action === 'open') {
				var visibility = this._dropdownOpen();
				
				EventHandler.fire('com.woltlab.wcf.inlineEditor', 'dropdownOpen_' + this._options.dropdownIdentifier, {
					element: this._activeDropdownElement,
					visibility: visibility
				});
				
				var item, listItem, visiblePredecessor = false;
				for (var i = 0; i < this._dropdownMenu.childElementCount; i++) {
					listItem = this._dropdownMenu.children[i];
					item = elData(listItem, 'item');
					
					if (item === 'divider') {
						if (visiblePredecessor) {
							elShow(listItem);
							
							visiblePredecessor = false;
						}
						else {
							elHide(listItem);
						}
					}
					else {
						if (objOwns(visibility, item) && visibility[item] === false) {
							elHide(listItem);
						}
						else {
							elShow(listItem);
							
							visiblePredecessor = true;
						}
					}
				}
			}
		},
		
		/**
		 * Returns the list of dropdown items for this type.
		 * 
		 * @return      {Array<Object>}         list of objects containing the type name and label
		 * @protected
		 */
		_dropdownGetItems: function() {},
		
		/**
		 * Invoked once the dropdown for this type is shown, expects a list of type name and a boolean value
		 * to represent the visibility of each item. Items that do not appear in this list will be considered
		 * visible.
		 * 
		 * @return      {Object<string, boolean>}
		 * @protected
		 */
		_dropdownOpen: function() {},
		
		/**
		 * Invoked whenever the user selects an item from the dropdown menu, the selected item is passed as argument.
		 * 
		 * @param       {string}        item    selected dropdown item
		 * @protected
		 */
		_dropdownSelect: function(item) {},
		
		/**
		 * Handles clicks on a dropdown item.
		 * 
		 * @param	{Event}         event   event object
		 * @protected
		 */
		_clickDropdownItem: function(event) {
			event.preventDefault();
			
			this._dropdownSelect(elData(event.currentTarget, 'item'));
		},
		
		/**
		 * Prepares the message for editor display.
		 * 
		 * @protected
		 */
		_prepare: function() {
			var data = this._elements.get(this._activeElement);
			
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
		 * @param	{Object}	data		ajax response data
		 * @protected
		 */
		_showEditor: function(data) {
			var id = this._getEditorId();
			var elementData = this._elements.get(this._activeElement);
			
			this._activeElement.classList.add('jsInvalidQuoteTarget');
			var icon = DomTraverse.childByClass(elementData.messageBodyEditor, 'icon');
			elRemove(icon);
			
			var messageBody = elementData.messageBodyEditor;
			var editor = elCreate('div');
			editor.className = 'editorContainer';
			DomUtil.setInnerHtml(editor, data.returnValues.template);
			messageBody.appendChild(editor);
			
			// bind buttons
			var formSubmit = elBySel('.formSubmit', editor);
			
			var buttonSave = elBySel('button[data-type="save"]', formSubmit);
			buttonSave.addEventListener('click', this._save.bind(this));
			
			if (this._options.extendedForm) {
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
					new WCF.Effect.Scroll().scrollTo(this._activeElement, true);
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
			var elementData = this._elements.get(this._activeElement);
			
			this._destroyEditor();
			
			elRemove(elementData.messageBodyEditor);
			elementData.messageBodyEditor = null;
			
			elShow(elementData.messageBody);
			elShow(elementData.messageFooter);
			this._activeElement.classList.remove('jsInvalidQuoteTarget');
			
			this._activeElement = null;
			
			// @TODO
			if (this._quoteManager) {
				this._quoteManager.clearAlternativeEditor();
			}
		},
		
		/**
		 * Saves the editor message.
		 * 
		 * @protected
		 */
		_save: function() {
			var parameters = {
				containerID: this._options.containerId,
				data: {
					message: ''
				},
				objectID: this._getObjectId(this._activeElement),
				removeQuoteIDs: [] // @TODO
			};
			
			var id = this._getEditorId();
			
			EventHandler.fire('com.woltlab.wcf.redactor2', 'getText_' + id, parameters.data);
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
		 * @param	{Object}	data		ajax response data
		 * @protected
		 */
		_showMessage: function(data) {
			var elementData = this._elements.get(this._activeElement);
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
			
			this._updateHistory(this._getHash(this._getObjectId(this._activeElement)));
			
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
		 * 
		 * @protected
		 */
		_prepareExtended: function() {
			var data = {
				actionName: 'jumpToExtended',
				parameters: {
					containerID: this._options.containerId,
					message: '',
					messageID: this._getObjectId(this._activeElement)
				}
			};
			
			var id = this._getEditorId();
			EventHandler.fire('com.woltlab.wcf.redactor', 'getText_' + id, data.parameters);
			
			Ajax.api(this, data);
		},
		
		/**
		 * Hides the editor from view.
		 * 
		 * @protected
		 */
		_hideEditor: function() {
			var elementData = this._elements.get(this._activeElement);
			elHide(DomTraverse.childByClass(elementData.messageBodyEditor, 'editorContainer'));
			
			var icon = elCreate('span');
			icon.className = 'icon icon48 fa-spinner';
			elementData.messageBodyEditor.appendChild(icon);
		},
		
		/**
		 * Restores the previously hidden editor.
		 * 
		 * @protected
		 */
		_restoreEditor: function() {
			var elementData = this._elements.get(this._activeElement);
			var icon = elBySel('.fa-spinner', elementData.messageBodyEditor);
			elRemove(icon);
			
			var editorContainer = DomTraverse.childByClass(elementData.messageBodyEditor, 'editorContainer');
			if (editorContainer !== null) elShow(editorContainer);
		},
		
		/**
		 * Destroys the editor instance.
		 * 
		 * @protected
		 */
		_destroyEditor: function() {
			EventHandler.fire('com.woltlab.wcf.redactor', 'destroy_' + this._getEditorId());
		},
		
		/**
		 * Returns the hash added to the url after successfully editing a message.
		 * 
		 * @param	{int}   objectId        message object id
		 * @return	string
		 * @protected
		 */
		_getHash: function(objectId) {
			return '#message' + objectId;
		},
		
		/**
		 * Updates the history to avoid old content when going back in the browser
		 * history.
		 * 
		 * @param	{string}        hash    location hash
		 * @protected
		 */
		_updateHistory: function(hash) {
			window.location.hash = hash;
		},
		
		/**
		 * Returns the unique editor id.
		 * 
		 * @return	{string}	editor id
		 * @protected
		 */
		_getEditorId: function() {
			return this._options.editorPrefix + this._getObjectId(this._activeElement);
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
			var elementData = this._elements.get(this._activeElement);
			var editor = elBySel('.redactor-editor', elementData.messageBodyEditor);
			
			// handle errors occuring on editor load
			if (editor === null) {
				this._restoreMessage();
				
				return true;
			}
			
			this._restoreEditor();
			
			if (!data || data.returnValues === undefined || data.returnValues.errorType === undefined) {
				return true;
			}
			
			var innerError = elBySel('.innerError', elementData.messageBodyEditor);
			if (innerError === null) {
				innerError = elCreate('small');
				innerError.className = 'innerError';
				
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
					className: this._options.className,
					interfaceName: 'wcf\\data\\IMessageInlineEditorAction'
				}
			};
		},
		
		/** @deprecated	2.2 - used only for backward compatibility with `WCF.Message.InlineEditor` */
		legacyGetDropdownMenus: function() { return this._dropdownMenus; },
		
		/** @deprecated	2.2 - used only for backward compatibility with `WCF.Message.InlineEditor` */
		legacyGetElements: function() { return this._elements; },
		
		/** @deprecated	2.2 - used only for backward compatibility with `WCF.Message.InlineEditor` */
		legacyEdit: function(containerId) {
			this._click(elById(containerId), null);
		}
	};
	
	return UiMessageInlineEditor;
});

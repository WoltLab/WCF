/**
 * Flexible message inline editor.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Ui/Message/InlineEditor
 */
define(
	[
		'Ajax',         'Core',            'Dictionary',          'Environment',
		'EventHandler', 'Language',        'ObjectMap',           'Dom/ChangeListener', 'Dom/Traverse',
		'Dom/Util',     'Ui/Notification', 'Ui/ReusableDropdown', 'WoltLabSuite/Core/Ui/Scroll'
	],
	function(
		Ajax,            Core,              Dictionary,            Environment,
		EventHandler,    Language,          ObjectMap,             DomChangeListener,    DomTraverse,
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
			_clickDropdown: function() {},
			_dropdownBuild: function() {},
			_dropdownToggle: function() {},
			_dropdownGetItems: function() {},
			_dropdownOpen: function() {},
			_dropdownSelect: function() {},
			_clickDropdownItem: function() {},
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
			_getHash: function() {},
			_updateHistory: function() {},
			_getEditorId: function() {},
			_getObjectId: function() {},
			_ajaxFailure: function() {},
			_ajaxSuccess: function() {},
			_ajaxSetup: function() {},
			legacyEdit: function() {}
		};
		return Fake;
	}
	
	/**
	 * @constructor
	 */
	function UiMessageInlineEditor(options) { this.init(options); }
	UiMessageInlineEditor.prototype = {
		/**
		 * Initializes the message inline editor.
		 * 
		 * @param	{Object}        options		list of configuration options
		 */
		init: function(options) {
			this._activeDropdownElement = null;
			this._activeElement = null;
			this._dropdownMenu = null;
			this._elements = new ObjectMap();
			this._options = Core.extend({
				canEditInline: false,
				
				className: '',
				containerId: 0,
				dropdownIdentifier: '',
				editorPrefix: 'messageEditor',
				
				messageSelector: '.jsMessage',
				
				quoteManager: null
			}, options);
			
			this.rebuild();
			
			DomChangeListener.add('Ui/Message/InlineEdit_' + this._options.className, this.rebuild.bind(this));
		},
		
		/**
		 * Initializes each applicable message, should be called whenever new
		 * messages are being displayed.
		 */
		rebuild: function() {
			var button, canEdit, element, elements = elBySelAll(this._options.messageSelector);
			
			for (var i = 0, length = elements.length; i < length; i++) {
				element = elements[i];
				if (this._elements.has(element)) {
					continue;
				}
				
				button = elBySel('.jsMessageEditButton', element);
				if (button !== null) {
					canEdit = elDataBool(element, 'can-edit');
					
					if (this._options.canEditInline || elDataBool(element, 'can-edit-inline')) {
						button.addEventListener(WCF_CLICK_EVENT, this._clickDropdown.bind(this, element));
						button.classList.add('jsDropdownEnabled');
						
						if (canEdit) {
							button.addEventListener('dblclick', this._click.bind(this, element));
						}
					}
					else if (canEdit) {
						button.addEventListener(WCF_CLICK_EVENT, this._click.bind(this, element));
					}
				}
				
				var messageBody = elBySel('.messageBody', element);
				var messageFooter = elBySel('.messageFooter', element);
				var messageHeader = elBySel('.messageHeader', element);
				
				this._elements.set(element, {
					button: button,
					messageBody: messageBody,
					messageBodyEditor: null,
					messageFooter: messageFooter,
					messageFooterButtons: elBySel('.messageFooterButtons', messageFooter),
					messageHeader: messageHeader,
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
				button.addEventListener(WCF_CLICK_EVENT, (function(event) {
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
				Core.triggerEvent(button, WCF_CLICK_EVENT);
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
						listItem.addEventListener(WCF_CLICK_EVENT, this._click.bind(this, null));
					}
					else {
						listItem.addEventListener(WCF_CLICK_EVENT, callbackClick);
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
							
							// check if previous item was a divider
							if (i > 0 && i + 1 === this._dropdownMenu.childElementCount) {
								if (elData(listItem.previousElementSibling, 'item') === 'divider') {
									elHide(listItem.previousElementSibling);
								}
							}
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
			
			//noinspection JSCheckFunctionSignatures
			var item = elData(event.currentTarget, 'item');
			var data = {
				cancel: false,
				element: this._activeDropdownElement,
				item: item
			};
			EventHandler.fire('com.woltlab.wcf.inlineEditor', 'dropdownItemClick_' + this._options.dropdownIdentifier, data);
			
			if (data.cancel === true) {
				event.preventDefault();
			}
			else {
				this._dropdownSelect(item);
			}
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
			//noinspection JSUnresolvedVariable
			DomUtil.setInnerHtml(editor, data.returnValues.template);
			messageBody.appendChild(editor);
			
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
			
			// hide message header and footer
			elHide(elementData.messageHeader);
			elHide(elementData.messageFooter);
			
			var editorElement = elById(id);
			if (Environment.editor() === 'redactor') {
				window.setTimeout((function() {
					if (this._options.quoteManager) {
						this._options.quoteManager.setAlternativeEditor(id);
					}
					
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
			var elementData = this._elements.get(this._activeElement);
			
			this._destroyEditor();
			
			elRemove(elementData.messageBodyEditor);
			elementData.messageBodyEditor = null;
			
			elShow(elementData.messageBody);
			elShow(elementData.messageFooter);
			elShow(elementData.messageHeader);
			this._activeElement.classList.remove('jsInvalidQuoteTarget');
			
			this._activeElement = null;
			
			if (this._options.quoteManager) {
				this._options.quoteManager.clearAlternativeEditor();
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
				removeQuoteIDs: (this._options.quoteManager) ? this._options.quoteManager.getQuotesMarkedForRemoval() : []
			};
			
			var id = this._getEditorId();
			
			// add any available settings
			var settingsContainer = elById('settings_' + id);
			if (settingsContainer) {
				elBySelAll('input, select, textarea', settingsContainer, function (element) {
					if (element.nodeName === 'INPUT' && (element.type === 'checkbox' || element.type === 'radio')) {
						if (!element.checked) {
							return;
						}
					}
					
					var name = element.name;
					if (parameters.hasOwnProperty(name)) {
						throw new Error("Variable overshadowing, key '" + name + "' is already present.");
					}
					
					parameters[name] = element.value.trim();
				});
			}
			
			EventHandler.fire('com.woltlab.wcf.redactor2', 'getText_' + id, parameters.data);
			
			var validateResult = this._validate(parameters);
			
			if (!(validateResult instanceof Promise)) {
				if (validateResult === false) {
					validateResult = Promise.reject();
				}
				else {
					validateResult = Promise.resolve();
				}
			}
			
			validateResult.then(function () {
				EventHandler.fire('com.woltlab.wcf.redactor2', 'submit_' + id, parameters);
				
				Ajax.api(this, {
					actionName: 'save',
					parameters: parameters
				});
				
				this._hideEditor();
			}.bind(this), function(e) {
				console.log('Validation of post edit failed: '+ e);
			});
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
			
			var data = {
				api: this,
				parameters: parameters,
				valid: true,
				promises: []
			};
			
			EventHandler.fire('com.woltlab.wcf.redactor2', 'validate_' + this._getEditorId(), data);
			
			data.promises.push(Promise[data.valid ? 'resolve' : 'reject']());
			
			return Promise.all(data.promises);
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
			var activeElement = this._activeElement;
			var editorId = this._getEditorId();
			var elementData = this._elements.get(activeElement);
			var attachmentLists = elBySelAll('.attachmentThumbnailList, .attachmentFileList', elementData.messageFooter);
			
			// set new content
			//noinspection JSUnresolvedVariable
			DomUtil.setInnerHtml(DomTraverse.childByClass(elementData.messageBody, 'messageText'), data.returnValues.message);
			
			// handle attachment list
			//noinspection JSUnresolvedVariable
			if (typeof data.returnValues.attachmentList === 'string') {
				for (var i = 0, length = attachmentLists.length; i < length; i++) {
					elRemove(attachmentLists[i]);
				}
				
				var element = elCreate('div');
				//noinspection JSUnresolvedVariable
				DomUtil.setInnerHtml(element, data.returnValues.attachmentList);
				
				var node;
				while (element.childNodes.length) {
					node = element.childNodes[element.childNodes.length - 1];
					elementData.messageFooter.insertBefore(node, elementData.messageFooter.firstChild);
				}
			}
			
			// handle poll
			//noinspection JSUnresolvedVariable
			if (typeof data.returnValues.poll === 'string') {
				// find current poll
				var poll = elBySel('.pollContainer', elementData.messageBody);
				if (poll !== null) {
					// poll contain is wrapped inside `.jsInlineEditorHideContent`
					elRemove(poll.parentNode);
				}
				
				var pollContainer = elCreate('div');
				pollContainer.className = 'jsInlineEditorHideContent';
				//noinspection JSUnresolvedVariable
				DomUtil.setInnerHtml(pollContainer, data.returnValues.poll);
				
				DomUtil.prepend(pollContainer, elementData.messageBody);
			}
			
			this._restoreMessage();
			
			this._updateHistory(this._getHash(this._getObjectId(activeElement)));
			
			EventHandler.fire('com.woltlab.wcf.redactor', 'autosaveDestroy_' + editorId);
			
			UiNotification.show();
			
			if (this._options.quoteManager) {
				this._options.quoteManager.clearAlternativeEditor();
				this._options.quoteManager.countQuotes();
			}
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
			EventHandler.fire('com.woltlab.wcf.redactor2', 'autosaveDestroy_' + this._getEditorId());
			EventHandler.fire('com.woltlab.wcf.redactor2', 'destroy_' + this._getEditorId());
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
			var editor = elBySel('.redactor-layer', elementData.messageBodyEditor);
			
			// handle errors occurring on editor load
			if (editor === null) {
				this._restoreMessage();
				
				return true;
			}
			
			this._restoreEditor();
			
			//noinspection JSUnresolvedVariable
			if (!data || data.returnValues === undefined || data.returnValues.realErrorMessage === undefined) {
				return true;
			}
			
			//noinspection JSUnresolvedVariable
			elInnerError(editor, data.returnValues.realErrorMessage);
			
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
			return {
				data: {
					className: this._options.className,
					interfaceName: 'wcf\\data\\IMessageInlineEditorAction'
				},
				silent: true
			};
		},
		
		/** @deprecated	3.0 - used only for backward compatibility with `WCF.Message.InlineEditor` */
		legacyEdit: function(containerId) {
			this._click(elById(containerId), null);
		}
	};
	
	return UiMessageInlineEditor;
});

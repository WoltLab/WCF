/**
 * Modal dialog handler.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	Ui/Dialog (alias)
 * @module	WoltLabSuite/Core/Ui/Dialog
 */
define(
	[
		'Ajax',         'Core',       'Dictionary',
		'Environment',  'Language',   'ObjectMap', 'Dom/ChangeListener',
		'Dom/Traverse', 'Dom/Util',   'Ui/Confirmation', 'Ui/Screen', 'Ui/SimpleDropdown',
		'EventHandler', 'List',       'EventKey'
	],
	function(
		Ajax,           Core,         Dictionary,
		Environment,    Language,     ObjectMap,   DomChangeListener,
		DomTraverse,    DomUtil,      UiConfirmation, UiScreen, UiSimpleDropdown,
		EventHandler,   List,         EventKey
	)
{
	"use strict";
	
	var _activeDialog = null;
	var _callbackFocus = null;
	var _container = null;
	var _dialogs = new Dictionary();
	var _dialogFullHeight = false;
	var _dialogObjects = new ObjectMap();
	var _dialogToObject = new Dictionary();
	var _focusedBeforeDialog = null;
	var _keyupListener = null;
	var _staticDialogs = elByClass('jsStaticDialog');
	var _validCallbacks = ['onBeforeClose', 'onClose', 'onShow'];
	
	// list of supported `input[type]` values for dialog submit
	var _validInputTypes = ['number', 'password', 'search', 'tel', 'text', 'url'];
	
	var _focusableElements = [
		'a[href]:not([tabindex^="-"]):not([inert])',
		'area[href]:not([tabindex^="-"]):not([inert])',
		'input:not([disabled]):not([inert])',
		'select:not([disabled]):not([inert])',
		'textarea:not([disabled]):not([inert])',
		'button:not([disabled]):not([inert])',
		'iframe:not([tabindex^="-"]):not([inert])',
		'audio:not([tabindex^="-"]):not([inert])',
		'video:not([tabindex^="-"]):not([inert])',
		'[contenteditable]:not([tabindex^="-"]):not([inert])',
		'[tabindex]:not([tabindex^="-"]):not([inert])'
	];
	
	/**
	 * @exports	WoltLabSuite/Core/Ui/Dialog
	 */
	return {
		/**
		 * Sets up global container and internal variables.
		 */
		setup: function() {
			// Fetch Ajax, as it cannot be provided because of a circular dependency
			if (Ajax === undefined) Ajax = require('Ajax');
			
			_container = elCreate('div');
			_container.classList.add('dialogOverlay');
			elAttr(_container, 'aria-hidden', 'true');
			_container.addEventListener('mousedown', this._closeOnBackdrop.bind(this));
			_container.addEventListener('wheel', function (event) {
				if (event.target === _container) {
					event.preventDefault();
				}
			}, { passive: false });
			
			elById('content').appendChild(_container);
			
			_keyupListener = (function(event) {
				if (event.keyCode === 27) {
					if (event.target.nodeName !== 'INPUT' && event.target.nodeName !== 'TEXTAREA') {
						this.close(_activeDialog);
						
						return false;
					}
				}
				
				return true;
			}).bind(this);
			
			UiScreen.on('screen-xs', {
				match: function() { _dialogFullHeight = true; },
				unmatch: function() { _dialogFullHeight = false; },
				setup: function() { _dialogFullHeight = true; }
			});
			
			this._initStaticDialogs();
			DomChangeListener.add('Ui/Dialog', this._initStaticDialogs.bind(this));
			
			UiScreen.setDialogContainer(_container);
			
			window.addEventListener('resize', (function () {
				_dialogs.forEach((function (dialog) {
					if (!elAttrBool(dialog.dialog, 'aria-hidden')) {
						this.rebuild(elData(dialog.dialog, 'id'));
					}
				}).bind(this));
			}).bind(this));
		},
		
		_initStaticDialogs: function() {
			var button, container, id;
			while (_staticDialogs.length) {
				button = _staticDialogs[0];
				button.classList.remove('jsStaticDialog');
				
				id = elData(button, 'dialog-id');
				if (id && (container = elById(id))) {
					((function(button, container) {
						container.classList.remove('jsStaticDialogContent');
						elData(container, 'is-static-dialog', true);
						elHide(container);
						button.addEventListener(WCF_CLICK_EVENT, (function(event) {
							event.preventDefault();
							
							this.openStatic(container.id, null, { title: elData(container, 'title') });
						}).bind(this));
					}).bind(this))(button, container);
				}
			}
		},
		
		/**
		 * Opens the dialog and implicitly creates it on first usage.
		 * 
		 * @param	{object}			callbackObject	used to invoke `_dialogSetup()` on first call
		 * @param	{(string|DocumentFragment=}	html		html content or document fragment to use for dialog content
		 * @returns	{object<string, *>}		dialog data
		 */
		open: function(callbackObject, html) {
			var dialogData = _dialogObjects.get(callbackObject);
			if (Core.isPlainObject(dialogData)) {
				// dialog already exists
				return this.openStatic(dialogData.id, html);
			}
			
			// initialize a new dialog
			if (typeof callbackObject._dialogSetup !== 'function') {
				throw new Error("Callback object does not implement the method '_dialogSetup()'.");
			}
			
			var setupData = callbackObject._dialogSetup();
			if (!Core.isPlainObject(setupData)) {
				throw new Error("Expected an object literal as return value of '_dialogSetup()'.");
			}
			
			dialogData = { id: setupData.id };
			
			var createOnly = true;
			if (setupData.source === undefined) {
				var dialogElement = elById(setupData.id);
				if (dialogElement === null) {
					throw new Error("Element id '" + setupData.id + "' is invalid and no source attribute was given. If you want to use the `html` argument instead, please add `source: null` to your dialog configuration.");
				}
				
				setupData.source = document.createDocumentFragment();
				setupData.source.appendChild(dialogElement);
				
				// remove id and `display: none` from dialog element
				dialogElement.removeAttribute('id');
				elShow(dialogElement);
			}
			else if (setupData.source === null) {
				// `null` means there is no static markup and `html` should be used instead
				setupData.source = html;
			}
			
			else if (typeof setupData.source === 'function') {
				setupData.source();
			}
			else if (Core.isPlainObject(setupData.source)) {
				if (typeof html === 'string' && html.trim() !== '') {
					setupData.source = html;
				}
				else {
					Ajax.api(this, setupData.source.data, (function (data) {
						if (data.returnValues && typeof data.returnValues.template === 'string') {
							this.open(callbackObject, data.returnValues.template);
							
							if (typeof setupData.source.after === 'function') {
								setupData.source.after(_dialogs.get(setupData.id).content, data);
							}
						}
					}).bind(this));
					
					// deferred initialization
					return {};
				}
			}
			else {
				if (typeof setupData.source === 'string') {
					var dialogElement = elCreate('div');
					elAttr(dialogElement, 'id', setupData.id);
					DomUtil.setInnerHtml(dialogElement, setupData.source);
					
					setupData.source = document.createDocumentFragment();
					setupData.source.appendChild(dialogElement);
				}
				
				if (!setupData.source.nodeType || setupData.source.nodeType !== Node.DOCUMENT_FRAGMENT_NODE) {
					throw new Error("Expected at least a document fragment as 'source' attribute.");
				}
				
				createOnly = false;
			}
			
			_dialogObjects.set(callbackObject, dialogData);
			_dialogToObject.set(setupData.id, callbackObject);
			
			return this.openStatic(setupData.id, setupData.source, setupData.options, createOnly);
		},
		
		/**
		 * Opens an dialog, if the dialog is already open the content container
		 * will be replaced by the HTML string contained in the parameter html.
		 * 
		 * If id is an existing element id, html will be ignored and the referenced
		 * element will be appended to the content element instead.
		 * 
		 * @param 	{string}			id		element id, if exists the html parameter is ignored in favor of the existing element
		 * @param	{?(string|DocumentFragment)}	html		content html
		 * @param	{object<string, *>}		options		list of options, is completely ignored if the dialog already exists
		 * @param	{boolean=}			createOnly	create the dialog but do not open it
		 * @return	{object<string, *>}		dialog data
		 */
		openStatic: function(id, html, options, createOnly) {
			UiScreen.pageOverlayOpen();
			
			if (Environment.platform() !== 'desktop') {
				if (!this.isOpen(id)) {
					UiScreen.scrollDisable();
				}
			}
			
			if (_dialogs.has(id)) {
				this._updateDialog(id, html);
			}
			else {
				options = Core.extend({
					backdropCloseOnClick: true,
					closable: true,
					closeButtonLabel: Language.get('wcf.global.button.close'),
					closeConfirmMessage: '',
					disableContentPadding: false,
					title: '',
					
					// callbacks
					onBeforeClose: null,
					onClose: null,
					onShow: null
				}, options);
				
				if (!options.closable) options.backdropCloseOnClick = false;
				if (options.closeConfirmMessage) {
					options.onBeforeClose = (function(id) {
						UiConfirmation.show({
							confirm: this.close.bind(this, id),
							message: options.closeConfirmMessage
						});
					}).bind(this);
				}
				
				this._createDialog(id, html, options);
			}
			
			var data = _dialogs.get(id);
			
			// iOS breaks `position: fixed` when input elements or `contenteditable`
			// are focused, this will freeze the screen and force Safari to scroll
			// to the input field
			if (Environment.platform() === 'ios') {
				window.setTimeout((function () {
					var input = elBySel('input, textarea', data.content);
					if (input !== null) {
						input.focus();
					}
				}).bind(this), 200);
			}
			
			return data;
		},
		
		/**
		 * Sets the dialog title.
		 * 
		 * @param	{(string|object)}	id		element id
		 * @param	{string}	        title		dialog title
		 */
		setTitle: function(id, title) {
			id = this._getDialogId(id);
			
			var data = _dialogs.get(id);
			if (data === undefined) {
				throw new Error("Expected a valid dialog id, '" + id + "' does not match any active dialog.");
			}
			
			var dialogTitle = elByClass('dialogTitle', data.dialog);
			if (dialogTitle.length) {
				dialogTitle[0].textContent = title;
			}
		},
		
		/**
		 * Sets a callback function on runtime.
		 * 
		 * @param       {(string|object)}       id              element id
		 * @param       {string}                key             callback identifier
		 * @param       {?function}             value           callback function or `null`
		 */
		setCallback: function(id, key, value) {
			if (typeof id === 'object') {
				var dialogData = _dialogObjects.get(id);
				if (dialogData !== undefined) {
					id = dialogData.id;
				}
			}
			
			var data = _dialogs.get(id);
			if (data === undefined) {
				throw new Error("Expected a valid dialog id, '" + id + "' does not match any active dialog.");
			}
			
			if (_validCallbacks.indexOf(key) === -1) {
				throw new Error("Invalid callback identifier, '" + key + "' is not recognized.");
			}
			
			if (typeof value !== 'function' && value !== null) {
				throw new Error("Only functions or the 'null' value are acceptable callback values ('" + typeof value+ "' given).");
			}
			
			data[key] = value;
		},
		
		/**
		 * Creates the DOM for a new dialog and opens it.
		 * 
		 * @param 	{string}			id		element id, if exists the html parameter is ignored in favor of the existing element
		 * @param	{?(string|DocumentFragment)}	html		content html
		 * @param	{object<string, *>}		options		list of options
		 * @param	{boolean=}			createOnly	create the dialog but do not open it
		 */
		_createDialog: function(id, html, options, createOnly) {
			var element = null;
			if (html === null) {
				element = elById(id);
				if (element === null) {
					throw new Error("Expected either a HTML string or an existing element id.");
				}
			}
			
			var dialog = elCreate('div');
			dialog.classList.add('dialogContainer');
			elAttr(dialog, 'aria-hidden', 'true');
			elAttr(dialog, 'role', 'dialog');
			elData(dialog, 'id', id);
			
			var header = elCreate('header');
			dialog.appendChild(header);
			
			var titleId = DomUtil.getUniqueId();
			elAttr(dialog, 'aria-labelledby', titleId);
			
			var title = elCreate('span');
			title.classList.add('dialogTitle');
			title.textContent = options.title;
			elAttr(title, 'id', titleId);
			header.appendChild(title);
			
			if (options.closable) {
				var closeButton = elCreate('a');
				closeButton.className = 'dialogCloseButton jsTooltip';
				closeButton.href = '#';
				elAttr(closeButton, 'role', 'button');
				elAttr(closeButton, 'tabindex', '0');
				elAttr(closeButton, 'title', options.closeButtonLabel);
				elAttr(closeButton, 'aria-label', options.closeButtonLabel);
				closeButton.addEventListener(WCF_CLICK_EVENT, this._close.bind(this));
				header.appendChild(closeButton);
				
				var span = elCreate('span');
				span.className = 'icon icon24 fa-times';
				closeButton.appendChild(span);
			}
			
			var contentContainer = elCreate('div');
			contentContainer.classList.add('dialogContent');
			if (options.disableContentPadding) contentContainer.classList.add('dialogContentNoPadding');
			dialog.appendChild(contentContainer);
			
			contentContainer.addEventListener('wheel', function (event) {
				var allowScroll = false;
				var element = event.target, clientHeight, scrollHeight, scrollTop;
				while (true) {
					clientHeight = element.clientHeight;
					scrollHeight = element.scrollHeight;
					
					if (clientHeight < scrollHeight) {
						scrollTop = element.scrollTop;
						
						// negative value: scrolling up
						if (event.deltaY < 0 && scrollTop > 0) {
							allowScroll = true;
							break;
						}
						else if (event.deltaY > 0 && (scrollTop + clientHeight < scrollHeight)) {
							allowScroll = true;
							break;
						}
					}
					
					if (!element || element === contentContainer) {
						break;
					}
					
					element = element.parentNode;
				}
				
				if (allowScroll === false) {
					event.preventDefault();
				}
			}, { passive: false });
			
			var content;
			if (element === null) {
				if (typeof html === 'string') {
					content = elCreate('div');
					content.id = id;
					DomUtil.setInnerHtml(content, html);
				}
				else if (html instanceof DocumentFragment) {
					var children = [], node;
					for (var i = 0, length = html.childNodes.length; i < length; i++) {
						node = html.childNodes[i];
						
						if (node.nodeType === Node.ELEMENT_NODE) {
							children.push(node);
						}
					}
					
					if (children[0].nodeName !== 'DIV' || children.length > 1) {
						content = elCreate('div');
						content.id = id;
						content.appendChild(html);
					}
					else {
						content = children[0];
					}
				}
				else {
					throw new TypeError("'html' must either be a string or a DocumentFragment");
				}
			}
			else {
				content = element;
			}
			
			contentContainer.appendChild(content);
			
			if (content.style.getPropertyValue('display') === 'none') {
				elShow(content);
			}
			
			_dialogs.set(id, {
				backdropCloseOnClick: options.backdropCloseOnClick,
				closable: options.closable,
				content: content,
				dialog: dialog,
				header: header,
				onBeforeClose: options.onBeforeClose,
				onClose: options.onClose,
				onShow: options.onShow,
				
				submitButton: null,
				inputFields: new List()
			});
			
			DomUtil.prepend(dialog, _container);
			
			if (typeof options.onSetup === 'function') {
				options.onSetup(content);
			}
			
			if (createOnly !== true) {
				this._updateDialog(id, null);
			}
		},
		
		/**
		 * Updates the dialog's content element.
		 * 
		 * @param 	{string}		id		element id
		 * @param	{?string}		html		content html, prevent changes by passing null
		 */
		_updateDialog: function(id, html) {
			var data = _dialogs.get(id);
			if (data === undefined) {
				throw new Error("Expected a valid dialog id, '" + id + "' does not match any active dialog.");
			}
			
			if (typeof html === 'string') {
				DomUtil.setInnerHtml(data.content, html);
			}
			
			if (elAttr(data.dialog, 'aria-hidden') === 'true') {
				// close existing dropdowns
				UiSimpleDropdown.closeAll();
				window.WCF.Dropdown.Interactive.Handler.closeAll();
				
				if (_callbackFocus === null) {
					_callbackFocus = this._maintainFocus.bind(this);
					document.body.addEventListener('focus', _callbackFocus, { capture: true });
				}
				
				if (data.closable && elAttr(_container, 'aria-hidden') === 'true') {
					window.addEventListener('keyup', _keyupListener);
				}
				
				elAttr(data.dialog, 'aria-hidden', 'false');
				elAttr(_container, 'aria-hidden', 'false');
				elData(_container, 'close-on-click', (data.backdropCloseOnClick ? 'true' : 'false'));
				_activeDialog = id;
				
				// Keep a reference to the currently focused element to be able to restore it later.
				_focusedBeforeDialog = document.activeElement;
				
				// Set the focus to the first focusable child of the dialog element.
				var closeButton = elBySel('.dialogCloseButton', data.header);
				if (closeButton) elAttr(closeButton, 'inert', true);
				this._setFocusToFirstItem(data.dialog);
				if (closeButton) closeButton.removeAttribute('inert');
				
				if (typeof data.onShow === 'function') {
					data.onShow(data.content);
				}
				
				if (elDataBool(data.content, 'is-static-dialog')) {
					EventHandler.fire('com.woltlab.wcf.dialog', 'openStatic', {
						content: data.content,
						id: id
					});
				}
			}
			
			this.rebuild(id);
			
			DomChangeListener.trigger();
		},
		
		/**
		 * @param {Event} event
		 */
		_maintainFocus: function(event) {
			if (_activeDialog) {
				var data = _dialogs.get(_activeDialog);
				if (!data.dialog.contains(event.target) && !event.target.closest('.dropdownMenuContainer') && !event.target.closest('.datePicker')) {
					this._setFocusToFirstItem(data.dialog, true);
				}
			}
		},
		
		/**
		 * @param {Element} dialog
		 * @param {boolean} maintain
		 */
		_setFocusToFirstItem: function(dialog, maintain) {
			var focusElement = this._getFirstFocusableChild(dialog);
			if (focusElement !== null) {
				if (maintain) {
					if (focusElement.id === 'username' || focusElement.name === 'username') {
						if (Environment.browser() === 'safari' && Environment.platform() === 'ios') {
							// iOS Safari's username/password autofill breaks if the input field is focused 
							focusElement = null;
						}
					}
				}
				
				if (focusElement) {
					// Setting the focus to a select element in iOS is pretty strange, because
					// it focuses it, but also displays the keyboard for a fraction of a second,
					// causing it to pop out from below and immediately vanish.
					// 
					// iOS will only show the keyboard if an input element is focused *and* the
					// focus is an immediate result of a user interaction. This method must be
					// assumed to be called from within a click event, but we want to set the
					// focus without triggering the keyboard.
					// 
					// We can break the condition by wrapping it in a setTimeout() call,
					// effectively tricking iOS into focusing the element without showing the
					// keyboard.
					setTimeout(function() {
						focusElement.focus();
					}, 1);
				}
			}
		},
		
		/**
		 * @param {Element} node
		 * @returns {?Element}
		 */
		_getFirstFocusableChild: function(node) {
			var nodeList = elBySelAll(_focusableElements.join(','), node);
			for (var i = 0, length = nodeList.length; i < length; i++) {
				if (nodeList[i].offsetWidth && nodeList[i].offsetHeight && nodeList[i].getClientRects().length) {
					return nodeList[i];
				}
			}
			
			return null;	
		},
		
		/**
		 * Rebuilds dialog identified by given id.
		 * 
		 * @param	{string}	id	element id
		 */
		rebuild: function(id) {
			id = this._getDialogId(id);
			
			var data = _dialogs.get(id);
			if (data === undefined) {
				throw new Error("Expected a valid dialog id, '" + id + "' does not match any active dialog.");
			}
			
			// ignore non-active dialogs
			if (elAttr(data.dialog, 'aria-hidden') === 'true') {
				return;
			}
			
			var contentContainer = data.content.parentNode;
			
			var formSubmit = elBySel('.formSubmit', data.content);
			var unavailableHeight = 0;
			if (formSubmit !== null) {
				contentContainer.classList.add('dialogForm');
				formSubmit.classList.add('dialogFormSubmit');
				
				unavailableHeight += DomUtil.outerHeight(formSubmit);
				
				// Calculated height can be a fractional value and depending on the
				// browser the results can vary. By subtracting a single pixel we're
				// working around fractional values, without visually changing anything.
				unavailableHeight -= 1;
				
				contentContainer.style.setProperty('margin-bottom', unavailableHeight + 'px', '');
			}
			else {
				contentContainer.classList.remove('dialogForm');
				contentContainer.style.removeProperty('margin-bottom');
			}
			
			unavailableHeight += DomUtil.outerHeight(data.header);
			
			var maximumHeight = (window.innerHeight * (_dialogFullHeight ? 1 : 0.8)) - unavailableHeight;
			contentContainer.style.setProperty('max-height', ~~maximumHeight + 'px', '');
			
			// fix for a calculation bug in Chrome causing the scrollbar to overlap the border
			if (Environment.browser() === 'chrome') {
				if (data.content.scrollHeight > maximumHeight) {
					data.content.style.setProperty('margin-right', '-1px', '');
				}
				else {
					data.content.style.removeProperty('margin-right');
				}
			}
			
			// Chrome and Safari use heavy anti-aliasing when the dialog's width
			// cannot be evenly divided, causing the whole text to become blurry
			if (Environment.browser() === 'chrome' || Environment.browser() === 'safari') {
				// The new Microsoft Edge is detected as "chrome", because effectively we're detecting
				// Chromium rather than Chrome specifically. The workaround for fractional pixels does
				// not work well in Edge, there seems to be a different logic for fractional positions,
				// causing the text to be blurry.
				// 
				// We can use `backface-visibility: hidden` to prevent the anti aliasing artifacts in
				// WebKit/Blink, which will also prevent some weird font rendering issues when resizing.
				data.content.parentNode.classList.add('jsWebKitFractionalPixelFix');
			}
			
			var callbackObject = _dialogToObject.get(id);
			//noinspection JSUnresolvedVariable
			if (callbackObject !== undefined && typeof callbackObject._dialogSubmit === 'function') {
				var inputFields = elBySelAll('input[data-dialog-submit-on-enter="true"]', data.content);
				
				var submitButton = elBySel('.formSubmit > input[type="submit"], .formSubmit > button[data-type="submit"]', data.content);
				if (submitButton === null) {
					// check if there is at least one input field with submit handling,
					// otherwise we'll assume the dialog has not been populated yet
					if (inputFields.length === 0) {
						console.warn("Broken dialog, expected a submit button.", data.content);
					}
					
					return;
				}
				
				if (data.submitButton !== submitButton) {
					data.submitButton = submitButton;
					
					submitButton.addEventListener(WCF_CLICK_EVENT, (function (event) {
						event.preventDefault();
						
						this._submit(id);
					}).bind(this));
					
					// bind input fields
					var inputField, _callbackKeydown = null;
					for (var i = 0, length = inputFields.length; i < length; i++) {
						inputField = inputFields[i];
						
						if (data.inputFields.has(inputField)) continue;
						
						if (_validInputTypes.indexOf(inputField.type) === -1) {
							console.warn("Unsupported input type.", inputField);
							continue;
						}
						
						data.inputFields.add(inputField);
						
						if (_callbackKeydown === null) {
							_callbackKeydown = (function (event) {
								if (EventKey.Enter(event)) {
									event.preventDefault();
									
									this._submit(id);
								}
							}).bind(this);
						}
						inputField.addEventListener('keydown', _callbackKeydown);
					}
				}
			}
		},
		
		/**
		 * Submits the dialog.
		 * 
		 * @param       {string}        id      dialog id
		 * @protected
		 */
		_submit: function (id) {
			var data = _dialogs.get(id);
			
			var isValid = true;
			data.inputFields.forEach(function (inputField) {
				if (inputField.required) {
					if (inputField.value.trim() === '') {
						elInnerError(inputField, Language.get('wcf.global.form.error.empty'));
						
						isValid = false;
					}
					else {
						elInnerError(inputField, false);
					}
				}
			});
			
			if (isValid) {
				//noinspection JSUnresolvedFunction
				_dialogToObject.get(id)._dialogSubmit();
			}
		},
		
		/**
		 * Handles clicks on the close button or the backdrop if enabled.
		 * 
		 * @param	{object}	event		click event
		 * @return	{boolean}	false if the event should be cancelled
		 */
		_close: function(event) {
			event.preventDefault();
			
			var data = _dialogs.get(_activeDialog);
			if (typeof data.onBeforeClose === 'function') {
				data.onBeforeClose(_activeDialog);
				
				return false;
			}
			
			this.close(_activeDialog);
		},
		
		/**
		 * Closes the current active dialog by clicks on the backdrop.
		 * 
		 * @param	{object}	event	event object
		 */
		_closeOnBackdrop: function(event) {
			if (event.target !== _container) {
				return true;
			}
			
			if (elData(_container, 'close-on-click') === 'true') {
				this._close(event);
			}
			else {
				event.preventDefault();
			}
		},
		
		/**
		 * Closes a dialog identified by given id.
		 * 
		 * @param	{(string|object)}	id	element id or callback object
		 */
		close: function(id) {
			id = this._getDialogId(id);
			
			var data = _dialogs.get(id);
			if (data === undefined) {
				throw new Error("Expected a valid dialog id, '" + id + "' does not match any active dialog.");
			}
			
			elAttr(data.dialog, 'aria-hidden', 'true');
			
			// avoid keyboard focus on a now hidden element 
			if (document.activeElement.closest('.dialogContainer') === data.dialog) {
				document.activeElement.blur();
			}
			
			if (typeof data.onClose === 'function') {
				data.onClose(id);
			}
			
			// get next active dialog
			_activeDialog = null;
			for (var i = 0; i < _container.childElementCount; i++) {
				var child = _container.children[i];
				if (elAttr(child, 'aria-hidden') === 'false') {
					_activeDialog = elData(child, 'id');
					break;
				}
			}
			
			UiScreen.pageOverlayClose();
			
			if (_activeDialog === null) {
				elAttr(_container, 'aria-hidden', 'true');
				elData(_container, 'close-on-click', 'false');
				
				if (data.closable) {
					window.removeEventListener('keyup', _keyupListener);
				}
			}
			else {
				data = _dialogs.get(_activeDialog);
				elData(_container, 'close-on-click', (data.backdropCloseOnClick ? 'true' : 'false'));
			}
			
			if (Environment.platform() !== 'desktop') {
				UiScreen.scrollEnable();
			}
		},
		
		/**
		 * Returns the dialog data for given element id.
		 * 
		 * @param	{(string|object)}	id	element id or callback object
		 * @return	{(object|undefined)}	dialog data or undefined if element id is unknown
		 */
		getDialog: function(id) {
			return _dialogs.get(this._getDialogId(id));
		},
		
		/**
		 * Returns true for open dialogs.
		 * 
		 * @param	{(string|object)}	id	element id or callback object
		 * @return      {boolean}
		 */
		isOpen: function(id) {
			var data = this.getDialog(id);
			return (data !== undefined && elAttr(data.dialog, 'aria-hidden') === 'false');
		},
		
		/**
		 * Destroys a dialog instance.
		 * 
		 * @param	{Object}	callbackObject  the same object that was used to invoke `_dialogSetup()` on first call
		 */
		destroy: function(callbackObject) {
			if (typeof callbackObject !== 'object' || callbackObject instanceof String) {
				throw new TypeError("Expected the callback object as parameter.");
			}
			
			if (_dialogObjects.has(callbackObject)) {
				var id = _dialogObjects.get(callbackObject).id;
				if (this.isOpen(id)) {
					this.close(id);
				}
				
				// If the dialog is destroyed in the close callback, this method is
				// called twice resulting in `_dialogs.get(id)` being undefined for
				// the initial call.
				if (_dialogs.has(id)) {
					elRemove(_dialogs.get(id).dialog);
					_dialogs.delete(id);
				}
				_dialogObjects.delete(callbackObject);
			}
		},
		
		/**
		 * Returns a dialog's id.
		 * 
		 * @param	{(string|object)}	id	element id or callback object
		 * @return      {string}
		 * @protected
		 */
		_getDialogId: function(id) {
			if (typeof id === 'object') {
				var dialogData = _dialogObjects.get(id);
				if (dialogData !== undefined) {
					return dialogData.id;
				}
			}
			
			return id.toString();
		},
		
		_ajaxSetup: function() {
			return {};
		}
	};
});

/**
 * Modal dialog handler.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/UI/Dialog
 */
define(['jquery', 'Core', 'Dictionary', 'DOM/Util'], function($, Core, Dictionary, DOMUtil) {
	"use strict";
	
	var _activeDialog = null;
	var _container = null;
	var _dialogs = null;
	var _keyupListener = null;
	
	/**
	 * @constructor
	 */
	function UIDialog() {};
	UIDialog.prototype = {
		/**
		 * Sets up global container and internal variables.
		 */
		setup: function() {
			_container = document.createElement('div');
			_container.classList.add('dialogOverlay');
			_container.setAttribute('aria-hidden', 'true');
			_container.addEventListener('click', this._closeOnBackdrop.bind(this));
			
			document.body.appendChild(_container);
			
			_dialogs = new Dictionary();
			
			_keyupListener = (function(event) {
				if (event.keyCode === 27) {
					if (event.target.nodeName !== 'INPUT' && event.target.nodeName !== 'TEXTAREA') {
						this.close(_activeDialog);
						
						return false;
					}
				}
				
				return true;
			}).bind(this);
		},
		
		/**
		 * Opens an dialog, if the dialog is already open the content container
		 * will be replaced by the HTML string contained in the parameter html.
		 * 
		 * If id is an existing element id, html will be ignored and the referenced
		 * element will be appended to the content element instead.
		 * 
		 * @param 	{string}		id		element id, if exists the html parameter is ignored in favor of the existing element
		 * @param	{?string}		html		content html
		 * @param	{object<string, *>}	options		list of options, is completely ignored if the dialog already exists
		 */
		open: function(id, html, options) {
			if (_dialogs.has(id)) {
				this._updateDialog(id, html);
			}
			else {
				options = Core.extend({
					backdropCloseOnClick: true,
					closable: true,
					closeButtonLabel: WCF.Language.get('wcf.global.button.close'),
					closeConfirmMessage: '',
					disableContentPadding: false,
					disposeOnClose: false,
					title: '',
					
					// callbacks
					onBeforeClose: null,
					onClose: null,
					onShow: null
				}, options);
				
				if (!options.closable) options.backdropCloseOnClick = false;
				if (options.closeConfirmMessage) {
					options.onBeforeClose = (function(id) {
						WCF.System.Confirmation.show(options.closeConfirmMessage, (function(action) {
							if (action === 'confirm') {
								this.close(id);
							}
						}).bind(this));
					}).bind(this);
				}
				
				this._createDialog(id, html, options);
			}
		},
		
		/**
		 * Sets the dialog title.
		 * 
		 * @param	{string}	id		element id
		 * @param	{string}	title		dialog title
		 */
		setTitle: function(id, title) {
			var data = _dialogs.get(id);
			if (typeof data === 'undefined') {
				throw new Error("Expected a valid dialog id, '" + id + "' does not match any active dialog.");
			}
			
			var header = DOMTraverse.childrenByTag(data.dialog, 'HEADER');
			DOMTraverse.childrenByTag(header[0], 'SPAN').textContent = title;
		},
		
		/**
		 * Creates the DOM for a new dialog and opens it.
		 * 
		 * @param 	{string}		id		element id, if exists the html parameter is ignored in favor of the existing element
		 * @param	{?string}		html		content html
		 * @param	{object<string, *>}	options		list of options
		 */
		_createDialog: function(id, html, options) {
			var element = null;
			if (html === null) {
				element = document.getElementById(id);
				if (element === null) {
					throw new Error("Expected either a HTML string or an existing element id.");
				}
			}
			
			var dialog = document.createElement('div');
			dialog.classList.add('dialogContainer');
			dialog.setAttribute('aria-hidden', 'true');
			dialog.setAttribute('role', 'dialog')
			dialog.setAttribute('data-id', id);
			
			if (options.disposeOnClose) {
				dialog.setAttribute('data-dispose-on-close', true);
			}
			
			var header = document.createElement('header');
			dialog.appendChild(header);
			
			if (options.title) {
				var titleId = DOMUtil.getUniqueId();
				dialog.setAttribute('aria-labelledby', titleId);
				
				var title = document.createElement('span');
				title.classList.add('dialogTitle');
				title.textContent = options.title;
				title.setAttribute('id', titleId);
				header.appendChild(title);
			}
			
			if (options.closable) {
				var closeButton = document.createElement('a');
				closeButton.className = 'dialogCloseButton jsTooltip';
				closeButton.setAttribute('title', options.closeButtonLabel);
				closeButton.setAttribute('aria-label', options.closeButtonLabel);
				closeButton.addEventListener('click', this._close.bind(this));
				header.appendChild(closeButton);
				
				var span = document.createElement('span');
				span.textContent = options.closeButtonLabel;
				closeButton.appendChild(span);
			}
			
			var contentContainer = document.createElement('div');
			contentContainer.classList.add('dialogContent');
			if (options.disableContentPadding) contentContainer.classList.add('dialogContentNoPadding');
			dialog.appendChild(contentContainer);
			
			var content;
			if (element === null) {
				content = document.createElement('div');
				content.setAttribute('id', id);
				content.innerHTML = html;
			}
			else {
				content = element;
			}
			
			contentContainer.appendChild(element);
			
			_dialogs.set(id, {
				backdropCloseOnClick: options.backdropCloseOnClick,
				content: content,
				dialog: dialog,
				header: header,
				onBeforeClose: options.onBeforeClose,
				onClose: options.onClose,
				onShow: options.onShow
			});
			
			if (_container.getAttribute('aria-hidden') === 'true') {
				window.addEventListener('keyup', _keyupListener);
			}
			
			DOMUtil.prepend(dialog, _container);
			_container.setAttribute('aria-hidden', 'false');
			_container.setAttribute('data-close-on-click', (options.backdropCloseOnClick ? 'true' : 'false'));
			dialog.setAttribute('aria-hidden', 'false');
			
			this.rebuild(id);
			
			_activeDialog = id;
			
			if (typeof options.onShow === 'function') {
				options.onShow(id);
			}
			
			WCF.DOMNodeInsertedHandler.execute();
		},
		
		/**
		 * Updates the dialog's content element.
		 * 
		 * @param 	{string}		id		element id
		 * @param	{?string}		html		content html, prevent changes by passing null
		 */
		_updateDialog: function(id, html) {
			var data = _dialogs.get(id);
			if (typeof data === 'undefined') {
				throw new Error("Expected a valid dialog id, '" + id + "' does not match any active dialog.");
			}
			
			if (typeof html === 'string') {
				data.content.innerHTML = '';
				
				var content = document.createElement('div');
				content.innerHTML = html;
				
				data.content.appendChild(content);
			}
			
			if (data.dialog.getAttribute('aria-hidden') === 'true') {
				data.dialog.setAttribute('aria-hidden', 'false');
				_container.setAttribute('aria-hidden', 'false');
				_container.setAttribute('data-close-on-click', (data.backdropCloseOnClick ? 'true' : 'false'));
				_activeDialog = id;
				
				window.addEventListener('keyup', _keyupListener);
				
				this.rebuild(id);
				
				if (typeof data.onShow === 'function') {
					data.onShow(id);
				}
			}
			
			WCF.DOMNodeInsertedHandler.execute();
		},
		
		/**
		 * Rebuilds dialog identified by given id.
		 * 
		 * @param	{string}	id	element id
		 */
		rebuild: function(id) {
			var data = _dialogs.get(id);
			if (typeof data === 'undefined') {
				throw new Error("Expected a valid dialog id, '" + id + "' does not match any active dialog.");
			}
			
			// ignore non-active dialogs
			if (data.dialog.getAttribute('aria-hidden') === 'true') {
				return;
			}
			
			// fix for a calculation bug in Chrome causing the scrollbar to overlap the border
			if ($.browser.chrome) {
				if (data.content.scrollHeight > data.content.clientHeight) {
					data.content.style.setProperty('margin-right', '-1px');
				}
			}
			
			var contentContainer = data.content.parentNode;
			
			var formSubmit = data.content.querySelector('.formSubmit');
			var unavailableHeight = 0;
			if (formSubmit !== null) {
				contentContainer.classList.add('dialogForm');
				formSubmit.classList.add('dialogFormSubmit');
				
				unavailableHeight += DOMUtil.outerHeight(formSubmit);
				contentContainer.style.setProperty('margin-bottom', unavailableHeight + 'px');
			}
			else {
				contentContainer.classList.remove('dialogForm');
			}
			
			unavailableHeight += DOMUtil.outerHeight(data.header);
			
			var maximumHeight = (window.innerHeight * 0.8) - unavailableHeight;
			contentContainer.style.setProperty('max-height', ~~maximumHeight + 'px');
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
			
			if (_container.getAttribute('data-close-on-click') === 'true') {
				this._close(event);
			}
			else {
				event.preventDefault();
			}
		},
		
		/**
		 * Closes a dialog identified by given id.
		 * 
		 * @param	{string}	id	element id
		 */
		close: function(id) {
			var data = _dialogs.get(id);
			if (typeof data === 'undefined') {
				throw new Error("Expected a valid dialog id, '" + id + "' does not match any active dialog.");
			}
			
			if (typeof data.onClose === 'function') {
				data.onClose(id);
			}
			
			if (data.dialog.getAttribute('data-dispose-on-close')) {
				setTimeout(function() {
					if (data.dialog.getAttribute('aria-hidden') === 'true') {
						_container.removeChild(data.dialog);
						_dialogs.remove(id);
					}
				}, 5000);
			}
			else {
				data.dialog.setAttribute('aria-hidden', 'true');
			}
			
			// get next active dialog
			_activeDialog = null;
			for (var i = 0; i < _container.childElementCount; i++) {
				var child = _container.children[i];
				if (child.getAttribute('aria-hidden') === 'false') {
					_activeDialog = child.getAttribute('data-id');
					break;
				}
			}
			
			if (_activeDialog === null) {
				_container.setAttribute('aria-hidden', 'true');
				_container.setAttribute('data-close-on-click', 'false');
				
				window.removeEventListener('keyup', _keyupListener);
			}
			else {
				data = _dialogs.get(_activeDialog);
				_container.setAttribute('data-close-on-click', (data.backdropCloseOnClick ? 'true' : 'false'));
			}
		},
		
		/**
		 * Returns the dialog data for given element id.
		 * 
		 * @param	{string}	id	element id
		 * @return	{(object|undefined)}	dialog data or undefined if element id is unknown
		 */
		getDialog: function(id) {
			return _dialogs.get(id);
		}
	};
	
	return new UIDialog();
});

define(['Ajax', 'Core', 'EventHandler', 'ObjectMap', 'Dom/Traverse', 'Dom/Util'], function(Ajax, Core, EventHandler, ObjectMap, DomTraverse, DomUtil) {
	"use strict";
	
	var _activeElement = null;
	var _elements = new ObjectMap();
	var _options = {};
	
	var UiMessageInlineEditor = {
		init: function(options) {
			_options = Core.extend({
				extendedForm: false,
				
				className: '',
				containerId: 0,
				editorPrefix: 'messageEditor',
				
				messageSelector: '.jsMessage'
			}, options);
			
			this._initElements();
		},
		
		_initElements: function() {
			var button, element, elements = elBySelAll(_options.messageSelector);
			for (var i = 0, length = elements.length; i < length; i++) {
				element = elements[i];
				if (_elements.has(element)) {
					continue;
				}
				
				if (elAttrBool(element, 'data-can-edit')) {
					button = elBySel('.jsMessageEditButton', element);
					if (button !== null) {
						button.addEventListener('click', this._click.bind(this, element));
					}
				}
				
				_elements.set(element, {
					messageBody: null,
					messageBodyEditor: null,
					messageFooter: null,
					messageText: null
				});
			}
		},
		
		_click: function(element, event) {
			event.preventDefault();
			
			if (_activeElement !== null) {
				// TODO: show notification
			}
			
			_activeElement = element;
			
			this._prepare();
			
			Ajax.api(this, {
				actionName: 'beginEdit',
				parameters: {
					containerID: _options.containerId,
					objectID: elAttr(element, 'data-object-id')
				}
			});
		},
		
		_prepare: function() {
			var data = _elements.get(_activeElement);
			if (data.messageBody === null) data.messageBody = elBySel('.messageBody', _activeElement);
			if (data.messageFooter === null) data.messageFooter = elBySel('.messageFooter', _activeElement);
			if (data.messageText === null) data.messageText = elBySel('.messageText', data.messageBody);
			
			var messageBodyEditor = elCreate('div');
			messageBodyEditor.className = 'messageBody editor';
			data.messageBodyEditor = messageBodyEditor;
			
			var icon = elCreate('span');
			icon.className = 'icon icon48 fa-spinner';
			messageBodyEditor.appendChild(icon);
			
			DomUtil.insertAfter(messageBodyEditor, data.messageBody);
			
			elHide(data.messageBody);
		},
		
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
			if ($.browser.redactor) {
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
		
		_save: function() {
			var parameters = {
				containerID: _options.containerId,
				data: {
					message: ''
				},
				objectID: elAttr(_activeElement, 'data-object-id'),
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
		
		_showMessage: function() {
			
		},
		
		_prepareExtended: function() {
			
		},
		
		_hideEditor: function() {
			var elementData = _elements.get(_activeElement);
			elHide(DomTraverse.childByClass(elementData.messageBodyEditor, 'editorContainer'));
			
			var icon = elCreate('span');
			icon.className = 'icon icon48 fa-spinner';
			elementData.messageBodyEditor.appendChild(icon);
		},
		
		_destroyEditor: function() {
			EventHandler.fire('com.woltlab.wcf.redactor', 'destroy_' + this._getEditorId());
		},
		
		_getEditorId: function() {
			return _options.editorPrefix + elAttr(_activeElement, 'data-object-id');
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
					className: _options.className,
					interfaceName: 'wcf\\data\\IMessageInlineEditorAction'
				}
			};
		}
	};
	
	return UiMessageInlineEditor;
});

/**
 * Manages insertation and editing of quotes.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Ui/Redactor/Quote
 */
define(['EventHandler', 'Language', 'Dom/Util', 'Ui/Dialog'], function(EventHandler, Language, DomUtil, UiDialog) {
	"use strict";
	
	var _callbackEdit = null;
	var _element = null;
	var _insertCallback = null;
	var _quotePaddingTop = 0;
	var _titleHeight = 0;
	var _wysiwygQuoteButton = null;
	var _wysiwygQuoteTitle = null;
	var _wysiwygQuoteUrl = null;
	
	return {
		/**
		 * Registers an editor instance.
		 * 
		 * @param       {string}        editorId        textarea identifier
		 * @param       {object}        editor          editor element
		 */
		initEditor: function(editorId, editor) {
			EventHandler.add('com.woltlab.wcf.redactor2', 'observe_load_' + editorId, (function(data) {
				this.observeAll(data.editor);
			}).bind(this));
			
			this.observeAll(editor);
		},
		
		/**
		 * Opens a dialog to insert a quote at caret position.
		 * 
		 * @param       {function}      callback        callback invoked with the <blockquote> element as parameter
		 */
		insert: function(callback) {
			_insertCallback = callback;
			
			UiDialog.open(this);
			UiDialog.setTitle(this, Language.get('wcf.wysiwyg.quote.insert'));
			
			_wysiwygQuoteButton.textContent = Language.get('wcf.global.button.submit');
			_wysiwygQuoteTitle.value = '';
			_wysiwygQuoteUrl.value = '';
		},
		
		/**
		 * Edits a <blockquote> element.
		 * 
		 * @param       {Event?}        event           event object
		 * @param       {Element=}      element         <blockquote> element
		 */
		edit: function(event, element) {
			if (event instanceof Event) {
				element = event.currentTarget;
			}
			
			if (_titleHeight === 0) {
				var styles = window.getComputedStyle(element, '::before');
				_titleHeight = DomUtil.styleAsInt(styles, 'height');
				
				styles = window.getComputedStyle(element);
				_quotePaddingTop = DomUtil.styleAsInt(styles, 'padding-top');
			}
			
			if (event instanceof Event) {
				// check if click occured within the ::before pseudo element
				var rect = DomUtil.offset(element);
				if ((event.clientY + window.scrollY) > (rect.top + _quotePaddingTop + _titleHeight)) {
					return;
				}
				
				event.preventDefault();
			}
			
			_element = element;
			
			UiDialog.open(this);
			UiDialog.setTitle(this, Language.get('wcf.wysiwyg.quote.edit'));
			
			// set values
			_wysiwygQuoteButton.textContent = Language.get('wcf.global.button.save');
			_wysiwygQuoteTitle.value = elData(_element, 'quote-title');
			_wysiwygQuoteUrl.value = elData(_element, 'quote-url');
		},
		
		/**
		 * Observes all <blockquote> elements for clicks on the editable headline
		 * @param       {Element}       editorElement           editor element
		 */
		observeAll: function(editorElement) {
			var elements = elByTag('BLOCKQUOTE', editorElement);
			for (var i = 0, length = elements.length; i < length; i++) {
				this._observe(elements[i], true);
			}
		},
		
		/**
		 * Observes clicks on a <blockquote> element and updates the headline.
		 * 
		 * @param       {Element}       element         <blockquote> element
		 * @param       {boolean}       updateHeader    update quote header
		 */
		_observe: function(element, updateHeader) {
			if (_callbackEdit === null) _callbackEdit = this.edit.bind(this);
			
			element.addEventListener(WCF_CLICK_EVENT, _callbackEdit);
			
			if (updateHeader) this._updateHeader(element);
		},
		
		/**
		 * Updates the headline of target <blockquote> element.
		 * 
		 * @param       {Element}       element         <blockquote> element
		 */
		_updateHeader: function(element) {
			var value = Language.get('wcf.wysiwyg.quote.header', {
				title: elData(element, 'quote-title') || elData(element, 'quote-url') || ''
			});
			
			if (elData(element, 'quote-header') !== value) {
				elData(element, 'quote-header', value);
			}
		},
		
		/**
		 * Adds or edits a <blockquote> element on dialog submit.
		 */
		_dialogSubmit: function() {
			if (_insertCallback !== null) {
				// insert a new <blockquote> element
				var element = elCreate('blockquote');
				element.className = 'quoteBox';
				element.id = 'quote-' + DomUtil.getUniqueId();
				
				_insertCallback(element);
				
				_element = elById(element.id);
				_element.id = '';
				
				this._observe(_element, false);
			}
			
			// edit an existing <blockquote> element
			elData(_element, 'quote-title', _wysiwygQuoteTitle.value.trim());
			elData(_element, 'quote-url', _wysiwygQuoteUrl.value.trim());
			
			this._updateHeader(_element);
			
			UiDialog.close(this);
		},
		
		_dialogOnSetup: function() {
			_wysiwygQuoteTitle = elById('wysiwygQuoteTitle');
			_wysiwygQuoteUrl = elById('wysiwygQuoteUrl');
			
			var _keyupCallback = (function(event) {
				if (event.which === 13) {
					this._dialogSubmit(event);
				}
			}).bind(this);
			
			_wysiwygQuoteTitle.addEventListener('keyup', _keyupCallback);
			_wysiwygQuoteUrl.addEventListener('keyup', _keyupCallback);
			
			_wysiwygQuoteButton = elById('wysiwygQuoteSubmit');
			_wysiwygQuoteButton.addEventListener(WCF_CLICK_EVENT, this._dialogSubmit.bind(this));
		},
		
		_dialogOnClose: function() {
			_element = null;
			_insertCallback = null;
		},
		
		_dialogSetup: function() {
			return {
				id: 'wysiwygQuoteDialog',
				options: {
					onClose: this._dialogOnClose.bind(this),
					onSetup: this._dialogOnSetup.bind(this)
				},
				source: '<dl>'
						+ '<dt><label for="wysiwygQuoteTitle">' + Language.get('wcf.wysiwyg.quote.title') + '</label></dt>'
						+ '<dd><input type="text" id="wysiwygQuoteTitle" class="long"></dd>'
					+ '</dl>'
					+ '<dl>'
						+ '<dt><label for="wysiwygQuoteUrl">' + Language.get('wcf.wysiwyg.quote.url') + '</label></dt>'
						+ '<dd><input type="text" id="wysiwygQuoteUrl" class="long"></dd>'
					+ '</dl>'
					+ '<div class="formSubmit">'
						+ '<button class="buttonPrimary" id="wysiwygQuoteSubmit"></button>'
					+ '</div>'
			};
		}
	};
});

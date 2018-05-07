/**
 * Manages quotes.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2018 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module      WoltLabSuite/Core/Ui/Redactor/Quote
 */
define(['Core', 'EventHandler', 'EventKey', 'Language', 'StringUtil', 'Dom/Util', 'Ui/Dialog', './Metacode', './PseudoHeader'], function (Core, EventHandler, EventKey, Language, StringUtil, DomUtil, UiDialog, UiRedactorMetacode, UiRedactorPseudoHeader) {
	"use strict";
	
	if (!COMPILER_TARGET_DEFAULT) {
		var Fake = function() {};
		Fake.prototype = {
			init: function() {},
			_insertQuote: function() {},
			_click: function() {},
			_observeLoad: function() {},
			_edit: function() {},
			_save: function() {},
			_setTitle: function() {},
			_delete: function() {},
			_dialogSetup: function() {},
			_dialogSubmit: function() {}
		};
		return Fake;
	}
	
	var _headerHeight = 0;
	
	/**
	 * @param       {Object}        editor  editor instance
	 * @param       {jQuery}        button  toolbar button
	 * @constructor
	 */
	function UiRedactorQuote(editor, button) { this.init(editor, button); }
	UiRedactorQuote.prototype = {
		/**
		 * Initializes the quote management.
		 * 
		 * @param       {Object}        editor  editor instance
		 * @param       {jQuery}        button  toolbar button
		 */
		init: function(editor, button) {
			this._quote = null;
			this._quotes = elByTag('woltlab-quote', editor.$editor[0]);
			this._editor = editor;
			this._elementId = this._editor.$element[0].id;
			
			EventHandler.add('com.woltlab.wcf.redactor2', 'observe_load_' + this._elementId, this._observeLoad.bind(this));
			
			this._editor.button.addCallback(button, this._click.bind(this));
			
			// static bind to ensure that removing works
			this._callbackEdit = this._edit.bind(this);
			
			// bind listeners on init
			this._observeLoad();
			
			// quote manager
			EventHandler.add('com.woltlab.wcf.redactor2', 'insertQuote_' + this._elementId, this._insertQuote.bind(this));
		},
		
		/**
		 * Inserts a quote.
		 * 
		 * @param       {Object}        data            quote data
		 * @protected
		 */
		_insertQuote: function (data) {
			if (this._editor.WoltLabSource.isActive()) {
				return;
			}
			
			EventHandler.fire('com.woltlab.wcf.redactor2', 'showEditor');
			
			var editor = this._editor.core.editor()[0];
			this._editor.selection.restore();
			
			this._editor.buffer.set();
			
			// caret must be within a `<p>`, if it is not: move it
			var block = this._editor.selection.block();
			if (block === false) {
				this._editor.focus.end();
				block = this._editor.selection.block();
			}
			
			while (block && block.parentNode !== editor) {
				block = block.parentNode;
			}
			
			var quote = elCreate('woltlab-quote');
			elData(quote, 'author', data.author);
			elData(quote, 'link', data.link);
			
			var content = data.content;
			if (data.isText) {
				content = StringUtil.escapeHTML(content);
				content = '<p>' + content + '</p>';
				content = content.replace(/\n\n/g, '</p><p>');
				content = content.replace(/\n/g, '<br>');
			}
			else {
				//noinspection JSUnresolvedFunction
				content = UiRedactorMetacode.convertFromHtml(this._editor.$element[0].id, content);
			}
			
			// bypass the editor as `insert.html()` doesn't like us
			quote.innerHTML = content;
			
			block.parentNode.insertBefore(quote, block.nextSibling);
			
			if (block.nodeName === 'P' && (block.innerHTML === '<br>' || block.innerHTML.replace(/\u200B/g, '') === '')) {
				block.parentNode.removeChild(block);
			}
			
			// avoid adjacent blocks that are not paragraphs
			var sibling = quote.previousElementSibling;
			if (sibling && sibling.nodeName !== 'P') {
				sibling = elCreate('p');
				sibling.textContent = '\u200B';
				quote.parentNode.insertBefore(sibling, quote);
			}
			
			this._editor.WoltLabCaret.paragraphAfterBlock(quote);
			
			this._editor.buffer.set();
		},
		
		/**
		 * Toggles the quote block on button click.
		 * 
		 * @protected
		 */
		_click: function() {
			this._editor.button.toggle({}, 'woltlab-quote', 'func', 'block.format');
			
			var quote = this._editor.selection.block();
			if (quote && quote.nodeName === 'WOLTLAB-QUOTE') {
				this._setTitle(quote);
				
				quote.addEventListener(WCF_CLICK_EVENT, this._callbackEdit);
				
				// work-around for Safari
				this._editor.caret.end(quote);
			}
		},
		
		/**
		 * Binds event listeners and sets quote title on both editor
		 * initialization and when switching back from code view.
		 * 
		 * @protected
		 */
		_observeLoad: function() {
			var quote;
			for (var i = 0, length = this._quotes.length; i < length; i++) {
				quote = this._quotes[i];
				
				quote.addEventListener('mousedown', this._callbackEdit);
				this._setTitle(quote);
			}
		},
		
		/**
		 * Opens the dialog overlay to edit the quote's properties.
		 * 
		 * @param       {Event}         event           event object
		 * @protected
		 */
		_edit: function(event) {
			var quote = event.currentTarget;
			
			if (_headerHeight === 0) {
				_headerHeight = UiRedactorPseudoHeader.getHeight(quote);
			}
			
			// check if the click hit the header
			var offset = DomUtil.offset(quote);
			if (event.pageY > offset.top && event.pageY < (offset.top + _headerHeight)) {
				event.preventDefault();
				
				this._editor.selection.save();
				this._quote = quote;
				
				UiDialog.open(this);
			}
		},
		
		/**
		 * Saves the changes to the quote's properties.
		 * 
		 * @protected
		 */
		_dialogSubmit: function() {
			var id = 'redactor-quote-' + this._elementId;
			var urlInput = elById(id + '-url');
			
			var url = urlInput.value.replace(/\u200B/g, '').trim();
			// simple test to check if it at least looks like it could be a valid url
			if (url.length && !/^https?:\/\/[^\/]+/.test(url)) {
				elInnerError(urlInput, Language.get('wcf.editor.quote.url.error.invalid'));
				return;
			}
			else {
				elInnerError(urlInput, false);
			}
			
			// set author
			elData(this._quote, 'author', elById(id + '-author').value);
			
			// set url
			elData(this._quote, 'link', url);
			
			this._setTitle(this._quote);
			this._editor.caret.after(this._quote);
			
			UiDialog.close(this);
		},
		
		/**
		 * Sets or updates the quote's header title.
		 * 
		 * @param       {Element}       quote     quote element
		 * @protected
		 */
		_setTitle: function(quote) {
			var title = Language.get('wcf.editor.quote.title', {
				author: elData(quote, 'author'),
				url: elData(quote, 'url')
			});
			
			if (elData(quote, 'title') !== title) {
				elData(quote, 'title', title);
			}
		},
		
		_delete: function (event) {
			event.preventDefault();
			
			var caretEnd = this._quote.nextElementSibling || this._quote.previousElementSibling;
			if (caretEnd === null && this._quote.parentNode !== this._editor.core.editor()[0]) {
				caretEnd = this._quote.parentNode;
			}
			
			if (caretEnd === null) {
				this._editor.code.set('');
				this._editor.focus.end();
			}
			else {
				elRemove(this._quote);
				this._editor.caret.end(caretEnd);
			}
			
			UiDialog.close(this);
		},
		
		_dialogSetup: function() {
			var id = 'redactor-quote-' + this._elementId,
			    idAuthor = id + '-author',
			    idButtonDelete = id + '-button-delete',
			    idButtonSave = id + '-button-save',
			    idUrl = id + '-url';
			
			return {
				id: id,
				options: {
					onClose: (function () {
						this._editor.selection.restore();
						
						UiDialog.destroy(this);
					}).bind(this),
					
					onSetup: (function() {
						elById(idButtonDelete).addEventListener(WCF_CLICK_EVENT, this._delete.bind(this));
					}).bind(this),
					
					onShow: (function() {
						elById(idAuthor).value = elData(this._quote, 'author');
						elById(idUrl).value = elData(this._quote, 'link');
					}).bind(this),
					
					title: Language.get('wcf.editor.quote.edit')
				},
				source: '<div class="section">'
					+ '<dl>'
						+ '<dt><label for="' + idAuthor + '">' + Language.get('wcf.editor.quote.author') + '</label></dt>'
						+ '<dd>'
							+ '<input type="text" id="' + idAuthor + '" class="long" data-dialog-submit-on-enter="true">'
						+ '</dd>'
					+ '</dl>'
					+ '<dl>'
						+ '<dt><label for="' + idUrl + '">' + Language.get('wcf.editor.quote.url') + '</label></dt>'
						+ '<dd>'
							+ '<input type="text" id="' + idUrl + '" class="long" data-dialog-submit-on-enter="true">'
							+ '<small>' + Language.get('wcf.editor.quote.url.description') + '</small>'
						+ '</dd>'
					+ '</dl>'
				+ '</div>'
				+ '<div class="formSubmit">'
					+ '<button id="' + idButtonSave + '" class="buttonPrimary" data-type="submit">' + Language.get('wcf.global.button.save') + '</button>'
					+ '<button id="' + idButtonDelete + '">' + Language.get('wcf.global.button.delete') + '</button>'
				+ '</div>'
			};
		}
	};
	
	return UiRedactorQuote;
});
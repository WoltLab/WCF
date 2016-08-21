/**
 * Manages quotes.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2016 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module      WoltLabSuite/Core/Ui/Redactor/Quote
 */
define(['Core', 'EventHandler', 'EventKey', 'Language', 'StringUtil', 'Dom/Util', 'Ui/Dialog'], function (Core, EventHandler, EventKey, Language, StringUtil, DomUtil, UiDialog) {
	"use strict";
	
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
			this._editor.buffer.set();
			
			// caret must be within a `<p>`, if it is not move it
			/** @type Node */
			var block = this._editor.selection.block();
			if (block === false) {
				this._editor.selection.restore();
				
				block = this._editor.selection.block();
			}
			
			var redactor = this._editor.core.editor()[0];
			while (block.parentNode && block.parentNode !== redactor) {
				block = block.parentNode;
			}
			
			this._editor.caret.after(block);
			
			var quoteId = Core.getUuid();
			this._editor.insert.html('<woltlab-quote id="' + quoteId + '"></woltlab-quote>');
			
			var quote = elById(quoteId);
			elData(quote, 'author', data.author);
			elData(quote, 'link', data.link);
			
			this._editor.selection.restore();
			var content = data.content;
			console.debug(data);
			if (data.isText) {
				content = StringUtil.escapeHTML(content);
				content = '<p>' + content + '</p>';
				content = content.replace(/\n\n/g, '</p><p>');
				content = content.replace(/\n/g, '<br>');
			}
			
			// bypass the editor as `insert.html()` doesn't like us
			quote.innerHTML = content;
			
			quote.removeAttribute('id');
			
			this._editor.caret.after(quote);
			
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
			}
		},
		
		/**
		 * Binds event listeners and sets quote title on both editor
		 * initialization and when switching back from code view.
		 * 
		 * @protected
		 */
		_observeLoad: function() {
			elBySelAll('woltlab-quote', this._editor.$editor[0], (function(quote) {
				quote.addEventListener(WCF_CLICK_EVENT, this._callbackEdit);
				this._setTitle(quote);
			}).bind(this));
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
				_headerHeight = ~~window.getComputedStyle(quote).paddingTop.replace(/px$/, '');
				
				var styles = window.getComputedStyle(quote, '::before');
				_headerHeight += ~~styles.paddingTop.replace(/px$/, '');
				_headerHeight += ~~styles.height.replace(/px$/, '');
				_headerHeight += ~~styles.paddingBottom.replace(/px$/, '');
			}
			
			// check if the click hit the header
			var offset = DomUtil.offset(quote);
			if (event.pageY > offset.top && event.pageY < (offset.top + _headerHeight)) {
				event.preventDefault();
				
				this._quote = quote;
				
				UiDialog.open(this);
			}
		},
		
		/**
		 * Saves the changes to the quote's properties.
		 * 
		 * @param       {Event}         event           event object
		 * @protected
		 */
		_save: function(event) {
			event.preventDefault();
			
			var id = 'redactor-quote-' + this._elementId;
			var urlInput = elById(id + '-url');
			var innerError = elBySel('.innerError', urlInput.parentNode);
			if (innerError !== null) elRemove(innerError);
			
			var url = urlInput.value.replace(/\u200B/g, '').trim();
			// simple test to check if it at least looks like it could be a valid url
			if (url.length && !/^https?:\/\/[^\/]+/.test(url)) {
				innerError = elCreate('small');
				innerError.className = 'innerError';
				innerError.textContent = Language.get('wcf.editor.quote.url.error.invalid');
				urlInput.parentNode.insertBefore(innerError, urlInput.nextElementSibling);
				return;
			}
			
			// set author
			elData(this._quote, 'author', elById(id + '-author').value);
			
			// set url
			elData(this._quote, 'url', url);
			
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
		
		_dialogSetup: function() {
			var id = 'redactor-quote-' + this._elementId,
			    idAuthor = id + '-author',
			    idButtonSave = id + '-button-save',
			    idUrl = id + '-url';
			
			return {
				id: id,
				options: {
					onSetup: (function() {
						elById(idButtonSave).addEventListener(WCF_CLICK_EVENT, this._save.bind(this));
					}).bind(this),
					
					onShow: (function() {
						elById(idAuthor).value = elData(this._quote, 'author');
						elById(idUrl).value = elData(this._quote, 'url');
					}).bind(this),
					
					title: Language.get('wcf.editor.quote.edit')
				},
				source: '<div class="section">'
					+ '<dl>'
						+ '<dt><label for="' + idAuthor + '">' + Language.get('wcf.editor.quote.author') + '</label></dt>'
						+ '<dd>'
							+ '<input type="text" id="' + idAuthor + '" class="long">'
						+ '</dd>'
					+ '</dl>'
					+ '<dl>'
						+ '<dt><label for="' + idUrl + '">' + Language.get('wcf.editor.quote.url') + '</label></dt>'
						+ '<dd>'
							+ '<input type="text" id="' + idUrl + '" class="long">'
							+ '<small>' + Language.get('wcf.editor.quote.url.description') + '</small>'
						+ '</dd>'
					+ '</dl>'
				+ '</div>'
				+ '<div class="formSubmit">'
					+ '<button id="' + idButtonSave + '" class="buttonPrimary">' + Language.get('wcf.global.button.save') + '</button>'
				+ '</div>'
			};
		}
	};
	
	return UiRedactorQuote;
});
/**
 * Manages quotes.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2016 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module      WoltLab/WCF/Ui/Redactor/Quote
 */
define(['EventHandler', 'EventKey', 'Language', 'StringUtil', 'Dom/Util', 'Ui/Dialog'], function (EventHandler, EventKey, Language, StringUtil, DomUtil, UiDialog) {
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
			this._blockquote = null;
			this._editor = editor;
			this._elementId = this._editor.$element[0].id;
			
			EventHandler.add('com.woltlab.wcf.redactor2', 'observe_load_' + this._elementId, this._observeLoad.bind(this));
			
			this._editor.button.addCallback(button, this._click.bind(this));
			
			// support for active button marking
			this._editor.opts.activeButtonsStates.blockquote = 'woltlabQuote';
			
			// static bind to ensure that removing works
			this._callbackEdit = this._edit.bind(this);
			
			// bind listeners on init
			this._observeLoad();
		},
		
		/**
		 * Toggles the quote block on button click.
		 * 
		 * @protected
		 */
		_click: function() {
			this._editor.button.toggle({}, 'blockquote', 'func', 'block.format');
			
			var blockquote = this._editor.selection.block();
			if (blockquote && blockquote.nodeName === 'BLOCKQUOTE') {
				this._setTitle(blockquote);
				
				blockquote.addEventListener(WCF_CLICK_EVENT, this._callbackEdit);
			}
		},
		
		/**
		 * Binds event listeners and sets quote title on both editor
		 * initialization and when switching back from code view.
		 * 
		 * @protected
		 */
		_observeLoad: function() {
			this._editor.events.stopDetectChanges();
			
			elBySelAll('blockquote', this._editor.$editor[0], (function(blockquote) {
				blockquote.addEventListener(WCF_CLICK_EVENT, this._callbackEdit);
				this._setTitle(blockquote);
			}).bind(this));
			
			this._editor.events.startDetectChanges();
		},
		
		/**
		 * Opens the dialog overlay to edit the quote's properties.
		 * 
		 * @param       {Event}         event           event object
		 * @protected
		 */
		_edit: function(event) {
			var blockquote = event.currentTarget;
			
			if (_headerHeight === 0) {
				_headerHeight = ~~window.getComputedStyle(blockquote).paddingTop.replace(/px$/, '');
				
				var styles = window.getComputedStyle(blockquote, '::before');
				_headerHeight += ~~styles.paddingTop.replace(/px$/, '');
				_headerHeight += ~~styles.height.replace(/px$/, '');
				_headerHeight += ~~styles.paddingBottom.replace(/px$/, '');
			}
			
			// check if the click hit the header
			var offset = DomUtil.offset(blockquote);
			if (event.pageY > offset.top && event.pageY < (offset.top + _headerHeight)) {
				event.preventDefault();
				
				this._blockquote = blockquote;
				
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
			
			this._editor.events.stopDetectChanges();
			
			var id = 'redactor-quote-' + this._elementId;
			
			['author', 'url'].forEach((function (attr) {
				elData(this._blockquote, attr, elById(id + '-' + attr).value);
			}).bind(this));
			
			this._setTitle(this._blockquote);
			this._editor.caret.after(this._blockquote);
			
			this._editor.events.startDetectChanges();
			
			UiDialog.close(this);
		},
		
		/**
		 * Sets or updates the quote's header title.
		 * 
		 * @param       {Element}       blockquote     quote element
		 * @protected
		 */
		_setTitle: function(blockquote) {
			var title = Language.get('wcf.editor.quote.title', {
				author: elData(blockquote, 'author'),
				url: elData(blockquote, 'url')
			});
			
			if (elData(blockquote, 'title') !== title) {
				elData(blockquote, 'title', title);
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
						elById(idAuthor).value = elData(this._blockquote, 'author');
						elById(idUrl).value = elData(this._blockquote, 'url');
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
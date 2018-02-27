/**
 * Manages code blocks.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2018 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module      WoltLabSuite/Core/Ui/Redactor/Code
 */
define(['EventHandler', 'EventKey', 'Language', 'StringUtil', 'Dom/Util', 'Ui/Dialog', './PseudoHeader'], function (EventHandler, EventKey, Language, StringUtil, DomUtil, UiDialog, UiRedactorPseudoHeader) {
	"use strict";
	
	if (!COMPILER_TARGET_DEFAULT) {
		var Fake = function() {};
		Fake.prototype = {
			init: function() {},
			_bbcodeCode: function() {},
			_observeLoad: function() {},
			_edit: function() {},
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
	 * @constructor
	 */
	function UiRedactorCode(editor) { this.init(editor); }
	UiRedactorCode.prototype = {
		/**
		 * Initializes the source code management.
		 * 
		 * @param       {Object}        editor  editor instance
		 */
		init: function(editor) {
			this._editor = editor;
			this._elementId = this._editor.$element[0].id;
			this._pre = null;
			
			EventHandler.add('com.woltlab.wcf.redactor2', 'bbcode_code_' + this._elementId, this._bbcodeCode.bind(this));
			EventHandler.add('com.woltlab.wcf.redactor2', 'observe_load_' + this._elementId, this._observeLoad.bind(this));
			
			// support for active button marking
			this._editor.opts.activeButtonsStates.pre = 'code';
			
			// static bind to ensure that removing works
			this._callbackEdit = this._edit.bind(this);
			
			// bind listeners on init
			this._observeLoad();
		},
		
		/**
		 * Intercepts the insertion of `[code]` tags and uses a native `<pre>` instead.
		 * 
		 * @param       {Object}        data    event data
		 * @protected
		 */
		_bbcodeCode: function(data) {
			data.cancel = true;
			
			var pre = this._editor.selection.block();
			if (pre && pre.nodeName === 'PRE' && pre.classList.contains('woltlabHtml')) {
				return;
			}
			
			this._editor.button.toggle({}, 'pre', 'func', 'block.format');
			
			pre = this._editor.selection.block();
			if (pre && pre.nodeName === 'PRE' && !pre.classList.contains('woltlabHtml')) {
				if (pre.childElementCount === 1 && pre.children[0].nodeName === 'BR') {
					// drop superfluous linebreak
					pre.removeChild(pre.children[0]);
				}
				
				this._setTitle(pre);
				
				pre.addEventListener(WCF_CLICK_EVENT, this._callbackEdit);
				
				// work-around for Safari
				this._editor.caret.end(pre);
			}
		},
		
		/**
		 * Binds event listeners and sets quote title on both editor
		 * initialization and when switching back from code view.
		 * 
		 * @protected
		 */
		_observeLoad: function() {
			elBySelAll('pre:not(.woltlabHtml)', this._editor.$editor[0], (function(pre) {
				pre.addEventListener('mousedown', this._callbackEdit);
				this._setTitle(pre);
			}).bind(this));
		},
		
		/**
		 * Opens the dialog overlay to edit the code's properties.
		 * 
		 * @param       {Event}         event           event object
		 * @protected
		 */
		_edit: function(event) {
			var pre = event.currentTarget;
			
			if (_headerHeight === 0) {
				_headerHeight = UiRedactorPseudoHeader.getHeight(pre);
			}
			
			// check if the click hit the header
			var offset = DomUtil.offset(pre);
			if (event.pageY > offset.top && event.pageY < (offset.top + _headerHeight)) {
				event.preventDefault();
				
				this._editor.selection.save();
				this._pre = pre;
				
				UiDialog.open(this);
			}
		},
		
		/**
		 * Saves the changes to the code's properties.
		 * 
		 * @protected
		 */
		_dialogSubmit: function() {
			var id = 'redactor-code-' + this._elementId;
			
			['file', 'highlighter', 'line'].forEach((function (attr) {
				elData(this._pre, attr, elById(id + '-' + attr).value);
			}).bind(this));
			
			this._setTitle(this._pre);
			this._editor.caret.after(this._pre);
			
			UiDialog.close(this);
		},
		
		/**
		 * Sets or updates the code's header title.
		 * 
		 * @param       {Element}       pre     code element
		 * @protected
		 */
		_setTitle: function(pre) {
			var file = elData(pre, 'file'),
			    highlighter = elData(pre, 'highlighter');
			
			//noinspection JSUnresolvedVariable
			highlighter = (this._editor.opts.woltlab.highlighters.hasOwnProperty(highlighter)) ? this._editor.opts.woltlab.highlighters[highlighter] : '';
			
			var title = Language.get('wcf.editor.code.title', {
				file: file,
				highlighter: highlighter
			});
			
			if (elData(pre, 'title') !== title) {
				elData(pre, 'title', title);
			}
		},
		
		_delete: function (event) {
			event.preventDefault();
			
			var caretEnd = this._pre.nextElementSibling || this._pre.previousElementSibling;
			if (caretEnd === null && this._pre.parentNode !== this._editor.core.editor()[0]) {
				caretEnd = this._pre.parentNode;
			}
			
			if (caretEnd === null) {
				this._editor.code.set('');
				this._editor.focus.end();
			}
			else {
				elRemove(this._pre);
				this._editor.caret.end(caretEnd);
			}
			
			UiDialog.close(this);
		},
		
		_dialogSetup: function() {
			var id = 'redactor-code-' + this._elementId,
			    idButtonDelete = id + '-button-delete',
			    idButtonSave = id + '-button-save',
			    idFile = id + '-file',
			    idHighlighter = id + '-highlighter',
			    idLine = id + '-line';
			
			return {
				id: id,
				options: {
					onClose: (function () {
						this._editor.selection.restore();
						
						UiDialog.destroy(this);
					}).bind(this),
					
					onSetup: (function() {
						elById(idButtonDelete).addEventListener(WCF_CLICK_EVENT, this._delete.bind(this));
						
						// set highlighters
						var highlighters = '<option value="">' + Language.get('wcf.editor.code.highlighter.detect') + '</option>';
						
						var values = [];
						//noinspection JSUnresolvedVariable
						for (var highlighter in this._editor.opts.woltlab.highlighters) {
							//noinspection JSUnresolvedVariable
							if (this._editor.opts.woltlab.highlighters.hasOwnProperty(highlighter)) {
								//noinspection JSUnresolvedVariable
								values.push([highlighter, this._editor.opts.woltlab.highlighters[highlighter]]);
							}
						}
						
						// sort by label
						values.sort(function(a, b) {
							if (a[1] < b[1]) {
								return  -1;
							}
							else if (a[1] > b[1]) {
								return 1;
							}
							
							return 0;
						});
						
						values.forEach((function(value) {
							highlighters += '<option value="' + value[0] + '">' + StringUtil.escapeHTML(value[1]) + '</option>';
						}).bind(this));
						
						elById(idHighlighter).innerHTML = highlighters;
					}).bind(this),
					
					onShow: (function() {
						elById(idHighlighter).value = elData(this._pre, 'highlighter');
						var line = elData(this._pre, 'line');
						elById(idLine).value = (line === '') ? 1 : ~~line;
						elById(idFile).value = elData(this._pre, 'file');
					}).bind(this),
					
					title: Language.get('wcf.editor.code.edit')
				},
				source: '<div class="section">'
					+ '<dl>'
						+ '<dt><label for="' + idHighlighter + '">' + Language.get('wcf.editor.code.highlighter') + '</label></dt>'
						+ '<dd>'
							+ '<select id="' + idHighlighter + '"></select>'
							+ '<small>' + Language.get('wcf.editor.code.highlighter.description') + '</small>'
						+ '</dd>'
					+ '</dl>'
					+ '<dl>'
						+ '<dt><label for="' + idLine + '">' + Language.get('wcf.editor.code.line') + '</label></dt>'
						+ '<dd>'
							+ '<input type="number" id="' + idLine + '" min="0" value="1" class="long" data-dialog-submit-on-enter="true">'
							+ '<small>' + Language.get('wcf.editor.code.line.description') + '</small>'
						+ '</dd>'
					+ '</dl>'
					+ '<dl>'
						+ '<dt><label for="' + idFile + '">' + Language.get('wcf.editor.code.file') + '</label></dt>'
						+ '<dd>'
							+ '<input type="text" id="' + idFile + '" class="long" data-dialog-submit-on-enter="true">'
							+ '<small>' + Language.get('wcf.editor.code.file.description') + '</small>'
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
	
	return UiRedactorCode;
});
/**
 * Manages html code blocks.
 * 
 * @author      Alexander Ebert
 * @copyright   2001-2018 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module      WoltLabSuite/Core/Ui/Redactor/Html
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
			_save: function() {},
			_setTitle: function() {},
			_delete: function() {},
			_dialogSetup: function() {}
		};
		return Fake;
	}
	
	var _headerHeight = 0;
	
	/**
	 * @param       {Object}        editor  editor instance
	 * @constructor
	 */
	function UiRedactorHtml(editor) { this.init(editor); }
	UiRedactorHtml.prototype = {
		/**
		 * Initializes the source code management.
		 *
		 * @param       {Object}        editor  editor instance
		 */
		init: function(editor) {
			this._editor = editor;
			this._elementId = this._editor.$element[0].id;
			this._pre = null;
			
			EventHandler.add('com.woltlab.wcf.redactor2', 'bbcode_woltlabHtml_' + this._elementId, this._bbcodeCode.bind(this));
			EventHandler.add('com.woltlab.wcf.redactor2', 'observe_load_' + this._elementId, this._observeLoad.bind(this));
			
			// support for active button marking
			this._editor.opts.activeButtonsStates['woltlab-html'] = 'woltlabHtml';
			
			// static bind to ensure that removing works
			this._callbackEdit = this._edit.bind(this);
			
			// bind listeners on init
			this._observeLoad();
		},
		
		/**
		 * Intercepts the insertion of `[woltlabHtml]` tags and uses a native `<pre>` instead.
		 *
		 * @param       {Object}        data    event data
		 * @protected
		 */
		_bbcodeCode: function(data) {
			data.cancel = true;
			
			var pre = this._editor.selection.block();
			if (pre && pre.nodeName === 'PRE' && !pre.classList.contains('woltlabHtml')) {
				return;
			}
			
			this._editor.button.toggle({}, 'pre', 'func', 'block.format');
			
			pre = this._editor.selection.block();
			if (pre && pre.nodeName === 'PRE') {
				pre.classList.add('woltlabHtml');
				
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
			elBySelAll('pre.woltlabHtml', this._editor.$editor[0], (function(pre) {
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
				
				console.warn("should edit");
			}
		},
		
		/**
		 * Sets or updates the code's header title.
		 *
		 * @param       {Element}       pre     code element
		 * @protected
		 */
		_setTitle: function(pre) {
			['title', 'description'].forEach(function(title) {
				var phrase = Language.get('wcf.editor.html.' + title);
				
				if (elData(pre, title) !== phrase) {
					elData(pre, title, phrase);
				}
			});
		},
		
		_delete: function (event) {
			console.warn("should delete");
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
		}
	};
	
	return UiRedactorHtml;
});
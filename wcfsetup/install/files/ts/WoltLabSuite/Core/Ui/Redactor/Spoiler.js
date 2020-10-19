/**
 * Manages spoilers.
 *
 * @author      Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module      WoltLabSuite/Core/Ui/Redactor/Spoiler
 */
define(['EventHandler', 'EventKey', 'Language', 'StringUtil', 'Dom/Util', 'Ui/Dialog', './PseudoHeader'], function (EventHandler, EventKey, Language, StringUtil, DomUtil, UiDialog, UiRedactorPseudoHeader) {
	"use strict";
	
	if (!COMPILER_TARGET_DEFAULT) {
		var Fake = function() {};
		Fake.prototype = {
			init: function() {},
			_bbcodeSpoiler: function() {},
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
	function UiRedactorSpoiler(editor) { this.init(editor); }
	UiRedactorSpoiler.prototype = {
		/**
		 * Initializes the spoiler management.
		 * 
		 * @param       {Object}        editor  editor instance
		 */
		init: function(editor) {
			this._editor = editor;
			this._elementId = this._editor.$element[0].id;
			this._spoiler = null;
			
			EventHandler.add('com.woltlab.wcf.redactor2', 'bbcode_spoiler_' + this._elementId, this._bbcodeSpoiler.bind(this));
			EventHandler.add('com.woltlab.wcf.redactor2', 'observe_load_' + this._elementId, this._observeLoad.bind(this));
			
			// static bind to ensure that removing works
			this._callbackEdit = this._edit.bind(this);
			
			// bind listeners on init
			this._observeLoad();
		},
		
		/**
		 * Intercepts the insertion of `[spoiler]` tags and uses
		 * the custom `<woltlab-spoiler>` element instead.
		 * 
		 * @param       {Object}        data    event data
		 * @protected
		 */
		_bbcodeSpoiler: function(data) {
			data.cancel = true;
			
			this._editor.button.toggle({}, 'woltlab-spoiler', 'func', 'block.format');
			
			var spoiler = this._editor.selection.block();
			if (spoiler) {
				// iOS Safari might set the caret inside the spoiler.
				if (spoiler.nodeName === 'P') {
					spoiler = spoiler.parentNode;
				}

				if (spoiler.nodeName === 'WOLTLAB-SPOILER') {
					this._setTitle(spoiler);

					spoiler.addEventListener(WCF_CLICK_EVENT, this._callbackEdit);

					// work-around for Safari
					this._editor.caret.end(spoiler);
				}
			}
		},
		
		/**
		 * Binds event listeners and sets quote title on both editor
		 * initialization and when switching back from code view.
		 * 
		 * @protected
		 */
		_observeLoad: function() {
			elBySelAll('woltlab-spoiler', this._editor.$editor[0], (function(spoiler) {
				spoiler.addEventListener('mousedown', this._callbackEdit);
				this._setTitle(spoiler);
			}).bind(this));
		},
		
		/**
		 * Opens the dialog overlay to edit the spoiler's properties.
		 * 
		 * @param       {Event}         event           event object
		 * @protected
		 */
		_edit: function(event) {
			var spoiler = event.currentTarget;
			
			if (_headerHeight === 0) {
				_headerHeight = UiRedactorPseudoHeader.getHeight(spoiler);
			}
			
			// check if the click hit the header
			var offset = DomUtil.offset(spoiler);
			if (event.pageY > offset.top && event.pageY < (offset.top + _headerHeight)) {
				event.preventDefault();
				
				this._editor.selection.save();
				this._spoiler = spoiler;
				
				UiDialog.open(this);
			}
		},
		
		/**
		 * Saves the changes to the spoiler's properties.
		 * 
		 * @protected
		 */
		_dialogSubmit: function() {
			elData(this._spoiler, 'label', elById('redactor-spoiler-' + this._elementId + '-label').value);
			
			this._setTitle(this._spoiler);
			this._editor.caret.after(this._spoiler);
			
			UiDialog.close(this);
		},
		
		/**
		 * Sets or updates the spoiler's header title.
		 * 
		 * @param       {Element}       spoiler     spoiler element
		 * @protected
		 */
		_setTitle: function(spoiler) {
			var title = Language.get('wcf.editor.spoiler.title', { label: elData(spoiler, 'label') });
			
			if (elData(spoiler, 'title') !== title) {
				elData(spoiler, 'title', title);
			}
		},
		
		_delete: function (event) {
			event.preventDefault();
			
			var caretEnd = this._spoiler.nextElementSibling || this._spoiler.previousElementSibling;
			if (caretEnd === null && this._spoiler.parentNode !== this._editor.core.editor()[0]) {
				caretEnd = this._spoiler.parentNode;
			}
			
			if (caretEnd === null) {
				this._editor.code.set('');
				this._editor.focus.end();
			}
			else {
				elRemove(this._spoiler);
				this._editor.caret.end(caretEnd);
			}
			
			UiDialog.close(this);
		},
		
		_dialogSetup: function() {
			var id = 'redactor-spoiler-' + this._elementId,
			    idButtonDelete = id + '-button-delete',
			    idButtonSave = id + '-button-save',
			    idLabel = id + '-label';
			
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
						elById(idLabel).value = elData(this._spoiler, 'label');
					}).bind(this),
					
					title: Language.get('wcf.editor.spoiler.edit')
				},
				source: '<div class="section">'
					+ '<dl>'
						+ '<dt><label for="' + idLabel + '">' + Language.get('wcf.editor.spoiler.label') + '</label></dt>'
						+ '<dd>'
							+ '<input type="text" id="' + idLabel + '" class="long" data-dialog-submit-on-enter="true">'
							+ '<small>' + Language.get('wcf.editor.spoiler.label.description') + '</small>'
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
	
	return UiRedactorSpoiler;
});

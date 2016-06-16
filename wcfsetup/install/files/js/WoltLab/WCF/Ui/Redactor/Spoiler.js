/**
 * Manages spoilers.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2016 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module      WoltLab/WCF/Ui/Redactor/Spoiler
 */
define(['EventHandler', 'EventKey', 'Language', 'StringUtil', 'Dom/Util', 'Ui/Dialog'], function (EventHandler, EventKey, Language, StringUtil, DomUtil, UiDialog) {
	"use strict";
	
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
			
			// register custom block element
			this._editor.WoltLabBlock.register('woltlab-spoiler');
			this._editor.block.tags.push('woltlab-spoiler');
			
			// support for active button marking
			this._editor.opts.activeButtonsStates['woltlab-spoiler'] = 'woltlabSpoiler';
			
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
			if (spoiler && spoiler.nodeName === 'WOLTLAB-SPOILER') {
				this._setTitle(spoiler);
				
				spoiler.addEventListener(WCF_CLICK_EVENT, this._callbackEdit);
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
				spoiler.addEventListener(WCF_CLICK_EVENT, this._callbackEdit);
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
				_headerHeight = ~~window.getComputedStyle(spoiler).paddingTop.replace(/px$/, '');
				
				var styles = window.getComputedStyle(spoiler, '::before');
				_headerHeight += ~~styles.paddingTop.replace(/px$/, '');
				_headerHeight += ~~styles.height.replace(/px$/, '');
				_headerHeight += ~~styles.paddingBottom.replace(/px$/, '');
			}
			
			// check if the click hit the header
			var offset = DomUtil.offset(spoiler);
			if (event.pageY > offset.top && event.pageY < (offset.top + _headerHeight)) {
				event.preventDefault();
				
				this._spoiler = spoiler;
				
				UiDialog.open(this);
			}
		},
		
		/**
		 * Saves the changes to the spoiler's properties.
		 * 
		 * @param       {Event}         event           event object
		 * @protected
		 */
		_save: function(event) {
			event.preventDefault();
			
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
		
		_dialogSetup: function() {
			var id = 'redactor-spoiler-' + this._elementId,
			    idButtonSave = id + '-button-save',
			    idLabel = id + '-label';
			
			return {
				id: id,
				options: {
					onSetup: (function() {
						elById(idButtonSave).addEventListener(WCF_CLICK_EVENT, this._save.bind(this));
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
							+ '<input type="text" id="' + idLabel + '" class="long">'
							+ '<small>' + Language.get('wcf.editor.spoiler.label.description') + '</small>'
						+ '</dd>'
					+ '</dl>'
				+ '</div>'
				+ '<div class="formSubmit">'
					+ '<button id="' + idButtonSave + '" class="buttonPrimary">' + Language.get('wcf.global.button.save') + '</button>'
				+ '</div>'
			};
		}
	};
	
	return UiRedactorSpoiler;
});
/**
 * Handles editing media files via dialog.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Media/Editor
 */
define(
	[
		'Ajax',
		'Core',
		'Dictionary',
		'Dom/ChangeListener',
		'Dom/Traverse',
		'Dom/Util',
		'Language',
		'Ui/Dialog',
		'Ui/Notification',
		'WoltLabSuite/Core/Language/Chooser',
		'WoltLabSuite/Core/Language/Input',
		'EventKey',
		'WoltLabSuite/Core/Media/Replace'
	],
	function(
		Ajax,
		Core,
		Dictionary,
		DomChangeListener,
		DomTraverse,
		DomUtil,
		Language,
		UiDialog,
		UiNotification,
		LanguageChooser,
		LanguageInput,
		EventKey,
		MediaReplace
	)
{
	"use strict";
	
	if (!COMPILER_TARGET_DEFAULT) {
		var Fake = function() {};
		Fake.prototype = {
			_ajaxSetup: function() {},
			_ajaxSuccess: function() {},
			_close: function() {},
			_keyPress: function() {},
			_saveData: function() {},
			_updateLanguageFields: function() {},
			edit: function() {}
		};
		return Fake;
	}
	
	/**
	 * @constructor
	 */
	function MediaEditor(callbackObject) {
		this._callbackObject = callbackObject || {};
		
		if (this._callbackObject._editorClose && typeof this._callbackObject._editorClose !== 'function') {
			throw new TypeError("Callback object has no function '_editorClose'.");
		}
		if (this._callbackObject._editorSuccess && typeof this._callbackObject._editorSuccess !== 'function') {
			throw new TypeError("Callback object has no function '_editorSuccess'.");
		}
		
		this._media = null;
		this._availableLanguageCount = 1;
		this._categoryIds = [];
		this._oldCategoryId = 0;
		
		this._dialogs = new Dictionary();
	}
	MediaEditor.prototype = {
		/**
		 * Returns the data for Ajax to setup the Ajax/Request object.
		 * 
		 * @return	{object}	setup data for Ajax/Request object
		 */
		_ajaxSetup: function() {
			return {
				data: {
					actionName: 'update',
					className: 'wcf\\data\\media\\MediaAction'
				}
			};
		},
		
		/**
		 * Handles successful AJAX requests.
		 * 
		 * @param	{object}	data	response data
		 */
		_ajaxSuccess: function(data) {
			UiNotification.show();
			
			if (this._callbackObject._editorSuccess) {
				this._callbackObject._editorSuccess(this._media, this._oldCategoryId);
				this._oldCategoryId = 0;
			}
			
			UiDialog.close('mediaEditor_' + this._media.mediaID);
			
			this._media = null;
		},
		
		/**
		 * Is called if an editor is manually closed by the user.
		 */
		_close: function() {
			this._media = null;
			
			if (this._callbackObject._editorClose) {
				this._callbackObject._editorClose();
			}
		},
		
		/**
		 * Initializes the editor dialog.
		 * 
		 * @param       {HTMLElement}           content
		 * @param       {object}                data
		 * @since       5.3
		 */
		_initEditor: function(content, data) {
			this._availableLanguageCount = ~~data.returnValues.availableLanguageCount;
			this._categoryIds = data.returnValues.categoryIDs.map(function(number) {
				return ~~number;
			});
			
			var didLoadMediaData = false;
			if (data.returnValues.mediaData) {
				this._media = data.returnValues.mediaData;
				
				didLoadMediaData = true;
			}
			
			// make sure that the language chooser is initialized first
			setTimeout(function() {
				if (this._availableLanguageCount > 1) {
					LanguageChooser.setLanguageId('mediaEditor_' + this._media.mediaID + '_languageID', this._media.languageID || LANGUAGE_ID);
				}
				
				if (this._categoryIds.length) {
					elBySel('select[name=categoryID]', content).value = ~~this._media.categoryID;
				}
				
				var title = elBySel('input[name=title]', content);
				var altText = elBySel('input[name=altText]', content);
				var caption = elBySel('textarea[name=caption]', content);
				
				if (this._availableLanguageCount > 1 && this._media.isMultilingual) {
					if (elById('altText_' + this._media.mediaID)) LanguageInput.setValues('altText_' + this._media.mediaID, Dictionary.fromObject(this._media.altText || { }));
					if (elById('caption_' + this._media.mediaID)) LanguageInput.setValues('caption_' + this._media.mediaID, Dictionary.fromObject(this._media.caption || { }));
					LanguageInput.setValues('title_' + this._media.mediaID, Dictionary.fromObject(this._media.title || { }));
				}
				else {
					title.value = this._media.title ? this._media.title[this._media.languageID || LANGUAGE_ID] : '';
					if (altText) altText.value = this._media.altText ? this._media.altText[this._media.languageID || LANGUAGE_ID] : '';
					if (caption) caption.value = this._media.caption ? this._media.caption[this._media.languageID || LANGUAGE_ID] : '';
				}
				
				if (this._availableLanguageCount > 1) {
					var isMultilingual = elBySel('input[name=isMultilingual]', content);
					isMultilingual.addEventListener('change', this._updateLanguageFields.bind(this));
					
					this._updateLanguageFields(null, isMultilingual);
				}
				
				var keyPress = this._keyPress.bind(this);
				if (altText) altText.addEventListener('keypress', keyPress);
				title.addEventListener('keypress', keyPress);
				
				elBySel('button[data-type=submit]', content).addEventListener(WCF_CLICK_EVENT, this._saveData.bind(this));
				
				// remove focus from input elements and scroll dialog to top
				document.activeElement.blur();
				elById('mediaEditor_' + this._media.mediaID).parentNode.scrollTop = 0;
				
				// Initialize button to replace media file.
				var uploadButton = elByClass('mediaManagerMediaReplaceButton', content)[0];
				var target = elByClass('mediaThumbnail', content)[0];
				if (!target) {
					target = elCreate('div');
					content.appendChild(target);
				}
				new MediaReplace(
					this._media.mediaID,
					DomUtil.identify(uploadButton),
					// Pass an anonymous element for non-images which is required internally
					// but not needed in this case.
					DomUtil.identify(target),
					{
						mediaEditor: this
					}
				);
				
				DomChangeListener.trigger();
			}.bind(this), 200);
		},
		
		/**
		 * Handles the `[ENTER]` key to submit the form.
		 * 
		 * @param	{object}	event		event object
		 */
		_keyPress: function(event) {
			if (EventKey.Enter(event)) {
				event.preventDefault();
				
				this._saveData();
			}
		},
		
		/**
		 * Saves the data of the currently edited media.
		 */
		_saveData: function() {
			var content = UiDialog.getDialog('mediaEditor_' + this._media.mediaID).content;
			
			var categoryId = elBySel('select[name=categoryID]', content);
			var altText = elBySel('input[name=altText]', content);
			var caption = elBySel('textarea[name=caption]', content);
			var captionEnableHtml = elBySel('input[name=captionEnableHtml]', content);
			var title = elBySel('input[name=title]', content);
			
			var hasError = false;
			var altTextError = (altText ? DomTraverse.childByClass(altText.parentNode.parentNode, 'innerError') : false);
			var captionError = (caption ? DomTraverse.childByClass(caption.parentNode.parentNode, 'innerError') : false);
			var titleError = DomTraverse.childByClass(title.parentNode.parentNode, 'innerError');
			
			// category
			this._oldCategoryId = this._media.categoryID;
			if (this._categoryIds.length) {
				this._media.categoryID = ~~categoryId.value;
				
				// if the selected category id not valid (manipulated DOM), ignore
				if (this._categoryIds.indexOf(this._media.categoryID) === -1) {
					this._media.categoryID = 0;
				}
			}
			
			// language and multilingualism
			if (this._availableLanguageCount > 1) {
				this._media.isMultilingual = ~~elBySel('input[name=isMultilingual]', content).checked;
				this._media.languageID = this._media.isMultilingual ? null : LanguageChooser.getLanguageId('mediaEditor_' + this._media.mediaID + '_languageID');
			}
			else {
				this._media.languageID = LANGUAGE_ID;
			}
			
			// altText, caption and title
			this._media.altText = {};
			this._media.caption = {};
			this._media.title = {};
			if (this._availableLanguageCount > 1 && this._media.isMultilingual) {
				if (elById('altText_' + this._media.mediaID) && !LanguageInput.validate('altText_' + this._media.mediaID, true)) {
					hasError = true;
					if (!altTextError) {
						var error = elCreate('small');
						error.className = 'innerError';
						error.textContent = Language.get('wcf.global.form.error.multilingual');
						altText.parentNode.parentNode.appendChild(error);
					}
				}
				if (elById('caption_' + this._media.mediaID) && !LanguageInput.validate('caption_' + this._media.mediaID, true)) {
					hasError = true;
					if (!captionError) {
						var error = elCreate('small');
						error.className = 'innerError';
						error.textContent = Language.get('wcf.global.form.error.multilingual');
						caption.parentNode.parentNode.appendChild(error);
					}
				}
				if (!LanguageInput.validate('title_' + this._media.mediaID, true)) {
					hasError = true;
					if (!titleError) {
						var error = elCreate('small');
						error.className = 'innerError';
						error.textContent = Language.get('wcf.global.form.error.multilingual');
						title.parentNode.parentNode.appendChild(error);
					}
				}
				
				this._media.altText = (elById('altText_' + this._media.mediaID) ? LanguageInput.getValues('altText_' + this._media.mediaID).toObject() : '');
				this._media.caption = (elById('caption_' + this._media.mediaID) ? LanguageInput.getValues('caption_' + this._media.mediaID).toObject() : '');
				this._media.title = LanguageInput.getValues('title_' + this._media.mediaID).toObject();
			}
			else {
				this._media.altText[this._media.languageID] = (altText ? altText.value : '');
				this._media.caption[this._media.languageID] = (caption ? caption.value : '');
				this._media.title[this._media.languageID] = title.value;
			}
			
			// captionEnableHtml
			if (captionEnableHtml) this._media.captionEnableHtml = ~~captionEnableHtml.checked;
			
			var aclValues = {
				allowAll: ~~elById('mediaEditor_' + this._media.mediaID + '_aclAllowAll').checked,
				group: [],
				user: []
			};
			
			var aclGroups = elBySelAll('input[name="aclValues[group][]"]', content);
			for (var i = 0, length = aclGroups.length; i < length; i++) {
				aclValues.group.push(~~aclGroups[i].value);
			}
			
			var aclUsers = elBySelAll('input[name="aclValues[user][]"]', content);
			for (var i = 0, length = aclUsers.length; i < length; i++) {
				aclValues.user.push(~~aclUsers[i].value);
			}
			
			if (!hasError) {
				if (altTextError) elRemove(altTextError);
				if (captionError) elRemove(captionError);
				if (titleError) elRemove(titleError);
				
				Ajax.api(this, {
					actionName: 'update',
					objectIDs: [ this._media.mediaID ],
					parameters: {
						aclValues: aclValues,
						altText: this._media.altText,
						caption: this._media.caption,
						data: {
							captionEnableHtml: this._media.captionEnableHtml,
							categoryID: this._media.categoryID,
							isMultilingual: this._media.isMultilingual,
							languageID: this._media.languageID
						},
						title: this._media.title
					}
				});
			}
		},
		
		/**
		 * Updates language-related input fields depending on whether multilingualism
		 * is enabled.
		 */
		_updateLanguageFields: function(event, element) {
			if (event) element = event.currentTarget;
			
			var languageChooserContainer = elById('mediaEditor_' + this._media.mediaID + '_languageIDContainer').parentNode;
			
			if (element.checked) {
				LanguageInput.enable('title_' + this._media.mediaID);
				if (elById('caption_' + this._media.mediaID)) LanguageInput.enable('caption_' + this._media.mediaID);
				if (elById('altText_' + this._media.mediaID)) LanguageInput.enable('altText_' + this._media.mediaID);
				
				elHide(languageChooserContainer);
			}
			else {
				LanguageInput.disable('title_' + this._media.mediaID);
				if (elById('caption_' + this._media.mediaID)) LanguageInput.disable('caption_' + this._media.mediaID);
				if (elById('altText_' + this._media.mediaID)) LanguageInput.disable('altText_' + this._media.mediaID);
				
				elShow(languageChooserContainer);
			}
		},
		
		/**
		 * Edits the media with the given data.
		 * 
		 * @param	{object|integer}	media		data of the edited media or media id for which the data will be loaded
		 */
		edit: function(media) {
			if (typeof media !== 'object') {
				media = {
					mediaID: ~~media
				};
			}
			
			if (this._media !== null) {
				throw new Error("Cannot edit media with id '" + media.mediaID + "' while editing media with id '" + this._media.mediaID + "'");
			}
			
			this._media = media;
			
			if (!this._dialogs.has('mediaEditor_' + media.mediaID)) {
				this._dialogs.set('mediaEditor_' + media.mediaID, {
					_dialogSetup: function() {
						return {
							id: 'mediaEditor_' + media.mediaID,
							options: {
								backdropCloseOnClick: false,
								onClose: this._close.bind(this),
								title: Language.get('wcf.media.edit')
							},
							source: {
								after: this._initEditor.bind(this),
								data: {
									actionName: 'getEditorDialog',
									className: 'wcf\\data\\media\\MediaAction',
									objectIDs: [media.mediaID]
								}
							}
						};
					}.bind(this)
				});
			}
			
			UiDialog.open(this._dialogs.get('mediaEditor_' + media.mediaID));
		},
		
		/**
		 * Updates the data of the currently edited media file.
		 * 
		 * @param       {object}        data
		 * @since       5.3
		 */
		updateData: function(data) {
			if (this._callbackObject._editorSuccess) {
				this._callbackObject._editorSuccess(data);
			}
		}
	};
	
	return MediaEditor;
});

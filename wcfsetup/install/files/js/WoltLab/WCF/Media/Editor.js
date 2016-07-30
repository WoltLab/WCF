/**
 * Handles editing media files via dialog.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Media/Editor
 */
define(
	[
		'Ajax',                         'Core',                       'Dictionary',          'Dom/ChangeListener',
		'Dom/Traverse',                 'Language',                   'Ui/Dialog',           'Ui/Notification',
		'WoltLab/WCF/Language/Chooser', 'WoltLab/WCF/Language/Input', 'WoltLab/WCF/File/Util'
	],
	function(
		Ajax,                            Core,                         Dictionary,            DomChangeListener,
		DomTraverse,                     Language,                     UiDialog,              UiNotification,
		LanguageChooser,                 LanguageInput,                FileUtil
	)
{
	"use strict";
	
	/**
	 * @constructor
	 */
	function MediaEditor(callbackObject) {
		if (typeof callbackObject !== 'object') {
			throw new TypeError("Parameter 'callbackObject' has to be an object, " + typeof callbackObject + " given.");
		}
		if (typeof callbackObject._editorClose !== 'function') {
			throw new TypeError("Callback object has no function '_editorClose'.");
		}
		if (typeof callbackObject._editorSuccess !== 'function') {
			throw new TypeError("Callback object has no function '_editorSuccess'.");
		}
		
		this._callbackObject = callbackObject;
		this._media = null;
		
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
			
			this._callbackObject._editorSuccess(this._media);
			
			UiDialog.close('mediaEditor_' + this._media.mediaID);
			
			this._media = null;
		},
		
		/**
		 * Is called if an editor is manually closed by the user.
		 */
		_close: function() {
			this._media = null;
			
			this._callbackObject._editorClose();
		},
		
		/**
		 * Handles the `[ENTER]` key to submit the form.
		 * 
		 * @param	{object}	event		event object
		 */
		_keyPress: function(event) {
			// 13 = [ENTER]
			if (event.charCode === 13) {
				event.preventDefault();
				
				this._saveData();
			}
		},
		
		/**
		 * Saves the data of the currently edited media.
		 */
		_saveData: function() {
			var content = UiDialog.getDialog('mediaEditor_' + this._media.mediaID).content;
			
			var altText = elBySel('input[name=altText]', content);
			var caption = elBySel('textarea[name=caption]', content);
			var title = elBySel('input[name=title]', content);
			
			var hasError = false;
			var altTextError = DomTraverse.childByClass(altText.parentNode.parentNode, 'innerError');
			var captionError = DomTraverse.childByClass(caption.parentNode.parentNode, 'innerError');
			var titleError = DomTraverse.childByClass(title.parentNode.parentNode, 'innerError');
			
			this._media.isMultilingual = ~~elBySel('input[name=isMultilingual]', content).checked;
			this._media.languageID = this._media.isMultilingual ? null : LanguageChooser.getLanguageId('languageID');
			
			this._media.altText = {};
			this._media.caption = {};
			this._media.title = {};
			if (this._media.isMultilingual) {
				if (!LanguageInput.validate('altText_' + this._media.mediaID, true)) {
					hasError = true;
					if (!altTextError) {
						var error = elCreate('small');
						error.className = 'innerError';
						error.textContent = Language.get('wcf.global.form.error.multilingual');
						altText.parentNode.parentNode.appendChild(error);
					}
				}
				if (!LanguageInput.validate('caption_' + this._media.mediaID, true)) {
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
						thistitle.parentNode.parentNode.appendChild(error);
					}
				}
				
				this._media.altText = LanguageInput.getValues('altText_' + this._media.mediaID).toObject();
				this._media.caption = LanguageInput.getValues('caption_' + this._media.mediaID).toObject();
				this._media.title = LanguageInput.getValues('title_' + this._media.mediaID).toObject();
			}
			else {
				this._media.altText[this._media.languageID] = altText.value;
				this._media.caption[this._media.languageID] = caption.value;
				this._media.title[this._media.languageID] = title.value;
			}
			
			var aclValues = {
				allowAll: ~~elById('mediaEditor_' + this._media.mediaID + '_aclAllowAll').checked,
				group: [],
				user: [],
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
				LanguageInput.enable('caption_' + this._media.mediaID);
				LanguageInput.enable('altText_' + this._media.mediaID);
				
				elHide(languageChooserContainer);
			}
			else {
				LanguageInput.disable('title_' + this._media.mediaID);
				LanguageInput.disable('caption_' + this._media.mediaID);
				LanguageInput.disable('altText_' + this._media.mediaID);
				
				elShow(languageChooserContainer);
			}
		},
		
		/**
		 * Edits the media with the given data.
		 * 
		 * @param	{object}	media		data of the edited media
		 */
		edit: function(media) {
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
								after: (function(content, data) {
									// make sure that the language chooser is initialized first
									setTimeout(function() {
										LanguageChooser.setLanguageId('languageID', this._media.languageID || LANGUAGE_ID);
										
										if (this._media.isMultilingual) {
											LanguageInput.setValues('altText_' + this._media.mediaID, Dictionary.fromObject(this._media.altText || { }));
											LanguageInput.setValues('caption_' + this._media.mediaID, Dictionary.fromObject(this._media.caption || { }));
											LanguageInput.setValues('title_' + this._media.mediaID, Dictionary.fromObject(this._media.title || { }));
										}
										
										var isMultilingual = elBySel('input[name=isMultilingual]', content);
										isMultilingual.addEventListener('change', this._updateLanguageFields.bind(this));
										
										this._updateLanguageFields(null, isMultilingual);
										
										var keyPress = this._keyPress.bind(this);
										elBySel('input[name=altText]', content).addEventListener('keypress', keyPress);
										elBySel('input[name=title]', content).addEventListener('keypress', keyPress);
										
										elBySel('button[data-type=submit]', content).addEventListener(WCF_CLICK_EVENT, this._saveData.bind(this));
										
										// remove focus from input elements and scroll dialog to top
										document.activeElement.blur();
										elById('mediaEditor_' + this._media.mediaID).parentNode.scrollTop = 0;
										
										DomChangeListener.trigger();
									}.bind(this), 0);
								}).bind(this),
								data: {
									actionName: 'getEditorDialog',
									className: 'wcf\\data\\media\\MediaAction',
									objectIDs: [media.mediaID]
								}
							}
						};
					}.bind(this),
				});
			}
			
			UiDialog.open(this._dialogs.get('mediaEditor_' + media.mediaID));
		}
	};
	
	return MediaEditor;
});

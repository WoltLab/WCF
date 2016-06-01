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
		
		this._elements = {};
	};
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
			
			UiDialog.close('mediaEditor');
			
			this._media = null;
		},
		
		/**
		 * Is called if the editor is manually closed by the user.
		 */
		_close: function() {
			this._media = null;
			
			this._callbackObject._editorClose();
		},
		
		/**
		 * Returns the data for Ui/Dialog to setup the editor dialog.
		 * 
		 * @return	{object}	data to setup the editor dialog
		 */
		_dialogSetup: function() {
			return {
				id: 'mediaEditor',
				options: {
					backdropCloseOnClick: false,
					onClose: this._close.bind(this),
					title: Language.get('wcf.media.edit')
				},
				source: {
					after: (function(content, data) {
						var editor = UiDialog.getDialog('mediaEditor').content;
						
						// data elements
						this._elements.thumbnail = elById('mediaThumbnail');
						this._elements.filename = elById('mediaFilename');
						this._elements.filesize = elById('mediaFilesize');
						this._elements.imageDimensions = elById('mediaImageDimensions');
						this._elements.fileIcon = elById('mediaFileIcon');
						this._elements.uploader = elById('mediaUploader');
						
						// input elements
						this._elements.altText = elById('altText');
						this._elements.caption = elById('caption');
						this._elements.isMultilingual = elById('isMultilingual');
						this._elements.isMultilingual.addEventListener('change', this._updateLanguageFields.bind(this));
						this._elements.title = elById('title');
						this._elements.languageIdContainer = elById('languageIDContainer');
						
						var keyPress = this._keyPress.bind(this);
						this._elements.altText.addEventListener('keypress', keyPress);
						this._elements.title.addEventListener('keypress', keyPress);
						
						setTimeout(this._setData.bind(this), 100);
						
						elBySel('button[data-type="submit"]', editor).addEventListener(WCF_CLICK_EVENT, this._saveData.bind(this));
					}).bind(this),
					data: {
						actionName: 'getEditorDialog',
						className: 'wcf\\data\\media\\MediaAction'
					}
				}
			};
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
			var hasError = false;
			var altTextError = DomTraverse.childByClass(this._elements.altText.parentNode.parentNode, 'innerError');
			var captionError = DomTraverse.childByClass(this._elements.caption.parentNode.parentNode, 'innerError');
			var titleError = DomTraverse.childByClass(this._elements.title.parentNode.parentNode, 'innerError');
			
			this._media.isMultilingual = ~~this._elements.isMultilingual.checked;
			this._media.languageID = this._media.isMultilingual ? null : LanguageChooser.getLanguageId('languageID');
			
			this._media.altText = {};
			this._media.caption = {};
			this._media.title = {};
			if (this._media.isMultilingual) {
				if (!LanguageInput.validate('altText', true)) {
					hasError = true;
					if (!altTextError) {
						var error = elCreate('small');
						error.className = 'innerError';
						error.textContent = Language.get('wcf.global.form.error.multilingual');
						this._elements.altText.parentNode.parentNode.appendChild(error);
					}
				}
				if (!LanguageInput.validate('caption', true)) {
					hasError = true;
					if (!captionError) {
						var error = elCreate('small');
						error.className = 'innerError';
						error.textContent = Language.get('wcf.global.form.error.multilingual');
						this._elements.caption.parentNode.parentNode.appendChild(error);
					}
				}
				if (!LanguageInput.validate('title', true)) {
					hasError = true;
					if (!titleError) {
						var error = elCreate('small');
						error.className = 'innerError';
						error.textContent = Language.get('wcf.global.form.error.multilingual');
						this._elements.title.parentNode.parentNode.appendChild(error);
					}
				}
				
				this._media.altText = LanguageInput.getValues('altText').toObject();
				this._media.caption = LanguageInput.getValues('caption').toObject();
				this._media.title = LanguageInput.getValues('title').toObject();
			}
			else {
				this._media.altText[this._media.languageID] = this._elements.altText.value;
				this._media.caption[this._media.languageID] = this._elements.caption.value;
				this._media.title[this._media.languageID] = this._elements.title.value;
			}
			
			if (!hasError) {
				if (altTextError) elRemove(altTextError);
				if (captionError) elRemove(captionError);
				if (titleError) elRemove(titleError);
				
				Ajax.api(this, {
					actionName: 'update',
					objectIDs: [ this._media.mediaID ],
					parameters: {
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
		 * Inserts the data of the currently edited media into the dialog.
		 */
		_setData: function() {
			this._elements.thumbnail.innerHTML = '';
			
			this._elements.filename.textContent = this._media.filename;
			this._elements.filesize.textContent = this._media.formattedFilesize;
			
			this._elements.uploader.innerHTML = '';
			if (this._media.userLink) {
				var a = elCreate('a');
				a.className = 'userLink';
				elAttr(a, 'href', this._media.userLink);
				elData(a, 'user-id', this._media.userID);
				a.textContent = this._media.username;
				
				this._elements.uploader.appendChild(a);
			}
			else {
				this._elements.uploader.textContent = this._media.username;
			}
			
			if (this._media.isImage) {
				if (this._media.smallThumbnailLink) {
					var img = elCreate('img');
					elAttr(img, 'src', this._media.smallThumbnailLink);
					elAttr(img, 'alt', '');
					
					this._elements.thumbnail.appendChild(img);
				}
				
				this._elements.imageDimensions.textContent = Language.get('wcf.media.imageDimensions.value', {
					height: this._media.height,
					width: this._media.width
				});
				elShow(this._elements.imageDimensions);
				elShow(this._elements.imageDimensions.previousElementSibling);
				
				this._elements.fileIcon.className = 'icon icon48 fa-file-image-o';
			}
			else {
				elHide(this._elements.imageDimensions);
				elHide(this._elements.imageDimensions.previousElementSibling);
				
				this._elements.fileIcon.className = 'icon icon48 ' + FileUtil.getIconClassByMimeType(this._media.fileType);
			}
			
			this._elements.isMultilingual.checked = this._media.isMultilingual;
			
			LanguageChooser.setLanguageId('languageID', this._media.languageID || LANGUAGE_ID);
			
			if (this._media.isMultilingual) {
				LanguageInput.setValues('altText', Dictionary.fromObject(this._media.altText || { }));
				LanguageInput.setValues('caption', Dictionary.fromObject(this._media.caption || { }));
				LanguageInput.setValues('title', Dictionary.fromObject(this._media.title || { }));
			}
			else {
				this._elements.altText.value = this._media.altText ? this._media.altText[this._media.languageID] : '';
				this._elements.caption.value = this._media.caption ? this._media.caption[this._media.languageID] : '';
				this._elements.title.value = this._media.title ? this._media.title[this._media.languageID] : '';
			}
			
			this._updateLanguageFields();
			
			DomChangeListener.trigger();
		},
		
		/**
		 * Updates language-related input fields depending on whether multilingualism
		 * is enabled.
		 */
		_updateLanguageFields: function() {
			if (this._elements.isMultilingual.checked) {
				LanguageInput.enable('title');
				LanguageInput.enable('caption');
				LanguageInput.enable('altText');
				
				elHide(this._elements.languageIdContainer.parentNode);
			}
			else {
				LanguageInput.disable('title');
				LanguageInput.disable('caption');
				LanguageInput.disable('altText');
				
				elShow(this._elements.languageIdContainer.parentNode);
			}
		},
		
		/**
		 * Edits the media with the given data.
		 * 
		 * @param	{object}	media		data of the edited media
		 */
		edit: function(media) {
			if (this._media !== null) {
				throw new Error("Cannot edit media with id '" + media.mediaID + "' while editing media with id '" + this._media.mediaID + "'")
			}
			
			this._media = media;
			
			if (UiDialog.getDialog('mediaEditor') !== undefined) {
				this._setData();
			}
			UiDialog.open(this);
		}
	};
	
	return MediaEditor;
});

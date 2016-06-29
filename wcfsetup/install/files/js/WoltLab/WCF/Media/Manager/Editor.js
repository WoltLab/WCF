/**
 * Provides the media manager dialog for selecting media for Redactor editors.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Media/Manager/Editor
 */
define(['Core', 'Dictionary', 'Dom/Traverse', 'Language', 'Ui/Dialog', 'WoltLab/WCF/Media/Manager/Base'], function(Core, Dictionary, DomTraverse, Language, UiDialog, MediaManagerBase) {
	"use strict";
	
	/**
	 * @constructor
	 */
	function MediaManagerEditor(options) {
		MediaManagerBase.call(this, options);
		
		this._activeButton = null;
		this._buttons = elByClass(this._options.buttonClass || 'jsMediaEditorButton');
		for (var i = 0, length = this._buttons.length; i < length; i++) {
			this._buttons[i].addEventListener(WCF_CLICK_EVENT, this._click.bind(this));
		}
		this._mediaToInsert = new Dictionary();
		this._mediaToInsertByClipboard = false;
	};
	Core.inherit(MediaManagerEditor, MediaManagerBase, {
		/**
		 * @see	WoltLab/WCF/Media/Manager/Base#_addButtonEventListeners
		 */
		_addButtonEventListeners: function() {
			MediaManagerEditor._super.prototype._addButtonEventListeners.call(this);
			
			if (!this._mediaManagerMediaList) return;
			
			var listItems = DomTraverse.childrenByTag(this._mediaManagerMediaList, 'LI');
			for (var i = 0, length = listItems.length; i < length; i++) {
				var listItem = listItems[i];
				
				var insertIcon = elByClass('jsMediaInsertIcon', listItem)[0];
				if (insertIcon) {
					insertIcon.classList.remove('jsMediaInsertIcon');
					insertIcon.addEventListener(WCF_CLICK_EVENT, this._openInsertDialog.bind(this));
				}
			}
		},
		
		/**
		 * Builds the dialog to setup inserting media files.
		 */
		_buildInsertDialog: function() {
			var thumbnailOptions = '';
			
			var sizes = ['small', 'medium', 'large'];
			var size, option;
			lengthLoop: for (var i = 0, length = sizes.length; i < length; i++) {
				size = sizes[i];
				
				// make sure that all thumbnails support the thumbnail size
				for (var j = 0, mediaLength = this._mediaToInsert.length; j < mediaLength; j++) {
					if (!this._mediaToInsert[i][size + 'ThumbnailType']) {
						continue lengthLoop;
					}
				}
				
				thumbnailOptions += '<option value="' + size + '">' + Language.get('wcf.media.insert.imageSize.' + size) + '</option>';
			}
			thumbnailOptions += '<option value="original">' + Language.get('wcf.media.insert.imageSize.original') + '</option>';
			
			var dialog = '<div class="section">'
			+ (this._mediaToInsert.size > 1 ? '<dl>'
				+ '<dt>' + Language.get('wcf.media.insert.type') + '</dt>'
				+ '<dd>'
					+ '<select name="insertType">'
						+ '<option value="separate">' + Language.get('wcf.media.insert.type.separate') + '</option>'
						+ '<option value="gallery">' + Language.get('wcf.media.insert.type.gallery') + '</option>'
					+ '</select>'
				+ '</dd>'
			+ '</dl>' : '')
			+ '<dl>'
				+ '<dt>' + Language.get('wcf.media.insert.imageSize') + '</dt>'
				+ '<dd>'
					+ '<select name="thumbnailSize">'
						+ thumbnailOptions
					+ '</select>'
				+ '</dd>'
			+ '</dl>'
			+ '</div>'
			+ '<div class="formSubmit">'
				+ '<button class="buttonPrimary">' + Language.get('wcf.global.button.insert') + '</button>'
			+ '</div>';
			
			UiDialog.open({
				_dialogSetup: (function() {
					return {
						id: this._getInsertDialogId(),
						options: {
							onClose: this._editorClose.bind(this),
							onSetup: function(content) {
								elByClass('buttonPrimary', content)[0].addEventListener(WCF_CLICK_EVENT, this._insertMedia.bind(this));
							}.bind(this),
							title: Language.get('wcf.media.insert')
						},
						source: dialog
					}
				}).bind(this)
			});
		},
		
		/**
		 * @see	WoltLab/WCF/Media/Manager/Base#_click
		 */
		_click: function(event) {
			this._activeButton = event.currentTarget;
			
			MediaManagerEditor._super.prototype._click.call(this, event);
		},
		
		/**
		 * @see	WoltLab/WCF/Media/Manager/Base#_clipboardAction
		 */
		_clipboardAction: function(actionData) {
			MediaManagerEditor._super.prototype._clipboardAction.call(this, actionData);
			
			if (actionData.data.actionName === 'com.woltlab.wcf.media.insert') {
				this.insertMedia(actionData.data.parameters.objectIDs, true);
			}
		},
		
		/**
		 * Returns the id of the insert dialog based on the media files to be inserted.
		 * 
		 * @return	{string}	insert dialog id
		 */
		_getInsertDialogId: function() {
			var dialogId = 'mediaInsert';
			
			this._mediaToInsert.forEach(function(media, mediaId) {
				dialogId += '-' + mediaId;
			});
			
			return dialogId;
		},
		
		/**
		 * Inserts media files into redactor.
		 * 
		 * @param	{Event?}	event
		 */
		_insertMedia: function(event) {
			var insertType = 'separate';
			var thumbnailSize;
			
			// update insert options with selected values if method is called by clicking on 'insert' button
			// in dialog
			if (event) {
				UiDialog.close(this._getInsertDialogId());
				
				var dialogContent = event.currentTarget.closest('.dialogContent');
				
				if (this._mediaToInsert.size > 1) {
					insertType = elBySel('select[name=insertType]', dialogContent).value;
				}
				thumbnailSize = elBySel('select[name=thumbnailSize]', dialogContent).value;
			}
			
			// TODO: media to be inserted is located in dictionary this._mediaToInsert
			// TODO: insertType = 'separate' or 'gallery' (last case only possible if multiple media files are inserted and all of them are images)
			// TODO: thumbnailSize = 'small', 'media', 'large' or 'original'
			// TODO: redactor is accessible by this._options.editor
			throw new Error("TODO: implement me")
			
			if (this._mediaToInsertByClipboard) {
				// TODO: unmark in clipboard
			}
			
			this._mediaToInsert = new Dictionary();
			this._mediaToInsertByClipboard = false;
			
			// todo: close manager dialog?
		},
		
		/**
		 * Handles clicking on the insert button.
		 * 
		 * @param	{Event}		event		insert button click event
		 */
		_openInsertDialog: function(event) {
			this.insertMedia([~~elData(event.currentTarget, 'object-id')]);
		},
		
		/**
		 * Prepares insertion of the media files with the given ids.
		 * 
		 * @param	{array<int>}	mediaIds		ids of the media files to be inserted
		 * @param	{boolean?}	insertedByClipboard	is true if the media files are inserted by clipboard
		 */
		insertMedia: function(mediaIds, insertedByClipboard) {
			this._mediaToInsert = new Dictionary();
			this._mediaToInsertByClipboard = insertedByClipboard || false;
			
			// open the insert dialog if all media files are images
			var imagesOnly = true, media;
			for (var i = 0, length = mediaIds.length; i < length; i++) {
				media = this._mediaData.get(mediaIds[i]);
				this._mediaToInsert.set(media.mediaID, media);
				
				if (!media.isImage) {
					imagesOnly = false;
				}
			}
			
			if (imagesOnly) {
				UiDialog.close(this);
				var dialogId = this._getInsertDialogId();
				if (UiDialog.getDialog(dialogId)) {
					UiDialog.openStatic(dialogId);
				}
				else {
					this._buildInsertDialog();
				}
			}
			else {
				this._insertMedia();
			}
		},
		
		/**
		 * @see	WoltLab/WCF/Media/Manager/Base#getMode
		 */
		getMode: function() {
			return 'editor';
		},
		
		/**
		 * @see	WoltLab/WCF/Media/Manager/Base#setupMediaElement
		 */
		setupMediaElement: function(media, mediaElement) {
			MediaManagerEditor._super.prototype.setupMediaElement.call(this, media, mediaElement);
			
			// add media insertion icon
			var smallButtons = elBySel('nav.buttonGroupNavigation > ul.smallButtons', mediaElement);
			
			var listItem = elCreate('li');
			smallButtons.appendChild(listItem);
			
			var a = elCreate('a');
			listItem.appendChild(a);
			
			var icon = elCreate('span');
			icon.className = 'icon icon16 fa-plus jsTooltip jsMediaInsertIcon';
			elData(icon, 'object-id', media.mediaID);
			elAttr(icon, 'title', Language.get('wcf.media.button.insert'));
			a.appendChild(icon);
		}
	});
	
	return MediaManagerEditor;
});

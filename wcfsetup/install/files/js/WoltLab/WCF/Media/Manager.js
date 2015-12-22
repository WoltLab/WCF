/**
 * Provides the media manager dialog.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Controller/Media/Manager
 */
define(
	[
		'Core',                     'Dictionary',               'Dom/ChangeListener',      'Dom/Traverse',
		'Dom/Util',                 'EventHandler',             'Language',                'List',
		'Permission',               'Ui/Dialog',                'Ui/Notification',         'WoltLab/WCF/Controller/Clipboard',
		'WoltLab/WCF/Media/Editor', 'WoltLab/WCF/Media/Upload', 'WoltLab/WCF/Media/Search'
		

	],
	function(
		Core,                        Dictionary,                 DomChangeListener,         DomTraverse,
		DomUtil,                     EventHandler,               Language,                  List,
		Permission,                  UiDialog,                   UiNotification,            Clipboard,
		MediaEditor,                 MediaUpload,                MediaSearch
	)
{
	"use strict";
	
	/**
	 * @constructor
	 */
	function MediaManager() {
		this._media = new Dictionary();
		this._mediaData = new Dictionary();
		this._mediaCache = null;
		this._mediaManagerMediaList = null;
		this._search = null;
		
		if (Permission.get('admin.content.cms.canManageMedia')) {
			this._mediaEditor = new MediaEditor(this);
		}
		
		elById('mediaManagerButton').addEventListener('click', this._click.bind(this));
		
		DomChangeListener.add('WoltLab/WCF/Controller/Media/Manager', this._addButtonEventListeners.bind(this));
	};
	MediaManager.prototype = {
		/**
		 * Adds click event listeners to media buttons.
		 */
		_addButtonEventListeners: function() {
			if (!this._mediaManagerMediaList) return;
			
			var listItems = DomTraverse.childrenByTag(this._mediaManagerMediaList, 'LI');
			for (var i = 0, length = listItems.length; i < length; i++) {
				var listItem = listItems[i];
				
				if (Permission.get('admin.content.cms.canManageMedia')) {
					var editIcon = elByClass('jsMediaEditIcon', listItem)[0];
					if (editIcon) {
						editIcon.classList.remove('jsMediaEditIcon');
						editIcon.addEventListener('click', this._editMedia.bind(this));
					}
				}
				
				var insertIcon = elByClass('jsMediaInsertIcon', listItem)[0];
				if (insertIcon) {
					insertIcon.classList.remove('jsMediaInsertIcon');
					insertIcon.addEventListener('click', this._openInsertDialog.bind(this));
				}
			}
		},
		
		/**
		 * Handles clicks on the media manager button.
		 * 
		 * @param	{object}	event	event object
		 */
		_click: function(event) {
			event.preventDefault();
			
			UiDialog.open(this);
		},
		
		/**
		 * Reacts to executed clipboard actions.
		 * 
		 * @param	{object<string, *>}	actionData	data of the executed clipboard action
		 */
		_clipboardAction: function(actionData) {
			// only consider events if the action has been executed
			if (actionData.responseData === null) {
				return;
			}
			
			switch (actionData.data.actionName) {
				case 'com.woltlab.wcf.media.delete':
					var mediaIds = actionData.responseData.objectIDs;
					for (var i = 0, length = mediaIds.length; i < length; i++) {
						this.removeMedia(~~mediaIds[i], true);
					}
					
					UiNotification.show();
					
					break;
				case 'com.woltlab.wcf.media.insert':
					// TODO
					break;
			}
		},
		
		/**
		 * Returns all data to setup the media manager dialog.
		 * 
		 * @return	{object}	dialog setup data
		 */
		_dialogSetup: function() {
			return {
				id: 'mediaManager',
				options: {
					title: Language.get('wcf.media.manager')
				},
				source: {
					after: this._initDialog.bind(this),
					data: {
						actionName: 'getManagementDialog',
						className: 'wcf\\data\\media\\MediaAction'
					}
				}
			};
		},
		
		/**
		 * Opens the media editor for a media file.
		 * 
		 * @param	{Event}		event		event object for clicks on edit icons
		 */
		_editMedia: function(event) {
			if (!Permission.get('admin.content.cms.canManageMedia')) {
				throw new Error("You are not allowed to edit media files.");
			}
			
			UiDialog.close('mediaManager');
			
			this._mediaEditor.edit(this._mediaData.get(~~elData(event.currentTarget, 'object-id')));
		},
		
		/**
		 * Re-opens the manager dialog after closing the editor dialog.
		 */
		_editorClose: function() {
			UiDialog.open(this);
		},
		
		/**
		 * Re-opens the manager dialog and updates the media data after
		 * successfully editing a media file.
		 * 
		 * @param	{object}	media		updated media file data
		 */
		_editorSuccess: function(media) {
			UiDialog.open(this);
			
			this._mediaData.set(~~media.mediaID, media);
			
			var listItem = this._media.get(~~media.mediaID);
			var p = elByClass('mediaTitle', listItem)[0];
			if (media.isMultilingual) {
				p.textContent = media.title[LANGUAGE_ID] || media.filename;
			}
			else {
				p.textContent = media.title[media.languageID] || media.filename;
			}
		},
		
		/**
		 * Initializes the dialog when first loaded.
		 * 
		 * @param	{string}	content		dialog content
		 * @param	{object}	data		AJAX request's response data
		 */
		_initDialog: function(content, data) {
			// store media data locally
			var media = data.returnValues.media || { };
			for (var mediaId in media) {
				if (media.hasOwnProperty(mediaId)) {
					this._mediaData.set(~~mediaId, media[mediaId]);
				}
			}
			
			this._mediaManagerMediaList = elById('mediaManagerMediaList');
			
			// store list items locally
			var listItems = DomTraverse.childrenByTag(this._mediaManagerMediaList, 'LI');
			for (var i = 0, length = listItems.length; i < length; i++) {
				var listItem = listItems[i];
				
				this._media.set(~~elData(listItem, 'object-id'), listItem);
			}
			
			if (Permission.get('admin.content.cms.canManageMedia')) {
				new MediaUpload('mediaManagerMediaUploadButton', 'mediaManagerMediaList', {
					mediaManager: this
				});
				
				Clipboard.setup({
					hasMarkedItems: data.returnValues.hasMarkedItems ? true : false,
					pageClassName: '*'
				});
				
				EventHandler.add('com.woltlab.wcf.clipboard', 'com.woltlab.wcf.media', this._clipboardAction.bind(this));
			}
			
			this._search = new MediaSearch(this);
			
			if (!listItems.length) {
				this._search.hideSearch();
				
				if (true) {
					elById('mediaManagerMediaUploadButton').classList.remove('marginTop');
				}
			}
		},
		
		_insertMedia: function() {
			// TODO
		},
		
		_openInsertDialog: function(event) {
			var media = this._mediaData.get(~~elData(event.currentTarget, 'object-id'));
			
			// check if media file is image and has at least small thumbnail
			// to show insertion options
			if (media.isImage && media.smallThumbnailType) {
				UiDialog.close(this);
				var dialogId = 'mediaInsert' + media.mediaID;
				if (UiDialog.getDialog(dialogId)) {
					UiDialog.openStatic(dialogId);
				}
				else {
					var dialog = elCreate('div');
					
					var fieldset = elCreate('fieldset');
					dialog.appendChild(fieldset);
					
					var dl = elCreate('dl');
					fieldset.appendChild(dl);
					
					var dt = elCreate('dt');
					dt.textContent = Language.get('wcf.media.insert.imageSize');
					dl.appendChild(dt);
					
					var dd = elCreate('dd');
					dl.appendChild(dd);
					
					var select = elCreate('select');
					dd.appendChild(select);
					
					var sizes = ['small', 'medium', 'large'];
					var size, option;
					for (var i = 0, length = sizes.length; i < length; i++) {
						size = sizes[i];
						
						if (media[size + 'ThumbnailType']) {
							option = elCreate('option');
							elAttr(option, 'value', size);
							option.textContent = Language.get('wcf.media.insert.imageSize.' + size, {
								height: media[size + 'ThumbnailHeight'],
								width: media[size + 'ThumbnailWidth']
							});
							select.appendChild(option);
						}
					}
					
					option = elCreate('option');
					elAttr(option, 'value', 'original');
					option.textContent = Language.get('wcf.media.insert.imageSize.original', {
						height: media.height,
						width: media.width
					});
					select.appendChild(option);
					
					var formSubmit = elCreate('div');
					formSubmit.className = 'formSubmit';
					dialog.appendChild(formSubmit);
					
					var submitButton = elCreate('button');
					submitButton.className = 'buttonPrimary';
					submitButton.textContent = Language.get('wcf.global.button.insert');
					elData(submitButton, 'object-id', media.mediaID);
					submitButton.addEventListener('click', this._insertMedia.bind(this));
					formSubmit.appendChild(submitButton);
					
					UiDialog.open({
						_dialogSetup: (function() {
							return {
								id: dialogId,
								options: {
									onClose: this._editorClose.bind(this),
									title: Language.get('wcf.media.insert')
								},
								source: dialog.outerHTML
							}
						}).bind(this)
					});
				}
			}
			else {
				// insert media
				// TODO
			}
		},
		
		_setMedia: function(media, listItems) {
			if (Core.isPlainObject(media)) {
				this._media = Dictionary.fromObject(media);
			}
			else {
				this._media = media;
			}
			
			var info = DomTraverse.nextByClass(this._mediaManagerMediaList, 'info');
			
			if (this._media.size) {
				if (info) {
					elHide(info);
				}
			}
			else {
				if (info === null) {
					info = elCreate('p');
					info.className = 'info';
					info.textContent = Language.get('wcf.media.search.noResults');
				}
				
				elShow(info);
				DomUtil.insertAfter(info, this._mediaManagerMediaList);
			}
			
			var mediaListItems = DomTraverse.childrenByTag(this._mediaManagerMediaList, 'LI');
			for (var i = 0, length = mediaListItems.length; i < length; i++) {
				var listItem = mediaListItems[i];
				
				if (!this._media.has(elData(listItem, 'object-id'))) {
					elHide(listItem);
				}
				else {
					elShow(listItem);
				}
			}
			
			DomChangeListener.trigger();
			
			Clipboard.reload();
		},
		
		/**
		 * Adds a media file to the manager.
		 * 
		 * @param	{object}	media		data of the media file
		 * @param	{Element}	listItem	list item representing the file
		 */
		addMedia: function(media, listItem) {
			if (!media.languageID) media.isMultilingual = 1;
			
			this._mediaData.set(~~media.mediaID, media);
			this._media.set(~~media.mediaID, listItem);
			
			if (this._media.size === 1) {
				this._search.showSearch();
				
				if (true) {
					elById('mediaManagerMediaUploadButton').classList.add('marginTop');
				}
			}
		},

		/**
		 * Removes a media file.
		 *
		 * @param	{int}			mediaId		id of the removed media file
		 * @param	{boolean|undefined}	checkCache	media file will also be removed from the local cache if true
	     	 */
		removeMedia: function(mediaId, checkCache) {
			if (this._media.has(mediaId)) {
				// remove list item
				elRemove(this._media.get(mediaId));

				this._media.delete(mediaId);
				this._mediaData.delete(mediaId);
			}

			if (checkCache && this._mediaCache && this._mediaCache.has(mediaId)) {
				this._mediaCache.delete(mediaId);
			}
		},
		
		/**
		 * Changes the displayed media to the previously displayed media.
		 */
		resetMedia: function() {
			if (this._mediaCache !== null) {
				this._setMedia(this._mediaCache);
				
				this._mediaCache = null;
				
				this._search.resetSearch();
			}
		},
		
		/**
		 * Sets the media files currently displayed.
		 * 
		 * @param	{object}	media		media data
		 * @param	{string}	template	
		 */
		setMedia: function(media, template) {
			if (!this._mediaCache) {
				this._mediaCache = this._media;
			}
			
			var hasMedia = false;
			for (var mediaId in media) {
				if (media.hasOwnProperty(mediaId)) {
					hasMedia = true;
				}
			}
			
			var newListItems = [];
			if (hasMedia) {
				var ul = elCreate('ul');
				ul.innerHTML = template;
				
				var listItems = DomTraverse.childrenByTag(ul, 'LI');
				for (var i = 0, length = listItems.length; i < length; i++) {
					var listItem = listItems[i];
					if (!this._mediaData.has(~~elData(listItem, 'object-id'))) {
						this._mediaData.set(elData(listItem, 'object-id'), listItem);
						
						this._mediaManagerMediaList.appendChild(listItem);
					}
				}
			}
			
			this._setMedia(media);
		}
	};
	
	return MediaManager;
});

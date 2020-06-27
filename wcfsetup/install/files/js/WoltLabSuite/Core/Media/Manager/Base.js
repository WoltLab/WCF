/**
 * Provides the media manager dialog.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Media/Manager/Base
 */
define(
	[
		'Core',                     'Dictionary',               'Dom/ChangeListener',              'Dom/Traverse',
		'Dom/Util',                 'EventHandler',             'Language',                        'List',
		'Permission',               'Ui/Dialog',                'Ui/Notification',                 'WoltLabSuite/Core/Controller/Clipboard',
		'WoltLabSuite/Core/Media/Editor', 'WoltLabSuite/Core/Media/Upload', 'WoltLabSuite/Core/Media/Manager/Search', 'StringUtil',
		'WoltLabSuite/Core/Ui/Pagination',
		'WoltLabSuite/Core/Media/Clipboard'
	],
	function(
		Core,                        Dictionary,                 DomChangeListener,                 DomTraverse,
		DomUtil,                     EventHandler,               Language,                          List,
		Permission,                  UiDialog,                   UiNotification,                    Clipboard,
		MediaEditor,                 MediaUpload,                MediaManagerSearch,                StringUtil,
		UiPagination,
		MediaClipboard
	)
{
	"use strict";
	
	if (!COMPILER_TARGET_DEFAULT) {
		var Fake = function() {};
		Fake.prototype = {
			_addButtonEventListeners: function() {},
			_click: function() {},
			_dialogClose: function() {},
			_dialogInit: function() {},
			_dialogSetup: function() {},
			_dialogShow: function() {},
			_editMedia: function() {},
			_editorClose: function() {},
			_editorSuccess: function() {},
			_removeClipboardCheckboxes: function() {},
			_setMedia: function() {},
			addMedia: function() {},
			clipboardDeleteMedia: function() {},
			getDialog: function() {},
			getMode: function() {},
			getOption: function() {},
			removeMedia: function() {},
			resetMedia: function() {},
			setMedia: function() {},
			setupMediaElement: function() {}
		};
		return Fake;
	}
	
	var _mediaManagerCounter = 0;
	
	/**
	 * @constructor
	 */
	function MediaManagerBase(options) {
		this._options = Core.extend({
			dialogTitle: Language.get('wcf.media.manager'),
			imagesOnly: false,
			minSearchLength: 3
		}, options);
		
		this._id = 'mediaManager' + _mediaManagerCounter++;
		this._listItems = new Dictionary();
		this._media = new Dictionary();
		this._mediaManagerMediaList = null;
		this._search = null;
		this._upload = null;
		this._forceClipboard = false;
		this._hadInitiallyMarkedItems = false;
		this._pagination = null;
		
		if (Permission.get('admin.content.cms.canManageMedia')) {
			this._mediaEditor = new MediaEditor(this);
		}
		
		DomChangeListener.add('WoltLabSuite/Core/Media/Manager', this._addButtonEventListeners.bind(this));
		
		EventHandler.add('com.woltlab.wcf.media.upload', 'success', this._openEditorAfterUpload.bind(this));
	}
	MediaManagerBase.prototype = {
		/**
		 * Adds click event listeners to media buttons.
		 */
		_addButtonEventListeners: function() {
			if (!this._mediaManagerMediaList) return;
			
			var listItems = DomTraverse.childrenByTag(this._mediaManagerMediaList, 'LI');
			for (var i = 0, length = listItems.length; i < length; i++) {
				var listItem = listItems[i];
				
				if (Permission.get('admin.content.cms.canManageMedia')) {
					var editIcon = elByClass('jsMediaEditButton', listItem)[0];
					if (editIcon) {
						editIcon.classList.remove('jsMediaEditButton');
						editIcon.addEventListener(WCF_CLICK_EVENT, this._editMedia.bind(this));
					}
				}
			}
		},
		
		/**
		 * Is called when a new category is selected.
		 */
		_categoryChange: function() {
			this._search.search();
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
		 * Is called if the media manager dialog is closed.
		 */
		_dialogClose: function() {
			// only show media clipboard if editor is open
			if (Permission.get('admin.content.cms.canManageMedia') || this._forceClipboard) {
				Clipboard.hideEditor('com.woltlab.wcf.media');
			}
		},
		
		/**
		 * Initializes the dialog when first loaded.
		 *
		 * @param	{string}	content		dialog content
		 * @param	{object}	data		AJAX request's response data
		 */
		_dialogInit: function(content, data) {
			// store media data locally
			var media = data.returnValues.media || { };
			for (var mediaId in media) {
				if (objOwns(media, mediaId)) {
					this._media.set(~~mediaId, media[mediaId]);
				}
			}
			
			this._initPagination(~~data.returnValues.pageCount);
			
			this._hadInitiallyMarkedItems = data.returnValues.hasMarkedItems;
		},
		
		/**
		 * Returns all data to setup the media manager dialog.
		 * 
		 * @return	{object}	dialog setup data
		 */
		_dialogSetup: function() {
			return {
				id: this._id,
				options: {
					onClose: this._dialogClose.bind(this),
					onShow: this._dialogShow.bind(this),
					title: this._options.dialogTitle
				},
				source: {
					after: this._dialogInit.bind(this),
					data: {
						actionName: 'getManagementDialog',
						className: 'wcf\\data\\media\\MediaAction',
						parameters: {
							mode: this.getMode(),
							imagesOnly: this._options.imagesOnly
						}
					}
				}
			};
		},
		
		/**
		 * Is called if the media manager dialog is shown.
		 */
		_dialogShow: function() {
			if (!this._mediaManagerMediaList) {
				var dialog = this.getDialog();
				
				this._mediaManagerMediaList = elByClass('mediaManagerMediaList', dialog)[0];
				
				this._mediaCategorySelect = elBySel('.mediaManagerCategoryList > select', dialog);
				if (this._mediaCategorySelect) {
					this._mediaCategorySelect.addEventListener('change', this._categoryChange.bind(this));
				}
				
				// store list items locally
				var listItems = DomTraverse.childrenByTag(this._mediaManagerMediaList, 'LI');
				for (var i = 0, length = listItems.length; i < length; i++) {
					var listItem = listItems[i];
					
					this._listItems.set(~~elData(listItem, 'object-id'), listItem);
				}
				
				if (Permission.get('admin.content.cms.canManageMedia')) {
					var uploadButton = elByClass('mediaManagerMediaUploadButton', UiDialog.getDialog(this).dialog)[0];
					this._upload = new MediaUpload(DomUtil.identify(uploadButton), DomUtil.identify(this._mediaManagerMediaList), {
						mediaManager: this
					});
					
					var deleteAction = new WCF.Action.Delete('wcf\\data\\media\\MediaAction', '.mediaFile');
					deleteAction._didTriggerEffect = function(element) {
						this.removeMedia(elData(element[0], 'object-id'));
					}.bind(this);
				}
				
				if (Permission.get('admin.content.cms.canManageMedia') || this._forceClipboard) {
					MediaClipboard.init(
						'menuManagerDialog-' + this.getMode(),
						this._hadInitiallyMarkedItems ? true : false,
						this
					);
				}
				else {
					this._removeClipboardCheckboxes();
				}
				
				this._search = new MediaManagerSearch(this);
				
				if (!listItems.length) {
					this._search.hideSearch();
				}
			}
			
			// only show media clipboard if editor is open
			if (Permission.get('admin.content.cms.canManageMedia') || this._forceClipboard) {
				Clipboard.showEditor('com.woltlab.wcf.media');
			}
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
			
			UiDialog.close(this);
			
			this._mediaEditor.edit(this._media.get(~~elData(event.currentTarget, 'object-id')));
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
		 * @param	{integer}	oldCategoryId	old category id
		 */
		_editorSuccess: function(media, oldCategoryId) {
			// if the category changed of media changed and category
			// is selected, check if media list needs to be refreshed
			if (this._mediaCategorySelect) {
				var selectedCategoryId = ~~this._mediaCategorySelect.value;
				
				if (selectedCategoryId) {
					var newCategoryId = ~~media.categoryID;
					
					if (oldCategoryId != newCategoryId && (oldCategoryId == selectedCategoryId || newCategoryId == selectedCategoryId)) {
						this._search.search();
					}
				}
			}
			
			UiDialog.open(this);
			
			this._media.set(~~media.mediaID, media);
			
			var listItem = this._listItems.get(~~media.mediaID);
			var p = elByClass('mediaTitle', listItem)[0];
			if (media.isMultilingual) {
				if (media.title && media.title[LANGUAGE_ID]) {
					p.textContent = media.title[LANGUAGE_ID];
				}
				else {
					p.textContent = media.filename;
				}
			}
			else {
				if (media.title && media.title[media.languageID]) {
					p.textContent = media.title[media.languageID];
				}
				else {
					p.textContent = media.filename;
				}
			}
			
			var thumbnail = elByClass('mediaThumbnail', listItem)[0];
			thumbnail.innerHTML = media.elementTag;
			// Bust browser cache by adding additional parameter.
			var imgs = elByTag('img', thumbnail);
			if (imgs.length) {
				imgs[0].src += '&refresh=' + Date.now();
			}
		},
		
		/**
		 * Initializes the dialog pagination.
		 *
		 * @param	{integer}	pageCount
		 * @param	{integer}	pageNo
		 */
		_initPagination: function(pageCount, pageNo) {
			if (pageNo === undefined) pageNo = 1;
			
			if (pageCount > 1) {
				var newPagination = elCreate('div');
				newPagination.className = 'paginationBottom jsPagination';
				DomUtil.replaceElement(elBySel('.jsPagination', UiDialog.getDialog(this).content), newPagination);
				
				this._pagination = new UiPagination(newPagination, {
					activePage: pageNo,
					callbackSwitch: this._search.search.bind(this._search),
					maxPage: pageCount
				});
			}
			else if (this._pagination) {
				elHide(this._pagination.getElement());
			}
		},
		
		/**
		 * Removes all media clipboard checkboxes.
		 */
		_removeClipboardCheckboxes: function() {
			var checkboxes = elByClass('mediaCheckbox', this._mediaManagerMediaList);
			while (checkboxes.length) {
				elRemove(checkboxes[0]);
			}
		},
		
		/**
		 * Opens the media editor after uploading a single file.
		 * 
		 * @param	{object}	data	upload event data
		 * @since	5.2
		 */
		_openEditorAfterUpload: function(data) {
			if (data.upload === this._upload && !data.isMultiFileUpload && !this._upload.hasPendingUploads()) {
				var keys = Object.keys(data.media);
				
				if (keys.length) {
					UiDialog.close(this);
					
					this._mediaEditor.edit(this._media.get(~~data.media[keys[0]].mediaID));
				}
			}
		},
		
		/**
		 * Sets the displayed media (after a search).
		 * 
		 * @param	{Dictionary}	media		media to be set as active
		 */
		_setMedia: function(media) {
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
			
			if (Permission.get('admin.content.cms.canManageMedia') || this._forceClipboard) {
				Clipboard.reload();
			}
			else {
				this._removeClipboardCheckboxes();
			}
		},
		
		/**
		 * Adds a media file to the manager.
		 * 
		 * @param	{object}	media		data of the media file
		 * @param	{Element}	listItem	list item representing the file
		 */
		addMedia: function(media, listItem) {
			if (!media.languageID) media.isMultilingual = 1;
			
			this._media.set(~~media.mediaID, media);
			this._listItems.set(~~media.mediaID, listItem);
			
			if (this._listItems.size === 1) {
				this._search.showSearch();
			}
		},
		
		/**
		 * Is called after the media files with the given ids have been deleted via clipboard.
		 * 
		 * @param	{int[]}		mediaIds	ids of deleted media files
		 */
		clipboardDeleteMedia: function(mediaIds) {
			for (var i = 0, length = mediaIds.length; i < length; i++) {
				this.removeMedia(~~mediaIds[i], true);
			}
			
			UiNotification.show();
		},
		
		/**
		 * Returns the id of the currently selected category or `0` if no category is selected.
		 * 
		 * @return	{integer}
		 */
		getCategoryId: function() {
			if (this._mediaCategorySelect) {
				return this._mediaCategorySelect.value;
			}
			
			return 0;
		},
		
		/**
		 * Returns the media manager dialog element.
		 * 
		 * @return	{Element}	media manager dialog
		 */
		getDialog: function() {
			return UiDialog.getDialog(this).dialog;
		},
		
		/**
		 * Returns the mode of the media manager.
		 *
		 * @return	{string}
		 */
		getMode: function() {
			return '';
		},
		
		/**
		 * Returns the media manager option with the given name.
		 * 
		 * @param	{string}	name		option name
		 * @return	{mixed}		option value or null
		 */
		getOption: function(name) {
			if (this._options[name]) {
				return this._options[name];
			}
			
			return null;
		},
		
		/**
		 * Removes a media file.
		 *
		 * @param	{int}			mediaId		id of the removed media file
	 	 */
		removeMedia: function(mediaId) {
			if (this._listItems.has(mediaId)) {
				// remove list item
				try {
					elRemove(this._listItems.get(mediaId));
				}
				catch (e) {
					// ignore errors if item has already been removed like by WCF.Action.Delete
				}
				
				this._listItems.delete(mediaId);
				this._media.delete(mediaId);
			}
		},
		
		/**
		 * Changes the displayed media to the previously displayed media.
		 */
		resetMedia: function() {
			// calling WoltLabSuite/Core/Media/Manager/Search.search() reloads the first page of the dialog
			this._search.search();
		},
		
		/**
		 * Sets the media files currently displayed.
		 * 
		 * @param	{object}	media		media data
		 * @param	{string}	template	
		 * @param	{object}	additionalData
		 */
		setMedia: function(media, template, additionalData) {
			var hasMedia = false;
			for (var mediaId in media) {
				if (objOwns(media, mediaId)) {
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
					if (!this._listItems.has(~~elData(listItem, 'object-id'))) {
						this._listItems.set(elData(listItem, 'object-id'), listItem);
						
						this._mediaManagerMediaList.appendChild(listItem);
					}
				}
			}
			
			this._initPagination(additionalData.pageCount, additionalData.pageNo);
			
			this._setMedia(media);
		},
		
		/**
		 * Sets up a new media element.
		 * 
		 * @param	{object}	media		data of the media file
		 * @param	{HTMLElement}	mediaElement	element representing the media file
		 */
		setupMediaElement: function(media, mediaElement) {
			var mediaInformation = DomTraverse.childByClass(mediaElement, 'mediaInformation');
			
			var buttonGroupNavigation = elCreate('nav');
			buttonGroupNavigation.className = 'jsMobileNavigation buttonGroupNavigation';
			mediaInformation.parentNode.appendChild(buttonGroupNavigation);
			
			var buttons = elCreate('ul');
			buttons.className = 'buttonList iconList';
			buttonGroupNavigation.appendChild(buttons);
			
			var listItem = elCreate('li');
			listItem.className = 'mediaCheckbox';
			buttons.appendChild(listItem);
			
			var a = elCreate('a');
			listItem.appendChild(a);
			
			var label = elCreate('label');
			a.appendChild(label);
			
			var checkbox = elCreate('input');
			checkbox.className = 'jsClipboardItem';
			elAttr(checkbox, 'type', 'checkbox');
			elData(checkbox, 'object-id', media.mediaID);
			label.appendChild(checkbox);
			
			if (Permission.get('admin.content.cms.canManageMedia')) {
				listItem = elCreate('li');
				listItem.className = 'jsMediaEditButton';
				elData(listItem, 'object-id', media.mediaID);
				buttons.appendChild(listItem);
				
				listItem.innerHTML = '<a><span class="icon icon16 fa-pencil jsTooltip" title="' + Language.get('wcf.global.button.edit') + '"></span> <span class="invisible">' + Language.get('wcf.global.button.edit') + '</span></a>';
				
				listItem = elCreate('li');
				listItem.className = 'jsDeleteButton';
				elData(listItem, 'object-id', media.mediaID);
				
				// use temporary title to not unescape html in filename
				var uuid = Core.getUuid();
				elData(listItem, 'confirm-message-html', StringUtil.unescapeHTML(Language.get('wcf.media.delete.confirmMessage', {
					title: uuid
				})).replace(uuid, StringUtil.escapeHTML(media.filename)));
				buttons.appendChild(listItem);
				
				listItem.innerHTML = '<a><span class="icon icon16 fa-times jsTooltip" title="' + Language.get('wcf.global.button.delete') + '"></span> <span class="invisible">' + Language.get('wcf.global.button.delete') + '</span></a>';
			}
		}
	};
	
	return MediaManagerBase;
});

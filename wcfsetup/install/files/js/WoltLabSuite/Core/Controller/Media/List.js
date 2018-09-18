/**
 * Initializes modules required for media list view.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Controller/Media/List
 */
define([
		'Dom/ChangeListener',
		'EventHandler',
		'WoltLabSuite/Core/Controller/Clipboard',
		'WoltLabSuite/Core/Media/Clipboard',
		'WoltLabSuite/Core/Media/Editor',
		'WoltLabSuite/Core/Media/List/Upload'
	],
	function(
		DomChangeListener,
		EventHandler,
		Clipboard,
		MediaClipboard,
		MediaEditor,
		MediaListUpload
	) {
	"use strict";
	
	if (!COMPILER_TARGET_DEFAULT) {
		var Fake = function() {};
		Fake.prototype = {
			init: function() {},
			_addButtonEventListeners: function() {},
			_deleteCallback: function() {},
			_deleteMedia: function(mediaIds) {},
			_edit: function() {}
		};
		return Fake;
	}
	
	var _mediaEditor;
	var _tableBody = elById('mediaListTableBody');
	var _clipboardObjectIds = [];
	var _upload;
	
	/**
	 * @exports	WoltLabSuite/Core/Controller/Media/List
	 */
	return {
		init: function(options) {
			options = options || {};
			_upload = new MediaListUpload('uploadButton', 'mediaListTableBody', {
				categoryId: options.categoryId,
				multiple: true
			});
			
			MediaClipboard.init(
				'wcf\\acp\\page\\MediaListPage',
				options.hasMarkedItems || false,
				this
			);
			
			EventHandler.add('com.woltlab.wcf.media.upload', 'removedErroneousUploadRow', this._deleteCallback.bind(this));
			
			var deleteAction = new WCF.Action.Delete('wcf\\data\\media\\MediaAction', '.jsMediaRow');
			deleteAction.setCallback(this._deleteCallback);
			
			_mediaEditor = new MediaEditor({
				_editorSuccess: function(media, oldCategoryId) {
					if (media.categoryID != oldCategoryId) {
						window.setTimeout(function() {
							window.location.reload();
						}, 500);
					}
				}
			});
			
			this._addButtonEventListeners();
			
			DomChangeListener.add('WoltLabSuite/Core/Controller/Media/List', this._addButtonEventListeners.bind(this));
			
			EventHandler.add('com.woltlab.wcf.media.upload', 'success', this._openEditorAfterUpload.bind(this));
		},
		
		/**
		 * Adds the `click` event listeners to the media edit icons in
		 * new media table rows.
		 */
		_addButtonEventListeners: function() {
			var buttons = elByClass('jsMediaEditButton', _tableBody), button;
			while (buttons.length) {
				button = buttons[0];
				button.classList.remove('jsMediaEditButton');
				button.addEventListener(WCF_CLICK_EVENT, this._edit.bind(this));
			}
		},
		
		/**
		 * Is triggered after media files have been deleted using the delete icon.
		 * 
		 * @param	{int[]?}	objectIds
		 */
		_deleteCallback: function(objectIds) {
			var tableRowCount = elByTag('tr', _tableBody).length;
			if (objectIds.length === undefined) {
				if (!tableRowCount) {
					window.location.reload();
				}
			}
			else if (objectIds.length === tableRowCount) {
				// table is empty, reload page
				window.location.reload();
			}
			else {
				Clipboard.reload.bind(Clipboard)
			}
		},
		
		/**
		 * Is called when a media edit icon is clicked.
		 * 
		 * @param	{Event}		event
		 */
		_edit: function(event) {
			_mediaEditor.edit(elData(event.currentTarget, 'object-id'));
		},
		
		/**
		 * Opens the media editor after uploading a single file.
		 *
		 * @param	{object}	data	upload event data
		 * @since	3.2
		 */
		_openEditorAfterUpload: function(data) {
			if (data.upload === _upload && !data.isMultiFileUpload) {
				var keys = Object.keys(data.media);
				
				if (keys.length) {
					_mediaEditor.edit(this._media.get(~~data.media[keys[0]].mediaID));
				}
			}
		},
		
		/**
		 * Is called after the media files with the given ids have been deleted via clipboard.
		 * 
		 * @param	{int[]}		mediaIds	ids of deleted media files
		 */
		clipboardDeleteMedia: function(mediaIds) {
			var mediaRows = elByClass('jsMediaRow');
			for (var i = 0; i < mediaRows.length; i++) {
				var media = mediaRows[i];
				var mediaID = ~~elData(elByClass('jsClipboardItem', media)[0], 'object-id');
				
				if (mediaIds.indexOf(mediaID) !== -1) {
					elRemove(media);
					i--;
				}
			}
			
			if (!mediaRows.length) {
				window.location.reload();
			}
		}
	}
});
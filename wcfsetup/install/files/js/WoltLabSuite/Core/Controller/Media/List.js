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
		'WoltLabSuite/Core/Media/Editor',
		'WoltLabSuite/Core/Media/List/Upload'
	],
	function(
		DomChangeListener,
		EventHandler,
		Clipboard,
		MediaEditor,
		MediaListUpload
	) {
	"use strict";
	
	if (!COMPILER_TARGET_DEFAULT) {
		var Fake = function() {};
		Fake.prototype = {
			init: function() {},
			_clipboardAction: function() {},
			_edit: function() {}
		};
		return Fake;
	}
	
	var _mediaEditor;
	var _tableBody = elById('mediaListTableBody');
	
	/**
	 * @exports	WoltLabSuite/Core/Controller/Media/List
	 */
	return {
		init: function(options) {
			options = options || {};
			new MediaListUpload('uploadButton', 'mediaListTableBody', {
				categoryId: options.categoryId,
				multiple: true
			});
			
			Clipboard.setup({
				hasMarkedItems: options.hasMarkedItems || false,
				pageClassName: 'wcf\\acp\\page\\MediaListPage'
			});
			
			EventHandler.add('com.woltlab.wcf.clipboard', 'com.woltlab.wcf.media', this._clipboardAction.bind(this));
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
		 * Handles successful clipboard actions.
		 * 
		 * @param	{object}	actionData
		 */
		_clipboardAction: function(actionData) {
			// only consider events if the action has been executed
			if (actionData.responseData === null) {
				return;
			}
			
			if (actionData.data.actionName === 'com.woltlab.wcf.media.delete') {
				var mediaIds = actionData.responseData.objectIDs;
				
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
		},
		
		/**
		 * Is called when a media edit icon is clicked.
		 * 
		 * @param	{Event}		event
		 */
		_edit: function(event) {
			_mediaEditor.edit(elData(event.currentTarget, 'object-id'));
		}
	}
});
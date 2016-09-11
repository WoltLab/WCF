/**
 * Initializes modules required for media list view.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Controller/Media/List
 */
define(['EventHandler', 'WoltLabSuite/Core/Controller/Clipboard', 'WoltLabSuite/Core/Media/Editor', 'WoltLabSuite/Core/Media/List/Upload'], function(EventHandler, Clipboard, MediaEditor, MediaListUpload) {
	"use strict";
	
	var _mediaEditor;
	
	/**
	 * @exports	WoltLabSuite/Core/Controller/Media/List
	 */
	return {
		init: function(options) {
			options = options || {};
			new MediaListUpload('uploadButton', 'mediaFile');
			
			Clipboard.setup({
				hasMarkedItems: options.hasMarkedItems || false,
				pageClassName: 'wcf\\acp\\page\\MediaListPage'
			});
			
			EventHandler.add('com.woltlab.wcf.clipboard', 'com.woltlab.wcf.media', this._clipboardAction.bind(this));
			
			new WCF.Action.Delete('wcf\\data\\media\\MediaAction', '.jsMediaRow');
			
			_mediaEditor = new MediaEditor();
			
			var editButtons = elByClass('jsMediaEditButton');
			for (var i = 0, length = editButtons.length; i < length; i++) {
				editButtons[i].addEventListener(WCF_CLICK_EVENT, this._edit.bind(this));
			}
		},
		
		/**
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
		
		_edit: function(event) {
			_mediaEditor.edit(elData(event.currentTarget, 'object-id'));
		}
	}
});
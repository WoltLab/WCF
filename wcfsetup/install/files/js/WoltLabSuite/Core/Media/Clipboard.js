/**
 * Initializes modules required for media clipboard.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Media/Clipboard
 */
define([
		'Ajax',
		'Dom/ChangeListener',
		'EventHandler',
		'Language',
		'Ui/Dialog',
		'Ui/Notification',
		'WoltLabSuite/Core/Controller/Clipboard',
		'WoltLabSuite/Core/Media/Editor',
		'WoltLabSuite/Core/Media/List/Upload'
	],
	function(
		Ajax,
		DomChangeListener,
		EventHandler,
		Language,
		UiDialog,
		UiNotification,
		Clipboard,
		MediaEditor,
		MediaListUpload
	) {
	"use strict";
	
	if (!COMPILER_TARGET_DEFAULT) {
		var Fake = function() {};
		Fake.prototype = {
			init: function() {},
			_ajaxSetup: function() {},
			_ajaxSuccess: function() {},
			_clipboardAction: function() {},
			_dialogSetup: function() {},
			_edit: function() {},
			_setCategory: function() {}
		};
		return Fake;
	}
	
	var _clipboardObjectIds = [];
	var _mediaManager;
	
	/**
	 * @exports	WoltLabSuite/Core/Media/Clipboard
	 */
	return {
		init: function(pageClassName, hasMarkedItems, mediaManager) {
			Clipboard.setup({
				hasMarkedItems: hasMarkedItems,
				pageClassName: pageClassName
			});
			
			_mediaManager = mediaManager;
			
			EventHandler.add('com.woltlab.wcf.clipboard', 'com.woltlab.wcf.media', this._clipboardAction.bind(this));
		},
		
		/**
		 * Returns the data used to setup the AJAX request object.
		 *
		 * @return	{object}	setup data
		 */
		_ajaxSetup: function() {
			return {
				data: {
					className: 'wcf\\data\\media\\MediaAction'
				}
			}
		},
		
		/**
		 * Handles successful AJAX request.
		 *
		 * @param	{object}	data	response data
		 */
		_ajaxSuccess: function(data) {
			switch (data.actionName) {
				case 'getSetCategoryDialog':
					UiDialog.open(this, data.returnValues.template);
					
					break;
					
				case 'setCategory':
					UiDialog.close(this);
					
					UiNotification.show();
					
					Clipboard.reload();
					
					break;
			}
		},
		
		/**
		 * Returns the data used to setup the dialog.
		 * 
		 * @return	{object}	setup data
		 */
		_dialogSetup: function() {
			return {
				id: 'mediaSetCategoryDialog',
				options: {
					onSetup: function(content) {
						elBySel('button', content).addEventListener(WCF_CLICK_EVENT, function(event) {
							event.preventDefault();
							
							this._setCategory(~~elBySel('select[name="categoryID"]', content).value);
							
							event.currentTarget.disabled = true;
						}.bind(this));
					}.bind(this),
					title: Language.get('wcf.media.setCategory')
				},
				source: null
			}
		},
		
		/**
		 * Handles successful clipboard actions.
		 * 
		 * @param	{object}	actionData
		 */
		_clipboardAction: function(actionData) {
			var mediaIds = actionData.data.parameters.objectIDs;
			
			switch (actionData.data.actionName) {
				case 'com.woltlab.wcf.media.delete':
					// only consider events if the action has been executed
					if (actionData.responseData !== null) {
						_mediaManager.clipboardDeleteMedia(mediaIds);
					}
					
					break;
					
				case 'com.woltlab.wcf.media.insert':
					_mediaManager.clipboardInsertMedia(mediaIds);
					
					break;
					
				case 'com.woltlab.wcf.media.setCategory':
					_clipboardObjectIds = mediaIds;
					
					Ajax.api(this, {
						actionName: 'getSetCategoryDialog'
					});
					
					break;
			}
		},
		
		/**
		 * Sets the category of the marked media files.
		 * 
		 * @param	{int}		categoryID	selected category id
		 */
		_setCategory: function(categoryID) {
			Ajax.api(this, {
				actionName: 'setCategory',
				objectIDs: _clipboardObjectIds,
				parameters: {
					categoryID: categoryID
				}
			});
		}
	}
});
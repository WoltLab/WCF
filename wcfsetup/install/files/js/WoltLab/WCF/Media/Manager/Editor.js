/**
 * Provides the media manager dialog for selecting media for input elements.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Media/Manager/Editor
 */
define(['Core', 'Dom/Traverse', 'Language', 'Ui/Dialog', 'WoltLab/WCF/Media/Manager/Base'], function(Core, DomTraverse, Language, UiDialog, MediaManagerBase) {
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
		
		this._activeButton = null;
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
		 * @see	WoltLab/WCF/Media/Manager/Base#_click
		 */
		_click: function(event) {
			this._activeButton = event.currentTarget;
			
			MediaManagerEditor._super.prototype._click.call(this, event);
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
					submitButton.addEventListener(WCF_CLICK_EVENT, this._insertMedia.bind(this));
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
			var smallButtons = elBySel('> nav.buttonGroupNavigation > ul.smallButtons', mediaElement);
			
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

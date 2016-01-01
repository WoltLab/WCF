/**
 * Provides the media manager dialog for selecting media for input elements.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Media/Manager/Select
 */
define(['Core', 'Dom/Traverse', 'Language', 'Ui/Dialog', 'WoltLab/WCF/Media/Manager/Base'], function(Core, DomTraverse, Language, UiDialog, MediaManagerBase) {
	"use strict";
	
	/**
	 * @constructor
	 */
	function MediaManagerSelect(options) {
		MediaManagerBase.call(this, options);
		
		this._activeButton = null;
		this._buttons = elByClass(this._options.buttonClass || 'jsMediaSelectButton');
		for (var i = 0, length = this._buttons.length; i < length; i++) {
			var button = this._buttons[i];
			
			// only consider buttons with a proper store specified
			var store = elData(button, 'store');
			if (store) {
				var storeElement = elById(store);
				if (storeElement && storeElement.tagName === 'INPUT') {
					this._buttons[i].addEventListener('click', this._click.bind(this));
				}
			}
		}
	};
	Core.inherit(MediaManagerSelect, MediaManagerBase, {
		/**
		 * @see	WoltLab/WCF/Media/Manager/Base#_addButtonEventListeners
		 */
		_addButtonEventListeners: function() {
			MediaManagerSelect._super.prototype._addButtonEventListeners.call(this);
			
			if (!this._mediaManagerMediaList) return;
			
			var listItems = DomTraverse.childrenByTag(this._mediaManagerMediaList, 'LI');
			for (var i = 0, length = listItems.length; i < length; i++) {
				var listItem = listItems[i];
				
				var chooseIcon = elByClass('jsMediaSelectIcon', listItem)[0];
				if (chooseIcon) {
					chooseIcon.classList.remove('jsMediaSelectIcon');
					chooseIcon.addEventListener('click', this._chooseMedia.bind(this));
				}
			}
		},
		
		/**
		 * Handles clicking on a media choose icon.
		 * 
		 * @param	{Event}		event		click event
		 */
		_chooseMedia: function(event) {
			if (this._activeButton === null) {
				throw new Error("Media cannot be chosen if no button is active.");
			}
			
			var media = this._mediaData.get(~~elData(event.currentTarget, 'object-id'));
			
			// save selected media in store element
			elById(elData(this._activeButton, 'store')).value = media.mediaID;
			
			// display selected media
			var display = elData(this._activeButton, 'display');
			if (display) {
				var displayElement = elById(display);
				if (displayElement) {
					// TODO: add visual representation of the media file to display element
					
					if (media.isImage) {
						displayElement.innerHTML = '<img src="' + media.smallThumbnailLink + '" alt="' + media.altText + '" />';
					}
				}
			}
			
			UiDialog.close('mediaManager');
		},
		
		/**
		 * @see	WoltLab/WCF/Media/Manager/Base#_click
		 */
		_click: function(event) {
			this._activeButton = event.currentTarget;
			
			MediaManagerSelect._super.prototype._click.call(this, event);
			
			// TODO: highlight selected medium?
		},
		
		/**
		 * @see	WoltLab/WCF/Media/Manager/Base#getMode
		 */
		getMode: function() {
			return 'select';
		},
		
		/**
		 * @see	WoltLab/WCF/Media/Manager/Base#setupMediaElement
		 */
		setupMediaElement: function(media, mediaElement) {
			MediaManagerSelect._super.prototype.setupMediaElement.call(this, media, mediaElement);
			
			// add media insertion icon
			var smallButtons = elBySel('nav.buttonGroupNavigation > ul.smallButtons', mediaElement);
			
			var listItem = elCreate('li');
			smallButtons.appendChild(listItem);
			
			var a = elCreate('a');
			listItem.appendChild(a);
			
			var icon = elCreate('span');
			icon.className = 'icon icon16 fa-check jsTooltip jsMediaSelectIcon';
			elData(icon, 'object-id', media.mediaID);
			elAttr(icon, 'title', Language.get('wcf.media.button.choose'));
			a.appendChild(icon);
		}
	});
	
	return MediaManagerSelect;
});

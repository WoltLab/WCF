/**
 * Uploads media files.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Controller/Media/Upload
 */
define(
	[
		'Core',                'Dom/ChangeListener', 'Dom/Traverse', 'Dom/Util',
		'EventHandler',        'Language',           'Permission',   'Upload',
		'WoltLab/WCF/File/Util'
	],
	function(
		Core,                   DomChangeListener,    DomTraverse,    DomUtil,
		EventHandler,           Language,             Permission,     Upload,
		FileUtil
	)
{
	"use strict";
	
	/**
	 * @constructor
	 */
	function MediaUpload(buttonContainerId, targetId, options) {
		options = options || {};
		
		this._mediaManager = null;
		if (options.mediaManager) {
			this._mediaManager = options.mediaManager;
			delete options.mediaManager;
		}
		
		Upload.call(this, buttonContainerId, targetId, Core.extend({
			className: 'wcf\\data\\media\\MediaAction',
			multiple: this._mediaManager ? true : false,
			singleFileRequests: true
		}, options));
	};
	Core.inherit(MediaUpload, Upload, {
		/**
		 * @see	WoltLab/WCF/Upload#_createFileElement
		 */
		_createFileElement: function(file) {
			var fileElement;
			if (this._target.nodeName === 'OL' || this._target.nodeName === 'UL') {
				fileElement = elCreate('li');
			}
			else {
				fileElement = elCreate('p');
			}
			
			var thumbnail = elCreate('div');
			thumbnail.className = 'mediaThumbnail';
			fileElement.appendChild(thumbnail);
			
			var fileIcon = elCreate('span');
			fileIcon.className = 'icon icon96 fa-spinner';
			thumbnail.appendChild(fileIcon);
			
			var mediaInformation = elCreate('div');
			mediaInformation.className = 'mediaInformation';
			fileElement.appendChild(mediaInformation);
			
			var p = elCreate('p');
			p.className = 'mediaTitle';
			p.textContent = file.name;
			mediaInformation.appendChild(p);
			
			var progress = elCreate('progress');
			elAttr(progress, 'max', 100);
			mediaInformation.appendChild(progress);
			
			DomUtil.prepend(fileElement, this._target);
			
			DomChangeListener.trigger();
			
			return fileElement;
		},
		
		/**
		 * @see	WoltLab/WCF/Upload#_success
		 */
		_success: function(uploadId, data) {
			var files = this._fileElements[uploadId];
			
			for (var i = 0, length = files.length; i < length; i++) {
				var file = files[i];
				var internalFileId = elData(file, 'internal-file-id');
				var media = data.returnValues.media[internalFileId];
				
				if (media) {
					var fileIcon = DomTraverse.childByTag(DomTraverse.childByClass(file, 'mediaThumbnail'), 'SPAN');
					if (media.tinyThumbnailType) {
						var parentNode = fileIcon.parentNode;
						elRemove(fileIcon);
						
						var img = elCreate('img');
						elAttr(img, 'src', media.tinyThumbnailLink);
						elAttr(img, 'alt', '');
						img.style.setProperty('width', '96px');
						img.style.setProperty('height', '96px');
						parentNode.appendChild(img);
					}
					else {
						fileIcon.classList.remove('fa-spinner');
						fileIcon.classList.add(FileUtil.getIconClassByMimeType(media.fileType));
					}
					
					file.className = 'jsClipboardObject';
					elData(file, 'object-id', media.mediaID);
					
					var mediaInformation = DomTraverse.childByClass(file, 'mediaInformation');
					
					elRemove(DomTraverse.childByTag(mediaInformation, 'PROGRESS'));
					
					if (this._mediaManager) {
						var buttonGroupNavigation = elCreate('nav');
						buttonGroupNavigation.className = 'buttonGroupNavigation';
						mediaInformation.parentNode.appendChild(buttonGroupNavigation);
						
						var smallButtons = elCreate('ul');
						smallButtons.className = 'smallButtons buttonGroup';
						buttonGroupNavigation.appendChild(smallButtons);
						
						var listItem = elCreate('li');
						smallButtons.appendChild(listItem);
						
						var checkbox = elCreate('input');
						checkbox.className = 'jsClipboardItem jsMediaCheckbox';
						elAttr(checkbox, 'type', 'checkbox');
						elData(checkbox, 'object-id', media.mediaID);
						listItem.appendChild(checkbox);
						
						if (Permission.get('admin.content.cms.canManageMedia')) {
							listItem = elCreate('li');
							smallButtons.appendChild(listItem);
							
							var a = elCreate('a');
							listItem.appendChild(a);
							
							var icon = elCreate('span');
							icon.className = 'icon icon16 fa-pencil jsTooltip jsMediaEditIcon';
							elData(icon, 'object-id', media.mediaID);
							elAttr(icon, 'title', Language.get('wcf.global.button.edit'));
							a.appendChild(icon);
							
							listItem = elCreate('li');
							smallButtons.appendChild(listItem);
							
							a = elCreate('a');
							listItem.appendChild(a);
							
							icon = elCreate('span');
							icon.className = 'icon icon16 fa-times jsTooltip jsMediaDeleteIcon';
							elData(icon, 'object-id', media.mediaID);
							elAttr(icon, 'title', Language.get('wcf.global.button.delete'));
							a.appendChild(icon);
						}
						
						listItem = elCreate('li');
						smallButtons.appendChild(listItem);
						
						var a = elCreate('a');
						listItem.appendChild(a);
						
						var icon = elCreate('span');
						icon.className = 'icon icon16 fa-plus jsTooltip jsMediaInsertIcon';
						elData(icon, 'object-id', media.mediaID);
						elAttr(icon, 'title', Language.get('wcf.media.button.insert'));
						a.appendChild(icon);
						
						this._mediaManager.resetMedia();
						this._mediaManager.addMedia(media, file);
					}
					
					DomChangeListener.trigger();
				}
				else {
					// error: TODO
				}
			}
			
			EventHandler.fire('com.woltlab.wcf.media.upload', 'success', {
				files: files,
				media: data.returnValues.media,
				upload: this
			});
		}
	});
	
	return MediaUpload;
});

/**
 * Uploads media files.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Media/Upload
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
			fileIcon.className = 'icon icon144 fa-spinner';
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
		 * @see	WoltLab/WCF/Upload#_getParameters
		 */
		_getParameters: function() {
			if (this._mediaManager) {
				return Core.extend(MediaUpload._super.prototype._getParameters.call(this), {
					fileTypeFilters: this._mediaManager.getOption('fileTypeFilters')
				});
			}
			
			return MediaUpload._super.prototype._getParameters.call(this);
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
				
				elRemove(DomTraverse.childByTag(DomTraverse.childByClass(file, 'mediaInformation'), 'PROGRESS'));
				
				if (media) {
					var fileIcon = DomTraverse.childByTag(DomTraverse.childByClass(file, 'mediaThumbnail'), 'SPAN');
					if (media.tinyThumbnailType) {
						var parentNode = fileIcon.parentNode;
						elRemove(fileIcon);
						
						var img = elCreate('img');
						elAttr(img, 'src', media.tinyThumbnailLink);
						elAttr(img, 'alt', '');
						img.style.setProperty('width', '144px');
						img.style.setProperty('height', '144px');
						parentNode.appendChild(img);
					}
					else {
						fileIcon.classList.remove('fa-spinner');
						fileIcon.classList.add(FileUtil.getIconClassByMimeType(media.fileType));
					}
					
					file.className = 'jsClipboardObject';
					elData(file, 'object-id', media.mediaID);
					
					if (this._mediaManager) {
						this._mediaManager.setupMediaElement(media, file);
						this._mediaManager.resetMedia();
						this._mediaManager.addMedia(media, file);
					}
				}
				else {
					var error = data.returnValues.errors[internalFileId];
					if (!error) {
						error = {
							errorType: 'uploadFailed',
							filename: elData(file, 'filename')
						};
					}
					
					var fileIcon = DomTraverse.childByTag(DomTraverse.childByClass(file, 'mediaThumbnail'), 'SPAN');
					fileIcon.classList.remove('fa-spinner');
					fileIcon.classList.add('fa-remove');
					fileIcon.classList.add('pointer');
					
					file.classList.add('uploadFailed');
					file.addEventListener(WCF_CLICK_EVENT, function() {
						elRemove(this);
					});
					
					var title = DomTraverse.childByClass(DomTraverse.childByClass(file, 'mediaInformation'), 'mediaTitle');
					title.innerText = Language.get('wcf.media.upload.error.' + error.errorType, {
						filename: error.filename
					});
				}
				
				DomChangeListener.trigger();
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

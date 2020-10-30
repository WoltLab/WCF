/**
 * Uploads media files.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Media/Upload
 */
define([
    'Core',
    'DateUtil',
    'Dom/ChangeListener',
    'Dom/Traverse',
    'Dom/Util',
    'EventHandler',
    'Language',
    'Permission',
    'Upload',
    'User',
    'WoltLabSuite/Core/FileUtil'
], function (Core, DateUtil, DomChangeListener, DomTraverse, DomUtil, EventHandler, Language, Permission, Upload, User, FileUtil) {
    "use strict";
    if (!COMPILER_TARGET_DEFAULT) {
        var Fake = function () { };
        Fake.prototype = {
            _createFileElement: function () { },
            _getParameters: function () { },
            _success: function () { },
            _uploadFiles: function () { },
            _createButton: function () { },
            _createFileElements: function () { },
            _failure: function () { },
            _insertButton: function () { },
            _progress: function () { },
            _removeButton: function () { },
            _upload: function () { }
        };
        return Fake;
    }
    /**
     * @constructor
     */
    function MediaUpload(buttonContainerId, targetId, options) {
        options = options || {};
        this._elementTagSize = 144;
        if (options.elementTagSize) {
            this._elementTagSize = options.elementTagSize;
        }
        this._mediaManager = null;
        if (options.mediaManager) {
            this._mediaManager = options.mediaManager;
            delete options.mediaManager;
        }
        this._categoryId = null;
        Upload.call(this, buttonContainerId, targetId, Core.extend({
            className: 'wcf\\data\\media\\MediaAction',
            multiple: this._mediaManager ? true : false,
            singleFileRequests: true
        }, options));
    }
    Core.inherit(MediaUpload, Upload, {
        /**
         * @see	WoltLabSuite/Core/Upload#_createFileElement
         */
        _createFileElement: function (file) {
            var fileElement;
            if (this._target.nodeName === 'OL' || this._target.nodeName === 'UL') {
                fileElement = elCreate('li');
            }
            else if (this._target.nodeName === 'TBODY') {
                var firstTr = elByTag('TR', this._target)[0];
                var tableContainer = this._target.parentNode.parentNode;
                if (tableContainer.style.getPropertyValue('display') === 'none') {
                    fileElement = firstTr;
                    tableContainer.style.removeProperty('display');
                    elRemove(elById(elData(this._target, 'no-items-info')));
                }
                else {
                    fileElement = firstTr.cloneNode(true);
                    // regenerate id of table row
                    fileElement.removeAttribute('id');
                    DomUtil.identify(fileElement);
                }
                var cells = elByTag('TD', fileElement), cell;
                for (var i = 0, length = cells.length; i < length; i++) {
                    cell = cells[i];
                    if (cell.classList.contains('columnMark')) {
                        elBySelAll('[data-object-id]', cell, elHide);
                    }
                    else if (cell.classList.contains('columnIcon')) {
                        elBySelAll('[data-object-id]', cell, elHide);
                        elByClass('mediaEditButton', cell)[0].classList.add('jsMediaEditButton');
                        elData(elByClass('jsDeleteButton', cell)[0], 'confirm-message-html', Language.get('wcf.media.delete.confirmMessage', {
                            title: file.name
                        }));
                    }
                    else if (cell.classList.contains('columnFilename')) {
                        // replace copied image with spinner
                        var image = elByTag('IMG', cell);
                        if (!image.length) {
                            image = elByClass('icon48', cell);
                        }
                        var spinner = elCreate('span');
                        spinner.className = 'icon icon48 fa-spinner mediaThumbnail';
                        DomUtil.replaceElement(image[0], spinner);
                        // replace title and uploading user
                        var ps = elBySelAll('.box48 > div > p', cell);
                        ps[0].textContent = file.name;
                        var userLink = elByTag('A', ps[1])[0];
                        if (!userLink) {
                            userLink = elCreate('a');
                            elByTag('SMALL', ps[1])[0].appendChild(userLink);
                        }
                        userLink.setAttribute('href', User.getLink());
                        userLink.textContent = User.username;
                    }
                    else if (cell.classList.contains('columnUploadTime')) {
                        cell.innerHTML = '';
                        cell.appendChild(DateUtil.getTimeElement(new Date()));
                    }
                    else if (cell.classList.contains('columnDigits')) {
                        cell.textContent = FileUtil.formatFilesize(file.size);
                    }
                    else {
                        // empty the other cells
                        cell.innerHTML = '';
                    }
                }
                DomUtil.prepend(fileElement, this._target);
                return fileElement;
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
         * @see	WoltLabSuite/Core/Upload#_getParameters
         */
        _getParameters: function () {
            var parameters = {
                elementTagSize: this._elementTagSize
            };
            if (this._mediaManager) {
                parameters.imagesOnly = this._mediaManager.getOption('imagesOnly');
                var categoryId = this._mediaManager.getCategoryId();
                if (categoryId) {
                    parameters.categoryID = categoryId;
                }
            }
            return Core.extend(MediaUpload._super.prototype._getParameters.call(this), parameters);
        },
        /**
         * Replaces the default or copied file icon with the actual file icon.
         *
         * @param	{HTMLElement}	fileIcon	file icon element
         * @param	{object}	media		media data
         * @param	{integer}	size		size of the file icon in pixels
         */
        _replaceFileIcon: function (fileIcon, media, size) {
            if (media.elementTag) {
                fileIcon.parentElement.innerHTML = media.elementTag;
            }
            else if (media.tinyThumbnailType) {
                var img = elCreate('img');
                elAttr(img, 'src', media.tinyThumbnailLink);
                elAttr(img, 'alt', '');
                img.style.setProperty('width', size + 'px');
                img.style.setProperty('height', size + 'px');
                DomUtil.replaceElement(fileIcon, img);
            }
            else {
                fileIcon.classList.remove('fa-spinner');
                var fileIconName = FileUtil.getIconNameByFilename(media.filename);
                if (fileIconName) {
                    fileIconName = '-' + fileIconName;
                }
                fileIcon.classList.add('fa-file' + fileIconName + '-o');
            }
        },
        /**
         * @see	WoltLabSuite/Core/Upload#_success
         */
        _success: function (uploadId, data) {
            var files = this._fileElements[uploadId];
            for (var i = 0, length = files.length; i < length; i++) {
                var file = files[i];
                var internalFileId = elData(file, 'internal-file-id');
                var media = data.returnValues.media[internalFileId];
                if (file.tagName === 'TR') {
                    if (media) {
                        // update object id
                        var objectIdElements = elBySelAll('[data-object-id]', file);
                        for (var i = 0, length = objectIdElements.length; i < length; i++) {
                            elData(objectIdElements[i], 'object-id', ~~media.mediaID);
                            elShow(objectIdElements[i]);
                        }
                        elByClass('columnMediaID', file)[0].textContent = media.mediaID;
                        // update icon
                        var fileIcon = elByClass('fa-spinner', file)[0];
                        this._replaceFileIcon(fileIcon, media, 48);
                    }
                    else {
                        var error = data.returnValues.errors[internalFileId];
                        if (!error) {
                            error = {
                                errorType: 'uploadFailed',
                                filename: elData(file, 'filename')
                            };
                        }
                        var fileIcon = elByClass('fa-spinner', file)[0];
                        fileIcon.classList.remove('fa-spinner');
                        fileIcon.classList.add('fa-remove');
                        fileIcon.classList.add('pointer');
                        fileIcon.classList.add('jsTooltip');
                        elAttr(fileIcon, 'title', Language.get('wcf.global.button.delete'));
                        fileIcon.addEventListener('click', function (event) {
                            elRemove(event.currentTarget.parentNode.parentNode.parentNode);
                            EventHandler.fire('com.woltlab.wcf.media.upload', 'removedErroneousUploadRow');
                        });
                        file.classList.add('uploadFailed');
                        var p = elBySelAll('.columnFilename .box48 > div > p', file)[1];
                        elInnerError(p, Language.get('wcf.media.upload.error.' + error.errorType, {
                            filename: error.filename
                        }));
                        elRemove(p);
                    }
                }
                else {
                    elRemove(DomTraverse.childByTag(DomTraverse.childByClass(file, 'mediaInformation'), 'PROGRESS'));
                    if (media) {
                        var fileIcon = DomTraverse.childByTag(DomTraverse.childByClass(file, 'mediaThumbnail'), 'SPAN');
                        this._replaceFileIcon(fileIcon, media, 144);
                        file.className = 'jsClipboardObject mediaFile';
                        elData(file, 'object-id', media.mediaID);
                        if (this._mediaManager) {
                            this._mediaManager.setupMediaElement(media, file);
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
                        file.classList.add('jsTooltip');
                        elAttr(file, 'title', Language.get('wcf.global.button.delete'));
                        file.addEventListener('click', function () {
                            elRemove(this);
                        });
                        var title = DomTraverse.childByClass(DomTraverse.childByClass(file, 'mediaInformation'), 'mediaTitle');
                        title.innerText = Language.get('wcf.media.upload.error.' + error.errorType, {
                            filename: error.filename
                        });
                    }
                }
                DomChangeListener.trigger();
            }
            EventHandler.fire('com.woltlab.wcf.media.upload', 'success', {
                files: files,
                isMultiFileUpload: this._multiFileUploadIds.indexOf(uploadId) !== -1,
                media: data.returnValues.media,
                upload: this,
                uploadId: uploadId
            });
        },
        /**
         * @see	WoltLabSuite/Core/Upload#_uploadFiles
         */
        _uploadFiles: function (files, blob) {
            return MediaUpload._super.prototype._uploadFiles.call(this, files, blob);
        }
    });
    return MediaUpload;
});

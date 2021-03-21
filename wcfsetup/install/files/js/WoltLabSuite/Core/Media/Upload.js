/**
 * Uploads media files.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Media/Upload
 */
define(["require", "exports", "tslib", "../Upload", "../Core", "../Dom/Util", "../Dom/Traverse", "../Language", "../User", "../Date/Util", "../FileUtil", "../Dom/Change/Listener", "../Event/Handler"], function (require, exports, tslib_1, Upload_1, Core, DomUtil, DomTraverse, Language, User_1, DateUtil, FileUtil, DomChangeListener, EventHandler) {
    "use strict";
    Upload_1 = tslib_1.__importDefault(Upload_1);
    Core = tslib_1.__importStar(Core);
    DomUtil = tslib_1.__importStar(DomUtil);
    DomTraverse = tslib_1.__importStar(DomTraverse);
    Language = tslib_1.__importStar(Language);
    User_1 = tslib_1.__importDefault(User_1);
    DateUtil = tslib_1.__importStar(DateUtil);
    FileUtil = tslib_1.__importStar(FileUtil);
    DomChangeListener = tslib_1.__importStar(DomChangeListener);
    EventHandler = tslib_1.__importStar(EventHandler);
    class MediaUpload extends Upload_1.default {
        constructor(buttonContainerId, targetId, options) {
            super(buttonContainerId, targetId, Core.extend({
                className: "wcf\\data\\media\\MediaAction",
                multiple: options.mediaManager ? true : false,
                singleFileRequests: true,
            }, options || {}));
            this._categoryId = null;
            options = options || {};
            this._elementTagSize = 144;
            if (this._options.elementTagSize) {
                this._elementTagSize = this._options.elementTagSize;
            }
            this._mediaManager = null;
            if (this._options.mediaManager) {
                this._mediaManager = this._options.mediaManager;
                delete this._options.mediaManager;
            }
        }
        _createFileElement(file) {
            let fileElement;
            if (this._target.nodeName === "OL" || this._target.nodeName === "UL") {
                fileElement = document.createElement("li");
            }
            else if (this._target.nodeName === "TBODY") {
                const firstTr = this._target.getElementsByTagName("TR")[0];
                const tableContainer = this._target.parentNode.parentNode;
                if (tableContainer.style.getPropertyValue("display") === "none") {
                    fileElement = firstTr;
                    tableContainer.style.removeProperty("display");
                    document.getElementById(this._target.dataset.noItemsInfo).remove();
                }
                else {
                    fileElement = firstTr.cloneNode(true);
                    // regenerate id of table row
                    fileElement.removeAttribute("id");
                    DomUtil.identify(fileElement);
                }
                Array.from(fileElement.getElementsByTagName("TD")).forEach((cell) => {
                    if (cell.classList.contains("columnMark")) {
                        cell.querySelectorAll("[data-object-id]").forEach((el) => DomUtil.hide(el));
                    }
                    else if (cell.classList.contains("columnIcon")) {
                        cell.querySelectorAll("[data-object-id]").forEach((el) => DomUtil.hide(el));
                        cell.querySelector(".mediaEditButton").classList.add("jsMediaEditButton");
                        cell.querySelector(".jsObjectAction[data-object-action='delete']").dataset.confirmMessage = Language.get("wcf.media.delete.confirmMessage", {
                            title: file.name,
                        });
                    }
                    else if (cell.classList.contains("columnFilename")) {
                        // replace copied image with spinner
                        let image = cell.querySelector("img");
                        if (!image) {
                            image = cell.querySelector(".icon48");
                        }
                        const spinner = document.createElement("span");
                        spinner.className = "icon icon48 fa-spinner mediaThumbnail";
                        DomUtil.replaceElement(image, spinner);
                        // replace title and uploading user
                        const ps = cell.querySelectorAll(".box48 > div > p");
                        ps[0].textContent = file.name;
                        let userLink = ps[1].getElementsByTagName("A")[0];
                        if (!userLink) {
                            userLink = document.createElement("a");
                            ps[1].getElementsByTagName("SMALL")[0].appendChild(userLink);
                        }
                        userLink.setAttribute("href", User_1.default.getLink());
                        userLink.textContent = User_1.default.username;
                    }
                    else if (cell.classList.contains("columnUploadTime")) {
                        cell.innerHTML = "";
                        cell.appendChild(DateUtil.getTimeElement(new Date()));
                    }
                    else if (cell.classList.contains("columnDigits")) {
                        cell.textContent = FileUtil.formatFilesize(file.size);
                    }
                    else {
                        // empty the other cells
                        cell.innerHTML = "";
                    }
                });
                DomUtil.prepend(fileElement, this._target);
                return fileElement;
            }
            else {
                fileElement = document.createElement("p");
            }
            const thumbnail = document.createElement("div");
            thumbnail.className = "mediaThumbnail";
            fileElement.appendChild(thumbnail);
            const fileIcon = document.createElement("span");
            fileIcon.className = "icon icon144 fa-spinner";
            thumbnail.appendChild(fileIcon);
            const mediaInformation = document.createElement("div");
            mediaInformation.className = "mediaInformation";
            fileElement.appendChild(mediaInformation);
            const p = document.createElement("p");
            p.className = "mediaTitle";
            p.textContent = file.name;
            mediaInformation.appendChild(p);
            const progress = document.createElement("progress");
            progress.max = 100;
            mediaInformation.appendChild(progress);
            DomUtil.prepend(fileElement, this._target);
            DomChangeListener.trigger();
            return fileElement;
        }
        _getParameters() {
            const parameters = {
                elementTagSize: this._elementTagSize,
            };
            if (this._mediaManager) {
                parameters.imagesOnly = this._mediaManager.getOption("imagesOnly");
                const categoryId = this._mediaManager.getCategoryId();
                if (categoryId) {
                    parameters.categoryID = categoryId;
                }
            }
            return Core.extend(super._getParameters(), parameters);
        }
        _replaceFileIcon(fileIcon, media, size) {
            if (media.elementTag) {
                fileIcon.outerHTML = media.elementTag;
            }
            else if (media.tinyThumbnailType) {
                const img = document.createElement("img");
                img.src = media.tinyThumbnailLink;
                img.alt = "";
                img.style.setProperty("width", `${size}px`);
                img.style.setProperty("height", `${size}px`);
                DomUtil.replaceElement(fileIcon, img);
            }
            else {
                fileIcon.classList.remove("fa-spinner");
                let fileIconName = FileUtil.getIconNameByFilename(media.filename);
                if (fileIconName) {
                    fileIconName = "-" + fileIconName;
                }
                fileIcon.classList.add(`fa-file${fileIconName}-o`);
            }
        }
        _success(uploadId, data) {
            const files = this._fileElements[uploadId];
            files.forEach((file) => {
                const internalFileId = file.dataset.internalFileId;
                const media = data.returnValues.media[internalFileId];
                if (file.tagName === "TR") {
                    if (media) {
                        // update object id
                        file.dataset.objectId = media.mediaID.toString();
                        file.querySelectorAll("[data-object-id]").forEach((el) => {
                            el.dataset.objectId = media.mediaID.toString();
                            el.style.removeProperty("display");
                        });
                        file.querySelector(".columnMediaID").textContent = media.mediaID.toString();
                        // update icon
                        this._replaceFileIcon(file.querySelector(".fa-spinner"), media, 48);
                    }
                    else {
                        let error = data.returnValues.errors[internalFileId];
                        if (!error) {
                            error = {
                                errorType: "uploadFailed",
                                filename: file.dataset.filename,
                            };
                        }
                        const fileIcon = file.querySelector(".fa-spinner");
                        fileIcon.classList.remove("fa-spinner");
                        fileIcon.classList.add("fa-remove", "pointer", "jsTooltip");
                        fileIcon.title = Language.get("wcf.global.button.delete");
                        fileIcon.addEventListener("click", (event) => {
                            const target = event.currentTarget;
                            target.closest(".mediaFile").remove();
                            EventHandler.fire("com.woltlab.wcf.media.upload", "removedErroneousUploadRow");
                        });
                        file.classList.add("uploadFailed");
                        const p = file.querySelectorAll(".columnFilename .box48 > div > p")[1];
                        DomUtil.innerError(p, Language.get(`wcf.media.upload.error.${error.errorType}`, {
                            filename: error.filename,
                        }));
                        p.remove();
                    }
                }
                else {
                    DomTraverse.childByTag(DomTraverse.childByClass(file, "mediaInformation"), "PROGRESS").remove();
                    if (media) {
                        const fileIcon = DomTraverse.childByTag(DomTraverse.childByClass(file, "mediaThumbnail"), "SPAN");
                        this._replaceFileIcon(fileIcon, media, 144);
                        file.classList.add("jsClipboardObject", "mediaFile", "jsObjectActionObject");
                        file.dataset.objectId = media.mediaID.toString();
                        if (this._mediaManager) {
                            this._mediaManager.setupMediaElement(media, file);
                            this._mediaManager.addMedia(media, file);
                        }
                    }
                    else {
                        let error = data.returnValues.errors[internalFileId];
                        if (!error) {
                            error = {
                                errorType: "uploadFailed",
                                filename: file.dataset.filename,
                            };
                        }
                        const fileIcon = DomTraverse.childByTag(DomTraverse.childByClass(file, "mediaThumbnail"), "SPAN");
                        fileIcon.classList.remove("fa-spinner");
                        fileIcon.classList.add("fa-remove", "pointer");
                        file.classList.add("uploadFailed", "jsTooltip");
                        file.title = Language.get("wcf.global.button.delete");
                        file.addEventListener("click", () => file.remove());
                        const title = DomTraverse.childByClass(DomTraverse.childByClass(file, "mediaInformation"), "mediaTitle");
                        title.innerText = Language.get(`wcf.media.upload.error.${error.errorType}`, {
                            filename: error.filename,
                        });
                    }
                }
                DomChangeListener.trigger();
            });
            EventHandler.fire("com.woltlab.wcf.media.upload", "success", {
                files: files,
                isMultiFileUpload: this._multiFileUploadIds.indexOf(uploadId) !== -1,
                media: data.returnValues.media,
                upload: this,
                uploadId: uploadId,
            });
        }
    }
    Core.enableLegacyInheritance(MediaUpload);
    return MediaUpload;
});

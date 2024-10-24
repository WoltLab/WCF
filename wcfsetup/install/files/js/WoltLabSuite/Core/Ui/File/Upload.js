/**
 * Uploads file via AJAX.
 *
 * @author  Joshua Ruesweg, Matthias Schmidt
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since  5.2
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "tslib", "../../Core", "./Delete", "../../Dom/Util", "../../Language", "../../Upload"], function (require, exports, tslib_1, Core, Delete_1, Util_1, Language, Upload_1) {
    "use strict";
    Core = tslib_1.__importStar(Core);
    Delete_1 = tslib_1.__importDefault(Delete_1);
    Util_1 = tslib_1.__importDefault(Util_1);
    Language = tslib_1.__importStar(Language);
    Upload_1 = tslib_1.__importDefault(Upload_1);
    class FileUpload extends Upload_1.default {
        _deleteHandler;
        constructor(buttonContainerId, targetId, options) {
            options = options || {};
            if (options.internalId === undefined) {
                throw new Error("Missing internal id.");
            }
            // set default options
            options = Core.extend({
                // image preview
                imagePreview: false,
                // max files
                maxFiles: null,
                // Dummy value, because it is checked in the base method, without using it with this upload handler.
                className: "invalid",
                // url
                url: `index.php?ajax-file-upload/&t=${Core.getXsrfToken()}`,
            }, options);
            options.multiple = options.maxFiles === null || options.maxFiles > 1;
            super(buttonContainerId, targetId, options);
            this.checkMaxFiles();
            this._deleteHandler = new Delete_1.default(buttonContainerId, targetId, this._options.imagePreview, this);
        }
        _createFileElement(file) {
            const element = super._createFileElement(file);
            element.classList.add("box64", "uploadedFile");
            const progress = element.querySelector("progress");
            const icon = document.createElement("fa-icon");
            icon.size = 64;
            icon.setIcon("spinner");
            const fileName = element.textContent;
            element.textContent = "";
            element.append(icon);
            const innerDiv = document.createElement("div");
            const fileNameP = document.createElement("p");
            fileNameP.textContent = fileName; // file.name
            const smallProgress = document.createElement("small");
            smallProgress.appendChild(progress);
            innerDiv.appendChild(fileNameP);
            innerDiv.appendChild(smallProgress);
            const div = document.createElement("div");
            div.appendChild(innerDiv);
            const ul = document.createElement("ul");
            ul.className = "buttonGroup";
            div.appendChild(ul);
            // reset element textContent and replace with own element style
            element.append(div);
            return element;
        }
        _failure(uploadId, data) {
            this._fileElements[uploadId].forEach((fileElement) => {
                fileElement.classList.add("uploadFailed");
                const small = fileElement.querySelector("small");
                small.innerHTML = "";
                const icon = fileElement.querySelector("fa-icon");
                icon.setIcon("ban");
                const innerError = document.createElement("span");
                innerError.className = "innerError";
                innerError.textContent = Language.get("wcf.upload.error.uploadFailed");
                small.insertAdjacentElement("afterend", innerError);
            });
            throw new Error(`Upload failed: ${data.message}`);
        }
        _upload(event, file, blob) {
            const parent = this._buttonContainer.parentElement;
            const innerError = parent.querySelector("small.innerError:not(.innerFileError)");
            if (innerError) {
                innerError.remove();
            }
            return super._upload(event, file, blob);
        }
        _success(uploadId, data) {
            this._fileElements[uploadId].forEach((fileElement, index) => {
                if (data.files[index] !== undefined) {
                    const fileData = data.files[index];
                    if (this._options.imagePreview) {
                        if (fileData.image === null) {
                            throw new Error("Expect image for uploaded file. None given.");
                        }
                        fileElement.remove();
                        const previewImage = this._target.querySelector("img.previewImage");
                        if (previewImage !== null) {
                            previewImage.src = fileData.image;
                        }
                        else {
                            const image = document.createElement("img");
                            image.classList.add("previewImage");
                            image.src = fileData.image;
                            image.style.setProperty("max-width", "100%", "");
                            image.dataset.uniqueFileId = fileData.uniqueFileId;
                            this._target.appendChild(image);
                        }
                    }
                    else {
                        fileElement.dataset.uniqueFileId = fileData.uniqueFileId;
                        fileElement.querySelector("small").textContent = fileData.filesize.toString();
                        const icon = fileElement.querySelector("fa-icon");
                        if (fileData.image !== null) {
                            const a = document.createElement("a");
                            a.dataset.fancybox = "";
                            a.href = fileData.image;
                            const image = document.createElement("img");
                            image.classList.add("formUploadHandlerContentListImage");
                            image.src = fileData.image;
                            image.width = fileData.imageWidth;
                            image.height = fileData.imageHeight;
                            a.appendChild(image);
                            icon.replaceWith(a);
                        }
                        else {
                            icon.setIcon(fileData.icon, fileData.icon === "paperclip");
                        }
                    }
                }
                else if (data.error[index] !== undefined) {
                    const errorData = data["error"][index];
                    fileElement.classList.add("uploadFailed");
                    const small = fileElement.querySelector("small");
                    small.innerHTML = "";
                    const icon = fileElement.querySelector("fa-icon");
                    icon.setIcon("ban");
                    let innerError = fileElement.querySelector(".innerError");
                    if (innerError === null) {
                        innerError = document.createElement("span");
                        innerError.className = "innerError";
                        innerError.textContent = errorData.errorMessage;
                        small.insertAdjacentElement("afterend", innerError);
                    }
                    else {
                        innerError.textContent = errorData.errorMessage;
                    }
                }
                else {
                    throw new Error(`Unknown uploaded file for uploadId ${uploadId}.`);
                }
            });
            // create delete buttons
            this._deleteHandler.rebuild();
            this.checkMaxFiles();
            Core.triggerEvent(this._target, "change");
        }
        _getFormData() {
            return {
                internalId: this._options.internalId,
            };
        }
        validateUpload(files) {
            if (this._options.maxFiles === null || files.length + this.countFiles() <= this._options.maxFiles) {
                return true;
            }
            else {
                const parent = this._buttonContainer.parentElement;
                let innerError = parent.querySelector("small.innerError:not(.innerFileError)");
                if (innerError === null) {
                    innerError = document.createElement("small");
                    innerError.className = "innerError";
                    this._buttonContainer.insertAdjacentElement("afterend", innerError);
                }
                innerError.textContent = Language.get("wcf.upload.error.reachedRemainingLimit", {
                    maxFiles: this._options.maxFiles - this.countFiles(),
                });
                return false;
            }
        }
        /**
         * Returns the count of the uploaded images.
         */
        countFiles() {
            if (this._options.imagePreview) {
                return this._target.querySelector("img") !== null ? 1 : 0;
            }
            else {
                return this._target.childElementCount;
            }
        }
        /**
         * Checks the maximum number of files and enables or disables the upload button.
         */
        checkMaxFiles() {
            if (this._options.maxFiles !== null && this.countFiles() >= this._options.maxFiles) {
                Util_1.default.hide(this._button);
            }
            else {
                Util_1.default.show(this._button);
            }
        }
    }
    return FileUpload;
});

/**
 * Uploads file via AJAX.
 *
 * @author  Matthias Schmidt
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  Upload (alias)
 * @module  WoltLabSuite/Core/Upload
 */
define(["require", "exports", "tslib", "./Ajax/Request", "./Core", "./Dom/Change/Listener", "./Language"], function (require, exports, tslib_1, Request_1, Core, Listener_1, Language) {
    "use strict";
    Request_1 = tslib_1.__importDefault(Request_1);
    Core = tslib_1.__importStar(Core);
    Listener_1 = tslib_1.__importDefault(Listener_1);
    Language = tslib_1.__importStar(Language);
    class Upload {
        constructor(buttonContainerId, targetId, options) {
            this._button = document.createElement("p");
            this._fileElements = [];
            this._fileUpload = document.createElement("input");
            this._internalFileId = 0;
            this._multiFileUploadIds = [];
            options = options || {};
            if (!options.className) {
                throw new Error("Missing class name.");
            }
            // set default options
            this._options = Core.extend({
                // name of the PHP action
                action: "upload",
                // is true if multiple files can be uploaded at once
                multiple: false,
                // array of acceptable file types, null if any file type is acceptable
                acceptableFiles: null,
                // name of the upload field
                name: "__files[]",
                // is true if every file from a multi-file selection is uploaded in its own request
                singleFileRequests: false,
                // url for uploading file
                url: `index.php?ajax-upload/&t=${window.SECURITY_TOKEN}`,
            }, options);
            this._options.url = Core.convertLegacyUrl(this._options.url);
            if (this._options.url.indexOf("index.php") === 0) {
                this._options.url = window.WSC_API_URL + this._options.url;
            }
            const buttonContainer = document.getElementById(buttonContainerId);
            if (buttonContainer === null) {
                throw new Error(`Element id '${buttonContainerId}' is unknown.`);
            }
            this._buttonContainer = buttonContainer;
            const target = document.getElementById(targetId);
            if (target === null) {
                throw new Error(`Element id '${targetId}' is unknown.`);
            }
            this._target = target;
            if (options.multiple &&
                this._target.nodeName !== "UL" &&
                this._target.nodeName !== "OL" &&
                this._target.nodeName !== "TBODY") {
                throw new Error("Target element has to be list or table body if uploading multiple files is supported.");
            }
            this._createButton();
        }
        /**
         * Creates the upload button.
         */
        _createButton() {
            this._fileUpload.type = "file";
            this._fileUpload.name = this._options.name;
            if (this._options.multiple) {
                this._fileUpload.multiple = true;
            }
            if (this._options.acceptableFiles !== null) {
                this._fileUpload.accept = this._options.acceptableFiles.join(",");
            }
            this._fileUpload.addEventListener("change", (ev) => this._upload(ev));
            this._button.className = "button uploadButton";
            this._button.setAttribute("role", "button");
            this._fileUpload.addEventListener("focus", () => {
                if (this._fileUpload.classList.contains("focus-visible")) {
                    this._button.classList.add("active");
                }
            });
            this._fileUpload.addEventListener("blur", () => {
                this._button.classList.remove("active");
            });
            const span = document.createElement("span");
            span.textContent = Language.get("wcf.global.button.upload");
            this._button.appendChild(span);
            this._buttonContainer.insertAdjacentElement("afterbegin", this._fileUpload);
            this._insertButton();
            Listener_1.default.trigger();
        }
        /**
         * Creates the document element for an uploaded file.
         */
        _createFileElement(file) {
            const progress = document.createElement("progress");
            progress.max = 100;
            let element;
            switch (this._target.nodeName) {
                case "OL":
                case "UL":
                    element = document.createElement("li");
                    element.innerText = file.name;
                    element.appendChild(progress);
                    this._target.appendChild(element);
                    return element;
                case "TBODY":
                    return this._createFileTableRow(file);
                default:
                    element = document.createElement("p");
                    element.appendChild(progress);
                    this._target.appendChild(element);
                    return element;
            }
        }
        /**
         * Creates the document elements for uploaded files.
         */
        _createFileElements(files) {
            if (!files.length) {
                return null;
            }
            const elements = [];
            Array.from(files).forEach((file) => {
                const fileElement = this._createFileElement(file);
                if (!fileElement.classList.contains("uploadFailed")) {
                    fileElement.dataset.filename = file.name;
                    fileElement.dataset.internalFileId = (this._internalFileId++).toString();
                    elements.push(fileElement);
                }
            });
            const uploadId = this._fileElements.length;
            this._fileElements.push(elements);
            Listener_1.default.trigger();
            return uploadId;
        }
        // eslint-disable-next-line @typescript-eslint/no-unused-vars
        _createFileTableRow(file) {
            // This should be an abstract method, but cannot be marked as such for backwards compatibility.
            throw new Error("Has to be implemented in subclass.");
        }
        /**
         * Handles a failed file upload.
         */
        _failure(
        /* eslint-disable @typescript-eslint/no-unused-vars */
        uploadId, data, responseText, xhr, requestOptions
        /* eslint-enable @typescript-eslint/no-unused-vars */
        ) {
            // This should be an abstract method, but cannot be marked as such for backwards compatibility.
            return true;
        }
        /**
         * Return additional parameters for upload requests.
         */
        _getParameters() {
            return {};
        }
        /**
         * Return additional form data for upload requests.
         *
         * @since       5.2
         */
        _getFormData() {
            return {};
        }
        /**
         * Inserts the created button to upload files into the button container.
         */
        _insertButton() {
            this._buttonContainer.insertAdjacentElement("afterbegin", this._button);
        }
        /**
         * Updates the progress of an upload.
         */
        _progress(uploadId, event) {
            const percentComplete = Math.round((event.loaded / event.total) * 100);
            this._fileElements[uploadId].forEach((element) => {
                const progress = element.querySelector("progress");
                if (progress) {
                    progress.value = percentComplete;
                }
            });
        }
        /**
         * Removes the button to upload files.
         */
        _removeButton() {
            this._button.remove();
            Listener_1.default.trigger();
        }
        /**
         * Handles a successful file upload.
         */
        _success(
        /* eslint-disable @typescript-eslint/no-unused-vars */
        uploadId, data, responseText, xhr, requestOptions
        /* eslint-enable @typescript-eslint/no-unused-vars */
        ) {
            // This should be an abstract method, but cannot be marked as such for backwards compatibility.
        }
        _upload(event, file, blob) {
            // remove failed upload elements first
            this._target.querySelectorAll(".uploadFailed").forEach((el) => el.remove());
            let uploadId = null;
            let files = [];
            if (file) {
                files.push(file);
            }
            else if (blob) {
                let fileExtension = "";
                switch (blob.type) {
                    case "image/jpeg":
                        fileExtension = "jpg";
                        break;
                    case "image/gif":
                        fileExtension = "gif";
                        break;
                    case "image/png":
                        fileExtension = "png";
                        break;
                }
                files.push({
                    name: `pasted-from-clipboard.${fileExtension}`,
                });
            }
            else {
                files = Array.from(this._fileUpload.files);
            }
            if (files.length && this.validateUpload(files)) {
                if (this._options.singleFileRequests) {
                    uploadId = [];
                    files.forEach((file) => {
                        const localUploadId = this._uploadFiles([file], blob);
                        if (files.length !== 1) {
                            this._multiFileUploadIds.push(localUploadId);
                        }
                        uploadId.push(localUploadId);
                    });
                }
                else {
                    uploadId = this._uploadFiles(files, blob);
                }
            }
            // re-create upload button to effectively reset the 'files'
            // property of the input element
            this._removeButton();
            this._createButton();
            return uploadId;
        }
        /**
         * Validates the upload before uploading them.
         *
         * @since       5.2
         */
        // eslint-disable-next-line @typescript-eslint/no-unused-vars
        validateUpload(files) {
            // This should be an abstract method, but cannot be marked as such for backwards compatibility.
            return true;
        }
        /**
         * Sends the request to upload files.
         */
        _uploadFiles(files, blob) {
            const uploadId = this._createFileElements(files);
            // no more files left, abort
            if (!this._fileElements[uploadId].length) {
                return null;
            }
            const formData = new FormData();
            for (let i = 0, length = files.length; i < length; i++) {
                if (this._fileElements[uploadId][i]) {
                    const internalFileId = this._fileElements[uploadId][i].dataset.internalFileId;
                    if (blob) {
                        formData.append(`__files[${internalFileId}]`, blob, files[i].name);
                    }
                    else {
                        formData.append(`__files[${internalFileId}]`, files[i]);
                    }
                }
            }
            formData.append("actionName", this._options.action);
            formData.append("className", this._options.className);
            if (this._options.action === "upload") {
                formData.append("interfaceName", "wcf\\data\\IUploadAction");
            }
            // recursively append additional parameters to form data
            function appendFormData(parameters, prefix) {
                prefix = prefix || "";
                Object.entries(parameters).forEach(([key, value]) => {
                    if (typeof value === "object") {
                        const newPrefix = prefix.length === 0 ? key : `${prefix}[${key}]`;
                        appendFormData(value, newPrefix);
                    }
                    else {
                        const dataName = prefix.length === 0 ? key : `${prefix}[${key}]`;
                        formData.append(dataName, value);
                    }
                });
            }
            appendFormData(this._getParameters(), "parameters");
            appendFormData(this._getFormData());
            const request = new Request_1.default({
                data: formData,
                contentType: false,
                failure: this._failure.bind(this, uploadId),
                silent: true,
                success: this._success.bind(this, uploadId),
                uploadProgress: this._progress.bind(this, uploadId),
                url: this._options.url,
                withCredentials: true,
            });
            request.sendRequest();
            return uploadId;
        }
        /**
         * Returns true if there are any pending uploads handled by this
         * upload manager.
         *
         * @since  5.2
         */
        hasPendingUploads() {
            return (this._fileElements.find((elements) => {
                return elements.find((el) => el.querySelector("progress") !== null);
            }) !== undefined);
        }
        /**
         * Uploads the given file blob.
         */
        uploadBlob(blob) {
            return this._upload(null, null, blob);
        }
        /**
         * Uploads the given file.
         */
        uploadFile(file) {
            return this._upload(null, file);
        }
    }
    return Upload;
});

/**
 * @author    Olaf Braun
 * @copyright 2001-2024 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since     6.1
 */
define(["require", "exports", "tslib", "WoltLabSuite/Core/Language", "WoltLabSuite/Core/Api/Files/DeleteFile", "WoltLabSuite/Core/FileUtil", "WoltLabSuite/Core/Dom/Change/Listener"], function (require, exports, tslib_1, Language_1, DeleteFile_1, FileUtil_1, Listener_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.FileProcessor = void 0;
    Listener_1 = tslib_1.__importDefault(Listener_1);
    class FileProcessor {
        #container;
        #uploadButton;
        #fieldId;
        #replaceElement = undefined;
        #fileInput;
        #imageOnly;
        #singleFileUpload;
        #extraButtons;
        constructor(fieldId, singleFileUpload = false, imageOnly = false, extraButtons = []) {
            this.#fieldId = fieldId;
            this.#imageOnly = imageOnly;
            this.#singleFileUpload = singleFileUpload;
            this.#extraButtons = extraButtons;
            this.#container = document.getElementById(fieldId + "Container");
            if (this.#container === null) {
                throw new Error("Unknown field with id '" + fieldId + "'");
            }
            this.#uploadButton = this.#container.querySelector("woltlab-core-file-upload");
            this.#uploadButton.addEventListener("uploadStart", (event) => {
                void this.#registerFile(event.detail);
            });
            this.#fileInput = this.#uploadButton.shadowRoot.querySelector('input[type="file"]');
            this.#container.querySelectorAll("woltlab-core-file").forEach((element) => {
                void this.#registerFile(element, element.parentElement);
            });
        }
        get classPrefix() {
            return this.showBigPreview ? "fileUpload__preview__" : "fileList__";
        }
        get showBigPreview() {
            return this.#singleFileUpload && this.#imageOnly;
        }
        addButtons(container, element) {
            const buttons = document.createElement("ul");
            buttons.classList.add("buttonList");
            buttons.classList.add(this.classPrefix + "item__buttons");
            this.addDeleteButton(element, buttons);
            if (this.#singleFileUpload) {
                this.addReplaceButton(element, buttons);
            }
            this.#extraButtons.forEach((button) => {
                const extraButton = document.createElement("button");
                extraButton.type = "button";
                extraButton.classList.add("button", "small");
                if (button.icon === undefined) {
                    extraButton.textContent = button.title;
                }
                else {
                    extraButton.classList.add("jsTooltip");
                    extraButton.title = button.title;
                    extraButton.innerHTML = button.icon;
                }
                extraButton.addEventListener("click", () => {
                    element.dispatchEvent(new CustomEvent("fileProcessorCustomAction", {
                        detail: button.actionName,
                        bubbles: true,
                    }));
                });
                const listItem = document.createElement("li");
                listItem.append(extraButton);
                buttons.append(listItem);
            });
            container.append(buttons);
        }
        #markElementUploadHasFailed(container, element, reason) {
            if (reason instanceof Error) {
                throw reason;
            }
            if (element.apiError === undefined) {
                return;
            }
            let errorMessage;
            const validationError = element.apiError.getValidationError();
            if (validationError !== undefined) {
                switch (validationError.param) {
                    case "preflight":
                        errorMessage = (0, Language_1.getPhrase)(`wcf.upload.error.${validationError.code}`);
                        break;
                    default:
                        errorMessage = "Unrecognized error type: " + JSON.stringify(validationError);
                        break;
                }
            }
            else {
                errorMessage = `Unexpected server error: [${element.apiError.type}] ${element.apiError.message}`;
            }
            container.classList.add("innerError");
            const errorElement = document.createElement("div");
            errorElement.classList.add(this.classPrefix + "item__errorMessage");
            errorElement.textContent = errorMessage;
            element.append(errorElement);
        }
        addDeleteButton(element, buttons) {
            const deleteButton = document.createElement("button");
            deleteButton.type = "button";
            deleteButton.classList.add("button", "small");
            deleteButton.textContent = (0, Language_1.getPhrase)("wcf.global.button.delete");
            deleteButton.addEventListener("click", async () => {
                await (0, DeleteFile_1.deleteFile)(element.fileId);
                this.#unregisterFile(element);
            });
            const listItem = document.createElement("li");
            listItem.append(deleteButton);
            buttons.append(listItem);
        }
        addReplaceButton(element, buttons) {
            const replaceButton = document.createElement("button");
            replaceButton.type = "button";
            replaceButton.classList.add("button", "small");
            replaceButton.textContent = (0, Language_1.getPhrase)("wcf.global.button.replace");
            replaceButton.addEventListener("click", () => {
                // add to context an extra attribute that the replace button is clicked.
                // after the dialog is closed or the file is selected, the context will be reset to his old value.
                // this is necessary as the serverside validation will otherwise fail.
                const oldContext = this.#uploadButton.dataset.context;
                const context = JSON.parse(oldContext);
                context.__replace = true;
                this.#uploadButton.dataset.context = JSON.stringify(context);
                // remove the element and all buttons from the dom, but keep them stored in a variable.
                // if the user cancels the dialog or the upload fails, reinsert the old elements and show an error message.
                // if the upload is successful, delete the old file.
                this.#replaceElement = element;
                this.#unregisterFile(element);
                const changeEventListener = () => {
                    this.#uploadButton.dataset.context = oldContext;
                    this.#fileInput.removeEventListener("cancel", cancelEventListener);
                };
                const cancelEventListener = () => {
                    this.#uploadButton.dataset.context = oldContext;
                    void this.#registerFile(this.#replaceElement);
                    this.#replaceElement = undefined;
                    this.#fileInput.removeEventListener("change", changeEventListener);
                };
                this.#fileInput.addEventListener("cancel", cancelEventListener, { once: true });
                this.#fileInput.addEventListener("change", changeEventListener, { once: true });
                this.#fileInput.click();
            });
            const listItem = document.createElement("li");
            listItem.append(replaceButton);
            buttons.append(listItem);
        }
        #unregisterFile(element) {
            if (this.showBigPreview) {
                element.parentElement.innerHTML = "";
            }
            else {
                element.parentElement.parentElement.remove();
            }
        }
        #trackUploadProgress(element, file) {
            const progress = document.createElement("progress");
            progress.classList.add("fileList__item__progress__bar");
            progress.max = 100;
            const readout = document.createElement("span");
            readout.classList.add("fileList__item__progress__readout");
            file.addEventListener("uploadProgress", (event) => {
                progress.value = event.detail;
                readout.textContent = `${event.detail}%`;
                if (progress.parentNode === null) {
                    element.classList.add("fileProcessor__item--uploading");
                    const wrapper = document.createElement("div");
                    wrapper.classList.add("fileList__item__progress");
                    wrapper.append(progress, readout);
                    element.append(wrapper);
                }
            });
        }
        #removeUploadProgress(element) {
            if (!element.classList.contains("fileProcessor__item--uploading")) {
                return;
            }
            element.classList.remove("fileProcessor__item--uploading");
            element.querySelector(".fileList__item__progress")?.remove();
        }
        async #registerFile(element, container = null) {
            if (container === null) {
                if (this.showBigPreview) {
                    container = this.#container.querySelector(".fileUpload__preview");
                    if (container === null) {
                        container = document.createElement("div");
                        container.classList.add("fileUpload__preview");
                        this.#uploadButton.insertAdjacentElement("beforebegin", container);
                    }
                    container.append(element);
                }
                else {
                    container = document.createElement("li");
                    container.classList.add("fileList__item");
                    this.#container.querySelector(".fileList").append(container);
                }
            }
            if (!this.showBigPreview) {
                // create a new container for the file element
                const fileContainer = document.createElement("div");
                fileContainer.classList.add(this.classPrefix + "item__file");
                fileContainer.append(element);
                container.append(fileContainer);
                // add filename and filesize information
                const filename = document.createElement("div");
                filename.classList.add(this.classPrefix + "item__filename");
                filename.textContent = element.filename || element.dataset.filename;
                container.append(filename);
                const fileSize = document.createElement("div");
                fileSize.classList.add(this.classPrefix + "item__fileSize");
                fileSize.textContent = (0, FileUtil_1.formatFilesize)(element.fileSize || parseInt(element.dataset.fileSize));
                container.append(fileSize);
            }
            try {
                this.#trackUploadProgress(container, element);
                await element.ready;
                if (this.#replaceElement !== undefined) {
                    await (0, DeleteFile_1.deleteFile)(this.#replaceElement.fileId);
                    this.#replaceElement = undefined;
                }
            }
            catch (reason) {
                // reinsert the element and show an error message
                if (this.#replaceElement !== undefined) {
                    await this.#registerFile(this.#replaceElement);
                    this.#replaceElement = undefined;
                    if (this.showBigPreview) {
                        // move the new uploaded file to his own container
                        // otherwise the file under `this.#replaceElement` will be marked as failed, too
                        const tmpContainer = document.createElement("div");
                        tmpContainer.append(element);
                        this.#uploadButton.insertAdjacentElement("afterend", tmpContainer);
                        container = tmpContainer;
                    }
                }
                this.#markElementUploadHasFailed(container, element, reason);
                return;
            }
            finally {
                this.#removeUploadProgress(container);
            }
            if (this.showBigPreview) {
                element.dataset.previewUrl = element.link;
                element.unbounded = true;
            }
            else {
                if (element.isImage()) {
                    const thumbnail = element.thumbnails.find((thumbnail) => thumbnail.identifier === "tiny");
                    if (thumbnail !== undefined) {
                        element.thumbnail = thumbnail;
                    }
                    else {
                        element.dataset.previewUrl = element.link;
                        element.unbounded = false;
                    }
                    if (element.link !== undefined && element.filename !== undefined) {
                        const filenameLink = document.createElement("a");
                        filenameLink.href = element.link;
                        filenameLink.title = element.filename;
                        filenameLink.textContent = element.filename;
                        filenameLink.classList.add("jsImageViewer");
                        // insert a hidden image element that will be used by the image viewer as the preview image
                        const previewImage = document.createElement("img");
                        previewImage.src = thumbnail !== undefined ? thumbnail.link : element.link;
                        previewImage.alt = element.filename;
                        previewImage.style.display = "none";
                        previewImage.loading = "lazy";
                        previewImage.classList.add(this.classPrefix + "item__previewImage");
                        filenameLink.append(previewImage);
                        const filenameContainer = container.querySelector("." + this.classPrefix + "item__filename");
                        filenameContainer.innerHTML = "";
                        filenameContainer.append(filenameLink);
                        Listener_1.default.trigger();
                    }
                }
            }
            const input = document.createElement("input");
            input.type = "hidden";
            input.name = this.#singleFileUpload ? this.#fieldId : this.#fieldId + "[]";
            input.value = element.fileId.toString();
            container.append(input);
            this.addButtons(container, element);
        }
    }
    exports.FileProcessor = FileProcessor;
});

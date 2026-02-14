/**
 * @author    Olaf Braun
 * @copyright 2001-2024 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since     6.1
 */
define(["require", "exports", "tslib", "WoltLabSuite/Core/Language", "WoltLabSuite/Core/Api/Files/DeleteFile", "WoltLabSuite/Core/Dom/Change/Listener", "WoltLabSuite/Core/Component/File/Helper", "WoltLabSuite/Core/Component/File/Upload", "WoltLabSuite/Core/Dom/Util", "WoltLabSuite/Core/Component/Confirmation"], function (require, exports, tslib_1, Language_1, DeleteFile_1, Listener_1, Helper_1, Upload_1, Util_1, Confirmation_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.FileProcessor = void 0;
    exports.getValues = getValues;
    exports.registerCallback = registerCallback;
    exports.unregisterCallback = unregisterCallback;
    Listener_1 = tslib_1.__importDefault(Listener_1);
    const fileProcessors = new Map();
    const callbacks = new Map();
    class FileProcessor {
        #container;
        #uploadButton;
        #fieldId;
        #replaceElement = undefined;
        #fileInput;
        #useBigPreview;
        #singleFileUpload;
        #simpleReplace;
        #hideDeleteButton;
        #thumbnailSize;
        #extraButtons;
        #uploadResolve;
        constructor(fieldId, singleFileUpload = false, useBigPreview = false, simpleReplace = false, hideDeleteButton = false, thumbnailSize, extraButtons = []) {
            this.#fieldId = fieldId;
            this.#useBigPreview = useBigPreview;
            this.#singleFileUpload = singleFileUpload;
            this.#simpleReplace = simpleReplace;
            this.#hideDeleteButton = hideDeleteButton;
            this.#extraButtons = extraButtons;
            this.#thumbnailSize = thumbnailSize;
            this.#container = document.getElementById(fieldId + "Container");
            if (this.#container === null) {
                throw new Error("Unknown field with id '" + fieldId + "'");
            }
            this.#uploadButton = this.#container.querySelector("woltlab-core-file-upload");
            if (this.#simpleReplace) {
                this.#uploadButton.addEventListener("shouldUpload", () => {
                    const file = this.#uploadButton.parentElement.querySelector("woltlab-core-file[file-id]");
                    if (!file) {
                        return;
                    }
                    this.#simpleFileReplace(file);
                });
            }
            this.#uploadButton.addEventListener("uploadStart", (event) => {
                if (this.#uploadResolve !== undefined) {
                    this.#uploadResolve();
                }
                this.#registerFile(event.detail);
            });
            this.#fileInput = this.#uploadButton.querySelector('input[type="file"]');
            this.#container.querySelectorAll("woltlab-core-file").forEach((element) => {
                this.#registerFile(element, element.parentElement, false);
            });
            fileProcessors.set(fieldId, this);
        }
        get classPrefix() {
            return this.#useBigPreview ? "fileUpload__preview__" : "fileList__";
        }
        addButtons(container, element) {
            const buttons = document.createElement("ul");
            buttons.classList.add("buttonList");
            buttons.classList.add(this.classPrefix + "item__buttons");
            if (!this.#hideDeleteButton) {
                const listItem = document.createElement("li");
                listItem.append(this.getDeleteButton(element));
                buttons.append(listItem);
            }
            if (this.#singleFileUpload && !this.#simpleReplace) {
                const listItem = document.createElement("li");
                listItem.append(this.getReplaceButton(element));
                buttons.append(listItem);
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
        getReplaceButton(element) {
            const replaceButton = document.createElement("button");
            replaceButton.type = "button";
            replaceButton.classList.add("button", "small");
            replaceButton.textContent = (0, Language_1.getPhrase)("wcf.global.button.replace");
            replaceButton.addEventListener("click", () => {
                const oldContext = this.#startReplaceFile(element);
                (0, Upload_1.clearPreviousErrors)(this.#uploadButton);
                const changeEventListener = () => {
                    this.#fileInput.removeEventListener("cancel", cancelEventListener);
                    // Wait until the upload starts,
                    // the request to the server is not synchronized with the end of the `change` event.
                    // Otherwise, we would swap the context too soon.
                    void new Promise((resolve) => {
                        this.#uploadResolve = resolve;
                    }).then(() => {
                        this.#uploadResolve = undefined;
                        this.#uploadButton.dataset.context = oldContext;
                    });
                };
                const cancelEventListener = () => {
                    this.#uploadButton.dataset.context = oldContext;
                    this.#registerFile(this.#replaceElement, null, false);
                    this.#replaceElement = undefined;
                    this.#uploadResolve = undefined;
                    this.#fileInput.removeEventListener("change", changeEventListener);
                };
                this.#fileInput.addEventListener("cancel", cancelEventListener, { once: true });
                this.#fileInput.addEventListener("change", changeEventListener, { once: true });
                this.#fileInput.click();
            });
            return replaceButton;
        }
        #markElementUploadHasFailed(element, reason) {
            let errorMessage;
            if (reason instanceof Error) {
                errorMessage = reason.message;
            }
            else {
                errorMessage = (0, Helper_1.getErrorMessageFromFile)(element);
            }
            (0, Util_1.innerError)(this.#uploadButton, errorMessage);
            element.remove();
            if (reason instanceof Error) {
                throw reason;
            }
        }
        getDeleteButton(element) {
            const deleteButton = document.createElement("button");
            deleteButton.type = "button";
            deleteButton.classList.add("button", "small");
            deleteButton.textContent = (0, Language_1.getPhrase)("wcf.global.button.delete");
            deleteButton.addEventListener("click", async () => {
                if (!(await (0, Confirmation_1.confirmationFactory)().delete())) {
                    return;
                }
                const result = await (0, DeleteFile_1.deleteFile)(element.fileId);
                if (result.ok) {
                    this.#unregisterFile(element);
                    notifyValueChange(this.#fieldId, this.values);
                }
                else {
                    let container = element;
                    if (!this.#useBigPreview) {
                        container = container.parentElement;
                    }
                    if (result.error.code === "permission_denied") {
                        (0, Util_1.innerError)(container, (0, Language_1.getPhrase)("wcf.upload.error.delete.permissionDenied"), true);
                    }
                    else {
                        (0, Util_1.innerError)(container, result.error.message ?? (0, Language_1.getPhrase)("wcf.upload.error.delete.unknownError"));
                    }
                }
            });
            return deleteButton;
        }
        #simpleFileReplace(oldFile) {
            const oldContext = this.#startReplaceFile(oldFile);
            const cropCancelledEvent = () => {
                this.#uploadResolve = undefined;
                this.#uploadButton.dataset.context = oldContext;
                this.#registerFile(this.#replaceElement, null, false);
                this.#replaceElement = undefined;
            };
            this.#uploadButton.addEventListener("cancel", cropCancelledEvent, { once: true });
            void new Promise((resolve) => {
                this.#uploadResolve = resolve;
            }).then(() => {
                this.#uploadResolve = undefined;
                this.#uploadButton.dataset.context = oldContext;
                this.#uploadButton.removeEventListener("cancel", cropCancelledEvent);
            });
        }
        /**
         * @deprecated 6.2 Use `FileProcessor::isSingleFile()` instead which gracefully handles replacements.
         */
        #startReplaceFile(element) {
            const oldContext = this.#uploadButton.dataset.context;
            const context = JSON.parse(oldContext);
            context.__replace = true;
            this.#uploadButton.dataset.context = JSON.stringify(context);
            this.#replaceElement = element;
            this.#unregisterFile(element);
            return oldContext;
        }
        #unregisterFile(element) {
            if (this.#useBigPreview) {
                element.parentElement.innerHTML = "";
            }
            else {
                element.parentElement.parentElement.remove();
            }
        }
        #registerFile(element, container = null, notifyCallback = true) {
            if (container === null) {
                if (this.#useBigPreview) {
                    container = this.#container.querySelector(".fileUpload__preview");
                    if (container === null) {
                        container = document.createElement("div");
                        container.classList.add("fileUpload__preview");
                        this.#uploadButton.insertAdjacentElement("afterbegin", container);
                    }
                    container.append(element);
                }
                else {
                    container = document.createElement("li");
                    container.classList.add("fileList__item");
                    this.#container.querySelector(".fileList").append(container);
                }
            }
            if (!this.#useBigPreview) {
                (0, Helper_1.insertFileInformation)(container, element);
                element.addEventListener("file:update-data", () => {
                    (0, Helper_1.updateFileInformation)(container, element);
                });
            }
            (0, Helper_1.trackUploadProgress)(container, element);
            element.ready
                .then(() => {
                if (this.#replaceElement !== undefined) {
                    void (0, DeleteFile_1.deleteFile)(this.#replaceElement.fileId);
                    this.#replaceElement = undefined;
                }
                this.#fileInitializationCompleted(element, container, notifyCallback);
            })
                .catch((reason) => {
                if (this.#replaceElement !== undefined) {
                    this.#registerFile(this.#replaceElement, null, false);
                    this.#replaceElement = undefined;
                    if (this.#useBigPreview) {
                        // `this.#replaceElement` need a new container, otherwise the element will be marked as erroneous, too.
                        const tmpContainer = document.createElement("div");
                        tmpContainer.append(element);
                        this.#uploadButton.insertAdjacentElement("afterend", tmpContainer);
                        container = tmpContainer;
                    }
                }
                this.#markElementUploadHasFailed(element, reason);
            })
                .finally(() => {
                (0, Helper_1.removeUploadProgress)(container);
            });
        }
        #fileInitializationCompleted(element, container, notifyCallback = true) {
            if (this.#useBigPreview) {
                setThumbnail(element, element.thumbnails.find((thumbnail) => thumbnail.identifier === this.#thumbnailSize), true);
            }
            else {
                if (element.isImage()) {
                    const thumbnailSize = this.#thumbnailSize ?? "tiny";
                    const thumbnail = element.thumbnails.find((thumbnail) => thumbnail.identifier === thumbnailSize);
                    setThumbnail(element, thumbnail);
                    if (element.link !== undefined && element.filename !== undefined) {
                        const filenameLink = document.createElement("a");
                        filenameLink.href = element.link;
                        filenameLink.title = element.filename;
                        filenameLink.textContent = element.filename;
                        filenameLink.dataset.fancybox = "";
                        filenameLink.dataset.caption = element.filename;
                        // Insert a hidden image element that will be used by the image viewer as the preview image
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
            if (notifyCallback) {
                notifyValueChange(this.#fieldId, this.values);
            }
        }
        get values() {
            if (this.#singleFileUpload) {
                const input = this.#container.querySelector('input[type="hidden"]');
                if (input === null) {
                    return undefined;
                }
                return parseInt(input.value);
            }
            return new Set(Array.from(this.#container.querySelectorAll('input[type="hidden"]')).map((input) => parseInt(input.value)));
        }
    }
    exports.FileProcessor = FileProcessor;
    function setThumbnail(element, thumbnail, unbounded = false) {
        if (unbounded) {
            element.dataset.previewUrl = thumbnail !== undefined ? thumbnail.link : element.link;
        }
        else if (thumbnail !== undefined) {
            element.thumbnail = thumbnail;
        }
        element.unbounded = unbounded;
    }
    function getValues(fieldId) {
        const field = fileProcessors.get(fieldId);
        if (field === undefined) {
            throw new Error("Unknown field with id '" + fieldId + "'");
        }
        return field.values;
    }
    /**
     * Registers a callback that will be called when the value of the field changes.
     *
     * @since 6.2
     */
    function registerCallback(fieldId, callback) {
        if (!callbacks.has(fieldId)) {
            callbacks.set(fieldId, []);
        }
        callbacks.get(fieldId).push(callback);
    }
    /**
     * @since 6.2
     */
    function unregisterCallback(fieldId, callback) {
        callbacks.set(fieldId, callbacks.get(fieldId)?.filter((registeredCallback) => registeredCallback !== callback) ?? []);
    }
    function notifyValueChange(fieldId, values) {
        callbacks.get(fieldId)?.forEach((callback) => callback(values));
    }
});

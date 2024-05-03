define(["require", "exports", "WoltLabSuite/Core/Api/Files/DeleteFile", "../Ckeditor/Event", "WoltLabSuite/Core/FileUtil", "../File/woltlab-core-file"], function (require, exports, DeleteFile_1, Event_1, FileUtil_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = void 0;
    function upload(fileList, file, editorId) {
        const element = document.createElement("li");
        element.classList.add("attachment__item");
        const fileWrapper = document.createElement("div");
        fileWrapper.classList.add("attachment__item__file");
        fileWrapper.append(file);
        const filename = document.createElement("div");
        filename.classList.add("attachment__item__filename");
        filename.textContent = file.filename || file.dataset.filename;
        const fileSize = document.createElement("div");
        fileSize.classList.add("attachment__item__fileSize");
        fileSize.textContent = (0, FileUtil_1.formatFilesize)(file.fileSize || parseInt(file.dataset.fileSize));
        element.append(fileWrapper, filename, fileSize);
        fileList.append(element);
        void file.ready
            .then(() => {
            const data = file.data;
            if (data === undefined) {
                // TODO: error handling
                return;
            }
            const fileId = file.fileId;
            if (fileId === undefined) {
                // TODO: error handling
                return;
            }
            const buttonList = document.createElement("div");
            buttonList.classList.add("attachment__item__buttons");
            buttonList.append(getDeleteAttachButton(fileId, data.attachmentID, editorId, element), getInsertAttachBbcodeButton(data.attachmentID, file.isImage() && file.link ? file.link : "", editorId));
            if (file.isImage()) {
                const thumbnail = file.thumbnails.find((thumbnail) => thumbnail.identifier === "tiny");
                if (thumbnail !== undefined) {
                    file.thumbnail = thumbnail;
                }
                const url = file.thumbnails.find((thumbnail) => thumbnail.identifier === "")?.link;
                if (url !== undefined) {
                    buttonList.append(getInsertThumbnailButton(data.attachmentID, url, editorId));
                }
            }
            element.append(buttonList);
        })
            .catch(() => {
            if (file.validationError === undefined) {
                return;
            }
            // TODO: Add a proper error message, this is for development purposes only.
            element.append(JSON.stringify(file.validationError));
            element.classList.add("attachment__item--error");
        });
    }
    function getDeleteAttachButton(fileId, attachmentId, editorId, element) {
        const button = document.createElement("button");
        button.type = "button";
        button.classList.add("button", "small");
        button.textContent = "TODO: delete";
        button.addEventListener("click", () => {
            const editor = document.getElementById(editorId);
            if (editor === null) {
                // TODO: error handling
                return;
            }
            void (0, DeleteFile_1.deleteFile)(fileId).then((result) => {
                result.unwrap();
                (0, Event_1.dispatchToCkeditor)(editor).removeAttachment({
                    attachmentId,
                });
                element.remove();
            });
        });
        return button;
    }
    function getInsertAttachBbcodeButton(attachmentId, url, editorId) {
        const button = document.createElement("button");
        button.type = "button";
        button.classList.add("button", "small");
        button.textContent = "TODO: insert";
        button.addEventListener("click", () => {
            const editor = document.getElementById(editorId);
            if (editor === null) {
                // TODO: error handling
                return;
            }
            (0, Event_1.dispatchToCkeditor)(editor).insertAttachment({
                attachmentId,
                url,
            });
        });
        return button;
    }
    function getInsertThumbnailButton(attachmentId, url, editorId) {
        const button = document.createElement("button");
        button.type = "button";
        button.classList.add("button", "small");
        button.textContent = "TODO: insert thumbnail";
        button.addEventListener("click", () => {
            const editor = document.getElementById(editorId);
            if (editor === null) {
                // TODO: error handling
                return;
            }
            (0, Event_1.dispatchToCkeditor)(editor).insertAttachment({
                attachmentId,
                url,
            });
        });
        return button;
    }
    function setup(editorId) {
        const container = document.getElementById(`attachments_${editorId}`);
        if (container === null) {
            // TODO: error handling
            return;
        }
        const editor = document.getElementById(editorId);
        if (editor === null) {
            // TODO: error handling
            return;
        }
        const uploadButton = container.querySelector("woltlab-core-file-upload");
        if (uploadButton === null) {
            throw new Error("Expected the container to contain an upload button", {
                cause: {
                    container,
                },
            });
        }
        let fileList = container.querySelector(".attachment__list");
        if (fileList === null) {
            fileList = document.createElement("ol");
            fileList.classList.add("attachment__list");
            uploadButton.insertAdjacentElement("afterend", fileList);
        }
        uploadButton.addEventListener("uploadStart", (event) => {
            upload(fileList, event.detail, editorId);
        });
        (0, Event_1.listenToCkeditor)(editor).uploadAttachment((payload) => {
            const event = new CustomEvent("ckeditorDrop", {
                detail: payload,
            });
            uploadButton.dispatchEvent(event);
        });
        const existingFiles = container.querySelector(".attachment__list__existingFiles");
        if (existingFiles !== null) {
            existingFiles.querySelectorAll("woltlab-core-file").forEach((file) => {
                upload(fileList, file, editorId);
            });
            existingFiles.remove();
        }
    }
    exports.setup = setup;
});

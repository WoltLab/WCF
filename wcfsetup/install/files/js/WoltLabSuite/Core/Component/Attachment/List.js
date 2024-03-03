define(["require", "exports", "../Ckeditor/Event"], function (require, exports, Event_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = void 0;
    function upload(fileList, file, editorId) {
        const element = document.createElement("li");
        element.classList.add("attachment__list__item");
        element.append(file);
        fileList.append(element);
        void file.ready.then(() => {
            const data = file.data;
            if (data === undefined) {
                // TODO: error handling
                return;
            }
            element.append(getInsertAttachBbcodeButton(data.attachmentID, editorId));
            if (file.isImage()) {
                const thumbnail = file.thumbnails.find((thumbnail) => thumbnail.identifier === "tiny");
                if (thumbnail !== undefined) {
                    file.thumbnail = thumbnail;
                }
                const url = file.thumbnails.find((thumbnail) => thumbnail.identifier === "")?.link;
                if (url !== undefined) {
                    element.append(getInsertThumbnailButton(data.attachmentID, url, editorId));
                }
            }
        });
    }
    function getInsertAttachBbcodeButton(attachmentId, editorId) {
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
            // TODO: Insert the original image if it is available.
            (0, Event_1.dispatchToCkeditor)(editor).insertAttachment({
                attachmentId,
                url: "",
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
    }
    exports.setup = setup;
});

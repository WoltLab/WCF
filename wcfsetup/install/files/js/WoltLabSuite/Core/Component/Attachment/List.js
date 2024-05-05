define(["require", "exports", "./Entry", "../Ckeditor/Event", "../File/woltlab-core-file"], function (require, exports, Entry_1, Event_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = void 0;
    function fileToAttachment(fileList, file, editorId) {
        fileList.append((0, Entry_1.createAttachmentFromFile)(file, editorId));
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
            fileToAttachment(fileList, event.detail, editorId);
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
                fileToAttachment(fileList, file, editorId);
            });
            existingFiles.remove();
        }
    }
    exports.setup = setup;
});

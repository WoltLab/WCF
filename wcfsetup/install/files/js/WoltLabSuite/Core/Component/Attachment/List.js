define(["require", "exports", "./Entry", "../Ckeditor/Event", "../File/woltlab-core-file"], function (require, exports, Entry_1, Event_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = void 0;
    function fileToAttachment(fileList, file, editor) {
        fileList.append((0, Entry_1.createAttachmentFromFile)(file, editor));
    }
    function setup(editorId) {
        const container = document.getElementById(`attachments_${editorId}`);
        if (container === null) {
            throw new Error(`The attachments container for '${editorId}' does not exist.`);
        }
        const editor = document.getElementById(editorId);
        if (editor === null) {
            throw new Error(`The editor element for '${editorId}' does not exist.`);
        }
        const uploadButton = container.querySelector("woltlab-core-file-upload");
        if (uploadButton === null) {
            throw new Error("Expected the container to contain an upload button", {
                cause: {
                    container,
                },
            });
        }
        let fileList = container.querySelector(".fileList");
        if (fileList === null) {
            fileList = document.createElement("ol");
            fileList.classList.add("fileList");
            uploadButton.insertAdjacentElement("afterend", fileList);
        }
        uploadButton.addEventListener("uploadStart", (event) => {
            fileToAttachment(fileList, event.detail, editor);
        });
        (0, Event_1.listenToCkeditor)(editor)
            .uploadAttachment((payload) => {
            const event = new CustomEvent("ckeditorDrop", {
                detail: payload,
            });
            uploadButton.dispatchEvent(event);
        })
            .collectMetaData((payload) => {
            let context = undefined;
            try {
                if (uploadButton.dataset.context !== undefined) {
                    context = JSON.parse(uploadButton.dataset.context);
                }
            }
            catch (e) {
                if (window.ENABLE_DEBUG_MODE) {
                    console.warn("Unable to parse the context.", e);
                }
            }
            if (context !== undefined) {
                payload.metaData.tmpHash = context.tmpHash;
            }
        });
        const existingFiles = container.querySelector(".attachment__list__existingFiles");
        if (existingFiles !== null) {
            existingFiles.querySelectorAll("woltlab-core-file").forEach((file) => {
                fileToAttachment(fileList, file, editor);
            });
            existingFiles.remove();
        }
    }
    exports.setup = setup;
});

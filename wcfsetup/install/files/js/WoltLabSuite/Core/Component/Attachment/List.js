define(["require", "exports", "tslib", "WoltLabSuite/Core/Api/Files/DeleteFile", "../Ckeditor/Event", "WoltLabSuite/Core/FileUtil", "WoltLabSuite/Core/Dom/Change/Listener", "WoltLabSuite/Core/Ui/Dropdown/Simple", "../File/woltlab-core-file"], function (require, exports, tslib_1, DeleteFile_1, Event_1, FileUtil_1, Listener_1, Simple_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = void 0;
    Listener_1 = tslib_1.__importDefault(Listener_1);
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
            const extraButtons = [];
            let insertButton;
            if (file.isImage()) {
                const thumbnail = file.thumbnails.find((thumbnail) => thumbnail.identifier === "tiny");
                if (thumbnail !== undefined) {
                    file.thumbnail = thumbnail;
                }
                const url = file.thumbnails.find((thumbnail) => thumbnail.identifier === "")?.link;
                if (url !== undefined) {
                    insertButton = getInsertThumbnailButton(data.attachmentID, url, editorId);
                    extraButtons.push(getInsertAttachBbcodeButton(data.attachmentID, file.link ? file.link : "", editorId));
                }
                else {
                    insertButton = getInsertAttachBbcodeButton(data.attachmentID, file.link ? file.link : "", editorId);
                }
                if (file.link !== undefined && file.filename !== undefined) {
                    const link = document.createElement("a");
                    link.href = file.link;
                    link.classList.add("jsImageViewer");
                    link.title = file.filename;
                    link.textContent = file.filename;
                    filename.innerHTML = "";
                    filename.append(link);
                    Listener_1.default.trigger();
                }
            }
            else {
                insertButton = getInsertAttachBbcodeButton(data.attachmentID, file.isImage() && file.link ? file.link : "", editorId);
            }
            const dropdownMenu = document.createElement("ul");
            dropdownMenu.classList.add("dropdownMenu");
            for (const button of extraButtons) {
                const listItem = document.createElement("li");
                listItem.append(button);
                dropdownMenu.append(listItem);
            }
            if (dropdownMenu.childElementCount !== 0) {
                const listItem = document.createElement("li");
                listItem.classList.add("dropdownDivider");
                dropdownMenu.append(listItem);
            }
            const listItem = document.createElement("li");
            listItem.append(getDeleteAttachButton(fileId, data.attachmentID, editorId, element));
            dropdownMenu.append(listItem);
            const moreOptions = document.createElement("button");
            moreOptions.classList.add("button", "small", "jsTooltip");
            moreOptions.type = "button";
            moreOptions.title = "TODO: more options";
            moreOptions.innerHTML = '<fa-icon name="ellipsis-vertical"></fa-icon>';
            const buttonList = document.createElement("div");
            buttonList.classList.add("attachment__item__buttons");
            insertButton.classList.add("button", "small");
            buttonList.append(insertButton, moreOptions);
            element.append(buttonList);
            (0, Simple_1.initFragment)(moreOptions, dropdownMenu);
            moreOptions.addEventListener("click", (event) => {
                event.stopPropagation();
                (0, Simple_1.toggleDropdown)(moreOptions.id);
            });
        })
            .catch((e) => {
            if (e instanceof Error) {
                throw e;
            }
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

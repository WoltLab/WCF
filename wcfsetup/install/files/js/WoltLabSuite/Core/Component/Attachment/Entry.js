define(["require", "exports", "tslib", "WoltLabSuite/Core/FileUtil", "WoltLabSuite/Core/Ui/Dropdown/Simple", "WoltLabSuite/Core/Dom/Change/Listener", "../Ckeditor/Event", "WoltLabSuite/Core/Api/Files/DeleteFile", "WoltLabSuite/Core/Language", "WoltLabSuite/Core/Component/File/File"], function (require, exports, tslib_1, FileUtil_1, Simple_1, Listener_1, Event_1, DeleteFile_1, Language_1, File_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.createAttachmentFromFile = void 0;
    Listener_1 = tslib_1.__importDefault(Listener_1);
    function fileInitializationCompleted(element, file, editor) {
        const data = file.data;
        if (data === undefined) {
            throw new Error("No meta data was returned from the server.", {
                cause: {
                    file,
                },
            });
        }
        const fileId = file.fileId;
        if (fileId === undefined) {
            throw new Error("The file id is not set.", {
                cause: {
                    file,
                },
            });
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
                insertButton = getInsertButton(data.attachmentID, url, editor);
                const insertOriginalImage = getInsertButton(data.attachmentID, file.link, editor);
                insertOriginalImage.textContent = (0, Language_1.getPhrase)("wcf.attachment.insertFull");
                extraButtons.push(insertOriginalImage);
            }
            else {
                insertButton = getInsertButton(data.attachmentID, file.link ? file.link : "", editor);
            }
            if (file.link !== undefined && file.filename !== undefined) {
                const link = document.createElement("a");
                link.href = file.link;
                link.classList.add("jsImageViewer");
                link.title = file.filename;
                link.textContent = file.filename;
                const filename = element.querySelector(".fileList__item__filename");
                filename.innerHTML = "";
                filename.append(link);
                Listener_1.default.trigger();
            }
        }
        else {
            insertButton = getInsertButton(data.attachmentID, file.isImage() && file.link ? file.link : "", editor);
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
        listItem.append(getDeleteAttachButton(fileId, data.attachmentID, editor, element));
        dropdownMenu.append(listItem);
        const moreOptions = document.createElement("button");
        moreOptions.classList.add("button", "small");
        moreOptions.type = "button";
        moreOptions.setAttribute("aria-label", (0, Language_1.getPhrase)("wcf.global.button.more"));
        moreOptions.innerHTML = '<fa-icon name="ellipsis-vertical"></fa-icon>';
        const buttonList = document.createElement("div");
        buttonList.classList.add("fileList__item__buttons");
        insertButton.classList.add("button", "small");
        buttonList.append(insertButton, moreOptions);
        element.append(buttonList);
        (0, Simple_1.initFragment)(moreOptions, dropdownMenu);
        moreOptions.addEventListener("click", (event) => {
            event.stopPropagation();
            (0, Simple_1.toggleDropdown)(moreOptions.id);
        });
    }
    function getDeleteAttachButton(fileId, attachmentId, editor, element) {
        const button = document.createElement("button");
        button.type = "button";
        button.textContent = (0, Language_1.getPhrase)("wcf.global.button.delete");
        button.addEventListener("click", () => {
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
    function getInsertButton(attachmentId, url, editor) {
        const button = document.createElement("button");
        button.type = "button";
        button.textContent = (0, Language_1.getPhrase)("wcf.attachment.insert");
        button.addEventListener("click", () => {
            (0, Event_1.dispatchToCkeditor)(editor).insertAttachment({
                attachmentId,
                url,
            });
        });
        return button;
    }
    function createAttachmentFromFile(file, editor) {
        const element = document.createElement("li");
        element.classList.add("fileList__item", "attachment__item");
        const fileWrapper = document.createElement("div");
        fileWrapper.classList.add("fileList__item__file");
        fileWrapper.append(file);
        const filename = document.createElement("div");
        filename.classList.add("fileList__item__filename");
        filename.textContent = file.filename || file.dataset.filename;
        const fileSize = document.createElement("div");
        fileSize.classList.add("fileList__item__fileSize");
        fileSize.textContent = (0, FileUtil_1.formatFilesize)(file.fileSize || parseInt(file.dataset.fileSize));
        element.append(fileWrapper, filename, fileSize);
        void file.ready
            .then(() => {
            fileInitializationCompleted(element, file, editor);
        })
            .catch((reason) => {
            (0, File_1.fileInitializationFailed)(element, file, reason);
        })
            .finally(() => {
            (0, File_1.removeUploadProgress)(element);
        });
        (0, File_1.trackUploadProgress)(element, file);
        return element;
    }
    exports.createAttachmentFromFile = createAttachmentFromFile;
});

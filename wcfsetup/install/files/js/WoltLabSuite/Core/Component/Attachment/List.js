define(["require", "exports", "tslib", "./Entry", "../Ckeditor/Event", "../Message/MessageTabMenu", "sortablejs", "WoltLabSuite/Core/Helper/PromiseMutex", "WoltLabSuite/Core/Api/PostObject"], function (require, exports, tslib_1, Entry_1, Event_1, MessageTabMenu_1, sortablejs_1, PromiseMutex_1, PostObject_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    sortablejs_1 = tslib_1.__importDefault(sortablejs_1);
    function fileToAttachment(fileList, file, editor) {
        fileList.append((0, Entry_1.createAttachmentFromFile)(file, editor));
    }
    function setup(editorId) {
        const container = document.getElementById(`attachments_${editorId}`);
        if (container === null) {
            throw new Error(`The attachments container for '${editorId}' does not exist.`);
        }
        const tabMenu = (0, MessageTabMenu_1.getTabMenu)(editorId);
        if (tabMenu === undefined) {
            throw new Error("Unable to find the corresponding tab menu.");
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
        new sortablejs_1.default(fileList, {
            direction: "vertical",
            dragClass: ".fileList__item",
            ghostClass: "fileList__item--ghost",
            handle: ".fileList__item__file",
            animation: 150,
            fallbackOnBody: true,
            onChange(event) {
                const file = event.item.querySelector("woltlab-core-file");
                const thumbnail = file.thumbnails.find((thumbnail) => thumbnail.identifier === "tiny");
                if (thumbnail !== undefined) {
                    file.thumbnail = thumbnail;
                }
                else if (file.link) {
                    file.previewUrl = file.link;
                }
            },
            onEnd: (0, PromiseMutex_1.promiseMutex)(async (event) => {
                if (event.oldIndex === event.newIndex) {
                    return;
                }
                const attachmentIDs = Array.from(fileList.querySelectorAll("woltlab-core-file"))
                    .map((file) => file.data?.attachmentID)
                    .filter((attachmentID) => attachmentID !== undefined);
                const context = JSON.parse(uploadButton.dataset.context);
                await (0, PostObject_1.postObject)(`${window.WSC_RPC_API_URL}core/attachments/show-order`, { ...context, attachmentIDs });
            }),
        });
        let showOrder = -1;
        uploadButton.addEventListener("uploadStart", (event) => {
            fileToAttachment(fileList, event.detail, editor);
            const context = JSON.parse(uploadButton.dataset.context);
            context.showOrder = ++showOrder;
            uploadButton.dataset.context = JSON.stringify(context);
        });
        (0, Event_1.listenToCkeditor)(editor)
            .uploadAttachment((payload) => {
            const event = new CustomEvent("ckeditorDrop", {
                detail: payload,
            });
            uploadButton.dispatchEvent(event);
            const messageTabMenu = document.querySelector(`.messageTabMenu[data-wysiwyg-container-id="${editorId}"]`);
            if (messageTabMenu === null) {
                return;
            }
            (0, MessageTabMenu_1.getTabMenu)(editorId)?.setActiveTab("attachments");
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
        })
            .reset(() => {
            fileList.querySelectorAll(".fileList__item").forEach((element) => element.remove());
        });
        const existingFiles = container.querySelector(".attachment__list__existingFiles");
        if (existingFiles !== null) {
            existingFiles.querySelectorAll("woltlab-core-file").forEach((file) => {
                fileToAttachment(fileList, file, editor);
                const attachmentShowOrder = file.data?.showOrder;
                if (typeof attachmentShowOrder === "number") {
                    showOrder = Math.max(showOrder, attachmentShowOrder);
                }
            });
            existingFiles.remove();
        }
        const files = fileList.getElementsByTagName("woltlab-core-file");
        const observer = new MutationObserver(() => {
            let counter = 0;
            for (const file of files) {
                if (!file.isFailedUpload()) {
                    counter++;
                }
            }
            tabMenu.setTabCounter("attachments", counter);
        });
        observer.observe(fileList, {
            childList: true,
            subtree: true,
        });
    }
});

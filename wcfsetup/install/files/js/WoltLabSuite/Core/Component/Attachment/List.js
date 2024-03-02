define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = void 0;
    function upload(fileList, file) {
        const element = document.createElement("li");
        element.classList.add("attachment__list__item");
        element.append(file);
        fileList.append(element);
        void file.ready.then(() => {
            if (file.isImage()) {
                const thumbnail = file.thumbnails.find((thumbnail) => {
                    return thumbnail.identifier === "tiny";
                });
                if (thumbnail !== undefined) {
                    file.thumbnail = thumbnail;
                }
            }
        });
    }
    function setup(container) {
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
            upload(fileList, event.detail);
        });
    }
    exports.setup = setup;
});

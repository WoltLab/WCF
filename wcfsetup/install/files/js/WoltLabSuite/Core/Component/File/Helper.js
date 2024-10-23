define(["require", "exports", "WoltLabSuite/Core/Language", "WoltLabSuite/Core/FileUtil", "WoltLabSuite/Core/Component/File/woltlab-core-file"], function (require, exports, Language_1, FileUtil_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.trackUploadProgress = trackUploadProgress;
    exports.removeUploadProgress = removeUploadProgress;
    exports.fileInitializationFailed = fileInitializationFailed;
    exports.insertFileInformation = insertFileInformation;
    function trackUploadProgress(element, file) {
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
    function removeUploadProgress(element) {
        if (!element.classList.contains("fileProcessor__item--uploading")) {
            return;
        }
        element.classList.remove("fileProcessor__item--uploading");
        element.querySelector(".fileList__item__progress")?.remove();
    }
    function fileInitializationFailed(element, file, reason) {
        if (reason instanceof Error) {
            throw reason;
        }
        if (file.apiError === undefined) {
            return;
        }
        let errorMessage;
        const validationError = file.apiError.getValidationError();
        if (validationError !== undefined) {
            switch (validationError.param) {
                case "preflight":
                    errorMessage = (0, Language_1.getPhrase)(`wcf.upload.error.${validationError.code}`);
                    break;
                case "validation":
                    errorMessage = (0, Language_1.getPhrase)(`wcf.upload.validation.error.${validationError.code}`);
                    break;
                default:
                    errorMessage = "Unrecognized error type: " + JSON.stringify(validationError);
                    break;
            }
        }
        else {
            errorMessage = `Unexpected server error: [${file.apiError.type}] ${file.apiError.message}`;
        }
        markElementAsErroneous(element, errorMessage);
    }
    function markElementAsErroneous(element, errorMessage) {
        element.classList.add("fileList__item--error");
        const errorElement = document.createElement("div");
        errorElement.classList.add("fileList__item__errorMessage");
        errorElement.textContent = errorMessage;
        element.append(errorElement);
    }
    function insertFileInformation(container, file) {
        const fileWrapper = document.createElement("div");
        fileWrapper.classList.add("fileList__item__file");
        fileWrapper.append(file);
        const filename = document.createElement("div");
        filename.classList.add("fileList__item__filename");
        filename.textContent = file.filename || file.dataset.filename;
        const fileSize = document.createElement("div");
        fileSize.classList.add("fileList__item__fileSize");
        fileSize.textContent = (0, FileUtil_1.formatFilesize)(file.fileSize || parseInt(file.dataset.fileSize));
        container.append(fileWrapper, filename, fileSize);
    }
});

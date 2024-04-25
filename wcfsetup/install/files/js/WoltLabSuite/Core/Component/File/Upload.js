define(["require", "exports", "tslib", "WoltLabSuite/Core/Helper/Selector", "WoltLabSuite/Core/Api/Files/Upload", "WoltLabSuite/Core/Api/Files/Chunk/Chunk", "WoltLabSuite/Core/Api/Files/GenerateThumbnails", "WoltLabSuite/Core/Image/Resizer"], function (require, exports, tslib_1, Selector_1, Upload_1, Chunk_1, GenerateThumbnails_1, Resizer_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = void 0;
    Resizer_1 = tslib_1.__importDefault(Resizer_1);
    async function upload(element, file) {
        const typeName = element.dataset.typeName;
        const fileHash = await getSha256Hash(await file.arrayBuffer());
        const fileElement = document.createElement("woltlab-core-file");
        fileElement.dataset.filename = file.name;
        const event = new CustomEvent("uploadStart", { detail: fileElement });
        element.dispatchEvent(event);
        const response = await (0, Upload_1.upload)(file.name, file.size, fileHash, typeName, element.dataset.context || "");
        if (!response.ok) {
            const validationError = response.error.getValidationError();
            if (validationError === undefined) {
                fileElement.uploadFailed();
                throw response.error;
            }
            console.log(validationError);
            return;
        }
        const { identifier, numberOfChunks } = response.value;
        const chunkSize = Math.ceil(file.size / numberOfChunks);
        // TODO: Can we somehow report any meaningful upload progress?
        for (let i = 0; i < numberOfChunks; i++) {
            const start = i * chunkSize;
            const end = start + chunkSize;
            const chunk = file.slice(start, end);
            const checksum = await getSha256Hash(await chunk.arrayBuffer());
            const response = await (0, Chunk_1.uploadChunk)(identifier, i, checksum, chunk);
            if (!response.ok) {
                fileElement.uploadFailed();
                throw response.error;
            }
            await chunkUploadCompleted(fileElement, response.value);
        }
    }
    async function chunkUploadCompleted(fileElement, result) {
        if (!result.completed) {
            return;
        }
        fileElement.uploadCompleted(result.fileID, result.mimeType, result.link, result.data, result.generateThumbnails);
        if (result.generateThumbnails) {
            const response = await (0, GenerateThumbnails_1.generateThumbnails)(result.fileID);
            fileElement.setThumbnails(response.unwrap());
        }
    }
    async function getSha256Hash(data) {
        const buffer = await window.crypto.subtle.digest("SHA-256", data);
        return Array.from(new Uint8Array(buffer))
            .map((b) => b.toString(16).padStart(2, "0"))
            .join("");
    }
    function clearPreviousErrors(element) {
        element.parentElement?.querySelectorAll(".innerError").forEach((x) => x.remove());
    }
    async function resizeImage(element, file) {
        switch (file.type) {
            case "image/jpeg":
            case "image/png":
            case "image/webp":
                // Potential candidate for a resize operation.
                break;
            default:
                // Not an image or an unsupported file type.
                return file;
        }
        const timeout = new Promise((resolve) => {
            window.setTimeout(() => resolve(file), 10000);
        });
        const resizeConfiguration = JSON.parse(element.dataset.resizeConfiguration);
        const resizer = new Resizer_1.default();
        const { image, exif } = await resizer.loadFile(file);
        const maxHeight = resizeConfiguration.maxHeight;
        let maxWidth = resizeConfiguration.maxWidth;
        if (window.devicePixelRatio >= 2) {
            const actualWidth = window.screen.width * window.devicePixelRatio;
            const actualHeight = window.screen.height * window.devicePixelRatio;
            // If the dimensions are equal then this is a screenshot from a HiDPI
            // device, thus we downscale this to the “natural” dimensions.
            if (actualWidth === image.width && actualHeight === image.height) {
                maxWidth = Math.min(maxWidth, window.screen.width);
            }
        }
        const canvas = await resizer.resize(image, maxWidth, maxHeight, resizeConfiguration.quality, true, timeout);
        if (canvas === undefined) {
            // The resize operation failed, timed out or there was no need to perform
            // any scaling whatsoever.
            return file;
        }
        let fileType = resizeConfiguration.fileType;
        if (fileType === "image/jpeg" || fileType === "image/webp") {
            fileType = "image/webp";
        }
        else {
            fileType = file.type;
        }
        const resizedFile = await resizer.saveFile({
            exif,
            image: canvas,
        }, file.name, fileType, resizeConfiguration.quality);
        return resizedFile;
    }
    function setup() {
        (0, Selector_1.wheneverFirstSeen)("woltlab-core-file-upload", (element) => {
            element.addEventListener("upload", (event) => {
                const file = event.detail;
                clearPreviousErrors(element);
                void resizeImage(element, file).then((resizedFile) => {
                    void upload(element, resizedFile);
                });
            });
        });
    }
    exports.setup = setup;
});

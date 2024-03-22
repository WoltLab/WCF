define(["require", "exports", "WoltLabSuite/Core/Helper/Selector", "WoltLabSuite/Core/Api/Files/Upload", "WoltLabSuite/Core/Api/Files/Chunk/Chunk", "WoltLabSuite/Core/Api/Files/GenerateThumbnails"], function (require, exports, Selector_1, Upload_1, Chunk_1, GenerateThumbnails_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = void 0;
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
    function setup() {
        (0, Selector_1.wheneverFirstSeen)("woltlab-core-file-upload", (element) => {
            element.addEventListener("upload", (event) => {
                void upload(element, event.detail);
            });
        });
    }
    exports.setup = setup;
});

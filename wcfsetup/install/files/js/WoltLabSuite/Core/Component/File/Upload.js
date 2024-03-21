define(["require", "exports", "WoltLabSuite/Core/Ajax/Backend", "WoltLabSuite/Core/Helper/Selector", "WoltLabSuite/Core/Api/Files/Upload"], function (require, exports, Backend_1, Selector_1, Upload_1) {
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
            // TODO fix the URL
            throw new Error("TODO: fix the url");
            const endpoint = new URL(String(i));
            const checksum = await getSha256Hash(await chunk.arrayBuffer());
            endpoint.searchParams.append("checksum", checksum);
            let response;
            try {
                response = (await (0, Backend_1.prepareRequest)(endpoint.toString()).post(chunk).fetchAsJson());
            }
            catch (e) {
                // TODO: Handle errors
                console.error(e);
                fileElement.uploadFailed();
                throw e;
            }
            await chunkUploadCompleted(fileElement, response);
        }
    }
    async function chunkUploadCompleted(fileElement, response) {
        if (!response.completed) {
            return;
        }
        const hasThumbnails = response.endpointThumbnails !== "";
        fileElement.uploadCompleted(response.fileID, response.mimeType, response.link, response.data, hasThumbnails);
        if (hasThumbnails) {
            await generateThumbnails(fileElement, response.endpointThumbnails);
        }
    }
    async function generateThumbnails(fileElement, endpoint) {
        let response;
        try {
            response = (await (0, Backend_1.prepareRequest)(endpoint).get().fetchAsJson());
        }
        catch (e) {
            // TODO: Handle errors
            console.error(e);
            throw e;
        }
        fileElement.setThumbnails(response);
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

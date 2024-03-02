define(["require", "exports", "WoltLabSuite/Core/Ajax/Backend", "WoltLabSuite/Core/Ajax/Error", "WoltLabSuite/Core/Core", "WoltLabSuite/Core/Helper/Selector"], function (require, exports, Backend_1, Error_1, Core_1, Selector_1) {
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
        let response = undefined;
        try {
            response = (await (0, Backend_1.prepareRequest)(element.dataset.endpoint)
                .post({
                filename: file.name,
                fileSize: file.size,
                fileHash,
                typeName,
                context: element.dataset.context,
            })
                .fetchAsJson());
        }
        catch (e) {
            if (e instanceof Error_1.StatusNotOk) {
                const body = await e.response.clone().json();
                if ((0, Core_1.isPlainObject)(body) && (0, Core_1.isPlainObject)(body.error)) {
                    console.log(body);
                    return;
                }
                else {
                    throw e;
                }
            }
            else {
                throw e;
            }
        }
        finally {
            if (response === undefined) {
                fileElement.uploadFailed();
            }
        }
        const { endpoints } = response;
        const chunkSize = Math.ceil(file.size / endpoints.length);
        // TODO: Can we somehow report any meaningful upload progress?
        for (let i = 0, length = endpoints.length; i < length; i++) {
            const start = i * chunkSize;
            const end = start + chunkSize;
            const chunk = file.slice(start, end);
            const endpoint = new URL(endpoints[i]);
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
        // TODO: The response contains the `.data` property which holds important data
        //       returned by the file processor that needs to be forwarded.
        fileElement.uploadCompleted(response.fileID, response.mimeType, hasThumbnails);
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

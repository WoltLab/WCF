define(["require", "exports", "WoltLabSuite/Core/Ajax/Backend", "WoltLabSuite/Core/Helper/Selector", "WoltLabSuite/Core/StringUtil"], function (require, exports, Backend_1, Selector_1, StringUtil_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = void 0;
    async function upload(element, file) {
        const typeName = element.dataset.typeName;
        const context = getContextFromDataAttributes(element);
        const fileHash = await getSha256Hash(await file.arrayBuffer());
        const response = (await (0, Backend_1.prepareRequest)(element.dataset.endpoint)
            .post({
            filename: file.name,
            fileSize: file.size,
            fileHash,
            typeName,
            context,
        })
            .fetchAsJson());
        const { endpoints } = response;
        const chunkSize = Math.ceil(file.size / endpoints.length);
        for (let i = 0, length = endpoints.length; i < length; i++) {
            const start = i * chunkSize;
            const end = start + chunkSize;
            const chunk = file.slice(start, end);
            const endpoint = new URL(endpoints[i]);
            const checksum = await getSha256Hash(await chunk.arrayBuffer());
            endpoint.searchParams.append("checksum", checksum);
            const response = await (0, Backend_1.prepareRequest)(endpoint.toString()).post(chunk).fetchAsResponse();
            if (response) {
                console.log(await response.text());
            }
        }
    }
    function getContextFromDataAttributes(element) {
        const context = {};
        const prefixContext = "data-context-";
        for (const attribute of element.attributes) {
            if (!attribute.name.startsWith(prefixContext)) {
                continue;
            }
            const key = attribute.name
                .substring(prefixContext.length)
                .split("-")
                .map((part, index) => {
                if (index === 0) {
                    return part;
                }
                return (0, StringUtil_1.ucfirst)(part);
            })
                .join("");
            context[key] = attribute.value;
        }
        return context;
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

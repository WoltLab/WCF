define(["require", "exports", "WoltLabSuite/Core/Ajax/Backend", "WoltLabSuite/Core/Helper/Selector"], function (require, exports, Backend_1, Selector_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = void 0;
    async function upload(element, file) {
        const response = (await (0, Backend_1.prepareRequest)(element.dataset.endpoint)
            .post({
            filename: file.name,
            filesize: file.size,
        })
            .fetchAsJson());
        const { endpoints } = response;
        const chunkSize = 2000000;
        const chunks = Math.ceil(file.size / chunkSize);
        const arrayBufferToHex = (buffer) => {
            return Array.from(new Uint8Array(buffer))
                .map((b) => b.toString(16).padStart(2, "0"))
                .join("");
        };
        const hash = await window.crypto.subtle.digest("SHA-256", await file.arrayBuffer());
        console.log("checksum for the entire file is:", arrayBufferToHex(hash));
        const data = [];
        for (let i = 0; i < chunks; i++) {
            const start = i * chunkSize;
            const end = start + chunkSize;
            const chunk = file.slice(start, end);
            data.push(chunk);
            console.log("Uploading", start, "to", end, " (total: " + chunk.size + " of " + file.size + ")");
            await (0, Backend_1.prepareRequest)(endpoints[i]).post(chunk).fetchAsResponse();
        }
        const uploadedChunks = new Blob(data);
        const uploadedHash = await window.crypto.subtle.digest("SHA-256", await uploadedChunks.arrayBuffer());
        console.log("checksum for the entire file is:", arrayBufferToHex(uploadedHash));
    }
    function setup() {
        (0, Selector_1.wheneverFirstSeen)("woltlab-core-file-upload", (element) => {
            element.addEventListener("upload", (event) => {
                void upload(element, event.detail);
            });
            const file = new File(["a".repeat(4000001)], "test.txt");
            void upload(element, file);
        });
    }
    exports.setup = setup;
});

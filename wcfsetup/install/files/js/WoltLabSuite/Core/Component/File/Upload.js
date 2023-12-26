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
        for (let i = 0; i < chunks; i++) {
            const start = i * chunkSize;
            const end = start + chunkSize;
            const chunk = file.slice(start, end);
            await (0, Backend_1.prepareRequest)(endpoints[i]).post(chunk).fetchAsResponse();
        }
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

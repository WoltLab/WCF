define(["require", "exports", "WoltLabSuite/Core/Ajax/Backend", "WoltLabSuite/Core/Helper/Selector"], function (require, exports, Backend_1, Selector_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = void 0;
    async function upload(element, file) {
        const chunkSize = 2000000;
        const chunks = Math.ceil(file.size / chunkSize);
        for (let i = 0; i < chunks; i++) {
            const chunk = file.slice(i * chunkSize, i * chunkSize + chunkSize + 1);
            const response = await (0, Backend_1.prepareRequest)(element.dataset.endpoint).post(chunk).fetchAsResponse();
            console.log(response);
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

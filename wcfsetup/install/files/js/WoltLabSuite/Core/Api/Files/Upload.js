define(["require", "exports", "WoltLabSuite/Core/Ajax/Backend", "../Result"], function (require, exports, Backend_1, Result_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.upload = void 0;
    async function upload(filename, fileSize, fileHash, objectType, context) {
        const url = new URL(`${window.WSC_API_URL}index.php?api/rpc/core/files/upload`);
        const payload = {
            filename,
            fileSize,
            fileHash,
            objectType,
            context,
        };
        let response;
        try {
            response = (await (0, Backend_1.prepareRequest)(url).post(payload).fetchAsJson());
        }
        catch (e) {
            return (0, Result_1.apiResultFromError)(e);
        }
        return (0, Result_1.apiResultFromValue)(response);
    }
    exports.upload = upload;
});

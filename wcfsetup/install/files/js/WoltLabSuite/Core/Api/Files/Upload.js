define(["require", "exports", "WoltLabSuite/Core/Ajax/Backend", "../Result"], function (require, exports, Backend_1, Result_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.upload = upload;
    async function upload(filename, fileSize, fileHash, objectType, context, exifBytes = null) {
        const url = new URL(`${window.WSC_RPC_API_URL}core/files/upload`);
        let exifData = null;
        if (exifBytes !== null) {
            exifData = "";
            for (let i = 0, length = exifBytes.length; i < length; i++) {
                exifData += exifBytes[i].toString(16).padStart(2, "0");
            }
        }
        const payload = {
            filename,
            fileSize,
            fileHash,
            objectType,
            context,
            exifData,
        };
        let response;
        try {
            response = (await (0, Backend_1.prepareRequest)(url).post(payload).disableLoadingIndicator().fetchAsJson());
        }
        catch (e) {
            return (0, Result_1.apiResultFromError)(e);
        }
        return (0, Result_1.apiResultFromValue)(response);
    }
});

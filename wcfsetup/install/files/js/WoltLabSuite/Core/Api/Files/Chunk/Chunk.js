define(["require", "exports", "WoltLabSuite/Core/Ajax/Backend", "../../Result"], function (require, exports, Backend_1, Result_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.uploadChunk = uploadChunk;
    async function uploadChunk(identifier, sequenceNo, checksum, payload) {
        const url = new URL(`${window.WSC_RPC_API_URL}core/files/upload/${identifier}/chunk/${sequenceNo}`);
        let response;
        try {
            response = (await (0, Backend_1.prepareRequest)(url)
                .post(payload)
                .withHeader("chunk-checksum-sha256", checksum)
                .fetchAsJson());
        }
        catch (e) {
            return (0, Result_1.apiResultFromError)(e);
        }
        return (0, Result_1.apiResultFromValue)(response);
    }
});

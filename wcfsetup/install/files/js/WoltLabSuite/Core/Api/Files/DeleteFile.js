define(["require", "exports", "WoltLabSuite/Core/Ajax/Backend", "../Result"], function (require, exports, Backend_1, Result_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.deleteFile = deleteFile;
    async function deleteFile(fileId) {
        try {
            await (0, Backend_1.prepareRequest)(`${window.WSC_RPC_API_URL}core/files/${fileId}`).delete().fetchAsJson();
        }
        catch (e) {
            return (0, Result_1.apiResultFromError)(e);
        }
        return (0, Result_1.apiResultFromValue)([]);
    }
});

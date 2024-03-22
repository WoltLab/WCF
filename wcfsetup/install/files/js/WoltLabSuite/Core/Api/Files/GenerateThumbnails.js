define(["require", "exports", "WoltLabSuite/Core/Ajax/Backend", "../Result"], function (require, exports, Backend_1, Result_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.generateThumbnails = void 0;
    async function generateThumbnails(fileID) {
        const url = new URL(`${window.WSC_API_URL}index.php?api/rpc/core/files/${fileID}/generatethumbnails`);
        let response;
        try {
            response = (await (0, Backend_1.prepareRequest)(url).post().fetchAsJson());
        }
        catch (e) {
            return (0, Result_1.apiResultFromError)(e);
        }
        return (0, Result_1.apiResultFromValue)(response);
    }
    exports.generateThumbnails = generateThumbnails;
});

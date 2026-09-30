define(["require", "exports", "WoltLabSuite/Core/Ajax/Backend", "../Result"], function (require, exports, Backend_1, Result_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.generateThumbnails = generateThumbnails;
    async function generateThumbnails(fileID, uploaderToken) {
        const url = new URL(`${window.WSC_RPC_API_URL}core/files/${fileID}/generate-thumbnails`);
        if (uploaderToken !== undefined) {
            url.searchParams.set("uploaderToken", uploaderToken);
        }
        let response;
        try {
            response = (await (0, Backend_1.prepareRequest)(url).post().disableLoadingIndicator().fetchAsJson());
        }
        catch (e) {
            return (0, Result_1.apiResultFromError)(e);
        }
        return (0, Result_1.apiResultFromValue)(response);
    }
});

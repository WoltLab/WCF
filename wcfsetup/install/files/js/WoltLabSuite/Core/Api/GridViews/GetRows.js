define(["require", "exports", "WoltLabSuite/Core/Ajax/Backend", "../Result"], function (require, exports, Backend_1, Result_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.getRows = void 0;
    async function getRows(gridViewClass, pageNo) {
        const url = new URL(`${window.WSC_RPC_API_URL}core/gridViews/rows`);
        url.searchParams.set("gridView", gridViewClass);
        url.searchParams.set("pageNo", pageNo.toString());
        let response;
        try {
            response = (await (0, Backend_1.prepareRequest)(url).get().allowCaching().disableLoadingIndicator().fetchAsJson());
        }
        catch (e) {
            return (0, Result_1.apiResultFromError)(e);
        }
        return (0, Result_1.apiResultFromValue)(response);
    }
    exports.getRows = getRows;
});

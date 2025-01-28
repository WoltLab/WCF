/**
 * Gets the rows for the rendering of a grid view.
 *
 * @author Marcel Werk
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
define(["require", "exports", "WoltLabSuite/Core/Ajax/Backend", "../Result"], function (require, exports, Backend_1, Result_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.getRows = getRows;
    async function getRows(gridViewClass, pageNo, sortField = "", sortOrder = "ASC", filters, gridViewParameters) {
        const url = new URL(`${window.WSC_RPC_API_URL}core/grid-views/rows`);
        url.searchParams.set("gridView", gridViewClass);
        url.searchParams.set("pageNo", pageNo.toString());
        url.searchParams.set("sortField", sortField);
        url.searchParams.set("sortOrder", sortOrder);
        if (filters) {
            filters.forEach((value, key) => {
                url.searchParams.set(`filters[${key}]`, value);
            });
        }
        if (gridViewParameters) {
            gridViewParameters.forEach((value, key) => {
                url.searchParams.set(`gridViewParameters[${key}]`, value);
            });
        }
        let response;
        try {
            response = (await (0, Backend_1.prepareRequest)(url).get().allowCaching().disableLoadingIndicator().fetchAsJson());
        }
        catch (e) {
            return (0, Result_1.apiResultFromError)(e);
        }
        return (0, Result_1.apiResultFromValue)(response);
    }
});

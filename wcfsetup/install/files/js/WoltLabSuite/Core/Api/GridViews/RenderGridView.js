/**
 * Renders a complete grid view, including its header and the first page of
 * rows.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
define(["require", "exports", "WoltLabSuite/Core/Ajax/Backend", "../Result"], function (require, exports, Backend_1, Result_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.renderGridView = renderGridView;
    function renderGridView(gridViewClass, pageNo = 1, sortField = "", sortOrder = "ASC", filters, gridViewParameters) {
        const url = new URL(`${window.WSC_RPC_API_URL}core/grid-views/render`);
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
                if (Array.isArray(value)) {
                    value.forEach((innerValue, innerKey) => {
                        url.searchParams.set(`gridViewParameters[${key}][${innerKey}]`, innerValue);
                    });
                }
                else {
                    url.searchParams.set(`gridViewParameters[${key}]`, value);
                }
            });
        }
        return (0, Result_1.fromInfallibleApiRequest)(() => {
            return (0, Backend_1.prepareRequest)(url).get().fetchAsJson();
        });
    }
});

/**
 * Gets the items for the rendering of a list view.
 *
 * @author Marcel Werk
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
define(["require", "exports", "WoltLabSuite/Core/Ajax/Backend", "../Result"], function (require, exports, Backend_1, Result_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.getItems = getItems;
    async function getItems(listViewClass, pageNo, sortField = "", sortOrder = "ASC", filters, listViewParameters) {
        const url = new URL(`${window.WSC_RPC_API_URL}core/list-views/items`);
        url.searchParams.set("listView", listViewClass);
        url.searchParams.set("pageNo", pageNo.toString());
        url.searchParams.set("sortField", sortField);
        url.searchParams.set("sortOrder", sortOrder);
        if (filters) {
            filters.forEach((value, key) => {
                url.searchParams.set(`filters[${key}]`, value);
            });
        }
        if (listViewParameters) {
            listViewParameters.forEach((value, key) => {
                if (Array.isArray(value)) {
                    value.forEach((innerValue, innerkey) => {
                        url.searchParams.set(`listViewParameters[${key}][${innerkey}]`, innerValue);
                    });
                }
                else {
                    url.searchParams.set(`listViewParameters[${key}]`, value);
                }
            });
        }
        return (0, Result_1.fromInfallibleApiRequest)(() => {
            return (0, Backend_1.prepareRequest)(url).get().allowCaching().disableLoadingIndicator().fetchAsJson();
        });
    }
});

/**
 * Gets a single row for rendering in a grid view.
 *
 * @author Marcel Werk
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
define(["require", "exports", "WoltLabSuite/Core/Ajax/Backend", "../Result"], function (require, exports, Backend_1, Result_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.getRow = getRow;
    async function getRow(gridViewClass, objectId, gridViewParameters) {
        const url = new URL(`${window.WSC_RPC_API_URL}core/grid-views/row`);
        url.searchParams.set("gridView", gridViewClass);
        url.searchParams.set("objectID", objectId.toString());
        if (gridViewParameters) {
            gridViewParameters.forEach((value, key) => {
                if (Array.isArray(value)) {
                    value.forEach((innerValue, innerkey) => {
                        url.searchParams.set(`gridViewParameters[${key}][${innerkey}]`, innerValue);
                    });
                }
                else {
                    url.searchParams.set(`gridViewParameters[${key}]`, value);
                }
            });
        }
        return (0, Result_1.fromInfallibleApiRequest)(() => {
            return (0, Backend_1.prepareRequest)(url).get().allowCaching().disableLoadingIndicator().fetchAsJson();
        });
    }
});

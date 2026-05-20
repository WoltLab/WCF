/**
 * Gets the items of a node tree view.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
define(["require", "exports", "WoltLabSuite/Core/Ajax/Backend", "../Result"], function (require, exports, Backend_1, Result_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.getNodes = getNodes;
    async function getNodes(nodeTreeViewClass, nodeTreeViewParameters) {
        const url = new URL(`${window.WSC_RPC_API_URL}core/node-tree-views/nodes`);
        url.searchParams.set("nodeTreeView", nodeTreeViewClass);
        if (nodeTreeViewParameters) {
            nodeTreeViewParameters.forEach((value, key) => {
                if (Array.isArray(value)) {
                    value.forEach((innerValue, innerKey) => {
                        url.searchParams.set(`nodeTreeViewParameters[${key}][${innerKey}]`, innerValue);
                    });
                }
                else {
                    url.searchParams.set(`nodeTreeViewParameters[${key}]`, value);
                }
            });
        }
        return (0, Result_1.fromInfallibleApiRequest)(() => {
            return (0, Backend_1.prepareRequest)(url).get().allowCaching().disableLoadingIndicator().fetchAsJson();
        });
    }
});

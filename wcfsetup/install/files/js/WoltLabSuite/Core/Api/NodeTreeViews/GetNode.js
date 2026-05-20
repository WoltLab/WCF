/**
 * Gets a single node for rendering in a node tree view.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
define(["require", "exports", "WoltLabSuite/Core/Ajax/Backend", "../Result"], function (require, exports, Backend_1, Result_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.getNode = getNode;
    async function getNode(nodeTreeViewClass, objectId, nodeTreeViewParameters) {
        const url = new URL(`${window.WSC_RPC_API_URL}core/node-tree-views/node`);
        url.searchParams.set("nodeTreeView", nodeTreeViewClass);
        url.searchParams.set("objectID", objectId.toString());
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

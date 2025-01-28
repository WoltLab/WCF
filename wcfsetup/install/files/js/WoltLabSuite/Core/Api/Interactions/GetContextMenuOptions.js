/**
 * Gets the context menu options for an interaction button.
 *
 * @author Marcel Werk
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
define(["require", "exports", "WoltLabSuite/Core/Ajax/Backend", "../Result"], function (require, exports, Backend_1, Result_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.getContextMenuOptions = getContextMenuOptions;
    async function getContextMenuOptions(providerClassName, objectId) {
        const url = new URL(`${window.WSC_RPC_API_URL}core/interactions/context-menu-options`);
        url.searchParams.set("provider", providerClassName);
        url.searchParams.set("objectID", objectId.toString());
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

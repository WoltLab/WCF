/**
 * Refreshes the package database and returns the list of the suggested packages
 * that can be installed.
 *
 * @author Alexander Ebert
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "WoltLabSuite/Core/Ajax/Backend", "WoltLabSuite/Core/Api/Result"], function (require, exports, Backend_1, Result_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.resolveSuggestedPackages = resolveSuggestedPackages;
    async function resolveSuggestedPackages(packageIds, suggestions) {
        let response;
        try {
            response = (await (0, Backend_1.prepareRequest)(`${window.WSC_RPC_API_URL}core/packages/suggestions/resolve`)
                .post({ packageIDs: packageIds, suggestions })
                .disableLoadingIndicator()
                .fetchAsJson());
        }
        catch (e) {
            return (0, Result_1.apiResultFromError)(e);
        }
        return (0, Result_1.apiResultFromValue)(response);
    }
});

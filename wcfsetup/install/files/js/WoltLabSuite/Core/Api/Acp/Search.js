/**
 * Fetches ACP search results for a given query.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "WoltLabSuite/Core/Ajax/Backend", "WoltLabSuite/Core/Api/Result"], function (require, exports, Backend_1, Result_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.searchAcp = searchAcp;
    async function searchAcp(query, provider = "") {
        const url = new URL(`${window.WSC_RPC_API_URL}core/acp/search`);
        url.searchParams.set("query", query);
        if (provider) {
            url.searchParams.set("provider", provider);
        }
        let response;
        try {
            response = (await (0, Backend_1.prepareRequest)(url.toString()).get().fetchAsJson());
        }
        catch (e) {
            return (0, Result_1.apiResultFromError)(e);
        }
        return (0, Result_1.apiResultFromValue)(response);
    }
});

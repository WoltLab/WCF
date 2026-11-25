/**
 * Requests the list of users and groups that match the provided query string.
 *
 * @author Alexander Ebert
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "WoltLabSuite/Core/Ajax/Backend", "../Result"], function (require, exports, Backend_1, Result_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.mentionSuggestions = mentionSuggestions;
    function mentionSuggestions(query, context) {
        const url = new URL(window.WSC_RPC_API_URL + "core/messages/mention-suggestions");
        url.searchParams.set("query", query);
        if (context !== undefined) {
            url.searchParams.set("objectType", context.objectType);
            url.searchParams.set("objectID", context.objectID.toString());
        }
        return (0, Result_1.fromInfallibleApiRequest)(() => {
            return (0, Backend_1.prepareRequest)(url).get().allowCaching().disableLoadingIndicator().fetchAsJson();
        });
    }
});

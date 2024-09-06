/**
 * Gets the html code for the rendering of responses.
 *
 * @author Marcel Werk
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */
define(["require", "exports", "WoltLabSuite/Core/Ajax/Backend", "../../Result"], function (require, exports, Backend_1, Result_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.renderResponses = void 0;
    async function renderResponses(commentId, lastResponseTime, lastResponseId, loadAllResponses) {
        const url = new URL(`${window.WSC_RPC_API_URL}core/comments/responses/render`);
        url.searchParams.set("commentID", commentId.toString());
        url.searchParams.set("lastResponseTime", lastResponseTime.toString());
        url.searchParams.set("lastResponseID", lastResponseId.toString());
        url.searchParams.set("loadAllResponses", loadAllResponses.toString());
        let response;
        try {
            response = (await (0, Backend_1.prepareRequest)(url).get().fetchAsJson());
        }
        catch (e) {
            return (0, Result_1.apiResultFromError)(e);
        }
        return (0, Result_1.apiResultFromValue)(response);
    }
    exports.renderResponses = renderResponses;
});

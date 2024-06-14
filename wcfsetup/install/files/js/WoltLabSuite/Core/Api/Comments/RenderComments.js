/**
 * Gets the html code for the rendering of comments.
 *
 * @author Marcel Werk
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */
define(["require", "exports", "WoltLabSuite/Core/Ajax/Backend", "../Result"], function (require, exports, Backend_1, Result_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.renderComments = void 0;
    async function renderComments(objectTypeId, objectId, lastCommentTime = 0) {
        const url = new URL(`${window.WSC_API_URL}index.php?api/rpc/core/comments/render`);
        url.searchParams.set("objectTypeID", objectTypeId.toString());
        url.searchParams.set("objectID", objectId.toString());
        url.searchParams.set("lastCommentTime", lastCommentTime.toString());
        let response;
        try {
            response = (await (0, Backend_1.prepareRequest)(url).get().fetchAsJson());
        }
        catch (e) {
            return (0, Result_1.apiResultFromError)(e);
        }
        return (0, Result_1.apiResultFromValue)(response);
    }
    exports.renderComments = renderComments;
});

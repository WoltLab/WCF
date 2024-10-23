/**
 * Creates a new comment.
 *
 * @author Marcel Werk
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "WoltLabSuite/Core/Ajax/Backend", "../Result"], function (require, exports, Backend_1, Result_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.createComment = createComment;
    async function createComment(objectTypeId, objectId, message, guestToken = "") {
        const url = new URL(`${window.WSC_RPC_API_URL}core/comments`);
        const payload = {
            objectTypeID: objectTypeId,
            objectID: objectId,
            message,
            guestToken,
        };
        let response;
        try {
            response = (await (0, Backend_1.prepareRequest)(url).post(payload).fetchAsJson());
        }
        catch (e) {
            return (0, Result_1.apiResultFromError)(e);
        }
        return (0, Result_1.apiResultFromValue)(response);
    }
});

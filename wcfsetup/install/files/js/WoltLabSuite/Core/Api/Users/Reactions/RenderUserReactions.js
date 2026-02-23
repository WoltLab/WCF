/**
 * Loads a paginated list of reactions for a user profile.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
define(["require", "exports", "WoltLabSuite/Core/Ajax/Backend", "../../Result"], function (require, exports, Backend_1, Result_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.renderUserReactions = renderUserReactions;
    async function renderUserReactions(userID, targetType, lastLikeTime = 0, reactionTypeID = 0) {
        const url = new URL(`${window.WSC_RPC_API_URL}core/users/${userID}/reactions/render`);
        url.searchParams.set("targetType", targetType);
        url.searchParams.set("lastLikeTime", lastLikeTime.toString());
        url.searchParams.set("reactionTypeID", reactionTypeID.toString());
        let response;
        try {
            response = (await (0, Backend_1.prepareRequest)(url).get().fetchAsJson());
        }
        catch (e) {
            return (0, Result_1.apiResultFromError)(e);
        }
        return (0, Result_1.apiResultFromValue)(response);
    }
});

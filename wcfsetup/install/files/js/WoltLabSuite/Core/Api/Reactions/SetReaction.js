/**
 * Sets a reaction on an object.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "WoltLabSuite/Core/Ajax/Backend", "../Result"], function (require, exports, Backend_1, Result_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setReaction = setReaction;
    async function setReaction(objectType, objectID, reactionTypeID) {
        const url = new URL(`${window.WSC_RPC_API_URL}core/reactions/set`);
        let response;
        try {
            response = (await (0, Backend_1.prepareRequest)(url)
                .post({
                objectType,
                objectID,
                reactionTypeID,
            })
                .fetchAsJson());
        }
        catch (e) {
            return (0, Result_1.apiResultFromError)(e);
        }
        return (0, Result_1.apiResultFromValue)(response);
    }
});

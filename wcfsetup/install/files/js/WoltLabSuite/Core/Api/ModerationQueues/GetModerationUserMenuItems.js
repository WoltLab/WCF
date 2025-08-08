/**
 * Retrieves the user menu items for the moderation queues.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "WoltLabSuite/Core/Ajax/Backend", "../Result"], function (require, exports, Backend_1, Result_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.getModerationUserMenuItems = getModerationUserMenuItems;
    async function getModerationUserMenuItems() {
        let response;
        try {
            response = (await (0, Backend_1.prepareRequest)(`${window.WSC_RPC_API_URL}core/moderation-queues/user-menu-items`)
                .get()
                .fetchAsJson());
        }
        catch (e) {
            return (0, Result_1.apiResultFromError)(e);
        }
        return (0, Result_1.apiResultFromValue)(response);
    }
});

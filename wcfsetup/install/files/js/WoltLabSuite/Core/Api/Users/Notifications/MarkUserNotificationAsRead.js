/**
 * Marks a user notification as read.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "WoltLabSuite/Core/Api/Result", "WoltLabSuite/Core/Ajax/Backend"], function (require, exports, Result_1, Backend_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.markUserNotificationAsRead = markUserNotificationAsRead;
    async function markUserNotificationAsRead(notificationId) {
        return (0, Result_1.fromInfallibleApiRequest)(() => {
            return (0, Backend_1.prepareRequest)(`${window.WSC_RPC_API_URL}core/users/notifications/${notificationId}/mark-as-read`)
                .post()
                .fetchAsJson();
        });
    }
});

/**
 * Handles the buttons on the notifcation list page.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "WoltLabSuite/Core/Component/Confirmation", "WoltLabSuite/Core/Component/Snackbar", "WoltLabSuite/Core/Helper/PromiseMutex", "WoltLabSuite/Core/Language", "WoltLabSuite/Core/Api/Users/Notifications/MarkAllUserNotificationsAsRead", "WoltLabSuite/Core/Api/Users/Notifications/MarkUserNotificationAsRead"], function (require, exports, Confirmation_1, Snackbar_1, PromiseMutex_1, Language_1, MarkAllUserNotificationsAsRead_1, MarkUserNotificationAsRead_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    function initMarkAllAsRead() {
        document.querySelector(".jsMarkAllAsConfirmed")?.addEventListener("click", (0, PromiseMutex_1.promiseMutex)(() => markAllAsRead()));
    }
    async function markAllAsRead() {
        const result = await (0, Confirmation_1.confirmationFactory)()
            .custom((0, Language_1.getPhrase)("wcf.user.notification.markAllAsConfirmed.confirmMessage"))
            .withoutMessage();
        if (!result) {
            return;
        }
        await (0, MarkAllUserNotificationsAsRead_1.markAllUserNotificationsAsRead)();
        (0, Snackbar_1.showDefaultSuccessSnackbar)().addEventListener("snackbar:close", () => {
            window.location.reload();
        });
    }
    function initMarkAsRead() {
        document.querySelectorAll('.notificationListItem[data-is-read="false"]').forEach((element) => {
            element.querySelector(".notificationListItem__markAsRead")?.addEventListener("click", (0, PromiseMutex_1.promiseMutex)(() => markAsRead(element)));
        });
    }
    async function markAsRead(element) {
        await (0, MarkUserNotificationAsRead_1.markUserNotificationAsRead)(parseInt(element.dataset.objectId, 10));
        element.querySelector(".notificationListItem__unread")?.remove();
        element.dataset.isRead = "true";
    }
    function setup() {
        initMarkAllAsRead();
        initMarkAsRead();
    }
});

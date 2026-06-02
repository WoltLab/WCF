/**
 * Handles the 'mark as read' action for moderation queues.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "WoltLabSuite/Core/Component/Snackbar", "WoltLabSuite/Core/Api/ModerationQueues/MarkAllModerationQueuesAsRead", "WoltLabSuite/Core/Helper/PromiseMutex"], function (require, exports, Snackbar_1, MarkAllModerationQueuesAsRead_1, PromiseMutex_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    async function markAllAsRead(button, gridView) {
        (await (0, MarkAllModerationQueuesAsRead_1.markAllModerationQueuesAsRead)()).unwrap();
        if (gridView !== undefined) {
            gridView.dispatchEvent(new CustomEvent("interaction:invalidate-all"));
        }
        button.remove();
        (0, Snackbar_1.showDefaultSuccessSnackbar)();
    }
    function setup(button, gridView) {
        button.addEventListener("click", (0, PromiseMutex_1.promiseMutex)(async () => {
            await markAllAsRead(button, gridView);
        }));
    }
});

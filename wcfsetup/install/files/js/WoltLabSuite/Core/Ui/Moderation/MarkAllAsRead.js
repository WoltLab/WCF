/**
 * Marks all moderation queue entries as read.
 *
 * @author  Marcel Werk
 * @copyright  2001-2022 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
define(["require", "exports", "WoltLabSuite/Core/Component/Snackbar", "WoltLabSuite/Core/Api/ModerationQueues/MarkAllModerationQueueItemsAsRead"], function (require, exports, Snackbar_1, MarkAllModerationQueueItemsAsRead_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    async function markAllAsRead() {
        await (0, MarkAllModerationQueueItemsAsRead_1.markAllModerationQueueItemsAsRead)();
        const gridViewTable = document.getElementById("wcf-system-gridView-user-ModerationQueueGridView_table");
        gridViewTable.dispatchEvent(new CustomEvent("interaction:invalidate-all"));
        (0, Snackbar_1.showDefaultSuccessSnackbar)();
    }
    function setup() {
        document.querySelector(".markAllAsReadButton")?.addEventListener("click", () => {
            void markAllAsRead();
        });
    }
});

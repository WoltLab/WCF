/**
 * Handles the 'mark as read' action for articles.
 *
 * @author  Marcel Werk
 * @copyright  2001-2023 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "WoltLabSuite/Core/Component/Snackbar", "WoltLabSuite/Core/Api/Articles/MarkAllArticlesAsRead", "WoltLabSuite/Core/Helper/PromiseMutex"], function (require, exports, Snackbar_1, MarkAllArticlesAsRead_1, PromiseMutex_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    async function markAllAsRead(listView) {
        await (0, MarkAllArticlesAsRead_1.markAllArticlesAsRead)();
        if (listView !== undefined) {
            listView.dispatchEvent(new CustomEvent("interaction:invalidate-all"));
        }
        document.querySelectorAll(".boxMenu .active .badgeUpdate").forEach((el) => el.remove());
        (0, Snackbar_1.showDefaultSuccessSnackbar)();
    }
    function setup(listView) {
        document.querySelectorAll(".markAllAsReadButton").forEach((el) => {
            el.addEventListener("click", (0, PromiseMutex_1.promiseMutex)(async () => {
                await markAllAsRead(listView);
            }));
        });
    }
});

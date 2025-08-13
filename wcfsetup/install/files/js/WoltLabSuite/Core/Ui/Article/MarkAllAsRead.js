/**
 * Handles the 'mark as read' action for articles.
 *
 * @author  Marcel Werk
 * @copyright  2001-2023 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "WoltLabSuite/Core/Component/Snackbar", "WoltLabSuite/Core/Api/Articles/MarkAllArticlesAsRead"], function (require, exports, Snackbar_1, MarkAllArticlesAsRead_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    async function markAllAsRead() {
        await (0, MarkAllArticlesAsRead_1.markAllArticlesAsRead)();
        document.querySelectorAll(".contentItemList .contentItemBadgeNew").forEach((el) => el.remove());
        document.querySelectorAll(".boxMenu .active .badge").forEach((el) => el.remove());
        (0, Snackbar_1.showDefaultSuccessSnackbar)();
    }
    function setup() {
        document.querySelectorAll(".markAllAsReadButton").forEach((el) => {
            el.addEventListener("click", (event) => {
                event.preventDefault();
                void markAllAsRead();
            });
        });
    }
});

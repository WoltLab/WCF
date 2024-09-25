/**
 * Handles the 'mark as read' action for articles.
 *
 * @author  Marcel Werk
 * @copyright  2001-2023 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "tslib", "../../Ajax", "../Notification"], function (require, exports, tslib_1, Ajax_1, UiNotification) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    UiNotification = tslib_1.__importStar(UiNotification);
    async function markAllAsRead() {
        await (0, Ajax_1.dboAction)("markAllAsRead", "wcf\\data\\article\\ArticleAction").dispatch();
        document.querySelectorAll(".contentItemList .contentItemBadgeNew").forEach((el) => el.remove());
        document.querySelectorAll(".boxMenu .active .badge").forEach((el) => el.remove());
        UiNotification.show();
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

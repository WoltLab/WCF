/**
 * Handles the 'mark as read' action for articles.
 *
 * @author  Marcel Werk
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Article/MarkAllAsRead
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "tslib", "../../Ajax", "../../Event/Handler"], function (require, exports, tslib_1, Ajax, EventHandler) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.init = void 0;
    Ajax = tslib_1.__importStar(Ajax);
    EventHandler = tslib_1.__importStar(EventHandler);
    class UiArticleMarkAllAsRead {
        constructor() {
            document.querySelectorAll(".markAllAsReadButton").forEach((button) => {
                button.addEventListener("click", this.click.bind(this));
            });
        }
        click(event) {
            event.preventDefault();
            Ajax.api(this);
        }
        _ajaxSuccess() {
            /* remove obsolete badges */
            // main menu
            const badge = document.querySelector(".mainMenu .active .badge");
            if (badge)
                badge.remove();
            // mobile page menu badge
            document.querySelectorAll(".pageMainMenuMobile .active").forEach((container) => {
                var _a, _b;
                (_b = (_a = container.closest(".menuOverlayItem")) === null || _a === void 0 ? void 0 : _a.querySelector(".badge")) === null || _b === void 0 ? void 0 : _b.remove();
            });
            // article list
            document.querySelectorAll(".contentItemList .contentItemBadgeNew").forEach((el) => el.remove());
            EventHandler.fire("com.woltlab.wcf.MainMenuMobile", "updateButtonState");
        }
        _ajaxSetup() {
            return {
                data: {
                    actionName: "markAllAsRead",
                    className: "wcf\\data\\article\\ArticleAction",
                },
            };
        }
    }
    function init() {
        new UiArticleMarkAllAsRead();
    }
    exports.init = init;
});

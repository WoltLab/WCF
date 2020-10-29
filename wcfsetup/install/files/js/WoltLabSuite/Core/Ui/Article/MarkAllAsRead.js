/**
 * Handles the 'mark as read' action for articles.
 *
 * @author  Marcel Werk
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Article/MarkAllAsRead
 */
define(["require", "exports", "tslib", "../../Ajax"], function (require, exports, tslib_1, Ajax) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.init = void 0;
    Ajax = tslib_1.__importStar(Ajax);
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
            // article list
            document.querySelectorAll(".articleList .newMessageBadge").forEach((el) => el.remove());
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

/**
 * Handles the 'mark as read' action for articles.
 *
 * @author  Marcel Werk
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Article/MarkAllAsRead
 */
var __createBinding = (this && this.__createBinding) || (Object.create ? (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    Object.defineProperty(o, k2, { enumerable: true, get: function() { return m[k]; } });
}) : (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    o[k2] = m[k];
}));
var __setModuleDefault = (this && this.__setModuleDefault) || (Object.create ? (function(o, v) {
    Object.defineProperty(o, "default", { enumerable: true, value: v });
}) : function(o, v) {
    o["default"] = v;
});
var __importStar = (this && this.__importStar) || function (mod) {
    if (mod && mod.__esModule) return mod;
    var result = {};
    if (mod != null) for (var k in mod) if (k !== "default" && Object.prototype.hasOwnProperty.call(mod, k)) __createBinding(result, mod, k);
    __setModuleDefault(result, mod);
    return result;
};
define(["require", "exports", "../../Ajax"], function (require, exports, Ajax) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.init = void 0;
    Ajax = __importStar(Ajax);
    class UiArticleMarkAllAsRead {
        constructor() {
            document.querySelectorAll('.markAllAsReadButton').forEach(button => {
                button.addEventListener('click', this.click.bind(this));
            });
        }
        click(event) {
            event.preventDefault();
            Ajax.api(this);
        }
        _ajaxSuccess() {
            /* remove obsolete badges */
            // main menu
            const badge = document.querySelector('.mainMenu .active .badge');
            if (badge)
                badge.remove();
            // article list
            document.querySelectorAll('.articleList .newMessageBadge').forEach(el => el.remove());
        }
        _ajaxSetup() {
            return {
                data: {
                    actionName: 'markAllAsRead',
                    className: 'wcf\\data\\article\\ArticleAction',
                },
            };
        }
    }
    function init() {
        new UiArticleMarkAllAsRead();
    }
    exports.init = init;
});

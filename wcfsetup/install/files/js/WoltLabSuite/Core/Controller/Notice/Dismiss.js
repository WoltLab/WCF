/**
 * Handles dismissible user notices.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "tslib", "../../Ajax"], function (require, exports, tslib_1, Ajax) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    Ajax = tslib_1.__importStar(Ajax);
    /**
     * Initializes dismiss buttons.
     */
    function setup() {
        document.querySelectorAll(".jsDismissNoticeButton").forEach((button) => {
            button.addEventListener("click", (ev) => click(ev));
        });
    }
    /**
     * Sends a request to dismiss a notice and removes it afterwards.
     */
    function click(event) {
        const button = event.currentTarget;
        Ajax.apiOnce({
            data: {
                actionName: "dismiss",
                className: "wcf\\data\\notice\\NoticeAction",
                objectIDs: [button.dataset.objectId],
            },
            success: () => {
                button.parentElement.remove();
            },
        });
    }
});

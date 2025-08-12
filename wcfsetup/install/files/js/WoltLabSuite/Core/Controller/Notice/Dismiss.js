/**
 * Handles dismissible user notices.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "WoltLabSuite/Core/Api/Notices/DismissNotice", "WoltLabSuite/Core/Helper/PromiseMutex"], function (require, exports, DismissNotice_1, PromiseMutex_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    /**
     * Initializes dismiss buttons.
     */
    function setup() {
        document.querySelectorAll(".jsDismissNoticeButton").forEach((button) => {
            button.addEventListener("click", (0, PromiseMutex_1.promiseMutex)(() => click(button)));
        });
    }
    /**
     * Sends a request to dismiss a notice and removes it afterwards.
     */
    async function click(button) {
        await (0, DismissNotice_1.dismissNotice)(parseInt(button.dataset.objectId, 10));
        button.parentElement.remove();
    }
});

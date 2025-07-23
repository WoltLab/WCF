/**
 * Handles the user follow buttons.
 *
 * @author Marcel Werk
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */
define(["require", "exports", "WoltLabSuite/Core/Ajax/Backend", "WoltLabSuite/Core/Helper/PromiseMutex", "WoltLabSuite/Core/Helper/Selector", "WoltLabSuite/Core/Language", "../Snackbar"], function (require, exports, Backend_1, PromiseMutex_1, Selector_1, Language_1, Snackbar_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    async function toggleFollow(button) {
        if (button.dataset.following !== "1") {
            await (0, Backend_1.prepareRequest)(button.dataset.followUser)
                .post({
                action: "follow",
            })
                .fetchAsResponse();
            button.dataset.following = "1";
            if (button.dataset.type === "button") {
                button.textContent = (0, Language_1.getPhrase)("wcf.user.button.unfollow");
            }
            else {
                button.dataset.tooltip = (0, Language_1.getPhrase)("wcf.user.button.unfollow");
                button.querySelector("fa-icon")?.setIcon("minus");
            }
        }
        else {
            await (0, Backend_1.prepareRequest)(button.dataset.followUser)
                .post({
                action: "unfollow",
            })
                .fetchAsResponse();
            button.dataset.following = "0";
            if (button.dataset.type === "button") {
                button.textContent = (0, Language_1.getPhrase)("wcf.user.button.follow");
            }
            else {
                button.dataset.tooltip = (0, Language_1.getPhrase)("wcf.user.button.follow");
                button.querySelector("fa-icon")?.setIcon("plus");
            }
        }
        (0, Snackbar_1.showDefaultSuccessSnackbar)();
    }
    function setup() {
        (0, Selector_1.wheneverFirstSeen)("[data-follow-user]", (button) => {
            button.addEventListener("click", (0, PromiseMutex_1.promiseMutex)(() => toggleFollow(button)));
        });
    }
});

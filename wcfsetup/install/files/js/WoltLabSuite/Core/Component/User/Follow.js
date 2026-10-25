/**
 * Handles the user follow buttons.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */
define(["require", "exports", "WoltLabSuite/Core/Ajax/Backend", "WoltLabSuite/Core/Api/Users/FollowUser", "WoltLabSuite/Core/Api/Users/UnfollowUser", "WoltLabSuite/Core/Helper/PromiseMutex", "WoltLabSuite/Core/Helper/Selector", "WoltLabSuite/Core/Language", "../Snackbar"], function (require, exports, Backend_1, FollowUser_1, UnfollowUser_1, PromiseMutex_1, Selector_1, Language_1, Snackbar_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    async function sendRequest(button, follow) {
        const value = button.dataset.followUser;
        // Legacy markup provides the URL of the deprecated `UserFollowAction`.
        if (!/^\d+$/.test(value)) {
            await (0, Backend_1.prepareRequest)(value)
                .post({
                action: follow ? "follow" : "unfollow",
            })
                .fetchAsResponse();
            return;
        }
        const userId = parseInt(value, 10);
        if (follow) {
            await (0, FollowUser_1.followUser)(userId);
        }
        else {
            await (0, UnfollowUser_1.unfollowUser)(userId);
        }
    }
    async function toggleFollow(button) {
        if (button.dataset.following !== "1") {
            await sendRequest(button, true);
            button.dataset.following = "1";
            if (button.dataset.type === "button") {
                button.textContent = (0, Language_1.getPhrase)("wcf.user.button.unfollow");
            }
            else {
                button.dataset.tooltip = (0, Language_1.getPhrase)("wcf.user.button.unfollow");
                button.querySelector("fa-icon")?.setIcon("user-minus");
                updateInvisibleLabel(button, (0, Language_1.getPhrase)("wcf.user.button.unfollow"));
            }
        }
        else {
            await sendRequest(button, false);
            button.dataset.following = "0";
            if (button.dataset.type === "button") {
                button.textContent = (0, Language_1.getPhrase)("wcf.user.button.follow");
            }
            else {
                button.dataset.tooltip = (0, Language_1.getPhrase)("wcf.user.button.follow");
                button.querySelector("fa-icon")?.setIcon("user-plus");
                updateInvisibleLabel(button, (0, Language_1.getPhrase)("wcf.user.button.follow"));
            }
        }
        (0, Snackbar_1.showDefaultSuccessSnackbar)();
    }
    // Icon buttons in a `.buttonGroupNavigation` show this label in the mobile dropdown.
    function updateInvisibleLabel(button, label) {
        const invisible = button.querySelector(".invisible");
        if (invisible !== null) {
            invisible.textContent = label;
        }
    }
    function setup() {
        (0, Selector_1.wheneverFirstSeen)("[data-follow-user]", (button) => {
            button.addEventListener("click", (0, PromiseMutex_1.promiseMutex)(() => toggleFollow(button)));
        });
    }
});

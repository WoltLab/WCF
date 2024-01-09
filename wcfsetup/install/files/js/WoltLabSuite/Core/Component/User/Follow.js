/**
 * Handles the user follow buttons.
 *
 * @author Marcel Werk
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */
define(["require", "exports", "tslib", "WoltLabSuite/Core/Ajax/Backend", "WoltLabSuite/Core/Helper/PromiseMutex", "WoltLabSuite/Core/Helper/Selector", "WoltLabSuite/Core/Language", "WoltLabSuite/Core/Ui/Notification"], function (require, exports, tslib_1, Backend_1, PromiseMutex_1, Selector_1, Language_1, UiNotification) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = void 0;
    UiNotification = tslib_1.__importStar(UiNotification);
    async function toggleFollow(button) {
        if (button.dataset.following !== "1") {
            await (0, Backend_1.prepareRequest)(button.dataset.followUser)
                .post({
                action: "follow",
            })
                .fetchAsResponse();
            button.dataset.following = "1";
            button.dataset.tooltip = (0, Language_1.getPhrase)("wcf.user.button.unfollow");
            button.querySelector("fa-icon")?.setIcon("circle-minus");
        }
        else {
            await (0, Backend_1.prepareRequest)(button.dataset.followUser)
                .post({
                action: "unfollow",
            })
                .fetchAsResponse();
            button.dataset.following = "0";
            button.dataset.tooltip = (0, Language_1.getPhrase)("wcf.user.button.follow");
            button.querySelector("fa-icon")?.setIcon("circle-plus");
        }
        UiNotification.show();
    }
    function setup() {
        (0, Selector_1.wheneverFirstSeen)("[data-follow-user]", (button) => {
            button.addEventListener("click", (0, PromiseMutex_1.promiseMutex)(() => toggleFollow(button)));
        });
    }
    exports.setup = setup;
});
